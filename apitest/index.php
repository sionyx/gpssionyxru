<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Travel Map</title>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>

<?php 
	require_once("src/FoursquareAPI.class.php");
	require_once("src/MapHelper.class.php");
	$client_key = "";
	$client_secret = "";  
	$auth_token = "";

	$foursquare = new FoursquareAPI($client_key,$client_secret);
	$foursquare->SetAccessToken($auth_token);
	$params = array("limit" => 250, 
		            "afterTimestamp" => 1403049600);
	$response = $foursquare->GetPrivate("users/self/checkins",$params);
	$places = array();
	
	$checkins = json_decode($response);
	$reversedItems = array_reverse($checkins->response->checkins->items);
	$distance = 0;
	foreach($reversedItems as $item):
		if (isset($location)){
			$distance += MapHelper::vincentyGreatCircleDistance($location->lat, $location->lng, $item->venue->location->lat, $item->venue->location->lng) / 1000;
		}
		$location = $item->venue->location;
		if (isset($location->city))
		{
			$city = $location->city . ", ";
		}
		else 
		{
			$city = "";
		}
		$time = $item->createdAt - $item->timeZoneOffset;
		
		$places[] = array('time' => $time, 
			              'descr' => $item->venue->name, 
			              'la' => $location->lat, 
			              'lo' => $location->lng, 
			              'city' => $city,
			              'country' => $location->country);
	endforeach
?>

	<script>
		var myPlacemark;
		var map;
		function initialize() {
		    var mapCenter = new google.maps.LatLng(<?= $location->lat ?>, <?= $location->lng ?>);
		    var mapOptions = {
		    	zoom: 15,
		    	center: mapCenter,
		    	mapTypeId: google.maps.MapTypeId.ROADMAP
		    };
	    	map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
			var flightPlanCoordinates = [
				<? foreach($places as $place): ?>
			    new google.maps.LatLng(<?= $place['la'] ?>, <?= $place['lo'] ?>),
			    <? endforeach ?>
			];

			var flightPath = new google.maps.Polyline({
			    path: flightPlanCoordinates,
			    geodesic: true,
			    strokeColor: '#FF0000',
			    strokeOpacity: 1.0,
			    strokeWeight: 2
			});

  			flightPath.setMap(map);
  			myPlacemark = new google.maps.Marker({
						    position: mapCenter,
						    map: map,
						    draggable: false,
    						animation: google.maps.Animation.DROP
			});
  		}
  		google.maps.event.addDomListener(window, 'load', initialize);
		


    function PutPlaceMark(la, lo, title)
    {
		myPlacemark = new google.maps.Marker({
						    position: new google.maps.LatLng(la, lo),
						    map: map
		});
		myPlacemark.setMap(map);  
    }

    function MoveToLL(la, lo, title)
    {
    	map.setCenter(new google.maps.LatLng(la, lo));
    	map.setZoom(15);
    	myPlacemark.setMap(null);
    	PutPlaceMark(la, lo, title);
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
	<p>Distance covered: <b><?= number_format($distance, 2, '.', ' ') ?></b> km.</p>
	<div id="map-canvas" style="width:650px; height:480px"></div>
	<? foreach($places as $place): ?>
		<p class=""> <?= gmdate("H:i d.m", $place['time']) ?> GMT: <a href="#" onclick="MoveToLL(<?= $place['la'] ?>, <?= $place['lo'] ?>, '<?= htmlentities($place['descr'], ENT_QUOTES, "UTF-8") ?>'); return false;">
		<?= htmlentities($place['descr'], ENT_QUOTES, "UTF-8") ?></a>, <?= $place['city'] . " " . $place['country'] ?> </p>		
	<? endforeach ?>
</body>
</html>
