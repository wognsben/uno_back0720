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
  /*
    Product Sub Navigation
    ----------------------------------------------------------
    기존 CATEGORY / 현재 선택 상품명 영역은 Product Body와 정보가 중복되므로
    메인 Hero Navigation과 같은 상위 상품군 Navigation 톤으로 변경.

    현재 props 구조는 유지:
    - categories
    - activeCategory
    - onCategoryChange
    - activeProduct

    activeProduct는 기존 상위 컴포넌트 연결을 깨지 않기 위해 props로 유지하되,
    이 컴포넌트 내부에서는 중복 노출하지 않는다.
  */
  const semiCategories = categories;
  const dailyCategories = [
    { id: "daily-italy", label: "이탈리아", labelEn: "ITALY" },
    { id: "daily-france", label: "프랑스", labelEn: "FRANCE" },
  ];

  const getCategoryLabelEn = (category: ProductCategory) => {
    return category.labelEn || category.label;
  };

  return (
    <section className="product-category-section" aria-label="상품 상위 네비게이션">
      <style>{`
        .product-category-section {
          width: 100vw;
          background: #ffffff;
        }

        /*
          Product Sub Navigation
          ----------------------------------------------------------
          메인 Hero Navigation과 통일감을 주기 위해
          기존 3단 CATEGORY Grid를 2단 상품군 Navigation으로 변경.
        */
        .product-category-inner {
          min-height: 132px;
          display: grid;
          grid-template-columns: 1fr 1px 0.82fr;
          align-items: stretch;
          border-top: 1px solid rgba(21, 21, 21, 0.08);
          border-bottom: 1px solid rgba(21, 21, 21, 0.1);
          background: rgba(255, 255, 255, 0.86);
          overflow: hidden;
        }

        .product-nav-block {
          position: relative;
          min-width: 0;
          display: flex;
          flex-direction: column;
          justify-content: center;
          padding: 24px 34px 26px;
        }

        .product-nav-block--semi {
          align-items: flex-start;
        }

        .product-nav-block--daily {
          align-items: flex-start;
        }

        .product-nav-divider {
          width: 1px;
          height: calc(100% - 44px);
          align-self: center;
          background: rgba(21, 21, 21, 0.12);
        }

        .product-nav-title {
          font-family: var(--font-en);
          font-size: 28px;
          line-height: 1;
          letter-spacing: 0.02em;
          color: #151515;
          margin-bottom: 24px;
        }

        .product-nav-list {
          display: flex;
          align-items: center;
          flex-wrap: wrap;
          width: 100%;
        }

        .product-nav-item {
          position: relative;
          appearance: none;
          border: 0;
          background: transparent;
          padding: 0 26px;
          min-height: 40px;
          cursor: pointer;
          color: #151515;
          display: flex;
          flex-direction: column;
          align-items: flex-start;
          justify-content: center;
          transition:
            opacity 0.24s ease,
            transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .product-nav-item:first-child {
          padding-left: 0;
        }

        .product-nav-item + .product-nav-item::before {
          content: "";
          position: absolute;
          left: 0;
          top: 4px;
          width: 1px;
          height: 32px;
          background: rgba(21, 21, 21, 0.12);
        }

        .product-nav-en {
          font-family: var(--font-en);
          font-size: 16px;
          line-height: 1;
          letter-spacing: 0.08em;
          color: #151515;
          text-transform: uppercase;
        }

        .product-nav-ko {
          margin-top: 10px;
          font-family: var(--font-ko);
          font-size: 13px;
          line-height: 1;
          letter-spacing: -0.02em;
          color: rgba(21, 21, 21, 0.68);
        }

        .product-nav-item::after {
          content: "";
          position: absolute;
          left: 26px;
          bottom: -8px;
          width: 5px;
          height: 5px;
          border-radius: 999px;
          background: #fcc800;
          opacity: 0;
          transform: scale(0.6);
          transition:
            opacity 0.24s ease,
            transform 0.24s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .product-nav-item:first-child::after {
          left: 0;
        }

        .product-nav-item:hover,
        .product-nav-item.is-active {
          opacity: 1;
          transform: translateY(-2px);
        }

        .product-nav-item:not(.is-active) {
          opacity: 0.62;
        }

        .product-nav-item:hover::after,
        .product-nav-item.is-active::after {
          opacity: 1;
          transform: scale(1);
        }

        .product-nav-item:focus-visible {
          outline: 1px solid rgba(21, 21, 21, 0.4);
          outline-offset: 8px;
        }

        /*
          Daily Tour Preview
          ----------------------------------------------------------
          현재 ProductTemplate props에는 Daily Tour 전용 카테고리 데이터가 없으므로
          하위 카테고리 미리보기는 비활성 버튼으로만 표시.
          실제 Daily Tour 라우트 연결 시 onClick 또는 href 연결만 추가하면 된다.
        */
        .product-nav-item--disabled {
          cursor: default;
        }

        .product-nav-item--disabled:hover {
          transform: none;
        }

        .product-nav-item--disabled::after {
          display: none;
        }

        @media (max-width: 1024px) {
          .product-category-inner {
            grid-template-columns: 1fr;
            min-height: auto;
          }

          .product-nav-divider {
            width: calc(100% - 48px);
            height: 1px;
            justify-self: center;
          }

          .product-nav-block {
            padding: 24px;
          }

          .product-nav-title {
            font-size: 24px;
          }
        }

        @media (max-width: 640px) {
          .product-nav-list {
            gap: 18px 0;
          }

          .product-nav-item {
            width: 50%;
            padding: 0;
          }

          .product-nav-item + .product-nav-item::before {
            display: none;
          }

          .product-nav-item::after {
            left: 0;
          }
        }
      `}</style>

      <div className="product-editorial-grid product-category-inner">
        <div className="product-nav-block product-nav-block--semi">
          <div className="product-nav-title">SEMI PACKAGE</div>

          <div className="product-nav-list" aria-label="세미패키지 국가 네비게이션">
            {semiCategories.map((category) => (
              <button
                key={category.id}
                type="button"
                className={`product-nav-item ${activeCategory === category.id ? "is-active" : ""}`}
                aria-pressed={activeCategory === category.id}
                onClick={() => onCategoryChange(category.id)}
              >
                <span className="product-nav-en">{getCategoryLabelEn(category)}</span>
                <span className="product-nav-ko">{category.label}</span>
              </button>
            ))}
          </div>
        </div>

        <div className="product-nav-divider" />

        <div className="product-nav-block product-nav-block--daily">
          <div className="product-nav-title">DAILY TOUR</div>

          <div className="product-nav-list" aria-label="데일리투어 지역 미리보기">
            {dailyCategories.map((category) => (
              <button
                key={category.id}
                type="button"
                className="product-nav-item product-nav-item--disabled"
                aria-disabled="true"
                tabIndex={-1}
              >
                <span className="product-nav-en">{category.labelEn}</span>
                <span className="product-nav-ko">{category.label}</span>
              </button>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
