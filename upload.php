<?php

$dbl = mysql_connect('a28779.mysql.mchost.ru', "a28779_sio", "SiO3535");
	
mysql_query ('set character_set_client="utf8"');
mysql_query ('set character_set_results="utf8"');
mysql_query ('set collation_connection="utf8_general_ci"');/**/
	
mysql_select_db("a28779_sio");

$query = sprintf("SELECT `id` FROM `gps_users` WHERE `hash` = '%s' LIMIT 0 , 1",
	mysql_real_escape_string($_GET['hash']));
	
$result = mysql_query($query);
$num = mysql_num_rows($result);
$user = mysql_fetch_array($result, MYSQL_ASSOC);
mysql_free_result($result);


// если нет - добавляем нового
if ($num != 1)
{
	$query = sprintf("INSERT INTO `a28779_sio`.`gps_users` (`id` , `login` , `pass` , `hash` ) VALUES (NULL , '%s', '%s', '%s')",
		mysql_real_escape_string($_GET['hash']),
		mysql_real_escape_string($_GET['hash']),
		mysql_real_escape_string($_GET['hash']));
		
	$insertresult = mysql_query($query);
	
	$user['id'] = mysql_insert_id();
}


{ 

	$query = sprintf("INSERT INTO `a28779_sio`.`gps_sessions` (`id`, `userid`, `la`, `lo`, `he`, `ve`, `ac`, `time`, `descr`) VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP, '')",
		$user['id'],
		mysql_real_escape_string($_GET['la']),
		mysql_real_escape_string($_GET['lo']),
		mysql_real_escape_string($_GET['he']),
		mysql_real_escape_string($_GET['ve']),
		mysql_real_escape_string($_GET['ac']));

	echo $query;
		
	mysql_query($query);
}


mysql_close($dbl);

?>