<?php

if (!(function_exists("showtimexp"))){
	function showtimexp($row, $spvMode){
		global $AppUI, $iconsYN, $timexp_types, $timexp_status,  $timexp_status_color, $te_status_transition,$timexp_type;
	// completar obt de permisos para editar y borrar y luego generar la línea.
		
		$canEdit = false;
		$texp = new CTimExp();
		$texp->load($row["timexp_id"]);
		$msg="";
		$canEdit = $texp->canEdit($msg);
		$tedate = new CDate($row["timexp_date"]);
		$df = $AppUI->getPref('SHDATEFORMAT');
		$bgcolor = "style=\"background-color: ".$timexp_status_color[$row["timexp_last_status"]].";\"";
		$canDelete = false;
		$html = "<tr>";
		$html .= "<td>";
		$sufix = strtolower($timexp_types[$row["timexp_type"]]);
		if ($canEdit) {
			$html .= "\n\t\t<a href=\"?m=timexp&a=addedit$sufix&timexp_id={$row['timexp_id']}\">"
				. "\n\t\t\t".'<img src="./images/icons/edit_small.gif" alt="'.$AppUI->_( 'Edit' ).'" border="0" width="20" height="20">'
				. "\n\t\t</a>";
		}
		if ($canEdit){
			$html .= "\n\t\t<a href=\"javascript: deleteIt{$row['timexp_type']}('{$row['timexp_id']}');\">"
				. "\n\t\t\t".'<img src="./images/icons/trash_small.gif" alt="'.$AppUI->_( 'Delete' ).'" border="0" width="16" height="16">'
				. "\n\t\t</a>";
		}
		$html .= "</td>";
		$html .= "<td title=\"{$row['timexp_description']}\">";
		$html .= "<a href=\"?m=timexp&a=view&timexp_id={$row['timexp_id']}\">".$row["timexp_name"]."</a></td>";
		$html .= "<td align=\"center\">".$tedate->format($df)."</td>";
		$html .= "<td align=\"center\">".$iconsYN[$row["timexp_billable"]]."</td>";
		if ($timexp_type=="1")
			$html .= "<td align=\"center\">".$iconsYN[$row["timexp_contribute_task_completion"]]."</td>";
		$html .= "<td align=\"right\">".number_format($row["timexp_value"], 2)."</td>";
		$html .= "<td nowrap=\"nowrap\">".$row["user_name"]."</td>";
		$html .= "<td align=\"center\" $bgcolor>";
		$html .= $AppUI->_($timexp_status[$row["timexp_last_status"]]);
		$html .= "</td>";
		
		$html .= "</tr>";
		return $html;
	}
}

global  $timexp_type, $timexp_types, $timexp_applied_to_types, $qty_units , $supervise_user, $app_to_type, $task_id, $bug_id, $canEdit,$spvMode;

$df = $AppUI->getPref('SHDATEFORMAT');

if (!isset($timexp_types)){
		$AppUI->setMsg( "Timexp" );
		$AppUI->setMsg( "Global variables not loaded", UI_MSG_ERROR, true );
		$AppUI->redirect();	
}

// Si no hay definido un tipo válido
if (!$timexp_type || !isset($timexp_types[$timexp_type])){
		$AppUI->setMsg( "Timexp" );
		$AppUI->setMsg( "Missing Type", UI_MSG_ERROR, true );
		$AppUI->redirect();	
}

$supervised_users = CTimexpSupervisor::getSupervisedUsers();
$supervised_users[$AppUI->user_id] = $AppUI->user_first_name." ".$AppUI->user_last_name;
if ($spvMode){
	$users_with_timexp = CTimExp::getUsersWithTimexp($timexp_type, 1, $task_id);
	$supervised_users = arrayMerge($supervised_users, $users_with_timexp);
}

if ($spvMode)
	$user_id = $_GET["user"] ?  $_GET["user"] : $AppUI->user_id ;
else
	$user_id = $AppUI->user_id;

//verifico que pueda ver a ese usuario
if($user_id != "-1" && !isset($supervised_users[$user_id])){
	$AppUI->redirect("m=public&a=access_denied");
}
	
	
$user_id = $user_id == "-1" ? NULL : $user_id;

//filtros de fecha
$from_date = intval($_GET["from"]) ? new CDate( $_GET["from"] ) : NULL;
$to_date = intval($_GET["to"]) ? new CDate( $_GET["to"] ) : null;


if ($from_date == NULL && $to_date == null){
	$from_date = new CDate();
	$to_date = new CDate();
	$from_date->subtractSeconds(60 * 60 * 24 * 30);
}



//modo supervisor
//$spvMode = $canEdit; //isset($supervised_users[$user_id]) && ($_GET["a"]=="vw_sup_day" || $_GET["a"]=="view");

$sufix = $timexp_types[$timexp_type];

// obtengo todos los registros del tipo seleccionado en la semana
/*
if ($spvMode){
	$supervised_timexp_id = CTimexpSupervisor::getSupervisedTimexpId();
	$list = CTimExp::getTimExpDateList(	$user_id
								, NULL
								, $timexp_type
								, NULL
								, $task_id
								, $bug_id
								, implode($supervised_timexp_id, ",")
								, "timexp_date DESC" 
								, ($from_date === null ? NULL : $from_date->format(FMT_TIMESTAMP_DATE)) 
								, ($to_date === null ? NULL : $to_date->format(FMT_TIMESTAMP_DATE)) );
}else{*/
	$list = CTimExp::getTimExpDateList(	$user_id
								, NULL
								, $timexp_type
								, NULL
								, $task_id
								, $bug_id
								, NULL
								, "timexp_date DESC"
								, ($from_date === null ? NULL : $from_date->format("%Y-%m-%d")) 
								, ($to_date === null ? NULL : $to_date->format("%Y-%m-%d")) );
/*}*/	

//echo "$task_id - $user_id - ".$from_date->format(FMT_TIMESTAMP_DATE)." <pre>"; print_r($list); echo "</pre>";

$getvars= explode("&", $_SERVER["QUERY_STRING"]);
$hiddens = "";
for($i=0;$i<count($getvars); $i++){
	if (substr($getvars[$i],0, strlen($sel_date_field."=")) != $sel_date_field."="  ){
		$tmpvals = explode("=",$getvars[$i]);
		$hiddens .= "<input type=\"hidden\" name=\"$tmpvals[0]\" value=\"$tmpvals[1]\" />\n";
	}
}
?>

<script language="javascript"><!--
var calendarField = '';

/*function asignacion(str)
{
	 to = fto->format(FMT_TIMESTAMP_DATE);
}*/

function popCalendarFlt( field ){
	calendarField = field;
	idate = eval( 'document.fltTimexp.' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendarFlt&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendarFlt( idate, fdate ) {
	fld_date = eval( 'document.fltTimexp.' + calendarField );
	fld_fdate = eval( 'document.fltTimexp.f' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}
function deleteIt<?php echo $timexp_type;?>(tid) {
	if (confirm( "<?=	$AppUI->_('doDeleteAdvice');?>" )) {
		var f = document.editFrm<?php echo $timexp_type;?>;
		f.timexp_id.value = tid;
		f.del.value = "1";
		f.dosql.value = "do_timexp_aed";
		f.submit();
	}
}

function clear_filter() {
	var f = document.fltTimexp;
	var id = "";
	for(var i=0;i<f.user.options.length && id=="";i++){
		if (f.user.options[i].value == <?php echo $AppUI->user_id;?>){
			id = i;
		}
	}
	f.user.selectedIndex = id;
	f.from.value = "";
	f.to.value = "";
	f.submit();
}
// --></script>

<?php 
$query_string = filterQueryString(array("to", "from", "user"));
$getvars= explode("&", $query_string);
$hiddens = "";
for($i=0;$i<count($getvars); $i++){
	$tmpvals = explode("=",$getvars[$i]);
	$hiddens .= "<input type=\"hidden\" name=\"$tmpvals[0]\" value=\"$tmpvals[1]\" />\n";
}

$tableStyle = 'class="std" style="border-top-width:1px;border-bottom-width:0px;border-left-width:0px;border-right-width:0px;border-style:solid;border-color:black;"';

if ($spvMode){
?>
<table width="100%" border="0" cellpadding="2" cellspacing="2" class="tableForm_bg" <?=$tableStyle?>>
<form action="" name="fltTimexp" method="GET">
<tr>
<?php echo $hiddens;?>
  <th colspan="10"><strong>
  <?php
///	echo $AppUI->_(($spvMode?"":"My ").$sufix."s")." - ";
//	echo $AppUI->_("Date").": ".
//		arraySelect( $list_dates, $sel_date_field, 'size="1" class="text" onchange="javascript: this.form.submit();"', $date->format(FMT_TIMESTAMP_DATE), true );	
	$supervised_users = arrayMerge(array("-1"=>"All"), $supervised_users);
	$select_user = $AppUI->_("User").": ".arraySelect( $supervised_users, "user", 'size="1" class="text"', $user_id, false );
	echo $select_user;
	?></strong></th>
	<th><?php echo $AppUI->_("From");?>:&nbsp;
		<input type="hidden" name="from" value="<?php echo ($from_date === null ? "" : $from_date->format(FMT_TIMESTAMP_DATE));?>">
		
        <input type="text" name="ffrom" value="<?php echo ($from_date === null ? "" : $from_date->format($df));?>" class="text"  size="12" disabled >
       
        
		<a href="#" onClick="popCalendarFlt('from')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>	
	</th>
	
	<th><?php echo $AppUI->_("To");?>:&nbsp;
		<input type="hidden" name="to" value="<?php echo ($to_date === null ? "" : $to_date->format(FMT_TIMESTAMP_DATE));?>">
       
		<input type="text" name="fto" value="<?php echo ($to_date === null ? "" : $to_date->format($df));?>" class="text"  size="12" disabled>
		<a href="#" onClick="popCalendarFlt('to')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>	
	</th>	
	
	<th>
		<input type="button" value="<?php echo $AppUI->_("clear");?>" class="button"/ onclick="clear_filter();">
		<input type="submit" value="<?php echo $AppUI->_("filter");?>" class="button"/>
	</th>
</tr>
</form>
</table>
<?php }  ?>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="">
<form name="editFrm<?php echo $timexp_type;?>" action="" method="POST">
<input type="hidden" name="del" value="" />
<input type="hidden" name="dosql" value="do_day_timexp_status_a" />	
<input type="hidden" name="timexp_id" value="" />
<input type="hidden" name="nxtscr" value="" />
<?
//cabeceras de la tabla 
?>
<tr class="tableHeaderGral">
	<th width="50px"  nowrap="nowrap">&nbsp;</th>
	<th width="80%"  nowrap="nowrap"><?=$AppUI->_("Name");?></th>
	<th width="50px" align="center" nowrap="nowrap"><?=$AppUI->_("Date");?></th>
	<th width="20px" align="center" nowrap="nowrap" title="<?=$AppUI->_("Billable");?>"><?=$AppUI->_("B");?></th>
<? if ($timexp_type=="1"){?>
	<th width="20px" align="center" nowrap="nowrap" title="<?=$AppUI->_("Contribute to task completion");?>"><?=$AppUI->_("CTC");?></th>
<? } ?>
	<th width="50px" align="center" nowrap="nowrap" title="<?=$AppUI->_($qty_units[$timexp_type]);?>" ><?=$AppUI->_($qty_units[$timexp_type]);?></th>
	<th nowrap="nowrap" align="center"><?=$AppUI->_("User");?></th>
	<th width="60px" align="center" nowrap="nowrap"><?=$AppUI->_("Status");?></th>


</tr>

<?

$coltotal = 0;
$html = "";
$nrofilas=0;
for($i=0; $i<count($list); $i++){
	$nrofilas++;
	extract($list[$i],EXTR_PREFIX_ALL,"t");
	$html .= showtimexp($list[$i], $spvMode);
	$coltotal += $list[$i]["timexp_value"];
}
echo $html;	

if ($nrofilas){
	$colspan = "4";
	if ($timexp_type=="1")
		$colspan = "5";

	?>
<tr>
	<td colspan="<?php echo $colspan;?>" align="right"><strong><?php echo $AppUI->_("Total");?></strong></th>
	<td align="right"><strong><?php echo number_format($coltotal, 2);?></strong></td>
</tr>
<tr class="tableRowLineCell"><td colspan="99"></td></tr>
<?php }

?>
</form>
</table>


<?php
if ($_GET["debug"]){
	echo "<pre>";var_dump($list);echo "</pre>";
	}
?>
