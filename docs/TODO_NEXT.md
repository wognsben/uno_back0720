# TODO NEXT

작성일: 2026-07-13

내일 다른 기기에서 이어서 작업하기 위한 인수인계 문서입니다.

## 현재 상태

- 상세페이지는 거의 구현 완료 상태입니다.
- Product Document는 탭 기반으로 정리했고, 코스 일정 탭은 제거했습니다.
- 가이드 선택 정보는 Product Hub에서 지정하고 상세페이지 Product Document의 가이드 소개 영역에서 표시하는 방향으로 연결했습니다.
- 예약/결제는 구조가 잡히는 중이며, 결제 정책 수정은 마지막 단계에서 다시 크게 손볼 예정입니다.
- 리뉴얼 관리자 백엔드는 회원, 예약, 문의, 결제, 방문자 현황까지 기본 흐름이 연결된 상태입니다.

## 다음 우선순위

### 1. 서버 업로드 후 리뉴얼 관리자 실제 동작 확인

- `/admin/renewal/index.php`
- `/admin/renewal/members.php`
- `/admin/renewal/member-detail.php`
- `/admin/renewal/reservations.php`
- `/admin/renewal/payments.php`
- `/admin/renewal/inquiries.php`
- `/admin/renewal/visits.php`

확인할 것:

- 메뉴 클릭 시 기존 백엔드가 아니라 리뉴얼 페이지로 이동하는지
- 회원 목록에서 `tour_fee_b2b` 테이블 오류가 더 이상 발생하지 않는지
- 예약 목록에서 세미패키지/데일리투어가 분리되어 보이는지
- 예약 캘린더가 가로 스크롤 없이 한 화면에 보이는지
- 1:1 문의와 KSNET 결제 내역 링크가 리뉴얼 페이지로 연결되는지
- 방문자 현황에서 일별/주간/월간/브라우저/접속자 목록 탭이 열리는지

### 2. Product Detail 최종 QA

- FAQ 한글 깨짐 확인
- Product Document 탭 한글 표시 확인
- 가이드 선택 상품에서 가이드 이름/이미지 노출 확인
- 다른 투어 상품 영역이 같은 상품 타입만 보여주는지 확인
  - 세미패키지 상세에서는 세미패키지만
  - 데일리투어 상세에서는 데일리투어만
- 포함/불포함, 예약 안내, 미팅 장소 탭 내용 확인

### 3. Product Hub 관리자 QA

- 가이드 선택 UI가 너무 길거나 불편하지 않은지 확인
- 검색 가능한 체크 리스트/칩 형태가 실제 운영자에게 충분히 편한지 확인
- 캘린더는 월 단위로 이전/다음 이동만 되는지 확인
- 예외 운영처럼 혼란을 주는 요소가 남아 있지 않은지 확인
- 보딩패스 입력에서 표시 메모가 제거되었는지 확인
- 보딩패스는 항공권이 아니라 프리미엄하게 보이기 위한 데코용 일정표임을 유지

### 4. 예약/결제 백엔드 보강

예약/결제는 아직 최종 확정 단계가 아닙니다.

남은 방향:

- 계좌 입금 예약은 관리자가 입금 확인 후 확정
- 카드 결제 예약은 마이페이지에서 KSNET 결제 후 상태가 카드로 변경
- 예약 상세에서 KSNET 승인번호/승인일시 확인
- 취소 시 “신용카드 취소하기” 버튼으로 KSNET 승인 취소 연결
- 결제/취소는 운영 실수 위험이 있으므로 실제 서버에서 테스트 계정으로만 확인

### 5. 회원관리 추가 보강

- 회원 상세에서 예약 히스토리 가독성 개선
- B2B 회원/가이드 회원 필터 실제 데이터 기준 재확인
- 회원 수정은 현재 기존 `member_form.php`로 연결되어 있으므로, 필요하면 리뉴얼 수정 화면을 별도로 구축

### 6. 대시보드 완성도 개선

- 예약/회원/문의/결제/방문자 현황 카드 클릭 동작 전체 점검
- 아직 기존 백엔드로 빠지는 링크가 있는지 전수 확인
- 기존 백엔드가 필요한 링크는 “기존 관리”처럼 명확히 표시
- 리뉴얼 관리자 화면 톤을 카드/테이블 기반으로 통일

## 빌드/배포 메모

프론트엔드 변경을 배포하려면 저장소 루트에서 실행합니다.

```powershell
cd "C:\Users\wogns\OneDrive\Desktop\uno_git"
pnpm run build
```

성공하면 `dist/` 전체를 서버 정적 파일 위치에 업로드합니다.

PHP 관리자/API 변경만 배포할 때는 `pnpm run build`가 필요 없습니다. 변경된 PHP 파일만 서버의 동일 경로에 업로드하면 됩니다.

## 오늘 기준 주요 업로드 후보

프론트엔드 빌드 결과:

- `dist/`

리뉴얼 관리자/PHP:

- `backend-bridge/admin-pages/renewal/_layout.php`
- `backend-bridge/admin-pages/renewal/index.php`
- `backend-bridge/admin-pages/renewal/visits.php`
- `backend-bridge/admin-pages/renewal/members.php`
- `backend-bridge/admin-pages/renewal/member-detail.php`
- `backend-bridge/admin-pages/renewal/reservations.php`
- `backend-bridge/admin-pages/renewal/payments.php`
- `backend-bridge/admin-pages/renewal/inquiries.php`
- `backend-bridge/admin-pages/renewal/product-edit.php`
- `backend-bridge/admin-pages/renewal/spam-moderation.php`
- `backend-bridge/php-api/admin/product-editor.php`
- `backend-bridge/php-api/admin/reservation-editor.php`
- `backend-bridge/php-api/products/detail.php`

프론트엔드 빌드에 반영되는 주요 소스:

- `src/api/reservationApi.ts`
- `src/pages/product/Infiniteother.tsx`
- `src/pages/product/ProductDetail.tsx`
- `src/pages/product/product_com/BoardingPass.tsx`
- `src/pages/product/product_com/ReservationModule.tsx`
- `src/pages/product/product_com/product_document.tsx`
- `src/pages/product/product_com/reservationStore.ts`

프론트엔드 소스는 서버에 직접 올리는 대상이 아니라, 빌드 후 `dist/`에 반영됩니다.

## 주의사항

- `npm run build` 또는 `pnpm run build`는 반드시 `package.json`이 있는 루트에서 실행합니다.
- 콘솔에서 한글이 깨져 보여도 실제 파일은 UTF-8일 수 있으니, 브라우저 또는 UTF-8 에디터에서 확인합니다.
- 결제 취소 버튼은 실제 KSNET 승인 취소와 연결될 수 있으므로 운영 서버에서는 테스트 예약으로만 검증합니다.
- 방문자 통계는 Gnuboard 방문 테이블 기준이므로 Google Analytics 또는 외부 접속로그 분석 시스템과 수치가 다를 수 있습니다.
