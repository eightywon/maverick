<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="refresh" content="1200;URL='./'">
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
    <script src="./nosleep.js"></script>
    <script>
    var noSleep = new NoSleep();
    var allowedToSleep=true;

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
					//$("#silenceAlertDiv").css("display","none");
					$("#silenceAlertDiv").hide();
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
			//alert($('#smoker').val());
			$.ajax({
				url: 'togglecook.php',
				type: 'POST',
				data: $("#alertsForm").serialize(),
				success: function(data) {
					if (data=='Start Cook') {
						$('#toggleCook').prop('value',data);
						$('#toggleCook').removeClass().addClass("btn btn-lg btn-success");
						//$('#alertsDiv').css("display","block");
						$('#alertsDiv').show();
						noSleep.disable();
						allowedToSleep=true;
					} else {
						$('#toggleCook').prop('value',data);
						$('#toggleCook').removeClass().addClass("btn btn-lg btn-danger");
						//$('#alertsDiv').css("display","none");
						$('#alertsDiv').hide();
						if (allowedToSleep) {
							noSleep.enable();
							allowedToSleep=false;
						}
					}
				},
			});
			//$('#silenceAlertDiv').css("display","none");
			$('#silenceAlertDiv').hide();
		});

		var callAjax = function(){
			$.ajax({
				url:'interval.php',
				type:'POST',
				success:function(data){
					if(data=='Start Cook') {
						$('#toggleCook').prop('value',data);
						$('#toggleCook').removeClass().addClass("btn btn-lg btn-success");
						noSleep.disable();
					} else {
						$('#toggleCook').prop('value', data);
						$('#toggleCook').removeClass().addClass("btn btn-lg btn-danger");
					}
				}
			});

		}
		setInterval(callAjax,1000);

		var checkAlerts = function(){
			noSleep.disable();
			allowedToSleep=true;
			$.ajax({
				url:'togglecook.php',
				type:'POST',
				data: 'p1=alerts',
				success:function(data){
					if(data=='alert' && silenceAlerts==false) {
						audio.play();
						//$("#silenceAlertDiv").css("display","block");
						$("#silenceAlertDiv").show();
					} else {
						audio.pause();
						if (silenceAlerts==false) {
							//$("#silenceAlertDiv").css("display","none");
							$("#silenceAlertDiv").hide();
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
    <?php $btnActive[0]=" class='active'";?>
    <?php require 'menu.php';?>
      <!-- Main component for a primary marketing message or call to action -->
      <div class="jumbotron">
       <h2>Maverick ET-732 BBQ Thermometer</h2>
         <?php

			exec("pgrep maverick", $pids);
			if(empty($pids)) {
				$val='Start Cook';
				$btnClass='btn btn-lg btn-success';
				$showAlertsRow='display:block';
			} else {
				$val='Stop Cook';
				$btnClass='btn btn-lg btn-danger';
				$showAlertsRow='display:none';
			}

			class MyDB extends SQLite3 {
				function __construct() {
					$this->open('the.db');
				}
			}
			$database=new MyDB();

			$smokersList=null;
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

			if (($database->querySingle('SELECT cookid FROM activecook'))>-1) {
				$keepAwake="noSleep.enable(); allowedToSleep=false;";
			} else {
				$keepAwake="noSleep.disable(); allowedToSleep=true;";
			}

			if (empty($pids)) {
				$query="SELECT * FROM smokers ORDER BY id DESC;";
				$smokersList=$database->query($query);
			}
         ?>
	    <div id="alertsDiv" class="row" style="<?=$showAlertsRow?>">
	   	 <form id="alertsForm">
	   	  <div class="form-group">
	   	   <div class="col-sm-2 col-xs-4">
	   	    <input type="hidden" name="p1" id="p1" value="clicked"></input>
	   	    <label for="smoker">Smoker:</label>
	   	    <select name="smoker" id="smoker">
	   	    <?php if ($smokersList) { ?>
	   	    <?php  while($smokersRow=$smokersList->fetchArray()) { ?>
	   	     <option value=<?=$smokersRow['id']?>><?=$smokersRow['desc']?></option>
	   	    <?php  } ?>
	   	    <?php } ?>
	   	    <?php $database->close(); ?>
	   	    </select>
                    <br />
	   	    <label for="pitLow">Pit Low:</label><input type="number" class="form-control" name="pitLow" id="pitLow" min="1" max="500" value=<?=$pL?>>
	   	    <label for="pitHigh">Pit High:</label><input type="number" class="form-control" name="pitHi" id="pitHi" min="1" max="500" value=<?=$pH?>>
	   	    <label for="foodLow">Food Low:</label><input type="number" class="form-control" name="foodLow" id="foodLow" min="1" max="500" value=<?=$fL?>>
	   	    <label for="foodHigh">Food High:</label><input type="number" class="form-control" name="foodHi" id="foodHi" min="1" max="500" value=<?=$fH?>>
	   	    <label for="alertEmail">Send To:</label><input type="email" class="form-control" name="alertEmail" id="alertEmail" value=<?=$email?>>
	   	   </div>
	   	  </div>
	   	 </form>
        </div><br />
        <div class="col-md-12">
         <input class="<?=$btnClass?>" type="submit" value="<?=$val?>" id="toggleCook">
        </div>
        <div class="col-md-12" id="silenceAlertDiv" style="display:none">
		 <input class="btn btn-lg btn-danger" type="button" value="Silence" id="silenceAlert">
        </div><br />
      </div>
    </div> <!-- /container -->
    <?php require 'footer.php';?>
    <script><?=$keepAwake?></script>
  </body>
</html>

