
<!--
var mOvrClass='';
function mOvr(src,clrOver) {
 if (clrOver == undefined) {var clrOver='mOvrDL'};
 mOvrClass = src.className;
 src.className = mOvrClass + ' ' + clrOver;
}
function mOut(src) {
 src.className=mOvrClass;
}

overdiv="0";
function popLayer(a){
if (navigator.family == "gecko") {pad="0"; bord="1 bordercolor=black";}
else {pad="1"; bord="0";}
desc = "<table cellspacing=0 cellpadding="+pad+" border="+bord+"  bgcolor=#FBF4C4><tr><td>\n"
        +"<table cellspacing=0 cellpadding=10 border=0 width=100%><tr><td bgcolor=#C1CADE><center><font size=-1>\n"
        +a
        +"\n</td></tr></table>\n"
        +"</td></tr></table>";
if(navigator.family =="nn4") {
        document.object1.document.write(desc);
        document.object1.document.close();
        document.object1.left=x+15;
        document.object1.top=y-5;
        }
else if(navigator.family =="ie4"){
        object1.innerHTML=desc;
        object1.style.pixelLeft=x+15;
        object1.style.pixelTop=y-5;
        }
else if(navigator.family =="gecko"){
        document.getElementById("object1").innerHTML=desc;
        document.getElementById("object1").style.left=x+15;
        document.getElementById("object1").style.top=y-5;
        }
}

function hideLayer(){
if (overdiv == "0") {
        if(navigator.family =="nn4") {eval(document.object1.top="-500");}
        else if(navigator.family =="ie4"){object1.innerHTML="";}
        else if(navigator.family =="gecko") {document.getElementById("object1").style.top="-500";}
        }
}

var isNav = (navigator.appName.indexOf("Netscape") !=-1);
function handlerMM(e){

x = (isNav) ? e.pageX : event.clientX + document.body.scrollLeft;
y = (isNav) ? e.pageY : event.clientY + document.body.scrollTop;

}
if (isNav){document.captureEvents(Event.MOUSEMOVE);}
document.onmousemove = handlerMM;
//-->
