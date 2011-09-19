<? 
//date_created 	date_updated  user_type=
$sql = "
SELECT DATE_FORMAT(users.date_updated,'%d/%m/%Y') as date_updatedf, hhrr.*, DATE_FORMAT(hhrr.inputdate,'%d/%m/%Y') as inputdatef, hhrr.id as hrid
FROM hhrr, users
WHERE SUBSTRING(inputdate,1,10) > DATE_SUB(CURDATE(), INTERVAL 30 DAY)
AND hhrr.iduser = users.user_id
ORDER BY lastname, firstname
";
$sql = "
SELECT users.*,  DATE_FORMAT(users.date_updated,'%d/%m/%Y') as date_updatedf, DATE_FORMAT(users.date_created,'%d/%m/%Y') as inputdatef, users.user_id as hrid
FROM users
WHERE users.user_type = 5 AND SUBSTRING(users.date_updated,1,10) > DATE_SUB(CURDATE(), INTERVAL 30 DAY)
ORDER BY user_last_name, user_first_name
";
//echo "query: ".$sql;
$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $subject   = "RRHH - Registrados modificados del mes.";
  $message   = "";
  $xml.= "<candidates>";
  foreach ($rows as $a) {
	$xml.= "<candidate>";
	$xml.= "<date><![CDATA[".$a["inputdatef"]."]]></date>";
	$xml.= "<dateupdated><![CDATA[".$a["date_updatedf"]."]]></dateupdated>";
	$xml.= "<firstname><![CDATA[".$a["user_first_name"]."]]></firstname>";
	$xml.= "<lastname><![CDATA[".$a["user_last_name"]."]]></lastname>";
	$xml.= "<homephone><![CDATA[".$a["user_home_phone"]."]]></homephone>";
	$xml.= "<cellphone><![CDATA[".$a["user_mobile"]."]]></cellphone>";
	$xml.= "<address><![CDATA[".$a["user_address1"]."]]></address>";
	$xml.= "<city><![CDATA[".$a["user_city"]."]]></city>";
	$xml.= "<state><![CDATA[".$a["user_state"]."]]></state>";
	if($langpref=="en") $xml.= "<link><url><![CDATA[".$dPconfig['base_url']."/?m=hhrr&a=viewhhrr&id=".$a["hrid"]."]]></url><label>More...</label></link>";
	else $xml.= "<link><url><![CDATA[".$dPconfig['base_url']."/?m=hhrr&a=viewhhrr&id=".$a["hrid"]."]]></url><label>Ver Mas</label></link>";
	$xml.= "</candidate>";

  }
  $xml.= "</candidates>";
}

?>
