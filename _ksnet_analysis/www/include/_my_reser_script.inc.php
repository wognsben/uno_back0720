<script type="text/javascript">

function popup_page(val,target_name){

var features = 'width=740,height=700,resizable=yes,scrollbars=yes,toolbar=yes';

window.open(val,'target_name',features);

}

function popup_detail(id){
	window.open('','tourdetail','width=650, height=550, menubar=no, status=no,scrollbars=auto, toolbar=no');
	document.tourFrm.target='tourdetail';
	document.tourFrm.action='/tourdetail.php'; 
	$('#tour_id').val(id);

	document.tourFrm.submit();
}

function card_event(){
	/*$('#cardInfoPop').load(
		"/contents/ajax/load_by_function.php", 
		{gubun : 'cardInfoPop'} , 
		function() { 
			
		}
	)*/
	 $('#cardInfoPop').fancybox().trigger('click'); 
}

function req_cancel(){
	var chk=$('input:checkbox[name="sel"]:checked');
	var sel="";
	$(":checkbox").each(function (i) {
         if( this.checked ){ sel += $(this).val() + ";"; }
   });
   if(!sel) {
	alert("취소요청을 하실 투어를 선택해 주세요");
	return false;
	}
	/* 테스트 하고 코드 수정 확인할것 */
	$.get("/contents/ajax/load_by_function.php?gubun=req_cancel&sel="+sel,function(data,status){				
		alert("취소 요청을 관리자 에게 전달 해 드렸습니다.");
		location.reload();
	});
}

function req_pay(sel){
	/*var chk=$('input:checkbox[name="sel"]:checked');
	var sel="";
	$(":checkbox").each(function (i) {
         if( this.checked ){ sel += $(this).val() + ";"; }
   });
   if(!sel) {
	alert("결제하실 투어를 선택해 주세요");
	return false;
	}*/

<? //18-03-15 open  테스트 아이디는 kspay로 //if($member[mb_id] == 'unotravel@unotravel.co.kr'){?>
	window.open('','pay_win','width=578, height=630, menubar=no, status=no,scrollbars=auto, toolbar=no'); 
	document.payFrm.target='pay_win';
	document.payFrm.action='/kspay.php'; 


	/* 이니시스 결제창 18-03-15 변경됨
	window.open('','pay_win','width=650, height=550, menubar=no, status=no,scrollbars=auto, toolbar=no'); 
	document.payFrm.target='pay_win';
	document.payFrm.action='/pay.php'; */

	$('#sel_val').val(sel);

	document.payFrm.submit();
	
}
function req_pay_m(sel){
	/*var chk=$('input:checkbox[name="sel"]:checked');
	var sel="";
	$(":checkbox").each(function (i) {
         if( this.checked ){ sel += $(this).val() + ";"; }
   });
   if(!sel) {
	alert("결제하실 투어를 선택해 주세요");
	return false;
	}*/


	//window.open('','pay_win','width=650, height=550, menubar=no, status=no,scrollbars=auto, toolbar=no'); 
	//document.payFrm.target='pay_win';
	
	document.payFrm.action='/m/kspay/kspay_wh_order.php'; 
	
	/* 이니시스 결제창 18-03-15 변경됨
	document.payFrm.action='/m2/pay/pay.php'; 
	*/

	$('#sel_val').val(sel);

	document.payFrm.submit();
	
}

function  myMemo(rid) {
	$('#myMemo').load(
		"/contents/ajax/load_by_function.php", 
		{gubun : 'myMemo' , rid  : rid} , 
		function() { 
			
		}
	)
	 $('#myMemoPop').fancybox().trigger('click'); 
}

</script>

<? if($isMobile) {?>
<form method="post" _action="/m/pay/pay.php" id="payFrm" name="payFrm" target="self">
	<input type="hidden" name="sel" id="sel_val">
</form>
<form method="post" action="" id="tourFrm" name="tourFrm">
	<input type="hidden" name="tour_id" id="tour_id">
</form>
<?} else {?>
<form method="post" action="" id="payFrm" name="payFrm">
	<input type="hidden" name="sel" id="sel_val">
</form>
<form method="post" action="" id="tourFrm" name="tourFrm">
	<input type="hidden" name="tour_id" id="tour_id">
</form>
<?}?>