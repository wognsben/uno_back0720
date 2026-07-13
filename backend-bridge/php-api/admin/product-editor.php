<?php
/*
 * admin/product-editor.php
 * Renewal admin API for editing one legacy UNO Travel product from a single modern screen.
 * It reads and saves basic product metadata, detailed content, thumbnail images, semi-package boarding-pass schedules, and daily-tour calendar controls.
 * This endpoint keeps the renewal editor separate from legacy write.php while still writing to the existing Gnuboard tables.
 */

require_once dirname(__DIR__) . '/_bootstrap.php';
require_once dirname(__DIR__) . '/_product_map.php';
require_once dirname(__DIR__) . '/_product_mapping_store.php';
require_once dirname(__DIR__) . '/_reservation_helpers.php';

uno_api_require_login();
uno_api_require_admin();

if (!function_exists('sql_fetch') || !function_exists('sql_query')) {
    uno_api_error('SERVER_ERROR', 'Gnuboard DB functions are not available.', 500);
}

function uno_api_editor_json_body()
{
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true);
    return is_array($body) ? $body : array();
}

function uno_api_editor_date($value)
{
    $value = trim((string) $value);
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
}

function uno_api_editor_money($value)
{
    return (int) preg_replace('/[^0-9-]/', '', (string) $value);
}

function uno_api_editor_text($value)
{
    return trim((string) $value);
}

function uno_api_editor_escape($value)
{
    return uno_api_reservation_escape((string) $value);
}

function uno_api_editor_text_from_html($value)
{
    $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/[ \t]+/', ' ', $text);
    $text = preg_replace('/\R{3,}/', "\n\n", $text);
    return trim($text);
}

function uno_api_editor_guide_table()
{
    global $g5;
    return isset($g5['write_prefix']) ? $g5['write_prefix'] . 'admGuideInfo' : 'g5_write_admGuideInfo';
}

function uno_api_editor_fetch_guide_options()
{
    if (!function_exists('sql_query') || !function_exists('sql_fetch_array')) {
        return array();
    }

    $guideTable = uno_api_editor_guide_table();
    $result = sql_query(
        "select wr_id, wr_subject, wr_content
           from {$guideTable}
          order by wr_subject asc, wr_id asc"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $items[] = array(
            'id' => isset($row['wr_id']) ? (int) $row['wr_id'] : 0,
            'title' => isset($row['wr_subject']) ? stripslashes((string) $row['wr_subject']) : '',
            'bodyText' => isset($row['wr_content']) ? uno_api_editor_text_from_html(stripslashes((string) $row['wr_content'])) : '',
        );
    }

    return $items;
}

function uno_api_editor_boarding_label($body)
{
    $custom = uno_api_editor_text(isset($body['boardingLabel']) ? $body['boardingLabel'] : '');
    if ($custom !== '') {
        return $custom;
    }

    $legs = array(
        '가는편' => array(
            uno_api_editor_text(isset($body['outboundDeparturePlace']) ? $body['outboundDeparturePlace'] : ''),
            uno_api_editor_text(isset($body['outboundDepartureTime']) ? $body['outboundDepartureTime'] : ''),
            uno_api_editor_text(isset($body['outboundArrivalPlace']) ? $body['outboundArrivalPlace'] : ''),
            uno_api_editor_text(isset($body['outboundArrivalLabel']) ? $body['outboundArrivalLabel'] : ''),
        ),
        '오는편' => array(
            uno_api_editor_text(isset($body['returnDeparturePlace']) ? $body['returnDeparturePlace'] : ''),
            uno_api_editor_text(isset($body['returnDepartureTime']) ? $body['returnDepartureTime'] : ''),
            uno_api_editor_text(isset($body['returnArrivalPlace']) ? $body['returnArrivalPlace'] : ''),
            uno_api_editor_text(isset($body['returnArrivalLabel']) ? $body['returnArrivalLabel'] : ''),
        ),
    );

    $lines = array();
    foreach ($legs as $label => $leg) {
        list($departurePlace, $departureTime, $arrivalPlace, $arrivalLabel) = $leg;
        $left = trim($departurePlace . ($departureTime !== '' ? ' ' . $departureTime : ''));
        $right = trim($arrivalPlace . ($arrivalLabel !== '' ? ' / ' . $arrivalLabel : ''));
        if ($left !== '' || $right !== '') {
            $lines[] = $label . ': ' . trim($left . ($left !== '' && $right !== '' ? ' -> ' : '') . $right);
        }
    }

    return implode("\n", $lines);
}

function uno_api_editor_boarding_label_v2($body)
{
    $custom = uno_api_editor_text(isset($body['boardingLabel']) ? $body['boardingLabel'] : '');
    if ($custom !== '') {
        return $custom;
    }

    $legs = array(
        'OUT' => array(
            uno_api_editor_text(isset($body['outboundDeparturePlace']) ? $body['outboundDeparturePlace'] : ''),
            uno_api_editor_text(isset($body['outboundDepartureTime']) ? $body['outboundDepartureTime'] : ''),
            uno_api_editor_text(isset($body['outboundArrivalPlace']) ? $body['outboundArrivalPlace'] : ''),
            uno_api_editor_text(isset($body['outboundArrivalLabel']) ? $body['outboundArrivalLabel'] : ''),
        ),
        'RETURN' => array(
            uno_api_editor_text(isset($body['returnDeparturePlace']) ? $body['returnDeparturePlace'] : ''),
            uno_api_editor_text(isset($body['returnDepartureTime']) ? $body['returnDepartureTime'] : ''),
            uno_api_editor_text(isset($body['returnArrivalPlace']) ? $body['returnArrivalPlace'] : ''),
            uno_api_editor_text(isset($body['returnArrivalLabel']) ? $body['returnArrivalLabel'] : ''),
        ),
    );

    $lines = array();
    foreach ($legs as $label => $leg) {
        list($departurePlace, $departureTime, $arrivalPlace, $arrivalLabel) = $leg;
        $left = trim($departurePlace . ($departureTime !== '' ? ' ' . $departureTime : ''));
        $right = trim($arrivalPlace . ($arrivalLabel !== '' ? ' / ' . $arrivalLabel : ''));
        if ($left !== '' || $right !== '') {
            $lines[] = $label . ': ' . trim($left . ($left !== '' && $right !== '' ? ' -> ' : '') . $right);
        }
    }

    return implode("\n", $lines);
}

function uno_api_editor_product_type($row, $mapping)
{
    if ($mapping && isset($mapping['productType'])) {
        return $mapping['productType'] === 'semi' ? 'semi' : 'daily';
    }

    $category = isset($row['ca_name']) ? (string) $row['ca_name'] : '';
    return (strpos($category, '세미') !== false || strpos($category, '패키지') !== false) ? 'semi' : 'daily';
}

function uno_api_editor_mapping_by_legacy($legacyProductId)
{
    $legacyProductId = (int) $legacyProductId;
    $rows = uno_api_product_mapping_fetch_rows(false);

    foreach ($rows as $row) {
        if ((int) $row['legacyProductId'] === $legacyProductId) {
            return $row;
        }
    }

    foreach (uno_api_product_default_map() as $productId => $row) {
        if (isset($row['legacyProductId']) && (int) $row['legacyProductId'] === $legacyProductId) {
            return array(
                'productId' => $productId,
                'legacyProductId' => $legacyProductId,
                'legacyFeeOptionId' => isset($row['legacyFeeOptionId']) ? (int) $row['legacyFeeOptionId'] : null,
                'productType' => isset($row['legacyCategory']) && $row['legacyCategory'] === 'semi' ? 'semi' : 'daily',
                'confidence' => isset($row['confidence']) ? (string) $row['confidence'] : 'default',
                'isActive' => true,
                'sortOrder' => 0,
            );
        }
    }

    return null;
}

function uno_api_editor_legacy_id()
{
    if (isset($_GET['legacyProductId'])) {
        return (int) $_GET['legacyProductId'];
    }

    if (isset($_GET['productId'])) {
        $mapping = uno_api_product_mapping((string) $_GET['productId']);
        return $mapping && isset($mapping['legacyProductId']) ? (int) $mapping['legacyProductId'] : 0;
    }

    return 0;
}

function uno_api_editor_file_table()
{
    global $g5;
    return isset($g5['board_file_table']) ? $g5['board_file_table'] : 'g5_board_file';
}

function uno_api_editor_thumbnail_url($legacyProductId)
{
    $legacyProductId = (int) $legacyProductId;
    $fileTable = uno_api_editor_file_table();
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

function uno_api_editor_board_file_url($boardTable, $fileName)
{
    $boardTable = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $boardTable);
    $fileName = (string) $fileName;

    if ($boardTable === '' || $fileName === '') {
        return '';
    }

    return '/bbs/data/file/' . $boardTable . '/' . str_replace('%2F', '/', rawurlencode($fileName));
}

function uno_api_editor_fetch_board_files($boardTable, $legacyProductId)
{
    $boardTable = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $boardTable);
    $legacyProductId = (int) $legacyProductId;

    if ($boardTable === '' || $legacyProductId <= 0) {
        return array();
    }

    $fileTable = uno_api_editor_file_table();
    $result = sql_query(
        "select bf_no, bf_source, bf_file, bf_filesize
           from {$fileTable}
          where bo_table = '{$boardTable}'
            and wr_id = '{$legacyProductId}'
            and bf_file <> ''
          order by bf_no asc"
    );

    $files = array();
    while ($row = sql_fetch_array($result)) {
        $fileName = isset($row['bf_file']) ? (string) $row['bf_file'] : '';
        $files[] = array(
            'no' => isset($row['bf_no']) ? (int) $row['bf_no'] : 0,
            'source' => isset($row['bf_source']) ? (string) $row['bf_source'] : '',
            'file' => $fileName,
            'url' => uno_api_editor_board_file_url($boardTable, $fileName),
            'size' => isset($row['bf_filesize']) ? (int) $row['bf_filesize'] : 0,
        );
    }

    return $files;
}

function uno_api_editor_fetch_product($legacyProductId)
{
    $legacyProductId = (int) $legacyProductId;
    $productTable = uno_api_reservation_table_product();
    $row = sql_fetch(
        "select wr_id, ca_name, wr_subject, wr_content, wr_reg_result,
                is_passport, is_delivery, is_roominfo, wr_2, wr_5,
                fee_org, wr_b2b_result, is_ticket, is_best_tour, carlendar_max_m,
                reg_msg_top, reg_msg_bottom, reg_msg_event, reg_msg_middel,
                voucher_msg, wr_can_rule, pop_content, guide_info, recommend_tour, wr_event_course
           from {$productTable}
          where wr_id = '{$legacyProductId}'"
    );

    if (!$row || empty($row['wr_id'])) {
        uno_api_error('PRODUCT_NOT_FOUND', '상품 정보를 찾을 수 없습니다.', 404);
    }

    $mapping = uno_api_editor_mapping_by_legacy($legacyProductId);
    $productType = uno_api_editor_product_type($row, $mapping);
    $productId = $mapping && isset($mapping['productId']) ? (string) $mapping['productId'] : '';
    $productImages = uno_api_editor_fetch_board_files('product', $legacyProductId);
    $tourTopImages = uno_api_editor_fetch_board_files('v2_tourTop', $legacyProductId);
    $tourCourseImages = uno_api_editor_fetch_board_files('v2_course', $legacyProductId);
    $tourInfoImages = uno_api_editor_fetch_board_files('v2_tourInfo', $legacyProductId);
    $tourAdImages = uno_api_editor_fetch_board_files('v2_tourAd', $legacyProductId);

    return array(
        'legacyProductId' => $legacyProductId,
        'productId' => $productId,
        'productType' => $productType,
        'category' => isset($row['ca_name']) ? (string) $row['ca_name'] : '',
        'title' => isset($row['wr_subject']) ? (string) $row['wr_subject'] : '',
        'summary' => isset($row['wr_2']) ? (string) $row['wr_2'] : '',
        'guideText' => isset($row['wr_5']) ? (string) $row['wr_5'] : '',
        'content' => isset($row['wr_content']) ? (string) $row['wr_content'] : '',
        'reservationStatus' => isset($row['wr_reg_result']) ? (string) $row['wr_reg_result'] : '',
        'requiresPassport' => uno_api_reservation_bool(isset($row['is_passport']) ? $row['is_passport'] : ''),
        'requiresDelivery' => uno_api_reservation_bool(isset($row['is_delivery']) ? $row['is_delivery'] : ''),
        'requiresRoomInfo' => uno_api_reservation_bool(isset($row['is_roominfo']) ? $row['is_roominfo'] : ''),
        'thumbnailUrl' => uno_api_editor_thumbnail_url($legacyProductId),
        'frontendHref' => $productId !== '' ? uno_api_reservation_href_for_product($productId, $productType) : '',
        'legacyEditHref' => '/admin/write.php?w=u&bo_table=product&wr_id=' . $legacyProductId,
        'media' => array(
            'productImages' => $productImages,
            'heroImages' => array_slice($productImages, 0, 9),
            'tourTopImages' => $tourTopImages,
            'tourCourseImages' => $tourCourseImages,
            'tourInfoImages' => $tourInfoImages,
            'tourAdImages' => $tourAdImages,
            'counts' => array(
                'product' => count($productImages),
                'hero' => min(count($productImages), 9),
                'tourTop' => count($tourTopImages),
                'tourCourse' => count($tourCourseImages),
                'tourInfo' => count($tourInfoImages),
                'tourAd' => count($tourAdImages),
            ),
            'legacyAdminLinks' => array(
                'product' => '/admin/write.php?w=u&bo_table=product&wr_id=' . $legacyProductId,
                'tourTop' => '/admin/tourCourse.php?bo_table=v2_tourTop&wr_id=' . $legacyProductId,
                'tourCourse' => '/admin/tourCourse.php?bo_table=v2_course&wr_id=' . $legacyProductId,
                'tourInfo' => '/admin/tourCourse.php?bo_table=v2_tourInfo&wr_id=' . $legacyProductId,
                'tourAd' => '/admin/tourCourse.php?bo_table=v2_tourAd&wr_id=' . $legacyProductId,
            ),
        ),
        'extras' => array(
            'originalFeeText' => isset($row['fee_org']) ? (string) $row['fee_org'] : '',
            'b2bStatus' => isset($row['wr_b2b_result']) ? (string) $row['wr_b2b_result'] : '',
            'isTicket' => uno_api_reservation_bool(isset($row['is_ticket']) ? $row['is_ticket'] : ''),
            'isBestTour' => uno_api_reservation_bool(isset($row['is_best_tour']) ? $row['is_best_tour'] : ''),
            'calendarMonths' => isset($row['carlendar_max_m']) ? (int) $row['carlendar_max_m'] : 2,
            'reservationTopMessage' => isset($row['reg_msg_top']) ? (string) $row['reg_msg_top'] : '',
            'reservationMiddleMessage' => isset($row['reg_msg_middel']) ? (string) $row['reg_msg_middel'] : '',
            'reservationBottomMessage' => isset($row['reg_msg_bottom']) ? (string) $row['reg_msg_bottom'] : '',
            'reservationEventMessage' => isset($row['reg_msg_event']) ? (string) $row['reg_msg_event'] : '',
            'voucherMessage' => isset($row['voucher_msg']) ? (string) $row['voucher_msg'] : '',
            'cancelRule' => isset($row['wr_can_rule']) ? (string) $row['wr_can_rule'] : '',
            'popupContent' => isset($row['pop_content']) ? (string) $row['pop_content'] : '',
            'guideInfo' => isset($row['guide_info']) ? (string) $row['guide_info'] : '',
            'recommendTour' => isset($row['recommend_tour']) ? (string) $row['recommend_tour'] : '',
            'eventCourse' => isset($row['wr_event_course']) ? (string) $row['wr_event_course'] : '',
        ),
    );
}

function uno_api_editor_fetch_semi_schedules($legacyProductId)
{
    $legacyProductId = (int) $legacyProductId;
    $result = sql_query(
        "select id, start_time, arrive_time, air, fee_1, fee_2, fee_3, fee_air,
                price, seat, status, is_view, is_main
           from v2_pkgTour
          where pid = '{$legacyProductId}'
            and (del_time = 0 or del_time is null)
          order by start_time asc, id asc"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $items[] = array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'startDate' => isset($row['start_time']) ? (string) $row['start_time'] : '',
            'arriveDate' => isset($row['arrive_time']) ? (string) $row['arrive_time'] : '',
            'air' => isset($row['air']) ? (string) $row['air'] : '',
            'deposit' => uno_api_editor_money(isset($row['fee_1']) ? $row['fee_1'] : 0),
            'localPayment' => uno_api_editor_money(isset($row['fee_2']) ? $row['fee_2'] : 0),
            'extraPayment' => uno_api_editor_money(isset($row['fee_3']) ? $row['fee_3'] : 0),
            'airfare' => uno_api_editor_money(isset($row['fee_air']) ? $row['fee_air'] : 0),
            'totalPrice' => uno_api_editor_money(isset($row['price']) ? $row['price'] : 0),
            'seat' => isset($row['seat']) ? (int) $row['seat'] : 0,
            'status' => isset($row['status']) ? (string) $row['status'] : '',
            'isVisible' => uno_api_reservation_bool(isset($row['is_view']) ? $row['is_view'] : ''),
            'isMain' => uno_api_reservation_bool(isset($row['is_main']) ? $row['is_main'] : ''),
        );
    }

    return $items;
}

function uno_api_editor_fetch_daily_fee_options($legacyProductId)
{
    $legacyProductId = (int) $legacyProductId;
    $result = sql_query(
        "select id, fee_subject, fee1, fee2, fee3, is_first
           from tour_fee
          where wr_id = '{$legacyProductId}'
          order by id asc"
    );

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $items[] = array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'label' => isset($row['fee_subject']) ? (string) $row['fee_subject'] : '',
            'deposit' => uno_api_editor_money(isset($row['fee1']) ? $row['fee1'] : 0),
            'localPayment' => uno_api_editor_money(isset($row['fee2']) ? $row['fee2'] : 0),
            'extraPayment' => uno_api_editor_money(isset($row['fee3']) ? $row['fee3'] : 0),
            'isDefault' => uno_api_reservation_bool(isset($row['is_first']) ? $row['is_first'] : ''),
        );
    }

    return $items;
}

function uno_api_editor_fetch_product_options($legacyProductId)
{
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

function uno_api_editor_is_closed($value)
{
    $normalized = strtoupper(trim((string) $value));
    return in_array($normalized, array('Y', 'E', 'CLOSED', 'SOLDOUT'), true);
}

function uno_api_editor_fetch_daily_calendar($legacyProductId)
{
    $legacyProductId = (int) $legacyProductId;
    $from = date('Y-m-d');
    $to = date('Y-m-d', strtotime('+370 days'));
    $closed = array();
    $counts = array();

    $closedResult = sql_query(
        "select closedDate, isClose
           from tour_closed_2
          where pid = '{$legacyProductId}'
            and closedDate >= '{$from}'
            and closedDate <= '{$to}'"
    );
    while ($row = sql_fetch_array($closedResult)) {
        $date = uno_api_editor_date(isset($row['closedDate']) ? $row['closedDate'] : '');
        if ($date !== '') {
            $closed[$date] = isset($row['isClose']) ? (string) $row['isClose'] : '';
        }
    }

    $countResult = sql_query(
        "select tourDate, nowCount, maxCount
           from tour_reg_count
          where pid = '{$legacyProductId}'
            and tourDate >= '{$from}'
            and tourDate <= '{$to}'"
    );
    while ($row = sql_fetch_array($countResult)) {
        $date = uno_api_editor_date(isset($row['tourDate']) ? $row['tourDate'] : '');
        if ($date !== '') {
            $counts[$date] = array(
                'nowCount' => isset($row['nowCount']) ? (int) $row['nowCount'] : 0,
                'maxCount' => isset($row['maxCount']) ? (int) $row['maxCount'] : 0,
            );
        }
    }

    $items = array();
    for ($time = strtotime($from); $time <= strtotime($to); $time += 86400) {
        $date = date('Y-m-d', $time);
        $nowCount = isset($counts[$date]) ? (int) $counts[$date]['nowCount'] : 0;
        $maxCount = isset($counts[$date]) ? (int) $counts[$date]['maxCount'] : 0;
        $remaining = $maxCount > 0 ? max(0, $maxCount - $nowCount) : null;
        $status = $remaining !== null && $remaining <= ceil($maxCount / 3) ? 'soon' : 'available';

        if (isset($closed[$date]) && uno_api_editor_is_closed($closed[$date])) {
            $status = 'soldout';
            $remaining = 0;
        }

        $items[] = array(
            'date' => $date,
            'status' => $status,
            'maxCount' => $maxCount,
            'nowCount' => $nowCount,
            'remainingSeats' => $remaining,
        );
    }

    return $items;
}

function uno_api_editor_get_payload($legacyProductId)
{
    $product = uno_api_editor_fetch_product($legacyProductId);
    $data = array(
        'product' => $product,
        'guideOptions' => uno_api_editor_fetch_guide_options(),
        'productOptions' => uno_api_editor_fetch_product_options($legacyProductId),
        'semiSchedules' => array(),
        'dailyFeeOptions' => array(),
        'dailyCalendar' => array(),
    );

    if ($product['productType'] === 'semi') {
        $data['semiSchedules'] = uno_api_editor_fetch_semi_schedules($legacyProductId);
    } else {
        $data['dailyFeeOptions'] = uno_api_editor_fetch_daily_fee_options($legacyProductId);
        $data['dailyCalendar'] = uno_api_editor_fetch_daily_calendar($legacyProductId);
    }

    return $data;
}

function uno_api_editor_save_product($legacyProductId, $body)
{
    $legacyProductId = (int) $legacyProductId;
    $productTable = uno_api_reservation_table_product();
    $title = uno_api_editor_escape(isset($body['title']) ? $body['title'] : '');
    $category = uno_api_editor_escape(isset($body['category']) ? $body['category'] : '');
    $summary = uno_api_editor_escape(isset($body['summary']) ? $body['summary'] : '');
    $guideText = uno_api_editor_escape(isset($body['guideText']) ? $body['guideText'] : '');
    $content = uno_api_editor_escape(isset($body['content']) ? $body['content'] : '');
    $reservationStatus = uno_api_editor_escape(isset($body['reservationStatus']) ? $body['reservationStatus'] : '');
    $requiresPassport = !empty($body['requiresPassport']) ? 'Y' : '';
    $requiresDelivery = !empty($body['requiresDelivery']) ? 'Y' : '';
    $requiresRoomInfo = !empty($body['requiresRoomInfo']) ? 'Y' : '';
    $extras = isset($body['extras']) && is_array($body['extras']) ? $body['extras'] : array();
    $originalFeeText = uno_api_editor_escape(isset($extras['originalFeeText']) ? $extras['originalFeeText'] : '');
    $b2bStatus = uno_api_editor_escape(isset($extras['b2bStatus']) ? $extras['b2bStatus'] : '');
    $isTicket = !empty($extras['isTicket']) ? 'Y' : '';
    $isBestTour = !empty($extras['isBestTour']) ? 'Y' : '';
    $calendarMonths = isset($extras['calendarMonths']) ? max(1, min(18, (int) $extras['calendarMonths'])) : 2;
    $reservationTopMessage = uno_api_editor_escape(isset($extras['reservationTopMessage']) ? $extras['reservationTopMessage'] : '');
    $reservationMiddleMessage = uno_api_editor_escape(isset($extras['reservationMiddleMessage']) ? $extras['reservationMiddleMessage'] : '');
    $reservationBottomMessage = uno_api_editor_escape(isset($extras['reservationBottomMessage']) ? $extras['reservationBottomMessage'] : '');
    $reservationEventMessage = uno_api_editor_escape(isset($extras['reservationEventMessage']) ? $extras['reservationEventMessage'] : '');
    $voucherMessage = uno_api_editor_escape(isset($extras['voucherMessage']) ? $extras['voucherMessage'] : '');
    $cancelRule = uno_api_editor_escape(isset($extras['cancelRule']) ? $extras['cancelRule'] : '');
    $popupContent = uno_api_editor_escape(isset($extras['popupContent']) ? $extras['popupContent'] : '');
    $guideInfo = uno_api_editor_escape(isset($extras['guideInfo']) ? $extras['guideInfo'] : '');
    $recommendTour = uno_api_editor_escape(isset($extras['recommendTour']) ? $extras['recommendTour'] : '');
    $eventCourse = uno_api_editor_escape(isset($extras['eventCourse']) ? $extras['eventCourse'] : '');

    if ($title === '') {
        uno_api_error('VALIDATION_ERROR', '상품명은 비워둘 수 없습니다.', 400);
    }

    sql_query(
        "update {$productTable}
            set wr_subject = '{$title}',
                ca_name = '{$category}',
                wr_2 = '{$summary}',
                wr_5 = '{$guideText}',
                wr_content = '{$content}',
                wr_reg_result = '{$reservationStatus}',
                is_passport = '{$requiresPassport}',
                is_delivery = '{$requiresDelivery}',
                is_roominfo = '{$requiresRoomInfo}',
                fee_org = '{$originalFeeText}',
                wr_b2b_result = '{$b2bStatus}',
                is_ticket = '{$isTicket}',
                is_best_tour = '{$isBestTour}',
                carlendar_max_m = '{$calendarMonths}',
                reg_msg_top = '{$reservationTopMessage}',
                reg_msg_middel = '{$reservationMiddleMessage}',
                reg_msg_bottom = '{$reservationBottomMessage}',
                reg_msg_event = '{$reservationEventMessage}',
                voucher_msg = '{$voucherMessage}',
                wr_can_rule = '{$cancelRule}',
                pop_content = '{$popupContent}',
                guide_info = '{$guideInfo}',
                recommend_tour = '{$recommendTour}',
                wr_event_course = '{$eventCourse}'
          where wr_id = '{$legacyProductId}'"
    );
}

function uno_api_editor_save_pricing_meta($legacyProductId, $body)
{
    $legacyProductId = (int) $legacyProductId;
    $productTable = uno_api_reservation_table_product();
    $extras = isset($body['extras']) && is_array($body['extras']) ? $body['extras'] : array();
    $originalFeeText = uno_api_editor_escape(isset($extras['originalFeeText']) ? $extras['originalFeeText'] : '');

    sql_query(
        "update {$productTable}
            set fee_org = '{$originalFeeText}'
          where wr_id = '{$legacyProductId}'"
    );
}

function uno_api_editor_save_audit_fields($legacyProductId, $body)
{
    $legacyProductId = (int) $legacyProductId;
    $productTable = uno_api_reservation_table_product();
    $extras = isset($body['extras']) && is_array($body['extras']) ? $body['extras'] : array();

    $originalFeeText = uno_api_editor_escape(isset($extras['originalFeeText']) ? $extras['originalFeeText'] : '');
    $b2bStatus = uno_api_editor_escape(isset($extras['b2bStatus']) ? $extras['b2bStatus'] : '');
    $isTicket = !empty($extras['isTicket']) ? 'Y' : '';
    $isBestTour = !empty($extras['isBestTour']) ? 'Y' : '';
    $calendarMonths = isset($extras['calendarMonths']) ? max(1, min(18, (int) $extras['calendarMonths'])) : 2;
    $reservationTopMessage = uno_api_editor_escape(isset($extras['reservationTopMessage']) ? $extras['reservationTopMessage'] : '');
    $reservationMiddleMessage = uno_api_editor_escape(isset($extras['reservationMiddleMessage']) ? $extras['reservationMiddleMessage'] : '');
    $reservationBottomMessage = uno_api_editor_escape(isset($extras['reservationBottomMessage']) ? $extras['reservationBottomMessage'] : '');
    $reservationEventMessage = uno_api_editor_escape(isset($extras['reservationEventMessage']) ? $extras['reservationEventMessage'] : '');
    $voucherMessage = uno_api_editor_escape(isset($extras['voucherMessage']) ? $extras['voucherMessage'] : '');
    $cancelRule = uno_api_editor_escape(isset($extras['cancelRule']) ? $extras['cancelRule'] : '');
    $popupContent = uno_api_editor_escape(isset($extras['popupContent']) ? $extras['popupContent'] : '');

    sql_query(
        "update {$productTable}
            set fee_org = '{$originalFeeText}',
                wr_b2b_result = '{$b2bStatus}',
                is_ticket = '{$isTicket}',
                is_best_tour = '{$isBestTour}',
                carlendar_max_m = '{$calendarMonths}',
                reg_msg_top = '{$reservationTopMessage}',
                reg_msg_middel = '{$reservationMiddleMessage}',
                reg_msg_bottom = '{$reservationBottomMessage}',
                reg_msg_event = '{$reservationEventMessage}',
                voucher_msg = '{$voucherMessage}',
                wr_can_rule = '{$cancelRule}',
                pop_content = '{$popupContent}'
          where wr_id = '{$legacyProductId}'"
    );
}

function uno_api_editor_save_basic_info($legacyProductId, $body)
{
    $legacyProductId = (int) $legacyProductId;
    $productTable = uno_api_reservation_table_product();
    $title = uno_api_editor_escape(isset($body['title']) ? $body['title'] : '');
    $category = uno_api_editor_escape(isset($body['category']) ? $body['category'] : '');
    $summary = uno_api_editor_escape(isset($body['summary']) ? $body['summary'] : '');
    $guideText = uno_api_editor_escape(isset($body['guideText']) ? $body['guideText'] : '');
    $reservationStatus = uno_api_editor_escape(isset($body['reservationStatus']) ? $body['reservationStatus'] : '');
    $requiresPassport = !empty($body['requiresPassport']) ? 'Y' : '';
    $requiresDelivery = !empty($body['requiresDelivery']) ? 'Y' : '';
    $requiresRoomInfo = !empty($body['requiresRoomInfo']) ? 'Y' : '';

    if ($title === '') {
        uno_api_error('VALIDATION_ERROR', '상품명을 입력해 주세요.', 400);
    }

    sql_query(
        "update {$productTable}
            set wr_subject = '{$title}',
                ca_name = '{$category}',
                wr_2 = '{$summary}',
                wr_5 = '{$guideText}',
                wr_reg_result = '{$reservationStatus}',
                is_passport = '{$requiresPassport}',
                is_delivery = '{$requiresDelivery}',
                is_roominfo = '{$requiresRoomInfo}'
          where wr_id = '{$legacyProductId}'"
    );
}

function uno_api_editor_save_detail_content($legacyProductId, $body)
{
    $legacyProductId = (int) $legacyProductId;
    $productTable = uno_api_reservation_table_product();
    $content = uno_api_editor_escape(isset($body['content']) ? $body['content'] : '');
    $extras = isset($body['extras']) && is_array($body['extras']) ? $body['extras'] : array();
    $guideInfo = uno_api_editor_escape(isset($extras['guideInfo']) ? $extras['guideInfo'] : '');
    $recommendTour = uno_api_editor_escape(isset($extras['recommendTour']) ? $extras['recommendTour'] : '');
    $eventCourse = uno_api_editor_escape(isset($extras['eventCourse']) ? $extras['eventCourse'] : '');

    sql_query(
        "update {$productTable}
            set wr_content = '{$content}',
                guide_info = '{$guideInfo}',
                recommend_tour = '{$recommendTour}',
                wr_event_course = '{$eventCourse}'
          where wr_id = '{$legacyProductId}'"
    );
}

function uno_api_editor_save_semi_schedule($legacyProductId, $body)
{
    $legacyProductId = (int) $legacyProductId;
    $scheduleId = isset($body['id']) ? (int) $body['id'] : 0;
    $previous = array();

    if ($scheduleId > 0) {
        $previousRow = sql_fetch(
            "select fee_1, fee_2, fee_3, fee_air, price, seat, status
               from v2_pkgTour
              where id = '{$scheduleId}'
                and pid = '{$legacyProductId}'
              limit 1"
        );
        $previous = is_array($previousRow) ? $previousRow : array();
    }

    $startDate = uno_api_editor_date(isset($body['startDate']) ? $body['startDate'] : '');
    $arriveDate = uno_api_editor_date(isset($body['arriveDate']) ? $body['arriveDate'] : '');
    $air = uno_api_editor_escape(uno_api_editor_boarding_label_v2($body));
    $deposit = array_key_exists('deposit', $body) ? uno_api_editor_money($body['deposit']) : uno_api_editor_money(isset($previous['fee_1']) ? $previous['fee_1'] : 0);
    $localPayment = array_key_exists('localPayment', $body) ? uno_api_editor_money($body['localPayment']) : uno_api_editor_money(isset($previous['fee_2']) ? $previous['fee_2'] : 0);
    $extraPayment = array_key_exists('extraPayment', $body) ? uno_api_editor_money($body['extraPayment']) : uno_api_editor_money(isset($previous['fee_3']) ? $previous['fee_3'] : 0);
    $airfare = array_key_exists('airfare', $body) ? uno_api_editor_money($body['airfare']) : uno_api_editor_money(isset($previous['fee_air']) ? $previous['fee_air'] : 0);
    $totalPrice = array_key_exists('totalPrice', $body) ? uno_api_editor_money($body['totalPrice']) : uno_api_editor_money(isset($previous['price']) ? $previous['price'] : 0);
    $seat = array_key_exists('seat', $body) ? (int) $body['seat'] : (isset($previous['seat']) ? (int) $previous['seat'] : 0);
    $status = uno_api_editor_escape(array_key_exists('status', $body) ? $body['status'] : (isset($previous['status']) ? $previous['status'] : ''));
    $isVisible = !empty($body['isVisible']) ? 'Y' : 'N';
    $isMain = !empty($body['isMain']) ? 'Y' : 'N';

    if ($startDate === '') {
        uno_api_error('VALIDATION_ERROR', '출발일을 입력해 주세요.', 400);
    }

    if ($arriveDate === '') {
        $arriveDate = $startDate;
    }

    if ($totalPrice <= 0) {
        $totalPrice = $deposit + $localPayment + $extraPayment + $airfare;
    }

    if ($isMain === 'Y') {
        sql_query("update v2_pkgTour set is_main = 'N' where pid = '{$legacyProductId}'");
    }

    if ($scheduleId > 0) {
        sql_query(
            "update v2_pkgTour
                set start_time = '{$startDate}',
                    arrive_time = '{$arriveDate}',
                    air = '{$air}',
                    fee_1 = '{$deposit}',
                    fee_2 = '{$localPayment}',
                    fee_3 = '{$extraPayment}',
                    fee_air = '{$airfare}',
                    price = '{$totalPrice}',
                    seat = '{$seat}',
                    status = '{$status}',
                    is_view = '{$isVisible}',
                    is_main = '{$isMain}'
              where id = '{$scheduleId}'
                and pid = '{$legacyProductId}'"
        );
        return;
    }

    sql_query(
        "insert into v2_pkgTour
            set pid = '{$legacyProductId}',
                start_time = '{$startDate}',
                arrive_time = '{$arriveDate}',
                air = '{$air}',
                fee_1 = '{$deposit}',
                fee_2 = '{$localPayment}',
                fee_3 = '{$extraPayment}',
                fee_air = '{$airfare}',
                price = '{$totalPrice}',
                seat = '{$seat}',
                status = '{$status}',
                is_view = '{$isVisible}',
                is_main = '{$isMain}',
                del_time = 0"
    );
}

function uno_api_editor_delete_semi_schedule($legacyProductId, $body)
{
    $legacyProductId = (int) $legacyProductId;
    $scheduleId = isset($body['id']) ? (int) $body['id'] : 0;

    if ($scheduleId <= 0) {
        uno_api_error('VALIDATION_ERROR', '삭제할 일정을 찾을 수 없습니다.', 400);
    }

    $now = date('Y-m-d H:i:s');
    sql_query(
        "update v2_pkgTour
            set del_time = '{$now}'
          where id = '{$scheduleId}'
            and pid = '{$legacyProductId}'"
    );
}

function uno_api_editor_save_daily_calendar($legacyProductId, $body)
{
    $legacyProductId = (int) $legacyProductId;
    $date = uno_api_editor_date(isset($body['date']) ? $body['date'] : '');
    $status = isset($body['status']) ? (string) $body['status'] : 'available';
    $maxCount = isset($body['maxCount']) ? (int) $body['maxCount'] : 0;
    $previousCount = $date !== '' ? sql_fetch("select nowCount from tour_reg_count where pid = '{$legacyProductId}' and tourDate = '{$date}' limit 1") : array();
    $nowCount = isset($body['nowCount'])
        ? (int) $body['nowCount']
        : (isset($previousCount['nowCount']) ? (int) $previousCount['nowCount'] : 0);

    if ($date === '') {
        uno_api_error('VALIDATION_ERROR', '날짜를 확인해 주세요.', 400);
    }

    sql_query("delete from tour_closed_2 where pid = '{$legacyProductId}' and closedDate = '{$date}'");
    if ($status === 'soldout') {
        sql_query(
            "insert into tour_closed_2
                set pid = '{$legacyProductId}',
                    closedDate = '{$date}',
                    isClose = 'Y'"
        );
    }

    sql_query("delete from tour_reg_count where pid = '{$legacyProductId}' and tourDate = '{$date}'");
    if ($maxCount > 0 || $nowCount > 0) {
        sql_query(
            "insert into tour_reg_count
                set pid = '{$legacyProductId}',
                    tourDate = '{$date}',
                    nowCount = '{$nowCount}',
                    maxCount = '{$maxCount}'"
        );
    }
}

function uno_api_editor_save_product_options($legacyProductId, $body)
{
    $legacyProductId = (int) $legacyProductId;
    $options = isset($body['productOptions']) && is_array($body['productOptions']) ? $body['productOptions'] : $body;
    $meeting = uno_api_editor_escape(isset($options['meeting']) ? $options['meeting'] : '');
    $meetingTime = uno_api_editor_escape(isset($options['meetingTime']) ? $options['meetingTime'] : '');
    $tourDay = uno_api_editor_escape(isset($options['tourDay']) ? $options['tourDay'] : '');
    $tourTime = uno_api_editor_escape(isset($options['tourTime']) ? $options['tourTime'] : '');
    $includes = uno_api_editor_escape(isset($options['includes']) ? $options['includes'] : '');
    $excludes = uno_api_editor_escape(isset($options['excludes']) ? $options['excludes'] : '');
    $map = uno_api_editor_escape(isset($options['map']) ? $options['map'] : '');
    $youtube = uno_api_editor_escape(isset($options['youtube']) ? $options['youtube'] : '');
    $beforeNotice = uno_api_editor_escape(isset($options['beforeNotice']) ? $options['beforeNotice'] : '');
    $preparation = uno_api_editor_escape(isset($options['preparation']) ? $options['preparation'] : '');
    $cancelRules = uno_api_editor_escape(isset($options['cancelRules']) ? $options['cancelRules'] : '');
    $exists = sql_fetch("select id from v2_product_options where pid = '{$legacyProductId}' limit 1");

    if ($exists && isset($exists['id'])) {
        sql_query(
            "update v2_product_options
                set meeting = '{$meeting}',
                    meeting_time = '{$meetingTime}',
                    tour_day = '{$tourDay}',
                    tour_time = '{$tourTime}',
                    tour_include = '{$includes}',
                    tour_notInclude = '{$excludes}',
                    tour_map = '{$map}',
                    youtube = '{$youtube}',
                    tour_before = '{$beforeNotice}',
                    tour_junbi = '{$preparation}',
                    tour_can_rules = '{$cancelRules}'
              where pid = '{$legacyProductId}'"
        );
        return;
    }

    sql_query(
        "insert into v2_product_options
            set pid = '{$legacyProductId}',
                meeting = '{$meeting}',
                meeting_time = '{$meetingTime}',
                tour_day = '{$tourDay}',
                tour_time = '{$tourTime}',
                tour_include = '{$includes}',
                tour_notInclude = '{$excludes}',
                tour_map = '{$map}',
                youtube = '{$youtube}',
                tour_before = '{$beforeNotice}',
                tour_junbi = '{$preparation}',
                tour_can_rules = '{$cancelRules}'"
    );
}

function uno_api_editor_save_daily_fee_option($legacyProductId, $body)
{
    $legacyProductId = (int) $legacyProductId;
    $feeId = isset($body['id']) ? (int) $body['id'] : 0;
    $label = uno_api_editor_escape(isset($body['label']) ? $body['label'] : '');
    $deposit = uno_api_editor_money(isset($body['deposit']) ? $body['deposit'] : 0);
    $localPayment = uno_api_editor_money(isset($body['localPayment']) ? $body['localPayment'] : 0);
    $extraPayment = uno_api_editor_money(isset($body['extraPayment']) ? $body['extraPayment'] : 0);
    $isDefault = !empty($body['isDefault']) ? 'Y' : '';

    if ($label === '') {
        uno_api_error('VALIDATION_ERROR', '요금 옵션명을 입력해 주세요.', 400);
    }

    if ($isDefault === 'Y') {
        sql_query("update tour_fee set is_first = '' where wr_id = '{$legacyProductId}'");
    }

    if ($feeId > 0) {
        sql_query(
            "update tour_fee
                set fee_subject = '{$label}',
                    fee1 = '{$deposit}',
                    fee2 = '{$localPayment}',
                    fee3 = '{$extraPayment}',
                    is_first = '{$isDefault}'
              where id = '{$feeId}'
                and wr_id = '{$legacyProductId}'"
        );
        return;
    }

    sql_query(
        "insert into tour_fee
            set wr_id = '{$legacyProductId}',
                fee_subject = '{$label}',
                fee1 = '{$deposit}',
                fee2 = '{$localPayment}',
                fee3 = '{$extraPayment}',
                is_first = '{$isDefault}'"
    );
}

function uno_api_editor_delete_daily_fee_option($legacyProductId, $body)
{
    $legacyProductId = (int) $legacyProductId;
    $feeId = isset($body['id']) ? (int) $body['id'] : 0;

    if ($feeId <= 0) {
        uno_api_error('VALIDATION_ERROR', '삭제할 요금 옵션을 찾을 수 없습니다.', 400);
    }

    sql_query("delete from tour_fee where id = '{$feeId}' and wr_id = '{$legacyProductId}'");
}

function uno_api_editor_apply_daily_calendar_pattern($legacyProductId, $body)
{
    $from = uno_api_editor_date(isset($body['from']) ? $body['from'] : '');
    $to = uno_api_editor_date(isset($body['to']) ? $body['to'] : '');
    $pattern = isset($body['pattern']) ? (string) $body['pattern'] : 'everyday';
    $weekdays = isset($body['weekdays']) && is_array($body['weekdays']) ? $body['weekdays'] : array();
    $status = isset($body['status']) ? (string) $body['status'] : 'available';
    $maxCount = isset($body['maxCount']) ? (int) $body['maxCount'] : 0;
    $nowCount = isset($body['nowCount']) ? (int) $body['nowCount'] : null;
    $replaceRange = !empty($body['replaceRange']);

    if ($from === '' || $to === '') {
        uno_api_error('VALIDATION_ERROR', '적용할 시작일과 종료일을 입력해 주세요.', 400);
    }

    $fromTime = strtotime($from);
    $toTime = strtotime($to);
    if ($fromTime === false || $toTime === false || $fromTime > $toTime) {
        uno_api_error('VALIDATION_ERROR', '캘린더 기간을 확인해 주세요.', 400);
    }

    if (($toTime - $fromTime) / 86400 > 370) {
        uno_api_error('VALIDATION_ERROR', '반복 적용 기간은 최대 1년까지만 가능합니다.', 400);
    }

    $weekdayMap = array();
    foreach ($weekdays as $weekday) {
        $weekdayMap[(int) $weekday] = true;
    }

    $applied = 0;
    for ($time = $fromTime; $time <= $toTime; $time += 86400) {
        $day = (int) date('j', $time);
        $weekday = (int) date('w', $time);
        $matches = $pattern === 'everyday'
            || ($pattern === 'odd' && $day % 2 === 1)
            || ($pattern === 'even' && $day % 2 === 0)
            || ($pattern === 'weekday' && isset($weekdayMap[$weekday]));

        if (!$matches && !$replaceRange) {
            continue;
        }

        uno_api_editor_save_daily_calendar($legacyProductId, array(
            'date' => date('Y-m-d', $time),
            'status' => $matches ? $status : 'soldout',
            'maxCount' => $matches ? $maxCount : 0,
            'nowCount' => $nowCount,
        ));
        if ($matches) {
            $applied++;
        }
    }

    return $applied;
}

function uno_api_editor_upload_dir()
{
    if (defined('G5_DATA_PATH')) {
        return G5_DATA_PATH . '/file/product';
    }

    return dirname(__DIR__, 2) . '/bbs/data/file/product';
}

function uno_api_editor_upload_thumbnail($legacyProductId)
{
    $legacyProductId = (int) $legacyProductId;

    if (!isset($_FILES['thumbnail']) || !is_array($_FILES['thumbnail'])) {
        uno_api_error('VALIDATION_ERROR', '업로드할 썸네일 이미지를 선택해 주세요.', 400);
    }

    $file = $_FILES['thumbnail'];

    if (!isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK) {
        uno_api_error('VALIDATION_ERROR', '썸네일 업로드에 실패했습니다.', 400);
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        uno_api_error('VALIDATION_ERROR', '업로드 파일을 확인할 수 없습니다.', 400);
    }

    $size = isset($file['size']) ? (int) $file['size'] : 0;
    if ($size <= 0 || $size > 8 * 1024 * 1024) {
        uno_api_error('VALIDATION_ERROR', '썸네일은 8MB 이하 이미지만 업로드할 수 있습니다.', 400);
    }

    $sourceName = isset($file['name']) ? (string) $file['name'] : 'thumbnail';
    $extension = strtolower(pathinfo($sourceName, PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'webp', 'gif');

    if (!in_array($extension, $allowed, true)) {
        uno_api_error('VALIDATION_ERROR', 'jpg, png, webp, gif 이미지만 업로드할 수 있습니다.', 400);
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if (!$imageInfo || empty($imageInfo[0]) || empty($imageInfo[1])) {
        uno_api_error('VALIDATION_ERROR', '이미지 파일만 업로드할 수 있습니다.', 400);
    }

    $uploadDir = uno_api_editor_upload_dir();
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, defined('G5_DIR_PERMISSION') ? G5_DIR_PERMISSION : 0755, true);
    }

    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
        uno_api_error('SERVER_ERROR', '썸네일 저장 폴더에 쓸 수 없습니다.', 500);
    }

    $storedFile = date('YmdHis') . '_renewal_' . $legacyProductId . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $uploadDir . '/' . $storedFile;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        uno_api_error('SERVER_ERROR', '썸네일 파일을 저장하지 못했습니다.', 500);
    }

    @chmod($destination, defined('G5_FILE_PERMISSION') ? G5_FILE_PERMISSION : 0644);

    $fileTable = uno_api_editor_file_table();
    $productTable = uno_api_reservation_table_product();
    $source = uno_api_editor_escape($sourceName);
    $stored = uno_api_editor_escape($storedFile);
    $width = (int) $imageInfo[0];
    $height = (int) $imageInfo[1];
    $fileSize = filesize($destination);
    $now = date('Y-m-d H:i:s');

    sql_query("delete from {$fileTable} where bo_table = 'product' and wr_id = '{$legacyProductId}' and bf_no = '0'");
    sql_query(
        "insert into {$fileTable}
            set bo_table = 'product',
                wr_id = '{$legacyProductId}',
                bf_no = '0',
                bf_source = '{$source}',
                bf_file = '{$stored}',
                bf_download = '0',
                bf_content = '',
                bf_filesize = '{$fileSize}',
                bf_width = '{$width}',
                bf_height = '{$height}',
                bf_type = '1',
                bf_datetime = '{$now}'"
    );
    sql_query("update {$productTable} set wr_file = '1' where wr_id = '{$legacyProductId}'");
}

$legacyProductId = uno_api_editor_legacy_id();

if ($legacyProductId <= 0) {
    uno_api_error('VALIDATION_ERROR', '상품 ID가 필요합니다.', 400);
}

$method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';

if ($method === 'GET') {
    uno_api_success(uno_api_editor_get_payload($legacyProductId));
}

if ($method !== 'POST') {
    uno_api_error('VALIDATION_ERROR', '허용되지 않는 요청 방식입니다.', 405);
}

if (isset($_POST['action']) && (string) $_POST['action'] === 'uploadThumbnail') {
    uno_api_editor_upload_thumbnail($legacyProductId);
    uno_api_success(uno_api_editor_get_payload($legacyProductId));
}

$body = uno_api_editor_json_body();
$action = isset($body['action']) ? (string) $body['action'] : '';

if ($action === 'saveProduct') {
    uno_api_editor_save_product($legacyProductId, $body);
} elseif ($action === 'saveBasicInfo') {
    uno_api_editor_save_basic_info($legacyProductId, $body);
} elseif ($action === 'savePricingMeta') {
    uno_api_editor_save_pricing_meta($legacyProductId, $body);
} elseif ($action === 'saveAuditFields') {
    uno_api_editor_save_audit_fields($legacyProductId, $body);
} elseif ($action === 'saveDetailContent') {
    uno_api_editor_save_detail_content($legacyProductId, $body);
} elseif ($action === 'saveProductOptions') {
    uno_api_editor_save_product_options($legacyProductId, $body);
} elseif ($action === 'saveSemiSchedule') {
    uno_api_editor_save_semi_schedule($legacyProductId, $body);
} elseif ($action === 'deleteSemiSchedule') {
    uno_api_editor_delete_semi_schedule($legacyProductId, $body);
} elseif ($action === 'saveDailyFeeOption') {
    uno_api_editor_save_daily_fee_option($legacyProductId, $body);
} elseif ($action === 'deleteDailyFeeOption') {
    uno_api_editor_delete_daily_fee_option($legacyProductId, $body);
} elseif ($action === 'saveDailyCalendar') {
    uno_api_editor_save_daily_calendar($legacyProductId, $body);
} elseif ($action === 'applyDailyCalendarPattern') {
    uno_api_editor_apply_daily_calendar_pattern($legacyProductId, $body);
} else {
    uno_api_error('VALIDATION_ERROR', '알 수 없는 저장 요청입니다.', 400);
}

uno_api_success(uno_api_editor_get_payload($legacyProductId));
