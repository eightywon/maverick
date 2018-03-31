<html>
 <head>
  <meta http-equiv="refresh" content="5;URL='readings.php'">
 </head>
 <body>
	<?php
		class MyDB extends SQLite3
		{
			function __construct()
			{
				$this->open('the.db');
			}
		}
		$database=new MyDB();
		$query="SELECT probe1, probe2, time FROM readings WHERE cookid=".$_COOKIE['cookID']." ORDER BY time DESC;";
		if ($resall=$database->query($query))
		{
			while($rowall=$resall->fetchArray())
			{
				echo "<center>Probe 1: ".$rowall['probe1']." Probe 2: ".$rowall['probe2']." ".date('F jS @ h:i:s a',strtotime($rowall['time']))."</center>";
			}
		}
	?>
 </body>
</html>
