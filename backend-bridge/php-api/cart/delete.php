<?php
/*
 * cart/delete.php
 * 기존 tour_reg에 저장된 장바구니 row를 React 장바구니 화면에서 삭제 처리하는 endpoint입니다.
 * status=cart이고 현재 로그인 사용자 소유인 예약만 del_time으로 숨겨 기존 데이터 흐름과 충돌하지 않게 관리합니다.
 * 예약 확정, 결제, 관리자 예약 상태 변경은 담당하지 않고 장바구니 목록 정리 역할만 수행합니다.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_reservation_helpers.php';

uno_api_require_login();

$method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'DELETE';
if (!in_array($method, array('DELETE', 'POST'), true)) {
    uno_api_error('VALIDATION_ERROR', '허용되지 않은 요청 방식입니다.', 405);
}

if (!function_exists('sql_fetch') || !function_exists('sql_query')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB 함수를 찾을 수 없습니다.', 500);
}

$rid = isset($_GET['rid']) ? (int) $_GET['rid'] : 0;
if ($rid < 1) {
    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);
    if (is_array($payload) && isset($payload['rid'])) {
        $rid = (int) $payload['rid'];
    }
}

if ($rid < 1) {
    uno_api_error('VALIDATION_ERROR', '장바구니 ID가 필요합니다.', 400);
}

$memberId = uno_api_reservation_escape(uno_api_current_member_id());
$row = sql_fetch(
    "select id
       from tour_reg
      where id = '{$rid}'
        and mb_id = '{$memberId}'
        and status = 'cart'
        and (del_time = 0 or del_time is null or del_time < 111)"
);

if (!$row || empty($row['id'])) {
    uno_api_error('INVALID_RESERVATION', '삭제할 장바구니 항목을 찾을 수 없습니다.', 404);
}

sql_query(
    "update tour_reg
        set del_time = " . time() . "
      where id = '{$rid}'
        and mb_id = '{$memberId}'
        and status = 'cart'"
);

uno_api_success(array(
    'rid' => $rid,
    'deleted' => true,
));
