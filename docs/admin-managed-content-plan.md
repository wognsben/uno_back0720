<!--
  admin-managed-content-plan.md
  Documents which renewed frontend areas should become editable from the legacy admin/backend.
  Tracks product navigation, product data, reservation calendar states, info/community/brand content, and their API boundaries.
  This is a planning document only, so it does not replace PHP API files or React UI components.
-->

# Admin Managed Content Plan

## 기준 주소

- Renewed frontend: `/`
- React API bridge: `/api`
- Legacy admin: `/admin/main.php`
- Legacy frontend reference: `/contents/index.php`
- Legacy Gnuboard/session base: `/bbs`

## 1. Product Navigation

현재 1차 적용:

- `GET /api/products/navigation.php` 추가
- React `ProductNavigation`은 API 응답을 우선 사용
- API가 없거나 실패하면 기존 정적 네비게이션으로 fallback
- 이탈리아 세미패키지와 이탈리아 데일리투어의 상품명은 기존 상품 DB에서 읽음
- `/admin/renewal/product-navigation.php` 추가 예정
- 관리자 화면에서 국가 탭, subtitle, href, region, 연결 상품 줄을 수정하고 저장 가능

다음 단계:

- 기존 `/admin/main.php` 메뉴에 리뉴얼 product navigation 링크 추가
- 노출/숨김 상태 관리
- 국가별 실제 상품 연결 관리
- 드래그 또는 버튼 기반 순서 관리

## 2. Products

현재 1차 적용:

- 기존 product map 기준으로 리뉴얼 product id와 legacy product id 연결
- 상품 목록/상세/예약 API는 기존 DB를 조회

다음 단계:

- 실제 판매 상품 전체 매핑 확정
- 상품별 대표 이미지, 요약 문구, 노출 여부 관리
- 메인 2번째 섹션과 상품 목록의 하드코딩 제거

## 3. Reservation Calendar

다음 단계:

- 날짜별 예약 가능, 마감 임박, 마감 상태 관리자 수정
- 잔여석/정원 관리
- 프런트 캘린더와 관리자 캘린더가 같은 상태값 사용

## 4. Info / Community / Brand

다음 단계:

- Info 문서 관리: 이용방법, 주의사항, 취소 및 환불 규정, 여행자 약관, 개인정보처리방침
- Community 관리: 공지사항, 여행 후기
- Brand 관리: About UNO, Contact, 푸터 회사 정보, SNS 링크

## 5. Admin UI 원칙

- 기존 관리자 로그인과 권한은 유지
- 기존 DB를 우선 사용하고 필요한 관리 테이블만 추가
- 화면은 가볍고 읽기 쉽게 구성
- 상세페이지와 예약 흐름이 무겁게 느껴지지 않도록 API 응답은 필요한 데이터만 반환
