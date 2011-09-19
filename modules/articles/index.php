<?php /* $Id: index.php,v 1.6 2009-06-19 18:33:55 pkerestezachi Exp $ */

if (getDenyRead( $m ))
	$AppUI->redirect( "m=public&a=access_denied" );

$AppUI->savePlace();
$xajax->printJavascript('./includes/xajax/');
if (isset( $_GET['tab'] )) {
    $AppUI->setState( 'ArticleIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ArticleIdxTab' ) !== NULL ? $AppUI->getState( 'ArticleIdxTab' ) : 0;



// setup the title block
$titleBlock = new CTitleBlock( 'Knowledge Base', 'article_management.gif', $m, "colaboration.index" );

$titleBlock->addCell(
		'<input type="text" class="text" name="txt_search" value="'.$_POST[txt_search].'">&nbsp;<input type="submit" class="button" value="'.$AppUI->_('search').'" >', '',
		'<form action="index.php?m=articles" method="post">', '</form>'
	);

$canEdit = !getDenyEdit( $m  );
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.strtolower($AppUI->_('Manage')).'">', '',
		'<form action="index.php?m=articles&a=admin" method="post">', '</form>'
	);
}

     if($_POST[articlesection_id]!="" || $_GET[articlesection_id] != "")
	 {
	 	$section_id = $_REQUEST[articlesection_id];
	 }
	 else
	 {
	    if($_POST[id]!="")
		{
			$section_id = $_POST[id];
		}
	
		// Si el id de la seccion esta vacio y el id de seccion guardado en la session es distinto de 1 trae la seccion top (-1)
		if($_POST[id]=="" && $_POST[txt_search] =="" && $AppUI->state[ArticleIdxTab]!="1")
		{
	       if ($_GET[id]!='')
			{
				$section_id = $_GET[id];
			}else{
	            $section_id = "-1";
			}
		}
		
	 }
	 
	 if($_POST[articletype_id] != '')
	 {
	 	$articletype_id = $_POST[articletype_id];
	 }else{
	 	$articletype_id = $_GET[type];
	 }
	 

if ($canEdit) {
	$titleBlock->addCrumb('?m=articles&a=admin&tab=0&type='.$articletype_id.'&id='.$section_id, $AppUI->_('Manage'), '');
}
$titleBlock->addCrumb('?m=articles&a=addeditarticle&type='.$articletype_id.'&sec_id='.$section_id, $AppUI->_('add article'), '');
$titleBlock->addCrumb('?m=articles&a=addeditlink&id=0&type='.$articletype_id.'&sec_id='.$section_id, $AppUI->_('add link'), '');
$titleBlock->addCrumb('?m=articles&a=addeditfile&type='.$articletype_id.'&sec_id='.$section_id, $AppUI->_('add file'), '');

include_once('./modules/public/itemToFavorite_functions.php');
$deleteFavorite = HasItemInFavorites($section_id, 3);

$titleBlock->addCrumb( "javascript:itemToFavorite(".$section_id.", 3, $deleteFavorite);", $deleteFavorite == 1 ? $AppUI->_('Remove from favorites') : $AppUI->_('Add to favorites') );
//$titleBlock->addCrumb('?m=files&a=addedit&sec_id='.$section_id, $AppUI->_('add file'), '');


$titleBlock->show();

$id = $_GET[id];

?>

<script language="javascript">

function show_sections(action){
    if(action == "show"){
	document.getElementById("sections").style.display = '';
	document.getElementById("hide_i").style.display = '';
	document.getElementById("show_i").style.display = 'none';
	}
	else{ //action = hide
    document.getElementById("sections").style.display = 'none';
	document.getElementById("hide_i").style.display = 'none';
	document.getElementById("show_i").style.display = '';
	}

}

function itemToFavorite(item_id, item_type, item_delete)
{
	window.top.location = "./index.php?m=public&a=itemToFavorite&item_id=" + item_id + "&item_type=" + item_type + "&item_mode_del=" + item_delete + "&dialog=1&suppressLogo=1";
}

</script>

<br>
<table width="100%" border="0" cellpadding="0" cellspacing="0" >
   <tr>
    <td >
    
	<table cellpadding="0" cellspacing="0" background="images/common/back_1linea_06.gif" width="100%">
	<tr>
    <td width="6" align="left"> 

		<div id="show_i" name="show_i" style="display:">
		  <a  href="javascript: //" onclick="show_sections('show')" style="text-decoration:none">
			<img src="images/icons/expand.gif" width="16" height="16" >
		  </a>
		</div>
    </td>

	<td width="6" align="left">

		<div id="hide_i" name="hide_i" style="display:none">
		  <a  href="javascript: //" onclick="show_sections('hide')" style="text-decoration:none" >
			<img src="images/icons/collapse.gif" width="16" height="16"  >
		  </a>
		</div>
    </td>

	<td>
      <span class="boldblanco"><?=$AppUI->_("Choose one of our sections")?>:</span>
	</td>
   </tr>
 </table>


   </td>
  </tr>
  
  <tr>
    <td >  
        <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#ffffff">
            <tr>
              <td width="6"><!-- <img src="images/common/ladoizq.gif" width="6" height="19"> --></td>
              <td>


                <div id="sections" name ="sections" style="display:none"> 

			     <table border="0" width="100%"> 
				    <tr>
					  <td align="left" width="230">
			           [&nbsp;<a href="javascript: submitFm('articlesection_id','-1')"><?php echo $AppUI->_('top'); ?></a>]&nbsp;
					  </td>
					   <td>
					    
					   </td>
					   <td>
					    
					   </td>					   
					   <td align="right">
					     
			             <a href="rss.php?s=-1" target=_blank >
					        <img src='./images/icons/rss_enabled.bmp' width='15'  border='0' alt='RSS'>
				         </a>

					   </td>
					</tr>
			
                 <?
                    $results = mysql_query("SELECT * FROM articlesections order by name");
                    while ($rows = mysql_fetch_array($results, MYSQL_ASSOC)) {
                 ?>
               <!-- [&nbsp;<a href="index.php?m=articles&tab=<?=$tab;?>&id=<?php echo $rows["articlesection_id"];?>"><?php echo $AppUI->_($rows["name"]); ?></a>]&nbsp; -->
                     <tr>
					   <td>
					      [&nbsp;<a href="javascript: submitFm('articlesection_id','<?php echo $rows["articlesection_id"];?>')"><?php echo $AppUI->_($rows["name"]); ?></a>]
					   </td>
					   <td align="left">
					     <? echo $rows["description"] ?>
					   </td>
					   <td align="left">
					     
			             <a href="mailto:<?=$rows["articlesection_email"] ?>" >
					        <? echo $rows["articlesection_email"] ?>
				         </a>

					   </td>					   
					   <td align="right">
					     
			             <a href="rss.php?s=<?=$rows["articlesection_id"] ?>" target=_blank >
					        <img src='./images/icons/rss_enabled.bmp' width='15'  border='0' alt='RSS'>
				         </a>

					   </td>
					</tr>
			    

                <?php } ?>
                </table>
			   </div>


              </td>
              <td width="6"> <!-- <div align="right"><img src="images/common/ladoder.gif" width="6" height="19"></div>--></td>
            </tr>
        </table>
    </td>
  </tr>
  <tr bgcolor="#666666">
    <td height="1" colspan="3"></td>
  </tr>
</table>
<br>
<?
	 if($section_id !="")
	 {
       $query_arts = "SELECT description FROM articlesections WHERE articlesection_id = '$section_id' ";
	   $sql_arts = mysql_query($query_arts);
	   $desc_section = mysql_fetch_array($sql_arts);

	   //echo $desc_section[0]."<br><br>";
	 }

$tabBox = new CTabBox( "?m=articles&id=$id", "{$AppUI->cfg['root_dir']}/modules/articles/", $tab );
$tabBox->add( 'viewsection', 'Articles' );
$tabBox->add( 'viewsection', $AppUI->_('News') );
$tabBox->add( 'viewsection', $AppUI->_('Last commented') );
$tabBox->add( 'viewsection', $AppUI->_('Last modified') );
$tabBox->show( $extra );
?>


<br><br>
<div align="justify">
<font size="1" >La informaci&oacute;n contenida en este sistema se encuentra sujeta a un CONVENIO DE CONFIDENCIALIDAD (NDA) el cual se considera aceptado por cualquier usuario que acceda al mismo. La violaci&oacute;n de los t&eacute;rminos y/o condiciones de este NDA generar&aacute; a favor de 'Technology for Solutions' el derecho de reclamar los da&ntilde;os y perjuicios ocasionados incluyendo pero sin limitarlo a gastos judiciales y honorarios profesionales devengados en el reclamo o en las acciones legales tendientes a proteger la informaci&oacute;n confidencial. Asimismo, la violaci&oacute;n al deber de confidencialidad har&aacute; pasible a la parte infractora de las sanciones previstas en los arts. 11 y 12 de la ley 24.766. </font>
</div>
<br>
