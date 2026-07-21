<?php
include_once("./_common.php");

function uno_ksnet_payment_reject($message)
{
	$message = addslashes((string) $message);
	echo "<script>alert('".$message."'); self.close();</script>";
	exit;
}

function uno_ksnet_payment_money($value)
{
	return (int) preg_replace('/[^0-9-]/', '', (string) $value);
}

function uno_ksnet_payment_is_event_child($row)
{
	$parentId = isset($row['parent_id']) ? (int) $row['parent_id'] : 0;
	$isEvent = isset($row['isEvent']) ? strtoupper(trim((string) $row['isEvent'])) : '';

	return $parentId > 0 || $isEvent === 'Y';
}

function uno_ksnet_payment_is_general($resData, $pData)
{
	$nation = isset($resData['nation']) ? (string) $resData['nation'] : '';
	$category = isset($pData['ca_name']) ? (string) $pData['ca_name'] : '';

	if ($nation === '패키지' || $category === '패키지') {
		return false;
	}

	if (stripos($nation, '세미패키지') !== false || stripos($category, '세미패키지') !== false) {
		return false;
	}

	return true;
}

function uno_ksnet_payment_latest_payment($cardPay)
{
	$cardPay = trim((string) $cardPay);
	if ($cardPay === '') {
		return null;
	}

	$cardPay = function_exists('sql_escape_string') ? sql_escape_string($cardPay) : addslashes($cardPay);
	$row = sql_fetch("select id, Result, ResultCode, ApplNum, CancelDate from kspay_result where ApplNum = '{$cardPay}' order by id desc limit 1");

	if (!$row || empty($row['id'])) {
		return null;
	}

	return $row;
}

function uno_ksnet_payment_is_success_payment($payment)
{
	if (!$payment) {
		return false;
	}

	$result = isset($payment['Result']) ? trim((string) $payment['Result']) : '';
	$resultCode = isset($payment['ResultCode']) ? trim((string) $payment['ResultCode']) : '';
	$cancelDate = isset($payment['CancelDate']) ? trim((string) $payment['CancelDate']) : '';

	return $result === 'O' && $resultCode === '0000' && $cancelDate === '';
}

if($sel) {

	$sel_a=explode(";",$sel); //1차구분해서
	$rid_a=explode("_", $sel_a[0]);
	$rid_a=explode("_", $sel_a[0]); //패키지의경우 rid 뒤의 추가 코드 fee1, fee2, fee3, fee_air 확인을 위해.


	$resData=sql_fetch("select * from `tour_reg` where id='$rid_a[0]' ");
	//echo "select * from `tour_reg` where id='$sel_a[0]'";

	$pData=sql_fetch("select wr_subject,ca_name from g5_write_product where wr_id='$resData[pid]' ");
//    var_dump($sel);

	if(empty($member['mb_id'])) {
		uno_ksnet_payment_reject('Login is required.');
	}
	if(!$resData || empty($resData['id'])) {
		uno_ksnet_payment_reject('Reservation was not found.');
	}
	if((string) $resData['mb_id'] !== (string) $member['mb_id']) {
		uno_ksnet_payment_reject('Reservation owner does not match.');
	}
	if(uno_ksnet_payment_is_event_child($resData)) {
		uno_ksnet_payment_reject('Event child reservations cannot be paid directly.');
	}
	if(!uno_ksnet_payment_is_general($resData, $pData)) {
		uno_ksnet_payment_reject('Package payments are excluded from this flow.');
	}
	if((string) $resData['status'] !== '2') {
		uno_ksnet_payment_reject('Only checked reservations can start card payment.');
	}

	$unoTotalFee1 = uno_ksnet_payment_money(isset($resData['total_fee1']) ? $resData['total_fee1'] : 0);
	$unoTotalFee4 = uno_ksnet_payment_money(isset($resData['total_fee4']) ? $resData['total_fee4'] : 0);
	if($unoTotalFee1 <= 0 || ($unoTotalFee1 + $unoTotalFee4) <= 0) {
		uno_ksnet_payment_reject('Payment amount is invalid.');
	}

	$unoLatestPayment = uno_ksnet_payment_latest_payment(isset($resData['card_pay']) ? $resData['card_pay'] : '');
	if(uno_ksnet_payment_is_success_payment($unoLatestPayment)) {
		uno_ksnet_payment_reject('This reservation is already paid.');
	}

	if($pData[ca_name]=="패키지") {
		$fee=str_replace(",","",$resData['total_'.$rid_a[1]]);
	}
    else if (stripos($pData[ca_name], '세미패키지') !== false) {
        $fee=str_replace(",","",$resData['total_'.$rid_a[1]]);
    }
	else {
		$fee=str_replace(",","",$resData[total_fee1]);
		$tiket_fee=str_replace(",","",$resData[total_fee4]);
		
		$fee += $tiket_fee;
	}
	
	$subject=$pData[wr_subject];
	if($rid_a[1]) {
		$oid=$member[mb_no]."_".$rid_a[0]."_".$rid_a[1]; //패키지 결제
		//$fee="1000";
	}
	else $oid=$member[mb_no]."_".$rid_a[0];
	//$subject= iconv( "UTF-8", "EUC-KR", $subject);
	
	
}
$mb_name=$member[mb_name];
?>

<html>
<head>
<meta http-equiv="Cache-Control" content="no-cache"> 
<meta http-equiv="Pragma" content="no-cache"> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>*** KSNET WebHost</title>
<link href="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/css/pgstyle.css" rel="stylesheet" type="text/css" charset="euc-kr">
</head>
<script language="javascript">

	function _pay(_frm) 
	{
 		_frm.sndReply.value           = getLocalUrl("kspay_wh_rcv.php") ;

		var agent = navigator.userAgent;
		var midx		= agent.indexOf("MSIE");
		var out_size	= (midx != -1 && agent.charAt(midx+5) < '7');
    	
		var width_	= 500;
		var height_	= out_size ? 568 : 518;
		var left_	= screen.width;
		var top_	= screen.height;
    	
		left_ = left_/2 - (width_/2);
		top_ = top_/2 - (height_/2);
		
		op = window.open('about:blank','AuthFrmUp',
		        'height='+height_+',width='+width_+',status=yes,scrollbars=no,resizable=no,left='+left_+',top='+top_+'');

		if (op == null)
		{
			alert("팝업이 차단되어 결제를 진행할 수 없습니다.");
			return false;
		}
		
		_frm.target = 'AuthFrmUp';
		_frm.action ='https://kspay.ksnet.to/store/KSPayFlashV1.3/KSPayPWeb.jsp?sndCharSet=utf-8';
		//_frm.action ='http://210.181.28.116/store/KSPayFlashV1.3/KSPayPWeb.jsp?sndCharSet=utf-8';
		
		_frm.submit();
    }

	function getLocalUrl(mypage) 
	{ 
		var myloc = location.href; 
		return myloc.substring(0, myloc.lastIndexOf('/')) + '/' + mypage;
	} 
	
	// goResult() - 함수설명 : 결재완료후 결과값을 지정된 결과페이지(kspay_wh_result.php)로 전송합니다.
	function goResult(){
		document.KSPayWeb.target = "";
		document.KSPayWeb.action = "./kspay_result.php";
		document.KSPayWeb.submit();
	}
	// eparamSet() - 함수설명 : 결재완료후 (kspay_wh_rcv.php로부터)결과값을 받아 지정된 결과페이지(kspay_wh_result.php)로 전송될 form에 세팅합니다.
	function eparamSet(rcid, rctype, rhash){
		document.KSPayWeb.reWHCid.value 	= rcid;
		document.KSPayWeb.reWHCtype.value   = rctype  ;
		document.KSPayWeb.reWHHash.value 	= rhash  ;
	}

</script>
<body>
<!-----------------------------------------<Part 1. KSPayWeb Form: 결과페이지주소 설정 > ---------------------------------------->
<!--결제 완료후 결과값을 받아처리할 결과페이지의 주소-->
<form name=KSPayWeb action = "./kspay_result.php" method=post>
<input type='hidden' name='sndStoreid' value='2659100038' size='15' maxlength='10'>
<input type='hidden' name='sndOrdernumber' value='<?=$oid?>'>
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
<table border='0' cellpadding='0' cellspacing='0' width='500' align='center'>
    
   		
       
    <tr>
      <td height="40" style="padding:0px 0px 0px 15px; ">
    <!--옵션정보 : 옵션 사항 입니다. 설정 안하거나 값을 보내지 않을경우 default 값으로 설정됩니다.-->
    
    </tr>
    <tr>
      <td align="center"><table width="400" border="0" cellspacing="0" cellpadding="0">
      	 <tr>
          <td width="110"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 결제수단</td> <!--신용카드/가상계좌/계좌이체/월드패스카드/포인트/휴대폰결제/상품권-->
          <td width="290">
      			<label><input type="radio" name="sndPayMethod" value="1000000000" checked _onclick="$('#info1').show()"> 신용카드</label>  <!-- 신용카드인 경우 -->
      			<label><input type="radio" name="sndPayMethod" value="0010000000" _onclick="$('#info1').show()"> 계좌 이체</label>
		  </td>
        </tr>
      	
        <tr>
          <td width="110"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 상품명</td> 
		  <!--상품명 50Byte(한글 25자) 입니다. ' " ` 는 사용하실수 없습니다. 따옴표,쌍따옴표,백쿼테이션 -->
          <td width="290"><input type='text' name='sndGoodname' value='<?=$subject?>' size='30' readonly></td>
        </tr>
        <tr bgcolor="#E3E3E3">
          <td height="1" colspan="2"></td>
        </tr>
        <tr>
          <td width="110"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 금액</td> 
		  <!--금액은 ,없이 입력 -->
          <td width="290"><input type='text' name='sndAmount' value='<?=$fee?>' size='15' maxlength='9' readonly></td>
        </tr>
        <tr bgcolor="#E3E3E3">
          <td height="1" colspan="2"></td>
        </tr>
        <tr>
          <td width="110"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 주문자명</td> 
          <td width="290"><input type='text' name='sndOrdername' value='<?=$mb_name?>' size='30' readonly></td>
        </tr>
        <tr bgcolor="#E3E3E3">
          <td height="1" colspan="2"></td>
        </tr>
        <tr>
          <td width="110"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 전자우편</td> 
		  <!--KSPAY에서 결제정보를 메일로 보내줍니다.(신용카드거래에만 해당)-->
          <td width="290"><input type='text' name='sndEmail' value='<?=$member[mb_email]?>' size='30'></td>
        </tr>
        <tr bgcolor="#E3E3E3">
          <td height="1" colspan="2"></td>
        </tr>
        <tr>
          <td width="110"><img src="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/ico_right.gif" width="11" height="11" align="absmiddle"> 이동전화</td> 
		  <!--전화번호 value 값에 숫자만 넣게 해주시길 바랍니다. : '-' 가 들어가면 안됩니다.-->
          <td width="290"><input type='text' name='sndMobile' value='<?=$member[mb_hp]?>' size='12' maxlength='12'></td>
        </tr>
        <tr bgcolor="#E3E3E3">
          <td height="1" colspan="2"></td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td height="40" align="center"><input type="button" value="결 제" onClick="javascript:_pay(document.KSPayWeb);"></td>
    </tr>
    <!-- <tr>
        <td height="66" align='center' background="http://kspay.ksnet.to/store/KSPayFlashV1.3/mall/imgs/bg_rem.gif"> 위의 파라미터는 기본파라미터만 정의되어있으며 <br>
        	추가파라미터 기능추가는 소스상에 hidden으로 처리되어있으므로  <br>
			메뉴얼 참조후 세팅하여 사용하시면 됩니다. </td>
    </tr> -->
</table>
	<input type=hidden  name=sndServicePeriod  value="YYYY년MM월DD일~YYYY년MM월DD일"> <!-- 실제 배송상품이아닌 컨텐츠상품시 제공기간표시 -->
<!----------------------------------------------- <Part 2. 추가설정항목(메뉴얼참조)>  ----------------------------------------------->

	<!-- 0. 공통 환경설정 -->
	<input type=hidden	name=sndReply value="">
	<input type=hidden  name=sndGoodType value="1"> 	<!-- 상품유형: 실물(1),디지털(2) -->
	
	<!-- 1. 신용카드 관련설정 -->
	
	<!-- 신용카드 결제방법  -->
	<!-- 일반적인 업체의 경우 ISP,안심결제만 사용하면 되며 다른 결제방법 추가시에는 사전에 협의이후 적용바랍니다 -->
	<input type=hidden  name=sndShowcard value="I,M"> <!-- I(ISP), M(안심결제), N(일반승인:구인증방식), A(해외카드), W(해외안심)-->
	
	<!-- 신용카드(해외카드) 통화코드: 해외카드결제시 달러결제를 사용할경우 변경 -->
	<input type=hidden	name=sndCurrencytype value="WON"> <!-- 원화(WON), 달러(USD) -->
	
	<!-- 할부개월수 선택범위 -->
	<!--상점에서 적용할 할부개월수를 세팅합니다. 여기서 세팅하신 값은 결제창에서 고객이 스크롤하여 선택하게 됩니다 -->
	<!--아래의 예의경우 고객은 0~12개월의 할부거래를 선택할수있게 됩니다. -->
	<input type=hidden	name=sndInstallmenttype value="ALL(0:2:3:4:5:6:7:8:9:10:11:12)">
	
	<!-- 가맹점부담 무이자할부설정 -->
	<!-- 카드사 무이자행사만 이용하실경우  또는 무이자 할부를 적용하지 않는 업체는  "NONE"로 세팅  -->
	<!-- 예 : 전체카드사 및 전체 할부에대해서 무이자 적용할 때는 value="ALL" / 무이자 미적용할 때는 value="NONE" -->
	<!-- 예 : 전체카드사 3,4,5,6개월 무이자 적용할 때는 value="ALL(3:4:5:6)" -->
	<!-- 예 : 삼성카드(카드사코드:04) 2,3개월 무이자 적용할 때는 value="04(3:4:5:6)"-->
	<!-- <input type=hidden	name=sndInteresttype value="10(02:03),05(06)"> -->
	<input type=hidden	name=sndInteresttype value="NONE">

	<!-- 2. 온라인입금(가상계좌) 관련설정 -->
	<input type=hidden	name=sndEscrow value="1"> 			<!-- 에스크로사용여부 (0:사용안함, 1:사용) -->
	
	<!-- 3. 월드패스카드 관련설정 -->
	<input type=hidden	name=sndWptype value="1">  			<!--선/후불카드구분 (1:선불카드, 2:후불카드, 3:모든카드) -->
	<input type=hidden	name=sndAdulttype value="1">  		<!--성인확인여부 (0:성인확인불필요, 1:성인확인필요) -->
	
	<!-- 4. 계좌이체 현금영수증발급여부 설정 -->
    <input type=hidden  name=sndCashReceipt value="0">          <!--계좌이체시 현금영수증 발급여부 (0: 발급안함, 1:발급) -->

	<!-- 5. 상품권, 게임문화상품권 관련 설정 -->
	<input type=hidden  name=sndMembId value="userid"> <!-- 가맹점사용자ID (문화,게임문화 상품권결제시 필수) -->
	
<!----------------------------------------------- <Part 3. 승인응답 결과데이터>  ----------------------------------------------->
<!-- 결과데이타: 승인이후 자동으로 채워집니다. (*변수명을 변경하지 마세요) -->

	<input type=hidden name=reWHCid 	value="">
	<input type=hidden name=reWHCtype 	value="">
	<input type=hidden name=reWHHash 	value="">
<!--------------------------------------------------------------------------------------------------------------------------->

<!--업체에서 추가하고자하는 임의의 파라미터를 입력하면 됩니다.-->
<!--이 파라메터들은 지정된결과 페이지(kspay_result.php)로 전송됩니다.-->
	<input type=hidden name=a        value="a1">
	<input type=hidden name=b        value="b1">
	<input type=hidden name=c        value="c1">
	<input type=hidden name=d        value="d1">
<!--------------------------------------------------------------------------------------------------------------------------->
</form>
		</td>
        <td width="30">&nbsp;</td>
      </tr>
    </table>

	<table border='0' cellpadding='0' cellspacing='0' width='400' align='center' style="margin-top:30px;_display:none" id="info1">
		<tr>
			<td>당사의 환불청책과 추가로 PG사의 환불수수료가 별도로 발생됨을 양해 부탁드립니다.<br><br> 
				가상계좌의 경우 <font size="" color="red">결제취소</font> 시 <font size="" color="red">1건당 330원의 수수료</font>가 발생합니다. 
			</td>
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
