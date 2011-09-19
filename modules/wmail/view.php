<?php

/////////////////////////////////////////////////////////
//
//	source/view.php
//
//	(C)Copyright 2000-2002 Ryo Chijiiwa <Ryo@IlohaMail.org>
//
//		This file is part of IlohaMail.
//		IlohaMail is free software released under the GPL
//		license.  See enclosed file COPYING for details,
//		or see http://www.fsf.org/copyleft/gpl.html
//
/////////////////////////////////////////////////////////

/********************************************************

	AUTHOR: Ryo Chijiiwa <ryo@ilohamail.org>
	FILE: view.php
	PURPOSE:
		Display message part data (whether it be text, images, or whatever).  Decode as necessary.
		Sets HTTP "Content-Type" header as appropriate, so that the browser will (hopefully) know
		what to do with the data.
	PRE-CONDITIONS:
		$user - Session ID
		$folder - Folder in which message to open is in
		$id - Message ID (not UID)
		$part - IMAP (or MIME?) part code to view.

********************************************************/

include_once("./modules/wmail/include/super2global.inc");
global $host, $loginID, $password, $AUTH_MODE, $folder, $session, $id, $part, $port, $my_prefs;
$user    = $session;

include_once("./modules/wmail/include/nocache.inc");


if ((isset($user))&&(isset($folder))){
	include_once("./modules/wmail/include/session_auth.inc");
	include_once("./modules/wmail/include/icl.inc");
	$view_conn=iil_Connect($host, $loginID, $password, $AUTH_MODE);
	if ($iil_errornum==-11){
		for ($i=0; (($i<10)&&(!$view_conn)); $i++){
			sleep(1);
			$view_conn=iil_Connect($host, $loginID, $password, $AUTH_MODE);
		}
	}
	if (!$view_conn){
		echo "failed\n".$iil_error;
		flush();
	}else{

		// Let's look for MSIE as it needs special treatment
		if  (strpos (getenv('HTTP_USER_AGENT'), "MSIE"))
			$DISPOSITION_MODE="inline";
		else
			$DISPOSITION_MODE="attachment";
			
		if($_GET["forcedownload"]==1){
			$DISPOSITION_MODE="attachment";
		}
		if($_GET["viewinline"]==1 || $_GET["viewinframe"]==1){
			$DISPOSITION_MODE="inline";
		}		
		if (isset($source)){
			//show source
			header("Content-type: text/plain");
			iil_C_PrintSource(&$view_conn, $folder, $id, $part);
		}else if ($show_header){
			//show header
			header("Content-Type: text/plain");
			$header = iil_C_FetchPartHeader($view_conn, $folder, $id, $part);
			//$header = str_replace("\r", "", $header);
			//$header = str_replace("\n", "\r\n", $header);
			echo "<pre>";
			echo $header;
			echo "</pre>";
		}else if ($printer_friendly){
			//show printer friendly version
			include_once("./modules/wmail/include/mime.inc");
			include_once("./modules/wmail/include/ryosimap.inc");
			include("./modules/wmail/lang/".$my_prefs["charset"].".inc");

			//get message info
			$conn = $view_conn;
			$header = iil_C_Fetchheader($conn, $folder, $id);
			$structure_str=iil_C_FetchStructureString($conn, $folder, $id);
			$structure=iml_GetRawStructureArray($structure_str);
			$num_parts=iml_GetNumParts($structure, $part);
			$parent_type=iml_GetPartTypeCode($structure, $part);
			$uid = $header->uid;

			//get basic header fields
			$subject = encodeHTML(LangDecodeSubject($header->subject, $my_prefs["charset"]));
			$from = LangShowAddresses($header->from,  $my_prefs["charset"], $user);
			$to = LangShowAddresses($header->to,  $my_prefs["charset"], $user);
			if (!empty($header->cc)) $cc = LangShowAddresses($header->cc,  $my_prefs["charset"], $user);
			else $cc = "";

			header("Content-type: text/html");

			//output
			?>
			<html>
			<head><title><?php echo $subject ?></title></head>
			<body>
			<?php
			echo "<b>".$AppUI->_("Subject").":&nbsp;</b>$subject<br>\n";
			echo "<b>".$AppUI->_("Date").":&nbsp;</b>".htmlspecialchars($header->date)."<br>\n";
			echo "<b>".$AppUI->_("From").":&nbsp;</b>".$from."<br>\n";
			echo "<b>".$AppUI->_("To").":&nbsp;</b>".$to."<br>\n";
			if (!empty($cc)) echo "<b>".$AppUI->_("CC").":&nbsp;</b>".$cc."<br>\n";
//20094
			include("./modules/wmail/include/read_message_handler.inc");
			?>
			</body>
			</html>
			<script language="javascript">
			if (window.print)
			    window.print();			
			</script>
			<?php

		}else if(isset($tneffid)){
			//show ms-tnef
			include_once("./modules/wmail/include/header_main.inc");
			//include_once("./modules/wmail/include/icl.inc");
			//include_once("./modules/wmail/include/cache.inc");			
			include_once("./modules/wmail/include/mime.inc");
			include_once("./modules/wmail/include/tnef_decoder.inc");
			$structure_str=iil_C_FetchStructureString($view_conn, $folder, $id);
			$structure=iml_GetRawStructureArray($structure_str);
			$type=iml_GetPartTypeCode($structure, $part);
			$typestring=iml_GetPartTypeString($structure, $part);
			list($type, $subtype) = explode("/", $typestring);
			$body=iil_C_FetchPartBody($view_conn, $folder, $id, $part);
			$encoding=iml_GetPartEncodingCode($structure, $part);
			if ($encoding == 3 ) $body=base64_decode($body);
			else if ($encoding == 4) $body=quoted_printable_decode($body);
			$charset=iml_GetPartCharset($structure, $part);
			if (strcasecmp($charset, "utf-8")==0){
				include_once("./modules/wmail/include/utf8.inc");
				$is_unicode = true;
				$body = utf8ToUnicodeEntities($body);
			}else{
				$is_unicode = false;
			}
			$body=LangConvert($body, $my_charset, $charset);
			$tnef_files=tnef_decode($body);
			header("Content-type: ".$tnef_files[$tneffid]['type0']."/".$tnef_files[$tneffid]['type1']."; name=\"".$tnef_files[$tneffid]['name']."\"");
			header("Content-Disposition: ".$DISPOSITION_MODE."; filename=\"".$tnef_files[$tneffid]['name']."\"");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Pragma: public");
			echo($tnef_files[$tneffid]['stream']);
		}else{
			//everythign else
			include("./modules/wmail/include/mime.inc");

			// fetch relevant data (i.e. MIME structure, type codes, etc)
			$header_obj = iil_C_FetchHeader($view_conn, $folder, $id);
			$structure_str=iil_C_FetchStructureString($view_conn, $folder, $id);
			$structure=iml_GetRawStructureArray($structure_str);
			$type=iml_GetPartTypeCode($structure, $part);
			if ($is_html) $typestr = "text/html";
			else if (empty($part) || $part==0) $typestr = $header_obj->ctype;
			else $typestr = iml_GetPartTypeString($structure, $part);
/*	PARA FORZAR DOWNLOAD */
/*		
		header("Content-Type: application/force-download; name=\"$filename\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: $size");
		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Expires: 0");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache"); 			*/
		/*
			if ($typestr != "text/html"){
				$typestr = "application/force-download";
				$DISPOSITION_MODE = "attachment";
			}*/
			//$DISPOSITION_MODE = "inline";
			
			// structure string
			if ($show_struct){
				echo $structure_str;
					exit;
			}

			// format and send HTTP header
			if ($type==$MIME_APPLICATION){
				$name = str_replace("/",".",iml_GetPartName($structure, $part));
				header("Content-type: $typestr; name=\"".$name."\"");
				header("Content-Disposition: ".$DISPOSITION_MODE."; filename=\"".$name."\"");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Pragma: public");
			}else if ($type==$MIME_MESSAGE){
				$name=str_replace("/",".", iml_GetPartName($structure, $part));
				header("Content-Type: text/plain; name=\"".$name."\"");
			}else if ($type != $MIME_INVALID){
				$charset=iml_GetPartCharset($structure, $part);
				$name=str_replace("/",".", iml_GetPartName($structure, $part));
				$header="Content-type: $typestr";
				if (!empty($charset)) $header.="; charset=\"".$charset."\"";
				if (!empty($name)) $header.="; name=\"".$name."\"";
				header($header);
				if ($type!=$MIME_TEXT && $type!=$MIME_IMAGE){
					header("Content-Disposition: ".$DISPOSITION_MODE."; filename=\"".$name."\"");
					header("Expires: 0");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Pragma: public");
				}else if (!empty($name)){
					if ($viewinline=="1"){
						header("Content-Disposition: inline; filename=\"".$name."\"");
					} else { 
						header("Content-Disposition: attachment; filename=\"".$name."\"");
					}
				}
			}else{
				if ($debug) echo "Invalid type code!\n";
			}
			if ($debug) echo "Type code = $type ;\n";

			// send actual output
			if ($print){
				// straight output, no processing
				iil_C_PrintPartBody($view_conn, $folder, $id, $part);
				if ($debug) echo $view_conn->error;
			}else{
				// process as necessary, based on encoding
				$encoding=iml_GetPartEncodingCode($structure, $part);
				$num_parts=iml_GetNumParts($structure, 0);
				if ($debug) echo "Part code = $encoding;\n";

				ob_start();
				if ($raw){
					iil_C_PrintPartBody($view_conn, $folder, $id, $part);
				}else if ($encoding==3){
					// base 64
					if ($debug) echo "Calling iil_C_PrintBase64Body\n"; flush();
					iil_C_PrintBase64Body($view_conn, $folder, $id, $part);
				}else if ($encoding == 4){
					// quoted printable
					$body = iil_C_FetchPartBody($view_conn, $folder, $id, $part);
					if ($debug) echo "Read ".strlen($body)." bytes\n";
					$body=quoted_printable_decode(str_replace("=\r\n","",$body));
					$charset=iml_GetPartCharset($structure, $part);
					if (strcasecmp($charset, "utf-8")==0){
						include_once("./modules/wmail/include/utf8.inc");
						$body = utf8ToUnicodeEntities($body);
					}				
					echo $body;
				}else{
					// otherwise, just dump it out
					iil_C_PrintPartBody($view_conn, $folder, $id, $part);
				}
				$body = ob_get_contents();
				ob_end_clean();
				
				// detecto si alguna de las partes es imagen y reemplazo en el body con el link correspondiente	
				$images = array();
				for ($i=1;$i<=$num_parts;$i++){
					//get attachment info
					if ($parent_type == 1)
						$codepart=$part.(empty($part)?"":".").$i;
					else if ($parent_type == 2){
						$codepart=$part.(empty($part)?"":".").$i;
						//$parts_text .= implode(" ", iml_GetPartArray($structure, $code));
					}
					$code = $i;
					$type=iml_GetPartTypeCode($structure, $code);
				
					if ($type==5){
						$part_id=iml_GetPartID($structure, $code);
						if ($part_id !=""){
							$images[$part_id] =	"?m=wmail&a=bridge&tab=0&suppressHeaders=true&xa=view&session=$user&folder=$folder_url&id=$id&part=$i&viewinline=1";
							if (strpos($body, "cid:$part_id")){
								$body = str_replace("cid:$part_id", $images[$part_id], $body);
							}		
						}
					}
				}	
				
				//reemplazo el target de todos los links y dejo solo los tags permitidos
				if($_GET["viewinframe"]==1){
					
					$body = eregi_replace("<!--", '{@@@@comment@@@@}"', $body);
					$body = eregi_replace("-->", '{/@@@@comment@@@@}"', $body);
					$body=strip_tags($body, '<a><b><i><u><p><br><hr><font><div><style><img><table><tr><td><th><col><span><ul><ol><li>');				
					$body = eregi_replace('{@@@@comment@@@@}"', "<!--", $body);
					$body = eregi_replace('{/@@@@comment@@@@}"', "-->", $body);					
					$body = eregi_replace("<a ", '<a target="_blank" ', $body);
				}
				echo $body;						
				
				
				if ($debug) echo $view_conn->error;
			}
		}
		iil_Close($view_conn);
	}
}


function echo_header($str=""){
	echo $str;
}
?>
