<?
$AppUI->savePlace();

$canRead = !getDenyRead( $m  );
$canEdit = !getDenyEdit( $m  );
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

if (isset( $_GET['tab'] )) {
    $AppUI->setState( 'WmailIdxTab', $_GET['tab'] );
}
if($fromlogin!=true) $tab = $AppUI->getState( 'WmailIdxTab' ) !== NULL ? $AppUI->getState( 'WmailIdxTab' ) : 0;

//
include_once("./modules/wmail/lang/".$my_prefs["lang"]."defaultFolders.inc");
include_once("./modules/wmail/lang/".$my_prefs["lang"]."main.inc");
include_once("./modules/wmail/lang/".$my_prefs["lang"]."compose.inc");

$arLinks=array();
if($tab==0){
    if($id > 0){//si veo un mensaje
        $strFolder="Read Message";
        $arLinks[0]["link"]="?m=wmail&a=bridge&tab=0&session={$user}&folder={$folder}&start={$start}&sort_field={$sort_field}&sort_order={$sort_order}";
        $arLinks[0]["text"]="Index";
    }else{
        $strFolder="Inbox";
        $arLinks[0]["link"]="?m=wmail&tab=0&a=bridge&session={$user}&folder={$folder}";
        $arLinks[0]["text"]=$mainStrings[17];
        $arLinks[1]["link"]="?m=wmail&tab=0&a=bridge&session={$user}&folder={$folder}&delete_all=1";
        $arLinks[1]["text"]=$mainStrings[18];
    }

}elseif($tab==1){
    //$strFolder="Compose Message2";
    $strFolder=$composeStrings[0];
    //$arLinks[0]["link"]="javascript:window.close();\"";
    //$arLinks[0]["text"]=$composeStrings[11];
}elseif($tab==2){
    $strFolder="bookmarks";
}elseif($tab==3){
    $strFolder="identities";
}



$titleBlock = new CTitleBlock( $AppUI->_($strFolder), 'webmail.gif', $m, "colaboration.index" );
if (count($arLinks)) {
    foreach($arLinks as $ActionLinks){
    	$titleBlock->addCrumb($ActionLinks["link"], $ActionLinks["text"]);
	}
}

$titleBlock->show();
/*
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_06.gif">
  <tr>
    <td width="6" align="left"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
    <td  width="100%" align="left"><span class="boldblanco"><img src="images/common/cuadradito_naranja.gif" width="9" height="9">
    <?php echo $AppUI->_($strFolder); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
    <td align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></td>
  </tr>
  <tr bgcolor="#666666">
    <td height="1" colspan="3"></td>
  </tr>
<?php if (count($arLinks)) { ?>
  <tr>
    <td colspan="3">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_degrade.gif">
            <tr>
              <td width="6"><img src="images/common/ladoizq.gif" width="6" height="19"></td>
              <td>
                <?php
                    foreach($arLinks as $ActionLinks){
                        echo " [&nbsp;<a href=\"".$ActionLinks["link"]."\">".$ActionLinks["text"]."</a>&nbsp;]&nbsp;";
                    }
                    if(!count($arLinks)) echo "&nbsp;";
                ?>
                </td>
              <td width="6"> <div align="right"><img src="images/common/ladoder.gif" width="6" height="19"></div></td>
            </tr>
        </table>
      </td>
  </tr>
<?php } ?>  
  <tr bgcolor="#666666">
    <td height="1" colspan="3"></td>
  </tr>
</table>
<?php
*/
//

// tabbed information boxes
$tabBox = new CTabBox( "?m=wmail&session={$user}&folder={$folder}", "{$AppUI->cfg['root_dir']}/modules/wmail/", $tab );
$tabBox->add( 'main', 'Inbox' );
$tabBox->add( 'compose2', 'Compose' );
$tabBox->add( 'bookmarks', 'Bookmarks' );
$tabBox->add( 'pref_identities', 'Identities' );
/*
$tabBox->add( 'folders', 'Folders' );
$tabBox->add( 'prefs', 'Options' );
*/

if($users[0]["user_webmail_autologin"]=="No") $tabBox->add( 'logout', 'Logout' );
//$tabBox->add( 'logout', 'Logout' );

$tabBox->show('',false  );
?>
