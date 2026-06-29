/* ==========================================================
   ProductList.tsx

   Gallery / List 두 가지 뷰를 동일 컴포넌트에서 전환.
   FILTER: Gallery | List 토글이 viewMode를 제어한다.

   Gallery 뷰 : Figma 세미패키지 메인 히어로 그리드 디자인 적용
   List 뷰    : 기존 에디토리얼 리스트 디자인 유지
========================================================== */

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

  .pl-gallery-mid-info {
    display: flex;
    align-items: flex-start;
    padding: 10px 80px;
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
  }

  .pl-gallery-name-btn:hover {
    text-decoration: underline;
    text-underline-offset: 3px;
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

  /* List view uses inline styles — no utility classes needed */
`;

/* ── Gallery View ── */
function GalleryView({
  items,
  categories,
}: {
  items: ProductItem[];
  categories: ProductCategory[];
}) {
  return (
    <div>
      {/* Left: 2 tall images */}
      <div className="pl-gallery-body">
        <div className="pl-gallery-left">
          <img className="pl-gallery-img-tall" src={imgA} alt="" />
          <img className="pl-gallery-img-tall" src={imgB} alt="" />
        </div>

        {/* Right: top images / mid info / quote / bottom images */}
        <div className="pl-gallery-right">
          {/* Top row: 2 images */}
          <div className="pl-gallery-row-images">
            <img className="pl-gallery-img-mid" src={imgC} alt="" />
            <img className="pl-gallery-img-mid" src={imgD} alt="" />
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
              {items.map((item) => (
                <a
                  key={item.id}
                  href={item.href ?? "#"}
                  className="pl-gallery-name-btn"
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
            <img className="pl-gallery-img-mid" src={imgE} alt="" />
            <img className="pl-gallery-img-mid" src={imgF} alt="" />
          </div>
        </div>
      </div>
    </div>
  );
}

/* ── List View ── */
function ListView({ items, categories }: { items: ProductItem[]; categories: ProductCategory[] }) {
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
            {categories.map((cat, i) => (
              <div key={cat.id} style={{ fontWeight: i === 0 ? 700 : 400 }}>
                ㆍ{cat.label}
              </div>
            ))}
          </div>

          {/* Product names */}
          <div style={{
            width: 650, display: "flex", flexDirection: "column",
            justifyContent: "space-between", alignItems: "flex-start",
            gap: 10, paddingTop: 10, paddingBottom: 10,
          }}>
            {items.map((item) => (
              <a
                key={item.id}
                href={item.href ?? "#"}
                style={{
                  display: "flex", alignItems: "center",
                  padding: "0 10px", height: 20, width: 630,
                  fontFamily: "var(--font-en)", fontWeight: 400,
                  fontSize: 20, lineHeight: "40px", color: "#151515",
                  textDecoration: "none",
                }}
                onMouseEnter={(e) => { (e.currentTarget as HTMLAnchorElement).style.textDecoration = "underline"; }}
                onMouseLeave={(e) => { (e.currentTarget as HTMLAnchorElement).style.textDecoration = "none"; }}
              >
                {item.title}
              </a>
            ))}
          </div>
        </div>

        {/* Quote — top: 418px in Figma (Component3) = 418-120=298 in body */}
        <div style={{ position: "absolute", left: 10, top: 298, width: 768 }}>
          {/* Divider */}
          <div style={{ width: 68, height: 1, background: "rgba(21,21,21,0.6)", marginBottom: 29 }} />
          <p style={{
            margin: 0, width: 768,
            fontFamily: "var(--font-en)", fontWeight: 400,
            fontSize: 24, lineHeight: "30px", color: "#151515",
          }}>
            A COLLECTION<br />OF PLACES<br />AND MOMENTS.
          </p>
        </div>
      </div>

      {/* Union photo collage — left:597, top:208 in full 736px → top:88 in body */}
      <div style={{ position: "absolute", left: 727, top: 88, width: 843, height: 454, pointerEvents: "none" }}>
        <img
          src={imgUnion}
          alt=""
          style={{ position: "absolute", inset: 0, width: "100%", height: "100%", display: "block" }}
        />
      </div>

      {/* Statue + yellow accent — left:847, top:35 in full 736px → top:-85 in body */}
      <div style={{ position: "absolute", left: 977, top: -85, width: 158, height: 340, zIndex: 4 }}>
        {/* Yellow block with white border — top:225-35=190 within statue group */}
        <div style={{
          position: "absolute", left: 0, top: 190, width: 158, height: 150,
          background: "#FCC800", border: "10px solid #ffffff", boxSizing: "border-box",
        }} />
        {/* Statue image */}
        <div style={{ position: "absolute", left: 18, top: 0, width: 123, height: 300, overflow: "hidden" }}>
          <img
            src={imgStatue}
            alt=""
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
