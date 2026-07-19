// LoginPage.tsx
// UNO Travel 로그인 화면을 담당하며 이메일/소셜 로그인 진입, 로그인 후 이동, 임시 세션 상태를 관리합니다.
// 실제 인증 API 구현 파일이 아니라 프런트 로그인 UI와 예약 플로우 진입 전 사용자 상태 안내를 맡습니다.
// RegisterForm이나 authSession과 혼동되지 않도록 화면 렌더링과 임시 로그인 동작만 포함합니다.
import { useState } from "react";

import googleLogo from "./snsloginlogo.tsx/google_logo.png";
import kakaoLogo from "./snsloginlogo.tsx/kakao_logo.png";
import naverLogo from "./snsloginlogo.tsx/naver_login.png";
import unoYellowLogo from "./snsloginlogo.tsx/노랑 로고.png";
import loginVideo from "./snsloginlogo.tsx/login_video.mp4";
import { loginWithCredentials } from "../../api/reservationApi";

/* ==========================================================
   LoginPage.tsx

   UNOTRAVEL Login Page

   사용 페이지
   - /login

   백엔드 연동
   ------------------------------------------
   email/password login  ← 기존 회원 로그인
   social login          ← Google / Kakao / Naver OAuth 연결 예정
   register              ← 회원가입 페이지 이동 예정
   find password          ← 비밀번호 찾기 페이지 이동 예정

   Header / Footer는 App.tsx 공통 컴포넌트 사용
========================================================== */

type SocialProvider = "google" | "kakao" | "naver";

const DEFAULT_SOCIAL_RETURN_URL = "/mypage";
const ALLOWED_SOCIAL_PROVIDERS = new Set<SocialProvider>(["google", "kakao", "naver"]);

function navigateTo(path: string) {
  if (typeof window === "undefined") return;

  window.history.pushState({}, "", path);
  window.dispatchEvent(new Event("unotravel:navigate"));
}

function isSafeInternalPath(value: string | null) {
  if (!value) return false;

  const trimmed = value.trim();
  if (!trimmed.startsWith("/") || trimmed.startsWith("//")) return false;
  if (/^[a-z][a-z0-9+.-]*:/i.test(trimmed)) return false;

  return true;
}

function getSocialReturnUrl() {
  if (typeof window === "undefined") return DEFAULT_SOCIAL_RETURN_URL;

  const storedReturnUrl = window.sessionStorage.getItem("unotravel:redirect-after-login");
  if (isSafeInternalPath(storedReturnUrl)) {
    return storedReturnUrl as string;
  }

  return DEFAULT_SOCIAL_RETURN_URL;
}

const SOCIAL_PROVIDER_LABEL: Record<SocialProvider, string> = {
  google: "구글",
  kakao: "카카오",
  naver: "네이버",
};

const SOCIAL_PROVIDER_LOGO: Record<SocialProvider, string> = {
  google: googleLogo,
  kakao: kakaoLogo,
  naver: naverLogo,
};

const SOCIAL_PROVIDER_DESCRIPTION: Record<SocialProvider, string> = {
  google: "",
  kakao: "",
  naver: "",
};

export default function LoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [isPasswordVisible, setIsPasswordVisible] = useState(false);
  const [formError, setFormError] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  function handleSocialLogin(provider: SocialProvider) {
    if (!ALLOWED_SOCIAL_PROVIDERS.has(provider)) return;

    const returnUrl = getSocialReturnUrl();
    const socialLoginUrl =
      `/bbs/bbs/login.php?provider=${encodeURIComponent(provider)}` +
      `&url=${encodeURIComponent(returnUrl)}`;

    window.location.assign(socialLoginUrl);
    return;

    /*
      Social Login Backend Hook
      ------------------------------------------
      실제 백엔드 연동 시 provider별 OAuth endpoint로 교체한다.

      예)
      - /auth/google
      - /auth/kakao
      - /auth/naver

      현재는 프론트 UI/동선 확인용 placeholder다.
    */
  }

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const normalizedLoginId = email.trim();
    const normalizedPassword = password;

    if (!normalizedLoginId) {
      setFormError("아이디 또는 이메일을 입력해 주세요.");
      return;
    }

    if (!normalizedPassword) {
      setFormError("비밀번호를 입력해 주세요.");
      return;
    }

    setFormError("");
    setIsSubmitting(true);

    try {
      const session = await loginWithCredentials({
        mb_id: normalizedLoginId,
        mb_password: normalizedPassword,
      });

      if (typeof window !== "undefined") {
        window.sessionStorage.setItem("unotravel:auth", "true");

        if (session.member?.name) {
          window.sessionStorage.setItem("unotravel:user-name", session.member.name);
        }

        if (session.member?.email) {
          window.sessionStorage.setItem("unotravel:user-email", session.member.email);
          window.sessionStorage.setItem("unotravel:email", session.member.email);
        }

        window.dispatchEvent(new Event("unotravel:auth-change"));
      }

      const redirectAfterLogin =
        typeof window !== "undefined"
          ? window.sessionStorage.getItem("unotravel:redirect-after-login")
          : null;

      if (typeof window !== "undefined" && redirectAfterLogin) {
        window.sessionStorage.removeItem("unotravel:redirect-after-login");
      }

      navigateTo(redirectAfterLogin || "/");
    } catch (error) {
      setFormError(
        error instanceof Error
          ? error.message
          : "로그인 중 문제가 발생했습니다. 다시 시도해 주세요.",
      );
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <main className="login-page-shell">
      <style>{STYLE}</style>

      <section className="login-page-inner" aria-label="우노트래블 로그인">
        <div className="login-content">
          <button
            type="button"
            className="login-back-button"
            aria-label="이전 페이지로 이동"
            onClick={() => navigateTo("/")}
          >
            ←
          </button>

          <div className="login-form-center">
            <div className="login-kicker">UNOTRAVEL ACCOUNT</div>

            <h1 className="login-title">로그인</h1>
            <p className="login-description">
              예약 내역과 문의 내역을 확인하려면 로그인해 주세요.
            </p>
            <p className="login-brand-line">
              Every journey begins before departure.
            </p>

            <form className="login-form" onSubmit={handleSubmit} noValidate>
              <label className="login-field">
                <span>아이디 또는 이메일</span>
                <input
                  type="text"
                  value={email}
                  placeholder="아이디 또는 이메일을 입력하세요"
                  autoComplete="username"
                  onChange={(event) => {
                    setEmail(event.target.value);
                    if (formError) setFormError("");
                  }}
                />
              </label>

              <label className="login-field">
                <span>비밀번호</span>
                <div className="login-password-wrap">
                  <input
                    type={isPasswordVisible ? "text" : "password"}
                    value={password}
                    placeholder="비밀번호를 입력하세요"
                    autoComplete="current-password"
                    onChange={(event) => {
                    setPassword(event.target.value);
                    if (formError) setFormError("");
                  }}
                  />
                  <button
                    type="button"
                    className={`login-password-toggle ${isPasswordVisible ? "is-visible" : ""}`}
                    aria-label={isPasswordVisible ? "비밀번호 숨기기" : "비밀번호 보기"}
                    onClick={() => setIsPasswordVisible((prev) => !prev)}
                  >
                    {isPasswordVisible ? "HIDE" : "VIEW"}
                  </button>
                </div>
              </label>

              <button type="submit" className="login-submit" disabled={isSubmitting}>
                <span>{isSubmitting ? "로그인 중" : "로그인"}</span>
                <span aria-hidden="true">→</span>
              </button>
            </form>

            {formError && (
              <p className="login-form-error" role="alert" aria-live="polite">
                <span aria-hidden="true" />
                {formError}
              </p>
            )}

            <div className="login-divider">
              <span />
              <strong>간편 로그인</strong>
              <span />
            </div>

            <div className="login-social-list" aria-label="SNS 로그인">
              {(Object.keys(SOCIAL_PROVIDER_LOGO) as SocialProvider[]).map((provider) => (
                <button
                  key={provider}
                  type="button"
                  aria-label={`${SOCIAL_PROVIDER_LABEL[provider]} 로그인`}
                  onClick={() => handleSocialLogin(provider)}
                >
                  <span className="login-social-icon" aria-hidden="true">
                    <img src={SOCIAL_PROVIDER_LOGO[provider]} alt="" />
                  </span>
                </button>
              ))}
            </div>

            <nav className="login-link-row" aria-label="로그인 보조 링크">
              <button type="button" onClick={() => navigateTo("/find-password")}>
                비밀번호 찾기
              </button>
              <span aria-hidden="true" />
              <button type="button" onClick={() => navigateTo("/register")}>
                회원가입
              </button>
            </nav>
          </div>

          <div className="login-brand-mark" aria-label="UNO TRAVEL">
            <img src={unoYellowLogo} alt="UNO TRAVEL" />
            
            
          </div>
        </div>

        <aside className="login-visual" aria-label="우노트래블 로그인 비주얼">
          <video
            className="login-visual-video"
            src={loginVideo}
            autoPlay
            muted
            loop
            playsInline
            preload="auto"
          />
          <div className="login-visual-scrim" aria-hidden="true" />

          <div className="login-visual-copy-top">
            <span />
            <p>
              THE JOURNEY BEGINS
              <br />
              BEFORE DEPARTURE.
            </p>
            <strong>UNOTRAVEL</strong>
          </div>

          <div className="login-visual-small-copy">
            <span>Curated Mediterranean experiences.</span>
            <strong>Since 2011</strong>
          </div>
        </aside>
      </section>
    </main>
  );
}

const STYLE = `
  .login-page-shell {
    width: 100%;
    min-width: 1024px;
    min-height: 100vh;
    background: #ffffff;
    color: #111111;
    overflow-x: hidden;
  }
  .login-page-inner {
    width: 100%;
    min-height: 100vh;
    display: grid;
    grid-template-columns: minmax(460px, 45vw) minmax(560px, 1fr);
    background: #ffffff;
    padding: 14px;
    box-sizing: border-box;
    gap: 0;
  }
  .login-content {
    position: relative;
    min-height: calc(100vh - 28px);
    padding: 34px 54px 40px;
    box-sizing: border-box;
    display: grid;
    grid-template-rows: auto 1fr auto;
    background: #ffffff;
  }

  .login-back-button {
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

  .login-back-button:hover {
    color: #111111;
    transform: translateX(-2px);
  }

  .login-form-center {
    width: min(410px, 100%);
    align-self: center;
    justify-self: center;
    margin-top: 8px;
  }

  .login-kicker {
    margin: 0 0 24px;
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.31em;
    font-weight: 760;
    color: rgba(17, 17, 17, 0.36);
  }

  .login-title {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 34px;
    line-height: 1.1;
    letter-spacing: -0.06em;
    font-weight: 660;
    color: #111111;
  }

  .login-description {
    max-width: 330px;
    margin: 18px 0 16px;
    font-family: var(--font-ko);
    font-size: 14px;
    line-height: 1.78;
    letter-spacing: -0.04em;
    font-weight: 520;
    color: rgba(17, 17, 17, 0.56);
    word-break: keep-all;
  }

  .login-brand-line {
    margin: 0 0 38px;
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1.2;
    letter-spacing: 0.08em;
    font-weight: 690;
    color: rgba(17, 17, 17, 0.42);
  }

  .login-form {
    display: flex;
    flex-direction: column;
    gap: 26px;
  }

  .login-field {
    display: flex;
    flex-direction: column;
    gap: 10px;
    font-family: var(--font-ko);
    font-size: 12px;
    font-weight: 720;
    letter-spacing: -0.035em;
    color: #111111;
  }

  .login-field > span {
    display: block;
  }

  .login-field input {
    width: 100%;
    height: 42px;
    border: 0;
    border-bottom: 1px solid rgba(17, 17, 17, 0.28);
    border-radius: 0;
    background: transparent;
    padding: 0;
    box-sizing: border-box;
    font-family: var(--font-ko);
    font-size: 14px;
    font-weight: 520;
    color: #111111;
    outline: none;
    transition: border-color 0.22s ease;
  }

  .login-field input::placeholder {
    color: rgba(17, 17, 17, 0.34);
  }

  .login-field input:focus {
    border-color: #111111;
  }

  .login-password-wrap {
    position: relative;
  }

  .login-password-toggle {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    border: 0;
    background: transparent;
    padding: 0;
    cursor: pointer;
    font-family: var(--font-en);
    font-size: 10px;
    font-weight: 760;
    line-height: 1;
    letter-spacing: 0.16em;
    color: rgba(17, 17, 17, 0.5);
    transition: color 0.2s ease;
  }

  .login-password-toggle:hover {
    color: #111111;
  }

  .login-submit {
    width: 100%;
    height: 56px;
    margin-top: 2px;
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

  .login-submit:hover:not(:disabled) {
    background: #fcc800;
    color: #111111;
    transform: translateY(-1px);
  }

  
  .login-form-error {
    position: relative;
    margin: 14px 0 0;
    min-height: 42px;
    border: 1px solid rgba(17, 17, 17, 0.13);
    border-radius: 0;
    background: rgba(17, 17, 17, 0.032);
    padding: 13px 14px 13px 42px;
    box-sizing: border-box;
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1.45;
    font-weight: 560;
    letter-spacing: -0.035em;
    color: rgba(17, 17, 17, 0.72);
    animation: loginErrorIn 0.24s ease both;
  }

  .login-form-error span {
    position: absolute;
    left: 14px;
    top: 50%;
    width: 16px;
    height: 16px;
    transform: translateY(-50%);
    border-radius: 999px;
    background: #111111;
  }

  .login-form-error span::before,
  .login-form-error span::after {
    content: "";
    position: absolute;
    left: 50%;
    background: #ffffff;
    transform: translateX(-50%);
  }

  .login-form-error span::before {
    top: 3px;
    width: 1px;
    height: 6px;
  }

  .login-form-error span::after {
    bottom: 3px;
    width: 2px;
    height: 2px;
    border-radius: 999px;
  }

  @keyframes loginErrorIn {
    from {
      opacity: 0;
      transform: translateY(-4px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  .login-divider {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 18px;
    margin: 30px 0 16px;
    font-family: var(--font-ko);
    font-size: 11px;
    font-weight: 560;
    letter-spacing: -0.025em;
    color: rgba(17, 17, 17, 0.42);
  }

  .login-divider span {
    height: 1px;
    background: rgba(17, 17, 17, 0.16);
  }
  .login-social-list {
    display: grid;
    grid-template-columns: repeat(3, 52px);
    justify-content: center;
    align-items: center;
    gap: 12px;
    border-top: 0;
  }
  .login-social-list button {
    width: 52px;
    height: 52px;
    border: 1px solid rgba(17, 17, 17, 0.13);
    border-radius: 999px;
    background: rgba(17, 17, 17, 0.018);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    box-sizing: border-box;
    color: #111111;
    transition:
      border-color 0.22s ease,
      background 0.22s ease,
      transform 0.22s ease;
  }
  .login-social-list button:hover {
    border-color: rgba(17, 17, 17, 0.38);
    background: rgba(17, 17, 17, 0.045);
    transform: translateY(-2px);
  }
  .login-social-icon {
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .login-social-icon img {
    width: 24px;
    height: 24px;
    display: block;
    object-fit: contain;
  }

  .login-social-copy {
    display: grid;
    gap: 6px;
    justify-self: start;
    text-align: left;
    transition: transform 0.24s ease;
  }

  .login-social-copy strong {
    font-family: var(--font-en);
    font-size: 12px;
    line-height: 1;
    letter-spacing: 0.08em;
    font-weight: 760;
  }

  .login-social-copy small {
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.01em;
    font-weight: 620;
    color: rgba(17, 17, 17, 0.46);
  }


  .login-social-list button:hover .login-social-icon {
    transform: scale(1.08);
  }

  .login-social-list button:hover .login-social-copy {
    transform: translateX(4px);
  }


  .login-social-notice {
    margin: 16px 0 0;
    border-top: 1px solid rgba(17, 17, 17, 0.14);
    border-bottom: 1px solid rgba(17, 17, 17, 0.14);
    background: transparent;
    padding: 12px 0;
    box-sizing: border-box;
    font-family: var(--font-ko);
    font-size: 12px;
    font-weight: 560;
    line-height: 1.55;
    letter-spacing: -0.04em;
    color: rgba(17, 17, 17, 0.58);
  }

  .login-link-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 26px;
    margin: 26px 0 0;
  }

  .login-link-row button {
    border: 0;
    background: transparent;
    padding: 0;
    cursor: pointer;
    font-family: var(--font-ko);
    font-size: 13px;
    font-weight: 640;
    letter-spacing: -0.035em;
    color: rgba(17, 17, 17, 0.7);
    transition: color 0.2s ease;
  }

  .login-link-row button:hover {
    color: #111111;
  }

  .login-link-row span {
    width: 1px;
    height: 15px;
    background: rgba(17, 17, 17, 0.22);
  }

  .login-brand-mark {
    align-self: end;
    justify-self: start;
    display: grid;
    gap: 7px;
    min-height: 42px;
  }

  .login-brand-mark img {
    display: block;
    width: 86px;
    height: auto;
    object-fit: contain;
  }

  .login-brand-mark span,
  .login-brand-mark strong {
    display: block;
    font-family: var(--font-en);
    font-size: 9px;
    line-height: 1;
    letter-spacing: 0.18em;
    font-weight: 760;
    color: rgba(17, 17, 17, 0.38);
  }

  .login-brand-mark strong {
    color: rgba(17, 17, 17, 0.58);
  }
  .login-visual {
    position: relative;
    min-height: calc(100vh - 28px);
    overflow: hidden;
    background: #111111;
    border-radius: 22px;
    isolation: isolate;
    box-shadow: 0 24px 70px rgba(17, 17, 17, 0.09);
  }
  .login-visual-video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: saturate(0.9) contrast(1.03) brightness(0.94);
    transform: scale(1.06);
    animation: loginVideoDrift 22s ease-in-out infinite alternate;
    will-change: transform;
  }

  .login-visual-scrim {
    position: absolute;
    inset: 0;
    background:
      linear-gradient(180deg, rgba(0, 0, 0, 0.02) 0%, rgba(0, 0, 0, 0.18) 100%),
      linear-gradient(90deg, rgba(0, 0, 0, 0.08) 0%, rgba(0, 0, 0, 0.02) 54%);
    pointer-events: none;
  }

  .login-visual-copy-top {
    position: absolute;
    right: 52px;
    top: 52px;
    display: grid;
    grid-template-columns: 26px minmax(0, auto);
    column-gap: 18px;
    row-gap: 16px;
    color: #ffffff;
    text-align: left;
  }

  .login-visual-copy-top span {
    width: 26px;
    height: 1px;
    margin-top: 8px;
    background: rgba(255, 255, 255, 0.82);
  }

  .login-visual-copy-top p {
    grid-column: 2 / 3;
    margin: 0;
    font-family: var(--font-en);
    font-size: 14px;
    line-height: 1.5;
    letter-spacing: 0.12em;
    font-weight: 680;
  }

  .login-visual-copy-top strong {
    grid-column: 2 / 3;
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.38em;
    font-weight: 680;
    color: rgba(255, 255, 255, 0.72);
  }

  .login-visual-small-copy {
    position: absolute;
    left: 34px;
    bottom: 32px;
    max-width: 340px;
    display: grid;
    gap: 5px;
    color: #ffffff;
    text-shadow: 0 14px 38px rgba(0, 0, 0, 0.22);
  }

  .login-visual-small-copy span,
  .login-visual-small-copy strong {
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1.08;
    letter-spacing: 0.02em;
    font-weight: 760;
  }

  .login-visual-small-copy strong {
    color: rgba(255, 255, 255, 0.68);
  }

  @keyframes loginVideoDrift {
    from {
      transform: scale(1.06) translate3d(0, 0, 0);
    }
    to {
      transform: scale(1.12) translate3d(-1.2%, -1%, 0);
    }
  }

  @media (max-width: 1180px) {
    .login-page-inner {
      grid-template-columns: minmax(430px, 45vw) minmax(520px, 1fr);
    }

    .login-content {
      padding-left: 40px;
      padding-right: 40px;
    }

    .login-form-center {
      width: min(390px, 100%);
    }
  }
`;
