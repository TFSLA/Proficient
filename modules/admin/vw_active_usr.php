<?php /* ADMIN $Id: vw_active_usr.php,v 1.2 2009-07-27 15:53:19 nnimis Exp $ */
GLOBAL  $canEdit, $stub, $where, $orderby,$utype,$ucompany,$ustatus;

$sql = "
SELECT user_id, user_username, user_last_name, user_first_name, permission_user, user_email, company_name, user_company, user_status, count(permission_id) AS perm_count
FROM users
LEFT JOIN permissions ON user_id = permission_user
LEFT JOIN companies ON company_id = user_company
WHERE ( user_type = $utype or $utype = -1)";
if ($stub) {
	$sql .= "\n	AND ((UPPER(user_last_name) LIKE '$stub%') OR (UPPER(user_username) LIKE '$stub%'))";
} else if ($where) {
	$sql .= "\n	AND ((UPPER(user_username) LIKE '%$where%' or UPPER(user_first_name) LIKE '%$where%' OR UPPER(user_last_name) LIKE '%$where%'))";
}
$sql .=" AND ( user_company = $ucompany or $ucompany = 0) ";
$sql .=" AND (user_status = !$ustatus) ";
$sql .=" GROUP BY (user_id) ";
$sql .= "\nORDER by $orderby";

if($AppUI->getState( 'Revert' ) == 1)
	$sql .= " DESC";

//echo "<pre>$sql";
//var_dump($_POST);
//var_dump($stub);
//echo "</pre>";

//$users = db_loadList( $sql );

$dp = new DataPager($sql, "users");
$dp->showPageLinks = true;
$users = $dp->getResults();
$rn = $dp->num_result;
$pager_links = $dp->RenderNav();

require "{$AppUI->cfg['root_dir']}/modules/admin/vw_usr.php";

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

