// community.data.ts
// 커뮤니티 화면의 임시 네비게이션, 게시글, FAQ 데이터를 모아두는 파일입니다.
// 실제 Gnuboard 연동 전까지 화면 구성과 이동 목적지를 안정적으로 유지합니다.
// 문의하기는 공개 문의 화면이며, 마이페이지 1:1 문의와 역할이 겹치지 않도록 분리합니다.

import type {
    CommunityFaqCategory,
    CommunityFaqItem,
    CommunityNavItem,
    CommunityPost,
} from "./community.types";

export const COMMUNITY_NAV_ITEMS: CommunityNavItem[] = [
    {
        id: "notice",
        label: "공지사항",
        labelEn: "Notice",
        href: "/community/notice",
        description: "우노트래블의 새로운 소식",
    },
    {
        id: "event",
        label: "이벤트",
        labelEn: "Event",
        href: "/community/event",
        description: "진행중인 이벤트",
    },
    {
        // Community FAQ is public help content. Do not confuse it with My Page 1:1 inquiry (cusTour).
        id: "faq",
        label: "FAQ",
        labelEn: "FAQ",
        href: "/community/faq",
        description: "자주 묻는 질문",
    },
    {
        id: "review",
        label: "여행후기",
        labelEn: "Review",
        href: "/community/review",
        description: "여행자들의 생생한 후기",
    },
];

export const COMMUNITY_POSTS: CommunityPost[] = [
    {
        id: "1",
        type: "notice",
        title: "2026년 여름 시즌 예약 안내",
        excerpt: "예약 일정 및 운영 안내입니다.",
        date: "2026-07-07",
        views: 324,
        href: "/community/notice/1",
        isPinned: true,
        isNew: true,
    },
    {
        id: "2",
        type: "review",
        title: "돌로미티 세미패키지 여행 후기",
        excerpt: "평생 기억에 남을 최고의 여행이었습니다.",
        author: "UNO",
        date: "2026-07-05",
        views: 128,
        thumbnail: "/images/temp/review01.jpg",
        href: "/community/review/2",
    },
    {
        id: "3",
        type: "event",
        title: "여름 얼리버드 이벤트",
        excerpt: "예약 고객 대상 특별 혜택",
        date: "2026-07-01",
        views: 91,
        thumbnail: "/images/temp/event01.jpg",
        href: "/community/event/3",
        startDate: "2026-07-01",
        endDate: "2026-08-31",
        status: "active",
    },
];

export const FAQ_CATEGORIES: CommunityFaqCategory[] = [
    {
        id: "all",
        label: "전체",
    },
    {
        id: "reservation",
        label: "예약",
    },
    {
        id: "tour",
        label: "투어",
    },
    {
        id: "payment",
        label: "결제",
    },
];

export const FAQ_ITEMS: CommunityFaqItem[] = [
    {
        id: "1",
        categoryId: "reservation",
        question: "예약은 언제까지 가능한가요?",
        answer: "상품마다 예약 가능 기간이 다르며 상세페이지의 날짜 선택 영역을 참고해 주세요.",
    },
    {
        id: "2",
        categoryId: "payment",
        question: "결제는 어떻게 진행되나요?",
        answer: "예약금 결제와 잔금 안내는 예약 확정 단계에서 상품별 기준에 맞춰 안내됩니다.",
    },
];
