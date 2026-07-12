<?php
/*
 * products.php
 * Renewal admin product operations page for legacy UNO Travel products.
 * It lists real Gnuboard products with thumbnails, frontend exposure status, mapping state, and renewal edit links.
 * This page is the simple operator-facing hub, while product-mapping.php remains the advanced ID mapping editor.
 */

require_once __DIR__ . '/_guard.php';

uno_renewal_admin_require_access('/admin/renewal/products.php');

?><!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>UNO Renewal Products</title>
  <style>
    :root {
      --ink: #111;
      --muted: #707070;
      --line: #e4e4e0;
      --soft: #f5f5f3;
      --panel: #fff;
      --good: #0f766e;
      --wait: #9a6a00;
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
    select {
      font: inherit;
    }

    .page {
      width: min(1480px, calc(100% - 48px));
      margin: 0 auto;
      padding: 32px 0 72px;
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
      max-width: 780px;
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
      color: var(--good);
    }

    .status.warn {
      color: var(--wait);
    }

    .tools {
      display: grid;
      grid-template-columns: 220px minmax(0, 1fr);
      gap: 10px;
      margin: 28px 0 14px;
    }

    .tools input,
    .tools select {
      width: 100%;
      min-height: 44px;
      padding: 0 12px;
      border: 1px solid var(--line);
      background: #fff;
      color: var(--ink);
    }

    .summary {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 10px;
      margin-bottom: 14px;
    }

    .summary-card {
      min-height: 86px;
      padding: 16px;
      border: 1px solid var(--line);
      background: var(--panel);
      display: grid;
      align-content: space-between;
    }

    .summary-card span {
      color: var(--muted);
      font-size: 12px;
      letter-spacing: 1.4px;
      text-transform: uppercase;
    }

    .summary-card strong {
      font-size: 28px;
      line-height: 1;
    }

    .product-board {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 14px;
      align-items: start;
    }

    .product-column {
      display: grid;
      gap: 10px;
      min-width: 0;
    }

    .column-head {
      min-height: 74px;
      padding: 16px;
      border: 1px solid var(--line);
      background: var(--panel);
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 18px;
    }

    .column-head h2 {
      margin: 0;
      font-size: 26px;
      line-height: 1;
      letter-spacing: -.02em;
    }

    .column-head span {
      color: var(--muted);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 1.4px;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .list {
      display: grid;
      gap: 8px;
    }

    .row {
      display: grid;
      grid-template-columns: 74px minmax(0, 1fr);
      gap: 12px;
      align-items: start;
      min-height: 150px;
      padding: 12px;
      border: 1px solid var(--line);
      background: var(--panel);
    }

    .thumb {
      width: 72px;
      height: 88px;
      border: 1px solid var(--line);
      background: #f0f0ed;
      object-fit: cover;
    }

    .no-thumb {
      width: 72px;
      height: 88px;
      border: 1px solid var(--line);
      background: #f0f0ed;
      color: #aaa;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: 800;
    }

    .title {
      display: grid;
      gap: 7px;
      min-width: 0;
    }

    .title strong {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      font-size: 18px;
      line-height: 1.35;
    }

    .meta {
      color: var(--muted);
      font-size: 13px;
      line-height: 1.45;
    }

    .row-meta {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      align-items: center;
      margin-top: 6px;
    }

    .badge {
      width: max-content;
      min-height: 28px;
      display: inline-flex;
      align-items: center;
      padding: 0 10px;
      border: 1px solid var(--line);
      font-size: 12px;
      font-weight: 800;
    }

    .badge.on {
      border-color: rgba(15, 118, 110, .28);
      color: var(--good);
      background: rgba(15, 118, 110, .06);
    }

    .badge.wait {
      border-color: rgba(154, 106, 0, .28);
      color: var(--wait);
      background: rgba(154, 106, 0, .07);
    }

    .status-select {
      min-height: 36px;
      padding: 0 34px 0 12px;
      border: 1px solid var(--line);
      background: #fff;
      color: var(--ink);
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
    }

    .status-select.is-active {
      border-color: rgba(15, 118, 110, .28);
      color: var(--good);
      background-color: rgba(15, 118, 110, .06);
    }

    .status-select.is-ready {
      border-color: rgba(154, 106, 0, .28);
      color: var(--wait);
      background-color: rgba(154, 106, 0, .07);
    }

    .status-select.is-unmapped {
      color: var(--muted);
      background-color: #fff;
    }

    .row-actions {
      display: flex;
      justify-content: flex-start;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 12px;
    }

    .mini {
      min-height: 38px;
      padding: 0 12px;
      border: 1px solid var(--line);
      background: #fff;
      color: var(--ink);
      cursor: pointer;
      font-size: 13px;
      font-weight: 800;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      line-height: 1;
      text-align: center;
      text-decoration: none;
    }

    .mini.primary {
      border-color: var(--ink);
      background: var(--ink);
      color: #fff;
    }

    .empty {
      padding: 64px 20px;
      border: 1px solid var(--line);
      background: var(--panel);
      color: var(--muted);
      text-align: center;
    }

    @media (max-width: 1120px) {
      .top,
      .tools,
      .summary,
      .product-board,
      .row {
        grid-template-columns: 1fr;
      }

      .row-actions {
        justify-content: flex-start;
      }
    }
  </style>
</head>
<body>
  <main class="page">
    <header class="top">
      <div>
        <p class="eyebrow">UNO Travel Renewal Admin</p>
        <h1>Product<br>Operations</h1>
        <p class="copy">
          기존 우노트래블 상품을 기준으로 프런트 노출 상태를 관리합니다.
          상품명, 상세 설명, 썸네일 같은 원본 수정은 기존 상품 수정 화면에서 진행하고,
          리뉴얼 프런트 노출 여부는 이 화면에서 빠르게 조정합니다.
        </p>
        <p class="status" data-status>불러오는 중입니다.</p>
      </div>
      <nav class="actions" aria-label="Admin shortcuts">
        <a class="button secondary" href="/admin/renewal/index.php">관리자 메인</a>
        <a class="button secondary" href="/admin/renewal/product-navigation.php">상품 네비게이션</a>
        <a class="button secondary" href="/admin/write.php?bo_table=product">상품 추가</a>
      </nav>
    </header>

    <section class="tools" aria-label="Product filters">
      <select data-filter-status>
        <option value="">전체 상태</option>
        <option value="active">프런트 노출</option>
        <option value="ready">상품 준비중</option>
        <option value="unmapped">미연결</option>
      </select>
      <input type="search" data-search placeholder="상품명, 카테고리, legacy ID, productId 검색">
    </section>

    <section class="summary" aria-label="Product summary">
      <div class="summary-card"><span>All</span><strong data-count-all>0</strong></div>
      <div class="summary-card"><span>Frontend</span><strong data-count-active>0</strong></div>
      <div class="summary-card"><span>Preparing</span><strong data-count-ready>0</strong></div>
      <div class="summary-card"><span>Unmapped</span><strong data-count-unmapped>0</strong></div>
    </section>

    <section class="product-board" data-board aria-label="Product list">
      <article class="product-column">
        <div class="column-head">
          <h2>SEMI PACKAGE</h2>
          <span><strong data-count-semi>0</strong> items</span>
        </div>
        <div class="list" data-list-semi></div>
      </article>

      <article class="product-column">
        <div class="column-head">
          <h2>DAILY TOUR</h2>
          <span><strong data-count-daily>0</strong> items</span>
        </div>
        <div class="list" data-list-daily></div>
      </article>
    </section>
  </main>

  <script>
    const state = {
      products: [],
      mappings: [],
      saving: false,
    };

    const boardEl = document.querySelector("[data-board]");
    const semiListEl = document.querySelector("[data-list-semi]");
    const dailyListEl = document.querySelector("[data-list-daily]");
    const statusEl = document.querySelector("[data-status]");
    const filterStatusEl = document.querySelector("[data-filter-status]");
    const searchEl = document.querySelector("[data-search]");
    const countAllEl = document.querySelector("[data-count-all]");
    const countActiveEl = document.querySelector("[data-count-active]");
    const countReadyEl = document.querySelector("[data-count-ready]");
    const countUnmappedEl = document.querySelector("[data-count-unmapped]");
    const countSemiEl = document.querySelector("[data-count-semi]");
    const countDailyEl = document.querySelector("[data-count-daily]");

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

    const fallbackProductId = (product) => {
      const prefix = product.productType === "semi" ? "semi" : "daily";
      return product.productId || prefix + "-" + product.legacyProductId;
    };

    const mappingForProduct = (product) => {
      return state.mappings.find((mapping) => String(mapping.legacyProductId) === String(product.legacyProductId)) || null;
    };

    const normalizedProducts = () => state.products.map((product) => {
      const mapping = mappingForProduct(product);
      return {
        ...product,
        mapping,
        productId: mapping && mapping.productId ? mapping.productId : product.productId,
        isMapped: Boolean(mapping || product.isMapped),
        isActive: mapping ? mapping.isActive !== false : false,
      };
    });

    const productStatusWeight = (product) => {
      if (product.isActive) return 0;
      if (product.isMapped) return 1;
      return 2;
    };

    const sortProducts = (products) => products.sort((a, b) => {
      const statusDiff = productStatusWeight(a) - productStatusWeight(b);
      if (statusDiff !== 0) return statusDiff;

      return String(a.title || "").localeCompare(String(b.title || ""), "ko");
    });

    const filteredProducts = (type) => {
      const status = filterStatusEl.value;
      const keyword = searchEl.value.trim().toLowerCase();

      return normalizedProducts().filter((product) => {
        const haystack = [
          product.title,
          product.category,
          product.legacyProductId,
          product.productId,
        ].join(" ").toLowerCase();

        if (type && product.productType !== type) return false;
        if (status === "active" && !product.isActive) return false;
        if (status === "ready" && (!product.isMapped || product.isActive)) return false;
        if (status === "unmapped" && product.isMapped) return false;
        if (keyword && !haystack.includes(keyword)) return false;
        return true;
      });
    };

    const syncCounts = () => {
      const products = normalizedProducts();
      countAllEl.textContent = products.length;
      countActiveEl.textContent = products.filter((product) => product.isActive).length;
      countReadyEl.textContent = products.filter((product) => product.isMapped && !product.isActive).length;
      countUnmappedEl.textContent = products.filter((product) => !product.isMapped).length;
      countSemiEl.textContent = products.filter((product) => product.productType === "semi").length;
      countDailyEl.textContent = products.filter((product) => product.productType === "daily").length;
    };

    const statusBadge = (product) => {
      if (product.isActive) {
        return '<span class="badge on">프런트 노출</span>';
      }

      if (product.isMapped) {
        return '<span class="badge wait">상품 준비중</span>';
      }

      return '<span class="badge">미연결</span>';
    };

    const productThumb = (product) => {
      if (product.thumbnailUrl) {
        return '<img class="thumb" src="' + escapeHtml(product.thumbnailUrl) + '" alt="">';
      }

      return '<span class="no-thumb">NO IMAGE</span>';
    };

    const productStatusValue = (product) => {
      if (product.isActive) return "active";
      if (product.isMapped) return "ready";
      return "unmapped";
    };

    const statusSelect = (product) => {
      const value = productStatusValue(product);
      return (
        '<select class="status-select is-' + escapeHtml(value) + '" data-status-select="' + escapeHtml(product.legacyProductId) + '">' +
          '<option value="active"' + (value === "active" ? " selected" : "") + '>프런트 노출</option>' +
          '<option value="ready"' + (value === "ready" ? " selected" : "") + '>상품 준비중</option>' +
          '<option value="unmapped"' + (value === "unmapped" ? " selected" : "") + '>미연결</option>' +
        '</select>'
      );
    };

    const renderProduct = (product) => {
      const frontendHref = product.productId ? product.href : "";
      const editHref = product.renewalEditHref || ("/admin/renewal/product-edit.php?legacyProductId=" + encodeURIComponent(product.legacyProductId || ""));
      return (
        '<article class="row">' +
          '<div>' + productThumb(product) + '</div>' +
          '<div>' +
            '<a class="title" href="' + escapeHtml(editHref) + '">' +
              '<strong>' + escapeHtml(product.title || "상품명 없음") + '</strong>' +
              '<span class="meta">' + escapeHtml(product.category || "-") + ' · legacy #' + escapeHtml(product.legacyProductId || "-") + '</span>' +
              '<span class="meta">productId: ' + escapeHtml(product.productId || "미연결") + '</span>' +
            '</a>' +
            '<div class="row-meta">' + statusSelect(product) + '</div>' +
            '<div class="row-actions">' +
              '<a class="mini primary" href="' + escapeHtml(editHref) + '">수정</a>' +
              (frontendHref ? '<a class="mini" href="' + escapeHtml(frontendHref) + '" target="_blank" rel="noreferrer">보기</a>' : '') +
            '</div>' +
          '</div>' +
        '</article>'
      );
    };

    const renderList = (target, products) => {
      if (!products.length) {
        target.innerHTML = '<div class="empty">조건에 맞는 상품이 없습니다.</div>';
        return;
      }

      target.innerHTML = sortProducts(products).map(renderProduct).join("");
    };

    const render = () => {
      syncCounts();
      renderList(semiListEl, filteredProducts("semi"));
      renderList(dailyListEl, filteredProducts("daily"));
    };

    const load = async () => {
      try {
        const [productsResponse, mappingResponse] = await Promise.all([
          fetch("/api/admin/products.php?limit=1000", { credentials: "same-origin" }),
          fetch("/api/admin/product-mapping.php", { credentials: "same-origin" }),
        ]);
        const productsJson = await productsResponse.json();
        const mappingJson = await mappingResponse.json();

        if (!productsJson.ok) throw new Error(productsJson.error ? productsJson.error.message : "상품 목록을 불러오지 못했습니다.");
        if (!mappingJson.ok) throw new Error(mappingJson.error ? mappingJson.error.message : "상품 노출 정보를 불러오지 못했습니다.");

        state.products = Array.isArray(productsJson.data.items) ? productsJson.data.items : [];
        state.mappings = Array.isArray(mappingJson.data.mappings) ? mappingJson.data.mappings : [];
        setStatus("상품 목록을 불러왔습니다.", "ok");
        render();
      } catch (error) {
        setStatus(error.message || "불러오기 중 문제가 발생했습니다.", "warn");
      }
    };

    const saveMappings = async () => {
      const csrf = getCookie("unotravel_csrf_token");
      const response = await fetch("/api/admin/product-mapping.php", {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-Token": csrf,
        },
        body: JSON.stringify({
          mappings: state.mappings.map((mapping, index) => ({
            productId: mapping.productId,
            legacyProductId: Number(mapping.legacyProductId),
            legacyFeeOptionId: mapping.legacyFeeOptionId ? Number(mapping.legacyFeeOptionId) : null,
            productType: mapping.productType === "semi" ? "semi" : "daily",
            confidence: mapping.confidence || "admin",
            isActive: mapping.isActive !== false,
            sortOrder: index,
          })),
        }),
      });
      const json = await response.json();

      if (!json.ok) throw new Error(json.error ? json.error.message : "저장하지 못했습니다.");

      state.mappings = Array.isArray(json.data.mappings) ? json.data.mappings : state.mappings;
    };

    const updateProductStatus = async (legacyProductId, nextStatus) => {
      if (state.saving) return;

      const product = normalizedProducts().find((item) => String(item.legacyProductId) === String(legacyProductId));
      if (!product) return;

      let mapping = mappingForProduct(product);
      const mappingIndex = state.mappings.findIndex((item) => String(item.legacyProductId) === String(product.legacyProductId));

      if (nextStatus === "unmapped") {
        if (mappingIndex >= 0) {
          state.mappings.splice(mappingIndex, 1);
        }
      } else if (!mapping) {
        mapping = {
          productId: fallbackProductId(product),
          legacyProductId: Number(product.legacyProductId),
          legacyFeeOptionId: product.legacyFeeOptionId || null,
          productType: product.productType === "semi" ? "semi" : "daily",
          confidence: "admin",
          isActive: nextStatus === "active",
          sortOrder: state.mappings.length,
        };
        state.mappings.push(mapping);
      } else {
        mapping.isActive = nextStatus === "active";
      }

      try {
        state.saving = true;
        setStatus("저장 중입니다.");
        await saveMappings();
        setStatus("저장되었습니다. 프런트 노출 상태가 반영됩니다.", "ok");
      } catch (error) {
        setStatus(error.message || "저장 중 문제가 발생했습니다.", "warn");
      } finally {
        state.saving = false;
        render();
      }
    };

    boardEl.addEventListener("change", (event) => {
      const select = event.target.closest("[data-status-select]");
      if (select) {
        updateProductStatus(select.dataset.statusSelect, select.value);
      }
    });

    [filterStatusEl, searchEl].forEach((input) => {
      input.addEventListener("input", render);
      input.addEventListener("change", render);
    });

    load();
  </script>
</body>
</html>
