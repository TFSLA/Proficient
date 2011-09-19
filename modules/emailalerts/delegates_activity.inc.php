<?
$today = date("Ymd");
$sql = "
SELECT delegations_log.*, delegations_log_desc.*, users.user_id, users.user_username, users.user_first_name, users.user_last_name, SUBSTRING(dl_timestamp,9,6) as time
FROM delegations_log, delegations_log_desc, users  
WHERE SUBSTRING(dl_timestamp,1,8) = '" .$today. "'
AND dl_user_id = '$user_id'
AND delegations_log.dl_description = delegations_log_desc.dl_description
AND delegations_log.dl_module_id   = delegations_log_desc.dl_module_id
AND users.user_id = delegations_log.dl_delegate_id
ORDER BY dl_timestamp
";
$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $subject   = "Delegados - Movimientos del dia.";
  $message   = "";
  $xml.= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><activities>";
  foreach ($rows as $a) {
	$xml.= "<activity>";
	$xml.= "<username><![CDATA[".$a["user_username"]."]]></username>";
	$xml.= "<time><![CDATA[".substr($a["time"],0,2).":".substr($a["time"],2,2).":".substr($a["time"],4,2)."]]></time>";
	$xml.= "<firstname><![CDATA[".$a["user_first_name"]."]]></firstname>";
	$xml.= "<lastname><![CDATA[".$a["user_last_name"]."]]></lastname>";
	$xml.= "<accion><![CDATA[".html2ascii($a["dld_es"])."]]></accion>";
	$xml.= "</activity>";
  }
  $xml.= "</activities>";
}

?>
