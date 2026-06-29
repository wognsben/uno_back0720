import { useEffect, useMemo, useState } from "react";
import imgImage10 from "./45f0f4367d0864282c8c2c2a4259edb97f283fb8.png";

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
    image: imgImage10,
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
    image: imgImage10,
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
    image: imgImage10,
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
    image: imgImage10,
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
    image: imgImage10,
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
    image: imgImage10,
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
    image: imgImage10,
    href: "/product/daily/france?view=gallery",
  },
];

const SEMI_ITEMS = HERO_ITEMS.filter((item) => item.category === "semi");
const DAILY_ITEMS = HERO_ITEMS.filter((item) => item.category === "daily");

function useHeroRotation() {
  const [activeIndex, setActiveIndex] = useState(0);

  useEffect(() => {
    const timer = window.setInterval(() => {
      setActiveIndex((prev) => (prev + 1) % HERO_ITEMS.length);
    }, 5200);

    return () => window.clearInterval(timer);
  }, []);

  return { activeIndex, setActiveIndex };
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

function HeroNavigation({
  activeItem,
  onSelect,
  onNavigate,
}: {
  activeItem: HeroItem;
  onSelect: (id: string) => void;
  /** ProductTemplate 연결: 클릭 시 해당 상품 서브페이지로 이동 */
  onNavigate: (item: HeroItem) => void;
}) {
  const renderCountry = (item: HeroItem) => {
    const isActive = item.id === activeItem.id;

    return (
      <button
        key={item.id}
        type="button"
        onMouseEnter={() => onSelect(item.id)}
        onFocus={() => onSelect(item.id)}
        onClick={() => onNavigate(item)}
        className={`hero-nav-country ${isActive ? "is-active" : ""}`}
        aria-pressed={isActive}
      >
        <span className="hero-nav-country-en">{item.country}</span>
        <span className="hero-nav-country-ko">{item.countryKo}</span>
      </button>
    );
  };

  return (
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
  );
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
    <div className="absolute z-20 bg-[rgba(255,255,255,0.9)] border-3 border-solid border-white h-[256px] left-[276px] overflow-visible rounded-[18px] top-0 w-[417px]" data-name="card 13">
      <div
        className="[word-break:keep-all] absolute inset-0 box-border flex flex-col items-center justify-center leading-[0] not-italic px-[24px] text-[#151515] text-center hero-title-block"
        style={{ fontVariationSettings: '"wght" 400' }}
      >
        <p className="leading-[30px] text-[25px] mb-[14px] whitespace-pre-wrap">{item.title}</p>
        <p className="leading-[23px] text-[15px] tracking-[0.24px] whitespace-pre-wrap">{item.subtitle}</p>
      </div>
    </div>
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
  return (
    <div className="hero-grid-shell bg-white h-[725px] overflow-visible relative shrink-0 w-screen">
      <div className="absolute left-1/2 top-0 h-[725px] w-[1700px] -translate-x-1/2">
        <Group item={item} />
      </div>
    </div>
  );
}

export default function Frame() {
  const { activeIndex, setActiveIndex } = useHeroRotation();
  const activeItem = HERO_ITEMS[activeIndex];

  const loadedImages = useMemo(() => HERO_ITEMS.map((item) => item.image), []);

  const handleSelect = (id: string) => {
    const nextIndex = HERO_ITEMS.findIndex((item) => item.id === id);
    if (nextIndex >= 0) {
      setActiveIndex(nextIndex);
    }
  };

  const handleNavigate = (item: HeroItem) => {
    /*
      ProductTemplate 연결 지점

      현재 임시 라우트:
      - /product/semi/italy?view=gallery
      - /product/daily/italy?view=gallery

      실제 우노트래블 PHP 백엔드 연동 시
      아래 item.href 값만 기존 URL 규칙에 맞게 교체하면 된다.
    */
    window.location.href = item.href;
  };

  return (
    <div className="absolute left-0 top-0 h-[1040px] w-screen overflow-visible bg-white">
      <div className="relative h-full w-screen">
        <div className="bg-white content-stretch flex flex-col items-center justify-start pb-[54px] pt-[88px] px-0 relative size-full">
      <style>{`
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

        .hero-grid-shell {
          border-radius: 0;
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

      <HeroNavigation activeItem={activeItem} onSelect={handleSelect} onNavigate={handleNavigate} />

      <div aria-hidden="true" className="hidden">
        {loadedImages.map((image) => (
          <img key={image} src={image} alt="" />
        ))}
      </div>

      <Frame1 item={activeItem} />
        </div>
      </div>
    </div>
  );
}
