<?
global $AppUI;

$id = isset($_GET['id']) ? $_GET['id'] : 0;


if(!$id){
	$AppUI->redirect("m=hhrr&a=addedit&tab=0");
}

$df = $AppUI->getPref('SHDATEFORMAT');

?>

<table id='table_compensations' cellspacing="0" cellpadding="0" border="0" width="100%" >
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
    $sql = "SELECT
				  CONCAT(SUBSTRING(h1.hhrr_comp_last_update,7,2),'-',SUBSTRING(h1.hhrr_comp_last_update,5,2),'-',SUBSTRING(h1.hhrr_comp_last_update,3,2)) AS date,
				  h1.hhrr_comp_remuneration,
				  h1.hhrr_comp_id,
				  h1.hhrr_comp_user_id,
				  h1.hhrr_comp_remuneration,
				  max(h2.hhrr_comp_last_update_date) AS vfecha,
				  IF (h2.hhrr_comp_remuneration<>0,CONCAT(ROUND((h1.hhrr_comp_remuneration/h2.hhrr_comp_remuneration*100)-100),'%'),'N/A') AS hhrr_comp_last_update_porc,
				  h1.hhrr_comp_last_update_date,
				  CONCAT(SUBSTRING(h1.hhrr_comp_last_update_date,9,2),'-',SUBSTRING(h1.hhrr_comp_last_update_date,6,2),'-',SUBSTRING(h1.hhrr_comp_last_update_date,3,2)) AS hhrr_comp_last_update_date,
				  h1.hhrr_comp_gap_pc,
				  h1.hhrr_comp_last_reward,
				  (h1.hhrr_comp_remuneration*13+h1.hhrr_comp_remuneration*h1.hhrr_comp_last_reward) AS hhrr_comp_anual_remuneration,
				  h1.hhrr_comp_actual_benefits,
				  h1.hhrr_comp_gap_mer,
				  h1.hhrr_comp_proposed_plan
				FROM hhrr_comp AS h1
				LEFT JOIN hhrr_comp AS h2
				  ON (
				    h1.hhrr_comp_user_id=h2.hhrr_comp_user_id AND
				    h1.hhrr_comp_last_update_date > h2.hhrr_comp_last_update_date)
				WHERE h1.hhrr_comp_user_id ='$id'
				GROUP BY h1.hhrr_comp_remuneration, h1.hhrr_comp_last_update_date
				ORDER BY h1.hhrr_comp_last_update_date DESC, h2.hhrr_comp_last_update_date DESC LIMIT 3";
   //echo "<br>$sql<br>";
  	$rc = db_exec($sql);
	while ($vec = db_fetch_array($rc)){
   ?>
	<tr>
		<td align='center' bgcolor="#ffffff" nowrap><?echo $vec['date'];?></td>
		<td align='center'  bgcolor="#ffffff" >
				<input type="hidden" value="<?=$vec['hhrr_comp_remuneration']?>" />
				<?=$vec['hhrr_comp_remuneration']?>
		</td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_last_update_porc'];?></td>
		<td align='center' bgcolor="#ffffff" nowrap><?echo $vec['hhrr_comp_last_update_date'];?></td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_gap_pc'];?></td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_last_reward'];?></td>			
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_anual_remuneration'];?></td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_actual_benefits'];?></td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_gap_mer'];?></td>
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
		<br><br>
     </td>
   </tr>
 


<? if (!$print)
{?>
<tr>
	<td align="right" colspan="5">
		<? $see_more_comp = "index.php?m=hhrr&a=viewhhrr&tab=5&id=".$id;?>
		<!--<input type="button" value="<?php echo $AppUI->_( 'See more' );?>" class="button" onClick="javascript:window.location='<?=$see_more_comp;?>';" />!-->
		<a href="<?= $see_more_comp; ?>"> <?=$AppUI->_( 'See more' );?></a>
		<?
		if (validar_permisos_hhrr($id,'compensations',-1))
		{				
			$edit_hrf = "index.php?m=hhrr&a=addedit&tab=5&id=".$id;
			?>
			&nbsp;&nbsp;&nbsp;
			<!--<input type="button" value="<?php echo $AppUI->_( 'edit' );?>" class="button" onClick="javascript:window.location='<?=$edit_hrf;?>';" />!-->
			<a href="<?= $edit_hrf; ?>"> <?=$AppUI->_( 'edit' );?></a>
		<? 
		}?>
	</td>
</tr>
<?
}?>
</table>