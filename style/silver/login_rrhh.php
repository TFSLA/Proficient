<?php /* STYLE/DEFAULT $Id: login_rrhh.php,v 1.1 2009-05-19 21:15:50 pkerestezachi Exp $ */

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
	<style>
	body{
		background-color: #FFFFFF;
		/*background-image:  url(images/BG.gif);*/
	}
	</style>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onload="document.loginform.username.focus()">

<table width="770" border="0" cellspacing="0" cellpadding="0" align="center" height="82">

  <tr> 
    <td colspan="97">
		<table width="770" border="0" align="center" cellpadding="0" cellspacing="0" background="images/silver-back_header.jpg">
		  <tr>
		    <td width="239"><a href="index.php"><img src="images/silver-header-logo.jpg" width="239" height="43" border="0"></a></td>
				<th>Portal de candidatos</th>
				<td width="15"><img src="images/buttons/silver-header-end.jpg" alt="" name="end" width="15" height="43" border="0" id="end"></td>
		  </tr>
		</table>
    </td>
  </tr>
  <tr>
  	<td bgcolor="#DDDDDD"
			style=" background-image: url(images/common/back_grande.jpg);
							background-repeat: repeat-x;
							" valign="top">  
  	<img src="images/1x1.gif" width="1" height="320">
  	</td>
  	<td bgcolor="#DDDDDD"
			style=" background-image: url(images/common/back_grande.jpg);
							background-repeat: repeat-x;
							border-left: #9A9A9A solid 1px;
								border-right: #9A9A9A solid 1px;" valign="top">
  	<br><br><br>

<table align="center" border="0" cellpadding="1" cellspacing="1" width="95%">
<form action="hhrr/" method="post" name="loginform">
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
</form>
</table>
<?php if (@$AppUI->getConfig( 'version' )) { ?>
<div align="center">
	<span style="font-size:7pt">Version <?php echo @$AppUI->getConfig( 'version' );?></span>
</div>
<?php } ?>

  </td>
  <td style="border-right: #9A9A9A solid 1px;">
  
  
  <table align="center" border="0" cellpadding="1" cellspacing="1" width="98%">
<form method="post" name="hhrrFrm" action="hhrr/">
<input type="hidden" name="submit" value="Registrarme">
  <tr>
  <td>
  <p align="left"><br><b>&nbsp;&nbsp;<?php echo $AppUI->_('Sign in form');?></b>
  <center><br>
  <br>
  <table align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
    <td width="35%" valign="top" rowspan="7">
  <font color='#111111'>&nbsp;&nbsp;<?php echo $AppUI->_('msgSignIn');?><br><br></font>
  </p>

    </td>
    <td><b><?php echo $AppUI->_('First Name');?>:&nbsp;</b></td>
    <td colspan="2">&nbsp;<input class="text" size="30" type="text" name="firstname" value="<?=$firstname?>" ></td>
    </tr>  
    <tr>
    <td ><b><?php echo $AppUI->_('Last Name');?>:&nbsp;</b></td>
    <td  colspan="2">&nbsp;<input class="text" size="30" type="text" name="lastname" value="<?=$lastname?>" ></td>
    </tr>  
    <tr>
    <td ><b><?php echo $AppUI->_('Email');?>:&nbsp;</b></td>
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
    <td ><b><?php echo ucfirst($AppUI->_('Username'));?>:&nbsp;</b></td>
    <td  colspan="2">&nbsp;<input class="text" size="30" type="text" name="username" value="<?php echo $newusername;?>">
		</td>
    </tr>  
    <tr>
    <td ><b><?php echo ucfirst($AppUI->_('Password'));?>:&nbsp;</b></td>
    <td  colspan="2">&nbsp;<input class="text" size="16" type="password" name="password" value="<?=$password?>"></td>
    </tr>  
    <tr>
    <td ><b><?php echo $AppUI->_('Repeat Password');?>:&nbsp;</b></td>
    <td  colspan="2">&nbsp;<input class="text" size="16" type="password" name="password2" value="<?=$password2?>"></td>
    </tr>  
  </table>
  <br><br>
  <input class="button" type="submit" name="register_candidate" value="<?php echo $AppUI->_('Register');?>" >
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
		<table width="770" border="0" align="center" cellpadding="0" cellspacing="0" background="images/silver-footer-back.gif">
		      <tr background="images/silver-footer-back.gif">
		        <td width="10"><img src="images/silver-footer-inicio.gif" width="10" height="14"></td>
		        <td><div align="center" class="footertext">Copyright © 2004-<?php echo date("Y");?> Proficient.&nbsp;<?php echo $AppUI->_("All rights reserved.")?></div></td>
		        <td width="5"><div align="right"><img src="images/silver-footer-fin.gif" width="5" height="14"></div></td>
		      </tr>
		</table>

</body>
</html>
