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
if (!$canReadHHRR OR !validar_permisos_hhrr($id,'education',1))
	 $AppUI->redirect( "m=public&a=access_denied" );
?>
<br>
<table cellspacing="0" cellpadding="0" border="0" width="100%" class="tableForm_bg">
   <tr>
     <td >
     <table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
	 <tr class="tableHeaderGral" >
	  <th align="center" colspan="5"><?=$AppUI->_("Formal Education")?></th>
	 </tr>
   <tr class="tableHeaderGral">
     <th align="left" width='100'><?=$AppUI->_("Academic level")?></th>
		 <th align="left" width='100'><?=$AppUI->_("Title")?></th>
		 <th align="left" width='100'><?=$AppUI->_("Institution")?></th>
		 <th align="left" width='100'><?=$AppUI->_("Status")?></th>
		 <th align="left" width='10'><?=$AppUI->_("Completed")?></th>
   </tr>
   <?
    $sql = "SELECT id, id_user, level, title, instit, status, DATE_FORMAT(end_date,'%d-%m-%Y') as end_date FROM hhrr_education WHERE id_user ='$id' AND type='0' order by level desc";  
	  $rc = db_exec($sql);

	  while ($vec = db_fetch_array($rc)){
   ?>
	   <!-- Lista de antecedentes -->
	   <tr>
			 <td  bgcolor="#ffffff" width='125' >
			   <?
		      	if ($AppUI->user_locale == 'es')
		      		$name = 'name_es';
		      	else
		      		$name = 'name_en';
		  
						 @$level = db_loadResult("SELECT $name FROM hhrr_academic_level WHERE id ='$vec[level]' ");
						 echo $level;
				?>		 	
			 </td>
			 <td  bgcolor="#ffffff"  width='300'>
			     
			     <?
					
					//$desc = "name_".$AppUI->user_prefs['LOCALE'];
					$desc = "name_es";
								
					$query_title = "SELECT $desc  FROM hhrr_education_title WHERE title_id='$vec[title]' "; 
					$sql_title = db_exec($query_title);
					$title_desc = mysql_fetch_array($sql_title);
								
			     ?>
			     <!--<?=$vec['title']?>-->
			     <?=$title_desc[0]?>
			 </td>
			 <td  bgcolor="#ffffff"  width='250'>
			     <!--<?=$vec['instit']?>-->
			     <?
					
					$query_instit = "SELECT name FROM hhrr_education_institution WHERE instit_id='$vec[instit]' "; 
					$sql_instit = db_exec($query_instit);
					$instit_desc = mysql_fetch_array($sql_instit);
								
			     ?>
			     <?=$instit_desc[name]?>
			     
			 </td>
			 <td  bgcolor="#ffffff"  width='5'>
			 <?    
				   switch($vec['status'])
				   {
				     case "1":
							 echo $AppUI->_("Completed");
						 break;
						 case "0":
							 echo $AppUI->_("Incomplet");
						 break;
						 case "2":
							 echo $AppUI->_("On Course");
						 break;
			    }
			 ?>
			 </td>
			 <td  bgcolor="#ffffff"  width='70'><?=$vec['end_date']?></td>
	   </tr>
   <? } ?>
        <tr>
		  <td colspan="5" align="center">
		  <? 
		  if(db_num_rows($rc)==0)
		  {
		   echo $AppUI->_('Noitems');
		  }

		  ?><td>
		</tr>
        
		</form>
        </table>
		<br><br>

     </td>
   </tr>
			 
   <tr>
     <td>

     <table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
	 <tr class="tableHeaderGral">
	  <th align="center" colspan="5"><?=$AppUI->_("Training")?></th>
	 </tr>
   <tr class="tableHeaderGral">
		 <th align="left"><?=$AppUI->_("Type")?></th>
	     <th align="left"><?=$AppUI->_("Program")?></th>
		 <th align="left"><?=$AppUI->_("Activity")?></th>
		 <th align="left"><?=$AppUI->_("Institution")?></th>
		 <th align="left"><?=$AppUI->_("Date")?></th>
   </tr>
   <?
    $query = "SELECT id, id_user, seminary_type, seminary, title, instit, DATE_FORMAT(s_date,'%d-%m-%Y') as sdate FROM hhrr_education WHERE id_user ='$id' AND type='1' ";
	  $sql = mysql_query($query);

	  while ($vec = mysql_fetch_array($sql)){
   ?>
	   <!-- Lista de antecedentes -->
	   <tr>
		 <td  bgcolor="#ffffff" width='30'>
		 <?    
			   switch($vec['seminary_type'])
			   {
			     case "0":
						 echo $AppUI->_("Local");
					 break;
					 case "1":
						 echo $AppUI->_("In-Company");
					 break;
					 case "2":
						 echo $AppUI->_("Exterior");
					 break;
		    }
		 ?>		 	
		 	
		 </td>
		 <td  bgcolor="#ffffff" width='200'>
		    <!--<?=$vec['seminary']?>-->
		    <?
					
			$query_program = "SELECT name FROM hhrr_education_program WHERE program_id='$vec[seminary]' "; 
			$sql_program = db_exec($query_program);
			$program_desc = mysql_fetch_array($sql_program);
								
			?>
			
			<?=$program_desc[name]?>
		   
		 </td>
		 <td  bgcolor="#ffffff" width='200' ><?=$vec['title']?></td>
		 <td  bgcolor="#ffffff" width='200' >
		    <!--<?=$vec['instit']?>-->
		    <?
					
			$query_institut = "SELECT name FROM hhrr_education_institution WHERE instit_id='$vec[instit]' "; 
			$sql_institut = db_exec($query_institut);
			$institut_desc = mysql_fetch_array($sql_institut);
								
			?>
			
			<?=$institut_desc[name]?>
		    
		 </td>
		 <td  bgcolor="#ffffff" width='70' ><?=$vec['sdate']?></td>
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

		  ?><td>
		</tr>

        </table>
     </td>
   </tr>
   <tr>
   	<td align = "right" >
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
						if (validar_permisos_hhrr($id,'education',-1))
						{
							$edit_hrf = "index.php?m=hhrr&a=addedit&tab=$tab&id=".$id;
						?>
					 		<input type="button" value="<?php echo $AppUI->_( 'edit' );?>" class="button" onClick="javascript:window.location='<?=$edit_hrf;?>';" />
					 <?}?>
					</td>	
				</tr>   
		   <table>	  
	   
	 	</td>
   </tr>
</table>
