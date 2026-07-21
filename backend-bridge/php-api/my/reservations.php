<?php
/*
 * my/reservations.php
 * 로그인 사용자의 기존 tour_reg 예약 목록을 React 마이페이지에서 읽을 수 있는 JSON으로 돌려주는 endpoint입니다.
 * cart/booking 초안은 제외하고 예약대기, 예약확인, 예약확정, 취소 관련 상태와 결제 표시용 최소 상태를 정리합니다.
 * 관리자 예약 관리나 결제 처리 역할은 하지 않고, 기존 my_reser.php의 목록 조회 의미만 API로 분리합니다.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_map.php';
require_once dirname(__DIR__) . '/_reservation_helpers.php';

uno_api_require_method('GET');
uno_api_require_login();

$currentMemberId = uno_api_current_member_id();
if ($currentMemberId === '') {
    uno_api_error('LOGIN_REQUIRED', 'Login is required to view reservations.', 401);
}

function uno_api_my_reservations_fetch_rows($memberId)
{
    if (!function_exists('sql_query')) {
        uno_api_error('SERVER_ERROR', 'Gnuboard DB 함수를 찾을 수 없습니다.', 500);
    }

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
            and r.status not in ('cart', 'booking')
            and (r.del_time = 0 or r.del_time is null or r.del_time < 111)
          order by r.id desc
          limit 50"
    );

    $rows = array();
    while ($row = sql_fetch_array($result)) {
        $rows[] = $row;
    }

    return $rows;
}

function uno_api_my_reservations_money($value)
{
    return (int) preg_replace('/[^0-9-]/', '', (string) $value);
}

function uno_api_my_reservations_is_event_child($row)
{
    $parentId = isset($row['parent_id']) ? (int) $row['parent_id'] : 0;
    $isEvent = isset($row['isEvent']) ? strtoupper(trim((string) $row['isEvent'])) : '';

    return $parentId > 0 || $isEvent === 'Y';
}

function uno_api_my_reservations_is_general_payment($row)
{
    $nation = isset($row['nation']) ? (string) $row['nation'] : '';
    $category = isset($row['ca_name']) ? (string) $row['ca_name'] : '';
    $nationType = strtolower(trim($nation));
    $categoryType = strtolower(trim($category));

    if ($nationType === 'semi' || $nationType === 'package' || $categoryType === 'semi' || $categoryType === 'package') {
        return false;
    }

    if (strpos($nationType, 'semi') !== false || strpos($nationType, 'package') !== false || strpos($categoryType, 'semi') !== false || strpos($categoryType, 'package') !== false) {
        return false;
    }

    if ($nation === '패키지' || $category === '패키지') {
        return false;
    }

    if (stripos($nation, '세미패키지') !== false || stripos($category, '세미패키지') !== false) {
        return false;
    }

    return true;
}

function uno_api_my_reservations_latest_payment($cardPay)
{
    $cardPay = trim((string) $cardPay);
    if ($cardPay === '') {
        return null;
    }

    $cardPay = uno_api_reservation_escape($cardPay);
    $row = sql_fetch(
        "select id, Result, ResultCode, OrderNumber, TotPrice, AppDate, ApplNum, CancelDate
           from kspay_result
          where ApplNum = '{$cardPay}'
          order by id desc
          limit 1"
    );

    if (!$row || empty($row['id'])) {
        return null;
    }

    return $row;
}

function uno_api_my_reservations_is_success_payment($payment)
{
    if (!$payment) {
        return false;
    }

    $result = isset($payment['Result']) ? trim((string) $payment['Result']) : '';
    $resultCode = isset($payment['ResultCode']) ? trim((string) $payment['ResultCode']) : '';
    $cancelDate = isset($payment['CancelDate']) ? trim((string) $payment['CancelDate']) : '';

    return $result === 'O' && $resultCode === '0000' && $cancelDate === '';
}

function uno_api_my_reservations_payment($row)
{
    $totalFee1 = uno_api_my_reservations_money(isset($row['total_fee1']) ? $row['total_fee1'] : 0);
    $totalFee4 = uno_api_my_reservations_money(isset($row['total_fee4']) ? $row['total_fee4'] : 0);
    $amount = $totalFee1 + $totalFee4;
    $cardPay = isset($row['card_pay']) ? trim((string) $row['card_pay']) : '';
    $latestPayment = uno_api_my_reservations_latest_payment($cardPay);
    $isGeneral = uno_api_my_reservations_is_general_payment($row);
    $isEventChild = uno_api_my_reservations_is_event_child($row);
    $status = isset($row['status']) ? (string) $row['status'] : '';

    $paymentStatus = 'unpaid';
    $paymentStatusLabel = '결제 전';
    $cancelDate = null;
    $transactionId = null;

    if ($latestPayment) {
        $transactionId = isset($latestPayment['ApplNum']) ? (string) $latestPayment['ApplNum'] : $cardPay;
        $cancelDateValue = isset($latestPayment['CancelDate']) ? trim((string) $latestPayment['CancelDate']) : '';

        if ($cancelDateValue !== '') {
            $paymentStatus = 'cancelled';
            $paymentStatusLabel = '카드 취소';
            $cancelDate = $cancelDateValue;
        } elseif (uno_api_my_reservations_is_success_payment($latestPayment)) {
            $paymentStatus = 'paid';
            $paymentStatusLabel = '결제 완료';
        }
    }

    $blockedReason = null;
    if (!$isGeneral) {
        $blockedReason = '패키지/세미패키지 결제는 이번 연결 범위에서 제외됩니다.';
    } elseif ($isEventChild) {
        $blockedReason = '이벤트 연결 예약은 직접 결제할 수 없습니다.';
    } elseif ($status !== '2') {
        $blockedReason = '예약확인 상태에서만 카드결제가 가능합니다.';
    } elseif ($totalFee1 <= 0) {
        $blockedReason = '결제할 예약금이 없습니다.';
    } elseif ($amount <= 0) {
        $blockedReason = '결제 금액이 올바르지 않습니다.';
    } elseif ($paymentStatus === 'paid') {
        $blockedReason = '이미 결제 완료된 예약입니다.';
    }

    $canPay = $blockedReason === null;

    return array(
        'deposit' => $totalFee1,
        'localPayment' => isset($row['total_fee2']) ? uno_api_my_reservations_money($row['total_fee2']) : 0,
        'amount' => $amount,
        'cardPayRef' => $cardPay !== '' ? $cardPay : null,
        'transactionId' => $transactionId,
        'hasLedger' => $latestPayment !== null,
        'status' => $paymentStatus,
        'statusLabel' => $paymentStatusLabel,
        'cancelDate' => $cancelDate,
        'canPay' => $canPay,
        'canPayByCard' => $canPay,
        'blockedReason' => $blockedReason,
        'type' => $isGeneral ? 'general' : 'package',
        'isEventChild' => $isEventChild,
    );
}

function uno_api_my_reservations_item($row)
{
    $detail = uno_api_reservation_response_from_row($row);
    $options = array();

    foreach ($detail['options'] as $option) {
        $options[] = array(
            'label' => isset($option['label']) ? $option['label'] : '',
            'personCount' => isset($option['personCount']) ? (int) $option['personCount'] : 0,
        );
    }

    return array(
        'rid' => $detail['rid'],
        'reservationNo' => $detail['reservationNo'],
        'createdAt' => $detail['createdAt'],
        'tourDate' => $detail['tourDate'],
        'status' => $detail['status'],
        'statusLabel' => $detail['statusLabel'],
        'product' => array(
            'id' => $detail['product']['id'],
            'legacyProductId' => $detail['product']['legacyProductId'],
            'title' => $detail['product']['title'],
            'href' => $detail['product']['href'],
        ),
        'options' => $options,
        'payment' => uno_api_my_reservations_payment($row),
    );
}

$rows = uno_api_my_reservations_fetch_rows($currentMemberId);
$items = array();

foreach ($rows as $row) {
    $items[] = uno_api_my_reservations_item($row);
}

uno_api_success(array(
    'items' => $items,
));
