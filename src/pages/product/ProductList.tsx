/* ==========================================================
   ProductList.tsx

   Gallery / List 두 가지 뷰를 동일 컴포넌트에서 전환.
   FILTER: Gallery | List 토글이 viewMode를 제어한다.

   Gallery 뷰 : Figma 세미패키지 메인 히어로 그리드 디자인 적용
   List 뷰    : 기존 에디토리얼 리스트 디자인 유지
========================================================== */

import { useEffect, useRef, useState } from "react";
import type { ProductCategory, ProductItem } from "./ProductTemplate";

// List view images
import imgUnion  from "../../imports/세미패키지메인히어로리스트/1b7d77e27b8ba6a7714d11f1d35222896c1a13a8.png";
import imgStatue from "../../imports/세미패키지메인히어로리스트/53d12808d051374ee7f5af9cc9b1904682827469.png";

/* Desktop Responsive Base
   - 실제 ProductList canvas는 Figma 기준 1700px
   - 화면에서 보이는 desktop 원본 기준은 1600px로 제한
   - 1600px 이하에서는 canvas 전체를 부모 폭에 맞춰 scale 처리 */
const PRODUCT_LIST_CANVAS_WIDTH = 1700;
const PRODUCT_LIST_DESKTOP_BASE_WIDTH = 1600;
const PRODUCT_LIST_GALLERY_HEIGHT = 1340;
const PRODUCT_LIST_FILTER_HEIGHT = 120;
const PRODUCT_LIST_BODY_LIST_HEIGHT = 736;

function useProductListScale() {
  const shellRef = useRef<HTMLElement | null>(null);

  /*
    Desktop Responsive Initial Scale
    ------------------------------------------
    SPA 방식으로 서브페이지 진입 시 ResizeObserver 실행 전
    canvas가 순간적으로 줄어드는 layout jump를 줄인다.
  */
  const getProductListScale = (width: number) => {
    const safeWidth = Math.max(width, 1024);
    const visibleWidth = Math.min(safeWidth, PRODUCT_LIST_DESKTOP_BASE_WIDTH);

    return visibleWidth / PRODUCT_LIST_CANVAS_WIDTH;
  };

  const [scale, setScale] = useState(() => {
    if (typeof window === "undefined") {
      return getProductListScale(PRODUCT_LIST_DESKTOP_BASE_WIDTH);
    }

    return getProductListScale(
      document.documentElement.clientWidth || PRODUCT_LIST_DESKTOP_BASE_WIDTH
    );
  });

  useEffect(() => {
    const updateScale = () => {
      const shellWidth =
        shellRef.current?.getBoundingClientRect().width ||
        document.documentElement.clientWidth ||
        PRODUCT_LIST_DESKTOP_BASE_WIDTH;

      /* Desktop Responsive
         - 1600px 이상: 1600px를 원본 표시 기준으로 고정
         - 1600px 이하: 부모 폭 기준으로 1700px canvas를 축소
         - 100vw를 쓰지 않아 vertical scrollbar 폭으로 인한 가로 스크롤을 방지 */
      const nextScale = getProductListScale(shellWidth);

      setScale(nextScale);
    };

    updateScale();

    const resizeObserver = new ResizeObserver(updateScale);
    if (shellRef.current) resizeObserver.observe(shellRef.current);

    window.addEventListener("resize", updateScale);

    return () => {
      resizeObserver.disconnect();
      window.removeEventListener("resize", updateScale);
    };
  }, []);

  return { shellRef, scale };
}

/* ── 공통 스타일 ── */
const STYLE = `
  .pl-product-shell {
    /* Desktop Responsive
       - 100vw 대신 부모 100% 기준
       - 1024~1600 구간에서 가로 스크롤 방지 */
    width: 100%;
    min-width: 1024px;
    background: #ffffff;
    overflow: hidden;
    display: flex;
    justify-content: center;
  }

  /*
    메인 페이지 Hero와 동일한 1700px canvas 구조.
    Figma에서 가져온 절대적인 디자인 비율은 유지하고,
    바깥 shell에서 계산한 scale만 적용한다.
  */
  .pl-product-canvas {
    width: 1700px;
    flex-shrink: 0;
    background: #ffffff;
    overflow: visible;
    transform-origin: top center;
    will-change: transform;
  }

  .pl-filter-bar {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    padding: 10px 20px;
    width: 1700px;
    height: 120px;
    background: #ffffff;
    box-sizing: border-box;
  }

  .pl-page-title {
    width: 768px;
    height: 100px;
    font-family: var(--font-en);
    font-weight: 700;
    font-size: 96px;
    line-height: 40px;
    letter-spacing: 0.04em;
    color: #151515;
    display: flex;
    align-items: center;
  }

  .pl-filter-toggle {
    position: relative;
    width: 368px;
    height: 48px;
    background: #ffffff;
    display: flex;
    align-items: center;
  }

  .pl-filter-label {
    position: absolute;
    left: 0;
    top: 10px;
    width: 100px;
    height: 28px;
    font-family: var(--font-en);
    font-weight: 700;
    font-size: 18px;
    line-height: 40px;
    color: #000000;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .pl-filter-btn {
    position: absolute;
    top: 10px;
    width: 80px;
    height: 28px;
    font-family: var(--font-en);
    font-weight: 400;
    font-size: 20px;
    line-height: 40px;
    color: #000000;
    background: none;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .pl-filter-btn.gallery { left: 144px; }
  .pl-filter-btn.list    { left: 288px; }

  .pl-filter-underline {
    position: absolute;
    bottom: 0;
    height: 3px;
    width: 34px;
    background: #151515;
    transition: left 0.28s cubic-bezier(0.22, 1, 0.36, 1);
  }

  /* ── Gallery layout ── */
  .pl-empty-products {
    width: 1700px;
    min-height: 520px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-top: 1px solid rgba(21, 21, 21, 0.1);
    background: #ffffff;
    box-sizing: border-box;
  }

  .pl-empty-products-inner {
    display: grid;
    gap: 12px;
    text-align: center;
  }

  .pl-empty-products-title {
    font-family: var(--font-ko);
    font-size: 28px;
    font-weight: 700;
    line-height: 1.2;
    letter-spacing: -0.04em;
    color: #151515;
  }

  .pl-empty-products-caption {
    font-family: var(--font-ko);
    font-size: 15px;
    font-weight: 400;
    line-height: 1.6;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.55);
  }

  .pl-gallery-body {
    display: flex;
    align-items: flex-start;
    padding: 10px;
    width: 1700px;
    background: #ffffff;
    box-sizing: border-box;
  }

  .pl-gallery-empty-slot {
    display: block;
    background: #ffffff;
    flex-shrink: 0;
  }

  .pl-gallery-empty-slot.is-tall {
    width: 440px;
    height: 560px;
  }

  .pl-gallery-empty-slot.is-mid {
    width: 580px;
    height: 320px;
  }

  .pl-gallery-extra-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    width: 1680px;
    padding: 20px 30px 10px;
    box-sizing: border-box;
    background: #ffffff;
  }

  .pl-gallery-extra-grid .pl-gallery-image-card {
    width: 100%;
    height: 320px;
  }

  .pl-gallery-left {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 20px;
    gap: 20px;
    width: 480px;
    background: #ffffff;
    flex-shrink: 0;
  }

  .pl-gallery-img-tall {
    width: 440px;
    height: 560px;
    object-fit: cover;
    display: block;
  }

  .pl-gallery-right {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    width: 1220px;
    background: #ffffff;
    flex-shrink: 0;
  }

  .pl-gallery-row-images {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 20px;
    gap: 20px;
    width: 1220px;
    box-sizing: border-box;
  }

  .pl-gallery-img-mid {
    width: 580px;
    height: 320px;
    object-fit: cover;
    display: block;
    flex-shrink: 0;
  }

  /*
    Gallery Image Hover
    ------------------------------------------
    큰 프리뷰 오버레이는 중앙 상품 리스트를 가리기 때문에 제거.
    각 이미지 카드 안에서만 은은한 그라데이션과 상품명만 노출한다.
  */
  .pl-gallery-image-card {
    position: relative;
    display: block;
    overflow: hidden;
    flex-shrink: 0;
    color: #ffffff;
    text-decoration: none;
    background: #f5f1e8;
  }

  .pl-gallery-image-card.is-tall {
    width: 440px;
    height: 560px;
  }

  .pl-gallery-image-card.is-mid {
    width: 580px;
    height: 320px;
  }

  .pl-gallery-image-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transform: scale(1);
    filter: brightness(1) contrast(1);
    transition:
      transform 0.55s cubic-bezier(0.16, 1, 0.3, 1),
      filter 0.45s ease,
      opacity 0.35s ease;
  }

  .pl-gallery-image-card.is-dimmed img {
    opacity: 0.82;
  }

  .pl-gallery-image-card.is-active img,
  .pl-gallery-image-card:hover img {
    transform: scale(1.025);
    filter: brightness(1.04) contrast(1.02);
    opacity: 1;
  }

  .pl-gallery-image-overlay {
    position: absolute;
    inset: auto 0 0 0;
    min-height: 46%;
    padding: 26px 28px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.46) 100%);
    opacity: 0;
    transform: translateY(10px);
    transition:
      opacity 0.28s ease,
      transform 0.34s cubic-bezier(0.16, 1, 0.3, 1);
    pointer-events: none;
  }

  .pl-gallery-image-card.is-active .pl-gallery-image-overlay,
  .pl-gallery-image-card:hover .pl-gallery-image-overlay {
    opacity: 1;
    transform: translateY(0);
  }

  .pl-gallery-image-kicker {
    font-family: var(--font-en);
    font-size: 12px;
    line-height: 1;
    letter-spacing: 0.16em;
    opacity: 0.82;
    margin-bottom: 12px;
  }

  .pl-gallery-image-title {
    font-family: var(--font-ko);
    font-size: 20px;
    line-height: 1.35;
    letter-spacing: -0.03em;
    word-break: keep-all;
  }

  .pl-gallery-image-view {
    position: absolute;
    right: 26px;
    bottom: 24px;
    font-family: var(--font-en);
    font-size: 13px;
    line-height: 1;
    letter-spacing: 0.12em;
    opacity: 0.88;
  }

  .pl-gallery-mid-info {
    display: flex;
    align-items: flex-start;
    /*
      Gallery bottom alignment
      ------------------------------------------
      오른쪽 하단 가로 이미지가 왼쪽 세로 그리드보다
      살짝 내려가는 문제를 맞추기 위해 중간 정보 영역의
      상하 padding만 10px → 5px로 조정한다.
    */
    padding: 5px 80px;
    gap: 80px;
    width: 1220px;
    box-sizing: border-box;
    background: #ffffff;
  }

  .pl-gallery-cats {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 10px 0;
    width: 240px;
    flex-shrink: 0;
    font-family: var(--font-en);
    font-size: 20px;
    line-height: 40px;
    color: #151515;
  }

  .pl-gallery-cats button {
    appearance: none;
    border: 0;
    background: transparent;
    font-family: inherit;
    font-size: inherit;
    line-height: inherit;
    color: #151515;
    text-align: left;
    cursor: pointer;
    padding: 0;
  }

  .pl-gallery-cats button.is-active {
    font-weight: 700;
  }

  .pl-gallery-names {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 10px;
    padding: 10px 0;
    width: 660px;
    flex-shrink: 0;
  }

  .pl-gallery-name-btn {
    appearance: none;
    border: 0;
    background: transparent;
    font-family: var(--font-en);
    font-size: 20px;
    line-height: 40px;
    color: #151515;
    text-align: left;
    cursor: pointer;
    padding: 10px;
    transition: opacity 0.24s ease, transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .pl-gallery-name-btn:hover,
  .pl-gallery-name-btn.is-active {
    text-decoration: underline;
    text-underline-offset: 3px;
    transform: translateX(4px);
  }

  .pl-gallery-quote {
    width: 900px;
    font-family: var(--font-en);
    font-weight: 400;
    font-size: 24px;
    line-height: 30px;
    color: #151515;
    display: flex;
    align-items: center;
  }



  /* ── List View Interaction ── */
  /*
    List Category Filter Interaction
    ------------------------------------------
    ALL / EUROPE / AFRICA 같은 카테고리는
    현재 선택 또는 hover 상태를 font-weight + 짧은 underline으로 보여준다.
    실제 상품 필터링 로직은 백엔드 카테고리 연동 시 확장한다.
  */
  .pl-list-category-item {
    appearance: none;
    border: 0;
    background: transparent;
    position: relative;
    display: inline-flex;
    align-items: center;
    width: fit-content;
    font-family: inherit;
    font-size: inherit;
    line-height: inherit;
    color: #151515;
    text-align: left;
    cursor: pointer;
    padding: 0;
    transition:
      opacity 0.28s ease,
      font-weight 0.24s ease,
      letter-spacing 0.28s ease,
      transform 0.32s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .pl-list-category-item::after {
    content: "";
    position: absolute;
    left: 14px;
    bottom: 4px;
    width: 0;
    height: 1px;
    background: #151515;
    opacity: 0;
    transition:
      width 0.34s cubic-bezier(0.16, 1, 0.3, 1),
      opacity 0.24s ease;
  }

  .pl-list-category-item.is-active,
  .pl-list-category-item.is-hovered {
    font-weight: 700;
    letter-spacing: 0.02em;
    opacity: 1;
  }

  .pl-list-category-item.is-active::after,
  .pl-list-category-item.is-hovered::after {
    width: calc(100% - 14px);
    opacity: 1;
  }

  .pl-list-name-link {
    position: relative;
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 0 10px;
    height: 34px;
    width: 630px;
    font-family: var(--font-en);
    font-weight: 400;
    font-size: 20px;
    line-height: 34px;
    color: #151515;
    text-decoration: none;
    overflow: visible;
    transition:
      color 0.28s ease,
      letter-spacing 0.34s cubic-bezier(0.16, 1, 0.3, 1),
      transform 0.34s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .pl-list-name-link::after {
    content: "";
    position: absolute;
    left: 10px;
    bottom: -7px;
    width: 0;
    height: 1px;
    background: #151515;
    opacity: 0;
    transition:
      width 0.38s cubic-bezier(0.16, 1, 0.3, 1),
      opacity 0.24s ease;
  }

  .pl-list-name-link.is-active {
    color: #000000;
    letter-spacing: 0.02em;
    transform: translateX(8px);
  }

  .pl-list-name-link.is-active::after {
    width: 92%;
    opacity: 1;
  }

  .pl-list-number {
    min-width: 26px;
    opacity: 0.34;
    font-size: 13px;
    line-height: 1;
    letter-spacing: 0.08em;
    transition: opacity 0.28s ease, transform 0.34s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .pl-list-name-link.is-active .pl-list-number {
    opacity: 1;
    transform: translateX(-2px);
  }

  .pl-list-title {
    position: relative;
    display: inline-flex;
    align-items: center;
  }

  .pl-list-title::before {
    content: "";
    width: 0;
    height: 8px;
    margin-right: 0;
    background: #fcc800;
    transform: translateY(1px);
    transition:
      width 0.34s cubic-bezier(0.16, 1, 0.3, 1),
      margin-right 0.34s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .pl-list-name-link.is-active .pl-list-title::before {
    width: 32px;
    margin-right: 12px;
  }

  .pl-list-quote {
    transition: opacity 0.28s ease, transform 0.34s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .pl-list-collage-img {
    transition:
      transform 0.72s cubic-bezier(0.16, 1, 0.3, 1),
      filter 0.55s ease,
      opacity 0.42s ease;
  }

  .pl-list-collage-img.is-active {
    transform: scale(1.025) translateX(-8px);
    filter: brightness(1.05) contrast(1.03);
  }

  .pl-list-yellow-accent {
    transition:
      transform 0.48s cubic-bezier(0.16, 1, 0.3, 1),
      background 0.3s ease;
  }

  .pl-list-yellow-accent.is-active {
    transform: translateX(10px);
    background: #f2c100;
  }

  .pl-list-statue-img {
    transition:
      transform 0.62s cubic-bezier(0.16, 1, 0.3, 1),
      filter 0.5s ease;
  }

  .pl-list-statue-img.is-active {
    transform: scale(1.035);
    filter: brightness(1.03) contrast(1.02);
  }

  /* List view uses inline styles — no utility classes needed */
`;


/* ── Gallery Page Transition ── */
function runGalleryPageTransition(event: React.MouseEvent<HTMLElement>, href?: string) {
  /*
    Gallery Click Transition
    ------------------------------------------
    LocomotiveScroll / body overflow 제어 / article layout은 사용하지 않는다.
    클릭한 이미지가 화면을 부드럽게 덮은 뒤 상세페이지로 이동하는
    최소 전환 효과만 적용한다.
  */
  if (!href || href === "#") return;

  event.preventDefault();

  const source = event.currentTarget as HTMLElement;
  const sourceImage = source.querySelector("img");
  const rect = sourceImage?.getBoundingClientRect() ?? source.getBoundingClientRect();

  const overlay = document.createElement("div");
  overlay.setAttribute("aria-hidden", "true");
  overlay.style.position = "fixed";
  overlay.style.inset = "0";
  overlay.style.zIndex = "9999";
  overlay.style.pointerEvents = "none";
  overlay.style.background = "rgba(255,255,255,0)";
  overlay.style.transition = "background 0.68s cubic-bezier(0.76, 0, 0.24, 1)";

  /*
    Detail Transition Title
    ------------------------------------------
    Codrops식 thumbnail → article 전환처럼
    이미지가 커지기 전에 상단에 번호 / 지역 / 상품명을 먼저 노출한다.
  */
  const transitionTitle = document.createElement("div");
  transitionTitle.style.position = "fixed";
  transitionTitle.style.left = "50%";
  transitionTitle.style.top = "150px";
  transitionTitle.style.width = "min(1180px, 72vw)";
  transitionTitle.style.transform = "translateX(-50%) translateY(12px)";
  transitionTitle.style.zIndex = "10001";
  transitionTitle.style.opacity = "0";
  transitionTitle.style.transition =
    "opacity 0.42s ease, transform 0.62s cubic-bezier(0.16, 1, 0.3, 1)";
  transitionTitle.style.color = "#151515";
  transitionTitle.style.pointerEvents = "none";

  transitionTitle.innerHTML = `
    <div style="
      font-family: var(--font-en);
      font-size: 13px;
      line-height: 1;
      letter-spacing: 0.14em;
      margin-bottom: 28px;
    ">
      ${source.getAttribute("data-product-number") ?? "01"}
    </div>

    <div style="
      font-family: var(--font-en);
      font-size: 14px;
      line-height: 1;
      letter-spacing: 0.16em;
      color: rgba(21,21,21,0.52);
      margin-bottom: 22px;
    ">
      ${source.getAttribute("data-product-region") ?? "ITALY"}
    </div>

    <div style="
      font-family: var(--font-ko);
      font-size: 64px;
      line-height: 1.02;
      letter-spacing: -0.065em;
      font-weight: 600;
      word-break: keep-all;
    ">
      ${source.getAttribute("data-product-title") ?? ""}
    </div>
  `;

  overlay.appendChild(transitionTitle);

  const clone = sourceImage?.cloneNode(true) as HTMLImageElement | null;

  if (clone) {
    clone.style.position = "fixed";
    clone.style.left = `${rect.left}px`;
    clone.style.top = `${rect.top}px`;
    clone.style.width = `${rect.width}px`;
    clone.style.height = `${rect.height}px`;
    clone.style.objectFit = "cover";
    clone.style.zIndex = "10000";
    clone.style.transformOrigin = "center center";
    clone.style.transition =
      "left 0.92s cubic-bezier(0.76, 0, 0.24, 1), top 0.92s cubic-bezier(0.76, 0, 0.24, 1), width 0.92s cubic-bezier(0.76, 0, 0.24, 1), height 0.92s cubic-bezier(0.76, 0, 0.24, 1), filter 0.72s ease";
    clone.style.filter = "brightness(1.02) contrast(1.02)";
    overlay.appendChild(clone);
  }

  document.body.appendChild(overlay);

  requestAnimationFrame(() => {
    overlay.style.background = "rgba(255,255,255,0.9)";

    if (clone) {
      /*
        Detail Hero Transition
        ------------------------------------------
        이미지를 과도하게 키우지 않고,
        상단 타이포 아래의 16:9 article hero 영역으로 확장한다.
      */
      const viewportWidth = document.documentElement.clientWidth || window.innerWidth;
      const targetWidth = Math.min(viewportWidth * 0.72, 1180);
      const targetHeight = targetWidth * 9 / 16;
      const targetLeft = (viewportWidth - targetWidth) / 2;
      const targetTop = 320;

      transitionTitle.style.opacity = "1";
      transitionTitle.style.transform = "translateX(-50%) translateY(0)";

      clone.style.left = `${targetLeft}px`;
      clone.style.top = `${targetTop}px`;
      clone.style.width = `${targetWidth}px`;
      clone.style.height = `${targetHeight}px`;
      clone.style.filter = "brightness(1.08) contrast(1.03)";
    }
  });

  window.setTimeout(() => {
    /*
      SPA Detail Navigation
      ------------------------------------------
      full reload 없이 상세페이지로 이동한다.
      SPA 이동에서는 overlay가 자동 제거되지 않으므로
      route 변경 직후 직접 fade-out 후 제거한다.
    */
    window.history.pushState({}, "", href);
    window.dispatchEvent(new Event("unotravel:navigate"));

    overlay.style.opacity = "0";
    overlay.style.transition = "opacity 0.22s ease";

    window.setTimeout(() => {
      overlay.remove();
    }, 860);
  }, 1020);
}

/* ── Gallery Image Card ── */
function GalleryImageCard({
  src,
  item,
  index,
  activeIndex,
  onHover,
  onLeave,
  variant,
}: {
  src: string;
  item?: ProductItem;
  index: number;
  activeIndex: number | null;
  onHover: (index: number) => void;
  onLeave: () => void;
  variant: "tall" | "mid";
}) {
  const isActive = activeIndex === index;
  const isDimmed = activeIndex !== null && !isActive;

  return (
    <a
      href={item?.href ?? "#"}
      data-product-number={item?.number ?? String(index + 1).padStart(2, "0")}
      data-product-region={item?.region ?? "ITALY"}
      data-product-title={item?.title ?? "Premium Journey"}
      className={`pl-gallery-image-card is-${variant}${isActive ? " is-active" : ""}${isDimmed ? " is-dimmed" : ""}`}
      onClick={(event) => runGalleryPageTransition(event, item?.href)}
      onMouseEnter={() => onHover(index)}
      onFocus={() => onHover(index)}
      onMouseLeave={onLeave}
      onBlur={onLeave}
      aria-label={item?.title ?? "상품 이미지"}
    >
      {src ? <img src={src} alt="" /> : null}

      {/*
        Gallery Hover Caption
        ------------------------------------------
        중앙 카테고리/상품 리스트를 가리지 않고,
        이미지 내부에서만 상품명과 VIEW 라벨을 노출한다.
      */}
      <span className="pl-gallery-image-overlay" aria-hidden="true">
        <span className="pl-gallery-image-kicker">UNOTRAVEL</span>
        <span className="pl-gallery-image-title">{item?.title ?? "Premium Journey"}</span>
        <span className="pl-gallery-image-view">VIEW</span>
      </span>
    </a>
  );
}

/* ── Gallery View ── */
function GalleryView({
  items,
  categories,
}: {
  items: ProductItem[];
  categories: ProductCategory[];
  suppressFallbackImages?: boolean;
}) {
  /*
    Gallery Hover State
    ------------------------------------------
    이미지 index가 아니라 상품 index를 기준으로 연결한다.
    마지막 하단 이미지처럼 이미지 수가 상품 수보다 많아도
    중앙 상품 리스트 hover 상태가 함께 맞춰지도록 한다.
  */
  const [activeProductIndex, setActiveProductIndex] = useState<number | null>(null);
  const visibleItems = items.slice(0, 6);
  const extraItems = items.slice(6);
  const getGalleryItem = (imageIndex: number) => visibleItems[imageIndex] ?? null;
  const getGalleryImage = (imageIndex: number) => {
    const item = getGalleryItem(imageIndex);
    return item?.image ?? item?.thumbnailUrl ?? "";
  };
  const renderGallerySlot = (imageIndex: number, variant: "tall" | "mid") => {
    const item = getGalleryItem(imageIndex);
    const src = getGalleryImage(imageIndex);

    if (!item) {
      return <span className={`pl-gallery-empty-slot is-${variant}`} aria-hidden="true" />;
    }

    return (
      <GalleryImageCard
        src={src}
        item={item}
        index={imageIndex}
        activeIndex={activeProductIndex}
        onHover={setActiveProductIndex}
        onLeave={() => setActiveProductIndex(null)}
        variant={variant}
      />
    );
  };

  return (
    <div>
      <div className="pl-gallery-body">
        <div className="pl-gallery-left">
          {renderGallerySlot(0, "tall")}
          {renderGallerySlot(1, "tall")}
        </div>

        <div className="pl-gallery-right">
          <div className="pl-gallery-row-images">
            {renderGallerySlot(2, "mid")}
            {renderGallerySlot(3, "mid")}
          </div>

          <div className="pl-gallery-mid-info">
            <div className="pl-gallery-cats">
              {categories.map((cat, i) => (
                <button key={cat.id} className={i === 0 ? "is-active" : ""}>
                  ㆍ{cat.label}
                </button>
              ))}
            </div>

            <div className="pl-gallery-names">
              {visibleItems.map((item, index) => (
                <a
                  key={item.id}
                  href={item.href ?? "#"}
                  data-product-number={item.number ?? String(index + 1).padStart(2, "0")}
                  data-product-region={item.region ?? "ITALY"}
                  data-product-title={item.title}
                  className={`pl-gallery-name-btn${activeProductIndex === index ? " is-active" : ""}`}
                  onClick={(event) => runGalleryPageTransition(event, item.href)}
                  onMouseEnter={() => setActiveProductIndex(index)}
                  onFocus={() => setActiveProductIndex(index)}
                  onMouseLeave={() => setActiveProductIndex(null)}
                  onBlur={() => setActiveProductIndex(null)}
                >
                  {item.title}
                </a>
              ))}
            </div>
          </div>

          <div className="pl-gallery-quote">
            A COLLECTION
            <br />OF PLACES
            <br />AND MOMENTS.
          </div>

          <div className="pl-gallery-row-images">
            {renderGallerySlot(4, "mid")}
            {renderGallerySlot(5, "mid")}
          </div>
        </div>
      </div>

      {extraItems.length > 0 ? (
        <div className="pl-gallery-extra-grid">
          {extraItems.map((item, extraIndex) => {
            const index = extraIndex + 6;
            return (
              <GalleryImageCard
                key={item.id}
                src={item.image ?? item.thumbnailUrl ?? ""}
                item={item}
                index={index}
                activeIndex={activeProductIndex}
                onHover={setActiveProductIndex}
                onLeave={() => setActiveProductIndex(null)}
                variant="mid"
              />
            );
          })}
        </div>
      ) : null}
    </div>
  );
}

/* ── List View ── */
function ListView({ items, categories }: { items: ProductItem[]; categories: ProductCategory[] }) {
  /*
    List View Hover State
    ------------------------------------------
    Gallery는 이미지 중심, List는 타이포 중심으로 구분한다.
    상품명 hover 시 텍스트 리듬 / 우측 콜라주 / Quote만 은은하게 반응한다.
  */
  const [activeListIndex, setActiveListIndex] = useState<number | null>(null);

  /*
    List Category Active State
    ------------------------------------------
    기본값은 첫 번째 카테고리(ALL 역할)로 유지한다.
    hover 시에는 임시 강조, click 시에는 선택 상태를 변경한다.
  */
  const [activeCategoryIndex, setActiveCategoryIndex] = useState(0);
  const [hoveredCategoryIndex, setHoveredCategoryIndex] = useState<number | null>(null);
  const activeItem = activeListIndex !== null ? items[activeListIndex] : null;

  const listQuotes = [
    "A JOURNEY\nTHROUGH\nTIMELESS CITIES.",
    "CLASSIC ROUTES\nWITH QUIET\nDETAILS.",
    "SEASONAL DAYS\nBETWEEN PEAKS\nAND STREETS.",
    "SECOND ITALY,\nSLOWER LIGHT\nAND SEA.",
    "ART, MEMORY\nAND THE SHAPE\nOF TRAVEL.",
  ];

  const currentQuote =
    activeListIndex !== null
      ? listQuotes[activeListIndex % listQuotes.length]
      : "A COLLECTION\nOF PLACES\nAND MOMENTS.";

  return (
    <div style={{ position: "relative", width: 1700, height: 700, overflow: "visible", background: "#fff" }}>

      {/* Left content: categories + names + quote */}
      {/* Component3 in Figma: absolute left:0 top:120px→offset to 0 in body */}
      <div style={{ position: "absolute", left: 0, top: 0, width: 900, height: 540, overflow: "hidden" }}>

        {/* Categories + product names row — top:88px in body */}
        <div style={{
          position: "absolute", left: 10, right: 10, top: 88,
          height: 291, display: "flex", alignItems: "flex-start",
          gap: 10, paddingTop: 10, paddingBottom: 10,
        }}>
          {/* Category list */}
          <div style={{
            width: 200, display: "flex", flexDirection: "column",
            gap: 10, paddingTop: 10, paddingBottom: 10, background: "#fff",
            fontFamily: "var(--font-en)", fontSize: 20,
            lineHeight: "40px", color: "#151515",
          }}>
            {categories.map((cat, i) => {
              const isActiveCategory = activeCategoryIndex === i;
              const isHoveredCategory = hoveredCategoryIndex === i;

              return (
                <button
                  key={cat.id}
                  type="button"
                  className={`pl-list-category-item${isActiveCategory ? " is-active" : ""}${isHoveredCategory ? " is-hovered" : ""}`}
                  onClick={() => setActiveCategoryIndex(i)}
                  onMouseEnter={() => setHoveredCategoryIndex(i)}
                  onMouseLeave={() => setHoveredCategoryIndex(null)}
                  onFocus={() => setHoveredCategoryIndex(i)}
                  onBlur={() => setHoveredCategoryIndex(null)}
                  aria-pressed={isActiveCategory}
                  style={{
                    opacity:
                      isActiveCategory || isHoveredCategory
                        ? 1
                        : hoveredCategoryIndex !== null
                          ? 0.48
                          : 0.72,
                  }}
                >
                  ㆍ{cat.label}
                </button>
              );
            })}
          </div>

          {/* Product names */}
          {/*
            List View Typography Interaction
            ------------------------------------------
            ScrollTrigger / pin / SplitText는 사용하지 않는다.
            hover만으로 번호, underline, yellow accent, 우측 콜라주를 연결한다.
          */}
          <div style={{
            width: 650, display: "flex", flexDirection: "column",
            justifyContent: "flex-start", alignItems: "flex-start",
            gap: 24, paddingTop: 10, paddingBottom: 10,
          }}>
            {items.map((item, index) => (
              <a
                key={item.id}
                href={item.href ?? "#"}
                data-product-number={item.number ?? String(index + 1).padStart(2, "0")}
                data-product-region={item.region ?? "ITALY"}
                data-product-title={item.title}
                className={`pl-list-name-link${activeListIndex === index ? " is-active" : ""}`}
                onClick={(event) => runGalleryPageTransition(event, item.href)}
                onMouseEnter={() => setActiveListIndex(index)}
                onFocus={() => setActiveListIndex(index)}
                onMouseLeave={() => setActiveListIndex(null)}
                onBlur={() => setActiveListIndex(null)}
              >
                <span className="pl-list-number">{item.number ?? String(index + 1).padStart(2, "0")}</span>
                <span className="pl-list-title">{item.title}</span>
              </a>
            ))}
          </div>
        </div>

        {/* Quote — top: 418px in Figma (Component3) = 418-120=298 in body */}
        <div style={{ position: "absolute", left: 10, top: 298, width: 768 }}>
          {/* Divider */}
          <div style={{ width: activeListIndex !== null ? 96 : 68, height: 1, background: "rgba(21,21,21,0.6)", marginBottom: 29, transition: "width 0.36s cubic-bezier(0.16, 1, 0.3, 1)" }} />
          <p
            className="pl-list-quote"
            style={{
              margin: 0, width: 768,
              fontFamily: "var(--font-en)", fontWeight: 400,
              fontSize: 24, lineHeight: "30px", color: "#151515",
              opacity: activeItem ? 1 : 0.92,
              transform: activeItem ? "translateX(8px)" : "translateX(0)",
              whiteSpace: "pre-line",
            }}
          >
            {currentQuote}
          </p>
        </div>
      </div>

      {/* Union photo collage — left:597, top:208 in full 736px → top:88 in body */}
      <div style={{ position: "absolute", left: 727, top: 88, width: 843, height: 454, pointerEvents: "none", overflow: "hidden" }}>
        <img
          src={imgUnion}
          alt=""
          className={`pl-list-collage-img${activeListIndex !== null ? " is-active" : ""}`}
          style={{ position: "absolute", inset: 0, width: "100%", height: "100%", display: "block" }}
        />
      </div>

      {/* Statue + yellow accent — left:847, top:35 in full 736px → top:-85 in body */}
      <div style={{ position: "absolute", left: 977, top: -85, width: 158, height: 340, zIndex: 4, pointerEvents: "none" }}>
        {/* Yellow block with white border — top:225-35=190 within statue group */}
        <div
          className={`pl-list-yellow-accent${activeListIndex !== null ? " is-active" : ""}`}
          style={{
            position: "absolute", left: 0, top: 190, width: 158, height: 150,
            background: "#FCC800", border: "10px solid #ffffff", boxSizing: "border-box",
          }}
        />
        {/* Statue image */}
        <div style={{ position: "absolute", left: 18, top: 0, width: 123, height: 300, overflow: "hidden" }}>
          <img
            src={imgStatue}
            alt=""
            className={`pl-list-statue-img${activeListIndex !== null ? " is-active" : ""}`}
            style={{ position: "absolute", height: "115.86%", left: "-55.45%", top: "-12.3%", maxWidth: "none", width: "350.72%" }}
          />
        </div>
      </div>
    </div>
  );
}

/* ── ProductList (combined) ── */
export default function ProductList({
  items = [],
  categories = [],
  viewMode = "gallery",
  onViewModeChange,
  suppressFallbackImages = false,
  pageTitle = "세미패키지",
}: {
  items?: ProductItem[];
  categories?: ProductCategory[];
  viewMode?: "gallery" | "list";
  onViewModeChange?: (mode: "gallery" | "list") => void;
  suppressFallbackImages?: boolean;
  pageTitle?: string;
}) {
  const galleryUnderlineLeft = 167;
  const listUnderlineLeft = 311;

  const { shellRef, scale } = useProductListScale();
  const extraGalleryRowCount = Math.ceil(Math.max(0, items.length - 6) / 3);
  const dynamicGalleryHeight = PRODUCT_LIST_GALLERY_HEIGHT + extraGalleryRowCount * 360;

  /*
    ProductList Dynamic Height
    ------------------------------------------
    Gallery / List view 높이가 다르므로
    현재 viewMode에 맞춰 shell 높이를 scale 값과 함께 계산한다.
  */
  const canvasHeight =
    viewMode === "gallery"
      ? Math.max(PRODUCT_LIST_GALLERY_HEIGHT, dynamicGalleryHeight)
      : PRODUCT_LIST_FILTER_HEIGHT + PRODUCT_LIST_BODY_LIST_HEIGHT;

  return (
    <section
      ref={shellRef}
      className="pl-product-shell"
      aria-label="상품 목록"
      style={{ height: `${canvasHeight * scale}px` }}
    >
      <style>{STYLE}</style>

      <div
        className="pl-product-canvas"
        style={{
          height: canvasHeight,
          transform: `scale(${scale})`,
        }}
      >
        {/* ── FILTER BAR ── */}
        <div className="pl-filter-bar">
          <div className="pl-page-title">{pageTitle}</div>

          <div className="pl-filter-toggle">
            <span className="pl-filter-label">FILTER :</span>

            <button
              className="pl-filter-btn gallery"
              onClick={() => onViewModeChange?.("gallery")}
              aria-pressed={viewMode === "gallery"}
            >
              Gallery
            </button>

            <button
              className="pl-filter-btn list"
              onClick={() => onViewModeChange?.("list")}
              aria-pressed={viewMode === "list"}
            >
              List
            </button>

            {/* Active underline — slides between Gallery and List */}
            <span
              className="pl-filter-underline"
              style={{ left: viewMode === "gallery" ? galleryUnderlineLeft : listUnderlineLeft }}
            />
          </div>
        </div>

        {/* ── BODY ── */}
        {items.length <= 0 ? (
          <div className="pl-empty-products" role="status">
            <div className="pl-empty-products-inner">
              <strong className="pl-empty-products-title">상품 준비중</strong>
              <span className="pl-empty-products-caption">
                해당 지역의 상품은 곧 업데이트됩니다.
              </span>
            </div>
          </div>
        ) : viewMode === "gallery" ? (
          <GalleryView
            items={items}
            categories={categories}
            suppressFallbackImages={suppressFallbackImages}
          />
        ) : (
          <ListView items={items} categories={categories} />
        )}
      </div>
    </section>
  );
}
