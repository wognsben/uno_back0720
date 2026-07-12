<?php
/*
 * _bootstrap.php
 * 기존 우노트래블 Gnuboard 런타임을 API endpoint에서 안전하게 불러오는 공통 부트스트랩 파일입니다.
 * bbs/common.php 로드, JSON 기본 헤더, CSRF 토큰 발급, 로그인 세션 접근 기준을 관리합니다.
 * endpoint별 DB 로직은 이 파일에 넣지 않고, 인증/응답 공통 준비만 담당합니다.
 */

define('UNO_API_BOOTSTRAPPED', true);

if (ob_get_level() === 0) {
    ob_start();
}

$unoApiRoot = dirname(__DIR__);
$unoCommonPath = $unoApiRoot . '/bbs/common.php';

if (file_exists($unoCommonPath)) {
    require_once $unoCommonPath;
}

require_once __DIR__ . '/_response.php';

function uno_api_member()
{
    global $member;
    return isset($member) && is_array($member) ? $member : array();
}

function uno_api_is_logged_in()
{
    $member = uno_api_member();
    return !empty($member['mb_id']);
}

function uno_api_current_member_id()
{
    $member = uno_api_member();
    return isset($member['mb_id']) ? (string) $member['mb_id'] : '';
}

function uno_api_is_admin()
{
    global $is_admin;

    $member = uno_api_member();
    $memberId = isset($member['mb_id']) ? (string) $member['mb_id'] : '';

    if ($memberId === '') {
        return false;
    }

    if (isset($is_admin) && $is_admin) {
        return true;
    }

    if (function_exists('is_admin') && is_admin($memberId)) {
        return true;
    }

    return isset($member['mb_level']) && (int) $member['mb_level'] >= 10;
}

function uno_api_require_admin()
{
    if (!uno_api_is_admin()) {
        uno_api_error('PERMISSION_DENIED', '관리자 권한이 필요합니다.', 403);
    }
}

function uno_api_require_login()
{
    if (!uno_api_is_logged_in()) {
        uno_api_error('LOGIN_REQUIRED', '예약을 위해서는 로그인이 필요합니다.', 401);
    }
}

function uno_api_csrf_token()
{
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    if (empty($_SESSION['unotravel_csrf_token'])) {
        $_SESSION['unotravel_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['unotravel_csrf_token'];
}

function uno_api_attach_csrf_cookie()
{
    $token = uno_api_csrf_token();
    setcookie('unotravel_csrf_token', $token, array(
        'expires' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => false,
        'samesite' => 'Lax',
    ));
}

function uno_api_verify_csrf()
{
    $method = isset($_SERVER['REQUEST_METHOD'])
        ? strtoupper($_SERVER['REQUEST_METHOD'])
        : 'GET';

    if (in_array($method, array('GET', 'HEAD', 'OPTIONS'), true)) {
        return;
    }

    $expected = uno_api_csrf_token();
    $received = isset($_SERVER['HTTP_X_CSRF_TOKEN'])
        ? (string) $_SERVER['HTTP_X_CSRF_TOKEN']
        : '';

    if (!$received || !hash_equals($expected, $received)) {
        uno_api_error('PERMISSION_DENIED', '요청 보안 토큰이 올바르지 않습니다.', 403);
    }
}

uno_api_attach_csrf_cookie();
uno_api_verify_csrf();
