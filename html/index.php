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

    <title>Maverick ET-732 BBQ Thermometer</title>

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
    <script>
    	$(function(){
		$('#toggleCook').click(function(){
			$.ajax({
				url: 'togglecook.php',
				type: 'POST',
				data: 'p1=clicked',
				success: function(data) {
					if (data=='Start Cook')
					{
						document.getElementById('cookStatus').innerHTML='Cook Stopped';
						$('#toggleCook').prop('value',data);
						$('#toggleCook').removeClass().addClass("btn btn-lg btn-success");
					}
					else
					{
						document.getElementById('cookStatus').innerHTML='Cook Started';
						$('#toggleCook').prop('value',data);
						$('#toggleCook').removeClass().addClass("btn btn-lg btn-danger");
					}
				},
			});
		});
		$('#cookStatus').bind("DOMSubtreeModified",function(){
                        $('#cookStatus').show();
			$('#cookStatus').fadeOut(3500);
		});
		var callAjax = function(){
			$.ajax({
				url:'togglecook.php',
				type:'POST',
				data: 'p1=interval',
				success:function(data){
					if(data=='Start Cook')
					{
						$('#toggleCook').prop('value',data);
						$('#toggleCook').removeClass().addClass("btn btn-lg btn-success");
					}
					else
					{
						$('#toggleCook').prop('value', data);
						$('#toggleCook').removeClass().addClass("btn btn-lg btn-danger");
					}
				}
			});

        	}
        	setInterval(callAjax,1000);
	});
   </script>
  </head>

  <body>

    <div class="container">

      <!-- Static navbar -->
      <nav class="navbar navbar-default">
        <div class="container-fluid">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="./index.php">Maverick</a>
          </div>
          <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
              <li class="active"><a href="./index.php">Home</a></li>
              <li><a href="alerts.php">Alerts</a></li>
              <li><a href="#">Contact</a></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a href="./bbq.php">Gauge</a></li>
                  <li><a href="./line.php">Line Graph</a></li>
                  <li><a href="#">Something else here</a></li>
                  <li role="separator" class="divider"></li>
                  <li class="dropdown-header">Nav header</li>
                  <li><a href="#">Separated link</a></li>
                  <li><a href="#">One more separated link</a></li>
                </ul>
              </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
            </ul>
          </div><!--/.nav-collapse -->
        </div><!--/.container-fluid -->
      </nav>

      <!-- Main component for a primary marketing message or call to action -->
      <div class="jumbotron">
       <h2>Maverick ET-732 BBQ Thermometer</h2>
        <p>
         <?php
          exec("pgrep maverick", $pids);
          if(empty($pids)) {
           $val='Start Cook';
           $btnClass='btn btn-lg btn-success';
          } else {
           $val='Stop Cook';
           $btnClass='btn btn-lg btn-danger';
          }
         ?>
        <div class="row">
         <div class="col-md-6">
          <input class="<?=$btnClass?>" type="submit" value="<?=$val?>" id="toggleCook">
         </div>
         <div class="col-md-6">
          <span id="cookStatus" class="label label-warning"></span>
         </div>
         </div>
        </p>
      </div>
    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="./js/vendor/jquery.min.js"><\/script>')</script>
    <script src="./js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="./js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>

