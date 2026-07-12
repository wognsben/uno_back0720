<?php
/*
 * auth/login.php
 * React 로그인 화면에서 기존 Gnuboard 회원 계정으로 로그인 세션을 생성하는 API endpoint입니다.
 * mb_id와 mb_password를 검증한 뒤 기존 login_check.php와 같은 ss_mb_id / ss_mb_key 세션 상태를 관리합니다.
 * 화면 출력용 로그인 페이지가 아니라 JSON 응답만 반환해 React 로그인 UI와 기존 회원 DB 사이의 연결 역할만 맡습니다.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';

uno_api_require_method('POST');

function uno_api_login_text($jsonString)
{
    $decoded = json_decode($jsonString);
    return is_string($decoded) ? $decoded : '';
}

function uno_api_login_json_body()
{
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true);

    return is_array($body) ? $body : array();
}

function uno_api_login_escape($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string($value);
    }

    return addslashes($value);
}

function uno_api_find_member_for_login($loginId)
{
    global $g5;

    $member = get_member($loginId);
    if ($member && !empty($member['mb_id'])) {
        return $member;
    }

    if (strpos($loginId, '@') === false || !function_exists('sql_fetch')) {
        return $member;
    }

    $memberTable = isset($g5['member_table']) ? $g5['member_table'] : 'g5_member';
    $escapedLoginId = uno_api_login_escape($loginId);

    return sql_fetch(
        "select *
           from {$memberTable}
          where mb_email = '{$escapedLoginId}'
          limit 1"
    );
}

$body = uno_api_login_json_body();
$mbId = isset($body['mb_id']) ? trim((string) $body['mb_id']) : '';
$mbPassword = isset($body['mb_password']) ? trim((string) $body['mb_password']) : '';

if ($mbId === '' || $mbPassword === '') {
    uno_api_error(
        'VALIDATION_ERROR',
        uno_api_login_text('"\uc774\uba54\uc77c\uacfc \ube44\ubc00\ubc88\ud638\ub97c \uc785\ub825\ud574 \uc8fc\uc138\uc694."'),
        400
    );
}

if (!function_exists('get_member') || !function_exists('check_password') || !function_exists('set_session')) {
    uno_api_error(
        'SERVER_ERROR',
        uno_api_login_text('"\uae30\uc874 \ud68c\uc6d0 \uc778\uc99d \ud568\uc218\ub97c \ucc3e\uc744 \uc218 \uc5c6\uc2b5\ub2c8\ub2e4."'),
        500
    );
}

$mb = uno_api_find_member_for_login($mbId);

if (!$mb || empty($mb['mb_id']) || !check_password($mbPassword, $mb['mb_password'])) {
    uno_api_error(
        'PERMISSION_DENIED',
        uno_api_login_text('"\uc544\uc774\ub514 \ub610\ub294 \ube44\ubc00\ubc88\ud638\uac00 \uc62c\ubc14\ub974\uc9c0 \uc54a\uc2b5\ub2c8\ub2e4."'),
        401
    );
}

if (!empty($mb['mb_intercept_date']) && $mb['mb_intercept_date'] <= date('Ymd')) {
    uno_api_error(
        'PERMISSION_DENIED',
        uno_api_login_text('"\uc811\uadfc\uc774 \uc81c\ud55c\ub41c \ud68c\uc6d0 \uacc4\uc815\uc785\ub2c8\ub2e4."'),
        403
    );
}

if (!empty($mb['mb_leave_date']) && $mb['mb_leave_date'] <= date('Ymd')) {
    uno_api_error(
        'PERMISSION_DENIED',
        uno_api_login_text('"\ud0c8\ud1f4 \ucc98\ub9ac\ub41c \ud68c\uc6d0 \uacc4\uc815\uc785\ub2c8\ub2e4."'),
        403
    );
}

set_session('ss_mb_id', $mb['mb_id']);
set_session(
    'ss_mb_key',
    md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT'])
);

global $member;
$member = get_member($mb['mb_id']);

uno_api_success(array(
    'isLoggedIn' => true,
    'member' => array(
        'id' => isset($member['mb_id']) ? (string) $member['mb_id'] : '',
        'name' => isset($member['mb_name']) ? (string) $member['mb_name'] : '',
        'email' => isset($member['mb_email']) ? (string) $member['mb_email'] : '',
        'phone' => isset($member['mb_hp']) ? (string) $member['mb_hp'] : '',
        'kakaoId' => isset($member['mb_kakao']) ? (string) $member['mb_kakao'] : '',
    ),
));
