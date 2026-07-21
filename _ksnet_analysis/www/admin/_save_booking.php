<?php
include_once('./_common.php');


if($member[mb_level]>7 ) { //관리자 만 가능
	if($gubun=="booking") {

		If($isRegSave=="Y") {
			//히스토리 기록을 위한 수정전 레코드 확인
			$org_row=sql_fetch("select * from `tour_reg` where id='$rid'");
			if(isset($_POST['adminMemo']) && trim($_POST['adminMemo']) ) $que_a[]= " adminMemo = '".addslashes(trim($_POST['adminMemo']))."' ";
			if(isset($_POST['adminMemoCancel']) &&  trim($_POST['adminMemoCancel'])  ) $que_a[]= " adminMemoCancel = '".addslashes(trim($_POST['adminMemoCancel']))."' ";
			if(isset($_POST['kakao_id']) &&  trim($_POST['kakao_id'])  ) $que_a[]= " kakao_id = '".addslashes(trim($_POST['kakao_id']))."' ";
			if(isset($_POST['tourDay']) &&  trim($_POST['tourDay'])  ) $que_a[]= " tourDay = '".addslashes(trim($_POST['tourDay']))."' ";
			if(isset($_POST['mb_id']) &&  trim($_POST['mb_id'])  ) $que_a[]= " mb_id = '".addslashes(trim($_POST['mb_id']))."' ";
			if(isset($_POST['mb_email']) &&  trim($_POST['mb_email'])  ) $que_a[]= " mb_email = '".addslashes(trim($_POST['mb_email']))."' ";
			if(isset($_POST['mb_hp']) &&  trim($_POST['mb_hp'])  ) $que_a[]= " mb_hp = '".addslashes(trim($_POST['mb_hp']))."' ";
			if(isset($_POST['mb_name']) &&  trim($_POST['mb_name'])  ) $que_a[]= " mb_name = '".addslashes(trim($_POST['mb_name']))."' ";


            if($org_row['nation']=="패키지") {
                if(isset($_POST['total_fee1']) &&  trim($_POST['total_fee1'])  ) $que_a[]= " total_fee1 = '".str_replace(",","",$_POST['total_fee1'])."' ";
                if(isset($_POST['total_fee2']) &&  trim($_POST['total_fee2'])  ) $que_a[]= " total_fee2 = '".str_replace(",","",$_POST['total_fee2'])."' ";
                if(isset($_POST['total_fee3']) &&  trim($_POST['total_fee3'])  ) $que_a[]= " total_fee3 = '".str_replace(",","",$_POST['total_fee3'])."' ";
                //if(isset($_POST['total_fee4']) &&  trim($_POST['total_fee4'])  ) $que_a[]= " total_fee4 = '".str_replace(",","",$_POST['total_fee4'])."' ";
                if(isset($_POST['total_fee_air']) &&  trim($_POST['total_fee_air'])  ) $que_a[]= " total_fee_air = '".str_replace(",","",$_POST['total_fee_air'])."' ";
            }

            if(stripos($org_row['nation'], '세미패키지') !== false) {
                if(isset($_POST['total_fee1']) &&  trim($_POST['total_fee1'])  ) $que_a[]= " total_fee1 = '".str_replace(",","",$_POST['total_fee1'])."' ";
                if(isset($_POST['total_fee2']) &&  trim($_POST['total_fee2'])  ) $que_a[]= " total_fee2 = '".str_replace(",","",$_POST['total_fee2'])."' ";
                if(isset($_POST['total_fee3']) &&  trim($_POST['total_fee3'])  ) $que_a[]= " total_fee3 = '".str_replace(",","",$_POST['total_fee3'])."' ";
                //if(isset($_POST['total_fee4']) &&  trim($_POST['total_fee4'])  ) $que_a[]= " total_fee4 = '".str_replace(",","",$_POST['total_fee4'])."' ";
                if(isset($_POST['total_fee_air']) &&  trim($_POST['total_fee_air'])  ) $que_a[]= " total_fee_air = '".str_replace(",","",$_POST['total_fee_air'])."' ";
            }
				
				
			
			if($rid) {//예약 수정
				if(count($que_a))	sql_query("update `tour_reg` set ".implode( " , ", $que_a )." where id='$rid'" );

				if($org_row['nation']=="패키지") {//투어요금 한번더 계산
					$new_row=sql_fetch("select * from `tour_reg` where id='$rid'");

					$total_fee4=$new_row['total_fee1']+$new_row['total_fee2']+$new_row['total_fee3']+$new_row['total_fee_air'];
					sql_query("update `tour_reg` set total_fee4 ='$total_fee4' where id='$rid'" );

					/* 패키지의 경우 입금일이 있으면 기록 */

					if(isset($_POST['fee1_date']) &&  trim($_POST['fee1_date'])  )  sql_query("UPDATE  `tour_reg_pkg_fee` SET `pay_gubun` = 'bank',  `in_date` = '".$_POST['fee1_date']."' WHERE  `rid` = '$rid' and `fee_gubun` = 'fee1'  ");
					if(isset($_POST['fee2_date']) &&  trim($_POST['fee2_date'])  )  sql_query("UPDATE  `tour_reg_pkg_fee` SET `pay_gubun` = 'bank',  `in_date` = '".$_POST['fee2_date']."' WHERE  `rid` = '$rid' and  `fee_gubun` = 'fee2'  ");
					if(isset($_POST['fee3_date']) &&  trim($_POST['fee3_date'])  )  sql_query("UPDATE  `tour_reg_pkg_fee` SET `pay_gubun` = 'bank',  `in_date` = '".$_POST['fee3_date']."' WHERE  `rid` = '$rid' and `fee_gubun` = 'fee3'  ");
					if(isset($_POST['fee_air_date']) &&  trim($_POST['fee_air_date'])  )  sql_query("UPDATE  `tour_reg_pkg_fee` SET `pay_gubun` = 'bank',  `in_date` = '".$_POST['fee_air_date']."' WHERE  `rid` = '$rid' and `fee_gubun` = 'fee_air'  ");
				}

                if(stripos($org_row['nation'], '세미패키지') !== false) {//투어요금 한번더 계산
                    $new_row=sql_fetch("select * from `tour_reg` where id='$rid'");

                    $total_fee4=$new_row['total_fee1']+$new_row['total_fee2']+$new_row['total_fee3']+$new_row['total_fee_air'];
                    sql_query("update `tour_reg` set total_fee4 ='$total_fee4' where id='$rid'" );

                    /* 패키지의 경우 입금일이 있으면 기록 */

                    if(isset($_POST['fee1_date']) &&  trim($_POST['fee1_date'])  )  sql_query("UPDATE  `tour_reg_pkg_fee` SET `pay_gubun` = 'bank',  `in_date` = '".$_POST['fee1_date']."' WHERE  `rid` = '$rid' and `fee_gubun` = 'fee1'  ");
                    if(isset($_POST['fee2_date']) &&  trim($_POST['fee2_date'])  )  sql_query("UPDATE  `tour_reg_pkg_fee` SET `pay_gubun` = 'bank',  `in_date` = '".$_POST['fee2_date']."' WHERE  `rid` = '$rid' and  `fee_gubun` = 'fee2'  ");
                    if(isset($_POST['fee3_date']) &&  trim($_POST['fee3_date'])  )  sql_query("UPDATE  `tour_reg_pkg_fee` SET `pay_gubun` = 'bank',  `in_date` = '".$_POST['fee3_date']."' WHERE  `rid` = '$rid' and `fee_gubun` = 'fee3'  ");
                    if(isset($_POST['fee_air_date']) &&  trim($_POST['fee_air_date'])  )  sql_query("UPDATE  `tour_reg_pkg_fee` SET `pay_gubun` = 'bank',  `in_date` = '".$_POST['fee_air_date']."' WHERE  `rid` = '$rid' and `fee_gubun` = 'fee_air'  ");
                }
			}
			else {//예약 추가
				/*
				$regDate=time();
				
				For($i=0;$i<count($memb); $i++) {
					$membCnt.=$memb[$i]."|"; 				
				}
				sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `mb_name`, `mb_email`,  `kakao_id`, `tourDay`, `pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `res_add_opt`,  `regMemo`, `status`, `mb_ip`,  `isMobile`, `nation`) 
						VALUES ('$regDate', '$mb_id', '$mb[mb_name]', '$mb_email', '$kakao_id', '$tourDay', '$pid', '$membCnt',  '$fee_ids', '$totalFee1', '$totalFee2', '$totalFee3', '$res_add_opt', '$regMemo', '1', '$_SERVER[REMOTE_ADDR]', '$isMobile', '$nation')");
				$r_id=mysql_insert_id();
				*/
			}

			


			//히스토리 기록. 수정전과 수정후 레코드 비교
			$new_row=sql_fetch("select * from `tour_reg` where id='$rid'");

			foreach($org_row as $k => $v) {
				if($v==$new_row[$k]) {
				}
				else {
					 $key_name=code2str("예약정보필드",$k);
						if($key_name)  {
							if(is_numeric($new_row[$k])) {
								$arr[]="[".$key_name."] ".number_format($v)."→".number_format($new_row[$k]);
							}
							else {
								$arr[]="[".$key_name."] ".$v."→".$new_row[$k];
							}
						}
				}
			}
			if(count($arr)) {
				$contents=implode(", ", $arr);
				sql_query("INSERT INTO  `tour_reg_history` (  `reg_date`,  `mb_id`, `rid`, `contents`)VALUES  (     '".G5_TIME_YMDHIS."',    '{$member[mb_id]}', '$rid',   '$contents'  )");
			}
		}

		$url="/admin/popup/pop_content.php?gubun=booking&rid=".$rid;
		
	}
	if($url) goto_url($url);
}
else {
	alert("권한이 없습니다.","/");
	exit;
}
?>