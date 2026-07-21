<?
include_once("./_common.php");

$sel_a = explode(";", $sel);
$cancelDate=time();
For($i=0; $i<count($sel_a); $i++) {
	sql_query("update  tour_reg set status='91' , memCancelDate='$cancelDate' where id='$sel_a[$i]' ");

	@sql_query("update  tour_reg set status='91' , memCancelDate='$cancelDate' where parent_id ='$sel_a[$i]' ");


	//echo "update  tour_reg set status='91' where id='$sel_a[$i]'";
}
?>