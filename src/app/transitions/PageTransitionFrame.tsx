import {
  useEffect,
  useLayoutEffect,
  useRef,
  useState,
  type ReactNode,
} from "react";

/* ==========================================================
   PageTransitionFrame.tsx

   React 기반 페이지 전환 프레임

   목적
   - 기존 자체 SPA Navigation 구조 유지
   - react-router-dom 사용 금지
   - About UNO → Contact 클릭 전환 구간에만 페이지 전환 레이어 적용
   - 페이지 컴포넌트 내부에서 wheel 라우팅을 직접 처리하지 않도록 분리
   - 스크롤 기반 자동 페이지 전환 금지

   작동 방식
   ------------------------------------------
   1. pathname 변경 감지
   2. 이전 children을 fixed layer로 보존
   3. 다음 children을 fixed layer로 동시에 렌더링
   4. CSS transform으로 current / next 전환
   5. 전환 종료 후 일반 문서 흐름으로 복귀

   Backend Hook
   ------------------------------------------
   백엔드 연동 없음.
   화면 전환 전용 UI 레이어이므로 데이터/API 로직과 분리한다.
========================================================== */

type TransitionDirection = "down" | "up";

type PageTransitionFrameProps = {
  pathname: string;
  children: ReactNode;
};

const ABOUT_PATH = "/about-uno";
const CONTACT_PATH = "/contact";
const TRANSITION_DURATION = 820;

function isAboutContactPath(path: string) {
  return path === ABOUT_PATH || path === CONTACT_PATH;
}

function getTransitionDirection(
  previousPathname: string,
  nextPathname: string,
): TransitionDirection {
  /*
    Click Transition Direction
    ------------------------------------------
    현재 정책은 /about-uno → /contact 클릭 이동만 사용한다.
    다만 브라우저 뒤로가기 등으로 /contact → /about-uno가 발생할 수 있으므로
    반대 방향 클래스는 보존한다.
  */
  if (previousPathname === CONTACT_PATH && nextPathname === ABOUT_PATH) {
    return "up";
  }

  return "down";
}

export default function PageTransitionFrame({
  pathname,
  children,
}: PageTransitionFrameProps) {
  const previousPathnameRef = useRef(pathname);
  const transitionTimerRef = useRef<number | null>(null);
  const isTransitioningRef = useRef(false);

  const [displayChildren, setDisplayChildren] = useState(children);
  const [previousChildren, setPreviousChildren] = useState<ReactNode | null>(
    null,
  );
  const [direction, setDirection] = useState<TransitionDirection>("down");
  const [isTransitioning, setIsTransitioning] = useState(false);
  const [isActive, setIsActive] = useState(false);
  const [previousScrollY, setPreviousScrollY] = useState(0);

  useLayoutEffect(() => {
    const previousPathname = previousPathnameRef.current;

    if (previousPathname === pathname) {
      setDisplayChildren(children);
      return;
    }

    const shouldTransition =
      isAboutContactPath(previousPathname) && isAboutContactPath(pathname);

    previousPathnameRef.current = pathname;

    if (!shouldTransition) {
      setPreviousChildren(null);
      setDisplayChildren(children);
      setIsTransitioning(false);
      setIsActive(false);
      isTransitioningRef.current = false;
      return;
    }

    const nextDirection = getTransitionDirection(previousPathname, pathname);

    setDirection(nextDirection);
    setPreviousChildren(displayChildren);
    setPreviousScrollY(window.scrollY || document.documentElement.scrollTop || 0);
    setDisplayChildren(children);
    setIsTransitioning(true);
    setIsActive(false);
    isTransitioningRef.current = true;

    window.requestAnimationFrame(() => {
      window.requestAnimationFrame(() => {
        setIsActive(true);
      });
    });

    if (transitionTimerRef.current) {
      window.clearTimeout(transitionTimerRef.current);
    }

    transitionTimerRef.current = window.setTimeout(() => {
      setPreviousChildren(null);
      setIsTransitioning(false);
      setIsActive(false);
      isTransitioningRef.current = false;

      /*
        Scroll Reset After Transition
        ------------------------------------------
        About 하단에서 Contact로 이동할 때 이전 scrollY가 Contact에 남으면
        다음 페이지가 중간 위치에서 보이므로 전환 완료 후 최상단으로 고정한다.
      */
      window.scrollTo({ top: 0, left: 0, behavior: "auto" });
    }, TRANSITION_DURATION);
  }, [pathname]);

  useEffect(() => {
    return () => {
      if (transitionTimerRef.current) {
        window.clearTimeout(transitionTimerRef.current);
      }
    };
  }, []);

  if (!isTransitioning || !previousChildren) {
    return (
      <div data-transition="wrapper" className="unotravel-transition-wrapper">
        <div
          data-transition="container"
          data-namespace={pathname}
          className="unotravel-transition-container"
        >
          {displayChildren}
        </div>
      </div>
    );
  }

  const previousLayerClass = [
    "unotravel-transition-layer",
    "unotravel-transition-layer-current",
    `is-${direction}`,
    isActive ? "is-active" : "",
  ].join(" ");

  const nextLayerClass = [
    "unotravel-transition-layer",
    "unotravel-transition-layer-next",
    `is-${direction}`,
    isActive ? "is-active" : "",
  ].join(" ");

  return (
    <div
      data-transition="wrapper"
      className="unotravel-transition-wrapper is-transitioning"
    >
      <div
        data-transition="container"
        data-namespace={pathname}
        className="unotravel-transition-container unotravel-transition-placeholder"
        aria-hidden="true"
      >
        {displayChildren}
      </div>

      <div className={previousLayerClass} aria-hidden="true">
        <div
          className="unotravel-transition-layer-inner"
          style={{ transform: `translate3d(0, ${-previousScrollY}px, 0)` }}
        >
          {previousChildren}
        </div>
      </div>

      <div className={nextLayerClass} aria-hidden="true">
        <div className="unotravel-transition-layer-inner">{displayChildren}</div>
      </div>
    </div>
  );
}
