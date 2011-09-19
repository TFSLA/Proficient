<?php
/////////////////////////////////////////////////////////
//	
//	source/compose2.php
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
	FILE:  source/compose.php
	PURPOSE:
		1.  Provide interface for creating messages
		2.  Provide interface for uploading attachments
		3.  Form MIME format (RFC822) compliant messages
		4.  Send message
		5.  Save to "sent items" folder if so specified
	PRE-CONDITIONS:
		$user - Session ID for session validation and user preference retreaval
	POST-CONDITIONS:
		Displays standard message composition interface by default
		If "upload" button pressed, displays all inputted text and attachment info
		If "send" button pressed, sends, files, and displays status
	COMMENTS:
	
********************************************************/


include_once("./modules/wmail/include/super2global.inc");
$session = $GLOBALS["session"];
$folder  = $GLOBALS["folder"];
$user    = $session;
$upload  =  $upload_file == 1 ? "Upload" : $upload;
include_once("./modules/wmail/include/header_main.inc");
//incluido en optionfolders.inc.php
//include_once("./modules/wmail/lang/".$my_prefs["lang"]."compose.inc");
global $composeHStrings;
global $composeStrings;
global $composeErrors;
include_once("./modules/wmail/lang/".$my_prefs["lang"]."dates.inc");
include_once("./modules/wmail/include/icl.inc");
include_once("./modules/wmail/include/version.inc");
include_once("./modules/wmail/conf/defaults.inc");
include_once("./modules/wmail/include/javascript.inc");


?>
		<script type="text/javascript" language="JavaScript1.2">
		var contacts_popup_visible=false;
		var contacts_popup;
		
		function readTo(){
			return document.messageform.to.value;
		}
		
		function readCc(){
			return document.messageform.cc.value;
		}
		
		function readBcc(){
			return document.messageform.bcc.value;
		}	
		function writeTo(val){ document.messageform.to.value = val;}						
		function writeCc(val){ document.messageform.cc.value = val;}						
		function writeBcc(val){ document.messageform.bcc.value = val;}						
		
		function CopyAdresses() {
			switch (document.messageform.to_a_field.selectedIndex) {
			case 1:
				var target = document.messageform.cc;
				break;
			case 2:
				var target = document.messageform.bcc;
				break;
			default:
				var target = document.messageform.to;
			}
			var selbox=document.messageform.elements['to_a[]'];
			for (var i=0; selbox.length>i; i++) {
				if ((selbox.options[i].selected == true) &&
		 		 (target.value.indexOf(selbox.options[i].text, 0)==-1)) { //A check to prevent adresses from getting listed twice.
					if (target.value != '') 
						target.value += ', ';
					target.value += selbox.options[i].text;
				}
			}
		}
		
		function DeselectAdresses() {
			var selbox = document.messageform.elements['to_a[]'];
			if (selbox) {
				for (var i=0; selbox.length>i; i++)
					selbox.options[i].selected = false;
			}
		}
		
		function DoCloseWindow(redirect_url){
/*			if(parent.frames.length!=0){
				parent.list2.location=redirect_url;
			}else{
				window.close();
			}
*/
		}		
		
		function fixtitle(title_str) {
			if (document.messageform.subject.value=='')
				document.title=title_str;
			else
				document.title=title_str+": "+document.messageform.subject.value;
		}
		
		function open_popup(comp_uri) {
			if (comp_uri) {
				if (contacts_popup_visible==false) {
					contacts_popup = window.open(comp_uri+"&suppressLogo=true&dialog=1", "_blank","width=700,height=600,scrollbars=yes,resizable=yes");
					if (contacts_popup.opener == null)
					contacts_popup.opener = window;
				}
				contacts_popup.focus();
			}
			return;
		}
		
  		function close_popup(){
			if (contacts_popup_visible)
  				contacts_popup.close();
  		}
  		
  		function uploadFile(){
  			var frm = document.messageform;
  			frm.upload_file.value = 1;
  			if (trim(frm.userfile.value)!=""){
  				alert("<?php echo $AppUI->_("wmailNonUploadedFile");?>");
  			}; 
  			frm.submit();  		
  		}
  		
  		function validate_message(){
  			DeselectAdresses(); 
  			close_popup(); 

  			var frm = document.messageform;
  			var rta = true;

  			var emailfield = frm.to;
  			if (trim(emailfield.value)=="" || !isEmailField(trim(emailfield.value))){
	  				alert('"' + to_label + '"' + "<?php echo ": ".$AppUI->_("Please enter a valid email address");?>");
	  				emailfield.focus();
	  				return false;  			
  			};

  			var emailfield = frm.cc;
  			if (trim(emailfield.value)!="" && !isEmailField(trim(emailfield.value))){
	  				alert('"' + cc_label + '"' +  "<?php echo ": ".$AppUI->_("Please enter a valid email address");?>");
	  				emailfield.focus();
	  				return false;  			
  			}; 			
  			var emailfield = frm.bcc;
  			if (trim(emailfield.value)!="" && !isEmailField(trim(emailfield.value))){
	  				alert('"' + bcc_label + '"' + "<?php echo ": ".$AppUI->_("Please enter a valid email address");?>");
	  				emailfield.focus();
	  				return false;  			
  			};  			
  			if (trim(frm.subject.value)==""){
	  				if (!confirm("<?php echo $AppUI->_("wmailEmptySubject");?>")){
		  				frm.subject.focus();
		  				return false;  			  				
	  				};
  			};  			
  			if (trim(frm.userfile.value)!=""){
  				alert("<?php echo $AppUI->_("wmailNonUploadedFile");?>");
  			}; 
  			  			
  			return true;
  		}

		</SCRIPT>
	 <!-- <a href="javascript: alert(validate_message());">validar</a> -->
<?

if ($GPG_ENABLE){
	include_once("./modules/wmail/include/gpg.inc");
}

function RemoveDoubleAddresses($to) {
	$to_adr = iil_ExplodeQuotedString(",", $to);
	$adresses = array();
	$contacts = array();
	foreach($to_adr as $addr) {
		$addr = trim($addr);
		if (preg_match("/(.*<)?.*?([^\s\"\']+@[^\s>\"\']+)/", $addr, $email)) {
			$email = strtolower($email[2]);
			if (!in_array($email, $adresses)) {						//New adres
				array_push($adresses, $email);
				$contacts[$email] = $addr;
			} elseif (strlen($contacts[$email])<strlen($addr)) {				//Adres already in list and name is longer
				$contacts[$email] = trim($addr);
			}
		}
	}
	return implode(", ",$contacts);
}

function ResolveContactsGroup($str){
	global $contacts;
	
	$tokens = explode(" ", $str);
	if (!is_array($tokens)) return $str;
	
	while ( list($k,$token)=each($tokens) ){
		if (ereg("@contacts.group", $token)){
			if (ereg("^<", $token)) $token = substr($token, 1);
			list($group, $junk) = explode("@contacts.", $token);
			$group = base64_decode($group);
			$newstr = "";
			reset($contacts);
			while ( list($blah, $contact)=each($contacts) ){
				if ($contact["grp"]==$group && !empty($contact["email"])){
					$newstr.= (!empty($newstr)?", ":"");
					$newstr.= "\"".$contact["name"]."\" <".$contact["email"].">";
				}
			}
			if (ereg(",$", $token)) $newstr.= ",";
			$tokens[$k] = $newstr;
			if (ereg(str_replace(" ", "_", $group), $tokens[$k-1])) $tokens[$k-1] = "";
		}
	}
	
	return implode(" ", $tokens);
}


if (ini_get('file_uploads')!=1){
	echo $AppUI->_("Error:  Make sure the 'file_uploads' directive is enabled (set to 'On' or '1') in your php.ini file");
}



/******* Init values *******/
if (!isset($attachments)) $attachments=0;
if (isset($change_contacts)) $show_contacts = $new_show_contacts;
if (isset($change_show_cc)) $show_cc = $new_show_cc;

//$show_contacts = true;
//read alternate identities
include_once("./modules/wmail/include/data_manager.inc");
$ident_dm = new DataManager_obj;
if ($ident_dm->initialize($loginID, $host, $DB_IDENTITIES_TABLE, $DB_TYPE)){
	$alt_identities = $ident_dm->read();
}

//Handle ddresses submitted from contacts list 
//(in contacts window)

if (is_array($contact_to)) $to .= (empty($to)?"":", ").urldecode(implode(", ", $contact_to));
if (is_array($contact_cc)) $cc .= (empty($cc)?"":", ").urldecode(implode(", ", $contact_cc));
if (is_array($contact_bcc)) $bcc .= (empty($bcc)?"":", ").urldecode(implode(", ", $contact_bcc));
//(in compose window)
if ((isset($to_a)) && (is_array($to_a))){
    reset($to_a);
    while ( list($key, $val) = each($to_a)) $$to_a_field .= ($$to_a_field!=""?", ":"").stripslashes($val);
}

//generate authenticated email address
if (empty($init_from_address)){
	//$sender_addr = $loginID.( strpos($loginID, "@")>0 ? "":"@".$host );
	$sender_addr =  $AppUI->user_email; //( strpos($loginID, "@")>0 ? $loginID : $AppUI->user_email );
}else{
	$sender_addr = str_replace("%u", $loginID, str_replace("%h", $host, $init_from_address));
}

//generate user's name
$from_name = $my_prefs["user_name"];
$from_name = LangEncodeSubject($from_name, $my_charset);
if ((!empty($from_name)) && (count(explode(" ", $from_name)) > 1)) $from_name = "\"".$from_name."\"";

if ($TRUST_USER_ADDRESS){
    //Honor User Address
    //If email address is specified in prefs, use that in the "From"
    //field, and set the Sender field to an authenticated address
    $from_addr = (empty($my_prefs["email_address"]) ? $sender_addr : $my_prefs["email_address"] );
    $from = $from_name." <".$from_addr.">";
    $reply_to = "";
}else{
    //Default
    //Set "From" to authenticated user address
    //Set "Reply-To" to user specified address (if any)
	$from_addr = $sender_addr;
    $from = $from_name." <".$sender_addr.">";
    if (!empty($my_prefs["email_address"])) $reply_to = $from_name."<".$my_prefs["email_address"].">";
    else $reply_to = "";
}
$original_from = $from;

echo "\n<!-- FROM: $original_from //-->\n";


//resolve groups added from contacts selector
$to_has_group = $cc_has_group = $bcc_has_group = false;
if (!empty($to)) $to_has_group = ereg("@contacts.group", $to);
if (!empty($cc)) $cc_has_group = ereg("@contacts.group", $cc);
if (!empty($bcc)) $bcc_has_group = ereg("@contacts.group", $bcc);
if ($to_has_group || $cc_has_group || $bcc_has_group){
	$dm = new DataManager_obj;
	if ($dm->initialize($loginID, $host, $DB_CONTACTS_TABLE, $DB_TYPE)){
		if (empty($sort_field)) $sort_field = "grp,name";
		if (empty($sort_order)) $sort_order = "ASC";
		$contacts = $dm->sort($sort_field, $sort_order);
		
		if ($to_has_group) $to = ResolveContactsGroup($to);
		if ($cc_has_group) $cc = ResolveContactsGroup($cc);
		if ($bcc_has_group) $bcc = ResolveContactsGroup($bcc);
	}
}

function getTnefAttachs($conn, $folder, $id, $part, $structure){
	global $AppUI;
	require_once( $AppUI->getModuleClass( 'files' ) );

/* figure out the body part's type */
	
	$typestring=iml_GetPartTypeString($structure, $part);
	list($type, $subtype) = explode("/", $typestring);
					
/* fetch body part */
	$body=iil_C_FetchPartBody($conn, $folder, $id, $part);

/* decode body part */
	$encoding=iml_GetPartEncodingCode($structure, $part);
	if ($encoding == 3 ) $body=base64_decode($body);
	else if ($encoding == 4) $body=quoted_printable_decode($body);					

/* check if UTF-8 */
	$charset=iml_GetPartCharset($structure, $part);
	if (strcasecmp($charset, "utf-8")==0){
		include_once("./modules/wmail/include/utf8.inc");
		$is_unicode = true;
		$body = utf8ToUnicodeEntities($body);
	}else{
		$is_unicode = false;
	}
		
/* run through character encoding engine */
	$body=LangConvert($body, $my_charset, $charset);
	$tnef_files=tnef_decode($body);
	return $tnef_files;
		
	//show attachments/parts
	//	$num_parts = sizeof($tnef_files);
}
/***
	CHECK UPLOADS DIR
***/
$uploadDir = $UPLOAD_DIR.ereg_replace("[\\/]", "", $loginID.".".$host);
if (!file_exists(realpath($uploadDir))) $error .= $AppUI->_("Invalid uploads dir")."<br>";


/****
	SEND
****/
function cmp_send(){}
if (isset($send)){
	$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
	$exit = false;
	if (!$conn)
		echo $AppUI->_("failed");
	else{
		//echo "Composing...<br>\n"; flush();
		
		$error = "";
		
		/**** Check for subject ***/
        $no_subject = false;
		/* comentado el 09/12/2004 para permitir el envio de emails sin asunto si el usuario lo desea
    if ((strlen($subject)==0)&&(!$confirm_no_subject)){
            $error .= $composeErrors[0]."<br>\n";
            $no_subject = true;
        }
		*/
		
		/**** alternate identity? ****/
		if ($sender_identity_id > 0){
			//format sender name
			$from_name = $alt_identities[$sender_identity_id]["name"];
			$from_name = LangEncodeSubject($from_name, $my_charset);
			if ((!empty($from_name)) && (count(explode(" ", $from_name)) > 1)) $from_name = "\"".$from_name."\"";
			
			//format "From:" header
			$from_addr = $alt_identities[$sender_identity_id]["email"];
			$from = $from_name." <".$from_addr.">";
			
			//format "Reply-To:" header
			if (!empty($alt_identities[$sender_identity_id]["replyto"])) 
				$reply_to = $from_name." <".$alt_identities[$sender_identity_id]["replyto"].">";
			else
				$reply_to = "";
		}
		
		/**** Check "from" ***/
		if (strlen($from)<7) $error .= $composeErrors[1]."<br>\n";
		
		/**** Check for recepient ***/
		$to = stripslashes($to);
		if ((strcasecmp($to, "self")==0) || (strcasecmp($to, "me")==0)) $to=$my_prefs["email_address"];
		if ((strlen($to) < 7) || (strpos($to, "@")===false))
			$error .= $composeErrors[2]."<br>\n";
			
		/**** Anti-Spam *****/
		$as_ok = true;
		//echo "lastSend: $lastSend <br> numSent: $numSent <br>\n";
		//echo "$max_rcpt_message $max_rcpt_session $min_send_interval <br>";
		if ((isset($max_rcpt_message)) && ((isset($max_rcpt_session))) && (isset($min_send_interval))){
			$num_recepients = substr_count($to.$cc.$bcc, "@");
			if ($num_recepients > $max_rcpt_message) $as_ok = false;
			if (($num_recepients + $numSent) > $max_rcpt_session) $as_ok = false;
			if ((time() - $lastSend) < $min_send_interval) $as_ok = false;
		}else{
			echo $AppUI->_("Bypassing anti-spam")."<br>\n";
		}
		if (!$as_ok){
			$as_error = $composeErrors[5];
			$as_error = str_replace("%1", $max_rcpt_message, $as_error);
			$as_error = str_replace("%2", $max_rcpt_session, $as_error);
			$as_error = str_replace("%3", $min_send_interval, $as_error);
			$error .= $as_error;
		}
		/**********************/

		if ($error){
			//echo "<font color=\"red\">".$error."</font><br><br>\n";
		}else{
			//echo "<p>Sending....";
			//flush();
			
			$num_parts=0;
	
			/*** Initialize header ***/
			$headerx = "Date: ".TZDate($my_prefs["timezone"])."\r\n";
			$headerx.= "X-Mailer: PSAmail/".$version." (On: ".$_SERVER["SERVER_NAME"].")\r\n";
			if (!empty($replyto_messageID)) $headerx.= "In-Reply-To: <".$replyto_messageID.">\r\n";
		

					
			/****  Attach Sig ****/
			if ($attach_sig==1){
				if ($sender_identity_id > 0) $message.="\n\n".$alt_identities[$sender_identity_id]["sig"];
				else $message.= "\n\n".$my_prefs["signature1"];
			}	

			/****  Attach Tag-line ***/
			
			if ($userLevel < $TAG_LEVEL){
				$message .= "\n\n".$TAG_LINE;
			}

			/******* GPG stuff *********/
			if(isset($keytouse) && $GPG_ENABLE){
				$gpg_encrypted = gpg_encrypt($loginID, $host, $keytouse, $message);
			}
			
			/****  smart wrap ****/
			$message = LangSmartWrap($message, 74);

			/****  Encode  ****/
			$subject=stripslashes($subject);
			$subject=LangEncodeSubject($subject, $my_charset);
			
			if (!$gpg_encrypted){
				$message=stripslashes($message);
				$part[0]=LangEncodeMessage($message, $my_charset);
			}else{
				$part[0]["data"] = $message;
			}
			/***********************/
				
			/****  Pre-process addresses */
			$from = stripslashes($from);
			$to = stripslashes($to);
			
			$to = RemoveDoubleAddresses($to);
			
			//echo "To: ".htmlspecialchars($to)." <br>\n";
				
			$to = LangEncodeAddressList($to, $my_charset);
			$from = LangEncodeAddressList($from, $my_charset);
					
			if (!empty($cc)){
				$cc= stripslashes($cc);
				$cc = RemoveDoubleAddresses($cc);
				$cc = LangEncodeAddressList($cc, $my_charset);
			}
			if (!empty($bcc)){
				$bcc = stripslashes($bcc);
				$bcc = RemoveDoubleAddresses($bcc);
				$bcc = LangEncodeAddressList($bcc, $my_charset);
			}
			/***********************/

                    
			/****  Add Recipients *********/
			//$headerx.="Return-Path: ".$sender_addr."\n";
			$headerx.="From: ".$from."\r\n";
            //$headerx.="Sender: ".$sender_addr."\n";
			$headerx.="Bounce-To: ".$from."\r\n";
            $headerx.="Errors-To: ".$from."\r\n";
			if (!empty($reply_to)) $headerx.="Reply-To: ".stripslashes($reply_to)."\r\n";
			if ($cc){
				$headerx.="CC: ". stripslashes($cc)."\r\n";
			}
			if ($bcc){
				$headerx.="BCC: ".stripslashes($bcc)."\r\n";
			}
			/************************/
			
			/****  Priority ****/
			if ($priority>=1 && $priority<=5){
				$headerx.= "X-Priority: $priority (".$message_priorities[$priority].")\r\n";
			}	
			
			/****  Confirm reading ****/
			if ($confirm_read==1){
				$headerx.= "X-Confirm-Reading-To: ".(!empty($reply_to) ? $reply_to : $from).")\r\n";
				$headerx.= "Disposition-Notification-To: ".(!empty($reply_to) ? $reply_to : $from).")\r\n";
			}	
			
			/****  Return receipt ****/
			if ($return_receipt==1){
				$headerx.= "Return-Receipt-To: ".(!empty($reply_to) ? $reply_to : $from).")\r\n";
			}				
			
			/****  upload unuploaded attachments *****/
			ignore_user_abort(true);
			if (($userfile)&&($userfile!="none")){			
				$i=$attachments;
				$newfile = $user.".".base64_encode($userfile_name).".".base64_encode($userfile_type).".".base64_encode($userfile_size);
				$newpath=$uploadDir."/".$newfile;
				if (move_uploaded_file($userfile, $newpath)){
					$attach[$newfile] = 1;
				}
			}
			/*****************************************/
			
			/****  Prepare attachments *****/
			//echo "Attachments: $attachments <br>\n";
			if (file_exists(realpath($uploadDir))){
				if (is_array($attach)){
					$tnef_files = array();
					while ( list($file, $v) = each($attach) ){
						if ($v==1){
							//split up file name
							$file_parts = explode(".", $file);
							
							//put together full path
							$a_path = $uploadDir."/".$file;

							//get name and type
							$a_name=base64_decode($file_parts[1]);
							$a_type=strtolower(base64_decode($file_parts[2]));
							if ($a_type=="") $a_type="application/octet-stream";								

							//if data is good...
							if (($file_parts[0]==$user) && (file_exists(realpath($a_path)))){
								//echo "Attachment $i is good <br>\n";
								$num_parts++;			
								
								//stick it in conent array
								$part[$num_parts]["type"]="Content-Type: ".$a_type."; name=\"".$a_name."\"\r\n";
								$part[$num_parts]["disposition"]="Content-Disposition: attachment; filename=\"".$a_name."\"\r\n";
								$part[$num_parts]["encoding"]="Content-Transfer-Encoding: base64\r\n";
								$part[$num_parts]["size"] = filesize($a_path);
								$attachment_size += $part[$num_parts]["size"];
								$part[$num_parts]["path"] = $a_path;
							}else if (strpos($file_parts[0], "fwd-")!==false){
							//forward an attachment
								
								$fwd_tnefid = "";
								$fwd_encoded = true;
								
								//extract specs of attachment
								$fwd_specs = explode("-", $file_parts[0]);
								$fwd_folder = base64_decode($fwd_specs[1]);
								$fwd_id = $fwd_specs[2];
								$fwd_part = base64_decode($fwd_specs[3]);
								$fwd_part_codes = explode(".", $fwd_part);
								if (count($fwd_part_codes)>1){
									$fwd_part = $fwd_part_codes[0];
									$fwd_tnefid = $fwd_part_codes[1];
								}
								
								//get attachment content
								$fwd_content = iil_C_FetchPartBody($conn, $fwd_folder, $fwd_id, $fwd_part);

								//get attachment header
								$fwd_header = iil_C_FetchPartHeader($conn, $fwd_folder, $fwd_id, $fwd_part);
        
        						// si es un adjunto tnef de outlook cambio el contenido del archivo
        						if ($fwd_tnefid!=""){ 
						        	include_once("./modules/wmail/include/mime.inc");
						        	include_once("./modules/wmail/include/tnef_decoder.inc");	
									$fwd_structure_str=iil_C_FetchStructureString($conn, $fwd_folder, $fwd_id);
	        						$fwd_structure=iml_GetRawStructureArray($fwd_structure_str);
	        												        							
        							if(!isset($tnef_files["$fwd_folder.$fwd_id.$fwd_part"][$fwd_tnefid]))
										$tnef_files["$fwd_folder.$fwd_id.$fwd_part"] = getTnefAttachs($conn, $fwd_folder, $fwd_id, $fwd_part, $fwd_structure);
									$fwd_content = $tnef_files["$fwd_folder.$fwd_id.$fwd_part"][$fwd_tnefid]["stream"];
									$fwd_encoded = false;
        						}
        						
								//extract "content-transfer-encoding field
								$head_a = explode("\n", $fwd_header);
								if (is_array($head_a)){
									while ( list($k,$head_line)=each($head_a) ){
										$head_line = chop($head_line);
										if (strlen($head_line)>15){
											list($head_field,$head_val)=explode(":", $head_line);
											if (strcasecmp($head_field, "content-transfer-encoding")==0){
												$fwd_encoding = trim($head_val);
												//echo $head_field.": ".$head_val."<br>\n";
											}
										}
									}
								}
									

								//create file in uploads dir
								$file = $user.".".$file_parts[1].".".$file_parts[2].".".$file_parts[3];
								$a_path = $uploadDir."/".$file;
								$fp = fopen($a_path, "w");
								if ($fp){
									fputs($fp, $fwd_content);
									fclose($fp);
								}else{
									echo $AppUI->_("Error when saving fwd att to")." $a_path <br>\n";
								}
								$fwd_content = "";
									
								//echo "Attachment $i is a forward <br>\n";
								$num_parts++;

								//stick it in content array
								$part[$num_parts]["type"]="Content-Type: ".$a_type."; name=\"".$a_name."\"\r\n";
								$part[$num_parts]["disposition"]="Content-Disposition: attachment; filename=\"".$a_name."\"\r\n";
								if (!empty($fwd_encoding)) $part[$num_parts]["encoding"] = "Content-Transfer-Encoding: $fwd_encoding\r\n";
								$part[$num_parts]["size"] = filesize($a_path);
								$attachment_size += $part[$num_parts]["size"];
								$part[$num_parts]["path"] = $a_path;
								$part[$num_parts]["encoded"] = $fwd_encoded;
								
							}
						}
					}
				}
			}

			
			/**** Put together MIME message *****/
			//echo "Num parts: $num_parts <br>\n";
			
			$received_header = "Received: from ".$_SERVER["REMOTE_ADDR"]." (auth. user $loginID@$host)\r\n";
			$received_header.= "          by ".$_SERVER["SERVER_NAME"]." with HTTP; ".TZDate($my_prefs["timezone"])."\r\n";
			$headerx = $received_header."To: ".$to."\r\n".(!empty($subject)?"Subject: ".$subject."\r\n":"").$headerx;
			
			if ($gpg_encrypted){
				//OpenPGP Compliance.  See RFC2015
				//create boundary
				$tempID = $loginID.time();
				$boundary="RWP_PART_".$tempID;

				//message header...
				$headerx.="Mime-Version: 1.0\r\n";
				$headerx.="Content-Type: multipart/encrypted; boundary=$boundary;\r\n";
				$headerx.="        protocol=\"application/pgp-encrypted\"\r\n";

				$body = "--".$boundary."\r\n";
				$body.= "Content-Type: application/pgp-encrypted\r\n\r\n";
				$body.= "Version: 1\r\n\r\n";
				
				$body.= "--".$boundary."\r\n";
				$body.= "Content-Type: application/octet-stream\r\n\r\n";
				$body.= $part[0]["data"];
				$body.= "\r\n";
				
				$body.= "--".$boundary."--\r\n";
				
				$message = $headerx."\r\n".$body;
				$is_file = false;
			}else if ($num_parts==0){
				//simple message, just store as string
				$headerx.="MIME-Version: 1.0 \r\n";
				$headerx.=$part[0]["type"];
				if (!empty($part[0]["encoding"])) $headerx.=$part[0]["encoding"];
				$body=$part[0]["data"];
				
				$message = $headerx."\r\n".$body;
				$is_file = false;
			}else{
				//for multipart message, we'll assemble it and dump it into a file
				
				//echo "Uploads directory: $uploadDir <br>\n";
				if (file_exists(realpath($uploadDir))){
					$tempID = $bodytag = str_replace ("/", "_", $loginID.time());

					$boundary="RWP_PART_".$tempID;
					

					$temp_file = $uploadDir."/".$tempID;
					//echo "Temp file: $temp_file <br>\n";
					$temp_fp = fopen($temp_file, "w");
					if ($temp_fp){
						//setup header
						$headerx.="MIME-Version: 1.0 \r\n";
						$headerx.="Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n"; 

						//write header to temp file
						fputs($temp_fp, $headerx."\r\n");
					
						//write main body
						fputs($temp_fp, "This message is in MIME format.\n");
			
						//loop through attachments
						for ($i=0;$i<=$num_parts;$i++){
							//write boundary
							fputs($temp_fp, "\n--".$boundary."\n");
							
							//form part header
							$part_header = "";
							if ($part[$i]["type"]!="") $part_header .= $part[$i]["type"];
							if ($part[$i]["encoding"]!="") $part_header .= $part[$i]["encoding"];
							if ($part[$i]["disposition"]!="") $part_header .= $part[$i]["disposition"];
							
							//write part header
							fputs($temp_fp, $part_header."\n");
								
							//open uploaded attachment
							$ul_fp = false;
							if ((!empty($part[$i]["path"])) && (file_exists(realpath($part[$i]["path"])))){
								$ul_fp = fopen($part[$i]["path"], "rb");
							}
							if ($ul_fp){
								//transfer data in uploaded file to MIME message
								
								if ($part[$i]["encoded"]){
									//straight transfer if already encoded
									while(!feof($ul_fp)){
										$line = fgets($ul_fp, 1024);
										fputs($temp_fp, $line);
									}
								}else{
									//otherwisee, base64 encode
									while(!feof($ul_fp)){
										//read 57 bytes at a time
										$buffer = fread($ul_fp, 57);
										//base 64 encode and write (line len becomes 76 bytes)
										fputs($temp_fp, base64_encode($buffer)."\n");
									}
								}
								fclose($ul_fp);
								unlink($part[$i]["path"]);
							}else if (!empty($part[$i]["data"])){
								//write message (part is not an attachment)
								fputs($temp_fp, $part[$i]["data"]."\n");
							}
						}
						
						//write closing boundary
						fputs($temp_fp, "\n--".$boundary."--");
						
						//close temp file
						fclose($temp_fp);
						
						$message = $temp_file;
						$is_file = true;
					}else{
						$error .= $AppUI->_("Temp file could not be opened").": $temp_file <br>\n";
					}
				}else{
					$error .= $AppUI->_("Invalid uploads dir")."<br>\n";
				}
			}
			
			/*** Clean up uploads directory ***/
			if (file_exists(realpath($uploadDir))){
				//open directory
				if ($handle = opendir($uploadDir)) {
					//loop through files
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != "..") {
							//split up file name
							$file_parts = explode(".", $file);
				
							if ((count($file_parts)==4) && (strpos($file_parts[0], "fwd-")!==false)){
								$path = $uploadDir."/".$file;
								unlink($path);
							}
						} 
					}
					closedir($handle); 
				}
			}	
			


			/**** Send message *****/
			if (!empty($error)){
				echo $error;
				//echo "</body></html>";
				$exit = true;
			}else{
			
					
				//echo "Sending...<br>";
	
				$sent = false;
				if (!empty($SMTP_SERVER)){
				//send thru SMTP server using cusotm SMTP library
					include_once("./modules/wmail/include/smtp.inc");
					global $smtp_error;
					
					//connect to SMTP server
					$smtp_conn = smtp_connect($SMTP_SERVER, "25", $loginID, $password);

					if ($smtp_conn){
						//echo "OK";
						//generate list of recipients
						$recipients = $to.", ".$cc.", ".$bcc;
						$recipient_list = smtp_expand($recipients);
						//echo "Sending to: ".htmlspecialchars(implode(",", $recipient_list))."<br>\n";
					
						//send message
						$sent = smtp_mail($smtp_conn, $from_addr, $recipient_list, $message, $is_file);
					}else{
						echo $AppUI->_("SMTP connection failed").": $smtp_error \n";
					}
				}else{
				//send using PHP's mail() function
					include_once("./modules/wmail/include/smtp.inc");
					$to = implode(",", smtp_expand($to));
					$to = ereg_replace("[<>]", "", $to);
					//echo "Adjusted to: ".htmlspecialchars($to)."<br>";
					
					
					if ($is_file){
						//open file
						$fp = fopen($message, "r");
						
						//if file opened...
						if ($fp){
							//read header
							$header = "";
							do{
								$line = chop(iil_ReadLine($fp, 1024));
	
								if ((!empty($line))
									and (!iil_StartsWith($line, "Subject:"))
									and (!iil_StartsWith($line, "To:"))
									)
								{
									$header .= $line."\n";
								}							
							}while((!feof($fp)) && (!empty($line)));
							
							echo nl2br($header);
							
							//read body
							$body = "";
							while(!feof($fp)){
								$body .= fgets($fp, 1024);
							}
							fclose($fp);
							
							//echo "<br>From: $from_addr <br>\n";
							
							//send
							$sent = mail($to, $subject, $body, $header, "-f$from_addr");
						}else{
							$error .= $AppUI->_("Couldn't open temp file for reading")." :$message <br>\n";
						}
					}else{
						//take out unnecessary header fields
						$header_a = explode("\n", $headerx);
						$header_a[2] = "X-PSAmail-Blah: ".$sender_addr;
						$header_a[3] = "X-PSAmail-Method: mail() [mem]";
						$header_a[4] = "X-PSAmail-Dummy: moo";
	
						reset($header_a);
						while ( list($k,$line) = each($header_a) ) $header_a[$k] = chop($line);
	
						$headerx = implode("\n", $header_a);
						
						//echo "<br>From: $from_addr <br>\n";
	
						//send
						$sent = mail($to,$subject,$body,$headerx, "-f$from_addr");
					}
				}
				
				//send!!
				if ($sent){
					//echo "Sent!<br>"; flush();
					$error = "";
					
					//save in send folder
					flush();
					if ($my_prefs["save_sent"]==1){
						//echo "Moving to send folder...";
						if ($is_file) $saved = iil_C_AppendFromFile($conn, $my_prefs["sent_box_name"], $message);
						else $saved = iil_C_Append($conn, $my_prefs["sent_box_name"], $message);
						if (!$saved) $error .= $AppUI->_("Couldn't save").":".$conn->error."<br>\n";
						//else echo "done.<br>\n";
					}
					
					//delete temp file, if necessary
					if ($is_file) unlink($message);
					
					//if replying, flag original message
					if (isset($in_reply_to)) $reply_id = $in_reply_to;
					else if (isset($forward_of)) $reply_id = $forward_of;
					if (($ICL_CAPABILITY["flags"]) && (isset($reply_id))){
						$pos = strrpos($reply_id, ":");
						$reply_uid = substr($reply_id, $pos+1);
						$reply_folder = substr($reply_id, 0, $pos);
						$reply_num = iil_C_UID2ID($conn, $reply_folder, $reply_uid);
						
						if ($reply_num !== false){
							if (iil_C_Flag($conn, $reply_folder, $reply_num, "ANSWERED") < 1){
								echo $AppUI->_("Flagging failed:").$conn->error." ()<br>\n";
							}
						}else{
							echo "UID -> ID ".$AppUI->_("conversion failed").".<br>\n";
						}
					}
					
					//update spam-prevention related records
					include_once("./modules/wmail/include/as_update.inc");
	
					if ((empty($error))&&($my_prefs["closeAfterSend"]==1)){
						//clean up uploads dir
						$uploadDir = $UPLOAD_DIR.ereg_replace("[\\/]", "", $loginID.".".$host);
	
						if (file_exists(realpath($uploadDir))){
							if ($handle = opendir($uploadDir)) {
								while (false !== ($file = readdir($handle))) { 
									if ($file != "." && $file != "..") { 
										$file_path = $uploadDir."/".$file;
										unlink($file_path);
									} 
								}
								closedir($handle); 
							}
						}	
						
						//close window

	                    echo "<table border=\"0\"  cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">";
	                    echo "<tr class=\"tableHeaderGral\"><td>".$AppUI->_('Information')."</td></tr>";
	                    echo "<tr class=\"tableForm_bg\"><td align=\"center\">";
	                    //echo"<br><br><br><p>";
						echo "<br><b>".$AppUI->_('Message successfully sent').".</b><br>";
	                    //echo "<br><br><br>";
	                    echo "\n<script type=\"text/javascript\">\n";
						echo "   DoCloseWindow(\"main.php?user=$user&folder=".(empty($folder)?"INBOX":urlencode($folder))."\");\n";
						echo "</script>\n";
						echo "</td></tr>";
	                    echo "<tr class=\"tableForm_bg\"><td>&nbsp;</td></tr></table>";
	                    flush();
	                   
	          //$AppUI->setMsg( "Message successfully sent", UI_MSG_OK );
					}else{
						//$AppUI->setMsg( $error, UI_MSG_ERROR );
						echo $error;
					}
				}else{
					//$AppUI->setMsg( "<p><font color=\"red\">".$AppUI->_('Send FAILED')."</font><br>$smtp_errornum : ".nl2br($smtp_error), UI_MSG_ERROR );
					echo "<p><font color=\"red\">".$AppUI->_('Send FAILED')."</font><br>$smtp_errornum : ".nl2br($smtp_error);
				}
	
				iil_Close($conn); 
				//$AppUI->redirect("m=wmail");
				$exit=true;
				
			}
			
			
			
		}
	if (!$exit)
		iil_Close($conn);
	}
}


/****
	HANDLE UPLOADED FILE
****/
function upload(){}
if (isset($upload)){
	if (($userfile)&&($userfile!="none")){
		$i=$attachments;
		$file_parts = pathinfo($userfile_name);
		$file_type = new CFileTypes();
		$part_description = "Unknow";
		if ($file_type->loadByExtension($file_parts["extension"])){
			$part_description = $file_type->friendly;				
		}		
		$newfile = $user
			.".".base64_encode($userfile_name)
			.".".base64_encode($userfile_type)
			.".".base64_encode($userfile_size)
			.".".base64_encode($part_description)
			.".".base64_encode($file_parts["extension"]);
			
			
		$newpath=$uploadDir."/".$newfile;
		if (move_uploaded_file($userfile, $newpath)){
			$attach[$newfile] = 1;
		}else{
			echo $userfile_name." : ".$composeErrors[3];
		}
	}else{
		echo $composeErrors[4];
	}
}


/****
	FETCH LIST OF UPLOADED FILES
****/
function fetchUploads(){}
if (file_exists(realpath($uploadDir))){
	//open directory
	if ($handle = opendir($uploadDir)) {
		//loop through files
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				//split up file name
				$file_parts = explode(".", $file);
				
				//make sure first part is session ID, and add to list
				if ((strcmp($file_parts[0], $user)==0)||(strpos($file_parts[0], "fwd-")!==false))
					$uploaded_files[] = $file;
			} 
		}
		closedir($handle); 
	}
}
if (is_array($fwd_att_list)){
	reset($fwd_att_list);
	while ( list($file, $v) = each($fwd_att_list) ){
		$uploaded_files[] = $file;
	}
}


/****
	REPLYING OR FORWARDING
****/
function replyOrForward(){}
if ((isset($replyto)) || (isset($forward))){
    // if REPLY, or FORWARD
	if ((isset($folder))&&(isset($id))){		
        include_once("./modules/wmail/include/mime.inc");
        
		//connect
		$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);

		//get message
		$header=iil_C_FetchHeader($conn, $folder, $id);

		//check IMAP UID, if set
		if (($uid > 0) && ($header->uid!=$uid)){
			$temp_id = iil_C_UID2ID($conn, $folder, $uid);
			if ($temp_id) $header=iil_C_FetchHeader($conn, $folder, $temp_id);
			else{
				"UID - MID mismatch:  UID $uid not found.  Original message no longer exists in $folder <br>\n";
				exit;
			}
		}else{
			//echo "UID matched:  $uid <br>\n";
		}

        $structure_str=iil_C_FetchStructureString($conn, $folder, $id);
        $structure=iml_GetRawStructureArray($structure_str);
		
		$subject=LangDecodeSubject($header->subject, $my_prefs["charset"]);
		$lookfor=(isset($replyto)?"Re:":"Fwd:");
		$pos = strpos ($subject, $lookfor);
        if ($pos===false) {
			$pos = strpos ($subject, strtoupper($lookfor));
        	if ($pos===false) {
				$subject=$lookfor." ".$subject;
			}
        }
		
		//get messageID
		$replyto_messageID = $header->messageID;
		
		//get "from";
		$from = $header->from;
		//replace to "reply-to" if specified
		if ($replyto){
			$to = $from;
			if (!empty($header->replyto)) $to = $header->replyto;
		}
		if ($replyto_all){
			if (!empty($header->to)) $to .= (empty($to)?"":", ").$header->to;
			if (!empty($header->cc)) $cc .= (empty($cc)?"":", ").$header->cc;
		}
		
		//mime decode "to," "cc," and "from" fields
		if (isset($to)){
			$to_a = LangParseAddressList($to);
			$to = "";
			while ( list($k, $v) = each($to_a) ){
                //remove user's own address from "to" list
                if ((stristr($to_a[$k]["address"], $from_addr) === false) and
 				    (stristr($to_a[$k]["address"], $loginID."@".$host) === false) and
					($my_prefs["email_address"] != $to_a[$k]["address"])){
                    $to .= (empty($to)?"":", ")."\"".LangDecodeSubject($to_a[$k]["name"], $my_prefs["charset"])."\" <".$to_a[$k]["address"].">";
                }
            }
		}
		if (isset($cc)){
			echo "<!-- $cc //-->\n";

			$cc_a = LangParseAddressList($cc);
			$cc = "";
			while ( list($k, $v) = each($cc_a) ){
				echo "<!-- CC: ".$cc_a[$k]["address"]." //-->\n";
                //remove user's own address from "cc" list
                if ((stristr($cc_a[$k]["address"], $from_addr) === false) and
 				    (stristr($cc_a[$k]["address"], $loginID."@".$host) === false) and
					($my_prefs["email_address"] != $cc_a[$k]["address"])){
					echo "<!-- adding: ".$cc_a[$k]["address"]." //-->\n";
                    $cc .= (empty($cc)?"":", ")."\"".LangDecodeSubject($cc_a[$k]["name"], $my_prefs["charset"])."\" <".$cc_a[$k]["address"].">";
                }
            }
		}
		
		$from_a = LangParseAddressList($from);
		$from = "\"".LangDecodeSubject($from_a[0]["name"], $my_prefs["charset"])."\" <".$from_a[0]["address"].">";
		
		//format headers for reply/forward
		if (isset($replyto)){
			$message_head = $composeStrings[9];
			$message_head = str_replace("%d", LangFormatDate($header->timestamp, $lang_datetime["prevyears"]), $message_head);
			$message_head = str_replace("%s", $from, $message_head);
		}else if (isset($forward)){
			if ($show_header){
				$message_head = iil_C_FetchPartHeader($conn, $folder, $id, 0);
			}else{
				$message_head = $composeStrings[10];
				$message_head .= $composeHStrings[5].": ".ShowDate2($header->date,"","short")."\n";
				$message_head .= $composeHStrings[1].": ". LangDecodeSubject($from, $my_prefs["charset"])."\n";
				$message_head .= $composeHStrings[0].": ".LangDecodeSubject($header->subject, $my_prefs["charset"])."\n\n";
			}
		}
		if (!empty($message_head)) $message_head = "\n".$message_head."\n";
		
		//get message
        if (!empty($part)) $part.=".1";
        else{
            $part = iml_GetFirstTextPart($structure, "");
        }
        		
		$message=iil_C_FetchPartBody($conn, $folder, $id, $part);
					
		//decode message if necessary
        $encoding=iml_GetPartEncodingCode($structure, $part);        
		if ($encoding==3) $message = base64_decode($message);
		else if ($encoding==4){
            //if ($encoding == 3 ) $message = base64_decode($message);
            //else if ($encoding == 4) $message = quoted_printable_decode($message);
			//$message = quoted_printable_decode($message);
            $message = str_replace("=\n", "", $message);
            $message = quoted_printable_decode(str_replace("=\r\n", "", $message));
        }
		
        //add quote marks
		$message = str_replace("\r", "", $message);
		$charset=iml_GetPartCharset($structure, $part);


		$message=LangConvert($message, $my_prefs["charset"], $charset);
		if (isset($replyto)) $message=">".str_replace("\n","\n>",$message);
		$message = "\n".LangConvert($message_head, $my_prefs["charset"], $charset).$message;

		
		//get message attachments
		if ($forward){
			include_once("./modules/wmail/include/tnef_decoder.inc");				
			$att_list = iml_GetPartList($structure, "");
			while ( list($i,$v) = each($att_list) ){
				if ((strcasecmp($att_list[$i]["disposition"], "inline")==0)
					or (strcasecmp($att_list[$i]["disposition"], "attachment")==0)
					or (!empty($att_list[$i]["name"]))){
						
					if (strcasecmp($att_list[$i]["typestring"], "application/ms-tnef")==0){
						
						
						//$code=$part.(empty($part)?"":".").$i;
						/*
						$tnef_files = getTnefAttachs($conn, $folder, $id, $i, $structure);
						*/
						$code = $i;
						$type=iml_GetPartTypeCode($structure, $code);
						$name=iml_GetPartName($structure, $code);
						$typestring=iml_GetPartTypeString($structure,$code);
						list($dummy, $subtype) = explode("/", $typestring);
						$bytes=iml_GetPartSize($structure,$code);
						$encoding=iml_GetPartEncodingCode($structure, $code);
						$disposition = iml_GetPartDisposition($structure, $code);
												
						$body=iil_C_FetchPartBody($conn, $folder, $id, $code);						
						if ($encoding == 3 ) $body=base64_decode($body);
						else if ($encoding == 4) $body=quoted_printable_decode($body);
						if (strcasecmp($charset, "utf-8")==0){
							include_once("./modules/wmail/include/utf8.inc");
							$is_unicode = true;
							$body = utf8ToUnicodeEntities($body);
						}else{
							$is_unicode = false;
						}
						$body=LangConvert($body, $my_charset, $charset);
						$tnef_files=tnef_decode($body);							
						
						for($j=0; $j<count($tnef_files);$j++){
							$file_parts = pathinfo($tnef_files[$j]["name"]);
							
							$file_type = new CFileTypes();
							$part_description = "Unknow";
							if ($file_type->loadByExtension($file_parts["extension"])){
								$part_description = $file_type->friendly;				
							}			
							$file = "fwd-".base64_encode($folder)."-$id-".base64_encode("$i.$j");
							$file .= ".".base64_encode($tnef_files[$j]["name"]);
							$file .= ".".base64_encode($file_type->mime);
							$file .= ".".base64_encode($tnef_files[$j]["size"]);
							$file .= ".".base64_encode($part_description);
							$file .= ".".base64_encode($file_parts["extension"]);
							if (!$fwd_att_list[$file]){
								$uploaded_files[] = $file;
								$fwd_att_list[$file] = 1;
								$attach[$file] = 1;
							}						
						}
					
					}else{
						$file_parts = pathinfo($att_list[$i]["name"]);
						$file_type = new CFileTypes();
						$part_description = "Unknow";
						if ($file_type->loadByExtension($file_parts["extension"])){
							$part_description = $file_type->friendly;				
						}							
						$file = "fwd-".base64_encode($folder)."-$id-".base64_encode($i);
						$file .= ".".base64_encode($att_list[$i]["name"]);
						$file .= ".".base64_encode($att_list[$i]["typestring"]);
						$file .= ".".base64_encode($att_list[$i]["size"]);
						$file .= ".".base64_encode($part_description);
						$file .= ".".base64_encode($file_parts["extension"]);						
						if (!$fwd_att_list[$file]){
							$uploaded_files[] = $file;
							$fwd_att_list[$file] = 1;
							$attach[$file] = 1;
						}
					}
				}
			}
		}

			
		iil_Close($conn);			
	}
}else{
	$message = stripslashes($message);
}

function showForm(){}

if (($show_contacts) || ($my_prefs["showContacts"])) {
?>
<?php
}

if (!$exit){
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="tableForm_bg">

<FORM NAME="messageform" ENCTYPE="multipart/form-data" 
ACTION="?m=wmail&tab=1&a=bridge" METHOD="POST" 
onSubmit='return validate_message();'>
<input type="hidden" name="user" value="<?php echo $user?>">
<input type="hidden" name="user" value="<?php echo $user?>">
<input type="hidden" name="session" value="<?php echo $user?>">
<input type="hidden" name="show_contacts" value="<?php echo $show_contacts?>">
<input type="hidden" name="show_cc" value="<?php echo $show_cc?>">
<tr>
	<td>
	<?php
        if ($no_subject) echo '<input type="hidden" name="confirm_no_subject" value="1">';
    
		if (($replyto) || ($in_reply_to)){
			if (empty($in_reply_to)) $in_reply_to = $folder.":".$uid;
			echo "<input type=\"hidden\" name=\"in_reply_to\" value=\"$in_reply_to\">\n";
			echo "<input type=\"hidden\" name=\"replyto_messageID\" value=\"$replyto_messageID\">\n";
		}else if (($forward) || ($forward_of)){
			if (empty($forward_of)) $forward_of = $folder.":".$uid;
			echo "<input type=\"hidden\" name=\"forward_of\" value=\"$forward_of\">\n";
		}
		
		if (is_array($fwd_att_list)){
			reset($fwd_att_list);
			while ( list($file,$v) = each($fwd_att_list)){
				echo "<input type=\"hidden\" name=\"fwd_att_list[".$file."]\" value=\"1\">\n";
			}
		}
		if (!empty($folder)){
			echo '<input type="hidden" name="folder" value="'.$folder.'">';
		}
	
	/*	
	?>
	
	<!--table border="0" width="100%" bgcolor="<?php echo $my_colors["main_head_bg"]?>">
	<tr>
		<td valign="bottom" align="left">
			<span class="bigTitle"><?php echo $composeStrings[0]; ?></span>
			&nbsp;&nbsp;&nbsp;
			<span class="mainHeadingSmall">
			<?php
			
			if (!$my_prefs["compose_inside"]){
				$jsclose="";//"[<a href=\"javascript:window.close();\" class=\"mainHeadingSmall\">".$composeStrings[11]."</a>]";
				echo "<SCRIPT type=\"text/javascript\" language=\"JavaScript1.2\">\n";
				echo "document.write('$jsclose');\n</SCRIPT>";
			}
		
			?>
			</span>
		</td>
		<td valign="bottom" align="right">

		</td>
	</tr>
	</table-->
	
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr class="tableHeaderGral">
            <td>&nbsp;</td>
        </tr>
    </table>

    <?php
    */
    
    
    if (!empty($error)) echo '<br><font color="red">'.$error.'</font>';
		$to = encodeUTFSafeHTML($to);
		$cc = encodeUTFSafeHTML($cc);
		$bcc = encodeUTFSafeHTML($bcc);
		
		// format sender's email address (i.e. "from" string)
        $email_address = htmlspecialchars($original_from);
		echo "<table class=\"tableForm_bg\" cellspacing=\"2\" cellpadding=\"0\" width=\"610px\">";
		echo '<col width="100px"><col width="510px">';
		//echo "<table class=\"tableForm_bg\" width=\"100%\">";
		//echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\" bgcolor=\"".$my_colors["main_bg"]."\">\n";
		echo "<tr>";
		echo "<td align=right class=\"mainLight\" width=\"100px\">".$composeHStrings[1].":</td>";
		echo "<td class=\"mainLight\" width=\"510px\">";
			echo "<table class=\"tableForm_bg\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">";
			echo "<tr><td>";
			if (($alt_identities) && (count($alt_identities)>0)){
				echo "<select class=\"text\" name=\"sender_identity_id\">\n";
					echo "<option value=\"-1\">".LangDecodeSubject($email_address, $my_prefs["charset"])."\n";
				while ( list($key,$ident_a) = each($alt_identities) ){
					if ($ident_a["name"]!=$my_prefs["user_name"] || $ident_a["email"]!=$my_prefs["email_address"]){
						echo "<option value=\"$key\" ".($key==$sender_identity_id?"SELECTED":"").">";
						echo "\"".$ident_a["name"]."\"&nbsp;&nbsp;&lt;".$ident_a["email"]."&gt;\n";
					}
				}
				echo "</select>\n";
			}else{
				echo LangDecodeSubject($email_address, $my_prefs["charset"]);
			}
	
			echo "</td><td align=\"right\">";
			echo '<input type="submit" class="button" name=send value="'.$composeStrings[1].'">';
			echo "</td></tr>\n";
			echo "</table>";
		echo "</td></tr>\n";
  
		//if (($show_contacts) || ($my_prefs["showContacts"])){
		if ($show_contacts){
			echo "<tr>\n<td align=right valign=top>";
			echo "<select name=\"to_a_field\" class=\"text\">\n";
			echo "<option value=\"to\">".$composeHStrings[2].":\n";
			echo "<option value=\"cc\">".$composeHStrings[3].":\n";
			echo "<option value=\"bcc\">".$composeHStrings[4].":\n";
			echo "</select>\n";
			echo"</td><td>";
		
			// display "select" box with contacts
			include_once("./modules/wmail/include/data_manager.inc");
			$source_name = $DB_CONTACTS_TABLE;
			if (empty($source_name)) $source_name = "contacts";
			$dm = new DataManager_obj;
			if ($dm->initialize($loginID, $host, $source_name, $DB_TYPE)){
				if (empty($sort_field)) $sort_field = "contact_company,contact_first_name, contact_last_name";
				if (empty($sort_order)) $sort_order = "ASC";
				$contacts = $dm->sort($sort_field, $sort_order);
			}else{
				echo "Data Manager initialization failed:<br>\n";
				$dm->showError();
			}

			if ((is_array($contacts)) && (count($contacts) > 0)){
				echo "<select class=\"text\" name=\"to_a[]\" MULTIPLE SIZE=7 onDblClick='CopyAdresses(); return true;'>\n";
				while ( list($key, $foobar) = each($contacts) ){
					$contact = $contacts[$key];
					$id=$contact["contact_id"];
					$contact["name"] = trim($contact["contact_first_name"])." ".trim($contact["contact_last_name"]);
					$contact["email"] = $contact["contact_email"];
					$contact["email2"] = $contact["contact_email2"];
					$contact["grp"] = $contact["contact_company"];					
					if (!empty($contact["email"])){
						$line = "\"".$contact["name"]."\" <".$contact["email"].">";
						echo "<option>".htmlspecialchars($line)."\n";
					}
					if (!empty($contact["email2"])){
						$line = "\"".$contact["name"]."\" <".$contact["email2"].">";
						echo "<option>".htmlspecialchars($line)."\n";
					}
				}
				echo "</select>"; 
				
				echo "<script type=\"text/javascript\" language=\"JavaScript1.2\">";
				echo "document.write('<input type=\"button\" class=\"button\" name=\"add_contacts\" value=\"".$composeStrings[8]."\" onClick=\"CopyAdresses()\">');\n";
				echo "</script>\n";
				echo "<noscript><input type=\"submit\" class=\"button\" name=\"add_contacts\" value=\"".$composeStrings[8]."\"><br></noscript>\n";
/*
				if ($my_prefs["showContacts"]!=1){
					echo "<input type=\"hidden\" name=\"new_show_contacts\" value=0>\n";
					//echo "<input type=\"submit\" class=\"button\" name=\"change_contacts\" value=\"".$composeStrings[6]."\">\n";
					echo "<input type=\"button\" class=\"button\" name=\"change_contacts\" value=\"".$composeStrings[6]."\" onclick=\"javascript: showhidecontacts();\">\n";
				}
				*/
			}
			echo "</td></tr>\n";
			$contacts_shown = true;
		}else{
			$contacts_shown = false;
		}
		
		// build contact popup url
		$popup_url = "?m=wmail&tab=0&xa=contacts_popup&session=$user&to_a_field=%s";
		$popup_url = "javascript:open_popup('$popup_url')";

	
		// display to field
		//echo "<tr>\n<td align=right class=\"mainLight\">".$composeHStrings[2].":</td><td>";
		echo "<tr>\n<td align=right class=\"mainLight\">";
		$popup_url_to = str_replace("%s", "to", $popup_url);
		/*
		echo "\n<input type=\"button\" class=\"button\" 
				name=\"change_contacts\" value=\"".$composeHStrings[2]."\" 
		*/
		echo "\n<b><a href=\"javascript: //\" 
				onclick=\"$popup_url_to\" style=\"height: 19px; width: 60px;\">\n";
		echo $composeHStrings[2].":</a></b>";
		echo "</td><td>";
		echo "<script> var to_label = '".$composeHStrings[2]."'; </script>";
		if (strlen($to) < 60)
            echo "<input type=text class=\"text\" name=\"to\" value=\"".stripslashes($to)."\" size=83>";
        else
            echo "<textarea name=\"to\" class=\"text\" cols=\"60\" rows=\"3\">".stripslashes($to)."</textarea>";
		
            /*
        if (!$contacts_shown){
			//"show contacts" button

			echo "<input type=\"hidden\" name=\"new_show_contacts\" value=1>\n";
			$popup_url = "?m=wmail&tab=0&xa=contacts_popup&session=$user";
			$showcon_link = "<a href=\"javascript:open_popup('$popup_url')\" class=\"mainLight\">[<b>".$composeStrings[5]."</b>]</a>";
			$showcon_link = addslashes($showcon_link);
			echo "<script type=\"text/javascript\" language=\"JavaScript1.2\">\n";
			echo "document.write('$showcon_link');\n";
			echo "</script>\n";
			echo "<noscript>\n<input type=\"submit\" name=\"change_contacts\" value=\"".$composeStrings[5]."\">\n</noscript>\n";

			//echo "<input type=\"submit\" name=\"change_contacts\" value=\"".$composeStrings[5]."\">\n";
		}
		*/
		echo "</td></tr>\n";
		
		if ((!empty($cc)) || ($my_prefs["showCC"]==1) || ($show_cc)){
			// display cc box
			//echo "<tr>\n<td align=right class=\"mainLight\">".$composeHStrings[3].":</td><td>";
			echo "<tr>\n<td align=right class=\"mainLight\">";
			$popup_url_cc = str_replace("%s", "cc", $popup_url);
			/*
			echo "\n<input type=\"button\" class=\"button\" 
					name=\"change_contacts\" value=\"".$composeHStrings[3]."\" 
					*/
			echo "\n<b><a href=\"javascript: //\" 					
					onclick=\"$popup_url_cc\" style=\"height: 19px; width: 60px;\">\n";
			echo $composeHStrings[3].":</a></b>";
			echo "</td><td>";			
			echo "<script> var cc_label = '".$composeHStrings[3]."'; </script>";
        	if (strlen($cc) < 60)
            	echo "<input type=text class=\"text\" name=\"cc\" value=\"".stripslashes($cc)."\" size=83>";
        	else
            	echo "<textarea name=\"cc\" class=\"text\" cols=\"60\" rows=\"3\">".stripslashes($cc)."</textarea>";
			echo "</td></tr>\n";
			
			$cc_field_shown = true;
		}else{
			$cc_field_shown = false;
		}
		
		if ((!empty($bcc)) || ($my_prefs["showCC"]==1) || ($show_cc)){
			// display bcc box
			//echo "<tr>\n<td align=right class=\"mainLight\">".$composeHStrings[4].":</td><td>";
			echo "<tr>\n<td align=right class=\"mainLight\">";
			$popup_url_bcc = str_replace("%s", "bcc", $popup_url);
			/*echo "\n<input type=\"button\" class=\"button\" 
					name=\"change_contacts\" value=\"".$composeHStrings[4]."\" 
			*/
			echo "\n<b><a href=\"javascript: //\" 					
					onclick=\"$popup_url_bcc\" style=\"height: 19px; width: 60px;\">\n";
			echo $composeHStrings[4].":</a></b>";
			
			echo "</td><td>";
			echo "<script> var bcc_label = '".$composeHStrings[4]."'; </script>";
        	if (strlen($bcc) < 60)
            	echo "<input type=text class=\"text\" name=\"bcc\" value=\"".stripslashes($bcc)."\" size=83>";
			else
            	echo "<textarea name=\"bcc\" class=\"text\" cols=\"60\" rows=\"3\">".stripslashes($bcc)."</textarea>\n";
			echo "</td></tr>\n";
			$bcc_field_shown = true;
		}else{
			$bcc_field_shown = false;
		}
		echo "<tr>";
		echo "<td align=right class=\"mainLight\">".$composeHStrings[0].":</td><td class=\"mainLight\">";
		echo '<input type=text name="subject" class="text" value="'.encodeUTFSafeHTML(stripslashes($subject)).'" size="83" onKeyUp="fixtitle(\''.$composeStrings["title"].'\');">';
		//echo '<input type=text name="subject" class="text" value="'.encodeUTFSafeHTML(stripslashes($subject)).'" size="60";">';
		echo "</td></tr>\n";


		//show attachments
		echo "<tr>";
		echo "<td align=\"right\" valign=\"top\" class=\"mainLight\">".$composeStrings[4].":</td>";
		echo "<td valign=\"middle\">";
		
		if ((is_array($uploaded_files)) && (count($uploaded_files)>0)){
			//echo "<table>";
			echo "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"1\" bgcolor=\"".$my_colors["main_head_bg"]."\">\n";
			echo '<input type="hidden" name="upload_file" value="">';
			reset($uploaded_files);
			while ( list($k,$file) = each($uploaded_files) ){
				$file_parts = explode(".", $file);
				echo "<tr bgcolor=\"".$my_colors["main_bg"]."\">\n";
				echo "<td valign=\"bottom\"><input type=\"checkbox\" name=\"attach[$file]\" value=\"1\" ".($attach[$file]==1?"CHECKED":"")."></td>\n";
				$file_icon = dPshowImage( 'filetype.php?extension='.base64_decode($file_parts[5]), '16', '16', base64_decode($file_parts[1]) );
				echo "<td valign=\"bottom\">$file_icon&nbsp;".base64_decode($file_parts[1])."&nbsp;</td>\n";
				//echo "<td valign=\"bottom\" class=\"small\">".base64_decode($file_parts[3])." &nbsp;</td>\n";
				echo "<td valign=\"bottom\" class=\"small\">".ShowBytes(base64_decode($file_parts[3]))." &nbsp;</td>\n";
				echo "<td valign=\"bottom\" class=\"small\">".base64_decode($file_parts[4])."</td>\n";
				echo "</td></tr>\n";
			}
			echo "</table>";
		}
		echo "<table border=\"0\"cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">\n";
		echo "<tr>";
		echo "<td valign=\"middle\">";
		echo '<input type="hidden" name="MAX_FILE_SIZE" value="2000000">';
		echo "<INPUT TYPE=\"file\" NAME=\"userfile\" class=\"text\"  SIZE=\"55\" >&nbsp;";
		echo "</td>";
		echo "<td valign=\"middle\" align=\"right\">";
		//echo '<INPUT TYPE="submit" class="button" NAME="upload" VALUE="'.$composeStrings[2].'">';
		echo '<INPUT TYPE="button" class="button" NAME="upload" VALUE="'.$composeStrings[2].'" onclick="uploadFile();">';
		//echo "&nbsp;&nbsp;";
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		echo "</td></tr>\n";
		echo "<tr><td></td><td>";	
		
		if ((!$cc_field_shown) || (!$bcc_field_shown)){
			//"show cc/bcc field" button
			include_once("./modules/wmail/lang/".$my_prefs["lang"]."prefs.inc");
			echo '<input type="hidden" name="new_show_cc" value="1">';
			echo '<input type="submit" class="button" name="change_show_cc" value="'.$prefsStrings["6.2"].'">';
		}else{
			echo '<input type="hidden" name="new_show_cc" value="'.$show_cc.'">';
		}
		echo "</td></tr>\n";

        echo "</table>";


	/***
		SPELL CHECK
	****/
	if ($check_spelling){
		include_once("./modules/wmail/include/spellcheck.inc");

		//run spell check
		$result = splchk_check($message, $spell_dict_lang);
		
		//handle results
		if ($result){
			echo "<table><tr bgcolor=\"".$my_colors["main_bg"]."\"><td>\n";
			$words = $result["words"];
			$positions = $result["pos"];
			if (count($positions)>0){
				//show errors and possible corrections
				echo "<b>".$composeStrings[15]."</b><br>\n";
				echo str_replace("%s", $DICTIONARIES[$spell_dict_lang], $composeErrors[8]);

				$splstr["ignore"] = $composeStrings[17];
				$splstr["delete"] = $composeStrings[18];
				$splstr["correct"] = $composeStrings[13];
				$splstr["nochange"] = $composeStrings[14];
				$splstr["formname"] = "messageform";
			
				splchk_showform($positions, $words, $splstr);
			}else{
				//show "no changes needed"
				echo $composeErrors[6].str_replace("%s", $DICTIONARIES[$spell_dict_lang], $composeErrors[8]);
			}
			echo "</td></tr></table>\n";
			
		}else{
			echo $composeErrors[7];
		}
	}else if ($correct_spelling){
		//correct spelling
		include_once("./modules/wmail/include/spellcheck.inc");

		//do some shifting here...
		while (list($num,$word)=each($words)){
			$correct_var = "correct".$num;
			$correct[$num] = $$correct_var;
		}
		
		echo "<table><tr bgcolor=\"".$my_colors["main_bg"]."\"><td>\n";
		echo "<b>".$composeStrings[16]."</b><br>\n";

		//do the actual corrections
		$message = splchk_correct($message, $words, $offsets, $suggestions, $correct);

		echo "</td></tr></table>\n";
	}
	
	/***
		SHOW TEXT BOX
	***/
	?>
    <table width="610px" cellpadding="2" border="0" class="tableForm_bg">
	<col width="25%"><col width="25%"><col width="25%"><col width="25%">
	<tr>
		<td>
		<input type=checkbox name="attach_sig" value=1 <?php  echo ($my_prefs["show_sig1"]==1?"CHECKED":""); ?> >
		<?php  echo $composeStrings[3]; ?>
		</td>
		<td>
		<?php  echo $AppUI->_("Priority"); ?>
		<?php global $message_priorities;
		echo arraySelect($message_priorities, "priority", "size='1' class='text'", 3, true);?>
		</td>
		<td>
		<input type=checkbox name="confirm_read" value=1 <?php  echo ($my_prefs["confirm_read"]==1?"CHECKED":""); ?> >
		<!--<?php  echo $AppUI->_("Confirm reading"); ?>!-->
		<?php  echo $AppUI->_("Read receipt"); ?>
		</td>
		<td>
		<input type=checkbox name="return_recipt" value=1 <?php  echo ($my_prefs["return_recipt"]==1?"CHECKED":""); ?> >
		<?php  echo $AppUI->_("Return receipt"); ?>		
		</td>				
	</tr>	
	<? /*
        <tr>
			<td align=right class="mainLight" width="170px"><?php echo $composeStrings[7]?></td>
			<td class="mainLight" width="540px">&nbsp;</td>
		</tr> /**/ ?>
		<tr>
            <td colspan="4" align="center">
	<TEXTAREA NAME=message ROWS=30 COLS=96 WRAP=virtual class="text"><?php echo "\n".encodeUTFSafeHTML($message); ?></TEXTAREA>

	<?php
		//spell check controls
		if (is_array($DICTIONARIES) && count($DICTIONARIES)>0){
			echo "<br><select name='spell_dict_lang'>\n";
			reset($DICTIONARIES);
			while ( list($l,$n)=each($DICTIONARIES) ) echo "<option value='$l'>$n\n";
			echo "</select>\n";
			echo '<input type="submit" name="check_spelling" value="'.$composeStrings[12].'">';
		}
	?>
            </td>
        </tr>

	<?php
	//GPG stuff
	if ($GPG_ENABLE){
	?>
	<tr>
	<td colspan="4">
	<span class="mainLight">Whose public key to use? (this feature is still experimental)<br>
	<?php
		$keys = gpg_list_keys();
		$options = "";
		if (is_array($keys) && count($keys)>0){
			while (list($k,$str)=each($keys)){
				$options.= "<option value=\"$k\">$str\n";
			}
		}
		?>
		<select name="keytouse">
		<option value = "noencode">None</option>
		<?php echo $options ?>
		</select>
	</span>
	</td>
	</tr>
	<?php
	} //end if $GPG_ENABLE
	?>

	<tr>
		<td align="right" colspan="4">
		<input type=submit class="button" name=send value="<?php  echo  $composeStrings[1]?>">
		</td>		
	</tr>
	</table>
	</td>
</tr>	
</form>
</table>
	<script type=text/javascript>
		var _p = this.parent;
		if (_p==this){
			_p.document.title = "<?php echo $composeStrings[0] ?>";
		}
	</script>


<?php } ?>