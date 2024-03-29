<?php
$session = $GLOBALS["session"];
$folder  = $GLOBALS["folder"];
$user    = $session;
include_once("./modules/wmail/include/optionfolders.inc.php");
/////////////////////////////////////////////////////////
//	
//	source/bookmarks.php
//
//	(C)Copyright 2003 Ryo Chijiiwa <Ryo@IlohaMail.org>
//
//		This file is part of IlohaMail.
//		IlohaMail is free software released under the GPL 
//		license.  See enclosed file COPYING for details,
//		or see http://www.fsf.org/copyleft/gpl.html
//
/////////////////////////////////////////////////////////

/********************************************************

	AUTHOR: Ryo Chijiiwa <ryo@ilohamail.org>
	FILE: source/bookmarks.php
	PURPOSE:
		Create/edit/delete bookmarks
	PRE-CONDITIONS:
		$user - Session ID
		
********************************************************/

	include_once("./modules/wmail/include/super2global.inc");
	include_once("./modules/wmail/include/header_main.inc");
	include_once("./modules/wmail/include/langs.inc");
	include_once("./modules/wmail/include/icl.inc");	
	include_once("./modules/wmail/lang/".$my_prefs["lang"]."bookmarks.inc");
	include_once("./modules/wmail/include/data_manager.inc");    
	
	//authenticate
	$conn=iil_Connect($host, $loginID, $password, $AUTH_MODE);
	if ($conn){
		if ($ICL_CAPABILITY["folders"]){
			if ($my_prefs["hideUnsubscribed"]){
				$mailboxes = iil_C_ListSubscribed($conn, $my_prefs["rootdir"], "*");
			}else{
				$mailboxes = iil_C_ListMailboxes($conn, $my_prefs["rootdir"], "*");
			}
			sort($mailboxes);
		}
		iil_Close($conn);
	}else{
		echo "Authentication failed.";
		echo "</body></html>\n";
		exit;
	}
	
	//make sure feature is not disabled
	if ($DISABLE_BOOKMARKS){
		echo $bmError[2];
		echo "</body></html>\n";
		exit;
	}

	//open DM connection
	$dm = new DataManager_obj;
	if ($dm->initialize($loginID, $host, $DB_BOOKMARKS_TABLE, $DB_TYPE)){
	}else{
		echo "Data Manager initialization failed:<br>\n";
		$dm->showError();
	}

	//do add
	if (isset($add)){
		if ((empty($new_name)) || (empty($new_url))) $error .= $bmError[1];
		else{
			if (!ereg("[fht]+tp[s]*://", $new_url)) $new_url = "http://".$new_url;
			$new_entry = array();
			$new_entry["name"] = $new_name;
			$new_entry["url"] = $new_url;
			$new_entry["grp"] = (empty($new_grp)?$new_grp_other:$new_grp);
			$new_entry["comments"] = $new_comments;
			$new_entry["is_private"] = $new_private;
						
			if ($dm->insert($new_entry)) echo "<!-- Inserted //-->";
			else echo "<!-- Not inserted //-->";
			
			$new_name = $new_url = $new_grp = $new_comments = $new_private = $new_grp_other = "";
		}
	}
	
	//do edit
	if (isset($edit) && ($edit_id > 0)){
		if (!ereg("[fht]+tp[s]*://", $edit_url)) $edit_url = "http://".$edit_url;
		$new_entry["name"] = $edit_name;
		$new_entry["url"] = $edit_url;
		$new_entry["grp"] = (empty($edit_grp)?$edit_grp_other:$edit_grp);
		$new_entry["comments"] = $edit_comments;
		$new_entry["is_private"] = $edit_private;
			
		if ($dm->update($edit_id, $new_entry)) echo "<!-- Updated! //-->";
		else echo "<!-- Not updated //-->";
		
		$edit_id = 0;
	}
	
	//do delete
	if (isset($delete) && ($edit_id > 0)){
		if ($dm->delete($edit_id)) $edit_id = 0;
		else $error .= "Deletion failed<br>\n";
	}
	
	//get sorted list of bookmarks
	$urls_a = $dm->sort("grp", "ASC");
	
	//get groups and form <option> list
	$groups = $dm->getDistinct("grp", "ASC");
	
	$error .= $dm->error;
	
	//show title and error
	?>
	<!--table border="0" cellspacing="2" cellpadding="0" width="100%">
	<tr bgcolor="<?php echo $my_colors["main_head_bg"]?>"><td><span class="bigTitle"><?php echo $bmStrings["bookmarks"]?></span></td></tr>
	</table-->

	<font color="red"><?php echo $error?></font>
	<p>
	
	<?php
	echo "<center>\n";

	//list bookmark entries
	if (is_array($urls_a) && count($urls_a)>0){
		$prev_cat = "";
		reset($urls_a);
		echo '<table border="0" cellspacing="0" cellpadding="2" width="100%">';
		while ( list($k, $v) = each($urls_a) ){
			$v = $urls_a[$k];
			if ($v["grp"]!=$prev_cat){
				echo '<tr class="tableHeaderGral">';
				echo '<td colspan=9><span class="tblheader">'.$v["grp"].'</span></td>';
				echo '</tr>';
				$prev_cat = $v["grp"];
			}
			echo '<tr>';
				echo "<td width=\"20px\" nowrap=\"nowrap\" valign=\"middle\" align=\"left\"><a href=\"?m=wmail&tab=0&a=bridge&tab=2&session=$user&edit_id=".$v["id"]."\" title=\"".$bmStrings["edit"]."\">";
				echo dPshowImage( './images/icons/edit_small.gif', 20, 20, $bmStrings["edit"] )."</a></td>";
				echo "<td width=\"100px\" nowrap=\"nowrap\" valign=\"middle\">".$v["name"]."</td>";
				//echo "<td valign=\"middle\"><nobr>".$v["name"]."</nobr></td>";
				echo "<td width=\"20%\" nowrap=\"nowrap\" valign=\"middle\"><a href=\"".$v["url"]."\" target=_blank>".$v["url"]."</a></td>";
				echo "<td width=\"50%\" valign=\"middle\">".$v["comments"]."</td>";
			echo "</tr>\n";
            echo "<tr class=\"tableRowLineCell\"><td colspan=\"9\"></td></tr>";
		}
		echo "</table>";
	}


	//show edit/add form
	echo "<p>";

	if ($edit_id>0){
		reset($urls_a);
		while ( list($k,$foo) = each($urls_a) ){
			if ($urls_a[$k]["id"]==$edit_id){
				$v = $urls_a[$k];
				//echo "Found $edit_id <br>\n";
			}
		}
?>
		<form method="post" action="?m=wmail&tab=0&a=bridge&tab=2&session=<?=$user?>">
		<input type="hidden" name="user" value="<?php echo $user ?>">
		<input type="hidden" name="edit_id" value="<?php echo $edit_id ?>">
		<table border="0" cellspacing="0" cellpadding="1" width="100%">
		<tr class="tableHeaderGral">
			<td align="left"><span class="tblheader"><?php echo $bmStrings["edit_url"]?></span></td>
		</tr>
		<tr class="tableForm_bg">
			<td align="center">
				<table>
					<tr>
						<td align="right"><?php echo $bmStrings["name"]?>:</td>
						<td><input type="text" class="text" name="edit_name" value="<?php echo $v["name"]?>" size="25">
						<?php echo $bmStrings["category"]?>:
						<select name="edit_grp" class="text">
						<?php 
							echo "<option value=\"\">".$bmStrings["other"]."\n";
							if (is_array($groups) && count($groups)>0){
								while ( list($k,$grp) = each($groups) ){
									echo "<option value=\"$grp\" ".($grp==$v["grp"]?"SELECTED":"").">$grp\n";
								}
							}
						?>
						</select>
						<input type="text" class="text" name="edit_grp_other" value="" size="15">
						</td>
					</tr>
					<tr>
						<td align="right"><?php echo $bmStrings["url"]?>:</td>
						<td><input type="text" class="text" name="edit_url" value="<?php echo $v["url"] ?>" size="60"></td>
					</tr>
					<tr>
						<td align="right" valign="top"><?php echo $bmStrings["comments"]?>:</td>
						<td>
						<input type="text" class="text" name="edit_comments" value="<?php echo htmlspecialchars(stripslashes($v["comments"]))?>" size="60">
						</td>
					</tr>
				</table>
				<input type="submit" class="button" name="edit" value="<?php echo $bmStrings["edit"]?>">
				<input type="submit" class="button" name="delete" value="<?php echo $bmStrings["delete"]?>">
			</td>
		</tr>
		</table>
		</form>
<?php
	}else{
?>
		<form method="post" action="?m=wmail&tab=0&a=bridge&tab=2&session=<?=$user?>">
		<input type="hidden" name="user" value="<?php echo $user?>">
		<input type="hidden" name="session" value="<?php echo $user?>">
		<table border="0" cellspacing="0" cellpadding="1" width="100%">
			<tr class="tableHeaderGral">
				<td align="left"><span class="tblheader"><?php echo $bmStrings["new"]?></span></td>
			</tr>
			<tr class="tableForm_bg">
				<td align="center">
					<table>
						<tr>
							<td align="right"><?php echo $bmStrings["name"]?>:</td>
							<td><input type="text" class="text" name="new_name" value="<?php echo htmlspecialchars(stripslashes($new_name))?>" size="25">
							<?php echo $bmStrings["category"]?>:
							<select name="new_grp" class="text">
							<?php
								echo "<option value=\"\">".$bmStrings["other"]."\n";
								if (is_array($groups) && count($groups)>0){
									while ( list($k,$v) = each($groups) ){
										echo "<option value=\"$v\">$v\n";
									}
								}
							?>
							</select>
							<input type="text" class="text" name="new_grp_other" value="<?php echo $new_grp_other?>" size="15">
							</td>
						</tr>
						<tr>
							<td align="right"><?php echo $bmStrings["url"]?>:</td>
							<td><input type="text" class="text" name="new_url" value="<?php echo $new_url ?>" size="60"></td>
						</tr>
						<tr>
							<td align="right" valign="top"><?php echo $bmStrings["comments"]?>:</td>
							<td>
							<input type="text" class="text" name="new_comments" value="<?php echo htmlspecialchars(stripslashes($new_comments))?>" size="60">
							</td>
						</tr>
					</table>
					<input type="submit" name="add" class="button" value="<?php echo $bmStrings["add"]?>">
				</td>
			</tr>
		</table>
		</form>		
<?php
	}
?>
</BODY></HTML>
