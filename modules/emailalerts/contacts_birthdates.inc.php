<?
$sql = "
SELECT contacts.*, DATE_FORMAT(contacts.contact_birthday,'%d/%m/%Y') as birthdate
FROM contacts
WHERE DAYOFMONTH(CURDATE()) = DAYOFMONTH(contact_birthday) AND MONTH(CURDATE()) = MONTH(contact_birthday) 
AND (contact_public = '1' OR contact_owner = '$user_id')
ORDER BY contact_first_name 
";

$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $subject   = "Contactos - Cumplea¤os del dia.";
  $message   = "";
  $xml.= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><birthdates>";
  foreach ($rows as $a) {
	$xml.= "<birthdate>";
	$xml.= "<firstname><![CDATA[".$a["contact_first_name"]."]]></firstname>";
	$xml.= "<middlename><![CDATA[".$a["contact_middle_name"]."]]></middlename>";
	$xml.= "<lastname><![CDATA[".$a["contact_last_name"]."]]></lastname>";
	$xml.= "<email><![CDATA[".$a["contact_email"]."]]></email>";
	$xml.= "<birthdate><![CDATA[".$a["birthdate"]."]]></birthdate>";
	$xml.= "<phone><![CDATA[".$a["contact_phone"]."]]></phone>";
	$xml.= "<mobile><![CDATA[".$a["contact_mobile"]."]]></mobile>";
	$xml.= "</birthdate>";
  }
  $xml.= "</birthdates>";
}

?>
