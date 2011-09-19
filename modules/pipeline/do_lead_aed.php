<?php /* DEPARTMENTS $Id: do_lead_aed.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
include ($AppUI->getFunctionFile('delegates'));
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
@include_once( "leads.class.php" );
$del = isset($_POST['del']) ? $_POST['del'] : 0;
$lead = new CLead();
$mod_id = 18;

if (($msg = $lead->bind( $_POST ))) 
{
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}
if ( $delegator_id != $AppUI->user_id )
{
	//echo "Soy delegado";
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
	if ( $del )
	{
		$lead->load( $_POST["id"] );
	}
	$canEdit = $canEdit || ( $permisos == "AUTHOR" && $lead->lead_owner == $delegator_id && $lead->lead_creator == $AppUI->user_id );
	$canEdit = $canEdit || ( $permisos == "EDITOR" && $lead->lead_owner == $delegator_id);
	echo "<p>mis permisos son de $permisos, el lead_owner es '$lead->lead_owner'</p>";
	$canEdit = $canEdit || $AppUI->user_type == 1;
	if ($del!=0)do_log($delegator_id, $mod_id, $AppUI, 2);
}

//viene de email publishing
if($code)
{
	$sql = "  SELECT id FROM salespipeline";
	$sql .= " WHERE accountname = '".$_POST['accountname']."'";
	$sql .= " LIMIT 0,1";

	if(db_loadHash( $sql, $opportunityExist))
	{
		$msgBody = "Nombre de oportunidad \"".$_POST['accountname']."\" duplicado. Por favor, modifique el asunto y vuelva a enviar el mensaje.";
		require_once( $AppUI->getModuleClass( "admin" ) );
		mail(CUser::getUserEmail($AppUI->user_id), 'Error en carga de Oportunidad | '.$_POST['accountname'], $msgBody, 'From: '.$AppUI->getConfig("mailfrom"));
		exit();
	}	
}

if ( !$canEdit )
{
	/*echo "No puedo editar";
	exit;*/
	$AppUI->redirect( "m=public&a=access_denied" );
}
// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Lead' );
if ($del) {
	if (($msg = $lead->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
	}
	
	$AppUI->redirect('m=pipeline&delegator_id='.$delegator_id);
	
} else {

	if(!$_POST["id"])
	{
		$dateTemp = new CDate();
		$lead->opportunitycode = $dateTemp->format( "%Y%m%d%H%M" );
	}

	if (($msg = $lead->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['id'];
		if($isNotNew){
			$action = 4;
		}else{
			$action = 3;
		}
		do_log($delegator_id, $mod_id, $AppUI, $action);
		$AppUI->setMsg( $isNotNew ? 'updated' : 'inserted', UI_MSG_OK, true );
	}
	
	//No viene de email publishing
	if(!$code)
	{
		$AppUI->redirect('m=pipeline&a=view&lead_id='.(@$_POST['id'] > 0 ? @$_POST['id'] : $lead->id).'&delegator_id='.$delegator_id);	
}
	else
	{
		$lead->load( $lead->id );
		$msgBody = "El código generado para la nueva oportunidad es: ".$lead->opportunitycode;
		require_once( $AppUI->getModuleClass( "admin" ) );
		mail(CUser::getUserEmail($AppUI->user_id), 'Código Nueva Oportunidad | '.$_POST['accountname'], $msgBody, 'From: '.$AppUI->getConfig("mailfrom"));

		echo($lead->id);
	}
}
?>