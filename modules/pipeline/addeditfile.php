<?php 
//$debugsql=1;
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id);
//$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $user_id );
$mod_id = 18;
$lead_id = dPgetParam( $_GET, "lead_id", 0 );
$file_id = dPgetParam( $_GET, "id", 0 );
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

$file = new CLeadFile();
if ( !$file->load( $file_id ) && $file_id > 0)  
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
/*echo "canEdit = $canEdit";
exit();*/
if ( !$canEdit )
{
	$AppUI->redirect( "m=public&a=access_denied" );
}

// setup the title block
$ttl = $file_id > 0 ? "Edit lead file" : "Add lead file";
$titleBlock = new CTitleBlock( $ttl, 'pipeline.gif', $m, 'ID_HELP_DEPT_EDIT' );
$titleBlock->show();
?>
<script language="javascript">

function submitIt() {
	var form = document.editFrm;
<?
if($id!=0){
?>
 form.submit();
<?
}
else{
?>
	if (form.filename.value.length < 1) {
		alert( "<? echo $AppUI->_('Please select the file')?>" );
		form.filename.focus();
	} else 
		form.submit();
<?}?>
}
</script>
<table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
<form name="editFrm" action="?m=pipeline&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" method="post" enctype="multipart/form-data" >
	<input type="hidden" name="dosql" value="do_leadfile_aed" />
	<input type="hidden" name="id" value="<?php echo $file->id;?>" />
	<input type="hidden" name="lead_id" value="<?php echo $lead->id;?>" />
<?
if(!$file->id)
  $date=date("Y-m-d");
else 
  $date=$file->date;
  
$d = new CDate( $date );
?>
	<input type="hidden" name="date" value="<?php echo $date;?>" />
<tr>
	<td align="right"><?php echo $AppUI->_( 'Date' );?>:</td>
	<td><?php echo $d->format( $df );?>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Short description' );?>:</td>
	<td><input type="text" class="text" name="shortdesc" value="<?php echo $file->shortdesc;?>" maxlength="64" size="64"></td>
</tr>
<tr>
	<td valign="top" align="right"><br><?php echo $AppUI->_( 'Long description' );?>:</td>
	<td><textarea rows="6" cols="64" name="longdesc"><?php echo $file->longdesc;?></textarea>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'File' );?>:</td>
	<td><input type="file" class="text" name="filename"><a target="_blank" href="files/pipeline/<?=$file->filename?>"><?=$file->filename?></a></td>
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

