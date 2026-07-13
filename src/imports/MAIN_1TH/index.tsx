import { useEffect, useMemo, useRef, useState } from "react";
import imgSemiItaly from "../세미패키지메인히어로그리드/3f5da2e34aadc41b88babc2cb3cf79d54480fb17.png";
import imgSemiSpain from "../세미패키지메인히어로그리드/4330107f5001d8438ca2a32856e91d36fc97e09f.png";
import imgSemiPortugal from "../세미패키지메인히어로그리드/a1bb687947753b4c890d720a1b31402344e5c88d.png";
import imgSemiGreeceTurkey from "../세미패키지메인히어로그리드/ca8b91484dd437b9300e61e1611bbff92bf1b412.png";
import imgSemiEgypt from "../세미패키지메인히어로그리드/724c69b9aeb2689cd61d20b56baa17a9093971ca.png";
import imgDailyItaly from "../세미패키지메인히어로그리드/176f62c60c3978e3ac5e3d4bb41f76b68f88c0b6.png";
import { getProducts, type ProductSummary } from "../../api/reservationApi";

/*
  ProductNavigation 공통화
  ------------------------------------------
  메인 Hero 내부 전용 HeroNavigation 대신
  모든 페이지에서 사용하는 공통 ProductNavigation을 사용한다.
*/
import ProductNavigation from "../../app/components/common_navi/ProductNavigation";

/* Desktop Responsive Base
   - 실제 카드 배치 canvas는 Figma 기준 1700px
   - 화면에서 보이는 desktop 원본 기준은 1600px로 제한
   - 1600px 이하에서는 canvas 전체를 부모 폭에 맞춰 scale 처리 */
const HERO_CANVAS_WIDTH = 1700;
const HERO_DESKTOP_BASE_WIDTH = 1600;
const HERO_CANVAS_HEIGHT = 725;

type HeroCategory = "semi" | "daily";

type HeroItem = {
  id: string;
  category: HeroCategory;
  country: string;
  countryKo: string;
  title: string;
  subtitle: string;
  meta: string[];
  image: string;
  /** ProductTemplate route: 메인 Hero에서 상품 서브페이지(Type A)로 이동할 때 사용하는 URL */
  href: string;
};

const HERO_ITEMS: HeroItem[] = [
  {
    id: "semi-italy",
    category: "semi",
    country: "ITALY",
    countryKo: "이탈리아",
    title: "SEMI PACKAGE · ITALY",
    subtitle: "남부 · 북부 · 시칠리아 · 돌로미티",
    meta: ["EST.2011", "ITALY", "SEMI PACKAGE", "MEDITERRANEAN"],
    image: imgSemiItaly,
    href: "/product/detail/italy-11",
  },
  {
    id: "semi-spain",
    category: "semi",
    country: "SPAIN",
    countryKo: "스페인",
    title: "SEMI PACKAGE · SPAIN",
    subtitle: "바르셀로나 · 안달루시아",
    meta: ["EST.2011", "SPAIN", "SEMI PACKAGE", "CURATED ROUTE"],
    image: imgSemiSpain,
    href: "/product/detail/spain-9",
  },
  {
    id: "semi-portugal",
    category: "semi",
    country: "PORTUGAL",
    countryKo: "포르투갈",
    title: "SEMI PACKAGE · PORTUGAL",
    subtitle: "리스본 · 포르투",
    meta: ["EST.2011", "PORTUGAL", "SEMI PACKAGE", "ATLANTIC ROUTE"],
    image: imgSemiPortugal,
    href: "/product/detail/portugal-8",
  },
  {
    id: "semi-greece-turkey",
    category: "semi",
    country: "GREECE / TURKEY",
    countryKo: "그리스 / 터키",
    title: "SEMI PACKAGE · GREECE / TURKEY",
    subtitle: "산토리니 · 이스탄불",
    meta: ["EST.2011", "GREECE", "TURKEY", "SEMI PACKAGE"],
    image: imgSemiGreeceTurkey,
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
    image: imgSemiEgypt,
    href: "/product/detail/egypt-8",
  },
  {
    id: "daily-italy",
    category: "daily",
    country: "ITALY",
    countryKo: "이탈리아",
    title: "DAILY TOUR · ITALY",
    subtitle: "로마 · 피렌체 · 나폴리 · 베네치아",
    meta: ["EST.2011", "ITALY", "DAILY TOUR", "LOCAL SCENE"],
    image: imgDailyItaly,
    href: "/product/detail/daily/rome-vatican-daily",
  },
  {
    id: "daily-france",
    category: "daily",
    country: "FRANCE",
    countryKo: "프랑스",
    title: "DAILY TOUR · FRANCE",
    subtitle: "파리 · 몽생미셸",
    meta: ["EST.2011", "FRANCE", "DAILY TOUR", "FRENCH ROUTE"],
    image: imgSemiSpain,
    href: "/product/daily/france?view=gallery",
  },
];


function mapRemoteProductToHeroItem(product: ProductSummary, index: number): HeroItem {
  const category = product.productType === "daily" ? "daily" : "semi";

  return {
    id: product.id,
    category,
    country: product.legacyCategory || (category === "daily" ? "DAILY TOUR" : "SEMI PACKAGE"),
    countryKo: product.legacyCategory || "",
    title: category === "daily" ? "DAILY TOUR" : "SEMI PACKAGE",
    subtitle: product.title,
    meta: ["UNOTRAVEL", category === "daily" ? "DAILY TOUR" : "SEMI PACKAGE", String(index + 1).padStart(2, "0")],
    image: product.thumbnailUrl || "",
    href: product.href,
  };
}

function useHeroRotation(itemCount: number) {
  const [activeIndex, setActiveIndex] = useState(0);

  useEffect(() => {
    if (itemCount <= 1) {
      setActiveIndex(0);
      return;
    }

    const timer = window.setInterval(() => {
      setActiveIndex((prev) => (prev + 1) % itemCount);
    }, 5200);

    return () => window.clearInterval(timer);
  }, [itemCount]);

  return { activeIndex, setActiveIndex };
}

function useDesktopHeroScale() {
  const shellRef = useRef<HTMLDivElement | null>(null);
  /*
  Desktop Responsive Initial Scale
  ------------------------------------------
  Logo / ProductNavigation 등 SPA 방식으로 메인 Hero에 재진입할 때
  ResizeObserver가 실행되기 전 초기 scale이 잘못 잡혀 카드가 순간적으로 줄어드는 현상을 줄인다.
*/
const [scale, setScale] = useState(() => {
  if (typeof window === "undefined") {
    return HERO_DESKTOP_BASE_WIDTH / HERO_CANVAS_WIDTH;
  }

  const initialWidth = Math.min(
    document.documentElement.clientWidth || HERO_DESKTOP_BASE_WIDTH,
    HERO_DESKTOP_BASE_WIDTH
  );

  return initialWidth / HERO_CANVAS_WIDTH;
});

  useEffect(() => {
    const updateScale = () => {
      const shellWidth =
        shellRef.current?.getBoundingClientRect().width ||
        document.documentElement.clientWidth ||
        HERO_DESKTOP_BASE_WIDTH;

      /* Desktop Responsive
         - 1600px 이상: 1600px를 원본 표시 기준으로 고정
         - 1600px 이하: 부모 폭 기준으로 1700px canvas를 축소
         - 100vw를 쓰지 않아 vertical scrollbar 폭으로 인한 가로 스크롤을 방지 */
      const visibleWidth = Math.min(shellWidth, HERO_DESKTOP_BASE_WIDTH);
      const nextScale = visibleWidth / HERO_CANVAS_WIDTH;

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

function SharedImage({
  item,
  className,
}: {
  item: HeroItem;
  className: string;
}) {
  return (
    <img
      alt=""
      className={`${className} hero-shared-image`}
      src={item.image}
      decoding="async"
    />
  );
}

function navigateToProductDetail(path: string) {
  if (typeof window === "undefined" || !path) return;

  window.scrollTo({ top: 0, behavior: "auto" });

  if (window.location.pathname + window.location.search === path) {
    window.dispatchEvent(new Event("unotravel:navigate"));
    return;
  }

  window.history.pushState({}, "", path);
  window.dispatchEvent(new Event("unotravel:navigate"));
}

function Card({ item }: { item: HeroItem }) {
  return (
    <div className="absolute bg-[rgba(0,0,0,0)] border-3 border-solid border-white h-[480px] left-0 overflow-clip rounded-[18px] top-[2px] w-[268px]" data-name="card 1">
      <div className="absolute h-[725px] left-[-4px] top-[-3px] w-[1700px]" data-name="image 10">
        <SharedImage item={item} className="absolute inset-0 max-w-none object-cover pointer-events-none size-full" />
      </div>
    </div>
  );
}

function Card6({ item }: { item: HeroItem }) {
  return (
    <div className="absolute bg-[rgba(0,0,0,0)] border-3 border-solid border-white h-[320px] left-[1035px] overflow-clip rounded-[18px] top-[162px] w-[268px]" data-name="card 8">
      <div className="absolute h-[725px] left-[-1038px] top-[-168px] w-[1700px]" data-name="image 10">
        <SharedImage item={item} className="absolute inset-0 max-w-none object-cover pointer-events-none size-full" />
      </div>
    </div>
  );
}

function Card7({ item }: { item: HeroItem }) {
  return (
    <div className="absolute bg-[rgba(0,0,0,0)] border-3 border-solid border-white h-[206px] left-[1310px] overflow-clip rounded-[18px] top-[276px] w-[390px]" data-name="card 9">
      <div className="absolute h-[725px] left-[-1314px] top-[-281px] w-[1700px]" data-name="image 10">
        <SharedImage item={item} className="absolute inset-0 max-w-none object-cover pointer-events-none size-full" />
      </div>
    </div>
  );
}

function Card8({ item }: { item: HeroItem }) {
  return (
    <div className="absolute bg-[rgba(0,0,0,0)] border-3 border-solid border-white h-[266px] left-[1304px] overflow-clip rounded-[18px] top-0 w-[396px]" data-name="card 10">
      <div className="absolute h-[725px] left-[-1308px] top-[-3px] w-[1700px]" data-name="image 10">
        <SharedImage item={item} className="absolute inset-0 max-w-none object-cover pointer-events-none size-full" />
      </div>
    </div>
  );
}

function Card9({ item }: { item: HeroItem }) {
  return (
    <div className="absolute bg-[rgba(0,0,0,0)] border-3 border-solid border-white h-[152px] left-[1035px] overflow-clip rounded-[18px] top-0 w-[261px]" data-name="card 11">
      <div className="absolute h-[725px] left-[-1035px] top-[-3px] w-[1700px]" data-name="image 10">
        <SharedImage item={item} className="absolute inset-0 max-w-none object-cover pointer-events-none size-full" />
      </div>
    </div>
  );
}

function Card10({ item }: { item: HeroItem }) {
  return (
    <div className="absolute bg-[rgba(0,0,0,0)] border-3 border-solid border-white h-[256px] left-[704px] overflow-clip rounded-[18px] top-0 w-[325px]" data-name="card 12">
      <div className="absolute h-[725px] left-[-709px] top-[-3px] w-[1700px]" data-name="image 10">
        <SharedImage item={item} className="absolute inset-0 max-w-none object-cover pointer-events-none size-full" />
      </div>
    </div>
  );
}

function Card11({ item }: { item: HeroItem }) {
  return (
    <button
      type="button"
      className="absolute z-20 bg-[rgba(255,255,255,0.9)] border-3 border-solid border-white h-[256px] left-[276px] overflow-visible rounded-[18px] top-0 w-[417px] cursor-pointer p-0"
      data-name="card 13"
      aria-label={`${item.title} 상세페이지로 이동`}
      onClick={() => navigateToProductDetail(item.href)}
    >
      <div
        className="[word-break:keep-all] absolute inset-0 box-border flex flex-col items-center justify-center leading-[0] not-italic px-[24px] text-[#151515] text-center hero-title-block"
        style={{ fontVariationSettings: '"wght" 400' }}
      >
        <p className="leading-[30px] text-[25px] mb-[14px] whitespace-pre-wrap">
  {item.country}
</p>
        <p className="leading-[23px] text-[15px] tracking-[0.24px] whitespace-pre-wrap">{item.subtitle}</p>
      </div>
    </button>
  );
}

function Card3Y() {
  return <div className="absolute h-[240px] left-0 rounded-[20px] top-0 w-[174px]" data-name="card 3-y" />;
}

function Frame2({ item }: { item: HeroItem }) {
  return (
    <div className="absolute h-[240px] left-0 top-[485px] w-[222px]">
      <Card3Y />
      <div className="-translate-x-1/2 -translate-y-1/2 [word-break:break-word] absolute bg-clip-text bg-gradient-to-b flex flex-col hero-meta-block from-black h-[240px] justify-center leading-[0] left-[111px] not-italic text-[22px] text-[transparent] text-center to-[#666] top-[120px] tracking-[0.72px] w-[222px]">
        <p className="leading-[30px] whitespace-pre-wrap">{item.meta.join("\n\n")}</p>
      </div>
    </div>
  );
}

function Card2({ item }: { item: HeroItem }) {
  return (
    <div className="absolute bg-[rgba(0,0,0,0)] border-3 border-solid border-white h-[240px] left-[229px] overflow-clip rounded-[18px] top-[485px] w-[243px]" data-name="card 4">
      <div className="absolute h-[725px] left-[-238px] top-[-488px] w-[1700px]" data-name="image 10">
        <SharedImage item={item} className="absolute inset-0 max-w-none object-cover pointer-events-none size-full" />
      </div>
    </div>
  );
}

function Card3({ item }: { item: HeroItem }) {
  return (
    <div className="absolute bg-[rgba(0,0,0,0)] border-3 border-solid border-white h-[240px] left-[478px] overflow-clip rounded-[18px] top-[485px] w-[314px]" data-name="card 5">
      <div className="absolute h-[725px] left-[-483px] top-[-488px] w-[1700px]" data-name="image 10">
        <SharedImage item={item} className="absolute inset-0 max-w-none object-cover pointer-events-none size-full" />
      </div>
    </div>
  );
}

function Card4({ item }: { item: HeroItem }) {
  return (
    <div className="absolute bg-[rgba(0,0,0,0)] border-3 border-solid border-white h-[240px] left-[799px] overflow-clip rounded-[18px] top-[485px] w-[678px]" data-name="card 6">
      <div className="absolute h-[725px] left-[-801px] top-[-488px] w-[1700px]" data-name="image 10">
        <SharedImage item={item} className="absolute inset-0 max-w-none object-cover pointer-events-none size-full" />
      </div>
    </div>
  );
}

function Card5() {
  return <div className="absolute bg-[rgba(255,255,255,0.1)] border-3 border-solid border-white h-[240px] left-[1483px] rounded-[18px] top-[485px] w-[217px]" data-name="card 7" />;
}

function Card1({ item }: { item: HeroItem }) {
  return (
    <div className="bg-[rgba(166,38,38,0.2)] border-3 border-solid border-white h-[590px] overflow-clip relative rounded-[18px] w-[220px]" data-name="card 2">
      <div className="absolute flex h-[1700px] items-center justify-center left-[-339px] top-[-526px] w-[725px]">
        <div className="-rotate-90 flex-none">
          <div className="h-[725px] relative w-[1700px]" data-name="image 10">
            <SharedImage item={item} className="absolute inset-0 max-w-none object-cover pointer-events-none size-full" />
          </div>
        </div>
      </div>
    </div>
  );
}

function Group({ item }: { item: HeroItem }) {
  return (
    <div className="absolute contents left-0 top-0">
      <Card item={item} />
      <Card6 item={item} />
      <Card7 item={item} />
      <Card8 item={item} />
      <Card9 item={item} />
      <Card10 item={item} />
      <Card11 item={item} />
      <Frame2 item={item} />
      <Card2 item={item} />
      <Card3 item={item} />
      <Card4 item={item} />
      <Card5 />
      <div className="absolute flex h-[220px] items-center justify-center left-[275px] top-[262px] w-[754px]">
        <div className="flex-none rotate-90">
          <Card1 item={item} />
        </div>
      </div>
    </div>
  );
}

function Frame1({ item }: { item: HeroItem }) {
  const { shellRef, scale } = useDesktopHeroScale();

  return (
    <div
      ref={shellRef}
      className="hero-grid-shell bg-white overflow-hidden relative shrink-0 w-full"
      style={{
        /* Desktop Responsive
           - 1700px 기준 Figma 카드 배치는 유지
           - 1600px를 desktop 원본 표시 기준으로 사용
           - 부모 실제 width 기준으로 canvas를 축소해 1024~1600 구간 가로 스크롤을 방지 */
        height: `${HERO_CANVAS_HEIGHT * scale}px`,
      }}
    >
      <div
        className="hero-grid-canvas absolute left-1/2 top-0 h-[725px] w-[1700px]"
        style={{
          /* Desktop Responsive
             - 카드 좌표는 그대로 두고 stage 전체만 scale 처리 */
          transform: `translateX(-50%) scale(${scale})`,
        }}
      >
        <Group item={item} />
      </div>
    </div>
  );
}

export default function Frame() {
  const [remoteHeroItems, setRemoteHeroItems] = useState<HeroItem[]>([]);

  useEffect(() => {
    let isCancelled = false;

    getProducts()
      .then((response) => {
        if (isCancelled) return;

        setRemoteHeroItems(
          (response.items ?? [])
            .map(mapRemoteProductToHeroItem)
            .filter((item) => item.image),
        );
      })
      .catch(() => {
        if (!isCancelled) {
          setRemoteHeroItems([]);
        }
      });

    return () => {
      isCancelled = true;
    };
  }, []);

  const heroItems = remoteHeroItems;
  const { activeIndex } = useHeroRotation(heroItems.length);
  const activeItem = heroItems[activeIndex % Math.max(heroItems.length, 1)];

  const loadedImages = useMemo(() => [...new Set(heroItems.map((item) => item.image).filter(Boolean))], [heroItems]);

  return (
    <div className="relative left-0 top-0 w-full min-w-[1024px] overflow-hidden bg-white">
      {/* Desktop Responsive
          - Main Hero root는 100vw 대신 100% 기준
          - Desktop/Tablet Landscape 최소 폭은 1024px 유지
          - App.tsx 고정 height에 의존하지 않고 내부 content 높이로 section height를 결정 */}
      <div className="relative w-full min-w-[1024px] overflow-hidden">
        <div className="bg-white content-stretch flex flex-col items-center justify-start pb-[54px] pt-[88px] px-0 relative w-full overflow-hidden">
      <style>{`
        .hero-product-nav {
          /* Desktop Responsive
             - ProductNavigation은 Section3 Slider가 아니므로 100vw 사용 금지
             - 부모 폭 기준 100% 사용, 1700px 이상에서는 기존 편집형 폭 유지 */
          width: 100%;
          max-width: 1600px;
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


        /*
          Main Hero ProductNavigation Override
          ------------------------------------------
          공통 ProductNavigation을 메인 Hero 안에서 사용할 때의 폭 보정.
          ProductNavigation 내부 className은 그대로 유지하되,
          메인 Hero에서는 100vw가 아닌 부모 100% / 최대 1600px 기준으로 표시한다.

          Mega Panel이 아래로 펼쳐져야 하므로
          hero-product-nav 자체는 overflow visible로 보정한다.
        */
        .main-hero-product-navigation {
          width: 100%;
          max-width: 1600px;
          margin-bottom: 22px;
          position: relative;
          z-index: 30;
        }

        .main-hero-product-navigation .hero-product-nav {
          width: 100%;
          max-width: 1600px;
          margin-bottom: 0;
          overflow: visible;
        }

        .hero-grid-shell {
          /* Desktop Responsive
             - Hero 이미지 카드 영역은 부모 100% 기준
             - 1700px Figma canvas는 React에서 계산한 scale로 축소
             - 표시 기준은 1600px로 제한 */
          width: 100%;
          max-width: 100%;
          min-width: 1024px;
          border-radius: 0;
          overflow: hidden;
        }

        .hero-grid-canvas {
          transform-origin: top center;
          will-change: transform;
        }

        /* Desktop Minimum Responsive
           - 1024~1600px 구간에서는 grid 구조 변경 없이 canvas만 비율 축소
           - layout/grid 자체는 유지하고, overflow를 줄여 가로 스크롤을 방지 */
        @media (min-width: 1024px) and (max-width: 1599px) {
          .hero-product-nav {
            min-height: 154px;
            margin-bottom: 18px;
          }

          .hero-nav-block {
            padding: 22px 24px 24px;
          }

          .hero-nav-title {
            font-size: clamp(26px, 2.35vw, 34px);
            margin-bottom: 24px;
          }

          .hero-nav-country {
            padding: 0 clamp(14px, 2.1vw, 30px);
          }

          .hero-nav-country-en {
            font-size: clamp(13px, 1.2vw, 17px);
          }

          .hero-nav-country-ko {
            font-size: clamp(11px, 1vw, 13px);
          }

  
        /*
          Main Hero ProductNavigation Override
          ------------------------------------------
          공통 ProductNavigation을 메인 Hero 안에서 사용할 때의 폭 보정.
          ProductNavigation 내부 className은 그대로 유지하되,
          메인 Hero에서는 100vw가 아닌 부모 100% / 최대 1600px 기준으로 표시한다.

          Mega Panel이 아래로 펼쳐져야 하므로
          hero-product-nav 자체는 overflow visible로 보정한다.
        */
        .main-hero-product-navigation {
          width: 100%;
          max-width: 1600px;
          margin-bottom: 22px;
          position: relative;
          z-index: 30;
        }

        .main-hero-product-navigation .hero-product-nav {
          width: 100%;
          max-width: 1600px;
          margin-bottom: 0;
          overflow: visible;
        }

        .hero-grid-shell {
            min-height: 0;
          }
        }

        .hero-shared-image {
          opacity: 1;
          transform: none;
          animation: none;
          will-change: auto;
        }

        .hero-title-block {
          font-family: var(--font-en);
          transition: opacity 0.24s ease;
          will-change: auto;
        }

        .hero-title-block p:first-child {
          font-family: var(--font-en);
        }

        .hero-title-block p:last-child {
          font-family: var(--font-ko);
        }

        .hero-meta-block {
          font-family: var(--font-en);
        }
      `}</style>

      {/*
        ProductNavigation 공통화
        ------------------------------------------
        기존 메인 Hero 전용 HeroNavigation을 제거하고
        서브페이지와 동일한 공통 ProductNavigation을 사용한다.
        따라서 ProductNavigation의 hover Mega Panel 수정사항이
        메인페이지와 서브페이지에 동일하게 반영된다.
      */}
      <div className="main-hero-product-navigation">
        <ProductNavigation disableScrollHandle />
      </div>

      <div aria-hidden="true" className="hidden">
        {loadedImages.map((image) => (
          <img key={image} src={image} alt="" />
        ))}
      </div>

      {activeItem ? <Frame1 item={activeItem} /> : <div className="hero-grid-shell bg-white w-full" style={{ height: 725 }} />}
        </div>
      </div>
    </div>
  );
}
