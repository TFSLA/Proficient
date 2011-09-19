<?php /* TASKS $Id: index.php,v 1.2 2009-06-19 23:24:22 pkerestezachi Exp $ */
$AppUI->savePlace();

// retrieve any state parameters
if (isset( $_POST['f'] )) {
	$AppUI->setState( 'TaskIdxFilter', $_POST['f'] );
}
$f = $AppUI->getState( 'TaskIdxFilter' ) ? $AppUI->getState( 'TaskIdxFilter' ) : 'my';


if (isset( $_POST['f2'] )) {
	$AppUI->setState( 'CompanyIdxFilter', $_POST['f2'] );
}
$f2 = $AppUI->getState( 'CompanyIdxFilter' ) ? $AppUI->getState( 'CompanyIdxFilter' ) : 'all';


if (isset( $_POST['f3'] )) {
	$AppUI->setState( 'ChannelIdxFilter', $_POST['f3'] );
}
$f3 = $AppUI->getState( 'ChannelIdxFilter' ) ? $AppUI->getState( 'ChannelIdxFilter' ) : 'all';


if (isset( $_POST['projectStatus'] )) {
	$AppUI->setState( 'ProjectStatusIdxFilter', $_POST['projectStatus'] );
}
$project_status_id = $AppUI->getState( 'ProjectStatusIdxFilter' ) != null ? $AppUI->getState( 'ProjectStatusIdxFilter' ) : 3;


if (isset( $_POST['f4'] )) {
	$AppUI->setState( 'ProjectIdxFilter', $_POST['f4'] );
}
$f4 = $AppUI->getState( 'ProjectIdxFilter' ) ? $AppUI->getState( 'ProjectIdxFilter' ) : 'all';


if (isset( $_GET['project_id'] )) {
	$AppUI->setState( 'TaskIdxProject', $_GET['project_id'] );
}
$project_id = $AppUI->getState( 'TaskIdxProject' ) ? $AppUI->getState( 'TaskIdxProject' ) : 0;


// get CCompany() to filter tasks by company
require_once( $AppUI->getModuleClass( 'companies' ) );
$obj = new CCompany();
$companies = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$filters2 = arrayMerge(  array( 'all' => $AppUI->_('All Companies') ), $companies );

// Fitro por canal
if($f2 !="all" && $f2 !="")
{
	$sql_cia = "AND p.project_company = '$f2' ";
}

$sql="SELECT distinct(p.project_canal), c.company_name FROM companies as c, projects as p WHERE p.project_canal = c.company_id ".$sql_cia." order by company_name ";

$rc=mysql_query($sql);
$channels=ARRAY( 'all' => $AppUI->_('All Channels'));
WHILE ($vec=mysql_fetch_array($rc)) { $temp1=ARRAY( $vec['project_canal']=>$vec['company_name']); $channels=arrayMerge($channels, $temp1); }



// Fitro por Proyecto 
$sql="SELECT p.project_id, p.project_name FROM project_roles AS pr INNER JOIN projects AS p ON ( pr.project_id=p.project_id) WHERE pr.user_id=".$AppUI->user_id." ORDER BY project_name;";
$rc=mysql_query($sql);
$projects=ARRAY( 'all' => $AppUI->_('All Projects'));
WHILE ($vec=mysql_fetch_array($rc)) { $temp1=ARRAY( $vec['project_id']=>$vec['project_name']); $projects=arrayMerge($projects, $temp1); }

//natcasesort($filters);


//FILL PROJECT STATUS COMBO
$projectStatusAll = array(-1 => $AppUI->_('All Status'));
$projectStatus = dPgetSysVal( 'ProjectStatus' );
$projectStatus = arrayMerge($projectStatusAll, $projectStatus);


if ( dPgetParam( $_GET, 'inactive', '' ) == 'toggle' )
	$AppUI->setState( 'inactive', $AppUI->getState( 'inactive' ) == -1 ? 0 : -1 );
$in = $AppUI->getState( 'inactive' ) == -1 ? '' : 'in';

if ($AppUI->getState( 'inactive' ) == -1){
	$titleBlock = new CTitleBlock( 'Completed Tasks', 'tasks.gif', $m, "projects.index" );
}else{
	$titleBlock = new CTitleBlock( 'All tasks', 'tasks.gif', $m, "projects.index" );	
}


$titleBlock->addCell('<form action="?m=tasks" method="post" name="companyFilter">'.arraySelect( $filters2, 'f2', 'size=1 class=text onChange="document.companyFilter.submit();"', $f2, false, false,'200px'), ' ','', '</form>');
$titleBlock->addCell('<form action="?m=tasks" method="post" name="channelFilter">'.arraySelect( $channels, 'f3', 'size=1 class=text onChange="document.channelFilter.submit();"', $f3, false, false,'200px'), ' ','', '</form>');
$titleBlock->addCell('<form action="?m=tasks" method="post" name="taskFilter">'.arraySelect( $filters, 'f', 'size=1 class=text onChange="document.taskFilter.submit();"', $f, true, false, '200 px'), '','', '</form>');
$titleBlock->addCell('<form action="?m=tasks" method="post" name="projectStatusFilter">'.arraySelect( $projectStatus, 'projectStatus', 'size=1 class=text onChange="document.projectStatusFilter.submit();"', $project_status_id, true, false, '200px' ), '','', '</form>');




if ($canEdit && $project_id) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new task').'">', '',
		'<form action="?m=tasks&a=addedit&task_project=' . $project_id . '" method="post">', '</form>'
	);
}




$titleBlock->addCrumb( "?m=tasks&a=todo", "my todo" );

if ($in !=""){
    $titleBlock->addCrumb( "?m=tasks&inactive=toggle", "show completed tasks" );
}else{
   $titleBlock->addCrumb( "?m=tasks&inactive=toggle", "show all tasks" );
}
$titleBlock->show();

// include the re-usable sub view
	$min_view = false;
	include("./modules/tasks/tasks.php");
	include ('./modules/timexp/report_to_items.php');
?>
