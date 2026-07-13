<?php
/*
 * member-detail.php
 * Renewal admin read-only member detail page.
 * Editing remains connected to the legacy Gnuboard member form for safety.
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

uno_renewal_admin_require_access('/admin/renewal/member-detail.php');

function uno_renewal_member_detail_escape_db($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_renewal_member_detail_table()
{
    global $g5;
    return isset($g5['member_table']) && $g5['member_table'] !== '' ? $g5['member_table'] : 'g5_member';
}

function uno_renewal_member_detail_fetch($sql)
{
    if (!function_exists('sql_fetch')) {
        return array();
    }

    $row = sql_fetch($sql);
    return is_array($row) ? $row : array();
}

function uno_renewal_member_detail_query($sql)
{
    return function_exists('sql_query') ? sql_query($sql) : false;
}

function uno_renewal_member_detail_fetch_array($result)
{
    return $result && function_exists('sql_fetch_array') ? sql_fetch_array($result) : false;
}

function uno_renewal_member_detail_level_label($level)
{
    $level = (int) $level;
    if ($level >= 9 || $level === 3) {
        return '관리자';
    }
    if ($level === 5) {
        return 'B2B';
    }
    if ($level === 4) {
        return '가이드';
    }

    return '일반';
}

function uno_renewal_member_detail_date($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '-';
    }

    if (preg_match('/^\d{8}$/', $value)) {
        return substr($value, 0, 4) . '-' . substr($value, 4, 2) . '-' . substr($value, 6, 2);
    }

    return substr($value, 0, 16);
}

function uno_renewal_member_detail_money($value)
{
    return number_format((int) preg_replace('/[^0-9-]/', '', (string) $value)) . '원';
}

$memberId = isset($_GET['mb_id']) ? trim((string) $_GET['mb_id']) : '';
if ($memberId === '') {
    $memberId = isset($_GET['keyword']) ? trim((string) $_GET['keyword']) : '';
}

$memberTable = uno_renewal_member_detail_table();
$safeMemberId = uno_renewal_member_detail_escape_db($memberId);
$memberRow = $memberId !== ''
    ? uno_renewal_member_detail_fetch(
        "select mb_no, mb_id, mb_name, mb_nick, mb_email, mb_hp, mb_1, mb_level, mb_datetime, mb_today_login,
                mb_intercept_date, mb_leave_date, mb_mailling
           from {$memberTable}
          where mb_id = '{$safeMemberId}'
          limit 1"
    )
    : array();

$reservationSummary = array('total' => 0, 'active' => 0, 'confirmed' => 0, 'cancelled' => 0);
$reservationRows = array();
if ($memberRow) {
    $summary = uno_renewal_member_detail_fetch(
        "select count(*) as total,
                sum(case when status in ('1', '2', '11', '3', '91') then 1 else 0 end) as active,
                sum(case when status = '3' then 1 else 0 end) as confirmed,
                sum(case when status in ('9', '99') then 1 else 0 end) as cancelled
           from tour_reg
          where mb_id = '{$safeMemberId}'
            and status not in ('cart', 'booking')"
    );
    $reservationSummary = array(
        'total' => isset($summary['total']) ? (int) $summary['total'] : 0,
        'active' => isset($summary['active']) ? (int) $summary['active'] : 0,
        'confirmed' => isset($summary['confirmed']) ? (int) $summary['confirmed'] : 0,
        'cancelled' => isset($summary['cancelled']) ? (int) $summary['cancelled'] : 0,
    );

    $result = uno_renewal_member_detail_query(
        "select r.id, r.regDate, r.tourDay, r.status, r.total_fee1, r.total_fee2, r.card_pay,
                p.wr_subject, p.ca_name
           from tour_reg r
           left join g5_write_product p on p.wr_id = r.pid
          where r.mb_id = '{$safeMemberId}'
            and r.status not in ('cart', 'booking')
          order by r.id desc
          limit 20"
    );
    while ($row = uno_renewal_member_detail_fetch_array($result)) {
        $reservationRows[] = $row;
    }
}

uno_renewal_admin_render_head('UNO Renewal Member Detail');
uno_renewal_admin_render_header();
uno_renewal_admin_render_pagehead(
    'MEMBER',
    'Member<br>Detail',
    '회원 정보를 리뉴얼 관리자 화면에서 확인합니다. 실제 회원 정보 수정과 권한 변경은 안전하게 기존 회원 수정 화면에서 처리합니다.',
    array(
        array('label' => '회원 목록', 'href' => '/admin/renewal/members.php', 'secondary' => true),
        array('label' => '기존 회원 수정', 'href' => '/admin/member_form.php?w=u&mb_id=' . rawurlencode($memberId), 'secondary' => true),
    )
);
?>

<style>
  .member-detail-grid { display:grid; grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr); gap:16px; }
  .member-detail-card { border:1px solid var(--uno-line); background:#fff; padding:18px; }
  .member-detail-card h2 { margin:0 0 14px; font-size:22px; }
  .member-detail-row { display:grid; grid-template-columns:130px minmax(0, 1fr); gap:12px; padding:9px 0; border-bottom:1px solid #f0f0ed; }
  .member-detail-row:last-child { border-bottom:0; }
  .member-detail-row span { color:var(--uno-muted); font-size:12px; font-weight:900; }
  .member-detail-actions { display:flex; flex-wrap:wrap; gap:8px; margin-top:14px; }
  .member-detail-table { width:100%; border-collapse:collapse; min-width:840px; }
  .member-detail-table th, .member-detail-table td { padding:12px; border-bottom:1px solid var(--uno-line); text-align:left; vertical-align:top; }
  .member-detail-table th { color:var(--uno-muted); font-size:11px; letter-spacing:1.6px; text-transform:uppercase; background:#fafaf8; }
  @media (max-width: 980px) {
    .member-detail-grid { grid-template-columns:1fr; }
  }
</style>

<?php if (!$memberRow) { ?>
  <section class="uno-admin-panel">
    <p class="uno-admin-status warn">회원 정보를 찾을 수 없습니다.</p>
  </section>
<?php } else {
    $isBlocked = isset($memberRow['mb_intercept_date']) && trim((string) $memberRow['mb_intercept_date']) !== '';
    $isLeft = isset($memberRow['mb_leave_date']) && trim((string) $memberRow['mb_leave_date']) !== '';
?>
  <section class="member-detail-grid">
    <article class="member-detail-card">
      <h2>회원 정보</h2>
      <div class="member-detail-row"><span>아이디</span><strong><?php echo uno_renewal_admin_escape($memberRow['mb_id']); ?></strong></div>
      <div class="member-detail-row"><span>이름 / 닉네임</span><strong><?php echo uno_renewal_admin_escape(($memberRow['mb_name'] ?? '-') . ' / ' . ($memberRow['mb_nick'] ?? '-')); ?></strong></div>
      <div class="member-detail-row"><span>구분</span><strong><?php echo uno_renewal_admin_escape(uno_renewal_member_detail_level_label($memberRow['mb_level'] ?? 0)); ?> · LV <?php echo uno_renewal_admin_escape($memberRow['mb_level'] ?? '0'); ?></strong></div>
      <div class="member-detail-row"><span>이메일</span><strong><?php echo uno_renewal_admin_escape($memberRow['mb_email'] ?? '-'); ?></strong></div>
      <div class="member-detail-row"><span>휴대폰</span><strong><?php echo uno_renewal_admin_escape($memberRow['mb_hp'] ?? '-'); ?></strong></div>
      <div class="member-detail-row"><span>카카오</span><strong><?php echo uno_renewal_admin_escape($memberRow['mb_1'] ?? '-'); ?></strong></div>
      <div class="member-detail-row"><span>메일 수신</span><strong><?php echo !empty($memberRow['mb_mailling']) ? '수신' : '미수신'; ?></strong></div>
      <div class="member-detail-row"><span>상태</span><strong><?php echo $isLeft ? '탈퇴' : ($isBlocked ? '차단' : '정상'); ?></strong></div>
      <div class="member-detail-row"><span>가입일</span><strong><?php echo uno_renewal_admin_escape(uno_renewal_member_detail_date($memberRow['mb_datetime'] ?? '')); ?></strong></div>
      <div class="member-detail-row"><span>최근 로그인</span><strong><?php echo uno_renewal_admin_escape(uno_renewal_member_detail_date($memberRow['mb_today_login'] ?? '')); ?></strong></div>
      <div class="member-detail-actions">
        <a class="uno-admin-button" href="/admin/renewal/reservations.php?keyword=<?php echo rawurlencode($memberRow['mb_id']); ?>">예약 보기</a>
        <a class="uno-admin-button secondary" href="/admin/member_form.php?w=u&mb_id=<?php echo rawurlencode($memberRow['mb_id']); ?>">기존 회원 수정</a>
      </div>
    </article>

    <article class="member-detail-card">
      <h2>예약 요약</h2>
      <div class="uno-admin-grid" style="grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;">
        <div class="member-detail-card"><span>전체</span><h3><?php echo number_format($reservationSummary['total']); ?>건</h3></div>
        <div class="member-detail-card"><span>진행</span><h3><?php echo number_format($reservationSummary['active']); ?>건</h3></div>
        <div class="member-detail-card"><span>확정</span><h3><?php echo number_format($reservationSummary['confirmed']); ?>건</h3></div>
        <div class="member-detail-card"><span>취소</span><h3><?php echo number_format($reservationSummary['cancelled']); ?>건</h3></div>
      </div>
    </article>
  </section>

  <section class="uno-admin-panel" style="margin-top:16px;overflow:auto;">
    <h2 style="margin-top:0;">최근 예약</h2>
    <table class="member-detail-table">
      <thead>
        <tr>
          <th>예약</th>
          <th>상품</th>
          <th>투어일</th>
          <th>금액</th>
          <th>결제</th>
          <th>관리</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$reservationRows) { ?>
          <tr><td colspan="6">예약 내역이 없습니다.</td></tr>
        <?php } ?>
        <?php foreach ($reservationRows as $row) { ?>
          <tr>
            <td>#<?php echo uno_renewal_admin_escape($row['id']); ?><div style="color:var(--uno-muted);font-size:12px;"><?php echo uno_renewal_admin_escape(uno_renewal_member_detail_date($row['regDate'] ?? '')); ?></div></td>
            <td><strong>[<?php echo uno_renewal_admin_escape($row['ca_name'] ?? ''); ?>]</strong> <?php echo uno_renewal_admin_escape($row['wr_subject'] ?? '-'); ?></td>
            <td><?php echo uno_renewal_admin_escape(substr((string) ($row['tourDay'] ?? ''), 0, 10)); ?></td>
            <td><?php echo uno_renewal_member_detail_money($row['total_fee1'] ?? 0); ?><div style="color:var(--uno-muted);font-size:12px;">현지 <?php echo uno_renewal_admin_escape($row['total_fee2'] ?? '0'); ?></div></td>
            <td><?php echo !empty($row['card_pay']) ? '카드' : '은행'; ?></td>
            <td><a class="uno-admin-button secondary" href="/admin/renewal/reservations.php?keyword=<?php echo rawurlencode((string) $row['id']); ?>">예약 보기</a></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </section>
<?php } ?>

<?php
uno_renewal_admin_render_footer();
