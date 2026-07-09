// reservationUtils.ts
// 예약 관련 공통 타입과 유틸 함수를 관리한다.
// 가격 표기, 날짜 id 변환, 달력 날짜 생성, 예약 가능 여부, 예약 상태 className 계산을 담당한다.
// ProductDetail, resmodule, DailyTourCalendar에서 중복으로 만들지 않고 이 파일의 공통 함수를 사용한다.
// JSX를 쓰지 않는 .ts 파일이므로 PriceText는 React.createElement 기반으로 작성한다.

import { createElement } from "react";

export type AvailableDate = {
  id: string;
  label: string;
  day: string;
  seats: number;
  capacity: number;
  price: number;
  status: string;
  guide: string;
};

export type AvailabilityStatus = "예약 가능" | "마감 임박" | "마감";
export type AvailabilityTone = "available" | "soon" | "soldout";

export type PriceTextProps = {
  price: number;
  currency?: string;
  className?: string;
};

const CURRENCY_SYMBOL_MAP: Record<string, string> = {
  KRW: "₩",
  USD: "$",
  EUR: "€",
  JPY: "¥",
};

const CLOSED_STATUS_SET = new Set(["마감", "예약 마감"]);

export const getCurrencySymbol = (currency = "KRW") => {
  return CURRENCY_SYMBOL_MAP[currency] ?? currency;
};

export const formatPriceValue = (price: number) => {
  return price.toLocaleString("ko-KR");
};

/*
  Price Text
  ------------------------------------------
  ProductDetail / ReservationModule에서 공통으로 사용하는 가격 표시 컴포넌트다.
  reservationUtils.ts는 .ts 파일이므로 JSX 대신 createElement를 사용한다.
*/
export function PriceText({
  price,
  currency = "KRW",
  className = "",
}: PriceTextProps) {
  return createElement(
    "span",
    { className: `pd-price-text ${className}`.trim() },
    createElement(
      "span",
      { className: "pd-price-symbol" },
      getCurrencySymbol(currency),
    ),
    createElement(
      "span",
      { className: "pd-price-number" },
      formatPriceValue(price),
    ),
  );
}

export const getTodayStart = () => {
  const date = new Date();
  date.setHours(0, 0, 0, 0);
  return date;
};

export const parseDateId = (dateId: string) => {
  const [year, month, day] = dateId.split("-").map(Number);
  return new Date(year, month - 1, day);
};

export const getMonthLabel = (year: number, monthIndex: number) => {
  return `${year}년 ${String(monthIndex + 1).padStart(2, "0")}월`;
};

export const getWeekdayKo = (date: Date) => {
  return ["일", "월", "화", "수", "목", "금", "토"][date.getDay()];
};

export const getDateIdFromDate = (date: Date) => {
  return [
    date.getFullYear(),
    String(date.getMonth() + 1).padStart(2, "0"),
    String(date.getDate()).padStart(2, "0"),
  ].join("-");
};

export const isSundayDate = (date: Date) => date.getDay() === 0;

export const isDateClosedStatus = (status?: string) => {
  return CLOSED_STATUS_SET.has(status ?? "");
};

export const createDailyCalendarDate = (
  date: Date,
  referenceDate?: AvailableDate,
): AvailableDate => {
  const dateId = getDateIdFromDate(date);
  const isSunday = isSundayDate(date);
  const fallbackCapacity = referenceDate?.capacity ?? 12;

  return {
    id: dateId,
    label: dateId.replaceAll("-", "."),
    day: getWeekdayKo(date),
    seats: isSunday ? 0 : fallbackCapacity,
    capacity: fallbackCapacity,
    price: referenceDate?.price ?? 89000,
    status: isSunday ? "마감" : "예약 가능",
    guide: referenceDate?.guide ?? "UNO GUIDE",
  };
};

export const isDailyStoredDateClosed = (date?: AvailableDate) => {
  if (!date) return false;
  return date.seats <= 0 || isDateClosedStatus(date.status);
};

export const getDailyDateOption = (date: Date, dates: AvailableDate[]) => {
  const dateId = getDateIdFromDate(date);
  const storedDate = dates.find((item) => item.id === dateId);
  const fallbackDate = createDailyCalendarDate(date, dates[0]);

  if (isSundayDate(date)) {
    return {
      ...(storedDate ?? fallbackDate),
      id: fallbackDate.id,
      label: fallbackDate.label,
      day: fallbackDate.day,
      seats: 0,
      capacity: storedDate?.capacity ?? fallbackDate.capacity,
      price: storedDate?.price ?? fallbackDate.price,
      status: "마감",
    };
  }

  if (isDailyStoredDateClosed(storedDate)) {
    return {
      ...fallbackDate,
      ...storedDate,
      status: "마감",
    };
  }

  return {
    ...fallbackDate,
    ...storedDate,
    seats: storedDate?.seats ?? fallbackDate.seats,
    status: storedDate?.status === "마감 임박" ? "마감 임박" : "예약 가능",
  };
};

export const isDateSoldOut = (date?: AvailableDate) => {
  if (!date) return false;
  return date.seats <= 0 || isDateClosedStatus(date.status);
};

export const isDateBookable = (date: AvailableDate | undefined, today: Date) => {
  if (!date) return false;
  return parseDateId(date.id) >= today && !isDateSoldOut(date);
};

export const getInitialDailyDateId = (dates: AvailableDate[]) => {
  const today = getTodayStart();

  for (let offset = 0; offset < 370; offset += 1) {
    const candidateDate = new Date(today);
    candidateDate.setDate(today.getDate() + offset);

    const candidateOption = getDailyDateOption(candidateDate, dates);

    if (isDateBookable(candidateOption, today)) {
      return candidateOption.id;
    }
  }

  return dates[0]?.id ?? "";
};

export const getAvailabilityStatus = (
  date?: AvailableDate,
): AvailabilityStatus => {
  if (!date || date.seats <= 0 || isDateClosedStatus(date.status)) {
    return "마감";
  }

  const remainingRatio = date.capacity > 0 ? date.seats / date.capacity : 0;
  if (remainingRatio <= 0.3) return "마감 임박";

  return "예약 가능";
};

export const getAvailabilityTone = (
  status: AvailabilityStatus,
): AvailabilityTone => {
  if (status === "마감 임박") return "soon";
  if (status === "마감") return "soldout";
  return "available";
};

export const getAvailabilityClassName = (date?: AvailableDate) => {
  return `is-${getAvailabilityTone(getAvailabilityStatus(date))}`;
};

export const formatAvailablePeople = (count?: number) => {
  const safeCount = Math.max(0, count ?? 0);
  return safeCount <= 0 ? "예약 종료" : `${safeCount}명 예약 가능`;
};
