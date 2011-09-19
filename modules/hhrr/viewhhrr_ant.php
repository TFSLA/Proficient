<?
global $AppUI;
$id = isset($_GET['id']) ? $_GET['id'] : 0;
if(!$id)
	$AppUI->redirect("m=hhrr&a=addedit&tab=0");
	
$tab = $_GET['tab'];

$canReadHHRR = !getDenyRead("hhrr");

if ($AppUI->user_id == $_GET['id'])
{
$canReadHHRR = '1';
}

// si el usuario logueado no puede leer hhrr
if (!$canReadHHRR OR !validar_permisos_hhrr($id,'work_experience',1))
	 $AppUI->redirect( "m=public&a=access_denied" );
?>
<br>

<table cellspacing="0" cellpadding="0" border="0" width="100%" class="tableForm_bg">
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
      //$query = "SELECT id, user_id, company, internal_company , area_internal, function, DATE_FORMAT(from_date,'%d-%m-%Y') as fdate ,DATE_FORMAT(to_date,'%d-%m-%Y') as tdate, profit, reports, functional_area, level_management  FROM hhrr_ant WHERE user_id ='$id' AND internal_company = '1' ";
      
      $query = "SELECT h.id, h.user_id, h.company, h.internal_company , h.area_internal, h.function, DATE_FORMAT(h.from_date,'%d-%m-%Y') as fdate ,DATE_FORMAT(h.to_date,'%d-%m-%Y') as tdate, h.profit, h.reports, 
h.functional_area, h.level_management FROM hhrr_ant as h, companies as c WHERE user_id ='$id' AND internal_company = '1' AND h.company = c.company_id
      ";

	  $sql = mysql_query($query);

	  while ($vec = mysql_fetch_array($sql)){
   ?>
	   <!-- Lista de antecedentes -->
	   <tr>
		 <td  bgcolor="#ffffff" width="100" >
		   <?
					 $cia = mysql_result(mysql_query("SELECT company_name FROM companies WHERE company_id ='$vec[company]' "),0);                     
					 echo $cia;
			?>
		 </td>
			 <td  bgcolor="#ffffff" width="100" >
		   <?
					 @$cia = mysql_result(mysql_query("SELECT dept_name FROM departments WHERE dept_id ='$vec[area_internal]' "),0);                     
					 echo $cia;
			?>			 	
			 </td>
			 <td  bgcolor="#ffffff" width="100" ><?=$vec['function']?></td>
			 <td  bgcolor="#ffffff" nowrap="nowrap" width="70"><?=$vec['fdate']?></td>
			 <td  bgcolor="#ffffff" nowrap="nowrap" width="70"><?=$vec['tdate']?></td>
			 <td  bgcolor="#ffffff" width="100">
		   <?
					 @$cia = mysql_result(mysql_query("SELECT CONCAT_WS(' ',user_first_name,user_last_name) AS name FROM users WHERE user_id ='$vec[reports]' "),0);                     
					 echo $cia;
			?>	
			 </td>
			 <td  bgcolor="#ffffff" width="100">
		   <?
					 @$cia = mysql_result(mysql_query("SELECT area_name FROM hhrr_functional_area WHERE id ='$vec[functional_area]' "),0);                     
					 echo $cia;
			?>					 
			 </td>
			 <td  bgcolor="#ffffff" width="100"><?=$vec['level_management']?></td>
	   </tr>
	   <tr>
	   	<td colspan='8' bgcolor="#ffffff">
	   		<textarea cols="135" rows="2" class="text" READONLY><?=$vec['profit']?></textarea>
	   	</td>
	   </tr>
			<tr>
				<td colspan="10" bgcolor="#e9e9e9" height="3"></td>
			</tr>
	   
   <? } ?>
     <tr>
		  <td colspan="7" align="center">
		  <? 
		  $cant = mysql_num_rows($sql); 
		  
		  if($cant =="0")
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
      $query = "SELECT id, user_id, company, internal_company , area_external, function, DATE_FORMAT(from_date,'%d-%m-%Y') as fdate ,DATE_FORMAT(to_date,'%d-%m-%Y') as tdate, profit, reports, functional_area, level_management FROM hhrr_ant WHERE user_id ='$id' AND internal_company = '0' ";

	  $sql = mysql_query($query);

	  while ($vec = mysql_fetch_array($sql)){
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
	   		<textarea cols="135" rows="2" class="text" READONLY><?=$vec['profit']?></textarea>
	   	</td>
	   </tr>
			<tr>
				<td colspan="10" bgcolor="#e9e9e9" height="3"></td>
			</tr>
	   
   <? } ?>
     <tr>
		  <td colspan="5" align="center">
		  <? 
		  $cant = mysql_num_rows($sql); 
		  
		  if($cant =="0")
		  {
		   echo $AppUI->_('Noitems');
		  }

		  ?>
		  <td>
		</tr>
		
	 <form>
   </table>
	
	<br><br>

   <table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
		<tr>
			<td align="right" colspan='5'>					
			  <?
				if($_GET[a]!="personalinfo")
				{ ?>
					<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
				<? 
				} ?>
				&nbsp;&nbsp;
				<?
				if (validar_permisos_hhrr($id,'work_experience',-1))
				{
					$edit_hrf = "index.php?m=hhrr&a=addedit&tab=$tab&id=".$id;
				?>
			 		<input type="button" value="<?php echo $AppUI->_( 'edit' );?>" class="button" onClick="javascript:window.location='<?=$edit_hrf;?>';" />
			<?}?>
			</td>	
		</tr>   
   <table>
</table>

