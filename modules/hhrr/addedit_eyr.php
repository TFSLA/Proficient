<?php /* HHRR $Id: addedit_eyr.php,v 1.4 2009-07-15 14:15:20 nnimis Exp $ */

global $AppUI, $canEdit;

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$tab = $_GET['tab'];
$df = $AppUI->getPref('SHDATEFORMAT');

$canAdd = CHhrr::canAdd();
$canEdit = CHhrr::canEdit($id);

if ($id == 0){
	$canEdit = $canAdd;
}

// check permissions
if (!$canEdit || $id == $AppUI->user_id OR !validar_permisos_hhrr($id,'performance_management',-1))
    $AppUI->redirect( "m=public&a=access_denied" );


$df = $AppUI->getPref('SHDATEFORMAT');

$accion = $_POST[accion];
$perf_id = $_POST[perf_id];

switch($accion)
{
	case "new":

		$perf_id = $_POST[perf_id];
		$log_from_date = $_POST[log_from_date];
        $log_to_date = $_POST[log_to_date];
		$performance = $_POST[performance];
		$potential = $_POST[potential];
		$supervisor = $_POST[supervisor];

        $from_date = substr($log_from_date,0,4)."-".substr($log_from_date,4,2)."-".substr($log_from_date,6,2);
		$to_date = substr($log_to_date,0,4)."-".substr($log_to_date,4,2)."-".substr($log_to_date,6,2);
        
		if($perf_id =="")
	    {
		 $query = "INSERT INTO hhrr_performance (id, user_id, from_date, to_date, performance, potential, supervisor) VALUES (NULL, '$id', '$from_date', '$to_date', '$performance', '$potential', '$supervisor')";
	    }
		else
	    {
		 $query = "UPDATE hhrr_performance SET 
		 from_date = '$from_date',
         to_date = '$to_date',
         performance = '$performance', 
		 potential = '$potential', 
		 supervisor = '$supervisor'
		 WHERE id='$perf_id' ";
	    }

		$sql = mysql_query($query);

		unset($_POST);
		unset($perf_id);
		unset($log_from_date);
		unset($log_to_date);
		unset($performance);
		unset($potential);
		unset($supervisor);
        unset($accion);
		$from_date = null;
		$to_date = null;
        	  
	break;

	case "delant":
	  
	 $del_query = "DELETE FROM hhrr_performance WHERE id = '$perf_id' ";
	 $sql_del = mysql_query($del_query);
     
	break;
}

?>
<style type="text/css">

.blockName {color:"Black";
		   background-color:"FFDD99";
		   text-align:"center";
		   font-weight: bold;
		  }

</style>

<script language="javascript">
<?="<!--";?>

var calendarField = '';

function popTSCalendar( field ){
	calendarField = field;
	idate = eval( 'document.docFrm.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setTSCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setTSCalendar( idate, fdate ) {
		fld_date = eval( 'document.docFrm.log_' + calendarField );
		fld_fdate = eval( 'document.docFrm.' + calendarField );
		fld_date.value = idate;
		fld_fdate.value = fdate;
}

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.frmaddedit.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	var f = document.frmaddedit;

	if(calendarField=="from_date")
	{
	 var anio = idate.substring(0,4);
	 var mes =  idate.substring(4,6);
	 var dia =  idate.substring(6,8);
     

	 fecha = new Date(anio,mes,dia); 
     nuevoY = parseInt(fecha.getFullYear()) - 1 ; 

	 document.frmaddedit.log_to_date.value = nuevoY+mes+dia;
	 document.frmaddedit.to_date.value = dia+'/'+mes+'/'+nuevoY;

 	}

	fld_date = eval( 'document.frmaddedit.log_' + calendarField );
	fld_fdate = eval( 'document.frmaddedit.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;

  
}

function submit_edit(obj){
   var f = document.editFrm;

   f.perf_id.value = obj;

   f.submit();
}

function submit_edit_doc(obj){
   var f = document.editDocFrm;

   f.doc_id.value = obj;
   f.submit();
}

function confirma(obj){
  
   var f = document.delFrm;
   f.perf_id.value = obj;

   var borrar=confirm('<?=$AppUI->_("Do you want to delete this")."?";?>');
    if (borrar)
	{
    	f.submit();
	}
}

function confirma_doc(obj){
  
   var f = document.delDocFrm;
   f.doc_id.value = obj;

   var borrar=confirm('<?=$AppUI->_("Do you want to delete this")."?";?>');
    if (borrar)
	{
    	f.submit();
	}
}

function submitIt(){
	var f = document.frmaddedit;
    var rta = true;
    
    var today = new Date();
    
    var vec_fecha = f.to_date.value.split("/");
    var to_date_txt = new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
    
    var vec_fecha = f.from_date.value.split("/");
    var from_date_txt = new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
    
    if (from_date_txt > today){
    	alert("<?php echo $AppUI->_('DateErrorMayor');?>");
    	rta = false;
    }else if (to_date_txt > today){
    	alert("<?php echo $AppUI->_('DateErrorMayor');?>");
    	rta = false;
    }else if (from_date_txt > to_date_txt){
    	alert("<?php echo $AppUI->_('DateErrorFromTo');?>");
    	rta = false;
    }else{
    	rta = true;
    }
    
	if (rta){
		f.submit();
	}
}

function submitItDoc(){
	var f = document.docFrm;
    var rta = true;
    
    if (f.log_from_date_doc.value > f.log_to_date_doc.value){
    	alert("<?php echo $AppUI->_('DateErrorFromTo');?>");
    	rta = false;
    }else if (f.doc_file.value == "" && f.action.value == "add"){
    	alert("<?php echo $AppUI->_('Please insert a document to upload');?>");
    	rta = false;
    }else{
    	rta = true;
    }
    
	if (rta){
		f.submit();
	}
}
//-->
</script>

<form name="editFrm" action="" method="POST">
	<input type="hidden" name="perf_id" value="" />
</form>

<form name="editDocFrm" action="" method="POST">
	<input type="hidden" name="doc_id" value="" />
	<input type="hidden" name="action" value="upd" />
</form>

<form name="delFrm" action="" method="POST">
	<input type="hidden" name="perf_id" value="" />
	<input type="hidden" name="accion" value="delant" />
</form>

<form name="delDocFrm" action="" method="POST">
	<input type="hidden" name="doc_id" value="" />
	<input type="hidden" name="dosql" value="do_performance_doc_aed" />
	<input type="hidden" name="action" value="del" />
	<input type="hidden" name="user_id" value="<?=$_GET["id"]?>">
</form>

<table cellspacing="0" cellpadding="0" border="0" width="100%" class="tableForm_bg">
   <tr>
     <td>
	   <table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
	     <tr class="blockName"><td colspan="7"><?=$AppUI->_("Evaluations")?></td></tr>
		 <tr class="tableHeaderGral">
		 <th width="20">&nbsp;</th>
		 <th width="16">&nbsp;</th>
		 <th align="center" width="205"><?=$AppUI->_("Performance Evaluation")?></th>
		 <th align="center" width="205"><?=$AppUI->_("Potential")?></th>
		 <th align="center"><?=$AppUI->_("Supervisor")?></th>
		 <th align="center"><?=$AppUI->_("From")?></th>
		 <th align="center"><?=$AppUI->_("To")?></th>
	    </tr>
        
        <? 
		$query = "SELECT h.id, h.from_date as from_date, h.to_date as to_date, h.performance, h.potential, h.supervisor, u.user_last_name, u.user_first_name FROM hhrr_performance as h, users as u WHERE h.user_id ='$id' AND h.supervisor = u.user_id ";

		$sql = mysql_query($query);
        
		while($vec = mysql_fetch_array($sql))
		{
		?>
        <tr>
           <td bgcolor="#ffffff" width="20">
		     <a href="javascript:submit_edit(<?=$vec[id]?>);"><img src="./images/icons/edit_small.gif" alt="<?=$AppUI->_("Edit")?>" border="0"></a>
		   </td>
		   <td bgcolor="#ffffff" width="16">
		     <a href="JavaScript:confirma(<?=$vec[id]?>)"><img src='./images/icons/trash_small.gif' alt='<?=$AppUI->_("Delete")?>' border='0'></a>
		   </td>
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
			 $from_date = new CDate($vec[from_date]);
			 $to_date = new CDate($vec[to_date]);
			 ?>
			
			 Nivel <?=$potential_desc[0]?> <?=$potential_desc[1]?>
		     
		   </td>
		   <td bgcolor="#ffffff"><?=$vec[user_last_name];?>, <?=$vec[user_first_name];?></td>
		   <td bgcolor="#ffffff"><?=$from_date->format($df);?></td>
		   <td bgcolor="#ffffff"><?=$to_date->format($df);?></td>
		</tr>
		<?
        }
		?>
        
		<tr> 
		  <td colspan="7">&nbsp;
		  </td>
		</tr>

		<?

		if($perf_id !="")
		{
		$query = "SELECT * from hhrr_performance WHERE id= '$perf_id'";
		$sql = mysql_query($query);
        $row = mysql_fetch_array($sql);
        
		$performance = $row[performance];
		$potential = $row[potential];
		$supervisor = $row[supervisor];
		$from_date = new CDate($row[from_date]);
	    $to_date = new CDate($row[to_date]);
		}
		else
		{
     $from_date = new CDate();
		 $to_date = new CDate();
		 $from_date->year = $to_date->year - 1;
		}
		?>
        <!-- <tr>
		<td colspan="7">
		<table border="0" cellspacing="1" cellpadding="0"> -->
        <td width="20">
		   <img src="images/1x1.gif" width="20" height="1">
		  </td>
		  <td width="16">
		   <img src="images/1x1.gif" width="16" height="1">
		  </td>
		<form method="POST" name="frmaddedit">
		<input type="hidden" name="accion" value="new" />
		<input type="hidden" name="perf_id" value="<?=$perf_id;?>" />
		  <td >
		    <!--<textarea name="performance" cols="34" rows="5" ><?=$performance;?></textarea>-->
		    
		        <?
				  $query = "SELECT id_item , name_es  FROM hhrr_performance_items ORDER BY name_es ASC"; 
				  $sql = mysql_query($query);
				 
                ?>
                <select name="performance" class="text" style="width:250px;" >
				    <option value="" ><?php echo $AppUI->_('Not Specified')?></option>
					
				    <?
					 
					while($vec = mysql_fetch_array($sql) )
					{ 
					  if ($vec[id_item]==$performance){
					  	$selected = "selected";
					  }else{
					    $selected = "";
					  }
					  
					  echo "<option " .$selected ." value=\"".$vec[id_item]."\">".$vec[name_es]."</option>";
					}
					?>
		         </select>
		     
		    
		  </td>
		  <td >
		       <!--<textarea name="potential" cols="34" rows="5" ><?=$potential;?></textarea>-->
		         
		        <?
				  $query = "SELECT id_potential ,level, name_es  FROM hhrr_performance_potential "; 
				  $sql = mysql_query($query);
				 
                ?>
                <select name="potential" class="text" style="width:250px;" >
				    <option value="" ><?php echo $AppUI->_('Not Specified')?></option>
					
				    <?
					 
					while($vec = mysql_fetch_array($sql) )
					{ 
					  if ($vec[id_potential]==$potential){
					  	$selected = "selected";
					  }else{
					    $selected = "";
					  }
					  
					  echo "<option " .$selected ." value=\"".$vec[id_potential]."\">Nivel ".$vec[level]." ".$vec[name_es]."</option>";
					}
					?>
		         </select>
		         
		  </td>
		  <td valign="top">
		    <select name="supervisor" class="text">

		    <?
            $query = "SELECT user_id , user_last_name, user_first_name FROM users WHERE user_type NOT IN ('5') "; 
			$sql = mysql_query($query);

			while ($vec=mysql_fetch_array($sql))
			{
				echo "<option value=\"$vec[user_id]\">$vec[user_last_name], $vec[user_first_name]</option>";
			}
			?>
			</select>
		 <!--  </td>
		</tr>
		</table> -->
		</td>
		
		<td valign="top" NOWRAP>
	  		<input type="hidden" name="log_from_date" value="<?php echo $from_date ? $from_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
	  		<input type="text" name="from_date" value="<?php echo $from_date ? $from_date->format( $df ) : '';?>" class="text" disabled="disabled" size="10" />
			<a href="#" onClick="popCalendar('from_date', 'from_date')">
				<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
			</a>
		</td>
		
		<td valign="top" NOWRAP>
			<input type="hidden" name="log_to_date" value="<?php echo $to_date ? $to_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
	 	 	<input type="text" name="to_date" value="<?php echo $to_date ? $to_date->format( $df ) : '';?>" class="text" disabled="disabled" size="10" />
			<a href="#" onClick="popCalendar('to_date', 'to_date')">
				<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		 	</a>
		</td>
		
		</tr>
		<tr>
		  <td colspan="7" align="right">
            
			<table>
			<tr>
				<td>
                <input type="button" value="<?php echo $AppUI->_('save');?>" class="button" onclick="submitIt()">
				</td></form>
				<td>
				 <?
				 if($_GET[a]!="personalinfo")
				 { 
				 $salir = "index.php?m=hhrr&a=viewhhrr&tab=$tab&id=".$id;
				 ?>
				 <input type="button" value="<?php echo $AppUI->_( 'exit' );?>" class="button" onClick="javascript:window.location='<?=$salir;?>';" />&nbsp;
				 <? } ?>
				</td>
			</tr>
			</table>

		  </td>
		</tr>
       </table>
       <tr><td><br></td></tr>
       
	   <tr><td height="1" bgcolor="AAAAAA" colspan="7"></td></tr>
	   
	   <?php
	   	if($_POST["action"]=="upd"){
	   		$action = "edit";
	   		$doc_id = $_POST["doc_id"];
	   		
	   		$sql = "SELECT * FROM hhrr_performance_documents WHERE doc_id = $doc_id";
	   		$doc_data = mysql_fetch_array(mysql_query($sql));
	   		$from_date = new CDate($doc_data["from_date"]);
	   		$to_date = new CDate($doc_data["to_date"]);
	   		
	   		$from_date_edit = $from_date->format($df);
	   		$to_date_edit = $to_date->format($df);
	   	} else {
	   		$doc_data["from_date"] = date("Ymd");
	   		$from_date_edit = new CDate();
	   		$from_date_edit = $from_date_edit->format($df);
	   		
	   		$doc_data["to_date"] = $doc_data["from_date"];
	   		$to_date_edit = $from_date_edit;
	   		$action = "add";
	   	}
	   ?>
	   
       <table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
       <form name="docFrm" action="" method="POST" enctype="multipart/form-data">
       <input type="hidden" name="dosql" value="do_performance_doc_aed">
       <input type="hidden" name="user_id" value="<?=$_GET["id"]?>">
       <input type="hidden" name="action" value="<?=$action?>">
       <input type="hidden" name="doc_id" value="<?=$doc_id?>">
		<tr class="blockName"><td colspan="7"><?=$AppUI->_("Documents")?></td></tr>
		<tr class="tableHeaderGral">
		 <th width="16"></th>
		 <th width="16"></th>
		 <th align="left" width="40%"><?=$AppUI->_("File")?></th>
		 <th align="left" width="240"><?=$AppUI->_("Comments")?></th>
		 <th align="center" width="15%"><?=$AppUI->_("From")?></th>
		 <th align="center" width="15%"><?=$AppUI->_("To")?></th>
		 <th align="center" width="15%"><?=$AppUI->_("Saved")?></th>
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
				<td bgcolor="#ffffff" width="20">
			     <a href="javascript:submit_edit_doc(<?=$vec["doc_id"]?>);"><img src="./images/icons/edit_small.gif" alt="<?=$AppUI->_("Edit")?>" border="0"></a>
			    </td>
			    <td bgcolor="#ffffff" width="16">
			     <a href="JavaScript:confirma_doc(<?=$vec["doc_id"]?>)"><img src='./images/icons/trash_small.gif' alt='<?=$AppUI->_("Delete")?>' border='0'></a>
			    </td>
			    <td bgcolor="#ffffff">
			     <a href="<?=$href.$vec["doc_file"]?>"><?=$vec["doc_file"]?></a>
			    </td>
			    <td bgcolor="#ffffff">
			     <?=$vec["comments"]?>
			    </td>
				<td bgcolor="#ffffff">
			     <?=$from_date->format($df)?>
			    </td>
			    <td bgcolor="#ffffff">
			     <?=$to_date->format($df)?>
			    </td>
			    <td bgcolor="#ffffff">
			     <?=$saved_date->format($df).$hour?>
			    </td>
			    </tr>
				<?php
			}
        }
		?>
		<tr><td><br></td></tr>
		<tr>
		<td><br>
		</td>
		<td><br>
		</td>
		<td valign="top" NOWRAP>
			<input type="hidden" name="MAX_FILE_SIZE" value="2500000">
			<input type="file" style="width:400px" class="text" id="doc_file" name="doc_file">
		</td>
		<td valign="top" NOWRAP>
			<input type="text" class="text" maxlength="255" style="width:290px" name="doc_comments" value="<?=$doc_data["comments"]?>">
		</td>
		<td valign="top" NOWRAP align="center">
	  		<input type="hidden" name="log_from_date_doc" value="<?php echo $doc_data["from_date"]; ?>" />
	  		<input type="text" name="from_date_doc" value="<?php echo $from_date_edit; ?>" class="text" disabled="disabled" size="10" />
			<a href="#" onClick="popTSCalendar('from_date_doc', 'from_date_doc')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
		</td>
		<td valign="top" NOWRAP align="center">
	  		<input type="hidden" name="log_to_date_doc" value="<?php echo $doc_data["to_date"]; ?>" />
	  		<input type="text" name="to_date_doc" value="<?php echo $to_date_edit; ?>" class="text" disabled="disabled" size="10" />
			<a href="#" onClick="popTSCalendar('to_date_doc', 'to_date_doc')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
		</td>
		</tr>
		<tr>
		  <td colspan="8" align="right">
			<table>
			<tr>
				<td>
                <input type="button" value="<?php echo $AppUI->_('save');?>" class="button" onclick="submitItDoc()">
				</td></form>
				<td>
				 <?
				 if($_GET[a]!="personalinfo")
				 {
				 $salir = "index.php?m=hhrr&a=viewhhrr&tab=$tab&id=".$id;
				 ?>
				 <input type="button" value="<?php echo $AppUI->_( 'exit' );?>" class="button" onClick="javascript:window.location='<?=$salir;?>';" />&nbsp;
				 <? } ?>
				</td>
			</tr>
			</table>
		  </td>
		</tr>
		<tr><td><br>
		</td></tr>
		</table>
     </td>
   </tr>
   </form>
</table>