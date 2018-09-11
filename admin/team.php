<?php

require_once "../setup.php";
init('../');

session_start();
if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: login.php');
	exit();
}

echo '<html><head><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>';

if(isset($_GET["id"])){
	if(!Team::existsById($_GET["id"])){
		exit("Laget finns inte");
	}
	$team = Team::constructById($_GET["id"]);
	
	if(isset($_POST["s_id"],$_POST["r_ts_unlock"],$_POST["r_help"])){
		$s_id_converted = Logic::convertStationId($team,$_POST["s_id"]);
		$station = Station::constructById($s_id_converted);
		$progress = Progress::constructByTeamAndStation($team,$station);
		$progress->setHelp($_POST["r_help"]);
		$progress->setTsUnlock(trim($_POST["r_ts_unlock"]));
		exit("Framsteg ändrat/skapat. <a href='team.php?id=".$team->getId()."'>team.php?id=".$team->getId()."</a>");
	}
	if(isset($_POST["t_ts_start"])){
		$team->setTsStart(trim($_POST["t_ts_start"]));
		exit("Starttid sparad. <a href='team.php?id=".$team->getId()."'>team.php?id=".$team->getId()."</a>");
	}
	if(isset($_POST["t_ts_finish"])){
		$team->setTsFinish(trim($_POST["t_ts_finish"]));
		exit("Måltid sparad. <a href='team.php?id=".$team->getId()."'>team.php?id=".$team->getId()."</a>");
	}
	?>
	<h1><?= (string)$team ?></h1>
	<div>
		Starttid: <?= $team->getTsStart() ?>
		<form style="display: inline;" action="team.php?id=<?= $team->getId() ?>" method="post">
			<input name="t_ts_start" type="text" value="<?= date('Y-m-d H:i:s') ?>"/>
			<input type="submit" value="Ändra"/>
			<?php if($team->hasStarted()){echo 'incheckat';}else{echo 'ej incheckat';} ?>
		</form>
		
		<br/>
		Måltid: <?= $team->getTsFinish() ?>
		<form style="display: inline;" action="team.php?id=<?= $team->getId() ?>" method="post">
			<input name="t_ts_finish" type="text" value="<?= date('Y-m-d H:i:s') ?>"/>
			<input type="submit" value="Ändra"/>
			<?php if($team->hasFinished()){echo 'utcheckat';}else{echo 'ej utcheckat';} ?>
		</form>
	</div>
	<?php
	$id = $DB->real_escape_string($team->getId());
	$query = $DB->query("SELECT * FROM r18_progress WHERE t_id='$id'");
	if($query->num_rows > 0){
		echo "<h2>Framsteg (".$query->num_rows.")</h2>";
		while($row = $query->fetch_assoc()){
			$pr = new Progress($row);
			echo (string)$pr."<br/>";
		}
	}
	?>
	<div>
	<form action="team.php?id=<?= $team->getId() ?>" method="post">
		<h3>Lägg till eller ändra framsteg</h3>
		Rebus ID: <input name="s_id" type="number" min="0" max="10"/><br/>
		Upplåsningstid: <input name="r_ts_unlock" type="text" value="<?= date('Y-m-d H:i:s') ?>"/><br/>
		Hjälprebusar: <input name="r_help" type="number" min="0" max="4"/><br/>
		<input type="submit" value="Skicka"/>
	</form>
	</div>
	
	<?php
	
	echo "<h2>Meddelanden</h2>";
	$sql = "SELECT * FROM r18_messages WHERE t_id='".$team->getId()."' ORDER BY m_id DESC";
	$query = $DB->query($sql);
	if($query->num_rows > 0){
		while($row = $query->fetch_assoc()){
			$msg = new Message($row);
			echo (string)$msg."<br/>";
		}
	}
	
}else{
	$query = $DB->query("SELECT * FROM r18_teams");
	if($query->num_rows > 0){
		while($row = $query->fetch_assoc()){
			$team = new Team($row);
			echo (string)$team." <a href='team.php?id=".$team->getId()."'>Länk</a><br/>";
		}
	}
}

echo "</body></html>";