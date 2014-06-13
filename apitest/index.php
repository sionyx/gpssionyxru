<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<link rel="canonical" href="http://ourlove.ws/pages/about/">
	<title>Travel - Filipp Panfilov</title>
    <script src="//api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>
	<script type="text/javascript">
		ymaps.ready(init);
		var myMap; 
		function init() {

<?php 
	require_once("src/FoursquareAPI.class.php");
	$name = array_key_exists("name",$_GET) ? $_GET['name'] : "Foursquare";

	// Set your client key and secret
	$client_key = "DRQMOPUSYDKJQ1LQXPWJ45RI42UAFLRZUSPSU021OYSFDF51";
	$client_secret = "MQCQ3AFCBDU2UTH43H5J2VDP15ZKZJIJRHSAKULTB3JCQYP5";  
	// Set your auth token, loaded using the workflow described in tokenrequest.php
	$auth_token = "B0YGZPZVOOKVO4N2U3ZNMETMMGWTRGIA0M1GJS5VLPCC2CUQ";
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
		if (isset($location->city))
		{
			$city = $location->city . ", ";
		}
		else {
			$city = "";
		}
		$polyline[$route] .= "[ " . $location->lat . ", " . $location->lng . "], ";
		$places[] = array('time' => $item->createdAt, 
			              'descr' => $item->venue->name, 
			              'la' => $location->lat, 
			              'lo' => $location->lng, 
			              'city' => $city,
			              'country' => $location->country);
	endforeach
?>

    myMap = new ymaps.Map("map", {
            center: <? echo "[ " . $location->lat . ", " . $location->lng . "]"; ?>,
            zoom: 16
        });

    // Создаем ломаную с помощью вспомогательного класса Polyline.
    var myPolyline = new ymaps.Polyline([
    <?php echo $polyline[$route]; ?>

        ], {}, {
            strokeWidth: 4,
            strokeOpacity: 1,
            strokeStyle: '3 2'
        });

    // Добавляем линии на карту.
	    myMap.geoObjects
	        .add(myPolyline);
	}

    function PutPlaceMark(la, lo, title)
    {
		myPlacemark = new ymaps.GeoObject({
            // Описание геометрии.
            geometry: {
                type: "Point",
                coordinates: [la, lo]
            },
            // Свойства.
            properties: {
                // Контент метки.
                iconContent: title,
                hintContent: ''
            }
        }, {
            // Опции.
            // Иконка метки будет растягиваться под размер ее содержимого.
            preset: 'islands#blackStretchyIcon'
            // Метку можно перемещать.
            //draggable: true
        });       

		myMap.geoObjects.add(myPlacemark);    
    }

    function MoveToLL(la, lo, title)
    {
    	LA = parseFloat(la);
    	LO = parseFloat(lo);
    	myMap.setCenter([LA, LO], 16, { checkZoomRange: true });

    	myMap.geoObjects.remove(myPlacemark);
    	PutPlaceMark(LA, LO, title);
    }
	</script>
	<script>
	    window.onload = function() {
	    if (parent) {
	        var oHead = document.getElementsByTagName("head")[0];
	        var arrStyleSheets = parent.document.getElementsByTagName("link");
	        for (var i = 0; i < arrStyleSheets.length; i++){    
	            oHead.appendChild(arrStyleSheets[i].cloneNode(true));
	        }            
	    }    
	}
	</script>
</head>
<body>
	<div id="map" style="width:640px; height:480px"></div>
	<? foreach($places as $place): ?>
		<p> <?= gmdate("H:i d.m", $place['time']) ?> GMT: <a href="#" onclick="MoveToLL(<?= $place['la'] ?>, <?= $place['lo'] ?>, '<?= htmlentities($place['descr'], ENT_QUOTES, "UTF-8") ?>'); return false;">
		<?= htmlentities($place['descr'], ENT_QUOTES, "UTF-8") ?></a>, <?= $place['city'] . " " . $place['country'] ?> </p>		
	<? endforeach ?>
</body>
</html>
