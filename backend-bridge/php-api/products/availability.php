<?php
/*
 * products/availability.php
 * React 상세페이지 예약 캘린더가 기존 우노트래블 DB 기준의 날짜별 예약 상태를 조회하는 API endpoint입니다.
 * 데일리투어는 tour_closed_2와 tour_reg_count, 세미패키지는 v2_pkgTour 출발 일정을 가볍게 읽어 상태를 계산합니다.
 * 상세 설명이나 관리자 메모는 포함하지 않고 available/soon/soldout과 잔여석만 반환해 상세페이지가 무겁지 않게 유지합니다.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_map.php';

uno_api_require_method('GET');

define('UNO_API_MAX_AVAILABILITY_DAYS', 120);

function uno_api_date_value($value)
{
    $value = trim((string) $value);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return '';
    }

    return $value;
}

function uno_api_date_ts($value)
{
    $time = strtotime($value . ' 00:00:00');
    return $time === false ? 0 : $time;
}

function uno_api_weekday_ko($date)
{
    $labels = json_decode('["\uc77c","\uc6d4","\ud654","\uc218","\ubaa9","\uae08","\ud1a0"]', true);
    $index = (int) date('w', uno_api_date_ts($date));
    return isset($labels[$index]) ? $labels[$index] : '';
}

function uno_api_availability_label($status)
{
    if ($status === 'soldout') {
        return json_decode('"\ub9c8\uac10"');
    }

    if ($status === 'soon') {
        return json_decode('"\ub9c8\uac10 \uc784\ubc15"');
    }

    return json_decode('"\uc608\uc57d \uac00\ub2a5"');
}

function uno_api_availability_status_from_remaining($remainingSeats, $maxCount = 0)
{
    if ($remainingSeats !== null && $remainingSeats < 1) {
        return 'soldout';
    }

    if ($remainingSeats !== null && $maxCount > 0 && $remainingSeats <= ceil($maxCount / 3)) {
        return 'soon';
    }

    return 'available';
}

function uno_api_is_closed_status($value)
{
    $normalized = strtoupper(trim((string) $value));
    return in_array($normalized, array('Y', 'E', 'CLOSED', 'SOLDOUT'), true)
        || strpos($normalized, '마감') !== false
        || strpos($normalized, '휴무') !== false;
}

function uno_api_is_package_closed_status($value)
{
    $normalized = strtoupper(trim((string) $value));
    return in_array($normalized, array('CLOSED', 'SOLDOUT'), true);
}

function uno_api_create_availability_item($date, $status, $remainingSeats = null, $extra = array())
{
    $item = array(
        'id' => $date,
        'date' => $date,
        'weekday' => uno_api_weekday_ko($date),
        'status' => $status,
        'displayLabel' => uno_api_availability_label($status),
    );

    if ($remainingSeats !== null) {
        $item['remainingSeats'] = (int) $remainingSeats;
    }

    foreach ($extra as $key => $value) {
        if ($value !== null && $value !== '') {
            $item[$key] = $value;
        }
    }

    return $item;
}

function uno_api_fetch_daily_closed_dates($legacyProductId, $from, $to)
{
    if (!function_exists('sql_query')) {
        return array();
    }

    $legacyProductId = (int) $legacyProductId;
    $result = sql_query(
        "select closedDate, isClose
           from tour_closed_2
          where pid = '{$legacyProductId}'
            and closedDate >= '{$from}'
            and closedDate <= '{$to}'"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $date = uno_api_date_value(isset($row['closedDate']) ? $row['closedDate'] : '');
        if ($date === '') {
            continue;
        }
        $items[$date] = isset($row['isClose']) ? (string) $row['isClose'] : '';
    }

    return $items;
}

function uno_api_fetch_daily_counts($legacyProductId, $from, $to)
{
    if (!function_exists('sql_query')) {
        return array();
    }

    $legacyProductId = (int) $legacyProductId;
    $result = sql_query(
        "select tourDate, nowCount, maxCount
           from tour_reg_count
          where pid = '{$legacyProductId}'
            and tourDate >= '{$from}'
            and tourDate <= '{$to}'"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $date = uno_api_date_value(isset($row['tourDate']) ? $row['tourDate'] : '');
        if ($date === '') {
            continue;
        }
        $maxCount = isset($row['maxCount']) ? (int) $row['maxCount'] : 0;
        $nowCount = isset($row['nowCount']) ? (int) $row['nowCount'] : 0;
        $items[$date] = array(
            'maxCount' => $maxCount,
            'nowCount' => $nowCount,
            'remainingSeats' => $maxCount > 0 ? max(0, $maxCount - $nowCount) : null,
        );
    }

    return $items;
}

function uno_api_daily_availability($legacyProductId, $from, $to)
{
    $closedDates = uno_api_fetch_daily_closed_dates($legacyProductId, $from, $to);
    $counts = uno_api_fetch_daily_counts($legacyProductId, $from, $to);
    $items = array();

    for ($time = uno_api_date_ts($from); $time <= uno_api_date_ts($to); $time += 86400) {
        $date = date('Y-m-d', $time);
        $remainingSeats = isset($counts[$date]) ? $counts[$date]['remainingSeats'] : null;
        $maxCount = isset($counts[$date]) ? (int) $counts[$date]['maxCount'] : 0;
        $status = uno_api_availability_status_from_remaining($remainingSeats, $maxCount);

        if (isset($closedDates[$date]) && uno_api_is_closed_status($closedDates[$date])) {
            $status = 'soldout';
            $remainingSeats = 0;
        }

        $items[] = uno_api_create_availability_item($date, $status, $remainingSeats);
    }

    return $items;
}

function uno_api_package_availability($legacyProductId, $from, $to)
{
    if (!function_exists('sql_query')) {
        return array();
    }

    $legacyProductId = (int) $legacyProductId;
    $result = sql_query(
        "select id, start_time, seat, status
           from v2_pkgTour
          where pid = '{$legacyProductId}'
            and start_time >= '{$from}'
            and start_time <= '{$to}'
            and (del_time = 0 or del_time is null)
            and (is_view = 'Y' or is_view = '1')
          order by start_time asc, id asc"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $date = uno_api_date_value(isset($row['start_time']) ? $row['start_time'] : '');
        if ($date === '') {
            continue;
        }

        $remainingSeats = isset($row['seat']) ? max(0, (int) $row['seat']) : null;
        $status = uno_api_is_package_closed_status(isset($row['status']) ? $row['status'] : '')
            ? 'soldout'
            : 'available';

        $items[] = uno_api_create_availability_item($date, $status, $remainingSeats, array(
            'legacyPackageScheduleId' => isset($row['id']) ? (int) $row['id'] : null,
        ));
    }

    return $items;
}

$productId = isset($_GET['id']) ? trim((string) $_GET['id']) : '';
$from = uno_api_date_value(isset($_GET['from']) ? $_GET['from'] : '');
$to = uno_api_date_value(isset($_GET['to']) ? $_GET['to'] : '');

if ($productId === '' || $from === '' || $to === '') {
    uno_api_error('VALIDATION_ERROR', '상품 ID와 조회 기간이 필요합니다.', 400);
}

$fromTime = uno_api_date_ts($from);
$toTime = uno_api_date_ts($to);

if ($fromTime === 0 || $toTime === 0 || $toTime < $fromTime) {
    uno_api_error('VALIDATION_ERROR', '조회 기간이 올바르지 않습니다.', 400);
}

$maxToTime = $fromTime + UNO_API_MAX_AVAILABILITY_DAYS * 86400;
if ($toTime > $maxToTime) {
    $to = date('Y-m-d', $maxToTime);
}

$mapping = uno_api_product_mapping($productId);
if (!$mapping || empty($mapping['legacyProductId'])) {
    uno_api_error('PRODUCT_NOT_MAPPED', '기존 DB와 연결되지 않은 상품입니다.', 404);
}

if (!function_exists('sql_query')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB 함수를 찾을 수 없습니다.', 500);
}

$legacyProductId = (int) $mapping['legacyProductId'];
$productType = isset($mapping['legacyCategory']) && $mapping['legacyCategory'] === 'semi'
    ? 'semi'
    : 'daily';

$dates = $productType === 'semi'
    ? uno_api_package_availability($legacyProductId, $from, $to)
    : uno_api_daily_availability($legacyProductId, $from, $to);

uno_api_success(array(
    'productId' => $productId,
    'legacyProductId' => $legacyProductId,
    'from' => $from,
    'to' => $to,
    'dates' => $dates,
));
