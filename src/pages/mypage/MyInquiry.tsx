// MyInquiry.tsx
// 마이페이지의 개인 1:1 문의 작성/조회 화면입니다.
// 기존 Gnuboard cusTour 게시판의 원글과 댓글 구조를 대화형 UI로 보여줍니다.
// 커뮤니티 공개 문의(qna)와 분리된 개인 상담 채널 역할만 담당합니다.

import { useEffect, useRef, useState, type FormEvent, type ReactNode } from "react";

import { UnoApiRequestError } from "../../api/apiClient";
import {
  createInquiry,
  getMyInquiryThread,
  type InquiryMessage,
  type InquiryThread,
} from "../../api/reservationApi";

const FONT_EN = "var(--font-en)";
const FONT_KO = "var(--font-ko)";
const BLACK = "#151515";
const BORDER = "#E8E9E9";
const YELLOW = "#FCC800";
const MAX_ATTACHMENT_SIZE = 10 * 1024 * 1024;
const ALLOWED_ATTACHMENT_EXTENSIONS = [
  "jpg",
  "jpeg",
  "png",
  "pdf",
  "doc",
  "docx",
  "xls",
  "xlsx",
];

function navigateTo(path: string) {
  if (typeof window === "undefined") return;

  if (window.location.pathname === path) {
    window.scrollTo({ top: 0, behavior: "smooth" });
    return;
  }

  window.history.pushState({}, "", path);
  window.dispatchEvent(new Event("unotravel:navigate"));
}

function getUserName() {
  if (typeof window === "undefined") return "회원";

  try {
    const raw = sessionStorage.getItem("unotravel:user");
    const parsed = raw ? JSON.parse(raw) : null;
    return parsed?.name || parsed?.userName || parsed?.mb_name || "회원";
  } catch {
    return "회원";
  }
}

function logout() {
  if (typeof window === "undefined") return;

  sessionStorage.removeItem("unotravel:user");
  sessionStorage.removeItem("unotravel:auth");
  sessionStorage.removeItem("unotravel:user-name");
  sessionStorage.removeItem("unotravel:user-email");
  window.dispatchEvent(new Event("unotravel:auth-change"));
  navigateTo("/");
}

const MY_MENU = [
  { label: "마이페이지", path: "/mypage" },
  { label: "장바구니", path: "/mypage/cart" },
  { label: "예약목록", path: "/mypage/reservations" },
  { label: "1:1 문의하기", path: "/mypage/inquiry" },
  { label: "개인정보 수정", path: "/mypage/profile" },
  { label: "투어 신청 / 예약 전환", path: "/mypage/tour" },
];

function MyPageLayout({
  title,
  eyebrow,
  description,
  activePath,
  children,
}: {
  title: string;
  eyebrow: string;
  description: string;
  activePath: string;
  children: ReactNode;
}) {
  const userName = getUserName();

  return (
    <main className="mypage-shell">
      <style>{STYLE}</style>
      <section className="mypage-inner" aria-label={title}>
        <aside className="mypage-side">
          <div className="mypage-side-kicker">MY PAGE</div>
          <h1>{userName}님</h1>
          <p>예약과 문의, 회원 정보를 한 곳에서 관리합니다.</p>

          <nav className="mypage-nav" aria-label="마이페이지 메뉴">
            {MY_MENU.map((item) => (
              <button
                key={item.path}
                type="button"
                className={activePath === item.path ? "is-active" : ""}
                onClick={() => navigateTo(item.path)}
              >
                <span>{item.label}</span>
                <span aria-hidden="true">›</span>
              </button>
            ))}
          </nav>

          <button type="button" className="mypage-logout" onClick={logout}>
            로그아웃
          </button>
        </aside>

        <div className="mypage-content">
          <div className="mypage-heading">
            <span>{eyebrow}</span>
            <h2>{title}</h2>
            <p>{description}</p>
          </div>
          {children}
        </div>
      </section>
    </main>
  );
}

const STYLE = `
  .mypage-shell {
    width: 100%;
    min-width: 1024px;
    min-height: calc(100vh - 110px);
    background: #fff;
    color: ${BLACK};
    overflow-x: hidden;
  }

  .mypage-inner {
    width: 100%;
    min-height: 780px;
    padding: 150px 54px 80px;
    box-sizing: border-box;
    display: grid;
    grid-template-columns: 300px minmax(0, 1fr);
    gap: 54px;
  }

  .mypage-side {
    position: sticky;
    top: 132px;
    align-self: start;
    border: 2px solid ${BORDER};
    border-radius: 22px;
    padding: 28px 24px 24px;
    box-sizing: border-box;
    background: #fff;
  }

  .mypage-side-kicker,
  .mypage-heading span,
  .card-kicker,
  .inquiry-thread-kicker {
    font-family: ${FONT_EN};
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.12em;
    color: rgba(21,21,21,.48);
  }

  .mypage-side h1 {
    margin: 18px 0 10px;
    font-family: ${FONT_KO};
    font-size: 28px;
    line-height: 1.05;
    letter-spacing: -0.04em;
  }

  .mypage-side p {
    margin: 0 0 28px;
    font-family: ${FONT_KO};
    font-size: 13px;
    font-weight: 600;
    line-height: 1.55;
    letter-spacing: -0.02em;
    color: rgba(21,21,21,.58);
    word-break: keep-all;
  }

  .mypage-nav {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .mypage-nav button,
  .mypage-logout,
  .primary-action,
  .ghost-action {
    border: none;
    background: transparent;
    cursor: pointer;
    font-family: ${FONT_KO};
  }

  .primary-action:disabled,
  .ghost-action:disabled {
    cursor: not-allowed;
    opacity: .44;
  }

  .mypage-nav button {
    height: 42px;
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    padding: 0 12px;
    border-radius: 14px;
    color: ${BLACK};
    font-size: 14px;
    font-weight: 800;
    letter-spacing: -0.02em;
    text-align: left;
    transition: background .22s ease, transform .22s ease;
  }

  .mypage-nav button:hover,
  .mypage-nav button.is-active {
    background: rgba(252,200,0,.18);
    transform: translateY(-1px);
  }

  .mypage-logout {
    width: 100%;
    height: 42px;
    margin-top: 18px;
    border-top: 1px solid ${BORDER};
    padding-top: 18px;
    color: rgba(21,21,21,.58);
    font-size: 13px;
    font-weight: 800;
    letter-spacing: -0.02em;
    text-align: left;
  }

  .mypage-logout:hover { color: ${BLACK}; }
  .mypage-content { min-width: 0; }

  .mypage-heading {
    min-height: 186px;
    border-bottom: 2px solid ${BLACK};
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding-bottom: 34px;
    box-sizing: border-box;
  }

  .mypage-heading h2 {
    margin: 18px 0 16px;
    font-family: ${FONT_EN};
    font-size: 88px;
    font-weight: 400;
    line-height: .82;
    letter-spacing: -0.04em;
  }

  .mypage-heading p {
    margin: 0;
    max-width: 560px;
    font-family: ${FONT_KO};
    font-size: 17px;
    font-weight: 650;
    line-height: 1.6;
    letter-spacing: -0.02em;
    color: rgba(21,21,21,.68);
    word-break: keep-all;
  }

  .grid { display: grid; gap: 16px; margin-top: 30px; }
  .grid.two { grid-template-columns: repeat(2, minmax(0,1fr)); }

  .card {
    border: 1px solid ${BORDER};
    border-radius: 18px;
    background: #fff;
    padding: 24px;
    box-sizing: border-box;
    min-height: 150px;
  }

  .card h3 {
    margin: 14px 0 10px;
    font-family: ${FONT_KO};
    font-size: 22px;
    line-height: 1.15;
    letter-spacing: -0.03em;
  }

  .card p, .notice-box p, .field span {
    margin: 0;
    font-family: ${FONT_KO};
    font-size: 14px;
    font-weight: 600;
    line-height: 1.55;
    letter-spacing: -0.02em;
    color: rgba(21,21,21,.62);
    word-break: keep-all;
  }

  .inquiry-thread {
    margin-top: 30px;
    border: 1px solid ${BORDER};
    border-radius: 18px;
    background: #fff;
    overflow: hidden;
  }

  .inquiry-thread-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    padding: 24px;
    border-bottom: 1px solid ${BORDER};
  }

  .inquiry-thread-head h3 {
    margin: 10px 0 0;
    font-family: ${FONT_KO};
    font-size: 22px;
    letter-spacing: -0.03em;
  }

  .inquiry-thread-date {
    margin: 0;
    color: rgba(21,21,21,.52);
    font-family: ${FONT_EN};
    font-size: 12px;
    font-weight: 700;
  }

  .inquiry-messages {
    display: flex;
    flex-direction: column;
    gap: 14px;
    min-height: 180px;
    max-height: 430px;
    padding: 24px;
    overflow: auto;
    background: rgba(21,21,21,.025);
  }

  .inquiry-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 160px;
    color: rgba(21,21,21,.54);
    font-family: ${FONT_KO};
    font-size: 15px;
    font-weight: 700;
    text-align: center;
  }

  .inquiry-message {
    width: min(72%, 620px);
    border: 1px solid rgba(21,21,21,.1);
    border-radius: 18px;
    padding: 16px 18px;
    background: #fff;
  }

  .inquiry-message.is-user {
    align-self: flex-end;
    background: ${YELLOW};
    border-color: rgba(21,21,21,.08);
  }

  .inquiry-message.is-admin {
    align-self: flex-start;
  }

  .inquiry-message-meta {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 8px;
    color: rgba(21,21,21,.54);
    font-family: ${FONT_EN};
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .06em;
  }

  .inquiry-message p {
    margin: 0;
    white-space: pre-wrap;
    word-break: keep-all;
    color: ${BLACK};
    font-family: ${FONT_KO};
    font-size: 14px;
    font-weight: 650;
    line-height: 1.7;
    letter-spacing: -0.02em;
  }

  .primary-action, .ghost-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    border-radius: 999px;
    padding: 0 18px;
    font-size: 13px;
    font-weight: 850;
    letter-spacing: -0.02em;
  }

  .primary-action { background: ${YELLOW}; color: ${BLACK}; }
  .ghost-action { border: 1px solid ${BORDER}; color: ${BLACK}; }

  .notice-box {
    margin-top: 24px;
    padding: 20px 22px;
    border-radius: 18px;
    background: rgba(21,21,21,.035);
  }

  .form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    gap: 16px;
    margin-top: 30px;
  }

  .field {
    display: flex;
    flex-direction: column;
    gap: 9px;
  }

  .field textarea {
    width: 100%;
    min-height: 150px;
    border: 1px solid rgba(21,21,21,.16);
    border-radius: 16px;
    background: #fff;
    padding: 16px;
    box-sizing: border-box;
    font-family: ${FONT_KO};
    font-size: 14px;
    font-weight: 650;
    line-height: 1.6;
    outline: none;
    resize: vertical;
  }

  .file-input-native {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0 0 0 0);
    white-space: nowrap;
  }

  .file-picker-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
  }

  .file-picker-button,
  .file-remove-button {
    border: 1px solid ${BORDER};
    background: #fff;
    color: ${BLACK};
    cursor: pointer;
    font-family: ${FONT_KO};
    font-size: 13px;
    font-weight: 850;
    letter-spacing: -0.02em;
  }

  .message-attachments {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
  }

  .message-attachment-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 32px;
    max-width: 100%;
    border: 1px solid rgba(21,21,21,.16);
    border-radius: 999px;
    background: rgba(255,255,255,.7);
    color: ${BLACK};
    padding: 0 11px;
    box-sizing: border-box;
    font-family: ${FONT_KO};
    font-size: 12px;
    font-weight: 800;
    text-decoration: none;
  }

  .message-attachment-link span {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .message-attachment-link small {
    flex: 0 0 auto;
    color: rgba(21,21,21,.54);
    font-family: ${FONT_EN};
    font-size: 11px;
    font-weight: 800;
  }

  .file-picker-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    border-radius: 999px;
    padding: 0 18px;
  }

  .file-name {
    max-width: 420px;
    color: ${BLACK};
    font-family: ${FONT_KO};
    font-size: 13px;
    font-weight: 750;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .file-remove-button {
    min-height: 34px;
    border-radius: 999px;
    padding: 0 12px;
  }

  .field-help {
    font-family: ${FONT_KO};
    font-size: 12px;
    font-weight: 650;
    line-height: 1.5;
    color: rgba(21,21,21,.52);
  }

  .field.full { grid-column: 1 / -1; }

  .actions {
    display: flex;
    gap: 10px;
    margin-top: 24px;
  }

  .inquiry-status {
    margin: 18px 0 0;
    border: 1px solid rgba(21,21,21,.12);
    border-radius: 16px;
    padding: 14px 16px;
    font-family: ${FONT_KO};
    font-size: 14px;
    font-weight: 700;
    line-height: 1.5;
    letter-spacing: -0.02em;
  }

  .inquiry-status.is-success {
    border-color: rgba(28, 128, 92, .22);
    background: rgba(28, 128, 92, .08);
    color: #166748;
  }

  .inquiry-status.is-error {
    border-color: rgba(190, 40, 40, .22);
    background: rgba(190, 40, 40, .08);
    color: #8f1f1f;
  }

  @media (max-width: 1180px) {
    .mypage-inner {
      grid-template-columns: 260px minmax(0,1fr);
      gap: 32px;
      padding-left: 38px;
      padding-right: 38px;
    }

    .mypage-heading h2 { font-size: 72px; }
  }
`;

function formatInquiryDate(value?: string) {
  if (!value) return "";

  const date = new Date(value.replace(" ", "T"));
  if (Number.isNaN(date.getTime())) return value;

  return new Intl.DateTimeFormat("ko-KR", {
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    hour: "2-digit",
    minute: "2-digit",
  }).format(date);
}

function validateAttachment(file: File) {
  const extension = file.name.split(".").pop()?.toLowerCase() ?? "";

  if (!ALLOWED_ATTACHMENT_EXTENSIONS.includes(extension)) {
    return "첨부파일은 jpg, png, pdf, doc, docx, xls, xlsx 형식만 가능합니다.";
  }

  if (file.size <= 0 || file.size > MAX_ATTACHMENT_SIZE) {
    return "첨부파일은 10MB 이하만 가능합니다.";
  }

  return "";
}

function formatAttachmentSize(size: number) {
  if (size >= 1024 * 1024) {
    return `${(size / 1024 / 1024).toFixed(1)}MB`;
  }

  if (size >= 1024) {
    return `${(size / 1024).toFixed(1)}KB`;
  }

  return `${Math.max(0, size)}B`;
}

function getAttachmentExtension(fileName: string) {
  return fileName.split(".").pop()?.toLowerCase() ?? "";
}

function isViewableAttachment(fileName: string) {
  return ["jpg", "jpeg", "png", "pdf"].includes(getAttachmentExtension(fileName));
}

function InquiryThreadView({
  thread,
  messages,
  isLoading,
}: {
  thread: InquiryThread | null;
  messages: InquiryMessage[];
  isLoading: boolean;
}) {
  return (
    <section className="inquiry-thread" aria-label="1:1 문의 대화 내역">
      <div className="inquiry-thread-head">
        <div>
          <span className="inquiry-thread-kicker">MESSAGE HISTORY</span>
          <h3>{thread?.subject ?? "아직 접수된 문의가 없습니다"}</h3>
        </div>
        {thread?.updatedAt && (
          <p className="inquiry-thread-date">{formatInquiryDate(thread.updatedAt)}</p>
        )}
      </div>

      <div className="inquiry-messages">
        {isLoading ? (
          <div className="inquiry-empty">문의 내역을 불러오는 중입니다.</div>
        ) : messages.length === 0 ? (
          <div className="inquiry-empty">
            첫 문의를 남기면 이곳에서 답변과 대화 내역을 확인할 수 있습니다.
          </div>
        ) : (
          messages.map((message) => (
            <article
              key={message.id}
              className={`inquiry-message is-${message.role}`}
            >
              <div className="inquiry-message-meta">
                <span>{message.role === "admin" ? "UNO TRAVEL" : "ME"}</span>
                <span>{formatInquiryDate(message.createdAt)}</span>
              </div>
              <p>{message.content}</p>
              {message.attachments && message.attachments.length > 0 && (
                <div className="message-attachments">
                  {message.attachments.map((file) => (
                    <a
                      key={`${message.id}-${file.no}`}
                      className="message-attachment-link"
                      href={file.url}
                      target="_blank"
                      rel="noopener"
                    >
                      <span>{file.source || "첨부파일"}</span>
                      <small>
                        {formatAttachmentSize(file.size)} · {isViewableAttachment(file.source) ? "보기" : "다운로드"}
                      </small>
                    </a>
                  ))}
                </div>
              )}
            </article>
          ))
        )}
      </div>
    </section>
  );
}

export default function MyInquiry() {
  const fileInputRef = useRef<HTMLInputElement | null>(null);
  const [content, setContent] = useState("");
  const [attachment, setAttachment] = useState<File | null>(null);
  const [thread, setThread] = useState<InquiryThread | null>(null);
  const [messages, setMessages] = useState<InquiryMessage[]>([]);
  const [status, setStatus] = useState<{
    type: "success" | "error";
    message: string;
  } | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const loadInquiryThread = async () => {
    setIsLoading(true);

    try {
      const response = await getMyInquiryThread();
      setThread(response.thread);
      setMessages(response.messages);
    } catch (error) {
      const message =
        error instanceof UnoApiRequestError
          ? error.response.error.message
          : "문의 내역을 불러오지 못했습니다.";
      setStatus({ type: "error", message });
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    void loadInquiryThread();
  }, []);

  const handleSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    const trimmedContent = content.trim();
    if (trimmedContent.length < 20) {
      setStatus({
        type: "error",
        message: "문의 내용은 20자 이상 입력해 주세요.",
      });
      return;
    }

    setIsSubmitting(true);
    setStatus(null);

    try {
      const response = await createInquiry({
        content: trimmedContent,
        attachment,
      });
      setContent("");
      setAttachment(null);
      if (fileInputRef.current) {
        fileInputRef.current.value = "";
      }
      await loadInquiryThread();
      setStatus({
        type: "success",
        message: response.isNewThread
          ? `문의가 접수되었습니다. 접수번호: ${response.threadId}`
          : `문의가 추가되었습니다. 메시지 번호: ${response.messageId}`,
      });
    } catch (error) {
      const message =
        error instanceof UnoApiRequestError
          ? error.response.error.message
          : "문의 접수 중 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.";

      setStatus({ type: "error", message });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <MyPageLayout
      title="1:1 문의하기"
      eyebrow="INQUIRY"
      description="예약 변경, 취소, 상품 상담처럼 개인 확인이 필요한 내용을 우노트래블에 남길 수 있습니다."
      activePath="/mypage/inquiry"
    >
      <div className="grid two">
        <article className="card">
          <div className="card-kicker">NOTICE</div>
          <h3>예약 정보 확인</h3>
          <p>예약 변경이나 취소 문의는 상품명과 출발일을 함께 남겨 주세요.</p>
        </article>
        <article className="card">
          <div className="card-kicker">PRIVATE</div>
          <h3>개인 상담 전용</h3>
          <p>마이페이지 1:1 문의는 예약, 환불, 결제처럼 개인 확인이 필요한 상담만 접수합니다.</p>
        </article>
      </div>

      <InquiryThreadView thread={thread} messages={messages} isLoading={isLoading} />

      <form onSubmit={handleSubmit}>
        <div className="form-grid">
          <label className="field full">
            <span>{thread ? "추가 문의" : "문의 내용"}</span>
            <textarea
              value={content}
              placeholder="궁금한 내용을 입력해 주세요. 예약 관련 문의라면 상품명, 출발일, 예약자명을 함께 적어 주세요."
              onChange={(event) => {
                setContent(event.target.value);
                if (status) setStatus(null);
              }}
            />
          </label>
          <label className="field full">
            <span>첨부파일</span>
            <input
              ref={fileInputRef}
              id="my-inquiry-attachment"
              className="file-input-native"
              type="file"
              accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx"
              onChange={(event) => {
                const file = event.target.files?.[0] ?? null;
                if (!file) {
                  setAttachment(null);
                  return;
                }

                const validationMessage = validateAttachment(file);
                if (validationMessage) {
                  setAttachment(null);
                  event.currentTarget.value = "";
                  setStatus({ type: "error", message: validationMessage });
                  return;
                }

                setAttachment(file);
                if (status) setStatus(null);
              }}
            />
            <div className="file-picker-row">
              <label className="file-picker-button" htmlFor="my-inquiry-attachment">
                첨부파일 선택
              </label>
              {attachment && (
                <>
                  <span className="file-name">
                    {attachment.name} ({formatAttachmentSize(attachment.size)})
                  </span>
                  <button
                    className="file-remove-button"
                    type="button"
                    onClick={() => {
                      setAttachment(null);
                      if (fileInputRef.current) {
                        fileInputRef.current.value = "";
                      }
                    }}
                  >
                    삭제
                  </button>
                </>
              )}
            </div>
            <span className="field-help">
              JPG, PNG, PDF, DOC, DOCX, XLS, XLSX / 최대 10MB / 1개
            </span>
          </label>
        </div>

        <div className="actions">
          <button className="primary-action" type="submit" disabled={isSubmitting}>
            {isSubmitting ? "접수 중" : thread ? "추가 문의 보내기" : "문의 보내기"}
          </button>
        </div>
      </form>

      {status && (
        <p className={`inquiry-status is-${status.type}`} role="status">
          {status.message}
        </p>
      )}

      <div className="notice-box">
        <p>접수된 문의는 기존 우노트래블 관리자 1:1 문의 게시판과 연결됩니다.</p>
      </div>
    </MyPageLayout>
  );
}
