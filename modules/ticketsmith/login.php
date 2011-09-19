<?php

/* $Id: login.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */

require("modules/ticketsmith/config.inc.php");
require("modules/ticketsmith/common.inc.php");

/* set title */
$title = "Ticketsmith Login";

/* start form */
print("<form name=\"form\" action=\"$PHP_SELF\" method=\"post\">\n");

/* start table */
print("<table class=maintable>\n");
print("<tr>\n");
print("<td colspan=\"2\" align=\"center\" bgcolor=\"" . $CONFIG["heading_color"] . "\">\n");
print("<div class=\"heading\">$title</div>\n");
print("</td>\n");
print("</tr>\n");

/* output content */
print("<tr><td align=\"right\"><strong>Username</strong></td><td><input type=\"text\" name=\"login_attempt\"></td></tr>\n");
print("<tr><td align=\"right\"><strong>Password</strong></td><td><input type=\"password\" name=\"password_attempt\"></td></tr>\n");
print("<tr><td><br /></td><td><input type=\"submit\" value=\"Login\"> <input type=\"reset\" value=\"Clear\"></td></tr>\n");

/* end table */
print("</table>\n");

/* end form */
print("</form>\n");

/* focus login */
print("<script language=\"javascript\">\n");
print("\tdocument.form.login_attempt.focus();\n");
print("</script>\n");
?>
