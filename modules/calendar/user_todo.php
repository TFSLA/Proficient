<?php

IF ($_GET['FilterAuto']=='') {
	$_GET['AddProjectTodo']='off';
	$_GET['HideCompleted']='on';
	//echo "Entro!!!<br>";
	//print_r($_GET);
}
//print_r($_GET);
IF ($_GET['action']=='new')	utExecNewToDo ($_GET, $user_id);
IF ($_GET['action']=='delete') utExecDelTodo ($_GET, $user_id);
IF ($_GET['action']=='doedit') utExecEdit ($_GET, $user_id);
IF ($_GET['action']=='check') utExecCheck ($_GET, $user_id);
include ('./modules/todo/functions.php');
todojsp($AppUI);
?>
<table border="0" cellspacing="1" cellpadding="2" width="100%" class="motitle">
	<tr>
		<td colspan='2'>
			<img src='./modules/tasks/images/tasks.gif' alt='todo icon' border='0' height='15' width='15'>
			<b><span style="font-size:10pt"><?php echo $AppUI->_('Private To-Do'); ?></span></b>
		</td>
	</tr>
	<tr>
		<td>
			<table border='0' width='100%'>
				<tr>	
					<td align='left'>
					<form action='' name='form_filtro1' method='GET'>
					<input type='hidden' name='m' value='calendar'>
					<input type='hidden' name='FilterAuto' value='off'>
					<input type='hidden' name='HideCompleted' value='<?php echo $_GET['HideCompleted']; ?>'>
					<?php IF ($_GET['delegator_id']!='') echo "<input type='hidden' name='delegator_id' value='".$_GET['delegator_id']."'>"; ?>
					<?php IF ($_GET['AddProjectTodo']=='on') $check1='checked'; ?>
					<input type='checkbox' name='AddProjectTodo' onclick='submit()' <?php echo $check1;  ?>> <?php echo $AppUI->_('Add Projects ToDo'); ?>
					</td>
					</form>
					<td align='left'>
					<form action='' name='form_filtro2' method='GET'>
					<input type='hidden' name='m' value='calendar'>
					<input type='hidden' name='FilterAuto' value='off'>
					<input type='hidden' name='AddProjectTodo' value='<?php echo $_GET['AddProjectTodo']; ?>'>
					<?php IF ($_GET['delegator_id']!='') echo "<input type='hidden' name='delegator_id' value='".$_GET['delegator_id']."'>"; ?>
					<?php IF ($_GET['HideCompleted']=='on') $check2='checked'; ?>
					<input type='checkbox' name='HideCompleted' onclick='submit()' <?php echo $check2;  ?>> <?php echo $AppUI->_('Hide Completed'); ?>
					</td>
					</form>
				</tr>
			</table>
		</td>
	</tr>
</table>
<table border="0" cellspacing="1" cellpadding="2" width="100%" style="margin-width:4px;background-color:white">
<tr class="tableHeaderGral">
	<?php
		echo "<td></td>";
		echo "<td class='tableHeaderText' align='center'>";
		echo "<form action='index.php' name='ord_prio' method='GET'>";
		echo "<input type='hidden' name='FilterAuto' value='off'>";
		echo "<input type='hidden' name='AddProjectTodo' value='".$_GET['AddProjectTodo']."'>";
		echo "<input type='hidden' name='HideCompleted' value='".$_GET['HideCompleted']."'>";
		echo "<input type='hidden' name='m' value='calendar'>";
		echo "<input type='hidden' name='ord_prio' value='on'>";
		IF ($_GET['delegator_id']!='')	echo "<input type='hidden' name='delegator_id' value='".$_GET['delegator_id']."'>";
		echo "<input type='image' src='images/high_black.png'></td>";
		echo "</form>";
		
		echo "<td class='tableHeaderText' align='center'>"; 
		echo "<form action='index.php' name='ord_desc' method='GET'>";
		echo "<input type='hidden' name='FilterAuto' value='off'>";
		echo "<input type='hidden' name='AddProjectTodo' value='".$_GET['AddProjectTodo']."'>";
		echo "<input type='hidden' name='HideCompleted' value='".$_GET['HideCompleted']."'>";
		echo "<input type='hidden' name='m' value='calendar'>";
		echo "<input type='hidden' name='ord_desc' value='on'>";
		echo "<a onclick='document.ord_desc.submit()'>".$AppUI->_('Description')."</a></td>";
		IF ($_GET['delegator_id']!='')	echo "<input type='hidden' name='delegator_id' value='".$_GET['delegator_id']."'>";
		echo "</form>";
		
		
		echo "<td class='tableHeaderText' align='center'>";
		echo "<form name='ord_ddate' action='index.php' method='GET'>";
		echo "<input type='hidden' name='FilterAuto' value='off'>";
		echo "<input type='hidden' name='AddProjectTodo' value='".$_GET['AddProjectTodo']."'>";
		echo "<input type='hidden' name='HideCompleted' value='".$_GET['HideCompleted']."'>";
		echo "<input type='hidden' name='m' value='calendar'>";
		echo "<input type='hidden' name='ord_ddate' value='on'>";
		IF ($_GET['delegator_id']!='')	echo "<input type='hidden' name='delegator_id' value='".$_GET['delegator_id']."'>";
		echo "<a onclick='document.ord_ddate.submit()'>".$AppUI->_('DueDate')."</a></td>\n";
		echo "</form>";
	?>
	<td colspan='2'></td>
</tr>
<?php
$get=$_GET;
IF ($delegator_id==''OR ($delegator_id!='' AND $permiso!='REVIEWER')) utnewTodo($vec, $AppUI, $get, $delegator_id);
$rc=utquery($user_id, $get);
while ($vec=mysql_fetch_array($rc)){
	if ($get['id']==$vec['id'] AND $get['action']=='edit' AND $get['from']==$vec['from']) utnewTodo($vec, $AppUI, $get);
	else utTodoRow($vec, $AppUI, $get, $permiso, $delegator_id);
} 
?>
</table>
<?php

function utquery($user_id, $_GET){
	IF ($_GET['HideCompleted']=='on') $where="AND status=1";
	IF ($_GET['ord_prio']=='on') $order="priority ASC";
	ELSEIF ($_GET['ord_ddate']=='on') $order="due_date ASC";
	ELSEIF ($_GET['ord_desc']=='on') $order="description ASC"; 
	ELSE $order="priority ASC, due_date ASC";
	IF ($_GET['AddProjectTodo']=='on') {
		$union="
			SELECT
				id_todo AS id,
				p.project_short_name AS pname,
				pt.description,
				priority,
				IF (pt.due_date>'0000-00-00 00:00:00',pt.due_date,'3000-01-01 00:00:00') AS due_date,
				'proj_todo' as 'from',
				user_owner,
				status
			FROM
				project_todo AS pt
			INNER JOIN 
				projects AS p
	  				on (pt.project_id=p.project_id)
			WHERE
				user_assigned =".$user_id." $where
			UNION";
	}
	$sql="$union 
			SELECT
			id,
			'' AS pname,
			description,
			priority,
			IF (due_date>'0000-00-00 00:00:00',due_date,'3000-01-01 00:00:00') AS due_date,
			'user_todo' as 'from',
			'0' as user_owner,
			status
		FROM
			user_todo
		WHERE
			user=".$user_id." $where
		
		ORDER BY $order";
	//echo "<br><br>$sql<br><br>";
	return mysql_query($sql);
}

function utTodoRow($vec, $AppUI, $_GET, $permiso, $delegator_id){
	?>
	<tr class="tableRowLineCell">
		<td colspan="6"></td>
	</tr>
	<tr valign="top">
		<td width='1'>
				<form name="frmchecktodo_<?php echo $vec['id'] ?>" action="index.php" method="GET">
				<input type='hidden' name='m' value='calendar'>
				<input type='hidden' name='action' value='check'>
				<?php
				//filter_input_ToDo_ToDo($_GET);
				echo "<input type='hidden' name='todo_id' value=".$vec['id'].">";
				echo "<input type='hidden' name='from' value=".$vec['from'].">";
				echo "<input type='hidden' name='FilterAuto' value='off'>";
				IF ($_GET['delegator_id']!='') echo "<input type='hidden' name='delegator_id' value='".$_GET['delegator_id']."'>\n";
				echo "<input type='hidden' name='AddProjectTodo' value='".$_GET['AddProjectTodo']."'>";
				echo "<input type='hidden' name='HideCompleted' value='".$_GET['HideCompleted']."'>";
				IF ($vec['status']==1) $check="checked";
				ELSE $check=""; 
				echo "<input type='checkbox' onclick='submit()' name='status' $check $DISABLED>";
				?>
		</td>
		</form>
		<td width='1'>
			<?php
				switch ($vec['priority']) {
				case 1: $prio="./images/high.png"; break;
				case 3: $prio="./images/icons/low.gif"; break;
				default: $prio="./images/1x1.gif";
				}
				echo "<a name='todo_".$vec['project_id']."_".$vec['id']."'></a>";
			?>
			<img src="<?php echo $prio; ?>">
		</td>
		<td width="90%">
			<?php
				if ($vec['pname']!='') {
					strike ("<b>".$vec['pname'].": </b>", $vec['status']);
				}
				strike ($vec['description'], $vec['status']) ;
			?>
		</td>
		<td>
			<?php
				if ($vec['due_date']!='3000-01-01 00:00:00') $due_date=substr($vec['due_date'],8,2)."/".substr($vec['due_date'],5,2)."/".substr($vec['due_date'],0,4);
				else $due_date="N/A";
				strike ($due_date, $vec['status']);
			?>
		</td>
		<?php
			//echo "delegator_id = $delegator_id<br>";
			IF (
					($vec['from']=='user_todo' OR ($vec['from']=='proj_todo' AND $vec['user_owner']==$AppUI->user_id)) AND
					($delegator_id!='' OR $permiso=='EDITOR')
				){
				
				echo "<form action='index.php' method='GET'>";
				echo "<td width='1' align='right'><input type='image' src='./images/icons/edit_small.gif' alt='".$AppUI->_('Edit')."'>";
				echo "<input type='hidden' name='FilterAuto' value='off'>";
				echo "<input type='hidden' name='id' value=".$vec['id'].">";
				echo "<input type='hidden' name='m' value='calendar'>";
				echo "<input type='hidden' name='todo_assign' value=".$vec['user_assigned'].">";
				echo "<input type='hidden' name='from' value=".$vec['from'].">";
				echo "<input type='hidden' name='action' value='edit'>";
				IF ($_GET['delegator_id']!='')	echo "<input type='hidden' name='delegator_id' value='".$_GET['delegator_id']."'>";
				echo "<input type='hidden' name='ord_prio' value='".$_GET['ord_prio']."'>";
				echo "<input type='hidden' name='ord_desc' value='".$_GET['ord_desc']."'>";
				echo "<input type='hidden' name='ord_ddate' value='".$_GET['ord_ddate']."'>";
				echo "<input type='hidden' name='AddProjectTodo' value='".$_GET['AddProjectTodo']."'>";
				echo "<input type='hidden' name='HideCompleted' value='".$_GET['HideCompleted']."'>";
				echo "</td>";
				echo "</form>";
				
				echo "<form name='todo_name_".$vec['from']."_".$vec['id']."' action='index.php' method='GET'>";
				echo "<td width='1' align='left'><a href=\"javascript:ConfirmSend ('".$AppUI->_('Do you want to delete this todo?')." ".$vec['description']."', '".$vec['from']."_".$vec['id']."')\"><img src='./images/icons/trash20.gif' alt='".$AppUI->_('Delete')."' border='0'></a>";
				echo "<input type='hidden' name='FilterAuto' value='off'>";
				echo "<input type='hidden' name='id' value=".$vec['id'].">";
				echo "<input type='hidden' name='m' value='calendar'>";
				echo "<input type='hidden' name='todo_assign' value=".$vec['user_assigned'].">";
				echo "<input type='hidden' name='from' value=".$vec['from'].">\n";
				echo "<input type='hidden' name='action' value='delete'>\n";
				IF ($_GET['delegator_id']!='')	echo "<input type='hidden' name='delegator_id' value='".$_GET['delegator_id']."'>";
				echo "<input type='hidden' name='ord_prio' value='".$_GET['ord_prio']."'>";
				echo "<input type='hidden' name='ord_desc' value='".$_GET['ord_desc']."'>";
				echo "<input type='hidden' name='ord_ddate' value='".$_GET['ord_ddate']."'>";
				echo "<input type='hidden' name='AddProjectTodo' value='".$_GET['AddProjectTodo']."'>";
				echo "<input type='hidden' name='HideCompleted' value='".$_GET['HideCompleted']."'>";
				echo "</td>\n";
				echo "</form>";
				
			}
			else echo "<td></td><td></td>";
		?>
	</tr>
	<?php
}

function utnewTodo($vec, $AppUI, $_GET, $delegator_id='0'){
	IF ($_GET['action']=='edit'){
		$input="<input type='hidden' name='action' value='doedit'>";
		$input.= "<input type='hidden' name='todo_id' value='".$_GET['id']."'>";
		$input.= "<input type='hidden' name='from' value=".$vec['from'].">";
		IF ($vec['due_date']!='3000-01-01 00:00:00' AND $vec['due_date']>'1'){
			$due_date_from=substr($vec['due_date'],8,2)."/".substr($vec['due_date'],5,2)."/".substr($vec['due_date'],0,4);
			$due_date_hide=substr($vec['due_date'],0,4).substr($vec['due_date'],5,2).substr($vec['due_date'],8,2);		
		}
	}
	ELSE $input="<input type='hidden' name='action' value='new'>";
	//print_r($vec);
	?>
	<tr class="tableRowLineCell">
		<td colspan="6"></td>
	</tr>
	<tr valign="middle">
		<td colspan='2'>
			<form name="frmnewtodo_<?php echo $vec['id']; ?>" action="index.php" method="GET">
			<input type='hidden' name='FilterAuto' value='off'>
			<input type='hidden' name='m' value='calendar'>
			<input type='hidden' name='AddProjectTodo' value='<?php echo $_GET['AddProjectTodo']; ?>'>
			<input type='hidden' name='HideCompleted' value='<?php echo $_GET['HideCompleted']; ?>'>
			<?php
				IF ($_GET['delegator_id']!='')	echo "<input type='hidden' name='delegator_id' value='".$_GET['delegator_id']."'>";
				echo "<a name='todo_".$_GET['project_id']."_".$_GET['id']."'></a>";
				echo $input;
				//filter_input_ToDo($_GET);
				switch ($vec['priority']) {
					case 1: $high="selected"; break;
					case 2: $normal="selected"; break;
					case 3: $low="selected"; break;
					case 0:$normal="selected"; break;
				}
			?>
			<select name="todo_prio" class="text" size="1">
				<option value="0"></option>
				<option value="1" <?php echo $high; ?> ><?php echo $AppUI->_('High'); ?></option>
				<option value="2" <?php echo $normal; ?> ><?php echo $AppUI->_('Normal'); ?></option>
				<option value="3" <?php echo $low; ?> ><?php echo $AppUI->_('Low'); ?></option>
			</select>
		</td>
		<td><input type='text' name='todo_desc' size='20'  value='<?php echo $vec['description']; ?>'> </td>
		<td>
			<table width='1'>
				<tr>
					<td> 
						 <input name="date_<?php echo $vec['id']; ?>" value="<?php echo $due_date_from; ?>" class="text" size="10" type="text" DISABLED>
					</td>
					<td>
						<input type="hidden" name="timexp_date_<?php echo $vec['id']; ?>" value="<?php echo $due_date_hide; ?>">
		        		<input type="hidden" name="timexp_date_<?php echo $vec['id']; ?>_format" value="%d/%m/%Y">
						<a href="#" onClick="popCalendar_ToDo('date_<?php echo $vec['id']; ?>', 'frmnewtodo_<?php echo $vec['id']; ?>')"><img src="./images/calendar.gif" width="24" height="12" alt="Calendario" border="0"></a>
					</td>
				</tr>
			</table>
		</td>
		<td width='1' align='center' colspan='2'>
			<input type='button' class='button' value='<?php echo strtolower($AppUI->_('Send')); ?>' onclick="validate_todo(frmnewtodo_<?php echo $vec['id']; ?>.todo_desc.value,'<?php echo $vec['id']; ?>')">
		</td>
		</form>
	</tr>
	<?php
}

function utExecNewToDo ($_GET, $user_id){
	eval ("\$due_date=\$_GET['timexp_date_".$_GET['todo_id']."'];");
	$_GET['due_date']=substr($due_date,0,4)."-".substr($due_date,4,2)."-".substr($due_date,6,2)." 00:00:00";
	$sql= "INSERT INTO user_todo (
						description, 
						priority, 
						user, 
						date, 
						due_date) 
					VALUES ( 
						'".$_GET['todo_desc']."', 
						'".$_GET['todo_prio']."', 
						'".$user_id."', 
						NOW(),
						'".$_GET['due_date']."')";
	mysql_query($sql);
	//echo "<br>$sql";
	//$AppUI->setMsg( 'ToDo Inserted', UI_MSG_OK);
}

function utExecDelTodo ($_GET, $user_id){
	//echo "entro ac ";
	IF ($_GET['from']=='proj_todo') $sql="DELETE FROM project_todo WHERE id_todo='".$_GET['id']."' AND user_owner='".$user_id."'";
	IF ($_GET['from']=='user_todo')	$sql="DELETE FROM user_todo WHERE id='".$_GET['id']."' AND user='".$user_id."'";
	IF (mysql_query($sql)) echo "";
	ELSE "FAILED!!!";
}

function utExecEdit ($_GET, $user_id){
	//echo "entro aca <br>";
	//echo "<br>".$_GET['from']."<br>";
	eval ("\$due_date=\$_GET['timexp_date_".$_GET['todo_id']."'];");
	$_GET['due_date']=substr($due_date,0,4)."-".substr($due_date,4,2)."-".substr($due_date,6,2)." 00:00:00";
	IF ($_GET['from']=='proj_todo') $sql="UPDATE project_todo SET description='".$_GET['todo_desc']."', due_date='".$_GET['due_date']."', priority='".$_GET['todo_prio']."' WHERE id_todo='".$_GET['todo_id']."' AND user_owner='".$user_id."'";
	IF ($_GET['from']=='user_todo') $sql="UPDATE user_todo SET description='".$_GET['todo_desc']."', due_date='".$_GET['due_date']."', priority='".$_GET['todo_prio']."' WHERE id='".$_GET['todo_id']."' AND user='".$user_id."'";
	IF (mysql_query($sql)) echo "";
	ELSE "FAILED!!!";
}

function utExecCheck ($_GET, $user_id) {
	//echo "entro aca <br>";
	IF ($_GET['status']=='on') $status=1;
	ELSE $status=0;
	IF ($_GET['from']=='proj_todo') $sql="UPDATE project_todo SET status='$status' WHERE id_todo='".$_GET['todo_id']."' AND (user_owner='".$user_id."' OR user_assigned='".$user_id."')";
	IF ($_GET['from']=='user_todo') $sql="UPDATE user_todo SET status='$status' WHERE id='".$_GET['todo_id']."' AND user='".$user_id."'";
	IF (mysql_query($sql)) echo "";
	ELSE "FAILED!!!";
}
?>


<script language="javascript">
    function validate_todo(description,FrmName)
    {
    	var rta = true;
    	
    	if(description ==""){
    	     var msg = "<?=$AppUI->_('descripvalid');?>";
    	     rta = false;
    	}
    	
    	if(rta){ eval ('document.frmnewtodo_'+FrmName+'.submit()'); }else{  alert1(msg);  }
    }
</script>