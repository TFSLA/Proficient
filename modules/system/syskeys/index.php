<?php /* SYSKEYS $Id: index.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */
$AppUI->redirect( "m=public&a=access_denied" );

$AppUI->savePlace();
$canEdit = !getDenyEdit('system');

if (!$canEdit){
	$AppUI->redirect( "m=public&a=access_denied" );
}

// pull all the key types
$sql = "SELECT syskey_id,syskey_name FROM syskeys ORDER BY syskey_name";
$keys = arrayMerge( array( 0 => '- Select Type -' ), db_loadHashList( $sql ) );

$sql = "SELECT * FROM syskeys, sysvals WHERE sysval_key_id = syskey_id ORDER BY sysval_title";
$values = db_loadList( $sql );

$sysval_id = isset( $_GET['sysval_id'] ) ? $_GET['sysval_id'] : 0;

$titleBlock = new CTitleBlock( 'System Lookup Values', 'preferences.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system", "system admin" );
$titleBlock->show();
?>
<script language="javascript">
function delIt(id) {
	if (confirm( 'Are you sure you want to delete this?' )) {
		f = document.sysValFrm;
		f.del.value = 1;
		f.sysval_id.value = id;
		f.submit();
	}
}
function SubmitIt(){
    var f = document.sysValFrm;
    var error = false;
    if(f.sysval_key_id.value == 0 && !error){
        alert('<?php echo $AppUI->_('Please select Key Type'); ?>');
        error=true;
        f.sysval_key_id.focus();
    }
    if(trim(f.sysval_title.value).length == 0 && !error){
        alert('<?php echo $AppUI->_('Please enter a Name'); ?>');
        error=true;
        f.sysval_title.focus();
    }
    if(!error){
        f.submit();
    }

}
</script>

<table border="0" cellpadding="2" cellspacing="0" width="100%" class="">
<tr class="tableHeaderGral">
	<th>&nbsp;</th>
	<th><?php echo $AppUI->_('Key Type');?></th>
	<th><?php echo $AppUI->_('Title');?></th>
	<th colspan="2"><?php echo $AppUI->_('Values');?></th>
	<th>&nbsp;</th>
</tr>
<?php

function showRow($id=0, $key=0, $title='', $value='') {
	GLOBAL $canEdit, $sysval_id, $CR, $AppUI, $keys;
	$s = '<tr>'.$CR;
	if ($sysval_id == $id && $canEdit) {
	// edit form
		$s .= '<form name="sysValFrm" method="post" action="?m=system&u=syskeys">'.$CR;
		$s .= '<input type="hidden" name="dosql" value="syskeys/do_sysval_aed" />'.$CR;
		$s .= '<input type="hidden" name="del" value="0" />'.$CR;
		$s .= '<input type="hidden" name="sysval_id" value="'.$id.'" />'.$CR;

		$s .= '<td>&nbsp;</td>';
		$s .= '<td valign="top">'.arraySelect( $keys, 'sysval_key_id', 'size="1" class="text"', $key).'</td>';
		$s .= '<td valign="top"><input type="text" name="sysval_title" value="'.$title.'" class="text" /></td>';
		$s .= '<td valign="top"><textarea name="sysval_value" class="small" rows="5" cols="40">'.$value.'</textarea></td>';
		$s .= '<td><input type="button" value="'.$AppUI->_($id ? 'edit' : 'add').'" onclick="javascript:SubmitIt();" class="button" /></td>';
		$s .= '<td>&nbsp;</td>';
	} else {
		$s .= '<td width="12" valign="top">';
		if ($canEdit) {
			$s .= '<a href="?m=system&u=syskeys&sysval_id='.$id.'" title="'.$AppUI->_('edit').'">'
				. dPshowImage( './images/icons/edit_small.gif', 20, 20, '' )
				. '</a>';
			$s .= '</td>'.$CR;
		}
		$s .= '<td valign="top">'.$keys[$key].'</td>'.$CR;
		$s .= '<td valign="top">'.$title.'</td>'.$CR;
		$s .= '<td valign="top" colspan="2">'.$value.'</td>'.$CR;
		$s .= '<td valign="top" width="16">';
		if ($canEdit) {
			$s .= '<a href="#" onclick="return delIt('.$id.')" title="'.$AppUI->_('delete').'">'
				. dPshowImage( './images/icons/trash_small.gif', NULL, NULL, '' )
				. '</a>';
		}
		$s .= '</td>'.$CR;
	}
    if (!($sysval_id == $id && $canEdit)) {
        $s .= '<tr class="tableRowLineCell"><td colspan="6"></td></tr>';
    }
	$s .= '</tr>'.$CR;
	return $s;
}

// do the modules that are installed on the system
$s = '';
foreach ($values as $row) {
	echo showRow( $row['sysval_id'], $row['sysval_key_id'], $row['sysval_title'], $row['sysval_value'] );
}
// add in the new key row:
if ($sysval_id == 0) {
	echo showRow();
}
?>
</table>