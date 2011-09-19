<?php /*  STYLE/CLASSIC $Id: login.php,v 1.1 2009-05-19 21:15:50 pkerestezachi Exp $ */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=<?php echo isset( $locale_char_set ) ? $locale_char_set : 'UTF-8';?>" />
	<meta http-equiv="Pragma" content="no-cache">
	<link href="./style/<?php echo $uistyle;?>/main.css" rel="STYLESHEET" type="text/css" />
</head>

<body bgcolor="white" onload="document.loginform.username.focus();">
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<table align="center" border="0" width="250" cellpadding="4" cellspacing="0" bgcolor="#cccccc" class="bordertable">
<form action="./index.php" method="post" name="loginform">
<input type="hidden" name="login" value="<?php echo time();?>" />
<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
<input type="hidden" name="lostpass" value="0" />
<tr>
	<td colspan="2" class="headerfontWhite" bgcolor="gray">
		<strong>Professional Services Automation</strong>
	</td>
</tr>
<tr>
	<td bgcolor="#eeeeee" align="right" nowrap width="100">
		<?php echo $AppUI->_('Username');?>:
	</td>
	<td bgcolor="#eeeeee" align="left" class="menufontlight" nowrap>
		<input type="text" size="25" maxlength="32" name="username" class="text" />
	</td>
</tr>
<tr>
	<td bgcolor="#eeeeee" align="right"  nowrap>
		<?php echo $AppUI->_('Password');?>:
	</td>
	<td bgcolor="#eeeeee" align="left" class="menufontlight" nowrap>
		<input type="password" size="25" maxlength="32" name="password" class="text" />
	</td>
</tr>
<tr>
	<td bgcolor="#eeeeee" align="center" class="menufontlight" nowrap colspan="2">
	    <input type="checkbox" name="savecookie"><?php echo $AppUI->_('Save info');?>
		<input type="submit" name="login" value="<?php echo $AppUI->_('login');?>" class="button" /></p>
	</td>
</tr>
</table>

<p align="center"><?php 
	echo '<span class="error">'.$AppUI->getMsg().'</span>';
	//echo ini_get( 'register_globals') ? '' : '<br /><span class="warning">WARNING:  not fully supported with register_globals=off</span>';
?></p>

<table align="center" border="0" width="250" cellpadding="4" cellspacing="0">
<tr>
	<td colspan="2" align="center"><ul type="square"><li>
	<a href="#" onclick="f=document.loginform;f.lostpass.value=1;f.submit();"><?php echo $AppUI->_('forgotPassword');?></a></li></ul>
	</td>
</tr>
<tr>
	<td align=center>
		<img src="./images/icons/dp.gif"  border=0 alt="" />
	</td>
</tr>
</form>
</table>

</body>
</html>