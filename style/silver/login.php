<?php /* STYLE/DEFAULT $Id: login.php,v 1.2 2009-06-19 00:08:58 pkerestezachi Exp $ */
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
	<style>
	body{
		background-color: #FFFFFF;
		/*background-image:  url(images/BG.gif);*/
	}
	</style>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0"  onload="document.loginform.username.focus()">


<table width="770" border="0" cellspacing="0" cellpadding="0" align="center" height="82">
  <tr> 
    <td>
<table width="770" border="0" align="center" cellpadding="0" cellspacing="0" background="images/silver-back_header.jpg">
  <tr>
    <td width="239"><a href="index.php"><img src="images/silver-header-logo.jpg" width="239" height="43" border="0"></a></td>
  </tr>
</table>
<!--
    <td rowspan="3" bgcolor="#000000" width="4"><img src="images/1x1.gif" width="1" height="1"></td>
    <td background="images/silver-back_header.jpg" colspan="3" height="50"><img src="images/silver-header-logo.jpg" width="239" height="43"></td>
    <td rowspan="3" bgcolor="#000000" width="4"><img src="images/1x1.gif" width="1" height="1"></td>
-->
    </td>
  </tr>
  <tr><td bgcolor="#DDDDDD">


<br /><br /><br /><br />
<form action="" method="post" name="loginform">
<table align="center" border="0" width="250" cellpadding="6" cellspacing="0" >
<input type="hidden" name="login" value="<?php echo time();?>" />
<input type="hidden" name="lostpass" value="0" />
<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
<tr>
	<th colspan="2"><em><?php echo $AppUI->_('Professional Services Automation');?></em></th>
</tr>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_('username');?>:</td>
	<td align="left" nowrap><input type="text" size="25" maxlength="20" name="username" class="text" /></td>
</tr>
<tr>
	<td align="right" nowrap><?php echo $AppUI->_('password');?>:</td>
	<td align="left" nowrap><input type="password" size="25" maxlength="32" name="password" class="text" /></td>
</tr>
<tr>
	<td align="left" nowrap></td>
	<td align="left" valign="bottom" nowrap>
	<table border="0" width="175" >
		<tr>
			<td align="left">
			 <input type="submit" name="login" value="<?php echo $AppUI->_('login');?>" class="button" />
			</td>
			<td align="rigth">
			 <input type="checkbox" name="savecookie">
			</td>
			<td align="left" nowrap>
			 <?php echo $AppUI->_('Save info');?>
			</td>
		</tr>
	</table>
	</td>
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
	if ($user_limit=='1') echo "<span class='error'>".$AppUI->_('Limit Of Actived Users Exceded')."</span><br>";
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
</table>
		<table width="770" border="0" align="center" cellpadding="0" cellspacing="0" background="images/silver-footer-back.gif">
		      <tr background="images/silver-footer-back.gif">
		        <td width="10"><img src="images/silver-footer-inicio.gif" width="10" height="14"></td>
		        <td><div align="center" class="footertext">Copyright  2004-<?php echo date("Y");?> Proficient.&nbsp;<?php echo $AppUI->_("All rights reserved.")?></div></td>
		        <td width="5"><div align="right"><img src="images/silver-footer-fin.gif" width="5" height="14"></div></td>
		      </tr>
		</table>

</body>
</html>
