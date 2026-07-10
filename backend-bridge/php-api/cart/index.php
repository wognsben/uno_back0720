<?php
/*
 * cart/index.php
 * React 장바구니 CTA가 기존 우노트래블 tour_reg 테이블에 장바구니 row를 생성하는 API endpoint입니다.
 * 프런트 payload의 금액을 신뢰하지 않고 tour_fee 또는 v2_pkgTour에서 옵션/금액을 다시 조회해 저장합니다.
 * 예약 확정 화면이나 관리자 화면을 만들지 않고, status=cart 저장과 최소 JSON 응답만 담당합니다.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_map.php';
require_once dirname(__DIR__) . '/_reservation_helpers.php';

uno_api_require_login();

$unoApiCartMethod = isset($_SERVER['REQUEST_METHOD'])
    ? strtoupper($_SERVER['REQUEST_METHOD'])
    : 'GET';

if (!in_array($unoApiCartMethod, array('GET', 'POST'), true)) {
    uno_api_error('VALIDATION_ERROR', '허용되지 않은 요청 방식입니다.', 405);
}

function uno_api_cart_read_json()
{
    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);

    if (!is_array($payload)) {
        uno_api_error('VALIDATION_ERROR', '요청 형식이 올바르지 않습니다.', 400);
    }

    return $payload;
}

function uno_api_cart_escape($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string($value);
    }

    if (function_exists('sql_real_escape_string')) {
        return sql_real_escape_string($value);
    }

    return addslashes($value);
}

function uno_api_cart_money($value)
{
    return (int) preg_replace('/[^0-9-]/', '', (string) $value);
}

function uno_api_cart_pipe($values)
{
    $items = array();
    foreach ($values as $value) {
        if ($value === null || $value === '') {
            continue;
        }
        $items[] = (string) $value;
    }

    return count($items) ? implode('|', $items) . '|' : '';
}

function uno_api_cart_client_ip()
{
    return isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
}

function uno_api_cart_insert_id()
{
    if (function_exists('sql_insert_id')) {
        return sql_insert_id();
    }

    if (function_exists('mysql_insert_id')) {
        return mysql_insert_id();
    }

    return 0;
}

function uno_api_cart_member_defaults()
{
    $member = uno_api_member();

    return array(
        'mb_id' => isset($member['mb_id']) ? (string) $member['mb_id'] : '',
        'mb_name' => isset($member['mb_name']) ? (string) $member['mb_name'] : '',
        'mb_email' => isset($member['mb_email']) ? (string) $member['mb_email'] : '',
        'mb_kakao' => isset($member['mb_kakao']) ? (string) $member['mb_kakao'] : '',
        'mb_hp' => isset($member['mb_hp']) ? (string) $member['mb_hp'] : '',
    );
}

function uno_api_cart_fetch_daily_option($legacyProductId, $feeId)
{
    $legacyProductId = (int) $legacyProductId;
    $feeId = (int) $feeId;
    $row = sql_fetch(
        "select id, fee_subject, fee1, fee2, fee3
           from tour_fee
          where wr_id = '{$legacyProductId}'
            and id = '{$feeId}'"
    );

    if (!$row || empty($row['id'])) {
        uno_api_error('VALIDATION_ERROR', '선택한 요금 옵션을 찾을 수 없습니다.', 400);
    }

    return array(
        'feeId' => (int) $row['id'],
        'label' => isset($row['fee_subject']) ? (string) $row['fee_subject'] : '',
        'deposit' => uno_api_cart_money(isset($row['fee1']) ? $row['fee1'] : 0),
        'localPayment' => uno_api_cart_money(isset($row['fee2']) ? $row['fee2'] : 0),
        'extraPayment' => uno_api_cart_money(isset($row['fee3']) ? $row['fee3'] : 0),
        'packageTotal' => 0,
        'airfare' => 0,
    );
}

function uno_api_cart_fetch_package_option($legacyProductId, $scheduleId)
{
    $legacyProductId = (int) $legacyProductId;
    $scheduleId = (int) $scheduleId;
    $row = sql_fetch(
        "select id, start_time, fee_1, fee_2, fee_3, fee_air, price, seat, status
           from v2_pkgTour
          where pid = '{$legacyProductId}'
            and id = '{$scheduleId}'
            and (del_time = 0 or del_time is null)
            and (is_view = 'Y' or is_view = '1')"
    );

    if (!$row || empty($row['id'])) {
        uno_api_error('VALIDATION_ERROR', '선택한 출발 일정을 찾을 수 없습니다.', 400);
    }

    $statusText = isset($row['status']) ? strtoupper((string) $row['status']) : '';
    if (in_array($statusText, array('Y', 'E', 'CLOSED', 'SOLDOUT'), true) || strpos($statusText, '마감') !== false) {
        uno_api_error('SOLD_OUT', '마감된 출발 일정입니다.', 409);
    }

    return array(
        'feeId' => (int) $row['id'],
        'label' => isset($row['start_time']) ? (string) $row['start_time'] : '',
        'deposit' => uno_api_cart_money(isset($row['fee_1']) ? $row['fee_1'] : 0),
        'localPayment' => uno_api_cart_money(isset($row['fee_2']) ? $row['fee_2'] : 0),
        'extraPayment' => uno_api_cart_money(isset($row['fee_3']) ? $row['fee_3'] : 0),
        'packageTotal' => uno_api_cart_money(isset($row['price']) ? $row['price'] : 0),
        'airfare' => uno_api_cart_money(isset($row['fee_air']) ? $row['fee_air'] : 0),
    );
}

function uno_api_cart_build_lines($payload, $mapping, $productType)
{
    $items = isset($payload['items']) && is_array($payload['items'])
        ? $payload['items']
        : array();

    if (!count($items)) {
        uno_api_error('VALIDATION_ERROR', '예약 인원 정보가 필요합니다.', 400);
    }

    $legacyProductId = (int) $mapping['legacyProductId'];
    $lines = array();

    foreach ($items as $item) {
        $personCount = isset($item['personCount']) ? (int) $item['personCount'] : 0;
        if ($personCount < 1) {
            uno_api_error('VALIDATION_ERROR', '예약 인원이 올바르지 않습니다.', 400);
        }

        if ($productType === 'semi') {
            $scheduleId = isset($item['feeId']) && $item['feeId']
                ? $item['feeId']
                : (isset($payload['legacyPackageScheduleId']) ? $payload['legacyPackageScheduleId'] : null);
            if (!$scheduleId) {
                uno_api_error('VALIDATION_ERROR', '출발 일정 ID가 필요합니다.', 400);
            }
            $option = uno_api_cart_fetch_package_option($legacyProductId, $scheduleId);
        } else {
            $feeId = isset($item['feeId']) && $item['feeId']
                ? $item['feeId']
                : (isset($mapping['legacyFeeOptionId']) ? $mapping['legacyFeeOptionId'] : null);
            if (!$feeId) {
                uno_api_error('VALIDATION_ERROR', '요금 옵션 ID가 필요합니다.', 400);
            }
            $option = uno_api_cart_fetch_daily_option($legacyProductId, $feeId);
        }

        $option['personCount'] = $personCount;
        $lines[] = $option;
    }

    return $lines;
}

function uno_api_cart_sum($lines, $key)
{
    $sum = 0;
    foreach ($lines as $line) {
        $sum += (isset($line[$key]) ? (int) $line[$key] : 0) * (int) $line['personCount'];
    }

    return $sum;
}

function uno_api_cart_insert_row($payload, $mapping, $productType, $lines)
{
    $member = uno_api_cart_member_defaults();
    $legacyProductId = (int) $mapping['legacyProductId'];
    $tourDate = isset($payload['tourDate']) ? trim((string) $payload['tourDate']) : '';
    $tourTime = isset($payload['tourTime']) ? trim((string) $payload['tourTime']) : '';
    $memo = isset($payload['memo']) ? trim((string) $payload['memo']) : '';

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tourDate)) {
        uno_api_error('DATE_REQUIRED', '투어일이 필요합니다.', 400);
    }

    $feeIds = array();
    $memberCounts = array();
    foreach ($lines as $line) {
        $feeIds[] = $line['feeId'];
        $memberCounts[] = $line['personCount'];
    }

    $fields = array(
        'regDate' => time(),
        'mb_id' => $member['mb_id'],
        'mb_name' => $member['mb_name'],
        'mb_email' => $member['mb_email'],
        'mb_kakao' => $member['mb_kakao'],
        'mb_hp' => $member['mb_hp'],
        'tourDay' => $tourDate,
        'tourTime' => $tourTime,
        'pid' => $legacyProductId,
        'event_pid' => 0,
        'membCnt' => uno_api_cart_pipe($memberCounts),
        'fee_id' => uno_api_cart_pipe($feeIds),
        'total_fee1' => uno_api_cart_sum($lines, 'deposit'),
        'total_fee2' => uno_api_cart_sum($lines, 'localPayment'),
        'total_fee3' => uno_api_cart_sum($lines, 'extraPayment'),
        'total_fee4' => uno_api_cart_sum($lines, 'packageTotal'),
        'total_fee_air' => uno_api_cart_sum($lines, 'airfare'),
        'regMemo' => $memo,
        'status' => 'cart',
        'mb_ip' => uno_api_cart_client_ip(),
        'nation' => $productType,
        'isMobile' => 'N',
        'del_time' => 0,
    );

    $sets = array();
    foreach ($fields as $key => $value) {
        if (is_int($value)) {
            $sets[] = "{$key} = {$value}";
        } else {
            $sets[] = "{$key} = '" . uno_api_cart_escape($value) . "'";
        }
    }

    sql_query('insert into tour_reg set ' . implode(', ', $sets));

    return uno_api_cart_insert_id();
}

function uno_api_cart_fetch_rows($memberId)
{
    $memberId = uno_api_reservation_escape($memberId);
    $productTable = uno_api_reservation_table_product();
    $result = sql_query(
        "select r.*,
                p.wr_subject,
                p.ca_name,
                p.wr_reg_result,
                p.is_passport,
                p.is_delivery,
                p.is_roominfo
           from tour_reg r
           left join {$productTable} p on p.wr_id = r.pid
          where r.mb_id = '{$memberId}'
            and r.status = 'cart'
            and (r.del_time = 0 or r.del_time is null or r.del_time < 111)
          order by r.id desc"
    );

    $rows = array();
    while ($row = sql_fetch_array($result)) {
        $rows[] = $row;
    }

    return $rows;
}

function uno_api_cart_response_item($row)
{
    $detail = uno_api_reservation_response_from_row($row);
    $options = array();

    foreach ($detail['options'] as $option) {
        $options[] = array(
            'feeId' => isset($option['feeId']) ? $option['feeId'] : '',
            'personCount' => isset($option['personCount']) ? (int) $option['personCount'] : 0,
            'label' => isset($option['label']) ? $option['label'] : '',
            'deposit' => isset($option['deposit']) ? (int) $option['deposit'] : 0,
            'localPayment' => isset($option['localPayment']) ? (int) $option['localPayment'] : 0,
        );
    }

    return array(
        'rid' => $detail['rid'],
        'productId' => $detail['product']['id'],
        'legacyProductId' => $detail['product']['legacyProductId'],
        'title' => $detail['product']['title'],
        'tourDate' => $detail['tourDate'],
        'options' => $options,
        'totalDeposit' => $detail['totalDeposit'],
        'totalLocalPayment' => $detail['totalLocalPayment'],
    );
}

function uno_api_cart_list_response()
{
    $rows = uno_api_cart_fetch_rows(uno_api_current_member_id());
    $items = array();

    foreach ($rows as $row) {
        $items[] = uno_api_cart_response_item($row);
    }

    uno_api_success(array(
        'items' => $items,
        'count' => count($items),
    ));
}

if (!function_exists('sql_fetch') || !function_exists('sql_query')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB 함수를 찾을 수 없습니다.', 500);
}

if ($unoApiCartMethod === 'GET') {
    uno_api_cart_list_response();
}

$payload = uno_api_cart_read_json();
$productId = isset($payload['productId']) ? trim((string) $payload['productId']) : '';

if ($productId === '') {
    uno_api_error('VALIDATION_ERROR', '상품 ID가 필요합니다.', 400);
}

$mapping = uno_api_product_mapping($productId);
if (!$mapping || empty($mapping['legacyProductId'])) {
    uno_api_error('PRODUCT_NOT_MAPPED', '기존 DB와 연결되지 않은 상품입니다.', 404);
}

$productType = isset($mapping['legacyCategory']) && $mapping['legacyCategory'] === 'semi'
    ? 'semi'
    : 'daily';
$lines = uno_api_cart_build_lines($payload, $mapping, $productType);
$rid = uno_api_cart_insert_row($payload, $mapping, $productType, $lines);

uno_api_success(array(
    'rid' => $rid,
    'status' => 'cart',
), 201);
