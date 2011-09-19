<?php
/*
//$debugsql=1;
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id);
//$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $user_id );
$mod_id = 18;
$lead_id = dPgetParam( $_GET, "lead_id", 0 );
$contact_id = dPgetParam( $_GET, "contact_id", 0 );
$df = $AppUI->getPref('SHDATEFORMAT');

//El archivo deberia llamarse pipeline.class.php!!!
//require_once( $AppUI->getModuleClass( "pipeline") );
require_once( $AppUI->getConfig( "root_dir")."/modules/pipeline/leads.class.php" );
$lead = new CLead();

if ( !$lead->load( $lead_id ) && $lead_id > 0)  
{
	$AppUI->setMsg( 'Lead' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} 

$contact = new CLeadContact();
if ( !$contact->load( $contact_id ) && $contact_id > 0)  
{
	$AppUI->setMsg( 'LeadContact' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} 

if ( $delegator_id != $AppUI->user_id )
{	
	require_once( $AppUI->getModuleClass( "admin" ) );
	$usr = new CUser();
	$usr->load( $AppUI->user_id );

	//Hay que chequear que este sea un delegador valido	
	if ( !$usr->isDelegator($delegator_id, $mod_id) && $AppUI->user_type != 1 )
	{
		$AppUI->setMsg( 'Delegator' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$delegador = new CUser();
	$delegador->load( $delegator_id );
	$permisos = $delegador->getDelegatePermission( $AppUI->user_id, $mod_id );
	$canEdit = !$id;
	$canEdit = $canEdit || ( $permisos == "AUTHOR" && $lead->account_owner == $delegator_id && $lead->account_creator == $AppUI->user_id );
	$canEdit = $canEdit || ( $permisos == "EDITOR" && $lead->account_owner == $delegator_id);
	$canEdit = $canEdit || $AppUI->user_type == 1;
}

if ( !$canEdit )
{
	$AppUI->redirect( "m=public&a=access_denied" );
}

// setup the title block
$ttl = $contact->id > 0 ? "Edit lead contact log" : "Add lead contact log";
$titleBlock = new CTitleBlock( $ttl, 'pipeline.gif', $m, 'ID_HELP_DEPT_EDIT' );
$titleBlock->show();
?>
<script language="javascript">

var calendarField = '';

function popCalendar( field )
{
	calendarField = field;
	idate = eval( 'document.editFrm.' + field + '.value' );	
	if ( idate == "0000-00-00" )
	{
		idate = "<?=$date?>";		
	}
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate +'&suppressLogo=1', 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}


function setCalendar( idate, fdate )
{	
	fld_date = eval( 'document.editFrm.' + calendarField );	
	fld_fdate = eval( 'document.editFrm._' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;	
}

function submitIt() {
	var form = document.editFrm;
	if (form.summary.value.length < 1) {
		alert( "<? echo $AppUI->_('Please enter the summary')?>" );
		form.summary.focus();
	} else 
		form.submit();
}
</script>
<form name="editFrm" action="?m=pipeline&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" method="post">
	<input type="hidden" name="dosql" value="do_leadcontact_aed" />
	<input type="hidden" name="id" value="<?php echo $contact->id;?>" />
	<input type="hidden" name="lead_id" value="<?=$lead->id?>" />
	
<table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
<tr>
	<td align="right"><?php echo $AppUI->_( 'Date' );?>:</td>
	<td>
	<?
	$d = new CDate( $contact->date );
	?>
		<input type="hidden" name="date" value="<?=$d->format( FMT_DATETIME_MYSQL )?>">
		<input type="text" class="text" name="_date" value="<?php echo $d->format( $df );?>" maxlength="10" size="10" disabled>
		<a href="#" onClick="popCalendar('date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>	
		&nbsp;&nbsp;<?php echo $AppUI->_('Kind of contact' );?>:		
		<select name="kindofcontact" class="text">
			<option <?if ( $contact->kindofcontact=="Call" )echo "selected"?> value="Call"><?php echo $AppUI->_('Call')?></option>
			<option <?if ( $contact->kindofcontact=="Meeting" ) echo "selected"?> value="Meeting"><?php echo $AppUI->_('Meeting')?></option>
			<option <?if ( $contact->kindofcontact=="Email" ) echo "selected"?> value="Email"><?php echo $AppUI->_('Email')?></option>
		</select>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Summary' );?>:</td>
	<td><input type="text" class="text" name="summary" value="<?php echo $contact->summary;?>" maxlength="60" size="60"></td>
</tr>
<tr>
	<td valign="top" align="right"><br><?php echo $AppUI->_( 'Description' );?>:</td>
	<td><textarea rows="6" cols="60" name="description"><?php echo $contact->description;?></textarea>
</tr>
<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
	</td>
	<td colspan="4" align="right">
		<input type="button" value="<?php echo $AppUI->_( 'submit' );?>" class="button" onClick="submitIt()" />
	</td>
</tr>
</form>
</table>
<br>*/

require_once("./modules/contacts/contacts.class.php");
include("./modules/contacts/addedit.php");

?>