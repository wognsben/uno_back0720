// DailyTourCalendar.tsx
// 데일리투어 예약 UI 내부의 날짜 선택 달력 표시 전용 컴포넌트.
// 예약 가능 날짜, 마감 임박, 마감, 지난 날짜 상태를 달력 셀로 렌더링한다.
// 날짜 계산과 예약 가능 여부 판정은 reservationUtils.ts의 공통 함수를 사용한다.
// 선택된 날짜 id는 상위 예약 모듈(resmodule.tsx)로 전달한다.

import { useState } from "react";
import {
  type AvailableDate,
  PriceText,
  formatAvailablePeople,
  formatPriceValue,
  getAvailabilityClassName,
  getAvailabilityStatus,
  getDailyDateOption,
  getMonthLabel,
  getTodayStart,
  isDateBookable,
  getDateIdFromDate,
} from "./reservationUtils";

const DAILY_TOUR_CALENDAR_STYLE = `
  .pd-calendar-card {
    width: 100%;
    max-width: 100%;
    background: #ffffff;
    border: 0;
    border-radius: 0;
    box-shadow: none;
    overflow: hidden;
    box-sizing: border-box;
  }

  .pd-calendar-head {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 20px;
    align-items: end;
    padding: 0 0 20px;
    border-bottom: 1px solid rgba(21, 21, 21, 0.12);
    background: #ffffff;
  }

  .pd-calendar-kicker {
    margin-bottom: 14px;
    font-family: var(--font-en);
    font-size: 12px;
    line-height: 1;
    letter-spacing: 0.18em;
    font-weight: 620;
    color: rgba(21, 21, 21, 0.58);
    text-transform: uppercase;
  }

  .pd-calendar-title {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 26px;
    line-height: 1.16;
    letter-spacing: -0.055em;
    font-weight: 560;
    color: #151515;
    word-break: keep-all;
  }

  .pd-calendar-controls {
    display: inline-flex;
    align-items: center;
    gap: 16px;
    font-family: var(--font-ko);
    font-size: 17px;
    letter-spacing: -0.035em;
    color: #151515;
    white-space: nowrap;
  }

  .pd-calendar-controls button {
    appearance: none;
    width: 38px;
    height: 38px;
    border: 1px solid rgba(21, 21, 21, 0.14);
    border-radius: 999px;
    background: #ffffff;
    color: #151515;
    cursor: pointer;
    font-family: var(--font-en);
    font-size: 18px;
    line-height: 1;
    transition: border-color 0.22s ease, background 0.22s ease;
  }

  .pd-calendar-controls button:hover {
    border-color: #151515;
    background: #f7f7f7;
  }

  .pd-calendar-legend {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 16px;
    padding: 14px 0 0;
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1;
    letter-spacing: -0.025em;
    font-weight: 560;
    color: rgba(21, 21, 21, 0.68);
  }

  .pd-calendar-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    white-space: nowrap;
  }

  .pd-calendar-legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    box-sizing: border-box;
  }

  .pd-calendar-legend-dot.is-available {
    background: #ffffff;
    border: 1px solid rgba(21, 21, 21, 0.28);
  }

  .pd-calendar-legend-dot.is-soon {
    background: #f08a24;
    border: 1px solid #f08a24;
  }

  .pd-calendar-legend-dot.is-soldout {
    background: #2b2b2b;
    border: 1px solid #2b2b2b;
  }

  .pd-calendar-weekdays,
  .pd-calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
  }

  .pd-calendar-weekdays {
    padding: 18px 0 0;
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1;
    letter-spacing: -0.02em;
    font-weight: 600;
    color: rgba(21, 21, 21, 0.54);
    text-align: center;
  }

  .pd-calendar-grid {
    gap: 5px;
    padding: 16px 0 0;
  }

  .pd-calendar-empty,
  .pd-calendar-day {
    min-width: 0;
    min-height: 70px;
  }

  .pd-calendar-day {
    appearance: none;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 7px;
    padding: 9px 7px;
    border: 1px solid rgba(21, 21, 21, 0.12);
    border-radius: 12px;
    background: #ffffff;
    color: #151515;
    text-align: left;
    overflow: hidden;
    cursor: pointer;
    box-sizing: border-box;
    transition: border-color 0.2s ease, background 0.2s ease, color 0.2s ease;
  }

  .pd-calendar-day-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 4px;
  }

  .pd-calendar-day-number {
    font-family: var(--font-en);
    font-size: 18px;
    line-height: 1;
    letter-spacing: -0.045em;
    font-weight: 520;
  }

  .pd-calendar-day-status {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: rgba(21, 21, 21, 0.16);
    flex-shrink: 0;
  }

  .pd-calendar-day-price,
  .pd-calendar-day-label {
    display: block;
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1.08;
    letter-spacing: -0.045em;
    font-weight: 800;
    color: #151515;
  }

  .pd-calendar-day-price .pd-price-text,
  .pd-calendar-day-price .pd-price-symbol,
  .pd-calendar-day-price .pd-price-number {
    font-size: inherit;
    line-height: inherit;
    letter-spacing: inherit;
    font-weight: inherit;
    color: inherit;
  }

  .pd-calendar-day.is-soldout .pd-calendar-day-price,
  .pd-calendar-day.is-soldout .pd-calendar-day-label {
    color: rgba(255, 255, 255, 0.86);
  }

  .pd-calendar-day.is-available {
    background: #ffffff;
    border-color: rgba(21, 21, 21, 0.14);
  }

  .pd-calendar-day.is-available .pd-calendar-day-status {
    background: #ffffff;
    border: 1px solid rgba(21, 21, 21, 0.24);
  }

  .pd-calendar-day.is-soon {
    background: #f08a24;
    border-color: #f08a24;
    color: #151515;
  }

  .pd-calendar-day.is-soon .pd-calendar-day-status {
    background: #151515;
  }

  .pd-calendar-day.is-soldout,
  .pd-calendar-day.is-soldout.is-disabled,
  .pd-calendar-day.is-past,
  .pd-calendar-day.is-past.is-disabled {
    background: #2b2b2b;
    border-color: #2b2b2b;
    color: #ffffff;
    opacity: 1;
  }

  .pd-calendar-day.is-soldout .pd-calendar-day-status,
  .pd-calendar-day.is-past .pd-calendar-day-status {
    background: rgba(255, 255, 255, 0.72);
  }

  .pd-calendar-day:not(.is-disabled):hover {
    border-color: #151515;
  }

  .pd-calendar-day.is-selected {
    border-color: #151515;
    box-shadow: 0 0 0 2px #151515;
    z-index: 2;
  }

  .pd-calendar-day.is-selected::after {
    content: none;
  }

  .pd-calendar-note {
    margin: 18px 0 0;
    font-family: var(--font-ko);
    font-size: 13px;
    line-height: 1.64;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.48);
    word-break: keep-all;
  }

  .pd-book-calendar-panel .pd-calendar-card,
  .pd-reservation-drawer-calendar .pd-calendar-card,
  .pd-body-booking-calendar .pd-calendar-card {
    width: 100%;
    max-width: 100%;
    overflow: hidden;
  }

  .pd-book-calendar-panel .pd-calendar-head,
  .pd-reservation-drawer-calendar .pd-calendar-head,
  .pd-body-booking-calendar .pd-calendar-head {
    grid-template-columns: 1fr;
    gap: 14px;
    align-items: start;
    padding-bottom: 16px;
  }

  .pd-book-calendar-panel .pd-calendar-title,
  .pd-reservation-drawer-calendar .pd-calendar-title,
  .pd-body-booking-calendar .pd-calendar-title {
    font-size: 21px;
    line-height: 1.16;
  }

  .pd-book-calendar-panel .pd-calendar-controls,
  .pd-reservation-drawer-calendar .pd-calendar-controls,
  .pd-body-booking-calendar .pd-calendar-controls {
    justify-content: space-between;
    width: 100%;
  }

  .pd-book-calendar-panel .pd-calendar-grid,
  .pd-reservation-drawer-calendar .pd-calendar-grid,
  .pd-body-booking-calendar .pd-calendar-grid {
    gap: 4px;
    padding-top: 14px;
  }

  .pd-book-calendar-panel .pd-calendar-legend,
  .pd-reservation-drawer-calendar .pd-calendar-legend,
  .pd-body-booking-calendar .pd-calendar-legend {
    justify-content: flex-start;
    gap: 10px;
    padding-top: 10px;
    font-size: 11px;
    font-weight: 560;
  }

  .pd-book-calendar-panel .pd-calendar-legend-dot,
  .pd-reservation-drawer-calendar .pd-calendar-legend-dot,
  .pd-body-booking-calendar .pd-calendar-legend-dot {
    width: 8px;
    height: 8px;
  }

  .pd-book-calendar-panel .pd-calendar-weekdays,
  .pd-reservation-drawer-calendar .pd-calendar-weekdays,
  .pd-body-booking-calendar .pd-calendar-weekdays {
    padding-top: 12px;
    font-size: 11px;
  }

  .pd-book-calendar-panel .pd-calendar-empty,
  .pd-book-calendar-panel .pd-calendar-day,
  .pd-reservation-drawer-calendar .pd-calendar-empty,
  .pd-reservation-drawer-calendar .pd-calendar-day,
  .pd-body-booking-calendar .pd-calendar-empty,
  .pd-body-booking-calendar .pd-calendar-day {
    min-height: 58px;
  }

  .pd-book-calendar-panel .pd-calendar-day,
  .pd-reservation-drawer-calendar .pd-calendar-day,
  .pd-body-booking-calendar .pd-calendar-day {
    gap: 5px;
    padding: 7px 5px;
    border-radius: 10px;
  }

  .pd-book-calendar-panel .pd-calendar-day-number,
  .pd-reservation-drawer-calendar .pd-calendar-day-number,
  .pd-body-booking-calendar .pd-calendar-day-number {
    font-size: 16px;
  }

  .pd-book-calendar-panel .pd-calendar-day-price,
  .pd-book-calendar-panel .pd-calendar-day-label,
  .pd-reservation-drawer-calendar .pd-calendar-day-price,
  .pd-reservation-drawer-calendar .pd-calendar-day-label,
  .pd-body-booking-calendar .pd-calendar-day-price,
  .pd-body-booking-calendar .pd-calendar-day-label {
    font-size: 11px;
    font-weight: 800;
  }

  .pd-book-calendar-panel .pd-calendar-note,
  .pd-reservation-drawer-calendar .pd-calendar-note,
  .pd-body-booking-calendar .pd-calendar-note {
    display: none;
  }
`;

export function DailyTourCalendarStyles() {
  return <style>{DAILY_TOUR_CALENDAR_STYLE}</style>;
}

function DailyTourCalendar({
  dates,
  selectedDateId,
  onSelectDate,
}: {
  dates: AvailableDate[];
  selectedDateId: string;
  onSelectDate: (dateId: string) => void;
}) {
  const today = getTodayStart();
  const [visibleMonth, setVisibleMonth] = useState(() => {
    return new Date(today.getFullYear(), today.getMonth(), 1);
  });

  const year = visibleMonth.getFullYear();
  const monthIndex = visibleMonth.getMonth();
  const firstDay = new Date(year, monthIndex, 1);
  const lastDay = new Date(year, monthIndex + 1, 0);
  const calendarCells = Array.from({
    length: firstDay.getDay() + lastDay.getDate(),
  }).map((_, index) => {
    const dayNumber = index - firstDay.getDay() + 1;

    if (dayNumber <= 0) return null;

    const cellDate = new Date(year, monthIndex, dayNumber);
    const dateId = getDateIdFromDate(cellDate);
    const option = getDailyDateOption(cellDate, dates);
    const isPast = cellDate < today;
    const isBookable = isDateBookable(option, today);

    return {
      dateId,
      dayNumber,
      isPast,
      isToday: cellDate.getTime() === today.getTime(),
      isBookable,
      option,
    };
  });

  return (
    <>
      <DailyTourCalendarStyles />
      <section
        className="pd-calendar-card"
        aria-label="데일리투어 예약 가능 달력"
      >
        <div className="pd-calendar-head">
          <div>
            <div className="pd-calendar-kicker">데일리투어 예약 달력</div>

          </div>

          <div className="pd-calendar-controls">
            <button
              type="button"
              onClick={() =>
                setVisibleMonth(
                  (date) => new Date(date.getFullYear(), date.getMonth() - 1, 1),
                )
              }
              aria-label="이전 달"
            >
              ←
            </button>
            <strong>{getMonthLabel(year, monthIndex)}</strong>
            <button
              type="button"
              onClick={() =>
                setVisibleMonth(
                  (date) => new Date(date.getFullYear(), date.getMonth() + 1, 1),
                )
              }
              aria-label="다음 달"
            >
              →
            </button>
          </div>
        </div>

        <div className="pd-calendar-legend" aria-label="예약 상태 안내">
          <span className="pd-calendar-legend-item">
            <span className="pd-calendar-legend-dot is-available" aria-hidden="true" />
            예약 가능
          </span>
          <span className="pd-calendar-legend-item">
            <span className="pd-calendar-legend-dot is-soon" aria-hidden="true" />
            마감임박
          </span>
          <span className="pd-calendar-legend-item">
            <span className="pd-calendar-legend-dot is-soldout" aria-hidden="true" />
            마감
          </span>
        </div>

        <div className="pd-calendar-weekdays" aria-hidden="true">
          {["일", "월", "화", "수", "목", "금", "토"].map((day) => (
            <span key={day}>{day}</span>
          ))}
        </div>

        <div className="pd-calendar-grid">
          {calendarCells.map((cell, index) => {
            if (!cell) {
              return <span key={`empty-${index}`} className="pd-calendar-empty" />;
            }

            const disabled = !cell.isBookable;
            const isSelected = selectedDateId === cell.dateId;
            const status = getAvailabilityStatus(cell.option);
            const statusClassName = getAvailabilityClassName(cell.option);
            const isClosed = status === "마감";
            const statusLabel = cell.isPast ? "지난 날짜" : status;
            const priceLabel = formatPriceValue(cell.option.price);
            const peopleLabel = formatAvailablePeople(cell.option.seats);

            return (
              <button
                key={cell.dateId}
                type="button"
                className={`pd-calendar-day ${cell.isToday ? "is-today" : ""} ${cell.isPast ? "is-past" : ""} ${isSelected ? "is-selected" : ""} ${statusClassName} ${disabled ? "is-disabled" : ""}`}
                disabled={disabled}
                onClick={() => {
                  if (!disabled) onSelectDate(cell.dateId);
                }}
                aria-pressed={isSelected}
                aria-label={`${cell.dateId} ${statusLabel} ${priceLabel}원 ${peopleLabel}`}
              >
                <span className="pd-calendar-day-top">
                  <span className="pd-calendar-day-number">{cell.dayNumber}</span>
                  <span className="pd-calendar-day-status" aria-hidden="true" />
                </span>

                {!cell.isPast && !isClosed ? (
                  <span className="pd-calendar-day-price">
                    <PriceText price={cell.option.price} />
                  </span>
                ) : (
                  <span className="pd-calendar-day-label">
                    {cell.isPast ? "지난" : "마감"}
                  </span>
                )}
              </button>
            );
          })}
        </div>

        <p className="pd-calendar-note">
          흰색은 예약 가능, 주황은 마감임박, 차콜은 마감 상태입니다.
        </p>
      </section>
    </>
  );
}

export default DailyTourCalendar;
