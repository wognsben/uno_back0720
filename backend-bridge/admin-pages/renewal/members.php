<?php
/*
 * members.php
 * Renewal admin member list.
 * It provides a readable member index while keeping edits inside the legacy Gnuboard member form.
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

uno_renewal_admin_require_access('/admin/renewal/members.php');

function uno_renewal_member_escape_sql($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string($value);
    }

    if (function_exists('sql_real_escape_string')) {
        return sql_real_escape_string($value);
    }

    return addslashes($value);
}

function uno_renewal_member_table()
{
    global $g5;
    return isset($g5['member_table']) && $g5['member_table'] !== '' ? $g5['member_table'] : 'g5_member';
}

function uno_renewal_member_level_label($level)
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

function uno_renewal_member_date_label($value)
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

function uno_renewal_member_count($memberTable, $where)
{
    $row = sql_fetch("select count(*) as cnt from {$memberTable} m where {$where}");
    return isset($row['cnt']) ? (int) $row['cnt'] : 0;
}

function uno_renewal_member_table_exists($tableName)
{
    $safeTable = uno_renewal_member_escape_sql($tableName);
    $row = sql_fetch("show tables like '{$safeTable}'");
    return is_array($row) && count($row) > 0;
}

$keyword = isset($_GET['keyword']) ? trim((string) $_GET['keyword']) : '';
$level = isset($_GET['level']) ? trim((string) $_GET['level']) : '';
$status = isset($_GET['status']) ? trim((string) $_GET['status']) : '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 30;
$offset = ($page - 1) * $perPage;
$memberTable = uno_renewal_member_table();
$b2bAccountYear = date('Y');
$safeB2bAccountYear = uno_renewal_member_escape_sql($b2bAccountYear);
$hasB2bFeeTable = uno_renewal_member_table_exists('tour_fee_b2b');

$where = array('1=1');
if ($keyword !== '') {
    $safeKeyword = uno_renewal_member_escape_sql($keyword);
    $where[] = "(m.mb_id like '%{$safeKeyword}%' or m.mb_name like '%{$safeKeyword}%' or m.mb_nick like '%{$safeKeyword}%' or m.mb_email like '%{$safeKeyword}%' or m.mb_hp like '%{$safeKeyword}%' or m.mb_1 like '%{$safeKeyword}%')";
}
if ($level !== '' && preg_match('/^\d+$/', $level)) {
    $safeLevel = (int) $level;
    if ($safeLevel === 4) {
        $where[] = 'm.mb_level = 4';
    } elseif ($safeLevel === 5) {
        $where[] = 'm.mb_level = 5';
    } elseif ($safeLevel === 10) {
        $where[] = '(m.mb_level = 3 or m.mb_level >= 9)';
    } else {
        $where[] = 'm.mb_level = 2';
    }
}
if ($status === 'blocked') {
    $where[] = "m.mb_intercept_date <> ''";
} elseif ($status === 'left') {
    $where[] = "m.mb_leave_date <> ''";
} elseif ($status === 'active') {
    $where[] = "(m.mb_intercept_date = '' or m.mb_intercept_date is null) and (m.mb_leave_date = '' or m.mb_leave_date is null)";
}

$whereSql = implode(' and ', $where);
$totalRow = sql_fetch("select count(*) as cnt from {$memberTable} m where {$whereSql}");
$totalCount = isset($totalRow['cnt']) ? (int) $totalRow['cnt'] : 0;
$totalPages = max(1, (int) ceil($totalCount / $perPage));
$b2bMemberSelectSql = $hasB2bFeeTable
    ? ", (select coalesce(sum(f.total_sum), 0)
               from tour_reg r
               join tour_fee_b2b f on f.r_id = r.id
              where r.mb_id = m.mb_id
                and r.isB2B = 'Y'
                and r.status = '3'
                and r.tourDay like '{$safeB2bAccountYear}-%'
                and length(r.b2b_invoice) > 2
                and r.b2b_end < 1) as b2b_pending_sum,
            (select coalesce(sum(f.total_sum), 0)
               from tour_reg r
               join tour_fee_b2b f on f.r_id = r.id
              where r.mb_id = m.mb_id
                and r.isB2B = 'Y'
                and r.status = '3'
                and r.tourDay like '{$safeB2bAccountYear}-%'
                and length(r.b2b_invoice) > 2
                and r.b2b_end > 1) as b2b_done_sum"
    : ", 0 as b2b_pending_sum, 0 as b2b_done_sum";
$result = sql_query(
    "select m.mb_no, m.mb_id, m.mb_name, m.mb_nick, m.mb_email, m.mb_hp, m.mb_1, m.mb_level,
            m.mb_datetime, m.mb_today_login, m.mb_intercept_date, m.mb_leave_date, m.mb_mailling,
            (select count(*)
               from tour_reg r
              where r.mb_id = m.mb_id
                and r.status not in ('cart', 'booking')) as reservation_count,
            (select count(*)
               from tour_reg r
              where r.mb_id = m.mb_id
                and r.status in ('1', '2', '11', '3', '91')) as active_reservation_count,
            (select max(r.tourDay)
               from tour_reg r
              where r.mb_id = m.mb_id
                and r.status not in ('cart', 'booking')) as latest_tour_day
            {$b2bMemberSelectSql}
       from {$memberTable} m
      where {$whereSql}
      order by m.mb_datetime desc, m.mb_id asc
      limit {$offset}, {$perPage}"
);

$members = array();
while ($row = sql_fetch_array($result)) {
    $members[] = $row;
}

$queryBase = http_build_query(array_filter(array(
    'keyword' => $keyword,
    'level' => $level,
    'status' => $status,
), function ($value) {
    return $value !== '';
}));
$pageHref = function ($targetPage) use ($queryBase) {
    return '/admin/renewal/members.php?' . ($queryBase !== '' ? $queryBase . '&' : '') . 'page=' . (int) $targetPage;
};

$summaryCards = array(
    array('label' => '전체 회원', 'count' => uno_renewal_member_count($memberTable, '1=1'), 'href' => '/admin/renewal/members.php'),
    array('label' => '일반', 'count' => uno_renewal_member_count($memberTable, 'm.mb_level = 2'), 'href' => '/admin/renewal/members.php?level=3'),
    array('label' => '가이드', 'count' => uno_renewal_member_count($memberTable, 'm.mb_level = 4'), 'href' => '/admin/renewal/members.php?level=4'),
    array('label' => 'B2B', 'count' => uno_renewal_member_count($memberTable, 'm.mb_level = 5'), 'href' => '/admin/renewal/members.php?level=5'),
    array('label' => '관리자', 'count' => uno_renewal_member_count($memberTable, '(m.mb_level = 3 or m.mb_level >= 9)'), 'href' => '/admin/renewal/members.php?level=10'),
    array('label' => '차단', 'count' => uno_renewal_member_count($memberTable, "m.mb_intercept_date <> ''"), 'href' => '/admin/renewal/members.php?status=blocked'),
    array('label' => '탈퇴', 'count' => uno_renewal_member_count($memberTable, "m.mb_leave_date <> ''"), 'href' => '/admin/renewal/members.php?status=left'),
);

$b2bAccountRow = $hasB2bFeeTable
    ? sql_fetch(
        "select coalesce(sum(case when length(r.b2b_invoice) > 2 and r.b2b_end < 1 then f.total_sum else 0 end), 0) as pending_sum,
                coalesce(sum(case when length(r.b2b_invoice) > 2 and r.b2b_end > 1 then f.total_sum else 0 end), 0) as done_sum
           from tour_reg r
           join tour_fee_b2b f on f.r_id = r.id
          where r.isB2B = 'Y'
            and r.status = '3'
            and r.tourDay like '{$safeB2bAccountYear}-%'"
    )
    : array('pending_sum' => 0, 'done_sum' => 0);
$b2bPendingSum = isset($b2bAccountRow['pending_sum']) ? (int) $b2bAccountRow['pending_sum'] : 0;
$b2bDoneSum = isset($b2bAccountRow['done_sum']) ? (int) $b2bAccountRow['done_sum'] : 0;

$socialRows = array();
if (function_exists('sql_query')) {
    $socialResult = sql_query("select provider, count(*) as cnt from g5_member_social_profiles group by provider order by provider asc");
    while ($socialRow = sql_fetch_array($socialResult)) {
        $socialRows[] = $socialRow;
    }
}

uno_renewal_admin_render_head('UNO Renewal Members');
uno_renewal_admin_render_header();
uno_renewal_admin_render_pagehead(
    'UNO Travel Renewal Admin',
    'Member<br>List',
    '기존 회원 데이터를 리뉴얼 관리자 톤으로 확인합니다. 회원 수정, 권한 변경, 차단 같은 실제 변경은 기존 회원 수정 화면으로 연결합니다.',
    array(
        array('label' => '전체 회원', 'href' => '/admin/renewal/members.php', 'secondary' => true),
        array('label' => 'B2B 매출 집계', 'href' => '/admin/b2b_account.php?selYear=' . rawurlencode($b2bAccountYear), 'secondary' => true),
        array('label' => '스팸 문의 관리', 'href' => '/admin/renewal/spam-moderation.php', 'secondary' => true),
    )
);
?>

  <style>
    .member-filter { display: grid; grid-template-columns: minmax(220px, 1fr) 160px 160px auto; gap: 10px; align-items: end; margin-bottom: 16px; }
    .member-filter label { display: grid; gap: 6px; color: var(--uno-muted); font-size: 11px; font-weight: 900; letter-spacing: 1.8px; text-transform: uppercase; }
    .member-filter input, .member-filter select { min-height: 42px; border: 1px solid var(--uno-line); background: #fff; padding: 0 12px; color: var(--uno-ink); }
    .member-summary { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
    .member-chip { display: inline-flex; align-items: center; min-height: 32px; border: 1px solid var(--uno-line); background: #fff; padding: 0 10px; font-size: 12px; font-weight: 900; }
    .member-card-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 8px; margin-bottom: 16px; }
    .member-b2b-strip { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin-bottom: 16px; }
    .member-card { border: 1px solid var(--uno-line); background: #fff; padding: 12px; }
    .member-card span { display: block; color: var(--uno-muted); font-size: 10px; font-weight: 900; letter-spacing: 1.6px; text-transform: uppercase; }
    .member-card strong { display: block; margin-top: 5px; font-size: 22px; }
    .member-table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid var(--uno-line); }
    .member-table th, .member-table td { border-bottom: 1px solid var(--uno-line); padding: 12px; text-align: left; vertical-align: top; }
    .member-table th { color: var(--uno-muted); font-size: 11px; letter-spacing: 1.7px; text-transform: uppercase; background: #fafaf8; }
    .member-title { font-weight: 900; }
    .member-sub { margin-top: 4px; color: var(--uno-muted); font-size: 12px; line-height: 1.45; }
    .member-status { display: inline-flex; min-height: 28px; align-items: center; border: 1px solid rgba(15,118,110,.24); background: rgba(15,118,110,.06); color: var(--uno-good); padding: 0 8px; font-size: 12px; font-weight: 900; }
    .member-status.warn { border-color: rgba(159,41,41,.24); background: rgba(159,41,41,.07); color: var(--uno-danger); }
    .member-status.muted { border-color: rgba(111,116,123,.24); background: rgba(111,116,123,.07); color: var(--uno-muted); }
    .member-action-stack { display: flex; flex-wrap: wrap; gap: 6px; }
    .member-b2b-amount { margin-top: 6px; font-weight: 900; }
    .member-pager { display: flex; justify-content: center; gap: 8px; margin-top: 16px; }
    @media (max-width: 860px) {
      .member-filter, .member-card-grid, .member-b2b-strip { grid-template-columns: 1fr; }
      .member-table { display: block; overflow-x: auto; }
    }
  </style>

  <section class="uno-admin-panel">
    <form class="member-filter" method="get" action="/admin/renewal/members.php">
      <label>검색
        <input type="search" name="keyword" value="<?php echo uno_renewal_admin_escape($keyword); ?>" placeholder="ID, 이름, 이메일, 전화번호">
      </label>
      <label>회원 구분
        <select name="level">
          <option value="">전체</option>
          <option value="3"<?php echo $level === '3' ? ' selected' : ''; ?>>일반</option>
          <option value="4"<?php echo $level === '4' ? ' selected' : ''; ?>>가이드</option>
          <option value="5"<?php echo $level === '5' ? ' selected' : ''; ?>>B2B</option>
          <option value="10"<?php echo $level === '10' ? ' selected' : ''; ?>>관리자</option>
        </select>
      </label>
      <label>상태
        <select name="status">
          <option value="">전체</option>
          <option value="active"<?php echo $status === 'active' ? ' selected' : ''; ?>>정상</option>
          <option value="blocked"<?php echo $status === 'blocked' ? ' selected' : ''; ?>>차단</option>
          <option value="left"<?php echo $status === 'left' ? ' selected' : ''; ?>>탈퇴</option>
        </select>
      </label>
      <button class="uno-admin-button" type="submit">검색</button>
    </form>

    <div class="member-card-grid">
      <?php foreach ($summaryCards as $card) { ?>
        <a class="member-card" href="<?php echo uno_renewal_admin_escape($card['href']); ?>">
          <span><?php echo uno_renewal_admin_escape($card['label']); ?></span>
          <strong><?php echo number_format((int) $card['count']); ?></strong>
        </a>
      <?php } ?>
    </div>

    <div class="member-b2b-strip">
      <a class="member-card" href="/admin/b2b_account.php?selYear=<?php echo rawurlencode($b2bAccountYear); ?>">
        <span>B2B 청구 대기 · <?php echo uno_renewal_admin_escape($b2bAccountYear); ?></span>
        <strong><?php echo number_format($b2bPendingSum); ?>원</strong>
      </a>
      <a class="member-card" href="/admin/b2b_account.php?selYear=<?php echo rawurlencode($b2bAccountYear); ?>">
        <span>B2B 정산 완료 · <?php echo uno_renewal_admin_escape($b2bAccountYear); ?></span>
        <strong><?php echo number_format($b2bDoneSum); ?>원</strong>
      </a>
    </div>
    <?php if (!$hasB2bFeeTable) { ?>
      <p class="member-sub">현재 DB에 <code>tour_fee_b2b</code> 테이블이 없어 B2B 매출 금액은 0원으로 표시합니다. 회원 목록과 B2B 회원 필터는 정상 동작합니다.</p>
    <?php } ?>

    <div class="member-summary">
      <span class="member-chip">총 <?php echo number_format($totalCount); ?>명</span>
      <span class="member-chip"><?php echo number_format($page); ?> / <?php echo number_format($totalPages); ?> 페이지</span>
      <?php foreach ($socialRows as $socialRow) { ?>
        <span class="member-chip"><?php echo uno_renewal_admin_escape($socialRow['provider']); ?> <?php echo number_format((int) $socialRow['cnt']); ?>명</span>
      <?php } ?>
    </div>

    <table class="member-table">
      <thead>
        <tr>
          <th>회원</th>
          <th>연락처</th>
          <th>구분</th>
          <th>예약 내역</th>
          <th>가입 / 로그인</th>
          <th>상태</th>
          <th>관리</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($members) === 0) { ?>
          <tr><td colspan="7">조건에 맞는 회원이 없습니다.</td></tr>
        <?php } ?>
        <?php foreach ($members as $row) {
            $memberId = isset($row['mb_id']) ? (string) $row['mb_id'] : '';
            $isBlocked = isset($row['mb_intercept_date']) && trim((string) $row['mb_intercept_date']) !== '';
            $isLeft = isset($row['mb_leave_date']) && trim((string) $row['mb_leave_date']) !== '';
            $reservationCount = isset($row['reservation_count']) ? (int) $row['reservation_count'] : 0;
            $activeReservationCount = isset($row['active_reservation_count']) ? (int) $row['active_reservation_count'] : 0;
            $b2bPendingMemberSum = isset($row['b2b_pending_sum']) ? (int) $row['b2b_pending_sum'] : 0;
            $b2bDoneMemberSum = isset($row['b2b_done_sum']) ? (int) $row['b2b_done_sum'] : 0;
            $isB2bMember = isset($row['mb_level']) && (int) $row['mb_level'] === 5;
        ?>
          <tr>
            <td>
              <div class="member-title"><?php echo uno_renewal_admin_escape($memberId); ?></div>
              <div class="member-sub"><?php echo uno_renewal_admin_escape(isset($row['mb_name']) && $row['mb_name'] !== '' ? $row['mb_name'] : '-'); ?> / <?php echo uno_renewal_admin_escape(isset($row['mb_nick']) ? $row['mb_nick'] : ''); ?></div>
            </td>
            <td>
              <div><?php echo uno_renewal_admin_escape(isset($row['mb_email']) ? $row['mb_email'] : '-'); ?></div>
              <div class="member-sub"><?php echo uno_renewal_admin_escape(isset($row['mb_hp']) ? $row['mb_hp'] : '-'); ?></div>
              <div class="member-sub">카카오 <?php echo uno_renewal_admin_escape(isset($row['mb_1']) && $row['mb_1'] !== '' ? $row['mb_1'] : '-'); ?></div>
            </td>
            <td><?php echo uno_renewal_admin_escape(uno_renewal_member_level_label(isset($row['mb_level']) ? $row['mb_level'] : 0)); ?><div class="member-sub">LV <?php echo uno_renewal_admin_escape(isset($row['mb_level']) ? $row['mb_level'] : '0'); ?></div></td>
            <td>
              <div>전체 <?php echo number_format($reservationCount); ?>건</div>
              <div class="member-sub">진행 <?php echo number_format($activeReservationCount); ?>건</div>
              <div class="member-sub">최근 투어 <?php echo uno_renewal_admin_escape(uno_renewal_member_date_label(isset($row['latest_tour_day']) ? $row['latest_tour_day'] : '')); ?></div>
              <?php if ($isB2bMember || $b2bPendingMemberSum > 0 || $b2bDoneMemberSum > 0) { ?>
                <div class="member-b2b-amount">B2B 청구 <?php echo number_format($b2bPendingMemberSum); ?>원</div>
                <div class="member-sub">정산 완료 <?php echo number_format($b2bDoneMemberSum); ?>원</div>
              <?php } ?>
            </td>
            <td>
              <div><?php echo uno_renewal_admin_escape(uno_renewal_member_date_label(isset($row['mb_datetime']) ? $row['mb_datetime'] : '')); ?></div>
              <div class="member-sub">최근 <?php echo uno_renewal_admin_escape(uno_renewal_member_date_label(isset($row['mb_today_login']) ? $row['mb_today_login'] : '')); ?></div>
              <div class="member-sub">메일 <?php echo !empty($row['mb_mailling']) ? '수신' : '미수신'; ?></div>
            </td>
            <td>
              <?php if ($isLeft) { ?>
                <span class="member-status muted">탈퇴</span>
                <div class="member-sub"><?php echo uno_renewal_admin_escape(uno_renewal_member_date_label($row['mb_leave_date'])); ?></div>
              <?php } elseif ($isBlocked) { ?>
                <span class="member-status warn">차단</span>
                <div class="member-sub"><?php echo uno_renewal_admin_escape(uno_renewal_member_date_label($row['mb_intercept_date'])); ?></div>
              <?php } else { ?>
                <span class="member-status">정상</span>
              <?php } ?>
            </td>
            <td>
              <div class="member-action-stack">
                <a class="uno-admin-button secondary" href="/admin/renewal/member-detail.php?mb_id=<?php echo rawurlencode($memberId); ?>">상세</a>
                <a class="uno-admin-button secondary" href="/admin/renewal/reservations.php?keyword=<?php echo rawurlencode($memberId); ?>">예약</a>
                <?php if ($isB2bMember || $b2bPendingMemberSum > 0 || $b2bDoneMemberSum > 0) { ?>
                  <a class="uno-admin-button secondary" href="/admin/b2b_account.php?selYear=<?php echo rawurlencode($b2bAccountYear); ?>">B2B 매출</a>
                <?php } ?>
                <a class="uno-admin-button secondary" href="/admin/member_form.php?w=u&mb_id=<?php echo rawurlencode($memberId); ?>">기존 수정</a>
              </div>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>

    <div class="member-pager">
      <?php if ($page > 1) { ?><a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape($pageHref($page - 1)); ?>">이전</a><?php } ?>
      <?php if ($page < $totalPages) { ?><a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape($pageHref($page + 1)); ?>">다음</a><?php } ?>
    </div>
  </section>

<?php
uno_renewal_admin_render_footer();
