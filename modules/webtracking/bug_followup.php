<?
/**
 * Una vez resuelto un bug, si tiene emails asociados en la tabla btpsa_email_followup, se les envia un mensaje
 *
 * @param  integer $f_bug_id = id de la incidencia
 * @param  string   $f_bugnote_text  = Texto de resolucion de la incidencia
 */
function send_followup_mails( $f_bug_id,  $f_bugnote_text )
{
	global $AppUI, $dPconfig;
	
	$t_bug_table = config_get( 'mantis_bug_table' );
	
	// Traigo los mails 
	$query = "SELECT email, name FROM btpsa_email_followup WHERE bug_id='".$f_bug_id."' ";
	$result = db_query($query);

	$cantidad = db_num_rows($result);
	
	if($cantidad>0)
	{
	      // Traigo los datos de la incidencia para armar el mensaje
	      $query_bug = "SELECT  b.summary, b.reporter_id, b.handler_id, b.project_id , p.project_email_support, concat( u.user_last_name , ' ', u.user_first_name) as handler , u.user_job_title, t.description  
			      FROM ".$t_bug_table." as b, projects as p, users as u, btpsa_bug_text_table as t
			      WHERE b.id='".$f_bug_id."'
			      AND p.project_id = b.project_id
			      AND u.user_id = b.handler_id
			      AND t.id = b.id";
	      //echo "<pre>$query_bug</pre>";
                  $result_bug = db_query($query_bug);
	      $data_bug = db_fetch_array($result_bug);
	  
	      $sql_handler = "SELECT  concat( user_last_name , ' ', user_first_name) as handler, user_job_title, user_email FROM users WHERE user_id='".$AppUI->user_id."' ";
	      	//echo "<pre>$sql_handler </pre>";
	      $result_handler = db_query($sql_handler);
	      $data_handler =  db_fetch_array($result_handler);
	      	
	      $handler = $data_handler['handler'];
	      $user_job_title = $data_handler['user_job_title'];
	      $sender_email = $data_handler['user_email'];
	      
	      $summary = $data_bug['summary'];
	      $resolution_text =  utf8_encode($f_bugnote_text);  
	      
	      $subject   = $summary;
	      $message   = "";
	      
	      // Recorro la lista de mails a enviar
	      while($row = db_fetch_array($result)) 
	      {
	      	$receptor = $row['email'];
	      	
	      	$sxml.= "<?xml version=\"1.0\" encoding=\"UTF-8\"?><publicaciones>";
	      	$sxml.= "<publicacion>";
	      	$sxml.="<logo><![CDATA[".$dPconfig['base_url']."/images/logo_calista.jpg]]></logo>";
	      	$sxml.="<namereceptor><![CDATA[".$row['name']."]]></namereceptor>";
	            $sxml.="<resolutiontext><![CDATA[".$resolution_text."]]></resolutiontext>";
	            $sxml.="<handler><![CDATA[".$handler."]]></handler>";
	            $sxml .="<cargousuario><![CDATA[".$user_job_title."]]></cargousuario>";
		$sxml.= "</publicacion>";
		$sxml.= "</publicaciones>";
		
		# LOAD XML FILE 
		$XML = new DOMDocument(); 
		$XML->loadXML( $sxml ); 
		
                       $XSL = new DOMDocument('1.0','UTF-8'); 
                       $XSL->loadXML(file_get_contents($dPconfig['root_dir'].'/modules/webtracking/bug_followup_email.xsl')); 
                       $XSL->documentURI = $dPconfig['root_dir'].'/modules/webtracking/bug_followup_email.xsl'; 
		
		$xslt = new XSLTProcessor(); 
		$xslt->importStylesheet( $XSL ); 
		
		#PRINT 
		$message = $xslt->transformToXML( $XML ); 
		
		$recipients[] =$receptor;
		$subjects[]   = $subject;
		$messages[]   = $message;
		
	      }
	      
	    if(is_array($recipients))
	       foreach ($recipients as $i => $recipient) {
		    $message = $messages[$i];
		    $subject = $subjects[$i];
		   
		    if ($sender_email==""){ $sender_email =$dPconfig['mailfrom']; }
		    
		    $sender_email = "$handler <$sender_email>";
		    
		    echo "sender mail: $sender_email <br>";
		    echo "Mail por defecto: ".$dPconfig['mailfrom']."<br>";
		    echo "Destinatario: ".$recipient."<br>";
		    echo "Message: ".$message; 
		    
		    $m= new Mail;
		    $m->From($sender_email); 
		    $m->To($recipient);
		    $m->Subject($subject,"utf-8");
		    $m->IsHtml(true);
		    $m->Body($message);
		    $m->Send();
	       }

    
	}else{
		return;
	}
}

?>