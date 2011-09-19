<?php
/////////////////////////////////////////////////////////
//	
//	include/read_message_tools.inc
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
		See "source/read_message.php"
	PRE-CONDITIONS:
		See "source/read_message.php"
	COMMENTS:
		Basically exiled into an include because it was an obnoxious block of code
		that messed up code-aesthetics beyond reason.
		
********************************************************/
//https://ssl.ilohamail.org/webmail/read_message.php?user=1056651142-97236&folder=INBOX&id=140&uid=6413&start=17&num_msgs=163&sort_field=DATE&sort_order=DESC
				$folder_url = urlencode($folder);
				if ($my_prefs["compose_inside"]) $target="list2";
				else $target="scr".$user.$folder_url.$id;

				echo "<table border=0 width=\"100%\" cellpadding=0 cellspacing=0><tr class=\"tableHeaderGral\"><td>\n";
				echo "<table  cellpadding=0 cellspacing=0><tr class=\"tableHeaderGral\"><td class=\"tableHeaderText\">\n";
				echo "\n";
				
				if (!empty($prev_link)) echo $prev_link.("&nbsp;");
				if ($my_prefs["view_inside"]){
					echo "<!--a href=\"?m=wmail&a=bridge&tab=0&session=$user&folder=$folder&start=$start&sort_field=$sort_field&sort_order=$sort_order\" class=mainHeadingSmall>";
					if ($ICL_CAPABILITY["folders"]){
						$folder_name = $defaults[$folder];
						if (empty($folder_name)){
							$delim = iil_C_GetHierarchyDelimiter($conn);
							$pos = strrpos($folder, $delim);
							if ($pos!==false) $pos++;
							$folder_name = substr($folder, $pos);
						}
						echo $folder_name;
					}else{
						echo $rmStrings[0];
					}
					echo "</a-->\n";
				}
				if (!empty($next_link)) echo $next_link.("&nbsp;&nbsp;&nbsp;&nbsp;");
				else echo "\n&nbsp;|&nbsp;\n";

				echo "<a href=\"?m=wmail&a=bridge&tab=1&session=$user&replyto=1&folder=$folder_url&id=$id&uid=$uid&part=$part\" target=\"$target\">";
					echo "<img src=\"modules/wmail/themes/".$my_prefs["theme"]."/images/reply.gif\" border=\"0\">".$rmStrings[1];
				echo "</a>\n";
				echo "\n&nbsp;|&nbsp;\n";
				if ($multiple_recipients){
					$url = "?m=wmail&a=bridge&tab=1&session=$user&replyto=1&replyto_all=1&folder=$folder_url&id=$id&uid=$uid&part=$part";
					echo "<a href=\"$url\" target=\"$target\">";
						echo "<img src=\"modules/wmail/themes/".$my_prefs["theme"]."/images/allr.gif\" border=\"0\">".$rmStrings[14];
					echo "</a>\n";
					echo "\n&nbsp;|&nbsp;\n";
				}
				echo "<a href=\"?m=wmail&a=bridge&tab=1&session=$user&forward=1&folder=$folder_url&id=$id&uid=$uid&part=$part\" target=\"$target\" >";
					echo "<img src=\"modules/wmail/themes/".$my_prefs["theme"]."/images/forward.gif\" border=\"0\">".$rmStrings[2];
				echo "</a>\n";
				echo "\n&nbsp;|&nbsp;\n";
				if ($header->deleted) echo "<a href=\"read_message.php?user=$user&folder=$folder_url&id=$id&undelete=1&start=$start\" >".$rmStrings[3]."</a>";
 				else {
 				    echo "\n<script type=text/javascript language=JavaScript1.2>\n ";
					echo "if (window.name!='list2') window.name=\"$target\";\n";
 				    echo "function deletemsg() {\n var url=\"?m=wmail&tab=0&a=bridge&session=$user&folder=$folder_url&checkboxes[]=$id&uids[]=$uid&submit=Delete&start=$start\";\n if (opener) { opener.parent.list2.location=url; window.close();} else location=url;\n}\n";
 				    echo "document.write(\"<a href='javascript:deletemsg();' >"."<img src=modules/wmail/themes/".$my_prefs["theme"]."/images/del.gif border=0>".$rmStrings[4]."</a>\");\n";
					 echo "</script>\n";
 				    echo "<noscript>\n";
					 	echo "<a href=\"?m=wmail&a=bridge&tab=0&session=$user&folder=$folder_url&checkboxes[]=$id&uids[]=$uid&submit=Delete&start=$start\">\n";
						 echo "<img src=\"modules/wmail/themes/".$my_prefs["theme"]."/images/del.gif\" border=\"0\">".$rmStrings[4];
						echo "</a>\n";
					echo "</noscript>\n";
 				}
				echo "&nbsp;|&nbsp;";
				echo "<a href=\"?m=wmail&a=bridge&tab=0&session=$user&folder=$folder_url&checkboxes[]=$id&uids[]=$uid&submit=Unread&start=$start\" >";
				echo "<img src=\"modules/wmail/themes/".$my_prefs["theme"]."/images/unread.gif\" border=\"0\">".$rmStrings[11]."</a>\n";
				echo "\n";
				echo "</td></tr></table>\n";
				echo "</td><td align=\"right\" valign=\"top\">\n";
					echo "<form method=\"POST\" action=\"?m=wmail&a=bridge&tab=0&session=$user\">\n";
					echo "<input type=\"hidden\" name=\"user\" value=\"".$user."\">\n";
					echo "<input type=\"hidden\" name=\"folder\" value=\"".$folder."\">\n";
					echo "<input type=\"hidden\" name=\"checkboxes[]\" value=\"".$id."\">\n";
					echo "<input type=\"hidden\" name=\"uids[]\" value=\"".$uid."\">\n";
					echo "<input type=\"hidden\" name=\"start\" value=\"".$start."\">\n";
					echo "<input type=\"hidden\" name=\"max_messages\" value=\"".($id+1)."\">\n";
					if ($ICL_CAPABILITY["folders"]){
						if (!is_array($folderlist)){
							$cached_folders = cache_read($loginID, $host, "folders");
							if (is_array($cached_folders)){
								echo "<!-- Read cache! //-->";
								$folderlist = $cached_folders;
							}else{
								echo "<!-- No cache...";
								if ($my_prefs["hideUnsubscribed"]) $folderlist = iil_C_ListSubscribed($conn, $my_prefs["rootdir"], "*");
								else $folderlist = iil_C_ListMailboxes($conn, $my_prefs["rootdir"], "*");
								$cache_result = cache_write($loginID, $host, "folders", $folderlist);
								echo "write: $cache_result //-->";
							}
							//if ($my_prefs["hideUnsubscribed"]) $folderlist = iil_C_ListSubscribed($conn, $my_prefs["rootdir"], "*");
							//else $folderlist = iil_C_ListMailboxes($conn, $my_prefs["rootdir"], "*");
						}
						//$folderlist=iil_C_ListMailboxes($conn, $my_prefs["rootdir"], "*");
						echo "<select name=moveto>";
						RootedFolderOptions($folderlist, $defaults, $my_prefs["rootdir"]);
						echo "</select>";
						echo "<input type=submit name=\"move_selected\" value=\"".$rmStrings[5]."\">";
					}
					echo "</form>\n";
				echo "</td></tr></table>\n";
				flush();
?>