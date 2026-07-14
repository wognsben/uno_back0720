<?php
/*
 * product-edit.php
 * Renewal admin product hub for editing one legacy UNO Travel product.
 * It separates dense legacy product work into focused modal editors for readability.
 * This page manages renewal-only editing UI; legacy write.php remains linked for fields not rebuilt yet.
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

$legacyProductId = isset($_GET['legacyProductId']) ? (int) $_GET['legacyProductId'] : 0;
uno_renewal_admin_require_access('/admin/renewal/product-edit.php?legacyProductId=' . $legacyProductId);

uno_renewal_admin_render_head('UNO Renewal Product Hub');
uno_renewal_admin_render_header();
uno_renewal_admin_render_pagehead(
    'UNO Travel Renewal Admin',
    'Product<br>Hub',
    '상품 기본 정보와 운영 기능을 한 화면에 몰아넣지 않고, 필요한 편집만 모달로 열어 관리합니다. 세미패키지 일정과 데일리투어 캘린더는 각각의 운영 방식에 맞게 분리합니다.',
    array(
        array('label' => '상품 운영', 'href' => '/admin/renewal/products.php', 'secondary' => true),
        array('label' => '기존 고급 수정', 'href' => '/admin/write.php?w=u&bo_table=product&wr_id=' . $legacyProductId, 'secondary' => true),
    )
);
?>

    <style>
      .uno-product-hero { display: grid; grid-template-columns: 108px minmax(0, 1fr) auto; gap: 18px; align-items: start; }
      .uno-product-thumb, .uno-product-thumb img { width: 108px; height: 136px; }
      .uno-product-thumb { display: grid; place-items: center; border: 1px solid var(--uno-line); background: #ededeb; color: #aaa; font-size: 11px; font-weight: 800; }
      .uno-product-thumb img { object-fit: cover; display: block; }
      .uno-product-summary-bar { display: grid; gap: 12px; min-width: 220px; }
      .uno-product-actions { display: grid; gap: 8px; }
      .uno-product-actions .uno-admin-button { width: 100%; }
      .uno-product-state { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
      .uno-product-state div { border: 1px solid var(--uno-line); background: #fafaf8; padding: 10px; }
      .uno-product-state span { display: block; color: var(--uno-muted); font-size: 10px; font-weight: 900; letter-spacing: 1.8px; text-transform: uppercase; }
      .uno-product-state strong { display: block; margin-top: 5px; font-size: 14px; line-height: 1.25; }
      .uno-hub-section { margin-top: 18px; }
      .uno-hub-section-head { display: flex; justify-content: space-between; gap: 16px; align-items: end; margin: 0 0 10px; }
      .uno-hub-section-head h3 { margin: 0; font-size: 18px; letter-spacing: 1.5px; text-transform: uppercase; }
      .uno-hub-section-head p { margin: 0; color: var(--uno-muted); line-height: 1.5; }
      .uno-hub-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
      .uno-hub-grid.is-operational { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .uno-hub-card { min-height: 154px; }
      .uno-hub-card .uno-admin-button { width: max-content; min-width: 96px; }
      .uno-card-kicker { display: block; margin-bottom: 8px; color: var(--uno-muted); font-size: 10px; font-weight: 900; letter-spacing: 2px; text-transform: uppercase; }
      .uno-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
      .uno-form-field { display: grid; gap: 7px; }
      .uno-form-field.full { grid-column: 1 / -1; }
      .uno-form-field label { color: var(--uno-muted); font-size: 11px; font-weight: 900; letter-spacing: 2px; text-transform: uppercase; }
      .uno-form-field input, .uno-form-field select, .uno-form-field textarea { width: 100%; min-height: 44px; border: 1px solid var(--uno-line); background: #fff; padding: 10px 12px; color: var(--uno-ink); }
      .uno-form-field textarea { min-height: 92px; resize: vertical; line-height: 1.55; }
      .uno-check-row { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; }
      .uno-check-row label { display: inline-flex; gap: 7px; align-items: center; color: var(--uno-ink); font-weight: 800; }
      .uno-guide-picker { display: grid; gap: 10px; }
      .uno-guide-selected { display: flex; flex-wrap: wrap; gap: 6px; min-height: 32px; align-items: center; color: var(--uno-muted); }
      .uno-guide-chip { display: inline-flex; align-items: center; min-height: 28px; padding: 0 10px; border: 1px solid rgba(21, 21, 21, .14); background: #fafaf8; color: var(--uno-ink); font-size: 12px; font-weight: 900; }
      .uno-guide-search { min-height: 40px !important; }
      .uno-guide-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 8px; max-height: 260px; overflow: auto; padding: 10px; border: 1px solid var(--uno-line); background: #fff; }
      .uno-guide-option { min-width: 0; display: flex; align-items: center; gap: 8px; min-height: 36px; padding: 6px 8px; border: 1px solid rgba(21, 21, 21, .08); background: #fafaf8; color: var(--uno-ink); font-size: 13px; font-weight: 900; line-height: 1.25; }
      .uno-guide-option input { width: 16px !important; min-height: 16px !important; flex: 0 0 16px; padding: 0; }
      .uno-guide-option.is-hidden { display: none; }
      .uno-section-note { margin: 0 0 16px; color: var(--uno-muted); line-height: 1.65; word-break: keep-all; }
      .uno-schedule-list { display: grid; gap: 14px; }
      .uno-schedule-card { border: 1px solid var(--uno-line); background: #fff; padding: 16px; }
      .uno-schedule-head { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 14px; }
      .uno-schedule-title { margin: 0; font-size: 18px; }
      .uno-calendar-toolbar { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 16px; }
      .uno-calendar-title { margin: 0; font-size: 24px; }
      .uno-calendar-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); border-top: 1px solid var(--uno-line); border-left: 1px solid var(--uno-line); background: #fff; }
      .uno-calendar-weekday, .uno-calendar-day { border-right: 1px solid var(--uno-line); border-bottom: 1px solid var(--uno-line); }
      .uno-calendar-weekday { min-height: 38px; display: grid; place-items: center; color: var(--uno-muted); font-size: 12px; font-weight: 900; }
      .uno-calendar-day { min-height: 118px; padding: 10px; display: grid; gap: 7px; align-content: start; }
      .uno-calendar-day.is-empty { background: #f7f7f4; color: rgba(21, 21, 21, .26); }
      .uno-calendar-day strong { font-size: 16px; }
      .uno-calendar-day select, .uno-calendar-day input { width: 100%; min-height: 34px; border: 1px solid var(--uno-line); background: #fff; padding: 6px 8px; }
      .uno-calendar-count { min-height: 28px; display: flex; align-items: center; color: var(--uno-muted); font-size: 12px; font-weight: 800; line-height: 1.35; }
      .uno-calendar-status { min-height: 30px; display: inline-flex; width: max-content; align-items: center; padding: 0 9px; border: 1px solid var(--uno-line); font-size: 12px; font-weight: 900; }
      .uno-calendar-status.is-available { color: var(--uno-good); border-color: rgba(15, 118, 110, .25); background: rgba(15, 118, 110, .06); }
      .uno-calendar-status.is-soon { color: var(--uno-warn); border-color: rgba(154, 106, 0, .28); background: rgba(154, 106, 0, .07); }
      .uno-calendar-status.is-soldout { color: var(--uno-danger); border-color: rgba(159, 41, 41, .24); background: rgba(159, 41, 41, .07); }
      .uno-calendar-small-button { min-height: 34px; border: 1px solid var(--uno-ink); background: var(--uno-ink); color: #fff; font-weight: 900; cursor: pointer; }
      .uno-calendar-small-button.secondary { background: #fff; color: var(--uno-ink); border-color: var(--uno-line); }
      .uno-calendar-actions { display: flex; gap: 6px; flex-wrap: wrap; }
      .uno-pattern-panel { margin-top: 18px; border: 1px solid var(--uno-line); background: #fff; padding: 16px; }
      .uno-price-list { display: grid; gap: 14px; }
      .uno-price-row { border: 1px solid var(--uno-line); background: #fff; padding: 16px; }
      .uno-media-preview { display: grid; grid-template-columns: 180px minmax(0, 1fr); gap: 18px; align-items: start; }
      .uno-media-preview img { width: 180px; height: 226px; object-fit: cover; border: 1px solid var(--uno-line); background: #ededeb; }
      .uno-media-empty { width: 180px; height: 226px; display: grid; place-items: center; border: 1px solid var(--uno-line); background: #ededeb; color: #aaa; font-weight: 900; }
      .uno-media-audit { display: grid; gap: 16px; margin-top: 18px; }
      .uno-media-group { border: 1px solid var(--uno-line); background: #fff; padding: 16px; }
      .uno-media-group-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 12px; }
      .uno-media-group-head h4 { margin: 0; font-size: 17px; letter-spacing: .2px; }
      .uno-media-group-head p { margin: 5px 0 0; color: var(--uno-muted); line-height: 1.55; }
      .uno-media-tools { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
      .uno-media-edit-link { min-height: 30px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--uno-line); padding: 0 10px; color: var(--uno-ink); text-decoration: none; font-size: 12px; font-weight: 900; background: #fafaf8; }
      .uno-media-edit-link:hover { border-color: var(--uno-ink); }
      .uno-media-count { display: inline-flex; align-items: center; min-height: 30px; border: 1px solid var(--uno-line); padding: 0 10px; font-size: 12px; font-weight: 900; white-space: nowrap; }
      .uno-media-count.is-empty { color: var(--uno-danger); border-color: rgba(159, 41, 41, .24); background: rgba(159, 41, 41, .07); }
      .uno-media-strip { display: grid; grid-template-columns: repeat(auto-fill, minmax(132px, 1fr)); gap: 10px; }
      .uno-media-tile { min-width: 0; border: 1px solid var(--uno-line); background: #fafaf8; overflow: hidden; }
      .uno-media-tile img { width: 100%; max-height: 420px; object-fit: contain; display: block; background: #ededeb; }
      .uno-media-tile span { display: block; padding: 8px; color: var(--uno-muted); font-size: 11px; line-height: 1.35; word-break: break-all; }
      .uno-media-warning { margin: 12px 0 0; padding: 12px; border: 1px solid rgba(159, 41, 41, .24); background: rgba(159, 41, 41, .07); color: var(--uno-danger); font-size: 13px; font-weight: 800; line-height: 1.55; }
      .uno-wide-textarea textarea { min-height: 280px; font-family: Consolas, "Noto Sans KR", monospace; font-size: 14px; }
      @media (max-width: 860px) {
        .uno-product-hero, .uno-product-state, .uno-form-grid, .uno-hub-grid, .uno-hub-grid.is-operational { grid-template-columns: 1fr; }
        .uno-calendar-grid { grid-template-columns: 1fr; }
        .uno-calendar-weekday { display: none; }
      }
    </style>

    <p class="uno-admin-status" data-status>상품 정보를 불러오는 중입니다.</p>

    <section class="uno-admin-panel" style="margin-top: 18px;">
      <div class="uno-product-hero">
        <div data-thumb><div class="uno-product-thumb">LOADING</div></div>
        <div>
          <p class="uno-admin-eyebrow" data-type>PRODUCT</p>
          <h2 style="margin: 0; font-size: clamp(30px, 4vw, 52px); line-height: 1.05;" data-product-title>상품명 확인 중</h2>
          <p class="uno-admin-copy" data-product-summary>기존 우노트래블 DB에서 상품 정보를 읽고 있습니다.</p>
          <div class="uno-admin-meta" data-product-meta></div>
        </div>
        <aside class="uno-product-summary-bar">
          <div class="uno-product-state" data-product-state></div>
          <div class="uno-product-actions" data-product-actions></div>
        </aside>
      </div>
    </section>

    <section class="uno-hub-section">
      <div class="uno-hub-section-head">
        <div><h3>기본 관리</h3><p>상품이 프런트에 보이는 기본 요소를 먼저 정리합니다.</p></div>
      </div>
      <div class="uno-hub-grid">
        <article class="uno-admin-card uno-hub-card"><div><span class="uno-card-kicker">01 Basic</span><h3>기본 정보</h3><p>상품명, 카테고리, 간단 소개, 예약 입력 필수값을 확인합니다.</p></div><button class="uno-admin-button secondary" type="button" data-open-modal="basic">수정</button></article>
        <article class="uno-admin-card uno-hub-card"><div><span class="uno-card-kicker">02 Media</span><h3>썸네일 / 이미지</h3><p>프런트 목록과 상세 상단에서 보이는 대표 이미지를 관리합니다.</p></div><button class="uno-admin-button secondary" type="button" data-open-modal="media">수정</button></article>
        <article class="uno-admin-card uno-hub-card"><div><span class="uno-card-kicker">03 Pricing</span><h3>요금 / 옵션</h3><p>예약금, 현지 지불금, 연령/조건별 요금 옵션을 확인합니다.</p></div><button class="uno-admin-button secondary" type="button" data-open-modal="pricing">수정</button></article>
      </div>
    </section>

    <section class="uno-hub-section">
      <div class="uno-hub-section-head">
        <div><h3>운영 관리</h3><p>세미패키지와 데일리투어의 실제 운영 정보를 상품 유형에 맞춰 관리합니다.</p></div>
      </div>
      <div class="uno-hub-grid is-operational">
        <article class="uno-admin-card uno-hub-card" data-semi-card><div><span class="uno-card-kicker">Semi Schedule</span><h3>세미패키지 일정</h3><p>항공권 판매 항목이 아니라, 보딩패스 UI에 표시할 출발/도착 일정만 관리합니다.</p></div><button class="uno-admin-button secondary" type="button" data-open-modal="semi">수정</button></article>
        <article class="uno-admin-card uno-hub-card" data-daily-card><div><span class="uno-card-kicker">Daily Calendar</span><h3>데일리투어 캘린더</h3><p>월 단위 캘린더에서 가능, 임박, 마감, 정원과 예약 인원을 관리합니다.</p></div><button class="uno-admin-button secondary" type="button" data-open-modal="daily">수정</button></article>
        <article class="uno-admin-card uno-hub-card"><div><span class="uno-card-kicker">Operation Notes</span><h3>운영 안내</h3><p>만남장소, 포함사항, 준비물, 취소 규정은 별도 편집 흐름으로 관리합니다.</p></div><button class="uno-admin-button secondary" type="button" data-open-modal="operation">수정</button></article>
        <article class="uno-admin-card uno-hub-card"><div><span class="uno-card-kicker">Front Status</span><h3>프런트 노출 상태</h3><p>프런트 노출, 준비중, 숨김 상태는 상품 운영 목록에서 빠르게 조정합니다.</p></div><a class="uno-admin-button secondary" href="/admin/renewal/products.php">이동</a></article>
      </div>
    </section>

    <section class="uno-hub-section">
      <div class="uno-hub-section-head">
        <div><h3>상세 / 점검</h3><p>무거운 상세 콘텐츠와 기존 관리자에서 흩어진 필드는 마지막에 확인합니다.</p></div>
      </div>
      <div class="uno-hub-grid">
        <article class="uno-admin-card uno-hub-card"><div><span class="uno-card-kicker">Detail</span><h3>상세 내용 / 코스</h3><p>상세 본문, 코스 이미지, 안내 이미지처럼 무거운 상세 콘텐츠를 관리합니다.</p></div><button class="uno-admin-button secondary" type="button" data-open-modal="detail">수정</button></article>
        <article class="uno-admin-card uno-hub-card"><div><span class="uno-card-kicker">Legacy Audit</span><h3>누락 검토</h3><p>정상가격, B2B, 바우처, 상품 팝업, 추천 표시 등 기존 필드를 재검토합니다.</p></div><button class="uno-admin-button secondary" type="button" data-open-modal="audit">수정</button></article>
      </div>
    </section>

    <div class="uno-admin-modal" data-modal aria-hidden="true">
      <section class="uno-admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="uno-admin-modal-head">
          <div><p class="uno-admin-eyebrow" data-modal-eyebrow>Product Hub</p><h2 id="modal-title" data-modal-title>편집 영역</h2></div>
          <button class="uno-admin-close" type="button" data-close-modal>닫기</button>
        </div>
        <div data-modal-body></div>
      </section>
    </div>

    <script>
      const legacyProductId = <?php echo (int) $legacyProductId; ?>;
      const state = { data: null, activeModal: "", dailyMonth: null };
      const $ = (selector, root = document) => root.querySelector(selector);
      const statusEl = $("[data-status]");
      const titleEl = $("[data-product-title]");
      const summaryEl = $("[data-product-summary]");
      const metaEl = $("[data-product-meta]");
      const typeEl = $("[data-type]");
      const thumbEl = $("[data-thumb]");
      const stateEl = $("[data-product-state]");
      const actionsEl = $("[data-product-actions]");
      const semiCardEl = $("[data-semi-card]");
      const dailyCardEl = $("[data-daily-card]");
      const modalEl = $("[data-modal]");
      const modalTitleEl = $("[data-modal-title]");
      const modalEyebrowEl = $("[data-modal-eyebrow]");
      const modalBodyEl = $("[data-modal-body]");

      const setStatus = (message, tone = "") => {
        statusEl.textContent = message;
        statusEl.className = "uno-admin-status" + (tone ? " " + tone : "");
      };
      const getCookie = (name) => document.cookie.split(";").map((v) => v.trim()).find((v) => v.indexOf(name + "=") === 0)?.slice(name.length + 1) || "";
      const escapeHtml = (value) => String(value ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
      const textFromHtml = (value) => { const div = document.createElement("div"); div.innerHTML = String(value || ""); return div.textContent.trim(); };
      const linesFromHtml = (value) => {
        const normalized = String(value || "")
          .replace(/<br\s*\/?>/gi, "\n")
          .replace(/<\/p>/gi, "\n")
          .replace(/<\/li>/gi, "\n")
          .replace(/<li[^>]*>/gi, "- ");
        return textFromHtml(normalized).replace(/\n{3,}/g, "\n\n").trim();
      };
      const linesToHtml = (value) => String(value || "")
        .split(/\r?\n/)
        .map((line) => line.trim())
        .filter((line) => line !== "")
        .map((line) => escapeHtml(line))
        .join("<br>");
      const valueOrDash = (value) => { const text = String(value ?? "").trim(); return text === "" ? "-" : text; };
      const chip = (label, tone = "") => '<span class="uno-admin-chip ' + tone + '">' + escapeHtml(label) + '</span>';
      const mediaTile = (image) => {
        const label = "#" + escapeHtml(image.no ?? "-") + " " + escapeHtml(image.source || image.file || "image");
        return '<a class="uno-media-tile" href="' + escapeHtml(image.url || "#") + '" target="_blank" rel="noopener">' +
          '<img src="' + escapeHtml(image.url || "") + '" alt="" loading="lazy">' +
          '<span>' + label + '</span>' +
        '</a>';
      };
      const mediaGroup = (title, description, images, emptyText, editHref = "") => {
        const list = Array.isArray(images) ? images.filter((image) => image && image.url) : [];
        return '<section class="uno-media-group">' +
          '<div class="uno-media-group-head"><div><h4>' + escapeHtml(title) + '</h4><p>' + escapeHtml(description) + '</p></div>' +
          '<div class="uno-media-tools">' +
          (editHref ? '<a class="uno-media-edit-link" href="' + escapeHtml(editHref) + '" target="_blank" rel="noopener">기존 관리자에서 수정</a>' : '') +
          '<strong class="uno-media-count ' + (list.length ? '' : 'is-empty') + '">' + list.length + ' files</strong></div></div>' +
          (list.length ? '<div class="uno-media-strip">' + list.map(mediaTile).join("") + '</div>' : '<p class="uno-media-warning">' + escapeHtml(emptyText) + '</p>') +
        '</section>';
      };
      const mediaAudit = (product, mode = "all") => {
        const media = product.media || {};
        const links = media.legacyAdminLinks || {};
        const groups = [];
        if (mode === "all" || mode === "hero") {
          groups.push(mediaGroup(
            "write.php 파일 #1~#11 / 상품 상세 히어로 썸네일",
            "bo_table=product. 파일 #1은 상품 목록 대표 썸네일 겸 히어로 첫 썸네일이고, 파일 #2 이후도 바디가 아니라 히어로 갤러리입니다.",
            media.productImages || [],
            "product 첨부 이미지가 없습니다. 상품 목록 썸네일과 히어로 갤러리가 비어 보일 수 있습니다.",
            links.product || ""
          ));
          groups.push(mediaGroup(
            "tourCourse.php v2_tourTop / 상세 히어로 메인",
            "bo_table=v2_tourTop. 상세페이지 상단 히어로의 메인 이미지로 사용합니다.",
            media.tourTopImages || [],
            "v2_tourTop 이미지가 없습니다. 프런트는 product 파일 #1을 보조 이미지로 사용할 수 있습니다.",
            links.tourTop || ""
          ));
        }
        if (mode === "all" || mode === "body") {
          groups.push(mediaGroup(
            "tourCourse.php v2_course / PRODUCT DOCUMENT 투어코스",
            "bo_table=v2_course. 상세페이지 바디가 아니라 PRODUCT DOCUMENT의 투어코스 이미지입니다.",
            media.tourCourseImages || [],
            "v2_course 이미지가 없습니다. PRODUCT DOCUMENT 투어코스 영역이 비어 보일 수 있습니다.",
            links.tourCourse || ""
          ));
        }
        if (mode === "all" || mode === "body") {
          groups.push(mediaGroup(
            "tourCourse.php v2_tourAd / PRODUCT DOCUMENT 특장점",
            "bo_table=v2_tourAd. 이 상품을 왜 구매해야 하는지 보여주는 특장점 이미지입니다.",
            media.tourAdImages || [],
            "v2_tourAd 이미지가 없습니다. PRODUCT DOCUMENT 특장점 영역이 비어 보일 수 있습니다.",
            links.tourAd || ""
          ));
          groups.push(mediaGroup(
            "tourCourse.php v2_tourInfo / 상세 바디 투어설명",
            "bo_table=v2_tourInfo. 상세페이지 바디에 들어가는 관광지 설명/투어설명 이미지입니다.",
            media.tourInfoImages || [],
            "v2_tourInfo 이미지가 없습니다. 상세페이지 바디 투어설명이 비어 보입니다.",
            links.tourInfo || ""
          ));
        }
        return '<div class="uno-media-audit">' + groups.join("") + '</div>';
      };

      const apiRequest = async (body = null) => {
        const options = { credentials: "same-origin" };
        if (body) {
          options.method = "POST";
          options.headers = { "Content-Type": "application/json", "X-CSRF-Token": getCookie("unotravel_csrf_token") };
          options.body = JSON.stringify(body);
        }
        const response = await fetch("/api/admin/product-editor.php?legacyProductId=" + encodeURIComponent(legacyProductId), options);
        const json = await response.json();
        if (!json.ok) throw new Error(json.error ? json.error.message : "상품 정보를 처리하지 못했습니다.");
        state.data = json.data;
        return json.data;
      };

      const sectionRows = (rows) => '<div style="display:grid;gap:10px;">' + rows.map(([label, value]) => (
        '<div style="display:grid;grid-template-columns:150px minmax(0,1fr);gap:14px;padding-bottom:10px;border-bottom:1px solid var(--uno-line);">' +
        '<strong>' + escapeHtml(label) + '</strong><span style="color:var(--uno-muted);line-height:1.55;white-space:pre-wrap;">' + escapeHtml(valueOrDash(value)) + '</span></div>'
      )).join("") + '</div>';

      const field = (label, name, value = "", type = "text", placeholder = "") => '<div class="uno-form-field"><label>' + escapeHtml(label) + '</label><input type="' + escapeHtml(type) + '" data-field="' + escapeHtml(name) + '" value="' + escapeHtml(value) + '" placeholder="' + escapeHtml(placeholder) + '"></div>';
      const textareaField = (label, name, value = "", placeholder = "") => '<div class="uno-form-field full"><label>' + escapeHtml(label) + '</label><textarea data-field="' + escapeHtml(name) + '" placeholder="' + escapeHtml(placeholder) + '">' + escapeHtml(value) + '</textarea></div>';
      const selectField = (label, name, options, selected = "") => '<div class="uno-form-field"><label>' + escapeHtml(label) + '</label><select data-field="' + escapeHtml(name) + '">' + options.map(([value, text]) => '<option value="' + escapeHtml(value) + '" ' + (value === selected ? 'selected' : '') + '>' + escapeHtml(text) + '</option>').join("") + '</select></div>';
      const readField = (root, name) => { const input = $('[data-field="' + name + '"]', root); if (!input) return ""; return input.type === "checkbox" ? input.checked : input.value.trim(); };
      const guideIdsFromValue = (value = "") => Array.from(new Set((String(value || "").match(/\d+/g) || []).map(String)));
      const updateGuideSelector = (root) => {
        if (!root) return;
        const checked = Array.from(root.querySelectorAll("[data-guide-option]:checked"));
        const ids = checked.map((input) => input.value);
        const labels = checked.map((input) => input.dataset.guideTitle).filter(Boolean);
        const hidden = $('[data-field="guideInfo"]', root);
        const summary = $("[data-guide-summary]", root);
        if (hidden) hidden.value = ids.join(",");
        if (summary) summary.innerHTML = labels.length > 0
          ? labels.map((label) => '<span class="uno-guide-chip">' + escapeHtml(label) + '</span>').join("")
          : '<span>선택된 가이드가 없습니다.</span>';
      };
      const filterGuideSelector = (root, keyword) => {
        if (!root) return;
        const normalized = String(keyword || "").trim().toLowerCase();
        root.querySelectorAll("[data-guide-row]").forEach((row) => {
          const text = String(row.dataset.guideSearch || "").toLowerCase();
          row.classList.toggle("is-hidden", normalized !== "" && text.indexOf(normalized) === -1);
        });
      };
      const buildGuideSelector = (selectedValue = "") => {
        const options = state.data.guideOptions || [];
        if (!options.length) {
          return textareaField("가이드 정보", "guideInfo", selectedValue, "admGuideInfo 가이드 ID를 쉼표로 입력");
        }
        const selectedIds = new Set(guideIdsFromValue(selectedValue));
        const selectedLabels = options.filter((guide) => selectedIds.has(String(guide.id))).map((guide) => guide.title);
        return '<div class="uno-form-field full" data-guide-selector>' +
          '<label>가이드 정보</label>' +
          '<input type="hidden" data-field="guideInfo" value="' + escapeHtml(guideIdsFromValue(selectedValue).join(",")) + '">' +
          '<div class="uno-guide-picker">' +
          '<div class="uno-guide-selected" data-guide-summary>' + (selectedLabels.length ? selectedLabels.map((label) => '<span class="uno-guide-chip">' + escapeHtml(label) + '</span>').join("") : '<span>선택된 가이드가 없습니다.</span>') + '</div>' +
          '<input class="uno-guide-search" type="search" data-guide-search placeholder="가이드 이름 검색">' +
          '<div class="uno-guide-list">' +
            options.map((guide) => {
              const id = String(guide.id);
              const title = guide.title || ("Guide #" + id);
              return '<label class="uno-guide-option" data-guide-row data-guide-search="' + escapeHtml(title + " " + (guide.bodyText || "")) + '" title="' + escapeHtml(guide.bodyText || title) + '">' +
                '<input type="checkbox" data-guide-option value="' + escapeHtml(id) + '" data-guide-title="' + escapeHtml(guide.title || ("Guide #" + id)) + '" ' + (selectedIds.has(id) ? 'checked' : '') + '> ' +
                '<span>' + escapeHtml(title) + '</span>' +
              '</label>';
            }).join("") +
          '</div>' +
          '</div>' +
          '<small style="display:block;margin-top:8px;color:var(--uno-muted);">bo_table=admGuideInfo의 가이드를 선택하면 기존 guide_info 필드에 ID로 저장됩니다.</small>' +
        '</div>';
      };
      const dateKey = (date) => date.getFullYear() + "-" + String(date.getMonth() + 1).padStart(2, "0") + "-" + String(date.getDate()).padStart(2, "0");
      const parseDateKey = (value) => { const [y, m, d] = String(value || "").split("-").map(Number); return new Date(y || 1970, (m || 1) - 1, d || 1); };

      const actionLinks = (product) => '<div class="uno-admin-actions" style="justify-content:flex-start;margin-top:18px;">' +
        (product.frontendHref ? '<a class="uno-admin-button secondary" href="' + escapeHtml(product.frontendHref) + '" target="_blank" rel="noopener">프런트 보기</a>' : '') +
        '<a class="uno-admin-button secondary" href="' + escapeHtml(product.legacyEditHref || ("/admin/write.php?w=u&bo_table=product&wr_id=" + legacyProductId)) + '">기존 고급 수정</a></div>';

      const buildBasicEditor = () => {
        const product = state.data.product || {};
        return '<p class="uno-section-note">상품명, 카테고리, 간단 소개, 예약 입력 필수값처럼 상품의 뼈대가 되는 정보만 관리합니다.</p>' +
          '<div class="uno-price-row" data-basic-editor>' +
            '<div class="uno-schedule-head"><h3 class="uno-schedule-title">기본 정보</h3><button class="uno-admin-button" type="button" data-save-basic>기본 정보 저장</button></div>' +
            '<div class="uno-form-grid">' +
              field("상품명", "title", product.title || "", "text", "상품명을 입력해 주세요") +
              field("카테고리", "category", product.category || "", "text", "예: 세미패키지, 유럽, 이탈리아") +
              textareaField("간단 소개", "summary", textFromHtml(product.summary || ""), "상품 목록과 상세 상단에 쓰이는 짧은 소개") +
              textareaField("가이드 문구", "guideText", textFromHtml(product.guideText || ""), "상세 상단 또는 예약 모듈에 표시할 안내 문구") +
              selectField("예약 상태값", "reservationStatus", [["", "기본값"], ["Y", "예약 가능"], ["N", "예약 중지"], ["H", "숨김 / 확인 필요"]], product.reservationStatus || "") +
              '<div class="uno-form-field full"><label>예약 입력 정보</label><div class="uno-check-row">' +
                '<label><input type="checkbox" data-field="requiresPassport" ' + (product.requiresPassport ? 'checked' : '') + '> 여권 정보 사용</label>' +
                '<label><input type="checkbox" data-field="requiresRoomInfo" ' + (product.requiresRoomInfo ? 'checked' : '') + '> 룸 정보 사용</label>' +
                '<label><input type="checkbox" data-field="requiresDelivery" ' + (product.requiresDelivery ? 'checked' : '') + '> 배송 정보 사용</label>' +
              '</div></div>' +
            '</div>' +
          '</div>';
      };

      const basicPayload = () => {
        const box = $("[data-basic-editor]", modalBodyEl);
        return {
          action: "saveBasicInfo",
          title: readField(box, "title"),
          category: readField(box, "category"),
          summary: readField(box, "summary"),
          guideText: readField(box, "guideText"),
          reservationStatus: readField(box, "reservationStatus"),
          requiresPassport: readField(box, "requiresPassport"),
          requiresRoomInfo: readField(box, "requiresRoomInfo"),
          requiresDelivery: readField(box, "requiresDelivery"),
        };
      };

      const parseBoardingText = (schedule) => {
        const raw = String(schedule.air || "").trim();
        const parsed = { outboundDeparturePlace: "", outboundDepartureTime: "", outboundArrivalPlace: "", outboundArrivalLabel: "", returnDeparturePlace: "", returnDepartureTime: "", returnArrivalPlace: "", returnArrivalLabel: "", boardingLabel: "" };
        if (!raw) return parsed;
        const parseLeg = (text, prefix) => {
          const [left = "", right = ""] = text.split("->").map((part) => part.trim());
          const timeMatch = left.match(/(\d{1,2}:\d{2})/);
          parsed[prefix + "DepartureTime"] = timeMatch ? timeMatch[1] : "";
          parsed[prefix + "DeparturePlace"] = timeMatch ? left.replace(timeMatch[1], "").trim() : left;
          const rightParts = right.split("/").map((part) => part.trim());
          parsed[prefix + "ArrivalPlace"] = rightParts[0] || "";
          parsed[prefix + "ArrivalLabel"] = rightParts.slice(1).join(" / ");
        };
        raw.split(/\r?\n/).forEach((line) => {
          const clean = line.trim();
          if (clean.indexOf("OUT:") === 0) parseLeg(clean.slice(4).trim(), "outbound");
          if (clean.indexOf("RETURN:") === 0) parseLeg(clean.slice(7).trim(), "return");
        });
        if (!parsed.outboundDeparturePlace && !parsed.outboundArrivalPlace && !parsed.returnDeparturePlace && !parsed.returnArrivalPlace) parsed.boardingLabel = raw;
        return parsed;
      };

      const semiScheduleCard = (schedule = {}) => {
        const parsed = parseBoardingText(schedule);
        const visibleChecked = schedule.id ? !!schedule.isVisible : true;
        return '<article class="uno-schedule-card" data-semi-schedule-card data-id="' + escapeHtml(schedule.id || "") + '">' +
          '<div class="uno-schedule-head"><h3 class="uno-schedule-title">' + escapeHtml(schedule.id ? "일정 #" + schedule.id : "새 일정") + '</h3><div class="uno-admin-actions">' +
          '<button class="uno-admin-button secondary" type="button" data-delete-semi-schedule ' + (!schedule.id ? 'disabled' : '') + '>삭제</button><button class="uno-admin-button" type="button" data-save-semi-schedule>저장</button></div></div>' +
          '<div class="uno-form-grid">' +
          field("투어 시작일", "startDate", schedule.startDate || "", "date") +
          field("도착 / 종료일", "arriveDate", schedule.arriveDate || schedule.startDate || "", "date") +
          field("가는 편 출발지", "outboundDeparturePlace", parsed.outboundDeparturePlace) +
          field("가는 편 출발 시간", "outboundDepartureTime", parsed.outboundDepartureTime, "time") +
          field("가는 편 도착지", "outboundArrivalPlace", parsed.outboundArrivalPlace) +
          field("가는 편 도착 일정", "outboundArrivalLabel", parsed.outboundArrivalLabel, "text", "2일차 오전") +
          field("오는 편 출발지", "returnDeparturePlace", parsed.returnDeparturePlace) +
          field("오는 편 출발 시간", "returnDepartureTime", parsed.returnDepartureTime, "time") +
          field("오는 편 도착지", "returnArrivalPlace", parsed.returnArrivalPlace) +
          field("오는 편 도착 일정", "returnArrivalLabel", parsed.returnArrivalLabel, "text", "11일차 오후") +
          '<div class="uno-form-field full"><div class="uno-check-row"><label><input type="checkbox" data-field="isVisible" ' + (visibleChecked ? 'checked' : '') + '> 프런트 노출</label><label><input type="checkbox" data-field="isMain" ' + (schedule.isMain ? 'checked' : '') + '> 대표 일정</label></div></div>' +
          '</div></article>';
      };

      const buildSemiScheduleEditor = () => {
        const schedules = state.data.semiSchedules || [];
        return '<p class="uno-section-note">좌석, 항공권 코드, 추가금 같은 항공권 판매 항목은 여기서 다루지 않습니다. 프런트 보딩패스에 필요한 일정만 관리합니다.</p>' +
          '<div class="uno-admin-actions" style="justify-content:flex-start;margin-bottom:14px;"><button class="uno-admin-button" type="button" data-add-semi-schedule>일정 추가</button></div>' +
          '<div class="uno-schedule-list" data-semi-schedule-list>' + (schedules.length ? schedules.map(semiScheduleCard).join("") : semiScheduleCard({})) + '</div>';
      };

      const semiPayload = (card) => ({ action: "saveSemiSchedule", id: card.dataset.id ? Number(card.dataset.id) : 0, startDate: readField(card, "startDate"), arriveDate: readField(card, "arriveDate"), outboundDeparturePlace: readField(card, "outboundDeparturePlace"), outboundDepartureTime: readField(card, "outboundDepartureTime"), outboundArrivalPlace: readField(card, "outboundArrivalPlace"), outboundArrivalLabel: readField(card, "outboundArrivalLabel"), returnDeparturePlace: readField(card, "returnDeparturePlace"), returnDepartureTime: readField(card, "returnDepartureTime"), returnArrivalPlace: readField(card, "returnArrivalPlace"), returnArrivalLabel: readField(card, "returnArrivalLabel"), isVisible: readField(card, "isVisible"), isMain: readField(card, "isMain") });

      const priceField = (label, name, value = "") => field(label, name, String(value || ""), "number");
      const textPriceField = (label, name, value = "", placeholder = "") => field(label, name, String(value || ""), "text", placeholder);
      const ticketFeeSelect = (selected = "") => selectField(
        "티켓 요금(필요시)",
        "ticketFeeId",
        (state.data.ticketFeeOptions || [{ id: "", label: "선택 안 함" }]).map((item) => [String(item.id ?? ""), item.label || ("Ticket #" + item.id)]),
        String(selected || "")
      );
      const money = (value) => Number(String(value || "0").replace(/[^0-9-]/g, "")) || 0;

      const dailyFeeCard = (option = {}) => '<article class="uno-price-row" data-daily-fee-card data-id="' + escapeHtml(option.id || "") + '">' +
        '<div class="uno-schedule-head"><h3 class="uno-schedule-title">' + escapeHtml(option.id ? "요금 옵션 #" + option.id : "새 요금 옵션") + '</h3><div class="uno-admin-actions">' +
        '<button class="uno-admin-button secondary" type="button" data-delete-daily-fee ' + (!option.id ? 'disabled' : '') + '>삭제</button><button class="uno-admin-button" type="button" data-save-daily-fee>저장</button></div></div>' +
        '<div class="uno-form-grid">' +
        field("신청구분", "label", option.label || "", "text", "예: 성인") +
        priceField("홈페이지 예약금", "deposit", option.deposit || 0) +
        textPriceField("사전 예약 후 현장 지불", "localPayment", option.localPayment || "", "0 또는 -") +
        textPriceField("현지 지불", "extraPayment", option.extraPayment || "", "0 또는 -") +
        ticketFeeSelect(option.ticketFeeId || "") +
        '<div class="uno-form-field full"><div class="uno-check-row"><label><input type="checkbox" data-field="isDefault" ' + (option.isDefault ? 'checked' : '') + '> 대표요금</label></div></div>' +
        '</div></article>';

      const semiPriceCard = (schedule = {}) => '<article class="uno-price-row" data-semi-price-card data-id="' + escapeHtml(schedule.id || "") + '">' +
        '<div class="uno-schedule-head"><h3 class="uno-schedule-title">' + escapeHtml((schedule.startDate || "일정") + " 가격") + '</h3><button class="uno-admin-button" type="button" data-save-semi-price>저장</button></div>' +
        '<div class="uno-form-grid">' +
        priceField("예약금", "deposit", schedule.deposit || 0) +
        priceField("현지 지불금", "localPayment", schedule.localPayment || 0) +
        priceField("추가금", "extraPayment", schedule.extraPayment || 0) +
        priceField("총 금액", "totalPrice", schedule.totalPrice || 0) +
        '<div class="uno-form-field full"><span style="color:var(--uno-muted);line-height:1.55;">일정 날짜와 보딩패스 문구는 세미패키지 일정 모달에서 관리합니다. 여기서는 기존 일정의 가격만 수정합니다.</span></div>' +
        '</div></article>';

      const buildPricingEditor = () => {
        const product = state.data.product || {};
        const extras = product.extras || {};
        const isDaily = product.productType === "daily";
        const rows = isDaily
          ? (state.data.dailyFeeOptions || []).map(dailyFeeCard).join("") + dailyFeeCard({})
          : (state.data.semiSchedules || []).map(semiPriceCard).join("") || '<p class="uno-section-note">등록된 세미패키지 일정이 없습니다. 먼저 세미패키지 일정 모달에서 일정을 추가해 주세요.</p>';

        return '<p class="uno-section-note">예약과 직접 연결되는 값입니다. 데일리투어는 옵션 단위로, 세미패키지는 일정 단위로 가격을 관리합니다.</p>' +
          '<div class="uno-price-row" data-pricing-meta><div class="uno-schedule-head"><h3 class="uno-schedule-title">프런트 가격 문구</h3><button class="uno-admin-button" type="button" data-save-pricing-meta>저장</button></div>' +
          '<div class="uno-form-grid">' +
            textareaField("정상가격 / 전체 상품가", "originalFeeText", extras.originalFeeText || "", "예: 6000000") +
            textareaField("가격 안내 문구", "priceDescription", extras.priceDescription || "", "예: 투어 요금 600만원은 예약 비용과 요금을 포함한 금액입니다.") +
          '</div></div>' +
          '<div class="uno-price-list" style="margin-top:14px;">' + rows + '</div>';
      };

      const buildMediaEditor = () => {
        const product = state.data.product || {};
        const preview = product.thumbnailUrl
          ? '<img data-thumbnail-preview src="' + escapeHtml(product.thumbnailUrl) + '" alt="">'
          : '<div class="uno-media-empty" data-thumbnail-preview>NO IMAGE</div>';

        return '<p class="uno-section-note">대표 이미지는 프런트 상품 목록과 상세 상단에서 가장 먼저 보이는 이미지입니다. 기존 상품 게시판의 첫 번째 파일 위치에 저장됩니다.</p>' +
          '<div class="uno-media-preview">' +
            '<div>' + preview + '</div>' +
            '<div class="uno-price-row" data-thumbnail-uploader>' +
              '<div class="uno-form-grid">' +
                '<div class="uno-form-field full"><label>대표 썸네일</label><input type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-thumbnail-file></div>' +
                '<div class="uno-form-field full"><span style="color:var(--uno-muted);line-height:1.6;">권장 비율은 세로형 상품 카드에 맞는 4:5 또는 3:4입니다. jpg, png, webp, gif / 8MB 이하 이미지만 업로드합니다.</span></div>' +
              '</div>' +
              '<div class="uno-admin-actions" style="justify-content:flex-start;margin-top:16px;">' +
                '<button class="uno-admin-button" type="button" data-upload-thumbnail>썸네일 저장</button>' +
              '</div>' +
            '</div>' +
          '</div>' +
          '<p class="uno-section-note" style="margin-top:16px;">상세 본문 이미지와 코스 이미지는 다음 단계에서 별도 이미지 관리 모달로 분리합니다.</p>' +
          mediaAudit(product, "all") +
          actionLinks(product);
      };

      const buildOperationEditor = () => {
        const options = state.data.productOptions || {};
        return '<p class="uno-section-note">기존 팝업에 흩어져 있던 운영 안내를 한 화면에서 관리합니다. HTML 코드는 숨기고, 줄바꿈 중심의 문장으로 편집합니다.</p>' +
          '<div class="uno-price-row" data-operation-editor>' +
            '<div class="uno-schedule-head"><h3 class="uno-schedule-title">운영 안내</h3><button class="uno-admin-button" type="button" data-save-operation>운영 안내 저장</button></div>' +
            '<div class="uno-form-grid">' +
              textareaField("만남장소", "meeting", linesFromHtml(options.meeting), "예: 06시 20분까지 호텔 갈레스 앞") +
              textareaField("만남시간", "meetingTime", linesFromHtml(options.meetingTime), "예: 06:20까지") +
              textareaField("투어 요일", "tourDay", linesFromHtml(options.tourDay), "예: 매일 출발 / 월,수,금 진행") +
              textareaField("투어 시간", "tourTime", linesFromHtml(options.tourTime), "예: 06:20 ~ 22:00") +
              textareaField("포함사항", "includes", linesFromHtml(options.includes), "한 줄에 하나씩 입력") +
              textareaField("불포함사항", "excludes", linesFromHtml(options.excludes), "한 줄에 하나씩 입력") +
              textareaField("예약 전 안내", "beforeNotice", linesFromHtml(options.beforeNotice), "예약 전 반드시 확인해야 하는 내용") +
              textareaField("준비물", "preparation", linesFromHtml(options.preparation), "한 줄에 하나씩 입력") +
              textareaField("취소 규정", "cancelRules", linesFromHtml(options.cancelRules), "상품별 취소/환불 규정") +
              textareaField("지도 / 위치", "map", options.map || "", "구글 지도 iframe 또는 지도 URL") +
              textareaField("유튜브", "youtube", options.youtube || "", "유튜브 iframe 또는 URL") +
            '</div>' +
          '</div>';
      };

      const operationPayload = () => {
        const box = $("[data-operation-editor]", modalBodyEl);
        return {
          action: "saveProductOptions",
          productOptions: {
            meeting: linesToHtml(readField(box, "meeting")),
            meetingTime: linesToHtml(readField(box, "meetingTime")),
            tourDay: linesToHtml(readField(box, "tourDay")),
            tourTime: linesToHtml(readField(box, "tourTime")),
            includes: linesToHtml(readField(box, "includes")),
            excludes: linesToHtml(readField(box, "excludes")),
            beforeNotice: linesToHtml(readField(box, "beforeNotice")),
            preparation: linesToHtml(readField(box, "preparation")),
            cancelRules: linesToHtml(readField(box, "cancelRules")),
            map: readField(box, "map"),
            youtube: readField(box, "youtube"),
          },
        };
      };

      const buildDetailEditor = () => {
        const product = state.data.product || {};
        const extras = product.extras || {};
        return '<p class="uno-section-note">상세 본문은 기존 프런트 출력과 연결되어 있어 HTML을 보존합니다. 코스, 추천 상품, 가이드 정보는 별도 필드로 분리해 관리합니다.</p>' +
          '<div class="uno-price-row" data-detail-editor>' +
            '<div class="uno-schedule-head"><h3 class="uno-schedule-title">상세 내용 / 코스</h3><button class="uno-admin-button" type="button" data-save-detail>상세 내용 저장</button></div>' +
            '<div class="uno-form-grid">' +
              '<div class="uno-form-field full uno-wide-textarea"><label>상세 본문 HTML</label><textarea data-field="content" placeholder="기존 상세 본문 HTML">' + escapeHtml(product.content || "") + '</textarea></div>' +
              textareaField("추천 상품", "recommendTour", extras.recommendTour || "", "상품 ID 또는 legacy ID를 줄 단위로 입력") +
              textareaField("이벤트 / 코스", "eventCourse", extras.eventCourse || "", "코스 설명 또는 연결 정보") +
              buildGuideSelector(extras.guideInfo || "") +
            '</div>' +
          '</div>' +
          '<p class="uno-section-note" style="margin-top:16px;">상세 이미지와 코스 이미지 업로드는 다음 이미지 관리 단계에서 분리합니다.</p>';
      };

      const detailPayload = () => {
        const box = $("[data-detail-editor]", modalBodyEl);
        return {
          action: "saveDetailContent",
          content: readField(box, "content"),
          extras: {
            recommendTour: readField(box, "recommendTour"),
            eventCourse: readField(box, "eventCourse"),
            guideInfo: readField(box, "guideInfo"),
          },
        };
      };

      const buildAuditEditor = () => {
        const product = state.data.product || {};
        const extras = product.extras || {};
        return '<p class="uno-section-note">기존 관리자에서 흩어져 있던 상품 운영 필드를 한곳에서 점검합니다. 상세 본문이나 일정은 각 전용 모달에서 관리하고, 이 화면은 프런트 표시와 예약 안내에 직접 영향을 주는 누락 가능 항목만 저장합니다.</p>' +
          '<div class="uno-price-row" data-audit-editor>' +
            '<div class="uno-schedule-head"><h3 class="uno-schedule-title">누락 검토 / 표시 설정</h3><button class="uno-admin-button" type="button" data-save-audit>누락 검토 저장</button></div>' +
            '<div class="uno-form-grid">' +
              textareaField("정상가격 / 전체 상품가", "originalFeeText", extras.originalFeeText || "", "예: 6000000") +
              textareaField("가격 안내 문구", "priceDescription", extras.priceDescription || "", "예: 투어 요금 600만원은 예약 비용과 요금을 포함한 금액입니다.") +
              field("B2B 상태", "b2bStatus", extras.b2bStatus || "", "text", "B2B 또는 제휴 판매 상태 메모") +
              field("캘린더 표시 개월", "calendarMonths", extras.calendarMonths || 2, "number", "예: 2") +
              '<div class="uno-form-field full"><label>상품 플래그</label><div class="uno-check-row">' +
                '<label><input type="checkbox" data-field="isTicket" ' + (extras.isTicket ? 'checked' : '') + '> 티켓 상품</label>' +
                '<label><input type="checkbox" data-field="isBestTour" ' + (extras.isBestTour ? 'checked' : '') + '> 추천 상품 표시</label>' +
              '</div></div>' +
              textareaField("예약 상단 안내", "reservationTopMessage", textFromHtml(extras.reservationTopMessage || ""), "예약 화면 상단에 보여줄 안내") +
              textareaField("예약 중간 안내", "reservationMiddleMessage", textFromHtml(extras.reservationMiddleMessage || ""), "예약 입력 중간 안내") +
              textareaField("예약 하단 안내", "reservationBottomMessage", textFromHtml(extras.reservationBottomMessage || ""), "예약 화면 하단 안내") +
              textareaField("예약 이벤트 안내", "reservationEventMessage", textFromHtml(extras.reservationEventMessage || ""), "이벤트/프로모션 안내") +
              textareaField("바우처 문구", "voucherMessage", textFromHtml(extras.voucherMessage || ""), "예약 확정 후 바우처에 노출할 문구") +
              textareaField("취소 규정 요약", "cancelRule", textFromHtml(extras.cancelRule || ""), "상품별 취소/환불 규정 요약") +
              textareaField("상품 팝업 내용", "popupContent", textFromHtml(extras.popupContent || ""), "상품 상세 진입 시 안내 팝업이 필요할 때만 사용") +
            '</div>' +
          '</div>' +
          actionLinks(product);
      };

      const auditPayload = () => {
        const box = $("[data-audit-editor]", modalBodyEl);
        return {
          action: "saveAuditFields",
          extras: {
            originalFeeText: readField(box, "originalFeeText"),
            priceDescription: readField(box, "priceDescription"),
            b2bStatus: readField(box, "b2bStatus"),
            isTicket: readField(box, "isTicket"),
            isBestTour: readField(box, "isBestTour"),
            calendarMonths: Number(readField(box, "calendarMonths") || 2),
            reservationTopMessage: linesToHtml(readField(box, "reservationTopMessage")),
            reservationMiddleMessage: linesToHtml(readField(box, "reservationMiddleMessage")),
            reservationBottomMessage: linesToHtml(readField(box, "reservationBottomMessage")),
            reservationEventMessage: linesToHtml(readField(box, "reservationEventMessage")),
            voucherMessage: linesToHtml(readField(box, "voucherMessage")),
            cancelRule: linesToHtml(readField(box, "cancelRule")),
            popupContent: linesToHtml(readField(box, "popupContent")),
          },
        };
      };

      const dailyFeePayload = (card) => ({ action: "saveDailyFeeOption", id: card.dataset.id ? Number(card.dataset.id) : 0, label: readField(card, "label"), deposit: money(readField(card, "deposit")), localPayment: readField(card, "localPayment"), extraPayment: readField(card, "extraPayment"), ticketFeeId: readField(card, "ticketFeeId"), isDefault: readField(card, "isDefault") });
      const semiPricePayload = (card) => {
        const schedule = (state.data.semiSchedules || []).find((item) => Number(item.id) === Number(card.dataset.id)) || {};
        return { action: "saveSemiSchedule", id: Number(card.dataset.id), startDate: schedule.startDate, arriveDate: schedule.arriveDate || schedule.startDate, boardingLabel: schedule.air || "", deposit: money(readField(card, "deposit")), localPayment: money(readField(card, "localPayment")), extraPayment: money(readField(card, "extraPayment")), totalPrice: money(readField(card, "totalPrice")), isVisible: !!schedule.isVisible, isMain: !!schedule.isMain };
      };

      const dailyStatusOptions = (selected) => [["available", "예약 가능"], ["soon", "마감 임박"], ["soldout", "마감"]].map(([value, label]) => '<option value="' + value + '" ' + (value === selected ? 'selected' : '') + '>' + label + '</option>').join("");
      const dailyStatusLabel = (status) => ({ available: "예약 가능", soon: "마감 임박", soldout: "마감" }[status] || "예약 가능");
      const calendarItemMap = () => { const map = new Map(); (state.data.dailyCalendar || []).forEach((item) => map.set(item.date, item)); return map; };
      const ensureDailyMonth = () => { if (!state.dailyMonth) { const first = (state.data.dailyCalendar || [])[0]; const base = first && first.date ? parseDateKey(first.date) : new Date(); state.dailyMonth = new Date(base.getFullYear(), base.getMonth(), 1); } };

      const buildDailyPatternPanel = () => '<section class="uno-pattern-panel"><div class="uno-schedule-head"><div><h3 class="uno-schedule-title">운영 규칙 적용</h3><p class="uno-section-note" style="margin:6px 0 0;">매일, 홀수일, 짝수일, 특정 요일 투어를 기간 단위로 한 번에 생성합니다. 마감 임박과 마감은 예약 인원 기준으로 자동 계산됩니다.</p></div><button class="uno-admin-button" type="button" data-apply-daily-pattern>규칙 적용</button></div>' +
        '<div class="uno-form-grid" data-daily-pattern-panel>' +
        field("시작일", "patternFrom", dateKey(state.dailyMonth), "date") +
        field("종료일", "patternTo", dateKey(new Date(state.dailyMonth.getFullYear() + 1, state.dailyMonth.getMonth(), state.dailyMonth.getDate())), "date") +
        selectField("운영 방식", "pattern", [["everyday", "매일 운영"], ["odd", "홀수일 운영"], ["even", "짝수일 운영"], ["weekday", "요일 선택 운영"]], "everyday") +
        field("예약 가능 인원", "patternMaxCount", "0", "number", "예: 12") +
        '<div class="uno-form-field full"><label>요일</label><div class="uno-check-row">' + [1, 2, 3, 4, 5, 6, 0].map((day) => '<label><input type="checkbox" data-weekday="' + day + '"> ' + ["일", "월", "화", "수", "목", "금", "토"][day] + '</label>').join("") + '</div></div>' +
        '<div class="uno-form-field full"><label>적용 방식</label><div class="uno-check-row"><label><input type="checkbox" data-field="replaceRange" checked> 선택 기간을 이 운영 규칙으로 재구성합니다. 운영하지 않는 날은 마감 처리됩니다.</label></div></div>' +
        '</div></section>';

      const buildDailyCalendarEditor = () => {
        ensureDailyMonth();
        const year = state.dailyMonth.getFullYear();
        const month = state.dailyMonth.getMonth();
        const firstDate = new Date(year, month, 1);
        const lastDate = new Date(year, month + 1, 0).getDate();
        const items = calendarItemMap();
        const weekdays = ["일", "월", "화", "수", "목", "금", "토"];
        const cells = [];
        for (let blank = 0; blank < firstDate.getDay(); blank += 1) {
          cells.push('<div class="uno-calendar-day is-empty" aria-hidden="true"></div>');
        }
        for (let day = 1; day <= lastDate; day += 1) {
          const current = new Date(year, month, day);
          const key = dateKey(current);
          const item = items.get(key) || { date: key, status: "available", maxCount: 0, nowCount: 0 };
          const status = item.status || "available";
          const maxCount = Number(item.maxCount || 0);
          const nowCount = Number(item.nowCount || 0);
          cells.push('<div class="uno-calendar-day" data-daily-date="' + key + '" data-max-count="' + escapeHtml(maxCount) + '"><strong>' + current.getDate() + '</strong><span class="uno-calendar-status is-' + escapeHtml(status) + '">' + escapeHtml(dailyStatusLabel(status)) + '</span><div class="uno-calendar-count">정원 ' + escapeHtml(maxCount || "-") + '명 · 예약 ' + escapeHtml(nowCount) + '명</div><div class="uno-calendar-actions"><button class="uno-calendar-small-button secondary" type="button" data-close-daily-date>마감 처리</button></div></div>');
        }
        return '<div class="uno-calendar-toolbar"><button class="uno-admin-button secondary" type="button" data-daily-month-prev>이전 월</button><h3 class="uno-calendar-title">' + year + '년 ' + String(month + 1).padStart(2, "0") + '월</h3><button class="uno-admin-button secondary" type="button" data-daily-month-next>다음 월</button></div>' +
          buildDailyPatternPanel() +
          '<p class="uno-section-note">아래 캘린더는 선택한 월의 운영 결과를 확인하는 화면입니다. 운영 요일과 기간은 위 규칙에서 관리하고, 특정 날짜를 닫아야 할 때만 마감 처리합니다.</p>' +
          '<div class="uno-calendar-grid">' + weekdays.map((day) => '<div class="uno-calendar-weekday">' + day + '</div>').join("") + cells.join("") + '</div>';
      };

      const dailyPayload = (cell, status) => ({ action: "saveDailyCalendar", date: cell.dataset.dailyDate, status, maxCount: status === "soldout" ? 0 : Number(cell.dataset.maxCount || readField($("[data-daily-pattern-panel]"), "patternMaxCount") || 0) });
      const patternPayload = () => { const panel = $("[data-daily-pattern-panel]"); return { action: "applyDailyCalendarPattern", from: readField(panel, "patternFrom"), to: readField(panel, "patternTo"), pattern: readField(panel, "pattern"), status: "available", maxCount: Number(readField(panel, "patternMaxCount") || 0), replaceRange: readField(panel, "replaceRange"), weekdays: Array.from(panel.querySelectorAll("[data-weekday]:checked")).map((input) => Number(input.dataset.weekday)) }; };

      const renderProduct = () => {
        const product = state.data.product || {};
        const options = state.data.productOptions || {};
        const extras = product.extras || {};
        const productTypeLabel = product.productType === "semi" ? "SEMI PACKAGE" : "DAILY TOUR";
        titleEl.textContent = product.title || "상품명 없음";
        summaryEl.textContent = textFromHtml(product.summary || product.content || "등록된 소개 문구를 확인해 주세요.").slice(0, 180);
        typeEl.textContent = productTypeLabel;
        thumbEl.innerHTML = product.thumbnailUrl ? '<div class="uno-product-thumb"><img src="' + escapeHtml(product.thumbnailUrl) + '" alt=""></div>' : '<div class="uno-product-thumb">NO IMAGE</div>';
        metaEl.innerHTML = [chip("legacy #" + (product.legacyProductId || "-")), chip(product.productId ? "productId " + product.productId : "productId 미연결", product.productId ? "good" : "warn"), chip(product.category || "카테고리 없음"), chip(product.requiresPassport ? "여권 필요" : "여권 없음"), chip(product.requiresRoomInfo ? "룸 정보 필요" : "룸 정보 없음"), chip(product.requiresDelivery ? "배송 정보 필요" : "배송 정보 없음")].join("");
        stateEl.innerHTML =
          '<div><span>Type</span><strong>' + escapeHtml(productTypeLabel) + '</strong></div>' +
          '<div><span>Mapping</span><strong>' + escapeHtml(product.productId ? "연결됨" : "미연결") + '</strong></div>' +
          '<div><span>Reservation</span><strong>' + escapeHtml(product.reservationStatus || "기본값") + '</strong></div>' +
          '<div><span>Input</span><strong>' + escapeHtml([product.requiresPassport ? "여권" : "", product.requiresRoomInfo ? "룸" : "", product.requiresDelivery ? "배송" : ""].filter(Boolean).join(" · ") || "기본") + '</strong></div>';
        actionsEl.innerHTML =
          (product.frontendHref ? '<a class="uno-admin-button" href="' + escapeHtml(product.frontendHref) + '" target="_blank" rel="noopener">프런트 보기</a>' : '<a class="uno-admin-button secondary" href="/admin/renewal/product-mapping.php">ID 연결하기</a>') +
          '<a class="uno-admin-button secondary" href="/admin/renewal/products.php">상품 운영</a>' +
          '<a class="uno-admin-button secondary" href="' + escapeHtml(product.legacyEditHref || ("/admin/write.php?w=u&bo_table=product&wr_id=" + legacyProductId)) + '">기존 고급 수정</a>';
        semiCardEl.style.display = product.productType === "semi" ? "" : "none";
        dailyCardEl.style.display = product.productType === "daily" ? "" : "none";
        window.productHubSections = {
          basic: { title: "기본 정보", eyebrow: productTypeLabel, body: buildBasicEditor() },
          media: { title: "썸네일 / 이미지", eyebrow: "Media", body: buildMediaEditor() },
          pricing: { title: "요금 / 옵션", eyebrow: "Pricing", body: buildPricingEditor() },
          detail: { title: "상세 내용 / 코스", eyebrow: "Detail", body: buildDetailEditor() },
          semi: { title: "세미패키지 일정", eyebrow: "Boarding Pass Schedule", body: buildSemiScheduleEditor() },
          daily: { title: "데일리투어 캘린더", eyebrow: "Monthly Calendar", body: buildDailyCalendarEditor() },
          operation: { title: "운영 안내", eyebrow: "Operation Notes", body: buildOperationEditor() },
          audit: { title: "누락 검토", eyebrow: "Legacy Field Audit", body: buildAuditEditor() },
        };
      };

      const refreshCurrentModal = () => { renderProduct(); if (state.activeModal) openModal(state.activeModal); };
      const openModal = (key) => { const section = window.productHubSections && window.productHubSections[key]; if (!section) return; state.activeModal = key; modalTitleEl.textContent = section.title; modalEyebrowEl.textContent = section.eyebrow; modalBodyEl.innerHTML = key === "semi" ? buildSemiScheduleEditor() : key === "daily" ? buildDailyCalendarEditor() : section.body; if (key === "detail") { modalBodyEl.insertAdjacentHTML("beforeend", '<p class="uno-section-note" style="margin-top:16px;">상세페이지 바디 투어설명은 tourCourse.php의 v2_tourInfo 파일입니다. v2_course는 PRODUCT DOCUMENT 투어코스입니다.</p>' + mediaAudit(state.data.product || {}, "body")); } modalEl.classList.add("is-open"); modalEl.setAttribute("aria-hidden", "false"); };
      const closeModal = () => { modalEl.classList.remove("is-open"); modalEl.setAttribute("aria-hidden", "true"); };

      const uploadThumbnail = async (file) => {
        const formData = new FormData();
        formData.append("action", "uploadThumbnail");
        formData.append("thumbnail", file);
        const response = await fetch("/api/admin/product-editor.php?legacyProductId=" + encodeURIComponent(legacyProductId), {
          method: "POST",
          credentials: "same-origin",
          headers: { "X-CSRF-Token": getCookie("unotravel_csrf_token") },
          body: formData,
        });
        const json = await response.json();
        if (!json.ok) throw new Error(json.error ? json.error.message : "썸네일을 저장하지 못했습니다.");
        state.data = json.data;
      };

      document.addEventListener("click", async (event) => {
        const openButton = event.target.closest("[data-open-modal]");
        if (openButton) { openModal(openButton.dataset.openModal); return; }
        if (event.target.closest("[data-close-modal]") || event.target === modalEl) { closeModal(); return; }
        if (event.target.closest("[data-add-semi-schedule]")) { $("[data-semi-schedule-list]", modalBodyEl).insertAdjacentHTML("afterbegin", semiScheduleCard({})); return; }
        const saveBasic = event.target.closest("[data-save-basic]");
        if (saveBasic) { try { saveBasic.disabled = true; setStatus("기본 정보를 저장하는 중입니다."); await apiRequest(basicPayload()); setStatus("기본 정보가 저장되었습니다.", "ok"); refreshCurrentModal(); } catch (error) { setStatus(error.message || "기본 정보를 저장하지 못했습니다.", "warn"); } finally { saveBasic.disabled = false; } return; }
        const uploadButton = event.target.closest("[data-upload-thumbnail]");
        if (uploadButton) { const fileInput = $("[data-thumbnail-file]", modalBodyEl); const file = fileInput && fileInput.files ? fileInput.files[0] : null; if (!file) { setStatus("업로드할 썸네일 이미지를 선택해 주세요.", "warn"); return; } try { uploadButton.disabled = true; setStatus("썸네일을 저장하는 중입니다."); await uploadThumbnail(file); setStatus("썸네일이 저장되었습니다.", "ok"); refreshCurrentModal(); } catch (error) { setStatus(error.message || "썸네일을 저장하지 못했습니다.", "warn"); } finally { uploadButton.disabled = false; } return; }
        const saveOperation = event.target.closest("[data-save-operation]");
        if (saveOperation) { try { saveOperation.disabled = true; setStatus("운영 안내를 저장하는 중입니다."); await apiRequest(operationPayload()); setStatus("운영 안내가 저장되었습니다.", "ok"); refreshCurrentModal(); } catch (error) { setStatus(error.message || "운영 안내를 저장하지 못했습니다.", "warn"); } finally { saveOperation.disabled = false; } return; }
        const saveDetail = event.target.closest("[data-save-detail]");
        if (saveDetail) { try { saveDetail.disabled = true; setStatus("상세 내용을 저장하는 중입니다."); await apiRequest(detailPayload()); setStatus("상세 내용이 저장되었습니다.", "ok"); refreshCurrentModal(); } catch (error) { setStatus(error.message || "상세 내용을 저장하지 못했습니다.", "warn"); } finally { saveDetail.disabled = false; } return; }
        const saveAudit = event.target.closest("[data-save-audit]");
        if (saveAudit) { try { saveAudit.disabled = true; setStatus("누락 검토 항목을 저장하는 중입니다."); await apiRequest(auditPayload()); setStatus("누락 검토 항목이 저장되었습니다.", "ok"); refreshCurrentModal(); } catch (error) { setStatus(error.message || "누락 검토 항목을 저장하지 못했습니다.", "warn"); } finally { saveAudit.disabled = false; } return; }
        const savePricingMeta = event.target.closest("[data-save-pricing-meta]");
        if (savePricingMeta) { const box = savePricingMeta.closest("[data-pricing-meta]"); try { savePricingMeta.disabled = true; setStatus("가격 정보를 저장하는 중입니다."); await apiRequest({ action: "savePricingMeta", extras: { originalFeeText: readField(box, "originalFeeText"), priceDescription: readField(box, "priceDescription") } }); setStatus("가격 정보가 저장되었습니다.", "ok"); refreshCurrentModal(); } catch (error) { setStatus(error.message || "가격 정보를 저장하지 못했습니다.", "warn"); } finally { savePricingMeta.disabled = false; } return; }
        const saveDailyFee = event.target.closest("[data-save-daily-fee]");
        if (saveDailyFee) { const card = saveDailyFee.closest("[data-daily-fee-card]"); try { saveDailyFee.disabled = true; setStatus("요금 옵션을 저장하는 중입니다."); await apiRequest(dailyFeePayload(card)); setStatus("요금 옵션이 저장되었습니다.", "ok"); refreshCurrentModal(); } catch (error) { setStatus(error.message || "요금 옵션을 저장하지 못했습니다.", "warn"); } finally { saveDailyFee.disabled = false; } return; }
        const deleteDailyFee = event.target.closest("[data-delete-daily-fee]");
        if (deleteDailyFee) { const card = deleteDailyFee.closest("[data-daily-fee-card]"); const id = card.dataset.id ? Number(card.dataset.id) : 0; if (!id || !window.confirm("이 요금 옵션을 삭제할까요?")) return; try { deleteDailyFee.disabled = true; setStatus("요금 옵션을 삭제하는 중입니다."); await apiRequest({ action: "deleteDailyFeeOption", id }); setStatus("요금 옵션이 삭제되었습니다.", "ok"); refreshCurrentModal(); } catch (error) { setStatus(error.message || "요금 옵션을 삭제하지 못했습니다.", "warn"); } finally { deleteDailyFee.disabled = false; } return; }
        const saveSemiPrice = event.target.closest("[data-save-semi-price]");
        if (saveSemiPrice) { const card = saveSemiPrice.closest("[data-semi-price-card]"); try { saveSemiPrice.disabled = true; setStatus("세미패키지 가격을 저장하는 중입니다."); await apiRequest(semiPricePayload(card)); setStatus("세미패키지 가격이 저장되었습니다.", "ok"); refreshCurrentModal(); } catch (error) { setStatus(error.message || "세미패키지 가격을 저장하지 못했습니다.", "warn"); } finally { saveSemiPrice.disabled = false; } return; }
        const saveSemi = event.target.closest("[data-save-semi-schedule]");
        if (saveSemi) { const card = saveSemi.closest("[data-semi-schedule-card]"); try { saveSemi.disabled = true; setStatus("세미패키지 일정을 저장하는 중입니다."); await apiRequest(semiPayload(card)); setStatus("세미패키지 일정이 저장되었습니다.", "ok"); refreshCurrentModal(); } catch (error) { setStatus(error.message || "일정을 저장하지 못했습니다.", "warn"); } finally { saveSemi.disabled = false; } return; }
        const deleteSemi = event.target.closest("[data-delete-semi-schedule]");
        if (deleteSemi) { const card = deleteSemi.closest("[data-semi-schedule-card]"); const id = card.dataset.id ? Number(card.dataset.id) : 0; if (!id || !window.confirm("이 일정을 삭제할까요?")) return; try { deleteSemi.disabled = true; setStatus("세미패키지 일정을 삭제하는 중입니다."); await apiRequest({ action: "deleteSemiSchedule", id }); setStatus("세미패키지 일정이 삭제되었습니다.", "ok"); refreshCurrentModal(); } catch (error) { setStatus(error.message || "일정을 삭제하지 못했습니다.", "warn"); } finally { deleteSemi.disabled = false; } return; }
        if (event.target.closest("[data-daily-month-prev]")) { ensureDailyMonth(); state.dailyMonth = new Date(state.dailyMonth.getFullYear(), state.dailyMonth.getMonth() - 1, 1); openModal("daily"); return; }
        if (event.target.closest("[data-daily-month-next]")) { ensureDailyMonth(); state.dailyMonth = new Date(state.dailyMonth.getFullYear(), state.dailyMonth.getMonth() + 1, 1); openModal("daily"); return; }
        const closeDaily = event.target.closest("[data-close-daily-date]");
        if (closeDaily) { const cell = closeDaily.closest("[data-daily-date]"); try { closeDaily.disabled = true; setStatus("선택한 날짜를 마감 처리하는 중입니다."); await apiRequest(dailyPayload(cell, "soldout")); setStatus("선택한 날짜가 마감 처리되었습니다.", "ok"); refreshCurrentModal(); } catch (error) { setStatus(error.message || "날짜를 마감 처리하지 못했습니다.", "warn"); } finally { closeDaily.disabled = false; } return; }
        const applyPattern = event.target.closest("[data-apply-daily-pattern]");
        if (applyPattern) { try { applyPattern.disabled = true; setStatus("반복 캘린더를 적용하는 중입니다."); await apiRequest(patternPayload()); setStatus("반복 캘린더가 적용되었습니다.", "ok"); refreshCurrentModal(); } catch (error) { setStatus(error.message || "반복 캘린더를 적용하지 못했습니다.", "warn"); } finally { applyPattern.disabled = false; } }
      });

      document.addEventListener("input", (event) => {
        const guideSearch = event.target.closest("[data-guide-search]");
        if (!guideSearch) return;
        filterGuideSelector(guideSearch.closest("[data-guide-selector]"), guideSearch.value);
      });

      document.addEventListener("change", (event) => {
        const guideOption = event.target.closest("[data-guide-option]");
        if (guideOption) {
          updateGuideSelector(guideOption.closest("[data-guide-selector]"));
          return;
        }

        const fileInput = event.target.closest("[data-thumbnail-file]");
        if (!fileInput || !fileInput.files || !fileInput.files[0]) return;
        const file = fileInput.files[0];
        if (!file.type || file.type.indexOf("image/") !== 0) {
          setStatus("이미지 파일만 선택할 수 있습니다.", "warn");
          return;
        }
        const previewUrl = URL.createObjectURL(file);
        const preview = $("[data-thumbnail-preview]", modalBodyEl);
        if (!preview) return;
        if (preview.tagName === "IMG") {
          preview.src = previewUrl;
        } else {
          preview.outerHTML = '<img data-thumbnail-preview src="' + previewUrl + '" alt="">';
        }
      });

      document.addEventListener("keydown", (event) => { if (event.key === "Escape") closeModal(); });
      apiRequest().then(() => { renderProduct(); setStatus("상품 허브를 불러왔습니다.", "ok"); }).catch((error) => setStatus(error.message || "상품 정보를 불러오지 못했습니다.", "warn"));
    </script>

<?php
uno_renewal_admin_render_footer();
