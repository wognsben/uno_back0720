<!--
backend-api-contract.md
새 React 프런트와 기존 우노트래블 PHP/Gnuboard DB를 연결하기 위한 API 계약 문서입니다.
상품/날짜/장바구니/예약/마이페이지 API의 요청·응답 형태와 기존 테이블 매핑을 정의합니다.
실제 구현 파일이 아니라 백엔드 브릿지 작업 전 데이터 충돌을 막기 위한 연결 기준 문서입니다.
-->

# UNO Travel Backend API Contract

## 목표

새 React 프런트는 화면과 예약 UX를 담당하고, 기존 우노트래블 DB는 회원/상품/예약 원장을 계속 담당합니다.

1차 연결 범위는 결제를 제외합니다.

- 기존 회원 로그인 확인
- 상품 목록/상세 조회
- 날짜별 예약 가능 상태 조회
- 장바구니 담기/보기/삭제
- 예약 초안 생성
- 예약 동의 후 최종 신청
- 마이페이지 예약 목록 조회

## 연결 원칙

- 기존 회원과 예약 내역은 마이그레이션하지 않습니다.
- 기존 `tour_reg` 상태 의미를 유지합니다.
- 새 프런트 상품 ID와 기존 `wr_id/pid` 숫자 ID를 분리합니다.
- 예약 가능 여부 상태와 예약 진행 상태를 섞지 않습니다.
- 결제는 추후 별도 단계로 둡니다.

## 상품 ID 규칙

현재 React 상품 ID는 화면용 문자열입니다.

예:

- `italy-11`
- `rome-vatican-daily`
- `napoli-pompei-daily`

기존 DB 상품 ID는 `g5_write_product.wr_id` 숫자입니다.

예:

- `63`: 기존 코드 예시의 남부아말피코스트투어

따라서 API 응답에는 두 값을 모두 둡니다.

```json
{
  "id": "napoli-pompei-daily",
  "legacyProductId": 63,
  "title": "남부 아말피 코스트 투어"
}
```

`legacyProductId`가 없는 상품은 예약 API 호출을 막아야 합니다.

## 공통 응답 형태

성공:

```json
{
  "ok": true,
  "data": {}
}
```

실패:

```json
{
  "ok": false,
  "error": {
    "code": "LOGIN_REQUIRED",
    "message": "예약을 위해서는 로그인이 필요합니다."
  }
}
```

## 공통 에러 코드

| Code | 의미 |
| --- | --- |
| `LOGIN_REQUIRED` | 로그인 필요 |
| `PRODUCT_NOT_FOUND` | 상품 없음 |
| `PRODUCT_NOT_MAPPED` | React 상품과 기존 DB 상품 ID 미연결 |
| `DATE_REQUIRED` | 투어일 미선택 |
| `DATE_CLOSED` | 휴무 또는 마감 |
| `SOLD_OUT` | 잔여석 부족 |
| `DUPLICATE_RESERVATION` | 중복 예약 |
| `INVALID_RESERVATION` | 예약 ID 오류 |
| `PERMISSION_DENIED` | 본인 예약/장바구니가 아님 |
| `VALIDATION_ERROR` | 입력값 오류 |
| `SERVER_ERROR` | 서버 오류 |

## 인증 API

### `GET /api/auth/session`

현재 Gnuboard 로그인 세션을 확인합니다.

기존 기준:

- `$member['mb_id']`가 있으면 로그인 상태
- `g5_member`가 회원 원장

Response:

```json
{
  "ok": true,
  "data": {
    "isLoggedIn": true,
    "member": {
      "id": "test1",
      "name": "test1",
      "email": "test@test.com",
      "phone": "010-1111-1111",
      "kakaoId": ""
    }
  }
}
```

로그인하지 않은 상태:

```json
{
  "ok": true,
  "data": {
    "isLoggedIn": false,
    "member": null
  }
}
```

## 상품 API

### `GET /api/products`

상품 목록을 조회합니다.

Query:

| Name | Required | Example | 설명 |
| --- | --- | --- | --- |
| `type` | no | `semi`, `daily` | 프런트 상품 타입 |
| `category` | no | `rome`, `italy` | 프런트 카테고리 |
| `legacyCategory` | no | `단체`, `세미패키지` | 기존 `ca_name` 기준 필터 |

기존 DB:

- `g5_write_product`
- 대표 요금은 `tour_fee`
- 썸네일은 기존 `get_list_thumbnail("product", wr_id, ...)` 흐름

Response:

```json
{
  "ok": true,
  "data": {
    "items": [
      {
        "id": "napoli-pompei-daily",
        "legacyProductId": 63,
        "productType": "daily",
        "title": "남부 아말피 코스트 투어",
        "category": "napoli",
        "legacyCategory": "단체",
        "href": "/product/detail/daily/napoli-pompei-daily",
        "thumbnailUrl": "/bbs/data/file/product/example.jpg",
        "price": {
          "deposit": 90000,
          "localPayment": 50,
          "localPaymentCurrency": "EUR"
        },
        "requiresPassport": false,
        "requiresRoomInfo": false,
        "requiresDelivery": false
      }
    ]
  }
}
```

### `GET /api/products/:id`

상품 상세를 조회합니다.

`:id`는 React 상품 ID를 우선 사용합니다. 서버에서 `legacyProductId`로 변환합니다.

Response:

```json
{
  "ok": true,
  "data": {
    "id": "napoli-pompei-daily",
    "legacyProductId": 63,
    "productType": "daily",
    "title": "남부 아말피 코스트 투어",
    "legacyCategory": "단체",
    "reservationDefaults": {
      "requiresPassport": false,
      "requiresRoomInfo": false,
      "requiresDelivery": false,
      "defaultFinalStatus": "1"
    },
    "feeOptions": [
      {
        "id": 123,
        "label": "만 6세 이상 ~ 성인",
        "deposit": 90000,
        "localPayment": 50,
        "localPaymentCurrency": "EUR",
        "isDefault": true
      }
    ]
  }
}
```

기존 테이블 매핑:

| API Field | 기존 DB |
| --- | --- |
| `legacyProductId` | `g5_write_product.wr_id` |
| `title` | `g5_write_product.wr_subject` |
| `legacyCategory` | `g5_write_product.ca_name` |
| `requiresPassport` | `g5_write_product.is_passport` |
| `requiresRoomInfo` | `g5_write_product.is_roominfo` |
| `requiresDelivery` | `g5_write_product.is_delivery` |
| `defaultFinalStatus` | `wr_reg_result`, fallback `1` |
| `feeOptions[].id` | `tour_fee.id` |
| `feeOptions[].label` | `tour_fee.fee_subject` |
| `feeOptions[].deposit` | `tour_fee.fee1` |
| `feeOptions[].localPayment` | `tour_fee.fee2` |

## 날짜/잔여석 API

### `GET /api/products/:id/availability`

날짜별 상태를 조회합니다.

Query:

| Name | Required | Example | 설명 |
| --- | --- | --- | --- |
| `from` | yes | `2026-07-01` | 시작일 |
| `to` | yes | `2026-08-31` | 종료일 |

기존 기준:

- `tour_closed_2.isClose = Y`: 휴무
- `tour_closed_2.isClose = E`: 마감
- `tour_reg_count.maxCount - nowCount <= 5`: 마감 임박 또는 마감
- `tour_reg_count.maxCount - nowCount < 1`: 마감

Response:

```json
{
  "ok": true,
  "data": {
    "productId": "napoli-pompei-daily",
    "legacyProductId": 63,
    "dates": [
      {
        "id": "2026-07-29",
        "date": "2026-07-29",
        "weekday": "수",
        "status": "available",
        "displayLabel": "예약 가능",
        "remainingSeats": 12
      },
      {
        "id": "2026-07-30",
        "date": "2026-07-30",
        "weekday": "목",
        "status": "soon",
        "displayLabel": "마감 임박",
        "remainingSeats": 3
      },
      {
        "id": "2026-07-31",
        "date": "2026-07-31",
        "weekday": "금",
        "status": "soldout",
        "displayLabel": "마감",
        "remainingSeats": 0
      }
    ]
  }
}
```

프런트 날짜 상태는 아래 코드만 사용합니다.

- `available`
- `soon`
- `soldout`

## 장바구니 API

### `GET /api/cart`

현재 회원의 장바구니를 조회합니다.

기존 조회 조건:

- `tour_reg.status = 'cart'`
- `tour_reg.del_time < 111`
- `tour_reg.mb_id = $member['mb_id']`
- `tour_reg.pid = g5_write_product.wr_id`

Response:

```json
{
  "ok": true,
  "data": {
    "items": [
      {
        "rid": 12345,
        "productId": "napoli-pompei-daily",
        "legacyProductId": 63,
        "title": "남부 아말피 코스트 투어",
        "tourDate": "2026-07-29",
        "options": [
          {
            "feeId": 123,
            "label": "만 6세 이상 ~ 성인",
            "personCount": 1,
            "deposit": 90000,
            "localPayment": 50
          }
        ],
        "totalDeposit": 90000,
        "totalLocalPayment": 50
      }
    ],
    "count": 1
  }
}
```

### `POST /api/cart`

장바구니에 예약 초안을 생성합니다.

기존 `_saveBooking.php` 초기 저장과 같은 의미이며, `tour_reg.status = cart`로 저장합니다.

Request:

```json
{
  "productId": "napoli-pompei-daily",
  "legacyProductId": 63,
  "tourDate": "2026-07-29",
  "tourTime": "",
  "items": [
    {
      "feeId": 123,
      "personCount": 1
    }
  ],
  "memo": ""
}
```

Response:

```json
{
  "ok": true,
  "data": {
    "rid": 12345,
    "status": "cart"
  }
}
```

### `DELETE /api/cart/:rid`

장바구니 항목을 삭제합니다.

권장 구현:

- 실제 삭제보다 기존 방식처럼 `del_time` 업데이트 우선
- 현재 회원의 `rid`인지 반드시 검증
- `status = cart`인 항목만 삭제 허용

Response:

```json
{
  "ok": true,
  "data": {
    "rid": 12345,
    "deleted": true
  }
}
```

## 예약 API

### `POST /api/reservations/draft`

예약하기 버튼을 눌렀을 때 예약 초안을 생성합니다.

기존 `_saveBooking.php` 초기 저장과 같은 의미이며, `tour_reg.status = booking`으로 저장합니다.

Request:

```json
{
  "productId": "napoli-pompei-daily",
  "legacyProductId": 63,
  "tourDate": "2026-07-29",
  "tourTime": "",
  "items": [
    {
      "feeId": 123,
      "personCount": 1
    }
  ],
  "memo": ""
}
```

Response:

```json
{
  "ok": true,
  "data": {
    "rid": 12345,
    "status": "booking",
    "nextUrl": "/reservation?rid=12345"
  }
}
```

기존 검증:

- 로그인 확인
- 상품 존재 확인
- 투어일 확인
- 휴무/마감 확인
- 중복 예약 확인
- 잔여석 확인
- 인원 수 확인

### `GET /api/reservations/:rid`

예약 입력 화면에서 초안 정보를 조회합니다.

Response:

```json
{
  "ok": true,
  "data": {
    "rid": 12345,
    "status": "booking",
    "product": {
      "id": "napoli-pompei-daily",
      "legacyProductId": 63,
      "productType": "daily",
      "title": "남부 아말피 코스트 투어",
      "requiresPassport": false,
      "requiresRoomInfo": false,
      "requiresDelivery": false
    },
    "tourDate": "2026-07-29",
    "options": [
      {
        "feeId": 123,
        "label": "만 6세 이상 ~ 성인",
        "personCount": 1,
        "deposit": 90000,
        "localPayment": 50
      }
    ],
    "totalDeposit": 90000,
    "totalLocalPayment": 50,
    "applicantDefaults": {
      "name": "test1",
      "phone": "010-1111-1111",
      "email": "test@test.com",
      "kakaoId": ""
    }
  }
}
```

### `POST /api/reservations/:rid/confirm`

동의 후 예약 입력 폼을 최종 저장합니다.

기존 `_saveBooking.php`의 `is_booking = y` 흐름과 같은 의미입니다. 새 행을 만들지 않고 기존 `tour_reg` 행을 업데이트합니다.

Request:

```json
{
  "agreeRefundPolicy": true,
  "applicant": {
    "name": "test1",
    "phone": "010-1111-1111",
    "email": "test@test.com",
    "kakaoId": "kakao-id"
  },
  "memo": "기타 사항을 입력합니다.",
  "passports": [
    {
      "nameKo": "홍길동",
      "nameEn": "HONG GILDONG",
      "birthDate": "1990-01-01",
      "passportNo": "M12345678",
      "passportExpireDate": "2030-01-01",
      "gender": "M"
    }
  ],
  "roomInfo": "2인 1실 트윈 요청",
  "delivery": {
    "zip": "",
    "addr1": "",
    "addr2": "",
    "addr3": "",
    "gift": ""
  }
}
```

Response:

```json
{
  "ok": true,
  "data": {
    "rid": 12345,
    "status": "1",
    "statusLabel": "예약대기",
    "nextUrl": "/reservation/complete?rid=12345"
  }
}
```

기존 업데이트 매핑:

| Request Field | 기존 DB |
| --- | --- |
| `applicant.name` | `tour_reg.mb_name` |
| `applicant.phone` | `tour_reg.mb_hp` |
| `applicant.email` | `tour_reg.mb_email` |
| `applicant.kakaoId` | `tour_reg.mb_kakao` |
| `memo` | `tour_reg.regMemo` |
| `passports[]` | `tour_reg.mb_passport_info` |
| `roomInfo` | `tour_reg.roominfo` |
| `delivery.*` | `zip`, `addr1`, `addr2`, `addr3`, `gift` |
| final status | `get_booking_status(pid)` 결과 |

세미패키지라면 `passports`, `roomInfo`를 표시/검증합니다.

데일리투어라면 기본적으로 신청자 정보와 기타사항만 받습니다.

### `GET /api/reservations/:rid/complete`

예약 완료 화면 데이터를 조회합니다.

Response:

```json
{
  "ok": true,
  "data": {
    "rid": 12345,
    "status": "1",
    "statusLabel": "예약대기",
    "product": {
      "title": "남부 아말피 코스트 투어",
      "href": "/product/detail/daily/napoli-pompei-daily"
    },
    "tourDate": "2026-07-29",
    "options": [
      {
        "label": "만 6세 이상 ~ 성인",
        "personCount": 1
      }
    ],
    "paymentNotice": {
      "deposit": 90000,
      "depositCurrency": "KRW",
      "localPayment": 50,
      "localPaymentCurrency": "EUR",
      "depositDueText": "예약 확인 상태라면 12시간 이내 예약금 결제가 필요합니다.",
      "accounts": [
        "이탈리아 전용 입금 계좌: 신한은행 140-010-791268 (예금주: (주)우노컴패니)",
        "세미패키지 전용 입금 계좌: 우리은행 1005-302-870059 (예금주:(주)우노컴패니)"
      ]
    }
  }
}
```

## 마이페이지 API

### `GET /api/my/reservations`

현재 회원의 예약 목록을 조회합니다.

기존 조회 조건:

- `tour_reg.pid = g5_write_product.wr_id`
- `tour_reg.mb_id = $member['mb_id']`
- `status not in ('cart', 'booking')`
- `order by tourDay desc`

Response:

```json
{
  "ok": true,
  "data": {
    "items": [
      {
        "rid": 12345,
        "reservationNo": "12345",
        "createdAt": "2026-07-10T12:00:00+09:00",
        "tourDate": "2026-07-29",
        "status": "1",
        "statusLabel": "예약대기",
        "product": {
          "id": "napoli-pompei-daily",
          "legacyProductId": 63,
          "title": "남부 아말피 코스트 투어",
          "href": "/product/detail/daily/napoli-pompei-daily"
        },
        "options": [
          {
            "label": "만 6세 이상 ~ 성인",
            "personCount": 1
          }
        ],
        "payment": {
          "deposit": 90000,
          "localPayment": 50,
          "cardPayRef": null,
          "canPayByCard": false
        }
      }
    ]
  }
}
```

## 기존 상태값 라벨

| DB Status | Label | 새 프런트 의미 |
| --- | --- | --- |
| `cart` | 장바구니 | 장바구니 목록에만 표시 |
| `booking` | 예약 입력 전 | 예약 초안, 마이페이지에는 숨김 |
| `1` | 예약대기 | 최종 신청 완료 |
| `2` | 예약확인 | 입금/확인 대기 또는 결제 가능 상태 |
| `3` | 예약확정 | 확정, 바우처 가능 |
| `9` | 예약취소 | 취소 완료 |
| `91` | 취소요청 | 사용자 취소 요청 |

## 구현 순서

1. 상품 매핑 파일 또는 매핑 테이블 생성
2. `GET /api/auth/session`
3. `GET /api/products`, `GET /api/products/:id`
4. `GET /api/products/:id/availability`
5. `POST /api/reservations/draft`
6. `GET /api/reservations/:rid`
7. `POST /api/reservations/:rid/confirm`
8. `GET /api/my/reservations`
9. `GET /api/cart`, `POST /api/cart`, `DELETE /api/cart/:rid`

## 첫 구현에서 보류할 것

- 카드 결제 연동
- 예약 취소/환불 처리
- 관리자 예약 상태 변경
- 알림톡 발송
- 이벤트/콤보 투어 자동 추가
- B2B 특수 예약
- 기존 모바일 PHP 페이지 재설계

## 확인 필요

- 운영 DB에서 실제 `tour_reg` 컬럼 전체 목록 확인
- 운영 DB에서 실제 `g5_write_product` 컬럼 전체 목록 확인
- 운영 DB에서 React 상품별 `legacyProductId` 확정
- 기존 로그인 쿠키/세션을 React 호스팅 도메인에서 그대로 읽을 수 있는지 확인
- API를 기존 PHP 서버에 둘지, 별도 Node/PHP 브릿지 서버에 둘지 결정
