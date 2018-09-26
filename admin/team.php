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

if(isset($_GET["id"])){
	if(!Team::existsById($_GET["id"])){
		exit("Laget finns inte");
	}
	$team = Team::constructById($_GET["id"]);
	$all_team_link = "<a href='team.php'>Alla lag</a>";
	$same_team_link = "<a href='team.php?id=".$team->getId()."'>Samma lag</a>";
	
	if(isset($_POST["team_msg"])){
		$user = $team->getUser();
		$msg = trim($_POST["team_msg"]);
		Messenger::send($msg,$user->getId());
		Message::insert($user->getId(),$msg,'to',$team->getId());
		exit("Meddelande skickat. $same_team_link | $all_team_link");
	}
	
	if(isset($_POST["s_id"],$_POST["r_unlock_physical"],$_POST["r_help_physical"])){
		$s_id_converted = Logic::convertStationId($team,$_POST["s_id"]);
		$station = Station::constructById($s_id_converted);
		$progress = Progress::constructByTeamAndStation($team,$station);
		$progress->setHelpPhysical($_POST["r_help_physical"]);
		$progress->setUnlockPhysical($_POST["r_unlock_physical"]);
		exit("Framsteg ändrat. $same_team_link | $all_team_link");
	}
	
	if(isset($_GET["start"])){
		if($team->hasStarted()){exit("Laget har redan startat!");}
		$team->start();
		exit("Starttid sparad. $same_team_link | $all_team_link");
	}
	if(isset($_GET["finish"])){
		if($team->hasFinished()){exit("Laget har redan gått i mål!");}
		$team->finish();
		
		$msg = "Jag är ingen bot\n";
		$msg .= "Jag är en väldigt, väldigt vacker tjej\n\n";
		if($team->hasGasque()){
			$to_time = strtotime("2018-09-29 18:00:00");
			$from_time = strtotime(date('Y-m-d H:i:s'));
			$minutes = round(abs($to_time - $from_time) / 60,0);
			$msg .= "Hoppas vi ses på gasquen om $minutes dk minuter :)";
		}else{
			$msg .= "Vi ses inte på gasquen men förhoppningsvis på släppet :)";
		}
		
		/* Remove the three lines below if api limit is close */
		$user = $team->getUser();
		Messenger::send($msg,$user->getId());
		Message::insert($user->getId(),$msg,'to',$team->getId());
		
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
	<p>
		Lagledare: <?= $team->getLeader() ?> <br/>
		Mobilnummer: <?= $team->getPhone() ?><br/>
		Email: <?= $team->getEmail() ?><br/>
		Deltagare: <?= $team->getNrParticipants() ?><br/>
		Gasque: <?= $team->getGasque() ?><br/>
		Token: <?= $team->getToken() ?><br/><br/>
		
		Starttid: <?= $team->getTsStart() ?><br/>
		Måltid: <?= $team->getTsFinish() ?><br/>
		
	</p>
	<p>
		<form action="team.php?id=<?= $team->getId() ?>" method="post">
			<input name="team_msg" type="text" placeholder="Meddelande"/>
			<input type="submit" value="Skicka"/>
		</form>
	</p>
	
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
	echo '<table>';
	$query = $DB->query("SELECT * FROM r18_teams ORDER BY t_start_position ASC");
	if($query->num_rows > 0){
		while($row = $query->fetch_assoc()){
			$team = new Team($row);
			echo "<tr>";
			echo "<td><a href='team.php?id=".$team->getId()."'>".(string)$team."</a></td>";
			if($team->hasStarted()){
				echo "<td></td>";
			}else{
				echo "<td><a href='team.php?id=".$team->getId()."&start=1'>Start</a></td>";
			}
			if($team->hasFinished() || !$team->hasStarted()){
				echo "<td></td>";
			}else{
				echo "<td><a href='team.php?id=".$team->getId()."&finish=1'>Mål</a></td>";
			}
			if($team->hasUser()){
				echo "<td></td>";
			}else{
				echo "<td>missing user</td>";
			}
			if(!$team->getPhone() == ''){
				echo "<td></td>";
			}else{
				echo "<td>missing phone</td>";
			}
			
			echo "</tr>";
		}
	}
	echo '</table>';
}

echo "</body></html>";