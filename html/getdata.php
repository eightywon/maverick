<?php
	class MyDB extends SQLite3
	{
		function __construct()
		{
			$this->open('the.db');
		}
	}

	$database=new MyDB();

	if ($_POST['reqType']=="temps") {
		if($_COOKIE['cookid']) {
			$activeCook=$_COOKIE['cookid'];
		} else {
			$activeCook=$database->querySingle('SELECT cookid FROM activecook;');
		}

		if ($_POST['cookid']) {
			$cookID=$_POST['cookid'];
			$temps['probe1']=$database->querySingle('SELECT probe1 FROM readings WHERE cookid='.$_POST['cookid'].' ORDER BY time DESC LIMIT 1;');
			$temps['probe2']=$database->querySingle('SELECT probe2 FROM readings WHERE cookid='.$_POST['cookid'].' ORDER BY time DESC LIMIT 1;');
			$when=strtotime($database->querySingle('SELECT time FROM readings WHERE cookid='.$_POST['cookid'].' ORDER BY time DESC LIMIT 1;'));
			$temps['when']=date('m',$when)."/".date('d',$when)."/".date('Y',$when)." at ".date('g',$when).":".date('ia',$when);
		}

		if ($_POST['cookid']!=$activeCook) {
			$temps['probe1']='';
			$temps['probe2']='';
			$temps['when']='Cook #'.$cookID;
		}

		$json=json_encode($temps);
		echo $json;
	} elseif ($_POST['reqType']=="chart") {
		$query="SELECT probe1, probe2, time FROM readings WHERE cookid=".$_POST['cookid']." ORDER BY time DESC LIMIT 500";
		if ($result=$database->query($query)) {
			echo "{\n\"cols\": [\n {\"label\": \"A\", \"type\": \"datetime\"},\n {\"label\": \"Food\", \"type\": \"number\"},\n {\"label\": \"Pit\", \"type\": \"number\"}\n ],\n\"rows\": [\n";
			$flag=true;
			while($row=$result->fetchArray()) {
				if (!$flag) {
					echo ",\n";
				}
				$t=strtotime($row['time']);
				echo "  {\"c\":[{\"v\": \"Date(".date('Y',$t).",".(date('m',$t)-1).",".date('d',$t).",".date('G',$t).",".date('i',$t).",".date('s',$t).")\"}, {\"v\": ".$row['probe1']."}, {\"v\": ".$row['probe2']."}]}";
				$flag=false;
			}
			echo "\n]\n}";
		}
	}
	$database->close();
	unset($database);
?>
