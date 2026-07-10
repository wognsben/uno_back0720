<?php
/*
 * reservations/detail.php
 * 예약 입력 화면과 예약 완료 화면에서 기존 tour_reg row를 읽어 React가 표시할 예약 상세 JSON으로 돌려주는 endpoint입니다.
 * 신청자 기본값, 상품 요구 상태, 선택 옵션, 예약금/현지지불금 합계 같은 화면 상태를 정리합니다.
 * 예약 저장이나 관리자 목록 처리는 하지 않고, 현재 로그인 사용자의 예약 row를 안전하게 조회하는 역할만 담당합니다.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_map.php';
require_once dirname(__DIR__) . '/_reservation_helpers.php';

uno_api_require_method('GET');
uno_api_require_login();

$rid = isset($_GET['rid']) ? (int) $_GET['rid'] : 0;

if ($rid < 1) {
    uno_api_error('VALIDATION_ERROR', '예약 ID가 필요합니다.', 400);
}

$row = uno_api_reservation_fetch_row($rid, uno_api_current_member_id());
uno_api_success(uno_api_reservation_response_from_row($row));
