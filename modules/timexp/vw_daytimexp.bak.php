<?php

$supervised_users = CTimexpSupervisor::getSupervisedUsers();
$user_id = $supervise_user ?  $supervise_user : $AppUI->user_id ;

$spvMode = isset($supervised_users[$user_id]) && $_GET["a"]!="vw_myday";

IF ($_POST['sup_user']!='') $sup_user=$_POST['sup_user'];
ELSEIF ($_GET['sup_user']!='') $sup_user=$_GET['sup_user'];
ELSEIF ($_GET["a"]=="vw_myday") $sup_user=$user_id;
//echo "<br>POST =".$_POST['sup_user']."<br>";
//echo "<br>GET  =".$_GET['sup_user']."<br>";
//echo "<br>sup_user =".$sup_user."<br>";
//echo "<br>user_id =".$user_id."<br>";

if (!(function_exists("mkFromTo"))){
	function mkFromTo($from_date, $to_date, $AppUI){
		if ($from_date!='--'){
			$from_year=substr($from_date, 0, 4);
			$from_month=substr($from_date, 5, 2);
			$from_day=substr($from_date, 8, 2);
		}
		else {
			$from_year=date("Y");
			$from_month=date("m")-1;
			$from_day=date("d");
		}
		if ($to_date!='--'){
			$to_year=substr($to_date, 0, 4);
			$to_month=substr($to_date, 5, 2);
			$to_day=substr($to_date, 8, 2);
		}
		else {
			$to_year=date("Y");
			$to_month=date("m");
			$to_day=date("d");
		}
		?>
		<table>
			<TR>
				<TD>Desde :</TD>
				<TD>
					<select name='from_day' size="1" class="text">
						<?php mkOption (1, 31, $from_day); ?>
					</select>
					<select name='from_month' size="1" class="text">
						<?php mkOption (1, 12, $from_month ); ?>
					</select>
					<select name='from_year' size="1" class="text">
						<?php mkOption (2000, date("Y"), $from_year); ?>
					</select>
					<INPUT type="image" src="images/arrow-right.gif" onclick="javascript: this.form.submit();">
				</TD>
			</TR>
			<TR>
				<TD>Hasta :</TD>
				<TD>
					<select name='to_day' size="1" class="text">
						<?php mkOption (1, 31, $to_day); ?>
					</select>
					<select name='to_month' size="1" class="text">
						<?php mkOption (1, 12, $to_month ); ?>
					</select>
					<select name='to_year' size="1" class="text">
						<?php mkOption (2000, date("Y"), $to_year); ?>
					</select>
					<INPUT type="image" src="images/arrow-right.gif" onclick="javascript: this.form.submit();">
				</TD>
			</TR>
		</table>
		<?php
	}
}

if (!(function_exists("mkOption"))){
	function mkOption ($from, $to, $formsel) {
		while ($to >= $from){
			if ($from==$formsel) $sel='SELECTED';
			if ($from<10) $cero=0;
			echo "<option value='$cero$from' $sel>$cero$from</option>\n";
			$sel='';
			$cero='';
			$from++;
		}
	}
}

if (!(function_exists("showprojectrow"))){
	function showprojectrow($project_id, $user_id, $from_date, $to_date){
		global $AppUI;
		$where = "";
		IF ($user_id!=0) $user_sql="timexp_creator=$user_id";
		else $user_sql="timexp_creator IS NOT NULL";
		if ($from_date!=NULL and $to_date!=NULL){
					$where .=" AND t.timexp_date >= '$from_date 00:00:00.000' \n\t";
					$where .=" AND t.timexp_date <= '$to_date 23:59:59.999' \n\t";
		}	
		$sql ="	SELECT sum(t.timexp_value) AS projtime 
						FROM timexp AS t 
						INNER JOIN projects AS p 
							ON (
										p.project_id=tp.task_project OR
										p.project_id = bt.project_id)
						LEFT JOIN tasks AS tp
							ON (
										tp.task_id=t.timexp_applied_to_id AND
										t.timexp_applied_to_type = 1)
						LEFT JOIN btpsa_bug_table AS bt 
							ON (
										t.timexp_applied_to_id=bt.id AND
										t.timexp_applied_to_type = 2)
						WHERE 
								$user_sql AND 
								p.project_id=$project_id 
								$where";

		//echo "<br>$sql<br>";
		$vec=db_fetch_array(db_exec($sql));
		$prj = new CProject();
		$prj->load($project_id);
		$bgcolor = " style=\"background-color: #".$prj->project_color_identifier."; \"";

		$html = "<tr><td colspan='4' $bgcolor>";
		$project_name = "<span style='color:".bestColor(@$prj->project_color_identifier).";text-decoration:none;'><strong>".$prj->project_name."</strong></span>";
		if ($prj->canRead())
			$html .= "<a href=\"?m=projects&a=view&project_id={$project_id}\" title=\"".$AppUI->_("View")."\">$project_name</strong></span></a>";
		else
			$html .= $project_name;
		
		$html .= "</td>";
		$html .= "<td $bgcolor align='center'>
								<span style='color:".bestColor(@$prj->project_color_identifier).";text-decoration:none;'>
									<strong>".
										round ($vec['projtime'], 2)."
									</strong></span>
							</td>";
		$html .= "<td colspan='2' $bgcolor></td>";
		$html .= "</tr>\n\t";
		return $html;

	}
}
if (!(function_exists("showtasklink"))){
	function showtasklink($task_id){
		global $AppUI, $disable_edition,$timexp_type, $supervise_user;
		$perm=CTask::getTaskAccesses($task_id);
		$canRead = $perm["read"];

		$accessTaskLog = $perm["log"];
		$accessTaskExpense = $perm["expense"];
		$tabs=0;
		$tab=array();
		if ( $accessTaskLog <> PERM_DENY){
			$tab[1]=$tabs;
			$tabs++;
			if ( $accessTaskLog == PERM_EDIT){
				$tabs++;$tabs++;
			}
		}
		if ( $accessTaskExpense <> PERM_DENY){
			$tab[2] = $tabs;
			$tabs++;
			if ( $accessTaskExpense == PERM_EDIT){
				$tabs++;
			}
		}		
		$tab_str = "";
		if (isset($tab[$timexp_type])){
			$tab_str = "&tab=".strval($tab[$timexp_type]);
			if($disable_edition)
				$tab_str .= "&user=$supervise_user";
		}
		if ($canRead) {
			return "<a href=\"?m=tasks&a=view&task_id={$task_id}{$tab_str}\" title=\"".$AppUI->_("View")."\">$task_id</a>";
		}else{
			return "$task_id";
		}	

	}
}
if (!(function_exists("showtimexp"))){
	function showtimexp($row, $spvMode){
		global $AppUI, $iconsYN, $timexp_types, $timexp_status,  
				$timexp_status_color, $te_status_transition,
				$timexp_type, $disable_edition;
	// completar obt de permisos para editar y borrar y luego generar la l?ea.
		
		$canEdit = false;
		$texp = new CTimExp();
		$texp->load($row["timexp_id"]);
		$msg="";
		$canEdit = $texp->canEdit($msg) && !$disable_edition;
		$canDelete = $texp->canDelete($msgDelete) && !$disable_edition;
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
		if ($canEdit || $texp->timexp_applied_to_type == 3){
			$html .= "\n\t\t<a href=\"javascript: deleteIt{$row['timexp_type']}('{$row['timexp_id']}');\">"
				. "\n\t\t\t".'<img src="./images/icons/trash.gif" alt="'.$AppUI->_( 'Delete' ).'" border="0" width="20" height="20">'
				. "\n\t\t</a>";
		}
		$html .= "</td>";
		$html .= "<td title=\"{$row['timexp_description']}\">";
		$html .= "<a href=\"?m=timexp&a=view&timexp_id={$row['timexp_id']}\">".$row["timexp_name"]."</a></td>";
		$html .= "<td align=\"center\">".$tedate->format($df)."</td>";
		$html .= "<td align=\"center\">".$iconsYN[$row["timexp_billable"]]."</td>";
		$html .= "<td align=\"center\">".number_format($row["timexp_value"], 2)."</td>";
		$html .= "<td $bgcolor>";
		$html .= $AppUI->_($timexp_status[$row["timexp_last_status"]]);
		$html .= "</td>";
		$html .= "<td align=\"center\">";
		$html .= $iconsYN[$texp->isAvailable()];
		$html .= "</td>";		
		$html .= "</tr>";

		return $html;
	}
}

global  $timexp_type, $timexp_types, $timexp_applied_to_types, $qty_units , $supervise_user;

$df = $AppUI->getPref('SHDATEFORMAT');

// Si no hay definido un tipo v?ido
if (!$timexp_type || !isset($timexp_types[$timexp_type])){
		$AppUI->setMsg( "Timexp" );
		$AppUI->setMsg( "Missing Type", UI_MSG_ERROR, true );
		$AppUI->redirect();	
}




//modo supervisor
$spvMode = isset($supervised_users[$user_id]) && $_GET["a"]!="vw_myday";

// Obtengo todas las fechas con registros del tipo seleccionado
/*if ($spvMode){
	$supervised_timexp_id = CTimexpSupervisor::getSupervisedTimexpId();
	$dates = CTimExp::getTimExpDates( $user_id, $timexp_type, implode($supervised_timexp_id, ","));
}else{
	$dates = CTimExp::getTimExpDates( $user_id, $timexp_type);
}
//construyo el array de fechas
$list_dates[""]="All";
for($i=0; $i < count($dates) ;$i++){
	$tmpdate = new CDate($dates[$i]["timexp_date"]);
	$list_dates[$tmpdate->format(FMT_TIMESTAMP_DATE)] = $tmpdate->format($df);
}
*/


$sufix = $timexp_types[$timexp_type];

//variables de filtros
$sel_date_field = 'sel_date_'.strtolower($sufix);
if (isset( $_GET[$sel_date_field] )) {
    $AppUI->setState( 'TxpLstDD'.$timexp_type, $_GET[$sel_date_field] );
}
$sel_date = $AppUI->getState( 'TxpLstDD'.$timexp_type ) !== NULL ? $AppUI->getState( 'TxpLstDD'.$timexp_type ) : "";

$sel_project_field = 'sel_project_'.strtolower($sufix);
if (isset( $_GET[$sel_project_field] )) {
    $AppUI->setState( 'TxpLstDP'.$timexp_type, $_GET[$sel_project_field] );
}
$sel_project = $AppUI->getState( 'TxpLstDP'.$timexp_type ) !== NULL ? $AppUI->getState( 'TxpLstDP'.$timexp_type ) : NULL;



//$date = new CDate($sel_date);
$from_date=$_GET['from_year']."-".$_GET['from_month']."-".$_GET['from_day'];
$to_date=$_GET['to_year']."-".$_GET['to_month']."-".$_GET['to_day'];

// obtengo todos los registros del tipo seleccionado en la semana
if ($spvMode){
	$list = CTimExp::getTimExpDateList(
										$sup_user 					//USER_ID
									, NULL								//$date
									, $timexp_type				//timexp_type
									, $sel_project				//project_id
									, NULL								//task_id
									, NULL								//BUG_ID
									, NULL								// TIMEXP_ID
									, NULL								// ORDER BY
									, $from_date					// FROM DATE
									, $to_date						// TO DATE
									, NULL								// TIME EXP STATUS
									, NULL 								// BILLIABLE
									);
//implode($supervised_timexp_id, ",")
}else{
	$list = CTimExp::getTimExpDateList(
										$user_id						//USER_ID
									, NULL								//$date
									, $timexp_type				//timexp_type
									, $sel_project				//project_id
									, NULL								//task_id
									, NULL								//BUG_ID
									, NULL								// TIMEXP_ID
									, NULL								// ORDER BY
									, $from_date					// FROM DATE
									, $to_date						// TO DATE
									, NULL								// TIME EXP STATUS
									, NULL 								// BILLIABLE
									);
}


//lista de proyectos visibles al usuario
$tmpprj = new CProject();
$tmp_projects = $tmpprj->getAllowedRecords($user_id, "project_id, project_name", $orderby='project_name', $index="project_id");
unset($tmpprj);
$list_projects = array(""=>"All");
if (count($tmp_projects))
foreach($tmp_projects as $pid => $row){
	$list_projects[$pid]=$row["project_name"];
}





if (true){

//reordeno los resultados en varios arrays para facilitar el manejo de la info
$table = array (); 
$projects = array(); 
$tasks = array(); 
$bugs = array(); 
$naList = array();
for($i=0; $i < count($list); $i++){
	extract($list[$i],EXTR_PREFIX_ALL,"t");
	$app_id = "NULL";
	if (!is_null($t_task_id)){
		$app_id = $t_task_id;
		$tasks[$t_task_id] = $t_task_name;
	}
	if (!is_null($t_bug_id)){
		$app_id = $t_bug_id;
		$bugs[$t_bug_id] = $t_summary;
	}
	
	if (is_null($t_project_id)){
		$naList[$t_timexp_id]= $list[$i];
	}else{
		$projects[$t_project_id]=$t_project_name;
		$table["$t_project_id"]["$t_timexp_applied_to_type"]["$app_id"]["$t_timexp_id"]=$list[$i];
	}
}



$getvars= explode("&", $_SERVER["QUERY_STRING"]);
$hiddens = "";
for($i=0;$i<count($getvars); $i++){
	if (substr($getvars[$i],0, strlen($sel_date_field."=")) != $sel_date_field."="  &&
		substr($getvars[$i],0, strlen($sel_project_field."=")) != $sel_project_field."=" ){
		$tmpvals = explode("=",$getvars[$i]);
		$hiddens .= "<input type=\"hidden\" name=\"$tmpvals[0]\" value=\"$tmpvals[1]\" />\n";
	}
}
?>

<script language="javascript"><!--
function deleteIt<?php echo $timexp_type;?>(tid) {
	if (confirm( "<?=	$AppUI->_('doDeleteAdvice');?>" )) {
		var f = document.editFrm<?php echo $timexp_type;?>;
		f.timexp_id.value = tid;
		f.del.value = "1";
		f.dosql.value = "do_timexp_aed";
		f.submit();
	}
}
// --></script>

<table width="650" border="0" cellpadding="2" cellspacing="0" >
<tr class="tableForm_bg">
<form action="" method="GET">
<input type='hidden' name='sup_user' value='<?php echo $sup_user; ?>'>
<input type='hidden' name='m' value='<?php echo $_GET['m']; ?>'>
<input type='hidden' name='a' value='<?php echo $_GET['a']; ?>'>
<?php //echo $hiddens;?>
  <th colspan="10"><strong>
  <?php

//	echo $AppUI->_(($spvMode?"":"My ").$sufix."s")." - ";
	// MUESTRO FECHAS
/*	echo $AppUI->_("Date").": ".
		arraySelect( $list_dates, $sel_date_field, 'size="1" class="text" onchange="javascript: this.form.submit();"', $date->format(FMT_TIMESTAMP_DATE), true );
*/
	mkFromTo($from_date, $to_date, $AppUI);
	// MUESTRO PROYECTOS
	echo $AppUI->_("Project").": ".		
		arraySelect( $list_projects, $sel_project_field, 'size="1" class="text" onchange="javascript: this.form.submit();"', @$sel_project, true,false );
	echo "<!-- - " . $AppUI->_("Containing").": ";
?>
<input type='hidden' name='sup_user' value='<?php $_POST['sup_user'] ?>'>
<input class="formularios" type="text" size="15" name="txtsearch">
&nbsp;&nbsp;&nbsp;        <input type="submit" value="<?=$AppUI->_("Filter")?>" class="button">
-->
        </strong></th>
</tr>
</form>
</table>

<table width="650" border="0" cellpadding="2" cellspacing="0" class="">
<form name="editFrm<?php echo $timexp_type;?>" action="" method="POST">
<input type="hidden" name="del" value="" />
<input type="hidden" name="dosql" value="do_day_timexp_status_a" />	
<input type="hidden" name="timexp_id" value="" />
<input type="hidden" name="nxtscr" value="" />
<?php /* if ($spvMode){ ?>
<input type="hidden" name="timexp_status_id" value="" />
<input type="hidden" name="timexp_status_user" value="<?php echo $AppUI->user_id;?>" />
<?php }*/  ?>
<?
//cabeceras de la tabla 
?>
<tr class="tableHeaderGral">
	<th width="50px" nowrap="nowrap">&nbsp;</th>
	<th width="80%" nowrap="nowrap"><?=$AppUI->_("Applied to");?></th>
	<th width="50px" align="center" nowrap="nowrap"><?=$AppUI->_("Date");?></th>
	<th width="20px" align="center" nowrap="nowrap"><?=$AppUI->_("Billable");?></th>
	<th width="50px" align="center" nowrap="nowrap"><?=$AppUI->_($qty_units[$timexp_type]);?></th>
	<th width="50px" align="center" nowrap="nowrap"><?=$AppUI->_("Status");?></th>
	<th width="20px" align="center" nowrap="nowrap"><?=$AppUI->_("Available");?></th>
<?php /*if ($spvMode){ ?>
	<th nowrap="nowrap"><?=$AppUI->_("Change to");?></th>
<?php } */ ?>
</tr>

<?
/*if ($_GET['sup_user']!='') $sup_user=$_GET['sup_user'];
elseif ($_POST['sup_user']!='') $sup_user=$_POST['sup_user'];*/

$coltotal = array();
$html = "";
$nrofilas=0;
if (count($table)>0){
	if (count($projects)>0){
		foreach ($projects as $pid => $pname){
			$html .= showprojectrow($pid, $sup_user, $from_date, $to_date);
			//muestro las tareas que tienen reg asignados
			if (count($tasks)>0){
			foreach($tasks as $tid=>$tname){
				if (isset( $table[$pid]["1"][$tid])){
						$html .= "<tr><td colspan=\"20\">";
						$html .= '<table width="100%" border="0" cellpadding="2" cellspacing="0"><tr>';
						$html .= "<td  nowrap=\"nowrap\">"."[".$AppUI->_($timexp_applied_to_types[1]).": ".showtasklink($tid)."]</td>";
						$html .= "<td width=\"100%\">".$tname."</td>";
						$html .= '</tr></table>';
						$html .= "</td></tr>\n\t";
					foreach($table[$pid]["1"][$tid] as $timexp_id=>$row){
						$nrofilas++;
						$html .= showtimexp($row, $spvMode);
					}
                    $html .= "<tr class=\"tableRowLineCell\"><td colspan=\"99\"></td></tr>";
				}		
			}
			}	
			
			//muestro los bugs que tienen reg asignados
			if (count($bugs)>0){
			foreach($bugs as $bid=>$bname){
				if (isset( $table[$pid]["2"][$bid])){
						$html .= "<tr><td colspan=\"20\">";
						$html .= '<table width="100%" border="0" cellpadding="2" cellspacing="0"><tr>';
						$html .= "<td  nowrap=\"nowrap\">"."[".$AppUI->_($timexp_applied_to_types[2]).": ".$bid."]</td>";
						$html .= "<td width=\"100%\">".$bname."</td>";
						$html .= '</tr></table>';
						$html .= "</td></tr>\n\t";
					foreach($table[$pid]["2"][$bid] as $timexp_id=>$row){
						$nrofilas++;
						$html .= showtimexp($row, $spvMode);
					}
				}		
			}
			}
		}
	}	
}
echo $html;	

$where = "";

IF ($project_id==''){
	IF ($sup_user!=0) $sup_user="timexp_creator=$sup_user";
	else $sup_user="timexp_creator IS NOT NULL";
	if ($from_date!='--' and $to_date!='--'){
				$where .=" AND t.timexp_date >= '$from_date 00:00:00.000' \n\t";
				$where .=" AND t.timexp_date <= '$to_date 23:59:59.999' \n\t";
	}	
	$sql ="	SELECT 
						sum(t.timexp_value) AS projtime 
					FROM 
						timexp AS t 
					WHERE 
						t.timexp_applied_to_type = 3 AND
						t.timexp_creator=1
						$where";
	//echo "<br>$sql<br>";
	$vec=db_fetch_array(db_exec($sql));
}


$html = "";	
if (count($naList)>0){
	$html .= "<tr class='tableRowLineCell'><th colspan='4'><strong>- ".$AppUI->_($timexp_applied_to_types[3])." -</strong></th>";
						
	$html .= "<th $bgcolor align='center' class='tableRowLineCell'>".round ($vec['projtime'], 2)."</th>";
	$html .= "<th colspan='2' $bgcolor></th>";
	$html .= "</tr>\n\t";
	
	foreach($naList as $row){
		$nrofilas++;
		$html .= showtimexp($row, $spvMode);	
	}
}

echo $html;	
?>

<tr>
	<td colspan="10" align="right">
<?php /* if ($spvMode){ ?>
		<input type="submit" value="<?php echo $AppUI->_('update');?>" class="button" name="sqlaction2">
<?php } */ ?>
	</td>	
</tr>

</form>
</table>


<?
}


?>

<pre>
<?=//var_dump($table);?>
<?=//var_dump($tmp_projects);?>
</pre>
