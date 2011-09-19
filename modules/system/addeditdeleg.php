<?php 

$titleBlock = new CTitleBlock( 'Edit User Delegates', 'preferences.jpg', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system&a=addeditpref&user_id=$AppUI->user_id", "edit preferences" );
$titleBlock->show();
$sql = "SELECT mod_id, mod_ui_name
FROM modules 
WHERE mod_name IN ( 'Calendar', 'Contacts', 'Pipeline', 'wmail' )";
$modulos = db_loadHashList( $sql, "mod_id" );

$tiposPermiso = array( "NONE"=>"NONE", "REVIEWER"=>"REVIEWER", "AUTHOR"=>"AUTHOR", "EDITOR"=>"EDITOR" );
$tiposPermisoMail = array( "NONE"=>"NONE", "REVIEWER"=>"REVIEWER", "EDITOR"=>"EDITOR" );
?>
<script language="javascript">

function hayPermisos()
{
	var form = document.editFrm;
	var hay = false;
	
	for ( var i = 0; i < form.elements.length && !hay; i++ )
	{	
		var e = form.elements[i];
		if ( e.type == "select-one" && e.name.indexOf("permisosNuevo") != -1 )
		{
			hay = e.value != "NONE";			
		}				
	}
	
	return hay;
}

function submitIt()
{
	var form = document.editFrm;
	var doSubmit = true;
		
	if ( form.nuevoDelegado )
	{
		if ( form.nuevoDelegado.value != "u0" )
		{		
			if ( !hayPermisos() )
			{
				alert( "<?=$AppUI->_("Please select some permission level")?>" );
				doSubmit = false;
			}
		}
		if ( hayPermisos() && form.nuevoDelegado.value == "u0" )
		{
			alert( "<?=$AppUI->_("Please select some user to delegate on")?>");
			doSubmit = false;
		}		
	}
		
	if ( doSubmit )
	{
		form.submit();
	}
}
</script>

<form name="editFrm" action="./index.php?m=system&a=addeditdeleg" method="post">
	<input type="hidden" name="dosql" value="do_delegate_aed" />	
<table width="100%" border="0" cellpadding="1" cellspacing="1" class="std">
<tr height="20">
	<th colspan="2"><?php echo $AppUI->_('User delegates');?>: <?php echo "$AppUI->user_first_name $AppUI->user_last_name";?></th>
</tr>
<tr>
	<td>
		<table width="100%" border="0" cellpadding="1" cellspacing="1" class="std">
			<tr>
				<th>
					<?=$AppUI->_("User")?>
				</th>
				<?
				foreach ( $modulos as $m )
				{
				?>
				<th>
					<?=$AppUI->_($m["mod_ui_name"]	)?>
				</th>
				<?
				}
				?>
			</tr>
			<?
			require_once( $AppUI->getModuleClass( "admin" ) );
			$usr = new CUser();
			$usr->load( $AppUI->user_id );
			$delegados = $usr->getDelegates();			
			$noDelegados = $usr->getNonDelegates();
			//Tuve que meter la u al principio para que el array_merge no me reviente las claves.
			$nuevosUsuarios = array( "u0" => "-" );
			foreach ( $noDelegados as $nd )
			{
				$und = new CUser();
				$und->load( $nd["user_id"] );
				$nuevoUsuario = array( "u".$und->user_id => $und->user_first_name." ".$und->user_last_name );				
				$nuevosUsuarios = array_merge( $nuevosUsuarios, $nuevoUsuario );				
			}
			foreach ( $delegados as $d )
			{
				$usr_del = new CUser();
				$usr_del->load( $d["delegate_id"] );
			?>			
			<tr>
				<td>
					<?=$usr_del->user_first_name." ".$usr_del->user_last_name?>					
				</td>
				<?
				foreach ( $modulos as $m )
				{					
					$permiso = $usr->getDelegatePermission( $usr_del->user_id, $m["mod_id"] );
					?>
					<td>
					<?						
					if ( $m["mod_ui_name"] != "Webmail" )
					{				
						echo arraySelect( $tiposPermiso, "permisos[{$usr_del->user_id}][{$m['mod_id']}]", 'class="text"', $permiso, true );
					}
					else
					{						
						echo arraySelect( $tiposPermisoMail, "permisos[{$usr_del->user_id}][{$m['mod_id']}]", 'class="text"', $permiso, true );
					}
					?>
					</td>
					<?
				}
				?>
			</tr>
			<?
			}
			if ( count( $nuevosUsuarios ) > 1 )
			{				
			?>
			<tr>
				<th colspan="5" align="center">
					<?=$AppUI->_("New delegate")?>
				</th>
			</tr>
			<tr>
				<td>
					<?=arraySelect( $nuevosUsuarios, "nuevoDelegado", 'class="text"', "0" ) ?>
				</td>
				<?
				foreach ( $modulos as $m )
				{
					?>
					<td>
					<?					
					if ( $m["mod_ui_name"] != "Webmail" )
					{
						echo arraySelect( $tiposPermiso, "permisosNuevo[{$m['mod_id']}]", 'class="text"', "NONE", true );
					}
					else
					{						
						echo arraySelect( $tiposPermisoMail, "permisosNuevo[{$m['mod_id']}]", 'class="text"', "NONE", true );
					}
					?>
					</td>
					<?
				}
				?>
			</tr>
			<?
			}
			?>
		</table>
	</td>
</tr>
<tr>
	<td align="left"><input class="button"  type="button" value="<?php echo $AppUI->_('back');?>" onClick="javascript:history.back(-1);" /></td>
	<td align="right"><input class="button" type="button" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt()" /></td>
</tr>
</table>
</form>