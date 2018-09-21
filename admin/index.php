<?php
require_once "../setup.php";
init('../');

session_start();
if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: login.php');
	exit();
}
?>
<html><head><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>
<h1>Meny</h1>
<ul>
	<li><a href="team.php">Lag</a></li>
	<li><a href="messages.php">Meddelanden</a></li>
	<li><a href="lunch.php">Lunch</a></li>
	<li><a href="leaderboard.php">Bäst tid (gammal)</a></li>
</ul>

</body></html>