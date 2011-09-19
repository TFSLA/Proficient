<?php /* STYLE/CLASSIC $Id: header_hhrr.php,v 1.1 2009-05-19 21:15:50 pkerestezachi Exp $ */
$dialog = dPgetParam( $_GET, 'dialog', 0 );
$suppressLogo = dPgetParam( $_GET, "suppressLogo", 0 );
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$autorefresh = dPgetParam( $_GET, 'autorefresh', 0 );
require_once( $AppUI->getModuleClass( "admin" ) );
$usr = new CUser();
if ( !$usr->load( $delegator_id ) )
{
	$AppUI->setMsg( "User" );
	$AppUI->setMsg( "Invalid id", UI_MSG_ERROR, true );
	$AppUI->redirect();
}
$sufixlang="";
if($AppUI->user_locale=="es") $sufixlang="_es";
?>
<BASE href="<?php echo $AppUI->getConfig("base_url");?>/"> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta name="Description" content="Classic Style" />
	<meta name="Version" content="<?php echo @$AppUI->getConfig( 'version' );?>" />
        <meta http-equiv="Content-Type" content="text/html;charset=<?php echo isset( $locale_char_set ) ? $locale_char_set : 'UTF-8';?>" />
<?php 
	$session_life = ini_get("session.gc_maxlifetime");
 	if ($autorefresh>0 && $session_life > 0){
		$refresh_every = $session_life/2;	?>  
     	<meta http-equiv="refresh" content="<?php echo $refresh_every ?>" />
<?php }?>  
      	<title><?php echo @$AppUI->getConfig( 'page_title' );?></title>
	<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle;?>/main.css" media="all" />
	<style type="text/css" media="all">@import "./style/<?php echo $uistyle;?>/main.css";</style>

<script language="JavaScript1.2"><!--

// Script Source: CodeLifter.com
// Copyright 2003
// Do not remove this header

var isIE=document.all;
var isNN=!document.all&&document.getElementById;
var isN4=document.layers;
var isHot=false;
var topDog;
var whichDog;
var contentPopup;
var hotDog;
var nowX=0;
var nowY=0;
var offsetx=0;
var offsety=0;
var ddEnabled=true;



function ddInit(e){
  whichDog=isIE ? document.all.theLayer : document.getElementById("theLayer");
  if (whichDog){
	  topDog=isIE ? "BODY" : "HTML";
	  contentPopup=isIE ? document.all.contentPopup : document.getElementById("contentPopup");
	  hotDog=isIE ? event.srcElement : e.target;  
	  if (typeof(hotDog.id)=="undefined"  && !event.srcElement && !e.target ) return;
	  while (hotDog && hotDog.id!="titleBar" && hotDog.tagName!=topDog){
		hotDog=isIE ? hotDog.parentElement : hotDog.parentNode;
	  }  
	  if (hotDog)
		  if (hotDog.id=="titleBar"){
			offsetx=isIE ? event.clientX : e.clientX;
			offsety=isIE ? event.clientY : e.clientY;
			nowX=parseInt(whichDog.style.left);
			nowY=parseInt(whichDog.style.top);
			nowX=isNaN(nowX) ? parseInt(whichDog.offsetLeft) : nowX;
			nowY=isNaN(nowY) ? parseInt(whichDog.offsetTop) : nowY;
			nowX=isNaN(nowX) ? 0 : nowX;
			nowY=isNaN(nowY) ? 0 : nowY;
			ddEnabled=true;
			document.onmousemove=dd;
		  }
  }
}

function dd(e){
  if (!ddEnabled) return;
  whichDog.style.left=isIE ? nowX+event.clientX-offsetx : nowX+e.clientX-offsetx; 
  whichDog.style.top=isIE ? nowY+event.clientY-offsety : nowY+e.clientY-offsety;
  return false;  
}

function ddN4(whatDog){
  if (!isN4) return;
  N4=eval(whatDog);
  N4.captureEvents(Event.MOUSEDOWN|Event.MOUSEUP);
  N4.onmousedown=function(e){
    N4.captureEvents(Event.MOUSEMOVE);
    N4x=e.x;
    N4y=e.y;
  }
  N4.onmousemove=function(e){
    if (isHot){
      N4.moveBy(e.x-N4x,e.y-N4y);
      return false;
    }
  }
  N4.onmouseup=function(){
    N4.releaseEvents(Event.MOUSEMOVE);
  }
}

function hideMe(){
  //if (isIE||isNN) whichDog.style.visibility="hidden";
 // else if (isN4) document.theLayer.visibility="hide";
}

function showMe(){
  //if (isIE||isNN) whichDog.style.visibility="visible";
 // else if (isN4) document.theLayer.visibility="show";
}

function onoffdisplay(){
	if (contentPopup.style.display==''){ 
		contentPopup.style.display='none';
		document.getElementById("imgbtn").src = "images/icons/down.gif";
	}else{
		contentPopup.style.display='';
		document.getElementById("imgbtn").src = "images/icons/up.gif";
	}
} 
document.onmousedown=ddInit;
document.onmouseup=Function("ddEnabled=false");

//--></script>
	<script language="JavaScript">
	function doBtn() {
		var oEl = event.srcElement;
		var doit = event.type;
	
		while (-1 == oEl.className.indexOf( "Btn" )) {
			oEl = oEl.parentElement;
			if (!oEl) {
				return;
			}
		}
		if (doit == "mouseover" || doit == "mouseup") {
			oEl.className = "clsBtnOn";
		} else if (doit == "mousedown") {
			oEl.className = "clsBtnDown";
		} else {
			oEl.className = "clsBtnOff";
		}
	}
	function tboff(){
		var oEl = event.srcElement;
		var doit = event.type;
		oEl.className = "topBtnOff";
	}
	</script>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}

function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

//-->
</script>
<script language="Javascript" src="./lib/jsLibs/functions.php" type="text/javascript"></script>
<script language='javascript'>

function alert1(x) { alert(acentos(x)) }

function confirm1(x) 
{
	if (confirm(acentos(x)) ){
		return true;
	}else{
		return false;
	}
	
}

function acentos(x) {
	// version 040623
	// Spanish - Español
	// Portuguese - Portugués - Português
	// Italian - Italiano
	// French - Francés - Français
	// Also accepts and converts single and double quotation marks, square and angle brackets
	// and miscelaneous symbols.
	// Also accepts and converts html entities for all the above.
//	if (navigator.appVersion.toLowerCase().indexOf("windows") != -1) {return x}
	x = x.replace(/¡/g,"\xA1");	x = x.replace(/&iexcl;/g,"\xA1")
	x = x.replace(/¿/g,"\xBF");	x = x.replace(/&iquest;/g,"\xBF")
	x = x.replace(/À/g,"\xC0");	x = x.replace(/&Agrave;/g,"\xC0")
	x = x.replace(/à/g,"\xE0");	x = x.replace(/&agrave;/g,"\xE0")
	x = x.replace(/Á/g,"\xC1");	x = x.replace(/&Aacute;/g,"\xC1")
	x = x.replace(/á/g,"\xE1");	x = x.replace(/&aacute;/g,"\xE1")
	x = x.replace(/Â/g,"\xC2");	x = x.replace(/&Acirc;/g,"\xC2")
	x = x.replace(/â/g,"\xE2");	x = x.replace(/&acirc;/g,"\xE2")
	x = x.replace(/Ã/g,"\xC3");	x = x.replace(/&Atilde;/g,"\xC3")
	x = x.replace(/ã/g,"\xE3");	x = x.replace(/&atilde;/g,"\xE3")
	x = x.replace(/Ä/g,"\xC4");	x = x.replace(/&Auml;/g,"\xC4")
	x = x.replace(/ä/g,"\xE4");	x = x.replace(/&auml;/g,"\xE4")
	x = x.replace(/Å/g,"\xC5");	x = x.replace(/&Aring;/g,"\xC5")
	x = x.replace(/å/g,"\xE5");	x = x.replace(/&aring;/g,"\xE5")
	x = x.replace(/Æ/g,"\xC6");	x = x.replace(/&AElig;/g,"\xC6")
	x = x.replace(/æ/g,"\xE6");	x = x.replace(/&aelig;/g,"\xE6")
	x = x.replace(/Ç/g,"\xC7");	x = x.replace(/&Ccedil;/g,"\xC7")
	x = x.replace(/ç/g,"\xE7");	x = x.replace(/&ccedil;/g,"\xE7")
	x = x.replace(/È/g,"\xC8");	x = x.replace(/&Egrave;/g,"\xC8")
	x = x.replace(/è/g,"\xE8");	x = x.replace(/&egrave;/g,"\xE8")
	x = x.replace(/É/g,"\xC9");	x = x.replace(/&Eacute;/g,"\xC9")
	x = x.replace(/é/g,"\xE9");	x = x.replace(/&eacute;/g,"\xE9")
	x = x.replace(/Ê/g,"\xCA");	x = x.replace(/&Ecirc;/g,"\xCA")
	x = x.replace(/ê/g,"\xEA");	x = x.replace(/&ecirc;/g,"\xEA")
	x = x.replace(/Ë/g,"\xCB");	x = x.replace(/&Euml;/g,"\xCB")
	x = x.replace(/ë/g,"\xEB");	x = x.replace(/&euml;/g,"\xEB")
	x = x.replace(/Ì/g,"\xCC");	x = x.replace(/&Igrave;/g,"\xCC")
	x = x.replace(/ì/g,"\xEC");	x = x.replace(/&igrave;/g,"\xEC")
	x = x.replace(/Í/g,"\xCD");	x = x.replace(/&Iacute;/g,"\xCD")
	x = x.replace(/í/g,"\xED");	x = x.replace(/&iacute;/g,"\xED")
	x = x.replace(/Î/g,"\xCE");	x = x.replace(/&Icirc;/g,"\xCE")
	x = x.replace(/î/g,"\xEE");	x = x.replace(/&icirc;/g,"\xEE")
	x = x.replace(/Ï/g,"\xCF");	x = x.replace(/&Iuml;/g,"\xCF")
	x = x.replace(/ï/g,"\xEF");	x = x.replace(/&iuml;/g,"\xEF")
	x = x.replace(/Ñ/g,"\xD1");	x = x.replace(/&Ntilde;/g,"\xD1")
	x = x.replace(/ñ/g,"\xF1");	x = x.replace(/&ntilde;/g,"\xF1")
	x = x.replace(/Ò/g,"\xD2");	x = x.replace(/&Ograve;/g,"\xD2")
	x = x.replace(/ò/g,"\xF2");	x = x.replace(/&ograve;/g,"\xF2")
	x = x.replace(/Ó/g,"\xD3");	x = x.replace(/&Oacute;/g,"\xD3")
	x = x.replace(/ó/g,"\xF3");	x = x.replace(/&oacute;/g,"\xF3")
	x = x.replace(/Ô/g,"\xD4");	x = x.replace(/&Ocirc;/g,"\xD4")
	x = x.replace(/ô/g,"\xF4");	x = x.replace(/&ocirc;/g,"\xF4")
	x = x.replace(/Õ/g,"\xD5");	x = x.replace(/&Otilde;/g,"\xD5")
	x = x.replace(/õ/g,"\xF5");	x = x.replace(/&otilde;/g,"\xF5")
	x = x.replace(/Ö/g,"\xD6");	x = x.replace(/&Ouml;/g,"\xD6")
	x = x.replace(/ö/g,"\xF6");	x = x.replace(/&ouml;/g,"\xF6")
	x = x.replace(/Ø/g,"\xD8");	x = x.replace(/&Oslash;/g,"\xD8")
	x = x.replace(/ø/g,"\xF8");	x = x.replace(/&oslash;/g,"\xF8")
	x = x.replace(/Ù/g,"\xD9");	x = x.replace(/&Ugrave;/g,"\xD9")
	x = x.replace(/ù/g,"\xF9");	x = x.replace(/&ugrave;/g,"\xF9")
	x = x.replace(/Ú/g,"\xDA");	x = x.replace(/&Uacute;/g,"\xDA")
	x = x.replace(/ú/g,"\xFA");	x = x.replace(/&uacute;/g,"\xFA")
	x = x.replace(/Û/g,"\xDB");	x = x.replace(/&Ucirc;/g,"\xDB")
	x = x.replace(/û/g,"\xFB");	x = x.replace(/&ucirc;/g,"\xFB")
	x = x.replace(/Ü/g,"\xDC");	x = x.replace(/&Uuml;/g,"\xDC")
	x = x.replace(/ü/g,"\xFC");	x = x.replace(/&uuml;/g,"\xFC")
	
	x = x.replace(/\"/g,"\x22")
	x = x.replace(/\'/g,"\x27")
	x = x.replace(/\</g,"\x3C")
	x = x.replace(/\>/g,"\x3E")
	x = x.replace(/\[/g,"\x5B")
	x = x.replace(/\]/g,"\x5D")

	x = x.replace(/¢/g,"\xA2");	x = x.replace(/&cent;/g,"\xA2") 
	x = x.replace(/£/g,"\xA3");	x = x.replace(/&pound;/g,"\xA3")
	x = x.replace(/©/g,"\xA9");	x = x.replace(/&copy;/g,"\xA9") 
	x = x.replace(/®/g,"\xAE");	x = x.replace(/&reg;/g,"\xAE") 
	x = x.replace(/ª/g,"\xAA");	x = x.replace(/&ordf;/g,"\xAA") 
	x = x.replace(/º/g,"\xBA");	x = x.replace(/&ordm;/g,"\xBA") 
	x = x.replace(/°/g,"\xB0");	x = x.replace(/&deg;/g,"\xB0") 
	x = x.replace(/±/g,"\xB1");	x = x.replace(/&plusmn;/g,"\xB1")
	x = x.replace(/×/g,"\xD7");	x = x.replace(/&times;/g,"\xD7") 
	
		
	return x
}

</script>

</head>
<?
if ( !$_GET["suppressLogo"] )
{
?>
<body link="#333333" vlink="#333333" onLoad="MM_preloadImages('images/buttons/silver-header-home<?=$sufixlang?>-2.jpg','images/buttons/silver-header-myinfo<?=$sufixlang?>-2.jpg','images/buttons/silver-header-print<?=$sufixlang?>-2.jpg','images/buttons/silver-header-help<?=$sufixlang?>-2.jpg','images/buttons/silver-header-signout<?=$sufixlang?>-2.jpg');">
<?
} else {
?>
<body link="#333333" vlink="#333333">
<?
}
?>
<div id="dhtmltooltip"></div>
<script language="Javascript" src="./lib/dhtmltooltip/dhtmltooltip.js" type="text/javascript"></script>
<table width="98%" align="center" cellpadding="0" cellspacing="0" border="0">

<?
if ( !$_GET["suppressLogo"] )
{
?>
<tr>
	<td width="100%" colspan="3">
	
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" background="images/silver-back_header.jpg">

  <tr>
    <td width="239"><a href="index.php"><img src="images/silver-header-logo.jpg" width="239" height="43" border="0"></a></td>
    <td><div align="center">
		<? if ( $AppUI->user_id == $delegator_id ) { ?> 
        <table border="0" cellspacing="3" cellpadding="0">
          <tr>
		  <? /*<form action="index.php?m=search" method="post"> 
            <td><div align="right">
                <input name="query" type="text" class="formularios" value="<?=$query?>">
              </div></td>
            <td> 
              <input name="image" type="image" src="images/silver-search-button.gif" align="bottom" width="59" height="16" border="0"></td>
		  </form>*/ ?>
            <th><?php 
            	echo $AppUI->_("Candidates Portal");
            ?>
              </th>		  
          </tr>
        </table>
        <? }else{ ?>&nbsp;<? } ?>
      </div></td>
    <td width="226"> 
      <table border="0" cellpadding="0" cellspacing="0" width="226">
        <!-- fwtable fwsrc="sistema nuevo.png" fwbase="botones.gif" fwstyle="Dreamweaver" fwdocid = "742308039" fwnested="0" -->
        <tr>
          <td><? if ($AppUI->user_id==$delegator_id) { ?><a href="hhrr/index.php?a=home" ><img src="images/buttons/silver-header-home<?=$sufixlang?>-1.jpg" alt="" name="home" width="50" height="43" border="0" id="home" onMouseOut="MM_swapImgRestore();" onMouseOver="MM_swapImage('home','','images/buttons/silver-header-home<?=$sufixlang?>-2.jpg',1);"></a><? } ?></td>
          <td><? if ($AppUI->user_id==$delegator_id) { ?><a href="javascript:window.print();" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('print','','images/buttons/silver-header-print<?=$sufixlang?>-2.jpg',1)"><img src="images/buttons/silver-header-print<?=$sufixlang?>-1.jpg" alt="" name="print" width="45" height="43" border="0" id="print"></a><? } ?></td>
          <?php /*<td><? if ($AppUI->user_id==$delegator_id) { ?><a href="javascript:window.open('?m=help&dialog=1&hid=', 'contexthelp', 'width=400, height=520, left=50, top=50, scrollbars=yes, resizable=yes')" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('help','','images/buttons/silver-header-help<?=$sufixlang?>-2.jpg',1)"><img src="images/buttons/silver-header-help<?=$sufixlang?>-1.jpg" alt="" name="help" width="40" height="43" border="0" id="help"></a><? } ?></td>*/ ?>
          <td><? if ($AppUI->user_id==$delegator_id) { ?><a href="hhrr/index.php?logout=-1" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('signout','','images/buttons/silver-header-signout<?=$sufixlang?>-2.jpg',1)"><? }else{ ?><a href="javascript:window.close();" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('signout','','images/buttons/silver-header-signout<?=$sufixlang?>-2.jpg',1)"><? } ?><img src="images/buttons/silver-header-signout<?=$sufixlang?>-1.jpg" alt="" name="signout" width="36" height="43" border="0" id="signout"></a></td>
          <td><img src="images/buttons/silver-header-end.jpg" alt="" name="end" width="15" height="43" border="0" id="end"></td>
        </tr>
      </table>
      </td>
  </tr>
</table>


	</td>
</tr>
<?
}
?>
<tr>
<?php if (!$dialog) 
{ 
	// left side navigation menu
?>	
	<td valign="top" style="    width: 140px;
								border-left: #9A9A9A solid 1px;
								border-right: #9A9A9A solid 1px;
								background-color: #DFDFDF;
								">


		<table width="100%" border="0" cellspacing="0" cellpadding="0">
	        <tr>
				<td width="140" colspan="3" >
					<table width="100%"  height="34px" border="0" cellspacing="1" cellpadding="4" bgcolor="#999999">
						<tr>
				 			<td class="nombreuser" bgcolor="#666666"><?php echo $AppUI->_("Welcome"); ?><br> 
								<strong>
	             				<?php echo $usr->user_first_name ." " . $usr->user_last_name; ?>
	             				</strong>
							</td>
						</tr>
					</table> 
					<table width="100%" border="0" cellspacing="1" cellpadding="4" bgcolor="#999999">						
						<tr>
				 			<td class="nombreuser" bgcolor="#666666">
				 			<?php 
				 			echo $AppUI->_("Online"); 

				 			
				 			echo ': <span id="online_time" name="online_time">&nbsp;</span>';
				 			?>
							</td>
						</tr>						
					</table>          
<script language="JavaScript">
<!-- 
var timeCrono; 
<?php 	
$ol_time = new CDate();		
$ol_time = logusergetsessiontime();
echo "
var hor = ".$ol_time->getHour().";
var min = ".$ol_time->getMinute().";
var seg = ".$ol_time->getSecond().";
";
?>
StartCrono();

function StartCrono() {
if (seg + 1 > 59) { 
min+= 1 ;
seg = 0;
}
if (min > 59) {
min = 0;
hor+= 1;
}

timeCrono= (hor < 10) ? "0" + hor : hor;
timeCrono+= ((min < 10) ? ":0" : ":") + min;
timeCrono+= ((seg < 10) ? ":0" : ":") + seg;
document.getElementById("online_time").innerHTML = timeCrono;
seg++;
setTimeout("StartCrono()",1000);
}
 //--> 
</script>					
	           </td>        
	        </tr>
<?php
if ( $AppUI->user_id == $delegator_id )
{

	//Menu propio	
	$nav = $AppUI->getMenuModules();
	//$s = '';
	$s = '
	        <tr>
				<td colspan="3" >
					
					<table border="0" cellspacing="0" cellpadding="0" 
					style=" background-image: url(images/common/back_grande.jpg);
							background-repeat: repeat-x;
							width: 100%;
	">';	
			$s .= '<tr>'
				.'	<td colspan="5"><img src="images/1x1.gif" height="1" width="135"></td>'
				.'</tr>';	
			$s .= '<tr>'
				.'	<td rowspan="500"><img src="images/1x1.gif" height="320" width="1"></td>'
				.'</tr>';		
			$s .= '<tr>'
				.'	<td colspan="4" valign="top">';
	foreach ($nav as $module) 
	{       
		    $moduledir = $module['mod_directory'];

			if ($moduledir=="myskills")
		    {
			 $moduledir = "editskills";
		    }

			$s1 = '<tr height="12px">'
				.'	<td><img src="images/1x1.gif" height="15" width="3"></td>'
				.'  <td><img src="images/silver-cuadradito.gif" width="4" height="9"></td>'
				.'	<td><img src="images/1x1.gif" height="15" width="3"></td>'				
				.'  <td nowrap=\"nowrap\"><a href="hhrr/index.php?a='.$moduledir.'" class="special">'.$AppUI->_($module['mod_ui_name']).'</a></td>'
				.'</tr>';
			$s .= "<table border=0 cellpadding=0 cellspacing=0>".$s1."</table>";

	}	
			$s1 = '<tr height="12px">'
				.'	<td><img src="images/1x1.gif" height="15" width="3"></td>'
				.'  <td><img src="images/silver-cuadradito.gif" width="4" height="9"></td>'
				.'	<td><img src="images/1x1.gif" height="15" width="3"></td>'				
				.'  <td nowrap=\"nowrap\"><a href="hhrr/index.php?logout=-1" class="special">'.$AppUI->_("hhrrLogout").'</a></td>'
				.'</tr>';
			$s .= "<table border=0 cellpadding=0 cellspacing=0>".$s1."</table>";	
	$s .= "			</td></tr>
					</table>        
	           </td>        
	        </tr>";
	/*
	$href = "?m=public&a=delegation_selector";
	$s .= '<tr>'
				.'	<td width="6">&nbsp;</td>'
				.'  <td width="10"><img src="images/silver-cuadradito.gif" width="4" height="9"></td>'
				.'  <td><a href="?m='.$href.'">'.$href.'</a></td>'
				.'</tr>';
	*/	
	echo $s;
			
}
else
{
	//Menu de un delegador, hay que mostrar solo los modulos delegados por esta persona
	/*$debugsql=1;
	echo "<p>Menu del delegado</p>";
	echo "<p>Soy $AppUI->user_id haciendo de cuenta que soy $user_id</p>";*/
	$usr->load( $AppUI->user_id );
	$modulos = $usr->getModulesDelegatedBy( $delegator_id );
	///*$debugsql=0;
	//echo "<p>Modulos disponibles: ";print_r( $modulos );echo "</p>";
	$s = "";
	$s = '
	        <tr>
				<td colspan="3" style=" 
								border-left: #9A9A9A solid 1px;
								border-right: #9A9A9A solid 1px;
								">
					
					<table border="0" cellspacing="0" cellpadding="0" 
					style=" background-image: url(images/common/back_grande.jpg);
							background-repeat: repeat-x;
							width: 100%;
	">';	
	foreach( $modulos as $module )
	{
		$s .= '<tr>'
				.'	<td><img src="images/1x1.gif" height="15" width="3"></td>'
				.'  <td><img src="images/silver-cuadradito.gif" width="4" height="9"></td>'
				.'	<td><img src="images/1x1.gif" height="15" width="3"></td>'	
				.'  <td><a href="?m='.$module['mod_directory'].'&delegator_id='.$delegator_id.'">'.$AppUI->_($module['mod_ui_name']).'</a></td>'
				.'</tr>';

	}
	$s .= "			</table> 
					      
	           </td>        
	        </tr>";	
	echo $s;
	
}
	?>
		</table>
	<img src="images/1x1.gif" height="1" width="140" />
	<br>
	</td>
	
	
	
<?php /*	
<td><!--img src="images/pixel.gif" width="5" height="1" border="0" alt=""></td><td><img src="images/pixel.gif" width="5" height="1" border="0" alt=""--></td>	
*/ ?>
<td valign="top" align="left" width="90%">	
<?php }
 // END DIALOG 
 else{
?>
<?php /*
<td colspan="2"><!--img src="images/pixel.gif" width="5" height="1" border="0" alt=""></td><td><img src="images/pixel.gif" width="5" height="1" border="0" alt=""--></td>
*/ ?>
<td colspan="2" valign="top" align="left" width="100%">

 <?php }?>

<!-- New Design 07092004 -->
<!--table width="100%" border="0" cellspacing="0" cellpadding="6">
        <tr>
          <td-->
<?php 
	echo $AppUI->getMsg();
?>
