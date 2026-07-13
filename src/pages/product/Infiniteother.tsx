/* ==========================================================
   Infiniteother.tsx

   Product Detail Related Infinite Gallery

   사용 페이지
   - ProductDetail.tsx 하단 "다른 투어 상품" 영역

   원칙
   ------------------------------------------
   - ProductDetail 본문 높이 / 예약 Drawer / Body 이미지 레이아웃과 분리
   - 16:9 이미지 카드만 노출
   - ProductList/ProductTemplate에서 내려온 relatedProducts를 그대로 사용
   - 현재 상품 제외 / 같은 타입 상품 필터링은 ProductDetail에서 처리
========================================================== */

import { useEffect, useMemo, useRef } from "react";
import type { MouseEvent } from "react";

export type ProductKind = "semi" | "daily";

export type InfiniteOtherProduct = {
  id: string;
  title: string;
  eyebrow: string;
  duration: string;
  price: number;
  href: string;
  image: string;
};

export type InfiniteOtherSourceProduct = Partial<InfiniteOtherProduct> & {
  id: string;
  legacyProductId?: number | string;
  legacyFeeOptionId?: number | string;
  legacyPackageScheduleId?: number | string;
  title: string;
  href?: string;
  image?: string;
  thumbnail?: string;
  eyebrow?: string;
  region?: string;
  duration?: string;
  price?: number;
  basePrice?: number;
  productType?: ProductKind;
  category?: ProductKind | string;
  type?: ProductKind | string;
};

export const getProductListItemType = (
  product: InfiniteOtherSourceProduct,
): ProductKind | undefined => {
  const value = String(
    product.productType ?? product.category ?? product.type ?? "",
  ).toLowerCase();

  if (
    value.includes("daily") ||
    value.includes("데일리") ||
    value.includes("일일")
  ) {
    return "daily";
  }

  if (value.includes("semi") || value.includes("세미")) {
    return "semi";
  }

  return undefined;
};

export const normalizeProductListRelatedProducts = (
  products: InfiniteOtherSourceProduct[] | undefined,
  currentProductType: ProductKind,
  currentProductId: string,
  fallbackImage: string,
): InfiniteOtherProduct[] => {
  if (!products?.length) return [];

  return products
    .filter((product) => product.id !== currentProductId)
    .filter((product) => getProductListItemType(product) === currentProductType)
    .map((product) => ({
      id: product.id,
      title: product.title,
      eyebrow:
        product.eyebrow ??
        product.region ??
        (currentProductType === "daily" ? "DAILY TOUR" : "SEMI PACKAGE"),
      duration: product.duration ?? "",
      price: product.price ?? product.basePrice ?? 0,
      href: product.href ?? `/product/detail/${product.id}`,
      image: product.image ?? product.thumbnail ?? fallbackImage,
    }));
};

type InfiniteOtherProps = {
  products: InfiniteOtherProduct[];
  label: string;
  title?: string;
  onNavigate: (event: MouseEvent<HTMLAnchorElement>, href: string) => void;
};

const INFINITE_OTHER_STYLE = `
  .pd-related-section {
    width: 1700px;
    margin: 0;
    padding: 56px 50px 48px;
    box-sizing: border-box;
    overflow: hidden;
    background: #ffffff;
  }

  .pd-related-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 36px;
    position: relative;
    z-index: 2;
  }

  .pd-related-heading {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 54px;
    line-height: 1;
    letter-spacing: -0.065em;
    color: #151515;
  }

  .pd-related-viewport {
    width: 100%;
    height: 276px;
    overflow: hidden;
    position: relative;
    perspective: 1200px;
    cursor: grab;
    touch-action: pan-y;
    user-select: none;
    z-index: 1;
  }

  .pd-related-viewport.is-dragging {
    cursor: grabbing;
  }

  .pd-related-track {
    position: absolute;
    inset: 0 auto auto 0;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
    will-change: transform;
  }

  .pd-related-card {
    position: absolute;
    left: 0;
    top: 0;
    bottom: auto;
    width: clamp(300px, 27vw, 390px);
    margin-right: 42px;
    overflow: visible;
    text-decoration: none;
    color: inherit;
    background: transparent;
    border: 0 !important;
    border-radius: 0;
    box-shadow: none !important;
    transform-style: preserve-3d;
    transform-origin: center center;
    will-change: transform, opacity;
    transition: opacity 0.22s ease-out;
  }

  .pd-related-image-wrap {
    width: 100%;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    border-radius: 0;
    background: #f2efe8;
    position: relative;
    transform: translateZ(0);
  }

  .pd-related-image-wrap::after {
    content: none;
  }

  .pd-related-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    user-select: none;
    pointer-events: none;
  }

  .pd-related-caption {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding-top: 13px;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1.28;
    letter-spacing: -0.045em;
    color: #151515;
    word-break: keep-all;
  }

  .pd-related-caption span {
    display: block;
  }
`;

export default function InfiniteOther({
  products,
  label,
  title = "다른 투어 상품",
  onNavigate,
}: InfiniteOtherProps) {
  const viewportRef = useRef<HTMLDivElement | null>(null);
  const cardRefs = useRef<Array<HTMLAnchorElement | null>>([]);
  const dragStateRef = useRef({
    isDragging: false,
    lastX: 0,
    dragStartX: 0,
    dragStartTime: 0,
    hasDragged: false,
  });

  const galleryProducts = useMemo(() => {
    if (products.length === 0) return [];
    const minRenderCount = 12;
    const repeatCount = Math.max(
      3,
      Math.ceil(minRenderCount / products.length),
    );

    return Array.from({ length: repeatCount }).flatMap((_, cloneIndex) =>
      products.map((product) => ({
        ...product,
        galleryKey: `${product.id}-${cloneIndex}`,
      })),
    );
  }, [products]);

  useEffect(() => {
    const viewport = viewportRef.current;
    if (!viewport || galleryProducts.length === 0) return;

    let cards = cardRefs.current.filter(Boolean) as HTMLAnchorElement[];
    if (!cards.length) return;

    let itemWidth = 0;
    let totalWidth = 0;
    let visibleCenterX = 0;
    let position = 0;
    let velocity = 0;
    let smoothPosition = 0;
    let animationFrame = 0;

    const friction = 0.86;
    const wheelMultiplier = 0.052;
    const lerpSpeed = 0.13;
    const clamp = (min: number, max: number, value: number) =>
      Math.min(max, Math.max(min, value));
    const mapRange = (
      inMin: number,
      inMax: number,
      outMin: number,
      outMax: number,
      value: number,
    ) => outMin + (outMax - outMin) * ((value - inMin) / (inMax - inMin || 1));
    const wrap = (value: number, max: number) => ((value % max) + max) % max;
    const easeScale = (value: number) =>
      value < 0.5 ? 2 * value * value : -1 + (4 - 2 * value) * value;

    const measure = () => {
      cards = cardRefs.current.filter(Boolean) as HTMLAnchorElement[];
      const firstCard = cards[0];
      if (!firstCard) return;
      const style = window.getComputedStyle(firstCard);
      itemWidth = firstCard.offsetWidth + parseFloat(style.marginRight || "0");
      totalWidth = itemWidth * cards.length;
      visibleCenterX = viewport.getBoundingClientRect().width / 2;
    };

    const animate = () => {
      const dragState = dragStateRef.current;
      if (!dragState.isDragging) {
        position += velocity;
        velocity *= friction;
      }
      smoothPosition += (position - smoothPosition) * lerpSpeed;

      if (totalWidth > 0) {
        const viewportWidth = viewport.getBoundingClientRect().width;
        cards.forEach((card, index) => {
          const loopX = wrap(index * itemWidth - smoothPosition, totalWidth);
          const finalX =
            loopX > viewportWidth + itemWidth ? loopX - totalWidth : loopX;
          const cardCenterX = finalX + itemWidth / 2;
          const distance = Math.abs(cardCenterX - visibleCenterX);
          const t = easeScale(
            clamp(0, 1, distance / Math.max(viewportWidth, 900)),
          );
          const scale = mapRange(0, 1, 1, 0.9, t);
          const yOffset = mapRange(0, 1, 0, 10, t);
          const isRenderable =
            finalX > -itemWidth * 1.05 &&
            finalX < viewportWidth + itemWidth * 1.25;

          card.style.transform = `translate3d(${finalX}px, ${yOffset}px, 0) scale(${scale})`;
          card.style.opacity = isRenderable ? "1" : "0";
          card.style.pointerEvents = isRenderable ? "auto" : "none";
          card.style.filter = "none";
        });
      }

      animationFrame = window.requestAnimationFrame(animate);
    };

    const handleWheel = (event: WheelEvent) => {
      const target = event.target as HTMLElement | null;
      const isImageHover = Boolean(target?.closest(".pd-related-image-wrap"));

      if (!isImageHover) return;

      event.preventDefault();
      velocity = clamp(-20, 20, velocity + event.deltaY * wheelMultiplier);
    };

    const handleTouchStart = (event: TouchEvent) => {
      dragStateRef.current.lastX = event.touches[0].clientX;
    };

    const handleTouchMove = (event: TouchEvent) => {
      const currentX = event.touches[0].clientX;
      const deltaX = currentX - dragStateRef.current.lastX;
      position -= deltaX;
      velocity = clamp(-14, 14, -deltaX * 0.22);
      dragStateRef.current.lastX = currentX;
      dragStateRef.current.hasDragged = true;
    };

    const handleMouseDown = (event: globalThis.MouseEvent) => {
      dragStateRef.current = {
        isDragging: true,
        lastX: event.clientX,
        dragStartX: event.clientX,
        dragStartTime: performance.now(),
        hasDragged: false,
      };
      velocity = 0;
      viewport.classList.add("is-dragging");
    };

    const handleMouseMove = (event: globalThis.MouseEvent) => {
      const dragState = dragStateRef.current;
      if (!dragState.isDragging) return;
      const deltaX = event.clientX - dragState.lastX;
      position -= deltaX * 0.8;
      dragState.lastX = event.clientX;
      if (Math.abs(event.clientX - dragState.dragStartX) > 6) {
        dragState.hasDragged = true;
      }
    };

    const handleMouseUp = (event: globalThis.MouseEvent) => {
      const dragState = dragStateRef.current;
      if (!dragState.isDragging) return;
      viewport.classList.remove("is-dragging");
      dragState.isDragging = false;
      const deltaX = event.clientX - dragState.dragStartX;
      const deltaTime = (performance.now() - dragState.dragStartTime) / 1000;
      if (deltaTime > 0) {
        velocity = clamp(-16, 16, -(deltaX / deltaTime) * 0.022);
      }
    };

    measure();
    animate();
    viewport.addEventListener("wheel", handleWheel, { passive: false });
    viewport.addEventListener("touchstart", handleTouchStart, { passive: true });
    viewport.addEventListener("touchmove", handleTouchMove, { passive: true });
    viewport.addEventListener("mousedown", handleMouseDown);
    viewport.addEventListener("mousemove", handleMouseMove);
    window.addEventListener("mouseup", handleMouseUp);
    window.addEventListener("resize", measure);

    return () => {
      window.cancelAnimationFrame(animationFrame);
      viewport.removeEventListener("wheel", handleWheel);
      viewport.removeEventListener("touchstart", handleTouchStart);
      viewport.removeEventListener("touchmove", handleTouchMove);
      viewport.removeEventListener("mousedown", handleMouseDown);
      viewport.removeEventListener("mousemove", handleMouseMove);
      window.removeEventListener("mouseup", handleMouseUp);
      window.removeEventListener("resize", measure);
    };
  }, [galleryProducts]);

  const handleClick = (event: MouseEvent<HTMLAnchorElement>, href: string) => {
    if (dragStateRef.current.hasDragged) {
      event.preventDefault();
      dragStateRef.current.hasDragged = false;
      return;
    }

    onNavigate(event, href);
  };

  if (products.length === 0) return null;

  return (
    <section
      className="pd-related-section"
      aria-label="관련 상품"
      data-webgl-section="related-products"
    >
      <style>{INFINITE_OTHER_STYLE}</style>

      <div className="pd-related-head">
        <div>
          <div className="pd-section-label">{label}</div>
          <h2 className="pd-related-heading">{title}</h2>
        </div>
      </div>

      <div
        className="pd-related-viewport"
        ref={viewportRef}
        data-related-infinite-gallery
      >
        <div className="pd-related-track">
          {galleryProducts.map((product, index) => (
            <a
              key={product.galleryKey}
              ref={(node) => {
                cardRefs.current[index] = node;
              }}
              className="pd-related-card"
              href={product.href}
              onClick={(event) => handleClick(event, product.href)}
            >
              <div className="pd-related-image-wrap" data-webgl-media-wrap>
                <img
                  className="pd-related-image"
                  src={product.image}
                  alt={product.title}
                  draggable={false}
                  data-webgl-media
                  data-webgl-media-kind="related"
                />
              </div>
              <div className="pd-related-caption">
                <span>{product.title}</span>
              </div>
            </a>
          ))}
        </div>
      </div>
    </section>
  );
}
