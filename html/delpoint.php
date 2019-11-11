<?php
        include_once('db.php');
        $db=Database::getInstance();
        $pdo=$db->getConnection();
	//format passedv in: Mar 9, 2018, 9:57:26 PM
	//$dateArray=date_create_from_format("M j, Y, g:i:s A",$_POST['time']);
	//format needed by db: 2018-03-10 09:31:26
	//$time=$dateArray->format('Y-m-d H:i:s');
	$time=$_POST['time'];
        $delete=Database::delete("delete from readings where cookid=".$_POST['cookid']." and time='".$time."'",$pdo);
	echo $delete;
?>
