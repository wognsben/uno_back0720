<?php
/*
 * Community board API only.
 *
 * IMPORTANT:
 * type=qna maps to the public community board:
 *   /contents/board.php?bo_table=qna
 *
 * This is NOT the product-detail bottom QNA/FAQ section.
 * Product-detail bottom QNA/FAQ is fetched from products/detail.php as `faqs`
 * and rendered under the product detail body image.
 */
require_once dirname(__DIR__) . '/_bootstrap.php';

uno_api_require_method('GET');

function uno_api_community_escape($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    if (function_exists('sql_real_escape_string')) {
        return sql_real_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_api_community_text($value, $maxLength = 0)
{
    $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
    $text = preg_replace("/\r\n|\r|\n/", "\n", $text);
    $text = preg_replace("/[ \t]+/", " ", $text);
    $text = trim($text);

    if ($maxLength > 0) {
        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $maxLength, 'UTF-8');
        }

        return substr($text, 0, $maxLength);
    }

    return $text;
}

function uno_api_community_board_table($boTable)
{
    global $g5;

    $prefix = isset($g5['write_prefix']) && $g5['write_prefix'] !== ''
        ? $g5['write_prefix']
        : 'g5_write_';

    return $prefix . $boTable;
}

function uno_api_community_board_map($type)
{
    $map = array(
        'notice' => 'notice',
        'review' => 'write',
        // Public community Q&A board only. Do not use this for product-detail bottom QNA/FAQ.
        'qna' => 'qna',
    );

    return isset($map[$type]) ? $map[$type] : '';
}

function uno_api_community_post_payload($row, $type, $boTable, $contentLength = 160)
{
    $id = isset($row['wr_id']) ? (int) $row['wr_id'] : 0;
    $subject = uno_api_community_text(isset($row['wr_subject']) ? $row['wr_subject'] : '', 255);
    $contentHtml = isset($row['wr_content']) ? (string) $row['wr_content'] : '';
    $date = isset($row['wr_datetime']) ? substr((string) $row['wr_datetime'], 0, 10) : '';
    $hrefType = $type === 'qna' ? 'inquiry' : $type;

    return array(
        'id' => (string) $id,
        'type' => $hrefType,
        'board' => $boTable,
        'legacyBoardUrl' => '/contents/board.php?bo_table=' . $boTable . '&wr_id=' . $id,
        'title' => $subject,
        'excerpt' => uno_api_community_text($contentHtml, $contentLength),
        'contentHtml' => $contentHtml,
        'contentText' => uno_api_community_text($contentHtml),
        'author' => isset($row['wr_name']) ? uno_api_community_text($row['wr_name'], 80) : '',
        'date' => $date,
        'views' => isset($row['wr_hit']) ? (int) $row['wr_hit'] : 0,
        'href' => '/community/' . $hrefType . '/' . $id,
        'isPinned' => false,
        'isNew' => $date === date('Y-m-d'),
    );
}

if (!function_exists('sql_query') || !function_exists('sql_fetch')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB functions are unavailable.', 500);
}

$type = isset($_GET['type']) ? strtolower(trim((string) $_GET['type'])) : 'notice';
$boTable = uno_api_community_board_map($type);

if ($boTable === '') {
    uno_api_error('VALIDATION_ERROR', 'Unknown community board type.', 400);
}

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = isset($_GET['perPage']) ? (int) $_GET['perPage'] : 10;
$perPage = max(1, min(30, $perPage));
$offset = ($page - 1) * $perPage;

$search = isset($_GET['search']) ? uno_api_community_text($_GET['search'], 120) : '';
$safeSearch = uno_api_community_escape($search);
$table = uno_api_community_board_table($boTable);
$postId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($postId > 0) {
    $row = sql_fetch(
        "select wr_id, wr_subject, wr_content, wr_name, mb_id, wr_datetime, wr_hit
           from {$table}
          where wr_is_comment = 0
            and wr_id = '{$postId}'
          limit 1"
    );

    if (!$row || empty($row['wr_id'])) {
        uno_api_error('PRODUCT_NOT_FOUND', 'Community post was not found.', 404);
    }

    sql_query("update {$table} set wr_hit = wr_hit + 1 where wr_id = '{$postId}'", false);

    uno_api_success(array(
        'type' => $type,
        'board' => $boTable,
        'item' => uno_api_community_post_payload($row, $type, $boTable, 0),
    ));
}

$where = "where wr_is_comment = 0";

if ($search !== '') {
    $where .= " and (wr_subject like '%{$safeSearch}%' or wr_content like '%{$safeSearch}%')";
}

$countRow = sql_fetch("select count(*) as cnt from {$table} {$where}");
$total = isset($countRow['cnt']) ? (int) $countRow['cnt'] : 0;
$totalPages = $total > 0 ? (int) ceil($total / $perPage) : 1;

$result = sql_query(
    "select wr_id, wr_subject, wr_content, wr_name, mb_id, wr_datetime, wr_hit
       from {$table}
       {$where}
      order by wr_num asc, wr_reply asc
      limit {$offset}, {$perPage}",
    false
);

if (!$result) {
    uno_api_error('SERVER_ERROR', 'Community board posts could not be loaded.', 500);
}

$items = array();

for ($i = 0; $row = sql_fetch_array($result); $i++) {
    $items[] = uno_api_community_post_payload($row, $type, $boTable);
}

uno_api_success(array(
    'type' => $type,
    'board' => $boTable,
    'items' => $items,
    'pagination' => array(
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => $totalPages,
    ),
));
