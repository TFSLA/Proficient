<?php

/* $Id: search.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */

require("modules/ticketsmith/config.inc.php");
require("modules/ticketsmith/common.inc.php");
if(empty($search_pattern)) $search_pattern = "";
if(empty($search_field)) $search_field = "";
if(empty($search_depth)) $search_depth = "";
if(empty($sort_column)) $sort_column = "";
// setup the title block
$titleBlock = new CTitleBlock( 'Search Tickets', 'tickets.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=ticketsmith", "tickets list" );
$titleBlock->show();

/* set title */
$title = "Search Tickets";
/* start form */
print("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" background=\"images/common/back_1linea_04.gif\">");
print("<tr>");
print("<td align=\"left\"><img src=\"images/common/lado.gif\" width=\"1\" height=\"17\"></td>");
print("<td class=\"boldtext\" align=\"left\">");
print($AppUI->_($title));
print("</td>");
print("<td align=\"right\"><img src=\"images/common/lado.gif\" width=\"1\" height=\"17\"></td>");
print("</tr>");
print("<tr bgcolor=\"#666666\">");
print("<td colspan=\"3\"></td>");
print("</tr>");
print("</table>");
print("<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" width=100%>\n");
print("<form action=index.php?m=ticketsmith&a=search method=\"post\">\n");
/* start table */
print("<!--tr>\n");
print("<td colspan=\"2\" align=\"center\"  width=100%>\n");

print("</td>\n</tr-->\n");

/* field select */
print("<tr class=\"tableForm_bg\">\n");
print("<td align=\"right\"><strong>Field</strong></td>\n");
print("<td>");
$field_choices = array("author"  => "Author",
                       "body"    => "Body", 
                       "subject" => "Subject");


$field_selectbox = create_selectbox("search_field", $field_choices, $search_field);
print("$field_selectbox\n");
print("</td>\n");
print("</tr>\n");

/* pattern select */
print("<tr class=\"tableForm_bg\">\n");
print("<td align=\"right\"><strong>Pattern</strong></td>\n");
print("<td><input type=\"text\" name=\"search_pattern\" class=\"text\" value=\"$search_pattern\"></td>\n");
print("</tr>\n");

/* depth select */
print("<tr class=\"tableForm_bg\">\n");
print("<td align=\"right\"><strong>Depth</strong></td>\n");
print("<td>");
$depth_choices = array("All"     => "All Tickets", 
                       "Open"    => "Open Parents",
                       "Closed"  => "Closed Parents",
                       "Deleted" => "Deleted Parents",
                       "Child"   => "Followups &amp; Comments");

$depth_selectbox = create_selectbox("search_depth", $depth_choices, $search_depth);
print("$depth_selectbox\n");
print("</td>\n");
print("</tr>\n");

/* sort select */
print("<tr class=\"tableForm_bg\">\n");
print("<td align=\"right\"><strong>Sort By</strong></td>\n");
print("<td>");
$sort_choices = array("ticket"     => "Ticket",
                      "author"     => "Author",
                      "subject"    => "Subject",
                      "timestamp"  => "Date",
                      "activity"   => "Activity",
                      "type"       => "Type",
                      "priority"   => "Priority",
                      "assignment" => "Owner");

$sort_selectbox = create_selectbox("sort_column", $sort_choices, $sort_column);
print($sort_selectbox);
print(" <input type=\"radio\" name=\"sort_direction\" value=\"ASC\"> Ascending");
print(" <input type=\"radio\" name=\"sort_direction\" value=\"DESC\" checked> Descending");
print("</td>\n");
print("</tr>\n");

/* submit button */
print("<tr class=\"tableForm_bg\">\n");
print("<td><br /></td>\n");
print("<td><input type=\"submit\" class=\"button\" value=\"Search\"></td>\n");
print("</tr>\n");

/* output footer */
/*print("<tr class=\"tableForm_bg\">\n");
print("<td><br /></td>\n");
print("<td><a href=index.php?m=ticketsmith>Return to ticket list</a></td>\n");
print("</tr>\n");*/

/* end table */
print("</table>\n");

if ($search_pattern) {

    /* set fields */
    $fields = array("columns"  => array("ticket", "author", "subject", "timestamp", "type"),
                    "types"    => array("view", "email", "normal", "elapsed_date", "normal"),
                    "aligns"   => array("center", "left", "left", "left", "center"));
    
    /* start results table */
    print("<p>\n");
    print("<table width=\"95%\" border=\"1\" cellspacing=\"5\" cellpadding=\"5\">\n");

    /* form search query */
    $select_columns = join(", ", $fields["columns"]);
    $search_pattern = "%" . escape_string($search_pattern) . "%";
    $query = "SELECT $select_columns FROM tickets WHERE $search_field LIKE '$search_pattern'";
    if ($search_depth == "Child") {
        $query .= " AND parent != 0";
    }
    elseif ($search_depth != "All") {
        $query .= " AND type = '$search_depth'";
    }
    $query .= " ORDER BY $sort_column $sort_direction";
    
    /* perform search */
    $result = do_query($query);
    
    /* display results */
    $result_count = number_rows($result);
    if ($result_count) {
        print("<tr><td colspan=\"5\">There were $result_count results in the given search.</td></tr>\n");
        while ($row = result2hash($result)) {
            print("<tr>");
            for ($loop = 0; $loop < count($fields["columns"]); $loop++) {
                print("<td align=\"" . $fields["aligns"][$loop] . "\">");
                print(format_field($row[$fields["columns"][$loop]], $fields["types"][$loop]));
                print("</td>");
            }
            print("</tr>\n");
        }
    }
    else {
        print("<tr><td>There were no results in the given search.</td></tr>\n");
    }

    /* end results table */
    print("</table>\n");

}
/* end form */
print("</form>\n");
?>