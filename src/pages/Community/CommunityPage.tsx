import { useEffect } from "react";

const BLACK = "#151515";
const BORDER = "#E8E9E9";
const FONT_MONO = "var(--font-en)";

const COMMUNITY_HUB_ITEMS = [
  {
    label: "공지사항",
    labelEn: "NOTICE",
    description: "예약, 운영, 상품 관련 주요 안내를 확인합니다.",
    href: "/community/notice",
  },
  {
    label: "이벤트",
    labelEn: "EVENT",
    description: "진행 중인 프로모션과 시즌 이벤트를 확인합니다.",
    href: "/community/event",
  },
  {
    label: "FAQ",
    labelEn: "FAQ",
    description: "자주 묻는 질문과 기본 안내를 확인합니다.",
    href: "/community/faq",
  },
  {
    label: "여행후기",
    labelEn: "REVIEW",
    description: "우노트래블을 경험한 여행자의 기록을 확인합니다.",
    href: "/community/review",
  },
] as const;

function navigateTo(path: string) {
  if (typeof window === "undefined") return;

  if (window.location.pathname === path) {
    window.scrollTo({
      top: 0,
      left: 0,
      behavior: "smooth",
    });
    return;
  }

  window.history.pushState({}, "", path);
  window.scrollTo({
    top: 0,
    left: 0,
    behavior: "auto",
  });
  window.dispatchEvent(new Event("unotravel:navigate"));
}

export default function CommunityPage() {
  useEffect(() => {
    document.title = "Community | UNO TRAVEL";
  }, []);

  return (
    <main
      style={{
        width: "100%",
        minHeight: "100vh",
        background: "#FFFFFF",
        color: BLACK,
      }}
    >
      <section
        style={{
          width: "100%",
          maxWidth: 1440,
          margin: "0 auto",
          padding: "188px 48px 80px",
          boxSizing: "border-box",
        }}
      >
        <div
          style={{
            display: "grid",
            gridTemplateColumns: "minmax(0, 1fr) 360px",
            gap: 80,
            alignItems: "end",
            borderBottom: `2px solid ${BORDER}`,
            paddingBottom: 58,
          }}
        >
          <div>
            <div
              style={{
                fontFamily: FONT_MONO,
                fontSize: 12,
                fontWeight: 800,
                letterSpacing: "0.16em",
                color: "rgba(21, 21, 21, 0.46)",
                marginBottom: 28,
              }}
            >
              COMMUNITY
            </div>

            <h1
              style={{
                margin: 0,
                fontFamily: "var(--font-ko)",
                fontSize: 84,
                fontWeight: 800,
                lineHeight: 0.94,
                letterSpacing: "-0.085em",
              }}
            >
              여행 전후의
              <br />
              필요한 기록들
            </h1>
          </div>

          <p
            style={{
              margin: 0,
              fontFamily: "var(--font-ko)",
              fontSize: 15,
              fontWeight: 700,
              lineHeight: 1.72,
              letterSpacing: "-0.045em",
              color: "rgba(21, 21, 21, 0.56)",
            }}
          >
            여행후기, 공지사항, 이벤트, FAQ를 한 곳에서 확인하는
            UNO TRAVEL 커뮤니티 허브입니다.
          </p>
        </div>
      </section>

      <section
        style={{
          width: "100%",
          maxWidth: 1440,
          margin: "0 auto",
          padding: "0 48px 120px",
          boxSizing: "border-box",
        }}
      >
        <div
          style={{
            display: "grid",
            gridTemplateColumns: "repeat(4, minmax(0, 1fr))",
            borderTop: `1px solid ${BORDER}`,
            borderLeft: `1px solid ${BORDER}`,
          }}
        >
          {COMMUNITY_HUB_ITEMS.map((item, index) => (
            <button
              key={item.href}
              type="button"
              onClick={() => navigateTo(item.href)}
              style={{
                minHeight: 360,
                border: "none",
                borderRight: `1px solid ${BORDER}`,
                borderBottom: `1px solid ${BORDER}`,
                background: "#FFFFFF",
                cursor: "pointer",
                padding: 30,
                boxSizing: "border-box",
                textAlign: "left",
                display: "flex",
                flexDirection: "column",
                justifyContent: "space-between",
                color: BLACK,
                transition: "background 0.22s ease, padding 0.22s ease",
              }}
              onMouseEnter={(event) => {
                event.currentTarget.style.background = "rgba(21, 21, 21, 0.025)";
                event.currentTarget.style.padding = "30px 30px 36px";
              }}
              onMouseLeave={(event) => {
                event.currentTarget.style.background = "#FFFFFF";
                event.currentTarget.style.padding = "30px";
              }}
            >
              <div
                style={{
                  fontFamily: FONT_MONO,
                  fontSize: 12,
                  fontWeight: 800,
                  letterSpacing: "0.12em",
                  color: "rgba(21, 21, 21, 0.42)",
                }}
              >
                {String(index + 1).padStart(2, "0")} / {item.labelEn}
              </div>

              <div>
                <h2
                  style={{
                    margin: 0,
                    fontFamily: "var(--font-ko)",
                    fontSize: 34,
                    fontWeight: 800,
                    lineHeight: 1,
                    letterSpacing: "-0.07em",
                  }}
                >
                  {item.label}
                </h2>

                <p
                  style={{
                    margin: "18px 0 0",
                    fontFamily: "var(--font-ko)",
                    fontSize: 14,
                    fontWeight: 700,
                    lineHeight: 1.62,
                    letterSpacing: "-0.045em",
                    color: "rgba(21, 21, 21, 0.52)",
                  }}
                >
                  {item.description}
                </p>
              </div>

              <div
                style={{
                  fontFamily: FONT_MONO,
                  fontSize: 12,
                  fontWeight: 800,
                  letterSpacing: "0.08em",
                }}
              >
                ENTER&nbsp;&nbsp;›
              </div>
            </button>
          ))}
        </div>
      </section>
    </main>
  );
}
