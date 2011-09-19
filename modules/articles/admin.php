<?php /* $Id: admin.php,v 1.6 2009-06-19 22:08:45 pkerestezachi Exp $ */
$AppUI->savePlace();

$canEdit = !getDenyEdit($m);

if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}


if (isset( $_GET['tab'] )) {
    $AppUI->setState( 'ArticleIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ArticleIdxTab' ) !== NULL ? $AppUI->getState( 'ArticleIdxTab' ) : 0;

$query = "SELECT * FROM articlesections";
$results = mysql_query($query);
 
if($_GET['addedit']=="cancel")
{
	unset($AppUI->companies_o);	 
    unset($AppUI->companies_d);	 
    unset($AppUI->project_o);	 
    unset($AppUI->project_d);	 
}

// setup the title block
$titleBlock = new CTitleBlock( 'Management', 'article_management.gif', $m, "colaboration.index" );


	 if($_POST[articlesection_id]!="")
	 {   
	 $section_id = $_POST[articlesection_id];
	 }
	 else
	 {
	 	 if($_POST[id] != "")
	 	 {
	 	 	$section_id = $_POST[id];
	 	 }else {
		     if($_GET[id]!="")
			 {
	          $section_id = $_GET[id];
			 }else{
			  $section_id = '-1';
			 }
	 	 }
	 }
	 
	 if($_POST[articletype_id] != "")
	 {
	 	$articletype_id = $_POST[articletype_id];
	 }else{
	 	$articletype_id = $_GET[type];
	 }
	
	 
 		$titleBlock->addCell(
		'<input type="text" class="text" name="txt_search" value="'.$_POST[txt_search].'">&nbsp;<input type="submit" class="button" value="'.$AppUI->_('Search').'" >', '',
		'<form action="index.php?m=articles&a=admin" method="post">', '</form>'
	);
    $strCell .= '&nbsp;&nbsp;<input type="button" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new article').'" onClick="javascript:window.location=\'./index.php?m=articles&a=addeditarticle&sec_id='.$section_id.'\';" />';
//}else{
    $strCell .= '&nbsp;<input type="button" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new section').'" onClick="javascript:window.location=\'./index.php?m=articles&a=addeditsection\';" />';
//}
    $titleBlock->addCell($strCell);
   
    $titleBlock->addCrumb('?m=articles&tab=0&type='.$articletype_id.'&id='.$section_id, $AppUI->_('View list'), '');
	
    $titleBlock->addCrumb('?m=articles&type='.$articletype_id.'&a=addeditsection', $AppUI->_('add section'), '');

$titleBlock->show();

?>
<script language="javascript">
function delArticle( x, y ) {
	if (confirm1( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Article');?> " + y + "?" )) {
		document.frmDelete.article_id.value = x;
		document.frmDelete.submit();
	}
}
function delSection( x, y ) {
	if (confirm1( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Section');?> " + y + "?" )) {
		document.frmDeleteSection.articlesection_id.value = x;
		document.frmDeleteSection.submit();
	}
}

function submitIt(x){
	document.pickSection.section_id.value = x.value;
	document.pickSection.submit();
}
</script>

<?php


// tabbed information boxes
$tabBox = new CTabBox( "?m=articles&a=admin", "{$AppUI->cfg['root_dir']}/modules/articles/", $tab );
//$tabBox->add( 'vw_articles', 'Articles' );
$tabBox->add( 'vw_sections', 'Sections' );
$tabBox->show( $extra );
?>

<form name="frmDelete" action="./index.php?m=articles&a=admin" method="post">
	<input type="hidden" name="dosql" value="do_article_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="article_id" value="0" />
</form>

<form name="frmDeleteSection" action="./index.php?m=articles&a=admin" method="post">
	<input type="hidden" name="dosql" value="do_section_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="articlesection_id" value="0" />
</form>

<form action="./index.php?m=articles&a=admin" method="post" name="pickSection">
   <input type="hidden" name="section_id" value="">
</form>
