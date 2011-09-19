<?php /*  STYLE/CLASSIC $Id: lostpass.php,v 1.1 2009-05-19 21:15:50 pkerestezachi Exp $ */ ?>
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
<form method="post" name="lostpassform">
<input type="hidden" name="lostpass" value="1" />
<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
<tr>
	<td colspan="2" class="headerfontWhite" bgcolor="gray">
		<strong>Professional Services Automation</strong>
	</td>
</tr>
<tr>
	<td align="right" nowrap bgcolor="#eeeeee"><?php echo $AppUI->_('Username');?>:</td>
	<td align="left" nowrap bgcolor="#eeeeee"><input type="text" size="25" maxlength="20" name="checkusername" class="text" /></td>
</tr>
<tr>
	<td align="right" nowrap bgcolor="#eeeeee"><?php echo $AppUI->_('EMail');?>:</td>
	<td align="left" nowrap bgcolor="#eeeeee"><input type="email" size="25" maxlength="64" name="checkemail" class="text" /></td>
</tr>
<tr>
	<td align="left" nowrap bgcolor="#eeeeee"></td>
	<td align="right" valign="bottom" nowrap bgcolor="#eeeeee"><input type="submit" name="sendpass" value="<?php echo $AppUI->_('send password');?>" class="button" /></td>
</tr>
</form>
</table>

<p align="center"><?php 
	echo '<span class="error">'.$AppUI->getMsg().'</span>';
	//echo ini_get( 'register_globals') ? '' : '<br /><span class="warning">WARNING:  not fully supported with register_globals=off</span>';
?></p>

<table align="center" border="0" width="250" cellpadding="4" cellspacing="0">
<tr>
	<td align=center>
		<img src="./images/icons/dp.gif"  border=0 alt="" />
	</td>
</tr>
</table>

</body>
</html>