<?php

require_once "../setup.php";
init('../');

session_start();
if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: login.php');
	exit();
}

echo '<html><head><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>';

echo "<h1>Meddelanden</h1>";
$sql = "SELECT * FROM r18_messages ORDER BY m_id DESC";
$query = $DB->query($sql);
if($query->num_rows > 0){
	while($row = $query->fetch_assoc()){
		$msg = new Message($row);
		echo (string)$msg."<br/>";
	}
}

echo "</body></html>";