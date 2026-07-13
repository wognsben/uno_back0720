<?php
/*
 * admin/products.php
 * Admin-only API that lists legacy UNO Travel products for renewal admin pickers.
 * It reads real product ids and names from the Gnuboard product table and marks whether each product is mapped to a React detail route.
 * This endpoint does not edit products; it only helps admin screens connect navigation/content to existing legacy product data.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_map.php';
require_once dirname(__DIR__) . '/_product_mapping_store.php';
require_once dirname(__DIR__) . '/_reservation_helpers.php';

uno_api_require_method('GET');
uno_api_require_login();
uno_api_require_admin();

if (!function_exists('sql_query')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB functions are not available.', 500);
}

function uno_api_admin_products_text($json)
{
    $decoded = json_decode($json);
    return is_string($decoded) ? $decoded : '';
}

function uno_api_admin_products_type_from_category($category)
{
    $category = (string) $category;
    $semi = uno_api_admin_products_text('"\uc138\ubbf8"');
    $package = uno_api_admin_products_text('"\ud328\ud0a4\uc9c0"');

    if (strpos($category, $semi) !== false || strpos($category, $package) !== false) {
        return 'semi';
    }

    return 'daily';
}

function uno_api_admin_products_mapped_fee_id($productId)
{
    $mapping = uno_api_product_mapping($productId);
    return $mapping && isset($mapping['legacyFeeOptionId']) ? $mapping['legacyFeeOptionId'] : null;
}

function uno_api_admin_products_default_mapping_rows()
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

function uno_api_admin_products_mapping_by_legacy()
{
    $rows = uno_api_product_mapping_fetch_rows(false);

    if (!is_array($rows) || count($rows) === 0) {
        $rows = uno_api_admin_products_default_mapping_rows();
    }

    $map = array();

    foreach ($rows as $row) {
        if (!isset($row['legacyProductId'])) {
            continue;
        }

        $map[(string) $row['legacyProductId']] = $row;
    }

    return $map;
}

function uno_api_admin_products_file_table()
{
    global $g5;
    return isset($g5['board_file_table']) ? $g5['board_file_table'] : 'g5_board_file';
}

function uno_api_admin_products_thumbnail_url($legacyProductId)
{
    if (!function_exists('sql_fetch')) {
        return '';
    }

    $legacyProductId = (int) $legacyProductId;
    $fileTable = uno_api_admin_products_file_table();
    $row = sql_fetch(
        "select bf_file
           from {$fileTable}
          where bo_table = 'product'
            and wr_id = '{$legacyProductId}'
            and bf_file <> ''
          order by bf_no asc
          limit 1"
    );

    if (!$row || empty($row['bf_file'])) {
        return '';
    }

    return '/bbs/data/file/product/' . str_replace('%2F', '/', rawurlencode((string) $row['bf_file']));
}

function uno_api_admin_products_file_count($boardTable, $legacyProductId)
{
    if (!function_exists('sql_fetch')) {
        return 0;
    }

    $boardTable = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $boardTable);
    $legacyProductId = (int) $legacyProductId;

    if ($boardTable === '' || $legacyProductId <= 0) {
        return 0;
    }

    $fileTable = uno_api_admin_products_file_table();
    $row = sql_fetch(
        "select count(*) as cnt
           from {$fileTable}
          where bo_table = '{$boardTable}'
            and wr_id = '{$legacyProductId}'
            and bf_file <> ''"
    );

    return $row && isset($row['cnt']) ? (int) $row['cnt'] : 0;
}

$productTable = uno_api_reservation_table_product();
$mappingByLegacy = uno_api_admin_products_mapping_by_legacy();
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 300;

if ($limit < 20) {
    $limit = 20;
}

if ($limit > 1000) {
    $limit = 1000;
}

$result = sql_query(
    "select wr_id, ca_name, wr_subject
       from {$productTable}
      where wr_subject <> ''
      order by wr_id desc
      limit {$limit}"
);

$items = array();

while ($row = sql_fetch_array($result)) {
    $legacyProductId = isset($row['wr_id']) ? (int) $row['wr_id'] : 0;

    if ($legacyProductId <= 0) {
        continue;
    }

    $mapping = isset($mappingByLegacy[(string) $legacyProductId])
        ? $mappingByLegacy[(string) $legacyProductId]
        : null;
    $mappedProductId = $mapping && isset($mapping['productId']) ? (string) $mapping['productId'] : '';
    $productType = $mapping && isset($mapping['productType'])
        ? (string) $mapping['productType']
        : uno_api_admin_products_type_from_category(isset($row['ca_name']) ? $row['ca_name'] : '');
    $href = $mappedProductId !== ''
        ? uno_api_reservation_href_for_product($mappedProductId, $productType)
        : '/contents/tour_view.php?pid=' . $legacyProductId;

    $items[] = array(
        'legacyProductId' => $legacyProductId,
        'productId' => $mappedProductId,
        'isMapped' => $mappedProductId !== '',
        'isActive' => $mapping ? (!isset($mapping['isActive']) || !!$mapping['isActive']) : false,
        'productType' => $productType,
        'title' => isset($row['wr_subject']) ? (string) $row['wr_subject'] : '',
        'category' => isset($row['ca_name']) ? (string) $row['ca_name'] : '',
        'href' => $href,
        'legacyFeeOptionId' => $mapping && isset($mapping['legacyFeeOptionId']) ? $mapping['legacyFeeOptionId'] : null,
        'thumbnailUrl' => uno_api_admin_products_thumbnail_url($legacyProductId),
        'mediaCounts' => array(
            'product' => uno_api_admin_products_file_count('product', $legacyProductId),
            'tourTop' => uno_api_admin_products_file_count('v2_tourTop', $legacyProductId),
            'tourCourse' => uno_api_admin_products_file_count('v2_course', $legacyProductId),
            'tourAd' => uno_api_admin_products_file_count('v2_tourAd', $legacyProductId),
            'tourInfo' => uno_api_admin_products_file_count('v2_tourInfo', $legacyProductId),
        ),
        'legacyMediaLinks' => array(
            'product' => '/admin/write.php?w=u&bo_table=product&wr_id=' . $legacyProductId,
            'tourTop' => '/admin/tourCourse.php?bo_table=v2_tourTop&wr_id=' . $legacyProductId,
            'tourCourse' => '/admin/tourCourse.php?bo_table=v2_course&wr_id=' . $legacyProductId,
            'tourAd' => '/admin/tourCourse.php?bo_table=v2_tourAd&wr_id=' . $legacyProductId,
            'tourInfo' => '/admin/tourCourse.php?bo_table=v2_tourInfo&wr_id=' . $legacyProductId,
        ),
        'renewalEditHref' => '/admin/renewal/product-edit.php?legacyProductId=' . $legacyProductId,
        'legacyEditHref' => '/admin/write.php?w=u&bo_table=product&wr_id=' . $legacyProductId,
    );
}

uno_api_success(array(
    'items' => $items,
));
