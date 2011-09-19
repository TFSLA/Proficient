<?php  /* PROJECTS $Id: index.php,v 1.7 2009-07-27 14:13:29 nnimis Exp $ */
global $debuguser, $orderImage, $revertOrder;

$AppUI->savePlace();
$canRead = !getDenyRead( $m  );
$canEdit = !getDenyEdit( $m  );
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// load the companies class to retrieved denied companies
require_once( $AppUI->getModuleClass( 'companies' ) );

// Let's update project status!
if(isset($_GET["update_project_status"]) && isset($_GET["project_status"]) && isset($_GET["project_id"]) ){
	$projects_id = $_GET["project_id"]; // This must be an array
	$msgGral = "";
	foreach($projects_id as $project_id){
		$msg = "";
		$prj = new CProject();
		$prj->load($project_id);
		if ($prj->canEdit()){
			$prj->project_status = $_GET['project_status'];
			if (($msg = $prj->store())) {
				$msgGral .= $AppUI->_($msg)." - ";
			}		
		}
	}
	if ($msgGral){
		$AppUI->setMsg( $msgGral, UI_MSG_ERROR );
	}	else{
		$AppUI->setMsg( 'Project updated' , UI_MSG_OK);
	}		
	echo $AppUI->getMsg(true);
}
// End of project status update

$downImage = "<img src='./images/arrow-down.gif' border='0' alt='".$AppUI->_("Ascending")."'>";
$upImage = "<img src='./images/arrow-up.gif' border='0' alt='".$AppUI->_("Descending")."'>";
$orderImage = isset($_GET["revert"]) ? $upImage : $downImage;
$revertOrder = isset($_GET["revert"]) ? "" : "&revert=1";

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjIdxTab' ) !== NULL ? $AppUI->getState( 'ProjIdxTab' ) : 3;
$active = intval( !$AppUI->getState( 'ProjIdxTab' ) );

if (isset( $_POST['company_id'] )) {
	$AppUI->setState( 'ProjIdxCompany', intval( $_POST['company_id'] ) );
}

if (isset( $_POST['canal_id'] )) {
	$AppUI->setState( 'ProjIdxCanal', intval( $_POST['canal_id'] ) );
}


// BUG FIX: Selecting all companies didn't work
// $company_id = $AppUI->getState( 'ProjIdxCompany' ) !== NULL ? $AppUI->getState( 'ProjIdxCompany' ) : $AppUI->user_company;
$company_id = $AppUI->getState( 'ProjIdxCompany' );
$canal_id = $AppUI->getState( 'ProjIdxCanal' );


if (isset( $_GET['orderby'] )) {
	$AppUI->setState( 'ProjIdxOrderBy', $_GET['orderby'] );
}
$orderby = $AppUI->getState( 'ProjIdxOrderBy' ) ? $AppUI->getState( 'ProjIdxOrderBy' ) : 'project_end_date';

// get any records denied from viewing
$obj = new CProject();
$deny = $obj->getDeniedRecords( $AppUI->user_id );

// Task sum table
// by Pablo Roca (pabloroca@mvps.org)
// 16 August 2003

// $sql = "
// CREATE TEMPORARY TABLE tasks_sum
//  SELECT task_project,
//  COUNT(distinct task_id) AS total_tasks,
//  SUM(task_duration*task_duration_type*task_manual_percent_complete)/sum(task_duration*task_duration_type) as project_percent_complete
//  FROM tasks GROUP BY task_project
// ";

$sql = "
CREATE TEMPORARY TABLE tasks_sum
	SELECT task_project,
	IF(COUNT(distinct task_id)  = 0 , 0,
		ROUND(
				(
					SUM(
						CASE
							WHEN task_complete='1' THEN 1
							WHEN task_manual_percent_complete='100' THEN 1
							ELSE 0
						END
					)
					/
					SUM(
						CASE
							WHEN task_id!=0 THEN 1
							ELSE 0
						END
					)
				)
				*20
			)
		*5
	)
	AS project_percent_completed_tasks
	FROM tasks GROUP BY task_project
";

$tasks_sum = db_exec($sql);

// temporary My Tasks
// by Pablo Roca (pabloroca@mvps.org)
// 16 August 2003
$sql = "
CREATE TEMPORARY TABLE tasks_summy
 SELECT task_project, COUNT(distinct task_id) AS my_tasks
 FROM tasks 
 WHERE task_owner = $AppUI->user_id GROUP BY task_project
";

$tasks_summy = db_exec($sql);

// retrieve list of records
// modified for speed
// by Pablo Roca (pabloroca@mvps.org)
// 16 August 2003
/*
$sql = "
SELECT
	project_id, project_active, project_status, project_color_identifier, project_name, project_description,
	project_start_date, project_end_date, project_actual_end_date,
	project_color_identifier,
	project_company, company_name, project_status,
	tasks_sum.total_tasks,
	tasks_summy.my_tasks,
	tasks_sum.project_percent_complete,
	user_username
FROM permissions,projects
LEFT JOIN companies ON company_id = projects.project_company
LEFT JOIN users ON projects.project_owner = users.user_id
LEFT JOIN tasks_sum ON projects.project_id = tasks_sum.task_project
LEFT JOIN tasks_summy ON projects.project_id = tasks_summy.task_project
WHERE permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)"
.(count($deny) > 0 ? "\nAND project_id NOT IN (" . implode( ',', $deny ) . ')' : '')
.($company_id ? "\nAND project_company = $company_id" : '')
."
GROUP BY project_id
ORDER BY $orderby
LIMIT 0,50
";

$projects = db_loadList( $sql );
*/

include("read_projects.inc.php");

// get the list of permitted companies
$obj = new CCompany();
$companies = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$companies = arrayMerge( array( '0'=>$AppUI->_('All') ), $companies );



// setup the title block
$titleBlock = new CTitleBlock( 'Projects', 'projects.gif', $m, "$m.index" );

// ## SELECT DE COMPAÃ‘Ã?AS
$titleBlock->addCell( $AppUI->_('Company') . ':'.arraySelect( $companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id,TRUE , FALSE, '200 px' ), '',
	'<form action="?m=projects" method="post" name="pickCompany">', '');
$titleBlock->addCell();
// ## SELECT DE COMPAÃ‘Ã?AS


$obj_cia = new CCompany();
$allow = $obj_cia->getAllowedRecords( $AppUI->user_id, 'company_id');

if (count($allow) >0  )
{
  $allow = "AND company_id IN (" . implode( ',', $allow ) . ")";
}

if($company_id > 0)
{
  $sql_cia = "AND p.project_company = '$company_id' ";
}

$sql_canal = "SELECT distinct(p.project_canal), c.company_name FROM companies as c, projects as p WHERE p.project_canal = c.company_id  ".(!$allow ? "" : $allow)." ".$sql_cia." order by company_name ";

$canal = db_loadHashList( $sql_canal );
$canal = arrayMerge( array( '0'=>$AppUI->_('All') ), $canal );


$titleBlock->addCell($AppUI->_('companycanal') . ':' .arraySelect( $canal, 'canal_id', 'onChange="document.pickCompany.submit()" class="text"', $canal_id,TRUE , FALSE, '200 px' ), '',
	'', '</form>'); 


$titleBlock->addCell();



if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new project').'">', '',
		'<form action="?m=projects&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->show();

$project_types = dPgetSysVal("ProjectStatus");

$fixed_project_type_file = array("In Progress" => "vw_idx_active",
                                 "Complete"    => "vw_idx_complete",
                                 "Inactive"    => "vw_idx_archived");
// we need to manually add Archived project type because this status is defined by 
// other field (Active) in the project table, not project_status
$project_types[] = "Inactive";

// Only display the All option in tabbed view, in plain mode it would just repeat everything else
// already in the page
if ( $tab != -1 ) $project_types[] = "All";

/**
* Now, we will figure out which vw_idx file are available
* for each project type using the $fixed_project_type_file array 
*/
$project_type_file = array();

foreach($project_types as $project_type){
	$project_type = trim($project_type);
	if(isset($fixed_project_type_file[$project_type])){
		$project_file_type[$project_type] = $fixed_project_type_file[$project_type];
	} else { // if there is no fixed vw_idx file, we will use vw_idx_proposed
		$project_file_type[$project_type] = "vw_idx_proposed";
	}
}

$show_all_projects = false;
if($tab == count($project_types)-1) $show_all_projects = true;

// tabbed information boxes
$tabBox = new CTabBox( "?m=projects&orderby=project_name", "{$AppUI->cfg['root_dir']}/modules/projects/", $tab );
foreach($project_types as $project_type){
	$project_type = trim($project_type);
	$tabBox->add($project_file_type[$project_type], $project_type);
}

$tabBox->show();

if (isset($debuguser)){	
	$objProject = new CProject();
	$objProject->load($project_id);


		//muestra tabla de permisos unificada
		$tbl= "";
		$perm = $objProject->projectPermissions($debuguser);
		
			$tbl.="<tr><td>$project_id</td>";
			for($ii = 0; $ii <= count($perm) ; $ii++){
				$valor=$perm[$ii] ? $perm[$ii] : "&nbsp;" ;
				$tbl.="<td>";
				if (is_array($valor))
					foreach ($valor as $key=>$val){$tbl.=" $key = $val<br>";}
				else
					$tbl.="$valor";
				$tbl.="</td>";
			}
			$tbl.="</tr>";

		$tit = "<tr><td>Proyecto</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td></tr>";
		echo "<h1>perm de Proyecto</h1>";
		echo "<table border=1> $tit $tbl </table>";		
echo "Can edit: $canEdit - ";
echo "Can Read Details: $canReadDetails - ";
echo "Can Read Ec. Values: $canReadEcValues - ";
echo "Can Read Company: $canReadCompany ";		


		//muestra tabla de permisos unificada
		$tbl= "";
		$perms = CProject::projectPermissions($debuguser);
	
		foreach ($perms as $prjid=>$perm){
			$tbl.="<tr><td>$prjid</td>";
			for($ii = 1; $ii <= count($perm) ; $ii++){
				$valor=$perm[$ii] ? $perm[$ii] : "&nbsp;" ;
				$tbl.="<td>";
				if (is_array($valor))
					foreach ($valor as $key=>$val){$tbl.=" $key = $val<br>";}
				else
					$tbl.="$valor";
				$tbl.="</td>";
			}
			$tbl.="</tr>";
		}
		$tit = "<tr><td>Proyecto</td><td>1</td><td>2</td><td>3</td><td>4</td><td>5</td></tr>";
		echo "<h1>perm de Proyecto avanzados</h1>";
		echo "<table border=1> $tit $tbl </table>";	
echo "<h2>Assigned projects</h2>";		
$assprj = CUser::getAssignedProjects($debuguser);
var_dump($assprj);

echo "<h2>Allowed projects</h2>";
$prjs = $objProject->getAllowedRecords($debuguser, "project_id, project_name");
var_dump($prjs);
}
?>