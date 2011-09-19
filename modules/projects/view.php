<?php /* PROJECTS $Id: view.php,v 1.13 2009-07-30 15:50:43 nnimis Exp $ */

$project_id = intval( dPgetParam( $_GET, "project_id", 0 ) );

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjVwTab', $_GET['tab'] );
}

$tab = $AppUI->getState( 'ProjVwTab' ) !== NULL ? $AppUI->getState( 'ProjVwTab' ) : 0;

// check if this record has dependancies to prevent deletion
$msg = '';
$obj = new CProject();

$canDelete = $obj->canDelete( $msg, $project_id );
if (!$obj->load($project_id, false)){
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();

}

//get and check permissions
$canRead =$obj->canRead();
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$canEdit = $obj->canEdit();
#echo "Can edit: $canEdit - ";
$canReadDetails = $obj->canReadDetails();
#echo "Can Read Details: $canReadDetails - ";
$canReadEcValues = $obj->canReadEcValues();
#echo "Can Read Ec. Values: $canReadEcValues - ";
$canReadCompany = $obj->canReadCompany();
#echo "Can Read Company: $canReadCompany ";
$canAddTasks = $obj->canAddTasks();

$canManageRoles = $obj->canManageRoles();

if ($project_id!="0")
{
 // Si no es un projecto nuevo, me fijo si tiene tareas y pongo la fecha de finalizacion de la tarea que termine mas tarde//

 $query = "select task_end_date from tasks where task_project='$project_id' order by task_end_date desc";
 $sql = mysql_query($query);
                     
 $fecha_fin = mysql_fetch_array($sql);

 if ($fecha_fin[0]=="")
	{
	 $query = "select project_start_date from projects where project_id='$project_id'";
	 $sql = mysql_query($query);
						 
	 $fecha_comienzo = mysql_fetch_array($sql);
	 
     // Actualizo la base de datos //

	 $query2 = "UPDATE projects SET project_actual_end_date = '$fecha_comienzo[0]' WHERE project_id=$project_id";

	 $sql2 = mysql_query($query2);

    }
	else
	 {

	 // Actualizo la base de datos //

	 $query3 = "UPDATE projects SET project_actual_end_date = '$fecha_fin[0]' WHERE project_id=$project_id";

	 $sql3 = mysql_query($query3);
	 }
}



$sql = "
SELECT
	p.company_name,
	c.company_name AS canal_name,
	CONCAT_WS(' ',user_first_name,user_last_name) user_name,
	projects.*,
	IF(
		COUNT(distinct t1.task_id) = 0, '0/0',
		CONCAT_WS('/',
			(
				SUM(
					CASE
						WHEN t1.task_complete='1' THEN 1
						ELSE 0
					END
				)
			),
			COUNT(distinct t1.task_id)
		)
	) AS project_percent_completed_tasks,
	
	IF(
		project_target_budget = 0, 0,
		IF(
			COUNT(distinct t1.task_id) = 0, 0,
			ROUND(
				(
					SUM(
						CASE
							WHEN t1.task_complete='1' THEN t1.task_target_budget
							ELSE 0
						END
					)
					/
					project_target_budget
				)
				*20
			)
			*5
		)
	)
	AS project_percent_completed_oozed_cost,

	IF(
		COUNT(distinct t1.task_id) = 0, 0,
		IF( SUM(t1.task_duration) < 1, 0,
			ROUND((
				SUM(
					CASE
						WHEN t1.task_complete='1' THEN t1.task_duration
						ELSE 0
					END
				)
				/
				SUM(t1.task_duration)
			)*100)
		)
	)
	AS project_percent_completed_work
	
FROM projects
LEFT JOIN companies AS p 
	ON (p.company_id = project_company)
LEFT JOIN companies AS c 
	ON (c.company_id = project_canal)
LEFT JOIN users 
	ON (user_id = project_owner)
LEFT JOIN tasks t1 
	ON (projects.project_id = t1.task_project)
WHERE project_id = $project_id
GROUP BY project_id 
";

//echo "sql:".$sql;


$obj = null;
if (!db_loadObject( $sql, $obj )) {
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// Get the owners for the project
$owners=CProject::getOwners($project_id);


$worked_hours = CProject::getWorkedHours($project_id);

$total_hours = CProject::getTotalHours($project_id);

$total_expenses = CProject::getExpenses($project_id);

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

// create Date objects from the datetime fields
$start_date = intval( $obj->project_start_date ) ? new CDate( $obj->project_start_date ) : null;
$end_date = intval( $obj->project_end_date ) ? new CDate( $obj->project_end_date ) : null;
$actual_end_date = intval( $obj->project_actual_end_date ) ? new CDate( $obj->project_actual_end_date ) : null;

// Me fijo si se puede importar tareas desde este proyecto

$canImport = true;

# Si el proyecto tiene tareas no se puede importar nada (por ahora)
$query_count_tasks = "SELECT count(task_id) FROM tasks WHERE task_project = '".$project_id."' ";
$count_tasks = db_loadResult($query_count_tasks);

if($count_tasks > 0 )
{
	$canImport = false;
}

// Si no puede agregar tareas tampoco puede importarlas 
if (!$canAddTasks) {
	$canImport = false;
}

// setup the title block
$titleBlock = new CTitleBlock( 'View Project', 'projects.gif', $m, "$m.$a" );

//$newTask = "<form action=\"?m=tasks&a=addedit&task_project=$project_id\" method=\"post\">";
$newTask = "<input type=\"button\" class=\"buttontitle\" onmouseout=\"this.className='buttontitle';\" onmouseover=\"this.className='buttontitleover';\" value=\"".$AppUI->_('new task')."\"";
$newTask .= " onclick=\"javascript:location.href='./index.php?m=tasks&a=addedit&task_project=$project_id';\">";

$buttons = $viewGantt;

if ($canAddTasks) {
	$buttons .= "&nbsp;".$newTask;
}

$titleBlock->addCell($buttons, '', '', '');

/*if ($canAddTasks) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new task').'">', '',
		'<form action="?m=tasks&a=addedit&task_project=' . $project_id . '" method="post">', '</form>&nbsp;'.$viewGantt
	);
}*/

//$titleBlock->addCrumb( "?m=projects", "projects list" );

if ($canEdit) {
	$titleBlock->addCrumb( "?m=projects&a=addedit&project_id=$project_id", "edit this project" );
	$titleBlock->addCrumbDelete( 'delete project', $canDelete, $msg );
	$titleBlock->addCrumb( "?m=projects&a=vw_baselines&project_id=$project_id", "baselines" );
}
if ($canReadCompany) {
	$titleBlock->addCrumb( "?m=companies&a=view&company_id=$obj->project_company", "view company" );
}
if ($canImport){
	$titleBlock->addCrumb("?m=projects&a=import_tasks&task_project=".$project_id,"import XML MS-Project");
}

if ($canAddTasks) {
	$titleBlock->addCrumb( "JavaScript:openGantt();", "gantt view" );
}

include_once('./modules/public/itemToFavorite_functions.php');
$deleteFavorite = HasItemInFavorites($project_id, 1);

$titleBlock->addCrumb( "javascript:itemToFavorite(".$project_id.", 1, $deleteFavorite);", $deleteFavorite == 1 ? $AppUI->_('remove from favorites') : $AppUI->_('add to favorites') );

$titleBlock->show();
?>

<script language="javascript">
function openGantt(){
	window.open('./index.php?m=tasks&a=viewgantt&project_id=<?=$project_id?>&dialog=1&suppressLogo=1', '_blank', 'top=0,left=0,width=1015, height=520, scrollbars=yes, status=no' );
}

function itemToFavorite(item_id, item_type, item_delete)
{
	window.top.location = "./index.php?m=public&a=itemToFavorite&item_id=" + item_id + "&item_type=" + item_type + "&item_mode_del=" + item_delete + "&dialog=1&suppressLogo=1";
}

function delIt() {
	if (confirm( "<?=$AppUI->_('doDeleteAdvice');?>" )) {
		document.frmDelete.submit();
	}
}



</script>

<TABLE border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<FORM name="frmDelete" action="./index.php?m=projects" method="post">
	<INPUT type="hidden" name="dosql" value="do_project_aed" />
	<INPUT type="hidden" name="del" value="1" />
	<INPUT type="hidden" name="project_id" value="<?php echo $project_id;?>" />
</FORM>

<TR>
	<TD style="border: outset #d1d1cd 1px;background-color:#<?php echo $obj->project_color_identifier;?>" colspan="2">
	<?php
		echo '<font color="' . bestColor( $obj->project_color_identifier ) . '"><strong>'
			. $obj->project_name .'<strong></font>';
	?>
	</TD>
</TR>

<TR>
<?php 
if ($canReadDetails){
?>	
	<TD width="50%" valign="top">

		<STRONG><?php echo $AppUI->_('Details');?></STRONG>
		<TABLE cellspacing="1" cellpadding="2" border="0" width="100%">
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Company');?>:</TD>
			<TD class="hilite" width="100%"><?php echo htmlspecialchars( $obj->company_name, ENT_QUOTES) ;?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Canal');?>:</TD>
			<TD class="hilite" width="100%"><?php echo htmlspecialchars( $obj->canal_name, ENT_QUOTES) ;?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Short Name');?>:</TD>
			<TD class="hilite"><?php echo htmlspecialchars( @$obj->project_short_name, ENT_QUOTES) ;?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Start Date');?>:</TD>
			<TD class="hilite"><?php echo $start_date ? $start_date->format( $df ) : '-';?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Target End Date');?>:</TD>
			<TD class="hilite"><?php echo $end_date ? $end_date->format( $df ) : '-';?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Actual End Date');?>:</TD>
			<TD class="hilite"><?php echo $actual_end_date ? $actual_end_date->format( $df ) : '-';?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Status');?>:</TD>
			<TD class="hilite" width="100%"><?php echo $AppUI->_($pstatus[$obj->project_status]);?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Active');?>:</TD>
			<TD class="hilite" width="100%"><?php echo $obj->project_active ? $AppUI->_('Yes') : $AppUI->_('No'); ?></TD>
		</TR>		
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Project Owner');?>:</TD>
			<TD class="hilite"><?php echo htmlspecialchars( $obj->user_name, ENT_QUOTES) ; ?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Project Administrators');?>:</TD>
			<TD class="hilite"><?php echo htmlspecialchars( implode($owners,", "), ENT_QUOTES) ; ?></TD>
		</TR>		
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('URL');?>:</TD>
			<TD class="hilite"><A href="<?
			  if(substr(@$obj->project_url,0,7)=="http://") echo @$obj->project_url;
			  else echo "http://". @$obj->project_url;
                         ?>" target="_new"><?php echo @$obj->project_url;?></A></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Staging URL');?>:</TD>
			<TD class="hilite"><A href="<?
			  if(substr(@$obj->project_demo_url,0,7)=="http://") echo @$obj->project_demo_url;
			  else echo "http://". @$obj->project_demo_url;
                          ?>" target="_new"><?php echo @$obj->project_demo_url;?></A></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('E-Mail Documentation');?>:</TD>
			<TD class="hilite"><A href="mailto:<?echo @$obj->project_email_docs;?>"><?php echo @$obj->project_email_docs;?></A></TD>
		</TR>		
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('E-Mail Incidents');?>:</TD>
			<TD class="hilite"><A href="mailto:<?echo @$obj->project_email_support;?>"><?php echo @$obj->project_email_support;?></A></TD>
		</TR>		
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('E-Mail ToDo');?>:</TD>
			<TD class="hilite"><A href="mailto:<?echo @$obj->project_email_todo;?>"><?php echo @$obj->project_email_todo;?></A></TD>
		</TR>		
		<TR>
			<TD align="left" colspan="2" nowrap><STRONG><br/><?php echo $AppUI->_('Assessment of Satisfaction');?></STRONG></TD>
		</TR>
		<?
			include_once('./modules/public/satisfaction_suppliers_customers.php');

			$arrlevel_customer_satisfaction = getSatisfactionLevel(1, @$obj->project_id);
			if($arrlevel_customer_satisfaction)
			{
				$level_customer_satisfaction = $arrlevel_customer_satisfaction['level_satisfaction'];
				$level_customer_satisfaction_user = $arrlevel_customer_satisfaction['level_satisfaction_user'];
				$level_customer_satisfaction_date = $arrlevel_customer_satisfaction['level_satisfaction_date'];
			}

			$arrlevel_canal_satisfaction = getSatisfactionLevel(2, @$obj->project_id);			
			if($arrlevel_canal_satisfaction)
			{
				$level_canal_satisfaction = $arrlevel_canal_satisfaction['level_satisfaction'];
				$level_canal_satisfaction_user = $arrlevel_canal_satisfaction['level_satisfaction_user'];
				$level_canal_satisfaction_date = $arrlevel_canal_satisfaction['level_satisfaction_date'];
			}
		?>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Level of Customer Satisfaction');?>:</TD>
			<TD class="hilite">
				<?
					if($arrlevel_customer_satisfaction)
					{
						for($i=0;$i<$level_customer_satisfaction;$i++)
							echo '<img src="modules/reviews/images/blue.gif" alt="">';

						if ($i == 0)
							echo '----';

						if($level_customer_satisfaction_user > 0)
						{
							$arrUser = CUser::getUsersFullName(array($level_customer_satisfaction_user));
							$level_customer_satisfaction_date = new CDate($level_customer_satisfaction_date);
							echo '<br/>('.$arrUser[0]['fullname'].' '.$AppUI->_('on').' '.$level_customer_satisfaction_date->format($AppUI->getPref('SHDATEFORMAT')).')';
						}
					}
				?>
			</TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Level of Satisfaction with the Canal');?>:</TD>
			<TD class="hilite">
				<?
					if($arrlevel_canal_satisfaction)
					{
						for($i=0;$i<$level_canal_satisfaction;$i++)
							echo '<img src="modules/reviews/images/blue.gif" alt="">';

						if ($i == 0)
							echo '----';						

						if($level_canal_satisfaction_user > 0)
						{
							$arrUser = CUser::getUsersFullName(array($level_canal_satisfaction_user));
							$level_canal_satisfaction_date = new CDate($level_canal_satisfaction_date);
							echo '<br/>('.$arrUser[0]['fullname'].' '.$AppUI->_('on').' '.$level_canal_satisfaction_date->format($AppUI->getPref('SHDATEFORMAT')).')';
						}
					}
				?>
			</TD>
		</TR>
		</FORM>
		</TABLE>
	</TD>
	<?php 
}	
	?>
	<TD width="50%" rowspan="9" valign="top">
	<?php 
if ($canReadEcValues){	
    
	$sql = "SELECT SUM(task_target_budget) 
	FROM tasks 
	WHERE task_project= '$obj->project_id'
    ";
    
	$task_gs = db_loadResult($sql);

	$estimated_gs = $obj->project_other_estimated_cost + $task_gs;

	?>	
		<STRONG><?php echo $AppUI->_('Ec.Values');?></STRONG><BR />
		<TABLE cellspacing="1" cellpadding="2" border="0" width="100%">
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Contract price');?>:</TD>
			<TD class="hilite" width="100%"><?php echo $obj->project_target_budget; ?></TD>
		</TR>	
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Worked Hours');?>:</TD>
			<TD class="hilite" width="100%"><?php echo $worked_hours ?></TD>
		</TR>	
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Total Hours');?>:</TD>
			<?
			     $hs_total = number_format(total_hours($project_id), 3);
			     
			     $separado_por_puntos = explode(".", $hs_total);
			     
			     if (count($separado_por_puntos)>1)
			       {
			       	$decimal1 = substr($separado_por_puntos[1], 0,1);
		       	            $decimal2 = substr($separado_por_puntos[1], 1,1);
		       	            $decimal3 = substr($separado_por_puntos[1], 2,1);
		       	
			       	if($separado_por_puntos[1]=="000"){
			       	    $hs_total =$separado_por_puntos[0];
			       	}elseif ($decimal2=="0" && $decimal3=="0"){
			       	    $hs_total = $separado_por_puntos[0].".".$decimal1;
			       	}elseif ($decimal2!="0" && $decimal3=="0"){
			       	    $hs_total = $separado_por_puntos[0].".".$decimal1.$decimal2;
			       	}
			       }
			     
			?>
			<TD class="hilite" width="100%"><?php echo  $hs_total; ?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Estimated HHRR Budget');?>:</TD>
			<TD class="hilite"><?php echo $dPconfig['currency_symbol'] ?><?php 
		      if(actual_rrhh_estimated_cost($project_id)==0){echo "0";}else{
	          echo actual_rrhh_estimated_cost($project_id);}?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Current HHRR Budget');?>:</TD>
			<TD class="hilite"><?php echo $dPconfig['currency_symbol'] ?><?php echo actual_rrhh_real_cost ($project_id);?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Estimated Expenses G/S');?>:</TD>
			<TD class="hilite" width="100%"><?php echo $dPconfig['currency_symbol'] ?><?php 
			  if ($estimated_gs ==0){echo "0";}else{
			  echo $estimated_gs;} ?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Actual Expenses G/S');?>:</TD>
			<TD class="hilite" width="100%"><?php echo $dPconfig['currency_symbol']; ?><?php echo total_exp ($project_id); ?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Estimated Total Budget');?>:</TD>
			<TD class="hilite"><?php echo $dPconfig['currency_symbol'] ?><?php echo presupuesto_total_estimado($project_id,$estimated_gs);?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Current Total Budget');?>:</TD>
			<TD class="hilite"><?php echo $dPconfig['currency_symbol'] ?><?php echo actual_budget ($project_id);?></TD>
		</TR>
		
		<TR>
			<TD align="left" nowrap colspan="0"> </TD>
		</TR>
		<TR>
			<TD align="left" nowrap colspan="0"><STRONG><?php echo $AppUI->_('Progress');?></STRONG></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Estimated Completed Work');?>:</TD>
			<!-- <TD class="hilite" width="100%"><?php printf( "%.1f%%", $obj->project_percent_completed_work );?></TD> -->
			<TD class="hilite" width="100%"><?php printf( "%.1f%%", project_percent_completed_work($project_id));?></TD>
		</TR>
				<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Completed Duration');?>:</TD>
			<TD class="hilite"><?php echo pdc ($project_id); ?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Completed Tasks');?>:</TD>
			<TD class="hilite" width="100%"><?php echo $obj->project_percent_completed_tasks; ?></TD>
		</TR>
		<TR>
			<TD align="right" nowrap><?php echo $AppUI->_('Estimated Actual Cost');?>:</TD>
			<TD class="hilite" width="100%"><?php printf( "%.1f%%", project_percent_completed_oozed_cost($project_id,$obj->project_other_estimated_cost) ); ?></TD>
			
		</TR>
		</TABLE>
		<BR>
		&nbsp;
	<?php 
}	
	?>		

	</TD>
	</TR>
	<TR>
		<TD> </TD>
	</TR>
	<TR>
		<TD align="left" width="100%" colspan="5">
			<br>
			<STRONG><?php echo $AppUI->_('Description');?></STRONG>
			<TABLE cellspacing="1" cellpadding="2" border="0" width="100%">
				<TR>
					<TD class="hilite" width="100%" colspan="5"><?php echo str_replace( chr(10), "<br>", $obj->project_description) ; ?>&nbsp;</TD>
				</TR>
			</TABLE>
		</TD>
	</TR>
</TABLE>


<?php

if ($tab == 1) {
	$_GET['task_status'] = -1;
}
$query_string = "?m=projects&a=view&project_id=$project_id";

// tabbed information boxes
$tabBox = new CTabBox( "?m=projects&a=view&project_id=$project_id", "", $tab );
$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/tasks", 'Planning' );

// Solo pueden ver este tab los owners y pms del proyecto
if (array_key_exists($AppUI->user_id, $owners) || $AppUI->user_id == $obj->project_owner || $AppUI->user_type=='1' ) {
$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/tasks/tasks_monitoring", 'Monitoring' );
}

$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/projects/vw_forums", 'Forums' );

if (!getDenyRead( 'files' ))
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/projects/vw_files", 'Documents' );
	
if ($canEdit){
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/projects/vw_project_users", 'Users / Roles' );
}

if (!getDenyEdit( 'todo' ))
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/projects/vw_todo", 'To-do' );
	
$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/articles/vw_articles_projects", 'Knowledge');

if (!getDenyRead( 'calendar' ))
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/calendar/vw_tasks_events", 'Events');
	
if (!getDenyRead( 'contacts' ))
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/contacts/vw_project_contacts", 'Contacts');

// Me fijo si el usuario tiene permisos para ver webtracking
$canRead_webtracking = !getDenyRead( 'webtracking' );

if($canRead_webtracking)
{ 
      $tabBox->add( "{$AppUI->cfg['root_dir']}/modules/projects/vw_bugs", 'Webtracking' );
}	
	
// settings for tasks
$f = 'all';
$min_view = true;

if (!($tab >= 0 && $tab < count($tabBox->tabs))){
	$tab=0;
	$tabBox->active=0;
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
		$objProject = new CProject();
		$perms = $objProject->projectPermissions($debuguser);
	
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



function pdc ($project_id){

	$SQL0 = "SELECT UNIX_TIMESTAMP(task_end_date) AS ted FROM tasks t WHERE task_project='$project_id' AND task_complete='1' ORDER BY task_end_date DESC LIMIT 0,1";
	$task = db_fetch_array(db_exec($SQL0));

	$SQL1 = "SELECT UNIX_TIMESTAMP(project_start_date) AS psd, UNIX_TIMESTAMP(project_actual_end_date) AS ped FROM projects p WHERE project_id='$project_id'";
	$proj = db_fetch_array(db_exec($SQL1));
           
	if ($proj['psd']!=0 AND $proj['ped']!=0 AND ($proj['ped']-$proj['psd'])>0 AND ($task['ted']-$proj['psd'])>0){
		 
		// $arriba = fecha estimada de fin de última tarea terminada -  fecha de inicio de proyecto
		//$abajo = fecha de fin de proyecto - fecha de inicio de proyecto;
		
	           $arriba = $task['ted']-$proj['psd'];
	           $abajo = $proj['ped'] -$proj['psd'];
	           
	           $tmp = $arriba / $abajo;
	          
		if ($task['ted'] < $proj['ped'])
		{ 
		$pdc=($task['ted']-$proj['psd'])/($proj['ped']-$proj['psd']);
		 
		$pdc=sprintf( "%.2f%%", $pdc);
		 
		$mult = $pdc * 100;
		 
		$pdc = sprintf( "%.1f%%", $mult);
		}
		else
		{
		 $pdc=($task['ted']-$proj['psd'])/($proj['ped']-$proj['psd']);
		 $exd = $pdc + 100;
		 $pdc = "<FONT COLOR=\"#FF0000\" onmouseover=\"tooltipLink('Proyecto excedido en tiempo');\" onMouseOut =\"tooltipClose();\" >".sprintf( "%.1f%%", $exd)."</FONT>";
		}
		
		if ($task['ted'] == $proj['ped']) $pdc= "100.0%";

	}
	else {$pdc='N/A'; }

	return $pdc;

}


//esto es para que muestre el valor real de avance de project,
//porque si se usa el checkbox para marcar tareas completas,
//el project_percent_complete no refleja el valor real hasta
//que se recarga la pagina. Inmundo, pero funciona.
if(isset($_POST['task_complete'])) 
	$AppUI->redirect("");

?>
