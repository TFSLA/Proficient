<?
$hhrr_dev_pf_id  = isset($_POST['hhrr_dev_pf_id'])? $_POST['hhrr_dev_pf_id'] : "";

?>

<table cellspacing="1" cellpadding="0" border="0" width="100%">
<form name="addedit_dev_pf" action="" method="POST">
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
    $sql = "SELECT hhrr_dev_pf_id,hhrr_dev_pf_action, DATE_FORMAT(hhrr_dev_pf_date,'%d-%m-%Y') as hhrr_dev_pf_date,hhrr_dev_pf_coment,hhrr_dev_pf_aproved,hhrr_dev_pf_status FROM hhrr_dev_pf WHERE hhrr_dev_pf_user_id = $id order by hhrr_dev_pf_date desc LIMIT 3";
    //echo "<br>$sql<br>";
  	$rc = db_exec($sql);
	 	while ($vec = db_fetch_array($rc)){
  ?>
   <tr>
		<td width="16" bgcolor="#ffffff">	   
		</td>		   	
		<td width="16" bgcolor="#ffffff">	   
		</td>

		<td align='center' bgcolor="#ffffff" ><?=$vec['hhrr_dev_pf_action']?></td>
		<td align='center' bgcolor="#ffffff" ><?=$vec['hhrr_dev_pf_date']?></td>
		<td align='center' bgcolor="#ffffff" ><?=$vec['hhrr_dev_pf_coment']?></td>
		<td align='center' bgcolor="#ffffff" ><?=($vec['hhrr_dev_pf_aproved']==1) ? $AppUI->_('Yes') :$AppUI->_('No')?></td>			
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
	 
		<table cellspacing="1" cellpadding="0" border="0" width="100%">
			<tr>
				<td>
				</td>
			</tr>
		</table>

	</tr>

<? if (!$print)
{?>
  <tr>
		<td align="right" colspan='5'>					
			<? $see_more = "index.php?m=hhrr&a=viewhhrr&tab=6&id=".$id; ?>
 			<a href="<?= $see_more; ?>"> <?=$AppUI->_( 'See more' );?></a>
 			<?
 			if (validar_permisos_hhrr($id,'development',-1))
			{
				$edit_hrf = "index.php?m=hhrr&a=addedit&tab=6&id=".$id;?>
				&nbsp;&nbsp;
				<a href="<?= $edit_hrf; ?>"> <?=$AppUI->_( 'edit' );?></a>
			<?
			}?>
		</td>	
	</form>
	</tr> 
<?
}?>	
</table>