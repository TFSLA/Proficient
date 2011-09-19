<?
global $AppUI;

$id = isset($_GET['id']) ? $_GET['id'] : 0;
if(!$id)
	$AppUI->redirect("m=hhrr&a=addedit&tab=0");
	
$tab = $_GET['tab'];

$canReadHHRR = !getDenyRead("hhrr");

// si el usuario logueado no puede leer hhrr
if (!$canReadHHRR OR !validar_permisos_hhrr($id,'development',1))
	 $AppUI->redirect( "m=public&a=access_denied" );

$df = $AppUI->getPref('SHDATEFORMAT');

$f_date = intval( $_POST[f_date] ) ? new CDate( $_POST[f_date] ) : null;


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

$hhrr_dev_mov_af1 =($data['hhrr_dev_mov_af1']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_af1'] ) : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$hhrr_dev_mov_asa1 =($data['hhrr_dev_mov_asa1']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_asa1'] ) : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$hhrr_dev_mov_af2 =($data['hhrr_dev_mov_af2']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_af2'] ) : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$hhrr_dev_mov_asa2 =($data['hhrr_dev_mov_asa2']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_asa2'] ) : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$hhrr_dev_mov_af3 =($data['hhrr_dev_mov_af3']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_af3'] ) : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$hhrr_dev_mov_asa3 =($data['hhrr_dev_mov_asa3']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_asa3'] ) : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

$hhrr_dev_int_a = $data['hhrr_dev_int_a'];
$hhrr_dev_exp = $data['hhrr_dev_exp'];

?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td>&nbsp;</td>
	</tr>
</table>

<table cellspacing="1" cellpadding="5" border="0" width="100%"
	style="border: 1px solid #000000;" class="tableForm_bg">
	<tr>
		<td>
		<table cellspacing="1" cellpadding="0" border="0" width="100%">
			<tr class="tableHeaderGral">
				<th align="center" colspan="7"><?=$AppUI->_("Work Profile - Mobility area")?></th>
			</tr>

			<tr>
				<td align="right"><?=$AppUI->_("Functional Area 1")?>:&nbsp;</td>
				<td align="left" bgcolor="#ffffff"><?=$hhrr_dev_mov_af1;?></td>

				<td align="right"><?=$AppUI->_("Functional Area 2")?>:&nbsp;</td>
				<td align="left" bgcolor="#ffffff"><?=$hhrr_dev_mov_af2;?></td>

				<td align="right"><?=$AppUI->_("Functional Area 3")?>:&nbsp;</td>
				<td align="left" bgcolor="#ffffff"><?=$hhrr_dev_mov_af3;?></td>
			</tr>

			<tr>
				<td align="right"><?=$AppUI->_("Sub Area")?> 1:&nbsp;</td>
				<td align="left" bgcolor="#ffffff"><?=$hhrr_dev_mov_asa1;?></td>

				<td align="right"><?=$AppUI->_("Sub Area")?> 2:&nbsp;</td>
				<td align="left" bgcolor="#ffffff"><?=$hhrr_dev_mov_asa2;?></td>

				<td align="right"><?=$AppUI->_("Sub Area")?> 3:&nbsp;</td>
				<td align="left" bgcolor="#ffffff"><?=$hhrr_dev_mov_asa3;?></td>
				<td width="40">&nbsp;</td>
			</tr>
			
			<tr>
				<td><br></td>
			</tr>
		</table>
		<table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
			<tr>
				<td>
				<table cellspacing="1" cellpadding="0" border="0" width="100%"
					class="tableForm_bg">
					<tr class="tableHeaderGral">
						<th align="center" colspan="2"><?=$AppUI->_("Could Replace")?>:</th>
					</tr>
					<tr>
						<td align="center" width='40%'>
						<table width='100%'>
							<tr>
								<td align="right" width='60%'><?=$AppUI->_("ST")?>:&nbsp;</td>
								<td align="left" width='40%' bgcolor="#ffffff"><?=$hhrr_dev_rst;?></td>
							</tr>
						</table>
						</td>
						<td align="center" width='60%'>
						<table width='100%'>
							<tr>
								<td align="right" width='60%'><?=$AppUI->_("Position Key Person")?>:</td>
								<td align="left" width='40%' bgcolor="#ffffff"><? echo ($hhrr_dev_pos_k)? $AppUI->_("Yes") : $AppUI->_("No"); ?></td>
							</tr>
						</table>
						</td>
					</tr>
					<tr>
						<td align="center">
						<table width='100%'>
							<tr>
								<td align="right" width='60%'><?=$AppUI->_("MT")?>:&nbsp;</td>
								<td align="left" width='40%' bgcolor="#ffffff"><?=$hhrr_dev_rmt;?></td>
							</tr>
						</table>
						</td>
						<td align="center"><?=$AppUI->_("")?></td>
					</tr>

					<tr>
						<td align="center">
						<table width='100%'>
							<tr>
								<td align="right" width='60%'><?=$AppUI->_("LT")?>:&nbsp;</td>
								<td align="left" width='40%' bgcolor="#ffffff"><?=$hhrr_dev_rlt;?></td>
							</tr>
						</table>
						</td>
						<td align="center">
						<table width='100%'>
							<tr>
								<td align="right" width='60%'><?=$AppUI->_("Position Key Person")?>:&nbsp;</td>
								<td align="left" width='40%' bgcolor="#ffffff"><?echo ($hhrr_dev_per_k)? $AppUI->_("Yes") : $AppUI->_("No"); ?></td>
							</tr>
						</table>
						</td>
					</tr>
				</table>
				</td>
				<td valign='top'>
				<table cellspacing="1" cellpadding="0" border="0" width="100%" class="tableForm_bg">
					<tr class="tableHeaderGral">
						<th align="center" colspan="4"><?=$AppUI->_("Potential review")?></th>
					</tr>
					<tr class="tableHeaderGral">
						<td colspan="2" align="center" width='50%'><?=$AppUI->_("Management")?></td>
						<td colspan="2" align="center" width='50%'><?=$AppUI->_("Technical Functional")?></td>
					</tr>
					<tr>
						<td align="right"  width='30%'>1 <?=$AppUI->_("Level")?>:&nbsp;</td>
						<td align="left" width='20%' bgcolor="#ffffff"><?=$hhrr_dev_eval_g_1;?></td>
						<td align="right"  width='30%'>1 <?=$AppUI->_("Level")?>:&nbsp;</td>
						<td align="left" width='20%' bgcolor="#ffffff"><?=$hhrr_dev_eval_t_1;?></td>
					</tr>
					<tr>
						<td align="right"  width='30%'><?=$AppUI->_("More than 1 Level")?>:&nbsp;</td>
						<td align="left" width='20%' bgcolor="#ffffff"><?=$hhrr_dev_eval_g_S;?></td>
						<td align="right"  width='30%'><?=$AppUI->_("More than 1 Level")?>:&nbsp;</td>
						<td align="left" width='20%' bgcolor="#ffffff"><?=$hhrr_dev_eval_t_S;?></td>
					</tr>
				</table>
			</td>
			</tr>
		</table>
		
		<table cellspacing="1" cellpadding="0" border="0" width="100%">
			<tr>
				<td><br>
				<br>
				</td>
			</tr>
		</table>

		<table cellspacing="1" cellpadding="0" border="0" width="100%"
			class="tableForm_bg">
			<tr class="tableHeaderGral">
				<td><?=$AppUI->_("Employee interest areas")?></td>
			</tr>
			<tr>
				<td height='25' valign='top' bgcolor="#ffffff"><?=$hhrr_dev_int_a;?></td>
			</tr>
		</table>
<br>
		<table cellspacing="1" cellpadding="0" border="0" width="100%">
				<tr class="tableHeaderGral">
					<td><?=$AppUI->_("Employee personal development expectations")?>
					</td>
				</tr>
				<tr>
					<td height='25' valign='top' bgcolor="#ffffff"><?=$hhrr_dev_exp;?></td>
				</tr>
			</table>
			<br>
			<table cellspacing="1" cellpadding="0" border="0" width="100%"
				class="tableForm_bg">
				<tr class="tableHeaderGral">
					<td><?=$AppUI->_("Development ideas")?></td>
				</tr>
				<tr>
					<td height='25' valign='top' bgcolor="#ffffff"><?=$hhrr_dev_sug;?></td>
				</tr>
			</table>

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

		<table cellspacing="1" cellpadding="5" border="0" width="100%" style="border: 1px solid #000000;" class="tableForm_bg">
			<tr>
				<td align="center" width="100%" colspan='2'>
					<? include_once('viewhhrr_dev_pf.php'); ?>
				</td>
			</tr>
		</table>

		<table cellspacing="1" cellpadding="0" border="0" width="100%">
			<tr>
				<td><br>
				</td>
			</tr>
			<tr>
				<td align="right" colspan='5'><?
					if($_GET[a]!="personalinfo")
					{?> 
						<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" /> <?
					}?> &nbsp;&nbsp; <?
					if (validar_permisos_hhrr($id,'development',-1))
					{
						$edit_hrf = "index.php?m=hhrr&a=addedit&tab=$tab&id=".$id;?>
						<input type="button" value="<?php echo $AppUI->_( 'edit' );?>" class="button" onClick="javascript:window.location='<?=$edit_hrf;?>';" />
					<?
					}?>
				</td>
			</tr>
		</table>
</table>