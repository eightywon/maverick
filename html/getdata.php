<?php
	class MyDB extends SQLite3
	{
		function __construct()
		{
			$this->open('the.db');
		}
	}

	$database=new MyDB();
	$query="SELECT probe1, probe2, time FROM readings WHERE cookid=".$_COOKIE['cookID']." ORDER BY time DESC LIMIT 100";
	if ($result=$database->query($query))
	{
		echo "{\n\"cols\": [\n {\"label\": \"A\", \"type\": \"datetime\"},\n {\"label\": \"Pit\", \"type\": \"number\"},\n {\"label\": \"Food\", \"type\": \"number\"}\n ],\n\"rows\": [\n";
		$flag=true;
		while($row=$result->fetchArray())
		{
			if (!$flag)
			{
				echo ",\n";
			}
			$t=strtotime($row['time']);
			echo "  {\"c\":[{\"v\": \"Date(".date('Y',$t).",".(date('m',$t)-1).",".date('d',$t).",".date('G',$t).",".date('i',$t).",".date('s',$t).")\"}, {\"v\": ".$row['probe1']."}, {\"v\": ".$row['probe2']."}]}";
			$flag=false;
		}
		echo "\n]\n}";
	}
?>
