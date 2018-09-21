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

if(isset($_POST["message"],$_POST["group"],$_POST["min"],$_POST["max"])){
	$grp = $_POST["group"];
	$msg = trim($_POST["message"]);
	$min = intval($_POST["min"]);
	$max = intval($_POST["max"]);
	echo "till: $grp. meddelande:$msg. mellan $min och $max <br>";
	$receivers = [];
	for($i=$min;$i<=$max;$i++){
		if($i % 2 == 0 && $grp == 'even'){
			$receivers[] = $i;
		}else if($i % 2 == 1 && $grp == 'odd'){
			$receivers[] = $i;
		}else if($grp == 'all'){
			$receivers[] = $i;
		}
	}
	echo "Mottagare: ";
	print_r($receivers);
	$receivers_imp = implode(',',$receivers);
	$query = $DB->query("SELECT * FROM r18_teams WHERE t_start_position IN ($receivers_imp)");
	if($query->num_rows > 0){
		while($row = $query->fetch_assoc()){
			$team = new Team($row);
			try{
				$user = $team->getUser();
				Messenger::send($msg,$user->getId());
				Message::insert($user->getId(),$msg,'to',$team->getId());
			}catch (Exception $e) {
				echo '<br>Fel: '.$e->getMessage();
			}
			
		}
	}else{
		echo "Inga lag i det intervallet";
	}
	exit();
}
?>
<form action="messages.php" method="post">
	<input name="message" type="text" placeholder="Meddelande"/>
	<select name="group">
		<option value="all">Alla</option>
		<option value="even">Jämna</option>
		<option value="odd">Udda</option>
	</select>
	Mellan <input name="min" type="number" value="0"/> och <input name="max" type="number" value="99"/>
	<input type="submit" value="Skicka"/>
</form>


<?php

$sql = "SELECT * FROM r18_messages ORDER BY m_id DESC";
$query = $DB->query($sql);
if($query->num_rows > 0){
	while($row = $query->fetch_assoc()){
		$msg = new Message($row);
		echo (string)$msg."<br/>";
	}
}

echo "</body></html>";