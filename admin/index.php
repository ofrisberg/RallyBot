<?php
require_once "../setup.php";
init('../');

session_start();
if(!isset($_SESSION["rr_admin"]) || !$_SESSION["rr_admin"]){
	header('Location: login.php');
	exit();
}

if(isset($_POST["slack_msg"])){
	$sl = new Slack();
	$sl->send($_POST["slack_msg"]);
	exit("Slack-meddelande skickat.");
}

$summ = new Summary();
?>
<html><head><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>
<h1>Meny</h1>
<ul>
	<li><a href="team.php">Start och mål</a></li>
	<li><a href="messages.php">Meddelanden</a></li>
	<li><a href="lunch.php">Lunchstation</a></li>
	<li><a href="https://developers.facebook.com/apps/180505402598044/dashboard/?business_id=709711106054256">Facebook App Dashboard</a></li>
	<li><a href="results.php">Resultat</a></li>
	<li><a href="animation.php">Animation</a></li>
</ul>
<h2>Sammanfattning</h2>
<table>
	<tr>
		<td><b>Ihopkopplade</b></td>
		<td><?= $summ->nrCoupled() ?>/100</td>
	</tr>
	<tr>
		<td><b>Startade</b></td>
		<td><?= $summ->nrStarted() ?>/100</td>
	</tr>
	<tr>
		<td><b>Upplåsningar</b></td>
		<td><?= $summ->nrUnlocks() ?>/1000</td>
	</tr>
	<tr>
		<td><b>Hjälprebusar</b></td>
		<td><?= $summ->nrHelps() ?>/4000</td>
	</tr>
	<tr>
		<td><b>Lunch in</b></td>
		<td><?= $summ->nrLunchIn() ?>/100</td>
	</tr>
	<tr>
		<td><b>Lunch ut</b></td>
		<td><?= $summ->nrLunchOut() ?>/100</td>
	</tr>
	<tr>
		<td><b>Stålsvar</b></td>
		<td><?= $summ->nrAnswers() ?>/700</td>
	</tr>
	<tr>
		<td><b>Avslutade</b></td>
		<td><?= $summ->nrFinished() ?>/100</td>
	</tr>
	<tr>
		<td></td>
		<td><a href="summary.php">Mer</a></td>
	</tr>
</table>

<h2>Slack-meddelande</h2>
<form action="index.php" method="post">
	<textarea name="slack_msg"></textarea><br/>
	<input type="submit" value="Skicka"/>
</form>

</body></html>