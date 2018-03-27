<!DOCTYPE html>
<html>
  <head>
    <title>Maverick ET-732</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/solid.css" integrity="sha384-v2Tw72dyUXeU3y4aM2Y0tBJQkGfplr39mxZqlTBDUZAb9BGoC40+rdFCG0m10lXk" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/fontawesome.css" integrity="sha384-q3jl8XQu1OpdLgGFvNRnPdj5VIlCvgsDQTQB6owSOHWlAurxul7f+JpUOVdAiJ5P" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <script src="https://code.jquery.com/jquery-3.1.0.js"   integrity="sha256-slogkvB1K3VOkzAI8QITxV3VzpOnkeNVsKvtkYLMjfk="   crossorigin="anonymous"></script>
    <style>
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
    </style>
    <script>
      var getWeather;
      var zipCode, apiKey;

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
      getCookie();

      $(function() {

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
          gotWeather=false;
          $("#weatherIcon").removeClass("fa fa-sun");
          $("#weatherIcon").addClass("fa fa-spinner fa-spin");
          getCookie();
          $.ajax({
            url: "http://api.wunderground.com/api/"+apiKey+"/forecast/q/"+zipCode+".json",
            dataType: "jsonp",
            async: false,
            success: function(json) {
              console.log(json);
              if (json.forecast) {
                $("#wHigh").html("&nbsp;"+json.forecast.simpleforecast.forecastday[0].high.fahrenheit+"&deg;");
                $("#wLow").html("&nbsp;"+json.forecast.simpleforecast.forecastday[0].low.fahrenheit+"&deg;");
                $("#wPrecipitation").html("&nbsp;"+json.forecast.simpleforecast.forecastday[0].qpf_allday.in+"''");
                $("#wDesc0").html("<b>"+json.forecast.txt_forecast.forecastday[0].title+"</b>: &nbsp;"+
                                        json.forecast.txt_forecast.forecastday[0].fcttext);
                $("#wDesc1").html("<b>"+json.forecast.txt_forecast.forecastday[1].title+"</b>: &nbsp;"+
                                        json.forecast.txt_forecast.forecastday[1].fcttext);
                $("#wDesc2").html("<b>"+json.forecast.txt_forecast.forecastday[2].title+"</b>: &nbsp;"+
                                        json.forecast.txt_forecast.forecastday[2].fcttext);
                $("#weatherIcon").removeClass("fa fa-spinner fa-spin");
                $("#weatherIcon").addClass("fa fa-sun");
              } else if (json.response) {
                $("#wLocation").html(" - Invalid API Key - <a href=\"#\" onclick=\"document.getElementById('settingsModal').style.display='block'\"><u>Click here to fix</u></a>");
              }
            },
            error: function(data) {
              console.log(data);
            }
          });

          $.ajax({
            url: "http://api.wunderground.com/api/"+apiKey+"/conditions/q/"+zipCode+".json",
            dataType: "jsonp",
            async: false,
            success: function(json) {
              console.log(json);
              if (json.current_observation) {
                $("#wNow").html("&nbsp;"+json.current_observation.temp_f+"&deg;");
                $("#wLocation").html(" - "+json.current_observation.display_location.full);
                $("#wWind").html("&nbsp;"+json.current_observation.wind_string);
                $("#wFeelsLike").html("&nbsp;"+json.current_observation.feelslike_f+"&deg;");
                $("#wRelHumidity").html("&nbsp;"+json.current_observation.relative_humidity);
                $("#weatherIcon").removeClass("fa fa-spinner fa-spin");
                $("#weatherIcon").addClass("fa fa-sun");
              } else if (json.response) {
                $("#wLocation").html(" - Invalid API Key -  <a href=\"#\" onclick=\"document.getElementById('settingsModal').style.display='block'\"><u>Click here to fix</u></a>");
              }
            },
            error: function(data) {
              console.log(data);
            }
          });
        }//getWeather
        getWeather();

        $("#submitSettingsForm").click(function(event){
          event.preventDefault(); //kills submit button default event processing
          $.post("newindex.php",$("settingsForm").serialize());
        });
      }); //jquery loaded
    </script>
  </head>
  <body class="w3-light-grey">

    <!-- Top container -->
    <div id="top_container" class="w3-bar w3-top w3-black w3-large" style="z-index:4">
      <button class="w3-bar-item w3-button w3-hide-large w3-hover-none w3-hover-text-light-grey" onclick="w3_open();"><span class="w3-xlarge"><i class="fa fa-bars"></i> &nbsp;Menu</span></button>
      <button id="toggleCook" class="w3-bar-item w3-button w3-hide-large w3-hover-none w3-hover-text-light-grey w3-right" onclick="w3_open();"><span class="w3-xlarge"><i class="fa fa-thermometer-half"></i> &nbsp;New Cook</span></button>
    </div>

    <!-- Sidebar/menu -->
    <nav class="w3-sidebar w3-collapse w3-white w3-animate-left" style="z-index:3;width:300px;" id="mySidebar">
      <div id="sidebar_header" class="w3-container w3-bottombar w3-border-light-gray" style="padding-top:1px;padding-left:18px">
        <h4><b><i class="fa fa-fire"></i>&nbsp; Maverick ET-732</b></h4>
      </div>
      <div class="w3-bar-block" style="padding-top:8px">
        <a href="#" id="getWeather" class="w3-bar-item w3-button w3-padding"><i class="fa fa-chart-pie fa-fw"></i>&nbsp; Dashboard</a>
        <a href="#" class="w3-bar-item w3-button w3-padding"><i class="fa fa-utensils fa-fw"></i>&nbsp; Cooks</a>
        <a href="#" class="w3-bar-item w3-button w3-padding"><i class="fa fa-bell fa-fw"></i>&nbsp; Alerts</a>
        <a href="#" class="w3-bar-item w3-button w3-padding"><i class="material-icons fa-fw" style="font-size:15px">whatshot</i>&nbsp; Smokers</a>
        <a class="w3-bar-item w3-button w3-padding" onclick="document.getElementById('settingsModal').style.display='block'"><i class="fa fa-cog fa-fw"></i>&nbsp; Settings</a><br><br>
      </div>
    </nav>

    <!-- Settings modal -->
    <div id="settingsModal" class="w3-modal">
      <div class="w3-modal-content w3-large" style="margin-top:50px">
        <header class="w3-container w3-black">
          <span onclick="document.getElementById('settingsModal').style.display='none'" class="w3-btn w3-right"
                style="padding-top:3px;padding-bottom:3px;padding-left:8px;padding-right:8px">&#10006;</span>
          <h2>Settings</h2>
        </header>
        <div class="w3-container w3-padding">
          <form id="settingsForm" class="w3-container">
            <input class="w3-input w3-border" type="text" id="zipCode" name="zipCode">
            <label>Forecast Zip Code</label><br><br>
            <input class="w3-input w3-border" type="text" id="apiKey" name="apiKey">
            <label>Weather Underground API Key</label><br><br>
            <button class="w3-btn w3-black w3-right" id="submitSettingsForm"
                    onclick="setCookie(document.getElementById('settingsForm'));
                    document.getElementById('settingsModal').style.display='none';
                    getWeather();w3_open();">Submit</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Overlay effect when opening sidebar on small screens -->
    <div class="w3-overlay w3-hide-large w3-animate-opacity" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>

    <!-- !PAGE CONTENT! -->
    <div id="page_content" class="w3-main" style="margin-left:300px;margin-top:43px;">

      <!-- Header -->
      <header id="page_content_header" class="w3-container" style="padding-top:22px">
        <h4><b><i class="fa fa-chart-pie"></i><span class="w3-hide-large"> Maverick ET-732</span> Dashboard</b></h4>
      </header>

      <!-- Forecast card -->
      <div class="w3-container w3-margin-bottom">
        <div class="w3-row w3-card-4 w3-red">
          <span onclick="this.parentElement.style.display='none'" class="w3-btn w3-right"
                style="padding-top:3px;padding-bottom:3px;padding-left:8px;padding-right:8px">&#10006;</span>
          <div class="w3-padding w3-xlarge" style="padding-bottom:0px !important">
            <i id="weatherIcon" class="fa fa-sun"></i> Forecast <span id="wLocation"></span>
          </div>
          <div class="w3-third w3-padding" style="padding-top:4px !important">
            <div><b>Now:</b> <span id="wNow"></span></div>
            <div><b>Feels Like:</b> <span id="wFeelsLike"></span></div>
            <div><b>High:</b> <span id="wHigh"></span></div>
            <div><b>Low:</b> <span id="wLow"></span></div>
            <div><b>Precipitation:</b> <span id="wPrecipitation"></span></div>
            <div><b>Relative Humidity:</b> <span id="wRelHumidity"></span></div>
            <div><b>Wind:</b> <span id="wWind"></span></div>
          </div>
          <div id="weatherLoading" class="w3-rest w3-padding" style="padding-top:4px !important">
            <div id="wDesc0" class=""></div><br>
            <div id="wDesc1" class=""></div><br>
            <div id="wDesc2" class=""></div><br>
          </div>
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
            <div class="w3-left"><i class="material-icons w3-xxxlarge">assignments</i></div>
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
            <div class="w3-left"><i class="material-icons w3-xxxlarge">check box</i></div>
            <div class="w3-right">
              <h3>12</h3>
            </div>
            <div class="w3-clear"></div>
            <h4>Notes</h4>
          </div>
        </div>
      </div>

      <div class="w3-panel w3-hide-small">
         <div class="w3-row-padding" style="margin:0 -16px">
          <div class="w3-third w3-container">
            <h5>Weather</h5>
          </div>
          <div class="w3-twothird">
            <h5>Feeds</h5>
            <table class="w3-table w3-striped w3-white">
              <tr>
                <td><i class="fa fa-user w3-text-blue w3-large"></i></td>
                <td>New record, over 90 views.</td>
                <td><i>10 mins</i></td>
              </tr>
              <tr>
                <td><i class="fa fa-bell w3-text-red w3-large"></i></td>
                <td>Database error.</td>
                <td><i>15 mins</i></td>
              </tr>
              <tr>
                <td><i class="fa fa-users w3-text-yellow w3-large"></i></td>
                <td>New record, over 40 users.</td>
                <td><i>17 mins</i></td>
              </tr>
              <tr>
                <td><i class="fa fa-comment w3-text-red w3-large"></i></td>
                <td>New comments.</td>
                <td><i>25 mins</i></td>
              </tr>
              <tr>
                <td><i class="fa fa-bookmark w3-text-blue w3-large"></i></td>
                <td>Check transactions.</td>
                <td><i>28 mins</i></td>
              </tr>
              <tr>
                <td><i class="fa fa-laptop w3-text-red w3-large"></i></td>
                <td>CPU overload.</td>
                <td><i>35 mins</i></td>
              </tr>
              <tr>
                <td><i class="fa fa-share-alt w3-text-green w3-large"></i></td>
                <td>New shares.</td>
                <td><i>39 mins</i></td>
              </tr>
            </table>
          </div>
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
