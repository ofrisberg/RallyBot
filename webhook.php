<?php

require_once "setup.php";
init('',false);

/* Setup */
if (isset($_REQUEST['hub_challenge'], $_REQUEST['hub_verify_token'])) {
    Messenger::verify($_REQUEST['hub_verify_token'], $_REQUEST['hub_challenge']);
}
$Me = new Messenger(json_decode(file_get_contents('php://input'), true));
file_put_contents('test.txt', json_encode($Me->inp, JSON_PRETTY_PRINT));
$Me->setIO(require "io.php");

/* Something went wrong on setup */
if ($Me->error != "") {
	file_put_contents('test.txt',$Me->error);
	exit($Me->error);
}

$time_db_start = microtime(true);

/* Connect to database */
$DB = @mysqli_connect($CFG['DATABASE']['host'], $CFG['DATABASE']['username'], $CFG['DATABASE']['password'], $CFG['DATABASE']['name']);
if (mysqli_connect_errno()) {
	$Me->reply("Systemet är överbelastat. Försök igen om 1 minut!");
	exit(); //couldnt connect, more then 24 or 40 current connections
}

/* Construct user */
$userid = $Me->getSenderId();
$textmessage = $Me->getTextMessage();
if(!User::existsById($userid)){
	if(!User::insert($userid)){
		$Me->reply("Internt fel: Kunde ej spara användaren");
		exit();
	}
}
$user = User::constructById($userid);

/* Game control during rally */
$reply = Logic::initActiveRally($user,$textmessage,$Me);

/* Simple Bot IO replies */
if($reply == ''){
	$reply = Logic::getIOReply($textmessage, require "io.php");
}

/* No response reply */
if($reply == ''){
	$reply = "-";
}

/* Save message and incoming reply if any */
Message::insert($user->getId(),$textmessage,'from',$user->getTeamId());
if($reply != ""){
	Message::insert($user->getId(),$reply,'to',$user->getTeamId());
}

$DB->close();
$time_db_seconds = microtime(true) - $time_db_start;


/* Reply to Messenger */
if(strpos($reply,".jpg") !== false){
	$Me->replyImage($reply); //image reply
}else{
	$Me->reply($reply); //text reply
}
exit();




?>