<script language='JavaScript' type='text/javascript' 	src='./lib/jsLibs/gen_validatorv2.js'></script>
<?
global $AppUI;

js();

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$tab = $_GET['tab'];

$canEditHHRR = !getDenyEdit("hhrr") || $id == $AppUI->user_id;

// si el usuario logueado no puede leer hhrr y no es ?l mismo
if (!$canEditHHRR OR !validar_permisos_hhrr($id,'compensations',-1))
	 $AppUI->redirect( "m=public&a=access_denied" );


if(!$id){
	$AppUI->redirect("m=hhrr&a=addedit&tab=0");
}

//print_r($_GET);
$df = $AppUI->getPref('SHDATEFORMAT');

$f_date = intval( $_POST[f_date] ) ? new CDate( $_POST[f_date] ) : null;

if (isset($_POST['hhrr_comp_id'])) { $hhrr_comp_id = $_POST['hhrr_comp_id']; }

$style='background-color:#E5E5E5;';

switch($_POST[accion]){   
	case "neduc":
		$hhrr_comp_remuneration = $_POST['hhrr_comp_remuneration'];
		$hhrr_comp_last_update_porc = $_POST['hhrr_comp_last_update_porc'];
		$hhrr_comp_last_update_date = substr($_POST[log_from_date],0,4)."-".substr($_POST[log_from_date],4,2)."-".substr($_POST[log_from_date],6,2);
		$hhrr_comp_gap_pc = $_POST['hhrr_comp_gap_pc'];
		$hhrr_comp_last_reward = $_POST['hhrr_comp_last_reward'];
		$hhrr_comp_anual_remuneration = $_POST['hhrr_comp_anual_remuneration'];
		$hhrr_comp_actual_benefits = $_POST['hhrr_comp_actual_benefits'];
		$hhrr_comp_gap_mer = $_POST['hhrr_comp_gap_mer'];
		$Hhrr_comp_proposed_plan = $_POST['Hhrr_comp_proposed_plan'];
	// Agrega nuevo 
	if ($_GET['hhrr_comp_id']== ""){ 
    	$sql = "INSERT INTO hhrr_comp 
    				(hhrr_comp_user_id, hhrr_comp_remuneration, hhrr_comp_last_update_porc, hhrr_comp_last_update_date, hhrr_comp_gap_pc, hhrr_comp_last_reward, hhrr_comp_actual_benefits, hhrr_comp_gap_mer, Hhrr_comp_proposed_plan)
    				VALUES ($id, '$hhrr_comp_remuneration', '$hhrr_comp_last_update_porc', '$hhrr_comp_last_update_date', '$hhrr_comp_gap_pc', '$hhrr_comp_last_reward', '$hhrr_comp_actual_benefits', '$hhrr_comp_gap_mer', '$Hhrr_comp_proposed_plan');";
    	db_exec($sql);
    	unset($hhrr_comp_id);
	}
	else{
		//echo "<br> actualizo<br>";
		$sql = "UPDATE hhrr_comp SET 
				hhrr_comp_remuneration = '$hhrr_comp_remuneration',
				hhrr_comp_last_update_porc = '$hhrr_comp_last_update_porc',
				hhrr_comp_last_update_date = '$hhrr_comp_last_update_date',
				hhrr_comp_gap_pc = '$hhrr_comp_gap_pc',
				hhrr_comp_last_reward = '$hhrr_comp_last_reward',
				hhrr_comp_gap_pc = '$hhrr_comp_gap_pc',
				hhrr_comp_actual_benefits = '$hhrr_comp_actual_benefits',
				hhrr_comp_gap_mer = '$hhrr_comp_gap_mer',
				Hhrr_comp_proposed_plan = '$Hhrr_comp_proposed_plan'
				WHERE hhrr_comp_id = '".$_GET['hhrr_comp_id']."'";
		//echo "<br>$sql<br>";
  	db_exec($sql);
  	$AppUI->redirect( "m=hhrr&a=addedit&id=$id&tab=$tab" );
	}

	unset($hhrr_comp_remuneration);
	unset($hhrr_comp_last_update_porc);
	unset($hhrr_comp_last_update_date);
	unset($hhrr_comp_gap_pc);
	unset($hhrr_comp_last_reward);
	unset($hhrr_comp_anual_remuneration);
	unset($hhrr_comp_actual_benefits);
	unset($hhrr_comp_gap_mer);
	unset($Hhrr_comp_proposed_plan);
	unset($_POST);
    
	break;

	case "delant":
	  
	 $del_query = "DELETE FROM hhrr_comp WHERE hhrr_comp_id = '$hhrr_comp_id' ";
	 $sql_del = db_exec($del_query);
     
	break;
}


//Si este editando algun registro cargo los datos
if($_GET['hhrr_comp_id']!="" AND $_GET['do']=='edit'){
		//echo "Entré";
	  $sql = "SELECT * FROM hhrr_comp WHERE hhrr_comp_id='".$_GET['hhrr_comp_id']."'";
	  $rc = db_exec($sql);
	  $data = mysql_fetch_array($rc);
	  
	  $hhrr_comp_remuneration = $data['hhrr_comp_remuneration'];
	  $hhrr_comp_last_update_porc = $data['hhrr_comp_last_update_porc'];
	  $from_date = new CDate($data['hhrr_comp_last_update_date']);
	  $hhrr_comp_gap_pc = $data['hhrr_comp_gap_pc'];
	  $hhrr_comp_last_reward = $data['hhrr_comp_last_reward'];
	  $hhrr_comp_anual_remuneration = $data['hhrr_comp_anual_remuneration'];
	  $hhrr_comp_actual_benefits = $data['hhrr_comp_actual_benefits'];
	  $hhrr_comp_gap_mer = $data['hhrr_comp_gap_mer'];
	  $Hhrr_comp_proposed_plan = $data['Hhrr_comp_proposed_plan'];
	  	  
	  $hora_editada=$from_date->format( "%d/%m/%Y" );//Guardo la hora en esta variable para no cargarla en el vector que uso para validad que no se repita la hora.
}
else $from_date = new CDate($data[s_date]);
 ?> 


<form name="editFrm" action="" method="POST">
	<input type="hidden" name="hhrr_comp_id" value="" />
	<input type="hidden" name="fila_editada" value="" />
</form>

<form name="delFrm" action="" method="POST">
	<input type="hidden" name="hhrr_comp_id" value="" />
	<input type="hidden" name="accion" value="delant" />
</form>


<?
/*
Rem Mensual actual: valor num?rico (formato moneda)
% Ultimo ajuste: porcentaje de 1 a 100 (calculo autom?tico entre REM MENSUAL
ACTUAL y REM ANTERIOR)
Gap top en PC actual: porcentaje de 1 a 100 (PC = position class)
Ult Premio: valor num?rico (cantidad de sueldos)

Rem Anual: valor num?rico c?lculo autom?tico (rem mensual actual * 13 + ult
premio* rem mensual)

Bfs Actuales: alfanum?rico (Ej. OSDE 410, Auto y Tickets)
Gap Mercado: porcentaje de 1 a 100 
Plan Propuesto: alfanum?rico (campo observaciones)
*/
?>

<table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">	
	<tr class="tableHeaderGral" >
		<th align="center" colspan="12"><?=$AppUI->_("compensations")?></th>
	</tr>
	<tr class="tableHeaderGral">
		<th width="1%">&nbsp;</th>
		<th width="1%">&nbsp;</th>
		<th width="1%"></th>
		<th align="center" width='1%'><?=$AppUI->_("actualmonthremuneration")?></th>
		<th align="center" width='1%'><?=$AppUI->_("porcentuallastupdate")?></th>
		<th align="center" width='50%'><?=$AppUI->_("lastuptdate")?></th>
		<th align="center" width='1%'><?=$AppUI->_("gaptoppcactual")?></th>
		<th align="center" width='1%'><?=$AppUI->_("lastreward")?></th>
		<th align="center" width='1%'><?=$AppUI->_("anualremuneration")?></th>
		<th align="center" width='15%'><?=$AppUI->_("actualbenefits")?></th>
		<th align="center" width='1%'><?=$AppUI->_("marketgap")?></th>
		<th align="center" width='30%'><?=$AppUI->_("proposedplan")?></th>
	</tr>
		<script LANGUAGE="JavaScript"> 
			 var vec=new Array();
		</script>
	<?
  	$cont=0;
    $sql = "SELECT
				  CONCAT(SUBSTRING(h1.hhrr_comp_last_update,9,2),'-',SUBSTRING(h1.hhrr_comp_last_update,6,2),'-',SUBSTRING(h1.hhrr_comp_last_update,3,2)) AS date,
				  h1.hhrr_comp_remuneration,
				  h1.hhrr_comp_id,
				  h1.hhrr_comp_user_id,
				  max(h2.hhrr_comp_last_update_date) AS vfecha,
				  IF (h2.hhrr_comp_remuneration<>0,CONCAT(ROUND((h1.hhrr_comp_remuneration/max(h2.hhrr_comp_remuneration)*100)-100),'%'),'N/A') AS hhrr_comp_last_update_porc,
				  h1.hhrr_comp_last_update_date,
				  CONCAT(SUBSTRING(h1.hhrr_comp_last_update_date,9,2),'-',SUBSTRING(h1.hhrr_comp_last_update_date,6,2),'-',SUBSTRING(h1.hhrr_comp_last_update_date,3,2)) AS hhrr_comp_last_update_date,
				  CONCAT(SUBSTRING(h1.hhrr_comp_last_update_date,9,2),'/',SUBSTRING(h1.hhrr_comp_last_update_date,6,2),'/',SUBSTRING(h1.hhrr_comp_last_update_date,1,4)) AS fecha_completa,
				  h1.hhrr_comp_gap_pc,
				  h1.hhrr_comp_last_reward,
				  (h1.hhrr_comp_remuneration*13+h1.hhrr_comp_last_reward) AS hhrr_comp_anual_remuneration,
				  h1.hhrr_comp_actual_benefits,
				  h1.hhrr_comp_gap_mer,
				  h1.Hhrr_comp_proposed_plan
				FROM hhrr_comp AS h1
				LEFT JOIN hhrr_comp AS h2
				  ON (
				    h1.hhrr_comp_user_id=h2.hhrr_comp_user_id AND
				    h1.hhrr_comp_last_update_date > h2.hhrr_comp_last_update_date)
				WHERE h1.hhrr_comp_user_id ='$id'
				GROUP BY h1.hhrr_comp_remuneration, h1.hhrr_comp_last_update_date
				ORDER BY h1.hhrr_comp_last_update_date DESC, h2.hhrr_comp_last_update_date DESC";
   
  	$rc = db_exec($sql);
	while ($vec = db_fetch_array($rc)){
   ?>
	<tr>
		<td width="16" bgcolor="#ffffff">	   
			<a href="?m=hhrr&a=addedit&do=edit&id=<?php echo $id; ?>&tab=<?=$tab?>&hhrr_comp_id=<?php echo $vec[hhrr_comp_id]; ?>">
			<img src="./images/icons/edit_small.gif" alt="<?=$AppUI->_("Edit")?>" border="0"></a>
		</td>		   	
		<td width="16" bgcolor="#ffffff">	   
			<a href="JavaScript:confirma(<?=$vec[hhrr_comp_id]?>)"><img src='./images/icons/trash_small.gif' alt='<?=$AppUI->_("Delete")?>' border='0'></a>
		</td>
		<td align='center' bgcolor="#ffffff" nowrap><?echo $vec['date'];?></td>
		<td align='center'  bgcolor="#ffffff" >
				<input type="hidden" name="comp_remuneration_<?=$cont; $cont++;?>" value="<?=$vec['hhrr_comp_remuneration']?>" />
				<?="$ ".$vec['hhrr_comp_remuneration']?>
		</td>
		<td align='center' nowrap width="65" bgcolor="#ffffff"><?echo $vec['hhrr_comp_last_update_porc'];?></td>
		<script LANGUAGE="JavaScript">
			if(<? echo ($vec['fecha_completa'] != $hora_editada)? "true" : "false"?>)
			 vec[vec.length]="<?= $vec['fecha_completa'];?>";
		</script>
		<td align='center' bgcolor="#ffffff" nowrap><?echo $vec['hhrr_comp_last_update_date'];?></td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_gap_pc']."%";?></td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_last_reward'];?></td>			
		<td align='center' bgcolor="#ffffff"><?echo "$ ".$vec['hhrr_comp_anual_remuneration'];?></td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_actual_benefits'];?></td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['hhrr_comp_gap_mer']."%";?></td>
		<td align='center' bgcolor="#ffffff"><?echo $vec['Hhrr_comp_proposed_plan'];?></td>
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

	<tr>
		<form name="editform" action="" method="POST"><input type="hidden" name="accion" value="neduc" />
		<input type="hidden" name="hhrr_comp_id" value="<? echo $_GET['hhrr_comp_id'];?>" /> 
		<input type="hidden" name="fila_editada" value="<?=$hhrr_comp_id;?>" />
		<td width="20">
	   		<img src="images/1x1.gif" width="20" height="1">
	  	</td>
	  	<td width="16">
	   		<img src="images/1x1.gif" width="16" height="1">
	  	</td>
		<td width="16"><img src="images/1x1.gif" width="16" height="1"></td>
		<td align='center' >
			$ <input type="text" size='7' name="hhrr_comp_remuneration" value="<?=$hhrr_comp_remuneration;?>" class="text">
		</td>
		<td align='center'>
			<input type="text" size='5' style='<?=$style;?>' disabled name="hhrr_comp_last_update_porc" value="<?=$hhrr_comp_last_update_porc;?>" class="text">%&nbsp;
		</td>
		<td align='center' nowrap>
			<input type='hidden' id="log_from_date" name='log_from_date' value='<?php echo $from_date ? $from_date->format( FMT_TIMESTAMP_DATE ) : '';?>' />
			<input type='text' name='from_date' id="from_date" value='<?php echo $from_date ? $from_date->format( $df ) : '';?>' class='text' disabled='disabled' size='10' />
			<input name="from_date_format" value="%d/%m/%Y" type="hidden">
			<a href='#' onClick="popCalendar('from_date', 'editform')"> <img src='./images/calendar.gif' width='24' height='12' alt='<?php echo $AppUI->_('Calendar');?>' border='0' /> </a>
		</td>
		<td align='center' nowrap width="65">
		 	<input type="text" size='5' name='hhrr_comp_gap_pc' value="<?=$hhrr_comp_gap_pc;?>" class="text">%&nbsp;
		</td>
		<td align='center'>
		 	<input type="text" size='5' name='hhrr_comp_last_reward' value="<?=$hhrr_comp_last_reward;?>" class="text">
		</td>
		<td align='center'>
		 	$<input type="text" size='5' name='hhrr_comp_anual_remuneration' style='<?=$style;?>' disabled value="<?=$hhrr_comp_anual_remuneration;?>" class="text">
		</td>
		<td align='center'>
		 	<input type="text" size='5' name='hhrr_comp_actual_benefits' value="<?=$hhrr_comp_actual_benefits;?>" class="text">
		</td>
		<td align='center'>
		 	<input type="text" size='5' name='hhrr_comp_gap_mer' value="<?=$hhrr_comp_gap_mer;?>" class="text">%&nbsp;
		</td>
		<td align='center'>
		 	<textarea cols='35' rows='2' name='Hhrr_comp_proposed_plan'><? echo $Hhrr_comp_proposed_plan;?></textarea>
		</td>
	</tr>
   	<tr>
		<td align="right" colspan='12'>
			<input type="submit" value="<?php echo $AppUI->_('save');?>" class="button" >					
			<?
			if($_GET[a]!="personalinfo"){
				$salir = "index.php?m=hhrr&a=viewhhrr&tab=$tab&id=".$id;?> <input
				type="button" value="<?php echo $AppUI->_( 'exit' );?>"
				class="button" onClick="javascript:window.location='<?=$salir;?>';" />
				<?
			} ?>
		</td>	
	</form>
	</tr> 
</table>

<SCRIPT language="JavaScript"> 	
//Valida que la fecha ingresada no este previamente cargada
function DoCustomValidation()
{
	  var frm = document.editform;
	  var from_date=document.getElementById("from_date").value;
      var rta = true;
      
	  for(var cont = 0; cont < vec.length; cont++)
	  {
			if (vec[cont]==from_date)
			{
				alert1("<?= $AppUI->_("The dates can't be repeated"); ?>");
				rta = false;
			}
	  }
	  
	  var today = new Date();
    
      var vec_fecha = frm.from_date.value.split("/");
      var from_date_txt = new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
      
      if (from_date_txt > today && rta){
    	alert("<?php echo $AppUI->_('DateErrorMayor');?>");
    	rta = false;
      }
      
      
	  return rta;
}
	

	var frmvalidator = new Validator("editform");
	
 	frmvalidator.addValidation("hhrr_comp_remuneration","req","<? echo $AppUI->_("TheFieldactualmonthremunerationIsRequired"); ?>");
 	frmvalidator.addValidation("hhrr_comp_remuneration","numeric","<? echo $AppUI->_("Thefieldactualmonthremunerationmustbenumeric"); ?>");
 	frmvalidator.addValidation("hhrr_comp_last_reward","numeric","<? echo $AppUI->_("Thefieldhhrrcomplastrewardnmustbenumeric"); ?>");
 	
 	frmvalidator.setAddnlValidationFunction("DoCustomValidation"); 	
</SCRIPT>

<?php
function js(){
	global $AppUI;
	?>
	<script language="Javascript">
	<!--
	var calendarField = '';

	function popCalendar( field, FrmName ){
		calendarField = field;
		CalFrmName = FrmName;
		idate = eval( 'document.'+FrmName+'.log_'+field+'.value' );
		window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
	}

	/**
	 *	@param string Input date in the format YYYYMMDD
	 *	@param string Formatted date
	 */
	var today= "<?php date('Ymd')?>";
	function setCalendar( idate, fdate ) {
		if(idate<today){
			alert1("La fecha debe ser mayor o igual a la del d&iacute;a de hoy.");
		}else{
			fld_date = eval( 'document.'+CalFrmName+'.log_'+calendarField );
			fld_fdate = eval( 'document.'+CalFrmName+'.'+calendarField );
			fld_date.value = idate;
			fld_fdate.value = fdate;
		}
	}
	
	function confirma(obj){
		var f = document.delFrm;
		f.hhrr_comp_id.value = obj;

		var borrar=confirm1("<?= $AppUI->_("doDelete"); ?>");

		if (borrar)
		{
			f.submit();
		}
	}
	-->
	</script>
<?php
}
?>
