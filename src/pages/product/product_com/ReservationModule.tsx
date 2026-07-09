// ReservationModule.tsx
// 상품 상세 예약 모듈 전체를 담당한다.
// 세미패키지 예약 UI의 보딩패스 표시, 인원 선택, 총액 계산, 장바구니 저장, 예약 페이지 이동을 처리한다.
// 좌측 설명 컬럼(Boarding Pass / 접기)과 검은 BOOK 탭은 사용하지 않는다.
// 예약 모듈은 항상 열린 상태로 노출한다.

import { useEffect, useMemo, useState } from "react";
import BookingBoardingPass from "./BoardingPass";
import DailyTourCalendar from "./DailyTourCalendar";
import {
  type AvailableDate,
  PriceText,
  formatAvailablePeople,
  getAvailabilityClassName,
  getAvailabilityStatus,
  getInitialDailyDateId,
} from "./reservationUtils";

const CART_STORAGE_KEY = "unotravel_cart_items";
const CART_COUNT_STORAGE_KEY = "unotravel_cart_count";
const PENDING_RESERVATION_STORAGE_KEY = "unotravel_pending_reservation";
const RESERVATION_PAGE_URL = "/reservation";
const MY_CART_PAGE_URL = "/mypage/cart";

type ProductKind = "semi" | "daily";

type ReservationProductContext = {
  id: string;
  productType: ProductKind;
  title: string;
  href: string;
  currency?: string;
  basePrice?: number;
};

type ReservationStoragePayload = {
  productId: string;
  productType: ProductKind;
  title: string;
  href: string;
  selectedDateId: string;
  selectedDateLabel: string;
  selectedWeekday: string;
  personCount: number;
  unitPrice: number;
  totalPrice: number;
  currency: string;
  seatsBeforeSelection: number;
  remainingSeatsAfterSelection: number;
  guide: string;
  createdAt: number;
};

const parseCartItems = (value: string | null): ReservationStoragePayload[] => {
  if (!value) return [];

  try {
    const parsedValue = JSON.parse(value);
    return Array.isArray(parsedValue) ? parsedValue : [];
  } catch {
    return [];
  }
};

const navigateInternal = (href: string) => {
  if (typeof window === "undefined") return;

  window.history.pushState({}, "", href);
  window.dispatchEvent(new PopStateEvent("popstate"));
  window.dispatchEvent(new Event("unotravel:navigate"));
  window.scrollTo({ top: 0, left: 0, behavior: "auto" });
};

const RESERVATION_MODULE_STYLE = `
  .pd-book-drawer {
  position: sticky;
  top: 104px;
  z-index: 35;
  width: min(1760px, calc(100vw - 0.01px));
  margin: 0 auto 72px;
  border-top: 1px solid rgba(21, 21, 21, 0.16);
  border-bottom: 1px solid rgba(21, 21, 21, 0.16);
  background: rgba(255, 255, 255, 0.98);
  box-sizing: border-box;
}

  .pd-book-surface {
  display: grid;
  grid-template-columns: minmax(980px, 1fr) 250px;
  gap: 20px;
  align-items: stretch;
  min-height: 540px;
  padding: 32px 28px 30px;
  box-sizing: border-box;
}



  .pd-book-price-line {
    display: none;
  }


  .pd-book-screen {
    min-height: 0;
    padding: 0;
    background: #ffffff;
    color: #151515;
    overflow: visible;
  }

  .pd-book-side {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  gap: 18px;
  margin: 0;
  padding: 0 0 0 20px;
  border-left: 1px solid rgba(21, 21, 21, 0.12);
}

  .pd-book-side-count span,
  .pd-book-side-price span {
    display: block;
    margin-bottom: 10px;
    font-family: var(--font-en);
    font-size: 14px;
    line-height: 1;
    letter-spacing: 0.36em;
    color: rgba(21, 21, 21, 1.00);
    text-transform: uppercase;
  }

  .pd-book-side-count strong {
    display: block;
    font-family: var(--font-ko);
    font-size: 34px;
    line-height: 1;
    letter-spacing: -0.055em;
    font-weight: 760;
    color: #151515;
  }

  .pd-book-people-control {
  display: grid;
  grid-template-columns: 46px 1fr 46px;
  width: 100%;
  min-height: 50px;
  height: 50px;
  margin: 0;
  border: 1px solid rgba(21, 21, 21, 0.18);
}

  .pd-book-people-control button {
    appearance: none;
    border: 0;
    background: transparent;
    color: #151515;
    cursor: pointer;
    font-size: 18px;
    font-weight: 520;
  }

  .pd-book-people-control button:disabled {
    cursor: not-allowed;
    opacity: 0.32;
  }

  .pd-book-people-control span {
    display: flex;
    align-items: center;
    justify-content: center;
    border-left: 1px solid rgba(21, 21, 21, 0.14);
    border-right: 1px solid rgba(21, 21, 21, 0.14);
    font-family: var(--font-en);
    font-size: 16px;
    font-weight: 560;
  }

  .pd-book-side-price {
    display: block;
    padding: 18px 0 0;
    border-top: 1px solid rgba(21, 21, 21, 0.12);
  }

  .pd-book-side-price strong{
    display:flex;
    align-items:flex-end;
    gap:8px;
    font-family:var(--font-en);
    font-size:56px;
    line-height:.9;
    letter-spacing:-0.07em;
    font-weight:920;
    color:#151515;
}

.pd-book-price-symbol{
    font-size:.42em;
    line-height:1;
    font-weight:700;
    transform:translateY(-4px);
}

.pd-book-price-value{
    line-height:1;
}

  .pd-book-side-price strong .pd-price-symbol,
  .pd-book-side-price strong .pd-price-number {
    font-size: inherit;
    line-height: inherit;
    letter-spacing: inherit;
    font-weight: inherit;
    color: inherit;
  }

  .pd-book-side p {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 14px;
    line-height: 1.62;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.68);
    word-break: keep-all;
  }

  .pd-book-actions {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 10px;
    width: 100%;
    margin: 0;
  }

  .pd-book-action {
    appearance: none;
    min-height: 54px;
    height: 54px;
    border: 1px solid rgba(21, 21, 21, 0.18);
    background: #ffffff;
    color: #151515;
    cursor: pointer;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1;
    letter-spacing: -0.035em;
    font-weight: 650;
  }

  .pd-book-action.is-primary {
    background: #fcc800;
    border-color: #fcc800;
    color: #151515;
  }

  .pd-book-action.is-secondary {
    background: #ffffff;
    border-color: rgba(21, 21, 21, 0.18);
    color: #151515;
  }

  .pd-book-action:disabled {
    cursor: not-allowed;
    opacity: 0.36;
  }

@media (max-width: 1280px) {
  .pd-book-drawer {
    width: calc(100vw - 32px);
  }

  .pd-book-surface {
    grid-template-columns: minmax(760px, 1fr) 240px;
    gap: 18px;
    padding: 28px 20px 26px;
  }

  .pd-book-side {
    gap: 16px;
    padding-left: 18px;
  }
}
`;

export function ReservationModuleStyles() {
  return <style>{RESERVATION_MODULE_STYLE}</style>;
}

export type ReservationModuleProps = {
  product: ReservationProductContext;
  dates: AvailableDate[];
  selectedDateId?: string;
  initialPeople?: number;
  minPeople?: number;
  maxPeople?: number;
};

function ReservationModule({
  product,
  dates,
  selectedDateId,
  initialPeople = 1,
  minPeople = 1,
  maxPeople,
}: ReservationModuleProps) {
  const [people, setPeople] = useState(initialPeople);

const [dailySelectedDateId, setDailySelectedDateId] = useState(() =>
  selectedDateId || getInitialDailyDateId(dates),
);

const activeDateId =
  product.productType === "daily"
    ? dailySelectedDateId
    : selectedDateId || (dates[0]?.id ?? "");
  const activeDate = useMemo(
    () => dates.find((date) => date.id === activeDateId) ?? dates[0],
    [activeDateId, dates],
  );

const status = getAvailabilityStatus(activeDate);
const statusClassName = getAvailabilityClassName(activeDate);
const isReservationDisabled = !activeDate || status === "마감";

/*
  Backend Seats
  ------------------------------------------
  백엔드 연동 전에는 activeDate.seats를 예약 가능 인원으로 사용한다.
  이후 백엔드에서 날짜별 잔여 좌석을 내려주면 이 값만 교체하면 된다.
*/
const availableSeats = activeDate?.seats ?? 8;

const safeMaxPeople = maxPeople ?? Math.max(minPeople, availableSeats);
const safePeople = Math.max(minPeople, Math.min(people, safeMaxPeople));
const remainingSeats = Math.max(0, availableSeats - safePeople);

const unitPrice = activeDate?.price ?? product.basePrice ?? 0;
const totalPrice = unitPrice * safePeople;
const canDecrease = safePeople > minPeople;
const canIncrease = safePeople < safeMaxPeople;

  useEffect(() => {
    setPeople((value) => Math.max(minPeople, Math.min(value, safeMaxPeople)));
  }, [minPeople, safeMaxPeople]);

  const getReservationPayload = (): ReservationStoragePayload => {
    const seatsBeforeSelection = activeDate?.seats ?? 0;

    return {
      productId: product.id,
      productType: product.productType,
      title: product.title,
      href: product.href,
      selectedDateId: activeDate?.id ?? "",
      selectedDateLabel: activeDate?.label ?? "",
      selectedWeekday: activeDate?.day ?? "",
      personCount: safePeople,
      unitPrice,
      totalPrice,
      currency: product.currency ?? "KRW",
      seatsBeforeSelection,
      remainingSeatsAfterSelection: Math.max(0, seatsBeforeSelection - safePeople),
      guide: activeDate?.guide ?? "",
      createdAt: Date.now(),
    };
  };

  const handleCart = () => {
    if (typeof window === "undefined" || isReservationDisabled) return;

    const payload = getReservationPayload();
    const previousItems = parseCartItems(sessionStorage.getItem(CART_STORAGE_KEY));
    const existingIndex = previousItems.findIndex(
      (item) =>
        item.productId === payload.productId &&
        item.selectedDateId === payload.selectedDateId,
    );
    const nextItems =
      existingIndex >= 0
        ? previousItems.map((item, index) =>
            index === existingIndex ? payload : item,
          )
        : [payload, ...previousItems];

    sessionStorage.setItem(CART_STORAGE_KEY, JSON.stringify(nextItems));
    sessionStorage.setItem(CART_COUNT_STORAGE_KEY, String(nextItems.length));
    window.dispatchEvent(
      new CustomEvent("unotravel:cart-updated", {
        detail: { count: nextItems.length, items: nextItems },
      }),
    );
    navigateInternal(MY_CART_PAGE_URL);
  };

  const handleReserve = () => {
    if (typeof window === "undefined" || isReservationDisabled) return;

    sessionStorage.setItem(
      PENDING_RESERVATION_STORAGE_KEY,
      JSON.stringify(getReservationPayload()),
    );
    navigateInternal(RESERVATION_PAGE_URL);
  };

  return (
    <>
      <ReservationModuleStyles />
      <section className="pd-book-drawer" aria-label="세미패키지 예약 모듈">
  <div className="pd-book-surface">

          <div className="pd-book-screen">
  {product.productType === "daily" ? (
    <div className="pd-book-calendar-panel">
      <DailyTourCalendar
        dates={dates}
        selectedDateId={activeDateId}
        onSelectDate={setDailySelectedDateId}
      />
    </div>
  ) : (
    <BookingBoardingPass />
  )}
</div>

          <aside className="pd-book-side" aria-label="예약 정보">
            <div className="pd-book-side-count">
              <span>People</span>
              <strong>{safePeople}명</strong>
            </div>

            <div className="pd-book-people-control" aria-label="예약 인원 선택">
              <button
                type="button"
                disabled={!canDecrease}
                onClick={() => setPeople((value) => Math.max(minPeople, value - 1))}
                aria-label="인원 감소"
              >
                −
              </button>
              <span>{safePeople}</span>
              <button
                type="button"
                disabled={!canIncrease}
                onClick={() => setPeople((value) => Math.min(safeMaxPeople, value + 1))}
                aria-label="인원 증가"
              >
                +
              </button>
            </div>

            <div className="pd-book-side-price">
  <span>Total Price</span>

  <strong className="pd-book-price">
    <span className="pd-book-price-symbol">₩</span>
    <span className="pd-book-price-value">
      {totalPrice.toLocaleString("ko-KR")}
    </span>
  </strong>
</div>

            <p>{remainingSeats}명 예약 가능</p>

            <div className="pd-book-actions">
              <button
                type="button"
                className="pd-book-action is-secondary"
                disabled={isReservationDisabled}
                onClick={handleCart}
              >
                장바구니 담기
              </button>
              <button
                type="button"
                className={`pd-book-action is-primary ${statusClassName}`}
                disabled={isReservationDisabled}
                onClick={handleReserve}
              >
                예약하기
              </button>
            </div>
          </aside>
        </div>
      </section>
    </>
  );
}

export default ReservationModule;
