<?php /* $Id: rss_recursos.php,v 1.1 2009-05-19 21:15:27 pkerestezachi Exp $ */

ob_start();

$dPconfig = array();
include_once( "./includes/config.php" );
require_once( "./classes/ui.class.php" );


session_name( 'psa'.$dPconfig['instanceprefix'] );

if (get_cfg_var( 'session.auto_start' ) > 0)
{
	session_write_close();
}

session_start();
session_register( 'AppUI' );


if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout'])) {
	if (isset( $_SESSION['AppUI'] ) && isset($_GET['logout']))
		$user_log_id = $AppUI->user_log_id;
	$AppUI = new CAppUI();	
    $_SESSION['AppUI'] = new CAppUI();
}
$AppUI =& $_SESSION['AppUI'];
$AppUI->setConfig( $dPconfig );



require "{$AppUI->cfg['root_dir']}/includes/db_connect.php";
include "{$AppUI->cfg['root_dir']}/includes/main_functions.php";
include "{$AppUI->cfg['root_dir']}/includes/permissions.php";

if (isset($_GET['p']))
{   
	if ($_GET['p']=='psa'){
		$where_project_art = "AND project <> '' ";
		$where_project_file = "AND file_project <> '' ";
	}else{
	   $where_project_art = "AND project = '".$_GET['p']."' ";
	   $where_project_file = "AND file_project = '".$_GET['p']."' ";
	}
}
else{
	$where_project_art = "AND project = '-1' ";
	$where_project_file = "AND file_project = '-1' ";
}



header('Content-Type: text/xml'); 

echo '<?xml version="1.0" encoding="ISO-8859-1"?>'; 
echo '<!-- Correccion  27/09/07 18:50 -->';

echo '<rss version="0.91">
<channel> 
    <title>';

echo @$AppUI->getConfig( 'page_title' ).'</title>';
 
echo '<link>'.@$AppUI->getConfig( 'base_url' ).'</link> 
      <description>
        Base de conocimientos de '.@$AppUI->getConfig( 'page_title' ).'
      </description> 
      <language>es</language> 
      <generator>'.@$AppUI->getConfig( 'company_name' ).'</generator>';

$time = time();
$last_build_date = date('D, d M Y 09:00:00',$time); 

echo '<ttl>60</ttl>';


$result = mysql_query("
(SELECT DATE_FORMAT(date,'%Y%m%d') AS ord,articlesection_id, 
			user_id,
			article_id, 
			title, 
			type, 
			abstract, 
			article_comments,
			DATE_FORMAT(date,'%a, %d %b %Y %H:%i:%s') as datefmt,
			article_comments AS comments,
			project_name
FROM articles , projects
WHERE  1=1
AND type = '0'
AND is_private = 0
and project_id = project
$where_project_art

)UNION
(	SELECT DATE_FORMAT(date,'%Y%m%d') AS ord,articlesection_id,
			user_id,
			article_id,
			title,
			type,
			CONCAT(body,'<br/>',abstract) as abstract,
			article_comments,
			DATE_FORMAT(date,'%a, %d %b %Y %H:%i:%s') as datefmt,
			article_comments AS comments,
			project_name
FROM articles , projects
WHERE  1=1
AND type = '1'
AND is_private = 0
and project_id = project
$where_project_art

)UNION 
(
		SELECT DATE_FORMAT(file_date,'%Y%m%d') AS ord,
			file_section,
			file_owner,
			file_id, 
			file_description, 
			file_type, 
			file_name, 
			file_comments,
			DATE_FORMAT(file_date,'%a, %d %b %Y %H:%i:%s') as datefmt,
			file_comments AS comments,
			project_name
FROM files, projects
WHERE 
project_id = file_project AND
file_delete_pending = 0 
AND is_private = 0
$where_project_file

)ORDER BY ord desc

");



while($row = mysql_fetch_array($result))
{   
	
if  ($row['type']=='0'){
$link= @$AppUI->getConfig( 'base_url' ).'/index_inc.php?inc=./modules/articles/viewarticle.php&amp;m=articles&amp;id='.$row['article_id']; 
}

if  ($row['type']=='1'){
$link= @$AppUI->getConfig( 'base_url' ).'/index_inc.php?inc=./modules/articles/vwlink.php&amp;m=articles&amp;id='.$row['article_id']; 	
}

if  ($row['type']!='1' && $row['type'] !='0'){
//$link= @$AppUI->getConfig( 'base_url' ).'/fileviewer.php?file_id='.$row['article_id']; 
$link= @$AppUI->getConfig( 'base_url' ).'/index_inc.php?inc=./modules/files/show_versions.php&amp;m=files&amp;file_id='.$row['article_id']; 
}
 
 if ($row['type']!='1' && $row['type'] !='0')
 	 {
 	 	$note_type = '1';
 	 }
 	 else{
 	    $note_type = '2';
 	 }
 
 echo '<item>';
 echo '<title><![CDATA[';
 echo " [ ".$row['project_name']." ] ".$row['title'];
 echo "]]></title>"; 
 echo '<description><![CDATA['.$row['abstract'].']]></description>'; 
 echo '<pubDate>'.$row['datefmt'].'</pubDate>';  
 echo '<link>'.$link.'</link>';
 echo '</item>';
   
}

$sqlA = "
SELECT article_id
       FROM articles , projects
       WHERE  1=1
       and project_id = project
       $where_project_art
";
$articles = db_loadColumn($sqlA);


if (count($articles)==0)
{
	$articles[0] = '-1';
}

$sqlB = "
SELECT file_id 
       FROM files, projects
       WHERE 
       project_id = file_project AND
       file_delete_pending = 0 
       $where_project_file
";
$files = db_loadColumn($sqlB);


if (count($files)==0)
{
	$files[0] = '-1';
}



$sql_coment = mysql_query("SELECT 
count(*) as cant, know_base_type,know_base_item_id
from know_base_note 
WHERE  ( know_base_item_id IN (" . implode( ',', $articles ) . ") and  know_base_type = '2') OR
( know_base_item_id IN (" . implode( ',', $files ) . ") and  know_base_type = '1')
group by know_base_item_id, know_base_type" );


while($vec = mysql_fetch_array($sql_coment ))
{ 
	$link = '';
	$titulo = '';
	
	$sql_item = mysql_query("SELECT k.user_id, 
	                    k.know_base_note_id,
	                    u.user_username, 
	                    k.know_base_note, 
	                    DATE_FORMAT(k.know_base_date,'%a, %d %b %Y %H:%i:%s') as date_coment 
	             from know_base_note as k , users as u  
	             WHERE u.user_id = k.user_id and 
	                   know_base_type = '".$vec['know_base_type']."' and 
	                   know_base_item_id= '".$vec['know_base_item_id']."' 
	             order by k.know_base_date desc limit 1");
	
	$data_c = mysql_fetch_array($sql_item);
	$id_coment = $data_c['know_base_note_id'];
	

    # Es un articulo o enlace
	if ($vec['know_base_type'] == "2")
	{ 
	   $sql_item2 = mysql_query("SELECT 
			title, 
			type
            FROM articles 
            WHERE article_id = '".$vec['know_base_item_id']."'
            ");
            
	   $data_item = mysql_fetch_array($sql_item2);
	   
	   $titulo = $data_item['title'];
	   
	   // Es un articulo
	   if ($data_item['type']=='0')
	   {
	   	  $link = @$AppUI->getConfig( 'base_url' ).'/index_inc.php?inc=./modules/articles/viewarticle.php&amp;m=articles&amp;id='.$vec['know_base_item_id'].'#'.$id_coment;
	   }
	   
	   if ($data_item['type']=='1')
	   {
	   	   $link = @$AppUI->getConfig( 'base_url' ).'/index_inc.php?inc=./modules/articles/vwlink.php&amp;m=articles&amp;id='.$vec['know_base_item_id'].'#'.$id_coment; 
	   }
	   
	   // Traigo el nombre del proyecto
	   
	   $sql_p = mysql_query("SELECT project_name FROM articles, projects WHERE article_id ='".$vec['know_base_item_id']."' and project_id= project ");
	   $project_name = mysql_result($sql_p, 'project_name');
	    
	}else{
		# es un archivo
		
		$sql_item3 = mysql_query("SELECT 
			file_description, 
			file_type,
			file_section
            FROM files
            WHERE file_id = '".$vec['know_base_item_id']."' 
            ");
	   $data_item3 = mysql_fetch_array($sql_item3);
	   
	   $titulo = $data_item3['file_description'];
	   //$link = @$AppUI->getConfig( 'base_url' ).'/fileviewer.php?file_id='.$vec['know_base_item_id'].'#'.$id_coment; 
	   $link= @$AppUI->getConfig( 'base_url' ).'/index_inc.php?inc=./modules/files/show_versions.php&amp;m=files&amp;file_id='.$vec['know_base_item_id'].'#'.$id_coment;        
	   $seccion = $data_item3['file_section'];
	   
	   $sql_p = mysql_query("SELECT  project_name FROM files, projects WHERE file_id ='".$vec['know_base_item_id']."' and project_id= file_project ");
	   
	   $project_name = mysql_result($sql_p, 'project_name');
	}
	
	if (($vec['know_base_type'] == "1" && $section == 0) || $vec['know_base_type'] == "2" && $section == 0){
	echo '<item>';
	echo '<title><![CDATA[';
	echo " [ ".$project_name." ]  ".$titulo." - Comentarios (".$vec['cant'].")']]></title>"; 
	echo '<pubDate>'.$data_c['date_coment'].'</pubDate>';  
	echo '<link>'.$link.'</link>';
	echo '<description><![CDATA[';
	echo  $data_c['user_username'].':';
	echo '<br>'.$data_c['know_base_note'];
	echo ']]></description>'; 
	echo '</item>';
	}
	
	
}
	 

echo '</channel></rss>'; 
?>
