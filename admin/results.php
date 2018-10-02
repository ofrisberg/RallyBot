<?php

require_once "../setup.php";
init('../');

session_start();
if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: login.php');
	exit();
}

echo '<html><head><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>';
echo '<style>table {border-collapse: collapse;}table, th, td {border: 1px solid black;}</style>';

$query = $DB->query("SELECT * FROM r18_teams WHERE t_start_position<=99 AND t_ts_finish IS NOT NULL ORDER BY t_start_position ASC");
if($query->num_rows == 0){
	exit("Inga lag");
}
$teamResArr = [];
while($row = $query->fetch_assoc()){
	$teamResArr[] = new TeamResult($row);
}


function cmp($a, $b){
	return ($a->result > $b->result);
	//return strcmp($a->getStartFinishDiffMinutes(), $b->getStartFinishDiffMinutes());
}

usort($teamResArr, "cmp");

echo '<p>P=Placering. Nr=Startnr. T=Start till mål-tid. S=Stål. H=Häftig. R=Resultat. Hj=Hjälprebusar/lösningar</p>';
echo '<table>';
echo '<th>P</th>';
echo '<th>Nr</th>';
//echo '<th></th>';
//echo '<th></th>';
echo '<th>T</th>';
echo '<th>S</th>';
echo '<th>H</th>';
echo '<th>L</th>';
echo '<th>Hj</th>';
echo '<th>R</th>';

foreach($teamResArr as $k => $teamRes){
	echo "<tr>";
	echo "<td>$k</td>";
	echo "<td>".$teamRes->getStartPosition()."</td>";
	//echo "<td>".$teamRes->getTsStart2()."</td>";
	//echo "<td>".$teamRes->getTsFinish()."</td>";
	echo "<td>".$teamRes->getStartFinishDiffMinutes()."</td>";
	echo "<td>".$teamRes->getCorrStal()."</td>";
	echo "<td>".$teamRes->getCorrHaftig()."</td>";
	echo "<td>".$teamRes->getNrLocked()."</td>";
	echo "<td>".$teamRes->getHelpBan()."</td>";
	
	$res = $teamRes->computeResult();
	$teamRes->setResult($res);
	echo "<td>$res</td>";
	echo "</tr>";
}
echo '</table>';