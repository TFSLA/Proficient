<?php

include_once("./modules/wmail/include/encryption.inc");
include_once("./modules/wmail/include/version.inc");
include_once("./modules/wmail/include/langs.inc");
include_once("./modules/wmail/conf/conf.inc");
include_once("./modules/wmail/conf/login.inc");
include_once("./modules/wmail/conf/defaults.inc");
include_once("./modules/wmail/conf/db_conf.php");
//echo "var:".$DB_HOST;
//echo "<br>global:".$GLOBALS["DB_HOST"];
global $GLOBALS, $AppUI;
$hideseen = $GLOBALS["hideseen"];
$infobox = $GLOBALS["infobox"];
//echo "<br>global: hideseen=$hideseen  infobox=$infobox";

$session = $GLOBALS["session"];
$folder  = $GLOBALS["folder"];
$user    = $session;
//include_once("./modules/wmail/include/header.inc.php");
//include_once("./modules/wmail/include/optionfolders.inc.php");
/////////////////////////////////////////////////////////
//	
//	source/main.php
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
	FILE: source/main.php
	PURPOSE:
		1.  List specified number of messages in specified order from given folder.
		2.  Provide interface to read messages (link subjects to source/read_message.php)
		3.  Provide interface to send messasge to senders (link "From" field to source/compose.php)
		4.  Provide interface to move or delete messages
		5.  Provide interface to view messages not currently listed.
		6.  Provide functionality to move, delete messages and expunge folders.
	PRE-CONDITIONS:
		$user - Session ID
		$folder - Folder name
		[$sort_field] - Field to sort by {"subject", "from", "to", "size", "date"}
		[$sort_order] - Order, "ASC" or "DESC"
		[$start] - Show specified number of messages starting with this index

********************************************************/

$exec_start_time = microtime();

include_once("./modules/wmail/include/stopwatch.inc");
$clock = new stopwatch;

$clock->register("start");
$tmpuser=$user;
include_once("./modules/wmail/include/super2global.inc");
$user=$tmpuser;
$clock->register("pre-header");
include_once("./modules/wmail/include/header_main.inc");
$clock->register("post-header");
include_once("./modules/wmail/include/ryosdates.inc");
include_once("./modules/wmail/include/icl.inc");
include_once("./modules/wmail/include/main.inc");
include_once("./modules/wmail/include/cache.inc");
$clock->register("includes done");
	if (!isset($folder)) $folder="INBOX";
	if (!isset($folder)){
		echo "Error: folder not specified";
		exit;	
	}
	include_once("./modules/wmail/lang/".$my_prefs["lang"]."defaultFolders.inc");
	include_once("./modules/wmail/lang/".$my_prefs["lang"]."main.inc");
	include_once("./modules/wmail/lang/".$my_prefs["lang"]."dates.inc");

	//initialize some vars
	if (!isset($hideseen)) $hideseen=0;
	if (!isset($showdeleted)) $showdeleted=0;
	if (strcmp($folder, $my_prefs["trash_name"])==0) $showdeleted=1;
	if (empty($my_prefs["main_cols"])) $my_prefs["main_cols"]="camfsdz";
	
	$clock->register("pre-connect");
	
	//connect to mail server
	$conn = iil_Connect($host, $loginID, $password, $AUTH_MODE);
	if (!$conn){
		echo "Connection failed: $iil_error <br> ";
		exit;
	}
	
	$clock->register("post-connect");
		
	echo "\n<!-- ICLMessages:\n".$conn->message."-->\n";
	
	//move columns
	//$MOVE_FIELDS = 1;
	if ($MOVE_FIELDS){
		$report = $mainErrors[8];
		if ($move_col && $move_direction){
			//echo "Moving fields <br>\n";
			$col_pos = strpos($my_prefs["main_cols"], $move_col);
			if ($col_pos !== false){
				if ($move_direction=="right") $move_direction = 1;
				else if ($move_direction=="left") $move_direction = -1;
				$partner_col = $my_prefs["main_cols"][$col_pos+$move_direction];
				//echo "Shift is $move_direction switching with $partner_col <br>\n";
				if ($partner_col){
					$my_prefs["main_cols"][$col_pos+$move_direction] = $move_col;
					$my_prefs["main_cols"][$col_pos] = $partner_col;
					include("./modules/wmail/include/save_prefs.inc");
				}
			}
		}
	}
	

	//default names for toolbar input fields, used in main_tools.inc as well
	$main_tool_fields = array("expunge", "empty_trash", "delete_selected",
								"mark_read", "mark_unread", "moveto", "move_selected");

	//if toolbar displayed at top & bottom, bottom fields will have '_2' appened
	//at the end of field name.  we deal with that here
	reset($main_tool_fields); 
	while ( list($k,$tool_field)=each($main_tool_fields) ){
		$tool_var_name = $tool_field."_2";
		$tool_var_val = $$tool_var_name;
		if (!empty($tool_var_val)) $$tool_field = $tool_var_val;
	}										
	
	//actions (flagging, deleting, moving, etc)
	if ($move_selected) $submit = "File";
	if ($delete_selected) $submit = "Delete";
	if ($empty_trash) $submit = "Expunge";
	if ($mark_read) $submit = "Read";
	if ($mark_unread) $submit = "Unread";
	
	if (isset($submit)){
		$messages="";
		
		/* compose an IMAP message list string including all checked items */
		if ((is_array($uids)) && (implode("",$uids)!="")){
			$checkboxes = iil_C_Search($conn, $folder, "UID ".implode(",", $uids));
		}
		if (is_array($checkboxes)){
               $messages = implode(",", $checkboxes);
               $num_checked = count($checkboxes);
		}
		
		/* "Move to trash" is same as "Delete" */
		if (($submit=="File") && (strcmp($moveto, $my_prefs["trash_name"])==0)) $submit="Delete";
           
		/*  delete all */
		if ($delete_all == 2 ){
			$messages .= "1:".$delete_all_num;
		}
					
		/* delete items */
		if (($submit=="Delete")||(strcmp($submit,$mainStrings[10])==0)){
			if (iil_C_Delete($conn, $folder, $messages) > 0){	
                   if ($ICL_CAPABILITY["folders"]){
                       if (strcmp($folder, $my_prefs["trash_name"])!=0){
                           if (!empty($my_prefs["trash_name"])){
                               if (iil_C_Move($conn, $messages, $folder, $my_prefs["trash_name"]) >= 0){
                                   $report =  str_replace("%n", $num_checked, $mainMessages["delete"]);
                               }else{
                                   $report = $mainErrors[2].":".$messages;
                               }
                           }else{
                               $report = str_replace("%n", $num_checked, $mainMessages["delete"])."<br>".$mainErrors[5];
                           }
                       }else{
                           $report = $mainErrors[3].":".$messages;
                       }
                   }else{
                       $report =  str_replace("%n", $num_checked, $mainMessages["delete"]);
                   }
			}
		}
		
		/*  move items */
		if (($submit=="File")||(strcmp($submit,$mainStrings[12])==0)){
			if (strcasecmp($folder, $my_prefs["trash_name"])==0){
				iil_C_Undelete($conn, $folder, $messages);
			}
			if (iil_C_Move($conn, $messages, $folder, $moveto) >= 0){
				$report = str_replace("%n", $num_checked, $mainMessages["move"]);
				$report = str_replace("%f", $moveto, $report);
				if (strcasecmp($folder, $my_prefs["trash_name"])==0){
					iil_C_Delete($conn, $folder, $messages);
				}
			}else{
				$report = $mainErrors[4];
			}
		}
			
			
		/* empty trash  command */
		if (($submit=="Expunge") && ($expunge==1)){
			if ($folder==$my_prefs["trash_name"]){
				if (!iil_C_ClearFolder($conn, $folder)){
					echo $mainErrors[6]." (".$conn->error.")<br>\n";
				}
			}else{
				$error .= "Folder $folder is not trash (trash is ".$my_prefs["trash_name"].")<br>\n";
			}
		}
		
		/* expunge non-trash folders automatically */
		if (strcasecmp($folder,$my_prefs["trash_name"])!=0){
			iil_C_Expunge($conn, $folder);
		}
		
		/* mark as unread */
		if ($submit=="Unread"){
			iil_C_Unseen($conn, $folder, $messages);
			$reload_folders = true;
			$selected_boxes = $checkboxes;
		}
		
		/* mark as read */
		if ($submit=="Read"){
			iil_C_Flag($conn, $folder, $messages, "SEEN");
			$reload_folders = true;
			$selected_boxes = $checkboxes;
		}
	} //end if submit
		
		
	/* If search results were moved or deleted, stop execution here. */
	if (isset($search_done)){
		echo "<p>Request completed.\n";
		echo "</body></html>";
		exit;
	}
	
	/* initialize sort field and sort order 
		(set to default prefernce values if not specified */
	
	if (empty($sort_field)) $sort_field=$my_prefs["sort_field"];
	if (empty($sort_order)) $sort_order=$my_prefs["sort_order"];

	
	/* figure out which/how many messages to fetch */
	if ((empty($start)) || (!isset($start)) || ($infobox)) $start = 0;
	$num_show=$my_prefs["view_max"];
	if ($num_show==0) $num_show=50;
	$next_start = $start + $num_show;
	$prev_start = $start - $num_show;
	if ($prev_start<0) $prev_start=0;
	//echo "<p>Start: $start";
	
	/* flush, so the browser can't start renderin and user sees some feedback */
	flush();
	
	$clock->register("pre-count");

	/* retreive message list (search, or list all in folder) */
	if ((!empty($search)) || (!empty($search_criteria))){
		include("./modules/wmail/lang/".$my_prefs["lang"]."search_errors.inc");
		$criteria="";
		$error="";
		$date = $month."/".$day."/".$year;
		if (empty($search_criteria)){
			// check criteria
			if ($date_operand=="ignore"){
				if ($field=="-"){
					$error=$searchErrors["field"];
				}
				if (empty($string)){
					$error=$searchErrors["empty"];
				}
			}else if ((empty($date))||($date=="mm/dd/yyyy")){
				$error=$searchErrors["date"];
			}
			if (!empty($date)){
				$date_a=explode("/", $date);
				$date=iil_FormatSearchDate($date_a[0], $date_a[1], $date_a[2]);
			}
		}
		if ($error==""){
			// format search string
			if (empty($search_criteria)){
				$criteria="ALL";
				if ($field!="-") $criteria.=" $field \"$string\"";
				if ($date_operand!="ignore") $criteria.=" $date_operand $date";
				$search_criteria = $criteria;
			}else{
				$search_criteria = stripslashes($search_criteria);
				$criteria = $search_criteria;
			}
			
			echo "Searching \"$criteria\" in $folder<br>\n"; flush();
			
			// search
			$messages_a=iil_C_Search($conn, $folder, $criteria);
			$total_num=count($messages_a);
			if (is_array($messages_a)) $messages_str=implode(",", $messages_a);
			else $messages_str="";
			echo "found: {".$messages_str."} <br>\n"; flush();
		}else{
			$headers=false;
		}
	}else{
		$total_num=iil_C_CountMessages($conn, $folder);
		if ($total_num > 0) $messages_str="1:".$total_num;
		else $messages_str="";
		$index_failed = false;		
	}
	
	$clock->register("post count");
	
	echo "<!-- Total num: $total_num //-->\n"; flush();
		
		
	/* if there are more messages than will be displayed,
	 		create an index array, sort, 
	 		then figure out which messages to fetch 
	*/
	if (($total_num - $num_show) > 0){
		//attempt ot read from cache
		$read_cache = false;
		if (file_exists(realpath($CACHE_DIR))){
			$cache_path = $CACHE_DIR.ereg_replace("[\\/]", "", $loginID.".".$host);
			$index_a = main_ReadCache($cache_path, $folder, $messages_str, $sort_field, $read_cache);
		}
		//if there are "recent" messages, ignore cache
	    if ($ICL_CAPABILITY["radar"]){
			$recent=iil_C_CheckForRecent($conn, $folder);
			if ($recent > 0) $read_cache = false;
		}
		
		//if not read from cache, go to server
		if (!$read_cache){
			$index_a=iil_C_FetchHeaderIndex($conn, $folder, $messages_str, $sort_field);
			$clock->register("post index: no cache");
		}else{
			$clock->register("post index: cache");
		}
		
		if ($index_a===false){
			//echo "iil_C_FetchHeaderIndex failed<br>\n";
            if (strcasecmp($sort_field,"date")==0){
                if (strcasecmp($sort_order, "ASC")==0){
                    $messages_str = $start.":".($start + $num_show);
                }else{
                    $messages_str = ($total_num - $start - $num_show).":".($total_num - $start);
                }
                //echo $messages_str; flush();
                $index_failed = false;
            }else{
                $index_failed = true;
            }
		}else{
			if ((!$read_cache) && (file_exists(realpath($CACHE_DIR))))
				main_WriteCache($cache_path, $folder, $sort_field, $index_a, $messages_str);

			if (strcasecmp($sort_order, "ASC")==0) asort($index_a);
			else if (strcasecmp($sort_order, "DESC")==0) arsort($index_a);
			
			reset($index_a);
			$i=0;
			while (list($key, $val) = each ($index_a)){
				if (($i >= $start) && ($i < $next_start)) $id_a[$i]=$key;
				$i++;
			}
			if (is_array($id_a)) $messages_str=implode(",", $id_a);

		}
		
		
		echo "<!-- Indexed: $index_a //-->"; flush();
	}
	
	$clock->register("post index");

	/* fetch headers */
	if ($messages_str!=""){
		//echo "Messages: $messages_str <br>\n";
		$headers=iil_C_FetchHeaders($conn, $folder, $messages_str);
		$headers=iil_SortHeaders($headers, $sort_field, $sort_order);  //if not from index array
	}else{
		$headers=false;
	}
	
	$clock->register("post headers");
	echo "<!-- Headers fetched: $headers //-->\n"; flush();
	
	/* if indexing failed, we need to get messages within range */
	if ($index_failed){
		$i = 0;
		$new_header_a=array();
		reset($headers);
		while ( list($k, $h) = each($headers) ){
			if (($i >= $start) && ($i < $next_start)){
				$new_header_a[$k] = $headers[$k];
				//echo "<br>Showing $i : ".$h->id;
			}
			$i++;
		}
		$headers = $new_header_a;
	}
		
	/*  start form */
	if (!$infobox) echo "\n<form name=\"messages\" method=\"POST\" action=\"?m=wmail&tab=0&a=bridge&session=$user\">\n";			

	/* Show folder name, num messages, page selection pop-up */
	
	if ($headers==false) $headers=array();
	

	$c_date["day"]=GetCurrentDay();
	$c_date["month"]=GetCurrentMonth();
	$c_date["year"]=GetCurrentYear();

	if (count($headers)>0) {
		if (!isset($start)) $start=0;
		$i=0;

		if (sizeof($headers)>0){			
			/*  show "To" field or "From" field? */
			if ($folder==$my_prefs["sent_box_name"]){
				$showto=true;
				$fromheading=$mainStrings[7];
			}else{
				$fromheading=$mainStrings[8];
			}			



			$clock->register("pre list");

			/***
			Show tool bar
			***/
			if (strpos($my_prefs["main_toolbar"], "t")!==false){
				include("./modules/wmail/include/main_tools.inc");
			}

			/* main list */
			$num_cols = strlen($my_prefs["main_cols"]);
			echo "\n<!-- MAIN LIST //-->\n";
    //echo "\n".'        <img src="images/titles/my-emails.jpg">';
if (!$infobox){	    
    echo "\n".'          <table width="100%" border="0" cellpadding="5" cellspacing="0" background="images/tabla-medio-back.jpg">';
    echo "\n".'          <tr> ';
    echo "\n".'            <td>';
    echo "\n".'  			<table width="100%" border="0" cellspacing="0" cellpadding="4">';
    echo "\n".'              <tr> ';
    echo "\n".'                  	<td bgcolor="#FFFFFF">';
}    
    echo "\n".'  						<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">';
    echo "\n".'                      	<tr> ';
    echo "\n".'                        		<th class="title-table">'.$AppUI->_("Subject").'</td>';
    echo "\n".'                        		<th class="title-table">'.$AppUI->_("From").'</td>';
    echo "\n".'                     	   		<th class="title-table">'.$AppUI->_("Sent").'</td>';
    echo "\n".'                      	</tr>';

			$display_i=0;
			$prev_id = "";
			while (list ($key,$val) = each ($headers)) {
				//$next_id = $headers[key($headers)]->id;
				$header = $headers[$key];
				$id = $header->id;
				$seen = ($header->seen?"Y":"N");
				$deleted = ($header->deleted?"D":"");
				if ((($showdeleted==0)&&($deleted!="D")) || ($showdeleted)){
					if (($hideseen==0)||($seen=="N")){
						$display_i++;
					
						echo "\n<tr >\n";

						//show subject
						$subject=trim(chop($header->subject));
						if (empty($subject)) $subject=$mainStrings[15];
						$args = "session=$user&folder=".urlencode($folder)."&id=$id&uid=".$header->uid."&start=$start";
						$args.= "&num_msgs=$total_num&sort_field=$sort_field&sort_order=$sort_order";
						$row["s"] = "<td><a href=\"?xa=read_message&m=wmail&tab=0&a=bridge&".$args."\" ";
						$row["s"].= ($my_prefs["view_inside"]!=1?"target=\"scr".$user.urlencode($folder).$id."\"":"").">".($seen=="N"?"":"");
						$row["s"].= encodeUTFSafeHTML(LangDecodeSubject($subject, $my_prefs["charset"])).($seen=="N"?"":"")."</a></td>\n";
						//echo $row["s"];
						
						//show sender||recipient
						if ($showto) $row["f"] = "<td>".LangDecodeAddressList($header->to, $my_prefs["charset"], $user)."</td>\n";						
						else $row["f"] = "<td>".LangDecodeAddressList($header->from, $my_prefs["charset"], $user)."</td>\n";
						//echo $row["f"];

						//show date/time
						$timestamp = $header->timestamp;
						$timestamp = $timestamp + ((int)$my_prefs["timezone"] * 3600);
						$row["d"] = "<td><nobr>".ShowShortDate($timestamp, $lang_datetime)."&nbsp;</nobr></td>\n";
						//echo $row["d"];

						for ($i=0;$i<$num_cols;$i++) echo $row[$my_prefs["main_cols"][$i]];
						
						echo "</tr>\n";
						flush();
					}
				}
				$i++;
			}

		
    echo "\n".'                    </table>';
if (!$infobox){	
    echo "\n".'          		</td></tr>';
    echo "\n".'              </table>';
    echo "\n".'          </td></tr>';
    echo "\n".'        </table>';
}

			flush();
			
			$clock->register("post list: $i");
			

			if (!$infobox){
				echo "<input type=\"hidden\" name=\"user\" value=\"$user\">\n";
				echo "<input type=\"hidden\" name=\"folder\" value=\"$folder\">\n";
				echo "<input type=hidden name=\"sort_field\" value=\"".$sort_field."\">\n";
				echo "<input type=hidden name=\"sort_order\" value=\"".$sort_order."\">\n";
				if (isset($search)) echo "<input type=hidden name=search_done value=1>\n";
				echo "<input type=\"hidden\" name=\"max_messages\" value=\"".$display_i."\">\n";
			}
			
		}else{
//			if (!empty($search)) echo "<p><center>".$mainErrors[0]."</center>";
//			else echo "<p><center><span class=mainLight>".$mainErrors[1]."</span></center>";
		}
	}else{
//		if (!empty($search)) echo "<p><center><span class=mainLight>".$mainErrors[0]."</span></center>";
//		else echo "<p><center><span class=mainLight>".$mainErrors[1]."</span></center>";
	}
	
	iil_Close($conn);

$clock->register("done");
$exec_finish_time = microtime();
echo '<!-- execution time: '.$exec_start_time.' ~ '.$exec_finish_time.' -->';
echo "\n<!--\n";
$clock->dump();
echo "\n//-->\n";

if (!$infobox){
	//echo " no es infobox";
?>


<? 
}
?>