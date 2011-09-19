<?php /* $Id: index.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
$AppUI->savePlace();

# --------------------
# retun an array of project IDs to which the user has access
function user_get_accessible_projects( $c_user_id ) {

	$t_project_table = 'projects';
	$t_project_user_list_table = 'btpsa_project_user_list_table';

	if ( $AppUI->user_type==1 ) {
		$query = "SELECT DISTINCT( project_id )
				  FROM $t_project_table
				  WHERE enabled=1
				  ORDER BY project_name";
	} else {
		$query = "SELECT DISTINCT( p.project_id )
				  FROM $t_project_table p
				  LEFT JOIN $t_project_user_list_table u
				    ON p.project_id=u.project_id
				  WHERE ( p.enabled = 1 ) AND
					( p.view_state='10'
					    OR (p.view_state='50'
						    AND
					        u.user_id='$c_user_id' )
					)
				  ORDER BY p.project_name";
	}
	//echo "<br> QUERY $query <br>";
	$result = db_exec( $query );
	$row_count = db_num_rows( $result );

	$t_projects = array();

	for ( $i=0 ; $i < $row_count ; $i++ ) {
		$row = db_fetch_array( $result );

		array_push( $t_projects, $row['project_id'] );
	}

	return $t_projects;
}


// setup the title block
$titleBlock = new CTitleBlock( 'Search', 'tasks.gif', $m, "$m.$a" );
$titleBlock->show();

if(strlen($query)<2)echo "<br><br><br><center><b>".$AppUI->_('Search string too short').".</b></center><br>";
else{
?>
<br><b><?php echo $AppUI->_('Search results for'); ?> "<?=$query?>":<br></b>

<br><b><?php echo $AppUI->_('Projects');?>:<br></b>
<?
$result = mysql_query("SELECT project_id, company_name, project_name, project_description  FROM projects, companies WHERE company_id = project_company AND (project_name LIKE '%$query%' OR project_description LIKE '%$query%')");
if(mysql_num_rows($result)==0)echo $AppUI->_('No matches for')."&nbsp;". $AppUI->_('Projects')."<br>";
else{
  while ($row2 = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo '&nbsp;&nbsp;<a href="index.php?m=projects&a=view&project_id='.$row2["project_id"].'">'.$row2["project_name"].'</a>. '. $AppUI->_('Company') .': ' .$row2["company_name"].'.<br>';
  }
}
?>

<br><b><?php echo $AppUI->_('Tasks');?>:<br></b>
<?
$result = mysql_query("SELECT task_id, task_name, project_name FROM tasks, projects  WHERE  task_project=project_id AND (task_name LIKE '%$query%' OR task_description LIKE '%$query%')");
if(mysql_num_rows($result)==0)echo $AppUI->_('No matches for')."&nbsp;". $AppUI->_('Tasks')."<br>";
else{
  while ($row2 = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo '&nbsp;&nbsp;<a href="index.php?m=tasks&a=view&task_id='.$row2["task_id"].'">'.$row2["task_name"].'</a> - '.$row2["project_name"].'.<br>';
  }
}
?>
<br><b><?php echo $AppUI->_('Files');?>:<br></b>
<?
$result = mysql_query("
(SELECT file_id, file_name, DATE_FORMAT(file_date,'%m/%d/%Y') as datefmt  FROM files
WHERE file_name LIKE '%$query%' OR file_description LIKE '%$query%'
AND (is_private = 0 OR (is_private = 1 AND file_owner = '$AppUI->user_id')))
UNION DISTINCT
(SELECT file_id, file_name, DATE_FORMAT(file_date,'%m/%d/%Y') as datefmt FROM know_base_note k
JOIN files f ON f.file_id=k.know_base_item_id
WHERE k.know_base_type = '1'
AND know_base_note LIKE '%$query%'
AND (f.is_private = 0 OR (f.is_private = 1 AND f.file_owner = '$AppUI->user_id')))
ORDER BY datefmt DESC;
");
if(mysql_num_rows($result)==0)echo $AppUI->_('No matches for')."&nbsp;". $AppUI->_('Files')."<br>";
else{
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo '&nbsp;&nbsp;<a href="javascript:popUp(\'index_inc.php?inc=./modules/files/show_versions.php&m=files&file_id='.$row["file_id"].'\')" >' .$row["file_name"].'</a> - '.$row["datefmt"].'<br>';
  }
}
?>
<br><b><?php echo $AppUI->_('Articles');?>:<br></b>
<?
$result = mysql_query("
(SELECT title, article_id, DATE_FORMAT(date,'%d/%m/%Y') as datefmt  FROM articles
WHERE type='0' AND (title LIKE '%$query%' OR abstract LIKE '%$query%' OR body LIKE '%$query%')
AND (is_private = 0 OR (is_private = 1 AND user_id = '$AppUI->user_id')))
UNION DISTINCT
(SELECT title, article_id, DATE_FORMAT(date,'%d/%m/%Y') as datefmt FROM know_base_note k
JOIN articles a ON a.article_id=k.know_base_item_id
WHERE k.know_base_type = 2 AND a.type='0'
AND know_base_note LIKE '%$query%'
AND (a.is_private = 0 OR (a.is_private = 1 AND a.user_id = '$AppUI->user_id')))
ORDER BY datefmt DESC;
");
if(mysql_num_rows($result)==0)echo $AppUI->_('No matches for')."&nbsp;". $AppUI->_('Articles')."<br>";
else{
  while ($row2 = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo '&nbsp;&nbsp;<a href="javascript:popUp(\'index_inc.php?inc=./modules/articles/viewarticle.php&m=articles&id='.$row2["article_id"].'\')">'.$row2["title"].'</a> - '.$row2["datefmt"].'.<br>';
  }
}
?>


<br><b><?php echo $AppUI->_('Links');?>:<br></b>
<?
$result = mysql_query("
(SELECT title, article_id, DATE_FORMAT(date,'%d/%m/%Y') as datefmt  FROM articles
WHERE type='1' AND (title LIKE '%$query%' OR abstract LIKE '%$query%' OR body LIKE '%$query%')
AND (is_private = 0 OR (is_private = 1 AND user_id = '$AppUI->user_id')))
UNION DISTINCT
(SELECT title, article_id, DATE_FORMAT(date,'%d/%m/%Y') as datefmt FROM know_base_note k
JOIN articles a ON a.article_id=k.know_base_item_id
WHERE k.know_base_type = 2 AND a.type='1'
AND know_base_note LIKE '%$query%'
AND (a.is_private = 0 OR (a.is_private = 1 AND a.user_id = '$AppUI->user_id')))
");

if(mysql_num_rows($result)==0)echo $AppUI->_('No matches for')."&nbsp;". $AppUI->_('Links')."<br>";
else{
  while ($row2 = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo '&nbsp;&nbsp;<a href="javascript:popUp(\'index_inc.php?inc=./modules/articles/vwlink.php&m=articles&id='.$row2["article_id"].'\')">'.$row2["title"].'</a> - '.$row2["datefmt"].'.<br>';
  }
}
?>

<br><b><?php echo $AppUI->_('Reviews');?>:<br></b>
<?
$result = mysql_query("SELECT review_id, producttitle, score, hits, DATE_FORMAT(date,'%d/%m/%Y') as datefmt FROM reviews  WHERE  producttitle LIKE '%$query%' OR review LIKE '%$query%'");
if(mysql_num_rows($result)==0)echo $AppUI->_('No matches for')."&nbsp;". $AppUI->_('Reviews')."<br>";
else{
  while ($row2 = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo '&nbsp;&nbsp;<a href="index.php?m=reviews&a=viewreview&id='.$row2["review_id"].'">'.$row2["producttitle"].'</a> - '.$row2["datefmt"].'.<br>';
  }
}
?>

<br><b><?php echo $AppUI->_('Contacts');?>:<br></b>
<?
$result = mysql_query("SELECT contact_email, contact_id, contact_first_name, contact_last_name FROM contacts WHERE contact_first_name LIKE '%$query%' OR contact_last_name LIKE '%$query%' OR contact_notes LIKE '%$query%'");
if(mysql_num_rows($result)==0)echo $AppUI->_('No matches for')."&nbsp;". $AppUI->_('Contacts')."<br>";
else{
  while ($row2 = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo '&nbsp;&nbsp;<a href="index.php?m=contacts&a=addedit&contact_id='.$row2["contact_id"].'">'.$row2["contact_first_name"].' '.$row2["contact_last_name"].'</a> - <a href="mailto:'.$row2["contact_email"].'">'.$row2["contact_email"].'</a>.<br>';
  }
}
?>

<br><b><?php echo $AppUI->_('Users');?>:<br></b>
<?
$result = mysql_query("SELECT users.user_id, users.user_username, CONCAT(users.user_last_name,', ',users.user_first_name) AS name, users.user_company, companies.company_name FROM users LEFT JOIN companies ON users.user_company = companies.company_id WHERE users.user_username LIKE '%$query%' OR users.user_first_name LIKE '%$query%' OR users.user_last_name LIKE '%$query%' OR companies.company_name LIKE '%$query%'");
if(mysql_num_rows($result)==0)echo $AppUI->_('No matches for')."&nbsp;". $AppUI->_('Users')."<br>";
else{
  while ($row2 = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo '&nbsp;&nbsp;<a href="index.php?m=admin&a=viewuser&user_id='.$row2["user_id"].'">'.$row2["name"].'</a> - <a href="index.php?m=companies&a=view&company_id='.$row2["user_company"].'">'.$row2["company_name"].'</a>.<br>';
  }
}
?>

<br><b><?php echo $AppUI->_('To-Do');?>:<br></b>
<?
//TO-DOS asociados a proyectos
	$sql="SELECT project_id FROM project_roles WHERE user_id=".$AppUI->user_id."
				UNION
				SELECT project_id FROM project_owners po WHERE project_owner=".$AppUI->user_id."
				UNION
				SELECT project_id FROM projects WHERE project_owner=".$AppUI->user_id."
				GROUP BY project_id
				ORDER BY project_id";
	//echo "$sql";
	$aut_proj=implode( ',', array_keys(db_loadHashList($sql)) );
    if ($aut_proj=='') $aut_proj='0';

$result1 = mysql_query("SELECT p.*, CONCAT(u.user_last_name,', ',u.user_first_name) AS name FROM project_todo p JOIN users u ON p.user_assigned=u.user_id WHERE p.project_id IN ($aut_proj) AND description LIKE '%$query%' ORDER BY date DESC;");
if(mysql_num_rows($result1)!=0)
{
  while ($row2 = mysql_fetch_array($result1, MYSQL_ASSOC)) {
    echo '&nbsp;&nbsp;<a href="index.php?m=todo">'.$row2["description"].'</a> - '.$row2["name"].'<br>';
    //echo '&nbsp;&nbsp;<a href="index.php?m=projects&a=view&project_id='.$row2["project_id"].'&tab=7">'.$row2["description"].'</a> - '.$row2["name"].'<br>';
  }
}

//TODOS asociados a usuarios
$result2 = mysql_query("SELECT p.*, CONCAT(u.user_last_name,', ',u.user_first_name) AS name
FROM user_todo p
JOIN users u ON p.user=u.user_id
WHERE user=$AppUI->user_id AND description LIKE '%$query%' ORDER BY date DESC;");
if(mysql_num_rows($result2)==0 AND mysql_num_rows($result1)==0 )echo $AppUI->_('No matches for')."&nbsp;". $AppUI->_('To-Do')."<br>";
else{
  while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
    echo '&nbsp;&nbsp;<a href="index.php?m=calendar">'.$row2["description"].'</a> - '.$row2["name"].'<br>';
  }
}
?>
<br><b><?php echo $AppUI->_('Webtracking');?>:<br></b>
<?
//Traigo los peroyectos sobre los cuales el usuario que intenta hacer la busqueda tiene permisos.
$project=IMPLODE(", ",user_get_accessible_projects($AppUI->user_id));
if (count($project)==0)
	$project="''";

$consulta="
SELECT btpsa_bug_text_table.id, summary, project_name FROM btpsa_bug_text_table
JOIN btpsa_bug_table ON btpsa_bug_text_table.id = btpsa_bug_table.id
JOIN projects ON btpsa_bug_table.project_id=projects.project_id
WHERE btpsa_bug_table.project_id IN ($project) AND
(summary LIKE '%$query%' OR description LIKE '%$query%' OR additional_information LIKE '%$query%')
";
/*
echo "<pre>";
echo $consulta;
echo "</pre>";*/
$result = mysql_query($consulta);
if(mysql_num_rows($result)==0)echo $AppUI->_('No matches for')."&nbsp;". $AppUI->_('Webtracking')."<br>";
else{
  while ($row2 = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo '&nbsp;&nbsp;<a href="index.php?m=webtracking&a=bug_view_page&bug_id='.$row2["id"].'">'.$row2["summary"].'</a> - '.$row2["project_name"].'<br>';
  }
}

}?>

<script language="javascript">
	function popUp(URL)
	{
		day = new Date();
		id = day.getTime();
		eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0 ,scrollbars=yes, location=0, statusbar=0, menubar=0, resizable=1, width=900, height=500');");
	}
</script>
