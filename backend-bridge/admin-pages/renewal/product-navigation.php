<?php
/*
 * product-navigation.php
 * Renewal admin page for arranging mapped products inside SEMI/DAILY navigation country tabs.
 * It edits only navigation labels, regions, links, and visible mapped products while Product Mapping manages product identity.
 * This page stays separate from legacy product editing screens to keep frontend navigation changes lightweight and reversible.
 */

require_once __DIR__ . '/_guard.php';
require_once dirname(__DIR__, 2) . '/api/_product_map.php';
require_once dirname(__DIR__, 2) . '/api/_product_navigation_store.php';
require_once dirname(__DIR__, 2) . '/api/_reservation_helpers.php';

uno_renewal_admin_require_access('/admin/renewal/product-navigation.php');
uno_api_product_navigation_ensure_table();

$navigation = uno_api_product_navigation_fetch_groups();

if ($navigation === null) {
    $navigation = array(
        'groups' => uno_api_product_navigation_default_groups(),
    );
}

?><!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>UNO Renewal Product Navigation</title>
  <style>
    :root {
      --ink: #111;
      --muted: #727272;
      --line: #e4e4e0;
      --soft: #f5f5f3;
      --panel: #fff;
      --ok: #0f766e;
      --warn: #b7791f;
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

    button,
    input,
    textarea,
    select {
      font: inherit;
    }

    .page {
      width: min(1480px, calc(100% - 48px));
      margin: 0 auto;
      padding: 32px 0 64px;
    }

    .top {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 24px;
      align-items: end;
      padding-bottom: 24px;
      border-bottom: 1px solid var(--line);
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
      font-size: clamp(42px, 5.8vw, 84px);
      line-height: .92;
      letter-spacing: -1px;
    }

    .copy {
      max-width: 760px;
      margin: 18px 0 0;
      color: var(--muted);
      font-size: 16px;
      line-height: 1.75;
      word-break: keep-all;
    }

    .actions {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }

    .button {
      min-height: 44px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0 16px;
      border: 1px solid var(--ink);
      background: var(--ink);
      color: #fff;
      font-weight: 800;
      cursor: pointer;
    }

    .button.secondary {
      border-color: var(--line);
      background: var(--panel);
      color: var(--ink);
    }

    .status {
      min-height: 24px;
      margin: 18px 0 0;
      color: var(--muted);
    }

    .status.ok {
      color: var(--ok);
    }

    .status.warn {
      color: var(--warn);
    }

    .group {
      margin-top: 34px;
    }

    .group-head {
      display: flex;
      align-items: end;
      justify-content: space-between;
      gap: 24px;
      margin-bottom: 14px;
    }

    .group-title {
      margin: 0;
      font-size: 28px;
      letter-spacing: 1px;
    }

    .tabs {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px;
    }

    .card {
      border: 1px solid var(--line);
      background: var(--panel);
      padding: 18px;
      min-width: 0;
    }

    .card-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 16px;
    }

    .card-title {
      display: grid;
      gap: 4px;
      min-width: 0;
    }

    .card-title strong {
      font-size: 22px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .card-title span {
      color: var(--muted);
      font-size: 13px;
    }

    .ghost {
      border: 0;
      background: transparent;
      color: var(--muted);
      cursor: pointer;
    }

    .fields {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
    }

    .field {
      display: grid;
      gap: 6px;
      min-width: 0;
    }

    .field.wide {
      grid-column: 1 / -1;
    }

    .label {
      color: var(--muted);
      font-size: 11px;
      letter-spacing: 1.6px;
      text-transform: uppercase;
    }

    .input,
    .select,
    .textarea {
      width: 100%;
      border: 1px solid var(--line);
      background: #fff;
      color: var(--ink);
      padding: 10px 11px;
      outline: none;
    }

    .textarea {
      min-height: 72px;
      line-height: 1.55;
      resize: vertical;
    }

    .picker {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 8px;
    }

    .picker select {
      min-height: 44px;
    }

    .connected {
      display: grid;
      gap: 6px;
      margin-top: 10px;
    }

    .product-row {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 10px;
      align-items: center;
      padding: 10px;
      border: 1px solid var(--line);
      background: var(--soft);
    }

    .product-row strong {
      display: block;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      font-size: 14px;
    }

    .product-row span {
      display: block;
      margin-top: 4px;
      color: var(--muted);
      font-size: 12px;
    }

    .mini {
      min-height: 34px;
      padding: 0 10px;
      border: 1px solid var(--line);
      background: #fff;
      color: var(--muted);
      cursor: pointer;
    }

    .empty {
      padding: 14px;
      border: 1px dashed var(--line);
      color: var(--muted);
      font-size: 13px;
      line-height: 1.6;
    }

    .hint {
      margin: 8px 0 0;
      color: var(--muted);
      font-size: 12px;
      line-height: 1.55;
    }

    @media (max-width: 1080px) {
      .top,
      .tabs,
      .fields {
        grid-template-columns: 1fr;
      }

      .actions {
        justify-content: flex-start;
      }
    }

    @media (max-width: 720px) {
      .page {
        width: min(100% - 28px, 1480px);
      }

      .group-head,
      .card-head,
      .picker {
        display: block;
      }

      .picker .button {
        width: 100%;
        margin-top: 8px;
      }
    }
  </style>
</head>
<body>
  <main class="page">
    <header class="top">
      <div>
        <p class="eyebrow">UNO Travel Renewal Admin</p>
        <h1>Product<br>Navigation</h1>
        <p class="copy">
          국가 탭과 노출 상품만 관리합니다. 상품의 productId, 기존 상품 ID, 옵션 ID는 Product Mapping에서 먼저 정리합니다.
        </p>
        <p class="status" data-status>불러오는 중입니다.</p>
      </div>
      <nav class="actions" aria-label="Admin shortcuts">
        <a class="button secondary" href="/admin/renewal/index.php">관리자 허브</a>
        <a class="button secondary" href="/admin/renewal/product-mapping.php">상품 매핑</a>
        <button class="button" type="button" data-save>저장</button>
      </nav>
    </header>

    <section id="navigation-editor" aria-label="Product navigation editor"></section>
  </main>

  <script id="navigation-data" type="application/json"><?php echo json_encode($navigation, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
  <script>
    const root = document.querySelector("#navigation-editor");
    const statusEl = document.querySelector("[data-status]");
    const saveButton = document.querySelector("[data-save]");
    const initialNavigation = JSON.parse(document.querySelector("#navigation-data").textContent || '{"groups":[]}');

    const state = {
      groups: Array.isArray(initialNavigation.groups) ? initialNavigation.groups : [],
      products: [],
    };

    const setStatus = (message, tone = "") => {
      statusEl.textContent = message;
      statusEl.className = "status" + (tone ? " " + tone : "");
    };

    const getCookie = (name) => {
      const row = document.cookie.split("; ").find((item) => item.startsWith(name + "="));
      return row ? decodeURIComponent(row.split("=").slice(1).join("=")) : "";
    };

    const escapeHtml = (value) => String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");

    const lines = (value) => String(value || "")
      .split("\n")
      .map((line) => line.trim())
      .filter(Boolean);

    const productHref = (product) => {
      if (product.href) return product.href;
      if (product.productType === "semi") return "/product/detail/" + product.productId;
      return "/product/detail/daily/" + product.productId;
    };

    const mappedProductsForType = (type) => state.products.filter((product) => {
      return product.isMapped && product.productId && product.productType === type;
    });

    const optionHtml = (type) => {
      const products = mappedProductsForType(type);

      if (!products.length) {
        return '<option value="">먼저 Product Mapping에서 상품을 연결하세요</option>';
      }

      return [
        '<option value="">매핑 상품 선택</option>',
        ...products.map((product) => (
          '<option value="' + escapeHtml(product.productId) + '">' +
          '[' + escapeHtml(product.legacyProductId) + '] ' + escapeHtml(product.title) +
          '</option>'
        )),
      ].join("");
    };

    const findMappedProduct = (productId, legacyProductId) => {
      return state.products.find((product) => {
        if (productId && product.productId === productId) return true;
        return legacyProductId && String(product.legacyProductId) === String(legacyProductId);
      }) || null;
    };

    const normalizeConnectedProduct = (product) => {
      const mapped = findMappedProduct(product.productId, product.legacyProductId);

      if (!mapped) {
        return {
          title: product.title || "연결 상품",
          href: product.href || "",
          productId: product.productId || "",
          legacyProductId: product.legacyProductId || "",
        };
      }

      return {
        title: mapped.title,
        href: productHref(mapped),
        productId: mapped.productId,
        legacyProductId: mapped.legacyProductId,
      };
    };

    const renderProducts = (products, groupIndex, itemIndex) => {
      const normalized = Array.isArray(products) ? products.map(normalizeConnectedProduct) : [];

      if (!normalized.length) {
        return '<div class="empty">이 탭에 노출할 상품이 없습니다. Product Mapping에서 연결된 상품을 선택해 추가하세요.</div>';
      }

      return '<div class="connected">' + normalized.map((product, productIndex) => (
        '<div class="product-row">' +
          '<span>' +
            '<strong>' + escapeHtml(product.title) + '</strong>' +
            '<span>' + escapeHtml(product.productId || "-") + ' · legacy #' + escapeHtml(product.legacyProductId || "-") + '</span>' +
          '</span>' +
          '<button class="mini" type="button" data-remove-product="' + groupIndex + ':' + itemIndex + ':' + productIndex + '">삭제</button>' +
        '</div>'
      )).join("") + '</div>';
    };

    const render = () => {
      root.innerHTML = state.groups.map((group, groupIndex) => {
        const groupType = group.id === "daily" ? "daily" : "semi";
        return (
          '<section class="group" data-group-index="' + groupIndex + '">' +
            '<div class="group-head">' +
              '<h2 class="group-title">' + escapeHtml(group.title || group.id) + '</h2>' +
              '<button class="button secondary" type="button" data-add-item="' + groupIndex + '">탭 추가</button>' +
            '</div>' +
            '<div class="tabs">' +
              (Array.isArray(group.items) ? group.items : []).map((item, itemIndex) => {
                const type = item.category === "daily" ? "daily" : groupType;
                return (
                  '<article class="card" data-item-index="' + itemIndex + '">' +
                    '<div class="card-head">' +
                      '<span class="card-title"><strong>' + escapeHtml(item.country || "NEW") + '</strong><span>' + escapeHtml(item.countryKo || "") + '</span></span>' +
                      '<button class="ghost" type="button" data-remove-item="' + groupIndex + ':' + itemIndex + '">삭제</button>' +
                    '</div>' +
                    '<div class="fields">' +
                      field("ID", "id", item.id || "") +
                      selectField("TYPE", "category", type) +
                      field("COUNTRY", "country", item.country || "") +
                      field("KOREAN LABEL", "countryKo", item.countryKo || "") +
                      field("TITLE", "title", item.title || "", true) +
                      field("SUBTITLE", "subtitle", item.subtitle || "", true) +
                      field("HREF", "href", item.href || "", true) +
                      textareaField("REGIONS", "regions", Array.isArray(item.regions) ? item.regions.join("\\n") : "", true) +
                      '<div class="field wide">' +
                        '<span class="label">VISIBLE PRODUCTS</span>' +
                        '<div class="picker">' +
                          '<select class="select" data-product-picker>' + optionHtml(type) + '</select>' +
                          '<button class="button" type="button" data-add-product="' + groupIndex + ':' + itemIndex + '">상품 추가</button>' +
                        '</div>' +
                        renderProducts(item.products, groupIndex, itemIndex) +
                        '<p class="hint">상품명과 상세 주소는 Product Mapping의 연결값을 기준으로 정리됩니다. 상품 준비중 상태는 선택 목록에서 제외됩니다.</p>' +
                      '</div>' +
                    '</div>' +
                  '</article>'
                );
              }).join("") +
            '</div>' +
          '</section>'
        );
      }).join("");
    };

    const field = (label, key, value, wide = false) => (
      '<label class="field' + (wide ? ' wide' : '') + '">' +
        '<span class="label">' + label + '</span>' +
        '<input class="input" data-field="' + key + '" value="' + escapeHtml(value) + '">' +
      '</label>'
    );

    const textareaField = (label, key, value, wide = false) => (
      '<label class="field' + (wide ? ' wide' : '') + '">' +
        '<span class="label">' + label + '</span>' +
        '<textarea class="textarea" data-field="' + key + '">' + escapeHtml(value) + '</textarea>' +
      '</label>'
    );

    const selectField = (label, key, value) => (
      '<label class="field">' +
        '<span class="label">' + label + '</span>' +
        '<select class="select" data-field="' + key + '">' +
          '<option value="semi"' + (value === "semi" ? " selected" : "") + '>SEMI</option>' +
          '<option value="daily"' + (value === "daily" ? " selected" : "") + '>DAILY</option>' +
        '</select>' +
      '</label>'
    );

    const collect = () => {
      document.querySelectorAll("[data-group-index]").forEach((groupEl) => {
        const groupIndex = Number(groupEl.dataset.groupIndex);
        const group = state.groups[groupIndex];
        const items = [];

        groupEl.querySelectorAll("[data-item-index]").forEach((itemEl) => {
          const previous = group.items[Number(itemEl.dataset.itemIndex)] || {};
          const item = {
            meta: Array.isArray(previous.meta) ? previous.meta : [],
            products: Array.isArray(previous.products) ? previous.products.map(normalizeConnectedProduct) : [],
          };

          itemEl.querySelectorAll("[data-field]").forEach((fieldEl) => {
            const key = fieldEl.dataset.field;
            const value = fieldEl.value.trim();

            if (key === "regions") {
              item[key] = lines(value);
              return;
            }

            item[key] = value;
          });

          items.push(item);
        });

        group.items = items;
      });
    };

    const createEmptyItem = (groupId) => {
      const type = groupId === "daily" ? "daily" : "semi";
      return {
        id: type + "-new-" + Date.now(),
        category: type,
        country: "NEW",
        countryKo: "새 탭",
        title: type === "daily" ? "DAILY TOUR · NEW" : "SEMI PACKAGE · NEW",
        subtitle: "",
        meta: ["EST.2011"],
        regions: [],
        href: type === "daily" ? "/product/daily/new?view=gallery" : "/product/semi/new?view=gallery",
        products: [],
      };
    };

    const loadProducts = async () => {
      try {
        const response = await fetch("/api/admin/products.php?limit=1000", {
          credentials: "same-origin",
          headers: { "Accept": "application/json" },
        });
        const json = await response.json();

        if (!json.ok) throw new Error(json.error ? json.error.message : "상품 목록을 불러오지 못했습니다.");

        state.products = Array.isArray(json.data.items) ? json.data.items : [];
        const mappedCount = state.products.filter((product) => product.isMapped).length;
        setStatus("프런트 노출 상태의 매핑 상품 " + mappedCount + "개를 불러왔습니다. 탭별 노출 상품만 선택해 저장하세요.", "ok");
        render();
      } catch (error) {
        setStatus(error.message || "상품 목록을 불러오지 못했습니다.", "warn");
      }
    };

    root.addEventListener("click", (event) => {
      const addItem = event.target.closest("[data-add-item]");
      const removeItem = event.target.closest("[data-remove-item]");
      const addProduct = event.target.closest("[data-add-product]");
      const removeProduct = event.target.closest("[data-remove-product]");

      if (addItem) {
        collect();
        const groupIndex = Number(addItem.dataset.addItem);
        const group = state.groups[groupIndex];
        group.items = Array.isArray(group.items) ? group.items : [];
        group.items.push(createEmptyItem(group.id));
        render();
      }

      if (removeItem) {
        collect();
        const parts = removeItem.dataset.removeItem.split(":").map(Number);
        state.groups[parts[0]].items.splice(parts[1], 1);
        render();
      }

      if (addProduct) {
        collect();
        const parts = addProduct.dataset.addProduct.split(":").map(Number);
        const card = addProduct.closest("[data-item-index]");
        const picker = card.querySelector("[data-product-picker]");
        const selected = state.products.find((product) => product.productId === picker.value);

        if (!selected) {
          setStatus("추가할 매핑 상품을 선택하세요.", "warn");
          return;
        }

        const item = state.groups[parts[0]].items[parts[1]];
        item.products = Array.isArray(item.products) ? item.products : [];

        if (!item.products.some((product) => product.productId === selected.productId)) {
          item.products.push(normalizeConnectedProduct({
            productId: selected.productId,
            legacyProductId: selected.legacyProductId,
          }));
        }

        render();
      }

      if (removeProduct) {
        collect();
        const parts = removeProduct.dataset.removeProduct.split(":").map(Number);
        const item = state.groups[parts[0]].items[parts[1]];
        item.products = Array.isArray(item.products) ? item.products : [];
        item.products.splice(parts[2], 1);
        render();
      }
    });

    root.addEventListener("change", (event) => {
      if (event.target.matches('[data-field="category"]')) {
        collect();
        render();
      }
    });

    saveButton.addEventListener("click", async () => {
      collect();
      saveButton.disabled = true;
      setStatus("저장 중입니다.");

      try {
        const response = await fetch("/api/admin/product-navigation.php", {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "Accept": "application/json",
            "Content-Type": "application/json",
            "X-CSRF-Token": getCookie("unotravel_csrf_token"),
          },
          body: JSON.stringify({ groups: state.groups }),
        });
        const json = await response.json();

        if (!json.ok) throw new Error(json.error ? json.error.message : "저장하지 못했습니다.");

        setStatus("저장되었습니다. 프런트 상품 네비게이션에 반영됩니다.", "ok");
      } catch (error) {
        setStatus(error.message || "저장하지 못했습니다.", "warn");
      } finally {
        saveButton.disabled = false;
      }
    });

    render();
    loadProducts();
  </script>
</body>
</html>
