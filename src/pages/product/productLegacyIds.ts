// productLegacyIds.ts
// React 상품 ID와 기존 우노트래블 DB의 wr_id/pid 후보값을 연결하는 매핑 파일이다.
// ProductTemplate 목록 데이터와 ProductDetail 예약 payload가 같은 legacyProductId를 쓰도록 관리한다.
// 실제 운영 DB 확인 전 후보값만 보관하며, 예약 API 구현 시 미확정 상품 차단 기준으로 사용한다.

export type ProductLegacyId = number | string;

export const PRODUCT_LEGACY_ID_CANDIDATES: Record<string, ProductLegacyId> = {
  "italy-11": 82,
  "rome-vatican-daily": 1,
  "venezia-walk-daily": 11,
  "napoli-pompei-daily": 63,
};

export const getLegacyProductId = (
  productId: string,
): ProductLegacyId | undefined => PRODUCT_LEGACY_ID_CANDIDATES[productId];
