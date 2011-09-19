<?php
// Menu de Carpertas Mensajeria Privada
//Averiguo en que Folder esta
$strFolder='';
if($a=="index"){
    $strFolder="Your Inbox";
}else if(strtolower($a)=="sentbox"){
    $strFolder="Your Sent Box";
}else if(strtolower($a)=="savebox"){
    $strFolder="Your Save Box";
}
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_06.gif">
              <tr>
                <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
                <td><img src="images/common/cuadradito_naranja.gif" width="9" height="9">
                <span class="boldblanco"><?php echo $AppUI->_(date("l")); ?>,&nbsp;<?php echo date("d"); ?>&nbsp;<?php echo $AppUI->_(date("F")); ?>&nbsp;<?php echo $AppUI->_(date("Y")); ?>&nbsp;<?php if($strFolder!="") echo ":"; ?>&nbsp;<?php echo $AppUI->_($strFolder); ?></span></td>
                <td><div align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></div></td>
              </tr>
              <tr bgcolor="#666666">
                <td height="1" colspan="3"></td>
              </tr>
              <tr>
                <td colspan="3">
<table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_degrade.gif">
        <tr>
          <td width="6"><img src="images/common/ladoizq.gif" width="6" height="19"></td>
          <td>[&nbsp;<a href="index.php?m=privmsg"><?php echo $AppUI->_('Inbox'); ?> </a>]&nbsp;[&nbsp;<a href="index.php?m=privmsg&a=sentbox"><?php echo $AppUI->_('Sentbox'); ?></a>&nbsp;]&nbsp;[&nbsp;<a href="index.php?m=privmsg&a=savebox"><?php echo $AppUI->_('Savebox'); ?></a>&nbsp;]</td>
          <td width="6"> <div align="right"><img src="images/common/ladoder.gif" width="6" height="19"></div></td>
        </tr>
      </table></td>
  </tr>
  <tr bgcolor="#666666">
    <td height="1" colspan="3"></td>
  </tr>
</table>