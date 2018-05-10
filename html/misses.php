<html>
 <head>
  <!-- <meta http-equiv="refresh" content="15;URL='misses.php?cookid=".$_GET['cookid']."'"> -->
 </head>
 <body>
	<?php

		include_once('db.php');
		$db=Database::getInstance();
		$pdo=$db->getConnection();

		$query="select probe1,probe2,time from readings where cookid=".$_GET['cookid']." order by time desc";
		$results=Database::select($query,$pdo);
		if ($result!==false) {
			foreach ($results as $row) {
				$t=strtotime($row['time']);
				if ($lt-$t>13) {
					echo "missed at ".date('F jS @ h:i:s a',strtotime($row['time'])+12)."\n<br>";
				}
				$lt=$t;
			}
		}
	?>
 </body>
</html>
