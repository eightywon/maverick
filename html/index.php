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
		silenceAlerts=false;
		var audio = new Audio('alarm.mp3');
		audio.loop=true;

		$("#silenceAlert").click(function(){
			$("#silenceAlert").prop("disabled",true);
			audio.pause();
			silenceAlerts=true;

			var counter=300;
			var countdown = function(){
				counter--;
				$("#silenceAlert").prop('value', '('+counter+')');
				if (counter==0) {
					$("#silenceAlertDiv").css("display","none");
					$("#silenceAlert").prop('value', 'Silence');
					$("#silenceAlert").prop("disabled",false);
					clearInterval(handle);
					handle=0;
					silenceAlerts=false;
				}
			}
			var handle=setInterval(countdown,1000);
		});

		$('#toggleCook').click(function(){
			$.ajax({
				url: 'togglecook.php',
				type: 'POST',
				//data: 'p1=clicked',
				data: $("#alertsForm").serialize(),
				success: function(data) {
					if (data=='Start Cook')
					{
						$('#toggleCook').prop('value',data);
						$('#toggleCook').removeClass().addClass("btn btn-lg btn-success");
						$('#alertsDiv').css("display","block");
						$('#alertStatus').css("display","none");
					}
					else
					{
						$('#toggleCook').prop('value',data);
						$('#toggleCook').removeClass().addClass("btn btn-lg btn-danger");
						$('#alertsDiv').css("display","none");
						$('#alertStatus').css("display","block");
					}
				},
			});
		});

		var callAjax = function(){
			$.ajax({
				url:'togglecook.php',
				type:'POST',
				data: 'p1=interval',
				success:function(data){
					if(data=='Start Cook') {
						$('#toggleCook').prop('value',data);
						$('#toggleCook').removeClass().addClass("btn btn-lg btn-success");
					} else {
						$('#toggleCook').prop('value', data);
						$('#toggleCook').removeClass().addClass("btn btn-lg btn-danger");
					}
				}
			});

		}
		setInterval(callAjax,1000);

		var checkAlerts = function(){
			$.ajax({
				url:'togglecook.php',
				type:'POST',
				data: 'p1=alerts',
				success:function(data){
					if(data=='alert' && silenceAlerts==false) {
						audio.play();
						$("#silenceAlertDiv").css("display","block");
					} else {
						audio.pause();
						if (silenceAlerts==false) {
							$("#silenceAlertDiv").css("display","none");
						}
					}
				}
			});
		}
		setInterval(checkAlerts,5000);
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
         <?php
			exec("pgrep maverick", $pids);
			if(empty($pids)) {
				$val='Start Cook';
				$btnClass='btn btn-lg btn-success';
				$showAlertsRow='display:block';
				$alertStatusStyle='display:none';
			} else {
				$val='Stop Cook';
				$btnClass='btn btn-lg btn-danger';
				$showAlertsRow='display:none';
				$alertStatusStyle='display:block';
			}

			class MyDB extends SQLite3 {
				function __construct() {
					$this->open('the.db');
				}
			}
			$database=new MyDB();

			$query="SELECT start,end,pitLow,pitHi,foodLow,foodHi,email FROM cooks ORDER BY id DESC LIMIT 1;";
			if($result=$database->query($query))
			{
				while($row=$result->fetchArray())
				{
					$pL=$row['pitLow'];
					$pH=$row['pitHi'];
					$fL=$row['foodLow'];
					$fH=$row['foodHi'];
					$email=$row['email'];
					$start=$row['start'];
					$end=$row['end'];
				}
			}
			$database->close();
         ?>
	    <div id="alertsDiv" class="row" style="<?=$showAlertsRow?>">
	   	 <form id="alertsForm">
	   	  <div class="form-group">
	   	   <div class="col-sm-2 col-xs-4">
	   	    <input type="hidden" name="p1" id="p1" value="clicked"></input>
	   	    <input type="hidden" name="smoker" id="smoker" value="1"></input>
	   		<label for="pitLow">Pit Low:</label><input type="number" class="form-control" name="pitLow" id="pitLow" min="1" max="500" value=<?=$pL?>>
	   		<label for="pitHigh">Pit High:</label><input type="number" class="form-control" name="pitHi" id="pitHi" min="1" max="500" value=<?=$pH?>>
	   		<label for="foodLow">Food Low:</label><input type="number" class="form-control" name="foodLow" id="foodLow" min="1" max="500" value=<?=$fL?>>
	   		<label for="foodHigh">Food High:</label><input type="number" class="form-control" name="foodHi" id="foodHi" min="1" max="500" value=<?=$fH?>>
	   		<label for="alertEmail">Send To:</label><input type="email" class="form-control" name="alertEmail" id="alertEmail" value=<?=$email?>>
	   	   </div>
	   	  </div>
	   	 </form>
        </div><br />
        <p>
         <div class="col-md-12">
	      <input class="<?=$btnClass?>" type="submit" value="<?=$val?>" id="toggleCook">
         </div>
        </p>
        <p>
         <div class="col-md-12" id="silenceAlertDiv" style="display:none">
		  <input class="<?=$btnClass?>" type="button" value="Silence" id="silenceAlert">
         </div>
        </p>
      </div>
      End: <?=$end?><br />
      Start: <?=$start?><br />
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

