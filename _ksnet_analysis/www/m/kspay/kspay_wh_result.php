<? 
include_once("./_common.php");
include "./KSPayWebHost.inc"; 
?>
<?
  $result_query = "";
  $rcid       = $_POST["reCommConId"];
  $rctype     = $_POST["reCommType"];
  $rhash      = $_POST["reHash"];
	// rcid 없으면 결제를 끝까지 진행하지 않고 중간에 결제취소 
	$ipg = new KSPayWebHost($rcid, null);

	$authyn		= "";
	$trno		= "";
	$trddt		= "";
	$trdtm		= "";
	$amt		= "";
	$authno		= "";
	$msg1		= "";
	$msg2		= "";
	$ordno		= "";
	$isscd		= "";
	$aqucd		= "";
	$temp_v		= "";
	$result		= "";

	$resultcd =  "";

	if ($ipg->send_msg("1"))
	{
		$authyn	 = $ipg->getValue("authyn");
		$trno	 = $ipg->getValue("trno"  );
		$trddt	 = $ipg->getValue("trddt" );
		$trdtm	 = $ipg->getValue("trdtm" );
		$amt	 = $ipg->getValue("amt"   );
		$authno	 = $ipg->getValue("authno");
		$msg1	 = $ipg->getValue("msg1"  );
		$msg1 = iconv("euc-kr","utf-8",$msg1 );		
		$msg2	 = $ipg->getValue("msg2"  );
		$msg2 = iconv("euc-kr","utf-8",$msg2 );		
		$ordno	 = $ipg->getValue("ordno" );
		$isscd	 = $ipg->getValue("isscd" );
		$aqucd	 = $ipg->getValue("aqucd" );
		//$temp_v	 = $ipg->getValue("temp_v");
		$result	 = $ipg->getValue("result");

		if (!empty($authyn) && 1 == strlen($authyn))
		{
			if ($authyn == "O")
			{
				$resultcd = "0000";
			}else
			{
				$resultcd = trim($authno);
			}

			$ipg->send_msg("3");
		}
	}

//결제방법
if(substr($result,0,1) == '2') {
	$PayMethod = '계좌이체'; 
}
else $PayMethod = '신용카드';
/*
if(substr($result,0,1) == '1') {
	$PayMethod = '신용카드'; //결제방법
} else
else $PayMethod = substr($result,0,1);
*/

$AppDate =$trddt." ".$trdtm; //거래시간
$TotPrice = trim($amt); //거래금액
$TID = $trno; //거래번호
$MOID = $ordno; //주문번호
$rtn_oid_a=explode("_", $MOID);
$rtn_oid = $rtn_oid_a[1]; //고객 ID값


if($authyn == "O"){

	$tmp = sql_fetch("select * from kspay_result where OrderNumber='$MOID' "); //kspay으로 테이블 변경해야함

	if(!$tmp[TID]) {
		sql_query("INSERT INTO kspay_result(PayMethod,Result,ResultCode,OrderNumber,TotPrice,AppDate,ApplNum,AppCode,AquCode,Meassage1,Meassage2) VALUES ('$PayMethod','$authyn','$resultcd','$MOID','$TotPrice','$AppDate','$TID','$isscd','$aqucd','$msg1','$msg2')");

		$result_query = sql_query("update tour_reg set card_pay = '$TID' where id='$rtn_oid' ");
		$result_query = sql_query("update tour_reg set card_pay = '$TID' where parent_id='$rtn_oid' and isEvent='Y'");//이벤트가 있는경우 같이 업데이트

		if($rtn_oid_a[2]) { //추가 코드가 있으면 패키지임. 패키지용 별도 관리에 추가.
			//sql_query("INSERT INTO `tour_reg_pkg_fee` (  `rid`,  `fee_gubun`,  `pay_gubun`,  `in_date`,  `card_pay`) VALUES  (    '{$rtn_oid}',    '{$rtn_oid_a[2]}',    'PG',    '".G5_TIME_YMDHIS."',    '$TID'  ) ");
			sql_query(" UPDATE  `tour_reg_pkg_fee` SET    `pay_gubun` = 'PG',  `in_date` = '".G5_TIME_YMDHIS."',  `card_pay` = '$TID' WHERE `rid` = '{$rtn_oid}' and `fee_gubun`='{$rtn_oid_a[2]}' ");
		}
	}
}


?>
<script>
<? 
if($result_query) {?>
	alert("결제가 완료 되었습니다.");
		
<?} else {?>
	alert("실패하였습니다.");
	
<?}?>
	opener.parent.location.reload();
	self.close();

</script>
