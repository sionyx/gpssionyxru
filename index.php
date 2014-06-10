<?php

	require 'int/db.php';
	$db = new DB();
	
	//$query = "SELECT COUNT(*) AS `count` FROM `gps_sessions`";
	//$result = mysql_query($query) or die("Запрос не выполнен: " . mysql_error());
	//$line = mysql_fetch_array($result, MYSQL_ASSOC);
	//$count = $line['count'];
	//mysql_free_result($result);
	
	// 15543524
	if (isset($_GET['user']))
	{
		$query = sprintf("SELECT `la` , `lo`, `ac`, `time`, `descr` FROM `gps_sessions` WHERE `userid` = (SELECT `id` FROM `gps_users` WHERE `login` = '%s' LIMIT 0 , 1) AND `time` > '%s' ORDER BY `time` ASC",// LIMIT ".($count - 30)." , 30";
				$_GET['user'],
				date("Y-m-d H:i:s", time() - 1296000));
	}
	else
	{
		$query = sprintf("SELECT `la` , `lo`, `ac`, `time` FROM `gps_sessions` WHERE `time` > '%s' ORDER BY `time` ASC",// LIMIT ".($count - 30)." , 30";
				date("Y-m-d H:i:s", time() - 1296000));
	}
	
	
	$result = mysql_query($query) or die("Запрос не выполнен: " . mysql_error());
	
	$route = 0;
	$polyline[$route] = "";
	$places = array();
   
	while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		//if (isset($last) && (strtotime($line['time']) - strtotime($last['time']) > 7200))
		//{
		//	$route++;
        //    $polyline[$route] = "";
		//}
      
		if ($polyline[$route] != "")
			$polyline[$route] .= ", ";
			
		$polyline[$route] .= "[ " . $line['la'] . ", " . $line['lo'] . "]";
		$last = $line;
		
		$places[] = array('time' => $line['time'], 'descr' => $line['descr'], 'la' => $line['la'], 'lo' => $line['lo']);
	}
	$route++;
	mysql_free_result($result);

if (isset($_GET['mobile']))
{
	$zoom = 16;
	$meta = '<meta name="viewport" content="width=' . $_GET['w'] . ', user-scalable=no" />';
}
else 
{
	$zoom = 10;
	$meta = '';
}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Мы тут</title>
    <?= $meta ?>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <!--
        Подключаем API карт 2.x
        Параметры:
          - load=package.full - полная сборка;
	      - lang=ru-RU - язык русский.
    -->
    <script src="http://api-maps.yandex.ru/2.1/?lang=ru_RU"
            type="text/javascript"></script>

    <script type="text/javascript">
        // Как только будет загружен API и готов DOM, выполняем инициализацию
        var myMap;
        var myPlacemark;
        var Zoom = <?= $zoom ?>;
        ymaps.ready(init);

        function init () {
            myMap = new ymaps.Map("map", {
                    center: [<?= $last['la'] ?>, <?= $last['lo'] ?>],
                    zoom: Zoom
                }),

<?php
for ($j = 0; $j < $route; $j++)
{        
?>
				// Ломаная
                myPolyline<?= $j ?> = new ymaps.Polyline([
                    // Координаты вершин ломаной.
                    <?= $polyline[$j] ?>
                ], {}, {
                    strokeWidth: 3 // ширина линии
                }),  
<?php
}
?>
                myCircle = new ymaps.Circle([
                                             // Координаты центра круга
                                             [<?= $last['la'] ?>, <?= $last['lo'] ?>],
                                             // Радиус круга в метрах
                                             <?= $last['ac'] ?>     
                                             ]);         
                
                // Первый способ задания метки
                //myPlacemark = new ymaps.Placemark([<?= $last['la'] ?>, <?= $last['lo'] ?>], {}, {
                //	preset: 'islands#redStretchyIcon'
                //}),
                // Второй способ
                myGeoObject = new ymaps.GeoObject({
                    // Геометрия.
                    geometry: {
                        // Тип геометрии - точка
                        type: "Point",
                        // Координаты точки.
                        coordinates: [55.8, 37.8]
                    }
                });

            // Добавляем метки на карту
            myMap.geoObjects
<?php
for ($j = 0; $j < $route; $j++)
{        
?>
            	.add(myPolyline<?= $j ?>)
<?php
}
?>
            	.add(myCircle);
            //.add(myGeoObject)
			PutPlaceMark(<?= $last['la'] ?>, <?= $last['lo'] ?>, '<?= htmlentities($last['descr']) ?>');
            

<?php
	if (!isset($_GET['mobile']))
	{ 
?>
			myMap.controls
				// Кнопка изменения масштаба
				.add('zoomControl')
				// Список типов карты
				.add('typeSelector')
				// Стандартный набор кнопок
				.add('mapTools');

			// Также в метод add можно передать экземпляр класса, реализующего определенный элемент управления.
			// Например, линейка масштаба ('scaleLine')
			myMap.controls
				.add(new ymaps.control.ScaleLine())
				// В конструкторе элемента управления можно задавать расширенные
				// параметры, например, тип карты в обзорной карте
				.add(new ymaps.control.MiniMap({
				    type: 'yandex#publicMap'
				}))
				.add(new ymaps.control.TrafficControl({
					providerKey: 'traffic#actual'
				}));
<?php
   	}
?>
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

        function MoveByXY(x, y)
        {
        	var coords = myMap.getGlobalPixelCenter();
        	coords[0] = coords[0] - parseInt(x);
        	coords[1] = coords[1] - parseInt(y);
        	myMap.setGlobalPixelCenter(coords, Zoom, {duration: 0});
        }
        function MoveToLL(la, lo, title)
        {
        	LA = parseFloat(la);
        	LO = parseFloat(lo);
        	myMap.setCenter([LA, LO], Zoom, { checkZoomRange: true });

        	myMap.geoObjects.remove(myPlacemark);
        	PutPlaceMark(LA, LO, title);
        }

        function ZoomIn()
        {
            Zoom = Zoom + 1;
            myMap.setZoom(Zoom, {duration: 0});
        	//Zoom = myMap.getZoom();
        }
        
        function ZoomOut()
        {
            Zoom --;
            myMap.setZoom(Zoom, {checkZoomRange: true, duration: 0});
        	//Zoom = myMap.getZoom();
        }
        
        
    </script>
</head>

<body>
<?php
	if (isset($_GET['mobile']))
	{ 
?>
<div id="map" style="width:<?= $_GET['w'] ?>px; height:<?= $_GET['h'] ?>px"></div>
<?php
	}
	else 
	{ 
?>
<h2>Мы тут</h2>

<a href="https://foursquare.com/oauth2/authenticate?client_id=NFMQSR1Y22XRO3DBDRCCAY2JAGC1M5HVBTEBTJFBASCOU2NJ&response_type=code&redirect_uri=http://gps.sionyx.ru/service/fsredirect.php"><img src="connect-fs.png" /></a><br />

<?php

	$query = "SELECT * FROM `gps_users` WHERE `fstoken` != '' LIMIT 0 , 30";
	$result = mysql_query($query) or die("Запрос не выполнен: " . mysql_error());
	
	while ($user = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		?>
			<a href="http://gps.sionyx.ru/?user=<?= $user['login'] ?>" ><?= $user['firname'] ?> <?= $user['surname'] ?></a>
		<?php  
	}
	mysql_free_result($result);
	
?>
<div id="map" style="width:600px; height:500px"></div>
<?php 
	foreach ($places as $place) 
	{
		?>
		<p><?= date("Y-m-d H:i:s", strtotime($place['time']) + 14400)  ?>: <a href="#" onclick="MoveToLL(<?= $place['la'] ?>, <?= $place['lo'] ?>, '<?= htmlentities($place['descr'], ENT_QUOTES, "UTF-8") ?>'); return false;"><?= $place['descr'] ?></a></p>
		<?php 
	}	
?>
<!-- a href="#" onclick="MoveXY(10, 10); return false;">move</a -->
<?php
	} 
?>
</body>

</html>
<?php 
?>