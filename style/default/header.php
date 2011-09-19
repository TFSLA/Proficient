<?php /* STYLE/DEFAULT $Id: header.php,v 1.1 2009-05-19 21:15:50 pkerestezachi Exp $ */
$dialog = dPgetParam( $_GET, 'dialog', 0 );
$suppressLogo = dPgetParam( $_GET, "suppressLogo", 0 ); 
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$autorefresh = dPgetParam( $_GET, 'autorefresh', 0 );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
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

<script language="Javascript" src="./lib/jsLibs/functions.php" type="text/javascript"></script>
<script language="Javascript" src="./lib/dhtmltooltip/dhtmltooltip.js" type="text/javascript"></script>
</head>

<body link="#333333" vlink="#333333" onload="this.focus();<?PHP
		$header_onload = $AppUI->getJsEvent("onload", true );
		if ($header_onload != NULL){
			echo $header_onload;
		}
?>">
<div id="dhtmltooltip"></div>

<? if (!$suppressLogo){?>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<td>
		<table width="100%" border=0 cellpadding=0 cellspacing=0 bgcolor="#22368c">    
		<tr>
		<td align=left><img src="images/top_01.jpg" width="443" height="37"></td>
		<td align=right><div align="right"><img src="images/top_02.jpg" width="317" height="37"></div></td>

		</td>
		</tr>
		</table>

	</td>
	</tr>

	<?php if (!$dialog) 
	{
		// top navigation menu
		$nav = $AppUI->getMenuModules();
	?>
	<tr>
		<td class="nav" align="left">
		<table background="images/back_02.jpg" width="100%" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td class="botonera" align="center">
			<?php		
			$links = array();
			if ( $AppUI->user_id == $delegator_id )
			{			
				foreach ($nav as $module) 
				{
					if (!getDenyRead( $module['mod_directory'])) 
					{
						$links[] = '<a href="?m='.$module['mod_directory'].'">'.$AppUI->_($module['mod_ui_name']).'</a>';
					}
				}
				echo implode( ' | ', $links );
				echo "\n";
					?> | <a href="?m=public&a=delegation_selector"><?=$AppUI->_("Delegated")?></a> <?			
			}
			else
			{
				require_once( $AppUI->getModuleClass( "admin" ) );
				$usr = new CUser();
				$usr->load( $AppUI->user_id );
				$modulos = $usr->getModulesDelegatedBy( $delegator_id );
				foreach ($modulos as $module) 
				{
					$links[] = '<a href="?m='.$module['mod_directory'].'&delegator_id='.$delegator_id.'">'.$AppUI->_($module['mod_ui_name']).'</a>';				
				}
				echo implode( ' | ', $links );
			}
			?>		
			</td>
		</tr>
		</table>
		</td>
	</tr>
	<?
	}
	?>
	<tr>
		<td>
			<table cellspacing="0" cellpadding="3" border="0" width="100%" bgcolor=white>
			<tr>
				<td width="100%" class="welcome">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<?php 
					echo $AppUI->_('Welcome')." $AppUI->user_first_name $AppUI->user_last_name"; 
					echo ' - [<span id="online_time" name="online_time" title="'.$AppUI->_("Online").'">&nbsp;</span>]';
					?>
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
				<td nowrap="nowrap" class="navegationbar">
					<?php if(!getDenyRead( 'help' )) echo dPcontextHelp( 'Help' ) . "|" ;?>
					<?
					if ( $AppUI->user_id == $delegator_id)
					{
						?>
					<a href="./index.php?m=system&a=addeditpref&user_id=<?php echo $AppUI->user_id;?>"><?php echo $AppUI->_('My Info');?></a> |
						<?
					}
	if (!getDenyRead( 'calendar' ) && $AppUI->user_id == $delegator_id ) 
	{
		$now = new CDate();
	?>
					<a href="./index.php?m=calendar&a=day_view&delegator_id=<?=$delegator_id?>&date=<?php echo $now->format( FMT_TIMESTAMP_DATE );?>"><?php echo $AppUI->_('Today');?></a> |
	<?php 
	}
	if ( $AppUI->user_id == $delegator_id )
	{
		?>
					<a href="./index.php?m=dashboard&a=viewdb"><?php echo $AppUI->_('Dashboard');?></a> |
		<? 
	}
					if ( $AppUI->user_id == $delegator_id )
					{
						?>
					<a href="./index.php?logout=-1"><?php echo $AppUI->_('Logout');?></a>
						<?
					}
					else
					{
						?>
						<a href="#" onClick="window.close();"><?php echo $AppUI->_('Close');?></a>
						<?
					}
						?>
				</td>
			<form name="frm_new" method=GET action="./index.php">
	<?php
	echo '        <td nowrap="nowrap" align="right">';
	$newItem = array( ""=>'- New Item -' );
	if(!getDenyEdit( 'companies' ))   $newItem["companies"]   = "Company";
	if(!getDenyEdit( 'projects' ))    $newItem["projects"]    = "Project";
	if(!getDenyEdit( 'files' ))       $newItem["files"]       = "File";
	if(!getDenyEdit( 'contacts' ))    $newItem["contacts"]    = "Contact";
	if(!getDenyEdit( 'calendar' ))    $newItem["calendar"]    = "Event";
	if(!getDenyEdit( 'hhrr' ))        $newItem["hhrr"]        =  "Resource";
	if(!getDenyEdit( 'pipeline' ))    $newItem["pipeline"]    = "Lead";
	if(!getDenyEdit( 'webtracking' )) $newItem["webtracking"] = "Bug";
	if(!getDenyEdit( 'admin' ))       $newItem["admin"]       = "User";

	echo arraySelect( $newItem, 'm', 'style="font-size:10px" onChange="f=document.frm_new;mod=f.m.options[f.m.selectedIndex].value;if(mod) f.submit();"', '', true);

	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
	echo "        <input type=\"hidden\" name=\"a\" value=\"addedit\" />\n";

	//build URI string
	if (isset( $company_id )) 
	{
		echo '<input type="hidden" name="company_id" value="'.$company_id.'" />';
	}
	if (isset( $task_id )) 
	{
		echo '<input type="hidden" name="task_parent" value="'.$task_id.'" />';
	}
	if (isset( $file_id )) 
	{
		echo '<input type="hidden" name="file_id" value="'.$file_id.'" />';
	}
	?>
			</form>
			</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>

<? } ?>
<table width="100%" cellspacing="0" cellpadding="2" border="0">
<tr>
<td valign="top" align="left" width="98%">
<?php
	echo $AppUI->getMsg();
?>