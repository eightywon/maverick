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
	if ($_POST["p1"]=="clicked")
	{
        if (!empty($pids)) {
			$pid=$pids[0];
			exec("sudo kill ".$pid);
			echo "Start Cook";
			mail("2192294610@msg.fi.google.com","Cook Stopped","Cook #".$_COOKIE['cookID']." stopped ".date('m/d/Y h:i a',time()));
			if (isset($_COOKIE['cookID']))
			{
				setcookie("cookID","",time()-3600); //delete it
			}
			$query="UPDATE cooks SET end='".date('Y-m-d H:i:s',time())."' WHERE id=".$activeCook.";";
			$database->query($query);
			/*
			strftime(buff, 20, "%Y-%m-%d %H:%M:%S", localtime(&now));
			$query="INSERT id, strftime('%m/%d/%Y',start) AS 'd', strftime('%H',start) AS 'h', strftime('%M',start) AS 'm' FROM cooks ORDER BY id DESC LIMIT 1;";
			*/
        }
		else
		{
			exec("sudo ./maverick > /var/log/nginx/maverick.log &");
			echo "Stop Cook";
			sleep(2);
			if ($result=$database->query($query))
			{
				while($row=$result->fetchArray())
				{
					setcookie("cookID", $row['id']);
					$dt=$row['d'];
					if ($row['h']==0)
					{
						$dt=$dt." 12:".$row['m']." am";
					}
					else if ($row['h']==12)
					{
						$dt=$dt." 12:".$row['m']." pm";
					}
					else if ($row['h']>12)
					{
						$dt=$dt." ".($row['h']-12).":".$row['m']." pm";
					}
					else
					{
						$dt=$dt." ".$row['h'].":".$row['m']." am";
					}
					mail("2192294610@msg.fi.google.com","Cook Started","Cook #".$row['id']." started ".$dt);
				}
			}
		}
	}
	else
	{
		if (!empty($pids))
		{
			echo "Stop Cook";
                        if ($result=$database->query($query))
                        {
                                while($row=$result->fetchArray())
                                {
					setcookie("cookID", $row['id']);
                                }
                        }
		}
		else
		{
			echo "Start Cook";
		}
	}
?>
