<?php
/*
 * payments.php
 * Renewal admin KSNET payment list.
 * It reads kspay_result and links each approval back to the renewal reservation detail flow.
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

uno_renewal_admin_require_access('/admin/renewal/payments.php');

function uno_renewal_payment_escape_db($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_renewal_payment_fetch($sql)
{
    if (!function_exists('sql_fetch')) {
        return array();
    }

    $row = sql_fetch($sql);
    return is_array($row) ? $row : array();
}

function uno_renewal_payment_query($sql)
{
    return function_exists('sql_query') ? sql_query($sql) : false;
}

function uno_renewal_payment_fetch_array($result)
{
    return $result && function_exists('sql_fetch_array') ? sql_fetch_array($result) : false;
}

function uno_renewal_payment_table_exists($tableName)
{
    $safeTable = uno_renewal_payment_escape_db($tableName);
    $row = uno_renewal_payment_fetch("show tables like '{$safeTable}'");
    return is_array($row) && count($row) > 0;
}

function uno_renewal_payment_date_param($key, $fallback)
{
    $value = isset($_GET[$key]) ? trim((string) $_GET[$key]) : '';
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : $fallback;
}

function uno_renewal_payment_money($value)
{
    return number_format((int) preg_replace('/[^0-9-]/', '', (string) $value));
}

function uno_renewal_payment_order_rid($orderNumber)
{
    $parts = explode('_', (string) $orderNumber);
    return isset($parts[1]) && preg_match('/^\d+$/', $parts[1]) ? (int) $parts[1] : 0;
}

$hasPaymentTable = uno_renewal_payment_table_exists('kspay_result');
$dateTo = uno_renewal_payment_date_param('dateTo', date('Y-m-d'));
$dateFrom = uno_renewal_payment_date_param('dateFrom', date('Y-m-d', strtotime('-30 days')));
$keyword = isset($_GET['keyword']) ? trim((string) $_GET['keyword']) : '';
$status = isset($_GET['status']) ? trim((string) $_GET['status']) : 'all';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

$where = array('1=1');
if ($dateFrom !== '') {
    $where[] = "left(k.AppDate, 8) >= '" . date('Ymd', strtotime($dateFrom)) . "'";
}
if ($dateTo !== '') {
    $where[] = "left(k.AppDate, 8) <= '" . date('Ymd', strtotime($dateTo)) . "'";
}
if ($keyword !== '') {
    $safeKeyword = uno_renewal_payment_escape_db($keyword);
    $where[] = "(k.OrderNumber like '%{$safeKeyword}%' or k.ApplNum like '%{$safeKeyword}%' or m.mb_name like '%{$safeKeyword}%' or m.mb_id like '%{$safeKeyword}%' or p.wr_subject like '%{$safeKeyword}%')";
}
if ($status === 'cancelled') {
    $where[] = "k.CancelDate <> ''";
} elseif ($status === 'approved') {
    $where[] = "(k.CancelDate = '' or k.CancelDate is null)";
}
$whereSql = implode(' and ', $where);

$rows = array();
$totalCount = 0;
$approvedTotal = 0;
$cancelledCount = 0;

if ($hasPaymentTable) {
    $totalRow = uno_renewal_payment_fetch(
        "select count(*) as cnt, coalesce(sum(case when k.CancelDate = '' or k.CancelDate is null then k.TotPrice else 0 end), 0) as total
           from kspay_result k
           left join tour_reg r on r.card_pay = k.ApplNum
           left join g5_member m on m.mb_id = r.mb_id
           left join g5_write_product p on p.wr_id = r.pid
          where {$whereSql}"
    );
    $cancelRow = uno_renewal_payment_fetch(
        "select count(*) as cnt
           from kspay_result k
           left join tour_reg r on r.card_pay = k.ApplNum
           left join g5_member m on m.mb_id = r.mb_id
           left join g5_write_product p on p.wr_id = r.pid
          where {$whereSql}
            and k.CancelDate <> ''"
    );
    $totalCount = isset($totalRow['cnt']) ? (int) $totalRow['cnt'] : 0;
    $approvedTotal = isset($totalRow['total']) ? (int) $totalRow['total'] : 0;
    $cancelledCount = isset($cancelRow['cnt']) ? (int) $cancelRow['cnt'] : 0;
    $totalPages = max(1, (int) ceil($totalCount / $perPage));
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $perPage;
    }

    $result = uno_renewal_payment_query(
        "select k.*, r.id as reservation_id, r.mb_id, r.mb_name, r.pid, p.wr_subject, p.ca_name
           from kspay_result k
           left join tour_reg r on r.card_pay = k.ApplNum
           left join g5_member m on m.mb_id = r.mb_id
           left join g5_write_product p on p.wr_id = r.pid
          where {$whereSql}
          group by k.OrderNumber
          order by k.AppDate desc, k.id desc
          limit {$offset}, {$perPage}"
    );

    while ($row = uno_renewal_payment_fetch_array($result)) {
        $rows[] = $row;
    }
} else {
    $totalPages = 1;
}

$queryBase = array(
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
    'keyword' => $keyword,
    'status' => $status,
);

uno_renewal_admin_render_head('UNO Renewal KSNET Payments');
uno_renewal_admin_render_header();
uno_renewal_admin_render_pagehead(
    'PAYMENTS',
    'KSNET<br>Payments',
    'KSNET 카드 결제 승인 내역을 리뉴얼 관리자 화면에서 확인합니다. 실제 카드 승인 취소는 예약 상세의 명시적 취소 버튼에서만 실행합니다.',
    array(
        array('label' => '예약 목록', 'href' => '/admin/renewal/reservations.php', 'secondary' => true),
        array('label' => '기존 KSNET 보기', 'href' => '/admin/kscardPayList.php', 'secondary' => true),
    )
);
?>

<style>
  .payment-filter { display:grid; grid-template-columns: 160px 160px 160px minmax(220px, 1fr) auto; gap:10px; align-items:end; }
  .payment-filter label { display:grid; gap:6px; color:var(--uno-muted); font-size:11px; font-weight:900; letter-spacing:1.8px; text-transform:uppercase; }
  .payment-filter input, .payment-filter select { min-height:42px; border:1px solid var(--uno-line); background:#fff; padding:0 12px; }
  .payment-summary { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:10px; margin-top:16px; }
  .payment-table-wrap { margin-top:16px; border:1px solid var(--uno-line); background:#fff; overflow:auto; }
  .payment-table { width:100%; min-width:1060px; border-collapse:collapse; }
  .payment-table th, .payment-table td { padding:13px 12px; border-bottom:1px solid var(--uno-line); text-align:left; vertical-align:top; }
  .payment-table th { color:var(--uno-muted); font-size:11px; font-weight:900; letter-spacing:1.7px; text-transform:uppercase; background:#fafaf8; }
  .payment-title { font-weight:900; }
  .payment-sub { margin-top:4px; color:var(--uno-muted); font-size:12px; line-height:1.45; }
  .payment-pager { display:flex; flex-wrap:wrap; justify-content:center; gap:8px; margin-top:16px; }
  .payment-pager span { min-height:40px; display:inline-flex; align-items:center; color:var(--uno-muted); font-size:12px; font-weight:900; }
  @media (max-width: 900px) {
    .payment-filter, .payment-summary { grid-template-columns:1fr; }
  }
</style>

<section class="uno-admin-panel">
  <form class="payment-filter" method="get" action="/admin/renewal/payments.php">
    <input type="hidden" name="page" value="1">
    <label>시작일
      <input type="date" name="dateFrom" value="<?php echo uno_renewal_admin_escape($dateFrom); ?>">
    </label>
    <label>종료일
      <input type="date" name="dateTo" value="<?php echo uno_renewal_admin_escape($dateTo); ?>">
    </label>
    <label>상태
      <select name="status">
        <option value="all"<?php echo $status === 'all' ? ' selected' : ''; ?>>전체</option>
        <option value="approved"<?php echo $status === 'approved' ? ' selected' : ''; ?>>승인</option>
        <option value="cancelled"<?php echo $status === 'cancelled' ? ' selected' : ''; ?>>취소</option>
      </select>
    </label>
    <label>검색
      <input type="search" name="keyword" value="<?php echo uno_renewal_admin_escape($keyword); ?>" placeholder="주문번호, 승인번호, 예약자, 상품명">
    </label>
    <button class="uno-admin-button" type="submit">조회</button>
  </form>

  <div class="payment-summary">
    <article class="uno-admin-card"><span>조회 건수</span><h3><?php echo number_format($totalCount); ?>건</h3></article>
    <article class="uno-admin-card"><span>승인 금액</span><h3><?php echo number_format($approvedTotal); ?>원</h3></article>
    <article class="uno-admin-card"><span>취소 건수</span><h3><?php echo number_format($cancelledCount); ?>건</h3></article>
  </div>

  <?php if (!$hasPaymentTable) { ?>
    <p class="uno-admin-status warn" style="margin-top:16px;">현재 DB에 <code>kspay_result</code> 테이블이 없어 결제 내역을 표시할 수 없습니다.</p>
  <?php } ?>
</section>

<section class="payment-table-wrap">
  <table class="payment-table">
    <thead>
      <tr>
        <th>결제일시</th>
        <th>주문 / 승인</th>
        <th>예약자</th>
        <th>예약상품</th>
        <th>결제금액</th>
        <th>상태</th>
        <th>관리</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows) { ?>
        <tr><td colspan="7">조건에 맞는 KSNET 결제 내역이 없습니다.</td></tr>
      <?php } ?>
      <?php foreach ($rows as $row) {
          $rid = isset($row['reservation_id']) && $row['reservation_id'] ? (int) $row['reservation_id'] : uno_renewal_payment_order_rid($row['OrderNumber'] ?? '');
          $isCancelled = isset($row['CancelDate']) && trim((string) $row['CancelDate']) !== '';
      ?>
        <tr>
          <td>
            <div class="payment-title"><?php echo uno_renewal_admin_escape($row['AppDate'] ?? '-'); ?></div>
            <div class="payment-sub"><?php echo uno_renewal_admin_escape($row['PayMethod'] ?? '신용카드'); ?></div>
          </td>
          <td>
            <div class="payment-title"><?php echo uno_renewal_admin_escape($row['OrderNumber'] ?? '-'); ?></div>
            <div class="payment-sub">승인 <?php echo uno_renewal_admin_escape($row['ApplNum'] ?? '-'); ?></div>
          </td>
          <td>
            <div class="payment-title"><?php echo uno_renewal_admin_escape($row['mb_name'] ?? '-'); ?></div>
            <div class="payment-sub"><?php echo uno_renewal_admin_escape($row['mb_id'] ?? ''); ?></div>
          </td>
          <td>
            <div class="payment-title">[<?php echo uno_renewal_admin_escape($row['ca_name'] ?? ''); ?>] <?php echo uno_renewal_admin_escape($row['wr_subject'] ?? '-'); ?></div>
          </td>
          <td><?php echo uno_renewal_payment_money($row['TotPrice'] ?? 0); ?>원</td>
          <td>
            <span class="uno-admin-chip <?php echo $isCancelled ? 'warn' : 'good'; ?>"><?php echo $isCancelled ? '취소' : '승인'; ?></span>
            <?php if ($isCancelled) { ?><div class="payment-sub"><?php echo uno_renewal_admin_escape($row['CancelDate']); ?></div><?php } ?>
          </td>
          <td>
            <?php if ($rid > 0) { ?>
              <a class="uno-admin-button secondary" href="/admin/renewal/reservations.php?keyword=<?php echo rawurlencode((string) $rid); ?>">예약 보기</a>
            <?php } ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</section>

<nav class="payment-pager" aria-label="KSNET 결제 페이지">
  <?php if ($page > 1) { ?><a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape('/admin/renewal/payments.php?' . http_build_query(array_merge($queryBase, array('page' => $page - 1)))); ?>">이전 페이지</a><?php } ?>
  <span><?php echo number_format($page); ?> / <?php echo number_format($totalPages); ?> 페이지</span>
  <?php if ($page < $totalPages) { ?><a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape('/admin/renewal/payments.php?' . http_build_query(array_merge($queryBase, array('page' => $page + 1)))); ?>">다음 페이지</a><?php } ?>
</nav>

<?php
uno_renewal_admin_render_footer();
