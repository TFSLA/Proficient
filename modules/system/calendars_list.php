<?php
global 	$canEdit, $calendar_type, $calendar_id,$company_id, $project_id, 
		$user_id, $default_working_days, $cal_config,$m,$a, $calendar_status, $calendar_status_list;

$list_all = "-1";
$calendar_status == "-1";

if(($_GET[status]!="-1")&&($_GET[status]!=""))
{   
	$params["calendar_status"] = $_GET[status];
}

$project_id = $_GET['project_id'];

if ($calendar_type >0){
	$strphp ='$sql = CCalendar::'.$cal_config["method"]."SQL(".$$cal_config["field_id"].', $params'.");";
}else{
	$strphp ='$sql = CCalendar::'.$cal_config["method"]."SQL( ".'$params'.");";
}

eval($strphp);

$dp = new DataPager($sql, "cals");
$dp->showPageLinks = true;
$calendars = $dp->getResults();
$rn = $dp->num_result;
$pager_links = $dp->RenderNav();


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

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');
$tf12hr= stristr($tf, "%p");

if($_POST[action]=="updatestatus")
{
  foreach($calendars as $row)
	{
        if ($row[calendar_id]!=$_POST[status_ch])
		{
		$query = "UPDATE calendar SET calendar_status='0' WHERE calendar_id='$row[calendar_id]'";
		}
		else
		{
		$query = "UPDATE calendar SET calendar_status='1' WHERE calendar_id='$_POST[status_ch]'";
		}
        $sql = mysql_query($query);

	}
}

?>
<script language="javascript">
function delIt(id) {
	if (confirm( "<?=	$AppUI->_('doDeleteCalendarAdvice');?>" )) {
		document.frmDelete.calendar_id.value = id;
		document.frmDelete.submit();
	}
}
</script>

<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">
<tr>
    <td>
	  <form action="" method="get" name="calendarFilter">
		  <input type="hidden" name="m" value="<?=$m;?>" />
		  <input type="hidden" name="a" value="<?=$a;?>" />
		  <input type="hidden" name="user_id" value="<?=$user_id;?>" />
		  <? if($project_id){ ?>
		  	<input type="hidden" name="project_id" value="<?=$project_id;?>" />
		  <? } ?>
		  <?
            $filter_status = $AppUI->_('Status')."&nbsp;";

			$arr_status = array(
			"1" => $AppUI->_('Active'),
			"0" => $AppUI->_('Inactive'),
			"-1" => $AppUI->_('All'));

			if($_GET[status]=="")
			{
			 $status = "1";
			}
			else{
			 $status = $_GET[status];
			}

			$filter_status .= arraySelect( $arr_status , "status", 'size=1 class=text 
					onChange="javascript:this.form.submit();"',$status, false,'','80px' );

			echo $filter_status;
          ?>
	  </form>
	</td>
<?if ($canEdit){ ?>

	<td align="right" valign="top">
	   <input type="button" value="<?php echo $AppUI->_( 'Add' )?>" class="button" onclick="javascript: document.location = '<?php echo $PHP_SELF."?m=$m&a=addeditcalendar&".$cal_config["field_id"]."=".$$cal_config["field_id"];
	   ?>';" /></td>
</tr>
<?php } ?>

</table>

<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">
<form name="frmDelete" action="" method="post">
	<input type="hidden" name="dosql" value="do_calendar_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="calendar_id" value="" />
</form>

<form name="frmUpdate" action="" method="post">
	<input type="hidden" name="action" value="updatestatus" />
<tr class="tableHeaderGral">
	<th width="50px">&nbsp;</th>
	<th width="150px" align="left"><?php echo $AppUI->_( 'Name' );?></th>
	<th align="left"><?php echo $AppUI->_( 'Working Days' );?></th>
<?php if ($list_all) { ?>	
	<th align="left"><?php echo $AppUI->_( 'Status' );?></th>
<?php }
	if ($canEdit){ ?>
	<th></th>	
<?php } ?>
</tr>
<?php if (count($calendars)==0) { ?>
<tr>
	<td colspan="97"><?php echo $AppUI->_('No data available').'<br />'.$AppUI->getMsg();?></td>
</tr>
<?php } else { 
$s = '';
$exist_checks = false; 
	for ($i = 0; $i < count($calendars) ; $i++){
		extract($calendars[$i]);
	
		$cal = new CCalendar();
		if ( !$cal->load( $calendar_id ) )
		{
			$AppUI->setMsg( "Calendar: InvalidId", UI_ERROR_MSG );
			$AppUI->redirect();
		}
		$cal->loadCalendarDays();
		$cal_date = new CDate($cal->calendar_from_date);
		
		$working_days = array();
		for ($j = 1 ; $j <= 7; $j++){
			if ($cal->_calendar_days[$j]->calendar_day_working)
				$working_days[]=$AppUI->_($days[$j]);
		}
		?>
	<tr>
		<td align="left">
	<?php if ($canEdit ){ ?>	
			<a href="<?php echo $PHP_SELF."?m=$m&a=addeditcalendar&calendar_id=$calendar_id";?>">
			<img src="./images/icons/edit_small.gif" alt="<?php echo $AppUI->_( 'Edit' )?>" title="<?php echo $AppUI->_( 'Edit' )?>"
			border="0" width="20" height="20"></a>
			<?php if ($cal->canDelete() ){
				// Si es un calendario activo, no puede desactivarlo //
				if($cal->calendar_status=="0"){
			?>
				<a href="javascript: delIt(<?php echo $calendar_id?>);">
				<img src="./images/icons/trash_small.gif" alt="<?php echo $AppUI->_( 'Delete' )?>"  title="<?php echo $AppUI->_( 'Delete' )?>"
				border="0" width="16" height="16"></a>
			<?php 
				} } ?>
	<?php } ?>		
			</td>
		<td><a href="<?php echo $PHP_SELF."?m=$m&a=viewcalendar&calendar_id=".$calendar_id.($project_id > 0 ? "&project_id=".$project_id : "");?>">
			<?php echo $cal->calendar_name;?></a></td>
		<td><?php echo implode(", ", $working_days);?></td>
<?php if ($list_all) { ?>	
		<td><?php echo $AppUI->_( $calendar_status_list[$cal->calendar_status] );?></td>
<?php }
	if ($canEdit){ 
		$exist_checks = true; ?>
		<td><input type="radio" name="status_ch" value="<?php echo $cal->calendar_id;?>" <? if($cal->calendar_status=="1"){ echo "checked";}?> onClick="javascript:submit();"/></td>	
<?php } ?>	
	</tr>
	<tr class="tableRowLineCell"><td colspan="97"></td></tr>

<?php } } ?>
</form>
</table>
<?php 
echo "
<table border='0' width='100%' cellspacing='0' cellpadding='1'>
<tr bgcolor=#E9E9E9>
	<td align='center'>$pager_links</td>
</tr>
<tr>
		<td height=1 colspan=5 bgcolor=#E9E9E9></td>
</tr>
</table>"; 
?>
