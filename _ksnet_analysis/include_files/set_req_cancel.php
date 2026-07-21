<?
include_once("./_common.php");

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
?>
