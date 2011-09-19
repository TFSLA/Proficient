<?
require_once("getadminprojects.inc.php");
if($params=="") $params = "7";
$prjs = getAdminProjects($user_id);
if (count($prjs)) {
  foreach ($prjs as $a) {
   $projectid   = $a;
   $rowsp = db_loadList("SELECT * FROM projects WHERE project_id=$projectid",NULL);
   $projectname = $rowsp[0]["project_name"];
   $sql = "
   SELECT btpsa_bug_table.*, DATE_FORMAT(btpsa_bug_table.date_submitted,'%d/%m/%Y') as date_submittedf, btpsa_bug_table.id as bid
   FROM btpsa_bug_table
   WHERE project_id = '$projectid'
   AND handler_id = 0
   AND date_submitted < DATE_SUB(CURDATE(), INTERVAL ". $params ." DAY)
   ";
   $rowsb = db_loadList( $sql, NULL );
   if (count( $rowsb)) {
    foreach ($rowsb as $b) {
	$xml.= "<bug>";
	$xml.= "<date><![CDATA[".$b["date_submittedf"]."]]></date>";
	$xml.= "<bugid><![CDATA[".$b["bid"]."]]></bugid>";
	$xml.= "<summary><![CDATA[".$b["summary"]."]]></summary>";
	$xml.= "<project><![CDATA[".$projectname."]]></project>";
	if($langpref=="en") $xml.= "<link><url><![CDATA[".$dPconfig['base_url']."/index.php?m=webtracking&a=bug_view_page&bug_id=".$b["bid"]."]]></url><label>More...</label></link>";
	else $xml.= "<link><url><![CDATA[".$dPconfig['base_url']."/index.php?m=webtracking&a=bug_view_page&bug_id=".$b["bid"]."]]></url><label>Ver Mas</label></link>";
	$xml.= "</bug>";
    }
   }
  }
  if($xml!=""){
    $xml= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><bugs>".$xml."</bugs>";
    $subject   = "Webtracking - Incidencias sin asignar.";
    $message   = "";
  }
}
?>
