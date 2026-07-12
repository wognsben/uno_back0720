<?php
/*
 * admin/product-mapping.php
 * Admin-only API for reading and saving renewal product ID mappings.
 * It connects React product IDs, product types, legacy product IDs, and optional fee option IDs through a small custom table.
 * This endpoint is separate from public product APIs so admin writes remain permission-gated and frontend reads stay stable.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_map.php';
require_once dirname(__DIR__) . '/_product_mapping_store.php';

function uno_api_admin_product_mapping_body()
{
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true);

    return is_array($body) ? $body : array();
}

function uno_api_admin_product_mapping_rows_from_default()
{
    $rows = array();
    $order = 0;

    foreach (uno_api_product_default_map() as $productId => $mapping) {
        $rows[] = array(
            'productId' => $productId,
            'legacyProductId' => isset($mapping['legacyProductId']) ? (int) $mapping['legacyProductId'] : 0,
            'legacyFeeOptionId' => isset($mapping['legacyFeeOptionId']) ? (int) $mapping['legacyFeeOptionId'] : null,
            'productType' => isset($mapping['legacyCategory']) && $mapping['legacyCategory'] === 'semi' ? 'semi' : 'daily',
            'confidence' => isset($mapping['confidence']) ? (string) $mapping['confidence'] : 'default',
            'isActive' => true,
            'sortOrder' => $order,
        );
        $order++;
    }

    return $rows;
}

function uno_api_admin_product_mapping_method()
{
    return isset($_SERVER['REQUEST_METHOD'])
        ? strtoupper((string) $_SERVER['REQUEST_METHOD'])
        : 'GET';
}

uno_api_require_login();
uno_api_require_admin();

$method = uno_api_admin_product_mapping_method();

if ($method === 'GET') {
    uno_api_product_mapping_ensure_table();
    $rows = uno_api_product_mapping_fetch_rows(false);
    $hasSavedMapping = count($rows) > 0;

    uno_api_success(array(
        'tableReady' => true,
        'hasSavedMapping' => $hasSavedMapping,
        'mappings' => $hasSavedMapping ? $rows : uno_api_admin_product_mapping_rows_from_default(),
    ));
}

if ($method === 'POST') {
    $body = uno_api_admin_product_mapping_body();
    $rows = isset($body['mappings']) && is_array($body['mappings'])
        ? $body['mappings']
        : null;

    if ($rows === null) {
        uno_api_error('VALIDATION_ERROR', 'Mappings are required.', 400);
    }

    uno_api_product_mapping_save_rows($rows);

    uno_api_success(array(
        'saved' => true,
        'mappings' => uno_api_product_mapping_fetch_rows(false),
    ));
}

uno_api_error('VALIDATION_ERROR', 'Method not allowed.', 405);
