<? include "../include/header.php"; 
if(!$member[mb_id]) {
	alert("로그인후 이용 가능합니다.","/contents/login.php?url=my_reser");
}

?>

<div id="container">
	<div class="sub-heading" style="background-image:url(../images/sub/sub_visual5.jpg)">
		<div class="cell">
			<h2>예약목록</h2>
		</div>
	</div>
	<div class="real-cont">
		<div class="contain">
			<div class="inner-wrap">
				<div class="body-left">
					<? include "../include/menu_mypage.php"; ?>
				</div>
				<div class="body-right">
					<!--// content -->			
					<div class="order-form">
						<table>
							<colgroup>
								<col style="width:16%">
								<col style="width:auto">
								<col style="width:15%">
							</colgroup>
							<thead>
								<tr>
									<th>예약일</th>
									<th>예약상품</th>
									<th>상태</th>										
								</tr>
							</thead>
							<tbody>
							<?
							$where[]=" not(status='cart' or  status='booking') ";
							$where[]="  r.pid=p.wr_id ";
							//$where[]="   r.mb_id='$member[mb_id]' ";
							if($member[mb_id]=="_admin") $where[]="  r.mb_id = 'eksql5736@naver.com' ";
							else $where[]="  r.mb_id = '{$member[mb_id]}' ";

                            $q = "Select *, r.mb_id as mb_ids from `tour_reg` as r, g5_write_product as p where ".implode(" and " , $where)." order by r.tourDay desc";

//                            echo $q;

							//echo "Select *, r.mb_id as mb_ids from `tour_reg` as r, g5_write_product as p where ".implode(" and " , $where)." order by r.tourDay desc";
							$rs= sql_query($q);
							$isCancelBtn="N";

							for ($i=0; $row=sql_fetch_array($rs); $i++) { 
								//$pData=sql_fetch("select wr_subject,ca_name from g4_write_product where wr_id='$row[pid]' ");
								$mb = get_member($row[mb_ids]);
								$reg_no=$mb[mb_no]."_".$row[id];

								If($row[status]=="1" || $row[status]=="2" || $row[status]=="3") {$isCancelBtn="Y";}

								if($row[ISECMemo] || $row[mb_passport_info] || $row[regMemo] ) $is_memo="1"; //메모 여부

								if($row[event_pid]) {
									$row[wr_subject]=get_product_subject($row[pid], $row[event_pid]);
									$row[wr_id]=$row[event_pid];
								}

								
								if($row['nation']=="패키지") {?>
									<tr>
									<td>
										<? If($row[status]<"9") {?><input type="checkbox" name="sel" value="<?=$row[id]?>"><?}?>
										<a _href="my_reser2.php"><?=Date("Y-m-d",$row[regDate])?><br><span class="text-red">(<?=$reg_no?>)</span></a></td>
									<td class="info">
										<table>
											<colgroup>
												<col style="width:auto">
												<col style="width:10%">
												<col style="width:28%">
											</colgroup>
											<tbody>
												<tr>
													<td colspan="3" class="tit"><a href="tour_view.php?pid=<?=$row[wr_id]?>"><?=stripslashes($row[wr_subject])?></a></td>
												</tr>
												<tr>
													<td  class="date2">투어일 : <?=$row['tourDay'];?></td>
													<td class="opt" colspan="2">만 4세이상 ~ 성인 <?=$row['membCnt']?>명</td>
												</tr>
												<?
												//선택한 인원 요금 표시, 국제 학생증 인원 리턴
												list( $mb_list, $ISEC_cnt) =booking_mb_list("res", $row);
												?>
												<tr>
													<td colspan="3" class="date2 text-right" style="border-top:1px solid #aaa;;" >
													<? if($is_memo) {?>
														<input type="button" value="작성 내용 보기" onclick="myMemo('<?=$row[id]?>')" class="btn-pack small  fl " >
													<?}?>
													
													<? 
														

													$fee1=str_replace(",","",$row[total_fee1]);
													$fee2=str_replace(",","",$row[total_fee2]);
													$fee3=str_replace(",","",$row[total_fee3]);
													$fee4=str_replace(",","",$row[total_fee4]);
													$fee_air=str_replace(",","",$row[total_fee_air]);

													echo '<table style="width:340px;float:right"><colgroup>
								<col style="width:190px">
								<col style="width:150px">
							</colgroup>';
													


													if($fee1) $feeIn_a[]="<tr><td>예약금 : ".number_format($fee1)."원 </td><td>".get_pkg_feein2($row['id'], $row[status], 'fee1', $fee1,  'fr').'</td></tr>';
													if($fee2) $feeIn_a[]= "<tr><td>중도금 : ".number_format($fee2)."원 </td><td>".get_pkg_feein2($row['id'], $row[status], 'fee2', $fee2,  'fr').'</td></tr>';
													if($fee_air) $feeIn_a[]= "<tr><td>항공요금 : ".number_format($fee_air)."원 </td><td>". get_pkg_feein2($row['id'], $row[status], 'fee_air', $fee_air,  'fr').'</td></tr>';
													if($fee3) $feeIn_a[]= "<tr><td>잔금 : ".number_format($fee3)."원 </td><td>".get_pkg_feein2($row['id'], $row[status], 'fee3', $fee3,  'fr').'</td></tr>';
													echo implode("",$feeIn_a);
													
													echo '</table>';
													unset($feeIn_a);
													
													?>
													
													</td>
												</tr>
											</tbody>
										</table>
										
									</td>
									<td><?=get_res_status_btn($row)?></td>
								</tr>
                                <?} else if (stripos($row['nation'], '세미패키지') !== false) {?>
                                    <tr>
                                        <td>
                                            <? If($row[status]<"9") {?><input type="checkbox" name="sel" value="<?=$row[id]?>"><?}?>
                                            <a _href="my_reser2.php"><?=Date("Y-m-d",$row[regDate])?><br><span class="text-red">(<?=$reg_no?>)</span></a></td>
                                        <td class="info">
                                            <table>
                                                <colgroup>
                                                    <col style="width:auto">
                                                    <col style="width:10%">
                                                    <col style="width:28%">
                                                </colgroup>
                                                <tbody>
                                                <tr>
                                                    <td colspan="3" class="tit"><a href="tour_view.php?pid=<?=$row[wr_id]?>"><?=stripslashes($row[wr_subject])?></a></td>
                                                </tr>
                                                <tr>
                                                    <td  class="date2">투어일 : <?=$row['tourDay'];?></td>
                                                    <td class="opt" colspan="2">만 4세이상 ~ 성인 <?=$row['membCnt']?>명</td>
                                                </tr>
                                                <?
                                                //선택한 인원 요금 표시, 국제 학생증 인원 리턴
                                                list( $mb_list, $ISEC_cnt) =booking_mb_list("res", $row);
                                                ?>
                                                <tr>
                                                    <td colspan="3" class="date2 text-right" style="border-top:1px solid #aaa;;" >
                                                        <? if($is_memo) {?>
                                                            <input type="button" value="작성 내용 보기" onclick="myMemo('<?=$row[id]?>')" class="btn-pack small  fl " >
                                                        <?}?>

                                                        <?


                                                        $fee1=str_replace(",","",$row[total_fee1]);
                                                        $fee2=str_replace(",","",$row[total_fee2]);
                                                        $fee3=str_replace(",","",$row[total_fee3]);
                                                        $fee4=str_replace(",","",$row[total_fee4]);
                                                        $fee_air=str_replace(",","",$row[total_fee_air]);

                                                        echo '<table style="width:340px;float:right"><colgroup>
								<col style="width:190px">
								<col style="width:150px">
							</colgroup>';



                                                        if($fee1) $feeIn_a[]="<tr><td>예약금 : ".number_format($fee1)."원 </td><td>".get_pkg_feein2($row['id'], $row[status], 'fee1', $fee1,  'fr').'</td></tr>';
                                                        if($fee2) $feeIn_a[]= "<tr><td>중도금 : ".number_format($fee2)."원 </td><td>".get_pkg_feein2($row['id'], $row[status], 'fee2', $fee2,  'fr').'</td></tr>';
                                                        if($fee_air) $feeIn_a[]= "<tr><td>항공요금 : ".number_format($fee_air)."원 </td><td>". get_pkg_feein2($row['id'], $row[status], 'fee_air', $fee_air,  'fr').'</td></tr>';
                                                        if($fee3) $feeIn_a[]= "<tr><td>잔금 : ".number_format($fee3)."원 </td><td>".get_pkg_feein2($row['id'], $row[status], 'fee3', $fee3,  'fr').'</td></tr>';
                                                        echo implode("",$feeIn_a);

                                                        echo '</table>';
                                                        unset($feeIn_a);

                                                        ?>

                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>

                                        </td>
                                        <td><?=get_res_status_btn($row)?></td>
                                    </tr>
                                <?} else {?>
								<tr>
									<td>
										<? If($row[status]<"9") {?><input type="checkbox" name="sel" value="<?=$row[id]?>"><?}?>
										<a _href="my_reser2.php"><?=Date("Y-m-d",$row[regDate])?><br><span class="text-red">(<?=$reg_no?>)</span></a></td>
									<td class="info">
										<table>
											<colgroup>
												<col style="width:auto">
												<col style="width:10%">
												<col style="width:28%">
											</colgroup>
											<tbody>
												<tr>
													<td colspan="3" class="tit"><a href="tour_view.php?pid=<?=$row[wr_id]?>"><?=stripslashes($row[wr_subject])?></a></td>
												</tr>
												<tr>
													<td colspan="3" class="date2">투어일 : <?=$row['tourDay'];?></td>
												</tr>
												<?
												//선택한 인원 요금 표시, 국제 학생증 인원 리턴
												list( $mb_list, $ISEC_cnt) =booking_mb_list("res", $row);
												echo $mb_list;
												
												
												?>
												<tr>
													<td colspan="3" class="date2 text-right" style="border-top:1px solid #aaa;;" >
													<? if($is_memo) {?>
														<input type="button" value="작성 내용 보기" onclick="myMemo('<?=$row[id]?>')" class="btn-pack small  fl " >
													<?}?>
													<p class="" style="padding-top:5px;">
													<? if(!$row[isEvent]) {
													$fee1=str_replace(",","",$row[total_fee1]);
													$fee2=str_replace(",","",$row[total_fee2]);
													$fee4=str_replace(",","",$row[total_fee4]);
													?>
													예약금 : <?=number_format($fee1)?>원<br>
													<? if($fee4) { ?>
													 바티칸 입장권 : <span class="text-black"><?=number_format($fee4)?>원</span><br>
													<?}?>
													현장지불잔금 : <?=($fee2)?number_format($fee2):"0"?>유로 
													
													<?}?></p>
													</td>
												</tr>
											</tbody>
										</table>
										
									</td>
									<td><?=get_res_status_btn($row)?></td>
								</tr>
								<?}
								}?>
								
							</tbody>
						</table>
					</div>
					<? If($isCancelBtn=="Y") {?>
					 <input type="button" value="예약취소 요청" onclick="req_cancel()" class="btn-pack small red mgt10"> 
					 <?}?>
					 <input type="button" value="무이자 할부안내" onclick="card_event()" class="btn-pack small red mgt10"> 

					<div class="order-form mgt30">
						<h3>예약금 입금 유의사항</h3>
						<div class="order-info-box">
							<?=stripslashes($config['cf_res_ok_pc'])?>
						</div>
					</div>

					<div class="order-form mgt30">
						<h3>세미패키지 예약금 입금 유의사항</h3>
						<div class="order-info-box">
							<?=stripslashes($config['cf_res_ok_pkg_pc'])?>
						</div>
					</div>

					<div class="paginate hide">
						<?
						$qry_str=query_string($_SERVER['QUERY_STRING'], "page");
						echo get_paging_fr("5", $page, $total_page, $PHP_SELF."?".$qry_str."&page="); 
						?>
					</div>
					<!-- content //-->
				</div>
			</div>
		</div>
	</div>
</div><!-- end container -->
<div id="myMemoPop" class="pop-layer" style="display:none;">
	<h2 class="pop-title">작성 내용 보기</h2>
	<div class="pop-content" id="myMemo">

	</div>
	<!-- <a href="javascript:$.fancybox.close()" class="pop-close">닫기</a> -->
</div>
<div id="cardInfoPop" class="pop-layer" style="display:none; max-width:1000px">
	<div class="pop-content">
	<?
	$row=sql_fetch("select * from g5_write_event where wr_id='29' ");
	echo $row[wr_content];
	?>
	</div>
</div>
<? include $_SERVER['DOCUMENT_ROOT'] . "/include/_my_reser_script.inc.php";?>

<? include "../include/footer.php"; ?>