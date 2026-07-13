<?php
/*
 * spam-moderation.php
 * Renewal admin page for reviewing community spam and suspicious members.
 * It surfaces suspicious inquiry posts, high-frequency writers, member intercept actions, and limited verified spam cleanup.
 * It keeps bulk deletion behind explicit confirmation so legacy Gnuboard board data is handled cautiously.
 */

require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

uno_renewal_admin_require_access('/admin/renewal/spam-moderation.php');

uno_renewal_admin_render_head('UNO Renewal Spam Moderation');
uno_renewal_admin_render_header();
uno_renewal_admin_render_pagehead(
    'UNO Travel Renewal Admin',
    'Spam<br>Control',
    '1:1 문의와 커뮤니티에 반복 등록되는 도박, 성인, 스캠성 게시물을 빠르게 확인하고 작성자 계정을 차단합니다. 글 일괄 삭제는 그누보드 삭제 흐름 확인 후 별도 단계로 분리합니다.',
    array(
        array('label' => '기존 1:1 문의', 'href' => '/admin/board.php?bo_table=cusTour', 'secondary' => true),
        array('label' => '회원 목록', 'href' => '/admin/renewal/members.php', 'secondary' => true),
    )
);
?>

    <style>
      .spam-filter { display: grid; grid-template-columns: 160px minmax(0, 1fr) minmax(0, 1fr) auto; gap: 10px; align-items: end; }
      .spam-filter label { display: grid; gap: 6px; color: var(--uno-muted); font-size: 11px; font-weight: 900; letter-spacing: 1.8px; text-transform: uppercase; }
      .spam-filter select,
      .spam-filter input { min-height: 42px; border: 1px solid var(--uno-line); background: #fff; padding: 0 12px; }
      .spam-layout { display: grid; grid-template-columns: 360px minmax(0, 1fr); gap: 14px; margin-top: 18px; }
      .spam-panel { border: 1px solid var(--uno-line); background: #fff; padding: 16px; }
      .spam-panel h3 { margin: 0 0 12px; font-size: 20px; }
      .spam-writer-list { display: grid; gap: 10px; }
      .spam-writer { border: 1px solid var(--uno-line); padding: 12px; }
      .spam-writer strong { display: block; font-size: 16px; }
      .spam-meta { margin-top: 5px; color: var(--uno-muted); font-size: 13px; line-height: 1.45; }
      .spam-posts { display: grid; gap: 10px; }
      .spam-post { border: 1px solid var(--uno-line); background: #fff; padding: 16px; }
      .spam-post-head { display: flex; justify-content: space-between; gap: 14px; align-items: flex-start; }
      .spam-post h3 { margin: 0; font-size: 19px; line-height: 1.3; }
      .spam-post p { margin: 10px 0 0; color: var(--uno-muted); line-height: 1.6; word-break: break-word; }
      .spam-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
      .spam-toolbar { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; margin-bottom: 10px; padding: 12px; border: 1px solid var(--uno-line); background: #fff; }
      .spam-toolbar strong { font-size: 14px; }
      .spam-check { display: inline-flex; gap: 8px; align-items: center; font-weight: 900; }
      .spam-check input { width: 18px; height: 18px; }
      .spam-badge { display: inline-flex; min-height: 28px; align-items: center; padding: 0 9px; border: 1px solid var(--uno-line); color: var(--uno-muted); font-size: 12px; font-weight: 900; }
      .spam-badge.warn { color: var(--uno-warn); border-color: rgba(154, 106, 0, .28); background: rgba(154, 106, 0, .07); }
      @media (max-width: 980px) {
        .spam-filter,
        .spam-layout { grid-template-columns: 1fr; }
      }
    </style>

    <section class="uno-admin-panel">
      <form class="spam-filter" data-spam-filter>
        <label>게시판
          <select name="board">
            <option value="cusTour">1:1 문의</option>
            <option value="qna">묻고답하기</option>
            <option value="write">여행후기</option>
          </select>
        </label>
        <label>키워드
          <input type="search" name="keyword" placeholder="비워두면 기본 스팸 키워드로 검색">
        </label>
        <label>회원 ID
          <input type="search" name="memberId" placeholder="특정 작성자 글만 보기">
        </label>
        <button class="uno-admin-button" type="submit">조회</button>
      </form>
      <p class="uno-admin-status" data-spam-status style="margin-top:12px;">스팸 의심 게시물을 불러오는 중입니다.</p>
    </section>

    <section class="spam-layout">
      <aside class="spam-panel">
        <h3>24시간 반복 작성자</h3>
        <div class="spam-writer-list" data-spam-writers></div>
      </aside>
      <div>
        <div class="spam-toolbar">
          <strong data-spam-selected>선택 0건</strong>
          <button class="uno-admin-button secondary" type="button" data-delete-selected>선택 글 삭제</button>
        </div>
        <div class="spam-posts" data-spam-posts></div>
      </div>
    </section>

    <script>
      const spamFilter = document.querySelector("[data-spam-filter]");
      const spamStatus = document.querySelector("[data-spam-status]");
      const spamWriters = document.querySelector("[data-spam-writers]");
      const spamPosts = document.querySelector("[data-spam-posts]");
      const spamSelected = document.querySelector("[data-spam-selected]");
      let spamState = { board: "cusTour", keyword: "", memberId: "" };

      const escapeSpam = (value) => String(value ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
      const getCookie = (name) => document.cookie.split(";").map((v) => v.trim()).find((v) => v.indexOf(name + "=") === 0)?.slice(name.length + 1) || "";
      const setSpamStatus = (message, tone = "") => {
        spamStatus.textContent = message;
        spamStatus.className = "uno-admin-status" + (tone ? " " + tone : "");
      };

      const spamRequest = async (params = {}, body = null) => {
        const query = new URLSearchParams({ ...spamState, ...params });
        const options = { credentials: "same-origin" };
        if (body) {
          options.method = "POST";
          options.headers = { "Content-Type": "application/json", "X-CSRF-Token": getCookie("unotravel_csrf_token") };
          options.body = JSON.stringify(body);
        }
        const response = await fetch("/api/admin/spam-moderation.php?" + query.toString(), options);
        const json = await response.json();
        if (!json.ok) throw new Error(json.error ? json.error.message : "스팸 관리 정보를 불러오지 못했습니다.");
        return json.data;
      };

      const renderWriters = (writers) => {
        spamWriters.innerHTML = writers.length ? writers.map((writer) => (
          '<article class="spam-writer">' +
            '<strong>' + escapeSpam(writer.memberId || writer.writer || "비회원") + '</strong>' +
            '<div class="spam-meta">최근 24시간 ' + escapeSpam(writer.count) + '건 · 마지막 ' + escapeSpam(writer.latest || "-") + '</div>' +
            (writer.interceptDate ? '<span class="spam-badge warn">차단됨 ' + escapeSpam(writer.interceptDate) + '</span>' : '') +
            '<div class="spam-actions">' +
              '<button class="uno-admin-button secondary" type="button" data-filter-member="' + escapeSpam(writer.memberId) + '">글 보기</button>' +
              (writer.memberId ? '<button class="uno-admin-button secondary" type="button" data-block-member="' + escapeSpam(writer.memberId) + '">회원 차단</button>' : '') +
              (writer.memberId ? '<button class="uno-admin-button" type="button" data-delete-member-posts="' + escapeSpam(writer.memberId) + '">작성자 글 일괄 삭제</button>' : '') +
            '</div>' +
          '</article>'
        )).join("") : '<p class="spam-meta">최근 24시간 반복 작성자가 없습니다.</p>';
      };

      const renderPosts = (posts) => {
        spamPosts.innerHTML = posts.length ? posts.map((post) => (
          '<article class="spam-post" data-post-card="' + escapeSpam(post.id) + '">' +
            '<div class="spam-post-head"><div><h3>' + escapeSpam(post.subject || "제목 없음") + '</h3><div class="spam-meta">' + escapeSpam(post.createdAt) + ' · ' + escapeSpam(post.memberId || post.writer || "비회원") + ' · IP ' + escapeSpam(post.ip || "-") + '</div></div>' +
            (post.member?.interceptDate ? '<span class="spam-badge warn">회원 차단됨</span>' : '<span class="spam-badge">검토 필요</span>') + '</div>' +
            '<p>' + escapeSpam(post.excerpt || "") + '</p>' +
            '<div class="spam-actions">' +
              '<label class="spam-check"><input type="checkbox" data-select-post value="' + escapeSpam(post.id) + '"> 선택</label>' +
              '<a class="uno-admin-button secondary" href="' + escapeSpam(post.links?.post || "#") + '">게시글 보기</a>' +
              (post.links?.member ? '<a class="uno-admin-button secondary" href="' + escapeSpam(post.links.member) + '">회원 보기</a>' : '') +
              (post.memberId ? '<button class="uno-admin-button" type="button" data-block-member="' + escapeSpam(post.memberId) + '">작성자 차단</button>' : '') +
              (post.memberId ? '<button class="uno-admin-button secondary" type="button" data-delete-member-posts="' + escapeSpam(post.memberId) + '">작성자 글 일괄 삭제</button>' : '') +
            '</div>' +
          '</article>'
        )).join("") : '<article class="spam-post"><h3>검색 결과 없음</h3><p>조건에 맞는 스팸 의심 게시물이 없습니다.</p></article>';
        updateSelectedCount();
      };

      const selectedPostIds = () => Array.from(document.querySelectorAll("[data-select-post]:checked")).map((input) => Number(input.value)).filter(Boolean);
      const updateSelectedCount = () => { spamSelected.textContent = "선택 " + selectedPostIds().length + "건"; };

      const loadSpam = async () => {
        try {
          setSpamStatus("스팸 의심 게시물을 불러오는 중입니다.");
          const data = await spamRequest();
          renderWriters(data.writers || []);
          renderPosts(data.posts || []);
          setSpamStatus("스팸 의심 게시물을 불러왔습니다.", "ok");
        } catch (error) {
          setSpamStatus(error.message || "스팸 의심 게시물을 불러오지 못했습니다.", "warn");
        }
      };

      spamFilter.addEventListener("submit", (event) => {
        event.preventDefault();
        const formData = new FormData(spamFilter);
        spamState = {
          board: String(formData.get("board") || "cusTour"),
          keyword: String(formData.get("keyword") || ""),
          memberId: String(formData.get("memberId") || ""),
        };
        loadSpam();
      });

      document.addEventListener("click", async (event) => {
        const filterButton = event.target.closest("[data-filter-member]");
        if (filterButton) {
          spamFilter.elements.memberId.value = filterButton.dataset.filterMember || "";
          spamState.memberId = filterButton.dataset.filterMember || "";
          loadSpam();
          return;
        }

        const blockButton = event.target.closest("[data-block-member]");
        if (blockButton) {
          const memberId = blockButton.dataset.blockMember || "";
          if (!memberId || !window.confirm(memberId + " 회원을 차단할까요?")) return;
          try {
            blockButton.disabled = true;
            setSpamStatus("회원을 차단하는 중입니다.");
            await spamRequest({}, { action: "blockMember", memberId });
            setSpamStatus("회원이 차단되었습니다.", "ok");
            loadSpam();
          } catch (error) {
            setSpamStatus(error.message || "회원을 차단하지 못했습니다.", "warn");
          } finally {
            blockButton.disabled = false;
          }
          return;
        }

        const deleteSelectedButton = event.target.closest("[data-delete-selected]");
        if (deleteSelectedButton) {
          const postIds = selectedPostIds();
          if (!postIds.length) {
            setSpamStatus("삭제할 게시글을 먼저 선택해 주세요.", "warn");
            return;
          }
          if (!window.confirm("선택한 게시글 " + postIds.length + "건을 삭제할까요? 첨부파일과 최신글 기록도 함께 정리됩니다.")) return;
          try {
            deleteSelectedButton.disabled = true;
            setSpamStatus("선택 게시글을 삭제하는 중입니다.");
            const data = await spamRequest({}, { action: "deleteSelectedPosts", board: spamState.board, postIds });
            const mutation = data.mutation || {};
            setSpamStatus("게시글 " + (mutation.deleted || 0) + "건을 삭제했습니다." + ((mutation.skipped || []).length ? " 일부 글은 답글 연결 때문에 건너뛰었습니다." : ""), "ok");
            loadSpam();
          } catch (error) {
            setSpamStatus(error.message || "선택 게시글을 삭제하지 못했습니다.", "warn");
          } finally {
            deleteSelectedButton.disabled = false;
          }
          return;
        }

        const deleteMemberButton = event.target.closest("[data-delete-member-posts]");
        if (deleteMemberButton) {
          const memberId = deleteMemberButton.dataset.deleteMemberPosts || "";
          if (!memberId) return;
          const confirmId = window.prompt(memberId + " 작성자의 최근 스팸 의심 글을 최대 100건 삭제합니다. 진행하려면 회원 ID를 그대로 입력해 주세요.");
          if (confirmId !== memberId) {
            setSpamStatus("작성자 일괄 삭제가 취소되었습니다.", "warn");
            return;
          }
          try {
            deleteMemberButton.disabled = true;
            setSpamStatus("작성자 글을 일괄 삭제하는 중입니다.");
            const data = await spamRequest({}, { action: "deleteMemberPosts", board: spamState.board, memberId, confirm: confirmId });
            const mutation = data.mutation || {};
            setSpamStatus(memberId + " 작성자의 게시글 " + (mutation.deleted || 0) + "건을 삭제했습니다." + ((mutation.skipped || []).length ? " 일부 글은 답글 연결 때문에 건너뛰었습니다." : ""), "ok");
            loadSpam();
          } catch (error) {
            setSpamStatus(error.message || "작성자 글을 일괄 삭제하지 못했습니다.", "warn");
          } finally {
            deleteMemberButton.disabled = false;
          }
        }
      });

      document.addEventListener("change", (event) => {
        if (event.target.closest("[data-select-post]")) updateSelectedCount();
      });

      loadSpam();
    </script>

<?php
uno_renewal_admin_render_footer();
