<?php
	exec("pgrep maverick", $pids);
	echo (!empty($pids) ? "Stop Cook" : "Start Cook");
?>
