<?php
/*
 * _response.php
 * UNO Travel PHP API 브리지의 JSON 응답과 HTTP method 검증을 담당하는 공통 파일입니다.
 * 모든 endpoint가 같은 성공/실패 형태를 쓰도록 하며, 화면 출력용 PHP와 API 응답 로직을 분리합니다.
 * DB 접속이나 비즈니스 로직은 담당하지 않고, 응답 포맷과 종료 처리를 담당합니다.
 */

if (!defined('UNO_API_BOOTSTRAPPED')) {
    http_response_code(500);
    exit;
}

function uno_api_send_json($payload, $statusCode = 200)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function uno_api_success($data = array(), $statusCode = 200)
{
    uno_api_send_json(array(
        'ok' => true,
        'data' => $data,
    ), $statusCode);
}

function uno_api_error($code, $message, $statusCode = 400, $details = null)
{
    $error = array(
        'code' => $code,
        'message' => $message,
    );

    if ($details !== null) {
        $error['details'] = $details;
    }

    uno_api_send_json(array(
        'ok' => false,
        'error' => $error,
    ), $statusCode);
}

function uno_api_require_method($method)
{
    $requestMethod = isset($_SERVER['REQUEST_METHOD'])
        ? strtoupper($_SERVER['REQUEST_METHOD'])
        : 'GET';

    if ($requestMethod !== strtoupper($method)) {
        uno_api_error('VALIDATION_ERROR', '허용되지 않은 요청 방식입니다.', 405);
    }
}
