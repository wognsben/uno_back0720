<? include "../include/header.php"; 
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

if(!$member[mb_id]) {
	alert("로그인후 이용 가능합니다.","/contents/login.php?url=reservation");
}

if($rid) {
	$row=sql_fetch("select * from tour_reg where id='$rid'");
	$nation=$row['nation'];
	if(!$row[id]) {
		alert("잘못된 예약 번호입니다.","/");
		exit;
	}
}
else if($res_ok) {
	//$row=sql_fetch("select * from tour_reg where id='$rid'");
	$row=sql_fetch("select * from tour_reg where id='$res_ok'");
	$nation=$row['nation'];
	$rid=$res_ok;
}
else {
	alert("잘못된 예약 번호입니다.","/");
	exit;
}

if($agree && !$res_ok) {
	?>
<div id="container">
	<div class="sub-heading" style="background-image:url(../images/sub/sub_visual5.jpg)">
		<div class="cell">
			<h2>예약하기</h2>
		</div>
	</div>
	<div class="real-cont">
		<div class="contain">
			<div class="inner-wrap">
				<!--// content -->		
				<form  id="res_form" name="res_form" method="post"   _action="reservation3.php">
				<input type="hidden" name="rid" value="<?=$rid?>">
				<input type="hidden" name="is_ver" value="v2">
				<input type="hidden" name="is_booking" value="y">
				<? if($nation=="패키지") {?>
					<div class="order-form">
						<table>
							<colgroup>
								<col style="width:220px">
								<col style="width:*">
								<col style="width:20%">
								<col style="width:20%">
							</colgroup>
							<thead>
								<tr>
									<th colspan="2">투어상품정보</th>
									<th>인원</th><th>투어일</th>
									<th>총 상품금액</th>
								</tr>
							</thead>
							<tbody>
							<?
								$img_width=180;
								$img_height=110;

								$where[]= str2qry("id", $rid, "no");
								$where[]= "r.pid=p.wr_id ";

								if(count($where)) $sql_search=" where ". implode(" and ", $where);
								 $sql="select r.*, p.wr_subject , p.wr_2, p.wr_event_option, p.wr_event_course, p.is_ticket, p.is_passport , p.is_delivery, p.is_roominfo,  p.carlendar_max_m from tour_reg as r, g5_write_product p  $sql_search  ";
         
								$rs=sql_query($sql);
								while ($row=sql_fetch_array($rs)) {


									if($row[is_delivery] ) {
										 $is_delivery="1"; //배송주소 등록
									}
									if($row[is_roominfo] ) {
										 $is_roominfo="1"; //룸타입 입력 여부
									}
									if($row[is_passport])  $is_passport="1"; 

									$mb_cnt_no=$row['membCnt'];
									
									$thumb = get_list_thumbnail("product",  $row['pid'], $img_width, $img_height,  $is_create, $is_crop, $crop_mode,$is_sharpen, $um_value, $bf_no, $is_watermark); //src 만 처리
								?>

								<tr>
									<td class="pic"><a href="tour_view.php"><img src="<?=$thumb[src]?>" width="180" height="110" alt=""></a></td>
									<td class="info"><a href="tour_view.php?pid=<?=$row[pid]?>" target="_blank"><?=stripslashes($row[wr_subject])?> <?//=stripslashes($row[wr_2])?></a></td>
									<td class="opt"><?=$row['membCnt']?>명</td>
									<td class="date"><?=$row[tourDay]?></td>
									<td class="price"><big><?=number_format($row[total_fee4])?></big>원</td>
								</tr>
								<?
									$total_fee1+=$row['total_fee1'];
									$total_fee2+=$row['total_fee2'];
									$total_fee3+=$row['total_fee3'];
									$total_fee_air+=$row['total_fee_air'];
								}?>
								
							</tbody>
						</table>
					</div>
					<div class="order-tot">
						<table>
							<colgroup>
								<col style="width:auto">
								<col style="width:15%">
								<col style="width:20%">
							</colgroup>
							<tbody>
								<tr>
									<td class="tit" rowspan="4">총 투어 금액</td>
									<td class="txt">예약금</td>
									<td class="price"><big><?=number_format($total_fee1)?></big>원</td>
								</tr>
								<? if($total_fee2) {?>
								<tr>
									<td class="txt">중도금</td>
									<td class="price "><?=number_format($total_fee2)?></big>원</td>
								</tr>
								<?}?>
								<? if($total_fee_air) {?>
								<tr>
									<td class="txt">항공비</td>
									<td class="price"><?=number_format($total_fee_air)?></big>원</td>
								</tr>
								<?}?>

								<tr>
									<td class="txt">잔금</td>
									<td class="price"><?=number_format($total_fee3)?></big>원</td>
								</tr>
							</tbody>
						</table>
					</div>
					<ul class="help-txt text-sky  mgb15">
							<li>- 1차결제 :  예약금은 투어 신청시 바로 결제 가능 합니다. </li>
							<li>- 2차결제 :  모객 완료 후 중도금과 항공비 결제가 가능하며, 관련한 안내사항을 개별(메일 또는 유선) 연락드립니다. </li>
							<li>- 3차결제 :  여행 출발 30일전 여행 비용의 잔금 결제를 위해 개별(메일 또는 유선)  연락드립니다.  </li>
						</ul>
                <?} else if(stripos($nation, '세미패키지') !== false) {?>
                    <div class="order-form">
                        <table>
                            <colgroup>
                                <col style="width:220px">
                                <col style="width:*">
                                <col style="width:20%">
                                <col style="width:20%">
                            </colgroup>
                            <thead>
                            <tr>
                                <th colspan="2">투어상품정보</th>
                                <th>인원</th>
                                <th>투어일</th>
                                <th>총 상품금액</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?
                            $img_width=180;
                            $img_height=110;

                            $where[]= str2qry("id", $rid, "no");
                            $where[]= "r.pid=p.wr_id ";

                            if(count($where)) $sql_search=" where ". implode(" and ", $where);
//                            $sql="select r.*, p.wr_subject , p.wr_2, p.wr_event_option, p.wr_event_course, p.is_ticket, p.is_passport , p.is_delivery, p.is_roominfo,  p.carlendar_max_m from tour_reg as r, g5_write_product as p  $sql_search  ";
                            $sql="select r.*, p.* from tour_reg as r, g5_write_product as p  $sql_search  ";
                            
                            
//                            select r.*, p.* from tour_reg as r, g5_write_product as p where ( id = '57595' ) and r.pid=p.wr_id
//                            echo $sql;
                            
                            $rs=sql_query($sql);
                            while ($row=sql_fetch_array($rs)) {

                                if($row[is_delivery] ) {
                                    $is_delivery="1"; //배송주소 등록
                                }
                                if($row[is_roominfo] ) {
                                    $is_roominfo="1"; //룸타입 입력 여부
                                }
                                if($row[is_passport])  $is_passport="1";

                                $mb_cnt_no=$row['membCnt'];

                                $thumb = get_list_thumbnail("product",  $row['pid'], $img_width, $img_height,  $is_create, $is_crop, $crop_mode,$is_sharpen, $um_value, $bf_no, $is_watermark); //src 만 처리
                                ?>

                                <tr>
                                    <td class="pic">
                                        <a href="tour_view.php?pid=<?=$row[pid]?>">
                                            <img src="<?=$thumb[src]?>" width="180" height="110" alt="">
                                        </a>
                                    </td>
                                    <td class="info">
                                        <a href="tour_view.php?pid=<?=$row[pid]?>" target="_blank">
                                            <?=stripslashes($row[wr_subject])?> <?//=stripslashes($row[wr_2])?>
                                        </a>
                                    </td>
                                    <td class="opt"><?=$row['membCnt']?>명</td>
                                    <td class="date"><?=$row[tourDay]?></td>
                                    <td class="price"><big><?=number_format($row[fee_org])?></big>원</td>
                                </tr>
                                <?
                                $total_fee1+=$row['total_fee1'];
                                $total_fee2+=$row['total_fee2'];
                                $total_fee3+=$row['total_fee3'];
                                $total_fee_air+=$row['total_fee_air'];
                            }?>

                            </tbody>
                        </table>
                    </div>
                    <div class="order-tot">
                        <table>
                            <colgroup>
                                <col style="width:auto">
                                <col style="width:15%">
                                <col style="width:20%">
                            </colgroup>
                            <tbody>
                            <tr>
                                <td class="tit" rowspan="4">총 투어 금액</td>
                                <td class="txt">예약금</td>
                                <td class="price"><big><?=number_format($total_fee1)?></big>원</td>
                            </tr>
                            <? if($total_fee2) {?>
                                <tr>
                                    <td class="txt">중도금</td>
                                    <td class="price "><?=number_format($total_fee2)?></big>원</td>
                                </tr>
                            <?}?>
                            <? if($total_fee_air) {?>
                                <tr>
                                    <td class="txt">항공비</td>
                                    <td class="price"><?=number_format($total_fee_air)?></big>원</td>
                                </tr>
                            <?}?>

                            <tr>
                                <td class="txt">잔금</td>
                                <td class="price"><?=number_format($total_fee3)?></big>원</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <ul class="help-txt text-sky  mgb15">
                        <li>- 1차결제 :  예약금은 투어 신청시 바로 결제 가능 합니다. </li>
                        <li>- 2차결제 :  모객 완료 후 중도금과 항공비 결제가 가능하며, 관련한 안내사항을 개별(메일 또는 유선) 연락드립니다. </li>
                        <li>- 3차결제 :  여행 출발 30일전 여행 비용의 잔금 결제를 위해 개별(메일 또는 유선)  연락드립니다.  </li>
                    </ul>
                <?} else {?>

					<div class="order-form">
						<table>
							<colgroup>
								<col style="width:220px">
								<col style="width:auto">
								<col style="width:12%">
								<col style="width:10%">
							</colgroup>
							<thead>
								<tr>
									<th colspan="2">투어상품정보 <p class="fr"><span class="mgr10">인원</span> <span class="mgl120">투어일</span></p></th>
									<th>예약금</th>
									<th>현지지불금</th>
								</tr>
							</thead>
							<tbody>
							<?
								$img_width=180;
								$img_height=110;

								$where[]= str2qry("id", $rid, "no");
								$where[]= "r.pid=p.wr_id ";

								if(count($where)) $sql_search=" where ". implode(" and ", $where);
								 $sql="select r.*, p.wr_subject , p.wr_2, p.wr_event_option, p.wr_event_course, p.is_ticket, p.is_passport , p.is_delivery, p.is_roominfo,  p.carlendar_max_m from tour_reg as r, g5_write_product p  $sql_search  ";
								$rs=sql_query($sql);
								while ($row=sql_fetch_array($rs)) {
									$event_tour_a=explode(",",$row[wr_event_course]);
									if($row[is_ticket]) {
										$is_ticket="1"; 
										$ticket_rid[]=$row[id]; //티켓이 필요한 reg id를 배열로 전달
									}
									if($row[is_delivery] ) {
										 $is_delivery="1"; //배송주소 등록
									}
									//$is_delivery="1";
									if($row[is_roominfo] ) {
										 $is_roominfo="1"; //룸타입 입력 여부
									}
									if($row[pid]=="74" || $row[pid]=="75" || in_array("74", $event_tour_a) || in_array("75", $event_tour_a)) {
										 $is_Uffizi="1"; //우피치 투어 
										 if( in_array("74", $event_tour_a) || in_array("75", $event_tour_a) ) $is_Uffizi2="1";//우피치 투어 이벤트일 경우
									}
									if($row[is_passport])  $is_passport="1"; 
									$thumb = get_list_thumbnail("product",  $row['pid'], $img_width, $img_height,  $is_create, $is_crop, $crop_mode,$is_sharpen, $um_value, $bf_no, $is_watermark); //src 만 처리
								?>

								<tr>
									<td class="pic"><a href="tour_view.php"><img src="<?=$thumb[src]?>" width="180" height="110" alt=""></a></td>
									<td class="info">
										<table>
											<colgroup>
												<col style="width:auto">
												<col style="width:10%">
												<col style="width:28%">
											</colgroup>
											<tbody>
												<tr>
													<td colspan="2" class="tit"><a href="tour_view.php?pid=<?=$row[pid]?>" target="_blank"><?=stripslashes($row[wr_subject])?> <?//=stripslashes($row[wr_2])?></a></td>
													<td class="date"><?=$row[tourDay]?></td>
												</tr>
												<?
												//선택한 인원 요금 표시, 국제 학생증 인원, 입장권 id  리턴
												list( $mb_list, $ISEC_cnt,  $mb_cnt_no) =booking_mb_list("cart", $row); 
												echo $mb_list;
												?>
												<?=booking_event_tour($row);//이벤트 투어가 있으면 이벤트 투어 표시. 날자 선택 필수?>
												<?
												
												/*
												<tr>
													<td class="opt">- 만 6세 이하(유모차 반입 금지) / 0원 / 10유로</td>
													<td class="opt">2명</td>
													<td class="opt">60,000원 / 0유로</td>
												</tr>
												<tr>
													<td class="opt">- 만 7세 이상 ~ 만 26세 이하 / 20,000원 /  0유로</td>
													<td class="opt">1명</td>
													<td class="opt">20,000원 / 0유로</td>
												</tr>
												<tr>
													<td class="opt">- 만 27세 이상 / 30,000원 /  0유로</td>
													<td class="opt">2명</td>
													<td class="opt">0원 / 10유로</td>
												</tr> */?>
											</tbody>
										</table>
									</td>
									<td class="price"><big><?=number_format($row[total_fee1])?></big>원</td>
									<td class="price"><?=number_format($row[total_fee2])?"<big>".number_format($row[total_fee2])."</big>유로":"없음"?></td>
								</tr>
								<?
									$tt_fee1+=$row[total_fee1];
									$tt_fee2+=$row[total_fee2];

								}?>
								
							</tbody>
						</table>
					</div>
					<div class="order-tot">
						<table>
							<colgroup>
								<col style="width:auto">
								<col style="width:15%">
								<col style="width:15%">
							</colgroup>
							<tbody>
								<tr>
									<td class="tit" rowspan="2">총 투어 금액</td>
									<td class="txt">예약금</td>
									<td class="price"><big><?=number_format($tt_fee1)?></big>원</td>
								</tr>
								<tr>
									<td class="txt">현지지불금</td>
									<td class="price"><?=number_format($tt_fee2)?"<big>".number_format($tt_fee2)."</big>유로":"없음"?></td>
								</tr>
							</tbody>
						</table>
					</div>
					<?}?>

					<br>
					<br>


					<div class="order-form">
						<!-- <p class="tour-tit">남부 아말피 코스트투어 &nbsp; SOUTH AMALFI COAST TOUR</p> -->
						<h3>투어 신청자 정보</h3>
						<ul class="help-txt text-red  mgb15">
							<!-- <li>- 여행자 전원의 여권상 영문이름, 생년월일(주민번호 앞 6자리), 성별은 필수 입력사항입니다.</li> -->
							<li>- 해당 정보의 기입 오기로 인해 발생하는 불이익에 대해서는 당사에서 도움을 드릴 수 없습니다.</li>
							<li>- 네이버 / 카카오톡 / 구글 계정으로 로그인한 경우 필히 신청자 정보를 작성 해주셔야 합니다.(미작성시 결제확인에 문제 발생 할 수 있습니다.)</li>
						</ul>
						<table>
							<colgroup>
								<col style="width:16%">
								<col style="width:21%">
								<col style="width:32%">
								<col style="width:21%">
							</colgroup>
							<thead>
								<tr>
									<th>이름</th>
									<th>연락처</th>
									<th>이메일</th>
									<th>카카오톡 ID</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><input type="text" name="mb_name" value="<?=$member[mb_name]?>" class="input" style="width:100%" placeholder="한글이름" required></td>
									<td><input type="text" name="mb_hp" value="<?=$member[mb_hp]?>" class="input" style="width:100%" placeholder="연락처" required></td>
									<td><input type="text" name="mb_email"  value="<?=$member[mb_email]?>"class="input" style="width:100%" placeholder="이메일" required></td>
									<td><input type="text" name="mb_kakao" value="<?//=$member[mb_name]?>" class="input" style="width:100%" placeholder="카카오톡 ID" _required></td>
								</tr>
								
							</tbody>
						</table>
						<? 
						if($is_delivery) {
								$zip_case="2"; //우편 번호 새창
								echo G5_POSTCODE_JS;
							?>
						<br><br>
						<h3>배송지 정보</h3>
						<ul class="help-txt text-red  mgb15">
							<li>- 해당정보는 이벤트 상품수령을 위해 기재하고 있으며, 배송지 정보가 확인된 예약 건에 한해서 이벤트 상품 수령이 가능합니다.</li>
							<!-- <li>- 이벤트 상품 수령 후 투어 취소하는 경우 가이드북과 선스틱금액을 제하고 환불 됩니다. (가이드북과 선스틱은 개별발송됩니다)</li> -->
							<li>-  이벤트 상품 수령 후 투어 취소 하는 경우 상품금액을 제하고 투어비 환불진행됩니다. </li>

						</ul>
						<table>
								<colgroup>
									<col style="width:10%">
									<col style="width:auto">
									<col style="width:40%">
								</colgroup>
							<tbody>
								<tr>
									<th>사은품 선택</th>
									<td colspan="2" style="text-align:left">혜택 1 &nbsp; 
										<select name="gift_1" class="select" required style="width:200px" >
											<option value="" selected>선택해 주세요
											<option value="선택안함" >선택안함
											<option value="유럽여행백서" >유럽여행백서
											<option value="이탈리아여행백서" >이탈리아여행백서
										</select>
										<? /* 2020-01-06 삭제
										&nbsp; &nbsp; &nbsp; 혜택 2 &nbsp; 
										<select name="gift_2" class="select" required style="width:200px" >
											<option value="" selected>선택해 주세요
											<option value="선택안함" >선택안함
											<option value="선스틱" disabled >선스틱(품절)
										</select> */?>
									</td>
								</tr>
								<tr>
									<td ><input type="text" name="zip" value="" class="input" style="width:100%" placeholder="우편번호" readonly required onclick="win_zip('res_form', 'zip', 'addr1', 'addr2', 'addr3', 'addr_jibeon');"></td>
									<td ><input type="text" name="addr1" value="" class="input" style="width:100%" placeholder="주소" required onclick="win_zip('res_form', 'zip', 'addr1', 'addr2', 'addr3', 'addr_jibeon');"></td>
									<td >
										<input type="text" name="addr2"  value=""class="input" style="width:100%" placeholder="상세주소" required>
										<input type="hidden" name="addr3">
										<input type="hidden" name="addr_jibeon">
									</td>
								</tr>
								
							</tbody>
						</table>
						
						<ul class="help-txt text-red  mgb15">
							<li><input type="checkbox" name="gift_chk" value="1" required> 이벤트 상품 수령 후 투어 취소 하는 경우 상품금액을 제하고 투어비 환불진행됩니다. </li>
						</ul>
						<?}?>
					</div>
					

					<? if($ISEC_cnt>0) {?>
					<br>
					<br>
					<div class="order-form">
						<!-- <p class="tour-tit">남부 아말피 코스트투어 &nbsp; SOUTH AMALFI COAST TOUR</p> -->
						<h3>국제 학생증 정보</h3>
						<ul class="help-txt mgb15">
							<!-- <li>- 여행자 전원의 여권상 영문이름, 생년월일(주민번호 앞 6자리), 성별은 필수 입력사항입니다.</li> -->
							<li>* 국제학생증을 소지하신분은 아래에 성함, 생년 월일, 국제학생증 유효기간을(만료일) 표기해 주시기 바랍니다.</li>
							<li>* 국제학생증 만료일은 발급 받은 날짜 기준 1년 또는 2년 입니다. (월/년)</li>
							<li>* 투어당일 국제학생증을 소지하지않는 경우 입장권은 성인 비용(17유로) 추가 지불 해 주셔야합니다.</li>
						</ul>
						<table>
							<colgroup>
								<col style="width:16%">
								<col style="width:21%">
								<col style="width:32%">
								<col style="width:21%">
								<col style="width:21%">
							</colgroup>
							<thead>
								<tr>
									<th>한글이름</th>
									<th>영문이름(여권)</th>
									<th>국제 학생증 번호</th>
									<th>유효기간</th>
									<th>생년월일</th>
								</tr>
							</thead>
							<tbody>
							<?
							//$mb_cnt_a=explode("|",$row[membCnt]);
							for($i=1; $i<=$ISEC_cnt; $i++) {
							
							?>
								<tr>
									
									<td><input type="text" name="ISEC_name[]" class="input ISEC" style="width:100%" placeholder="한글이름" required></td>
									<td>
										<input type="text" name="ISEC_ename_1[]" class="input ISEC" style="width:45%" placeholder="성" required> 
										<input type="text" name="ISEC_ename_2[]" class="input ISEC" style="width:45%" placeholder="이름" required></td>
									<td><input type="text" name="ISEC_no[]" class="input ISEC" style="width:100%" placeholder="국제 학생증 번호 " required></td>
									<td><input type="text" name="ISEC_expired[]" class="input ISEC" style="width:70%" placeholder="YYYY-MM" required></td>
									<td><input type="text" name="ISEC_birth[]" class="input ISEC" style="width:70%" placeholder="YYYY-MM-DD" required></td>
								</tr>
								
								<?
							}?>
							</tbody>
						</table>
					</div>
					<?}?>
					<? if($is_ticket && 0) {
						echo get_ticket_fee($ticket_rid);
					}?>

					<? if($is_passport) {?>
					<br>
					<br>
					<div class="order-form">
						<!-- <p class="tour-tit">남부 아말피 코스트투어 &nbsp; SOUTH AMALFI COAST TOUR</p> -->
						<h3>여권 정보</h3>
						<ul class="help-txt text-red mgb15">
							<li>- 해당 정보의 기입 오기로 인해 발생하는 불이익에 대해서는 당사에서 도움을 드릴 수 없습니다.</li>
							<!-- <li>- 원하는 룸타입은 기타사항에 남겨주세요. (더블룸/트윈룸/싱글룸/트리플룸)</li> -->
						</ul>
						<table>
							<colgroup>
								<!-- <col style="width:16%">
								<col style="width:21%">
								<col style="width:32%">
								<col style="width:21%">
								<col style="width:21%"> -->
							</colgroup>
							<thead>
								<tr>
									<th>한글이름</th>
									<th>영문이름</th>
									<th>생년월일</th>
									<th>여권번호</th>
									<th>여권만료일</th>
									<th>성별</th>
									
								</tr>
							</thead>
							<tbody>
							<?
							
							//$mb_cnt_a=explode("|",$row[membCnt]);
							for($i=1; $i<=$mb_cnt_no; $i++) {
							
							?>
								<tr>
									<td><input type="text" name="passport_name_ko[<?=$i?>]" class="input" style="width:100%" placeholder="한글이름 " required></td>
									<td><input type="text" name="passport_name_en[<?=$i?>]" class="input" style="width:70%" placeholder="영문이름" required></td>
									<td><input type="text" name="passport_birth[<?=$i?>]" class="input" style="width:70%" placeholder="YYYY-MM-DD" required></td>
									<td><input type="text" name="passport_no[<?=$i?>]" class="input" style="width:100%" placeholder="여권번호" required></td>
									<td><input type="text" name="passport_expired[<?=$i?>]" class="input"  style="width:100%"  placeholder="여권만료일" required></td>
									<td>
										<label><input type="radio" name="passport_sex[<?=$i?>]" class="passport_sex" value="남" required> 남</label>
										<label><input type="radio" name="passport_sex[<?=$i?>]" class="passport_sex" value="여" required> 여</label>
									</td>
									
								</tr>
								
								<?
							}?>
							</tbody>
						</table>
					</div>
					<?}?>
					<? if($is_roominfo) {?>
					<br>
					<br>
					<div class="order-form">
						<!-- <p class="tour-tit">남부 아말피 코스트투어 &nbsp; SOUTH AMALFI COAST TOUR</p> -->
						<h3>룸타입 정보</h3>
						<? if($nation=="패키지") {?>
						<ul class="help-txt text-red mgb15">
							<li>- 원하는 룸타입을 작성해주세요. (트윈룸(룸쉐어) / 더블룸 / 트윈룸 / 싱글룸 / 트리플룸)</li>
							<li>- 기본 2인 1실로 룸배정 됩니다. 별도의 요청이 없는 경우, 동일한 성별의 1인 고객님이 있을 경우 룸메이트는 자동 배정(트윈룸(룸쉐어))됩니다. 단 해당일 룸메이트 배정되지 못한 경우는 싱글자치 발생됩니다.</li>
							<li>- 호텔 1인실(싱글룸) 사용을 원하시는 경우 싱글차지 발생되기에 예약시 메모란에 요청 해주셔야 합니다.</li>
						</ul>
                        <?} else if(stripos($nation, '세미패키지') !== false) {?>
                        <ul class="help-txt text-red mgb15">
                            <li>- 원하는 룸타입을 작성해주세요. (트윈룸(룸쉐어) / 더블룸 / 트윈룸 / 싱글룸 / 트리플룸)</li>
                            <li>- 기본 2인 1실로 룸배정 됩니다. 별도의 요청이 없는 경우, 동일한 성별의 1인 고객님이 있을 경우 룸메이트는 자동 배정(트윈룸(룸쉐어))됩니다. 단 해당일 룸메이트 배정되지 못한 경우는 싱글자치 발생됩니다.</li>
                            <li>- 호텔 1인실(싱글룸) 사용을 원하시는 경우 싱글차지 발생되기에 예약시 메모란에 요청 해주셔야 합니다.</li>
                        </ul>
						<?} else {?>
						<ul class="help-txt text-red mgb15">
							<li>- 원하는 룸타입을 작성해주세요. (트윈룸(룸쉐어) / 더블룸 / 트윈룸 / 싱글룸 / 트리플룸)</li>
							<li>- 호텔 1인실(싱글룸) 사용을 원하시는 경우 싱글차지 40유로가 추가 발생 되며 예약시 메모란에 요청 해주셔야 합니다.</li>     
							<li>- 기본 2인 1실로 룸배정 되며, 동일한 성별의 1인 고객님이 있을 경우 룸메이트는 자동 배정됩니다. 단 해당일 룸메이트 배정되지 못한 경우는 싱글자치 20유로 발생됩니다.</li>
						</ul>
						<?}?>
						<textarea name="roominfo" class="memo" required placeholder="인원에 맞게 룸타입, 이름 작성해주세요.&#13;&#10;&#13;&#10;(룸타입 예시) &#13;&#10;트윈룸(룸쉐어) - 홍길돌  /  더블룸 - 홍길동, 춘향이  /  트윈룸 - 향단이, 향난이  /  트리플룸 - 홍길동, 홍길돌, 홍길길&#13;&#10;&#13;&#10;(잘못된 예시)  &#13;&#10;더블배드룸 - 홍길동 (X) 이경우 자동으로 동일한 성별의 1인과 룸쉐어로 방 배정 됩니다. &#13;&#10;트리플룸 - 홍길동, 춘향이 (X) 이경우 자동으로 트윈룸으로 방 배정 됩니다. "></textarea>
					</div>
					<?}?>

					<br>
					<br>
					<br>
					
					
					<div class="order-form">
						<h3>기타사항</h3>
						<? if($nation=="패키지") {?>
						<ul class="help-txt mgb15">
							<li>- 예약금 결제 완료 후 2차결제와 3차결제를 위해 개별 연락드립니다. </li>
							<li>- 업무 시간 내 통화 가능한 시간을 표기해주세요. (월~금(주말,공휴일 휴무) 10:00~15:00)</li>
							<li>- 연락 받을 전화번호 표기해주세요. (미 작성시, 투어 신청자 정보란에 표기된 번호로 연락)</li>
						</ul>
                        <?} else if(stripos($nation, '세미패키지') !== false) {?>
                        <ul class="help-txt mgb15">
                            <li>- 예약금 결제 완료 후 2차결제와 3차결제를 위해 개별 연락드립니다. </li>
                            <li>- 업무 시간 내 통화 가능한 시간을 표기해주세요. (월~금(주말,공휴일 휴무) 10:00~15:00)</li>
                            <li>- 연락 받을 전화번호 표기해주세요. (미 작성시, 투어 신청자 정보란에 표기된 번호로 연락)</li>
                        </ul>
						<?} else {?>
						<ul class="help-txt mgb15">
							<li>- 투어 신청비용 선 결제자 순으로 예약확정 됩니다.</li>
							<li>- 투어 신청비용 선 결제 없이 당일 전액 현지에서 현장지불하길 원하신다면 별도의 연락을 주셔야 합니다.</li>
							<? if($is_Uffizi || $is_Uffizi2) {//우피치
								if($is_Uffizi || $is_Uffizi2) { ?>
									<li class="help-txt text-red">- 투어참여자 중에서 만 18세미만 청소년이 있는경우, 투어당일 여권원본 반드시 지참 해야만 합니다. (여권 사본지참, 여권원본 미지참 시 입장불가/투어불가능)</li>
									<li class="help-txt text-red">- 피렌체카드 및 아미치카드 등 통합패스권을 소지하신 분들은 예약시 미리 알려주셔야 카드사용이 가능합니다. <br>
									 &nbsp;  (입장권을 사전예약 구매하여 투어 진행 되기에 카드 소지여부 알리지 않은 경우 입장료 1인당 성수기 24유로, 비수기 16유로씩 비용지불 해야만 합니다)</li>
									<!-- <li>- 이벤트로 제공 되는 우피치 투어 참여자 중에서 만 18세미만 청소년이 있는경우 기타 하상에 필히 작성해주셔야 합니다.<br> &nbsp;    (별도의 메모내용 없는 경우 성입 입장권으로 구매 됩니다)
									<li>- 투어당일 여권원본 반드시 지참 해야만 합니다.<br> &nbsp;  (여권 사본지참, 여권원본 미지참 시 입장불가/투어불가능)</li>
									<li>	- 피렌체카드 및 아미치카드 소지하신 분들은 예약시 미리 알려주셔야 카드사용이 가능합니다. <br> &nbsp; 
										  (입장권을 사전예약 구매하여 투어 진행 되기에 카드 소지여부 알리지 않은 경우 입장료 1인당 성수기 24유로, 비수기 16유로씩 비용지불 해야만 합니다)
										</li> -->
									<?
								}
								else {?>
									<li>- 투어참여자 중에서 만 18세미만 청소년이 있는경우, 투어당일 여권원본 반드시 지참 해야만 합니다.<br> &nbsp;   (여권 사본지참, 여권원본 미지참 시 입장불가/투어불가능)</li>
									<li>- 피렌체카드 및 아미치카드 소지하신 분들은 예약시 미리 알려주셔야 카드사용이 가능합니다. <br> &nbsp;  (입장권을 사전예약 구매하여 투어 진행 되기에 카드 소지여부 알리지 않은 경우 입장료 1인당 성수기 24유로, 비수기 16유로씩 비용지불 해야만 합니다)</li>
									<?}
							}?>
						</ul>
						<?}?>
						<textarea name="regMemo" class="memo" placeholder="기타 사항을 입력하세요."></textarea>
					</div>

					<div class="buttons">
						<div class="cen">
							<input type="submit" value="확인" class="btn-pack large focus round">
						</div>
					</div>
				</form>
				<!-- content //-->
			</div>
		</div>
	</div>
</div><!-- end container -->
<? include $_SERVER['DOCUMENT_ROOT'] . "/include/_reservation_script.inc.php";?>

<?}
else if($res_ok) { //예약 완료?>
<div id="container">
	<div class="sub-heading" style="background-image:url(../images/sub/sub_visual5.jpg)">
		<div class="cell">
			<h2>예약하기</h2>
		</div>
	</div>
	<div class="real-cont">
		<div class="contain">
			<div class="inner-wrap">
				<!--// content -->
				<form action="">
					<div class="reser-head">
						<h3 style="font-weight:300;">다음과 같이 투어 예약이 신청되었습니다.</h3>
					</div>
					<? if($nation=="패키지") {?>
					<div class="order-form">
						<table>
							<colgroup>
								<col style="width:220px">
								<col style="width:*">
								<col style="width:20%">
								<col style="width:20%">
							</colgroup>
							<thead>
								<tr>
									<th colspan="2">투어상품정보</th>
									<th>인원</th><th>투어일</th>
									<th>총 상품금액</th>
								</tr>
							</thead>
							<tbody>
							<?
								$img_width=180;
								$img_height=110;

								$where[]= str2qry("id", $rid, "no");
								$where[]= "r.pid=p.wr_id ";

								if(count($where)) $sql_search=" where ". implode(" and ", $where);
								  $sql="select r.*, p.wr_subject , p.wr_2, p.wr_event_option, p.wr_event_course, p.is_ticket, p.is_passport , p.is_delivery, p.is_roominfo,  p.carlendar_max_m from tour_reg as r, g5_write_product p  $sql_search  ";
								$rs=sql_query($sql);
								while ($row=sql_fetch_array($rs)) {

									
									$thumb = get_list_thumbnail("product",  $row['pid'], $img_width, $img_height,  $is_create, $is_crop, $crop_mode,$is_sharpen, $um_value, $bf_no, $is_watermark); //src 만 처리
								?>

								<tr>
									<td class="pic"><a href="tour_view.php"><img src="<?=$thumb[src]?>" width="180" height="110" alt=""></a></td>
									<td class="info"><a href="tour_view.php?pid=<?=$row[pid]?>" target="_blank"><?=stripslashes($row[wr_subject])?> <?//=stripslashes($row[wr_2])?></a></td>
									<td class="opt"><?=$row['membCnt']?>명</td>
									<td class="date"><?=$row[tourDay]?></td>
									<td class="price"><big><?=number_format($row[total_fee4])?></big>원</td>
								</tr>
								<?
									$total_fee1+=$row['total_fee1'];
									$total_fee2+=$row['total_fee2'];
									$total_fee3+=$row['total_fee3'];
									$total_fee_air+=$row['total_fee_air'];

									$tt_fee1+=$row[total_fee1];
								}?>
								
							</tbody>
						</table>
					</div>
					<div class="order-tot">
						<table>
							<colgroup>
								<col style="width:auto">
								<col style="width:15%">
								<col style="width:15%">
							</colgroup>
							<tbody>
								<tr>
									<td class="tit" rowspan="4">총 투어 금액</td>
									<td class="txt">예약금</td>
									<td class="price"><big><?=number_format($total_fee1)?></big>원</td>
								</tr>
								<? if($total_fee2) {?>
								<tr>
									<td class="txt">중도금</td>
									<td class="price "><?=number_format($total_fee2)?></big>원</td>
								</tr>
								<?}?>
								<? if($total_fee_air) {?>
								<tr>
									<td class="txt">항공비</td>
									<td class="price"><?=number_format($total_fee_air)?></big>원</td>
								</tr>
								<?}?>

								<tr>
									<td class="txt">잔금</td>
									<td class="price"><?=number_format($total_fee3)?></big>원</td>
								</tr>
							</tbody>
						</table>
					</div>
                    <?} else if (stripos($nation, '세미패키지') !== false) {?>
                        <div class="order-form">
                            <table>
                                <colgroup>
                                    <col style="width:220px">
                                    <col style="width:*">
                                    <col style="width:20%">
                                    <col style="width:20%">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th colspan="2">투어상품정보</th>
                                    <th>인원</th><th>투어일</th>
                                    <th>총 상품금액</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?
                                $img_width=180;
                                $img_height=110;

                                $where[]= str2qry("id", $rid, "no");
                                $where[]= "r.pid=p.wr_id ";

                                if(count($where)) $sql_search=" where ". implode(" and ", $where);
                                $sql="select r.*, p.wr_subject , p.wr_2, p.wr_event_option, p.wr_event_course, p.is_ticket, p.is_passport , p.is_delivery, p.is_roominfo,  p.carlendar_max_m from tour_reg as r, g5_write_product p  $sql_search  ";
                                $rs=sql_query($sql);
                                while ($row=sql_fetch_array($rs)) {


                                    $thumb = get_list_thumbnail("product",  $row['pid'], $img_width, $img_height,  $is_create, $is_crop, $crop_mode,$is_sharpen, $um_value, $bf_no, $is_watermark); //src 만 처리
                                    ?>

                                    <tr>
                                        <td class="pic"><a href="tour_view.php"><img src="<?=$thumb[src]?>" width="180" height="110" alt=""></a></td>
                                        <td class="info"><a href="tour_view.php?pid=<?=$row[pid]?>" target="_blank"><?=stripslashes($row[wr_subject])?> <?//=stripslashes($row[wr_2])?></a></td>
                                        <td class="opt"><?=$row['membCnt']?>명</td>
                                        <td class="date"><?=$row[tourDay]?></td>
                                        <td class="price"><big><?=number_format($row[total_fee4])?></big>원</td>
                                    </tr>
                                    <?
                                    $total_fee1+=$row['total_fee1'];
                                    $total_fee2+=$row['total_fee2'];
                                    $total_fee3+=$row['total_fee3'];
                                    $total_fee_air+=$row['total_fee_air'];

                                    $tt_fee1+=$row[total_fee1];
                                }?>

                                </tbody>
                            </table>
                        </div>
                        <div class="order-tot">
                            <table>
                                <colgroup>
                                    <col style="width:auto">
                                    <col style="width:15%">
                                    <col style="width:15%">
                                </colgroup>
                                <tbody>
                                <tr>
                                    <td class="tit" rowspan="4">총 투어 금액</td>
                                    <td class="txt">예약금</td>
                                    <td class="price"><big><?=number_format($total_fee1)?></big>원</td>
                                </tr>
                                <? if($total_fee2) {?>
                                    <tr>
                                        <td class="txt">중도금</td>
                                        <td class="price "><?=number_format($total_fee2)?></big>원</td>
                                    </tr>
                                <?}?>
                                <? if($total_fee_air) {?>
                                    <tr>
                                        <td class="txt">항공비</td>
                                        <td class="price"><?=number_format($total_fee_air)?></big>원</td>
                                    </tr>
                                <?}?>

                                <tr>
                                    <td class="txt">잔금</td>
                                    <td class="price"><?=number_format($total_fee3)?></big>원</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    <?} else {?>
					<div class="order-form">
						<table>
							<colgroup>
								<col style="width:220px">
								<col style="width:auto">
								<col style="width:12%">
								<col style="width:10%">
							</colgroup>
							<thead>
								<tr>
									<th colspan="2">투어상품정보 <p class="fr"><span class="mgr10">인원</span> <span class="mgl120">투어일</span></p></th>
									<th>예약금</th>
									<th>현지지불금</th>
								</tr>
							</thead>
							<tbody>
							<?
								$img_width=180;
								$img_height=110;

								$where[]= "r.pid=p.wr_id ";

								$where_p[]= str2qry("id", $res_ok, "no");
								$where_p[] = "  ( isEvent='Y' and " . str2qry("parent_id", $res_ok, "no")." )";

								$where[]= " ( ". implode(" or ", $where_p ).")";
								

								if(count($where)) $sql_search=" where ". implode(" and ", $where);
								$sql="select r.*, p.wr_subject , p.wr_2, p.wr_event_option, p.wr_event_course, p.is_ticket from tour_reg as r, g5_write_product p  $sql_search  ";
								$rs=sql_query($sql);
								while ($row=sql_fetch_array($rs)) {
									if($row[is_ticket]) {
										$is_ticket="1"; 
										$ticket_rid[]=$row[id]; //티켓이 필요한 reg id를 배열로 전달
									}
									if($row[event_pid]) {
										$row_e=get_product_row($row[event_pid]);
										$row[wr_subject]=$row_e[wr_subject];
										$row[pid]=$row_e[wr_id];

									}
									else {
										
									}
									$thumb = get_list_thumbnail("product",  $row['pid'], $img_width, $img_height,  $is_create, $is_crop, $crop_mode,$is_sharpen, $um_value, $bf_no, $is_watermark); //src 만 처리
								?>

								<tr>
									<td class="pic"><a href="tour_view.php"><img src="<?=$thumb[src]?>" width="180" height="110" alt=""></a></td>
									<td class="info">
										<table>
											<colgroup>
												<col style="width:auto">
												<col style="width:10%">
												<col style="width:28%">
											</colgroup>
											<tbody>
												<tr>
													<td colspan="2" class="tit"><a href="tour_view.php?pid=<?=$row[pid]?>"><?=stripslashes($row[wr_subject])?> <?//=stripslashes($row[wr_2])?></a></td>
													<td class="date"><?=$row[tourDay]?></td>
												</tr>
												<?
												//선택한 인원 요금 표시, 국제 학생증 인원, 입장권 id  리턴
												
													list( $mb_list, $ISEC_cnt, $tk_ids) =booking_mb_list("res", $row);
												echo $mb_list;
												?>
												<?//=booking_event_tour($row);//이벤트 투어가 있으면 이벤트 투어 표시. 날자 선택 필수?>
												
											</tbody>
										</table>
									</td>
									<?
									if($row[isEvent]=="Y") {
										?>
										<td class="price" colspan="2"><span class="btn-pack large orange round" style="cursor:default">이벤트 투어</span></td>
										<?
									}
									else if($row[status]=="cart" || $row[status]=="booking" ) {
										$denied_msg[]=stripslashes($row[wr_subject]);
										$jan_arr=get_tour_jan_cnt($row[pid], $row[tourDay]);?>
										<td class="price" colspan="2">잔여 좌석 : <?=$jan_arr[ddCount]?></td>
										<?
									}
									else {?>
									<td class="price"><big><?=number_format($row[total_fee1])?></big>원</td>
									<td class="price"><?=number_format($row[total_fee2])?"<big>".number_format($row[total_fee2])."</big>유로":"없음"?></td>
									<?}?>
								</tr>
								<?
									if($row[status]=="cart" || $row[status]=="booking" ) {
									}
									else {
										$tt_fee1+=$row[total_fee1];
										$tt_fee2+=$row[total_fee2];
										$tt_fee4+=$row[total_fee4];
									}

								}?>
							</tbody>
						</table>
					</div>
					<?}?>
					<br>
					<br>
					<br>

					<? if(count($denied_msg)) {?>

					<div class="order-form">
						<h3>예약불가능 안내</h3>
						<div class="order-info-box">
							※ <?=implode(",",$denied_msg)?>는(은) 잔여 좌석이 부족하여 예약이 진행되지 않았습니다.<br>
							다른 날자를 이용해 주시면 감사하겠습니다.
						</div>
					</div>

					<br>
					<br>
					<br>
					<?}?>

					<div class="order-form">
						<table>
							<colgroup>
								<col style="width:25%">
								<col style="width:auto">							
							</colgroup>
							<tbody>
								<? if($tt_fee4) {?>
								<tr>
									<th>예약금 + 바티칸 입장권</th>
									<td class="tl"><?=number_format($tt_fee1+$tt_fee4)?>원 &nbsp; <span class="text-red">(<?=date("Y-m-d H",  time()+(12*3600) )?>시까지)</span></td>								
								</tr>
								<?} else { 
									if($nation=="패키지") {?>
									<tr>
										<th>예약금</th>
										<td class="tl">예약금 <?=number_format($tt_fee1)?>원 &nbsp; <span class="text-red">(<?=date("Y-m-d H",  time()+(36*3600) )?>시까지)</span></td>								
									</tr>
                                    <?} else if (stripos($nation, '세미패키지') !== false) {?>
                                    <tr>
                                        <th>예약금</th>
                                        <td class="tl">예약금 <?=number_format($tt_fee1)?>원 &nbsp; <span class="text-red">(<?=date("Y-m-d H",  time()+(36*3600) )?>시까지)</span></td>
                                    </tr>
									<?} else {?>
									<tr>
										<th>예약금</th>
										<td class="tl">예약금 <?=number_format($tt_fee1)?>원 &nbsp; <span class="text-red">(<?=date("Y-m-d H",  time()+(12*3600) )?>시까지)</span></td>								
									</tr>
								<?}
								}?>
								<? 
								if($tt_fee2) {?>
								<tr>
									<th>현장지불금</th>
									<td class="tl"><?=number_format($tt_fee2)?"<big>".number_format($tt_fee2)."</big>유로":"없음"?></td>								
								</tr>
								<?}?>
							</tbody>
						</table>
					</div>

					<br>
					<br>
					<br>
<!--                    --><?//} else if (stripos($nation, '세미패키지') !== false) {?>

					<div class="order-form">
						<h3>예약금 입금 유의사항</h3>
						<div class="order-info-box">
							<?
						if($nation=="제주도") echo stripslashes($config['cf_res_ok_jeju_pc']);
						else if($nation=="패키지") echo stripslashes($config['cf_res_ok_pkg_pc']);
                        else if (stripos($nation, '세미패키지') !== false) echo stripslashes($config['cf_res_ok_pkg_pc']);
						//else if($nation=="티켓") echo stripslashes($config['cf_ticket_pc']);
							else echo stripslashes($config['cf_res_ok_pc']);
						?>
						</div>
					</div>

					<div class="buttons">
						<div class="cen">							
							<!-- <input type="submit" value="결제하기" class="btn-pack large focus round" onclick="req_pay(\''.$row[id].'\');" > -->
							<a href="my_reser.php" class="btn-pack large round">마이페이지</a>
						</div>
					</div>
				</form>
				<!-- content //-->
			</div>
		</div>
	</div>
</div><!-- end container -->
<? include $_SERVER['DOCUMENT_ROOT'] . "/include/_my_reser_script.inc.php";?>

<?
}
else { //약관 동의	?>
<div id="container">
	<div class="sub-heading" style="background-image:url(../images/sub/sub_visual5.jpg)">
		<div class="cell">
			<h2>예약하기</h2>
		</div>
	</div>
	<div class="real-cont">
		<div class="contain">
			<div class="inner-wrap">
				<!--// content -->		
				<form action="reservation.php"  method="POST" onsubmit="return fregister_submit(this);" autocomplete="off">	
				<input type="hidden" name="rid" value="<?=$rid?>">
					<div class="reser-head">
						<h3>예약안내</h3>
						<p>투어마다 환불 규정이 다르게 적용되니, <br>
						예약 전 반드시 해당 투어의 환불/취소 규정을 확인하시기 바랍니다.</p>
						<p>우노트래블의 모든 투어는 예약금 입금 순서대로 확정 처리되며,<br>
						예약금이 입금되지 않은 투어는 2일 후에 자동으로 취소 처리되고 있습니다.</p>
					</div>
					<div class="regis-agree">
						<div class="mgb10">
							<h3 class="inline bold">취소 및 환불규정</h3>
							<!-- <a href="guide_cancel.php" target="_blank" class="btn-pack small">전문보기</a> -->
						</div>
						<div class="box"><? include "_cancel.php"; ?></div>
						<div class="check">
							<input type="checkbox" name="agree" id="agree"> <label for="agree">취소 및 환불규정 규정에 동의합니다.</label>
						</div>
					</div>
					<div class="buttons">
						<div class="cen">
							<input type="submit" value="확인" class="btn-pack large focus round">
						</div>
					</div>
				</form>
				<!-- content //-->
			</div>
		</div>
	</div>
</div><!-- end container -->
<script>
   function fregister_submit(f)
    {
        if (!f.agree.checked) {
            alert("투어 환불/취소 규정에 동의 하셔야 신청을 하실 수 있습니다.");
            f.agree.focus();
            return false;
        }


        return true;
    }
    

    </script>
<?}?>
<? include "../include/footer.php"; ?>