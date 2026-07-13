<?php
/*
 * index.php
 * Renewal admin dashboard page for the Cafe24 PHP/Gnuboard bridge.
 * It shows a concise admin header, live legacy DB status summaries, reservation/visitor tables, and renewal module links.
 * This page keeps renewal admin tools under /admin/renewal/ so the existing legacy admin screens remain untouched.
 */

require_once __DIR__ . '/_guard.php';

uno_renewal_admin_require_access('/admin/renewal/index.php');

$adminId = isset($member['mb_id']) ? (string) $member['mb_id'] : 'admin';
$adminName = isset($member['mb_name']) && $member['mb_name'] !== '' ? (string) $member['mb_name'] : 'UNO TRAVEL';
$adminPhone = isset($member['mb_hp']) ? (string) $member['mb_hp'] : '';
$adminEmail = isset($member['mb_email']) ? (string) $member['mb_email'] : '';
$nowYmd = defined('G5_TIME_YMD') ? G5_TIME_YMD : date('Y-m-d');
$nowYmdHis = defined('G5_TIME_YMDHIS') ? G5_TIME_YMDHIS : date('Y-m-d H:i:s');

function uno_renewal_dashboard_fetch($sql)
{
    if (!function_exists('sql_fetch')) {
        return array();
    }

    $row = sql_fetch($sql);
    return is_array($row) ? $row : array();
}

function uno_renewal_dashboard_query($sql)
{
    return function_exists('sql_query') ? sql_query($sql) : false;
}

function uno_renewal_dashboard_fetch_array($result)
{
    return $result && function_exists('sql_fetch_array') ? sql_fetch_array($result) : false;
}

function uno_renewal_dashboard_number($value)
{
    return number_format((int) $value);
}

function uno_renewal_dashboard_money($value)
{
    return number_format((int) $value) . '원';
}

function uno_renewal_dashboard_status_count($where)
{
    $row = uno_renewal_dashboard_fetch("select count(id) as cnt from `tour_reg` where {$where}");
    return isset($row['cnt']) ? (int) $row['cnt'] : 0;
}

function uno_renewal_dashboard_status_total($statusWhere, $timeWhere)
{
    $row = uno_renewal_dashboard_fetch(
        "select count(*) as cnt, SUM(CAST(REPLACE(total_fee1, ',', '') AS UNSIGNED)) as total from `tour_reg` where {$statusWhere} {$timeWhere}"
    );

    return array(
        'cnt' => isset($row['cnt']) ? (int) $row['cnt'] : 0,
        'total' => isset($row['total']) ? (int) $row['total'] : 0,
    );
}

function uno_renewal_dashboard_board_count($table, $where)
{
    $row = uno_renewal_dashboard_fetch("select count(*) as cnt from `g5_write_{$table}` where {$where}");
    return isset($row['cnt']) ? (int) $row['cnt'] : 0;
}

$todayCards = array(
    array('label' => '예약 대기', 'detail' => '신청', 'count' => uno_renewal_dashboard_status_count("status = '1' and tourDay > '{$nowYmdHis}'"), 'href' => '/admin/booking.php?tourStatus=1'),
    array('label' => '예약 확인', 'detail' => '입금', 'count' => uno_renewal_dashboard_status_count("status = '11' and tourDay > '{$nowYmdHis}'"), 'href' => '/admin/booking.php?tourStatus=11'),
    array('label' => '예약 확정', 'detail' => '진행', 'count' => uno_renewal_dashboard_status_count("status = '3' and tourDay >= '{$nowYmdHis}'"), 'href' => '/admin/booking.php?tourStatus=3'),
    array('label' => '취소 요청', 'detail' => '환불', 'count' => uno_renewal_dashboard_status_count("status = '91' and tourDay >= '{$nowYmdHis}'"), 'href' => '/admin/booking.php?tourStatus=91'),
    array('label' => '예약 취소', 'detail' => '완료', 'count' => uno_renewal_dashboard_status_count("(status = '9' or status = '99') and tourDay >= '{$nowYmdHis}'"), 'href' => '/admin/booking.php?tourStatus=9'),
);

$boardCards = array(
    array('label' => '1:1문의', 'count' => uno_renewal_dashboard_board_count('cusTour', "wr_is_comment = '0' and wr_10 > 111"), 'href' => '/admin/renewal/inquiries.php?board=cusTour'),
    array('label' => '묻고답하기', 'count' => uno_renewal_dashboard_board_count('qna', "wr_is_comment = '0' and SUBSTR(wr_datetime, 1, 10) = '{$nowYmd}'"), 'href' => '/admin/board.php?bo_table=qna'),
    array('label' => '여행후기', 'count' => uno_renewal_dashboard_board_count('write', "wr_is_comment = '0' and SUBSTR(wr_datetime, 1, 10) = '{$nowYmd}'"), 'href' => '/admin/board.php?bo_table=write'),
);

$reservationRows = array();

for ($i = 0; $i < 5; $i++) {
    $date = date('Y-m-d', strtotime('-' . $i . ' day'));
    $from = strtotime($date);
    $to = $from + 86400;
    $timeWhere = "and regDate > '{$from}' and regDate < '{$to}'";

    $reservationRows[] = array(
        'label' => $date,
        'request' => uno_renewal_dashboard_status_total("status = '1'", $timeWhere),
        'confirmed' => uno_renewal_dashboard_status_total("status = '3'", $timeWhere),
        'cancelled' => uno_renewal_dashboard_status_total("(status = '91' or status = '9' or status = '99')", $timeWhere),
        'summary' => false,
    );
}

foreach (array(7, 30) as $days) {
    $from = strtotime('-' . ($days - 1) . ' day');
    $to = strtotime('tomorrow');
    $timeWhere = "and regDate >= '{$from}' and regDate < '{$to}'";

    $reservationRows[] = array(
        'label' => '최근 ' . $days . '일 합계',
        'request' => uno_renewal_dashboard_status_total("status = '1'", $timeWhere),
        'confirmed' => uno_renewal_dashboard_status_total("status = '3'", $timeWhere),
        'cancelled' => uno_renewal_dashboard_status_total("(status = '91' or status = '9' or status = '99')", $timeWhere),
        'summary' => true,
    );
}

$maxReservationCount = 1;
foreach (array_slice($reservationRows, 0, 5) as $row) {
    $maxReservationCount = max($maxReservationCount, $row['request']['cnt'], $row['confirmed']['cnt'], $row['cancelled']['cnt']);
}

$visitRows = array();
$visitResult = uno_renewal_dashboard_query("select vs_date, vs_count from `g5_visit_sum` order by vs_date desc limit 10");

while ($visitRow = uno_renewal_dashboard_fetch_array($visitResult)) {
    $visitRows[] = array(
        'date' => isset($visitRow['vs_date']) ? (string) $visitRow['vs_date'] : '',
        'count' => isset($visitRow['vs_count']) ? (int) $visitRow['vs_count'] : 0,
    );
}

if (!$visitRows) {
    for ($i = 0; $i < 10; $i++) {
        $visitRows[] = array('date' => date('Y-m-d', strtotime('-' . $i . ' day')), 'count' => 0);
    }
}

$maxVisitCount = 1;
$visitTotal = 0;
foreach ($visitRows as $row) {
    $maxVisitCount = max($maxVisitCount, $row['count']);
    $visitTotal += $row['count'];
}

$topMenus = array(
    array('label' => '예약관리', 'href' => '/admin/renewal/reservations.php'),
    array('label' => '회원관리', 'href' => '/admin/renewal/members.php'),
    array('label' => '상품관리', 'href' => '/admin/renewal/products.php'),
    array('label' => '투어 설정', 'href' => '/admin/tourClose.php'),
    array('label' => '사이트 설정', 'href' => '/admin/productDispIndex.php'),
    array('label' => '접속 통계', 'href' => '/admin/renewal/visits.php'),
);

$moduleSections = array(
    array('id' => 'reservation', 'title' => '예약관리', 'items' => array(
        array('label' => '예약 목록', 'href' => '/admin/renewal/reservations.php'),
        array('label' => '세미패키지 예약관리', 'href' => '/admin/renewal/reservations.php?type=semi'),
        array('label' => '데일리투어 예약관리', 'href' => '/admin/renewal/reservations.php?type=daily'),
        array('label' => '예약 취소 / 환불 요청', 'href' => '/admin/renewal/reservations.php?status=91'),
        array('label' => '관리자 1:1 문의', 'href' => '/admin/renewal/inquiries.php?board=cusTour'),
        array('label' => 'KSNET 카드 결제 내역', 'href' => '/admin/renewal/payments.php'),
    )),
    array('id' => 'members', 'title' => '회원관리', 'items' => array(
        array('label' => '회원 목록', 'href' => '/admin/renewal/members.php'),
        array('label' => '회원 검색', 'href' => '/admin/renewal/members.php'),
        array('label' => '가이드 회원', 'href' => '/admin/renewal/members.php?level=4'),
        array('label' => 'B2B 회원', 'href' => '/admin/renewal/members.php?level=5'),
        array('label' => 'B2B 매출 집계', 'href' => '/admin/b2b_account.php'),
    )),
    array('id' => 'products', 'title' => '상품관리', 'items' => array(
        array('label' => '상품 운영', 'href' => '/admin/renewal/products.php'),
        array('label' => '상품 네비게이션', 'href' => '/admin/renewal/product-navigation.php'),
        array('label' => '상품 ID 매핑', 'href' => '/admin/renewal/product-mapping.php'),
        array('label' => '상품 상세 FAQ', 'href' => '/admin/renewal/faqs.php'),
        array('label' => '기존 상품 추가', 'href' => '/admin/write.php?bo_table=product'),
    )),
    array('id' => 'tour', 'title' => '투어 설정', 'items' => array(
        array('label' => '예약 캘린더', 'href' => '/admin/regist_calendar.php'),
        array('label' => '데일리투어 마감 / 인원', 'href' => '/admin/tourClose.php'),
        array('label' => '요금 옵션', 'href' => '/admin/tourFee.php'),
        array('label' => '가이드 배정', 'href' => '/admin/include_files/assignGuide.php'),
        array('label' => '환율 / 티켓', 'href' => '/admin/tourExchange.php'),
    )),
    array('id' => 'site', 'title' => '사이트 설정', 'items' => array(
        array('label' => '메인 / 추천 상품', 'href' => '/admin/productDispIndex.php'),
        array('label' => '약관 / 개인정보', 'href' => '/admin/Config.php?gubun=privacy'),
        array('label' => '취소 및 환불규정', 'href' => '/admin/Config.php?gubun=cancel'),
        array('label' => 'Footer / 연락처', 'href' => '/admin/Config.php?gubun=foot'),
        array('label' => '팝업 관리', 'href' => '/admin/newwinlist.php'),
    )),
    array('id' => 'system', 'title' => '기타', 'items' => array(
        array('label' => '방문자 현황', 'href' => '/admin/renewal/visits.php'),
        array('label' => '접속자 목록', 'href' => '/admin/renewal/visits.php?view=list'),
        array('label' => '일별 접속 통계', 'href' => '/admin/renewal/visits.php?view=date'),
        array('label' => '주간 접속 통계', 'href' => '/admin/renewal/visits.php?view=week'),
        array('label' => '월간 접속 통계', 'href' => '/admin/renewal/visits.php?view=month'),
        array('label' => '브라우저 통계', 'href' => '/admin/renewal/visits.php?view=browser'),
        array('label' => '메일 발송 목록', 'href' => '/admin/emailHistory.php'),
        array('label' => '비즈톡 현황', 'href' => '/admin/biztalk.php'),
    )),
);

?><!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>UNO Renewal Admin</title>
  <style>
    :root {
      --ink: #111;
      --muted: #727272;
      --soft: #f5f5f3;
      --panel: #fff;
      --line: #e4e4e0;
      --green: #0f766e;
      --amber: #b7791f;
      --blue: #4267c7;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      background: var(--soft);
      color: var(--ink);
      font-family: Arial, "Noto Sans KR", sans-serif;
      letter-spacing: 0;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    .shell {
      width: min(1480px, calc(100% - 48px));
      margin: 0 auto;
      padding: 28px 0 64px;
    }

    .top {
      display: grid;
      grid-template-columns: auto 1fr auto;
      gap: 34px;
      align-items: center;
      padding-bottom: 22px;
      border-bottom: 1px solid var(--line);
    }

    .logo {
      font-size: 42px;
      font-weight: 900;
      line-height: .75;
      letter-spacing: -1px;
    }

    .logo span {
      display: block;
      margin-top: 9px;
      font-size: 12px;
      letter-spacing: 5px;
    }

    .nav {
      display: flex;
      gap: 6px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .nav a,
    .utility a {
      min-height: 38px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0 14px;
      border: 1px solid transparent;
      color: #373737;
      font-size: 14px;
      font-weight: 700;
    }

    .nav a:hover,
    .utility a:hover {
      border-color: var(--line);
      background: var(--panel);
    }

    .utility {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .hero {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 28px;
      align-items: end;
      padding: 40px 0 28px;
    }

    .eyebrow {
      margin: 0 0 10px;
      color: var(--muted);
      font-size: 12px;
      letter-spacing: 4px;
      text-transform: uppercase;
    }

    h1 {
      margin: 0;
      font-size: clamp(46px, 6vw, 88px);
      line-height: .92;
      letter-spacing: -1px;
    }

    .profile {
      min-width: 280px;
      padding: 20px;
      border: 1px solid var(--line);
      background: var(--panel);
    }

    .profile strong {
      display: block;
      margin-bottom: 8px;
      font-size: 18px;
    }

    .profile p {
      margin: 0;
      color: var(--muted);
      line-height: 1.65;
    }

    .status-grid {
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 10px;
    }

    .status-card,
    .panel,
    .module {
      border: 1px solid var(--line);
      background: var(--panel);
    }

    .status-card {
      min-height: 116px;
      padding: 18px;
    }

    .status-card .label {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      color: var(--muted);
      font-size: 13px;
    }

    .status-card strong {
      display: block;
      margin-top: 22px;
      font-size: 34px;
      line-height: 1;
    }

    .board-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 10px;
      margin-top: 10px;
    }

    .section {
      margin-top: 32px;
    }

    .section-head {
      display: flex;
      align-items: end;
      justify-content: space-between;
      gap: 24px;
      margin-bottom: 12px;
    }

    .section-head h2 {
      margin: 0;
      font-size: 24px;
      letter-spacing: 1px;
    }

    .section-head p {
      max-width: 640px;
      margin: 0;
      color: var(--muted);
      font-size: 14px;
      line-height: 1.7;
      word-break: keep-all;
    }

    .section-link {
      min-height: 40px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0 14px;
      border: 1px solid var(--ink);
      color: var(--ink);
      font-size: 13px;
      font-weight: 900;
      white-space: nowrap;
    }

    .panel {
      padding: 20px;
    }

    .data-layout {
      display: grid;
      grid-template-columns: minmax(0, .9fr) minmax(520px, 1.1fr);
      gap: 22px;
      align-items: stretch;
    }

    .bar-chart {
      min-height: 318px;
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 18px;
      align-items: end;
      padding: 18px 18px 8px;
      border: 1px solid var(--line);
      background:
        linear-gradient(to top, transparent 0, transparent 24%, #ecece8 25%, transparent 26%),
        linear-gradient(to top, transparent 0, transparent 49%, #ecece8 50%, transparent 51%),
        linear-gradient(to top, transparent 0, transparent 74%, #ecece8 75%, transparent 76%),
        #fff;
    }

    .bar-item {
      height: 280px;
      display: grid;
      align-items: end;
      justify-items: center;
      gap: 10px;
    }

    .bar-group {
      height: 238px;
      display: flex;
      align-items: end;
      justify-content: center;
      gap: 4px;
    }

    .bar {
      width: 18px;
      min-height: 2px;
      background: var(--green);
    }

    .bar.confirmed {
      background: var(--blue);
    }

    .bar.cancelled {
      background: var(--amber);
    }

    .bar-label {
      color: var(--muted);
      font-size: 12px;
    }

    .visit-chart {
      grid-template-columns: repeat(10, minmax(0, 1fr));
    }

    .visit-chart .bar {
      width: 32px;
      background: #7ca5e6;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }

    th,
    td {
      padding: 12px 10px;
      border-bottom: 1px solid var(--line);
      text-align: right;
      vertical-align: middle;
    }

    th:first-child,
    td:first-child {
      text-align: left;
    }

    th {
      color: var(--muted);
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
    }

    .summary td {
      font-weight: 800;
      background: #fafafa;
    }

    .modules {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 10px;
    }

    .module {
      padding: 18px;
    }

    .module h3 {
      margin: 0 0 16px;
      font-size: 18px;
    }

    .module a {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      padding: 10px 0;
      border-top: 1px solid #f0f0ed;
      color: #555;
      font-size: 14px;
    }

    .module a::after {
      content: "→";
      color: #aaa;
    }

    @media (max-width: 1160px) {
      .top,
      .hero,
      .data-layout,
      .modules {
        grid-template-columns: 1fr;
      }

      .status-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 760px) {
      .shell {
        width: min(100% - 28px, 1480px);
      }

      .status-grid,
      .board-grid,
      .bar-chart,
      .visit-chart {
        grid-template-columns: 1fr;
      }

      .section-head {
        display: block;
      }
    }
  </style>
</head>
<body>
  <main class="shell">
    <header class="top">
      <a class="logo" href="/admin/renewal/index.php">UNO<span>T·R·A·V·E·L</span></a>
      <nav class="nav" aria-label="Renewal admin menu">
        <?php foreach ($topMenus as $menu) { ?>
          <a href="<?php echo uno_renewal_admin_escape($menu['href']); ?>"><?php echo uno_renewal_admin_escape($menu['label']); ?></a>
        <?php } ?>
      </nav>
      <div class="utility">
        <a href="/">Front 가기</a>
        <a href="/admin/main.php">기존 관리자</a>
      </div>
    </header>

    <section class="hero">
      <div>
        <p class="eyebrow">Renewal Admin Dashboard</p>
        <h1>Dashboard</h1>
      </div>
      <aside class="profile">
        <strong><?php echo uno_renewal_admin_escape($adminName); ?></strong>
        <p>
          ID <?php echo uno_renewal_admin_escape($adminId); ?><br>
          <?php echo uno_renewal_admin_escape($adminPhone); ?><br>
          <?php echo uno_renewal_admin_escape($adminEmail); ?>
        </p>
      </aside>
    </section>

    <section class="section">
      <div class="section-head">
        <h2>오늘의 주요현황</h2>
        <p>투어일이 현재시간 이후인 예약을 기준으로 계산합니다. 문의 카운트는 기존 게시판 데이터를 사용합니다.</p>
      </div>
      <div class="status-grid">
        <?php foreach ($todayCards as $card) { ?>
          <a class="status-card" href="<?php echo uno_renewal_admin_escape($card['href']); ?>">
            <span class="label">
              <span><?php echo uno_renewal_admin_escape($card['label']); ?></span>
              <span><?php echo uno_renewal_admin_escape($card['detail']); ?></span>
            </span>
            <strong><?php echo uno_renewal_dashboard_number($card['count']); ?><small>건</small></strong>
          </a>
        <?php } ?>
      </div>
      <div class="board-grid">
        <?php foreach ($boardCards as $card) { ?>
          <a class="status-card" href="<?php echo uno_renewal_admin_escape($card['href']); ?>">
            <span class="label"><span><?php echo uno_renewal_admin_escape($card['label']); ?></span><span>board</span></span>
            <strong><?php echo uno_renewal_dashboard_number($card['count']); ?><small>건</small></strong>
          </a>
        <?php } ?>
      </div>
    </section>

    <section class="section">
      <div class="section-head">
        <h2>예약현황</h2>
        <p>신청, 확정, 환불/취소 흐름을 최근 5일과 합계 기준으로 확인합니다.</p>
      </div>
      <div class="panel data-layout">
        <div class="bar-chart" aria-label="최근 예약현황 그래프">
          <?php foreach (array_slice($reservationRows, 0, 5) as $row) { ?>
            <div class="bar-item">
              <div class="bar-group">
                <span class="bar" style="height: <?php echo max(2, round($row['request']['cnt'] / $maxReservationCount * 230)); ?>px"></span>
                <span class="bar confirmed" style="height: <?php echo max(2, round($row['confirmed']['cnt'] / $maxReservationCount * 230)); ?>px"></span>
                <span class="bar cancelled" style="height: <?php echo max(2, round($row['cancelled']['cnt'] / $maxReservationCount * 230)); ?>px"></span>
              </div>
              <span class="bar-label"><?php echo uno_renewal_admin_escape(substr($row['label'], 5)); ?></span>
            </div>
          <?php } ?>
        </div>

        <table>
          <thead>
            <tr>
              <th>날짜</th>
              <th>예약신청</th>
              <th>예약확정</th>
              <th>환불/취소</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($reservationRows as $row) { ?>
              <tr class="<?php echo $row['summary'] ? 'summary' : ''; ?>">
                <td><?php echo uno_renewal_admin_escape($row['label']); ?></td>
                <td><?php echo uno_renewal_dashboard_money($row['request']['total']); ?><br><?php echo uno_renewal_dashboard_number($row['request']['cnt']); ?>(건)</td>
                <td><?php echo uno_renewal_dashboard_money($row['confirmed']['total']); ?><br><?php echo uno_renewal_dashboard_number($row['confirmed']['cnt']); ?>(건)</td>
                <td><?php echo uno_renewal_dashboard_money($row['cancelled']['total']); ?><br><?php echo uno_renewal_dashboard_number($row['cancelled']['cnt']); ?>(건)</td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="section">
      <div class="section-head">
        <h2>방문자 현황</h2>
        <p>Google Analytics 또는 외부 접속로그 분석 시스템과 데이터가 다를 수 있습니다.</p>
        <a class="section-link" href="/admin/renewal/visits.php">방문자 현황 보기</a>
      </div>
      <div class="panel data-layout">
        <div class="bar-chart visit-chart" aria-label="최근 방문자 그래프">
          <?php foreach ($visitRows as $row) { ?>
            <div class="bar-item">
              <div class="bar-group">
                <span class="bar" style="height: <?php echo max(2, round($row['count'] / $maxVisitCount * 230)); ?>px"></span>
              </div>
              <span class="bar-label"><?php echo uno_renewal_admin_escape(substr($row['date'], 5)); ?></span>
            </div>
          <?php } ?>
        </div>

        <table>
          <thead>
            <tr>
              <th>일시</th>
              <th>전체방문자</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($visitRows as $row) { ?>
              <tr>
                <td><?php echo uno_renewal_admin_escape($row['date']); ?></td>
                <td><?php echo uno_renewal_dashboard_number($row['count']); ?></td>
              </tr>
            <?php } ?>
            <tr class="summary">
              <td>합계</td>
              <td><?php echo uno_renewal_dashboard_number($visitTotal); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="section">
      <div class="section-head">
        <h2>리뉴얼 관리 메뉴</h2>
        <p>기존 관리자 기능은 유지하고, 리뉴얼 프런트에 필요한 관리 기능만 이 영역에서 순차적으로 연결합니다.</p>
      </div>
      <div class="modules">
        <?php foreach ($moduleSections as $section) { ?>
          <article class="module" id="<?php echo uno_renewal_admin_escape($section['id']); ?>">
            <h3><?php echo uno_renewal_admin_escape($section['title']); ?></h3>
            <?php foreach ($section['items'] as $item) { ?>
              <a href="<?php echo uno_renewal_admin_escape($item['href']); ?>"><?php echo uno_renewal_admin_escape($item['label']); ?></a>
            <?php } ?>
          </article>
        <?php } ?>
      </div>
    </section>
  </main>
</body>
</html>
