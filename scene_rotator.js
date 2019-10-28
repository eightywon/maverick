var xjs = require('xjs');
var Scene=xjs.Scene;
  
var newScene;
var oldScene;

var scoreScreenSelect=document.getElementById("scoreScreenSelect");
var inGameScreenSelect=document.getElementById("inGameScreenSelect");
var loadingScreenSelect=document.getElementById("loadingScreenSelect");
var menuScreenSelect=document.getElementById("menuScreenSelect");
var brbScreenSelect=document.getElementById("brbScreenSelect");

xjs.ready().then(function() {
	var Extension=xjs.Extension.getInstance();
	xjs.ExtensionWindow.getInstance().resize(1000, 500);

	console.log("loading config");
	var config = Extension.loadConfig();
	Extension.loadConfig().then(function(config) {
		console.log("got config:");
		var sceneCount=0;
		Scene.getSceneCount().then(function(count) {
			console.log("setting dropdowns");
			for(let i = 1; i < count + 1; i++) {
				Scene.getById(i).then(function(scene) {
					scene.getName().then(function(name) {
						var option1=document.createElement("option");
						option1.text=name;
						option1.value=i;
						scoreScreenSelect.add(option1);
						if (i==config.scoreScreenSelect) {
							option1.selected=true;
						}
						
						var option2=document.createElement("option");
						option2.text=name;
						option2.value=i;
						inGameScreenSelect.add(option2);
						if (i==config.inGameScreenSelect) {
							option2.selected=true;
						}
		  
						var option3=document.createElement("option");
						option3.text=name;
						option3.value=i;
						loadingScreenSelect.add(option3);
						if (i==config.loadingScreenSelect) {
							option3.selected=true;
						}
		  
						var option4=document.createElement("option");
						option4.text=name;
						option4.value=i;
						menuScreenSelect.add(option4);
						if (i==config.menuScreenSelect) {
							option4.selected=true;
						}
		  
						var option5=document.createElement("option");
						option5.text=name;
						option5.value=i;
						brbScreenSelect.add(option5);
						if (i==config.brbScreenSelect) {
							option5.selected=true;
						}
					});
				});
			}
			sceneCount = count;
		});
	});

    this.saveConfigTimeout=setTimeout(this.saveConfig.bind(this), 3000);
	
	var theLoop=function(){
	  $.ajax({
		url: "http://localhost:6119/ui",
		dataType: "json",
		async: false,
		success: function(json) {
			getCurrentScene(json);
			//console.log("new: "+(newScene-1)+", oldScene: "+oldScene);
			if ((newScene-1)!=oldScene) {
				//console.log("getting active scene");
				Scene.getActiveScene()
				 .then(function(theScene) {
					//console.log("got active scene");
					theScene.getSceneIndex()
					 .then(function(theIndex) {
						 //console.log("got scene index");
						 oldScene=theIndex;
					 });
				 });
				//console.log("setting scene to "+newScene);
				Scene.setActiveScene(newScene);
			}
		},
		error: function(data) {
		  alert('fail '+data);
		}
	  });
	}
	setInterval(theLoop,1000);
})

document.getElementById("saveConfig").onclick = function(){saveConfig()};

function saveConfig() {
	var Extension=xjs.Extension.getInstance();
	var _this=this;
	var config= {
		'scoreScreenSelect': scoreScreenSelect.value,
		'inGameScreenSelect': inGameScreenSelect.value,
		'loadingScreenSelect': loadingScreenSelect.value,
		'menuScreenSelect': menuScreenSelect.value,
		'brbScreenSelect': brbScreenSelect.value
	};
	Extension.saveConfig(config);
	_this.saveConfigTimeout=setTimeout(_this.saveConfig.bind(_this), 10*1000);	
};

function getCurrentScene(theJson) {
	console.log(theJson.activeScreens);
	if (theJson.activeScreens.indexOf("ScreenScore/ScreenScore")>=0) {
		newScene=parseInt($("#scoreScreenSelect").prop('selectedIndex'))+1;
	} else if (theJson.activeScreens.indexOf("ScreenLoading/ScreenLoading")>=0) {
		newScene=parseInt($("#loadingScreenSelect").prop('selectedIndex'))+1;
	} else if (theJson.activeScreens.indexOf("ScreenCoopCampaign/ScreenCoopCampaign")>=0) {
		newScene=parseInt($("#brbScreenSelect").prop('selectedIndex'))+1;
	} else if (theJson.activeScreens.length==0) {
		newScene=parseInt($("#inGameScreenSelect").prop('selectedIndex'))+1;
	} else {
		newScene=parseInt($("#menuScreenSelect").prop('selectedIndex'))+1;
	}
}