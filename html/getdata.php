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
			echo "{\n\"cols\": [\n {\"label\": \"A\", \"type\": \"datetime\"},\n {\"label\": \"Food\", \"type\": \"number\"},\n {\"label\": \"Pit\", \"type\": \"number\"}\n ],\n\"rows\": [\n";
			$flag=true;
			foreach ($results as $row) {
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
?>
