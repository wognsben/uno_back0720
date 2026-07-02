import { useEffect, useRef, useState, type ReactNode } from "react";
import imgImage21 from "./82946044a68c061ee150f4ee5fd02f4ec778c1b7.png";
import {
  getRecentlyViewedProducts,
  type RecentlyViewedProduct,
} from "../../../utils/recentlyViewed";

/* ────────────────────────────────────────────
   공통 HEADER
   Premium Editorial Header

   수정 메모
   - 최상단 Header : 820px 카드형 중앙 배치
   - 스크롤 Header : width: 100% 기준 전체 화면 폭
   - 기존 디자인/구조 유지, Header 폭 기준만 수정
──────────────────────────────────────────── */

type HeaderProps = {
  isLoggedIn?: boolean;
  userName?: string;
};

type LanternState = {
  x: number;
  y: number;
  visible: boolean;
};

type ViewedPanelAnchor = {
  left: number;
  top: number;
  bottom: number;
  width: number;
};

type HeaderAuthState = {
  isLoggedIn: boolean;
  userName: string;
  userEmail: string;
};

const RECENTLY_VIEWED_FIRST_OPEN_KEY = "unotravel_recently_viewed_first_opened";

function getHeaderAuthState(
  propIsLoggedIn: boolean,
  propUserName: string,
): HeaderAuthState {
  const fallbackName = propUserName || "회원";
  const fallbackEmail = "hong@example.com";

  if (typeof window === "undefined") {
    return {
      isLoggedIn: propIsLoggedIn,
      userName: fallbackName,
      userEmail: fallbackEmail,
    };
  }

  const savedName = window.sessionStorage.getItem("unotravel:user-name");
  const savedEmail =
    window.sessionStorage.getItem("unotravel:user-email") ||
    window.sessionStorage.getItem("unotravel:email") ||
    fallbackEmail;

  if (propIsLoggedIn) {
    return {
      isLoggedIn: true,
      userName: savedName || fallbackName,
      userEmail: savedEmail,
    };
  }

  const emailLoginAuth = window.sessionStorage.getItem("unotravel:auth");
  const registerLoginAuth = window.sessionStorage.getItem("unotravel_auth");

  if (emailLoginAuth === "true") {
    return {
      isLoggedIn: true,
      userName: savedName || fallbackName,
      userEmail: savedEmail,
    };
  }

  if (registerLoginAuth) {
    try {
      const parsed = JSON.parse(registerLoginAuth) as {
        isLoggedIn?: boolean;
        name?: string;
        email?: string;
        mb_email?: string;
      };

      if (parsed?.isLoggedIn) {
        return {
          isLoggedIn: true,
          userName: parsed.name || savedName || fallbackName,
          userEmail: parsed.email || parsed.mb_email || savedEmail,
        };
      }
    } catch {
      return {
        isLoggedIn: false,
        userName: fallbackName,
        userEmail: fallbackEmail,
      };
    }
  }

  return {
    isLoggedIn: false,
    userName: fallbackName,
    userEmail: fallbackEmail,
  };
}

function navigateTo(path: string) {
  if (typeof window === "undefined") return;

  if (window.location.pathname === path) {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
    return;
  }

  window.history.pushState({}, "", path);
  window.dispatchEvent(new Event("unotravel:navigate"));
}

function formatRecentlyViewedCategory(product: RecentlyViewedProduct) {
  if (product.productType === "daily") return "DAILY TOUR";
  if (product.productType === "semi") return "SEMI PACKAGE";

  const href = product.href.toLowerCase();
  if (href.includes("/daily/")) return "DAILY TOUR";

  return "SEMI PACKAGE";
}

function navigateToRecentlyViewedProduct(href: string) {
  if (!href) return;

  window.history.pushState({}, "", href);
  window.dispatchEvent(new Event("unotravel:navigate"));
}

const FONT_MONO = "var(--font-en)";
const BLACK = "#151515";
const BORDER = "#E8E9E9";

const MENU_ITEMS = [
  { label: "SEMI PACKAGE", href: "/contents/tour_list.php?ca_id=semi" },
  { label: "DAILY TOUR", href: "/contents/tour_list.php?ca_id=daily" },
  { label: "NOTICE", href: "/contents/board.php?bo_table=notice" },
  { label: "ABOUT UNO", href: "/contents/about.php" },
  { label: "CONTACT", href: "/contents/contact.php" },
] as const;

function DotGrid({
  isOpen,
  onClick,
}: {
  isOpen: boolean;
  onClick: () => void;
}) {
  const [isHovered, setIsHovered] = useState(false);

  // Hover 시 3x3 구조는 유지하되 일부 dot만 사라져 비규칙적인 메뉴 아이콘처럼 보이게 함
  const hiddenOnHover = new Set([1, 3, 7]);

  return (
    <button
      aria-label={isOpen ? "Close menu" : "Open menu"}
      aria-expanded={isOpen}
      onClick={onClick}
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
      style={{
        background: "none",
        border: "none",
        cursor: "pointer",
        padding: 0,
        width: 24,
        height: 24,
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        position: "relative",
        zIndex: 6,
      }}
    >
      <span
        style={{
          display: "grid",
          gridTemplateColumns: "repeat(3, 4.8px)",
          gap: "4.8px",
          width: 24,
          height: 24,
          alignContent: "center",
          justifyContent: "center",
          transform: isHovered || isOpen ? "scale(1.08)" : "scale(1)",
          transition: "transform 0.25s ease",
          position: "relative",
        }}
      >
        {Array.from({ length: 9 }).map((_, i) => {
          const openVisible =
            i === 0 || i === 2 || i === 4 || i === 6 || i === 8;
          return (
            <span
              key={i}
              style={{
                width: 4.8,
                height: 4.8,
                borderRadius: "50%",
                background: BLACK,
                opacity: isOpen
                  ? openVisible
                    ? 1
                    : 0
                  : isHovered && hiddenOnHover.has(i)
                    ? 0
                    : 1,
                transform: isOpen
                  ? i === 0 || i === 8
                    ? "scaleX(2.2) rotate(45deg)"
                    : i === 2 || i === 6
                      ? "scaleX(2.2) rotate(-45deg)"
                      : "scale(0.9)"
                  : isHovered && hiddenOnHover.has(i)
                    ? "scale(0.4)"
                    : "scale(1)",
                transition: "opacity 0.24s ease, transform 0.24s ease",
              }}
            />
          );
        })}
      </span>
    </button>
  );
}

function Logo() {
  return (
    <button
      type="button"

      /*
        SPA Navigation
        ------------------------------------------
        Logo 클릭 시 전체 페이지를 다시 로드하지 않는다.
        ProductNavigation과 동일하게 history + custom event로 이동한다.
      */
      onClick={() => {
        if (window.location.pathname === "/") {
          window.scrollTo({
            top: 0,
            behavior: "smooth",
          });
          return;
        }

        window.history.pushState({}, "", "/");
        window.dispatchEvent(new Event("unotravel:navigate"));
      }}

      aria-label="UNOTRAVEL home"

      style={{
        width: 80,
        height: 46,
        display: "flex",
        flexDirection: "column",
        alignItems: "flex-start",
        justifyContent: "center",
        flexShrink: 0,
        background: "none",
        border: "none",
        cursor: "pointer",
        textDecoration: "none",
        color: BLACK,
        padding: 0,
      }}
    >
      <span
        style={{
          fontFamily: FONT_MONO,
          fontWeight: 700,
          fontSize: 35,
          color: BLACK,
          lineHeight: 0.78,
          letterSpacing: "-0.07em",
        }}
      >
        UNO
      </span>
      <span
        style={{
          fontFamily: FONT_MONO,
          fontWeight: 700,
          fontSize: 10,
          color: BLACK,
          letterSpacing: "0.22em",
          lineHeight: 1,
          marginTop: 7,
        }}
      >
        T·R·A·V·E·L
      </span>
    </button>
  );
}

function HeaderButton({
  children,
  width,
  isHovered = false,
  onClick,
  onMouseEnter,
  buttonRef,
}: {
  children: ReactNode;
  width: number;
  isHovered?: boolean;
  onClick?: () => void;
  onMouseEnter?: (event: React.MouseEvent<HTMLButtonElement>) => void;
  buttonRef?: React.Ref<HTMLButtonElement>;
}) {
  return (
    <button
      ref={buttonRef}
      onClick={onClick}
      onMouseEnter={onMouseEnter}
      style={{
        width,
        height: 30,
        fontFamily: FONT_MONO,
        fontWeight: 700,
        fontSize: 16,
        letterSpacing: "0.03em",
        lineHeight: 0,
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        textAlign: "center",
        color: BLACK,
        background: "none",
        border: "none",
        cursor: "pointer",
        padding: 0,
        position: "relative",
        zIndex: 4,
        transform: isHovered ? "translateY(-1px)" : "translateY(0)",
        transition: "transform 0.28s ease",
      }}
    >
      {children}
    </button>
  );
}


const INFO_MENU_ITEMS = [
  { label: "예약 시 주의사항", path: "/info/notice" },
  { label: "취소 및 환불규정", path: "/info/refund" },
] as const;

function InfoDropdownPanel({
  isOpen,
  isScrolled,
  anchor,
  onNavigate,
  onMouseEnter,
  onMouseLeave,
}: {
  isOpen: boolean;
  isScrolled: boolean;
  anchor: ViewedPanelAnchor | null;
  onNavigate: (path: string) => void;
  onMouseEnter: () => void;
  onMouseLeave: () => void;
}) {
  const panelWidth = 232;
  const panelLeft =
    typeof window === "undefined" || !anchor
      ? 0
      : Math.min(
          Math.max(12, anchor.left + anchor.width / 2 - panelWidth / 2),
          window.innerWidth - panelWidth - 12,
        );

  return (
    <div
      aria-hidden={!isOpen}
      onMouseEnter={onMouseEnter}
      onMouseLeave={onMouseLeave}
      style={{
        position: isScrolled && anchor ? "fixed" : "absolute",
        top: isScrolled && anchor ? anchor.bottom + 10 : 102,
        left: isScrolled && anchor ? panelLeft : "50%",
        width: panelWidth,
        background: "#FFFFFF",
        border: `2px solid ${BORDER}`,
        borderRadius: isScrolled ? 16 : 20,
        boxSizing: "border-box",
        padding: "10px",
        pointerEvents: isOpen ? "auto" : "none",
        opacity: isOpen ? 1 : 0,
        transform:
          isOpen
            ? isScrolled && anchor
              ? "translate3d(0, 0, 0) scale(1)"
              : "translate3d(-50%, 0, 0) scale(1)"
            : isScrolled && anchor
              ? "translate3d(0, -8px, 0) scale(0.985)"
              : "translate3d(-50%, -8px, 0) scale(0.985)",
        transformOrigin: "top center",
        transition:
          "opacity 0.2s ease, transform 0.24s cubic-bezier(0.16, 1, 0.3, 1)",
        zIndex: isScrolled ? 1002 : 5,
        boxShadow: "0 18px 48px rgba(0, 0, 0, 0.075)",
      }}
    >
      <div
        style={{
          padding: "8px 8px 12px",
          borderBottom: `1px solid ${BORDER}`,
          marginBottom: 6,
          fontFamily: FONT_MONO,
          fontSize: 11,
          fontWeight: 800,
          lineHeight: 1,
          letterSpacing: "0.12em",
          color: "rgba(21, 21, 21, 0.58)",
        }}
      >
        INFO
      </div>

      {INFO_MENU_ITEMS.map((item) => (
        <button
          key={item.path}
          type="button"
          onClick={() => onNavigate(item.path)}
          style={{
            width: "100%",
            height: 38,
            border: "none",
            borderRadius: 11,
            background: "#FFFFFF",
            cursor: "pointer",
            display: "flex",
            alignItems: "center",
            justifyContent: "space-between",
            padding: "0 8px",
            boxSizing: "border-box",
            fontFamily: "var(--font-ko)",
            fontSize: 13,
            fontWeight: 800,
            letterSpacing: "-0.04em",
            color: BLACK,
            textAlign: "left",
            transition: "background 0.16s ease, padding 0.16s ease",
          }}
          onMouseEnter={(event) => {
            event.currentTarget.style.background = "rgba(21, 21, 21, 0.035)";
            event.currentTarget.style.padding = "0 11px";
          }}
          onMouseLeave={(event) => {
            event.currentTarget.style.background = "#FFFFFF";
            event.currentTarget.style.padding = "0 8px";
          }}
        >
          <span>{item.label}</span>
          <span aria-hidden="true">›</span>
        </button>
      ))}
    </div>
  );
}

function UserBlock({
  userName,
  userEmail,
  onNavigate,
  onLogout,
}: {
  userName: string;
  userEmail: string;
  onNavigate: (path: string) => void;
  onLogout: () => void;
}) {
  const [isOpen, setIsOpen] = useState(false);
  const closeTimerRef = useRef<number | null>(null);

  const menuItems = [
    { label: "장바구니", path: "/mypage/cart" },
    { label: "예약목록", path: "/mypage/reservations" },
    { label: "1:1 문의", path: "/mypage/inquiry" },
    { label: "개인정보 수정", path: "/mypage/profile" },
    { label: "투어 신청", path: "/mypage/tour" },
  ] as const;

  const openPanel = () => {
    if (closeTimerRef.current) {
      window.clearTimeout(closeTimerRef.current);
      closeTimerRef.current = null;
    }

    setIsOpen(true);
  };

  const scheduleClosePanel = () => {
    if (closeTimerRef.current) {
      window.clearTimeout(closeTimerRef.current);
    }

    closeTimerRef.current = window.setTimeout(() => {
      setIsOpen(false);
    }, 120);
  };

  useEffect(() => {
    return () => {
      if (closeTimerRef.current) {
        window.clearTimeout(closeTimerRef.current);
      }
    };
  }, []);

  return (
    <div
      onMouseEnter={openPanel}
      onMouseLeave={scheduleClosePanel}
      style={{
        display: "flex",
        flexDirection: "column",
        alignItems: "flex-start",
        gap: 4,
        width: 96,
        height: 44,
        justifyContent: "center",
        background: "transparent",
        position: "relative",
        zIndex: 6,
      }}
    >
      <span
        style={{
          width: 96,
          height: 20,
          fontFamily: FONT_MONO,
          fontWeight: 700,
          fontSize: 11,
          lineHeight: 1,
          display: "flex",
          alignItems: "center",
          color: BLACK,
          whiteSpace: "nowrap",
          overflow: "hidden",
          textOverflow: "ellipsis",
        }}
      >
        {userName}님
      </span>
      <button
        type="button"
        onClick={() => onNavigate("/mypage")}
        style={{
          width: 96,
          height: 20,
          fontFamily: FONT_MONO,
          fontWeight: 700,
          fontSize: 14,
          lineHeight: 1,
          display: "flex",
          alignItems: "center",
          color: BLACK,
          background: "none",
          border: "none",
          cursor: "pointer",
          padding: 0,
          transition: "transform 0.28s ease",
        }}
        onMouseEnter={(event) => {
          event.currentTarget.style.transform = "translateY(-1px)";
        }}
        onMouseLeave={(event) => {
          event.currentTarget.style.transform = "translateY(0)";
        }}
      >
        MY PAGE
      </button>

      <div
        aria-hidden={!isOpen}
        style={{
          position: "absolute",
          top: 54,
          right: -14,
          width: 238,
          background: "#FFFFFF",
          border: `2px solid ${BORDER}`,
          borderRadius: 18,
          boxSizing: "border-box",
          padding: "16px 16px 14px",
          pointerEvents: isOpen ? "auto" : "none",
          opacity: isOpen ? 1 : 0,
          transform: isOpen
            ? "translate3d(0, 0, 0) scale(1)"
            : "translate3d(0, -8px, 0) scale(0.985)",
          transformOrigin: "top right",
          transition:
            "opacity 0.22s ease, transform 0.32s cubic-bezier(0.16, 1, 0.3, 1)",
          boxShadow: "0 18px 58px rgba(0, 0, 0, 0.09)",
        }}
      >
        <div
          style={{
            padding: "2px 2px 14px",
            borderBottom: `1px solid ${BORDER}`,
            marginBottom: 8,
          }}
        >
          <div
            style={{
              fontFamily: "var(--font-ko)",
              fontSize: 14,
              fontWeight: 800,
              lineHeight: 1.2,
              letterSpacing: "-0.045em",
              color: BLACK,
            }}
          >
            {userName}님
          </div>
          <div
            style={{
              marginTop: 8,
              fontFamily: FONT_MONO,
              fontSize: 11,
              fontWeight: 700,
              lineHeight: 1.2,
              letterSpacing: "-0.02em",
              color: "rgba(21, 21, 21, 0.48)",
              whiteSpace: "nowrap",
              overflow: "hidden",
              textOverflow: "ellipsis",
            }}
          >
            {userEmail}
          </div>
        </div>

        {menuItems.map((item) => (
          <button
            key={item.path}
            type="button"
            onClick={() => {
              setIsOpen(false);
              onNavigate(item.path);
            }}
            style={{
              width: "100%",
              height: 34,
              border: "none",
              background: "#FFFFFF",
              cursor: "pointer",
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
              padding: "0 2px",
              boxSizing: "border-box",
              fontFamily: "var(--font-ko)",
              fontSize: 13,
              fontWeight: 800,
              letterSpacing: "-0.04em",
              color: BLACK,
              textAlign: "left",
              transition: "background 0.18s ease, padding 0.18s ease",
            }}
            onMouseEnter={(event) => {
              event.currentTarget.style.background = "rgba(21, 21, 21, 0.035)";
              event.currentTarget.style.padding = "0 8px";
            }}
            onMouseLeave={(event) => {
              event.currentTarget.style.background = "#FFFFFF";
              event.currentTarget.style.padding = "0 2px";
            }}
          >
            <span>{item.label}</span>
            <span aria-hidden="true">›</span>
          </button>
        ))}

        <div
          aria-hidden="true"
          style={{
            width: "100%",
            height: 1,
            background: BORDER,
            margin: "8px 0",
          }}
        />

        <button
          type="button"
          onClick={() => {
            setIsOpen(false);
            onLogout();
          }}
          style={{
            width: "100%",
            height: 36,
            border: "none",
            background: "#FFFFFF",
            cursor: "pointer",
            display: "flex",
            alignItems: "center",
            justifyContent: "space-between",
            padding: "0 2px",
            boxSizing: "border-box",
            fontFamily: "var(--font-ko)",
            fontSize: 13,
            fontWeight: 800,
            letterSpacing: "-0.04em",
            color: BLACK,
            textAlign: "left",
            transition:
              "background 0.18s ease, padding 0.18s ease, color 0.18s ease",
          }}
          onMouseEnter={(event) => {
            event.currentTarget.style.background = "rgba(252, 200, 0, 0.14)";
            event.currentTarget.style.padding = "0 8px";
          }}
          onMouseLeave={(event) => {
            event.currentTarget.style.background = "#FFFFFF";
            event.currentTarget.style.padding = "0 2px";
          }}
        >
          <span>로그아웃</span>
          <span aria-hidden="true">→</span>
        </button>
      </div>
    </div>
  );
}

function ShortMenuPanel({
  isOpen,
  onClose,
  isScrolled,
}: {
  isOpen: boolean;
  onClose: () => void;
  isScrolled: boolean;
}) {
  return (
    <div
      aria-hidden={!isOpen}
      style={{
        position: "absolute",
        top: 102,
        left: "50%",
        width: isScrolled ? 1440 : 820,
        maxWidth: "100%",
        height: isOpen ? 390 : 0,
        transform: isOpen
          ? "translateX(-50%) translateY(0)"
          : "translateX(-50%) translateY(-14px)",
        opacity: isOpen ? 1 : 0,
        overflow: "hidden",
        background: "#FFFFFF",
        border: `2px solid ${BORDER}`,
        /* Header Radius
   - 최상단: 플로팅 카드 형태라 radius 유지
   - 스크롤: 전체 폭 앱바 형태라 radius 제거 */
        borderRadius: isScrolled ? 0 : 20,
        boxSizing: "border-box",
        pointerEvents: isOpen ? "auto" : "none",
        transition:
          "height 0.48s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.28s ease, transform 0.48s cubic-bezier(0.16, 1, 0.3, 1)",
        zIndex: 3,
      }}
    >
      <div
        style={{
          height: 390,
          padding: "38px 44px 34px",
          display: "grid",
          gridTemplateColumns: "1fr 220px",
          gap: 48,
          boxSizing: "border-box",
        }}
      >
        <div
          style={{
            display: "flex",
            flexDirection: "column",
            justifyContent: "center",
            gap: 18,
          }}
        >
          {MENU_ITEMS.map((item, index) => (
            <a
              key={item.label}
              href={item.href}
              onClick={onClose}
              style={{
                fontFamily: "var(--font-en)",
                fontSize: 54,
                lineHeight: 0.92,
                color: BLACK,
                textDecoration: "none",
                letterSpacing: "-0.055em",
                transform: isOpen ? "translateY(0)" : "translateY(16px)",
                opacity: isOpen ? 1 : 0,
                transition: `opacity 0.36s ease ${index * 0.045}s, transform 0.42s cubic-bezier(0.16, 1, 0.3, 1) ${index * 0.045}s`,
              }}
            >
              {item.label}
            </a>
          ))}
        </div>

        <aside
          style={{
            alignSelf: "end",
            fontFamily: FONT_MONO,
            color: BLACK,
            fontSize: 12,
            lineHeight: 1.55,
            letterSpacing: "-0.02em",
            opacity: isOpen ? 1 : 0,
            transform: isOpen ? "translateY(0)" : "translateY(12px)",
            transition:
              "opacity 0.36s ease 0.18s, transform 0.42s cubic-bezier(0.16, 1, 0.3, 1) 0.18s",
          }}
        >
          <div style={{ fontWeight: 700, marginBottom: 14 }}>UNOTRAVEL</div>
          <div>Premium Semi Package</div>
          <div>Daily Tour Collection</div>
          <div style={{ marginTop: 18 }}>Italy · Spain · Portugal · Egypt</div>
        </aside>
      </div>
    </div>
  );
}

function RecentlyViewedPanel({
  isOpen,
  isScrolled,
  products,
  onClose,
  anchor,
}: {
  isOpen: boolean;
  isScrolled: boolean;
  products: RecentlyViewedProduct[];
  onClose: () => void;
  anchor: ViewedPanelAnchor | null;
}) {
  const count = products.length;
  const panelWidth = 360;
  const scrolledLeft =
    typeof window === "undefined" || !anchor
      ? 32
      : Math.min(
          Math.max(12, anchor.left),
          window.innerWidth - panelWidth - 12,
        );

  return (
    <div
      aria-hidden={!isOpen}
      style={{
        position: isScrolled && anchor ? "fixed" : "absolute",
        top: isScrolled && anchor ? anchor.bottom + 8 : 102,
        left: isScrolled && anchor ? scrolledLeft : undefined,
        right: isScrolled && anchor ? undefined : 84,
        width: panelWidth,
        maxHeight: 472,
        background: "#FFFFFF",
        border: `2px solid ${BORDER}`,
        borderRadius: isScrolled ? 16 : 20,
        boxSizing: "border-box",
        padding: "18px 18px 14px",
        pointerEvents: isOpen ? "auto" : "none",
        opacity: isOpen ? 1 : 0,
        overflow: "hidden",
        overflowX: "hidden",
        transform: isOpen
          ? "translate3d(0, 0, 0) scale(1)"
          : "translate3d(0, -10px, 0) scale(0.985)",
        transformOrigin: "top right",
        transition:
          "opacity 0.24s ease, transform 0.34s cubic-bezier(0.16, 1, 0.3, 1)",
        zIndex: isScrolled ? 1002 : 5,
        boxShadow: isScrolled
          ? "0 18px 54px rgba(0, 0, 0, 0.10)"
          : "0 24px 70px rgba(0, 0, 0, 0.10)",
      }}
    >
      <div
        style={{
          display: "flex",
          alignItems: "center",
          justifyContent: "space-between",
          paddingBottom: 14,
          borderBottom: `1px solid ${BORDER}`,
          fontFamily: FONT_MONO,
          color: BLACK,
        }}
      >
        <div
          style={{
            fontSize: 12,
            fontWeight: 700,
            letterSpacing: "0.08em",
          }}
        >
          RECENTLY VIEWED
        </div>
        <div
          style={{
            fontSize: 12,
            fontWeight: 700,
            letterSpacing: "0.04em",
          }}
        >
          {String(count).padStart(2, "0")}
        </div>
      </div>

      {count === 0 ? (
        <div
          style={{
            height: 158,
            display: "flex",
            flexDirection: "column",
            alignItems: "center",
            justifyContent: "center",
            gap: 14,
            color: "rgba(21, 21, 21, 0.42)",
            fontFamily: FONT_MONO,
            fontSize: 12,
            fontWeight: 700,
            letterSpacing: "-0.02em",
          }}
        >
          <div
            aria-hidden="true"
            style={{
              width: 28,
              height: 28,
              border: "2px solid rgba(21, 21, 21, 0.16)",
              borderRadius: 8,
              boxSizing: "border-box",
              position: "relative",
            }}
          >
            <span
              style={{
                position: "absolute",
                left: 7,
                top: -8,
                width: 10,
                height: 10,
                border: "2px solid rgba(21, 21, 21, 0.16)",
                borderBottom: "none",
                borderRadius: "10px 10px 0 0",
              }}
            />
          </div>
          최근 본 상품이 없습니다.
        </div>
      ) : (
        <div
          style={{
            maxHeight: 334,
            overflowY: count >= 5 ? "auto" : "hidden",
            overflowX: "hidden",
            scrollbarWidth: "none",
            msOverflowStyle: "none",
            paddingRight: 0,
          }}
        >
          {products.map((product) => (
            <button
              key={product.id}
              type="button"
              onClick={() => {
                onClose();
                navigateToRecentlyViewedProduct(product.href);
              }}
              style={{
                width: "100%",
                maxWidth: "100%",
                display: "grid",
                gridTemplateColumns: "64px minmax(0, 1fr)",
                alignItems: "center",
                gap: 12,
                minHeight: 96,
                padding: "12px 0",
                border: "none",
                borderBottom: `1px solid ${BORDER}`,
                background: "#FFFFFF",
                cursor: "pointer",
                textAlign: "left",
                color: BLACK,
                boxSizing: "border-box",
                overflow: "hidden",
                transition: "background 0.22s ease",
              }}
              onMouseEnter={(event) => {
                event.currentTarget.style.background =
                  "rgba(21, 21, 21, 0.025)";
              }}
              onMouseLeave={(event) => {
                event.currentTarget.style.background = "#FFFFFF";
              }}
            >
              <div
                style={{
                  width: 64,
                  height: 72,
                  borderRadius: 12,
                  overflow: "hidden",
                  background: "#F3F3F1",
                  flexShrink: 0,
                }}
              >
                {product.thumbnail ? (
                  <img
                    src={product.thumbnail}
                    alt=""
                    style={{
                      width: "100%",
                      height: "100%",
                      objectFit: "cover",
                    }}
                  />
                ) : null}
              </div>

              <div
                style={{
                  minWidth: 0,
                  display: "flex",
                  flexDirection: "column",
                  justifyContent: "center",
                  gap: 7,
                }}
              >
                <div
                  style={{
                    fontFamily: FONT_MONO,
                    fontSize: 11,
                    fontWeight: 700,
                    lineHeight: 1,
                    letterSpacing: "0.08em",
                    color: "rgba(21, 21, 21, 0.52)",
                    whiteSpace: "nowrap",
                    overflow: "hidden",
                    textOverflow: "ellipsis",
                  }}
                >
                  {formatRecentlyViewedCategory(product)}
                </div>
                <div
                  style={{
                    fontFamily: "var(--font-kr, var(--font-en))",
                    fontSize: 14,
                    fontWeight: 700,
                    lineHeight: 1.28,
                    letterSpacing: "-0.04em",
                    whiteSpace: "nowrap",
                    overflow: "hidden",
                    textOverflow: "ellipsis",
                  }}
                >
                  {product.title}
                </div>
              </div>
            </button>
          ))}
        </div>
      )}

      {count > 0 && (
        <button
          type="button"
          onClick={() => {
            onClose();
            navigateToRecentlyViewedProduct(products[0].href);
          }}
          style={{
            width: "100%",
            height: 46,
            marginTop: 2,
            border: "none",
            background: "#FFFFFF",
            color: BLACK,
            fontFamily: FONT_MONO,
            fontSize: 12,
            fontWeight: 700,
            letterSpacing: "-0.02em",
            cursor: "pointer",
          }}
        >
          최근 본 상품으로 이동&nbsp;&nbsp;›
        </button>
      )}
    </div>
  );
}

function ExpandedMenuCard({
  isOpen,
  onClose,
}: {
  isOpen: boolean;
  onClose: () => void;
}) {
  return (
    <div
      aria-hidden={!isOpen}
      style={{
        position: "absolute",
        top: 124,
        right: 0,
        width: 625,
        height: 690,
        background: "#FFFFFF",
        borderRadius: 18,
        boxSizing: "border-box",
        padding: "38px 44px",
        pointerEvents: isOpen ? "auto" : "none",
        opacity: isOpen ? 1 : 0,
        transform: isOpen
          ? "translate3d(0, 0, 0) scale(1)"
          : "translate3d(24px, -12px, 0) scale(0.985)",
        transformOrigin: "top right",
        transition:
          "opacity 0.34s ease, transform 0.52s cubic-bezier(0.16, 1, 0.3, 1)",
        zIndex: 3,
        boxShadow: "0 28px 80px rgba(0, 0, 0, 0.10)",
      }}
    >
      <button
        aria-label="Close menu"
        onClick={onClose}
        style={{
          position: "absolute",
          top: 28,
          right: 32,
          width: 46,
          height: 46,
          borderRadius: "50%",
          border: "none",
          background: BLACK,
          color: "#FFFFFF",
          cursor: "pointer",
          fontFamily: FONT_MONO,
          fontSize: 22,
          lineHeight: 1,
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          padding: 0,
        }}
      >
        ×
      </button>

      <div
        style={{
          position: "absolute",
          left: 44,
          top: 52,
          fontFamily: FONT_MONO,
          fontSize: 12,
          fontWeight: 700,
          letterSpacing: "0.08em",
          color: BLACK,
        }}
      >
        MENU
      </div>

      <div
        style={{
          height: "100%",
          display: "flex",
          flexDirection: "column",
          justifyContent: "center",
          gap: 24,
        }}
      >
        {MENU_ITEMS.map((item, index) => (
          <a
            key={item.label}
            href={item.href}
            onClick={onClose}
            style={{
              fontFamily: "var(--font-en)",
              fontSize: 76,
              lineHeight: 0.88,
              color: BLACK,
              textDecoration: "none",
              letterSpacing: "-0.06em",
              opacity: isOpen ? 1 : 0,
              transform: isOpen ? "translateX(0)" : "translateX(18px)",
              transition: `opacity 0.38s ease ${index * 0.055}s, transform 0.48s cubic-bezier(0.16, 1, 0.3, 1) ${index * 0.055}s`,
            }}
          >
            {item.label}
          </a>
        ))}
      </div>

      <div
        style={{
          position: "absolute",
          left: 44,
          bottom: 34,
          fontFamily: FONT_MONO,
          fontSize: 12,
          lineHeight: 1.45,
          color: BLACK,
        }}
      >
        Italy · Spain · Portugal · Egypt
      </div>
    </div>
  );
}

export default function Header({
  isLoggedIn = false,
  userName = "",
}: HeaderProps) {
  const navRef = useRef<HTMLElement | null>(null);
  const viewedButtonRef = useRef<HTMLButtonElement | null>(null);
  const infoButtonRef = useRef<HTMLButtonElement | null>(null);
  const [viewedPanelAnchor, setViewedPanelAnchor] =
    useState<ViewedPanelAnchor | null>(null);
  const [infoPanelAnchor, setInfoPanelAnchor] =
    useState<ViewedPanelAnchor | null>(null);
  const [isScrolled, setIsScrolled] = useState(false);

  /* Header Width Motion
     - 스크롤 감지는 즉시 처리
     - Header 가로 확장은 짧은 지연 후 실행해서 갑작스럽게 펼쳐지는 느낌을 완화 */
  const [isHeaderExpanded, setIsHeaderExpanded] = useState(false);

  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [isViewedOpen, setIsViewedOpen] = useState(false);
  const [isInfoOpen, setIsInfoOpen] = useState(false);
  const [recentlyViewedProducts, setRecentlyViewedProducts] = useState<
    RecentlyViewedProduct[]
  >([]);
  const [authState, setAuthState] = useState<HeaderAuthState>(() =>
    getHeaderAuthState(isLoggedIn, userName),
  );
  const viewedAutoCloseTimerRef = useRef<number | null>(null);
  const infoCloseTimerRef = useRef<number | null>(null);
  const [hoveredItem, setHoveredItem] = useState<string | null>(null);
  const [lantern, setLantern] = useState<LanternState>({
    x: 0,
    y: 0,
    visible: false,
  });

  useEffect(() => {
    const syncHeaderAuthState = () => {
      setAuthState(getHeaderAuthState(isLoggedIn, userName));
    };

    syncHeaderAuthState();
    window.addEventListener("storage", syncHeaderAuthState);
    window.addEventListener("unotravel:auth-change", syncHeaderAuthState);

    return () => {
      window.removeEventListener("storage", syncHeaderAuthState);
      window.removeEventListener("unotravel:auth-change", syncHeaderAuthState);
    };
  }, [isLoggedIn, userName]);

  function updateViewedPanelAnchorFromElement(element?: HTMLElement | null) {
    if (typeof window === "undefined") return;

    const target = element ?? viewedButtonRef.current;
    if (!target) return;

    const rect = target.getBoundingClientRect();

    setViewedPanelAnchor({
      left: rect.left,
      top: rect.top,
      bottom: rect.bottom,
      width: rect.width,
    });
  }


  function updateInfoPanelAnchorFromElement(element?: HTMLElement | null) {
    if (typeof window === "undefined") return;

    const target = element ?? infoButtonRef.current;
    if (!target) return;

    const rect = target.getBoundingClientRect();

    setInfoPanelAnchor({
      left: rect.left,
      top: rect.top,
      bottom: rect.bottom,
      width: rect.width,
    });
  }

  function openInfoPanel(element?: HTMLElement | null) {
    if (infoCloseTimerRef.current) {
      window.clearTimeout(infoCloseTimerRef.current);
      infoCloseTimerRef.current = null;
    }

    updateInfoPanelAnchorFromElement(element);
    setIsMenuOpen(false);
    setIsViewedOpen(false);
    setIsInfoOpen(true);
  }

  function scheduleCloseInfoPanel() {
    if (infoCloseTimerRef.current) {
      window.clearTimeout(infoCloseTimerRef.current);
    }

    infoCloseTimerRef.current = window.setTimeout(() => {
      setIsInfoOpen(false);
    }, 120);
  }

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 12);
    };

    handleScroll();
    window.addEventListener("scroll", handleScroll, { passive: true });

    return () => {
      window.removeEventListener("scroll", handleScroll);
    };
  }, []);

  useEffect(() => {
    /* Header Width Motion
       - 내려갈 때: top 이동 후 0.16초 뒤 가로 폭 확장
       - 다시 최상단으로 갈 때: 바로 820px 카드로 복귀 */
    if (!isScrolled) {
      setIsHeaderExpanded(false);
      return;
    }

    const expandTimer = window.setTimeout(() => {
      setIsHeaderExpanded(true);
    }, 160);

    return () => {
      window.clearTimeout(expandTimer);
    };
  }, [isScrolled]);

  useEffect(() => {
    const syncRecentlyViewedProducts = () => {
      const previousCount = recentlyViewedProducts.length;
      const nextProducts = getRecentlyViewedProducts();

      setRecentlyViewedProducts(nextProducts);

      if (previousCount === 0 && nextProducts.length > 0) {
        const alreadyOpened = window.sessionStorage.getItem(
          RECENTLY_VIEWED_FIRST_OPEN_KEY,
        );

        if (!alreadyOpened) {
          window.sessionStorage.setItem(RECENTLY_VIEWED_FIRST_OPEN_KEY, "true");
          setIsMenuOpen(false);
          setIsViewedOpen(true);

          if (viewedAutoCloseTimerRef.current) {
            window.clearTimeout(viewedAutoCloseTimerRef.current);
          }

          viewedAutoCloseTimerRef.current = window.setTimeout(() => {
            setIsViewedOpen(false);
          }, 2600);
        }
      }
    };

    syncRecentlyViewedProducts();
    window.addEventListener("storage", syncRecentlyViewedProducts);
    window.addEventListener(
      "unotravel:recently-viewed-updated",
      syncRecentlyViewedProducts,
    );

    return () => {
      window.removeEventListener("storage", syncRecentlyViewedProducts);
      window.removeEventListener(
        "unotravel:recently-viewed-updated",
        syncRecentlyViewedProducts,
      );

      if (viewedAutoCloseTimerRef.current) {
        window.clearTimeout(viewedAutoCloseTimerRef.current);
      }
    };
  }, [recentlyViewedProducts.length]);

  useEffect(() => {
    if (!isViewedOpen) return;

    updateViewedPanelAnchorFromElement();

    const handlePositionUpdate = () => {
      updateViewedPanelAnchorFromElement();
    };

    window.addEventListener("resize", handlePositionUpdate);
    window.addEventListener("scroll", handlePositionUpdate, { passive: true });

    return () => {
      window.removeEventListener("resize", handlePositionUpdate);
      window.removeEventListener("scroll", handlePositionUpdate);
    };
  }, [isViewedOpen, isScrolled, isHeaderExpanded]);


  useEffect(() => {
    if (!isInfoOpen) return;

    updateInfoPanelAnchorFromElement();

    const handlePositionUpdate = () => {
      updateInfoPanelAnchorFromElement();
    };

    window.addEventListener("resize", handlePositionUpdate);
    window.addEventListener("scroll", handlePositionUpdate, { passive: true });

    return () => {
      window.removeEventListener("resize", handlePositionUpdate);
      window.removeEventListener("scroll", handlePositionUpdate);
    };
  }, [isInfoOpen, isScrolled, isHeaderExpanded]);


  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        setIsMenuOpen(false);
        setIsViewedOpen(false);
        setIsInfoOpen(false);
      }
    };

    window.addEventListener("keydown", handleKeyDown);

    return () => {
      if (infoCloseTimerRef.current) {
        window.clearTimeout(infoCloseTimerRef.current);
      }

      window.removeEventListener("keydown", handleKeyDown);
    };
  }, []);

  const shortNavItems = authState.isLoggedIn
    ? (["VIEWED", "INFO", "CONTACT"] as const)
    : (["VIEWED", "INFO", "CONTACT", "LOGIN"] as const);

  const scrolledPrimaryNavItems = ["VIEWED", "INFO", "CONTACT"] as const;

  function moveLanternToButton(
    event: React.MouseEvent<HTMLButtonElement>,
    item: string,
  ) {
    const navRect = navRef.current?.getBoundingClientRect();
    const buttonRect = event.currentTarget.getBoundingClientRect();

    if (!navRect) return;

    setHoveredItem(item);
    setLantern({
      x: buttonRect.left - navRect.left + buttonRect.width / 2,
      y: buttonRect.top - navRect.top + buttonRect.height / 2,
      visible: true,
    });
  }

  function hideLantern() {
    setHoveredItem(null);
    setLantern((prev) => ({
      ...prev,
      visible: false,
    }));
  }

  function handleLogout() {
    if (typeof window === "undefined") return;

    window.sessionStorage.removeItem("unotravel:auth");
    window.sessionStorage.removeItem("unotravel_auth");
    window.sessionStorage.removeItem("unotravel:user-name");
    window.sessionStorage.removeItem("unotravel:user-email");
    window.sessionStorage.removeItem("unotravel:email");
    window.dispatchEvent(new Event("unotravel:auth-change"));
    setAuthState({ isLoggedIn: false, userName: "", userEmail: "hong@example.com" });
    setIsMenuOpen(false);
    setIsViewedOpen(false);
    setIsInfoOpen(false);
    navigateTo("/");
  }

  function renderNavButton(item: string, width: number) {
    const isViewedButton = item === "VIEWED";
    const isLoginButton = item === "LOGIN";
    const isInfoButton = item === "INFO";
    const isMyPageButton = item === "MY PAGE";
    const hasRecentlyViewed = recentlyViewedProducts.length > 0;

    return (
      <HeaderButton
        key={item}
        width={width}
        isHovered={hoveredItem === item}
        buttonRef={
          isViewedButton
            ? viewedButtonRef
            : isInfoButton
              ? infoButtonRef
              : undefined
        }
        onClick={
          isViewedButton
            ? () => {
                updateViewedPanelAnchorFromElement(viewedButtonRef.current);
                setIsMenuOpen(false);
                setIsViewedOpen((prev) => !prev);
              }
            : isLoginButton
              ? () => {
                  setIsMenuOpen(false);
                  setIsViewedOpen(false);
                  navigateTo("/login");
                }
              : isInfoButton
                ? () => {
                    setIsMenuOpen(false);
                    setIsViewedOpen(false);
                    setIsInfoOpen(false);
                    navigateTo("/info");
                  }
                : isMyPageButton
                  ? () => {
                      setIsMenuOpen(false);
                      setIsViewedOpen(false);
                      setIsInfoOpen(false);
                      navigateTo("/mypage");
                    }
                  : undefined
        }
        onMouseEnter={(event) => {
          moveLanternToButton(event, item);

          if (isViewedButton) {
            updateViewedPanelAnchorFromElement(event.currentTarget);
            setIsMenuOpen(false);
            setIsInfoOpen(false);
            setIsViewedOpen(true);
          }

          if (isInfoButton) {
            openInfoPanel(event.currentTarget);
          }
        }}
      >
        <span
          style={{
            position: "relative",
            display: "inline-flex",
            alignItems: "center",
          }}
        >
          {item}
          {isViewedButton && hasRecentlyViewed && (
            <span
              aria-hidden="true"
              style={{
                width: 6,
                height: 6,
                borderRadius: "50%",
                background: BLACK,
                marginLeft: 7,
                transform: "translateY(-5px)",
              }}
            />
          )}
        </span>
      </HeaderButton>
    );
  }

  return (
    <div
      style={{
        position: "fixed",
        top: isScrolled ? 0 : 20,
        left: 0,
        zIndex: 1000,

        /* Header Root
           - 최상단: 내부 Header 카드(820px)를 화면 중앙에 배치
           - 스크롤: 100vw가 아닌 부모 기준 100% 사용
           - 100vw로 인한 브라우저 스크롤바 포함 가로 넘침 방지 */
        width: "100%",

        display: "flex",
        justifyContent: "center",
        pointerEvents: "none",
        /* Header Position Motion
           - 먼저 상단으로 이동한 뒤 width가 따라오도록 분리 */
        transition: "top 0.35s cubic-bezier(0.22, 1, 0.36, 1)",
      }}
    >
      {(isMenuOpen || isViewedOpen) && (
        <button
          aria-label={
            isMenuOpen ? "Close menu overlay" : "Close recently viewed panel"
          }
          onClick={() => {
            setIsMenuOpen(false);
            setIsViewedOpen(false);
          }}
          style={{
            position: "fixed",
            inset: 0,
            border: "none",
            background:
              isMenuOpen && isScrolled ? "rgba(0, 0, 0, 0.14)" : "transparent",
            padding: 0,
            pointerEvents: "auto",
            cursor: "default",
            zIndex: 1,
          }}
        />
      )}

      <div
        style={{
          position: "relative",

          /* Header Width Controller
             - 최상단: 820px 고정 카드
             - 스크롤: top 이동 후 0.16초 뒤 부모 기준 100%로 부드럽게 확장
             - Section3 Horizontal Slider 외에는 100vw 사용 금지 */
          width: isHeaderExpanded ? "100%" : 820,
          maxWidth: isHeaderExpanded ? "100%" : 820,

          margin: "0 auto",
          pointerEvents: "none",
          transition:
            "width 0.72s cubic-bezier(0.22, 1, 0.36, 1), max-width 0.72s cubic-bezier(0.22, 1, 0.36, 1)",
        }}
      >
        <header
          style={{
            /* Header Size
       - 최상단: 820px x 90px
       - 스크롤: 100% x 110px */
            width: "100%",
            maxWidth: "100%",
            height: isScrolled ? 110 : 90,

            background: "#FFFFFF",

            border: isScrolled ? "0 solid transparent" : `2px solid ${BORDER}`,

            /* Header Radius
   - 최상단: 플로팅 카드 형태라 radius 유지
   - 스크롤: 전체 폭 앱바 형태라 radius 제거 */
            borderRadius: isScrolled ? 0 : 20,

            display: "flex",
            flexDirection: "row",
            justifyContent: "space-between",
            alignItems: "center",

            padding: "10px 20px",

            gap: 10,

            boxSizing: "border-box",

            pointerEvents: "auto",

            position: "relative",

            zIndex: 4,

            transition:
              "height 0.48s cubic-bezier(0.22, 1, 0.36, 1), border .25s ease, border-radius .42s ease",
          }}
        >
          <Logo />

          <nav
            ref={navRef}
            aria-label="Main navigation"
            onMouseLeave={() => {
              hideLantern();
              scheduleCloseInfoPanel();
            }}
            style={{
              display: "flex",
              flexDirection: "row",
              alignItems: isScrolled ? "flex-start" : "center",
              padding: 10,
              gap: isScrolled ? 10 : 20,
              width: isScrolled
                ? scrolledPrimaryNavItems.length * 100 +
                  84 +
                  (authState.isLoggedIn ? 106 : 100)
                : authState.isLoggedIn
                  ? 464
                  : 448,
              height: isScrolled ? 50 : 70,
              background: "#FFFFFF",
              boxSizing: "border-box",
              position: "relative",
              overflow: "visible",
              transition: "width 0.42s ease, height 0.42s ease, gap 0.42s ease",
            }}
          >
            <span
              aria-hidden="true"
              style={{
                position: "absolute",
                left: 0,
                top: 0,
                width: 150,
                height: 74,
                borderRadius: "999px",
                pointerEvents: "none",
                background:
                  "radial-gradient(circle at center, rgba(21, 21, 21, 0.13) 0%, rgba(21, 21, 21, 0.07) 34%, rgba(21, 21, 21, 0) 72%)",
                filter: "blur(8px)",
                opacity: lantern.visible && !isMenuOpen ? 1 : 0,
                transform: `translate3d(${lantern.x - 75}px, ${lantern.y - 37}px, 0)`,
                transition:
                  "transform 0.42s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.28s ease",
                zIndex: 1,
              }}
            />

            {isScrolled ? (
              <>
                {scrolledPrimaryNavItems.map((item) =>
                  renderNavButton(item, 100),
                )}

                {authState.isLoggedIn ? (
                  <UserBlock
                    userName={authState.userName}
                    userEmail={authState.userEmail}
                    onNavigate={navigateTo}
                    onLogout={handleLogout}
                  />
                ) : (
                  renderNavButton("LOGIN", 100)
                )}

                <DotGrid
                  isOpen={isMenuOpen}
                  onClick={() => {
                    setIsViewedOpen(false);
                    setIsInfoOpen(false);
                    setIsMenuOpen((prev) => !prev);
                  }}
                />
              </>
            ) : (
              <>
                {shortNavItems.map((item) =>
                  renderNavButton(item, item === "CONTACT" ? 84 : 80),
                )}

                {authState.isLoggedIn && (
                  <UserBlock
                    userName={authState.userName}
                    userEmail={authState.userEmail}
                    onNavigate={navigateTo}
                    onLogout={handleLogout}
                  />
                )}

                <DotGrid
                  isOpen={isMenuOpen}
                  onClick={() => {
                    setIsViewedOpen(false);
                    setIsInfoOpen(false);
                    setIsMenuOpen((prev) => !prev);
                  }}
                />
              </>
            )}
          </nav>
        </header>

        <InfoDropdownPanel
          isOpen={isInfoOpen}
          isScrolled={isScrolled}
          anchor={infoPanelAnchor}
          onNavigate={(path) => {
            setIsInfoOpen(false);
            navigateTo(path);
          }}
          onMouseEnter={() => {
            if (infoCloseTimerRef.current) {
              window.clearTimeout(infoCloseTimerRef.current);
              infoCloseTimerRef.current = null;
            }
          }}
          onMouseLeave={scheduleCloseInfoPanel}
        />

        <RecentlyViewedPanel
          isOpen={isViewedOpen}
          isScrolled={isScrolled}
          products={recentlyViewedProducts}
          onClose={() => setIsViewedOpen(false)}
          anchor={viewedPanelAnchor}
        />

        <ShortMenuPanel
          isOpen={isMenuOpen && !isScrolled}
          onClose={() => setIsMenuOpen(false)}
          isScrolled={isScrolled}
        />
        <ExpandedMenuCard
          isOpen={isMenuOpen && isScrolled}
          onClose={() => setIsMenuOpen(false)}
        />
      </div>
    </div>
  );
}
