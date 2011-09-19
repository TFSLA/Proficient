<?php /* STYLE/CLASSIC $Id: header.php,v 1.1 2009-05-19 21:15:50 pkerestezachi Exp $ */
$dialog = dPgetParam( $_GET, 'dialog', 0 );
$suppressLogo = dPgetParam( $_GET, "suppressLogo", 0 );
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$autorefresh = dPgetParam( $_GET, 'autorefresh', 0 );
$query = dPgetParam( $_POST, 'query', "" );

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
      	<link rel="shortcut icon" href="./style/<?php echo $uistyle;?>/images/favicon.ico" type="image/ico" />
	<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle;?>/main.css" media="all" />
	<style type="text/css" media="all">@import "./style/<?php echo $uistyle;?>/main.css";</style>

    <? if ($m == "projects" && $project_id !=""){ ?>

	<LINK HREF="<?=$AppUI->cfg['base_url']?>/rss_recursos.php?p=<?=$project_id ?>" TITLE="RSS" TYPE="application/rss+xml" rel="alternate">
	<? } ?>

	<? if ($m == "articles"){

		if ($id == "" || $id == "0")
		{
		  $sec_rss = "psa";
		}else{
		  $sec_rss = $id;
		}
	?>
		<LINK HREF="<?=$AppUI->cfg['base_url']?>/rss.php?s=<?=$sec_rss ?>" TITLE="RSS" TYPE="application/rss+xml" rel="alternate">
	<? } ?>



	<script type="text/javascript" src="./includes/javascript/tooltip.js"></script>
<?php echo $AppUI->getJsHeader();?>	
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
<script language="Javascript" src="./lib/dhtmltooltip/dhtmltooltip.js" type="text/javascript"></script>
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
<body link="#333333" vlink="#333333" class="mainpage" background="images/background-intranet.jpg" onload="<?PHP
		$header_onload = $AppUI->getJsEvent("onload", true );
		if ($header_onload != NULL){
			echo $header_onload;
		}
?>">
<div id="dhtmltooltip"></div>
<table width="100" border="0" cellpadding="0" cellspacing="0" class="form-fields">
  <tr>  	
    <td><a href="index.php"><img src="images/header-top.jpg" width="780" height="32" border=0></a></td>    
  </tr>
  <tr>
    <td><table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/header-bckg.gif">
	<form action="index.php?m=search" method="post">
        <tr> 
          <td width="39%" valign="top"><img src="images/header-bottom-logo.jpg" width="307" height="29"></td>
          <td width="42%" rowspan="2"><? if ( $AppUI->user_id == $delegator_id ) { ?><table width="100" border="0" cellpadding="0" cellspacing="0" background="images/search-bckg.gif">
              <tr> 
                <td colspan="2"><img src="images/search-title<?=$sufixlang?>.gif" width="231" height="15"></td>
              </tr>
              <tr> 
                <td valign="top"><img src="images/search-decorative-left.gif" width="11" height="24" align="absmiddle">
                  <input name="query" type="text" class="form-fields" value="<?=$query?>">
                </td>
                <td align="right" valign="bottom"><input type="image" src="images/search-button.gif" width="38" height="24" align="absmiddle"></td>
              </tr>
            </table><? } else { ?>&nbsp;<? } ?></td>
          <td width="19%" rowspan="2" align="right" nowrap="nowrap"><? if ($AppUI->user_id==$delegator_id) { ?><a href="index.php?<?=$AppUI->user_prefs['HOMEPAGE']; ?>"><img src="images/buttons/header-home<?=$sufixlang?>-1.gif" name="home" width="48" height="47" border="0" id="home" onMouseOver="MM_swapImage('home','','images/buttons/header-home<?=$sufixlang?>-2.gif',1)" onMouseOut="MM_swapImgRestore()"></a><? } ?><? if ($AppUI->user_id == $delegator_id ) { ?><a href="./index.php?m=system&a=addeditpref&user_id=<?php echo $AppUI->user_id;?>"><img src="images/buttons/header-myinfo<?=$sufixlang?>-1.gif" name="myinfo" width="48" height="47" border="0" id="myinfo" onMouseOver="MM_swapImage('myinfo','','images/buttons/header-myinfo<?=$sufixlang?>-2.gif',1)" onMouseOut="MM_swapImgRestore()"></a><? } ?><a href="javascript:window.print();"><img src="images/buttons/header-print<?=$sufixlang?>-1.gif" name="print" width="48" height="47" border="0" id="print" onMouseOver="MM_swapImage('print','','images/buttons/header-print<?=$sufixlang?>-2.gif',1)" onMouseOut="MM_swapImgRestore()"></a><a href="#" onClick="javascript:window.open('?m=help&dialog=1&hid=', 'contexthelp', 'width=400, height=520, left=50, top=50, scrollbars=yes, resizable=yes')"><img src="images/buttons/header-help<?=$sufixlang?>-1.gif" name="help" width="48" height="47" border="0" id="help" onMouseOver="MM_swapImage('help','','images/buttons/header-help<?=$sufixlang?>-2.gif',1)" onMouseOut="MM_swapImgRestore()"></a><? if ( $AppUI->user_id == $delegator_id ) { ?><a href="index.php?logout=-1"><? } else { ?><a href="#" onclick="window.close();"><? } ?><img src="images/buttons/header-signout<?=$sufixlang?>-1.gif" name="signout" width="48" height="47" border="0" id="signout" onMouseOver="MM_swapImage('signout','','images/buttons/header-signout<?=$sufixlang?>-2.gif',1)" onMouseOut="MM_swapImgRestore()"></a></td>
        </tr>
        <tr> 
          <td>&nbsp;</td>
        </tr>
	</form>
      </table></td>
  </tr>
</table>
  <?
  }
  ?>
<br>
<? /* ?>
<table class="nav" width="100%" cellpadding="0" cellspacing="2">
<tr>
	<td nowrap width="33%"></td>
<?php if (!$dialog) { ?>
	<td nowrap width="34%"></td>
	<td nowrap width="33%" align="right">
	<table cellpadding="1" cellspacing="1" width="150">
	<tr>
		<td class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();"><a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $AppUI->user_id;?>" onmouseover="doBtn();"><?php echo $AppUI->_('My Info');?></a></td>
		<td class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();"><a href="./index.php?logout=-1" onmouseover="doBtn();"><?php echo $AppUI->_('Logout');?></a></td>
		<td class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();"><?php echo dPcontextHelp( 'Help' );?></td>
	</tr>
	</table>
	</td>

	<form name="frm_new" method=GET action="./index.php">
<?php
	echo '<td>';
	$newItem = array( ""=>'- New Item -' );
	$newItem["companies"] = "Company";
	$newItem["contacts"] = "Contact";
	$newItem["calendar"] = "Event";
	$newItem["files"] = "File";
	$newItem["projects"] = "Project";

	echo arraySelect( $newItem, 'm', 'style="font-size:10px" onChange="f=document.frm_new;mod=f.m.options[f.m.selectedIndex].value;if(mod) f.submit();"', '', true);

	echo '</td><input type="hidden" name="a" value="addedit" />';

//build URI string
	if (isset( $company_id )) {
		echo '<input type="hidden" name="company_id" value="'.$company_id.'" />';
	}
	if (isset( $task_id )) {
		echo '<input type="hidden" name="task_parent" value="'.$task_id.'" />';
	}
	if (isset( $file_id )) {
		echo '<input type="hidden" name="file_id" value="'.$file_id.'" />';
	}
?>
	</form>
<?php } // END DIALOG BLOCK ?>
</tr>
</table>
<?*/?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td width="138" bgcolor="#ffffff" valign="top">
<?php if (!$dialog) 
{ 
	// left side navigation menu
?>
		<table width="100" border="0" cellspacing="0" cellpadding="5">
	        <tr>
	           <td class="raya-abajo">
	             <img src="images/titles/welcome<?=$sufixlang?>.gif"  width="115" height="19"></td>
            </tr>
            <tr>
               <td class="raya-abajo">
                 <table width="100%" border="0" cellspacing="0" cellpadding="5">
                 	<tr>                 
                 		<td bgcolor="#1F30A4" class="txt">
                 			<font color="#FFFFFF"><strong>
	             			<?php echo $usr->user_first_name ." " . $usr->user_last_name; ?>
	             			</strong>
	             			<?php //echo ' - [<span id="online_time" name="online_time">&nbsp;</span>]'; ?>
	             			</font>
	             		</td>
	               </tr>    
                 	<tr>                 
                 		<td bgcolor="#1F30A4" class="txt">
                 			<font color="#FFFFFF">
	             			<?php 
				 			echo $AppUI->_("Online"); 

				 			
				 			echo ': <span id="online_time" name="online_time">&nbsp;</span>';
				 			?>
	             			</font>
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
	             </table>          
	           </td>        
	        </tr>
	        
	        
<?php
if ( $AppUI->user_id == $delegator_id )
{
	//Menu propio	
	$nav = $AppUI->getMenuModules();
	$s = '';
	foreach ($nav as $module) 
	{
		if (!getDenyRead( $module['mod_directory'] )) 
		{
			$s .= '<tr>'
				.'  <td class="raya-abajo">'
				.'  <a href="?m='.$module['mod_directory'].'">'
				.'    <img src="images/buttons/'.$module['mod_directory'].$sufixlang.'-1.gif" name="'.$module['mod_directory'].'" width="160" height="13" border="0" id="'.$module['mod_directory'].'" onMouseOver="MM_swapImage(\''.$module['mod_directory'].'\',\'\',\'images/buttons/'.$module['mod_directory'].$sufixlang.'-2.gif\',1)" onMouseOut="MM_swapImgRestore()">'
				.'  </a>'
				.'  </td>'
				.'</tr>';
		}
	}	
	$href = "?m=public&a=delegation_selector";
	$s .= '<tr>'
		.'  <td class="raya-abajo">'
		.'  <a href="'.$href.'">'
		.'    <img src="images/buttons/delegates'.$sufixlang.'-1.gif" name="delegates" width="160" height="13" border="0" id="delegates" onMouseOver="MM_swapImage(\'delegates\',\'\',\'images/buttons/delegates'.$sufixlang.'-2.gif\',1)" onMouseOut="MM_swapImgRestore()">'
		.'  </a>'
		.'  </td>'
		.'</tr>';
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
	foreach( $modulos as $module )
	{
		$s .= '<tr>'
				.'  <td class="raya-abajo">'
				.'  <a href="?m='.$module['mod_directory'].'&delegator_id='.$delegator_id.'">'
				.'    <img src="images/buttons/'.$module['mod_directory'].$sufixlang.'-1.gif" name="'.$module['mod_directory'].'" width="160" height="13" border="0" id="'.$module['mod_directory'].'" onMouseOver="MM_swapImage(\''.$module['mod_directory'].'\',\'\',\'images/buttons/'.$module['mod_directory'].$sufixlang.'-2.gif\',1)" onMouseOut="MM_swapImgRestore()">'
				.'  </a>'
				.'  </td>'
				.'</tr>';		
	}
	echo $s;
}
	?>
		</table>			
<?php }
 // END DIALOG ?>
	</td>
<td class="rayado-derecha"><img src="images/pixel.gif" width="5" height="1" border="0" alt=""></td><td><img src="images/pixel.gif" width="5" height="1" border="0" alt=""></td>
<td valign="top" align="left" width="100%">
<?php 
	echo $AppUI->getMsg();
?>
