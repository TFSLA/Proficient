<?php /* STYLE/DEFAULT $Id: login.php,v 1.1 2009-05-19 21:15:50 pkerestezachi Exp $ */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo @$AppUI->getConfig( 'page_title' );?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=<?php echo isset( $locale_char_set ) ? $locale_char_set : 'UTF-8';?>" />
       	<title><?php echo $AppUI->cfg['company_name'];?> ::  Login</title>
	<meta http-equiv="Pragma" content="no-cache" />
	<meta name="Version" content="<?php echo @$AppUI->getConfig( 'version' );?>" />
	<link rel="shortcut icon" href="./style/<?php echo $uistyle;?>/images/favicon.ico" type="image/ico" />
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
  <tr><td bgcolor="#DDDDDD" colspan="5">


<br /><br /><br /><br />
<form action="./index.php" method="post" name="loginform">
<table align="center" border="0" width="250" cellpadding="6" cellspacing="0" >
<input type="hidden" name="login" value="<?php echo time();?>" />
<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
<input type="hidden" name="lostpass" value="0" />
<tr>
	<th colspan="2"><em>Professional Services Automation</em></th>
</tr>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_('Username');?>:</td>
	<td align="left" nowrap><input type="text" size="25" maxlength="20" name="username" class="text" /></td>
</tr>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_('Password');?>:</td>
	<td align="left" nowrap><input type="password" size="25" maxlength="32" name="password" class="text" /></td>
</tr>
<tr>
	<td align="left" nowrap></td>
	<td align="right" valign="bottom" nowrap>
	<input type="checkbox" name="savecookie"><?php echo $AppUI->_('Save info');?>
	<input type="submit" name="login" value="<?php echo $AppUI->_('login');?>" class="button" /></td>
</tr>
<tr>
	<td colspan="2" align="center"><a href="#" onclick="f=document.loginform;f.lostpass.value=1;f.submit();"><?php echo $AppUI->_('forgotPassword');?></a></td>
</tr>
</table>
<?php if (@$AppUI->getConfig( 'version' )) { ?>
<div align="center">
	<span style="font-size:7pt">Version <?php echo @$AppUI->getConfig( 'version' );?></span>
</div>
<?php } ?>
</form>
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
<br>
<br>
<br>
<br>
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