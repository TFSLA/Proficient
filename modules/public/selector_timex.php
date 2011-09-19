<?php /* PUBLIC $Id: selector_timex.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */

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
 case 'projec':
	$table = "project";

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


                
				// Projectos con tareas asignadas //
				$sql = "SELECT DISTINCT p.project_id,p.project_name
						FROM task_permissions 
						INNER JOIN projects AS p
						WHERE task_user_id = 3
					    AND task_permission_value <> 0 
					    AND project_id IN (" . implode( ',', $allowed ) . ")
						
						union

						SELECT DISTINCT p.project_id,p.project_name
						FROM tasks
						INNER JOIN projects AS p
						WHERE task_access = 2
				        AND task_project=p.project_id
				        AND task_project NOT IN (" . implode( ',', $allowed ) . ")";
                
			
				}

				
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

            
			// Traigo las tareas que tiene permitidas //

			$task_allowed_list=array();
			if (strtolower($task_perm)=="time"){
				$task_allowed_list = CTask::getPermissions($AppUI->user_id, $task_project, 0, 3 );
			}
			if (strtolower($task_perm)=="expense"){
				$task_allowed_list = CTask::getPermissions($AppUI->user_id, $task_project, 0, 4 );
			}
			
			$allowed = array();
			for ($i=0; $i<count($task_allowed_list);$i++){
				if ($task_allowed_list[$i]["task_permission_value"]==PERM_EDIT){
					$allowed[]=$task_allowed_list[$i]["task_id"];
				}
			
			}
            

			if(count($allowed) !=0)
	         {
					$sql = "SELECT DISTINCT task_id,task_name FROM tasks WHERE 1=1 
							AND task_project = $task_project 
							AND task_id IN (" . implode( ',', $allowed ) . ")";
			 }
             else
			{   
				 $nosqlquery = true;
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

		$list = arrayMerge( array( 0=>''), db_loadHashList( $sql ) );
		echo db_error();
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
	if (count( $list ) > 1) {

		echo arraySelect( $list, 'list', ' size="8"', 0 );
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
