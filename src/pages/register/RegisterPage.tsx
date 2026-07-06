import { useState } from "react";

/* ==========================================================
   RegisterPage.tsx

   UNOTRAVEL Register Main Page

   사용 페이지
   - /register

   백엔드 연동
   ------------------------------------------
   register start       ← 기존 regis_agree.php 흐름 대응
   agreement            ← 약관 동의 페이지 이동
   login                ← 로그인 페이지 이동

   Header / Footer는 App.tsx에서 /register 계열 진입 시 숨김 처리 권장
========================================================== */

const REGISTER_IMAGE_URL = "/assets/login-architecture.jpg";

function navigateTo(path: string) {
  if (typeof window === "undefined") return;

  window.history.pushState({}, "", path);
  window.dispatchEvent(new Event("unotravel:navigate"));
}

export default function RegisterPage() {
  const [notice, setNotice] = useState("");

  function handleStartRegister() {
    /*
      Register Start Hook
      ------------------------------------------
      실제 백엔드 연동 시 기존 regis_agree.php 또는 신규 약관 API와 연결한다.
      현재는 프론트 UI/동선 확인용으로 /register/agreement로 이동한다.
    */
    setNotice("약관 확인 페이지로 이동합니다.");
    navigateTo("/register/agreement");
  }

  return (
    <main className="register-page-shell">
      <style>{STYLE}</style>

      <section className="register-page-inner" aria-label="우노트래블 회원가입 시작">
        <div className="register-content">
          <button
            type="button"
            className="register-back-button"
            aria-label="로그인 페이지로 이동"
            onClick={() => navigateTo("/login")}
          >
            ←
          </button>

          <div className="register-editorial">
            <div className="register-kicker">UNOTRAVEL ACCOUNT</div>

            <h1 className="register-title">
              회원가입
            </h1>

            <p className="register-description">
              회원가입은 예약과 문의, 여행 준비 과정을 한곳에서 관리하기 위해 필요합니다.
            </p>

            <div className="register-timeline" aria-label="회원가입 진행 순서">
              <div className="register-timeline-item">
                <span>01</span>
                <div>
                  <strong>약관 확인</strong>
                  <p>이용약관과 개인정보 수집 및 이용 안내를 확인합니다.</p>
                </div>
              </div>

              <div className="register-timeline-item">
                <span>02</span>
                <div>
                  <strong>정보 입력</strong>
                  <p>이름, 이메일, 비밀번호 등 기본 회원 정보를 입력합니다.</p>
                </div>
              </div>

              <div className="register-timeline-item">
                <span>03</span>
                <div>
                  <strong>가입 완료</strong>
                  <p>계정 생성 후 예약 내역과 문의 내역을 확인할 수 있습니다.</p>
                </div>
              </div>
            </div>

            <div className="register-action-row">
              <button type="button" className="register-submit" onClick={handleStartRegister}>
                <span>가입 시작</span>
                <span aria-hidden="true">→</span>
              </button>

              <button type="button" className="register-login-link" onClick={() => navigateTo("/login")}>
                로그인
              </button>
            </div>

            {notice && (
              <p className="register-notice" role="status" aria-live="polite">
                <span aria-hidden="true" />
                {notice}
              </p>
            )}
          </div>

          <div className="register-footnote">
            <span>Already have an account?</span>
            <button type="button" onClick={() => navigateTo("/login")}>
              로그인으로 이동
            </button>
          </div>
        </div>

        <aside className="register-visual" aria-label="우노트래블 회원가입 비주얼">
          <div
            className="register-visual-image"
            style={{ backgroundImage: `url(${REGISTER_IMAGE_URL})` }}
          />
          <div className="register-visual-scrim" aria-hidden="true" />

          <div className="register-visual-index">
            <span>ACCOUNT</span>
            <strong>03 STEPS</strong>
          </div>

          <p className="register-visual-statement">
            Travel records,
            <br />
            arranged quietly.
          </p>
        </aside>
      </section>
    </main>
  );
}

const STYLE = `
  .register-page-shell {
    width: 100%;
    min-width: 1024px;
    min-height: 100vh;
    background: #ffffff;
    color: #111111;
    overflow-x: hidden;
  }

  .register-page-inner {
    width: 100%;
    min-height: 100vh;
    display: grid;
    grid-template-columns: minmax(560px, 52vw) minmax(420px, 1fr);
    padding: 14px;
    box-sizing: border-box;
    background: #ffffff;
  }

  .register-content {
    position: relative;
    min-height: calc(100vh - 28px);
    padding: 34px 62px 40px;
    box-sizing: border-box;
    display: grid;
    grid-template-rows: auto 1fr auto;
    background: #ffffff;
  }

  .register-back-button {
    width: 34px;
    height: 34px;
    border: 0;
    background: transparent;
    color: rgba(17, 17, 17, 0.48);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: flex-start;
    font-family: var(--font-en);
    font-size: 26px;
    line-height: 1;
    transition: color 0.2s ease, transform 0.2s ease;
  }

  .register-back-button:hover {
    color: #111111;
    transform: translateX(-2px);
  }

  .register-editorial {
    width: min(620px, 100%);
    align-self: center;
    justify-self: center;
    margin-top: 8px;
  }

  .register-kicker {
    margin: 0 0 34px;
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.31em;
    font-weight: 760;
    color: rgba(17, 17, 17, 0.36);
  }

  .register-title {
    max-width: 560px;
    margin: 0;
    font-family: var(--font-ko);
    font-size: clamp(42px, 4.2vw, 66px);
    line-height: 1.08;
    letter-spacing: -0.075em;
    font-weight: 620;
    color: #111111;
    word-break: keep-all;
  }

  .register-description {
    max-width: 430px;
    margin: 38px 0 72px;
    font-family: var(--font-ko);
    font-size: 14px;
    line-height: 1.86;
    letter-spacing: -0.04em;
    font-weight: 500;
    color: rgba(17, 17, 17, 0.56);
    word-break: keep-all;
  }

  .register-timeline {
    width: min(520px, 100%);
    border-top: 1px solid rgba(17, 17, 17, 0.22);
  }

  .register-timeline-item {
    display: grid;
    grid-template-columns: 64px 1fr;
    gap: 26px;
    align-items: start;
    min-height: 92px;
    padding: 24px 0 23px;
    box-sizing: border-box;
    border-bottom: 1px solid rgba(17, 17, 17, 0.18);
  }

  .register-timeline-item span {
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.22em;
    font-weight: 760;
    color: rgba(17, 17, 17, 0.34);
  }

  .register-timeline-item:first-child span {
    color: #111111;
  }

  .register-timeline-item:first-child span::after {
    content: "";
    display: block;
    width: 20px;
    height: 2px;
    margin-top: 18px;
    background: #fcc800;
  }

  .register-timeline-item strong {
    display: block;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1;
    font-weight: 650;
    letter-spacing: -0.045em;
    color: #111111;
  }

  .register-timeline-item p {
    max-width: 340px;
    margin: 12px 0 0;
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1.62;
    font-weight: 500;
    letter-spacing: -0.04em;
    color: rgba(17, 17, 17, 0.52);
    word-break: keep-all;
  }

  .register-action-row {
    width: min(520px, 100%);
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 18px;
    align-items: center;
    margin-top: 34px;
  }

  .register-submit {
    height: 56px;
    border: 0;
    border-radius: 2px;
    background: #111111;
    color: #ffffff;
    cursor: pointer;
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    padding: 0 22px;
    box-sizing: border-box;
    font-family: var(--font-ko);
    font-size: 14px;
    font-weight: 690;
    letter-spacing: -0.02em;
    transition: background 0.22s ease, color 0.22s ease, transform 0.22s ease;
  }

  .register-submit:hover {
    background: #fcc800;
    color: #111111;
    transform: translateY(-1px);
  }

  .register-login-link {
    height: 56px;
    border: 0;
    background: transparent;
    padding: 0 4px;
    cursor: pointer;
    font-family: var(--font-ko);
    font-size: 13px;
    font-weight: 620;
    letter-spacing: -0.035em;
    color: rgba(17, 17, 17, 0.62);
    transition: color 0.2s ease;
  }

  .register-login-link:hover {
    color: #111111;
  }

  .register-notice {
    position: relative;
    width: min(520px, 100%);
    margin: 14px 0 0;
    min-height: 42px;
    border: 1px solid rgba(17, 17, 17, 0.13);
    background: rgba(17, 17, 17, 0.032);
    padding: 13px 14px 13px 42px;
    box-sizing: border-box;
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1.45;
    font-weight: 560;
    letter-spacing: -0.035em;
    color: rgba(17, 17, 17, 0.72);
    animation: registerNoticeIn 0.24s ease both;
  }

  .register-notice span {
    position: absolute;
    left: 14px;
    top: 50%;
    width: 16px;
    height: 16px;
    transform: translateY(-50%);
    border-radius: 999px;
    background: #fcc800;
  }

  .register-notice span::before {
    content: "";
    position: absolute;
    left: 50%;
    top: 4px;
    width: 1px;
    height: 8px;
    background: #111111;
    transform: translateX(-50%);
  }

  .register-notice span::after {
    content: "";
    position: absolute;
    left: 50%;
    bottom: 3px;
    width: 2px;
    height: 2px;
    border-radius: 999px;
    background: #111111;
    transform: translateX(-50%);
  }

  .register-footnote {
    display: flex;
    align-items: center;
    gap: 12px;
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.08em;
    font-weight: 680;
    color: rgba(17, 17, 17, 0.38);
  }

  .register-footnote button {
    border: 0;
    background: transparent;
    padding: 0;
    cursor: pointer;
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1;
    font-weight: 620;
    letter-spacing: -0.035em;
    color: rgba(17, 17, 17, 0.68);
    transition: color 0.2s ease;
  }

  .register-footnote button:hover {
    color: #111111;
  }

  .register-visual {
    position: relative;
    min-height: calc(100vh - 28px);
    overflow: hidden;
    background: #111111;
    border-radius: 22px;
    isolation: isolate;
    box-shadow: 0 24px 70px rgba(17, 17, 17, 0.09);
  }

  .register-visual-image {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    filter: saturate(0.82) contrast(1.05) brightness(0.95);
    transform: scale(1.055);
    animation: registerVisualDrift 24s ease-in-out infinite alternate;
    will-change: transform;
  }

  .register-visual-scrim {
    position: absolute;
    inset: 0;
    background:
      linear-gradient(180deg, rgba(0, 0, 0, 0.02) 0%, rgba(0, 0, 0, 0.22) 100%),
      linear-gradient(90deg, rgba(0, 0, 0, 0.04) 0%, rgba(0, 0, 0, 0.12) 100%);
    pointer-events: none;
  }

  .register-visual-index {
    position: absolute;
    left: 34px;
    top: 34px;
    display: flex;
    align-items: center;
    gap: 14px;
    color: #ffffff;
  }

  .register-visual-index span,
  .register-visual-index strong {
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.2em;
    font-weight: 760;
  }

  .register-visual-index span {
    color: rgba(255, 255, 255, 0.88);
  }

  .register-visual-index strong {
    color: rgba(255, 255, 255, 0.58);
  }

  .register-visual-statement {
    position: absolute;
    right: 44px;
    bottom: 40px;
    max-width: 390px;
    margin: 0;
    color: #ffffff;
    font-family: var(--font-en);
    font-size: clamp(42px, 5vw, 78px);
    line-height: 0.92;
    letter-spacing: -0.065em;
    font-weight: 520;
    text-align: right;
    text-shadow: 0 18px 46px rgba(0, 0, 0, 0.22);
  }

  @keyframes registerVisualDrift {
    from {
      transform: scale(1.055) translate3d(0, 0, 0);
    }
    to {
      transform: scale(1.105) translate3d(-1.4%, -1%, 0);
    }
  }

  @keyframes registerNoticeIn {
    from {
      opacity: 0;
      transform: translateY(-4px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @media (max-width: 1180px) {
    .register-page-inner {
      grid-template-columns: minmax(540px, 54vw) minmax(420px, 1fr);
    }

    .register-content {
      padding-left: 44px;
      padding-right: 44px;
    }

    .register-editorial {
      width: min(560px, 100%);
    }

    .register-title {
      font-size: 48px;
    }

    .register-description {
      margin-bottom: 56px;
    }
  }
`;
