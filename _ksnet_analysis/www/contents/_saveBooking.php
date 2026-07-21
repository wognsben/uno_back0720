<? 
include_once("./_common.php");

/* ver 2 추가 기능 */
if($is_ver=="v2") {
	/* 패키지 상품의 투어 첫날 출발일을 구한다*/
	$row=sql_fetch("select * from g5_write_product where wr_id='$pid' " );
	if($row[ca_name]=="패키지") {
		$pCate="패키지";
		$sql="SELECT *,   SUBSTR(start_time, 1, 10) AS start_ymd FROM  `v2_pkgTour` WHERE del_time < 111  AND is_view = '1'  AND is_main = '1'  AND start_time <='{$tourDate} 23:59:59' and arrive_time >= '{$tourDate}' ";
		$pkg_row=sql_fetch($sql);
		$tourDate=$pkg_row[start_ymd];

		$pkg_mb_cnt=$memb[0];

		$pkg_id=$pkg_row['id'];

		$totalFee1=$pkg_mb_cnt*$pkg_row['fee_1'];
		$totalFee2=$pkg_mb_cnt*$pkg_row['fee_2'];
		$totalFee3=$pkg_mb_cnt*$pkg_row['fee_3'];
		$totalFee4=$pkg_mb_cnt*$pkg_row['price'];
		$total_fee_air=$pkg_mb_cnt*$pkg_row['fee_air'];
	} else if(stripos($row[ca_name], '세미패키지') !== false) {
		$pCate="세미패키지";
		$sql="SELECT *,   SUBSTR(start_time, 1, 10) AS start_ymd FROM  `v2_pkgTour` WHERE del_time < 111  AND is_view = '1'  AND is_main = '1'  AND start_time <='{$tourDate} 23:59:59' and arrive_time >= '{$tourDate}' ";
		$pkg_row=sql_fetch($sql);
//		$tourDate=$pkg_row[start_ymd];

		$pkg_mb_cnt=$memb[0];

		$pkg_id=$pkg_row['id'];

		$totalFee1=$pkg_mb_cnt*$pkg_row['fee_1'];
		$totalFee2=$pkg_mb_cnt*$pkg_row['fee_2'];
		$totalFee3=$pkg_mb_cnt*$pkg_row['fee_3'];
		$totalFee4=$pkg_mb_cnt*$pkg_row['price'];
		$total_fee_air=$pkg_mb_cnt*$pkg_row['fee_air'];
	}
	
	if($is_booking=="y") { //최종 저장시
	
		$rid_a=explode(",",$rid);
	
		foreach($rid_a as $k => $r_id) {
			if($r_id) {

				
				$row=sql_fetch("select * from `tour_reg` where id='$r_id' ");

				if($row[id]) {
					$jan_arr=get_tour_jan_cnt($row[pid], $row[tourDay]);
					$mb_cnt_total=get_res_member_cnt($row[membCnt]);
					if($jan_arr[id] && ($jan_arr[ddCount]< $mb_cnt_total) ) {//검색이되어서 id가 있고, 남은 자리가 전체 인원보다 작으면 
						$err_msg[]=get_product_name_by_booking($r_id). " 예약가능 : ".$jan_arr[ddCount]." 자리";
						
					}
					else { //예약 가능 인원에 제한이 없으면 저장
					
						$nation=get_product_nation($row[pid]);

						$tourStatus=get_booking_status($row[pid]);
						$p_row=get_product_row($row[pid]);

						//$total_fee3=str_replace(",","",$totalFee3);//티켓 요금 재계산
						//$total_fee4=str_replace(",","",$totalFee4);

						$ISECMemo="";

						foreach($ISEC_name as $k => $v) {
							$ISECMemo.=trim($ISEC_name[$k]).",".trim($ISEC_ename_1[$k]).",".trim($ISEC_ename_2[$k]).",".trim($ISEC_no[$k]).",".trim($ISEC_birth[$k]).",".trim($ISEC_expired[$k])."|";
						}

						$mb_passport_info='';

						foreach($passport_no as $k => $v) {
						   if($passport_sex[$k]) $sex="남";
						   else  if($passport_sex[$k]) $sex="여";
						  $mb_passport_info.=$passport_name_ko[$k].",".$passport_name_en[$k].",".$passport_birth[$k].",".$passport_no[$k].",".$passport_expired[$k].",".$passport_sex[$k]."|";
						}


						$ISECMemo=addslashes($ISECMemo);
						$mb_passport_info=addslashes($mb_passport_info);
						$regMemo=addslashes($regMemo);

						  /* 티켓 비용 계산 */
						If($nation=="패키지") {
							$total_fee3=$row['total_fee3'];
							$total_fee4=$row['total_fee4'];
						} else if(stripos($nation, '세미패키지') !== false) {
							$total_fee3=$row['total_fee3'];
							$total_fee4=$row['total_fee4'];
						}
						else {
							$total_fee3=$total_fee4=0;
							$rate['exchange_rate']=get_exchange_rate();
							$tk_rs = sql_query("select * from tour_fee_ticket  order by id" );
							While ($tk_row=sql_fetch_array($tk_rs)) {

								$ticket_fee_won = $tk_row[fee] * $rate['exchange_rate'];
								list($fee3, $fee4) =ticket_fee_calc($tk_row[id], $r_id, $ticket_fee_won);
								$total_fee3+=$fee3;
								$total_fee4+=$fee4;
							}
						}

						/* 투어 옵션 확인해서 배송지 주소, 룸타입 정보 que 처리 */
						$zip=addslashes($zip);
						$addr1=addslashes($addr1);
						$addr2=addslashes($addr2);
						$addr3=addslashes($addr3);
						$addr_jibeon=addslashes($addr_jibeon);
						$gift=$gift_1.",".$gift_2;

						$roominfo=addslashes($roominfo);

						if($p_row[is_delivery]) $addr_que=" 	`zip` = '$zip',  `addr1` = '$addr1',  `addr2` = '$addr2',  `addr3` = '$addr3',  `addr_jibeon` = '$addr_jibeon',  `gift` = '$gift', "; 
						if($p_row[is_roominfo]) $roominfo_que=" 	`roominfo` = '$roominfo', ";

						// 기존 reg정보 업데이트 
						sql_query("UPDATE `tour_reg` SET `status` = '$tourStatus', mb_name= '$mb_name', mb_email= '$mb_email', mb_kakao= '$mb_kakao', mb_hp= '$mb_hp' ,  `isMobile`='$isMobile', nation='$nation', 
						total_fee3='$total_fee3', total_fee4='$total_fee4' , regMemo='$regMemo',  {$addr_que} {$roominfo_que}
						ISECMemo='$ISECMemo' , mb_passport_info='$mb_passport_info' 
						WHERE id = '$r_id'");

						//추가 투어가 있으면 저장. 이벤트, 콤보 등
						if(count($addProd)) {
							$isEvent="Y";
							$parent_id=$r_id;
							

							foreach($addProd as $ak => $a_pid) {
								//echo $a_pid;
								if($a_pid && $a_pid!="X") {
									if($a_pid && $ak==$parent_id) {//이벤트가 아닌것도 예약을 할수 있어서 addProd 의 key를 pid와 확인한다.

										$tourStatus=get_booking_status($a_pid);
										$nation=get_product_nation($a_pid);

										$add_row=sql_fetch(" select * from `tour_reg` where parent_id ='$parent_id' ");

										if($add_row[id]) {//main reg id로 등록된게 있으면 pass
										}
										else {//없으면 추가
											sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `mb_name`, `mb_email`, `mb_kakao`, `mb_hp`, 
											 `tourDay`,  `tourTime`,`pid`, `event_pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`,  `total_fee4`, `total_fee_air`, 
											 `regMemo`, `ISECMemo`, 
											 `status`, `mb_ip`, `isEvent` , `isFRevent`, `parent_id`, `isMobile`, `nation`, `isB2B`, `fee_status`,
											 `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`,`is_ticket` , `mb_passport_info` )

											VALUES ('{$row[regDate]}', '{$member[mb_id]}', '$mb_name', '$mb_email', '$mb_kakao', '$mb_hp', 
											'{$addTourDay[$ak]}', '$is_ampm', '{$row[pid]}',  '$a_pid', '{$row[membCnt]}', '{$row[fee_id]}',  '$total_fee1', '$total_fee2', '$total_fee3', '$total_fee4', '$total_fee_air',  
											'$regMemo', '$ISECMemo',
											'$tourStatus', '{$_SERVER[REMOTE_ADDR]}', '$isEvent', '$is_fr_event', '$parent_id', '$isMobile', '$nation', '$isB2B', '1',
											'$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9','$is_ticket', '$mb_passport_info')");
										}
										// 이벤트는 부모 투어의 ampm을 리셋한다.
										//sql_query("update `tour_reg` set `tourTime`='' where id='$parent_id' ");
									}
								}
							}
						}
							If($nation=="패키지") { //패키지는 알림톡 발송 안함.
							} else if (stripos($nation, '세미패키지') !== false) {
							}
							else {
								if($tourStatus=="2" || $tourStatus=="1") ATA("reg_status",$row[mb_id],$r_id); 
							}
					}

					/* err_msg가 있으면 문구 정리해서 리턴 */
					
				}
				
			}
		} 
		/*if(count($err_msg)) {
			$err_str="대단히 죄송합니다^^ <br>남은 자리가 부족하여 ".implode("<br>",$err_msg)."<br>는 예약이 처리되지 못했습니다.<br>다른 날자를 이용해 주시기 바랍니다.";

		}*/
		echo $rid;

	}
	else {// tour view에서 저장시
		If(!$pid) {
			echo "Error";
			exit;
		}
		//echo "SELECT * FROM tour_closed_2 WHERE pid='$pid'  AND closedDate ='$tourDate' ";
		$close_row=sql_fetch("SELECT * FROM tour_closed_2 WHERE pid='$pid'  AND closedDate ='$tourDate' ");
		if($close_row['isClose']=="E" || $close_row['isClose']=="Y") { //Y는 휴무 E는 마감
//			echo '<pre>' . var_dump($close_row) . '</pre>';
			echo "close";
			exit;
		}
		else {
			
			
		} 


		$tourStatus=$booking_mode;
		if(is_numeric($totalFee2)) {
		}
		else $totalFee2="";

		If($pid=="9" Or $pid=="10" Or $pid=="3" Or $pid=="40") { //고대 로마 9 바로크 로마 10, 로마 야경 3는 하루에 2개 가능
			$maxCnt=2;
		}
		Else $maxCnt=1;

		

		$data=sql_fetch("select count(*) as cnt from `tour_reg` where `mb_id`= '$member[mb_id]' and `tourDay` = '$tourDate' and   pid='$pid' AND `status` ='3'   and status<9");//and pid='$pid'
		$total_count = $data[cnt];
		If($wmode != "modify" && $total_count>=$maxCnt &&$isB2B !="Y") {
			echo  $responseText="dup";
		}
		Else {
			$regDate=time();
			If($pCate=="car") {
				$membCnt.="Y|";
				$fee_ids.=$selFee_id."|";
			}
			elseIf($pCate=="패키지") {//패키지의 fee id와 인원 설정
				$membCnt=$pkg_mb_cnt;
				$fee_ids=$pkg_id; //
			}
			else if(stripos($pCate, '세미패키지') !== false) {//패키지의 fee id와 인원 설정
				$membCnt=$pkg_mb_cnt;
				$fee_ids=$pkg_id; //
			}
			Else {
				$isMembCntNon="Y"; //인원수 0일경우 Y
				For($i=0;$i<count($memb); $i++) {
					$membCnt.=$memb[$i]."|";   
					
					$fee_ids.=$fee_id[$i]."|";
					if($memb[$i]>0) $isMembCntNon="N"; //인원수가 0이상일 경우가 한번이라도 있으면 N

					$mb_cnt_total+=$memb[$i];  //전체 인원 합계
				}
			}

			$week=date("w",strtotime($tourDate));
			if($pid=="4") { //남부 아말피는 8,9월은 수요일만 휴무
				if($week=='3') $week="3";
				else $week="33";
			}
			if($isMembCntNon=="Y") {echo  $responseText="error"; exit;} //0이면 중단.

			$jan_arr=get_tour_jan_cnt($pid, $tourDate);
			if($jan_arr[id] && ($jan_arr[ddCount]< $mb_cnt_total) ) {//검색이되어서 id가 있고, 남은 자리가 전체 인원보다 작으면 
				echo "대단히 죄송합니다^^ <br>남은 자리가 부족하여 신청이 불가능합니다.<br>예약가능 : ".$jan_arr[ddCount]." 자리";
				exit;
			}

			$tmp=sql_fetch("select * from tour_closed_2 where pid='$pid' AND closedDate ='$tourDate'  AND isClose='Y'  ");

			if($tmp[id] && $isCombo!="Y")  {echo $responseText= "closed"; exit;}
			else {
				$totalFee1=del_comma($totalFee1);
				$totalFee2=del_comma($totalFee2);
				$totalFee3=del_comma($totalFee3);
				//$total_fee_air=del_comma($total_fee_air);

				$nation=get_product_nation($pid);

				sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`, `tourTime`, `pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `total_fee4`, `total_fee_air`, 
				`regMemo`,  `ISECMemo`, `status`, `mb_ip`, `isMobile` , `nation`, `isB2B`, `fee_status`,
				`membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`,`is_ticket` ) VALUES
				('$regDate', '{$member['mb_id']}', '$tourDate', '$is_ampm', '$pid', '$membCnt',  '$fee_ids', '$totalFee1', '$totalFee2', '$totalFee3', '$totalFee4', '$total_fee_air',  
				'$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isMobile', '$nation', '$isB2B', '1', 
				'$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9','$is_ticket')");
			}


			echo $r_id = sql_insert_id();
		}

	}


}
else if($ver1)  { //v1 버전
	$isB2B = NULL;
	if (($member[mb_level] >= 5) && ($member[mb_level] < 10)) {
		$is_b2bmem = 'Y';
		$isB2B = 'Y';
		$b2b_rank = $member[mb_rank];

		$membMemo1 = "";
		$membMemo2 = "";
		$membMemo3 = "";
		$membMemo4 = "";
		$membMemo5 = "";
		$membMemo6 = "";
		$membMemo7 = "";
		$membMemo8 = "";
		$membMemo9 = "";


		if($members[0] > 0) {
			$membMemo1 = $memb_name_str_0."|:|".$memb_birth_str_0."|:|".$memb_cp_str_0."|:|".$memb_nsid_str_0."|:|".$memb_expired_str_0."|:|".$memb_etc_str_0;
		} if($members[1] > 0) {
			$membMemo2 = $memb_name_str_1."|:|".$memb_birth_str_1."|:|".$memb_cp_str_1."|:|".$memb_nsid_str_1."|:|".$memb_expired_str_1."|:|".$memb_etc_str_1;
		} if($members[2] > 0) {
			$membMemo3 = $memb_name_str_2."|:|".$memb_birth_str_2."|:|".$memb_cp_str_2."|:|".$memb_nsid_str_2."|:|".$memb_expired_str_2."|:|".$memb_etc_str_2;
		} if($members[3] > 0) {
			$membMemo4 = $memb_name_str_3."|:|".$memb_birth_str_3."|:|".$memb_cp_str_3."|:|".$memb_nsid_str_3."|:|".$memb_expired_str_3."|:|".$memb_etc_str_3;
		} if($members[4] > 0) {
			$membMemo5 = $memb_name_str_4."|:|".$memb_birth_str_4."|:|".$memb_cp_str_4."|:|".$memb_nsid_str_4."|:|".$memb_expired_str_4."|:|".$memb_etc_str_4;
		} if($members[5] > 0) {
			$membMemo6 = $memb_name_str_5."|:|".$memb_birth_str_5."|:|".$memb_cp_str_5."|:|".$memb_nsid_str_5."|:|".$memb_expired_str_5."|:|".$memb_etc_str_5;
		} if($members[6] > 0) {
			$membMemo7 = $memb_name_str_6."|:|".$memb_birth_str_6."|:|".$memb_cp_str_6."|:|".$memb_nsid_str_6."|:|".$memb_expired_str_6."|:|".$memb_etc_str_6;
		} if($members[7] > 0) {
			$membMemo8 = $memb_name_str_7."|:|".$memb_birth_str_7."|:|".$memb_cp_str_7."|:|".$memb_nsid_str_7."|:|".$memb_expired_str_7."|:|".$memb_etc_str_7;
		} if($members[8] > 0) {
			$membMemo9 = $memb_name_str_8."|:|".$memb_birth_str_8."|:|".$memb_cp_str_8."|:|".$memb_nsid_str_8."|:|".$memb_expired_str_8."|:|".$memb_etc_str_8;
		}
	}

	/* b2b 예약시 상태값 */
	if ($is_b2bmem=="Y") {
		$tourStatus=$wr_b2b_result;
	}
	else if ( $wr_reg_result ) { //일반회원 예약시 예약 상태 기본값
		$tourStatus=$wr_reg_result;
	}
	else if ( $status ) { //일반회원 예약시 예약 상태 기본값
		$tourStatus=$status;
	}
	else if(!$tourStatus) {//기본 값 1
		$tourStatus="1";
	}

	If(!$member[mb_id]) {
		echo "login"; //로그인이 풀릴경우 다시 로그인후 접속함.
		exit;
	}
	Else {
		If($pid=="9" Or $pid=="10" Or $pid=="3" Or $pid=="40") { //고대 로마 9 바로크 로마 10, 로마 야경 3는 하루에 2개 가능
			$maxCnt=2;
		}
		Else $maxCnt=2;


		$data=sql_fetch("select count(*) as cnt from `tour_reg` where `mb_id`= '$member[mb_id]' and `tourDay` = '$tourDate' and pid !='3' and status<9");//and pid='$pid'
		$total_count = $data[cnt];
		If($wmode != "modify" && $total_count>=$maxCnt &&$isB2B !="Y") {
			echo  $responseText="dup";
		}
		Else {
			$regDate=time();
			If($pCate=="car") {
				$membCnt.="Y|";
				$fee_ids.=$selFee_id."|";
			}
			Else {
				$isMembCntNon="Y"; //인원수 0일경우 Y
				For($i=0;$i<count($memb); $i++) {
					$membCnt.=$memb[$i]."|";
					$fee_ids.=$fee_id[$i]."|";
					if($memb[$i]>0) $isMembCntNon="N"; //인원수가 0이상일 경우가 한번이라도 있으면 N
				}
			}
			if($isMembCntNon=="Y") {echo  $responseText="error"; exit;} //0이면 중단.

			If($pid) {
				$week=date("w",strtotime($tourDate));
				if($pid=="4") { //남부 아말피는 8,9월은 수요일만 휴무
					if($week=='3') $week="3";
					else $week="33";
				}
					
				//if($isCombo!="Y") {
				$tmp=sql_fetch("select * from tour_closed_2 where pid='$pid' AND closedDate ='$tourDate'  AND isClose='Y'  ");
				//}

				if($tmp[id] && $isCombo!="Y")  {echo $responseText= "closed"; exit;}
				else {
					if($isCombo=="Y") { //콤보 투어일 경우
						if ($wmode == "modify") {
							sql_query("UPDATE `tour_reg` SET regDate = '$regDate', mb_id = '$member[mb_id]', tourDay = '$tourDate', pid = '$comboA_pid', membCnt = '$membCnt', fee_id = '$fee_ids', total_fee1 = '$totalFee1', total_fee2 = '$totalFee2', total_fee3 = '$totalFee3', regMemo = '$regMemo',  ISECMemo = '$ISECMemo', mb_ip = '$_SERVER[REMOTE_ADDR]', isMobile = '$isMobile', nation = '$nation' , membMemo1 = '$membMemo1', membMemo2 = '$membMemo2', membMemo3 = '$membMemo3', membMemo4 = '$membMemo4', membMemo5 = '$membMemo5', membMemo6 = '$membMemo6', membMemo7 = '$membMemo7', membMemo8 = '$membMemo8', membMemo9 = '$membMemo9' WHERE id = '$wid'");

							$w_event_id = $wid + 1;
							sql_query("UPDATE `tour_reg` SET regDate = '$regDate', mb_id = '$member[mb_id]', tourDay = '$tourDateAdd', pid = '$comboB_pid', membCnt = '$membCnt', fee_id = '$fee_ids', regMemo = '$regMemo', ISECMemo = '$ISECMemo', mb_ip = '$_SERVER[REMOTE_ADDR]', isCombo = '$isCombo', isMobile = '$isMobile', nation = '$nation' , membMemo1 = '$membMemo1', membMemo2 = '$membMemo2', membMemo3 = '$membMemo3', membMemo4 = '$membMemo4', membMemo5 = '$membMemo5', membMemo6 = '$membMemo6', membMemo7 = '$membMemo7', membMemo8 = '$membMemo8', membMemo9 = '$membMemo9' WHERE id = '$w_event_id'");
						} else {
							sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`, `pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`,  `ISECMemo`, `status`, `mb_ip`,  `isMobile` , `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`,`is_ticket`, `total_fee4`) VALUES ('$regDate', '$member[mb_id]', '$tourDate', '$comboA_pid', '$membCnt',  '$fee_ids', '$totalFee1', '$totalFee2', '$totalFee3', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]',  '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9','$is_ticket', '$total_fee4')");

							$parent_id = mysql_insert_id();
							sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`, `pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`, `ISECMemo`,  `status`, `mb_ip`, `isCombo` , `parent_id`, `isMobile`, `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`,`is_ticket`, `total_fee4`) VALUES ('$regDate', '$member[mb_id]', '$tourDateAdd', '$comboB_pid', '$membCnt',  '$fee_ids', '', '', '', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isCombo', '$parent_id', '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9','$is_ticket', '$total_fee4')");

							echo  $parent_id;//$responseText= "ok";
						}

					}
					else {
						if($is_b2bmem == 'Y')
						{
							$ISECMemo=$ISEC_name_str."|:|".$ISEC_birth_str."|:|".$ISEC_cp_str."|:|".$ISEC_nsid_str."|:|".$ISEC_expired_str."|:|".$ISEC_etc_str;
						}
						else
						{
						 if($ISEC_name_str && $ISEC_birth_str && $ISEC_expired_str)
						 {
							$ISECMemo=$ISEC_name_str."|:|".$ISEC_birth_str."|:|".$ISEC_expired_str;
						 }
						}
							
						if( ($pid=="__45" || $pid=="__50") && $is_ampm) {
							if($is_ampm=="AM") $pid_ampm="45";
							else if($is_ampm=="PM") $pid_ampm="50";
							else $pid_ampm=$pid;

							if ($wmode == "modify") {
								sql_query("UPDATE `tour_reg` SET regDate = '$regDate', mb_id = '$member[mb_id]', tourDay = '$tourDate', tourTime = '$is_ampm', pid = '$pid_ampm', membCnt = '$membCnt', fee_id = '$fee_ids', total_fee1 = '$totalFee1', total_fee2 = '$totalFee2', total_fee3 = '$totalFee3', regMemo = '$regMemo',  ISECMemo = '$ISECMemo', mb_ip = '$_SERVER[REMOTE_ADDR]', isMobile = '$isMobile', nation = '$nation', membMemo1 = '$membMemo1', membMemo2 = '$membMemo2', membMemo3 = '$membMemo3', membMemo4 = '$membMemo4', membMemo5 = '$membMemo5', membMemo6 = '$membMemo6', membMemo7 = '$membMemo7', membMemo8 = '$membMemo8', membMemo9 = '$membMemo9' WHERE id = '$wid'");
							} else {
								sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`, `tourTime`, `pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`,  `ISECMemo`, `status`, `mb_ip`, `isMobile` , `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`,`is_ticket`, `total_fee4`) VALUES ('$regDate', '$member[mb_id]', '$tourDate', '$is_ampm', '$pid_ampm', '$membCnt',  '$fee_ids', '$totalFee1', '$totalFee2', '$totalFee3', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9','$is_ticket','$totalFee4')");
							}
						}
						else {
							if ($wmode == "modify") {
								sql_query("UPDATE `tour_reg` SET regDate = '$regDate', mb_id = '$member[mb_id]', tourDay = '$tourDate', tourTime = '$is_ampm', pid = '$pid', membCnt = '$membCnt', fee_id = '$fee_ids', total_fee1 = '$totalFee1', total_fee2 = '$totalFee2', total_fee3 = '$totalFee3', regMemo = '$regMemo', ISECMemo = '$ISECMemo', mb_ip = '$_SERVER[REMOTE_ADDR]', isMobile = '$isMobile', nation = '$nation', membMemo1 = '$membMemo1', membMemo2 = '$membMemo2', membMemo3 = '$membMemo3', membMemo4 = '$membMemo4', membMemo5 = '$membMemo5', membMemo6 = '$membMemo6', membMemo7 = '$membMemo7', membMemo8 = '$membMemo8', membMemo9 = '$membMemo9' WHERE id = '$wid'");
							} else {
								if ($pid == '45' || $pid == '46' || $pid == '50') {
																	
									/* if ($is_ticket == 1 ) {
										$totalFee1 = str_replace(",", "",$totalFee1) + str_replace(",", "",$totalFee4);
										$totalFee1 = number_format($totalFee1);
									} else {
										$totalFee2 = $totalFee3;
									} */
									
									sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`, `tourTime`, `pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`,  `ISECMemo`, `status`, `mb_ip`, `isMobile` , `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`, `is_ticket`, `total_fee4`) VALUES ('$regDate', '$member[mb_id]', '$tourDate', '$is_ampm', '$pid', '$membCnt',  '$fee_ids', '$totalFee1', '$totalFee2', '$totalFee3', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9', '$is_ticket','$totalFee4')");						     
								} else {
									sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`, `tourTime`, `pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`,  `ISECMemo`, `status`, `mb_ip`, `isMobile` , `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`, `is_ticket`, `total_fee4`) VALUES ('$regDate', '$member[mb_id]', '$tourDate', '$is_ampm', '$pid', '$membCnt',  '$fee_ids', '$totalFee1', '$totalFee2', '$totalFee3', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9', '$is_ticket','$totalFee4')");
									
								}

								$parent_id = mysql_insert_id();
								
								
							}
						}
						echo  $parent_id;//$responseText= "ok";

						If($isEvent=="Y" && ($pid=="4" || $is_fr_event=="Y" ) && $addProd!="X" ) {
							if( ($addProd=="__45" || $addProd=="__50") && $is_ampm) {
								if($is_ampm=="AM") $addProd_ampm="45";
								else if($is_ampm=="PM") $addProd_ampm="50";
								else $addProd_ampm=$addProd;

								if ($wmode == "modify") {
									$w_event_id = $wid + 1;

									sql_query("UPDATE `tour_reg` SET regDate = '$regDate', mb_id = '$member[mb_id]', tourDay = '$tourDateAdd', tourTime = '$is_ampm', pid = '$addProd_ampm', membCnt = '$membCnt', fee_id = '$fee_ids', regMemo = '$regMemo', ISECMemo = '$ISECMemo', mb_ip = '$_SERVER[REMOTE_ADDR]', isEvent = '$isEvent', isFRevent = '$is_fr_event', isMobile = '$isMobile', nation = '$nation', membMemo1 = '$membMemo1', membMemo2 = '$membMemo2', membMemo3 = '$membMemo3', membMemo4 = '$membMemo4', membMemo5 = '$membMemo5', membMemo6 = '$membMemo6', membMemo7 = '$membMemo7', membMemo8 = '$membMemo8', membMemo9 = '$membMemo9' WHERE id = '$w_event_id'");
								} else {
									$parent_id = mysql_insert_id();

									sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`,  `tourTime`,`pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`, `ISECMemo`,  `status`, `mb_ip`, `isEvent` , `isFRevent`, `parent_id`, `isMobile`, `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`,`is_ticket`, `total_fee4`)
											VALUES ('$regDate', '$member[mb_id]', '$tourDateAdd', '$is_ampm', '$addProd_ampm', '$membCnt',  '$fee_ids', '', '', '', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isEvent', '$is_fr_event', '$parent_id', '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9','$is_ticket','$total_fee4')");
									// 이벤트는 부모 투어의 ampm을 리셋한다.
									sql_query("update `tour_reg` set `tourTime`='' where id='$parent_id' ");
								}
							}
							else {
								if ($wmode == "modify") {
									$w_event_id = $wid + 1;
									
									sql_query("UPDATE `tour_reg` SET regDate = '$regDate', mb_id = '$member[mb_id]', tourDay = '$tourDateAdd', pid = '$pid', event_pid = '$addProd', membCnt = '$membCnt', fee_id = '$fee_ids', regMemo = '$regMemo', ISECMemo = '$ISECMemo', mb_ip = '$_SERVER[REMOTE_ADDR]', isEvent = '$isEvent', isFRevent = '$is_fr_event', isMobile = '$isMobile', nation = '$nation', membMemo1 = '$membMemo1', membMemo2 = '$membMemo2', membMemo3 = '$membMemo3', membMemo4 = '$membMemo4', membMemo5 = '$membMemo5', membMemo6 = '$membMemo6', membMemo7 = '$membMemo7', membMemo8 = '$membMemo8', membMemo9 = '$membMemo9' WHERE id = '$w_event_id'");
								} else {
									sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`, `pid`,`event_pid`,`membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`, `ISECMemo`,  `status`, `mb_ip`, `isEvent` , `isFRevent`,  `parent_id`, `isMobile`, `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`,`is_ticket`, `total_fee4`)
											VALUES ('$regDate', '$member[mb_id]', '$tourDateAdd', '$pid', '".$addProd."', '$membCnt',  '$fee_ids', '', '', '', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isEvent', '$is_fr_event', '$parent_id', '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9','$is_ticket','$total_fee4')");
								}
							}
						}
					}
				}
			}
			Else {echo  $responseText= "error";}
		}
	}

	// ★남부 아말피 코스트 여행을 제외한 이벤트 투어 입력
	if($pid != "4" && $addProd!="X"){
		$row=sql_fetch("select * from g5_write_product where wr_id='".$pid."'");
		if($row['wr_event_option'] == "2"){
			if ($wmode == "modify") {
				$w_event_id = $wid + 1;

				sql_query("UPDATE `tour_reg` SET regDate = '$regDate', mb_id = '$member[mb_id]', tourDay = '$tourDateAdd', tourTime = '$is_ampm', pid = '$pid', event_pid = '$addProd', membCnt = '$membCnt', fee_id = '$fee_ids', regMemo = '$regMemo', ISECMemo = '$ISECMemo', mb_ip = '$_SERVER[REMOTE_ADDR]', isFRevent = '$is_fr_event', isMobile = '$isMobile', nation = '$nation', membMemo1 = '$membMemo1', membMemo2 = '$membMemo2', membMemo3 = '$membMemo3', membMemo4 = '$membMemo4', membMemo5 = '$membMemo5', membMemo6 = '$membMemo6', membMemo7 = '$membMemo7', membMemo8 = '$membMemo8', membMemo9 = '$membMemo9' WHERE id = '$w_event_id'");
			} else {
				$parent_id = mysql_insert_id();
				
				
					
				  
					sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`,  `tourTime`,`pid`,`event_pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`, `ISECMemo`,  `status`, `mb_ip`, `isEvent` , `isFRevent`, `parent_id`, `isMobile`, `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`,`is_ticket`, `total_fee4`)
						VALUES ('$regDate', '$member[mb_id]', '$tourDateAdd', '$is_ampm', '$pid', '".$addProd."', '$membCnt',  '$fee_ids', '', '', '', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isEvent', '$is_fr_event', '$parent_id', '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9', '$is_ticket','$totalFee4')");
				
				
				
			}
		}
	}

	if($tourStatus=="2") {
		$tmp=sql_fetch("select id, pid, isEvent, parent_id, isCombo, card_pay, is_ticket , total_fee1, total_fee4 from `tour_reg` where mb_id='$member[mb_id]' and status in(2) order by id desc");
		if($tmp[isEvent]  || $tmp[isCombo]) {
			$ata_rid=$tmp[parent_id];
		}
		else $ata_rid=$tmp[id];

		//	echo $ata_rid;
		$fee1=str_replace(",","",$tmp[total_fee1]);
		$fee4=str_replace(",","",$tmp[total_fee4]);
		
		//if($fee1>0 || $$fee4>0) 
		if($ata_rid) ATA("reg_status",$member[mb_id],$ata_rid); //입금할 비용이 있으면. 티켓 비용도 계산
	}




	//b2b 예약확정시 바우처발송

	If($is_b2bmem=="Y" && $tourStatus=="3" && $responseText=="ok") {

		include_once(G5_LIB_PATH.'/mailer.lib.php');

		$rid=mysql_insert_id();
		$data=sql_fetch("select * from `tour_reg` where id='$rid'");
		//  $mb = get_member($data[mb_id]);

		ob_start();

		include ("../voucher.php");
		$content = ob_get_contents();
		ob_end_clean();

		if($data[mb_id]=="admin") $data[mb_id]="unotravel@unotravel.co.kr";

		//mailer("[UNO TRAVEL]", "unotravel@unotravel.co.kr", $data[mb_id], "안녕하세요. 우노트래블입니다. 예약하신 투어 voucher 입니다.", $content, 1);
	}


	if($isMobile=="m") {
		if($responseText=="dup") alert('이미 신청된 날자입니다. \n동일한 날자에 예약을 하실 수 없습니다.');
		if($responseText=="closed") alert('선택하신 날자는 휴무일입니다. \n투어일을 다시 선택해 주세요.');
		if($responseText=="error") alert('신청에 오류가 발생하였습니다.\n다시 신청해 주시기 바랍니다.');
		if($responseText=="ok") {
			alert('예약 신청이 되었습니다.\n예약 현황은 마이페이지에서 보실수 있습니다.',"/m/");
		}
	}
}
?>
