<?php
/*
 * admin/product-navigation-seed.php
 * Seeds the renewed product navigation admin table with the current DB-backed default navigation data.
 * It is used once before the visual admin editor exists, so saved navigation can be tested through the public frontend API.
 * This endpoint is admin-only and requires an explicit confirm query to avoid accidental overwrites.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_map.php';
require_once dirname(__DIR__) . '/_product_navigation_store.php';
require_once dirname(__DIR__) . '/_reservation_helpers.php';

uno_api_require_method('GET');
uno_api_require_login();
uno_api_require_admin();

$confirm = isset($_GET['confirm']) ? (string) $_GET['confirm'] : '';
$overwrite = isset($_GET['overwrite']) && (string) $_GET['overwrite'] === '1';

if ($confirm !== '1') {
    uno_api_success(array(
        'seeded' => false,
        'message' => 'Seed 실행은 ?confirm=1을 붙여야 합니다.',
        'example' => '/api/admin/product-navigation-seed.php?confirm=1',
    ));
}

$existingNavigation = uno_api_product_navigation_fetch_groups();

if ($existingNavigation !== null && !$overwrite) {
    uno_api_success(array(
        'seeded' => false,
        'alreadySeeded' => true,
        'message' => '이미 저장된 product navigation이 있습니다. 다시 덮어쓰려면 overwrite=1을 추가하세요.',
        'navigation' => $existingNavigation,
    ));
}

$groups = uno_api_product_navigation_default_groups();
uno_api_product_navigation_save_groups($groups);
$savedNavigation = uno_api_product_navigation_fetch_groups();

uno_api_success(array(
    'seeded' => true,
    'overwritten' => $overwrite,
    'navigation' => $savedNavigation,
));
