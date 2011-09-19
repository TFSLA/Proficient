<?php /* TASKS $Id: vw_logs.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */
global $AppUI, $task_id, $df, $canEdit;
?>
<script language="JavaScript">
function delIt2(id) {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Task Log').'?';?>" )) {
		document.frmDelete2.task_log_id.value = id;
		document.frmDelete2.submit();
	}
}
</script>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="">
<form name="frmDelete2" action="./index.php?m=tasks" method="post">
	<input type="hidden" name="dosql" value="do_updatetask">
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_log_id" value="0" />
</form>

<tr class="tableHeaderGral">
	<th></th>
	<th><?php echo $AppUI->_('Date');?></th>
	<th width="100"><?php echo $AppUI->_('Summary');?></th>
	<th width="100"><?php echo $AppUI->_('User');?></th>
	<th width="100"><?php echo $AppUI->_('Hours');?></th>
	<th width="100"><?php echo $AppUI->_('Cost Code');?></th>
	<th width="100%"><?php echo $AppUI->_('Comments');?></th>
	<th></th>
</tr>
<?php
/* Added Tasks security patch

// Pull the task comments
$sql = "
SELECT task_log.*, user_username
FROM task_log
LEFT JOIN users ON user_id = task_log_creator
WHERE task_log_task = $task_id
ORDER BY task_log_date
";
$logs = db_loadList( $sql );*/

// get the task logs for the task when access is allowed
$obj = new CTask();
if (!$obj->load( $task_id ) && $task_id) {
	$AppUI->setMsg( 'Task' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

//verifico el permiso para expense
$perms=CTask::getTaskAccesses($task_id);
$canEdit = $perms["log"] == PERM_EDIT;

$logs = CTaskLog::getTaskLogs($task_id, $AppUI->user_id);

$s = '';
$hrs = 0;
foreach ($logs as $row) {
	$task_log_date = intval( $row['task_log_date'] ) ? new CDate( $row['task_log_date'] ) : null;

	$s .= '<tr bgcolor="white" valign="top">';
	$s .= "\n\t<td>";
	if ($canEdit &&  ($row["task_log_creator"] == $AppUI->user_id || $AppUI->user_type == 1) ) {
		$s .= "\n\t\t<a href=\"?m=tasks&a=view&task_id=$task_id&tab=1&task_log_id=".@$row['task_log_id']."\">"
			. "\n\t\t\t". dPshowImage( './images/icons/edit_small.jpg', 20, 20, '' )
			. "\n\t\t</a>";
	}
	$s .= "\n\t</td>";
	$s .= '<td nowrap="nowrap">'.($task_log_date ? $task_log_date->format( $df ) : '-').'</td>';
	$s .= '<td width="30%">'.@$row["task_log_name"].'</td>';
	$s .= '<td width="100">'.$row["user_username"].'</td>';
	$s .= '<td width="100" align="right">'.sprintf( "%.2f", $row["task_log_hours"] ) . '</td>';
	$s .= '<td width="100">'.$row["task_log_costcode"].'</td>';
	$s .= '<td>';

// dylan_cuthbert: auto-transation system in-progress, leave these lines
	$transbrk = "\n[translation]\n";
	$descrip = str_replace( "\n", "<br />", $row['task_log_description'] );
	$tranpos = strpos( $descrip, str_replace( "\n", "<br />", $transbrk ) );
	if ( $tranpos === false) $s .= $descrip;
	else
	{
		$descrip = substr( $descrip, 0, $tranpos );
		$tranpos = strpos( $row['task_log_description'], $transbrk );
		$transla = substr( $row['task_log_description'], $tranpos + strlen( $transbrk ) );
		$transla = trim( str_replace( "'", '"', $transla ) );
		$s .= $descrip."<div style='font-weight: bold; text-align: right'><a title='$transla' class='hilite'>[".$AppUI->_("translation")."]</a></div>";
	}
// end auto-translation code
			
	$s .= '</td>';
	$s .= "\n\t<td>";
	if ($canEdit  &&  ($row["task_log_creator"] == $AppUI->user_id || $AppUI->user_type == 1)) {
		$s .= "\n\t\t<a href=\"javascript:delIt2({$row['task_log_id']});\" title=\"".$AppUI->_('delete log')."\">"
			. "\n\t\t\t". dPshowImage( './images/icons/trash_small.gif', NULL, NULL, '' )
			. "\n\t\t</a>";
	}
	$s .= "\n\t</td>";
	$s .= '</tr>';
	$hrs += (float)$row["task_log_hours"];
}
$s .= '<tr bgcolor="white" valign="top">';
$s .= '<td colspan="3" align="right">' . $AppUI->_('Total Hours') . ' =</td>';
$s .= '<td align="right">' . sprintf( "%.2f", $hrs ) . '</td>';
$s .= '</tr>';
$s .= "<tr class=\"tableRowLineCell\"><td colspan=\"8\"></td></tr>";
echo $s;
?>
</table>
