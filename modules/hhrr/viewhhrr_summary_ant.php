<?
global $AppUI;
$id = isset($_GET['id']) ? $_GET['id'] : 0;

if(!$id){
	$AppUI->redirect("m=hhrr&a=addedit&tab=0");
}

?>

<table id='table_work_experience' cellspacing="1" cellpadding="5" border="0" width="100%">
<form name="antFrmInt" action="" method="POST">
	<tr>
  	<td>
     <table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
	 <tr class="tableHeaderGral" >
      <th align="center" colspan="11"><?=$AppUI->_("Internal Companies")?></th>
	 </tr>
     <tr class="tableHeaderGral" >
		 <th align="left" width="100"><?=$AppUI->_("Company")?></th>
		 <th align="left" width="100"><?=$AppUI->_("Management/Area")?></th>
		 <th align="left" width="100"><?=$AppUI->_("Function")?></th>
		 <th align="left" nowrap="nowrap" width="50" ><?=$AppUI->_("From")?></th>
		 <th align="left" nowrap="nowrap" width="50" ><?=$AppUI->_("To")?></th>
		 <th align="left" width="100"><?=$AppUI->_("reports")?></th>
		 <th align="left" width="100"><?=$AppUI->_("functional_area")?></th>
		 <th align="left" width="100"><?=$AppUI->_("level_management")?></th>
   </tr>
   <?
		$query = "
		SELECT hhrr_ant.id, CONCAT_WS(' ',user_first_name,user_last_name) AS name, company_name, internal_company, dept_name, function, DATE_FORMAT(from_date,'%d-%m-%Y') as fdate ,DATE_FORMAT(to_date,'%d-%m-%Y') as tdate, profit, area_name, level_management
		FROM hhrr_ant
		LEFT JOIN companies ON companies.company_id=hhrr_ant.company
		LEFT JOIN departments ON departments.dept_id=hhrr_ant.area_internal
		LEFT JOIN users ON users.user_id=hhrr_ant.reports
		LEFT JOIN hhrr_functional_area ON hhrr_functional_area.id=hhrr_ant.functional_area
		WHERE hhrr_ant.user_id ='$id' AND internal_company = '1' order by from_date desc LIMIT 3
		";
	  $sql = db_exec($query);

	  while ($vec = db_fetch_array($sql)){
   ?>
	   <!-- Lista de antecedentes -->
	   <tr>
			 <td  bgcolor="#ffffff" width="100" ><?= $vec['company_name']; ?></td>
			 <td  bgcolor="#ffffff" width="100" ><?= $vec['dept_name'];?></td>
			 <td  bgcolor="#ffffff" width="100" ><?=$vec['function']?></td>
			 <td  bgcolor="#ffffff" nowrap="nowrap" width="70"><?=$vec['fdate']?></td>
			 <td  bgcolor="#ffffff" nowrap="nowrap" width="70"><?=$vec['tdate']?></td>
			 <td  bgcolor="#ffffff" width="100"><?= $vec['name'];?></td>
			 <td  bgcolor="#ffffff" width="100"><?=$vec['area_name'];?></td>
			 <td  bgcolor="#ffffff" width="100"><?=$vec['level_management']?></td>
	   </tr>
	   <tr>
	   	<td colspan='8' bgcolor="#ffffff">
	   		<textarea cols="120" rows="2" class="text" READONLY><?=$vec['profit']?></textarea>
	   	</td>
	   </tr>
			<tr>
				<td colspan="10" bgcolor="#e9e9e9" height="3"></td>
			</tr>
	   
   <? } ?>
     <tr>
		  <td colspan="7" align="center">
		  <? 
		  if(db_num_rows($sql)=="0")
		  {
		   echo $AppUI->_('Noitems');
		  }
		  ?>
		  <td>
		</tr>
	   
	 </form>
   </table>
		<br><br>

   <table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
   <form name="antFrmExt" action="" method="POST">
	 
	 <tr class="tableHeaderGral" >
      <th align="center" colspan="5"><?=$AppUI->_("Other Companies")?></th>
	 </tr>
   <tr class="tableHeaderGral" >
		 <th align="left" width="120"><?=$AppUI->_("Company")?></th>
		 <th align="left" width="100"><?=$AppUI->_("Management/Area")?></th>
		 <th align="left" width="100"><?=$AppUI->_("Function")?></th>
		 <th align="left" nowrap="nowrap" width="70" ><?=$AppUI->_("From")?></th>
		 <th align="left" nowrap="nowrap" width="70" ><?=$AppUI->_("To")?></th>
   </tr>
   <?
    $query = "SELECT id, user_id, company, internal_company , area_external, function, DATE_FORMAT(from_date,'%d-%m-%Y') as fdate ,DATE_FORMAT(to_date,'%d-%m-%Y') as tdate, profit, reports, functional_area, level_management FROM hhrr_ant WHERE user_id ='$id' AND internal_company = '0' order by from_date desc LIMIT 3";
	  $sql = db_exec($query);

	  while ($vec = db_fetch_array($sql)){
   ?>
	   <!-- Lista de antecedentes -->
	   <tr>
			 <td  bgcolor="#ffffff" width="120" ><?=$vec['company'];?></td>
			 <td  bgcolor="#ffffff" width="100" ><?=$vec['area_external']?></td>
			 <td  bgcolor="#ffffff" width="100" ><?=$vec['function']?></td>
			 <td  bgcolor="#ffffff" nowrap="nowrap" width="70"><?=$vec['fdate']?></td>
			 <td  bgcolor="#ffffff" nowrap="nowrap" width="70"><?=$vec['tdate']?></td>
	   </tr>
	   <tr>
	   	<td colspan='5' bgcolor="#ffffff">
	   		<textarea cols="120" rows="2" class="text" READONLY><?=$vec['profit']?></textarea>
	   	</td>
	   </tr>
			<tr>
				<td colspan="10" bgcolor="#e9e9e9" height="3"></td>
			</tr>
	   
   <? } ?>
     <tr>
		  <td colspan="5" align="center">
		  <? 
		  if(db_num_rows($sql) =="0")
		  {
		   echo $AppUI->_('Noitems');
		  }

		  ?>
		  <td>
		</tr>
	 <form>
   </table>



<? if (!$print)
{?>
<tr>
	<td align="right" colspan="5">
		<? $see_more_ant = "index.php?m=hhrr&a=viewhhrr&tab=2&id=".$id; ?>
		<!--<input type="button" value="<?php echo $AppUI->_( 'See more' );?>" class="button" onClick="javascript:window.location='<?=$see_more_ant;?>';" />!-->
		<a href="<?= $see_more_ant; ?>"> <?=$AppUI->_( 'See more' );?></a>
		<?
		if (validar_permisos_hhrr($id,'work_experience',-1))
		{				
			$edit_hrf = "index.php?m=hhrr&a=addedit&tab=2&id=".$id;
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

