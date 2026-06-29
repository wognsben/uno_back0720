/**
 * ProductHero
 * -----------
 * Hero / Gallery Area
 *
 * (주석 유지)
 */

import type { ProductTemplateData, ProductViewMode } from "../ProductTemplate";

export default function ProductHero({
  pageData,
  viewMode,
  onViewModeChange,
}: {
  pageData: ProductTemplateData;
  viewMode: ProductViewMode;
  onViewModeChange: (mode: ProductViewMode) => void;
}) {
  return (
    <section className="product-hero-section" aria-label={`${pageData.pageTitle} 상품 서브페이지 상단`}>
      <style>{`
        .product-hero-section {
          width: 100vw;
          background: #ffffff;
          padding-top: 76px;
        }

        .product-hero-inner {
          height: 146px;
          display: flex;
          align-items: flex-end;
          justify-content: space-between;
          border-bottom: 1px solid rgba(21, 21, 21, 0.12);
          padding: 0 0 28px;
        }

        .product-hero-title-wrap {
          display: flex;
          align-items: flex-end;
          gap: 28px;
        }

        .product-hero-title {
          margin: 0;
          font-family: var(--font-en);
          font-size: 108px;
          line-height: 0.82;
          letter-spacing: 0.035em;
          color: #151515;
          font-weight: 700;
        }

        .product-hero-meta {
          display: flex;
          flex-direction: column;
          gap: 8px;
          padding-bottom: 4px;
          font-family: var(--font-ko);
          font-size: 16px;
          line-height: 1;
          letter-spacing: 0.08em;
          color: rgba(21, 21, 21, 0.62);
          text-transform: uppercase;
        }

        .product-view-toggle {
          display: flex;
          align-items: center;
          gap: 32px;
          height: 48px;
          font-family: var(--font-en);
          font-size: 20px;
          color: #151515;
        }

        .product-view-toggle-label {
          font-weight: 700;
          letter-spacing: 0.02em;
        }

        .product-view-toggle-button {
          appearance: none;
          border: 0;
          background: transparent;
          color: #151515;
          cursor: pointer;
          font-family: inherit;
          font-size: 20px;
          font-weight: 400;
          line-height: 1;
          padding: 10px 0 14px;
          position: relative;
          opacity: 0.46;
          transition: opacity 0.26s ease, transform 0.26s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .product-view-toggle-button:hover,
        .product-view-toggle-button.is-active {
          opacity: 1;
          transform: translateY(-1px);
        }

        .product-view-toggle-button::after {
          content: "";
          position: absolute;
          left: 50%;
          bottom: 0;
          width: 34px;
          height: 3px;
          background: #151515;
          opacity: 0;
          transform: translateX(-50%) scaleX(0.4);
          transform-origin: center;
          transition: opacity 0.24s ease, transform 0.28s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .product-view-toggle-button.is-active::after {
          opacity: 1;
          transform: translateX(-50%) scaleX(1);
        }
      `}</style>
    </section>
  );
}
