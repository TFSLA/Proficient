<?

// pull permissions into master array
$sql = "
SELECT permission_grant_on g, permission_item i, permission_value v
FROM permissions
WHERE permission_user = $AppUI->user_id
UNION
SELECT permission_grant_on g, permission_user i, permission_value v
FROM permissions
WHERE permission_item = $AppUI->user_id AND permission_grant_on = 'calendar'
";

$perms = array();
$res = db_exec( $sql );

// build the master permissions array
while ($row = db_fetch_assoc( $res )) {
	$perms[$row['g']][$row['i']] = $row['v'];
}

?>