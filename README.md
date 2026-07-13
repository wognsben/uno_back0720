# UNO Travel Renewal

우노트래블 프론트엔드 리뉴얼과 Cafe24/Gnuboard 기반 관리자 백엔드 브릿지를 함께 관리하는 저장소입니다.

## 프로젝트 구조

- `src/`: Vite/React 프론트엔드 상세페이지, 상품 화면, 공통 UI
- `backend-bridge/php-api/`: 기존 PHP/Gnuboard 데이터를 프론트엔드와 리뉴얼 관리자에 맞춰 반환하는 API
- `backend-bridge/admin-pages/renewal/`: 리뉴얼 관리자 페이지
- `docs/`: 배포, 백엔드 구조, 다음 작업 인수인계 문서
- `dist/`: `pnpm run build` 또는 `npm run build` 후 생성되는 정적 배포 결과물

## 로컬 실행

```bash
pnpm install
pnpm run dev
```

`npm`을 사용할 수도 있지만, 이 저장소에는 `pnpm-lock.yaml`이 있으므로 `pnpm` 기준으로 맞추는 것을 권장합니다.

중요: 빌드/설치 명령은 반드시 `package.json`이 있는 저장소 루트에서 실행해야 합니다.

```powershell
cd "C:\Users\wogns\OneDrive\Desktop\uno_git"
pnpm run build
```

다른 폴더에서 실행하면 `Could not read package.json` 오류가 납니다.

## 배포 기준

프론트엔드 변경이 있을 때:

1. 저장소 루트에서 `pnpm run build`
2. 생성된 `dist/` 전체 업로드

PHP 관리자/API 변경이 있을 때:

1. `backend-bridge/` 아래 변경된 PHP 파일 업로드
2. 프론트엔드만 수정한 경우에는 PHP 파일 업로드 불필요
3. PHP만 수정한 경우에는 `pnpm run build` 불필요

## 2026-07-13 작업 요약

- 상세페이지 Product Document 탭 구조 정리
- Product Document 한글 깨짐 대응 및 코스 일정 탭 제거
- 가이드 선택/표시 구조 연결
- 다른 투어 상품 영역을 현재 상품 타입 기준으로 필터링
- FAQ 한글 깨짐 fallback 정리
- Product Hub 가이드 선택 UI 개선
- 캘린더 UI 단순화 및 세미패키지/데일리투어 예약 분리
- 보딩패스 입력 정리 및 “항공권 아님, 데코용 일정표” 성격 명확화
- 리뉴얼 관리자 회원 목록/회원 상세 구축
- 리뉴얼 관리자 예약 목록/예약 상세/캘린더 구축
- KSNET 카드 결제 내역 및 카드 취소 연결 준비
- 1:1 문의, 결제 내역, 회원관리 링크를 리뉴얼 관리자 흐름으로 연결
- 방문자 현황/접속 통계 리뉴얼 관리자 페이지 추가
- `tour_fee_b2b` 테이블이 없는 서버에서도 회원 목록이 Fatal error 없이 열리도록 방어 처리

## 다음 작업

다음 작업 우선순위와 확인 포인트는 `docs/TODO_NEXT.md`를 확인하세요.
