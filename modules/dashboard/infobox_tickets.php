<?php  /* TICKETSMITH $Id: infobox_tickets.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
GLOBAL $AppUI;
//$type = dPgetParam( $_GET, 'type', 'All' );
//$user = dPgetParam( $_REQUEST, 'user', null );

require("modules/ticketsmith/config.inc.php");
require("modules/ticketsmith/common.inc.php");

/* setup table & database field stuff */
if ($dPconfig['link_tickets_kludge']) {
	$fields = array("headings" => array("View", "Author", "Subject", "Date", 
                                    "Followup", "Status", "Priority", "Owner", "Link"),

                "columns"  => array("ticket", "author", "subject", "timestamp", 
                                    "activity", "type", "priority", "assignment", "ticket"),

                "types"    => array("view", "email", "normal", "open_date", 
                                    "activity_date", "normal", "priority_view", "user", "attach"),
                              
                "aligns"   => array("center", "left", "left", "left", "left", 
                                    "center", "center", "center", "center"));
} else {
/*	$fields = array("headings" => array("View", "Author", "Subject", "Date", 
                                    "Followup", "Status", "Priority", "Owner"),

                "columns"  => array("ticket", "author", "subject", "timestamp", 
                                    "activity", "type", "priority", "assignment"),

                "types"    => array("view", "email", "normal", "open_date", 
                                    "activity_date", "normal", "priority_view", "user"),
                              
                "aligns"   => array("center", "left", "left", "left", "left", 
                                    "center", "center", "center"));
                                    */
	$fields = array("headings" => array("View", "Author", "Subject", "Date", 
                                    "Status", "Priority"),

                "columns"  => array("ticket", "author", "subject", "timestamp", 
                                     "type", "priority"),

                "types"    => array("view", "email", "normal", "open_date", 
                                     "normal", "priority_view"),
                              
                "aligns"   => array("center", "left", "left", "left", 
                                    "center", "center"));                                    
}
												
/* set up defaults for viewing */

$column = @$column ? $column : "priority";
$direction = @$direction ? $direction : "DESC";
$offset = @$offset ? $offset : 0;
$limit = @$limit ? $limit : $CONFIG["view_rows"];

/* count tickets */
$query = "SELECT COUNT(*) FROM tickets WHERE parent = '0'";
if ($type != 'All') {
    $query .= " AND type = '$type'";
}
$ticket_count = query2result($query);

/* paging controls */
if (($offset + $limit) < $ticket_count) {
    $page_string = ($offset + 1) . " to " . ($offset + $limit) . " of $ticket_count";
}
else {
    $page_string = ($offset + 1) . " to $ticket_count of $ticket_count";
}


/* start table */
?>

<table cellspacing="0" cellpadding="2" border="0" width="100%"  class="">


<?php
/* form query */
$select_fields= join(", ", $fields["columns"]);
$query = "SELECT $select_fields FROM tickets WHERE ";
if ($type != 'All') {
   $query .= "type = '$type' AND ";
}

if  (!is_null($user)) {
	$query .= "(assignment = '$user' OR assignment = '0') AND ";
}

$query .= "parent = '0' ORDER BY " . urlencode($column) . " $direction LIMIT $offset, $limit";



//echo "<pre>$query</pre>";

/* do query */
$result = do_query($query);
$parent_count = number_rows($result);

/* output tickets */
if ($parent_count) {
    print("<tr class=\"tableHeaderGral\">\n");
    for ($loop = 0; $loop < count($fields["headings"]); $loop++) {
        print("<th class=\"tableHeaderText\" align=" . $fields["aligns"][$loop] . ">");
        print("<a href=index.php?m=ticketsmith&type=$type");
        print("&column=" . $fields["columns"][$loop]);
        if ($column != $fields["columns"][$loop]) {
            $new_direction = "ASC";
        }
        else {
            if ($direction == "ASC") {
                $new_direction = "DESC";
            }
            else {
                $new_direction == "ASC";
            }
        }
        print("&direction=$new_direction");
        print(' class="">' . $AppUI->_($fields["headings"][$loop]) . "</a></th>\n");
    }
    print("</tr>\n");
    while ($row = result2hash($result)) {
        print("<tr height=25>\n");
        for ($loop = 0; $loop < count($fields["headings"]); $loop++) {
            print("<td  bgcolor=white align=" . $fields["aligns"][$loop] . ">\n");
	        print(format_field($row[$fields["columns"][$loop]], $fields["types"][$loop], $row[$fields["columns"][0]]) . "\n");
            print("</td>\n");
        }
        print("</tr>\n");
        print("<tr class=\"tableRowLineCell\"><td colspan=\"" . count($fields["headings"]) . "\"></td></tr>");
    }
}
else {
    print("<tr height=25>\n");
    print("<td align=center colspan=" . count($fields["headings"]) . ">\n");
    print("There are no ");
    print($type == "All" ? "" : strtolower($type) . " ");
    print("tickets.\n");
    print("</td>\n");
    print("</tr>\n");
}

if ($ticket_count > $limit) {
/* output action links */
print("<tr>\n");
print("<td><br /></td>\n");
print("<td colspan=" . (count($fields["headings"]) - 1) . " align=right>\n");

/*
print("<table width=100% border=0 cellspacing=0 cellpadding=0>\n");
print("<tr height=25><td align=left>");
$types = array("My","Open","Processing","Closed","Deleted","All");
for ($loop = 0; $loop < count($types); $loop++) {
    $toggles[] = "<a href=index.php?m=ticketsmith&type=" . $types[$loop] . ">" . $AppUI->_($types[$loop]) . "</a>";
}
print(join(" | ", $toggles));
print(" Tickets</td>\n");
if ($type == "Deleted" && $parent_count) {
    print("<td align=center><a href=index.php?m=ticketsmith&type=Deleted&action=expunge>".$AppUI->_('Expunge Deleted')."</a></td>");
}
print("<td align=right><a href=index.php?m=ticketsmith&a=search>".$AppUI->_('Search')."</a> | 
<a href=index.php?m=ticketsmith&type=$type>".$AppUI->_('Back to top')."</a></td></tr>\n");
print("</table>\n");
*/


    if ($offset - $limit >= 0) {
        print("<a href=index.php?m=ticketsmith&type=$type&column=$column&direction=$direction&offset=" . ($offset - $limit) . "><img src=\"modules/ticketsmith/ltwt.gif\" border=0></a> | \n");
    }
    print($AppUI->_("$page_string")."\n");
    if ($offset + $limit < $ticket_count) {
        print(" | <a href=index.php?m=ticketsmith&type=$type&column=$column&direction=$direction&offset=" . ($offset + $limit) . "><img src=\"modules/ticketsmith/rtwt.gif\" border=0></a>\n");
    }

print("</td>\n");
print("</tr>\n");    
}
?>
</table>
