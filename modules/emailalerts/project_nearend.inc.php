<? 
require_once("getadminprojects.inc.php");
$prjs = getAdminProjects($user_id);
if($params=="") $params = "15"; 
$params2 = $params - 1;
if (count($prjs)) {
$sql = "
SELECT projects.*, DATE_FORMAT(projects.project_start_date,'%d/%m/%Y') as project_start_datef, DATE_FORMAT(projects.project_end_date,'%d/%m/%Y') as project_end_datef
FROM projects
WHERE project_active = 1
  AND CURDATE() > SUBSTRING(DATE_SUB(project_end_date, INTERVAL ".$params." DAY),1,10)
  AND CURDATE() < SUBSTRING(DATE_SUB(project_end_date, INTERVAL ".$params2." DAY),1,10)
  AND projects.project_id  IN (" . implode( ',', $prjs ) . ") 
ORDER BY project_name
";

$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $subject   = "Proyectos por alcanzar la fecha de finalizacion.";
  $message   = "";
  $xml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><projects>" ;
  foreach ($rows as $a) {
	$project_id = $a["project_id"];
          $xml.= "<project>";
	  $xml.= "<projectname><![CDATA[".$a["project_name"]."]]></projectname>";
	  $xml.= "<description><![CDATA[".$a["project_description"]."]]></description>";
	  $xml.= "<start><![CDATA[".$a["project_start_datef"]."]]></start>";
	  $xml.= "<end><![CDATA[".$a["project_end_datef"]."]]></end>";
	  $xml.= "<targetbudget><![CDATA[".$a["project_target_budget"]."]]></targetbudget>";
	  $xml.= "<actualbudget><![CDATA[".$a["project_actual_budget"]."]]></actualbudget>";
	  $xml.= "</project>";
  }
  $xml.= "</projects>";
}


}

?>
