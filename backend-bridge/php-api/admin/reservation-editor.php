<?php
/*
 * admin/reservation-editor.php
 * Renewal admin API for viewing and lightly editing one legacy UNO Travel reservation.
 * It reads tour_reg with product context and saves status/admin memo/cancel memo only.
 * Alert sending, voucher sending, payment changes, and deep accounting remain in legacy admin flows for safety.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_reservation_helpers.php';

uno_api_require_login();
uno_api_require_admin();

if (!function_exists('sql_fetch') || !function_exists('sql_query')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB functions are not available.', 500);
}

function uno_api_reservation_editor_escape($value)
{
    return uno_api_reservation_escape((string) $value);
}

function uno_api_reservation_editor_json_body()
{
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true);
    return is_array($body) ? $body : array();
}

function uno_api_reservation_editor_status_label($status)
{
    $map = array(
        '1' => '예약 대기',
        '2' => '예약 확인',
        '11' => '입금 확인',
        '3' => '예약 확정',
        '9' => '예약 취소',
        '91' => '취소 요청',
        '99' => '취소 완료',
    );

    return isset($map[(string) $status]) ? $map[(string) $status] : (string) $status;
}

function uno_api_reservation_editor_money($value)
{
    return number_format((int) preg_replace('/[^0-9-]/', '', (string) $value));
}

function uno_api_reservation_editor_date($value)
{
    if ($value === null || $value === '') {
        return '';
    }

    if (is_numeric($value)) {
        return date('Y-m-d H:i', (int) $value);
    }

    return substr((string) $value, 0, 16);
}

function uno_api_reservation_editor_payment($row)
{
    $cardPay = isset($row['card_pay']) ? trim((string) $row['card_pay']) : '';
    $payment = array(
        'method' => $cardPay !== '' ? 'card' : 'bank',
        'methodLabel' => $cardPay !== '' ? '카드' : '은행',
        'approvalNo' => $cardPay,
        'ksnet' => null,
        'canCancelCard' => false,
    );

    if ($cardPay === '') {
        return $payment;
    }

    if (!uno_api_reservation_editor_table_exists('kspay_result')) {
        return $payment;
    }

    $safeCardPay = uno_api_reservation_editor_escape($cardPay);
    $payRow = sql_fetch("select * from kspay_result where ApplNum = '{$safeCardPay}' order by id desc limit 1");
    if (!$payRow || !is_array($payRow)) {
        return $payment;
    }

    $cancelDate = isset($payRow['CancelDate']) ? trim((string) $payRow['CancelDate']) : '';
    $payMethod = isset($payRow['PayMethod']) ? (string) $payRow['PayMethod'] : '신용카드';
    $payment['methodLabel'] = $cancelDate !== '' ? '카드 취소' : '카드';
    $payment['canCancelCard'] = $cancelDate === '' && $payMethod !== '계좌이체' && $payMethod !== '실시간계좌이체';
    $payment['ksnet'] = array(
        'payMethod' => $payMethod,
        'result' => isset($payRow['Result']) ? (string) $payRow['Result'] : '',
        'resultCode' => isset($payRow['ResultCode']) ? (string) $payRow['ResultCode'] : '',
        'orderNumber' => isset($payRow['OrderNumber']) ? (string) $payRow['OrderNumber'] : '',
        'amount' => isset($payRow['TotPrice']) ? (string) $payRow['TotPrice'] : '',
        'amountLabel' => uno_api_reservation_editor_money(isset($payRow['TotPrice']) ? $payRow['TotPrice'] : 0) . '원',
        'approvedAt' => isset($payRow['AppDate']) ? (string) $payRow['AppDate'] : '',
        'approvalNo' => isset($payRow['ApplNum']) ? (string) $payRow['ApplNum'] : $cardPay,
        'cancelPayload' => array(
            'authty' => '1010',
            'trno' => isset($payRow['ApplNum']) ? (string) $payRow['ApplNum'] : $cardPay,
            'canc_amt' => isset($payRow['TotPrice']) ? (string) $payRow['TotPrice'] : '',
            'canc_seq' => '',
            'canc_type' => '0',
        ),
        'appCode' => isset($payRow['AppCode']) ? (string) $payRow['AppCode'] : '',
        'aquCode' => isset($payRow['AquCode']) ? (string) $payRow['AquCode'] : '',
        'message1' => isset($payRow['Meassage1']) ? (string) $payRow['Meassage1'] : '',
        'message2' => isset($payRow['Meassage2']) ? (string) $payRow['Meassage2'] : '',
        'cancelDate' => $cancelDate,
    );

    return $payment;
}

function uno_api_reservation_editor_table_exists($tableName)
{
    $safeTable = uno_api_reservation_editor_escape($tableName);
    $row = sql_fetch("show tables like '{$safeTable}'");
    return is_array($row) && count($row) > 0;
}

function uno_api_reservation_editor_column_exists($tableName, $columnName)
{
    $safeTable = uno_api_reservation_editor_escape($tableName);
    $safeColumn = uno_api_reservation_editor_escape($columnName);
    $row = sql_fetch("show columns from {$safeTable} like '{$safeColumn}'");
    return is_array($row) && count($row) > 0;
}

function uno_api_reservation_editor_fetch($rid)
{
    $rid = (int) $rid;
    if ($rid <= 0) {
        uno_api_error('VALIDATION_ERROR', '예약번호를 확인해 주세요.', 400);
    }

    $hasAdminMemo = uno_api_reservation_editor_column_exists('tour_reg', 'adminMemo');
    $hasAdminMemoCancel = uno_api_reservation_editor_column_exists('tour_reg', 'adminMemoCancel');

    $row = sql_fetch(
        "select r.*, p.wr_subject, p.ca_name
           from tour_reg r
           left join g5_write_product p on r.pid = p.wr_id
          where r.id = '{$rid}'
            and r.status <> 'cart'
          limit 1"
    );

    if (!$row || empty($row['id'])) {
        uno_api_error('NOT_FOUND', '예약 정보를 찾을 수 없습니다.', 404);
    }

    $detail = uno_api_reservation_response_from_row($row);

    return array(
        'id' => (int) $row['id'],
        'createdAt' => uno_api_reservation_editor_date(isset($row['regDate']) ? $row['regDate'] : ''),
        'status' => isset($row['status']) ? (string) $row['status'] : '',
        'statusLabel' => uno_api_reservation_editor_status_label(isset($row['status']) ? $row['status'] : ''),
        'tourDay' => isset($row['tourDay']) ? substr((string) $row['tourDay'], 0, 10) : '',
        'tourTime' => isset($row['tourTime']) ? (string) $row['tourTime'] : '',
        'product' => array(
            'id' => isset($row['pid']) ? (int) $row['pid'] : 0,
            'title' => isset($row['wr_subject']) ? (string) $row['wr_subject'] : '',
            'category' => isset($row['ca_name']) ? (string) $row['ca_name'] : '',
        ),
        'customer' => array(
            'id' => isset($row['mb_id']) ? (string) $row['mb_id'] : '',
            'name' => isset($row['mb_name']) ? (string) $row['mb_name'] : '',
            'email' => isset($row['mb_email']) ? (string) $row['mb_email'] : '',
            'phone' => isset($row['mb_hp']) ? (string) $row['mb_hp'] : '',
            'kakaoId' => isset($row['mb_kakao']) ? (string) $row['mb_kakao'] : '',
        ),
        'price' => array(
            'deposit' => isset($row['total_fee1']) ? (string) $row['total_fee1'] : '0',
            'localPayment' => isset($row['total_fee2']) ? (string) $row['total_fee2'] : '0',
            'extraPayment' => isset($row['total_fee3']) ? (string) $row['total_fee3'] : '0',
            'depositLabel' => uno_api_reservation_editor_money(isset($row['total_fee1']) ? $row['total_fee1'] : 0) . '원',
        ),
        'options' => isset($detail['options']) ? $detail['options'] : array(),
        'payment' => uno_api_reservation_editor_payment($row),
        'memo' => array(
            'request' => isset($row['regMemo']) ? (string) $row['regMemo'] : '',
            'admin' => $hasAdminMemo && isset($row['adminMemo']) ? (string) $row['adminMemo'] : '',
            'cancel' => $hasAdminMemoCancel && isset($row['adminMemoCancel']) ? (string) $row['adminMemoCancel'] : '',
        ),
        'links' => array(
            'legacyDetail' => '/admin/booking.php?rid=' . (int) $row['id'],
            'legacyEditPopup' => '/admin/popup/pop_content.php?gubun=booking&rid=' . (int) $row['id'],
            'voucherPreview' => '/voucher.php?rid=' . (int) $row['id'],
            'legacySetReg' => '/admin/include_files/setReg.php',
        ),
    );
}

function uno_api_reservation_editor_save($rid, $body)
{
    $rid = (int) $rid;
    $previous = sql_fetch("select id, pid, tourDay, status from tour_reg where id = '{$rid}' limit 1");
    if (!$previous || empty($previous['id'])) {
        uno_api_error('NOT_FOUND', '예약 정보를 찾을 수 없습니다.', 404);
    }

    $allowedStatuses = array('1' => true, '2' => true, '11' => true, '3' => true, '9' => true, '91' => true, '99' => true);
    $status = isset($body['status']) ? (string) $body['status'] : (string) $previous['status'];
    if (!isset($allowedStatuses[$status])) {
        uno_api_error('VALIDATION_ERROR', '예약 상태값을 확인해 주세요.', 400);
    }

    $updates = array("status = '{$status}'");
    if (uno_api_reservation_editor_column_exists('tour_reg', 'adminMemo')) {
        $adminMemo = uno_api_reservation_editor_escape(isset($body['adminMemo']) ? $body['adminMemo'] : '');
        $updates[] = "adminMemo = '{$adminMemo}'";
    }
    if (uno_api_reservation_editor_column_exists('tour_reg', 'adminMemoCancel')) {
        $cancelMemo = uno_api_reservation_editor_escape(isset($body['adminMemoCancel']) ? $body['adminMemoCancel'] : '');
        $updates[] = "adminMemoCancel = '{$cancelMemo}'";
    }

    sql_query("update tour_reg set " . implode(', ', $updates) . " where id = '{$rid}'");

    if (function_exists('re_cal_max_counter') && isset($previous['pid'], $previous['tourDay'])) {
        re_cal_max_counter((int) $previous['pid'], substr((string) $previous['tourDay'], 0, 10));
    }
}

$rid = isset($_GET['rid']) ? (int) $_GET['rid'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = uno_api_reservation_editor_json_body();
    uno_api_reservation_editor_save($rid, $body);
}

uno_api_success(uno_api_reservation_editor_fetch($rid));
