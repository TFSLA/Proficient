<?php

/* $Id: config.inc.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */

// reply-to address for staff followups
// i.e. the address the gateway receives
if ( ! isset($AppUI->cfg['site_domain']))
  $AppUI->cfg['site_domain'] = "tfsla.com";

$CONFIG["reply_to"] = "support@" . $AppUI->cfg['site_domain'];
// If you want to hide real addresses behind a bogus 
// generic email, uncomment the following:
// $CONFIG["reply_name"] = "Help Desk";

// relative path of the program installation
// i.e. the part of the URL after the server name
// use "" if at the top-level of a server
$CONFIG["relative_path"] = "/ticketsmith";

// page color preferences
$CONFIG["background_color"] = "#ffffff";
$CONFIG["heading_color"] = "#cc0000";
$CONFIG["ticket_color"] = "#ffffee";

// date format
$CONFIG["date_format"] = $AppUI->getPref('SHDATEFORMAT');//"D M j Y g:ia";

// visual warnings for old tickets
$CONFIG["warning_active"]= 1; // 0 = inactive, 1 = active
$CONFIG["warning_color"] = "#ff0000";
$CONFIG["warning_age"] = "0.5"; // in hours

// priority names (low to high)
$CONFIG["priority_names"] = array($AppUI->_("Low"),$AppUI->_("Normal"),$AppUI->_("High"),$AppUI->_("Highest"),$AppUI->_("911"));

// priority colors (low to high)
$CONFIG["priority_colors"] = array("#006600","#000000","#ff0000","#ff0000","#ff0000");

$CONFIG["type_names"] = array("Open"=>$AppUI->_("Open"),"Closed"=>$AppUI->_("Closed"),"Deleted"=>$AppUI->_("Deleted"));
// number of tickets to see at once
$CONFIG["view_rows"] = 40;

// wordwrap badly-formatted messages (PHP >= 4.0.2 only)
$CONFIG["wordwrap"] = 1; // 0 = inactive, 1 = active

// order in which to display followups
$CONFIG["followup_order"] = "ASC"; // "ASC" or "DESC"

// go to parent or latest followup from index?
// note that latest followup is slightly slower
$CONFIG["index_link"] = "parent"; // "parent" or "latest"

?>
