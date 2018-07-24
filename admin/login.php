<?php

require_once "../setup.php";
init('../');

if(isset($_POST["pass"])){
	session_start();
	if($_POST["pass"] == $CFG['GENERAL']['admin_password']){
		$_SESSION["rr_admin"] = true;
		if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
			exit("Internt fel");
		}
		header('Location: index.php');
	}else{
		exit("Fel lösenord");
	}
	exit();
}
?>
<html>
<head><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body>
<h1>Logga in</h1>
<form action="login.php" method="post">
Lösenord: <br/>
<input type="password" name="pass"><br>
<input type="submit" value="skicka">
</form>
<p>Fråga NätSäk <?= date('Y') ?> efter lösenord.</p>
</body>
</html>