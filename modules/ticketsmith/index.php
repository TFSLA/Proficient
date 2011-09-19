<?php  /* TICKETSMITH $Id: index.php,v 1.4 2009-06-19 18:33:56 pkerestezachi Exp $ */
$type = dPgetParam( $_GET, 'type', 'Open' );
$action = dPgetParam( $_REQUEST, 'action', null );

// setup the title block
$titleBlock = new CTitleBlock( 'Trouble Ticket Management', 'tickets.gif', $m, "$m.$a" );
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new ticket').'">', '',
		'<form action="?m=ticketsmith&a=post_ticket" method="post">', '</form>'
	);
}
$titleBlock->show();

require("modules/ticketsmith/config.inc.php");
require("modules/ticketsmith/common.inc.php");

/* expunge deleted tickets */
if (@$action == "expunge") {
    $deleted_parents = column2array("SELECT ticket FROM tickets WHERE type = 'Deleted'");
    for ($loop = 0; $loop < count($deleted_parents); $loop++) {
        do_query("DELETE FROM tickets WHERE ticket = '$deleted_parents[$loop]'");
        do_query("DELETE FROM tickets WHERE parent = '$deleted_parents[$loop]'");
    }
}

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
	$fields = array("headings" => array("View", "Author", "Subject", "Date", 
                                    "Followup", "Status", "Priority", "Owner"),

                "columns"  => array("ticket", "author", "subject", "timestamp", 
                                    "activity", "type", "priority", "assignment"),

                "types"    => array("view", "email", "normal", "open_date", 
                                    "activity_date", "normal", "priority_view", "user"),
                              
                "aligns"   => array("center", "left", "left", "left", "left", 
                                    "center", "center", "center"));
}
												
/* set up defaults for viewing */
if($type == "my"){
	$title = "My Tickets";
}
else{
	$title = "$type Tickets";
}
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

<table border="0" cellspacing="0" cellpadding="0" class="" width="100%">
<tr>
	<td colspan="<?php echo count( $fields["headings"] );?>" align="center">
        <table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_04.gif">
            <tr>
                <td align="left"><img src="images/common/lado.gif" width="1" height="17"></td>
                <td class="boldtext">
                    <?php echo $AppUI->_($title);?>
                    <?php
                        if ($ticket_count > $limit) {
                            if ($offset - $limit >= 0) {
                                print("<a href=index.php?m=ticketsmith&type=$type&column=$column&direction=$direction&offset=" . ($offset - $limit) . "><img src=ltwt.gif border=0></a> | \n");
                            }
                            print($AppUI->_("$page_string")."\n");
                            if ($offset + $limit < $ticket_count) {
                                print(" | <a href=index.php?m=ticketsmith&type=$type&column=$column&direction=$direction&offset=" . ($offset + $limit) . "><img src=rtwt.gif border=0></a>\n");
                            }
                        }
                    ?>
                </td>
                <td align="right"><img src="images/common/lado.gif" width="1" height="17"></td>
            </tr>
            <tr bgcolor="#666666">
                <td colspan="3"></td>
            </tr>
        </table>
        <!--table width="100%" border="0" cellspacing="1" cellpadding="1">
		<tr>
			<td width="33%"></td>
			<td width="34%" align="center"><strong><?php echo $AppUI->_($title);?></strong></td>
			<td width="33%" align="right" valign="middle">
<?php
if ($ticket_count > $limit) {
    if ($offset - $limit >= 0) {
        print("<a href=index.php?m=ticketsmith&type=$type&column=$column&direction=$direction&offset=" . ($offset - $limit) . "><img src=ltwt.gif border=0></a> | \n");
    }
    print($AppUI->_("$page_string")."\n");
    if ($offset + $limit < $ticket_count) {
        print(" | <a href=index.php?m=ticketsmith&type=$type&column=$column&direction=$direction&offset=" . ($offset + $limit) . "><img src=rtwt.gif border=0></a>\n");
    }
}
?>
			</td>
		</tr>
		</table-->
	</td>
</tr>

<?php
/* form query */
$select_fields= join(", ", $fields["columns"]);
$query = "SELECT $select_fields FROM tickets WHERE ";
if ($type == "My") {
    $query .= "type = 'Open' AND (assignment = '$AppUI->user_id' OR assignment = '0') AND ";
}
elseif ($type != "All") {
    $query .= "type = '$type' AND ";
}
$query .= "parent = '0' ORDER BY " . urlencode($column) . " $direction LIMIT $offset, $limit";

/* do query */
$result = do_query($query);
$parent_count = number_rows($result);

/* output tickets */
if ($parent_count) {
    print("<tr class=\"tableHeaderGral\">\n");
    for ($loop = 0; $loop < count($fields["headings"]); $loop++) {
        print("<td class=\"tableHeaderText\" align=" . $fields["aligns"][$loop] . ">");
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
        print("<tr class=\"tableRowLineCell\"><td colspan=\"10\"></td></tr>");
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

/* output action links */
print("<tr>\n");
print("<td><br /></td>\n");
print("<td colspan=" . (count($fields["headings"]) - 1) . " align=right>\n");
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
print("</td>\n");
print("</tr>\n");    

/* end table */
print("</table>\n");

?>
