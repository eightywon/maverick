<?php
	include_once('db.php');

	$db=Database::getInstance();
	$pdo=$db->getConnection();

	if (isset($_POST["addSmoker"])) {
		$desc=$_POST['desc'];
		$single=Database::update("insert into smokers values (null,'".$desc."')",$pdo);
	} elseif (isset($_POST["deleteSmoker"])) {
		$id=$_POST['deleteSmoker'];
		$single=Database::update("delete from smokers where id=".$id,$pdo);
	}

	$results=Database::select("select * from smokers order by id",$pdo);
?>

<?php require 'header.php';?>
  <script type="text/javascript">
   $(function() {
	$('[id^=deleteSmoker]').hide();
	$("#addSmoker").prop("disabled",true);

        $('[data-toggle=confirmation]').confirmation({
		rootSelector: '[data-toggle=confirmation]',
	});

	$('[id^=deleteSmoker]').click(function() {
		$('#smokerRow'+$(this).val()).remove();

		$.ajax({
			url:'smokers.php',
				type:'POST',
				data: 'deleteSmoker='+$(this).val(),
		});
	});

	$('[id^=smokerRow]').hover(function() {
		$('#deleteSmoker'+$(this).attr('id').match(/\d+/)).toggle();
	});

	$('#desc').on('input', function() {
		if ($(this).val()!='') {
			$("#addSmoker").prop("disabled",false);
		} else {
			$("#addSmoker").prop("disabled",true);
		}
	});
   });
  </script>
 </head>
 <body>
  <div class="container">
   <?php $btnActive[2]=" class='active'";?>
   <?php require 'menu.php';?>
   <!-- Main component for a primary marketing message or call to action -->
   <div class="jumbotron">
    <h2>Smokers</h2>
    <table class="table table-hover table-sm">
     <thead><tr><th>Smoker ID</th><th>Description</th><th>&nbsp;</th></tr></thead>
     <?php  foreach ($results as $row) { ?>
      <tbody>
       <tr id="smokerRow<?=$row['id']?>">
        <td><?=$row['id'].'</td>
        <td>'.$row['desc']?></td>
        <td width=15% align=right>
         <button type="button" class="btn btn-xs btn-danger" data-toggle="confirmation" data-singleton="true" data-popout="true" data-btn-ok-class="btn-xs btn-danger" data-placement="left" data-title="Delete '<?=$row['desc']?>'?" id="deleteSmoker<?=$row['id']?>" style="display:none" value=<?=$row['id']?>>
          <span class="glyphicon glyphicon-remove"></span>
         </button>
        </td>
       </tr>
      </tbody>
     <?php  } ?>
    </table>
    <div class="row">
     <form action="smokers.php" method="post">
      <div class="form-group">
       <h2>Add a Smoker</h2>
       <div class="col-sm-2 col-xs-4">
        <label for="desc">Smoker Name/Description:</label><input type="text" class="form-control" name="desc" id="desc"><br />
        <input class="btn btn-lg btn-success" type="submit" value="Add Smoker" name="addSmoker" id="addSmoker">
       </div>
      </div>
     </form>
    </div>
   </div>
  </div> <!-- /container -->
  <?php require 'footer.php';?>
 </body>
</html>

