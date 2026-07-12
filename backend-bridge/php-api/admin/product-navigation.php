<?php
/*
 * admin/product-navigation.php
 * Admin-only API for saving and reading renewed product navigation settings.
 * It manages category/country tabs, linked products, ordering, and visibility through the custom navigation table.
 * It is separate from the public products/navigation.php endpoint so frontend reads stay light and admin writes stay permission-gated.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_navigation_store.php';

function uno_api_admin_product_navigation_json_body()
{
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true);

    return is_array($body) ? $body : array();
}

function uno_api_admin_product_navigation_method()
{
    return isset($_SERVER['REQUEST_METHOD'])
        ? strtoupper((string) $_SERVER['REQUEST_METHOD'])
        : 'GET';
}

uno_api_require_login();
uno_api_require_admin();

$method = uno_api_admin_product_navigation_method();

if ($method === 'GET') {
    uno_api_product_navigation_ensure_table();
    $savedNavigation = uno_api_product_navigation_fetch_groups();

    uno_api_success(array(
        'tableReady' => true,
        'hasSavedNavigation' => $savedNavigation !== null,
        'navigation' => $savedNavigation,
    ));
}

if ($method === 'POST') {
    $body = uno_api_admin_product_navigation_json_body();
    $groups = isset($body['groups']) && is_array($body['groups'])
        ? $body['groups']
        : null;

    if ($groups === null) {
        uno_api_error('VALIDATION_ERROR', '저장할 product navigation groups가 필요합니다.', 400);
    }

    uno_api_product_navigation_save_groups($groups);
    $savedNavigation = uno_api_product_navigation_fetch_groups();

    uno_api_success(array(
        'saved' => true,
        'navigation' => $savedNavigation,
    ));
}

uno_api_error('VALIDATION_ERROR', '허용되지 않는 요청 방식입니다.', 405);
