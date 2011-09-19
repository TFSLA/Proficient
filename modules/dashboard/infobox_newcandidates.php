<?
global $AppUI;

$uploads_dir = $AppUI->getConfig('hhrr_uploads_dir');

$sql = "
SELECT hhrr.*
FROM hhrr
WHERE inputdate = NOW()
ORDER BY lastname, firstname
";
$rows = db_loadList( $sql, NULL );

$s = '<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">';

$rows = db_loadList( $sql, NULL );

if (count( $rows)) {
    $s .= '<tr class="tableHeaderGral">';
    $s .= '<th>&nbsp;</th>';
	$s .= '<th>'.$AppUI->_( 'Name' ).'</th>';
	$s .= '<th>'.$AppUI->_( 'Skills' ).'</th>';
	$s .= '<th>'.$AppUI->_( 'CV' ).'</th>';
} else {
    $s .= '<tr>';
    $s .= $AppUI->_('No data available');
}
$s .= '</tr>';
echo $s;

foreach ($rows as $a) {

	$s = '';
	$s .= '<td>';
	$s .= '<a href="./index.php?m=hhrr&a=addedit&id='.$a["id"].'">';
	$s .= '<img src="./images/icons/edit_small.gif" alt="'.$AppUI->_('Edit HHRR').'" border="0" width="20" height="20"></a>';
	//$s .= ' <a href="javascript:delHhrr('. $a["id"] .', \''. $a["lastname"] . ", " . $a["firstname"] . '\')"><img src="images/icons/trash.gif" width="20" height="20" border="0" alt="'.$AppUI->_('delete').'"></a>';
	$s .= '</td>';
	$s .= '<td>';
	$s .= '<a href="./index.php?m=hhrr&a=addedit&id='.$a["id"].'">'.$a["lastname"].", ".$a["firstname"].'</a>';
	$s .= '</td>';
	$s .= '<td>';
	$s .= "<a href='./index.php?m=hhrr&a=viewskills&id=".$a["id"]."'>".$AppUI->_( 'View' )."</a>" ;
	$s .= '</td>';
	$s .= '<td>';
	if($a["resume"]!="" && $a["resume"]!="ninguna")
		$s .= "<a href='$uploads_dir/".$a["id"]."/".rawurlencode($a["resume"])."'>".$AppUI->_( 'View' )."</a>" ;
	$s .= '</td>';

	echo "<tr>$s</tr>";
    echo "<tr class=\"tableRowLineCell\"><td colspan=\"4\"></td></tr>";
}
echo '</table>';


?>
