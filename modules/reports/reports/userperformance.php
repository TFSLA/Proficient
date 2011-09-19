<?php
$do_report 		    = dPgetParam( $_POST, "do_report", 0 );
$log_start_date 	= dPgetParam( $_POST, "log_start_date", 0 );
$log_end_date 	    = dPgetParam( $_POST, "log_end_date", 0 );
//$log_all_projects 	= dPgetParam($_POST,"log_all_projects", 0);
$log_all		    = dPgetParam($_POST,"log_all", 0);
$log_pdf 			= dPgetParam($_POST, 'log_pdf', 0 ); 
$group_by_unit      = dPgetParam($_POST,"group_by_unit","day");
$p_user_id 			= dPgetParam($_POST, 'user_id', 0);
$project_id			= dPgetParam($_POST,"project_id", 0);
$canal_id           = dPgetParam($_POST,"canal_id", 0);

$log_all_projects= ($project_id) ? FALSE : TRUE;

//Para debugging

/*foreach ( $_POST as $k => $v )
{
	echo "<p>$k = $v</p>";
}
*/

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : new CDate();
$end_date = intval( $log_end_date ) ? new CDate( $log_end_date ) : new CDate();

if (!$log_start_date) {
	$start_date->subtractSpan( new Date_Span( "14,0,0,0" ) );
}
$end_date->setTime( 23, 59, 59 );

//Load viewable projects
//include("modules/projects/read_projects.inc.php");


//Esto habria que ponerlo en un inc como se hace con los projects
//Es para llenar el combo de los usuarios
$sql = "
SELECT DISTINCT(user_id), user_username, user_last_name, user_first_name, permission_user, user_email, company_name, user_company
FROM users
LEFT JOIN permissions ON user_id = permission_user
LEFT JOIN companies ON company_id = user_company
WHERE user_type <> 5 
";
$users = db_loadList( $sql );

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


function canal_project(company, canal, project, user )
{
    xajax_addCanal('canal_id', company ,canal,'TRUE','','');
    xajax_addProjects(company, canal, project, 'TRUE', '', '', 'project_id' );
    xajax_addUsersProjects('user_id', project ,canal ,company , user );
}
      
</script>

<form name="editFrm" action="" method="post">
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
	<td width="1%"> <?php echo $AppUI->_('Company');?>: </td>
	<td colspan="2">
		<?
			$sql = "SELECT company_id, company_name FROM companies order by company_name;";
			$companies = db_loadHashList( $sql );
			
			echo arraySelect($companies, "company_id", "name='company_id'  onchange=\"canal_project(document.editFrm.company_id.value, '', '','' );\"", $company_id,'','','210px');
		?>
	</td>
</tr>
<script type="text/javascript">
    canal_project(document.editFrm.company_id.value, '<?=$canal_id?>', '<?=$project_id?>','<?=$p_user_id ?>' );			
</script>
<tr>
   <td width="1%"> <?php echo $AppUI->_('Canal');?>: </td>
   <td colspan="2">                                                
        <select name="canal_id" id="canal_id" onchange="canal_project(document.editFrm.company_id.value, document.editFrm.canal_id.value, '','' );"; style="width: 210px; "></select>
   </td>
</tr>
<tr>
	<td align="left" nowrap="nowrap"  width="1%">
		<?php echo $AppUI->_('Project');?>:&nbsp;&nbsp;
	</td>
	<td colspan="2">
		<select name="project_id" id="project_id" onchange="canal_project(document.editFrm.company_id.value, document.editFrm.canal_id.value, document.editFrm.project_id.value,'' );"; style="width: 210px; "></select>		

	</td>
</tr>
<tr>
	<td  width="1%">
		<?php echo $AppUI->_('User');?>:&nbsp;&nbsp;
	</td>
	<td colspan="2">
		<select id="user_id" name="user_id" style="width: 210px; "></select>
	</td>
</tr>

<tr>
	<td align="left" nowrap="nowrap" width="1%"><?php echo $AppUI->_('From');?>:</td>
	<td align="left" nowrap="nowrap">
		<input type="hidden" name="log_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
		<input type="text" name="start_date" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('start_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<?php echo $AppUI->_('To');?>
		<input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('end_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>
	<td>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="checkbox" name="log_all" <?php if ($log_all) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log All' );?>
	</td>
</tr>
<tr>
	<td nowrap="nowrap" colspan="2">
		<input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo "checked" ?> />
		<?php echo $AppUI->_( 'Make PDF' );?>
	</td>

	<td align="right" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>

</table>
</form>

<?php
if($do_report){	
	// Let's figure out which users we have
	$sql = "SELECT  u.user_id,
	 				u.user_username, 
					u.user_first_name, 
					u.user_last_name
	        FROM users AS u
					where 1 = 1";
	        
	if ( $p_user_id != 0 )
	{
		$sql .= " and u.user_id = $p_user_id";
	}
	$sql .= " and user_type <> 5 ";
	$user_list = db_loadHashList($sql, "user_id");
	
	// Now which tasks will we need and the real allocated hours (estimated time / number of users)
	// Also we will use tasks with duration_type = 1 (hours) and those that are not marked
	// as milstones
	$sql = "SELECT t.task_id, round(t.task_duration * t.task_duration_type/count(ut.task_id),2) as hours_allocated
	        FROM tasks as t, user_tasks as ut
	        WHERE t.task_id = ut.task_id
				  AND t.task_milestone    ='0'";
	
	if(!$log_all_projects){
		$sql .= " AND t.task_project='$project_id'\n";
	}
	
	if(!$log_all){
		$sql .= " AND t.task_start_date >= \"".$start_date->format( FMT_DATETIME_MYSQL )."\"
		          AND t.task_start_date <= \"".$end_date->format( FMT_DATETIME_MYSQL )."\"";
	}
	
	$sql .= "GROUP BY t.task_id";
	echo "<pre>";
	//var_dump($sql);
	echo "</pre>";
	$task_list = db_loadHashList($sql, "task_id");
	$pdfdata = array();
	$pdfdata[] = array( $AppUI->_('User'),
		$AppUI->_('Allocated Hours'),
		$AppUI->_('Worked Hours'),
		"%"
	 )	
?>

<table cellspacing="1" cellpadding="4" border="0" class="tbl">
	<tr>
		<th colspan='2'><?php echo $AppUI->_('User');?></th>
		<th><?php echo $AppUI->_('Allocated Hours'); ?></th>
		<th><?php echo $AppUI->_('Worked Hours'); ?></th>
		<th>%</th>
	</tr>

<?php
	if(count($user_list)){
		$percentage_sum = $hours_allocated_sum = $hours_worked_sum = 0;
		$sum_total_hours_allocated = $sum_total_hours_worked = 0;
		
		foreach($user_list as $user_id => $user){
			$sql = "SELECT task_id
			        FROM user_tasks
			        where user_id = $user_id";
			$tasks_id = db_loadColumn($sql);
			$total_hours_allocated = $total_hours_worked = 0;
			
			foreach($tasks_id as $task_id){
				if(isset($task_list[$task_id])){
					// Now let's figure out how many time did the user spent in this task
					$sql = "				
					SELECT coalesce(sum(timexp_value),0)
		        			FROM timexp
		        			WHERE timexp_applied_to_type = 1
							AND timexp_applied_to_id = $task_id 
							AND timexp_type = 1
							AND timexp_creator = $user_id";

					$hours_worked = round(db_loadResult($sql),2);
					
					$total_hours_allocated += $task_list[$task_id]["hours_allocated"];
					$total_hours_worked    += $hours_worked;
				}
			}
			
			$sum_total_hours_allocated += $total_hours_allocated;
			$sum_total_hours_worked    += $total_hours_worked;
			
			if($total_hours_allocated > 0 || $total_hours_worked > 0){
				$percentage = 0;
				if($total_hours_worked>0){
					$percentage = ($total_hours_allocated/$total_hours_worked)*100;
				}
				$pdfdata[] = array( "(".$user["user_username"].") - ".$user["user_first_name"]." ".$user["user_last_name"], 
					$total_hours_allocated,
					$total_hours_worked,
					number_format($percentage,0)."%"
				);
				?>
				<tr>
					<td><?php echo "(".$user["user_username"].") </td><td> ".$user["user_first_name"]." ".$user["user_last_name"]; ?></td>
					<td align='right'><?php echo $total_hours_allocated; ?> </td>
					<td align='right'><?php echo $total_hours_worked; ?> </td>
					<td align='right'> <?php echo number_format($percentage,0); ?>% </td>
				</tr>
				<?php
			}
		}
		$sum_percentage = 0;
		if($sum_total_hours_worked > 0){
			$sum_percentage = ($sum_total_hours_allocated/$sum_total_hours_worked)*100;
		}
		$pdfdata[] = array( $AppUI->_('Total'),
			$sum_total_hours_allocated,
			$sum_total_hours_worked,
			number_format($sum_percentage,0)."%"
		);
		?>
			<tr>
				<td colspan='2'><?php echo $AppUI->_('Total'); ?></td>
				<td align='right'><?php echo $sum_total_hours_allocated; ?></td>
				<td align='right'><?php echo $sum_total_hours_worked; ?></td>
				<td align='right'><?php echo number_format($sum_percentage,0); ?>%</td>
			</tr>
		<?php		
	} else {
		?>
		<tr>
		    <td><p><?php echo $AppUI->_('There are no tasks that fulfill selected filters');?></p></td>
		</tr>
		<?php
	}
	?>
</table>
<?	
	if ( $log_pdf )
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
		$pdf->ezText( "\n" . $AppUI->_('User Performance Report'), 12 );
		$pdf->ezText( "$pname", 15 );
		if ($log_all) {
			$pdf->ezText( $AppUI->_("All task log entries"), 9 );
		} else {
			$pdf->ezText( $AppUI->_("Task log entries from")." ".$start_date->format( $df ).' '.$AppUI->_("to").' '.$end_date->format( $df ), 9 );
		}
		$pdf->ezText( "\n\n" );

		$columns = null;
		$title = null;
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

		$pdf->ezTable( $pdfdata, $columns, $title, $options );

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
