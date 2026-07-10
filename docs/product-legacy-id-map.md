<!--
product-legacy-id-map.md
새 React 상품 ID와 기존 우노트래블 DB의 g5_write_product.wr_id/pid를 연결하기 위한 매핑표 문서입니다.
상품 목록, 상세, 예약 API가 같은 상품을 바라보도록 legacyProductId 확정 상태를 관리합니다.
실제 상품 데이터 파일이 아니며, 백엔드 연결 전 누락/오매핑을 확인하기 위한 작업표 역할입니다.
-->

# Product Legacy ID Map

## 목적

새 프런트 상품 ID는 사람이 읽기 쉬운 문자열입니다.

기존 DB 상품 ID는 `g5_write_product.wr_id` 숫자이며, 기존 예약 저장에서는 `pid`로 사용됩니다.

예약 API는 반드시 `legacyProductId`가 확정된 상품만 호출해야 합니다.

## 매핑 상태

| Status | 의미 |
| --- | --- |
| `confirmed` | 기존 DB `wr_id/pid` 확인 완료 |
| `candidate` | 기존 코드/예시에서 유력하지만 운영 DB 확인 필요 |
| `todo` | 아직 확인 필요 |
| `not-bookable` | 예약 대상 아님 |

## 세미패키지

| React ID | 현재 화면명 | Current URL | legacyProductId | Status | 메모 |
| --- | --- | --- | --- | --- | --- |
| `italy-11` | 이탈리아 일주 9박 11일 | `/product/detail/italy-11` | `82` | `candidate` | `www/include/header.php.bak`에 `/contents/tour_view.php?pid=82` = `이탈리아 일주 10박12일` 흔적 있음. 현재 화면명 9박 11일과 다르므로 운영 DB 확인 필요 |
| `italy-9` | 이탈리아 일주 7박 9일 | `/product/detail/italy-9` |  | `todo` | 기존 `g5_write_product`에서 상품명 검색 필요 |
| `dolomiti-11` | [8-9]월 한정 이탈리아일주+돌로미티 11 | `/product/detail/dolomiti-11` |  | `todo` | 기존 `g5_write_product`에서 상품명 검색 필요 |
| `sicilia-9` | 나의 두번째 이탈리아, 지중해의 황금빛 시칠리아 일주 9일 | `/product/detail/sicilia-9` |  | `todo` | 기존 `g5_write_product`에서 상품명 검색 필요 |
| `art-tour-11` | 이탈리아 아트투어 일주 9박 11일 | `/product/detail/art-tour-11` |  | `todo` | 기존 `g5_write_product`에서 상품명 검색 필요 |

## 데일리투어

| React ID | 현재 화면명 | Current URL | legacyProductId | Status | 메모 |
| --- | --- | --- | --- | --- | --- |
| `rome-vatican-daily` | 로마 바티칸 집중 투어 | `/product/detail/daily/rome-vatican-daily` | `1` | `candidate` | `uno_170331.sql` 기준 `pid=1`, `ca=단체`, `subject=로마 바티칸 투어`. 현재 운영 DB에서는 오전/오후 반일 투어 `45/50`과 구분 필요 |
| `rome-city-walk` | 로마 시내 워킹 투어 | `/product/detail/daily/rome-city-walk` |  | `todo` | 덤프 후보: `pid=3 로마 야경 투어`, `pid=9 고대 로마 투어`, `pid=10 바로크 로마 투어`, `pid=59 로마 차타고 투어`. 현재 화면명과 직접 일치하는 상품은 아직 미확인 |
| `firenze-uffizi-daily` | 피렌체 우피치 미술관 투어 | `/product/detail/daily/firenze-uffizi-daily` |  | `todo` | 덤프 후보: `pid=15 [단독] 피렌체 & 피사 투어`, `pid=60 로마에서 피렌체 투어`, `pid=61 피렌체에서 로마 투어`. 우피치 상품 직접 일치 미확인 |
| `venezia-walk-daily` | 베네치아 수상 도시 산책 | `/product/detail/daily/venezia-walk-daily` | `11` | `candidate` | `uno_170331.sql` 기준 `pid=11`, `subject=베네치아 시티 투어`, 단 `ca=단체-숨김`. 운영 DB에서 현재 노출 상품인지 확인 필요 |
| `napoli-pompei-daily` | 나폴리 · 폼페이 데일리 투어 | `/product/detail/daily/napoli-pompei-daily` | `63` | `candidate` | 사용자가 제공한 현재 예약 예시의 `[남부아말피코스트투어](tour_view.php?pid=63)`와 가장 가까운 후보. 오래된 덤프에는 `pid=4 남부 아말피 코스트 투어`, `pid=62 남부 아말피 코스트 투어`도 존재하므로 운영 DB 기준 최종 확인 필요 |

## 덤프에서 확인된 데일리투어 후보

아래 목록은 `dump0406.sql`, `uno_170331.sql`의 `g4_write_product`에서 추출한 과거 DB 후보입니다.

현재 PHP 코드는 `g5_write_product`를 사용하므로, 아래 값은 운영 DB 확정값이 아니라 매핑 후보입니다.

| Legacy PID | Category | Subject | React 후보 |
| --- | --- | --- | --- |
| `1` | 단체 | 로마 바티칸 투어 | `rome-vatican-daily` |
| `3` | 단체 | 로마 야경 투어 | `rome-city-walk` 후보 아님, 별도 상품 후보 |
| `4` | 단체차량 | 남부 아말피 코스트 투어 | `napoli-pompei-daily`의 과거 후보 |
| `5` | 차량-숨김 | [단독] 로마 바티칸 + 시내 투어 | 단독/숨김 상품 |
| `6` | 차량 | [단독] 남부 카프리 투어 | 별도 남부 상품 후보 |
| `8` | 단체차량 | 카프리 투어 | 별도 남부 상품 후보 |
| `9` | 단체 | 고대 로마 투어 | 로마 워킹 계열 후보 |
| `10` | 단체 | 바로크 로마 투어 | 로마 워킹 계열 후보 |
| `11` | 단체-숨김 | 베네치아 시티 투어 | `venezia-walk-daily` 후보 |
| `12` | 차량 | [단독] 남부 아말피 코스트 투어 A | 단독 남부 상품 |
| `15` | 차량-숨김 | [단독] 피렌체 & 피사 투어 | 피렌체 계열 후보 |
| `17` | 차량-숨김 | [단독] 남부 아말피 코스트 투어 B | 단독 남부 상품 |
| `18` | 단체-숨김 | 베네치아 야경투어 | 베네치아 계열 후보 |
| `32` | 단체-숨김 | 바티칸 플러스 투어 | 바티칸 계열 후보 |
| `45` | 단체 | [오전] 바티칸 반일 투어 | 바티칸 반일 상품 |
| `50` | 단체 | [오후]바티칸 반일 투어 | 바티칸 반일 상품 |
| `59` | 단체차량 | 로마 차타고 투어 | 로마 차량 상품 |
| `60` | 단체차량 | 로마에서 피렌체 투어 | 피렌체 이동 상품 |
| `61` | 단체차량 | 피렌체에서 로마 투어 | 피렌체 이동 상품 |
| `62` | 차량-숨김 | 남부 아말피 코스트 투어 | 남부 상품의 과거/숨김 후보 |

## 기존 DB에서 확인할 방법

운영 DB 또는 기존 관리자에서 아래 기준으로 확인합니다.

```sql
select wr_id, ca_name, wr_subject, wr_reg_result, is_passport, is_roominfo, is_delivery
from g5_write_product
where wr_is_comment = 0
  and length(wr_subject) > 2
  and not (ca_name like '%숨김%')
order by ca_name, wr_subject;
```

상품별 요금 옵션 확인:

```sql
select id, wr_id, fee_subject, fee1, fee2, fee3, is_first
from tour_fee
where wr_id = {legacyProductId}
order by id;
```

패키지/세미패키지 일정 확인:

```sql
select id, start_time, arrive_time, fee_1, fee_2, fee_3, fee_air, price
from v2_pkgTour
where del_time < 111
  and is_view = '1'
  and is_main = '1'
order by start_time;
```

## React 코드 반영 위치

매핑이 확정되면 아래 중 하나로 반영합니다.

1. 프런트 정적 데이터에 `legacyProductId` 추가
2. 백엔드 상품 API에서 React ID와 `wr_id` 매핑 처리
3. 별도 매핑 테이블 생성

초기에는 2번 또는 3번이 더 안전합니다. 기존 DB의 상품명이 바뀌어도 React 코드 전체를 수정하지 않아도 되기 때문입니다.

## 예약 버튼 차단 규칙

`legacyProductId`가 없으면 예약 버튼은 API를 호출하지 않습니다.

화면 안내:

```text
현재 상품은 예약 준비 중입니다. 카카오톡 채널로 문의해 주세요.
```

이렇게 하면 잘못된 `pid`로 기존 `tour_reg`에 예약이 들어가는 사고를 막을 수 있습니다.
