<?
$sql = "
SELECT salespipelinecontacts.*, salespipeline.description as pipelinename, DATE_FORMAT(salespipeline.closingdate,'%d/%m/%Y') as datef
FROM salespipelinecontacts, salespipeline, users
WHERE salespipelinecontacts.idsalespipeline = salespipeline.id
AND salespipeline.lead_owner = users.user_id
AND users.user_id = '$user_id'
AND salespipelinecontacts.date > DATE_SUB(CURDATE(), INTERVAL 2 DAY)
AND salespipelinecontacts.date < DATE_SUB(CURDATE(), INTERVAL 1 DAY)
ORDER BY date 
";
$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $subject   = "Sales Pipeline - Proximas actividades";
  $message   = "";
  $xml.= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><activities>";
  foreach ($rows as $a) {
	$xml.= "<activity>";
	$xml.= "<opportunity><![CDATA[".$a["pipelinename"]."]]></opportunity>";
	$xml.= "<date><![CDATA[".$a["datef"]."]]></date>";
	$xml.= "<summary><![CDATA[".$a["summary"]."]]></summary>";
	$xml.= "<kindofcontact><![CDATA[".$a["kindofcontact"]."]]></kindofcontact>";
	$xml.= "<description><![CDATA[".$a["description"]."]]></description>";
	$xml.= "</activity>";
  }
  $xml.= "</activities>";
}

?>
