<?php
/*
 * _product_navigation_store.php
 * Stores and reads renewed product navigation settings for the React frontend and legacy admin extensions.
 * It manages the custom DB table, JSON fields for meta/regions/products, ordering, and visibility state.
 * It does not render admin HTML or replace legacy product tables; it only keeps navigation editing separate from product/reservation data.
 */

if (!defined('UNO_API_BOOTSTRAPPED')) {
    http_response_code(500);
    exit;
}

function uno_api_product_navigation_table()
{
    return 'uno_renewal_product_navigation';
}

function uno_api_product_navigation_escape($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string($value);
    }

    return addslashes($value);
}

function uno_api_product_navigation_json_encode($value)
{
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function uno_api_product_navigation_json_decode($value, $fallback = array())
{
    $decoded = json_decode((string) $value, true);
    return is_array($decoded) ? $decoded : $fallback;
}

function uno_api_product_navigation_table_exists()
{
    if (!function_exists('sql_fetch')) {
        return false;
    }

    $table = uno_api_product_navigation_table();
    $escapedTable = uno_api_product_navigation_escape($table);
    $row = sql_fetch("show tables like '{$escapedTable}'");

    return is_array($row) && count($row) > 0;
}

function uno_api_product_navigation_ensure_table()
{
    if (!function_exists('sql_query')) {
        uno_api_error('SERVER_ERROR', 'Gnuboard DB functions are not available.', 500);
    }

    $table = uno_api_product_navigation_table();

    sql_query(
        "create table if not exists {$table} (
            nav_id varchar(80) not null,
            nav_group varchar(20) not null default 'semi',
            country varchar(80) not null default '',
            country_ko varchar(80) not null default '',
            title varchar(160) not null default '',
            subtitle varchar(255) not null default '',
            meta_json text null,
            regions_json text null,
            href varchar(255) not null default '',
            products_json mediumtext null,
            sort_order int not null default 0,
            is_visible tinyint(1) not null default 1,
            created_at datetime not null default '0000-00-00 00:00:00',
            updated_at datetime not null default '0000-00-00 00:00:00',
            primary key (nav_id),
            key idx_nav_group_order (nav_group, sort_order),
            key idx_visible_order (is_visible, sort_order)
        ) default charset=utf8mb4"
    );
}

function uno_api_product_navigation_group_meta($groupId)
{
    if ($groupId === 'daily') {
        return array(
            'id' => 'daily',
            'title' => 'DAILY TOUR',
            'eyebrow' => 'LOCAL DAILY COLLECTION',
            'items' => array(),
        );
    }

    return array(
        'id' => 'semi',
        'title' => 'SEMI PACKAGE',
        'eyebrow' => 'PREMIUM ROUTE COLLECTION',
        'items' => array(),
    );
}

function uno_api_product_navigation_default_text($json)
{
    return json_decode($json);
}

function uno_api_product_navigation_default_country_map()
{
    return array(
        'semi-italy' => array(
            'id' => 'semi-italy',
            'category' => 'semi',
            'country' => 'ITALY',
            'countryKo' => uno_api_product_navigation_default_text('"\uc774\ud0c8\ub9ac\uc544"'),
            'title' => 'SEMI PACKAGE · ITALY',
            'subtitle' => uno_api_product_navigation_default_text('"\ub85c\ub9c8 · \ubd81\ubd80 · \uc2dc\uce60\ub9ac\uc544 · \ub3cc\ub85c\ubbf8\ud2f0"'),
            'meta' => array('EST.2011', 'ITALY', 'SEMI PACKAGE', 'MEDITERRANEAN'),
            'regions' => array(
                uno_api_product_navigation_default_text('"\ub85c\ub9c8"'),
                uno_api_product_navigation_default_text('"\ubd81\ubd80"'),
                uno_api_product_navigation_default_text('"\uc2dc\uce60\ub9ac\uc544"'),
                uno_api_product_navigation_default_text('"\ub3cc\ub85c\ubbf8\ud2f0"'),
            ),
            'href' => '/product/semi/italy?view=gallery',
            'products' => array(),
        ),
        'semi-spain' => array(
            'id' => 'semi-spain',
            'category' => 'semi',
            'country' => 'SPAIN',
            'countryKo' => uno_api_product_navigation_default_text('"\uc2a4\ud398\uc778"'),
            'title' => 'SEMI PACKAGE · SPAIN',
            'subtitle' => uno_api_product_navigation_default_text('"\ubc14\ub974\uc140\ub85c\ub098 · \uc548\ub2ec\ub8e8\uc2dc\uc544"'),
            'meta' => array('EST.2011', 'SPAIN', 'SEMI PACKAGE', 'CURATED ROUTE'),
            'regions' => array(
                uno_api_product_navigation_default_text('"\ubc14\ub974\uc140\ub85c\ub098"'),
                uno_api_product_navigation_default_text('"\uc548\ub2ec\ub8e8\uc2dc\uc544"'),
            ),
            'href' => '/product/semi/spain?view=gallery',
            'products' => array(),
        ),
        'semi-portugal' => array(
            'id' => 'semi-portugal',
            'category' => 'semi',
            'country' => 'PORTUGAL',
            'countryKo' => uno_api_product_navigation_default_text('"\ud3ec\ub974\ud22c\uac08"'),
            'title' => 'SEMI PACKAGE · PORTUGAL',
            'subtitle' => uno_api_product_navigation_default_text('"\ub9ac\uc2a4\ubcf8 · \ud3ec\ub974\ud22c"'),
            'meta' => array('EST.2011', 'PORTUGAL', 'SEMI PACKAGE', 'ATLANTIC ROUTE'),
            'regions' => array(
                uno_api_product_navigation_default_text('"\ub9ac\uc2a4\ubcf8"'),
                uno_api_product_navigation_default_text('"\ud3ec\ub974\ud22c"'),
            ),
            'href' => '/product/semi/portugal?view=gallery',
            'products' => array(),
        ),
        'semi-greece-turkey' => array(
            'id' => 'semi-greece-turkey',
            'category' => 'semi',
            'country' => 'GREECE / TURKEY',
            'countryKo' => uno_api_product_navigation_default_text('"\uadf8\ub9ac\uc2a4 / \ud130\ud0a4"'),
            'title' => 'SEMI PACKAGE · GREECE / TURKEY',
            'subtitle' => uno_api_product_navigation_default_text('"\uc0b0\ud1a0\ub9ac\ub2c8 · \uc774\uc2a4\ud0c4\ubd88"'),
            'meta' => array('EST.2011', 'GREECE', 'TURKEY', 'SEMI PACKAGE'),
            'regions' => array(
                uno_api_product_navigation_default_text('"\uc0b0\ud1a0\ub9ac\ub2c8"'),
                uno_api_product_navigation_default_text('"\uc544\ud14c\ub124"'),
                uno_api_product_navigation_default_text('"\uc774\uc2a4\ud0c4\ubd88"'),
            ),
            'href' => '/product/semi/greece-turkey?view=gallery',
            'products' => array(),
        ),
        'semi-egypt' => array(
            'id' => 'semi-egypt',
            'category' => 'semi',
            'country' => 'EGYPT',
            'countryKo' => uno_api_product_navigation_default_text('"\uc774\uc9d1\ud2b8"'),
            'title' => 'SEMI PACKAGE · EGYPT',
            'subtitle' => uno_api_product_navigation_default_text('"\uce74\uc774\ub85c · \ub8e9\uc18c\ub974"'),
            'meta' => array('EST.2011', 'EGYPT', 'SEMI PACKAGE', 'ANCIENT ROUTE'),
            'regions' => array(
                uno_api_product_navigation_default_text('"\uce74\uc774\ub85c"'),
                uno_api_product_navigation_default_text('"\ub8e9\uc18c\ub974"'),
            ),
            'href' => '/product/semi/egypt?view=gallery',
            'products' => array(),
        ),
        'daily-italy' => array(
            'id' => 'daily-italy',
            'category' => 'daily',
            'country' => 'ITALY',
            'countryKo' => uno_api_product_navigation_default_text('"\uc774\ud0c8\ub9ac\uc544"'),
            'title' => 'DAILY TOUR · ITALY',
            'subtitle' => uno_api_product_navigation_default_text('"\ub85c\ub9c8 · \ud53c\ub80c\uccb4 · \ub098\ud3f4\ub9ac · \ubca0\ub124\uce58\uc544"'),
            'meta' => array('EST.2011', 'ITALY', 'DAILY TOUR', 'LOCAL SCENE'),
            'regions' => array(
                uno_api_product_navigation_default_text('"\ub85c\ub9c8"'),
                uno_api_product_navigation_default_text('"\ud53c\ub80c\uccb4"'),
                uno_api_product_navigation_default_text('"\ub098\ud3f4\ub9ac"'),
                uno_api_product_navigation_default_text('"\ubca0\ub124\uce58\uc544"'),
            ),
            'href' => '/product/daily/italy?view=gallery',
            'products' => array(),
        ),
        'daily-france' => array(
            'id' => 'daily-france',
            'category' => 'daily',
            'country' => 'FRANCE',
            'countryKo' => uno_api_product_navigation_default_text('"\ud504\ub791\uc2a4"'),
            'title' => 'DAILY TOUR · FRANCE',
            'subtitle' => uno_api_product_navigation_default_text('"\ud30c\ub9ac · \ubabd\uc0dd\ubbf8\uc178"'),
            'meta' => array('EST.2011', 'FRANCE', 'DAILY TOUR', 'FRENCH ROUTE'),
            'regions' => array(
                uno_api_product_navigation_default_text('"\ud30c\ub9ac"'),
                uno_api_product_navigation_default_text('"\ubabd\uc0dd\ubbf8\uc178"'),
            ),
            'href' => '/product/daily/france?view=gallery',
            'products' => array(),
        ),
    );
}

function uno_api_product_navigation_default_target_for_mapping($mapping)
{
    if (!isset($mapping['legacyCategory']) || $mapping['legacyCategory'] !== 'daily') {
        return 'semi-italy';
    }

    return 'daily-italy';
}

function uno_api_product_navigation_default_product_title($productTable, $legacyProductId)
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

function uno_api_product_navigation_default_placeholder($title, $href)
{
    return array(
        'title' => $title,
        'href' => $href,
    );
}

function uno_api_product_navigation_default_groups()
{
    if (!function_exists('uno_api_product_map') || !function_exists('uno_api_reservation_table_product')) {
        uno_api_error('SERVER_ERROR', 'Product navigation dependencies are not available.', 500);
    }

    $productTable = uno_api_reservation_table_product();
    $countryItems = uno_api_product_navigation_default_country_map();

    foreach (uno_api_product_map() as $productId => $mapping) {
        if (!isset($mapping['legacyProductId'])) {
            continue;
        }

        $targetId = uno_api_product_navigation_default_target_for_mapping($mapping);
        if (!isset($countryItems[$targetId])) {
            continue;
        }

        $productType = isset($mapping['legacyCategory']) && $mapping['legacyCategory'] === 'semi'
            ? 'semi'
            : 'daily';
        $legacyProductId = (int) $mapping['legacyProductId'];
        $title = uno_api_product_navigation_default_product_title($productTable, $legacyProductId);

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
        $countryItems['semi-spain']['products'][] = uno_api_product_navigation_default_placeholder(
            uno_api_product_navigation_default_text('"\uc2a4\ud398\uc778 \uc138\ubbf8\ud328\ud0a4\uc9c0"'),
            '/product/semi/spain?view=gallery'
        );
    }

    if (count($countryItems['semi-portugal']['products']) === 0) {
        $countryItems['semi-portugal']['products'][] = uno_api_product_navigation_default_placeholder(
            uno_api_product_navigation_default_text('"\ud3ec\ub974\ud22c\uac08 \uc138\ubbf8\ud328\ud0a4\uc9c0"'),
            '/product/semi/portugal?view=gallery'
        );
    }

    if (count($countryItems['semi-greece-turkey']['products']) === 0) {
        $countryItems['semi-greece-turkey']['products'][] = uno_api_product_navigation_default_placeholder(
            uno_api_product_navigation_default_text('"\uadf8\ub9ac\uc2a4 · \ud130\ud0a4 \uc138\ubbf8\ud328\ud0a4\uc9c0"'),
            '/product/semi/greece-turkey?view=gallery'
        );
    }

    if (count($countryItems['semi-egypt']['products']) === 0) {
        $countryItems['semi-egypt']['products'][] = uno_api_product_navigation_default_placeholder(
            uno_api_product_navigation_default_text('"\uc774\uc9d1\ud2b8 \uc138\ubbf8\ud328\ud0a4\uc9c0"'),
            '/product/semi/egypt?view=gallery'
        );
    }

    if (count($countryItems['daily-france']['products']) === 0) {
        $countryItems['daily-france']['products'][] = uno_api_product_navigation_default_placeholder(
            uno_api_product_navigation_default_text('"\ud504\ub791\uc2a4 \ub370\uc77c\ub9ac\ud22c\uc5b4"'),
            '/product/daily/france?view=gallery'
        );
    }

    return array(
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
    );
}

function uno_api_product_navigation_fetch_groups()
{
    if (!uno_api_product_navigation_table_exists() || !function_exists('sql_query')) {
        return null;
    }

    $table = uno_api_product_navigation_table();
    $result = sql_query(
        "select *
           from {$table}
          where is_visible = 1
          order by field(nav_group, 'semi', 'daily'), sort_order asc, nav_id asc"
    );

    if (!$result) {
        return null;
    }

    $groups = array(
        'semi' => uno_api_product_navigation_group_meta('semi'),
        'daily' => uno_api_product_navigation_group_meta('daily'),
    );

    while ($row = sql_fetch_array($result)) {
        $groupId = isset($row['nav_group']) && $row['nav_group'] === 'daily'
            ? 'daily'
            : 'semi';

        $groups[$groupId]['items'][] = array(
            'id' => isset($row['nav_id']) ? (string) $row['nav_id'] : '',
            'category' => $groupId,
            'country' => isset($row['country']) ? (string) $row['country'] : '',
            'countryKo' => isset($row['country_ko']) ? (string) $row['country_ko'] : '',
            'title' => isset($row['title']) ? (string) $row['title'] : '',
            'subtitle' => isset($row['subtitle']) ? (string) $row['subtitle'] : '',
            'meta' => uno_api_product_navigation_json_decode(isset($row['meta_json']) ? $row['meta_json'] : '[]'),
            'regions' => uno_api_product_navigation_json_decode(isset($row['regions_json']) ? $row['regions_json'] : '[]'),
            'href' => isset($row['href']) ? (string) $row['href'] : '',
            'products' => uno_api_product_navigation_json_decode(isset($row['products_json']) ? $row['products_json'] : '[]'),
        );
    }

    if (count($groups['semi']['items']) === 0 && count($groups['daily']['items']) === 0) {
        return null;
    }

    return array(
        'groups' => array($groups['semi'], $groups['daily']),
    );
}

function uno_api_product_navigation_text_field($item, $key)
{
    return isset($item[$key]) ? trim((string) $item[$key]) : '';
}

function uno_api_product_navigation_array_field($item, $key)
{
    return isset($item[$key]) && is_array($item[$key]) ? array_values($item[$key]) : array();
}

function uno_api_product_navigation_save_groups($groups)
{
    uno_api_product_navigation_ensure_table();

    $table = uno_api_product_navigation_table();
    sql_query("delete from {$table}");

    $sortOrder = 0;

    foreach ($groups as $group) {
        $groupId = isset($group['id']) && $group['id'] === 'daily' ? 'daily' : 'semi';
        $items = isset($group['items']) && is_array($group['items']) ? $group['items'] : array();

        foreach ($items as $item) {
            $navId = uno_api_product_navigation_text_field($item, 'id');
            if ($navId === '') {
                continue;
            }

            $country = uno_api_product_navigation_text_field($item, 'country');
            $countryKo = uno_api_product_navigation_text_field($item, 'countryKo');
            $title = uno_api_product_navigation_text_field($item, 'title');
            $subtitle = uno_api_product_navigation_text_field($item, 'subtitle');
            $href = uno_api_product_navigation_text_field($item, 'href');
            $metaJson = uno_api_product_navigation_json_encode(uno_api_product_navigation_array_field($item, 'meta'));
            $regionsJson = uno_api_product_navigation_json_encode(uno_api_product_navigation_array_field($item, 'regions'));
            $productsJson = uno_api_product_navigation_json_encode(uno_api_product_navigation_array_field($item, 'products'));
            $isVisible = isset($item['isVisible']) && !$item['isVisible'] ? 0 : 1;

            $sets = array(
                "nav_id = '" . uno_api_product_navigation_escape($navId) . "'",
                "nav_group = '" . uno_api_product_navigation_escape($groupId) . "'",
                "country = '" . uno_api_product_navigation_escape($country) . "'",
                "country_ko = '" . uno_api_product_navigation_escape($countryKo) . "'",
                "title = '" . uno_api_product_navigation_escape($title) . "'",
                "subtitle = '" . uno_api_product_navigation_escape($subtitle) . "'",
                "meta_json = '" . uno_api_product_navigation_escape($metaJson) . "'",
                "regions_json = '" . uno_api_product_navigation_escape($regionsJson) . "'",
                "href = '" . uno_api_product_navigation_escape($href) . "'",
                "products_json = '" . uno_api_product_navigation_escape($productsJson) . "'",
                "sort_order = " . (int) $sortOrder,
                "is_visible = " . (int) $isVisible,
                "created_at = now()",
                "updated_at = now()",
            );

            sql_query('insert into ' . $table . ' set ' . implode(', ', $sets));
            $sortOrder += 10;
        }
    }
}
