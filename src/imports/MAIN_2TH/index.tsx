/*
  Main page second section.
  Manages the JOURNEYS product preview area, semi-package/daily-tour tabs, title list hover states, and image preview motion.
  Product title rows and the right preview image link into product detail pages while product list/detail page rendering stays in src/pages/product.
*/
import { useEffect, useRef, useState } from "react";
import { gsap } from "gsap";
import { getProducts, type ProductSummary } from "../../api/reservationApi";
import imgImage21 from "./66cc55f7b10f09c3fd983a97214f281b9d50cc4f.png";


type PackageItem = {
  id: string;
  number: string;
  title: string;
  lines: string[];
  eyebrow: string;
  href: string;
  image?: string;
};

function mergeRemoteProducts(items: PackageItem[], remoteProducts: ProductSummary[]) {
  if (remoteProducts.length > 0) {
    return remoteProducts.map((product, index) => ({
      id: product.id,
      number: String(index + 1).padStart(2, "0"),
      title: product.title,
      lines: product.title ? [product.title] : [`${product.productType.toUpperCase()} TOUR`],
      eyebrow: product.productType === "daily" ? "DAILY TOUR" : "SEMI PACKAGE",
      href: product.href,
      image: product.thumbnailUrl,
    }));
  }

  const remoteById = new Map(remoteProducts.map((product) => [product.id, product]));

  return items.map((item) => {
    const remote = remoteById.get(item.id);

    if (!remote) {
      return item;
    }

    return {
      ...item,
      title: remote.title || item.title,
      lines: remote.title ? [remote.title] : item.lines,
      href: remote.href || item.href,
      image: remote.thumbnailUrl || item.image,
    };
  });
}

const SEMI_PACKAGE_ITEMS: PackageItem[] = [
  {
    id: "italy-11",
    number: "01",
    title: "이탈리아 일주 9박 11일",
    lines: ["이탈리아 일주 9박 11일"],
    eyebrow: "ITALY GRAND TOUR",
    href: "/product/detail/italy-11",
  },
  {
    id: "italy-9",
    number: "02",
    title: "이탈리아 일주 7박 9일",
    lines: ["이탈리아 일주 7박 9일"],
    eyebrow: "CLASSIC ITALY",
    href: "/product/detail/italy-9",
  },
  {
    id: "dolomiti-11",
    number: "03",
    title: "[8-9]월 한정 이탈리아일주+돌로미티 11",
    lines: ["[8-9]월 한정", "이탈리아일주+돌로미티 11"],
    eyebrow: "DOLOMITI LIMITED",
    href: "/product/detail/dolomiti-11",
  },
  {
    id: "sicilia-9",
    number: "04",
    title: "나의 두번째 이탈리아, 지중해의 황금빛 시칠리아 일주 9일",
    lines: ["나의 두번째 이탈리아,", "지중해의 황금빛 시칠리아 일주 9일"],
    eyebrow: "SICILIA COLLECTION",
    href: "/product/detail/sicilia-9",
  },
  {
    id: "art-tour-11",
    number: "05",
    title: "이탈리아 아트투어 일주 9박 11일",
    lines: ["이탈리아 아트투어 일주 9박 11일"],
    eyebrow: "ART TOUR",
    href: "/product/detail/art-tour-11",
  },
];

const DAILY_TOUR_ITEMS: PackageItem[] = [
  {
    id: "daily-01",
    number: "01",
    title: "DAILY TOUR 01",
    lines: ["DAILY TOUR 01"],
    eyebrow: "DAILY TOUR",
    href: "/product/detail/daily/rome-vatican-daily",
  },
  {
    id: "daily-02",
    number: "02",
    title: "DAILY TOUR 02",
    lines: ["DAILY TOUR 02"],
    eyebrow: "DAILY TOUR",
    href: "/product/detail/daily/rome-city-walk",
  },
  {
    id: "daily-03",
    number: "03",
    title: "DAILY TOUR 03",
    lines: ["DAILY TOUR 03"],
    eyebrow: "DAILY TOUR",
    href: "/product/detail/daily/firenze-uffizi-daily",
  },
  {
    id: "daily-04",
    number: "04",
    title: "DAILY TOUR 04",
    lines: ["DAILY TOUR 04"],
    eyebrow: "DAILY TOUR",
    href: "/product/detail/daily/venezia-walk-daily",
  },
  {
    id: "daily-05",
    number: "05",
    title: "DAILY TOUR 05",
    lines: ["DAILY TOUR 05"],
    eyebrow: "DAILY TOUR",
    href: "/product/detail/daily/napoli-pompei-daily",
  },
];

type CategoryKey = "semi" | "daily";

const CATEGORY_LABELS: Record<CategoryKey, { title: string; shortTitle: string; description: string[] }> = {
  semi: {
    title: "PREMIUM SEMI-PACKAGE",
    shortTitle: "SEMI-PACKAGE",
    description: ["VIP같은 세미 패키지 투어로 ", "더 다양한 일정을 만나보세요."],
  },
  daily: {
    title: "DAILY TOUR",
    shortTitle: "DAILY TOUR",
    description: ["하루의 일정 안에서 ", "더 깊은 도시의 장면을 만나보세요."],
  },
};

/*
  Section2 Desktop Responsive
  ------------------------------------------
  - 원본 Figma canvas는 1700px 좌표계를 유지한다.
  - Desktop 기준 폭은 1600px로 잡고, 1600px 이하에서는 canvas 전체를 scale한다.
  - 카드/이미지/absolute 좌표는 수정하지 않아 기존 디자인을 보존한다.
*/
const SECTION2_BASE_WIDTH = 1600;
const SECTION2_CANVAS_WIDTH = 1700;
const SECTION2_CANVAS_HEIGHT = 1245;

function navigateInternal(href: string) {
  if (typeof window === "undefined") return;

  if (window.location.pathname === href) {
    window.scrollTo({ top: 0, left: 0, behavior: "smooth" });
    return;
  }

  window.history.pushState({}, "", href);
  window.scrollTo({ top: 0, left: 0, behavior: "auto" });
  window.dispatchEvent(new Event("unotravel:navigate"));
}

function Group() {
  return (
    <div className="grid-cols-[max-content] grid-rows-[max-content] inline-grid leading-[0] place-items-start relative shrink-0">
      <div className="[word-break:break-word] col-1 flex flex-col font-en-bold h-[24px] justify-center ml-0 mt-0 not-italic relative row-1 text-[24px] text-black text-center w-[39.502px]">
        <p className="leading-[0px]">02</p>
      </div>
      <div className="[word-break:break-word] col-1 flex flex-col font-en h-[24px] justify-center ml-[135.93px] mt-0 not-italic relative row-1 text-[#151515] text-[24px] text-center w-[144.066px]">
        <p className="leading-[0px]">JOURNEYS</p>
      </div>
      <div className="col-1 h-0 ml-[76px] mt-[12px] relative row-1 w-[30px]">
        <div className="absolute inset-[-0.5px_0]">
          <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 30 1">
            <path d="M0 0.5H30" id="Vector 2" stroke="var(--stroke-0, black)" />
          </svg>
        </div>
      </div>
    </div>
  );
}

function Frame1() {
  return (
    <div className="absolute content-stretch flex items-center left-[120px] overflow-clip p-[10px] top-[44px] w-[300px]">
      <Group />
    </div>
  );
}

function PackageRow({
  item,
  isActive,
  onHover,
}: {
  item: PackageItem;
  isActive: boolean;
  onHover: () => void;
}) {
  return (
    <button
      type="button"
      onClick={() => navigateInternal(item.href)}
      onMouseEnter={onHover}
      onFocus={onHover}
      aria-label={`${item.title} 상세페이지로 이동`}
      className="group h-[82px] relative shrink-0 w-[456px]"
      data-name={item.title}
      style={{
        display: "block",
        border: "none",
        background: "transparent",
        padding: 0,
        cursor: "pointer",
        textAlign: "left",
      }}
    >
      <div className="[word-break:break-word] content-stretch flex gap-[14px] items-center leading-[0] overflow-visible px-[10px] py-[8px] relative rounded-[inherit] size-full text-center">
        <div
          className="relative shrink-0 rounded-full"
          style={{
            width: isActive ? 9 : 5,
            height: isActive ? 9 : 5,
            background: isActive ? "#fcc800" : "rgba(21,21,21,0.45)",
            transform: isActive ? "translateX(0) scale(1)" : "translateX(-2px) scale(0.85)",
            transition:
              "width 0.32s cubic-bezier(0.16, 1, 0.3, 1), height 0.32s cubic-bezier(0.16, 1, 0.3, 1), background 0.28s ease, transform 0.32s cubic-bezier(0.16, 1, 0.3, 1)",
          }}
        />

        {/*
          Product Number
          ------------------------------------------
          Hover 시 font-size가 36px로 커질 때
          숫자 2자리(예: 01)가 세로로 줄바꿈되는 문제 방지.

          기존 위치/간격/모션은 유지하고,
          숫자 텍스트에만 줄바꿈 방지 속성을 적용한다.
        */}
        <div
          className="flex flex-col font-en h-[58px] justify-center not-italic relative shrink-0 text-black w-[42px]"
          style={{
            fontSize: isActive ? 36 : 28,
            opacity: isActive ? 1 : 0.38,
            transform: isActive ? "translateY(-1px)" : "translateY(0)",
            transition:
              "font-size 0.32s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.28s ease, transform 0.32s cubic-bezier(0.16, 1, 0.3, 1)",
          }}
        >
          <p
            className="leading-[1]"
            style={{
              whiteSpace: "nowrap",
              wordBreak: "normal",
              overflowWrap: "normal",
            }}
          >
            {item.number}
          </p>
        </div>

        <div
          className="flex flex-col font-mixed h-[64px] justify-center relative shrink-0 text-[#151515] w-[372px]"
          style={{
            fontVariationSettings: '"wght" 400',
            fontSize: isActive ? 19 : 18,
            opacity: isActive ? 1 : 0.56,
            transform: isActive ? "translateX(7px)" : "translateX(0)",
            transition:
              "opacity 0.3s ease, transform 0.36s cubic-bezier(0.16, 1, 0.3, 1), font-size 0.28s ease",
          }}
        >
          {item.lines.map((line) => (
            <p key={line} className="leading-[28px] text-left">
              {line}
            </p>
          ))}
        </div>
      </div>

      <div
        aria-hidden
        className="absolute border-solid inset-0 pointer-events-none"
        style={{
          borderBottom: `1px solid ${isActive ? "#151515" : "rgba(21,21,21,0.24)"}`,
          transition: "border-color 0.32s ease",
        }}
      />
    </button>
  );
}

function PremiumSemiPackage({
  items,
  activeIndex,
  onChange,
}: {
  items: PackageItem[];
  activeIndex: number;
  onChange: (index: number) => void;
}) {
  return (
    <div className="absolute content-stretch flex flex-col gap-[4px] h-[470px] items-start left-[120px] overflow-visible p-[10px] top-[208px]" data-name="PRODUCT CATEGORY LIST">
      {items.map((item, index) => (
        <PackageRow
          key={item.id}
          item={item}
          isActive={activeIndex === index}
          onHover={() => onChange(index)}
        />
      ))}
    </div>
  );
}

function PreviewImage({
  items,
  activeIndex,
  hasInteracted,
}: {
  items: PackageItem[];
  activeIndex: number;
  hasInteracted: boolean;
}) {
  const safeIndex = activeIndex >= 0 && activeIndex < items.length ? activeIndex : 0;
  const activeItem = items[safeIndex];
  const stageRef = useRef<HTMLDivElement | null>(null);
  const featuredRefs = useRef<Array<HTMLDivElement | null>>([]);
  const thumbnailRefs = useRef<Array<HTMLButtonElement | null>>([]);
  const [imageError, setImageError] = useState(false);

  useEffect(() => {
    if (!stageRef.current) return;

    gsap.set(stageRef.current, { autoAlpha: 1 });

    featuredRefs.current.forEach((card, index) => {
      if (!card) return;
      gsap.set(card, {
        autoAlpha: index === 0 ? 1 : 0,
        scale: 1,
        zIndex: index === 0 ? 5 : 1,
        transformOrigin: "50% 50%",
      });
    });

    thumbnailRefs.current.forEach((thumbnail, index) => {
      if (!thumbnail) return;
      gsap.set(thumbnail, {
        x: index * 72,
        y: 382,
        rotate: 0,
        scale: index === 0 ? 1 : 0.92,
        autoAlpha: index === 0 ? 1 : 0.48,
        zIndex: index === 0 ? 12 : items.length - index,
      });
    });
  }, []);

  useEffect(() => {
    if (!stageRef.current) return;

    const featuredCards = featuredRefs.current.filter(Boolean) as HTMLDivElement[];
    const thumbnails = thumbnailRefs.current.filter(Boolean) as HTMLButtonElement[];

    gsap.killTweensOf([...featuredCards, ...thumbnails]);

    featuredRefs.current.forEach((card, index) => {
      if (!card) return;
      const isActive = index === safeIndex;

      gsap.to(card, {
        duration: isActive ? 0.82 : 0.48,
        ease: isActive ? "expo.out" : "power2.out",
        autoAlpha: isActive ? 1 : 0,
        scale: 1,
        zIndex: isActive ? 5 : 1,
        overwrite: true,
      });
    });

    thumbnailRefs.current.forEach((thumbnail, index) => {
      if (!thumbnail) return;
      const isActive = index === safeIndex;

      const bottomX = index * 72;
      const bottomY = 382;
      const sideX = 702;
      const sideY = index * 61;

      gsap.to(thumbnail, {
        duration: 0.86,
        ease: "expo.inOut",
        x: hasInteracted ? sideX : bottomX,
        y: hasInteracted ? sideY : bottomY,
        rotate: 0,
        scale: isActive ? 1 : 0.88,
        autoAlpha: isActive ? 1 : hasInteracted ? 0.52 : 0.38,
        zIndex: isActive ? 12 : items.length - Math.abs(index - safeIndex),
        overwrite: true,
      });
    });
  }, [safeIndex, hasInteracted]);

  return (
    <div className="absolute left-[880px] top-[208px] w-[780px] h-[455px]">
      <div
        ref={stageRef}
        className="absolute inset-0 overflow-visible opacity-0"
        aria-label={activeItem.title}
        role="img"
      >
        <div
          className="absolute left-0 top-0 h-[354px] w-[680px] overflow-hidden bg-[#f7f1df]"
          style={{
            borderRadius: 0,
            boxShadow: "0 24px 70px rgba(0,0,0,0.065)",
          }}
        >
          {!imageError ? (
            items.map((item, index) => (
              <div
                key={item.id}
                ref={(node) => {
                  featuredRefs.current[index] = node;
                }}
                className="absolute inset-0 overflow-hidden"
                style={{
                  opacity: index === 0 ? 1 : 0,
                  visibility: index === 0 ? "visible" : "hidden",
                  willChange: "opacity, transform",
                }}
              >
                {item.image ? (
                  <img
                    alt=""
                    className="absolute inset-0 size-full object-cover"
                    src={item.image}
                    onError={() => setImageError(true)}
                    style={{
                      objectPosition: "center center",
                      transform: "scale(1.04)",
                    }}
                  />
                ) : null}
              </div>
            ))
          ) : (
            <div className="absolute inset-0 bg-[#f7f1df]" />
          )}

          <div
            aria-hidden
            className="absolute inset-0"
            style={{
              background:
                "linear-gradient(90deg, rgba(0,0,0,0.055) 0%, rgba(0,0,0,0.015) 48%, rgba(255,255,255,0.04) 100%)",
              pointerEvents: "none",
            }}
          />

          <div
            aria-hidden
            className="absolute left-[22px] top-[22px]"
            style={{
              width: 6,
              height: 6,
              borderRadius: "50%",
              background: "#fcc800",
              boxShadow: "0 0 18px rgba(252,200,0,0.42)",
            }}
          />

          <div
            className="absolute left-[34px] top-[17px] font-en text-[#151515] text-[13px] tracking-[1.4px]"
            style={{
              opacity: 0.72,
            }}
          >
            {activeItem.eyebrow}
          </div>

          <button
            type="button"
            className="absolute inset-0"
            onClick={() => navigateInternal(activeItem.href)}
            aria-label={`${activeItem.title} 상세페이지로 이동`}
            style={{
              border: "none",
              background: "transparent",
              cursor: "pointer",
            }}
          />
        </div>

        <div
          aria-hidden
          className="absolute left-0 top-0 h-[455px] w-[780px] pointer-events-none"
        >
          {items.map((item, index) => (
            <button
              key={`${item.id}-thumbnail`}
              ref={(node) => {
                thumbnailRefs.current[index] = node;
              }}
              type="button"
              tabIndex={-1}
              className="absolute left-0 top-0 overflow-hidden bg-[#f7f1df]"
              style={{
                width: hasInteracted ? 68 : 72,
                height: hasInteracted ? 92 : 96,
                borderRadius: 0,
                pointerEvents: "none",
                willChange: "transform, opacity",
                border: "none",
                outline: "none",
                boxShadow: index === safeIndex ? "0 14px 30px rgba(0,0,0,0.11)" : "none",
                transition: "width 0.45s cubic-bezier(0.16, 1, 0.3, 1), height 0.45s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease",
              }}
            >
              {item.image ? (
                <img
                  alt=""
                  className="size-full object-cover"
                  src={item.image}
                  style={{
                    objectPosition: "center center",
                    transform: "scale(1.08)",
                  }}
                />
              ) : null}
              <span
                className="absolute left-[-30px] top-[1px] font-en text-[11px] text-[#151515]"
                style={{
                  opacity: hasInteracted ? 0.9 : 0,
                  transition: "opacity 0.32s ease",
                }}
              >
                {item.number}
              </span>
            </button>
          ))}
        </div>
      </div>
    </div>
  );
}

function Frame4({ item }: { item: PackageItem }) {
  const [isHovered, setIsHovered] = useState(false);

  const handleClick = () => {
    navigateInternal(item.href);
  };

  return (
    <button
      type="button"
      onClick={handleClick}
      aria-label={`${item.title} 상세보기`}
      className="group bg-[#fcc800] content-stretch flex items-start overflow-clip p-[10px] relative rounded-[20px] shrink-0"
      style={{
        border: "none",
        cursor: "pointer",
        transform: "translateY(0)",
        transition:
          "background 0.28s ease, transform 0.34s cubic-bezier(0.16, 1, 0.3, 1)",
      }}
      onMouseEnter={(event) => {
        setIsHovered(true);
        event.currentTarget.style.background = "#f2c100";
        event.currentTarget.style.transform = "translateY(-2px)";
      }}
      onMouseLeave={(event) => {
        setIsHovered(false);
        event.currentTarget.style.background = "#fcc800";
        event.currentTarget.style.transform = "translateY(0)";
      }}
      onFocus={(event) => {
        setIsHovered(true);
        event.currentTarget.style.background = "#f2c100";
        event.currentTarget.style.transform = "translateY(-2px)";
      }}
      onBlur={(event) => {
        setIsHovered(false);
        event.currentTarget.style.background = "#fcc800";
        event.currentTarget.style.transform = "translateY(0)";
      }}
    >
      <div className="[word-break:break-word] flex flex-col font-mixed h-[50px] justify-center leading-[0] relative shrink-0 text-[#151515] text-[0px] text-center w-[160px]" style={{ fontVariationSettings: '"wght" 400' }}>
        <p>
          <span className="leading-[40px] text-[20px]">{`상세보기 `}</span>
          <span
            className="inline-block leading-[40px] text-[32px]"
            style={{
              transform: isHovered ? "translateX(6px)" : "translateX(0)",
              transition: "transform 0.34s cubic-bezier(0.16, 1, 0.3, 1)",
            }}
          >
            →
          </span>
        </p>
      </div>
    </button>
  );
}

function Frame5({ category, activeItem }: { category: CategoryKey; activeItem: PackageItem }) {
  const label = CATEGORY_LABELS[category];

  return (
    <div className="absolute content-stretch flex gap-[10px] items-center justify-center left-[970px] overflow-clip p-[10px] top-[718px]">
      <div className="[word-break:break-word] flex flex-col font-mixed h-[60px] justify-center leading-[0] relative shrink-0 text-[#151515] text-[18px] text-center w-[260px] whitespace-pre-wrap" style={{ fontVariationSettings: '"wght" 400' }}>
        <p className="leading-[32px] mb-0">{label.description[0]}</p>
        <p className="leading-[32px]">{label.description[1]}</p>
      </div>
      <div className="h-[60px] relative shrink-0 w-0">
        <div className="absolute inset-[0_-1px]">
          <svg className="block size-full" fill="none" preserveAspectRatio="none" viewBox="0 0 2 60">
            <path d="M1 0V60" id="Vector 4" stroke="var(--stroke-0, #151515)" strokeWidth="2" />
          </svg>
        </div>
      </div>
      <div className="[word-break:break-word] flex flex-col font-en h-[60px] justify-center leading-[0] not-italic relative shrink-0 text-[18px] text-black text-center w-[160px]">
        <p className="leading-[0px]">{label.shortTitle}</p>
      </div>
      <Frame4 item={activeItem} />
    </div>
  );
}

function Group3({
  category,
  onChange,
}: {
  category: CategoryKey;
  onChange: (category: CategoryKey) => void;
}) {
  const tabs: Array<{ key: CategoryKey; title: string; count: string; left: number; width: number }> = [
    { key: "semi", title: "PREMIUM SEMI-PACKAGE", count: String(SEMI_PACKAGE_ITEMS.length).padStart(2, "0"), left: 1320, width: 270 },
    { key: "daily", title: "DAILY TOUR", count: String(DAILY_TOUR_ITEMS.length).padStart(2, "0"), left: 1559, width: 160 },
  ];

  return (
    <div className="[word-break:break-word] absolute contents font-en leading-[0] left-[1200px] not-italic text-[#151515] text-[20px] text-center top-[44px]">
      {tabs.map((tab) => {
        const isActive = category === tab.key;

        return (
          <button
            key={tab.key}
            type="button"
            onClick={() => onChange(tab.key)}
            className="-translate-x-1/2 -translate-y-1/2 absolute flex h-[30px] items-center justify-center gap-[8px] left-[var(--tab-left)] top-[56px]"
            style={{
              ["--tab-left" as string]: `${tab.left}px`,
              width: tab.width,
              border: "none",
              background: "transparent",
              padding: 0,
              cursor: "pointer",
              opacity: isActive ? 1 : 0.42,
              transition: "opacity 0.28s ease, transform 0.32s cubic-bezier(0.16, 1, 0.3, 1)",
            }}
            onMouseEnter={(event) => {
              if (!isActive) event.currentTarget.style.opacity = "0.75";
            }}
            onMouseLeave={(event) => {
              if (!isActive) event.currentTarget.style.opacity = "0.42";
            }}
            aria-pressed={isActive}
          >
            <span className="leading-[1]">{tab.title}</span>
            <span
              className="font-en leading-[1] text-[12px]"
              style={{ opacity: isActive ? 0.72 : 0.5 }}
            >
              {tab.count}
            </span>
            <span
              aria-hidden
              className="absolute bottom-[-7px] left-1/2 h-[1px] -translate-x-1/2 bg-[#151515]"
              style={{
                width: isActive ? "72%" : "0%",
                opacity: isActive ? 1 : 0,
                transition: "width 0.34s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.24s ease",
              }}
            />
          </button>
        );
      })}
    </div>
  );
}

function Frame() {
  const [category, setCategory] = useState<CategoryKey>("semi");
  const [activeIndex, setActiveIndex] = useState(0);
  const [hasInteracted, setHasInteracted] = useState(false);
  const [remoteProducts, setRemoteProducts] = useState<ProductSummary[]>([]);
  const baseItems = category === "semi" ? SEMI_PACKAGE_ITEMS : DAILY_TOUR_ITEMS;
  const items = mergeRemoteProducts(baseItems, remoteProducts);

  useEffect(() => {
    let isCancelled = false;

    getProducts({ type: category })
      .then((response) => {
        if (!isCancelled) {
          setRemoteProducts(response.items ?? []);
        }
      })
      .catch(() => {
        if (!isCancelled) {
          setRemoteProducts([]);
        }
      });

    return () => {
      isCancelled = true;
    };
  }, [category]);

  /*
    Section2 Desktop Responsive
    ------------------------------------------
    - 부모 실제 width 기준으로 scale 값을 계산한다.
    - 100vw를 직접 사용하지 않아 세로 스크롤바 폭 때문에 생기는 가로 스크롤을 방지한다.
    - 1600px 이상에서는 원본 크기, 1600px 미만에서는 동일 레이아웃을 비율 축소한다.
  */
const sectionRef = useRef<HTMLDivElement | null>(null);

/*
  Section2 Desktop Responsive Initial Scale
  ------------------------------------------
  Logo / ProductNavigation 등 SPA 방식으로
  메인페이지에 재진입할 때 ResizeObserver가 실행되기 전
  초기 scale이 잘못 적용되어 레이아웃이 순간적으로
  줄어드는 현상을 줄인다.
*/
/*
  Section2 Desktop Responsive Scale Calculator
  ------------------------------------------
  초기 렌더링과 ResizeObserver에서 동일한 계산식을 사용한다.
  계산 기준이 달라져 SPA 이동 시 확대/축소가 한 프레임 늦게 보이는 현상을 줄인다.
*/
const getSectionScale = (width: number) => {
  const safeWidth = Math.max(width, 1024);
  return Math.min(safeWidth / SECTION2_BASE_WIDTH, 1);
};

const [sectionScale, setSectionScale] = useState(() => {
  if (typeof window === "undefined") {
    return 1;
  }

  return getSectionScale(document.documentElement.clientWidth || SECTION2_BASE_WIDTH);
});

  /*
    Section2 Dynamic Height
    ------------------------------------------
    Canvas가 scale되면 부모 height도 함께 변경한다.
    App.tsx에서 고정 height를 사용하지 않도록 하기 위한 값.
  */
  const sectionHeight =
    SECTION2_CANVAS_HEIGHT * sectionScale;

  useEffect(() => {
    const target = sectionRef.current;
    if (!target) return;

    const updateScale = (width: number) => {
  const nextScale = getSectionScale(width);
  setSectionScale(nextScale);
};

    updateScale(target.clientWidth);

    const resizeObserver = new ResizeObserver((entries) => {
      const entry = entries[0];
      if (!entry) return;
      updateScale(entry.contentRect.width);
    });

    resizeObserver.observe(target);

    return () => {
      resizeObserver.disconnect();
    };
  }, []);

  return (
    <div
      ref={sectionRef}
      className="relative left-0 top-0 w-full min-w-[1024px] overflow-hidden bg-white"
      style={{
        height: sectionHeight,
      }}
    >
      <style>{`
        /*
          Section2 Font Rules

          전역 폰트 규칙
          ------------------------------------------
          --font-en : 영문 UI / 숫자 / 메뉴 / 라벨
          --font-ko : 한글 제목 / 본문 / 상품명

          Figma Make가 생성한 font-['Crimson_Text...'] 계열은
          직접 사용하지 않고 아래 공통 클래스로 연결한다.
        */
        .font-en,
        .font-en-bold {
          font-family: var(--font-en);
        }

        .font-mixed {
          font-family: var(--font-ko);
        }

        /*
          Section2 Desktop Responsive
          ------------------------------------------
          - Section3 Slider가 아니므로 w-screen/100vw를 사용하지 않는다.
          - 1700px Figma canvas는 absolute stage 안에서만 유지한다.
          - stage가 normal flow 폭을 밀지 않도록 부모는 overflow hidden 처리한다.
        */
        .section2-responsive-stage {
          position: absolute;
          left: 50%;
          top: 0;
          width: ${SECTION2_CANVAS_WIDTH}px;
          height: ${SECTION2_CANVAS_HEIGHT}px;
          transform-origin: top center;
          will-change: transform;
        }
      `}</style>

      <div
        className="section2-responsive-stage overflow-visible bg-white"
        style={{
          transform: `translateX(-50%) scale(${sectionScale})`,
        }}
      >
        <Frame1 />
        <PremiumSemiPackage
          items={items}
          activeIndex={activeIndex}
          onChange={(index) => {
            setHasInteracted(true);
            setActiveIndex(index);
          }}
        />
        <PreviewImage key={category} items={items} activeIndex={activeIndex} hasInteracted={hasInteracted} />

        <Frame5 category={category} activeItem={items[activeIndex] ?? items[0]} />

        <div className="absolute blur-[50px] h-[906px] left-[-36px] top-[808px] w-[1775px]" data-name="image 21">
          <div className="absolute inset-0 overflow-hidden pointer-events-none">
            <img alt="" className="absolute h-[125.39%] left-[-16.65%] max-w-none top-[-25.39%] w-[133.3%]" src={imgImage21} />
          </div>
        </div>
        <div className="absolute h-[447px] left-0 top-[798px] w-[1700px]" data-name="image 20">
          <div className="absolute inset-0 overflow-hidden pointer-events-none">
            <img alt="" className="absolute h-[254.14%] left-[-23.7%] max-w-none top-[-55.93%] w-[140.25%]" src={imgImage21} />
          </div>
        </div>
        <Group3
          category={category}
          onChange={(nextCategory) => {
            setCategory(nextCategory);
            setActiveIndex(0);
            setHasInteracted(false);
          }}
        />
      </div>
    </div>
  );
}

export default function Component2() {
  return (
    <div className="relative h-full w-full min-w-[1024px] overflow-hidden" data-name="메인페이지-2번 섹션">
      <Frame />
    </div>
  );
}
