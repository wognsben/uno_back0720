<?
include_once("./_common.php"); 

$fromDate = strtotime($start);
$toDate = strtotime($end);

if($is_clear=="Y") {
	sql_query("delete from `tour_fee` where id='$fee_id' limit 1");
}
else if($is_delEventFee=="Y") {
	sql_query("delete from `tour_fee` where fee_group='$fee_group' and fee_gubun='$fee_gubun'  ");
}
else {
	$fee_group=time(); //등록 시간을 구분으로 처리
	For($k=$fromDate; $k<=$toDate;$k+=86400) {			
		$k_week=date("w", $k); //요일을 구해서
		$feeDate=date("Y-m-d", $k);

		$isDc="Y";
		
		

		if(in_array($k_week, $week_array)) { //있으면 추가
			//$isClose="W";
		

			For($i=0; $i<=count($parent);$i++) {
				if($parent[$i]) {
					$parent_id=$parent[$i];

					$parent_row=sql_fetch("select * from tour_fee where id='$parent_id'  " );
					
					if($fee1[$i] || $fee2[$i] || $fee3[$i] ) {

						$tmp=sql_fetch("select * from tour_fee where parent='$parent_id' and  feeDate='$feeDate' ");
						
						$fee1[$i]=str_replace(",","",$fee1[$i]);
						$fee2[$i]=str_replace(",","",$fee2[$i]);
						$fee3[$i]=str_replace(",","",$fee3[$i]);

						if($tmp[id]) {
							sql_query("update `tour_fee` set  `fee1` = '$fee1[$i]', `fee2` = '$fee2[$i]' , `fee3` = '$fee3[$i]', `fee_gubun_type` =  '$fee_gubun_type', `fee_group`='$fee_group'   where id='$tmp[id]' ");
						}
						else {	
							sql_query("INSERT INTO `tour_fee` ( `pid`, `fee_gubun`,  `fee_subject`, `air_id`, `fee1`, `fee2`, `fee3`, `feeUnit_str`, `feeUnit_str3`, isDc, feeDate, parent, fee_gubun_type, fee_group) VALUES ( '$parent_row[pid]', '$parent_row[fee_gubun]', '$parent_row[fee_subject]', '$parent_row[air_id]', '$fee1[$i]', '$fee2[$i]', '$fee3[$i]', '$parent_row[feeUnit_str]', '$parent_row[feeUnit_str3]', 'Y', '$feeDate', '$parent_id' , '$fee_gubun_type', '$fee_group')");
						}
					}
				}
			}
		}
	}
}
?>