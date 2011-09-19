<? 
$sql = "
SELECT forum_watch.*, forums.forum_id as fid, forum_messages.*, users.*, forums.forum_name, DATE_FORMAT(forum_messages.message_date,'%d/%m/%Y') as datef, DATE_FORMAT(forum_messages.message_date,'%T') as timef
FROM forum_watch, forum_messages, forums, users
WHERE forum_watch.watch_user = '$user_id'
AND (forum_watch.watch_forum = forum_messages.message_forum OR 
     forum_watch.watch_topic = forum_messages.message_parent)
AND message_date > DATE_SUB(CURDATE(), INTERVAL 1 DAY)
AND forum_messages.message_author = users.user_id
AND forums.forum_id = forum_messages.message_forum
ORDER BY message_date 
";
$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $subject   = "Foros - Movimientos de Foros.";
  $message   = "";
  $xml.= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><messages>";
  foreach ($rows as $a) {
	if($a["message_parent"]<>-1){
                $sql = "SELECT * FROM forum_messages WHERE forum_messages.message_id = ".$a["message_parent"];
		$rowd = db_loadList( $sql, NULL );
		$topic = $rowd[0]["message_title"];
        }
        else $topic = $a["message_title"];
	$xml.= "<message>";
	$xml.= "<forum><![CDATA[".$a["forum_name"]."]]></forum>";
	$xml.= "<topic><![CDATA[".$topic."]]></topic>";
	$xml.= "<title><![CDATA[".$a["message_title"]."]]></title>";
	$xml.= "<body><![CDATA[".$a["message_body"]."]]></body>";
	$xml.= "<authorfn><![CDATA[".$a["user_first_name"]."]]></authorfn>";
	$xml.= "<authorln><![CDATA[".$a["user_last_name"]."]]></authorln>";
	$xml.= "<date><![CDATA[".$a["datef"]."]]></date>";
	$xml.= "<time><![CDATA[".$a["timef"]."]]></time>";

	if($langpref=="en") $xml.= "<link><url><![CDATA[".$dPconfig['base_url']."/?m=forums&a=viewer&forum_id=".$a["fid"]."&message_id=".$a["message_id"]."]]></url><label>More...</label></link>";
	else $xml.= "<link><url><![CDATA[".$dPconfig['base_url']."/?m=forums&a=viewer&forum_id=".$a["fid"]."&message_id=".$a["message_id"]."]]></url><label>Ver mas</label></link>";

	$xml.= "</message>"; 
  }
  $xml.= "</messages>";
}

?>
