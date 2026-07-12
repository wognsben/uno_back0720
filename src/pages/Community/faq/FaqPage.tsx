// FaqPage.tsx
// 커뮤니티 FAQ 목록과 카테고리 필터를 보여주는 화면입니다.
// FAQ 글쓰기 버튼의 로그인 필요 상태와 커뮤니티 문의 화면 이동을 관리합니다.
// 마이페이지 1:1 문의와 분리된 공개 문의 진입점 역할입니다.

import { useState } from "react";

import CommunityLayout from "../CommunityLayout";

import { isLocalAuthSessionActive } from "../../../api/authSession";
import { FAQ_CATEGORIES, FAQ_ITEMS } from "../community.data";

const INQUIRY_WRITE_PATH = "/community/inquiry";
const LOGIN_PATH = "/login";

function navigateTo(path: string) {
    if (typeof window === "undefined") return;

    if (window.location.pathname === path) {
        window.scrollTo({ top: 0, behavior: "smooth" });
        return;
    }

    window.history.pushState({}, "", path);
    window.dispatchEvent(new Event("unotravel:navigate"));
}

export default function FaqPage() {
    const [activeCategory, setActiveCategory] = useState("all");
    const [openId, setOpenId] = useState<string | null>(FAQ_ITEMS[0]?.id ?? null);
    const [isLoginModalOpen, setIsLoginModalOpen] = useState(false);

    const filteredItems =
        activeCategory === "all"
            ? FAQ_ITEMS
            : FAQ_ITEMS.filter((item) => item.categoryId === activeCategory);

    const handleWriteClick = () => {
        if (isLocalAuthSessionActive()) {
            navigateTo(INQUIRY_WRITE_PATH);
            return;
        }

        setIsLoginModalOpen(true);
    };

    const handleLoginClick = () => {
        if (typeof window !== "undefined") {
            window.sessionStorage.setItem("unotravel:redirect-after-login", INQUIRY_WRITE_PATH);
        }

        navigateTo(LOGIN_PATH);
    };

    return (
        <CommunityLayout type="faq">
            <section className="community-faq">
                <div className="community-faq-category" aria-label="FAQ Category">
                    {FAQ_CATEGORIES.map((category) => (
                        <button
                            key={category.id}
                            className={`community-faq-category-button ${
                                activeCategory === category.id ? "is-active" : ""
                            }`}
                            type="button"
                            onClick={() => {
                                setActiveCategory(category.id);
                                setOpenId(null);
                            }}
                        >
                            {category.label}
                        </button>
                    ))}
                </div>

                <div className="community-faq-main">
                    <div className="community-faq-toolbar">
                        <div>
                            <span>QUESTION</span>
                            <p>FAQ에서 해결되지 않은 내용은 커뮤니티 문의로 남겨 주세요.</p>
                        </div>
                        <button
                            className="community-write-button"
                            type="button"
                            onClick={handleWriteClick}
                        >
                            글쓰기
                        </button>
                    </div>

                    <div className="community-faq-list">
                        {filteredItems.map((item) => {
                            const isOpen = openId === item.id;

                            return (
                                <article
                                    key={item.id}
                                    className={`community-faq-item ${isOpen ? "is-open" : ""}`}
                                >
                                    <button
                                        className="community-faq-question"
                                        type="button"
                                        onClick={() => setOpenId(isOpen ? null : item.id)}
                                        aria-expanded={isOpen}
                                    >
                                        <span>{item.question}</span>
                                        <span className="community-faq-icon">+</span>
                                    </button>

                                    {isOpen && (
                                        <div className="community-faq-answer">
                                            <p>{item.answer}</p>
                                        </div>
                                    )}
                                </article>
                            );
                        })}
                    </div>
                </div>
            </section>

            {isLoginModalOpen && (
                <div
                    className="community-login-backdrop"
                    role="presentation"
                    onClick={() => setIsLoginModalOpen(false)}
                >
                    <div
                        className="community-login-modal"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="community-login-title"
                        onClick={(event) => event.stopPropagation()}
                    >
                        <span>LOGIN REQUIRED</span>
                        <h2 id="community-login-title">로그인이 필요합니다</h2>
                        <p>
                            문의 글을 작성하려면 로그인이 필요합니다. 로그인 후 커뮤니티 문의
                            화면으로 이동합니다.
                        </p>
                        <div className="community-login-actions">
                            <button type="button" onClick={() => setIsLoginModalOpen(false)}>
                                계속 보기
                            </button>
                            <button type="button" onClick={handleLoginClick}>
                                로그인 화면으로 이동
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </CommunityLayout>
    );
}
