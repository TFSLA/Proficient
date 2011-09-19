<?php
/////////////////////////////////////////////////////////
//	
//	source/contacts.php
//
//	(C)Copyright 2001-2002 Ryo Chijiiwa <Ryo@IlohaMail.org>
//
//		This file is part of IlohaMail.
//		IlohaMail is free software released under the GPL 
//		license.  See enclosed file COPYING for details,
//		or see http://www.fsf.org/copyleft/gpl.html
//
/////////////////////////////////////////////////////////

/********************************************************

	AUTHOR: Ryo Chijiiwa <ryo@ilohamail.org>
	FILE:  source/contacts.php
	PURPOSE:
		List basic information of all contacts. 
		Offer links to
			-view/edit contact
			-send email to contact
			-add new contact
		Process posted data to edit/add/remove contacts information
	PRE-CONDITIONS:
		Required:
			$user-Session ID for session validation and user prefernce retreaval
		Optional:
			POST'd data for add/remove/edit entries.  See source/edit_contact.php
	POST-CONDITIONS:
	COMMENTS:

********************************************************/

function FormatHeaderLink($user, $label, $color, $new_sort_field, $sort_field, $sort_order){
	if (strcasecmp($new_sort_field, $sort_field)==0){
		if (strcasecmp($sort_order, "ASC")==0) $sort_order="DESC";
		else $sort_order = "ASC";
	}
	$link = "<a href=\"?m=wmail&tab=0&xa=contacts_popup&session=$user&suppressLogo=true&dialog=1&sort_field=$new_sort_field&sort_order=$sort_order\" class=\"mainHeading\">";
	$link .= "<b>".$label."</b></a>";
	return $link;
}

function ShowRow($a, $id){
	global $my_colors, $grp_sort, $AppUI;

	//echo "<tr bgcolor=\"".$my_colors["main_bg"]."\">\n";
	echo "<tr>\n";
#	$toString=(!empty($a["contact_first_name"])?"\"".$a["contact_first_name"]."\" ":"")."<".$a["email"].">";
#	$toString=htmlspecialchars($toString);
	if (empty($a["name"])) $a["name"]="--";
	$title = $AppUI->_("Name").": ".$a["name"]."<br>".
			 $AppUI->_("Email").": ".$a["email"]."<br>".
			 $AppUI->_("Company").": ".$a["grp"];
	$title = $a["name"]." (".$a["email"].")";
	echo "<td nowrap=\"nowrap\"><a href=\"javascript:addcontact2('$id');\" title=\"$title\">".
			($a["type"]=="user"? "<font color='#000099'>":"").
			$a["name"].
			"&nbsp;&lt;".$a["email"]."&gt;".
			($a["type"]=="user"? "</font>":"").
			"</a></td>";
	//echo "<td>".$a["email"]."</td>";
	//if (!$grp_sort) echo "<td>".$a["grp"]."</td>";
	echo "</tr>\n";
}


require_once( $AppUI->getModuleClass( 'companies' ) );
include("./modules/wmail/include/super2global.inc");
include("./modules/wmail/include/contacts_commons.inc");
include_once("./modules/wmail/include/data_manager.inc");

$user=$session;
if (isset($user)){
	include("./modules/wmail/include/header_main.inc");
?>
		<script type="text/javascript" language="JavaScript1.2">
		var contacts;
		var to = "to";
		var cc = "cc";
		var bcc = "bcc";
		function gettarget() {
			switch (document.contactsopts.selected.value) {
			case cc:
				var target = document.contactsopts.cc;
				break;
			case bcc:
				var target = document.contactsopts.bcc;
				break;
			default:
				var target = document.contactsopts.to;
			}
			return target;
		}

		function addcontact(address) {
			var target = gettarget();
			if (target.value.indexOf(address, 0)==-1) { //A check to prevent adresses from getting listed twice.
				if (target.value != '') target.value += ', ';
				target.value += address;
			}
		}
		
		function addcontact2(id) {
			for (var i=0; i<contacts.length; i++) {
				if (id==contacts[i][0])
					addcontact("\""+contacts[i][1]+"\" <"+contacts[i][2]+">");
			}
		}
		
		function addgroup(group) {
			for (var i=0; i<contacts.length; i++) {
				if (group==contacts[i][3])
					addcontact("\""+contacts[i][1]+"\" <"+contacts[i][2]+">");
			}
		}

		function addall() {
			for (var i=0; i<contacts.length; i++) {
				addcontact("\""+contacts[i][1]+"\" <"+contacts[i][2]+">");
			}
		}		
		
		function acknowledge_popup() {
			opener.contacts_popup_visible=true;
		}
		
		function alert_close() {
			opener.contacts_popup_visible=false;
		}
		
		function sendcontacts(){
			var f = document.contactsopts;
			window.opener.writeTo(f.to.value);
			window.opener.writeCc(f.cc.value);
			window.opener.writeBcc(f.bcc.value);
			window.close();
		}
		
		</script>

<?
	include("./modules/wmail/lang/".$my_prefs["lang"]."/contacts.inc");
	include("./modules/wmail/lang/".$my_prefs["lang"]."/compose.inc");

	//authenticate
	include_once("./modules/wmail/include/icl.inc");
	$conn=iil_Connect($host, $loginID, $password, $AUTH_MODE);
	if ($conn){
		iil_Close($conn);
	}else{
		echo "Authentication failed.";
		echo "</html>\n";
		exit;
	}
	
	//initialize source name
	$source_name = $DB_CONTACTS_TABLE;
	if (empty($source_name)) $source_name = "contacts";
	
	//open data manager connection
	$dm = new DataManager_obj;
	if ($dm->initialize($loginID, $host, $source_name, $backend)){
	}else{
		echo "Data Manager initialization failed:<br>\n";
		$dm->showError();
	}
	
	//initialize sort fields and order
	//if (empty($sort_field)) $sort_field = "contact_company,contact_first_name, contact_last_name";
	if (empty($sort_field)) $sort_field = "contact_first_name, contact_last_name";
	if (empty($sort_order)) $sort_order = "ASC";
	if (empty($search_string)) $search_string = "";
	if (ereg("^contact_company", $sort_field))  $grp_sort = true;
	else $grp_sort = false;
	
	//fetch and sort

	$list_contacts = $dm->sort($sort_field, $sort_order);
echo "<!-- \n";
var_dump($list_contacts);	
echo "\n -->";
	$contacts = array();
	for($i = 0; $i < count($list_contacts); $i++){
		$addcontact = false;
		$contact = $list_contacts[$i];
		// si tiene una direccion de mail cargada
		if (trim($contact["contact_email"]) == "" && trim($contact["contact_email2"]) == "")
			continue;
	
		if ($search_string != ""){
			$infirstname = stristr ($contact["contact_first_name"], $search_string);
			$inlastname = stristr ($contact["contact_last_name"], $search_string);
			if ( ! ($infirstname || $inlastname) )
				continue;
	
		}
		if (! (empty($show_grp) || $show_grp==strtoupper(trim($contact["contact_company"]))) ){
			continue;
		}
		$contacts[] = $list_contacts[$i];
	}
	
	if (!( is_array($contacts) && count($contacts) > 0)){
}


	$numContacts = count($contacts);
	$groups = explode(",", base64_decode(GetGroups($list_contacts)));
	
	$checked_groups = array();
	for($i = 0; $i < count( $groups ); $i++ ){
		$groups[$i] = strtoupper(trim($groups[$i]));
		if ( !in_array($groups[$i], $checked_groups)){
			$checked_groups[] = $groups[$i];
		}	
	}
	$groups = $checked_groups;
	$cpyObj = new CCompany();
	$companies = $cpyObj->getCompanies($AppUI->user_id);
	for ($i = 0; $i < count($companies); $i++)
		if (!in_array(strtoupper(trim($companies[$i]["company_name"])), $groups))
			$groups[] = strtoupper(trim($companies[$i]["company_name"]));
			
	natcasesort($groups);
	
	
	
	//show error, if any
	if (!empty($error)) echo "<p>".$error."<br>\n";
	
	
	//show title heading
	echo "\n<table width=\"100%\" cellpadding=2 cellspacing=0><tr bgcolor=\"".$my_colors["main_head_bg"]."\">\n";
	echo "<td align=left valign=bottom>\n";
	echo "<span class=\"bigTitle\">".$cStrings[0]."</span>\n";
	echo "&nbsp;&nbsp;&nbsp;";
	echo '<span class="mainHeadingSmall">';
	echo '[<a href="javascript:close();" onClick="window.close();" class="mainHeadingSmall">'.$cStrings["close"].'</a>]';
	echo '</span>';
	echo "</td></tr></table>\n";


	//show instructions (al final de la tabla
	//echo "<span class=mainLight>".$cStrings["instructions"]."</span>\n";
	
	//show controls
	
	echo "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"\">";
	echo "<form method=\"POST\" name=\"contactsopts\" action=\"?m=wmail&tab=0&xa=contacts_popup&session=$user&suppressLogo=true&dialog=1\">\n";
	echo "<input type=\"hidden\" name=\"user\" value=\"$user\">\n";
	//echo "<input type=\"hidden\" name=\"cc\" value=\"$cc\">\n";
	//echo "<input type=\"hidden\" name=\"bcc\" value=\"$bcc\">\n";
	echo "<input type=\"hidden\" name=\"sort_order\" value=\"$sort_order\">\n";
	echo "<input type=\"hidden\" name=\"sort_field\" value=\"$sort_field\">\n";
	echo "<table width=\"98%\" class=\"\">";

	/*echo "<tr><td valign=\"middle\" colspan=\"2\"><span class=mainLight>\n";
	echo $cStrings["search"]."&nbsp;";
	echo "</span></td>\n";
	echo "</tr>";*/
	echo "<tr ><td valign=\"middle\"><span class=mainLight>\n";
	echo $cStrings["search"]."&nbsp;";
	echo "<input type=text class=\"text\" name=\"search_string\" value=\"".stripslashes($search_string)."\" size=25>";
	echo "&nbsp;";
	echo '<INPUT TYPE="submit" class="button" NAME="search" VALUE="'.$cStrings["go"].'">&nbsp;';
	echo '<INPUT TYPE="button" class="button" NAME="clear" VALUE="'.$AppUI->_("clear").'" onclick="this.form.search_string.value=\'\'; this.form.submit();">';
	echo "</span></td>\n";
	
	echo "<td valign=\"top\" align=\"right\"><span class=mainLight>\n";
		//$select_str = "<select name=\"show_grp\" onChange=\"contactsopts.submit()\" class=\"text\">\n";
		$select_str = "<select name=\"show_grp\" class=\"text\" onChange=\"contactsopts.submit()\">\n";
		$select_str.= "<option value=\"\" ".(empty($show_grp)?"SELECTED":"").">".$cStrings["all"]."\n";
		while ( list($k,$val)=each($groups) ) $select_str.= "<option value=\"$val\" ".($show_grp==$val?"SELECTED":"").">$val\n";
		$select_str.= "</select>\n";
		//echo str_replace("%s", $select_str, $cStrings["showgrp"]);
		echo str_replace("%s", $select_str, $cStrings[12]." %s");
	
	echo "&nbsp;&nbsp;";
	//echo '<INPUT TYPE="button" class="button" NAME="clear" VALUE="'.$cStrings["clear"].'" onclick="javascript: this.form.search_string.value = \'\';">';	
	//echo "&nbsp;";
	//echo '<INPUT TYPE="submit" class="button" NAME="search" VALUE="'.$cStrings["go"].'">';	
	echo "</span></td>\n";
	echo "</tr>";
	
	$to_fields = array(	"to" => $composeHStrings[2],
							"cc" => $composeHStrings[3],
							"bcc" => $composeHStrings[4]);	
	/*
	echo "<tr>";
	echo "<td valign=\"top\" colspan=\"2\" align=\"right\"><span class=mainLight>\n";
							
		$select_str = "<select name=\"to_a_field\" class=\"text\">\n";
		$select_str.= "<option value=\"to\">".$composeHStrings[2].":\n";
		if ($cc) $select_str.= "<option value=\"cc\">".$composeHStrings[3].":\n";
		if ($bcc) $select_str.= "<option value=\"bcc\">".$composeHStrings[4].":\n";
		$select_str.= "</select>\n";
		$select_str = arraySelect($to_fields, "to_a_field", "class='text'", $to_a_field);
		echo str_replace("%s", $select_str, $cStrings["addto"]);
	echo "</span></td>\n";
	echo "</tr>";	
	*/
	//echo "</form>\n";
	echo "</table>\n";
	flush();

	//show contacts
	if ( is_array($contacts) && count($contacts) > 0){
		reset($contacts);
		$num_c=0;
		echo "<script type=\"text/javascript\" language=\"JavaScript1.2\">\n";
		echo "contacts = new Array(";
		$showed_contacts = array();	
		while( list($k1, $foobar) = each($contacts) ){
			$a=$contacts[$k1];
			$a["name"] = trim($a["contact_first_name"])." ".trim($a["contact_last_name"]);
			$a["email"] = $a["contact_email"];
			$a["email2"] = $a["contact_email2"];
			$a["grp"] = $a["contact_company"];	
				
			if ($a["email"] && !in_array($a["email"] , $showed_contacts)){
				if ($num_c>0) echo ",\n";
				$name=(!empty($a["name"])?"\"".$a["name"]."\" ":"\"".$a["email"]."\"");
				echo "new Array($num_c,$name,\"".$a["email"]."\",\"".$a["grp"]."\")";
				//$showed_contacts[]=$a["email"];
				$num_c++;
			}
			if ($a["email2"] && !in_array($a["email2"] , $showed_contacts)){
				$name=(!empty($a["name"])?"\"".$a["name"]."\" ":"\"".$a["email2"]."\"");
				echo ",\nnew Array($num_c,$name,\"".$a["email2"]."\",\"".$a["grp"]."\")";
				//$showed_contacts[]=$a["email2"];
				$num_c++;
			}
		}
		echo ");\n</script>";
	}

		
		//echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\" bgcolor=\"".$my_colors["main_hilite"]."\">\n";
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\">\n";
		echo "<tr bgcolor=\"".$my_colors["tool_bg"]."\">";
		echo "<td class=\"mainHeading\"><b>".$cStrings[11]."</b></td>";
		//echo "<td>".FormatHeaderLink($user, $cStrings[3], $textc, "contact_first_name, contact_last_name", $sort_field, $sort_order)."</td>";
		//echo "<td>".FormatHeaderLink($user, $cStrings[4], $textc, "contact_email", $sort_field, $sort_order)."</td>";
		//if (!$grp_sort) echo "<td>".FormatHeaderLink($user, $cStrings[6], $textc, "contact_company,contact_first_name, contact_last_name", $sort_field, $sort_order)."</td>";
		echo "</tr>";
		echo "<tr>";
		/* esta es la celda donde estan los contactos */
		echo "<td valign=\"top\" style=\"border: 1px black solid\">";
	if ( is_array($contacts) && count($contacts) > 0){	
			reset($contacts);
			$num_c=0;
		echo '<div style="overflow: auto; width: 99%; height: 430px; padding:0px; margin: 0px; border: 0px">';
		echo '<div style="overflow: hidden; width: 96%;  padding:0px; margin: 0px; border: 0px">';
		echo "<table width=\"96%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\">\n";
		$prev_grp = "";
		$num_c = 0;
		$showed_contacts = array();
		while( list($k1, $foobar) = each($contacts) ){
			$a=$contacts[$k1];
			$a["name"] = trim($a["contact_first_name"])." ".trim($a["contact_last_name"]);
			$a["type"] = $a["contact_type"] == "user" ? "user" : "contact";
			$a["email"] = $a["contact_email"];
			$a["email2"] = $a["contact_email2"];
			$a["grp"] = $a["contact_company"];				
			if (empty($show_grp) || $show_grp==strtoupper(trim($a["grp"]))){
				/*
				if ($grp_sort && $a["grp"]!=$prev_grp){
					//$grp = str_replace(" ", "_", $a["grp"]);
					$toString = htmlspecialchars($a["grp"]);
					echo "<tr bgcolor=\"".$my_colors["main_bg"]."\"><td colspan=2 align=center><br><b>";
					echo "<a href=\"javascript:addgroup('$toString');\">".$a["grp"]."</a>";
					echo "</b></td></tr>";
					$prev_grp = $a["grp"];
				}*/
				
				if ($a["email"] && !in_array($a["email"] , $showed_contacts)){
					ShowRow($a, $num_c); $num_c++;
					//$showed_contacts[]=$a["email"];
				}
				if ($a["email2"] && !in_array($a["email"] , $showed_contacts)){
					$a["email"] = $a["email2"];
					ShowRow($a, $num_c); $num_c++;
					//$showed_contacts[]=$a["email"];
				}
			}
		}
		echo "</table>\n";
		echo "</div>";
		echo "</div>";
	}else{
		echo $cErrors[0];	
	}
		echo "</td>";	
		
		/* esta es la celda donde estan los campos To CC y Bcc */
		echo "<td valign=\"top\">";	
		echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\">\n";
		//echo "<form method=\"POST\" name=\"contactsfields\" action=\"\">\n";
		echo "<input type=\"hidden\" name=\"selected\" value=\"$to_a_field\">\n";
			echo "<tr><td>";
			echo "<b>".$to_fields["to"].":</b><br>";
		echo "<script language=\"javascript\">
			document.write('<textarea name=\"to\" cols=\"40\" rows=\"5\" class=\"text\" onfocus=\"this.form.selected.value=this.name;\">');
			document.write(".(isset($to) && $to!="" ? "\"$to\"" :"window.opener.readTo()").");

		</script>";			
			echo "</textarea><br><br>";
			echo "</td></tr>";			
			echo "<tr><td>";
			echo "<b>".$to_fields["cc"].":</b><br>";
		echo "<script language=\"javascript\">
			document.write('<textarea name=\"cc\" cols=\"40\" rows=\"5\" class=\"text\" onfocus=\"this.form.selected.value=this.name;\">');
			document.write(".(isset($cc) && $cc!="" ? "\"$cc\"" :"window.opener.readCc()").");

		</script>";		
			echo "</textarea><br><br>";
			echo "</td></tr>";	
			echo "<tr><td>";
			echo "<b>".$to_fields["bcc"].":</b><br>";
		echo "<script language=\"javascript\">
			document.write('<textarea name=\"bcc\" cols=\"40\" rows=\"5\" class=\"text\" onfocus=\"this.form.selected.value=this.name;\">');
			document.write(".(isset($bcc) && $bcc!="" ? "\"$bcc\"" :"window.opener.readBcc()").");
		</script>";		
			echo "</textarea><br><br>";	
			echo "</td></tr>";
		echo "<script language=\"javascript\">
		document.contactsopts.$to_a_field.focus();	
		</script>";	
									
		echo "</table>\n";	
		echo "</td>";
		echo "</tr>";
		echo "<tr>
				<td><a href=\"javascript: addall();\">".$AppUI->_('Add all')."</a></td>
				<td>&nbsp;</td></tr>";
		echo "<tr><td colspan=\"100\" align=\"center\"><hr noshade></td></tr>";
		echo "<tr>";	
		//show instructions
		echo "<td><span class=mainLight>".$cStrings["instructions"]."</span></td>";	
		
		echo "<td valign=\"top\" align=\"right\">";
		echo '<INPUT TYPE="button" class="button" NAME="ok" VALUE="'.$AppUI->_("submit").'" onclick="sendcontacts();">&nbsp;&nbsp;';
		echo '<INPUT TYPE="button" class="button" NAME="cancel" VALUE="'.$AppUI->_("back").'" onclick="window.close();">';
		echo "</td>";
		echo "</tr>";				
		echo "</table>\n";		
		
		echo "</td>";
		echo "</tr>";
		echo "</form>";	
		echo "</table>\n";		
		
}
?>