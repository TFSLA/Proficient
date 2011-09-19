<?php /* COMPANIES $Id: vw_depts.php,v 1.5 2009-07-21 19:13:14 nnimis Exp $ */
##
##	Companies: View Projects sub-table
##
GLOBAL $AppUI, $company_id, $canEdit, $canDelete;

$AppUI->savePlace();

$sql = "
SELECT departments.*, (SELECT COUNT(*) FROM users WHERE user_department = departments.dept_id AND user_type <> 5) AS dept_users
FROM departments
WHERE dept_company = $company_id
GROUP BY dept_id
ORDER BY dept_parent, dept_name
";

/*
SELECT departments.*, COUNT(user_department) dept_users
FROM departments
LEFT JOIN users ON user_department = dept_id
WHERE dept_company = $company_id
AND user_type <> 5
GROUP BY dept_id
ORDER BY dept_parent,dept_name
*/
//echo $sql;


function showchild( &$a, $level=0 ) {
	global $AppUI,$canDelete,$canEdit;
	$s = '';

	$s .= '<td width="50">';
	if ($canEdit) {
	$s .= '<a href="./index.php?m=departments&a=addedit&dept_id='.$a["dept_id"].'" title="'.$AppUI->_('edit').'">';
	$s .= dPshowImage( './images/icons/edit_small.gif', 20, 20, '' );
	}

    if ($canDelete) {
	$s .= '<a href="javascript:delItDept('.$a["dept_id"].')" title="'.$AppUI->_('delete').'">';
	$s .= "<img src='./images/icons/trash_small.gif' border='0' />";
	}
    

	$s .= '</td>';
	$s .= '<td>';


	for ($y=0; $y < $level; $y++) {
		if ($y+1 == $level) {
			$s .= '<img src="./images/corner-dots.gif" width="16" height="12" border="0">';
		} else {
			$s .= '<img src="./images/shim.gif" width="16" height="12" border="0">';
		}
	}
	
	$sql="SELECT concat( user_first_name,' ', user_last_name ) name from users WHERE user_id = ".$a["dept_owner"];
	//echo "$sql";
	$owner_name = @db_loadResult( $sql );

	$s .= '<a href="./index.php?m=departments&a=view&dept_id='.$a["dept_id"].'">'.$a["dept_name"].'</a>';
	$s .= '</td>';
	$s .= '<td align="center">'.(($owner_name) ? $owner_name : '').'</td>';
	$s .= '<td align="center">'.($a["dept_users"] ? $a["dept_users"] : '0').'</td>';

	echo "<tr>$s</tr>";
    echo "<tr class=\"tableRowLineCell\"><td colspan=\"4\"></td></tr>"; 
}

function findchild( &$tarr, $parent, $level=0 ){
	$level = $level+1;
	$n = count( $tarr );
	for ($x=0; $x < $n; $x++) {
		if($tarr[$x]["dept_parent"] == $parent && $tarr[$x]["dept_parent"] != $tarr[$x]["dept_id"]){
			showchild( $tarr[$x], $level );
			findchild( $tarr, $tarr[$x]["dept_id"], $level);
		}
	}
}


$s = '<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">';
$s .= '<form name="frmDelete1" action="./index.php?m=departments" method="post">
	<input type="hidden" name="dosql" value="do_dept_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="dept_id" value="" />
</form>';

//$s .= '<tr class="tableHeaderGral">';
$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
    $s .= '<tr class="tableHeaderGral">';
    $s .= '<th width="50">&nbsp;</th>';
	$s .= '<th >'.$AppUI->_( 'Name' ).'</th>';
	$s .= '<th align="center">'.$AppUI->_( 'Owner' ).'</th>';
	$s .= '<th align="center">'.$AppUI->_( 'Users' ).'</th>';
} else {
	$s .= $AppUI->_('No data available');
}

$s .= '</tr>';
echo $s;

foreach ($rows as $row) {
	if ($row["dept_parent"] == 0) {
		showchild( $row );
		findchild( $rows, $row["dept_id"] );
	}
}

echo '<td colspan="4" nowrap="nowrap" rowspan="99" align="right" valign="top" style="background-color:#ffffff">';
if ($canEdit) {
	echo '<input type="button" class="buttonbig" value="'.$AppUI->_( 'new department' ).'" onClick="javascript:window.location=\'./index.php?m=departments&a=addedit&company_id='.$company_id.'\';">';
}         
echo '</td>';

echo '</table>';
?>

<script language="javascript" >
function delItDept(val) {
	if (confirm( "<?php echo $AppUI->_('delDept');?>" )) {
		document.frmDelete1.dept_id.value = val;
		document.frmDelete1.submit();
	}
}
</script>
