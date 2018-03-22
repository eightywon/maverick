<?php
	$page = $_SERVER['PHP_SELF'];
	$sec = "30";

	class MyDB extends SQLite3
	{
		function __construct()
		{
			//$this->open('maverick.db');
			$this->open('the.db');
		}
	}
	$database=new MyDB();
	$query="SELECT probe1, probe2, time FROM readings WHERE cookid=".$_COOKIE['cookID']." ORDER BY time DESC LIMIT 1;";
	if ($result=$database->query($query))
	{
		while($row=$result->fetchArray())
		{
			$probe1=$row['probe1'];
			$probe2=$row['probe2'];
			$time=$row['time'];
		}
	}

?>
<html>
  <head><title>Maverick ET-732 BBQ Thermometer</title>
  <meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">
    <script type='text/javascript' src='https://www.google.com/jsapi'></script>
    <script type='text/javascript'>
      google.load('visualization', '1', {packages:['gauge']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Label', 'Value'],
          ['Pit', <?=$probe1?>],
          ['Food', <?=$probe2?>],
        ]);

        var options = {
          width: 600, height: 340,
          redFrom: 250, redTo: 350,
          greenFrom:215, greenTo: 250,
          minorTicks: 10, max:350, min:100, majorTicks:['100', '150', '200', '250', '300', '350']
        };

        var chart = new google.visualization.Gauge(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
  </head>
  <body>
   <div id='chart_div' align=center></div><br />
   <center><?= ($time ? 'Last updated: '.date('l, F jS, Y @ h:ia', strtotime($time)) : 'There isn\'t anything cooking right now.'); ?></center>
  </body>
</html>

