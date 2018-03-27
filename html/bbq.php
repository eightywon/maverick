<?php
	$page = $_SERVER['PHP_SELF'];
	$sec = "30";

        include_once('db.php');

        $db=Database::getInstance();
        $pdo=$db->getConnection();

	$single=Database::selectSingle("select probe1,probe2,time from readings where cookid=".$_COOKIE['cookID']." order by time desc limit 1;",$pdo);
	$probe1=$single['probe1'];
	$probe2=$single['probe2'];
	$time=$single['time'];
?>
<html>
 <head>
  <title>Maverick ET-732 BBQ Thermometer</title>
  <meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">
  <script type='text/javascript' src='https://www.google.com/jsapi'></script>
  <script type='text/javascript'>
   google.load('visualization', '1', {packages:['gauge']});
   google.setOnLoadCallback(drawChart);
   function drawChart() {
	var data=google.visualization.arrayToDataTable([
		['Label', 'Value'],
		['Pit', <?=$probe1?>],
		['Food', <?=$probe2?>],
	]);

	var options={
		width: 600, height: 340,
		redFrom: 250, redTo: 350,
		greenFrom:215, greenTo: 250,
		minorTicks: 10, max:350, min:100, majorTicks:['100', '150', '200', '250', '300', '350']
	};

	var chart=new google.visualization.Gauge(document.getElementById('chart_div'));
	chart.draw(data, options);
	}
  </script>
 </head>
 <body>
  <div id='chart_div' align=center></div><br />
  <center>Last updated: <?=date('l, F jS, Y @ h:ia', strtotime($time));?></center>
 </body>
</html>
