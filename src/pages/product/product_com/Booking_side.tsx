import { useMemo, useState } from "react";

import { PriceText, type AvailableDate } from "./reservationUtils";

type ProductKind = "semi" | "daily";

type BookingSideProduct = {
  id: string;
  title: string;
  productType: ProductKind;
  basePrice: number;
  currency?: string;
  duration?: string;
  routeCode?: string;
};

type BookingSideProps = {
  product: BookingSideProduct;
  availableDates: AvailableDate[];
  kakaoChannelUrl?: string;
  cartHref?: string;
  reservationHref?: string;
};

const DEFAULT_KAKAO_CHANNEL_URL = "https://pf.kakao.com/_YOUR_CHANNEL_ID/chat";
const DEFAULT_CART_HREF = "/mypage/cart";
const DEFAULT_RESERVATION_HREF = "/mypage/reservation";
const STYLE_ID = "uno-booking-side-style";

function formatCompactDateLabel(value: string) {
  const match = value.match(/^(\d{4})[.-](\d{2})[.-](\d{2})$/);
  if (!match) return value;
  return `${Number(match[2])}.${Number(match[3])}`;
}


function normalizeDateKey(value: string) {
  return value.replaceAll(".", "-");
}

function getAvailableLabel(date: AvailableDate) {
  if (date.seats <= 0 || date.status.includes("마감")) return "마감";
  return date.status;
}

function isSoldOut(date: AvailableDate) {
  return date.seats <= 0 || date.status === "마감";
}

function getBookingSideStyle() {
  return `
  .uno-booking-side {
    position: sticky;
    top: 116px;
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
    border-top: 1px solid rgba(21, 21, 21, 0.18);
    border-bottom: 1px solid rgba(21, 21, 21, 0.18);
    background: #ffffff;
    padding: 22px 0 26px;
    color: #151515;
  }

  .uno-booking-side__kicker {
    margin: 0 0 18px;
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.22em;
    color: rgba(21, 21, 21, 0.42);
    text-transform: uppercase;
  }

  .uno-booking-side__block {
    padding: 20px 0;
    border-top: 1px solid rgba(21, 21, 21, 0.1);
  }

  .uno-booking-side__block:first-of-type {
    border-top: 0;
    padding-top: 0;
  }

  .uno-booking-side__label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 14px;
  }

  .uno-booking-side__label {
    font-family: var(--font-ko);
    font-size: 13px;
    line-height: 1;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.68);
    font-weight: 700;
  }

  .uno-booking-side__hint {
    font-family: var(--font-ko);
    font-size: 11px;
    line-height: 1;
    letter-spacing: -0.025em;
    color: rgba(21, 21, 21, 0.42);
  }

  .uno-booking-side__select {
    width: 100%;
    height: 46px;
    border: 1px solid rgba(21, 21, 21, 0.16);
    border-radius: 0;
    background: #ffffff;
    padding: 0 14px;
    box-sizing: border-box;
    font-family: var(--font-ko);
    font-size: 13px;
    line-height: 1;
    letter-spacing: -0.035em;
    color: #151515;
    outline: none;
  }

  .uno-booking-side__calendar-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 12px;
  }

  .uno-booking-side__calendar-title {
    font-family: var(--font-ko);
    font-size: 13px;
    line-height: 1;
    letter-spacing: -0.035em;
    font-weight: 700;
    color: rgba(21, 21, 21, 0.72);
  }

  .uno-booking-side__calendar-month {
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.14em;
    color: rgba(21, 21, 21, 0.42);
  }

  .uno-booking-side__legend {
    display: none;
  }

  .uno-booking-side__calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 4px;
    padding: 10px;
    border: 1px solid rgba(21, 21, 21, 0.12);
    background: linear-gradient(180deg, rgba(255,255,255,1) 0%, rgba(248,248,245,0.72) 100%);
  }

  .uno-booking-side__weekday {
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.04em;
    color: rgba(21, 21, 21, 0.38);
    text-transform: uppercase;
  }

  .uno-booking-side__day {
    position: relative;
    appearance: none;
    border: 1px solid transparent;
    border-radius: 999px;
    background: transparent;
    min-height: 38px;
    padding: 0;
    box-sizing: border-box;
    cursor: pointer;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #151515;
    transition: border-color 0.2s ease, background 0.2s ease, opacity 0.2s ease, transform 0.2s ease;
  }

  .uno-booking-side__day:hover:not(:disabled) {
    border-color: rgba(21, 21, 21, 0.22);
    background: rgba(255, 255, 255, 0.92);
  }

  .uno-booking-side__day.is-selected {
    border-color: #151515;
    background: #151515;
    color: #ffffff;
    box-shadow: 0 10px 24px rgba(21, 21, 21, 0.16);
  }

  .uno-booking-side__day.is-available:not(.is-selected)::after {
    content: "";
    position: absolute;
    bottom: 5px;
    width: 4px;
    height: 4px;
    border-radius: 999px;
    background: #fcc800;
  }

  .uno-booking-side__day:disabled {
    cursor: not-allowed;
    opacity: 0.24;
    background: transparent;
  }

  .uno-booking-side__day-num {
    font-family: var(--font-en);
    font-size: 12px;
    line-height: 1;
    font-weight: 650;
  }

  .uno-booking-side__day-status {
    display: none;
  }

  .uno-booking-side__selected-date {
    margin: 10px 0 0;
    padding: 11px 12px;
    border: 1px solid rgba(21, 21, 21, 0.1);
    background: rgba(255, 255, 255, 0.72);
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1.45;
    letter-spacing: -0.03em;
    color: rgba(21, 21, 21, 0.62);
    word-break: keep-all;
  }

  .uno-booking-side__empty-day {
    min-height: 38px;
  }

  .uno-booking-side__people {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 46px;
    border: 1px solid rgba(21, 21, 21, 0.14);
    padding: 0 8px 0 14px;
    box-sizing: border-box;
  }

  .uno-booking-side__people-label {
    font-family: var(--font-ko);
    font-size: 13px;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.72);
  }

  .uno-booking-side__counter {
    display: inline-flex;
    align-items: center;
    gap: 12px;
  }

  .uno-booking-side__counter-button {
    appearance: none;
    width: 30px;
    height: 30px;
    border: 1px solid rgba(21, 21, 21, 0.14);
    border-radius: 999px;
    background: #ffffff;
    cursor: pointer;
    font-family: var(--font-en);
    font-size: 15px;
    line-height: 1;
    color: #151515;
  }

  .uno-booking-side__counter-button:disabled {
    cursor: not-allowed;
    opacity: 0.35;
  }

  .uno-booking-side__count {
    min-width: 18px;
    text-align: center;
    font-family: var(--font-en);
    font-size: 15px;
    line-height: 1;
    font-weight: 700;
  }

  .uno-booking-side__price-row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 16px;
  }

  .uno-booking-side__price-label {
    font-family: var(--font-ko);
    font-size: 13px;
    line-height: 1;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.56);
  }

  .uno-booking-side__price {
    font-family: var(--font-en);
    font-size: 26px;
    line-height: 1;
    letter-spacing: -0.035em;
    font-weight: 720;
    color: #151515;
    text-align: right;
  }

  .uno-booking-side__price-note {
    margin-top: 9px;
    font-family: var(--font-ko);
    font-size: 11px;
    line-height: 1.5;
    letter-spacing: -0.03em;
    color: rgba(21, 21, 21, 0.42);
    word-break: keep-all;
  }

  .uno-booking-side__actions {
    display: grid;
    gap: 9px;
    padding-top: 20px;
    border-top: 1px solid rgba(21, 21, 21, 0.1);
  }

  .uno-booking-side__button {
    appearance: none;
    width: 100%;
    min-height: 48px;
    border: 1px solid #151515;
    background: #ffffff;
    color: #151515;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-ko);
    font-size: 14px;
    line-height: 1;
    letter-spacing: -0.035em;
    font-weight: 800;
    text-decoration: none;
    box-sizing: border-box;
    transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
  }

  .uno-booking-side__button:hover {
    background: #151515;
    color: #ffffff;
  }

  .uno-booking-side__button.is-added {
    border-color: rgba(21, 21, 21, 0.18);
    background: rgba(21, 21, 21, 0.04);
    color: #151515;
  }

  .uno-booking-side__button.is-added:hover {
    background: #151515;
    color: #ffffff;
  }

  .uno-booking-side__button.is-primary {
    background: #151515;
    color: #ffffff;
  }

  .uno-booking-side__button.is-primary:hover {
    background: #fcc800;
    border-color: #fcc800;
    color: #151515;
  }

  .uno-booking-side__button.is-kakao {
    border-color: #fee500;
    background: #fee500;
    color: #151515;
  }

  .uno-booking-side__button.is-kakao:hover {
    background: #151515;
    border-color: #151515;
    color: #ffffff;
  }

  .uno-booking-side__notice {
    margin: 12px 0 0;
    font-family: var(--font-ko);
    font-size: 11px;
    line-height: 1.55;
    letter-spacing: -0.03em;
    color: rgba(21, 21, 21, 0.42);
    word-break: keep-all;
  }
`;
}

function BookingSideStyle() {
  return <style id={STYLE_ID}>{getBookingSideStyle()}</style>;
}

export default function BookingSide({
  product,
  availableDates,
  kakaoChannelUrl = DEFAULT_KAKAO_CHANNEL_URL,
  cartHref = DEFAULT_CART_HREF,
  reservationHref = DEFAULT_RESERVATION_HREF,
}: BookingSideProps) {
  const selectableDates = useMemo(
    () => availableDates.filter((date) => !isSoldOut(date)),
    [availableDates],
  );

  const [selectedDateId, setSelectedDateId] = useState(
    selectableDates[0]?.id ?? availableDates[0]?.id ?? "",
  );
  const [peopleCount, setPeopleCount] = useState(1);
  const [isCartAdded, setIsCartAdded] = useState(false);

  const isDailyTour = product.productType === "daily";
  const selectedDate =
    availableDates.find((date) => date.id === selectedDateId) ??
    selectableDates[0] ??
    availableDates[0];
  const selectedPrice = selectedDate?.price ?? product.basePrice ?? 0;
  const totalPrice = selectedPrice * peopleCount;
  const maxPeople = Math.max(1, selectedDate?.seats ?? 99);

  const calendarMeta = useMemo(() => {
    const firstDate = availableDates[0];
    const firstKey = firstDate ? normalizeDateKey(firstDate.id) : "";
    const first = firstKey ? new Date(`${firstKey}T00:00:00`) : new Date();
    const year = first.getFullYear();
    const month = first.getMonth();
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const monthLabel = `${year}.${String(month + 1).padStart(2, "0")}`;

    return { year, month, firstDay, daysInMonth, monthLabel };
  }, [availableDates]);

  const datesByDay = useMemo(() => {
    const map = new Map<number, AvailableDate>();
    availableDates.forEach((date) => {
      const key = normalizeDateKey(date.id);
      const parsed = new Date(`${key}T00:00:00`);
      if (!Number.isNaN(parsed.getTime())) {
        map.set(parsed.getDate(), date);
      }
    });
    return map;
  }, [availableDates]);

  const persistBookingPayload = () => {
    if (typeof window === "undefined") return;

    const payload = {
      id: `${product.id}-${selectedDate?.id ?? "date"}`,
      productId: product.id,
      title: product.title,
      productType: product.productType,
      dateId: selectedDate?.id ?? "",
      dateLabel: selectedDate?.label ?? "",
      peopleCount,
      unitPrice: selectedPrice,
      totalPrice,
      currency: product.currency ?? "KRW",
      createdAt: new Date().toISOString(),
    };

    try {
      const key = "unotravel:booking-side:latest";
      window.localStorage.setItem(key, JSON.stringify(payload));
    } catch {
      // localStorage가 막힌 환경에서도 이동 동작은 유지한다.
    }
  };

  const handleCart = () => {
    if (isCartAdded) {
      if (typeof window !== "undefined") {
        window.location.href = cartHref;
      }
      return;
    }

    persistBookingPayload();
    setIsCartAdded(true);
  };

  const handleReservation = () => {
    persistBookingPayload();
    if (typeof window !== "undefined") {
      window.location.href = reservationHref;
    }
  };

  return (
    <aside className="uno-booking-side" aria-label="간소 예약 패널">
      <BookingSideStyle />

      <p className="uno-booking-side__kicker">BOOKING ASIDE</p>

      <section className="uno-booking-side__block">
        {isDailyTour ? (
          <>
            <div className="uno-booking-side__calendar-head">
              <strong className="uno-booking-side__calendar-title">예약 날짜</strong>
              <span className="uno-booking-side__calendar-month">
                {calendarMeta.monthLabel}
              </span>
            </div>

            <div className="uno-booking-side__calendar-grid">
              {["일", "월", "화", "수", "목", "금", "토"].map((day) => (
                <div key={day} className="uno-booking-side__weekday">
                  {day}
                </div>
              ))}

              {Array.from({ length: calendarMeta.firstDay }).map((_, index) => (
                <div
                  key={`empty-${index}`}
                  className="uno-booking-side__empty-day"
                />
              ))}

              {Array.from({ length: calendarMeta.daysInMonth }).map((_, index) => {
                const dayNumber = index + 1;
                const date = datesByDay.get(dayNumber);
                const disabled = !date || isSoldOut(date);
                const isSelected = !!date && date.id === selectedDate?.id;

                return (
                  <button
                    key={dayNumber}
                    type="button"
                    className={`uno-booking-side__day${date && !disabled ? " is-available" : ""}${isSelected ? " is-selected" : ""}`}
                    disabled={disabled}
                    onClick={() => {
                      if (date) {
                        setSelectedDateId(date.id);
                        setPeopleCount(1);
                        setIsCartAdded(false);
                      }
                    }}
                    aria-label={date ? `${date.label} ${date.status}` : `${dayNumber}일 예약 불가`}
                  >
                    <span className="uno-booking-side__day-num">{dayNumber}</span>
                    <span className="uno-booking-side__day-status">
                      {date ? getAvailableLabel(date) : ""}
                    </span>
                  </button>
                );
              })}
            </div>

            {selectedDate ? (
              <p className="uno-booking-side__selected-date">
                선택 날짜: {selectedDate.label} ({selectedDate.day}) · {selectedDate.status} · 잔여 {selectedDate.seats}석
              </p>
            ) : null}
          </>
        ) : (
          <>
            <div className="uno-booking-side__label-row">
              <strong className="uno-booking-side__label">예약 날짜</strong>
              <span className="uno-booking-side__hint">출발일 기준</span>
            </div>

            <select
              className="uno-booking-side__select"
              value={selectedDateId}
              onChange={(event) => {
                setSelectedDateId(event.target.value);
                setPeopleCount(1);
                setIsCartAdded(false);
              }}
            >
              {availableDates.map((date) => (
                <option key={date.id} value={date.id} disabled={isSoldOut(date)}>
                  {formatCompactDateLabel(date.label)} ({date.day}) · {date.status} · 잔여 {date.seats}석
                </option>
              ))}
            </select>
          </>
        )}
      </section>

      <section className="uno-booking-side__block">
        <div className="uno-booking-side__label-row">
          <strong className="uno-booking-side__label">인원</strong>
          <span className="uno-booking-side__hint">최대 {maxPeople}명</span>
        </div>

        <div className="uno-booking-side__people">
          <span className="uno-booking-side__people-label">예약 인원</span>
          <span className="uno-booking-side__counter">
            <button
              type="button"
              className="uno-booking-side__counter-button"
              disabled={peopleCount <= 1}
              onClick={() => {
                setPeopleCount((count) => Math.max(1, count - 1));
                setIsCartAdded(false);
              }}
              aria-label="인원 줄이기"
            >
              −
            </button>
            <strong className="uno-booking-side__count">{peopleCount}</strong>
            <button
              type="button"
              className="uno-booking-side__counter-button"
              disabled={peopleCount >= maxPeople}
              onClick={() => {
                setPeopleCount((count) => Math.min(maxPeople, count + 1));
                setIsCartAdded(false);
              }}
              aria-label="인원 늘리기"
            >
              +
            </button>
          </span>
        </div>
      </section>

      <section className="uno-booking-side__block">
        <div className="uno-booking-side__price-row">
          <span className="uno-booking-side__price-label">가격</span>
          <strong className="uno-booking-side__price">
            <PriceText price={totalPrice} currency={product.currency ?? "KRW"} />
          </strong>
        </div>
        <p className="uno-booking-side__price-note">
{peopleCount}명 기준 금액입니다. 최종 확정 전 예약 조건과 가능 여부를 다시 확인합니다.
        </p>
      </section>

      <div className="uno-booking-side__actions">
        <button
          type="button"
          className={`uno-booking-side__button${isCartAdded ? " is-added" : ""}`}
          onClick={handleCart}
        >
          {isCartAdded ? "장바구니로 이동" : "장바구니 담기"}
        </button>
        <button
          type="button"
          className="uno-booking-side__button is-primary"
          onClick={handleReservation}
        >
          예약 진행하기
        </button>
        <a
          className="uno-booking-side__button is-kakao"
          href={kakaoChannelUrl}
          target="_blank"
          rel="noreferrer"
        >
          카카오톡 문의하기
        </a>
      </div>

      <p className="uno-booking-side__notice">
        세미패키지는 출발일 선택 방식이며, 데일리투어에만 미니 캘린더가 표시됩니다.
      </p>
    </aside>
  );
}
