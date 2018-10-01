<?php
require_once "../setup.php";
init('../');

session_start();
if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: ../admin/login.php');
	exit();
}

// comment the line below to make the script runnable
exit();

$base_time = strtotime('2018-09-29 08:10:00');
$delta_seconds = 20;
$sql = "SELECT * FROM r18_teams WHERE t_start_position <= 99 ORDER BY t_start_position ASC";
$query = $DB->query($sql);
if($query->num_rows==0){
	exit("Inga lag");
}
while($row = $query->fetch_assoc()){
	$team = new Team($row);
	$sp = $team->getStartPosition();
	$added_seconds = $delta_seconds*$sp;
	$newdate = $base_time+$added_seconds;
	$newdate = date('Y-m-d H:i:s',$newdate);
	//echo "$newdate<br/>";
	$DB->query("UPDATE r18_teams SET t_ts_start2='$newdate' WHERE t_id='".$team->getId()."'");
}