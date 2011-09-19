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

//Cargo los estados de los proyectos
$pstatus = dPgetSysVal( 'ProjectStatus' );

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : new CDate();
$end_date = intval( $log_end_date ) ? new CDate( $log_end_date ) : new CDate();

if (!$log_start_date) {
	$start_date->subtractSpan( new Date_Span( "14,0,0,0" ) );
}
$end_date->setTime( 23, 59, 59 );
?>

<script language="javascript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
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
	<td nowrap="nowrap" width="1%">
		<?php echo $AppUI->_('Company');?>:&nbsp;&nbsp;
		<?
		   $obj = new CCompany();
           $companies = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );

		   echo arraySelect($companies, "company_id", "size='1' class='text' onchange=\"canal_project(document.editFrm.company_id.value, '', '' )\"", $company_id,'','true','220px');
		?>
	</td>
	<td nowrap="nowrap"  width="1%">
		<input type="checkbox" name="log_tasks" <?php if ($log_tasks) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log tasks' );?>
	</td>
	<td nowrap="nowrap" width="1%">
		<input type="checkbox" name="log_files" <?php if ($log_files) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log files' );?>
	</td>
	<td nowrap="nowrap" width="1%">
		<input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo "checked" ?> />
		<?php echo $AppUI->_( 'Make PDF' );?>
	</td>		
</tr>

      <script type="text/javascript">
          canal_project(document.editFrm.company_id.value, '<?=$canal_id?>', '<?=$project_id?>' );			
      </script>
		
<tr>
	<td nowrap="nowrap" width="1%">	
		<?php echo $AppUI->_('Canal');?>:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?
		//Con esto primego genero el select y llamo a la funcion de ajax addProjects para que lo complete
		?>
		<select name="canal_id" id="canal_id" class="text" style="font-size:10px; width: 220px " onchange="canal_project(document.editFrm.company_id.value, document.editFrm.canal_id.value, '' )" >
        </select>
   
	</td>
	<td nowrap="nowrap" width="1%">
		<input type="checkbox" name="log_forums" <?php if ($log_forums) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log forums' );?>
	</td>
	<td nowrap="nowrap" width="1%">
		<input type="checkbox" name="log_bugs" <?php if ($log_bugs) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log bugs' );?>
	</td>	
</tr>

<tr>
	<td nowrap="nowrap" width="1%">	
		<?php echo $AppUI->_('Project');?>:&nbsp;&nbsp;
		<?
		//Con esto primego genero el select y llamo a la funcion de ajax addProjects para que lo complete
		?>
		<select name="project_id" id="project_id" size="1" class="text" style="width: 220px;"></select>		
		
	</td>
	<td nowrap="nowrap" colspan="3">
		&nbsp;
	</td>
	
</tr>


<tr>
	<td align="right" width="50%" nowrap="nowrap" colspan="4">
	    <input type="hidden" name="do_report" value="true">
		<input class="button" type="button" name="do_report" value="<?php echo $AppUI->_('submit');?>" onclick="submitIt()" />
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
/*$sql = "
SELECT ROUND(SUM(task_log_hours),2) 
FROM task_log, tasks 
WHERE task_log_task = task_id 
AND task_project = $project_id 
AND task_milestone ='0'";*/
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

//$sql = "SELECT 	SUM(task_expense_cost) as task_total_expenses FROM tasks, task_expense WHERE task_expense_task = task_id AND task_project = $project_id";
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
		$where = "task_project = $project_id";
		
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
				<td><?php echo $tarea["user_username"]?></td>
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
				
		//require_once( $AppUI->getModuleClass("forums") );
		//Estaria bueno usar la clase CForum
		
		$sql = "SELECT f.*, COUNT(distinct t.message_id) forum_topics, MAX(t.message_date) forum_last_publication
		FROM forums f LEFT JOIN forum_messages t ON t.message_forum = f.forum_id AND t.message_parent = -1
		WHERE forum_project = $project_id"; 
		$sql .= "\nGROUP BY forum_id\nORDER BY forum_name";
				
		$forums = db_loadHashList ( $sql, "forum_id");
		
		//Inicio el array para los pdf
		$pdf_forums = array();
		$pdf_forums[] = array( $AppUI->_("Forum Name"),
			$AppUI->_("Messages"),
			$AppUI->_("Last Publication")
			);
		?>
		<p><?php echo $AppUI->_("Forums")?></p>
		<table cellspacing="1" cellpadding="4" border="0" class="tbl">
			<tr>
				<th><?php echo $AppUI->_("Forum Name"); ?></th>
				<th><?php echo $AppUI->_("Messages"); ?></th>
				<th><?php echo $AppUI->_("Last Publication"); ?></th>								
			</tr>
		<?php
		foreach ($forums as $forum)
		{
			$lp = intval( $forum["forum_last_publication"] ) ? new CDate( $forum["forum_last_publication"] ) : null;			
			?>			
			<tr>
				<td><?php echo $forum["forum_name"] ?></td>
				<td><?php echo $forum["forum_topics"] ?></td>
				<td><?php echo ( $lp != null ? $lp->format($df) : "-" ) ?></td>
			</tr>
			<?php
			$pdf_forums[] = array( $forum["forum_name"],
				$forum["forum_topics"],
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
		$sql = "select f.* from files f where file_project = $project_id";
		
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
		$sql = "SELECT b.*
		  FROM 
			btpsa_bug_table b 
		 	WHERE b.project_id = $project_id
		 	ORDER BY b.priority";
		
		$bs = db_loadHashList( $sql, "id" );
		
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
		
		//Para el pdf		
		$pdf_bugs = array();
		$pdf_bugs[] = array( 			
			$AppUI->_("Priority"),
			$AppUI->_("Category"),
			$AppUI->_("Status"),
			$AppUI->_("Updated"),
			$AppUI->_("Summary")			
		);
		?>		
		<p><?php echo $AppUI->_("Bugs")?></p>
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
			$d = new CDate( $b["last_updated"] );
			$d = $d->format( $df);						
			?>
			<tr>
				<td><?php echo $AppUI->_($priorities[$b["priority"]]) ?></td>
				<td><?php echo $AppUI->_($severities[$b["severity"]]) ?></td>
				<td><?php echo $AppUI->_($status[$b["status"]]) ?></td>
				<td><?php echo $d?></td>
				<td><?php echo $b["summary"] ?></td>								
			</tr>
			<?php
			$pdf_bugs[] = array(
				$AppUI->_($priorities[$b["priority"]]),
				$AppUI->_($severities[$b["severity"]]),
				$AppUI->_($status[$b["status"]]),
				$d,
				$b["summary"]					
			);
		}	
		?>
		</table>
		<?php
	}
	if ($log_pdf) {
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
?>