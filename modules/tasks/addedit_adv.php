<?php /* TASKS $Id: addedit_adv.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */
/**
* Tasks :: Add/Edit - Tab Advance/Avanzado
*/

?>
<script language="javascript">

  function changedconstrainttype(){
	 var f = document.editFrm;
	 var mandatory_constraint_dates = "<?php echo $mandatory_constraint_dates?>";
	 var ctype = f.task_constraint_type.options[f.task_constraint_type.selectedIndex].value;

	 if (mandatory_constraint_dates.indexOf(ctype) > -1 )
		f.constraint_date.disabled = false;
	 else		
		f.constraint_date.disabled = true;
  }
  
  var task_data = new Array();

  function cache_task_data(){
		var form = document.editFrm;
		task_data["task_duration"] = form.task_duration.value;
		task_data["task_duration_type"] = form.task_duration_type.selectedIndex;
		task_data["task_work"] = form.task_work.value;
		task_data["end_date"] = form.end_date.value;
		task_data["task_end_date"] = form.task_end_date.value;
		task_data["end_hour"] = form.end_hour.selectedIndex;
		task_data["end_minute"] = form.end_minute.selectedIndex;

	    <?php if ( $isAMPMTimeFormat ) {	?>
		task_data["end_hour_ampm"] = form.end_hour_ampm.value;
	    <?php }	?>	

		task_data["users"] = new Array();
		task_data["users"] = assignedid;	
  }

  function load_task_data(){
	var form = document.editFrm;
	form.task_duration.value = task_data["task_duration"];
	form.task_duration_type.selectedIndex = task_data["task_duration_type"];
	form.task_work.value = task_data["task_work"];
	form.end_date.value = task_data["end_date"];
	form.task_end_date.value = task_data["task_end_date"];
	form.end_hour.selectedIndex = task_data["end_hour"];
	form.end_minute.selectedIndex = task_data["end_minute"];

    <?php if ( $isAMPMTimeFormat ) {	?>	
	form.end_hour_ampm.value = task_data["end_hour_ampm"];
    <?php }	?>		

	assignedid = new Array();
	assignedid = task_data["users"];	
	refreshfilters();
	selectChange( null );	
  }


  function swapMilestone(){
	 var form = document.editFrm;
	 var is_milestone = form.task_milestone.checked;

	 if ( is_milestone ){
		cache_task_data();
		assignedid = new Array();
		changeDate(form.start_date);
		form.task_work.value = 0;
		refreshfilters();
		selectChange( null );
		
	 }else{
		load_task_data();
		changeDate(form.start_date);	
		execute_remote_script("changed_resources");
	 }

		form.task_duration.disabled = is_milestone; 
		form.task_duration_type.disabled = is_milestone;
		form.task_work.disabled = is_milestone;
		form.end_date.disabled = is_milestone; 
		form.task_end_date.disabled = is_milestone; 
		form.end_hour.disabled = is_milestone; 
		form.end_minute.disabled = is_milestone; 	
	
   //if (this.checked) changeDate(this.form.start_date);

 }

</script>

<input type="hidden" name="task_dynamic" value="1">
<table cellspacing="2" cellpadding="0" border="0" width="98%" class="tableForm_bg">
  <col width="15%"><col width="35%"><col width="15%"><col width="35%">
  <tr>
	<td align="right" nowrap="nowrap" style="font-weight: bold;">
	  <?php echo $AppUI->_( 'Milestone' );?>?
	</td>
	<td >
		<input type="checkbox" value="1" name="task_milestone" <?php if($obj->task_milestone){?>checked<?php }?> onclick="swapMilestone();" />
	</td>		
  </tr>
  <tr>
    <td colspan="97">
	   <hr noshade="noshade" size="1">
	</td>
  </tr>
  <tr>
	<td  align="right" nowrap="nowrap" style="font-weight: bold;">
	   <?php echo $AppUI->_( 'Task Type' );?>:
	</td>
	<td nowrap="nowrap" align="left">
		<?php 
	     if (@$obj->task_type=="")
		 {
         @$obj->task_type = 1;
		 }
		echo arraySelect( $task_types, 'task_type', 'class="text" size="1"', @$obj->task_type, true ); 
		?>
	</td>
	<td align="right" nowrap="nowrap" style="font-weight: bold;"><?php echo $AppUI->_( 'Effort Driven' );?>:</td>
	<td nowrap="nowrap" align="left">
		<input type="checkbox" name="task_effort_driven" value="1" <?php if($obj->task_effort_driven!="0") echo "checked"?> />
	</td>
  </tr>	
  <tr>
	<td  align="right" nowrap="nowrap" style="font-weight: bold;">
	  <?php echo $AppUI->_( 'Constraint Type' );?>:
	</td>
	<td nowrap="nowrap" align="left">
		<?php echo arraySelect( $task_constraints, 'task_constraint_type', 'class="text" size="1" onchange="javascript: changedconstrainttype(this);"', @$obj->task_constraint_type, true ); ?>
	</td>
	<td  align="right" nowrap="nowrap" style="font-weight: bold;">
	  <?php echo $AppUI->_( 'Constraint Date' );?>:
	</td>
	<td nowrap="nowrap" align="left">
	    <input type="hidden" name="task_constraint_date_format" value="<?php echo $df;?>">
		<input type="hidden" name="task_constraint_date" value="<?php echo $constraint_date ? $constraint_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="constraint_date" value="<?php echo $constraint_date ? $constraint_date->format( $df ) : '';?>" size="10" class="text"  onblur="changeDate(this);" />
		<a href="#" onClick="popConstraint();">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
		</a>	

	</td>
  </tr>

  <tr>
    <td colspan="97">
	  <hr noshade="noshade" size="1">
	</td>
  </tr>		
  <tr>
	<td valign="top" align="right" nowrap="nowrap" style="font-weight: bold;">
	    
	</td>
	<td colspan="2">
		
	</td>
	<td valign="top">
	     <?php  
		         if($department_selection_list != ""){
					echo $AppUI->_("Departments"); 
					echo "<br />";
					echo $department_selection_list; 
					echo "<hr />"; 
				}
				
				$sql = "select c.company_name
				        from companies as c, tasks as t, projects as p
				        where t.task_id = $task_id
				              and t.task_project = p.project_id
				              and p.project_company = company_id";
				$company_name = db_loadResult($sql);
				
				if($department_selection_list != "" || !is_null($company_name) ) {
					echo "<input type='button' class='button' value='".$AppUI->_("Contacts")."' onclick='javascript:popContacts();' title=\"".$AppUI->_("Select contacts...")."\" />";
				}
			?>
	</td>	
  </tr>
  <tr>
	<td colspan="4">
      <?php
	    $custom_fields = dPgetSysVal("TaskCustomFields");
		if ( count($custom_fields) > 0 ){
		    if ( $obj->task_custom != "" || !is_null($obj->task_custom))  {
			$custom_field_previous_data = unserialize($obj->task_custom);
		    }
		
		$output = '<table cellspacing="0" cellpadding="2" border="0">';
		foreach ( $custom_fields as $key => $array) {
			$output .= "<tr colspan='3' valign='top' id='custom_tr_$key' >";
			$field_options = unserialize($array);
			$output .= "<td align='right' nowrap='nowrap' >". ($field_options["type"] == "label" ? "<strong>". $field_options['name']. "</strong>" : $field_options['name']) . ":" ."</td>";
			switch ( $field_options["type"]){
				case "text":
					$output .= "<td align='left'><input type='text' name='custom_$key' class='text'" . $field_options["options"] . "value='" . ( isset($custom_field_previous_data[$key]) ? $custom_field_previous_data[$key] : "") . "' /></td>";
					break;
				case "select":
					$output .= "<td align='left'>". arraySelect(explode(",",$field_options["selects"]), "custom_$key", 'size="1" class="text" ' . $field_options["options"] ,( isset($custom_field_previous_data[$key]) ? $custom_field_previous_data[$key] : "")) . "</td>";
					break;
				case "textarea":
					$output .=  "<td align='left'><textarea name='custom_$key' class='textarea'" . $field_options["options"] . ">" . ( isset($custom_field_previous_data[$key]) ? $custom_field_previous_data[$key] : "") . "</textarea></td>";
					break;
				case "checkbox":
					$options_array = explode(",",$field_options["selects"]);
					$output .= "<td align='left'>";
					foreach ( $options_array as $option ) {
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
		$output .= "</table>";
		echo $output;
	}else{
		echo "&nbsp;";
	}
?>	
	</td>
</tr>
</table>
