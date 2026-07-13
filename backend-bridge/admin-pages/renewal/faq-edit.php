<?php
/*
 * faq-edit.php
 * Renewal admin create/update screen for product-detail bottom FAQ/QNA.
 *
 * This edits g5_faq rows used under product detail body images. It is separate
 * from the public community Q&A board (`bo_table=qna`).
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';
require_once dirname(__DIR__, 2) . '/api/_product_mapping_store.php';

uno_renewal_admin_require_access('/admin/renewal/faq-edit.php');

function uno_renewal_faq_edit_table($key, $fallback)
{
    global $g5;
    return isset($g5[$key]) && $g5[$key] !== '' ? $g5[$key] : $fallback;
}

function uno_renewal_faq_edit_escape_db($value)
{
    if (function_exists('sql_escape_string')) {
        return sql_escape_string((string) $value);
    }

    return addslashes((string) $value);
}

function uno_renewal_faq_edit_fetch($sql)
{
    if (!function_exists('sql_fetch')) {
        return array();
    }

    $row = sql_fetch($sql);
    return is_array($row) ? $row : array();
}

function uno_renewal_faq_edit_query($sql)
{
    return function_exists('sql_query') ? sql_query($sql) : false;
}

function uno_renewal_faq_edit_fetch_array($result)
{
    return $result && function_exists('sql_fetch_array') ? sql_fetch_array($result) : false;
}

function uno_renewal_faq_edit_text($value)
{
    $html = preg_replace('/<\s*br\s*\/?>/i', "\n", (string) $value);
    return trim(html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8'));
}

function uno_renewal_faq_edit_redirect($url)
{
    header('Location: ' . $url);
    exit;
}

$faqTable = uno_renewal_faq_edit_table('faq_table', 'g5_faq');
$faqMasterTable = uno_renewal_faq_edit_table('faq_master_table', 'g5_faq_master');
$productTable = isset($g5['write_prefix']) ? $g5['write_prefix'] . 'product' : 'g5_write_product';
$method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper((string) $_SERVER['REQUEST_METHOD']) : 'GET';

if ($method === 'POST') {
    $w = isset($_POST['w']) ? (string) $_POST['w'] : '';
    $faId = isset($_POST['fa_id']) ? (int) $_POST['fa_id'] : 0;
    $fmId = isset($_POST['fm_id']) ? (int) $_POST['fm_id'] : 0;
    $subject = uno_renewal_faq_edit_text($_POST['fa_subject'] ?? '');
    $content = isset($_POST['fa_content']) ? (string) $_POST['fa_content'] : '';
    $order = isset($_POST['fa_order']) ? (int) $_POST['fa_order'] : 0;
    $pidValues = isset($_POST['pid']) && is_array($_POST['pid']) ? $_POST['pid'] : array();
    $cleanPids = array();

    foreach ($pidValues as $pidValue) {
        $pid = (int) $pidValue;
        if ($pid > 0) {
            $cleanPids[] = (string) $pid;
        }
    }

    $pidCsv = implode(',', array_values(array_unique($cleanPids)));
    $safeSubject = uno_renewal_faq_edit_escape_db($subject);
    $safeContent = uno_renewal_faq_edit_escape_db($content);
    $safePidCsv = uno_renewal_faq_edit_escape_db($pidCsv);

    if ($fmId <= 0) {
        $fm = uno_renewal_faq_edit_fetch("select fm_id from {$faqMasterTable} order by fm_order asc, fm_id asc limit 1");
        $fmId = isset($fm['fm_id']) ? (int) $fm['fm_id'] : 0;
    }

    if ($w === 'u' && $faId > 0) {
        uno_renewal_faq_edit_query(
            "update {$faqTable}
                set fm_id = '{$fmId}',
                    fa_subject = '{$safeSubject}',
                    fa_content = '{$safeContent}',
                    fa_order = '{$order}',
                    pid = '{$safePidCsv}'
              where fa_id = '{$faId}'"
        );
    } else {
        uno_renewal_faq_edit_query(
            "insert into {$faqTable}
                set fm_id = '{$fmId}',
                    fa_subject = '{$safeSubject}',
                    fa_content = '{$safeContent}',
                    fa_order = '{$order}',
                    pid = '{$safePidCsv}'"
        );
    }

    uno_renewal_faq_edit_redirect('/admin/renewal/faqs.php?fm_id=' . $fmId);
}

$w = isset($_GET['w']) ? (string) $_GET['w'] : '';
$faId = isset($_GET['fa_id']) ? (int) $_GET['fa_id'] : 0;
$fmId = isset($_GET['fm_id']) ? (int) $_GET['fm_id'] : 0;
$faq = array(
    'fa_id' => 0,
    'fm_id' => $fmId,
    'pid' => '',
    'fa_subject' => '',
    'fa_content' => '',
    'fa_order' => 1,
);

if ($w === 'u' && $faId > 0) {
    $loaded = uno_renewal_faq_edit_fetch("select * from {$faqTable} where fa_id = '{$faId}' limit 1");
    if ($loaded && !empty($loaded['fa_id'])) {
        $faq = $loaded;
        $fmId = isset($faq['fm_id']) ? (int) $faq['fm_id'] : $fmId;
    }
}

$selectedPids = array_filter(array_map('trim', explode(',', (string) ($faq['pid'] ?? ''))));
$categories = array();
$categoryResult = uno_renewal_faq_edit_query("select fm_id, fm_subject from {$faqMasterTable} order by fm_order asc, fm_id asc");
while ($row = uno_renewal_faq_edit_fetch_array($categoryResult)) {
    $categories[] = $row;
}

if ($fmId <= 0 && $categories) {
    $fmId = (int) $categories[0]['fm_id'];
}

$products = array();
$productMappingByLegacyId = array();

if (function_exists('uno_api_product_mapping_fetch_rows')) {
    foreach (uno_api_product_mapping_fetch_rows(false) as $mapping) {
        $legacyProductId = isset($mapping['legacyProductId']) ? (string) (int) $mapping['legacyProductId'] : '';
        if ($legacyProductId !== '' && $legacyProductId !== '0') {
            $productMappingByLegacyId[$legacyProductId] = $mapping;
        }
    }
}

$productResult = uno_renewal_faq_edit_query(
    "select wr_id, wr_subject, ca_name
       from {$productTable}
      where wr_is_comment = '0'
      order by ca_name asc, wr_subject asc
      limit 1200"
);
while ($row = uno_renewal_faq_edit_fetch_array($productResult)) {
    $legacyProductId = isset($row['wr_id']) ? (string) (int) $row['wr_id'] : '';
    $mapping = isset($productMappingByLegacyId[$legacyProductId]) ? $productMappingByLegacyId[$legacyProductId] : null;

    $row['_mappingProductId'] = $mapping && isset($mapping['productId']) ? (string) $mapping['productId'] : '';
    $row['_isMapped'] = $row['_mappingProductId'] !== '';
    $row['_isActive'] = $mapping ? (!isset($mapping['isActive']) || !!$mapping['isActive']) : false;
    $row['_sortOrder'] = $mapping && isset($mapping['sortOrder']) ? (int) $mapping['sortOrder'] : 999999;
    $row['_productGroup'] = $row['_isMapped'] && $row['_isActive'] ? 'visible' : ($row['_isMapped'] ? 'hidden' : 'legacy');
    $products[] = $row;
}

usort($products, function ($a, $b) {
    $priority = array('visible' => 0, 'hidden' => 1, 'legacy' => 2);
    $aPriority = isset($priority[$a['_productGroup']]) ? $priority[$a['_productGroup']] : 3;
    $bPriority = isset($priority[$b['_productGroup']]) ? $priority[$b['_productGroup']] : 3;

    if ($aPriority !== $bPriority) {
        return $aPriority - $bPriority;
    }

    $aOrder = isset($a['_sortOrder']) ? (int) $a['_sortOrder'] : 999999;
    $bOrder = isset($b['_sortOrder']) ? (int) $b['_sortOrder'] : 999999;
    if ($aOrder !== $bOrder) {
        return $aOrder - $bOrder;
    }

    $aName = uno_renewal_faq_edit_text(($a['ca_name'] ?? '') . ' ' . ($a['wr_subject'] ?? ''));
    $bName = uno_renewal_faq_edit_text(($b['ca_name'] ?? '') . ' ' . ($b['wr_subject'] ?? ''));
    return strcmp($aName, $bName);
});

$productGroups = array(
    'visible' => array('label' => '프런트 노출 상품', 'items' => array()),
    'hidden' => array('label' => '매핑됨 / 숨김 상품', 'items' => array()),
    'legacy' => array('label' => '미연결 기존 상품', 'items' => array()),
);

foreach ($products as $product) {
    $groupKey = isset($product['_productGroup']) ? $product['_productGroup'] : 'legacy';
    if (!isset($productGroups[$groupKey])) {
        $groupKey = 'legacy';
    }
    $productGroups[$groupKey]['items'][] = $product;
}

uno_renewal_admin_render_head($w === 'u' ? 'FAQ 수정' : 'FAQ 추가');
uno_renewal_admin_render_header();
uno_renewal_admin_render_pagehead(
    'PRODUCT DETAIL FAQ',
    $w === 'u' ? 'FAQ 수정' : 'FAQ 추가',
    '상품 상세페이지 바디 이미지 하단에 노출되는 FAQ/QNA를 관리합니다. 상품을 선택하지 않으면 전체 상품에 공통 노출됩니다.',
    array(
        array('label' => 'FAQ 목록', 'href' => '/admin/renewal/faqs.php?fm_id=' . $fmId, 'secondary' => true),
        array('label' => '기존 FAQ 폼', 'href' => '/admin/faqform.php' . ($w === 'u' ? '?w=u&fm_id=' . $fmId . '&fa_id=' . $faId : '?fm_id=' . $fmId), 'secondary' => true),
    )
);
?>

<section class="uno-admin-panel">
  <form method="post" style="display:grid;gap:18px;">
    <input type="hidden" name="w" value="<?php echo uno_renewal_admin_escape($w === 'u' ? 'u' : ''); ?>">
    <input type="hidden" name="fa_id" value="<?php echo (int) ($faq['fa_id'] ?? 0); ?>">

    <label style="display:grid;gap:8px;">
      <strong>FAQ 카테고리</strong>
      <select name="fm_id" required style="height:44px;border:1px solid var(--uno-line);padding:0 12px;background:#fff;">
        <?php foreach ($categories as $category) {
            $categoryId = isset($category['fm_id']) ? (int) $category['fm_id'] : 0;
        ?>
          <option value="<?php echo $categoryId; ?>"<?php echo $categoryId === $fmId ? ' selected' : ''; ?>>
            <?php echo uno_renewal_admin_escape(uno_renewal_faq_edit_text($category['fm_subject'] ?? '')); ?>
          </option>
        <?php } ?>
      </select>
    </label>

    <label style="display:grid;gap:8px;">
      <strong>질문</strong>
      <input name="fa_subject" value="<?php echo uno_renewal_admin_escape(uno_renewal_faq_edit_text($faq['fa_subject'] ?? '')); ?>" required style="height:46px;border:1px solid var(--uno-line);padding:0 12px;">
    </label>

    <label style="display:grid;gap:8px;">
      <strong>답변 HTML</strong>
      <textarea name="fa_content" rows="12" style="width:100%;border:1px solid var(--uno-line);padding:12px;line-height:1.7;"><?php echo uno_renewal_admin_escape((string) ($faq['fa_content'] ?? '')); ?></textarea>
    </label>

    <label style="display:grid;gap:8px;">
      <strong>연결 상품</strong>
      <select name="pid[]" multiple size="12" data-product-select style="width:100%;border:1px solid var(--uno-line);padding:8px;background:#fff;">
        <?php foreach ($productGroups as $group) {
            if (empty($group['items'])) {
                continue;
            }
        ?>
          <optgroup label="<?php echo uno_renewal_admin_escape($group['label']); ?>">
            <?php foreach ($group['items'] as $product) {
            $productId = isset($product['wr_id']) ? (string) $product['wr_id'] : '';
            $selected = in_array($productId, $selectedPids, true);
            $mappedProductId = isset($product['_mappingProductId']) ? (string) $product['_mappingProductId'] : '';
            $statusLabel = $product['_productGroup'] === 'visible' ? '노출' : ($product['_productGroup'] === 'hidden' ? '숨김' : '미연결');
        ?>
          <option value="<?php echo uno_renewal_admin_escape($productId); ?>"<?php echo $selected ? ' selected' : ''; ?>>
            [<?php echo uno_renewal_admin_escape($statusLabel); ?>][<?php echo uno_renewal_admin_escape($productId); ?><?php echo $mappedProductId !== '' ? ' / ' . uno_renewal_admin_escape($mappedProductId) : ''; ?>] <?php echo uno_renewal_admin_escape(uno_renewal_faq_edit_text($product['ca_name'] ?? '')); ?> - <?php echo uno_renewal_admin_escape(uno_renewal_faq_edit_text($product['wr_subject'] ?? '')); ?>
          </option>
            <?php } ?>
          </optgroup>
        <?php } ?>
      </select>
      <span style="color:var(--uno-muted);font-size:13px;">상품은 클릭할 때마다 선택/해제됩니다. 다른 상품을 클릭해도 기존 선택은 유지됩니다. 선택하지 않으면 전체 상품에 표시됩니다.</span>
    </label>

    <label style="display:grid;gap:8px;max-width:220px;">
      <strong>출력 순서</strong>
      <input name="fa_order" type="number" value="<?php echo (int) ($faq['fa_order'] ?? 1); ?>" style="height:46px;border:1px solid var(--uno-line);padding:0 12px;">
    </label>

    <div class="uno-admin-actions" style="justify-content:flex-start;">
      <button class="uno-admin-button" type="submit">저장</button>
      <a class="uno-admin-button secondary" href="/admin/renewal/faqs.php?fm_id=<?php echo $fmId; ?>">취소</a>
    </div>
  </form>
</section>

<script>
  (function () {
    const select = document.querySelector("[data-product-select]");
    if (!select) return;

    select.addEventListener("mousedown", function (event) {
      const option = event.target;
      if (!option || option.tagName !== "OPTION") return;

      event.preventDefault();
      option.selected = !option.selected;
      select.dispatchEvent(new Event("change", { bubbles: true }));
    });
  })();
</script>

<?php
uno_renewal_admin_render_footer();
