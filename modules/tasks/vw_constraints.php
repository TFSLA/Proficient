<?php
$AppUI->savePlace();
$task_id = dpGetParam( $_GET, "task_id", 0 );

if ( !$task_id )
{
	$AppUI->setMsg( 'Task' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}
$obj = new CTask();
$obj->load( $task_id );
// setup the title block
$titleBlock = new CTitleBlock( 'Task constraints', 'tasks.gif', $m, "$m.$a" );
$titleBlock->show();

$constr_types = array ( 'ALAP'=>"As soon as possible", 'ASAP'=>"As late as possible", 'FNET'=>"Finish no earlier than", 'FNLT'=>"Finish no later than", 'MFO'=>"Must finish on", 'MSO'=>"Must start on", 'SNET'=>"Start no earlier than", 'SNLT'=>"Start no later than" );
$allowed_constr_types = $constr_types;
//En ppio son todos validos, a medida que leo los que tiene voy quitando los que no sirven

$df = $AppUI->getPref('SHDATEFORMAT');
?>
<script language="JavaScript">
function submitIt()
{
	var frm = document.editFrm;
	
	if ( frm.constraint_type.value != "ASAP" && frm.constraint_type.value != "ALAP" )
	{
		if ( frm.constraint_parameter.value == "" )
		{
			alert( "<?=$AppUI->_("The type of constraint you selected requires a date parameter")?>" );
			return;
		}
	}
	frm.submit();
}

function delIt(id) {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Task constraint').'?';?>" )) {
		document.frmDelete.constraint_id.value = id;
		document.frmDelete.submit();
	}
}

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.constraint_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.constraint_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function toggleParameterSelection()
{
	var elDiv = document.getElementById("parameterSelection");
	var elCombo = document.editFrm.constraint_type;
	
	if ( elCombo.value == "ALAP" || elCombo.value == "ASAP" )
	{
		elDiv.style.visibility = "hidden";
	}
	else
	{
		elDiv.style.visibility = "visible";
	}
}
</script>

<form name="frmDelete" action="?m=tasks" method="post">
	<input type="hidden" name="dosql" value="do_constraint_aed">
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="constraint_id" value="0" />
</form>

<table cellpadding="2" cellspacing="0" border="0" width="100%" class="">
<tr>
	<th colspan="3" align="center"><?=$AppUI->_("Existing constraints")?></th>
</tr>
<tr class="tableHeaderGral">
	<th width="20" align="right">&nbsp;</th>
	<th align="right" width="250"><?php echo $AppUI->_('Type of constraint');?></th>
	<th align="left"><?php echo $AppUI->_('Date');?></th>
</tr>
<?
$constrs = $obj->getConstraints();
$constr = new CTaskConstraint();
foreach( $constrs as $constr_id )
{	
	$constr->load( $constr_id["constraint_id"] );
	if ( $constr->constraint_parameter )
	{
		$d = new CDate($constr->constraint_parameter);		
		$dd = $d->format( $df );
	}
	else
	{
		$dd = "";
	}		
	?>
<tr>
	<td>
		<a href="javascript:delIt(<?php echo $constr_id["constraint_id"];?>)" title="<?php echo $AppUI->_('delete');?>">
			<?php echo dPshowImage( './images/icons/trash_small.gif', NULL, NULL, '' ); ?>
		</a>
	</td>
	<td align="right"><?=$AppUI->_($constr_types[$constr->constraint_type])?></td>
	<td align="left"><?=$dd?></td>
</tr>
<tr class="tableRowLineCell"><td colspan="3"></td></tr>  
	<?
	//Sacar los elementos que no se pueden combinar con este para los nuevos constraints
	$allowed_constr_types = allowed( $allowed_constr_types, $constr->constraint_type );
}
?>
</table>
<? 
if ( count ( $allowed_constr_types ) )
{
	?>
<form name="editFrm" action="?m=tasks" method="post">
<input type="hidden" name="dosql" value="do_constraint_aed"/>
<input type="hidden" name="task_id" value="<?=$task_id?>"/>
<table cellpadding="2" cellspacing="0" border="0" width="100%" class="">
<tr class="tableHeaderGral">
	<th colspan="3" align="center"><?=$AppUI->_("New constraint")?></th>
</tr>
<tr class="tableForm_bg">
	<td align="right" width="275"><?=arraySelect($allowed_constr_types, "constraint_type", 'class="text" onclick="toggleParameterSelection()"', '', true)?></td>
	<td align="left">
		<div id="parameterSelection" style="visibility: hidden">
		<input type="hidden" name="constraint_parameter" value="">
		<input type="text" name="parameter" value="" class="text" disabled="disabled">
		<a href="#" onClick="popCalendar('parameter')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
		</div>
	</td>
	<td align="right">
		<input type="button" class="button" value="<?=$AppUI->_("add")?>" onclick="submitIt()"/>
	</td>
</tr>
</form>
</table>
	<?
}
?>
<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std">
<tr>
	<td colspan="2">
		<input type="button" value="<?php echo $AppUI->_( 'done' );?>" class="button" onclick="window.location.href='?m=tasks&a=view&task_id=<?=$task_id?>';">
	</td>
</tr>
</table>
<?

/*
	@author Mauro
	@param array de tipos de constraints
	@param tipo de constraint que se desea ver
	@return array de tipos de constraints compatibles con el que esta
*/

function allowed( $constr_types, $constr_type )
{	
	if ( array_key_exists( $constr_type, $constr_types ) )
	{
		unset( $constr_types[$constr_type] );
	}
	
	//Imagino que los dos primeros invalidan a todos los demas, pero habria que chequearlo
	switch ( $constr_type )
	{
		case "ASAP":
			//No se como habria que tratar este constraint
			if ( array_key_exists( "ALAP", $constr_types ) )
			{
				unset( $constr_types["ALAP"] );
			}
			if ( array_key_exists( "MSO", $constr_types ) )
			{
				unset( $constr_types["MSO"] );
			}
			break;
		case "ALAP":
			//No se como habria que tratar este constraint
			if ( array_key_exists( "ASAP", $constr_types ) )
			{
				unset( $constr_types["ASAP"] );
			}
			if ( array_key_exists( "MFO", $constr_types ) )
			{
				unset( $constr_types["MFO"] );
			}
			break;
		case "FNLT":
		case "FNET":
			if ( array_key_exists( "MFO", $constr_types ) )
			{
				unset( $constr_types["MFO"] );
			}
			break;
		case "MFO":
			if ( array_key_exists( "FNLT", $constr_types ) )
			{
				unset( $constr_types["FNLT"] );
			}
			if ( array_key_exists( "FNET", $constr_types ) )
			{
				unset( $constr_types["FNET"] );
			}
			if ( array_key_exists( "ALAP", $constr_types ) )
			{
				unset( $constr_types["ALAP"] );
			}
		case "SNLT":
		case "SNET":
			if ( array_key_exists( "MSO", $constr_types ) )
			{
				unset( $constr_types["MSO"] );
			}
			break;
		case "MSO":
			if ( array_key_exists( "SNLT", $constr_types ) )
			{
				unset( $constr_types["SNLT"] );
			}
			if ( array_key_exists( "SNET", $constr_types ) )
			{
				unset( $constr_types["SNET"] );
			}
			if ( array_key_exists( "ASAP", $constr_types ) )
			{
				unset( $constr_types["ASAP"] );
			}
			break;			
	}	
	return $constr_types;
}

?>
