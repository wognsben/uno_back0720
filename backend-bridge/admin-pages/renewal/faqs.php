<?php
/*
 * faqs.php
 * Renewal admin list for product-detail bottom FAQ/QNA content.
 *
 * This is NOT the public community Q&A board (`bo_table=qna`).
 * These rows are legacy FAQ records from g5_faq/g5_faq_master and are rendered
 * under the product detail body image through products/detail.php as `faqs`.
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

uno_renewal_admin_require_access('/admin/renewal/faqs.php');

function uno_renewal_faq_table($key, $fallback)
{
    global $g5;
    return isset($g5[$key]) && $g5[$key] !== '' ? $g5[$key] : $fallback;
}

function uno_renewal_faq_escape_db($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_renewal_faq_fetch($sql)
{
    if (!function_exists('sql_fetch')) {
        return array();
    }

    $row = sql_fetch($sql);
    return is_array($row) ? $row : array();
}

function uno_renewal_faq_query($sql)
{
    return function_exists('sql_query') ? sql_query($sql) : false;
}

function uno_renewal_faq_fetch_array($result)
{
    return $result && function_exists('sql_fetch_array') ? sql_fetch_array($result) : false;
}

function uno_renewal_faq_text($value, $limit = 0)
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

function uno_renewal_faq_product_names($pid)
{
    $pid = trim((string) $pid);

    if ($pid === '') {
        return '전체 상품';
    }

    if (function_exists('get_prod_name')) {
        $name = get_prod_name($pid);
        if (trim((string) $name) !== '') {
            return uno_renewal_faq_text($name);
        }
    }

    return $pid;
}

$faqTable = uno_renewal_faq_table('faq_table', 'g5_faq');
$faqMasterTable = uno_renewal_faq_table('faq_master_table', 'g5_faq_master');
$fmId = isset($_GET['fm_id']) ? (int) $_GET['fm_id'] : 0;
$keyword = isset($_GET['keyword']) ? trim((string) $_GET['keyword']) : '';

$where = array();

if ($fmId > 0) {
    $where[] = "f.fm_id = '{$fmId}'";
}

if ($keyword !== '') {
    $safeKeyword = uno_renewal_faq_escape_db($keyword);
    $where[] = "(f.fa_subject like '%{$safeKeyword}%' or f.fa_content like '%{$safeKeyword}%' or f.pid like '%{$safeKeyword}%')";
}

$whereSql = $where ? 'where ' . implode(' and ', $where) : '';

$categories = array();
$categoryResult = uno_renewal_faq_query("select fm_id, fm_subject from {$faqMasterTable} order by fm_order asc, fm_id asc");
while ($row = uno_renewal_faq_fetch_array($categoryResult)) {
    $categories[] = $row;
}

$rows = array();
$result = uno_renewal_faq_query(
    "select f.fa_id, f.fm_id, f.pid, f.fa_subject, f.fa_content, f.fa_order, m.fm_subject
       from {$faqTable} f
       left join {$faqMasterTable} m on m.fm_id = f.fm_id
       {$whereSql}
      order by f.fa_order asc, f.fa_id asc
      limit 200"
);
while ($row = uno_renewal_faq_fetch_array($result)) {
    $rows[] = $row;
}

$totalRow = uno_renewal_faq_fetch("select count(*) as cnt from {$faqTable}");
$linkedRow = uno_renewal_faq_fetch("select count(*) as cnt from {$faqTable} where pid is not null and pid <> ''");
$globalRow = uno_renewal_faq_fetch("select count(*) as cnt from {$faqTable} where pid is null or pid = ''");
$firstCategory = $categories ? $categories[0] : array('fm_id' => $fmId);
$createFmId = isset($firstCategory['fm_id']) ? (int) $firstCategory['fm_id'] : $fmId;

uno_renewal_admin_render_head('상품 상세 FAQ 관리');
uno_renewal_admin_render_header();
uno_renewal_admin_render_pagehead(
    'PRODUCT DETAIL FAQ',
    '상품 상세 FAQ 관리',
    '상품 상세페이지 바디 이미지 하단에 노출되는 FAQ/QNA입니다. 커뮤니티 묻고답하기 게시판이 아니며, 기존 g5_faq의 pid 연결 기준으로 상품별 노출됩니다.',
    array(
        array('label' => 'FAQ 추가', 'href' => '/admin/renewal/faq-edit.php?fm_id=' . $createFmId),
        array('label' => '기존 FAQ 목록', 'href' => '/admin/faqlist.php', 'secondary' => true),
        array('label' => '프런트 FAQ', 'href' => '/contents/faq.php', 'secondary' => true, 'target' => '_blank'),
    )
);
?>

<section class="uno-admin-grid">
  <article class="uno-admin-card">
    <div>
      <h3>전체 FAQ</h3>
      <p><?php echo number_format((int) ($totalRow['cnt'] ?? 0)); ?>건</p>
    </div>
  </article>
  <article class="uno-admin-card">
    <div>
      <h3>상품 연결</h3>
      <p><?php echo number_format((int) ($linkedRow['cnt'] ?? 0)); ?>건</p>
    </div>
  </article>
  <article class="uno-admin-card">
    <div>
      <h3>전체 상품 공통</h3>
      <p><?php echo number_format((int) ($globalRow['cnt'] ?? 0)); ?>건</p>
    </div>
  </article>
</section>

<section class="uno-admin-panel" style="margin-top:16px;">
  <form method="get" style="display:grid;grid-template-columns:220px minmax(220px,1fr) auto;gap:8px;align-items:center;">
    <select name="fm_id" style="height:42px;border:1px solid var(--uno-line);padding:0 10px;background:#fff;">
      <option value="0">전체 카테고리</option>
      <?php foreach ($categories as $category) {
          $categoryId = isset($category['fm_id']) ? (int) $category['fm_id'] : 0;
      ?>
        <option value="<?php echo $categoryId; ?>"<?php echo $categoryId === $fmId ? ' selected' : ''; ?>>
          <?php echo uno_renewal_admin_escape(uno_renewal_faq_text($category['fm_subject'] ?? '')); ?>
        </option>
      <?php } ?>
    </select>
    <input name="keyword" value="<?php echo uno_renewal_admin_escape($keyword); ?>" placeholder="제목, 내용, 상품 ID 검색" style="height:42px;border:1px solid var(--uno-line);padding:0 12px;">
    <button class="uno-admin-button" type="submit">검색</button>
  </form>
</section>

<section class="uno-admin-panel" style="margin-top:16px;overflow:auto;">
  <table style="width:100%;border-collapse:collapse;min-width:1160px;table-layout:fixed;">
    <colgroup>
      <col style="width:64px;">
      <col style="width:128px;">
      <col>
      <col style="width:420px;">
      <col style="width:72px;">
      <col style="width:150px;">
    </colgroup>
    <thead>
      <tr>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">ID</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">카테고리</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">질문/답변</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">상품 연결</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">순서</th>
        <th style="text-align:left;border-bottom:1px solid var(--uno-line);padding:12px;">관리</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$rows) { ?>
        <tr>
          <td colspan="6" style="padding:28px 12px;color:var(--uno-muted);">FAQ 데이터가 없습니다.</td>
        </tr>
      <?php } ?>
      <?php foreach ($rows as $row) {
          $faId = isset($row['fa_id']) ? (int) $row['fa_id'] : 0;
          $rowFmId = isset($row['fm_id']) ? (int) $row['fm_id'] : 0;
          $pid = isset($row['pid']) ? (string) $row['pid'] : '';
          $editHref = '/admin/renewal/faq-edit.php?w=u&fm_id=' . $rowFmId . '&fa_id=' . $faId;
          $deleteHref = '/admin/faqformupdate.php?w=d&fm_id=' . $rowFmId . '&fa_id=' . $faId;
      ?>
        <tr>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;"><?php echo $faId; ?></td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;word-break:keep-all;"><?php echo uno_renewal_admin_escape(uno_renewal_faq_text($row['fm_subject'] ?? '-')); ?></td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;">
            <strong><?php echo uno_renewal_admin_escape(uno_renewal_faq_text($row['fa_subject'] ?? '')); ?></strong>
            <div style="margin-top:6px;color:var(--uno-muted);font-size:12px;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              <?php echo uno_renewal_admin_escape(uno_renewal_faq_text($row['fa_content'] ?? '', 120)); ?>
            </div>
          </td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;">
            <div style="max-height:92px;overflow:auto;line-height:1.55;">
              <?php echo nl2br(uno_renewal_admin_escape(uno_renewal_faq_product_names($pid))); ?>
            </div>
            <div style="margin-top:5px;color:var(--uno-muted);font-size:12px;"><?php echo $pid !== '' ? 'pid: ' . uno_renewal_admin_escape($pid) : '전체 상품 노출'; ?></div>
          </td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;"><?php echo (int) ($row['fa_order'] ?? 0); ?></td>
          <td style="border-bottom:1px solid var(--uno-line);padding:12px;white-space:nowrap;">
            <a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape($editHref); ?>">수정</a>
            <a class="uno-admin-button secondary" href="<?php echo uno_renewal_admin_escape($deleteHref); ?>" onclick="return confirm('FAQ를 삭제하시겠습니까?');">삭제</a>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</section>

<?php
uno_renewal_admin_render_footer();
