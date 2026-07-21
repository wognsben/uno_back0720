<?
include_once("./_common.php");

if($gubun=="chk_del") { //  삭제
	if($target=="cart") {
		 sql_query ("update  `tour_reg` set del_time='".time()."'  WHERE ". str2qry("id", $sel_chk,"no") );
	}

}
else if($gubun=="getDisabledDates") { //  마감일 가져오기
	$today=date("Y-m-d",(time() -(86400*2)));
		$close_rs=sql_query("SELECT * FROM tour_closed_2 WHERE pid='$pid'  AND closedDate >'$today'  AND isClose!='N'  order by closedDate");
		while($cRow=sql_fetch_array($close_rs)) {
			$arr[]= $cRow[closedDate];
		}
	echo json_encode($arr);
}
else if($gubun=="deadlineDay") { //  마감임박 가져오기
	list($dd_arr, $close_arr) =get_tour_reg_count($pid, "null");

	$arr[]=$dd_arr;
	$arr[]=$close_arr;
		
	echo json_encode($arr);
}
else if($gubun=="req_cancel") {//취소 요청

	$sel_a = explode(";", $sel);
	$cancelDate=time();
	For($i=0; $i<count($sel_a); $i++) {
	  @sql_query("update  tour_reg set status='91' , memCancelDate='$cancelDate' where id='$sel_a[$i]' ");

	  $data=sql_fetch("select id, parent_id, isEvent from `tour_reg` where id='$sel_a[$i]'");
	  $data1=sql_fetch("select id from `tour_reg` where parent_id='$sel_a[$i]' or id='$data[parent_id]' ");

	  if ($data1[id]) {
		@sql_query("update  tour_reg set status='91' , memCancelDate='$cancelDate' where id='$data1[id]' ");
	  } else {
		if ($data[isEvent]) { // Child
		  $parent_id = $data[id] - 1;
		  @sql_query("update  tour_reg set status='91' , memCancelDate='$cancelDate' where id='$parent_id' ");
		} else {  // Parent
		  $child_id = $data[id] + 1;
		  $data2 = sql_fetch("select * from `tour_reg` where id='$child_id' ");
		  if ($data2[pid] == $data[pid] && $data2[isEvent] == "Y") {
			@sql_query("update  tour_reg set status='91' , memCancelDate='$cancelDate' where id='$child_id' ");
		  }
		}
	  }
	  
	  //@sql_query("update  tour_reg set status='91' , memCancelDate='$cancelDate' where parent_id ='$sel_a[$i]' ");
	}
}
else if($gubun=="myMemo") { //  내가 신청한 정보 확인
	if( is_mobile() ) $isMobile="1";
	echo myMemo($rid, $isMobile);
}
else if($gubun=="cardInfoPop") { //  내가 신청한 정보 확인
		//	if( is_mobile() ) $isMobile="1";
	$row=sql_fetch("select * from g5_write_event where wr_id='29' ");
	echo $row[wr_content];

}
else if($gubun=="chk_tour_day") { //  투어일이 유효한지 확인  addTourDay[rid] 투어일로 전달

	
	foreach($addTourDay as $rid => $tour_day) {
		$pid= $addProd[$rid];
		chk_tour_day($pid, $tour_day);
		
	}

}
else if($gubun=="get_pkg_info") { //  투어일이 유효한지 확인  addTourDay[rid] 투어일로 전달
if($dv=="m") $isMobile="Y";
	echo get_skd($pkg_gubun, $pid, "fr", $start_day);

}
else if($gubun=="get_pkg_timeskd") { //  투어일이 유효한지 확인  addTourDay[rid] 투어일로 전달
	echo skd_timetable_front($pid, $sel_ym);
}
else if($gubun=="get_pkg_ym") { //  투어일이 유효한지 확인  addTourDay[rid] 투어일로 전달
	//echo $sel_ym;
	 $sql="SELECT * , SUBSTR(start_time ,1,7) AS start_ym FROM `v2_pkgTour` WHERE del_time<111 AND is_view='1'  and is_main='1' AND start_time LIKE  '{$sel_ym}%' and start_time > '".G5_TIME_YMDHIS."' GROUP BY SUBSTR(start_time ,1,7)";
	 $row=sql_fetch($sql);
	/*$rs=sql_query($sql);
	for($i=0; $pkg_row=sql_fetch_array($rs); $i++) {
		 $ym_a[]=$pkg_row[start_ym];
	}*/
	/*//print_r($ym_a);
	$key = array_search($sel_ym, $ym_a);
	//echo skd_timetable_front($pid, $sel_ym);

	if($md=="next") {
		$key++;
	}
	else if($md=="prev") {
		$key--;
	}
	*/
	if($row['start_ym']) {
		$arr[skd]= skd_timetable_front($pid, $row['start_ym'], $dv);
	//	$arr[ym]=$ym_a[$key];
	}
	else {
		$arr[skd]="no";
	//	$arr[ym]="";
	}
	echo $arr[skd];
	//	echo json_encode( $arr );
}
else if($gubun=="pkg_tour_price") { //  pkg 가격 불러오기
	//echo $sel_ym;
	 $sql="SELECT price FROM `v2_pkgTour` WHERE del_time<111 AND is_view='1'  and is_main='1' AND start_time <='{$selDate} 23:59:59' and arrive_time >= '{$selDate}' ORDER BY start_time ";
	$row=sql_fetch($sql);

	if($row[price]>0 ) echo number_format($row[price]); 
	
}
?>
