<?php

/*********************************************
2018 11 14 이전 버전의 회원등 데이터 이전 처리 
*********************************************/
//https 도메인 처리 부분. 차후 다시 오픈

//	if(!isset($_SERVER["HTTPS"])) {
//	 header('Location: https://www.unotravel.co.kr'.$_SERVER[REQUEST_URI]);
//	}
//	else
//		if(!( $_SERVER['HTTP_HOST']=="www.unotravel.co.kr" ) ) {
//		 header('Location: https://www.unotravel.co.kr'.$_SERVER[REQUEST_URI]);
//		 exit();
//		}

if($member[mb_id]) {
	//$tmp_a=explode("_", $member[mb_id]);

	$mp_row=sql_fetch("select * from g5_member_social_profiles where mb_id='$member[mb_id]' ");
	if($mp_row[mp_no]) $sns_provider=$mp_row[provider];
}

// 세미 페키지 추가를 위한 버전
 $ver2002="1";

/* 포인트 사용여부 항상 0 */
$config['cf_use_point']="";

/* 회원 정보 복사 리뉴얼시 사용 */
/*
if($mb_copy=0) {
	$columns = sql_field_names($g5['member_table']);

	$sql = " select * from g4_member";
	$result = sql_query($sql);
	for($i=0; $row=sql_fetch_array($result); $i++) {


		// 중복체크
		$sql2 = " select count(*) as cnt from {$g5['member_table']} where mb_id = '{$row['mb_id']}' ";
		$row2 = sql_fetch($sql2);
		if($row2['cnt'])
			continue;

		$comma = '';
		$sql_common = '';

		foreach($row as $key=>$val) {
			if($key == 'mb_no')
				continue;

			if(!in_array($key, $columns))
				continue;

			$sql_common .= $comma . " $key = '".addslashes($val)."' ";

			$comma = ',';
		}

		sql_query(" INSERT INTO {$g5['member_table']} SET $sql_common ");
	}
} */
/**/
/*$trs=sql_query("select * from g5_board_file  " );
while($tmp_row=sql_fetch_array($trs)) {
	$select_que=" select * from g5_board_file where bo_table='{$tmp_row[bo_table]}' and wr_id='{$tmp_row[wr_id]}' and bf_no='{$tmp_row[bf_no]}' ";
	$ttt=sql_fetch($select_que );
	if($ttt[bo_table]) {
	}
	else {
		sql_query ("insert into g5_board_file select * from g5_board_file where bo_table='{$tmp_row[bo_table]}' and wr_id='{$tmp_row[wr_id]}' and bf_no='{$tmp_row[bf_no]}' ");
	}
}*/

 $pn=substr(basename($PHP_SELF),0,-4);

if(!$_na) $_na="it";
if(!$na) $_na="it";

$bk_status_a=array("1"=>"예약 대기(신청)",  "2"=>"예약 확인(입금)", "3"=>"예약 확정",  "91"=>"예약 취소 요청", "9"=>"예약 취소",  "past"=>"지난 예약" );


$tmp_na_a=array("단체","단체차량","차량","파리-워킹","파리-차량", "패키지");
if($pid && $inc) { //$inc=="prod"
	$tmp_row=sql_fetch("select * from g5_write_product where wr_id='$pid'");

	if(preg_match("/파리/",$tmp_row[ca_name]) ) $_na="fr";
	else if(preg_match("/런던/",$tmp_row[ca_name]) )  $_na="uk";
	else $_na="it";

}

$na_a=array("it"=>"이탈리아", "fr"=>"프랑스");
$prod_sca_a["it"]=array("단체"=>"단체 워킹 투어","단체차량"=>"단체 차량 투어");// 차량은 1:1 맞춤투어로 ,"차량"=>"단독 차량 패키지 투어");
$prod_sca_a["fr"]=array("파리-워킹"=>"단체 워킹 투어","파리-차량"=>"단체 차량 투어");


/*if($_na=="it") {
	$_na_str="이탈리아"; $_na_str_en="Italy";
	$_ct_str="로마"; $_ct_str_en="Roma";
	$_na_a=array("이탈리아","파리"); $_na_cd_a=array("it","fr","uk");
}
else if($_na=="fr") {
	$_na_str="프랑스"; $_na_str_en="France";
	$_ct_str="파리"; $_ct_str_en="Paris";
	$_na_a=array("파리","이탈리아");  $_na_cd_a=array("fr","uk","it");
}*/

//if($bo_table=="tourinfo" || $bo_table=="tourOption" || $bo_table=="product" || $bo_table=="admin_img") {$sca="";}

$_isMobile=$_COOKIE[_isMobile];

/* 자동 실행 처리.
- 예약 table에 booking, cart 에 등록후 최종 예약 완료하지 않은 예약건 정리
- 기타 자동 실행 */
auto_run();

function get_select_list($gubun, $sel_value="", $opt="", $na="") {
	global $tmp_na_a;

	if($gubun=="product") {//투어 선택
		if($na) {
			if($na=="콤보투어") {
				$ca_que=" and ( ca_name='$na') ";
				$order_by=" ORDER BY  wr_subject";
			}
//			else if($na=="세미패키지") {
			else if (stripos($na, '세미패키지') !== false) {
				$ca_que=" and (ca_name = '$na' or ca_name LIKE '%, $na%' or ca_name LIKE '%$na,%') ";
				$order_by=" ORDER BY  wr_subject";
			}
			else if($na=="b2b") {
				$ca_que=" and wr_7>111";
				$order_by=" ORDER BY  wr_subject";
			}
			else {
				$ca_que=" and ( ca_name='$na' or  ca_name='기타' or  ca_name='티켓' ) and wr_7>111";
				$order_by=" ORDER BY CASE WHEN (ca_name = '기타' or ca_name='티켓' ) THEN 1 ELSE 0 END,  wr_subject";
			}
		}
		else {
			$ki=1;
			foreach($tmp_na_a as $k => $v) {

				$orderby_a[]=" WHEN ca_name = '$v'  THEN $ki  ";
				$ki++;
			}
			$order_by=" order by CASE ".implode(" ", $orderby_a)."			ELSE 9 END, ca_name, wr_subject ";
		}
		  $sql="select * from g5_write_product where wr_is_comment = 0 and LENGTH(wr_subject) >2 $ca_que $order_by ";
		$result = sql_query($sql);

		while ($row=sql_fetch_array($result)) {
			$selected = ($row[wr_id]==$sel_value)?"selected":"";
			$valueText="[".$row[ca_name]."] ".$row[wr_subject]."-".$row[wr_id];
			$str .= "<option value='".$row[wr_id]."' $selected >".$valueText."</option>";
		}
	}
	else if($gubun=="rec_product") {//추천 투어

		 $sql="select * from g5_write_product where wr_is_comment = 0 and LENGTH(wr_subject) >2   order by  ca_name, wr_subject ";
		$result = sql_query($sql);

		$pid_a=explode(",",$sel_value);

		while ($row=sql_fetch_array($result)) {
			$selected = (in_array( $row[wr_id], $pid_a) )?"selected":"";
			$valueText="[".$row[ca_name]."] ".$row[wr_subject]."-".$row[wr_id];
			$str .= "<option value='".$row[wr_id]."' $selected >".$valueText."</option>";
		}
	}
	else if($gubun=="wrtie_product") {//후기 다녀온 투어

//		$sql="select * from g5_write_product where wr_is_comment = 0 and LENGTH(wr_subject) >2 and ( ca_name='단체' or ca_name='단체차량' or ca_name='파리-워킹' or ca_name='파리-차량' )  order by  ca_name, wr_subject ";
//		$sql="select * from g5_write_product where wr_is_comment = 0 and LENGTH(wr_subject) >2 and ( ca_name LIKE '%세미패키지%' or ca_name LIKE '%데이투어%' or ca_name LIKE '%유럽%' or ca_name LIKE '%아프리카-중동%' or ca_name LIKE '%아시아%' or ca_name LIKE '%프리미엄일주%' or ca_name LIKE '%현대미술%' or ca_name LIKE '%트래킹%' or ca_name LIKE '%한국%' or ca_name LIKE '%제주도%' or ca_name LIKE '%이탈리아%' or ca_name LIKE '%스페인%' or ca_name LIKE '%이집트%' or ca_name LIKE '%일본%' ) AND NOT (ca_name LIKE '%숨김%') order by  ca_name, wr_subject ";
		$sql="select * from g5_write_product where wr_is_comment = 0 and LENGTH(wr_subject) >2 AND NOT (ca_name LIKE '%숨김%') order by  ca_name, wr_subject ";

//		echo $sql;

		$result = sql_query($sql);

		$pid_a=explode(",",$sel_value);

		while ($row=sql_fetch_array($result)) {
			$selected = (in_array( $row[wr_id], $pid_a) )?"selected":"";
			$valueText="[".$row[ca_name]."] ".$row[wr_subject];
			$str .= "<option value='".$row[wr_id]."' $selected >".$valueText."</option>";
		}
	}
	else if($gubun=="event_product") {// 이벤트 투어 선택

		 $sql="select * from g5_write_product where wr_is_comment = 0 and LENGTH(wr_subject) >2   order by  ca_name, wr_subject ";
		$result = sql_query($sql);

		$pid_a=explode(",",$sel_value);

		while ($row=sql_fetch_array($result)) {
			$selected = (in_array( $row[wr_id], $pid_a) )?"selected":"";
			$valueText="[".$row[ca_name]."] ".$row[wr_subject]."-".$row[wr_id];
			$str .= "<option value='".$row[wr_id]."' $selected >".$valueText."</option>";
		}
	}
	else if($gubun=="add_event_product") {// 예약시 이벤트 투어 선택

		 $sql="select * from g5_write_product where wr_is_comment = 0 and LENGTH(wr_subject) >2  ".str2qry("wr_id", $sel_value)."  order by  ca_name, wr_subject ";
		$result = sql_query($sql);

		//$pid_a=explode(",",$sel_value);

		while ($row=sql_fetch_array($result)) {
			$selected = (in_array( $row[wr_id], $sel_value) )?"selected":"";
			$valueText=$row[wr_subject];
			$str .= "<option value='".$row[wr_id]."' $selected >".$valueText."</option>";
		}
	}
	else if($gubun=="mb_guide") {//투어 선택
		$guide_a=explode(",",$sel_value);

		$result=sql_query("select * from g5_member where mb_level='4'  " );

		while ($row=sql_fetch_array($result)) {
			$selected = (in_array( $row[mb_no], $guide_a) )?"selected":"";
			$str .= "<option value='".$row[mb_no]."' $selected >".$guide_pre." - ".$row[mb_name]."</option>";
		}
	}
	else if($gubun=="guide_info") {//투어 선택
		$guide_a=explode(",",$sel_value);

		$result=sql_query("select * from g5_write_admGuideInfo order by wr_subject, wr_id  " );

		while ($row=sql_fetch_array($result)) {
			$selected = (in_array( $row[wr_id], $guide_a) )?"selected":"";
			$str .= "<option value='".$row[wr_id]."' $selected >[".$row[ca_name]."] ".$row[wr_subject]."</option>";
		}
	}
	else if($gubun=="__status") {//투어 선택
		${"selected_" . $sel_value} = "selected";
		$adminCancelDate=($opt[adminCancelDate]>0)?"-".Date("Y-m-d H:i:s",$opt[adminCancelDate]):"";
		$memCancelDate=($opt[memCancelDate]>0)?"-".Date("Y-m-d H:i:s",$opt[memCancelDate]):"";

		if($opt[adm_mb_id]) ${"adm_str_" . $sel_value} = " (".$opt[adm_mb_id]. " / ".substr($opt[adm_date],0,16).")";

		$str='<option value="1" '.$selected_1.'>예약대기 '.$adm_str_1.'
				<option value="11" '.$selected_11.'>오피스 예약확인 '.$adm_str_11.'
				<option value="2" '.$selected_2.'>예약확인(입금확인) '.$adm_str_2.'
				<option value="3" '.$selected_3.'>예약확정  '.$adm_str_3;
		if($row[mb_hp]) $str.='<option value="3S" >예약확정+SMS';
		$str.='<option value="9" '.$selected_9.' style="color:red">예약취소 '.$adm_str_9;
		if($row[mb_hp]) $str.='<option value="9S" >예약취소+SMS';
		$str.='<option value="99" '.$selected_99.' style="color:red">예약취소 환불완료 '.$adminCancelDate.' '.$adm_str_99.' 
				<option value="91" '.$selected_91.' style="color:red;">취소요청'.$memCancelDate.' '.$adm_str_91;
	}
	else if($gubun=="__postcode") {//픽업 우편번호 선택
		if($sel_value=="장거리") {
			$rs=sql_query("select * from v3_pickup_fee where gubun='장거리' order by ord, gubun, destination desc ");
			while ($row=sql_fetch_array($rs)) {
				$selected = ($row[mb_no]==$sel_value)?"selected":"";

				 $str .= "<option value='".$row[id]."' $selected >".$row['destination']."</option>";
			}
		}
		else {
			$postcode_a=explode(",",$sel_value);

			foreach($postcode_a as $k=>$v) {
				$selected = ($row[mb_no]==$sel_value)?"selected":"";

				$str .= "<option value='".$v."' $selected >".$v."</option>";
			}
		}
	}
	else if($gubun=="__cnt") {//인원 선택
		for ($i=1;$i<=$sel_value;$i++) {
			$str .= "<option value='".$i."' $selected >".$i."</option>";
		}
	}
	else if($gubun=="__na") {//인원 선택
		foreach($sel_na_a as $k => $v) {
			$str .= "<option value='".$v."' $selected >".$k."</option>";
		}
	}
	else if($gubun=="product_fee") {//투어요금 가져오기
		$pid=$sel_value;

		$rs=sql_query("select * from tour_fee where wr_id='$pid' order by id" );
		while ($row=sql_fetch_array($rs)) {
			if( is_numeric($row[fee3]) ) $fee3=number_format($row[fee3])."유로";
			else if( is_null ($row[fee3]) ) $fee3="불가능";
			else $fee3=$row[fee3];

			if( is_numeric($row[fee2]) && $row[fee2]>0 ) $fee2=number_format($row[fee2])."유로";
			else if( is_null ($row[fee2]) < 1) $fee2="없음";
			else $fee2=$row[fee2]."유로";

			$val=$row[id]."|".$row[fee_subject]."|".number_format($row[fee1])."|".$row[fee2];

			$str.='<option value="'.$val.'">'.$row[fee_subject].' / '.number_format($row[fee1]).'원 / '.$fee2.'</option>';
		}
	}
	else if($gubun=="fee_ticket") {//티켓요금 선택
		$pid=$sel_value;

		$rs=sql_query("select * from tour_fee_ticket order by id" );
		while ($row=sql_fetch_array($rs)) {
			$selected = ($row[id]==$sel_value)?"selected":"";

			$str.='<option value="'.$row[id].'" '.$selected.'>'.$row[fee_subject].' - '.number_format($row[fee]).'유로</option>';
		}
	}
	else if($gubun=="g5_faq_master") {//티켓요금 선택
		$pid=$sel_value;

		$rs=sql_query("select * from g5_faq_master group by fm_subject  order by fm_order" );
		while ($row=sql_fetch_array($rs)) {
			$selected = ($row[fm_id]==$sel_value)?"selected":"";

			$str.='<option value="'.$row['fm_id'].'" '.$selected.'>'.$row['fm_subject'].'</option>';
		}
	}
	else if($gubun=="항공사" || $gubun=="패키지상태" || $gubun=="패키지식사") {//
		$rs=sql_query("select * from v2_code where c_part='$gubun' order by c_ord" );
		while ($row=sql_fetch_array($rs)) {
			$selected = ($row[c_code]==$sel_value)?"selected":"";

			$str.='<option value="'.$row[c_code].'" '.$selected.'>'.$row[c_text].'</option>';
		}
	}


	return $str;
}

function get_tour_fee($pid, $dv="pc") {

	$p_row=sql_fetch("select * from g5_write_product where wr_id='$pid'");
	$str.='<table class="mgb20">
			<colgroup>
				<col width="46%" />
				<col width="18%" />
				<col width="18%" />
				 <col width="18%" /> 
			</colgroup>
			<tr>
				<th rowspan=2>신청구분</th>
				<th colspan=2>사전 예약시</th>
				<th rowspan=2>현지 지불시</th>
			</tr>
			<tr>
				<th >홈페이지 신청비용</th>
				<th >현장 지불</th>
			</tr>';
	//echo "select * from tour_fee where wr_id='$pid' order by id";
	$rs=sql_query("select * from tour_fee where wr_id='$pid' order by id" );
	While ($row=sql_fetch_array($rs)) {
		$fee2= ($row[fee2]>0)?"".number_format($row[fee2])."유로":"없음";
		$fee3=(is_numeric($row[fee3]))?number_format($row[fee3])."유로":$row[fee3];

		$str.='<tr>
			<td>'.$row[fee_subject].'</td>
			<td>'.number_format($row[fee1]).'원</td>
			<td>'.$fee2 .' </td>
			<td>'.$fee3 .' </td>
		</tr>';
	}
			/*							<tr >
				<td class="space-center"><?=$row[fee_subject]?></td>
				<td class="space-center"><?=number_format($row[fee1])?></td>
				<td class="space-center"><?=($row[fee2]>0)?number_format($feeData[row])."유로":"-";?></td>
				<td class="space-center"><?=(is_numeric($row[fee3]))?number_format($row[fee3])."유로":$feeData[fee3]?></td>
			</tr>*/

	$str.='</table>';
	$str.='<ul class="help-txt text-gray">';
	If(!($pid=="3"  || $pid=="18")) {
		$str.='<li>'.nl2br($p_row[wr_4]).'</li>';
	}
	If($p_row[ca_name]=="단체" || $p_row[ca_name]=="단체차량") {
				/*<br><?
				/$tourOptionData=sql_fetch("select * from g4_write_tourOption where wr_id = '99' order by wr_num");
				echo nl2br($tourOptionData[wr_content]) */
	}

	/*
	<ul class="help-txt text-gray">
		<li>※ 만 3세 이하 유아는 투어에 참가하실 수 없습니다.</li>
		<li>※ 만 4세이상 ~ 만 6세미만의 경우 투어 비 무료입니다. (단 유모차 반입은 불가능합니다.)</li>
		<li>※ 투어예약금 입금자 순으로 예약확정 되며 투어예약금 없이 투어 비용 전액을 현지에서 현장지불하길 원하신다면 별도의 연락을 주셔야 합니다.</li>
	</ul>'; */

	return $str;
}
/* 예약시 추가 티켓 구입이 필요한 상품이면 표시 */
function get_ticket_fee($ticket_rid_a, $dv="pc") { //
	global $config;

	$rate['exchange_rate']=get_exchange_rate();
	$str='<br>
		<div class="order-form">
			<h3>박물관 입장료 (투어요금 + 사전예약비) &nbsp; &nbsp; <small style="color: #e60012;">적용 환율 :'.number_format($rate['exchange_rate']).'원</small></h3>
			<table>
				<colgroup>
					<col width="*" />
					<col width="15%" />
					<col width="20%" />
					<col width="20%" />
				</colgroup>
				<tr>
					<th class="">연령 구분</th>
					<th class="tbl_bg01">인원</th>
					<th class="tbl_bg01">외화금액</th>					
					<th>원화금액</th>
				</tr>';
	$tk_rs = sql_query("select * from tour_fee_ticket  order by id" );

	//$batican_won = array();
	//$batican_euro = array();
	While ($tk_row=sql_fetch_array($tk_rs)) {

		 $ticket_fee_won = $tk_row[fee] * $rate['exchange_rate'];
		$str.='<tr>
			<td class="space-center" >'.$tk_row[fee_subject].'</td>
			<td class="space-center">1 인당</td>
			<td class="space-center">'.$tk_row[fee].' 유로
			<input type="hidden" name="ticket_euro" value="'.$tk_row[fee].'">
			</td>
			<td class="space-center" >'.number_format($ticket_fee_won).' 원
			<!-- <input type="hidden" name="ticket_won" class="ticket_won"  value="'.$ticket_fee_won.'"> -->
			</td>
		</tr>';
		/* 예약 rid 를 전달해서 티켓 비용 합계를 구해 온다 */
		foreach($ticket_rid_a as $k => $v) {
			if($v) {
				list($fee3, $fee4) =ticket_fee_calc($tk_row[id], $v, $ticket_fee_won);
				$totalFee3+=$fee3;
				$totalFee4+=$fee4;
			}
		}
	}
	$str.='<tr>
		<td class="space-center" colspan="2" style="border-top:2px solid #aaa">티켓 합계</td>
		<td class="space-center" style="border-top:2px solid #aaa">'.number_format($totalFee3).'유로<input type="hidden" name="totalFee3" id="totalFee3" value="'.$totalFee3.'" ></td>
		<td class="space-center" style="border-top:2px solid #aaa">'.number_format($totalFee4).'원<input type="hidden" name="totalFee4" id="totalFee4" value="'.$totalFee4.'" ></td>
		</tr>
		<tr>
		<th class="space-center" colspan="2">바티칸 입장료 + 투어요금</th>
		<th class="space-center" colspan="2"><label><input type="checkbox"  name="is_ticket" id="is_ticket" value="1" required> 구매합니다.</label> </th>
		</tr>
	</table>';

	if($dv=="mobile") $str.=stripslashes($config[cf_ticket_m]);
	else $str.=stripslashes($config[cf_ticket_pc]);
	/*<ul class="help-txt mgb15">
					<li>* 성인(만 26세이상) 또는 만 26세미만 국제학생증 미 소지자 21 유로</li>
					<li>* 만 26세미만 국제학생증 소지자 또는 만6세이상 ~ 만 18세미만 12 유로</li>
					<li>* 바티칸 입장권은 신청일 기준 환율 적용되어 투어비와 함께 결제금액에 반영 됩니다. 나의 예약관리 페이지에서 투어비와 바티칸 입장료 상세내역 확인 가능 합니다.</li>
					<li>* 바티칸 입장권은 사전예약으로 구매 진행되기에 투어 확정 받은 후 변경/취소/환불 불가능 합니다. 충분히 고민 후 선택 해주세요.</li>
				</ul>*/
	$str.='</div>	';

	return $str;
}
/* rid 로 티켓 비용 계산 */
function ticket_fee_calc($tk_id, $rid, $ticket_fee_won) {

		 $sql="select membCnt, fee_id from tour_reg where id='$rid' ";

		$reg_rs=sql_query($sql);
		while($reg_row=sql_fetch_array($reg_rs)) {

			$fee_id_a=explode("|",$reg_row[fee_id]);
			$membCnt_a=explode("|",$reg_row[membCnt]);

			foreach( $fee_id_a as $k => $v) {   //if($tk_id_a[$tk_ids]) {

				if($v && $membCnt_a[$k]>0 ) {
					//echo "select * from tour_fee where id='$v' ";
					$fee_row=sql_fetch("select * from tour_fee where id='$v' ");
					$tkfee_row=sql_fetch("select * from tour_fee_ticket where id='{$fee_row[fee_ticket_id]}' ");
					//echo "select * from tour_fee_ticket where id='{$fee_row[fee_ticket_id]}' ";

					if($tk_id==$fee_row[fee_ticket_id]) {
						 $mb_cnt= $membCnt_a[$k];
						 $totalFee3+=$tkfee_row[fee]*$mb_cnt;
						$totalFee4+=$ticket_fee_won*$mb_cnt;//환율 계산. 한화
					}
				}
			}
		}
	$arr[]=$totalFee3;
	$arr[]=$totalFee4;

	return $arr;
}


/* 예약 된 인원 리턴 */
function get_res_member_str($rid, $mode="") {
	$r_row=sql_fetch("select * from tour_reg where id='$rid' ");
	$row=sql_fetch("select * from g5_write_product where wr_id='$r_row[pid]'");

	$membCnt_a=explode("|",$r_row[membCnt]);
	$fee_a=explode("|",$r_row[fee_id]);
	$mCnt=0;
	$fee_str="";


	For($mci=0; $mci<count($membCnt_a);$mci++) {
		If($membCnt_a[$mci]>0) {
			$mCnt+=$membCnt_a[$mci];
			//echo $mci;
			$feeData=sql_fetch("select * from tour_fee where id='$fee_a[$mci]' order by id" );
			If($row[ca_name]=="단체" || $row[ca_name]=="단체차량" ) {
				if($mode=="bk") {//부킹에서 확인시
					$sb_a=explode("~", $feeData[fee_subject]);
					unset($age_a);
					$is_ISEC="";
					foreach($sb_a as $k => $v) {
						if($v)  {

							if(preg_match("/성인/",$v))   $is_adt="성인";
							else $is_adt="세";

							if(preg_match("/국제학생증 소지자/",$v))  $is_ISEC=' <span class="label label-success">국제 소지</span>';
							else if(preg_match("/국제학생증 미 소지자/",$v))  $is_ISEC=' <span class="label label-info">국제 미소지</span>';

							$age_a[] = preg_replace("/[^0-9]*/s", "", $v).$is_adt;

						}
					}

					$fee_str.=implode("~",$age_a). $is_ISEC." : ".$membCnt_a[$mci]."명<br>  ";

				}
				else {
				//echo $num = preg_replace("/[^0-9]*/s", "", $feeData[fee_subject]);
				$fee_str.=$feeData[fee_subject]." : ".$membCnt_a[$mci]."명<br>  ";
				}
			}
			else If($row[ca_name]=="패키지" ) {
				$fee_str.=$membCnt_a[$mci]."명<br>  ";
			}
			Else If($row[ca_name]=="차량") {
				$fee_str.=$feeData[fee_subject]." : 선택함<br>  ";
			}
			else {
				$fee_str.=$feeData[fee_subject]." : ".$membCnt_a[$mci]."명<br>  ";
			}
		}
	}

	if($row[pid]=="4") {
		$ttmp=sql_fetch("select id from tour_reg where (pid='1' or pid='9' or pid='10' or pid='32' or pid='45') and isEvent='Y' and parent_id='$row[id]' ");
		if($ttmp[id]) $fee_str="<진정한 이벤트2>".$fee_str;
	}
	if($row[isEvent]=="Y" && $row[parent_id] && $row[isFRevent]!="Y" ) $fee_str="<진정한 이벤트2>".$fee_str;
	else if($row[isFRevent]=="Y" && $row[parent_id] ) $fee_str="<화려한 이벤트>".$fee_str;

	$tttmp=sql_fetch("select id from tour_reg where   isCombo='Y' and parent_id='$row[id]' ");
	//echo "select id from tour_reg where   isCombo='Y' and parent_id='$row[id]'";
	if($tttmp[id]) $fee_str=$fee_str."<strong><콤보투어></strong>";


	 If($row[ca_name]=="단체" || $row[ca_name]=="단체차량") $fee_str='<strong>전체 : '.$mCnt.'명</strong><br>'.$fee_str;


	return $fee_str;
}
function get_res_member_cnt($membCnt) {
	$membCnt_a=explode("|",$membCnt);

	return array_sum($membCnt_a);
}
/* 예약 상태 버튼 리턴
*/
function get_res_status_btn($row, $mode="") {
	global $isMobile;

	If($row[status]=="1") $btn_str='<span class="btn-pack medium  block">예약대기</span>';
	Else If($row[status]=="2") $btn_str='<span class="btn-pack medium focus block">예약확인</span>';
	Else If($row[status]=="3") $btn_str='<span class="btn-pack medium orange block">예약확정</span>';
	Else If($row[status]=="9") $btn_str='<span class="btn-pack medium red block">예약취소</span>';
	Else If($row[status]=="91") $btn_str='<span class="btn-pack medium gray2 block">취소요청</span>';

	if($row[card_pay]) {
		$pay_row=sql_fetch("select * from kspay_result where ApplNum = '".$row[card_pay]."'   order by id desc limit 1");
		if($row['nation']!="패키지") {
			if (stripos($row['nation'], '세미패키지') !== false) {}
			else if($pay_row[CancelDate] ) $btn_str.='<span ><small>'.$pay_row[CancelDate].' 카드 취소</small></span>';
			else $btn_str.='<p class="mgb7"><input type="button" value="결제 완료" class="btn-pack medium green block">';
		}
	}
	if($row[status]=="2" && str_replace(",","",$row[total_fee1])>0) {
		if($row['nation']!="패키지") {
			if (stripos($row['nation'], '세미패키지') !== false) {}
			else if($isMobile)  $btn_str.='<input type="button" value="카드 결제" onclick="req_pay_m(\''.$row[id].'\');" class="btn-pack medium orange block" >';
			else $btn_str.='<input type="button" value="카드 결제" onclick="req_pay(\''.$row[id].'\');" class="btn-pack medium orange block" >';
		}
	}
	else if($row[status]=="3") {
		if($row['nation']=="패키지") { }
		else if (stripos($row['nation'], '세미패키지') !== false) {}
		else $btn_str.='<input type="button" value="바우처 보기" onclick="popup_page(\'/voucher.php?rid='.$row[id].'\',\'voucherWin\');"  class="btn-pack medium sky block" >';
	}

	return $btn_str;
	/* <p class="mgb7">입금대기</p>
										<p class="mgb7"><a href="#" class="btn-pack small green">결제하기</a></p>
										<p><a href="#" class="btn-pack small">취소</a></p>
										<span class="btn-pack medium gray2 block">결제완료</span>*/
}

function get_pkg_feein($rid , $fee_gubun,  $fee, $mode="") {
	global $isMobile;
	$row=sql_fetch("select * from tour_reg_pkg_fee where rid='$rid' and fee_gubun='$fee_gubun' ");

	if($fee_gubun=="fee1") {
	}
	else if($fee_gubun=="fee2") {
		$prev_row=sql_fetch("select * from tour_reg_pkg_fee where rid='$rid' and fee_gubun='fee1' ");
	}
	else if($fee_gubun=="fee_air") {
		$prev_row=sql_fetch("select * from tour_reg_pkg_fee where rid='$rid' and fee_gubun='fee2' ");
	}
	else if($fee_gubun=="fee3") {
		$prev_row=sql_fetch("select * from tour_reg_pkg_fee where rid='$rid' and fee_gubun='fee_air' ");
	}
	//echo "select * from tour_reg_pkg_fee where rid='$rid' and fee_gubun='fee_air' ";

	//echo "select * from tour_reg_pkg_fee where rid='$rid' and fee_gubun='$fee_gubun'";

	if(!$row['id']) { //없으면 빈 레코드 추가한다.
		sql_query("INSERT INTO `tour_reg_pkg_fee` (   `rid`,  `fee_gubun`) VALUES  (   '$rid',    '$fee_gubun' )");
	}
	if($mode=="adm") {//관리자

		if($row['pay_gubun']=="bank" && strtotime($row['in_date'])>111) {
			$fee_str='<span class="input-group-addon" style="width:80px; font-size:11px">입금일 : '.substr($row['in_date'],0,10).'</span>';
		}
		else  if($row['pay_gubun']=="PG") {

			if(strtotime($row[CancelDate])>111) $fee_str='<span class="input-group-addon" style="width:80px; padding:0px 5px ; color:red; font-size:11px"> '.$row['CancelDate'].' 취소 처리 ';
			else $fee_str='<span class="input-group-addon" style="width:80px; padding:0px  5px; font-size:11px ">'.$row['card_pay'] .' <span style="color:blue">'.$row['in_date'].' 승인</span> &nbsp; <input type="button" value="취소" class="btn btn-xs btn-danger" onclick="ksnet_cancel(\''.$row[card_pay].'\',\''.$fee.'\');">';

			$fee_str= $fee_str."</span>";
		}
		else $fee_str='<input type="text" name="'.$fee_gubun.'_date" class="form-control input-sm mgl5 selDate " value="" placeholder="입금일" style="width:100px"> ';


	}
	else 	if($mode=="fr") {//사용자 페이지
		if( $fee_gubun=="fee_air") $is_bank_only="1"; //은행 입금온리

		if($row['pay_gubun']=="bank" && strtotime($row['in_date'])>111) {
			$fee_str='<span class="input-group-addon" style="width:80px; font-size:12px">입금일 : '.substr($row['in_date'],0,10).'</span>';
		}
		else  if($row['pay_gubun']=="PG") {

			if(strtotime($row[CancelDate])>111) $fee_str='<span class="input-group-addon" style="width:150px; padding:0px 5px ; color:red; font-size:13px"> '.$row['CancelDate'].' 취소</span>';
			else $fee_str='<span class="input-group-addon" style="width:80px; padding:0px  5px; font-size:13px "> <span style="color:blue">'.$row['in_date'].' 승인</span>';//'.$row['card_pay'] .'

			$fee_str= $fee_str."</span>";
		}
		else {
			if($is_bank_only)  {
				if($isMobile) $fee_str=' &nbsp;<input type="button" value="계좌 이체" _onclick="req_pay_m(\''.$rid.'_'.$fee_gubun.'\');" class="btn-pack small focus2 " > ';
				else $fee_str=' &nbsp;<input type="button" value="계좌 이체" _onclick="req_pay_m(\''.$rid.'_'.$fee_gubun.'\');" class="btn-pack xsmall focus2 " > ';
			//$fee_str=' &nbsp;<span class="input-group-addon" style="width:150px; padding:0px 5px ; color:red; font-size:13px"> 카드결제 불가능 </span> ';
			}
			else if( ($prev_row['id'] && strtotime($prev_row['in_date']) > 111) || $fee_gubun=="fee1")  {
				if($isMobile) $fee_str=' &nbsp;<input type="button" value="카드 결제" onclick="req_pay_m(\''.$rid.'_'.$fee_gubun.'\');" class="btn-pack small orange " > ';
				else $fee_str=' &nbsp;<input type="button" value="카드 결제" onclick="req_pay(\''.$rid.'_'.$fee_gubun.'\');" class="btn-pack xsmall orange " > ';
			}
			else {
				 if($fee_gubun=="fee2") $fee_str="<small>예약금 결제후 가능</small>";
					else if($fee_gubun=="fee_air") $fee_str="<small>중도금 결제후 가능</small>";
					else if($fee_gubun=="fee3") $fee_str="<small>항공요금 결제후 가능</small>";
				/*if($isMobile) $fee_str=' &nbsp;<input type="button" value="카드 결제" onclick="alert(\''.$msg.'\')" class="btn-pack small _orange " > ';
				else $fee_str=' &nbsp;<input type="button" value="카드 결제" onclick="alert(\''.$msg.'\');" class="btn-pack xsmall _orange " > ';*/

			}
		}
	}

	return $fee_str;
}

function get_pkg_feein2($rid, $state, $fee_gubun, $fee, $mode="") {
	global $isMobile;
	$row=sql_fetch("select * from tour_reg_pkg_fee where rid='$rid' and fee_gubun='$fee_gubun' ");

	if($fee_gubun=="fee1") {
	}
	else if($fee_gubun=="fee2") {
		$prev_row=sql_fetch("select * from tour_reg_pkg_fee where rid='$rid' and fee_gubun='fee1' ");
	}
	else if($fee_gubun=="fee_air") {
		$prev_row=sql_fetch("select * from tour_reg_pkg_fee where rid='$rid' and fee_gubun='fee2' ");
	}
	else if($fee_gubun=="fee3") {
		$prev_row=sql_fetch("select * from tour_reg_pkg_fee where rid='$rid' and fee_gubun='fee_air' ");
	}
	//echo "select * from tour_reg_pkg_fee where rid='$rid' and fee_gubun='fee_air' ";

	//echo "select * from tour_reg_pkg_fee where rid='$rid' and fee_gubun='$fee_gubun'";

	if(!$row['id']) { //없으면 빈 레코드 추가한다.
		sql_query("INSERT INTO `tour_reg_pkg_fee` (   `rid`,  `fee_gubun`) VALUES  (   '$rid',    '$fee_gubun' )");
	}
	if($mode=="adm") {//관리자

		if($row['pay_gubun']=="bank" && strtotime($row['in_date'])>111) {
			$fee_str='<span class="input-group-addon" style="width:80px; font-size:11px">입금일 : '.substr($row['in_date'],0,10).'</span>';
		}
		else  if($row['pay_gubun']=="PG") {

			if(strtotime($row[CancelDate])>111) $fee_str='<span class="input-group-addon" style="width:80px; padding:0px 5px ; color:red; font-size:11px"> '.$row['CancelDate'].' 취소 처리 ';
			else $fee_str='<span class="input-group-addon" style="width:80px; padding:0px  5px; font-size:11px ">'.$row['card_pay'] .' <span style="color:blue">'.$row['in_date'].' 승인</span> &nbsp; <input type="button" value="취소" class="btn btn-xs btn-danger" onclick="ksnet_cancel(\''.$row[card_pay].'\',\''.$fee.'\');">';

			$fee_str= $fee_str."</span>";
		}
		else $fee_str='<input type="text" name="'.$fee_gubun.'_date" class="form-control input-sm mgl5 selDate " value="" placeholder="입금일" style="width:100px"> ';


	}
	else 	if($mode=="fr") {//사용자 페이지
		if( $fee_gubun=="fee_air") $is_bank_only="1"; //은행 입금온리

		if ($state == '9') {
			$fee_str="<small>취소된 예약입니다.</small>";
		}
		else if($row['pay_gubun']=="bank" && strtotime($row['in_date'])>111) {
			$fee_str='<span class="input-group-addon" style="width:80px; font-size:12px">입금일 : '.substr($row['in_date'],0,10).'</span>';
		}
		else  if($row['pay_gubun']=="PG") {

			if(strtotime($row[CancelDate])>111) $fee_str='<span class="input-group-addon" style="width:150px; padding:0px 5px ; color:red; font-size:13px"> '.$row['CancelDate'].' 취소</span>';
			else $fee_str='<span class="input-group-addon" style="width:80px; padding:0px  5px; font-size:13px "> <span style="color:blue">'.$row['in_date'].' 승인</span>';//'.$row['card_pay'] .'

			$fee_str= $fee_str."</span>";
		}
		else {
			if($is_bank_only)  {
				if($isMobile) $fee_str=' &nbsp;<input type="button" value="계좌 이체" _onclick="req_pay_m(\''.$rid.'_'.$fee_gubun.'\');" class="btn-pack small focus2 " > ';
				else $fee_str=' &nbsp;<input type="button" value="계좌 이체" _onclick="req_pay_m(\''.$rid.'_'.$fee_gubun.'\');" class="btn-pack xsmall focus2 " > ';
				//$fee_str=' &nbsp;<span class="input-group-addon" style="width:150px; padding:0px 5px ; color:red; font-size:13px"> 카드결제 불가능 </span> ';
			}
			else if( ($prev_row['id'] && strtotime($prev_row['in_date']) > 111) || $fee_gubun=="fee1")  {
				if ($state == '1' && $fee_gubun != "fee1") $fee_str="<small>예약 확인중입니다.</small>";
				else if($isMobile) $fee_str=' &nbsp;<input type="button" value="카드 결제" onclick="req_pay_m(\''.$rid.'_'.$fee_gubun.'\');" class="btn-pack small orange " > ';
				else $fee_str=' &nbsp;<input type="button" value="카드 결제" onclick="req_pay(\''.$rid.'_'.$fee_gubun.'\');" class="btn-pack xsmall orange " > ';
			}
			else {
				if($fee_gubun=="fee2") $fee_str="<small>예약금 결제후 가능</small>";
				else if($fee_gubun=="fee_air") $fee_str="<small>중도금 결제후 가능</small>";
				else if($fee_gubun=="fee3") $fee_str="<small>항공요금 결제후 가능</small>";
				/*if($isMobile) $fee_str=' &nbsp;<input type="button" value="카드 결제" onclick="alert(\''.$msg.'\')" class="btn-pack small _orange " > ';
				else $fee_str=' &nbsp;<input type="button" value="카드 결제" onclick="alert(\''.$msg.'\');" class="btn-pack xsmall _orange " > ';*/

			}
		}
	}

	return $fee_str;
}


function get_paging_bs($write_pages, $cur_page, $total_page, $url, $add="")
{
    $str = "";
    if ($cur_page > 1) {
        $str .= "<li><a href='" . $url . "1{$add}' aria-label=\"Previous\">처음</a></li>";
        //$str .= "[<a href='" . $url . ($cur_page-1) . "'>이전</a>]";
    }

    $start_page = ( ( (int)( ($cur_page - 1 ) / $write_pages ) ) * $write_pages ) + 1;
    $end_page = $start_page + $write_pages - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1) $str .= "<li><a href='" . $url . ($start_page-1) . "{$add}' aria-label=\"Previous\">이전</a></li>";

    if ($total_page > 1) {
        for ($k=$start_page;$k<=$end_page;$k++) {
            if ($cur_page != $k)
                $str .= "<li><a href='$url$k{$add}'>$k</a></li>";
            else
                $str .= "<li class=\"active\"><a href='#n'>$k</a></li>";
        }
    }

    if ($total_page > $end_page) $str .= "<li><a href='" . $url . ($end_page+1) . "{$add}' aria-label=\"Next\">다음</a></li>";

    if ($cur_page < $total_page) {
        //$str .= "[<a href='$url" . ($cur_page+1) . "'>다음</a>]";
        $str .= "<li><a href='$url$total_page{$add}' aria-label=\"Next\">맨끝</a></li>";
    }
    $str .= "";

    return $str;
}

/* 썸네일 처리 함수. 기존 그누보드에 추가 처리 */
function get_list_thumbnail_panda($bo_table, $wr_id,$img_w, $img_h,  $is_create, $is_crop, $crop_mode,$is_sharpen, $um_value, $bf_no="") {
	global $pn;


	include_once(G5_LIB_PATH.'/thumbnail.lib.php');

	$thumb = get_list_thumbnail($bo_table, $wr_id,$img_w, $img_h,  $is_create, $is_crop, $crop_mode,$is_sharpen, $um_value, $bf_no);

	if($thumb['src']) {
		$img_content = '<img src="'.$thumb['src'].'" alt="'.$thumb['alt'].'" >';
	} else {
		if($pn=="___webService")  $img_content="미등록";
		else {
			$img_content = '<div style="padding:0px 5px; text-align:center; width:'.$img_w.'px;height:'.$img_h.'px;line-height:'.$img_h.'px;display: table-cell;   vertical-align: middle;" >
			<img src="/images/common/logo.png" style="display: block;margin-left: auto;margin-right: auto;width:'.$img_w.'px;_height:'.$img_h.'px;">
		</div>';
		}
	}
	return $img_content;
}

function tour_info_disp($pid, $isEvent) {
}

function tour_info_disp_m($pid, $isEvent) {
}
function get_prod_msg($wr_id,$gubun) {
	$row=sql_fetch("select $gubun from g5_write_product where wr_id='$wr_id' " );

	return stripslashes(nl2br($row[$gubun]));

}
function get_prod_name($pid) {

	$pid_a=explode(",",trim($pid));
	if($pid_a) {
		foreach($pid_a as  $k=>$wr_id) {
			if($wr_id) {
			$row=sql_fetch("select * from g5_write_product where wr_id='$wr_id' " );

			$p_name_a[]="[".$row[ca_name]."] ".$row[wr_subject]."-".$wr_id;
			}
		}

	return implode("<br>", $p_name_a);
	}

}
/* 투어 국가 확인*/
function get_tour_na($na="", $sca="") {
	if($na) {
		if($na=="it" || $na=="단체" || $na=="단체차량") $str= '<img src="/images/sub/country_it2.jpg" alt=""> 이탈리아';
		else if($na=="fr"|| $na=="파리-워킹" || $na=="파리-차량") $str= '<img src="/images/sub/country_fr2.jpg" alt=""> 프랑스';
		else  if($na=="제주도") $str= ' 제주도';
	}
	else if($sca) {
		if($sca=="단체") $str= '단체 워킹';
		else if($sca=="단체차량") $str= '단체 차량';
		else if($sca=="제주도") $str= '제주도';
		else $str='전체 투어';

		if ($sca=="세미패키지") $str="세미패키지";
		else if ($sca=="유럽") $str="유럽 여행";
		else if ($sca=="아프리카-중동") $str="아프리카/중동 여행";
		else if ($sca=="아시아") $str="아시아 여행";
		else if ($sca=="프리미엄일주") $str="프리미엄일주";
		else if ($sca=="현대미술") $str="현대미술";
		else if ($sca=="트래킹") $str="트래킹";
		else if ($sca=="데이투어") $str="일일투어";
	}
	else $str='전체';

return $str;

}

/* 상품 리스트 가져오기 */
function product_list($na="it", $sca="단체", $pid="", $is_swiper="") {
	include_once(G5_LIB_PATH.'/thumbnail.lib.php');

	global $prod_sca_a;

	$img_width=360;
	$img_height=240;

	if($is_swiper) $swiper_class=' class="swiper-slide" '; //모바일 투어 상세 하단 추천투어는 슬라이드임. class 추가

	if($pid) {//추천 투어 가져오기
		$row=sql_fetch(" select recommend_tour from g5_write_product where wr_id='$pid' ");
		$where[]=str2qry("wr_id", $row[recommend_tour],"no");


	}


	if($sca) {
		if($sca=="단체") $where[]=" (ca_name = '단체' or ca_name = '파리-워킹' )";
		else if($sca=="제주도") $where[]=" (ca_name = '제주도' )";
		else if($sca=="단체차량") $where[]=" ( ca_name = '단체차량' or ca_name = '파리-차량' )";
	}

	if($sca!="제주도") {
		if($na) {
				if($na=="it") $where[]=" (ca_name = '단체' or ca_name = '단체차량'  )";
				else if($na=="fr") $where[]=" (ca_name = '파리-워킹' or ca_name = '파리-차량'  )";
		}
		else if($sca) {
			$where[]=" (ca_name = '$sca' or ca_name LIKE '%, $sca%' or ca_name LIKE '%$sca,%') ";
		}
		else $where[]=" (ca_name = '단체' or ca_name = '단체차량' or ca_name = '파리-워킹' or ca_name = '파리-차량' )";
	}

	if(count($where)) $sql_search=" where ". implode(" and ", $where);

//	echo "select * from g5_write_product $sql_search order by wr_8 ";

	$rs=sql_query("select * from g5_write_product $sql_search order by wr_8 ");
	for($i=0; $row=sql_fetch_array($rs); $i++) {
		$thumb = get_list_thumbnail("product",  $row['wr_id'], $img_width, $img_height,  $is_create, $is_crop, $crop_mode,$is_sharpen, $um_value, $bf_no, $is_watermark); //src 만 처리
		if($row[is_best_tour]) $is_best_icon='<div class="best"><img src="../images/sub/best_icon_'.$row[is_best_tour].'.png" alt=""></div>'; else $is_best_icon="";

		$fee_a= get_fee_disp($row['wr_id'], "arr");
		//print_r($fee_txt);
		$fee_txt="";
		if($fee_a[won]) $fee_txt.='<span>신청비용 <strong>'.$fee_a[won].'</strong></span>';
		if($fee_a[euro]) $fee_txt.='<span>현지지불금 <strong>'.$fee_a[euro].'</strong></span>';

		if($row[fee_org]) $fee_txt='<strike style="color:#999;font-weight:normal">'.stripslashes($row[fee_org]).'</strike><br>'.$fee_txt;

		if($na) $na_cd=$na;
		else if($sca=="한국") $na_cd="kr";
		else if($sca=="이탈리아") $na_cd="it";
		else if($sca=="스페인") $na_cd="sp";
		else if($sca=="이집트") $na_cd="eg";
		else if($sca=="일본") $na_cd="jp";
		else {
			if(preg_match("/파리/",$row[ca_name]) ) $na_cd="fr";
			else $na_cd="it";
		}

		$country_icon='<div class="country"><img src="/images/common/flag_'.$na_cd.'.png" alt="" style="width: 60px; height: 40px; border: 1px solid black;"></div>';
		if(preg_match("/투어중단/",$row[wr_subject])) $p_link="javascript:jalert('투어중단','선택하신 투어는 중단된 투어입니다.');void(0);";
		else $p_link='tour_view.php?pid='.$row[wr_id];

		$str.='<li '.$swiper_class .'>
					<a href="'.$p_link.'" class="item">
						'.$is_best_icon.'
						'.$country_icon.'
						<div class="thumb" style="background-image:url('.$thumb['src'].')"></div>
						<div class="cnt">
							<div class="icons">
								<span class="icon-1">1인 기준</span>
								<span class="icon-2">일정 확인</span>
							</div>
							<h3>'.stripslashes($row[wr_subject]).'</h3>
							<div class="price">
								'.$fee_txt.'
							</div>
						</div>
					</a>
				</li>	';
	}

	return $str;
}

function product_quick_view() {
	global $member;
	include_once(G5_LIB_PATH.'/thumbnail.lib.php');


	$img_width=88;
	$img_height=66;

	$where[]=" p.wr_id=h.pid";
	$where[]=" h.mb_id='{$member[mb_id]}'";


	if(count($where)) $sql_search=" where ". implode(" and ", $where);

	//echo "select * from g5_write_product as p, v2_tourview_history as h $sql_search order by view_date desc limit 10 ";

	$rs=sql_query("select * from g5_write_product as p, v2_tourview_history as h $sql_search group by h.pid order by view_date   desc limit 10 ");
	for($i=0; $row=sql_fetch_array($rs); $i++) {
		$thumb = get_list_thumbnail("product",  $row['wr_id'], $img_width, $img_height,  $is_create, $is_crop, $crop_mode,$is_sharpen, $um_value, $bf_no, $is_watermark); //src 만 처리

		$fee_a= get_fee_disp($row['wr_id'], "arr");

		$country_icon='<div class="country"><img src="../images/sub/country_'.$na.'2.jpg" alt=""></div>';

		$str.='<li>
				<a href="tour_view.php?pid='.$row[wr_id].'">
					<div class="thumb"><img src="'.$thumb['src'].'" alt="" width="88" height="66"></div>
					<div class="tit">'.stripslashes($row[wr_subject]).'</div>
					<div class="price">'.$fee_a[won].'</div>
				</a>
			</li>';
	}
	if($i==0) $str='<li>최근 본 투어가 없습니다.</li>';

	return $str;
}

function booking_mb_list($md, $row) {
	global $is_ticket;



	$feeID_a=explode("|",$row[fee_id]);
	$membCnt_a=explode("|",$row[membCnt]);

	//echo $row[ca_name];
	if($md=="cart" || $md=="booking") {
		foreach($feeID_a as $k => $feeID_a) {
			$mb_cnt=$membCnt_a[$k];
			if($feeID_a && $mb_cnt)  {
				unset($arr);
				//echo "select * from tour_fee where id='{$feeID_a}' order by id";
				$feeData=sql_fetch("select * from tour_fee where id='{$feeID_a}' order by id" );
				$fee1=number_format($feeData[fee1]);
				$arr[]=$fee1.'원';
				if( $md=="cart") {
					 $fee2= ($feeData[fee2]>0)?number_format($feeData[fee2])."유로":"없음";
					$fee3=(is_numeric($row[fee3]))?number_format($row[fee3])."유로":$row[fee3];

					$arr[]=$fee2;
				}

				if(preg_match("/국제학생증 소지자/",$feeData[fee_subject]))  $ISEC_cnt+=$membCnt_a[$k];


				$jan_arr=get_tour_jan_cnt($row[pid], $row[tourDay]);
				$mb_cnt_total=get_res_member_cnt($row[membCnt]);
			if($jan_arr[id] && ($jan_arr[ddCount]< $mb_cnt_total) ) {
				$str.='<tr>
					<td class="opt">- '.$feeData[fee_subject].'</td>
					<td class="opt">'.$membCnt_a[$k].'명</td>
					<td class="opt"><span class="text-red">예약 불가능</span></td>
				</tr>';
			}
			else {
				$str.='<tr>
					<td class="opt">- '.$feeData[fee_subject].'</td>
					<td class="opt">'.$membCnt_a[$k].'명</td>
					<td class="opt">'.implode(" / ", $arr).'</td>
				</tr>';
			}

				/*if( $is_ticket) { /// 티켓 구입 필수 투어인 경우 . 기능 변경 19-03-17
					$tk_row=sql_fetch ("select * from tour_fee_ticket where fee_subject='{$feeData[fee_subject]}'   order by id" );
					$tk_id=$tk_row[id];
					if($tk_row[id]) $tk_id_a[$tk_id]=$membCnt_a[$k];
				}
				*/

				 $mb_cnt_no+=$membCnt_a[$k];
			}
		}
	}
	if($md=="res") {

		foreach($feeID_a as $k => $feeID_a) {
			$mb_cnt=$membCnt_a[$k];
			if($feeID_a && $mb_cnt)  {
				unset($arr);
				//echo "select * from tour_fee where id='{$feeID_a}' order by id";
				$feeData=sql_fetch("select * from tour_fee where id='{$feeID_a}' order by id" );
				//$fee1=number_format($feeData[fee1]);
				//$arr[]=$fee1.'원';
				/*if( $md=="cart") {
					$fee2= ($row[fee2]>0)?"".number_format($feeData[row])."유로":"불가";
					$fee3=(is_numeric($row[fee3]))?number_format($row[fee3])."유로":$row[fee3];

					$arr[]=$fee2;
				}*/
				/* 2019-04-26 마이페이지 예약 내역에서는 상품의 예약 금액을 보여주지 않는다.
				if($feeData[fee1]) {
					$arr[]="예약금 : ".number_format(del_comma($feeData[fee1]*$membCnt_a[$k])); //예약금
				}
				else $arr[]="예약금 : 0 "; //예약금*/



				/*if($feeData[fee2]>0 ) {
					if(trim($feeData[fee2])=="-" || !$feeData[fee2]) $feeData[fee2]=0;
					$arr[]="현장지불잔금 : ".$feeData[fee2]."유로"; //잔금
				}*/
				//if($row[total_fee3]) $arr[]=$row[total_fee3]; //입장권비용 유로
				//if($row[total_fee4]) $arr[]="입장권 : ".number_format(del_comma($row[total_fee4])); //입장권 한화

				//if(preg_match("/국제학생증 소지자/",$feeData[fee_subject]))  $ISEC_cnt+=$membCnt_a[$k];
				if($row[isEvent]) {
					unset($arr);
					$arr[]="이벤트투어";
				}

				if($row[status]=="cart" || $row[status]=="booking" ) {
					$str.='<tr>
					<td class="opt">- '.$feeData[fee_subject].'</td>
					<td class="opt">'.$membCnt_a[$k].'명</td>
					<td class="opt"><span class="text-red">예약 불가능</span></td>
				</tr>';
				}
				else {
					$str.='<tr>
					<td class="opt">- '.$feeData[fee_subject].'</td>
					<td class="opt">'.$membCnt_a[$k].'명</td>
					<td class="opt">'.implode("<br>", $arr).'</td>
				</tr>';
				}

				$mb_cnt_no+=$membCnt_a[$k];

				/*if( $is_ticket) { // 티켓 구입 필수 투어인 경우
					$tk_row=sql_fetch ("select * from tour_fee_ticket where fee_subject='{$feeData[fee_subject]}'   order by id" );
					if($tk_row[id]) $tk_id.=$tk_row[id].",";
				}*/
			}
		}
	}
	$arr=array($str, $ISEC_cnt, $mb_cnt_no);

	return $arr;

	/* old 버전
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
						else {

							If($membCnt_a[$mci]>0) {
								$mCnt+=$membCnt_a[$mci];
								$feeData=sql_fetch("select * from tour_fee where id='$fee_a[$mci]' order by id" );
								$feeTxt.=$feeData[fee_subject]." : ".$membCnt_a[$mci]."명<br>  ";
							}
						}
					}*/
}
/* 사용하지 않음. cart 의 booking_mb_list 사용 */
function __get_tour_fee_booking($pid, $fee_id, $mb_cnt,  $dv="pc") {

	$mb_cnt_a=explode("|",$mb_cnt);
	$fee_id_a=explode("|",$fee_id);

	//$where[]= str2qry("id", str_replace("|",",",$fee_id), "");
	//if(count($where)) $sql_search=" where  wr_id='$pid'  ". implode(" and ", $where);

	//echo $sql="select * from tour_fee $sql_search";
	$rs=sql_query($sql);
	foreach ($mb_cnt_a as $k=>$v) {
		if($v) {
			$row=sql_fetch(" select * from tour_fee where  id='{$fee_id_a[$k]}' ");

			$fee2= ($row[fee2]>0)?"".number_format($row[fee2])."유로":"없음";
			$fee3=(is_numeric($row[fee3]))?number_format($row[fee3])."유로":$row[fee3];

			$str.='<tr>
				<td class="opt">'.$row[fee_subject].'</td>
				<td class="opt">'.number_format($mb_cnt_a[$k]).'명</td>
				<td class="opt">'.number_format($row[fee1]).'원/'.$fee2 .'</td>
			</tr>';
		}
		/*
		<tr>
													<td class="opt">- 만 6세 이하(유모차 반입 금지) / 0원 / 10유로</td>
													<td class="opt">2명</td>
													<td class="opt">60,000원 / 0유로</td>
												</tr>
												*/
	}

	return $str;
}

/* 내가 신청한 메모 가져오기 */
function myMemo($rid, $isMobile ) {

	$row=sql_fetch("select * from tour_reg where id='$rid' ");

	if($row[regMemo]) {
		$regMemo=stripslashes(nl2br($row[regMemo]));
		if($isMobile)  $str_a[]='<tr><th>신청시 메모</th></tr><tr><td > '.$regMemo.'</td></tr>';
		else $str_a[]='<tr><th>신청시 메모</th><td style="padding:10px;"> '.$regMemo.'</td></tr>';
	}
	if($row[ISECMemo]) {
		$ISEC_a=explode("|",$row[ISECMemo]);
		foreach($ISEC_a as $k=>$v) {

			if($v) $ISEC_str.=$v."<br>";
		}
		if($isMobile) $str_a[]='<tr><th>국제학생증</th></tr><tr><td> '.$ISEC_str.'</td></tr>';
		else $str_a[]='<tr><th>국제학생증</th><td style="padding:10px;"> '.$ISEC_str.'</td></tr>';
	}
	if($row[mb_passport_info]) {
		$pass_a=explode("|",$row[mb_passport_info]);

						foreach($pass_a as $k => $v) {
							if($v) $mb_passport_info.=  $v."<br>";
						}
		if($isMobile)  $str_a[]='<tr><th>여권정보</th></tr><tr><td > '.$rid.stripslashes($mb_passport_info).'</td></tr>';
		else $str_a[]='<tr><th>여권정보</th><td style="padding:10px;"> '.stripslashes($mb_passport_info).'</td></tr>';

	}
	if($row[addr1]) {
		$zip=stripslashes(nl2br($row[zip]));
		$addr1=stripslashes(nl2br($row[addr1]));
		$addr2=stripslashes(nl2br($row[addr2]));
		if($isMobile)  $str_a[]='<tr><th>배송지</th></tr><tr><td > '.$zip.' '.$addr1.' '.$addr2.'</td></tr>';
		else $str_a[]='<tr><th>배송지</th><td style="padding:10px;">  '.$zip.' '.$addr1.' '.$addr2.'</td></tr>';
	}
	if($row[gift]) {
		$gift=stripslashes(nl2br($row[gift]));
		if($isMobile)  $str_a[]='<tr><th>사은품</th></tr><tr><td > '.$gift.'</td></tr>';
		else $str_a[]='<tr><th>사은품</th><td style="padding:10px;">  '.$gift.'</td></tr>';
	}
	if($row[roominfo]) {
		$roominfo=stripslashes(nl2br($row[roominfo]));
		if($isMobile)  $str_a[]='<tr><th>룸타입</th></tr><tr><td > '.$roominfo.'</td></tr>';
		else $str_a[]='<tr><th>룸타입</th><td style="padding:10px;">  '.$roominfo.'</td></tr>';
	}

	echo '<table class="_table">'.print_r($str_a).implode("",$str_a).'</table>';
}

/*******************
투어 상품명 카테고리와 제목 가져오기
**********************/
function get_product_subject($pid, $event_pid="") {
	$pData=sql_fetch("select wr_subject,ca_name from g5_write_product where wr_id='$pid' ");

	if($event_pid) {
		$row_e=sql_fetch("select * from g5_write_product where wr_id='".$event_pid."'");
		$str='<strong>['.$row_e[ca_name].']</strong>'. $row_e['wr_subject'];

	}
	else {
		$str= "<strong>[".$pData[ca_name]."]</strong> ".$pData[wr_subject];
	}
	return $str;

}
function get_product_row($pid) {
	$pData=sql_fetch("select * from g5_write_product where wr_id='$pid' ");

	return $pData;
}
function get_product_nation($pid) {
	$pData=sql_fetch("select * from g5_write_product where wr_id='$pid' ");

	return $pData[ca_name];
}
/* 예약내역에서 투어명 가져오기 */
function get_product_name_by_booking($rid, $md="") {
	$r_row=sql_fetch("select * from tour_reg where id='$rid'  ");
	if($r_row[event_pid]) 	$row=sql_fetch("select * from  g5_write_product  where wr_id ='{$r_row[event_pid]}'  ");
	else 	$row=sql_fetch("select * from  g5_write_product  where wr_id ='{$r_row[pid]}'  ");


	if($md=="tourDay") {
		if($row[wr_subject]) {
			$arr[]=$row[wr_subject];
			$arr[]=$r_row[tourDay];
		}

		return implode(" / ", $arr);
	}
	else return $row[wr_subject];
}
/* 이벤트 투어 반환*/
function booking_event_tour($row) {
	// 이벤트 투어가 있는지 확인해서
	if($row[wr_event_option]=="2" && $row[wr_event_course] ) {
		$event_pid_str=str_replace("|",",",$row[wr_event_course]);
		$event_pid_a=explode(",", $event_pid_str); //이전 버전의 | 구분자를 컴마로 변환.

		if($row[carlendar_max_m]>3) $endDate=$row[carlendar_max_m]; else $endDate="3";//달력 표시할 최장 개월수
		if(count($event_pid_a)) {
			$str.='<tr>
				<td  class="tit2" style="padding-top:5px;">
					<select name="addProd['.$row[id].']" class="addProd select" style="width:80%" required data-endmonth="'.$endDate.'">
						<option value="" selected style="_color:#00a1e9">무료투어를 선택해 주세요
						<option value="X">선택하지 않습니다.
						'.get_select_list("add_event_product", $event_pid_str).'
					</select>
				</td>
				<td class="" colspan=2 style="padding-top:5px;">
					<input type="text" name="addTourDay['.$row[id].']" class="input tourDay" data-pid="'.$event_pid.'" data-endDate="'.$endDate.'"  placeholder="투어일 선택" onkeyup="removeChar(event)" style="display:none;width:110px; "  required data-tourday="'.$row[tourDay].'"> 
				</td>
			</tr>';
		}
		/*
		foreach($event_pid_a as $k => $event_pid) {
			if($event_pid) {
				$eRow=sql_fetch("select * from g5_write_product where wr_id='$event_pid' ");
				$str.='<tr>
						<td  class="tit2"><a href="tour_view.php?pid='.$event_pid.'" target="_blank">'.stripslashes($eRow[wr_subject]).' </a></td>
						<td class="" colspan=2 style="font-size:14px"><input type="text" name="" class="input tourDay" data-pid="'.$event_pid.'" placeholder="투어일 선택" style="width:100px; display:inline-block">
						<label class="mgl10" style="display:inline-block"><input type="checkbox" name="">선택하지 않음</labe></td>
				</tr>';
			}
		}*/
	}

	//return '<tr><td colspan="auto" style="padding-top:5px;color:#00a1e9">무료 투어 선택</td></tr>'.$str;
	return $str;
}

/* 예약시 product 디폴트 status를 가져와 리턴*/
function get_booking_status($pid) {
	global $is_b2bmem;

	$row=sql_fetch(" select * from g5_write_product where wr_id='$pid'  ");

	/* b2b 예약시 상태값 */
	if ($is_b2bmem=="Y") {
		$tourStatus=$row[wr_b2b_result];
	}
	else if ( $row[wr_reg_result] ) { //일반회원 예약시 예약 상태 기본값
		$tourStatus=$row[wr_reg_result];
	}
	else $tourStatus="1";//기본 예약 상태

	return $tourStatus;

}

/* faq 목록 */
function faq_list( $pid="", $fm_id="") {
	global $g5;

	$sql_common = " from {$g5['faq_table']}  ";

	if($pid) $where[]=" ( FIND_IN_SET($pid,pid) )  "; //pid='' or
	//if($fm_id) $where[]=" fm_id='$fm_id'  ";

	if(count($where)) $sql_common.=" where " . implode(" and ", $where);//fm_id = '$fm_id'

	// 테이블의 전체 레코드수만 얻음
	$sql = " select count(*) as cnt " . $sql_common ;
	$row = sql_fetch($sql);
	$total_count = $row['cnt'];

	  $sql = "select * $sql_common order by fa_order , fa_id ";
	$result = sql_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $faq_list.='<dt>'.str_replace("<div><br></div>","",str_replace("<p><br></p>","",stripslashes($row['fa_subject']))).'</dt>
					<dd>'.stripslashes($row['fa_content']).' </dd>';
    }

	return "<dl>".$faq_list."</dl>";

}
/* 투어 상세 가이드 소개 표시 */
function get_guide_info($guide_str, $dv="pc") {
	include_once(G5_LIB_PATH.'/thumbnail.lib.php');

	$que=str2qry("wr_id", $guide_str,"no");

		$result=sql_query("select * from g5_write_admGuideInfo where ".$que." order by wr_subject, wr_id  " );
		$img_width=$img_height=215;

		while ($row=sql_fetch_array($result)) {
			$thumb = get_list_thumbnail("admGuideInfo",  $row['wr_id'], $img_width, $img_height,  $is_create, $is_crop, $crop_mode,$is_sharpen, $um_value, $bf_no, $is_watermark); //src 만 처리

			$info='<dl>
						<dt><span class="name">'.stripslashes($row[wr_subject]).'</span> 가이드</dt>
						<dd>'.nl2br(stripslashes($row[wr_content])).'</dd>
					</dl>';
			if($dv=="pc") {
				//$img_content=get_list_thumbnail_panda("admGuideInfo", $row['wr_id'], $img_width, $img_height,  $is_create, $is_crop, $crop_mode,$is_sharpen, $um_value, $bf_no); //이미지. img tag까지
				$str.='<li>
							<div class="box"><img src="'.$thumb['src'].'" class="pic" alt="">'.$info.'</div>
						</li>';
			}
			else if($dv=="mobile") {

				$str.='<li>
						<div class="box">
							<div class="pic" style="background-image:url('.$thumb['src'].')"></div>
							'.$info.'
						</div>
					</li>';
			}
		}

		return $str;



}

function sns_list($dv="pc") {
	global $config;

	if($dv=="pc") {
		$li1="<li>";
		$li2="</li>";
	}

//	if(trim($config[cf_10])) $sns_a[]=$li1.'<a href="'.stripslashes(trim($config[cf_10])).'" target="_blank" title="새창열림"><img src="../images/common/sns_f.png" alt="facebook"></a>'.$li2;
//	if(trim($config[cf_9])) $sns_a[]=$li1.'<a href="'.stripslashes(trim($config[cf_9])).'" target="_blank" title="새창열림"><img src="../images/common/sns_y.png" alt="youtube"></a>'.$li2;
//	if(trim($config[cf_8])) $sns_a[]=$li1.'<a href="'.stripslashes(trim($config[cf_8])).'" target="_blank" title="새창열림"><img src="../images/common/sns_b.png" alt="blog"></a>'.$li2;
//	if(trim($config[cf_7])) $sns_a[]=$li1.'<a href="'.stripslashes(trim($config[cf_7])).'" target="_blank" title="새창열림"><img src="../images/common/sns_k.png" alt="kakao"></a>'.$li2;
//	if(trim($config[cf_6])) $sns_a[]=$li1.'<a href="'.stripslashes(trim($config[cf_6])).'" target="_blank" title="새창열림"><img src="../images/common/sns_i.png" alt="instagram"></a>'.$li2;

	$sql = " SELECT * FROM `g5_board_file` WHERE bo_table = 'sns' ";
	$result = sql_query($sql);

	for ($i=0; $row=sql_fetch_array($result); $i++) {
		$icon[] = $row;
	}

	$facebook = array_filter($icon, function($item) {
		return isset($item['bf_no']) && $item['bf_no'] == 0;
	});

//	var_dump($facebook);

	$youtube = array_filter($icon, function($item) {
		return isset($item['bf_no']) && $item['bf_no'] == 1;
	});

//	var_dump($youtube);

	$blog = array_filter($icon, function($item) {
		return isset($item['bf_no']) && $item['bf_no'] == 2;
	});

//	var_dump($blog);

	$kakao = array_filter($icon, function($item) {
		return isset($item['bf_no']) && $item['bf_no'] == 3;
	});

//	var_dump($kakao);

	$instagram = array_filter($icon, function($item) {
		return isset($item['bf_no']) && $item['bf_no'] == 4;
	});

//	var_dump($instagram);

	$list = array(
		[
			title => 'facebook',
			order => $config[cf_10_subj],
			link => $config[cf_10],
			icon => $facebook[0]['bf_source'] ? '/bbs/data/file/sns/' . $facebook[0]['bf_file'] : '../images/common/sns_f.png',
		],
		[
			title => 'youtube',
			order => $config[cf_9_subj],
			link => $config[cf_9],
//			icon => '../images/common/sns_y.png',
			icon => $youtube[1]['bf_source'] ? '/bbs/data/file/sns/' . $youtube[1]['bf_file'] : '../images/common/sns_y.png',
		],
		[
			title => 'blog',
			order => $config[cf_8_subj],
			link => $config[cf_8],
//			icon => '../images/common/sns_b.png',
			icon => $blog[2]['bf_source'] ? '/bbs/data/file/sns/' . $blog[2]['bf_file'] : '../images/common/sns_b.png',
		],
		[
			title => 'kakao',
			order => $config[cf_7_subj],
			link => $config[cf_7],
			icon => $kakao[3]['bf_source'] ? '/bbs/data/file/sns/' . $kakao[3]['bf_file'] : '../images/common/sns_k.png',
		],
		[
			title => 'instagram',
			order => $config[cf_6_subj],
			link => $config[cf_6],
//			icon => '../images/common/sns_i.png',
			icon => $instagram[4]['bf_source'] ? '/bbs/data/file/sns/' . $instagram[4]['bf_file'] : '../images/common/sns_i.png',
		],
	);

	// order 값을 기준으로 오름차순으로 정렬
	usort($list, function ($a, $b) {
		return $a['order'] - $b['order'];
	});

	for ($i = 0; $i < count($list); $i++) {
		if(trim($list[$i]['link']))
			$sns_a[]=$li1.'<a href="'.stripslashes(trim($list[$i]['link'])).'" target="_blank" title="새창열림"><img style="max-width: 63px; max-height: 63px;" src="' . trim($list[$i]['icon']) . '" alt="' . trim($list[$i]['title']) . '"></a>'.$li2;
	}

	return implode("",$sns_a);

	/*	<a href="#" target="_blank" title="새창열림"><img src="../images/common/sns_f.png" alt="facebook"></a>
				<a href="#" target="_blank" title="새창열림"><img src="../images/common/sns_y.png" alt="youtube"></a>
				<a href="http://blog.naver.com/ysb0301" target="_blank" title="새창열림"><img src="../images/common/sns_b.png" alt="blog"></a>
				<a href="#" target="_blank" title="새창열림"><img src="../images/common/sns_k.png" alt="kakao"></a>
				<a href="#" target="_blank" title="새창열림"><img src="../images/common/sns_i.png" alt="instagram"></a>

			<li><a href="#" target="_blank" title="새창열림"><img src="../images/common/sns_f.png" alt="facebook"></a></li>
					<li><a href="#" target="_blank" title="새창열림"><img src="../images/common/sns_y.png" alt="youtube"></a></li>
					<li><a href="http://blog.naver.com/ysb0301" target="_blank" title="새창열림"><img src="../images/common/sns_b.png" alt="blog"></a></li>
					<li><a href="#" target="_blank" title="새창열림"><img src="../images/common/sns_k.png" alt="kakao"></a></li>
					<li><a href="#" target="_blank" title="새창열림"><img src="../images/common/sns_i.png" alt="instagram"></a></li>*/
}
/******************
콤마로 구분된 문자열 sql 문으로 변환
******************/
function str2qry($fld, $str, $pre="" ) {
	if(trim($str)) {
		$str_a=explode(",",trim($str));
		if($pre=="in") { //IN 문으로 처리
			foreach($str_a as $v) {
				if($v) $qry_a[]= " '".$v."' ";
			}
			$qry =" $fld IN (".implode(",",$qry_a).")";
		}

		else {
			//print_r($str_a);// count($str_a);
			if(count($str_a)>0) {
				foreach($str_a as $v) {
					if($v) $qry_a[]= " ".$fld." = '".$v."' ";
				}
				if($pre=="no") $qry="  (  ".implode("or",$qry_a)." ) ";
				else if($pre=="not") $qry=" and NOT( ".implode("or",$qry_a)." ) ";
				else $qry=" and (  ".implode("or",$qry_a)." ) ";
			}
		}
	}

	return $qry;
}

/* 메인 슬라이드및 중간 배너 가져오기 */
function get_main_img($bo_table, $dv="pc") {
	if($dv=="pc") $wr_id=1;
	else if($dv=="mobile") $wr_id=2;

	$file=get_file($bo_table, $wr_id);
	//print_r($file);

	if($bo_table=="v2_main_slide" && $dv=="pc") {
		for ($i=0;  $i<$file["count"]; $i++) {
			if($file[$i]['file']) {
				if($file[$i]['content']) {
					$style="cursor:pointer";
					if(substr($file[$i]['content'],0,4)=="http") $bnr_link=' onclick="window.open(\''.trim($file[$i]['content']).'\')" ' ;
					else $bnr_link=' onclick="location.href=\''.trim($file[$i]['content']).'\'" ' ;
				}
				else {
					$style="";
					$bnr_link="";
				}
				$str.='<div class="slide" style="background-image:url(/bbs/data/file/'.$bo_table.'/'.$file[$i]['file'].'); '.$style.' " '.$bnr_link.'></div>';
			}
		}
	}
	else if($bo_table=="v2_main_slide" && $dv=="mobile" ) {
		for ($i=0;  $i<$file["count"]; $i++) {
			if($file[$i]['file']) {
				if($file[$i]['content']) {
					$style="cursor:pointer";
					if(substr($file[$i]['content'],0,4)=="http") $bnr_link=' onclick="window.open(\''.trim($file[$i]['content']).'\')" ' ;
					else $bnr_link=' onclick="location.href=\''.trim($file[$i]['content']).'\'" ' ;
				}
				else {
					$style="";
					$bnr_link="";
				}
				$str.='<div class="swiper-slide" style="background-image:url(/bbs/data/file/'.$bo_table.'/'.$file[$i]['file'].'); '.$style.' " '.$bnr_link.'></div>';
			}
		}
	}
	else if($bo_table=="v2_top_bnr" && $dv=="pc" ) {
		for ($i=0;  $i<$file["count"]; $i++) {
			$bnr_link=$file[$i]['content']?trim($file[$i]['content']):"#";
			if($file[$i]['file']) $str.='<li style="background-color:#a6dfe0"><a href="'.$bnr_link.'"><img src="/bbs/data/file/'.$bo_table.'/'.$file[$i]['file'].'"></a></li>';
		}
	}
	else if($bo_table=="v2_top_bnr" && $dv=="mobile" ) {
		for ($i=0;  $i<$file["count"]; $i++) {
			$bnr_link=$file[$i]['content']?trim($file[$i]['content']):"#";
			if($file[$i]['file']) $str.='<li class="swiper-slide" style="background-color:#a6dfe0"><a href="'.$bnr_link.'"><img src="/bbs/data/file/'.$bo_table.'/'.$file[$i]['file'].'"></a></li>';
		}
	}

	return $str;
}

function tour_view_history_insert($pid, $dv="pc") {
	global $member;

	if($member[mb_id]) {
		sql_query("INSERT INTO `v2_tourview_history` ( `pid`, `mb_id`, `view_date`, `device`) VALUES  ( '$pid', '{$member[mb_id]}', '".G5_TIME_YMDHIS."', '$dv' )");
	}
}

function re_cal_max_counter($pid,$tourdate) {
	$row0=sql_fetch("select * from g5_write_product  where wr_id='$pid' order by wr_id ");
	$result=sql_query("select * from `tour_reg`  where pid='$pid' and tourDay ='$tourdate' and (status='3') order by regDate");
	if(mysql_num_rows($result) >1)  {

		$total_memb=0;

		for ($regi=0; $row=sql_fetch_array($result); $regi++) {

			$membCnt_a=explode("|",$row[membCnt]);
			If($row0[ca_name]=="단체" || $row0[ca_name]=="단체차량") {
				$mCnt=0;
				For($mci=0; $mci<count($membCnt_a);$mci++) $mCnt+=$membCnt_a[$mci];

				$total_memb+=$mCnt;
			}
			Else If($row0[ca_name]=="차량") {
				if($total_memb<1) $total_memb=1;
				$total_memb++;

			}
		}
	}
	$row_cnt=sql_fetch("select id, maxCount from tour_reg_count where pid='$pid' and tourDate='$tourdate' "); //max counter 확인하고. 없으면 신규 추가
	if($row_cnt[id])	sql_query("update tour_reg_count set nowCount='$total_memb' where id='$row_cnt[id]' ");
	//echo "update tour_reg_count set nowCount='$total_memb' where id='$row_cnt[id]'";

}
/* 마감 임박일, 마감일 가져오기 */
function get_tour_reg_count($pid, $opt="", $tourday="") {
	$today=date("Y-m-d",(time() -(86400*0)));

	if($tourday) $cnt_rs=sql_query("SELECT *,  (maxCount-nowCount) AS ddCount FROM tour_reg_count WHERE pid='$pid' AND tourDate ='$today' AND maxCount>0 AND (maxCount-nowCount)<=5 ORDER BY tourDate" );
	else $cnt_rs=sql_query("SELECT *,  (maxCount-nowCount) AS ddCount FROM tour_reg_count WHERE pid='$pid' AND tourDate >='$today' AND maxCount>0 AND (maxCount-nowCount)<=5 ORDER BY tourDate" );
	if($opt=="null") { //홑 따옴표 없이 날자 리턴
		while($cnt_row=sql_fetch_array($cnt_rs)) {
			if($cnt_row[ddCount]>0) $dd_arr[]=$cnt_row[tourDate]; //deadline 마감임박 배열
			else if($cnt_row[ddCount]<1) $close_arr[]=$cnt_row[tourDate]; //deadline 마감 배열
		}
	}
	else {//tour_view에서 호출하는 따옴표 있는 것.
		while($cnt_row=sql_fetch_array($cnt_rs)) {
			if($cnt_row[ddCount]>0) $dd_arr[]="'".$cnt_row[tourDate]."'"; //deadline 마감임박 배열
			else if($cnt_row[ddCount]<1) $close_arr[]="'".$cnt_row[tourDate]."'"; //deadline 마감 배열
		}
	}
	//echo "SELECT * FROM tour_closed_2 WHERE pid='$pid'  AND closedDate = '$today' AND isClose='E' ";
	$close_rs=sql_query("SELECT * FROM tour_closed_2 WHERE pid='$pid'  AND closedDate >= '$today' AND isClose='E' ");//E 마감
	if($opt=="null") { //홑 따옴표 없이 날자 리턴
		while($cRow=sql_fetch_array($close_rs)) {
			$close_arr[] = $cRow[closedDate];
		}
	}
	else {
		while($cRow=sql_fetch_array($close_rs)) {
			$close_arr[] = "'".$cRow[closedDate]."'";
		}
	}

	$arr[]=$dd_arr;
	$arr[]=$close_arr;

return $arr;
}
/* 해당 날자의 여유 좌석 확인 */
function get_tour_jan_cnt($pid, $tourday) {
	$row=sql_fetch("SELECT *,  (maxCount-nowCount) AS ddCount FROM tour_reg_count WHERE pid='$pid' AND tourDate ='$tourday' AND maxCount>0 AND (maxCount-nowCount)<=5 ORDER BY tourDate" );

	return $row;
}

//대표 요금 표시
function get_fee_disp($pid, $md="") { //md는 상세보기 처리옵션


	$feeData=sql_fetch("select * from tour_fee where wr_id='".$pid."' order by is_first desc, id " );
	$feeData2=sql_fetch("select  * , CAST(fee2 AS SIGNED) AS fee2  from tour_fee where wr_id='".$pid."'  and fee2>=0 order by is_first desc, id " );

	If($feeData2[fee2]>0) {
		if($md=="view") $fee_txt=number_format($feeData[fee1]). "원<br> <span class=\"f_14 space-center c_5c11d7\">+ ".number_format($feeData[fee2])."유로</span>";
		else if($md=="arr") {
			$fee_txt[won]=number_format($feeData[fee1]). "원";
			$fee_txt[euro]=number_format($feeData[fee2])."유로";
		}
		else $fee_txt="신청비 ".number_format($feeData[fee1]). "원 + 현지지불 ".number_format($feeData2[fee2])."유로";
	}
	Else {
		if($md=="arr") {
			$fee_txt[won]=number_format($feeData[fee1]). "원";
			//$fee_txt[euro]=number_format($feeData[fee2])."유로";
		}
		else $fee_txt="신청비용 ".number_format($feeData[fee1]). "원";
	}

	if($row[fee_org] && $md!="arr") $fee_txt='<strike style="color:#999;font-weight:normal">'.stripslashes($row[fee_org]).'</strike><br>'.$fee_txt;


	return $fee_txt;
}

/* QUERY_STRING 중복 제거 후 리턴*/
function query_string($str, $value="") {
	parse_str($str, $arr);
	foreach($arr as $k=>$v) {
		if($k!=$value && $k) $arr2[]=$k."=".$v;
	}
	 $str=implode("&",$arr2);
	 return $str;
}

function get_paging_fr($write_pages, $cur_page, $total_page, $url, $add="")
{
    $str = "";
    if ($cur_page > 1) {
        $str .= "<a href='" . $url . "1{$add}' aria-label=\"Previous\" class=\"direction first\" ><span>처음</span></a>";
        //$str .= "[<a href='" . $url . ($cur_page-1) . "'>이전</a>]";
    }

    $start_page = ( ( (int)( ($cur_page - 1 ) / $write_pages ) ) * $write_pages ) + 1;
    $end_page = $start_page + $write_pages - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1) $str .= "<a href='" . $url . ($start_page-1) . "{$add}' aria-label=\"Previous\" class=\"direction prev\" ><span>이전</span></a>";
    if ($total_page > 1) {
        for ($k=$start_page;$k<=$end_page;$k++) {
            if ($cur_page != $k)
                $str .= "<a href='$url$k{$add}'>$k</a>";
            else
                $str .= "<strong>$k</strong>";
        }
    }

    if ($total_page > $end_page) $str .= "<a href='" . $url . ($end_page+1) . "{$add}' aria-label=\"Next\" class=\"direction next\"><span>다음</span></a>";

    if ($cur_page < $total_page) {
        //$str .= "[<a href='$url" . ($cur_page+1) . "'>다음</a>]";
        $str .= "<a href='$url$total_page{$add}' aria-label=\"Next\" class=\"direction last\"><span>맨끝</span></a>";
    }
    $str .= "";

    return $str;
}

function mypage_qna_msg($row, $is_mb="") {

	$wr_datetime=substr($row[wr_datetime],2,14);
	$wr_content=nl2br($row[wr_content]);

	if($is_mb) {//고객 문의시
		$str.='<div class="incoming-msg mgb5">
			<div class="received-msg">
				<div class="received-withd-msg">
					<p>'.$wr_content.'</p>
					<span class="time-date">'.$wr_datetime.'</span>
				</div>
			</div>
		</div>';
	}
	else {
		$str.='<div class="outgoing-msg mgb5">
			<div class="sent-msg">
				<div class="sent-withd-msg">
					<p>'.$wr_content.'</p>
					<span class="time-date">'.$wr_datetime.'</span>
				</div>
			</div>
		</div>';
	}

	return $str;
}
/**************************************************** 쿠폰 발행용 함수들 *************************************************************/

function makeCoupon( $issueCnt , $cipher)
{

        $keysNumbers = Array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        $makeCouponResult  = Array();    //-- 발급된 쿠폰 번호

        for($i = 0; $i < $issueCnt; $i++){

	        shuffle($keysNumbers);                         //-- 배열값을 무작위로 섞기
	        $nkey     = array_flip($keysNumbers);          //-- 키&데이타 값 바꾸기
	        $cpNumber = implode('',array_rand( $nkey , $cipher ));   //-- 쿠폰 번호 생성
	        $makeCouponResult[$i]  = $cpNumber;

      	}

return $makeCouponResult;

}


function get_rand_number($len=4) {


    $len = abs((int)$len);
    if ($len < 1) $len = 1;
    else if ($len > 10) $len = 10;


    return rand(pow(10, $len - 1), (pow(10, $len) - 1));
}


//넘어온 세자리수를 36진수로 변환해서 반환합니다. preg_match_callback 을 통해서만 사용됩니다.
function get_simple_36($m){


    $str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $div = floor($m[0] / 36);
    $rest = $m[0] % 36;


    return $str[$div] . $str[$rest];
}


//지정된 자리수에 존재하는 소수 전체를 배열로 반환합니다. max len = 5
function get_simple_prime_number($len=5){

    $len = abs((int)$len);
    if ($len < 1) $len = 1;
    else if ($len > 5) $len = 5;

    $prime_1 = Array(1, 2, 3, 5, 7);

    if ($len == 1) return $prime_1;

    $start = pow(10, ($len - 1)) + 1;//101
    $end = pow(10, $len) - 1;//999
    $prime = $prime_1;

    unset($prime[0]);//1제거
    unset($prime[1]);//2제거
    $array = Array();
    for($i = 11; $i <= $end; $i+=2){//10보다 큰 소수에는 짝수가 없다.

        $max = floor(sqrt($i));
        foreach($prime as $j) {

            if ($j > $max) break;
            if ($i % $j == 0) continue 2;
        }

        $prime[] = $i;
        if ($i >= $start) $array[] = $i;
    }

    return $array;
}


//지정된 자릿수의 숫자로된 시리얼을 반환합니다. - 를 포함하고 싶지 않을때는 $cut 이 $len 보다 크거나 같으면 됩니다. max len = 36
function get_serial($len=16, $cut=4, $hipen='-'){


    $len = abs((int)$len);
    if ($len < 1) $len = 16;
    else if ($len > 36) $len = 36;


    $cut = abs((int)$cut);
    if ($cut < 1) $cut = 4;
    else if ($cut > $len) $cut = $len;


    list($usec, $sec) = explode(' ', microtime());
    $base_number = (string)$sec . str_replace('0.', '', (string)$usec);
     $base_number .= (string)get_rand_number(10) . (string)get_rand_number(8);//36자리 유니크한 숫자 문자열




    $prime = get_simple_prime_number(5);//5자리 소수 배열
    shuffle($prime);
    $prime = $prime[0];//랜덤한 5자리 소수


    $serial = bcmul(substr($base_number, 0, $len), $prime);
    $serial_length = strlen($serial);
    $sub = $len - $serial_length;


    if ($sub > 0) $serial .= (string)get_rand_number($sub);
    else if ($sub < 0) $serial = substr($serial, 0, $len);


    return preg_replace("`(.{" . $cut . "})`", "$1" . $hipen, $serial, floor(($len-1) / $cut));
}


//지정된 자릿수의 숫자와 영문으로된 시리얼을 반환합니다. - 를 포함하고 싶지 않을때는 $cut 이 $len 보다 크거나 같으면 됩니다. max len = 24
function get_serial_mix($len=16, $cut=4, $hipen='-'){


    $len = abs((int)$len);
    if ($len < 1) $len = 16;
    else if ($len > 24) $len = 24;


    $cut = abs((int)$cut);
    if ($cut < 1) $cut = 4;
    else if ($cut > $len) $cut = $len;


    $len2 = (int)($len * 3 / 2);
    if ($len2 % 2 == 1) $len2 += 1;


    $serial = get_serial($len2, $len2, $hipen);


    $serial = substr(preg_replace_callback("`.{3}`", "get_simple_36", $serial), 0, $len);


    return preg_replace("`(.{" . $cut . "})`", "$1" . $hipen, $serial, floor(($len-1) / $cut));
}

/* 환율 구하기
등록된 가장 마지막 날자의 환율을 환율을 구한다.
동일 날자가 있을수 있어 등록일도 order에 추가.
잘못 입력할수 있어 환율금액이 최소 1,000원 이상, 3000이하만 검색.
*/
function get_exchange_rate() {
	$row=sql_fetch("select exchange_rate from tour_fee_ex where exchange_rate>1000 and exchange_rate<3000 order by ex_date desc, reg_date desc ");

	return $row[exchange_rate];
}

/* 구 옵션을 직접 처리로 변경 */

/* 투어 옵션 부분 반환. v1의 게시판 데이타 이전 */
function get_product_option($pid) {
	global $g5;
	$row=sql_fetch("select * from v2_product_options where pid='$pid' ");

	if($row[id]) {
		//$file_m=get_file("tourOption", $cat_a[$ci]);
	}
	else {
		/* 이전 상품 관리의 데이타 이전 */
		$row2=sql_fetch("select * from g4_write_product where wr_id='$pid' ");
		//echo $row2[wr_10];
		$cat_a=explode("|",$row2[wr_10]);
		for($ci=0; $ci<count($cat_a); $ci++) {
			if($cat_a[$ci]!="noSel") {
				$optData=sql_fetch("SELECT * FROM g4_write_tourOption where  wr_id='$cat_a[$ci]' ");

				$tourOption[$ci]= addslashes($optData[wr_content]);


			}
		}
		// 모임장소  tourOption[1] 구글 지도는 wr_link1,  투어일 tourOption[0] 투어시간 tourOption[3] 포함/불포함 tourOption[4] 필독사항 $tourOption
		// 자료 이전 등록
		sql_query(" INSERT INTO `v2_product_options` (  `pid`,  `meeting`,  `tour_day`,  `tour_time`,  `tour_option`,  `tour_info`)
		VALUES  (    '$pid',    '{$tourOption[1]}',    '{$tourOption[0]}',    '{$tourOption[3]}',    '{$tourOption[4]}',    '{$tourOption[5]}'  )");

		/* 첨부 파일 이전 */


	//$row=sql_fetch("select * from g4_write_product where wr_id='$pid' ");
	//$cat_a=explode("|",$row[wr_10]);
		$bo_table="tourOption";
		$move_bo_table="v2_product_options";
		$src_dir = G5_DATA_PATH.'/file/'.$bo_table; // 원본 디렉토리
        $dst_dir = G5_DATA_PATH.'/file/'.$move_bo_table; // 복사본 디렉토리

		@mkdir(G5_DATA_PATH.'/file/'.$move_bo_table, G5_DIR_PERMISSION);
		@chmod(G5_DATA_PATH.'/file/'.$move_bo_table, G5_DIR_PERMISSION);

		 $sql3 = " select * from g4_board_file where bo_table = '$bo_table' and wr_id = '".$cat_a[1]."' order by bf_no ";
		$result3 = sql_query($sql3);
        for ($k=0; $row3 = sql_fetch_array($result3); $k++)
        {
			if ($row3['bf_file'])
			{
				// 원본파일을 복사하고 퍼미션을 변경
                // 제이프로님 코드제안 적용
                $copy_file_name = $row3['bf_file'];
                @copy($src_dir.'/'.$row3['bf_file'], $dst_dir.'/'.$copy_file_name);
                @chmod($dst_dir/$row3['bf_file'], G5_FILE_PERMISSION);
			}
			 $sql = " insert into {$g5['board_file_table']}
				set bo_table = '$move_bo_table',
				wr_id = '$pid',
				bf_no = '{$row3['bf_no']}',
				bf_source = '".addslashes($row3['bf_source'])."',
				bf_file = '$copy_file_name',
				bf_download = '{$row3['bf_download']}',
				bf_content = '".addslashes($row3['bf_content'])."',
				bf_filesize = '{$row3['bf_filesize']}',
				bf_width = '{$row3['bf_width']}',
				bf_height = '{$row3['bf_height']}',
				bf_type = '{$row3['bf_type']}',
				bf_datetime = '{$row3['bf_datetime']}' ";
				sql_query($sql);
			}

		//print_r($tourOption);
	}

	$row=sql_fetch("select * from `v2_product_options` where pid='$pid' ");
	return $row;
}


/* 파일 업로드 */

 function upload_file_panda($bo_table, $wr_id, $w="") {
	global $_FILES, $g5, $bf_content;

	// 파일개수 체크
	$file_count   = 0;
	$upload_count = count($_FILES['bf_file']['name']);

	for ($i=0; $i<$upload_count; $i++) {
		if($_FILES['bf_file']['name'][$i] && is_uploaded_file($_FILES['bf_file']['tmp_name'][$i]))
			$file_count++;
	}

	if($w == 'u') {
		$file = get_file($bo_table, $wr_id);
	} else {

	}

	// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
	@mkdir(G5_DATA_PATH.'/file/'.$bo_table, G5_DIR_PERMISSION);
	@chmod(G5_DATA_PATH.'/file/'.$bo_table, G5_DIR_PERMISSION);

	$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

	// 가변 파일 업로드
	$file_upload_msg = '';
	$upload = array();
	for ($i=0; $i<count($_FILES['bf_file']['name']); $i++) {
		$upload[$i]['file']     = '';
		$upload[$i]['source']   = '';
		$upload[$i]['filesize'] = 0;
		$upload[$i]['image']    = array();
		$upload[$i]['image'][0] = '';
		$upload[$i]['image'][1] = '';
		$upload[$i]['image'][2] = '';

		// 삭제에 체크가 되어있다면 파일을 삭제합니다.
		if (isset($_POST['bf_file_del'][$i]) && $_POST['bf_file_del'][$i]) {
			$upload[$i]['del_check'] = true;

			$row = sql_fetch(" select bf_file from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '{$i}' ");
			@unlink(G5_DATA_PATH.'/file/'.$bo_table.'/'.$row['bf_file']);
			// 썸네일삭제
			if(preg_match("/\.({$config['cf_image_extension']})$/i", $row['bf_file'])) {
				delete_board_thumbnail($bo_table, $row['bf_file']);
			}
		}
		else
			$upload[$i]['del_check'] = false;

		$tmp_file  = $_FILES['bf_file']['tmp_name'][$i];
		$filesize  = $_FILES['bf_file']['size'][$i];
		$filename  = $_FILES['bf_file']['name'][$i];
		$filename  = get_safe_filename($filename);

		// 서버에 설정된 값보다 큰파일을 업로드 한다면
		if ($filename) {
			if ($_FILES['bf_file']['error'][$i] == 1) {
				$file_upload_msg .= '\"'.$filename.'\" 파일의 용량이 서버에 설정('.$upload_max_filesize.')된 값보다 크므로 업로드 할 수 없습니다.\\n';
				continue;
			}
			else if ($_FILES['bf_file']['error'][$i] != 0) {
				$file_upload_msg .= '\"'.$filename.'\" 파일이 정상적으로 업로드 되지 않았습니다.\\n';
				continue;
			}
		}

		if (is_uploaded_file($tmp_file)) {
			// 관리자가 아니면서 설정한 업로드 사이즈보다 크다면 건너뜀
			/*if (!$is_admin && $filesize > $board['bo_upload_size']) {
				$file_upload_msg .= '\"'.$filename.'\" 파일의 용량('.number_format($filesize).' 바이트)이 게시판에 설정('.number_format($board['bo_upload_size']).' 바이트)된 값보다 크므로 업로드 하지 않습니다.\\n';
				continue;
			}*/

			//=================================================================\
			// 090714
			// 이미지나 플래시 파일에 악성코드를 심어 업로드 하는 경우를 방지
			// 에러메세지는 출력하지 않는다.
			//-----------------------------------------------------------------
			$timg = @getimagesize($tmp_file);
			// image type
			if ( preg_match("/\.({$config['cf_image_extension']})$/i", $filename) ||
				 preg_match("/\.({$config['cf_flash_extension']})$/i", $filename) ) {
				if ($timg['2'] < 1 || $timg['2'] > 16)
					continue;
			}
			//=================================================================

			$upload[$i]['image'] = $timg;

			// 4.00.11 - 글답변에서 파일 업로드시 원글의 파일이 삭제되는 오류를 수정
			if ($w == 'u') {
				// 존재하는 파일이 있다면 삭제합니다.
				$row = sql_fetch(" select bf_file from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '$wr_id' and bf_no = '$i' ");
				@unlink(G5_DATA_PATH.'/file/'.$bo_table.'/'.$row['bf_file']);
				// 이미지파일이면 썸네일삭제
				if(preg_match("/\.({$config['cf_image_extension']})$/i", $row['bf_file'])) {
					delete_board_thumbnail($bo_table, $row['bf_file']);
				}
			}

			// 프로그램 원래 파일명
			$upload[$i]['source'] = $filename;
			$upload[$i]['filesize'] = $filesize;

			// 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
			$filename = preg_replace("/\.(php|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $filename);

			shuffle($chars_array);
			$shuffle = implode('', $chars_array);

			// 첨부파일 첨부시 첨부파일명에 공백이 포함되어 있으면 일부 PC에서 보이지 않거나 다운로드 되지 않는 현상이 있습니다. (길상여의 님 090925)
			$upload[$i]['file'] = abs(ip2long($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.replace_filename($filename);

			$dest_file = G5_DATA_PATH.'/file/'.$bo_table.'/'.$upload[$i]['file'];

			// 업로드가 안된다면 에러메세지 출력하고 죽어버립니다.
			$error_code = move_uploaded_file($tmp_file, $dest_file) or die($_FILES['bf_file']['error'][$i]);

			// 올라간 파일의 퍼미션을 변경합니다.
			chmod($dest_file, G5_FILE_PERMISSION);
		}
	}

	// 나중에 테이블에 저장하는 이유는 $wr_id 값을 저장해야 하기 때문입니다.
	for ($i=0; $i<count($upload); $i++)
	{
		if (!get_magic_quotes_gpc()) {
			$upload[$i]['source'] = addslashes($upload[$i]['source']);
		}

		$sql = " select count(*) as cnt from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '{$i}' ";

		$row = sql_fetch($sql);
		if ($row['cnt'])
		{
			// 삭제에 체크가 있거나 파일이 있다면 업데이트를 합니다.
			// 그렇지 않다면 내용만 업데이트 합니다.
			if ($upload[$i]['del_check'] || $upload[$i]['file'])
			{
				$sql = " update {$g5['board_file_table']}
							set bf_source = '{$upload[$i]['source']}',
								 bf_file = '{$upload[$i]['file']}',
								 bf_content = '{$bf_content[$i]}',
								 bf_filesize = '{$upload[$i]['filesize']}',
								 bf_width = '{$upload[$i]['image']['0']}',
								 bf_height = '{$upload[$i]['image']['1']}',
								 bf_type = '{$upload[$i]['image']['2']}',
								 bf_datetime = '".G5_TIME_YMDHIS."'
						  where bo_table = '{$bo_table}'
									and wr_id = '{$wr_id}'
									and bf_no = '{$i}' ";
				sql_query($sql);
			}
			else
			{
				$sql = " update {$g5['board_file_table']}
							set bf_content = '{$bf_content[$i]}'
							where bo_table = '{$bo_table}'
									  and wr_id = '{$wr_id}'
									  and bf_no = '{$i}' ";
				sql_query($sql);
			}
		}
		else
		{
			$sql = " insert into {$g5['board_file_table']}
						set bo_table = '{$bo_table}',
							 wr_id = '{$wr_id}',
							 bf_no = '{$i}',
							 bf_source = '{$upload[$i]['source']}',
							 bf_file = '{$upload[$i]['file']}',
							 bf_content = '{$bf_content[$i]}',
							 bf_download = 0,
							 bf_filesize = '{$upload[$i]['filesize']}',
							 bf_width = '{$upload[$i]['image']['0']}',
							 bf_height = '{$upload[$i]['image']['1']}',
							 bf_type = '{$upload[$i]['image']['2']}',
							 bf_datetime = '".G5_TIME_YMDHIS."' ";
			sql_query($sql);
		}
	}

	// 업로드된 파일 내용에서 가장 큰 번호를 얻어 거꾸로 확인해 가면서
	// 파일 정보가 없다면 테이블의 내용을 삭제합니다.
	 $sql = " select max(bf_no) as max_bf_no from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' ";

	$row = sql_fetch($sql);
	for ($i=(int)$row['max_bf_no']; $i>=0; $i--)
	{
		$sql = " select bf_file from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '{$i}' ";
		$row2 = sql_fetch($sql);

		// 정보가 있다면 빠집니다.
		if ($row2['bf_file']) break;

		$sql =" delete from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '{$i}' ";
		// 그렇지 않다면 정보를 삭제합니다.
		sql_query($sql);
	}
	return $sql;
}

/* 국가 리스트 반환. */
function na_list($md="li", $dv="pc") {
	global $na_a;

	if($md=="li") {
		foreach($na_a as $na => $na_str) {
			$str.='<li><a href="../contents/tour_list.php?na='.$na.'">'.$na_str.'</a></li>';
		}
	}

	return $str;
}
/* 회원 예약 상태 확인 */
function mb_booking_status ($mb_id) {
global $status_a;


	 $sql=" select *, r.mb_id as mb_ids , r.id as rid from `tour_reg` as r, g5_write_product as p where r.pid=p.wr_id and r.mb_id='$mb_id' and  (r.status<999) and regDate>1 and membCnt>'' and not( r.status='booking' or r.status='cart'  )";
	$rs=sql_query($sql);
					  // echo "select * from `tour_reg` as r, g4_write_product as p where r.pid=p.wr_id and r.mb_id='$mb_id' and  (r.status<9999) ";
	for ($regi=0; $row3=sql_fetch_array($rs); $regi++) {
		//인원 계산
		//echo $row3[status];
        if ($row3[isEvent] == "Y") {
			if ($row3[event_pid] == NULL && $row3[parent_id] != NULL) {
				$row4 = sql_fetch("select * from tour_reg as R, g5_write_product as P where R.pid = P.wr_id and wr_id = '$row3[pid]' and R.id = '$row3[id]'");
            } else {
               $row4 = sql_fetch("select * from tour_reg as R, g5_write_product as P where R.event_pid = P.wr_id and wr_id = '$row3[event_pid]' and R.id = '$row3[id]'");
			}
			if($row4[status]=="3") $class_name= " label-success ";
			else if($row4[status]=="9") $class_name= " label-danger ";
			else $class_name=" label-warning";

			$btn_status="<span class=' label $class_name'>".$status_a["$row4[status]"]." </span> <span class=' label label-warning'>이벤트 </span>";
			$row4[wr_subject]=preg_replace(" 투어","", $row4[wr_subject]);
			$membCnt_a=explode("|",$row4[membCnt]);
			$fee_a=explode("|",$row4[fee_id]);

			If($row4[ca_name]=="단체" || $row4[ca_name]=="단체차량") {
				$mCnt=0;
				For($mci=0; $mci<count($membCnt_a);$mci++) $mCnt+=$membCnt_a[$mci];
					$str= "[".$row4[ca_name]."] ".$row4[wr_subject]."  " .$mCnt ."명 (".$row4[tourDay].")".$btn_status."<br>";
			  }
			Else If($row4[ca_name]=="차량") {
				//$mCnt+=$membCnt_a[$mci];
				$feeData=sql_fetch("select * from tour_fee where id='$fee_a[$regi]' order by id" );
				$str= "[".$row4[ca_name]."] ".$row4[wr_subject]." " .$feeTxt.=$feeData[fee_subject]."(".$row4[tourDay].") <br> $btn_status ";
			}
			else {
				$mCnt=0;
				For($mci=0; $mci<count($membCnt_a);$mci++) $mCnt+=$membCnt_a[$mci];
				$str= "[".$row4[ca_name]."] ".$row4[wr_subject]."  " . $mCnt ."명 (".$row4[tourDay].")".$btn_status."<br>";
			}
		} else {

			if($row3[status]=="1") $class_name= " label-default ";
			else if($row3[status]=="2") $class_name= " label-info ";
			else if($row3[status]=="3") $class_name= " label-success ";
			else if($row3[status]=="9") $class_name= " label-danger ";
			else if($row3[status]=="91") $class_name= " label-default ";
			else $class_name=" label-default";

			$btn_status="<span class=' label $class_name'>".$status_a["$row3[status]"]."</span>";
			$row3[wr_subject]=preg_replace(" 투어","",$row3[wr_subject]);
			$membCnt_a=explode("|",$row3[membCnt]);
			$fee_a=explode("|",$row3[fee_id]);

			If($row3[ca_name]=="단체" || $row3[ca_name]=="단체차량" || $row3[ca_name]=="파리-워킹") {
				$mCnt=0;
				For($mci=0; $mci<count($membCnt_a);$mci++) $mCnt+=$membCnt_a[$mci];

				$str=  "[".$row3[ca_name]."] ".$row3[wr_subject]."  " . $mCnt ."명 (".$row3[tourDay].")".$btn_status;
			}
			Else If($row3[ca_name]=="차량" || $row3[ca_name]=="파리-차량") {
				//$mCnt+=$membCnt_a[$mci];
				$feeData=sql_fetch("select * from tour_fee where id='$fee_a[$regi]' order by id" );
				$str=  "[".$row3[ca_name]."] ".$row3[wr_subject]." " . $feeData[fee_subject]."(".$row3[tourDay].")" .$btn_status;
			}
			else {
				$mCnt=0;
				For($mci=0; $mci<count($membCnt_a);$mci++) $mCnt+=$membCnt_a[$mci];
				$str=  "[".$row3[ca_name]."] ".$row3[wr_subject]."  " . $mCnt ."명 (".$row3[tourDay].")".$btn_status;
			}

		}
		if($str) $arr[]='<a href="/admin/booking.php?serch_title='.$row3[mb_ids].'" style="text-decoration:none">'.$str.'</a>';
	}
	return implode("<br>", $arr);
}
/* 투어 종류 리스트 반환. */
function tour_sca_list( $md="li", $dv="pc") {
	global $na_a, $prod_sca_a, $_na;

	if($md=="li") {
		foreach($prod_sca_a[it] as $k => $sca) {
			$str.='<li><a href="../contents/tour_list.php?sca='.$k.'">'.$sca.'</a></li>';
		}
	}

	/*if($dv=="pc") $str.='<li><a href="/contents/my_qna.php">단독 맞춤 투어</a></li>';
	else $str.='<li><a href="/m/contents/my_qna.php">단독 맞춤 투어</a></li>';*/

	return $str;
}


function get_member_by_no($mb_no, $fields='*')
{
    global $g5;

    return sql_fetch(" select $fields from $g5[member_table] where mb_no = TRIM('$mb_no') ");
}

function get_member_by_mail($mb_email, $fields='*')
{
    global $g5;

    return sql_fetch(" select $fields from $g5[member_table] where mb_email = TRIM('$mb_email') ");
}
function get_res_info($rid)
{
	return  sql_fetch("select * from `tour_reg` as r, g5_write_product as p where r.pid=p.wr_id and  id='$rid'");

}

// 숫자의 콤마 comma 제거
function del_comma($val) {
	$val= str_replace(",","",$val);

	if($val=="0") $val="";

	return $val;

}

//ATA("reg_status","panda","36995");

//ATA();
/******** ATA insert ************/
//ATA("reg_status","panda","46951");
function ATA($gubun, $mb_id, $rid="") {
	global $member, $isAdmin,  $sendV ;

	if($isAdmin && $sendV) return false; //관리자 테스트 발송은 전송하지 않음.

	$mb=get_member($mb_id);
	if(!$mb[mb_hp]) {
		$row=sql_fetch("select * from `tour_reg` where id='".$rid."' ");
		$mb[mb_hp]=$row[mb_hp];
		sql_query("update g5_member set mb_hp='{$row[mb_hp]}' , mb_email='{$row[mb_email]}' where mb_id='$mb_id' ");
	}
	$callback="02-324-2136";

	// 일반 회원만 비즈톡 발송
	if($mb[mb_level]>=3) return false;
	else {

		if($gubun=="1mb_reg")  { //비용 문제로 중지. 17.7.18
			$template_code="Code1";
			$subject="가입축하";
			$content="신규가입회원이 되신것을 진심으로 감사드립니다.\n앞으로 투어 예약 / 확인 / 확정 여부 등을 메세지로 받으실 수 있습니다";

		}
		else if($gubun=="reg_status")   {
			if($rid) {
				$row=sql_fetch("select * from `tour_reg` where id='".$rid."' ");
				$pRow=sql_fetch("select * from g5_write_product where wr_id='".$row[pid]."'");
				$status=$row[status];
			}
			if($status=="1")  { //예약 대기
				/* v1  아래 19-03-22 버전으로 변경
				$template_code="Code2";
				$subject="예약 대기 상태";
				$content="안녕하세요 ".$mb[mb_name]."님  유럽 투어 전문 여행사 우노트래블입니다\n\n저희 투어를 예약해주셔서 감사드립니다\n예약하신  ".$row[tourDay]."  ".stripslashes($pRow[wr_subject])." 예약 대기 상태이십니다.\n\n저희가 24시간 내로 투어 가능여부 확인해드립니다\n\n예약 확인 상태로 변경 되면 입금 혹은 카드결제 가능하시니 기다려주세요 ^^\n\n감사합니다"; */

				/* 2019-03-22 */
				$template_code="status1_1904";
				$subject="예약 대기 상태_1904";

				$content="안녕하세요. ".$row[mb_name]."님, \n\n투어 예약신청 해주셔서 감사합니다.\n".$row[tourDay]." ".stripslashes($pRow[wr_subject])."는 진행 가능여부 확인이 필요합니다.\n예약확인으로 상태 변경 시 결제진행이 가능합니다.\n시간이내 상태 변경해드리니 조금만 기다려주세요.\n\n이용해 주셔서 감사합니다.";


			}
			else if($status=="2")  { //예약 확인 - 결제 가능 안내
				/* v1  아래 19-03-22 버전으로 변경
				내용 변경 2018-03-24
				$template_code="Code3-2";
				$subject="예약 확인 - 결제 가능 (12시간내)";

				 $content="안녕하세요 ".$mb[mb_name]." 우노트래블입니다\n\n저희 투어를 예약해주셔서 감사합니다\n예약하신 ".$row[tourDay]." ".stripslashes($pRow[wr_subject])." 의 예약 가능여부 확인이 되셨습니다\n12시간내로 입금 혹은 홈페이지에서 카드결제 해주시면 투어 확정 되십니다 ^^\n\n감사합니다"; */

				 	/* 2019-03-22 */
				 /*$template_code="status2_1904";
				$subject="예약확인_1904";

				$content="안녕하세요. ".$mb[mb_name]."님, 우노트래블입니다.\n\n투어 예약신청 해주셔서 감사합니다.\n예약신청한 ".$row[tourDay]." ".stripslashes($pRow[wr_subject])."의 예약 가능여부 확인되어 [예약대기] 상태에서 [예약확인] 상태로 변경되었습니다.\n예약확인 상태 변경 후 12시간이내 예약금(카드 또는 계좌이체) 결제 진행해야 합니다.\n결제 확인 후 [예약확인] 상태에서 [예약확정] 상태로 변경 해드립니다.  \n\n우노트래블을 이용해 주셔서 감사합니다.";*/

				 /* 2019-03-28 문구변경 재신청*/
				 $template_code="status2_1904_2";
				$subject="예약확인_1904_2";
				 $content="안녕하세요.   ".$row[mb_name]."님, 우노트래블입니다.\n\n투어 예약신청 해주셔서 감사합니다. \n예약신청한 ".$row[tourDay]." ".stripslashes($pRow[wr_subject])."의 예약 가능여부 확인되었습니다. \n[예약확인] 상태 에서 12시간이내 예약금(카드 또는 계좌이체) 결제 진행해야 합니다.  \n결제 확인 후 [예약확인] 상태에서 [예약확정] 상태로 변경 해드립니다.  \n\n우노트래블을 이용해 주셔서 감사합니다.";

				 /********$template_code="Code3-3";
				$subject="예약확인 - 결제";

				 $content="안녕하세요 ".$mb[mb_name]." 님 우노트래블입니다\n\n저희 투어를 예약해주셔서 감사합니다\n예약하신 ".$row[tourDay]." ".stripslashes($pRow[wr_subject])."  의 예약 가능여부 확인이 되었습니다\n12시간내로 홈페이지에서 입금 혹은 카드결제 해주시면 투어 확정 됩니다 ^^\n\감사합니다";*/



				/* 내용 변경 2018-03-24  이전 사용*/
				/*$template_code="Code3";
				$subject="예약 확인 - 결제 가능 안내";
				$content="안녕하세요  ".$mb[mb_name]."님 우노트래블입니다\n\n저희 투어를 예약해주셔서 감사합니다\n\n예약하신 ".$row[tourDay]."  ".stripslashes($pRow[wr_subject])." 의 예약 가능여부 확인이 되셨습니다\n\n48시간내로 입금 혹은 홈페이지에서 카드결제 해주시면 투어 확정 되십니다 ^^\n\n감사합니다";*/


			}
			else if($status=="3")  { //예약확정
				/* v1  아래 19-03-22 버전으로 변경
				$template_code="Code4";
				$subject="예약확정";
				$content="안녕하세요 우노트래블입니다\n\n예약하신 ".$row[tourDay]."  ".stripslashes($pRow[wr_subject])." 가 확정되셨습니다 \n\n저희 홈페이지 나의 예약보기에서도 확인이 가능하시며, 메일로 바우처가 자동 발송되었으니 참고 부탁드립니다.\n\n기타 문의는 저희 홈페이지 혹은 카카오톡으로 마음껏 문의 주세요 ^^\n\n감사합니다"; */

				/* 2019-03-22 */
				 $template_code="status3_1904";
				$subject="예약확정";

				$content="안녕하세요. ".$row[mb_name]."님, 우노트래블입니다.\n\n예약신청한 ".$row[tourDay]." ".stripslashes($pRow[wr_subject])."가 확정되었습니다.\n\n작성한 메일로 바우처 발송되었습니다.\n바우처는 홈페이지 로그인 후 [My Page] - [예약목록]에서 확인 가능합니다.\n\n기타 문의는 홈페이지 로그인 후 [My Page] - [1:1문의하기 또는 카카오톡]으로 문의주시기바랍니다.\n\n우노트래블을 이용해 주셔서 감사합니다.";


			}
			else if($status=="9")  { //예약취소.
				if($row[cancel_code]=="2") { //투어취소-투어불가능 및 기타
					/* v1  아래 19-03-22 버전으로 변경
					$template_code="Code8";
					$subject="투어취소-투어불가능 및 기타";

					$content="안녕하세요 우노트래블입니다\n\n예약하신  ".$row[tourDay]."  ".stripslashes($pRow[wr_subject])." 이 진행이 불가능하여 취소되었습니다.\n취소 관련 궁금하신 점이 있으시다면, 가입시 등록하신 메일을 확인해주세요.\n기타 문의사항은  저희 홈페이지 www.unotravel.co.kr  의 Q&A 게시판을 이용해주시거나 카카오톡으로 연락주시면 안내해드리겠습니다\n\n감사합니다.";
					*/

					/* 2019-03-22 */
					$template_code="status92_1904";
					$subject="투어취소-투어불가능 및 기타_1904";
					$content="안녕하세요. ".$row[mb_name]."님, 우노트래블입니다.\n\n예약신청한 ".$row[tourDay]." ".stripslashes($pRow[wr_subject])."의 진행이 불가능하여 취소처리 되었습니다.\n\n기타 문의는 홈페이지 로그인 후 [My Page] - [1:1문의하기 또는 카카오톡]으로 문의주시기바랍니다.\n\n우노트래블을 이용해 주셔서 감사합니다.";

				}
				else if($row[cancel_code]=="1") {
					/* v1  아래 19-03-22 버전으로 변경
					$template_code="Code7";
					$subject="투어취소-미입금시";

					$content="안녕하세요 우노트래블입니다\n\n예약하신  ".$row[tourDay]."  ".stripslashes($pRow[wr_subject])." 이 결제가 확인되지 않아 취소되었습니다.\n취소 관련 궁금하신 점이 있으시다면, 저희 홈페이지 www.unotravel.co.kr  의 Q&A 게시판을 이용해주시거나\n카카오톡으로 연락주시면 안내해드리겠습니다\n\n감사합니다."; */

					/* 2019-03-22 */
					$template_code="status91_1904";
					$subject="투어취소-미입금시_1904";
					$content="안녕하세요. ".$row[mb_name]."님, 우노트래블입니다.\n\n예약확인 상태의 ".$row[tourDay]." ".stripslashes($pRow[wr_subject])."의 예약금 결제 기한이 경과되어 취소처리 되었습니다.\n\n기타 문의는 홈페이지 로그인 후 [My Page] - [1:1문의하기 또는 카카오톡]으로 문의주시기바랍니다.\n\n우노트래블을 이용해 주셔서 감사합니다.";

				}
				else {
					/* v1  아래 19-03-22 버전으로 변경
					$template_code="Code6";
					$subject="예약취소";
					$content="안녕하세요 우노트래블입니다\n\n예약하신  ".$row[tourDay]."  ".stripslashes($pRow[wr_subject])." 이 취소되셨습니다.\n\n취소 관련 궁금하신 점이 있으시다면, 저희 홈페이지 www.unotravel.co.kr  의 Q&A 게시판을 이용해주시거나 카카오톡으로 연락주시면 안내해드리겠습니다\n\n감사합니다."; */

					/* 2019-03-22 */
					$template_code="status9_1904";
					$subject="예약취소_1904";
					$content="안녕하세요. ".$row[mb_name]."님, 우노트래블입니다.\n\n예약신청 후 취소요청하신  ".$row[tourDay]." ".stripslashes($pRow[wr_subject])."가 정상적으로 취소처리 되었습니다.\n\n기타 문의는 홈페이지 로그인 후 [My Page] - [1:1문의하기 또는 카카오톡]으로 문의주시기바랍니다.\n\n우노트래블을 이용해 주셔서 감사합니다.";

				}

			}


			else if($status=="_77")  { //투어취소-미입금시
				$template_code="Code7";
				$subject="투어취소-미입금시";

				$content="안녕하세요 우노트래블입니다\n\n예약하신  ".$row[tourDay]."  ".stripslashes($pRow[wr_subject])." 이 결제가 확인되지 않아 취소되었습니다.\n취소 관련 궁금하신 점이 있으시다면, 저희 홈페이지 www.unotravel.co.kr  의 Q&A 게시판을 이용해주시거나\n카카오톡으로 연락주시면 안내해드리겠습니다\n\n감사합니다.";

			}
			/*else if($status=="9")  { //예약취소. 1차. 마감과 취소 구분이 힘들어 아래로 변경함.17.7.18
				$template_code="Code5";
				$subject="예약취소";
				$content="안녕하세요 우노트래블입니다\n\n저희 투어를 예약해주셔서 감사합니다\n\n죄송하게도, 해당 투어는 이미 마감되어 더이상 예약이 불가능하여 예약 취소 진행되었습니다\n(투어 예약 확정은 입금자 우선순입니다)\n\n취소 관련 궁금하신 점이 있으시다면 저희 홈페이지 게시판 혹은 카카오톡으로 연락주시면 안내해드립니다\n\n감사합니다";

			}*/

		}
		/*else if($gubun=="test")   {
			$row=sql_fetch("select * from `tour_reg` where id='30099' ");
				$pRow=sql_fetch("select * from g5_write_product where wr_id='".$row[pid]."'");
			$template_code="Code6";
				$subject="예약취소";
				$content="안녕하세요 우노트래블입니다\n\n예약하신  ".$row[tourDay]."  ".stripslashes($pRow[wr_subject])." 이 취소되셨습니다.\n\n취소 관련 궁금하신 점이 있으시다면, 저희 홈페이지 www.unotravel.co.kr  의 Q&A 게시판을 이용해주시거나 카카오톡으로 연락주시면 안내해드리겠습니다\n\n감사합니다.";
		}*/
		if($template_code && $subject) { //코드와 제목이 있는 경우만
			if($mb_id=="panda") $mb[mb_hp]="010-5547-6626"; //조승오 테스트용
			sql_query("INSERT INTO ata_mmt_tran (date_client_req, `subject`, content, callback,  msg_status, recipient_num, msg_type, sender_key, template_code, etc_text_1, etc_text_2) 
			VALUES(SYSDATE(), '".$subject."', '".$content."', '".$callback."',   '1', '".$row[mb_hp]."', '1008', '3f6235e929d647e2c7f3892795fef5ce38129750','".$template_code."', '".$gubun."', '".$rid."' )");
		}
	}

}

/* 모바일 접속 확인 */
function dv_is_mobile($chk_mode="iphone") {
	require_once $_SERVER[DOCUMENT_ROOT] . "/bbs/lib/Mobile_Detect.php";

	$detect = new Mobile_Detect;

	if($chk_mode=="iphone") {
		return $detect->isiOS() ;
	}
	else if ( $detect->isMobile() ) {
		return "1";
	}
}

 /***************/

/* 자동 실행 정리 */
function auto_run() {
	/* 예약에 booking, cart등 최종 완료하지 않은 예약건 처리 */
	 $tourDay = G5_TIME_YMD;//date("Y-m-d",strtotime("-2 days"));
	sql_query ("update  `tour_reg` set del_time='".time()."'  WHERE ( `status`='cart' OR `status`='booking' ) AND tourDay <'$tourDay' and del_time <111 ");
	//echo "update  `tour_reg` set del_time='".time()."'  WHERE ( `status`='cart' OR `status`='booking' ) AND tourDay <'$tourDay' and del_time <111";
}


function removeEmoji($text) {
    $clean_text = "";
    // Match Emoticons
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clean_text = preg_replace($regexEmoticons, '', $text);
    // Match Miscellaneous Symbols and Pictographs
    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clean_text = preg_replace($regexSymbols, '', $clean_text);
    // Match Transport And Map Symbols
    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clean_text = preg_replace($regexTransport, '', $clean_text);
    // Match Miscellaneous Symbols
    $regexMisc = '/[\x{2600}-\x{26FF}]/u';
    $clean_text = preg_replace($regexMisc, '', $clean_text);
    // Match Dingbats
    $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);
    // Match Flags
    $regexDingbats = '/[\x{1F1E6}-\x{1F1FF}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);
    // Others
    $regexDingbats = '/[\x{1F910}-\x{1F95E}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);
    $regexDingbats = '/[\x{1F980}-\x{1F991}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);
    $regexDingbats = '/[\x{1F9C0}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);
    $regexDingbats = '/[\x{1F9F9}]/u';
    $clean_text = preg_replace($regexDingbats, '', $clean_text);
    return $clean_text;
}
/* 투어가 가능한 날인지 체크
변수는 배열로 이벤트 투어일 key 는 rid, value는 투어일
이벤트 투어는 1개 이상 있을수 있음.
*/
function chk_tour_day($pid, $tour_day) {

	$row=sql_fetch("SELECT * FROM tour_closed_2 WHERE pid='$pid'  AND closedDate ='$tour_day' AND isClose!='N' AND isClose!='E'");

	list($dd_arr, $close_arr) =get_tour_reg_count($pid, "null", $tour_day);

	$arr[]=$dd_arr;
	$arr[]=$close_arr;

	print_r($close_arr);



}

// 회원 삭제
function member_delete_panda($mb_id)
{
    global $config;
    global $g5;

    $sql = " select mb_name, mb_nick, mb_ip, mb_recommend, mb_memo, mb_level from {$g5['member_table']} where mb_id= '".$mb_id."' ";
    $mb = sql_fetch($sql);

    // 이미 삭제된 회원은 제외
    if(preg_match('#^[0-9]{8}.*삭제함#', $mb['mb_memo']))
        return;

    if ($mb['mb_recommend']) {
        $row = sql_fetch(" select count(*) as cnt from {$g5['member_table']} where mb_id = '".addslashes($mb['mb_recommend'])."' ");
        if ($row['cnt'])
            insert_point($mb['mb_recommend'], $config['cf_recommend_point'] * (-1), $mb_id.'님의 회원자료 삭제로 인한 추천인 포인트 반환', "@member", $mb['mb_recommend'], $mb_id.' 추천인 삭제');
    }

    // 회원자료는 정보만 없앤 후 아이디는 보관하여 다른 사람이 사용하지 못하도록 함 : 061025
    $sql = " update {$g5['member_table']} set mb_password = '', mb_level = 1, mb_email = '', mb_homepage = '', mb_tel = '', mb_hp = '', mb_zip1 = '', mb_zip2 = '', mb_addr1 = '', mb_addr2 = '', mb_birth = '', mb_sex = '', mb_signature = '', mb_memo = '".date('Ymd', G5_SERVER_TIME)." 삭제함\n{$mb['mb_memo']} {$mb['mb_hp']} {$mb['mb_email']}' where mb_id = '{$mb_id}' ";
    sql_query($sql);

    // 포인트 테이블에서 삭제
    sql_query(" delete from {$g5['point_table']} where mb_id = '$mb_id' ");

    // 그룹접근가능 삭제
    sql_query(" delete from {$g5['group_member_table']} where mb_id = '$mb_id' ");

    // 쪽지 삭제
    sql_query(" delete from {$g5['memo_table']} where me_recv_mb_id = '$mb_id' or me_send_mb_id = '$mb_id' ");

    // 스크랩 삭제
    sql_query(" delete from {$g5['scrap_table']} where mb_id = '$mb_id' ");

    // 관리권한 삭제
    sql_query(" delete from {$g5['auth_table']} where mb_id = '$mb_id' ");

    // 그룹관리자인 경우 그룹관리자를 공백으로
    sql_query(" update {$g5['group_table']} set gr_admin = '' where gr_admin = '$mb_id' ");

    // 게시판관리자인 경우 게시판관리자를 공백으로
    sql_query(" update {$g5['board_table']} set bo_admin = '' where bo_admin = '$mb_id' ");

    //소셜로그인에서 삭제 또는 해제
    if(function_exists('social_member_link_delete')){
        social_member_link_delete($mb_id);
    }

    // 아이콘 삭제
    @unlink(G5_DATA_PATH.'/member/'.substr($mb_id,0,2).'/'.$mb_id.'.gif');
}

/* 패키지 상품 관련 */
function get_skd($skd_gubun, $pid, $md="", $start_day="") { //md는 fr일때
	global $isMobile;

	$where[]=" pid='$pid' and is_main='1' and skd_gubun='{$skd_gubun}' ";

		if(count($where) ) $sql_search = " where ".implode(" and " , $where);

		if($skd_gubun=="skd")  {
			$rs=sql_query("select * from v2_pkgTourSkd {$sql_search} order by day " );
		//	echo "select * from v2_pkgTourSkd {$sql_search} order by day";
		if($start_day) $start_time=strtotime($start_day);

			while($row=sql_fetch_array($rs)) {
				$food_a=explode(",",stripslashes($row[food]));
				$food_a[0] = $food_a[0]? $food_a[0] : "불포함";
				$food_a[1] = $food_a[1]? $food_a[1] : "불포함";
				$food_a[2] = $food_a[2]? $food_a[2] : "불포함";

				if($start_time) {
					$s_day=yoil(date("Y-m-d", $start_time));
					$start_time+=86400;
				}

				$content=skd_content($row[content], $isMobile);// $content=stripslashes($row[content]);
				//$content = preg_replace('/font-family:.+?;/', "", $content);;

				$chked = preg_replace("/<[^>]*>/", "", $content);
				$chked = str_replace("&nbsp;", "", $chked);

				if ($chked.trim() != '' || $row[day]=="0") {
					if($row[day]=="0") $str.="<tr><th colspan=\"2\" >PREVIEW</th></tr>";
					else $str.="<tr><th colspan=\"2\" >{$row[day]}일차 &nbsp; &nbsp;".$s_day."</th></tr>";
					$str.="<tr><td style=\"vertical-align:top \" class=\"border-right\">".stripslashes(nl2br($row[city]))."</td><td>".$content."</td></tr>";
					if(trim($row[air])) $str.="<tr><td colspan=\"2\" class=\"border-top\"><strong>[항공]</strong> ".stripslashes($row[air])."</td></tr>";
					if(trim($row[hotel])) $str.="<tr><td colspan=\"2\" class=\"border-top\"><strong>[호텔]</strong> ".stripslashes($row[hotel])."</td></tr>";
					$str.="<tr><td colspan=\"2\" class=\"border-top\"><strong>[조식]</strong> ".$food_a[0]." &nbsp; &nbsp; &nbsp; <strong>[중식]</strong> ".$food_a[1]." &nbsp; &nbsp; &nbsp; <strong>[석식]</strong> ".$food_a[2]."</td></tr>";
				}
			}
		}
		else {
			$row=sql_fetch("select * from v2_pkgTourSkd {$sql_search} " );
			//echo "select * from v2_pkgTourSkd {$sql_search} ";

			$content=skd_content($row[content], $isMobile);

			if($md!="fr") $str="<tr><th>{$skd_gubun}</th></tr>";
			$str.="<tr><td colspan=\"2\" style=\"word-break: break-all; word-wrap: break-word;\">".$content."</td></tr>";

		}


		if($skd_gubun=="skd" || $skd_gubun=="포함내역" || $skd_gubun=="불포함내역") {
			unset($where);
			$where[]=" pid='$pid' and is_main='1' and skd_gubun='일정하단' ";
			if(count($where) ) {
				 $sql_search = " where ".implode(" and " , $where);
				$row=sql_fetch("select * from v2_pkgTourSkd {$sql_search} " );
				$content=skd_content($row[content], $isMobile);
			}
		}
		else $content="";


		if($md=="fr") return '<div class="tour-res-table">	<table >'.$str.'</table>'.$content.'</div>';
		else return '	<table class="table">'.$str.'</table>'.$content;
}

function skd_timetable($pid) {
	global $sel_y;

	$prow=sql_fetch("select * from g5_write_product where wr_id='$pid' " );
	$tour_subject=stripslashes($prow[wr_subject]);

	$where[]=" pid='$pid' and is_main='1' and del_time < 111 ";
	if($sel_y) $where[]=" start_time like  '".$sel_y."%'";

	if(count($where) ) $sql_search = " where ".implode(" and " , $where);
	$sql = " select * , SUBSTR(start_time,1,7) as ym from `v2_pkgTour` $sql_search GROUP BY SUBSTR(start_time,1,7) ORDER BY start_time limit 5";
	$result = sql_query($sql);

	$str='<ul>';

	for ($i=0; $row=sql_fetch_array($result); $i++) {
		$this_ym=$row[ym];//$sel_y."-".sprintf("%02d", $i);
		$str.='<li ><a href="javascript:void()" class="" data-ym="'.$this_ym.'">'.$this_ym.'월</a></li>	';
	}
	$str.='</ul>';
	$str.='<table class="mgb20">
								<colgroup>
									<col width="15%">
									<col width="15%">
									<col width="18%">
									 <col width="18%"> 
								</colgroup>
								<tbody>
								<tr>
									<th>출발시간</th><th>도착시간</th><th>투어명</th><th>항공사</th><th>요금</th><th>잔여석</th><th>스케줄보기</th><th>예약하기</th></tr>';

	//$where[]="  ";
	//if(count($where) ) $sql_search = " where ".implode(" and " , $where);
	$sql = " select *  from `v2_pkgTour` $sql_search  ORDER BY start_time ";
	$result = sql_query($sql);
	for ($i=0; $row=sql_fetch_array($result); $i++) {
		$air_name=code2str("항공사", $row[air] );
		$str.='<tr><td>'.yoil($row[start_time]).'</td>
				<td>'.yoil($row[arrive_time]).'</td>
				<td>'.$tour_subject.'</td>
				<td><img src="/images/air/'.$row[air].'.gif"> $air_name</td>
				<td>'.number_format($row[price]).'</td>
				<td>'.$row[seat].'</td>
				<td></td>

				<td></td></tr>';
	}
	$str.='</tbody></table>
							';
	return $str;
}
/* front 일정 가져오기*/
function skd_timetable_front($pid, $sel_ym, $dv="") {
	$prow=sql_fetch("select * from g5_write_product where wr_id='$pid' " );
	$tour_subject=stripslashes($prow[wr_subject]);

	$sql="SELECT * , SUBSTR(start_time ,1,7) AS start_ym FROM `v2_pkgTour` WHERE del_time<111 AND pid='$pid' and  is_view='1'  and is_main='1' AND start_time LIKE  '{$sel_ym}%' and start_time > '".G5_TIME_YMDHIS."' GROUP BY SUBSTR(start_time ,1,10) order by start_time ";
	$rs=sql_query($sql);

//	echo $sql;
//	exit;

	if($dv=="m") {
		$str= '<table>
										<!-- <colgroup>
											<col style="width:20%">
											<col style="width:auto">
											<col style="width:12%">
											<col style="width:12%">
											<col style="width:8%">
										</colgroup> -->
										<thead>
											<tr>
												<th >출발시간</th>
												<th colspan="3">투어명</th>
												<th rowspan="2"></th>
											</tr>
											<tr>
												<th>도착시간</th>
												<th>항공사</th>
												<th>요금</th>
												<th>잔여석</th>
											</tr>
										</thead>
										<tbody>';

	for($i=0; $row=sql_fetch_array($rs); $i++) {
		if($row[status]=="1") $st_btr='<span class="bt type1">예약가능</span>';
		else if($row[status]=="2") $st_btr='<span class="bt type2">출발 확정</span>';
		else if($row[status]=="3") $st_btr='<span class="bt type3">예약 마감</span>';
		else if($row[status]=="4") $st_btr='<span class="bt type4">예약 대기</span>';

		$air_name=code2str("항공사", $row[air] );

		if(!$sel_start_date) $sel_start_date=substr($row[start_time],0,10);

		$str.='<tr>
					<td style="letter-spacing: 0em;" data-ymd="'.substr($row[start_time],0,10).'">'.yoil($row[start_time]).'</td>
					<td colspan="3">'.$tour_subject.'</td>
					<td rowspan="2"><a href="javascript:view_skd_m(\''.$pid.'\', \''.substr($row[start_time],0,10).'\')" class="bt type1" >스케줄보기</a><br><br>'.$st_btr.'</td>
				</tr>
				<tr>
					<td style="letter-spacing: 0em;">'.yoil($row[arrive_time]).'</td>
					<td><img src="/images/air/'.$row[air].'.gif" style="vertical-align: middle"> '.$air_name.' </td>
					<td><span class="price">'.number_format($row[price]).'원</span></td>
					<td>'.$row[seat].'석</td>

				</tr>';
	}
	$str.='</tbody>
	</table>';
	}
	else {
		$str= '<table>
										<colgroup>
											<col style="width:11%">
											<col style="width:11%">
											<col style="width:auto">
											<col style="width:12%">
											<col style="width:12%">
											<col style="width:8%">
											<col style="width:12%">
											<col style="width:12%">
										</colgroup>
										<thead>
											<tr>
												<th>출발시간</th>
												<th>도착시간</th>
												<th>투어명</th>
												<th>항공사</th>
												<th>요금</th>
												<th>잔여석</th>
												<th>스케줄보기</th>
												<th>예약상태</th>
											</tr>
										</thead>
										<tbody>';

	for($i=0; $row=sql_fetch_array($rs); $i++) {
		if($row[status]=="1") $st_btr='<span class="bt type1">예약가능</span>';
		else if($row[status]=="2") $st_btr='<span class="bt type2">출발확정</span>';
		else if($row[status]=="3") $st_btr='<span class="bt type3">예약마감</span>';
		else if($row[status]=="4") $st_btr='<span class="bt type4">예약대기</span>';

		$air_name=code2str("항공사", $row[air] );

		if(!$sel_start_date) $sel_start_date=substr($row[start_time],0,10);

		$str.='<tr>
					<td style="letter-spacing: 0em;">'.yoil($row[start_time]).'</td>
					<td style="letter-spacing: 0em;">'.yoil($row[arrive_time]).'</td>
					<td>'.$tour_subject.'</td>
					<td><img src="/images/air/'.$row[air].'.gif" style="vertical-align: middle"> '.$air_name.'</td>
					<td><span class="price">'.number_format($row[price]).'원</span></td>
					<td>'.$row[seat].'석</td>
					<td><a href="javascript:view_skd(\''.$pid.'\', \''.substr($row[start_time],0,10).'\')" class="bt type1" >스케줄보기</a></td>
					<td>'.$st_btr.'</td>
				</tr>';
	}
	$str.='</tbody>
	</table>';
	}
	echo $str;

}
function yoil($datetime) {
	$datetime=strtotime($datetime);
	$day_a[]=date("m/d", $datetime);

	$week = array("(일)" , "(월)"  , "(화)" , "(수)" , "(목)" , "(금)" ,"(토)") ;

	$week_no=date('w'  , $datetime  );
	$day_a[]= $week[$week_no] ;


	$hi=date("H:i", $datetime);
	if($hi!="00:00") $day_a[]=$hi;


	return implode(" ",$day_a);
}
/* 에디터 내용에서 특정 style 제거 */
function skd_content($content, $dv="") {
	$content=stripslashes($content);

	if($dv) {
		$replace_search = array("margin-left: 40px;", "font-size: 12pt;", "font-size:12pt;",  "font-size: 18pt;","font-size: 16px;", "font-size: 12px;", "font-family:");
		$replace_target = array("margin-left: 10px;", "font-size: 11px;", "font-size:11px;",  "font-size: 14px;", "font-size: 11px;", "font-size: 14px;", "_font-family:");
		$content=str_replace($replace_search,  $replace_target, $content);
	}
	else {
		$replace_search = array("font-family:");
		$replace_target = array("_font-family:");
		$content=str_replace($replace_search,  $replace_target, $content);
	}

	return $content;
}
/*********************
코드명 반환
*********************/
function code2str($c_part,$c_code, $ret_mode="" ) {
 $row=sql_fetch("select * from v2_code where c_part='$c_part' and c_code='$c_code'  ");

		//echo "select * from v2_code where c_part='$c_part' and c_code='$c_code'";
		if($row['c_text']) {
			if($ret_mode=="str") { //권역을 front에 맞게 표시
				return $row['c_text2'];
			}
			else return $row['c_text'];
		}
		else return "";


}

function run_replace($tag, $arg = ''){

	if( $hook = get_hook_class() ){

		$args = array();

		if (
			is_array($arg)
			&&
			isset($arg[0])
			&&
			is_object($arg[0])
			&&
			1 == count($arg)
		) {
			$args[] =& $arg[0];
		} else {
			$args[] = $arg;
		}

		$numArgs = func_num_args();

		for ($a = 2; $a < $numArgs; $a++) {
			$args[] = func_get_arg($a);
		}

		return $hook->apply_filters($tag, $args, false);
	}

	return null;
}

function get_menu_db($use_mobile=0, $is_cache=false){
	global $g5;

	static $cache = array();

//	$cache = run_replace('get_menu_db_cache', $cache, $use_mobile, $is_cache);

	$key = md5($use_mobile);

	if( $is_cache && isset($cache[$key]) ){
		return $cache[$key];
	}

	$where = $use_mobile ? "me_mobile_use = '1'" : "me_use = '1'";

//	if( !($cache[$key] = run_replace('get_menu_db', array(), $use_mobile)) ){
		$sql = " select *
                from {$g5['menu_table']}
                where $where
                and length(me_code) = '2'
                order by me_order, me_id ";
		$result = sql_query($sql, false);

		for ($i=0; $row=sql_fetch_array($result); $i++) {

			$row['ori_me_link'] = $row['me_link'];
//			$row['me_link'] = short_url_clean($row['me_link']);
			$row['sub'] = isset($row['sub']) ? $row['sub'] : array();
			$cache[$key][$i] = $row;

			$sql2 = " select *
                    from {$g5['menu_table']}
                    where $where
                    and length(me_code) = '4'
                    and substring(me_code, 1, 2) = '{$row['me_code']}'
                    order by me_order, me_id ";
			$result2 = sql_query($sql2);
			for ($k=0; $row2=sql_fetch_array($result2); $k++) {
				$row2['ori_me_link'] = $row2['me_link'];
//				$row2['me_link'] = short_url_clean($row2['me_link']);
				$cache[$key][$i]['sub'][$k] = $row2;
			}
		}
//	}

	return $cache[$key];
}
?>
