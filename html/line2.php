<html>
  <head>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript">

    google.charts.load('current', {'packages':['corechart','line']});
	google.charts.setOnLoadCallback(function() {
		$(function() {
			refreshChart();

			$("#cookid").change(function() {
				refreshChart();
			});

			$("#showFood").change(function() {
				refreshChart();
			});

			$("#showPit").change(function() {
				refreshChart();
			});

			function refreshChart() {
				var chartData = $.ajax({
					url: "getdata.php",
					data: {'reqType': 'chart', 'cookid': $("#cookid").val()},
					type: "POST",
					dataType: "json",
					async: false
				}).responseText;
				drawChart(chartData);
			}
			setInterval(refreshChart,10000);

			var callAjax = function(){
				$.ajax({
					url:'getdata.php',
					type:'POST',
					data: {'reqType': 'temps', 'cookid': $("#cookid").val()},
					dataType: "json",
					success:function(data){
						$("#food").html("Food: "+data['probe1']);
						$("#pit").html("Pit: "+data['probe2']);
						$("#when").html(data['when']);
					}
				});

			}
			setInterval(callAjax,10000);

		});//jquery load
	}); //google chart

	function drawChart(chartJson) {
		if ($("#showPit").is(":checked") || $("#showFood").is(":checked")) {
			var options = {
				hAxis: {
					title: 'Time',
					textStyle: {
						color: '#01579b',
						fontSize: 20,
						fontName: 'Arial',
						bold: true,
						italic: true
					},
					titleTextStyle: {
						color: '#01579b',
						fontSize: 16,
						fontName: 'Arial',
						bold: false,
					italic: true
					}
				},
				vAxis: {
					title: 'Temp',
					textStyle: {
						color: '#1a237e',
						fontSize: 24,
						bold: true
					},
					titleTextStyle: {
						color: '#1a237e',
						fontSize: 24,
						bold: true
					}
				},
				colors: ['#a52714', '#097138'],
				explorer: {
					actions: ['dragToZoom', 'rightClickToReset'],
					axis: 'horizontal',
					keepInBounds: true,
					maxZoomIn: 10.0
				}
			};

			// Create our data table out of JSON data loaded from server.
			var data = new google.visualization.DataTable(chartJson);

			// Instantiate and draw our chart, passing in some options.
			var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
			chart.draw(data,options);

			if (!$("#showPit").is(":checked") || !$("#showFood").is(":checked")) {
				view = new google.visualization.DataView(data);
				if (!$("#showFood").is(":checked")) {
					view.hideColumns([1]);
				}
				if (!$("#showPit").is(":checked")) {
					view.hideColumns([2]);
				}
				chart.draw(view, options);
			}

		} else {
			$("#chart_div").html("");
		}
	} //drawChart

    </script>
  </head>
  <body>
   <table width=90% align=center>
    <tr align=center>
     <td width=50%><h1><div id="pit">Pit: </div></h1></td>
     <td width=50%><h1><div id="food">Food: </div></h1></td>
    </tr>
    <tr align=center>
     <td colspan=2><h2><div id="when"></div></h2></td>
    </tr>
    <tr align=center><td colspan=2><div id='chart_div'></div></td></tr>
    <tr align=left><td width=25%><input type='checkbox' id='showFood' checked>Food</input>&nbsp;
     <input type='checkbox' id='showPit' checked>Pit</input></td><td>&nbsp;</td></tr>
    <?php
	class MyDB extends SQLite3 {
		function __construct() {
			$this->open('the.db');
		}
	}
	$database=new MyDB();
        echo "    <tr align=left><td width=25%>\n";
        echo "     <select id='cookid'>\n";
        $query="SELECT id, start FROM cooks ORDER BY id DESC LIMIT 20";
	if ($result=$database->query($query)) {
		while($row=$result->fetchArray()) {
			$t=strtotime($row['start']);
			echo "      <option value='".$row['id']."'>Cook #".$row['id']." - ".date('m',$t)."/".date('d',$t)."/".date('Y',$t)." at ".date('h',$t).":".date('ia',$t)."</option>\n";
		}
	}
    ?>
   </td><td>&nbsp;</td></tr></table>
  </body>
</html>
