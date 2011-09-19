<?php /* SYSTEM $Id: index.php,v 1.2 2009-06-25 14:28:40 nnimis Exp $ */

if (getDenyRead( $m ))
	$AppUI->redirect( "m=public&a=access_denied" );

$AppUI->savePlace();

$titleBlock = new CTitleBlock( 'System Administration', 'system_admin.gif', $m, "$m.$a" );
$titleBlock->show();
?>
<p>
<table width="50%" border="0" cellpadding="0" cellspacing="5" align="left">
<?php /* Localization disabled
<tr>
	<td width="42">
		<?php echo dPshowImage( dPfindImage( 'language_support.gif', $m ), 32, 32, '' ); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_( 'Language Support' );?>
	</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=translate"><?php echo $AppUI->_( 'Translation Management' );?></a>
	</td>
</tr>
*/ ?>
<tr>
	<td>
		<?php echo dPshowImage( dPfindImage( 'preferences.gif', $m ), 32, 32, '' ); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Preferences');?>
	</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=addeditpref"><?php echo $AppUI->_('Default User Preferences');?></a>
<?php /*
		<br /><a href="?m=system&u=syskeys&a=keys"><?php echo $AppUI->_( 'System Lookup Keys' );?></a>
		<br /><a href="?m=system&u=syskeys"><?php echo $AppUI->_( 'System Lookup Values' );?></a>
		*/ ?>
	</td>
</tr>

<tr>
	<td>
		<?php echo dPshowImage( dPfindImage( 'modules.gif', $m ), 32, 32, '' ); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Modules');?>
	</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=viewmods"><?php echo $AppUI->_('View Modules');?></a>
	</td>
</tr>
<tr>
	<td>
		<?php echo dPshowImage( dPfindImage( 'administration.gif', $m ), 32, 32, '' ); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Security');?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=vw_roles_perms"><?php echo $AppUI->_('Default Roles Permissions');?></a>
	</td>
</tr>
<tr>
	<td>
		<?php echo dPshowImage( dPfindImage( 'administration.gif', $m ), 32, 32, '' ); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Calendar');?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=calendars"><?php echo $AppUI->_('Work Calendars');?></a>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=hollidays"><?php echo $AppUI->_('Hollidays');?></a>
	</td>
</tr>
<tr>
	<td>
		<?php echo dPshowImage( dPfindImage( 'files.gif', $m ), 32, 32, '' ); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Categories');?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=addedit_category&modify=no"><?php echo $AppUI->_('Document Categories');?></a>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=addedit_expense_category&modify=no"><?php echo $AppUI->_('Expense Categories');?></a>
	</td>
</tr>
<?php /*
<tr>
	<td>
		<?php echo dPshowImage( dPfindImage( 'administration.gif', $m ), 32, 32, '' ); ?>
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Administration');?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&u=roles"><?php echo $AppUI->_('User Roles');?></a>
	</td>
</tr>
*/?>
</table>
</p>
