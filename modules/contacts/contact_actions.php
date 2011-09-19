<?php /* CONTACTS $Id: contact_actions.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 6;
$strContactActions = dPgetParam( $_POST, 'ContactListAction', "" );
$strContactList = dPgetParam( $_POST, 'ContactList', "" );
var_dump($strContactActions);
if($strContactActions == "" || $strContactList == ""){
	$AppUI->setMsg( "", UI_MSG_ERROR );
	$AppUI->redirect();
}

$arContactList = explode(",", $strContactList);

if($strContactActions == "del"){
	foreach ($arContactList as $idContact){
		$strSql = "DELETE FROM contacts WHERE contact_id='$idContact'";
		$resultTMP = db_exec($strSql);
	}
}

$error = FALSE;

if($strContactActions == "public"){
	foreach ($arContactList as $idContact){
		
		$Sql = "SELECT contact_first_name, contact_last_name, contact_company FROM contacts WHERE contact_id='$idContact'";
		db_loadHash($Sql, $data_contact);
		
		//Me fijo si existe el mismo contacto como PUBLICO
		$Sql = "SELECT COUNT(*) FROM contacts WHERE 
		contact_first_name='".$data_contact['contact_first_name']."' AND contact_last_name='".$data_contact['contact_last_name']."' AND contact_company='".$data_contact['contact_company']."' AND contact_public=1 AND contact_id!='$idContact'";
		
		if ( db_loadResult($Sql) == 0 ){
			$strSql = "UPDATE contacts SET contact_public = 1 WHERE contact_id='$idContact'";
			$resultTMP = db_exec($strSql);
		}
		else{
			//Si existe le aviso
			$error=true;//Marco que hay un error pero continuo con los demas
			
			//$contactsError .= "<br> * " .$data_contact['contact_last_name'].", ".$data_contact['contact_first_name'];
			//$AppUI->setMsg(  $AppUI->_('updatePublic').$data_contact['contact_last_name'].", ".$data_contact['contact_first_name'],UI_MSG_ALERT );
			//$AppUI->redirect();			
		}
	}
}

if ($error==TRUE )//Si hubo algun error:
{
	$AppUI->setMsg($AppUI->_('updateError').$AppUI->_('updatePublic'),UI_MSG_ALERT );
	$AppUI->redirect();
}


if($strContactActions == "private"){
	foreach ($arContactList as $idContact){

		$Sql = "SELECT contact_first_name, contact_last_name, contact_company, contact_owner FROM contacts WHERE contact_id='$idContact'";
		db_loadHash($Sql, $data_contact);
				
		/*Para pasar un contacto de privado a publico tengo que validar que el usuario que esta realizando la accion se el dueño
		o sea ADMIN*/
		if( !($data_contact['contact_owner']==$AppUI->user_id OR $AppUI->user_type==1 ) )
		{
			$error="ContactNotOwner";//Marco que hay un error pero continuo con los demas
			//$AppUI->setMsg( $AppUI->_('ContactNotOwner'). $data_contact['contact_last_name'].", ".$data_contact['contact_first_name'],UI_MSG_ALERT );
			//$AppUI->redirect();
		}
		else
		{
			//Me fijo si ya existe el mismo contacto como PRIVADO
			$Sql = "SELECT COUNT(*) FROM contacts WHERE 
			contact_first_name='".$data_contact['contact_first_name']."' AND contact_last_name='".$data_contact['contact_last_name']."' AND contact_company='".$data_contact['contact_company']."' AND contact_public=0 AND contact_id!='$idContact' AND contact_owner=".$AppUI->user_id;
	
			if ( db_loadResult($Sql) != 0 ){
				//Si existe le aviso
				$error="updatePrivate";//Marco que hay un error pero continuo con los demas
				//$AppUI->setMsg(  $AppUI->_('updatePrivate'). $data_contact['contact_last_name'].", ".$data_contact['contact_first_name'],UI_MSG_ALERT );
				//$AppUI->redirect();
			}
			else
			{
				$strSql = "UPDATE contacts SET contact_public = 0 WHERE contact_id='$idContact'";
				$resultTMP = db_exec($strSql);				
			}
		}
	}
}

if ($error=="updatePrivate" )//Si ya enia estado privado
{
	$AppUI->setMsg($AppUI->_('updateError').$AppUI->_("updatePrivate"),UI_MSG_ALERT );
	$AppUI->redirect();
}
if ($error=="ContactNotOwner" )//Si no es el dueño del contacto:
{
	$AppUI->setMsg($AppUI->_('updateError').$AppUI->_("ContactNotOwner"),UI_MSG_ALERT );
	$AppUI->redirect();
}

$AppUI->setMsg( $strContactActions != "del" ? "updated" : "deleted", UI_MSG_OK );
$AppUI->redirect();

?>