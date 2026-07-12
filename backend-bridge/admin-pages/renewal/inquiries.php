<?php
/*
 * inquiries.php
 * 리뉴얼 관리자에서 1:1 문의(cusTour)와 공개 문의(qna)를 분리해 조회하는 관리 페이지입니다.
 * 기존 /admin/board.php 래퍼를 대체하기 전 단계의 가독성 좋은 목록 허브 역할을 합니다.
 * 답변/삭제/스팸 일괄 처리 기능은 다음 단계에서 별도 액션으로 확장합니다.
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

uno_renewal_admin_require_access('/admin/renewal/inquiries.php');

function uno_renewal_inquiry_escape_db($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_renewal_inquiry_query($sql)
{
    return function_exists('sql_query') ? sql_query($sql) : false;
}

function uno_renewal_inquiry_fetch_array($result)
{
    return $result && function_exists('sql_fetch_array') ? sql_fetch_array($result) : false;
}

function uno_renewal_inquiry_fetch($sql)
{
    if (!function_exists('sql_fetch')) {
        return array();
    }

    $row = sql_fetch($sql);
    return is_array($row) ? $row : array();
}

function uno_renewal_inquiry_board_config($board)
{
    $map = array(
        'cusTour' => array(
            'board' => 'cusTour',
            'title' => '1:1 문의 관리',
            'copy' => '마이페이지 1:1 문의 내역입니다. 개인 상담이므로 기존 cusTour 게시판과 연결됩니다.',
            'legacyHref' => '/admin/board.php?bo_table=cusTour',
        ),
        'qna' => array(
            'board' => 'qna',
            'title' => '공개 문의 관리',
            'copy' => '커뮤니티 문의하기에서 등록되는 공개 문의입니다. 다른 사용자에게 공개될 수 있는 질문을 관리합니다.',
            'legacyHref' => '/admin/board.php?bo_table=qna',
        ),
    );

    return isset($map[$board]) ? $map[$board] : $map['cusTour'];
}

function uno_renewal_inquiry_write_table($board)
{
    global $g5;
    $prefix = isset($g5['write_prefix']) && $g5['write_prefix'] !== ''
        ? $g5['write_prefix']
        : 'g5_write_';

    return $prefix . $board;
}

function uno_renewal_inquiry_date($value)
{
    if ($value === null || $value === '') {
        return '-';
    }

    return substr((string) $value, 0, 16);
}

$boardParam = isset($_GET['board']) ? (string) $_GET['board'] : 'cusTour';
$config = uno_renewal_inquiry_board_config($boardParam);
$board = $config['board'];
$table = uno_renewal_inquiry_write_table($board);
$keyword = isset($_GET['keyword']) ? trim((string) $_GET['keyword']) : '';

$where = array("wr_is_comment = '0'");
if ($keyword !== '') {
    $safeKeyword = uno_renewal_inquiry_escape_db($keyword);
    $where[] = "(wr_subject like '%{$safeKeyword}%' or wr_content like '%{$safeKeyword}%' or mb_id like '%{$safeKeyword}%' or wr_name like '%{$safeKeyword}%')";
}
$whereSql = implode(' and ', $where);

$rows = array();
$result = uno_renewal_inquiry_query(
    "select wr_id, wr_subject, wr_content, wr_name, mb_id, wr_datetime, wr_last, wr_comment, wr_hit, wr_10
       from {$table}
      where {$whereSql}
      order by wr_id desc
      limit 80"
);

while ($row = uno_renewal_inquiry_fetch_array($result)) {
    $rows[] = $row;
}

$totalRow = uno_renewal_inquiry_fetch("select count(*) as cnt from {$table} where wr_is_comment = '0'");
$today = defined('G5_TIME_YMD') ? G5_TIME_YMD : date('Y-m-d');
$todayRow = uno_renewal_inquiry_fetch("select count(*) as cnt from {$table} where wr_is_comment = '0' and substr(wr_datetime, 1, 10) = '{$today}'");
$unansweredRow = uno_renewal_inquiry_fetch("select count(*) as cnt from {$table} where wr_is_comment = '0' and wr_comment = '0'");

uno_renewal_admin_render_head($config['title']);
uno_renewal_admin_render_header();
uno_renewal_admin_render_pagehead(
    'INQUIRY',
    $config['title'],
    $config['copy'],
    array(
        array('label' => '1:1 문의', 'href' => '/admin/renewal/inquiries.php?board=cusTour', 'secondary' => $board !== 'cusTour'),
        array('label' => '공개 문의', 'href' => '/admin/renewal/inquiries.php?board=qna', 'secondary' => $board !== 'qna'),
        array('label' => '기존 관리자 보기', 'href' => $config['legacyHref'], 'secondary' => true),
    )
);
?>

<section class="uno-admin-grid">
  <article class="uno-admin-card">
    <div>
      <h3>전체 문의</h3>
      <p><?php echo number_format((int) ($totalRow['cnt'] ?? 0)); ?>건</p>
    </div>
  </article>
  <article class="uno-admin-card">
    <div>
      <h3>오늘 등록</h3>
      <p><?php echo number_format((int) ($todayRow['cnt'] ?? 0)); ?>건</p>
    </div>
  </article>
  <article class="uno-admin-card">
    <div>
      <h3>미답변</h3>
      <p><?php echo number_format((int) ($unansweredRow['cnt'] ?? 0)); ?>건</p>
    </div>
  </article>
</section>

<section class="uno-admin-panel" style="margin-top:16px;">
  <form method="get" style="display:grid;grid-template-columns:auto minmax(220px, 1fr) auto;gap:8px;align-items:center;">
    <input type="hidden" name="board" value="<?php echo uno_renewal_admin_escape($board); ?>">
    <strong><?php echo uno_renewal_admin_escape($board === 'cusTour' ? '1:1 문의' : '공개 문의'); ?></strong>
    <input name="keyword" value="<?php echo uno_renewal_admin_escape($keyword); ?>" placeholder="제목, 내용, 작성자, 아이디 검색" style="height:42px;border:1px solid var(--uno-line);padding:0 12px;">
    <button class="uno-admin-button" type="submit">검색</button>
  </form>
</section>

<section class="uno-admin-panel" style="margin-top:16px;overflow:auto;">
  <table style="width:100%;border-collapse:collapse;min-width:920px;">
    <thead>
      <tr>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">번호</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">제목</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">작성자</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">등록일</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">답변</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">관리</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows) { ?>
        <tr>
          <td colspan="6" style="padding:28px 12px;color:var(--uno-muted);">문의 내역이 없습니다.</td>
        </tr>
      <?php } ?>
      <?php foreach ($rows as $row) {
          $wrId = isset($row['wr_id']) ? (int) $row['wr_id'] : 0;
          $subject = isset($row['wr_subject']) && $row['wr_subject'] !== '' ? (string) $row['wr_subject'] : '(제목 없음)';
          $commentCount = isset($row['wr_comment']) ? (int) $row['wr_comment'] : 0;
          $legacyView = '/admin/board.php?bo_table=' . rawurlencode($board) . '&wr_id=' . $wrId;
      ?>
        <tr>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;"><?php echo $wrId; ?></td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;">
            <strong><?php echo uno_renewal_admin_escape($subject); ?></strong>
            <div style="margin-top:6px;color:var(--uno-muted);font-size:12px;max-width:520px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              <?php echo uno_renewal_admin_escape(trim(strip_tags((string) ($row['wr_content'] ?? '')))); ?>
            </div>
          </td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;">
            <?php echo uno_renewal_admin_escape((string) ($row['wr_name'] ?? '')); ?>
            <div style="color:var(--uno-muted);font-size:12px;"><?php echo uno_renewal_admin_escape((string) ($row['mb_id'] ?? '')); ?></div>
          </td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;"><?php echo uno_renewal_admin_escape(uno_renewal_inquiry_date($row['wr_datetime'] ?? '')); ?></td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;">
            <span class="uno-admin-chip <?php echo $commentCount > 0 ? 'good' : 'warn'; ?>">
              <?php echo $commentCount > 0 ? '답변 ' . $commentCount . '개' : '미답변'; ?>
            </span>
          </td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;">
            <a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape($legacyView); ?>">기존 화면</a>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</section>

<?php
uno_renewal_admin_render_footer();
