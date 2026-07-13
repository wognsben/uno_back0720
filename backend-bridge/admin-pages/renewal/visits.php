<?php
/*
 * visits.php
 * Renewal admin visitor analytics page.
 * It reads Gnuboard visit tables and keeps legacy visit pages as optional fallback links.
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

uno_renewal_admin_require_access('/admin/renewal/visits.php');

function uno_renewal_visit_escape_db($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_renewal_visit_fetch($sql)
{
    if (!function_exists('sql_fetch')) {
        return array();
    }

    $row = sql_fetch($sql);
    return is_array($row) ? $row : array();
}

function uno_renewal_visit_query($sql)
{
    return function_exists('sql_query') ? sql_query($sql) : false;
}

function uno_renewal_visit_fetch_array($result)
{
    return $result && function_exists('sql_fetch_array') ? sql_fetch_array($result) : false;
}

function uno_renewal_visit_table_exists($tableName)
{
    $safeTable = uno_renewal_visit_escape_db($tableName);
    $row = uno_renewal_visit_fetch("show tables like '{$safeTable}'");
    return is_array($row) && count($row) > 0;
}

function uno_renewal_visit_date_param($key, $fallback)
{
    $value = isset($_GET[$key]) ? trim((string) $_GET[$key]) : '';
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : $fallback;
}

function uno_renewal_visit_percent($value, $max)
{
    $max = max(1, (int) $max);
    return max(2, min(100, round(((int) $value / $max) * 100)));
}

$view = isset($_GET['view']) ? trim((string) $_GET['view']) : 'date';
$allowedViews = array('list' => true, 'date' => true, 'week' => true, 'month' => true, 'browser' => true);
if (!isset($allowedViews[$view])) {
    $view = 'date';
}
$dateTo = uno_renewal_visit_date_param('dateTo', date('Y-m-d'));
$dateFrom = uno_renewal_visit_date_param('dateFrom', date('Y-m-d', strtotime('-30 days')));
$safeDateFrom = uno_renewal_visit_escape_db($dateFrom);
$safeDateTo = uno_renewal_visit_escape_db($dateTo);
$hasVisitSum = uno_renewal_visit_table_exists('g5_visit_sum');
$hasVisit = uno_renewal_visit_table_exists('g5_visit');

$today = date('Y-m-d');
$todayRow = $hasVisitSum ? uno_renewal_visit_fetch("select vs_count from g5_visit_sum where vs_date = '{$today}'") : array();
$rangeRow = $hasVisitSum
    ? uno_renewal_visit_fetch("select coalesce(sum(vs_count), 0) as cnt from g5_visit_sum where vs_date >= '{$safeDateFrom}' and vs_date <= '{$safeDateTo}'")
    : array();
$totalRow = $hasVisitSum ? uno_renewal_visit_fetch("select coalesce(sum(vs_count), 0) as cnt from g5_visit_sum") : array();
$maxDayRow = $hasVisitSum
    ? uno_renewal_visit_fetch("select coalesce(max(vs_count), 0) as cnt from g5_visit_sum where vs_date >= '{$safeDateFrom}' and vs_date <= '{$safeDateTo}'")
    : array();

$rows = array();
$maxCount = 1;
if ($view === 'date' && $hasVisitSum) {
    $result = uno_renewal_visit_query(
        "select vs_date as label, vs_count as cnt
           from g5_visit_sum
          where vs_date >= '{$safeDateFrom}'
            and vs_date <= '{$safeDateTo}'
          order by vs_date desc"
    );
} elseif ($view === 'week' && $hasVisitSum) {
    $result = uno_renewal_visit_query(
        "select concat(year(vs_date), '-W', lpad(week(vs_date, 1), 2, '0')) as label, sum(vs_count) as cnt
           from g5_visit_sum
          where vs_date >= '{$safeDateFrom}'
            and vs_date <= '{$safeDateTo}'
          group by year(vs_date), week(vs_date, 1)
          order by year(vs_date) desc, week(vs_date, 1) desc"
    );
} elseif ($view === 'month' && $hasVisitSum) {
    $result = uno_renewal_visit_query(
        "select left(vs_date, 7) as label, sum(vs_count) as cnt
           from g5_visit_sum
          where vs_date >= '{$safeDateFrom}'
            and vs_date <= '{$safeDateTo}'
          group by left(vs_date, 7)
          order by left(vs_date, 7) desc"
    );
} elseif ($view === 'browser' && $hasVisit) {
    $result = uno_renewal_visit_query(
        "select vi_browser as label, count(*) as cnt
           from g5_visit
          where vi_date >= '{$safeDateFrom}'
            and vi_date <= '{$safeDateTo}'
          group by vi_browser
          order by cnt desc, vi_browser asc
          limit 50"
    );
} elseif ($view === 'list' && $hasVisit) {
    $result = uno_renewal_visit_query(
        "select concat(vi_date, ' ', vi_time) as label, vi_ip, vi_referer, vi_browser, vi_os, vi_device
           from g5_visit
          where vi_date >= '{$safeDateFrom}'
            and vi_date <= '{$safeDateTo}'
          order by vi_id desc
          limit 100"
    );
} else {
    $result = false;
}

while ($row = uno_renewal_visit_fetch_array($result)) {
    $rows[] = $row;
    if (isset($row['cnt'])) {
        $maxCount = max($maxCount, (int) $row['cnt']);
    }
}

$tabs = array(
    'date' => '일별 접속 통계',
    'week' => '주간 접속 통계',
    'month' => '월간 접속 통계',
    'browser' => '브라우저 통계',
    'list' => '접속자 목록',
);

uno_renewal_admin_render_head('UNO Renewal Visits');
uno_renewal_admin_render_header();
uno_renewal_admin_render_pagehead(
    'VISITORS',
    'Visitor<br>Analytics',
    '백엔드 대시보드 방문자 현황입니다. Google Analytics 또는 외부 접속로그 분석 시스템과 데이터가 다를 수 있습니다.',
    array(
        array('label' => '대시보드', 'href' => '/admin/renewal/index.php', 'secondary' => true),
        array('label' => '기존 접속자 목록', 'href' => '/admin/visit_list.php', 'secondary' => true),
    )
);
?>

<style>
  .visit-filter { display:grid; grid-template-columns: 160px 160px auto; gap:10px; align-items:end; }
  .visit-filter label { display:grid; gap:6px; color:var(--uno-muted); font-size:11px; font-weight:900; letter-spacing:1.8px; text-transform:uppercase; }
  .visit-filter input { min-height:42px; border:1px solid var(--uno-line); background:#fff; padding:0 12px; }
  .visit-tabs { display:flex; flex-wrap:wrap; gap:8px; margin-top:16px; }
  .visit-summary { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; margin-top:16px; }
  .visit-table-wrap { margin-top:16px; border:1px solid var(--uno-line); background:#fff; overflow:auto; }
  .visit-table { width:100%; min-width:880px; border-collapse:collapse; }
  .visit-table th, .visit-table td { padding:12px; border-bottom:1px solid var(--uno-line); text-align:left; vertical-align:top; }
  .visit-table th { color:var(--uno-muted); font-size:11px; font-weight:900; letter-spacing:1.6px; text-transform:uppercase; background:#fafaf8; }
  .visit-bar { width:100%; height:8px; margin-top:7px; background:#f0f0ed; overflow:hidden; }
  .visit-bar span { display:block; height:100%; background:var(--uno-good); }
  .visit-sub { color:var(--uno-muted); font-size:12px; line-height:1.45; }
  @media (max-width: 900px) {
    .visit-filter, .visit-summary { grid-template-columns:1fr; }
  }
</style>

<section class="uno-admin-panel">
  <form class="visit-filter" method="get" action="/admin/renewal/visits.php">
    <input type="hidden" name="view" value="<?php echo uno_renewal_admin_escape($view); ?>">
    <label>시작일
      <input type="date" name="dateFrom" value="<?php echo uno_renewal_admin_escape($dateFrom); ?>">
    </label>
    <label>종료일
      <input type="date" name="dateTo" value="<?php echo uno_renewal_admin_escape($dateTo); ?>">
    </label>
    <button class="uno-admin-button" type="submit">조회</button>
  </form>

  <nav class="visit-tabs" aria-label="방문 통계 보기">
    <?php foreach ($tabs as $tabKey => $tabLabel) { ?>
      <a class="uno-admin-button <?php echo $view === $tabKey ? '' : 'secondary'; ?>" href="/admin/renewal/visits.php?view=<?php echo rawurlencode($tabKey); ?>&dateFrom=<?php echo rawurlencode($dateFrom); ?>&dateTo=<?php echo rawurlencode($dateTo); ?>">
        <?php echo uno_renewal_admin_escape($tabLabel); ?>
      </a>
    <?php } ?>
  </nav>

  <div class="visit-summary">
    <article class="uno-admin-card"><span>오늘 방문</span><h3><?php echo number_format((int) ($todayRow['vs_count'] ?? 0)); ?></h3></article>
    <article class="uno-admin-card"><span>조회 기간 합계</span><h3><?php echo number_format((int) ($rangeRow['cnt'] ?? 0)); ?></h3></article>
    <article class="uno-admin-card"><span>전체 누적</span><h3><?php echo number_format((int) ($totalRow['cnt'] ?? 0)); ?></h3></article>
  </div>

  <p class="uno-admin-status" style="margin-top:16px;">Google Analytics 또는 외부 접속로그 분석 시스템과 데이터가 다를 수 있습니다.</p>
</section>

<section class="visit-table-wrap">
  <table class="visit-table">
    <thead>
      <?php if ($view === 'list') { ?>
        <tr><th>접속일시</th><th>IP</th><th>환경</th><th>유입</th></tr>
      <?php } else { ?>
        <tr><th><?php echo uno_renewal_admin_escape($tabs[$view]); ?></th><th>방문수</th><th>비중</th></tr>
      <?php } ?>
    </thead>
    <tbody>
      <?php if (!$rows) { ?>
        <tr><td colspan="<?php echo $view === 'list' ? 4 : 3; ?>">방문 통계 데이터가 없습니다.</td></tr>
      <?php } ?>
      <?php foreach ($rows as $row) { ?>
        <?php if ($view === 'list') { ?>
          <tr>
            <td><?php echo uno_renewal_admin_escape($row['label'] ?? '-'); ?></td>
            <td><?php echo uno_renewal_admin_escape($row['vi_ip'] ?? '-'); ?></td>
            <td>
              <strong><?php echo uno_renewal_admin_escape($row['vi_browser'] ?? '-'); ?></strong>
              <div class="visit-sub"><?php echo uno_renewal_admin_escape(($row['vi_os'] ?? '-') . ' / ' . ($row['vi_device'] ?? '-')); ?></div>
            </td>
            <td class="visit-sub"><?php echo uno_renewal_admin_escape($row['vi_referer'] ?? '-'); ?></td>
          </tr>
        <?php } else {
            $count = isset($row['cnt']) ? (int) $row['cnt'] : 0;
            $percent = uno_renewal_visit_percent($count, $maxCount);
        ?>
          <tr>
            <td><strong><?php echo uno_renewal_admin_escape($row['label'] ?? '-'); ?></strong></td>
            <td><?php echo number_format($count); ?></td>
            <td><div class="visit-bar"><span style="width:<?php echo $percent; ?>%;"></span></div></td>
          </tr>
        <?php } ?>
      <?php } ?>
    </tbody>
  </table>
</section>

<?php
uno_renewal_admin_render_footer();
