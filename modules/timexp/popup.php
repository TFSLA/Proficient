<?php
global $is_popup;

$is_popup = true;

?>
<div id="theLayer" style="position:absolute;width:250px;left:1;top:1;visibility:visible">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
<tr>
	<td>
        <!--table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_06.gif">
          <tr>
            <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
            <td align="left" class="tableHeaderText"><img src="images/common/cuadradito_naranja.gif" width="9" height="9">InfoBoxes:</td>
            <td width="6" align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></td>
          </tr>
        </table-->
		<table width="100%" border="0" cellpadding="0" cellspacing="0" class="" background="images/common/back_1linea_06.gif">
		<tr class="tableHeaderGral">
              <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
			  <td style="cursor:hand" valign="middle">
			  <img id="imgbtn" align="right" src="images/icons/up.gif" onClick="onoffdisplay()">
			  </td>
			  <td id="titleBar" style="cursor:move" width="100%">
			  <ilayer width="100%" onSelectStart="return false">
			  <layer width="100%" onMouseover="isHot=true;if (isN4) ddN4(theLayer)" onMouseout="isHot=false">&nbsp;
			  <?php echo $AppUI->_("New Time");?>
			  </layer>
			  </ilayer>
			  </td>
              <td width="6" align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></td>
		</tr>
		</table>
		<table width="100%" border="0" cellpadding="2" cellspacing="1" class="std">
		<tr>
			<td width="100%"colspan="2">
				<div id="contentPopup">
	<?php
	$hideTitleBlock=1;
	include ("addedittime.php");
	?> 
				</div>
			</td>
		 </tr>
		</table> 
	</td>
	</tr>
</table>
	

</div>
<?php 

?>


