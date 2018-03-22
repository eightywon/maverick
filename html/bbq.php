<?php
	$page = $_SERVER['PHP_SELF'];
	$sec = "30";

        include_once('db.php');

        $db=Database::getInstance();
        $pdo=$db->getConnection();

	$single=Database::selectSingle('select probe1,probe2,time from readings where cookid='.$_COOKIE['cookid'].' order by time desc limit 1',$pdo);
	$probe1=$single['probe1'];
	$probe2=$single['probe2'];
	$time=$single['time'];
?>
<html>
 <head>
  <title>Maverick ET-732 BBQ Thermometer</title>
  <meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">
  <script type='text/javascript' src='https://www.gstatic.com/charts/loader.js'></script>
  <script type='text/javascript'>
      google.charts.load('current', {packages:['gauge']});
      google.charts.setOnLoadCallback(drawFoodChart);
      google.charts.setOnLoadCallback(drawPitChart);

      function drawFoodChart() {
        var data = google.visualization.arrayToDataTable([
          ['Label', 'Value'],
          ['Food', <?=$probe1?>]
        ]);

	var foodOptions = {
	  width:275, height: 275,
	  redFrom: 203, redTo: 250,
	  yellowFrom: 130, yellowTo: 165,
	  greenFrom: 165, greenTo: 203,
	  minorTicks: 10, max:250, min:100, majorTicks:['100', '150', '200', '250']
	};

        var chart = new google.visualization.Gauge(document.getElementById('food_div'));
        chart.draw(data, foodOptions);
      }

      function drawPitChart() {
	var data = google.visualization.arrayToDataTable([
	  ['Label', 'Value'],
	  ['Pit', <?=$probe2?>]
	]);

        var pitOptions = {
          width: 275, height: 275,
          redFrom: 300, redTo: 350,
	  yellowFrom: 250, yellowTo: 300,
          greenFrom: 215, greenTo: 250,
          minorTicks: 10, max:350, min:100, majorTicks:['100', '150', '200', '250', '300', '350']
        };

	var chart = new google.visualization.Gauge(document.getElementById('pit_div'));
	chart.draw(data, pitOptions);
      }
  </script>
 </head>
 <body>
  <div id='chart_div' align=center></div><br />
  <center><?= ($time ? 'Last updated: '.date('l, F jS, Y @ h:ia', strtotime($time)) : 'There isn\'t anything cooking right now.'); ?></center>
 </body>
</html>
