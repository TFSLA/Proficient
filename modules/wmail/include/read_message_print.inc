<?php
/////////////////////////////////////////////////////////
//	
//	include/read_message_print.inc
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
		Actual code that displays message body part in "source/read_message.php"
	PRE-CONDITIONS:
		There's a bunch.  Look in "source/read_message.php"
	COMMENTS:
		Yeah, I know it's messy.

********************************************************/

				echo "\n<!-- Printing part: $part //-->\n";


				/* figure out the body part's type */
					
					if (empty($typestring) || empty($type) || empty($subtype)){
						$typestring=iml_GetPartTypeString($structure, $part);
						list($type, $subtype) = explode("/", $typestring);
					}
					
				/* fetch body part */
					$body=iil_C_FetchPartBody($conn, $folder, $id, $part);

				/* decode body part */
					$encoding=iml_GetPartEncodingCode($structure, $part);
					if ($encoding == 3 ) $body=base64_decode($body);
					else if ($encoding == 4) $body=quoted_printable_decode($body);

				/* detect HTML */
					if (eregi("^<!DOCTYPE", $body) && eregi("<html>", $body)){
						if ($my_prefs["html_in_frame"]){
							$subtype = "html";
						}else{
							$body = "";
							$view_url = "?m=wmail&a=bridge&tab=0&xa=view&suppressHeaders=true&session=$user&folder=$folder_url&id=$id&part=$part&is_html=1";
							echo "<p>".$rmStrings[7];
							echo '<a href="'.$view_url.'" >'.$rmStrings[8].'</a>';
						}
					}

				/* decrypt message */
					if (eregi("-----BEGIN PGP MESSAGE-----.*-----END PGP MESSAGE-----", $body))
					{
						//show password field if passphrase not specified
						if (empty($gpg_passphrase)){
							?>
							<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']?>">
							GPG Passphrase: <input type="password" name="gpg_passphrase" value="">
							<input type="submit" name="gpg_decrypt" value="Decrypt">
							</form>
							<?php
						}else{
							include("./modules/wmail/include/gpg.inc");
							gpg_decrypt($gpg_passphrase, $body);
						}
					} else { 
					}

				/* check if UTF-8 */
					$charset=iml_GetPartCharset($structure, $part);
					if (strcasecmp($charset, "utf-8")==0){
						include_once("./modules/wmail/include/utf8.inc");
						$is_unicode = true;
						//$body = utf8ToUnicodeEntities($body);
					}else if (preg_match("/#[0-9]{5};/", $body)){
						//look for unicode that look like #12345; (without '&')
						$body = preg_replace("/(?<!&)(#[0-9]{5};)/", "&$1", $body);
						$is_unicode = false;
					}else{
						$is_unicode = false;
					}
						
				/* run through character encoding engine */
					echo "\n<!-- converting from $charset to $my_charset //-->\n";
					$body=LangConvert($body, $my_charset, $charset);

				/* dump! */
					//echo "<p>";
					if (strcasecmp($subtype, "html")==0){
						if (!$my_prefs["html_in_frame"]){
							$body=strip_tags($body, '<a><b><i><u><p><br><hr><font><div><style><img><table><tr><td><th><col><span>');
						} else {				
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
										/*$name=iml_GetPartName($structure, $code);
										$typestring=iml_GetPartTypeString($structure,$code);
										list($dummy, $subtype) = explode("/", $typestring);
										$bytes=iml_GetPartSize($structure,$code);
										$encoding=iml_GetPartEncodingCode($structure, $code);
										$disposition = iml_GetPartDisposition($structure, $code);										
										$images[$part_id] = array(
															"code" => $codepart,
															"name" => $name,
															"bytes" => $bytes,
															"disposition" => $disposition,
															"url" => "?m=wmail&a=bridge&tab=0&suppressHeaders=true&xa=view&session=$user&folder=$folder_url&id=$id&part=$i&viewinline=1");
										*/
										$images[$part_id] =	"?m=wmail&a=bridge&tab=0&suppressHeaders=true&xa=view&session=$user&folder=$folder_url&id=$id&part=$i&viewinline=1";
										if (strpos($body, "cid:$part_id")){
											$body = str_replace("cid:$part_id", $images[$part_id], $body);
										}		
									}
								}
							}

							
							$view_url = "?m=wmail&a=bridge&tab=0&xa=view&suppressHeaders=true&session=$user&folder=$folder_url&id=$id&part=$part&is_html=1&viewinframe=1";
							
							//muestra el mensaje en un iframe y lo autoajusta a su tamaño
							$body = "
							<script type=\"text/javascript\">
							function calcHeight(id)
							{
								if(document.getElementById && !(document.all))
								{
									h = document.getElementById(id).contentDocument.body.scrollHeight;
									document.getElementById(id).style.height = h+20+'px';
								}
								else if(document.all)
								{
									h = document.frames(id).document.body.scrollHeight;
									document.all[id].style.height = h+20+'px';
								}
							
							}	
							</script>							
							<iframe src=\"$view_url\" id=\"message_text\" 
							width=\"100%\" 
							marginwidth=\"0\" marginheight=\"0\" 
							onload=\"calcHeight('message_text');\" frameborder=0></iframe>";
						}
						
						
						
						echo $body;
					}else{
						/* quote colorization */
                        $process = (($my_prefs["colorize_quotes"]==1) || ($my_prefs["detect_links"]==1));
                        if ($process){
                            $color=$my_colors["quotes"];
                            if (empty($color)) $color="blue";
                            
                            $lines=explode("\n", $body);
                            while (list($key, $line)=each($lines)){
                                $line = chop($line);
								
								//color quotes
                                if ($my_prefs["colorize_quotes"]==1){
                                    //colorize quotes
                                    if (($line[0]==">")&&(!$quoteLN)){
                                        $quoteLN=true;
                                        echo "<font color=$color>";
                                    }
                                    if (($line[0]!=">")&&($quoteLN)){
                                        $quoteLN=false;
                                        echo "</font>";
                                    }
                                }
								
								//detect links
								$html_encoded = false;
                                if ($my_prefs["detect_links"]==1){
                                    //detect URL
									$pattern = "/(.*)([fh]+[t]*tp[s]*:\/\/[a-zA-Z0-9_~#=&%\/\:;@,\.\?\+-]+)(.*)/";
									if (preg_match($pattern, $line, $match)){
										$line = encodeHTML($match[1]);
										$line.= "<a href=\"".$match[2]."\" target=_blank\">".$match[2]."</a>";
										$line.= encodeHTML($match[3]);
										$html_encoded = true;
									}
									/*
									if (ereg("[fht]+tp[s]*://", $line)){
										$words = explode(" ", $line);
										while ( list($k2, $word) = each($words) ){
											$words[$k2] = (detectURLinWord($word));
										}
										$html_encoded = true;
										$line = implode(" ", $words);
									}
									*/
                                }
								
								//encode and spit out
								if (!$html_encoded){
									if ($is_unicode) $line = utf8ToUnicodeEntities($line);	
									else $line = encodeUTFSafeHTML($line);
								}
		                        //if ((!$html_encoded) && (!$is_unicode)) $line = encodeHTML($line);
								//else if ($is_unicode) $line = utf8ToUnicodeEntities($line);							
							
								//convert leading spaces to &nbsp;
								$indent = "";
								for ($c_pos=0; $line[$c_pos]==' ' || $line[$c_pos]=='\t'; $c_pos++){
									if ($line[$c_pos]==' ') $indent .= "&nbsp;";
									else if ($line[$c_pos]=='\t') $index .= "&nbsp;&nbsp;&nbsp;&nbsp;";
								}
								$line = $indent.trim($line);

                                echo $line."<br>\n"; flush();
                            }
						}else{
							if (!$is_unicode) $body = encodeHTML($body);
							else  $body = utf8ToUnicodeEntities($body);

							echo "<p>".nl2br($body);
						}
					}

					flush();
					
?>
