<?php /* CONTACTS $Id: do_contact_aed.php,v 1.2 2009-07-17 21:19:12 nnimis Exp $ */
//print_r($_GET);
include ('./functions/delegates_func.php');
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 6;
//$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $user_id );

$project_id = $_POST["project_id"];
$lead_id = $_POST["lead_id"];
$company_id = $_POST["company_id"];
$isNotNew = @$_POST['contact_id'];

$createRelation = (isset($_POST["company_id"]) || isset($_POST["project_id"]) || isset($_POST["lead_id"])) && !$isNotNew;

$obj = new CContact();
$msg = '';

$back = $_POST["origen"];

if (!$obj->bind( $_POST ))
{
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect($back);
}
$obj->contact_creator = $_POST["contact_creator"];

$permisos = "";

if ( $delegator_id != $AppUI->user_id )
{
	require_once( $AppUI->getModuleClass( "admin" ) );
	$usr = new CUser();
	$usr->load( $AppUI->user_id );
	if ( !$usr->isDelegator($delegator_id, $mod_id) && $AppUI->user_type != 1 )
	{
		$AppUI->setMsg( 'Delegator' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$delegador = new CUser();
	$delegador->load( $delegator_id );
	$permisos = $delegador->getDelegatePermission( $AppUI->user_id, $mod_id );
	$canEdit = $canEdit || ( $permisos == "AUTHOR" && ( ( $obj->contact_creator == $AppUI->user_id && $obj->contact_owner == $delegator_id ) || !$obj->contact_id ) );
	$canEdit = $canEdit || ( $permisos == "EDITOR" && ( $obj->contact_owner == $delegator_id || !$obj->contact_id ) );
	$canEdit = $canEdit || $AppUI->user_type == 1;
}


$del = dPgetParam( $_POST, 'del', 0 );

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Contact' );

if ( !$canEdit )
{
	$AppUI->redirect( "m=public&a=access_denied" );
}

if ($del)
{
	$obj->contact_id = $del;
	if($msg = $obj->deleteRelations()){
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}elseif (($msg = $obj->delete()))
	{
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	else
	{
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect( $back );
	}
}
else
{
?>
<script language="javascript">
	confirm("Esta seguro");
</script>
<?php
/*
Estas dos lineas de abajo fueron sacadas x que hacian que por cualquier cambio en el contacto se estan cambiando el owner y el creador del contacto
Y esto no tiene x que ser asi.
Vi vemos que trae problemas x otro lado avisen, pero no lo descomenten asi nomas.
Fravizzini*/
/*
	$obj->contact_owner = $delegator_id;
	$obj->contact_creator = $delegator_id;
*/
	
	if(empty($obj->contact_public) || $obj->contact_public!="1" || $obj->contact_public!=1){
		$obj->contact_public=0;
	}

	$contact_exists = contact_exists($obj->contact_first_name,
										$obj->contact_last_name, $obj->contact_company, $obj->contact_public);
	
	if ($contact_exists!=0 && $contact_exists!=$obj->contact_id)
	{
		$AppUI->setMsg( "ContactExists", UI_MSG_ERROR );
	}
	else
	{
		if(!$isNotNew){
			$obj->contact_owner = $AppUI->user_id;
			$obj->contact_creator = $AppUI->user_id;
			if($delegator_id != $AppUI->user_id) {
				$obj->contact_owner = $delegator_id;
				$obj->contact_creator = $delegator_id;
			}
		}
		if ( ($msg = $obj->store() ) )
		{
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
		}
		else
		{
			if($createRelation){
				if(isset($_POST["company_id"])){
					$relationType = "companies";
					$typeId = $_POST["company_id"];
				}
				if(isset($_POST["project_id"])){
					$relationType = "projects";
					$typeId = $_POST["project_id"];
				}
				if(isset($_POST["lead_id"])){
					$relationType = "leads";
					$typeId = $_POST["lead_id"];
				}
				if ( ($msg = $obj->saveRelation($relationType,$typeId) ) )
				{
					$AppUI->setMsg( $msg, UI_MSG_ERROR );
				}
			}
			if($delegator_id!=$AppUI->user_id && $isNotNew){
				do_log($delegator_id, $mod_id, $AppUI, 5);
			}else if($delegator_id!=$AppUI->user_id){
				do_log($delegator_id, $mod_id, $AppUI, 6);
			}
			
			$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
		}
		
		$AppUI->redirect($back);
	}
	$AppUI->redirect("m=contacts&dialog=&delegator_id=$AppUI->user_id&a=addedit&contact_id=".$_REQUEST['contact_id']);
}

//----------------------------------------------------------------------------------------------------------------------------------

function contact_exists($first_name, $last_name, $company, $public){
    
	$sql = "select contact_id from contacts where
	contact_last_name = '".db_escape($last_name)."' AND contact_first_name = '"
    .db_escape($first_name)."' AND contact_company = '".$company
    ."' AND contact_public  = ".$public;
    
    $result = mysql_query($sql);
    
    if(mysql_num_rows($result) > 0){
	    $row = mysql_fetch_array($result);
	    return $row['contact_id'];
    }else{
    	return 0;
    }
}

?>