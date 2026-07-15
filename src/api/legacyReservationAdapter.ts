// legacyReservationAdapter.ts
// React 예약 API 요청을 기존 우노트래블 DB의 tour_reg 저장 구조로 바꾸는 기준 어댑터 파일이다.
// fee_id/membCnt 파이프 문자열, 예약금/현지지불금 합계, 상태 라벨 같은 백엔드 저장 규칙을 모아둔다.
// 실제 DB 접속 파일이 아니라 PHP/Node 브리지 구현 시 중복 해석을 막기 위한 순수 변환 로직 역할이다.

import type {
  ReservationDraftRequest,
  ReservationOptionRequest,
  ReservationStatusCode,
} from "./reservationApi";

export type LegacyReservationRowStatus = ReservationStatusCode;

export type LegacyReservationMember = {
  id: string;
  name?: string;
  email?: string;
  phone?: string;
  kakaoId?: string;
};

export type LegacyReservationFeeLine = ReservationOptionRequest & {
  deposit?: number;
  localPayment?: number;
  packageTotal?: number;
  airfare?: number;
};

export type LegacyReservationDraftInput = Omit<
  ReservationDraftRequest,
  "items"
> & {
  items: LegacyReservationFeeLine[];
  member: LegacyReservationMember;
  status: Extract<LegacyReservationRowStatus, "cart" | "booking">;
  productType?: "semi" | "daily";
  nation?: string;
  ip?: string;
  isMobile?: "Y" | "N";
  createdAt?: number;
};

export type LegacyTourRegDraftRow = {
  regDate: number;
  mb_id: string;
  mb_name: string;
  mb_email: string;
  mb_kakao: string;
  mb_hp: string;
  tourDay: string;
  tourTime: string;
  pid: number | string;
  event_pid: number;
  membCnt: string;
  fee_id: string;
  total_fee1: number;
  total_fee2: number;
  total_fee3: number;
  total_fee4: number;
  total_fee_air: number;
  regMemo: string;
  status: LegacyReservationRowStatus;
  mb_ip: string;
  nation: string;
  isMobile: "Y" | "N";
  del_time: number;
};

export const LEGACY_STATUS_LABELS: Record<LegacyReservationRowStatus, string> = {
  cart: "장바구니",
  booking: "예약 입력 중",
  "1": "예약대기",
  "2": "예약확인",
  "3": "예약확정",
  "9": "예약취소",
  "91": "취소요청",
};

export const getLegacyReservationStatusLabel = (
  status: LegacyReservationRowStatus,
) => LEGACY_STATUS_LABELS[status] ?? "예약상태 확인";

export const serializeLegacyPipeValues = (
  values: Array<number | string | undefined>,
) => {
  const normalizedValues = values.filter(
    (value) => value !== undefined && value !== "",
  );

  return normalizedValues.length ? `${normalizedValues.map(String).join("|")}|` : "";
};

export const serializeLegacyFeeIds = (items: LegacyReservationFeeLine[]) =>
  serializeLegacyPipeValues(items.map((item) => item.feeId));

export const serializeLegacyMemberCounts = (
  items: LegacyReservationFeeLine[],
) => serializeLegacyPipeValues(items.map((item) => item.personCount));

export const sumLegacyLineAmount = (
  items: LegacyReservationFeeLine[],
  key: "deposit" | "localPayment" | "packageTotal" | "airfare",
) =>
  items.reduce((sum, item) => {
    const amount = item[key] ?? 0;
    return sum + amount * item.personCount;
  }, 0);

export const createLegacyTourRegDraftRow = ({
  legacyProductId,
  legacyPackageScheduleId,
  tourDate,
  tourTime,
  items,
  memo,
  member,
  status,
  productType = "daily",
  nation = "",
  ip = "",
  isMobile = "N",
  createdAt,
}: LegacyReservationDraftInput): LegacyTourRegDraftRow => {
  const pid = legacyProductId ?? "";
  const feeItems = items;

  return {
    regDate: createdAt ?? Math.floor(Date.now() / 1000),
    mb_id: member.id,
    mb_name: member.name ?? "",
    mb_email: member.email ?? "",
    mb_kakao: member.kakaoId ?? "",
    mb_hp: member.phone ?? "",
    tourDay: tourDate,
    tourTime: tourTime ?? "",
    pid,
    event_pid: 0,
    membCnt: serializeLegacyMemberCounts(feeItems),
    fee_id: serializeLegacyFeeIds(feeItems),
    total_fee1: sumLegacyLineAmount(feeItems, "deposit"),
    total_fee2: sumLegacyLineAmount(feeItems, "localPayment"),
    total_fee3: 0,
    total_fee4: sumLegacyLineAmount(feeItems, "packageTotal"),
    total_fee_air: sumLegacyLineAmount(feeItems, "airfare"),
    regMemo: memo ?? "",
    status,
    mb_ip: ip,
    nation,
    isMobile,
    del_time: 0,
  };
};

export const canExposeReservationInMyPage = (
  status: LegacyReservationRowStatus,
) => status !== "cart" && status !== "booking";
