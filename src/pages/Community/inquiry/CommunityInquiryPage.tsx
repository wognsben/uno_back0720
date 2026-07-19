// CommunityInquiryPage.tsx
// 커뮤니티의 공개 문의 화면입니다.
// 마이페이지 1:1 문의(cusTour)와 섞이지 않도록 공개 Q&A(qna) 작성 상태만 관리합니다.
// 로그인 안내, 공개 문의 작성 폼, 개인 상담 이동 액션을 담당합니다.

// Public community Q&A writer for the legacy qna board.
// This is intentionally separate from My Page private 1:1 inquiry (cusTour).

import { useState, type FormEvent } from "react";

import { UnoApiRequestError } from "../../../api/apiClient";
import { isLocalAuthSessionActive } from "../../../api/authSession";
import {
  createCommunityInquiry,
} from "../../../api/reservationApi";
import CommunityLayout from "../CommunityLayout";
import CommunityList from "../CommunityList";
import CommunityPagination from "../CommunityPagination";
import CommunitySearch from "../CommunitySearch";
import { useCommunityPosts } from "../useCommunityPosts";

const LOGIN_PATH = "/login";
const MY_INQUIRY_PATH = "/mypage/inquiry";

function navigateTo(path: string) {
  if (typeof window === "undefined") return;

  if (window.location.pathname === path) {
    window.scrollTo({ top: 0, behavior: "smooth" });
    return;
  }

  window.history.pushState({}, "", path);
  window.dispatchEvent(new Event("unotravel:navigate"));
}

export default function CommunityInquiryPage() {
  const [isLoginModalOpen, setIsLoginModalOpen] = useState(false);
  const [subject, setSubject] = useState("");
  const [content, setContent] = useState("");
  const [status, setStatus] = useState<{
    type: "success" | "error";
    message: string;
  } | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const {
    items,
    page,
    search,
    totalPages,
    setPage,
    handleSearch,
    reload,
  } = useCommunityPosts("qna");

  const openMyInquiry = () => {
    navigateTo(MY_INQUIRY_PATH);
  };

  const openLogin = () => {
    if (typeof window !== "undefined") {
      window.sessionStorage.setItem("unotravel:redirect-after-login", "/community/inquiry");
    }

    navigateTo(LOGIN_PATH);
  };

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (!isLocalAuthSessionActive()) {
      setIsLoginModalOpen(true);
      return;
    }

    const trimmedSubject = subject.trim();
    const trimmedContent = content.trim();

    if (trimmedSubject.length < 2) {
      setStatus({ type: "error", message: "문의 제목을 입력해 주세요." });
      return;
    }

    if (trimmedContent.length < 20) {
      setStatus({ type: "error", message: "문의 내용은 20자 이상 입력해 주세요." });
      return;
    }

    setIsSubmitting(true);
    setStatus(null);

    try {
      await createCommunityInquiry({
        subject: trimmedSubject,
        content: trimmedContent,
      });
      setSubject("");
      setContent("");
      setPage(1);
      reload();
      setStatus({
        type: "success",
        message: "공개 문의가 등록되었습니다. 답변은 커뮤니티 문의 게시판에서 확인할 수 있습니다.",
      });
    } catch (error) {
      const message =
        error instanceof UnoApiRequestError
          ? error.response.error.message
          : "공개 문의 등록 중 문제가 발생했습니다.";
      setStatus({ type: "error", message });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <CommunityLayout type="inquiry">
      <section className="community-inquiry">
        <div className="community-inquiry-intro">
          <span>INQUIRY CHANNEL</span>
          <h2>문의 성격에 맞는 채널을 선택해 주세요</h2>
          <p>
            여행 준비, 일정, 상품에 대한 일반 질문은 공개 문의로 남길 수 있습니다. 예약 변경,
            결제, 환불, 개인정보가 포함된 상담은 마이페이지 1:1 문의를 이용해 주세요.
          </p>
        </div>

        <div className="community-inquiry-grid">
          <article className="community-inquiry-card community-inquiry-form-card">
            <span>PUBLIC Q&A</span>
            <h3>커뮤니티 공개 문의</h3>
            <p>
              다른 여행자도 함께 참고할 수 있는 공개 질문입니다. 개인정보, 예약번호, 연락처는
              입력하지 않는 것을 권장합니다.
            </p>

            <form className="community-inquiry-form" onSubmit={handleSubmit}>
              <label>
                <span>문의 제목</span>
                <input
                  value={subject}
                  onChange={(event) => setSubject(event.target.value)}
                  placeholder="예: 남부투어 집결 시간 문의"
                />
              </label>
              <label>
                <span>문의 내용</span>
                <textarea
                  value={content}
                  onChange={(event) => setContent(event.target.value)}
                  placeholder="공개 문의로 남길 내용을 입력해 주세요."
                  rows={7}
                />
              </label>
              {status && (
                <p className={`community-inquiry-status is-${status.type}`}>
                  {status.message}
                </p>
              )}
              <button type="submit" disabled={isSubmitting}>
                {isSubmitting ? "등록 중" : "공개 문의 등록"}
              </button>
            </form>
          </article>

          <article className="community-inquiry-card is-private">
            <span>PRIVATE SUPPORT</span>
            <h3>마이페이지 1:1 문의</h3>
            <p>
              예약, 환불, 결제, 회원정보처럼 개인 확인이 필요한 상담은 1:1 문의 내역으로
              접수됩니다. 본인 문의와 답변만 마이페이지에서 확인할 수 있습니다.
            </p>
            <button type="button" onClick={openMyInquiry}>
              1:1 문의로 이동
            </button>
          </article>
        </div>
      </section>

      <CommunitySearch
        placeholder="공개 문의를 검색하세요."
        value={search}
        onSearch={handleSearch}
      />
      <CommunityList type="inquiry" items={items} />
      <CommunityPagination
        currentPage={page}
        totalPages={totalPages}
        onPageChange={setPage}
      />

      {isLoginModalOpen && (
        <div
          className="community-login-backdrop"
          role="presentation"
          onClick={() => setIsLoginModalOpen(false)}
        >
          <div
            className="community-login-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="community-inquiry-login-title"
            onClick={(event) => event.stopPropagation()}
          >
            <span>LOGIN REQUIRED</span>
            <h2 id="community-inquiry-login-title">로그인이 필요합니다</h2>
            <p>
              공개 문의를 작성하려면 로그인이 필요합니다. 로그인 후 커뮤니티 문의 화면으로
              돌아올 수 있습니다.
            </p>
            <div className="community-login-actions">
              <button type="button" onClick={() => setIsLoginModalOpen(false)}>
                계속 보기
              </button>
              <button type="button" onClick={openLogin}>
                로그인 화면으로 이동
              </button>
            </div>
          </div>
        </div>
      )}
    </CommunityLayout>
  );
}
