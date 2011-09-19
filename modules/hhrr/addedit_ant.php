<?
global $AppUI, $hhrr_portal, $xajax;

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$tab = $_GET['tab'];

$canEditHHRR = !getDenyEdit("hhrr") || $id == $AppUI->user_id;

// si el usuario logueado no puede leer hhrr y no es ?l mismo
if ((!$canEditHHRR OR !validar_permisos_hhrr($id,'work_experience',-1)) && !$hhrr_portal)
	 $AppUI->redirect( "m=public&a=access_denied" );


if(!$id){
	$AppUI->redirect("m=hhrr&a=addedit&tab=0");
}

$df = $AppUI->getPref('SHDATEFORMAT');

$f_date = intval( $_POST[f_date] ) ? new CDate( $_POST[f_date] ) : null;


if (isset($_POST['ant_id'])) { $ant_id = $_POST['ant_id']; }


switch($_POST[accion]) 
{   
	case "newant":

	// Agrega nuevo antecedentes
	if ($ant_id == "")
	{ 
	  $area_internal = $_POST['area_internal'];
	  $area_external = $_POST['area_external'];
	  $logros = $_POST['logros'];
	  $function = $_POST['function'];
	  $type_cia = $_POST['type_cia'];
	  $reports = $_POST['reports'];
	  $functional_area = $_POST['functional_area'];
	  $level_management = $_POST['level_management'];
	  
	  if($type_cia == "1")
	  	$company = $_POST['company_int'];
	  else
		  $company = $_POST['company_ext'];
      
	  $from_date = new CDate($_POST[log_from_date]);
	  $to_date = new CDate($_POST[log_to_date]);

	  $f_date = substr($_POST[log_from_date],0,4)."-".substr($_POST[log_from_date],4,2)."-".substr($_POST[log_from_date],6,2);

	  $t_date = substr($_POST[log_to_date],0,4)."-".substr($_POST[log_to_date],4,2)."-".substr($_POST[log_to_date],6,2);


    $insert = "INSERT INTO hhrr_ant VALUES (null,'$id','$company','$type_cia','$area_external',$area_internal,'$function','$t_date','$f_date','$logros','$reports','$functional_area','$level_management')";
	  $sql = mysql_query($insert);
	  //echo $insert;
      
	  unset($company);
	  unset($area_internal);
	  unset($area_external);
	  unset($logros);
	  unset($function);
	  unset($from_date);
	  unset($to_date);
	  unset($type_cia);
	  unset($reports);
	  unset($company_supervisor);
	  unset($_POST);

	}
	else{
	  $area_internal = $_POST['area_internal'];
	  $area_external = $_POST['area_external'];
	  $logros = $_POST['logros'];
	  $function = $_POST['function'];
	  $type_cia = $_POST['type_cia'];
	  $reports = $_POST['reports'];
	  $functional_area = $_POST['functional_area'];
	  $level_management = $_POST['level_management'];	
	  $company_supervisor = $_POST['company_supervisor'];
      
	  if($type_cia =="1"){
	  $company = $_POST['company_int'];
	  }
	  else{
	  $company = $_POST['company_ext'];
	  }

	  $from_date = new CDate($_POST[log_from_date]);
	  $to_date = new CDate($_POST[log_to_date]);

	  $f_date = substr($_POST[log_from_date],0,4)."-".substr($_POST[log_from_date],4,2)."-".substr($_POST[log_from_date],6,2);

	  $t_date = substr($_POST[log_to_date],0,4)."-".substr($_POST[log_to_date],4,2)."-".substr($_POST[log_to_date],6,2);

	  $update = "UPDATE hhrr_ant SET 
	       company= '$company',
				 area_internal = $area_internal,
				 area_external = '$area_external',
				 function = '$function',
				 from_date = '$f_date',
				 to_date = '$t_date',
				 profit = '$logros',
				 internal_company = '$type_cia',
				 reports = '$reports',
				 functional_area = '$functional_area',
				 level_management = '$level_management'
				 
				 WHERE id= '$ant_id' ";

    $sql = mysql_query($update);
		//echo $update;
	  
	  unset($company);
	  unset($area);
	  unset($logros);
	  unset($function);
	  unset($type_cia);
	  $from_date = null;
	  $to_date = null;
	  unset($_POST);
	  unset($ant_id);
	  unset($area_internal);
	  unset($area_internal_name);
	  unset($reports);
	  unset($company_supervisor);

	}
    
	break;

	case "delant":
	  
	 $del_query = "DELETE FROM hhrr_ant WHERE id = '$ant_id' ";
	 $sql_del = mysql_query($del_query);
     
	break;

}

$sql_particular = "SELECT department FROM hhrr_permissions WHERE id_user='".$AppUI->user_id."'  AND work_experience <>'0' ";
$allow_dept =  db_loadHashList( $sql_particular );
  
	
  	if (count($allow_dept)>0)
  	{
  		
  	$sql_dept = db_exec("SELECT distinct(dept_company) FROM departments WHERE dept_company > '0' AND dept_id IN (". implode( ',', $allow_dept ).") ");
  	
  	}else{
  	$sql_dept = db_exec("SELECT distinct(dept_company) FROM departments WHERE dept_company > '0' and dept_id='-1' ");
  	}


if($app_id->user_type != '1'){
	$sql_dept = db_exec("SELECT distinct(dept_company) FROM departments WHERE dept_company > '0' ");
}

  	
  	
  	
// Traigo las empresas que tienen departamento
//$sql_dept = db_exec("SELECT distinct(dept_company) FROM departments WHERE dept_company > '0' ");

$strJS_dept = "var arDept = new Array();\n";

 while ($vec_dept = mysql_fetch_array($sql_dept))
 {
 $strJS_dept .= "arDept[arDept.length] = new Array('".$vec_dept[dept_company]."');\n";
 }
 
 if ($hhrr_portal)
 {
 	$modulo = "";
 	$modulo_tab = ""; 
 }else{
 	$modulo = "hhrr";
 	$modulo_tab = "work_experience";
 }
 
?>

<script language="javascript">
<?="<!--";?>

var calendarField = '';
var frm = '';

function popCalendar( field, formulario ){
	calendarField = field;
	frm = formulario;
	idate = eval( 'document.'+frm+'.log_' + field + '.value' );
	window.open( '<?php echo "{$AppUI->cfg[base_url]}"; ?>/index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	var f = 'document.'+frm;

	if(calendarField=="from_date")
	{
	 var anio = idate.substring(0,4);
	 var mes =  idate.substring(4,6);
	 var dia =  idate.substring(6,8);
     

	 fecha = new Date(anio,mes,dia); 
     nuevoY = parseInt(fecha.getFullYear()) + 1 ; 
	
		if (frm == "antFrmInt")
		{
	 		document.antFrmInt.log_to_date.value = nuevoY+mes+dia;
	 		document.antFrmInt.to_date.value = dia+'/'+mes+'/'+nuevoY;
	 	}
	 	else
	 	{
	 		document.antFrmExt.log_to_date.value = nuevoY+mes+dia;
	 		document.antFrmExt.to_date.value = dia+'/'+mes+'/'+nuevoY;	 		
	 	} 

 	}


	if (frm == "antFrmInt")
	{
		fld_date = eval( 'document.antFrmInt.log_' + calendarField );
		fld_fdate = eval( 'document.antFrmInt.' + calendarField );
 	}
 	else
 	{
 		fld_date = eval( 'document.antFrmExt.log_' + calendarField );
		fld_fdate = eval( 'document.antFrmExt.' + calendarField );
 	} 

	fld_date.value = idate;
	fld_fdate.value = fdate;

  
}

function submit_edit(obj, frm){
  var f = document.editFrm;
 
  f.ant_id.value = obj;
  f.submit();
}

function confirma(obj){
  
   var f = document.delFrm;
   f.ant_id.value = obj;

   var borrar=confirm1("<?=$AppUI->_("delete_reg")?>");

    if (borrar)
	{
    f.submit();
	}
}

function popDept() {
    var f = document.antFrmInt;
    if (f.company_int.selectedIndex == -1) {
        alert("<?=$AppUI->_('Please select a company first!')?>");
    } else {
        window.open('index.php?a=selector&m=public&dialog=1&suppressLogo=1&callback=setDept&table=departments&company_id='
            + f.company_int.options[f.company_int.selectedIndex].value
            + '&dept_id='+f.area_internal.value,'dept','left=50,top=50,height=250,width=400,resizable')
    }
}
// Callback function for the generic selector
function setDept( key, val ) {
    var f = document.antFrmInt;
    if (val != '') {
        f.area_internal.value = key;
        f.area_internal_name.value = val;
    } else {
        f.area_internal.value = '0';
        f.area_internal_name.value = '';
    }
}

function change_cia(cia){

	var f = document.antFrmInt;

	if (cia.value != f.company_or.value )
	{
	f.area_internal.value = '-1';
	f.area_internal_name.value = "<?=$AppUI->_('All');?>";
	}else{
	f.area_internal.value = f.area_internal_or.value;
	f.area_internal_name.value = f.area_internal_name_or.value;
	}

}

function submitIt(){
	var f = document.antFrmInt;
    var rta = true;
    var val_dept = false;

    <?=$strJS_dept;?>
       
	   for(var h = 0; h < arDept.length; h++){
			if (f.company_int.value == arDept[h][0])
		    {
			val_dept = true;
			h = arDept.length;
	        }
	   }
    
	var today = new Date();
    
    var vec_fecha = f.to_date.value.split("/");
    var to_date_txt = new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
    
    var vec_fecha = f.from_date.value.split("/");
    var from_date_txt = new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
    
    if (from_date_txt > today){
    	alert1("<?php echo $AppUI->_('DateErrorMayor');?>");
    	rta = false;
    }else if (to_date_txt > today){
    	alert1("<?php echo $AppUI->_('DateErrorMayor');?>");
    	rta = false;
    }else if (from_date_txt > to_date_txt){
    	alert1("<?php echo $AppUI->_('DateErrorFromTo');?>");
    	rta = false;
    }else if (f.area_internal.value == '' || f.area_internal.value == '-1')
	{
	    alert1("<?php echo $AppUI->_('hhrrInvalidepartement');?>");
	    rta = false;
	}else{
		rta = true;
	}
	
	
	
	if ((f.logros.value==f.logros_ref.value) && (rta))
	{
		f.logros.value = "";
	}
	
	if ((f.logros.value == "") && (rta))
	{
		alert1("<?php echo $AppUI->_('ErrorLogros');?>");
    	rta = false;
	}

	if (rta){
	f.submit();
	}

}

function submitItExt(){
	var f = document.antFrmExt;
    var rta = true;
    
    var today = new Date();
    
    var vec_fecha = f.to_date.value.split("/");
    var to_date_txt = new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
    
    var vec_fecha = f.from_date.value.split("/");
    var from_date_txt = new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
    
    if (from_date_txt > today){
    	alert1("<?php echo $AppUI->_('DateErrorMayor');?>");
    	rta = false;
    }else if (to_date_txt > today){
    	alert1("<?php echo $AppUI->_('DateErrorMayor');?>");
    	rta = false;
    }else if (from_date_txt > to_date_txt){
    	alert1("<?php echo $AppUI->_('DateErrorFromTo');?>");
    	rta = false;
    }else{
    	rta = true;
    }
    
    if ((f.logros.value==f.logros_ref.value) && (rta))
	{
		f.logros.value = "";
	}
	
	if ((f.logros.value == "") && (rta))
	{
		alert1("<?php echo $AppUI->_('ErrorLogros');?>");
    	rta = false;
	}
    
	if (rta){
	f.submit();
	}
}

function select_ajax_supervisor(company, supervisor)
{
	xajax_combo_UserSupervisor('reports', company, supervisor, 'direct_report');
}

//-->
</script>

<?
  if($ant_id !="")
  {
	  $select = "SELECT * FROM hhrr_ant WHERE id='$ant_id' ";
	  $sql_sel = mysql_query($select);
	  $data = mysql_fetch_array($sql_sel);
	
	  $company = $data['company'];
	  $area_internal = $data['area_internal'];
	  $area_external = $data['area_external'];
	  $logros = $data['profit'];
	  $function = $data['function'];
	  $type_cia = $data['internal_company'];
	  
	  $from_date = new CDate($data[from_date]);
	  $to_date = new CDate($data[to_date]);
	  
	  $reports = $data['reports'];
	  $functional_area = $data['functional_area'];
	  $level_management = $data['level_management'];
  }
  else
  	$type_cia = -1;
?>

<form name="editFrm" action="" method="POST">
	<input type="hidden" name="ant_id" value="" />
</form>

<form name="delFrm" action="" method="POST">
	<input type="hidden" name="ant_id" value="" />
	<input type="hidden" name="accion" value="delant" />
</form>


<table cellspacing="0" cellpadding="0" border="0" width="100%" class="tableForm_bg">
<form name="antFrmInt" action="" method="POST">
<input type="hidden" name="accion" value="newant" />
<input type="hidden" name="ant_id" value="<?=$ant_id;?>" />
<input type="hidden" name="type_cia" value="1" />
	<tr>
  	<td>
     <table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
	 <tr class="tableHeaderGral" >
      <th align="center" colspan="11"><?=$AppUI->_("Internal Companies")?></th>
	 </tr>
     <tr class="tableHeaderGral" >
		 <th width="16">&nbsp;</th>
		 <th width="16">&nbsp;</th>
		 <th align="center" width="100"><?=$AppUI->_("Company")?></th>
		 <th align="center" width="100"><?=$AppUI->_("Management/Area")?></th>
		 <th align="center" width="100"><?=$AppUI->_("Function")?></th>
		 <th align="center" nowrap="nowrap" width="70" ><?=$AppUI->_("From")?></th>
		 <th align="center" nowrap="nowrap" width="70" ><?=$AppUI->_("To")?></th>
		 <th align="center" width="100"><?=$AppUI->_("reports")?></th>
		 <th align="center" width="100"><?=$AppUI->_("functional_area")?></th>
		 <th align="center" width="100"><?=$AppUI->_("level_management")?></th>
   </tr>
   <?

      $query = "SELECT h.id, h.user_id, h.company, h.internal_company , h.area_internal, h.function, DATE_FORMAT(h.from_date,'%d-%m-%Y') as fdate ,DATE_FORMAT(h.to_date,'%d-%m-%Y') as tdate, h.profit, h.reports, 
h.functional_area, h.level_management FROM hhrr_ant as h, companies as c WHERE user_id ='$id' AND internal_company = '1' AND h.company = c.company_id
      ";

	  $sql = mysql_query($query);

	  while ($vec = mysql_fetch_array($sql)){
   ?>
	   <!-- Lista de antecedentes -->
	   <tr>
		 <td rowspan="2" bgcolor="#ffffff" width="16">
		   <a href="javascript:submit_edit(<?=$vec[id]?>, 'antFrmInt');"><img src="./images/icons/edit_small.gif" alt="<?=$AppUI->_("Edit")?>" border='0'></a>
		 </td>
		 <td rowspan="2" bgcolor="#ffffff" width="16">
		   <a href="JavaScript:confirma(<?=$vec[id]?>)"><img src='./images/icons/trash_small.gif' alt='<?=$AppUI->_("Delete")?>' border='0'></a>
		 </td>
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
	   		<textarea cols="163" rows="2" class="text" READONLY><?=$vec['profit']?></textarea>
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


		<tr>
			<td width="16" valign="top">
			 <img src="images/1x1.gif" width="16" height="1">
			</td>
			<td width="16" valign="top">
			 <img src="images/1x1.gif" width="16" height="1">
			</td>
			 <td width="100" valign="top">
			    <input type = "hidden" name = "company_or" value = "<?=$company;?>" >
			 	<select name="company_int" class="text" style="width:120px;" onchange="xajax_addSelect_Departments('area_internal', document.antFrmInt.company_int.value,'', '<?=$modulo?>', '<?=$modulo_tab?>', '<?=$AppUI->_('All')?>')">
	         	<?
				  	$query = "SELECT company_name, company_id FROM companies WHERE company_type='0' ORDER BY company_name ASC"; 
				  	$sql = mysql_query($query);
	         
					  while($vec = mysql_fetch_array($sql) )
					  {
					  	$selected = ($vec['company_id']==$company) ? "selected" : "";
		        	echo "<option " .$selected ." value=\"$vec[company_id]\">$vec[company_name]</option>";
		        }
				  ?>
		   </select>
			 </td>
			 <td width="130" nowrap valign="top">
                  <?
				      if($area_internal == "")
					  {
					   $area_internal = "-1";
					  }
					  
				  ?>
				 
		     <script type="text/javascript">
			    xajax_addSelect_Departments('area_internal', document.antFrmInt.company_int.value ,'<?=$area_internal?>', '<?=$modulo?>', '<?=$modulo_tab?>', '<?=$AppUI->_('All')?>');		
			 </script>
			 
			 <select name="area_internal" id="area_internal" class="text" style="width:160px;" >
			 </select>
			 	
			 </td>
			 <td width="100" valign="top" >
			 	<input type="text" name="function" value="<?=($type_cia==1) ? $function : '';?>" size="20" class="text">
			 </td>
			 
			 <td align='center' nowrap="nowrap" width="105" valign="top">
				 	<?php
		    		$fecha_hoy = DATE("Y-m-d", mktime(0, 0, 0, date("m"),  date("d"),  date("Y")) );
					  $date_past_year = DATE("Y-m-d", mktime(0, 0, 0, date("m"),  date("d"),  date("Y")-1) );
					  
					  $fecha_hoy = new CDate($fecha_hoy);
					  $date_past_year = new CDate($date_past_year);
		    	?>
				  <input type="hidden" name="log_from_date" value="<?php echo $from_date ? $from_date->format( FMT_TIMESTAMP_DATE ) : $date_past_year->format( FMT_TIMESTAMP_DATE ) ;?>" />
          <input type="text" name="from_date" value="<?php echo $from_date ? $from_date->format( $df ) : $date_past_year->format( $df ) ;?>" class="text" disabled="disabled" size="10" />
				  <a href="javascript://" onClick="popCalendar('from_date', 'antFrmInt')">
				  	<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				  </a>
			 </td>
			 
			 <td align='center' nowrap="nowrap" width="105" valign="top">
					<input type="hidden" name="log_to_date" value="<?php echo $to_date ? $to_date->format( FMT_TIMESTAMP_DATE ) : $fecha_hoy->format( FMT_TIMESTAMP_DATE );?>" />
					<input type="text" name="to_date" value="<?php echo $to_date ? $to_date->format( $df ) : $fecha_hoy->format( $df );?>" class="text" disabled="disabled" size="10" />
					<a href="javascript://" onClick="popCalendar('to_date', 'antFrmInt')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				  </a>
			 </td>
			 <td width="100" valign="top" >
			 
			    <? 
			    if ($reports != "" && $reports!= '0' && $company_supervisor =="")
			    {
	                $query_sup = "SELECT user_company FROM users WHERE user_id='".$reports."' "; 
					$sql_sup = mysql_query($query_sup);	
					$c_s = mysql_fetch_array($sql_sup);	
					$company_supervisor	= $c_s[0];
			    }
			   ?>  
			    
			    <select name="company_supervisor" class="text" style="width:120px;" onchange="select_ajax_supervisor(document.antFrmInt.company_supervisor.value, '')" >
				    <option value="" ><?php echo $AppUI->_('Not Specified')?></option>
					<?
					$query = "SELECT company_name, company_id FROM companies ORDER BY company_name ASC"; 
					$sql = mysql_query($query);
					 
					while($vec = mysql_fetch_array($sql) )
					{
					 $selected = ($vec['company_id']==$company_supervisor) ? "selected" : "";
					  echo "<option " .$selected ." value=\"$vec[company_id]\">$vec[company_name]</option>";
					}
					?>
		        </select>		
		        
		        <script type="text/javascript">
			    select_ajax_supervisor('<?=$company_supervisor?>','<?=$reports?>');	
			    </script>
			  	 			
		  	 <select name="reports" id="reports" class="text" style="width:120px;"  >
			 </select>
			 
			 
			 </td>
			 <td width="100" valign="top" >		 	
			 	<select name="functional_area" class="text" style="width:120px;">
	      	<?
				  	$query = "SELECT area_name, id FROM hhrr_functional_area ORDER BY area_name ASC"; 
				  	$sql = mysql_query($query);
	         
					  while($vec = mysql_fetch_array($sql) )
					  {
					  	$selected = ($vec['id']==$functional_area) ? "selected" : "";
		        	echo "<option " .$selected ." value=\"$vec[id]\">$vec[area_name]</option>";
		        }
				  ?>
		   </select>
			 </td>
			 <td width="100" valign="top">
			 	<input id='level_management' type="text" name="level_management" value="<?=($type_cia==1) ? $level_management : '';?>" size="20" class="text">
			 </td>
	   </tr>
	   <tr>
			<td colspan='2' width="16">
			 <img src="images/1x1.gif" width="16" height="1">
			</td>	   	
	   	<td colspan='8' bgcolor="#ffffff" width="300">
	   	    <input type="hidden" name="logros_ref" value="<?=$AppUI->_('msg_profit');?>">
	   		<textarea cols="163" rows="2" name="logros" class="text"><?=($type_cia==1) ? $logros : $AppUI->_('msg_profit');?></textarea>
	   	</td>
	   </tr>
	   
	   <tr><td>&nbsp;</td></tr>
			<tr>
				<td align="right" valign="bottom" colspan='11'>
				   <input type="button" value="<?php echo $AppUI->_('save');?>" class="button" onclick="submitIt()">
				</td>
			</tr>
	 </form>
   </table>

		<br><br>




   <table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
   <form name="antFrmExt" action="" method="POST">
	 <input type="hidden" name="accion" value="newant" />
	 <input type="hidden" name="ant_id" value="<?=$ant_id;?>" />
	 <input type="hidden" name="type_cia" value="0" />
	 <input type="hidden" name="area_internal" value="0" />
	 <input type="hidden" name="functional_area" value="0" />
	 
	 <tr class="tableHeaderGral" >
      <th align="center" colspan="7"><?=$AppUI->_("Other Companies")?></th>
	 </tr>
   <tr class="tableHeaderGral" >
		 <th width="16">&nbsp;</th>
		 <th width="16">&nbsp;</th>
		 <th align="center" width="120"><?=$AppUI->_("Company")?></th>
		 <th align="center" width="100"><?=$AppUI->_("Management/Area")?></th>
		 <th align="center" width="100"><?=$AppUI->_("Function")?></th>
		 <th align="center" nowrap="nowrap" width="70" ><?=$AppUI->_("From")?></th>
		 <th align="center" nowrap="nowrap" width="70" ><?=$AppUI->_("To")?></th>
   </tr>
   <?
      $query = "SELECT id, user_id, company, internal_company , area_external, function, DATE_FORMAT(from_date,'%d-%m-%Y') as fdate ,DATE_FORMAT(to_date,'%d-%m-%Y') as tdate, profit, reports, functional_area, level_management FROM hhrr_ant WHERE user_id ='$id' AND internal_company = '0' ";

	  $sql = mysql_query($query);

	  while ($vec = mysql_fetch_array($sql)){
   ?>
	   <!-- Lista de antecedentes -->
	   <tr>
			 <td rowspan="2" bgcolor="#ffffff" width="16">
			   <a href="javascript:submit_edit(<?=$vec[id]?>, 'antFrmExt');"><img src="./images/icons/edit_small.gif" alt="<?=$AppUI->_("Edit")?>" border='0'></a>
			 </td>
			 <td rowspan="2" bgcolor="#ffffff" width="16">
			   <a href="JavaScript:confirma(<?=$vec[id]?>)"><img src='./images/icons/trash_small.gif' alt='<?=$AppUI->_("Delete")?>' border='0'></a>
			 </td>
			 <td  bgcolor="#ffffff" width="120" ><?=$vec['company'];?></td>
			 <td  bgcolor="#ffffff" width="100" ><?=$vec['area_external']?></td>
			 <td  bgcolor="#ffffff" width="100" ><?=$vec['function']?></td>
			 <td  bgcolor="#ffffff" nowrap="nowrap" width="70"><?=$vec['fdate']?></td>
			 <td  bgcolor="#ffffff" nowrap="nowrap" width="70"><?=$vec['tdate']?></td>
	   </tr>
	   <tr>
	   	<td colspan='5' bgcolor="#ffffff">
	   		<textarea cols="163" rows="2" class="text" READONLY><?=$vec['profit']?></textarea>
	   	</td>
	   </tr>
			<tr>
				<td colspan="5" bgcolor="#e9e9e9" height="3"></td>
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
	
		<tr>
			<td width="16">
			 <img src="images/1x1.gif" width="16" height="1">
			</td>
			<td width="16">
			 <img src="images/1x1.gif" width="16" height="1">
			</td>
			 <td width="120" >
			 	<input type="text" name="company_ext" value="<?=($type_cia==0) ? $company : '';?>" size="40" class="text">
			 </td>
			 <td width="100" >
			 	<input type="text" name="area_external" value="<?=($type_cia==0) ? $area_external : '';?>" size="40" class="text">
			 </td>
			 <td width="100" >
			 	<input type="text" name="function" value="<?=($type_cia==0) ? $function : '';?>" size="45" class="text">
			 </td>
			 
			 <td nowrap="nowrap" width="1%">
				 	<?php
		    		$fecha_hoy = DATE("Y-m-d", mktime(0, 0, 0, date("m"),  date("d"),  date("Y")) );
					$date_past_year = DATE("Y-m-d", mktime(0, 0, 0, date("m"),  date("d"),  date("Y")-1) );
					  
					  $fecha_hoy = new CDate($fecha_hoy);
					  $date_past_year = new CDate($date_past_year);
		    	?>
				  <input type="hidden" name="log_from_date" value="<?php echo $from_date ? $from_date->format( FMT_TIMESTAMP_DATE ) : $date_past_year->format( FMT_TIMESTAMP_DATE ) ;?>" />
          <input type="text" name="from_date" value="<?php echo $from_date ? $from_date->format( $df ) : $date_past_year->format( $df ) ;?>" class="text" disabled="disabled" size="10" />
				  <a href="javascript://" onClick="popCalendar('from_date', 'antFrmExt')">
				  	<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				  </a>
			 </td>
			 
			 <td nowrap="nowrap" width="1%">
					<input type="hidden" name="log_to_date" value="<?php echo $to_date ? $to_date->format( FMT_TIMESTAMP_DATE ) : $fecha_hoy->format( FMT_TIMESTAMP_DATE );?>" />
					<input type="text" name="to_date" value="<?php echo $to_date ? $to_date->format( $df ) : $fecha_hoy->format( $df );?>" class="text" disabled="disabled" size="10" />
					<a href="javascript://" onClick="popCalendar('to_date', 'antFrmExt')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				  </a>
			 </td>
	   </tr>

	   <tr>
			<td colspan='2' width="16">
			 <img src="images/1x1.gif" width="16" height="1">
			</td>	   	
	   	<td colspan='5' bgcolor="#ffffff" width="300">
	   	    <input type="hidden" name="logros_ref" value="<?=$AppUI->_('msg_profit');?>">
	   		<textarea cols="163" rows="2" name="logros" class="text"><?=($type_cia==0) ? $logros : $AppUI->_('msg_profit');?></textarea>
	   	</td>
	   </tr>	   
	   
	   <tr><td>&nbsp;</td></tr>
			<tr>
				<td align="right" valign="bottom" colspan='7'>
				   <input type="button" value="<?php echo $AppUI->_('save');?>" class="button"  onclick="submitItExt()">
				   </form>
				    <?
					if($_GET[a]!="personalinfo"){ 
							$salir = "index.php?m=hhrr&a=viewhhrr&tab=$tab&id=".$id;
							?>
							<input type="button" value="<?php echo $AppUI->_( 'exit' );?>" class="button" onClick="javascript:window.location='<?=$salir;?>';" />&nbsp;
							<? 
					} ?>
				</td>
			</tr>
   </table>
      <tr>
     <td align = "right" >
	   &nbsp;
	 </td>
   </tr>

</table>

