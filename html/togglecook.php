<?php
	class MyDB extends SQLite3
	{
		function __construct()
		{
			$this->open('the.db');
		}
	}

	$database=new MyDB();
	$query="SELECT id, strftime('%m/%d/%Y',start) AS 'd', strftime('%H',start) AS 'h', strftime('%M',start) AS 'm' FROM cooks ORDER BY id DESC LIMIT 1;";
	$q1="SELECT cookid FROM activecook;";
	if ($q1Result=$database->query($q1)) {
		while ($q1Row=$q1Result->fetchArray()) {
			$activeCook=$q1Row['cookid'];
		}
	}

	exec("pgrep maverick", $pids);

	//button was clicked
	if ($_POST["p1"]=="clicked") {
		if (!empty($pids)) {
			//maverick program is running, kill it
			$pid=$pids[0];
			exec("sudo kill ".$pid);
			echo "Start Cook";
			mail("2192294610@msg.fi.google.com","Cook Stopped","Cook #".$_COOKIE['cookID']." stopped ".date('m/d/Y h:i a',time()));
			if (isset($_COOKIE['cookID'])) {
				setcookie("cookID","",time()-3600); //delete it
			}
			$query="UPDATE cooks SET end='".date('Y-m-d H:i:s',time())."' WHERE id=".$activeCook.";";
			$database->query($query);
			$query="UPDATE activecook SET cookid=-1";
			$database->query($query);
		} else {
			//maverick program isn't running ,start it
			exec("sudo ./maverick.sh");
			echo "Stop Cook";
			sleep(1);
			if ($result=$database->query($query)) {
				while($row=$result->fetchArray()) {
					setcookie("cookID", $row['id']);
					$dt=$row['d'];
					if ($row['h']==0) {
						$dt=$dt." 12:".$row['m']." am";
					} else if ($row['h']==12) {
						$dt=$dt." 12:".$row['m']." pm";
					} else if ($row['h']>12) {
						$dt=$dt." ".($row['h']-12).":".$row['m']." pm";
					} else {
						$dt=$dt." ".$row['h'].":".$row['m']." am";
					}
					mail("2192294610@msg.fi.google.com","Cook Started","Cook #".$row['id']." started ".$dt);
					$activeCook=$row['id'];
				}
			}

			if (($database->querySingle('SELECT cookid FROM activecook'))>-1) {
				$smoker=$_POST['smoker'];
				$query="UPDATE cooks SET smoker='".$smoker."' WHERE id=".$activeCook.";";
				if (isset($_POST["alertEmail"])) {
					($_POST['pitLow']!='' ? $pL=$_POST['pitLow'] : $pL=0);
					($_POST['pitHi']!='' ? $pH=$_POST['pitHi'] : $pH=0);
					($_POST['foodLow']!='' ? $fL=$_POST['foodLow'] : $fL=0);
					($_POST['foodHi']!='' ? $fH=$_POST['foodHi'] : $fH=0);
					$email=$_POST['alertEmail'];
					$query="UPDATE cooks SET smoker='".$smoker."',pitLow='".$pL."',pitHi='".$pH."',foodLow='".$fL."',foodHi='".$fH."',email='".$email."' WHERE id=".$activeCook.";";
				}
				$database->query($query);
			}
		}
	//ajax call from alerts
	} else if ($_POST["p1"]=="alerts") {
		if (($database->querySingle('SELECT cookid FROM activecook'))>-1) {
			$pL=$database->querySingle('SELECT pitLow FROM cooks WHERE id='.$activeCook.';');
			$pH=$database->querySingle('SELECT pitHi FROM cooks WHERE id='.$activeCook.';');
			$fL=$database->querySingle('SELECT foodLow FROM cooks WHERE id='.$activeCook.';');
			$fH=$database->querySingle('SELECT foodHi FROM cooks WHERE id='.$activeCook.';');
			$probe1=$database->querySingle('SELECT probe1 FROM readings WHERE cookid='.$activeCook.' ORDER BY time DESC LIMIT 1;');
			$probe2=$database->querySingle('SELECT probe2 FROM readings WHERE cookid='.$activeCook.' ORDER BY time DESC LIMIT 1;');
			echo ($probe1>0 && $fH>0 && $probe1>=$fH) ? "Food High: ".$probe1 : "";
			echo ($probe1>0 && $fL>0 && $probe1<=$fL) ? "Food Low: ".$probe1 : "";
			echo ($probe2>0 && $pH>0 && $probe2>=$pH) ? "Pit High: ".$probe2 : "";
			echo ($probe2>0 && $pL>0 && $probe2<=$pL) ? "Pit Low: ".$probe2 : "";
		}
	}
	$database->close();
?>
