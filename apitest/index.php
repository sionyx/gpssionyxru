<!DOCTYPE html>
<html>
<head>
    <script src="//api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
<script type="text/javascript">
ymaps.ready(init);
function init() {

<?php 
	require_once("src/FoursquareAPI.class.php");
	$name = array_key_exists("name",$_GET) ? $_GET['name'] : "Foursquare";

	// Set your client key and secret
	$client_key = "";
	$client_secret = "";  
	$auth_token = "";

	// Load the Foursquare API library
	$foursquare = new FoursquareAPI($client_key,$client_secret);
	$foursquare->SetAccessToken($auth_token);

	$params = array("limit"=>30);

	$response = $foursquare->GetPrivate("users/self/checkins",$params);
 
	$route = 0;
	$polyline[$route] = "";
	$places = array();
	
	$checkins = json_decode($response);
	$reversedItems = array_reverse($checkins->response->checkins->items);
	foreach($reversedItems as $item):
		$location = $item->venue->location;
		$polyline[$route] .= "[ " . $location->lat . ", " . $location->lng . "], ";
		$places[] = array('time' => $item->createdAt, 'descr' => $item->venue->name, 'la' => $location->lat, 'lo' => $location->lng);
	endforeach
?>

 // Создаем карту.
    var myMap = new ymaps.Map("map", {
            center: <? echo "[ " . $location->lat . ", " . $location->lng . "]"; ?>,
            zoom: 10
        });

    // Создаем ломаную с помощью вспомогательного класса Polyline.
    var myPolyline = new ymaps.Polyline([
    <?php echo $polyline[$route]; ?>

        ], {
            // Описываем свойства геообъекта.
            // Содержимое балуна.
            balloonContent: "Ломаная линия"
        }, {
            // Задаем опции геообъекта.
            // Отключаем кнопку закрытия балуна.
            balloonCloseButton: false,
            strokeWidth: 4,
            strokeOpacity: 1,
            strokeStyle: '3 2'
        });

    // Добавляем линии на карту.
    myMap.geoObjects
        .add(myPolyline);
}

</script>
</head>
<body>
		<center>
			<div id="map" style="width:640px; height:480px"></div>
		</center>
		<? foreach($places as $place): ?>
			<p> <?= date("Y-m-d H:i:s", strtotime($place['time']) + 14400)  ?>: <?= htmlentities($place['descr'], ENT_QUOTES, "UTF-8") ?> </p>
		<? endforeach ?>
	
</body>
</html>
