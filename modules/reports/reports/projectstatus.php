<?php
$do_report 		    = dPgetParam( $_POST, "do_report", 0 );
$log_start_date 	= dPgetParam( $_POST, "log_start_date", 0 );
$log_end_date 	    = dPgetParam( $_POST, "log_end_date", 0 );
$log_pdf 			= dPgetParam( $_POST, 'log_pdf', 0 ); 
$group_by_unit      = dPgetParam($_POST,"group_by_unit","day");
$project_id			= dPgetParam($_POST,"project_id", "");
$log_tasks			= dPgetParam($_POST,"log_tasks", 0);
$log_forums			= dPgetParam($_POST,"log_forums", 0);
$log_files			= dPgetParam($_POST,"log_files", 0);
$log_bugs 			= dPgetParam($_POST,"log_bugs", 0);
$log_people			= dPgetParam($_POST,"log_people", 0);

//Cargo los estados de los proyectos
$pstatus = dPgetSysVal( 'ProjectStatus' );

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : new CDate();
$end_date = intval( $log_end_date ) ? new CDate( $log_end_date ) : new CDate();

if (!$log_start_date) {
	$start_date->subtractSpan( new Date_Span( "14,0,0,0" ) );
}
$end_date->setTime( 23, 59, 59 );

//Load viewable projects
//include("modules/projects/read_projects.inc.php");
?>

<script language="javascript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&suppressLogo=1&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.log_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function canal_project(company, canal, project )
{
    xajax_addCanal('canal_id', company ,canal,'TRUE','','');
    xajax_addProjects(company, canal, project, 'FALSE', '', '', 'project_id' );
}

function submitIt()
{
	if (document.editFrm.project_id.value !="")
	{
		document.editFrm.submit();
	}else{
		alert1("<?php echo $AppUI->_('Please select a project');?>");
	}
}

</script>

<form name="editFrm" action="index.php?m=reports" method="post">
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
	<td align="left" nowrap="nowrap"><?php echo $AppUI->_('Company');?>:&nbsp;&nbsp;
		<?
			$sql = "SELECT company_id, company_name FROM companies order by company_name;";
			$companies = db_loadHashList( $sql );
			echo arraySelect($companies, "company_id", "size='1' class='text' onchange=\"canal_project(document.editFrm.company_id.value, '','' );\" ", $company_id,'','','210px');
		?>
	</td>
	<td nowrap="nowrap">
		<input type="checkbox" name="log_bugs" <?php if ($log_bugs) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log bugs' );?>
	</td>
	<td nowrap="nowrap">
		<input type="checkbox" name="log_people" <?php if ($log_people) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log people with lacking hours' );?>		
	</td>
	<td nowrap="nowrap">
		<input type="checkbox" name="log_files" <?php if ($log_files) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log new files (30 days)' );?>		
	</td>	
</tr>
<tr>	
	<td nowrap="nowrap"><?php echo $AppUI->_('Canal');?>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<select name="canal_id" id="canal_id" size="1" class="text" style="width: 210px;" onchange="canal_project(document.editFrm.company_id.value, document.editFrm.canal_id.value, '' )" ></select>	
			
		<script type="text/javascript">
		     canal_project(document.editFrm.company_id.value, '<?=$canal_id?>', '<?=$project_id?>' );
		</script>
	</td>
	<td nowrap="nowrap">
		<input type="checkbox" name="log_tasks" <?php if ($log_tasks) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log uncompleted tasks' );?>
	</td>
	<td nowrap="nowrap">
		<input type="checkbox" name="log_forums" <?php if ($log_forums) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log new forum topics (30 days)' );?>		
	</td>
	<td nowrap="nowrap">
		<input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo "checked" ?> />
		<?php echo $AppUI->_( 'Make PDF' );?>
	</td>

</tr>
<tr>
  <td nowrap="nowrap"><?php echo $AppUI->_('Project');?>:&nbsp;&nbsp;
		<select name="project_id" id="project_id" size="1" class="text" style="width: 210px;"></select>		
	</td>
	<td nowrap="nowrap">&nbsp;
	</td>
	<td nowrap="nowrap">&nbsp;
	</td>
	<td nowrap="nowrap">&nbsp;
	</td>
</tr>
<tr>
	<td align="right" colspan="4" nowrap="nowrap">
	    <input type="hidden" name="do_report" value="true">
		<input class="button" type="button" name="do_report" value="<?php echo $AppUI->_('submit');?>" onclick="submitIt()"/>
	</td>
</tr>

</table>
</form>

<?php
if($do_report){
	$sql = "
SELECT
	company_name,
	CONCAT_WS(' ',user_first_name,user_last_name) user_name,
	projects.*,
	SUM(t1.task_duration*t1.task_duration_type*t1.task_manual_percent_complete)/SUM(t1.task_duration*t1.task_duration_type) AS project_percent_complete
FROM projects
LEFT JOIN companies ON company_id = project_company
LEFT JOIN users ON user_id = project_owner
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
WHERE project_id = $project_id
GROUP BY project_id
";
	$obj = null;
	db_loadObject( $sql, $obj );
	
// worked hours
// by definition milestones don't have duration so even if they specified, they shouldn't add up
// the sums have to be rounded to prevent the sum form having many (unwanted) decimals because of the mysql floating point issue
// more info on http://www.mysql.com/doc/en/Problems_with_float.html

	$sql = "SELECT ROUND(SUM(t.timexp_value),2)
	
			FROM timexp AS t inner join tasks ta on task_id = timexp_applied_to_id 
										and timexp_applied_to_type = 1
										and timexp_type = 1
			WHERE 
				 task_project = $project_id 
				 AND task_milestone ='0'";	
				
$worked_hours = db_loadResult($sql);
$worked_hours = rtrim($worked_hours, "0");

// total hours
// same milestone comment as above, also applies to dynamic tasks
$sql = "SELECT ROUND(SUM(task_duration),2) FROM tasks WHERE task_project = $project_id AND task_duration_type = 24 AND task_milestone ='0' AND task_dynamic = 0";
$days = db_loadResult($sql);
$sql = "SELECT ROUND(SUM(task_duration),2) FROM tasks WHERE task_project = $project_id AND task_duration_type = 1 AND task_milestone  ='0' AND task_dynamic = 0";
$hours = db_loadResult($sql);
$total_hours = $days * $dPconfig['daily_working_hours'] + $hours;
//due to the round above, we don't want to print decimals unless they really exist
$total_hours = rtrim($total_hours, "0");

	$sql = "SELECT ROUND(SUM(t.timexp_value),2) as task_total_expenses
	
			FROM timexp AS t inner join tasks ta on task_id = timexp_applied_to_id 
										and timexp_applied_to_type = 1
										and timexp_type = 2
			WHERE 
				 task_project = $project_id 
				 AND task_milestone ='0'";	
$total_expenses = db_loadList($sql);

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

// create Date objects from the datetime fields
$start_date = intval( $obj->project_start_date ) ? new CDate( $obj->project_start_date ) : null;
$end_date = intval( $obj->project_end_date ) ? new CDate( $obj->project_end_date ) : null;
$actual_end_date = intval( $obj->project_actual_end_date ) ? new CDate( $obj->project_actual_end_date ) : null;

//Para el pdf
$pdfdata = array();
	?>
<table cellspacing="1" cellpadding="4" border="0">
	<tr>		
		<td><?php echo $AppUI->_('Company'); ?></td>
		<td><?php echo htmlspecialchars( $obj->company_name, ENT_QUOTES);?></td>
		<?
		$pdfdata[][] = $AppUI->_('Company');
		$pdfdata[count($pdfdata) - 1][] = $obj->company_name;
		?>
	</tr>
	<tr>	
		<td><?php echo $AppUI->_('Short Name'); ?></td>
		<td><?php echo htmlspecialchars( @$obj->project_short_name, ENT_QUOTES) ;?></td>
		<?
		$pdfdata[][] = $AppUI->_('Short Name');
		$pdfdata[count($pdfdata) - 1][] = @$obj->project_short_name;
		?>
	</tr>
	<tr>	
		<td><?php echo $AppUI->_('Start Date'); ?></td>
		<td><?php echo $start_date ? $start_date->format( $df ) : '-';?></td>
		<?
		$pdfdata[][] = $AppUI->_('Start Date');
		$pdfdata[count($pdfdata) - 1][] = $start_date ? $start_date->format( $df ) : '-';
		?>
	</tr>
	<tr>		
		<td><?php echo $AppUI->_('Target End Date'); ?></td>
		<td><?php echo $end_date ? $end_date->format( $df ) : '-';?></td>
		<?
		$pdfdata[][] = $AppUI->_('Target End Date');
		$pdfdata[count($pdfdata) - 1][] = $end_date ? $end_date->format( $df ) : '-';
		?>
	</tr>
	<tr>	
		<td><?php echo $AppUI->_('Actual End Date'); ?></td>
		<td><?php echo $actual_end_date ? $actual_end_date->format( $df ) : '-';?></td>
		<?
		$pdfdata[][] = $AppUI->_('Actual End Date');
		$pdfdata[count($pdfdata) - 1][] = $actual_end_date ? $actual_end_date->format( $df ) : '-';
		?>
	</tr>
	<tr>	
		<td><?php echo $AppUI->_('Target Budget'); ?></td>
		<td><?php echo $dPconfig['currency_symbol'].@$obj->project_target_budget;?></td>
		<?
		$pdfdata[][] = $AppUI->_('Target Budget');
		$pdfdata[count($pdfdata) - 1][] = $dPconfig['currency_symbol'].@$obj->project_target_budget;
		?>
	</tr>
	<tr>	
		<td><?php echo $AppUI->_('Project Owner'); ?></td>
		<td><?php echo htmlspecialchars( $obj->user_name, ENT_QUOTES) ; ?></td>
		<?
		$pdfdata[][] = $AppUI->_('Project Owner');
		$pdfdata[count($pdfdata) - 1][] = $obj->user_name;
		?>		
	</tr>
	<tr>
		<? $href = (substr(@$obj->project_url,0,7)=="http://") ? @$obj->project_url : "http://". @$obj->project_url; ?>   	
		<td><?php echo $AppUI->_('URL'); ?></td>
		<td><a href="<?= $href ?>" target="_new"><?php echo @$obj->project_url;?></A></td>
		<?
		$pdfdata[][] = $AppUI->_('URL');
		$pdfdata[count($pdfdata) - 1][] = @$obj->project_url;
		?>
	</tr>
	<tr>	
		<td><?php echo $AppUI->_('Staging URL'); ?></td>
		<td><a href="<? if(substr(@$obj->project_demo_url,0,7)=="http://") echo @$obj->project_demo_url; 
			  else echo "http://". @$obj->project_demo_url;
                          ?>" target="_new"><?php echo @$obj->project_demo_url;?></a></td>
		<?
		$pdfdata[][] = $AppUI->_('Staging URL');
		$pdfdata[count($pdfdata) - 1][] = @$obj->project_demo_url;
		?>
	</tr>
	<tr>	
		<td><?php echo $AppUI->_('Status'); ?></td>
		<td><?php echo $AppUI->_($pstatus[$obj->project_status]);?></td>
		<?
		$pdfdata[][] = $AppUI->_('Status');
		$pdfdata[count($pdfdata) - 1][] = $AppUI->_($pstatus[$obj->project_status]);
		?>
	</tr>
	<tr>	
		<td><?php echo $AppUI->_('Progress'); ?></td>
		<td><?php printf( "%.1f%%", $obj->project_percent_complete );?></td>
		<?
		$pdfdata[][] = $AppUI->_('Progress');
		$pdfdata[count($pdfdata) - 1][] = sprintf( "%.1f%%", $obj->project_percent_complete );
		?>
	</tr>
	<tr>	
		<td><?php echo $AppUI->_('Active'); ?></td>
		<td><?php echo $obj->project_active ? $AppUI->_('Yes') : $AppUI->_('No');?></td>
		<?
		$pdfdata[][] = $AppUI->_('Active');
		$pdfdata[count($pdfdata) - 1][] = $obj->project_active ? $AppUI->_('Yes') : $AppUI->_('No');
		?>
	</tr>
	<tr>	
		<td><?php echo $AppUI->_('Worked Hours'); ?></td>
		<td><?php echo $worked_hours ?></td>
		<?
		$pdfdata[][] = $AppUI->_('Worked Hours');
		$pdfdata[count($pdfdata) - 1][] = $worked_hours;
		?>
	</tr>
	<tr>	
		<td><?php echo $AppUI->_('Total Hours'); ?></td>
		<td><?php echo $total_hours ?></td>
		<?
		$pdfdata[][] = $AppUI->_('Total Hours');
		$pdfdata[count($pdfdata) - 1][] = $total_hours;
		?>
	</tr>
	<tr>	
		<td><?php echo $AppUI->_('Expenses'); ?></td>
		<td><?php echo $total_expenses[0]["task_total_expenses"] ?></td>
		<?
		$pdfdata[][] = $AppUI->_('Expenses');
		$pdfdata[count($pdfdata) - 1][] = $total_expenses[0]["task_total_expenses"];
		?>
	</tr>
	<tr>	
		<td><?php echo $AppUI->_('Description'); ?></td>
		<td><?php echo str_replace( chr(10), "<br>", $obj->project_description) ; ?>&nbsp;</td>
		<?
		$pdfdata[][] = $AppUI->_('Description');
		$pdfdata[count($pdfdata) - 1][] = $obj->project_description;
		?>		                  
	</tr>
	</table>
	<?php
	if ( $log_tasks )
	{
		//Mostrar las tareas asociadas
		
		//require_once( $AppUI->getModuleClass("tasks") );
		//Estaria bueno usar la clase CTask		
		
		$select = "
		tasks.task_id, task_parent, task_name, task_start_date, task_end_date,
		task_priority, task_manual_percent_complete, task_duration, task_duration_type, task_project,
		task_description, task_owner, user_username, task_milestone
		";

		$from = "tasks";
		$join = "LEFT JOIN projects ON project_id = task_project";
		$join .= " LEFT JOIN users as usernames ON task_owner = usernames.user_id";
		$where = "task_project = $project_id
		AND task_manual_percent_complete < 100
		AND task_complete = '0'
		AND task_status != '-1'
		AND NOW() > task_end_date
		";
		
		$sql = "select ".$select." from ".$from." ".$join." where ".$where;		
		$tareas = db_loadHashList ( $sql, "task_id");
		?>
		<p><?php echo $AppUI->_("Tasks")?></p>
		<?
		//Preparo la tabla de tareas para pdf
		$pdf_tasks = array();
		?>
		<table cellspacing="1" cellpadding="4" border="0" class="tbl">
			<tr>
				<th><?php echo $AppUI->_("Task Name"); ?></th>
				<th><?php echo $AppUI->_("Task Owner"); ?></th>
				<th><?php echo $AppUI->_("Task Start Date"); ?></th>
				<th><?php echo $AppUI->_("Task Duration"); ?></th>
				<th><?php echo $AppUI->_("Task End Date"); ?></th>				
			</tr>
		<?php
		$pdf_tasks[] = array( $AppUI->_("Task Name"),
			$AppUI->_("Task Owner"),
			$AppUI->_("Task Start Date"),
			$AppUI->_("Task Duration"),
			$AppUI->_("Task End Date")
			);
		
		foreach ($tareas as $tarea)
		{			
			$start_date = intval( $tarea["task_start_date"] ) ? new CDate( $tarea["task_start_date"] ) : null;
			$end_date = intval( $tarea["task_end_date"] ) ? new CDate( $tarea["task_end_date"] ) : null;
			?>			
			<tr>
				<td><?php echo $tarea["task_name"]?></td>
				<td><?php echo $tarea["task_owner"]?></td>
				<td><?php echo ( $start_date != null ? $start_date->format($df) : "-" ) ?></td>
				<td><?php echo $tarea["task_duration"]?></td>
				<td><?php echo ( $end_date != null ? $end_date->format($df) : "-" ) ?></td>								
			</tr>
			<?php
			$pdf_tasks[] = array( $tarea["task_name"],
				$tarea["user_username"],
				( $start_date != null ? $start_date->format($df) : "-" ),
				$tarea["task_duration"],
				( $end_date != null ? $end_date->format($df) : "-" )
			);			
		}	
		?>
		</table>
		<?php		
	}
	if ( $log_forums )
	{
		//Mostrar los foros
		
		$sql = "SELECT f.forum_id, f.forum_name, t.message_date, t.message_title 
		FROM forums f 
		INNER JOIN forum_messages t 
		ON t.message_forum = f.forum_id AND t.message_parent = -1
		WHERE (forum_project = $project_id) 
		AND (t.message_date >= DATE_ADD( CURDATE(), INTERVAL -30 DAY ) )
		ORDER BY forum_name";
				
		//echo "<pre>$sql</pre>";		
		$forums = db_loadHashList ( $sql, "forum_id");
		
		//Inicio el array para los pdf
		$pdf_forums = array();
		$pdf_forums[] = array( $AppUI->_("Forum Name"),
			$AppUI->_("Topic"),
			$AppUI->_("Post date")
			);
		?>
		<p><?php echo $AppUI->_("Forums")?></p>
		<table cellspacing="1" cellpadding="4" border="0" class="tbl">
			<tr>
				<th><?php echo $AppUI->_("Forum Name"); ?></th>
				<th><?php echo $AppUI->_("Topic"); ?></th>
				<th><?php echo $AppUI->_("Post date"); ?></th>								
			</tr>
		<?php
		foreach ($forums as $forum)
		{
			$lp = intval( $forum["message_date"] ) ? new CDate( $forum["message_date"] ) : null;			
			?>			
			<tr>
				<td><?php echo $forum["forum_name"] ?></td>
				<td><?php echo $forum["message_title"] ?></td>
				<td><?php echo ( $lp != null ? $lp->format($df) : "-" ) ?></td>
			</tr>
			<?php
			$pdf_forums[] = array( $forum["forum_name"],
				$forum["message_title"],
				( $lp != null ? $lp->format($df) : "-" )
			);
		}	
		?>
		</table>
		<?php		
	}
	if ( $log_files )
	{
		//Mostrar los archivos
		$sql = "SELECT f.* 
		FROM files f 
		WHERE (file_project = $project_id)
		AND (f.file_date >= DATE_ADD( CURDATE(), INTERVAL -30 DAY ))
		";
		
		$fs = db_loadHashList( $sql, "file_id" );
		
		//Para el pdf
		
		$pdf_files = array();
		$pdf_files[] = array( $AppUI->_("File Name"),
			$AppUI->_("Version"),
			$AppUI->_("Owner"),
			$AppUI->_("Size"),
			$AppUI->_("Type"),
			$AppUI->_("Date")
		);
		?>		
		<p><?php echo $AppUI->_("Files")?></p>
		<table cellspacing="1" cellpadding="4" border="0" class="tbl">
			<tr>
				<th><?php echo $AppUI->_("File Name"); ?></th>
				<th><?php echo $AppUI->_("Version"); ?></th>
				<th><?php echo $AppUI->_("Owner"); ?></th>								
				<th><?php echo $AppUI->_("Size"); ?></th>
				<th><?php echo $AppUI->_("Type"); ?></th>
				<th><?php echo $AppUI->_("Date"); ?></th>
			</tr>
		<?php
		foreach ($fs as $f)
		{
			?>
			<tr>
				<td><?php echo $f["file_name"] ?></td>
				<td><?php echo $f["file_version"] ?></td>
				<td><?php echo $f["file_owner"] ?></td>
				<td><?php echo $f["file_size"] ?></td>
				<td><?php echo $f["file_type"] ?></td>
				<td><?php echo $f["file_date"] ?></td>
			</tr>
			<?php
			$pdf_files[] = array( $f["file_name"],
				$f["file_version"],
				$f["file_owner"],
				$f["file_size"],
				$f["file_type"],
				$f["file_date"]
			);
		}	
		?>
		</table>
		<?php
	}
	if ( $log_bugs )
	{
		//Mostrar las incidencias
		?>
		<p><?php echo $AppUI->_("Bugs")?></p>
		<?
		$sql = "SELECT COUNT(b.id) as totales  
		  FROM 
			btpsa_bug_table b 
		 	WHERE b.project_id = $project_id
		 	";
		 //echo "<pre>$sql</pre>";
		$res = db_loadResult( $sql );		
		//$totales = $res["totales"]; Con esta linea solo muestra el primer caracter del total
		$totales = $res;

		
		
		$sql = "SELECT COUNT(b.id) as abiertas  
		  FROM 
			btpsa_bug_table b 
		 	WHERE b.project_id = $project_id
		 	AND b.status != 90 
		 	";
		$res = db_loadResult( $sql );		
		//$abiertas = $res["abiertas"];
		$abiertas = $res;
				
		$sql = "SELECT COUNT(b.id) as cerradas  
		  FROM 
			btpsa_bug_table b 
		 	WHERE b.project_id = $project_id
		 	AND b.status = 90 
		 	";
		$res = db_loadResult( $sql );
		//$cerradas = $res["cerradas"];
		$cerradas = $res;
		
		//Para el pdf		
		
		$pdf_bugs = array();
		$pdf_bugs[] = array(
			$AppUI->_("Total"),
			$AppUI->_("Open"),
			$AppUI->_("Closed")
		);		
		?>		
		<table cellspacing="1" cellpadding="4" border="0" class="tbl">
			<tr>
				<th><?php echo $AppUI->_("Total"); ?></th>
				<th><?php echo $AppUI->_("Open"); ?></th>
				<th><?php echo $AppUI->_("Closed"); ?></th>												
			</tr>
			<tr>
				<td><?= $totales ?></td>
				<td><?= $abiertas ?></td>
				<td><?= $cerradas ?></td>				
			</tr>
		</table>
		<p><?= $AppUI->_("Open bugs")?></p>		
		<?php
		
		$pdf_bugs[] = array(
				$totales,
				$abiertas,
				$cerradas						
			);
		
		$sql = "SELECT b.* 
		FROM btpsa_bug_table b
		WHERE b.project_id = $project_id
		AND b.status != 90
		ORDER BY b.priority
		";
		
		$bs = db_loadHashList( $sql, "id");
		 
		$pdf_open_bugs[] = array( 			
			$AppUI->_("Priority"),
			$AppUI->_("Category"),
			$AppUI->_("Status"),
			$AppUI->_("Updated"),
			$AppUI->_("Summary")			
		);
		
		$severities = array(
			10 => "functionality",
			20 => "trivial",
			30 => "text",
			40 => "minimum change",
			50 => "minor",
			60 => "major",
			70 => "sudden interruption",
			80 => "block"
		);
		
		$status = array (
			10 => "new",
			20 => "more data needed",
			30 => "accepted",
			40 => "confirmed", 
			50 => "assigned",
			80 => "solved",
			90 => "closed"
		);
		
		$priorities = array (
			10 => "none",
			20 => "low", 
			30 => "normal",
			40 => "high",
			50 => "urgent",
			60 => "immediate"
		);
		?>				
		<table cellspacing="1" cellpadding="4" border="0" class="tbl">
			<tr>
				<th><?php echo $AppUI->_("Priority"); ?></th>
				<th><?php echo $AppUI->_("Severity"); ?></th>
				<th><?php echo $AppUI->_("Status"); ?></th>
				<th><?php echo $AppUI->_("Updated"); ?></th>
				<th><?php echo $AppUI->_("Summary"); ?></th>								
			</tr>
		<?php
		foreach ($bs as $b)
		{
			$d = new CDate($b["last_updated"]);			
			?>
			<tr>
				<td><?php echo $AppUI->_($priorities[$b["priority"]]) ?></td>
				<td><?php echo $AppUI->_($severities[$b["severity"]]) ?></td>
				<td><?php echo $AppUI->_( $status[$b["status"]] ) ?></td>
				<td><?php echo $d->format("%D-%M-%A") ?></td>
				<td><?php echo $b["summary"] ?></td>								
			</tr>
			<?php
			$pdf_open_bugs[] = array(
				$priorities[$b["priority"]],
				$severities[$b["severity"]],
				$status[$b["status"]],
				$d->format("%D-%M-%A"),
				$b["summary"]					
			);
		}	
		?>
		</table>
		<?php
	}
	if ( $log_people )
	{ 
		$pdf_people = array();
		$pdf_people[] = array(
			$AppUI->_('Real Name'),
			$AppUI->_('Pending Records'),
			$AppUI->_('Max. Delay')
		);
		?>
	<table cellspacing="1" cellpadding="4" border="0" class="tbl">
	<tr>
		<th width="150">
			<?php echo $AppUI->_('Real Name');?>
		</th>
		<th>
			<?php echo $AppUI->_('Pending Records');?>
		</th>
		<th>
			<?php echo $AppUI->_('Max. Delay');?>
		</th>
	</tr>
<?
  $today = new CDate();
  $resultut = mysql_query("SELECT * FROM users WHERE user_type <> 5  ORDER BY user_last_name, user_first_name ;");
  while ($rowut = mysql_fetch_array($resultut, MYSQL_ASSOC)) 
  {
    $resultut2 = mysql_query("SELECT user_id, tasks.task_id, DATE_FORMAT(task_start_date,'%m/%d/%Y') as date FROM user_tasks, tasks WHERE user_tasks.user_id = {$rowut["user_id"]} AND tasks.task_id = user_tasks.task_id AND tasks.task_status=0 AND tasks.task_complete = 0 AND tasks.task_manual_percent_complete <> 100 AND tasks.task_start_date < CURDATE() ;");
    $tottasks=0;
    $maxdelay=0;
    while ($rowut2 = mysql_fetch_array($resultut2, MYSQL_ASSOC)) 
    {
    	$sql = "
				SELECT timexp_date, DATE_FORMAT(timexp_date,'%m/%d/%Y') as date 
				FROM timexp 
				WHERE timexp_applied_to_type = 1
				AND timexp_applied_to_id = {$rowut2["task_id"]} 
				AND timexp_type = 1
				AND timexp_creator = {$rowut2["user_id"]} 
				ORDER BY timexp_date  DESC ;";
      $resultut3 = mysql_query($sql);
      if(mysql_num_rows($resultut3)>0){
      	
        $rowut3 = mysql_fetch_array($resultut3, MYSQL_ASSOC);
        //$cal = new CWorkCalendar(3, $rowut2["user_id"], $rowut2["task_project"], $rowut3["timexp_date"] );
		$cal = new CDate($rowut3["timexp_date"]);

        $curdelay = abs($cal->dateDiff($today));
        if($curdelay > 0){  // =0 significa ayer
          if($maxdelay < $curdelay) $maxdelay = $curdelay;
          $tottasks++;
        }        
      }
      else{
        //$cal = new CWorkCalendar(3, $rowut2["user_id"], $rowut2["task_project"], $rowut2["task_start_date"]);
        $cal = new CDate($rowut2["task_start_date"]);
        $curdelay = abs($cal->dateDiff($today));

        if($curdelay > 0){  // =0 significa ayer
          if($maxdelay < $curdelay) $maxdelay = $curdelay;
          $tottasks++;
        }
      }
    }    	
    if($tottasks!=0)
    { 
    	$pdf_people[] = array (
    		$rowut["user_last_name"]." ".$rowut["user_first_name"],
    		$tottasks,
    		$maxdelay." ".$AppUI->_('days')
    	);
    	?>
	<tr>
		<td>			
			<?php echo $rowut["user_last_name"].", ".$rowut["user_first_name"];?>
		</td>
		<td>
			<?php echo $tottasks;?>
		</td>
		<td>
			<?php echo $maxdelay;?>  <?php echo $AppUI->_('days');?>
		</td>
	</tr>
<?
  	}
  }
?>
</table>
<?
	}
	if ($log_pdf) 
	{
	// make the PDF file
		$sql = "SELECT project_name FROM projects WHERE project_id=$project_id";
		$pname = db_loadResult( $sql );
		echo db_error();

		$font_dir = $AppUI->getConfig( 'root_dir' )."/lib/ezpdf/fonts";
		$temp_dir = $AppUI->getConfig( 'root_dir' )."/files/temp";
		$base_url  = $AppUI->getConfig( 'base_url' );
		require( $AppUI->getLibraryClass( 'ezpdf/class.ezpdf' ) );

		$pdf =& new Cezpdf();
		$pdf->ezSetCmMargins( 1, 2, 1.5, 1.5 );
		$pdf->selectFont( "$font_dir/Helvetica.afm" );

		$pdf->ezText( $AppUI->getConfig( 'company_name' ), 12 );
		
		$date = new CDate();
		$pdf->ezText( "\n" . $date->format( $df ) , 8 );

		$pdf->selectFont( "$font_dir/Helvetica-Bold.afm" );
		$pdf->ezText( "\n" . $AppUI->_('General Information Report'), 12 );
		$pdf->ezText( "$pname", 15 );
		
		$pdf->ezText( "\n\n" );

		$columns = null;
		$title = null;
		$options = array(
			'showLines' => 0,
			'showHeadings' => 0,
			'fontSize' => 8,
			'rowGap' => 2,
			'colGap' => 5,
			'xPos' => 50,
			'xOrientation' => 'right',
			'width'=>'500',
			'shaded'=>0
		);

		$pdf->ezTable( $pdfdata, $columns, $title, $options );

		if ( $log_tasks )
		{
			$options = array(
			'showLines' => 1,
			'showHeadings' => 0,
			'fontSize' => 8,
			'rowGap' => 2,
			'colGap' => 5,
			'xPos' => 50,
			'xOrientation' => 'right',
			'width'=>'500'
			);			
			$pdf->ezTable( $pdf_tasks, null, $AppUI->_("Tasks"), $options );
		}
		
		if ( $log_forums )
		{
			$options = array(
			'showLines' => 1,
			'showHeadings' => 0,
			'fontSize' => 8,
			'rowGap' => 2,
			'colGap' => 5,
			'xPos' => 50,
			'xOrientation' => 'right',
			'width'=>'500'
			);			
			$pdf->ezTable( $pdf_forums, null, $AppUI->_("Forums"), $options );
		}
		
		if ( $log_files )
		{
			$options = array(
			'showLines' => 1,
			'showHeadings' => 0,
			'fontSize' => 8,
			'rowGap' => 2,
			'colGap' => 5,
			'xPos' => 50,
			'xOrientation' => 'right',
			'width'=>'500'
			);			
			$pdf->ezTable( $pdf_files, null, $AppUI->_("Files"), $options );
		}		
		
		if ( $log_bugs )
		{
			$options = array(
			'showLines' => 1,
			'showHeadings' => 0,
			'fontSize' => 8,
			'rowGap' => 2,
			'colGap' => 5,
			'xPos' => 50,
			'xOrientation' => 'right',
			'width'=>'500'
			);			
			$pdf->ezTable( $pdf_bugs, null, $AppUI->_("Bugs"), $options );
			$pdf->ezTable( $pdf_open_bugs, null, $AppUI->_("Open bugs"), $options );			
		}
		
		if ( $log_people )
		{
			$options = array(
			'showLines' => 1,
			'showHeadings' => 0,
			'fontSize' => 8,
			'rowGap' => 2,
			'colGap' => 5,
			'xPos' => 50,
			'xOrientation' => 'right',
			'width'=>'500'
			);			
			$pdf->ezTable( $pdf_people, null, $AppUI->_("People with lacking hours"), $options );
		}
		
		if ($fp = fopen( "$temp_dir/temp$AppUI->user_id.pdf", 'wb' )) {
			fwrite( $fp, $pdf->ezOutput() );
			fclose( $fp );
			echo "<br><center><a href=\"$base_url/files/temp/temp$AppUI->user_id.pdf\" target=\"pdf\">";
			echo $AppUI->_( "View PDF File" );
			echo "</a></center>";
		} else {
			echo "Could not open file to save PDF.  ";
			if (!is_writable( $temp_dir )) {
				"The files/temp directory is not writable.  Check your file system permissions.";
			}
		}
	}	
}

function count_workdays($date1,$date2)
{
	$firstdate = strtotime($date1);
	$lastdate = strtotime($date2);
	$firstday = date("w",$firstdate);
	$lastday = date("w",$lastdate);
	$totaldays = intval(($lastdate-$firstdate)/86400)+1;
	
	//check for one week only
	if ($totaldays<=7 && $firstday<=$lastday){
	     $workdays = $lastday-$firstday+1;
	     //check for weekend
	     if ($firstday==0){
	             $workdays = $workdays-1;
	            }
	     if ($lastday==6){
	             $workdays = $workdays-1;
	            }
	     
	     }else { //more than one week
	
	             //workdays of first week
	            if ($firstday==0){
	                 //so we don't count weekend
	                 $firstweek = 5; 
	                 }else {
	                 $firstweek = 6-$firstday;
	                 }
	            $totalfw = 7-$firstday;
	
	            //workdays of last week
	            if ($lastday==6){
	                 //so we don't count sat, sun=0 so it won't be counted anyway
	                 $lastweek = 5;
	                 }else {
	                 $lastweek = $lastday;
	                 }
	            $totallw = $lastday+1;
	                 
	            //check for any mid-weeks 
	            if (($totalfw+$totallw)>=$totaldays){
	                 $midweeks = 0;
	                 } else { //count midweeks
	                 $midweeks = (($totaldays-$totalfw-$totallw)/7)*5;
	                 }
	
	            //total num of workdays
	            $workdays = $firstweek+$midweeks+$lastweek;
	
	        }
	
	/*
	check for and subtract and holidays etc. here
	...
	*/
	
	return ($workdays);
}
?>