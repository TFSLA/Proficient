<?
$hhrr_dev_pf_id  = isset($_POST['hhrr_dev_pf_id'])? $_POST['hhrr_dev_pf_id'] : "";

?>

<table cellspacing="1" cellpadding="0" border="0" width="100%">
<form name="addedit_dev_pf" action="" method="POST">
	<input type="hidden" name="hhrr_dev_pf_id" value="<?=$hhrr_dev_pf_id;?>" />
	<input type="hidden" name="accion" value="" />
	<tr class="tableHeaderGral" >
		<th align="center" colspan="5"><?=$AppUI->_("Plan Desarrollo Individual")?></th>
	</tr>
	<tr class="tableHeaderGral">
 		<td align="center" width="20%"><?=$AppUI->_("Action")?></td>
		<td align="center" width="15%"><?=$AppUI->_("Date")?></td>
		<td align="center" width="40%"><?=$AppUI->_("Comments")?></td>
		<td align="center" width="10%"><?=$AppUI->_("Approved")?></td>
		<td align="center" width="10%"><?=$AppUI->_("Status")?></td>
	</tr>

	<?
    $sql = "SELECT hhrr_dev_pf_id,hhrr_dev_pf_action, DATE_FORMAT(hhrr_dev_pf_date,'%d-%m-%Y') as hhrr_dev_pf_date,hhrr_dev_pf_coment,hhrr_dev_pf_aproved,hhrr_dev_pf_status FROM hhrr_dev_pf WHERE hhrr_dev_pf_user_id = $id";
    //echo "<br>$sql<br>";
  	$rc = db_exec($sql);
	 	while ($vec = db_fetch_array($rc)){
  ?>
   <tr>
		<td align='center' bgcolor="#ffffff" ><?=$vec['hhrr_dev_pf_action']?></td>
		<td align='center' bgcolor="#ffffff" ><?=$vec['hhrr_dev_pf_date']?></td>
		<td align='center' bgcolor="#ffffff" ><?=$vec['hhrr_dev_pf_coment']?></td>
		<td align='center' bgcolor="#ffffff" ><?=($vec['hhrr_dev_pf_aproved']==1) ? $AppUI->_('Yes') :$AppUI->_('No')?></td>			
		<td align='center' bgcolor="#ffffff" ><?=$vec['hhrr_dev_pf_status']?></td>
   </tr>
<? }
		if ($rc){ ?>
   <tr>
	  <td colspan="5" align="center">
		<? 
	  $cant = db_num_rows($rc); 
	  if($cant =="0")
	  	echo $AppUI->_('Noitems');
	  ?>
	  <td>
	</tr>
	<?}?>
	 
	</form>
</table>