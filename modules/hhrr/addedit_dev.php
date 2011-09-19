<?
global $AppUI,$xajax;
/*
echo "<pre>";
print_r($_POST);
echo "</pre>";
*/
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$tab = $_GET['tab'];

$canEditHHRR = !getDenyEdit("hhrr") || $id == $AppUI->user_id;

// si el usuario logueado no puede leer hhrr y no es ?l mismo
if (!$canEditHHRR OR !validar_permisos_hhrr($id,'development',-1))
	 $AppUI->redirect( "m=public&a=access_denied" );


if(!$id){
	$AppUI->redirect("m=hhrr&a=addedit&tab=0");
}

$df = $AppUI->getPref('SHDATEFORMAT');

$f_date = intval( $_POST[f_date] ) ? new CDate( $_POST[f_date] ) : null;


switch($_POST[accion]) 
{   
	case "neduc":

  $update = "REPLACE INTO hhrr_dev
  (hhrr_dev_user_id, 
  hhrr_dev_eval_g_1,
	hhrr_dev_eval_g_S,
	hhrr_dev_eval_t_1,
	hhrr_dev_eval_t_S,
	hhrr_dev_sug,
	hhrr_dev_rst,
	hhrr_dev_rmt,
	hhrr_dev_rlt,
	hhrr_dev_pos_k,
	hhrr_dev_per_k,
	hhrr_dev_mov_af1,
	hhrr_dev_mov_asa1,
	hhrr_dev_mov_af2,
	hhrr_dev_mov_asa2,
	hhrr_dev_mov_af3,
	hhrr_dev_mov_asa3,
	hhrr_dev_int_a,
	hhrr_dev_exp)
  VALUES ($id, 
  '".$_POST['hhrr_dev_eval_g_1']."', 
  '".$_POST['hhrr_dev_eval_g_S']."', 
  '".$_POST['hhrr_dev_eval_t_1']."',
  '".$_POST['hhrr_dev_eval_t_S']."',
  '".$_POST['hhrr_dev_sug']."',
  '".$_POST['hhrr_dev_rst']."',
  '".$_POST['hhrr_dev_rmt']."',
  '".$_POST['hhrr_dev_rlt']."',
  '".$_POST['hhrr_dev_pos_k']."',
  '".$_POST['hhrr_dev_per_k']."',
  '".$_POST['hhrr_dev_mov_af1']."',
  '".$_POST['hhrr_dev_mov_asa1']."',
  '".$_POST['hhrr_dev_mov_af2']."',
  '".$_POST['hhrr_dev_mov_asa2']."',
  '".$_POST['hhrr_dev_mov_af3']."',
  '".$_POST['hhrr_dev_mov_asa3']."',
  '".$_POST['hhrr_dev_int_a']."',
  '".$_POST['hhrr_dev_exp']."');";
	  
	$sql = db_exec($update);
	//echo $update;
	unset($_POST);
    
	break;
}


  $select = "SELECT * FROM hhrr_dev WHERE hhrr_dev_user_id=$id";
  $sql_sel = mysql_query($select);
  $data = mysql_fetch_array($sql_sel);

  $hhrr_dev_eval_g_1 = $data['hhrr_dev_eval_g_1'];
	$hhrr_dev_eval_g_S = $data['hhrr_dev_eval_g_S'];
	$hhrr_dev_eval_t_1 = $data['hhrr_dev_eval_t_1'];
	$hhrr_dev_eval_t_S = $data['hhrr_dev_eval_t_S'];
	$hhrr_dev_sug = $data['hhrr_dev_sug'];
	$hhrr_dev_rst = $data['hhrr_dev_rst'];
	$hhrr_dev_rmt = $data['hhrr_dev_rmt'];
	$hhrr_dev_rlt = $data['hhrr_dev_rlt'];
	$hhrr_dev_pos_k = $data['hhrr_dev_pos_k'];
	$hhrr_dev_per_k = $data['hhrr_dev_per_k'];
	$hhrr_dev_mov_af1 = $data['hhrr_dev_mov_af1'];
	$hhrr_dev_mov_asa1 = $data['hhrr_dev_mov_asa1'];
	$hhrr_dev_mov_af2 = $data['hhrr_dev_mov_af2'];
	$hhrr_dev_mov_asa2 = $data['hhrr_dev_mov_asa2'];
	$hhrr_dev_mov_af3 = $data['hhrr_dev_mov_af3'];
	$hhrr_dev_mov_asa3 = $data['hhrr_dev_mov_asa3'];
	$hhrr_dev_int_a = $data['hhrr_dev_int_a'];
	$hhrr_dev_exp = $data['hhrr_dev_exp'];
	
	$user_company = db_loadResult( "select user_company from users where user_id = $id" );
?>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
	<td>
		&nbsp;
	</td>
</tr>
</table>

<table cellspacing="1" cellpadding="5" border="0" width="100%" style="border: 1px solid #000000;" class="tableForm_bg">
	<form name="eduFrm" action="" method="POST">
	<input type="hidden" name="accion" value="neduc" />
<tr>
	<td>

		<table cellspacing="1" cellpadding="0" border="0" width="100%">
			<tr class="tableHeaderGral" >
				<th align="center" colspan="6"><?=$AppUI->_("Perfil Laboral - &Aacute;reas de movilidad")?></th>
			</tr>			
			<tr>
		  	<td align="center"><?=$AppUI->_("Functional Area 1")?></td>
				<td align="center">
					<?
						$sql = "SELECT * from hhrr_functional_area where area_parent=0 AND area_company='$user_company';";
						$list = db_loadHashList( $sql );
						
						if (!$list)//Si la empresa no tiene ningun area asociada lo informo
							$list=array(0=>$AppUI->_("No data available"));

						echo arraySelect( $list, 'hhrr_dev_mov_af1', 'id="hhrr_dev_mov_af1" size="1" class="text" onchange="xajax_addSubAreas(\'hhrr_dev_mov_asa1\', document.eduFrm.hhrr_dev_mov_af1.value)"', $hhrr_dev_mov_af1 , true, TRUE, '120px' ); 
					?>

		  	<td align="center"><?=$AppUI->_("Functional Area 2")?></td>
				<td align="center">
					<?
						echo arraySelect( $list, 'hhrr_dev_mov_af2', 'id="hhrr_dev_mov_af2" size="1" class="text" onchange="xajax_addSubAreas(\'hhrr_dev_mov_asa2\', document.eduFrm.hhrr_dev_mov_af2.value)"', $hhrr_dev_mov_af2 , true, TRUE, '120px' ); 
					?>
				</td>
				</td>
				
		  	<td align="center"><?=$AppUI->_("Functional Area 3")?></td>
				<td align="center">
					<?
						echo arraySelect( $list, 'hhrr_dev_mov_af3', 'id="hhrr_dev_mov_af3" size="1" class="text" onchange="xajax_addSubAreas(\'hhrr_dev_mov_asa3\', document.eduFrm.hhrr_dev_mov_af3.value)"', $hhrr_dev_mov_af3 , true, TRUE, '120px' ); 
					?>					
				</td>
			</tr>
			
			<? /*Llamo a la funcion que actualiza la sub lista
					* xajax_addSubAreas(El id del select, el valor seleccionado del area,el valor que se va a seleccionar);
					*/
			?>
			<script type="text/javascript">
				xajax_addSubAreas('hhrr_dev_mov_asa1', document.eduFrm.hhrr_dev_mov_af1.value,<?=($hhrr_dev_mov_asa1)? $hhrr_dev_mov_asa1 : "-1";?>);
				xajax_addSubAreas('hhrr_dev_mov_asa2', document.eduFrm.hhrr_dev_mov_af2.value,<?=($hhrr_dev_mov_asa2)? $hhrr_dev_mov_asa2 : "-1";?>);
				xajax_addSubAreas('hhrr_dev_mov_asa3', document.eduFrm.hhrr_dev_mov_af3.value,<?=($hhrr_dev_mov_asa3)? $hhrr_dev_mov_asa3 : "-1";?>);
			</script>
			
			<tr>
		  	<td align="center"><?=$AppUI->_("Sub Area")?></td>
				<td align="center">
					<select name="hhrr_dev_mov_asa1" id="hhrr_dev_mov_asa1" size="1" class="text" style="width: 120px;"></select>
				</td>
		  	<td align="center"><?=$AppUI->_("Sub Area")?></td>
				<td align="center">
					<select name="hhrr_dev_mov_asa2" id="hhrr_dev_mov_asa2" size="1" class="text" style="width: 120px;"></select>
				</td>
		  	<td align="center"><?=$AppUI->_("Sub Area")?></td>
				<td align="center">
		  		<select name="hhrr_dev_mov_asa3" id="hhrr_dev_mov_asa3" size="1" class="text" style="width: 120px;"></select>
				</td>	
			</tr>
			
			<tr>
				<td>
					<br>
				</td>
			</tr>
			
		</table>


		<table cellspacing="0" cellpadding="0" border="0" width="100%" class="tableForm_bg">
			<tr>
				<td>
					<table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
						<tr class="tableHeaderGral" >
							<td align="center" colspan="2"><?=$AppUI->_("Could Replace")?>:</td>
						</tr>
						
						<tr>
					  	<td align="center">
					  		<?=$AppUI->_("ST");?>: 
					  		<input type="text" size='20' name="hhrr_dev_rst" value="<?=$hhrr_dev_rst;?>" class="text">
					  	</td>
							<td align="center">
								<?=$AppUI->_("Functional Area Key")?>
								<input type="checkbox" name="hhrr_dev_pos_k" value="" <? echo ($hhrr_dev_pos_k)? "checked" : ""; ?>>
							</td>
						</tr>
		
						<tr>
					  	<td align="center">
					  		<?=$AppUI->_("MT")?>: 
					  		<input type="text" size='20' name="hhrr_dev_rmt" value="<?=$hhrr_dev_rmt;?>" class="text">
					  	</td>
							<td align="center">
							</td>
						</tr>
		
						<tr>	
					  	<td align="center">
					  		<?=$AppUI->_("LT")?>: 
					  		<input type="text" size='20' name="hhrr_dev_rlt" value="<?=$hhrr_dev_rlt;?>" class="text">
					  	</td>
							<td align="center">
								<?=$AppUI->_("Persona Clave")?>
								<input type="checkbox" name="hhrr_dev_per_k" value="hhrr_dev_per_k" <? echo ($hhrr_dev_per_k)? "checked" : ""; ?> >
							</td>
						</tr>
						
					</table>			
				</td>
				
				<td valign='TOP'>		
					<table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
						<tr class="tableHeaderGral" >
							<td align="center" colspan="4"><?=$AppUI->_("Potential review")?></td>
						</tr>
						<tr class="tableHeaderGral">
					  	<td colspan="2" align="center"><?=$AppUI->_("Management")?></td>
							<td colspan="2" align="center"><?=$AppUI->_("Technical Functional")?></td>
						</tr>
						<tr>
					  	<td align="center">1 <?=$AppUI->_("Level")?></td>
							<td align="center">
								<input type="text" size='10' name="hhrr_dev_eval_g_1" value="<?=$hhrr_dev_eval_g_1;?>" class="text">
							</td>
							<td align="center">1 <?=$AppUI->_("Level")?></td>
							<td align="center">
								<input type="text" size='10' name="hhrr_dev_eval_t_1" value="<?=$hhrr_dev_eval_t_1;?>" class="text">
							</td>
						</tr>
						<tr>
					  	<td align="center"><?=$AppUI->_("More than 1 Level")?></td>
							<td align="center">
								<input type="text" size='10' name="hhrr_dev_eval_g_S" value="<?=$hhrr_dev_eval_g_S;?>" class="text">
							</td>
							<td align="center"><?=$AppUI->_("More than 1 Level")?></td>
							<td align="center">
								<input type="text" size='10' name="hhrr_dev_eval_t_S" value="<?=$hhrr_dev_eval_t_S;?>" class="text">
							</td>
						</tr>
					</table>
				</td>
			</tr>

		<table cellspacing="1" cellpadding="0" border="0" width="100%">
			<tr>
				<td>
					<br><br>
				</td>
			</tr>
		</table>

		<table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
			<tr class="tableHeaderGral" >
				<th aling='center'>
					<?=$AppUI->_("Employee interest areas")?>
				</th>
			</tr>
			<tr>
				<td>
					<textarea cols='131' rows='4' name="hhrr_dev_int_a"><?=$hhrr_dev_int_a;?></textarea>
				</td>
			</tr>
		</table>

		<table cellspacing="1" cellpadding="0" border="0" width="100%">
			<tr>
				<td>
					<br><br>
				</td>
			</tr>
		</table>

		<table cellspacing="1" cellpadding="0" border="0" width="100%">
			<tr class="tableHeaderGral" >
				<th align='center'>
					<?=$AppUI->_("Employee personal development expectations")?>
				</th>
			</tr>
			<tr>
				<td>
					<textarea cols='131' rows='4' name="hhrr_dev_exp"><?=$hhrr_dev_exp;?></textarea>
				</td>
			</tr>
		</table>

		<table cellspacing="1" cellpadding="0" border="0" width="100%">
			<tr>
				<td>
					<br><br>
				</td>
			</tr>
		</table>
	
		<table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
			<tr class="tableHeaderGral" >
				<th align='center'>
					<?=$AppUI->_("Development ideas")?>
				</th>
			</tr>
			<tr>
				<td>
					<textarea cols='131' rows='4' name="hhrr_dev_sug"><?=$hhrr_dev_sug;?></textarea>
				</td>
			</tr>
		</table>
	
	</td>
</tr>
<tr>
	<td align="right" colspan='2'>
		<input type="submit" value="<?php echo $AppUI->_('save');?>" class="button" onClick="send()">
		<?
			if($_GET[a]!="personalinfo"){ 
				$salir = "index.php?m=hhrr&a=viewhhrr&tab=$tab&id=$id";?>
				<input type="button" value="<?php echo $AppUI->_( 'exit' );?>" class="button" onClick="javascript:window.location='<?=$salir;?>';" />
				<? 
			} 
		?>
	</td>
</tr>   
</form>
</table>

<table cellspacing="1" cellpadding="0" border="0" width="100%">
<tr>
	<td>
		<br>
	</td>
</tr>
</table>

<table cellspacing="1" cellpadding="5" border="0" width="100%" style="border: 1px solid #000000;" class="tableForm_bg">
<tr>
	<td align="right" colspan='2'>
		
<? include_once('addedit_dev_pf.php'); ?>
			
	</td>
</tr>
</table>

<table cellspacing="1" cellpadding="0" border="0" width="100%">
<tr>
	<td>
		<br>
	</td>
</tr>
</table>  
 

<script language="javascript">

	function send()
	{
	   var f = document.eduFrm;
	
		if ( f.hhrr_dev_pos_k.checked == true )
			f.hhrr_dev_pos_k.value = 1;
		else
			f.hhrr_dev_pos_k.value = 0;

		if ( f.hhrr_dev_per_k.checked == true )
			f.hhrr_dev_per_k.value = 1;
		else
			f.hhrr_dev_per_k.value = 0;
	
		f.submit();
  }
  
</script>