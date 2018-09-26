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

$arr = require("anmalan_data.php");

$nr_success = 0;
foreach($arr as $e){
	$res = [
		"t_id" => $e[0],
		"t_start_position" => $e[0],
		"t_name" => $e[1],
		"t_leader" => $e[5],
		"t_phone" => $e[7],
		"t_email" => $e[6],
		"t_token" => $e[4],
		"t_gasque" => $e[3],
		"t_nr_participants" => $e[8],
		"t_description" => $e[2],
	];
	$sql = "INSERT INTO r18_teams ";
	$keyArr = [];
	$valArr = [];
	foreach($res as $rk => $re){
		$keyArr[] = $rk;
		$valArr[] = $DB->real_escape_string($re);
	}
	$sql .= "(".implode(',',$keyArr).") VALUES ";
	$sql .= "('".implode("','",$valArr)."')";
	
	if(!Team::existsById($e[0])){
		if($DB->query($sql)){
			$nr_success++;
		}else{
			echo "Databasfel $e[0]<br>";
		}
	}else{
		echo "Laget finns redan $e[0]<br>";
	}
	
}
echo "$nr_success lag lades till<br>";
