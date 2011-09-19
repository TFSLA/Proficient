<table width="100%" border="0" cellspacing="15" cellpadding="0">
      <tr>
        <td class="texto">
<?php 

$msg = $AppUI->_("homeMsg");
$msg = str_replace("[company_name]", $AppUI->getConfig("company_name"), $msg);
$msg = str_replace("[company_hhrr_mail]", $AppUI->getConfig("company_hhrr_mail"), $msg);
$msg = str_replace("[company_contact_info]", $AppUI->getConfig("company_contact_info"), $msg);
echo $msg;
?>
		</td>
	  </tr>
</table>