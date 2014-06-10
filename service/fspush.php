<?php

if (isset($_POST["secret"]) &&
	$_POST["secret"] == "2GJ2Q05KLXG5EXIUI22LLZ1U4XRDDVSIBWGEHONXLO1XTLH5" &&
	isset($_POST["checkin"]) && 
	isset($_POST["user"]))
{
	$checkin = json_decode($_POST["checkin"], true);
	
	if ($checkin['type'] != 'checkin') return;

	require '../int/db.php';
	$db = new DB();
	
	$query = sprintf("SELECT `id` FROM `gps_users` WHERE `login` = '%s' LIMIT 0 , 1",
			$checkin['user']['id']);
	$resultuser = mysql_query($query);
	$userinfo = mysql_fetch_array($resultuser, MYSQL_ASSOC);
	mysql_free_result($resultuser);
	
	
	$query = sprintf("INSERT INTO `a28779_sio`.`gps_sessions` (`id`, `userid`, `la`, `lo`, `he`, `ve`, `ac`, `src`, `time`, `fschekinid`, `descr`) VALUES (NULL , '%s', '%s', '%s', '0', '0', '0', 'f', '%s', '%s', '%s')",
			$userinfo['id'],
			$checkin['venue']['location']['lat'],
			$checkin['venue']['location']['lng'],
			date("Y-m-d H:i:s", intval($checkin['createdAt']) - intval($checkin['timeZoneOffset']) * 60),
			$checkin['id'],
			$checkin['venue']['name']);

	mysql_query($query);
}

?>