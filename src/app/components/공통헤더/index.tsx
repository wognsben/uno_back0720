import { useEffect, useRef, useState, type ReactNode } from "react";
import imgImage21 from "./82946044a68c061ee150f4ee5fd02f4ec778c1b7.png";

/* ────────────────────────────────────────────
   공통 HEADER
   Premium Editorial Header

   수정 메모
   - 최상단 Header : 820px 카드형 중앙 배치
   - 스크롤 Header : 100vw 전체 화면 폭
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

function DotGrid({ isOpen, onClick }: { isOpen: boolean; onClick: () => void }) {
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
          const openVisible = i === 0 || i === 2 || i === 4 || i === 6 || i === 8;
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
    <a
      href="/"
      aria-label="UNOTRAVEL home"
      style={{
        width: 80,
        height: 46,
        display: "flex",
        flexDirection: "column",
        alignItems: "flex-start",
        justifyContent: "center",
        flexShrink: 0,
        textDecoration: "none",
        color: BLACK,
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
    </a>
  );
}

function HeaderButton({
  children,
  width,
  isHovered = false,
  onClick,
  onMouseEnter,
}: {
  children: ReactNode;
  width: number;
  isHovered?: boolean;
  onClick?: () => void;
  onMouseEnter?: (event: React.MouseEvent<HTMLButtonElement>) => void;
}) {
  return (
    <button
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

function UserBlock({ userName }: { userName: string }) {
  return (
    <div
      style={{
        display: "flex",
        flexDirection: "column",
        alignItems: "flex-start",
        gap: 4,
        width: 80,
        height: 44,
        justifyContent: "center",
        background: "transparent",
        position: "relative",
        zIndex: 4,
      }}
    >
      <span
        style={{
          width: 80,
          height: 20,
          fontFamily: FONT_MONO,
          fontWeight: 700,
          fontSize: 11,
          lineHeight: 1,
          display: "flex",
          alignItems: "center",
          color: BLACK,
          whiteSpace: "nowrap",
        }}
      >
        {userName}님
      </span>
      <button
        style={{
          width: 80,
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
    </div>
  );
}

function ShortMenuPanel({ isOpen, onClose, isScrolled }: { isOpen: boolean; onClose: () => void; isScrolled: boolean }) {
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
        transform: isOpen ? "translateX(-50%) translateY(0)" : "translateX(-50%) translateY(-14px)",
        opacity: isOpen ? 1 : 0,
        overflow: "hidden",
        background: "#FFFFFF",
        border: `2px solid ${BORDER}`,
        borderRadius: 20,
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
        <div style={{ display: "flex", flexDirection: "column", justifyContent: "center", gap: 18 }}>
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
            transition: "opacity 0.36s ease 0.18s, transform 0.42s cubic-bezier(0.16, 1, 0.3, 1) 0.18s",
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

function ExpandedMenuCard({ isOpen, onClose }: { isOpen: boolean; onClose: () => void }) {
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
        transform: isOpen ? "translate3d(0, 0, 0) scale(1)" : "translate3d(24px, -12px, 0) scale(0.985)",
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

export default function Header({ isLoggedIn = false, userName = "김민수" }: HeaderProps) {
  const navRef = useRef<HTMLElement | null>(null);
  const [isScrolled, setIsScrolled] = useState(false);

  /* Header Width Motion
     - 스크롤 감지는 즉시 처리
     - Header 가로 확장은 짧은 지연 후 실행해서 갑작스럽게 펼쳐지는 느낌을 완화 */
  const [isHeaderExpanded, setIsHeaderExpanded] = useState(false);

  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [hoveredItem, setHoveredItem] = useState<string | null>(null);
  const [lantern, setLantern] = useState<LanternState>({
    x: 0,
    y: 0,
    visible: false,
  });

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
    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === "Escape") setIsMenuOpen(false);
    };

    window.addEventListener("keydown", handleKeyDown);

    return () => {
      window.removeEventListener("keydown", handleKeyDown);
    };
  }, []);

  const shortNavItems = isLoggedIn
    ? (["VIEWED", "INFO", "CONTACT"] as const)
    : (["VIEWED", "INFO", "CONTACT", "LOGIN"] as const);

  const expandedNavItems = isLoggedIn
    ? (["MY PAGE", "CONTACT", "INFO", "VIEWED"] as const)
    : (["LOGIN", "MY PAGE", "CONTACT", "INFO"] as const);

  function moveLanternToButton(event: React.MouseEvent<HTMLButtonElement>, item: string) {
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

  function renderNavButton(item: string, width: number) {
    return (
      <HeaderButton
        key={item}
        width={width}
        isHovered={hoveredItem === item}
        onMouseEnter={(event) => moveLanternToButton(event, item)}
      >
        {item}
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
           - 스크롤: Header가 실제 viewport 전체 폭(100vw)을 사용 */
        width: "100vw",

        display: "flex",
        justifyContent: "center",
        pointerEvents: "none",
        /* Header Position Motion
           - 먼저 상단으로 이동한 뒤 width가 따라오도록 분리 */
        transition: "top 0.35s cubic-bezier(0.22, 1, 0.36, 1)",
      }}
    >
      {isMenuOpen && (
        <button
          aria-label="Close menu overlay"
          onClick={() => setIsMenuOpen(false)}
          style={{
            position: "fixed",
            inset: 0,
            border: "none",
            background: isScrolled ? "rgba(0, 0, 0, 0.14)" : "transparent",
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
             - 스크롤: top 이동 후 0.16초 뒤 100vw로 부드럽게 확장 */
          width: isHeaderExpanded ? "100vw" : 820,
          maxWidth: isHeaderExpanded ? "100vw" : 820,

          margin: "0 auto",
          pointerEvents: "none",
          transition: "width 0.72s cubic-bezier(0.22, 1, 0.36, 1), max-width 0.72s cubic-bezier(0.22, 1, 0.36, 1)",
        }}
      >
      <header
  style={{
    /* Header Size
       - 최상단: 820px x 90px
       - 스크롤: 100vw x 110px */
    width: "100%",
    maxWidth: "100%",
    height: isScrolled ? 110 : 90,

    background: "#FFFFFF",

    border: isScrolled
      ? "0 solid transparent"
      : `2px solid ${BORDER}`,

    borderRadius: 20,

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
          onMouseLeave={hideLantern}
          style={{
            display: "flex",
            flexDirection: "row",
            alignItems: isScrolled ? "flex-start" : "center",
            padding: 10,
            gap: isScrolled ? 10 : 20,
            width: isScrolled ? 484 : 448,
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
              {expandedNavItems.map((item) => renderNavButton(item, 100))}
              <DotGrid isOpen={isMenuOpen} onClick={() => setIsMenuOpen((prev) => !prev)} />
            </>
          ) : (
            <>
              {shortNavItems.map((item) => renderNavButton(item, item === "CONTACT" ? 84 : 80))}

              {isLoggedIn && <UserBlock userName={userName} />}

              <DotGrid isOpen={isMenuOpen} onClick={() => setIsMenuOpen((prev) => !prev)} />
            </>
          )}
        </nav>
      </header>

      <ShortMenuPanel
        isOpen={isMenuOpen && !isScrolled}
        onClose={() => setIsMenuOpen(false)}
        isScrolled={isScrolled}
      />
      <ExpandedMenuCard isOpen={isMenuOpen && isScrolled} onClose={() => setIsMenuOpen(false)} />
      </div>
    </div>
  );
}