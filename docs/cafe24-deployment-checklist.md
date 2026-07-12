<!--
cafe24-deployment-checklist.md
카페24 PHP 웹호스팅에 UNO Travel React 프런트와 PHP API 브릿지를 올리기 전 확인할 배포 체크리스트입니다.
업로드 위치, 제외 파일, API 확인 순서, 오류별 점검 포인트를 정리합니다.
서버 코드를 직접 대체하는 파일이 아니라, 실서버 적용 중 실수를 줄이기 위한 운영 메모 역할입니다.
-->

# Cafe24 Deployment Checklist

## 1. Local build

로컬에서 프런트 빌드를 먼저 만듭니다.

```powershell
cd C:\Users\wogns\OneDrive\Desktop\uno
npm run build
```

Codex 터미널에서 npm 경로가 꼬여 있으면 아래 우회 명령으로도 같은 빌드를 확인할 수 있습니다.

```powershell
.\node_modules\.bin\vite.cmd build --configLoader runner
```

성공하면 `dist/` 폴더가 배포 대상입니다.

## 2. Upload targets

카페24 서버 기준 권장 위치입니다.

```txt
www/
  index.html
  .htaccess
  assets/
  api/
    _bootstrap.php
    _product_map.php
    _response.php
    _reservation_helpers.php
    health.php
    auth/
    cart/
    my/
    products/
    reservations/
  bbs/
  admin/
  contents/
```

업로드합니다.

- `dist/index.html` -> `www/index.html`
- `dist/assets/` -> `www/assets/`
- `backend-bridge/php-api/` 안의 내용 -> `www/api/`
- `backend-bridge/cafe24-root/.htaccess` -> `www/.htaccess`

## 3. Do not upload

카페24 일반 PHP 호스팅에는 아래 파일을 올리지 않습니다.

- `node_modules/`
- `src/`
- `.git/`
- `.agents/`
- `.codex/`
- `package.json`
- `package-lock.json`
- `pnpm-lock.yaml`
- `vite.config.*`
- `docs/`
- `backend-bridge/` 폴더 자체

`backend-bridge/php-api/`의 내부 파일만 `www/api/`로 복사합니다.

## 4. First API checks

브라우저에서 아래 순서로 확인합니다.

```txt
http://wognsben19997.mycafe24.com/api/health.php
http://wognsben19997.mycafe24.com/api/auth/session.php
POST http://wognsben19997.mycafe24.com/api/auth/login.php
http://wognsben19997.mycafe24.com/api/admin/product-navigation.php
http://wognsben19997.mycafe24.com/api/admin/product-navigation-seed.php?confirm=1
http://wognsben19997.mycafe24.com/api/admin/products.php
http://wognsben19997.mycafe24.com/admin/renewal/product-navigation.php
http://wognsben19997.mycafe24.com/api/products/index.php
http://wognsben19997.mycafe24.com/api/products/navigation.php
http://wognsben19997.mycafe24.com/api/products/detail.php?id=napoli-pompei-daily&mode=reservation
http://wognsben19997.mycafe24.com/api/products/availability.php?id=napoli-pompei-daily&from=2026-07-01&to=2026-08-31
```

정상 응답은 대략 아래 형태입니다.

```json
{
  "ok": true,
  "data": {}
}
```

로그인하지 않은 상태의 `auth/session.php`는 `isLoggedIn: false`가 나와도 정상입니다.

## 5. Login-required API checks

로그인 후 확인합니다.

```txt
http://wognsben19997.mycafe24.com/api/cart/index.php
http://wognsben19997.mycafe24.com/api/my/reservations.php
```

예약 저장 계열은 브라우저 주소창에서 직접 확인하기보다 프런트 버튼 또는 API 테스트 도구로 확인합니다.

- `POST /api/cart/index.php`
- `POST /api/cart/delete.php?rid=12345`
- `POST /api/reservations/draft.php`
- `POST /api/reservations/confirm.php?rid=12345`

## 6. Common failure points

### 404

- `www/api/` 위치가 맞는지 확인
- `backend-bridge/php-api` 폴더 자체를 올린 것은 아닌지 확인
- 실제 경로가 `www/api/auth/session.php`인지 확인
- `/product/detail/...` 같은 React 내부 주소가 404이면 `www/.htaccess`가 올라갔는지 확인

### 500 or blank page

- PHP 문법 오류 가능성
- 카페24 PHP 버전 확인
- `api/_bootstrap.php`에서 `bbs/common.php` 경로가 맞는지 확인
- 서버 에러 로그 확인

### `Gnuboard DB 함수를 찾을 수 없습니다.`

- `bbs/common.php`가 로드되지 않은 상태입니다.
- `www/api/_bootstrap.php` 기준으로 `www/bbs/common.php`가 존재해야 합니다.

### `LOGIN_REQUIRED`

- 로그인이 필요한 API를 비로그인 상태에서 호출한 것입니다.
- 먼저 기존 로그인 페이지에서 로그인한 뒤 같은 브라우저에서 다시 확인합니다.

### `PRODUCT_NOT_MAPPED`

- React 상품 ID와 기존 `g5_write_product.wr_id` 매핑이 빠진 상태입니다.
- `api/_product_map.php`와 `src/pages/product/productLegacyIds.ts`를 같이 확인합니다.

## 7. After first success

첫 서버 확인이 성공하면 다음 순서로 진행합니다.

1. 실제 상품 상세에서 예약 가능일 확인
2. 로그인 후 장바구니 담기
3. 장바구니 목록 조회
4. 예약 진행하기
5. 예약 입력/확정
6. 마이페이지 예약 목록 확인
7. 기존 관리자 예약 목록에 row가 보이는지 확인

## 8. Later tasks

- Rewrite 적용: `/api/products/detail.php?id=...`를 `/api/products/:id`처럼 정리
- 관리자 CSS/UI 정리
- 모바일 QA
- 결제 연동 검토
- 예약 취소/환불 API
