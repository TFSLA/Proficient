<?
// retrieve any state parameters
if (isset( $_GET['where'] )) {
	$AppUI->setState( 'ContIdxWhere', $_GET['where'] );
}
if (isset( $_POST["search_string"] )){
	$AppUI->setState ('ContIdxWhere', "%".$_POST['search_string']);
				// Added the first % in order to find instrings also
	$additional_filter = "OR contact_first_name like '%{$_POST['search_string']}%'
	                      OR contact_last_name  like '%{$_POST['search_string']}%'";
}
$where = $AppUI->getState( 'ContIdxWhere' ) ? $AppUI->getState( 'ContIdxWhere' ) : '%';

$orderby = 'contact_order_by';
// assemble the sql statement
$sql = "SELECT contact_id, contact_order_by, ";

$sql.= "contact_first_name, contact_last_name, contact_phone
FROM contacts
WHERE (contact_order_by LIKE '$where%' $additional_filter)
	AND (contact_public=1
		OR (contact_public=0 AND contact_owner=$AppUI->user_id)
		OR contact_owner IS NULL OR contact_owner = 0
	)
ORDER BY $orderby
";

$res = db_exec( $sql );
$rn = db_num_rows( $res );

if($where==0)$search_string="";
?>
	<template><do type="prev" label="back"><prev/></do></template>
	<card title="PSA - Contacts">
	<p> 

	Search: <input name="search_string" title="search" value="<?=$search_string?>" size="8"/>
	<anchor>[<?php echo $AppUI->_('Go');?>]<go method="post" href="wap.php?sid=<?=$sid?>&amp;m=contacts">
	<postfield name="search_string" value="$(search_string)"/>
	</go></anchor>
	<br/> 

<?
// Pull First Letters
$let = ":";
$sql = "
SELECT DISTINCT UPPER(SUBSTRING($orderby,1,1)) as L
FROM contacts
WHERE contact_public=1
	OR (contact_public=0 AND contact_owner=$AppUI->user_id)
	OR contact_owner IS NULL OR contact_owner = 0
";
$arr = db_loadList( $sql );
foreach( $arr as $L )  $let .= $L['L'];
echo "<a href=\"wap.php?m=contacts&amp;where=0\">All</a> ";
for ($c=65; $c < 91; $c++) {
	$cu = chr( $c );
	if(strpos($let, "$cu") > 0)
		echo "<a href=\"wap.php?m=contacts&amp;where=$cu\">$cu</a> ";

}
echo "<br/>";
while ($row = db_fetch_assoc( $res )){
	echo "<a href=\"wap.php?sid=".$sid."&amp;m=contacts&amp;a=view&amp;contact_id=".$row["contact_id"]."\">".substr($row["contact_last_name"]." ".$row["contact_first_name"],0,12)."</a><br/>";
}

?>

</p>
</card>
