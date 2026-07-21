<?
include_once("./_common.php"); 
$frToDate_a=explode("|",$frToDate);
$frDate=$frToDate_a[0];

$k=0;
//$shop_id="admin";

$result=sql_query("select  *, sum(TotPrice) as sum, count(id) as cnt, left(AppDate,8) as appDate from kspay_result  group by left(AppDate,8) ");
for($i=0; $row=sql_fetch_array($result);$i++) {
	$arr[$k]["id"]=$row[id];
	$arr[$k]["start"]=substr($row[appDate],0,4)."-".substr($row[appDate],4,2)."-".substr($row[appDate],6,2);
	$arr[$k]["title"]=$row[cnt]."건 ".number_format($row[sum])."원";
	$arr[$k]["url"]="/Admin/index.html?inc=kscardPayList&v=list&termFrom=".$arr[$k]["start"]."&termTo=".$arr[$k]["start"];
	$arr[$k]["className"]="cal_text";
	$k++;
}

echo json_encode($arr);
?>