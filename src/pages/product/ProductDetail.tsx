/* ==========================================================
   ProductDetail.tsx

   Product Type A Detail Page

   ?�용 ?�이지
   - ?��??�키지 ?�세
   - ?�일리투???�세

   백엔???�동
   ------------------------------------------
   product        ???�품 기본 ?�보
   guide          ??가?�드 ?�개
   reviews        ??리뷰
   schedule       ???�정 / 출발 / ?�착 / 가�?/ ?�약 ?�보
   availableDates ??가???�약 ?�짜
   detailImages   ???�세?�이지 ?��?지
   notices        ???�불 규정 / ?�약 ?�내 / 중요 고�? / ?�수 준비품

   Header / ProductNavigation / Footer??App.tsx 공통 컴포?�트 ?�용
========================================================== */

import { useEffect, useMemo, useRef, useState } from "react";
import type { MouseEvent } from "react";

import {
  getProductAvailability,
  getProductDetail,
  type ProductAvailabilityResponse,
  type ProductDetailResponse,
} from "../../api/reservationApi";
import { saveRecentlyViewedProduct } from "../../utils/recentlyViewed";
import ReservationModule from "./product_com/ReservationModule";
import BookingSide from "./product_com/Booking_side";
import InfiniteOther, {
  getProductListItemType,
  normalizeProductListRelatedProducts,
} from "./Infiniteother";
import {
  type AvailableDate,
  getAvailabilityDisplayLabel,
  getWeekdayKo,
  parseDateId,
  PriceText,
} from "./product_com/reservationUtils";
import type {
  InfiniteOtherProduct,
  InfiniteOtherSourceProduct,
} from "./Infiniteother";
import ProductDocument from "./product_com/product_document";
import {
  getLegacyFeeOptionId,
  getLegacyPackageScheduleId,
  getLegacyProductId,
} from "./productLegacyIds";
import type {
  DetailNotice,
  DetailScheduleDay,
  MeetingPoint,
  ReviewItem,
} from "./product_com/product_document";

import imgHero from "../../imports/세미패키지메인히어로그리드/3f5da2e34aadc41b88babc2cb3cf79d54480fb17.png";
import imgDetailA from "../../imports/세미패키지메인히어로그리드/4330107f5001d8438ca2a32856e91d36fc97e09f.png";
import imgDetailB from "../../imports/세미패키지메인히어로그리드/a1bb687947753b4c890d720a1b31402344e5c88d.png";
import imgDetailC from "../../imports/세미패키지메인히어로그리드/ca8b91484dd437b9300e61e1611bbff92bf1b412.png";

/* Desktop Responsive Base
   - ?�제 ProductDetail canvas??Figma 기�? 1700px
   - ?�면?�서 보이??desktop ?�본 기�??� 1600px�??�한
   - 1600px ?�하?�서??canvas ?�체�?부�???�� 맞춰 scale 처리 */
const DETAIL_CANVAS_WIDTH = 1700;
const DETAIL_DESKTOP_BASE_WIDTH = 1600;

const getAvailabilityQueryRange = () => {
  const from = new Date();
  from.setHours(0, 0, 0, 0);

  const to = new Date(from);
  to.setDate(from.getDate() + 119);

  const formatDate = (date: Date) =>
    [
      date.getFullYear(),
      String(date.getMonth() + 1).padStart(2, "0"),
      String(date.getDate()).padStart(2, "0"),
    ].join("-");

  return {
    from: formatDate(from),
    to: formatDate(to),
  };
};

/*
  Recently Viewed Storage
  ------------------------------------------
  Header VIEWED ?�동 ?�픈 기능?�서 ?�용?????�도�?
  ?�세?�이지 진입 ???�재 ?�품 ?�보�?sessionStorage???�?�한??
*/
type ProductKind = "semi" | "daily";
type RelatedProduct = InfiniteOtherProduct;

type ProductListProduct = InfiniteOtherSourceProduct;

type ProductDetailProps = {
  products?: ProductListProduct[];
};

const SEMI_DETAIL_DATA = {
  id: "italy-11",
  legacyProductId: getLegacyProductId("italy-11"),
  legacyPackageScheduleId: getLegacyPackageScheduleId("italy-11"),
  href: "/product/detail/italy-11",

  /*
    Product Type Split
    ------------------------------------------
    semi  : ?��??�키지 ?�세. ??��권형 ?�정??+ ?�약 ?�널 중심.
    daily : ?�일리투???�세. ?��? 즉시 ?�약 ?�격??강하므�??�력???�약 UI�??�선 ?�출.

    ?�제 백엔???�동 ??category �?semi / daily)???�라 ??값을 교체?�다.
  */
  productType: "semi" as ProductKind,

  eyebrow: "SEMI PACKAGE · ITALY",
  title: "ITALY GRAND TOUR",
  titleEn: "ITALY GRAND TOUR",
  region: "ROME · FIRENZE · VENEZIA · NAPOLI",
  routeCode: "UNO / IT11",
  duration: "9D11N",
  basePrice: 2890000,
  currency: "KRW",

  /*
    Currency Backend Fields
    ------------------------------------------
    백엔???�동 ??currency / price�?분리?�서 받는??
    currency �?KRW / EUR / USD / JPY)???�라 ?�론?�에???�폐 기호�?매핑?�다.
  */

  /*
    Premium Travel Document Backend Fields
    ------------------------------------------
    ?��??�키지 ?�세??PREMIUM TRAVEL DOCUMENT ?�역???�시?�는 ?�이?�다.
    ?�제 백엔???�동 ??schedule / flight / ticket / route / status 값을
    관리자 ?�력값으�?교체?�다.

    - ticket.status       ??출발?�정 / ?�약가??/ 마감?�박 ???�태
    - ticket.outbound     ??가?�편 ??�� ?�는 ?�???�동 ?�정
    - ticket.inbound      ???�는????�� ?�는 ?�???�동 ?�정
    - routeCode / duration???�품 코드?� ?�행 기간
  */
  ticket: {
    status: "출발 예정",
    outbound: {
      label: "가는 편",
      airline: "터키항공",
      departDate: "10월 17일",
      arriveDate: "10월 18일",
      fromCode: "ICN",
      fromCity: "인천",
      toCode: "FCO",
      toCity: "로마",
      departTime: "23:20",
      arriveTime: "09:50",
      duration: "17시간 30분",
      transfer: "이스탄불 경유",
    },
    inbound: {
      label: "오는 편",
      airline: "터키항공",
      departDate: "10월 25일",
      arriveDate: "10월 26일",
      fromCode: "FCO",
      fromCity: "로마",
      toCode: "ICN",
      toCity: "인천",
      departTime: "19:25",
      arriveTime: "18:20",
      duration: "22시간 55분",
      transfer: "이스탄불 경유",
    },
  },
  heroImage: imgHero,

  /*
    Detail Tab Backend Fields
    ------------------------------------------
    ?�세?�이지 ??콘텐츠는 모두 백엔??관리자 ?�력값으�?교체?�다.

    - review        ??리뷰 ??본문
    - scheduleIntro ??코스 ?�정 ???�단 ?�명
    - scheduleDays  ??코스 ?�정 ?�세 DAY 리스??
    - guide         ??가?�드 ?�보 ??본문
    - included      ???�함 ??본문
    - excluded      ??불포????본문
    - reservationNotice ???�약 ?�내 ??본문

    ?�재 문자?��? ?�론??UI ?�인???��? ?�이?�다.
  */
  guide:
    "우노트래블의 이탈리아 전문 가이드가 도시의 역사, 미술, 음식, 동선까지 여행의 흐름에 맞춰 안내합니다. 단순 관광이 아니라 도시의 맥락을 이해하는 일정으로 구성했습니다.",
  included:
    "전문 가이드 해설, 현장 일정 관리, 주요 구간 동선 안내가 포함됩니다.",
  excluded:
    "항공권, 개인 경비, 여행자 보험, 자유 일정 비용은 상품 조건에 따라 별도로 안내됩니다.",
  seller:
    "우노트래블은 이탈리아와 지중해 지역을 중심으로 세미패키지와 데일리투어를 운영합니다. 예약 전 일정, 가능 날짜, 포함 조건을 확인한 뒤 상담을 통해 최종 확정합니다.",
  review:
    "일정이 과하게 빡빡하지 않고 주요 도시의 분위기를 충분히 느낄 수 있었다는 후기가 많습니다. 특히 동선 구성과 미술관 해설에 대한 만족도가 높았습니다.",
  reviews: [
    {
      id: "review-semi-01",
      nickname: "김민서",
      writtenAt: "2026.06.18",
      productTitle: "이탈리아 일주 9박 11일",
      rating: 5,
      title: "부모님과 함께 가기에 일정 구성이 좋았습니다.",
      body: "도시마다 이동 시간이 과하지 않았고, 가이드 설명이 단순 관광 안내가 아니라 배경까지 이해하게 해주는 방식이라 만족도가 높았습니다.",
    },
    {
      id: "review-semi-02",
      nickname: "박지윤",
      writtenAt: "2026.05.29",
      productTitle: "이탈리아 일주 9박 11일",
      rating: 5,
      title: "처음 이탈리아에 가는 사람에게 맞는 구성입니다.",
      body: "로마, 피렌체, 베네치아를 빠르게 훑는 느낌이 아니라 핵심을 잘 짚어주는 흐름이라 좋았습니다. 자유시간도 적당해서 부담이 덜했습니다.",
    },
    {
      id: "review-semi-03",
      nickname: "이수현",
      writtenAt: "2026.04.12",
      productTitle: "이탈리아 일주 9박 11일",
      rating: 4.8,
      title: "여유 있는 일정이라 오래 기억에 남았습니다.",
      body: "개별 여행으로는 동선 짜기가 어려웠을 것 같은 구간을 편하게 이동했습니다. 중간중간 식사 추천까지 세심하게 안내해줘서 좋았습니다.",
    },
  ] as ReviewItem[],
  reservationNotice:
    "출발일, 항공 및 현지 상황에 따라 세부 일정은 일부 조정될 수 있습니다. 예약 확정 전 가능 날짜, 포함 조건, 최종 금액을 다시 확인합니다.",
  scheduleIntro:
    "선택한 출발일 기준으로 가이드가 안내하는 대표 일정입니다. 실제 이동 순서와 방문지는 현장 상황과 예약 상태에 따라 일부 조정될 수 있습니다.",
  scheduleDays: [
    {
      day: "DAY 01",
      city: "ROME",
      time: "14:00",
      title: "로마 도착 · 오리엔테이션",
      body: "현지 미팅 장소 체크와 일정 안내, 주요 동선 브리핑을 진행합니다.",
    },
    {
      day: "DAY 02",
      city: "ROME",
      time: "09:30",
      title: "고대 로마와 도시 산책",
      body: "콜로세움과 포로 로마노 주변을 중심으로 로마의 시작과 도시 구조를 이해합니다.",
    },
    {
      day: "DAY 03",
      city: "FIRENZE",
      time: "10:00",
      title: "피렌체 이동 · 르네상스 해설",
      body: "피렌체의 광장, 성당, 미술관 동선을 따라 르네상스의 맥락을 살펴봅니다.",
    },
    {
      day: "DAY 04",
      city: "VENEZIA",
      time: "10:00",
      title: "베네치아 수상 도시 경험",
      body: "수상 교통과 골목 동선을 이용해 베네치아의 도시 구조를 체험합니다.",
    },
    {
      day: "DAY 05",
      city: "NAPOLI",
      time: "09:00",
      title: "남부 이동 · 빛과 해안",
      body: "나폴리와 남부 루트를 연결해 이탈리아 남쪽의 분위기를 완성합니다.",
    },
  ] as DetailScheduleDay[],
  /*
    Reservation Backend Fields
    ------------------------------------------
    ?�약 ?�널 / ?�른 가???�약 ?�짜 ?�인?�기 / ?�바구니 / ?�약 ?�이지 ?�동??
    공통?�로 ?�용?�는 ?�약 가???�정 ?�이?�다.

    ?�제 백엔???�동 ??관리자?�서 ?�력??출발?? ?�일, ?�여?? ?�원, 가�?
    ?�약 ?�태, ?�당 가?�드 값을 availableDates�?주입?�다.

    - seats    ???�재 ?�약 가?�한 ?�여??
    - capacity ??�??�원
    - price    ???�당 출발??1??가�?
    - status   ???�약 가??/ 마감 ?�박 / 마감 ???�태
    - guide    ???�당 가?�드 ?�는 ?�영?� ?�보
  */
  availableDates: [
    {
      id: "2026-07-15",
      label: "2026.07.15",
      day: "수",
      seats: 8,
      capacity: 12,
      price: 2890000,
      status: "예약 가능",
      guide: "UNO GUIDE A",
    },
    {
      id: "2026-08-03",
      label: "2026.08.03",
      day: "월",
      seats: 3,
      capacity: 12,
      price: 2990000,
      status: "마감 임박",
      guide: "UNO GUIDE B",
    },
    {
      id: "2026-09-11",
      label: "2026.09.11",
      day: "금",
      seats: 12,
      capacity: 12,
      price: 2890000,
      status: "예약 가능",
      guide: "UNO GUIDE A",
    },
  ] as AvailableDate[],
  /*
    Daily Tour Calendar Backend Fields
    ------------------------------------------
    ?�일리투???�세???�력 ?�약 UI???�용?�는 ?�짜 ?�이?�다.
    ?�늘 ?�전 ?�짜???�론?�에??disabled 처리?�고, ?�제 ?�약 가???��???
    백엔??availableDates ?�답??기�??�로 ?�단?�다.
  */
  dailyAvailableDates: [
    {
      id: "2026-07-04",
      label: "2026.07.04",
      day: "토",
      seats: 0,
      capacity: 12,
      price: 89000,
      status: "마감",
      guide: "ROME GUIDE A",
    },
    {
      id: "2026-07-15",
      label: "2026.07.15",
      day: "수",
      seats: 6,
      capacity: 12,
      price: 89000,
      status: "예약 가능",
      guide: "ROME GUIDE A",
    },
    {
      id: "2026-07-18",
      label: "2026.07.18",
      day: "토",
      seats: 2,
      capacity: 12,
      price: 99000,
      status: "마감 임박",
      guide: "ROME GUIDE B",
    },
    {
      id: "2026-07-22",
      label: "2026.07.22",
      day: "수",
      seats: 8,
      capacity: 12,
      price: 89000,
      status: "예약 가능",
      guide: "ROME GUIDE A",
    },
    {
      id: "2026-07-27",
      label: "2026.07.27",
      day: "월",
      seats: 10,
      capacity: 12,
      price: 89000,
      status: "예약 가능",
      guide: "ROME GUIDE A",
    },
  ] as AvailableDate[],
  detailImages: [
    {
      src: imgDetailA,
      kicker: "CITY",
      title: "도시를 천천히 걷는 일정",
      body: "이탈리아의 주요 도시를 빠르게 소비하지 않고, 각 도시의 고유한 리듬에 맞춰 이동합니다.",
    },
    {
      src: imgDetailB,
      kicker: "ART",
      title: "미술과 건축의 맥락을 읽는 시간",
      body: "작품명만 나열하는 해설이 아니라 작품과 도시의 관계를 함께 설명합니다.",
    },
    {
      src: imgDetailC,
      kicker: "SOUTH",
      title: "남부의 빛과 풍경을 더한 루트",
      body: "로마 중심 일정에서 끝나지 않고 남부의 풍경까지 연결한 구성입니다.",
    },
  ],
  notices: [
    {
      title: "환불 규정",
      body: "예약 확정 후 취소 시점에 따라 취소 수수료가 발생할 수 있습니다. 실제 환불 규정은 최종 예약 확정 안내 기준으로 적용됩니다.",
    },
    {
      title: "필수 준비품",
      body: "여권, 편한 워킹화, 얇은 겉옷, 유럽형 멀티 어댑터와 개인 경비를 준비해 주세요. 미술관 입장 시 큰 캐리어 반입은 제한될 수 있습니다.",
    },
  ] as DetailNotice[],
  /*
    Meeting Point Backend Fields
    ------------------------------------------
    PREMIUM TRAVEL DOCUMENT ?�는 DAILY TOUR CALENDAR ?�단???�출?�는 미팅 ?�소 ?�이?�다.
    ?��??�키지 / ?�일리투??모두 ?�일 컴포?�트�??�용?�며, ?�제 백엔???�동 ??
    meetingPoint 값을 ?�품�?미팅 ?�소 ?�는 ?�??집결지�?교체?�다.
  */
  meetingPoint: {
    name: "Roma Termini Station",
    address: "Via Giovanni Giolitti 34, 00185 Roma RM, Italy",
    time: "08:30",
    lat: 41.901,
    lng: 12.501,
    mapUrl:
      "https://www.google.com/maps?q=Roma%20Termini%20Station&output=embed",
    directionUrl:
      "https://www.google.com/maps/search/?api=1&query=Roma%20Termini%20Station",
  } as MeetingPoint,
  /*
    Related Products Backend Fields
    ------------------------------------------
    ?�세?�이지 ?�단 추천 ?�품 ?�역?�다.
    ?�재 ?�품�?같�? productType�?보여준??

    - semi  ?�세: relatedSemiPackages
    - daily ?�세: relatedDailyTours

    ?�제 백엔???�동 ???�재 productId�??�외??관???�품 배열???�려준??
  */
  relatedSemiPackages: [
    {
      id: "italy-9",
      title: "이탈리아 일주 7박 9일",
      eyebrow: "CLASSIC ITALY",
      duration: "7N 9D",
      price: 2590000,
      href: "/product/detail/italy-9",
      image: imgDetailA,
    },
    {
      id: "dolomiti-11",
      title: "이탈리아 일주 + 돌로미티 11일",
      eyebrow: "DOLOMITI LIMITED",
      duration: "11D",
      price: 3290000,
      href: "/product/detail/dolomiti-11",
      image: imgDetailB,
    },
    {
      id: "sicilia-9",
      title: "지중해의 황금빛 시칠리아 일주 9일",
      eyebrow: "SICILIA COLLECTION",
      duration: "9D",
      price: 2790000,
      href: "/product/detail/sicilia-9",
      image: imgDetailC,
    },
    {
      id: "spain-9",
      title: "스페인 클래식 세미패키지 9일",
      eyebrow: "SPAIN CLASSIC",
      duration: "9D",
      price: 2690000,
      href: "/product/detail/spain-9",
      image: imgDetailA,
    },
    {
      id: "portugal-8",
      title: "포르투갈 리스본 · 포르투 8일",
      eyebrow: "PORTUGAL ROUTE",
      duration: "8D",
      price: 2490000,
      href: "/product/detail/portugal-8",
      image: imgDetailB,
    },
    {
      id: "egypt-8",
      title: "이집트 고대문명 세미패키지 8일",
      eyebrow: "EGYPT HERITAGE",
      duration: "8D",
      price: 2890000,
      href: "/product/detail/egypt-8",
      image: imgDetailC,
    },
  ] as RelatedProduct[],
  relatedDailyTours: [] as RelatedProduct[],
};

/*
  Daily Detail Mock Data
  ------------------------------------------
  ?�일리투???�세?�이지???��??�키지??PREMIUM TRAVEL DOCUMENT가 ?�니??
  DAILY TOUR CALENDAR�?중심?�로 ?�약 UI�?구성?�다.

  ?�제 백엔???�동 ??product id / category / region 값으�?
  ?�래 ?�이???�체�?관리자 ?�력값으�?교체?�다.
*/
const DAILY_DETAIL_DATA = {
  ...SEMI_DETAIL_DATA,
  id: "rome-vatican-daily",
  legacyProductId: getLegacyProductId("rome-vatican-daily"),
  legacyFeeOptionId: getLegacyFeeOptionId("rome-vatican-daily"),
  legacyPackageScheduleId: getLegacyPackageScheduleId("rome-vatican-daily"),
  href: "/product/detail/daily/rome-vatican-daily",
  productType: "daily" as ProductKind,
  eyebrow: "DAILY TOUR · ROME",
  title: "로마 바티칸 데일리 투어",
  titleEn: "ROME VATICAN DAY TOUR",
  region: "ROME · VATICAN · MUSEUM",
  routeCode: "UNO / RM01",
  duration: "1일",
  basePrice: 89000,
  currency: "KRW",
  guide:
    "로마 현지 전문 가이드가 바티칸 박물관, 성 베드로 대성당, 로마 핵심 동선을 하루 일정에 맞게 안내합니다. 현장 합류형 투어이므로 날짜와 잔여 인원을 확인한 뒤 예약합니다.",
  included:
    "전문 가이드 해설, 현장 일정 안내, 주요 코스 동선 브리핑이 포함됩니다.",
  excluded:
    "현장 개인 경비, 교통비, 여행자 보험, 개인 이어폰 등은 상품 조건에 따라 별도입니다.",
  review:
    "짧은 하루 안에 핵심 동선을 효율적으로 볼 수 있었다는 후기가 많습니다. 특히 바티칸 해설과 현장 이동 안내에 대한 만족도가 높았습니다.",
  reviews: [
    {
      id: "review-daily-01",
      nickname: "정유진",
      writtenAt: "2026.06.22",
      productTitle: "로마 바티칸 데일리 투어",
      rating: 5,
      title: "혼자 갔는데도 합류가 어렵지 않았습니다.",
      body: "미팅 장소 안내가 명확했고, 사람이 많은 구간에서도 가이드가 계속 동선을 정리해줘서 따라가기 편했습니다. 설명도 너무 길지 않아 좋았습니다.",
    },
    {
      id: "review-daily-02",
      nickname: "한도윤",
      writtenAt: "2026.06.09",
      productTitle: "로마 바티칸 데일리 투어",
      rating: 4.9,
      title: "바티칸을 처음 보는 사람에게 적당히 밀도 있습니다.",
      body: "사전 지식 없이 갔는데 무엇을 봐야 하는지 작품 위주로 설명해줘서 이해가 쉬웠습니다. 현장 상황도 계속 공유해줘서 불안하지 않았습니다.",
    },
    {
      id: "review-daily-03",
      nickname: "김세아",
      writtenAt: "2026.05.31",
      productTitle: "로마 바티칸 데일리 투어",
      rating: 4.8,
      title: "짧은 일정에서 시간을 아끼기 좋았습니다.",
      body: "로마에 머무는 시간이 짧아서 신청했는데 개인적으로 갔으면 놓쳤을 포인트를 많이 볼 수 있었습니다. 큰 동선 위주라 이동 피로도 적었습니다.",
    },
  ] as ReviewItem[],
  reservationNotice:
    "데일리투어는 현장 합류형 상품입니다. 미팅 시간, 장소, 현장 상황에 따른 코스 변경 가능성을 예약 확정 전 반드시 확인합니다.",
  scheduleIntro:
    "데일리투어는 선택 날짜 기준으로 현장에서 합류하는 코스입니다. 실제 미팅 장소와 시간은 예약 확정 후 안내합니다.",
  scheduleDays: [
    {
      day: "COURSE 01",
      city: "VATICAN",
      time: "09:00",
      title: "바티칸 미팅 · 투어 시작",
      body: "지정 미팅 포인트에서 가이드와 합류한 뒤 전체 동선과 유의사항을 안내합니다.",
    },
    {
      day: "COURSE 02",
      city: "MUSEUM",
      time: "10:00",
      title: "바티칸 박물관 해설",
      body: "주요 작품과 공간을 중심으로 역사와 맥락을 설명합니다.",
    },
    {
      day: "COURSE 03",
      city: "BASILICA",
      time: "13:30",
      title: "성 베드로 대성당 주변 안내",
      body: "현장 상황에 따라 대성당 및 광장 주변 동선을 안내합니다.",
    },
  ] as DetailScheduleDay[],
  dailyAvailableDates: [
    {
      id: "2026-07-04",
      label: "2026.07.04",
      day: "토",
      seats: 0,
      capacity: 12,
      price: 89000,
      status: "마감",
      guide: "ROME GUIDE A",
    },
    {
      id: "2026-07-15",
      label: "2026.07.15",
      day: "수",
      seats: 6,
      capacity: 12,
      price: 89000,
      status: "예약 가능",
      guide: "ROME GUIDE A",
    },
    {
      id: "2026-07-18",
      label: "2026.07.18",
      day: "토",
      seats: 2,
      capacity: 12,
      price: 99000,
      status: "마감 임박",
      guide: "ROME GUIDE B",
    },
    {
      id: "2026-07-22",
      label: "2026.07.22",
      day: "수",
      seats: 8,
      capacity: 12,
      price: 89000,
      status: "예약 가능",
      guide: "ROME GUIDE A",
    },
    {
      id: "2026-07-27",
      label: "2026.07.27",
      day: "월",
      seats: 10,
      capacity: 12,
      price: 89000,
      status: "예약 가능",
      guide: "ROME GUIDE A",
    },
  ] as AvailableDate[],
  meetingPoint: {
    name: "바티칸 박물관 입구",
    address: "Viale Vaticano, 00165 Roma RM, Italy",
    time: "08:50",
    lat: 41.9065,
    lng: 12.4536,
    mapUrl: "https://www.google.com/maps?q=Vatican%20Museums&output=embed",
    directionUrl:
      "https://www.google.com/maps/search/?api=1&query=Vatican%20Museums",
  } as MeetingPoint,
  relatedDailyTours: [
    {
      id: "rome-city-walk",
      title: "로마 시티워크 데일리 투어",
      eyebrow: "ROME WALK",
      duration: "1D",
      price: 79000,
      href: "/product/detail/daily/rome-city-walk",
      image: imgDetailA,
    },
    {
      id: "firenze-uffizi-daily",
      title: "피렌체 우피치 미술관 투어",
      eyebrow: "FIRENZE ART",
      duration: "1D",
      price: 99000,
      href: "/product/detail/daily/firenze-uffizi-daily",
      image: imgDetailB,
    },
    {
      id: "napoli-pompei-daily",
      title: "나폴리 · 폼페이 데일리 투어",
      eyebrow: "NAPOLI POMPEI",
      duration: "1D",
      price: 119000,
      href: "/product/detail/daily/napoli-pompei-daily",
      image: imgDetailC,
    },
    {
      id: "venezia-walk-daily",
      title: "베네치아 골목 산책 데일리 투어",
      eyebrow: "VENEZIA WALK",
      duration: "1D",
      price: 89000,
      href: "/product/detail/daily/venezia-walk-daily",
      image: imgDetailA,
    },
    {
      id: "milano-design-daily",
      title: "밀라노 디자인 · 두오모 데일리 투어",
      eyebrow: "MILANO DESIGN",
      duration: "1D",
      price: 109000,
      href: "/product/detail/daily/milano-design-daily",
      image: imgDetailB,
    },
    {
      id: "amalfi-coast-daily",
      title: "아말피 코스트 데일리 투어",
      eyebrow: "AMALFI COAST",
      duration: "1D",
      price: 149000,
      href: "/product/detail/daily/amalfi-coast-daily",
      image: imgDetailC,
    },
  ] as RelatedProduct[],
};

function useProductDetailScale() {
  const shellRef = useRef<HTMLElement | null>(null);

  /*
    Desktop Responsive Initial Scale
    ------------------------------------------
    SPA 방식?�로 ?�세?�이지 진입 ??ResizeObserver ?�행 ??
    canvas가 ?�간?�으�?줄어?�는 layout jump�?줄인??
  */
  const getProductDetailScale = (width: number) => {
    const safeWidth = Math.max(width, 1024);
    const visibleWidth = Math.min(safeWidth, DETAIL_DESKTOP_BASE_WIDTH);

    return visibleWidth / DETAIL_CANVAS_WIDTH;
  };

  const [scale, setScale] = useState(() => {
    if (typeof window === "undefined") {
      return getProductDetailScale(DETAIL_DESKTOP_BASE_WIDTH);
    }

    return getProductDetailScale(
      document.documentElement.clientWidth || DETAIL_DESKTOP_BASE_WIDTH,
    );
  });

  useEffect(() => {
    const updateScale = () => {
      const shellWidth =
        shellRef.current?.getBoundingClientRect().width ||
        document.documentElement.clientWidth ||
        DETAIL_DESKTOP_BASE_WIDTH;

      /* Desktop Responsive
         - 1600px ?�상: 1600px�??�본 ?�시 기�??�로 고정
         - 1600px ?�하: 부�???기�??�로 1700px canvas�?축소
         - 100vw�??��? ?�아 vertical scrollbar ??���??�한 가�??�크롤을 방�? */
      const nextScale = getProductDetailScale(shellWidth);

      setScale(nextScale);
    };

    updateScale();

    const resizeObserver = new ResizeObserver(updateScale);
    if (shellRef.current) resizeObserver.observe(shellRef.current);

    window.addEventListener("resize", updateScale);

    return () => {
      resizeObserver.disconnect();
      window.removeEventListener("resize", updateScale);
    };
  }, []);

  return { shellRef, scale };
}

const STYLE = `
  .pd-shell {
    width: 100%;
    min-width: 1024px;
    background: #ffffff;
    /*
      Sticky descendants must use the document as their scroll container.
      overflow: hidden here made .uno-booking-side stick relative to this
      non-scrolling shell, so the booking panel moved away with the page.
      Horizontal clipping is already handled by the outer app layout.
    */
    overflow: visible;
    display: flex;
    justify-content: center;
  }

  .pd-canvas {
    width: 1700px;
    min-height: 0;
    height: auto;
    flex-shrink: 0;
    background: #ffffff;
    color: #151515;
    position: relative;
    transform-origin: top center;
  }

  .pd-hero {
    position: relative;
    width: 1700px;
    padding: 118px 50px 0;
    box-sizing: border-box;
  }

  .pd-hero-editorial {
    display: grid;
    grid-template-columns: 660px 1fr;
    gap: 58px;
    min-height: 960px;
    align-items: start;
  }

  .pd-hero-copy {
    position: sticky;
    top: 42px;
    min-height: 900px;
    padding: 34px 0 0;
    box-sizing: border-box;
    border-top: 1px solid rgba(21, 21, 21, 0.18);
  }

  .pd-eyebrow {
    font-family: var(--font-en);
    font-size: 13px;
    line-height: 1;
    letter-spacing: 0.22em;
    color: rgba(21, 21, 21, 0.54);
    margin-bottom: 28px;
    text-transform: uppercase;
  }

  .pd-title {
    margin: 0;
    max-width: 610px;
    font-family: var(--font-ko);
    font-size: 76px;
    line-height: 0.96;
    letter-spacing: -0.072em;
    font-weight: 560;
    color: #111111;
    word-break: keep-all;
  }

  .pd-title-en {
    margin-top: 22px;
    font-family: var(--font-en);
    font-size: 24px;
    line-height: 1;
    letter-spacing: 0.1em;
    color: rgba(21, 21, 21, 0.66);
    text-transform: uppercase;
  }

  .pd-hero-lead {
    display: none;
    margin: 46px 0 0;
    max-width: 520px;
    font-family: var(--font-ko);
    font-size: 19px;
    line-height: 1.82;
    letter-spacing: -0.045em;
    color: rgba(21, 21, 21, 0.72);
    word-break: keep-all;
  }

  .pd-hero-visual {
    display: grid;
    gap: 22px;
  }

  .pd-hero-media-card {
    position: relative;
    margin: 0;
    overflow: hidden;
    background: #f4f1ea;
  }

  .pd-hero-media-card.is-large {
    height: 640px;
  }

  .pd-hero-media-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
  }

  .pd-hero-media-card.is-small {
    height: 296px;
  }

  .pd-hero-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transform: scale(1.01);
  }

  .pd-hero-media-card figcaption {
    display: none;
    position: absolute;
    left: 22px;
    right: 22px;
    bottom: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1.24;
    letter-spacing: 0.14em;
    color: #ffffff;
    text-transform: uppercase;
    text-shadow: 0 1px 18px rgba(0, 0, 0, 0.32);
  }

  .pd-hero-media-card figcaption::before {
    content: "";
    width: 34px;
    height: 1px;
    flex: 0 0 auto;
    background: rgba(255, 255, 255, 0.72);
  }

  .pd-review-surface-backdrop {
    position: fixed;
    inset: 0;
    z-index: 120;
    background: rgba(21, 21, 21, 0.34);
    display: flex;
    justify-content: flex-end;
    align-items: stretch;
  }

  .pd-review-surface {
    width: min(760px, calc(100% - 80px));
    height: 100%;
    background: #ffffff;
    border-left: 1px solid rgba(21, 21, 21, 0.12);
    box-shadow: -28px 0 90px rgba(21, 21, 21, 0.18);
    padding: 42px 44px;
    box-sizing: border-box;
    overflow-y: auto;
  }

  .pd-review-surface-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 28px;
    padding-bottom: 34px;
    border-bottom: 1px solid rgba(21, 21, 21, 0.12);
  }

  .pd-review-surface-kicker {
    font-family: var(--font-en);
    font-size: 12px;
    line-height: 1;
    letter-spacing: 0.18em;
    color: rgba(21, 21, 21, 0.52);
    margin-bottom: 18px;
  }

  .pd-review-surface-title {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 38px;
    line-height: 1.12;
    letter-spacing: -0.06em;
    color: #151515;
    word-break: keep-all;
  }

  .pd-review-surface-close {
    appearance: none;
    border: 1px solid rgba(21, 21, 21, 0.14);
    border-radius: 999px;
    background: #ffffff;
    width: 42px;
    height: 42px;
    cursor: pointer;
    font-family: var(--font-en);
    font-size: 18px;
    color: #151515;
    transition: background 0.22s ease, box-shadow 0.22s ease;
  }

  .pd-review-surface-close:hover {
    background: rgba(252, 200, 0, 0.1);
    box-shadow: 0 0 24px rgba(252, 200, 0, 0.16);
  }

  .pd-review-surface-summary {
    display: block;
    padding: 28px 0;
    border-bottom: 1px solid rgba(21, 21, 21, 0.1);
  }


  .pd-review-summary-text {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 18px;
    line-height: 1.72;
    letter-spacing: -0.04em;
    color: rgba(21, 21, 21, 0.68);
    word-break: keep-all;
  }

  .pd-review-list {
    display: grid;
    gap: 12px;
    padding-top: 30px;
  }

  .pd-review-card {
    border: 1px solid rgba(21, 21, 21, 0.1);
    border-radius: 24px;
    background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(249,249,246,0.92) 100%);
    padding: 24px;
    box-sizing: border-box;
    box-shadow: 0 16px 46px rgba(21,21,21,0.045);
  }

  .pd-review-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: flex-start;
    gap: 20px;
    margin-bottom: 18px;
  }

  .pd-review-user {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .pd-review-avatar {
    width: 38px;
    height: 38px;
    border-radius: 999px;
    background: #151515;
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--font-ko);
    font-size: 14px;
    line-height: 1;
    font-weight: 700;
  }

  .pd-review-nickname {
    display: block;
    font-family: var(--font-ko);
    font-size: 16px;
    line-height: 1;
    letter-spacing: -0.035em;
    color: #151515;
    font-weight: 700;
  }

  .pd-review-meta {
    display: block;
    margin-top: 7px;
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1;
    letter-spacing: -0.025em;
    color: rgba(21,21,21,0.48);
  }


  .pd-review-card-title {
    margin: 0 0 12px;
    font-family: var(--font-ko);
    font-size: 20px;
    line-height: 1.32;
    letter-spacing: -0.045em;
    color: #151515;
    word-break: keep-all;
  }

  .pd-review-card-body {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1.72;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.64);
    word-break: keep-all;
  }


  .pd-section-label {
    font-family: var(--font-en);
    font-size: clamp(12px, 0.9vw, 14px);
    line-height: 1;
    letter-spacing: 0.18em;
    color: rgba(21, 21, 21, 0.52);
    margin-bottom: 24px;
  }



  .pd-editorial-section {
    width: 1700px;
    padding: 104px 50px 0;
    box-sizing: border-box;
  }

  .pd-editorial-item {
    display: grid;
    grid-template-columns: 940px 1fr;
    gap: 80px;
    align-items: end;
    margin-bottom: 88px;
  }

  .pd-editorial-item:nth-child(even) {
    grid-template-columns: 1fr 940px;
  }

  .pd-editorial-item:nth-child(even) .pd-editorial-image-wrap {
    order: 2;
  }

  .pd-editorial-image-wrap {
    width: 940px;
    height: 610px;
    overflow: hidden;
    border-radius: 24px;
    background: #f5f1e8;
  }

  .pd-editorial-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .pd-editorial-text {
    padding-bottom: 18px;
  }

  .pd-editorial-kicker {
    font-family: var(--font-en);
    font-size: clamp(12px, 0.9vw, 14px);
    line-height: 1;
    letter-spacing: 0.16em;
    color: rgba(21, 21, 21, 0.52);
    margin-bottom: 24px;
  }

  .pd-editorial-title {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 36px;
    line-height: 1.12;
    letter-spacing: -0.055em;
    color: #151515;
    word-break: keep-all;
  }

  .pd-editorial-body {
    margin: 24px 0 0;
    font-family: var(--font-ko);
    font-size: 17px;
    line-height: 1.72;
    letter-spacing: -0.04em;
    color: rgba(21, 21, 21, 0.64);
    word-break: keep-all;
  }

  .pd-notice-section {
    width: 1700px;
    padding: 56px 50px 150px;
    box-sizing: border-box;
  }

  .pd-notice-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    border: 1px solid rgba(21, 21, 21, 0.16);
    border-radius: 24px;
    overflow: hidden;
    background: #ffffff;
  }

  .pd-notice-card {
    min-height: 210px;
    padding: 34px 30px;
    border-right: 1px solid rgba(21, 21, 21, 0.12);
    box-sizing: border-box;
    background: #ffffff;
  }

  .pd-notice-card:last-child {
    border-right: 0;
  }

  .pd-notice-title {
    margin: 0 0 22px;
    font-family: var(--font-ko);
    font-size: 22px;
    line-height: 1;
    letter-spacing: -0.04em;
    color: #151515;
  }

  .pd-notice-body {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1.72;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.64);
    word-break: keep-all;
  }



  .pd-price-text {
    display: inline-flex;
    align-items: baseline;
    gap: 7px;
    white-space: nowrap;
  }

  .pd-price-symbol {
    font-family: var(--font-ko);
    font-size: 0.58em;
    line-height: 1;
    font-weight: 700;
    opacity: 0.72;
    transform: translateY(-0.08em);
  }

  .pd-price-number {
    font-family: inherit;
    font-size: 1em;
    line-height: 1;
    font-weight: inherit;
  }


  .pd-rating-stars {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    font-family: var(--font-en);
    font-size: 15px;
    line-height: 1;
    letter-spacing: 0.02em;
    color: rgba(21, 21, 21, 0.18);
  }

  .pd-rating-stars .is-filled {
    color: #fcc800;
    text-shadow: 0 0 14px rgba(252, 200, 0, 0.22);
  }

  .pd-review-inline-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 28px;
    padding: 0 0 20px;
    border-bottom: 1px solid rgba(21, 21, 21, 0.1);
  }

  .pd-review-inline-title-block {
    display: grid;
    gap: 8px;
  }

  .pd-review-inline-title-block strong {
    font-family: var(--font-ko);
    font-size: 26px;
    line-height: 1;
    letter-spacing: -0.055em;
    color: #151515;
  }

  .pd-review-inline-title-block span {
    font-family: var(--font-ko);
    font-size: 14px;
    line-height: 1;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.56);
  }

  .pd-review-inline-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
    padding-top: 18px;
  }

  .pd-review-inline-card {
    padding: 18px 0 0;
    border-top: 1px solid rgba(21, 21, 21, 0.1);
  }

  .pd-review-inline-head {
    display: grid;
    grid-template-columns: 38px 1fr;
    align-items: center;
    gap: 12px;
    margin-bottom: 14px;
  }

  .pd-review-inline-head strong {
    display: block;
    font-family: var(--font-ko);
    font-size: 16px;
    line-height: 1;
    letter-spacing: -0.04em;
    color: #151515;
    margin-bottom: 7px;
  }

  .pd-review-inline-head div span {
    display: block;
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1;
    letter-spacing: -0.03em;
    color: rgba(21, 21, 21, 0.48);
  }

  .pd-review-inline-card p {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1.72;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.68);
    word-break: keep-all;
  }

  .pd-review-helpful {
    appearance: none;
    border: 0;
    background: transparent;
    padding: 0;
    margin-top: 16px;
    cursor: pointer;
    font-family: var(--font-ko);
    font-size: clamp(12px, 0.9vw, 14px);
    line-height: 1;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.52);
  }

  .pd-review-helpful:hover {
    color: #151515;
  }


  .pd-notice-button-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }

  .pd-notice-button {
    appearance: none;
    border: 1px solid rgba(21, 21, 21, 0.14);
    border-radius: 22px;
    background: #ffffff;
    min-height: 92px;
    padding: 0 28px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-family: var(--font-ko);
    color: #151515;
    transition: background 0.24s ease, border-color 0.24s ease, box-shadow 0.24s ease, transform 0.24s ease;
  }

  .pd-notice-button:hover {
    background: rgba(252, 200, 0, 0.07);
    border-color: rgba(252, 200, 0, 0.52);
    box-shadow: 0 20px 46px rgba(252, 200, 0, 0.12);
    transform: translateY(-1px);
  }

  .pd-notice-button span:first-child {
    font-size: 22px;
    line-height: 1;
    letter-spacing: -0.04em;
    font-weight: 700;
  }

  .pd-notice-button span:last-child {
    font-family: var(--font-en);
    font-size: 12px;
    line-height: 1;
    letter-spacing: 0.14em;
    color: rgba(21, 21, 21, 0.42);
  }


  /*
    WebGL Media Fallback
    ------------------------------------------
    Astro / Three.js ?�용 ???�라?�언???�연??CSS fallback?�다.
    ?�제 WebGL ?�결 ??[data-webgl-media] ?��?지�?Canvas Media ?�?�으�?변?�한??
    body overflow hidden / ScrollSmoother??ProductDetail.tsx가 ?�니??App ?�벨 wrapper?�서 처리?�다.
  */
  body.unotravel-product-detail-webgl {
    /*
      ScrollSmoother / WebGL Canvas ?�결 ???�용?�는 ?�역 ?�래�?
      ?�제 overflow hidden?� App/Layout??smoother wrapper가 준비된 ?�태?�서�?켠다.
    */
  }

  [data-webgl-media-wrap] {
    isolation: isolate;
  }

  [data-webgl-media] {
    will-change: transform, opacity;
  }

  .pd-notice-modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 130;
    background:
      radial-gradient(circle at 50% 12%, rgba(252, 200, 0, 0.14) 0%, rgba(252, 200, 0, 0) 34%),
      rgba(7, 9, 12, 0.42);
    backdrop-filter: blur(18px) saturate(1.18);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 28px;
    box-sizing: border-box;
  }

  .pd-notice-modal {
    width: min(620px, calc(100% - 48px));
    border: 1px solid rgba(255, 255, 255, 0.72);
    border-radius: 34px;
    background:
      linear-gradient(180deg, rgba(255,255,255,0.96) 0%, rgba(244,245,241,0.92) 100%);
    box-shadow:
      0 44px 130px rgba(0, 0, 0, 0.24),
      inset 0 1px 0 rgba(255, 255, 255, 0.95);
    padding: 0;
    box-sizing: border-box;
    overflow: hidden;
  }

  .pd-notice-modal-titlebar {
    height: 54px;
    padding: 0 20px;
    box-sizing: border-box;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(21, 21, 21, 0.08);
    background: rgba(255,255,255,0.42);
  }

  .pd-notice-modal-dots {
    display: inline-flex;
    align-items: center;
    gap: 7px;
  }

  .pd-notice-modal-dots span {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: rgba(21, 21, 21, 0.18);
  }

  .pd-notice-modal-label {
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.16em;
    color: rgba(21,21,21,0.42);
  }

  .pd-notice-modal-content {
    padding: 38px 38px 34px;
    box-sizing: border-box;
  }

  .pd-notice-modal-title {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 34px;
    line-height: 1.1;
    letter-spacing: -0.06em;
    color: #151515;
  }

  .pd-notice-modal-body {
    margin: 24px 0 0;
    padding: 24px;
    border: 1px solid rgba(21,21,21,0.08);
    border-radius: 24px;
    background: rgba(255,255,255,0.56);
    font-family: var(--font-ko);
    font-size: 16px;
    line-height: 1.76;
    letter-spacing: -0.04em;
    color: rgba(21, 21, 21, 0.68);
    word-break: keep-all;
  }

  .pd-notice-modal-confirm {
    appearance: none;
    border: 0;
    border-radius: 999px;
    background: #fcc800;
    width: 100%;
    height: 58px;
    margin-top: 28px;
    cursor: pointer;
    font-family: var(--font-ko);
    font-size: 16px;
    line-height: 1;
    letter-spacing: -0.035em;
    color: #151515;
    font-weight: 800;
    box-shadow: 0 20px 52px rgba(252, 200, 0, 0.28);
  }

  /* Product Detail Premium Editorial Body Renewal */
  .pd-body-section {
    width: 1700px;
    display: grid;
    grid-template-columns: 620px 1fr;
    gap: 70px;
    padding: 96px 50px 0;
    box-sizing: border-box;
    border-top: 1px solid rgba(21, 21, 21, 0.12);
  }

  .pd-body-left {
    min-width: 0;
  }

  .pd-body-sticky {
    position: sticky;
    top: 44px;
    min-height: 860px;
    padding-top: 28px;
    border-top: 1px solid rgba(21, 21, 21, 0.2);
  }

  .pd-body-nav-kicker {
    font-family: var(--font-en);
    font-size: 12px;
    line-height: 1;
    letter-spacing: 0.22em;
    color: rgba(21, 21, 21, 0.45);
    margin-bottom: 28px;
  }

  .pd-body-nav {
    border-top: 1px solid rgba(21, 21, 21, 0.12);
  }

  .pd-body-nav-item {
    appearance: none;
    width: 100%;
    min-height: 68px;
    border: 0;
    border-bottom: 1px solid rgba(21, 21, 21, 0.12);
    background: transparent;
    color: #151515;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0;
    text-align: left;
    transition: opacity 0.24s ease, padding 0.34s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .pd-body-nav-item:not(.is-active) {
    opacity: 0.42;
  }

  .pd-body-nav-item:hover,
  .pd-body-nav-item.is-active {
    opacity: 1;
    padding-left: 10px;
  }

  .pd-body-nav-label {
    font-family: var(--font-ko);
    font-size: 20px;
    line-height: 1;
    letter-spacing: -0.045em;
    font-weight: 560;
  }

  .pd-body-nav-index {
    font-family: var(--font-en);
    font-size: 13px;
    line-height: 1;
    letter-spacing: 0.16em;
    color: rgba(21, 21, 21, 0.42);
  }

  .pd-body-content {
    min-height: 430px;
    padding: 52px 0 46px;
  }

  .pd-body-content-index {
    font-family: var(--font-en);
    font-size: 78px;
    line-height: 0.86;
    letter-spacing: -0.07em;
    color: #151515;
  }

  .pd-body-content-kicker {
    margin-top: 20px;
    font-family: var(--font-ko);
    font-size: 30px;
    line-height: 1;
    letter-spacing: -0.06em;
    font-weight: 560;
    color: #151515;
  }


  .pd-body-review-score {
    margin-top: 36px;
    display: flex;
    align-items: end;
    gap: 10px;
  }

  .pd-body-review-score strong {
    font-family: var(--font-en);
    font-size: 68px;
    line-height: 0.9;
    letter-spacing: -0.06em;
    font-weight: 520;
  }

  .pd-body-review-score span {
    font-family: var(--font-en);
    font-size: 16px;
    color: rgba(21, 21, 21, 0.42);
  }

  .pd-body-review-list,
  .pd-body-course-list,
  .pd-body-check-list,

  .pd-body-review-item,

  .pd-body-review-item span,

  .pd-body-review-item strong,


  .pd-body-course-item {
    display: grid;
    grid-template-columns: 82px 104px 1fr;
    gap: 22px;
    padding: 24px 0;
    border-top: 1px solid rgba(21, 21, 21, 0.14);
  }

  .pd-body-course-number {
    font-family: var(--font-en);
    font-size: 12px;
    line-height: 1;
    letter-spacing: 0.18em;
    color: rgba(21, 21, 21, 0.42);
  }

  .pd-body-course-meta {
    display: flex;
    flex-direction: column;
    gap: 10px;
    font-family: var(--font-en);
    font-size: 12px;
    line-height: 1;
    letter-spacing: 0.12em;
    color: rgba(21, 21, 21, 0.48);
    text-transform: uppercase;
  }

  .pd-body-course-copy strong {
    display: block;
    font-family: var(--font-ko);
    font-size: 20px;
    line-height: 1.34;
    letter-spacing: -0.05em;
    font-weight: 560;
    color: #151515;
    word-break: keep-all;
  }

  .pd-body-course-copy p {
    margin-top: 12px;
    max-width: 430px;
    font-size: 15px;
    line-height: 1.68;
    color: rgba(21, 21, 21, 0.62);
  }


  .pd-body-meeting-map {
    width: 100%;
    margin-top: 22px;
    border-top: 1px solid rgba(21, 21, 21, 0.12);
    padding-top: 18px;
  }

  .pd-body-meeting-map iframe {
    width: 100%;
    height: 250px;
    border: 0;
    display: block;
    filter: grayscale(1) contrast(0.94);
    background: #f2f2f2;
  }

  .pd-body-right {
    min-width: 0;
    padding-top: 28px;
  }

  .pd-body-image-card,
  .pd-body-image-story,
  .pd-body-map-panel {
    margin: 0 0 70px;
  }

  .pd-body-image-card.is-lead {
    border-top: 1px solid rgba(21, 21, 21, 0.2);
    padding-top: 28px;
  }

  .pd-body-image-card.is-lead .pd-body-image {
    width: 100%;
    aspect-ratio: 16 / 9;
    object-fit: cover;
    display: block;
  }

  .pd-body-image-card figcaption {
    display: flex;
    justify-content: space-between;
    gap: 30px;
    margin-top: 16px;
  }

  .pd-body-image-card figcaption strong {
    font-family: var(--font-en);
    font-size: 12px;
    line-height: 1;
    letter-spacing: 0.14em;
    font-weight: 520;
  }

  .pd-body-image-story {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 34px;
    align-items: end;
  }

  .pd-body-image-story.is-offset {
    grid-template-columns: 300px 1fr;
  }

  .pd-body-image-story.is-offset .pd-body-image-wrap {
    order: 2;
  }

  .pd-body-image-wrap {
    overflow: hidden;
    background: #f4f2ed;
  }

  .pd-body-image-story .pd-body-image {
    width: 100%;
    height: 430px;
    object-fit: cover;
    display: block;
  }

  .pd-body-image-caption {
    padding-bottom: 12px;
  }

  .pd-body-image-caption h2 {
    margin: 16px 0 0;
    font-family: var(--font-ko);
    font-size: 26px;
    line-height: 1.18;
    letter-spacing: -0.06em;
    font-weight: 560;
    color: #151515;
    word-break: keep-all;
  }

  .pd-body-image-caption p {
    margin-top: 16px;
    font-size: 15px;
    line-height: 1.68;
  }

  .pd-body-map-panel {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 34px;
    padding-top: 34px;
    border-top: 1px solid rgba(21, 21, 21, 0.14);
  }

  .pd-body-map-panel iframe {
    width: 100%;
    height: 360px;
    border: 0;
    filter: grayscale(1) contrast(0.94);
    background: #f2f2f2;
  }

  .pd-editorial-section,
  .pd-notice-section {
    display: none;
  }


  /* ==========================================================
     V10 Layout Correction
     ----------------------------------------------------------
     - Booking Module is a sticky top-level module, not a body card.
     - Product Document becomes a full-width one-line index.
     - Body images move to the left; text moves to the right.
     - Calendar avoids yellow overuse and shows without clipping.
  ========================================================== */

  .pd-hero {
    padding-top: 96px;
  }

  .pd-body-section.is-empty {
    min-height: 420px;
    padding: 0;
    border-top: 0;
    border-bottom: 0;
  }

  .pd-body-section {
    position: relative;
    width: 1600px;
    margin: 0 50px 126px;
    padding: 108px 0 0;
    border-top: 1px solid rgba(21, 21, 21, 0.14);
    display: block;
    overflow: visible;
    isolation: isolate;
  }

  .pd-body-right {
    position: relative;
    z-index: 1;
    display: grid;
    gap: 46px;
    min-width: 0;
    padding-right: 0;
  }

  .pd-body-image-story {
    width: 100%;
    min-height: 520px;
    margin: 0;
    background: #ffffff;
    border-top: 1px solid rgba(21, 21, 21, 0.12);
    display: block;
    pointer-events: none;
  }

  .pd-body-image-story.is-offset {
    transform: none;
  }

  .pd-body-image-wrap {
    width: 100%;
    height: 520px;
    overflow: hidden;
    background: #ffffff;
  }

  .pd-body-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    pointer-events: none;
  }

  /* Final Detail Body / Inline Sticky Booking Cleanup */
  .pd-body-section {
    width: 1700px;
    max-width: 100%;
    margin: 0 auto 96px;
    padding: 90px 50px 110px;
    box-sizing: border-box;
    display: block;
    background: #ffffff;
  }

  .pd-body-layout {
    width: 100%;
    max-width: 1480px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 80px;
    align-items: start;
    justify-items: center;
  }

  .pd-body-image-list {
    width: 100%;
    max-width: 960px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
  }

  .pd-body-html {
    width: 100%;
    max-width: 960px;
    min-width: 0;
    overflow: hidden;
    color: #2a241f;
    font-size: 16px;
    line-height: 1.75;
    word-break: keep-all;
  }

  .pd-body-html:not(:last-child) {
    margin-bottom: 36px;
  }

  .pd-body-html :where(img, iframe, video) {
    max-width: 100%;
  }

  .pd-body-html :where(img) {
    height: auto;
    display: block;
  }

  .pd-body-html :where(table) {
    max-width: 100%;
    border-collapse: collapse;
  }

  .pd-body-html :where(p, ul, ol, table, blockquote) {
    margin: 0 0 18px;
  }

  .pd-body-image-frame {
    width: 100%;
    margin: 0 auto;
    overflow: visible;
    background: transparent;
    display: flex;
    justify-content: center;
  }

  .pd-body-image-frame .pd-body-image {
    width: auto;
    max-width: 100%;
    height: auto;
    display: block;
    object-fit: initial;
    object-position: center;
  }


  .pd-booking-aside {
    position: relative;
    min-width: 0;
    align-self: stretch;
    height: auto;
    overflow: visible;
  }

  .pd-booking-aside-sticky {
    position: sticky;
    top: 126px;
    width: 100%;
    z-index: 3;
  }

  .pd-booking-aside-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
    padding-top: 18px;
    border-top: 1px solid rgba(21, 21, 21, 0.2);
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.18em;
    color: rgba(21, 21, 21, 0.48);
    text-transform: uppercase;
  }

  .pd-booking-aside-label::after {
    content: "";
    width: 44px;
    height: 1px;
    background: rgba(21, 21, 21, 0.2);
  }

  .pd-body-layout.is-image-with-booking {
    grid-template-columns: minmax(0, 1fr);
  }

  .pd-body-layout.is-image-with-booking .pd-booking-aside {
    display: none;
  }

  .pd-detail-faq-section {
    width: 1700px;
    max-width: 100%;
    margin: 0 auto;
    padding: 0 50px 120px;
    box-sizing: border-box;
    background: #ffffff;
  }

  .pd-detail-faq-inner {
    width: 100%;
    max-width: 1480px;
    margin: 0 auto;
    border-top: 1px solid rgba(21, 21, 21, 0.16);
    padding-top: 44px;
  }

  .pd-detail-faq-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 26px;
  }

  .pd-detail-faq-title {
    margin: 0;
    font-family: var(--font-en);
    font-size: 48px;
    line-height: 0.95;
    letter-spacing: 0;
    font-weight: 520;
    color: #151515;
  }

  .pd-detail-faq-title {
    font-size: 0;
  }

  .pd-detail-faq-title::before {
    content: "자주 묻는 FAQ";
    font-family: var(--font-ko);
    font-size: 48px;
    line-height: 0.95;
    letter-spacing: 0;
    font-weight: 520;
  }

  .pd-detail-faq-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    border: 1px solid rgba(21, 21, 21, 0.18);
    border-radius: 999px;
    padding: 0 18px;
    color: #151515;
    text-decoration: none;
    font-family: var(--font-ko);
    font-size: 13px;
    font-weight: 800;
    letter-spacing: -0.02em;
  }

  .pd-detail-faq-action {
    font-size: 0;
  }

  .pd-detail-faq-action::before {
    content: "1:1 상담하기";
    font-size: 13px;
    font-weight: 800;
  }

  .pd-detail-faq-list {
    display: grid;
    gap: 0;
    border-top: 1px solid rgba(21, 21, 21, 0.1);
  }

  .pd-detail-faq-item {
    display: grid;
    grid-template-columns: 220px minmax(0, 1fr);
    gap: 34px;
    padding: 24px 0;
    border-bottom: 1px solid rgba(21, 21, 21, 0.1);
  }

  .pd-detail-faq-item > div {
    min-width: 0;
    overflow: hidden;
  }

  .pd-detail-faq-meta {
    font-family: var(--font-en);
    font-size: 11px;
    line-height: 1;
    letter-spacing: 0.14em;
    color: rgba(21, 21, 21, 0.46);
    text-transform: uppercase;
  }

  .pd-detail-faq-question {
    margin: 0;
    font-family: var(--font-ko);
    font-size: 20px;
    line-height: 1.36;
    letter-spacing: -0.045em;
    color: #151515;
    word-break: keep-all;
    overflow-wrap: anywhere;
  }

  .pd-detail-faq-answer {
    margin: 10px 0 0;
    width: 100%;
    max-width: 840px;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1.76;
    letter-spacing: -0.035em;
    color: rgba(21, 21, 21, 0.64);
    word-break: keep-all;
    overflow-wrap: anywhere;
  }

  .pd-detail-faq-answer :where(*) {
    max-width: 100%;
    white-space: normal;
    overflow-wrap: anywhere;
  }

  .pd-detail-faq-answer :where(p, ul, ol, table, blockquote) {
    margin-top: 0;
    margin-bottom: 12px;
  }

  .pd-detail-faq-answer :where(img) {
    max-width: 100%;
    height: auto;
  }

  /* Body booking CSS moved to ReservationModule.tsx. */

`;

function legacyHtmlToText(value?: string) {
  const raw = value?.trim() ?? "";
  if (!raw) return "";

  const withBreaks = raw
    .replace(/<br\s*\/?>/gi, "\n")
    .replace(/<\/p>/gi, "\n")
    .replace(/<\/li>/gi, "\n");

  if (typeof window !== "undefined" && window.DOMParser) {
    const doc = new window.DOMParser().parseFromString(withBreaks, "text/html");
    return (doc.body.textContent ?? "")
      .replace(/\u00a0/g, " ")
      .replace(/[ \t]+\n/g, "\n")
      .replace(/\n{3,}/g, "\n\n")
      .trim();
  }

  return withBreaks
    .replace(/<[^>]+>/g, " ")
    .replace(/&nbsp;/gi, " ")
    .replace(/&amp;/gi, "&")
    .replace(/&lt;/gi, "<")
    .replace(/&gt;/gi, ">")
    .replace(/\s+/g, " ")
    .trim();
}

export default function ProductDetail({
  products = [],
}: ProductDetailProps = {}) {
  const { shellRef, scale } = useProductDetailScale();

  useEffect(() => {
    /*
      WebGL / ScrollSmoother Page Flag
      ------------------------------------------
      Astro ?�퍼?�스??body overflow hidden / ?�체 canvas 방식?�
      ProductDetail ?�독 CSS가 ?�니??App/Layout ?�벨?�서 ?�작?�야 ?�다.
      ?�기?�는 ?�세?�이지 진입 ??body???�래�??�래?�만 부?�해??
      ?�역 WebGL Canvas / ScrollSmoother가 ???�이지�?감�??????�게 ?�다.
    */
    document.body.classList.add("unotravel-product-detail-webgl");

    return () => {
      document.body.classList.remove("unotravel-product-detail-webgl");
    };
  }, []);

  /*
    Product Detail Route Split
    ------------------------------------------
    App.tsx??/product/detail/... 경로?�서 ProductDetail ?�나�??�더링한??
    ?�라???�세?�이지 ?��??�서 ?�재 pathname / product id�?기�??�로
    ?��??�키지?� ?�일리투???�이?��? 분기?�다.

    VIEWED Panel / Related Product / ProductList?�서 SPA ?�동?�도
    ProductDetail 컴포?�트가 그�?�??��??????�으므�? ?�재 pathname??state�?관리한??
    history.pushState ?�후 발생?�는 unotravel:navigate ?�벤?��? 구독?�서
    ?�로고침 ?�이 ?�재 ?�품 ?�이?��? ?�시 계산?�다.

    ?�제 백엔???�동 ?�에????분기 ?�??productId�?API�?조회?�고,
    ?�답??category �?semi / daily)???�라 문서??UI ?�는 캘린?�형 UI�??�출?�다.
  */
  const [currentPathname, setCurrentPathname] = useState(() => {
    if (typeof window === "undefined") return "";
    return window.location.pathname;
  });

  useEffect(() => {
    if (typeof window === "undefined") return;

    const syncPathname = () => {
      setCurrentPathname(window.location.pathname);
    };

    window.addEventListener("popstate", syncPathname);
    window.addEventListener("unotravel:navigate", syncPathname);

    return () => {
      window.removeEventListener("popstate", syncPathname);
      window.removeEventListener("unotravel:navigate", syncPathname);
    };
  }, []);

  const [remoteProductDetail, setRemoteProductDetail] =
    useState<ProductDetailResponse | null>(null);
  const [remoteAvailability, setRemoteAvailability] =
    useState<ProductAvailabilityResponse | null>(null);
  const [isRemoteProductDetailLoading, setIsRemoteProductDetailLoading] =
    useState(false);

  useEffect(() => {
    if (!currentPathname.startsWith("/product/detail/")) {
      setRemoteProductDetail(null);
      setRemoteAvailability(null);
      setIsRemoteProductDetailLoading(false);
      return;
    }

    const productId = decodeURIComponent(
      currentPathname.split("/").filter(Boolean).at(-1) ?? "",
    );

    if (!productId) {
      setRemoteProductDetail(null);
      setRemoteAvailability(null);
      setIsRemoteProductDetailLoading(false);
      return;
    }

    let isCancelled = false;
    setIsRemoteProductDetailLoading(true);
    setRemoteAvailability(null);

    getProductDetail(productId, { mode: "full" })
      .then((detail) => {
        if (!isCancelled) {
          setRemoteProductDetail(detail);
          setIsRemoteProductDetailLoading(false);
        }
      })
      .catch(() => {
        if (!isCancelled) {
          setRemoteProductDetail(null);
          setIsRemoteProductDetailLoading(false);
        }
      });

    getProductAvailability(productId, getAvailabilityQueryRange())
      .then((availability) => {
        if (!isCancelled) {
          setRemoteAvailability(availability);
        }
      })
      .catch(() => {
        if (!isCancelled) {
          setRemoteAvailability(null);
        }
      });

    return () => {
      isCancelled = true;
    };
  }, [currentPathname]);

  const DETAIL_DATA = useMemo(() => {
    const pathname = currentPathname;
    const productId = decodeURIComponent(
      pathname.split("/").filter(Boolean).at(-1) ?? "",
    );
    const currentListProduct = products.find(
      (product) => product.id === productId,
    );
    const productListType = currentListProduct
      ? getProductListItemType(currentListProduct)
      : undefined;
    const isDailyDetail =
      productListType === "daily" ||
      pathname.includes("/product/detail/daily/") ||
      productId.includes("daily") ||
      productId.includes("rome-") ||
      productId.includes("firenze-") ||
      productId.includes("venezia-") ||
      productId.includes("napoli-") ||
      productId.includes("milano-") ||
      productId.includes("amalfi-");

    const baseDetailData = isDailyDetail ? DAILY_DETAIL_DATA : SEMI_DETAIL_DATA;
    const remoteBasePrice = remoteProductDetail?.price?.deposit;
    const remoteThumbnailUrl =
      remoteProductDetail?.heroImageUrl || remoteProductDetail?.thumbnailUrl;
    const remoteDetailHtml = remoteProductDetail?.detailHtml?.trim() ?? "";
    const remoteHeroImages = (remoteProductDetail?.heroImages ?? [])
      .filter((image) => image.url)
      .map((image, index) => ({
        src: image.url,
        kicker: image.source || `HERO ${String(index + 1).padStart(2, "0")}`,
        title: image.source || `HERO ${String(index + 1).padStart(2, "0")}`,
      }));
    const remoteBodyImages = (remoteProductDetail?.bodyImages ?? remoteProductDetail?.detailImages ?? [])
      .filter((image) => image.url)
      .map((image, index) => ({
        src: image.url,
        kicker: image.source || `BODY ${String(index + 1).padStart(2, "0")}`,
        title: image.source || `BODY ${String(index + 1).padStart(2, "0")}`,
      }));
    const remoteTourCourseImages = (remoteProductDetail?.tourCourseImages ?? [])
      .filter((image) => image.url)
      .map((image, index) => ({
        src: image.url,
        source: image.source,
        title: image.source || `TOUR COURSE ${String(index + 1).padStart(2, "0")}`,
      }));
    const remoteTourAdImages = (remoteProductDetail?.tourAdImages ?? [])
      .filter((image) => image.url)
      .map((image, index) => ({
        src: image.url,
        source: image.source,
        title: image.source || `FEATURE ${String(index + 1).padStart(2, "0")}`,
      }));
    const remoteTourBannerImages = (remoteProductDetail?.tourBannerImages ?? [])
      .filter((image) => image.url)
      .map((image, index) => ({
        src: image.url,
        source: image.source,
        title: image.source || `BANNER ${String(index + 1).padStart(2, "0")}`,
      }));
    const remoteTourInfoImages = (remoteProductDetail?.tourInfoImages ?? [])
      .filter((image) => image.url)
      .map((image, index) => ({
        src: image.url,
        kicker: image.source || `TOUR INFO ${String(index + 1).padStart(2, "0")}`,
        title: image.source || `TOUR INFO ${String(index + 1).padStart(2, "0")}`,
      }));
    const remoteTourOptions = remoteProductDetail?.tourOptions;
    const remoteTourDay = legacyHtmlToText(remoteTourOptions?.tourDay);
    const remoteTourTime = legacyHtmlToText(remoteTourOptions?.tourTime);
    const remoteIncluded = legacyHtmlToText(remoteTourOptions?.includes);
    const remoteExcluded = legacyHtmlToText(remoteTourOptions?.excludes);
    const remoteMeeting = legacyHtmlToText(remoteTourOptions?.meeting);
    const remoteMeetingTime = legacyHtmlToText(remoteTourOptions?.meetingTime);
    const remoteBeforeNotice = legacyHtmlToText(remoteTourOptions?.beforeNotice);
    const remotePreparation = legacyHtmlToText(remoteTourOptions?.preparation);
    const remoteCancelRules = legacyHtmlToText(remoteTourOptions?.cancelRules);
    const remoteGuides = (remoteProductDetail?.guides ?? []).filter(
      (guide) => guide.name || guide.bodyText,
    );
    const remoteGuideLabel = remoteGuides.map((guide) => guide.name).filter(Boolean).join(", ");
    const remoteGuideText =
      remoteGuides.length > 0
        ? "이 상품은 아래 담당 가이드가 현장 안내를 진행합니다."
        : "";
    const remoteDefaultFee = remoteProductDetail?.feeOptions?.find((item) => item.isDefault) ??
      remoteProductDetail?.feeOptions?.[0];
    const remoteAvailabilityDates: AvailableDate[] = (remoteAvailability?.dates ?? []).map(
      (date) => {
        const schedule = remoteProductDetail?.packageSchedules?.find(
          (item) =>
            String(item.id) === String(date.legacyPackageScheduleId) ||
            item.startDate === date.date,
        );
        const parsedDate = parseDateId(date.date);
        const price = schedule?.totalPrice ?? schedule?.deposit ?? 0;
        const capacity =
          schedule?.seat ||
          date.remainingSeats ||
          baseDetailData.availableDates[0]?.capacity ||
          12;

        return {
          id: date.date,
          legacyFeeOptionId: remoteDefaultFee?.id,
          legacyPackageScheduleId: schedule?.id ?? date.legacyPackageScheduleId,
          label: date.date.replaceAll("-", "."),
          day: getWeekdayKo(parsedDate),
          seats: date.remainingSeats ?? capacity,
          capacity,
          price,
          deposit: schedule?.deposit,
          intermediatePayment: schedule?.intermediatePayment ?? schedule?.middlePayment,
          middlePayment: schedule?.middlePayment ?? schedule?.intermediatePayment,
          balance: schedule?.balance ?? schedule?.finalPayment,
          finalPayment: schedule?.finalPayment ?? schedule?.balance,
          airfare: schedule?.airfare,
          totalPrice: schedule?.totalPrice,
          status: getAvailabilityDisplayLabel(date.status),
          guide: "UNO GUIDE",
        };
      },
    );
    const remoteNotices = [
      remoteBeforeNotice ? { title: "여행 전 안내", body: remoteBeforeNotice } : null,
      remotePreparation ? { title: "준비물", body: remotePreparation } : null,
      remoteCancelRules ? { title: "취소 및 환불", body: remoteCancelRules } : null,
    ].filter((notice): notice is DetailNotice => Boolean(notice));
    const legacyContentOverrides = {
      guide:
        remoteGuideText ||
        [remoteTourDay, remoteTourTime].filter(Boolean).join("\n") ||
        baseDetailData.guide,
      guides: remoteGuides,
      selectedGuide: remoteGuideLabel || "UNO GUIDE",
      included: remoteIncluded || baseDetailData.included,
      excluded: remoteExcluded || baseDetailData.excluded,
      reservationNotice:
        remoteBeforeNotice ||
        remotePreparation ||
        remoteCancelRules ||
        baseDetailData.reservationNotice,
      notices: remoteNotices.length > 0 ? remoteNotices : baseDetailData.notices,
      meetingPoint: {
        ...baseDetailData.meetingPoint,
        name: remoteMeeting ? "미팅 장소" : baseDetailData.meetingPoint.name,
        address: remoteMeeting || baseDetailData.meetingPoint.address,
        time: remoteMeetingTime || baseDetailData.meetingPoint.time,
        mapUrl: remoteTourOptions?.map || baseDetailData.meetingPoint.mapUrl,
        directionUrl:
          remoteTourOptions?.map ||
          baseDetailData.meetingPoint.directionUrl,
      },
    };
    const sourceProduct = currentListProduct;
    const remoteOrListTitle =
      remoteProductDetail?.title ?? currentListProduct?.title ?? "";

    if (!sourceProduct || productId === baseDetailData.id) {
      return {
        ...baseDetailData,
        id: productId || baseDetailData.id,
        legacyPackageScheduleId:
          remoteProductDetail?.packageSchedules?.find((item) => item.isDefault)?.id ??
          getLegacyPackageScheduleId(productId) ??
          baseDetailData.legacyPackageScheduleId,
        href: pathname || baseDetailData.href,
        productType: remoteProductDetail?.productType ?? baseDetailData.productType,
        title: remoteOrListTitle,
        titleEn: "",
        eyebrow: "",
        region: "",
        duration: "",
        legacyProductId:
          remoteProductDetail?.legacyProductId ??
          getLegacyProductId(productId) ??
          baseDetailData.legacyProductId,
        legacyFeeOptionId:
          remoteDefaultFee?.id ??
          remoteProductDetail?.legacyFeeOptionId ??
          getLegacyFeeOptionId(productId) ??
          baseDetailData.legacyFeeOptionId,
        basePrice: remoteBasePrice ?? 0,
        originalPrice: remoteProductDetail?.originalPrice,
        priceDescription: remoteProductDetail?.priceDescription,
        feeOptions: remoteProductDetail?.feeOptions ?? [],
        heroImage:
          remoteThumbnailUrl ||
          (isRemoteProductDetailLoading || !remoteProductDetail ? "" : baseDetailData.heroImage),
        heroImages:
          remoteHeroImages.length > 0
            ? remoteHeroImages
            : isRemoteProductDetailLoading || !remoteProductDetail
              ? []
              : [],
        detailImages: remoteBodyImages.length > 0 ? remoteBodyImages : [],
        availableDates:
          remoteAvailabilityDates.length > 0
            ? remoteAvailabilityDates
            : baseDetailData.availableDates,
        dailyAvailableDates:
          remoteAvailabilityDates.length > 0
            ? remoteAvailabilityDates
            : baseDetailData.dailyAvailableDates,
        detailHtml: remoteDetailHtml,
        hasRemoteDetailImages: remoteBodyImages.length > 0,
        courseImages: remoteTourCourseImages,
        featureImages: [...remoteTourAdImages, ...remoteTourBannerImages],
        remoteImages: remoteProductDetail?.images ?? [],
        faqs: remoteProductDetail?.faqs ?? baseDetailData.faqs,
        ...legacyContentOverrides,
      };
    }

    const sourceRegion =
      "region" in sourceProduct ? sourceProduct.region : undefined;
    const sourceBasePrice =
      "basePrice" in sourceProduct ? sourceProduct.basePrice : undefined;
    const sourceThumbnail =
      "thumbnail" in sourceProduct ? sourceProduct.thumbnail : undefined;

    return {
      ...baseDetailData,
      id: sourceProduct.id,
      legacyProductId:
        remoteProductDetail?.legacyProductId ??
        sourceProduct.legacyProductId ??
        getLegacyProductId(sourceProduct.id) ??
        baseDetailData.legacyProductId,
      legacyFeeOptionId:
        remoteDefaultFee?.id ??
        remoteProductDetail?.legacyFeeOptionId ??
        sourceProduct.legacyFeeOptionId ??
        getLegacyFeeOptionId(sourceProduct.id) ??
        baseDetailData.legacyFeeOptionId,
      legacyPackageScheduleId:
        remoteProductDetail?.packageSchedules?.find((item) => item.isDefault)?.id ??
        sourceProduct.legacyPackageScheduleId ??
        getLegacyPackageScheduleId(sourceProduct.id) ??
        baseDetailData.legacyPackageScheduleId,
      href: sourceProduct.href ?? pathname ?? baseDetailData.href,
      productType:
        remoteProductDetail?.productType ?? productListType ?? baseDetailData.productType,
      eyebrow: sourceProduct.eyebrow ?? sourceRegion ?? baseDetailData.eyebrow,
      title: remoteProductDetail?.title ?? sourceProduct.title,
      titleEn: "",
      region: sourceRegion ?? baseDetailData.region,
      duration: sourceProduct.duration ?? baseDetailData.duration,
      basePrice:
        remoteBasePrice ??
        sourceProduct.price ??
        sourceBasePrice ??
        (isRemoteProductDetailLoading || !remoteProductDetail ? 0 : baseDetailData.basePrice),
      originalPrice: remoteProductDetail?.originalPrice,
      priceDescription: remoteProductDetail?.priceDescription,
      feeOptions: remoteProductDetail?.feeOptions ?? [],
      heroImage:
        remoteThumbnailUrl ||
        (isRemoteProductDetailLoading || !remoteProductDetail ? "" : (
        sourceProduct.image ||
        sourceThumbnail ||
        baseDetailData.heroImage
        )),
      heroImages:
        remoteHeroImages.length > 0
          ? remoteHeroImages
          : isRemoteProductDetailLoading || !remoteProductDetail
            ? []
            : [],
      detailImages: remoteBodyImages.length > 0 ? remoteBodyImages : [],
      availableDates:
        remoteAvailabilityDates.length > 0
          ? remoteAvailabilityDates
          : baseDetailData.availableDates,
      dailyAvailableDates:
        remoteAvailabilityDates.length > 0
          ? remoteAvailabilityDates
          : baseDetailData.dailyAvailableDates,
      detailHtml: remoteDetailHtml,
      hasRemoteDetailImages: remoteBodyImages.length > 0,
      courseImages: remoteTourCourseImages,
      featureImages: [...remoteTourAdImages, ...remoteTourBannerImages],
      remoteImages: remoteProductDetail?.images ?? [],
      faqs: remoteProductDetail?.faqs ?? baseDetailData.faqs,
      ...legacyContentOverrides,
    };
  }, [currentPathname, products, remoteProductDetail, remoteAvailability, isRemoteProductDetailLoading]);
  /*
    Reservation Source
    ------------------------------------------
    ProductDetail?� ?�품 ?�?�에 맞는 ?�약 가???�정 배열�?ReservationModule�??�달?�다.
    ?�짜 ?�택 / ?�원 ?�택 / ?�약 payload ?�성 / ?�바구니 ?�?��? ReservationModule ?��??�서 처리?�다.

    백엔???�동 ???�품 ?�세 API?�서 ?�려주는 productType�??�약 가???�정 배열??
    ??구조??맞춰 매핑?�다.
  */
  const availableDateSource =
    DETAIL_DATA.productType === "daily"
      ? DETAIL_DATA.dailyAvailableDates
      : DETAIL_DATA.availableDates;

  /*
    Review Page Surface
    ------------------------------------------
    리뷰 ??? ?�세?�이지 ?�에???�약�?보여주고,
    ?�체 리뷰??차후 ?�작??리뷰 ?�이지/리뷰 컴포?�트�?
    Modal Surface ?�태�??�어 ?�약 ?�름???�기지 ?�게 ?�다.

    ?�제 백엔???�동 ??review list / review summary / review rating ?�이?��? ?�결?�다.
  */
  const [isReviewSurfaceOpen, setIsReviewSurfaceOpen] = useState(false);
  const [activeNotice, setActiveNotice] = useState<DetailNotice | null>(null);
  const isDailyTour = DETAIL_DATA.productType === "daily";
  const [heroRotationIndex, setHeroRotationIndex] = useState(0);
  const heroRotationImages = useMemo(() => {
    const images = [
      DETAIL_DATA.heroImage
        ? {
            src: DETAIL_DATA.heroImage,
            kicker: "MAIN",
            title: DETAIL_DATA.title,
          }
        : null,
      ...DETAIL_DATA.heroImages,
    ].filter((image): image is { src: string; kicker: string; title: string } => {
      return Boolean(image && image.src);
    });

    const seen = new Set<string>();
    return images.filter((image) => {
      if (seen.has(image.src)) return false;
      seen.add(image.src);
      return true;
    });
  }, [DETAIL_DATA.heroImage, DETAIL_DATA.heroImages, DETAIL_DATA.title]);
  const shouldRotateHero = heroRotationImages.length >= 3;
  const rotatedHeroImages = useMemo(() => {
    if (!shouldRotateHero) {
      return heroRotationImages;
    }

    return heroRotationImages.map((_, index) => {
      return heroRotationImages[(heroRotationIndex + index) % heroRotationImages.length];
    });
  }, [heroRotationImages, heroRotationIndex, shouldRotateHero]);
  const primaryHeroImage = rotatedHeroImages[0]?.src ?? "";
  const secondaryHeroImages = rotatedHeroImages.slice(1, 3);

  useEffect(() => {
    if (!shouldRotateHero) {
      setHeroRotationIndex(0);
      return;
    }

    const timer = window.setInterval(() => {
      setHeroRotationIndex((index) => (index + 1) % heroRotationImages.length);
    }, 3000);

    return () => {
      window.clearInterval(timer);
    };
  }, [heroRotationImages.length, shouldRotateHero]);
  /*
    Recently Viewed
    ------------------------------------------
    Header??VIEWED???�역 기능?��?�??�세?�이지 진입 ???�재 ?�품 ?�보�??�?�한??
    ?�??중복 ?�거/최�? 5�??��?/sessionStorage key 관리는 recentlyViewed ?�틸?�서 처리?�다.
  */
  useEffect(() => {
    if (!currentPathname.startsWith("/product/detail/")) return;

    saveRecentlyViewedProduct({
      id: DETAIL_DATA.id,
      title: DETAIL_DATA.title,
      thumbnail: DETAIL_DATA.heroImage,
      productType: DETAIL_DATA.productType,
      country: DETAIL_DATA.region,
      duration: DETAIL_DATA.duration,
      price: DETAIL_DATA.basePrice,
      href: DETAIL_DATA.href,
    });
  }, [DETAIL_DATA, currentPathname]);


  const productListRelatedProducts = useMemo(
    () =>
      normalizeProductListRelatedProducts(
        products,
        DETAIL_DATA.productType,
        DETAIL_DATA.id,
        imgDetailA,
      ),
    [products, DETAIL_DATA.productType, DETAIL_DATA.id],
  );

  const relatedProducts = productListRelatedProducts;
  const relatedSectionLabel = isDailyTour
    ? "RELATED DAILY TOURS"
    : "RELATED SEMI PACKAGES";


  const handleRelatedProductClick = (
    event: MouseEvent<HTMLAnchorElement>,
    href: string,
  ) => {
    event.preventDefault();
    window.history.pushState({}, "", href);
    window.dispatchEvent(new PopStateEvent("popstate"));
    window.dispatchEvent(new Event("unotravel:navigate"));
    window.scrollTo({ top: 0, left: 0, behavior: "auto" });
  };


  return (
    <>
      <section
        ref={shellRef}
        className="pd-shell"
        aria-label={`${DETAIL_DATA.title} ?�품 ?�세?�이지`}
      >
        <style>{STYLE}</style>
      <div className="pd-canvas" style={{ zoom: scale }}>
        {/* Detail Hero */}
        <header className="pd-hero">
          <div className="pd-hero-editorial">
            <div className="pd-hero-copy">
              <div className="pd-eyebrow">{DETAIL_DATA.eyebrow}</div>
              <h1 className="pd-title">{DETAIL_DATA.title}</h1>
              <div className="pd-title-en">{DETAIL_DATA.titleEn}</div>

              <p className="pd-hero-lead">
                {isDailyTour
                  ? "?��??�서 바로 ?�류?�는 ?�일리투?�입?�다. ?�짜, ?�여?? 미팅 ?�보�?먼�? ?�인?????�약???�정?�니??"
                  : "?�시???�동, ??�� ?�정, ?��? ?�선???�나???�행 문서처럼 ?�리???�리미엄 ?��??�키지?�니??"}
              </p>

              {DETAIL_DATA.basePrice > 0 ? (
                <div className="pd-hero-price-summary" aria-label="?�품 ?�작가">
                  <span>예약금</span>
                  <strong>
                    <PriceText price={DETAIL_DATA.basePrice} currency={DETAIL_DATA.currency} />
                  </strong>
                  {DETAIL_DATA.originalPrice ? (
                    <small>
                      정상가격 {Number(DETAIL_DATA.originalPrice).toLocaleString("ko-KR")}원
                    </small>
                  ) : null}
                  {DETAIL_DATA.priceDescription ? <p>{DETAIL_DATA.priceDescription}</p> : null}
                </div>
              ) : null}

            </div>

            <div className="pd-hero-visual" aria-label="상품 이미지 갤러리">
              <figure className="pd-hero-media-card is-large">
                {primaryHeroImage ? (
                  <img
                    className="pd-hero-image"
                    src={primaryHeroImage}
                    alt=""
                  />
                ) : null}
              </figure>

              <div className="pd-hero-media-grid">
                {secondaryHeroImages.map((image) => (
                  <figure
                    key={image.kicker}
                    className="pd-hero-media-card is-small"
                  >
                    <img className="pd-hero-image" src={image.src} alt="" />
                  </figure>
                ))}
              </div>
            </div>
          </div>
        </header>

        {/* Reservation Module Entry */}
        <ReservationModule
          product={{
            id: DETAIL_DATA.id,
            legacyProductId: DETAIL_DATA.legacyProductId,
            legacyFeeOptionId: DETAIL_DATA.legacyFeeOptionId,
            legacyPackageScheduleId: DETAIL_DATA.legacyPackageScheduleId,
            productType: DETAIL_DATA.productType,
            title: DETAIL_DATA.title,
            href: DETAIL_DATA.href,
            currency: DETAIL_DATA.currency,
            basePrice: DETAIL_DATA.basePrice,
            feeOptions: DETAIL_DATA.feeOptions,
            ticket: DETAIL_DATA.ticket,
          }}
          dates={availableDateSource}
        />


        <ProductDocument
          detailData={DETAIL_DATA}
          selectedGuide={DETAIL_DATA.selectedGuide}
          isDailyTour={isDailyTour}
          onOpenReview={() => setIsReviewSurfaceOpen(true)}
          onOpenNotice={setActiveNotice}
        />

        {/* Detail Body Image Area */}
        <section
          className="pd-body-section"
          aria-label="?�품 ?�세 ?��?지 ?�역"
          data-webgl-section="detail-body"
        >
          <div className="pd-body-layout is-image-with-booking">
            <div className="pd-body-image-list">
              {DETAIL_DATA.hasRemoteDetailImages
                ? DETAIL_DATA.detailImages.map((item) => (
                    <figure
                      key={`body-${item.title}`}
                      className="pd-body-image-frame"
                      data-webgl-media-wrap
                    >
                      <img
                        className="pd-body-image"
                        src={item.src}
                        alt=""
                        data-webgl-media
                        data-webgl-media-kind="detail"
                      />
                    </figure>
                  ))
                : null}
            </div>

            {/* Body Booking Aside
               ------------------------------------------
               Hero ?�단??기본 ReservationModule?� 그�?�??��??�다.
               ???�역?� ?�세 바디 ?�근?�을 ?�한 별도 sticky 간소 ?�약 ?�널?�다.
               Booking_side.tsx?�서 ?��??�키지??출발???�택,
               ?�일리투?�는 미니 캘린?�로 분기?�다.
            */}
            <aside className="pd-booking-aside" aria-label="간소 ?�약 ?�널">
              <div className="pd-booking-aside-sticky">
                <BookingSide
                  product={{
                    id: DETAIL_DATA.id,
                    legacyProductId: DETAIL_DATA.legacyProductId,
                    legacyFeeOptionId: DETAIL_DATA.legacyFeeOptionId,
                    legacyPackageScheduleId: DETAIL_DATA.legacyPackageScheduleId,
                    productType: DETAIL_DATA.productType,
                    title: DETAIL_DATA.title,
                    currency: DETAIL_DATA.currency,
                    basePrice: DETAIL_DATA.basePrice,
                    feeOptions: DETAIL_DATA.feeOptions,
                    duration: DETAIL_DATA.duration,
                    routeCode: DETAIL_DATA.routeCode,
                  }}
                  availableDates={availableDateSource}
                  cartHref="/mypage/cart"
                  reservationHref="/reservation"
                  kakaoChannelUrl="https://pf.kakao.com/_YOUR_CHANNEL_ID/chat"
                />
              </div>
            </aside>
          </div>
        </section>

        {true && (
          /*
            Product-detail bottom QNA/FAQ section.
            This is the content shown under the product detail body image.
            It is not the public community Q&A board (`bo_table=qna`).
          */
          <section className="pd-detail-faq-section" id="faq" aria-label="FAQ">
            <div className="pd-detail-faq-inner">
              <div className="pd-detail-faq-head">
                <h2 className="pd-detail-faq-title">{"\uC790\uC8FC \uBB3B\uB294 FAQ"}</h2>
                <a className="pd-detail-faq-action" href="/mypage/inquiry">
                  {"1:1 \uC0C1\uB2F4\uD558\uAE30"}
                </a>
              </div>

              <div className="pd-detail-faq-list">
                {DETAIL_DATA.faqs && DETAIL_DATA.faqs.length > 0 ? (
                  DETAIL_DATA.faqs.map((faq) => (
                  <article key={faq.id} className="pd-detail-faq-item">
                    <span className="pd-detail-faq-meta">
                      {faq.category || "FAQ"}
                    </span>
                    <div>
                      <h3 className="pd-detail-faq-question">
                        {faq.question || "등록된 FAQ가 없습니다."}
                      </h3>
                      <div className="pd-detail-faq-answer">
                        {faq.answerHtml ? (
                          <div dangerouslySetInnerHTML={{ __html: faq.answerHtml }} />
                        ) : (
                        <p>{faq.answerText || "관리자에서 상품 FAQ를 연결하면 이 영역에 표시됩니다."}</p>
                        )}
                      </div>
                    </div>
                  </article>
                  ))
                ) : (
                  <article className="pd-detail-faq-item">
                    <span className="pd-detail-faq-meta">FAQ</span>
                    <div>
                      <h3 className="pd-detail-faq-question">{"\uB4F1\uB85D\uB41C FAQ\uAC00 \uC5C6\uC2B5\uB2C8\uB2E4."}</h3>
                      <div className="pd-detail-faq-answer">
                        <p>{"\uAD00\uB9AC\uC790\uC5D0\uC11C \uC0C1\uD488 FAQ\uB97C \uC5F0\uACB0\uD558\uBA74 \uC774 \uC601\uC5ED\uC5D0 \uD45C\uC2DC\uB429\uB2C8\uB2E4."}</p>
                      </div>
                    </div>
                  </article>
                )}
              </div>
            </div>
          </section>
        )}

        <InfiniteOther
          products={relatedProducts}
          label={relatedSectionLabel}
          onNavigate={handleRelatedProductClick}
        />
      </div>
      </section>



      {activeNotice && (
        <div
          className="pd-notice-modal-backdrop"
          role="presentation"
          onClick={() => setActiveNotice(null)}
        >
          <aside
            className="pd-notice-modal"
            role="dialog"
            aria-modal="true"
            aria-label={activeNotice.title}
            onClick={(event) => event.stopPropagation()}
          >
            <div className="pd-notice-modal-titlebar" aria-hidden="true">
              <span className="pd-notice-modal-dots">
                <span />
                <span />
                <span />
              </span>
              <span className="pd-notice-modal-label">UNOTRAVEL NOTICE</span>
            </div>
            <div className="pd-notice-modal-content">
              <h2 className="pd-notice-modal-title">{activeNotice.title}</h2>
              <p className="pd-notice-modal-body">{activeNotice.body}</p>
              <button
                type="button"
                className="pd-notice-modal-confirm"
                onClick={() => setActiveNotice(null)}
              >
                ?�인
              </button>
            </div>
          </aside>
        </div>
      )}

      {isReviewSurfaceOpen && (
        <div
          className="pd-review-surface-backdrop"
          role="presentation"
          onClick={() => setIsReviewSurfaceOpen(false)}
        >
          <aside
            className="pd-review-surface"
            role="dialog"
            aria-modal="true"
            aria-label="?�체 리뷰"
            onClick={(event) => event.stopPropagation()}
          >
            <div className="pd-review-surface-head">
              <div>
                <div className="pd-review-surface-kicker">REVIEW PAGE</div>
                <h2 className="pd-review-surface-title">
                  ?�행??리뷰�????�면?�서 ?�인?�니??
                </h2>
              </div>
              <button
                type="button"
                className="pd-review-surface-close"
                onClick={() => setIsReviewSurfaceOpen(false)}
                aria-label="리뷰 ?�기"
              >
                ×
              </button>
            </div>

            {/*
              Review Page Backend Fields
              ------------------------------------------
              차후 리뷰 ?�이지�?별도 컴포?�트�?분리??????Surface ?��????�결?�다.

              - reviews       ??리뷰 목록
              - reviewSummary ???�점 / ?�기 ??/ 만족???�약
              - reviewImages  ??리뷰 ?��?지
              - reviewer      ???�성??/ ?�성??/ ?�약 ?�품 ?�보

              ?�재???�당 productId???�결???�제 리뷰 ?�태??Mock Data�??�용?�다.
            */}
            <div className="pd-review-surface-summary">
              <p className="pd-review-summary-text">{DETAIL_DATA.review}</p>
            </div>

            <div className="pd-review-list">
              {DETAIL_DATA.reviews.map((review) => (
                <article key={review.id} className="pd-review-card">
                  <div className="pd-review-card-head">
                    <div className="pd-review-user">
                      <span className="pd-review-avatar" aria-hidden="true">
                        {review.nickname.slice(0, 1)}
                      </span>
                      <div>
                        <span className="pd-review-nickname">
                          {review.nickname}
                        </span>
                        <span className="pd-review-meta">
                          {review.writtenAt} · {review.productTitle}
                        </span>
                      </div>
                    </div>
                  </div>
                  <h3 className="pd-review-card-title">{review.title}</h3>
                  <p className="pd-review-card-body">{review.body}</p>
                </article>
              ))}
            </div>
          </aside>
        </div>
      )}
    </>
  );
}
