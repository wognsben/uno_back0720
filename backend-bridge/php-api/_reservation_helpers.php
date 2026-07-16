<?php
/*
 * _reservation_helpers.php
 * 예약 상세, 예약 확정, 마이페이지 예약 목록 API가 기존 tour_reg 데이터를 같은 방식으로 읽도록 돕는 공통 helper 파일입니다.
 * 상품 플래그, 상태 라벨, fee_id/membCnt 파이프 문자열, 옵션 금액 조합 같은 예약 DB 해석 상태를 관리합니다.
 * endpoint 파일이 DB 화면이나 관리자 UI 역할까지 맡지 않도록, 기존 예약 row를 JSON 응답 형태로 정리하는 보조 역할만 담당합니다.
 */

if (!defined('UNO_API_BOOTSTRAPPED')) {
    http_response_code(500);
    exit;
}

function uno_api_reservation_escape($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string($value);
    }

    if (function_exists('sql_real_escape_string')) {
        return sql_real_escape_string($value);
    }

    return addslashes($value);
}

function uno_api_reservation_table_product()
{
    global $g5;
    return isset($g5['write_prefix']) ? $g5['write_prefix'] . 'product' : 'g5_write_product';
}

function uno_api_reservation_bool($value)
{
    $normalized = strtoupper(trim((string) $value));
    return in_array($normalized, array('1', 'Y', 'YES', 'TRUE'), true);
}

function uno_api_reservation_money($value)
{
    return (int) preg_replace('/[^0-9-]/', '', (string) $value);
}

function uno_api_reservation_pipe_values($value)
{
    $items = explode('|', (string) $value);
    $result = array();

    foreach ($items as $item) {
        $item = trim($item);
        if ($item !== '') {
            $result[] = $item;
        }
    }

    return $result;
}

function uno_api_reservation_status_label($status)
{
    $labels = array(
        'cart' => '장바구니',
        'booking' => '예약 입력 중',
        '1' => '예약대기',
        '2' => '예약확인',
        '3' => '예약확정',
        '9' => '예약취소',
        '91' => '취소요청',
    );

    $key = (string) $status;
    return isset($labels[$key]) ? $labels[$key] : '예약상태 확인';
}

function uno_api_reservation_href_for_product($productId, $productType)
{
    if ($productType === 'daily') {
        return '/product/detail/daily/' . rawurlencode($productId);
    }

    return '/product/detail/' . rawurlencode($productId);
}

function uno_api_reservation_status_label_v2($status)
{
    $labels = array(
        'cart' => '장바구니',
        'booking' => '예약 입력 중',
        '1' => '예약 대기',
        '2' => '예약 확인',
        '11' => '입금 확인',
        '3' => '예약 확정',
        '9' => '예약 취소',
        '91' => '취소 요청',
        '99' => '취소 완료',
    );

    $key = (string) $status;
    return isset($labels[$key]) ? $labels[$key] : '예약상태 확인';
}

function uno_api_reservation_type_from_row($row)
{
    if (isset($row['ca_name']) && strpos((string) $row['ca_name'], '패키지') !== false) {
        return 'semi';
    }

    if (isset($row['nation']) && (string) $row['nation'] === 'semi') {
        return 'semi';
    }

    return 'daily';
}

function uno_api_reservation_fetch_row($rid, $memberId)
{
    if (!function_exists('sql_fetch')) {
        uno_api_error('SERVER_ERROR', 'Gnuboard DB 함수를 찾을 수 없습니다.', 500);
    }

    $rid = (int) $rid;
    $memberId = uno_api_reservation_escape($memberId);
    $productTable = uno_api_reservation_table_product();

    $row = sql_fetch(
        "select r.*,
                p.wr_subject,
                p.ca_name,
                p.wr_reg_result,
                p.is_passport,
                p.is_delivery,
                p.is_roominfo
           from tour_reg r
           left join {$productTable} p on p.wr_id = r.pid
          where r.id = '{$rid}'
            and r.mb_id = '{$memberId}'
            and (r.del_time = 0 or r.del_time is null or r.del_time < 111)"
    );

    if (!$row || empty($row['id'])) {
        uno_api_error('INVALID_RESERVATION', '예약 정보를 찾을 수 없습니다.', 404);
    }

    return $row;
}

function uno_api_reservation_fetch_daily_fee($legacyProductId, $feeId)
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
        return array(
            'feeId' => $feeId,
            'label' => '선택 옵션',
            'deposit' => 0,
            'localPayment' => 0,
            'extraPayment' => 0,
        );
    }

    return array(
        'feeId' => (int) $row['id'],
        'label' => isset($row['fee_subject']) ? (string) $row['fee_subject'] : '',
        'deposit' => uno_api_reservation_money(isset($row['fee1']) ? $row['fee1'] : 0),
        'localPayment' => uno_api_reservation_money(isset($row['fee2']) ? $row['fee2'] : 0),
        'extraPayment' => uno_api_reservation_money(isset($row['fee3']) ? $row['fee3'] : 0),
    );
}

function uno_api_reservation_fetch_package_fee($legacyProductId, $scheduleId)
{
    $legacyProductId = (int) $legacyProductId;
    $scheduleId = (int) $scheduleId;
    $row = sql_fetch(
        "select id, start_time, fee_1, fee_2, fee_3, fee_air, price
           from v2_pkgTour
          where pid = '{$legacyProductId}'
            and id = '{$scheduleId}'"
    );

    if (!$row || empty($row['id'])) {
        return array(
            'feeId' => $scheduleId,
            'label' => '선택 출발일',
            'deposit' => 0,
            'localPayment' => 0,
            'extraPayment' => 0,
            'packageTotal' => 0,
            'airfare' => 0,
        );
    }

    return array(
        'feeId' => (int) $row['id'],
        'label' => isset($row['start_time']) ? (string) $row['start_time'] . ' 출발' : '',
        'deposit' => uno_api_reservation_money(isset($row['fee_1']) ? $row['fee_1'] : 0),
        'localPayment' => uno_api_reservation_money(isset($row['fee_2']) ? $row['fee_2'] : 0),
        'extraPayment' => uno_api_reservation_money(isset($row['fee_3']) ? $row['fee_3'] : 0),
        'packageTotal' => uno_api_reservation_money(isset($row['price']) ? $row['price'] : 0),
        'airfare' => uno_api_reservation_money(isset($row['fee_air']) ? $row['fee_air'] : 0),
    );
}

function uno_api_reservation_package_schedule_from_options($options, $productType)
{
    if ($productType !== 'semi' || !count($options)) {
        return null;
    }

    $first = $options[0];
    $scheduleId = isset($first['feeId']) ? (int) $first['feeId'] : 0;
    if ($scheduleId <= 0) {
        return null;
    }

    $row = sql_fetch(
        "select id, start_time, arrive_time, air
           from v2_pkgTour
          where id = '{$scheduleId}'
          limit 1"
    );

    if (!$row || empty($row['id'])) {
        return array('id' => $scheduleId);
    }

    return array(
        'id' => (int) $row['id'],
        'startDate' => isset($row['start_time']) ? (string) $row['start_time'] : '',
        'endDate' => isset($row['arrive_time']) ? (string) $row['arrive_time'] : '',
        'boardingLabel' => isset($row['air']) ? (string) $row['air'] : '',
    );
}

function uno_api_reservation_options_from_row($row, $productType)
{
    $feeIds = uno_api_reservation_pipe_values(isset($row['fee_id']) ? $row['fee_id'] : '');
    $memberCounts = uno_api_reservation_pipe_values(isset($row['membCnt']) ? $row['membCnt'] : '');
    $options = array();

    foreach ($feeIds as $index => $feeId) {
        $personCount = isset($memberCounts[$index]) ? (int) $memberCounts[$index] : 0;
        if ($personCount < 1) {
            $personCount = 1;
        }

        $option = $productType === 'semi'
            ? uno_api_reservation_fetch_package_fee($row['pid'], $feeId)
            : uno_api_reservation_fetch_daily_fee($row['pid'], $feeId);

        $option['personCount'] = $personCount;
        $options[] = $option;
    }

    return $options;
}

function uno_api_reservation_sum_options($options, $key)
{
    $sum = 0;

    foreach ($options as $option) {
        $sum += (isset($option[$key]) ? (int) $option[$key] : 0) * (int) $option['personCount'];
    }

    return $sum;
}

function uno_api_reservation_default_final_status($row)
{
    if (function_exists('get_booking_status')) {
        $status = get_booking_status((int) $row['pid']);
        if ($status !== null && $status !== '') {
            return (string) $status;
        }
    }

    if (isset($row['wr_reg_result']) && (string) $row['wr_reg_result'] !== '') {
        return (string) $row['wr_reg_result'];
    }

    return '1';
}

function uno_api_reservation_response_from_row($row)
{
    $legacyProductId = isset($row['pid']) ? (int) $row['pid'] : 0;
    $productId = function_exists('uno_api_product_id_from_legacy')
        ? uno_api_product_id_from_legacy($legacyProductId)
        : '';
    $productType = uno_api_reservation_type_from_row($row);
    $options = uno_api_reservation_options_from_row($row, $productType);
    $packageSchedule = uno_api_reservation_package_schedule_from_options($options, $productType);

    return array(
        'rid' => (int) $row['id'],
        'reservationNo' => (string) $row['id'],
        'status' => isset($row['status']) ? (string) $row['status'] : '',
        'statusLabel' => uno_api_reservation_status_label_v2(isset($row['status']) ? $row['status'] : ''),
        'createdAt' => isset($row['regDate']) && $row['regDate'] ? date('Y-m-d H:i:s', (int) $row['regDate']) : '',
        'product' => array(
            'id' => $productId,
            'legacyProductId' => $legacyProductId,
            'productType' => $productType,
            'title' => isset($row['wr_subject']) ? (string) $row['wr_subject'] : '',
            'href' => uno_api_reservation_href_for_product($productId, $productType),
            'requiresPassport' => uno_api_reservation_bool(isset($row['is_passport']) ? $row['is_passport'] : ''),
            'requiresRoomInfo' => uno_api_reservation_bool(isset($row['is_roominfo']) ? $row['is_roominfo'] : ''),
            'requiresDelivery' => uno_api_reservation_bool(isset($row['is_delivery']) ? $row['is_delivery'] : ''),
        ),
        'tourDate' => isset($row['tourDay']) ? (string) $row['tourDay'] : '',
        'tourTime' => isset($row['tourTime']) ? (string) $row['tourTime'] : '',
        'packageSchedule' => $packageSchedule,
        'options' => $options,
        'totalDeposit' => isset($row['total_fee1']) ? (int) $row['total_fee1'] : uno_api_reservation_sum_options($options, 'deposit'),
        'totalLocalPayment' => isset($row['total_fee2']) ? (int) $row['total_fee2'] : uno_api_reservation_sum_options($options, 'localPayment'),
        'totalExtraPayment' => isset($row['total_fee3']) ? (int) $row['total_fee3'] : uno_api_reservation_sum_options($options, 'extraPayment'),
        'totalPackagePrice' => isset($row['total_fee4']) ? (int) $row['total_fee4'] : uno_api_reservation_sum_options($options, 'packageTotal'),
        'totalAirfare' => isset($row['total_fee_air']) ? (int) $row['total_fee_air'] : uno_api_reservation_sum_options($options, 'airfare'),
        'applicantDefaults' => array(
            'name' => isset($row['mb_name']) ? (string) $row['mb_name'] : '',
            'phone' => isset($row['mb_hp']) ? (string) $row['mb_hp'] : '',
            'email' => isset($row['mb_email']) ? (string) $row['mb_email'] : '',
            'kakaoId' => isset($row['mb_kakao']) ? (string) $row['mb_kakao'] : '',
        ),
        'memo' => isset($row['regMemo']) ? (string) $row['regMemo'] : '',
        'roomInfo' => isset($row['roominfo']) ? (string) $row['roominfo'] : '',
    );
}
