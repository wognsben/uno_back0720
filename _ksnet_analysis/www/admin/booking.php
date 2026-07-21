<?php include($_SERVER['DOCUMENT_ROOT'] . "/admin/include/header.php"); 


/* PHP 8.4 기본값 방어: 기존 GET/POST 변수 사용 구조 유지 */
$termFrom = $_REQUEST['termFrom'] ?? ($termFrom ?? '');
$termTo = $_REQUEST['termTo'] ?? ($termTo ?? '');
$searchDate = $_REQUEST['searchDate'] ?? ($searchDate ?? '');
$day_gubun = $_REQUEST['day_gubun'] ?? ($day_gubun ?? '');
$serch_title = $_REQUEST['serch_title'] ?? ($serch_title ?? '');
$tourID = $_REQUEST['tourID'] ?? ($tourID ?? '');
$is_event_pid = $_REQUEST['is_event_pid'] ?? ($is_event_pid ?? '');
$isMemo = $_REQUEST['isMemo'] ?? ($isMemo ?? '');
$isB2B = $_REQUEST['isB2B'] ?? ($isB2B ?? '');
$is_card = $_REQUEST['is_card'] ?? ($is_card ?? '');
$is_all = $_REQUEST['is_all'] ?? ($is_all ?? '');
$rid = $_REQUEST['rid'] ?? ($rid ?? '');
$na = $_REQUEST['na'] ?? ($na ?? '');
$page = $_REQUEST['page'] ?? ($page ?? 1);
$s_ord = $_REQUEST['s_ord'] ?? ($s_ord ?? '');
$s_sort = $_REQUEST['s_sort'] ?? ($s_sort ?? '');
$tourStatus = $_REQUEST['tourStatus'] ?? ($tourStatus ?? '');
$p_sca = $_REQUEST['p_sca'] ?? ($p_sca ?? array());
$status = $_REQUEST['status'] ?? ($status ?? array());

$where = $where ?? array();
$where_na = $where_na ?? array();
$where_st = $where_st ?? array();
$where_p = $where_p ?? array();
$na_q = $na_q ?? '';
$sql_search = $sql_search ?? '';
$s_sort2 = $s_sort2 ?? 'asc';
$feeTxt = $feeTxt ?? '';
$mCnt = $mCnt ?? 0;
$pData = $pData ?? array();

if (!is_array($p_sca)) $p_sca = array($p_sca);
if (!is_array($status)) $status = array($status);


$sql_common = " from `tour_reg` as r, g5_write_product as p ";


$where[]=" r.del_time < 111  ";
$where[]=" r.pid=p.wr_id ";

$where[]=" not (r.status ='cart' or r.status ='booking' ) ";
if($termFrom) $past_tour_day = $termFrom;
else $past_tour_day = G5_TIME_YMDHIS;//$g4['time_ymdhis'];//date("Y-m-d",strtotime("-15 day"));//, "\n";

/* 국가 검색 처리 */
if(count($p_sca ?? array())<1) {
	if($na=="it") $p_sca=array("단체", "단체차량");
	else if($na=="fr") $p_sca=array("파리-워킹", "파리-차량");
}
foreach($p_sca as $k => $v) {
	if($v) {
		$where_na[]=" r.nation='$v'";
	}
}
if(count($where_na ?? array())) $where[]=" ( ".implode(" or ", $where_na). " ) ";

/* 상태값 처리 */
if(isset($status)) {
}
else {
	if(!$_REQUEST) if(!$tourStatus) $tourStatus=1;
	if($tourStatus) $status[]=$tourStatus;
}

foreach($status as $k => $v) {
	if($v) {
		if($is_all) $where_st[]=" (r.status ='$v'   )";
		else if($v=="3") $where_st[]=" (r.status ='3'  and  r.tourDay >= '".$past_tour_day."' )";
		else if($v=="2") $where_st[]=" (r.status ='2' and  r.tourDay >= '2018-01-01' )";
		else if($v=="past") $where_st[]="   (r.status ='3'  and r.tourDay < '".$past_tour_day."' )";
		else $where_st[]="  (r.status ='$v' ) ";
	}
}
if(count($where_st ?? array())) $where[]=" ( ".implode(" or ", $where_st). " ) ";

if($isB2B) $where[]=" ( r.isB2B='Y' or r.isB2B is NULL ) ";
/*if($bank_in_date=="Y") $where[]=" r.bank_in_date > 11   ";
else if($bank_in_date=="N") $where[]=" r.bank_in_date < 11   ";*/
if($tourID) {
	if($is_event_pid) $where[]="  ( r.pid='$tourID' and  r.event_pid is null ) ";
	else $where[]="  (r.pid ='$tourID' or  r.event_pid='$tourID') ";
}
if($isMemo) $where[]="  ( length(r.adminMemo) >5 or length(r.adminMemoCancel) >5  ) ";

If($serch_title) {
	$where[]="  (r.mb_id like '%$serch_title%' or r.card_pay like '%$serch_title%' or r.mb_name like '%$serch_title%'  or r.mb_hp like '%$serch_title%' or r.mb_kakao like '%$serch_title%' ) " ;//or r.bank_in_name like '%$serch_title%')";

}

if($searchDate) $termFrom=$termTo=$searchDate;

If($termFrom || $termTo) {
	$fromDate=$termFrom;
	If($termTo) $toDate=$termTo." 23:59:59"; Else $toDate=Date("Y-m-d",strtotime($fromDate)+(86400*14));
		//echo Date("Y-m-d",$toDate);
	if($day_gubun=="tourDay") $where[]=" (tourDay >= '$fromDate'   and tourDay <= '$toDate'  )";
	else {//예약일
		$where[]=" (regDate >= '".strtotime($fromDate)."'   and regDate <= '".strtotime($toDate)."'  )";
	}
}
if($is_card) $where[]=" length(card_pay) >10 ";

if($rid)  $where[]=" id='$rid' ";
//else if($pay_gubun=="bank") $sql_search .= " and length(card_pay) <10 ";
//If($sql_search) $sql_search=" where ".$sql_search;

if(count($where ?? array()) ) $sql_search = " where ".implode(" and " , $where);

If(!$s_ord) {
	if($tourStatus=="11") {//오피스 예약확인시 카드 결제를 먼저 보여준다.
		$s_ord="LENGTH(card_pay) DESC, tourDay";
	}
	else $s_ord="tourDay";
}
If(!$s_sort) $s_sort=" desc ";
If($s_sort=="asc") $s_sort2="desc"; Else If($s_sort=="desc") $s_sort2="asc"; Else $s_sort2="asc";

$sql_order = "Order By $s_ord $s_sort ";

   $sql = " select count(*) as cnt
         $sql_common
         $sql_search
         $sql_order ";
$row = sql_fetch($sql);
$total_count = (int)($row['cnt'] ?? 0);

$rows = 30;//$config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if (!$page) $page = 1;
$page = (int)$page; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = max(0, ($page - 1) * $rows); // 시작 열을 구함

$list_count = $total_count-$from_record;

   $sql = " select *
          $sql_common
          $sql_search
          $sql_order
          limit $from_record, $rows ";
$result = sql_query($sql);
//include_once("./admin.head.php");

/*
If($isMemoSave=="Y") {

	If($adminMemo[$r_id]) {
		$adminMemo2=addslashes($adminMemo[$r_id]);
		sql_query(" update `tour_reg` set adminMemo='$adminMemo2' where id='$r_id' ");
		//echo "update `tour_reg` set adminMemo='$adminMemo2' where id='$r_id'";
	}
	If($adminMemoCancel[$r_id]) {
		$adminMemoCancel2=addslashes($adminMemoCancel[$r_id]);
		sql_query(" update `tour_reg` set adminMemoCancel='$adminMemoCancel2' where id='$r_id' ");
		//echo "update `tour_reg` set adminMemoCancel='$adminMemoCancel2' where id='$r_id'";
	}
	alert("메모가 저장 되었습니다.","/Admin/index.html?inc=".$inc."&r_id=$r_id&tourStatus=$tourStatus&termFrom=$termFrom&termTo=$termTo&na=$na");
}
*/
    $sql = " select r.*, p.ca_name, p.wr_subject 
          $sql_common
          $sql_search 
          $sql_order
          limit $from_record, $rows ";
   
$result = sql_query($sql);

$colspan = 15;

$trs=sql_query("select * from tour_reg where mb_name is null order by id desc");
while ($trow=sql_fetch_array($trs) ) {
	$mb=get_member($trow['mb_id']);
	//print_r($mb);
	sql_query("UPDATE  `tour_reg` SET    `mb_name` = '{$mb['mb_name']}',  `mb_email` = '{$mb['mb_email']}', `mb_hp` = '{$mb['mb_hp']}' , `mb_kakao` = '{$mb['mb_1']}'  where   `id` = '{$trow['id']}'");
}

?>

<div class="sidebar-left mo-sidebar-close">
	<div class="side-menu">
		<?php include($_SERVER['DOCUMENT_ROOT'] . "/admin/_side_menu.php"); ?>
	</div>
	<div class="mo-close close"><i class="fa fa-angle-right"></i></div>
</div>

<div class="page-content dashboard mo-page-content">
	<div class="header-content">
		<h2><i class="fa fa-home"></i>예약자 관리 <?php//=date("Y-m-d H:i:s", "1555936016");//=$na_str?> <?php //echo  date("Y-m-d",strtotime("-1 days"));?><small> </small></h2>
	</div>
	<div class="body-content">
		<?php//=$sql?>
		<div class="panel rounded shadow panel-default"  >
			<div class="panel rounded shadow panel-default"  >
				<div class="board-form" style="margin-top:0px; padding:10px;background-color:#fff">
				<?php
				$not_que="and not (r.status ='cart' or r.status ='booking' )  ";
					foreach($bk_status_a as $k => $v) {
					if($k=="1" || $k=="11" || $k=="2" || $k=="3") $row_cnt=sql_fetch("select count(r.id) as cnt from  `tour_reg` as r, g5_write_product as p where r.pid=p.wr_id and (r.status ='$k' ) and r.tourDay >= '".G5_TIME_YMD."' {$not_que} $na_q ");
					else if($k=="91" || $k=="9" || $k=="99" || $k=="3") $row_cnt=sql_fetch("select count(r.id) as cnt from  `tour_reg` as r, g5_write_product as p where r.pid=p.wr_id and (r.status ='$k' ) and r.tourDay >= '".G5_TIME_YMD."'  {$not_que} ");
					else if($k=="past") $row_cnt=sql_fetch("select count(r.id) as cnt from  `tour_reg` as r, g5_write_product as p where r.pid=p.wr_id and (r.status ='3' ) and r.tourDay < '".G5_TIME_YMD."'   {$not_que}  $na_q  ");
					?>
					<a href="/admin/booking.php?na=<?=$na?>&tourStatus=<?=$k?>&tourID=<?=$tourID?>&day_gubun=<?=$day_gubun?>" class="btn btn-<?=($tourStatus==$k)?"primary":"default"?> btn-sm"><?=$v?> <span class="badge"><?=number_format($row_cnt['cnt'])?></span></a>
					<?php }?>
					<br><br>
				<!-- <fieldset class="serch_box" class="txt_box"> -->
				<form method="get" action="<?=$PHP_SELF?>" name="search" id="search_form" autocomplete="off">
				<input type="hidden" name="s_ord" id="s_ord"value="<?=$s_ord?>" >
				<input type="hidden" name="s_sort" id="s_sort" value="<?=$s_sort?>" >
				<input type="hidden" name="page" id="page" value="<?=$page?>" >
					<table class="table">
					<colgroup>
					<col style="width:100px">
					<col style="width:*">
					<col style="width:100px">
					<col style="width:*">
					</colgroup>
					<tr>
						<th>구분</th>
						<td ><?php
						//$sel_na_a=array("uk"=>"영국", "sp"=>"스페인",  "fr"=>"프랑스"   , "it"=>"이탈리아"  , "cz"=>"체코"  ,"티켓"=>"티켓" ,"기타"=>"기타");
						foreach($tmp_na_a as $k => $v) {
									
							?><label class="mgr10"><input type="checkbox" name="p_sca[]" value="<?=$v?>" <?=(in_array($v, $p_sca ?? array()))?"checked":""?>> <?=$v?></label>
						<?php }?>
						</td>
						<th>예약 상태</th>
						<td ><?php
						foreach($bk_status_a as $k => $v) {
							/*if($k=="1" || $k=="11" || $k=="2" || $k=="3") $row_cnt=sql_fetch("select count(id) as cnt from `tour_reg` where (status ='$k' ) and tourDay >= '".G5_TIME_YMD."' $na_q ");
							else if($k=="91" || $k=="9" || $k=="99" || $k=="3") $row_cnt=sql_fetch("select count(id) as cnt from `tour_reg` where (status ='$k' )  $na_q ");
							else if($k=="past") $row_cnt=sql_fetch("select count(id) as cnt from `tour_reg` where (status ='3' ) and tourDay < '".G5_TIME_YMD."'   $na_q  ");
							 <span class="badge"><?=number_format($row_cnt['cnt'])?></span> 
							 */
									
							?><label class="mgr10"><input type="checkbox" name="status[]" value="<?=$k?>"  <?=(in_array($k, $status ?? array()))?"checked":""?>> <?=$v?></label>
						<?php }?>
						</td>
					</tr>
					<tr>
						<th>기간검색</th>
						<td>
							<label ><input type="radio" name="day_gubun" value="tourDay" <?=($day_gubun=="regDate")?"":"checked"?>> 투어일</label> 
							<label ><input type="radio" name="day_gubun" value="regDate" <?=($day_gubun=="regDate")?"checked":""?>> 예약일</label> 
							<input type="text" name="termFrom" id="" class="form-control input-ssm selDate" style="width:70px "  value="<?=$termFrom?>"> ~ 
							<input type="text" name="termTo" id=""  class="form-control input-ssm selDate" style="width:70px "  value="<?=$termTo?>">
							<input type="button" class="btn btn-primary btn-xs" value="어제" onclick="set_date('yes')">
									<input type="button" class="btn btn-primary btn-xs" value="오늘" onclick="set_date('tod')">
									<input type="button" class="btn btn-primary btn-xs" value="일주" onclick="set_date('week')">
									<input type="button" class="btn btn-primary btn-xs" value="한달" onclick="set_date('mon')">
									<input type="button" class="btn btn-primary btn-xs" value="당월" onclick="set_date('thsMon')">
									<?php
									$date1_a['yes']=date("Y-m-d", (G5_SERVER_TIME-86400) );
									$date2_a['yes']=$date1_a['yes'];//." 23:59:59";

									$date1_a['tod']=date("Y-m-d", (G5_SERVER_TIME) );
									$date2_a['tod']=$date1_a['tod'];//." 23:59:59";

									$date1_a['week']=date("Y-m-d", (G5_SERVER_TIME-(86400*7)) );
									$date2_a['week']=date("Y-m-d", G5_SERVER_TIME);//." 23:59:59";

									$date1_a['mon']= date("Y-m-d", (G5_SERVER_TIME-(86400*30)) );
									$date2_a['mon']=$date1_a['tod'];//." 23:59:59";

									$date1_a['thsMon']= date("Y-m", G5_SERVER_TIME)."-01";		$end_day = date("t", G5_SERVER_TIME);
									$date2_a['thsMon']=date("Y-m", G5_SERVER_TIME)."-".$end_day;//." 23:59:59";

									/*$preMon_m = date("n", G5_SERVER_TIME) - 1;
									$thisY= date("Y", G5_SERVER_TIME);


									$date1_a['preMon']= date("Y-m", G5_SERVER_TIME)."-01";		
									$date2_a['preMon']=date("Y-m", G5_SERVER_TIME)."-".$end_day." 23:59:59";*/
									?>
									<script type="text/javascript">
									function set_date(gubun) {
										var d1, d2;
										if(gubun=="yes") {d1='<?=$date1_a['yes']?>'; d2='<?=$date2_a['yes']?>';}
										else if(gubun=="tod") {d1='<?=$date1_a['tod']?>'; d2='<?=$date2_a['tod']?>';}
										else if(gubun=="week") {d1='<?=$date1_a['week']?>'; d2='<?=$date2_a['week']?>';}
										else if(gubun=="mon") {d1='<?=$date1_a['mon']?>'; d2='<?=$date2_a['mon']?>';}
										else if(gubun=="thsMon") {d1='<?=$date1_a['thsMon']?>'; d2='<?=$date2_a['thsMon']?>';}
										//else if(gubun=="preMon") {d1='<?=$date1_a['preMon']?>'; d2='<?=$date2_a['preMon']?>';}

										$('#termFrom').val(d1);
										$('#termTo').val(d2)
									}
									</script>
						</td>
						<th>투어 선택</th>
						<td>
							<select name="tourID" class="form-control input-sm" style="padding:0px; width:350px; display:inline-block">
							<option value="" selected>투어 선택</option>
								<?=get_select_list("product", $tourID)?>		
							</select>
							<label ><input type="checkbox" name="is_event_pid" value="1" <?=($is_event_pid)?"checked":""?>>이벤트 투어 제외</label>
						
						</td>
					</tr>
					<tr>
						<th>검색</th>
						<td>
							<input type="text" name="rid" id="rid"  value="<?=$rid?>"  class="form-control input-sm" style="width: 100px; display:inline-block" placeholder="예약번호"/>
							<input type="text" name="serch_title" id="serch_title"  value="<?=$serch_title?>" title="검색어 입력" class="form-control input-sm" style="width:70%" placeholder="이름,이메일,카톡 검색"/>
						</td>
						 <th>기타 옵션</th>
						<td>
													
							<label class="mgr10"><input type="checkbox" name="is_card" value="1"  <?=($is_card)?"checked":""?>> 카드 결제 </label>
						
							<!-- <label class="mgl20 mgr10"><input type="radio" name="bank_in_date" value="Y"  <?=($bank_in_date=="Y")?"checked":""?>> 입금</label>
							<label class="mgr10"><input type="radio" name="bank_in_date" value="N"  <?=($bank_in_date=="N")?"checked":""?>> 미 입금</label> -->
						</td>
					</tr>
					</table>
					<div class="text-center mgt10">
						<input type="submit" class="btn btn-sm btn-primary" value="검색" style="_margin-right:50px">
						<a href="<?=$PHP_SELF?>" class="btn btn-sm btn-warning" style="color:#fff">Reset</a> &nbsp;  &nbsp;  &nbsp; 
						<!-- <a href="javascript:;" class="btn btn-sm btn-default edit-btn" data-toggle="modal" data-target="#regModal">추가</a> &nbsp;  -->
						<!-- <a href="javascript:;" onclick="window.open('/admin/XLS.php?inc=regist&tourStatus=<?=$tourStatus?>&day_gubun=<?=$day_gubun?>&termFrom=<?=$termFrom?>&termTo=<?=$termTo?>&tourID=<?=$tourID?>&na=<?=$na?>&na_list=<?=implode(",",$na_list)?>&status=<?=implode(",",$status)?>','')" class="btn btn-sm btn-danger " style="color:#fff" >엑셀다운</a> -->
					</div>
			</div>	
			<table class="table table-condensed table-bordered" >
				<colgroup>
					<col width="20"/>
					<col width="20"/>
					<col width="100" 예약일/>
					<col width="150" 아이디/>
					<col width="170" 핸드폰/>
					<col width="*"  예약상품/>
					<col width="90" 투어일/>
					<col width="180" 인원/>
					<col width="80" 예약금/>
					<col width="100" 잔금/>
					<col width="50" 결제/>
					<col width="90" 상태/>
					<col width="90" 바우처/>
					<col width="60" 상세/>
				</colgroup>
				<thead>
				<tr>
					<th scope="col"></th>
					<th scope="col">No</th>
					<th scope="col"><a href="/admin/booking.php?na=<?=$na?>&s_ord=r.regDate&s_sort=<?=$s_sort2?>">예약일</a><br>(예약번호)</th>
					<th scope="col"><a href="/admin/booking.php?na=<?=$na?>&s_ord=r.mb_id&s_sort=<?=$s_sort2?>">아이디</a><br>
					<a href="/admin/booking.php?na=<?=$na?>&s_ord=r.mb_name&s_sort=<?=$s_sort2?>">이름</a></th>
					<th class="space-center">이메일<br>핸드폰<br>카톡 아이디</th>
					<th scope="col" style="min-width:200px">예약상품</th>						
					<th scope="col"><a href="/admin/booking.php?na=<?=$na?>&s_ord=r.tourDay&s_sort=<?=$s_sort2?>">투어일</a></th>
					<th scope="col">인원</th>
					<th scope="col">예약금</th>
					<th scope="col">잔금</th>
					<!-- <th scope="col">현지지불</th> -->
					<th scope="col">결제
					<!-- <select name="card_pay" onchange="$('#pay_gubun').val(this.value);$('#search_form').submit();">
						<option value="" >결제
						<option value="card">카드 Only
					</select> --></th>
					<th scope="col">상태</th>
					<th scope="col">바우처</th>
					<th scope="col">상세</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$color_a=array("","c_5c11d7","c_11d7c7","c_ff5865","c_7d8de1","c_b0be01","c_bf83db","c_fb70b3","c_32c081","c_ff9a04");
				for ($i=0; $row=sql_fetch_array($result); $i++) {
					$product_subject=get_product_subject($row['pid'], $row['event_pid']); //투어명 가제오기
					$mb = get_member($row['mb_id']);

					if($row['card_pay'])  $pay_result="카드"; else $pay_result="은행";

					$pay_row=sql_fetch("select * from kspay_result where ApplNum = '".$row['card_pay']."'   order by id desc limit 1");
					if($pay_row['CancelDate'])  $pay_result="은행";

					$reg_no=$mb['mb_no']."_".$row['id'];

					$feeTxt=str_replace("<진정한 이벤트2><진정한 이벤트2>","<진정한 이벤트2>",$feeTxt);
					
					?>
				<tr  style="<?=($row['status']=="91")?"background-color:#ff9999":""?>">
					<td><?php If($row['status']=="9"){?><input type="checkbox" name="sel" value="<?=$row['id']?>"><?php }?></td>
					<td><?=$list_count--?></td>
					<td title="<?=Date("Y-m-d H:i:s",$row['regDate'])?>"><?=($row['regDate']>1)?Date("Y-m-d",$row['regDate']):"";?><?php//=$row['id']?><br>(<?=$reg_no?>)
					</td>
					<td><?php/* 일단 숨김. 실수 할수 있어서 <input type="text" name="" class="mb_id" value="<?=$row['mb_id']?>" id="<?=$row['id']?>_<?=$row['mb_id']?>">*/?>
						<?=$row['mb_id']?><br><?php if($row['isMobile']=="Y") {?><i class="fa fa-mobile-phone"></i> <?php }?>  <?=$row['mb_name']?></td>
					<td>
						<!-- <a href="javasctipt:;" data-toggle="modal" data-target="#sendMail" onclick="$('#mail_email').val('<?=$row['mb_email']?>')"> --><span><?=$row['mb_email']?></span></a><br>
						<!-- <a href="javasctipt:;" data-toggle="modal" data-target="#sendSms" onclick="$('#sms_hp').val('<?=$row['mb_hp']?>')"><span> --><i class="fa fa-mobile-phone"></i> <?=$row['mb_hp']?></span></a><br>
						<span><i class="fa fa-paperclip"></i> <?=$row['mb_kakao']?></span></td>
					<td><?=$product_subject?></td>
					<td class="space-center"><?=$row['tourDay']?> <?php//=($row['tourTime'])?$row['tourTime']:""?></td>						
					<td align="right"><?=get_res_member_str($row['id'], "bk")?>
					<?php If($pData['ca_name']=="단체" || $pData['ca_name']=="단체차량") {?><strong>전체 : <?=$mCnt?>명</strong><br><?php }?><?=$feeTxt?></td>
					<?php
					If($row['isEvent']=="Y") {
						if($row['isFRevent']=="Y") $tmp_str="화려한 이벤트"; else $tmp_str="진정한 이벤트2";?>
					<td align="center" colspan=2>
						<?php if($row['pid'] == "4"){?>
							<input type="button" value="<?=$tmp_str?>" class="btn btn-xs btn-warning">
						<?php }else{?>
							<input type="button" value="이벤트" class="btn btn-xs btn-warning">
						<?php }?>
					</td>
					<?php }
					else If($row['isCombo']=="Y") {?>
					<td align="center" colspan=2><input type="button" value="콤보투어" class="btn_status btn_status_combo"></td>
					<?php }
					Else {
						
						$total_fee4  = preg_replace("/[^0-9]/", "",$row['total_fee4']);   //티켓 요금과 예약 요금 분리 시킴. 2018/01/25
						$total_fee1  = preg_replace("/[^0-9]/", "",$row['total_fee1']);
						$total_fee = ((int)($row['total_fee3'] ?? 0) > 0) ? ((int)$total_fee4 / (int)$row['total_fee3']) : 0;
					if($row['nation']=="패키지") {

						?>
							<td align="right" colspan="2" >
								<span class="label label-primary">예</span> <?=number_format($row['total_fee1'])?>
								<span class="label label-primary">중</span> <?=number_format($row['total_fee2'])?><br>
								<span class="label label-primary">잔</span> <?=number_format($row['total_fee3'])?>
								<span class="label label-info">A</span> <?=number_format($row['total_fee_air'])?><br>
								<span class="label label-danger">Total</span> <?=number_format($row['total_fee4'])?>
								</td>
							<?php

					}
                    else if (stripos($row['nation'], '세미패키지') !== false) {
                        ?>
                        <td align="right" colspan="2" >
                            <span class="label label-primary">예</span> <?=number_format($row['total_fee1'])?>
                            <span class="label label-primary">중</span> <?=number_format($row['total_fee2'])?><br>
                            <span class="label label-primary">잔</span> <?=number_format($row['total_fee3'])?>
                            <span class="label label-info">A</span> <?=number_format($row['total_fee_air'])?><br>
                            <span class="label label-danger">Total</span> <?=number_format($row['total_fee4'])?>
                        </td>
                        <?php
                    }
					else {
						if ($row['total_fee4']) {?>
						<td align="right"><?=number_format($total_fee1)?></td>
						<?php } else {?>
						<td align="right"><?=number_format(str_replace(",","",$row['total_fee1']))?></td>
						<?php }?>					
						<td align="right"><?=$row['total_fee2']?>유로
						<?php if ($row['total_fee4']) {?>
						<br/>
						<?=$total_fee?> * <?=$row['total_fee3']?><br/>
						= <?=$row['total_fee4']?> 원
						<?php }?>
						</td>
						<?php }
						}?>
					<th scope="col"><?=($row['isEvent'])?"":$pay_result?></th>
					<td >
						<select name="status_<?=$i?>" id="status_<?=$i?>"  data-rid="<?=$row['id']?>" style="width:80px; padding:3px" class="set_reg_status <?=($color_a[$row['status']] ?? '')?>">
							<option value="1" <?=($row['status']=="1")?"selected":""?>>예약대기
							<option value="2" <?=($row['status']=="2")?"selected":""?>>예약확인
							<option value="3" <?=($row['status']=="3")?"selected":""?>>예약확정
							<option value="9" <?=($row['status']=="9" && !$row['cancel_code'])?"selected":""?> style="color:red">예약취소<?=($row['adminCancelDate']>0)?"-".Date("Y-m-d H:i:s",$row['adminCancelDate']):""?>
							<option value="95" <?=($row['status']=="9" && $row['cancel_code']=="1")?"selected":""?> style="color:red">예약취소-미결제 <?=($row['adminCancelDate']>0)?"-".Date("Y-m-d H:i:s",$row['adminCancelDate']):""?>
							<option value="96" <?=($row['status']=="9" && $row['cancel_code']=="2")?"selected":""?> style="color:red">예약취소-투어불가능<?=($row['adminCancelDate']>0)?"-".Date("Y-m-d H:i:s",$row['adminCancelDate']):""?> 
							<option value="91" <?=($row['status']=="91")?"selected":""?> style="color:red;">취소요청<?=($row['memCancelDate']>0)?"-".Date("Y-m-d H:i:s",$row['memCancelDate']):""?>
						</select>
					</td>
					<td>
						<input type="button" value="보기" onclick="popup_page('/voucher.php?rid=<?=$row['id']?>','voucherWin');" class="btn btn-xs btn-info" style="display:inline-block"> 
						<a class="btn btn-xs btn-success  pop_fancy _pop_fancy_bk" href="/admin/popup/pop_content.php?gubun=mail_history&rid=<?=$row['id']?>" style="display:inline-block">메일</a>
						<!-- <input type="button" value="메일" onclick="popup_page('/admin/popup/pop_content.php?rid=<?=$row['id']?>','voucherWin');" class="btn btn-xs btn-success mgl5">  -->
						<input type="button" value="발송-고객" onclick="send_mb_voucher('<?=$row['id']?>')" _onclick="popup_page('/admin/include_files/setReg.php?r_id=<?=$row['id']?>&sendV=Y','voucherWin');" class="btn btn-xs btn-warning">
						<input type="button" value="발송->관리자" onclick="send_adm_voucher('<?=$row['id']?>')" _onclick="popup_page('/admin/include_files/setReg.php?r_id=<?=$row['id']?>&sendV=Y&isAdm=Y','voucherWin');" class="btn btn-xs btn-info">	
					</td>
					<td align="right">
					<?php /*구 버전 기능
						<input type="button" value="상세" class="btn btn-xs btn-primary" onclick="$('#memo_<?=$i?>').toggle();">
						<button type="button" class="btn btn-info btn-xs edit-btn" id="<?=$row['id']?>" data-toggle="modal" data-target="#regModal">수정</button> */?>
						<a class="btn btn-xs btn-primary pop_fancy _pop_fancy_bk" href="/admin/popup/pop_content.php?gubun=booking&rid=<?=$row['id']?>" >수정</a>
					</td>
				</tr>
				<?php }?>
				</tbody>
			</table>
		</div>
		<nav class="space-center">
			<ul class="pagination ">
			<?php
			$qstr="&tourStatus=$tourStatus&na=$na&&tourID=$tourID&serch_title=$serch_title&termFrom=$termFrom&termTo=$termTo";
			echo get_paging_bs($config['cf_write_pages'], $page, $total_page, $_SERVER["PHP_SELF"]."?".$qstr."&page=");
			?>
			</ul>
		</nav>
	 <!-- <input type="button" value="선택 삭제" onclick="req_del()" class="btn btn-sm btn-danger">  -->
<script >
function send_adm_voucher(r_id) {
	$.post('/admin/include_files/setReg.php',{r_id:r_id, sendV :'Y', isAdm:'Y' },function(data,status){
		if($.trim(data)=="ok") jalert("바우처 발송","관리자에게 바우처가 발송 되었습니다.");
		else jalert("바우처 발송","알수 없는 오류가 발송되어 바우처가 방송 되지 않았습니다.<br>시스템 관리자에게 문의 해 주세요");
	});
}
function send_mb_voucher(r_id) {
	$.post('/admin/include_files/setReg.php',{r_id:r_id, sendV :'Y' },function(data,status){
		if($.trim(data)=="ok") jalert("바우처 발송","고객에게 바우처가 발송 되었습니다.");
		else jalert("바우처 발송","알수 없는 오류가 발송되어 바우처가 방송 되지 않았습니다.<br>시스템 관리자에게 문의 해 주세요");
	});
}

function req_del(){
	var chk=$('input:checkbox[name="sel"]:checked');
	var sel="";
	$(":checkbox").each(function (i) {
         if( this.checked ){ sel += $(this).val() + ";"; }
   });
   if(!sel) alert("삭제하실 하실 예약을 선택해 주세요");
	
	if(confirm('삭제시 되돌릴수 없습니다. 삭제 하시겠습니까?')) {

		$.get("/admin/include_files/del_regist.php?sel="+sel,function(data,status){		
			
			jalert("삭제","회원의 예약을 삭제하였습니다..", "re");
		});
	}
}

	

	$( ".set_reg_status" ).change(function() {
		 var sel_val=$(this).val(); //셀렉트 박스 값 읽어오기
		 var rid=$(this).data("rid"); 

		if(sel_val=='9' || sel_val=='3') {
			if (confirm('상태 변경을 하시겠습니까?') == false) {
				return;
			}
		}
		$.post('/admin/include_files/setReg.php',{r_id:rid, sel :sel_val },function(data,status){
		//console.log( data);
			//jalert("상태 변경","회원의 예약 상태를 변경하였습니다.", "re");
			alert("회원의 예약 상태를 변경하였습니다.");
			location.reload();
		});    
        
	});


</script>


<?php include($_SERVER['DOCUMENT_ROOT'] . "/admin/include/footer.php"); ?>