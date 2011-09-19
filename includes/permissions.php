<?php /* INCLUDES $Id: permissions.php,v 1.1 2009-05-19 21:15:31 pkerestezachi Exp $ */
/*
 * This page handles permissions
 * 
 * Permissions Theory:
 * 
 * Permissions are propagated and overwritten from most general
 * to most specific items.
 * 3 type of permissions are stored in the DB:
 * - read
 * - edit
 * - denied
 *
 * This way, if you grant edit permissions on a project and
 * deny access to an item of this project, you will be able
 * to access any item excluding the one you denied.
 * 
 * Special asumptions:
 * - if permissions array is empty => a user has no permissions at all (inactive)
 * - if permissions were granted on a module => the same goes for its items
 *
 * Propagations:
 * - all modules => all modules
 * - module m => items of m
 * - project p => tasks, files, events of project p
 */

// Permission flags used in the DB

define( 'PERM_DENY', '0' );
define( 'PERM_EDIT', '-1' );
define( 'PERM_READ', '1' );
define( 'PERM_CHANGE', '-2' );
define( 'PERM_ALL', '-1' );

// TODO: getDeny* should return true/false instead of 1/0

function getReadableModule() {
	$sql = "SELECT mod_directory FROM modules WHERE mod_active > 0 ORDER BY mod_ui_order";
	$modules = db_loadColumn( $sql );
	foreach ($modules as $mod) {
		if (!getDenyRead($mod)) {
			return $mod;
		}
	}
	return null;
}

/**
 * This function is used to check permissions.
 */
function checkFlag($flag, $perm_type, $old_flag) {
	if($old_flag) {
		return (
				($flag == PERM_DENY) ||	// permission denied
				($perm_type == PERM_EDIT && $flag == PERM_READ)	// we ask for editing, but are only allowed to read
				) ? 0 : 1;
	} else {
		if($perm_type == PERM_READ) {
			return ($flag != PERM_DENY)?1:0;
		} else {
			// => $perm_type == PERM_EDIT
			return ($flag == $perm_type)?1:0;
		}
	}
}

/**
 * This function checks certain permissions for
 * a given module and optionally an item_id.
 * 
 * $perm_type can be PERM_READ or PERM_EDIT
 */
function isAllowed($perm_type, $mod, $item_id = 0) {
	GLOBAL $perms, $AppUI;
	$allowed =false;
	
	/*** Administrator permissions ***/
	if ($AppUI->user_type == 1) return 1;
	
	/*** Special hardcoded permissions ***/
	
	if ($mod == 'public') return 1;
	if ($mod == 'functionalArea') return 1;
	
	if ( $mod == 'tasks' ){	
		$mod="projects";
	}

//Si es algun archivo de SecurityCenter pregunto x los permisos de admin
	if ( $mod == 'SecurityCenter' ){	
		$mod="admin";
	}
	
	/*** Manually granted permissions ***/

	// TODO: Check this
	// If $perms['all'] or $perms[$mod] is not empty we have full permissions???
	// If we just set a deny on a item we get read/edit permissions on the full module.
	
	if($perm_type!=PERM_EDIT)
	   $allowed = ! empty( $perms['all'] ) | ! empty( $perms[$mod] );
	
	// check permission on all modules
	if ( isset($perms['all']) && $perms['all'][PERM_ALL] ) {
		$allowed = checkFlag($perms['all'][PERM_ALL], $perm_type, $allowed);
	}

	// check permision on this module
	if ( isset($perms[$mod]) && isset($perms[$mod][PERM_ALL]) ) {
		$allowed = checkFlag($perms[$mod][PERM_ALL], $perm_type, $allowed);
	}
	
    // check permision for the item on this module
	if ($item_id > 0) {
		if ( isset($perms[$mod][$item_id]) ) {
			$allowed = checkFlag($perms[$mod][$item_id], $perm_type, $allowed);
		}
	}
		
	/*** Permission propagations ***/
			
	// if we have access on the project => we have access on its tasks
/*	if ( $mod == 'tasks' ) {
		//$allowed = isAllowed( $perm_type, "projects", 6, $allowed );
		if ( $item_id > 0 ) {			
			// get task's project id
			$sql = "SELECT task_project FROM tasks WHERE task_id = $item_id";
			$project_id = db_loadResult($sql);
			
			// check task's permission
			$allowed = isAllowed( $perm_type, "projects", $project_id, $allowed );
		}
	}

	if ( $mod == 'projects' ) {
		if ( $item_id > 0 ) {	
		
			$perms = CProject::projectPermissions($AppUI->user_id, $item_id);	
			switch ($perm_type){
			case PERM_READ:
				$allowed=$allowed &&  $perms[1]!= PERM_DENY;
				break;
			case PERM_WRITE:
				$prjObj = new CProject();
				$prjObj->load($item_id);			
				$allowed=$allowed && (	$perms[1]== PERM_CHANGE || 
									($perms[1]==PERM_EDIT && $prjObj->project_owner == $AppUI->user_id)
									);
				break;
			}
				
		}
	}
	*/
	/*** TODO: Specificaly denied items ***/
	// echo "$perm_type $mod $item_id $allowed<br>";
	
	return $allowed;
}

function getDenyRead( $mod, $item_id = 0 ) {
	
	$p =!isAllowed(PERM_READ, $mod, $item_id);
	//echo "Modulo: ".$mod." : $p<br>";
	
	return !isAllowed(PERM_READ, $mod, $item_id);
}

function getDenyEdit( $mod, $item_id=0 ) {
	return !isAllowed(PERM_EDIT, $mod, $item_id);
}

function canLogTask( $task_id){
	GLOBAL $AppUI;
	$sql = "
	SELECT '1' rdo FROM user_tasks 
	WHERE user_id = ".$AppUI->user_id."
	AND   task_id = ".$task_id;
	$lst = db_loadList($sql);
	return $lst[0]['rdo'] == '1'? true : false;	
}

function getCalendarDelegators(){
	GLOBAL $AppUI;
	/*$sql = "
SELECT u.user_id, concat( u.user_first_name,' ', u.user_last_name ) name
FROM  users u
WHERE user_id =$AppUI->user_id
UNION
SELECT u.user_id, concat( u.user_first_name,' ', u.user_last_name ) name
FROM user_delegates ud
NATURAL  JOIN users u
WHERE delegate_id =$AppUI->user_id";*/
	$sql="
SELECT u.user_id, concat( u.user_first_name,' ', u.user_last_name ) name
FROM  users u
WHERE user_id =$AppUI->user_id
UNION
SELECT u.user_id, concat( u.user_first_name,' ', u.user_last_name ) name
FROM permissions ud
JOIN users u on permission_user = user_id 
WHERE permission_item =$AppUI->user_id
AND		permission_value in ( -1 , 1)
AND		permission_grant_on = 'calendar'";
	return db_loadHashList($sql);
}

function getUserCompany($user_id){
	$sql = "
SELECT user_company
FROM `users` 
WHERE user_id = $user_id ";
	$list = db_loadList($sql);
	return $list[0][user_company];
}

function getUserName($user_id){
	$sql = "
SELECT concat( u.user_first_name,' ', u.user_last_name ) name
FROM `users` u 
WHERE user_id = $user_id ";
	$list = db_loadList($sql);
	return $list[0][name];
}
/**
 * Return a join statement and a where clause filtering
 * all items which for which no explicit read permission is granted.
 */
function winnow( $mod, $key, &$where, $alias = 'perm' ) {
	GLOBAL $AppUI, $perms;

	// TODO: Should we also check empty( $perms['all'] ?
	if( ! empty( $perms[$mod] ) ) {
		// We have permissions for specific items => filter items
		$sql = "\n  LEFT JOIN permissions AS $alias ON $alias.permission_item = $key ";
		if ($where) {
			$where .= "\n  AND";
		}
		$where .= "\n	$alias.permission_grant_on = '$mod'"
			. "\n	AND $alias.permission_value != " . PERM_DENY
			. "\n	AND $alias.permission_user = $AppUI->user_id";
		return $sql;
	} else {
		if (!$where) {
			$where = '1=1';  // dummy for handling 'AND $where' situations
		}
		return ' ';
	}		
}

require_once("{$AppUI->cfg['root_dir']}/includes/loadperms.php");

//Devuelve True si el usuario que se pasa es Administrador
function es_admin($user_id)
{
	$sql = "SELECT user_type FROM users where user_id = $user_id;";
	$resultado = mysql_query( $sql );
	//echo "<br>$sql<br>";
	if ( $resultado == FALSE)
		return FALSE;
		
	//return (mysql_result($resultado, 0) == 1);
	return (mysql_result($resultado, 0) == 1);
}

//Si yo soy admin 0 trato de editar a alguien que no lo es O un usuario comun edita a otro comun 0 que me edite a mi mismo
function edit_admin($user_id)
{
	global $AppUI;
	return ( $AppUI->user_type == 1 OR !es_admin($user_id) OR ( !es_admin($user_id) AND $AppUI->user_type != 1) OR $AppUI->user_id == $user_id);
}
?>