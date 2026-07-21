SELECT f.id, f.rid, f.fee_gubun, f.pay_gubun, f.in_date, f.card_pay, f.CancelDate AS fee_cancel_date, k.id AS kspay_id, k.Result, k.ResultCode, k.OrderNumber, k.TotPrice, k.AppDate, k.ApplNum, k.CancelDate AS kspay_cancel_date
FROM tour_reg_pkg_fee f
LEFT JOIN kspay_result k
ON k.ApplNum = f.card_pay
WHERE f.card_pay <> ''
ORDER BY f.id DESC, k.id DESC
LIMIT 20;

SELECT rid, fee_gubun, COUNT(*) AS cnt
FROM tour_reg_pkg_fee
GROUP BY rid, fee_gubun
HAVING COUNT(*) > 1
ORDER BY cnt DESC, rid DESC;
