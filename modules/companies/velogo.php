<?php

/////////////////////////
// EMPIEZO A CODIFICAR //
/////////////////////////
// Paso el id del formulario o link a la variable que uso

if ($_POST['id']!=0){
	$company_id=$_POST['id'];
}
else{
	$company_id=$_GET['id'];
}
$user_id=$AppUI->user_id;


// Verifico que tenga permisos para ver/editar
$sql="SELECT permission_value FROM permissions WHERE permission_user=$user_id AND ((permission_grant_on='companies' AND permission_item='$company_id') OR permission_grant_on='all')";
//echo "<br><br>$SQL<br><br>";
$rc=db_exec($sql);
$vec=db_fetch_array($rc);

?>
<html>
<head><title>Logo
	<?php echo $AppUI->_('Edit/View'); ?>
</title></head>
<link rel="stylesheet" type="text/css" href="./style/<?php echo "$uistyle"; ?>/main.css" media="all" />
<body>
<?php
	$fname=$_FILES['usrfile']['name'];
	$fsize=$_FILES['usrfile']['size'];
	$ftype=$_FILES['usrfile']['type'];
	$imgsize=getimagesize($_FILES['usrfile']['tmp_name']);
	$fheight = $imgsize[1];
	$fwidth = $imgsize[0];
// Guardo la imagen en la db si el formulario fue cargado
if ($_POST['id']!=0 AND $vec['permission_value']=='-1' /*AND $fname!=''*/) {
	$fbin_data=addslashes(file_get_contents($_FILES['usrfile']['tmp_name']));
	$sql="UPDATE companies SET fbin_data='$fbin_data', fname='$fname', fsize='$fsize', ftype='$ftype', fheight=$fheight, fwidth=$fwidth WHERE company_id=$company_id";
	//echo "sql";
	if ($fsize>0 AND $_FILES['usrfile']['tmp_name'])	db_exec($sql);
	elseif (!$fname=$_FILES['usrfile']['name'])	$msg=$AppUI->_('Non File Was Selected');
	else $msg=$AppUI->_('File Size Excess The Limit');
}
$sql="SELECT fsize, fname, ftype, fheight, fwidth, company_name FROM companies WHERE company_id='$company_id'";
$rc=db_exec($sql);
$vec2=db_fetch_array($rc);
$company_name = $vec2["company_name"];
if ($msg) echo "<img src='./images/icons/stock_cancel-16.png' width='16' height='16'><font color='red'><b>".$msg."</b></font>\n";
?>

<br>
<table width="90%" align="center">
	<tr>
		<td colspan="2">
			<table width="100%" border='0' cellpadding="0" cellspacing="0">
				<TR>
					<td width="6">
						<img src="./images/common/inicio_title_section.gif" height="34" width="6">
					</td>
					<td width="38" background="./images/common/back_title_section.gif">
						<img src="./modules/companies/images/handshake.gif" alt="" border="0" height="29" width="29">
					</td>
					<td class="titularmain2" background="./images/common/back_title_section.gif" align="center">
						<?php echo "<B>$company_name</B>"; ?>
					</td>
					<td valign="bottom" width="6">
						<img src="./images/common/fin_title_section.gif" height="34" width="6">
					</td>
				</TR>
			</table>
		</td>
	</tr>
	<TR>
		<TD colspan="2">
			<table background="./images/common/back_1linea_06.gif" border="0" cellpadding="0" cellspacing="0" width="100%">
			<tbody>
				<tr>
					<td width="6">
						<img src="./images/common/inicio_1linea.gif" height="19" width="6">
					</td>
					<td width="100%">
						<img src="./images/common/cuadradito_naranja.gif" height="9" width="9">
						<span class="boldblanco">Logo <?php echo $AppUI->_('Edit/View'); ?></span>
					</td>
					<td align="right" width="6">
						<img src="./images/common/fin_1linea.gif" height="19" width="3">
					</td>
				</tr>
				<tr bgcolor="#666666">
					<td colspan="3" height="1">
					</td>	
				</tr>
				<tr>
					<td colspan="3">
				</td>
				</tr>
				<tr bgcolor="#666666">
					<td colspan="3" height="1"></td>
				</tr>
			</tbody>
			</table>
		</TD>
	</TR>
	<TR>
		<TD align="center">
			<?
			if ($vec2['fsize']!=0){
				$alto=$vec2['fheight']/125;
				$ancho=$vec2['fwidth']/125;
				if ($alto>$ancho) {
					$height=$vec2['fheight']/$alto;
					$width=$vec2['fwidth']/$alto;
				}
				else{
					$height=$vec2['fheight']/$ancho;
					$width=$vec2['fwidth']/$ancho;
				}
				echo "<img src='./includes/view.php?mod=1&id=$company_id' height='$height' width='$width'>";
			}
			else echo "<img src='./images/nologo.jpg' height='125'>"; 
			?>
		</TD>
		<TD align="center">
			<?php
			if ($vec2['fsize']!=0){
			?>
			<TABLE aling='center' width="100%">
				<TR>
					<TD colspan="2" align="center">
						<b><?php echo $AppUI->_('File Data'); ?></b>
					</TD>
				</TR>
				<TR>
					<TD width="50%" align="right">
						<?php echo $AppUI->_('File Size');  ?>:
					</TD>
					<TD width="50%" aling='left'>
						<b>
							<?php
								$size=$vec2['fsize']/1024;
								 echo round ($size, 2);
							?>
						Kb.
						</b>
						<br>
					</TD>
				</TR>
				<TR>
					<TD width="50%" align="right">
						<?php echo $AppUI->_('File Name'); ?>:
					</TD>
					<TD width="50%" aling='left'>
						<b><?php echo $vec2['fname']; ?></b><br>
					</TD>
				</TR>
				<TR>
					<TD width="50%" align="right">
						<?php echo $AppUI->_('File Type'); ?>:
					</TD>
					<TD width="50%" aling='left'>
						<b><?php echo $vec2['ftype']; ?></b>
					</TD>
				</TR>
			</TABLE>
			<?php
			}
			?>
<?php
if ($vec['permission_value']=='-1'){
?>

			<form enctype='multipart/form-data' action='index_inc.php' method='post'>
				<input type="hidden" name='inc' value='./modules/companies/velogo.php'>
				<input type="hidden" name='m' value='companies'>
				<input type="hidden" name="company_name" value="<?php echo $company_name; ?>">
				<input type="hidden" name="id" value="<?php echo $company_id; ?>">
				<input type="hidden" name="MAX_FILE_SIZE" value="1000000">
				<input type="file" class="small" name="usrfile" accept="image/jpeg"><br>
				<TABLE>
					<TR>
						<TD>
							<input type="submit" class="button" value="<?php echo $AppUI->_('Submit'); ?>">
						</TD>
						</form>
						<form action="" method="post">
						<TD>
							<input type="submit" class="button" value="<?php echo $AppUI->_('Close'); ?>" onclick="window.close()">
						</TD>
						</form>
					</TR>
				</TABLE>
		</TD>
	</TR>
<?php
}
?>
	<TR>
		<TD COLSPAN='2'>
			<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" background="./images/silver-footer-back.gif">
				<tr background="./images/silver-footer-back.gif">
					<td width="10"><img src="./images/silver-footer-inicio.gif" width="10" height="14"></td>
					<td>
						<div align="center" class="footertext">Copyright &#169 2004-2006 Proficient.&nbsp;All rights reserved.</div>
					</td>
					<td width="5"><div align="right"><img src="./images/silver-footer-fin.gif" width="5" height="14"></div></td>
				</tr>
			</table>
		</TD>
	</TR>
</table>

<?php
	//MYSQL_CLOSE();
?>
</body>
</html>
