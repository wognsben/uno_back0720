<?php
/*
 * _product_map.php
 * React 상품 ID를 기존 우노트래블 DB의 상품 ID와 예약 옵션 후보로 연결하는 PHP 매핑 파일입니다.
 * products/detail.php, availability, reservation 저장 API가 같은 pid/fee_id 기준을 쓰도록 돕습니다.
 * 실제 운영 DB 확인 후 값만 조정하면 되며, DB 조회 로직이나 화면 출력은 담당하지 않습니다.
 */

if (!defined('UNO_API_BOOTSTRAPPED')) {
    http_response_code(500);
    exit;
}

require_once __DIR__ . '/_product_mapping_store.php';

function uno_api_product_default_map()
{
    return array(
        'italy-11' => array(
            'legacyProductId' => 82,
            'legacyCategory' => 'semi',
            'confidence' => 'confirmed',
        ),
        'italy-9' => array(
            'legacyProductId' => 87,
            'legacyCategory' => 'semi',
            'confidence' => 'needs-confirmation',
        ),
        'dolomiti-11' => array(
            'legacyProductId' => 97,
            'legacyCategory' => 'semi',
            'confidence' => 'needs-confirmation',
        ),
        'sicilia-9' => array(
            'legacyProductId' => 89,
            'legacyCategory' => 'semi',
            'confidence' => 'needs-confirmation',
        ),
        'art-tour-11' => array(
            'legacyProductId' => 88,
            'legacyCategory' => 'semi',
            'confidence' => 'needs-confirmation',
        ),
        'rome-vatican-daily' => array(
            'legacyProductId' => 1,
            'legacyFeeOptionId' => 4,
            'legacyCategory' => 'daily',
            'confidence' => 'needs-confirmation',
        ),
        'rome-city-walk' => array(
            'legacyProductId' => 66,
            'legacyCategory' => 'daily',
            'confidence' => 'needs-confirmation',
        ),
        'firenze-uffizi-daily' => array(
            'legacyProductId' => 74,
            'legacyFeeOptionId' => 258,
            'legacyCategory' => 'daily',
            'confidence' => 'needs-confirmation',
        ),
        'venezia-walk-daily' => array(
            'legacyProductId' => 11,
            'legacyFeeOptionId' => 28,
            'legacyCategory' => 'daily',
            'confidence' => 'needs-confirmation',
        ),
        'napoli-pompei-daily' => array(
            'legacyProductId' => 63,
            'legacyFeeOptionId' => 218,
            'legacyCategory' => 'daily',
            'confidence' => 'confirmed',
        ),
    );
}

function uno_api_product_map()
{
    $storedMap = uno_api_product_mapping_fetch_map();

    if (is_array($storedMap) && count($storedMap) > 0) {
        return $storedMap;
    }

    return uno_api_product_default_map();
}

function uno_api_product_mapping($productId)
{
    $map = uno_api_product_map();
    return isset($map[$productId]) ? $map[$productId] : null;
}

function uno_api_product_id_from_legacy($legacyProductId)
{
    foreach (uno_api_product_map() as $productId => $mapping) {
        if (isset($mapping['legacyProductId']) && (string) $mapping['legacyProductId'] === (string) $legacyProductId) {
            return $productId;
        }
    }

    return '';
}
