<?php
GLOBAL $AppUI, $company_id, $canEdit, $canDelete;

$sql = "
SELECT *
FROM hhrr_functional_area
WHERE area_company = $company_id
ORDER BY area_parent,area_name;";

function showchild( &$a, $level=0 ) {
	global $AppUI,$canDelete,$canEdit;
	$s = '';

	$s .= '<td width="50">';
	if ($canEdit) {
	$s .= '<a href="./index.php?m=functionalArea&a=addedit&id='.$a["id"].'" title="'.$AppUI->_('edit').'">';
	$s .= dPshowImage( './images/icons/edit_small.gif', 20, 20, '' );
	}

    if ($canDelete) {
	$s .= '<a href="javascript:delItArea('.$a["id"].')" title="'.$AppUI->_('delete').'">';
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
	

	$s .= '<a href="./index.php?m=functionalArea&a=view&id='.$a["id"].'">'.$a["area_name"].'</a>';
	$s .= '</td>';
	
	echo "<tr>$s</tr>";
    echo "<tr class=\"tableRowLineCell\"><td colspan=\"4\"></td></tr>"; 
}

function findchild( &$tarr, $parent, $level=0 ){
	$level = $level+1;
	$n = count( $tarr );
	for ($x=0; $x < $n; $x++) {
		if($tarr[$x]["area_parent"] == $parent && $tarr[$x]["area_parent"] != $tarr[$x]["id"]){
			showchild( $tarr[$x], $level );
			findchild( $tarr, $tarr[$x]["id"], $level);
		}
	}
}


$s = '<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">';
$s .= '<form name="frmDelete1" action="./index.php?m=functionalArea" method="post">
	<input type="hidden" name="dosql" value="do_area_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="id" value="" />
</form>';

//$s .= '<tr class="tableHeaderGral">';
$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
    $s .= '<tr class="tableHeaderGral">';
    $s .= '<th width="50">&nbsp;</th>';
	$s .= '<th >'.$AppUI->_( 'Name' ).'</th>';
} else {
	$s .= $AppUI->_('No data available');
}

$s .= '</tr>';
echo $s;

foreach ($rows as $row) {
	if ($row["area_parent"] == 0) {
		showchild( $row );
		findchild( $rows, $row["id"] );
	}
}

echo '<td colspan="4" nowrap="nowrap" rowspan="99" align="right" valign="top" style="background-color:#ffffff">';
if ($canEdit) {
	echo '<input type="button" class=button value="'.$AppUI->_( 'new area' ).'" onClick="javascript:window.location=\'./index.php?m=functionalArea&a=addedit&company_id='.$company_id.'\';">';
}         
echo '</td>';

echo '</table>';

?>

<script language="javascript" >
function delItArea(val) {
	if (confirm( "<?php echo $AppUI->_('delArea');?>" )) {
		document.frmDelete1.id.value = val;
		document.frmDelete1.submit();
	}
}
</script>
