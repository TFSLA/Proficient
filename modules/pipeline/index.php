<?
include ('./functions/delegates_func.php');
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id);
//$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $user_id );
$mod_id = 18;

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
	$canAdd = $permisos == "AUTHOR";
	$canAdd = $canAdd || $AppUI->user_type == 1;
	$canEdit = 0;
	do_log($delegator_id, $mod_id, $AppUI, 1);
}
else
{
	if ( !$canRead )
	{
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$canAdd = $canEdit;
}
function cutText($strText, $intChars=30){
    if(strlen($strText) > $intChars){
        $strText=substr($strText,0,$intChars)."...";
    }
    return $strText;
}

?>
<script language="javascript">
function delLead( x, y ) {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Lead');?> " + y + "?" )) {
		document.frmDeleteLead.id.value = x;
		document.frmDeleteLead.submit();
	}
}
</script>

<style type="text/css">
	.delegatorpipeline {color: blue;}
	.delegatorpipeline a:link {color: blue;}
	.delegatorpipeline a:visited {color: blue;}
</style>


<?
$AppUI->savePlace();

if (isset( $_GET['tab'] )) {
    $AppUI->setState( 'PipelineIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'PipelineIdxTab' ) !== NULL ? $AppUI->getState( 'PipelineIdxTab' ) : 0;

// setup the title block
$titleBlock = new CTitleBlock( 'Sales Pipeline', 'pipeline.gif', $m, "$m.$a" );
$strCell="";
if($tab==0){
    if ($canAdd) {
        $strCell='<input type="button" class="buttontitlebig" onmouseout="this.className=\'buttontitlebig\';" onmouseover="this.className=\'buttontitlebigover\';" value="'.$AppUI->_( 'new lead' ).'" onClick="javascript:window.location=\'./index.php?m=pipeline&a=addedit&type=Opportunity&delegator_id='.$delegator_id.'&dialog='.$dialog.'\';">';
    }
}elseif($tab==1){
    if ($canAdd) {
        $strCell='<input type="button" class="buttonbig" onmouseout="this.className=\'buttontitlebig\';" onmouseover="this.className=\'buttontitlebigover\';" value="'.$AppUI->_( 'new lead' ).'" onClick="javascript:window.location=\'./index.php?m=pipeline&a=addedit&type=Win&delegator_id='.$delegator_id.'&dialog='.$dialog.'\';">';
    }
}elseif($tab==2){
    if ($canAdd) {
        $strCell='<input type="button" class="buttonbig" onmouseout="this.className=\'buttontitlebig\';" onmouseover="this.className=\'buttontitlebigover\';" value="'.$AppUI->_( 'new lead' ).'" onClick="javascript:window.location=\'./index.php?m=pipeline&a=addedit&type=Loss&delegator_id='.$delegator_id.'&dialog='.$dialog.'\';">';
    }
}
$titleBlock->addCell($strCell);
$titleBlock->show();
?>

<?php

// tabbed information boxes
$tabBox = new CTabBox( "?m=pipeline&delegator_id=$delegator_id&dialog=$dialog", "{$AppUI->cfg['root_dir']}/modules/pipeline/", $tab );
$tabBox->add( 'vw_forecast', 'Forecast' );
$tabBox->add( 'vw_wins', 'Wins' );
$tabBox->add( 'vw_losses', 'Losses' );
$tabBox->show();
?>
<form name="frmDeleteLead" action="./index.php?m=pipeline&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" method="post">
	<input type="hidden" name="dosql" value="do_lead_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="id" value="0" />
</form>
