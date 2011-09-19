<?php


$msg = $AppUI->_("welcomeUser");

$user = new CUser();
$user->load($AppUI->user_id);
?>


<table width="100%" border="0" cellspacing="15" cellpadding="0">
      <tr>
        <td class="texto">
<?php
echo nl2br(sprintf($msg, $AppUI->getConfig("company_name"),
					$user->user_username,
					$user->hhrr_password));

?>
		</td>
	</tr>
</table>
