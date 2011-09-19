<?
global $AppUI,$hhrr_portal,$xajax;

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$tab = $_GET['tab'];

$canEditHHRR = !getDenyEdit("hhrr") || $id == $AppUI->user_id;

// si el usuario logueado no puede leer hhrr y no es ?l mismo
if ((!$canEditHHRR OR !validar_permisos_hhrr($id,'education',-1)) && !$hhrr_portal){
	 $AppUI->redirect( "m=public&a=access_denied" );
}

if(!$id){
	$AppUI->redirect("m=hhrr&a=addedit&tab=0");
}

$df = $AppUI->getPref('SHDATEFORMAT');

$f_date = intval( $_POST[f_date] ) ? new CDate( $_POST[f_date] ) : null;


if (isset($_POST['ant_id'])) { $ant_id = $_POST['ant_id']; }

switch($_POST[accion]) 
{   
	case "neduc":

	// Agrega nuevo 
	if ($ant_id == "")
	{ 
	  $level = $_POST['level'];
	  $title = $_POST['title'];
	  $instit = $_POST['instit'];
	  $status = $_POST['status'];

	  if($_POST['log_end_date']!=""){ 
                $this_date = new CDate($_POST['log_end_date']); 
                $end_date = $this_date->year.""-"".$this_date->month.""-"".$this_date->day;
              }else{
                $end_date = "";
              }
      
    $insert = "INSERT INTO hhrr_education (id_user,level,title,instit,status,type,end_date) VALUES ($id,$level,'$title','$instit','$status','0','$end_date')";
    //echo "$insert";
    $sql = mysql_query($insert);
      
	  unset($level);
	  unset($title);
	  unset($instit);
	  unset($status);
	  unset($ant_id);
	  unset($end_date);
	  
	  unset($_POST);

	}
	else{
	  $level = $_POST['level'];
	  $title = $_POST['title'];
	  $instit = $_POST['instit'];
	  $status = $_POST['status'];
	  
	  if($_POST['log_end_date']!=""){ 
                $this_date = new CDate($_POST['log_end_date']); 
                $end_date = $this_date->year.""-"".$this_date->month.""-"".$this_date->day;
              }else{
                $end_date = "";
              }
	        
	  $update = "UPDATE hhrr_education SET 
	       level = '$level',
				 title = '$title',
				 instit = '$instit',
				 status = '$status',
				 end_date = '$end_date'
				 WHERE id= '$ant_id' ";

    $sql = mysql_query($update);
     
	  unset($level);
	  unset($title);
	  unset($instit);
	  unset($status);
	  unset($_POST);
	  unset($ant_id);
	  unset($end_date);

	}
    
	break;

	case "delant":
	  
	 $del_query = "DELETE FROM hhrr_education WHERE id = '$ant_id' ";
	 $sql_del = mysql_query($del_query);
     
	break;

	case "nsduc":
	  // Agrega nuevo 
		if ($ant_id == "")
		{ 
		  $title_s = $_POST['title_s'];
		  $instit_s = $_POST['instit_s'];
		  $seminary_type = $_POST['seminary_type'];
		  $seminary = $_POST['seminary'];

		  if($_POST['log_from_date']!=""){ 
		     $this_date = new CDate($_POST['log_from_date']); 
		     $f_date = $this_date->year.""-"".$this_date->month.""-"".$this_date->day;
		   }else{
		     $f_date = "";
		   }
		  
		  $insert = "INSERT INTO hhrr_education (id_user,seminary_type,seminary, title,instit,s_date,type) VALUES ($id,'$seminary_type','$seminary','$title_s','$instit_s','$f_date','1')";
		  //echo $insert;
		  $sql = mysql_query($insert);

		  unset($title_s);
		  unset($instit_s);
		  unset($seminary_type);
		  unset($seminary);
		  unset($f_date);
		  $from_date = null;
		  unset($_POST);

		}
		else
		{
		  $title_s = $_POST['title_s'];
		  $instit_s = $_POST['instit_s'];
		  $seminary_type = $_POST['seminary_type'];
		  $seminary = $_POST['seminary'];
		  
		  if($_POST['log_from_date']!=""){ 
		     $this_date = new CDate($_POST['log_from_date']); 
		     $f_date = $this_date->year.""-"".$this_date->month.""-"".$this_date->day;
		  }else{
		     $f_date = "";
		  }
		  
		  $update = "UPDATE hhrr_education SET
		  		 seminary_type = '$seminary_type',
		  		 seminary = '$seminary',
					 title = '$title_s',
					 instit = '$instit_s',
					 s_date = '$f_date'
					 WHERE id= '$ant_id' ";

		  $sql = mysql_query($update);
		 
		  unset($title_s);
		  unset($instit_s);
		  unset($seminary_type);
		  unset($seminary);
		  unset($_POST);
		  $from_date = null;
		  unset($ant_id); 
		}
	break;

}

?>

<script language="javascript">


<?="<!--";?>

var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.semFrm.log_' + field + '.value' );
	window.open( './index.php?a=calendar&m=public&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
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

function popCalendar2( field ){
	calendarField = field;
	idate = eval( 'document.eduFrm.log_' + field + '.value' );
	window.open( './index.php?a=calendar&m=public&dialog=1&suppressLogo=1&callback=setCalendar2&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar2( idate, fdate ) {
	fld_date = eval( 'document.eduFrm.log_' + calendarField );
	fld_fdate = eval( 'document.eduFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function submit_edit(obj){
   var f = document.editFrm;

   f.ant_id.value = obj;

   f.submit();
}

function confirma(obj)
{  
   var f = document.delFrm;
   f.ant_id.value = obj;

   var borrar=confirm1("<?= $AppUI->_('delete_reg');?>");
   
    if (borrar)
	{
    f.submit();
	}
}

function submiteduFrm(){
    var f = document.eduFrm;
    var rta = true;

    var today = new Date();

    var fecha = f.log_end_date.value;
    var anio = fecha.substr(0,4);
    var mes = fecha.substr(4,2);
    var dia = fecha.substr(6,2);

    var  to_date_txt = new Date(anio,mes-1,dia);

    if (to_date_txt > today){
        alert1("<?php echo $AppUI->_('DateErrorMayor');?>");
        rta = false;
    }else{
        rta = true;
    }

        if (rta){
         f.submit();
        }
}

function submitsemFrm(){
        var f = document.semFrm;
    var rta = true;

    var today = new Date();

    var fecha = f.log_from_date.value;
    var anio = fecha.substr(0,4);
    var mes = fecha.substr(4,2);
    var dia = fecha.substr(6,2);

    var  to_date_txt = new Date(anio,mes-1,dia);

    if (to_date_txt > today){
        alert1("<?php echo $AppUI->_('DateErrorMayor');?>");
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
	<input type="hidden" name="ant_id" value="" />
</form>

<form name="delFrm" action="" method="POST">
	<input type="hidden" name="ant_id" value="" />
	<input type="hidden" name="accion" value="delant" />
</form>


<table cellspacing="0" cellpadding="0" border="0" width="100%" class="tableForm_bg">
   <tr>
     <td >
     <table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
	 <tr class="tableHeaderGral" >
	  <th align="center" colspan="7"><?=$AppUI->_("Formal Education")?></th>
	 </tr>
     <tr class="tableHeaderGral">
     <th >&nbsp;</th>
	 	 <th >&nbsp;</th>
     <th align="center" width='100'><?=$AppUI->_("Academic level")?></th>
		 <th align="center" width='100'><?=$AppUI->_("Title")?></th>
		 <th align="center" width='100'><?=$AppUI->_("Institution")?></th>
		 <th align="center" width='100'><?=$AppUI->_("Status")?></th>
		 <th align="center" width='100'><?=$AppUI->_("Completed")?></th>
   </tr>
   <?
    $sql = "SELECT id, id_user, level, title, instit, status, DATE_FORMAT(end_date,'%d-%m-%Y') as end_date FROM hhrr_education WHERE id_user ='$id' AND type='0' order by level desc ";  
	  $rc = db_exec($sql);

	  while ($vec = db_fetch_array($rc)){
   ?>
	   <!-- Lista de antecedentes -->
	   <tr>
			 <td bgcolor="#ffffff">
			  
			   <a href="javascript:submit_edit(<?=$vec[id]?>);"><img src="./images/icons/edit_small.gif" alt="<?=$AppUI->_("Edit")?>" border="0"></a>
			 </td>
			 <td bgcolor="#ffffff">
			   
			   <a href="JavaScript:confirma(<?=$vec[id]?>)"><img src='./images/icons/trash_small.gif' alt='<?=$AppUI->_("Delete")?>' border='0'></a>
			 </td>
			 <td  bgcolor="#ffffff" >
			   <?
		      	if ($AppUI->user_locale == 'es')
		      		$name = 'name_es';
		      	else
		      		$name = 'name_en';
		  
						 @$level = db_loadResult("SELECT $name FROM hhrr_academic_level WHERE id ='$vec[level]' ");
						 echo $level;
				?>		 	
			 </td>
			 <td  bgcolor="#ffffff" >
			    <?
					
					//$desc = "name_".$AppUI->user_prefs['LOCALE'];
					$desc = "name_es";
								
					$query_title = "SELECT $desc  FROM hhrr_education_title WHERE title_id='$vec[title]' "; 
					$sql_title = db_exec($query_title);
					$title_desc = mysql_fetch_array($sql_title);
								
			     ?>

			     <?=$title_desc[0]?>
			     
			 </td>
			 <td  bgcolor="#ffffff" >
			      
			     <?
					
					$query_instit = "SELECT name FROM hhrr_education_institution WHERE instit_id='$vec[instit]' "; 
					$sql_instit = db_exec($query_instit);
					$instit_desc = mysql_fetch_array($sql_instit);
								
			     ?>
			     <?=$instit_desc[name]?>
			 </td>
			 <td  bgcolor="#ffffff" >
			 <?    
				   switch($vec['status'])
				   {
				     case "1":
							 echo $AppUI->_("Completed");
						 break;
						 case "0":
							 echo $AppUI->_("Incomplete");
						 break;
						 case "2":
							 echo $AppUI->_("On Course");
						 break;
			    }
			 ?>
			 </td>
			 <td  bgcolor="#ffffff" ><?=$vec['end_date']?></td>
	   </tr>
   <? } ?>
        <tr>
		  <td colspan="6" align="center">
		  <? 
		  if(db_num_rows($rc)==0)
		  {
		   echo $AppUI->_('Noitems');
		  }

		  ?><td>
		</tr>
        
		<? 
		  if($ant_id !="")
		  {
			  $select = "SELECT * FROM hhrr_education WHERE id='$ant_id' and type='0'";
			  $sql_sel = mysql_query($select);
			  $data = mysql_fetch_array($sql_sel);
	
			  $level = $data['level'];
			  $title = $data['title'];
			  $instit = $data['instit'];
			  $status = $data['status'];
	
			  if($data!=""){
			  $end_date = new CDate($data['end_date']);
			  }
	
				switch($status){
					 case "0":
						$ck0 = "selected";
					 break;
					 case "1":
						$ck1 = "selected";
					 break;
					 case "2":
						$ck2 = "selected";
					 break;
					 default:
						$ck1 = "selected";
					 break;
				}
		  }

		  if($status=="")
		  {
		   $ck1 = "selected";
		  }
	   ?> 
	  <form name="eduFrm" action="" method="POST">
		<input type="hidden" name="accion" value="neduc" />
		<input type="hidden" name="ant_id" value="<?=$ant_id;?>" />

		<tr>
		  <td width="16">
		   <img src="images/1x1.gif" width="16" height="1">
		  </td>
		  <td width="16">
		   <img src="images/1x1.gif" width="16" height="1">
		  </td>
		  <td>
			 	<select name="level" class="text" onchange="xajax_addTitle('title', document.eduFrm.level.value)" style="width:120px;">
	      	<?
	      	if ($AppUI->user_locale == 'es')
	      		$name = 'name_es';
	      	else
	      		$name = 'name_en';
	      	
				  	$query = "SELECT id, $name AS name
											FROM hhrr_academic_level
											ORDER BY name"; 
				  	$sql = mysql_query($query);
				 
					  while($vec = mysql_fetch_array($sql) )
					  {
					  	$selected = ($vec['id']==$level) ? "selected" : "";
		        	echo "<option " .$selected ." value=\"$vec[id]\">$vec[name]</option>";
		        }
				  ?>
		   </select>
		  </td>
		  
		  <script type="text/javascript">
				xajax_addTitle('title', document.eduFrm.level.value,<?=($title)? $title : "-1";?>);
		  </script>
		  
		  <td>
			 
                <select name="title" id="title" class="text" style="width:350px;" ></select>
		  </td>
			 
		  <td>
			 <!--<input type="text" name="instit" value="<?=$instit;?>" size="37" class="text">-->
			    <?
					
					
					$query = "SELECT instit_id , name  FROM hhrr_education_institution ORDER BY name ASC"; 
					$sql = mysql_query($query);
					
                ?>
                <select name="instit" class="text" style="width:160px;" >
				    <option value="" ><?php echo $AppUI->_('Not Specified')?></option>
					
				    <?
					 
					while($vec = mysql_fetch_array($sql) )
					{ 
					  if ($vec[instit_id]==$instit){
					  	$selected = "selected";
					  }else{
					    $selected = "";
					  }
					  
					  echo "<option " .$selected ." value=\"".$vec[instit_id]."\">".$vec[name]."</option>";
					}
					?>
		         </select>
		  </td>
		  <td align="left">
		    <select name="status" class="text">
					<option value="0" <?=$ck0;?> ><?=$AppUI->_("Incomplete")?></option>
					<option value="1" <?=$ck1;?> ><?=$AppUI->_("Completed")?></option>
					<option value="2" <?=$ck2;?> ><?=$AppUI->_("On Course")?></option>
				</select>
			</td>
			
			<td align="left" nowrap>
		  	<input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />                                                 
		    <input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" size="10" />
			<input type="hidden" name="end_date_format" value="<?php echo $df; ?>">

					<a href="javascript://" onClick="popCalendar2('end_date')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
					</a>			
			</td>
			
		</tr>
		 <tr>
		  <td colspan="6" align="right">
		    &nbsp;
		  </td>
		</tr>
          <tr>
			   <td colspan="7" align="right">
			     <input type="button" value="<?php echo $AppUI->_('save');?>" class="button" onclick="submiteduFrm()" >
			   </td>
			 </tr>
		</form>
        </table>
		<br><br>

     </td>
   </tr>
			 

	 </td>
   </tr>

   <tr>
     <td>

     <table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
	 <tr class="tableHeaderGral">
	  <th align="center" colspan="7"><?=$AppUI->_("Training")?></th>
	 </tr>
   <tr class="tableHeaderGral">
	   <th >&nbsp;</th>
		 <th >&nbsp;</th>
		 <th align="center"><?=$AppUI->_("Type")?></th>
		 <th align="center"><?=$AppUI->_("Program")?></th>
		 <th align="center"><?=$AppUI->_("Activity")?></th>
		 <th align="center"><?=$AppUI->_("Institution")?></th>
		 <th align="center"><?=$AppUI->_("Date")?></th>
   </tr>
   <?
    $query = "SELECT id, id_user, seminary_type, seminary, title, instit, DATE_FORMAT(s_date,'%d-%m-%Y') as sdate FROM hhrr_education WHERE id_user ='$id' AND type='1' ";
	  $sql = mysql_query($query);

	  while ($vec = mysql_fetch_array($sql)){
   ?>
	   <!-- Lista de antecedentes -->
	   <tr>
		 <td bgcolor="#ffffff">
		  
		   <a href="javascript:submit_edit(<?=$vec[id]?>);"><img src="./images/icons/edit_small.gif" alt="<?=$AppUI->_("Edit")?>" border="0"></a>
		 </td>
		 <td bgcolor="#ffffff">
		   
		   <a href="JavaScript:confirma(<?=$vec[id]?>)"><img src='./images/icons/trash_small.gif' alt='<?=$AppUI->_("Delete")?>' border='0'></a>
		 </td>
		 <td  bgcolor="#ffffff" >
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
		 <td  bgcolor="#ffffff" >
		    <!-- <?=$vec['seminary']?>-->
		    
		    <?
					
			$query_program = "SELECT name FROM hhrr_education_program WHERE program_id='$vec[seminary]' "; 
			$sql_program = db_exec($query_program);
			$program_desc = mysql_fetch_array($sql_program);
								
			?>
			
			<?=$program_desc[name]?>
		 </td>
		 <td  bgcolor="#ffffff" ><?=$vec['title']?></td>
		 <td  bgcolor="#ffffff" >
		    <!--<?=$vec['instit']?>-->
		    <?
					
			$query_institut = "SELECT name FROM hhrr_education_institution WHERE instit_id='$vec[instit]' "; 
			$sql_institut = db_exec($query_institut);
			$institut_desc = mysql_fetch_array($sql_institut);
								
			?>
			
			<?=$institut_desc[name]?>
		 </td>
		 <td  bgcolor="#ffffff" ><?=$vec['sdate']?></td>
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

		  ?><td>
		</tr>

		 <? 
		  if($ant_id !=""){
		  $select = "SELECT * FROM hhrr_education WHERE id='$ant_id' and type='1' ";
		  $sql_sel = mysql_query($select);
		  $data = mysql_fetch_array($sql_sel);

		  $seminary_type = $data['seminary_type'];
		  $seminary = $data['seminary'];
		  $level_s = $data['level'];
		  $title_s = $data['title'];
		  $instit_s = $data['instit'];
		  

		  if($data!="")
		  	$from_date = new CDate($data[s_date]);

			switch($seminary_type){
			 case "0":
				$sel0 = "selected";
			 break;
			 case "1":
				$sel1 = "selected";
			 break;
			 case "2":
				$sel2 = "selected";
			 break;
			}
		  }
		  
	    ?>
    <form name="semFrm" action="" method="POST">
		<input type="hidden" name="accion" value="nsduc" />
		<input type="hidden" name="ant_id" value="<?=$ant_id;?>" />

		<tr>
		  <td width="16">
		   <img src="images/1x1.gif" width="16" height="1">
		  </td>
		  <td width="16">
		   <img src="images/1x1.gif" width="16" height="1">
		  </td>
		  <td>
		  	<select name="seminary_type" class="text">
					<option value="0" <?=$sel0;?> ><?=$AppUI->_("Local")?></option>
					<option value="1" <?=$sel1;?> ><?=$AppUI->_("In-Company")?></option>
					<option value="2" <?=$sel2;?> ><?=$AppUI->_("Exterior")?></option>
				</select>
		  </td>
		  <td>
		  	<!--<input type="text" name="seminary" value="<?=$seminary;?>" size="31" class="text">-->
		  	    
		  	    <?
				  $query = "SELECT program_id , name  FROM hhrr_education_program ORDER BY name ASC"; 
				  $sql = mysql_query($query);
					
                ?>
                <select name="seminary" class="text" style="width:250px;" >
				    <option value="" ><?php echo $AppUI->_('Not Specified')?></option>
					
				    <?
					 
					while($vec = mysql_fetch_array($sql) )
					{ 
					  if ($vec[program_id]==$seminary){
					  	$selected = "selected";
					  }else{
					    $selected = "";
					  }
					  
					  echo "<option " .$selected ." value=\"".$vec[program_id]."\">".$vec[name]."</option>";
					}
					?>
		         </select>
		         
		  </td>
		  <td>
		  	<input type="text" name="title_s" value="<?=$title_s;?>" size="31" class="text">
		  </td>
		  <td>
		    <!--<input type="text" name="instit_s" value="<?=$instit_s;?>" size="30" class="text"> -->
		  
		       <?
				  $query = "SELECT instit_id , name  FROM hhrr_education_institution ORDER BY name ASC"; 
				  $sql = mysql_query($query);
					
                ?>
                <select name="instit_s" class="text" style="width:200px;" >
				    <option value="" ><?php echo $AppUI->_('Not Specified')?></option>
					
				    <?
					 
					while($vec = mysql_fetch_array($sql) )
					{ 
					  if ($vec[instit_id]==$instit_s){
					  	$selected = "selected";
					  }else{
					    $selected = "";
					  }
					  
					  echo "<option " .$selected ." value=\"".$vec[instit_id]."\">".$vec[name]."</option>";
					}
					?>
		         </select>
		  </td>
		  <td width="105" nowrap>
		  	<input type="hidden" name="log_from_date" value="<?php echo $from_date ? $from_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		    <input type="text" name="from_date" value="<?php echo $from_date ? $from_date->format( $df ) : '';?>" class="text" disabled="disabled" size="10" />
					<a href="javascript://" onClick="popCalendar('from_date')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
					</a>
		  </td>
		</tr>
         <tr>
		  <td colspan="6" align="right">
		    &nbsp;
		  </td>
		</tr>
        <tr>
		  <td colspan="7" align="right">
		    <input type="button" value="<?php echo $AppUI->_('save');?>" class="button" onclick="submitsemFrm()">
		    </form>
		    <?
				if($_GET[a]!="personalinfo"){ 
					$salir = "index.php?m=hhrr&a=viewhhrr&tab=$tab&id=".$id;
					?>
					<input type="button" value="<?php echo $AppUI->_( 'exit' );?>" class="button" onClick="javascript:window.location='<?=$salir;?>';" />
					<? 
				} ?>
		  </td>
		</tr>
        </table>
     </td>
   </tr>
    <tr>
     <td align = "right" >
	   &nbsp;
	 </td>
   </tr>

</table>




