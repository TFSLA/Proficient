<?php /* TASKS $Id: viewgantt.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */

GLOBAL $min_view, $m, $a;
$min_view = defVal( @$min_view, false);

$project_id = defVal( @$_GET['project_id'], 0);

// sdate and edate passed as unix time stamps
$sdate = dPgetParam( $_POST, 'sdate', 0 );
$edate = dPgetParam( $_POST, 'edate', 0 );

// months to scroll
$scroll_date = 1;

$display_option = dPgetParam( $_POST, 'display_option', 'all' );

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

if ($display_option == 'custom') {
	// custom dates
	$start_date = intval( $sdate ) ? new CDate( $sdate ) : new CDate();
	$end_date = intval( $edate ) ? new CDate( $edate ) : new CDate();
} else {
	// month
	$start_date = new CDate();
	$end_date = clone($start_date);
	$end_date->addMonths( $scroll_date );
}

// setup the title block
if (!@$min_view) {
	//$titleBlock = new CTitleBlock( 'Gantt Chart', 'applet-48.png', $m, "$m.$a" );
	$titleBlock = new CTitleBlock( 'Gantt Chart', 'tasks.gif', $m, "projects.index" );
	
	if($suppressLogo!=1){
	   $titleBlock->addCrumb( "?m=tasks", "tasks list" );
	   $titleBlock->addCrumb( "?m=projects&a=view&project_id=$project_id&dialog=1&suppressLogo=1", "view this project" );
	}
	
	$titleBlock->show();
}
?>
<script language="javascript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.' + calendarField );
	fld_fdate = eval( 'document.editFrm.show_' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function scrollPrev() {
	f = document.editFrm;
<?php
	$new_start = clone($start_date);
	$new_end = clone($end_date);
	$new_start->addMonths( -$scroll_date );
	$new_end->addMonths( -$scroll_date );
	echo "f.sdate.value='".$new_start->format( FMT_TIMESTAMP_DATE )."';";
	echo "f.edate.value='".$new_end->format( FMT_TIMESTAMP_DATE )."';";
?>
	document.editFrm.display_option.value = 'custom';
	f.submit()
}

function scrollNext() {
	f = document.editFrm;
<?php
	$new_start = clone($start_date);
	$new_end = clone($end_date);
	$new_start->addMonths( $scroll_date );
	$new_end->addMonths( $scroll_date );
	echo "f.sdate.value='" . $new_start->format( FMT_TIMESTAMP_DATE ) . "';";
	echo "f.edate.value='" . $new_end->format( FMT_TIMESTAMP_DATE ) . "';";
?>
	document.editFrm.display_option.value = 'custom';
	f.submit()
}

function showThisMonth() {
	document.editFrm.display_option.value = "this_month";
	document.editFrm.submit();
}

function showFullProject() {
	document.editFrm.display_option.value = "all";
	document.editFrm.submit();
}



function preloadImages() { //v3.0
  var d=document; 
  if(d.images){ 
  	if(!d.MM_p) 
  		d.MM_p=new Array();
  		
    var i,j=d.MM_p.length,a=preloadImages.arguments; 
    for(i=0; i<a.length; i++)
    	if (a[i].indexOf("#")!=0){ 
    		d.MM_p[j]=new Image; 
    		d.MM_p[j++].src=a[i];
    	}
    }
}

function findObj(n, d) { //v4.01
  var p,i,x;  
  if(!d) d=document; 
  if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);
  }
  if(!(x=d[n])&&d.all) 
  	x=d.all[n]; 
  	
  for (i=0;!x&&i<d.forms.length;i++) 
  	x=d.forms[i][n];
  
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) 
  	x=findObj(n,d.layers[i].document);
  
  if(!x && d.getElementById) 
  	x=d.getElementById(n); 
  	
  return x;
}

function swapImage() { //v3.0
  var i,j=0,x,a=swapImage.arguments; 
  document.MM_sr=new Array; 
  for(i=0;i<(a.length-2);i+=3)
   if ((x=findObj(a[i]))!=null){
   	document.MM_sr[j++]=x; 
   	if(!x.oSrc) 
   		x.oSrc=x.src; 
   	
   	x.src=a[i+2];
  }
}
</script>

<table width="100%" border="0" cellpadding="0" cellspacing="4" class="tableForm_bg">

<form name="editFrm" method="post" action="?<?php echo "m=$m&a=$a&project_id=$project_id&dialog=1&suppressLogo=1";?>">
<input type="hidden" name="display_option" value="<?php echo $display_option;?>" />
<input type="hidden" name="width_gantt" value="<?php echo $width_gantt;?>" >

<tr>
	<td align="left" valign="top" width="20">
<?php if ($display_option != "all") { ?>
	    <a href="javascript:scrollPrev()"> 
			<img src="./images/prev.gif" width="16" height="16" alt="<?php echo $AppUI->_( 'previous' );?>" border="0">
		</a>
<?php } ?>
	</td>

	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'From' );?>:</td>
	<td align="left" nowrap="nowrap">
		<input type="hidden" name="sdate" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
		<input type="text" class="text" name="show_sdate" value="<?php echo $start_date->format( $df );?>" size="12" disabled="disabled" />
		<a href="javascript:popCalendar('sdate')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a>
	</td>

	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'To' );?>:</td>
	<td align="left" nowrap="nowrap">
		<input type="hidden" name="edate" value="<?php echo $end_date->format( FMT_TIMESTAMP_DATE );?>" />
		<input type="text" class="text" name="show_edate" value="<?php echo $end_date->format( $df );?>" size="12" disabled="disabled" />
		<a href="javascript:popCalendar('edate')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a>

	<td align="left">
		<input type="button" class="button" value="<?php echo $AppUI->_( 'submit' );?>" onclick='document.editFrm.display_option.value="custom";submit();'>
	</td>

	<td align="right" valign="top" width="20">
<?php if ($display_option != "all") { ?>
	  <a href="javascript:scrollNext()">
	  	<img src="./images/next.gif" width="16" height="16" alt="<?php echo $AppUI->_( 'next' );?>" border="0">
	  </a>
<?php } ?>
	</td>
</tr>

</form>

<tr>
	<td align="center" valign="bottom" colspan="7">
	   <?php echo "<a href='javascript:showThisMonth()'>".$AppUI->_('show this month')."</a> : <a href='javascript:showFullProject()'>".$AppUI->_('show full project')."</a><br>"; ?>

	</td>
</tr>

</table>

<table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
<tr>

	<td valign="top">
<?php /*   intento de hacerlo como paneles separados el graficos y los labels */
/*
<?php if (db_loadResult( "SELECT COUNT(*) FROM tasks WHERE task_project=$project_id" )) { 
	$src =
	  "?m=tasks&a=gantt&suppressHeaders=1&project_id=$project_id" .
	  ( $display_option == 'all' ? '' :
		'&start_date=' . $start_date->format( "%Y-%m-%d" ) . '&end_date=' . $end_date->format( "%Y-%m-%d" ) ) .
	  "&width=' + ((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth)*0.95) + '";
	$src1 =
	  "?m=tasks&a=gantt&suppressHeaders=1&project_id=$project_id" .
	  ( $display_option == 'all' ? '' :
		'&start_date=' . $start_date->format( "%Y-%m-%d" ) . '&end_date=' . $end_date->format( "%Y-%m-%d" ) );	  
	$imglabels = "<script>document.write('<img src=\"".$src1."&gantttype=labels"."\">');</script>";  	
	$imggantt = "<script>document.write('<img src=\"".$src1."&gantttype=gantt"."\">');</script>";  
	
	?>
			<div style="overflow: hidden; width: 223px; padding:0px; margin: 0px">
			<table  border="0" cellpadding="0" cellspacing="0"><tr><td>
			<?php //echo $imglabels; 
				echo "<img src=\"".$src1."&gantttype=labels"."\">";
				?>
			
			</td></tr></table>
			</div>
	</td>
	<td width="90%">
	          
			<div id="ganttdiv" style="overflow: auto;  padding:0px; margin: 0px">
			<table width="98%" border="0" cellpadding="0" cellspacing="0">
			<tr><td width="100%">	<img name="ganttchart" border="0">
			<?php //echo $imggantt;  
				$ganttchartfile = $AppUI->cfg['base_url']."/".$src1."&gantttype=gantt";
				$imgGantt = file_get_contents($ganttchartfile);
				switch ($uistyle)
				{
				case "classic":
					$menumargin = "170";
					break;
				case "silver":
					$menumargin = "140";
					break;
				default:
					$menumargin = "0";
					break;			
				}
				echo "<script language=\"javascript\"><!--
							
							var imgGantt = document.images['ganttchart'];
				
							preloadImages(\"".$src1."&gantttype=gantt"."\");
							swapImage('ganttchart','',\"".$src1."&gantttype=gantt"."\",1)
				
							var gwidth = ((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth));
							gwidth = gwidth - 260 - $menumargin;
							document.getElementById('ganttdiv').style.width = gwidth.toString() + 'px';
				
							
							function iscomplete(){
							
								alert(imgGantt.complete);
							}
							
							// --></script>";
				?>
			</td></tr>
			</table>
		</div>	
	<?php
} else {
	echo $AppUI->_( "No tasks to display" );
}
*/?>



<?php
/*if (db_loadResult( "SELECT COUNT(*) FROM tasks WHERE task_project=$project_id" )) {?>
		<div id="ganttdiv" style="overflow: auto;  padding:0px; margin: 0px">
				<table width="98%" border="0" cellpadding="0" cellspacing="0">
					<tr><td width="100%">


<?php
	switch ($uistyle)
	{
	case "classic":
		$menumargin = "170";
		break;
	case "silver":
		$menumargin = "140";
		break;
	default:
		$menumargin = "0";
		break;			
	}

	$src =
	  "?m=tasks&a=gantt&suppressHeaders=1&project_id=$project_id" .
	  ( $display_option == 'all' ? '' :
		'&start_date=' . $start_date->format( "%Y-%m-%d" ) . '&end_date=' . $end_date->format( "%Y-%m-%d" ) ) .
	  "&width=' + (((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth)-$menumargin)*0.95) + '";

	echo "<script language=\"javascript\"><!--
				var gwidth = ((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth));
				gwidth = gwidth - 40 -$menumargin;
				document.getElementById('ganttdiv').style.width = gwidth.toString() + 'px';
	
				document.write('<img src=\"$src\">');
				// --></script>";*/
if (db_loadResult( "SELECT COUNT(*) FROM tasks WHERE task_project=$project_id" )) {
	switch ($uistyle)
	{
	case "classic":
		$menumargin = "1900";
		break;
	case "silver":
		$menumargin = "160";
		break;
	default:
		$menumargin = "0";
		break;			
	}	
        
/*  $src =
	  "?m=tasks&a=gantt&suppressHeaders=1&project_id=$project_id" .
	  ( $display_option == 'all' ? '' :
		'&start_date=' . $start_date->format( "%Y-%m-%d" ) . '&end_date=' . $end_date->format( "%Y-%m-%d" ) ) .
	  "&width=' + (((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth)-$menumargin)*0.95) + '";


	echo '<div id="ganttdiv" style="overflow: auto; width: 100%; padding:0px; margin: 0px">';

	echo "<img id=\"ganttchart\" src=\"$src\" >";

	$src .=
	  "&width=' + (((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth)-$menumargin)*0.95) + '";
	echo "</div>";
	
	echo 	"<script language=\"javascript\">
				var gwidth = ((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth));
				gwidth = gwidth - 20 - $menumargin;
                  
				document.getElementById('ganttdiv').style.width = gwidth.toString() + 'px';
				var ganttimg = new Image();
				ganttimg.src = '$src';
				document.getElementById('ganttchart').src = ganttimg.src;
				</script>";
     echo "$src";*/
    


	if($display_option != 'all')
	{
	$_GET[start_date] = $start_date->format( "%Y-%m-%d" );
    $_GET[end_date] = $end_date->format( "%Y-%m-%d" );
	}
	
	$_GET[width] = 800;
    $project_id = $_GET['project_id'];

	$tmp_gantt = uniqid("jp");
    include($AppUI->getConfig('root_dir').'/modules/tasks/gantt.php');


} else {
	echo $AppUI->_( "No tasks to display" );
}

?>
	</td>
</tr>
</table>

<br />
