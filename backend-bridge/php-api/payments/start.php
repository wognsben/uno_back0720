<?php
/*
 * payments/start.php
 * React mypage starts only the legacy general-reservation KSNET card flow here.
 * This endpoint validates the logged-in owner and returns a legacy POST target.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_reservation_helpers.php';

uno_api_require_method('POST');
uno_api_require_login();

if (!function_exists('sql_fetch')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB functions are not available.', 500);
}

function uno_api_payment_start_read_json()
{
    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);

    if (!is_array($payload)) {
        uno_api_error('VALIDATION_ERROR', 'Request body is invalid.', 400);
    }

    return $payload;
}

function uno_api_payment_start_money($value)
{
    return (int) preg_replace('/[^0-9-]/', '', (string) $value);
}

function uno_api_payment_start_is_event_child($row)
{
    $parentId = isset($row['parent_id']) ? (int) $row['parent_id'] : 0;
    $isEvent = isset($row['isEvent']) ? strtoupper(trim((string) $row['isEvent'])) : '';

    return $parentId > 0 || $isEvent === 'Y';
}

function uno_api_payment_start_is_general($row)
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

function uno_api_payment_start_latest_payment($cardPay)
{
    $cardPay = trim((string) $cardPay);
    if ($cardPay === '') {
        return null;
    }

    $cardPay = uno_api_reservation_escape($cardPay);
    $row = sql_fetch(
        "select id, Result, ResultCode, ApplNum, CancelDate
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

function uno_api_payment_start_is_success_payment($payment)
{
    if (!$payment) {
        return false;
    }

    $result = isset($payment['Result']) ? trim((string) $payment['Result']) : '';
    $resultCode = isset($payment['ResultCode']) ? trim((string) $payment['ResultCode']) : '';
    $cancelDate = isset($payment['CancelDate']) ? trim((string) $payment['CancelDate']) : '';

    return $result === 'O' && $resultCode === '0000' && $cancelDate === '';
}

$payload = uno_api_payment_start_read_json();
$rid = isset($payload['rid']) ? (int) $payload['rid'] : 0;

if ($rid < 1) {
    uno_api_error('VALIDATION_ERROR', 'Reservation id is required.', 400);
}

$memberId = uno_api_reservation_escape(uno_api_current_member_id());
$productTable = uno_api_reservation_table_product();
$row = sql_fetch(
    "select r.*,
            p.wr_subject,
            p.ca_name
       from tour_reg r
       left join {$productTable} p on p.wr_id = r.pid
      where r.id = '{$rid}'
        and r.mb_id = '{$memberId}'
        and r.status not in ('cart', 'booking')
        and (r.del_time = 0 or r.del_time is null or r.del_time < 111)
      limit 1"
);

if (!$row || empty($row['id'])) {
    uno_api_error('INVALID_RESERVATION', 'Reservation was not found.', 404);
}

if (uno_api_payment_start_is_event_child($row)) {
    uno_api_error('INVALID_RESERVATION', 'Event child reservations cannot be paid directly.', 409);
}

if (!uno_api_payment_start_is_general($row)) {
    uno_api_error('VALIDATION_ERROR', 'Package payments are excluded from this flow.', 409);
}

if ((string) $row['status'] !== '2') {
    uno_api_error('VALIDATION_ERROR', 'Only checked reservations can start card payment.', 409);
}

$totalFee1 = uno_api_payment_start_money(isset($row['total_fee1']) ? $row['total_fee1'] : 0);
$totalFee4 = uno_api_payment_start_money(isset($row['total_fee4']) ? $row['total_fee4'] : 0);
$amount = $totalFee1 + $totalFee4;

if ($totalFee1 <= 0 || $amount <= 0) {
    uno_api_error('VALIDATION_ERROR', 'Payment amount is invalid.', 409);
}

$cardPay = isset($row['card_pay']) ? trim((string) $row['card_pay']) : '';
$latestPayment = uno_api_payment_start_latest_payment($cardPay);
if (uno_api_payment_start_is_success_payment($latestPayment)) {
    uno_api_error('VALIDATION_ERROR', 'This reservation is already paid.', 409);
}

uno_api_success(array(
    'reservation' => array(
        'rid' => (int) $row['id'],
        'productName' => isset($row['wr_subject']) ? (string) $row['wr_subject'] : '',
        'amount' => $amount,
    ),
    'payment' => array(
        'method' => 'POST',
        'action' => '/kspay.php',
        'target' => 'uno_ksnet_pay',
        'fields' => array(
            'sel' => (string) $row['id'],
        ),
    ),
));
