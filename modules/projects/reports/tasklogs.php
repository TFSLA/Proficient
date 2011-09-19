<?php /* PROJECTS $Id: tasklogs.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
/**
* Generates a report of the task logs for given dates
*/
error_reporting( E_ALL );
$do_report = dPgetParam( $_POST, "do_report", 0 );
$log_all = dPgetParam( $_POST, 'log_all', 0 );
$log_pdf = dPgetParam( $_POST, 'log_pdf', 0 );
$log_ignore = dPgetParam( $_POST, 'log_ignore', 0 );
$log_userfilter = dPgetParam( $_POST, 'log_userfilter', '0' );
$log_allprojects = dPgetParam( $_POST, 'log_allprojects', '0' );

$log_start_date = dPgetParam( $_POST, "log_start_date", 0 );
$log_end_date = dPgetParam( $_POST, "log_end_date", 0 );

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
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
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

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">

<form name="editFrm" action="index.php?m=projects&a=reports" method="post">
<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period');?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
		<input type="text" name="start_date" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" style="width: 80px" />
		<a href="#" onClick="popCalendar('start_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('to');?></td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" style="width: 80px"/>
		<a href="#" onClick="popCalendar('end_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	
	<TD NOWRAP>
		<?php echo $AppUI->_('User');?>: 
		<SELECT NAME="log_userfilter" CLASS="text" STYLE="width: 80px">
		
	<?php
		$usersql = "
		SELECT user_id, user_username, user_first_name, user_last_name
		FROM users
		";
		
		if ( $log_userfilter == 0 ) echo '<OPTION VALUE="0" SELECTED>'.$AppUI->_('All users' );
		else echo '<OPTION VALUE="0">All users';
		
		if (($rows = db_loadList( $usersql, NULL )))
		{
			foreach ($rows as $row)
			{
				if ( $log_userfilter == $row["user_id"])
					echo "<OPTION VALUE='".$row["user_id"]."' SELECTED>".$row["user_username"];
				else
					echo "<OPTION VALUE='".$row["user_id"]."'>".$row["user_username"];
			}
		}

	?>
			
		</SELECT>
	</TD>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_allprojects" <?php if ($log_allprojects) echo "checked" ?> />
		<?php echo $AppUI->_( 'All Projects' );?>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_all" <?php if ($log_all) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log All' );?>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo "checked" ?> />
		<?php echo $AppUI->_( 'Make PDF' );?>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_ignore" />
		<?php echo $AppUI->_( 'Ignore 0 hours' );?>
	</td>

	<td align="right" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>
</form>
</table>

<?php
if ($do_report) {

	$sql = "SELECT t.*, CONCAT_WS(' ',u.user_first_name,u.user_last_name) AS creator"
		."\nFROM task_log AS t, tasks"
		."\nLEFT JOIN users AS u ON user_id = task_log_creator"
		."\nWHERE task_log_task = task_id";
	if (!$log_allprojects)
	{
		 $sql .= "\nAND task_project = $project_id";
	}
	if (!$log_all) {
		$sql .= "\n	AND task_log_date >= '".$start_date->format( FMT_DATETIME_MYSQL )."'"
		."\n	AND task_log_date <= '".$end_date->format( FMT_DATETIME_MYSQL )."'";
	}
	if ($log_ignore) {
		$sql .= "\n	AND task_log_hours > 0";
	}
	if ($log_userfilter) {
		$sql .= "\n	AND task_log_creator = $log_userfilter";
	}
	$sql .= " ORDER BY task_log_date";

	//echo "<pre>$sql</pre>";

	$logs = db_loadList( $sql );
	echo db_error();
?>
	<table cellspacing="1" cellpadding="4" border="0" class="tbl">
	<tr>
		<th nowrap="nowrap"><?php echo $AppUI->_('Created by');?></th>
		<th><?php echo $AppUI->_('Summary');?></th>
		<th><?php echo $AppUI->_('Description');?></th>
		<th><?php echo $AppUI->_('Date');?></th>
		<th><?php echo $AppUI->_('Hours');?></th>
		<th><?php echo $AppUI->_('Cost Code');?></th>
	</tr>
<?php
	$hours = 0.0;
	$pdfdata = array();
	$pdfdata[] = array(
		$AppUI->_('Created by'),
		$AppUI->_('Summary'),
		$AppUI->_('Description'),
		$AppUI->_('Date'),
		$AppUI->_('Hours'),
		$AppUI->_('Cost Code')
	);

	foreach ($logs as $log) {
		$date = new CDate( $log['task_log_date'] );
		$hours += $log['task_log_hours'];

		$pdfdata[] = array(
			$log['creator'],
			$log['task_log_name'],
			$log['task_log_description'],
			$date->format( $df ),
			sprintf( "%.2f", $log['task_log_hours'] ),
			$log['task_log_costcode'],
		);
?>
	<tr>
		<td><?php echo $log['creator'];?></td>
		<td>
			<a href="index.php?m=tasks&a=view&tab=1&task_id=<?php echo $log['task_log_task'];?>&task_log_id=<?php echo $log['task_log_id'];?>"><?php echo $log['task_log_name'];?></a>
		</td>
		<td><?php
// dylan_cuthbert: auto-transation system in-progress, leave these lines for time-being
            $transbrk = "\n[translation]\n";
			$descrip = str_replace( "\n", "<br />", $log['task_log_description'] );
			$tranpos = strpos( $descrip, str_replace( "\n", "<br />", $transbrk ) );
			if ( $tranpos === false) echo $descrip;
			else
			{
				$descrip = substr( $descrip, 0, $tranpos );
				$tranpos = strpos( $log['task_log_description'], $transbrk );
				$transla = substr( $log['task_log_description'], $tranpos + strlen( $transbrk ) );
				$transla = trim( str_replace( "'", '"', $transla ) );
				echo $descrip."<div style='font-weight: bold; text-align: right'><a title='$transla' class='hilite'>[".$AppUI->_("translation")."]</a></div>";
			}
// dylan_cuthbert; auto-translation end
			?></td>
		<td><?php echo $date->format( $df );?></td>
		<td align="right"><?php printf( "%.2f", $log['task_log_hours'] );?></td>
		<td><?php echo $log['task_log_costcode'];?></td>
	</tr>
<?php
	}
	$pdfdata[] = array(
		'',
		'',
		'',
		$AppUI->_('Total Hours').':',
		sprintf( "%.2f", $hours ),
		'',
	);
?>
	<tr>
		<td align="right" colspan="4"><?php echo $AppUI->_('Total Hours');?>:</td>
		<td align="right"><?php printf( "%.2f", $hours );?></td>
	</tr>
	</table>
<?php
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
		// $pdf->ezText( $AppUI->getConfig( 'company_name' ).' :: '.$AppUI->getConfig( 'page_title' ), 12 );		

		$date = new CDate();
		$pdf->ezText( "\n" . $date->format( $df ) , 8 );

		$pdf->selectFont( "$font_dir/Helvetica-Bold.afm" );
		$pdf->ezText( "\n" . $AppUI->_('Task Log Report'), 12 );
		$pdf->ezText( "$pname", 15 );
		if ($log_all) {
			$pdf->ezText( "All task log entries", 9 );
		} else {
			$pdf->ezText( "Task log entries from ".$start_date->format( $df ).' to '.$end_date->format( $df ), 9 );
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
			echo "<a href=\"$base_url/files/temp/temp$AppUI->user_id.pdf\" target=\"pdf\">";
			echo $AppUI->_( "View PDF File" );
			echo "</a>";
		} else {
			echo "Could not open file to save PDF.  ";
			if (!is_writable( $temp_dir )) {
				"The files/temp directory is not writable.  Check your file system permissions.";
			}
		}
	}
}
?>
