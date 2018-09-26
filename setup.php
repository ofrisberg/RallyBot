<?php

function init($path, $dbAutoConnect = true){
	
	$CFG = parse_ini_file($path.'config.ini', true);
	$GLOBALS['CFG'] = $CFG;
	
	if($dbAutoConnect){
		$host = $CFG['DATABASE']['host'];
		$username = $CFG['DATABASE']['username'];
		$password = $CFG['DATABASE']['password'];
		$name = $CFG['DATABASE']['name'];
		$DB = mysqli_connect($host, $username, $password, $name);
		if (mysqli_connect_errno()) {
			echo mysqli_connect_error();
			exit();
		}
		$GLOBALS['DB'] = $DB;
	}
	
	require_once $path.'classes/class.gpfunctions.php';
	require_once $path.'classes/class.user.php';
	require_once $path.'classes/class.messenger.php';
	require_once $path.'classes/class.team.php';
	require_once $path.'classes/class.message.php';
	require_once $path.'classes/class.progress.php';
	require_once $path.'classes/class.station.php';
	require_once $path.'classes/class.answer.php';
	require_once $path.'classes/class.slack.php';
	require_once $path.'classes/class.logic.php';
	require_once $path.'classes/class.summary.php';
	
	ini_set("date.timezone", "Europe/Stockholm");
}

?>