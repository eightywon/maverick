<?php
        include_once('db.php');
        $db=Database::getInstance();
        $pdo=$db->getConnection();
	//cookid, noteid (auto), time, type, note
	$cookid=$_POST['cookid'];
	$time=$_POST['time'];
	$type=$_POST['type'];
	$note=$_POST['note'];
        $insert=Database::update("insert into notes values(".$_POST['cookid'].",null,'".$time."',1,'".$note."')",$pdo);
	echo $insert;
?>
