<?
if($pid) $row=sql_fetch("select * from g5_write_product  where wr_id='$pid'");
?>
<link id="bsdp-css" href="/js/bootstrap-datepicker-1.5.0-dist/css/bootstrap-datepicker3.css" rel="stylesheet">
<script src="/js/bootstrap-datepicker-1.5.0-dist/js/bootstrap-datepicker.min.js"></script>
<script src="/js/bootstrap-datepicker-1.5.0-dist/locales/bootstrap-datepicker.ko.min.js" charset="UTF-8"></script>
<p > <h3>투어 요금 설정 </h3></p>
<div class="panel panel-default" style="margin:10px 15px 0px 0px">
  <div class="panel-body">
	<? if($pid) {?>
	<style>	
		#calendar {margin: 0 auto; _min-height:1000px}
		.fc-day-header {padding:5px !important;}
		.event_team {background-color:#367fa9 !important; font-size:12px !important;color:#fff !important ; padding:5px  !important; cursor:pointer !important; border:0 !important;}
		.event_adult {background-color:#ff0000 !important; font-size:12px !important;color:#fff !important ; padding:5px  !important; cursor:pointer !important; border:0 !important;}
		.event_youth {background-color:#339900 !important; font-size:12px !important;color:#fff !important ; padding:5px  !important; cursor:pointer !important; border:0 !important;}
		.event_child {background-color:#ffcc00 !important; font-size:12px !important;color:#fff !important ; padding:5px  !important; cursor:pointer !important; border:0 !important;}
		.event_ {background-color:#d73925 !important; font-size:12px !important;color:#fff !important ; padding:5px  !important; cursor:pointer !important; border:0 !important;}
		.is_end {background-color:#d73925 !important; border:0 !important;border-color:#d73925 !important; font-size:12px !important; color:#fff !important; padding:5px  !important; cursor:pointer !important}
	</style>
	<link href='/js/fullcalendar-2.6.0/fullcalendar.css' rel='stylesheet' />
	<link href='/js/fullcalendar-2.6.0/fullcalendar.print.css' rel='stylesheet' media='print' />
	<script src='/js/fullcalendar-2.6.0/lib/moment.min.js'></script>

	<script src='/js/fullcalendar-2.6.0/fullcalendar.min.js'></script>
	<script src='/js/fullcalendar-2.6.0/lang-all.js'></script>

	
	<?}?>
	<div class="row">
		<div class="col-md-6">
		<select name="pid" id="pid"  required itemname="분류" class="form-control" style="_width:200px" onchange="location.href='/Admin/index.html?inc=tourFee&pid='+this.value">
			<option value="">투어를 선택하세요
			<?
			$p_result=sql_query("select * from g5_write_product where wr_is_comment=0 and wr_7 >11  order by ca_name, wr_subject");
			while($t_row=sql_fetch_array($p_result)) {
				if(!$wr_id) $wr_id=$t_row[wr_id];
				?>
				<option value="<?=$t_row[wr_id]?>" <?=($t_row[wr_id]==$pid)?"selected":""?>>[<?=$t_row[ca_name]?>] <?=$t_row[wr_subject]?>
				<?
			}
			?>
			</select>
		</div>
	</div><br><br>
	<?if($pid) {?>
	<div class="row">
		<form id="regForm" method="post">
			<div class="col-md-5">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">기본 요금</h3>
				</div>
				<div class="panel-body">
					<table class="table">
						<colgroup>
							<col width="15%">
							<col width="28%">
							<col width="28%">
							<col width="28%">
						</colgroup>
						<thead>
						<tr class="active">
							<th rowspan=2 class="space-center w_120">예약구분</th>
							<th colspan=2 class="space-center">사전 예약시</th>
							<th rowspan=2 class="space-center">총 금액</th>
						</tr>
						<tr class="active">
							<th class="space-center">예약금</th>
							<th class="space-center">현장 지불</th>
						</tr>
						</thead>
						<tbody><?
							if($row[ca_name]=="단독") $feeResult=sql_query("select * from tour_fee where pid='$pid' and fee_gubun='team' and isDc='N'   order by pid,   id" );
							elseif($row[ca_name]=="유럽여행") {
								$air_a=explode("|",$row[wr_5]);

								for($a_i=1; $a_i<count($air_a); $a_i++) {
									if(!$air_id) $air_id=$air_a[$a_i];
								}
								
								$feeResult=sql_query("select * from tour_fee where pid='$pid' and (fee_gubun='adult' or fee_gubun='youth'  or fee_gubun='child'  ) and air_id='$air_id' and isDc='N'   order by pid,   id" );
							}
							else $feeResult=sql_query("select * from tour_fee where pid='$pid' and isDc='N'  order by pid,   id" );

							for($fi=0; $feeRow=sql_fetch_array($feeResult); $fi++) {

								if($feeRow[fee_gubun]=="team") $fee_subject=$feeRow[fee_subject];
								else if($feeRow[fee_gubun]=="adult") $fee_subject="성인";
								else if($feeRow[fee_gubun]=="youth") $fee_subject="8-18세";
								else if($feeRow[fee_gubun]=="child") $fee_subject="8세미만";
								else  $fee_subject=$feeRow[fee_subject];

								if($feeRow[feeUnit_str]) $feeUnit_str=$feeRow[feeUnit_str]; else $feeUnit_str="유로";
								if($feeRow[feeUnit_str3]) $feeUnit_str3=$feeRow[feeUnit_str3]; else $feeUnit_str3="유로";
						?>
						<tr>	<input type="hidden" name="parent[]" value="<?=$feeRow[id]?>">			
							<td ><?=$fee_subject?></td>
							<td >
								<div class="input-group " >
									<input type="text" name="fee1[]" value="<?=number_format($feeRow[fee1])?>" class="form-control input-sm space-right" style="padding-left:2px; padding-right:2px">
									<span class="input-group-addon" >원</span>
								</div>
							</td>
							<td >
								<div class="input-group " >
									<input type="text" name="fee2[]" value="<?=number_format($feeRow[fee2])?>" class="form-control input-sm space-right" style="padding-left:2px; padding-right:2px">
									<span class="input-group-addon" ><?=$feeUnit_str?></span>
								</div>
							</td>
							<td >
								<div class="input-group " >
									<input type="text" name="fee3[]" value="<?=number_format($feeRow[fee3])?>" class="form-control input-sm space-right" style="padding-left:2px; padding-right:2px">
									<span class="input-group-addon" ><?=$feeUnit_str3?></span>
								</div>
							</td>
						</tr>
						<?
							}
						?>
						</tbody>
					</table>
				 </div>
			</div>
			

			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">투어 요금 설정</h3>
				</div>
				<div class="panel-body">
					<div class="input-daterange input-group" id="datepicker">
						<input type="text" class="input-sm form-control" name="start" id="term_From"/>
						<span class="input-group-addon">to</span>
						<input type="text" class="input-sm form-control" name="end" id="term_To"/>
					</div><br>
					<?
					$week_txt_a=array("일","월","화","수","목","금","토");
					
					for ($kk=0; $kk<7;  $kk++) {
					//$cData=sql_fetch("select * from tour_closed where pid='$wr_id' and gubun like 'week_".$kk."' order by gubun");
					?>
					<label class="checkbox-inline">
						<input type="checkbox" name="week_array[<?=$kk?>]" value="<?=$kk?>"  checked> <?=$week_txt_a[$kk]?>
					</label>
					<?
					//if($kk==3) echo "<br>";
					}?>
					<br><br>
					<input type="radio" name="fee_gubun_type" value="할인가"> 할인가 <input type="radio" name="fee_gubun_type" value="특가">특가 <input type="radio" name="fee_gubun_type" value="정상가">정상가
					<br><br><br>
					<input type="button" value="투어 할인요금 추가" onclick="saveTourFee();" class="btn btn-xs btn-primary center-block">
				 </div>
				 <br><br>
				<p class="text-info">- 상단에 표시되는 요금은 기본 요금이며, 수정후 저장하면 수정 저장한 요금이 추가됩니다.<br>
				- 적용 기간및 요일을 선택후 추가 버튼을 클릭합니다.<br>
				- 이미 등록된 요금일 해당 날자에 있을경우 요금이 업데이트 됩니다.<br>
				- 우측의 달력에서 날자를 클릭하면 요금이 reset 됩니다.</p>
			</div>

			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">색상 설명</h3>
				</div>
				<div class="panel-body">
					<span class="event_team"> 단체 </span> &nbsp;
					<span class="event_adult"> 성인 </span> &nbsp;
					<span class="event_youth"> 8-18세 </span> &nbsp;
					<span class="event_child"> 8세미만 </span> &nbsp;
					<span class="event_"> 기타 </span><br><br>
					<!-- 1년 전에 등록한 요금은 표시 하지 않는다. -->
				 </div>
				
			</div>

			

		</div>
		</form>
	  <div class="col-md-7"><div id='calendar'></div></div>
	</div>
	<?
	$today=date("Y-m-d",time());
	if($air_id) $air_que=" and air_id='$air_id' "; else $air_que="";
	$dc_result=sql_query("select * from tour_fee where isDC='Y' and pid='$pid' $air_que   GROUP BY fee_group ORDER BY feeDate desc" );

														//echo "select * from tour_fee where isDC='Y' and pid='$pid' $air_que and feeDate>'$today'";
	if(mysql_num_rows($dc_result)) {?>
	<div class="row">
			<div class="col-md-7">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">이벤트 특가</h3>
				</div>
				<div class="panel-body">
					<table class="table01_s"  >
						<colgroup>
							<col width="10%"><col width="20%"><col width="17%"><col width="18%"><col width="17%"><col width="25%">
						</colgroup>
						<thead>												
							<tr >
								<th class="space-center"></th>
								<th class="space-center">투어기간</th>
								<th class="space-center">구분</th>
								<th class="space-center">예약금</th>
								<th class="space-center">잔금</th>
								<th class="space-center">합계</th>
								<th class="space-center"></th>
							</tr>
						</thead>
						<tbody><?
						for($fi=0; $feeDCGroupRow=sql_fetch_array($dc_result); $fi++) {
																	
							$feeRow_s=sql_query("select * from tour_fee where fee_group='$feeDCGroupRow[fee_group]' group by fee_gubun order by feeDate  ");
							for($fi=0; $feeDCRow=sql_fetch_array($feeRow_s); $fi++) {

								$feeRow_e=sql_fetch("select feeDate as date from tour_fee where fee_group='$feeDCGroupRow[fee_group]' and fee_gubun='$feeDCRow[fee_gubun]'  order by feeDate desc" );

								

								if($feeDCRow[fee_gubun]=="team") $fee_gubun_code=$feeDCRow[fee_subject];
								else $fee_gubun_code=$fee_gubun_a["$feeDCRow[fee_gubun]"];
								if($feeDCRow[feeUnit_str]=="유로") $feeUnit_str="€"; else $feeUnit_str=$feeDCRow[feeUnit_str]; 
								if($feeDCRow[feeUnit_str3]=="유로") $feeUnit_str3="€"; else $feeUnit_str3=$feeDCRow[feeUnit_str3]; 

								$date_s=date("y-m-d",strtotime($feeDCRow[feeDate]));
								$date_e=date("y-m-d",strtotime($feeRow_e['date']));
								$fee1 = $feeDCRow[fee1]/10000;
								$fee3 = $feeDCRow[fee3]/10000;

								//if ($member[mb_level]>8) echo $feeRow_e['date'];//"select feeDate as date from tour_fee where fee_group='$feeDCGroupRow[fee_group]' and fee_gubun='$feeDCRow[fee_gubun]'  order by feeDate desc";

								if($feeDCRow[fee_gubun_type]=="특가") $sp_icon='<h5><span class="label_bs label-danger f-s-11">특 가</span></h5>';
								else if($feeDCRow[fee_gubun_type]=="할인가") $sp_icon='<h5><span class="label_bs label-warning">할인가</span></h5>';
								else if($feeDCRow[fee_gubun_type]=="정상가") $sp_icon='<h5><span class="label_bs label-default">정상가</span></h5>';
							?>
							<tr class="" >
								<td class="space-center" ><?=$sp_icon?></td>	
								<td class="space-center" ><?=$date_s?>~<?=$date_e?></td>	
								<td class="space-center" ><?=$fee_gubun_code?></td>				
								<td class="space-center"><?=$fee1?>만원</td>
								<td class="space-center"><?=$feeDCRow[fee2].$feeUnit_str?></td>
								<td class="space-center"><?=$fee3."만".$feeUnit_str3?></td>
								<td class="space-center"><input type="button" value="삭제" onclick="delEventFee('<?=$feeDCRow[fee_gubun]?>','<?=$feeDCRow[fee_group]?>');" class="btn btn-xs btn-danger center-block"></td>
							</tr>
							<?
								}
							}
						?>
						</tbody>
						</table>
						
				 </div>
				
			</div>
			
		</div>
		<div class="col-md-4">
				- 지난 이벤트 특가도 보여집니다.<br>
				- 이벤트 특가는 요금 적용일 기준으로 역순으로 보여집니다.<br>
				- 사용자 페이지에는 지난 날자의 이벤트 특가는 표시 되지 않습니다.<br>
				- 관리자는 과거의 요금도 확인할 필요가 있을수 있어 표시됩니다.
			</div>
	</div>
	<?}
	}?>
	
   </div>
	
 
</div>
<script>
		$(document).ready(function() {		
			$('#calendar').fullCalendar({
				header: {
					/*left: 'prev,next today',
					center: 'title',*/
					/*right: 'month',agendaWeek,agendaDay'*/
				},
				defaultDate: '<?=date("Y-m-d",time())?>',
				height: 700,
				lang: 'ko',
				editable: false,
				eventLimit: false, // allow "more" link when too many events
				events: {
					url: '/Admin/include_files/_getEventDataFee.php?pid=<?=$pid?>',
					error: function() {
					alert('잠시후에 다시 시도해 주세요.');				//$('#script-warning').show();
					}
				},
				dayClick: function(date, jsEvent, view) {
					/*
					//투어 마감
					if($('#is_end').is(':checked')){ var is_end="Y" }
					else {var is_end="N"}
					
					if ( date > new Date()) {

						$.post('/Admin/include_files/_setEventDataFee.php?pid=<?=$pid?>&setDate='+date.format()+'&is_end='+is_end,function(data){
							$('#calendar').fullCalendar( 'refetchEvents' ); //새로고침
						});		
					}
					else alert('오늘과 지난날은 휴무일 수정을 하실 수 없습니다.');

					
					//$(this).css('background-color', 'red');*/

				},
				viewRender: function(currentView){
					/*var minDate = moment();
					//maxDate = moment().add(2,'weeks');
					// Past
					if (minDate >= currentView.start && minDate <= currentView.end) {
						$(".fc-prev-button").prop('disabled', true); 
						$(".fc-prev-button").addClass('fc-state-disabled'); 
					}
					else {
						$(".fc-prev-button").removeClass('fc-state-disabled'); 
						$(".fc-prev-button").prop('disabled', false); 
					}*/
					/*// Future
					if (maxDate >= currentView.start && maxDate <= currentView.end) {
						$(".fc-next-button").prop('disabled', true); 
						$(".fc-next-button").addClass('fc-state-disabled'); 
					} else {
						$(".fc-next-button").removeClass('fc-state-disabled'); 
						$(".fc-next-button").prop('disabled', false); 
					}*/
				},
				eventRender: function (event, element) {
					//alert(element.find('span.fc-title').text());
					element.find('span.fc-title').html(element.find('span.fc-title').text());
				}
			});		
		});
	</script>
<script type="text/javascript">
$(document).ready(function() {
	$('.input-daterange, #term1_From').datepicker({
		format: 'yyyy-mm-dd',
		/*startDate: '+1d',*/
		autoclose:true,
		language :'ko',
		title:'기간 선택',
		todayHighlight:true
	});
});
function saveTourFee(){
	//var term;
	//alert($('#pid').val())
			
	if (!$("#term_From").val()) {
		alert("시작일을 선택하세요.");
		return false;
	}
	if (!$("#term_To").val()) {
		alert("시작일을 선택하세요.");
		return false;
	}
		

	$.post('/Admin/include_files/saveTourFee.php',$('#regForm').serialize(),function(data){
		//	alert(data);
			
		$('#calendar').fullCalendar( 'refetchEvents' ); //새로고침
	});			
}
function	set_fee(fee_id,fee_date){
	if(confirm("선택하신 "+fee_date+"일의 요금을 리셋하시겠습니까?")) {
		$.post('/Admin/include_files/saveTourFee.php?is_clear=Y&fee_id='+fee_id,function(data){
			$('#calendar').fullCalendar( 'refetchEvents' ); //새로고침
		});		
	}
}

function	delEventFee(fee_gubun,fee_group){
	if(confirm("선택하신 이벤트 특가를 삭제 하시겠습니까?\n삭제후에는 되살릴수 없으며, 필요시 재 등록을 하셔야 합니다.")) {
		$.post('/Admin/include_files/saveTourFee.php?is_delEventFee=Y&fee_gubun='+fee_gubun+'&fee_group='+fee_group,function(data){
			$('#calendar').fullCalendar( 'refetchEvents' ); //새로고침
		});		
	}
}
</script>