<?php /* SYSTEM $Id: addeditpref.php,v 1.2 2009-05-26 07:20:33 pkerestezachi Exp $ */
##
## add or edit a user preferences
##
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;

$canEdit = !getDenyEdit('system');

if (!$canEdit && $AppUI->user_id != $user_id){
	$AppUI->redirect( "m=public&a=access_denied" );
}


$sql = "
SELECT  pp.pref_name pref_name, coalesce(pu.pref_value, pp.pref_value) pref_value
FROM user_preferences pp 
	left join user_preferences pu on pp.pref_name = pu.pref_name and pu.pref_user = $user_id
WHERE pp.pref_user = 0";
$prefs = db_loadHashList( $sql );

// get the user name
$sql = "
SELECT user_first_name, user_last_name
FROM users
WHERE user_id = $user_id
";
$res  = db_exec( $sql );
echo db_error();
$user = db_fetch_row( $res );

$titleBlock = new CTitleBlock( 'Edit User Preferences', 'preferences.gif', $m, "$m.$a" );
if ( $user_id ){
	$titleBlock->addCrumb( "?m=admin&a=addedituser&user_id=".$user_id, "edit personal information" );
	$titleBlock->addCrumb( "?m=hhrr&a=addedit&tab=1&id=".$user_id, "edit hhrr information" );
	$titleBlock->addCrumb( "?m=admin&a=calendars&user_id=".$user_id, "work calendar" );
	
	if($user_id == $AppUI->user_id)
		$titleBlock->addCrumb( "javascript: popChgPwd();", "change password" );
}

$titleBlock->show();
?>
<script language="javascript">
function submitIt(){
	var form = document.changeuser;
		form.submit();
}

function popChgPwd() {
	window.open( './index.php?m=public&a=chpwd&dialog=1&suppressLogo=1', 'chpwd', 'top=250,left=250,width=350, height=220, scollbars=false' );
}
</script>

<table width="100%" border="0" cellpadding="1" cellspacing="0" class="tableForm_bg">

<form name="changeuser"  method="post">
	<input type="hidden" name="dosql" value="do_preference_aed" />
	<input type="hidden" name="pref_user" value="<?php echo $user_id;?>" />
	<input type="hidden" name="del" value="0" />

<tr height="20">
	<th colspan="2" >
        <table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_04.gif">
            <tr>
                <td align="left"><img src="images/common/lado.gif" width="1" height="17"></td>
                <td>
                <?php echo $AppUI->_('User Preferences');?>:
                <?php
                    echo $user_id ? "$user[0] $user[1]" : $AppUI->_("Default");
                ?>
                </td>
                <td align="right"><img src="images/common/lado.gif" width="1" height="17"></td>
            </tr>
            <tr bgcolor="#666666">
                <td colspan="3"></td>
            </tr>
        </table>
    </th>
</tr>

<tr >
	<td align="right"><?php echo $AppUI->_('Locale');?>:</td>
	<td>
<?php
	// read the installed languages
	$locales = $AppUI->readDirs( 'locales' );
	$temp = $AppUI->setWarning( false );
	echo arraySelect( $locales, 'pref_name[LOCALE]', 'class=text size=1', @$prefs['LOCALE'], true );
	$AppUI->setWarning( $temp );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Tabbed Box View');?>:</td>
	<td>
<?php
	$tabview = array( 'either', 'tabbed', 'flat' );
	echo arraySelect( $tabview, 'pref_name[TABVIEW]', 'class=text size=1', @$prefs['TABVIEW'], true );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Short Date Format');?>:</td>
	<td>
<?php
	// exmample date
	$ex = new CDate();

	$dates = array();
	$f = "%d/%m/%Y"; $dates[$f]	= $ex->format( $f );
	$f = "%d/%b/%Y"; $dates[$f]	= $ex->format( $f );
	$f = "%m/%d/%Y"; $dates[$f]	= $ex->format( $f );
	$f = "%b/%d/%Y"; $dates[$f]	= $ex->format( $f );
	$f = "%d.%m.%Y"; $dates[$f]	= $ex->format( $f );
	echo arraySelect( $dates, 'pref_name[SHDATEFORMAT]', 'class=text size=1', @$prefs['SHDATEFORMAT'], false );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Time Format');?>:</td>
	<td>
<?php
	// exmample date
	$times = array();
	$f = "%I:%M %p"; $times[$f]	= $ex->format( $f );
	$f = "%H:%M"; $times[$f]	= $ex->format( $f ).' (24)';
	$f = "%H:%M:%S"; $times[$f]	= $ex->format( $f ).' (24)';
	echo arraySelect( $times, 'pref_name[TIMEFORMAT]', 'class=text size=1', @$prefs['TIMEFORMAT'], false );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('User Interface Style');?>:</td>
	<td>
<?php
	/*$styles = $AppUI->readDirs( 'style' );
	$temp = $AppUI->setWarning( false );
	echo arraySelect( $styles, 'pref_name[UISTYLE]', 'class=text size=1', @$prefs['UISTYLE'], true );
	$AppUI->setWarning( $temp );*/
?>
	<select name="pref_name[UISTYLE]" class=text size=1 style="width : 160 px;" >
		<option value="silver" selected="selected">Proficient</option>
	</select>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('User Homepage');?>:</td>
	<td>
<?php
	$nav = $AppUI->getMenuModules();
	$ind=0;
	$menus=Array();
	$menus['m=dashboard&a=viewdb'] = 'DashBoard';
	$menus['m=calendar&a=day_view'] = 'today';
	foreach ($nav as $module) {
		if (!getDenyRead( $module['mod_directory'] )) {
			$menus["m=".$module['mod_directory']] = $module['mod_ui_name'];
		}
	}

	echo arraySelect( $menus, 'pref_name[HOMEPAGE]', 'class=text size=1', @$prefs['HOMEPAGE'], true );
?>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Records per Page');?>:</td>
	<td><?php
	$page_numbers = array();
	for ($i = 5; $i <= 100; $i = $i +5){
		$page_numbers[$i] = $i;
	}
	echo arraySelect( $page_numbers, 'pref_name[RECORDSxPAGE]', 'class=text size=1', @($prefs['RECORDSxPAGE'] ? $prefs['RECORDSxPAGE'] : $AppUI->getConfig('records_per_page')) );

	?>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('State public (twitter)');?>:</td>
	<td><?php
	$twitterState = array();
	$twitterState[0] = $AppUI->_('My Company');
	$twitterState[1] = $AppUI->_('All Companies');
	
	echo arraySelect( $twitterState, 'pref_name[STPU_TWITTER]', 'class=text size=1', @($prefs['STPU_TWITTER'] ? $prefs['STPU_TWITTER'] : 0) );

	?>
	</td>
</tr>
<tr>
	<td align="left"><input class="button"  type="button" value="<?php echo $AppUI->_('back');?>" onClick="javascript:history.back(-1);" /></td>
	<td align="right"><input class="button" type="button" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt()" /></td>
</tr>
</table>
