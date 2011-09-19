<?php /* HHRR $Id: viewhhrr_eyr.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
global $AppUI;

$id = isset($_GET['id']) ? $_GET['id'] : 0;
if(!$id)
	$AppUI->redirect("m=hhrr&a=addedit&tab=0");
	
$tab = $_GET['tab'];
$df = $AppUI->getPref('SHDATEFORMAT');

$canReadHHRR = !getDenyRead("hhrr");

// si el usuario logueado no puede leer hhrr
if (!$canReadHHRR OR !validar_permisos_hhrr($id,'performance_management',1))
	 $AppUI->redirect( "m=public&a=access_denied" );

?>
<style type="text/css">

.blockName {color:"Black";
		   background-color:"FFDD99";
		   text-align:"center";
		   font-weight: bold;
		  }

</style>

<table cellspacing="0" cellpadding="0" border="0" width="100%" class="tableForm_bg">
   <tr>
     <td>

	   <table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
	     <tr class="blockName"><td colspan="6"><?=$AppUI->_("Evaluations")?></td></tr>
		 <tr class="tableHeaderGral">
		 <th align="left" width="75"><?=$AppUI->_("From")?></th>
		 <th align="left" width="75"><?=$AppUI->_("To")?></th>
		 <th align="left" width="240"><?=$AppUI->_("Performance Evaluation")?></th>
		 <th align="left" width="240"><?=$AppUI->_("Potential")?></th>
		 <th align="left"><?=$AppUI->_("Supervisor")?></th>
	    </tr>
        
        <? 
		$query = "SELECT h.id, h.from_date as from_date, h.to_date as to_date, h.performance, h.potential, h.supervisor, u.user_last_name, u.user_first_name FROM hhrr_performance as h, users as u WHERE h.user_id ='$id' AND h.supervisor = u.user_id ";
		
		$sql = mysql_query($query);
        if(mysql_num_rows($sql) > 0){
			while($vec = mysql_fetch_array($sql))
			{
				$from_date = new CDate($vec["from_date"]);
				$to_date = new CDate($vec["to_date"]);
			?>
	        <tr>
			   <td bgcolor="#ffffff"><?=$from_date->format($df);?></td>
			   <td bgcolor="#ffffff"><?=$to_date->format($df);?></td>
			   <td bgcolor="#ffffff">
			     <?
						
				 $query_performance = "SELECT name_es FROM hhrr_performance_items WHERE id_item='$vec[performance]' "; 
				 $sql_performance = db_exec($query_performance);
				 $performance_desc = mysql_fetch_array($sql_performance);
									
				 ?>
				
				 <?=$performance_desc[0]?>
				 
			   </td>
			   <td bgcolor="#ffffff">
			      <?
						
				 $query_potential = "SELECT level, name_es FROM hhrr_performance_potential WHERE id_potential = '$vec[potential]' "; 
				 $sql_potential = db_exec($query_potential);
				 $potential_desc = mysql_fetch_array($sql_potential);
									
				 ?>
				
				 Nivel <?=$potential_desc[0]?> <?=$potential_desc[1]?>
				 
			   </td>
			   <td bgcolor="#ffffff"><?=$vec[user_last_name];?>, <?=$vec[user_first_name];?></td>
			</tr>
			<?
	        }
        }else{
        	echo "<tr><td colspan='6'>".$AppUI->_("No data available")."</td></tr>";
        }
		?>
        
		<tr> 
		  <td colspan="7">&nbsp;
		  </td>
		</tr>
</table>
</td>
</tr>
<tr>
<td>
<table align="right" border="0">
	<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
	</td>
<?
if (validar_permisos_hhrr($id,'performance_management',-1))
{?>
	<td align="center">
		 <?
		 $edit_hrf = "index.php?m=hhrr&a=addedit&tab=$tab&id=".$id;
		 ?>
		<input type="button" value="<?php echo $AppUI->_( 'edit' );?>" class="button" onClick="javascript:window.location='<?=$edit_hrf;?>';" />
	</td>
<?}?>
	</tr>
 </table>
</td>
</tr>
<tr><td><br></td></tr>
<tr><td height="1" bgcolor="AAAAAA"></td></tr>
	<table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
		<tr class="blockName"><td colspan="6"><?=$AppUI->_("Documents")?></td></tr>
		<tr class="tableHeaderGral">
		<th align="left" width="75"><?=$AppUI->_("From")?></th>
		 <th align="left" width="75"><?=$AppUI->_("To")?></th>
		 <th align="left" width="50%"><?=$AppUI->_("File")?></th>
		 <th align="left" width="25%"><?=$AppUI->_("Comments")?></th>
		 <th align="left" width="75"><?=$AppUI->_("Saved")?></th>
		</tr>
		<?php
		$query = "SELECT * FROM hhrr_performance_documents WHERE user_id ='$id' ORDER BY saved_date DESC";
		
		$result = mysql_query($query);
        if(mysql_num_rows($result) > 0){
			while($vec = mysql_fetch_array($result))
			{
				$from_date = new CDate($vec["from_date"]);
				$to_date = new CDate($vec["to_date"]);
				$saved_date = new CDate($vec["saved_date"]);
				$href = "./files/hhrr/$id/";
				$hour = " ".substr($vec["saved_date"],11,2).":".substr($vec["saved_date"],14,2);
			?>
	        <tr>
	        	<td bgcolor="#ffffff">
			     <?=$from_date->format($df)?>
			    </td>
			    <td bgcolor="#ffffff">
			     <?=$to_date->format($df)?>
			    </td>
			    <td bgcolor="#ffffff">
			     <a href="<?=$href.$vec["doc_file"]?>"><?=$vec["doc_file"]?></a>
			    </td>
			    <td bgcolor="#ffffff">
			     <?=$vec["comments"]?>
			    </td>
			    <td bgcolor="#ffffff">
			     <?=$saved_date->format($df).$hour?>
			    </td>
			</tr>
		<?php 
			}
        }else{
        	echo "<tr><td colspan='6'>".$AppUI->_("No data available")."</td></tr>";
        }
        	?>
        <tr>
        	<td><br>
			</td>
        </tr>
	</table>
	<tr class="tableForm_bg">
	<td colspan="7">
	<table align="right" border="0">
		<tr>
			<td>
			<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
		</td>
		<?
		if (validar_permisos_hhrr($id,'performance_management',-1))
		{?>
			<td align="center" class="std">
				 <?
				 $edit_hrf = "index.php?m=hhrr&a=addedit&tab=$tab&id=".$id;
				 ?>
				<input type="button" value="<?php echo $AppUI->_( 'edit' );?>" class="button" onClick="javascript:window.location='<?=$edit_hrf;?>';" />
			</td>
		<?}?>
		</tr>
		<tr><td><br></td></tr>
    </table>
    </td>
    </tr>
</table>