<?php
	include_once('db.php');
	$db=Database::getInstance();
	$pdo=$db->getConnection();

	if (isset($_POST["setAlerts"])) {
		$pL=$_POST['pitLow'];
		$pH=$_POST['pitHi'];
		$fL=$_POST['foodLow'];
		$fH=$_POST['foodHi'];
		$email=$_POST['alertEmail'];
		$query="update cooks set pitLow='".$pL."',pitHi='".$pH."',foodLow='".$fL."',foodHi='".$fH."',email='".$email."' where id=".$_COOKIE['cookID'].";";
	        $single=Database::update($query,$pdo);
	} else {
	        $row=Database::selectSingle('SELECT pitLow,pitHi,foodLow,foodHi,email FROM cooks WHERE id='.$_COOKIE['cookID'],$pdo);
		$pL=$row['pitLow'];
		$pH=$row['pitHi'];
		$fL=$row['foodLow'];
		$fH=$row['foodHi'];
		$email=$row['email'];
	}
?>

<!DOCTYPE html>
<html lang="en">
 <head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="../../favicon.ico">
  <title>ET-732 Alerts</title>
  <!-- Bootstrap core CSS -->
  <link href="./css/bootstrap.min.css" rel="stylesheet">
  <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
  <link href="./css/ie10-viewport-bug-workaround.css" rel="stylesheet">
  <!-- Custom styles for this template -->
  <link href="./css/navbar.css" rel="stylesheet">
  <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
  <!--[if lt IE 9]><script src="./js/ie8-responsive-file-warning.js"></script><![endif]-->
  <script src="./js/ie-emulation-modes-warning.js"></script>
  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  <script   src="https://code.jquery.com/jquery-3.1.0.js"   integrity="sha256-slogkvB1K3VOkzAI8QITxV3VzpOnkeNVsKvtkYLMjfk="   crossorigin="anonymous"></script>
 </head>
 <body>
  <div class="container">
   <?php $btnActive[1]=" class='active'";?>
   <?php require 'menu.php';?>
   <!-- Main component for a primary marketing message or call to action -->
   <div class="jumbotron">
    <h2>ET-732 Alerts</h2>
     <div class="row">
      <form action="alerts.php" method="post">
       <div class="form-group">
        <div class="col-sm-2 col-xs-4">
         <label for="pitLow">Pit Low:</label><input type="number" class="form-control" name="pitLow" id="pitLow" min="1" max="500" value=<?=$pL?>>
         <label for="pitHigh">Pit High:</label><input type="number" class="form-control" name="pitHi" id="pitHi" min="1" max="500" value=<?=$pH?>>
         <label for="foodLow">Food Low:</label><input type="number" class="form-control" name="foodLow" id="foodLow" min="1" max="500" value=<?=$fL?>>
         <label for="foodHigh">Food High:</label><input type="number" class="form-control" name="foodHi" id="foodHi" min="1" max="500" value=<?=$fH?>><br/>
         <label for="alertEmail">To:</label><input type="email" class="form-control" name="alertEmail" id="alertEmail" value=<?=$email?>><br/>
         <input class="btn btn-lg btn-success" type="submit" value="Set Alerts" name="setAlerts" id="setAlerts">
        </div>
       </div>
      </form>
     </div>
    </div>
   </div> <!-- /container -->
   <?php require 'footer.php';?>
 </body>
</html>

