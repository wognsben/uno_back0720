<!--
mobile-responsive-plan.md
UNO Travel 새 React 프런트의 모바일 대응 방식을 정리한 계획 문서입니다.
기존 우노트래블처럼 데스크탑/모바일 파일을 분리하지 않고 하나의 반응형 프런트로 관리하는 기준을 설명합니다.
백엔드 CSS나 관리자 모바일 화면이 아니라, 사용자 프런트 모바일 QA와 레이아웃 정리 범위를 구분하는 역할입니다.
-->

# Mobile Responsive Plan

## Direction

새 UNO Travel 프런트는 별도 모바일 사이트로 나누지 않고 하나의 React 코드에서 반응형으로 대응합니다.

기존 방식:

```txt
desktop PHP/CSS
mobile PHP/CSS
```

새 방식:

```txt
one React frontend
responsive CSS
component-level layout adjustment
```

## Why

- 같은 내용을 데스크탑/모바일에서 두 번 수정하지 않아도 됩니다.
- 예약 상태, 상품 데이터, 로그인 흐름이 한 코드에서 유지됩니다.
- 카페24 PHP 호스팅에서도 정적 프런트와 PHP API 구조를 단순하게 유지할 수 있습니다.

## Priority

1. Main page
2. Product detail page
3. Reservation module and bottom booking dock
4. Login and signup
5. My page reservation list
6. Footer
7. Info pages

## Product detail mobile checks

- 상단 product navigation이 화면 폭을 넘지 않는지 확인
- 상품 이미지, 제목, 가격, 예약 상태가 겹치지 않는지 확인
- 하단 fixed booking dock이 화면 아래에서 안정적으로 고정되는지 확인
- 날짜/인원 선택 패널이 작은 화면에서 스크롤 가능한지 확인
- 닫기 버튼, 장바구니, 예약하기 CTA 터치 영역이 충분한지 확인

## Reservation mobile checks

- 날짜 선택
- 인원 선택
- soldout disabled 상태
- 로그인 필요 팝업
- 장바구니 보기 이동
- 예약 진행 이동
- 예약 입력 폼
- 여권 정보/룸 정보 섹션

## Admin mobile

관리자 화면은 당장 모바일 전용으로 새로 만들지 않습니다.

1차 목표:

- PC 관리자 화면에서 예약 관리가 정상 작동
- 모바일에서는 깨지지 않고 최소 확인 가능

2차 목표:

- 예약 목록, 예약 상세, 상태 변경만 모바일 대응

## Breakpoint guide

권장 기준:

- `max-width: 1024px`: tablet layout adjustment
- `max-width: 768px`: mobile layout
- `max-width: 480px`: compact mobile layout

## Do not do

- 별도 `/m` 모바일 사이트 생성
- 데스크탑과 모바일에 서로 다른 예약 로직 구현
- 모바일 전용 DB/API 분리
- 전체 디자인 톤 변경

## Next QA order

서버 API 확인 후 아래 순서로 모바일 QA를 진행합니다.

1. iPhone width around 390px
2. Android width around 360px
3. Tablet width around 768px
4. Desktop around 1440px
