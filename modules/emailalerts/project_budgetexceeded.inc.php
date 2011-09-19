<? 
require_once("getadminprojects.inc.php");
$prjs = getAdminProjects($user_id);
if($params=="") $params = "90";

if (count($prjs)) {
$sql = "
SELECT projects.*, DATE_FORMAT(projects.project_start_date,'%d/%m/%Y') as project_start_datef, DATE_FORMAT(projects.project_end_date,'%d/%m/%Y') as project_end_datef
FROM projects
WHERE project_actual_budget > 100 / project_target_budget * project_actual_budget > $params
AND project_active = 1
AND projects.project_id  IN (" . implode( ',', $prjs ) . ") 
ORDER BY project_name
";

$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $subject   = "Proyectos con presupuesto cercano al maximo.";
  $message   = "";
  $xml.= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><projects>";
  foreach ($rows as $a) {
	$xml.= "<project>";
	$xml.= "<projectname><![CDATA[".$a["project_name"]."]]></projectname>";
	$xml.= "<description><![CDATA[".$a["project_description"]."]]></description>";
	$xml.= "<targetbudget><![CDATA[".$a["project_target_budget"]."]]></targetbudget>";
	$xml.= "<actualbudget><![CDATA[".$a["project_actual_budget"]."]]></actualbudget>";
	$xml.= "<start><![CDATA[".$a["project_start_datef"]."]]></start>";
	$xml.= "<end><![CDATA[".$a["project_end_datef"]."]]></end>"; 
	$xml.= "</project>";
  }
  $xml.= "</projects>";
}

}

?>
