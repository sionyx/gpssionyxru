<?php
function GetUrlJson($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	$res = curl_exec($ch);
	$headers = curl_getinfo($ch);
	curl_close($ch);

	if ($headers['http_code'] == '200')
	{
		$json = json_decode($res, true);
		return $json;
	}

	echo $res;
	echo $headers;

	return false;
}


if (isset($_GET["code"]))
{
	$url = sprintf("https://foursquare.com/oauth2/access_token?client_id=NFMQSR1Y22XRO3DBDRCCAY2JAGC1M5HVBTEBTJFBASCOU2NJ&client_secret=FUMADZTCZXLLL2CGTVS4V3W2U4FWQ0JFSBSBVVQXUFUWWQ3K&grant_type=authorization_code&redirect_uri=http://gps.sionyx.ru/service/fsredirect.php&code=%s",
			$_GET['code']);

	//echo $url;
	
	$auth = GetUrlJson($url);
	
	print_r($auth);
	
	if ($auth == false)
	{
		header("HTTP/1.1 302 Found");
		header("Location: http://gps.sionyx.ru/?auth=failed");
	
		echo "couldn't connet to the server";
	}
	else if (isset($auth['access_token']))
	{
		//echo $auth['access_token']."\n\n";
		//echo $auth['expires_in']."\n\n";
		//echo $auth['user_id']."\n\n";
		////echo $auth['secret']."\n\n";
	
		session_start();
	
		$_SESSION['user']['fstoken'] = $auth['access_token'];
	
		require '../int/db.php';
		$db = new DB();
	
		$query = sprintf("SELECT * FROM `gps_users` WHERE `fstoken` = '%s' LIMIT 0 , 1",
				mysql_real_escape_string($_SESSION['user']['fstoken']));
	
		//echo $query;
	
		$resultuser = mysql_query($query);
		$userinfo = mysql_fetch_array($resultuser, MYSQL_ASSOC);
		mysql_free_result($resultuser);
		

		$url = sprintf("https://api.foursquare.com/v2/users/self?oauth_token=%s&v=20140601",
				$auth['access_token']);
		$user = GetUrlJson($url);
		
		//print_r($user);
		
		
		$query = sprintf("UPDATE `a28779_sio`.`gps_users` SET `fstoken` = '%s', `firname` = '%s', `surname` = '%s' WHERE `gps_users`.`login` = '%s'", 
				$auth['access_token'],
				$user['response']['user']['firstName'],
				$user['response']['user']['lastName'],
				$user['response']['user']['id']);
		
		//echo $query;

		mysql_query($query);

		if (mysql_affected_rows() == 0)
		{
			$query = sprintf("INSERT INTO `a28779_sio`.`gps_users` (`fstoken`, `id`, `login`, `pass`, `hash`, `firname`, `surname`) VALUES ('%s', NULL , '%s', '', '%s', '%s', '%s')",
				$auth['access_token'],
				$user['response']['user']['id'],
				MD5($user['response']['user']['id']),
				$user['response']['user']['firstName'],
				$user['response']['user']['lastName']);
			mysql_query($query);
		}
		
		echo "done";

		header("HTTP/1.1 302 Found");
		header("Location: http://gps.sionyx.ru/");
		
		
		//https://api.foursquare.com/v2/
		
		//print_r($userinfo);
	
		//if (isset($userinfo['firstname']) && isset($userinfo['lastname']))
		//{
		//	$_SESSION['user']['firstname'] = $userinfo['firstname'];
		//	$_SESSION['user']['lastname'] = $userinfo['lastname'];
		//		
		//	header("HTTP/1.1 302 Found");
		//	header("Location: http://megabytedrive.ru/files");
		//		
		//		
		//		
		//	return;
		//}
		//else
		//{
		//	$url = sprintf("https://api.vk.com/method/users.get?user_id=%s&v=5.5&access_token=%s",
		//			$auth['user_id'],
		//			$auth['access_token']);
	
		//	//echo $url;
		//		
		//	$user = GetUrlJson($url);
		//		
		//	$_SESSION['user']['firstname'] = $user['response'][0]['first_name'];
		//	$_SESSION['user']['lastname'] = $user['response'][0]['last_name'];
		//		
		//	header("HTTP/1.1 302 Found");
		//	header("Location: http://megabytedrive.ru/eula");
		//		
		//	return;
		//}
	
	
	
		//print_r($user);
	}
	
	
	
	//echo "code=".$_GET["code"];
	
	
	
}


?>