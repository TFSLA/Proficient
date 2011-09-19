<?php 
/**
* Generates a report of the expense logs for given dates
*/

//Load viewable projects
include("modules/projects/read_projects.inc.php");

$sql = "
SELECT DISTINCT(user_id), user_username, user_last_name, user_first_name, permission_user, user_email, company_name, user_company
FROM users
LEFT JOIN permissions ON user_id = permission_user
LEFT JOIN companies ON company_id = user_company
where user_type <> 5 
ORDER BY user_username
";
$users = db_loadList( $sql );


error_reporting( E_ALL );
$do_report = dPgetParam( $_POST, "do_report", 0 );
$log_all = dPgetParam( $_POST, 'log_all', 0 );
$log_pdf = dPgetParam( $_POST, 'log_pdf', 0 );
$log_ignore = dPgetParam( $_POST, 'log_ignore', 0 );

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

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">

<form name="editFrm" action="index.php?m=reports" method="post">
<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<tr>
	<td align="left" nowrap="nowrap" colspan="8"><?php echo $AppUI->_('User');?>:&nbsp;&nbsp;
		<select name="user_id" style="font-size:10px">
		  <option value=""><?php echo $AppUI->_('All');?></option>
<?if($user_id==null)$user_id="";?>
<?foreach ($users as $row) {
?>
		  <option <?if($row["user_id"]==$user_id)echo " selected ";?> value="<?=$row["user_id"]?>"><?=$row["user_username"]?></option>
<?}?>
		</select>		
</td>
</tr>
<tr>
	<td align="left" nowrap="nowrap"><?php echo $AppUI->_('From');?>:
		<input type="hidden" name="log_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
		<input type="text" name="start_date" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('start_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>
	<td align="left" nowrap="nowrap"><?php echo $AppUI->_('To');?>
		<input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('end_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>
</tr>
<tr>
	<td nowrap="nowrap">
		<input type="checkbox" name="log_all" <?php if ($log_all) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log All' );?>
		<input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo "checked" ?> />
		<?php echo $AppUI->_( 'Make PDF' );?>
		<input type="checkbox" name="log_ignore" <?=($log_ignore ? "checked" : "")?>/>
		<?php echo $AppUI->_( 'Ignore 0 $' );?>
	</td>

	<td align="left" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>
</form>
</table>

<?php
if ($do_report) {
	$where='';
	if ($user_id!=''){
		$where .="timexp_creator = $user_id ";
	}

	if ($start_date!='' && !$log_all){
		if ($where!='') $where .=" AND ";
		$where .= "\n	 timexp_date >= '".$start_date->format( FMT_DATETIME_MYSQL )."'";
	}
	
	if ($end_date!='' && !$log_all){
		if ($where!='') $where .=" AND ";
		$where .="\n timexp_date <= '".$end_date->format( FMT_DATETIME_MYSQL )."'";
	}

	if ($log_ignore!='') {
		
		if ($where!='') $where .=" AND ";
		$where .= "\n  timexp_value > 0";
		
	}
	
	if ($where!='') $where="WHERE ".$where;

	$sql = "SELECT t.*, CONCAT_WS(' ',u.user_first_name,u.user_last_name) AS creator
			FROM timexp AS t inner join tasks ta on task_id = timexp_applied_to_id 
										and timexp_applied_to_type = 1
										and timexp_type = 2
						LEFT JOIN users AS u ON user_id = timexp_creator
			$where";
				 
						
	
	$sql .= " ORDER BY timexp_date";

	//echo "<pre>$sql</pre>";

	$logs = db_loadList( $sql );
	echo db_error();
?>
	<br><table width="95%" align="center" cellspacing="1" cellpadding="4" border="0" class="tbl">
	<tr>
		<th nowrap="nowrap"><?php echo $AppUI->_('Task Creator');?></th>
		<th><?php echo $AppUI->_('Summary');?></th>
		<th><?php echo $AppUI->_('Description');?></th>
		<th><?php echo $AppUI->_('Date');?></th>
		<th><?php echo $AppUI->_('Cost');?></th>
	</tr>
<?php
	$hours = 0.0;
	$pdfdata = array();
	$pdfdata[] = array(
		$AppUI->_('Task Creator'),
		$AppUI->_('Summary'),
		$AppUI->_('Description'),
		$AppUI->_('Date'),
		$AppUI->_('Cost')
	);

	foreach ($logs as $log) {
		$date = new CDate( $log['timexp_date'] );
		$hours += $log['timexp_value'];

		$pdfdata[] = array(
			$log['creator'],
			$log['timexp_name'],
			$log['timexp_description'],
			$date->format( $df ),
			sprintf( "%.2f", $log['timexp_value'] ),
		);
?>
	<tr>
		<td><?php echo $log['creator'];?></td>
		<td>
			<a href="index.php?m=timexp&a=view&timexp_id=<?php echo $log['timexp_id'];?>"><?php echo $log['timexp_name'];?></a>
		</td>
		<td><?php echo str_replace( "\n", "<br />", $log['timexp_description'] );?></td>
		<td><?php echo $date->format( $df );?></td>
		<td align="right"><?php printf( "%.2f", $log['timexp_value'] );?></td>
	</tr>
<?php
	}
	$pdfdata[] = array(
		'',
		'',
		'',
		$AppUI->_('totalexpenses').':',
		sprintf( "%.2f", $hours ),
		'',
	);
?>
	<tr>
		<td align="right" colspan="4"><?php echo $AppUI->_('totalexpenses');?>:</td>
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
		$pdf->ezText( "\n" . $AppUI->_('Expenses Report'), 12 );
		$pdf->ezText( "$pname", 15 );
		if ($log_all) {
			$pdf->ezText( $AppUI->_("All expenses"), 9 );
		} else {
			$pdf->ezText( $AppUI->_("Expenses from")." ".$start_date->format( $df )." ".$AppUI->_("to")." ".$end_date->format( $df ), 9 );
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
