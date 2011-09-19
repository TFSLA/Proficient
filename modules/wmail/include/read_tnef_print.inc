<?php 
/////////////////////////////////////////////////////////
//	
//	include/read_tnef_print.inc
//
//	Written by: Henry Fong
//  Notes: Copied from read_message_print.inc by Ryo Chijiiwa
//
//	This file is a hack for IlohaMail. IlohaMail is free software released 
//	under the GPL license.  
//
/////////////////////////////////////////////////////////

/********************************************************

	PURPOSE:
		Actual code that displays tnef message body part in "source/read_message.php"
	PRE-CONDITIONS:
		Patched read_message.php and extra file tnef_decoder.inc 
	COMMENTS:
		Looks ugly but it does the job

********************************************************/

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
					
					
			//show attachments/parts
			$num_parts = sizeof($tnef_files);
			if (sizeof($tnef_files) > 0){
				$tnef_parts_text = "";
				//$tnef_parts_text .= "<tr class=\"tableForm_bg\"><td valign=\"top\" colspan=\"2\">\n";
				/*$tnef_parts_text .= "<b>".$rmStrings[6].": </b>\n";
				$tnef_parts_text .= "<table size=100%><tr valign=top>
								<col width=\"60%\"><col width=\"10%\"><col width=\"40%\"><col width=\"10%\">
				<tr>\n";
				$tnef_parts_text .= "<td></td>\n";
				$tnef_parts_text .= "<td valign=\"top\"><b>&nbsp;&nbsp;&nbsp;&nbsp;</b></td>\n";
				*/
				$icons_a = array("text.gif", "multi.gif", "multi.gif", "application.gif", "music.gif", "image.gif", "movie.gif", "unknown.gif");
				$subtype_ext = array(
				"plain" => "txt",
				"alternative" => "eml",
				"ms-tnef" => "eml",
				);
				$types=array("text"=>0,"multipart"=>1,"message"=>2,"application"=>3,"audio"=>4,"image"=>5,"video"=>6,"other"=>7);
				for ($i=0;$i<sizeof($tnef_files);$i++){
					extract($tnef_files[$i]);
					$typestring = $type0."/".$type1;
					$type = 7;
					if (isset($types[$type0]))
						$type = $types[$type0];
					
					$dummy = $type0;
					$subtype = $type1;
					$bytes = $size;
				
					
					//format href
					if (($type == 1) || ($type==2) || (($type==3)&&(strcasecmp($subtype, "ms-tnef")==0))){
						$href = "?m=wmail&a=bridge&tab=0&xa=read_message&session=$user&folder=$folder_url&id=$id&part=".$code."&tneffid=$i";
						$target = "_blank";
					}	else {
						$href = "?m=wmail&a=bridge&tab=0&suppressHeaders=true&xa=view&session=$user&folder=$folder_url&id=$id&part=".$code."&tneffid=$i" ;
						$target = "filedownload";
						$href .= "&forcedownload=1";
						
						/*
						if (($type == 0) || ($type==3) || ($type==4) || ($type==5) || ($type==6) || ($type==7)){
							$href .= "&viewinline=1";
							$target = "_blank";
						}
						*/
						
					}
					
					$href_download = "?m=wmail&a=bridge&tab=0&suppressHeaders=true&xa=view&session=$user&folder=$folder_url&id=$id&part=".$code."&forcedownload=1"."&tneffid=$i";
					$target_download = "filedownload";
					//show icon, file name, size
						
					$tnef_parts_text .= "<tr>";
					$tnef_parts_text .= "<td>";
					$tnef_parts_text .= "<a target='".$target."' href=\"".$href."\" ".(($type==1)||($type==2)||(($type==3)&&(strcasecmp($subtype, "ms-tnef")==0))?"":"").">";
					$part_description = $typestring;
					if (( ($name == -1 || $name == "" ) && $type == 0 && $subtype!="alternative")
						|| strcasecmp($subtype, "ms-tnef")==0 ){
						
						$ext = isset($subtype_ext[$subtype]) ? $subtype_ext[$subtype] : $subtype;
						$name = "Message_.$ext";
					}		
					if (is_string($name)){
						
						$real_filename = LangDecodeSubject($name, $my_charset);
						$file_parts = pathinfo($real_filename);
						$tnef_parts_text .= dPshowImage( 'filetype.php?extension='.$file_parts["extension"], '16', '16', $real_filename );
						$file_type = new CFileTypes();
						if ($file_type->loadByExtension($file_parts["extension"]))
							$part_description = $file_type->friendly;
						unset ($file_type);
					}else{
						$tnef_parts_text .= "<img src=\"modules/wmail/themes/".$my_prefs["theme"]."/images/".$icons_a[$type]."\" border=0>";
					}
					$tnef_parts_text .= "&nbsp;";
					//$tnef_parts_text .= "<span class=\"small\">";
					if (is_string($name)) $tnef_parts_text .= LangDecodeSubject($name, $my_charset);
					$tnef_parts_text .= "</a></td>";
					$tnef_parts_text .= "<td align=\"right\">";
					if ($bytes>0) $tnef_parts_text .= "".ShowBytes($bytes)."";
					else $tnef_parts_text .= "&nbsp;";
					$tnef_parts_text .= "</td>";
					$tnef_parts_text .= "<td>";
					if (is_string($part_description)) $tnef_parts_text .= $part_description;
					$tnef_parts_text .= "</td>";
					$tnef_parts_text .= "<td>";
					if ($bytes>0)
						$tnef_parts_text .= "<a target='".$target_download."' href=\"".$href_download."\">".
										$AppUI->_('download')."</a>";
					else $tnef_parts_text .= "&nbsp;";								
					$tnef_parts_text .= "</td>";
					$tnef_parts_text .= "</tr>";		
				}
				/*
				$tnef_parts_text .= "</tr>\n</table>\n";
				$tnef_parts_text .= "</td></tr>\n";*/
			}			
			//$old_buffer = ob_get_contents();
			//ob_clean();
			//echo $tnef_parts_text;
			$parts_text .= $tnef_parts_text;
					
					
					
					/*
					echo "<table border=0 size=90%><tr>";
					for($i=0;$i<sizeof($tnef_files);$i++)
					{
						$tmptype = $tnef_files[$i]['type0'];
						//format href
						$href = "?m=wmail&a=bridge&tab=0&xa=view&suppressHeaders=true&session=$user&folder=$folder_url&id=$id&part=".$part."&tneffid=".$i;
						//show icon, file name, size
						echo "<td align=center><a href=\"".$href."\">";
						echo "<img src=\"images/".$tmptype.".gif\" border=0><br>";
						echo "<font size=\"-1\">".LangConvert($tnef_files[$i]['name'], $my_charset)."<br>[".ShowBytes($tnef_files[$i]['size'])."]";                        echo "<br>".$tnef_files[$i]['type0']."/".$tnef_files[$i]['type1']."</font>";
						echo "</a></td>";
					}
					echo "</tr></table>";
					*/
					//flush();
					//ob_clean();
					
?>
