<?php
require_once "../setup.php";
init('../');

session_start();
if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: login.php');
	exit();
}



$query = $DB->query("SELECT * FROM r18_teams");
if($query->num_rows > 0){
	while($row = $query->fetch_assoc()){
		$team = new Team($row);
		//echo (string)$team." <a href='team.php?id=".$team->getId()."'>Länk</a><br/>";
		for($i=1;$i<=10;$i++){
			$station = Station::constructById($i);
			if(Progress::exists($team,$station)){
				echo (string)$team;
				echo " finns redan för station $i<br>"; 
			}else{
				if(Progress::insertEmpty($team->getId(),$i)){
					//success
				}else{
					echo "Databasfel<br>";
				}
			}
		}
	}
}