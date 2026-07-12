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

function uno_api_reservation_editor_fetch($rid)
{
    $rid = (int) $rid;
    if ($rid <= 0) {
        uno_api_error('VALIDATION_ERROR', '예약번호를 확인해 주세요.', 400);
    }

    $row = sql_fetch(
        "select r.*, p.wr_subject, p.ca_name
           from tour_reg r
           left join g5_write_product p on r.pid = p.wr_id
          where r.id = '{$rid}'
            and r.status not in ('cart', 'booking')
          limit 1"
    );

    if (!$row || empty($row['id'])) {
        uno_api_error('NOT_FOUND', '예약 정보를 찾을 수 없습니다.', 404);
    }

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
        'memo' => array(
            'request' => isset($row['regMemo']) ? (string) $row['regMemo'] : '',
            'admin' => isset($row['adminMemo']) ? (string) $row['adminMemo'] : '',
            'cancel' => isset($row['adminMemoCancel']) ? (string) $row['adminMemoCancel'] : '',
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

    $adminMemo = uno_api_reservation_editor_escape(isset($body['adminMemo']) ? $body['adminMemo'] : '');
    $cancelMemo = uno_api_reservation_editor_escape(isset($body['adminMemoCancel']) ? $body['adminMemoCancel'] : '');

    sql_query(
        "update tour_reg
            set status = '{$status}',
                adminMemo = '{$adminMemo}',
                adminMemoCancel = '{$cancelMemo}'
          where id = '{$rid}'"
    );

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
