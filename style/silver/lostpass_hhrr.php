<?php /* STYLE/DEFAULT $Id: lostpass_hhrr.php,v 1.1 2009-05-19 21:15:50 pkerestezachi Exp $ */

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
				<th><?php 
            	echo $AppUI->_("Candidates Portal");
            ?></th>
				<td width="15"><img src="images/buttons/silver-header-end.jpg" alt="" name="end" width="15" height="43" border="0" id="end"></td>
		  </tr>
		</table>
    </td>
  </tr>
  <tr>
  	<td bgcolor="#DDDDDD">  
  	<img src="images/1x1.gif" width="1" height="80">
  	
	    <form action="hhrr/index.php?p=2" method="post" name="loginform">
		<table align="center" border="0" width="250" cellpadding="6" cellspacing="0">
			<input type="hidden" name="lostpass" value="1" />
			<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />
			
			<tr>
				<td align="right" nowrap><?php echo $AppUI->_('Username');?>:</td>
				<td align="left" nowrap><input type="text" size="25" maxlength="20" name="checkusername" class="text" /></td>
			</tr>
			<tr>
				<td align="right" nowrap><?php echo $AppUI->_('EMail');?>:</td>
				<td align="left" nowrap><input type="email" size="25" maxlength="64" name="checkemail" class="text" /></td>
			</tr>
			<tr>
				<td align="left" nowrap></td>
				<td align="right" valign="bottom" nowrap><input type="submit" name="sendpass" value="<?php echo $AppUI->_('send password');?>" class="button" /></td>
			</tr>
			</table>
		</form>
		<img src="images/1x1.gif" width="1" height="80">
	  </td>
	</tr>

 </table>

 <!-- Pie -->
		<table width="770" border="0" align="center" cellpadding="0" cellspacing="0" background="images/silver-footer-back.gif">
		      <tr background="images/silver-footer-back.gif">
		        <td width="10"><img src="images/silver-footer-inicio.gif" width="10" height="14"></td>
		        <td><div align="center" class="footertext">Copyright © 2004-<?php echo date("Y");?> Proficient.&nbsp;<?php echo $AppUI->_("All rights reserved.")?></div></td>
		        <td width="5"><div align="right"><img src="images/silver-footer-fin.gif" width="5" height="14"></div></td>
		      </tr>
		</table>




</body>
</html>
