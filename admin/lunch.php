<?php

require_once "../setup.php";
init('../');

session_start();
if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: login.php');
	exit();
}

echo '<html><head><meta name="viewport" content="width=device-width, initial-scale=1"></head><body><h1>Lunch</h1>';

if(isset($_GET["t_id"],$_GET["action"])){
	$team = Team::constructById($_GET["t_id"]);
	if($_GET["action"]=='checkin'){
		$team->lunchCheckIn();
		$msg = "Incheckad för obligatorisk lunch, smaklig måltid :)";
	}else if($_GET["action"]=='checkout'){
		$team->lunchCheckOut();
		$msg = "Utcheckad från obligatorisk lunch, hoppas det smakade :)";
	}else{
		exit("Action not checkin or checkout");
	}

	try{
		$user = $team->getUser();
		Messenger::send($msg,$user->getId());
		Message::insert($user->getId(),$msg,'to',$team->getId());
	}catch (Exception $e) {
		echo 'In/ut-checkning för lunch lyckades men ett fel uppstod påvägen: '.$e->getMessage();
	}
	exit();
	
}

$query = $DB->query("SELECT * FROM r18_teams");
if($query->num_rows > 0){
	while($row = $query->fetch_assoc()){
		$team = new Team($row);
		echo (string)$team." <a href='team.php?id=".$team->getId()."'>Länk</a><br/>";
		//echo $team->getTsLunchIn()." --- ".$team->getTsLunchOut()."<br>";
		if($team->getTsLunchIn() == ""){
			echo '<a href="lunch.php?t_id='.$team->getId().'&action=checkin">Check in</a> ';
		}else if($team->getTsLunchOut() == ""){
			
			$minutes_to_add = 45;
			$time = new DateTime($team->getTsLunchIn());
			$time->add(new DateInterval('PT' . $minutes_to_add . 'M'));
			$stamp = $time->format('H:i');
			
			echo '<a href="lunch.php?t_id='.$team->getId().'&action=checkout">Check out ('.$stamp.')</a>';
		}
		echo '<br><br>';
	}
}

echo "</body></html>";