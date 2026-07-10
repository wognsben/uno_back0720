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
        'payment' => array(
            'deposit' => $detail['totalDeposit'],
            'localPayment' => $detail['totalLocalPayment'],
            'cardPayRef' => isset($row['card_pay']) && $row['card_pay'] !== '' ? (string) $row['card_pay'] : null,
            'canPayByCard' => (string) $detail['status'] === '2',
        ),
    );
}

$rows = uno_api_my_reservations_fetch_rows(uno_api_current_member_id());
$items = array();

foreach ($rows as $row) {
    $items[] = uno_api_my_reservations_item($row);
}

uno_api_success(array(
    'items' => $items,
));
