<?php
$AppUI->savePlace();

$contact_id = dPgetParam( $_GET, 'contact_id', 0 );
$user_id = dPgetParam( $_GET, "user_id", $AppUI->user_id );
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 6;
$origen = $_POST['origen'];

if(isset($_GET['lead_id'])){
	$origen = "m=pipeline&a=addedit&lead_id=".$_GET['lead_id']."&delegator_id=$delegator_id&dialog=";
}
if(isset($_GET['company_id'])){
	
}
if(isset($_GET['project_id'])){
	
}

if (isset($b) && empty($origen))
{
 $origen = "m=companies&a=contacts&dialog=1&suppressLogo=1&callback=setContact&table=contacts";
}
elseif(empty($origen))
{
 $origen = "m=contacts&delegator_id=$delegator_id";
}

// load the record data
$msg = '';
$row = new CContact();
$canDelete = $row->canDelete( $msg, $contact_id );

if ( !$row->load( $contact_id ) && $contact_id > 0)  
{
	$AppUI->setMsg( 'Contact' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

if ( $user_id != $AppUI->user_id )
{
	require_once( $AppUI->getModuleClass( "admin" ) );
	$usr = new CUser();
	$usr->load( $AppUI->user_id );

	//Hay que chequear que este sea un delegador valido	
	if ( !$usr->isDelegator($user_id, $mod_id) && $AppUI->user_type != 1 )
	{
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$delegador = new CUser();
	$delegador->load( $user_id );
	$permisos = $delegador->getDelegatePermission( $AppUI->user_id, $mod_id );
	if ( $contact_id )
	{
		$canEdit = $permisos == "AUTHOR" && $row->contact_creator == $AppUI->user_id && $row->contact_owner == $user_id;
		$canEdit = $canEdit || ($permisos == "EDITOR" && $row->contact_owner == $user_id);
		$canEdit = $canEdit || $AppUI->user_type == 1;
	}
	else
	{
		$canEdit = 1;
	}
}
else
{
	if (getDenyRead( 'contacts' )) 
	{
		$AppUI->redirect( "m=public&a=access_denied" );
	}	

	// check permissions for this record
	$canEdit = !getDenyEdit( $m, $contact_id );
}

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ContactVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ContactVwTab' ) !== NULL ? $AppUI->getState( 'ContactVwTab' ) : 0;

// setup the title block
if($_GET['a'] == 'viewcontact')
	$ttl = "View Contact";
else 
	$ttl = $contact_id > 0 ? "Edit Contact" : "Add Contact";
	
$titleBlock = new CTitleBlock( $ttl, 'contacts.gif', $m, "colaboration.index" );

$pos1 = strpos($_SERVER[HTTP_REFERER],'=');
$pos2 = strpos ($_SERVER[HTTP_REFERER],'&');

$cant = $pos2 - $pos1;

$origen_tmp = substr ($_SERVER[HTTP_REFERER], $pos1, $cant);

if($origen_tmp=="=companies" || isset($_GET["company_id"]))
{
	$titleBlock->addCrumb( "?m=companies&a=view&company_id=$_GET[company_id]", "contacts list" );
}
else
{
	$titleBlock->addCrumb( "?m=contacts&delegator_id=$delegator_id&dialog=$dialog", "contacts list" );
}

include_once('./modules/public/itemToFavorite_functions.php');
$deleteFavorite = HasItemInFavorites($contact_id, 9);

$titleBlock->addCrumb( "javascript:itemToFavorite(".$contact_id.", 9, $deleteFavorite);", $deleteFavorite == 1 ? $AppUI->_('Remove from favorites') : $AppUI->_('Add to favorites') );

if ( $contact_id && $canEdit ) 
{
	$titleBlock->addCrumbDelete( 'delete contact', $canDelete, $msg );
	if($_GET["a"] != "addedit")
		$titleBlock->addCrumb( "?m=contacts&a=addedit&contact_id=$contact_id&delegator_id=$delegator_id&company_id=$_GET[company_id]", $AppUI->_("Edit Contact") );
}

if($_GET["a"] == "addedit")
	$titleBlock->addCrumb( "?m=contacts&a=viewcontact&contact_id=$contact_id&delegator_id=$delegator_id&company_id=$_GET[company_id]", $AppUI->_("View Contact") );

$titleBlock->show();

if(empty($ro))
	$file = "addedit";
else
	$file = "viewcontact";

$tabBox = new CTabBox( "?m=contacts&a=$file&contact_id=$contact_id&delegator_id=$delegator_id", "", $tab );

$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/contacts/addedit_contact", 'Personal');

if(empty($_REQUEST['hideTabs'])){
	if (!getDenyRead( 'projects' ))
		$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/contacts/contact_projects", 'Projects');
	
	if (!getDenyRead( 'companies' ))
		$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/contacts/contact_companies", 'Companies');
		
	if (!getDenyRead( 'pipeline' ))
		$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/contacts/contact_pipeline", 'Pipeline');
		
	if (!getDenyRead( 'calendar' ))
		$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/contacts/contact_events", 'Events');
}
$tabBox->show();
?>
<script language="javascript">
function delIt(){
	var form = document.delContact;
	if(confirm1( "<?php echo $AppUI->_('contactsDelete');?>" )) {
		form.del.value = "<?php echo $contact_id;?>";
		form.submit();
	}
}

function itemToFavorite(item_id, item_type, item_delete)
{
	window.top.location = "./index.php?m=public&a=itemToFavorite&item_id=" + item_id + "&item_type=" + item_type + "&item_mode_del=" + item_delete + "&dialog=1&suppressLogo=1";
}
</script>

<form name="delContact" method="POST" action="">
<input type="hidden" name="dosql" value="do_contact_aed">
<input type="hidden" name="del" value="0" />
	<input type="hidden" name="contact_project" value="0" />
	<input type="hidden" name="origen" value="<?php echo $origen;?>" />
	<input type="hidden" name="contact_unique_update" value="<?php echo uniqid("");?>" />
	<input type="hidden" name="contact_id" value="<?php echo $contact_id;?>" />
	<input type="hidden" name="contact_owner" value="<?php echo ($row->contact_owner) ? $row->contact_owner : $user_id;?>" />
	<input type="hidden" name="contact_company_ch" value="0" />
	<input type="hidden" name="contact_creator" value="<?php echo $row->contact_creator ? $row->contact_creator : $AppUI->user_id;?>" />
</form>