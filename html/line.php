<html>
  <head>
    <meta http-equiv="refresh" content="20;URL='line.php'">
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script type="text/javascript">

    // Load the Visualization API and the piechart package.
    google.charts.load('current', {'packages':['corechart','line']});

    // Set a callback to run when the Google Visualization API is loaded.
    //commented out in v0.0.2 because we're loading the chart on select now
    google.charts.setOnLoadCallback(function() {
	  $(function() {
	    $("#cookid").change(function() {
			drawChart($("#cookid").val());
		});
		drawChart($("#cookid").val());
	  });
	});

    function drawChart(val) {
      var jsonData = $.ajax({
          url: "getdata.php",
          data: "cookid="+val,
          type: "POST",
          dataType: "json",
          async: false
          }).responseText;

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
            maxZoomIn: 10.0}
      };

      // Create our data table out of JSON data loaded from server.
      var data = new google.visualization.DataTable(jsonData);

      // Instantiate and draw our chart, passing in some options.
      var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
      chart.draw(data,options);

		$("#showFood").change(function() {
			if (!this.checked) {
				view = new google.visualization.DataView(data);
				view.hideColumns([1]);
				if (!$("#showPit").is(":checked")) {
					view.hideColumns([2]);
				}
				chart.draw(view, options);
			} else {
				view = new google.visualization.DataView(data);
				chart.draw(view, options);
			}
		});
		$("#showPit").change(function() {
			if (!this.checked) {
				view = new google.visualization.DataView(data);
				view.hideColumns([2]);
				chart.draw(view, options);
			} else {
				view = new google.visualization.DataView(data);
				chart.draw(view, options);
			}
		});
    }

    </script>
  </head>

  <body>
    <?php
		class MyDB extends SQLite3
		{
			function __construct()
			{
				$this->open('the.db');
			}
		}
		$database=new MyDB();
		$activeCook=$database->querySingle('SELECT cookid FROM activecook;');
		$food=$database->querySingle('SELECT probe1 FROM readings WHERE cookid='.$activeCook.' ORDER BY time DESC LIMIT 1;');
		$pit=$database->querySingle('SELECT probe2 FROM readings WHERE cookid='.$activeCook.' ORDER BY time DESC LIMIT 1;');
		$when=strtotime($database->querySingle('SELECT time FROM readings WHERE cookid='.$activeCook.' ORDER BY time DESC LIMIT 1;'));
		$w=date('m',$when)."/".date('d',$when)."/".date('Y',$when)." at ".date('g',$when).":".date('ia',$when);
	    echo "<table width=90% align=center><tr align=center><td width=50%><h1>Pit: ".$pit."</h1></td><td width=50%><h1>Food: ".$food."</h1></td></tr><tr align=center><td colspan=2><h2>".$w."</h2></td></tr></table>\n";
	    echo "   <table width=90% align=center>\n";
	    echo "    <tr align=center><td colspan=2><div id='chart_div'></div></td></tr>\n";
        echo "    <tr align=left><td width=25%></td><td><input type='checkbox' id='showFood' checked>Food</input>&nbsp;<input type='checkbox' id='showPit' checked>Pit</input></td></tr>\n";
        echo "    <tr align=left><td width=25%></td>\n";
        echo "     <td><select id='cookid'>\n";
        $query="SELECT id, start FROM cooks ORDER BY id DESC LIMIT 20";
				if ($result=$database->query($query))
				{
					while($row=$result->fetchArray())
					{
						$t=strtotime($row['start']);
						echo "      <option value='".$row['id']."'>Cook #".$row['id']." - ".date('m',$t)."/".date('d',$t)."/".date('Y',$t)." at ".date('h',$t).":".date('ia',$t)."</option>\n";
					}
		}
		echo "      </select>\n";
        echo "     </td></tr>\n";
        echo "   </table>\n";
	?>
  </body>
</html>
