# UNO Renewal Handoff - 2026-07-14

이 문서는 다른 기기나 새 Codex 세션에서 바로 이어서 작업하기 위한 인수인계 문서입니다.

목표는 대화 기억에 의존하지 않고, 어떤 파일, 어떤 DB 테이블, 어떤 ID가 프런트/백엔드에서 어떤 역할을 하는지 명확히 남기는 것입니다.

## 현재 큰 방향

- 프런트는 React/Vite SPA입니다.
- 기존 우노트래블은 PHP + Gnuboard 기반입니다.
- 리뉴얼 프런트는 새 UI를 유지하되, 회원/예약/결제/문의의 실제 데이터는 기존 Gnuboard DB와 호환되어야 합니다.
- 리뉴얼 API는 `backend-bridge/php-api`에 있고, 서버 업로드 시 `/www/api` 아래에 대응됩니다.
- 리뉴얼 관리자 페이지는 `backend-bridge/admin-pages/renewal`에 있고, 서버 업로드 시 `/www/admin/renewal` 아래에 대응됩니다.
- 프런트 소스 변경 시 반드시 `npm run build` 후 `dist/` 전체를 업로드해야 합니다.

## 배포 경로 규칙

| 로컬 경로 | 서버 경로 | 역할 |
| --- | --- | --- |
| `dist/` | 웹 루트 정적 파일 위치 | React/Vite 빌드 결과. 프런트 변경 시 전체 업로드 필요 |
| `backend-bridge/php-api/*` | `/www/api/*` | React가 호출하는 JSON API |
| `backend-bridge/admin-pages/renewal/*` | `/www/admin/renewal/*` | 리뉴얼 관리자 PHP 화면 |
| `src/*` | 서버 직접 업로드 대상 아님 | 빌드 전 소스. `dist/`에 반영됨 |

중요:

- `dist/index.html`은 새 JS 파일명을 참조합니다. JS 파일만 올리면 안 됩니다.
- 2026-07-14 마지막 빌드 기준 JS는 `dist/assets/index-9aJiOmW4.js`였습니다.
- PHP만 변경한 경우에는 빌드가 필요 없고, 해당 PHP 파일만 서버 대응 경로에 올리면 됩니다.
- 프런트 TS/TSX/CSS가 바뀌면 빌드 후 `dist/` 전체 업로드가 필요합니다.
- `package-lock.json`은 사용자가 `npm install`을 실행하면서 생긴 미추적 파일입니다. 현재 repo 기준 lockfile은 `pnpm-lock.yaml`입니다.

## GitHub 상태

원격 저장소:

- `https://github.com/wognsben/uno_back0713`

2026-07-14 푸시된 주요 커밋:

| Commit | 내용 |
| --- | --- |
| `7a184a5` | 리뉴얼 관리자, 회원/문의/예약 관련 1차 개선 |
| `e7e1408` | 예약 draft legacy ID, 필수 컬럼, tourTime 문제 수정 |
| `d644ec4` | 마이페이지 장바구니/예약목록 실제 API 연결 |

## 회원가입/로그인 호환성

### 결론

리뉴얼 회원가입은 기존 `/bbs/bbs/register_form_update.php`로 HTML form POST를 보내는 방식이 아닙니다.

현재 구조:

- 프런트: React 회원가입 화면
- API: `/api/auth/register.php`
- 저장 DB: 기존 `g5_member`
- 로그인 API: `/api/auth/login.php`
- 세션: Gnuboard 세션 `ss_mb_id`, `ss_mb_key` 호환 필요

따라서 URL 흐름은 기존과 달라도 됩니다. 하지만 DB 저장 규칙과 로그인 검증은 기존 Gnuboard와 호환되어야 합니다.

### 기존 파일 역할

| 기존 파일 | 영역 | 역할 | 리뉴얼 대응 |
| --- | --- | --- | --- |
| `/www/contents/regis_form.php` | legacy frontend wrapper | 기존 회원가입 진입 화면 | 디자인/문구 참고 |
| `/www/bbs/bbs/register_form.php` | legacy frontend/backend bridge | Gnuboard 회원가입 폼 출력 | 필드명/검증 규칙 참고 |
| `/www/bbs/bbs/register_form_update.php` | legacy backend | `g5_member` 회원 생성 | `/api/auth/register.php` 내부 동작이 호환되어야 함 |
| `/www/contents/login.php` | legacy frontend | 기존 로그인 화면 | 디자인/필드 참고 |
| `/www/bbs/bbs/login.php` | legacy frontend/backend bridge | Gnuboard 로그인 화면/연결 | 참고 |
| `/www/bbs/bbs/login_check.php` | legacy backend | `get_member()` + `check_password()` + 세션 생성 | `/api/auth/login.php`가 이 기준과 호환되어야 함 |
| `/www/bbs/bbs/logout.php` | legacy backend | Gnuboard 로그아웃 세션 정리 | 리뉴얼 로그아웃 API/프런트 세션 정리 참고 |
| `/www/bbs/bbs/password*.php` | legacy backend/frontend | 비밀번호 찾기/변경 | 추후 구현 참고 |
| `/www/bbs/bbs/member_confirm.php` | legacy backend/frontend | 회원정보 수정 전 비밀번호 확인 | 추후 마이페이지 보안 확인 참고 |
| `/www/bbs/bbs/member_leave.php` | legacy backend | 회원 탈퇴 | 추후 구현 참고 |

### 회원 DB 테이블

| 테이블 | 역할 |
| --- | --- |
| `g5_member` | 기존 Gnuboard 회원 테이블. 기존 사이트와 리뉴얼 사이트가 같은 회원으로 로그인하려면 반드시 이 테이블을 써야 함 |

### 회원가입에서 확인된 DB 필수 컬럼

서버의 `SHOW CREATE TABLE g5_member` 확인 결과, 아래 컬럼은 `NOT NULL`인데 기본값이 없어 INSERT에 명시해야 했습니다.

| 컬럼 | 처리 |
| --- | --- |
| `mb_signature` | 빈 문자열 저장 |
| `mb_profile` | 빈 문자열 저장 |
| `mb_memo` | 빈 문자열 저장 |
| `mb_lost_certify` | 빈 문자열 저장 |

이 누락 때문에 회원가입 API에서 500 오류가 발생했습니다.

확인된 오류 예:

- `Field 'mb_signature' doesn't have a default value`
- `Field 'mb_memo' doesn't have a default value`
- `Field 'mb_lost_certify' doesn't have a default value`

### 로그인 호환 기준

리뉴얼 로그인은 아래 기준을 지켜야 합니다.

- `g5_member`에서 회원 조회
- 기존 `check_password()` 기준으로 비밀번호 검증
- 성공 시 Gnuboard 세션 생성
- 기존 사이트와 리뉴얼 사이트 양쪽에서 로그인 가능
- 백엔드 회원목록에서도 같은 회원으로 보여야 함

## 예약 생성 흐름

### 현재 결론

예약은 기존 `tour_reg` 테이블에 저장됩니다.

리뉴얼 프런트에서 예약하기를 누르면:

1. 상품 상세에서 날짜/옵션/인원 선택
2. `/api/reservations/draft.php` 호출
3. `tour_reg`에 초안 row 생성
4. 예약 입력/확정 화면으로 이동
5. 최종 저장 후 관리자 백엔드에서 예약대기로 확인

### 주요 리뉴얼 파일

| 파일 | 영역 | 역할 |
| --- | --- | --- |
| `src/api/reservationApi.ts` | frontend API client | 예약, 장바구니, 마이페이지 API 호출 정의 |
| `src/pages/product/product_com/reservationStore.ts` | frontend state helper | 예약 진행용 payload 생성/임시 저장 |
| `src/pages/product/product_com/reservationUtils.ts` | frontend helper | 날짜/가격/legacy ID 변환 보조 |
| `src/pages/product/ProductDetail.tsx` | frontend page | 상품 상세 전체 흐름 |
| `src/pages/product/product_com/ReservationModule.tsx` | frontend component | 상품 상세 본문 예약 모듈 |
| `src/pages/product/product_com/Booking_side.tsx` | frontend component | 하단/플로팅 예약 CTA |
| `backend-bridge/php-api/reservations/draft.php` | backend API | `tour_reg` 예약 초안 생성 |
| `backend-bridge/php-api/reservations/detail.php` | backend API | 예약 상세 조회 |
| `backend-bridge/php-api/reservations/confirm.php` | backend API | 예약 최종 확정/저장 |

### 예약 관련 DB 테이블

| 테이블 | 역할 |
| --- | --- |
| `tour_reg` | 기존 예약 메인 테이블. 데일리투어/세미패키지 예약, 장바구니도 여기 저장 |
| `tour_fee` | 데일리투어 요금 옵션 테이블 |
| `v2_pkgTour` | 세미패키지 출발 일정/요금 테이블 |
| `g5_write_product` 또는 product write table | 기존 상품 게시판 테이블. `wr_id`가 legacy product ID |

### ID 매핑 규칙

| 값 | 프런트/백엔드 위치 | 의미 |
| --- | --- | --- |
| `productId` | React | 리뉴얼 프런트용 문자열 상품 ID |
| `legacyProductId` | React/API/PHP | 기존 상품 `wr_id`, `tour_reg.pid`에 저장 |
| `legacyFeeOptionId` | React/API | 데일리투어의 기존 `tour_fee.id` |
| `legacyPackageScheduleId` | React/API | 세미패키지의 기존 `v2_pkgTour.id` |
| `items[].feeId` | API payload | daily는 `tour_fee.id`, semi는 `v2_pkgTour.id`로 사용 |
| `tour_reg.pid` | DB | 기존 상품 ID, 즉 `legacyProductId` |
| `tour_reg.fee_id` | DB | 선택 요금/출발 일정 ID를 pipe 형식으로 저장 |

### 2026-07-14 해결한 예약 오류

| 증상 | 원인 | 수정 방향 |
| --- | --- | --- |
| 데일리투어: `선택한 요금 옵션을 찾을 수 없습니다.` | 프런트가 기존 `tour_fee.id`와 다른 값을 보냄 | 상품 상세 API 응답부터 legacy fee ID를 보존하고 draft payload에 전달 |
| 세미패키지: `출발 일정 ID가 필요합니다.` | `legacyPackageScheduleId`가 payload에서 유실됨 | `items[].legacyPackageScheduleId`와 `items[].feeId`에 기존 `v2_pkgTour.id` 전달 |
| `Field 'adminMemo' doesn't have a default value` | `tour_reg` 필수 컬럼 누락 | `adminMemo` 기본값 추가 |
| `Field 'adminMemoCancel' doesn't have a default value` | `tour_reg` 필수 컬럼 누락 | `adminMemoCancel` 기본값 추가 |
| `Data too long for column 'tourTime'` | `tourTime varchar(5)`에 긴 문자열 저장 시도 | HH:MM 정규화 추가 |

### `tour_reg` INSERT에서 확인된 필수/주의 컬럼

| 컬럼 | 처리 |
| --- | --- |
| `adminMemo` | 없으면 빈 문자열 |
| `adminMemoCancel` | 빈 문자열 |
| `memCancelDate` | 0 |
| `adminCancelDate` | 0 |
| `tourTime` | `HH:MM`만 저장. 날짜 포함/초 포함 문자열 금지 |
| `status` | 예약 상태. 장바구니는 `cart`, 예약 초안은 `booking`, 실제 예약은 기존 상태 코드 |
| `nation` | 현재 daily/semi 구분에도 사용 |

## 장바구니 흐름

### 요구사항

- 아무 상품도 담지 않은 회원은 빈 장바구니
- 상품 상세에서 장바구니 담기 실행한 항목만 표시
- 현재 로그인한 회원의 항목만 조회
- 삭제 버튼 필요
- 삭제 시 해당 장바구니 row만 삭제 또는 숨김 처리
- 빈 문구: `장바구니에 담긴 투어가 없습니다.`

### 현재 구현

| 파일 | 영역 | 역할 |
| --- | --- | --- |
| `src/pages/mypage/MyCart.tsx` | frontend page | 실제 `getCart()` API 응답만 표시. mock 제거 완료 |
| `src/api/reservationApi.ts` | frontend API client | `getCart`, `createCartReservation`, `deleteCartReservation` 정의 |
| `src/pages/product/product_com/ReservationModule.tsx` | frontend component | 장바구니 담기 버튼이 `/api/cart/index.php` POST 호출 |
| `src/pages/product/product_com/Booking_side.tsx` | frontend component | 플로팅 CTA 장바구니 담기도 `/api/cart/index.php` POST 호출 |
| `backend-bridge/php-api/cart/index.php` | backend API | GET 장바구니 조회, POST 장바구니 row 생성 |
| `backend-bridge/php-api/cart/delete.php` | backend API | 현재 로그인 회원의 cart row 삭제 처리 |

### 장바구니 DB 조건

조회 조건:

```sql
where r.mb_id = 현재 로그인 회원 mb_id
  and r.status = 'cart'
  and (r.del_time = 0 or r.del_time is null or r.del_time < 111)
```

삭제 조건:

```sql
where id = rid
  and mb_id = 현재 로그인 회원 mb_id
  and status = 'cart'
```

삭제 방식:

- 실제 row를 hard delete하지 않고 `del_time = time()`으로 숨깁니다.

### 2026-07-14 제거한 mock/fallback

| 위치 | 제거 내용 |
| --- | --- |
| `src/pages/mypage/MyCart.tsx` | `CART-001`, `CART-002` 샘플 장바구니 제거 |
| `src/pages/mypage/MyCart.tsx` | “기존 tour_reg status='cart' 데이터와 연결될 예정입니다” 문구 제거 |
| `src/pages/mypage/MyReservation.tsx` | fallback 예약 샘플 제거 |
| `src/pages/mypage/MyReservation.tsx` | `reservationStore` 기반 예약목록 fallback 제거 |

## 마이페이지 예약목록 흐름

### 요구사항

- 관리자 계정이라도 마이페이지에서는 전체 회원 예약을 보여주면 안 됩니다.
- 마이페이지 예약목록은 현재 로그인한 `mb_id`의 예약만 보여야 합니다.
- 장바구니 row는 예약목록에서 제외해야 합니다.
- 전체 예약 관리는 관리자 페이지에서만 해야 합니다.

### 현재 구현

| 파일 | 영역 | 역할 |
| --- | --- | --- |
| `src/pages/mypage/MyReservation.tsx` | frontend page | `/api/my/reservations.php` 응답만 표시. fallback 제거 완료 |
| `backend-bridge/php-api/my/reservations.php` | backend API | 현재 로그인 회원의 예약목록만 조회 |

조회 조건:

```sql
where r.mb_id = 현재 로그인 회원 mb_id
  and r.status not in ('cart', 'booking')
  and (r.del_time = 0 or r.del_time is null or r.del_time < 111)
```

상태:

- `cart`: 장바구니. 예약목록에서 제외
- `booking`: 예약 초안. 예약목록에서 제외
- `1`, `2`, `11`, `3`, `9`, `91`, `99`: 기존 예약 상태 코드로 사용
- 화면에는 API의 `statusLabel`을 표시

## 리뉴얼 관리자 백엔드

### 주요 파일

| 파일 | 서버 경로 | 역할 |
| --- | --- | --- |
| `backend-bridge/admin-pages/renewal/_layout.php` | `/www/admin/renewal/_layout.php` | 리뉴얼 관리자 공통 레이아웃/헤더 |
| `backend-bridge/admin-pages/renewal/index.php` | `/www/admin/renewal/index.php` | 관리자 대시보드 |
| `backend-bridge/admin-pages/renewal/members.php` | `/www/admin/renewal/members.php` | 회원 목록 |
| `backend-bridge/admin-pages/renewal/member-detail.php` | `/www/admin/renewal/member-detail.php` | 회원 상세 |
| `backend-bridge/admin-pages/renewal/reservations.php` | `/www/admin/renewal/reservations.php` | 예약 목록/캘린더 |
| `backend-bridge/admin-pages/renewal/payments.php` | `/www/admin/renewal/payments.php` | 결제/KSNET 조회 화면 |
| `backend-bridge/admin-pages/renewal/inquiries.php` | `/www/admin/renewal/inquiries.php` | 1:1 문의 목록/상세/답변 |
| `backend-bridge/admin-pages/renewal/visits.php` | `/www/admin/renewal/visits.php` | 방문자 현황 |
| `backend-bridge/admin-pages/renewal/product-edit.php` | `/www/admin/renewal/product-edit.php` | 상품 수정 |

### 2026-07-14 처리한 관리자 개선

| 항목 | 상태 |
| --- | --- |
| 헤더 hover dropdown 공백으로 hover 끊기는 문제 | 수정 완료 |
| 1:1 문의 목록에서 문의 클릭 가능 | 수정 완료 |
| 문의 상세에서 목록/삭제/답변/수정 흐름 | 수정 완료 |
| 문의 기존 화면의 불필요 버튼 정리 | 수정 완료 |
| 문의 목록 페이지네이션 | 수정 완료 |
| 문의 답변/수정 보안 토큰 오류 | 기존 `write.php`, `board.php` 흐름 참고해 수정 |
| Reservation Control 캘린더 전환 시 새로고침 느낌 완화 | 수정 완료 |
| 예약 페이지 페이지네이션 1 2 3 4 5 + 처음/끝 | 수정 완료 |
| 세미패키지/데일리 캘린더 분리 | 수정 완료 |
| 회원목록 상태 색상 표시 | 수정 완료 |
| 전체 리뉴얼 관리자 top 버튼 | 수정 완료 |

## 1:1 문의 흐름

### 기존 파일 역할

| 기존 파일 | 영역 | 역할 | 리뉴얼 대응 |
| --- | --- | --- | --- |
| `/www/admin/board.php` | legacy admin | Gnuboard 게시판 목록/상세 이동 기준. `cusTour` 문의 관리 참고 | `renewal/inquiries.php` |
| `/www/admin/write.php` | legacy admin | 게시글/답변 작성, 수정, 토큰 처리 참고 | 답변 등록/수정/삭제 API 처리 참고 |

### 테이블/게시판

| 값 | 의미 |
| --- | --- |
| `bo_table=cusTour` | 기존 1:1 문의 게시판 |
| `wr_id` | 문의 글 ID |
| 답변 | 기존 Gnuboard 게시판 답변/댓글 구조를 따라야 함 |

## KSNET 결제 흐름

### 운영자가 설명한 실제 업무 흐름

예약 후 결제 방법은 2개입니다.

1. 계좌 입금
   - 고객 예약 후 12시간 이내 입금
   - 관리자가 백엔드에서 입금 내역 확인
   - 관리자가 예약 확정 처리

2. 카드 결제
   - 고객이 마이페이지에서 카드 결제
   - KSNET 결제창과 연동
   - 결제 성공 시 백엔드 예약 목록의 결제 상태가 카드로 변경
   - 예약 상세에서 KSNET 승인번호/승인일시 확인
   - 취소 시 관리자가 “신용카드 취소하기” 클릭
   - KSNET 승인 취소 연동

### 기존 KSNET 파일 역할

| 기존 파일 | 영역 | 역할 | 리뉴얼 대응 |
| --- | --- | --- | --- |
| `/www/kspay.php` | legacy frontend/backend bridge | KSNET 카드 결제 요청 진입 | 마이페이지 카드결제 버튼 구현 시 기준 |
| `/www/kspay_result.php` | legacy backend | KSNET 결제 승인 결과 수신/처리 | 결제 성공 후 `tour_reg` 반영 기준 |
| `/www/kspay_wh_rcv.php` | legacy backend | KSNET webhook/수신 처리로 추정 | 운영 반영 전 추가 확인 필요 |
| `/www/KSPayApprovalCancel.inc` | legacy library | KSNET 승인 취소 라이브러리 | 카드 취소 API 기준 |
| `/www/KSPayCancelPost.php` | legacy backend | 카드 승인 취소 요청 처리 | 리뉴얼 예약 상세 취소 버튼 기준 |
| `/www/KSPayWebHost.inc` | legacy library | KSNET 통신 공통 라이브러리 | 결제/취소 연동 공통 |
| `/www/admin/kscardPayList.php` | legacy admin | 관리자 KSNET 카드 결제 내역 목록 | `renewal/payments.php` 기준 |
| `/www/admin/include_files/_getKSCardPayData.php` | legacy admin include | KSNET 결제 데이터 조회 | 결제 조회 쿼리 기준 |
| `/pay.php`, `/pay_result.php` | legacy payment | 별도/구형 결제 흐름 가능성 | 실제 사용 여부 확인 필요 |

### KSNET 현재 상태

- 리뉴얼 백엔드 `payments.php`는 조회 틀을 만든 상태입니다.
- 아직 프런트 마이페이지 카드결제 버튼과 KSNET 결제창 연결은 최종 구현 전입니다.
- 카드 취소는 실제 승인 취소가 발생하므로 운영 서버에서 테스트 예약으로만 검증해야 합니다.

## 프런트 마이페이지 결제/예약 상태 UX

### 확인된 요구

- 프런트 마이페이지에서 예약 직후 `예약확인`으로 보이면 고객이 확정으로 오해합니다.
- 백엔드에서 입금확인 전이면 프런트도 `예약대기`로 보여야 합니다.
- 관리자가 입금 확인/확정하면 이후 프런트도 `예약확인` 또는 확정 상태로 보여야 합니다.
- 카드 결제는 마이페이지 예약 상세/목록에서 결제 버튼이 필요할 가능성이 큽니다.

### 현재 상태

- 예약 생성 후 백엔드에서 예약대기 상태로 들어가는 것은 확인됐습니다.
- 마이페이지 예약목록은 서버 API의 `statusLabel`을 표시합니다.
- 카드 결제 UI/KSNET 연결은 다음 작업 대상입니다.

## 빌드/검증 기록

2026-07-14 마지막 장바구니/예약목록 수정 후 검증:

```powershell
C:\Users\user\Documents\Codex\2026-07-14\d\work\php85\php.exe -l backend-bridge/php-api/cart/index.php
C:\Users\user\Documents\Codex\2026-07-14\d\work\php85\php.exe -l backend-bridge/php-api/cart/delete.php
C:\Users\user\Documents\Codex\2026-07-14\d\work\php85\php.exe -l backend-bridge/php-api/my/reservations.php
npm run build
```

결과:

- PHP 문법 검사 통과
- Vite build 성공
- 큰 chunk 경고만 있음. 기능 실패 아님

## 다음 작업 우선순위

1. 서버에 마지막 변경분 업로드 후 실제 로그인 회원별 테스트
   - 신규 회원 장바구니 0건
   - 상품 상세에서 장바구니 담기 후 1건 표시
   - 장바구니 삭제 후 DB/화면에서 제거
   - 예약 생성 후 본인 예약목록에만 표시
   - 다른 회원 로그인 시 이전 회원 데이터 미노출
   - 관리자 계정 마이페이지에서도 전체 예약 미노출

2. KSNET 결제 프런트/백엔드 연결
   - 마이페이지 예약 상세에 카드 결제 버튼
   - 기존 `kspay.php`, `kspay_result.php` 흐름 분석
   - 결제 성공 후 `tour_reg` 결제 상태 반영
   - 관리자 예약 상세에서 승인번호/승인일시 표시
   - 카드 취소 버튼은 테스트 예약으로만 검증

3. 모바일/태블릿 프런트 QA
   - 사용자가 별도 1~2일 작업으로 예상
   - 예약/마이페이지/상품 상세 우선

4. 기존 우노트래블 파일 정리 판단
   - 백엔드와 프런트 연동 검증 완료 후 삭제 여부 판단
   - KSNET, Gnuboard 회원/게시판/예약 호환 파일은 삭제 전 역할 재확인 필요

## 앞으로 문서화 규칙

큰 변화가 있을 때마다 아래를 이 문서 또는 후속 handoff 문서에 추가해야 합니다.

- 수정한 파일
- 서버 업로드 경로
- 관련 DB 테이블
- 관련 ID 규칙
- 기존 우노트래블 파일과의 대응 관계
- 테스트한 명령
- 실제 운영 서버에서 확인한 결과
- 아직 확인하지 못한 리스크

특히 다음 영역은 대화 기억에 의존하면 안 됩니다.

- 회원가입/로그인
- 예약 생성
- 마이페이지 예약목록
- 장바구니
- KSNET 결제/취소
- 1:1 문의
- 관리자 예약/회원/결제 화면

## 2026-07-14 추가 확인 사항 - 상품 요금 구조

다음 작업부터는 아래 문구로 시작하면 됩니다.

```text
현재까지 확정된 사항입니다.

(기존 내용은 그대로 유지)

[추가 확인 사항]

1.
fee_org = 정상가격

2.
fee_subject = 신청구분

3.
fee1 = 홈페이지 예약금
```

누적 업데이트 방식:

- 이 문서는 기존 내용을 삭제하지 않고 아래쪽에 확정 사항을 계속 추가합니다.
- 상품 상세페이지 프런트와 리뉴얼 관리자 백엔드의 요금 구조는 기존 운영 DB 기준을 우선합니다.
- 새 작업을 시작할 때는 이 문서의 기존 예약/장바구니/마이페이지 흐름과 아래 요금 매핑을 함께 기준으로 봅니다.

이번에 추가 확정된 DB 매핑:

| DB 필드 | 의미 | 적용 위치 |
| --- | --- | --- |
| `g5_write_product.fee_org` | 정상가격 | 상품 전체 정상가격/전체 상품가 표시 |
| `tour_fee.fee_subject` | 신청구분 | 요금 옵션명, 예약 인원 선택 구분 |
| `tour_fee.fee1` | 홈페이지 예약금 | 프런트 예약금 표시 및 예약금 합계 계산 |

주의:

- `fee_org`는 정상가격이며 `wr_4` 가격 안내 문구와 섞지 않습니다.
- `fee_subject`는 임의 가공하지 말고 운영 DB 값을 기준으로 표시합니다.
- `fee1`은 사용자가 홈페이지에서 확인하고 예약 시 합산되는 예약금입니다.

## 2026-07-14 구현 업데이트 - tour_fee 요금 매핑 반영

이번 구현에서 리뉴얼 관리자와 상품 상세 프런트의 요금 구조를 기존 운영 DB 기준으로 보강했습니다.

관리자 상품 편집:

- `label`은 `tour_fee.fee_subject`에 저장합니다. UI 라벨은 신청구분으로 정리했습니다.
- `deposit`은 `tour_fee.fee1`에 저장합니다. UI 라벨은 홈페이지 예약금으로 정리했습니다.
- `localPayment`은 `tour_fee.fee2`에 저장합니다. 숫자뿐 아니라 `-` 같은 기존 문자열 값을 보존합니다.
- `extraPayment`은 `tour_fee.fee3`에 저장합니다. 숫자 강제 변환을 하지 않고 기존 문자열 값을 보존합니다.
- `isDefault`는 `tour_fee.is_first`에 저장합니다. UI 라벨은 대표요금으로 정리했습니다.
- `ticketFeeId`는 `tour_fee.fee_ticket_id`에 저장합니다.
- `originalFeeText`는 기존 호환 필드명은 유지하되 실제 저장 위치는 `g5_write_product.fee_org`입니다.

상품 상세 API:

- 데일리투어 `feeOptions`는 `tour_fee`의 전체 유효 옵션을 `id asc` 순서로 내려줍니다.
- `feeOptions[].id`는 실제 `tour_fee.id`입니다.
- `feeOptions[].subject` / `label`은 `fee_subject`입니다.
- `feeOptions[].deposit`은 `fee1`입니다.
- `feeOptions[].advanceLocalPayment`은 `fee2`입니다.
- `feeOptions[].localPayment`은 `fee3`입니다.
- `feeOptions[].ticketFeeId`는 `fee_ticket_id`입니다.
- `feeOptions[].isPrimary` / `isDefault`는 `is_first === 'Y'` 기준입니다.
- `originalPrice`는 `g5_write_product.fee_org`입니다.
- `priceDescription`은 `g5_write_product.wr_4`입니다.

프런트 예약/장바구니 payload:

- 데일리투어는 신청구분별 인원을 선택할 수 있도록 `items: [{ feeId, personCount }]` 구조를 지원합니다.
- `feeId`는 프런트 임의 옵션 ID가 아니라 실제 `tour_fee.id`입니다.
- 선택 인원이 0명인 옵션은 payload에서 제외합니다.
- 모든 신청구분 합계가 0명이면 장바구니/예약 진행 버튼을 비활성화합니다.
- 세미패키지는 기존 `v2_pkgTour.id` 기반 단일 일정/인원 구조를 유지합니다.

검증:

- `php -l backend-bridge/php-api/admin/product-editor.php` 통과
- `php -l backend-bridge/php-api/products/detail.php` 통과
- `pnpm build` 통과
 
## 2026-07-15 이번 작업에서 새롭게 확인된 사실

- 세미패키지 상품 상세의 `2,890,000`은 프런트 정적 데이터 `SEMI_DETAIL_DATA.basePrice`/`availableDates[].price`에도 존재하지만, 운영 API 기준 실제 총 상품금액은 `v2_pkgTour.price -> packageSchedules[].totalPrice -> AvailableDate.totalPrice/price` 흐름으로 내려와야 한다.
- `backend-bridge/php-api/products/detail.php`의 세미패키지 일정 조회는 `v2_pkgTour`에서 `fee_1`, `fee_2`, `fee_3`, `fee_air`, `price`를 읽는다.
- 세미패키지 일정 가격 의미는 `fee_1=예약금`, `fee_2=중도금`, `fee_3=잔금`, `fee_air=항공요금`, `price=총 상품금액`으로 확인했다.
- 기존 프런트 매핑은 세미패키지 일정 가격이 누락되거나 일정 매칭이 실패하면 `remoteDefaultFee.deposit`, `remoteBasePrice`, `baseDetailData.basePrice`로 fallback할 수 있었다. 이 구조는 일정별 가격 오류를 숨길 수 있어 세미패키지 일정 가격 계산에서는 제거했다.
- `ReservationModule`과 `Booking_side`는 각각 별도 `useState`로 날짜/인원/feeCounts/cartAdded를 관리하고 있어, 한쪽에서 바꾼 신청구분별 인원 선택이 다른 쪽에 즉시 반영되지 않는 구조였다.
- 공통 선택 상태는 `useReservationSelection(productId)`로 통합했다. 관리 값은 `selectedDateId`, `peopleCount`, `feeCounts`, `isCartAdded`이다.
- 데일리투어 payload의 `items[].feeId`는 실제 `tour_fee.id`이고, 세미패키지 `legacyPackageScheduleId`와 예약 draft fallback `items[].feeId`는 실제 `v2_pkgTour.id`이다.

### 2026-07-15 수정 이력

- `src/pages/product/product_com/reservationUtils.ts`: `AvailableDate`에 세미패키지 일정별 가격 필드(`deposit`, `intermediatePayment`, `balance`, `airfare`, `totalPrice`)를 추가했다.
- `src/pages/product/product_com/reservationStore.ts`: `useReservationSelection(productId)` 공통 store를 추가해 본문 예약 모듈과 하단 예약바의 선택 상태를 단일 source of truth로 통합했다.
- `src/pages/product/ProductDetail.tsx`: 세미패키지 일정 매핑에서 `v2_pkgTour` 일정별 가격을 보존하고, 일정 가격 누락 시 상품 공통 가격으로 fallback하지 않도록 수정했다.
- `src/pages/product/product_com/ReservationModule.tsx`: 데일리 다중 신청구분 수량과 세미패키지 일정/인원 선택을 공통 store에 연결하고, 세미패키지 표시 금액을 예약금과 총 상품금액으로 구분했다.
- `src/pages/product/product_com/Booking_side.tsx`: 본문과 같은 공통 store를 사용하도록 수정하고, 데일리 다중 feeOptions 합계와 세미패키지 일정별 예약금/총액을 각각 분리 계산하도록 정리했다.
- `backend-bridge/php-api/products/detail.php`: 세미패키지 일정 응답에 `intermediatePayment`, `balance` alias를 추가해 API 필드 의미를 명확히 했다.
- `src/api/reservationApi.ts`: `PackageScheduleOption` 타입에 `intermediatePayment`, `balance`를 추가했다.

### 다음 작업 / 운영 확인 필요

- 운영 DB에서 같은 상품의 여러 `v2_pkgTour` 일정이 실제로 서로 다른 `fee_1/fee_2/fee_3/fee_air/price`를 갖는지 직접 확인해야 한다.
- 운영 상품 상세 API 응답의 `packageSchedules[]`와 화면 `availableDates[]`가 같은 `v2_pkgTour.id`로 매칭되는지 확인해야 한다.
- 운영 화면에서 데일리투어 신청구분 2개 선택 시 본문 예약 모듈과 하단 예약바가 모두 같은 총 인원/총 예약금을 표시하는지 확인해야 한다.
- 세미패키지 일정 변경 시 예약금, 중도금, 잔금, 항공요금, 총 상품금액, `legacyPackageScheduleId`가 모두 선택 일정 기준으로 바뀌는지 확인해야 한다.
