<?php
/*
 * products/navigation.php
 * Provides the renewed React product navigation data from the legacy product map and Gnuboard product table.
 * It controls the SEMI/DAILY navigation groups, country tabs, linked product titles, and route hrefs used by the frontend.
 * This is not the final admin editor; it is the API boundary that lets the frontend stop relying on hardcoded navigation data.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_map.php';
require_once dirname(__DIR__) . '/_product_navigation_store.php';
require_once dirname(__DIR__) . '/_reservation_helpers.php';

uno_api_require_method('GET');

if (!function_exists('sql_fetch')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB functions are not available.', 500);
}

function uno_api_navigation_text($json)
{
    return json_decode($json);
}

function uno_api_navigation_country_map()
{
    return array(
        'semi-italy' => array(
            'id' => 'semi-italy',
            'category' => 'semi',
            'country' => 'ITALY',
            'countryKo' => uno_api_navigation_text('"\uc774\ud0c8\ub9ac\uc544"'),
            'title' => 'SEMI PACKAGE · ITALY',
            'subtitle' => uno_api_navigation_text('"\ub85c\ub9c8 · \ubd81\ubd80 · \uc2dc\uce60\ub9ac\uc544 · \ub3cc\ub85c\ubbf8\ud2f0"'),
            'meta' => array('EST.2011', 'ITALY', 'SEMI PACKAGE', 'MEDITERRANEAN'),
            'regions' => array(
                uno_api_navigation_text('"\ub85c\ub9c8"'),
                uno_api_navigation_text('"\ubd81\ubd80"'),
                uno_api_navigation_text('"\uc2dc\uce60\ub9ac\uc544"'),
                uno_api_navigation_text('"\ub3cc\ub85c\ubbf8\ud2f0"'),
            ),
            'href' => '/product/semi/italy?view=gallery',
        ),
        'semi-spain' => array(
            'id' => 'semi-spain',
            'category' => 'semi',
            'country' => 'SPAIN',
            'countryKo' => uno_api_navigation_text('"\uc2a4\ud398\uc778"'),
            'title' => 'SEMI PACKAGE · SPAIN',
            'subtitle' => uno_api_navigation_text('"\ubc14\ub974\uc140\ub85c\ub098 · \uc548\ub2ec\ub8e8\uc2dc\uc544"'),
            'meta' => array('EST.2011', 'SPAIN', 'SEMI PACKAGE', 'CURATED ROUTE'),
            'regions' => array(
                uno_api_navigation_text('"\ubc14\ub974\uc140\ub85c\ub098"'),
                uno_api_navigation_text('"\uc548\ub2ec\ub8e8\uc2dc\uc544"'),
            ),
            'href' => '/product/semi/spain?view=gallery',
        ),
        'semi-portugal' => array(
            'id' => 'semi-portugal',
            'category' => 'semi',
            'country' => 'PORTUGAL',
            'countryKo' => uno_api_navigation_text('"\ud3ec\ub974\ud22c\uac08"'),
            'title' => 'SEMI PACKAGE · PORTUGAL',
            'subtitle' => uno_api_navigation_text('"\ub9ac\uc2a4\ubcf8 · \ud3ec\ub974\ud22c"'),
            'meta' => array('EST.2011', 'PORTUGAL', 'SEMI PACKAGE', 'ATLANTIC ROUTE'),
            'regions' => array(
                uno_api_navigation_text('"\ub9ac\uc2a4\ubcf8"'),
                uno_api_navigation_text('"\ud3ec\ub974\ud22c"'),
            ),
            'href' => '/product/semi/portugal?view=gallery',
        ),
        'semi-greece-turkey' => array(
            'id' => 'semi-greece-turkey',
            'category' => 'semi',
            'country' => 'GREECE / TURKEY',
            'countryKo' => uno_api_navigation_text('"\uadf8\ub9ac\uc2a4 / \ud130\ud0a4"'),
            'title' => 'SEMI PACKAGE · GREECE / TURKEY',
            'subtitle' => uno_api_navigation_text('"\uc0b0\ud1a0\ub9ac\ub2c8 · \uc774\uc2a4\ud0c4\ubd88"'),
            'meta' => array('EST.2011', 'GREECE', 'TURKEY', 'SEMI PACKAGE'),
            'regions' => array(
                uno_api_navigation_text('"\uc0b0\ud1a0\ub9ac\ub2c8"'),
                uno_api_navigation_text('"\uc544\ud14c\ub124"'),
                uno_api_navigation_text('"\uc774\uc2a4\ud0c4\ubd88"'),
            ),
            'href' => '/product/semi/greece-turkey?view=gallery',
        ),
        'semi-egypt' => array(
            'id' => 'semi-egypt',
            'category' => 'semi',
            'country' => 'EGYPT',
            'countryKo' => uno_api_navigation_text('"\uc774\uc9d1\ud2b8"'),
            'title' => 'SEMI PACKAGE · EGYPT',
            'subtitle' => uno_api_navigation_text('"\uce74\uc774\ub85c · \ub8e9\uc18c\ub974"'),
            'meta' => array('EST.2011', 'EGYPT', 'SEMI PACKAGE', 'ANCIENT ROUTE'),
            'regions' => array(
                uno_api_navigation_text('"\uce74\uc774\ub85c"'),
                uno_api_navigation_text('"\ub8e9\uc18c\ub974"'),
            ),
            'href' => '/product/semi/egypt?view=gallery',
        ),
        'daily-italy' => array(
            'id' => 'daily-italy',
            'category' => 'daily',
            'country' => 'ITALY',
            'countryKo' => uno_api_navigation_text('"\uc774\ud0c8\ub9ac\uc544"'),
            'title' => 'DAILY TOUR · ITALY',
            'subtitle' => uno_api_navigation_text('"\ub85c\ub9c8 · \ud53c\ub80c\uccb4 · \ub098\ud3f4\ub9ac · \ubca0\ub124\uce58\uc544"'),
            'meta' => array('EST.2011', 'ITALY', 'DAILY TOUR', 'LOCAL SCENE'),
            'regions' => array(
                uno_api_navigation_text('"\ub85c\ub9c8"'),
                uno_api_navigation_text('"\ud53c\ub80c\uccb4"'),
                uno_api_navigation_text('"\ub098\ud3f4\ub9ac"'),
                uno_api_navigation_text('"\ubca0\ub124\uce58\uc544"'),
            ),
            'href' => '/product/daily/italy?view=gallery',
        ),
        'daily-france' => array(
            'id' => 'daily-france',
            'category' => 'daily',
            'country' => 'FRANCE',
            'countryKo' => uno_api_navigation_text('"\ud504\ub791\uc2a4"'),
            'title' => 'DAILY TOUR · FRANCE',
            'subtitle' => uno_api_navigation_text('"\ud30c\ub9ac · \ubabd\uc0dd\ubbf8\uc178"'),
            'meta' => array('EST.2011', 'FRANCE', 'DAILY TOUR', 'FRENCH ROUTE'),
            'regions' => array(
                uno_api_navigation_text('"\ud30c\ub9ac"'),
                uno_api_navigation_text('"\ubabd\uc0dd\ubbf8\uc178"'),
            ),
            'href' => '/product/daily/france?view=gallery',
        ),
    );
}

function uno_api_navigation_target_for_mapping($productId, $mapping)
{
    if (!isset($mapping['legacyCategory']) || $mapping['legacyCategory'] !== 'daily') {
        return 'semi-italy';
    }

    return 'daily-italy';
}

function uno_api_navigation_product_title($productTable, $legacyProductId)
{
    $legacyProductId = (int) $legacyProductId;
    $row = sql_fetch(
        "select wr_subject
           from {$productTable}
          where wr_id = '{$legacyProductId}'"
    );

    if (!$row || !isset($row['wr_subject']) || trim((string) $row['wr_subject']) === '') {
        return '';
    }

    return (string) $row['wr_subject'];
}

function uno_api_navigation_create_placeholder_product($title, $href)
{
    return array(
        'title' => $title,
        'href' => $href,
    );
}

function uno_api_navigation_product_is_active($product, $activeProductMap)
{
    if (isset($product['productId']) && isset($activeProductMap[(string) $product['productId']])) {
        return true;
    }

    if (!isset($product['legacyProductId'])) {
        return false;
    }

    $legacyProductId = (string) $product['legacyProductId'];

    foreach ($activeProductMap as $mapping) {
        if (isset($mapping['legacyProductId']) && (string) $mapping['legacyProductId'] === $legacyProductId) {
            return true;
        }
    }

    return false;
}

function uno_api_navigation_filter_active_products($navigation, $activeProductMap)
{
    if (!isset($navigation['groups']) || !is_array($navigation['groups'])) {
        return $navigation;
    }

    foreach ($navigation['groups'] as $groupIndex => $group) {
        if (!isset($group['items']) || !is_array($group['items'])) {
            continue;
        }

        foreach ($group['items'] as $itemIndex => $item) {
            $products = isset($item['products']) && is_array($item['products'])
                ? $item['products']
                : array();

            $navigation['groups'][$groupIndex]['items'][$itemIndex]['products'] = array_values(array_filter(
                $products,
                function ($product) use ($activeProductMap) {
                    return is_array($product) && uno_api_navigation_product_is_active($product, $activeProductMap);
                }
            ));
        }
    }

    return $navigation;
}

$productTable = uno_api_reservation_table_product();
$countryItems = uno_api_navigation_country_map();
$activeProductMap = uno_api_product_map();

foreach ($countryItems as $countryId => $countryItem) {
    $countryItems[$countryId]['products'] = array();
}

foreach ($activeProductMap as $productId => $mapping) {
    if (!isset($mapping['legacyProductId'])) {
        continue;
    }

    $targetId = uno_api_navigation_target_for_mapping($productId, $mapping);
    if (!isset($countryItems[$targetId])) {
        continue;
    }

    $productType = isset($mapping['legacyCategory']) && $mapping['legacyCategory'] === 'semi'
        ? 'semi'
        : 'daily';
    $legacyProductId = (int) $mapping['legacyProductId'];
    $title = uno_api_navigation_product_title($productTable, $legacyProductId);

    if ($title === '') {
        continue;
    }

    $countryItems[$targetId]['products'][] = array(
        'title' => $title,
        'href' => uno_api_reservation_href_for_product($productId, $productType),
        'productId' => $productId,
        'legacyProductId' => $legacyProductId,
    );
}

if (count($countryItems['semi-spain']['products']) === 0) {
    $countryItems['semi-spain']['products'][] = uno_api_navigation_create_placeholder_product(
        uno_api_navigation_text('"\uc2a4\ud398\uc778 \uc138\ubbf8\ud328\ud0a4\uc9c0"'),
        '/product/semi/spain?view=gallery'
    );
}

if (count($countryItems['semi-portugal']['products']) === 0) {
    $countryItems['semi-portugal']['products'][] = uno_api_navigation_create_placeholder_product(
        uno_api_navigation_text('"\ud3ec\ub974\ud22c\uac08 \uc138\ubbf8\ud328\ud0a4\uc9c0"'),
        '/product/semi/portugal?view=gallery'
    );
}

if (count($countryItems['semi-greece-turkey']['products']) === 0) {
    $countryItems['semi-greece-turkey']['products'][] = uno_api_navigation_create_placeholder_product(
        uno_api_navigation_text('"\uadf8\ub9ac\uc2a4 · \ud130\ud0a4 \uc138\ubbf8\ud328\ud0a4\uc9c0"'),
        '/product/semi/greece-turkey?view=gallery'
    );
}

if (count($countryItems['semi-egypt']['products']) === 0) {
    $countryItems['semi-egypt']['products'][] = uno_api_navigation_create_placeholder_product(
        uno_api_navigation_text('"\uc774\uc9d1\ud2b8 \uc138\ubbf8\ud328\ud0a4\uc9c0"'),
        '/product/semi/egypt?view=gallery'
    );
}

if (count($countryItems['daily-france']['products']) === 0) {
    $countryItems['daily-france']['products'][] = uno_api_navigation_create_placeholder_product(
        uno_api_navigation_text('"\ud504\ub791\uc2a4 \ub370\uc77c\ub9ac\ud22c\uc5b4"'),
        '/product/daily/france?view=gallery'
    );
}

$savedNavigation = uno_api_product_navigation_fetch_groups();

if ($savedNavigation !== null) {
    uno_api_success(uno_api_navigation_filter_active_products($savedNavigation, $activeProductMap));
}

$defaultNavigation = array(
    'groups' => array(
        array(
            'id' => 'semi',
            'title' => 'SEMI PACKAGE',
            'eyebrow' => 'PREMIUM ROUTE COLLECTION',
            'items' => array(
                $countryItems['semi-italy'],
                $countryItems['semi-spain'],
                $countryItems['semi-portugal'],
                $countryItems['semi-greece-turkey'],
                $countryItems['semi-egypt'],
            ),
        ),
        array(
            'id' => 'daily',
            'title' => 'DAILY TOUR',
            'eyebrow' => 'LOCAL DAILY COLLECTION',
            'items' => array(
                $countryItems['daily-italy'],
                $countryItems['daily-france'],
            ),
        ),
    ),
);

uno_api_success(uno_api_navigation_filter_active_products($defaultNavigation, $activeProductMap));
