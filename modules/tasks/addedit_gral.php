<?php /* TASKS $Id: addedit_gral.php,v 1.5 2009-06-23 00:00:09 ctobares Exp $ */
/**
* Tasks :: Add/Edit - Tab General
*/

$durnTypes = dPgetSysVal( 'TaskDurationType' );


// Formato de las fechas
$df = $AppUI->getPref('SHDATEFORMAT');

//ingreso fechas manual
$objManualDate = new CDate();

$start_date = intval( $obj->task_start_date ) ? new CDate( $obj->task_start_date ) : new CDate();
$end_date = intval( $obj->task_end_date ) ? new CDate( $obj->task_end_date ) : new CDate();

if($task_id !="")
{
$constraint_date = intval( $obj->task_constraint_date ) ? new CDate( $obj->task_constraint_date ) : null;
}

$hours = array();

for ( $current = 0; $current < 25; $current++ ) {
	if ( $current < 10 ) {
		$current_key = "0" . $current;
	} else {
		$current_key = $current;
	}

	$hours[$current_key] = $current;
}

$minutes = array();
$inc = 15;

$minutes["00"] = "00";
for ( $current = 0 + $inc; $current < 60; $current += $inc ) {
	$minutes[$current] = $current;
}

if(!isset($obj->task_target_budget_hhrr) || $obj->task_target_budget_hhrr=="")
{
$obj->task_target_budget_hhrr = 0;
}

?>

<script language="javascript">

  function popContacts() {
	window.open('./index.php?m=public&a=contact_selector&suppressLogo=1&dialog=1&call_back=setContacts&company_id=<?php echo $company_id; ?>&selected_contacts_id='+selected_contacts_id, 'contacts','left=50,top=50,height=250,width=400,resizable,scrollbars=yes');
  }

  function popCalendar( field ){
	var form = document.editFrm;
	var is_milestone = form.task_milestone.checked;

	if (! is_milestone ||
		is_milestone &&  field.indexOf("end") == -1){
		calendarField = field;
		idate = eval( 'document.editFrm.task_' + field + '.value' );
		window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
		}
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
	var changed = calendarField == "start_date" ? "start" : "end";
	swap_changed_fields(fld_date);
  }

  function refreshfilters(){
		var f = document.editFrm;
		var comand;
		var element;
		var text;
		var uslist="";
		var utlist="";

		 var content = "";

	  for(i=0;i<assignedid.length;i++)
	  {
		if(assignedid[i]!=-1)
		{
		  uslist = uslist + assignedid[i] + ",";
		  utlist = utlist + units[assignedid[i]] + ",";
		}
	  }

	  uslist = uslist.substr(0,uslist.length-1);
	  utlist = utlist.substr(0,utlist.length-1);
	  f.hassign.value=uslist;
	  f.hunits.value=utlist;


	  content = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" bgcolor=\"#ffffff\">" ;

	  for(i=0;i<assignedid.length;i++)
	  {
		if(assignedid[i]!=-1)
		{
		  for(x=0;x<usersid.length;x++)
		  {
			if(assignedid[i]==usersid[x])
			{
				var coloruser = "";
				if (units[assignedid[i]] > 100)
					coloruser = ' style="color: #FF0000;"';

				content = content + "<tr" + coloruser + ">";
				content+= "<input type='hidden' name='user["+assignedid[i]+"]' value='"+assignedid[i]+"' />";
				content = content + "<td width=\"380px\">" + users[assignedid[i]]+ "</td>";

			         var owner = <?=$AppUI->user_id?>;

			         if(owner != assignedid[i]){
				content = content + "<td width=\"140px\" nowrap=\"nowrap\" align=\"right\"><input type='text' class='text' name='cost["+assignedid[i]+"]' value='"+cost[assignedid[i]]+"' size='3' disabled/></td><td>&nbsp;&nbsp;<a  href='javascript:  //' onclick='javascript: openUser(" + assignedid[i] + ")'><img src=\"images/icons/edit_small.gif\" width=\"20\" height=\"20\" border=\"0\" alt=\"<?=$AppUI->_('edit')?>\"  /></a>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
			         }else{
			         	content = content + "<td width=\"140px\" nowrap=\"nowrap\" align=\"right\"><input type='text' class='text' name='cost["+assignedid[i]+"]' value='"+cost[assignedid[i]]+"' size='3' disabled/></td><td>&nbsp;&nbsp;<img src=\"images/icons/edit_small.gif\" width=\"20\" height=\"20\" border=\"0\" alt=\"<?=$AppUI->_('edit')?>\"  />&nbsp;&nbsp;&nbsp;&nbsp;</td>";
			         }

				content = content + "<td width=\"100px\" nowrap=\"nowrap\" align=\"center\"><input type='text' class='text'  name='units["+assignedid[i]+"]' value='"+units[assignedid[i]]+"' size='3' onBlur=\"changeunits(this);\" "+coloruser+"/>%</td>";
				content = content + "<td width=\"50px\" align=\"center\"><a href='javascript:  //' onclick='javascript: removeuser(" + assignedid[i] + ")' alt=\"<?=$AppUI->_('delete')?>\"><img src=\"images/icons/trash_small.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"<?=$AppUI->_('delete')?>\"  /></a></td>";

				content = content + "</tr>";
			}
		  }
		}
	  }


	  content = content + "</table>";
	<? if ($canManageRoles){ ?>
		document.getElementById("taskusers").innerHTML = content;
	<?php } ?>

  }

function openUser(id_user){

	window.open('./index.php?m=admin&a=addedituser_admin&user_id='+id_user+'&dialog=1&suppressLogo=1&from=tasks', 'user_edit', 'top=0,left=0,width=1015, height=520, scrollbars=yes, status=no' );
}

function set_cost_per_hour(costo,user_edit){
	var f = document.editFrm;
	cost[user_edit] = costo ;

	var suma = 0;
	var parcial = 0;

	for(i=0;i<assignedid.length;i++)
	{
	     parcial = (f.task_work.value*units[assignedid[i]]*cost[assignedid[i]])/100;
	     suma = suma + parcial;
	}

	f.target_budget_hhrr.value = suma;
	f.task_target_budget_hhrr.value = suma;

	refreshfilters();
}

  function changeDate(field){

	 if(isValidDate(field.name)){
		//si posee valor
		if (trim(field.value)!=""){
				swap_changed_fields(eval( 'document.editFrm.task_' + field.name));

		}
	 }
  }


  function checkDates(changed){
	var form = document.editFrm;
	var st = '';
	var et = '';
	st = form.task_start_date.value;
	st += form.start_hour.options[form.start_hour.selectedIndex].value;
	st += form.start_minute.options[form.start_minute.selectedIndex].value;
	et = form.task_end_date.value;
	et += form.end_hour.options[form.end_hour.selectedIndex].value;
	et += form.end_minute.options[form.end_minute.selectedIndex].value;

	if (changed=="start"){
		if (st > et){
			form.task_end_date.value = form.task_start_date.value;
			form.end_date.value = form.start_date.value;
			form.end_hour.selectedIndex = form.start_hour.selectedIndex;
			form.end_minute.selectedIndex = form.start_minute.selectedIndex;
		}
	}else{
		if (st > et){
			alert( "<?php echo $AppUI->_('taskValidEndDate');?>" );
		}
	}
  }

  function changeWork(){
	var f = document.editFrm;

	if(f.task_work.value =="")
	{
	    f.task_work.value = '0';
	}

	is_ok = valida_numero(f.task_work, 'task_work');

	if(!is_ok){
		f.task_work.focus();
		alert1('<?=$AppUI->_('NaN_task_work')?>');
	}

	if(is_ok){
		if(curtask.work != f.task_work.value || curtask.duration_type !=f.task_duration_type.value){
			curtask.work = f.task_work.value;
                                    curtask.duration_type =f.task_duration_type.value;
			execute_remote_script("changed_work");
		}
	}
  }

  function adduser()
  {
		var f = document.editFrm;
		var is_milestone = f.task_milestone.checked;

		if (!is_milestone)
		if (f.resources.selectedIndex > -1){
			var id = f.resources.options[f.resources.selectedIndex].value;
			if ( ! checkunits(f.user_units.value) ){
				f.user_units.focus();
			}else{
			checkunits(f.user_units.value)
				if (id.length > 0){
					assignedid[assignedid.length] = id;
					units[id] = parseFloat(f.user_units.value);

					refreshfilters();
					selectChange();
					execute_remote_script("changed_resources");

				}
			}
		}
  }

  function removeuser(id)
  {
	  var f = document.editFrm;

	  for(i=0;i<assignedid.length;i++){
		if(assignedid[i] == id){
			assignedid[i] = -1;
			assignedid.splice(i,1);
			units[id] = null;

		}
	  }
	  refreshfilters();
	  selectChange( null );
	  execute_remote_script("changed_resources");
  }

  function checkunits(value){
	var ut = parseFloat(value);

	if ( isNaN(ut) || ut <= 0 || ut > 100){
		if(ut > 100){
			alert("<?php echo $AppUI->_('taskUserOverallocated');?>");
			return true;
		}
		alert("<?php echo $AppUI->_('taskUserInvalidUnits');?>");
		return false;
	}else{
		return true;
	}
  }

  function selectChange()
  {
	var myEle;
	var x;
	var control = document.editFrm.resources;
	var unassigned;

	for (var q=control.options.length; q>=0; q--)
	{
	  	control.options[q]=null;
	}

	//para cada uno de los usuarios disponibles
	for ( x = 0 ;x < usersid.length; x++ )
    {

		unassigned = true;
		//Me fijo si el usuario ya est?asignado
		unassigned = !(units[usersid[x]]);

		if( unassigned )
		{
			myEle = new Option( users[usersid[x]], usersid[x] );
			control.options[control.length] = myEle;
		}

    }
  }

  function changeunits(ut){

	if(! checkunits(ut.value)){
		ut.focus();
		return;
	}

	eval(ut.name + " = ut.value");
	refreshfilters();
	execute_remote_script("changed_units");
  }

  function update_units(user, value){
	units[user]=value;
	refreshfilters();
  }

  function update_budget_hhrr(value){
	var f = document.editFrm;
            f.target_budget_hhrr.value = value;
	f.task_target_budget_hhrr.value = value;
	return;
  }

  function update_constraints(type,cdate,chour, cmin)
  {
  	var f = document.editFrm;
    f.task_constraint_type.value = type;
    f.task_constraint_date.value = cdate.substring(6,10)+cdate.substring(3,5)+cdate.substring(0,2);
	f.constraint_date.value = cdate;
	f.constraint_hour.value = chour;
	f.constraint_minute.value = cmin;

	return;
  }

  function changedconstrainttype(){
	 var f = document.editFrm;
	 var mandatory_constraint_dates = "<?php echo $mandatory_constraint_dates?>";
	 var ctype = f.task_constraint_type.options[f.task_constraint_type.selectedIndex].value;

     /*if(f.constraint_date.value!="" && mandatory_constraint_dates.indexOf(ctype) > -1)
	 {
     execute_remote_script("task_constraint_date");
	 }*/

	 if (mandatory_constraint_dates.indexOf(ctype) > -1 )
	  {
		f.constraint_date.disabled = false;
	    changeDate(f.constraint_date);
	  }
	 else{
		f.constraint_date.disabled = true;
		execute_remote_script("task_constraint_date");
	 }

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

 function popConstraint(){

	var f = document.editFrm;
	var mandatory_constraint_dates = "<?php echo $mandatory_constraint_dates?>";
	var ctype = f.task_constraint_type.options[f.task_constraint_type.selectedIndex].value;

	if (mandatory_constraint_dates.indexOf(ctype) > -1 )
		popCalendar('constraint_date')


}

function changeEffortD(){
	var f = document.editFrm;

	if(assignedid.length <=1 || f.task_work.value ==0 ){
	      return;
	}

            execute_remote_script("changed_work");

}

function change_duration_type(dur, dur_type){
     alert('duracion: '+dur.value+' tipo de duracion: '+dur_type.value);
     // 1->horas /  24->días //

     if(dur_type =='1'){

     }

}

</script>

<table border="0" cellspacing="2" cellpadding="0" width="100%">
	<tr>
	  <td  style="font-weight: bold;" nowrap="nowrap" align="right">
	    <?php echo $AppUI->_('Expected Duration');?>:
	 </td>
	 <td align="left" width="170" nowrap="nowrap">
	   <input type="text" class="text" name="task_duration" maxlength="8" size="6" value="<?php echo $obj->task_duration!==NULL ? $obj->task_duration : 0;?>" onblur="swap_changed_fields(this);"  <?php if($is_dynamic_task) echo "disabled"?> />
		<?php
		echo arraySelect( $durnTypes, 'task_duration_type', 'class="text"  onChange="changeWork();" '.($is_dynamic_task? "disabled":"").' ', $obj->task_duration_type, true,'','60px' );
		?>
	 </td>
	 <td align="right" style="font-weight: bold;" nowrap="nowrap" >
		   <?php echo $AppUI->_( 'Work' );?>:
	 </td>
	 <td align="left" nowrap="nowrap" >
			<input type="text" class="text" name="task_work"  value="<?php echo @$obj->task_work;?>" size="5" maxlength="10" <?php if($is_dynamic_task) echo "disabled"?> onblur="changeWork();" />&nbsp;<?php echo $AppUI->_( 'hours' );?>
	  </td>

	</tr>

<?
if ($canEditDetails)
{ ?>
    <tr>
	<td align="right" nowrap="nowrap" style="font-weight: bold;"><?php echo $AppUI->_( 'Start Date' );?>:</td>
	<td align="left" nowrap="nowrap" >
		<input type="hidden" name="task_start_date_format" value="<?php echo $df;?>">
		<input type="hidden" name="task_start_date" value="<?php echo $start_date ? $start_date->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />
		<input type="text" name="start_date" value="<?php echo $start_date ? $start_date->format( $df ) : "" ;?>" size="10" class="text" onblur="changeDate(this);" <?php if($is_dynamic_task) echo "disabled"?>/>
		<a href="#" onClick="<?php echo ($is_dynamic_task ? "":"popCalendar('start_date')")?>"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0"></a>&nbsp;&nbsp;
		<?php
		if($start_date->hour=="00"){
			$start_date->hour = "09";
		}
 		echo arraySelect($hours, "start_hour",'size="1" '.($is_dynamic_task? "disabled":"").' onChange="checkDates(\'start\'); changeDate(this.form.start_date);" class="text"',$start_date ? $start_date->getHour() : $start ,'','','40px'). " : " ;
		echo arraySelect($minutes, "start_minute",'size="1" '.($is_dynamic_task? "disabled":"").' onChange="changeDate(this.form.start_date);" class="text"', $start_date ? $start_date->getMinute() : "0" ,'','','40px') ;
	?>
	</td>
		<td  align="right" nowrap="nowrap" style="font-weight: bold;">
		  <?php echo $AppUI->_( 'Constraint Type' );?>:
		</td>
		<td nowrap="nowrap" align="left">
		    <?
		       if($obj->task_constraint_type == "")
	                    {
	                     $task_constraint_type = "3";
	                    }
		        else
		        {
                                $task_constraint_type = $obj->task_constraint_type;
		        }

		         if($is_dynamic_task){
		         	$disabled_const = "disabled";
		         	$task_constraint_type = "3";
		         }else{$disabled_const = ""; }

		    ?>

			<?php
			echo arraySelect( $task_constraints, 'task_constraint_type', 'class="text" size="1" '.$disabled_const.' onChange="javascript: changedconstrainttype(this);"', @$task_constraint_type, true,'','212px' ); ?>
		</td>
	</tr>

    <tr>
		<td   align="right" nowrap="nowrap" style="font-weight: bold;"><?php echo $AppUI->_( 'Finish Date' );?>:</td>
		<td align="left" nowrap="nowrap" colspan="1">
		<input type="hidden" name="task_end_date_format" value="<?php echo $df;?>">
		<input type="hidden" name="task_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" size="10" class="text"  onblur="changeDate(this);" <?php if($is_dynamic_task) echo "disabled"?>/>
		<a href="#" onClick="<?php echo ($is_dynamic_task ? "":"popCalendar('end_date')")?>"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0"></a>&nbsp;&nbsp;<?php
		if($end_date->hour=="00"){
			$end_date->hour = "09";
		}
		echo arraySelect($hours, "end_hour",'size="1" '.($is_dynamic_task? "disabled":"").' onChange="checkDates(\'end\'); changeDate(this.form.end_date);" class="text"', $end_date ? $end_date->getHour() : $end,'','','40px' ) . " : " ;
		echo arraySelect($minutes, "end_minute",'size="1" '.($is_dynamic_task? "disabled":"").' class="text" onChange="changeDate(this.form.end_date);"', $end_date ? $end_date->getMinute() : "00",'','','40px' ) ;
		if ( $isAMPMTimeFormat ) {
			echo '<input type="text" name="end_hour_ampm" value="' . ( $end_date ? $end_date->getAMPM() : ( $end > 11 ? "pm" : "am" ) ) . '" disabled="disabled" class="text" size="2" />';
		}
	    ?>
		</td>

		<td  align="right" nowrap="nowrap" style="font-weight: bold;">
		  <?php echo $AppUI->_( 'Constraint Date' );?>:
		</td>
		<td nowrap="nowrap" align="left">
		       <?  if($task_constraint_type =="3") { $disabled_date_const = "disabled"; } ?>
			<input type="hidden" name="task_constraint_date_format" value="<?php echo $df;?>">
			<input type="hidden" name="task_constraint_date" value="<?php echo $constraint_date ? $constraint_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
			<input type="text" name="constraint_date" value="<?php echo $constraint_date ? $constraint_date->format( $df ) : '';?>" size="10" class="text"  onblur="changeDate(this);"  <?=$disabled_date_const ?> />
			<a href="#" onClick="popConstraint();"  <?=$disabled_date_const ?>><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0"></a>
            &nbsp;&nbsp;
		    <?php

			if($constraint_date->hour=="00"){
				$constraint_date->hour = "09";
			}
			echo arraySelect($hours, "constraint_hour",'size="1" '.($is_dynamic_task? "disabled":"").' onChange="checkDates(\'end\'); changeDate(this.form.constraint_date);" class="text"', $constraint_date ? $constraint_date->getHour() : "09",'','','40px' ) . " : " ;
			echo arraySelect($minutes, "constraint_minute",'size="1" '.($is_dynamic_task? "disabled":"").' class="text"onChange="changeDate(this.form.constraint_date);"', $constraint_date ? $constraint_date->getMinute() : "00",'','','40px' ) ;
			if ( $isAMPMTimeFormat ) {
				echo '<input type="text" name="constraint_hour_ampm" value="' . ( $constraint_date ? $constraint_date->getAMPM() : ( $end > 11 ? "pm" : "am" ) ) . '" disabled="disabled" class="text" size="2" />';
			}
			?>


		</td>

    </tr>

    <tr>
        <td colspan="97">
         <hr noshade="noshade" size="1">
        </td>
    </tr>

    <input type="hidden" name="task_dynamic" value="1">

	<tr>
	 <td colspan="6">
	  <table border = "0" width = "100%">
		<tr>
			<td width="95" align="right" nowrap="nowrap" style="font-weight: bold;">
			   <?php echo $AppUI->_( 'Task Type' );?>:
			</td>
			<td nowrap="nowrap" align="left">
				<?php
				 if (@$obj->task_type=="")
				 {
				 @$obj->task_type = 1;
				 }
				echo arraySelect( $task_types, 'task_type', 'class="text" size="1"', @$obj->task_type, true ,'','120px');
				?>
			</td>
			<td align="right" nowrap="nowrap" style="font-weight: bold;"><?php echo $AppUI->_( 'Effort Driven' );?>:</td>
			<td nowrap="nowrap" align="left" width="100">
			    <input type="checkbox" name="task_effort_driven" value="1" <?php if($obj->task_effort_driven!="0") echo "checked"?> onclick="changeEffortD();"/>
			   <!--<input type="checkbox" name="task_effort_driven" value="1" <?php if($obj->task_effort_driven!="0") echo "checked"?> /> -->
			</td>
			<td align="right" nowrap="nowrap" style="font-weight: bold;">
			  <?php echo $AppUI->_( 'Milestone' );?>?
			</td>
			<td width="100" >
				<!-- <input type="checkbox" value="1" name="task_milestone" <?php if($obj->task_milestone){?>checked<?php }?> onclick="swapMilestone();" /> -->
				<input type="checkbox" value="1" name="task_milestone" <?php if($obj->task_milestone){?>checked<?php }?>  />
			</td>
		</tr>
	  </table>

	 </td>
	</tr>

	<tr>
        <td colspan="97">
         <hr noshade="noshade" size="1">
        </td>
    </tr>

	<tr>
		<td colspan="4">

		  <table border="0">
		    <tr>
			    <td align="right" nowrap="nowrap" style="font-weight: bold;" >
					 <?php echo $AppUI->_( 'Priority' );?>:
				</td>
				<td nowrap="nowrap">
				     <?
			              if ($obj->task_priority == "")
						  {
			              $task_priority = "500";
						  }
						  else{
						  $task_priority = $obj->task_priority;
						  }

			         ?>
					 <input type="text" name="task_priority" value="<?=$task_priority; ?>" class="text" size="4" maxlength="4">*
				</td>

				<?php if ($canEditEcValues){ ?>
				<td align="right" nowrap="nowrap" style="font-weight: bold;"><?php echo $AppUI->_( 'Target Budget' )." RRHH";?>&nbsp;<?php echo $dPconfig['currency_symbol'] ?>:
				</td>
				<td nowrap="nowrap" align="left">
					<input type="text" class="text" name="target_budget_hhrr" value="<?php echo @$obj->task_target_budget_hhrr;?>" size="10" disabled/>
					<input type = "hidden" name = "task_target_budget_hhrr" value = "<?php echo @$obj->task_target_budget_hhrr;?>" size="10">
				</td>
				<?php }else{ ?>
				<td colspan="2">&nbsp;</td>
				<?php } ?>

				<?php if ($canEditEcValues){ ?>
				<td align="right" nowrap="nowrap" style="font-weight: bold;"><?php echo $AppUI->_( 'Target Budget' )." B/S";?>&nbsp;<?php echo $dPconfig['currency_symbol'] ?>:</td>
				<td nowrap="nowrap" align="left">
					<input type="text" class="text" name="task_target_budget" value="<?php echo @$obj->task_target_budget;?>" size="10" maxlength="10" />
				</td>
				<?php }else{ ?>
				<td colspan="2">&nbsp;</td>
				<?php } ?>

			</tr>
		    <tr>
			    <?
				//si la tarea es nueva no muestro estas opciones
				if(isset($_GET['task_id']))
				{
						if($obj->task_id != $obj->task_parent) {
							$sql2 = "SELECT task_complete as task_parent_is_complete
									FROM tasks
									WHERE
									task_id = ".$obj->task_parent;
							$vec2 = db_fetch_array(db_exec($sql2));
							} else
							$vec2['task_parent_is_complete'] == '0';

					?>
				<td align="right" nowrap="nowrap" style="font-weight: bold;">
							<?php echo $AppUI->_( 'Progress' );?>:
				</td>
				<td nowrap="nowrap">
				   <?php $obj->task_manual_percent_complete_select(TRUE); ?>
				   <?php echo arraySelect( $percent, 'task_manual_percent_complete_2', 'size="1" class="text" '.($obj->task_complete ? 'disabled="disabled"' : ' ' ), $obj->task_manual_percent_complete ,'','','50px') . '%';?>
				</td>
				<?php
				} ?>
				<td align="right" nowrap="nowrap" style="font-weight: bold;"><?php echo $AppUI->_('Task Creator');?>:</td>
				<td width="90">
				<?php echo arraySelect( $users, 'task_owner', 'class="text"', !isset($obj->task_owner) ? $AppUI->user_id : $obj->task_owner,'','','200px');?>
				</td>

				<td align="right" nowrap="nowrap" style="font-weight: bold;"><?php echo $AppUI->_('Task Visibility');?>:</td>
				<td colspan="1">
				<?php echo arraySelect( $task_access, 'task_access', 'class="text"', !is_null($obj->task_access) ? intval( $obj->task_access ) : $defaul_task_access , true );?>
				</td>
			</tr>
		  </table>

		</td>
   </tr>
   <tr>
        <td colspan="97">
		  <hr noshade="noshade" size="1">
		</td>
   </tr>
</table>

<?
}
?>

<?php if ($canManageRoles) {?>
 <input type="checkbox" name="task_notify" value="1" <?php if($obj->task_notify!="0") echo "checked"?> /><?php echo $AppUI->_( 'notifyChange' );?>

 <table cellspacing="0" cellpadding="0" border="0" width="100%" bgcolor="#ffffff" class="">

		<tr class="tableHeaderGral">
			<td align="center"><?php echo $AppUI->_( 'Resources' );?></td>
			<td width="100px" align="center">&nbsp;</td>
			<td width="10%" align="center"><?php echo $AppUI->_( 'Units' );?></td>
			<td width="50px">&nbsp;</td>
		</tr>
		<tr>
			<td>
			<?php echo arraySelect( $users, 'resources', 'style="width:250px" size="1" 	class="text"', null ); ?>
			</td>
			<td width="100px" align="center">&nbsp;</td>
			<td align="center"><input type="text" class="text" name="user_units" value="100" size="3" maxlength="3" />%</td>
			<td align="center">
			<? if($is_dynamic_task){?>
            <input type="button" class="button" value="<?php echo strtolower($AppUI->_("Assign"));?>"  title="<?php echo $AppUI->_("Add");?>" disabled />
			<?} else {?>
            <input type="button" class="button" value="<?php echo strtolower($AppUI->_("Assign"));?>"  title="<?php echo $AppUI->_("Add");?>" onClick="adduser()" />
			<? } ?>
			</td>
		</tr>

		<tr class="tableHeaderGral">
			<td align="center"><?php echo $AppUI->_( 'Assigned Users' );?></td>
			<td width="100px" align="center"><?php echo $AppUI->_( 'Cost' );?></td>
			<td width="20px" align="center"><?php echo $AppUI->_( 'Units' );?></td>
			<td width="50px">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="4"><div id="taskusers" name="taskusers"></div>
			</td>
		</tr>
		</table>
<?php }?>


<script language="JavaScript">
refreshfilters();
<? if ($canManageRoles){ ?>
selectChange();
<? } ?>
</script>
