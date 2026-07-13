// ReservationModule.tsx
// 상품 상세 예약 모듈 전체를 담당한다.
// 세미패키지 예약 UI의 보딩패스 표시, 인원 선택, 총액 계산, 예약 CTA 상태를 처리한다.
// 장바구니 저장과 예약 페이지 이동은 reservationStore.ts에 위임해 하단 예약 바와 충돌하지 않게 한다.
// 좌측 설명 컬럼(Boarding Pass / 접기)과 검은 BOOK 탭은 사용하지 않는다.
// 예약 모듈은 항상 열린 상태로 노출한다.

import { useEffect, useMemo, useState } from "react";
import { createPortal } from "react-dom";
import BookingBoardingPass from "./BoardingPass";
import DailyTourCalendar from "./DailyTourCalendar";
import {
  type AvailableDate,
  formatAvailablePeople,
  getAvailabilityClassName,
  getAvailabilityStatus,
  getInitialDailyDateId,
} from "./reservationUtils";
import {
  DEFAULT_MY_CART_PAGE_URL,
  DEFAULT_RESERVATION_PAGE_URL,
  ensureReservationUserLoggedIn,
  type ReservationProductContext,
  createReservationPayload,
  isReservationUserLoggedIn,
  navigateInternal,
  navigateToLoginForReservation,
  saveCartReservation,
  savePendingReservation,
} from "./reservationStore";

const RESERVATION_MODULE_STYLE = `
  .pd-book-drawer {
  position: relative;
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

  .pd-reservation-login-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1200;
    display: block;
    padding: 24px;
    background: rgba(21, 21, 21, 0.22);
    backdrop-filter: blur(14px);
  }

  .pd-reservation-login-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: min(420px, 100%);
    border: 1px solid rgba(21, 21, 21, 0.16);
    background: #ffffff;
    padding: 24px;
    box-sizing: border-box;
    color: #151515;
  }

  .pd-reservation-login-modal span {
    display: block;
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.18em;
    font-weight: 900;
    color: rgba(21, 21, 21, 0.46);
  }

  .pd-reservation-login-modal h2 {
    margin: 16px 0 10px;
    font-family: var(--font-ko);
    font-size: 25px;
    line-height: 1.14;
    letter-spacing: -0.045em;
    font-weight: 860;
  }

  .pd-reservation-login-modal p {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 13px;
    line-height: 1.58;
    letter-spacing: -0.025em;
    color: rgba(21, 21, 21, 0.62);
    word-break: keep-all;
  }

  .pd-reservation-login-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 24px;
  }

  .pd-reservation-login-actions button {
    appearance: none;
    min-height: 48px;
    border: 1px solid rgba(21, 21, 21, 0.18);
    background: #ffffff;
    color: #151515;
    cursor: pointer;
    font-family: var(--font-ko);
    font-size: 13px;
    letter-spacing: -0.025em;
    font-weight: 760;
  }

  .pd-reservation-login-actions button.is-primary {
    border-color: #151515;
    background: #151515;
    color: #ffffff;
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
  const [isLoginModalOpen, setIsLoginModalOpen] = useState(false);
  const [portalTarget, setPortalTarget] = useState<HTMLElement | null>(null);

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
const isReservationDisabled = !activeDate || status === "soldout";

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

  useEffect(() => {
    if (typeof document === "undefined") return;
    setPortalTarget(document.body);
  }, []);

  const getReservationPayload = () =>
    createReservationPayload({
      product,
      selectedDate: activeDate,
      personCount: safePeople,
      unitPrice,
      totalPrice,
    });

  const handleCart = () => {
    if (isReservationDisabled) return;

    saveCartReservation(getReservationPayload());
    navigateInternal(DEFAULT_MY_CART_PAGE_URL);
  };

  const handleReserve = async () => {
    if (isReservationDisabled) return;

    if (!isReservationUserLoggedIn() && !(await ensureReservationUserLoggedIn())) {
      setIsLoginModalOpen(true);
      return;
    }

    savePendingReservation(getReservationPayload());
    navigateInternal(DEFAULT_RESERVATION_PAGE_URL);
  };

  const handleLoginMove = () => {
    try {
      savePendingReservation(getReservationPayload());
    } finally {
      navigateToLoginForReservation(DEFAULT_RESERVATION_PAGE_URL);
    }
  };

  return (
    <>
      <ReservationModuleStyles />
      {portalTarget && isLoginModalOpen
        ? createPortal(
            <div
              className="pd-reservation-login-backdrop"
              role="dialog"
              aria-modal="true"
              aria-label="로그인 안내"
            >
              <div className="pd-reservation-login-modal">
                <span>LOGIN REQUIRED</span>
                <h2>예약을 위해 로그인이 필요합니다</h2>
                <p>
                  예약 정보 저장과 마이페이지 확인을 위해 로그인 후 예약을 진행해
                  주세요.
                </p>
                <div className="pd-reservation-login-actions">
                  <button type="button" onClick={() => setIsLoginModalOpen(false)}>
                    계속 보기
                  </button>
                  <button type="button" className="is-primary" onClick={handleLoginMove}>
                    로그인 화면으로 이동
                  </button>
                </div>
              </div>
            </div>,
            portalTarget,
          )
        : null}
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
    <BookingBoardingPass
      outbound={product.ticket?.outbound}
      inbound={product.ticket?.inbound}
    />
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

            <p>{formatAvailablePeople(remainingSeats)}</p>

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
