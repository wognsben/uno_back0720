<?php 
include_once("./_common.php");

if($sel) {
	
	$sel_a=explode(";",$sel); //1차구분해서
	$rid_a=explode("_", $sel_a[0]); //패키지의경우 rid 뒤의 추가 코드 fee1, fee2, fee3, fee_air 확인을 위해.
	//echo "select * from `tour_reg` where id='$rid_a[0]'";
	$resData=sql_fetch("select * from `tour_reg` where id='$rid_a[0]' ");//$resData=sql_fetch("select * from `tour_reg` where id='$sel_a[0]' ");
	$pData=sql_fetch("select wr_subject,ca_name from g5_write_product where wr_id='$resData[pid]' ");
	if($resData['nation']=="패키지") {
		$fee=str_replace(",","",$resData['total_'.$rid_a[1]]);
	} else if(stripos($resData['nation'], '세미패키지') !== false) {
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
	else $oid=$member[mb_no]."_".$rid_a[0];  //$oid=$member[mb_no]."_".$sel_a[0];
	//$subject= iconv( "UTF-8", "EUC-KR", $subject);


}
$mb_name=$member[mb_name];


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
<title>호스트방식(APP) 결제샘플</title>
<style type="text/css">
	BODY{font-size:9pt; line-height:100%}
	TD{font-size:9pt; line-height:100%}
	A {color:blue;line-height:100%; background-color:#E0EFFE}
	INPUT{font-size:9pt;}
	SELECT{font-size:9pt;}
</style>
</head>
<script language="javascript">

	function _pay(_frm) 
	{
		// sndReply는 kspay_wh_rcv.php (결제승인 후 결과값들을 본창의 KSPayWeb Form에 넘겨주는 페이지)의 절대경로를 넣어줍니다. 
 		_frm.sndReply.value           = getLocalUrl("kspay_wh_result.php") ;

		var agent = navigator.userAgent;
		var midx		= agent.indexOf("MSIE");
		var out_size	= (midx != -1 && agent.charAt(midx+5) < '7');
		_frm.action ='https://kspay.ksnet.to/store/mb2/KSPayPWeb_utf8.jsp';
		_frm.submit();
    }

	function getLocalUrl(mypage) 
	{ 
		var myloc = location.href; 
		return myloc.substring(0, myloc.lastIndexOf('/')) + '/' + mypage;
	} 
	
</script>
<body>
<!-----------------------------------------<Part 1. authFrmFrame Form: 결과페이지주소 설정 > ---------------------------------------->
<!--결제 완료후 결과값을 받아처리할 결과페이지의 주소-->
	<form name=authFrmFrame method=post>
<!-- 결과값 수신 파라메터, value값을 채우지마십시오. KSPayRcv.asp가 실행되면서 채워주는 값입니다-->
<!--------------------------------------------------------------------------------------------------------------------------->
<table border=0 width=100%>
	<tr>
	<td>
	<hr noshade size=1>
	<b>KSPay</b>
	<hr noshade size=1>
	</td>
	</tr>
</table>
<br>
<table border=0 width=100%>
<tr>
<td align=center>
<table width=100% cellspacing=0 cellpadding=0 border=0 bgcolor=#4F9AFF>
<tr>
<td>
<table width=100% cellspacing=1 cellpadding=2 border=0>
<tr bgcolor=#4F9AFF height=25>
<td align=center><font color="#FFFFFF">
정보를 기입하신 후 지불버튼을 눌러주십시오
</font></td>
</tr>
<tr bgcolor=#FFFFFF>
<td valign=top>
	<table width=100% cellspacing=0 cellpadding=2 border=0>
	<tr>
	<td align=center>
	<br>
		<table>
		<tr>
		<!----------------------------------------------- < Part 2. 고객에게 보여지지 않는 항목 > ------------------------------------>
		<!--이부분은 결제를 위해 상점에서 기본정보를 세팅해야 하는 부분입니다.														-->
		<!--단 고객에게는 보여지면 안되는 항목이니 테스트 후 필히 hidden으로 변경해주시길 바랍니다.									-->
			<td colspan=2 style="display:none;">고객에게 보여지지 않아야 하는 설정값 항목</td>
		</tr>
		<tr style="display:none;">
			<td>결제수단 : </td>
			<td>
				<select name=sndPaymethod>
					<option value='1000000000'>신용카드</option>
					<option value='0010000000'>계좌이체</option>
					
				</select>
			</td>
		</tr>
		<tr style="display:none;">
			<td>상점아이디 : </td>
			<td><input type=hidden name=sndStoreid size=10 maxlength=10 value="2659100038"></td></tr>
		<tr style="display:none;">
		<!-- 화폐단위 원화로 설정 : 410 또는 WON -->
			<td>화폐단위 : </td>
			<td><input type=hidden name=sndCurrencytype size=30 maxlength=3 value="WON"></td>
		</tr>
		<tr style="display:none;">
		<!--상품명은 30Byte(한글 15자) 입니다. 특수문자 ' " - ` 는 사용하실수 없습니다. 따옴표,쌍따옴표,빼기,백쿼테이션 -->
			<td>주문번호 : </td>
			<td><input type=text name=sndOrdernumber size=30 maxlength=30 value="<?=$oid?>"></td>
		</tr>
		<tr style="display:none;">
		<!--주민등록번호는 필수값이 아닙니다.-->
			<td>주민번호 : </td>
			<td><input type=text name=sndAllregid size=30 maxlength=13 value="">
		</td>
		</tr>
		<tr style="display:none;">
			<td colspan=2><hr></td></tr>
		<tr style="display:none;">
			<td colspan=2>신용카드 기본항목</td>
		</tr>
		<tr style="display:none;">
		<!--상점에서 적용할 할부개월수를 세팅합니다. 여기서 세팅하신 값은 KSPAY결재팝업창에서 고객이 스크롤선택하게 됩니다 -->
		<!--아래의 예의경우 고객은 0~12개월의 할부거래를 선택할수있게 됩니다. -->
			<td>할부개월수  : </td>
			<td><input type=text name=sndInstallmenttype size=30 maxlength=30 value="0:2:3:4:5:6:7:8:9:10:11:12"></td>
		</tr>
		<tr style="display:none;">
		<!--무이자 구분값은 중요합니다. 무이자 선택하게 되면 상점쪽에서 이자를 내셔야합니다.-->
		<!--무이자 할부를 적용하지 않는 업체는 value='NONE" 로 넘겨주셔야 합니다. -->
		<!--예 : 모두 무이자 적용할 때는 value="ALL" / 무이자 미적용할 때는 value="NONE" -->
		<!--예 : 3,4,5,6개월 무이자 적용할 때는 value="3:4:5:6" -->
			<td>무이자구분  : </td>
			<td><input type=text name=sndInteresttype size=30 maxlength=30 value="NONE"></td>
		</tr>
		<tr style="display:none;">
			<td>신용카드표시구분  : </td>
			<td><input type=text name=sndShowcard size=30 maxlength=30 value="I,M"></td>
		</tr>
		<tr>
			<td colspan=2><hr></td></tr>
		<tr>
		<!----------------------------------------------- <Part 3. 고객에게 보여주는 항목 > ----------------------------------------------->
			<td colspan=2></td>
		</tr>
		<tr>
		<!--상품명은 30Byte(한글 15자)입니다. 특수문자 ' " - ` 는 사용하실수 없습니다. 따옴표,쌍따옴표,빼기,백쿼테이션 -->
			<td>상품명 : </td>
			<td><input type=text name=sndGoodname size=30 maxlength=30 value="<?=$subject?>" readonly></td>
		</tr>
		<tr>
			<td>가격 : </td>
			<td><input type=text name=sndAmount size=30 maxlength=9 value="<?=$fee?>" readonly></td>
		</tr>
		<tr>
			<td>성명 : </td>
			<td><input type=text name=sndOrdername size=30 maxlength=20 value="<?=$mb_name?>" readonly></td>
		</tr>
		<!--KSPAY에서 결제정보를 메일로 보내줍니다.(신용카드거래에만 해당)-->
		<tr>
			<td>전자우편 : </td>
			<td>
			<input type=text name=sndEmail size=30 maxlength=50 value="<?=$member[mb_email]?>">
			</td>
		</tr>	
		<!--카드사에 SMS 서비스를 등록하신 고객에 한해서 SMS 문자메세지를 전송해 드립니다.-->
		<!--전화번호 value 값에 숫자만 넣게 해주시길 바랍니다. : '-' 가 들어가면 안됩니다.-->
		<tr>
			<td>이동전화 : </td>
			<td>
			<input type=text name=sndMobile size=30 maxlength=12 value="<?=$member[mb_hp]?>">
			</td>
		</tr>

		<tr>
			<td colspan=2 align=center>
			<br>
			<input type="button" value=" 지 불 " onClick="javascript:_pay(document.authFrmFrame);">
			<br><br>
			</td>
		</tr>
		<tr>
			<td colspan="2">당사의 환불청책과 추가로 PG사의 환불수수료가<br> 별도로 발생됨을 양해 부탁드립니다.<br><br> 
				가상계좌의 경우 <font size="" color="red">결제취소</font> 시 <font size="" color="red">1건당 330원의 수수료</font>가 발생합니다. 
			</td>
		</tr>
		</table>

	</td>
	</tr>
	</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
<br>

<table border=0 width=100%>
	
	<tr>
	<td><hr noshade size=1></td>
	</tr>
	<input type=hidden name=sndReply           		value="">
	<input type=hidden name=sndEscrow          	value="0">	  				<!--에스크로적용여부-- 0: 적용안함, 1: 적용함 -->
	<input type=hidden name=sndVirExpDt     		value="">      			<!-- 마감일시 -->
	<input type=hidden name=sndVirExpTm     		value="">      			<!-- 마감시간 -->
	<input type=hidden name=sndStoreName       	value="케이에스페이(주)">   <!--회사명을 한글로 넣어주세요(최대20byte)-->
	<input type=hidden name=sndStoreNameEng    	value="kspay_test_store">   <!--회사명을 영어로 넣어주세요(최대20byte)-->
	<input type=hidden name=sndStoreDomain     	value="http://www.kspay_test.co.kr">   <!-- 회사 도메인을 http://를 포함해서 넣어주세요-->
	<input type=hidden name=sndGoodType		   		value="1">								<!--실물(1) / 디지털(2) -->
	<input type=hidden name=sndUseBonusPoint		value="">   							<!-- 포인트거래시 60 -->                                                                                                                                                           
	<input type=hidden name=sndRtApp		   			value="">									<!-- 하이브리드APP 형태로 개발시 사용하는 변수 -->
</table>

</form>
</body>
</html>
