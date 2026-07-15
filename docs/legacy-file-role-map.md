# Legacy File Role Map

이 문서는 기존 우노트래블 파일을 받을 때마다 해당 파일이 어떤 프런트/백엔드 흐름에서 어떤 역할을 하는지 누적 기록하기 위한 작업 메모입니다.

목표는 기존 코드를 그대로 복사하는 것이 아니라, 리뉴얼에서 놓치면 안 되는 실제 업무 흐름과 데이터 계약을 분리해 보존하는 것입니다.

## 판단 기준

| 구분 | 의미 |
| --- | --- |
| Legacy frontend | 기존 PHP가 직접 HTML 화면을 출력하거나 폼을 구성하는 영역 |
| Legacy backend | 기존 PHP가 DB 저장, 로그인 세션, 결제 승인/취소, 관리자 처리 등을 수행하는 영역 |
| Renewal frontend | React/Vite 기반 화면과 사용자 인터랙션 |
| Renewal backend | `backend-bridge/php-api`, `backend-bridge/admin-pages/renewal` 아래의 PHP API/관리자 화면 |

## 회원가입 / 로그인

| 기존 파일 | 영역 | 역할 | 리뉴얼 대응 |
| --- | --- | --- | --- |
| `/www/contents/regis_form.php` | Legacy frontend wrapper | 기존 회원가입 화면으로 진입하는 콘텐츠 래퍼. 실제 저장 로직은 직접 갖고 있지 않고 Gnuboard 회원가입 폼 쪽으로 연결된다. | 리뉴얼 회원가입 화면의 참고 대상은 될 수 있지만, 저장 로직의 원본은 아니다. |
| `/www/bbs/bbs/register_form.php` | Legacy frontend/backend bridge | 약관 동의 확인 후 회원가입 폼 스킨을 출력하고 `register_form_update.php`를 저장 주소로 지정한다. | 리뉴얼 SPA에서는 이 PHP 폼 출력 흐름을 직접 쓰지 않는다. 필요한 검증 규칙만 추출한다. |
| `/www/bbs/bbs/register_form_update.php` | Legacy backend | 기존 우노트래블 회원가입의 핵심 저장 처리. `g5_member`에 회원을 생성하고 Gnuboard 암호화, CAPTCHA, 이메일 인증, 세션 설정, 포인트/메일 흐름을 처리한다. | 리뉴얼 `/api/auth/register.php`가 기능적으로 가장 가깝게 맞춰야 하는 원본 기준이다. 단, SPA이므로 응답은 JSON이어야 한다. |
| `/www/contents/login.php` | Legacy frontend | 기존 로그인 화면. `mb_id`, `mb_password`를 받아 Gnuboard 로그인 처리로 보낸다. | 리뉴얼 로그인 화면의 필드 의미와 UX 참고 대상. |
| `/www/bbs/bbs/login.php` | Legacy frontend/backend bridge | Gnuboard 로그인 페이지/처리 연결부. | 리뉴얼 로그인 API가 어떤 필드명을 기존과 맞춰야 하는지 확인할 때 참고한다. |
| `/www/bbs/bbs/login_check.php` | Legacy backend | 기존 로그인 검증의 핵심. `get_member($mb_id)`, `check_password()`로 검증하고 `ss_mb_id`, `ss_mb_key` 세션을 설정한다. | 리뉴얼 `/api/auth/login.php`가 반드시 같은 회원 테이블과 같은 암호 검증 방식을 사용해야 한다. |
| `/www/bbs/bbs/logout.php` | Legacy backend | 기존 Gnuboard 로그아웃 세션 정리. | 리뉴얼 로그아웃 API와 세션 공유 여부 확인 대상. |
| `/www/bbs/bbs/member_confirm.php` | Legacy backend/frontend | 회원정보 수정/탈퇴 전 비밀번호 재확인 흐름. | 리뉴얼 마이페이지 보안 확인 기능 구현 시 참고. |
| `/www/bbs/bbs/member_leave.php` | Legacy backend | 회원 탈퇴 처리. | 리뉴얼 회원 탈퇴 기능 구현 시 원본 기준. |
| `/www/bbs/bbs/password*.php` | Legacy backend/frontend | 비밀번호 찾기, 인증, 변경 관련 흐름. | 리뉴얼 비밀번호 재설정 기능 구현 전 별도 분석 필요. |
| `/www/bbs/bbs/register_email*.php` | Legacy backend | 이메일 인증/가입 메일 관련 처리. | 현재 리뉴얼 회원가입에서 이메일 인증을 생략할지 유지할지 결정해야 한다. |
| `/www/bbs/bbs/register_result.php` | Legacy frontend | 기존 가입 완료 화면. | 리뉴얼 `/register/complete` 화면의 문구/흐름 참고 대상. |

## 현재 회원가입 결론

현재 리뉴얼 회원가입은 기존 `/bbs/bbs/register_form_update.php`에 폼을 직접 제출하는 방식이 아니라, React 화면에서 `/api/auth/register.php`로 JSON을 보내는 별도 API 방식이다.

따라서 "기존 사이트에서 되던 회원가입이 리뉴얼에서도 당연히 된다"는 전제가 자동으로 성립하지 않는다.

실무적으로 맞는 방향은 다음과 같다.

1. 화면은 리뉴얼 React SPA를 유지한다.
2. 저장 처리는 리뉴얼 JSON API를 유지한다.
3. 단, API 내부의 회원 생성 규칙은 기존 `register_form_update.php`와 최대한 동일하게 맞춘다.
4. 로그인 API도 기존 `login_check.php`와 같은 회원 테이블, 같은 암호 검증 함수, 같은 Gnuboard 세션 기준을 사용해야 한다.

즉, URL 흐름까지 완전히 동일하게 만들 필요는 없지만, DB 저장 결과와 로그인 가능성은 기존 흐름과 동일해야 한다.

## 예약 / 결제

| 기존 파일 | 영역 | 역할 | 리뉴얼 대응 |
| --- | --- | --- | --- |
| `/www/admin/kscardPayList.php` | Legacy admin backend/frontend | 관리자 KSNET 카드 결제 내역 조회 화면. | 리뉴얼 `payments.php` 또는 예약 상세 결제 영역의 기준 파일. |
| `/www/admin/include_files/_getKSCardPayData.php` | Legacy admin backend | KSNET 카드 결제 데이터를 조회하는 관리자 include. | 리뉴얼 결제 조회 API에서 쿼리 조건과 테이블 기준 확인 필요. |
| `/www/kspay.php` | Legacy frontend/backend bridge | KSNET 카드 결제 요청 진입 파일. | 리뉴얼 마이페이지 카드결제 버튼 구현 시 결제창 호출 기준. |
| `/www/kspay_result.php` | Legacy backend | KSNET 결제 승인 결과 수신/처리. | 리뉴얼 카드 결제 완료 후 `tour_reg`, `kspay_result` 반영 기준. |
| `/www/kspay_wh_rcv.php` | Legacy backend | KSNET 웹훅/수신 처리로 추정. | 운영 결제 동기화 흐름 확인 필요. |
| `/www/KSPayApprovalCancel.inc` | Legacy backend library | KSNET 승인 취소 연동 라이브러리. | 리뉴얼 예약 상세의 카드 취소 버튼 구현 기준. |
| `/www/KSPayCancelPost.php` | Legacy backend | 카드 승인 취소 요청 처리. | 리뉴얼 KSNET 취소 API의 원본 기준. |
| `/www/KSPayWebHost.inc` | Legacy backend library | KSNET 결제 통신 공통 라이브러리. | 리뉴얼에서 직접 호출하거나 기존 파일을 유지 호출할지 결정 필요. |
| `/pay.php`, `/pay_result.php` | Legacy payment | KSNET 이전 또는 별도 결제 흐름 가능성. | 실제 운영 사용 여부 확인 후 유지/제외 결정. |

## 관리자 문의

| 기존 파일 | 영역 | 역할 | 리뉴얼 대응 |
| --- | --- | --- | --- |
| `/www/admin/board.php` | Legacy admin frontend/backend | Gnuboard 게시판 목록/관리 화면. `cusTour` 1:1 문의 목록과 답변 접근 기준. | 리뉴얼 `inquiries.php` 목록/상세/답변 기능 기준. |
| `/www/admin/write.php` | Legacy admin frontend/backend | 게시글/답변 작성 및 수정 화면. 보안 토큰, 답변 등록, 수정 흐름 확인 대상. | 리뉴얼 문의 상세의 답변 등록/수정/삭제 API 기준. |

## 운영 규칙

- 기존 파일을 새로 받으면 이 문서에 파일 단위로 역할을 먼저 기록한다.
- 구현 전에는 "화면 파일인지", "저장 처리 파일인지", "공통 라이브러리인지", "운영에서 실제 쓰는 파일인지"를 분리한다.
- 리뉴얼은 React/API 구조를 유지하되, DB 결과와 운영 흐름은 기존 우노트래블과 같아야 한다.
- 특히 회원, 예약, 결제, 문의는 새 테이블이나 새 인증 체계를 만들면 안 된다. 기존 Gnuboard 세션과 기존 운영 테이블을 기준으로 맞춘다.
 
=============================
Confirmed DB Contracts
=============================

tour_fee
--------------------------------
fee_subject     신청구분
fee1            홈페이지 예약금
fee2            사전 예약 후 현장 지불
fee3            현지 지불
fee_ticket_id   티켓요금
is_first        대표요금
fee4~fee7       B2B 요금(현재 미사용)

g5_write_product
--------------------------------
fee_org         정상가격
wr_4            가격 안내 문구

tour_reg
--------------------------------
fee_id          신청구분 ID 또는 세미패키지 일정 ID(pipe)
membCnt         신청구분별/일정별 인원(pipe)
total_fee1      예약금 합계
total_fee2      중도금 또는 현장 지불 합계
total_fee3      잔금 또는 현지 지불 합계
total_fee4      세미패키지 총 상품금액 합계
total_fee_air   세미패키지 항공요금 합계

v2_pkgTour
--------------------------------
id              세미패키지 출발 일정 ID
pid             g5_write_product.wr_id
start_time      출발일
arrive_time     도착일
fee_1           예약금
fee_2           중도금
fee_3           잔금
fee_air         항공요금
price           총 상품금액
seat            예약 가능 인원
status          일정 상태
is_main         대표 일정
is_view         노출 여부
del_time        삭제 여부

2026-07-15 확인:

- 상품 상세 API `backend-bridge/php-api/products/detail.php`는 세미패키지 일정 가격을 `v2_pkgTour.fee_1`, `fee_2`, `fee_3`, `fee_air`, `price`에서 읽는다.
- API 응답 매핑은 `fee_1 -> deposit`, `fee_2 -> middlePayment/intermediatePayment`, `fee_3 -> finalPayment/balance`, `fee_air -> airfare`, `price -> totalPrice`이다.
- 프런트 예약 payload에서 `items[].feeId`는 `tour_fee.id`를 사용한다.
- 세미패키지 `legacyPackageScheduleId`와 `items[].legacyPackageScheduleId`는 `v2_pkgTour.id`를 사용한다.
- 세미 일정 ID를 `items[].feeId`로 대체하지 않는다.
