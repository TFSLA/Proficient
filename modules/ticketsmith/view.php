<?php /* $Id: view.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */


$ticket = dPgetParam( $_GET, 'ticket', '' );
$ticket_type = dPgetParam( $_GET, 'ticket_type', '' );

$type_toggle = dPgetParam( $_POST, 'type_toggle', '' );
$priority_toggle = dPgetParam( $_POST, 'priority_toggle', '' );
$assignment_toggle = dPgetParam( $_POST, 'assignment_toggle', '' );

// setup the title block
$titleBlock = new CTitleBlock( 'View Ticket', 'tickets.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=ticketsmith", "tickets list" );
$titleBlock->show();

require("./modules/ticketsmith/config.inc.php");
require("./modules/ticketsmith/common.inc.php");

/* Centralize references */
$app_root = $AppUI->getConfig( 'base_url' );

/* initialize fields */
if ($ticket_type == "Staff Followup" || $ticket_type == "Client Followup") {

    $title = "$ticket_type ".$AppUI->_('to Ticket')." #$ticket_parent";

    $fields = array("headings" => array("From", "To", "Subject", "Date", "Cc", "<br />"),
                    "columns"  => array("author", "recipient", "subject", "timestamp", "cc", "body"),
                    "types"    => array("email", "original_author", "normal", "elapsed_date", "email", "body"));

}
elseif ($ticket_type == "Staff Comment") {

    $title = "$ticket_type to Ticket #$ticket_parent";

    $fields = array("headings" => array("From", "Date", "<br />"),
                    "columns"  => array("author", "timestamp", "body"),
                    "types"    => array("email", "elapsed_date", "body"));

}
else {

    $title = "Ticket #$ticket";

    $fields = array("headings" => array("From", "Subject", "Date", "Cc", "Status",
                                        "Priority", "Owner", "<br />"),

                    "columns"  => array("author", "subject", "timestamp", "cc",
                                        "type", "priority", "assignment", "body"),

                    "types"    => array("email", "normal", "elapsed_date", "email",
                                        "status", "priority_select", "assignment", "body"));
}

/* perform updates */
$orig_assignment = dPgetParam( $_POST, 'orig_assignment', '' );
$author = dPgetParam( $_POST, 'author', '' );
$priority = dPgetParam( $_POST, 'priority', '' );
$subject = dPgetParam( $_POST, 'subject', '' );

if (@$type_toggle || @$priority_toggle || @$assignment_toggle) {
    do_query("UPDATE tickets SET type = '$type_toggle', priority = '$priority_toggle', assignment = '$assignment_toggle' WHERE ticket = '$ticket'");
	if(@$assignment_toggle != @$orig_assignment)
	{
		$mailinfo = query2hash("SELECT user_first_name, user_last_name, user_email from users WHERE user_id = $assignment_toggle and user_type <> 5");

		$message = "<html>";
		$message .= "<head>";
		$message .= "<style>";
		$message .= ".title {";
		$message .= "	FONT-SIZE: 18pt; SIZE: 18pt;";
		$message .= "}";
		$message .= "</style>";
		$message .= "<title>".$AppUI->_('Trouble ticket assigned to you')."</title>";
		$message .= "</head>";
		$message .= "<body>";
		$message .= "";
		$message .= "<TABLE border=0 cellpadding=4 cellspacing=1>";
		$message .= "	<TR>";
		$message .= "	<TD valign=top><img src=$app_root/images/icons/ticketsmith.gif alt= border=0 width=42 height=42></td>";
		$message .= "		<TD nowrap><span class=title>".$AppUI->_('Trouble Ticket Management')."</span></td>";
		$message .= "		<TD valign=top align=right width=100%>&nbsp;</td>";
		$message .= "	</tr>";
		$message .= "</TABLE>";
		$message .= "<TABLE width=600 border=0 cellpadding=4 cellspacing=1 bgcolor=#878676>";
		$message .= "	<TR>";
		$message .= "		<TD colspan=2><font face=arial,san-serif size=2 color=white>".$AppUI->_('Ticket assigned to you')."</font></TD>";
		$message .= "	</tr>";
		$message .= "	<TR>";
		$message .= "		<TD bgcolor=white nowrap><font face=arial,san-serif size=2>".$AppUI->_('Ticket ID').":</font></TD>";
		$message .= "		<TD bgcolor=white nowrap><font face=arial,san-serif size=2>$ticket</font></TD>";
		$message .= "	</tr>";
		$message .= "	<TR>";
		$message .= "		<TD bgcolor=white><font face=arial,san-serif size=2>".$AppUI->_('Author').":</font></TD>";
		$message .= "		<TD bgcolor=white><font face=arial,san-serif size=2>" . str_replace(">", "&gt;", str_replace("<", "&lt;", str_replace('"', '', $author))) . "</font></TD>";
		$message .= "	</tr>";
		$message .= "	<TR>";
		$message .= "		<TD bgcolor=white><font face=arial,san-serif size=2>".$AppUI->_('Subject').":</font></TD>";
		$message .= "		<TD bgcolor=white><font face=arial,san-serif size=2>$subject</font></TD>";
		$message .= "	</tr>";
		$message .= "	<TR>";
		$message .= "		<TD bgcolor=white nowrap><font face=arial,san-serif size=2>".$AppUI->_('View').":</font></TD>";
		$message .= "		<TD bgcolor=white nowrap><a href=\"$app_root/index.php?m=ticketsmith&a=view&ticket=$ticket\"><font face=arial,san-serif size=2>$app_root/index.php?m=ticketsmith&a=view&ticket=$ticket</font></a></TD>";
		$message .= "	</tr>";
		$message .= "</TABLE>";
		$message .= "</body>";
		$message .= "</html>";


		mail($mailinfo["user_email"], $AppUI->_('Trouble ticket')." #$ticket ".$AppUI->_('has been assigned to you'), $message, "From: " . $CONFIG['reply_to'] . "\nContent-type: text/html\nMime-type: 1.0");
	}

}

/* start table */
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_04.gif">
    <tr>
        <td align="left"><img src="images/common/lado.gif" width="1" height="17"></td>
        <td class="boldtext"><? echo $AppUI->_($title)?></td>
        <td align="right"><img src="images/common/lado.gif" width="1" height="17"></td>
    </tr>
    <tr bgcolor="#666666">
        <td colspan="3"></td>
    </tr>
</table>
<table class="" cellspacing="0" cellpadding="2" border="0" width="100%">
<form name="form" action="index.php?m=ticketsmith&a=view&ticket=<?php echo $ticket;?>" method="post">
<input type="hidden" name="ticket" value="$ticket" />

<?php

/* start form */

/* get ticket */
$ticket_info = query2hash("SELECT * FROM tickets WHERE ticket = $ticket");

print("<input type=\"hidden\" name=\"orig_assignment\" value='" . $ticket_info["assignment"] . "' />\n");
print("<input type=\"hidden\" name=\"author\" value='" . $ticket_info["author"] . "' />\n");
print("<input type=\"hidden\" name=\"priority\" value='" . $ticket_info["priority"] . "' />\n");
print("<input type=\"hidden\" name=\"subject\" value='" . $ticket_info["subject"] . "' />\n");

/* output ticket */
for ($loop = 0; $loop < count($fields["headings"]); $loop++) {
    print("<tr>\n");
    print("<td align=\"right\" class=\"tableForm_bg\">" . $AppUI->_($fields["headings"][$loop]) . "&nbsp;</td>");
    print("<td align=\"left\" class=\"hilite\">" . format_field($ticket_info[$fields["columns"][$loop]], $fields["types"][$loop]) . "</td>\n");
    print("</tr>\n");
    print("<tr class=\"tableRowLineCell\"><td colspan=\"2\"></td></tr>\n");
}
$ticket_info["assignment"];

/* output attachment indicator */
$attach_count = query2result("SELECT attachment FROM tickets WHERE ticket = '$ticket'");

if ($attach_count == 1) {
    print("<tr>\n");
    print("<td align=\"left\"><strong>".$AppUI->_('Attachments')."</strong></td>");
    print("<td align=\"left\">".$AppUI->_('This email had attachments which were removed').".</td>\n");
    print("</tr>\n");
} else if ($attach_count == 2) {
  $result = do_query("SELECT file_id, file_name from files, tickets where ticket = '$ticket'
  and file_task = ticket and file_project = 0");
  if (number_rows($result)) {
       print("<tr>\n");
      print("<td align=\"left\"><b>Attachments</b></td>");
      print("<td align=\"left\">");
		  while ($row = result2hash($result)) {
			  echo "<a href='fileviewer.php?file_id=" . $row["file_id"] . "'>";
			  echo $row["file_name"];
			echo "</a><br>\n";
		}
		print("</td>\n");
		print("</tr>\n");
	}
}

/* output followup navigation */
if ($ticket_type != "Staff Followup" && $ticket_type != "Client Followup" && $ticket_type != "Staff Comment") {

    /* output followups */
    print("<tr class=\"tableForm_bg\">\n");
    print("<td align=\"left\" valign=\"top\"><strong>".$AppUI->_('Followups')."</strong></td>\n");
    print("<td align=\"left\" valign=\"top\">\n");

    /* grab followups */
    $query = "SELECT ticket, type, timestamp, author FROM tickets WHERE parent = '$ticket' ORDER BY ticket " . $CONFIG["followup_order"];
    $result = do_query($query);

    if (number_rows($result)) {

        /* print followups */
        print("<table width=\"100%\" border=\"1\" cellspacing=\"5\" cellpadding=\"5\">\n");
        while ($row = result2hash($result)) {

            /* determine row color */
            $color = (@$number++ % 2 == 0) ? "#d3dce3" : "#dddddd";

            /* start row */
            print("<tr>\n");

            /* do number/author */
            print("<td bgcolor=\"$color\">\n");
            print("<strong>$number</strong> : \n");
            $row["author"] = ereg_replace("\"", "", $row["author"]);
            $row["author"] = htmlspecialchars($row["author"]);
            print($row["author"] . "\n");
            print("</td>\n");

            /* do type */
            print("<td bgcolor=\"$color\"><a href=\"index.php?m=ticketsmith&a=view&ticket=" . $row["ticket"] . "\">" . $AppUI->_($row["type"]) . "</a></td>\n");

            /* do timestamp */
            print("<td bgcolor=\"$color\">\n");
            print(get_time_ago($row["timestamp"]));
            print("</td>\n");

            /* end row */
            print("</tr>\n");

        }
        print("</table>\n");

    }
    else {
        print("<em>none</em>\n");
    }

    print("</td>\n</tr>\n");

}

else {

    /* get peer followups */
    $results = do_query("SELECT ticket, type FROM tickets WHERE parent = '$ticket_parent' ORDER BY ticket " . $CONFIG["followup_order"]);

    /* parse followups */
    while ($row = result2hash($results)) {
        $peer_tickets[] = $row["ticket"];
    }

    /* count peers */
    $peer_count = count($peer_tickets);

    if ($peer_count > 1) {

        /* start row */
        print("<tr class=\"tableForm_bg\">\n");
        print("<td><strong>".$AppUI->_('Followups')."</strong></td>\n");

        /* start cell */
        print("<td valign=\"middle\">");

        /* form peer links */
        for ($loop = 0; $loop < $peer_count; $loop++) {
            if ($peer_tickets[$loop] == $ticket) {
                $viewed_peer = $loop;
                $peer_strings[$loop] = "<strong>" . ($loop + 1) . "</strong>";
            }
            else {
                $peer_strings[$loop] = "<a href=\"index.php?m=ticketsmith&a=view&ticket=$peer_tickets[$loop]\">" . ($loop + 1) . "</a>";
            }
        }

        /* previous navigator */
        if ($viewed_peer > 0) {
            print("<a href=\"index.php?m=ticketsmith&a=view&ticket=" . $peer_tickets[$viewed_peer - 1] . "\">");
            print($CONFIG["followup_order"] == "ASC" ?  "older" : "newer");
            print("</a> | ");
        }

        /* ticket list */
        print(join(" | ", $peer_strings));

        /* next navigator */
        if ($peer_count - $viewed_peer > 1) {
            print(" | <a href=\"index.php?m=ticketsmith&a=view&ticket=" . $peer_tickets[$viewed_peer + 1] . "\">");
            print($CONFIG["followup_order"] == "ASC" ?  "newer" : "older");
            print("</a>");
        }

        /* end cell */
        print("</td>\n");

        /* end row */
        print("</tr>\n");

    }

}

/* output action links */
print("<tr >\n");
print("<td><br /></td>\n");
print("<td>\n");
print("<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n");
if ($ticket_type == "Staff Followup" || $ticket_type == "Client Followup" || $ticket_type == "Staff Comment") {
    print("<tr><td align=\"left\"><a href=index.php?m=ticketsmith&a=followup&ticket=$ticket>".$AppUI->_('Post followup (emails client)')."</a> | ");
    print("<a href=index.php?m=ticketsmith&a=comment&ticket=$ticket>".$AppUI->_('Post internal comment')."</a> | ");
    print("<a href=index.php?m=ticketsmith&a=view&ticket=$ticket_parent>".$AppUI->_('Return to parent')."</a> | ");
}
else {
    print("<tr><td align=\"left\"><a href=index.php?m=ticketsmith&a=followup&ticket=$ticket>".$AppUI->_('Post followup (emails client)')."</a> | ");
    print("<a href=index.php?m=ticketsmith&a=comment&ticket=$ticket>".$AppUI->_('Post internal comment')."</a> | ");
}
print("</td>");
print("<td align=\"right\"><a href=\"index.php?m=ticketsmith&a=view&ticket=$ticket\">".$AppUI->_('Back to top')."</a></td></tr>\n");
print("</table>\n");
print("</td>");
print("</tr>\n");

/* end table */
print("</table>\n");

/* end form */
print("</form>\n");
?>