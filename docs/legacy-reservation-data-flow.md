<!--
legacy-reservation-data-flow.md
기존 우노트래블 PHP/Gnuboard 예약 코드의 DB 흐름을 정리한 분석 문서입니다.
상품 상세, 장바구니, 예약 동의/입력, 예약 확정, 마이페이지 조회가 어떤 테이블과 상태값을 거치는지 설명합니다.
새 React 프런트 파일이 아니며, 백엔드/API 연결 전 기존 데이터 구조를 오해하지 않기 위한 기준 문서 역할만 담당합니다.
-->

# 기존 우노트래블 예약 DB 흐름

## 분석 대상

기존 사이트의 예약 흐름은 아래 파일들이 중심입니다.

- `www/contents/tour_list.php`
- `www/contents/tour_view.php`
- `www/include/_tour_view_script.inc.php`
- `www/contents/_saveBooking.php`
- `www/contents/cart.php`
- `www/include/_cart_script.inc.php`
- `www/contents/reservation.php`
- `www/include/_reservation_script.inc.php`
- `www/contents/my_reser.php`
- `www/include/_my_reser_script.inc.php`
- `www/bbs/extend/panda.lib.php`

## 핵심 결론

기존 예약 시스템은 장바구니와 예약 초안을 별도 테이블에 저장하지 않습니다.

모두 `tour_reg` 테이블에 먼저 저장하고, `status` 값으로 의미를 구분합니다.

- `cart`: 장바구니에 담긴 상태
- `booking`: 예약 페이지로 넘어가기 전 임시 예약 상태
- `1`: 예약대기
- `2`: 예약확인
- `3`: 예약확정
- `9`: 예약취소
- `91`: 취소요청

따라서 새 프런트와 기존 DB를 연결할 때도 처음부터 새 예약 테이블을 만들기보다, 기존 `tour_reg` 흐름을 API로 감싸는 방식이 가장 안전합니다.

## 주요 DB 테이블

### `g5_write_product`

상품 본문 테이블입니다. Gnuboard 게시판형 상품 데이터로 보이며, 상품 상세/목록/예약 기본값의 기준입니다.

주요 사용 필드:

- `wr_id`: 상품 ID
- `wr_subject`: 상품명
- `ca_name`: 상품 카테고리
- `wr_reg_result`: 일반 회원 예약 시 기본 예약 상태
- `wr_b2b_result`: B2B 회원 예약 시 기본 예약 상태
- `recommend_tour`: 추천 상품 ID 목록
- `is_passport`: 여권 정보 필요 여부
- `is_roominfo`: 룸 정보 필요 여부
- `is_delivery`: 배송지 정보 필요 여부
- `wr_event_option`, `wr_event_course`: 추가/이벤트 투어 옵션

### `tour_fee`

일반 투어 옵션/요금 테이블입니다.

주요 사용 필드:

- `id`: 옵션 ID
- `fee_subject`: 옵션명
- `fee1`: 예약금, 원화
- `fee2`: 현지지불금, 유로
- `fee3`: 추가 금액 또는 표시용 요금

상품 상세의 옵션 선택에서 `fee_id[]`, `memb[]`로 선택값이 만들어지고, 저장 시 `tour_reg.fee_id`, `tour_reg.membCnt`에 `|` 구분 문자열로 들어갑니다.

### `tour_reg`

예약의 중심 테이블입니다. 장바구니, 예약 초안, 확정 예약, 취소 예약이 모두 이 테이블에 들어갑니다.

주요 사용 필드:

- `id`: 예약 ID, 기존 코드의 `rid`
- `regDate`: 신청 시각
- `mb_id`: 회원 ID
- `mb_name`, `mb_email`, `mb_kakao`, `mb_hp`: 신청자 정보
- `tourDay`: 투어일
- `tourTime`: 오전/오후 등 시간 옵션
- `pid`: 상품 ID
- `event_pid`: 이벤트/추가 투어 상품 ID
- `parent_id`: 추가 투어가 붙는 부모 예약 ID
- `membCnt`: 옵션별 인원 수 문자열
- `fee_id`: 옵션 ID 문자열
- `total_fee1`: 예약금
- `total_fee2`: 현지지불금
- `total_fee3`: 티켓/추가 금액
- `total_fee4`: 패키지 총액 또는 추가 계산 금액
- `total_fee_air`: 항공비 관련 금액
- `regMemo`: 기타 요청사항
- `ISECMemo`: 국제학생증 정보
- `mb_passport_info`: 여권 정보
- `roominfo`: 룸 타입/룸 요청 정보
- `status`: 예약 상태
- `nation`: 상품 성격/지역/패키지 구분
- `isMobile`: 모바일 신청 여부
- `isB2B`: B2B 예약 여부
- `fee_status`: 요금 상태
- `card_pay`: 카드 결제 결과 연결값

### `tour_closed_2`

날짜별 마감/휴무 정보입니다.

주요 사용 필드:

- `pid`: 상품 ID
- `closedDate`: 마감 날짜
- `isClose`: `Y` 휴무, `E` 마감

상품 상세에서 날짜 선택 가능 여부를 만들고, `_saveBooking.php`에서도 다시 검증합니다.

### `v2_pkgTour`

패키지/세미패키지 출발 일정과 패키지 요금 계산에 쓰입니다.

주요 사용 필드:

- `id`: 패키지 일정 ID
- `start_time`, `arrive_time`: 여행 시작/도착 범위
- `fee_1`, `fee_2`, `fee_3`, `fee_air`, `price`: 패키지 요금 구성
- `is_main`, `is_view`, `del_time`: 노출/삭제 상태

### `tour_reg_pkg_fee`

패키지 예약의 단계별 결제 상태와 금액을 보조하는 테이블입니다.

`panda.lib.php`의 `get_pkg_feein()`에서 `rid`, `fee_gubun` 기준으로 조회/생성합니다.

### `kspay_result`

기존 카드 결제 결과 테이블입니다.

`get_res_status_btn()`에서 `tour_reg.card_pay`와 연결해 결제 완료/취소 여부를 표시합니다. 현재 새 프런트에서는 결제 시스템을 보류하기로 했으므로, 우선 읽기/표시 대상에 가깝습니다.

## 전체 예약 흐름

### 1. 상품 목록

`tour_list.php`는 `product_list($na, $sca, $pid)`를 호출합니다.

`product_list()`는 `g5_write_product`에서 상품을 조회합니다.

주요 조건:

- `wr_is_comment = 0`
- `LENGTH(wr_subject) > 2`
- `ca_name`에 `숨김`이 포함되지 않음
- 카테고리는 `ca_name` 기준

추천 상품 영역에서는 현재 상품의 `recommend_tour`에 들어있는 상품 ID를 우선합니다.

### 2. 상품 상세에서 예약 옵션 선택

`tour_view.php`는 예약 폼 `#tourBooking`을 렌더링합니다.

주요 hidden/input 값:

- `pid`: 상품 ID
- `is_ver = v2`
- `pCate`: 차량/패키지/세미패키지 등 분기값
- `tourDate`: 선택 날짜
- `booking_mode`: `booking` 또는 `cart`
- `totalFee1`: 예약금
- `totalFee2`: 현지지불금
- `totalFee3`, `totalFee4`: 추가/패키지 금액
- `price_sel`: 선택 옵션
- `fee_id[]`: 옵션 ID
- `memb[]`: 옵션별 인원 수

`_tour_view_script.inc.php`가 옵션 선택, 인원 증감, 금액 계산, 날짜 선택, 저장 요청을 담당합니다.

### 3. 장바구니 또는 예약 초안 저장

상품 상세에서 버튼을 누르면 `save_booking(gubun)`이 실행됩니다.

- 장바구니: `save_booking('cart')`
- 예약하기: `save_booking('booking')`

스크립트는 `/contents/_saveBooking.php`로 `#tourBooking` 값을 전송합니다.

`_saveBooking.php`는 다음을 검증합니다.

- 상품 ID 존재 여부
- 날짜 마감 여부: `tour_closed_2`
- 중복 예약 여부: 같은 회원, 같은 날짜, 같은 상품, 확정 상태 기준
- 인원 선택 여부
- 잔여석 여부: `get_tour_jan_cnt()`, `get_res_member_cnt()`

검증 후 `tour_reg`에 새 행을 생성합니다.

- `booking_mode = cart`이면 `tour_reg.status = cart`
- `booking_mode = booking`이면 `tour_reg.status = booking`

성공 시 숫자 `rid`를 반환합니다.

### 4. 장바구니 조회와 예약 전환

`cart.php`는 별도 cart 테이블이 아니라 `tour_reg`를 조회합니다.

조회 조건:

- `r.pid = p.wr_id`
- `r.status = 'cart'`
- `r.del_time < 111`
- 현재 로그인 회원의 `mb_id`

장바구니에서 선택 후 예약 신청을 누르면 `_cart_script.inc.php`의 `chk_regist()`가 선택한 `tour_reg.id` 목록을 콤마로 묶어 이동합니다.

예:

```text
reservation.php?rid=123,124,
```

### 5. 예약 동의 화면

`reservation.php`는 먼저 로그인 여부를 확인합니다.

로그인하지 않은 경우:

```text
/contents/login.php?url=reservation
```

예약 페이지는 `rid`를 기준으로 `tour_reg`를 찾습니다. 동의 전에는 예약 안내와 환불/취소 규정 동의 화면을 보여줍니다.

동의 후 실제 예약 입력 폼으로 넘어갑니다.

### 6. 예약 입력 화면

동의 후 `reservation.php`는 `tour_reg`와 `g5_write_product`를 함께 조회합니다.

입력 폼의 핵심 값:

- `rid`: 예약 ID 또는 콤마로 묶인 예약 ID 목록
- `is_ver = v2`
- `is_booking = y`
- 신청자 정보: 이름, 연락처, 이메일, 카카오톡 ID
- 기타사항: `regMemo`
- 국제학생증 정보: `ISEC_*`
- 여권 정보: `passport_*`
- 룸 정보: `roominfo`
- 배송 정보: `zip`, `addr1`, `addr2`, `addr3`, `gift`

상품의 `is_passport`, `is_roominfo`, `is_delivery` 값에 따라 추가 입력 섹션이 열립니다.

즉 새 예약 페이지도 아래 기준이 맞습니다.

- 데일리투어: 상품 정보, 인원, 투어일, 예약금/현지지불금, 신청자 정보, 기타사항
- 세미패키지: 위 항목 + 여권 정보 + 룸 정보

### 7. 예약 최종 저장

예약 입력 폼은 `_reservation_script.inc.php`를 통해 `/contents/_saveBooking.php`로 다시 전송됩니다.

이때 `is_booking = y`이므로 `_saveBooking.php`는 새 행을 만들지 않고 기존 `tour_reg` 행을 업데이트합니다.

업데이트 내용:

- `status`: `get_booking_status(pid)` 결과
- `mb_name`, `mb_email`, `mb_kakao`, `mb_hp`
- `isMobile`
- `nation`
- `total_fee3`, `total_fee4`
- `regMemo`
- 배송지 정보
- 룸 정보
- `ISECMemo`
- `mb_passport_info`

`get_booking_status(pid)`는 `g5_write_product`의 설정을 기준으로 최종 기본 상태를 정합니다.

- B2B 회원이면 `wr_b2b_result`
- 일반 회원이고 `wr_reg_result`가 있으면 그 값
- 없으면 기본 `1`

### 8. 예약 완료 화면

최종 저장 성공 후 이동:

```text
reservation.php?res_ok={rid}
```

완료 화면은 `tour_reg`와 `g5_write_product`를 다시 읽어 다음 정보를 보여줍니다.

- 상품명
- 투어일
- 옵션/인원
- 예약금
- 현지지불금
- 입금 유의사항
- 마이페이지 이동 버튼

### 9. 마이페이지 예약 목록

`my_reser.php`도 `tour_reg`를 조회합니다.

조회 조건:

- `r.pid = p.wr_id`
- 현재 로그인 회원의 `mb_id`
- `status`가 `cart` 또는 `booking`이 아닌 행

즉 예약 입력이 끝나 `status`가 숫자 상태로 바뀐 건만 마이페이지 예약 목록에 보입니다.

상태 버튼은 `get_res_status_btn($row)`가 만듭니다.

상태 표시:

- `1`: 예약대기
- `2`: 예약확인
- `3`: 예약확정
- `9`: 예약취소
- `91`: 취소요청

## 새 프런트와 기존 DB 연결 방향

### 보존해야 할 기존 의미

기존 회원 수와 예약 내역 규모를 고려하면, 아래 값들은 최대한 유지하는 것이 안전합니다.

- 회원: 기존 Gnuboard 회원 세션/회원 테이블
- 상품 ID: `g5_write_product.wr_id`
- 예약 ID: `tour_reg.id`
- 예약 상태: 기존 `status` 값
- 장바구니: `tour_reg.status = cart`
- 예약 초안: `tour_reg.status = booking`
- 확정 예약 목록: `status not in ('cart', 'booking')`

### 추천 API 흐름

새 React 프런트는 기존 PHP 페이지로 직접 이동하기보다, 기존 DB/함수를 감싼 API를 호출하는 구조가 좋습니다.

1. 상품 목록 API

```text
GET /api/products
GET /api/products/:pid
```

기존 `g5_write_product`, `tour_fee`, 썸네일 데이터를 새 프런트 형식으로 반환합니다.

2. 날짜/잔여석 API

```text
GET /api/products/:pid/availability
```

기존 `tour_closed_2`, `get_tour_jan_cnt()` 기준으로 날짜별 상태를 반환합니다.

새 프런트 내부 상태는 이미 정리한 것처럼 아래 영문 코드로 유지합니다.

- `available`
- `soon`
- `soldout`

3. 장바구니 저장 API

```text
POST /api/cart
```

내부적으로 기존 `_saveBooking.php`의 초기 저장과 같은 의미로 `tour_reg.status = cart` 행을 생성합니다.

4. 예약 초안 생성 API

```text
POST /api/reservations/draft
```

내부적으로 `tour_reg.status = booking` 행을 생성하고 `rid`를 반환합니다.

5. 예약 확정 API

```text
POST /api/reservations/:rid/confirm
```

기존 `is_booking = y` 흐름과 동일하게 신청자 정보, 여권 정보, 룸 정보, 기타사항을 받아 `tour_reg`를 업데이트합니다.

6. 마이페이지 예약 목록 API

```text
GET /api/my/reservations
```

기존 `my_reser.php`와 동일하게 `status not in ('cart', 'booking')` 조건으로 조회합니다.

7. 내 장바구니 API

```text
GET /api/cart
DELETE /api/cart/:rid
```

기존 `cart.php`, `_cart_script.inc.php` 흐름을 API로 감쌉니다.

## React 예약 payload와 기존 DB 매핑

| React payload | 기존 PHP/DB |
| --- | --- |
| `productId` | `pid`, `g5_write_product.wr_id` |
| `productType` | `pCate`, `g5_write_product.ca_name`, `nation` |
| `title` | `g5_write_product.wr_subject` |
| `href` | `tour_view.php?pid={pid}` 또는 새 상세 URL |
| `selectedDateId` | `tourDay` |
| `selectedDateLabel` | `tourDay` 표시값 |
| `personCount` | `memb[]`, 저장 후 `membCnt` |
| `unitPrice` | `tour_fee.fee1` 또는 패키지 `v2_pkgTour.fee_1` |
| `totalPrice` | `total_fee1` |
| `currency` | 화면 표시용, 기존 DB는 금액별 컬럼 분리 |
| `guide` | 기존 핵심 예약 DB에는 직접 매핑 없음 |
| `remainingSeatsAfterSelection` | 저장 전 검증값, DB 직접 저장 대상 아님 |
| applicant name | `mb_name` |
| applicant phone | `mb_hp` |
| applicant email | `mb_email` |
| applicant kakao | `mb_kakao` |
| memo | `regMemo` |
| passport info | `mb_passport_info` |
| room info | `roominfo` |

## 상태값 정리

새 프런트에서 날짜 예약 가능 여부는 영문 코드로 유지합니다.

- `available`: 예약 가능
- `soon`: 마감 임박
- `soldout`: 마감

기존 DB의 예약 진행 상태는 별도입니다.

- `cart`: 장바구니
- `booking`: 예약 입력 전 임시 상태
- `1`: 예약대기
- `2`: 예약확인
- `3`: 예약확정
- `9`: 예약취소
- `91`: 취소요청

둘은 섞지 않는 것이 좋습니다.

## 다음 작업 제안

1. `tour_reg` 실제 테이블 스키마 확인
2. `g5_write_product`, `tour_fee`, `v2_pkgTour` 실제 컬럼 확인
3. 현재 React 상품 데이터와 기존 `wr_id` 매핑표 작성
4. 결제 제외 버전의 예약 API 계약서 작성
5. 기존 Gnuboard 로그인 세션을 새 프런트에서 어떻게 확인할지 결정
6. API 1차 범위는 장바구니/예약초안/예약확정/마이페이지 조회로 제한

## 아직 확인이 필요한 부분

- 현재 새 React 상품 ID와 기존 `g5_write_product.wr_id`가 1:1로 이미 맞는지
- 기존 `tour_fee` 옵션이 새 상세 페이지 옵션 구조와 완전히 맞는지
- `get_tour_jan_cnt()`가 참조하는 정확한 테이블/마감 로직
- 모바일 기존 페이지를 유지할지, 새 React 반응형으로 통합할지
- 카드 결제 재연동 시 `kspay_result`를 그대로 쓸지, 새 결제 모듈을 둘지
