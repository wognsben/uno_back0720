/* ==========================================================
   ProductList.tsx

   Gallery / List 두 가지 뷰를 동일 컴포넌트에서 전환.
   FILTER: Gallery | List 토글이 viewMode를 제어한다.

   Gallery 뷰 : Figma 세미패키지 메인 히어로 그리드 디자인 적용
   List 뷰    : 기존 에디토리얼 리스트 디자인 유지
========================================================== */

import { useState } from "react";
import type { ProductCategory, ProductItem } from "./ProductTemplate";

import imgA from "../../imports/세미패키지메인히어로그리드/3f5da2e34aadc41b88babc2cb3cf79d54480fb17.png";
import imgB from "../../imports/세미패키지메인히어로그리드/4330107f5001d8438ca2a32856e91d36fc97e09f.png";
import imgC from "../../imports/세미패키지메인히어로그리드/a1bb687947753b4c890d720a1b31402344e5c88d.png";
import imgD from "../../imports/세미패키지메인히어로그리드/ca8b91484dd437b9300e61e1611bbff92bf1b412.png";
import imgE from "../../imports/세미패키지메인히어로그리드/724c69b9aeb2689cd61d20b56baa17a9093971ca.png";
import imgF from "../../imports/세미패키지메인히어로그리드/176f62c60c3978e3ac5e3d4bb41f76b68f88c0b6.png";

// List view images
import imgUnion  from "../../imports/세미패키지메인히어로리스트/1b7d77e27b8ba6a7714d11f1d35222896c1a13a8.png";
import imgStatue from "../../imports/세미패키지메인히어로리스트/53d12808d051374ee7f5af9cc9b1904682827469.png";

/* ── 공통 스타일 ── */
const STYLE = `
  .pl-product-shell {
    width: 100vw;
    background: #ffffff;
    overflow: visible;
    display: flex;
    justify-content: center;
  }

  /*
    메인 페이지 Hero와 동일한 100vw shell + 1700px canvas 구조.
    Figma에서 가져온 절대적인 디자인 비율은 유지하고,
    바깥 페이지만 100vw 기준으로 가운데 정렬한다.
  */
  .pl-product-canvas {
    width: 1700px;
    flex-shrink: 0;
    background: #ffffff;
    overflow: visible;
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
  .pl-gallery-body {
    display: flex;
    align-items: flex-start;
    padding: 10px;
    width: 1700px;
    background: #ffffff;
    box-sizing: border-box;
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
      "left 0.72s cubic-bezier(0.76, 0, 0.24, 1), top 0.72s cubic-bezier(0.76, 0, 0.24, 1), width 0.72s cubic-bezier(0.76, 0, 0.24, 1), height 0.72s cubic-bezier(0.76, 0, 0.24, 1), filter 0.72s ease";
    clone.style.filter = "brightness(1.02) contrast(1.02)";
    overlay.appendChild(clone);
  }

  document.body.appendChild(overlay);

  requestAnimationFrame(() => {
    overlay.style.background = "rgba(255,255,255,0.86)";

    if (clone) {
      clone.style.left = "0px";
      clone.style.top = "0px";
      clone.style.width = "100vw";
      clone.style.height = "100vh";
      clone.style.filter = "brightness(1.08) contrast(1.03)";
    }
  });

  window.setTimeout(() => {
    window.location.href = href;
  }, 760);
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
      className={`pl-gallery-image-card is-${variant}${isActive ? " is-active" : ""}${isDimmed ? " is-dimmed" : ""}`}
      onClick={(event) => runGalleryPageTransition(event, item?.href)}
      onMouseEnter={() => onHover(index)}
      onFocus={() => onHover(index)}
      onMouseLeave={onLeave}
      onBlur={onLeave}
      aria-label={item?.title ?? "상품 이미지"}
    >
      <img src={src} alt="" />

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
}) {
  /*
    Gallery Hover State
    ------------------------------------------
    이미지 index가 아니라 상품 index를 기준으로 연결한다.
    마지막 하단 이미지처럼 이미지 수가 상품 수보다 많아도
    중앙 상품 리스트 hover 상태가 함께 맞춰지도록 한다.
  */
  const [activeProductIndex, setActiveProductIndex] = useState<number | null>(null);
  const galleryImages = [imgA, imgB, imgC, imgD, imgE, imgF];

  /*
    Gallery Image ↔ Product Mapping
    ------------------------------------------
    갤러리 이미지는 6장, 현재 상품 리스트는 5개일 수 있다.
    마지막 오른쪽 하단 이미지는 마지막 상품과 연결해
    이미지 hover 시 중앙 상품명 hover 효과도 같이 보이게 한다.
  */
  const getGalleryItemIndex = (imageIndex: number) => {
    if (items.length <= 0) return 0;
    return Math.min(imageIndex, items.length - 1);
  };

  const getGalleryItem = (imageIndex: number) => items[getGalleryItemIndex(imageIndex)];

  return (
    <div>
      {/* Left: 2 tall images */}
      <div className="pl-gallery-body">
        <div className="pl-gallery-left">
          <GalleryImageCard
            src={galleryImages[0]}
            item={getGalleryItem(0)}
            index={0}
            activeIndex={activeProductIndex}
            onHover={setActiveProductIndex}
            onLeave={() => setActiveProductIndex(null)}
            variant="tall"
          />
          <GalleryImageCard
            src={galleryImages[1]}
            item={getGalleryItem(1)}
            index={1}
            activeIndex={activeProductIndex}
            onHover={setActiveProductIndex}
            onLeave={() => setActiveProductIndex(null)}
            variant="tall"
          />
        </div>

        {/* Right: top images / mid info / quote / bottom images */}
        <div className="pl-gallery-right">
          {/* Top row: 2 images */}
          <div className="pl-gallery-row-images">
            <GalleryImageCard
              src={galleryImages[2]}
              item={getGalleryItem(2)}
              index={2}
              activeIndex={activeProductIndex}
              onHover={setActiveProductIndex}
              onLeave={() => setActiveProductIndex(null)}
              variant="mid"
            />
            <GalleryImageCard
              src={galleryImages[3]}
              item={getGalleryItem(3)}
              index={3}
              activeIndex={activeProductIndex}
              onHover={setActiveProductIndex}
              onLeave={() => setActiveProductIndex(null)}
              variant="mid"
            />
          </div>

          {/* Mid: categories + product names */}
          <div className="pl-gallery-mid-info">
            <div className="pl-gallery-cats">
              {categories.map((cat, i) => (
                <button key={cat.id} className={i === 0 ? "is-active" : ""}>
                  ㆍ{cat.label}
                </button>
              ))}
            </div>

            <div className="pl-gallery-names">
              {items.map((item, index) => (
                <a
                  key={item.id}
                  href={item.href ?? "#"}
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

          {/* Quote */}
          <div className="pl-gallery-quote">
            A COLLECTION
            <br />OF PLACES
            <br />AND MOMENTS.
          </div>

          {/* Bottom row: 2 images */}
          <div className="pl-gallery-row-images">
            <GalleryImageCard
              src={galleryImages[4]}
              item={getGalleryItem(4)}
              index={4}
              activeIndex={activeProductIndex}
              onHover={setActiveProductIndex}
              onLeave={() => setActiveProductIndex(null)}
              variant="mid"
            />
            <GalleryImageCard
              src={galleryImages[5]}
              item={getGalleryItem(5)}
              index={5}
              activeIndex={activeProductIndex}
              onHover={setActiveProductIndex}
              onLeave={() => setActiveProductIndex(null)}
              variant="mid"
            />
          </div>
        </div>
      </div>
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
  pageTitle = "세미패키지",
}: {
  items?: ProductItem[];
  categories?: ProductCategory[];
  viewMode?: "gallery" | "list";
  onViewModeChange?: (mode: "gallery" | "list") => void;
  pageTitle?: string;
}) {
  const galleryUnderlineLeft = 167;
  const listUnderlineLeft = 311;

  return (
    <section className="pl-product-shell" aria-label="상품 목록">
      <style>{STYLE}</style>

      <div className="pl-product-canvas">
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
        {viewMode === "gallery" ? (
          <GalleryView items={items} categories={categories} />
        ) : (
          <ListView items={items} categories={categories} />
        )}
      </div>
    </section>
  );
}
