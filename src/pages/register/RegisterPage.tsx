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

      <section className="register-page-frame" aria-label="우노트래블 회원가입 시작">
        <button
          type="button"
          className="register-back-button"
          aria-label="로그인 페이지로 이동"
          onClick={() => navigateTo("/login")}
        >
          ←
        </button>

        <div className="register-hero" aria-hidden="true">
          <div
            className="register-hero-image"
            style={{ backgroundImage: `url(${REGISTER_IMAGE_URL})` }}
          />
          <div className="register-hero-scrim" />
          <div className="register-hero-copy">
            <span>UNOTRAVEL ACCOUNT</span>
            <strong>회원가입</strong>
          </div>
        </div>

        <section className="register-canvas" aria-label="회원가입 안내">
          <div className="register-canvas-grid">
            <div className="register-primary">
              <div className="register-kicker">UNOTRAVEL ACCOUNT</div>

              <h1 className="register-title">회원가입</h1>

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
              </div>

              {notice && (
                <p className="register-notice" role="status" aria-live="polite">
                  <span aria-hidden="true" />
                  {notice}
                </p>
              )}
            </div>

            <aside className="register-editorial-panel" aria-label="우노트래블 계정 안내">
              <div className="register-panel-kicker">JOURNEYS DESIGNED WITH CARE</div>

              <p className="register-panel-title">
                Every journey
                <br />
                begins with
                <br />
                a quiet start.
              </p>

              <div className="register-panel-note">
                <span>ACCOUNT</span>
                <p>
                  예약, 문의를 하나의 계정 안에서 차분하게 정리합니다.
                </p>
              </div>
            </aside>
          </div>

          <div className="register-footnote">
            <span>Already have an account?</span>
            <button type="button" onClick={() => navigateTo("/login")}>
              로그인으로 이동
            </button>
            <span aria-hidden="true">→</span>
          </div>
        </section>
      </section>
    </main>
  );
}

const STYLE = `
  .register-page-shell {
    width: 100%;
    min-width: 1024px;
    min-height: 100vh;
    background: #111111;
    color: #111111;
    overflow-x: hidden;
  }

  .register-page-frame {
    position: relative;
    width: 100%;
    min-height: 100vh;
    background: #111111;
    overflow: hidden;
  }

  .register-back-button {
    position: absolute;
    left: 38px;
    top: 34px;
    z-index: 5;
    width: 36px;
    height: 36px;
    border: 0;
    background: transparent;
    color: rgba(255, 255, 255, 0.82);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: flex-start;
    font-family: var(--font-en);
    font-size: 28px;
    line-height: 1;
    transition: color 0.2s ease, transform 0.2s ease;
  }

  .register-back-button:hover {
    color: #ffffff;
    transform: translateX(-2px);
  }

  .register-hero {
    position: relative;
    width: 100%;
    height: 38vh;
    min-height: 300px;
    max-height: 390px;
    overflow: hidden;
    background: #111111;
    isolation: isolate;
  }

  .register-hero-image {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    filter: saturate(0.76) contrast(1.04) brightness(0.9);
    transform: scale(1.035);
    animation: registerHeroDrift 26s ease-in-out infinite alternate;
    will-change: transform;
  }

  .register-hero-scrim {
    position: absolute;
    inset: 0;
    background:
      linear-gradient(180deg, rgba(0, 0, 0, 0.08) 0%, rgba(0, 0, 0, 0.3) 100%),
      linear-gradient(90deg, rgba(0, 0, 0, 0.28) 0%, rgba(0, 0, 0, 0.08) 58%, rgba(0, 0, 0, 0.18) 100%);
    pointer-events: none;
  }

  .register-hero-copy {
    position: absolute;
    left: 86px;
    bottom: 84px;
    display: grid;
    gap: 24px;
    color: #ffffff;
  }

  .register-hero-copy span {
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.34em;
    font-weight: 760;
    color: rgba(255, 255, 255, 0.78);
  }

  .register-hero-copy strong {
    font-family: var(--font-ko);
    font-size: clamp(46px, 4.4vw, 72px);
    line-height: 0.98;
    letter-spacing: -0.08em;
    font-weight: 520;
    color: #ffffff;
  }

  .register-canvas {
    position: relative;
    z-index: 2;
    width: 100%;
    min-height: calc(62vh + 80px);
    margin-top: -72px;
    padding: 74px 86px 42px;
    box-sizing: border-box;
    background: #ffffff;
    border-top-left-radius: 96px;
  }

  .register-canvas-grid {
    width: min(100%, 1280px);
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(430px, 0.84fr) minmax(460px, 1fr);
    gap: clamp(72px, 9vw, 136px);
    align-items: start;
  }

  .register-primary {
    width: 100%;
    max-width: 540px;
  }

  .register-kicker {
    display: none;
    margin: 0 0 30px;
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.31em;
    font-weight: 760;
    color: rgba(17, 17, 17, 0.36);
  }

  .register-title {
    display: none;
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
    max-width: 370px;
    margin: 0 0 56px;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1.82;
    letter-spacing: -0.045em;
    font-weight: 500;
    color: rgba(17, 17, 17, 0.76);
    word-break: keep-all;
  }

  .register-timeline {
    width: 100%;
    border-top: 1px solid rgba(17, 17, 17, 0.22);
  }

  .register-timeline-item {
    display: grid;
    grid-template-columns: 58px minmax(0, 1fr);
    gap: 26px;
    align-items: start;
    min-height: 96px;
    padding: 24px 0 23px;
    box-sizing: border-box;
    border-bottom: 1px solid rgba(17, 17, 17, 0.16);
  }

  .register-timeline-item span {
    position: relative;
    font-family: var(--font-en);
    font-size: 26px;
    line-height: 1;
    letter-spacing: 0.04em;
    font-weight: 520;
    color: #111111;
  }

  .register-timeline-item:first-child span::after {
    content: "";
    display: block;
    width: 22px;
    height: 2px;
    margin-top: 18px;
    background: #fcc800;
  }

  .register-timeline-item strong {
    display: block;
    padding-top: 4px;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1;
    font-weight: 680;
    letter-spacing: -0.045em;
    color: #111111;
  }

  .register-timeline-item p {
    max-width: 360px;
    margin: 12px 0 0;
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1.64;
    font-weight: 500;
    letter-spacing: -0.04em;
    color: rgba(17, 17, 17, 0.52);
    word-break: keep-all;
  }

  .register-action-row {
    width: 100%;
    display: block;
    margin-top: 34px;
  }

  .register-submit {
    width: 100%;
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


  .register-notice {
    position: relative;
    width: 100%;
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

  .register-editorial-panel {
    position: relative;
    min-height: 520px;
    padding: 18px 0 0 clamp(44px, 5vw, 86px);
    box-sizing: border-box;
    border-left: 1px solid rgba(17, 17, 17, 0.14);
    display: grid;
    grid-template-rows: auto auto 1fr auto;
    color: #111111;
  }

  .register-panel-kicker {
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.32em;
    font-weight: 760;
    color: rgba(17, 17, 17, 0.46);
  }

  .register-panel-title {
    max-width: 560px;
    margin: 28px 0 0;
    font-family: var(--font-en);
    font-size: clamp(54px, 5.6vw, 86px);
    line-height: 0.92;
    letter-spacing: -0.072em;
    font-weight: 430;
    color: #111111;
  }

  .register-panel-note {
    align-self: end;
    width: 100%;
    max-width: 100%;
    margin-top: 48px;
    padding-top: 18px;
    border-top: 1px solid rgba(17, 17, 17, 0.72);
    display: grid;
    grid-template-columns: 90px minmax(0, 1fr);
    gap: 28px;
    align-items: center;
  }

  .register-panel-note span {
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.24em;
    font-weight: 760;
    color: rgba(17, 17, 17, 0.42);
  }

  .register-panel-note p {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 13px;
    line-height: 1;
    letter-spacing: -0.04em;
    font-weight: 500;
    color: rgba(17, 17, 17, 0.62);
    word-break: keep-all;
    white-space: nowrap;
  }



  .register-footnote {
    width: min(100%, 1280px);
    margin: 28px auto 0;
    display: flex;
    align-items: center;
    gap: 14px;
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.02em;
    font-weight: 560;
    color: rgba(17, 17, 17, 0.42);
  }

  .register-footnote button {
    border: 0;
    border-bottom: 1px solid rgba(17, 17, 17, 0.42);
    background: transparent;
    padding: 0 0 3px;
    cursor: pointer;
    font-family: var(--font-ko);
    font-size: 13px;
    line-height: 1;
    font-weight: 650;
    letter-spacing: -0.035em;
    color: rgba(17, 17, 17, 0.8);
    transition: color 0.2s ease, border-color 0.2s ease;
  }

  .register-footnote button:hover {
    color: #111111;
    border-bottom-color: #111111;
  }

  @keyframes registerHeroDrift {
    from {
      transform: scale(1.035) translate3d(0, 0, 0);
    }
    to {
      transform: scale(1.085) translate3d(-1.2%, -0.8%, 0);
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
    .register-canvas {
      padding-left: 58px;
      padding-right: 58px;
    }

    .register-canvas-grid {
      grid-template-columns: minmax(410px, 0.9fr) minmax(410px, 1fr);
      gap: 64px;
    }

    .register-panel-title {
      font-size: 60px;
    }

    .register-panel-note {
      grid-template-columns: 86px minmax(0, 1fr);
      gap: 22px;
    }

    .register-panel-note p {
      white-space: normal;
      line-height: 1.5;
    }
  }
`;
