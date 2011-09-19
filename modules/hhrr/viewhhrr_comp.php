<?
global $AppUI, $tabBox;
$id = isset($_GET['id']) ? $_GET['id'] : 0;
if(!$id)
	$AppUI->redirect("m=hhrr&a=addedit&tab=0");
	
$tab = $_GET['tab'];

$canReadHHRR = !getDenyRead("hhrr");

// si el usuario logueado no puede leer hhrr
if (!$canReadHHRR OR !validar_permisos_hhrr($id,'compensations',1))
	 $AppUI->redirect( "m=public&a=access_denied" );


$df = $AppUI->getPref('SHDATEFORMAT');

if (isset($_POST['ant_id'])) { $ant_id = $_POST['ant_id']; }

switch($_POST[accion]) 
{   
	case "delant":
	  
	 $del_query = "DELETE FROM hhrr_education WHERE id = '$ant_id' ";
	 $sql_del = mysql_query($del_query);
     
	break;
}

?>

<script language="javascript">
<?="<!--";?>

var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.semFrm.' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.semFrm.log_' + calendarField );
	fld_fdate = eval( 'document.semFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function submit_edit(obj){
   var f = document.editFrm;

   f.ant_id.value = obj;

   f.submit();
}

function confirma(obj){
  
   var f = document.delFrm;
   f.ant_id.value = obj;

   var borrar=confirm('Do you want to delete this?\n\n');

    if (borrar)
	{
    f.submit();
	}


}

//-->
</script>

<form name="editFrm" action="" method="POST">
	<input type="hidden" name="ant_id" value="" />
</form>

<form name="delFrm" action="" method="POST">
	<input type="hidden" name="ant_id" value="" />
	<input type="hidden" name="accion" value="delant" />
</form>


<table cellspacing="0" cellpadding="0" border="0" width="100%" class="tableForm_bg">
   <tr>
     <td >
     <table cellspacing="0" cellpadding="0" border="0" width="100%" >
   <tr>
     <td >
		<table cellspacing="1" cellpadding="2" border="0" width="100%"
			class="tableForm_bg">
			<tr class="tableHeaderGral">
				<th width="1%"></th>
				<th align="center" width='1%'><?=$AppUI->_("actualmonthremuneration")?></th>
				<th align="center" width='1%'><?=$AppUI->_("porcentuallastupdate")?></th>
				<th align="center" width='15%'><?=$AppUI->_("lastuptdate")?></th>
				<th align="center" width='1%'><?=$AppUI->_("gaptoppcactual")?></th>
				<th align="center" width='1%'><?=$AppUI->_("lastreward")?></th>
				<th align="center" width='1%'><?=$AppUI->_("anualremuneration")?></th>
				<th align="center" width='15%'><?=$AppUI->_("actualbenefits")?></th>
				<th align="center" width='1%'><?=$AppUI->_("marketgap")?></th>
				<th align="center" width='30%'><?=$AppUI->_("proposedplan")?></th>
			</tr>
				<?
  	$cont=0;
    $sql = "SELECT
				  CONCAT(SUBSTRING(h1.hhrr_comp_last_update,7,2),'-',SUBSTRING(h1.hhrr_comp_last_update,5,2),'-',SUBSTRING(h1.hhrr_comp_last_update,3,2)) AS date,
				  h1.hhrr_comp_remuneration,
				  h1.hhrr_comp_id,
				  h1.hhrr_comp_user_id,
				  max(h2.hhrr_comp_last_update_date) AS vfecha,
				  IF (h2.hhrr_comp_remuneration<>0,CONCAT(ROUND((h1.hhrr_comp_remuneration/max(h2.hhrr_comp_remuneration)*100)-100),'%'),'N/A') AS hhrr_comp_last_update_porc,
				  h1.hhrr_comp_last_update_date,
				  CONCAT(SUBSTRING(h1.hhrr_comp_last_update_date,9,2),'-',SUBSTRING(h1.hhrr_comp_last_update_date,6,2),'-',SUBSTRING(h1.hhrr_comp_last_update_date,3,2)) AS hhrr_comp_last_update_date,
				  CONCAT(SUBSTRING(h1.hhrr_comp_last_update_date,9,2),'/',SUBSTRING(h1.hhrr_comp_last_update_date,6,2),'/',SUBSTRING(h1.hhrr_comp_last_update_date,1,4)) AS fecha_completa,
				  h1.hhrr_comp_gap_pc,
				  h1.hhrr_comp_last_reward,
				  (h1.hhrr_comp_remuneration*13+h1.hhrr_comp_last_reward) AS hhrr_comp_anual_remuneration,
				  h1.hhrr_comp_actual_benefits,
				  h1.hhrr_comp_gap_mer,
				  h1.Hhrr_comp_proposed_plan
				FROM hhrr_comp AS h1
				LEFT JOIN hhrr_comp AS h2
				  ON (
				    h1.hhrr_comp_user_id=h2.hhrr_comp_user_id AND
				    h1.hhrr_comp_last_update_date > h2.hhrr_comp_last_update_date)
				WHERE h1.hhrr_comp_user_id ='$id'
				GROUP BY h1.hhrr_comp_remuneration, h1.hhrr_comp_last_update_date
				ORDER BY h1.hhrr_comp_last_update_date DESC, h2.hhrr_comp_last_update_date DESC";
   
    //echo "<pre>$sql</pre>";
  	$rc = db_exec($sql);
	while ($vec = db_fetch_array($rc)){
   ?>
	<tr>
		<td align='center' bgcolor="#ffffff" nowrap><?echo $vec['date'];?></td>
		<td align='center'  bgcolor="#ffffff" >
				$<input type="hidden" name="comp_remuneration_<?=$cont; $cont++;?>" value="<?=$vec['hhrr_comp_remuneration']?>" />
				<?=$vec['hhrr_comp_remuneration']?>
		</td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_last_update_porc'];?></td>
		<td align='center' bgcolor="#ffffff" nowrap><?echo $vec['hhrr_comp_last_update_date'];?></td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_gap_pc'];?>%</td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_last_reward'];?></td>			
		<td align='center' bgcolor="#ffffff">$ <?echo $vec['hhrr_comp_anual_remuneration'];?></td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_actual_benefits'];?></td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_gap_mer'];?>%</td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_proposed_plan'];?></td>
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

	  ?><td>
	</tr>
		</table>
     </td>
   </tr>
   <tr>
		<td colspan="5" align="right">
			<table  border="0" cellspacing="5">
				<tr>
					<td>
						<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
					</td>
					<?
					if (validar_permisos_hhrr($id,'compensations',-1))
					{?>
					<td align="center">
						<?
							$edit_hrf = "index.php?m=hhrr&a=addedit&tab=$tab&id=".$id;
						?>
						<input type="button" value="<?php echo $AppUI->_( 'edit' );?>" class="button" onClick="javascript:window.location='<?=$edit_hrf;?>';" />
					</td>
					<? }?>
				</tr>
			</table>
		</td>
	</tr>
</table>

