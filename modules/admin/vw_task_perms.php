<?php
GLOBAL  $seluser,$user_id;

if (! class_exists("CProject"))
	require_once( $AppUI->getModuleClass( 'projects' ) );

$seluser=$user_id;
?>
<table width="650" class="tbl">
<tr><Td><?PHP echo "".$AppUI->_("msgAdvUserSecurity")."";?></td></tr>
</table><br>
<?
//echo "<span>".$AppUI->_("msgAdvUserSecurity")."</span><br>";
require_once("modules/projects/vw_perms.php");

?>	
