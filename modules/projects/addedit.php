<?php /* PROJECTS $Id: addedit.php,v 1.3 2009-07-15 18:42:11 nnimis Exp $ */
$project_id = intval( dPgetParam( $_GET, "project_id", 0 ) );

// get a list of permitted companies
require_once( $AppUI->getModuleClass ('companies' ) );

$row = new CCompany();
$companies = $row->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$companies = arrayMerge( array( '0'=>'' ), $companies );

// pull users
//$sql = "SELECT user_id, CONCAT_WS(', ',user_last_name,user_first_name) FROM users ORDER BY user_last_name";
//$users = db_loadHashList( $sql );
$users = CUser::getAssignableUsers("user_id, CONCAT_WS(', ',user_last_name,user_first_name)");

if ($project_id!="0")
{
 // Si no es un projecto nuevo, me fijo si tiene tareas y pongo la fecha de finalizacion de la tarea que termine mas tarde//

 $query = "select task_end_date from tasks where task_project='$project_id' order by task_end_date desc";
 $sql = mysql_query($query);
                     
 $fecha_fin = mysql_fetch_array($sql);

 if ($fecha_fin[0]=="")
	{
	 $query = "select project_start_date from projects where project_id='$project_id'";
	 $sql = mysql_query($query);
						 
	 $fecha_comienzo = mysql_fetch_array($sql);
	 
     // Actualizo la base de datos //

	 $query2 = "UPDATE projects SET project_actual_end_date = '$fecha_comienzo[0]' WHERE project_id=$project_id";

	 $sql2 = mysql_query($query2);

	}
	else
	 {

	 // Actualizo la base de datos //

	 $query3 = "UPDATE projects SET project_actual_end_date = '$fecha_fin[0]' WHERE project_id=$project_id";

	 $sql3 = mysql_query($query3);
	 }
                     
 }

// load the record data
$row = new CProject();

if (!$row->load( $project_id, false ) && $project_id > 0) {
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else if (count( $companies ) < 2 && $project_id == 0) {
	$AppUI->setMsg( "noCompanies", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

if ($project_id > 0){
	$canEdit = $row->canEdit();
	$prole = new CProjectRoles();
	$prjUsers = $prole->getAssignedUsers(2 ,$project_id);
	$usersAssignedToTasks = $row->getUsersAssignedToTasks($project_id);
}else{
	global $colors_list;
	$canEdit = $row->canCreate();
	$prjUsers = array();
	$usersAssignedToTasks = array();
	
	$i=0;
	while ($row->project_color_identifier == '' && $i < count($colors_list)){
		$color_id = rand(0, count($colors_list));
		$sql = "select count(*)
				from projects 
				where project_color_identifier = '".$colors_list[$color_id]."'";
		$row->project_color_identifier = db_loadResult($sql) > 0 ? '':$colors_list[$color_id];
		$i++;
	}
	//$canReadDetails = $canEdit;
	//$canReadEcValues = $canEdit;

}

if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

if ( $project_id == 0 ) {
	// Add task creator to assigned users by default
	$assigned = array($AppUI->user_id => "$AppUI->user_first_name $AppUI->user_last_name");
} else {
	$assigned = $row->getOwners();
}

// add in the existing company if for some reason it is dis-allowed
if ($project_id && !array_key_exists( $row->project_company, $companies )) {
	$companies[$row->project_company] = db_loadResult(
		"SELECT company_name FROM companies WHERE company_id=$row->project_company"
	);
}

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = new CDate( $row->project_start_date );

$end_date = intval( $row->project_end_date ) ? new CDate( $row->project_end_date ) : null;
$actual_end_date = intval( $row->project_actual_end_date ) ? new CDate( $row->project_actual_end_date ) : null;

// setup the title block
$ttl = $project_id > 0 ? "Edit Project" : "New Project";
$titleBlock = new CTitleBlock( $ttl, 'projects.gif', $m, "projects.index" );
$titleBlock->addCrumb( "?m=projects", "projects list" );
if ($project_id != 0){
  $titleBlock->addCrumb( "?m=projects&a=view&project_id=$project_id", "view this project" );
	$titleBlock->addCrumb( "?m=projects&a=vw_baselines&project_id=$project_id", "baselines" );
}
$titleBlock->show();


?>

<link rel="stylesheet" type="text/css" media="all" href="/lib/calendar/calendar-dp.css" title="blue" />
<!-- import the calendar script -->
<script type="text/javascript" src="/lib/calendar/calendar.js"></script>
<!-- import the language module -->
<script type="text/javascript" src="/lib/calendar/lang/calendar-<?php echo $AppUI->user_locale; ?>.js"></script>

<script language="javascript">
<? 		echo "var usersAssignedToTasks = new Array();\n";
		foreach (array_keys($usersAssignedToTasks) as $campo=>$uid)
			echo "usersAssignedToTasks[$uid] = 1;\n";
?>

function setColor(color) {
	var f = document.editFrm;
	if (trim(color)) {
		f.project_color_identifier.value = trim(color);
	}
	//test.style.background = f.project_color_identifier.value;
	
	if (trim(f.project_color_identifier.value)){
		document.getElementById('test').style.background = '#' + f.project_color_identifier.value; 		//fix for mozilla: does this work with ie? opera ok.
	}
}

function setShort() {
	var f = document.editFrm;
	var x = 10;
	if (f.project_name.value.length < 11) {
		x = f.project_name.value.length;
	}
	if (f.project_short_name.value.length == 0) {
		f.project_short_name.value = f.project_name.value.substr(0,x);
	}
}

var calendarField = '';
var calWin = null;

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.project_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=280, height=250, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.project_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function submitIt() {
	var f = document.editFrm;
	var fl = f.assignedadmins.length -1;
	var fu = f.assignedusers.length -1;
	var fr = f.resources2.length -1;
	var msg = '';

	if (trim(f.project_name.value).length < 3) {
		msg += "\n<?php echo $AppUI->_('projectsValidName');?>";
		f.project_name.focus();
	}

	f.hassign.value = "";
	for (fl; fl > -1; fl--){
		f.hassign.value = "," + f.hassign.value +","+ f.assignedadmins.options[fl].value
	}
	
	
	f.husersassign.value = "";
	for (fu; fu > -1; fu--){
		f.husersassign.value = "," + f.husersassign.value +","+ f.assignedusers.options[fu].value
	}
	
	var confirmmsg='';
	for (fr; fr > -1; fr--){
		var tmpval = f.resources2.options[fr].value;
		if (usersAssignedToTasks[tmpval] && !f.assignedusers.options[getItemIndex(f.assignedusers, tmpval)] ){
			confirmmsg += '\n' + f.resources2.options[fr].text;
		}
	}
		
	
	
	if (confirmmsg!=''){
		if (!confirm1( '<?=$AppUI->_("The following users has one or more tasks assigned to them. Do you really want to delete those users from this project and all his assignations to tasks on it?");?>' + confirmmsg )) {
			return false;
		}	
	}
				
	if (f.project_id.value > 0 && f.original_company.value != f.project_company.options[f.project_company.selectedIndex].value){
		if (!confirm( "<?=$AppUI->_("doRemovePermissions");?>")) {
			return false;
		}	
	}	
	if (trim(f.project_color_identifier.value).length < 3) {
		msg += "\n<?php echo $AppUI->_('projectsColor');?>";
		f.project_color_identifier.focus();
	}
	if (f.project_company.options[f.project_company.selectedIndex].value < 1) {
		msg += "\n<?php echo $AppUI->_('projectsBadCompany');?>";
		f.project_name.focus();
	}

	f.project_start_date.value = trim(f.project_start_date.value);
	f.project_end_date.value = trim(f.project_end_date.value);
	f.project_actual_end_date.value = trim(f.project_actual_end_date.value);
	
	if (trim(f.project_end_date.value) != "" && f.project_end_date.value < f.project_start_date.value) {
		msg += "\n<?php echo $AppUI->_('projectsBadEndDate1');?>";
	}
	
	if (trim(f.project_url.value) != "" && !isURL(trim(f.project_url.value))) {
		msg += "\n<?php echo $AppUI->_('projectsBadURL');?>";
	}
	if (trim(f.project_demo_url.value) != "" && !isURL(trim(f.project_demo_url.value))) {
		msg += "\n<?php echo $AppUI->_('projectsBadDemoURL');?>";
	}

	if (trim(f.project_email_docs.value) != "" && !isEmail(f.project_email_docs.value)) {
		msg += "\n<?php echo $AppUI->_('projectsBadDocMail');?>";
	}
	
	if (trim(f.project_email_support.value) != "" && !isEmail(f.project_email_support.value)) {
		msg += "\n<?php echo $AppUI->_('projectsBadSupportMail');?>";
	}
	
	if (trim(f.project_email_todo.value) != "" && !isEmail(f.project_email_todo.value)) {
		msg += "\n<?php echo $AppUI->_('projectsBadTodoMail');?>";
	}	
	
	if (trim(f.project_email_docs.value) != "" && (trim(f.project_email_docs.value) == trim(f.project_email_support.value) || trim(f.project_email_docs.value) == trim(f.project_email_todo.value))) {
		msg += "\n<?php echo $AppUI->_('Project documentation e-mail already exists');?>";
	}
	
	if (trim(f.project_email_support.value) != "" && (trim(f.project_email_support.value) == trim(f.project_email_docs.value) || trim(f.project_email_support.value) == trim(f.project_email_todo.value))) {
		msg += "\n<?php echo $AppUI->_('Project support e-mail already exists');?>";
	}
		
	//f.project_target_budget.value = trim(f.project_target_budget.value);
	f.project_actual_budget.value = trim(f.project_actual_budget.value);
	
	/*var valor = parseFloat(f.project_target_budget.value);
	if (trim(f.project_target_budget.value) != "" &&  (isNaN(valor) || Math.abs(valor) > 2000000000)){
		msg += "\n<?php echo $AppUI->_('projectsBadTargetBudget');?>";
		f.project_target_budget.focus();
	}*/

	var valor = parseFloat(f.project_actual_budget.value);
	if (trim(f.project_actual_budget.value) != "" && (isNaN(valor) || Math.abs(valor) > 2000000000)){
		msg += "\n<?php echo $AppUI->_('projectsBadActualBudget');?>";
		f.project_actual_budget.focus();
	}	
    
	
	if (msg.length < 1) {
		f.submit();
	} else {
		alert(msg);
	}
}

function addAdmin() {
	var form = document.editFrm;
	var fl = form.resources1.length -1;
	var au = form.assignedadmins.length -1;
	var users = "x";

	//build array of assiged users
	for (au; au > -1; au--) {
		users = users + "," + form.assignedadmins.options[au].value + ","
	}

	//Pull selected resources and add them to list
	for (fl; fl > -1; fl--) {
		if (form.resources1.options[fl].selected && users.indexOf( "," + form.resources1.options[fl].value + "," ) == -1) {
			t = form.assignedadmins.length
			opt = new Option( form.resources1.options[fl].text, form.resources1.options[fl].value );
			form.assignedadmins.options[t] = opt
		}
	}
}

function removeAdmin() {
	var form = document.editFrm;
	fl = form.assignedadmins.length -1;

	for (fl; fl > -1; fl--) {
		if (form.assignedadmins.options[fl].selected) {
			form.assignedadmins.options[fl] = null;
		}
	}
}
function addUser() {
	var form = document.editFrm;
	var fl = form.resources2.length -1;
	var au = form.assignedusers.length -1;
	var users = "x";

	//build array of assiged users
	for (au; au > -1; au--) {
		users = users + "," + form.assignedusers.options[au].value + ","
	}

	//Pull selected resources and add them to list
	for (fl; fl > -1; fl--) {
		if (form.resources2.options[fl].selected && users.indexOf( "," + form.resources2.options[fl].value + "," ) == -1) {
			t = form.assignedusers.length
			opt = new Option( form.resources2.options[fl].text, form.resources2.options[fl].value );
			form.assignedusers.options[t] = opt
		}
	}
}

function removeUser() {
	var form = document.editFrm;
	fl = form.assignedusers.length -1;

	for (fl; fl > -1; fl--) {
		if (form.assignedusers.options[fl].selected) {
			form.assignedusers.options[fl] = null;
		}
	}
}
function getItemIndex(combo, value){
	
	var i = combo.length -1;
	for (i; i > -1; i--) {
		if (combo.options[i].value == value) {
			return i;
		}
	}
	return false;
} 

function changeSatisfactionCanalLevel(canalValue)
{
	if (canalValue == 0)
	{
		document.getElementById('project_level_canal_satisfaction').disabled = true;
		document.getElementById('project_level_canal_satisfaction').options[0].selected = true;
	}
	else
	{
		document.getElementById('project_level_canal_satisfaction').disabled = false;
	}
}

</script>

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<form name="editFrm" action="./index.php?m=projects" method="post">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
	<input type="hidden" name="project_creator" value="<?php echo $AppUI->user_id;?>" />
	<input type="hidden" name="original_company" value="<?php echo $row->project_company;?>" />

<tr>
	<td width="50%" valign="top">
		<table cellspacing="0" cellpadding="2" border="0">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Name');?></td>
			<td width="100%">
				<input type="text" name="project_name" value="<?php echo dPformSafe( $row->project_name );?>" size="25" maxlength="50" onBlur="setShort();" class="text" /> *
			</td>
		</tr>


		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Owner');?></td>
			<td>
<?php echo arraySelect( $users, 'project_owner', 'size="1" style="width:200px;" class="text"', $row->project_owner? $row->project_owner : $AppUI->user_id ) ?>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company');?></td>
			<td width="100%" nowrap="nowrap">
<?php
		//$row->project_company = $_GET['company_id'];
		//$AppUI->user_company = $_GET['company_id'];
		echo arraySelect( $companies, "project_company", "class=\"text\" size=\"1\" ", ($row->project_company ? $row->project_company : $_GET['company_id']),"","","200 px" );
?> *

            </td>
		</tr>
		
		
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Canal');?></td>
			<td width="100%" nowrap="nowrap">
			   <? echo arraySelect( $companies, "project_canal", "class=\"text\" size=\"1\" onchange=\"javascript:changeSatisfactionCanalLevel(this.value);\" ", ($row->project_canal ? $row->project_canal :  $AppUI->project_canal),"","","200 px" ); ?>

            </td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date');?></td>
			<td>	 <input type="hidden" name="project_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
				<input type="text" class="text" name="start_date" id="date1" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" />

				<a href="#" onClick="popCalendar( 'start_date', 'start_date');">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target End Date');?></td>
			<td>	
			    
			    <input type="hidden" name="project_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
				<input type="text" class="text" name="end_date" id="date2" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />

				 <a href="#" onClick="popCalendar('end_date', 'end_date');">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a> 
			</td>
		</tr>
		  <tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Contract price');?> <?php echo $dPconfig['currency_symbol'] ?></td>
			<td>
				<input type="Text" name="project_target_budget" value="<?php echo @$row->project_target_budget;?>" maxlength="11" class="text" />
			</td>
		</tr> 
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Additional expenses G/S');?> <?php echo $dPconfig['currency_symbol'] ?></td>
			<td>
				<input type="Text" name="project_other_estimated_cost" value="<?php echo @$row->project_other_estimated_cost;?>" maxlength="11" class="text" />
			</td>
		</tr>		
		<tr>
			<td colspan="2"><hr noshade="noshade" size="1"></td>
		</tr>		
		<tr>

			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Finish Date');?></td>
			<td>	<input type="hidden" name="project_actual_end_date" value="<?php echo $actual_end_date ? $actual_end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
				<input type="text" class="text" name="actual_end_date" id="date2" value="<?php echo $actual_end_date ? $actual_end_date->format( $df ) : '';?>" class="text" disabled="disabled" />

				<!-- <a href="#" onClick="popCalendar('actual_end_date', 'actual_end_date');">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a> -->


			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Budget');?> <?php echo $dPconfig['currency_symbol'] ?></td>
			<td>
				<input type="text" name="project_actual_budget" value="<?php echo actual_budget ($project_id);?>" maxlength="11" class="text" disabled/>
			</td>
		</tr>
		<tr>
			<td colspan="2"><hr noshade="noshade" size="1"></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL');?></td>
			<td>
				<input type="text" name="project_url" value='<?php echo @$row->project_url;?>' size="40" maxlength="255" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Staging URL');?></td>
			<td>
				<input type="Text" name="project_demo_url" value='<?php echo @$row->project_demo_url;?>' size="40" maxlength="255" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('E-Mail Documentation');?></td>
			<td>
				<input type="Text" name="project_email_docs" value='<?php echo @$row->project_email_docs;?>' size="40" maxlength="200" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('E-Mail Incidents');?></td>
			<td>
				<input type="Text" name="project_email_support" value='<?php echo @$row->project_email_support;?>' size="40" maxlength="200" class="text" />
			</td>
		</tr>		
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('E-Mail ToDo');?></td>
			<td>
				<input type="Text" name="project_email_todo" value='<?php echo @$row->project_email_todo;?>' size="40" maxlength="200" class="text" />
			</td>
		</tr>		
		<tr>
			<td colspan="2"><hr noshade="noshade" size="1"></td>
		</tr>
		<tr>
		<?
			if(@$row->project_id > 0)
			{
			include_once('./modules/public/satisfaction_suppliers_customers.php');
			$arrlevel_customer_satisfaction = getSatisfactionLevel(1, @$row->project_id);
			$level_customer_satisfaction = $arrlevel_customer_satisfaction[0]['level_satisfaction'];
			
			$arrlevel_canal_satisfaction = getSatisfactionLevel(2, @$row->project_id);
			$level_canal_satisfaction = $arrlevel_canal_satisfaction[0]['level_satisfaction'];
			}
		?>
			<input type="hidden" name="project_level_customer_satisfaction_original" value="<?=$level_customer_satisfaction?>" />
			<input type="hidden" name="project_level_canal_satisfaction_original" value="<?=$level_canal_satisfaction?>" />
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Level of Customer Satisfaction');?></td>
			<?
				$arrAssessmentSatisfactionTypes = CProject::getAssessmentSatisfactionTypes();
				$arrAssessmentSatisfactionTypes = array_merge(array('0'=>''), $arrAssessmentSatisfactionTypes);
			?>			
			<td width="100%" nowrap="nowrap">
			   <? echo arraySelect( $arrAssessmentSatisfactionTypes, 'project_level_customer_satisfaction', (!$canEdit ? 'disabled ' : '').'style="width:200px;" class="text"', $level_customer_satisfaction, false, false, null); ?>
            </td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Level of Satisfaction with the Canal');?></td>
			<td width="100%" nowrap="nowrap">
			   <? echo arraySelect( $arrAssessmentSatisfactionTypes, 'project_level_canal_satisfaction', (!$canEdit || $row->project_canal == 0 ? 'disabled ' : '').'style="width:200px;" class="text"', $level_canal_satisfaction, false, false, null); ?>
            </td>
		</tr>		
		<tr>
			<td colspan="2"><hr noshade="noshade" size="1"></td>
		</tr>
		<tr>
			<td colspan="2">
				<table cellspacing="0" cellpadding="2" border="0" width="100%">
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name');?></td>
					<td colspan="3">
						<input type="text" name="project_short_name" value="<?php echo dPformSafe( @$row->project_short_name ) ;?>" size="10" maxlength="10" class="text" /> *
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Color Identifier');?></td>
					<td nowrap="nowrap">
						<input type="text" name="project_color_identifier" value="<?php echo @$row->project_color_identifier;?>" size="10" maxlength="6" onBlur="setColor(this.value);" class="text" /> *
					</td>
					<td nowrap="nowrap">
						<a href="#" onClick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor&suppressLogo=1', 'calwin', 'width=320, height=300, scollbars=false');"><?php echo $AppUI->_('change color');?></a>
					</td>
					<td nowrap="nowrap">
						<span id="test" name="test" title="test" style="background:#<?php echo @$row->project_color_identifier;?>;"><a href="#" onClick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor&suppressLogo=1', 'calwin', 'width=320, height=300, scollbars=false');"><img src="./images/shim.gif" border="1" width="40" height="20" /></a></span>
					</td>
				</tr>
				<tr>
					<td colspan="4">
						<table width="100%" bgcolor="#cccccc">
						<tr>
							<td><?php echo $AppUI->_('Status');?> *</td>
							<td nowrap="nowrap"><?php echo $AppUI->_('Progress');?></td>
							<td><?php echo $AppUI->_('Active');?>?</td>
						</tr>
						<tr>
							<td>
								<?php echo arraySelect( $pstatus, 'project_status', 'size="1" class="text"', $row->project_status, true ); ?>
							</td>
							<td>
								<strong><?php echo intval(@$row->project_percent_complete);?> %</strong>
							</td>
							<td>
								<input type="checkbox" value="1" name="project_active" <?php echo $row->project_active||$project_id==0 ? 'checked="checked"' : '';?> />
							</td>
						</tr>
						</table>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
	</td>
	<td width="50%" valign="top">
		<table cellspacing="0" cellpadding="2" border="0" width="100%">

		<!-- BEGIN Handco patch -->
		<?php  
			if ($project_id != 0) {
				$sql = "SELECT COUNT(task_id) as count FROM tasks WHERE task_project = $project_id";
				$result = db_loadResult ($sql);
				$canImportTasks = $result == 0;
			} else
				$canImportTasks = true;
				
			if ($canImportTasks) { // We provide task import only for an empty project
				
				// Retrieve projects that the user can access
				$objProject = new CProject();
				$allowedProjects = $objProject->getAllowedRecords( $AppUI->user_id, 'project_id,project_name', 'project_name' );

				//retrieve the number of existing projects
				$sql = "SELECT COUNT(*) FROM projects";
				$numProj = db_loadColumn($sql);
				//echo $numProj[0];

				// Loading project with tasks
				$sql = 'SELECT DISTINCT p.project_id, p.project_name
						FROM projects AS p , tasks AS t 
						WHERE ( t.task_project = p.project_id )';
				if ( count($allowedProjects) > 0 ) {
					$sql .= ' AND (p.project_id IN (' .
						implode (',', array_keys($allowedProjects)) . ')) ORDER BY p.project_name';
				}

				$importList = db_loadHashList ($sql);
				$importList = arrayMerge( array( '0'=> $AppUI->_('none') ), $importList);

		?>
			<tr>
				<td align="right" nowrap="nowrap">
					<?php echo $AppUI->_('Copy tasks from');?><br/>
				</td>
				<td colspan="3">
					<?php echo arraySelect( $importList, 'import_tasks_from', 'size="1" class="text"', null, false ); ?>
				</td>
			</tr>
		<?php } // End of task import list code?>
		<!-- END Handco patch -->
		<tr>
			<td colspan="4">
				<table cellspacing="0" cellpadding="2" border="0">
					<tr>
						<td><?php echo $AppUI->_( 'Resources' );?></td>
						<td><?php echo $AppUI->_( 'Project Administrators' );?></td>
					</tr>
					<tr>
						<td>
							<?php echo arraySelect( $users, 'resources1', 'style="width:180px" size="8" style="font-size:9pt;" multiple="multiple"', null ); ?>
						</td>
						<td>
							<?php echo arraySelect( $assigned, 'assignedadmins', 'style="width:180px" size="8" style="font-size:9pt;" multiple="multiple"', null ); ?>
						</td>
					<tr>
						<td align="right"><input type="button" class="button" value="&gt;" onClick="addAdmin()" /></td>
						<td align="left"><input type="button" class="button" value="&lt;" onClick="removeAdmin()" /></td>
					</tr>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<table cellspacing="0" cellpadding="2" border="0">
					<tr>
						<td><?php echo $AppUI->_( 'Resources' );?></td>
						<td><?php echo $AppUI->_( 'Project Users' );?></td>
					</tr>
					<tr>
						<td>
							<?php echo arraySelect( $users, 'resources2', 'style="width:180px" size="8" style="font-size:9pt;" multiple="multiple"', null ); ?>
						</td>
						<td>
							<?php echo arraySelect( $prjUsers, 'assignedusers', 'style="width:180px" size="8" style="font-size:9pt;" multiple="multiple"', null ); ?>
						</td>
					<tr>
						<td align="right"><input type="button" class="button" value="&gt;" onClick="addUser()" /></td>
						<td align="left"><input type="button" class="button" value="&lt;" onClick="removeUser()" /></td>
					</tr>
					</tr>
				</table>
			</td>
		</tr>		
		<tr>
			<td colspan="4">
				<?php echo $AppUI->_('Description');?><br />
				<textarea name="project_description" cols="50" rows="10" wrap="virtual" class="textarea"><?php echo dPformSafe( @$row->project_description );?></textarea>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td>
		<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel');?>" onClick="javascript:if(confirm('<?=$AppUI->_('Are you sure you want to cancel ?')?>')){location.href = './index.php?m=projects';}" />
	</td>
	<td align="right">
		<input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt();" />
		<input type="hidden" name="hassign" />
		<input type="hidden" name="husersassign" />
	</td>
</tr>
</form>
</table>
* <?php echo $AppUI->_('requiredField');?>
