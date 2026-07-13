<?php
/*
 * reservations.php
 * Renewal admin reservation overview for legacy UNO Travel tour_reg data.
 * It summarizes reservation states and lists recent semi/daily bookings with filters.
 * This page is a readable renewal entry point; detailed edits still link to legacy booking screens.
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

uno_renewal_admin_require_access('/admin/renewal/reservations.php');

function uno_renewal_reservation_escape_db($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_renewal_reservation_fetch($sql)
{
    if (!function_exists('sql_fetch')) {
        return array();
    }

    $row = sql_fetch($sql);
    return is_array($row) ? $row : array();
}

function uno_renewal_reservation_query($sql)
{
    return function_exists('sql_query') ? sql_query($sql) : false;
}

function uno_renewal_reservation_fetch_array($result)
{
    return $result && function_exists('sql_fetch_array') ? sql_fetch_array($result) : false;
}

function uno_renewal_reservation_status_label($status)
{
    $map = array(
        '1' => '예약 대기',
        '2' => '예약 확인',
        '11' => '입금 확인',
        '3' => '예약 확정',
        '9' => '예약 취소',
        '91' => '취소 요청',
        '99' => '취소 완료',
    );

    return isset($map[$status]) ? $map[$status] : $status;
}

function uno_renewal_reservation_date($value)
{
    if ($value === null || $value === '') {
        return '-';
    }

    if (is_numeric($value)) {
        return date('Y-m-d H:i', (int) $value);
    }

    return substr((string) $value, 0, 16);
}

function uno_renewal_reservation_date_param($key, $fallback)
{
    $value = isset($_GET[$key]) ? trim((string) $_GET[$key]) : '';
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : $fallback;
}

function uno_renewal_reservation_type_condition($type, $alias = 'p')
{
    if ($type === 'semi') {
        return "{$alias}.ca_name like '%세미패키지%'";
    }

    if ($type === 'daily') {
        return "({$alias}.ca_name like '%데이투어%' or {$alias}.ca_name like '%데일리투어%' or {$alias}.ca_name like '%데이투어-숨김%')";
    }

    return '1=1';
}

function uno_renewal_reservation_type_from_category($category)
{
    $category = (string) $category;
    if (strpos($category, '세미패키지') !== false) {
        return 'semi';
    }

    if (strpos($category, '데이투어') !== false || strpos($category, '데일리투어') !== false) {
        return 'daily';
    }

    return 'other';
}

function uno_renewal_reservation_member_count($value)
{
    $total = 0;
    foreach (explode('|', (string) $value) as $count) {
        if ($count !== '') {
            $total += (int) $count;
        }
    }

    return $total;
}

function uno_renewal_reservation_calendar_range($mode, $date)
{
    $timestamp = strtotime($date);
    if (!$timestamp) {
        $timestamp = time();
    }

    if ($mode === 'week') {
        $weekStart = strtotime('monday this week', $timestamp);
        return array(
            'start' => date('Y-m-d', $weekStart),
            'end' => date('Y-m-d', strtotime('+6 days', $weekStart)),
            'title' => date('Y.m.d', $weekStart) . ' - ' . date('Y.m.d', strtotime('+6 days', $weekStart)),
        );
    }

    return array(
        'start' => date('Y-m-01', $timestamp),
        'end' => date('Y-m-t', $timestamp),
        'title' => date('Y.m', $timestamp),
    );
}

function uno_renewal_reservation_fetch_calendar($startDate, $endDate)
{
    $safeStart = uno_renewal_reservation_escape_db($startDate);
    $safeEnd = uno_renewal_reservation_escape_db($endDate);
    $calendar = array(
        'semi' => array(),
        'daily' => array(),
    );

    $result = uno_renewal_reservation_query(
        "select r.id, r.pid, r.event_pid, r.mb_name, r.membCnt, r.tourDay, r.status,
                p.wr_subject, p.ca_name
           from tour_reg r
           left join g5_write_product p on r.pid = p.wr_id
          where r.status not in ('cart', 'booking')
            and r.tourDay >= '{$safeStart}'
            and r.tourDay <= '{$safeEnd} 23:59:59'
          order by r.tourDay asc, p.ca_name asc, p.wr_subject asc, r.id asc"
    );

    while ($row = uno_renewal_reservation_fetch_array($result)) {
        $category = isset($row['ca_name']) ? (string) $row['ca_name'] : '';
        $type = uno_renewal_reservation_type_from_category($category);
        if ($type === 'other') {
            continue;
        }

        $day = isset($row['tourDay']) ? substr((string) $row['tourDay'], 0, 10) : '';
        if ($day === '') {
            continue;
        }

        if (!isset($calendar[$type][$day])) {
            $calendar[$type][$day] = array();
        }

        $productId = !empty($row['event_pid']) ? (int) $row['event_pid'] : (int) $row['pid'];
        if (!isset($calendar[$type][$day][$productId])) {
            $calendar[$type][$day][$productId] = array(
                'id' => $productId,
                'title' => isset($row['wr_subject']) ? (string) $row['wr_subject'] : '상품명 없음',
                'category' => $category,
                'count' => 0,
                'people' => 0,
                'confirmed' => 0,
            );
        }

        $calendar[$type][$day][$productId]['count']++;
        $calendar[$type][$day][$productId]['people'] += uno_renewal_reservation_member_count(isset($row['membCnt']) ? $row['membCnt'] : '');
        if ((string) (isset($row['status']) ? $row['status'] : '') === '3') {
            $calendar[$type][$day][$productId]['confirmed']++;
        }
    }

    return $calendar;
}

function uno_renewal_reservation_url($params)
{
    $query = array_filter($params, function ($value) {
        return $value !== '' && $value !== null;
    });

    return '/admin/renewal/reservations.php' . ($query ? '?' . http_build_query($query) : '');
}

function uno_renewal_reservation_calendar_days($mode, $range)
{
    $days = array();
    $start = strtotime($range['start']);
    $end = strtotime($range['end']);

    if ($mode === 'month') {
        $start = strtotime('sunday this week', strtotime($range['start']));
        $end = strtotime('saturday this week', strtotime($range['end']));
    }

    for ($date = $start; $date <= $end; $date = strtotime('+1 day', $date)) {
        $days[] = date('Y-m-d', $date);
    }

    return $days;
}

$type = isset($_GET['type']) ? (string) $_GET['type'] : 'all';
$status = isset($_GET['status']) ? (string) $_GET['status'] : 'all';
$keyword = isset($_GET['keyword']) ? trim((string) $_GET['keyword']) : '';
$dateFrom = uno_renewal_reservation_date_param('dateFrom', '');
$dateTo = uno_renewal_reservation_date_param('dateTo', '');
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 40;
$offset = ($page - 1) * $perPage;
$calendarMode = isset($_GET['calendarMode']) && $_GET['calendarMode'] === 'week' ? 'week' : 'month';
$calendarDate = uno_renewal_reservation_date_param('calendarDate', date('Y-m-d'));
$calendarRange = uno_renewal_reservation_calendar_range($calendarMode, $calendarDate);
$calendarRows = uno_renewal_reservation_fetch_calendar($calendarRange['start'], $calendarRange['end']);

$where = array("r.status not in ('cart', 'booking')");

if ($type === 'semi' || $type === 'daily') {
    $where[] = uno_renewal_reservation_type_condition($type, 'p');
}

if ($status !== 'all' && $status !== '') {
    $safeStatus = uno_renewal_reservation_escape_db($status);
    $where[] = "r.status = '{$safeStatus}'";
}

if ($keyword !== '') {
    $safeKeyword = uno_renewal_reservation_escape_db($keyword);
    $where[] = "(r.id like '%{$safeKeyword}%' or r.mb_id like '%{$safeKeyword}%' or r.mb_name like '%{$safeKeyword}%' or r.mb_email like '%{$safeKeyword}%' or p.wr_subject like '%{$safeKeyword}%')";
}

if ($dateFrom !== '') {
    $safeDateFrom = uno_renewal_reservation_escape_db($dateFrom);
    $where[] = "r.tourDay >= '{$safeDateFrom}'";
}

if ($dateTo !== '') {
    $safeDateTo = uno_renewal_reservation_escape_db($dateTo);
    $where[] = "r.tourDay <= '{$safeDateTo} 23:59:59'";
}

$whereSql = implode(' and ', $where);
$totalRow = uno_renewal_reservation_fetch("select count(*) as cnt from tour_reg r left join g5_write_product p on r.pid = p.wr_id where {$whereSql}");
$totalCount = isset($totalRow['cnt']) ? (int) $totalRow['cnt'] : 0;
$totalPages = max(1, (int) ceil($totalCount / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $perPage;
}
$cards = array(
    array('label' => '예약 대기', 'status' => '1'),
    array('label' => '입금 확인', 'status' => '11'),
    array('label' => '예약 확정', 'status' => '3'),
    array('label' => '취소 요청', 'status' => '91'),
    array('label' => '예약 취소', 'status' => '9'),
);

foreach ($cards as $index => $card) {
    $safeStatus = uno_renewal_reservation_escape_db($card['status']);
    $row = uno_renewal_reservation_fetch("select count(*) as cnt from tour_reg r left join g5_write_product p on r.pid = p.wr_id where {$whereSql} and r.status = '{$safeStatus}'");
    $cards[$index]['count'] = isset($row['cnt']) ? (int) $row['cnt'] : 0;
}

$rows = array();
$reservationGroups = array(
    'semi' => array('label' => '세미패키지 예약내역', 'rows' => array()),
    'daily' => array('label' => '데일리투어 예약내역', 'rows' => array()),
    'other' => array('label' => '기타 예약내역', 'rows' => array()),
);
$result = uno_renewal_reservation_query(
    "select r.id, r.regDate, r.mb_id, r.mb_name, r.mb_email, r.mb_hp, r.tourDay, r.status, r.total_fee1, r.total_fee2, r.card_pay,
            p.wr_subject, p.ca_name
       from tour_reg r
       left join g5_write_product p on r.pid = p.wr_id
      where {$whereSql}
      order by r.id desc
      limit {$offset}, {$perPage}"
);

while ($row = uno_renewal_reservation_fetch_array($result)) {
    $rows[] = $row;
    $rowType = uno_renewal_reservation_type_from_category(isset($row['ca_name']) ? $row['ca_name'] : '');
    if (!isset($reservationGroups[$rowType])) {
        $rowType = 'other';
    }
    $reservationGroups[$rowType]['rows'][] = $row;
}

if ($type === 'semi') {
    $reservationGroups = array('semi' => $reservationGroups['semi']);
} elseif ($type === 'daily') {
    $reservationGroups = array('daily' => $reservationGroups['daily']);
} else {
    $reservationGroups = array_filter($reservationGroups, function ($group) {
        return count($group['rows']) > 0 || $group['label'] !== '기타 예약내역';
    });
}

$calendarStep = $calendarMode === 'week' ? 'week' : 'month';
$calendarPrevDate = date('Y-m-d', strtotime('-1 ' . $calendarStep, strtotime($calendarDate)));
$calendarNextDate = date('Y-m-d', strtotime('+1 ' . $calendarStep, strtotime($calendarDate)));
$calendarBaseParams = array(
    'type' => $type,
    'status' => $status,
    'keyword' => $keyword,
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
);
$listBaseParams = array_merge($calendarBaseParams, array(
    'calendarMode' => $calendarMode,
    'calendarDate' => $calendarDate,
));
$calendarWeekdays = array('일', '월', '화', '수', '목', '금', '토');

uno_renewal_admin_render_head('UNO Renewal Reservations');
uno_renewal_admin_render_header();
uno_renewal_admin_render_pagehead(
    'UNO Travel Renewal Admin',
    'Reservation<br>Control',
    '기존 tour_reg 예약 데이터를 유지하면서 예약 대기, 입금 확인, 확정, 취소 흐름을 보기 쉽게 정리합니다. 상세 수정은 기존 예약관리 화면과 연결합니다.',
    array(
        array('label' => '기존 예약관리', 'href' => '/admin/booking.php', 'secondary' => true),
        array('label' => '예약 캘린더', 'href' => '/admin/regist_calendar.php', 'secondary' => true),
        array('label' => '관리자 1:1 문의', 'href' => '/admin/renewal/inquiries.php?board=cusTour', 'secondary' => true),
        array('label' => 'KSNET 결제 내역', 'href' => '/admin/renewal/payments.php', 'secondary' => true),
    )
);
?>

    <style>
      .reservation-filter { display: grid; grid-template-columns: 150px 150px 150px 150px minmax(0, 1fr) auto; gap: 10px; align-items: end; }
      .reservation-filter label { display: grid; gap: 6px; color: var(--uno-muted); font-size: 11px; font-weight: 900; letter-spacing: 1.8px; text-transform: uppercase; }
      .reservation-filter select,
      .reservation-filter input { min-height: 42px; border: 1px solid var(--uno-line); background: #fff; padding: 0 12px; }
      .reservation-calendar { margin-top: 18px; border: 1px solid var(--uno-line); background: #fff; padding: 18px; }
      .reservation-calendar-head { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 14px; }
      .reservation-calendar-title { margin: 0; font-size: 24px; }
      .reservation-calendar-actions { display: flex; flex-wrap: wrap; gap: 8px; }
      .reservation-calendar-lanes { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
      .reservation-calendar-lane { min-width: 0; border: 1px solid var(--uno-line); background: #fafaf8; padding: 12px; }
      .reservation-calendar-lane h3 { margin: 0 0 10px; font-size: 18px; }
      .reservation-calendar-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); width: 100%; border-top: 1px solid var(--uno-line); border-left: 1px solid var(--uno-line); overflow: hidden; }
      .reservation-calendar-weekday,
      .reservation-calendar-day { min-width: 0; border-right: 1px solid var(--uno-line); border-bottom: 1px solid var(--uno-line); background: #fff; }
      .reservation-calendar-weekday { padding: 7px 4px; color: var(--uno-muted); font-size: 11px; font-weight: 900; text-align: center; letter-spacing: 1.4px; }
      .reservation-calendar-day { min-height: 118px; padding: 8px; overflow: hidden; }
      .reservation-calendar-day.is-outside { background: #f4f4f1; color: var(--uno-muted); }
      .reservation-calendar-date { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 4px; font-weight: 900; }
      .reservation-calendar-total { color: var(--uno-muted); font-size: 11px; line-height: 1.3; }
      .reservation-calendar-item { display: block; margin-top: 8px; padding: 8px; border: 1px solid var(--uno-line); background: #fff; line-height: 1.35; }
      .reservation-calendar-item strong { display: block; font-size: 12px; overflow-wrap: anywhere; word-break: keep-all; }
      .reservation-calendar-item span { display: block; margin-top: 4px; color: var(--uno-muted); font-size: 11px; }
      .reservation-cards { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; margin-top: 18px; }
      .reservation-card { border: 1px solid var(--uno-line); background: #fff; padding: 16px; }
      .reservation-card span { color: var(--uno-muted); font-size: 11px; font-weight: 900; letter-spacing: 1.8px; text-transform: uppercase; }
      .reservation-card strong { display: block; margin-top: 10px; font-size: 32px; line-height: 1; }
      .reservation-table-wrap { margin-top: 18px; border: 1px solid var(--uno-line); background: #fff; overflow-x: auto; }
      .reservation-table-wrap + .reservation-table-wrap { margin-top: 14px; }
      .reservation-table-heading { display: flex; justify-content: space-between; gap: 12px; align-items: center; padding: 16px; border-bottom: 1px solid var(--uno-line); background: #fafaf8; }
      .reservation-table-heading h2 { margin: 0; font-size: 20px; }
      .reservation-table-heading span { color: var(--uno-muted); font-size: 12px; font-weight: 900; }
      .reservation-pager { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-top: 18px; }
      .reservation-pager span { min-height: 40px; display: inline-flex; align-items: center; padding: 0 12px; color: var(--uno-muted); font-size: 12px; font-weight: 900; }
      .reservation-table { width: 100%; min-width: 980px; border-collapse: collapse; }
      .reservation-table th,
      .reservation-table td { padding: 14px 12px; border-bottom: 1px solid var(--uno-line); text-align: left; vertical-align: top; }
      .reservation-table th { color: var(--uno-muted); font-size: 11px; font-weight: 900; letter-spacing: 1.8px; text-transform: uppercase; background: #fafaf8; }
      .reservation-table td { line-height: 1.45; }
      .reservation-title { font-weight: 900; }
      .reservation-sub { margin-top: 4px; color: var(--uno-muted); font-size: 13px; }
      .reservation-status { display: inline-flex; min-height: 30px; align-items: center; padding: 0 10px; border: 1px solid var(--uno-line); font-weight: 900; white-space: nowrap; }
      .reservation-actions { display: flex; flex-wrap: wrap; gap: 8px; }
      .reservation-modal-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
      .reservation-modal-card { border: 1px solid var(--uno-line); background: #fff; padding: 16px; }
      .reservation-modal-card.full { grid-column: 1 / -1; }
      .reservation-modal-card h3 { margin: 0 0 12px; font-size: 18px; }
      .reservation-modal-row { display: grid; grid-template-columns: 110px minmax(0, 1fr); gap: 12px; padding: 8px 0; border-bottom: 1px solid #f0f0ed; }
      .reservation-modal-row:last-child { border-bottom: 0; }
      .reservation-modal-row span { color: var(--uno-muted); font-size: 12px; font-weight: 900; }
      .reservation-form-field { display: grid; gap: 7px; margin-top: 12px; }
      .reservation-form-field label { color: var(--uno-muted); font-size: 11px; font-weight: 900; letter-spacing: 1.8px; text-transform: uppercase; }
      .reservation-form-field select,
      .reservation-form-field textarea { width: 100%; border: 1px solid var(--uno-line); background: #fff; padding: 10px 12px; }
      .reservation-form-field select { min-height: 42px; }
      .reservation-form-field textarea { min-height: 110px; resize: vertical; line-height: 1.55; }
      .reservation-modal-foot { display: flex; justify-content: space-between; gap: 10px; margin-top: 16px; }
      .reservation-tool-actions { display: flex; flex-wrap: wrap; gap: 8px; }
      .reservation-tool-note { margin: 10px 0 0; color: var(--uno-muted); line-height: 1.55; font-size: 13px; }
      @media (max-width: 900px) {
        .reservation-filter,
        .reservation-cards,
        .reservation-calendar-lanes,
        .reservation-modal-grid { grid-template-columns: 1fr; }
      }
    </style>

    <section class="uno-admin-panel">
      <form class="reservation-filter" method="get" action="/admin/renewal/reservations.php">
        <input type="hidden" name="calendarMode" value="<?php echo uno_renewal_admin_escape($calendarMode); ?>">
        <input type="hidden" name="calendarDate" value="<?php echo uno_renewal_admin_escape($calendarDate); ?>">
        <input type="hidden" name="page" value="1">
        <label>상품 유형
          <select name="type">
            <option value="all"<?php echo $type === 'all' ? ' selected' : ''; ?>>전체</option>
            <option value="semi"<?php echo $type === 'semi' ? ' selected' : ''; ?>>세미패키지</option>
            <option value="daily"<?php echo $type === 'daily' ? ' selected' : ''; ?>>데일리투어</option>
          </select>
        </label>
        <label>예약 상태
          <select name="status">
            <option value="all"<?php echo $status === 'all' ? ' selected' : ''; ?>>전체</option>
            <option value="1"<?php echo $status === '1' ? ' selected' : ''; ?>>예약 대기</option>
            <option value="11"<?php echo $status === '11' ? ' selected' : ''; ?>>입금 확인</option>
            <option value="3"<?php echo $status === '3' ? ' selected' : ''; ?>>예약 확정</option>
            <option value="91"<?php echo $status === '91' ? ' selected' : ''; ?>>취소 요청</option>
            <option value="9"<?php echo $status === '9' ? ' selected' : ''; ?>>예약 취소</option>
          </select>
        </label>
        <label>투어 시작일
          <input type="date" name="dateFrom" value="<?php echo uno_renewal_admin_escape($dateFrom); ?>">
        </label>
        <label>투어 종료일
          <input type="date" name="dateTo" value="<?php echo uno_renewal_admin_escape($dateTo); ?>">
        </label>
        <label>검색
          <input type="search" name="keyword" value="<?php echo uno_renewal_admin_escape($keyword); ?>" placeholder="예약번호, 이름, 이메일, 상품명">
        </label>
        <button class="uno-admin-button" type="submit">조회</button>
      </form>

      <div class="reservation-cards">
        <?php foreach ($cards as $card) { ?>
          <a class="reservation-card" href="<?php echo uno_renewal_admin_escape(uno_renewal_reservation_url(array_merge($listBaseParams, array('status' => $card['status'], 'page' => 1)))); ?>">
            <span><?php echo uno_renewal_admin_escape($card['label']); ?></span>
            <strong><?php echo number_format((int) $card['count']); ?></strong>
          </a>
        <?php } ?>
      </div>
    </section>

    <section class="reservation-calendar">
      <div class="reservation-calendar-head">
        <div>
          <p class="uno-admin-eyebrow">Reservation Calendar</p>
          <h2 class="reservation-calendar-title"><?php echo uno_renewal_admin_escape($calendarRange['title']); ?> 예약 현황</h2>
        </div>
        <nav class="reservation-calendar-actions" aria-label="예약 캘린더 보기">
          <a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape(uno_renewal_reservation_url(array_merge($calendarBaseParams, array('calendarMode' => $calendarMode, 'calendarDate' => $calendarPrevDate)))); ?>">이전</a>
          <a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape(uno_renewal_reservation_url(array_merge($calendarBaseParams, array('calendarMode' => 'month', 'calendarDate' => $calendarDate)))); ?>">월간</a>
          <a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape(uno_renewal_reservation_url(array_merge($calendarBaseParams, array('calendarMode' => 'week', 'calendarDate' => $calendarDate)))); ?>">주간</a>
          <a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape(uno_renewal_reservation_url(array_merge($calendarBaseParams, array('calendarMode' => $calendarMode, 'calendarDate' => $calendarNextDate)))); ?>">다음</a>
        </nav>
      </div>

      <div class="reservation-calendar-lanes">
        <?php foreach (array('semi' => '세미패키지', 'daily' => '데일리투어') as $laneType => $laneLabel) { ?>
          <article class="reservation-calendar-lane">
            <h3><?php echo uno_renewal_admin_escape($laneLabel); ?></h3>
            <div class="reservation-calendar-grid">
              <?php foreach ($calendarWeekdays as $weekday) { ?>
                <div class="reservation-calendar-weekday"><?php echo uno_renewal_admin_escape($weekday); ?></div>
              <?php } ?>
              <?php foreach (uno_renewal_reservation_calendar_days($calendarMode, $calendarRange) as $calendarDay) {
                  $dayProducts = isset($calendarRows[$laneType][$calendarDay]) ? $calendarRows[$laneType][$calendarDay] : array();
                  $dayCount = 0;
                  $dayPeople = 0;
                  foreach ($dayProducts as $dayProduct) {
                      $dayCount += (int) $dayProduct['count'];
                      $dayPeople += (int) $dayProduct['people'];
                  }
                  $isOutside = $calendarDay < $calendarRange['start'] || $calendarDay > $calendarRange['end'];
              ?>
                <div class="reservation-calendar-day<?php echo $isOutside ? ' is-outside' : ''; ?>">
                  <div class="reservation-calendar-date">
                    <span><?php echo uno_renewal_admin_escape(substr($calendarDay, 8, 2)); ?></span>
                    <?php if ($dayCount > 0) { ?><span class="reservation-calendar-total"><?php echo number_format($dayCount); ?>건 · <?php echo number_format($dayPeople); ?>명</span><?php } ?>
                  </div>
                  <?php foreach ($dayProducts as $dayProduct) { ?>
                    <a class="reservation-calendar-item" href="<?php echo uno_renewal_admin_escape(uno_renewal_reservation_url(array('type' => $laneType, 'status' => 'all', 'dateFrom' => $calendarDay, 'dateTo' => $calendarDay, 'calendarMode' => $calendarMode, 'calendarDate' => $calendarDate, 'page' => 1))); ?>">
                      <strong><?php echo uno_renewal_admin_escape($dayProduct['title']); ?></strong>
                      <span><?php echo number_format((int) $dayProduct['count']); ?>건 · <?php echo number_format((int) $dayProduct['people']); ?>명 · 확정 <?php echo number_format((int) $dayProduct['confirmed']); ?>건</span>
                    </a>
                  <?php } ?>
                </div>
              <?php } ?>
            </div>
          </article>
        <?php } ?>
      </div>
    </section>

    <?php foreach ($reservationGroups as $reservationGroup) { ?>
      <section class="reservation-table-wrap">
        <div class="reservation-table-heading">
          <h2><?php echo uno_renewal_admin_escape($reservationGroup['label']); ?></h2>
          <span><?php echo number_format(count($reservationGroup['rows'])); ?>건</span>
        </div>
        <table class="reservation-table">
          <thead>
            <tr>
              <th>예약</th>
              <th>상품 / 투어일</th>
              <th>예약자</th>
              <th>금액</th>
              <th>결제</th>
              <th>상태</th>
              <th>관리</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($reservationGroup['rows']) === 0) { ?>
              <tr><td colspan="7">조건에 맞는 예약이 없습니다.</td></tr>
            <?php } ?>
            <?php foreach ($reservationGroup['rows'] as $row) { ?>
              <tr>
                <td>
                  <div class="reservation-title">#<?php echo uno_renewal_admin_escape($row['id']); ?></div>
                  <div class="reservation-sub"><?php echo uno_renewal_admin_escape(uno_renewal_reservation_date(isset($row['regDate']) ? $row['regDate'] : '')); ?></div>
                </td>
                <td>
                  <div class="reservation-title"><?php echo uno_renewal_admin_escape(isset($row['wr_subject']) ? $row['wr_subject'] : '상품명 없음'); ?></div>
                  <div class="reservation-sub"><?php echo uno_renewal_admin_escape(isset($row['ca_name']) ? $row['ca_name'] : ''); ?></div>
                  <div class="reservation-sub">투어일 <?php echo uno_renewal_admin_escape(isset($row['tourDay']) ? substr((string) $row['tourDay'], 0, 10) : '-'); ?></div>
                </td>
                <td>
                  <div class="reservation-title"><?php echo uno_renewal_admin_escape(isset($row['mb_name']) && $row['mb_name'] !== '' ? $row['mb_name'] : $row['mb_id']); ?></div>
                  <div class="reservation-sub"><?php echo uno_renewal_admin_escape(isset($row['mb_email']) ? $row['mb_email'] : ''); ?></div>
                  <div class="reservation-sub"><?php echo uno_renewal_admin_escape(isset($row['mb_hp']) ? $row['mb_hp'] : ''); ?></div>
                </td>
                <td>
                  <div>예약금 <?php echo number_format((int) str_replace(',', '', isset($row['total_fee1']) ? $row['total_fee1'] : 0)); ?>원</div>
                  <div class="reservation-sub">현지 <?php echo uno_renewal_admin_escape(isset($row['total_fee2']) ? $row['total_fee2'] : '0'); ?></div>
                </td>
                <td>
                  <?php if (!empty($row['card_pay'])) { ?>
                    <span class="reservation-status">카드</span>
                    <div class="reservation-sub">승인 <?php echo uno_renewal_admin_escape($row['card_pay']); ?></div>
                  <?php } else { ?>
                    <span class="reservation-status">은행</span>
                    <div class="reservation-sub">입금 확인 필요</div>
                  <?php } ?>
                </td>
                <td><span class="reservation-status"><?php echo uno_renewal_admin_escape(uno_renewal_reservation_status_label(isset($row['status']) ? $row['status'] : '')); ?></span></td>
                <td>
                  <div class="reservation-actions">
                    <button class="uno-admin-button" type="button" data-open-reservation="<?php echo uno_renewal_admin_escape($row['id']); ?>">예약 확인</button>
                    <a class="uno-admin-button secondary" href="/admin/booking.php?rid=<?php echo uno_renewal_admin_escape($row['id']); ?>">기존 상세</a>
                  </div>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </section>
    <?php } ?>

    <nav class="reservation-pager" aria-label="예약내역 페이지">
      <?php if ($page > 1) { ?>
        <a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape(uno_renewal_reservation_url(array_merge($listBaseParams, array('page' => $page - 1)))); ?>">이전 페이지</a>
      <?php } ?>
      <span>총 <?php echo number_format($totalCount); ?>건 · <?php echo number_format($page); ?> / <?php echo number_format($totalPages); ?> 페이지</span>
      <?php if ($page < $totalPages) { ?>
        <a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape(uno_renewal_reservation_url(array_merge($listBaseParams, array('page' => $page + 1)))); ?>">다음 페이지</a>
      <?php } ?>
    </nav>

    <div class="uno-admin-modal" data-reservation-modal aria-hidden="true">
      <section class="uno-admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="reservation-modal-title">
        <div class="uno-admin-modal-head">
          <div>
            <p class="uno-admin-eyebrow">Reservation Detail</p>
            <h2 id="reservation-modal-title" data-reservation-title>예약 확인</h2>
          </div>
          <button class="uno-admin-close" type="button" data-close-reservation>닫기</button>
        </div>
        <p class="uno-admin-status" data-reservation-status>예약 정보를 선택해 주세요.</p>
        <div data-reservation-body></div>
      </section>
    </div>

    <script>
      const reservationModal = document.querySelector("[data-reservation-modal]");
      const reservationTitle = document.querySelector("[data-reservation-title]");
      const reservationStatus = document.querySelector("[data-reservation-status]");
      const reservationBody = document.querySelector("[data-reservation-body]");
      let activeReservation = null;

      const reservationEscape = (value) => String(value ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
      const getCookie = (name) => document.cookie.split(";").map((v) => v.trim()).find((v) => v.indexOf(name + "=") === 0)?.slice(name.length + 1) || "";
      const setReservationStatus = (message, tone = "") => {
        reservationStatus.textContent = message;
        reservationStatus.className = "uno-admin-status" + (tone ? " " + tone : "");
      };
      const statusOptions = (selected) => [["1", "예약 대기"], ["2", "예약 확인"], ["11", "입금 확인"], ["3", "예약 확정"], ["91", "취소 요청"], ["9", "예약 취소"], ["99", "취소 완료"]].map(([value, label]) => '<option value="' + value + '" ' + (String(selected) === value ? "selected" : "") + '>' + label + '</option>').join("");
      const detailRow = (label, value) => '<div class="reservation-modal-row"><span>' + reservationEscape(label) + '</span><strong>' + reservationEscape(value || "-") + '</strong></div>';

      const reservationRequest = async (rid, body = null) => {
        const options = { credentials: "same-origin" };
        if (body) {
          options.method = "POST";
          options.headers = { "Content-Type": "application/json", "X-CSRF-Token": getCookie("unotravel_csrf_token") };
          options.body = JSON.stringify(body);
        }
        const response = await fetch("/api/admin/reservation-editor.php?rid=" + encodeURIComponent(rid), options);
        const json = await response.json();
        if (!json.ok) throw new Error(json.error ? json.error.message : "예약 정보를 처리하지 못했습니다.");
        return json.data;
      };

      const legacyReservationAction = async (payload) => {
        const body = new URLSearchParams();
        Object.entries(payload).forEach(([key, value]) => body.append(key, value));
        const response = await fetch("/admin/include_files/setReg.php", {
          method: "POST",
          credentials: "same-origin",
          headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
          body,
        });
        if (!response.ok) throw new Error("기존 발송 기능을 실행하지 못했습니다.");
        return response.text();
      };

      const ksnetCancelAction = async (payment) => {
        const ksnet = payment?.ksnet || {};
        const body = new URLSearchParams();
        body.append("authty", "1010");
        body.append("trno", payment?.approvalNo || ksnet.approvalNo || "");
        body.append("canc_amt", ksnet.amount || "");
        const response = await fetch("/KSPayCancelPost.php", {
          method: "POST",
          credentials: "same-origin",
          headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
          body,
        });
        const text = (await response.text()).trim();
        if (!response.ok || text !== "O") throw new Error("KSNET 카드 취소가 완료되지 않았습니다. 응답: " + (text || response.status));
        return text;
      };

      const renderPaymentCard = (data) => {
        const payment = data.payment || {};
        const ksnet = payment.ksnet || {};
        const rows = [
          detailRow("결제 방식", payment.methodLabel || "-"),
          detailRow("예약 결제값", payment.approvalNo ? "카드 승인번호 " + payment.approvalNo : "계좌 입금 확인 필요"),
        ];
        if (ksnet.approvalNo) {
          rows.push(detailRow("KSNET 주문번호", ksnet.orderNumber));
          rows.push(detailRow("KSNET 승인번호", ksnet.approvalNo));
          rows.push(detailRow("승인 금액", ksnet.amountLabel));
          rows.push(detailRow("승인 일시", ksnet.approvedAt));
          rows.push(detailRow("취소 일시", ksnet.cancelDate || "취소 내역 없음"));
        }
        const cancelButton = payment.canCancelCard
          ? '<button class="uno-admin-button secondary" type="button" data-cancel-ksnet>신용카드 취소하기</button>'
          : '';
        const note = payment.method === "card"
          ? "카드 결제는 KSNET 승인 내역 기준으로 표시합니다. 취소 버튼은 기존 KSPayCancelPost.php 승인 취소 흐름을 사용합니다."
          : "계좌 입금 예약은 입금 내역 확인 후 관리자가 예약 상태를 확정으로 변경합니다.";
        return '<article class="reservation-modal-card full"><h3>결제 / KSNET</h3>' +
          rows.join("") +
          '<div class="reservation-tool-actions" style="margin-top:12px;">' +
            '<a class="uno-admin-button secondary" href="/admin/renewal/payments.php" target="_blank" rel="noopener">KSNET 결제 내역</a>' +
            cancelButton +
          '</div>' +
          '<p class="reservation-tool-note">' + reservationEscape(note) + '</p>' +
        '</article>';
      };

      const renderReservation = (data) => {
        activeReservation = data;
        reservationTitle.textContent = "예약 #" + data.id;
        reservationBody.innerHTML =
          '<div class="reservation-modal-grid">' +
            '<article class="reservation-modal-card"><h3>상품 정보</h3>' +
              detailRow("상품명", data.product?.title) +
              detailRow("분류", data.product?.category) +
              detailRow("투어일", data.tourDay) +
              detailRow("투어 시간", data.tourTime) +
            '</article>' +
            '<article class="reservation-modal-card"><h3>예약자 정보</h3>' +
              detailRow("이름", data.customer?.name || data.customer?.id) +
              detailRow("연락처", data.customer?.phone) +
              detailRow("이메일", data.customer?.email) +
              detailRow("카카오톡", data.customer?.kakaoId) +
            '</article>' +
            '<article class="reservation-modal-card"><h3>금액 / 상태</h3>' +
              detailRow("예약금", data.price?.depositLabel) +
              detailRow("현지 지불", data.price?.localPayment) +
              detailRow("추가금", data.price?.extraPayment) +
              detailRow("현재 상태", data.statusLabel) +
              '<div class="reservation-form-field"><label>상태 변경</label><select data-reservation-field="status">' + statusOptions(data.status) + '</select></div>' +
            '</article>' +
            '<article class="reservation-modal-card"><h3>요청사항</h3><div style="white-space:pre-wrap;line-height:1.65;color:var(--uno-muted);">' + reservationEscape(data.memo?.request || "등록된 요청사항이 없습니다.") + '</div></article>' +
            renderPaymentCard(data) +
            '<article class="reservation-modal-card full"><h3>바우처 / 알림 발송</h3>' +
              '<div class="reservation-tool-actions">' +
                '<a class="uno-admin-button secondary" href="' + reservationEscape(data.links?.voucherPreview || "#") + '" target="_blank" rel="noopener">바우처 보기</a>' +
                '<button class="uno-admin-button secondary" type="button" data-send-voucher="customer">고객에게 바우처 발송</button>' +
                '<button class="uno-admin-button secondary" type="button" data-send-voucher="admin">관리자에게 바우처 발송</button>' +
                '<button class="uno-admin-button secondary" type="button" data-send-status>현재 상태 알림 발송</button>' +
              '</div>' +
              '<p class="reservation-tool-note">발송 버튼은 기존 우노트래블 setReg.php 흐름을 명시적으로 호출합니다. 상태 저장과 알림 발송은 자동으로 묶지 않았습니다.</p>' +
            '</article>' +
            '<article class="reservation-modal-card full"><h3>관리자 메모</h3>' +
              '<div class="reservation-form-field"><label>관리자 메모</label><textarea data-reservation-field="adminMemo">' + reservationEscape(data.memo?.admin || "") + '</textarea></div>' +
              '<div class="reservation-form-field"><label>취소 / 환불 메모</label><textarea data-reservation-field="adminMemoCancel">' + reservationEscape(data.memo?.cancel || "") + '</textarea></div>' +
              '<div class="reservation-modal-foot"><div class="reservation-tool-actions"><a class="uno-admin-button secondary" href="' + reservationEscape(data.links?.legacyDetail || "#") + '">기존 목록 상세</a><a class="uno-admin-button secondary" href="' + reservationEscape(data.links?.legacyEditPopup || "#") + '">기존 수정 팝업</a></div><button class="uno-admin-button" type="button" data-save-reservation>저장</button></div>' +
            '</article>' +
          '</div>';
      };

      const openReservation = async (rid) => {
        reservationModal.classList.add("is-open");
        reservationModal.setAttribute("aria-hidden", "false");
        reservationTitle.textContent = "예약 확인";
        reservationBody.innerHTML = "";
        setReservationStatus("예약 정보를 불러오는 중입니다.");
        try {
          renderReservation(await reservationRequest(rid));
          setReservationStatus("예약 정보를 불러왔습니다.", "ok");
        } catch (error) {
          setReservationStatus(error.message || "예약 정보를 불러오지 못했습니다.", "warn");
        }
      };

      const closeReservation = () => {
        reservationModal.classList.remove("is-open");
        reservationModal.setAttribute("aria-hidden", "true");
      };

      document.addEventListener("click", async (event) => {
        const openButton = event.target.closest("[data-open-reservation]");
        if (openButton) {
          openReservation(openButton.dataset.openReservation);
          return;
        }
        if (event.target.closest("[data-close-reservation]") || event.target === reservationModal) {
          closeReservation();
          return;
        }
        const saveButton = event.target.closest("[data-save-reservation]");
        if (saveButton && activeReservation) {
          const root = reservationBody;
          const read = (name) => root.querySelector('[data-reservation-field="' + name + '"]')?.value.trim() || "";
          try {
            saveButton.disabled = true;
            setReservationStatus("예약 정보를 저장하는 중입니다.");
            renderReservation(await reservationRequest(activeReservation.id, {
              status: read("status"),
              adminMemo: read("adminMemo"),
              adminMemoCancel: read("adminMemoCancel"),
            }));
            setReservationStatus("예약 정보가 저장되었습니다.", "ok");
          } catch (error) {
            setReservationStatus(error.message || "예약 정보를 저장하지 못했습니다.", "warn");
          } finally {
            saveButton.disabled = false;
          }
          return;
        }
        const ksnetCancelButton = event.target.closest("[data-cancel-ksnet]");
        if (ksnetCancelButton && activeReservation) {
          const payment = activeReservation.payment || {};
          const amountLabel = payment.ksnet?.amountLabel || "";
          if (!window.confirm("KSNET 신용카드 승인을 취소할까요?\\n" + amountLabel + "\\n이 작업은 실제 카드 승인 취소 요청입니다.")) return;
          try {
            ksnetCancelButton.disabled = true;
            setReservationStatus("KSNET 카드 취소를 요청하는 중입니다.");
            await ksnetCancelAction(payment);
            renderReservation(await reservationRequest(activeReservation.id));
            setReservationStatus("KSNET 카드 취소가 완료되었습니다.", "ok");
          } catch (error) {
            setReservationStatus(error.message || "KSNET 카드 취소를 완료하지 못했습니다.", "warn");
          } finally {
            ksnetCancelButton.disabled = false;
          }
          return;
        }
        const voucherButton = event.target.closest("[data-send-voucher]");
        if (voucherButton && activeReservation) {
          const isAdmin = voucherButton.dataset.sendVoucher === "admin";
          const message = isAdmin ? "관리자에게 바우처를 발송할까요?" : "고객에게 바우처를 발송할까요?";
          if (!window.confirm(message)) return;
          try {
            voucherButton.disabled = true;
            setReservationStatus("바우처를 발송하는 중입니다.");
            await legacyReservationAction({ r_id: activeReservation.id, sendV: "Y", ...(isAdmin ? { isAdm: "Y" } : {}) });
            setReservationStatus("바우처 발송 요청이 완료되었습니다.", "ok");
          } catch (error) {
            setReservationStatus(error.message || "바우처를 발송하지 못했습니다.", "warn");
          } finally {
            voucherButton.disabled = false;
          }
          return;
        }
        const statusButton = event.target.closest("[data-send-status]");
        if (statusButton && activeReservation) {
          const statusValue = reservationBody.querySelector('[data-reservation-field="status"]')?.value || activeReservation.status;
          if (!window.confirm("현재 선택한 예약 상태로 기존 알림 발송 흐름을 실행할까요?")) return;
          try {
            statusButton.disabled = true;
            setReservationStatus("상태 알림을 발송하는 중입니다.");
            await legacyReservationAction({ r_id: activeReservation.id, sel: statusValue });
            renderReservation(await reservationRequest(activeReservation.id));
            setReservationStatus("상태 알림 발송 요청이 완료되었습니다.", "ok");
          } catch (error) {
            setReservationStatus(error.message || "상태 알림을 발송하지 못했습니다.", "warn");
          } finally {
            statusButton.disabled = false;
          }
        }
      });

      document.addEventListener("keydown", (event) => { if (event.key === "Escape") closeReservation(); });
    </script>

<?php
uno_renewal_admin_render_footer();
