<?
if($v=="list") {
$sql_common = " from kspay_result ";

$sql_search = " where (1) ";

//If($serch_title) $sql_search .= "and (mb_id like '%$serch_title%')";
if(!$termTo) $termTo=date("Ymd",time());
if(!$termFrom) $termFrom=date("Ymd",time()-(86400*30));

If($termFrom || $termTo) {
	$fromDate=date("Ymd",strtotime($termFrom));
	$termTo=date("Ymd",strtotime($termTo));
	If($termTo) $toDate=$termTo; Else $toDate=Date("Y-m-d",strtotime($fromDate)+(86400*14));
	//echo Date("Y-m-d",$toDate);
	$sql_search .= "and (left(AppDate,8) >= '$fromDate'  and left(AppDate,8) <= '$termTo')";
	//$sql_search .= "and (applDate >= '$fromDate'   and applDate <= '$toDate'  )";
}

//If($sql_search) $sql_search=" where ".$sql_search;

If(!$s_ord) $s_ord="appDate";
If(!$s_sort) $s_sort="desc";
If($s_sort=="asc") $s_sort2="desc"; Else If($s_sort=="desc") $s_sort2="asc"; Else $s_sort2="asc";

$sql_order = "Order By $s_ord $s_sort ";

   $sql = " select count(*) as cnt
         $sql_common
         $sql_search
         $sql_order ";
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = 500;//$config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if (!$page) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$list_count = $total_count-$from_record;


    $sql = " select *
          $sql_common
          $sql_search group by OrderNumber 
          $sql_order
          limit $from_record, $rows ";
    
          
    
$result = sql_query($sql);

$colspan = 15;


?><!-- 게시판 Strat -->
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
<style type="text/css">
<!--
/*.ui-datepicker { font:12px dotum; }*/
.ui-datepicker select.ui-datepicker-month, 
.ui-datepicker select.ui-datepicker-year { width: 70px;}
.ui-datepicker-trigger { margin:0 0 -5px 2px; }
.ui-datepicker .buttonImage {border:1}
-->
</style>

<script type="text/javascript">
/* Korean initialisation for the jQuery calendar extension. */
/* Written by DaeKwon Kang (ncrash.dk@gmail.com). */
jQuery(function($){
	$.datepicker.regional['ko'] = {
		closeText: '닫기',
		prevText: '이전달',
		nextText: '다음달',
		currentText: '오늘',
		monthNames: ['1월(JAN)','2월(FEB)','3월(MAR)','4월(APR)','5월(MAY)','6월(JUN)',
		'7월(JUL)','8월(AUG)','9월(SEP)','10월(OCT)','11월(NOV)','12월(DEC)'],
		monthNamesShort: ['1월','2월','3월','4월','5월','6월',
		'7월','8월','9월','10월','11월','12월'],
		dayNames: ['일','월','화','수','목','금','토'],
		dayNamesShort: ['일','월','화','수','목','금','토'],
		dayNamesMin: ['일','월','화','수','목','금','토'],
		weekHeader: 'Wk',
		dateFormat: 'yy-mm-dd',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: true,
		showOtherMonths: true,
		selectOtherMonths: true,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ko']);

    $('#termFrom').datepicker({
        showOn: 'button',
		buttonImage: '/Admin/images/icon_calender.gif',
		buttonImageOnly: false,
        buttonText: "달력",
        changeMonth: true,
		changeYear: true,
        showButtonPanel: false,
		dateFormat: 'yy-mm-dd',
        yearRange: 'c:c+5'        
        
    }); 
	$('#termTo').datepicker({
        showOn: 'button',
		buttonImage: '/Admin/images/icon_calender.gif',
		buttonImageOnly: false,
        buttonText: "달력",
        changeMonth: true,
		changeYear: true,
        showButtonPanel: false,
		dateFormat: 'yy-mm-dd',
        yearRange: 'c:c+5'        
        
    }); 

	
});
</script>

<p > <h3>카드 결제 현황</h1></p>
<table class="admin2_board" >
		<tr>
			<th class="space-left">도움말</th><th class="space-right"><a href="#n" onclick='$("#infoArea").toggle();' title="클릭하면 상태가 변경됩니다.">보이기/숨기기</a> </th>
		</tr>
		<tr>
			<td colspan=2 id="infoArea">
			<span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 0px 0;"></span>KSNET 카드 결제 목록을 보여줍니다. <br> 
			<span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 0px 0;"></span>달력 형태로 보기를 클릭하시면 월간 달력 형태로 합계가 표시되며, 합계를 클릭하면 다시 목록으로 돌아 옵니다. <br> 
			
			</td>
		</tr>
		
		</table><br>
		
		<div class="row">
						<form method="post" action="" name="search">
						<input type="hidden" name="inc" value="kscardPayList">
							결제일 <input type="text" name="termFrom" id="termFrom" class="txt_box" style="width:70px " readonly value="<?=$termFrom?>">  ~ <input type="text" name="termTo" id="termTo"  class="txt_box" style="width:70px " readonly value="<?=$termTo?>"> 
							
							<!-- <input type="text" name="serch_title" id="serch_title" maxlength="50" value="" title="검색어 입력" class="txt_box" /> -->&nbsp; &nbsp;
							<a href="javascript:search.submit()" class="btn btn-xs btn-info">검색</a>
							<a href="/Admin/index.html?inc=kscardPayList" class="btn btn-xs btn-warning"><span><em>Reset</em></span></a>&nbsp;&nbsp;
							<a href="/Admin/index.html?inc=kscardPayList&v=cal" class="btn btn-xs btn-primary"><span style="color:#fff">달력 형태로 보기</span></a>

						</form> 
				</div>
<br>
				<table class="admin1_board" summary="등록물 게시판 입니다.">
					<caption class="blind">신규 등록물</caption>
					<colgroup>
						<col width="20"/>
						<col width="120"/>
						<col width="80"/>
						<col width=""/>
						<col width="80"/>
						<col width="200"/>
						<col width="200"/>
						<col width="100"/>
						<col width="100"/>
						<col width="100"/>
					</colgroup>
					<thead>
					<tr>
						<th scope="col">No</th>
						<th scope="col"><a href="/Admin/index.html?inc=kscardPayList&s_ord=regDate&s_sort=<?=$s_sort2?>">결제일시</a></th>
						<th scope="col">예약번호</a></th>
						<th scope="col">승인일시</a></th>
						<th scope="col">승인번호</a></th>
						<th scope="col">예약자</th>
						<th scope="col">예약상품</th>
						<th scope="col">결제금액</th>
						<th scope="col">상태</th>
					</tr>
					</thead>
					<tbody>
					<?
					for ($i=0; $row=sql_fetch_array($result); $i++) {	
						
						$moid=explode("_",$row[OrderNumber]);
						$rData=sql_fetch("select pid from tour_reg where id='$moid[1]' ");
						$pData=sql_fetch("select wr_subject,ca_name from g4_write_product where wr_id='$rData[pid]' ");
						$mData=sql_fetch("select * from g4_member where mb_no='$moid[0]' ");
						

						if($row[CancelDate]) $status="$row[CancelDate] 취소";
						else {
							$status="";
							$total+=$row[TotPrice];
						}
					?>
					<tr>
						<td scope="col"><?=$list_count--?></td>
						<td scope="col"><?=$row[AppDate]?></td>
						<td scope="col"><?=$row[OrderNumber]?></td>
						<td scope="col"><?=$row[AppDate]?></a></td>
						<td scope="col"><?=$row[ApplNum]?></a></td>
						<td scope="col"><?=$mData[mb_name]?> / <?=$mData[mb_id]?></td>
						<td><strong>[<?=$pData[ca_name]?>]</strong> <?=$pData[wr_subject]?></td>
						<td scope="col"><?=number_format($row[TotPrice])?></td>
						<td scope="col"><?=$status?></td>
					</tr>
					<?}?>
					<tr>
						<td colspan=5 class="space-right"><strong>합 계</strong></td>
						<td scope="col"><?=number_format($total)?></td>
						<td scope="col"></td>
					</tr>
					</tbody>
				</table>
				
				<nav class="space-center">
					<ul class="pagination ">
					<?
					$qstr="tourStatus=$tourStatus&tourID=$tourID&serch_title=$serch_title";
					echo get_paging_bs($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?inc=kscardPayList&$qstr&v=list&page=");
					?>
					</ul>
				</nav>
<?}?>	
<?if($v=="cal") {?>
<table class="admin2_board" >
		<tr>
			<th class="space-left">도움말</th><th class="space-right"><a href="#n" onclick='$("#infoArea").toggle();' title="클릭하면 상태가 변경됩니다.">보이기/숨기기</a> </th>
		</tr>
		<tr>
			<td colspan=2 id="infoArea">
			<span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 0px 0;"></span>KSNET 카드 결제 달력입니다. 합계금액과 승인 건수를 표시합니다. <a href="/Admin/index.html?inc=kscardPayList&v=list" class="btn btn-xs btn-primary"><span style="color:#fff">목록 형태로 보기</span></a><br> 
			
			</td>
		</tr>
		
		</table><br>
		<link href='/js/fullcalendar-2.3.1/fullcalendar.css' rel='stylesheet' />
<link href='/js/fullcalendar-2.3.1/fullcalendar.print.css' rel='stylesheet' media='print' />
<script src='/js/fullcalendar-2.3.1/lib/moment.min.js'></script>

<script src='/js/fullcalendar-2.3.1/fullcalendar.min.js'></script>
<script src='/js/fullcalendar-2.3.1/lang-all.js'></script>

<script>

$(document).ready(function() {		
	$('#calendar').fullCalendar({
		header: {
		left: 'prev,next today',
		center: 'title',
		right: ''/*month,agendaWeek,agendaDay'*/

		},
		defaultDate: '<?=date("Y-m-d",time())?>',
		height: 800,
		lang: 'ko',
		editable: false,
		eventLimit: true, // allow "more" link when too many events
		events: {
			url: '/Admin/include_files/_getKSCardPayData.php',
			error: function() {
				alert('잠시후에 다시 시도해 주세요.');				//$('#script-warning').show();
			}
		},
		dayClick: function(date, jsEvent, view) {
			$('#scheduleFrm')[0].reset();
			$('#sDate').val(date.format());
			$('#sDateStr').text(date.format());
			
			$.post("/include_files/_getScheduleModal.php?shop_id=<?=$shopID?>",function(data,status){									
				$("#sc-modal-body").html(data);
			});	

			$('#sc-modal').modal('show');

        /*alert('Clicked on: ' + date.format());
        alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
        alert('Current view: ' + view.name);
        // change the day's background color just for fun
        $(this).css('background-color', 'red');*/

		},
		eventClick: function(calEvent, jsEvent, view) {

			//alert('Event: ' + calEvent.id);
			//$('#sf_id').val(calEvent.id);
			//alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
			//alert('View: ' + view.name);

			// change the border color just for fun
			//$(this).css('border-color', 'red');
			$.post("/include_files/_getScheduleModal.php?shop_id=<?=$shopID?>&sf_id="+calEvent.id,function(data,status){									
				$("#sc-modal-body").html(data);
			});	
			$('#sc-modal').modal('show');

		}


	});		
});
</script>
<style>
#calendar {
	max-width: 900px;
	min-height: 700px;
	margin: 0 auto;
}
</style>

<div id='calendar' ></div>
<br><br>
<?}?>