<?php
require_once "../../setup.php";
init('../../');

session_start();
/*if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: ../login.php');
	exit();
}*/

$stats = [];
$begin = new DateTime("2018-09-29 07:00");
$end   = new DateTime("2018-09-29 18:00");
for($i = $begin; $i <= $end; $i->modify('+1 minute')){
    $stats[$i->format("H:i")] = [
		"nr_messages" => 0,
		"nr_unlocks" => 0,
		"nr_lunchin" => 0,
		"nr_lunchout" => 0,
		"nr_answers" => 0,
		"nr_finish" => 0,
		"nr_confused_bot" => 0,
	];
}


$query = $DB->query("SELECT * FROM r18_teams WHERE t_id < 100");
if($query->num_rows == 0){
	exit("Inga lag");
}
$arr = [];
function cmp($a, $b){
	return ($a["ts"] < $b["ts"]) ? -1 : 1;
}
while($row = $query->fetch_assoc()){
	$team = new Team($row);
	
	$query2 = $DB->query("SELECT * FROM r18_messages WHERE t_id='".$team->getId()."' AND m_dir='to' AND m_text LIKE '%Lat:%' ORDER BY m_ts_insert ASC");
	if($query2->num_rows > 0){
		$subarr = [
			"nr" => $team->getId(),
			"data" => [],
		];
		$subarr["data"][] = [
			"lat" => 59.820733,
			"lng" => 17.656450,
			"ts" => $team->getTsStart2()
		];
		$subarr["data"][] = [
			"lat" => 60.000534,
			"lng" => 17.861430,
			"ts" => $team->getTsLunchIn()
		];
		$subarr["data"][] = [
			"lat" => 60.000534,
			"lng" => 17.861430,
			"ts" => $team->getTsLunchOut()
		];
		while($row2 = $query2->fetch_assoc()){
			$msg = new Message($row2);
			if(preg_match('/Lat:([0-9]+\.[0-9]+)\s/iu',$msg->getText(),$matches) && preg_match('/Lng:([0-9]+\.[0-9]+)\b/iu',$msg->getText(),$matches2)){
				$subarr["data"][] = [
					"lat" => floatval($matches[1]),
					"lng" => floatval($matches2[1]),
					"ts" => $msg->getTsInsert()
				];
			}
		}
		$subarr["data"][] = [
			"lat" => 59.820733,
			"lng" => 17.656450,
			"ts" => $team->getTsFinish()
		];
		
		usort($subarr["data"],"cmp");
		$arr[] = $subarr;
	}
}
$resultlist = [];
$query = $DB->query("SELECT * FROM r18_teams WHERE t_id < 100 AND t_result > 0 ORDER BY t_result ASC");
if($query->num_rows == 0){
	exit("Inga lag");
}
$i = 1;
while($row = $query->fetch_assoc()){
	$team = new Team($row);
	$stats[substr($team->getTsFinish(),11,5)]["nr_finish"]++;
	$stats[substr($team->getTsLunchIn(),11,5)]["nr_lunchin"]++;
	$stats[substr($team->getTsLunchOut(),11,5)]["nr_lunchout"]++;
	$resultlist[] = [
		"placement"=>$i,
		"start_position"=>$team->getStartPosition(),
		"name"=>$team->getName()
	];
	$i++;
}

$query = $DB->query("SELECT * FROM r18_messages WHERE m_ts_insert > '2018-09-29 07:00:00' AND m_ts_insert < '2018-09-29 17:00:00'");
if($query->num_rows == 0){
	exit("Inga meddelanden");
}
while($row = $query->fetch_assoc()){
	$msg = new Message($row);
	if(strpos($msg->getText(),'kontakta rallykå')!==false){
		$stats[substr($msg->getTsInsert(),11,5)]["nr_confused_bot"]++;
	}
	$stats[substr($msg->getTsInsert(),11,5)]["nr_messages"]++;
}

$query = $DB->query("SELECT * FROM r18_answers WHERE a_ts_insert > '2018-09-29 07:00:00' AND a_ts_insert < '2018-09-29 17:00:00'");
if($query->num_rows == 0){
	exit("Inga meddelanden");
}
while($row = $query->fetch_assoc()){
	$answer = new Answer($row);
	$stats[substr($answer->getTsInsert(),11,5)]["nr_answers"]++;
}

$query = $DB->query("SELECT * FROM r18_progress WHERE r_ts_unlock IS NOT NULL AND r_ts_unlock > '2018-09-29 07:00:00' AND r_ts_unlock < '2018-09-29 17:00:00'");
if($query->num_rows == 0){
	exit("Inga upplåsningar");
}
while($row = $query->fetch_assoc()){
	$progress = new Progress($row);
	$stats[substr($progress->getTsUnlock(),11,5)]["nr_unlocks"]++;
}


?>

<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>

		<div id="map" class="fill">
			<div id="clock"></div>
			<div id="teams" class="fill"></div>
			<div id="stats"><table id="stats_table"></table></div>
		</div>
		<script src="app.js" type="text/javascript"></script>
		<script>
			window.onload = function() {
				app.setup(<?= json_encode($arr) ?>,<?= json_encode($resultlist) ?>,<?= json_encode($stats) ?>);
				app.init();
			};
		</script>
		
	</body>
</html>