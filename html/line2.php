<html>
   <head>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript">
    google.charts.load('current', {'packages':['corechart','line']});
	google.charts.setOnLoadCallback(function() {
		$(function() {
			callAjax();
			refreshChart();

			$("#cookid").change(function() {
				callAjax();
				refreshChart();
				if ($("#cookid option:selected").text().indexOf("Active")>=0) {
					clearInterval(chartInterval);
					chartInterval=setInterval(refreshChart,10000);

					clearInterval(ajaxInterval);
					ajaxInterval=setInterval(callAjax,10000);
				} else {
					clearInterval(chartInterval);
					clearInterval(ajaxInterval);
				}
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

			//var callAjax = function(){
			function callAjax() {
				$.ajax({
					url:'getdata.php',
					type:'POST',
					data: {'reqType': 'temps', 'cookid': $("#cookid").val()},
					dataType: "json",
					success:function(data){
						if (data['when'].indexOf("Cook #")>=0) {
							$("#food").html("");
							$("#pit").html("");
						} else {
							$("#food").html("Food: "+data['probe1']);
							$("#pit").html("Pit: "+data['probe2']);
						}
						$("#when").html(data['when']);
					}
				});

			}
			if ($("#cookid option:selected").text().indexOf("Active")>=0) {
				chartInterval=setInterval(refreshChart,10000);
				ajaxInterval=setInterval(callAjax,10000);
			}

		});//jquery load
	}); //google chart

	var data, chart;
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
				},
				tooltip: {trigger: 'selection',
					  ignoreBouds: true,
					  isHtml: true,
					  pivot: {x: -50, y: -80}
					 }
			};

			// Create our data table out of JSON data loaded from server.
			data=new google.visualization.DataTable(chartJson);
			// Instantiate and draw our chart, passing in some options.
			chart=new google.visualization.LineChart(document.getElementById('chart_div'));
			chart.draw(data,options);
			//add event for body clicks that clears the tooltip
			document.body.addEventListener('click',clearSelection,true);
			//add event for tootltip delete data point click to perform the query
			chart.setAction({
				id: 'delPoint',
				text: 'X - delete data point',
				action: function() {
					selection=chart.getSelection();
					dtstring=data.getFormattedValue(selection[0].row,0);
					$.ajax({
						url: 'delpoint.php',
						type:'POST',
						data: {'cookid': $("#cookid").val(), 'time': dtstring},
						success:function(data){
							if (data!='fail') {
								var chartData = $.ajax({
									url: "getdata.php",
									data: {'reqType': 'chart', 'cookid': $("#cookid").val()},
									type: "POST",
									dataType: "json",
									async: false
								}).responseText;
								drawChart(chartData);
							} else { alert('failed');}
						}
					});
				}

			});

			//show or hide the food or pit graphs based on user input
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

	//clears tooltip when anything outside of chart is clicked/tapped
	function clearSelection (e) {
		if (!document.querySelector('#chart_div').contains(e.srcElement)) {
			chart.setSelection();
		}
	}

	function addEvent(element, evnt, funct){
		if (element.attachEvent) {
			return element.attachEvent('on'+evnt, funct);
		} else {
			return element.addEventListener(evnt, funct, false);
		}
	}
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
    <tr align=left>
     <td width=25%>
      <input type='checkbox' id='showFood' checked>Food</input>&nbsp;
      <input type='checkbox' id='showPit' checked>Pit</input>
     </td>
     <td>&nbsp;</td>
    </tr>
    <?php
	class MyDB extends SQLite3 {
		function __construct() {
			$this->open('the.db');
		}
	}
	$database=new MyDB();
	if ($_COOKIE['cookid']) {
		$activeCook=$_COOKIE['cookid'];
	} else {
		$activeCook=$database->querySingle('SELECT cookid from activecook;');
	}
        echo "    <tr align=left><td width=25%>\n";
        echo "     <select id='cookid'>\n";
	if ($activeCook!='-1') {
		echo "      <option value='".$activeCook."' selected>Active Cook (#".$activeCook.")</option>";
	}
        $query="SELECT id, start FROM cooks ORDER BY id DESC LIMIT 20";
	if ($result=$database->query($query)) {
		if ($activeCook!='-1') {
			$result->fetchArray(); //prime read to pass up active cook
		}
		while($row=$result->fetchArray()) {
			$t=strtotime($row['start']);
			echo "      <option value='".$row['id']."'>Cook #".$row['id']." - ".date('m',$t)."/".date('d',$t)."/".date('Y',$t)." at ".date('h',$t).":".date('ia',$t)."</option>\n";
		}
	}
    ?>
   </td><td>&nbsp;</td></tr></table>
  </body>
</html>
