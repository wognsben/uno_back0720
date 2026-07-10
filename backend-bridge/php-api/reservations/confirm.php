<?php
/*
 * reservations/confirm.php
 * 예약 동의 후 신청자 정보, 여권 정보, 룸 정보, 배송 정보를 기존 tour_reg row에 최종 저장하는 endpoint입니다.
 * status=booking 초안 row를 상품 기본 예약 상태로 전환해 마이페이지와 기존 관리자 예약 목록에 보이게 만듭니다.
 * 결제 처리나 관리자 상태 변경 UI는 담당하지 않고, 예약 신청 완료에 필요한 최소 저장 상태만 관리합니다.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_map.php';
require_once dirname(__DIR__) . '/_reservation_helpers.php';

uno_api_require_method('POST');
uno_api_require_login();

function uno_api_confirm_read_json()
{
    $rawBody = file_get_contents('php://input');
    $payload = json_decode($rawBody, true);

    if (!is_array($payload)) {
        uno_api_error('VALIDATION_ERROR', '요청 형식이 올바르지 않습니다.', 400);
    }

    return $payload;
}

function uno_api_confirm_string($payload, $key)
{
    return isset($payload[$key]) ? trim((string) $payload[$key]) : '';
}

function uno_api_confirm_applicant($payload)
{
    $applicant = isset($payload['applicant']) && is_array($payload['applicant'])
        ? $payload['applicant']
        : array();

    $name = uno_api_confirm_string($applicant, 'name');
    $phone = uno_api_confirm_string($applicant, 'phone');
    $email = uno_api_confirm_string($applicant, 'email');
    $kakaoId = uno_api_confirm_string($applicant, 'kakaoId');

    if ($name === '' || $phone === '' || $email === '') {
        uno_api_error('VALIDATION_ERROR', '이름, 연락처, 이메일을 입력해 주세요.', 400);
    }

    return array(
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'kakaoId' => $kakaoId,
    );
}

function uno_api_confirm_passport_json($payload, $requiresPassport)
{
    $passports = isset($payload['passports']) && is_array($payload['passports'])
        ? $payload['passports']
        : array();

    if ($requiresPassport && !count($passports)) {
        uno_api_error('VALIDATION_ERROR', '여권 정보를 입력해 주세요.', 400);
    }

    $normalized = array();
    foreach ($passports as $passport) {
        if (!is_array($passport)) {
            continue;
        }

        $normalized[] = array(
            'nameKo' => uno_api_confirm_string($passport, 'nameKo'),
            'nameEn' => uno_api_confirm_string($passport, 'nameEn'),
            'birthDate' => uno_api_confirm_string($passport, 'birthDate'),
            'passportNo' => uno_api_confirm_string($passport, 'passportNo'),
            'passportExpireDate' => uno_api_confirm_string($passport, 'passportExpireDate'),
            'gender' => uno_api_confirm_string($passport, 'gender'),
        );
    }

    return count($normalized)
        ? json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        : '';
}

function uno_api_confirm_delivery($payload)
{
    $delivery = isset($payload['delivery']) && is_array($payload['delivery'])
        ? $payload['delivery']
        : array();

    return array(
        'zip' => uno_api_confirm_string($delivery, 'zip'),
        'addr1' => uno_api_confirm_string($delivery, 'addr1'),
        'addr2' => uno_api_confirm_string($delivery, 'addr2'),
        'addr3' => uno_api_confirm_string($delivery, 'addr3'),
        'gift' => uno_api_confirm_string($delivery, 'gift'),
    );
}

function uno_api_confirm_update_row($rid, $fields)
{
    if (!function_exists('sql_query')) {
        uno_api_error('SERVER_ERROR', 'Gnuboard DB 함수를 찾을 수 없습니다.', 500);
    }

    $sets = array();
    foreach ($fields as $key => $value) {
        if (is_int($value)) {
            $sets[] = "{$key} = {$value}";
        } else {
            $sets[] = "{$key} = '" . uno_api_reservation_escape($value) . "'";
        }
    }

    sql_query('update tour_reg set ' . implode(', ', $sets) . ' where id = ' . (int) $rid);
}

$rid = isset($_GET['rid']) ? (int) $_GET['rid'] : 0;

if ($rid < 1) {
    uno_api_error('VALIDATION_ERROR', '예약 ID가 필요합니다.', 400);
}

$payload = uno_api_confirm_read_json();
if (empty($payload['agreeRefundPolicy'])) {
    uno_api_error('VALIDATION_ERROR', '투어 환불/취소 규정 동의가 필요합니다.', 400);
}

$row = uno_api_reservation_fetch_row($rid, uno_api_current_member_id());
if ((string) $row['status'] !== 'booking') {
    uno_api_error('INVALID_RESERVATION', '예약 입력 중 상태에서만 신청을 완료할 수 있습니다.', 409);
}

$requiresPassport = uno_api_reservation_bool(isset($row['is_passport']) ? $row['is_passport'] : '');
$requiresRoomInfo = uno_api_reservation_bool(isset($row['is_roominfo']) ? $row['is_roominfo'] : '');
$applicant = uno_api_confirm_applicant($payload);
$roomInfo = uno_api_confirm_string($payload, 'roomInfo');

if ($requiresRoomInfo && $roomInfo === '') {
    uno_api_error('VALIDATION_ERROR', '룸 정보를 입력해 주세요.', 400);
}

$delivery = uno_api_confirm_delivery($payload);
$finalStatus = uno_api_reservation_default_final_status($row);
$fields = array(
    'mb_name' => $applicant['name'],
    'mb_hp' => $applicant['phone'],
    'mb_email' => $applicant['email'],
    'mb_kakao' => $applicant['kakaoId'],
    'regMemo' => uno_api_confirm_string($payload, 'memo'),
    'mb_passport_info' => uno_api_confirm_passport_json($payload, $requiresPassport),
    'roominfo' => $roomInfo,
    'zip' => $delivery['zip'],
    'addr1' => $delivery['addr1'],
    'addr2' => $delivery['addr2'],
    'addr3' => $delivery['addr3'],
    'gift' => $delivery['gift'],
    'status' => $finalStatus,
);

uno_api_confirm_update_row($rid, $fields);

uno_api_success(array(
    'rid' => $rid,
    'status' => $finalStatus,
    'statusLabel' => uno_api_reservation_status_label($finalStatus),
    'nextUrl' => '/reservation/complete?rid=' . rawurlencode((string) $rid),
));
