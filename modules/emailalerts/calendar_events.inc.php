<? 
if($params=="") $params = "2"; 
$hours = $params;
$hours2 = $params-1;
$sql = "
SELECT events.*, DATE_FORMAT(events.event_start_date,'%d/%m/%Y') as event_start_datef, DATE_FORMAT(events.event_end_date,'%d/%m/%Y') as event_end_datef, DATE_FORMAT(events.event_start_date,'%H:%i') as event_start_timef, DATE_FORMAT(events.event_end_date,'%H:%i') as event_end_timef
FROM events
WHERE DATE_ADD(NOW(), INTERVAL '$hours' HOUR) >= event_start_date
AND DATE_ADD(NOW(), INTERVAL '$hours2' HOUR) < event_start_date
AND event_owner = '$user_id'
ORDER BY event_start_date 
";
$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $subject   = "Calendar - Proximos Eventos.";
  $message   = "";
  $xml.= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><events>";
  foreach ($rows as $a) {
	$rpipeline="";
	$rproject="";
	if($a["event_salepipeline"]!=0){
		$rpid=$a["event_salepipeline"];
		$rowsr = db_loadList( "SELECT * FROM salespipeline WHERE id = ".$rid, NULL );
		$rpipeline=$rowsr[0]["description"];		
	}
	if($a["event_project"]!=0){
		$rid=$a["event_project"];
		$rowsr = db_loadList( "SELECT * FROM projects WHERE project_id = ".$rid, NULL );
		$rproject=$rowsr[0]["project_name"];		
	}
	if($a["event_task"]!=0){
		$rid=$a["event_task"];
		$rowsr = db_loadList( "SELECT * FROM tasks WHERE task_id = ".$rid, NULL );
		$rtask=$rowsr[0]["task_name"];		
	}
	$xml.= "<event>";
	$xml.= "<relatedproject>".$rproject."</relatedproject>";
	$xml.= "<relatedpipeline>".$rpipeline."</relatedpipeline>";
	$xml.= "<relatedtask>".$rtask."</relatedtask>";
	$xml.= "<startdate><![CDATA[".$a["event_start_datef"]." - ".$a["event_start_timef"]."]]></startdate>";
	$xml.= "<enddate><![CDATA[".$a["event_end_datef"]." - ".$a["event_end_timef"]."]]></enddate>";
	$xml.= "<title><![CDATA[".$a["event_title"]."]]></title>";
	$xml.= "<location><![CDATA[".$a["event_location"]."]]></location>";
	$xml.= "<description><![CDATA[".$a["event_description"]."]]></description>";
	$xml.= "</event>";
  }
  $xml.= "</events>";
}

?>

