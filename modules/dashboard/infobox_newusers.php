<?
if (isset( $_GET['orderby'] )) {
    $AppUI->setState( 'UserIdxOrderby', $_GET['orderby'] );
}
$orderby = $AppUI->getState( 'UserIdxOrderby' ) ? $AppUI->getState( 'UserIdxOrderby' ) : 'user_username';

/*
$sql = "
SELECT DISTINCT(user_id), user_username, user_last_name, user_first_name, permission_user, user_email, company_name, user_company
FROM users
LEFT JOIN permissions ON user_id = permission_user
LEFT JOIN companies ON company_id = user_company
WHERE permission_value IS NOT NULL
";
*/

$sql = "
SELECT DISTINCT(user_id), user_username, user_last_name, user_first_name, permission_user, user_email, company_name, user_company
FROM users
LEFT JOIN permissions ON user_id = permission_user
LEFT JOIN companies ON company_id = user_company
";

if ($stub) {
	$sql .= "\n	AND (UPPER(user_username) LIKE '$stub%' or UPPER(user_first_name) LIKE '$stub%' OR UPPER(user_last_name) LIKE '$stub%')";
} else if ($where) {
	$sql .= "\n	AND (UPPER(user_username) LIKE '%$where%' or UPPER(user_first_name) LIKE '%$where%' OR UPPER(user_last_name) LIKE '%$where%')";
}

$sql .= "\nWHERE date_created = CURDATE() ";
$sql .= "\nORDER by $orderby";
$users = db_loadList( $sql );
?>


<table cellpadding="2" cellspacing="0" border="0" width="100%" class="">
<tr class="tableHeaderGral">
	<th width="60" align="right">
		&nbsp; 
	</th>
	<th width="150">
		<?php echo $AppUI->_('Login Name');?>
	</th>
	<th>
		<?php echo $AppUI->_('Real Name');?>
	</th>
	<th>
		<?php echo $AppUI->_('Company');?>
	</th>
</tr>
<?php 
foreach ($users as $row) {
?>
<tr>
	<td align="right" nowrap="nowrap" width=70 >
<?php if ($canEdit) { ?>
		<table align=center width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td>
				<a href="./index.php?m=admin&a=addedituser&user_id=<?php echo $row["user_id"];?>" title="<?php echo $AppUI->_('edit');?>">
					<?php echo dPshowImage( './images/icons/edit_small.gif', 20, 20, '' ); ?>
				</a> 
			</td>
			<td>
				<a href="?m=admin&a=viewuser&user_id=<?php echo $row["user_id"];?>&tab=1" title="">
					<img src="images/obj/edit_permissions_small.jpg" width="20" height="20" border="0" alt="<?php echo $AppUI->_('edit permissions');?>">
				</a> 
			</td>
			<td>
				<a href="javascript:delMe(<?php echo $row["user_id"];?>, '<?php echo $row["user_first_name"] . " " . $row["user_last_name"];?>')" title="<?php echo $AppUI->_('delete');?>">
					<?php echo dPshowImage( './images/icons/trash_small.gif', NULL, NULL, '' ); ?>
				</a>
			</td>
		</tr>
		</table>
<?php } ?>
	</td>
	<td>
		<a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $row["user_id"];?>"><?php echo $row["user_username"];?></a>
	</td>
	<td>
		<a href="mailto:<?php echo $row["user_email"];?>"><img src="images/obj/email.gif" width="16" height="16" border="0" alt="email"></a>
		<?php echo $row["user_last_name"].', '.$row["user_first_name"];?>
	</td>
	<td>
		<a href="./index.php?m=companies&a=view&company_id=<?php echo $row["user_company"];?>"><?php echo $row["company_name"];?></a>
	</td>
</tr>
<tr class="tableRowLineCell">
    <td colspan="4">
    </td>
</tr>
<?php }?>

</table>

