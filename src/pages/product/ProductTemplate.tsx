/* ==========================================================
   ProductTemplate.tsx

   Product Type A (상품 서브페이지)

   사용 페이지
   - 세미패키지
   - 데일리투어

   백엔드 연동
   ------------------------------------------
   category  ← 대분류
   region    ← 국가/지역
   products  ← 상품목록

   Header / Footer는 공통 컴포넌트 사용
========================================================== */
import { useEffect, useMemo, useState } from "react";

import CategoryFilter from "./CategoryFilter";
import ProductList from "./ProductList";
import { PRODUCT_LEGACY_ID_CANDIDATES } from "./productLegacyIds";

export type ProductViewMode = "gallery" | "list";
export type ProductKind = "semi" | "daily";

export type ProductCategory = {
  id: string;
  label: string;
  labelEn?: string;
};

export type ProductItem = {
  id: string;
  legacyProductId?: number | string;
  number: string;
  title: string;
  region?: string;
  duration?: string;
  eyebrow?: string;
  description?: string;
  image?: string;
  href?: string;

  /*
    Product Type
    ------------------------------------------
    semi  : 세미패키지 상품. 상세페이지에서 Premium Travel Document 중심으로 사용.
    daily : 데일리투어 상품. 상세페이지에서 Calendar 중심 예약 UI로 사용.

    실제 백엔드 연동 시 product.category 또는 product.type 값을 이 필드에 매핑한다.
  */
  productType?: ProductKind;

  /*
    Backend Mapping Placeholder
    ------------------------------------------
    categoryId / regionId는 현재 Mock Data 필터링을 위한 임시 필드다.
    실제 백엔드 연동 시 category / region 테이블 또는 관리자 입력값과 연결한다.
  */
  categoryId?: string;
  regionId?: string;
};

export type ProductTemplateData = {
  pageTitle: string;
  eyebrow: string;
  categoryLabel: string;
  regionLabel?: string;
  description: string;

  /*
    Backend Mapping Placeholder
    ------------------------------------------
    productType은 현재 App.tsx의 /product/semi, /product/daily 라우팅에서
    어떤 목록 데이터를 보여줄지 결정하기 위한 값이다.
    실제 백엔드 연동 시 URL category 또는 DB category 값을 매핑한다.
  */
  productType?: ProductKind;

  categories: ProductCategory[];
  products: ProductItem[];
  galleryImages: string[];
};

export const SEMI_PACKAGE_DATA: ProductTemplateData = {
  pageTitle: "세미패키지",
  eyebrow: "PREMIUM SEMI-PACKAGE",
  categoryLabel: "SEMI PACKAGE",
  regionLabel: "ITALY / MEDITERRANEAN",
  description:
    "A collection of places and moments. 우노트래블이 큐레이션한 프리미엄 세미패키지 여정을 만나보세요.",
  productType: "semi",
  categories: [
    { id: "all", label: "ALL", labelEn: "ALL" },
    { id: "europe", label: "유럽", labelEn: "EUROPE" },
    { id: "middle-east", label: "아프리카 & 중동", labelEn: "AFRICA & MIDDLE EAST" },
    { id: "asia", label: "아시아", labelEn: "ASIA" },
  ],
  products: [
    {
      id: "italy-11",
      legacyProductId: PRODUCT_LEGACY_ID_CANDIDATES["italy-11"],
      number: "01",
      title: "이탈리아 일주 9박 11일",
      region: "ITALY",
      duration: "9N 11D",
      eyebrow: "ITALY GRAND TOUR",
      description: "로마부터 남부까지 이어지는 우노트래블 대표 세미패키지.",
      href: "/product/detail/italy-11",
      productType: "semi",
      categoryId: "europe",
      regionId: "italy",
    },
    {
      id: "italy-9",
      number: "02",
      title: "이탈리아 일주 7박 9일",
      region: "ITALY",
      duration: "7N 9D",
      eyebrow: "CLASSIC ITALY",
      description: "핵심 도시를 중심으로 구성한 클래식 이탈리아 여정.",
      href: "/product/detail/italy-9",
      productType: "semi",
      categoryId: "europe",
      regionId: "italy",
    },
    {
      id: "dolomiti-11",
      number: "03",
      title: "[8-9]월 한정 이탈리아일주+돌로미티 11",
      region: "DOLOMITI",
      duration: "LIMITED",
      eyebrow: "DOLOMITI LIMITED",
      description: "여름 시즌에만 만나는 돌로미티 포함 한정 코스.",
      href: "/product/detail/dolomiti-11",
      productType: "semi",
      categoryId: "europe",
      regionId: "italy",
    },
    {
      id: "sicilia-9",
      number: "04",
      title: "나의 두번째 이탈리아, 지중해의 황금빛 시칠리아 일주 9일",
      region: "SICILIA",
      duration: "9D",
      eyebrow: "SICILIA COLLECTION",
      description: "지중해의 빛과 도시의 결이 살아있는 시칠리아 컬렉션.",
      href: "/product/detail/sicilia-9",
      productType: "semi",
      categoryId: "europe",
      regionId: "italy",
    },
    {
      id: "art-tour-11",
      number: "05",
      title: "이탈리아 아트투어 일주 9박 11일",
      region: "ITALY",
      duration: "9N 11D",
      eyebrow: "ART TOUR",
      description: "예술과 도시의 맥락을 따라가는 에디토리얼 아트 투어.",
      href: "/product/detail/art-tour-11",
      productType: "semi",
      categoryId: "europe",
      regionId: "italy",
    },
  ],
  galleryImages: [
    "/assets/product-gallery-01.jpg",
    "/assets/product-gallery-02.jpg",
    "/assets/product-gallery-03.jpg",
    "/assets/product-gallery-04.jpg",
    "/assets/product-gallery-05.jpg",
    "/assets/product-gallery-06.jpg",
  ],
};

export const DAILY_TOUR_DATA: ProductTemplateData = {
  pageTitle: "데일리투어",
  eyebrow: "LOCAL DAILY TOUR",
  categoryLabel: "DAILY TOUR",
  regionLabel: "ROME / FIRENZE / VENEZIA / NAPOLI",
  description:
    "Local days, guided with context. 현지에서 바로 합류하는 우노트래블 데일리투어를 확인하세요.",
  productType: "daily",
  categories: [
    { id: "all", label: "ALL", labelEn: "ALL" },
    { id: "rome", label: "로마", labelEn: "ROME" },
    { id: "firenze", label: "피렌체", labelEn: "FIRENZE" },
    { id: "venezia", label: "베네치아", labelEn: "VENEZIA" },
    { id: "napoli", label: "나폴리", labelEn: "NAPOLI" },
  ],
  products: [
    {
      id: "rome-vatican-daily",
      legacyProductId: PRODUCT_LEGACY_ID_CANDIDATES["rome-vatican-daily"],
      number: "01",
      title: "로마 바티칸 집중 투어",
      region: "ROME",
      duration: "1 DAY",
      eyebrow: "VATICAN DAILY TOUR",
      description: "바티칸 미술관과 성베드로 성당을 깊이 있게 이해하는 데일리투어.",
      href: "/product/detail/daily/rome-vatican-daily",
      productType: "daily",
      categoryId: "rome",
      regionId: "rome",
    },
    {
      id: "rome-city-walk",
      number: "02",
      title: "로마 시내 워킹 투어",
      region: "ROME",
      duration: "HALF DAY",
      eyebrow: "ROME CITY WALK",
      description: "고대 로마의 중심과 도시의 결을 따라 걷는 시내 워킹 투어.",
      href: "/product/detail/daily/rome-city-walk",
      productType: "daily",
      categoryId: "rome",
      regionId: "rome",
    },
    {
      id: "firenze-uffizi-daily",
      number: "03",
      title: "피렌체 우피치 미술관 투어",
      region: "FIRENZE",
      duration: "1 DAY",
      eyebrow: "UFFIZI ART TOUR",
      description: "르네상스 회화의 흐름을 도시와 함께 읽는 피렌체 미술관 투어.",
      href: "/product/detail/daily/firenze-uffizi-daily",
      productType: "daily",
      categoryId: "firenze",
      regionId: "firenze",
    },
    {
      id: "venezia-walk-daily",
      legacyProductId: PRODUCT_LEGACY_ID_CANDIDATES["venezia-walk-daily"],
      number: "04",
      title: "베네치아 수상 도시 산책",
      region: "VENEZIA",
      duration: "1 DAY",
      eyebrow: "VENEZIA WALK",
      description: "수상 도시의 골목, 광장, 운하를 연결해 베네치아를 이해하는 투어.",
      href: "/product/detail/daily/venezia-walk-daily",
      productType: "daily",
      categoryId: "venezia",
      regionId: "venezia",
    },
    {
      id: "napoli-pompei-daily",
      legacyProductId: PRODUCT_LEGACY_ID_CANDIDATES["napoli-pompei-daily"],
      number: "05",
      title: "나폴리 · 폼페이 데일리 투어",
      region: "NAPOLI",
      duration: "1 DAY",
      eyebrow: "POMPEI DAILY TOUR",
      description: "남부의 빛과 고대 도시의 흔적을 하루 동선으로 연결하는 투어.",
      href: "/product/detail/daily/napoli-pompei-daily",
      productType: "daily",
      categoryId: "napoli",
      regionId: "napoli",
    },
  ],
  galleryImages: [
    "/assets/daily-gallery-01.jpg",
    "/assets/daily-gallery-02.jpg",
    "/assets/daily-gallery-03.jpg",
    "/assets/daily-gallery-04.jpg",
    "/assets/daily-gallery-05.jpg",
    "/assets/daily-gallery-06.jpg",
  ],
};

const DEFAULT_DATA: ProductTemplateData = SEMI_PACKAGE_DATA;

export default function ProductTemplate({ pageData = DEFAULT_DATA }: { pageData?: ProductTemplateData }) {
  const [viewMode, setViewMode] = useState<ProductViewMode>("gallery");
  const [activeCategory, setActiveCategory] = useState(pageData.categories[0]?.id ?? "all");
  const [activeProductId, setActiveProductId] = useState(pageData.products[0]?.id ?? "");

  /*
    ProductTemplate Route Data Sync
    ------------------------------------------
    App.tsx에서 /product/semi, /product/daily 라우팅에 따라 pageData가 바뀌면
    이전 목록의 activeCategory / activeProductId가 남지 않도록 초기화한다.
  */
  useEffect(() => {
    setActiveCategory(pageData.categories[0]?.id ?? "all");
    setActiveProductId(pageData.products[0]?.id ?? "");
  }, [pageData]);

  const activeProduct = useMemo(() => {
    return pageData.products.find((item) => item.id === activeProductId) ?? pageData.products[0];
  }, [activeProductId, pageData.products]);

  const filteredProducts = useMemo(() => {
    /*
      ProductTemplate Filter Placeholder
      ------------------------------------------
      현재 Mock Data에는 category id와 상품 데이터가 직접 연결되어 있지 않다.
      백엔드 연동 전까지는 기존처럼 전체 상품을 유지한다.
      이후 category / region 필드가 연결되면 여기에서만 필터링하면 된다.
    */
    if (activeCategory === "all") {
      return pageData.products;
    }

    return pageData.products.filter((product) => product.categoryId === activeCategory);
  }, [activeCategory, pageData.products]);

  return (
    <div className="product-page-shell">
      <style>{`
        .product-page-shell {
          /* Desktop Responsive
             - 100vw 대신 100% 기준 사용
             - vertical scrollbar 폭으로 인한 가로 흔들림 방지
             - Desktop / Tablet Landscape 최소 폭은 1024px 유지 */
          width: 100%;
          min-width: 1024px;
          min-height: 100vh;
          overflow-x: hidden;
          background: #ffffff;
          color: #151515;
        }

        .product-page-main {
          /* Desktop Responsive
             - 메인 Hero / Section2 / Section3 / Section4와 동일하게
               페이지 루트는 100% 기준으로 통일 */
          width: 100%;
          min-width: 1024px;
          background: #ffffff;
          overflow-x: hidden;
        }

        .product-editorial-grid {
          /* Desktop Responsive
             - 1700px Figma canvas를 직접 width로 고정하지 않고
               실제 표시 기준은 1600px로 제한
             - ProductList처럼 1700 canvas scale이 필요한 영역은
               해당 컴포넌트 내부에서 별도 ResizeObserver로 처리 */
          width: 100%;
          max-width: 1600px;
          min-width: 1024px;
          margin: 0 auto;
          position: relative;
          box-sizing: border-box;
        }
      `}</style>

      <main className="product-page-main">
        <CategoryFilter
          categories={pageData.categories}
          activeCategory={activeCategory}
          onCategoryChange={(categoryId) => {
            setActiveCategory(categoryId);
            const firstMatchedProduct =
              categoryId === "all"
                ? pageData.products[0]
                : pageData.products.find((product) => product.categoryId === categoryId);

            setActiveProductId(firstMatchedProduct?.id ?? pageData.products[0]?.id ?? "");
          }}
          activeProduct={activeProduct}
        />

        {/*
          ProductList 연결

          기존 오류 원인:
          items라는 변수를 선언하지 않은 상태에서 items={items}로 전달해서
          ReferenceError: items is not defined가 발생했다.

          수정:
          ProductTemplateData의 상품 배열인 pageData.products를 그대로 전달한다.
        */}
        <ProductList
          items={filteredProducts}
          viewMode={viewMode}
          onViewModeChange={setViewMode}
          pageTitle={pageData.pageTitle}
          categories={pageData.categories}
        />
      </main>
    </div>
  );
}
