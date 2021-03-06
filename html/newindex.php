<!DOCTYPE html>
<html>
  <head>
    <title>BBQPi</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/solid.css" integrity="sha384-v2Tw72dyUXeU3y4aM2Y0tBJQkGfplr39mxZqlTBDUZAb9BGoC40+rdFCG0m10lXk" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/fontawesome.css" integrity="sha384-q3jl8XQu1OpdLgGFvNRnPdj5VIlCvgsDQTQB6owSOHWlAurxul7f+JpUOVdAiJ5P" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <script src="https://code.jquery.com/jquery-3.1.0.js"   integrity="sha256-slogkvB1K3VOkzAI8QITxV3VzpOnkeNVsKvtkYLMjfk="   crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.js"></script>
    <script src="https://www.chartjs.org/samples/latest/utils.js"></script>
    <style>

      body {
	-webkit-tap-highlight-color: transparent !important;
      }
      html,body,h1,h2,h3,h4,h5 {font-family: "Raleway", sans-serif}
      @media only screen and (min-width: 801px) {
        #top_container {
          display: none;
        }

        #page_content {
          margin-top: 0px !important;
        }

        #page_content_header{
          padding-top: 0px !important;
        }
      }

      @media only screen and (max-width: 800px) {
        #sidebar_header { margin-top: 16px !important;}
      }
      canvas{
        -moz-user-select: none;
	-webkit-user-select: none;
	-ms-user-select: none;
	-webkit-tap-highlight-color: transparent;
        -webkit-backface-visibility:  hidden;
      }
      #chartjs-tooltip {
	opacity: 1;
	position: absolute;
	background: rgba(0, 0, 0, .7);
	color: white;
	border-radius: 3px;
	-webkit-transition: all .1s ease;
	transition: all .1s ease;
	-webkit-transform: translate(-50%, 0);
	transform: translate(-50%, 0);
      }
      .chartjs-tooltip-key {
	display: inline-block;
	width: 10px;
	height: 10px;
	margin-right: 10px;
      }

    </style>
    <script>
	var getWeather;
	var zipCode, apiKey;
	var tooltipVisible=false; //todo: can this be replaced by checking tooltip opacity?
	var pointDetails;
	var isLink=-1;
	var linkDataset=-1;
	var noteLinks=new Array();
	var myChart;
	var flag=false;
	var iconSize;
	var iconXOffset;
	var iconYOffset;

	if (screen.width>=1920) {
		iconSize=36;
		iconXOffset=3;
		iconYOffset=2;
		iconBorder=24;
	} else {
		iconSize=18;
		iconXOffset=6;
		iconYOffset=4;
		iconBorder=18;
	}
	console.log(iconSize);

	function setCookie(form) {
		document.cookie="zipCode="+escape(form.zipCode.value)+";path=/;";
		document.cookie="apiKey="+escape(form.apiKey.value)+";path=/;";
	}

	function getCookie() {
		if (document.cookie) {
			match=document.cookie.match(new RegExp( 'zipCode=([^;]+)'));
			zipCode=match[1];
			match=document.cookie.match(new RegExp( 'apiKey=([^;]+)'));
			apiKey=match[1];
		} else {
			zipCode='92123';
		}
		$("#zipCode").val(zipCode);
		$("#apiKey").val(apiKey);
	}
	//getCookie();

	function showMaskPassword() {
		var fld=document.getElementById("password");
		if (fld.type=="password") {
			fld.type="text";
			$("#showPass").removeClass("fa fa-eye w3-right w3-xlarge");
			$("#showPass").addClass("fa fa-eye-slash w3-right w3-xlarge");
		} else {
			fld.type="password";
			$("#showPass").removeClass("fa fa-eye-slash w3-right w3-xlarge");
			$("#showPass").addClass("fa fa-eye w3-right w3-xlarge");
		}
	}

	function closeSettingsModal() {
		$("#settingsModal:visible").hide();
		document.getElementById("password").type="password";
		$("#showPass").removeClass("fa fa-eye-slash w3-right w3-xlarge");
		$("#showPass").addClass("fa fa-eye w3-right w3-xlarge");
		document.getElementById("ssid").style.backgroundColor="";
		document.getElementById("password").style.backgroundColor="";
	}

	function addWifi() {
		var ssid=document.getElementById("ssid");
		var pass=document.getElementById("password");
		var ret=true;
		if (ssid.value=="") {
			ssid.style.backgroundColor="lightcoral";
			ret=false;
		} else {
			ssid.style.backgroundColor="";
		}
		if (document.getElementById("password").value=="") {
			pass.style.backgroundColor="lightcoral";
			ret=false;
		} else {
			pass.style.backgroundColor="";
		}
		return ret;
	}

	//testing from here
	/*
	todo notes:
		1. add "add note" functionality - DONE
			a. note type - pit or food
			b. add ability to attach pic? for instance, picture of bark. would need to add blob column in db
			c. bug: canvas doesn't refresh to show new note when added, maybe call the function to refresh the chart
		2. fix weather, openweather map?
		3. bug: just found that note card clicks don't translate to the correct point on the chart to activate the correct tooltip
		   there must be some translation issue depending on screen size
		4. bug: note icon way too big on mobile - made this dynamic based on screen.width but it's not working well yet
		5. bug: tooltips overflow left bound
		6. bug: note icons overflow top and left bounds
		7. bug: note icon hovers are inconsistant
		8. todo: consider changing from note icons to custom point sizes to indicate a note has been added at that point (see https://stackoverflow.com/questions/55468483/highlight-a-particular-point-in-chart-js)
		9. delete note when deleting related point
	*/

	//called from mousemove event listener, used to track if mouse is currently over a note icon
	function CanvasMouseMove(e) {
		//if (!tooltipVisible) {
			var x, y;
			if (e.layerX || e.layerX == 0) { // for firefox
				x = e.layerX;
				y = e.layerY;
			}
			for (i=0;i<noteLinks.length;i++) {
				if (x >= noteLinks[i].xPos && x <= (noteLinks[i].xPos + iconBorder)
				    && y >= noteLinks[i].yPos && y <= (noteLinks[i].yPos + iconBorder)) {
					document.body.style.cursor = "pointer";
					isLink = noteLinks[i].index;
					linkDataset=noteLinks[i].dataSet;
					/*
					//test
					tooltipVisible=true;
					var tooltip=myChart.tooltip._model;
					tooltip.opacity=1;
					customTooltips(tooltip);
					myChart.update(); //this replaces the line below in function, as of now this is needed
					//end test
					*/
					break;
				} else {
					document.body.style.cursor = "";
					isLink = -1;
					linkDataset=-1;
				}
			}
		//}
	}

	//when the user clicks a note icon, this function is called to click the related line point which then shows the related tooltip
	function clickElement(chart, datasetIndex, index) {
		var node = chart.canvas;
		var rect = node.getBoundingClientRect();
		var el = chart.getDatasetMeta(datasetIndex).data[index];
		var point = resolveElementPoint(el);
		var event = new MouseEvent('touchmove', {
			clientX: rect.left+el._model.x-iconXOffset, //-iconX/YOffset is a fix to ensure the right point/tooltip is clicked for the note
			clientY: rect.top+el._model.y-iconYOffset,  //bug: this isn't working on mobile, right point/tooltip isn't being clicked
			cancelable: true,
			bubbles: true,
			view: window
		});
		tooltipVisible=true;
		node.dispatchEvent(event);
	}

	//get point data, used by clickElement procedure
	function resolveElementPoint(el) {
		var point = {x: 0, y: 0};
		if (el) {
			if (typeof el.getCenterPoint === 'function') {
				point = el.getCenterPoint();
			} else if (el.x !== undefined && el.y !== undefined) {
				point = el;
			} else if (el._model && el._model.x !== undefined && el._model.y !== undefined) {
				point = el._model;
			}
		}
		return point;
	}

	//delete option was clicked from tooltip, delete that point from DB
	function deletePoint() {
		var dateTime=new Date(pointDetails);
		dateTime=moment(dateTime).format("YYYY-MM-DD HH:mm:ss");
		$.ajax({
			url: 'delpoint.php',
			type:'POST',
			data: {'cookid': 101, 'time': dateTime}, //todo: send actual cookid
			success:function(data){
				if (data!='fail') {
					Chart.helpers.each(Chart.instances, function(instance){ //todo: is there a better way to get chart instance?
						var delIndex=instance.data.labels.indexOf(dateTime);
						instance.data.datasets[0].data.splice(delIndex,1);
						instance.data.datasets[1].data.splice(delIndex,1);
						instance.data.labels.splice(delIndex,1);
						instance.chart.update();
						document.getElementById("chartjs-tooltip").style.opacity="0";
						tooltipVisible=false;
					})
				} else { alert('failed');} //todo: what to do on failure?
			}
		});
	}

	//add note was clicked from tooltip, add note to DB
	function addNote() {
		var dateTime=new Date(pointDetails);
		var noteText=document.getElementById("addNoteText").value;
		dateTime=moment(dateTime).format("YYYY-MM-DD HH:mm:ss");
		$.ajax({
			url: 'addnote.php',
			type:'POST',
			data: {'cookid': 101, 'time': dateTime, 'note': noteText}, //todo: send actual cookid
			success:function(data){
				if (data!='fail') {
					Chart.helpers.each(Chart.instances, function(instance){ //todo: is there a better way to get chart instance?
						console.log("dataset: ",myChart.data.datasets);
						//myChart.data.datasets[0].data[2] = 50;
						//myChart.update();
						document.getElementById("chartjs-tooltip").style.opacity="0";
						tooltipVisible=false;
					})
				} else { alert('failed');} //todo: what to do on failure?
			}
		});
	}

	function pointClicked(e) {
		debugger;
		//CanvasMouseMove(e);
		console.log("clicked");
	//function pointClicked(e,item) { <--- change this back if using events: 'click' in chart options
		var item=myChart.tooltip._active; //bug: when clicking a different point with a point/tooltip currently active, this returns the current tooltip not the one at the new point
						 //or something like that, it looks like different objects are being passed under different conditions, maybe?
		var tooltip=myChart.tooltip._model;
		if (item && item[0]) {
			if (document.getElementById("chartjs-tooltip")) {
				document.getElementById("chartjs-tooltip").style.opacity="1";
				tooltipVisible=true;
				//customTooltips(item);
				customTooltips(tooltip);
			}
			myChart.update(); //this replaces the line below in function, as of now this is needed
			//updateConfigByMutating(); //no longer needed?
		} else {
			if (isLink!=-1) {
				clickElement(myChart,linkDataset,isLink);
				isLink=-1;
				linkDataset=-1;
			} else {
				if (document.getElementById("chartjs-tooltip")) {
					document.getElementById("chartjs-tooltip").style.opacity="0";
					tooltipVisible=false;
				}
			}
		}
	}

	/* no longer needed?
	*/
	function updateConfigByMutating() {
		//this is a hack to allow other point clicking when a tooltip is currently showing
		//without this, cliks on other points/tooltips don't display that tooltip
		Chart.helpers.each(Chart.instances, function(instance){
			instance.chart.update();
		})
	}
	/*
	*/

	var customTooltips=function(tooltip) {
		console.log(isLink);
		//don't show a new tooltip on hover if the the current tooltip showing has been clicked
		//this is to allow the user to 'sticky' the clicked tooltip so they can click the 'Delete?' link
		if (window.event.type!='click' && tooltipVisible==true && isLink==-1) { //what does isLink=-1 do again?
		//if (window.event.type!='click' && ((tooltipVisible==true && isLink==-1) || (tooltipVisible==false && isLink!=-1))) {
			console.log("suppressing tooltip click");
			return;
		}
		var tooltipEl=document.getElementById("chartjs-tooltip");

		if (!tooltipEl) {
			tooltipEl = document.createElement('div');
			tooltipEl.id = 'chartjs-tooltip';
			tooltipEl.innerHTML = '<table></table>';
			//this._chart.canvas.parentNode.appendChild(tooltipEl);
			myChart.canvas.parentNode.appendChild(tooltipEl);
		}

		document.getElementById("chartjs-tooltip").style.opacity="0.5";

		// Hide if no tooltip
		if (tooltip.opacity === 0) {
			tooltipEl.style.opacity = 0;
			return;
		}

		// Set caret Position
		tooltipEl.classList.remove('above', 'below', 'no-transform');
		if (tooltip.yAlign) {
			tooltipEl.classList.add(tooltip.yAlign);
		} else {
			tooltipEl.classList.add('no-transform');
		}

		function getBody(bodyItem) {
			return bodyItem.lines;
		}

		function getBody(bodyItem) {
			return bodyItem.lines;
		}

		// Set Text
		if (tooltip.body) {
			if  (tooltip.dataPoints[0]) {
				pointDetails=tooltip.dataPoints[0].label;
			}
			var titleLines = tooltip.title || [];
			var bodyLines = tooltip.body.map(getBody);
			var innerHtml = '<thead>';
			titleLines.forEach(function(title) {
				innerHtml += '<tr><th>' + title + '</th></tr>';
			});
			innerHtml += '</thead><tbody>';
			bodyLines.forEach(function(body, i) {
				var colors = tooltip.labelColors[i];
				var style = 'background:' + colors.backgroundColor;
				style += '; border-color:' + colors.borderColor;
				style += '; border-width: 2px';
				var span = '<span class="chartjs-tooltip-key" style="' + style + '"></span>';
				innerHtml += '<tr><td>' + span + body + '</td></tr>';
			});
			if (note[tooltip.dataPoints[0].index]!="") {
				var text=note[tooltip.dataPoints[0].index].replace(/(?![^\n]{1,50}$)([^\n]{1,50})\s/g, '$1<br />');
				//var text=note[tooltip.dataPoints[0].index];
				innerHtml+='<tr><td><span><strong>Note: </strong>'+text+'</span></td></tr>';
			} else {
				//innerHtml+='<tr><td align="center"><span><a href="#" onclick="addNote(); return false;">Add note?</a></span></td></tr>';
				innerHtml+='<tr><td align="center"><span><a href="#" onclick="document.getElementById(\'addNoteModal\').style.display=\'block\';">Add note?</a></span></td></tr>';
			}
			innerHtml+='<tr><td align="center"><span><a href="#" onclick="deletePoint(); return false;">Delete?</a></span></td></tr>';
			innerHtml += '</tbody>';
			var tableRoot = tooltipEl.querySelector('table');
			tableRoot.innerHTML = innerHtml;
		}

		//what was the point of these? this started working once I commented them out
		/*
		var positionY = this._chart.canvas.offsetTop;
		var positionX = this._chart.canvas.offsetLeft;
		*/
		var positionY = myChart.canvas.offsetTop;
		var positionX = myChart.canvas.offsetLeft;

		// Display, position, and set styles for font
		tooltipEl.style.opacity = 1;
		tooltipEl.style.left = positionX + tooltip.caretX + 100 + 'px';
		tooltipEl.style.top = positionY + tooltip.caretY + 'px';
		tooltipEl.style.fontFamily = tooltip._bodyFontFamily;
		tooltipEl.style.fontSize = tooltip.bodyFontSize + 'px';
		tooltipEl.style.fontStyle = tooltip._bodyFontStyle;
		tooltipEl.style.padding = tooltip.yPadding + 'px ' + tooltip.xPadding + 'px';

	};


	$(function() {
		$.ajax({
			url: "getdata.php",
			type: "POST",
			data: {'reqType': 'chartjs', 'cookid': 101}, //todo: get the real cook id
			async: false,
			dataType: "json",
			success: function(result){
				labels=result.map(function(e) {
					return e.time;
				}),
				food=result.map(function(e) {
					return e.food;
				}),
				note=result.map(function(e) {
					return e.note;
				}),
				pit=result.map(function(e) {
					return e.pit;
				});

				canvas=document.getElementById("myChart");
				canvas.addEventListener("mousemove",CanvasMouseMove, false, {passive: false});
				//canvas.addEventListener("touchmove",CanvasMouseMove, false);
				//canvas.addEventListener("click",customTooltips,true);
				canvas.addEventListener("click",pointClicked,false, {passive: false});
				//canvas.addEventListener("touchstart",pointClicked,false);
				var img=new Image();
				img.src="note.png";
				Chart.plugins.register({
					afterDatasetDraw: function(chart) {
						ctx=chart.ctx;
						theCtx=chart.ctx;
						noteLinks=[];
						$.each(note, function(index, value) {
							if (value!="") {
								var points=chart.getDatasetMeta(0).data;
								ctx.save();
								ctx.globalAlpha=0.15;
								ctx.drawImage(img,points[index]._model.x-iconSize,points[index]._model.y-iconSize,iconSize,iconSize);
								noteLinks.push({
									dataSet: 0,
									index: index,
									value: value,
									xPos: points[index]._model.x-iconSize,
									yPos: points[index]._model.y-iconSize
								});
								points=chart.getDatasetMeta(1).data;
								ctx.drawImage(img,points[index]._model.x-iconSize,points[index]._model.y-iconSize,iconSize,iconSize);
								noteLinks.push({
									dataSet: 1,
									index: index,
									value: value,
									xPos: points[index]._model.x-iconSize,
									yPos: points[index]._model.y-iconSize
								});
								ctx.restore();
							}
						});
					}
				});

				var color = Chart.helpers.color;
				var timeFormat = 'YYYY-MM-DD HH:mm:ss';
				var ctx = document.getElementById('myChart');
				 myChart = new Chart(ctx, {
					type: 'line',
					data: {
						labels: labels,
						datasets: [{
							label: 'Food',
							borderColor: color("#009688").alpha(0.75).rgbString(),
							backgroundColor: "#009688",
							borderWidth: 3,
							fill: false,
							pointRadius: 0,
							pointHoverRadius: 4,
							pointHitRadius: 5,
							data: food
						},{
							label: 'Pit',
							backgroundColor: "#2196F3",
							borderColor: color("#2196F3").alpha(0.75).rgbString(),
							borderWidth: 3,
							fill: false,
							pointRadius: 0,
							pointHoverRadius: 4,
							pointHitRadius: 5,
							data: pit
						}]
					},
					options: {
						//responsive: false,
						//events: ['mousemove'],
						onClick: pointClicked,
						tooltips: {
							enabled: false,
							mode: 'index',
							position: 'nearest',
							custom: customTooltips,
							tooltipXPadding: 100 //todo: fix tooltip going off right side when point is too close to margin
						},
						title: {
							text: ''
						},
						scales: {
							xAxes: [{
								type: 'time',
								time: {
									parser: timeFormat,
									tooltipFormat: 'll h:mm:ss A'
								},
								scaleLabel: {
									display: true,
									labelString: 'Time'
								}
							}],
							yAxes: [{
								scaleLabel: {
									display: true,
									labelString: 'Temp'
								},
								ticks: {beginAtZero: false}
							}]
						},
							}
				});
			}, //success function
		});//ajax call

		var interval=function(){
			$.ajax({
				url:'interval.php',
				type:'POST',
				success:function(data){
					if(data=='Start Cook') {
						$('#toggleCook').show();
						//noSleep.disable();
					} else {
						$('#toggleCook').hide();
					}
				}
			});
	        }
        	setInterval(interval,1000);

		getWeather=function() {
			setWeatherSpinner(true);
			//getCookie();
			$.ajax({
				url: "http://api.wunderground.com/api/"+apiKey+"/forecast/q/"+zipCode+".json",
				dataType: "jsonp",
				async: false,
				success: function(json) {
					if (json.forecast) {
						$("#wthrHigh").html("&nbsp;"+json.forecast.simpleforecast.forecastday[0].high.fahrenheit+"&deg;");
						$("#wthrLow").html("&nbsp;"+json.forecast.simpleforecast.forecastday[0].low.fahrenheit+"&deg;");
						$("#wthrPrecipitation").html("&nbsp;"+json.forecast.simpleforecast.forecastday[0].qpf_allday.in+"''");
						$("#wthrDesc0").html("<b>"+json.forecast.txt_forecast.forecastday[0].title+"</b>: &nbsp;"+
							json.forecast.txt_forecast.forecastday[0].fcttext);
						$("#wthrDesc1").html("<b>"+json.forecast.txt_forecast.forecastday[1].title+"</b>: &nbsp;"+
							json.forecast.txt_forecast.forecastday[1].fcttext);
						$("#wthrDesc2").html("<b>"+json.forecast.txt_forecast.forecastday[2].title+"</b>: &nbsp;"+
							json.forecast.txt_forecast.forecastday[2].fcttext);
						setWeatherSpinner(false);
					} else if (json.response) {
						$("[id^=wthr]").html("");
						$("#wLocation").html(" - Invalid API Key - <span onclick=\"document.getElementById('settingsModal').style.display='block'\" style=\"cursor:pointer\"><u>Click here to fix</u></span>");
					}
				},
				error: function(data) {
				}
			});

			$.ajax({
				url: "http://api.wunderground.com/api/"+apiKey+"/conditions/q/"+zipCode+".json",
				dataType: "jsonp",
				async: false,
				success: function(json) {
					if (json.current_observation) {
						$("#wthrNow").html("&nbsp;"+json.current_observation.temp_f+"&deg;");
						$("#wLocation").html(" - "+json.current_observation.display_location.full);
						$("#wthrWind").html("&nbsp;"+json.current_observation.wind_string);
						$("#wthrFeelsLike").html("&nbsp;"+json.current_observation.feelslike_f+"&deg;");
						$("#wthrRelHumidity").html("&nbsp;"+json.current_observation.relative_humidity);
						setWeatherSpinner(false);
					} else if (json.response) {
						$("[id^=wthr]").html("")
						$("#wLocation").html(" - Invalid API Key - <span onclick=\"document.getElementById('settingsModal').style.display='block'\" style=\"cursor:pointer\"><u>Click here to fix</u></span>");
					}
				},
				error: function(data) {
				}
			});
		}//getWeather
		//getWeather();

		function setWeatherSpinner(state) {
			if (state) {
				$("#weatherIcon").removeClass("fa fa-sun");
				$("#weatherIcon").addClass("fa fa-spinner fa-spin");
			} else {
				$("#weatherIcon").removeClass("fa fa-spinner fa-spin");
				$("#weatherIcon").addClass("fa fa-sun");
			}
		}

		$("#submitSettingsForm").click(function(event) {
			event.preventDefault(); //kills submit button default event processing
			$.post("newindex.php",$("settingsForm").serialize());
		});

		//click outside closes modal
		var modal=document.getElementById("settingsModal");

		window.onclick=function(event) {
			if (event.target==modal) {
				closeSettingsModal();
				//w3_close();
			}
		}

		//escape closes modal
		window.onkeyup=function(event) {
			if (event.keyCode==27) {
				closeSettingsModal();
			}
		}

		/* example of dynamically adding rows to table
		var newRecentCook="<tr>\n  <td><i class='material-icons w3-large'>whatshot</i></td>\n"+
			"  <td>It's a new cook!</td>\n  <td><i>now</i></td>\n  </tr>\n";
		$("#recentCooks tbody").append(newRecentCook);
		*/
	}); //jquery loaded
    </script>
  </head>
  <body class="w3-light-grey">
    <img id=>
    <!-- Top container -->
    <div id="top_container" class="w3-bar w3-top w3-black w3-large" style="z-index:4">
      <button class="w3-bar-item w3-button w3-hide-large w3-hover-none w3-hover-text-light-grey" onclick="w3_open();"><span class="w3-xlarge"><i class="fa fa-bars"></i> &nbsp;Menu</span></button>
      <button id="toggleCook" class="w3-bar-item w3-button w3-hide-large w3-hover-none w3-hover-text-light-grey w3-right" onclick="document.getElementById('newCookModal').style.display='block';"><span class="w3-xlarge"><i class="fa fa-thermometer-half"></i> &nbsp;New Cook</span></button>
    </div>

    <!-- Sidebar/menu -->
    <nav class="w3-sidebar w3-collapse w3-white w3-animate-left" style="z-index:3;width:300px;" id="mySidebar">
      <div id="sidebar_header" class="w3-container w3-bottombar w3-border-light-gray" style="padding-top:1px;padding-left:18px">
        <h4><b><i class="fa fa-fire"></i>&nbsp; BBQPi</b></h4>
      </div>
      <div class="w3-bar-block" style="padding-top:8px">
        <a href="#" class="w3-bar-item w3-button w3-padding" onclick="document.getElementById('newCookModal').style.display='block';"><i class="fa fa-thermometer-half fa-fw"></i>&nbsp; New Cook</a>
        <a href="/" id="getWeather" class="w3-bar-item w3-button w3-padding"><i class="fa fa-chart-pie fa-fw"></i>&nbsp; Dashboard</a>
        <a href="#" class="w3-bar-item w3-button w3-padding"><i class="fa fa-utensils fa-fw"></i>&nbsp; Cooks</a>
        <a href="#" class="w3-bar-item w3-button w3-padding"><i class="fa fa-bell fa-fw"></i>&nbsp; Alerts</a>
        <a href="#" class="w3-bar-item w3-button w3-padding"><i class="material-icons fa-fw" style="font-size:15px">whatshot</i>&nbsp; Smokers</a>
        <a class="w3-bar-item w3-button w3-padding" onclick="document.getElementById('settingsModal').style.display='block'"><i class="fa fa-cog fa-fw"></i>&nbsp; Settings</a>
        <a class="w3-bar-item w3-button w3-padding" onclick="document.getElementById('wifiModal').style.display='block'"><i class="fa fa-cog fa-fw"></i>&nbsp; BBQPi Wifi Settings</a>
      </div>
    </nav>

    <!-- Settings modal -->
    <div id="settingsModal" class="w3-modal">
      <div class="w3-modal-content w3-large" style="margin-top:50px" tabindex="-1">
        <header class="w3-container w3-black">
          <span onclick="closeSettingsModal();" class="w3-btn w3-right"
                style="padding-top:3px;padding-bottom:3px;padding-left:8px;padding-right:8px">&#10006;</span>
          <h2>Settings</h2>
        </header>
        <div class="w3-container w3-padding">
          <h3>Forecast Settings</h3>
          <form id="forecastSettings" class="w3-container">
            <input class="w3-input w3-border" type="text" id="zipCode" name="zipCode">
            <label>Forecast Zip Code</label><br><br>
            <input class="w3-input w3-border" type="text" id="apiKey" name="apiKey">
            <label>Weather Underground API Key (<a href="https://www.wunderground.com/weather/api/d/pricing.html"
                                                         target="blank">Need one?</a>)</label><br><br>
            <button class="w3-btn w3-black w3-right" id="submitForecastSettings"
                    onclick="setCookie(document.getElementById('forecastSettings'));
                    document.getElementById('settingsModal').style.display='none';
                    getWeather();w3_close();">Submit</button>
          </form>
        </div>
        <div class="w3-container w3-padding">
          <h3>Add Wifi Settings to BBQPi</h3>
          <form id="newWifiSettings" class="w3-container">
            <input class="w3-input w3-border" type="text" id="newWifiSsid" name="newWifiSsid"
             autocorrect="off" autocapitalize="none">
            <label>SSID</label><br><br>
            <input class="w3-input w3-border" type="password" id="newWifiPassword"
             name="newWifiPassword" autocorrect="off" autocapitalize="none">
            <label>Password</label><span class="fa fa-eye w3-right w3-xlarge" style="cursor:pointer;"
                                    onclick="showMaskPassword();" id="showPass"></span><br><br>
            <button class="w3-btn w3-black w3-right" id="submitNewWifiSettings" onclick="return addWifi();">Submit</button>
          </form>
        </div>
      </div>
    </div>

    <!-- bbqpi wifi modal -->
    <div id="wifiModal" class="w3-modal">
      <div class="w3-hide-large" style="margin-top:100px"></div>
      <div class="w3-modal-content w3-large" style="margin-top:-50px" tabindex="-1">
        <header class="w3-container w3-black">
          <span onclick="document.getElementById('wifiModal').style.display='none'" class="w3-btn w3-right"
                style="padding-top:3px;padding-bottom:3px;padding-left:8px;padding-right:8px">&#10006;</span>
          <h2>BBQPi Wifi Settings</h2>
        </header>
        <div class="w3-container w3-padding">
          <h3>Recent Networks</h3>
          <?php
            include_once('db.php');
            include_once('functions.php');
            $db=Database::getInstance();
            $pdo=$db->getConnection();
            $query="select * from networks order by last desc,signal;";
            $results=Database::select($query,$pdo);
          ?>
          <table id="wifiNetworks" class="w3-table w3-striped w3-white">
          <?php
          if ($result!==false) {
            $i=0;
            foreach ($results as $row) {
              $i++;
              $sig=abs($row["signal"]);
              if ($sig>=0 && $sig<67) {
                $icon="sig_4.png";
              } else if ($sig>=67 && $sig<70) {
                $icon="sig_3.png";
              } else if ($sig>=70 && $sig<80) {
                $icon="sig_2.png";
              } else if ($sig>=80 && $sig<90) {
                $icon="sig_1.png";
              } else {
                $icon="sig_0.png";
              }
              if ($row["secure"]==1) {
               $icon="s".$icon;
              } else {
               $icon="u".$icon;
              }
              echo "            <tr>\n";
              echo "              <td><img src='./img/".$icon."'></img></td>\n";
              echo "              <td>".$row['ssid']."</td>\n";
              echo "              <td>".$row['signal']."</td>\n";
              //echo "              <td>".date('F jS @ h:i:s a',strtotime($row['last']))."</td>\n";
              echo "              <td><span title='".date('F jS @ h:i:s a',strtotime($row['last']))."'>".getRelativeTime(strtotime($row['last']))."</span></td>\n";
              echo "              <td><button class='w3-button w3-round w3-small w3-green' value='".$row['ssid']."' onclick='alert(this.value);'>Join</button></td>\n";
              echo "            </tr>\n";
            }
          }
          ?>
          </table>
        </div>
        <div class="w3-container w3-padding">
          <h3>Add Wifi Network</h3>
          <form id="existingWifiSettings" class="w3-container">
            <input class="w3-input w3-border" type="text" id="existingWifiSsid" name="existingWifiSsid"
             autocorrect="off" autocapitalize="none">
            <label>SSID</label><br><br>
            <input class="w3-input w3-border" type="password" id="existingWifiPassword"
             name="existingWifiPassword" autocorrect="off" autocapitalize="none">
            <label>Password</label><br><span class="fa fa-eye w3-left w3-xlarge" style="cursor:pointer;"
                                    onclick="showMaskPassword();" id="showPass"></span>
            <button class="w3-button w3-green w3-right" id="submitExistingWifiSettings" onclick="return addWifi();">Add</button>
          </form>
        </div>
      </div>
    </div>

    <!-- new cook modal -->
    <div id="newCookModal" class="w3-modal">
      <div class="w3-hide-large" style="margin-top:100px"></div>
      <div class="w3-modal-content w3-large" style="margin-top:-50px" tabindex="-1">
        <header class="w3-container w3-black">
          <span onclick="document.getElementById('newCookModal').style.display='none'" class="w3-btn w3-right"
                style="padding-top:3px;padding-bottom:3px;padding-left:8px;padding-right:8px">&#10006;</span>
          <h2>New Cook</h2>
        </header>
        <div class="w3-container w3-padding">
          <h3>Cook Stuff</h3>
          <table class="w3-table w3-striped w3-white">
          </table>
        </div>
        <div class="w3-container w3-padding">
        </div>
      </div>
    </div>

    <!-- add note modal -->
    <div id="addNoteModal" class="w3-modal">
      <div class="w3-hide-large" style="margin-top:100px"></div>
      <div class="w3-modal-content w3-large" style="margin-top:-50px" tabindex="-1">
        <header class="w3-container w3-black">
          <span onclick="document.getElementById('addNoteModal').style.display='none'" class="w3-btn w3-right"
                style="padding-top:3px;padding-bottom:3px;padding-left:8px;padding-right:8px">&#10006;</span>
          <h2>Add a Note</h2>
        </header>
	<form id="addNoteForm" class="w3-container">
          <input class="w3-input w3-border" type="text" id="addNoteText" name="addNoteText">
          <label>Note</label><br><br>
          <button class="w3-button w3-green w3-right" id="addNoteSubmit" onclick="return addNote();">Add</button>
        </form>
        </div>
        <div class="w3-container w3-padding">
        </div>
      </div>
    </div>

    <!-- Overlay effect when opening sidebar on small screens -->
    <div class="w3-overlay w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>

    <!-- !PAGE CONTENT! -->
    <div id="page_content" class="w3-main" style="margin-left:300px;margin-top:43px;">

      <!-- Header -->
      <header id="page_content_header" class="w3-container" style="padding-top:22px">
        <h4><b><i class="fa fa-chart-pie"></i><span class="w3-hide-large"> BBQPi</span> Dashboard</b></h4>
      </header>

      <!-- Forecast card -->
      <div class="w3-container w3-margin-bottom">
        <div class="w3-row w3-card-4 w3-orange">
          <span onclick="this.parentElement.style.display='none'" class="w3-btn w3-right"
                style="padding-top:3px;padding-bottom:3px;padding-left:8px;padding-right:8px">&#10006;</span>
          <div class="w3-padding w3-xlarge" style="padding-bottom:0px !important">
            <i id="weatherIcon" class="fa fa-sun"></i> Forecast <span id="wLocation"></span>
          </div>
          <div class="w3-third w3-padding" style="padding-top:4px !important">
            <div><b>Now:</b> <span id="wthrNow"></span></div>
            <div><b>Feels Like:</b> <span id="wthrFeelsLike"></span></div>
            <div><b>High:</b> <span id="wthrHigh"></span></div>
            <div><b>Low:</b> <span id="wthrLow"></span></div>
            <div><b>Precipitation:</b> <span id="wthrPrecipitation"></span></div>
            <div><b>Relative Humidity:</b> <span id="wthrRelHumidity"></span></div>
            <div><b>Wind:</b> <span id="wthrWind"></span></div>
          </div>
          <div id="weatherLoading" class="w3-rest w3-padding" style="padding-top:4px !important">
            <div id="wthrDesc0" class=""></div><br>
            <div id="wthrDesc1" class=""></div><br>
            <div id="wthrDesc2" class=""></div><br>
          </div>
        </div>
      </div>

      <!-- graph card -->
      <div class="w3-container w3-margin-bottom">
       <div id="chartHolder" class="w3-row w3-card-4 w3-light-grey">
        <canvas id="myChart" width="1800" height="500"></canvas>
       </div>
      </div>

      <!-- Stats cards -->
      <div class="w3-row-padding w3-margin-bottom">
        <div class="w3-quarter">
          <div class="w3-container w3-purple w3-padding-16">
            <div class="w3-left"><i class="fa fa-utensils w3-xxxlarge"></i></div>
            <div class="w3-right">
              <h3>23</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Cooks</h4>
          </div>
        </div>
        <div class="w3-quarter">
          <div class="w3-container w3-blue w3-padding-16">
            <div class="w3-left"><i class="material-icons w3-xxxlarge" style="width:39px">assignments</i></div>
            <div class="w3-right">
              <h3>337</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Readings</h4>
          </div>
        </div>
        <div class="w3-quarter">
          <div class="w3-container w3-teal w3-padding-16">
            <div class="w3-left"><i class="material-icons w3-xxxlarge">whatshot</i></div>
            <div class="w3-right">
              <h3>2</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Smokers</h4>
          </div>
        </div>
        <div class="w3-quarter">
          <div class="w3-container w3-orange w3-text-white w3-padding-16">
            <div class="w3-left"><i class="material-icons w3-xxxlarge" style="width:39px">check box</i></div>
            <div class="w3-right">
              <h3>12</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Notes</h4>
          </div>
        </div>
      </div>

      <!-- Recent cooks panel -->
      <?php
        include_once('db.php');
        $db=Database::getInstance();
        $pdo=$db->getConnection();
        $query="select cooks.id,cooks.end,meat.icon from cooks left join meat on cooks.id=meat.cookid order by cooks.end desc limit 20";
        $results=Database::select($query,$pdo);
      ?>
      <div class="w3-panel">
        <div class="w3-row-padding" style="margin:0 -16px">
          <h5>Recent Cooks</h5>
          <table id="recentCooks" class="w3-table w3-striped w3-white">
          <?php
          if ($result!==false) {
            $i=0;
            foreach ($results as $row) {
              $i++;
              echo "            <tr>\n";
              echo "              <td><img src='".$row['icon']."' height=18 width=22></img></td>\n";
              echo "              <td>".$row['end']."</td>\n";
              echo "              <td>ok</td>\n";
              echo "            </tr>\n";
            }
          }
          ?>
            <!--
            <tr>
              <td><i class="fa fa-comment w3-text-red w3-large"></i></td>
              <td>New comments.</td>
              <td><i>25 mins</i></td>
            </tr>
            -->
          </table>
        </div>
      </div>

      <hr>
      <div class="w3-container">
        <h5>General Stats</h5>
        <p>New Visitors</p>
        <div class="w3-grey">
          <div class="w3-container w3-center w3-padding w3-green" style="width:25%">+25%</div>
        </div>

        <p>New Users</p>
        <div class="w3-grey">
          <div class="w3-container w3-center w3-padding w3-orange" style="width:50%">50%</div>
        </div>

        <p>Bounce Rate</p>
        <div class="w3-grey">
          <div class="w3-container w3-center w3-padding w3-red" style="width:75%">75%</div>
        </div>
      </div>
      <hr>

      <div class="w3-container">
        <h5>Countries</h5>
        <table class="w3-table w3-striped w3-bordered w3-border w3-hoverable w3-white">
          <tr>
            <td>United States</td>
            <td>65%</td>
          </tr>
          <tr>
            <td>UK</td>
            <td>15.7%</td>
          </tr>
          <tr>
            <td>Russia</td>
            <td>5.6%</td>
          </tr>
          <tr>
            <td>Spain</td>
            <td>2.1%</td>
          </tr>
          <tr>
            <td>India</td>
            <td>1.9%</td>
          </tr>
          <tr>
            <td>France</td>
            <td>1.5%</td>
          </tr>
        </table><br>
        <button class="w3-button w3-dark-grey">More Countries &nbsp;<i class="fa fa-arrow-right"></i></button>
      </div>
      <hr>
      <div class="w3-container">
        <h5>Recent Users</h5>
        <ul class="w3-ul w3-card-4 w3-white">
          <li class="w3-padding-16">
            <img src="" class="w3-left w3-circle w3-margin-right" style="width:35px">
            <span class="w3-xlarge">Mike</span><br>
          </li>
          <li class="w3-padding-16">
            <img src="" class="w3-left w3-circle w3-margin-right" style="width:35px">
            <span class="w3-xlarge">Jill</span><br>
          </li>
          <li class="w3-padding-16">
            <img src="" class="w3-left w3-circle w3-margin-right" style="width:35px">
            <span class="w3-xlarge">Jane</span><br>
          </li>
        </ul>
      </div>
      <hr>

      <div class="w3-container">
        <h5>Recent Comments</h5>
        <div class="w3-row">
          <div class="w3-col m2 text-center">
            <img class="w3-circle" src="" style="width:96px;height:96px">
          </div>
          <div class="w3-col m10 w3-container">
            <h4>John <span class="w3-opacity w3-medium">Sep 29, 2014, 9:12 PM</span></h4>
            <p>Keep up the GREAT work! I am cheering for you!! Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p><br>
          </div>
        </div>

        <div class="w3-row">
          <div class="w3-col m2 text-center">
            <img class="w3-circle" src="" style="width:96px;height:96px">
          </div>
          <div class="w3-col m10 w3-container">
            <h4>Bo <span class="w3-opacity w3-medium">Sep 28, 2014, 10:15 PM</span></h4>
            <p>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p><br>
          </div>
        </div>
      </div>
      <br>
      <div class="w3-container w3-dark-grey w3-padding-32">
        <div class="w3-row">
          <div class="w3-container w3-third">
            <h5 class="w3-bottombar w3-border-green">Demographic</h5>
            <p>Language</p>
            <p>Country</p>
            <p>City</p>
          </div>
          <div class="w3-container w3-third">
            <h5 class="w3-bottombar w3-border-red">System</h5>
            <p>Browser</p>
            <p>OS</p>
            <p>More</p>
          </div>
          <div class="w3-container w3-third">
            <h5 class="w3-bottombar w3-border-orange">Target</h5>
            <p>Users</p>
            <p>Active</p>
            <p>Geo</p>
            <p>Interests</p>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <footer class="w3-container w3-padding-16 w3-light-grey">
        <h4>FOOTER</h4>
        <p>Powered by <a href="https://www.w3schools.com/w3css/default.asp" target="_blank">w3.css</a></p>
      </footer>

      <!-- End page content -->
    </div>

    <script>
      // Get the Sidebar
      var mySidebar = document.getElementById("mySidebar");

      // Get the DIV with overlay effect
      var overlayBg = document.getElementById("myOverlay");

      // Toggle between showing and hiding the sidebar, and add overlay effect
      function w3_open() {
        if (mySidebar.style.display === 'block') {
          mySidebar.style.display = 'none';
          overlayBg.style.display = "none";
        } else {
          mySidebar.style.display = 'block';
          overlayBg.style.display = "block";
        }
      }

      // Close the sidebar with the close button
      function w3_close() {
        mySidebar.style.display = "none";
        overlayBg.style.display = "none";
      }
    </script>
  </body>
</html>
