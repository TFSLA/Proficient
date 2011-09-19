<?php
global $AppUI, $canEdit;
$id = isset($_GET['id']) ? $_GET['id'] : 0;

$maritalstates= dPgetSysVal("MaritalState");
$IMTypes= dPgetSysVal("IMType");
$SCandidateStatus = dPgetSysVal("CandidateStatus");

$uploads_dir = $AppUI->getConfig('hhrr_uploads_dir');

$canAddHHRR = CHhrr::canAdd();
$canEditHHRR = CHhrr::canEdit($id);

$canEditModule = !getDenyEdit( "hhrr" );
	
      $result = mysql_query("select * from users where user_id = $id;");
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      $id = $row["user_id"];
      $firstname = $row["user_first_name"];
      $lastname = $row["user_last_name"];
	  $user_type = $row["user_type"];

      
	$ttl = $firstname." ".$lastname;
	$titleBlock = new CTitleBlock( $ttl, 'hhrr.gif', $m, 'hhrr.index' );

	
	if ($canAddHHRR){
		$titleBlock->addCell();

		$button = '<table height="1" border="0"><form action="?m=hhrr&a=addedit&tab=1" method="post">
         <tr>
    	  <td>';
  
		$button .= '<input type="submit" class="button" value="'.strtolower($AppUI->_('Add HHRR')).'">';
        $button .= '</td></form>';
		$button .= '</td></tr></table>';

        if($user_type != '5'){
		$titleBlock->addCell( $button, '','', '');
		}else{
		$titleBlock->addCell(
		'<input type="button" class=button value="'.$AppUI->_( 'New Candidate' ).'" onClick="javascript:window.location=\'./index.php?m=hhrr&a=addedit&candidate=1&tab=1\';">');
		}

	}	
	
	if($id!=$AppUI->user_id){
	$titleBlock->addCrumb( "?m=hhrr&tab=1", strtolower($AppUI->_('Resources list')) );
	$titleBlock->addCrumb( "?m=hhrr&tab=3", strtolower($AppUI->_('Candidates list')) );
	$titleBlock->addCrumb( "?m=hhrr&tab=2", strtolower($AppUI->_('Jobs List')) );
	$titleBlock->addCrumb( "?m=hhrr&tab=0", strtolower($AppUI->_('Graphical View')) );
	}
	
  	if ($canEditHHRR || $id == $AppUI->user_id)
  	{
			if($user_type != '5'){
				$canEditOneTab  = CHhrr::canEditOneTab($id);

				if ($canEditOneTab || $id == $AppUI->user_id)
					$titleBlock->addCrumb( "?m=hhrr&a=addedit&id=$id", strtolower($AppUI->_('Edit HHRR')) );
			}
			else{
	    	$titleBlock->addCrumb( "?m=hhrr&a=addedit&id=$id", strtolower($AppUI->_('Edit candidate')) );
	    }

  		if ($canEditHHRR && $id != $AppUI->user_id)
  			$titleBlock->addCrumbDelete( 'Delete HHRR', $canDelete, $msg );
  	}
  	
  	if($canEditHHRR){
  		$titleBlock->addCrumb( "?m=hhrr&a=compare&id=$id", strtolower($AppUI->_('Compare with a job')) );
  	}
    
  	include_once('./modules/public/itemToFavorite_functions.php');
	$deleteFavorite = HasItemInFavorites($id, 10);
	
	$titleBlock->addCrumb( "javascript:itemToFavorite(".$id.", 10, $deleteFavorite);", $deleteFavorite == 1 ? $AppUI->_('remove from favorites') : $AppUI->_('add to favorites') );
  	
	$titleBlock->show();


if($id !='0')
{
	$result = mysql_query("select * from users where user_id = '$id';");
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    $firstname = $row["user_first_name"];
    $lastname = $row["user_last_name"];

}

?>


<script language="javascript">
	function itemToFavorite(item_id, item_type, item_delete)
	{
		window.top.location = "./index.php?m=public&a=itemToFavorite&item_id=" + item_id + "&item_type=" + item_type + "&item_mode_del=" + item_delete + "&dialog=1&suppressLogo=1";
	}

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

// tabbed information boxes
$tabBox = new CTabBox( "?m=hhrr&a=viewhhrr&id=$id", "./modules/hhrr/", $tab );

$tabBox->add( 'viewhhrr_summary', 'Summary', TRUE );
if (validar_permisos_hhrr($id,'personal',1))
	$tabBox->add( 'viewhhrr_personal', 'Personal data', TRUE );
else
	$tabBox->add( 'viewhhrr_personal', 'Personal data', FALSE );

if ( validar_permisos_hhrr($id,'work_experience',1) )
	$tabBox->add( 'viewhhrr_ant', 'Work Experience', TRUE  );
else
	$tabBox->add( 'viewhhrr_ant', 'Work Experience', FALSE  );

if ( validar_permisos_hhrr($id,'education',1) )
	$tabBox->add( 'viewhhrr_for', 'Education', TRUE );
else
	$tabBox->add( 'viewhhrr_for', 'Education', FALSE );

if ( $canEditHHRR && $id != $AppUI->user_id && validar_permisos_hhrr($id,'performance_management',1) )
	$tabBox->add( 'viewhhrr_eyr', 'Performance Management', TRUE );
else
	$tabBox->add( 'viewhhrr_eyr', 'Performance Management', FALSE );

if ( validar_permisos_hhrr($id,'compensations',1) )
	$tabBox->add( 'viewhhrr_comp', 'compensations', TRUE );
else
	$tabBox->add( 'viewhhrr_comp', 'compensations', FALSE );
	
if ( validar_permisos_hhrr($id,'development',1) )
	$tabBox->add( 'viewhhrr_dev', 'Development', TRUE );
else
	$tabBox->add( 'viewhhrr_dev', 'Development', FALSE );

if ( validar_permisos_hhrr($id,'matrix',1) )
	$tabBox->add( 'viewskills', 'Matrix', TRUE );
else
	$tabBox->add( 'viewskills', 'Matrix', FALSE );

$tabBox->show(  );

?>