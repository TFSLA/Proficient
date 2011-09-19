<?php /* STYLE/CLASSIC $Id: header.php,v 1.5 2009-06-23 17:16:41 pkerestezachi Exp $ */
$dialog = dPgetParam( $_GET, 'dialog', 0 );
$suppressLogo = dPgetParam( $_GET, "suppressLogo", 0 );
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$autorefresh = dPgetParam( $_GET, 'autorefresh', 0 );
$query = dPgetParam( $_POST, 'query', "" );

if(isset($HTTP_COOKIE_VARS['TWITTER_VIEW']))
$showTwitter = $HTTP_COOKIE_VARS['TWITTER_VIEW'];
else
	$showTwitter = 1;

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
      	<title><?php echo @$AppUI->getConfig( 'page_title' )." :: ".$AppUI->_($AppUI->getModuleName($m));?></title>
	<link rel="shortcut icon" href="./style/<?php echo $uistyle;?>/images/favicon.ico" type="image/ico" />
	<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle;?>/slidingdoors.css" media="all" />
	<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle;?>/main.css" media="all" />
	<style type="text/css" media="all">@import "./style/<?php echo $uistyle;?>/main.css";</style>

<? if ($m == "projects"){ ?>

	<LINK HREF="<?=$AppUI->cfg['base_url']?>/rss_recursos.php?p=<? echo ($project_id !="" ? $project_id : 'psa'); ?>" TITLE="RSS" TYPE="application/rss+xml" rel="alternate">
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
	
	function popUp(URL)
	{
		day = new Date();
		id = day.getTime();
		eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0 ,scrollbars=yes, location=0, statusbar=0, menubar=0, resizable=1, width=900, height=500');");
	}	
	
	function getFavoritesItems()
	{
		xajax_showFavoritesItems();
	}
	
	function showFavoritesItems()
	{
		if (isIE)
		{
			favoriteItem_x = posX();
			favoriteItem_y = posY();
		}
		else
		{
    	    favoriteItem_x = netX;
    	    favoriteItem_y = netY;
		}
    	
		tooltipLinkXY(document.getElementById('favorites_hidden').value, null, 'favorites', favoriteItem_x + 150, favoriteItem_y + 150);
	}
	
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

function alert1(x) {
	alert(acentos(x))
}

function confirm1(x) {
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

<script language='javascript'>
	function ManagePlugIn(user_id, hiddenField, plugin_name)
	{
		var enabledPlugin;

		if(hiddenField.value == '1')
			enabledPlugin = 0;
		else
			enabledPlugin = 1;

		var url =  '<?php echo($AppUI->getConfig( 'base_url' )) ?>';


	<?php if(isset( $_SERVER["HTTPS"] ) && ( strtolower( $_SERVER["HTTPS"] )) != 'off'){ ?>
		url = url.replace('http://', 'https://');
	<?php } ?>

		document.getElementById('fmConfigPlugins').src = url+'/index.php?m=public&a=config_plugins.bak&suppressHeaders=1&user_id='+user_id+'&pluginname='+plugin_name+'&pluginenabled='+enabledPlugin;

		hiddenField.value = enabledPlugin;
	}

	function SwapImages(enabledPlugin, imageName, image1, image2)
	{
		if(enabledPlugin=="1")
			MM_swapImage(imageName,'',image1,1);
		else
			MM_swapImage(imageName,'',image2,1);
	}
</script>

</head>
<?
if (!$suppressLogo){
?>
<body link="#333333" vlink="#333333" onLoad="MM_preloadImages('images/buttons/silver-header-home<?=$sufixlang?>-2.jpg','images/buttons/silver-header-myinfo<?=$sufixlang?>-2.jpg','images/buttons/silver-header-print<?=$sufixlang?>-2.jpg','images/buttons/silver-header-help<?=$sufixlang?>-2.jpg','images/buttons/silver-header-signout<?=$sufixlang?>-2.jpg');<?PHP
		$header_onload = $AppUI->getJsEvent("onload", true );
		if ($header_onload != NULL){
			echo $header_onload;
		}
?>">
<?
} else {
?>
<body link="#333333" vlink="#333333">
<?
}
?>
<div id="dhtmltooltip"></div>
<script language="Javascript" src="./lib/dhtmltooltip/dhtmltooltip.js" type="text/javascript"></script>
<script type="text/javascript" src="./includes/javascript/tooltip.js"></script>
<table width="98%" align="center" cellpadding="0" cellspacing="0" border="0">

<?
if (!$suppressLogo){
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
		  <form action="index.php?m=search" method="post">
            <td><div align="right">
                <input name="query" type="text" class="formularios" value="<?=$query?>">
              </div></td>
            <td>
              <input name="image" type="submit" class="button" value="<?php echo $AppUI->_('search') ?>">
	    </td>
		  </form>
          </tr>
        </table>
        <? }else{ ?>&nbsp;<? } ?>
      </div></td>
    <td width="226">
    	<iframe name="fmConfigPlugins" id="fmConfigPlugins" style="width:0px; height:0px; border: 0px"></iframe>
      <table border="0" cellpadding="0" cellspacing="0" width="226">
        <!-- fwtable fwsrc="sistema nuevo.png" fwbase="botones.gif" fwstyle="Dreamweaver" fwdocid = "742308039" fwnested="0" -->
        <?
        	$outlookEnabled = 0;
        	$googledesktopEnabled = 0;
			$sqlPlugins = "SELECT * FROM user_plugins WHERE plugin_user_id = $AppUI->user_id AND plugin_enabled";
			$resultPlugins = mysql_query($sqlPlugins);
			while ($rowPlugIn = mysql_fetch_array($resultPlugins, MYSQL_ASSOC))
			{
				switch($rowPlugIn["plugin_name"])
				{
					case "outlook":
						$outlookEnabled = 1;
						break;
					case "googledesktop":
						$googledesktopEnabled = 1;
						break;
				}
			}

			$urlImage =  $AppUI->getConfig('base_url');

			if(isset( $_SERVER["HTTPS"] ) && ( strtolower( $_SERVER["HTTPS"] )) != 'off')
				$urlImage = str_replace("http://","https://",$urlImage);
        ?>
        <tr>
			<input type="hidden" id="hiddenGoogleDesktop" value="<?=$googledesktopEnabled?>" />
			<input type="hidden" id="hiddenOutlook" value="<?=$outlookEnabled?>" />
		  <td><? if ($AppUI->user_id==$delegator_id) { ?><a id="hrefgoogledesktop" href="javascript: ManagePlugIn(<?=$AppUI->user_id?>, hiddenGoogleDesktop, 'googledesktop');" onMouseOut="MM_swapImgRestore()" onMouseOver="SwapImages(hiddenGoogleDesktop.value, 'googledesktop','<?=$urlImage?>/images/buttons/silver-header-google<?=$sufixlang?>-1.gif','<?=$urlImage?>/images/buttons/silver-header-google<?=$sufixlang?>-2.gif')"><img src="images/buttons/silver-header-google<?=$sufixlang?>.gif" alt="Google Desktop Plug-In" name="googledesktop" border="0" id="googledesktop"></a><? } ?></td>
		  <td>&nbsp;</td>
		  <td><? if ($AppUI->user_id==$delegator_id) { ?><a id="hrefoutlook" href="javascript: ManagePlugIn(<?=$AppUI->user_id?>, hiddenOutlook, 'outlook');" onMouseOut="MM_swapImgRestore()" onMouseOver="SwapImages(hiddenOutlook.value, 'outlook','<?=$urlImage?>/images/buttons/silver-header-outlook<?=$sufixlang?>-1.gif','<?=$urlImage?>/images/buttons/silver-header-outlook<?=$sufixlang?>-2.gif')"><img src="images/buttons/silver-header-outlook<?=$sufixlang?>.gif" alt="Outlook Plug-In" name="outlook" border="0" id="outlook"></a><? } ?></td>
          <td><? if ($AppUI->user_id==$delegator_id) { ?><a href="index.php?<?=$AppUI->user_prefs['HOMEPAGE']; ?>" ><img src="images/buttons/silver-header-home<?=$sufixlang?>-1.jpg" alt="" name="home" width="50" height="43" border="0" id="home" onMouseOut="MM_swapImgRestore();" onMouseOver="MM_swapImage('home','','images/buttons/silver-header-home<?=$sufixlang?>-2.jpg',1);"></a><? } ?></td>
          <td><? if ($AppUI->user_id==$delegator_id) { ?><a href="./index.php?m=system&a=addeditpref&user_id=<?php echo $AppUI->user_id;?>" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('myinfo','','images/buttons/silver-header-myinfo<?=$sufixlang?>-2.jpg',1)"><img src="images/buttons/silver-header-myinfo<?=$sufixlang?>-1.jpg" alt="" name="myinfo" width="40" height="43" border="0" id="myinfo"></a><? } ?></td>
          <td><? if ($AppUI->user_id==$delegator_id) { ?><a href="javascript:window.print();" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('print','','images/buttons/silver-header-print<?=$sufixlang?>-2.jpg',1)"><img src="images/buttons/silver-header-print<?=$sufixlang?>-1.jpg" alt="" name="print" width="45" height="43" border="0" id="print"></a><? } ?></td>
          <td><? if ($AppUI->user_id==$delegator_id) { ?><a href="javascript: void();" onclick="javascript:window.open('?m=help&suppressLogo=1&dialog=1&hid=', 'contexthelp', 'width=500, height=500, left=50, top=50, scrollbars=yes, resizable=yes')" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('help','','images/buttons/silver-header-help<?=$sufixlang?>-2.jpg',1)"><img src="images/buttons/silver-header-help<?=$sufixlang?>-1.jpg" alt="" name="help" width="40" height="43" border="0" id="help"></a><? } ?></td>
          <td><? if ($AppUI->user_id==$delegator_id) { ?><a href="index.php?logout=-1" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('signout','','images/buttons/silver-header-signout<?=$sufixlang?>-2.jpg',1)"><? }else{ ?><a href="javascript:window.close();" onMouseOut="MM_swapImgRestore()" onMouseOver="MM_swapImage('signout','','images/buttons/silver-header-signout<?=$sufixlang?>-2.jpg',1)"><? } ?><img src="images/buttons/silver-header-signout<?=$sufixlang?>-1.jpg" alt="" name="signout" width="36" height="43" border="0" id="signout"></a></td>
          <td valign="bottom" style="background-image:url(images/buttons/silver-header-end.jpg);"><a href="javascript:showHideTwitter();" id="hrefTwitter"><img id="imgCollapse" src="./images/icons/<?=($showTwitter ? 'collapse_alter.gif' : 'expand_alter.gif');?>" border="0" height="15" width="15" /></a></td>
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
						<?
							if($usr->user_pic != '')
								$user_pic_header = $AppUI->getConfig('hhrr_uploads_dir').'/'.$usr->user_id.'/'.rawurlencode($usr->user_pic);
							else
								$user_pic_header = "images/twitter2.png";
						?>
						<tr>
								<td class="nombreuser" bgcolor="#666666">
									<a href="index.php?m=admin&a=addedituser&user_id=<?=$AppUI->user_id?>"><img width="50" border="0" height="50" src="<?=$user_pic_header?>" /></a>
								</td>
								<?
								if (!$AppUI->getState('myassigment_dateactive'))
								{
									//Obtengo mi asignación
									$sql = "SELECT * FROM myassigments_active WHERE user_id = ".$AppUI->user_id;
	
									$myassigment = db_loadList($sql);

									//Si tengo una asignación, entro
									if(count($myassigment) > 0)
									{
										$dateAssigment = new CDate($myassigment[0]['myassigment_date']);
										$AppUI->setState('myassigment_dateactive', $dateAssigment->format($AppUI->user_prefs['SHDATEFORMAT'].' '.$AppUI->user_prefs['TIMEFORMAT']));
									}
									else
										$AppUI->setState('myassigment_dateactive', "N/A");
								}
								
								?>
								<td class="nombreuser" bgcolor="#666666">
									<span title="<?echo $AppUI->_('Last updated assigment active')?>"><?echo $AppUI->_('Updated')?>:
									<i><?echo $AppUI->getState('myassigment_dateactive') ?></i></span>
								</td>								
							</tr>
						<tr>
				 			<td class="nombreuser" bgcolor="#666666" colspan="2">
				 				<?php echo $AppUI->_("Welcome"); ?><br>
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
							<td class="nombreuser" bgcolor="#666666"><img onclick="javascript:getFavoritesItems();" style="cursor:pointer;" src="images/favorites_icon.gif" /></td>
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
//timeCrono+= ((seg < 10) ? ":0" : ":") + seg;
document.getElementById("online_time").innerHTML = timeCrono;
seg++;
setTimeout("StartCrono()",1000);
}
 //-->
</script>
	           </td>
	        </tr>
<?php
if ( $AppUI->user_id == $delegator_id || $_GET['listOP'] == 1)
{
	//Menu propio
	$group_lang='g.mod_group_'.$AppUI->user_locale;
	$nav = $AppUI->getMenuModules($group_lang);
	//$s = '';
	
	//echo "<pre>"; print_r($nav);echo "</pre>";
	$s = '
	        <tr >
				<td colspan="3" >

					<table border="0" cellspacing="0" cellpadding="0"
					style=" background-image: url(images/common/back_grande.jpg);
							background-repeat: repeat-x;
							width: 100%;
	">';
			$s .= '<tr>'
				.'	<td rowspan="100"><img src="images/1x1.gif" height="360" width="1"></td>'
				.'</tr>';
			$s .= '<tr height="1px">'
				.'	<td colspan="5"><img src="images/1x1.gif" height="1" width="135"></td>'
				.'</tr>';
	
	$s .= "<input type=\"hidden\" id=\"favorites_hidden\" />";
					
	$white_space = 360;
	$old_group='';
	foreach ($nav as $module)
	{
		if (!getDenyRead( $module['mod_directory'] ))
		{
			if ($module['group_name']!=$old_group){
				$old_group=$module['group_name'];
				$s .= '<tr height="15px">'
				.'  <td colspan=4>&nbsp;</td>'
				.'</tr>';
				$s .= '<tr height="15px">'
				.'	<!--<td><img src="images/1x1.gif" height="15" width="1"></td>-->'
				.'  <td colspan=4 nowrap=\"nowrap\"><b>'.$module['group_name'].'<b></td>'
				.'</tr>';
			}
			$white_space -= 15;
			$s .= '<tr height="15px">'
				.'	<td><img src="images/1x1.gif" height="15" width="3"></td>'
				.'  <td><img src="images/silver-cuadradito.gif" width="4" height="9"></td>'
				.'	<td><img src="images/1x1.gif" height="15" width="3"></td>'
				.'  <td nowrap=\"nowrap\"> <a href="?m='.$module['mod_directory'].'" class="special">'.$AppUI->_($module['mod_ui_name']).'</a></td>'
				.'</tr>';
		}
	}
	$white_space = $white_space > 0 ? $white_space : 1;
	$s .= '			<tr><td colspan="5" ><img src="images/1x1.gif" height="'.$white_space.'" width="135"></td></tr>
					</table>
	           </td>
	        </tr>';
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

	if($_GET['listOP'] != 1)
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
<? require_once ('modules/twitter/view.php'); ?>
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
	//echo $AppUI->getMsg();
?>

<?if($AppUI->msg != ''){
	$htmlmessage = str_replace("'",'', $AppUI->getMsg());
	$htmlmessage = str_replace('"','\"', $htmlmessage);
	?>
	<script language="javascript">
		showGenericMessage('<?=$htmlmessage;?>');
	</script>
<?}?>


