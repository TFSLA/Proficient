<?php
function enviar_mail($destino, $txt, $asunto){
	global $text_loc;
	$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
	$cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$cabeceras .='From: info@tfsla.com <info@tfsla.com>' . "\r\n";
	//echo "mail a $destino - $asunto";
	if ( ! mail($destino, $asunto, $txt, $cabeceras ) )
		echo "Error al enviar un mail a:".$destino;
	//else
		//echo "Se envio un mail a: ".$destino." con los Todos pendientes<br>";
}

function trans($string){
	global $text_loc;
	set($string);
	return (current($text_loc));
}

function set($key){
	global $text_loc;
   reset($text_loc);
   while($current=key($text_loc))
   {
       if($current==$key)
       {
           return true;
       }
       next($text_loc);
   }
   return false;
}

function locales($lang='en', $file, $path=''){ 
	//echo 'locales/'.$lang.'/'.$file;
	$file = fopen($path.'locales/'.$lang.'/'.$file, "r");
	while(!feof($file)) { 
	    $text.=fgets($file, 4096); 
	} 
	eval ("\$text_loc = array($text);");
	//print_r ($text_loc);
	fclose ($file);
	return $text_loc;
}
?>