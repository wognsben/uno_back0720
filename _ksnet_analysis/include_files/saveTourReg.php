<?
include_once("./_common.php");

//$wmode = $_GET[wmode];
//$wid = $_GET[wid];

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


if ($is_b2bmem=="Y") {
	$tourStatus=$wr_b2b_result;
}
else if ( $wr_reg_result ) { //일반회원 예약시 예약 상태 기본값
	$tourStatus=$wr_reg_result;
}
else if ( $status ) { //일반회원 예약시 예약 상태 기본값
	$tourStatus=$status;
}
else {
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
						sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`, `pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`,  `ISECMemo`, `status`, `mb_ip`,  `isMobile` , `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`) VALUES ('$regDate', '$member[mb_id]', '$tourDate', '$comboA_pid', '$membCnt',  '$fee_ids', '$totalFee1', '$totalFee2', '$totalFee3', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]',  '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9')");

						$parent_id = mysql_insert_id();
						sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`, `pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`, `ISECMemo`,  `status`, `mb_ip`, `isCombo` , `parent_id`, `isMobile`, `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`) VALUES ('$regDate', '$member[mb_id]', '$tourDateAdd', '$comboB_pid', '$membCnt',  '$fee_ids', '', '', '', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isCombo', '$parent_id', '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9')");

						echo  $responseText= "ok";
					}

				}
				else {
					
					if($is_b2bmem == 'Y') {
						$ISECMemo=$ISEC_name_str."|:|".$ISEC_birth_str."|:|".$ISEC_cp_str."|:|".$ISEC_nsid_str."|:|".$ISEC_expired_str."|:|".$ISEC_etc_str;
					}
                    else {
						if($ISEC_name_str && $ISEC_birth_str && $ISEC_expired_str) {
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
							sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`, `tourTime`, `pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`,  `ISECMemo`, `status`, `mb_ip`, `isMobile` , `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`) VALUES ('$regDate', '$member[mb_id]', '$tourDate', '$is_ampm', '$pid_ampm', '$membCnt',  '$fee_ids', '$totalFee1', '$totalFee2', '$totalFee3', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9')");
						}
					}
					else {
						if ($wmode == "modify") {
							sql_query("UPDATE `tour_reg` SET regDate = '$regDate', mb_id = '$member[mb_id]', tourDay = '$tourDate', tourTime = '$is_ampm', pid = '$pid', membCnt = '$membCnt', fee_id = '$fee_ids', total_fee1 = '$totalFee1', total_fee2 = '$totalFee2', total_fee3 = '$totalFee3', regMemo = '$regMemo', ISECMemo = '$ISECMemo', mb_ip = '$_SERVER[REMOTE_ADDR]', isMobile = '$isMobile', nation = '$nation', membMemo1 = '$membMemo1', membMemo2 = '$membMemo2', membMemo3 = '$membMemo3', membMemo4 = '$membMemo4', membMemo5 = '$membMemo5', membMemo6 = '$membMemo6', membMemo7 = '$membMemo7', membMemo8 = '$membMemo8', membMemo9 = '$membMemo9' WHERE id = '$wid'");
						} else {
							sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`, `tourTime`, `pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`,  `ISECMemo`, `status`, `mb_ip`, `isMobile` , `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`) VALUES ('$regDate', '$member[mb_id]', '$tourDate', '$is_ampm', '$pid', '$membCnt',  '$fee_ids', '$totalFee1', '$totalFee2', '$totalFee3', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9')");
						}
					}
					echo  $responseText= "ok";

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

                                    sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`,  `tourTime`,`pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`, `ISECMemo`,  `status`, `mb_ip`, `isEvent` , `isFRevent`, `parent_id`, `isMobile`, `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`) 
									VALUES ('$regDate', '$member[mb_id]', '$tourDateAdd', '$is_ampm', '$addProd_ampm', '$membCnt',  '$fee_ids', '', '', '', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isEvent', '$is_fr_event', '$parent_id', '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9')");
									// 이벤트는 부모 투어의 ampm을 리셋한다.
									sql_query("update `tour_reg` set `tourTime`='' where id='$parent_id' ");
								}
						}
						else {
							if ($wmode == "modify") {
								$w_event_id = $wid + 1;

								sql_query("UPDATE `tour_reg` SET regDate = '$regDate', mb_id = '$member[mb_id]', tourDay = '$tourDateAdd', pid = '$pid', event_pid = '$addProd', membCnt = '$membCnt', fee_id = '$fee_ids', regMemo = '$regMemo', ISECMemo = '$ISECMemo', mb_ip = '$_SERVER[REMOTE_ADDR]', isEvent = '$isEvent', isFRevent = '$is_fr_event', isMobile = '$isMobile', nation = '$nation', membMemo1 = '$membMemo1', membMemo2 = '$membMemo2', membMemo3 = '$membMemo3', membMemo4 = '$membMemo4', membMemo5 = '$membMemo5', membMemo6 = '$membMemo6', membMemo7 = '$membMemo7', membMemo8 = '$membMemo8', membMemo9 = '$membMemo9' WHERE id = '$w_event_id'");
							} else {
								sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`, `pid`,`event_pid`,`membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`, `ISECMemo`,  `status`, `mb_ip`, `isEvent` , `isFRevent`,  `parent_id`, `isMobile`, `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`) 
								VALUES ('$regDate', '$member[mb_id]', '$tourDateAdd', '$pid', '".$addProd."', '$membCnt',  '$fee_ids', '', '', '', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isEvent', '$is_fr_event', '$parent_id', '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9')");
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
if($pid != "4"){
	$row=sql_fetch("select * from g4_write_product where wr_id='".$pid."'");
	if($row['wr_event_option'] == "2"){
          if ($wmode == "modify") {
            $w_event_id = $wid + 1;

            sql_query("UPDATE `tour_reg` SET regDate = '$regDate', mb_id = '$member[mb_id]', tourDay = '$tourDateAdd', tourTime = '$is_ampm', pid = '$pid', event_pid = '$addProd', membCnt = '$membCnt', fee_id = '$fee_ids', regMemo = '$regMemo', ISECMemo = '$ISECMemo', mb_ip = '$_SERVER[REMOTE_ADDR]', isFRevent = '$is_fr_event', isMobile = '$isMobile', nation = '$nation', membMemo1 = '$membMemo1', membMemo2 = '$membMemo2', membMemo3 = '$membMemo3', membMemo4 = '$membMemo4', membMemo5 = '$membMemo5', membMemo6 = '$membMemo6', membMemo7 = '$membMemo7', membMemo8 = '$membMemo8', membMemo9 = '$membMemo9' WHERE id = '$w_event_id'");
          } else {
            $parent_id = mysql_insert_id();
            sql_query("INSERT INTO `tour_reg` ( `regDate`, `mb_id`, `tourDay`,  `tourTime`,`pid`,`event_pid`, `membCnt`, `fee_id`, `total_fee1`, `total_fee2`, `total_fee3`, `regMemo`, `ISECMemo`,  `status`, `mb_ip`, `isEvent` , `isFRevent`, `parent_id`, `isMobile`, `nation`, `isB2B`, `fee_status`, `membMemo1`, `membMemo2`, `membMemo3`, `membMemo4`, `membMemo5`, `membMemo6`, `membMemo7`, `membMemo8`, `membMemo9`)
			VALUES ('$regDate', '$member[mb_id]', '$tourDateAdd', '$is_ampm', '$pid', '".$addProd."', '$membCnt',  '$fee_ids', '', '', '', '$regMemo', '$ISECMemo', '$tourStatus', '$_SERVER[REMOTE_ADDR]', '$isEvent', '$is_fr_event', '$parent_id', '$isMobile', '$nation', '$isB2B', '1', '$membMemo1', '$membMemo2', '$membMemo3', '$membMemo4', '$membMemo5', '$membMemo6', '$membMemo7', '$membMemo8', '$membMemo9')");
          }
	}
}


//b2b 예약확정시 바우처발송

If($is_b2bmem=="Y" && $tourStatus=="3" && $responseText=="ok") {

	include_once("$g4[path]/lib/mailer.lib.php");

	$rid=mysql_insert_id();
	$data=sql_fetch("select * from `tour_reg` where id='$rid'");
	//  $mb = get_member($data[mb_id]);

	ob_start();
	
	include ("../voucher.php");
	$content = ob_get_contents();
	ob_end_clean();

	if($data[mb_id]=="admin") $data[mb_id]="unotravel@unotravel.co.kr";
		
	mailer("[UNO TRAVEL]", "unotravel@unotravel.co.kr", $data[mb_id], "안녕하세요. 우노트래블입니다. 예약하신 투어 voucher 입니다.", $content, 1);
}


if($isMobile=="m") {
	if($responseText=="dup") alert('이미 신청된 날자입니다. \n동일한 날자에 예약을 하실 수 없습니다.');
	if($responseText=="closed") alert('선택하신 날자는 휴무일입니다. \n투어일을 다시 선택해 주세요.');
	if($responseText=="error") alert('신청에 오류가 발생하였습니다.\n다시 신청해 주시기 바랍니다.');
	if($responseText=="ok") {
		alert('예약 신청이 되었습니다.\n예약 현황은 마이페이지에서 보실수 있습니다.',"/m/");
	}
}
?>
