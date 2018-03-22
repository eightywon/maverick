<html>
 <head>
  <style>
   #loading-img {
    background: url(loading.gif) center top no-repeat;
    height: 100%;
   }

   .loading {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 20;
   }
  </style>
  <!--Load the AJAX API-->
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script type="text/javascript">
   google.charts.load('current', {'packages':['line']});
   google.charts.setOnLoadCallback(function() {
	$(function() {
		//initialize
		$('.loading').show();
		callAjax();
		refreshChart();

		$("#cookid").change(function() {
			$('#chart_div').html('');
			$('#toggleLines').hide();
			$('#selectCook').hide();
			$('.loading').show();
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
			$.ajax({
				url: "getdata.php",
				data: {'reqType': 'chart', 'cookid': $("#cookid").val()},
				type: "POST",
				async: true,
				dataType: "json",
				success:function(data) {
					drawChart(data);
				}
			});
		}

		function callAjax() {
			$.ajax({
				url: "getdata.php",
				type: "POST",
				data: {'reqType': 'temps', 'cookid': $("#cookid").val()},
				async: false,
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

		//if active cook is being displayed, set callback to refresh chart every 10 seconds
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
					fontSize: 20
				},
				titleTextStyle: {
					fontSize: 24
				}
			},
			vAxis: {
				title: 'Temp (F)',
				textStyle: {
					fontSize: 20
				},
				titleTextStyle: {
					fontSize: 24
				}
			},
			colors: ['#008789', '#5b145b'],
			explorer: {
				actions: ['dragToZoom', 'rightClickToReset'],
				axis: 'horizontal',
				keepInBounds: true,
				maxZoomIn: 10.0
			}
		};

		// Create our data table out of JSON data loaded from server.
		data=new google.visualization.DataTable(chartJson);

		// Instantiate and draw our chart, passing in some options.
		chart=new google.charts.Line(document.getElementById('chart_div'));
		chart.draw(data, google.charts.Line.convertOptions(options));

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
							$('#chart_div').html('');
							$('#toggleLines').hide();
							$('#selectCook').hide();
							$('.loading').show();
							$.ajax({
								url: "getdata.php",
								data: {'reqType': 'chart', 'cookid': $("#cookid").val()},
								type: "POST",
								dataType: "json",
								async: true,
								success:function(data) {
									drawChart(data);
								}
							});
						} else { alert('failed');}
					}
				});
			}
			});

		//show or hide the food or pit graphs based on user input
		if (!$("#showPit").is(":checked") || !$("#showFood").is(":checked")) {
			view=new google.visualization.DataView(data);
			if (!$("#showFood").is(":checked")) {
				view.hideColumns([1]);
			}
			if (!$("#showPit").is(":checked")) {
				view.hideColumns([2]);
			}
			chart.draw(data, google.charts.Line.convertOptions(options));
		}

		//chart loaded, hide loading gif, show UI
		$('.loading').hide();
		$('#toggleLines').show();
		$('#selectCook').show();
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
    <td width=50%><h1><div id="pit"></div></h1></td>
    <td width=50%><h1><div id="food"></div></h1></td>
   </tr>
   <tr align=center>
    <td colspan=2><h2><div id="when"></div></h2></td>
   </tr>
   <tr align=center>
    <td colspan=2>
     <div class="loading"><div id="loading-img"></div></div>
     <div id='chart_div'></div>
    </td>
   </tr>
   <tr align=left>
    <td width=25%>
     <div id="toggleLines" style="display:none">
      <input type='checkbox' id='showFood' checked>Food</input>&nbsp;
      <input type='checkbox' id='showPit' checked>Pit</input>
     </div>
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
        echo "   <tr align=left><td width=25%><div id='selectCook' style='display:none'>\n";
        echo "    <select id='cookid'>\n";
	if ($activeCook!='-1') {
		echo "     <option value='".$activeCook."' selected>Active Cook (#".$activeCook.")</option>";
	}
        $query="SELECT id, start FROM cooks ORDER BY id DESC LIMIT 20";
	if ($result=$database->query($query)) {
		if ($activeCook!='-1') {
			$result->fetchArray(); //prime read to pass up active cook
		}
		while($row=$result->fetchArray()) {
			$t=strtotime($row['start']);
			echo "     <option value='".$row['id']."'>Cook #".$row['id']." - ".date('m',$t)."/".date('d',$t)."/".date('Y',$t)." at ".date('h',$t).":".date('ia',$t)."</option>\n";
		}
	}
    ?>
    </select></div></td><td>&nbsp;</td>
   </tr>
  </table>
 </body>
</html>
