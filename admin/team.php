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
	$all_team_link = "<a href='team.php'>Alla lag</a>";
	$same_team_link = "<a href='team.php?id=".$team->getId()."'>Samma lag</a>";
	if(isset($_POST["s_id"],$_POST["r_unlock_physical"],$_POST["r_help_physical"])){
		$s_id_converted = Logic::convertStationId($team,$_POST["s_id"]);
		$station = Station::constructById($s_id_converted);
		$progress = Progress::constructByTeamAndStation($team,$station);
		$progress->setHelpPhysical($_POST["r_help_physical"]);
		$progress->setUnlockPhysical($_POST["r_unlock_physical"]);
		exit("Framsteg ändrat. $same_team_link | $all_team_link");
	}
	if(isset($_POST["t_ts_start"])){
		$team->setTsStart(trim($_POST["t_ts_start"]));
		exit("Starttid sparad. $same_team_link | $all_team_link");
	}
	if(isset($_POST["t_ts_finish"])){
		$team->setTsFinish(trim($_POST["t_ts_finish"]));
		exit("Måltid sparad. $same_team_link | $all_team_link");
	}
	if(isset($_POST["t_corr_stal"])){
		$team->setCorrStal($_POST["t_corr_stal"]);
		exit("Stålpoäng sparat. $same_team_link | $all_team_link");
	}
	if(isset($_POST["t_corr_haftig"])){
		$team->setCorrHaftig($_POST["t_corr_haftig"]);
		exit("Häftigpoäng sparat. $same_team_link | $all_team_link");
	}
	?>
	<h1><?= (string)$team ?></h1>
	<p><b>Lagledare:</b> <?= $team->getLeader() ?> <?= $team->getPhone() ?></p>
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
	$ascdesc = "DESC";
	if(intval($team->getId()) % 2 == 0){
		$ascdesc = "ASC";
	}
	$query = $DB->query("SELECT * FROM r18_progress WHERE t_id='$id' ORDER BY s_id $ascdesc");
	$tot_unlocked = $DB->query("SELECT * FROM r18_progress WHERE t_id='$id' AND r_ts_unlock IS NOT NULL")->num_rows;
	if($query->num_rows > 0){
		echo "<h2>Framsteg ($tot_unlocked/".$query->num_rows.")</h2>";
		while($row = $query->fetch_assoc()){
			$pr = new Progress($row);
			echo (string)$pr."<br/>";
		}
	}
	?>
	<div>
	<form action="team.php?id=<?= $team->getId() ?>" method="post">
		<h3>Fysiska framsteg</h3>
		Rebus ID: <input name="s_id" type="number" min="0" max="10"/><br/>
		Upplåst: <input name="r_unlock_physical" value="1" type="number" min="0" max="1"/><br/>
		Hjälprebusar: <input value="0" name="r_help_physical" type="number" min="0" max="4"/><br/>
		<input type="submit" value="Skicka"/>
	</form>
	</div>
	
	
	<?php
	$sql = "SELECT * FROM r18_answers WHERE t_id='".$team->getId()."' ORDER BY q_id ASC";
	$query = $DB->query($sql);
	echo "<h2>Stålsvar ($query->num_rows/7)</h2>";
	if($query->num_rows > 0){
		while($row = $query->fetch_assoc()){
			$answer = new Answer($row);
			echo (string)$answer."<br/>";
		}
	}
	?>
	<form action="team.php?id=<?= $team->getId() ?>" method="post">
		Korrekta svar (fysiska och digitala): <input value="<?= $team->getCorrStal() ?>" name="t_corr_stal" type="number" min="0" max="7"/><input type="submit" value="Skicka"/>
	</form>
	
	<h2>Häftigs poäng</h2>
	<form action="team.php?id=<?= $team->getId() ?>" method="post">
		Summa poäng (exkl. stålsvar): <input value="<?= $team->getCorrHaftig() ?>" name="t_corr_haftig" type="number"/><input type="submit" value="Skicka"/>
	</form>
	<?php
	
	$sql = "SELECT * FROM r18_messages WHERE t_id='".$team->getId()."' ORDER BY m_id DESC";
	$query = $DB->query($sql);
	echo "<h2>Meddelanden ($query->num_rows)</h2>";
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