<?php
/*
 * product-mapping.php
 * Renewal admin page for connecting legacy UNO Travel products to React product IDs.
 * It manages product type, slug/productId, legacy product ID, optional fee option ID, and active state through the admin API.
 * This page focuses only on product identity mapping, separate from navigation grouping and product detail editing.
 */

require_once __DIR__ . '/_guard.php';

uno_renewal_admin_require_access('/admin/renewal/product-mapping.php');

?><!doctype html>
<html lang="ko">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>UNO Renewal Product Mapping</title>
  <style>
    :root {
      --ink: #111;
      --muted: #737373;
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

    .tools {
      display: grid;
      grid-template-columns: 220px 220px minmax(0, 1fr);
      gap: 10px;
      margin: 28px 0 14px;
    }

    .tools input,
    .tools select,
    .row input,
    .row select {
      width: 100%;
      min-height: 42px;
      padding: 0 12px;
      border: 1px solid var(--line);
      background: #fff;
      color: var(--ink);
    }

    .panel {
      border: 1px solid var(--line);
      background: var(--panel);
    }

    .head,
    .row {
      display: grid;
      grid-template-columns: 1.15fr 1fr 160px 120px 130px 88px;
      gap: 10px;
      align-items: center;
      padding: 12px;
      border-bottom: 1px solid var(--line);
    }

    .head {
      color: var(--muted);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 1.4px;
      text-transform: uppercase;
      background: #fafafa;
    }

    .row:last-child {
      border-bottom: 0;
    }

    .legacy-title {
      display: grid;
      gap: 4px;
      min-width: 0;
    }

    .legacy-title strong {
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .legacy-title span {
      color: var(--muted);
      font-size: 12px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .switch {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: var(--muted);
      font-size: 13px;
    }

    .switch input {
      width: auto;
      min-height: 0;
    }

    .remove {
      min-height: 38px;
      border: 1px solid var(--line);
      background: #fff;
      cursor: pointer;
    }

    .empty {
      padding: 48px 20px;
      color: var(--muted);
      text-align: center;
    }

    .add-panel {
      display: grid;
      grid-template-columns: minmax(0, 1fr) 160px;
      gap: 10px;
      margin-top: 14px;
    }

    .add-panel select {
      width: 100%;
      min-height: 44px;
      border: 1px solid var(--line);
      background: #fff;
      padding: 0 12px;
    }

    @media (max-width: 1120px) {
      .top,
      .tools,
      .add-panel,
      .head,
      .row {
        grid-template-columns: 1fr;
      }

      .head {
        display: none;
      }
    }
  </style>
</head>
<body>
  <main class="page">
    <header class="top">
      <div>
        <p class="eyebrow">UNO Travel Renewal Admin</p>
        <h1>Product<br>Mapping</h1>
        <p class="copy">
          기존 우노트래블 상품을 리뉴얼 프런트의 productId와 연결합니다.
          상태를 프런트 노출로 두면 상품 목록, 상세페이지, 예약 API에 반영되고,
          상품 준비중으로 바꾸면 리스트와 네비게이션에서 자동으로 제외됩니다.
        </p>
        <p class="status" data-status>불러오는 중입니다.</p>
      </div>
      <nav class="actions" aria-label="Admin shortcuts">
        <a class="button secondary" href="/admin/renewal/index.php">관리자 허브</a>
        <a class="button secondary" href="/admin/renewal/product-navigation.php">상품 네비게이션</a>
        <button class="button" type="button" data-save>저장</button>
      </nav>
    </header>

    <section class="tools" aria-label="Mapping filters">
      <select data-filter-type>
        <option value="">전체 상품</option>
        <option value="semi">세미패키지</option>
        <option value="daily">데일리투어</option>
      </select>
      <select data-filter-status>
        <option value="">전체 상태</option>
        <option value="active">프런트 노출</option>
        <option value="inactive">상품 준비중</option>
      </select>
      <input type="search" data-search placeholder="상품명, productId, 기존 상품 ID 검색">
    </section>

    <section class="panel" aria-label="Product mapping editor">
      <div class="head">
        <span>Legacy Product</span>
        <span>Renewal Product ID</span>
        <span>Type</span>
        <span>Fee Option</span>
        <span>Frontend Status</span>
        <span></span>
      </div>
      <div data-list></div>
    </section>

    <section class="add-panel" aria-label="Add legacy product">
      <select data-add-product></select>
      <button class="button" type="button" data-add>매핑 추가</button>
    </section>
  </main>

  <script>
    const state = {
      products: [],
      mappings: [],
    };

    const listEl = document.querySelector("[data-list]");
    const statusEl = document.querySelector("[data-status]");
    const saveButton = document.querySelector("[data-save]");
    const addButton = document.querySelector("[data-add]");
    const addProductEl = document.querySelector("[data-add-product]");
    const searchEl = document.querySelector("[data-search]");
    const filterTypeEl = document.querySelector("[data-filter-type]");
    const filterStatusEl = document.querySelector("[data-filter-status]");

    const setStatus = (message, tone = "") => {
      statusEl.textContent = message;
      statusEl.className = "status" + (tone ? " " + tone : "");
    };

    const getCookie = (name) => {
      const row = document.cookie.split("; ").find((item) => item.startsWith(name + "="));
      return row ? decodeURIComponent(row.split("=").slice(1).join("=")) : "";
    };

    const slugify = (value) => String(value || "")
      .toLowerCase()
      .replace(/[^a-z0-9가-힣]+/g, "-")
      .replace(/^-+|-+$/g, "");

    const fallbackProductId = (product) => {
      const prefix = product.productType === "semi" ? "semi" : "daily";
      return product.productId || prefix + "-" + product.legacyProductId;
    };

    const mappingProduct = (mapping) => {
      return state.products.find((product) => String(product.legacyProductId) === String(mapping.legacyProductId)) || null;
    };

    const filteredMappings = () => {
      const type = filterTypeEl.value;
      const status = filterStatusEl.value;
      const keyword = searchEl.value.trim().toLowerCase();

      return state.mappings.filter((mapping) => {
        const product = mappingProduct(mapping);
        const haystack = [
          mapping.productId,
          mapping.legacyProductId,
          product ? product.title : "",
          product ? product.category : "",
        ].join(" ").toLowerCase();

        if (type && mapping.productType !== type) return false;
        if (status === "active" && !mapping.isActive) return false;
        if (status === "inactive" && mapping.isActive) return false;
        if (keyword && !haystack.includes(keyword)) return false;
        return true;
      });
    };

    const renderAddOptions = () => {
      const mappedIds = new Set(state.mappings.map((mapping) => String(mapping.legacyProductId)));
      const available = state.products.filter((product) => !mappedIds.has(String(product.legacyProductId)));
      addProductEl.innerHTML = "";

      if (!available.length) {
        const option = document.createElement("option");
        option.value = "";
        option.textContent = "추가 가능한 기존 상품이 없습니다";
        addProductEl.appendChild(option);
        return;
      }

      available.forEach((product) => {
        const option = document.createElement("option");
        option.value = String(product.legacyProductId);
        option.textContent = "[" + product.legacyProductId + "] " + product.title;
        addProductEl.appendChild(option);
      });
    };

    const render = () => {
      const rows = filteredMappings();
      listEl.innerHTML = "";

      if (!rows.length) {
        const empty = document.createElement("div");
        empty.className = "empty";
        empty.textContent = "표시할 매핑이 없습니다.";
        listEl.appendChild(empty);
        renderAddOptions();
        return;
      }

      rows.forEach((mapping) => {
        const index = state.mappings.indexOf(mapping);
        const product = mappingProduct(mapping);
        const row = document.createElement("div");
        row.className = "row";

        const legacy = document.createElement("div");
        legacy.className = "legacy-title";
        const title = document.createElement("strong");
        title.textContent = product ? product.title : "기존 상품 #" + mapping.legacyProductId;
        const meta = document.createElement("span");
        meta.textContent = "Legacy ID " + mapping.legacyProductId + (product && product.category ? " · " + product.category : "");
        legacy.appendChild(title);
        legacy.appendChild(meta);

        const productId = document.createElement("input");
        productId.value = mapping.productId || "";
        productId.placeholder = "renewal-product-id";
        productId.addEventListener("input", () => {
          state.mappings[index].productId = productId.value;
        });

        const type = document.createElement("select");
        [["semi", "세미패키지"], ["daily", "데일리투어"]].forEach((item) => {
          const option = document.createElement("option");
          option.value = item[0];
          option.textContent = item[1];
          option.selected = mapping.productType === item[0];
          type.appendChild(option);
        });
        type.addEventListener("change", () => {
          state.mappings[index].productType = type.value;
          render();
        });

        const fee = document.createElement("input");
        fee.type = "number";
        fee.min = "0";
        fee.value = mapping.legacyFeeOptionId || "";
        fee.placeholder = "선택";
        fee.addEventListener("input", () => {
          state.mappings[index].legacyFeeOptionId = fee.value ? Number(fee.value) : null;
        });

        const activeLabel = document.createElement("label");
        activeLabel.className = "switch";
        const active = document.createElement("input");
        active.type = "checkbox";
        active.checked = mapping.isActive !== false;
        const statusText = document.createElement("span");
        const syncStatusText = () => {
          statusText.textContent = active.checked ? "프런트 노출" : "상품 준비중";
        };
        active.addEventListener("change", () => {
          state.mappings[index].isActive = active.checked;
          syncStatusText();
        });
        syncStatusText();
        activeLabel.appendChild(active);
        activeLabel.appendChild(statusText);

        const remove = document.createElement("button");
        remove.className = "remove";
        remove.type = "button";
        remove.textContent = "삭제";
        remove.addEventListener("click", () => {
          state.mappings.splice(index, 1);
          render();
        });

        row.appendChild(legacy);
        row.appendChild(productId);
        row.appendChild(type);
        row.appendChild(fee);
        row.appendChild(activeLabel);
        row.appendChild(remove);
        listEl.appendChild(row);
      });

      renderAddOptions();
    };

    const load = async () => {
      try {
        const productsResponse = await fetch("/api/admin/products.php?limit=1000", { credentials: "same-origin" });
        const mappingResponse = await fetch("/api/admin/product-mapping.php", { credentials: "same-origin" });
        const productsJson = await productsResponse.json();
        const mappingJson = await mappingResponse.json();

        if (!productsJson.ok) throw new Error(productsJson.error ? productsJson.error.message : "상품 목록을 불러오지 못했습니다.");
        if (!mappingJson.ok) throw new Error(mappingJson.error ? mappingJson.error.message : "매핑 정보를 불러오지 못했습니다.");

        state.products = Array.isArray(productsJson.data.items) ? productsJson.data.items : [];
        state.mappings = Array.isArray(mappingJson.data.mappings) ? mappingJson.data.mappings : [];
        setStatus(mappingJson.data.hasSavedMapping ? "저장된 매핑을 불러왔습니다." : "기본 매핑을 불러왔습니다. 저장하면 DB 매핑으로 전환됩니다.", "ok");
        render();
      } catch (error) {
        setStatus(error.message || "불러오기 중 문제가 발생했습니다.", "warn");
      }
    };

    saveButton.addEventListener("click", async () => {
      try {
        saveButton.disabled = true;
        const csrf = getCookie("unotravel_csrf_token");
        const payload = {
          mappings: state.mappings.map((mapping, index) => ({
            productId: mapping.productId,
            legacyProductId: Number(mapping.legacyProductId),
            legacyFeeOptionId: mapping.legacyFeeOptionId ? Number(mapping.legacyFeeOptionId) : null,
            productType: mapping.productType === "semi" ? "semi" : "daily",
            confidence: mapping.confidence || "admin",
            isActive: mapping.isActive !== false,
            sortOrder: index,
          })),
        };

        const response = await fetch("/api/admin/product-mapping.php", {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-Token": csrf,
          },
          body: JSON.stringify(payload),
        });
        const json = await response.json();

        if (!json.ok) throw new Error(json.error ? json.error.message : "저장하지 못했습니다.");

        state.mappings = Array.isArray(json.data.mappings) ? json.data.mappings : state.mappings;
        setStatus("저장되었습니다. 프런트 노출 상품만 목록과 네비게이션에 반영됩니다.", "ok");
        render();
      } catch (error) {
        setStatus(error.message || "저장 중 문제가 발생했습니다.", "warn");
      } finally {
        saveButton.disabled = false;
      }
    });

    addButton.addEventListener("click", () => {
      const product = state.products.find((item) => String(item.legacyProductId) === String(addProductEl.value));

      if (!product) return;

      state.mappings.push({
        productId: fallbackProductId(product),
        legacyProductId: Number(product.legacyProductId),
        legacyFeeOptionId: product.legacyFeeOptionId || null,
        productType: product.productType === "semi" ? "semi" : "daily",
        confidence: "admin",
        isActive: true,
        sortOrder: state.mappings.length,
      });
      render();
    });

    [searchEl, filterTypeEl, filterStatusEl].forEach((input) => {
      input.addEventListener("input", render);
      input.addEventListener("change", render);
    });

    load();
  </script>
</body>
</html>
