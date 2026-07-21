<? include "../include/header.php"; 
if(!$member[mb_id]) {
	alert("로그인후 이용 가능합니다.","/contents/login.php?url=my_reser");
}
?>

<div id="container">
	<div class="sub-heading">
		<h2>예약목록</h2>
	</div>
	<div class="category">
		<div class="box">
			<p>예약목록</p>
			<ul>
				<li><a href="my_reser.php">예약목록</a></li>
				<li><a href="cart.php">장바구니</a></li>
				<li><a href="my_qna.php">1:1 문의하기</a></li>
				<? if(!$sns_provider) {?><li><a href="my_info.php">개인정보확인/수정</a></li><?}?>
			</ul>
		</div>
	</div>
	<div class="real-cont">
		<!--// content -->			
		<div class="order-list">
			<ul><?
				$where[]=" not(status='cart' or  status='booking') ";
				$where[]="  r.pid=p.wr_id ";
				//$where[]="   r.mb_id='{$member[mb_id]}' ";
				if($member[mb_id]=="_admin") $where[]="  r.mb_id = 'eksql5736@naver.com' ";
				else $where[]="  r.mb_id = '{$member[mb_id]}' ";


				//echo "Select * from `tour_reg` as r, g5_write_product as p where r.pid=p.wr_id and r.mb_id='$member[mb_id]' order by r.tourDay desc";
				$rs= sql_query("Select *, r.mb_id as mb_ids from `tour_reg` as r, g5_write_product as p where ".implode(" and " , $where)." order by r.tourDay desc");
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
				if($row['nation']=="패키지") { ?>
				<li>
					<div class="reser-num">
						<? If($row[status]<"9") {?><input type="checkbox" name="sel" value="<?=$row[id]?>"><?}?>
						<a _href="my_reser2.php">예약일(주문번호) <strong><?=Date("Y-m-d",$row[regDate])?> <span class="num">(<?=$reg_no?>)</span></strong></a>
					</div>
					<table class="info">
						<colgroup>
							<col style="width:auto">
							<col style="width:28%">
							<col style="width:35%">
						</colgroup>
						<tbody>
							<tr>
								<td class="tit"><a href="tour_view.php?pid=<?=$row[wr_id]?>"><?=stripslashes($row[wr_subject])?></a></td>
								<td class="date"><?=$row['tourDay'];?></td>
								<td class="opt" style="font-size:14px">만 4세이상 ~ 성인<?=$row['membCnt']?>명 &nbsp;</td>
							</tr>
							<?
							//선택한 인원 요금 표시, 국제 학생증 인원 리턴
							list( $mb_list, $ISEC_cnt) =booking_mb_list("res", $row);
							
							?>
							
							<tr>
							<td colspan="3" class="date2 text-right" style="border-top:1px solid #aaa;;">
							<? if($is_memo) {?>
								<input type="button" value="작성 내용 보기" onclick="myMemo('<?=$row[id]?>')" class="btn-pack small fl  ">
							<?}?>
							<?
								$fee1=str_replace(",","",$row[total_fee1]);
													$fee2=str_replace(",","",$row[total_fee2]);
													$fee3=str_replace(",","",$row[total_fee3]);
													$fee4=str_replace(",","",$row[total_fee4]);
													$fee_air=str_replace(",","",$row[total_fee_air]);

													echo '<table style="width:280px;float:right"><colgroup>
								<col style="width:120px">
								<col style="width:150px">
							</colgroup>';
													


													if($fee1) $feeIn_a[]="<tr><td>예약금 : ".number_format($fee1)."원 </td><td>".get_pkg_feein2($row['id'], $row[status], 'fee1', $fee1,  'fr').'</td></tr>';
													if($fee2) $feeIn_a[]= "<tr><td>중도금 : ".number_format($fee2)."원 </td><td>".get_pkg_feein2($row['id'], $row[status], 'fee2', $fee2,  'fr').'</td></tr>';
													if($fee_air) $feeIn_a[]= "<tr><td>항공요금 : ".number_format($fee_air)."원 </td><td>". get_pkg_feein2($row['id'], $row[status], 'fee_air', $fee_air,  'fr').'</td></tr>';
													if($fee3) $feeIn_a[]= "<tr><td>잔금 : ".number_format($fee3)."원 </td><td>".get_pkg_feein2($row['id'], $row[status], 'fee3', $fee3,  'fr').'</td></tr>';
													echo implode("",$feeIn_a);
													
													echo '</table>';
													
													unset($feeIn_a);
													
													/*unset($fee_str);
								$fee1=str_replace(",","",$row[total_fee1]);
								$fee2=str_replace(",","",$row[total_fee2]);
								$fee3=str_replace(",","",$row[total_fee3]);
								$fee4=str_replace(",","",$row[total_fee4]);
								$fee_air=str_replace(",","",$row[total_fee_air]);

								if($fee1) $fee_str[]="예약금 : ".number_format($fee1)."원";
								if($fee2) $fee_str[]="중도금 : ".number_format($fee1)."원";
								if($fee_air) $fee_str[]="항공요금 : ".number_format($fee_air)."원";
								if($fee3) $fee_str[]="잔금 : ".number_format($fee1)."원";
								

								echo implode("<br>", $fee_str);*/?>
							</td></tr>
						</tbody>
					</table>
					
					
					<div class="buttons">
						<div class="row">
							<div class="col">
								<?=get_res_status_btn($row)?>
								<!-- <span class="btn-pack medium gray2 block">결제완료</span> -->
							</div>
						</div>					
					</div>
				</li>
				<? }
                else if (stripos($row['nation'], "세미패키지") !== false) {?>
                    <li>
                        <div class="reser-num">
                            <? If($row[status]<"9") {?><input type="checkbox" name="sel" value="<?=$row[id]?>"><?}?>
                            <a _href="my_reser2.php">예약일(주문번호) <strong><?=Date("Y-m-d",$row[regDate])?> <span class="num">(<?=$reg_no?>)</span></strong></a>
                        </div>
                        <table class="info">
                            <colgroup>
                                <col style="width:auto">
                                <col style="width:28%">
                                <col style="width:35%">
                            </colgroup>
                            <tbody>
                            <tr>
                                <td class="tit"><a href="tour_view.php?pid=<?=$row[wr_id]?>"><?=stripslashes($row[wr_subject])?></a></td>
                                <td class="date"><?=$row['tourDay'];?></td>
                                <td class="opt" style="font-size:14px">만 4세이상 ~ 성인<?=$row['membCnt']?>명 &nbsp;</td>
                            </tr>
                            <?
                            //선택한 인원 요금 표시, 국제 학생증 인원 리턴
                            list( $mb_list, $ISEC_cnt) =booking_mb_list("res", $row);

                            ?>

                            <tr>
                                <td colspan="3" class="date2 text-right" style="border-top:1px solid #aaa;;">
                                    <? if($is_memo) {?>
                                        <input type="button" value="작성 내용 보기" onclick="myMemo('<?=$row[id]?>')" class="btn-pack small fl  ">
                                    <?}?>
                                    <?
                                    $fee1=str_replace(",","",$row[total_fee1]);
                                    $fee2=str_replace(",","",$row[total_fee2]);
                                    $fee3=str_replace(",","",$row[total_fee3]);
                                    $fee4=str_replace(",","",$row[total_fee4]);
                                    $fee_air=str_replace(",","",$row[total_fee_air]);

                                    echo '<table style="width:280px;float:right"><colgroup>
								<col style="width:120px">
								<col style="width:150px">
							</colgroup>';



                                    if($fee1) $feeIn_a[]="<tr><td>예약금 : ".number_format($fee1)."원 </td><td>".get_pkg_feein2($row['id'], $row[status], 'fee1', $fee1,  'fr').'</td></tr>';
                                    if($fee2) $feeIn_a[]= "<tr><td>중도금 : ".number_format($fee2)."원 </td><td>".get_pkg_feein2($row['id'], $row[status], 'fee2', $fee2,  'fr').'</td></tr>';
                                    if($fee_air) $feeIn_a[]= "<tr><td>항공요금 : ".number_format($fee_air)."원 </td><td>". get_pkg_feein2($row['id'], $row[status], 'fee_air', $fee_air,  'fr').'</td></tr>';
                                    if($fee3) $feeIn_a[]= "<tr><td>잔금 : ".number_format($fee3)."원 </td><td>".get_pkg_feein2($row['id'], $row[status], 'fee3', $fee3,  'fr').'</td></tr>';
                                    echo implode("",$feeIn_a);

                                    echo '</table>';

                                    unset($feeIn_a);

                                    /*unset($fee_str);
                $fee1=str_replace(",","",$row[total_fee1]);
                $fee2=str_replace(",","",$row[total_fee2]);
                $fee3=str_replace(",","",$row[total_fee3]);
                $fee4=str_replace(",","",$row[total_fee4]);
                $fee_air=str_replace(",","",$row[total_fee_air]);

                if($fee1) $fee_str[]="예약금 : ".number_format($fee1)."원";
                if($fee2) $fee_str[]="중도금 : ".number_format($fee1)."원";
                if($fee_air) $fee_str[]="항공요금 : ".number_format($fee_air)."원";
                if($fee3) $fee_str[]="잔금 : ".number_format($fee1)."원";


                echo implode("<br>", $fee_str);*/?>
                                </td></tr>
                            </tbody>
                        </table>


                        <div class="buttons">
                            <div class="row">
                                <div class="col">
                                    <?=get_res_status_btn($row)?>
                                    <!-- <span class="btn-pack medium gray2 block">결제완료</span> -->
                                </div>
                            </div>
                        </div>
                    </li>
                <? }
				else {?>
				<li>
					<div class="reser-num">
						<? If($row[status]<"9") {?><input type="checkbox" name="sel" value="<?=$row[id]?>"><?}?>
						<a _href="my_reser2.php">예약일(주문번호) <strong><?=Date("Y-m-d",$row[regDate])?> <span class="num">(<?=$reg_no?>)</span></strong></a>
					</div>
					<table class="info">
						<colgroup>
							<col style="width:auto">
							<col style="width:10%">
							<col style="width:28%">
						</colgroup>
						<tbody>
							<tr>
								<td colspan="2" class="tit"><a href="tour_view.php?pid=<?=$row[wr_id]?>"><?=stripslashes($row[wr_subject])?></a></td>
								<td class="date"><?=$row['tourDay'];?></td>
							</tr>
							<?
							//선택한 인원 요금 표시, 국제 학생증 인원 리턴
							list( $mb_list, $ISEC_cnt) =booking_mb_list("res", $row);
							echo $mb_list;
							?>
							
							<tr>
							<td colspan="3" class="date2 text-right" style="border-top:1px solid #aaa;;">
							<? if($is_memo) {?>
								<input type="button" value="작성 내용 보기" onclick="myMemo('<?=$row[id]?>')" class="btn-pack small fl  ">
							<?}?>
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
							<?}?>
							</td></tr>
						</tbody>
					</table>
					
					
					<div class="buttons">
						<div class="row">
							<div class="col">
								<?=get_res_status_btn($row)?>
								<!-- <span class="btn-pack medium gray2 block">결제완료</span> -->
							</div>
						</div>					
					</div>
				</li>
				<?
				}
				}

				?>
			</ul>
		</div>
		<? If($isCancelBtn=="Y") {?>
			<input type="button" value="예약취소 요청" onclick="req_cancel()" class="btn-pack small red mgt10"> 
		<?}?>
		<input type="button" value="무이자 할부안내" onclick="card_event()" class="btn-pack small red mgt10"> 

		<div class="order-form mgt20">
			<h3>예약금 입금 유의사항</h3>
			<div class="order-info-box">
				<?=stripslashes($config['cf_res_ok_m'])?>
			</div>
		</div>
		<div class="order-form mgt20">
						<h3>세미패키지 예약금 입금 유의사항</h3>
						<div class="order-info-box">
							<?=stripslashes($config['cf_res_ok_pkg_m'])?>
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
</div><!-- end container -->

<div id="myMemoPop" class="pop-layer" style="display:none;">
	<h2 class="pop-title">작성 내용 보기</h2>
	<div class="pop-content" id="myMemo">

	</div>
	<!-- <a href="javascript:$.fancybox.close()" class="pop-close">닫기</a> -->
</div>
<div id="cardInfoPop" class="pop-layer" style="display:none; ">
	<div class="pop-content">
	<?
	$row=sql_fetch("select * from g5_write_event where wr_id='29' ");
	$row[wr_content]=str_replace("font-size","_font-size", $row[wr_content] );
	echo $row[wr_content];
	?>
	</div>
</div>
<? include $_SERVER['DOCUMENT_ROOT'] . "/include/_my_reser_script.inc.php";?>
<? include "../include/footer.php"; ?>