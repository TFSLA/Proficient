<?php
/////////////////////////////////////////////////////////
//	
//	include/read_message_handler.inc
//
//	(C)Copyright 2000-2002 Ryo Chijiiwa <Ryo@IlohaMail.org>
//
//	This file is part of IlohaMail. IlohaMail is free software released 
//	under the GPL license.  See enclosed file COPYING for details, or 
//	see http://www.fsf.org/copyleft/gpl.html
//
/////////////////////////////////////////////////////////

/********************************************************

	PURPOSE:
		Decides which part to display in read message window (and printer friendly view)
	PRE-CONDITIONS:
		There's a bunch.  Look in "source/read_message.php"
	COMMENTS:
		Yeah, I know it's messy.

********************************************************/


	$typeCode=iml_GetPartTypeCode($structure, $part);
	list($dummy,$subtype) = explode("/",iml_GetPartTypeString($structure,$part));
	
	echo "\n<!-- Type code: ".$header->ctype."//-->\n";
			
	if (($typeCode==3)&&(strcasecmp($subtype, "ms-tnef")==0)){
		//ms-tnef
		$type = $dummy;
		include_once("./modules/wmail/include/tnef_decoder.inc");
		include("./modules/wmail/include/read_tnef_print.inc");
	}else if ($typeCode==0){
		// major type is "TEXT"
		$typestring=iml_GetPartTypeString($structure, $part);
		
		// if part=0, and there's a conflict in content-type, use what's specified in header
		if (empty($part) && !empty($header->ctype) && strcmp($typestring, $header->ctype)!=0)
			$typestring = $header->ctype;

		
		list($type, $subtype) = explode("/", $typestring);
		
		
		if (strcasecmp($subtype, "HTML")==0){
			// type is "TEXT/HTML"
			if ($my_prefs["html_in_frame"]){
				//$part = $structure->pathcode;
				include("./modules/wmail/include/read_message_print.inc");
			}else{
				$view_url = "?m=wmail&a=bridge&tab=0&xa=view&suppressHeaders=true&session=$user&folder=$folder_url&id=$id&part=$part&is_html=1";
				echo "<p>".$rmStrings[7];
				echo '<a href="'.$view_url.'" >'.$rmStrings[8].'</a>';
			}
		}else{
			// type "TEXT/PLAIN"
			//echo "<tt>\n";
			include("./modules/wmail/include/read_message_print.inc");
			//echo "</tt>\n";
		}
	}else if ($typeCode==1 && empty($part) && ($structure[0][0]=="message")){
		// message content type is message/rfc822
		$part = "1.1";
		$typestring=iml_GetPartTypeString($structure, $part);
		list($type, $subtype) = explode("/", $typestring);
		$typeCode=iml_GetPartTypeCode($structure, $part);
		$disposition=iml_GetPartDisposition($structure, $part);
		include("./modules/wmail/include/read_message_print.inc");
	}else if (($typeCode==1) || ($typeCode==2)){
		// multipart message
		$typestring=iml_GetPartTypeString($structure, $part);
		list($type, $subtype) = explode("/", $typestring);
		
		$mode=0;
		$subtypes = array("mixed"=>1, "signed"=>1, "related"=>1, "array"=>2, "alternative"=>2);
		$subtype = strtolower($subtype);
		if ($subtypes[$subtype]>0){
			$mode = $subtypes[$subtype];
		}else if (strcasecmp($subtype, "rfc822")==0){
			$temp_num = iml_GetNumParts($structure, $part);
			if ($temp_num > 0) $mode = 2;
		}else if (strcasecmp($subtype, "encrypted")==0){
			//check for RFC2015
			$first_part = $part.(empty($part)?"":".")."2";
			$encrypted_type = iml_GetPartTypeString($structure, $part.".1");
			if (stristr($encrypted_type, "pgp-encrypted")!==false){
				$mode=-1;
			}
		}
		
		echo "\n<!-- MODE:  $mode TypeString: $typestring Sub: $subtype //-->\n";
		
		if ($mode==-1){
			//handle RFC2015 message
			$part = $part.(empty($part)?"":".")."2";
			$typestring=iml_GetPartTypeString($structure, $part);
			list($type, $subtype) = explode("/", $typestring);
			$typeCode=iml_GetPartTypeCode($structure, $part);
			$disposition=iml_GetPartDisposition($structure, $part);
			include("./modules/wmail/include/read_message_print.inc");
		}else if ($mode > 0){
			$originalPart=$part;
			/*
			//show attachments/parts
			if ($num_parts > 0){
				$parts_text = "";
				$parts_text .= "<tr class=\"tableForm_bg\"><td valign=\"top\" colspan=\"2\">\n";
				$parts_text .= "<b>".$rmStrings[6].": </b>\n";
				$parts_text .= "<table size=100%><tr valign=top>
								<col width=\"60%\"><col width=\"10%\"><col width=\"40%\"><col width=\"10%\">
				<tr>\n";
				//echo "<td valign=\"top\"><b>".$rmStrings[6].": </b>\n";
				$parts_text .= "<td></td>\n";
				$parts_text .= "<td valign=\"top\"><b>&nbsp;&nbsp;&nbsp;&nbsp;</b></td>\n";
				
				$icons_a = array("text.gif", "multi.gif", "multi.gif", "application.gif", "music.gif", "image.gif", "movie.gif", "unknown.gif");
				$subtype_ext = array(
				"plain" => "txt",
				"alternative" => "eml",
				"ms-tnef" => "eml",
				);
				for ($i=1;$i<=$num_parts;$i++){
					//get attachment info
					if ($parent_type == 1)
						$code=$part.(empty($part)?"":".").$i;
					else if ($parent_type == 2){
						$code=$part.(empty($part)?"":".").$i;
						//$parts_text .= implode(" ", iml_GetPartArray($structure, $code));
					}
						
					$type=iml_GetPartTypeCode($structure, $code);
					$name=iml_GetPartName($structure, $code);
					$typestring=iml_GetPartTypeString($structure,$code);
					list($dummy, $subtype) = explode("/", $typestring);
					$bytes=iml_GetPartSize($structure,$code);
					$encoding=iml_GetPartEncodingCode($structure, $code);
					$disposition = iml_GetPartDisposition($structure, $code);
				
					
					//format href
					if (($type == 1) || ($type==2) || (($type==3)&&(strcasecmp($subtype, "ms-tnef")==0))){
						$href = "?m=wmail&a=bridge&tab=0&xa=read_message&session=$user&folder=$folder_url&id=$id&part=".$code;
						$target = "_blank";
					}	else {
						$href = "?m=wmail&a=bridge&tab=0&suppressHeaders=true&xa=view&session=$user&folder=$folder_url&id=$id&part=".$code;
						$target = "filedownload";
						if (($type == 0) || ($type==3) || ($type==4) || ($type==5) || ($type==6) || ($type==7)){
							$href .= "&viewinline=1";
							$target = "_blank";
						}
						
					}
					
					$href_download = "?m=wmail&a=bridge&tab=0&suppressHeaders=true&xa=view&session=$user&folder=$folder_url&id=$id&part=".$code."&forcedownload=1";
					$target_download = "filedownload";
					//show icon, file name, size
						
					$parts_text .= "<tr>";
					$parts_text .= "<td>";
					$parts_text .= "<a target='".$target."' href=\"".$href."\" ".(($type==1)||($type==2)||(($type==3)&&(strcasecmp($subtype, "ms-tnef")==0))?"":"").">";
					$part_description = $typestring;
					if (( ($name == -1 || $name == "" ) && $type == 0 && $subtype!="alternative")
						|| strcasecmp($subtype, "ms-tnef")==0 ){
						
						$ext = isset($subtype_ext[$subtype]) ? $subtype_ext[$subtype] : $subtype;
						$name = "Message_.$ext";
					}		
					if (is_string($name)){
						
						$real_filename = LangDecodeSubject($name, $my_charset);
						$file_parts = pathinfo($real_filename);
						$parts_text .= dPshowImage( 'filetype.php?extension='.$file_parts["extension"], '16', '16', $real_filename );
						$file_type = new CFileTypes();
						if ($file_type->loadByExtension($file_parts["extension"]))
							$part_description = $file_type->friendly;
						unset ($file_type);
					}else{
						$parts_text .= "<img src=\"modules/wmail/themes/".$my_prefs["theme"]."/images/".$icons_a[$type]."\" border=0>";
					}
					$parts_text .= "&nbsp;";
					//$parts_text .= "<span class=\"small\">";
					if (is_string($name)) $parts_text .= LangDecodeSubject($name, $my_charset);
					$parts_text .= "</a></td>";
					$parts_text .= "<td align=\"right\">";
					if ($bytes>0) $parts_text .= "".ShowBytes($bytes)."";
					else $parts_text .= "&nbsp;";
					$parts_text .= "</td>";
					$parts_text .= "<td>";
					if (is_string($part_description)) $parts_text .= $part_description;
					$parts_text .= "</td>";
					$parts_text .= "<td>";
					if ($bytes>0)
						$parts_text .= "<a target='".$target_download."' href=\"".$href_download."\">".
										$AppUI->_('download')."</a>";
					else $parts_text .= "&nbsp;";								
					$parts_text .= "</td>";
					$parts_text .= "</tr>";
				}
				$parts_text .= "</tr>\n</table>\n";
				$parts_text .= "</td></tr>\n";
			}
					
			
			
			*/
			
			
			
			
			
			
			for ($i=1;$i<=$num_parts;$i++){
				//get part info
				$part=$originalPart.(empty($originalPart)?"":".").$i;
				$typestring=iml_GetPartTypeString($structure, $part);
				list($type, $subtype) = explode("/", $typestring);
				$typeCode=iml_GetPartTypeCode($structure, $part);
				$disposition=iml_GetPartDisposition($structure, $part);
				
				echo "\n<!-- \nPart: $part \nTypeCode: $typeCode \nMode: $mode \nType: $typestring \nSubtype: $subtype \n Disp: $disposition \n//-->\n";
				
				//if NOT attachemnt...
				if (strcasecmp($disposition, "attachment")!=0){
					if (($mode==1) && ($typeCode==0)){
						//if "mixed" and type is "text" then show
						include("./modules/wmail/include/read_message_print.inc");
					}else if ($mode==2){
						//if "alternative" and type is "text/plain" then show
						if ($my_prefs["html_in_frame"] && strcasecmp($subtype, "html")==0){
							//"Show HTML" and is html, then show
							include("./modules/wmail/include/read_message_print.inc");
						}else if (!$my_prefs["html_in_frame"] && strcasecmp($subtype, "plain")==0){
							//Not "Show HTML" and not html, then show
							include("./modules/wmail/include/read_message_print.inc");
						}
					}else if (($typeCode==5) && (strcasecmp($disposition, "inline")==0 || $my_prefs["show_images_inline"])){
						//if type is image and disposition is "inline" show
						echo "<img src=\"?m=wmail&a=bridge&tab=0&xa=view&suppressHeaders=true&session=$user&folder=$folder_url&id=$id&part=".$part."\">";
					}else if ($typeCode==1){
						//multipart part
						$part = iml_GetFirstTextPart($structure, $part);
						if ($my_prefs["html_in_frame"]){
							//if HTML preferred, see if next part is HTML
							$next_part = iml_GetNextPart($part);
							$next_type = iml_GetPartTypeString($structure, $next_part);
							echo "\n<!-- want html: $next_part '$next_type' //-->\n";
							//if it is HTML, use it instead of text part
							if (stristr($next_type,"html")!==false){
								$typestring = "text/html";
								$type = "text";
								$subtype = "html";
								$part = $next_part;
							}
							$i++;
						}
						include("./modules/wmail/include/read_message_print.inc");
					}
				}else{
					if (($typeCode==5) && ($my_prefs["show_images_inline"])){
						echo "<img src=\"?m=wmail&a=bridge&tab=0&xa=view&suppressHeaders=true&session=$user&folder=$folder_url&id=$id&part=".$part."\"><br>\n";
					}
				}
			}
		}else{
			//echo "<p>This is a multi-part MIME message.";
			if (strcasecmp($subtype, "rfc822")!=0){
				$part = iml_GetFirstTextPart($structure, "");
				if ($my_prefs["html_in_frame"]){
					//if HTML preferred, see if next part is HTML
					$next_part = iml_GetNextPart($part);
					$next_type = iml_GetPartTypeString($structure, $next_part);
					echo "\n<!-- want html: $next_part '$next_type' //-->\n";
					//if it is HTML, use it instead of text part
					if (stristr($next_type,"html")!==false){
						$typestring = "text/html";
						$type = "text";
						$subtype = "html";
						$part = $next_part;
					}
				}
			}
			include("./modules/wmail/include/read_message_print.inc");
		}
	}else{
		//not text or multipart, i.e. it's a file
			
		$type=iml_GetPartTypeCode($structure, $part);
		$name=iml_GetPartName($structure, $part);
		$typestring=iml_GetPartTypeString($structure,$part);
		$bytes=iml_GetPartSize($structure,$part);
		$encoding=iml_GetPartEncodingCode($structure, $part);
		$disposition = iml_GetPartDisposition($structure, $part);
		$icons_a = array("text.gif", "multi.gif", "multi.gif", "application.gif", "music.gif", "image.gif", "movie.gif", "unknown.gif");
		$href = "?m=wmail&a=bridge&tab=0&xa=view&suppressHeaders=true&session=$user&folder=$folder_url&id=$id&part=".$part;
		echo "<table><tr><td align=\"center\">";
		echo "<a href=\"".$href."\" target=_blank>";
		echo "<img src=\"modules/wmail/themes/".$my_prefs["theme"]."/images/".$icons_a[$type]."\" border=0><br>";
		echo "<font size=\"-1\">".LangDecodeSubject($name, $my_charset)."<br>[".ShowBytes($bytes)."]";
		echo "<br>".$typestring."</font>";
		echo "</a>";
		echo "</td></tr></table>\n";
	}
