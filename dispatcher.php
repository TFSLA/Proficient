<?php

$license_key="a010a2aabb1f83fbc2b75a2aa494e89b";						// Md5('nombre de la empresa')
include ('includes/license.php');
error_reporting( E_ALL & ~E_NOTICE);

is_file( "./includes/config.php" )
	or die( "Fatal Error.  You haven't created a config file yet." );

// required includes for start-up
$dPconfig = array();
require_once( "./includes/config.php" );
if(! is_file( "./includes/mktime_bug/mktime_difference.php" )){
        include_once("./includes/mktime_bug/mktime_update_difference.php");
}
require_once( "./classes/ui.class.php" );
require_once( "./includes/main_functions.php" );

// Check that the user has correctly set the root directory
is_file( "{$dPconfig['root_dir']}/includes/config.php" ) or die( "FATAL ERROR: Root directory in configuration file probably incorrect." );

$AppUI = new CAppUI();
$_SESSION['AppUI'] = new CAppUI();
$AppUI =& $_SESSION['AppUI'];
$AppUI->setConfig( $dPconfig );
$AppUI->checkStyle();
// load the commonly used classes
require_once( $AppUI->getSystemClass( 'date' ) );
require_once( $AppUI->getSystemClass( 'dp' ) );
require_once( $AppUI->getSystemClass( 'userlogs' ) );
require_once( $AppUI->getSystemClass( 'pager' ) );
require_once( $AppUI->getSystemClass( 'libmail' ) );
// load the db handler
require_once( "./includes/db_connect.php" );
require_once( "./misc/debug.php" );

// Recibe el "tag" en el primer parametro de linea de comando
// Normalmente el tag indicara la periodicidad del proceso

$tag = ($argv[1] ? $argv[1] : $_GET["TAG"]);
$debug = false;
$resultalerts = mysql_query("SELECT * FROM  emailalerts WHERE emailalert_tag = '$tag' AND emailalert_module <> '';");

// Vacia listas de envios (preventivo si existiese carga previa)
unset($recipients);
unset($messages);
unset($subjects);

// Iterar por cada alerta con la periodicidad especificada.
// Solo se toman en cuenta alertas que tengan definido un modulo de proceso.
while ($rowalerts = mysql_fetch_array($resultalerts, MYSQL_ASSOC)) {
  $emailalert_id = $rowalerts["emailalert_id"];
  $emailalert_xsl_es = $rowalerts["xsl_file_es"];
  $emailalert_xsl_en = $rowalerts["xsl_file_en"]; 
  $resultua = mysql_query("SELECT * FROM  useralerts WHERE emailalert_id = '$emailalert_id';");
  // Iterar por cada usuario que tenga relacion con la alerta.
  while ($rowua = mysql_fetch_array($resultua, MYSQL_ASSOC)) {
    $params  = $rowua["params"];
    $user_id   = $rowua["user_id"];

    $langpref = "es";
    $resultlc = mysql_query("SELECT * FROM user_preferences WHERE pref_user = '$user_id' AND pref_name = 'LOCALE';");
    if(mysql_numrows($resultlc)>0){
      $rowlc = mysql_fetch_array($resultlc, MYSQL_ASSOC);
      if($rowlc["pref_value"]=="es") $langpref = "es";
      if($rowlc["pref_value"]=="en") $langpref = "en";
    }
    if($langpref=="es") $emailalert_xsl = $emailalert_xsl_es;
    if($langpref=="en") $emailalert_xsl = $emailalert_xsl_en;

    // Lees permisos para el usuario en el modulo
    $sql = "SELECT user_username FROM users WHERE user_id = '$user_id' ";
    $row = null;
    db_loadObject( $sql, $row );
    $AppUI->login($row->user_username,"",false);
    require_once("./includes/permissions.php");

    // Procesa solo si tiene permisos
    if(!getDenyRead( $rowalerts["emailalert_module"])){
       $recipient = $rowua["recipient"];
       // Carga datos del usuario
       $resultu = mysql_query("SELECT * FROM  users WHERE user_id = '$user_id';");
       $rowu = mysql_fetch_array($resultu, MYSQL_ASSOC);
       $user_username   = $rowu["user_username"];
       $user_first_name = $rowu["user_first_name"];
       $user_last_name  = $rowu["user_last_name"];
       if($recipient=="")$recipient=$rowu["user_email"];
       // Evalua si se envia email, y en caso positivo agrega al array de recipients
       $xml="";
       include( "modules/emailalerts/".$rowalerts["emailalert_program"] );
       // Si se genero salida aplica transformacion XSL al XML de salida.
       if($debug) echo $xml."\n";
       if($xml!=""){
	   //echo "XSL: ".$emailalert_xsl."<br/>";
	   //echo "TO:: ".$recipient."<br/>";
	   //echo "XML: <br/>".$xml."<br/>";
	   $xsl_file = $emailalert_xsl;
	   // Alocar XSLT processor
	   //$xh = xslt_create();
		
		$objXml = new DOMDocument(); 
		$objXml->loadXML( $xml );
		
		$objXslt = new XSLTProcessor(); 
		$objXsl = new DOMDocument(); 
		$objXsl->load( 'modules/emailalerts/'.$xsl_file, LIBXML_NOCDATA); 
		$objXslt->importStylesheet( $objXsl ); 
		
		$message = $objXslt->transformToXML( $objXml );
	   
	   //$fileBase = 'file://' . getcwd () . '/modules/emailalerts/';
	   //xslt_set_base ( $xh, $fileBase );
	   //$args = array('/_xml'    =>    $xml);
	   //$message = xslt_process($xh, 'arg:/_xml', $xsl_file, NULL, $args);
	   if (!$message) echo 'XSLT processing error: ' .xslt_error($xh) ;
	   
           // Destruye el XSLT processor
	   //xslt_free($xh);
	   //Crea el mensaje con el HTML generado por la transformacion
  	   $recipients[] = $recipient;
	   $subjects[]   = $subject;
	   $messages[]   = $message;
       }
    }
  }
}

  // Enviar emails segun salida del modulo de alertas (solo si hay emails que enviar)
  if(is_array($recipients))
  foreach ($recipients as $i => $recipient) {
    $message = $messages[$i];
    $subject = $subjects[$i];
    if($debug)echo "idx: ".$i." value:".$recipient ." msg:". $message."\n";

    // Enviar el mensaje mediante el subsistema de email de Profient
    $m= new Mail;
    $m->From($dPconfig['mailfrom']); // Usa el FROM de la configuracion base
    $m->To($recipient);
    $m->Subject($subject,"utf-8");
    $m->IsHtml(true);
    $m->Body($message);
    $m->Send();
  }

  
function html2ascii($s){
 // convert links
 $s = preg_replace('/<a\s+.*?href="?([^\" >]*)"?[^>]*>(.*?)<\/a>/i','$2 ($1)',$s);

 // convert p, br and hr tags
 $s = preg_replace('@<(b|h)r[^>]*>@i',"\n",$s);
 $s = preg_replace('@<p[^>]*>@i',"\n\n",$s);
 $s = preg_replace('@<div[^>]*>(.*)</div>@i',"\n".'$1'."\n",$s);

 // convert bold and italic tags
 $s = preg_replace('@<b[^>]*>(.*?)</b>@i','*$1*',$s);
 $s = preg_replace('@<strong[^>]*>(.*?)</strong>@i','*$1*',$s);
 $s = preg_replace('@<i[^>]*>(.*?)</i>@i','_$1_',$s);
 $s = preg_replace('@<em[^>]*>(.*?)</em>@i','_$1_',$s);

 // decode any entities
 $s = strtr($s,array_flip(get_html_translation_table(HTML_ENTITIES)));

 // decode numbered entities
 $s = preg_replace('/&#(\d+);/e','chr(str_replace(";","",str_replace("&#","","$0")))',$s);

 // strip any remaining HTML tags
 $s = strip_tags($s);

 // return the string
 return $s;
}  

?>