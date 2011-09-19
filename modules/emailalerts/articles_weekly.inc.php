<?  
$sql = "
SELECT articles.*, DATE_FORMAT(articles.date,'%d/%m/%Y') as datef, users.*
FROM articles, users
WHERE SUBSTRING(date,1,10) > DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND users.user_id = articles.user_id
AND articles.articlesection_id <> 0
ORDER BY date
";

$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $subject   = "Base de Conocimiento- Publicaciones de la semana.";
  $message   = "";
  $xml.= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><publicaciones>";
  foreach ($rows as $a) {
	$xml.= "<publicacion>";
	$xml.= "<date><![CDATA[".$a["datef"]."]]></date>";
	$xml.= "<title><![CDATA[".$a["title"]."]]></title>";
	$xml.= "<username><![CDATA[".$a["user_username"]."]]></username>";
	$xml.= "<firstname><![CDATA[".$a["user_first_name"]."]]></firstname>";
	$xml.= "<lastname><![CDATA[".$a["user_last_name"]."]]></lastname>";
	$xml.= "<abstract><![CDATA[".$a["abstract"]."]]></abstract>";
	if($langpref=="en") $xml.= "<link><url><![CDATA[".$dPconfig['base_url']."/index_inc.php?inc=./modules/articles/vwlink.php&m=articles&id=".$a["article_id"]."]]></url><label>More...</label></link>";
	else $xml.= "<link><url><![CDATA[".$dPconfig['base_url']."/index_inc.php?inc=./modules/articles/vwlink.php&m=articles&id=".$a["article_id"]."]]></url><label>Ver Mas</label></link>";
	$xml.= "</publicacion>";
  }
  $xml.= "</publicaciones>";
}

?>
