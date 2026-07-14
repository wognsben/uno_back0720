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

export type LoginCredentials = {
  mb_id: string;
  mb_password: string;
};

export type RegisterMemberRequest = {
  name: string;
  email: string;
  phone: string;
  password: string;
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
  subject?: string;
  deposit: number;
  advanceLocalPayment?: number | string;
  localPayment?: number | string;
  extraPayment?: number | string;
  localPaymentCurrency?: "EUR" | "KRW" | "USD" | "JPY";
  ticketFeeId?: number | string;
  isDefault?: boolean;
  isPrimary?: boolean;
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

export type ProductFaqItem = {
  id: number | string;
  categoryId?: string;
  category?: string;
  question: string;
  answerHtml?: string;
  answerText: string;
  order?: number;
  legacyProductIds?: Array<number | string>;
};

export type ProductGuideItem = {
  id: number | string;
  name: string;
  bodyText?: string;
  imageUrl?: string;
  imageAlt?: string;
};

export type ProductImageItem = {
  no: number;
  source?: string;
  url: string;
  width?: number;
  height?: number;
};

export type ProductTourOptions = {
  meeting?: string;
  meetingTime?: string;
  tourDay?: string;
  tourTime?: string;
  includes?: string;
  excludes?: string;
  map?: string;
  youtube?: string;
  beforeNotice?: string;
  preparation?: string;
  cancelRules?: string;
};

export type ProductDetailResponse = ProductSummary & {
  originalPrice?: number;
  priceDescription?: string;
  reservationDefaults?: {
    requiresPassport?: boolean;
    requiresRoomInfo?: boolean;
    requiresDelivery?: boolean;
    defaultFinalStatus?: string;
  };
  feeOptions: ProductFeeOption[];
  packageSchedules?: PackageScheduleOption[];
  faqs?: ProductFaqItem[];
  detailHtml?: string;
  heroImageUrl?: string;
  heroImages?: ProductImageItem[];
  images?: ProductImageItem[];
  detailImages?: ProductImageItem[];
  tourTopImages?: ProductImageItem[];
  tourCourseImages?: ProductImageItem[];
  tourInfoImages?: ProductImageItem[];
  tourAdImages?: ProductImageItem[];
  tourBannerImages?: ProductImageItem[];
  meetingImages?: ProductImageItem[];
  tourOptions?: ProductTourOptions;
  bodyImages?: ProductImageItem[];
  guideInfo?: string;
  guides?: ProductGuideItem[];
  productDocumentImages?: {
    course?: ProductImageItem[];
    features?: ProductImageItem[];
  };
};

export type ProductListResponse = {
  items: ProductSummary[];
};

export type ProductNavigationProduct = {
  title: string;
  href: string;
  productId?: string;
  legacyProductId?: number | string;
};

export type ProductNavigationItem = {
  id: string;
  category: ProductKind;
  country: string;
  countryKo: string;
  title: string;
  subtitle: string;
  meta: string[];
  regions: string[];
  products: ProductNavigationProduct[];
  href: string;
};

export type ProductNavigationGroup = {
  id: ProductKind;
  title: string;
  eyebrow: string;
  items: ProductNavigationItem[];
};

export type ProductNavigationResponse = {
  groups: ProductNavigationGroup[];
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
  legacyPackageScheduleId?: number | string;
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
  applicant?: ReservationApplicant;
  passports?: ReservationPassport[];
  roomInfo?: string;
};

export type ReservationDraftResponse = {
  rid: number | string;
  status: ReservationStatusCode;
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
  | "11"
  | "3"
  | "9"
  | "91"
  | "99";

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

export type InquiryCreateRequest = {
  subject?: string;
  content: string;
};

export type InquiryCreateResponse = {
  board: "cusTour";
  threadId: number | string;
  messageId: number | string;
  isNewThread: boolean;
  subject: string;
  createdAt: string;
  nextUrl?: string;
};

export type CommunityInquiryCreateResponse = {
  board: "qna";
  postId: number | string;
  subject: string;
  createdAt: string;
  nextUrl?: string;
};

export type CommunityBoardType = "notice" | "review" | "qna";

// `qna` here means the public community Q&A board only.
// Product-detail bottom QNA/FAQ is separate and is exposed as ProductDetailResponse.faqs.

export type CommunityBoardPost = {
  id: string;
  type: "notice" | "review" | "inquiry";
  board: string;
  legacyBoardUrl?: string;
  title: string;
  excerpt?: string;
  contentHtml?: string;
  contentText?: string;
  author?: string;
  date: string;
  views?: number;
  href: string;
  isPinned?: boolean;
  isNew?: boolean;
};

export type CommunityPostsResponse = {
  type: CommunityBoardType;
  board: string;
  items: CommunityBoardPost[];
  pagination: {
    page: number;
    perPage: number;
    total: number;
    totalPages: number;
  };
};

export type CommunityPostDetailResponse = {
  type: CommunityBoardType;
  board: string;
  item: CommunityBoardPost;
};

export type InquiryMessage = {
  id: number | string;
  role: "user" | "admin";
  author?: string;
  content: string;
  createdAt: string;
};

export type InquiryThread = {
  id: number | string;
  subject: string;
  createdAt: string;
  updatedAt?: string;
  commentCount: number;
};

export type InquiryThreadResponse = {
  thread: InquiryThread | null;
  messages: InquiryMessage[];
};

const normalizeTourTime = (value?: string | null): string => {
  if (!value) return "";

  const match = value.match(/\b([01]\d|2[0-3]):[0-5]\d\b/);
  return match?.[0] ?? "";
};

export const createReservationDraftRequest = (
  payload: ReservationStoragePayload,
  details: Partial<ReservationDraftRequest> = {},
): ReservationDraftRequest => {
  const payloadItems =
    Array.isArray(payload.items) && payload.items.length > 0
      ? payload.items
      : null;
  const feeId =
    payload.productType === "semi"
      ? payload.legacyPackageScheduleId ?? payload.legacyFeeOptionId
      : payload.legacyFeeOptionId;

  return {
    productId: payload.productId,
    legacyProductId: payload.legacyProductId,
    legacyPackageScheduleId: payload.legacyPackageScheduleId,
    tourDate: payload.selectedDateId,
    tourTime: normalizeTourTime(payload.selectedDateLabel),
    items:
      payloadItems ??
      [
        {
          feeId,
          legacyPackageScheduleId: payload.legacyPackageScheduleId,
          personCount: payload.personCount,
        },
      ],
    ...details,
  };
};

export const getAuthSession = () =>
  unoApiData<AuthSessionResponse>("/auth/session.php");

export const loginWithCredentials = async (credentials: LoginCredentials) => {
  await getAuthSession().catch(() => null);

  return unoApiData<AuthSessionResponse>("/auth/login.php", {
    method: "POST",
    body: credentials,
  });
};

export const registerMember = async (payload: RegisterMemberRequest) => {
  await getAuthSession().catch(() => null);

  return unoApiData<AuthSessionResponse>("/auth/register.php", {
    method: "POST",
    body: payload,
  });
};

export const getProducts = (query?: {
  type?: ProductKind;
  category?: string;
  legacyCategory?: string;
}) => unoApiData<ProductListResponse>("/products/index.php", { query });

export const getProductNavigation = () =>
  unoApiData<ProductNavigationResponse>("/products/navigation.php");

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

export const createReservationDraft = (
  payload: ReservationStoragePayload,
  details: Partial<ReservationDraftRequest> = {},
) =>
  unoApiData<ReservationDraftResponse>("/reservations/draft.php", {
    method: "POST",
    body: createReservationDraftRequest(payload, details),
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

export const createInquiry = (body: InquiryCreateRequest) =>
  unoApiData<InquiryCreateResponse>("/inquiries/create.php", {
    method: "POST",
    body,
  });

export const getMyInquiryThread = () =>
  unoApiData<InquiryThreadResponse>("/inquiries/index.php");

export const createCommunityInquiry = (body: InquiryCreateRequest) =>
  unoApiData<CommunityInquiryCreateResponse>("/community/inquiries/create.php", {
    method: "POST",
    body,
  });

export const getCommunityPosts = (query: {
  type: CommunityBoardType;
  page?: number;
  perPage?: number;
  search?: string;
}) => unoApiData<CommunityPostsResponse>("/community/posts.php", { query });

export const getCommunityPostDetail = (
  type: CommunityBoardType,
  id: number | string,
) => unoApiData<CommunityPostDetailResponse>("/community/posts.php", {
  query: { type, id },
});
