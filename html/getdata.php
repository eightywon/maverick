<?php
        include_once('db.php');

        $db=Database::getInstance();
        $pdo=$db->getConnection();

	//set the cookID, activeCook
	$cookID=$_POST['cookid'];
	if ($_COOKIE['cookid']) {
		$activeCook=$_COOKIE['cookid'];
	} else {
        	$single=Database::selectSingle('select cookid from activecook',$pdo);
		$activeCook=$single['cookid'];
	}

	if ($_POST['reqType']=="temps") {
		if ($cookID==$activeCook) {
		       	$single=Database::selectSingle('select probe1,probe2,time from readings where cookid='.$cookID.' order by time desc limit 1',$pdo);
			$temps['probe1']=$single['probe1'];
			$temps['probe2']=$single['probe2'];
			$when=strtotime($single['time']);
			$temps['when']=date('m',$when)."/".date('d',$when)."/".date('Y',$when)." at ".date('g',$when).":".date('i',$when).":".date('sa',$when);
		} else {
			$temps['probe1']='';
			$temps['probe2']='';
			$temps['when']='Cook #'.$cookID;
		}
		$json=json_encode($temps);
		echo $json;
	} elseif ($_POST['reqType']=="chart") {
		$query="select probe1,probe2,time from readings where cookid=".$_POST['cookid']." order by time desc";
		if ($_POST['cookid']==$activeCook) {
			$query=$query." limit 500";
		}
		$results=Database::select($query,$pdo);
		if ($result!==false) {
			echo "{\n\"cols\": [\n {\"label\": \"A\", \"type\": \"datetime\"},
				{\"label\": \"Food\", \"type\": \"number\"},
				{\"label\": \"Pit\", \"type\": \"number\"},
				{\"type\": \"string\", \"role\": \"annotation\"},
				{\"type\": \"string\", \"role\": \"annotationText\",\"p\": {\"html\": false}}
				],\n\"rows\": [\n";
			//echo "{\n\"cols\": [\n {\"label\": \"A\", \"type\": \"datetime\"},\n {\"label\": \"Food\", \"type\": \"number\"},\n {\"label\": \"Pit\", \"type\": \"number\"}\n ],\n\"rows\": [\n";
			$flag=true;
			//$theNote="\"heres a note\"";
			foreach ($results as $row) {
				if (!$flag) {
					echo ",\n";
				}
				$single=Database::selectSingle("select note from notes where notes.cookid=".$_POST['cookid']." and notes.time='".$row['time']."'",$pdo);
				//echo "\n".$single."\n";
				if ($single['note']=="") {
					$theLabel="null";
					$theNote="null";
				} else {
					$theLabel="\"N\"";
					$theNote="\"".$single['note']. "\"";
				}
				//echo $theNote;
				$t=strtotime($row['time']);
				//echo "  {\"c\":[{\"v\": \"Date(".date('Y',$t).",".(date('m',$t)-1).",".date('d',$t).",".date('G',$t).",".date('i',$t).",".date('s',$t).")\"}, {\"v\": ".$row['probe1']."}, {\"v\": ".$row['probe2']."}]}";
				echo "  {\"c\":[{\"v\": \"Date(".date('Y',$t).",".(date('m',$t)-1).",".date('d',$t).",".date('G',$t).",".date('i',$t).",".date('s',$t).")\"},{\"v\": ".$row['probe1']."},{\"v\": ".$row['probe2']."},{\"v\": ".$theLabel."},{\"v\": ".$theNote."}]}";
				$flag=false;
				//$theNote="null";
			}
			echo "\n]\n}";
		}
	} elseif ($_POST['reqType']=="chartjs") {
		//this returns json object for test.html, new chart in development
		$query="select probe1,probe2,time from readings where cookid=".$_POST['cookid']." order by time desc";
		if ($_POST['cookid']==$activeCook) {
			$query=$query." limit 500";
		}
		$results=Database::select($query,$pdo);
		if ($result!==false) {
			$i=0;
			$len=count($results);
			echo "[{\n";
			foreach ($results as $row) {
				$single=Database::selectSingle("select note from notes where notes.cookid=".$_POST['cookid']." and notes.time='".$row['time']."'",$pdo);
				$t=strtotime($row['time']);
				echo "\"time\": \"".date('Y',$t)."-".date('m',$t)."-".date('d',$t)." ".date('G',$t).":".date('i',$t).":".date('s',$t)."\",\n";
				echo "\"food\": ".$row['probe1'].",\n";
				echo "\"pit\": ".$row['probe2']."\n,";
				echo "\"note\": \"".$single['note']."\"\n";
				if ($i==$len-1) {
					echo "}\n";
				} else {
					echo "},{\n";
				}
				$i++;
			}
			echo "]\n";
		}
	}
?>
