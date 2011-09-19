<?php /* COMPANIES $Id: vw_pipeline.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
##
##	Companies: View Projects sub-table
##
GLOBAL $AppUI, $canEdit;

$sql = "
SELECT salespipeline.*, CONCAT(users.user_first_name, ' ', users.user_last_name) AS _accountmanagername
FROM salespipeline LEFT JOIN users ON salespipeline.accountmanager = users.user_id
ORDER BY probability, totalincome
";
##echo $sql;


function showrowlead( &$a ) {
	$s = '';

	$s .= '<td>';
	$s .= '<a href="./index.php?m=pipeline&a=addedit&id='.$a["id"].'">';
	$s .= '<img src="./images/icons/edit_small.gif" alt="Edit Lead" border="0" width="12" height="12"></a>';
	$s .= '<a href="javascript:delLead('. $a["id"] .', \''. $a["accountname"] .'\')"><img src="images/icons/trash.gif" width="16" height="16" border="0" alt="delete"></a>';
	$s .= '</td>';
	$s .= '<td valign="top">';
	$s .= '<a href="./index.php?m=pipeline&a=addedit&id='.$a["id"].'">'.$a["_accountmanagername"].'</a>';
	$s .= '</td>';
	$s .= '<td valign="top">'.$a["segment"].'</td>';
	$s .= '<td valign="top">'.$a["accountname"].'</td>';
	$s .= '<td valign="top">'.$a["projecttype"].'</td>';
	$s .= '<td valign="top">'.$a["opportunitysource"].'</td>';
	$s .= '<td valign="top">'.$a["thirdparties"].'</td>';
	$s .= '<td valign="top">'.$a["description"].'</td>';
	$s .= '<td valign="top">'.$a["competition"].'</td>';
	$s .= '<td valign="top">'.$a["totalincome"].'</td>';
	$s .= '<td valign="top">'.$a["cost"].'</td>';
	$s .= '<td valign="top">'.$a["margin"].'</td>';
	$s .= '<td valign="top">'.$a["revised"].'</td>';
	$s .= '<td valign="top">&nbsp;&nbsp;'.$a["probability"].'%</td>';
	$s .= '<td valign="top">'.$a["closingdate"].'</td>';
	$s .= '<td valign="top">'.$a["invoicedate"].'</td>';
	$s .= '<td valign="top">'.$a["duration"].'</td>';

	echo "<tr>$s</tr>";
    echo "<tr class=\"tableRowLineCell\"><td colspan=\"17\"></td></tr>";
}


$s = '<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">';
$s .= '<tr class="tableHeaderGral">';
$s .= '<th>&nbsp;</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Account Manager' ).'</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Segment' ).'</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Account Name' ).'</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Project Type' ).'</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Lead Source' ).'</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Third Parties' ).'</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Description' ).'</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Competition' ).'</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Total Income' ).'</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Cost' ).'</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Margin' ).'</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Revised' ).'</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Probability of Winning' ).'</th>';
$s .= '<th width="70" valign="top">'.$AppUI->_( 'Close date' ).'</th>';
$s .= '<th width="70" valign="top">'.$AppUI->_( 'Invoice date' ).'</th>';
$s .= '<th valign="top">'.$AppUI->_( 'Duration' ).'</th>';

$rows = db_loadList( $sql, NULL );
if (!count( $rows)) {
    $s .= "</tr><tr><td>";
    //$s .= $AppUI->_('No data available');
    $s .= $AppUI->_('fede');
    $s .= "</td>";
}
/*
$s .= '<td nowrap="nowrap" rowspan="99" align="right" valign="top" style="background-color:#ffffff">';
if ($canEdit) {
	$s .= '<input type="button" class=button value="'.$AppUI->_( 'new lead' ).'" onClick="javascript:window.location=\'./index.php?m=pipeline&a=addedit\';">';
}
$s .= '</td>';
*/
$s .= '</tr>';
echo $s;

foreach ($rows as $row) {
	showrowlead( $row );
}
echo '</table>';
?>
