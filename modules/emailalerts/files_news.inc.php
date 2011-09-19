<?
 $sql = "
 SELECT projects.project_name as project, files.*, DATE_FORMAT(files.file_date,'%d/%m/%Y') as file_datef
 FROM project_roles, projects, files
 WHERE project_roles.user_id = '$user_id'
 AND projects.project_id = project_roles.project_id
 AND files.file_project = project_roles.project_id
 AND file_date >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
 ";

   $rowsb = db_loadList( $sql, NULL );
   if (count( $rowsb)) {
    $subject   = "Nuevos Documentos.";
    $message   = "";
    $xml.= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><files>";
    foreach ($rowsb as $b) {
	$xml.= "<file>";
	$xml.= "<date><![CDATA[".$b["file_datef"]."]]></date>";
	$xml.= "<filename><![CDATA[".$b["file_name"]."]]></filename>";
	$xml.= "<projectname><![CDATA[".$b["project"]."]]></projectname>";
	$xml.= "<description><![CDATA[".$b["file_description"]."]]></description>";
	$xml.= "</file>";
    }
    $xml.= "</files>";
   }


?>
