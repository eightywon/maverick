<?php
	include_once('db.php');

	$db=Database::getInstance();
	$pdo=$db->getConnection();

	if (isset($_POST["deleteCook"])) {
		$cookid=$_POST['deleteCook'];
		$single=Database::update("delete from cooks where id=".$cookid,$pdo);
		$single=Database::update("delete from readings where cookid=".$cookid,$pdo);
	}

	$results=Database::select("select * from cooks order by id",$pdo);
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
  <title>ET-732 Cooks</title>
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
  <script type="text/javascript">
   $(function() {
	$('[id^=deleteCook]').hide();

        $('[data-toggle=confirmation]').confirmation({
		rootSelector: '[data-toggle=confirmation]',
	});

	$('[id^=deleteCook]').click(function() {
		$('#cookRow'+$(this).val()).remove();

		$.ajax({
			url:'cooks.php',
				type:'POST',
				data: 'deleteCook='+$(this).val(),
		});
	});

	$('[id^=cookRow]').hover(function() {
		$('#deleteCook'+$(this).attr('id').match(/\d+/)).toggle();
	});
   });
  </script>
 </head>
 <body>
  <div class="container">
   <?php $btnActive[3]=" class='active'";?>
   <?php require 'menu.php';?>
   <!-- Main component for a primary marketing message or call to action -->
   <div class="jumbotron">
    <h2>Cooks</h2>
    <table class="table table-hover table-sm">
     <thead><tr><th>Cook ID</th><th>Date</th><th>&nbsp;</th></tr></thead>
     <?php  foreach ($results as $row) { ?>
      <tbody>
       <tr id="cookRow<?=$row['id']?>">
        <td><?=$row['id'].'</td>
        <td>'.$row['start']?></td>
        <td width=15% align=right>
         <button type="button" class="btn btn-xs btn-danger" data-toggle="confirmation" data-singleton="true" data-popout="true" data-btn-ok-class="btn-xs btn-danger" data-placement="left" data-title="Delete '<?=$row['start']?>'?" id="deleteCook<?=$row['id']?>" style="display:none" value=<?=$row['id']?>>
          <span class="glyphicon glyphicon-remove"></span>
         </button>
        </td>
       </tr>
      </tbody>
     <?php  } ?>
    </table>
   </div>
  </div> <!-- /container -->
  <?php require 'footer.php';?>
 </body>
</html>

