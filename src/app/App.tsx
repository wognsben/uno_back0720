import { useEffect, useRef, useState } from "react";

import Footer from "./components/footer/index";
import Header from "./components/header/index";
import PageTransitionFrame from "./transitions/PageTransitionFrame";
import "./transitions/pageTransition.css";

import Intro from "../imports/INTRO/INTRO";

import HeroComponent from "../imports/MAIN_1TH/index";
import Section2Component from "../imports/MAIN_2TH/index";
import Section3Component from "../imports/MAIN_3TH/index";
import Section4Component from "../imports/MAIN_4TH/index";

/*
  ProductNavigation 연결

  메인 Hero 안에 있던 상품군 Navigation을
  공통 Sub Page Navigation으로 분리해서 사용한다.

  사용 위치:
  - Main Page: HeroComponent 내부 Navigation 유지 또는 추후 교체
  - Product Sub Page: ProductHero 위에 항상 노출

  주의:
  실제 파일 위치에 맞춰 import 경로는 조정할 수 있다.
*/
import ProductNavigation from "./components/common_navi/ProductNavigation";

/*
  ProductTemplate 연결

  메인 Hero에서 아래 경로로 이동할 때 이 페이지를 보여준다.

  예)
  - /product/semi/italy?view=gallery
  - /product/semi/spain?view=gallery
  - /product/daily/italy?view=gallery

  나중에 우노트래블 PHP 백엔드 연동 시
  window.location.pathname 값을 기준으로 category / region 값을 읽고
  ProductTemplate에 실제 DB 데이터를 넘기면 된다.
*/
import ProductTemplate, {
  DAILY_TOUR_DATA,
  SEMI_PACKAGE_DATA,
} from "../pages/product/ProductTemplate";

/*
  ProductDetail 연결

  ProductTemplate(목록)와 분리된 상품 상세페이지.
  실제 파일 경로:
  src/pages/product/ProductDetail.tsx
*/
import ProductDetail from "../pages/product/ProductDetail";

/*
  LoginPage 연결

  Header의 LOGIN 클릭 또는 /login 경로 진입 시 렌더링한다.
  Header / Footer는 App.tsx 공통 컴포넌트를 그대로 사용한다.
*/
import LoginPage from "../pages/login/LoginPage";
import RegisterPage from "../pages/register/RegisterPage";
import RegisterAgreement from "../pages/register/RegisterAgreement";
import RegisterForm from "../pages/register/RegisterForm";
import RegisterComplete from "../pages/register/RegisterComplete";
import MyPage from "../pages/mypage/MyPage";
import MyCart from "../pages/mypage/MyCart";
import MyReservation from "../pages/mypage/MyReservation";
import MyInquiry from "../pages/mypage/MyInquiry";
import MyProfile from "../pages/mypage/MyProfile";
import MyTour from "../pages/mypage/MyTour";
import ReservationPage from "../pages/reservation/ReservationPage";
import InfoPage from "../pages/info/Infopage";
import NoticePage from "../pages/info/notice";
import GuideUsePage from "../pages/info/guide_use";
import RefundPage from "../pages/info/refund";
import RulePage from "../pages/info/rule";
import PrivacyPage from "../pages/info/privacy";
import AboutUnoPage from "../pages/aboutuno/AboutUnoPage";
import ContactPage from "../pages/contact_page/ContactPage";

/* ==========================================================
   Community
========================================================== */
import CommunityPage from "../pages/Community/CommunityPage";
import ReviewPage from "../pages/Community/review/ReviewPage";
import NoticeBoardPage from "../pages/Community/notice/NoticePage";
import EventPage from "../pages/Community/event/EventPage";
import CommunityInquiryPage from "../pages/Community/inquiry/CommunityInquiryPage";
import FaqPage from "../pages/Community/faq/FaqPage";
import "../pages/Community/community.css";

/* ────────────────────────────────────────────
   Page (sections use 100vw shell + full width content rule)
──────────────────────────────────────────── */

const scrollCards = [
  {
    bg: "#FCC800",
    shape: "square",
    title: "SEMI\nPACKAGE",
    titleColor: "#151515",
    sub: "프리미엄 세미패키지 여행",
    subColor: "#151515",
    dividerColor: "#000000",
    circularText: true,
  },
  {
    bg: "#DAD5D5",
    shape: "circle",
    title: "DAILY\nTOUR",
    titleColor: "#151515",
    sub: "일일 투어",
    subColor: "#151515",
    dividerColor: "#151515",
    circularText: false,
  },
  {
    bg: "#DAD5D5",
    shape: "square",
    title: "CURATED\nJOURNEYS",
    titleColor: "#151515",
    sub: "이탈리아 여행 가이드 & 스토리",
    subColor: "#151515",
    dividerColor: "#151515",
    circularText: false,
  },
  {
    bg: "#151515",
    shape: "square",
    title: "TALK\nTO US",
    titleColor: "#FFFFFF",
    sub: "카카오톡으로 간편하게 문의하기",
    subColor: "#FFFFFF",
    dividerColor: "#FFFFFF",
    circularText: false,
  },
  {
    bg: "#151515",
    shape: "square",
    title: "우노트래블\n가이드 모집!",
    titleColor: "#FFFFFF",
    sub: "우노트래블과 함께할 로마현지 가이드를 모집합니다! 여행을 사랑하고 낭만과 열정있는 당신이 필요해요!",
    subColor: "#FFFFFF",
    dividerColor: "#FFFFFF",
    circularText: false,
  },
  {
    bg: "#F4FE44",
    shape: "square",
    title: "페리투어:\n투어소개",
    titleColor: "#151515",
    sub: "[우노트래블X마이리얼트립] 반짝이는 지중해와 함께 원데이 바캉스! 페리투어를 오픈했습니다.",
    subColor: "#151515",
    dividerColor: "#151515",
    circularText: false,
  },
];

function CircularText({ text, r = 38 }: { text: string; r?: number }) {
  const cx = 55;
  const cy = 55;

  return (
    <svg
      width={110}
      height={110}
      style={{ position: "absolute", bottom: 16, right: 16 }}
    >
      <circle
        cx={cx}
        cy={cy}
        r={r}
        fill="none"
        stroke="#151515"
        strokeWidth={1.5}
      />
      <defs>
        <path
          id="circlePath"
          d={`M ${cx},${cy - r} a ${r},${r} 0 1,1 -0.01,0`}
        />
      </defs>
      <text
        style={{
          fontFamily: "var(--font-en)",
          fontWeight: 600,
          fontSize: 12,
          letterSpacing: "0.18em",
        }}
        fill="#151515"
      >
        <textPath href="#circlePath">{text}</textPath>
      </text>
      {/* Center logo mark */}
      <rect
        x={cx - 14}
        y={cy - 8}
        width={28}
        height={16}
        rx={2}
        fill="#151515"
      />
    </svg>
  );
}

function ScrollCard({ card }: { card: (typeof scrollCards)[number] }) {
  const w = card.shape === "circle" ? 405 : 334;
  const borderRadius = card.shape === "circle" ? "50%" : 0;

  return (
    <div
      style={{
        width: w,
        height: 405,
        background: card.bg,
        borderRadius,
        flexShrink: 0,
        position: "relative",
        padding: "30px 24px",
        boxSizing: "border-box",
        display: "flex",
        flexDirection: "column",
        overflow: "hidden",
      }}
    >
      {/* Title */}
      <span
        style={{
          fontFamily: /[ㄱ-ㅎㅏ-ㅣ가-힣]/.test(card.title)
            ? "var(--font-ko)"
            : "var(--font-en)",
          fontWeight: 600,
          fontSize: 48,
          lineHeight: "48px",
          letterSpacing: "-0.03em",
          color: card.titleColor,
          whiteSpace: "pre-line",
        }}
      >
        {card.title}
      </span>

      {/* Divider */}
      <div
        style={{
          width: 82,
          height: 1,
          background: card.dividerColor,
          marginTop: 24,
          marginBottom: 20,
          flexShrink: 0,
        }}
      />

      {/* Sub text */}
      <span
        style={{
          fontFamily: /[ㄱ-ㅎㅏ-ㅣ가-힣]/.test(card.sub)
            ? "var(--font-ko)"
            : "var(--font-en)",
          fontWeight: 600,
          fontSize: 15,
          lineHeight: "24px",
          letterSpacing: "-0.03em",
          color: card.subColor,
        }}
      >
        {card.sub}
      </span>

      {/* Bottom arrow line */}
      <div
        style={{
          position: "absolute",
          bottom: 28,
          right: card.circularText ? 130 : 24,
          width: 34,
          height: 2,
          background: card.dividerColor,
        }}
      />

      {/* Circular text badge on card 1 */}
      {card.circularText && (
        <CircularText text="PREMIUM · UNO TRAVEL · " r={38} />
      )}
    </div>
  );
}

/*
  Main Page Scroll Restore
  ------------------------------------------
  메인페이지에서 상품 서브페이지로 이동하기 전의 scrollY를 저장한다.
  브라우저 뒤로가기로 상품 서브페이지에서 메인페이지로 돌아올 때
  사용자가 보고 있던 메인페이지 위치를 복원한다.
*/
const MAIN_SCROLL_STORAGE_KEY = "unotravel_main_scroll_y";
const HISTORY_SCROLL_STATE_KEY = "__unotravelScrollY";

const getStoredMainScrollY = () => {
  if (typeof window === "undefined") {
    return 0;
  }

  const storedValue = sessionStorage.getItem(MAIN_SCROLL_STORAGE_KEY);
  const parsedValue = storedValue ? Number(storedValue) : 0;

  return Number.isFinite(parsedValue) ? parsedValue : 0;
};

const getHistoryScrollY = (state: unknown) => {
  if (!state || typeof state !== "object") {
    return null;
  }

  const value = (state as Record<string, unknown>)[HISTORY_SCROLL_STATE_KEY];

  return typeof value === "number" && Number.isFinite(value) ? value : null;
};

const saveCurrentHistoryScrollY = () => {
  if (typeof window === "undefined") {
    return;
  }

  const currentState =
    window.history.state && typeof window.history.state === "object"
      ? window.history.state
      : {};

  window.history.replaceState(
    {
      ...currentState,
      [HISTORY_SCROLL_STATE_KEY]: window.scrollY || 0,
    },
    "",
    window.location.href,
  );
};

/* ────────────────────────────────────────────
   Page
──────────────────────────────────────────── */
export default function App() {
  const previousPathnameRef = useRef(window.location.pathname);
  const shouldRestoreMainScrollRef = useRef(false);
  const pendingHistoryScrollYRef = useRef<number | null>(null);
  const routeUpdateFrameRef = useRef<number | null>(null);

  /*
  Intro
  ------------------------------------------
  브라우저 세션 기준 최초 진입에서만 Intro를 실행한다.
  이후 Logo 클릭 또는 메인 재진입 시에는 Intro를 다시 보여주지 않는다.
*/
  const [showIntro, setShowIntro] = useState(() => {
    return sessionStorage.getItem("uno_intro_played") !== "true";
  });

  /*
    ProductTemplate 임시 라우팅

    Figma Make 환경에서 react-router가 아직 없을 수 있으므로
    우선 window.location.pathname으로 분기한다.

    현재 조건:
    /product/ 로 시작하는 모든 경로는 ProductTemplate을 보여준다.

    나중에 실제 백엔드 연동 시:
    /product/semi/italy
    /product/daily/rome
    등의 URL 규칙에 맞춰 category / region / view 데이터를 추출하면 된다.
  */
  const [pathname, setPathname] = useState(window.location.pathname);

  useEffect(() => {
    /*
    SPA Scroll Restoration
    ------------------------------------------
    브라우저 기본 scroll 복원과 App 내부 route 렌더링 타이밍이 충돌하지 않도록
    수동 복원 방식으로 통일한다.
  */
    if ("scrollRestoration" in window.history) {
      window.history.scrollRestoration = "manual";
    }

    saveCurrentHistoryScrollY();

    const handleRouteChange = (event: Event) => {
      const previousPathname = previousPathnameRef.current;
      const nextPathname = window.location.pathname;
      const isPopState = event.type === "popstate";
      const popStateScrollY =
        event instanceof PopStateEvent ? getHistoryScrollY(event.state) : null;

      const wasProductPage = previousPathname.startsWith("/product/");
      const willBeProductPage = nextPathname.startsWith("/product/");

      /*
      Main Scroll Save
      ------------------------------------------
      메인페이지에서 상품 서브페이지로 이동하기 직전의 scrollY를 저장한다.
      unotravel:navigate 이벤트는 pushState 이후 발생하지만,
      화면은 아직 리렌더링 전이므로 현재 scrollY를 안전하게 읽을 수 있다.
    */
      if (!wasProductPage) {
        sessionStorage.setItem(MAIN_SCROLL_STORAGE_KEY, String(window.scrollY));
      }

      /*
      Back Navigation Scroll Restore Flag
      ------------------------------------------
      상품 서브페이지에서 브라우저 뒤로가기로 메인페이지에 돌아오는 경우에만
      기존 메인페이지 위치를 복원한다.

      Logo 클릭 등 programmatic navigation은 새 진입으로 보고
      스크롤 복원 대상에서 제외한다.
    */
      shouldRestoreMainScrollRef.current =
        isPopState && wasProductPage && !willBeProductPage && popStateScrollY === null;
      pendingHistoryScrollYRef.current = isPopState ? popStateScrollY : null;

      /*
      SPA Route Commit Timing
      ------------------------------------------
      INFO 문서 간 이동처럼 같은 레이아웃 안에서 페이지 컴포넌트가 교체될 때,
      즉시 setPathname을 실행하면 이전 scrollY 상태에서 새 페이지가 먼저 렌더링되고
      GSAP SplitText / Aside follower가 동시에 초기화되어 "쿵" 하는 전환처럼 보일 수 있다.

      custom navigation은 먼저 scrollTop을 안정화하고,
      다음 animation frame에서 페이지 컴포넌트를 교체한다.
    */
      const isAboutContactTransition =
        (previousPathname === "/about-uno" && nextPathname === "/contact") ||
        (previousPathname === "/contact" && nextPathname === "/about-uno");

      if (
        event.type === "unotravel:navigate" &&
        previousPathname !== nextPathname &&
        !isAboutContactTransition
      ) {
        window.scrollTo({
          top: 0,
          left: 0,
          behavior: "auto",
        });
      }

      previousPathnameRef.current = nextPathname;

      if (routeUpdateFrameRef.current) {
        window.cancelAnimationFrame(routeUpdateFrameRef.current);
      }

      routeUpdateFrameRef.current = window.requestAnimationFrame(() => {
        routeUpdateFrameRef.current = null;
        setPathname(window.location.pathname);
      });
    };

    window.addEventListener("popstate", handleRouteChange);
    window.addEventListener("unotravel:navigate", handleRouteChange);

    let scrollSaveFrame: number | null = null;

    const handleScrollSave = () => {
      if (scrollSaveFrame !== null) {
        return;
      }

      scrollSaveFrame = window.requestAnimationFrame(() => {
        scrollSaveFrame = null;
        saveCurrentHistoryScrollY();
      });
    };

    window.addEventListener("scroll", handleScrollSave, { passive: true });

    return () => {
      window.removeEventListener("popstate", handleRouteChange);
      window.removeEventListener("unotravel:navigate", handleRouteChange);
      window.removeEventListener("scroll", handleScrollSave);

      if (scrollSaveFrame !== null) {
        window.cancelAnimationFrame(scrollSaveFrame);
      }

      if (routeUpdateFrameRef.current) {
        window.cancelAnimationFrame(routeUpdateFrameRef.current);
        routeUpdateFrameRef.current = null;
      }
    };
  }, []);

  useEffect(() => {
    /*
    Main Scroll Tracking
    ------------------------------------------
    메인페이지를 보고 있는 동안 최신 scrollY를 계속 저장한다.
    상품 서브페이지에서는 저장값을 덮어쓰지 않는다.
  */
    const handleMainScroll = () => {
      /*
      Main Scroll Tracking Scope
      ------------------------------------------
      메인페이지가 아닌 /product/*, /login, /register/* 같은 서브 라우트에서는
      메인페이지 scrollY 저장값을 덮어쓰지 않는다.
    */
      if (window.location.pathname !== "/") {
        return;
      }

      sessionStorage.setItem(MAIN_SCROLL_STORAGE_KEY, String(window.scrollY));
    };

    window.addEventListener("scroll", handleMainScroll, { passive: true });

    return () => {
      window.removeEventListener("scroll", handleMainScroll);
    };
  }, []);

  useEffect(() => {
    const historyScrollY = pendingHistoryScrollYRef.current;
    const shouldRestoreHistoryScroll = historyScrollY !== null;
    const shouldRestoreMainScroll =
      !pathname.startsWith("/product/") && shouldRestoreMainScrollRef.current;

    if (!shouldRestoreHistoryScroll && !shouldRestoreMainScroll) {
      return;
    }

    pendingHistoryScrollYRef.current = null;
    shouldRestoreMainScrollRef.current = false;

    /*
    Main Scroll Restore
    ------------------------------------------
    메인페이지 섹션들이 다시 렌더링되고 각 섹션의 Dynamic Height가 잡힌 뒤
    저장된 위치로 복원한다.
  */
    const restoreScrollY = shouldRestoreHistoryScroll
      ? historyScrollY
      : getStoredMainScrollY();

    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        window.scrollTo({
          top: restoreScrollY,
          left: 0,
          behavior: "auto",
        });
      });
    });
  }, [pathname]);

  const isProductPage = pathname.startsWith("/product/");
  const isMainPage = pathname === "/";
  const isLoginPage = pathname === "/login";
  const isRegisterPage = pathname === "/register";
  const isRegisterAgreementPage = pathname === "/register/agreement";
  const isRegisterFormPage = pathname === "/register/form";
  const isRegisterCompletePage = pathname === "/register/complete";
  const isRegisterRoute = pathname === "/register" || pathname.startsWith("/register/");
  const isMyPage = pathname === "/mypage";
  const isMyCartPage = pathname === "/mypage/cart";
  const isMyReservationPage = pathname === "/mypage/reservations";
  const isMyInquiryPage = pathname === "/mypage/inquiry";
  const isMyProfilePage = pathname === "/mypage/profile";
  const isMyTourPage = pathname === "/mypage/tour";
  const isMyPageRoute = pathname.startsWith("/mypage");
  const isReservationPage = pathname === "/reservation";
  const isInfoPage = pathname === "/info";
  const isNoticePage = pathname === "/info/notice";
  const isGuideUsePage = pathname === "/info/guide_use";
  const isRefundPage = pathname === "/info/refund";
  const isRulePage = pathname === "/info/rule";
  const isPrivacyPage = pathname === "/info/privacy";
  const isInfoPageRoute = pathname === "/info" || pathname.startsWith("/info/");

  /*
  About UNO Route
  ------------------------------------------
  Header Dot Menu의 ABOUT UNO 진입 페이지.
  Community와 분리된 브랜드 소개 페이지로 렌더링한다.
*/
  const isAboutUnoPage = pathname === "/about-uno";
  const isContactPage = pathname === "/contact";

  /*
  Community Route
  ------------------------------------------
  Review / Notice / Event / FAQ 커뮤니티 페이지 라우팅.
  /community 기본 진입은 ReviewPage로 연결한다.
*/
  const isCommunityIndexPage = pathname === "/community";
  const isCommunityReviewPage = pathname === "/community/review";
  const isCommunityNoticePage = pathname === "/community/notice";
  const isCommunityEventPage = pathname === "/community/event";
  const isCommunityFaqPage = pathname === "/community/faq";
  const isCommunityInquiryPage = pathname === "/community/inquiry";
  const isCommunityRoute =
    pathname === "/community" || pathname.startsWith("/community/");

  /*
  Product Detail Route
  ------------------------------------------
  /product/detail/... 경로는 ProductDetail을 렌더링한다.
  ProductTemplate 내부 조건부 전환이 아니라 App.tsx에서 목록/상세를 분리한다.
*/
  const isProductDetail = pathname.startsWith("/product/detail/");

  /*
  Product List Route Data
  ------------------------------------------
  /product/daily/... 경로에서는 데일리투어 목록 데이터를 보여준다.
  /product/semi/... 또는 그 외 /product/... 경로에서는 기존 세미패키지 목록 데이터를 유지한다.

  실제 백엔드 연동 시 pathname에서 category / region 값을 추출해
  ProductTemplateData 대신 API 응답 데이터를 전달하면 된다.
*/
  const isDailyProductPage = pathname.startsWith("/product/daily/");
  const productTemplateData = isDailyProductPage
    ? DAILY_TOUR_DATA
    : SEMI_PACKAGE_DATA;
  const productPathParts = pathname.split("/").filter(Boolean);
  const productRouteRegionId =
    productPathParts[0] === "product" &&
    (productPathParts[1] === "semi" || productPathParts[1] === "daily") &&
    productPathParts[2]
      ? productPathParts[2]
      : undefined;
  const shouldShowGlobalProductNavigation =
    !isLoginPage &&
    !isRegisterRoute &&
    !isContactPage &&
    !isReservationPage &&
    !isProductPage &&
    !isMainPage;
  const shouldShowScrollOnlyProductNavigation =
    !isLoginPage &&
    !isRegisterRoute &&
    !isContactPage &&
    !isProductPage &&
    isMainPage;

  const sectionShell = {
    position: "relative" as const,

    /* Desktop Responsive
       - Section3 Horizontal Slider를 제외한 App shell은 100vw 대신 100% 기준 사용
       - 100vw는 scrollbar 폭까지 포함되어 가로 스크롤을 만들 수 있음 */
    width: "100%",
    minWidth: 1024,

    flexShrink: 0,
    overflow: "hidden" as const,
  };

  const pageContent = (
    isProductPage ? (
          /*
            Product Sub Page

            메인 Hero에서 국가/상품 클릭 시 진입하는 Type A 상품 서브페이지.
            Header / Footer는 App.tsx에서 공통으로 유지한다.

            ProductNavigation은 모든 상품 서브페이지의 Hero 위에 고정적으로 배치한다.
            즉, ProductTemplate 내부 Hero보다 먼저 노출되는 공통 Sub Page Navigation이다.
          */
          <>
            <div style={{ paddingTop: 132, width: "100%", minWidth: 1024 }}>
              <ProductNavigation />
            </div>
            {isProductDetail ? (
              <ProductDetail />
            ) : (
              <ProductTemplate
                pageData={productTemplateData}
                routeRegionId={productRouteRegionId}
              />
            )}
          </>
        ) : isLoginPage ? (
          /*
            Login Page

            /login 경로에서 로그인 페이지를 렌더링한다.
            로그인 화면은 인증 전용 화면이므로 Header / Footer를 노출하지 않는다.
            ProductNavigation은 상품 페이지 전용이므로 노출하지 않는다.
          */
          <LoginPage />
        ) : isRegisterPage ? (
          /*
            Register Start Page

            /register 경로에서 회원가입 시작 페이지를 렌더링한다.
            기존 regis_agree.php 진입 전 안내/시작 화면에 대응한다.
          */
          <RegisterPage />
        ) : isRegisterAgreementPage ? (
          /*
            Register Agreement Page

            /register/agreement 경로에서 약관 동의 페이지를 렌더링한다.
            기존 regis_agree.php 흐름에 대응한다.
          */
          <RegisterAgreement />
        ) : isRegisterFormPage ? (
          /*
            Register Form Page

            /register/form 경로에서 회원가입 입력 페이지를 렌더링한다.
            실제 백엔드 연동 전까지는 프론트 placeholder 흐름을 유지한다.
          */
          <RegisterForm />
        ) : isRegisterCompletePage ? (
          /*
            Register Complete Page

            /register/complete 경로에서 가입 완료 페이지를 렌더링한다.
          */
          <RegisterComplete />
        ) : isMyPage ? (
          <MyPage />
        ) : isMyCartPage ? (
          <MyCart />
        ) : isMyReservationPage ? (
          <MyReservation />
        ) : isMyInquiryPage ? (
          <MyInquiry />
        ) : isMyProfilePage ? (
          <MyProfile />
        ) : isMyTourPage ? (
          <MyTour />
        ) : isReservationPage ? (
          <ReservationPage />
        ) : isInfoPage ? (
          <InfoPage />
        ) : isNoticePage ? (
          <NoticePage />
        ) : isGuideUsePage ? (
          <GuideUsePage />
        ) : isRefundPage ? (
          <RefundPage />
        ) : isRulePage ? (
          <RulePage />
        ) : isPrivacyPage ? (
          <PrivacyPage />
        ) : isAboutUnoPage ? (
          <AboutUnoPage />
        ) : isContactPage ? (
          <ContactPage />
        ) : isCommunityIndexPage ? (
          <CommunityPage />
        ) : isCommunityReviewPage ? (
          <ReviewPage />
        ) : isCommunityNoticePage ? (
          <NoticeBoardPage />
        ) : isCommunityEventPage ? (
          <EventPage />
        ) : isCommunityInquiryPage ? (
          <CommunityInquiryPage />
        ) : isCommunityFaqPage ? (
          <FaqPage />
        ) : (
          <>
            {/* Hero */}
            <div
              style={{
                ...sectionShell,

                /* Desktop Responsive Height
                   - Hero 내부 ResizeObserver scale 기준으로 실제 높이를 직접 관리한다.
                   - App에서 고정 height를 주면 화면 폭이 줄어들 때 Hero 아래 공백이 생긴다. */
              }}
            >
              <HeroComponent />
            </div>

            {/* Section2 */}
            <div
              style={{
                ...sectionShell,

                /* Desktop Responsive Height
                   - Section2 내부 ResizeObserver scale 기준으로 실제 높이를 직접 관리한다.
                   - App에서 고정 height를 주면 Notebook 폭에서 세로 공백이 과도하게 남는다. */
              }}
            >
              <Section2Component />
            </div>

            {/* Section3 */}
            <div
              style={{
                ...sectionShell,

                /* Desktop Responsive Exception
       ------------------------------------------
       Section3 내부에서 ResizeObserver + Dynamic Height를 직접 관리한다.
       상단 무한 Horizontal Slider만 Section3 내부에서 100vw를 유지한다.
       App.tsx에서는 고정 height를 주지 않는다.
    */
                width: "100%",
                minWidth: 1024,
                overflow: "hidden",
              }}
            >
              <Section3Component />
            </div>

            {/* Section4 */}
            <div
              style={{
                ...sectionShell,

                /* Desktop Responsive Height
                   - Section4는 내부 ResizeObserver scale 기준으로 실제 높이를 직접 관리한다.
                   - App에서 고정 height를 주면 화면 폭이 줄어들 때 하단 공백이 생긴다. */
              }}
            >
              <Section4Component />
            </div>
          </>
        )
  );

  return (
    <>
      {/* Intro */}
      {!isProductPage &&
        !isLoginPage &&
        !isRegisterPage &&
        !isRegisterAgreementPage &&
        !isRegisterFormPage &&
        !isRegisterCompletePage &&
        !isMyPageRoute &&
        !isInfoPageRoute &&
        !isAboutUnoPage &&
        !isContactPage &&
        !isCommunityRoute &&
        showIntro && (
          <Intro
            onFinish={() => {
              sessionStorage.setItem("uno_intro_played", "true");
              setShowIntro(false);
            }}
          />
        )}

      {/* Main */}
      <div
        style={{
          /* Desktop Responsive
             - App root는 100vw 대신 100% 기준
             - Desktop/Tablet Landscape 최소 폭은 1024px 유지 */
          width: "100%",
          minWidth: 1024,

          background: "#FFFFFF",
          display: "flex",
          flexDirection: "column",
          alignItems: "stretch",
          boxSizing: "border-box",
          overflowX: "hidden",
        }}
      >
        {/* Header */}
        {!isLoginPage && !isRegisterRoute && !isContactPage && (
          <div
            style={{
              /* Desktop Responsive
                 - Header wrapper도 100vw 대신 부모 기준 100% 사용 */
              width: "100%",
              boxSizing: "border-box",
              padding: "51px 55px 0",
              display: "flex",
              justifyContent: "center",
              overflow: "hidden",
            }}
          >
            <Header />
          </div>
        )}

        {/*
          ProductNavigation — 비상품 페이지 전용 floating 핸들
          ------------------------------------------
          상품 페이지가 아닌 모든 페이지에서 스크롤 핸들로만 표시.
          forceFloating=true → document flow를 점유하지 않고 처음부터 fixed 핸들 상태.
          상품 페이지는 pageContent 안에서 document flow로 렌더링한다.
        */}
        {shouldShowGlobalProductNavigation && (
          <div style={{ paddingTop: 132, width: "100%", minWidth: 1024 }}>
            <ProductNavigation />
          </div>
        )}

        {shouldShowScrollOnlyProductNavigation && (
          <ProductNavigation
            forceFloating
            showFloatingAfterScroll
          />
        )}

        <PageTransitionFrame pathname={pathname}>
          {pageContent}
        </PageTransitionFrame>

        {/* Footer */}
        {!isLoginPage && !isRegisterRoute && !isAboutUnoPage && (
          <div
            style={{
              /* Desktop Responsive
                 - Footer wrapper도 100vw 대신 부모 기준 100% 사용 */
              width: "100%",
              minWidth: 1024,
              flexShrink: 0,
              overflow: "hidden",
            }}
          >
            <Footer />
          </div>
        )}
      </div>
    </>
  );
}
