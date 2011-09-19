<?php /* SYSTEM $Id: calendars.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */


global $m, $project_id, $calendar_type, $canEdit;

$obj = new CProject();

if (!$obj->load($project_id, false)){
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();

}
$canEdit = $obj->canEdit();

require_once( $AppUI->getModuleClass( 'system' ) );

$calendar_type = "2";

include_once( "./modules/system/calendars.php");




/*

global $canEdit,$m,$a,$company_id, $tab,$action;


if ($action=="addedit")
	include("addeditcalendar.php");
elseif($action=="view")
	include("viewcalendar.php");
else{
*/

	/*
	
// setup the title block
$titleBlock = new CTitleBlock( 'Company Calendars', 'handshake.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view company" );
$titleBlock->show();


$cpy = new CCompany();
$company_id = intval( dPgetParam( $_GET, "company_id", 0 ) );
//echo "<p>company id = $company_id</p>";
if ( !$cpy->load( $company_id ) || !$company_id )
{
	$AppUI->setMsg( "Company: InvalidId", UI_ERROR_MSG );
	$AppUI->redirect();
}
$d = new CDate();
$AppUI->savePlace( );

$df = $AppUI->getPref('SHDATEFORMAT');
$calendars = $cpy->getCalendars();

$days = array(
	1=>"Sunday",
	2=>"Monday",
	3=>"Tuesday",
	4=>"Wednesday",
	5=>"Thursday",
	6=>"Friday",
	7=>"Saturday"
);

?>
<table width="100%" border=0 cellpadding="2" cellspacing="0" class="">
<tr class="tableHeaderGral">
	<th width="50px">&nbsp;</th>
	<th width="100px"><?php echo $AppUI->_( 'Start Date' );?></th>
	<th width="150px"><?php echo $AppUI->_( 'Name' );?></th>
	<th><?php echo $AppUI->_( 'Working Days' );?></th>
</tr>
<?php if (count($calendars)==0) { ?>
<tr>
	<td colspan="97"><?php echo $AppUI->_('No data available').'<br />'.$AppUI->getMsg();?></td>
</tr>
<?php } else { 
$s = '';
for ($i = 0; $i < count($calendars) ; $i++){
	extract($calendars[$i]);

	$cal = new CCalendar();
	if ( !$cal->load( $calendar_id ) )
	{
		$AppUI->setMsg( "Calendar: InvalidId", UI_ERROR_MSG );
		$AppUI->redirect();
	}
	$cal->loadCalendarDays();
	$cal_date = new CDate($cal->calendar_from_date);
	
	$working_days = array();
	for ($i = 1 ; $i <= 7; $i++){
		if ($cal->_calendar_days[$i]->calendar_day_working)
			$working_days[]=$AppUI->_($days[$i]);
	}
	?>
<tr>
	<td>
<?php if ($canEdit){ ?>	
		<a href="<?php echo $PHP_SELF."?m=$m&a=addeditcalendar&calendar_id=$calendar_id";?>">
		<img src="./images/icons/edit_small.gif" alt="<?php echo $AppUI->_( 'Edit' )?>" 
		border="0" width="20" height="20"></a>
		<a href="">
		<img src="./images/icons/trash_small.gif" alt="<?php echo $AppUI->_( 'Delete' )?>" 
		border="0" width="20" height="20"></a>
<?php } ?>		
		</td>
	<td><?php echo $cal_date->format($df);?></td>
	<td><a href="<?php echo $PHP_SELF."?m=$m&a=viewcalendar&calendar_id=$calendar_id";?>">
		<?php echo $cal->calendar_name;?></a></td>
	<td><?php echo implode(", ", $working_days);?></td>
</tr>
<tr class="tableRowLineCell"><td colspan="97"></td></tr>
<?php
}
if ($canEdit){
?>
<tr>
	<td colspan="97" align="right"><input type="button" value="<?php echo $AppUI->_( 'Add' )?>" class="button"onclick="javascript: document.location = '<?php 
	
	echo $PHP_SELF."?m=$m&a=$a&company_id=$company_id&tab=$tab&action=addedit";
	
	?>';" /></td>
</tr>
<?php } ?>
</table>
<?php } 
*/
?>