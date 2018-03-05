	<?php
	class MyDB extends SQLite3
	{
		function __construct()
		{
			$this->open('the.db');
		}
	}
	$database=new MyDB();
	$query="SELECT pitLow,pitHi,foodLow,foodHi FROM cooks WHERE id=".$_COOKIE['cookID'].";";
	if ($result=$database->query($query))
	{
		while($row=$result->fetchArray())
		{
			$pL=$row['pitLow'];
			$pH=$row['pitHi'];
			$fL=$row['foodLow'];
			$fH=$row['foodHi'];
		}
	}
	$query="SELECT probe1,probe2 FROM readings WHERE cookid=".$_COOKIE['cookID']." ORDER BY time DESC LIMIT 1;";
        if ($result=$database->query($query))
        {
                while($row=$result->fetchArray())
                {
                        $probe1=$row['probe1'];
                        $probe2=$row['probe2'];
                }
        }
	if ($_POST["p"]=="alerts")
	{
		if ($probe1<$pL)
		{
			echo "PIT LOW: ".$probe1;
		}
		else if ($probe1>$pH)
		{
			echo "PIT HIGH: ".$probe1;
		}

		if ($probe2<$fL)
		{
			echo "FOOD LOW: ".$probe2;
		}
		else if ($probe2>$fH)
		{
			echo "FOOD HIGH: ".$probe2;
		}
		/*
                if (!empty($pids))
                {
			$pid=$pids[0];
                        exec("sudo kill ".$pid);
                        echo "Start Cook";
			mail("2192294610@mms.att.net","Cook Stopped","Cook #".$_COOKIE['cookID']." stopped ".date('m/d/Y h:i a',time()));
			if (isset($_COOKIE['cookID']))
			{
				setcookie("cookID","",time()-3600); //delete it
			}
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
					mail("2192294610@mms.att.net","Cook Started","Cook #".$row['id']." started ".$dt);
				}
			}
		}
		*/
	}
	else if ($_POST['p']=="send")
	{
		mail("2192294610@mms.att.net","Pit High Alert","Oh nooooooo");
	}
?>
