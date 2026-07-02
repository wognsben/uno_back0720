import React, { useRef } from "react";


const infoDocumentStyles = `
.uno-info-document {
  width: 100%;
  min-height: 100vh;
  padding: 150px 28px 132px;
  background: #ffffff;
  color: #111111;
  box-sizing: border-box;
}

.uno-info-document * {
  box-sizing: border-box;
}

.uno-info-shell {
  width: min(1180px, 100%);
  margin: 0 auto;
  border-left: 1px solid rgba(17, 17, 17, 0.14);
  border-right: 1px solid rgba(17, 17, 17, 0.14);
  background:
    linear-gradient(to right, rgba(17, 17, 17, 0.045) 1px, transparent 1px) 0 0 / calc(100% / 12) 100%,
    #ffffff;
}

.uno-info-split {
  opacity: 0;
  will-change: transform;
}

.uno-info-split * {
  will-change: transform;
}

.uno-info-split-line {
  overflow: hidden;
  padding-bottom: 0.08em;
}

.uno-info-hero {
  min-height: 520px;
  border-top: 1px solid rgba(17, 17, 17, 0.14);
  border-bottom: 1px solid rgba(17, 17, 17, 0.14);
  display: grid;
  grid-template-columns: repeat(12, minmax(0, 1fr));
  padding: 34px 34px 42px;
}

.uno-info-kicker {
  grid-column: 1 / 5;
  margin: 0;
  font-family: var(--font-en);
  font-size: 10px;
  line-height: 1;
  letter-spacing: 0.26em;
  font-weight: 760;
  color: rgba(17, 17, 17, 0.44);
}

.uno-info-page-index {
  grid-column: 10 / 13;
  justify-self: end;
  margin: 0;
  font-family: var(--font-en);
  font-size: 10px;
  line-height: 1;
  letter-spacing: 0.22em;
  font-weight: 760;
  color: rgba(17, 17, 17, 0.38);
}

.uno-info-title {
  grid-column: 1 / 8;
  align-self: end;
  margin: 0 0 96px;
  font-size: clamp(58px, 7.4vw, 112px);
  line-height: 1.02;
  letter-spacing: -0.08em;
  font-weight: 670;
  color: #111111;
  word-break: keep-all;
}

.uno-info-title.is-wide {
  grid-column: 1 / 9;
}

.uno-info-lead {
  grid-column: 8 / 12;
  align-self: end;
  margin: 0 0 98px;
  font-size: 16px;
  line-height: 1.9;
  letter-spacing: -0.04em;
  color: rgba(17, 17, 17, 0.62);
  word-break: keep-all;
}

.uno-info-doc-nav {
  display: grid;
  grid-template-columns: repeat(12, minmax(0, 1fr));
  border-bottom: 1px solid rgba(17, 17, 17, 0.14);
}

.uno-info-doc-button {
  min-height: 86px;
  padding: 0;
  border: 0;
  border-right: 1px solid rgba(17, 17, 17, 0.14);
  background: transparent;
  color: #111111;
  cursor: pointer;
  display: grid;
  grid-template-columns: repeat(6, minmax(0, 1fr));
  align-items: center;
  text-align: left;
  transition: background 180ms ease;
}

.uno-info-doc-button:first-child {
  grid-column: 1 / 7;
}

.uno-info-doc-button:last-child {
  grid-column: 7 / 13;
  border-right: 0;
}

.uno-info-doc-button:hover {
  background: rgba(17, 17, 17, 0.025);
}

.uno-info-doc-button.is-active {
  background: #111111;
  color: #ffffff;
}

.uno-info-doc-number {
  grid-column: 1 / 2;
  justify-self: center;
  font-family: var(--font-en);
  font-size: 18px;
  line-height: 1;
  letter-spacing: -0.04em;
  font-weight: 520;
  color: rgba(17, 17, 17, 0.4);
}

.uno-info-doc-button.is-active .uno-info-doc-number {
  color: rgba(255, 255, 255, 0.58);
}

.uno-info-doc-text {
  grid-column: 2 / 6;
}

.uno-info-doc-label {
  display: block;
  margin-bottom: 8px;
  font-family: var(--font-en);
  font-size: 9px;
  line-height: 1;
  letter-spacing: 0.22em;
  font-weight: 760;
  color: rgba(17, 17, 17, 0.42);
}

.uno-info-doc-button.is-active .uno-info-doc-label {
  color: rgba(255, 255, 255, 0.54);
}

.uno-info-doc-title {
  display: block;
  font-size: 16px;
  line-height: 1.1;
  letter-spacing: -0.045em;
  font-weight: 720;
  word-break: keep-all;
}

.uno-info-doc-arrow {
  grid-column: 6 / 7;
  justify-self: center;
  font-family: var(--font-en);
  font-size: 18px;
  color: rgba(17, 17, 17, 0.36);
}

.uno-info-doc-button.is-active .uno-info-doc-arrow {
  color: rgba(255, 255, 255, 0.54);
}

.uno-info-body {
  display: grid;
  grid-template-columns: repeat(12, minmax(0, 1fr));
  border-bottom: 1px solid rgba(17, 17, 17, 0.14);
}

.uno-info-aside {
  grid-column: 1 / 4;
  padding: 48px 34px;
  border-right: 1px solid rgba(17, 17, 17, 0.14);
}

.uno-info-aside-inner {
  position: sticky;
  top: 136px;
}

.uno-info-aside-label {
  margin: 0;
  font-family: var(--font-en);
  font-size: 10px;
  line-height: 1;
  letter-spacing: 0.24em;
  font-weight: 760;
  color: rgba(17, 17, 17, 0.4);
}

.uno-info-aside h2 {
  margin: 28px 0 0;
  font-size: 30px;
  line-height: 1.1;
  letter-spacing: -0.065em;
  font-weight: 680;
  word-break: keep-all;
}

.uno-info-aside p {
  margin: 22px 0 0;
  font-size: 13px;
  line-height: 1.82;
  letter-spacing: -0.038em;
  color: rgba(17, 17, 17, 0.58);
  word-break: keep-all;
}

.uno-info-list {
  grid-column: 4 / 13;
  margin: 0;
  padding: 0;
  list-style: none;
}

.uno-info-row {
  display: grid;
  grid-template-columns: repeat(9, minmax(0, 1fr));
  min-height: 132px;
  border-bottom: 1px solid rgba(17, 17, 17, 0.14);
}

.uno-info-row:last-child {
  border-bottom: 0;
}

.uno-info-row-number {
  grid-column: 1 / 2;
  padding: 30px 22px;
  font-family: var(--font-en);
  font-size: 22px;
  line-height: 1;
  letter-spacing: -0.05em;
  font-weight: 460;
  color: rgba(17, 17, 17, 0.46);
}

.uno-info-row-title {
  grid-column: 2 / 5;
  padding: 30px 28px 30px 0;
  border-right: 1px solid rgba(17, 17, 17, 0.14);
}

.uno-info-row-title h3 {
  margin: 0;
  font-size: 20px;
  line-height: 1.32;
  letter-spacing: -0.052em;
  font-weight: 720;
  color: #111111;
  word-break: keep-all;
}

.uno-info-row-copy {
  grid-column: 5 / 10;
  padding: 30px 42px 30px 32px;
}

.uno-info-row-copy p {
  max-width: 620px;
  margin: 0;
  font-size: 15px;
  line-height: 1.86;
  letter-spacing: -0.04em;
  color: rgba(17, 17, 17, 0.62);
  word-break: keep-all;
}

.uno-refund-list {
  grid-column: 4 / 13;
}

.uno-refund-row {
  display: grid;
  grid-template-columns: repeat(9, minmax(0, 1fr));
  min-height: 158px;
  border-bottom: 1px solid rgba(17, 17, 17, 0.14);
}

.uno-refund-row:last-child {
  border-bottom: 0;
}

.uno-refund-index {
  grid-column: 1 / 2;
  padding: 34px 22px;
  font-family: var(--font-en);
  font-size: 22px;
  line-height: 1;
  letter-spacing: -0.05em;
  font-weight: 460;
  color: rgba(17, 17, 17, 0.46);
}

.uno-refund-period {
  grid-column: 2 / 4;
  padding: 34px 28px 34px 0;
  border-right: 1px solid rgba(17, 17, 17, 0.14);
  font-size: 20px;
  line-height: 1.15;
  letter-spacing: -0.055em;
  font-weight: 690;
  word-break: keep-all;
}

.uno-refund-copy {
  grid-column: 4 / 10;
  padding: 34px 42px 34px 34px;
}

.uno-refund-copy h3 {
  margin: 0;
  font-size: clamp(28px, 3vw, 42px);
  line-height: 1.06;
  letter-spacing: -0.072em;
  font-weight: 670;
  color: #111111;
  word-break: keep-all;
}

.uno-refund-copy p {
  max-width: 650px;
  margin: 18px 0 0;
  font-size: 15px;
  line-height: 1.86;
  letter-spacing: -0.04em;
  color: rgba(17, 17, 17, 0.62);
  word-break: keep-all;
}

.uno-special-section {
  grid-column: 4 / 13;
  display: grid;
  grid-template-columns: repeat(9, minmax(0, 1fr));
  border-top: 1px solid rgba(17, 17, 17, 0.14);
}

.uno-special-head {
  grid-column: 1 / 4;
  padding: 42px 28px 42px 22px;
  border-right: 1px solid rgba(17, 17, 17, 0.14);
}

.uno-special-head p {
  margin: 0;
  font-family: var(--font-en);
  font-size: 10px;
  line-height: 1;
  letter-spacing: 0.24em;
  font-weight: 760;
  color: rgba(17, 17, 17, 0.42);
}

.uno-special-head h3 {
  margin: 26px 0 0;
  font-size: 28px;
  line-height: 1.08;
  letter-spacing: -0.07em;
  font-weight: 680;
  word-break: keep-all;
}

.uno-special-list {
  grid-column: 4 / 10;
  margin: 0;
  padding: 0;
  list-style: none;
}

.uno-special-list li {
  display: grid;
  grid-template-columns: 64px minmax(0, 1fr);
  gap: 18px;
  min-height: 82px;
  padding: 22px 42px 22px 0;
  border-bottom: 1px solid rgba(17, 17, 17, 0.14);
  font-size: 14px;
  line-height: 1.82;
  letter-spacing: -0.038em;
  color: rgba(17, 17, 17, 0.62);
  word-break: keep-all;
}

.uno-special-list li:last-child {
  border-bottom: 0;
}

.uno-special-list li::before {
  content: attr(data-index);
  font-family: var(--font-en);
  font-size: 12px;
  line-height: 1;
  letter-spacing: 0.14em;
  font-weight: 560;
  color: rgba(17, 17, 17, 0.45);
  padding-top: 6px;
}

.uno-info-note {
  display: grid;
  grid-template-columns: repeat(12, minmax(0, 1fr));
  min-height: 118px;
  border-bottom: 1px solid rgba(17, 17, 17, 0.14);
}

.uno-info-note strong {
  grid-column: 1 / 4;
  padding: 34px;
  border-right: 1px solid rgba(17, 17, 17, 0.14);
  font-family: var(--font-en);
  font-size: 10px;
  line-height: 1;
  letter-spacing: 0.24em;
  font-weight: 760;
}

.uno-info-note p {
  grid-column: 4 / 13;
  max-width: 760px;
  margin: 0;
  padding: 32px 42px;
  font-size: 14px;
  line-height: 1.82;
  letter-spacing: -0.038em;
  color: rgba(17, 17, 17, 0.62);
  word-break: keep-all;
}

.uno-info-index-page .uno-info-hero {
  min-height: 620px;
}

.uno-info-index-page .uno-info-title {
  grid-column: 1 / 7;
}

.uno-info-index-page .uno-info-lead {
  grid-column: 7 / 12;
}

.uno-info-index-list {
  border-bottom: 1px solid rgba(17, 17, 17, 0.14);
}

.uno-info-index-item {
  width: 100%;
  min-height: 180px;
  padding: 0;
  border: 0;
  border-bottom: 1px solid rgba(17, 17, 17, 0.14);
  background: transparent;
  color: #111111;
  display: grid;
  grid-template-columns: repeat(12, minmax(0, 1fr));
  align-items: stretch;
  cursor: pointer;
  text-align: left;
  transition: background 180ms ease;
}

.uno-info-index-item:last-child {
  border-bottom: 0;
}

.uno-info-index-item:hover {
  background: rgba(17, 17, 17, 0.025);
}

.uno-info-index-number {
  grid-column: 1 / 3;
  padding: 42px 34px;
  border-right: 1px solid rgba(17, 17, 17, 0.14);
  font-family: var(--font-en);
  font-size: 40px;
  line-height: 1;
  letter-spacing: -0.07em;
  font-weight: 430;
  color: rgba(17, 17, 17, 0.48);
}

.uno-info-index-copy {
  grid-column: 3 / 10;
  padding: 42px 42px;
}

.uno-info-index-copy p {
  margin: 0 0 18px;
  font-family: var(--font-en);
  font-size: 10px;
  line-height: 1;
  letter-spacing: 0.24em;
  font-weight: 760;
  color: rgba(17, 17, 17, 0.42);
}

.uno-info-index-copy h2 {
  margin: 0;
  font-size: clamp(36px, 4.6vw, 64px);
  line-height: 0.98;
  letter-spacing: -0.082em;
  font-weight: 660;
  word-break: keep-all;
}

.uno-info-index-copy span {
  display: block;
  max-width: 560px;
  margin-top: 22px;
  font-size: 15px;
  line-height: 1.84;
  letter-spacing: -0.04em;
  color: rgba(17, 17, 17, 0.62);
  word-break: keep-all;
}

.uno-info-index-arrow {
  grid-column: 10 / 13;
  align-self: stretch;
  display: flex;
  align-items: center;
  justify-content: center;
  border-left: 1px solid rgba(17, 17, 17, 0.14);
  font-family: var(--font-en);
  font-size: 32px;
  color: rgba(17, 17, 17, 0.38);
}

@media (max-width: 1024px) {
  .uno-info-document {
    padding: 126px 20px 96px;
  }

  .uno-info-hero {
    min-height: 520px;
  }

  .uno-info-title {
    grid-column: 1 / 9;
  }

  .uno-info-lead {
    grid-column: 7 / 13;
  }

  .uno-info-body {
    display: block;
  }

  .uno-info-aside {
    border-right: 0;
    border-bottom: 1px solid rgba(17, 17, 17, 0.14);
  }

  .uno-info-aside-inner {
    position: relative;
    top: auto;
  }

  .uno-info-list,
  .uno-refund-list,
  .uno-special-section {
    grid-column: 1 / -1;
  }
}

@media (max-width: 640px) {
  .uno-info-document {
    padding: 106px 14px 72px;
  }

  .uno-info-shell {
    background:
      linear-gradient(to right, rgba(17, 17, 17, 0.04) 1px, transparent 1px) 0 0 / calc(100% / 4) 100%,
      #ffffff;
  }

  .uno-info-hero {
    min-height: 480px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    padding: 24px;
  }

  .uno-info-kicker {
    grid-column: 1 / 3;
  }

  .uno-info-page-index {
    grid-column: 3 / 5;
  }

  .uno-info-title,
  .uno-info-title.is-wide,
  .uno-info-index-page .uno-info-title {
    grid-column: 1 / 5;
    align-self: end;
    margin-bottom: 132px;
    font-size: clamp(48px, 14vw, 72px);
    line-height: 1.04;
    letter-spacing: -0.074em;
  }

  .uno-info-lead,
  .uno-info-index-page .uno-info-lead {
    grid-column: 1 / 5;
    margin-bottom: 0;
    font-size: 14px;
  }

  .uno-info-doc-nav {
    grid-template-columns: 1fr;
  }

  .uno-info-doc-button,
  .uno-info-doc-button:first-child,
  .uno-info-doc-button:last-child {
    grid-column: 1 / -1;
    min-height: 78px;
    grid-template-columns: 56px minmax(0, 1fr) 40px;
    border-right: 0;
    border-bottom: 1px solid rgba(17, 17, 17, 0.14);
  }

  .uno-info-doc-button:last-child {
    border-bottom: 0;
  }

  .uno-info-doc-number,
  .uno-info-doc-text,
  .uno-info-doc-arrow {
    grid-column: auto;
  }

  .uno-info-aside {
    padding: 34px 24px;
  }

  .uno-info-row,
  .uno-refund-row {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    min-height: auto;
  }

  .uno-info-row-number,
  .uno-refund-index {
    grid-column: 1 / 2;
    padding: 26px 24px 0;
  }

  .uno-info-row-title,
  .uno-refund-period {
    grid-column: 2 / 5;
    padding: 26px 24px 0 0;
    border-right: 0;
  }

  .uno-info-row-copy,
  .uno-refund-copy {
    grid-column: 1 / 5;
    padding: 18px 24px 30px;
  }

  .uno-special-section {
    display: block;
  }

  .uno-special-head {
    border-right: 0;
    border-bottom: 1px solid rgba(17, 17, 17, 0.14);
    padding: 34px 24px;
  }

  .uno-special-list li {
    grid-template-columns: 52px minmax(0, 1fr);
    padding: 22px 24px;
  }

  .uno-info-note {
    display: block;
  }

  .uno-info-note strong {
    display: block;
    border-right: 0;
    padding: 28px 24px 0;
  }

  .uno-info-note p {
    padding: 20px 24px 30px;
  }

  .uno-info-index-item {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }

  .uno-info-index-number {
    grid-column: 1 / 2;
    padding: 30px 24px 0;
    border-right: 0;
    font-size: 28px;
  }

  .uno-info-index-copy {
    grid-column: 1 / 5;
    padding: 20px 24px 30px;
  }

  .uno-info-index-arrow {
    display: none;
  }
}
`;



function navigateTo(path: string) {
  if (typeof window === "undefined") return;

  if (window.location.pathname === path) {
    window.scrollTo({ top: 0, behavior: "smooth" });
    return;
  }

  window.history.pushState({}, "", path);
  window.dispatchEvent(new Event("unotravel:navigate"));
}

function InfoDocumentNav({ active }: { active: "notice" | "refund" }) {
  const items = [
    {
      id: "notice",
      number: "01",
      label: "NOTICE",
      title: "예약 시 주의사항",
      path: "/info/notice",
    },
    {
      id: "refund",
      number: "02",
      label: "REFUND",
      title: "취소 및 환불규정",
      path: "/info/refund",
    },
  ] as const;

  return (
    <nav className="uno-info-doc-nav" aria-label="INFO 문서 이동">
      {items.map((item) => {
        const isActive = active === item.id;

        return (
          <button
            key={item.id}
            type="button"
            aria-current={isActive ? "page" : undefined}
            className={`uno-info-doc-button ${isActive ? "is-active" : ""}`}
            onClick={() => navigateTo(item.path)}
          >
            <span className="uno-info-doc-number">{item.number}</span>
            <span className="uno-info-doc-text">
              <span className="uno-info-doc-label">{item.label}</span>
              <strong className="uno-info-doc-title">{item.title}</strong>
            </span>
            <span className="uno-info-doc-arrow" aria-hidden="true">→</span>
          </button>
        );
      })}
    </nav>
  );
}

function useInfoDocumentAnimation(scopeRef: React.RefObject<HTMLElement | null>) {
  React.useEffect(() => {
    let context: { revert: () => void } | undefined;
    let cancelled = false;

    async function setupAnimation() {
      if (typeof window === "undefined" || !scopeRef.current) return;

      const [{ gsap }, { ScrollTrigger }, { SplitText }] = await Promise.all([
        import("gsap"),
        import("gsap/ScrollTrigger"),
        import("gsap/SplitText"),
      ]);

      if (cancelled || !scopeRef.current) return;

      gsap.registerPlugin(ScrollTrigger, SplitText);

      const run = () => {
        if (!scopeRef.current) return;

        context = gsap.context(() => {
          gsap.set(".uno-info-split", { opacity: 1 });

          const splitTargets = gsap.utils.toArray<HTMLElement>(".uno-info-split");

          splitTargets.forEach((target) => {
            const trigger =
              target.closest<HTMLElement>(
                ".uno-info-hero, .uno-info-row, .uno-refund-row, .uno-special-section, .uno-info-note, .uno-info-aside, .uno-info-index-item",
              ) ?? target;

            SplitText.create(target, {
              type: "words,lines",
              mask: "lines",
              linesClass: "uno-info-split-line",
              autoSplit: true,
              onSplit: (instance) => {
                return gsap.from(instance.lines, {
                  yPercent: 112,
                  opacity: 0.001,
                  duration: 0.8,
                  ease: "power3.out",
                  stagger: 0.05,
                  scrollTrigger: {
                    trigger,
                    start: "clamp(top 84%)",
                    end: "clamp(bottom 58%)",
                    toggleActions: "play none none reverse",
                  },
                });
              },
            });
          });

          gsap.from(".uno-info-doc-button, .uno-info-index-item, .uno-info-row, .uno-refund-row, .uno-special-section, .uno-info-note", {
            y: 22,
            opacity: 0,
            duration: 0.62,
            ease: "power3.out",
            stagger: 0.04,
            scrollTrigger: {
              trigger: ".uno-info-shell",
              start: "top 72%",
              toggleActions: "play none none reverse",
            },
          });
        }, scopeRef);
      };

      if (document.fonts?.ready) {
        document.fonts.ready.then(run);
      } else {
        run();
      }
    }

    setupAnimation();

    return () => {
      cancelled = true;
      context?.revert();
    };
  }, [scopeRef]);
}


const infoItems = [
  {
    number: "01",
    label: "NOTICE",
    title: "예약 시 주의사항",
    body:
      "투어 참여 전 미팅, 일정 변경, 장비, 소지품 관련 주요 안내를 확인합니다.",
    path: "/info/notice",
  },
  {
    number: "02",
    label: "REFUND",
    title: "취소 및 환불규정",
    body:
      "취소 시점별 환불 기준과 특별약관, 유의사항을 한 번에 확인합니다.",
    path: "/info/refund",
  },
] as const;

export default function InfoPage() {
  const scopeRef = useRef<HTMLElement | null>(null);
  useInfoDocumentAnimation(scopeRef);

  return (
    <main ref={scopeRef} className="uno-info-document uno-info-index-page">
      <style>{infoDocumentStyles}</style>

      <div className="uno-info-shell">
        <section className="uno-info-hero">
          <p className="uno-info-kicker">UNOTRAVEL INFORMATION</p>
          <p className="uno-info-page-index">INDEX / 00</p>

          <h1 className="uno-info-title uno-info-split">
            INFO
          </h1>

          <p className="uno-info-lead uno-info-split">
            예약 전 꼭 확인해야 하는 안내를 정리했습니다. 필요한 문서를 선택해 핵심 내용을 빠르게 확인해 주세요.
          </p>
        </section>

        <section className="uno-info-index-list" aria-label="INFO 문서 목록">
          {infoItems.map((item) => (
            <button
              key={item.path}
              type="button"
              className="uno-info-index-item"
              onClick={() => navigateTo(item.path)}
            >
              <span className="uno-info-index-number">{item.number}</span>
              <span className="uno-info-index-copy">
                <p>{item.label}</p>
                <h2 className="uno-info-split">{item.title}</h2>
                <span className="uno-info-split">{item.body}</span>
              </span>
              <span className="uno-info-index-arrow" aria-hidden="true">→</span>
            </button>
          ))}
        </section>
      </div>
    </main>
  );
}
