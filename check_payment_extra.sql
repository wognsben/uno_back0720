SELECT ApplNum, COUNT(*) AS cnt
FROM kspay_result
WHERE ApplNum <> ''
GROUP BY ApplNum
HAVING COUNT(*) > 1
ORDER BY cnt DESC, ApplNum DESC
LIMIT 20;

SELECT f.id, f.rid, f.fee_gubun, f.pay_gubun, f.in_date, f.card_pay, f.CancelDate, k.OrderNumber, k.TotPrice, k.Result, k.ResultCode, k.AppDate, k.CancelDate AS kspay_cancel_date
FROM tour_reg_pkg_fee f
LEFT JOIN kspay_result k
    ON k.ApplNum = f.card_pay
WHERE f.fee_gubun = 'fee_air'
  AND f.card_pay <> ''
ORDER BY f.id DESC, k.id DESC
LIMIT 20;
