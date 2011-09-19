<?php 
global 	$canEdit, $calendar_type, $calendar_id,$company_id, $project_id, 
		$user_id, $default_working_days, $calendar_types,$m,$a;

require_once( $AppUI->getModuleClass( "admin" ) );

if (!isset($calendar_type))
	$calendar_type = 0;
$cal_config = $calendar_types[$calendar_type];


//cambiar el estado de varios calendarios
if ($_POST["updatestatus"]=="1"){
	
	for($i=0; $i < count($_POST["calendar_id"]); $i++){
		
		$calobj = new CCalendar();
		$calobj->load($_POST["calendar_id"][$i]);
		$calobj->calendar_status = $_POST["status"];
		$AppUI->setMsg( 'Calendar' );
		if (($msg = $calobj->store())) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
		}else{
			$AppUI->setMsg( 'updated' , UI_MSG_OK, true );
		}
		unset($calobj);
	}
	echo $AppUI->getMsg(true);
}

$cal_config = $calendar_types[$calendar_type];	

//si hay filtros
/*
if (isset($_GET["fid"])){
	$$cal_config["field_id"] = $_GET["fid"];
}*/

//si no es calendario del sistema
if ($calendar_type > 0 ){
	//y se agrega un nuevo calendario debe estar la variable id
	if ($isNew && !isset($$cal_config["field_id"])){
		$AppUI->setMsg( "Calendar: Missing ".$cal_config["field_id"], UI_ERROR_MSG );
		$AppUI->redirect();	
	}
}	




$AppUI->savePlace( );

$days = array(
	1=>"Sunday",
	2=>"Monday",
	3=>"Tuesday",
	4=>"Wednesday",
	5=>"Thursday",
	6=>"Friday",
	7=>"Saturday"
);

//add crumb from admin module
if( $AppUI->user_type == '1' OR $user_id==$AppUI->user_id OR !getDenyRead("admin"))
{
	$arrUserTemp = array("?m=system&a=addeditpref&user_id=$user_id"=>"edit preferences");
	$arrUser = array_merge((array) $arrUser, (array)$arrUserTemp);
	$arrUserTemp = array("?m=admin&a=addedituser&user_id=".$user_id=>"edit personal information");
	$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);
}

$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);
	
if (!getDenyRead("hhrr") || $user_id == $AppUI->user_id)
{
	$arrUserTemp = array("?m=hhrr&a=addedit&tab=1&id=".$user_id=>"edit hhrr information");

	$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);
}

if($user_id == $AppUI->user_id)
{
	$arrUserTemp = array("javascript: popChgPwd();"=>"change password");
					
	echo("<script language=\"javascript\">");
	echo("function popChgPwd() {");
	echo("window.open( './index.php?m=public&a=chpwd&dialog=1&suppressLogo=1', 'chpwd', 'top=250,left=250,width=350, height=220, scollbars=false' );");
	echo("}");
	echo("</script>");

	$arrUser = array_merge($arrUser, $arrUserTemp);
}


$headerBlock = array(
"0"=>array(
	"title"=>"System",
	"icon"=>"system_admin.gif",
	"crumbs"=>array(
		"?m=system"=> "system admin"
		)),
"1"=>array(
	"title"=>"Company",
	"icon"=>"handshake.gif",
	"crumbs"=>array(
		"?m=companies"=> "list companies",
		"?m=companies&a=view&company_id=$company_id"=> "view company"
		)),		
"2"=>array(
	"title"=>"Project",
	"icon"=>"projects.gif",
	"crumbs"=>array(
		"?m=projects"=> "list projects",
		"?m=projects&a=view&project_id=$project_id"=> "view project"
		)),				
"3"=>array(
	"title"=>"User",
	"icon"=>"user_management.gif",
	"crumbs"=>$arrUser
		)
);

$filter_list=array();
switch($calendar_type){
case "1":
	$tmpobj= new CCompany();
	$tmp_list = $tmpobj->getCompanies($AppUI->user_id);
	for ($i=0; $i<count($tmp_list); $i++){
		if (!getDenyEdit("companies", $tmp_list[$i]["company_id"]))
			$filter_list[$tmp_list[$i]["company_id"]]=$tmp_list[$i]["company_name"];
	}
	break;
case "2":
	$tmp_list = CProject::getAllowedRecords($AppUI->user_id);
	if (count($tmp_list))
	foreach ($tmp_list as $pid=>$val){
		$tmpobj= new CProject();
		$tmpobj->load($pid);
		if ($tmpobj->canEdit())
			$filter_list[$pid]=$tmpobj->project_name;
		unset ($tmpobj);
	}
	break;	
case "3":
	$filter_list=cuser::getEditableUsers($AppUI->user_id);
	break;

}
	/*
	echo "<pre>";
	var_dump($filter_list);
	echo "</pre>";*/
	
$head = $headerBlock[$calendar_type];
$title = $head["title"]." Calendars";
// setup the title block

$titleBlock = new CTitleBlock( $title, $head["icon"], $m, "$m.$a" );
foreach($head["crumbs"] as $linkcrumb => $titlecrumb ){
	$isAddEdit = (strpos($titlecrumb, "add") > 0 || strpos($titlecrumb, "edit") > 0);
	if (!$isAddEdit || $isAddEdit && $canEdit){
		$titleBlock->addCrumb( $linkcrumb,$titlecrumb );
	}
}



if (($calendar_type>0)&&($calendar_type!="3")){    
	$titleBlock->addCell(  $AppUI->_($head["title"])."&nbsp;"," width='100%' align=\"right\"" );
	
	$titleBlock->addCell(
		arraySelect( $filter_list, $cal_config["field_id"], 'size=1 class=text 
		onChange="javascript:
		var fid = this.options[this.selectedIndex].value;
		var url = \'index.php?'.filterQueryString($cal_config["field_id"]).'&'.$cal_config["field_id"].'=\' + fid;
		document.location = url;"',$$cal_config["field_id"], false )."&nbsp;", '',
		'<form action="?'.filterQueryString('fid').'" method="get" name="calendarFilter">'
	);
}

$titleBlock->show();



if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'CalendarIdxTab', $_GET['tab'] );
}
$tab = defVal( $AppUI->getState( 'CalendarIdxTab' ), 0 );

$tab_status = array(
"0" => "1",
"1" => "0",
"2" => "-1");
$calendar_status = $tab_status[$tab];


$query_string = filterQueryString("tab");
$tabBox = new CTabBox( "?".$query_string, "{$AppUI->cfg['root_dir']}/modules/system/", $tab );
$tabBox->add('calendars_list', "Calendars");
//$tabBox->add('calendars_list', "Inactive");
//$tabBox->add('calendars_list', "All");

if($calendar_type>0){
$tabBox->add('exceptions', "Exceptions");
}

if($user_id > 0)
{
	$arrUserData = CUser::getUsersFullName(array($user_id));
	?>

	<table cellspacing="2" cellpadding="1" border="0" width="100%" class="std">
		<tr>
			<td align="left" style="font-weight: bold;" width="1%">
				<?php echo $AppUI->_($cal_config["Label"]);?>
			</td>
			<td class="hilite" width="35%" align="left">
				<?php echo ($arrUserData[0]['fullname']);?>
			</td>		
		</tr>
	</table>
<? } ?>

<? $tabBox->show(); ?>