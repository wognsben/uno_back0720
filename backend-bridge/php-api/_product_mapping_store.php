<?php
/*
 * _product_mapping_store.php
 * Shared storage helper for renewal product ID mappings in the Cafe24 PHP/Gnuboard bridge.
 * It creates, reads, and saves the table that connects React product IDs to legacy UNO Travel product and fee IDs.
 * This file is separate from _product_map.php so admin pages can manage mappings while public APIs stay lightweight.
 */

if (!defined('UNO_API_BOOTSTRAPPED')) {
    http_response_code(500);
    exit;
}

function uno_api_product_mapping_table()
{
    return 'uno_renewal_product_mapping';
}

function uno_api_product_mapping_sql_escape($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_api_product_mapping_ensure_table()
{
    if (!function_exists('sql_query')) {
        return false;
    }

    $table = uno_api_product_mapping_table();
    sql_query(
        "create table if not exists `{$table}` (
            `id` int unsigned not null auto_increment,
            `product_id` varchar(120) not null,
            `legacy_product_id` int unsigned not null,
            `legacy_fee_option_id` int unsigned null,
            `product_type` varchar(20) not null default 'daily',
            `confidence` varchar(40) not null default 'admin',
            `is_active` tinyint unsigned not null default 1,
            `sort_order` int unsigned not null default 0,
            `created_at` datetime not null,
            `updated_at` datetime not null,
            primary key (`id`),
            unique key `product_id` (`product_id`),
            key `legacy_product_id` (`legacy_product_id`),
            key `product_type` (`product_type`),
            key `is_active` (`is_active`)
        ) default charset=utf8",
        false
    );

    return true;
}

function uno_api_product_mapping_normalize_row($row)
{
    $productId = isset($row['productId']) ? trim((string) $row['productId']) : '';
    $legacyProductId = isset($row['legacyProductId']) ? (int) $row['legacyProductId'] : 0;
    $legacyFeeOptionId = isset($row['legacyFeeOptionId']) && $row['legacyFeeOptionId'] !== ''
        ? (int) $row['legacyFeeOptionId']
        : null;
    $productType = isset($row['productType']) && $row['productType'] === 'semi' ? 'semi' : 'daily';
    $confidence = isset($row['confidence']) && $row['confidence'] !== ''
        ? trim((string) $row['confidence'])
        : 'admin';
    $isActive = !isset($row['isActive']) || !!$row['isActive'];
    $sortOrder = isset($row['sortOrder']) ? (int) $row['sortOrder'] : 0;

    if ($productId === '' || $legacyProductId <= 0) {
        return null;
    }

    $productId = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $productId);
    $productId = trim(preg_replace('/-+/', '-', $productId), '-');

    if ($productId === '') {
        return null;
    }

    return array(
        'productId' => $productId,
        'legacyProductId' => $legacyProductId,
        'legacyFeeOptionId' => $legacyFeeOptionId,
        'productType' => $productType,
        'confidence' => $confidence,
        'isActive' => $isActive,
        'sortOrder' => $sortOrder,
    );
}

function uno_api_product_mapping_fetch_rows($activeOnly = true)
{
    if (!function_exists('sql_query')) {
        return array();
    }

    uno_api_product_mapping_ensure_table();

    $table = uno_api_product_mapping_table();
    $where = $activeOnly ? 'where is_active = 1' : '';
    $result = sql_query(
        "select product_id, legacy_product_id, legacy_fee_option_id, product_type, confidence, is_active, sort_order
           from `{$table}`
          {$where}
          order by sort_order asc, id asc"
    );

    $rows = array();

    while ($row = sql_fetch_array($result)) {
        $rows[] = array(
            'productId' => isset($row['product_id']) ? (string) $row['product_id'] : '',
            'legacyProductId' => isset($row['legacy_product_id']) ? (int) $row['legacy_product_id'] : 0,
            'legacyFeeOptionId' => isset($row['legacy_fee_option_id']) && $row['legacy_fee_option_id'] !== null ? (int) $row['legacy_fee_option_id'] : null,
            'productType' => isset($row['product_type']) ? (string) $row['product_type'] : 'daily',
            'confidence' => isset($row['confidence']) ? (string) $row['confidence'] : 'admin',
            'isActive' => isset($row['is_active']) ? ((int) $row['is_active'] === 1) : true,
            'sortOrder' => isset($row['sort_order']) ? (int) $row['sort_order'] : 0,
        );
    }

    return $rows;
}

function uno_api_product_mapping_fetch_map()
{
    $rows = uno_api_product_mapping_fetch_rows(true);
    $map = array();

    foreach ($rows as $row) {
        $entry = array(
            'legacyProductId' => $row['legacyProductId'],
            'legacyCategory' => $row['productType'],
            'confidence' => $row['confidence'],
        );

        if ($row['legacyFeeOptionId'] !== null && $row['legacyFeeOptionId'] > 0) {
            $entry['legacyFeeOptionId'] = $row['legacyFeeOptionId'];
        }

        $map[$row['productId']] = $entry;
    }

    return $map;
}

function uno_api_product_mapping_save_rows($rows)
{
    if (!function_exists('sql_query')) {
        return false;
    }

    uno_api_product_mapping_ensure_table();

    $table = uno_api_product_mapping_table();
    $now = date('Y-m-d H:i:s');
    sql_query("delete from `{$table}`");

    $order = 0;

    foreach ($rows as $row) {
        $normalized = uno_api_product_mapping_normalize_row($row);

        if (!$normalized) {
            continue;
        }

        $productId = uno_api_product_mapping_sql_escape($normalized['productId']);
        $legacyProductId = (int) $normalized['legacyProductId'];
        $legacyFeeOptionId = $normalized['legacyFeeOptionId'] !== null && $normalized['legacyFeeOptionId'] > 0
            ? (string) ((int) $normalized['legacyFeeOptionId'])
            : 'null';
        $productType = uno_api_product_mapping_sql_escape($normalized['productType']);
        $confidence = uno_api_product_mapping_sql_escape($normalized['confidence']);
        $isActive = $normalized['isActive'] ? 1 : 0;
        $sortOrder = isset($normalized['sortOrder']) && $normalized['sortOrder'] > 0 ? (int) $normalized['sortOrder'] : $order;

        sql_query(
            "insert into `{$table}`
             set product_id = '{$productId}',
                 legacy_product_id = '{$legacyProductId}',
                 legacy_fee_option_id = {$legacyFeeOptionId},
                 product_type = '{$productType}',
                 confidence = '{$confidence}',
                 is_active = '{$isActive}',
                 sort_order = '{$sortOrder}',
                 created_at = '{$now}',
                 updated_at = '{$now}'"
        );

        $order++;
    }

    return true;
}
