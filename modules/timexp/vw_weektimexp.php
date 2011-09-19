<?php
if (!(function_exists("showprojectrow"))){
	function showprojectrow($project_id){
		global $AppUI;
		$prj = new CProject();
		$prj->load($project_id);
		$bgcolor = " style=\"background-color: #".$prj->project_color_identifier."; \"";

		$html = "<tr><td colspan=\"20\"$bgcolor>";
		$project_name = "<span style='color:".bestColor(@$prj->project_color_identifier).";text-decoration:none;'><strong>".$prj->project_name."</strong></span>";
		if ($prj->canRead())
			$html .= "<a href=\"?m=projects&a=view&project_id={$project_id}\" title=\"".$AppUI->_("View")."\">$project_name</strong></span></a>";
		else
			$html .= $project_name;
		
		$html .= "</td></tr>\n\t";

		return $html;

	}
}
if (!(function_exists("showtasklink"))){
	function showtasklink($task_id){
		global $AppUI;
		$perms=CTask::getTaskAccesses($task_id);
		$canRead=$perms["read"];
		
		if ($canRead) {
			return "<a href=\"?m=tasks&a=view&task_id={$task_id}\" title=\"".$AppUI->_("View")."\">$task_id</a>";
		}else{
			return "$task_id";
		}	

	}
}
global  $week_date_time, $week_date_expense, $timexp_type, $timexp_types, $timexp_applied_to_types, $supervise_user, $tab, $timexp_status, $spvMode;

$df = $AppUI->getPref('SHDATEFORMAT');

// Si no hay definido un tipo válido
if (!$timexp_type || !isset($timexp_types[$timexp_type])){
		$AppUI->setMsg( "Timexp" );
		$AppUI->setMsg( "Missing Type", UI_MSG_ERROR, true );
		$AppUI->redirect();	
}

$supervised_users = CTimexpSupervisor::getSupervisedUsers();
$user_id = $supervise_user ?  $supervise_user : $AppUI->user_id ;

//modo supervisor

if ($user_id != $AppUI->user_id && !$spvMode){
	$AppUI->redirect( "m=public&a=access_denied" );
}


// Obtengo todas las fechas con registros del tipo seleccionado
if ($spvMode){
	$supervised_timexp_id = CTimexpSupervisor::getSupervisedTimexpId();
	$dates = CTimExp::getTimExpDates( $user_id, $timexp_type, implode($supervised_timexp_id, ","));
}else{
	$dates = CTimExp::getTimExpDates( $user_id, $timexp_type);
}

$sufix = $timexp_types[$timexp_type];	

$select_field = 'week_date_'.strtolower($sufix);
// Busco la semana seleccionada
if (isset( $_GET[$select_field] )) {
    $AppUI->setState( 'TxpLstWD'.$timexp_type, $_GET[$select_field] );
}
$sel_week = $AppUI->getState( 'TxpLstWD'.$timexp_type ) !== NULL ? $AppUI->getState( 'TxpLstWD'.$timexp_type ) : NULL;


//obtengo la lista de semanas en que posee registros cargados
$week_list = array();

for($i=0; $i < count($dates) ;$i++){
	$date = $dates[$i]["timexp_date"];
	$this_week = new CDate($date);
	$dd = $this_week->getDay();
	$mm = $this_week->getMonth();
	$yy = $this_week->getYear();
	$first_time = new CDate( Date_calc::beginOfWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );
	$first_time->setTime( 0, 0, 0 );
	$first_time->subtractSeconds( 1 );
	$last_time = new CDate( Date_calc::endOfWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );
	$last_time->setTime( 23, 59, 59 );
	$date = $first_time;
	$date->addDays(2);
	$id = $date->format( FMT_TIMESTAMP_DATE );
	if ( !isset($week_list[$id])){
		$week_list[$id] = $first_time->format($df)." - ".$last_time->format($df);
	}
	$sel_week = $sel_week ? $sel_week : $id;
}

// establezco el primer  último día de la semana seleccionada
$this_week = new CDate($sel_week);
$dd = $this_week->getDay();
$mm = $this_week->getMonth();
$yy = $this_week->getYear();
$first_time = new CDate( Date_calc::beginOfWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );
$first_time->setTime( 0, 0, 0 );
$first_time->subtractSeconds( 1 );
$last_time = new CDate( Date_calc::endOfWeek( $dd, $mm, $yy, FMT_TIMESTAMP_DATE, LOCALE_FIRST_DAY ) );
$last_time->setTime( 23, 59, 59 );



// obtengo todos los registros del tipo seleccionado en la semana
if ($spvMode){
	/*
	$list = CTimExp::getTimExpList($user_id
							,$first_time->format( FMT_TIMESTAMP_DATE )
							,$last_time->format( FMT_TIMESTAMP_DATE )
							, NULL
							, $timexp_type
							, NULL
							, NULL
							, NULL
							, NULL
							, implode($supervised_timexp_id, ","));
							*/
	$list = CTimExp::getTimExpDateList($user_id
									, NULL
									, $timexp_type
									, NULL
									, NULL
									, NULL
									, implode($supervised_timexp_id, ",")
									, NULL
									,$first_time->format( FMT_TIMESTAMP_DATE )
									,$last_time->format( FMT_TIMESTAMP_DATE ));
}else{
	/*
	$list = CTimExp::getTimExpList($user_id
							,$first_time->format( FMT_TIMESTAMP_DATE )
							,$last_time->format( FMT_TIMESTAMP_DATE )
							, NULL
							, $timexp_type );*/
	$list = CTimExp::getTimExpDateList($user_id
									, NULL
									, $timexp_type
									, NULL
									, NULL
									, NULL
									, NULL
									, NULL
									,$first_time->format( FMT_TIMESTAMP_DATE )
									,$last_time->format( FMT_TIMESTAMP_DATE ));
}

//reordeno los resultados en varios arrays para facilitar el manejo de la info
$table = array (); 
$projects = array(); 
$tasks = array(); 
$bugs = array(); 
$naList = array();
$week_status = NULL;
$timexp_id_list=array();
for($i=0; $i < count($list); $i++){
	extract($list[$i],EXTR_PREFIX_ALL,"t");

	$timexp_id_list[$t_timexp_id]=$t_timexp_id;

	if ($week_status != -1){
		if ($week_status == NULL){
			$week_status = $t_timexp_last_status;
		}else{
			$week_status = $week_status == $t_timexp_last_status ? $week_status : -1;
		}
	}

	$app_id = "NULL";
	if (!is_null($t_task_id)){
		$app_id = $t_task_id;
		$tasks[$t_task_id] = $t_task_name;
	}
	if (!is_null($t_bug_id)){
		$app_id = $t_bug_id;
		$bugs[$t_bug_id] = $t_summary;
	}
	$tmp_date = new CDate($t_timexp_date);
	$tmp_date = $tmp_date->format(FMT_TIMESTAMP_DATE);
	
	if (is_null($t_project_id)){
		if (!$naList[$t_timexp_name]["$tmp_date"])
			$naList[$t_timexp_name]["$tmp_date"]=0;
		$naList[$t_timexp_name]["$tmp_date"] += floatval($t_timexp_value);
	}else{
		$projects[$t_project_id]=$t_project_name;
		if (!$table["$t_project_id"]["$t_timexp_applied_to_type"]["$app_id"]["$t_timexp_billable"]["$tmp_date"])
			$table["$t_project_id"]["$t_timexp_applied_to_type"]["$app_id"]["$t_timexp_billable"]["$tmp_date"]=0;
		$table["$t_project_id"]["$t_timexp_applied_to_type"]["$app_id"]["$t_timexp_billable"]["$tmp_date"]+=floatval($t_timexp_value);
	}
}

$coltotal = array();
$html = "";
$nrofilas=0;
if (count($table)>0){
	if (count($projects)>0){
		foreach ($projects as $pid => $pname){
			//$html .= "<tr><td colspan=\"20\"><h1> ".$pname."</h1></td></tr>\n\t";
			$html .= showprojectrow($pid);

			//muestro las tareas que tienen reg asignados
			if (count($tasks)>0){
			foreach($tasks as $tid=>$tname){
				if (isset( $table[$pid]["1"][$tid])){
					$nrofilas++;
					$html .= "<tr><td rowspan=\"2\">";
					$html .= "[".$AppUI->_($timexp_applied_to_types[1])."] ID = ".showtasklink($tid)."<br>";
					$html .= $AppUI->_("Desc.").": ".$tname;
					$html .= "</td>";
					$html .= "<td align=\"center\">".$AppUI->_("Yes")."</td>";
					$day = $first_time;
					$total = 0;
					for ($i=0; $i < 7; $i++){
						$dt = $day->format(FMT_TIMESTAMP_DATE);
						$valor = $table["$pid"]["1"]["$tid"]["1"]["$dt"];
						$valor = $valor ? $valor:"0";
						$html .= "<td align='center'>".number_format($valor, 2)."</td>";
						$day->addDays( 1);
						$total = $total + $valor;
						$coltotal[1]["$dt"] += $valor;
					}
					$html .= "<td align='right'>". number_format($total, 2)."</td>";
					
					$html .= "</tr><tr>";
					$html .= "<td align=\"center\">".$AppUI->_("No")."</td>";
					$day = $first_time;
					$total = 0;
					for ($i=0; $i < 7; $i++){
						$valor=0;
						$dt = $day->format(FMT_TIMESTAMP_DATE);
						$valor = $table["$pid"]["1"]["$tid"]["0"]["$dt"];
						$valor = $valor ? $valor:"0";
						$html .= "<td align='center'>".number_format($valor, 2)."</td>";
						$day->addDays( 1);
						$total = $total + $valor;
						$coltotal[0]["$dt"] += $valor;
					}
					$html .= "<td align='right'>". number_format($total, 2)."</td>";
					$html .= "</tr>";
                    $html .= "<tr class=\"tableRowLineCell\"><td colspan=\"99\"></td></tr>";
				}
				
			}
			}
			if (count($bugs)>0){
			//muestro las incidencias que tienen reg asignados
			foreach($bugs as $bid=>$bname){
				//muestro las tareas que tienen reg asignados
				if (isset( $table[$pid]["2"][$bid])){
					$nrofilas++;
					$html .= "<tr><td rowspan=\"2\">";
					$html .= "[".$AppUI->_($timexp_applied_to_types[2])."] ID = ".$bid."<br>";
					$html .= $AppUI->_("Desc.").": ".$bname;
					$html .= "</td>";
					$html .= "<td align='center'>".$AppUI->_("Yes")."</td>";
					$day = $first_time;
					$total = 0;
					for ($i=0; $i < 7; $i++){
						$dt = $day->format(FMT_TIMESTAMP_DATE);
						$valor = $table["$pid"]["2"]["$bid"]["1"]["$dt"];
						$valor = $valor ? $valor:"0";
						$html .= "<td align='center'>".number_format($valor, 2)."</td>";
						$day->addDays( 1);
						$total = $total + $valor;
						$coltotal[1]["$dt"] += $valor;
					}
					$html .= "<td align='right'>". number_format($total, 2)."</td>";
					
					$html .= "</tr><tr>";
					$html .= "<td align='center'>".$AppUI->_("No")."</td>";
					$day = $first_time;
					$total = 0;
					for ($i=0; $i < 7; $i++){
						$valor=0;
						$dt = $day->format(FMT_TIMESTAMP_DATE);
						$valor = $table["$pid"]["2"]["$bid"]["0"]["$dt"];
						$valor = $valor ? $valor:"0";
						$html .= "<td align='center'>".number_format($valor, 2)."</td>";
						$day->addDays( 1);
						$total = $total + $valor;
						$coltotal[0]["$dt"] += $valor;
					}
					$html .= "<td align='right'>". number_format($total, 2)."</td>";
					$html .= "</tr>";
				}		
				
			}		
			}

		}
	}	
}

//muestro los aplicados a nada
if (count($naList)>0){
	$html .= "<tr><td colspan=\"20\"><h1>- ".$AppUI->_($timexp_applied_to_types[3])." -</h1></td></tr>\n\t";
	foreach($naList as $naDesc=>$nfields){
		$nrofilas++;
		$html .= "<tr><td>";
		//$html .= "[".$AppUI->_($timexp_applied_to_types[3])."] <br>";
		$html .= $AppUI->_("Desc.").": ".$naDesc;
		$html .= "</td>";
		$html .= "<td align='center'>".$AppUI->_("No")."</td>";
		$day = $first_time;
		$total = 0;
		for ($i=0; $i < 7; $i++){
			$dt = $day->format(FMT_TIMESTAMP_DATE);
			$valor = $nfields["$dt"];
			$valor = $valor ? $valor:"0";
			$html .= "<td align='center'>".number_format($valor, 2)."</td>";
			$day->addDays( 1);
			$total = $total + $valor;
			$coltotal[0]["$dt"] += $valor;
		}
		$html .= "<td align='right'>". number_format($total, 2)."</td>";
		
		$html .= "</tr>";		
	}	
	
}



if ($nrofilas>0){
	$html .= "<tr style='font-weight: bold;'><td rowspan=\"2\">";
	$html .= $AppUI->_("Total");
	$html .= "</td>";
	$html .= "<td align='center'>".$AppUI->_("Yes")."</td>";
	$day = $first_time;
	$total = 0;
	for ($i=0; $i < 7; $i++){
		$dt = $day->format(FMT_TIMESTAMP_DATE);
		$valor = $coltotal[1]["$dt"];
		$valor = $valor ? $valor:"0";
		$html .= "<td align='center'>".number_format($valor, 2)."</td>";
		$day->addDays( 1);
		$total = $total + $valor;
	}
	$html .= "<td align='right'>". number_format($total, 2)."</td>";

	$html .= "</tr><tr  style='font-weight: bold; background-color: gray;'>";
	$html .= "<td align='center'>".$AppUI->_("No")."</td>";
	$day = $first_time;
	$total = 0;
	for ($i=0; $i < 7; $i++){
		$valor=0;
		$dt = $day->format(FMT_TIMESTAMP_DATE);
		$valor = $coltotal[0]["$dt"];
		$valor = $valor ? $valor:"0";
		$html .= "<td align='center'>".number_format($valor, 2)."</td>";
		$day->addDays( 1);
		$total = $total + $valor;
	}
	$html .= "<td align='right'>". number_format($total, 2)."</td>";
	$html .= "</tr>";
}



$getvars= explode("&", $_SERVER["QUERY_STRING"]);
$hiddens = "";
for($i=0;$i<count($getvars); $i++){
	if (substr($getvars[$i],0, strlen($select_field."=")) != $select_field."=" ){
		$tmpvals = explode("=",$getvars[$i]);
		$hiddens .= "<input type=\"hidden\" name=\"$tmpvals[0]\" value=\"$tmpvals[1]\" />\n";
	}
}

//echo "<pre>";var_dump($getvars);echo "</pre>";
//echo "<pre>";var_dump($action);echo "</pre>";
?>


<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
<tr>
<form action="index.php" method="GET">
<?php echo $hiddens;?>
    <td colspan="10">
        <table width="100%" cellpadding="0" cellspacing="0" class="tableForm_bg">
            <tr>

              <th><strong><?=$AppUI->_(($spvMode?"":"My ").$sufix."s");?></strong></th>
              <th align="right">
              		<?=$AppUI->_("Week");?>:
                    <?=arraySelect( $week_list, $select_field, 'size="1" class="text" onchange="javascript: this.form.submit();"', $sel_week, true );?>
              </th>
            <?php
            // obtengo el color de fondo para pintar la celda del estado semanal
            global $timexp_status_color;
            $bgcolor="";
            if (isset($timexp_status[$week_status]))
            	$bgcolor = "style=\"background-color: ".$timexp_status_color[$week_status]."; color: gray;\"";
            ?>
              <th  <?php echo $bgcolor;?>>
                    <?php
            		echo $AppUI->_("Status").": ";


            		//si hay varios registros con estados diferentes
            		if ($week_status == -1)
            			echo  "<span title=\"".$AppUI->_("timexpWeekMixedStatus")."\" >".$AppUI->_("Mixed")."</span>";
            		else{
            			if (isset($timexp_status[$week_status])){
            				global $te_status_transition;
            				echo  $AppUI->_($timexp_status[$week_status]);
            			}else
            				echo  $AppUI->_("");
            		}
                    ;?>
              </th>
            </tr>
        </table>
  </td>
  </form>
</tr>

<?
//cabeceras de la tabla 
?>
<tr class="tableHeaderGral">
	<th ><?=$AppUI->_("Applied to");?></th>
	<th width="" align="center" title="<?=$AppUI->_("Billable");?>"><?=$AppUI->_("B");?></th>
<?
	$day = $first_time;
	for ($i=0; $i < 7; $i++){?>
		<th width="90px" align="center" class="tableHeaderText">
		<?php
		$day_have_records = ($coltotal[0][$day->format(FMT_TIMESTAMP_DATE)] || $coltotal[1][$day->format(FMT_TIMESTAMP_DATE)]);
		
		if ($day_have_records){
			$a_href = "index.php?m=timexp&a=vw_";
			$a_href .= $spvMode ? "sup_day" : "myday";
			$a_href .= "&tab=".strtolower($tab)."&sel_date_".strtolower($sufix);
			$a_href .= "=".$day->format(FMT_TIMESTAMP_DATE);

			$a_title = $AppUI->_($spvMode ? "Daily Supervision" : "My Daily View");
			$a_title .=  " - ".$AppUI->_("Day")." ".$day->format($df);

		?>
			<a href="<?php echo $a_href; ?>"  title="<?php echo $a_title;?>"><strong>
		<?php
		}
		echo $day->format($df);
		if ($day_have_records){
		?>
			</strong>
			</a>
		<?php } ?>

		</th>
		<?php
		$day->addDays( 1);
	}	

	
?>
	<th width="" align="right"><?=$AppUI->_("Total");?></th>
</tr>

<?	
echo $html;	
if ($spvMode){?>
<tr>
<form action="" method="POST">
<input type="hidden" name="timexp_id_list" value="<?php echo implode($timexp_id_list, ',');?>" />
<input type="hidden" name="dosql" value="do_week_timexp_status_a" />	
	<th colspan="20"><?php 
		if ($week_status != -1){
			echo  $AppUI->_("Change status").":";
			$status_list_tmp = explode (",", $te_status_transition[$week_status]);
			$status_list = array();
			for($i=0; $i < count ($status_list_tmp); $i++){
				$status_list[$status_list_tmp[$i]] = $timexp_status[$status_list_tmp[$i]];
			}
			$status_list = arrayMerge(array(""=>""), $status_list );
			echo arraySelect($status_list, "week_status_value", 'size="1" class="text"', NULL, true );
			echo '<input type="submit" value="'.$AppUI->_("Ok").'" class="button" />';
		}


	
	?></th>
	</form>
</tr>
<?php }  ?>

</table>


<?

?>

<pre>
<?=//var_dump($list);?>
<?=//var_dump($timexp_id_list);?>
</pre>
