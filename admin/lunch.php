<?php

require_once "../setup.php";
init('../');

session_start();
if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: login.php');
	exit();
}

echo '<html><head><meta name="viewport" content="width=device-width, initial-scale=1"></head><body><h1>Lunch</h1>';
echo '<style>table {border-collapse: collapse;}table, th, td {border: 1px solid black;}</style>';

if(isset($_GET["t_id"],$_GET["action"])){
	$team = Team::constructById($_GET["t_id"]);
	if($_GET["action"]=='checkin'){
		if($team->getTsLunchIn() != ""){
			exit("Redan incheckad för lunch");
		}
		$team->lunchCheckIn();
		$msg = "Incheckad för obligatorisk lunch, smaklig måltid :)";
	}else if($_GET["action"]=='checkout'){
		if($team->getTsLunchOut() != ""){
			exit("Redan utcheckad för lunch");
		}
		$team->lunchCheckOut();
		$msg = "Utcheckad från obligatorisk lunch, hoppas det smakade :)";
	}else{
		exit("Action not checkin or checkout");
	}

	/* Remove the block below if api limit is close */
	try{
		$user = $team->getUser();
		Messenger::send($msg,$user->getId());
		Message::insert($user->getId(),$msg,'to',$team->getId());
	}catch (Exception $e) {
		echo 'In/ut-checkning för lunch lyckades men ett fel uppstod påvägen: '.$e->getMessage();
	}
	
	
	exit();
	
}

$query = $DB->query("SELECT * FROM r18_teams ORDER BY t_start_position ASC");
if($query->num_rows > 0){
	echo '<table>';
	while($row = $query->fetch_assoc()){
		$team = new Team($row);
		echo "<tr>";
		echo "<td><a href='team.php?id=".$team->getId()."'>".(string)$team."</a></td>";
		
		if($team->getTsLunchIn() == ""){
			echo '<td><a href="lunch.php?t_id='.$team->getId().'&action=checkin">Check in</a></td>';
		}else{
			echo "<td></td>";
		}
		
		if($team->getTsLunchOut() == "" && $team->getTsLunchIn() != ""){
			$minutes_to_add = 45;
			$time = new DateTime($team->getTsLunchIn());
			$time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
			$stamp = $time->format('H:i');
			
			echo '<td><a href="lunch.php?t_id='.$team->getId().'&action=checkout">Check out ('.$stamp.')</a></td>';
		}else{
			echo "<td></td>";
		}
		
		if($team->hasUser()){
			echo "<td></td>";
		}else{
			echo "<td>missing user</td>";
		}
		echo "</tr>";
	}
	echo '</table>';
}

echo "</body></html>";