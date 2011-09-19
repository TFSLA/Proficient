<?php
global 	$canEdit, $calendar_type, $calendar_id,$company_id, $project_id, 
		$user_id, $default_working_days, $calendar_types,$m,$a;

$cal = new CCalendar();

$calendar_id = isset($calendar_id) ? $calendar_id : 0;

/*
echo "<pre>
Calendar Type 	= $calendar_type
Calendar Id 	= $calendar_id
Company Id 		= $company_id
Project Id 		= $project_id
User Id			= $user_id
</pre>";
*/


if ( !$cal->load( $calendar_id ))
{
	$AppUI->setMsg( "Calendar: InvalidId", UI_ERROR_MSG );
	$AppUI->redirect();
}	
// cargo los días del calendario
$cal->loadCalendarDays();
	//var_dump($cal->_calendar_days);

$company_id = $cal->calendar_company ;
$project_id = $cal->calendar_project;
$user_id = $cal->calendar_user;
if ($company_id > 0)
	$calendar_type = 1;
else if ($project_id > 0)
	$calendar_type = 2;
else if ($user_id > 0)
	$calendar_type = 3;	
else 
	$calendar_type = 0;	

$from_date = new CDate($cal->calendar_from_date);	


$cal_config = $calendar_types[$calendar_type];	


if ($calendar_type > 0 ){
	$sql = "select 
			c1.company_start_time 'from', 
			c1.company_end_time 'to', 
			".$cal_config["name"]." 'name'
			from companies c1, ".$cal_config["table"]."
			where c1.company_id = ".$cal_config["table"].".".$cal_config["field_link"]."
			and   ".$cal_config["table"].".".$cal_config["field_id"]." = ".$$cal_config["field_id"];
	//echo "<pre>$sql</pre>S";

	$list = db_loadList($sql);
	$default_shift_from = $list[0]["from"];
	$default_shift_to = $list[0]["to"];
	$default_name = $list[0]["name"];
}else{
	$default_shift_from = intval( substr($AppUI->getConfig('cal_day_start'), 0, 2 ) );
	$default_shift_to   = intval( substr($AppUI->getConfig('cal_day_end'), 0, 2 ) );
	$default_name = "";	
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
if (!getDenyRead("admin") || $user_id == $AppUI->user_id)
{
	$arrUserTemp = array(
					"?m=admin&a=addeditcalendar&calendar_id=$calendar_id"=>"edit work calendar",
					"?m=admin&a=addeditcalendar&user_id=$user_id"=>"add work calendar"
					);

	$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);
}	

if( $AppUI->user_type == '1' OR $user_id==$AppUI->user_id OR !getDenyRead("admin"))
{
	$arrUserTemp = array("?m=system&a=addeditpref&user_id=$user_id"=>"edit preferences");
	$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);
	$arrUserTemp = array("?m=admin&a=addedituser&user_id=".$user_id=>"edit personal information");
	$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);
}

$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);
	
if (!getDenyRead("hhrr") || $user_id == $AppUI->user_id)
{
	$arrUserTemp = array(
					"?m=hhrr&a=addedit&tab=1&id=".$user_id=>"edit hhrr information"
					);

	$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);
}

$urlCalendar = 
$arrUserTemp = array("?m=admin&a=calendars&user_id=".$user_id.($_GET["project_id"] > 0 ? "&project_id=".$_GET["project_id"] : "")=> "work calendar");
$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);

if($user_id == $AppUI->user_id)
{
	$arrUserTemp = array("javascript: popChgPwd();"=>"change password");
					
	echo("<script language=\"javascript\">");
	echo("function popChgPwd() {");
	echo("window.open( './index.php?m=public&a=chpwd&dialog=1&suppressLogo=1', 'chpwd', 'top=250,left=250,width=350, height=220, scollbars=false' );");
	echo("}");
	echo("</script>");

	$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);
}

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');
$tf12hr= stristr($tf, "%p");


$headerBlock = array(
"0"=>array(
	"title"=>"System",
	"icon"=>"system_admin.gif",
	"crumbs"=>array(
		"?m=system"=> "system admin",
		"?m=system&a=calendars"=> "calendars",
		"?m=system&a=addeditcalendar&calendar_id=$calendar_id"=>"edit",
		"?m=system&a=addeditcalendar"=>"add"
		)),
"1"=>array(
	"title"=>"Companies",
	"icon"=>"handshake.gif",
	"crumbs"=>array(
		"?m=companies"=> "list companies",
		"?m=companies&a=view&company_id=$company_id"=> "view company",
		"?m=companies&a=calendars&company_id=$company_id"=> "calendars",
		"?m=companies&a=addeditcalendar&calendar_id=$calendar_id"=>"edit",
		"?m=companies&a=addeditcalendar&company_id=$company_id"=>"add"		
		)),		
"2"=>array(
	"title"=>"Projects",
	"icon"=>"projects.gif",
	"crumbs"=>array(
		"?m=projects"=> "list projects",
		"?m=projects&a=view&project_id=$project_id"=> "view project",
		"?m=projects&a=calendars&project_id=$project_id&tab=7"=> "calendars",
		"?m=projects&a=addeditcalendar&calendar_id=$calendar_id"=>"edit",
		"?m=projects&a=addeditcalendar&project_id=$project_id"=>"add"
		)),				
"3"=>array(
	"title"=>"Users",
	"icon"=>"user_management.gif",
	"crumbs"=>$arrUser
		)
); 

$head = $headerBlock[$calendar_type];
$title = "View Calendar";
// setup the title block

$titleBlock = new CTitleBlock( $title, $head["icon"], $m, "$m.$a" );
foreach($head["crumbs"] as $linkcrumb => $titlecrumb ){
	$isAddEdit = (strpos($titlecrumb, "add") > 0 || strpos($titlecrumb, "edit") > 0);
	if (!$isAddEdit || $isAddEdit && $canEdit){
		$titleBlock->addCrumb( $linkcrumb,$titlecrumb );
	}
}
if (($canEdit && $cal->canDelete())&&($cal->calendar_status=="0")) {
	$titleBlock->addCrumbDelete( 'delete', $canEdit, $msg );
}
$titleBlock->show();


?>
<script language="javascript">
function delIt() {
	if (confirm( "<?=$AppUI->_('doDeleteCalendarAdvice');?>" )) {
		document.frmDelete.submit();
	}
}
</script>
<table cellspacing="2" cellpadding="1" border="0" width="100%" class="std">
<form name="frmDelete" action="" method="post">
	<input type="hidden" name="dosql" value="do_calendar_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="calendar_id" value="<?php echo $calendar_id;?>" />
</form>
<tr>

	<td align="right" style="font-weight: bold;" width="15%">
		<?php echo $AppUI->_("Name");?></td>
	<td class="hilite" width="35%">
		<?php echo $cal->calendar_name;?></td>			
	<td align="right" style="font-weight: bold;" width="15%">
		<?php echo $AppUI->_('Start Date');?></td>
	<td nowrap="nowrap" class="hilite" width="35%">
		<?php echo $from_date->format( $df ) ;?></td>	
</tr>
<tr>
	<td align="right" style="font-weight: bold;" width="15%">
		<?php echo $AppUI->_($cal_config["Label"]);?></td>
	<td class="hilite" width="35%">
		<?php echo $default_name;?></td>			
	<td align="right" style="font-weight: bold;" width="15%">
		<?php echo $AppUI->_('Status');?></td>
	<td nowrap="nowrap" class="hilite" width="35%">
		<?php echo $AppUI->_(($cal->calendar_status == "1"?"Active":"Inactive")) ;?></td>	

</tr>
<tr>
	<td colspan="97"><br>
	
	
<table width="100%" border="0" cellpadding="1" cellspacing="0" bgcolor="White">
<tr class="tableHeaderGral">
	<th align="center" colspan="2"><?php echo $AppUI->_("Shift"); ?></th>
	
<?php
foreach ($days as $day_id => $day_name) 
{
	if($isNew){
		$is_working_day = in_array($day_id, $default_working_days);
	}else{
		$is_working_day = $cal->_calendar_days[$day_id]->calendar_day_working == 1;
	}
?>
	<th width="14.28%" colspan="2" align="center">
		<?php echo $AppUI->_($day_name); ?>
	</th>
<? 
} 
?>
</tr>
<tr class="tableRowLineCell">
  <td colspan="97"></td>
</tr><?php 
for($i=1;$i<6;$i++)
{
?>
<tr class="tableRowLineCell">
  <td colspan="97"></td>
</tr>
<tr>
	<td nowrap="nowrap" rowspan="2"><?php echo "$i"; ?></td>
	<td nowrap="nowrap" ><?php echo $AppUI->_("From"); ?></td>
	<?php
	foreach ($days as $day_id => $day_name)
	{
		$from_field = "calendar_day_from_time$i";
		$from_value = $cal->_calendar_days[$day_id]->$from_field;
		$from_time = new CDate($from_value);
		$is_working_day = ($cal->_calendar_days[$day_id]->calendar_day_working == 1);
		$bgcolor = $is_working_day ? "": " bgcolor='Silver'";			
	?>
	<td colspan="2" align="center" style="border-left: 1px solid black;"<?php echo $bgcolor; ?>>
		<?php echo is_null($from_value)?"":$from_time->format($tf); ?></td>
	<?php 
	} 
	?>
</tr>
<tr bgcolor="Silver">
	<td nowrap="nowrap" ><?php echo $AppUI->_("To"); ?></td>
	<?php
	foreach ($days as $day_id => $day_name)
	{
		$to_field = "calendar_day_to_time$i";
		$to_value = $cal->_calendar_days[$day_id]->$to_field;
		$to_time = new CDate($to_value);
		$is_working_day = ($cal->_calendar_days[$day_id]->calendar_day_working == 1);
		$bgcolor = $is_working_day ? "": " bgcolor='Grey'";					
	?>
	<td colspan="2" align="center" style="border-left: 1px solid black;"<?php echo $bgcolor; ?>>
		<?php echo (is_null($to_value)?"": $to_time->format($tf)); ?></td>
	<?php 
	} 
	?>
</tr>
<?php 
} 
?>
</table>
	</td>
</tr>
</table>		