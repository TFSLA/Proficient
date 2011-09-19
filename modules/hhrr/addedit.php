<?php /* DEPARTMENTS $Id: addedit.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
// Add / Edit Company
global $AppUI, $hhrr_portal;
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$candidate = isset($_GET['candidate']) ? $_GET['candidate'] : 0;

if($id =="")
{
$id = '0';
}

$MaritalStates= dPgetSysVal("MaritalState");
$IMTypes= dPgetSysVal("IMType");
$WorkTypes= dPgetSysVal("WorkType");
$SCandidateStatus = dPgetSysVal("CandidateStatus");

$uploads_dir = $AppUI->getConfig('hhrr_uploads_dir');
$extfiles_cv = $AppUI->getConfig('hhrr_cv_extensions');
$extfiles_pic = $AppUI->getConfig('hhrr_pic_extensions');

$canAdd = CHhrr::canAdd();
$canEdit = CHhrr::canEdit($id);

if ($AppUI->user_id == $_GET['id'])
{
$canEdit = '1';
}

if ($id == 0)
	$canEdit = $canAdd;

// check permissions
if (!$canEdit && !$hhrr_portal ){
   $AppUI->redirect( "m=public&a=access_denied" );
}

$canEditModule = !getDenyEdit( "hhrr" );

if ($AppUI->user_id == $_GET['id'])
{
$canEditModule = '1';
}


$result = mysql_query("select * from users where user_id = '$id';");
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      $firstname = $row["user_first_name"];
      $lastname = $row["user_last_name"];
      $user_type = $row["user_type"];

	$ttl_data = $firstname." ".$lastname;

// setup the title block
	$ttl = $id > 0 ? $AppUI->_('Edit HHRR')." - $ttl_data" : $AppUI->_('Add HHRR');
	$titleBlock = new CTitleBlock( $ttl, 'hhrr.gif', $m, 'hhrr.index' );
	if ($canAdd) {
		$titleBlock->addCell();

		if($user_type != '5'){
		$titleBlock->addCell(
		'<input type="submit" class="button" value="'.strtolower($AppUI->_('Add HHRR')).'">', '',
		'<form action="?m=hhrr&a=addedit&tab=1" method="post">', '</form>'
		);
		}else{
		$titleBlock->addCell(
		'<input type="button" class=button value="'.$AppUI->_( 'New Candidate' ).'" onClick="javascript:window.location=\'./index.php?m=hhrr&a=addedit&candidate=1&tab=1\';">');
		}
	}	
  
  if($id!=$AppUI->user_id){
  $titleBlock->addCrumb( "?m=hhrr&tab=1", strtolower($AppUI->_('Resources list')) );
  $titleBlock->addCrumb( "?m=hhrr&tab=3", strtolower($AppUI->_('Candidates list')) ); 
  $titleBlock->addCrumb( "?m=hhrr&tab=0", strtolower($AppUI->_('Graphical View')) );
  }

  //if ($canEdit && $id>0) $titleBlock->addCrumb( "?m=hhrr&a=viewskills&id=$id", strtolower($AppUI->_('View Skills')) );
  if ($canEdit && $id >0){
	  if($user_type != '5'){

	  	if( $AppUI->user_type == '1' OR $id==$AppUI->user_id OR !getDenyRead('admin'))
			$titleBlock->addCrumb( "?m=system&a=addeditpref&user_id=$id", "edit preferences" );
			
		$titleBlock->addCrumb( "?m=admin&a=addedituser&user_id=".$id, "edit personal information" );
		
		$titleBlock->addCrumb( "?m=admin&a=calendars&user_id=".$id, "work calendar" );
		
		if($id == $AppUI->user_id)
			$titleBlock->addCrumb( "javascript: popChgPwd();", "change password" );

	  $titleBlock->addCrumb( "?m=hhrr&a=viewhhrr&id=$id", strtolower($AppUI->_('view hhrr information')) );	
	  }else{
	  $titleBlock->addCrumb( "?m=hhrr&a=viewhhrr&id=$id", strtolower($AppUI->_('View candidate')) );
	  }
  }
  //if ($canEdit && $id>0) $titleBlock->addCrumb( "?m=hhrr&a=addedituserskills&id=$id", strtolower($AppUI->_('Edit User Skills')) );  	
	if ($canEdit && $id>0 && $id!=$AppUI->user_id) $titleBlock->addCrumbDelete( 'Delete HHRR', $canDelete, $msg );
		
if (!(isset($hhrr_portal) && @$hhrr_portal == true))	
	$titleBlock->show();


?>


<script language="javascript">
	function delIt() {
		if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('HHRR')." ". $firstname." ".$lastname;?> ?" )) {
			document.frmDeleteHr.submit();
		}
	}

</script>

<form name="frmDeleteHr" action="./index.php?m=hhrr" method="post">
	<input type="hidden" name="dosql" value="do_hhrr_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="user_id" value="<?=$id?>" />
</form>

<?

$AppUI->savePlace();

if (isset( $_GET['tab'] )) {
    $AppUI->setState( 'RrhhviewIdxTab', $_GET['tab'] );
}else{
	$tab = $AppUI->getState( 'RrhhviewIdxTab' ) !== NULL ? $AppUI->getState( 'RrhhviewIdxTab' ) : 0;
	$_GET['tab'] = $tab;
}

if(!$hhrr_portal)
{
  if ($id == '0'){
  	 $tab = "1";
  }
}

// tabbed information boxes
if(!$hhrr_portal){
	$tabBox = new CTabBox( "?m=hhrr&a=addedit&id=$id", "{$AppUI->cfg['root_dir']}/modules/hhrr/", $tab );
}else{
	$tabBox = new CTabBox( "hhrr/index.php?a=personalinfo&id=$id", "{$AppUI->cfg['root_dir']}/modules/hhrr/", $tab );
}

//NO BORRAR
if(!$hhrr_portal)
{
  $tabBox->add( 'viewhhrr_summary', 'Summary', TRUE );
  //$tabBox->add( 'index', '', FALSE);//Este tab que se agrega oculto es para que tengan correspondencia las solapas de edicion de las de vista
}

if($id!=$AppUI->user_id){

	if($candidate){
    $tabBox->add( 'addedit_candidate', 'Personal data', TRUE );
	}else
	{
		if (validar_permisos_hhrr($id,'personal',-1))
			$tabBox->add( 'addedit_personal', 'Personal data', TRUE );
		else
			$tabBox->add( 'addedit_personal', 'Personal data', FALSE );
	}
}
else
{    
	 if(!$hhrr_portal)
	 {  

	 	if (validar_permisos_hhrr($id,'personal',-1))
	 		$tabBox->add( 'addedit_personal', 'Personal data',TRUE );
		else
	 		$tabBox->add( 'addedit_personal', 'Personal data',FALSE );
	 }
	 else
	 {
	 $tabBox->add( 'addedit_candidate', 'Personal data',TRUE );
	 }
}


if($id > '0')
{   
	if (!$hhrr_portal){
		if ( validar_permisos_hhrr($id,'work_experience',-1) )
			$tabBox->add( 'addedit_ant', 'Work Experience', TRUE );
		else
			$tabBox->add( 'addedit_ant', 'Work Experience', FALSE );
		
		if ( validar_permisos_hhrr($id,'education',-1) )
			$tabBox->add( 'addedit_for', 'Education', TRUE );
		else
			$tabBox->add( 'addedit_for', 'Education', FALSE );

		if ( $id != $AppUI->user_id && validar_permisos_hhrr($id,'performance_management',-1)) 
			$tabBox->add( 'addedit_eyr', 'Performance Management', TRUE );
		else
			$tabBox->add( 'addedit_eyr', 'Performance Management', FALSE );
		
		if (validar_permisos_hhrr($id,'compensations',-1)) 
			$tabBox->add( 'addedit_comp', 'compensations', TRUE );
		else
			$tabBox->add( 'addedit_comp', 'compensations', FALSE );
			
		if ( validar_permisos_hhrr($id,'development',-1))
			$tabBox->add( 'addedit_dev', 'Development', TRUE );
		else
			$tabBox->add( 'addedit_dev', 'Development', FALSE );

		if ( validar_permisos_hhrr($id,'matrix',-1) )
			$tabBox->add( 'addedituserskills', 'Matrix', TRUE );
		else
			$tabBox->add( 'addedituserskills', 'Matrix', FALSE );			
	}else{
		$tabBox->add( 'addedit_ant', 'Work Experience', TRUE );
		$tabBox->add( 'addedit_for', 'Education', TRUE );
		$tabBox->add( 'addedituserskills', 'Matrix', TRUE );		
	}


}

$tabBox->show(  );

?>
