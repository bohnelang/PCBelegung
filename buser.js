function fetch_seats(){
	var scol = ["white","red","#EFEFEF","#8FFF8F","#CFCFCF", "#CFFFCF","#FF0080"];

	if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            var xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            var xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var result=this.responseText;
		var obj=JSON.parse(result);
            for (var prop in obj) {
			var val = obj[prop];
			if(document.getElementById("buser"+val.ip)) { 
				document.getElementById("buser"+val.ip).style.cssText = "background-color:"+ scol[val.css];  
			}
		}
            }
        };
        xmlhttp.open("GET","https://www.umm.uni-heidelberg.de/bibl/pcbelegung/buserpcs.json" + "?nocache=" + (new Date()).getTime() );
        xmlhttp.send();
}


function add_ids_css(){
	var 	cssd = "width: 20px;height: 20px;border-radius: 50%;-moz-border-radius: 50%;-webkit-border-radius: 50%;background-color: white";

	var 	css = '',
    		head = document.head || document.getElementsByTagName('head')[0],
    		style = document.createElement('style');

	if( document.getElementById("buserstyle")){
		cssd = document.getElementById("buserstyle").style.cssText ;
	}

	for(var i=1;i<255;i++) css += '#buser'+i+' {'+cssd+'}\n';


	style.type = 'text/css';
	if (style.styleSheet){
  		style.styleSheet.cssText = css;
	} else {
  		style.appendChild(document.createTextNode(css));
	}

	head.appendChild(style);
}


function init(){
	document.onreadystatechange = function () {
  		if (document.readyState == "complete") {
			add_ids_css();
			fetch_seats();
			setInterval(function(){fetch_seats(); }, 5000);
  		}
	}
}


init();
