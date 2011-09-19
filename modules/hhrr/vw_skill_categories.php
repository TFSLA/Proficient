<?php /* COMPANIES $Id: vw_skill_categories.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
##
##	Companies: View Projects sub-table
##
GLOBAL $AppUI, $canEdit;

$sql = "
SELECT skillcategories.*
FROM skillcategories
ORDER BY name
";

function showrow( &$a ) {
	global $AppUI;
	$s = '';
	$s .= '<td>';
	$s .= '<a href="./index.php?m=hhrr&a=addeditskillcategory&id='.$a["id"].'">';
	$s .= '<img src="./images/icons/edit_small.gif" alt="' . $AppUI->_('Edit Skill Category') . '" border="0" width="20" height="20"></a>';
	$s .= ' <a href="javascript:delSkillcategory('. $a["id"] .', \''. $a["name"] .'\')"><img src="images/icons/trash_small.gif"  border="0" alt="' . $AppUI->_('Delete') . '"></a>';
	$s .= '</td>';
	$s .= '<td>';
	$s .= '<a href="./index.php?m=hhrr&a=addeditskillcategory&id='.$a["id"].'">'.$a["name"].'</a>';
	$s .= '</td>';

	echo "<tr>$s</tr>";
    echo "<tr class=\"tableRowLineCell\"><td colspan=\"2\"></td></tr>";
}



$s = '<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">';
$s .= '<tr class="tableHeaderGral">';
$s .= '<th width="50">&nbsp;</th>';
$s .= '<th width="">'.$AppUI->_( 'Name' ).'</th>';

//$rows = db_loadList( $sql, NULL );
$dp = new DataPager($sql, "categories");
$dp->showPageLinks = true;
$rows = $dp->getResults();
if (!count( $rows)) {
    $s .= '</tr><tr>';
	$s .= "<td colspan=\"97\">".$AppUI->_('No data available')."</td>";
}
$rn = $dp->num_result;
$pager_links = $dp->RenderNav();
$s .= '<!--td nowrap="nowrap" rowspan="99" align="right" valign="top" style="background-color:#ffffff">';
if ($canEdit) {
	$s .= '<input type="button" class=button value="'.$AppUI->_( 'new skill category' ).'" onClick="javascript:window.location=\'./index.php?m=hhrr&a=addeditskillcategory\';"-->';
}

$s .= '</tr>';
echo $s;

foreach ($rows as $row) {
	showrow( $row );
}
echo '</table>';

echo "
<table border='0' width='100%' cellspacing='0' cellpadding='1'>
<tr bgcolor=#E9E9E9>
	<td align='center'>$pager_links</td>
</tr>
<tr>
		<td height=1 colspan=5 bgcolor=#E9E9E9></td>
</tr>
</table>"; 
?>
