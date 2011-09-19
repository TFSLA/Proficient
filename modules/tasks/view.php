<?php /* $Id: view.php,v 1.5 2009-07-30 15:50:57 nnimis Exp $ */
global $debuguser;
$task_id = intval( dPgetParam( $_GET, "task_id", 0 ) );

				/* //	IMPLEMENTED ON CTask Class
				now we use getTaskDetails method of the CTask class
				$sql = "
				SELECT tasks.*,
					project_name, project_color_identifier,
					u1.user_username as username,
					ROUND(SUM(task_log_hours),2) as log_hours_worked
				FROM tasks
				LEFT JOIN users u1 ON u1.user_id = task_owner
				LEFT JOIN projects ON project_id = task_project
				LEFT JOIN task_log ON task_log_task=$task_id
				WHERE task_id = $task_id
				GROUP BY task_id
				";
				*/

// check if this record has dependancies to prevent deletion
$msg = '';
$obj = new CTask();
if (!$obj->load( $task_id ) && $task_id) {
	$AppUI->setMsg( 'Task' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

$hasChildren = false;
if ( $task_id )
{
	$c = $obj->getChildren();
	$hasChildren = count( $c ) > 0;
}

$perm= $obj->getTaskAccesses();
$canRead = $perm["read"];
$canEdit = $perm["edit"];
$accessDetail = $perm["detail"];
$accessTaskLog = $perm["log"];
$accessTaskExpense = $perm["expense"];
$accessValues = $perm["values"];

$canDelete = $obj->canDelete( $msg, $task_id );


if (!$canRead) {
	
           $AppUI->redirect( "m=public&a=access_denied" );
	//echo "CanRead=false";
}
				/* 
				//	IMPLEMENTED ON CTask Class
				//$obj = null;
				if (!db_loadObject( $sql, $obj, true )) {
					$AppUI->setMsg( 'Task' );
					$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
					$AppUI->redirect();
				} else {
					$AppUI->savePlace();
				}*/
$info=$obj->getTaskDetails();
$users = $info['users'];
$files = $info['files'];
$taskDep = $info['dependencies'];
$contacts = $info['contacts'];

$AppUI->savePlace();
$obj->canAccess ="";
/*
if (!$obj->canAccess( $AppUI->user_id )) {
	$AppUI->redirect( "m=public&a=access_denied" );
	//echo "CanAccess=false";
}
*/
// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'TaskLogVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'TaskLogVwTab' ) !== NULL ? $AppUI->getState( 'TaskLogVwTab' ) : 0;

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');
//Also view the time
$df .= " " . $AppUI->getPref('TIMEFORMAT');

$start_date = intval( $obj->task_start_date ) ? new CDate( $obj->task_start_date ) : null;
$end_date = intval( $obj->task_end_date ) ? new CDate( $obj->task_end_date ) : null;

//check permissions for the associated project
$canReadProject = !getDenyRead( 'projects', $obj->task_project);
$task_project = $obj->task_project;
$prj = new CProject();
if (!$prj->load($obj->task_project, false)){
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();

}
$prmProject=$prj->projectPermissions($AppUI->user_id);
$canReadProject = $canReadProject && isset($prmProject[1]) && $prmProject[1]!= PERM_DENY;
$canCreate = $prj->canAddTasks();
$canSupTimexp = $obj->canSuperviseTimexp(); 
				//	IMPLEMENTED ON CTask Class
				/*
				// get the users on this task
				$sql = "
				SELECT u.user_id, u.user_username, u.user_first_name,u.user_last_name, u.user_email
				FROM users u, user_tasks t
				WHERE t.task_id =$task_id AND
					t.user_id = u.user_id
				ORDER by u.user_last_name, u.user_first_name
				";
				$users = db_loadList( $sql );

				//Pull files on this task
				$sql = "
				SELECT file_id, file_name, file_size,file_type
				FROM files
				WHERE file_task = $task_id
					AND file_task <> 0
				ORDER by file_name
				";
				$files = db_loadList( $sql );
				*/
$durnTypes = dPgetSysVal( 'TaskDurationType' );

				/* Not Used
				//Pull expenses on this task
				$sql = "
				SELECT 	SUM(task_expense_cost) as task_total_expenses
				FROM task_expense
				WHERE task_expense_task = $task_id";
				$totalexpenses = db_loadList( $sql );
				*/


// setup the title block
$titleBlock = new CTitleBlock( 'View Task', 'tasks.gif', $m, "projects.index" );
$titleBlock->addCell(
);
if ($canCreate) {
	$titleBlock->addCell(
		'<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new task').'">', '',
		'<form action="?m=tasks&a=addedit&task_project='.$obj->task_project.'&task_parent=' . $task_id . '" method="post">', '</form>'
	);
}
$titleBlock->addCrumb( "?m=tasks", "tasks list" );
if ($canReadProject) {
	$titleBlock->addCrumb( "?m=projects&a=view&project_id=$obj->task_project", "view this project" );
}
if ($canEdit) {
	$titleBlock->addCrumb( "?m=tasks&a=addedit&task_id=$task_id", "edit this task" );
}

$canEditTimesheets = true;
if ($accessTaskExpense <> PERM_DENY && $canEditTimesheets){
		$titleBlock->addCrumb("?m=timexp&a=addtime&project_id=$obj->task_project&task_id=$task_id", 'new time' );
		//$titleBlock->addCrumb( "?m=tasks&a=addedit&task_id=$task_id3", 'New Times' );
		//$titleBlock->addCrumb( "?m=tasks&a=addedit&task_id=$task_id4", 'New Expense' );
		$titleBlock->addCrumb( "?m=timexp&a=addeditexpense&project_id=$obj->task_project&task_id=$task_id", 'new expense' );
}
if ($canEdit) {
	$titleBlock->addCrumbDelete( 'delete task', $canDelete, $msg );
}
$titleBlock->show();

?>

<script language="JavaScript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.task_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.task_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function updateTask() {
	var f = document.editFrm;
	if(f.task_log_description==null){
		if (f.task_expense_description.value.length < 1) {
			alert( "<?php echo $AppUI->_("The description can't be empty");?>" );
			f.task_expense_description.focus();
		} else {
			f.submit();
		}
	}
	else{
		if (f.task_log_description.value.length < 1) {
			alert( "<?php echo $AppUI->_("The description can't be empty");?>" );
			f.task_log_description.focus();
		} else if (isNaN( parseInt( f.task_manual_percent_complete.value+0 ) )) {
			alert( "<?php echo $AppUI->_('tasksPercent');?>" );
			f.task_manual_manual_percent_complete.focus();
		} else if(f.task_manual_percent_complete.value  < 0 || f.task_manual_percent_complete.value > 100) {
			alert( "<?php echo $AppUI->_('tasksPercentValue');?>" );
			f.task_manual_percent_complete.focus();
		} else {
			f.submit();
		}
	}
}
function delIt() 
{
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Task').'?';?>" )) 
	{
		<? if ( $hasChildren ) { ?>
		if ( confirm1( "<?=$AppUI->_("This task has children. Do you want to delete them too?")?>" ) )
		{
			document.frmDelete.deleteChildren.value = "true";
		}
		else
		{
			document.frmDelete.deleteChildren.value = "false";
		} <? } ?>		
		document.frmDelete.submit();
	}
}
</script>
<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<form name="frmDelete" action="./index.php?m=tasks" method="post">
	<input type="hidden" name="dosql" value="do_task_aed">
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="task_id" value="<?php echo $task_id;?>" />
	<input type="hidden" name="deleteChildren" value="false" />
	<input type="hidden" name="task_project" value="<?php echo $obj->task_project; ?>">
</form>

<tr valign="top">
<?php
if ($accessValues != PERM_DENY or $accessDetail != PERM_DENY){
?>
	<td valign=top width="50%">
		<table width="100%" cellspacing="1" cellpadding="2">
		<?php
		if ($accessDetail != PERM_DENY){
		?>
		<tr>
			<td nowrap="nowrap" colspan=2><strong><?php echo $AppUI->_('Details');?></strong></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project');?>:</td>
			<td style="background-color:#<?php echo $prj->project_color_identifier;?>">
				<font color="<?php echo bestColor( $prj->project_color_identifier ); ?>">
					<?php echo @$prj->project_name;?>
				</font>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task');?>:</td>
			<td class="hilite"><strong><?php echo @$obj->task_name;?></strong></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Id');?>:</td>
                        <? 
			      $vec_task[$obj->task_id]['task_id'] = $obj->task_id;
			      $vec_task[$obj->task_id]['task_wbs_level'] = $obj->task_wbs_level;
			      $vec_task[$obj->task_id]['task_wbs_number'] = $obj->task_wbs_number;
			      $vec_task[$obj->task_id]['task_parent'] = $obj->task_parent;
			      
			      $wbs = wbs($vec_task[$obj->task_id]);
			?>
			<td class="hilite"><strong><?php echo $wbs;?></strong></td>
		</tr>
		<?php if ( $obj->task_parent != $obj->task_id ) { 
			$obj_parent = new CTask();
			$obj_parent->load($obj->task_parent);
		?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task Parent');?>:</td>
			<td class="hilite"><a href="<?php echo "./index.php?m=tasks&a=view&task_id=" . @$obj_parent->task_id; ?>"><?php echo @$obj_parent->task_name;?></a></td>
		</tr>
		<?php } ?>
		<tr>
		    <?
		        $query_owner = "SELECT user_first_name, user_last_name FROM users WHERE user_id='".$obj->task_owner."' ";
		        $sql_owner = db_loadList($query_owner);
		        $owner_task = $sql_owner[0]['user_first_name']." ".$sql_owner[0]['user_last_name'];
		    ?>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Creator');?>:</td>
			<td class="hilite"> <?php echo @$owner_task;?></td>
		</tr>				<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority');?>:</td>
			<td class="hilite"> <?php echo strval($obj->task_priority)?></td>		
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Web Address');?>:</td>
			<td class="hilite" width="300"><a href="<?php echo @$obj->task_related_url;?>" target="task<?php echo $task_id;?>"><?php echo @$obj->task_related_url;?></a></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Milestone');?>:</td>
			<td class="hilite" width="300"><?php if($obj->task_milestone){echo $AppUI->_("Yes");}else{echo $AppUI->_("No");}?></td>
		</tr>
		<tr>
			<form method='POST' action="?m=tasks&a=view&task_id=<?php echo $obj->task_id; ?>" name='TaskComplete2'>
				<?php
					if ($_POST['task_complete_'] == '1') 
						$obj->task_complete_set('0'); //marcamos la tarea como incompleta
					elseif ($_POST['task_complete_'] == '0') 
						$obj->task_complete_set('1'); //marcamos la tarea como completa
					$obj->task_complete_get();
				?>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Task Complete');?>:</td>
				<td class="hilite" width="300">
				<input type='hidden' name='task_complete_' value='<?php echo $obj->task_complete; ?>'>
					<?php
					echo ($obj->task_complete ? $AppUI->_("Yes") : $AppUI->_("No") );
					if ($canEdit AND $obj->task_complete_possible_get()) { ?>
						(<a href='javascript:document.TaskComplete2.submit()'><?php echo ($obj->task_complete ? $AppUI->_("No") : $AppUI->_("Yes") ); ?></a>)
					<?php } ?>
				</td>
			</form>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Progress');?>:</td>
			<td class="hilite" width="300">
				<?php 	$obj->task_manual_percent_complete_select();
						echo $obj->task_manual_percent_complete."%"; ?>
			</td>
		</TR>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Time Worked');?>:</td>
			<td class="hilite" width="300"><?php 
			//echo (@$obj->task_hours_worked + @rtrim($obj->log_hours_worked, "0"));
			echo $obj->task_hours_worked.' '.$AppUI->_( $durnTypes[1] );
			//(@rtrim($obj->task_hours_worked, "0"));
			?></td>
		</tr>
		<?php
		}?>
		<tr>
			<td nowrap="nowrap" colspan=2><strong><?php echo $AppUI->_('Dates and Targets');?></strong></td>
		</tr>
<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date');?>:</td>
			<td class="hilite" width="300"><?php echo $start_date ? $start_date->format( $df ) : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Finish Date');?>:</td>
			<td class="hilite" width="300"><?php echo $end_date ? $end_date->format( $df ) : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap" valign="top"><?php echo $AppUI->_('Work');?>:</td>
			<td class="hilite" width="300"><?php echo $obj->task_work.' '.$AppUI->_( $durnTypes[1] );?></td>
		</tr>		
		<tr>
			<td align="right" nowrap="nowrap" valign="top"><?php echo $AppUI->_('Expected Duration');?>:</td>
			<td class="hilite" width="300"><?php echo $obj->task_duration.' '.$AppUI->_( $durnTypes[$obj->task_duration_type] );?></td>
		</tr>
		<?php 
		if ($accessValues != PERM_DENY){
		?>						
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget');?> <?php echo $dPconfig['currency_symbol'] ?>:</td>
			<td class="hilite" width="300"><?php echo $obj->task_target_budget;?></td>
		</tr>
		<?php } ?>
		<!--<tr>
			<td>Tareas que dependen de esta:</td>
			<td>
			<?
			$dep = $obj->getDependants();
			print_r( $dep );
			?>
		</tr>-->
		</table>
	</td>
<?php } ?>
	<td valign=top width="50%">
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td colspan="3"><strong><?php echo $AppUI->_('Assigned Users');?></strong></td>
		</tr>
		<tr>
			<td colspan="3">
			<?php
				$s = '';
				$s = count( $users ) == 0 ? "<tr><td bgcolor=#ffffff>".$AppUI->_('none')."</td></tr>" : '';
				foreach($users as $row) {
					$s .= '<tr>';
					$s .= '<td class="hilite">'.$row["user_first_name"].' '.$row["user_last_name"].'</td>';
					$s .= '<td class="hilite"><a href="mailto:'.$row["user_email"].'">'.$row["user_email"].'</a></td>';
					$s .= '</tr>';
				}
				echo '<table width="100%" cellspacing=1 bgcolor="black">'.$s.'</table>';
			?>
			</td>
		</tr>
		<tr>
			<td colspan="3"><strong><?php echo $AppUI->_('Dependencies');?></strong></td>
		</tr>
		<tr>
			<td colspan="3">
			<?php 
				$s = count( $taskDep ) == 0 ? "<tr><td bgcolor=#ffffff>".$AppUI->_('none')."</td></tr>" : '';
				foreach($taskDep as $key => $value) {
					$s .= '<tr><td class="hilite">';
					$s .= '<a href="./index.php?m=tasks&a=view&task_id='.$key.'">'.$value.'</a>';
					$s .= '</td></tr>';
				}
				echo '<table width="100%" cellspacing=1 bgcolor="black">'.$s.'</table>';
			?>
			</td>
		</tr>

		<tr>
			<td nowrap="nowrap" colspan="2"><strong><?php echo $AppUI->_('Description');?></strong></td>
		</tr>
		<tr>
			<td valign="top" height="75" colspan="2" class="hilite">
				<?php $newstr = str_replace( chr(10), "<br />", $obj->task_description);echo $newstr;?>
			</td>
		</tr>

<?
		if($obj->task_departments != "") {
			?>
		    <tr>
		    	<td><strong><?php echo $AppUI->_("Departments"); ?></strong></td>
		    </tr>
		    <tr>
		    	<td colspan='3' class="hilite">
		    		<?php
		    			$depts = db_loadHashList("select dept_id, dept_name, dept_phone
		    			                          from departments
		    			                          where dept_id in (".$obj->task_departments.")", "dept_id");
		    			foreach($depts as $dept_id => $dept_info){
		    				echo "<div>".$dept_info["dept_name"];
		    				if($dept_info["dept_phone"] != ""){
		    					echo "( ".$dept_info["dept_phone"]." )";
		    				}
		    				echo "</div>";
		    			}
		    		?>
		    	</td>
		    </tr>
	 		<?php
		}
		
			if(count($contacts)>0){
				?>
			    <tr>
			    	<td><strong><?php echo $AppUI->_("Contacts"); ?></strong></td>
			    </tr>
			    <tr>
			    	<td colspan='3' class="hilite">
			    		<?php
			    			echo "<table cellspacing='1' cellpadding='2' border='0' width='100%' bgcolor='black'>";
			    			echo "<tr><th>".$AppUI->_("Name")."</th><th>".$AppUI->_("Email")."</th><th>".$AppUI->_("Phone")."</th><th>".$AppUI->_("Department")."</th></tr>";
			    			foreach($contacts as $contact_id => $contact_data){
			    				echo "<tr>";
			    				echo "<td class='hilite'><a href='index.php?m=contacts&a=addedit&contact_id=$contact_id'>".$contact_data["contact_first_name"]." ".$contact_data["contact_last_name"]."</a></td>";
			    				echo "<td class='hilite'>".$contact_data["contact_email"]."</td>";
			    				echo "<td class='hilite'>".$contact_data["contact_phone"]."</td>";
			    				echo "<td class='hilite'>".$contact_data["contact_department"]."</td>";
			    				echo "</tr>";
			    			}
			    			echo "</table>";
			    		?>
			    	</td>
			    </tr>
		 		<?php
			}
		
		$custom_fields = dPgetSysVal("TaskCustomFields");
		if ( count($custom_fields) > 0 ){
			//We have custom fields, parse them!
			//Custom fields are stored in the sysval table under TaskCustomFields, the format is
			//key|serialized array of ("name", "type", "options", "selects")
			
			if ( $obj->task_custom != "" || !is_null($obj->task_custom))  {
				//Custom info previously saved, retrieve it
				$custom_field_previous_data = unserialize($obj->task_custom);
			}
			
			$output = '<tr><table cellspacing="1" cellpadding="2" >';
			foreach ( $custom_fields as $key => $array) {
				$output .= "<tr id='custom_tr_$key' >";
				$field_options = unserialize($array);
				$output .= "<td align='right' nowrap='nowrap' >". ($field_options["type"] == "label" ? "<b>". $field_options['name']. "</b>" : $field_options['name'] . ":") ."</td>";
				switch ( $field_options["type"]){
					case "text":
						$output .= "<td class='hilite' width='300'>" . dPformSafe(( isset($custom_field_previous_data[$key]) ? $custom_field_previous_data[$key] : "")) . "</td>";
						break;
					case "select":
						$optionarray = explode(",",$field_options["selects"]);
						$output .= "<td class='hilite' width='300'>". dPformSafe(( isset($custom_field_previous_data[$key]) ? $optionarray[$custom_field_previous_data[$key]] : "")) . "</td>";
						break;
					case "textarea":
						$output .=  "<td valign='top' class='hilite'>" . dPformSafe(( isset($custom_field_previous_data[$key]) ? $custom_field_previous_data[$key] : "")) . "</td>";
						break;
					case "checkbox":
						$optionarray = explode(",",$field_options["selects"]);
                                                $output .= "<td align='left'>";
                                                foreach ( $optionarray as $option ) {
                                                        if ( isset($custom_field_previous_data[$key]) && array_key_exists( $option, array_flip($custom_field_previous_data[$key]) ) ) {
                                                                $checked = "checked";
                                                        }
                                                        $output .=  "<input type='checkbox' value='$option' name='custom_" . $key ."[]' class='text' style='border:0' $checked " . $field_options["options"] . ">$option<br />";
                                                        $checked = "";
                                                }
                                                $output .= "</td>";
                                                break;
				}
				$output .= "</tr>";
			}
			$output .= "</table></tr>";
			echo $output;
		}
		$constrs = $obj->getConstraints();
		$constr_types = array ( 'ALAP'=>"As soon as possible", 'ASAP'=>"As late as possible", 'FNET'=>"Finish not earlier than", 'FNLT'=>"Finish not later than", 'MFO'=>"Must finish on", 'MSO'=>"Must start on", 'SNET'=>"Start no earlier than", 'SNLT'=>"Start no later than" );
		?>
			<tr>
				<td><strong><?php echo $AppUI->_('Constraints');?></strong></td>
				<td align="right" nowrap="nowrap">
					<a href="?m=tasks&a=vw_constraints&task_id=<?php echo $task_id;?>"><?php echo $AppUI->_('Edit constraints');?></a>
				</td>				
			</tr>
		    <tr>
				<td colspan="3">
				<?php 
				$s = count( $constrs ) == 0 ? "<tr><td bgcolor=#ffffff>".$AppUI->_('none')."</td></tr>" : '';
				$df = $AppUI->getPref('SHDATEFORMAT');
				$cons = new CTaskConstraint();
				foreach($constrs as $constr_id) 
				{										
					$cons->load( $constr_id["constraint_id"] );
					if ( !$obj->keepsConstraint( $cons ) )
					{
						$color = "red";
					}
					else
					{
						$color = "black";
					}
					$s .= '<tr><td class="hilite">';
					$s .= '<font color="'.$color.'">';
					$s .= $AppUI->_($constr_types[$cons->constraint_type]);
					if ( $cons->constraint_parameter )
					{
						$d = new CDate( $cons->constraint_parameter );	
						$s .= " ".$d->format( $df );
					}
					$s .= '</font>';
					$s .= '</td></tr>';
				}
				echo '<table width="100%" cellspacing=1 bgcolor="black">'.$s.'</table>';
				?>
				</td>			
			</tr>			
		</table>
	</td>	
</tr>
</table>

<?php
$query_string = "?m=tasks&a=view&task_id=$task_id";
$tabBox = new CTabBox( "?m=tasks&a=view&task_id=$task_id", "", $tab );

// checkeo de permisos en modulo timesheets postergado por el momento
// 2005/08/03
//$canEditTimesheets = !getDenyEdit("timexp");



/***********************************************************************************
IMPORTANTE: si se cambian las aletas revisar el archivo timexp/vw_daytimexp.php 
en la funcion showTaskLink donde se construye el link a cada aleta

************************************************************************************/

//if ( $obj->task_dynamic == 0 && $accessTaskLog <> PERM_DENY) {
if ( $accessTaskLog <> PERM_DENY && $canEditTimesheets) {

	// tabbed information boxes
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/vw_times", 'Times' );
	$tabBox_show = 1;
}

if ( count($obj->getChildren()) > 0 ) {
	// Has children
	// settings for tasks
	$f = 'children';
	$min_view = true;
	$tabBox_show = 1;
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/tasks", 'Child Tasks' );
}
if ($accessTaskExpense <> PERM_DENY && $canEditTimesheets){
	$tabBox_show = 1;
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/vw_expenses", 'Expenses' );
}

$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/vw_todos", 'To-do' );

// Me fijo si el usuario tiene permisos para ver webtracking
$canRead_webtracking = !getDenyRead( 'webtracking' );

if($canRead_webtracking)
{
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/vw_bugs", 'Webtracking' );
}

if (!getDenyRead( 'calendar' ))
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/calendar/vw_tasks_events", 'Events');

if (!($tab >= 0 && $tab < count($tabBox->tabs))){
	$tab=0;
	$tabBox->active=0;
}
if ( $tabBox_show == 1)	$tabBox->show();

//recargamos toda la pagina si se cambio el porcentage de avance
if(isset($_POST['task_complete'])) 
	$AppUI->redirect("");
?>
