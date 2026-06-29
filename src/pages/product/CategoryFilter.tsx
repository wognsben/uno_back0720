/**
 * CategoryFilter
 * --------------
 * Category & Filter Navigation
 *
 * Props:
 *   - categories   : 필터 탭 목록 (예: ["전체", "9박 11일", "7박 9일"])
 *   - activeCategory : 현재 선택된 카테고리
 *   - onSelect     : 탭 선택 핸들러
 *   - sortOptions  : 정렬 옵션 목록 (예: ["추천순", "가격순", "인기순"])
 *   - activeSort   : 현재 정렬 방식
 *   - onSort       : 정렬 변경 핸들러
 *
/* ==========================================================
   CategoryFilter

   백엔드 연동

   tour_category

   예)

   ALL
   유럽
   아시아
   아프리카&중동

========================================================== */


import type { ProductCategory, ProductItem } from "../ProductTemplate";

export default function CategoryFilter({
  categories,
  activeCategory,
  onCategoryChange,
  activeProduct,
}: {
  categories: ProductCategory[];
  activeCategory: string;
  onCategoryChange: (id: string) => void;
  activeProduct?: ProductItem;
}) {
  return (
    <section className="product-category-section" aria-label="상품 카테고리 필터">
      <style>{`
        .product-category-section {
          width: 100vw;
          background: #ffffff;
        }

        .product-category-inner {
          height: 146px;
          display: grid;
          grid-template-columns: 280px 1fr 360px;
          align-items: center;
          border-bottom: 1px solid rgba(21, 21, 21, 0.1);
        }

        .product-category-kicker {
          font-family: var(--font-en);
          font-size: 18px;
          line-height: 1;
          letter-spacing: 0.12em;
          color: rgba(21, 21, 21, 0.54);
        }

        .product-category-list {
          display: flex;
          align-items: center;
          gap: 28px;
        }

        .product-category-button {
          appearance: none;
          border: 0;
          background: transparent;
          color: #151515;
          cursor: pointer;
          display: flex;
          align-items: center;
          gap: 8px;
          font-family: var(--font-ko);
          font-size: 19px;
          line-height: 1;
          letter-spacing: -0.01em;
          opacity: 0.44;
          padding: 10px 0;
          transition: opacity 0.24s ease, transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .product-category-button::before {
          content: "";
          width: 5px;
          height: 5px;
          border-radius: 999px;
          background: #151515;
          opacity: 0.4;
          transition: background 0.24s ease, opacity 0.24s ease, transform 0.24s ease;
        }

        .product-category-button:hover,
        .product-category-button.is-active {
          opacity: 1;
          transform: translateY(-1px);
        }

        .product-category-button.is-active::before {
          background: #fcc800;
          opacity: 1;
          transform: scale(1.25);
        }

        .product-active-summary {
          justify-self: end;
          width: 320px;
          font-family: var(--font-ko);
          color: #151515;
          text-align: right;
        }

        .product-active-summary-number {
          font-size: 13px;
          letter-spacing: 0.16em;
          opacity: 0.48;
          margin-bottom: 10px;
        }

        .product-active-summary-title {
          font-size: 20px;
          line-height: 1.35;
          word-break: keep-all;
        }
      `}</style>

      <div className="product-editorial-grid product-category-inner">
        <div className="product-category-kicker">CATEGORY</div>

        <div className="product-category-list">
          {categories.map((category) => (
            <button
              key={category.id}
              type="button"
              className={`product-category-button ${activeCategory === category.id ? "is-active" : ""}`}
              aria-pressed={activeCategory === category.id}
              onClick={() => onCategoryChange(category.id)}
            >
              {category.label}
            </button>
          ))}
        </div>

        {activeProduct && (
          <div className="product-active-summary" aria-label="현재 선택된 상품">
            <div className="product-active-summary-title">{activeProduct.title}</div>
          </div>
        )}
      </div>
    </section>
  );
}
