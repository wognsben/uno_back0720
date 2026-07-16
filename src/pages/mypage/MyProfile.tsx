import type { ReactNode } from "react";


const FONT_EN = "var(--font-en)";
const FONT_KO = "var(--font-ko)";
const BLACK = "#151515";
const BORDER = "#E8E9E9";
const YELLOW = "#FCC800";

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
  children: React.ReactNode;
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
                <span aria-hidden="true">→</span>
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
  .card-kicker {
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
    letter-spacing: -0.06em;
  }

  .mypage-side p {
    margin: 0 0 28px;
    font-family: ${FONT_KO};
    font-size: 13px;
    font-weight: 600;
    line-height: 1.55;
    letter-spacing: -0.04em;
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
  .card-link,
  .primary-action,
  .ghost-action {
    border: none;
    background: transparent;
    cursor: pointer;
    font-family: ${FONT_KO};
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
    letter-spacing: -0.04em;
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
    letter-spacing: -0.04em;
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
    letter-spacing: -0.065em;
  }

  .mypage-heading p {
    margin: 0;
    max-width: 560px;
    font-family: ${FONT_KO};
    font-size: 17px;
    font-weight: 650;
    line-height: 1.6;
    letter-spacing: -0.045em;
    color: rgba(21,21,21,.68);
    word-break: keep-all;
  }

  .grid { display: grid; gap: 16px; margin-top: 30px; }
  .grid.two { grid-template-columns: repeat(2, minmax(0,1fr)); }
  .grid.three { grid-template-columns: repeat(3, minmax(0,1fr)); }

  .card {
    border: 1px solid ${BORDER};
    border-radius: 22px;
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
    letter-spacing: -0.06em;
  }

  .card p, .list-row p, .notice-box p, .field label {
    margin: 0;
    font-family: ${FONT_KO};
    font-size: 14px;
    font-weight: 600;
    line-height: 1.55;
    letter-spacing: -0.04em;
    color: rgba(21,21,21,.62);
    word-break: keep-all;
  }

  .card-link, .primary-action, .ghost-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 44px;
    border-radius: 999px;
    padding: 0 18px;
    font-size: 13px;
    font-weight: 850;
    letter-spacing: -0.04em;
  }

  .card-link { margin-top: 20px; background: rgba(21,21,21,.045); color: ${BLACK}; }
  .primary-action { background: ${YELLOW}; color: ${BLACK}; }
  .ghost-action { border: 1px solid ${BORDER}; color: ${BLACK}; }

  .list { margin-top: 30px; border-top: 1px solid ${BORDER}; }
  .list-row {
    display: grid;
    grid-template-columns: 150px 1fr 140px;
    gap: 24px;
    align-items: center;
    padding: 22px 0;
    border-bottom: 1px solid ${BORDER};
  }

  .list-row strong {
    font-family: ${FONT_KO};
    font-size: 18px;
    letter-spacing: -0.05em;
  }

  .tag {
    justify-self: end;
    height: 32px;
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 0 12px;
    background: rgba(252,200,0,.18);
    font-family: ${FONT_KO};
    font-size: 12px;
    font-weight: 850;
    letter-spacing: -0.04em;
  }

  .notice-box {
    margin-top: 24px;
    padding: 20px 22px;
    border-radius: 18px;
    background: rgba(21,21,21,.035);
  }

  .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 16px; margin-top: 30px; }
  .field { display: flex; flex-direction: column; gap: 9px; }
  .field input, .field textarea {
    width: 100%;
    border: 1px solid rgba(21,21,21,.16);
    border-radius: 16px;
    background: #fff;
    padding: 0 16px;
    box-sizing: border-box;
    font-family: ${FONT_KO};
    font-size: 14px;
    font-weight: 650;
    outline: none;
  }
  .field input { height: 52px; }
  .field textarea { min-height: 150px; padding-top: 16px; resize: vertical; }
  .field.full { grid-column: 1 / -1; }

  .actions { display: flex; gap: 10px; margin-top: 24px; }

  @media (max-width: 1180px) {
    .mypage-inner { grid-template-columns: 260px minmax(0,1fr); gap: 32px; padding-left: 38px; padding-right: 38px; }
    .mypage-heading h2 { font-size: 72px; }
  }
`;



export default function MyProfile() {
  const userName = getUserName();
  return (
    <MyPageLayout title="개인정보 수정" eyebrow="PROFILE" description="회원정보와 연락처를 관리합니다. 실제 저장은 기존 register_form.php update mode와 연결합니다." activePath="/mypage/profile">
      <div className="form-grid">
        <label className="field"><span>이름</span><input defaultValue={userName} /></label>
        <label className="field"><span>E-mail</span><input defaultValue="user@example.com" /></label>
        <label className="field"><span>연락처</span><input placeholder="010-0000-0000" /></label>
        <label className="field"><span>카카오톡 아이디</span><input placeholder="Kakao ID" /></label>
        <label className="field"><span>현재 비밀번호</span><input type="password" /></label>
        <label className="field"><span>변경 비밀번호</span><input type="password" /></label>
      </div>
      <div className="actions"><button className="primary-action" type="button">수정완료</button><button className="ghost-action" type="button">회원탈퇴</button></div>
    </MyPageLayout>
  );
}
