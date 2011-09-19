<?php
include "{$AppUI->cfg['root_dir']}/modules/timexp/config.php";

$supervised_users = CTimexpSupervisor::getSupervisedUsers();
$user_id = $supervise_user ?  $supervise_user : $AppUI->user_id ;

$spvMode = isset($supervised_users) && $_GET["a"]!="vw_myday";

IF ($_POST['sup_user']!='') $sup_user=$_POST['sup_user'];
ELSEIF ($_GET['sup_user']!='') $sup_user=$_GET['sup_user'];
ELSEIF ($_GET["a"]=="vw_myday") $sup_user=$user_id;

$libre = $_GET['txtsearch'];
$bilable = $_GET['bilable'];
$status = $_GET['status'];
$applied_to_types = $_GET['applied_to_types'];

if($_GET["only_internal"])
{
$applied_to_types = "3";
}

 if (isset($_GET[from_hour]))
 {
   $from_h = $_GET[from_hour];
 }
 else
 {
   $from_h = "00";
 }

 if (isset($_GET[from_min]))
 {
   $from_m = $_GET[from_min];
 }
 else
 {
   $from_m = "00";
 }

 $from_hora = $from_h.":".$from_m.":00.000";


 if (isset($_GET[to_hour]))
 {
  $to_h = $_GET[to_hour];
 }
 else
 {
   $to_h = "23";
 }

 if (isset($_GET[to_min]))
 {
   $to_m = $_GET[to_min];
 }
 else
 {
   $to_m = "59";
 }

 $to_hora = $to_h.":".$to_m.":59.999";

if (!(function_exists("mkFromTo"))){
	function mkFromTo($from_date, $to_date, $from_hora, $to_hora, $AppUI){
		
		$from_hour = substr($from_hora, 0, 2);
		$from_min = substr($from_hora, 3, 2);
		$to_hour = substr($to_hora, 0, 2);
		$to_min = substr($to_hora, 3, 2);


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
				<TD><? echo $AppUI->_("From")?> :</TD>
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
				</TD>
				<TD><? echo $AppUI->_("To")?> :</TD>
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
					<!-- <INPUT type="image" src="images/arrow-right.gif" onclick="javascript: this.form.submit();"> -->
				</TD>
			</TR>
			<TR>
				<TD>Entre :</TD>
				<TD align="right">
					<select name='from_hour' size="1" class="text">
						<?php mkOption (00, 23, $from_hour ); ?>
					</select> : 
					<select name='from_min' size="1" class="text">
						<?php mkOption (00, 59, $from_min ); ?>
					</select> 
                   <TD>y :</TD> 
				   <TD align="right">
					 <select name='to_hour' size="1" class="text">
						<?php mkOption (00, 23, $to_hour ); ?>
					</select> : 
					<select name='to_min' size="1" class="text">
						<?php mkOption (00, 59, $to_min ); ?>
					</select>
					<!-- <INPUT type="image" src="images/arrow-right.gif" onclick="javascript: this.form.submit();"> -->
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

	function showprojectrow($project_id, $user_id, $from_date, $to_date, $from_hora, $to_hora, $timexp_type,$libre,$spvMode,$status,$bilable,$applied_to_types){
		global $AppUI;
        
		$where = "";
		IF ($user_id!=0) $user_sql = "timexp_creator=$user_id";
	    else $user_sql = "timexp_creator IS NOT NULL";

         
        $desde = substr($from_hora,0,2).substr($from_hora,3,2)."00";
		$hasta = substr($to_hora,0,2).substr($to_hora,3,2)."00";

		if ($from_hora!=NULL and $to_hora!=NULL){
		
		    if (($hasta=="000000")||($hasta=="235900"))
			{
			$hasta = "240000";
			}

        
		    if($timexp_type=="1"){

				$where .="AND (EXTRACT(HOUR_SECOND FROM t.timexp_end_time) <> '$desde' ) \n\t";
				$where .="AND (
							   ((IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  - 
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												)) > 0  
								)  \n\t";
		    }

		}	

		if ($status!=NULL){
			$where .=" and t.timexp_last_status = '$status' \n\t";
		}
		if ($bilable!=NULL){
			$where .=" and t.timexp_billable = '$bilable' \n\t";
		}			
		
		if (($applied_to_types!=NULL)&&($applied_to_types!="0"))
		{
			$where .=" and t.timexp_applied_to_type = '$applied_to_types' \n\t";
		}


		if ($libre != ""){
		
		//Reviso que no haya nada raro//
		$libre = eregi_replace("select","",$libre);
		$libre = eregi_replace("update","",$libre);	
		$libre = eregi_replace("delete","",$libre);
		$libre = eregi_replace("show","",$libre);
		$libre = eregi_replace("insert","",$libre);
		
        
		// busco en el nombre o la descripción del timexp, de la tarea y del bug
		$where .="and (t.timexp_name like '%$libre%' or t.timexp_description like '%$libre%' or tp.task_name like '%$libre%' or tp.task_description like '%$libre%' or bt.summary like '%$libre%' or td.description like '%$libre%') \n\t";
        
		}
        
		if (($from_hora!=NULL and $to_hora!=NULL)&&($timexp_type=="1")){

		$sql = " SELECT  sum(
		            IF (
					((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time))
							))/3600) > 0,
					 ((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time))
							))/3600),
					 24 +
					 ((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time))
							))/3600)
					 )
				 
				 ) 
				 AS projtime, 
		         sum(
						IF (
					((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time))
							))/3600) > 0,
					 ((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time))
							))/3600),
					 24 +
					 ((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time))
							))/3600)
					 )
						
					* u.user_cost_per_hour) as cost_p ";
        }
		else{
		$sql .= "SELECT  sum(t.timexp_value) AS projtime ";
		}

		$sql .= "   
						FROM timexp AS t   

                              				LEFT JOIN tasks AS tp 
                              				              ON ( tp.task_id=t.timexp_applied_to_id AND   t.timexp_applied_to_type = 1)

    					            LEFT JOIN btpsa_bug_table AS bt  
    					                          ON ( t.timexp_applied_to_id=bt.id AND t.timexp_applied_to_type = 2)

    						LEFT JOIN project_todo AS td  
    						              ON (  t.timexp_applied_to_id=td.id_todo  AND  t.timexp_applied_to_type = 4)

     					            LEFT JOIN projects AS p  
     					                         ON ( p.project_id=tp.task_project OR  p.project_id = bt.project_id OR  p.project_id = td.project_id  )
						LEFT JOIN users AS u
							 ON (
										u.user_id= t.timexp_creator  )
						WHERE 
								$user_sql AND 
								p.project_id=$project_id AND
                                t.timexp_type=$timexp_type 
							    AND t.timexp_date >= '$from_date 00:00:00' 
								AND t.timexp_date <= '$to_date 00:00:00'
								$where";

		//echo "<pre>".$sql."</pre>";
		$vec=db_fetch_array(db_exec($sql));
		$prj = new CProject();
		$prj->load($project_id);
		$bgcolor = " style=\"background-color: #".$prj->project_color_identifier."; \"";

		$html = "<tr><td colspan=4 $bgcolor>";
		$project_name = "<span style='color:".bestColor(@$prj->project_color_identifier).";text-decoration:none;'><strong>".$prj->project_name."</strong></span>";
		if ($prj->canRead())
			$html .= "<a href=\"?m=projects&a=view&project_id={$project_id}\" title=\"".$AppUI->_("View")."\">$project_name</strong></span></a>";
		else
			$html .= $project_name;
		
		$html .= "</td>";
		$html .= "<td $bgcolor align='center'>
								<span style='color:".bestColor(@$prj->project_color_identifier).";text-decoration:none;'>
									<strong>";
		if($timexp_type=="2"){$html .= "$";}
		$html .= round ($vec['projtime'], 2)."
									</strong></span>
							</td>";
		$html .= "<td colspan = 4 $bgcolor align=\"right\"><span style='color:".bestColor(@$prj->project_color_identifier).";text-decoration:none;'>";
        
		if(($spvMode)&&($timexp_type=="1")){
		$html .="<strong>$".round ($vec['cost_p'], 2)."</strong></span></td>";
        }else{
		$html .="&nbsp;</span></td>";
		}

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
			return "<a href=\"?m=tasks&a=view&task_id={$task_id}{$tab_str}\" title=\"".$AppUI->_("View")."\">$task_id </a>";
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
		$html = "<tr>";
		$html .= "<td>";
		$sufix = strtolower($timexp_types[$row["timexp_type"]]);
		
		if ($canEdit && $row["timexp_last_status"] != 3) {
			if($timexp_type=="1"){
			$html .= "<a href=\"?m=timexp&a=editime&timexp_id={$row['timexp_id']}\">"
				. '<img src="./images/icons/edit_small.gif" alt="'.$AppUI->_( 'Edit' ).'" border="0" width="20" height="20">'
				. "</a>";
			}
		
			if($timexp_type=="2" && $row["timexp_last_status"] != 3){
			$html .= "<a href=\"?m=timexp&a=addeditexpense&timexp_id={$row['timexp_id']}\">"
				. '<img src="./images/icons/edit_small.gif" alt="'.$AppUI->_( 'Edit' ).'" border="0" width="20" height="20">'
				. "</a>";
			}
		} 
		
		if ($canDelete && $row["timexp_last_status"] != 3){
			$html .= "<a href=\"javascript: deleteIt{$row['timexp_type']}('{$row['timexp_id']}');\">"
				. '<img src="./images/icons/trash_small.gif" alt="'.$AppUI->_( 'Delete' ).'" border="0">'
				. "</a>";
		}
		$html .= "</td>";
		
		if($texp->timexp_applied_to_type == 3){
			$sql = "select descrip_es,descrip_en from timexp_exp";
			$query = mysql_query($sql);
            
			while($nothing = mysql_fetch_array($query)){
               $vec[$nothing['descrip_en']] = $nothing['descrip_es'];
			}

            
			foreach($vec as $nada_en => $nada_es)
			{    
                 $desc_en = html_entity_decode($nada_en);
				 $row_en = html_entity_decode($row["timexp_name"]);
                 
				 if($desc_en == $row_en ){
					 $desc = $nada_es;
				 }
				 
			}
            
			if($desc == "")
			{
			  $desc = $row["timexp_name"];
			}

			$html .= "<td title=\"{$row['timexp_description']}\">";
			$html .= "<a href=\"?m=timexp&a=view&timexp_id={$row['timexp_id']}\">".$desc."</a></td>";
		
		}
		else{
        	$html .= "<td title=\"{$row['timexp_description']}\">";
			$html .= "<a href=\"?m=timexp&a=view&timexp_id={$row['timexp_id']}\">".$row["timexp_name"]."</a></td>";
		}
		
		$timesheetStatus = "";
		if($texp->timexp_timesheet != 0 && $texp->timexp_last_status == 0){
			//$timesheetStatus = $AppUI->_("linked to a timesheet");
		}
		
		
		$html .= "<td align=\"center\">".$tedate->format($df)."</td>";
		$html .= "<td align=\"center\">".$iconsYN[$row["timexp_billable"]]."</td>";
		$html .= "<td align=\"center\">";
		if($timexp_type=="2"){$html .= "$";}
		$html .= number_format($row["timexp_value"], 2)."</td>";
		$html .= "<td $bgcolor>";
		$html .= $AppUI->_($timexp_status[$row["timexp_last_status"]])." $timesheetStatus";
		$html .= "</td>";
		$html .= "<td align=\"center\">";
		$html .= $iconsYN[$texp->isAvailable()];
		$html .= "</td>";
		
        $sql_u = mysql_query("select user_username,user_cost_per_hour from users where user_id='".$row["timexp_creator"]."' ");
		$user_data = mysql_fetch_array($sql_u);
		
		$costo = $user_data["user_cost_per_hour"]*$row["timexp_value"];
        
        if ($spvMode){
			$html .= "<td align=\"center\">";
			$html .= $user_data["user_username"];
			$html .= "</td>";	
		if($timexp_type=="1"){
			$html .= "<td align=\"center\">$";
			$html .= number_format($costo, 2);
			$html .= "</td>";		
		}
		
		$html .= "</tr>";
		}

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
if ($status=="-1"){
$status = "";
}

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
									, $status						// TIME EXP STATUS
									, $bilable 								// BILLIABLE
									,$libre                          // Campo libre, para buscar en nombre o descripcion 
									,$from_hora
									,$to_hora
									,$spvMode
									,$applied_to_types
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
									, $status						// TIME EXP STATUS
									, $bilable 								// BILLIABLE
									,$libre                         // Campo libre, para buscar en nombre o descripcion 
									,$from_hora
									,$to_hora
									,$spvMode
									,$applied_to_types
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
$todos = array(); 
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

	if (!is_null($t_id_todo)){
		$app_id = $t_id_todo;
		$todos[$t_id_todo] = $t_description;
	}
	
	//if (is_null($t_project_id)){
	if ($t_timexp_applied_to_type=="3"){
		$naList[$t_timexp_id]= $list[$i];
	}
	if(!is_null($t_project_id)){
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
<?php $tableStyle = 'class="std" style="border-top-width:1px;border-bottom-width:0px;border-left-width:0px;border-right-width:0px;border-style:solid;border-color:black;"'; ?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td>
<table width="650" border="0" cellpadding="0" cellspacing="0" <?=$tableStyle?>>
<tr>
<form action="" method="GET">
<input type='hidden' name='sup_user' value="<?php echo $sup_user; ?>">
<input type='hidden' name='m' value='<?php echo $_GET['m']; ?>'>
<input type='hidden' name='a' value='<?php echo $_GET['a']; ?>'>
  <td colspan="10"><strong>
  <?php

    mkFromTo($from_date, $to_date, $from_hora, $to_hora, $AppUI);
?>
<table>
	<tr>
		<td>
		  <? echo $AppUI->_("Project").": ";?>
		</td>
		<td>
		  <? echo arraySelect( $list_projects, $sel_project_field, 'size="1" class="text" onchange="javascript: this.form.submit();"', @$sel_project, true,false ); ?>
		</td>
		<td>
		  <? echo $AppUI->_("Containing").": "; ?>
		</td>
		<td>
		 <input class="formularios" type="text" size="15" name="txtsearch">
		</td>
		<td>
		 &nbsp;&nbsp; <? echo $AppUI->_("Internal records"); ?>
		</td>
		<td>
		 <input type="checkbox" name="only_internal" <?if($_GET["only_internal"])echo "checked";?>>
		</td>
	</tr>
</table>
</td>
</tr>
<tr>
<td valign="top">
<table>
<tr>
	<td>
	 <? echo $AppUI->_("Status").": "; ?>
	</td>
	<td><?  
	
	foreach ($timexp_status as $key => $val) {
		$timexp_status_sort[$key] = $AppUI->_($val);
	}
	natcasesort($timexp_status_sort);
	 
	echo "<select name='status' size='1' class='text' >";
   
	echo "<option value='-1' >".$AppUI->_("All Status")."</option>\n";
        
		foreach ($timexp_status_sort as $key => $val) {
		if ($_GET[status] == $key) $sel_s ='selected';
		else $sel_s ='';
		if ($_GET[status] == '') $sel_s ='';

		echo "<option value='".$key."' $sel_s>".$val."</option>\n";
	    }

	echo "</select>";
	?>
	</td>
	<td><? echo $AppUI->_("Billable").": "; ?></td>
	<td>
	<?
	echo "<select name='bilable' size='1' class='text' >";

	if ($bilable=="0") $sel2='selected';
	if ($bilable=="1") $sel1='selected';
    
	echo "<option value='' $sel1>".$AppUI->_("All")."</option>\n";
	echo "<option value='0' $sel2>".$AppUI->_("No")."</option>\n";
	echo "<option value='1' $sel1>".$AppUI->_("Yes")."</option>\n";
	
	echo "</select>";
	?>
	</td>
	<td><? echo $AppUI->_("Type").": "; ?></td>
	<td>
	<?
	foreach ($timexp_applied_to_types as $key => $val) {
		$timexp_applied_to_types_sort[$key] = $AppUI->_($val);
	}
	natcasesort($timexp_applied_to_types_sort);
	
	echo "<select name='applied_to_types' size='1' class='text' >";
	   if ($applied_to_types=="0") $sel_a ='selected';
	   else $sel_a ='';

	echo "<option value='0' $sel_a>".$AppUI->_("All types")."</option>\n";
        
		foreach ($timexp_applied_to_types_sort as $key => $val) {
		if ($applied_to_types == $key) $sel_a ='selected';
		else $sel_a ='';
		echo "<option value='".$key."' $sel_a>".$val."</option>\n";
	    }

	echo "</select>";
	?>
	</td>
	<td>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="submit" value="<?=$AppUI->_("filter")?>" class="button">
	</td>
</tr>
</table>
</strong>
</td>
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
<tr class="tableHeaderGral" style="height:15px">
	<th width="50px" nowrap="nowrap">&nbsp;</th>
	<th width="80%" nowrap="nowrap"><?=$AppUI->_("Applied to");?></th>
	<th width="50px" align="center" nowrap="nowrap"><?=$AppUI->_("Date");?></th>
	<th width="20px" align="center" nowrap="nowrap"><?=$AppUI->_("Billable");?></th>
	<th width="50px" align="center" nowrap="nowrap"><?=$AppUI->_($qty_units[$timexp_type]);?></th>
	<th width="50px" align="center" nowrap="nowrap"><?=$AppUI->_("Status");?></th>
	<th width="20px" align="center" nowrap="nowrap"><?=$AppUI->_("Available");?></th>
	<? if ($spvMode){?>
	<th width="30px" align="center" nowrap="nowrap"><?=$AppUI->_("User");?></th>
	<? if($timexp_type=="1"){?>
	<th width="20px" align="center" nowrap="nowrap"><?=$AppUI->_("Cost");?></th>
	<?}}?>
<?php /*if ($spvMode){ ?>
	<th nowrap="nowrap"><?=$AppUI->_("Change to");?></th>
<?php } */ ?>
</tr>
<?
$coltotal = array();
$html = "";
$nrofilas=0;
$total_h = 0;
$total_c = 0;
if (count($table)>0){
	if (count($projects)>0){
		foreach ($projects as $pid => $pname){
			$html .= showprojectrow($pid, $sup_user, $from_date, $to_date, $from_hora, $to_hora, $timexp_type,$libre,$spvMode,$status,$bilable,$applied_to_types); 

            
			//muestro las tareas que tienen reg asignados
			if (count($tasks)>0){
			foreach($tasks as $tid=>$tname){
				if (isset( $table[$pid]["1"][$tid])){
                      
					  IF ($sup_user!=0) $user_sql = "timexp_creator=$sup_user";
	                  else $user_sql = "timexp_creator IS NOT NULL";

                       // Hago la sumatoria por tarea //
                        $desde = substr($from_hora,0,2).substr($from_hora,3,2)."00";
						$hasta = substr($to_hora,0,2).substr($to_hora,3,2)."00";

						if (($from_hora!=NULL and $to_hora!=NULL)&&($timexp_type=="1")){
						
						if (($hasta=="000000") || ($hasta=="235900"))
						{
						$hasta = "240000";
						}

							$where .="AND (EXTRACT(HOUR_SECOND FROM t.timexp_end_time) <> '$desde' ) \n\t";
							$where .="AND (
						   ((IF ((EXTRACT(HOUR_SECOND FROM
									t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
									t.timexp_end_time))
										  - 
											IF ((EXTRACT(HOUR_SECOND FROM
									t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
									t.timexp_start_time))
											)) > 0  
							)  \n\t";
						}
                        
						if (($status!=NULL)&&($status!="-1")){
							$where .=" and t.timexp_last_status = '$status' \n\t";
						}
						if ($billable!=NULL){
							$where .=" and t.timexp_billable = '$billable' \n\t";
						}

						if ($libre != ""){

						//Reviso que no haya nada raro//
						$libre = eregi_replace("select","",$libre);
						$libre = eregi_replace("update","",$libre);	
						$libre = eregi_replace("delete","",$libre);
						$libre = eregi_replace("show","",$libre);
						$libre = eregi_replace("insert","",$libre);
						

						// busco en el nombre o la descripción del timexp, de la tarea y del bug
						$where .="and (t.timexp_name like '%$libre%' or t.timexp_description like '%$libre%' or tp.task_name like '%$libre%' or tp.task_description like '%$libre%' or bt.summary like '%$libre%' or td.description like '%$libre%') \n\t";
						}
                     
					if (($from_hora!=NULL and $to_hora!=NULL)&&($timexp_type=="1")){

					$query_st = " 
					              SELECT sum(
									  IF (
										((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600) > 0,
										 ((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600),
										 24 +
										 ((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600)
										 )
									  ) AS tasktime, sum(
									  IF (
										((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600) > 0,
										 ((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600),
										 24 +
										 ((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta'),'$hasta',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde'),'$desde',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600)
										 )
									  * u.user_cost_per_hour) as cost 
							    ";

					}else{
                    $query_st = " SELECT sum(t.timexp_value) AS tasktime";
					}


					$query_st .= "
						FROM timexp AS t 
						left join tasks tp on t.timexp_applied_to_id=tp.task_id and t.timexp_applied_to_type = 1
						left join btpsa_bug_table bt on t.timexp_applied_to_id=bt.id and t.timexp_applied_to_type = 2
						left join project_todo td on t.timexp_applied_to_id=td.id_todo and t.timexp_applied_to_type = 4 
						left join projects p on p.project_id = tp.task_project or  p.project_id = bt.project_id or  p.project_id = td.project_id
						LEFT JOIN users AS u
							ON (
										u.user_id= t.timexp_creator  )
						WHERE   $user_sql AND
								timexp_applied_to_id = $tid  AND
                                t.timexp_type=$timexp_type AND
							    t.timexp_applied_to_type = 1
						        AND t.timexp_date >= '$from_date 00:00:00' 
								AND t.timexp_date <= '$to_date 00:00:00'
								$where";

						//echo "<pre>".$query_st."</pre>";
						$sql_st = mysql_query($query_st);
						$vec=mysql_fetch_array($sql_st);
                        $where = "";
					 	
                        //Link a la tarea
						$html .= "<tr>";
						$html .= "<td colspan=4 nowrap=\"nowrap\">"."[".$AppUI->_($timexp_applied_to_types[1]).": ".showtasklink($tid)."]&nbsp;";
						$html .= $tname."</td>";
						$html .= "<td align=\"center\"><strong>";

						if($timexp_type=="2"){$html .= "$";}
						$html .= round ($vec['tasktime'], 2)."</strong></td>";
						$total_h = $total_h + $vec['tasktime'];
						$total_c = $total_c + $vec['cost'];
						
						if (($spvMode)&&($timexp_type=="1")){
						$html .= "<td width=\"140px\" align=\"right\"><B>$".round ($vec['cost'], 2)."</B></td>";
						}else{
						$html .= "<td width=\"140px\" align=\"right\">&nbsp;</td>";
						}

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

					    IF ($sup_user!=0) $user_sql = "timexp_creator=$sup_user";
	                    else $user_sql = "timexp_creator IS NOT NULL";
				        
						 // Hago la sumatoria por tarea //
                        $desde_st = substr($from_hora,0,2).substr($from_hora,3,2)."00";
						$hasta_st = substr($to_hora,0,2).substr($to_hora,3,2)."00";

						if (($from_hora!=NULL and $to_hora!=NULL)&&($timexp_type=="1")){

						if (($hasta_st=="000000")||($hasta_st=="235900"))
						{
						$hasta_st = "240000";
						}
			

						$where_st .="AND (EXTRACT(HOUR_SECOND FROM t.timexp_end_time) <> '$desde_st' ) \n\t";
					    $where_st .="AND (
										   ((IF ((EXTRACT(HOUR_SECOND FROM
													t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
													t.timexp_end_time))
														  - 
															IF ((EXTRACT(HOUR_SECOND FROM
													t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
													t.timexp_start_time))
															)) > 0  
											)  \n\t";
						}	
                         

						if (($status!=NULL)&&($status!="-1")){
							$where_st .=" and t.timexp_last_status = '$status' \n\t";
						}
						if ($billable!=NULL){
							$where_st .=" and t.timexp_billable = '$billable' \n\t";
						}				


						if ($libre != ""){
		
						//Reviso que no haya nada raro//
						$libre = eregi_replace("select","",$libre);
						$libre = eregi_replace("update","",$libre);	
						$libre = eregi_replace("delete","",$libre);
						$libre = eregi_replace("show","",$libre);
						$libre = eregi_replace("insert","",$libre);
						

						// busco en el nombre o la descripción del timexp, de la tarea y del bug
						$where_st ="and (t.timexp_name like '%$libre%' or t.timexp_description like '%$libre%' or tp.task_name like '%$libre%' or tp.task_description like '%$libre%' or bt.summary like '%$libre%' or td.description like '%$libre%') \n\t";
						}
                   
						// Hago la sumatoria por Incidencia //
                        
                        if (($from_hora!=NULL and $to_hora!=NULL)&&($timexp_type=="1")){

						$query_st = " 
									  SELECT sum(
										  IF (
										((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600) > 0,
										 ((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600),
										 24 +
										 ((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600)
										 )
										  ) AS tasktime, sum(
										  IF (
										((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600) > 0,
										 ((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600),
										 24 +
										 ((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600)
										 )
										  * u.user_cost_per_hour) as cost 
									";

						}else{
						$query_st = " SELECT sum(t.timexp_value) AS tasktime";
						}

						$query_st .= "
									 from timexp as t
									 left join tasks tp on t.timexp_applied_to_id=tp.task_id and t.timexp_applied_to_type = 1
									 left join btpsa_bug_table bt on t.timexp_applied_to_id=bt.id and t.timexp_applied_to_type = 2
									 left join project_todo td on t.timexp_applied_to_id=td.id_todo and t.timexp_applied_to_type = 4 
									 left join projects p on p.project_id = tp.task_project or  p.project_id = bt.project_id or  p.project_id = td.project_id
									 LEFT JOIN users AS u
							         ON (
										u.user_id= t.timexp_creator  )
									 where $user_sql
									 AND timexp_applied_to_id = $bid 
									 AND t.timexp_type= '$timexp_type'  
									 AND t.timexp_applied_to_type= '2'
									 AND t.timexp_date >= '$from_date 00:00:00' 
									 AND t.timexp_date <= '$to_date 00:00:00'
									 $where_st
									 "; 
						

						//echo "<pre>".$query_st."</pre>";
						
						$sql_st = mysql_query($query_st);
						$vec=mysql_fetch_array($sql_st);
                        $where_st ="";

						$html .= "<tr>";
						$html .= "<td colspan=4 nowrap=\"nowrap\">"."[".$AppUI->_($timexp_applied_to_types[2]).": ".$bid."]&nbsp;";
						$html .= $bname."</td>";
						
						if (($spvMode)&&($timexp_type=="1")){
						$html .= "<td align=\"left\"><strong>".round ($vec['tasktime'], 2)."</strong></td>";
						$html .= "<td width=\"140px\" align=\"right\"><B>$".round ($vec['cost'], 2)."</B></td>";
						}else{
							$html .= "<td align=\"center\"><strong>";
						    if($timexp_type=="2"){$html .= "$";}
						    $html .= round ($vec['tasktime'], 2)."</strong></td>";
						$html .= "<td width=\"140px\" align=\"right\">&nbsp;</td>";
						}

                        $total_h = $total_h + $vec['tasktime'];
						$total_c = $total_c + $vec['cost'];

						$html .= "</td></tr>\n\t";
					foreach($table[$pid]["2"][$bid] as $timexp_id=>$row){
						$nrofilas++;
						$html .= showtimexp($row, $spvMode);
					}
					 $html .= "<tr class=\"tableRowLineCell\"><td colspan=\"99\"></td></tr>";
				}
			}
			}
           
			//muestro los to-do's que tienen reg asignados
			if (count($todos)>0){
			foreach($todos as $bid=>$toname){
				if (isset( $table[$pid]["4"][$bid])){
				        
						IF ($sup_user!=0) $user_sql = "timexp_creator=$sup_user";
	                    else $user_sql = "timexp_creator IS NOT NULL";

						 // Hago la sumatoria por tarea //
                        $desde_st = substr($from_hora,0,2).substr($from_hora,3,2)."00";
						$hasta_st = substr($to_hora,0,2).substr($to_hora,3,2)."00";

						if (($from_hora!=NULL and $to_hora!=NULL)&&($timexp_type=="1")){

							if (($hasta_st=="000000")||($hasta_st=="235900"))
							{
							$hasta_st = "240000";
							}

						$where_st .="AND (EXTRACT(HOUR_SECOND FROM t.timexp_end_time) <> '$desde_st' ) \n\t";
					    $where_st .="AND (
										   ((IF ((EXTRACT(HOUR_SECOND FROM
													t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
													t.timexp_end_time))
														  - 
															IF ((EXTRACT(HOUR_SECOND FROM
													t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
													t.timexp_start_time))
															)) > 0  
											)  \n\t";
						}	

						if (($status!=NULL)&&($status!="-1")){
							$where_st .=" and t.timexp_last_status = '$status' \n\t";
						}
						if ($billable!=NULL){
							$where_st .=" and t.timexp_billable = '$billable' \n\t";
						}		

						if ($libre != ""){
		
						//Reviso que no haya nada raro//
						$libre = eregi_replace("select","",$libre);
						$libre = eregi_replace("update","",$libre);	
						$libre = eregi_replace("delete","",$libre);
						$libre = eregi_replace("show","",$libre);
						$libre = eregi_replace("insert","",$libre);
						

						// busco en el nombre o la descripción del timexp, de la tarea y del bug
						$where_st ="and (t.timexp_name like '%$libre%' or t.timexp_description like '%$libre%' or tp.task_name like '%$libre%' or tp.task_description like '%$libre%' or bt.summary like '%$libre%' or td.description like '%$libre%') \n\t";
						}
                   
						// Hago la sumatoria por Incidencia //
                        
                        if (($from_hora!=NULL and $to_hora!=NULL)&&($timexp_type=="1")){

						$query_st = " 
									   SELECT sum(
									  IF (
										((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600) > 0,
										 ((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600),
										 24 +
										 ((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600)
										 )
									  ) AS todotime, sum(
									  IF (
										((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600) > 0,
										 ((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600),
										 24 +
										 ((TIME_TO_SEC(
											  IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_end_time))
											  )- TIME_TO_SEC(
												IF ((EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
										t.timexp_start_time))
												))/3600)
										 )
									  * u.user_cost_per_hour) as cost 
									";

						}else{
						$query_st = " SELECT sum(t.timexp_value) AS todotime";
						}

						$query_st .= "
									 from timexp as t
									 left join tasks tp on t.timexp_applied_to_id=tp.task_id and t.timexp_applied_to_type = 1
									 left join btpsa_bug_table bt on t.timexp_applied_to_id=bt.id and t.timexp_applied_to_type = 2
									 left join project_todo td on t.timexp_applied_to_id=td.id_todo and t.timexp_applied_to_type = 4 
									 left join projects p on p.project_id = tp.task_project or  p.project_id = bt.project_id or  p.project_id = td.project_id
									 LEFT JOIN users AS u
							         ON (
										u.user_id= t.timexp_creator  )
									 WHERE $user_sql  AND
												p.project_id='$pid'
										        AND timexp_applied_to_id = '$bid'
												AND t.timexp_type= '$timexp_type'
												AND t.timexp_applied_to_type= '4'
												AND t.timexp_date >= '$from_date 00:00:00'
												AND t.timexp_date <= '$to_date 00:00:00'";
						
						//die ("<pre>".$query_st."</pre>");
						
						$sql_st = mysql_query($query_st);
						$vec=mysql_fetch_array($sql_st);
                        $where_st ="";

						$html .= "<tr>";
						$html .= "<td colspan=4 nowrap=\"nowrap\">"."[".$AppUI->_($timexp_applied_to_types[4]).": ".$bid."]&nbsp;";
						$html .= $toname."</td>";
						
						if (($spvMode)&&($timexp_type=="1")){
						$html .= "<td align=\"left\"><strong>".round ($vec['todotime'], 2)."</strong></td>";
						$html .= "<td width=\"140px\" align=\"right\"><B>$".round ($vec['cost'], 2)."</B></td>";
						}else{
						$html .= "<td align=\"center\"><strong>";
						if($timexp_type=="2"){$html .= "$";}					
						$html .= round ($vec['todotime'], 2)."</strong></td>";
						$html .= "<td width=\"140px\" align=\"right\">&nbsp;</td>";
						}
						$html .= "</td></tr>\n\t";

						$total_h = $total_h + $vec['todotime'];
						$total_c = $total_c + $vec['cost'];

					foreach($table[$pid]["4"][$bid] as $timexp_id=>$row){
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
	
	IF ($sup_user!=0) $user_sql = "timexp_creator=$sup_user";
	else $user_sql = "timexp_creator IS NOT NULL";

	 // Hago la sumatoria por tarea //
                        $desde_st = substr($from_hora,0,2).substr($from_hora,3,2)."00";
						$hasta_st = substr($to_hora,0,2).substr($to_hora,3,2)."00";

						if (($from_hora!=NULL and $to_hora!=NULL)&&($timexp_type=="1")){
							if (($hasta_st=="000000")||($hasta_st=="235900"))
							{
							$hasta_st = "240000";
							}

						$where .="AND (EXTRACT(HOUR_SECOND FROM t.timexp_end_time) <> '$desde_st' ) \n\t";
					    $where .="AND (
										   ((IF ((EXTRACT(HOUR_SECOND FROM
													t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
													t.timexp_end_time))
														  - 
															IF ((EXTRACT(HOUR_SECOND FROM
													t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
													t.timexp_start_time))
															)) > 0  
											)  \n\t";
						}	

						if (($status!=NULL)&&($status!="-1")){
							$where .=" and t.timexp_last_status = '$status' \n\t";
						}
						if ($billable!=NULL){
							$where.=" and t.timexp_billable = '$billable' \n\t";
						}		

						if ($libre != ""){
		
						//Reviso que no haya nada raro//
						$libre = eregi_replace("select","",$libre);
						$libre = eregi_replace("update","",$libre);	
						$libre = eregi_replace("delete","",$libre);
						$libre = eregi_replace("show","",$libre);
						$libre = eregi_replace("insert","",$libre);
						

						// busco en el nombre o la descripción del timexp, de la tarea y del bug
						$where .="and (t.timexp_name like '%$libre%' or t.timexp_description like '%$libre%' or tp.task_name like '%$libre%' or tp.task_description like '%$libre%' or bt.summary like '%$libre%' or td.description like '%$libre%') \n\t";
						}
                   
	if ($from_date!='--' and $to_date!='--'){
				$where .=" AND t.timexp_date >= '$from_date 00:00:00' \n\t";
				$where .=" AND t.timexp_date <= '$to_date 00:00:00' \n\t";
	}	
    
    if (($from_hora!=NULL and $to_hora!=NULL)&&($timexp_type=="1")){
	$sql = " 
				SELECT sum(
				IF (
					((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time))
							))/3600) > 0,
					 ((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time))
							))/3600),
					 24 +
					 ((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time))
							))/3600)
					 )
				) AS notime, sum(
				IF (
					((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time))
							))/3600) > 0,
					 ((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time))
							))/3600),
					 24 +
					 ((TIME_TO_SEC(
						  IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time)>'$hasta_st'),'$hasta_st',EXTRACT(HOUR_SECOND FROM
					t.timexp_end_time))
						  )- TIME_TO_SEC(
							IF ((EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time)<'$desde_st'),'$desde_st',EXTRACT(HOUR_SECOND FROM
					t.timexp_start_time))
							))/3600)
					 )
				* u.user_cost_per_hour) as cost 
				";
	}else{
	$sql = " SELECT sum(t.timexp_value) AS notime";
	}

	$sql .= "	
					FROM 
						timexp AS t 
					    left join tasks tp on t.timexp_applied_to_id=tp.task_id and t.timexp_applied_to_type = 1
						left join btpsa_bug_table bt on t.timexp_applied_to_id=bt.id and t.timexp_applied_to_type = 2
						left join project_todo td on t.timexp_applied_to_id=td.id_todo and t.timexp_applied_to_type = 4 
						left join projects p on p.project_id = tp.task_project or  p.project_id = bt.project_id or  p.project_id = td.project_id
					LEFT JOIN users AS u
							         ON (
										u.user_id= t.timexp_creator  )
					WHERE 
						$user_sql
						AND t.timexp_type= '$timexp_type'  
						AND t.timexp_applied_to_type= '3'
						AND t.timexp_date >= '$from_date 00:00:00' 
						AND t.timexp_date <= '$to_date 00:00:00'
						$where";
                      

    //echo "<pre>".$sql."</pre>";
	$vec=db_fetch_array(db_exec($sql));
}

$html = "";	
if (count($naList)>0){
	$html .= "<tr class='tableRowLineCell'><th colspan='4'><strong>- ".$AppUI->_($timexp_applied_to_types[3])." -</strong></th>";
						
	$html .= "<th $bgcolor align='center' class='tableRowLineCell'>";
	if ($timexp_type=="2"){
		$html .="$";
	}
	$html .= round ($vec['notime'], 2)."</th>";
	$html .= "<th colspan='4' $bgcolor align=\"right\">";
	if (($spvMode)&&($timexp_type=="1")){
	$html .= "<B>$".round ($vec['cost'], 2)."</B>";
	}else{
	$html .= "&nbsp;</B>";
	}
	$html .= "</th></tr>\n\t";
	$total_h = $total_h + $vec['notime'];
	$total_c = $total_c + $vec['cost'];


	foreach($naList as $row){
		$nrofilas++;
		
		$html .= showtimexp($row, $spvMode);	
	}
}

echo $html;	
?>

<tr>
  <td colspan="97">
    &nbsp;
  </td>
</tr>
<?
if($total_h >0){
?>
<tr class="tableRowLineCell">
  <td colspan="4" >
    <B>TOTAL</B>
  </td>
  <td align="center"><b>
    <? 
    	if($timexp_type=="2") echo "$";
    	echo round($total_h, 2);
    ?></b>
  </td>
  <td colspan="4" align="right">
    <? if(($spvMode)&&($timexp_type=="1")){ echo "<b>$".round($total_c, 2)."</b>";} ?>
  </td>
</tr>
<tr>
<? } ?>
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
<?// echo var_dump($table); ?>
<?// echo var_dump($tmp_projects);?>
</pre>