<?php
/*
 * products/index.php
 * React 상품 목록/예약 보조 흐름에서 기존 우노트래블 상품 매핑 목록을 가볍게 조회하는 endpoint입니다.
 * _product_map.php의 React 상품 ID와 g5_write_product의 제목, 예약 필요 상태, 대표 가격 정보를 묶어 반환합니다.
 * 전체 상품 관리나 관리자 목록 UI가 아니라, API 연결 확인과 프런트 목록 데이터 보조 역할만 담당합니다.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_map.php';
require_once dirname(__DIR__) . '/_reservation_helpers.php';

uno_api_require_method('GET');

function uno_api_products_list_fetch_daily_price($legacyProductId)
{
    $legacyProductId = (int) $legacyProductId;
    $row = sql_fetch(
        "select fee1, fee2
           from tour_fee
          where wr_id = '{$legacyProductId}'
          order by is_first desc, id asc
          limit 1"
    );

    if (!$row) {
        return null;
    }

    return array(
        'deposit' => uno_api_reservation_money(isset($row['fee1']) ? $row['fee1'] : 0),
        'localPayment' => uno_api_reservation_money(isset($row['fee2']) ? $row['fee2'] : 0),
        'localPaymentCurrency' => 'EUR',
    );
}

function uno_api_products_list_fetch_package_price($legacyProductId)
{
    $legacyProductId = (int) $legacyProductId;
    $row = sql_fetch(
        "select fee_1, fee_2
           from v2_pkgTour
          where pid = '{$legacyProductId}'
            and (del_time = 0 or del_time is null)
            and (is_view = 'Y' or is_view = '1')
          order by is_main desc, start_time asc, id asc
          limit 1"
    );

    if (!$row) {
        return null;
    }

    return array(
        'deposit' => uno_api_reservation_money(isset($row['fee_1']) ? $row['fee_1'] : 0),
        'localPayment' => uno_api_reservation_money(isset($row['fee_2']) ? $row['fee_2'] : 0),
        'localPaymentCurrency' => 'KRW',
    );
}

function uno_api_products_list_file_table()
{
    global $g5;
    return isset($g5['board_file_table']) ? $g5['board_file_table'] : 'g5_board_file';
}

function uno_api_products_list_file_url($fileName)
{
    $fileName = trim((string) $fileName);

    if ($fileName === '') {
        return '';
    }

    return '/bbs/data/file/product/' . str_replace('%2F', '/', rawurlencode($fileName));
}

function uno_api_products_list_thumbnail_url($legacyProductId)
{
    if (!function_exists('sql_fetch')) {
        return '';
    }

    $legacyProductId = (int) $legacyProductId;
    $fileTable = uno_api_products_list_file_table();
    $row = sql_fetch(
        "select bf_file
           from {$fileTable}
          where bo_table = 'product'
            and wr_id = '{$legacyProductId}'
            and bf_no = '0'
            and bf_file <> ''
          limit 1"
    );

    if (!$row || empty($row['bf_file'])) {
        return '';
    }

    return uno_api_products_list_file_url($row['bf_file']);
}

function uno_api_products_list_mapping_by_legacy()
{
    $items = array();
    $rows = function_exists('uno_api_product_mapping_fetch_rows')
        ? uno_api_product_mapping_fetch_rows(false)
        : array();

    foreach ($rows as $row) {
        if (empty($row['legacyProductId'])) {
            continue;
        }

        $items[(string) ((int) $row['legacyProductId'])] = $row;
    }

    return $items;
}

function uno_api_products_list_default_product_id($legacyProductId, $productType)
{
    $prefix = $productType === 'semi' ? 'semi' : 'daily';
    return $prefix . '-' . (int) $legacyProductId;
}

function uno_api_products_list_product_type_from_row($row)
{
    $category = isset($row['ca_name']) ? (string) $row['ca_name'] : '';

    if (strpos($category, '세미패키지') !== false || strpos($category, '패키지') !== false) {
        return 'semi';
    }

    if (isset($row['nation']) && (string) $row['nation'] === 'semi') {
        return 'semi';
    }

    return 'daily';
}

function uno_api_products_list_category_condition($category)
{
    $category = trim((string) $category);

    if ($category === '') {
        return '';
    }

    $safe = uno_api_reservation_escape($category);
    return "(ca_name = '{$safe}' or ca_name like '%, {$safe}%' or ca_name like '%{$safe},%')";
}

function uno_api_products_list_type_condition($type)
{
    if ($type === 'daily') {
        return uno_api_products_list_category_condition('데이투어');
    }

    if ($type !== 'semi') {
        return '';
    }

    $categories = array('세미패키지', '유럽', '아프리카-중동', '아시아', '프리미엄일주', '현대미술', '트래킹');
    $conditions = array();

    foreach ($categories as $category) {
        $condition = uno_api_products_list_category_condition($category);
        if ($condition !== '') {
            $conditions[] = $condition;
        }
    }

    return count($conditions) > 0 ? '(' . implode(' or ', $conditions) . ')' : '';
}

if (!function_exists('sql_fetch')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB 함수를 찾을 수 없습니다.', 500);
}

$type = isset($_GET['type']) ? trim((string) $_GET['type']) : '';
$legacyCategory = isset($_GET['legacyCategory']) ? trim((string) $_GET['legacyCategory']) : '';
$productTable = uno_api_reservation_table_product();
$items = array();

foreach (uno_api_product_map() as $productId => $mapping) {
    $productType = isset($mapping['legacyCategory']) && $mapping['legacyCategory'] === 'semi'
        ? 'semi'
        : 'daily';

    if ($type !== '' && $type !== $productType) {
        continue;
    }

    if ($legacyCategory !== '' && (!isset($mapping['legacyCategory']) || $mapping['legacyCategory'] !== $legacyCategory)) {
        continue;
    }

    $legacyProductId = (int) $mapping['legacyProductId'];
    $product = sql_fetch(
        "select wr_id, ca_name, wr_subject, is_passport, is_delivery, is_roominfo
           from {$productTable}
          where wr_id = '{$legacyProductId}'"
    );

    if (!$product || empty($product['wr_id'])) {
        continue;
    }

    $price = $productType === 'semi'
        ? uno_api_products_list_fetch_package_price($legacyProductId)
        : uno_api_products_list_fetch_daily_price($legacyProductId);

    $items[] = array(
        'id' => $productId,
        'legacyProductId' => $legacyProductId,
        'legacyFeeOptionId' => isset($mapping['legacyFeeOptionId']) ? $mapping['legacyFeeOptionId'] : null,
        'productType' => $productType,
        'title' => isset($product['wr_subject']) ? (string) $product['wr_subject'] : '',
        'category' => '',
        'legacyCategory' => isset($product['ca_name']) ? (string) $product['ca_name'] : '',
        'href' => uno_api_reservation_href_for_product($productId, $productType),
        'thumbnailUrl' => uno_api_products_list_thumbnail_url($legacyProductId),
        'price' => $price,
        'requiresPassport' => uno_api_reservation_bool(isset($product['is_passport']) ? $product['is_passport'] : ''),
        'requiresRoomInfo' => uno_api_reservation_bool(isset($product['is_roominfo']) ? $product['is_roominfo'] : ''),
        'requiresDelivery' => uno_api_reservation_bool(isset($product['is_delivery']) ? $product['is_delivery'] : ''),
    );
}

uno_api_success(array(
    'items' => $items,
));
