<?
If($member[mb_level]<2) alert("로그인 후 이용하실 수 있습니다.","/");

if ($member[mb_level] >= 5) {
  $is_b2bmem = 'Y';
  $b2b_rank = $member[mb_rank];
}

?>
<link href="https://use.fontawesome.com/releases/v5.0.7/css/all.css" rel="stylesheet">

<div id="" class="contentWrap pd20 mgb20">
	
<? If($ver=="old") {?>
<div class="subTitleTxt">구) 예약 관리</div>
<?
If(!$mn) $mn="menu1";?>
		<table class="tab " border=0 style="width:500px">
		<tr>
			<td class="active"><span class="tabNo" >01</span><a href="/index.html?inc=mypage&ver=old&mn=menu1"><span class="tabName">예약문의</span></a></td>
			<td><span class="tabNoOff">02</span><a href="/index.html?inc=mypage&ver=old&mn=menu2"><span class="tabName">입금확인</span></a></td>
			
		</tr>
		</table> 
		<table class="regFormTable" border=0 style="width:1000px">
		<tr>
			<th align="left" style="text-align:left; padding:10">* 구)예약관리는 2014년 7월 20일 이전에 구) 홈페이지에서 예약하신 분의 예약 상황입니다.<br>
		* 구)예약관리는 2014년 11월 30일까지 운영됩니다.</th>
		</tr>
		<tr>
			<td><iframe src="http://rararang85.cafe24.com/default/04/<?=$mn?>.php?topmenu=4" width="100%" height=1500 style="overflow:hidden" frameborder=0></iframe></td>
		</tr>
		</table>
		

		
<?}
Else {?><div class="subTitleTxt">예약 관리</div><br>

		<table class="regFormTable" border=0 style="width:90%">
			<colgroup>
			  <col width="10px" />
			  <col width="*" />
			</colgroup>
			<tr>
				<th></th>
				<th>예약일시</th>
				<th>상품</th>
				<th>투어일</th>
				<th>투어인원</th>
				<th>예약금</th>
				<th width="60px">잔금</th>
				<!-- <th>현지지불시</th> -->
				<th>진행 상태</th>
				<th>바우처</th>
			</tr>
			<?
			$result= sql_query("Select * from `tour_reg` where mb_id='$member[mb_id]' order by tourDay desc");
			$isCancelBtn="N";
			
			for ($i=0; $row=sql_fetch_array($result); $i++) {
					$pData=sql_fetch("select wr_subject,ca_name from g4_write_product where wr_id='$row[pid]' ");
					$membCnt_a=explode("|",$row[membCnt]);
					$fee_a=explode("|",$row[fee_id]);
					$mCnt=0;
					$feeTxt="";
					For($mci=0; $mci<count($membCnt_a);$mci++) {
						If($pData[ca_name]=="단체" || $pData[ca_name]=="단체차량") {
							If($membCnt_a[$mci]>0) {
								$mCnt+=$membCnt_a[$mci];
								$feeData=sql_fetch("select * from tour_fee where id='$fee_a[$mci]' order by id" );
								$feeTxt.=$feeData[fee_subject]." : ".$membCnt_a[$mci]."명<br>  ";
							}
						}
						Else If($pData[ca_name]=="차량") {
							If($membCnt_a[$mci]=="Y") {
								$mCnt+=$membCnt_a[$mci];
								$feeData=sql_fetch("select * from tour_fee where id='$fee_a[$mci]' order by id" );
								$feeTxt.=$feeData[fee_subject]." : 선택함<br>  ";
							}
						}
					}

				
				$isEventData=sql_fetch("select * from tour_reg where parent_id='$row[id]' ");
				If($isEventData[isEvent]) {

					$pDataEventProd=sql_fetch("select wr_subject,ca_name from g4_write_product where wr_id='$isEventData[event_pid]' ");
					$isEventText="<br><input type=\"button\" value=\"1+1 이벤트\" class=\"btn_status btn_status_event\">{$pDataEventProd[wr_subject]} ( 투어일: {$isEventData[tourDay]})"; 
					
					
				}
				Else $isEventText="";
			
			
			
			If($row[isEvent]!="Y") {?>
			
			<tr>
				<td><? If($row[status]<"9") {?><input type="checkbox" name="sel" value="<?=$row[id]?>"><?}?></td>
				<td><?=Date("y.m.d",$row[regDate])?></td>
				<td><? If($is_b2bmem == 'Y' && $row[status] == "1") {?><a href="/index.html?inc=reserve.modify&pid=<?=$row[pid]?>&regid=<?=$row[id]?>"><?}?>
				<?
				// ★이벤트 상품일경우 무료 투어 제목 노출
				$event_no = ($row['id']+1);
				$row_1=sql_fetch("select * from tour_reg where id = '".$event_no."'");
				$row_e=sql_fetch("select * from g4_write_product where wr_id='".$row_1['event_pid']."'");
				?>
				<?if($row_e['_wr_subject'] != ""){?>
					<strong>[<?=$pData[ca_name]?>]</strong> <?=$pData[wr_subject]?><br />
					<input type="button" value="1+1 이벤트" class="btn_status btn_status_event"><?echo $row_e['wr_subject'];?> (투어일 : <?echo $row_1['tourDay'];?>)
				<?}else{?>
					<strong>[<?=$pData[ca_name]?>]</strong> <?=$pData[wr_subject]?><?=$isEventText?>
				<?}?>
				</a>
				</td>
				<td><?=Date("y.m.d",strtotime($row[tourDay]))?></td>
				<td align="right"><? If($pData[ca_name]=="단체") {?><strong>전체 : <?=$mCnt?>명</strong><br><?}?><?=$feeTxt?></td>
				<td align="right"><?=$row[total_fee1]?>원 <?if($row[total_fee4]) echo "+ ".$row[total_fee4]."원";?> <br/>
				<? if($row[is_ticket] == '1') {?>
					<a  href="javascript:popup_detail(<?=$row[id]?>);" class="btn_status btn_status_02" style="text-decoration:none; color:#fff"><i class="fas fa-question-circle"></i> 입장권 </a>
				<?}?>
				</td>
				<td align="right"><?=$row[total_fee2]?>유로</td>
				<!-- <td align="right"><?=$row[total_fee3]?>유로</td> -->
				<td align="center">
				
				<?
			
				If($row[status]=="1" || $row[status]=="2" || $row[status]=="3") {$isCancelBtn="Y";}

				If($row[status]=="1") {?><input type="button" value="예약대기" onclick="" class="btn_status btn_status_01"><?}
				Else If($row[status]=="2") {?><input type="button" value="예약확인" onclick="" class="btn_status btn_status_02"><?}
				Else If($row[status]=="3") {?><input type="button" value="예약확정" onclick="" class="btn_status btn_status_03"><?}
				Else If($row[status]=="9") {?><input type="button" value="예약취소" onclick="" class="btn_status btn_status_09"><?}
				Else If($row[status]=="91") {?><input type="button" value="취소요청" onclick="" class="btn_status btn_status_08"><?}?>
				
				<? if($row[card_pay]) {?><br><input type="button" value="결제 완료" class="btn_status btn_status_02"><?} 
				else if($row[status]=="2" && str_replace(",","",$row[total_fee1])>0) {?><br><input type="button" value="신용 카드 결제" onclick="req_pay('<?=$row[id]?>');" class="btn_status btn_status_02" style="margin-top:3px"><?}?>
				
				</td>
				
				<td align="center"><?  
				If(($row[status]=="3")&& $row[status]!="9") {
					?><input type="button" value="보기" onclick="popup_page('/voucher.php?rid=<?=$row[id]?>','voucherWin');" class="btn_status btn_status_05">
					<?if($row_e['wr_subject'] != ""){?>
					<br><input type="button" value="1+1 이벤트" onclick="popup_page('/voucher.php?rid=<?=$row_1[id]?>','voucherWin');" class="btn_status btn_status_event mgt5"><?}
                            
				}?>
				
				
				</td>
			</tr>

			<? if($row[card_pay]){?>
			<tr><td colspan=8 class="space-right"><?
					if($row[adminCancelDate]) $date_str="<span class=\"btn_status btn_status_05\">$row[adminCancelDate] 취소</span>";
					else $date_str="<span class=\"btn_status btn_status_06\">$row[card_pay] 정상 승인</span>";
					?>
				승인 번호 : <?=$row[card_pay]?>, <?=$date_str?> 
				</td>
				<td></td>
				</tr><?
			}
			}
			}?>
			</table><br><br>
			<table class="tourRule1" border=0 style="width:90%" align="center">
			<tr><td>
			<? If($isCancelBtn=="Y") {?>
			 <input type="button" value="예약취소 요청" onclick="req_cancel()" class="btn_status btn_status_01"> 
			 <?}?>
			<!-- <? if($member[mb_id]=="admin") {?><input type="button" value="신용 카드 결제하기" onclick="req_pay();" class="btn_status btn_status_02"><?}?> -->
			</td></tr>
			</table>

			<p class="mypageMemo" style="line-height:150%">※  예약후 진행상태가 <span class="btn_status_02">예약확인</span>이 된후 12시간 이내에 입금 하셔야 하며 미 입금시 예약은 취소 처리 됩니다.<br>
			※ 예약 확정시 바우처가 이메일로 발송되며, 별도로 출력하실 수 있습니다.  <br>
			※ 예약금 결제자 순으로 예약확정 해 드리고 있습니다.<br>
				※ 예약금 없이 현장에서 투어비용 전액 지불하길 원하신다면 별도의 연락을 주셔야 합니다.
			<br>
			<strong><?
				$tourOptionData=sql_fetch("select * from g4_write_tourOption where wr_subject = '입금계좌 ' order by wr_num");
				echo nl2br(stripslashes($tourOptionData[wr_content]))?></strong> </p>
	
</div></div>
<div id="" class="pd20"></div>
</div>
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

	$.get("/include_files/set_req_cancel.php?sel="+sel,function(data,status){				
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



</script>
<form method="post" action="" id="payFrm" name="payFrm">
	<input type="hidden" name="sel" id="sel_val">
</form>
<form method="post" action="" id="tourFrm" name="tourFrm">
	<input type="hidden" name="tour_id" id="tour_id">
</form>
<?}?>
