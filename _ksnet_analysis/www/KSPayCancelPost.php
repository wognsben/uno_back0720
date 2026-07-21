<? 
include_once('_common.php');


/*------------------------------------------------------------------------------
 FILE NAME : KSPayCreditPostM.php
 AUTHOR : kspay@ksnet.co.kr
 DATE : 2004-05-03
                                                         http://www.kspay.co.kr
                                                         http://www.ksnet.co.kr
                                  Copyright 2003 KSNET, Co. All rights reserved

실행 sc는 /home/antoi//www/Linux_ipgClient dml ./sc 실행할것.
-------------------------------------------------------------------------------*/ ?>
<? include "./KSPayApprovalCancel.inc"; ?>
<?
// Default-------------------------------------------------------
	$EncType     = "2";     // 0: 암화안함, 1:openssl, 2: seed
	$Version     = "0210";  // 전문버전
	$VersionType = "00";    // 구분
	$Resend      = "0";     // 전송구분 : 0 : 처음,  2: 재전송

	$RequestDate=           // 요청일자 : yyyymmddhhmmss
		SetZero(strftime("%Y"),4).
		SetZero(strftime("%m"),2).
		SetZero(strftime("%d"),2).
		SetZero(strftime("%H"),2).
		SetZero(strftime("%M"),2).
		SetZero(strftime("%S"),2);
	$KeyInType     = "K";   // KeyInType 여부 : S : Swap, K: KeyInType
	$LineType      = "1";   // lineType 0 : offline, 1:internet, 2:Mobile
	$ApprovalCount = "1";   // 복합승인갯수
	$GoodType      = "0";   // 제품구분 0 : 실물, 1 : 디지털
	$HeadFiller    = "";   // 예비
//-------------------------------------------------------------------------------

// Header (입력값 (*) 필수항목)--------------------------------------------------
	$StoreId		= "2659100038";    // *상점아이디
	$OrderNumber	=""; 							// *주문번호
	$UserName		="";   							// *주문자명
	$IdNum		    ="";       						// 주민번호 or 사업자번호
	$Email			="";       						// *email
	$GoodName		="";    						// *제품명
	$PhoneNo		="";     						// *휴대폰번호
// Header end -------------------------------------------------------------------
	
// Data Default(수정항목이 아님)-------------------------------------------------
	$ApprovalType   = $_POST["authty"];	// 승인구분
	$TransactionNo  = $_POST["trno"];		// 거래번호
	$Canc_amt       = $_POST["canc_amt"];	//' 취소금액
	$Canc_seq       = $_POST["canc_seq"];	//' 취소일련번호
	$Canc_type      = $_POST["canc_type"];	//' 취소유형 0 :거래번호취소 1: 주문번호취소 3:부분취소

	if($jan_price>0) { //잔액 이 있는 경우는 잔액만 취소
		$Canc_amt       = $_POST["jan_price"];	//' 취소금액
		$Canc_seq       = $_POST["Canc_seq"];	//' 취소일련번호
		$Canc_type      = "3";
	}
// Data Default end -------------------------------------------------------------

// Server로 부터 응답이 없을시 자체응답
	$rApprovalType     = "1011";
	$rTransactionNo    = "";              // 거래번호
	$rStatus           = "X";             // 상태 O : 승인, X : 거절
	$rTradeDate        = "";              // 거래일자
	$rTradeTime        = "";              // 거래시간
	$rIssCode          = "00";            // 발급사코드
	$rAquCode          = "00";            // 매입사코드
	$rAuthNo           = "9999";          // 승인번호 or 거절시 오류코드
	$rMessage1         = "취소거절";      // 메시지1
	$rMessage2         = "C잠시후재시도"; // 메시지2
	$rCardNo           = "";              // 카드번호
	$rExpDate          = "";              // 유효기간
	$rInstallment      = "";              // 할부
	$rAmount           = "";              // 금액
	$rMerchantNo       = "";              // 가맹점번호
	$rAuthSendType     = "N";             // 전송구분
	$rApprovalSendType = "N";             // 전송구분(0 : 거절, 1 : 승인, 2: 원카드)
	$rPoint1           = "000000000000";  // Point1
	$rPoint2           = "000000000000";  // Point2
	$rPoint3           = "000000000000";  // Point3
	$rPoint4           = "000000000000";  // Point4
	$rVanTransactionNo = "";              
	$rFiller           = "";              // 예비
	$rAuthType         = "";              // ISP : ISP거래, MP1, MP2 : MPI거래, SPACE : 일반거래
	$rMPIPositionType  = "";              // K : KSNET, R : Remote, C : 제3기관, SPACE : 일반거래
	$rMPIReUseType     = "";              // Y : 재사용, N : 재사용아님
	$rEncData          = "";              // MPI, ISP 데이터
// --------------------------------------------------------------------------------

	KSPayApprovalCancel("114.108.160.120", 29991);

	HeadMessage(
		$EncType       ,                  // 0: 암화안함, 1:openssl, 2: seed       
		$Version       ,                  // 전문버전                              
		$VersionType   ,                  // 구분                                  
		$Resend        ,                  // 전송구분 : 0 : 처음,  2: 재전송    
		$RequestDate   ,                  // 재사용구분                                       
		$StoreId       ,                  // 상점아이디                                   
		$OrderNumber   ,                  // 주문번호                                     
		$UserName      ,                  // 주문자명                                     
		$IdNum         ,                  // 주민번호 or 사업자번호                       
		$Email         ,                  // email                                        
		$GoodType      ,                  // 제품구분 0 : 실물, 1 : 디지털                
		$GoodName      ,                  // 제품명                                       
		$KeyInType     ,                  // KeyInType 여부 : S : Swap, K: KeyInType      
		$LineType      ,                  // lineType 0 : offline, 1:internet, 2:Mobile   
		$PhoneNo       ,                  // 휴대폰번호                                   
		$ApprovalCount ,                  // 복합승인갯수                                 
		$HeadFiller    );                 // 예비                                         

// ------------------------------------------------------------------------------
	if($Canc_type == '3'){
		CancelDataMessage($ApprovalType, $Canc_type, $TransactionNo,	"",	"", SetZero($Canc_amt,9).SetZero($Canc_seq,2),	"", "");   		
	}
	else{
		CancelDataMessage($ApprovalType, "0", $TransactionNo,	"",	"", "",	"", "");   	                    
	}  	                         

	if (SendSocket("1")) {
		$rApprovalType		= $ApprovalType	    ;
		$rTransactionNo		= $TransactionNo	;  	// 거래번호
		$rStatus			= $Status		  	;	// 상태 O : 승인, X : 거절
		$rTradeDate			= $TradeDate		;  	// 거래일자
		$rTradeTime			= $TradeTime		;  	// 거래시간
		$rIssCode			= $IssCode		  	;	// 발급사코드
		$rAquCode			= $AquCode		  	;	// 매입사코드
		$rAuthNo			= $AuthNo		  	;	// 승인번호 or 거절시 오류코드
		$rMessage1			= $Message1		  	;	// 메시지1
		$rMessage2			= $Message2		  	;	// 메시지2
		$rCardNo			= $CardNo		  	;	// 카드번호
		$rExpDate			= $ExpDate		  	;	// 유효기간
		$rInstallment		= $Installment	  	;	// 할부
		$rAmount			= $Amount		  	;	// 금액
		$rMerchantNo		= $MerchantNo	  	;	// 가맹점번호
		$rAuthSendType		= $AuthSendType	  	;	// 전송구분= new String(this.read(2))
		$rApprovalSendType	= $ApprovalSendType	;	// 전송구분(0 : 거절, 1 : 승인, 2: 원카드)
		$rPoint1			= $Point1		  	;	// Point1
		$rPoint2			= $Point2		  	;	// Point2
		$rPoint3			= $Point3		  	;	// Point3
		$rPoint4			= $Point4		  	;	// Point4
		$rVanTransactionNo  = $VanTransactionNo ;   // Van거래번호
		$rFiller			= $Filler		  	;	// 예비
		$rAuthType			= $AuthType		  	;	// ISP : ISP거래, MP1, MP2 : MPI거래, SPACE : 일반거래
		$rMPIPositionType	= $MPIPositionType 	;	// K : KSNET, R : Remote, C : 제3기관, SPACE : 일반거래
		$rMPIReUseType		= $MPIReUseType		;	// Y : 재사용, N : 재사용아님
		$rEncData			= $EncData		  	;	// MPI, ISP 데이터
	}


$orderstatus	= trim($rStatus);

$trno		= trim($TransactionNo);
$d_canis	= date("Y-m-d H:i:s",time());

//$s_status	= trim($rApprovalType);
//$msg1		= trim($rMessage1);
//$msg2		= trim($rMessage2);

/*(if (!$trno)
{
	getLink('','','정상적인 접근이 아닙니다.','close');
}*/

if ($orderstatus == "O"){
	/* 패키지 결제 내역도 취소일 등록 */
	sql_query("UPDATE   `tour_reg_pkg_fee` SET   `CancelDate` = '".$d_canis."' WHERE `card_pay` = '$trno' ");

	sql_query("UPDATE   `kspay_result` SET   `CancelDate` = '".$d_canis."' WHERE `ApplNum` = '$trno' ");
	echo "O";

	



	//sql_query(" update tour_reg set ");
}
else echo $orderstatus;

?>