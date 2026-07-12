/**
 * Community Types
 * ------------------------------------------------------------
 * 커뮤니티 화면에서 사용하는 게시글, FAQ, 네비게이션 공통 타입입니다.
 *
 * 기존 Gnuboard 연동 시 review / notice / event / qna / faq 계열 데이터를
 * 같은 프런트 구조로 변환하기 위한 경계 역할을 합니다.
 */

export type CommunityType = "review" | "notice" | "event" | "faq" | "inquiry";

export type CommunityStatus = "active" | "closed" | "scheduled" | "default";

export type CommunityNavItem = {
    id: CommunityType;
    label: string;
    labelEn: string;
    href: string;
    description: string;
};

export type CommunityPost = {
    id: string;
    type: Exclude<CommunityType, "faq" | "inquiry">;
    title: string;
    excerpt?: string;
    author?: string;
    date: string;
    views?: number;
    thumbnail?: string;
    href: string;
    status?: CommunityStatus;
    startDate?: string;
    endDate?: string;
    isPinned?: boolean;
    isNew?: boolean;
};

export type CommunityFaqCategory = {
    id: string;
    label: string;
};

export type CommunityFaqItem = {
    id: string;
    categoryId: string;
    question: string;
    answer: string;
};

export type CommunityLayoutProps = {
    type: CommunityType;
};

export type CommunityListProps = {
    type: Exclude<CommunityType, "faq" | "inquiry">;
    items: CommunityPost[];
};

export type CommunityCardProps = {
    item: CommunityPost;
};

export type CommunityDetailProps = {
    item: CommunityPost;
};

export type CommunitySearchProps = {
    placeholder?: string;
};

export type CommunityPaginationProps = {
    currentPage: number;
    totalPages: number;
};

export type CommunityNavigationProps = {
    active: CommunityType;
};
