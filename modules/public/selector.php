<?php /* PUBLIC $Id: selector.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */

function selPermWhere( $table, $idfld ) {
	global $AppUI;

	// get any companies denied from viewing
	$sql = "SELECT $idfld"
		."\nFROM $table, permissions"
		."\nWHERE permission_user = $AppUI->user_id"
		."\n	AND permission_grant_on = '$table'"
		."\n	AND permission_item = $idfld"
		."\n	AND permission_value = 0";

	$deny = db_loadColumn( $sql );
	echo db_error();

	return "permission_user = $AppUI->user_id"
		."\nAND permission_value <> 0"
		."\nAND ("
		."\n	(permission_grant_on = 'all')"
		."\n	OR (permission_grant_on = '$table' and permission_item = -1)"
		."\n	OR (permission_grant_on = '$table' and permission_item = $idfld)"
		."\n	)"
		. (count($deny) > 0 ? "\nAND $idfld NOT IN (" . implode( ',', $deny ) . ')' : '');
}

$debug = false;
$callback = dPgetParam( $_GET, 'callback', 0 );
$table = dPgetParam( $_GET, 'table', 0 );
$selected = dPgetParam( $_GET, 'selected', 0 ); //El ID del valor que se va a seleccionar de la lista

$ok = $callback & $table;

$title = "Generic Selector";
$select = '';
$from = $table;
$where = '';
$order = '';
$pretitle='';
$nosqlquery = false;
$includelist = array();
$maxchars=50;

switch ($table) {
case 'companies':
	$title = 'Company';
	$select = 'company_id,company_name';
	$order = 'company_name';
	$table .= ", permissions";
	$where = selPermWhere( 'companies', 'company_id' );
	break;
case 'departments':
// known issue: does not filter out denied companies
	$title = 'Department';
	$company_id = dPgetParam( $_GET, 'company_id', 0 );
	//$ok &= $company_id;  // Is it safe to delete this line ??? [kobudo 13 Feb 2003]
	//$where = selPermWhere( 'companies', 'company_id' );
	$where = "dept_company = company_id ";

	$table .= ", companies";
	$select = "dept_id,CONCAT_WS(': ',company_name,dept_name) AS dept_name";
	if ($company_id) {
		$where .= "\nAND dept_company = $company_id";
		$order = 'dept_name';
	} else {
		$order = 'company_name,dept_name';
	}
	break;
case 'hhrr_education_title':
// known issue: does not filter out denied companies
	$title = 'Academic Title';
	$level_id = dPgetParam( $_GET, 'level_id', 0 );
	
	$where = "1 = 1";
    $select = "title_id , name_es"; 
    
	if ($level_id) {
		$where .= "\nAND level_id = $level_id";
		$order = 'name_es';
	} else {
		$where .= "\nAND level_id = '-2' ";
		$order = 'name_es';
	}
	break;

case 'forums':
	$title = 'Forum';
	$select = 'forum_id,forum_name';
	$order = 'forum_name';
	break;
case 'projects':
	global $AppUI;
	require_once( $AppUI->getModuleClass( 'projects' ) );
	$prjobj = new CProject();
	$project_list =$prjobj->getAllowedRecords($AppUI->user_id, "project_id");
	$project_company = dPgetParam( $_GET, 'project_company', 0 );

	$title = 'Project';
	$select = 'project_id,project_name';
	$order = 'project_name';
	$where = selPermWhere( 'projects', 'project_id' );
	$where .= $project_company ? "\nAND project_company = $project_company" : '';
	$where .= count($project_list) ? "\nAND project_id IN (".implode(array_keys($project_list), ', ').")":"\nAND 1=0";
	$table .= ", permissions";
	break;
case 'projec':
	$table = "project";
	break;
case 'tasks':
	global $AppUI;
	require_once( $AppUI->getModuleClass( 'tasks' ) );
	$tskobj = new CTask();
	$task_list = CTask::getDeniedRecords($AppUI->user_id);
	$task_project = dPgetParam( $_GET, 'task_project', 0 );
	$task_perm = dPgetParam( $_GET, 'task_perm', 0 );
	$title = 'Task';
	if ($task_project!= 0 ){
		$prjobj = new CProject();
		$prjobj->load($task_project);
		$pretitle = $AppUI->_( "Project" ).": ". $prjobj->project_name;
	}
	$select = 'task_id,task_name';
	$order = 'task_name';
	$where = "1=1";
	$where .= $task_project ? "\nAND task_project = $task_project" : '';
	$where .= count($task_list) ? "\nAND task_id NOT IN (".implode($task_list, ', ').")":"";
	$task_allowed_list=array();
	$task_allowed_list = CTask::getPermissions($AppUI->user_id, $task_project, 0, 3 );  // Saque esto afuera del if 2006-11-03
	$task_allowed_list = CTask::getPermissions($AppUI->user_id, $task_project, 0, 4 );	// Saque esto afuera del if 2006-11-03
	/*if (strtolower($task_perm)=="time"){
		$task_allowed_list = CTask::getPermissions($AppUI->user_id, $task_project, 0, 3 );
	}
	if (strtolower($task_perm)=="expense"){
		$task_allowed_list = CTask::getPermissions($AppUI->user_id, $task_project, 0, 4 );
	}*/
	$allowed = array();
	for ($i=0; $i<count($task_allowed_list);$i++){
		if ($task_allowed_list[$i]["task_permission_value"]==PERM_EDIT){
			$allowed[]=$task_allowed_list[$i]["task_id"];
		}
	
	}
	unset($task_allowed_list );
	if (isset($task_perm)){
		$where .= count($allowed) ? "\nAND task_id IN (".implode($allowed, ', ').")":
									"\nAND 0=1";
	}else{
		$where .= count($allowed) ? "\nAND task_id IN (".implode($allowed, ', ').")":"";
	}
	unset($allowed);

	break;
	
	
case 'users':
	$title = 'User';
	$select = "user_id,CONCAT_WS(' ',user_first_name,user_last_name)";
	$order = 'user_first_name';
	$where = 'user_type <> 5'; 
	break;

case 'user_supervisor':
    
    if ($_GET[id] == '')
	{
	$id = '0';
	}else{
	$id = $_GET[id];
	}

	$company_id = dPgetParam( $_GET, 'company_id', 0 );
	$title = $AppUI->_('Direct report');
	$select = "user_id, concat(user_first_name,' ', user_last_name)";
	$table = "users";
	$order = 'user_first_name';
	$where = "user_type <> 5 AND user_status = '0' AND user_company = $company_id AND user_id != ".$id;

	//Selecciono por defecto el owner del departamento al cual pertenece el usuario
	$dept_id = dPgetParam( $_GET, 'dept_id', 0 );
	$sql="SELECT dept_owner  FROM departments WHERE dept_id = '$dept_id' ";

	$selected=db_loadResult( $sql );

	break;
	
case 'bugs':
	require_once( $AppUI->getModuleClass( 'projects' ) );
	$nosqlquery = true;
	$title = 'Bug';
	// obtengo los bugs que puede actualizar
	$btfilter = array();
	$btfilter['show_category'] 		= "any";
	$btfilter['show_severity']	 	= "any";
	$btfilter['show_status'] 		= "any";
	$btfilter['per_page'] 			= "10000";
	$btfilter['highlight_changed'] 	= "6";
	$btfilter['hide_closed'] 		= "";
	$btfilter['reporter_id']		= "any";
	$btfilter['handler_id'] 		= $AppUI->user_id;
	$btfilter['sort'] 				= "last_updated";
	$btfilter['dir']		 		= "DESC";
	$btfilter['start_month']		= "";
	$btfilter['start_day'] 			= "";
	$btfilter['start_year'] 		= "";
	$btfilter['end_month'] 			= "";
	$btfilter['end_day']			= "";
	$btfilter['end_year']			= "";
	$btfilter['search']				= "";
	$btfilter['hide_resolved'] 		= "";
	include("bug_inc.php");
	
	$bugs_assigned = $btrows;
	$list_assigned = array_keys($bugs_assigned);
	if (count($bugs_assigned)){
		$includelist["separator_1"] = "----------> ".$AppUI->_("My Bugs");
		foreach($bugs_assigned as $bid => $brow){
			if ($bug_project==0 || $bug_project == $brow["project_id"])
				$includelist[$bid] =$brow["bug_id"]." - ". $brow["summary"];
		}	
		$includelist["separator_2"] = "----------> ".$AppUI->_("Other Bugs");
	}
	$btrows = array();
	$btfilter['handler_id'] 		= "any";
	include("bug_inc.php");
	
	
	$bug_project = dPgetParam( $_GET, 'bug_project', 0 );
	if (count($btrows))
		foreach($btrows as $bid => $brow){
			if (($bug_project==0 || $bug_project == $brow["project_id"]) && 
				!in_array($bid, $list_assigned))
				$includelist[$bid] = $brow["bug_id"]." - ". $brow["summary"];
		}
	//echo "<pre>";var_dump($btrows);echo "</pre>";
	if ($bug_project!= 0 ){
		$prjobj = new CProject();
		$prjobj->load($bug_project);
		$pretitle = $AppUI->_( "Project" ).": ". $prjobj->project_name;
	}
	break;
	default:
	$ok = false;
	break;
}

if (!$ok) {
	echo "Incorrect parameters passed\n";
	if ($debug) {
		echo "<br />callback = $callback \n";
		echo "<br />table = $table \n";
		echo "<br />ok = $ok \n";
	}
} else {
	if (!$nosqlquery){

		if ($table !="project")
		{
		$sql = "SELECT DISTINCT $select FROM $table";
		$sql .= $where ? " WHERE $where" : '';
		$sql .= $order ? " ORDER BY $order" : '';
		}
        else
		{
				$allowed = array();

				
				//el SYSADMIN siempre puede ver todos los proyectos
				if ($AppUI->user_type == 1){
					$sql = "SELECT project_id,project_name, -1 permission_value"
						. "\nFROM projects "
						. ($orderby ? "\nORDER BY $orderby" : '');	
					
				}
				else
			    {

				//obtengo los proyectos en donde el usuario es responsable, administrador o usuario del proyecto
				$sql = "
				select project_id from projects where project_owner = $AppUI->user_id
				union
				select project_id from project_owners where project_owner = $AppUI->user_id
				union
				select project_id from project_roles where  role_id = 2 and user_id = $AppUI->user_id
				";
			  	//echo "<pre>$sql</pre>";		
				$allowed =  db_loadColumn($sql);		
				
				$sql = "select project_company from projects  where project_id IN (" . implode( ',', $allowed ) . ")";
				
				$companies = (count($allowed) > 0 ? db_loadColumn($sql) : array("-1"));	

				$sql= "SELECT DISTINCT p.project_id,p.project_name
				FROM task_permissions AS tp
						INNER JOIN task_permission_items AS tpi
							ON (tp.task_permission_on=tpi.item_id)
						INNER JOIN projects AS p
							ON (tp.task_project=p.project_id)
				WHERE task_user_id = $AppUI->user_id
					AND task_permission_value <> 0
					AND project_id IN (" . implode( ',', $allowed ) . ")";

				}
		}


		//echo "<pre>$sql</pre>";

		if($_GET[table] == 'user_supervisor' ){
		$list = arrayMerge( array( -1=>$AppUI->_("Not Supervised")), db_loadHashList( $sql ) );
                       
		}else{
		$list = db_loadHashList( $sql );
		}
		//echo "<pre>";var_dump($list);echo "</pre>";
		//echo db_error();
	}else{
		$list = arrayMerge( array( 0=>''), $includelist );
	}
	
	$keys = array_keys($list);
	for($i = 0 ; $i<count($list); $i++){
		$key =$keys[$i];
		$posttxt = strlen($list[$key]) >= $maxchars ? "..." : "";
		$list[$key] = substr($list[$key], 0, $maxchars).$posttxt;
	}

?>
<script language="javascript">
	function setClose(){
		var list = document.frmSelector.list;
		var key = list.options[list.selectedIndex].value;
		var val = list.options[list.selectedIndex].text;

	if (key.indexOf("separator") == -1){
			window.opener.<?php echo $callback;?>(key,val);
			window.close();
		}
	}
</script>
<br>
<table cellspacing="0" cellpadding="0" border="0" width="100%" height="100%">
    <tr>
        <td align="center" valign="bottom">
<table cellspacing="0" cellpadding="3" border="0">
<form name="frmSelector">
<tr>
	<td colspan="2">
<?php
	if ($pretitle <>"")
		echo $AppUI->_( $pretitle )."<br />";
	echo $AppUI->_( 'Select' ).' '.$AppUI->_( $title ).':<br />';
  //echo "<pre>";var_dump($list);echo "</pre>";
    if (count($list)=='1')
	{ 
    $list[0]= '';
    $selected = '-10';
	}

	if (count( $list ) > 1) {
		echo arraySelect( $list, 'list', ' size="8"', $selected,'','','350px' );
?>
	</td>
</tr>
<tr>
	<td>
		<input type="button" class="button" value="<?php echo $AppUI->_( 'cancel' );?>" onclick="window.close()" />
<?php
	} else {
		echo "<hr>".$AppUI->_( "There is no available")." ".$AppUI->_( UCFirst($table) )." ".$AppUI->_( " to select.");
		?><br><br><input type="button" class="button" value="<?php echo $AppUI->_( 'back' );?>" onclick="window.close()" /><?
	}
?>
	</td>
	<td align="right">
	<?if (count( $list ) > 1) {
		?><input type="button" class="button" value="<?php echo $AppUI->_( 'Select', UI_CASE_LOWER );?>" onclick="setClose()" /><?
	}?>
	</td>
</tr>
</form>
</table>
        </td>
    </tr>
</table>

<?php } ?>
