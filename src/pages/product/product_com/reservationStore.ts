// reservationStore.ts
// 상품 상세 예약 UI가 공통으로 사용하는 예약 payload 생성과 저장소 접근을 담당한다.
// 장바구니 저장, 예약 진행 저장, sessionStorage key, 내부 SPA 이동 상태를 한곳에서 관리한다.
// ReservationModule과 Booking_side가 각자 저장 로직을 갖지 않도록 중복과 상태 충돌을 막는 파일이다.

import type { AvailableDate } from "./reservationUtils";
import type { BoardingPassFlightSegment } from "./BoardingPass";
import {
  isLocalAuthSessionActive,
  refreshAuthSession,
} from "../../../api/authSession";

export const CART_STORAGE_KEY = "unotravel_cart_items";
export const CART_COUNT_STORAGE_KEY = "unotravel_cart_count";
export const PENDING_RESERVATION_STORAGE_KEY = "unotravel_pending_reservation";
export const RESERVATION_LIST_STORAGE_KEY = "unotravel_reservation_items";
export const DEFAULT_RESERVATION_PAGE_URL = "/reservation";
export const DEFAULT_MY_CART_PAGE_URL = "/mypage/cart";
export const LOGIN_REDIRECT_STORAGE_KEY = "unotravel:redirect-after-login";

export type ReservationProductKind = "semi" | "daily";

export type ReservationProductContext = {
  id: string;
  legacyProductId?: number | string;
  legacyFeeOptionId?: number | string;
  legacyPackageScheduleId?: number | string;
  productType: ReservationProductKind;
  title: string;
  href: string;
  currency?: string;
  basePrice?: number;
  ticket?: {
    outbound?: BoardingPassFlightSegment;
    inbound?: BoardingPassFlightSegment;
  };
};

export type ReservationStoragePayload = {
  productId: string;
  legacyProductId?: number | string;
  legacyFeeOptionId?: number | string;
  legacyPackageScheduleId?: number | string;
  productType: ReservationProductKind;
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

export type SubmittedReservation = ReservationStoragePayload & {
  reservationId: string;
  status: "예약 확인";
  submittedAt: number;
  customer?: {
    name?: string;
    phone?: string;
    email?: string;
    kakaoId?: string;
    memo?: string;
  };
  passport?: {
    name?: string;
    number?: string;
    birth?: string;
    expiry?: string;
  } | null;
  room?: {
    type?: string;
    request?: string;
  } | null;
};

export type CreateReservationPayloadOptions = {
  product: ReservationProductContext;
  selectedDate?: AvailableDate;
  personCount: number;
  unitPrice: number;
  totalPrice: number;
};

export const createReservationPayload = ({
  product,
  selectedDate,
  personCount,
  unitPrice,
  totalPrice,
}: CreateReservationPayloadOptions): ReservationStoragePayload => {
  const seatsBeforeSelection = selectedDate?.seats ?? 0;

  return {
    productId: product.id,
    legacyProductId: product.legacyProductId,
    legacyFeeOptionId: product.legacyFeeOptionId,
    legacyPackageScheduleId: product.legacyPackageScheduleId,
    productType: product.productType,
    title: product.title,
    href: product.href,
    selectedDateId: selectedDate?.id ?? "",
    selectedDateLabel: selectedDate?.label ?? "",
    selectedWeekday: selectedDate?.day ?? "",
    personCount,
    unitPrice,
    totalPrice,
    currency: product.currency ?? "KRW",
    seatsBeforeSelection,
    remainingSeatsAfterSelection: Math.max(0, seatsBeforeSelection - personCount),
    guide: selectedDate?.guide ?? "",
    createdAt: Date.now(),
  };
};

export const parseCartItems = (
  value: string | null,
): ReservationStoragePayload[] => {
  if (!value) return [];

  try {
    const parsedValue = JSON.parse(value);
    return Array.isArray(parsedValue) ? parsedValue : [];
  } catch {
    return [];
  }
};

export const saveCartReservation = (payload: ReservationStoragePayload) => {
  if (typeof window === "undefined") return { count: 0, items: [] };

  const previousItems = parseCartItems(
    window.sessionStorage.getItem(CART_STORAGE_KEY),
  );
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

  window.sessionStorage.setItem(CART_STORAGE_KEY, JSON.stringify(nextItems));
  window.sessionStorage.setItem(CART_COUNT_STORAGE_KEY, String(nextItems.length));
  window.dispatchEvent(
    new CustomEvent("unotravel:cart-updated", {
      detail: { count: nextItems.length, items: nextItems },
    }),
  );

  return { count: nextItems.length, items: nextItems };
};

export const savePendingReservation = (payload: ReservationStoragePayload) => {
  if (typeof window === "undefined") return;

  window.sessionStorage.setItem(
    PENDING_RESERVATION_STORAGE_KEY,
    JSON.stringify(payload),
  );
};

export const getPendingReservation = (): ReservationStoragePayload | null => {
  if (typeof window === "undefined") return null;

  try {
    const rawPayload = window.sessionStorage.getItem(
      PENDING_RESERVATION_STORAGE_KEY,
    );
    if (!rawPayload) return null;

    const parsedPayload = JSON.parse(rawPayload);
    if (!parsedPayload || typeof parsedPayload !== "object") return null;

    return parsedPayload as ReservationStoragePayload;
  } catch {
    return null;
  }
};

export const parseSubmittedReservations = (
  value: string | null,
): SubmittedReservation[] => {
  if (!value) return [];

  try {
    const parsedValue = JSON.parse(value);
    return Array.isArray(parsedValue) ? parsedValue : [];
  } catch {
    return [];
  }
};

export const getSubmittedReservations = (): SubmittedReservation[] => {
  if (typeof window === "undefined") return [];

  return parseSubmittedReservations(
    window.sessionStorage.getItem(RESERVATION_LIST_STORAGE_KEY),
  );
};

export const saveSubmittedReservation = (
  payload: ReservationStoragePayload,
  details: Omit<
    SubmittedReservation,
    keyof ReservationStoragePayload | "reservationId" | "status" | "submittedAt"
  > = {},
) => {
  if (typeof window === "undefined") return null;

  const submittedAt = Date.now();
  const reservationId = `R-${new Date(submittedAt)
    .toISOString()
    .slice(2, 10)
    .replace(/-/g, "")}-${String(submittedAt).slice(-4)}`;
  const nextReservation: SubmittedReservation = {
    ...payload,
    ...details,
    reservationId,
    status: "예약 확인",
    submittedAt,
  };
  const previousItems = getSubmittedReservations();
  const nextItems = [nextReservation, ...previousItems];

  window.sessionStorage.setItem(
    RESERVATION_LIST_STORAGE_KEY,
    JSON.stringify(nextItems),
  );
  window.dispatchEvent(
    new CustomEvent("unotravel:reservation-updated", {
      detail: { count: nextItems.length, items: nextItems },
    }),
  );

  return nextReservation;
};

export const navigateInternal = (href: string) => {
  if (typeof window === "undefined") return;

  window.history.pushState({}, "", href);
  window.dispatchEvent(new PopStateEvent("popstate"));
  window.dispatchEvent(new Event("unotravel:navigate"));
  window.scrollTo({ top: 0, left: 0, behavior: "auto" });
};

export const isReservationUserLoggedIn = () => {
  return isLocalAuthSessionActive();
};

export const ensureReservationUserLoggedIn = () => refreshAuthSession();

export const navigateToLoginForReservation = (
  redirectHref = DEFAULT_RESERVATION_PAGE_URL,
) => {
  if (typeof window === "undefined") return;

  window.sessionStorage.setItem(LOGIN_REDIRECT_STORAGE_KEY, redirectHref);
  navigateInternal("/login");
};
