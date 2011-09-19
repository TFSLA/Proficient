<?
$sql = "
SELECT timesheets.*, projects.project_name as project, DATE_FORMAT(timesheets.timesheet_date,'%d/%m/%Y') as timesheet_datef, DATE_FORMAT(timesheets.timesheet_start_date,'%d/%m/%Y') as timesheet_start_datef, DATE_FORMAT(timesheets.timesheet_end_date,'%d/%m/%Y') as timesheet_end_datef
FROM timesheets, projects
WHERE projects.project_id = timesheets.timesheet_project
AND timesheets.timesheet_user = '$user_id'
AND timesheets.timesheet_last_status = '1'
ORDER BY  timesheet_date
";

$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $subject   = "Timesheets sin supervisar.";
  $message   = "";
  $xml.= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><timesheets>";
  foreach ($rows as $a) {
	$xml.= "<timesheet>";
	$xml.= "<date><![CDATA[".$a["timesheet_datef"]."]]></date>";
	$xml.= "<start><![CDATA[".$a["timesheet_start_datef"]."]]></start>";
	$xml.= "<end><![CDATA[".$a["timesheet_end_datef"]."]]></end>";
	$xml.= "<project><![CDATA[".$a["project"]."]]></project>";
	$xml.= "</timesheet>";
  }
  $xml.= "</timesheets>";
}

?>
