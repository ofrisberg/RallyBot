<?php
require_once "../setup.php";
init('../');

session_start();
if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: ../admin/login.php');
	exit();
}

$arr = require("anmalan_data.php");
//print_r($arr);

$tbl = '<table><tr><th>Startnr</th><th>Lagnamn</th><th>Lagbeskrivning</th></tr>';

foreach($arr as $e){
	$tbl .= "<tr><td>$e[0]</td><td>$e[1]</td><td>$e[2]</td></tr>";
}

$tbl .= "</table>";

echo $tbl;