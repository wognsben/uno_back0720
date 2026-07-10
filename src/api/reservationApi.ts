// reservationApi.ts
// 기존 우노트래블 DB와 연결될 상품/예약/장바구니/마이페이지 API 함수와 타입을 모아둔 파일이다.
// reservationStore의 로컬 payload를 백엔드 예약 초안/장바구니 요청 형식으로 변환하는 역할을 담당한다.
// 현재 UI의 sessionStorage 예약 흐름과 실제 서버 연동 흐름이 섞이지 않도록 API 경계를 분리한다.

import { unoApiData } from "./apiClient";
import {
  DEFAULT_PRODUCT_DETAIL_MODE,
  clampAvailabilityQuery,
  type ProductDetailMode,
} from "./apiPerformance";
import type { ReservationStoragePayload } from "../pages/product/product_com/reservationStore";

export type ProductKind = "semi" | "daily";
export type AvailabilityStatus = "available" | "soon" | "soldout";

export type AuthSessionMember = {
  id: string;
  name?: string;
  email?: string;
  phone?: string;
  kakaoId?: string;
};

export type AuthSessionResponse = {
  isLoggedIn: boolean;
  member: AuthSessionMember | null;
};

export type ProductPrice = {
  deposit: number;
  localPayment?: number;
  localPaymentCurrency?: "EUR" | "KRW" | "USD" | "JPY";
};

export type ProductSummary = {
  id: string;
  legacyProductId?: number | string;
  legacyFeeOptionId?: number | string;
  legacyPackageScheduleId?: number | string;
  productType: ProductKind;
  title: string;
  category?: string;
  legacyCategory?: string;
  href: string;
  thumbnailUrl?: string;
  price?: ProductPrice;
  requiresPassport?: boolean;
  requiresRoomInfo?: boolean;
  requiresDelivery?: boolean;
};

export type ProductFeeOption = {
  id: number | string;
  label: string;
  deposit: number;
  localPayment?: number;
  localPaymentCurrency?: "EUR" | "KRW" | "USD" | "JPY";
  isDefault?: boolean;
};

export type PackageScheduleOption = {
  id: number | string;
  label: string;
  startDate: string;
  endDate?: string;
  deposit: number;
  middlePayment?: number;
  finalPayment?: number;
  airfare?: number;
  totalPrice?: number;
  seat?: number;
  status?: string;
  isDefault?: boolean;
};

export type ProductDetailResponse = ProductSummary & {
  reservationDefaults?: {
    requiresPassport?: boolean;
    requiresRoomInfo?: boolean;
    requiresDelivery?: boolean;
    defaultFinalStatus?: string;
  };
  feeOptions: ProductFeeOption[];
  packageSchedules?: PackageScheduleOption[];
  detailHtml?: string;
};

export type ProductListResponse = {
  items: ProductSummary[];
};

export type AvailabilityDateResponse = {
  id: string;
  date: string;
  weekday: string;
  status: AvailabilityStatus;
  displayLabel: string;
  remainingSeats?: number;
  legacyPackageScheduleId?: number | string;
};

export type ProductAvailabilityResponse = {
  productId: string;
  legacyProductId?: number | string;
  legacyPackageScheduleId?: number | string;
  from?: string;
  to?: string;
  dates: AvailabilityDateResponse[];
};

export type ReservationOptionRequest = {
  feeId?: number | string;
  personCount: number;
};

export type ReservationDraftRequest = {
  productId: string;
  legacyProductId?: number | string;
  legacyPackageScheduleId?: number | string;
  tourDate: string;
  tourTime?: string;
  items: ReservationOptionRequest[];
  memo?: string;
};

export type ReservationDraftResponse = {
  rid: number | string;
  status: "booking" | "cart";
  nextUrl?: string;
};

export type CartItemResponse = {
  rid: number | string;
  productId: string;
  legacyProductId?: number | string;
  title: string;
  tourDate: string;
  options: Array<
    ReservationOptionRequest & {
      label: string;
      deposit?: number;
      localPayment?: number;
    }
  >;
  totalDeposit: number;
  totalLocalPayment?: number;
};

export type CartResponse = {
  items: CartItemResponse[];
  count: number;
};

export type ReservationApplicant = {
  name: string;
  phone: string;
  email: string;
  kakaoId?: string;
};

export type ReservationPassport = {
  nameKo?: string;
  nameEn?: string;
  birthDate?: string;
  passportNo?: string;
  passportExpireDate?: string;
  gender?: "M" | "F" | "";
};

export type ReservationDelivery = {
  zip?: string;
  addr1?: string;
  addr2?: string;
  addr3?: string;
  gift?: string;
};

export type ReservationConfirmRequest = {
  agreeRefundPolicy: boolean;
  applicant: ReservationApplicant;
  memo?: string;
  passports?: ReservationPassport[];
  roomInfo?: string;
  delivery?: ReservationDelivery;
};

export type ReservationStatusCode =
  | "cart"
  | "booking"
  | "1"
  | "2"
  | "3"
  | "9"
  | "91";

export type ReservationDetailResponse = {
  rid: number | string;
  reservationNo: string;
  status: ReservationStatusCode;
  statusLabel: string;
  createdAt?: string;
  product: ProductSummary & {
    requiresPassport?: boolean;
    requiresRoomInfo?: boolean;
    requiresDelivery?: boolean;
  };
  tourDate: string;
  tourTime?: string;
  options: Array<{
    feeId: number | string;
    label: string;
    personCount: number;
    deposit?: number;
    localPayment?: number;
    extraPayment?: number;
    packageTotal?: number;
    airfare?: number;
  }>;
  totalDeposit: number;
  totalLocalPayment?: number;
  totalExtraPayment?: number;
  totalPackagePrice?: number;
  totalAirfare?: number;
  applicantDefaults: ReservationApplicant;
  memo?: string;
  roomInfo?: string;
};

export type ReservationConfirmResponse = {
  rid: number | string;
  status: ReservationStatusCode;
  statusLabel: string;
  nextUrl?: string;
};

export type MyReservationItem = {
  rid: number | string;
  reservationNo: string;
  createdAt?: string;
  tourDate: string;
  status: ReservationStatusCode;
  statusLabel: string;
  product: Pick<ProductSummary, "id" | "legacyProductId" | "title" | "href">;
  options: Array<{
    label: string;
    personCount: number;
  }>;
  payment?: {
    deposit?: number;
    localPayment?: number;
    cardPayRef?: string | null;
    canPayByCard?: boolean;
  };
};

export type MyReservationsResponse = {
  items: MyReservationItem[];
};

export const createReservationDraftRequest = (
  payload: ReservationStoragePayload,
): ReservationDraftRequest => ({
  productId: payload.productId,
  legacyProductId: payload.legacyProductId,
  legacyPackageScheduleId: payload.legacyPackageScheduleId,
  tourDate: payload.selectedDateId,
  items: [
    {
      feeId: payload.legacyFeeOptionId,
      personCount: payload.personCount,
    },
  ],
});

export const getAuthSession = () =>
  unoApiData<AuthSessionResponse>("/auth/session.php");

export const getProducts = (query?: {
  type?: ProductKind;
  category?: string;
  legacyCategory?: string;
}) => unoApiData<ProductListResponse>("/products/index.php", { query });

export const getProductDetail = (
  productId: string,
  query: { mode?: ProductDetailMode } = {},
) =>
  unoApiData<ProductDetailResponse>("/products/detail.php", {
    query: {
      id: productId,
      mode: query.mode ?? DEFAULT_PRODUCT_DETAIL_MODE,
    },
  });

export const getProductAvailability = (
  productId: string,
  query: { from: string; to: string },
) =>
  unoApiData<ProductAvailabilityResponse>("/products/availability.php", {
    query: {
      id: productId,
      ...clampAvailabilityQuery(query),
    },
  });

export const getCart = () => unoApiData<CartResponse>("/cart/index.php");

export const createCartReservation = (payload: ReservationStoragePayload) =>
  unoApiData<ReservationDraftResponse>("/cart/index.php", {
    method: "POST",
    body: createReservationDraftRequest(payload),
  });

export const deleteCartReservation = (rid: number | string) =>
  unoApiData<{ rid: number | string; deleted: boolean }>(
    "/cart/delete.php",
    {
      method: "POST",
      body: { rid },
    },
  );

export const createReservationDraft = (payload: ReservationStoragePayload) =>
  unoApiData<ReservationDraftResponse>("/reservations/draft.php", {
    method: "POST",
    body: createReservationDraftRequest(payload),
  });

export const getReservationDraft = (rid: number | string) =>
  unoApiData<ReservationDetailResponse>("/reservations/detail.php", {
    query: { rid },
  });

export const confirmReservation = (
  rid: number | string,
  body: ReservationConfirmRequest,
) =>
  unoApiData<ReservationConfirmResponse>(
    "/reservations/confirm.php",
    {
      method: "POST",
      query: { rid },
      body,
    },
  );

export const getReservationComplete = (rid: number | string) =>
  unoApiData<ReservationDetailResponse>("/reservations/detail.php", {
    query: { rid },
  });

export const getMyReservations = () =>
  unoApiData<MyReservationsResponse>("/my/reservations.php");
