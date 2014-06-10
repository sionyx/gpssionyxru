<?php
if (!isset($HTTP_RAW_POST_DATA))
{
	echo 'INVALIDPOST';
	return;
}

$json = json_decode($HTTP_RAW_POST_DATA, true);
if ($json == null)
{
	echo 'INVALIDJSON';
	return;
}


$dbl = mysql_connect('a28779.mysql.mchost.ru', "a28779_sio", "SiO3535");
	
mysql_query ('set character_set_client="utf8"');
mysql_query ('set character_set_results="utf8"');
mysql_query ('set collation_connection="utf8_general_ci"');/**/
	
mysql_select_db("a28779_sio");

$query = sprintf("SELECT `id` FROM `gps_users` WHERE `hash` = '%s' LIMIT 0 , 1",
	mysql_real_escape_string($json["hash"]));
	
$result = mysql_query($query);
$num = mysql_num_rows($result);
$user = mysql_fetch_array($result, MYSQL_ASSOC);
mysql_free_result($result);


// если нет - добавляем нового
if ($num != 1)
{
	$query = sprintf("INSERT INTO `a28779_sio`.`gps_users` (`id` , `login` , `pass` , `hash` ) VALUES (NULL , '%s', '%s', '%s')",
		mysql_real_escape_string($json["hash"]),
		mysql_real_escape_string($json["hash"]),
		mysql_real_escape_string($json["hash"]));
		
	$insertresult = mysql_query($query);
	
	$user['id'] = mysql_insert_id();
}

foreach($json["points"] as $point)
{ 
	$query = sprintf("INSERT INTO `a28779_sio`.`gps_sessions` (`id`, `userid`, `la`, `lo`, `he`, `ve`, `ac`, `src`, `time`, `descr`) VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '')",
		$user['id'],
		mysql_real_escape_string($point['Latitude']),
		mysql_real_escape_string($point['Longitude']),
		mysql_real_escape_string($point['Heading']),
		mysql_real_escape_string($point['Speed']),
		mysql_real_escape_string($point['Accuracy']),
		mysql_real_escape_string($point['Source']),
		mysql_real_escape_string($point['DateTime']));

	mysql_query($query);
}


mysql_close($dbl);

echo "DONE";
?>