<?php
/*
 * products/detail.php
 * React 상품 상세/예약 모듈이 기존 우노트래블 DB에서 상품 기본 정보와 예약 옵션을 가볍게 조회하는 API endpoint입니다.
 * 기본 mode=reservation에서는 긴 본문을 제외하고 pid, 상품명, 예약 옵션, 여권/룸정보 필요 여부만 반환합니다.
 * 상품 목록 HTML이나 관리자 화면을 만들지 않고, 상세페이지 첫 진입이 무겁지 않도록 작은 JSON 응답만 담당합니다.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_map.php';

uno_api_require_method('GET');

function uno_api_sql_escape($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string($value);
    }

    if (function_exists('sql_real_escape_string')) {
        return sql_real_escape_string($value);
    }

    return addslashes($value);
}

function uno_api_table_product()
{
    global $g5;
    return isset($g5['write_prefix']) ? $g5['write_prefix'] . 'product' : 'g5_write_product';
}

function uno_api_table_board_file()
{
    global $g5;
    return isset($g5['board_file_table']) ? $g5['board_file_table'] : 'g5_board_file';
}

function uno_api_table_guide()
{
    global $g5;
    return isset($g5['write_prefix']) ? $g5['write_prefix'] . 'admGuideInfo' : 'g5_write_admGuideInfo';
}

function uno_api_bool_field($value)
{
    $normalized = strtoupper(trim((string) $value));
    return in_array($normalized, array('1', 'Y', 'YES', 'TRUE'), true);
}

function uno_api_money($value)
{
    return (int) preg_replace('/[^0-9-]/', '', (string) $value);
}

function uno_api_href_for_product($productId, $productType)
{
    if ($productType === 'daily') {
        return '/product/detail/daily/' . rawurlencode($productId);
    }

    return '/product/detail/' . rawurlencode($productId);
}

function uno_api_detail_mapping_fallback($productId)
{
    $productId = trim((string) $productId);

    if (preg_match('/^(semi|daily)-([0-9]+)$/', $productId, $matches)) {
        return array(
            'legacyProductId' => (int) $matches[2],
            'legacyCategory' => $matches[1],
            'confidence' => 'legacy-product-id',
        );
    }

    if (preg_match('/^[0-9]+$/', $productId)) {
        return array(
            'legacyProductId' => (int) $productId,
            'legacyCategory' => 'daily',
            'confidence' => 'legacy-product-id',
        );
    }

    return null;
}

function uno_api_product_file_url($fileName)
{
    $fileName = trim((string) $fileName);

    if ($fileName === '') {
        return '';
    }

    return '/bbs/data/file/product/' . str_replace('%2F', '/', rawurlencode($fileName));
}

function uno_api_board_file_url($boardTable, $fileName)
{
    $boardTable = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $boardTable);
    $fileName = trim((string) $fileName);

    if ($boardTable === '' || $fileName === '') {
        return '';
    }

    return '/bbs/data/file/' . $boardTable . '/' . str_replace('%2F', '/', rawurlencode($fileName));
}

function uno_api_fetch_product_images($legacyProductId)
{
    if (!function_exists('sql_query') || !function_exists('sql_fetch_array')) {
        return array();
    }

    $legacyProductId = (int) $legacyProductId;
    $fileTable = uno_api_table_board_file();
    $result = sql_query(
        "select bf_no, bf_source, bf_file, bf_width, bf_height
           from {$fileTable}
          where bo_table = 'product'
            and wr_id = '{$legacyProductId}'
            and bf_file <> ''
          order by bf_no asc"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $url = uno_api_product_file_url(isset($row['bf_file']) ? $row['bf_file'] : '');
        if ($url === '') {
            continue;
        }

        $items[] = array(
            'no' => isset($row['bf_no']) ? (int) $row['bf_no'] : 0,
            'source' => isset($row['bf_source']) ? (string) $row['bf_source'] : '',
            'url' => $url,
            'width' => isset($row['bf_width']) ? (int) $row['bf_width'] : 0,
            'height' => isset($row['bf_height']) ? (int) $row['bf_height'] : 0,
        );
    }

    return $items;
}

function uno_api_guide_ids($value)
{
    preg_match_all('/\d+/', (string) $value, $matches);
    $ids = array();
    foreach ($matches[0] as $id) {
        $id = (int) $id;
        if ($id > 0 && !in_array($id, $ids, true)) {
            $ids[] = $id;
        }
    }

    return $ids;
}

function uno_api_fetch_product_guides($guideInfo)
{
    if (!function_exists('sql_query') || !function_exists('sql_fetch_array')) {
        return array();
    }

    $ids = uno_api_guide_ids($guideInfo);
    if (count($ids) === 0) {
        return array();
    }

    $guideTable = uno_api_table_guide();
    $fileTable = uno_api_table_board_file();
    $idList = implode(',', array_map('intval', $ids));
    $result = sql_query(
        "select g.wr_id, g.wr_subject, g.wr_content, f.bf_file, f.bf_source
           from {$guideTable} g
           left join {$fileTable} f
             on f.bo_table = 'admGuideInfo'
            and f.wr_id = g.wr_id
            and f.bf_no = 0
          where g.wr_id in ({$idList})
          order by g.wr_subject asc, g.wr_id asc"
    );

    $byId = array();
    while ($row = sql_fetch_array($result)) {
        $id = isset($row['wr_id']) ? (int) $row['wr_id'] : 0;
        if ($id <= 0) {
            continue;
        }

        $content = isset($row['wr_content']) ? stripslashes((string) $row['wr_content']) : '';
        $byId[$id] = array(
            'id' => $id,
            'name' => isset($row['wr_subject']) ? stripslashes((string) $row['wr_subject']) : '',
            'bodyText' => uno_api_text_from_html($content),
            'imageUrl' => isset($row['bf_file']) ? uno_api_board_file_url('admGuideInfo', $row['bf_file']) : '',
            'imageAlt' => isset($row['bf_source']) ? (string) $row['bf_source'] : '',
        );
    }

    $items = array();
    foreach ($ids as $id) {
        if (isset($byId[$id])) {
            $items[] = $byId[$id];
        }
    }

    return $items;
}

function uno_api_fetch_board_images($boardTable, $legacyProductId)
{
    if (!function_exists('sql_query') || !function_exists('sql_fetch_array')) {
        return array();
    }

    $boardTable = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $boardTable);
    if ($boardTable === '') {
        return array();
    }

    $legacyProductId = (int) $legacyProductId;
    $fileTable = uno_api_table_board_file();
    $result = sql_query(
        "select bf_no, bf_source, bf_file, bf_width, bf_height
           from {$fileTable}
          where bo_table = '{$boardTable}'
            and wr_id = '{$legacyProductId}'
            and bf_file <> ''
          order by bf_no asc"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $url = uno_api_product_file_url(isset($row['bf_file']) ? $row['bf_file'] : '');
        if ($url === '') {
            continue;
        }

        $items[] = array(
            'no' => isset($row['bf_no']) ? (int) $row['bf_no'] : 0,
            'source' => isset($row['bf_source']) ? (string) $row['bf_source'] : '',
            'url' => str_replace('/product/', '/' . $boardTable . '/', $url),
            'width' => isset($row['bf_width']) ? (int) $row['bf_width'] : 0,
            'height' => isset($row['bf_height']) ? (int) $row['bf_height'] : 0,
            'board' => $boardTable,
        );
    }

    return $items;
}

function uno_api_fetch_product_options($legacyProductId)
{
    if (!function_exists('sql_fetch')) {
        return array();
    }

    $legacyProductId = (int) $legacyProductId;
    $row = sql_fetch(
        "select meeting, meeting_time, tour_day, tour_time, tour_include, tour_notInclude,
                tour_map, youtube, tour_before, tour_junbi, tour_can_rules
           from v2_product_options
          where pid = '{$legacyProductId}'
          limit 1"
    );

    return array(
        'meeting' => isset($row['meeting']) ? (string) $row['meeting'] : '',
        'meetingTime' => isset($row['meeting_time']) ? (string) $row['meeting_time'] : '',
        'tourDay' => isset($row['tour_day']) ? (string) $row['tour_day'] : '',
        'tourTime' => isset($row['tour_time']) ? (string) $row['tour_time'] : '',
        'includes' => isset($row['tour_include']) ? (string) $row['tour_include'] : '',
        'excludes' => isset($row['tour_notInclude']) ? (string) $row['tour_notInclude'] : '',
        'map' => isset($row['tour_map']) ? (string) $row['tour_map'] : '',
        'youtube' => isset($row['youtube']) ? (string) $row['youtube'] : '',
        'beforeNotice' => isset($row['tour_before']) ? (string) $row['tour_before'] : '',
        'preparation' => isset($row['tour_junbi']) ? (string) $row['tour_junbi'] : '',
        'cancelRules' => isset($row['tour_can_rules']) ? (string) $row['tour_can_rules'] : '',
    );
}

function uno_api_image_by_no($images, $fileNo)
{
    foreach ($images as $image) {
        if (isset($image['no']) && (int) $image['no'] === (int) $fileNo) {
            return $image;
        }
    }

    return null;
}

function uno_api_fetch_daily_fee_options($legacyProductId)
{
    if (!function_exists('sql_query')) {
        return array();
    }

    $legacyProductId = (int) $legacyProductId;
    $result = sql_query(
        "select id, fee_subject, fee1, fee2, fee3, is_first, fee_ticket_id
           from tour_fee
          where wr_id = '{$legacyProductId}'
            and fee_subject <> ''
          order by id asc"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $items[] = array(
            'id' => (int) $row['id'],
            'label' => isset($row['fee_subject']) ? (string) $row['fee_subject'] : '',
            'subject' => isset($row['fee_subject']) ? (string) $row['fee_subject'] : '',
            'deposit' => uno_api_money(isset($row['fee1']) ? $row['fee1'] : 0),
            'advanceLocalPayment' => isset($row['fee2']) ? (string) $row['fee2'] : '',
            'localPayment' => isset($row['fee3']) ? (string) $row['fee3'] : '',
            'localPaymentCurrency' => 'EUR',
            'ticketFeeId' => isset($row['fee_ticket_id']) ? (string) $row['fee_ticket_id'] : '',
            'isDefault' => !empty($row['is_first']),
            'isPrimary' => !empty($row['is_first']),
        );
    }

    return $items;
}

function uno_api_detail_parse_boarding_pass($air)
{
    $air = trim((string) $air);
    $emptyLeg = array(
        'departDate' => '',
        'arriveDate' => '',
        'fromCode' => '',
        'fromCity' => '',
        'toCode' => '',
        'toCity' => '',
        'departTime' => '',
        'arriveTime' => '',
        'duration' => '',
        'transfer' => '',
    );
    $boarding = array('outbound' => $emptyLeg, 'inbound' => $emptyLeg);

    foreach (preg_split('/\r?\n/', $air) as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, ':') === false) {
            continue;
        }
        list($label, $value) = array_map('trim', explode(':', $line, 2));
        $key = strtoupper($label) === 'RETURN' ? 'inbound' : 'outbound';
        $parts = array_map('trim', explode('->', $value, 2));
        $left = isset($parts[0]) ? $parts[0] : '';
        $right = isset($parts[1]) ? $parts[1] : '';
        if (preg_match('/^(.*)\s+(\d{1,2}:\d{2})$/', $left, $m)) {
            $boarding[$key]['fromCity'] = trim($m[1]);
            $boarding[$key]['departTime'] = $m[2];
        } else {
            $boarding[$key]['fromCity'] = $left;
        }
        $rightParts = array_map('trim', explode('/', $right, 2));
        $arrivalText = isset($rightParts[0]) ? $rightParts[0] : '';
        if (preg_match('/^(.*)\s+(\d{1,2}:\d{2})$/', $arrivalText, $m)) {
            $boarding[$key]['toCity'] = trim($m[1]);
            $boarding[$key]['arriveTime'] = $m[2];
        } else {
            $boarding[$key]['toCity'] = $arrivalText;
        }
        $boarding[$key]['transfer'] = isset($rightParts[1]) ? $rightParts[1] : '';
    }

    return $boarding;
}

function uno_api_fetch_package_schedules($legacyProductId)
{
    if (!function_exists('sql_query')) {
        return array();
    }

    $legacyProductId = (int) $legacyProductId;
    $result = sql_query(
        "select id, start_time, arrive_time, air, fee_1, fee_2, fee_3, fee_air, price, seat, status, is_main
           from v2_pkgTour
          where pid = '{$legacyProductId}'
            and (del_time = 0 or del_time is null)
            and (is_view = 'Y' or is_view = '1')
          order by start_time asc, id asc"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $air = isset($row['air']) ? (string) $row['air'] : '';
        $items[] = array(
            'id' => (int) $row['id'],
            'label' => trim((string) $row['start_time'] . ' 출발'),
            'startDate' => isset($row['start_time']) ? (string) $row['start_time'] : '',
            'endDate' => isset($row['arrive_time']) ? (string) $row['arrive_time'] : '',
            'boardingLabel' => $air,
            'boardingPass' => uno_api_detail_parse_boarding_pass($air),
            'deposit' => uno_api_money(isset($row['fee_1']) ? $row['fee_1'] : 0),
            'middlePayment' => uno_api_money(isset($row['fee_2']) ? $row['fee_2'] : 0),
            'intermediatePayment' => uno_api_money(isset($row['fee_2']) ? $row['fee_2'] : 0),
            'finalPayment' => uno_api_money(isset($row['fee_3']) ? $row['fee_3'] : 0),
            'balance' => uno_api_money(isset($row['fee_3']) ? $row['fee_3'] : 0),
            'airfare' => uno_api_money(isset($row['fee_air']) ? $row['fee_air'] : 0),
            'totalPrice' => uno_api_money(isset($row['price']) ? $row['price'] : 0),
            'seat' => (int) $row['seat'],
            'status' => isset($row['status']) ? (string) $row['status'] : '',
            'isDefault' => !empty($row['is_main']),
        );
    }

    return $items;
}

function uno_api_text_from_html($value)
{
    return trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8'));
}

function uno_api_fetch_product_faqs($legacyProductId)
{
    /*
     * Product-detail bottom QNA/FAQ.
     *
     * This content belongs under the product detail body image.
     * It is managed through the legacy FAQ/product FAQ tables, not through
     * the public community Q&A board (`bo_table=qna`).
     */
    global $g5;

    if (!function_exists('sql_query') || !function_exists('sql_fetch_array')) {
        return array();
    }

    $faqTable = isset($g5['faq_table']) && $g5['faq_table'] !== ''
        ? $g5['faq_table']
        : 'g5_faq';
    $faqMasterTable = isset($g5['faq_master_table']) && $g5['faq_master_table'] !== ''
        ? $g5['faq_master_table']
        : 'g5_faq_master';
    $safePid = uno_api_sql_escape((string) (int) $legacyProductId);

    $result = sql_query(
        "select f.fa_id, f.fm_id, f.pid, f.fa_subject, f.fa_content, f.fa_order, m.fm_subject
           from {$faqTable} f
           left join {$faqMasterTable} m on m.fm_id = f.fm_id
          where f.pid = ''
             or f.pid is null
             or find_in_set('{$safePid}', replace(f.pid, ' ', '')) > 0
          order by f.fa_order asc, f.fa_id asc"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $items[] = array(
            'id' => isset($row['fa_id']) ? (int) $row['fa_id'] : 0,
            'categoryId' => isset($row['fm_id']) ? (string) $row['fm_id'] : '',
            'category' => isset($row['fm_subject']) ? uno_api_text_from_html($row['fm_subject']) : '',
            'question' => isset($row['fa_subject']) ? uno_api_text_from_html($row['fa_subject']) : '',
            'answerHtml' => isset($row['fa_content']) ? (string) $row['fa_content'] : '',
            'answerText' => isset($row['fa_content']) ? uno_api_text_from_html($row['fa_content']) : '',
            'order' => isset($row['fa_order']) ? (int) $row['fa_order'] : 0,
            'legacyProductIds' => isset($row['pid']) && $row['pid'] !== ''
                ? array_values(array_filter(array_map('trim', explode(',', (string) $row['pid']))))
                : array(),
        );
    }

    return $items;
}

function uno_api_fetch_product_reviews($legacyProductId, $productId, $productTitle)
{
    global $g5;

    if (!function_exists('sql_query') || !function_exists('sql_fetch_array')) {
        return array();
    }

    $prefix = isset($g5['write_prefix']) && $g5['write_prefix'] !== ''
        ? $g5['write_prefix']
        : 'g5_write_';
    $table = $prefix . 'write';
    $legacyProductId = (int) $legacyProductId;
    $safeLegacyProductId = uno_api_sql_escape((string) $legacyProductId);
    $safeProductId = uno_api_sql_escape((string) $productId);
    $safeProductTitle = uno_api_sql_escape((string) $productTitle);
    $whereParts = array();

    for ($i = 1; $i <= 10; $i++) {
        $column = 'wr_' . $i;
        $whereParts[] = "{$column} = '{$safeLegacyProductId}'";
        if ($safeProductId !== '') {
            $whereParts[] = "{$column} = '{$safeProductId}'";
        }
    }

    if ($safeProductTitle !== '') {
        $whereParts[] = "wr_subject like '%{$safeProductTitle}%'";
        $whereParts[] = "wr_content like '%{$safeProductTitle}%'";
    }

    if (!$whereParts) {
        return array();
    }

    $where = implode(' or ', $whereParts);
    $result = sql_query(
        "select wr_id, wr_subject, wr_content, wr_name, mb_id, wr_datetime, wr_good
           from {$table}
          where wr_is_comment = 0
            and ({$where})
          order by wr_num asc, wr_reply asc
          limit 6",
        false
    );

    if (!$result) {
        return array();
    }

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $contentText = isset($row['wr_content']) ? uno_api_text_from_html($row['wr_content']) : '';
        $items[] = array(
            'id' => isset($row['wr_id']) ? (string) $row['wr_id'] : '',
            'nickname' => isset($row['wr_name']) ? uno_api_text_from_html($row['wr_name']) : '',
            'writtenAt' => isset($row['wr_datetime']) ? substr((string) $row['wr_datetime'], 0, 10) : '',
            'productTitle' => (string) $productTitle,
            'rating' => 5,
            'title' => isset($row['wr_subject']) ? uno_api_text_from_html($row['wr_subject']) : '',
            'body' => $contentText,
            'helpfulCount' => isset($row['wr_good']) ? (int) $row['wr_good'] : 0,
            'href' => '/community/review/' . (isset($row['wr_id']) ? (int) $row['wr_id'] : 0),
        );
    }

    return $items;
}

$productId = isset($_GET['id']) ? trim((string) $_GET['id']) : '';
$mode = isset($_GET['mode']) ? trim((string) $_GET['mode']) : 'reservation';

if ($productId === '') {
    uno_api_error('VALIDATION_ERROR', '상품 ID가 필요합니다.', 400);
}

$mapping = uno_api_product_mapping($productId);
if (!$mapping || empty($mapping['legacyProductId'])) {
    $mapping = uno_api_detail_mapping_fallback($productId);
}
if (!$mapping || empty($mapping['legacyProductId'])) {
    uno_api_error('PRODUCT_NOT_MAPPED', '기존 DB와 연결되지 않은 상품입니다.', 404);
}

if (!function_exists('sql_fetch')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB 함수를 찾을 수 없습니다.', 500);
}

$legacyProductId = (int) $mapping['legacyProductId'];
$productTable = uno_api_table_product();
$product = sql_fetch(
    "select wr_id, ca_name, wr_subject, wr_content, wr_reg_result, is_passport, is_delivery, is_roominfo, guide_info, fee_org, wr_4
       from {$productTable}
      where wr_id = '{$legacyProductId}'"
);

if (!$product || empty($product['wr_id'])) {
    uno_api_error('PRODUCT_NOT_FOUND', '상품을 찾을 수 없습니다.', 404);
}

$productType = isset($mapping['legacyCategory']) && $mapping['legacyCategory'] === 'semi'
    ? 'semi'
    : 'daily';
$feeOptions = array();
$packageSchedules = array();

if ($productType === 'semi') {
    $packageSchedules = uno_api_fetch_package_schedules($legacyProductId);
    $feeOptions = uno_api_fetch_daily_fee_options($legacyProductId);
} else {
    $feeOptions = uno_api_fetch_daily_fee_options($legacyProductId);
}

$defaultFee = null;
foreach ($feeOptions as $feeOption) {
    if (!empty($feeOption['isDefault']) || !empty($feeOption['isPrimary'])) {
        $defaultFee = $feeOption;
        break;
    }
}
if (!$defaultFee && count($feeOptions) > 0) {
    $defaultFee = $feeOptions[0];
}
$productImages = uno_api_fetch_product_images($legacyProductId);
$thumbnailImage = uno_api_image_by_no($productImages, 0);
$tourTopImages = uno_api_fetch_board_images('v2_tourTop', $legacyProductId);
$tourCourseImages = uno_api_fetch_board_images('v2_course', $legacyProductId);
$tourInfoImages = uno_api_fetch_board_images('v2_tourInfo', $legacyProductId);
$tourAdImages = uno_api_fetch_board_images('v2_tourAd', $legacyProductId);
$tourBannerImages = uno_api_fetch_board_images('v2_tour_bnr', $legacyProductId);
$meetingImages = uno_api_fetch_board_images('v2_product_options', $legacyProductId);
$tourOptions = uno_api_fetch_product_options($legacyProductId);
$thumbnailUrl = $thumbnailImage && isset($thumbnailImage['url']) ? $thumbnailImage['url'] : '';
$heroImageUrl = count($tourTopImages) > 0
    ? $tourTopImages[0]['url']
    : $thumbnailUrl;
$response = array(
    'id' => $productId,
    'legacyProductId' => $legacyProductId,
    'legacyFeeOptionId' => isset($mapping['legacyFeeOptionId']) ? $mapping['legacyFeeOptionId'] : null,
    'productType' => $productType,
    'title' => isset($product['wr_subject']) ? (string) $product['wr_subject'] : '',
    'category' => '',
    'legacyCategory' => isset($product['ca_name']) ? (string) $product['ca_name'] : '',
    'href' => uno_api_href_for_product($productId, $productType),
    'thumbnailUrl' => $thumbnailUrl,
    'heroImageUrl' => $heroImageUrl,
    'price' => $defaultFee ? array(
        'deposit' => $defaultFee['deposit'],
            'localPayment' => isset($defaultFee['localPayment']) && is_numeric($defaultFee['localPayment']) ? (int) $defaultFee['localPayment'] : 0,
            'localPaymentCurrency' => isset($defaultFee['localPaymentCurrency']) ? $defaultFee['localPaymentCurrency'] : 'KRW',
    ) : null,
    'originalPrice' => isset($product['fee_org']) ? uno_api_money($product['fee_org']) : 0,
    'priceDescription' => isset($product['wr_4']) ? (string) $product['wr_4'] : '',
    'requiresPassport' => uno_api_bool_field(isset($product['is_passport']) ? $product['is_passport'] : ''),
    'requiresRoomInfo' => uno_api_bool_field(isset($product['is_roominfo']) ? $product['is_roominfo'] : ''),
    'requiresDelivery' => uno_api_bool_field(isset($product['is_delivery']) ? $product['is_delivery'] : ''),
    'guideInfo' => isset($product['guide_info']) ? (string) $product['guide_info'] : '',
    'reservationDefaults' => array(
        'requiresPassport' => uno_api_bool_field(isset($product['is_passport']) ? $product['is_passport'] : ''),
        'requiresRoomInfo' => uno_api_bool_field(isset($product['is_roominfo']) ? $product['is_roominfo'] : ''),
        'requiresDelivery' => uno_api_bool_field(isset($product['is_delivery']) ? $product['is_delivery'] : ''),
        'defaultFinalStatus' => isset($product['wr_reg_result']) && $product['wr_reg_result'] !== ''
            ? (string) $product['wr_reg_result']
            : '1',
    ),
    'feeOptions' => $feeOptions,
);

if ($productType === 'semi') {
    $response['packageSchedules'] = $packageSchedules;
}

if ($mode === 'full') {
    /*
     * Detail body image flow:
     * v2_course -> v2_tourAd -> v2_tour_bnr -> v2_tourInfo.
     * PRODUCT DOCUMENT stays as a visible label/section marker only.
     */
    $bodyImages = array_merge($tourCourseImages, $tourAdImages, $tourBannerImages, $tourInfoImages);
    $response['detailHtml'] = isset($product['wr_content']) ? (string) $product['wr_content'] : '';
    $response['images'] = $productImages;
    $response['heroImages'] = array_slice($productImages, 0, 9);
    $response['detailImages'] = $bodyImages;
    $response['tourTopImages'] = $tourTopImages;
    $response['tourCourseImages'] = $tourCourseImages;
    $response['tourInfoImages'] = $tourInfoImages;
    $response['tourAdImages'] = $tourAdImages;
    $response['tourBannerImages'] = $tourBannerImages;
    $response['meetingImages'] = $meetingImages;
    $response['tourOptions'] = $tourOptions;
    $response['bodyImages'] = $bodyImages;
    $response['guides'] = uno_api_fetch_product_guides(isset($product['guide_info']) ? $product['guide_info'] : '');
    $response['productDocumentImages'] = array(
        'course' => $tourCourseImages,
        'features' => array_merge($tourAdImages, $tourBannerImages),
    );
    $response['reviews'] = uno_api_fetch_product_reviews(
        $legacyProductId,
        $productId,
        isset($product['wr_subject']) ? (string) $product['wr_subject'] : ''
    );
    $response['faqs'] = uno_api_fetch_product_faqs($legacyProductId);
}

uno_api_success($response);
