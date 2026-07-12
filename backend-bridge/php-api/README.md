<!--
README.md
기존 우노트래블 www/api 아래로 옮겨 붙일 PHP API 브릿지 샘플 안내 문서입니다.
React 프런트와 Gnuboard 세션/DB를 연결하는 최소 endpoint 구조와 배치 위치를 설명합니다.
운영 서버 파일을 직접 수정하는 문서가 아니라, 검토 후 복사할 수 있는 기준 템플릿 역할입니다.
-->

# UNO Travel PHP API Bridge

이 폴더는 기존 우노트래블 `www/` 서버에 추가할 API 브릿지 샘플입니다.

권장 배치:

```txt
www/
  admin/
    renewal/
      product-navigation.php
  api/
    _bootstrap.php
    _product_map.php
    _product_navigation_store.php
    _response.php
    _reservation_helpers.php
    health.php
    admin/
      products.php
      product-navigation.php
      product-navigation-seed.php
    auth/
      session.php
    cart/
      index.php
      delete.php
    my/
      reservations.php
    products/
      index.php
      navigation.php
      detail.php
      availability.php
    reservations/
      draft.php
      detail.php
      confirm.php
```

현재 포함된 endpoint:

- `GET /api/auth/session.php`
- `POST /api/auth/login.php`
- `GET /api/admin/product-navigation.php`
- `POST /api/admin/product-navigation.php`
- `GET /api/admin/product-navigation-seed.php?confirm=1`
- `GET /api/admin/products.php`
- `GET /api/health.php`
- `GET /api/products/index.php`
- `GET /api/products/navigation.php`
- `GET /api/products/detail.php?id=napoli-pompei-daily&mode=reservation`
- `GET /api/products/availability.php?id=napoli-pompei-daily&from=2026-07-01&to=2026-08-31`
- `GET /api/cart/index.php`
- `POST /api/cart/index.php`
- `POST /api/cart/delete.php?rid=12345`
- `POST /api/reservations/draft.php`
- `GET /api/reservations/detail.php?rid=12345`
- `POST /api/reservations/confirm.php?rid=12345`
- `GET /api/my/reservations.php`

React 프런트에서는 최종적으로 `/api/auth/session`, `/api/auth/login`, `/api/products/:id`, `/api/cart`, `/api/reservations/draft`, `/api/reservations/:rid`, `/api/reservations/:rid/confirm`, `/api/my/reservations` 또는 서버 rewrite 설정에 맞춘 경로로 호출합니다.

## 구현 원칙

- 기존 `bbs/common.php`를 불러와 Gnuboard 세션과 `$member`를 그대로 사용합니다.
- JSON 응답은 `{ ok: true, data }`, `{ ok: false, error }` 형태를 유지합니다.
- 상태 변경 요청은 CSRF 검증을 붙입니다.
- DB 쓰기 endpoint는 프런트 payload를 신뢰하지 않고 기존 DB에서 상품/금액/좌석을 다시 확인합니다.
- 사용자 API는 관리자 화면 HTML/CSS를 맡지 않습니다.

## 아직 샘플이 아닌 것

- 결제 API
- 예약 취소/환불 API
- 관리자 전용 API

위 endpoint들은 `health.php`, `auth/session.php`, `auth/login.php`, `products/index.php`, `products/detail.php`, `products/availability.php`, `cart/index.php`, `cart/delete.php`, `reservations/draft.php`, `reservations/detail.php`, `reservations/confirm.php`, `my/reservations.php`를 운영 서버에서 확인한 뒤 순서대로 추가하는 것이 안전합니다.
