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

<?php require 'header.php';?>
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
   <?php $btnActive[2]=" class='active'";?>
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

