<?php /* SYSTEM $Id: holliday_edit.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */

// only user_type of Administrator (1) can access this page
if (!$canEdit && $AppUI->user_type != 1) {
	$AppUI->redirect( "m=public&a=access_denied" );
}
$titleBlock = new CTitleBlock( 'Hollidays', 'language_support.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system", "system admin" );
$titleBlock->show();
$holliday_id = dpGetParam( $_GET, "holliday_id", 0 );
$obj = new CHolliday();
if ( !$obj->load( $holliday_id) && $holliday_id )
{
	$AppUI->setMsg( "Holliday InvalidId", UI_MSG_ERROR );
	$AppUI->redirect();	
}
$months = array(
	1=>"January",
	2=>"February",
	3=>"March",
	4=>"April",
	5=>"May",
	6=>"June",
	7=>"July",
	8=>"August",
	9=>"September",
	10=>"October",
	11=>"November",
	12=>"December"	
);
?>
<script language="javascript">
function submitIt()
{
	var f = document.editFrm;
	var comboDia = f.holliday_day;
	var comboMes = f.holliday_month;
	
	var doSubmit = true;
	
	if ( f.holliday_name.value == "" )
	{
		doSubmit = false;
		alert( "<?=$AppUI->_("Please enter a name for the holliday")?>" );
	}
	else
	{
		var daysOfMonth = new Array( 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		
		if ( comboDia.value > daysOfMonth[comboMes.value-1] )
		{
			alert( "<?=$AppUI->_("That isn\'t a valid date")?>" );
			doSubmit = false;
		}
	}
	
	if ( doSubmit )
	{
		f.submit();	
	}	
}
</script>
<table width="100%" border="0" cellpadding="1" cellspacing="0" class="">
<tr class="tableHeaderGral">
	<th width="8%" nowrap>&nbsp;</th>
	<th width="45%" nowrap><?php echo $AppUI->_( 'Name' );?></th>
	<th width="20%" nowrap><?php echo $AppUI->_( 'Day' );?></th>
	<th width="20%" nowrap><?php echo $AppUI->_( 'Month' );?></th>
	<th width="10%" nowrap>&nbsp;</th>
</tr>
<form action="?m=system" method="post" name="editFrm">
	<input type="hidden" name="dosql" value="do_holliday_aed" />
	<input type="hidden" name="holliday_id" value="<?=$holliday_id?>" />
	<input type="hidden" name="holliday_year" value="<?=$obj->holliday_year?>" />
<tr>
	<td></td>
	<td>
		<input type="text" maxlength="255" size="40" class="text" name="holliday_name" value="<?=$obj->holliday_name?>"/>
	</td>
	<td>
	<?
	$days = array();
	for ( $i = 1; $i < 32; $i++ )
	{
		$days[$i] = $i;
	}
	echo arraySelect( $days, "holliday_day", 'class="text"', $obj->holliday_day );
	?>		
	</td>	
	<td>
		<?=arraySelect( $months, "holliday_month", 'class="text"', $obj->holliday_month, true )?>
	</td>
	<td>
		<input type="button" value="<?=$AppUI->_("save")?>" onclick="submitIt()" class="button">
	</td>
</tr>
<tr class="tableRowLineCell">
    <td colspan="5"></td>
</tr>
</form>
</table>
<input type="button" value="<?=$AppUI->_("back")?>" onclick="history.go(-1)" class="button">