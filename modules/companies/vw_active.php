<?php /* COMPANIES $Id: vw_active.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
##
##	Companies: View Projects sub-table
##
GLOBAL $AppUI, $company_id, $pstatus, $dPconfig;

$df = $AppUI->getPref('SHDATEFORMAT');
$canEditProjects = !getDenyEdit("projects");

$objPrj = new CProject();
$prjs = $objPrj->getAllowedRecords($AppUI->user_id, "project_id");
$where = (count($prjs) > 0 ? "\n\tWHERE project_id IN (" . implode( ',', array_keys($prjs) ) . ')' : "\n\tWHERE project_id is null");

$sql = "
SELECT
        project_id, project_active, project_status, project_color_identifier, project_name, project_description,
	project_start_date, project_end_date, project_actual_end_date,
	project_color_identifier, project_actual_budget, 
	project_company, company_name, project_status,
	COUNT(distinct t1.task_id) AS total_tasks,
	COUNT(distinct t2.task_id) AS my_tasks,
	user_username, companies.company_id as company_id,
	SUM(t1.task_duration*t1.task_duration_type*t1.task_manual_percent_complete)/sum(t1.task_duration*t1.task_duration_type) as project_percent_complete
FROM projects
LEFT JOIN companies ON company_id = projects.project_company
LEFT JOIN users ON projects.project_owner = users.user_id
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
LEFT JOIN tasks t2 ON projects.project_id = t2.task_project
	AND t2.task_owner = $AppUI->user_id
$where"
.($company_id ? "\nAND project_company = $company_id" : '')

."
	AND project_active = 1
GROUP BY project_id
";

$s = '';

if (!($rows = db_loadList( $sql, NULL ))) {
	$s = $AppUI->_( 'No data available' ).'<br />'.$AppUI->getMsg();
	$s .= "<tr class=\"tableRowLineCell\"><td colspan=\"5\"></td></tr>"; 
} else {
	$s .= '<tr class="tableHeaderGral">';
	$s .= '<th>'.$AppUI->_( 'Name' ).'</th>'
		.'<th>'.$AppUI->_( 'Owner' ).'</th>'
		.'<th>'.$AppUI->_( 'Started' ).'</th>'
		.'<th>'.$AppUI->_( 'Status' ).'</th>'
		.'<th>'.$AppUI->_( 'Budget' ).'</th>'
		.'</tr>';
	foreach ($rows as $row) {
		$start_date = new CDate( $row['project_start_date'] );
		$s .= '<tr>';
		$s .= '<td width="100%">';
		$s .= '<a href="?m=projects&a=view&project_id='.$row["project_id"].'">'.$row["project_name"].'</a></td>';
		//$s .= '<td nowrap="nowrap">'.$row["user_first_name"].'&nbsp;'.$row["user_last_name"].'</td>';
		$s .= '<td nowrap="nowrap">'.$row["user_username"].'</td>';
		$s .= '<td nowrap="nowrap">'.$start_date->format( $df ).'</td>';
		$s .= '<td nowrap="nowrap">'.$AppUI->_($pstatus[$row["project_status"]]).'</td>';
		$s .= '<td nowrap="nowrap" align="right">'.$dPconfig["currency_symbol"].$row["project_actual_budget"].'</td>';
		$s .= '</tr>';
        $s .= "<tr class=\"tableRowLineCell\"><td colspan=\"5\"></td></tr>"; 
	}
}
if ($canEditProjects){
	$s .= "<tr><td colspan=\"100\" align=\"right\">".
			'<input type="button" class="button" value="'.$AppUI->_('new project').
			'" onclick="javascript: document.location = \'?m=projects&a=addedit&company_id='.$company_id.'\';">'
			."</td></tr>";
}
echo '<table cellpadding="2" cellspacing="0" border="0" width="100%" class="">' . $s . '</table>';
?>
