<?php
/*-----------------------------------------------------------
    Reporte por Project Type:

    Poder filtrar por CLIENTE, PROYECTO y CANAL. 
	SUMARIZAR LOS TOTALES ABAJO.
-------------------------------------------------------------*/

$do_report 		    = dPgetParam( $_POST, "do_report", 0 );
$log_pdf 			= dPgetParam( $_POST, 'log_pdf', 0 ); 
$project_id			= dPgetParam($_POST,"project_id", "");
$company_id			= dPgetParam($_POST,"company_id", "");
$canal_id			= dPgetParam($_POST,"canal_id", "");

include_once("modules/projects/projects.class.php");
include("modules/projects/read_projects.inc.php");
//include("modules/public/ajax.php");

$obj = new CCompany();
$companies = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$companies = arrayMerge( array( '0'=>$AppUI->_('All') ), $companies );

$extra = array();
$extra['from'] = '';
$extra['where'] ='and company_type=0';

$canal = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name',$index= null, $extra);
$canal = arrayMerge( array( '0'=>$AppUI->_('All') ), $canal );

?>

<script type="text/javascript"> 
      
      // Para actualizar Canal y proyecto al mismo tiempo
      function canal_project(company, canal, project )
      {
      	xajax_addCanal('canal_id', company ,canal,'TRUE','','');
      	xajax_addProjects(company, canal, project, 'TRUE', '', '', 'project_id' );
      }
		
</script>

<form name="editFrm" action="index.php?m=reports" method="post">
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
	<td align="left" nowrap="nowrap" colspan="8">

<?php echo $AppUI->_('Company');?>:&nbsp;
		 <?                                                                               
         echo arraySelect( $companies, "company_id", "style=\"font-size:10px\" onchange=\"canal_project(document.editFrm.company_id.value, '', '' )\"", $company_id, TRUE , FALSE );
         ?>		
&nbsp;&nbsp;&nbsp;

<script type="text/javascript">
    canal_project('<?=$company_id?>', '<?=$canal_id?>', '<?=$project_id?>' );			
</script>

<?php echo $AppUI->_('Canal');?>:&nbsp;
                                                                                       
   <select name="canal_id" id="canal_id" class="text" style="font-size:10px; width: 160px " onchange="canal_project(document.editFrm.company_id.value, document.editFrm.canal_id.value, '' )" >
   </select>
&nbsp;&nbsp;&nbsp;
   
<?php echo $AppUI->_('Project');?>:&nbsp;&nbsp; 
   <select name="project_id" id="project_id" style="font-size:10px; width: 160px">
   </select>


</td>
</tr>

<tr>
	<td nowrap="nowrap">
		<input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo "checked" ?> />
		<?php echo $AppUI->_( 'Make PDF' );?>
	</td>	
	<td width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>

</table>
</form>

<?php
if($do_report){

	$sql = "
	        SELECT project_name, project_company, project_canal, project_id, project_other_estimated_cost, company_name 
            FROM projects 
            LEFT JOIN companies ON company_id = project_company
			WHERE 1=1
           ";						

	if ($project_id)
	{
	  $sql .= " \n AND project_id = '".$project_id."'";
	}

	if ($company_id)
	{
	  $sql .= " \n AND project_company = '".$company_id."'";
	}

	if ($canal_id)
	{ 
	 $sql .= " \n AND project_canal = '".$canal_id."'";
	}

	$sql .= " ORDER BY company_name";

	//echo "<pre>$sql</pre>";

	$logs = db_loadList( $sql );
	//$cant = mysql_num_rows($logs); 

	echo db_error();
    
?>
<br><table width="95%" align="center" cellspacing="1" cellpadding="4" border="0" class="tbl">
	<tr>
		<th nowrap="nowrap"><?php echo $AppUI->_('Company');?>/<?php echo $AppUI->_('Canal');?></th>
		<th><?php echo $AppUI->_('Project');?></th>
		<th><?php echo $AppUI->_('Estimated hours');?></th>
		<th><?php echo $AppUI->_('Worked hours');?></th>
		<th><?php echo $AppUI->_('Estimated HHRR Budget');?></th>
		<th><?php echo $AppUI->_('Current HHRR Budget');?></th>
        <th><?php echo $AppUI->_('Estimated Expenses');?></th>
		<th><?php echo $AppUI->_('Actual Expenses');?></th>
		<th><?php echo $AppUI->_('Estimated Total Budget');?></th>
		<th><?php echo $AppUI->_('Current Total Budget');?></th>
	</tr>
<?php
	$suma1 = 0;
    $suma2 = 0;
	$suma3 = 0;
	$suma4 = 0;
	$suma5 = 0;
	$suma6 = 0;
	$suma7 = 0;
	$suma8 = 0;

	$pdfdata = array();
	$pdfdata[] = array(
		$AppUI->_('Company'),
		$AppUI->_('Project'),
		$AppUI->_('Estimated hours'),
		$AppUI->_('Worked hours'),
		$AppUI->_('Estimated HHRR Budget'),
		$AppUI->_('Current HHRR Budget'),
		$AppUI->_('Estimated Expenses'),
		$AppUI->_('Actual Expenses'),
        $AppUI->_('Estimated Total Budget'),
        $AppUI->_('Current Total Budget')
	);


foreach ($logs as $log) {
		
		// Traigo el nombre del canal
        $query2 = "SELECT company_name from companies WHERE company_id = '".$log[project_canal]."' ";
        $sql2 = mysql_query($query2);
		$resp = mysql_fetch_array($sql2);
		$canal = $resp[0];

        $estimated_hours = total_hours($log[project_id]);
        $worked_hours = CProject::getWorkedHours($log[project_id]);
		$estimated_hhrr = actual_rrhh_estimated_cost($log[project_id]);
		$current_hhrr = actual_rrhh_real_cost ($log[project_id]);
		$estimated_total = presupuesto_total_estimado($log[project_id],$log[project_other_estimated_cost]);
        $current_total = actual_budget ($log[project_id]);
		$estimated_expenses = $log[project_other_estimated_cost];
        $current_expenses = total_exp ($log[project_id]);

		$pdfdata[] = array(
			"".$log['company_name']."/".$canal."",
			$log['project_name'],
			$estimated_hours,
			$worked_hours,
			$estimated_hhrr,
			$current_hhrr,
			$estimated_total,
            $current_total
		);

	$suma1 = $estimated_hours + $suma1;
	$suma2 = $worked_hours + $suma2;
	$suma3 = $estimated_hhrr + $suma3;
	$suma4 = $current_hhrr + $suma4;
	$suma5 = $estimated_total + $suma5;
	$suma6 = $current_total + $suma6;
	$suma7 = $estimated_expenses + $suma7;
	$suma8 = $current_expenses + $suma8;
?>
	<tr>
		<td align="right"><?php echo $log['company_name'];?> / <? echo $canal; ?></td>
		<td align="right"><?php echo $log['project_name'];?>
		</td>
		<td align="right"><?php echo $estimated_hours;?></td>
		<td align="right" ><?php echo $worked_hours;?></td>
		<td align="right"><?php echo $estimated_hhrr;?></td>
		<td align="right"><?php echo $current_hhrr;?></td>
		<td align="right"><?php echo $estimated_expenses;?></td>
		<td align="right"><?php echo $current_expenses;?></td>
		<td align="right"><?php echo $estimated_total;?></td>
		<td align="right"><?php echo $current_total;?></td>
	</tr>
<?php
	}
	$pdfdata[] = array(
		'',
		'',
		$AppUI->_('Totals').':',
		$suma1,
		$suma2,
		sprintf( "%.2f",$suma3 ),
		sprintf( "%.2f",$suma4 ),
		sprintf( "%.2f",$suma5 ),
		sprintf( "%.2f",$suma6 ),
	);
?>
	<tr>
		<td align="right" colspan="2"><b><?php echo $AppUI->_('Totals');?>:</b></td>
		<td align="right"><b><?php echo $suma1;?></b></td>
		<td align="right"><b><?php echo $suma2;?></b></td>
		<td align="right"><b><?php printf( "%.2f", $suma3 );?></b></td>
		<td align="right"><b><?php printf( "%.2f", $suma4 );?></b></td>
		<td align="right"><b><?php printf( "%.2f", $suma7 );?></b></td>
		<td align="right"><b><?php printf( "%.2f", $suma8 );?></b></td>
		<td align="right"><b><?php printf( "%.2f", $suma5 );?></b></td>
		<td align="right"><b><?php printf( "%.2f", $suma6 );?></b></td>
	</tr>
	</table>
<?php
	if ($log_pdf) {
	// make the PDF file

		$font_dir = $AppUI->getConfig( 'root_dir' )."/lib/ezpdf/fonts";
		$temp_dir = $AppUI->getConfig( 'root_dir' )."/files/temp";
		$base_url  = $AppUI->getConfig( 'base_url' );
		require( $AppUI->getLibraryClass( 'ezpdf/class.ezpdf' ) );

		$pdf =& new Cezpdf();
		$pdf->ezSetCmMargins( 1, 2, 1.5, 1.5 );
		$pdf->selectFont( "$font_dir/Helvetica.afm" );
        

		$date = new CDate();
		$pdf->ezText( "\n" . $date->format( $df ) , 8 );

		$pdf->selectFont( "$font_dir/Helvetica-Bold.afm" );
		$pdf->ezText( "\n Reporte por " . $AppUI->_('project_type'), 12 );

        if ($company_id){
		$pdf->ezText( $log['company_name'], 15 );
        }

		if (!$company_id) {
			$pdf->ezText( $AppUI->_("All Projects"), 9 );
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