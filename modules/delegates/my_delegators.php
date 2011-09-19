<?php
require_once( $AppUI->getModuleClass("admin") );
$usr = new CUser();
$usr->load( $AppUI->user_id );
$delegs = $usr->getDelegators();
?>
<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<?
$delegator = new CUser();
$hayDelegados = 0;
foreach( $delegs as $deleg )
{
	$hayDelegados = 1;
	$delegator->load( $deleg["delegator_id"] );	
	$modulos = $usr->getModulesDelegatedBy( $delegator->user_id );
	$mod = current($modulos);
	?>
	<tr>
		<td align="right"><?=$AppUI->_("Use PSA of")?>:</td>
		<td align="left">
			<a target="_window" href="?m=<?=$mod["mod_directory"]?>&delegator_id=<?=$deleg["delegator_id"]?>"><?=$delegator->user_first_name." ".$delegator->user_last_name?></a>
		</td>	
	</tr>
	<?
}
if ( !$hayDelegados )
{
	?>
	<tr>
		<td><?=$AppUI->_("You have no delegated modules")?></td>
	</tr>
	<?
}
?>
</table>
