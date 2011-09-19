<?php /* COMPANIES $Id: vw_archived.php,v 1.3 2009-07-21 16:05:23 nnimis Exp $ */
##
##	Companies: View Archived Projects sub-table
##
GLOBAL $AppUI, $company_id;

$objPrj = new CProject();
$prjs = $objPrj->getAllowedRecords($AppUI->user_id, "project_id");
$where = (count($prjs) > 0 ? "\n\tproject_id IN (" . implode( ',', array_keys($prjs) ) . ')' : "\n\tproject_id is null");

$sql = "
SELECT projects.project_id, projects.project_name, users.user_first_name,users.user_last_name
FROM projects
LEFT JOIN users ON users.user_id = projects.project_owner
WHERE project_company = $company_id
	AND project_active = 0
	AND $where
ORDER BY project_name
";

$s = '';
if (!($rows = db_loadList( $sql, NULL ))) {
	$s .= $AppUI->_( 'No data available' ).'<br />'.$AppUI->getMsg();
} else {
	$s .= '<tr class="tableHeaderGral">'
		.'<th>'.$AppUI->_( 'Name' ).'</td>'
		.'<th>'.$AppUI->_( 'Owner' ).'</td>'
		.'</tr>';

	foreach ($rows as $row){
		$s .= '<tr><td>';
		$s .= '<a href="?m=projects&a=view&project_id='.$row["project_id"].'">'.$row["project_name"].'</a>';
		$s .= '<td>'.$row["user_first_name"].'&nbsp;'.$row["user_last_name"].'</td>';
		$s .= '</tr>';
        $s .= "<tr class=\"tableRowLineCell\"><td colspan=\"2\"></td></tr>"; 
	}
}
echo '<table cellpadding="2" cellspacing="0" border="0" width="100%" class="">' . $s . '</table>';

?>
