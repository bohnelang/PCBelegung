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
                        val = obj[prop];

                        if(document.getElementById( val.name )) {
                                document.getElementById(val.name).style.cssText = "background-color:"+ scol[val.css];
                        }
                }
            }
        };
        xmlhttp.open("GET","https://www.umm.uni-heidelberg.de/bibl/pcbelegung/buserpcs.json" + "?nocache=" + (new Date()).getTime() );
        xmlhttp.send();
}


function init(){
        document.onreadystatechange = function () {
                if (document.readyState == "complete") {
                        fetch_seats();
                        setInterval(function(){fetch_seats(); }, 5000);
                }
        }
}


init();
