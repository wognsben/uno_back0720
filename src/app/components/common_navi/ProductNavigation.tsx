/* ==========================================================
   ProductNavigation

   UNOTRAVEL Global Product Navigation

   ----------------------------------------------------------

   메인 Hero와 모든 상품 서브페이지에서 공통으로 사용하는
   최상위 상품 Navigation 컴포넌트.

   중요:
   이 파일은 메인 Hero 내부에 있던 HeroNavigation의
   JSX 구조 / className / CSS를 최대한 그대로 유지한다.

   즉,
   메인페이지에서 보이는 Navigation과
   상품 서브페이지에서 보이는 Navigation이 다르게 보이지 않도록
   기존 hero-* 클래스명을 그대로 사용한다.

   사용 위치

   Main
   ------------------------------------------
   Header
   ProductNavigation
   Hero

   Product Type A
   ------------------------------------------
   Header
   ProductNavigation
   ProductHero
   ProductList

========================================================== */


/* ==========================================================
   Backend Connection

   현재는 Figma Make Mock Data 사용.

   추후 PHP Backend 연동 시
   category / region 값을 기준으로
   HERO_ITEMS 또는 PRODUCT_NAV_ITEMS를 DB 데이터로 교체하면 된다.

========================================================== */

type HeroCategory = "semi" | "daily";

type HeroItem = {
  id: string;
  category: HeroCategory;
  country: string;
  countryKo: string;
  title: string;
  subtitle: string;
  meta: string[];

  /*
    ProductTemplate route

    메인 Hero에서 상품 서브페이지(Type A)로 이동할 때 사용하는 URL.
    실제 우노트래블 PHP 백엔드 연동 시
    아래 href 값만 기존 URL 규칙에 맞게 교체하면 된다.
  */
  href: string;
};

/* ==========================================================
   Navigation Data

   메인 Hero에 있던 HERO_ITEMS에서
   Navigation에 필요한 데이터만 유지.

   주의:
   이 공통 Navigation에는 이미지 / 카드 / Hero Rotation 로직을 넣지 않는다.
   Navigation 표시와 이동에 필요한 정보만 둔다.

========================================================== */

const HERO_ITEMS: HeroItem[] = [
  {
    id: "semi-italy",
    category: "semi",
    country: "ITALY",
    countryKo: "이탈리아",
    title: "SEMI PACKAGE · ITALY",
    subtitle: "남부 · 북부 · 시칠리아 · 돌로미티",
    meta: ["EST.2011", "ITALY", "SEMI PACKAGE", "MEDITERRANEAN"],
    href: "/product/semi/italy?view=gallery",
  },
  {
    id: "semi-spain",
    category: "semi",
    country: "SPAIN",
    countryKo: "스페인",
    title: "SEMI PACKAGE · SPAIN",
    subtitle: "바르셀로나 · 안달루시아",
    meta: ["EST.2011", "SPAIN", "SEMI PACKAGE", "CURATED ROUTE"],
    href: "/product/semi/spain?view=gallery",
  },
  {
    id: "semi-portugal",
    category: "semi",
    country: "PORTUGAL",
    countryKo: "포르투갈",
    title: "SEMI PACKAGE · PORTUGAL",
    subtitle: "리스본 · 포르투",
    meta: ["EST.2011", "PORTUGAL", "SEMI PACKAGE", "ATLANTIC ROUTE"],
    href: "/product/semi/portugal?view=gallery",
  },
  {
    id: "semi-greece-turkey",
    category: "semi",
    country: "GREECE / TURKEY",
    countryKo: "그리스 / 터키",
    title: "SEMI PACKAGE · GREECE / TURKEY",
    subtitle: "산토리니 · 이스탄불",
    meta: ["EST.2011", "GREECE", "TURKEY", "SEMI PACKAGE"],
    href: "/product/semi/greece-turkey?view=gallery",
  },
  {
    id: "semi-egypt",
    category: "semi",
    country: "EGYPT",
    countryKo: "이집트",
    title: "SEMI PACKAGE · EGYPT",
    subtitle: "카이로 · 룩소르",
    meta: ["EST.2011", "EGYPT", "SEMI PACKAGE", "ANCIENT ROUTE"],
    href: "/product/semi/egypt?view=gallery",
  },
  {
    id: "daily-italy",
    category: "daily",
    country: "ITALY",
    countryKo: "이탈리아",
    title: "DAILY TOUR · ITALY",
    subtitle: "로마 · 피렌체 · 나폴리 · 베네치아",
    meta: ["EST.2011", "ITALY", "DAILY TOUR", "LOCAL SCENE"],
    href: "/product/daily/italy?view=gallery",
  },
  {
    id: "daily-france",
    category: "daily",
    country: "FRANCE",
    countryKo: "프랑스",
    title: "DAILY TOUR · FRANCE",
    subtitle: "파리 · 몽생미셸",
    meta: ["EST.2011", "FRANCE", "DAILY TOUR", "FRENCH ROUTE"],
    href: "/product/daily/france?view=gallery",
  },
];

const SEMI_ITEMS = HERO_ITEMS.filter((item) => item.category === "semi");
const DAILY_ITEMS = HERO_ITEMS.filter((item) => item.category === "daily");

/* ==========================================================
   Active Route Helper

   현재 URL을 기준으로 Active Navigation을 계산한다.

   예)
   /product/semi/portugal

   →

   semi-portugal active

   Figma Make 환경에서는 react-router가 없을 수 있으므로
   window.location.pathname을 기준으로 처리한다.

========================================================== */

function getActiveItemId() {
  if (typeof window === "undefined") {
    return HERO_ITEMS[0]?.id;
  }

  const pathname = window.location.pathname;

  const matchedItem = HERO_ITEMS.find((item) => {
    const targetPath = item.href.split("?")[0];
    return pathname.startsWith(targetPath);
  });

  return matchedItem?.id || HERO_ITEMS[0]?.id;
}

/* ==========================================================
   ProductNavigation Component

   메인 Hero에 있던 HeroNavigation 구조를 그대로 유지한다.

   기존 메인 Hero 구조:
   - .hero-product-nav
   - .hero-nav-block
   - .hero-nav-title
   - .hero-country-list
   - .hero-nav-country

   위 className을 그대로 유지해야
   메인페이지와 서브페이지의 Navigation이 같은 톤으로 보인다.

========================================================== */

export default function ProductNavigation() {
  const activeItemId = getActiveItemId();

  const handleNavigate = (item: HeroItem) => {
    /*
      ProductTemplate 연결 지점

      현재 임시 라우트:
      - /product/semi/italy?view=gallery
      - /product/daily/italy?view=gallery

      실제 우노트래블 PHP 백엔드 연동 시
      아래 item.href 값만 기존 URL 규칙에 맞게 교체하면 된다.
    */
    window.history.pushState({}, "", item.href);
window.dispatchEvent(new Event("unotravel:navigate"));
  };

  const renderCountry = (item: HeroItem) => {
    const isActive = item.id === activeItemId;

    return (
      <button
        key={item.id}
        type="button"
        onClick={() => handleNavigate(item)}
        className={`hero-nav-country ${isActive ? "is-active" : ""}`}
        aria-pressed={isActive}
      >
        <span className="hero-nav-country-en">{item.country}</span>
        <span className="hero-nav-country-ko">{item.countryKo}</span>
      </button>
    );
  };

  return (
    <>
      <style>{`
        /*
          Hero Navigation Original Style
          ----------------------------------------------------------
          메인 Hero 내부 Navigation CSS를 그대로 이동.

          주의:
          className을 product-*로 바꾸지 않는다.
          메인페이지와 상품 서브페이지에서 1:1로 같은 형태를 유지하기 위함.
        */
        .hero-product-nav {
          width: 100vw;
          min-height: 170px;
          margin-bottom: 22px;
          border: 1px solid rgba(21, 21, 21, 0.12);
          border-radius: 24px;
          background: rgba(255, 255, 255, 0.78);
          box-shadow: 0 30px 90px rgba(21, 21, 21, 0.055);
          display: grid;
          grid-template-columns: 1fr 1px 0.82fr;
          align-items: stretch;
          overflow: hidden;
          backdrop-filter: blur(14px);
        }

        .hero-nav-block {
          position: relative;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          padding: 24px 36px 28px;
          min-width: 0;
        }

        .hero-nav-divider {
          width: 1px;
          height: calc(100% - 54px);
          align-self: center;
          background: rgba(21, 21, 21, 0.12);
        }

        .hero-nav-index {
          display: none;
        }

        .hero-nav-title {
          font-family: var(--font-en);
          font-size: 34px;
          line-height: 1;
          letter-spacing: 0.02em;
          color: #151515;
          margin-bottom: 30px;
          text-align: center;
        }

        .hero-country-list {
          display: flex;
          align-items: center;
          justify-content: center;
          width: 100%;
        }

        .hero-country-list--semi {
          gap: 0;
        }

        .hero-country-list--daily {
          gap: 0;
        }

        .hero-nav-country {
          position: relative;
          appearance: none;
          border: 0;
          background: transparent;
          padding: 0 36px;
          min-height: 44px;
          cursor: pointer;
          color: #151515;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          transition:
            opacity 0.24s ease,
            transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .hero-nav-country + .hero-nav-country::before {
          content: "";
          position: absolute;
          left: 0;
          top: 4px;
          width: 1px;
          height: 36px;
          background: rgba(21, 21, 21, 0.12);
        }

        .hero-nav-country-en {
          font-family: var(--font-en);
          font-size: 17px;
          line-height: 1;
          letter-spacing: 0.08em;
          color: #151515;
        }

        .hero-nav-country-ko {
          margin-top: 12px;
          font-family: var(--font-ko);
          font-size: 13px;
          line-height: 1;
          letter-spacing: -0.02em;
          color: rgba(21, 21, 21, 0.68);
        }

        .hero-nav-country::after {
          content: "";
          position: absolute;
          left: 50%;
          bottom: -10px;
          width: 5px;
          height: 5px;
          border-radius: 999px;
          background: #fcc800;
          opacity: 0;
          transform: translateX(-50%) scale(0.6);
          transition:
            opacity 0.24s ease,
            transform 0.24s cubic-bezier(0.22, 1, 0.36, 1);
        }

        .hero-nav-country:hover,
        .hero-nav-country.is-active {
          opacity: 1;
          transform: translateY(-2px);
        }

        .hero-nav-country:not(.is-active) {
          opacity: 0.62;
        }

        .hero-nav-country:hover::after,
        .hero-nav-country.is-active::after {
          opacity: 1;
          transform: translateX(-50%) scale(1);
        }

        .hero-nav-country:focus-visible {
          outline: 1px solid rgba(21, 21, 21, 0.4);
          outline-offset: 8px;
        }
      `}</style>

      <div className="hero-product-nav" aria-label="Main product category navigation">
        <div className="hero-nav-block hero-nav-block--semi">
          <div className="hero-nav-title">SEMI PACKAGE</div>
          <div className="hero-country-list hero-country-list--semi">
            {SEMI_ITEMS.map(renderCountry)}
          </div>
        </div>

        <div className="hero-nav-divider" />

        <div className="hero-nav-block hero-nav-block--daily">
          <div className="hero-nav-title">DAILY TOUR</div>
          <div className="hero-country-list hero-country-list--daily">
            {DAILY_ITEMS.map(renderCountry)}
          </div>
        </div>
      </div>
    </>
  );
}
