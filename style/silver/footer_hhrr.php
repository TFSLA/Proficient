<?php /* STYLE/CLASSIC $Id: footer_hhrr.php,v 1.1 2009-05-19 21:15:50 pkerestezachi Exp $ */
$dialog = dPgetParam( $_GET, 'dialog', 0 );
$suppressLogo = dPgetParam( $_GET, "suppressLogo", 0 );
if ($titleBlock)
	$titleBlock->CloseTablePadding();

        ?>
        </td>
		<!--td> &nbsp;&nbsp;&nbsp;
		</td-->
	</tr>
<?php 
if (!($dialog && $suppressLogo)){ ?>
<tr>
	<td colspan="3">
		<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" background="images/silver-footer-back.gif">
		      <tr background="images/silver-footer-back.gif">
		        <td width="10"><img src="images/silver-footer-inicio.gif" width="10" height="14"></td>
		        <td><div align="center" class="footertext">Copyright © 2004-<?php echo date("Y");?> Proficient.&nbsp;<?php echo $AppUI->_("All rights reserved.")?></div></td>
		        <td width="5"><div align="right"><img src="images/silver-footer-fin.gif" width="5" height="14"></div></td>
		      </tr>
		</table>
		        </td>
				<!--td> &nbsp;&nbsp;&nbsp;
				</td-->
	</tr>
<?php	 } ?>
</table>	
	</body>
</html>