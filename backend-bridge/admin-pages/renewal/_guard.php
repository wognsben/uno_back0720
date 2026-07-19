<?php
/*
 * _guard.php
 * Shared access guard for renewal admin pages running inside the legacy Cafe24/Gnuboard admin area.
 * It loads the API bootstrap session, checks admin access, and renders a page-friendly login or permission notice instead of JSON API errors.
 * This helper keeps renewal admin pages separate from public APIs while reusing the same legacy login session.
 */

require_once dirname(__DIR__, 2) . '/api/_bootstrap.php';

function uno_renewal_admin_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function uno_renewal_admin_render_access_notice($title, $message, $currentPath, $showLoginForm)
{
    ?><!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo uno_renewal_admin_escape($title); ?></title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      min-height: 100vh;
      margin: 0;
      display: grid;
      place-items: center;
      background: #f4f4f2;
      color: #111;
      font-family: Arial, "Noto Sans KR", sans-serif;
      letter-spacing: 0;
    }

    .panel {
      width: min(560px, calc(100% - 32px));
      padding: 36px;
      border: 1px solid #e5e5e2;
      background: #fff;
    }

    .eyebrow {
      margin: 0 0 12px;
      color: #767676;
      font-size: 12px;
      letter-spacing: 3px;
      text-transform: uppercase;
    }

    h1 {
      margin: 0;
      font-size: clamp(34px, 7vw, 56px);
      line-height: .95;
    }

    p {
      margin: 20px 0 0;
      color: #666;
      font-size: 16px;
      line-height: 1.75;
      word-break: keep-all;
    }

    form {
      display: grid;
      gap: 10px;
      margin-top: 24px;
    }

    label {
      color: #777;
      font-size: 12px;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    input {
      width: 100%;
      min-height: 48px;
      padding: 0 14px;
      border: 1px solid #ddd;
      background: #fafafa;
      color: #111;
      font: inherit;
    }

    .actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 20px;
    }

    .button,
    button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 48px;
      padding: 0 18px;
      border: 1px solid #111;
      background: #111;
      color: #fff;
      font: inherit;
      font-weight: 700;
      text-decoration: none;
      cursor: pointer;
    }

    .button.secondary {
      background: transparent;
      color: #111;
    }
  </style>
</head>
<body>
  <main class="panel">
    <p class="eyebrow">UNO Travel Renewal Admin</p>
    <h1><?php echo uno_renewal_admin_escape($title); ?></h1>
    <p><?php echo uno_renewal_admin_escape($message); ?></p>

    <?php if ($showLoginForm) { ?>
      <form name="flogin" action="/bbs/bbs/login_check.php" method="post">
        <input type="hidden" name="url" value="<?php echo uno_renewal_admin_escape($currentPath); ?>">
        <div>
          <label for="renewal-admin-id">Admin ID</label>
          <input id="renewal-admin-id" type="text" name="mb_id" autocomplete="username" required>
        </div>
        <div>
          <label for="renewal-admin-password">Password</label>
          <input id="renewal-admin-password" type="password" name="mb_password" autocomplete="current-password" required>
        </div>
        <div class="actions">
          <button type="submit">로그인</button>
          <a class="button secondary" href="/admin/index.php">기존 로그인 화면</a>
        </div>
      </form>
    <?php } else { ?>
      <div class="actions">
        <a class="button" href="/admin/main.php">관리자 메인</a>
        <a class="button secondary" href="/">Front 가기</a>
      </div>
    <?php } ?>
  </main>
</body>
</html><?php
}

function uno_renewal_admin_require_access($currentPath)
{
    if (!uno_api_is_logged_in()) {
        uno_renewal_admin_render_access_notice(
            'Login Required',
            '리뉴얼 관리자 화면을 보려면 기존 우노트래블 관리자 계정으로 로그인해야 합니다.',
            $currentPath,
            true
        );
        exit;
    }

    if (!uno_api_is_admin()) {
        uno_renewal_admin_render_access_notice(
            'Permission Required',
            '현재 로그인 계정에는 리뉴얼 관리자 화면을 볼 권한이 없습니다.',
            $currentPath,
            true
        );
        exit;
    }
}
