<?php 

$task_id = intval(dPgetParam( $_GET, "task_id", 0 ));
$callback = dPgetParam( $_GET, "callback", "" );

$perms = CTask::getTaskAccesses($task_id);
$canRead = $perms["read"];	

if (!$canRead){
	$AppUI->setMsg( 'Task' );
	$AppUI->setMsg( "access denied", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

$obj = new CTask();
if (!$obj->load( $task_id ) && $task_id > 0) 
{
	$AppUI->setMsg( 'Task' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

echo "<b>".$AppUI->_("Selected Record")."</b><br/>";
echo "<b>".$AppUI->_("Task").":</b> ".$obj->task_name."<br/>";
 
echo "<br/>";
echo $AppUI->_("If this window doesn't close itself, click"). " ";
echo '<a href="javascript: setClose();">'.$AppUI->_("here")."</a> ";
echo $AppUI->_("to return.");
?>
<script language="javascript"><!--
	function setClose(){
		var key = "<?php echo $obj->task_id;?>";
		var val = "<?php echo $obj->task_name;?>";
		window.opener.<?php echo $callback;?>(key, val);
		window.close();
	}
	function loader(){
		window.setTimeout("setClose()", 2000);
	}
	loader();
//--></script>