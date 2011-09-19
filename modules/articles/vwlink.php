<?php
$m = $_GET[m];
GLOBAL $AppUI, $xajax, $msg;

include_once("{$AppUI->cfg['root_dir']}/includes/permissions.php");
include_once("{$AppUI->cfg['root_dir']}/modules/projects/projects.class.php");
include_once("{$AppUI->cfg['root_dir']}/modules/pipeline/leads.class.php");

$xajax->printJavascript('./includes/xajax/');
$id = isset($_GET['id']) ? $_GET['id'] : 0;

//BEGIN SECURITY
$query_article = "SELECT articlesection_id, project, is_private, user_id, opportunity FROM articles WHERE article_id = '".$id."' ";
$sql =  db_exec( $query_article );
$data_perm = mysql_fetch_array($sql);
$section_article = $data_perm[0];
$project_article = $data_perm[1];
$is_private = $data_perm[2];
$owner_user_id = $data_perm[3];
$opportunity = $data_perm[4];

//Validacion si el enlace es privado
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
		}
	 }
}

if ($accessdenied)
	$AppUI->redirect( "m=public&a=access_denied" );

//END SECURITY

require_once("./modules/articles/articles.class.php");
$obj = new CArticle();
$obj->article_id=$id;
$obj->saveLog(1,3);

$sql = "
	SELECT
	  a.date,
	  a.title,
	  a.articlesection_id,
	  a.abstract,
	  CONCAT(u.user_last_name,', ',u.user_first_name) AS autor,
	  a.body
	FROM articles AS a
	INNER JOIN users AS u
	  on(a.user_id=u.user_id)
	WHERE article_id =$id
";
if ($id!="0"){
	$query = mysql_query($sql);
	$row = mysql_fetch_array($query);
	$autor = $row[autor];
	$articlesection_id = $row[articlesection_id];
	$date=$row[date];
	$description = $row[title];
	$href = $row['abstract'];
	$abstract = $row[body];
}
?>
<html>
	<head>
		<title><?php echo $AppUI->_('Link'); ?> </title>
	</head>
		<link rel="stylesheet" type="text/css" href="./style/<?php echo "$uistyle"; ?>/main.css" media="all" />
	<body>
	<p align="right"><a href="javascript:window.close()"><? echo $AppUI->_('Close') ?></a></p>
	<table width="100%" border="0" cellpadding="2" cellspacing="0">

		<tr>
			<td width='15'></td>
			<td colspan="2">
				<?
				$dateLink = new Date($date);
				echo($dateLink->format($AppUI->user_prefs['SHDATEFORMAT']));
				?>
			</td>
		</tr>
		<tr>
			<td width='15'></td>
			<td align="left"><b><?php echo $AppUI->_( 'Title' );?>:</b>&nbsp;<?php echo $description;?></td>
			<td valign="top" align="center"></td>
		</tr>
		<tr>
			<td width='15'></td>
			<td align="left"><b><?php echo $AppUI->_( 'Autor' );?>:</b>&nbsp;<?php echo $autor;?></td>
			<td valign="top" align="center"></td>
		</tr>
		<tr>
			<td width='15'></td>
			<td align="left"><b><?php echo $AppUI->_('Url Address');?>:</b>&nbsp;<a href="http://<?php echo $href;?>" target="_blank"><?php echo $href;?></a></td>
			<td valign="top" align="center"></td>
		</tr>
		<tr>
			<td width='15'></td>
			<td align="left"><b><?php echo $AppUI->_( 'Abstract' );?>:</b>&nbsp;<?php echo $abstract;?></td>
			<td valign="top" align="center"></td>
		</tr>
	</table>
	<p align="right"><a href="javascript:window.close()"><? echo $AppUI->_('Close') ?></a></p>
	<table width="100%" border="0" cellpadding="2" cellspacing="0" >
	<tr>
		<td width='15'></td>
		<td align="left"><b>Comentarios de usuarios</b></td>
	</tr>
	<tr>
		<td width='15'></td>
		<td><span id='new_0'></span></td>
	</tr>
	<tr>
		<td width='15'></td>
		<td><span id='0'></span></td>
	</tr>

<script type="text/javascript">
	xajax_edit(0, <? echo $id ?>, 0, 2);
	xajax_notes(0, <? echo $id ?>, 2);
</script>
</table>
</body>
</html>
<?php
$obj->showHistory();
?>