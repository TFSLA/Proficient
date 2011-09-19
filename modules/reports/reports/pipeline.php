<?php
/* ---------------------------------------------------------------------
   Reporte de seguimiento de ventas
  
   Posiiblidad de filtrar por usuario logeado, algun delegado o TODOS. 
   Sumarizar los totales. 
   Poder filtrar por rango de fecha  y probabilidad.
-----------------------------------------------------------------------*/

include_once("modules/projects/read_projects.inc.php");


$obj = new CCompany();
$companies = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$companies = arrayMerge( array( '0'=>$AppUI->_('All') ), $companies );

// Traigo los pipeline

$sql = "select id as pipeline_id, accountname from salespipeline order by accountname";
$pipeline = db_loadList( $sql );

// Traigo los gerentes de cuentas

$sql2 = "select s.accountmanager as user_id, u.user_username as user_username from salespipeline as s, users as u where s.accountmanager = u.user_id order by user_username ";
$users = db_loadList( $sql2 );

// Traigo el tipo de proyecto

$sql3 = "select distinct(projecttype) from salespipeline order by projecttype";
$p_type = db_loadList( $sql3 );

// Traigo la fuente de la oportunidad 
$sql3 = "select distinct(opportunitysource) from salespipeline";
$source = db_loadList( $sql3 );


error_reporting( E_ALL );
$do_report = dPgetParam( $_POST, "do_report", 0 );
$log_all = dPgetParam( $_POST, 'log_all', 0 );
$log_pdf = dPgetParam( $_POST, 'log_pdf', 0 );

$log_start_date = dPgetParam( $_POST, "log_start_date", 0 );
$log_end_date = dPgetParam( $_POST, "log_end_date", 0 );
$pipeline_id = dPgetParam( $_POST, "pipeline_id", 0 );
$probabilidad = dPgetParam( $_POST, "probabilidad", "" );
$user_id = dPgetParam( $_POST, "user_id", 0 );
$status = dPgetParam( $_POST, "status", "" );
$project_type = dPgetParam( $_POST, "project_type", "" );
$fuente = dPgetParam( $_POST, "fuente", "" );

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : new CDate();
$end_date = intval( $log_end_date ) ? new CDate( $log_end_date ) : new CDate();

if (!$log_start_date) {
	$start_date->subtractSpan( new Date_Span( "14,0,0,0" ) );
}
$end_date->setTime( 23, 59, 59 );

$extra = array();
$extra['from'] = '';
$extra['where'] ='and company_type=0';

$canal = $obj->getAllowedRecords( $AppUI->user_id, 'company_name', 'company_name',$index= null, $extra);
$canal = arrayMerge( array( '0'=>$AppUI->_('All') ), $canal );

?>

<script language="javascript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
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
<input type="hidden" name="pipeline_id" value="<?php echo $pipeline_id;?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<tr>
	<td align="left" nowrap="nowrap" colspan="8"><?php echo $AppUI->_('Account Name');?>:&nbsp;   
		<select name="pipeline_id" style="font-size:10px">
		<option value=""><?php echo $AppUI->_('All');?></option>
<?   
    
     foreach ($pipeline as $row) {
           
	      if ($pipeline_id==$row["pipeline_id"])
			{
			 $sel = "selected";
			}
			else{
		     $sel = "";
			}
?>        
		  <option  value="<?=$row["pipeline_id"]?>" <? echo $sel;?>><?=$row["accountname"]?></option>
<?}?>
		</select>&nbsp;&nbsp;&nbsp;
<?php echo $AppUI->_('Account Manager');?>:&nbsp;
		<select name="user_id" style="font-size:10px">
		  <option value=""><?php echo $AppUI->_('All');?></option>
<?if($user_id==null)$user_id="";?>
<?foreach ($users as $row) {
?>
		  <option <?if($row["user_id"]==$user_id)echo " selected ";?> value="<?=$row["user_id"]?>"><?=$row["user_username"]?></option> 
<?}?>
		</select>&nbsp;&nbsp;&nbsp;
<?php echo $AppUI->_('Probability of Winning');?>:&nbsp;
		<select name="probabilidad" style="font-size:10px">
		  <option value=""><?php echo $AppUI->_('All');?></option>  
		  <option value="probability < 10" <? if ($probabilidad=="probability < 10") echo "selected"; ?>>< 10 %</option>
		  <option value="probability >= 10 AND probability < 20" <? if ($probabilidad=="probability >= 10 AND probability < 20") echo "selected"; ?>>Entre 10 y 20 %</option>
		  <option value="probability >= 20 AND probability < 30" <? if ($probabilidad=="probability >= 20 AND probability < 30") echo "selected"; ?>>Entre 20 y 30 %</option>
		  <option value="probability >= 30 AND probability < 40" <? if ($probabilidad=="probability >= 30 AND probability < 40") echo "selected"; ?>>Entre 30 y 40 %</option>
		  <option value="probability >= 40 AND probability < 50" <? if ($probabilidad=="probability >= 40 AND probability < 50") echo "selected"; ?>>Entre 40 y 50 %</option>
		  <option value="probability >= 50 AND probability < 60" <? if ($probabilidad=="probability >= 50 AND probability < 60") echo "selected"; ?>>Entre 50 y 60 %</option>
		  <option value="probability >= 60 AND probability < 70" <? if ($probabilidad=="probability >= 60 AND probability < 70") echo "selected"; ?>>Entre 60 y 70 %</option>
		  <option value="probability >= 70 AND probability < 80" <? if ($probabilidad=="probability >= 70 AND probability < 80") echo "selected"; ?>>Entre 70 y 80 %</option>
          <option value="probability >= 80 AND probability < 90" <? if ($probabilidad=="probability >= 80 AND probability < 90") echo "selected"; ?>>Entre 80 y 90 %</option>
		  <option value="probability >= 90 AND probability <= 100" <? if ($probabilidad=="probability >= 90 AND probability <= 100") echo "selected"; ?>>Entre 80 y 90 %</option>
		</select>&nbsp;&nbsp;&nbsp;
</td>
</tr>
<tr>
    <td align="left" nowrap="nowrap" colspan="8"> 
	   <?php echo $AppUI->_('Status');?>:&nbsp;
	   <?  $status_list['Opportunity'] = $AppUI->_( 'Opportunity');
	         $status_list['On Hold'] = $AppUI->_( 'On Hold' );
	         $status_list['Negotiation'] =  $AppUI->_( 'Negotiation' );
	         $status_list['Decision'] = $AppUI->_( 'Decision' );
	         $status_list['Win'] = $AppUI->_( 'Win' );
	         $status_list['Loss'] = $AppUI->_( 'Loss' );
	        
	         natcasesort($status_list);
	   ?>
        <select name="status" style="font-size:10px">
		    <option value=""><?php echo $AppUI->_('All');?></option>  
		    
		    <?
		          foreach ($status_list as $ind=>$desc)
		          {
		          	  if($status==$ind){ $sel = "selected"; }else{ $sel = "";}
		          	  
		          	  echo "<option value =\"".$ind."\" $sel>$desc</option>";
		          }
		    ?>
           
        </select>&nbsp;&nbsp;&nbsp;
                       
		<?php echo $AppUI->_('Project type');?>:&nbsp;
		<select name="project_type" style="font-size:10px">
		<option value=""><?php echo $AppUI->_('All');?></option>
     <?   
     
     foreach ($p_type as $row) {
           
	      if ($project_type==$row["projecttype"])
			{
			 $sel = "selected";
			}
			else{
		     $sel = "";
			}
      ?>        
		  <option  value="<?=$row["projecttype"]?>" <? echo $sel;?>><?=$row["projecttype"]?></option>
    <?}?>
		</select>&nbsp;&nbsp;&nbsp;

		
	<?php echo $AppUI->_('Opportunity Source');?>:&nbsp;
	<? echo arraySelect( $canal, 'fuente', 'style="font-size:10px"', $fuente,TRUE , FALSE ); ?>

	</td>
</tr>
<tr>
	<td align="left" nowrap="nowrap">
      <?php echo $AppUI->_('From');?>:
		<input type="hidden" name="log_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
		<input type="text" name="start_date" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('start_date')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
		
	</td>
	<td align="left" nowrap="nowrap">
	<?php echo $AppUI->_('To');?>
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
	</td>

	<td align="left" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>
</form>
</table>

<?php
if ($do_report) {

	$sql = "SELECT accountname,opportunitysource,closingdate,probability,
	        totalincome,accountmanager, status, projecttype, user_username
	        FROM salespipeline 
	        LEFT JOIN users ON accountmanager= user_id
			WHERE 1=1
			";
						
	if (!$log_all) {
		$sql .= "\n	AND closingdate >= '".$start_date->format( FMT_DATETIME_MYSQL )."'"
		."\n	AND closingdate <= '".$end_date->format( FMT_DATETIME_MYSQL )."'";
	}

	if ($pipeline_id)
	{
	  $sql .= " \n AND id = '".$pipeline_id."'";
	}

	if ($user_id)
	{
	  $sql .= " \n AND accountmanager = '".$user_id."'";
	}

	if ($probabilidad)
	{ 
	 $sql .= " \n AND ".$probabilidad."";
	}

    if ($status)
	{ 
	  $sql .= " \n AND status = '".$status."'";
	}

	if ($project_type)
	{ 
	  $sql .= " \n AND projecttype = '".$project_type."'";
	}

	if ($fuente)
	{ 
	  $sql .= " \n AND opportunitysource = '".$fuente."'";
	}

	$sql .= " ORDER BY closingdate";

	echo "<pre>$sql</pre>";

	$logs = db_loadList( $sql );
	echo db_error();
?>
	<br><table width="95%" align="center" cellspacing="1" cellpadding="4" border="0" class="tbl">
	<tr>
		<th nowrap="nowrap"><?php echo $AppUI->_('Account Manager');?></th>
		<th><?php echo $AppUI->_('Account Name');?></th>
		<th><?php echo $AppUI->_('Opportunity Source');?></th>
		<th><?php echo $AppUI->_('Total Income');?></th>
		<th><?php echo $AppUI->_('Closing Date');?></th>
		<th><?php echo $AppUI->_('Probability of Winning');?></th>
		<th><?php echo $AppUI->_('Status');?></th>
		<th><?php echo $AppUI->_('Project type');?></th>
	</tr>
<?php
	$income = 0;
	$pdfdata = array();
	$pdfdata[] = array(
		$AppUI->_('Account Manager'),
		$AppUI->_('Account Name'),
		$AppUI->_('Opportunity Source'),
		$AppUI->_('Total Income'),
		$AppUI->_('Closing Date'),
		$AppUI->_('Probability of Winning'),
		$AppUI->_('Status'),
		$AppUI->_('Project type')
	);

	foreach ($logs as $log) {
		$date = new CDate( $log['closingdate'] );

		$pdfdata[] = array(
			$log['user_username'],
			$log['accountname'],
			$log['opportunitysource'],
			$log['totalincome'],
			$date->format( $df ),
			$log['probability'],
			$log['status'],
			$log['projecttype'],
		);

	$income = $log['totalincome'] + $income;
?>
	<tr>
		<td align="right"><?php echo $log['user_username'];?></td>
		<td align="right"><?php echo $log['accountname'];?>
		</td>
		<td align="right"><?php echo $log['opportunitysource'];?></td>
		<td align="right" ><?php printf( "%.2f", $log['totalincome'] );?></td>
		<td align="right"><?php echo $date->format( $df );?></td>
		<td align="right"><?php echo $log['probability']."&nbsp;%";?></td>
		<td align="right"><?php echo $log['status'];?></td>
		<td align="right"><?php echo $log['projecttype'];?></td>
	</tr>
<?php
	}
	$pdfdata[] = array(
		'',
		'',
		$AppUI->_('totalincome').':',
		sprintf( "%.2f", $income ),
		'',
		'',
		'',
		'',
	);
?>
	<tr>
		<td align="right" colspan="3"><?php echo $AppUI->_('totalincome');?>:</td>
		<td align="right"><?php printf( "%.2f", $income );?></td>
		<td align="right" colspan="4">&nbsp;</td>
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
        
		if (!$log_all){
		$pdf->ezText( $log['accountname'], 12 );
	    }

		$date = new CDate();
		$pdf->ezText( "\n" . $date->format( $df ) , 8 );

		$pdf->selectFont( "$font_dir/Helvetica-Bold.afm" );
		$pdf->ezText( "\n" . $AppUI->_('Pipeline Report'), 12 );

        if (!$log_all){
		$pdf->ezText( $log['accountname'], 15 );
        }

		if ($log_all) {
			$pdf->ezText( $AppUI->_("All pipelines"), 9 );
		} else {
			$pdf->ezText( $AppUI->_("Pipeline from")." ".$start_date->format( $df )." ".$AppUI->_("to")." ".$end_date->format( $df ), 9 );
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
