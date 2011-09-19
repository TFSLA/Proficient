<?php /* $Id: viewarticle.php,v 1.3 2009-06-19 20:02:39 ctobares Exp $ */
//$AppUI->savePlace();

// setup the title block
//$titleBlock = new CTitleBlock( 'Articles', 'article_management.gif', $m, "$m.$a" );
//$titleBlock->addCrumb('?m=articles',$AppUI->_('back'),'');
//$titleBlock->show();
GLOBAL $AppUI, $xajax, $msg;

include_once("{$AppUI->cfg['root_dir']}/includes/permissions.php");
include_once("{$AppUI->cfg['root_dir']}/modules/projects/projects.class.php");
include_once("{$AppUI->cfg['root_dir']}/modules/pipeline/leads.class.php");

if($article_id !=""){
	$id = $article_id;
}

//BEGIN SECURITY
$query_article = "SELECT articlesection_id, project, is_private, user_id, opportunity FROM articles WHERE article_id = '".$id."' ";
$sql =  db_exec( $query_article );
$data_perm = mysql_fetch_array($sql);
$section_article = $data_perm[0];
$project_article = $data_perm[1];
$is_private = $data_perm[2];
$owner_user_id = $data_perm[3];
$opportunity = $data_perm[4];

//Validacion si el articulo es privado
if($is_private == 1 && $owner_user_id != $AppUI->user_id)
	if($AppUI->user_type != 1)
		$AppUI->redirect( "m=public&a=access_denied" );

//  Por si acceden directamente poniendo la direccion , verifico los permisos
$accessdenied = true;

$objProject = new CProject();
$prjs = $objProject->getAllowedRecords($AppUI->user_id, "project_id");

$leads = CLead::getAllowedLeads();

if ($project_article > 0 && (array_key_exists($project_article, $prjs))){
	$accessdenied = false;
}
elseif($opportunity > 0){
	if (array_key_exists($opportunity, $leads))
		$accessdenied = false;
	else{
		$usr = new CUser();
		$usr->load( $AppUI->user_id );
		$delegs = $usr->getDelegators();

		foreach( $delegs as $deleg )
		{
			$leads = CLead::getAllowedLeads($deleg["delegator_id"], 0);
			if(array_key_exists($opportunity, $leads))
				$accessdenied = false;
		}
	}
}
else{
	if($section_article <> 0){
		if(!getDenyRead('articles')){
			$accessdenied = false;
		}
		else{

			$userSections = CSection::getSectionsByUser();

			if (in_array($section_article, $userSections))
				$accessdenied = false;

			/*
			$objCompany = new CCompany();
			$companies = $objCompany->getCompanies($AppUI->user_id);

			$query_sections = "SELECT COUNT(DISTINCT(id)) FROM articlesections_projects";
			$query_sections .= " WHERE articlesection_id=".$section_article;
			$query_sections .= " AND (";
			$query_sections .= " project_id IN (". implode( ',', array_keys($prjs) ) .")";
			$query_sections .= " OR (company_id IN (". implode( ',', array_keys($companies) ) .") AND project_id = -1)";
			$query_sections .= " OR company_id IN (".$AppUI->user_company."))";

			$countSections = db_loadColumn($query_sections);

			if($countSections[0] > 0)
				$accessdenied = false;
			*/
		}
	 }
}

# Si el articulo esta relacionado a una incidencia permito que lo vean
$select_bug = mysql_query("SELECT count(id) as cant_kb_bug FROM btpsa_bug_kb WHERE kb_item='$id' AND (kb_type='0' OR kb_type='1') ");
$bug_row = mysql_fetch_array($select_bug, MYSQL_ASSOC);

if($bug_row['cant_kb_bug']>0) $accessdenied =false;

if ($accessdenied)
	$AppUI->redirect( "m=public&a=access_denied" );

//END SECURITY

$xajax->printJavascript('./includes/xajax/');


$result = mysql_query("SELECT articlesection_id, articles_reads, title, abstract,user_id, body, date  FROM articles WHERE article_id = $id ");

$row = mysql_fetch_array($result, MYSQL_ASSOC);
$newreads = $row["articles_reads"]+1;
$result2 = mysql_query("update articles set articles_reads = $newreads WHERE article_id = $id ");

require_once("./modules/articles/articles.class.php");
$obj = new CArticle();
$obj->article_id=$id;
$obj->saveLog(0,3);
?>
<html>
	<head>
		<title><?php echo $AppUI->_('Article') ?> </title>
	</head>
		<link rel="stylesheet" type="text/css" href="./style/<?php echo "$uistyle"; ?>/main.css" media="all" />
	<body>
<table width="90%" align="center"><tr><td>
<?

/*
 * if($row["articlesection_id"]==-1)
 * echo '<p align="right"><a href="javascript:window.close()">'.$AppUI->_('Close').'</a></p>';
 * else
*/

include_once('./modules/public/itemToFavorite_functions.php');
$deleteFavorite = HasItemInFavorites($id, 7);


 echo ("<p class=\"article\" align=\"right\"><a class=\"article\" href=\"javascript:itemToFavorite(".$id.", 7, $deleteFavorite);\">".($deleteFavorite == 1 ? $AppUI->_('Remove from favorites') : $AppUI->_('Add to favorites'))."</a>&nbsp;");
 echo ("<a class=\"article\" href=\"javascript:window.close()\">".$AppUI->_('Close')."</a></p>");

  ?>
<?
$dateArticle = new Date($row['date']);

echo($dateArticle->format($AppUI->user_prefs['SHDATEFORMAT']));
?><br>
<b>
<?=str_replace("\n","<br>",$row["title"])?>
</b>
<br><br>
<?
$segs = round(count(explode(" ",$row["body"])) * 0.27);
echo "<img src='modules/articles/images/clock.gif'> ".$AppUI->_('Estimated read time').": ".floor($segs / 60) . ":" . $segs % 60 . "<br>";
?>
<?
 $sql = mysql_query("SELECT user_first_name, user_last_name  FROM users WHERE user_id ='".$row["user_id"]."' ");
 $autor = mysql_fetch_array($sql);
?>
<b><?=$AppUI->_('Autor')." </b>: ".$autor["user_first_name"]." ".$autor["user_last_name"]?><br>
(<?=$row["articles_reads"]." ".$AppUI->_('reads')?>)<br><br>

<b><?=$AppUI->_('Summary')." </b>: ".str_replace("\n","<br>",$row["abstract"])?>

<br><br>
<?=$row["body"]?>

<br>
<?

if($row["articlesection_id"]==-1)
  echo '<p align="right" class="article"><a class="article" href="javascript:window.close()">'.$AppUI->_('Close').'</a></p>';
else
  echo '<p align="right" class="article"><a class="article" href="javascript:window.close()">'.$AppUI->_('Close').'</a></p>';

  ?>
</td></tr>
<tr><td><b>Comentarios de usuarios</b></td></tr>
<tr><td><span id='new_0'></span></td></tr>
<tr><td><span id='0'></span></td></tr>
</table>
<script type="text/javascript">
<!--
	xajax_edit(0, <? echo $id; ?>, 0, 2);
	xajax_notes(0, <? echo $id; ?>, 2);
//-->

function itemToFavorite(item_id, item_type, item_delete)
{
	window.parent.opener.top.location = "./index.php?m=public&a=itemToFavorite&item_id=" + item_id + "&item_type=" + item_type + "&item_mode_del=" + item_delete + "&dialog=1&suppressLogo=1";
	window.top.location.reload();
}

</script>
<?php
$obj->showHistory();
?>
</body>
</html>
