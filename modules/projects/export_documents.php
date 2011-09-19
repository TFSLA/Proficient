<?php

require_once("export_documents_functions.php");

GLOBAL $AppUI, $project_id;

$zohoProject = $_GET['zohoproject'];
$zohoProjectFolder = $_GET['zohoprojectfolder'];
$urlzoho = "http://projects.zoho.com/portal/tfsla/api/private/xml/doc/add?apikey=6fa18718367beedbef7dbe2574482e77&ticket=c609d9f2b811c586f3495701a54791e4";

$HandleLog = fopen("{$AppUI->cfg['root_dir']}/files/{$project_id}_DEBUG_LOG.txt", 'w');

$sql = "SELECT file_id, file_name, file_description, 
		(
			SELECT version_file_name
			FROM files_versions
			WHERE file_id = files.file_id
			AND delete_pending = '0'
			ORDER BY Version DESC
			LIMIT 0,1
		) as file_uid
		FROM files
		WHERE file_project = '".$project_id."'
		AND file_delete_pending = '0'
		ORDER BY date_modified desc";

$query = db_exec($sql);

$countFilesOK = 0;

while($file = mysql_fetch_array($query))
{
	$path = "{$AppUI->cfg['root_dir']}/files/{$file['file_uid']}";
	$pathReal = "{$AppUI->cfg['root_dir']}/files/{$file['file_name']}";
		
	if (file_exists($path) && copy($path, $pathReal))
	{
		if (do_cut_file($urlzoho, $zohoProject, $zohoProjectFolder, htmlentities($file['file_name']), $pathReal, $HandleLog))
			$countFilesOK++;

		unlink($pathReal);
	}
}

$sql = "SELECT article_id, title, body
		FROM articles
		WHERE project = '".$project_id."'
		AND type = '0'
		ORDER BY date_modified desc";

$query = db_exec($sql);

$countArticlesOK = 0;

while($article = mysql_fetch_array($query))
{
	$pathReal = "{$AppUI->cfg['root_dir']}/files/{$article['article_id']}.html";

	 $Handle = fopen($pathReal, 'w');
	 fwrite($Handle, $article['body']); 
	 fclose($Handle);
	 
	if (file_exists($pathReal))
	{
		if (do_cut_file($urlzoho, $zohoProject, $zohoProjectFolder, htmlentities($article['title']), $pathReal, $HandleLog))	
			$countArticlesOK++;
			
		unlink($pathReal);
	}
}

fclose($HandleLog);

$AppUI->setMsg("Se exportaron ".$countFilesOK." archivos y ".$countArticlesOK." artículos.", UI_MSG_OK);
$AppUI->redirect();

?>
