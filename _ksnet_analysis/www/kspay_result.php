<? 
   include_once("./_common.php");
   if (function_exists("mb_http_input")) mb_http_input("utf-8"); 
   if (function_exists("mb_http_output")) mb_http_output("utf-8");
?>
<? include "./KSPayWebHost.inc"; ?>
<?
	$result_query = ""; 
    $rcid       = $_POST["reWHCid"];
    $rctype     = $_POST["reWHCtype"];
    $rhash      = $_POST["reWHHash"];

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
	$halbu		= "";
	$cbtrno		= "";
	$cbauthno		= "";

	$resultcd =  "";

	//업체에서 추가하신 인자값을 받는 부분입니다
	$a =  $_POST["a"]; 
	$b =  $_POST["b"]; 
	$c =  $_POST["c"]; 
	$d =  $_POST["d"];

    $ipg = new KSPayWebHost($rcid, null);


	if ($ipg->kspay_send_msg("1"))
	{
		$authyn	 = $ipg->kspay_get_value("authyn");
		$trno	 = $ipg->kspay_get_value("trno"  );
		$trddt	 = $ipg->kspay_get_value("trddt" );
		$trdtm	 = $ipg->kspay_get_value("trdtm" );
		$amt	 = $ipg->kspay_get_value("amt"   );
		$authno	 = $ipg->kspay_get_value("authno");
		$msg1	 = $ipg->kspay_get_value("msg1"  );
		$msg2	 = $ipg->kspay_get_value("msg2"  );
		$ordno	 = $ipg->kspay_get_value("ordno" );
		$isscd	 = $ipg->kspay_get_value("isscd" );
		$aqucd	 = $ipg->kspay_get_value("aqucd" );
		$temp_v	 = "";
		$result	 = $ipg->kspay_get_value("result");
		$halbu	 = $ipg->kspay_get_value("halbu");
		$cbtrno	 = $ipg->kspay_get_value("cbtrno");
		$cbauthno	 = $ipg->kspay_get_value("cbauthno");
		
		if (!empty($msg1)) $msg1 = iconv("EUC-KR","UTF-8", $msg1);
		if (!empty($msg2)) $msg2 = iconv("EUC-KR","UTF-8", $msg2);

		if (!empty($authyn) && 1 == strlen($authyn))
		{
			if ($authyn == "O")
			{
				$resultcd = "0000";
			}else
			{
				$resultcd = trim($authno);
			}

			//$ipg->kspay_send_msg("3"); // 정상처리가 완료되었을 경우 호출합니다.(이 과정이 없으면 일시적으로 kspay_send_msg("1")을 호출하여 거래내역 조회가 가능합니다.)
		}
	}
?>

<? 
if(substr($result,0,1) == '2') {
	$PayMethod = '계좌이체'; //결제방법
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
		$result_query = sql_query("update tour_reg set card_pay = '$TID' where parent_id='$rtn_oid' and isEvent='Y' ");//이벤트가 있는경우 같이 업데이트

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
	window.opener.location.href = "/contents/my_reser.php";
	//opener.parent.location.reload();
	self.close();

</script>
<html>
<head>
<meta http-equiv="Cache-Control" content="no-cache"> 
<meta http-equiv="Pragma" content="no-cache"> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>*** KSNET WebHost 결과 [PHP] ***</title>
<link href="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/css/pgstyle.css" rel="stylesheet" type="text/css" charset="euc-kr">
</head>
<script language="javascript">
// 현금영수증 출력 스크립트
function CashreceiptView(tr_no)
{
    receiptWin = "http://pgims.ksnet.co.kr/pg_infoc/src/bill/ps2.jsp?s_pg_deal_numb="+tr_no;
    window.open(receiptWin , "" , "scrollbars=no,width=434,height=580");
}
// 신용카드 영수증 출력 스크립트
function receiptView(tr_no)
{
	receiptWin = "http://pgims.ksnet.co.kr/pg_infoc/src/bill/credit_view.jsp?tr_no="+tr_no;
    window.open(receiptWin , "" , "scrollbars=no,width=434,height=700");
}
-->
</script>

<body>
<table width="560" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="50" align="right" background="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/bg_top.gif" class="txt_pd1">우노트래블</td>
  </tr>
  <tr>
    <td height="530" valign="top" background="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/bg_man.gif">	
	<table width="560" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td width="25">&nbsp;</td>
        <td width="505" align="center">
		<table width="500" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td height="40" style="padding:0px 0px 0px 15px; "><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_tit5.gif" width="30" height="30" align="absmiddle"> <strong>결과항목</strong></td>
      </tr>
      <tr>
        <td align="center"><table width="400" border="0" cellspacing="0" cellpadding="5">
          <tr bgcolor="#FFFFFF">
            <td width="120"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 결제방법</td>
            <td width="280">
<?
						if (empty($result) || 4 != strlen($result))
						{
							echo("(???)");
						}else
						{
							switch (substr($result,0,1))
							{
								case '1' : echo("신용카드"); break;
								case 'I' : echo("신용카드"); break;
								case '2' : echo("실시간계좌이체"); break;
								case '6' : echo("가상계좌발급"); break; 
								case 'M' : echo("휴대폰결제"); break; 
								case 'G' : echo("상품권"); break; 
								default  : echo("(????)"); break; 
							}
						}
?>
            </td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>

          <tr bgcolor="#FFFFFF">
            <td width="120"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 성공여부</td>
            <td width="280"><?echo($authyn)?>(<? if(!empty($authyn) && "O" == $authyn) echo("승인성공"); else echo("승인거절"); ?>) <!-- <font color=red> :성공여부값은 영어 대문자 O,X입니다. </font> --></td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>
          <tr bgcolor="#FFFFFF">
            <td width="120"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 응답코드</td>
            <td width="280"><?echo($resultcd)?></td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>
          <tr bgcolor="#FFFFFF">
            <td width="120"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 주문번호</td>
            <td width="280"><?echo($ordno)?></td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>
          <tr bgcolor="#FFFFFF">
            <td width="120"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 금액</td>
            <td width="280"><?echo($amt)?></td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>
          <tr bgcolor="#FFFFFF">
            <td width="120"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 거래번호</td>
            <td width="280"><?echo($trno)?> <font color=red>:KSNET에서 부여한 고유번호입니다. </font></td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>
          <tr bgcolor="#FFFFFF">
            <td width="120"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 거래일자</td>
            <td width="280"><?echo($trddt)?></td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>
          <tr bgcolor="#FFFFFF">
            <td width="120"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 거래시간</td>
            <td width="280"><?echo($trdtm)?></td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>
<? if (!empty($authyn) && "O" == $authyn) { ?>
          <tr bgcolor="#FFFFFF">
            <td width="120"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 카드사 승인번호/은행 코드번호</td>
            <td width="280"><?echo($authno)?><!-- <font color=red>:카드사에서 부여한 번호로 고유한값은 아닙니다. </font> --></td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>
<? } ?>
          <tr bgcolor="#FFFFFF">
            <td width="120"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 발급사코드/가상계좌번호/계좌이체번호</td>
            <td width="280"><?echo($isscd)?></td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>
          <tr bgcolor="#FFFFFF">
            <td width="120"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 매입사코드</td>
            <td width="280"><?echo($aqucd)?></td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>
          <tr bgcolor="#FFFFFF">
            <td width="120"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 메시지1</td>
            <td width="280"><?echo($msg1)?></td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>
          <tr bgcolor="#FFFFFF">
            <td width="120"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 메시지2</td>
            <td width="280"><?echo($msg2)?></td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>

		<? if (!empty($authyn) && "O" == $authyn && "1" == substr($trno,0,1)) { ?> <!-- 정상승인의 경우만 영수증출력: 신용카드의 경우만 제공 -->
          <tr bgcolor="#FFFFFF">
            <td width="400" colspan="2" align="center"> <input type="button" value="영수증출력" onClick="javascript:receiptView('<?echo($trno)?>')"> </td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>
      	<? } ?>
      	<? if (!empty($authyn) && "O" == $authyn && "2" == substr($trno,0,1)) { ?> <!-- 정상승인의 경우만 영수증출력: 계좌이체의 경우만 제공 -->
          <tr bgcolor="#FFFFFF">
            <td width="400" colspan="2" align="center"> <input type="button" value="현금영수증출력" onClick="javascript:CashreceiptView('<?echo($cbtrno)?>')"> </td>
          </tr>
          <tr bgcolor="#E3E3E3"> <td height="1" colspan="2"></td> </tr>
      	<? } ?>
        </table></td>
      </tr>
    </table>
		</td>
        <td width="30">&nbsp;</td>
      </tr>
    </table>
	</td>
  </tr>
  <tr>
    <td height="37" background="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/bg_bot.gif">&nbsp;</td>
  </tr>
</table>
</body>
</html>
