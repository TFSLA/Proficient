<?php /* STYLE/DEFAULT $Id: login_hhrr.php,v 1.1 2009-05-19 21:15:50 pkerestezachi Exp $ */

/* <base href="<?php echo $AppUI->getConfig("base_url");?>"> 
*/
?>
<BASE href="<?php echo $AppUI->getConfig("base_url");?>/"> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo @$AppUI->getConfig( 'page_title' );?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=<?php echo isset( $locale_char_set ) ? $locale_char_set : 'UTF-8';?>" />
       	<title><?php echo $AppUI->cfg['company_name'];?> ::  Login</title>
	<meta http-equiv="Pragma" content="no-cache" />
	<meta name="Version" content="<?php echo @$AppUI->getConfig( 'version' );?>" />
	<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle;?>/main.css" media="all" />
	<style type="text/css" media="all">@import "./style/<?php echo $uistyle;?>/main.css";</style>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0"  background="images/BG.gif" onload="document.loginform.username.focus()">


<table width="770" border="0" cellspacing="0" cellpadding="0" align="center" height="82">
  <tr bgcolor="#000000">
    <td colspan="5" height="5"><img src="images/1x1.gif" width="1" height="1"></td>
  </tr>
  <tr> 
    <td rowspan="3" bgcolor="#000000" width="4"><img src="images/1x1.gif" width="1" height="1"></td>
    <td colspan="3" height="50"><img src="images/top_logomisc.gif" width="170" height="50"><img src="images/top_misc1.gif" width="302" height="50"><img src="images/top_misc2.gif" width="289" height="50"></td>
    <td rowspan="3" bgcolor="#000000" width="4"><img src="images/1x1.gif" width="1" height="1"></td>
  </tr>
  <tr>
  	<td bgcolor="#000000" valign="top">

<table align="center" border="0" cellpadding="1" cellspacing="1" width="95%" class="copyr">
<form action="" method="post" name="loginform">
<input type="hidden" name="login" value="<?php echo time();?>" />
<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
<tr>
	<td><?php echo $AppUI->_('Username');?>:<br>
		<input type="text" size="25" maxlength="20" name="username" class="text" /></td>
</tr>
<tr>
	<td nowrap><?php echo $AppUI->_('Password');?>:<br>
		<input type="password" size="25" maxlength="32" name="password" class="text" /></td>
</tr>
<tr>
	<td align="center" valign="bottom" nowrap><input type="submit" name="login" value="<?php echo $AppUI->_('login');?>" class="button" /></td>
</tr>
<tr>
	<td colspan="2" align="center"><br><a href="hhrr/index.php?p=1" ><font color="#FFFFFF"><?php echo $AppUI->_('forgotPassword');?></FONT></a></td>
</tr>
</form>
</table>
<?php if (@$AppUI->getConfig( 'version' )) { ?>
<div align="center" class="copyr">
	<span style="font-size:7pt">Version <?php echo @$AppUI->getConfig( 'version' );?></span>
</div>
<?php } ?>

  </td>
  <td bgcolor="#DDDDDD">
  
  
  
  
  
  
  <table align="center" border="0" cellpadding="1" cellspacing="1" width="98%">
<form method="post" name="hhrrFrm" action="">
  <tr>
  <td>
  <p align="left"><br><b>&nbsp;&nbsp;Formulario de Registro</b>
  <center><br>
  <?
    if($emptyfields=="true") echo '<font color="#CC0000">Debe completar <b>todos</b> los datos para poder registrarse.<br>Intente nuevamente.<br><br></font>';
    if($passwordmismatch=="true") echo '<font color="#CC0000"><b>El password y su confirmación no coinciden</b>.<br>Por favor ingréselos nuevamente.<br><br></font>';
    if($existinguser=="true"){
			echo '<font color="#CC0000">El nombre de usuario elegido "'.$username.'" ya existe en el sistema.<br>Por favor elija otro nombre de usuario.<br><br></font>';
		
		
		}
  ?>
  <br><br>
  <table align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
    <td width="35%" valign="top" rowspan="7">
	<p>

   <table width="90%">
		<tr>
			<td ><div align="justify"> <font color='#111111'>&nbsp;&nbsp;Complete los siguientes datos para obtener una cuenta de usuario. Con dicha cuenta podrá ingresar a la extranet de Technology for Solutions y cargar sus datos personales para participar en las selecciones de personal.<br><br></font></div>
			</td>
		</tr>
		</table>
  </p>

    </td>
    <td><b>Nombre:&nbsp;</b></td>
    <td colspan="2">&nbsp;<input class="text" size="30" type="text" name="firstname" value="<?=$firstname?>" ></td>
    </tr>  
    <tr>
    <td ><b>Apellido:&nbsp;</b></td>
    <td  colspan="2">&nbsp;<input class="text" size="30" type="text" name="lastname" value="<?=$lastname?>" ></td>
    </tr>  
    <tr>
    <td ><b>Email:&nbsp;</b></td>
    <td colspan="2">&nbsp;<input class="text" size="30" type="text" name="email" value="<?=$email?>"></td>
    </tr> 
		<? /*
    <tr>
    <td class="celdatexto"><b>Nombre de usuario:&nbsp;</b></td>
    <td class="celdatextoizquierda" width="100">&nbsp;<span id="txt_username" style="vertical-align: middle">&nbsp;</span></td>
		<td class="celdatextoizquierda"><input class="botforms" type="button" name="checkuser" value="Verificar" onclick="check()"><input class="text" size="16" type="hidden" name="username" value=""></td>
    </tr>  
		*/ ?>		
    <tr>
    <td ><b>Nombre de usuario:&nbsp;</b></td>
    <td  colspan="2">&nbsp;<input class="text" size="30" type="text" name="username" value="<?php echo $newusername;?>">
		</td>
    </tr>  
    <tr>
    <td ><b>Contraseña:&nbsp;</b></td>
    <td  colspan="2">&nbsp;<input class="text" size="16" type="password" name="password" value="<?=$password?>"></td>
    </tr>  
    <tr>
    <td ><b>Repetir Contraseña:&nbsp;</b></td>
    <td  colspan="2">&nbsp;<input class="text" size="16" type="password" name="password2" value="<?=$password2?>"></td>
    </tr>  
  </table>
  <br><br>
  <input class="button" type="submit" name="submit" value="Registrarme" >
  </center><br>

  </td></tr>
</form>  
</table>
  
  
  
  
  
  
  


<div align="center">
<?php
	echo '<span class="error">'.$AppUI->getMsg().'</span>';

	$msg = '';
	$msg .=  phpversion() < '4.1' ? '<br /><span class="warning">WARNING:  NOT SUPPORT for this PHP Version ('.phpversion().')</span>' : '';
	$msg .= function_exists( 'mysql_pconnect' ) ? '': '<br /><span class="warning">WARNING: PHP may not be compiled with MySQL support.  This will prevent proper operation.  Please check you system setup.</span>';
	echo $msg;
?>
</div>
<br>
<br>
<br>
</td></tr>
</table>
<table width="770" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#000000" height="20">
  <tr>
    <td width="174"><img src="images/1x1.gif" width="1" height="1"></td>
    <td align="center" valign="middle" class="copyr">Copyright 2002 - Technology for Solutions 
      - Todos los derechos reservados</td>
  </tr>
</table>

</body>
</html>
