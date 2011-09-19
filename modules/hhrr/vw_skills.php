<?php /* COMPANIES $Id: vw_skills.php,v 1.2 2009-07-15 13:52:58 nnimis Exp $ */
##
##	Companies: View Projects sub-table
##
GLOBAL $AppUI, $canEdit;


$cat = dPgetParam( $_GET, "cat", "-1" );

$sql = "
SELECT skills.*, skillcategories.name
FROM skills, skillcategories
WHERE skillcategories.id = skills.idskillcategory
".($cat != "-1" ? " and skillcategories.id = '$cat'" : "")."
ORDER BY skills.idskillcategory, skills.description
";
##echo $sql;


function showrowskill( &$a ) {
	global $AppUI;
	$s = '';
	$s .= '<td>';
	$s .= '<a href="./index.php?m=hhrr&a=addeditskill&id='.$a["id"].'">';
	$s .= '<img src="./images/icons/edit_small.gif" alt="'.$AppUI->_('Edit Skill').'" border="0" width="20" height="20"></a> ';
	$s .= '<a href="javascript:delSkill('. $a["id"] .', \''. $a["name"] .'\')"><img src="images/icons/trash_small.gif"  border="0" alt="'.$AppUI->_('Delete').'"></a>';
	$s .= '</td>';
	$s .= '<td>';
	$s .= '<a href="./index.php?m=hhrr&a=addeditskill&id='.$a["id"].'">'.$a["description"].'</a>';
	$s .= '</td>';
	$s .= '<td>';
	$s .= $a["name"];
	$s .= '</td>';

	echo "<tr>$s</tr>";
     echo "<tr class=\"tableRowLineCell\"><td colspan=\"3\"></td></tr>";
}

//$cat = dPgetParam( $_POST, "cat", "" );
$cats = db_loadHashList(" SELECT skillcategories.id, skillcategories.name
														FROM skillcategories
														ORDER BY skillcategories.name
														");

$cats = arrayMerge(array("-1"=>$AppUI->_('All')), $cats);
$vars = explode("&", $_SERVER["QUERY_STRING"]);
$hiddens ="";

   /* for($i = 0; $i < count($vars); $i++){
    	$val = explode("=",$vars[$i]);
    	$hiddens .= '<input type="hidden" name="'.$val[0].'" value="'.$val[1].'" />';
    }*/

$s = '<form action="" method="GET">';
$s .= '<input type="hidden" name="m" value="hhrr" /><input type="hidden" name="skill_next_page" value="'.$_GET[skill_next_page].'" />';

$s .= '<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">';
$s .= '<tr class="tableHeaderGral">';
$s .= '<th align="left" width="50" >&nbsp;</th>';
$s .= '<th >'.$AppUI->_( 'Name' ).'</th>';
$s .= '<th width="400" align="left">'.$AppUI->_( 'Category' ).'&nbsp;';


$s .= arraySelect($cats, "cat", 'size="1" class="text" onchange="javascript: this.form.submit();"', $cat, false ,false,"28s0px");

$s .= '</th>';
$s .= '</form>';

//$rows = db_loadList( $sql, NULL );
$dp = new DataPager($sql, "skill");
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
	$s .= '<input type="button" class=button value="'.$AppUI->_( 'new skill' ).'" onClick="javascript:window.location=\'./index.php?m=hhrr&a=addeditskill\';"-->';
}
//$s .= '</td>';
$s .= '</tr>';
echo $s;

foreach ($rows as $row) {
	showrowskill( $row );
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
