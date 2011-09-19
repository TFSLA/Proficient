<?
$hhrr_dev_pf_id  = isset($_POST['hhrr_dev_pf_id'])? $_POST['hhrr_dev_pf_id'] : "";

switch($_POST[accion]) 
{   
	//Si estan agregando editando algun registro de la tabla hhrr_dev_pf
	case "addedit_dev_pf":
	
	if ( $hhrr_dev_pf_id != "" )
	{
	  $update = "UPDATE hhrr_dev_pf SET 
	  hhrr_dev_pf_action = '".$_POST['hhrr_dev_pf_action']."',
	  hhrr_dev_pf_date = '".$_POST['log_from_date']."',
	  hhrr_dev_pf_coment = '".$_POST['hhrr_dev_pf_coment']."',
	  hhrr_dev_pf_aproved = '".$_POST['hhrr_dev_pf_aproved']."',
	  hhrr_dev_pf_status = '".$_POST['hhrr_dev_pf_status']."'
	  WHERE hhrr_dev_pf_id = '$hhrr_dev_pf_id'; ";
		  
		$sql = db_exec($update);
		//echo $update;
		unset($hhrr_dev_pf_id);
	}
	else
	{
    $sql = "INSERT INTO hhrr_dev_pf 
    	(hhrr_dev_pf_user_id, hhrr_dev_pf_action, hhrr_dev_pf_date, hhrr_dev_pf_coment, hhrr_dev_pf_aproved, hhrr_dev_pf_status)
    	VALUES ('".$id."', '".$_POST['hhrr_dev_pf_action']."', '".$_POST['log_from_date']."', '".$_POST['hhrr_dev_pf_coment']."', '".$_POST['hhrr_dev_pf_aproved']."', '".$_POST['hhrr_dev_pf_status']."');";
    db_exec($sql);
    //echo $sql;
	}
	unset($_POST);
	unset($hhrr_dev_pf_id);
	break;
	
	
	case "del_dev_pf":
	  
	$del_query = "DELETE FROM hhrr_dev_pf WHERE hhrr_dev_pf_id = '$hhrr_dev_pf_id'; ";
	$sql_del = db_exec($del_query);
	//echo $del_query;   
	unset($_POST);
	unset($hhrr_dev_pf_id);
	
	break;
}
?>

<table cellspacing="1" cellpadding="0" border="0" width="100%">
<form name="addedit_dev_pf" action="" method="POST" >
	<input type="hidden" name="hhrr_dev_pf_id" value="<?=$hhrr_dev_pf_id;?>" />
	<input type="hidden" name="accion" value="" />
	<tr class="tableHeaderGral" >
		<th align="center" colspan="7"><?=$AppUI->_("Individual Development Plan")?></th>
	</tr>
	<tr class="tableHeaderGral">
		<td align="center" width='1%'>&nbsp;</td>
		<td align="center" width='1%'>&nbsp;</td>
  	<td align="center" width='20%'><?=$AppUI->_("Action")?></td>
		<td align="center" width='15%'><?=$AppUI->_("Date")?></td>
		<td align="center" width='40%'><?=$AppUI->_("Comments")?></td>
		<td align="center" width='1%'><?=$AppUI->_("Approved")?></td>
		<td align="center" width='1%'><?=$AppUI->_("Status")?></td>
	</tr>

	<?
    $sql = "SELECT hhrr_dev_pf_id,hhrr_dev_pf_action, DATE_FORMAT(hhrr_dev_pf_date,'%d-%m-%Y') as hhrr_dev_pf_date,hhrr_dev_pf_coment,hhrr_dev_pf_aproved,hhrr_dev_pf_status FROM hhrr_dev_pf WHERE hhrr_dev_pf_user_id = $id";
    //echo "<br>$sql<br>";
  	$rc = db_exec($sql);
	 	while ($vec = db_fetch_array($rc)){
  ?>
   <tr>
		<td width="16" bgcolor="#ffffff">	   
			<a href="javascript:submit_edit(<?=$vec[hhrr_dev_pf_id]?>);"><img src="./images/icons/edit_small.gif" alt="<?=$AppUI->_("Edit")?>" border="0"></a>
		</td>		   	
		<td width="16" bgcolor="#ffffff">	   
			<a href="JavaScript:confirma(<?=$vec[hhrr_dev_pf_id]?>)"><img src='./images/icons/trash_small.gif' alt='<?=$AppUI->_("Delete")?>' border='0'></a>
		</td>

		<td align='center' bgcolor="#ffffff" ><?=$vec['hhrr_dev_pf_action']?></td>
		<td align='center' bgcolor="#ffffff" ><?=$vec['hhrr_dev_pf_date']?></td>
		<td align='center' bgcolor="#ffffff" ><?=$vec['hhrr_dev_pf_coment']?></td>
		<td align='center' bgcolor="#ffffff" ><?= ($vec['hhrr_dev_pf_aproved']==1) ? $AppUI->_('Yes') :$AppUI->_('No');?>
		</td>			
		<td align='center' bgcolor="#ffffff" ><?=$vec['hhrr_dev_pf_status']?></td>

   </tr>
<? } ?>
   <tr>
	  <td colspan="7" align="center">
	<? 
	  $cant = db_num_rows($rc); 
	  
	  if($cant =="0")
	  {
	   echo $AppUI->_('Noitems');
	  }

	  ?>
	  <td>
	</tr>
	


	<? 
  if($hhrr_dev_pf_id !="")
  {
	  $sql = "SELECT * FROM hhrr_dev_pf where hhrr_dev_pf_id = '$hhrr_dev_pf_id' ORDER BY hhrr_dev_pf_date DESC;";
	  $rc = db_exec($sql);
	  //echo "<br>$sql<br>";
	  $data = mysql_fetch_array($rc);

	  $hhrr_dev_pf_action = $data['hhrr_dev_pf_action'];
	  $from_date = new CDate($data['hhrr_dev_pf_date']);
	  $hhrr_dev_pf_coment = $data['hhrr_dev_pf_coment'];
	  $hhrr_dev_pf_aproved = $data['hhrr_dev_pf_aproved'];
	  $hhrr_dev_pf_status = $data['hhrr_dev_pf_status'];
	  
	  $name_button_send = $AppUI->_('submit');
	}
	else 
	{
		$from_date = new CDate($data[s_date]);
		$name_button_send = $AppUI->_('save');
	}
	  
   ?> 
   
   
   
	<tr>
		<td width="20">
	   		<img src="images/1x1.gif" width="20" height="1">
	  	</td>
	  	<td width="16">
	   		<img src="images/1x1.gif" width="16" height="1">
	  	</td>
		<td align='center'>
			<input type="text" size='25' name="hhrr_dev_pf_action" value="<?=$hhrr_dev_pf_action;?>" class="text">
		</td>
		<td align='center' nowrap>
			<input type='hidden' name='log_from_date' value='<?php echo $from_date ? $from_date->format( FMT_TIMESTAMP_DATE ) : '';?>' />
			<input type='text' name='from_date' value='<?php echo $from_date ? $from_date->format( $df ) : '';?>' class='text' disabled='disabled' size='10' />
			<input name="from_date_format" value="%d/%m/%Y" type="hidden">
			<a href='#' onClick="popCalendar('from_date', 'editform')"> <img src='./images/calendar.gif' width='24' height='12' alt='<?php echo $AppUI->_('Calendar');?>' border='0' /> </a>
		</td>
		<td align='center'>
		 	<input type="text" size='45' name='hhrr_dev_pf_coment' value="<?=$hhrr_dev_pf_coment;?>" class="text">
		</td>
		<td align='center'>
		 	<select name='hhrr_dev_pf_aproved'>
		 		<option value='0'><?php echo $AppUI->_('No'); ?></option>
		 		<option value='1'><?php echo $AppUI->_('Yes'); ?></option>
		 	</select>
		</td>
		<td align='center'>
		 	<input type="text" size='15' name='hhrr_dev_pf_status' value="<?=$hhrr_dev_pf_status;?>" class="text">
		</td>

		<table cellspacing="1" cellpadding="0" border="0" width="100%">
			<tr>
				<td>
				</td>
			</tr>
		</table>

	</tr>
   	<tr>
		<td align="right" colspan='5'>
			<input type="button" value="<?=$name_button_send;?>" class="button" onclick="submitIt()"></form>
			<?
			if($_GET[a]!="personalinfo"){ 
				$salir = "index.php?m=hhrr&a=viewhhrr&tab=$tab&id=".$id;?>
				<input type="button" value="<?php echo $AppUI->_( 'exit' );?>" class="button" onClick="javascript:window.location='<?=$salir;?>';" />
				<? 
			} 
		?>
		</td>	
	</tr> 
</table>

<script language="javascript">

function submitIt(){
	var f = document.addedit_dev_pf;
	
	var rta = true;
    
    var today = new Date();
    
    var vec_fecha = f.from_date.value.split("/");
    var from_date_txt = new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
    
   // if (from_date_txt > today){
   // 	alert("<?php echo $AppUI->_('DateErrorMayor');?>");
   // 	rta = false;
   // }
    
    
    if (rta){
    f.accion.value = "addedit_dev_pf";
	f.submit();
	}
	
}


function submit_edit(obj){
   var f = document.addedit_dev_pf;

   f.hhrr_dev_pf_id.value = obj;

   f.submit();
}

var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.addedit_dev_pf.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.addedit_dev_pf.log_' + calendarField );
	fld_fdate = eval( 'document.addedit_dev_pf.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function confirma(obj){
  
   var f = document.addedit_dev_pf;
   f.hhrr_dev_pf_id.value = obj;
	 f.accion.value='del_dev_pf';
	 
   var borrar=confirm('Do you want to delete this?\n\n');

    if (borrar)
	{
    f.submit();
	}
}
</script>