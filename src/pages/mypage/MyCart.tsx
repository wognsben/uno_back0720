import { useEffect, useState } from "react";
import {
  deleteCartReservation,
  getCart,
  type CartItemResponse,
} from "../../api/reservationApi";

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
  { label: "투어 신청 / 예약 현황", path: "/mypage/tour" },
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

const formatMoney = (value?: number) =>
  typeof value === "number" && value > 0
    ? `${value.toLocaleString("ko-KR")}원`
    : "-";

const formatOptions = (item: CartItemResponse) =>
  item.options
    .map((option) => `${option.label || "요금 옵션"} ${option.personCount}명`)
    .join(", ");

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
  .mypage-heading span {
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

  .mypage-side p,
  .notice-box p,
  .list-row p {
    margin: 0;
    font-family: ${FONT_KO};
    font-size: 14px;
    font-weight: 600;
    line-height: 1.55;
    letter-spacing: -0.04em;
    color: rgba(21,21,21,.62);
    word-break: keep-all;
  }

  .mypage-side p {
    margin-bottom: 28px;
    font-size: 13px;
  }

  .mypage-nav {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .mypage-nav button,
  .mypage-logout,
  .primary-action,
  .ghost-action,
  .danger-action {
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
    font-family: "Times New Roman", ${FONT_EN};
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

  .list {
    margin-top: 30px;
    border-top: 1px solid ${BORDER};
  }

  .list-row {
    display: grid;
    grid-template-columns: 150px 1fr 128px;
    gap: 24px;
    align-items: center;
    padding: 22px 0;
    border-bottom: 1px solid ${BORDER};
  }

  .list-row strong {
    display: block;
    margin-bottom: 6px;
    font-family: ${FONT_KO};
    font-size: 18px;
    letter-spacing: -0.05em;
  }

  .danger-action {
    justify-self: end;
    height: 36px;
    padding: 0 14px;
    border: 1px solid rgba(212, 58, 58, .28);
    border-radius: 999px;
    color: #c62828;
    font-size: 12px;
    font-weight: 850;
    letter-spacing: -0.04em;
  }

  .danger-action:disabled {
    cursor: not-allowed;
    opacity: .45;
  }

  .notice-box {
    margin-top: 24px;
    padding: 20px 22px;
    border-radius: 18px;
    background: rgba(21,21,21,.035);
  }

  .actions {
    display: flex;
    gap: 10px;
    margin-top: 24px;
  }

  .primary-action,
  .ghost-action {
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

  .primary-action { background: ${YELLOW}; color: ${BLACK}; }
  .ghost-action { border: 1px solid ${BORDER}; color: ${BLACK}; }

  @media (max-width: 1180px) {
    .mypage-inner { grid-template-columns: 260px minmax(0,1fr); gap: 32px; padding-left: 38px; padding-right: 38px; }
    .mypage-heading h2 { font-size: 72px; }
  }
`;

export default function MyCart() {
  const [items, setItems] = useState<CartItemResponse[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState("");
  const [deletingRid, setDeletingRid] = useState<number | string | null>(null);

  const loadCart = () => {
    setIsLoading(true);
    setErrorMessage("");

    getCart()
      .then((response) => {
        setItems(response.items);
      })
      .catch((error: Error) => {
        setItems([]);
        setErrorMessage(error.message || "장바구니를 불러오지 못했습니다. 로그인 상태를 확인해 주세요.");
      })
      .finally(() => {
        setIsLoading(false);
      });
  };

  useEffect(() => {
    loadCart();
  }, []);

  const handleDelete = async (rid: number | string) => {
    if (deletingRid !== null) return;

    setDeletingRid(rid);
    setErrorMessage("");

    try {
      await deleteCartReservation(rid);
      setItems((currentItems) => currentItems.filter((item) => item.rid !== rid));
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "장바구니 항목을 삭제하지 못했습니다.");
    } finally {
      setDeletingRid(null);
    }
  };

  return (
    <MyPageLayout
      title="장바구니"
      eyebrow="CART"
      description="상품 상세에서 장바구니에 담은 투어만 표시됩니다."
      activePath="/mypage/cart"
    >
      {errorMessage ? (
        <div className="notice-box">
          <p>{errorMessage}</p>
        </div>
      ) : null}

      {isLoading ? (
        <div className="notice-box">
          <p>장바구니를 불러오는 중입니다.</p>
        </div>
      ) : items.length ? (
        <div className="list">
          {items.map((item) => (
            <div className="list-row" key={item.rid}>
              <p>
                {item.tourDate || "-"}
              </p>
              <div>
                <strong>{item.title || "상품명 없음"}</strong>
                <p>{formatOptions(item) || "선택 옵션 없음"}</p>
                <p>
                  예약금 {formatMoney(item.totalDeposit)} · 현지지불금{" "}
                  {formatMoney(item.totalLocalPayment)}
                </p>
              </div>
              <button
                type="button"
                className="danger-action"
                disabled={deletingRid === item.rid}
                onClick={() => handleDelete(item.rid)}
              >
                {deletingRid === item.rid ? "삭제 중" : "삭제"}
              </button>
            </div>
          ))}
        </div>
      ) : (
        <div className="notice-box">
          <p>장바구니에 담긴 투어가 없습니다.</p>
        </div>
      )}

      <div className="actions">
        <button className="primary-action" type="button" onClick={() => navigateTo("/product/semi/italy")}>
          투어 둘러보기
        </button>
        <button className="ghost-action" type="button" onClick={loadCart}>
          새로고침
        </button>
      </div>
    </MyPageLayout>
  );
}
