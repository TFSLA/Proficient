<?php
$do_report 	    = dPgetParam( $_POST, "do_report", 0 );
$log_start_date     = dPgetParam( $_POST, "log_start_date", 0 );
$log_end_date 	    = dPgetParam( $_POST, "log_end_date", 0 );
$log_all_projects   = dPgetParam($_POST,"log_all_projects", 0);
$log_all	    = dPgetParam($_POST,"log_all", 0);
$log_pdf = dPgetParam( $_POST, 'log_pdf', 0 );
$p_user_id = dPgetParam( $_POST, 'user_id', null); //La p por parametro

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : new CDate();
$end_date   = intval( $log_end_date )   ? new CDate( $log_end_date ) : new CDate();

if (!$log_start_date) {
	$start_date->subtractSpan( new Date_Span( "14,0,0,0" ) );
}
$end_date->setTime( 23, 59, 59 );

//Load viewable projects
include("modules/projects/read_projects.inc.php");

//Esto habria que ponerlo en un inc como se hace con los projects
//Es para llenar el combo de los usuarios
$sql = "
SELECT DISTINCT(user_id), user_username, user_last_name, user_first_name, permission_user, user_email, company_name, user_company
FROM users
LEFT JOIN permissions ON user_id = permission_user
LEFT JOIN companies ON company_id = user_company
where user_type <> 5 order by user_username
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
</script>

<form name="editFrm" action="index.php?m=reports" method="post">
<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
<td>
<?php echo $AppUI->_('User');?>:&nbsp;&nbsp;
		<select name="user_id" style="font-size:10px;width: 160px;">
		  <option value="" <?= ($p_user_id==null ? "selected": "")?>><?php echo $AppUI->_('All');?></option>
<?
foreach ($users as $row) {
?>
		  <option <? if($row["user_id"]==$p_user_id)echo " selected ";?> value="<?=$row["user_id"]?>"><?=$row["user_username"]?></option>
<?}?>
		</select>
</td>
	<td align = "left" nowrap="nowrap"><?php echo $AppUI->_('From');?>:	
		<input type="hidden" name="log_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
		<input type="text" name="start_date" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('start_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>
	<td align = "left" nowrap="nowrap"><?php echo $AppUI->_('To');?>	
		<input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('end_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>
</tr>
<tr>	
	<td nowrap="nowrap">
		<input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo "checked" ?> />
		<?php echo $AppUI->_( 'Make PDF' );?>
	</td>
	<td align="left" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>

</table>
</form>

<?php
if($do_report){
	//Hay que incluir la clase CTask
	require_once ( $AppUI->getModuleClass( 'tasks' ) );
	
	//Falta ver el tema de los pdf
	
	//Hay que llenar un array de arrays $pdfdata
	//Aca creo el array
	$pdfdata = array();
	
	//En ppio el primer array sería el encabezado de la tabla.	
	
	// Let's figure out which users we have
	$sql = "SELECT  u.user_id,
	 				u.user_username, 
					u.user_first_name, 
					u.user_last_name
	        FROM users AS u";
	if ( $p_user_id != "" )
	{
		$sql .= " where u.user_id = $p_user_id";
	}
	
	$user_list = db_loadHashList($sql, "user_id");
	
	$sql = "SELECT t.*
			FROM tasks AS t
			WHERE (task_start_date
			   BETWEEN \"".$start_date->format( FMT_DATETIME_MYSQL )."\" 
	                AND \"".$end_date->format( FMT_DATETIME_MYSQL )."\" 
	           OR task_end_date	BETWEEN \"".$start_date->format( FMT_DATETIME_MYSQL )."\" 
	                AND \"".$end_date->format( FMT_DATETIME_MYSQL )."\")
	        AND !isnull(task_end_date) AND task_end_date != '0000-00-00 00:00:00'
	        AND !isnull(task_start_date) AND task_start_date != '0000-00-00 00:00:00'
	        AND task_dynamic   ='0'
	        AND task_milestone = '0'
	        AND task_duration  > 0";

	$task_list_hash = db_loadHashList($sql, "task_id");
	$task_list      = array();
	foreach($task_list_hash as $task_id => $task_data){
		$task = new CTask();
		$task->bind($task_data);
		$task_list[] = $task;
	}
	
	$user_usage            = array();
	$task_dates            = array();
	
	$actual_date = $start_date;
	$days_header = ""; // we will save days title here
	
	if ( count($task_list) == 0 ) {
		echo "<p>" . $AppUI->_( 'No data available' ) ."</p>";
	}else {
		foreach($task_list as $task) {
			$task_start_date  = new CDate($task->task_start_date);
			$task_end_date    = new CDate($task->task_end_date);
			
			$day_difference   = $task_end_date->dateDiff($task_start_date);
			$actual_date      = $task_start_date;
	
			$users                 = $task->getAssignedUsers();
			
			$task_duration_per_day = $task->getTaskDurationPerDay();
			
			for($i = 0; $i<=$day_difference; $i++){
				if(!$actual_date->before($start_date) && !$actual_date->after($end_date)
				   && $actual_date->isWorkingDay()){
			   		foreach($users as $user_id => $user_data){						
						if(!isset($user_usage[$user_id][$actual_date->format("%Y%m%d")])){
							$user_usage[$user_id][$actual_date->format("%Y%m%d")] = 0;
						}
						$user_usage[$user_id][$actual_date->format("%Y%m%d")] += $task_duration_per_day;
						if($user_usage[$user_id][$actual_date->format("%Y%m%d")] < 0.005){
							//We want to show at least 0.01 even when the assigned time is very small so we know
							//that at that time the user has a running task
							$user_usage[$user_id][$actual_date->format("%Y%m%d")] += 0.006;
						}
					}				   	
				}
				$actual_date->addDays(1);
			}
		}
	
		$days_difference = $end_date->dateDiff($start_date);
		$actual_date     = $start_date;
		$working_days_count = 0;
		$allocated_hours_sum = 0;
		
		//Voy cargando los datos del pdf a medida que se generan
		$pdfdata[] = array();		
		$pdfdata[count($pdfdata) - 1][] = $AppUI->_("User");
		
		$table_header = "<tr><th>".$AppUI->_("User")."</th>";
		for($i=0; $i<=$days_difference; $i++){
			//Guardo el dia para el pdf
			$pdfdata[count($pdfdata) - 1][] = utf8_encode(Date_Calc::getWeekdayAbbrname($actual_date->day, $actual_date->month, $actual_date->year, 3)); 
			$table_header .= "<th>".utf8_encode(Date_Calc::getWeekdayAbbrname($actual_date->day, $actual_date->month, $actual_date->year, 3))."</th>";
			if($actual_date->isWorkingDay()){
				$working_days_count++;
			}
			$actual_date->addDays(1);
		}
		$table_header .= "<th nowrap='nowrap'>".$AppUI->_("Allocated")."</th></tr>";
		$pdfdata[count($pdfdata) - 1][] = $AppUI->_("Allocated");		
		//Hasta aca tenemos el encabezado del pdf
		
		$table_rows = "";
		
		foreach($user_list as $user_id => $user_data){
			if(isset($user_usage[$user_id])) {
				$table_rows .= "<tr><td nowrap='nowrap'>(".$user_data["user_username"].") ".$user_data["user_first_name"]." ".$user_data["user_last_name"]."</td>";
				//Agrego una fila al pdf
				$pdfdata[] = array();
				
				//El nombre del usuario
				$pdfdata[count($pdfdata) - 1][] = "(".$user_data["user_username"].") ".$user_data["user_first_name"]." ".$user_data["user_last_name"];
				
				$actual_date = $start_date;
				for($i=0; $i<=$days_difference; $i++){
					$table_rows .= "<td>";
					if(isset($user_usage[$user_id][$actual_date->format("%Y%m%d")])){
						$hours       = number_format($user_usage[$user_id][$actual_date->format("%Y%m%d")],2);
						
						//Se agrega al html y al pdf
						$table_rows .= $hours;
						$pdfdata[count($pdfdata) - 1][] = $hours;
						
						$percentage_used = round($hours/$AppUI->getConfig("daily_working_hours")*100);
						$bar_color       = "blue";
						if($percentage_used > 100){
							$bar_color = "red";
							$percentage_used = 100;
						}
						$table_rows .= "<div style='height:2px;width:$percentage_used%; background-color:$bar_color'>&nbsp;</div>";
						//Ver si despues se puede agregar la barrita del porcentaje						
					} else {
						$table_rows .= "&nbsp;";
						$pdfdata[count($pdfdata) - 1][] = "";
					} 
					$table_rows .= "</td>";
					$actual_date->addDays(1);
				}
				
				$array_sum = array_sum($user_usage[$user_id]);
				$average_user_usage = number_format( ($array_sum/($working_days_count*$AppUI->getConfig("daily_working_hours")))*100, 2);
				$allocated_hours_sum += $array_sum;
				
				$bar_color = "blue";
				if($average_user_usage > 100){
					$bar_color = "red";
					$average_user_usage = 100;
				}
				$table_rows .= "<td ><div align='right'>". $average_user_usage;
				$table_rows .= "%</div>";
				$table_rows .= "<div align='left' style='height:2px;width:$average_user_usage%; background-color:$bar_color'>&nbsp;</div></td>";
				$table_rows .= "</tr>";
				$pdfdata[count($pdfdata) - 1][] = "$average_user_usage";
				//Ver lo de la barrita
			}
		}
		?>
			<center><table class="std">
			<?php echo $table_header . $table_rows; ?>
			</table>
			<table width="100%"><tr><td align="center">
		<?php		
			$total_hours_capacity = $working_days_count*$AppUI->getConfig("daily_working_hours")*count($user_usage);						
			
			echo "<h4>".$AppUI->_("Total capacity for shown users")."</h4>";
			echo $AppUI->_("Allocated hours").": ".number_format($allocated_hours_sum,2)."<br />";
			echo $AppUI->_("Total capacity").": ".number_format($total_hours_capacity,2)."<br />";
			echo $AppUI->_("Percentage used").": ".number_format($allocated_hours_sum/$total_hours_capacity,2)*100 ."%<br />";
			
			//Empieza el footer del pdf
			//Lo voy a hacer como una tabla
			
			$pdffooter = array();
			$pdffooter[][] = "<u>".$AppUI->_("Total capacity for shown users")."</u>";
			$pdffooter[][] = $AppUI->_("Allocated hours").": ".number_format($allocated_hours_sum,2);
			$pdffooter[][] = $AppUI->_("Total capacity").": ".number_format($total_hours_capacity,2);
			$pdffooter[][] = $AppUI->_("Percentage used").": ".number_format($allocated_hours_sum/$total_hours_capacity,2)*100;			
	?>
			</td>
			<td align="center">
		<?php
			$total_hours_capacity = $working_days_count*$AppUI->getConfig("daily_working_hours")*count($user_list);
		
			echo "<h4>".$AppUI->_("Total capacity for all users")."</h4>";
			echo $AppUI->_("Allocated hours").": ".number_format($allocated_hours_sum,2)."<br />";
			echo $AppUI->_("Total capacity").": ".number_format($total_hours_capacity,2)."<br />";
			echo $AppUI->_("Percentage used").": ".number_format($allocated_hours_sum/$total_hours_capacity,2)*100 ."%<br />";
			
			$pdffooter[][] = "\n\n"; //Dejo un renglon entre los dos resultados
			$pdffooter[][] = "<u>".$AppUI->_("Total capacity for all users")."</u>";
			$pdffooter[][] = $AppUI->_("Allocated hours").": ".number_format($allocated_hours_sum,2);
			$pdffooter[][] = $AppUI->_("Total capacity").": ".number_format($total_hours_capacity,2);
			$pdffooter[][] = $AppUI->_("Percentage used").": ".number_format($allocated_hours_sum/$total_hours_capacity,2)*100;
		?>
			</td></tr>
			</table>
			</center>
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
		$pdf->ezText( "\n" . $AppUI->_('Allocated User Hours Report'), 12 );
		$pdf->ezText( "$pname", 15 );
		
		$pdf->ezText( $AppUI->_("Allocated user hours from")." ".$start_date->format( $df )." ".$AppUI->_("to")." ".$end_date->format( $df ), 9 );
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
		
		//El array de $pdfdata tiene que estar lleno con las cosas que hay que meter
		/* Para debugging
		echo "<p>pdfdata</p>";
		foreach ( $pdfdata as $pdfline )
		{
			echo "<p>";
			foreach ( $pdfline as $dato )
			{
				echo "$dato - ";
			}
			echo "</p>";
		}
		*/		
		$pdf->ezTable( $pdfdata, $columns, $title, $options );
	
		$pdf->ezText("\n\n");
		//Aca hay que poner el footer		
	
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
		$pdf->ezTable( $pdffooter, null, null, $options );		
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