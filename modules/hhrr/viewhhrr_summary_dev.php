<?
global $AppUI;
$id = isset($_GET['id']) ? $_GET['id'] : 0;

if(!$id){
	$AppUI->redirect("m=hhrr&a=addedit&tab=0");
}

$df = $AppUI->getPref('SHDATEFORMAT');

$f_date = intval( $_POST[f_date] ) ? new CDate( $_POST[f_date] ) : null;



  $select = "SELECT * FROM hhrr_dev WHERE hhrr_dev_user_id=$id LIMIT 3";
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
	$hhrr_dev_mov_af1 =($data['hhrr_dev_mov_af1']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_af1'] ) : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$hhrr_dev_mov_asa1 =($data['hhrr_dev_mov_asa1']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_asa1'] ) : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$hhrr_dev_mov_af2 =($data['hhrr_dev_mov_af2']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_af2'] ) : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$hhrr_dev_mov_asa2 =($data['hhrr_dev_mov_asa2']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_asa2'] ) : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$hhrr_dev_mov_af3 =($data['hhrr_dev_mov_af3']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_af3'] ) : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$hhrr_dev_mov_asa3 =($data['hhrr_dev_mov_asa3']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_asa3'] ) : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$hhrr_dev_int_a = $data['hhrr_dev_int_a'];
	$hhrr_dev_exp = $data['hhrr_dev_exp'];
 	
?>

<table id='table_development' cellspacing="1" cellpadding="5" border="0" width="100%" align="center">
	<form name="eduFrm" action="" method="POST">
	<input type="hidden" name="accion" value="neduc" />
<tr>
	<?//Col 1 ?>
	<td valign="top" >
		
		<table cellspacing="1" cellpadding="0" border="0" width="100%" align="center">
			<tr>
				<td height="135" valign="top">
					<table cellspacing="1" cellpadding="0" border="0" width="100%" align="center">
						<tr class="tableHeaderGral" >
							<th align="center" colspan="4"><?=$AppUI->_("Potential review")?></th>
						</tr>
						<tr class="tableHeaderGral">
					  	<td colspan="2" align="center"><?=$AppUI->_("Management")?></td>
							<td colspan="2" align="center"><?=$AppUI->_("Technical Functional")?></td>
						</tr>
						<tr>
					  	<td align="center">1 <?=$AppUI->_("Level")?></td>
							<td align="center">
								<?=$hhrr_dev_eval_g_1;?>
							</td>
							<td align="center">1 <?=$AppUI->_("Nivel")?></td>
							<td align="center">
								<?=$hhrr_dev_eval_t_1;?>
							</td>
						</tr>
						<tr>
					  	<td align="center"><?=$AppUI->_("More than 1 Level")?></td>
							<td align="center">
								<?=$hhrr_dev_eval_g_S;?>
							</td>
							<td align="center"><?=$AppUI->_("More than 1 Level")?></td>
							<td align="center">
								<?=$hhrr_dev_eval_t_S;?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		
			<tr>
				<td>
					<br><br>
				</td>
			</tr>	
			
			<tr class="tableHeaderGral" >
				<td>
					<?=$AppUI->_("Development ideas")?>
				</td>
			</tr>
			<tr>
				<td >
					<?=$hhrr_dev_sug;?>
				</td>
			</tr>
		
			<tr>
				<td>
					<br><br>
				</td>
			</tr>
		
			<tr>
				<td>
					<table cellspacing="1" cellpadding="0" border="0" width="100%">
						<tr class="tableHeaderGral" >
							<th align="center" colspan="2"><?=$AppUI->_("Could Replace")?>:</th>
						</tr>
						
						<tr>
					  	<td align="center">
					  		<?=$AppUI->_("ST")?>: 
					  		<?=$hhrr_dev_rst;?>
					  	</td>
							<td align="center">
								<?=$AppUI->_("Functional Area Key")?>: 
								<? echo ($hhrr_dev_pos_k)? $AppUI->_("Yes") : $AppUI->_("No"); ?>
							</td>
						</tr>
		
						<tr>
					  	<td align="center">
					  		<?=$AppUI->_("MT")?>: 
					  		<?=$hhrr_dev_rmt;?>
					  	</td>
							<td align="center">
								<?=$AppUI->_("")?>
							</td>
						</tr>
		
						<tr>	
					  	<td align="center">
					  		<?=$AppUI->_("LP: ")?>
					  		<?=$hhrr_dev_rlt;?>
					  	</td>
							<td align="center">
								<?=$AppUI->_("Position Key Person")?>: 
								<? echo ($hhrr_dev_per_k)? $AppUI->_("Yes") : $AppUI->_("No"); ?>
							</td>
						</tr>
		
						</tr>
					</table>
				</td>
			</tr>	
		
			<tr>
				<td>
					<br><br>
				</td>
			</tr>		
		
		</table>
	</td>
	
	
	<? //Col 2 ?>
	<td valign="top" >
		<table cellspacing="1" cellpadding="0" border="0" width="100%" height="135">
			<tr class="tableHeaderGral" >
				<th align="center" colspan="5"><?=$AppUI->_("Work Profile - Mobility area")?></th>
			</tr>			

			<tr>
		  	<td align="right"><?=$AppUI->_("Functional Area 1")?>:&nbsp;</td>
				<td align="left"><?=$hhrr_dev_mov_af1;?></td>
			</tr>
			<tr>
		  	<td align="right"><?=$AppUI->_("Sub Area")?>:&nbsp;</td>
				<td align="left"><?=$hhrr_dev_mov_asa1;?></td>
			</tr>
	
			<tr>
		  	<td colspan="2">&nbsp;</td>
			</tr>
			
			<tr>
		  	<td align="right"><?=$AppUI->_("Functional Area 2")?>:&nbsp;</td>
				<td align="left"><?=$hhrr_dev_mov_af2;?></td>
			</tr>
			<tr>
		  	<td align="right"><?=$AppUI->_("Sub Area")?>:&nbsp;</td>
				<td align="left"><?=$hhrr_dev_mov_asa2;?></td>
			</tr>
			<tr>
		  	<td colspan="2" >&nbsp;</td>
			</tr>
			
			<tr>
		  	<td align="right"><?=$AppUI->_("Functional Area 3")?>:&nbsp;</td>
				<td align="left"><?=$hhrr_dev_mov_af3;?></td>
			</tr>
			<tr>
		  	<td align="right"><?=$AppUI->_("Sub Area")?>:&nbsp;</td>
				<td align="left"><?=$hhrr_dev_mov_asa3;?></td>
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
				<td>
					<?=$AppUI->_("Employee interest areas")?>
				</td>
			</tr>
			<tr>
				<td>
					<?=$hhrr_dev_int_a;?>
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
				<td>
					<?=$AppUI->_("Employee personal development expectations")?>
				</td>
			</tr>
			<tr>
				<td>
					<?=$hhrr_dev_exp;?>
				</td>
			</tr>
		</table>
		</form>
		
	</td>
</tr>

<tr>
	<td>
		<br>
	</td>
</tr>

<tr>
	<td align="right" colspan='2'>
		
<? include_once('viewhhrr_summary_dev_pf.php'); ?>
			
	</td>
</tr>

</table>
