<?php /* PROJECTS $Id: tasklogs.php,v 1.2 2009-06-26 17:43:24 pkerestezachi Exp $ */
/**
* Generates a report of the task logs for given dates
*/

//Load viewable projects
include("modules/projects/read_projects.inc.php");

$sql = "
SELECT DISTINCT(user_id), CONCAT_WS(' ',user_first_name,user_last_name) AS fullname, user_username, user_last_name, user_first_name, permission_user, user_email, company_name, user_company
FROM users
LEFT JOIN permissions ON user_id = permission_user
LEFT JOIN companies ON company_id = user_company
where
user_type <> 5 
order by fullname
";
$users = db_loadList( $sql );


error_reporting( E_ALL );
$do_report = dPgetParam( $_POST, "do_report", 0 );
$log_all = dPgetParam( $_POST, 'log_all', 0 );
$log_pdf = dPgetParam( $_POST, 'log_pdf', 0 );

$log_start_date = dPgetParam( $_POST, "log_start_date", 0 );
$log_end_date = dPgetParam( $_POST, "log_end_date", 0 );
$user_id = dPgetParam( $_POST, "user_id", 0 );
$company_id = dPgetParam( $_POST, "company_id", 0 );
$canal_id = dPgetParam( $_POST, "canal_id", 0 );
$project_id = dPgetParam( $_POST, "project_id", 0 );

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : new CDate();
$end_date = intval( $log_end_date ) ? new CDate( $log_end_date ) : new CDate();

if (!$log_start_date) {
	$start_date->subtractSpan( new Date_Span( "14,0,0,0" ) );
}
$end_date->setTime( 23, 59, 59 );

$obj = new CCompany();
$companies = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );

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
    xajax_addProjects(company, canal, project, 'FALSE', '', '', 'project_id' );
    xajax_addUsersProjects('user_id', project ,canal ,company , user );
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

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">

<form name="editFrm" action="index.php?m=reports" method="post">
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<tr>
	<td align="left" nowrap="nowrap" >
	   <?php echo $AppUI->_('Company');?>:
	</td>
	<td  nowrap="nowrap" >
		 <?                                                                               
         echo arraySelect( $companies, "company_id", "style=\"font-size:10px; width: 160px\" onchange=\"canal_project(document.editFrm.company_id.value, '', '','' )\"", $company_id, TRUE , FALSE );
         ?>	
    </td>	

<script type="text/javascript">
    canal_project(document.editFrm.company_id.value, '<?=$canal_id?>', '<?=$project_id?>','<?=$user_id ?>' );			
</script>

   <td align="left" nowrap="nowrap" >
     <?php echo $AppUI->_('Canal');?>:
   </td>

   <td  nowrap="nowrap" >  
	   <select name="canal_id" id="canal_id" class="text" style="font-size:10px; width: 160px " onchange="canal_project(document.editFrm.company_id.value, document.editFrm.canal_id.value, '','' )" >
	   </select>
   </td>

   <td align="left" nowrap="nowrap" >
      <?php echo $AppUI->_('Project');?>:
   </td>
   
   <td  nowrap="nowrap" >  
		<select name="project_id" id="project_id" onchange="canal_project(document.editFrm.company_id.value, document.editFrm.canal_id.value, document.editFrm.project_id.value,'' );"; style="font-size:10px; width: 160px; "></select>
   </td>
   
   <td align="left" nowrap="nowrap" >	
	   <?php echo $AppUI->_('User');?>:
   </td>
   
   <td  nowrap="nowrap" >
		<select name="user_id" name="user_id" style="font-size:10px; width: 160px;">
		</select>
    </td>
</tr>
<tr>
	<td align="left" nowrap="nowrap">
	  <?php echo $AppUI->_('From');?>:
	</td>
	
    <td  nowrap="nowrap" >
		<input type="hidden" name="log_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
		<input type="text" name="start_date" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('start_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>
	
	<td align="left" nowrap="nowrap">
	   <?php echo $AppUI->_('To');?>
	</td>
	
	<td  nowrap="nowrap" >
		<input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('end_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
	</td>
	<td colspan="4">&nbsp;
	</td>
</tr>
<tr>
	<td nowrap="nowrap" colspan="4">
		<input type="checkbox" name="log_all" <?php if ($log_all) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log All' );?>
		<input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo "checked" ?> />
		<?php echo $AppUI->_( 'Make PDF' );?>
	</td>

	<td align="right" width="50%" nowrap="nowrap" colspan="4">
	    <input type="hidden" name="do_report" value="true">
		<input class="button" type="button" name="do_report" value="<?php echo $AppUI->_('submit');?>" onclick="submitIt()"/>
	</td>
</tr>
</form>
</table>

<?php
if ($do_report) {

	$sql = "SELECT t.*, CONCAT_WS(' ',u.user_first_name,u.user_last_name) AS creator
			, task_id, task_name
			FROM timexp AS t 
			inner join tasks ta on task_id = timexp_applied_to_id 
					and timexp_applied_to_type = 1
					and timexp_type = 1
			LEFT JOIN users AS u ON user_id = timexp_creator
			WHERE 
				 task_project = $project_id ".
				($user_id ? "AND timexp_creator = $user_id " : "");		
	if (!$log_all) {
		$sql .= "\n	AND timexp_date >= '".$start_date->format( FMT_DATETIME_MYSQL )."'"
		."\n	AND timexp_date <= '".$end_date->format( FMT_DATETIME_MYSQL )."'";
	}

	$sql .= " ORDER BY creator, timexp_date desc";

	//echo "<pre>$sql</pre>";

	$logs = db_loadList( $sql );
	echo db_error();
?>
	<br><table width="95%" align="center" cellspacing="1" cellpadding="4" border="0" class="tbl">
	<tr>
		<th nowrap="nowrap"><?php echo $AppUI->_('Creator');?></th>
		<th nowrap="nowrap"><?php echo $AppUI->_('Task Name');?></th>
		<th><?php echo $AppUI->_('Summary');?></th>
		<th><?php echo $AppUI->_('Description');?></th>
		<th><?php echo $AppUI->_('Date');?></th>
		<th><?php echo $AppUI->_('Hours');?></th>
		<?php /*<th><?php echo $AppUI->_('Cost Code');?></th> */ ?>
	</tr>
<?php
	$hours = 0.0;
	$pdfdata = array();
	$pdfdata[] = array(
		$AppUI->_('Creator'),
		$AppUI->_('Task Name'),
		$AppUI->_('Summary'),
		$AppUI->_('Description'),
		$AppUI->_('Date'),
		$AppUI->_('Hours'),
		//$AppUI->_('Cost Code')
	);

	$last_creator_id = 0;
	foreach ($logs as $log) {
		$date = new CDate( $log['timexp_date'] );
		$hours += $log['timexp_value'];


		
		if ($last_creator_id != $log["timexp_creator"]){
			$pdfdata[] = array(
				$log['creator'],
				'',
				'',
				'',
				'',
				'',
				//$log['task_log_costcode'],
			);	
			?>
	<tr>
		<td nowrap="nowrap" colspan="10">
			<?php echo $log['creator'];?>
		</td>
	</tr>
		
		<?php }
		$pdfdata[] = array(
			'',
			$log['task_name'],
			$log['timexp_name'],
			$log['timexp_description'],
			$date->format( $df ),
			sprintf( "%.2f", $log['timexp_value'] ),
			//$log['task_log_costcode'],
		);
		$last_creator_id = $log["timexp_creator"];
?>
	<tr>
		<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td><?php echo $log['task_name'];?></td>
		<td>
			<a href="index.php?m=timexp&a=view&timexp_id=<?php echo $log['timexp_id'];?>"><?php echo $log['timexp_name'];?></a>
		</td>
		<td><?php echo str_replace( "\n", "<br />", $log['timexp_description'] );?></td>
		<td><?php echo $date->format( $df );?></td>
		<td align="right"><?php printf( "%.2f", $log['timexp_value'] );?></td>
		<?php /*<td><?php echo $log['task_log_costcode'];?></td> */?>
	</tr>
<?php
	}
	$pdfdata[] = array(
		'',
		'',
		'',
		'',
		$AppUI->_('Total Hours').':',
		sprintf( "%.2f", $hours ),
		//'',
	);
?>
	<tr>
		<td align="right" colspan="5"><?php echo $AppUI->_('Total Hours');?>:</td>
		<td align="right"><?php printf( "%.2f", $hours );?></td>
		<?php /*<td align="right">&nbsp;</td>*/?>
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
			$pdf->ezText( $AppUI->_("All task log entries"), 9 );
		} else {
			$pdf->ezText( $AppUI->_("Task log entries from")." ".$start_date->format( $df )." ".$AppUI->_("to")." ".$end_date->format( $df ), 9 );
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
