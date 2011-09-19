<?php /* COMPANIES $Id: vw_users.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
##
##	Companies: View User sub-table
##
GLOBAL $AppUI, $company_id;

$sql = "
SELECT user_id, user_username, user_first_name, user_last_name
FROM users
WHERE user_company = $company_id order by user_username
";

if (!($rows = db_loadList( $sql, NULL ))) {
	echo $AppUI->_('No data available').'<br />'.$AppUI->getMsg();
} else {
?>
<table width="100%" border=0 cellpadding="2" cellspacing="0" class="">
<tr class="tableHeaderGral">
	<th><?php echo $AppUI->_( 'Username' );?></td>
	<th><?php echo $AppUI->_( 'Name' );?></td>
</tr>
<?php
$s = '';
foreach ($rows as $row){
	$s .= '<tr><td>';
	$s .= '<a href="./index.php?m=admin&a=viewuser&user_id='.$row["user_id"].'">'.$row["user_username"].'</a>';
	$s .= '<td>'.$row["user_first_name"].'&nbsp;'.$row["user_last_name"].'</td>';
	$s .= '</tr>';
    $s .= "<tr class=\"tableRowLineCell\"><td colspan=\"2\"></td></tr>";
}
echo $s;
?>
</table>
<?php } ?>
