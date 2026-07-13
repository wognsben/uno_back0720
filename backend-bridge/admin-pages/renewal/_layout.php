<?php
/*
 * _layout.php
 * Shared renewal admin layout helper for Cafe24/Gnuboard admin pages.
 * It renders the consistent UNO admin header, menu groups, utility links, and base visual system.
 * Use this for renewal pages only; it does not replace the legacy /admin header or public frontend layout.
 */

if (!function_exists('uno_renewal_admin_escape')) {
    require_once __DIR__ . '/_guard.php';
}

function uno_renewal_admin_menu_groups()
{
    return array(
        array(
            'label' => '예약관리',
            'href' => '/admin/renewal/reservations.php',
            'items' => array(
                array('label' => '예약 목록', 'href' => '/admin/renewal/reservations.php'),
                array('label' => '세미패키지 예약', 'href' => '/admin/booking.php?sca=세미패키지'),
                array('label' => '데일리투어 예약', 'href' => '/admin/booking.php?sca=데이투어'),
                array('label' => '예약 캘린더', 'href' => '/admin/regist_calendar.php'),
                array('label' => '관리자 1:1 문의', 'href' => '/admin/renewal/inquiries.php?board=cusTour'),
                array('label' => 'KSNET 결제 내역', 'href' => '/admin/renewal/payments.php'),
            ),
        ),
        array(
            'label' => '회원관리',
            'href' => '/admin/renewal/members.php',
            'items' => array(
                array('label' => '회원 목록', 'href' => '/admin/renewal/members.php'),
                array('label' => '공개 묻고답하기', 'href' => '/admin/renewal/community-qna.php'),
                array('label' => '여행후기', 'href' => '/admin/renewal/community-reviews.php'),
                array('label' => '스팸 문의 관리', 'href' => '/admin/renewal/spam-moderation.php'),
                array('label' => '가이드', 'href' => '/admin/renewal/members.php?level=4'),
                array('label' => 'B2B 회원', 'href' => '/admin/renewal/members.php?level=5'),
                array('label' => 'B2B 매출 집계', 'href' => '/admin/b2b_account.php'),
            ),
        ),
        array(
            'label' => '상품관리',
            'href' => '/admin/renewal/products.php',
            'items' => array(
                array('label' => '상품 운영', 'href' => '/admin/renewal/products.php'),
                array('label' => '상품 네비게이션', 'href' => '/admin/renewal/product-navigation.php'),
                array('label' => '상품 ID 매핑', 'href' => '/admin/renewal/product-mapping.php'),
                array('label' => '상품 상세 FAQ', 'href' => '/admin/renewal/faqs.php'),
                array('label' => '기존 상품 추가', 'href' => '/admin/write.php?bo_table=product'),
            ),
        ),
        array(
            'label' => '투어 설정',
            'href' => '/admin/tourClose.php',
            'items' => array(
                array('label' => '데일리투어 마감/인원', 'href' => '/admin/tourClose.php'),
                array('label' => '요금 옵션', 'href' => '/admin/tourFee.php'),
                array('label' => '가이드 배정', 'href' => '/admin/include_files/assignGuide.php'),
                array('label' => '환율/티켓', 'href' => '/admin/tourExchange.php'),
            ),
        ),
        array(
            'label' => '사이트 설정',
            'href' => '/admin/productDispIndex.php',
            'items' => array(
                array('label' => '메인/추천 상품', 'href' => '/admin/productDispIndex.php'),
                array('label' => '약관/개인정보', 'href' => '/admin/Config.php?gubun=privacy'),
                array('label' => '취소 및 환불규정', 'href' => '/admin/Config.php?gubun=cancel'),
                array('label' => 'Footer/연락처', 'href' => '/admin/Config.php?gubun=foot'),
                array('label' => '팝업 관리', 'href' => '/admin/newwinlist.php'),
            ),
        ),
        array(
            'label' => '기타',
            'href' => '/admin/renewal/visits.php',
            'items' => array(
                array('label' => '방문자 현황', 'href' => '/admin/renewal/visits.php'),
                array('label' => '접속자 목록', 'href' => '/admin/renewal/visits.php?view=list'),
                array('label' => '일별 접속 통계', 'href' => '/admin/renewal/visits.php?view=date'),
                array('label' => '주간 접속 통계', 'href' => '/admin/renewal/visits.php?view=week'),
                array('label' => '월간 접속 통계', 'href' => '/admin/renewal/visits.php?view=month'),
                array('label' => '브라우저 통계', 'href' => '/admin/renewal/visits.php?view=browser'),
                array('label' => '메일 발송 목록', 'href' => '/admin/emailHistory.php'),
                array('label' => '비즈톡 현황', 'href' => '/admin/biztalk.php'),
                array('label' => '리뉴얼 대시보드', 'href' => '/admin/renewal/index.php'),
            ),
        ),
    );
}

function uno_renewal_admin_render_head($title)
{
    ?><!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo uno_renewal_admin_escape($title); ?></title>
  <style>
    :root {
      --uno-ink: #111;
      --uno-muted: #6f747b;
      --uno-line: #e4e4e0;
      --uno-soft: #f5f5f3;
      --uno-panel: #fff;
      --uno-good: #0f766e;
      --uno-warn: #9a6a00;
      --uno-danger: #9f2929;
    }

    * { box-sizing: border-box; }

    body {
      margin: 0;
      background: var(--uno-soft);
      color: var(--uno-ink);
      font-family: Arial, "Noto Sans KR", sans-serif;
      letter-spacing: 0;
    }

    a { color: inherit; text-decoration: none; }
    button, input, select, textarea { font: inherit; }

    .uno-admin-shell {
      width: min(1480px, calc(100% - 48px));
      margin: 0 auto;
      padding: 24px 0 72px;
    }

    .uno-admin-header {
      display: grid;
      grid-template-columns: 180px minmax(0, 1fr) auto;
      gap: 24px;
      align-items: center;
      padding-bottom: 18px;
      border-bottom: 1px solid var(--uno-line);
    }

    .uno-admin-logo {
      display: inline-grid;
      gap: 5px;
      width: max-content;
      font-size: 36px;
      font-weight: 900;
      line-height: .78;
    }

    .uno-admin-logo span {
      font-size: 11px;
      letter-spacing: 4px;
    }

    .uno-admin-nav {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 6px;
    }

    .uno-admin-menu {
      position: relative;
    }

    .uno-admin-menu > a,
    .uno-admin-utility a,
    .uno-admin-button {
      min-height: 40px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0 14px;
      border: 1px solid transparent;
      background: transparent;
      color: #313131;
      font-weight: 800;
      font-size: 14px;
      cursor: pointer;
    }

    .uno-admin-menu:hover > a,
    .uno-admin-utility a:hover,
    .uno-admin-button:hover {
      border-color: var(--uno-line);
      background: var(--uno-panel);
    }

    .uno-admin-submenu {
      position: absolute;
      z-index: 20;
      top: calc(100% + 8px);
      left: 50%;
      width: 220px;
      transform: translateX(-50%);
      display: none;
      padding: 8px;
      border: 1px solid var(--uno-line);
      background: var(--uno-panel);
      box-shadow: 0 18px 36px rgba(0, 0, 0, .08);
    }

    .uno-admin-menu:hover .uno-admin-submenu,
    .uno-admin-menu:focus-within .uno-admin-submenu {
      display: grid;
    }

    .uno-admin-submenu a {
      padding: 10px;
      color: var(--uno-muted);
      font-size: 13px;
      line-height: 1.35;
    }

    .uno-admin-submenu a:hover {
      color: var(--uno-ink);
      background: #f7f7f4;
    }

    .uno-admin-utility {
      display: flex;
      gap: 8px;
      align-items: center;
      justify-content: flex-end;
      white-space: nowrap;
    }

    .uno-admin-pagehead {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 24px;
      align-items: end;
      padding: 38px 0 22px;
    }

    .uno-admin-eyebrow {
      margin: 0 0 10px;
      color: var(--uno-muted);
      font-size: 12px;
      letter-spacing: 4px;
      text-transform: uppercase;
    }

    .uno-admin-title {
      margin: 0;
      font-size: clamp(42px, 5.8vw, 82px);
      line-height: .92;
      letter-spacing: -1px;
    }

    .uno-admin-copy {
      max-width: 780px;
      margin: 16px 0 0;
      color: var(--uno-muted);
      line-height: 1.75;
      word-break: keep-all;
    }

    .uno-admin-actions {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-end;
      gap: 8px;
    }

    .uno-admin-button {
      border-color: var(--uno-ink);
      background: var(--uno-ink);
      color: #fff;
    }

    .uno-admin-button.secondary {
      border-color: var(--uno-line);
      background: var(--uno-panel);
      color: var(--uno-ink);
    }

    .uno-admin-panel,
    .uno-admin-card {
      border: 1px solid var(--uno-line);
      background: var(--uno-panel);
    }

    .uno-admin-panel {
      padding: 22px;
    }

    .uno-admin-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 12px;
    }

    .uno-admin-card {
      min-height: 178px;
      padding: 18px;
      display: grid;
      align-content: space-between;
      gap: 16px;
    }

    .uno-admin-card h2,
    .uno-admin-card h3 {
      margin: 0;
      font-size: 22px;
      line-height: 1.2;
    }

    .uno-admin-card p {
      margin: 10px 0 0;
      color: var(--uno-muted);
      line-height: 1.65;
      word-break: keep-all;
    }

    .uno-admin-status {
      min-height: 24px;
      margin: 0;
      color: var(--uno-muted);
      line-height: 1.6;
    }

    .uno-admin-status.ok { color: var(--uno-good); }
    .uno-admin-status.warn { color: var(--uno-warn); }

    .uno-admin-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 14px;
    }

    .uno-admin-chip {
      min-height: 30px;
      display: inline-flex;
      align-items: center;
      padding: 0 10px;
      border: 1px solid var(--uno-line);
      color: var(--uno-muted);
      font-size: 12px;
      font-weight: 800;
    }

    .uno-admin-chip.good {
      border-color: rgba(15, 118, 110, .28);
      color: var(--uno-good);
      background: rgba(15, 118, 110, .06);
    }

    .uno-admin-chip.warn {
      border-color: rgba(154, 106, 0, .28);
      color: var(--uno-warn);
      background: rgba(154, 106, 0, .07);
    }

    .uno-admin-modal {
      position: fixed;
      inset: 0;
      z-index: 100;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 24px;
      background: rgba(0, 0, 0, .28);
      backdrop-filter: blur(8px);
    }

    .uno-admin-modal.is-open {
      display: flex;
    }

    .uno-admin-modal-panel {
      width: min(760px, 100%);
      max-height: min(82vh, 720px);
      overflow: auto;
      border: 1px solid var(--uno-line);
      background: var(--uno-panel);
      padding: 24px;
    }

    .uno-admin-modal-head {
      display: flex;
      align-items: start;
      justify-content: space-between;
      gap: 18px;
      margin-bottom: 18px;
    }

    .uno-admin-modal-head h2 {
      margin: 0;
      font-size: 28px;
      line-height: 1.15;
    }

    .uno-admin-close {
      width: 42px;
      height: 42px;
      position: relative;
      flex: 0 0 auto;
      border: 1px solid var(--uno-line);
      background: #fff;
      cursor: pointer;
      font-size: 0;
    }

    .uno-admin-close::before,
    .uno-admin-close::after {
      content: "";
      position: absolute;
      left: 11px;
      right: 11px;
      top: 20px;
      height: 2px;
      background: var(--uno-ink);
    }

    .uno-admin-close::before { transform: rotate(45deg); }
    .uno-admin-close::after { transform: rotate(-45deg); }

    @media (max-width: 1120px) {
      .uno-admin-header,
      .uno-admin-pagehead,
      .uno-admin-grid {
        grid-template-columns: 1fr;
      }

      .uno-admin-nav {
        justify-content: flex-start;
      }

      .uno-admin-utility,
      .uno-admin-actions {
        justify-content: flex-start;
      }
    }

    @media (max-width: 760px) {
      .uno-admin-shell {
        width: min(100% - 28px, 1480px);
      }

      .uno-admin-menu {
        width: 100%;
      }

      .uno-admin-menu > a {
        width: 100%;
        justify-content: flex-start;
      }

      .uno-admin-submenu {
        position: static;
        width: 100%;
        transform: none;
        box-shadow: none;
      }
    }
  </style>
</head>
<body>
<?php
}

function uno_renewal_admin_render_header()
{
    $menus = uno_renewal_admin_menu_groups();
    ?>
  <main class="uno-admin-shell">
    <header class="uno-admin-header">
      <a class="uno-admin-logo" href="/admin/renewal/index.php" aria-label="UNO Travel renewal admin">
        UNO<span>T R A V E L</span>
      </a>
      <nav class="uno-admin-nav" aria-label="Renewal admin menu">
        <?php foreach ($menus as $menu) { ?>
          <div class="uno-admin-menu">
            <a href="<?php echo uno_renewal_admin_escape($menu['href']); ?>"><?php echo uno_renewal_admin_escape($menu['label']); ?></a>
            <div class="uno-admin-submenu">
              <?php foreach ($menu['items'] as $item) { ?>
                <a href="<?php echo uno_renewal_admin_escape($item['href']); ?>"><?php echo uno_renewal_admin_escape($item['label']); ?></a>
              <?php } ?>
            </div>
          </div>
        <?php } ?>
      </nav>
      <div class="uno-admin-utility">
        <a href="/">Front 가기</a>
        <a href="/admin/main.php">기존 관리자</a>
      </div>
    </header>
<?php
}

function uno_renewal_admin_render_pagehead($eyebrow, $title, $copy, $actions = array())
{
    ?>
    <section class="uno-admin-pagehead">
      <div>
        <p class="uno-admin-eyebrow"><?php echo uno_renewal_admin_escape($eyebrow); ?></p>
        <h1 class="uno-admin-title"><?php echo $title; ?></h1>
        <?php if ($copy !== '') { ?>
          <p class="uno-admin-copy"><?php echo uno_renewal_admin_escape($copy); ?></p>
        <?php } ?>
      </div>
      <?php if ($actions) { ?>
        <nav class="uno-admin-actions" aria-label="Page actions">
          <?php foreach ($actions as $action) { ?>
            <a class="uno-admin-button <?php echo !empty($action['secondary']) ? 'secondary' : ''; ?>" href="<?php echo uno_renewal_admin_escape($action['href']); ?>"<?php echo !empty($action['target']) ? ' target="' . uno_renewal_admin_escape($action['target']) . '"' : ''; ?>>
              <?php echo uno_renewal_admin_escape($action['label']); ?>
            </a>
          <?php } ?>
        </nav>
      <?php } ?>
    </section>
<?php
}

function uno_renewal_admin_render_footer()
{
    ?>
  </main>
</body>
</html>
<?php
}
