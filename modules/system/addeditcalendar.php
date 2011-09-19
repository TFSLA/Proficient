<?php 
global 	$canEdit, $calendar_type, $calendar_id,$company_id, $project_id, 
		$user_id, $default_working_days, $calendar_types,$m,$a;

$cal = new CCalendar();

$calendar_id = isset($calendar_id) ? $calendar_id : 0;
$isNew = ($calendar_id == 0 ? true : false);
/*
echo "<pre>
Calendar Type 	= $calendar_type
Calendar Id 	= $calendar_id
Company Id 		= $company_id
Project Id 		= $project_id
User Id			= $user_id
</pre>";
*/

if (!$isNew){
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
	
}


if (!$canEdit && $AppUI->user_type!='1') {
	$AppUI->redirect( "m=public&a=access_denied" );
}


if (!isset($calendar_types[$calendar_type]))
	$calendar_type = 0;
$cal_config = $calendar_types[$calendar_type];

//si no es calendario del sistema
if ($calendar_type > 0 ){
	//y se agrega un nuevo calendario debe estar la variable id
	if ($isNew && !isset($$cal_config["field_id"])){
		$AppUI->setMsg( "Calendar: Missing ".$cal_config["field_id"], UI_ERROR_MSG );
		$AppUI->redirect();	
	}
}	

// valores por defecto para nuevos calendarios
if ($isNew){
	$cal->calendar_status = "1";
	$cal->calendar_propagate = "1";

}

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

//$AppUI->savePlace( );

$days = array(
	1=>"Sunday",
	2=>"Monday",
	3=>"Tuesday",
	4=>"Wednesday",
	5=>"Thursday",
	6=>"Friday",
	7=>"Saturday"
);

//construyo los array de horas y minutos para crear los combos
$times = array();
$times["-1"]="";
//Time arrays for selects
$start = intval( substr($AppUI->getConfig('cal_day_start'), 0, 2 ) );
$end   = intval( substr($AppUI->getConfig('cal_day_end'), 0, 2 ) );
$inc   = $AppUI->getConfig('cal_day_increment');
$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$tf12hr= stristr($tf, "%p");
if ($start === null ) $start = 8;
if ($end   === null ) $end = 17;
if ($inc   === null)  $inc = 15;
for ( $hour = $start; $hour <= $end; $hour++ ) {
	$hour_key = sprintf("%02d",$hour);
	for ( $minute = 0 ; $minute < 60; $minute += $inc ) {
		$minute_key = sprintf("%02d",$minute);
		$times[$hour_key.$minute_key] = sprintf("%02d",($tf12hr ? ((($hour-1)%12)+1) : $hour));
		$times[$hour_key.$minute_key] .= ":".sprintf("%02d",$minute);
		$times[$hour_key.$minute_key] .= ($tf12hr ? " ".($hour>=12?"pm":"am") : "");
	}	
}

if( $AppUI->user_type == '1' OR $user_id==$AppUI->user_id OR !getDenyRead("admin"))
{
	$arrUserTemp = array("?m=system&a=addeditpref&user_id=$user_id"=>"edit preferences");
	$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);
}

//add crumb from admin module
$arrUserTemp = array("?m=admin&a=addedituser&user_id=".$user_id=>"edit personal information");

$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);
	
if (!getDenyRead("hhrr") || $user_id == $AppUI->user_id)
{
	$arrUserTemp = array(
					"?m=hhrr&a=addedit&tab=1&id=".$user_id=>"edit hhrr information"
					);

	$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);
}

$arrUserTemp = array("?m=admin&a=calendars&user_id=$user_id"=> "work calendar");
$arrUser = array_merge((array)$arrUser, (array)$arrUserTemp);

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
		"?m=system"=> "system admin",
		"?m=system&a=calendars"=> "calendars",
		"?m=system&a=viewcalendar&calendar_id=$calendar_id"=>"view"
		)),
"1"=>array(
	"title"=>"Companies",
	"icon"=>"handshake.gif",
	"crumbs"=>array(
		"?m=companies"=> "list companies",
		"?m=companies&a=view&company_id=$company_id"=> "view company",
		"?m=companies&a=calendars&company_id=$company_id"=> "calendars",
		"?m=companies&a=viewcalendar&calendar_id=$calendar_id"=> "view",
		)),		
"2"=>array(
	"title"=>"Projects",
	"icon"=>"projects.gif",
	"crumbs"=>array(
		"?m=projects"=> "list projects",
		"?m=projects&a=view&project_id=$project_id"=> "view project",
		"?m=projects&a=calendars&project_id=$project_id&tab=7"=> "calendars",
		"?m=projects&a=viewcalendar&calendar&calendar_id=$calendar_id"=> "view",
		)),				
"3"=>array(
	"title"=>"Users",
	"icon"=>"user_management.gif",
	"crumbs"=>$arrUser
	)
); 

$head = $headerBlock[$calendar_type];
$title = $isNew ? "Add Calendar" : "Edit Calendar";
// setup the title block

$titleBlock = new CTitleBlock( $title, $head["icon"], $m, "$m.$a" );
foreach($head["crumbs"] as $linkcrumb => $titlecrumb ){
	if (!($isNew && strpos($linkcrumb, "viewcalendar") > 0)){
		$titleBlock->addCrumb( $linkcrumb,$titlecrumb );
	}
}
$titleBlock->show();

?>
<script language="javascript"><!-- 
function createCombo(type, day, turn, selected){
	
	var hdr = '<select name="cal_'+type+'_time'+turn+'['+day+']" size="1"';
	hdr += ' onchange="this.form.elements[\'calendar_'+type+'_time'+turn+'['+day+']\'].value = this.options[this.selectedIndex].value; propagate_time(this, '+turn+');" ';
	/*hdr += ' onblur="propagate_time(this, '+turn+'); validate_turn_time('+turn+','+day+');" class="text">';*/
	hdr += ' onblur="propagate_time(this, '+turn+');" class="text">';
	var opt = "";
	<?php 
	foreach ($times as $time_id => $time_name)
		echo "opt += '<option value=\"$time_id\"'+(\"$time_id\" == selected ? 'selected':'')+'>$time_name</option>';\n\t";
	?>
	var ft = "</select>";
	document.write(hdr + opt + ft);
}
function delIt() {
	if (confirm( "<?=	$AppUI->_('doDeleteCalendarAdvice');?>" )) {
		document.frmDelete.submit();
	}
}

//-->
</script>

<table width="100%" border="0" cellpadding="1" cellspacing="0" class="tableForm_bg">
<form name="frmDelete" action="" method="post">
	<input type="hidden" name="dosql" value="do_calendar_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="calendar_id" value="<?php echo $calendar_id;?>" />
</form>
<form action="" method="POST" name="editFrm" >
<input type="hidden" name="calendar_company" value="<?php echo ($company_id?$company_id:"0");?>" />
<input type="hidden" name="calendar_project" value="<?php echo ($project_id?$project_id:"0");?>" />
<input type="hidden" name="calendar_user" value="<?php echo ($user_id?$user_id:"0");?>" />
<input type="hidden" name="calendar_id" value="<?php echo $calendar_id;?>" />
<input type="hidden" name="dosql" value="do_calendar_aed" />
<tr>
	<td colspan="97">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center">
	<tr>
		<td align="right" width="15%" ><b><?php echo $AppUI->_('Name');?>:</b>&nbsp;</td>
		<td width="35%"><input type="text" name="calendar_name" value="<?php echo $cal->calendar_name;?>" maxlength="50" size="30" class="text" />*</td>
		<td align="right" width="15%"></td>	
		<td  width="35%">
			
		</td>		
	</tr>
	<tr>
		<td align="right"><b><?php echo $AppUI->_($cal_config["Label"]);?>:</b>&nbsp;</td>
		<td><?php echo $default_name;?></td>
		<td align="right"><b><?php echo $AppUI->_('Status');?>:</b>&nbsp;</td>
		<td>
			<? if ($cal->calendar_status == 1 && $calendar_id > 0)
			   {
			   echo $AppUI->_('Active');
			   }
			   else{
			   echo $AppUI->_('Inactive');
			   }

			?>
	</td>
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
		<input type="hidden" name="calendar_day_id[<?php echo $day_id ?>]" 
			value="<?php echo ($isNew?'':$cal->_calendar_days[$day_id]->calendar_day_id);?>" />
		<input type="checkbox" name="calendar_working[<?php echo $day_id ?>]" 
		value="1" class="text" onchange="swapWorkingDay(<?php echo $day_id ?>);"
		<?php echo ($is_working_day?"checked":"") ?> />
		<?php echo $AppUI->_($day_name); ?>
	</th>
<? 
} 
?>
</tr>
<tr class="tableRowLineCell">
  <td colspan="97"></td>
</tr>
<?php
/*
<tr>
	<td>&nbsp;</td>
	<?php
	foreach ($days as $day_id => $day_name) 
	{
	?>
	<td  align="center"><?php echo $AppUI->_("From"); ?></td>
	<td  align="center"><?php echo $AppUI->_("To"); ?></td>
	<? 
	} 
	?>
</tr>
<?php
*/
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
		if($isNew){
			if($i==1){
				$shift_from = "0000-00-00 ".$default_shift_from;
			}else{
				$shift_from = '';
			}
		}else{
			$from_field = "calendar_day_from_time$i";
			$shift_from = $cal->_calendar_days[$day_id]->$from_field;			
		}
		if ($shift_from !=''){
			$from_time = new CDate($shift_from);
			$shift_from = $from_time->format("%H%M");
		}
	
			
	?>
	<td colspan="2" align="center" style="border-left: 1px solid black;">
	<script language="javascript"><!-- 
	createCombo("from",<?php echo $day_id.",".$i.", '$shift_from'"; ?>);
	//-->
	</script>
	
	
	<input type="hidden" size="7" name="calendar_from_time<?php echo $i."[$day_id]" ?>" value="<?php echo $shift_from;?>" class="text" 
		onblur="check_time(this); 
				propagate_time(this, <?php echo $i;?>); 
				validate_turn_time(<?php echo $i;?>,<?php echo $day_id;?>);" disabled/></td>
	<?php 
	} 
	?>
</tr>
<tr bgcolor="Silver">	
	<td nowrap="nowrap" ><?php echo $AppUI->_("To"); ?></td>
	<?php
	foreach ($days as $day_id => $day_name) 
	{
		if($isNew){
			if($i==1){
				$shift_to = "0000-00-00 ".$default_shift_to;
			}else{
				$shift_to = '';			
			}
		}else{
				$to_field = "calendar_day_to_time$i";
				$shift_to = $cal->_calendar_days[$day_id]->$to_field;
		}
		
		if ($shift_to !=''){
			$to_time = new CDate($shift_to);
			$shift_to = $to_time->format("%H%M");
		}		
		
	?>	
	<td colspan="2" align="center" style="border-left: 1px solid black;">
	<script language="javascript"><!-- 
	createCombo("to",<?php echo $day_id.",".$i.", '$shift_to'";?>);
	//-->
	</script>	
	<input type="hidden" size="7" name="calendar_to_time<?php echo $i."[$day_id]" ?>" value="<?php echo $shift_to;?>" class="text" 
		onblur="check_time(this); 
				propagate_time(this, <?php echo $i;?>); 
				validate_turn_time(<?php echo $i;?>,<?php echo $day_id;?>);" disabled/>	</td>
	<?php
	} 
	?>
</tr>
<?php
} 
?>
<tr>
  <td colspan="97">
  	<input type="checkbox" name="calendar_propagate" value="1" class="text" onclick="swapPropagate(this);" <?php echo ($cal->calendar_propagate == 1 ? "checked":"");?> /> <?php 
  	echo $AppUI->_("Use the same time on each shift every day"); ?>
  </td>
</tr>
<tr>
	<td colspan="16">
	<table width="98%" border="0" cellpadding="0" cellspacing="0" align="center">
	<tr>
		<td width="50%" align="left">
			<input class="button" type="button" name="cancel" 
  				value="<?php echo $AppUI->_('cancel');?>" 
  				onClick="javascript:if(confirm('<?php echo $AppUI->_("Are you sure you want to go back without saving the changes?");?>')){location.href = '?<?php echo $AppUI->getPlace();?>';}" />	

		</td>
		<td width="50%" align="right">
			<input class="button" type="button" name="btnFuseAction" 
  				value="<?php echo $AppUI->_('save');?>" onClick="submitIt();" />	
		</td>
	</tr>
	</table>
	</td>
</tr>
</table>

	</td>
</tr>
</form>
</table>
<script language="javascript"><!-- 
var calendarField = "";
var isampm = <?php echo ($tf12hr ? "true" : "false")?>;
var nombredia = new Array();
<?php
	foreach ($days as $day_id => $day_name) {
		echo "nombredia[$day_id] = \"".$AppUI->_($day_name)."\";\n";
	}
?>

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.calendar_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
/*	var changed = false;
	while (hollidays[idate]=="1"){
		idate = parseFloat(idate)+1;
	}*/
	fld_date = eval( 'document.editFrm.calendar_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
	var changed = calendarField == "from_date" ? "start" : "end";
/*	checkDates(changed);
	calcDuration();*/
}

function swapWorkingDay(day)
{
	var f = document.editFrm;
	var checked = f.elements["calendar_working["+day+"]"].checked;
	for (var i = 1; i <= 5; i++)
	{
		f.elements['cal_from_time' + i + '[' + day + ']'].disabled = !checked;
		f.elements['cal_to_time' + i + '[' + day + ']'].disabled = !checked;
		/*
		f.elements['calendar_from_time' + i + '[' + day + ']'].disabled = !checked;
		f.elements['calendar_to_time' + i + '[' + day + ']'].disabled = !checked;
		*/
		/*eval ("var to = f.elements['calendar_to_time" + i + "[" + day + "]'];");
		from.disabled = !checked;
		to.disabled = !checked;*/
		
	}
	
}

function check_time(field) {
	// Checks if time is in HH:MM:SS AM/PM format.
	// The seconds and AM/PM are optional.
	
	var rta = true;
	var allowempty = true;
	if (allowempty && field.value==""){
		rta =true;
	}else{
		var timeStr = field.value;
		var timePat = /^(\d{1,2}):(\d{1,2})(:(\d{1,2}))?(\s?(AM|am|PM|pm))?$/;
		// permite ingrsar la hora separada con . también
		//var timePat = /^(\d{1,2})(:|.)(\d{1,2})((:|.)(\d{1,2}))?(\s?(AM|am|PM|pm))?$/;
	
	
		var matchArray = timeStr.match(timePat);
		if (matchArray == null) {
			alert("<?php echo $AppUI->_("Time is not in a valid format.")?>");
			field.focus();
			rta = false;
		}
		if (rta){
			hour = matchArray[1];
			minute = matchArray[2];
			second = matchArray[4];
			ampm = matchArray[6];
		
			if (second=="") { second = null; }
			if (ampm=="") { ampm = null }
		
			if (isampm){
				if (hour < 1  || hour > 12) {
					alert("<?php echo $AppUI->_("Hour must be between 1 and 12.")?>");
					field.focus();
					rta = false;
				}
				if (ampm == null) {
					alert("<?php echo $AppUI->_("You must specify AM or PM.")?>");
					field.focus();	
					rta = false;	
				}
				
			}else{
				if (hour < 0  || hour > 23) {
					alert("<?php echo $AppUI->_("Hour must be between 0 and 23.")?>");
					field.focus();
					rta = false;
				}		
				if  (ampm != null) {
					alert("<?php echo $AppUI->_("You can't specify AM or PM. Please enter the time in the format HH:MM.")?>");
					field.focus();
					rta = false;
				}		
			}
			if (minute < 0 || minute > 59) {
				alert ("<?php echo $AppUI->_("Minute must be between 0 and 59.")?>");
				field.focus();
				rta = false;
			}
			if (second != null && (second < 0 || second > 59)) {
				alert ("<?php echo $AppUI->_("Second must be between 0 and 59.")?>");
				field.focus();
				rta = false;
			}
		}
	}
	return rta;
}

function swapPropagate(field)
{
	var f = document.editFrm;
	if (field.checked){
		/* Busco si hay alguna fila con <> valor */
		var clearfrom = new Array();
		var clearto = new Array();
		var clearrows = false;
		for(var j = 1; j <=5; j++){	
			var lastfromindex = -1;
			var lasttoindex = -1;
			clearfrom[j] = 0;
			clearto[j] = 0;
			for(var i = 1; i <=7 && (clearfrom[j]==0 || clearto[j]==0); i++){
				var isworkingday = f.elements["calendar_working[" + i + "]"].checked;
				if (isworkingday){
					var curfromindex = f.elements["cal_from_time" + j + '[' + i + ']'].selectedIndex;
					var curtoindex = f.elements["cal_to_time" + j + '[' + i + ']'].selectedIndex;
					if (lastfromindex==-1)
						lastfromindex = curfromindex;
					if (lasttoindex==-1)
						lasttoindex = curtoindex;
						
					if	(lastfromindex != curfromindex){
						clearfrom[j] = 1;
						clearrows = true;
					}
					if	(lasttoindex != curtoindex){
						clearto[j] = 1;	
						clearrows = true;			
					}		
				}
			}
		}
				
			
		if (clearrows){				
			if (confirm("<?php echo $AppUI->_("If you activate this option, all the rows that have different values will be cleaned. Are you sure you want to perform this action?")?>")){
				for(var i = 1; i <=7; i++){
					for(var j = 1; j <=5; j++){
						if (clearfrom[j]==1)
							f.elements["cal_from_time" + j + '[' + i + ']'].selectedIndex = 0;
						if (clearto[j]==1)						
							f.elements["cal_to_time" + j + '[' + i + ']'].selectedIndex = 0;
					}
				}
			}else{
				field.checked = false;
			}
		}
		
	}

}

function propagate_time(field, turn){
	var f = document.editFrm;
	if (f.calendar_propagate.checked){
		if (field.name.search("from")>0){		
			fieldname = "calendar_from_time";
			comboname = "cal_from_time";
		}else{
			fieldname = "calendar_to_time";
			comboname = "cal_to_time";
		}
		for(var i = 1; i <=7; i++){
			/*
			if (!f.elements[fieldname + turn + '[' + i + ']'].disabled){
				f.elements[fieldname + turn + '[' + i + ']'].value = field.value;*/
			if (!f.elements[comboname + turn + '[' + i + ']'].disabled){				
				f.elements[comboname + turn + '[' + i + ']'].selectedIndex = field.selectedIndex;
				f.elements[fieldname + turn + '[' + i + ']'].value = field.options[field.selectedIndex].value;
			}
		}
	
	}
	
}

function validate_turn_time(turn, day){
	var f = document.editFrm;
	var from = f.elements["cal_from_time" + turn + '[' + day + ']'];
	var to = f.elements["cal_to_time" + turn + '[' + day + ']'];

	if (from.selectedIndex && to.selectedIndex){
		var from_HHMM = from.options[from.selectedIndex].value;	
		var to_HHMM = to.options[to.selectedIndex].value;
		
		if (to_HHMM <= from_HHMM){
			alert("<?php echo $AppUI->_("To time must be posterior to From time.")?>" +"\n"+
				"<?php echo $AppUI->_("From")?>: " + from.value +"\n"+
				"<?php echo $AppUI->_("To")?>: " + to.value);
			return false	
		}else{
			return true;
		}
	}	
	/*
	var from = f.elements["calendar_from_time" + turn + '[' + day + ']'];
	var to = f.elements["calendar_to_time" + turn + '[' + day + ']'];

	if (from.value.length && to.value.length){
		var from_HHMM = get_HHMM(from);	
		var to_HHMM = get_HHMM(to);
		
		if (to_HHMM <= from_HHMM){
			alert("To time must be posterior to From time." +"\n"+
				"From: " + from.value +"\n"+
				"To: " + to.value);
			return false	
		}else{
			return true;
		}
	}
	*/
}

function get_HHMM(field){
	var timeStr = field.value;
	var timePat = /^(\d{1,2}):(\d{1,2})(:(\d{1,2}))?(\s?(AM|am|PM|pm))?$/;

	var matchArray = timeStr.match(timePat);
	if (matchArray == null) {
		alert("<?php echo $AppUI->_("Time is not in a valid format.")?>");
		from.focus();
	}
	hour = matchArray[1];
	minute = matchArray[2];
	second = matchArray[4];
	ampm = matchArray[6];	
	
	if (isampm){
		hour = hour % 12;
		if (ampm.toLowerCase()=="pm"){
			hour+=12;
		}
	}
	
	hour = (hour.toString().length == 1 ? "0":"") + hour.toString();
	minute = (minute.toString().length == 1 ? "0":"") + minute.toString();
	//hour = hour.substr(-2);
	//minute = "0" + minute.toString();
	//minute = minute.substr(-2);
	return hour.toString()+minute.toString();
}

function submitIt()
{
	var f = document.editFrm;
	
	if ( trim(f.calendar_name.value) == ""){
		alert ("<?php echo $AppUI->_("Please enter the calendar name")?>")
		f.calendar_name.focus();
		return false;				
	}	
	
	//construyo un array con todos los horarios turnos para los dias marcados como laborables
	// verificando que estén definidos los dos horarios para los turnos y sean horas válidas
	// y que la hora desde no sea igual o superior a la hora hasta en cada turno
	var times = new Array();
	for(var i = 1; i <=7; i++){
		var isworkingday = f.elements["calendar_working[" + i + "]"].checked;
		var lastturndefined = 0;
		for(var j = 1; j <=5 && isworkingday; j++){
			var from = f.elements["calendar_from_time" + j + '[' + i + ']'];
			var to = f.elements["calendar_to_time" + j + '[' + i + ']'];

			//si ambos turnos estan vacios
			if (to.value == "" && from.value == ""){
				// y es el primer turno de un dia laborable no es válido
				if ( j == 1){
					alert (nombredia[i] +": <?php echo $AppUI->_("It cannot lack the from time and to time of the first shift of a working day.")?>")
					from.focus();
					return false;				
				}
			}else if (to.value != "" && from.value == "" ){
				alert ("<?php echo $AppUI->_("It cannot only be defined one time for a shift. You have only defined the to time, you should add the from time or erase the to time.")?>");
				from.focus();
				return false;
			}else if (from.value != "" && to.value == "" ){
				alert ("<?php echo $AppUI->_("It cannot only be defined one time for a shift. You have only defined the from time, you should add the to time or erase the from time.")?>");
				to.focus();
				return false;			
			}else if( from.value > to.value || from.value == to.value ) {
				if(from.value !="-1" && to.value !="-1")
				{
				alert ("<?php echo $AppUI->_("The from time must be later than the from time of each shift.")?> ");
				return false;
				}
			}else{
				lastturndefined = j;
				if (!times[i]) 	times[i] = new Array();
				if (!times[i][j]) times[i][j] = new Array();
				times[i][j]["from"] = from.value;
				times[i][j]["to"] = to.value;
			}
		}
	}

	//recorro el array y verifico que cada turno no tenga horario superpuesto con los anteriores	
	for(var i = 1; i <=7; i++)
		if(times[i])
			for(var j = 1; j <=5 && times[i][j]; j++){
				for(var k = 1; k < j ; k++){
					var curr_from = times[i][j]["from"];
					var curr_to = times[i][j]["to"];
					var prev_from = times[i][k]["from"];
					var prev_to = times[i][k]["to"];
					
					if(curr_to <= prev_from){
						alert("<?php echo $AppUI->_("The shifts should be defined in a growing way. The shift ")?>" + j + "<?php echo $AppUI->_(" of the day ")?>"+ nombredia[i] + "<?php echo $AppUI->_(" is prior to shift ")?>"+ k + "<?php echo $AppUI->_(" of the same day.")?>");
						return false;
					}else if (!(curr_from >= prev_to)){
						alert("En el dia "+ nombredia[i] +" el turno " + j + " tiene horarios superpuestos con el turno "+ k);
						return false;						
					}	 
				}
			}
		
	/*alert("Calendario válido!!!");
	return true;*/
	f.submit();


}

swapWorkingDay(1);
swapWorkingDay(2);
swapWorkingDay(3);
swapWorkingDay(4);
swapWorkingDay(5);
swapWorkingDay(6);
swapWorkingDay(7);


//--></script>