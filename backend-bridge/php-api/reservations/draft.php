<?php
/*
 * reservations/draft.php
 * React 예약하기 CTA가 기존 우노트래블 tour_reg 테이블에 예약 초안 row를 생성하는 API endpoint입니다.
 * cart 저장과 같은 검증 원칙을 쓰되 status=booking으로 저장하고, 성공 시 예약 입력 페이지 URL을 반환합니다.
 * 예약 확정이나 결제 처리는 담당하지 않고, 예약 페이지 진입 전 서버 기준 초안 생성만 담당합니다.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_map.php';

uno_api_require_method('POST');
uno_api_require_login();

function uno_api_draft_read_json()
{
    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);

    if (!is_array($payload)) {
        uno_api_error('VALIDATION_ERROR', '요청 형식이 올바르지 않습니다.', 400);
    }

    return $payload;
}

function uno_api_draft_escape($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string($value);
    }

    if (function_exists('sql_real_escape_string')) {
        return sql_real_escape_string($value);
    }

    return addslashes($value);
}

function uno_api_draft_money($value)
{
    return (int) preg_replace('/[^0-9-]/', '', (string) $value);
}

function uno_api_draft_pipe($values)
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

function uno_api_draft_insert_id()
{
    if (function_exists('sql_insert_id')) {
        return sql_insert_id();
    }

    if (function_exists('mysql_insert_id')) {
        return mysql_insert_id();
    }

    return 0;
}

function uno_api_draft_member_defaults()
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

function uno_api_draft_client_ip()
{
    return isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
}

function uno_api_draft_normalize_tour_time($value)
{
    $value = trim((string) $value);

    if ($value === '') {
        return '';
    }

    if (preg_match('/\b([01]\d|2[0-3]):[0-5]\d\b/', $value, $matches)) {
        return $matches[0];
    }

    uno_api_error('INVALID_TOUR_TIME', '투어 시간이 올바르지 않습니다.', 400);
}

function uno_api_draft_fetch_daily_option($legacyProductId, $feeId)
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
        'deposit' => uno_api_draft_money(isset($row['fee1']) ? $row['fee1'] : 0),
        'localPayment' => uno_api_draft_money(isset($row['fee2']) ? $row['fee2'] : 0),
        'extraPayment' => uno_api_draft_money(isset($row['fee3']) ? $row['fee3'] : 0),
        'packageTotal' => 0,
        'airfare' => 0,
    );
}

function uno_api_draft_fetch_package_option($legacyProductId, $scheduleId)
{
    $legacyProductId = (int) $legacyProductId;
    $scheduleId = (int) $scheduleId;
    $row = sql_fetch(
        "select id, fee_1, fee_2, fee_3, fee_air, price, status
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
    if (in_array($statusText, array('CLOSED', 'SOLDOUT'), true) || strpos($statusText, '마감') !== false) {
        uno_api_error('SOLD_OUT', '마감된 출발 일정입니다.', 409);
    }

    return array(
        'feeId' => (int) $row['id'],
        'deposit' => uno_api_draft_money(isset($row['fee_1']) ? $row['fee_1'] : 0),
        'localPayment' => uno_api_draft_money(isset($row['fee_2']) ? $row['fee_2'] : 0),
        'extraPayment' => uno_api_draft_money(isset($row['fee_3']) ? $row['fee_3'] : 0),
        'packageTotal' => uno_api_draft_money(isset($row['price']) ? $row['price'] : 0),
        'airfare' => uno_api_draft_money(isset($row['fee_air']) ? $row['fee_air'] : 0),
    );
}

function uno_api_draft_build_lines($payload, $mapping, $productType)
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
            $scheduleId = isset($item['legacyPackageScheduleId']) && $item['legacyPackageScheduleId']
                ? $item['legacyPackageScheduleId']
                : (isset($payload['legacyPackageScheduleId']) ? $payload['legacyPackageScheduleId'] : null);
            if (!$scheduleId) {
                uno_api_error('VALIDATION_ERROR', '출발 일정 ID가 필요합니다.', 400);
            }
            $option = uno_api_draft_fetch_package_option($legacyProductId, $scheduleId);
        } else {
            $feeId = isset($item['feeId']) && $item['feeId']
                ? $item['feeId']
                : (isset($mapping['legacyFeeOptionId']) ? $mapping['legacyFeeOptionId'] : null);
            if (!$feeId) {
                uno_api_error('VALIDATION_ERROR', '요금 옵션 ID가 필요합니다.', 400);
            }
            $option = uno_api_draft_fetch_daily_option($legacyProductId, $feeId);
        }

        $option['personCount'] = $personCount;
        $lines[] = $option;
    }

    return $lines;
}

function uno_api_draft_sum($lines, $key)
{
    $sum = 0;
    foreach ($lines as $line) {
        $sum += (isset($line[$key]) ? (int) $line[$key] : 0) * (int) $line['personCount'];
    }

    return $sum;
}

function uno_api_draft_insert_row($payload, $mapping, $productType, $lines)
{
    $member = uno_api_draft_member_defaults();
    $applicant = isset($payload['applicant']) && is_array($payload['applicant'])
        ? $payload['applicant']
        : array();
    $legacyProductId = (int) $mapping['legacyProductId'];
    $tourDate = isset($payload['tourDate']) ? trim((string) $payload['tourDate']) : '';
    $tourTime = uno_api_draft_normalize_tour_time(isset($payload['tourTime']) ? $payload['tourTime'] : '');
    $memo = isset($payload['memo']) ? trim((string) $payload['memo']) : '';
    $adminMemo = isset($payload['adminMemo']) ? trim((string) $payload['adminMemo']) : '';
    $roomInfo = isset($payload['roomInfo']) ? trim((string) $payload['roomInfo']) : '';
    $applicantName = isset($applicant['name']) && trim((string) $applicant['name']) !== '' ? trim((string) $applicant['name']) : $member['mb_name'];
    $applicantEmail = isset($applicant['email']) && trim((string) $applicant['email']) !== '' ? trim((string) $applicant['email']) : $member['mb_email'];
    $applicantPhone = isset($applicant['phone']) && trim((string) $applicant['phone']) !== '' ? trim((string) $applicant['phone']) : $member['mb_hp'];
    $applicantKakao = isset($applicant['kakaoId']) && trim((string) $applicant['kakaoId']) !== '' ? trim((string) $applicant['kakaoId']) : $member['mb_kakao'];

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
        'mb_name' => $applicantName,
        'mb_email' => $applicantEmail,
        'mb_kakao' => $applicantKakao,
        'mb_hp' => $applicantPhone,
        'tourDay' => $tourDate,
        'tourTime' => $tourTime,
        'pid' => $legacyProductId,
        'event_pid' => 0,
        'membCnt' => uno_api_draft_pipe($memberCounts),
        'fee_id' => uno_api_draft_pipe($feeIds),
        'total_fee1' => uno_api_draft_sum($lines, 'deposit'),
        'total_fee2' => uno_api_draft_sum($lines, 'localPayment'),
        'total_fee3' => uno_api_draft_sum($lines, 'extraPayment'),
        'total_fee4' => uno_api_draft_sum($lines, 'packageTotal'),
        'total_fee_air' => uno_api_draft_sum($lines, 'airfare'),
        'regMemo' => $memo,
        'adminMemo' => $adminMemo,
        'adminMemoCancel' => '',
        'roominfo' => $roomInfo,
        'status' => '1',
        'mb_ip' => uno_api_draft_client_ip(),
        'nation' => $productType,
        'isMobile' => 'N',
        'memCancelDate' => 0,
        'adminCancelDate' => 0,
        'del_time' => 0,
    );

    $sets = array();
    foreach ($fields as $key => $value) {
        if (is_int($value)) {
            $sets[] = "{$key} = {$value}";
        } else {
            $sets[] = "{$key} = '" . uno_api_draft_escape($value) . "'";
        }
    }

    sql_query('insert into tour_reg set ' . implode(', ', $sets));

    return uno_api_draft_insert_id();
}

if (!function_exists('sql_fetch') || !function_exists('sql_query')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB 함수를 찾을 수 없습니다.', 500);
}

$payload = uno_api_draft_read_json();
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
$lines = uno_api_draft_build_lines($payload, $mapping, $productType);
$rid = uno_api_draft_insert_row($payload, $mapping, $productType, $lines);

uno_api_success(array(
    'rid' => $rid,
    'status' => '1',
    'nextUrl' => '/reservation?rid=' . rawurlencode((string) $rid),
), 201);
