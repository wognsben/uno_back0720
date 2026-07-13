<?php
/*
 * community-qna.php
 * Renewal admin page for the public community Q&A board only.
 *
 * Scope:
 * - Uses legacy Gnuboard board `bo_table=qna`.
 * - This is the public "묻고답하기" community board.
 * - This is NOT 1:1 inquiry (`cusTour`).
 * - This is NOT product-detail bottom FAQ/QNA (`g5_faq`).
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

uno_renewal_admin_require_access('/admin/renewal/community-qna.php');

function uno_renewal_qna_escape_db($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_renewal_qna_query($sql)
{
    return function_exists('sql_query') ? sql_query($sql) : false;
}

function uno_renewal_qna_fetch($sql)
{
    if (!function_exists('sql_fetch')) {
        return array();
    }

    $row = sql_fetch($sql);
    return is_array($row) ? $row : array();
}

function uno_renewal_qna_fetch_array($result)
{
    return $result && function_exists('sql_fetch_array') ? sql_fetch_array($result) : false;
}

function uno_renewal_qna_write_table()
{
    global $g5;
    $prefix = isset($g5['write_prefix']) && $g5['write_prefix'] !== ''
        ? $g5['write_prefix']
        : 'g5_write_';

    return $prefix . 'qna';
}

function uno_renewal_qna_text($value, $limit = 0)
{
    $html = preg_replace('/<\s*br\s*\/?>/i', "\n", (string) $value);
    $text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8'));
    $text = preg_replace("/[ \t]+/", " ", $text);

    if ($limit > 0) {
        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, $limit, 'UTF-8');
        }

        return substr($text, 0, $limit);
    }

    return $text;
}

function uno_renewal_qna_date($value)
{
    if ($value === null || $value === '') {
        return '-';
    }

    return substr((string) $value, 0, 16);
}

$table = uno_renewal_qna_write_table();
$keyword = isset($_GET['keyword']) ? trim((string) $_GET['keyword']) : '';
$status = isset($_GET['status']) ? trim((string) $_GET['status']) : '';

$where = array("wr_is_comment = '0'");

if ($keyword !== '') {
    $safeKeyword = uno_renewal_qna_escape_db($keyword);
    $where[] = "(wr_subject like '%{$safeKeyword}%' or wr_content like '%{$safeKeyword}%' or mb_id like '%{$safeKeyword}%' or wr_name like '%{$safeKeyword}%')";
}

if ($status === 'unanswered') {
    $where[] = "wr_comment = '0'";
} elseif ($status === 'answered') {
    $where[] = "wr_comment > '0'";
}

$whereSql = implode(' and ', $where);

$rows = array();
$result = uno_renewal_qna_query(
    "select wr_id, wr_subject, wr_content, wr_name, mb_id, wr_datetime, wr_last, wr_comment, wr_hit, wr_ip
       from {$table}
      where {$whereSql}
      order by wr_id desc
      limit 100"
);

while ($row = uno_renewal_qna_fetch_array($result)) {
    $rows[] = $row;
}

$today = defined('G5_TIME_YMD') ? G5_TIME_YMD : date('Y-m-d');
$totalRow = uno_renewal_qna_fetch("select count(*) as cnt from {$table} where wr_is_comment = '0'");
$todayRow = uno_renewal_qna_fetch("select count(*) as cnt from {$table} where wr_is_comment = '0' and substr(wr_datetime, 1, 10) = '{$today}'");
$unansweredRow = uno_renewal_qna_fetch("select count(*) as cnt from {$table} where wr_is_comment = '0' and wr_comment = '0'");

uno_renewal_admin_render_head('공개 묻고답하기 관리');
uno_renewal_admin_render_header();
uno_renewal_admin_render_pagehead(
    'COMMUNITY Q&A',
    '공개 묻고답하기 관리',
    '커뮤니티 문의하기에서 등록되는 공개 게시판입니다. 1:1 문의(cusTour)와 상품상세 FAQ(g5_faq)는 이 화면에서 다루지 않습니다.',
    array(
        array('label' => '기존 qna 관리자', 'href' => '/admin/board.php?bo_table=qna', 'secondary' => true),
        array('label' => '프런트 문의하기', 'href' => '/community/inquiry', 'secondary' => true, 'target' => '_blank'),
    )
);
?>

<section class="uno-admin-grid">
  <article class="uno-admin-card">
    <div>
      <h3>전체 공개 문의</h3>
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
  <form method="get" style="display:grid;grid-template-columns:180px minmax(220px,1fr) auto;gap:8px;align-items:center;">
    <select name="status" style="height:42px;border:1px solid var(--uno-line);padding:0 10px;background:#fff;">
      <option value="">전체 상태</option>
      <option value="unanswered"<?php echo $status === 'unanswered' ? ' selected' : ''; ?>>미답변</option>
      <option value="answered"<?php echo $status === 'answered' ? ' selected' : ''; ?>>답변완료</option>
    </select>
    <input name="keyword" value="<?php echo uno_renewal_admin_escape($keyword); ?>" placeholder="제목, 내용, 작성자, 회원 ID 검색" style="height:42px;border:1px solid var(--uno-line);padding:0 12px;">
    <button class="uno-admin-button" type="submit">검색</button>
  </form>
</section>

<section class="uno-admin-panel" style="margin-top:16px;overflow:auto;">
  <table style="width:100%;border-collapse:collapse;min-width:1080px;table-layout:fixed;">
    <colgroup>
      <col style="width:82px;">
      <col>
      <col style="width:180px;">
      <col style="width:150px;">
      <col style="width:120px;">
      <col style="width:220px;">
    </colgroup>
    <thead>
      <tr>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">번호</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">제목/내용</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">작성자</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">등록일</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">답변</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">관리</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows) { ?>
        <tr>
          <td colspan="6" style="padding:28px 12px;color:var(--uno-muted);">공개 문의 내역이 없습니다.</td>
        </tr>
      <?php } ?>
      <?php foreach ($rows as $row) {
          $wrId = isset($row['wr_id']) ? (int) $row['wr_id'] : 0;
          $subject = uno_renewal_qna_text($row['wr_subject'] ?? '');
          if ($subject === '') {
              $subject = '(제목 없음)';
          }
          $commentCount = isset($row['wr_comment']) ? (int) $row['wr_comment'] : 0;
          $legacyView = '/admin/board.php?bo_table=qna&wr_id=' . $wrId;
          $frontView = '/community/inquiry/' . $wrId;
      ?>
        <tr>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;"><?php echo $wrId; ?></td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;">
            <strong><?php echo uno_renewal_admin_escape($subject); ?></strong>
            <div style="margin-top:6px;color:var(--uno-muted);font-size:12px;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              <?php echo uno_renewal_admin_escape(uno_renewal_qna_text($row['wr_content'] ?? '', 140)); ?>
            </div>
            <div style="margin-top:5px;color:var(--uno-muted);font-size:12px;">조회 <?php echo (int) ($row['wr_hit'] ?? 0); ?> · IP <?php echo uno_renewal_admin_escape((string) ($row['wr_ip'] ?? '-')); ?></div>
          </td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;">
            <?php echo uno_renewal_admin_escape(uno_renewal_qna_text($row['wr_name'] ?? '')); ?>
            <div style="color:var(--uno-muted);font-size:12px;"><?php echo uno_renewal_admin_escape((string) ($row['mb_id'] ?? '')); ?></div>
          </td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;"><?php echo uno_renewal_admin_escape(uno_renewal_qna_date($row['wr_datetime'] ?? '')); ?></td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;">
            <span class="uno-admin-chip <?php echo $commentCount > 0 ? 'good' : 'warn'; ?>">
              <?php echo $commentCount > 0 ? '답변 ' . $commentCount . '건' : '미답변'; ?>
            </span>
          </td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;white-space:nowrap;">
            <a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape($legacyView); ?>">답변/수정</a>
            <a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape($frontView); ?>" target="_blank">프런트</a>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</section>

<?php
uno_renewal_admin_render_footer();
