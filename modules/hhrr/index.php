<script language="javascript">
function delSkillcategory( x, y ) {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Skill Category');?> " + y + "?" )) {
		document.frmDeleteSkillcategory.id.value = x;
		document.frmDeleteSkillcategory.submit();
	}
}
function delSkill( x, y ) {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Skill');?> " + y + "?" )) {
		document.frmDeleteSkill.id.value = x;
		document.frmDeleteSkill.submit();
	}
}
function delHhrr( x, y ) {
	if (confirm(acentos( "<?php echo $AppUI->_('doDeletehhrr');?> " + y + "?" ))) {
		document.frmDeleteHhrr.user_id.value = x;
		document.frmDeleteHhrr.submit();
	}
}
</script>
<form name="frmDeleteSkillcategory" action="./index.php?m=hhrr" method="post">
	<input type="hidden" name="dosql" value="do_skillcategory_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="id" value="0" />
</form>
<form name="frmDeleteSkill" action="./index.php?m=hhrr" method="post">
	<input type="hidden" name="dosql" value="do_skill_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="id" value="0" />
</form>
<form name="frmDeleteHhrr" action="./index.php?m=hhrr" method="post">
	<input type="hidden" name="dosql" value="do_hhrr_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="user_id" value="0" />
</form>

<?
global $evaluation;

if (getDenyRead( $m ))
	$AppUI->redirect( "m=public&a=access_denied" );

$AppUI->savePlace();

if (isset( $_GET['tab'] )) {
    $AppUI->setState( 'RrhhIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'RrhhIdxTab' ) !== NULL ? $AppUI->getState( 'RrhhIdxTab' ) : 0;

// setup the title block
$titleBlock = new CTitleBlock( 'Human Resources Management', 'hhrr.gif', $m, "hhrr.index" );

$strCell="";
if($tab==1){
    //hhrr -> muestra el input de busqueda
    $strnombre = dPgetParam( $_GET, "where", "" );
    $strTableTmp = '<table><tr><form name="frmSearchFor" action="" method="post"><td>'.$AppUI->_('Search for').'</td>';
    $strTableTmp .='<td><input type="text" name="where" class="text" size="10" value="'.$strnombre.'" /></td>';
    $strTableTmp .='<td><input type="button" value=">" class="button" onclick="javascript:window.location=\'index.php?m=hhrr&where=\'+document.frmSearchFor.where.value" /></td>';
    $strTableTmp .='</form></tr></table>';
    $titleBlock->addCell($strTableTmp);
     //boton new hhrr
     if($canEdit){
        $strCell='<input type="button" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.strtolower($AppUI->_( 'new hhrr' )).'" onClick="javascript:window.location=\'./index.php?m=hhrr&a=addedit&tab=1\';">';
     }
}
elseif($tab==2){
    //boton new job
    if(!getDenyEdit( "hhrr" )){
		$strCell = '<input type="button" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.strtolower($AppUI->_( 'new job' )).'" onClick="javascript:window.location=\'./index.php?m=hhrr&a=addeditrole\';">';
    }
}elseif($tab==3){
    //hhrr -> muestra el input de busqueda
    $strnombre = dPgetParam( $_GET, "where", "" );
    $strTableTmp = '<table><tr><form name="frmSearchFor" action="" method="post"><td>'.$AppUI->_('Search for').'</td>';
    $strTableTmp .='<td><input type="text" name="where" class="text" size="10" value="'.$strnombre.'" /></td>';
    $strTableTmp .='<td><input type="button" value=">" class="button" onclick="javascript:window.location=\'index.php?m=hhrr&where=\'+document.frmSearchFor.where.value" /></td>';
    $strTableTmp .='</form></tr></table>';
    $titleBlock->addCell($strTableTmp);
     //boton new hhrr
     if($canEdit){
        $strCell='<input type="button" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.strtolower($AppUI->_( 'New Candidate' )).'" onClick="javascript:window.location=\'./index.php?m=hhrr&a=addedit&candidate=1&tab=1\';">';
     }
}elseif($tab==4){
    //boton new skill category
    if($canEdit){
        $strCell = '<input type="button" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_( 'new skill category' ).'" onClick="javascript:window.location=\'./index.php?m=hhrr&a=addeditskillcategory\';">';
        $strCell .= '&nbsp;&nbsp;<input type="button" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_( 'new skill' ).'" onClick="javascript:window.location=\'./index.php?m=hhrr&a=addeditskill&cat='.$_GET[cat].'\';">';
    }
}elseif($tab==5){
    //boton new skill
    if($canEdit){
		$strCell = '<input type="button" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.strtolower($AppUI->_( 'new skill category' )).'" onClick="javascript:window.location=\'./index.php?m=hhrr&a=addeditskillcategory\';">';
        $strCell .= '&nbsp;&nbsp;<input type="button" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.strtolower($AppUI->_( 'new skill' )).'" onClick="javascript:window.location=\'./index.php?m=hhrr&a=addeditskill&cat='.$_GET[cat].'\';">';
    }
}

$titleBlock->addCell($strCell);
$titleBlock->show();

// tabbed information boxes
$tabBox = new CTabBox( "?m=hhrr", "{$AppUI->cfg['root_dir']}/modules/hhrr/", $tab );
$tabBox->add( 'vw_graphical', 'Graphical View', TRUE );
$tabBox->add( 'vw_hhrr', 'Human Resources', TRUE );
$tabBox->add( 'vw_graphical', 'Jobs', TRUE );
$tabBox->add( 'vw_candidate', 'Candidates', TRUE );
$tabBox->add( 'vw_skill_categories', 'Skill Categories', TRUE );
$tabBox->add( 'vw_skills', 'Skills items', TRUE );
$tabBox->add( 'vw_search', 'Search', TRUE );
$tabBox->show( );
?>