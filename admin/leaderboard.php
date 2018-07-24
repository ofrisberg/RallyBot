<?php

require_once "../setup.php";
init('../');

session_start();
if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: login.php');
	exit();
}

echo '<html><head><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>';

$sql = Logic::getBestTimeSQL();
$query = $DB->query($sql);
echo "<h1>Lag efter minst tid</h1>";
echo "<p style='font-size:10px;'>$sql</p>";
echo "<p>Antal lag som gått i mål: ".$query->num_rows." st.</p>";
if($query->num_rows > 0){
	while($row = $query->fetch_assoc()){
		$team = new Team($row);
		echo (string)$team." ($row[nr_minutes] minuter)<br/>";
	}
}


echo "</body></html>";