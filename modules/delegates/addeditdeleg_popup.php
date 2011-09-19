<?php

// esta es una modificacion del archivo addeditdeleg.php preparada para editar los delegados del usario que se pase como parametro.

$AppUI->savePlace();
if (isset ($_POST['all_radio'] ) )
	updateSecurity($_POST['all_radio'], 'all');

if ( isset($_GET['user_id']) )
{
	$user_id = $_GET['user_id'];
	
	if ( $user_id != $AppUI->user_id )
	{
		//Si edita otro delegado que no sean los de el verifico seguridad.
		$canEdit = !getDenyEdit('delegates');
		if (!$canEdit)
    	$AppUI->redirect( "m=public&a=access_denied" );	
	}
	
		$sql= "SELECT user_first_name, user_last_name FROM users where user_id = $user_id;";
		$rc=mysql_query($sql);
		$vec=mysql_fetch_array($rc);
		$user_first_name = $vec['user_first_name'];
		$user_last_name = $vec['user_last_name'];
}
else
{
	$user_id = $AppUI->user_id;
	$user_first_name = $AppUI->user_first_name;
	$user_last_name = $AppUI->user_last_name;
}


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
		
	if ( doSubmit )
	{
		form.submit();
	}
}

function submitItNewDel()
{
	var form = document.editFrm;
	var doSubmit = true;
		
	if ( form.nuevoDelegado )
	{
		if ( form.nuevoDelegado.value == "u0" )
		{
			alert( "<?=$AppUI->_("Please select some user to delegate on")?>");
			doSubmit = false;
		}		
		else
		{
			if ( !hayPermisos() )
			{
				alert( "<?=$AppUI->_("Please select some permission level")?>" );
				doSubmit = false;
			}
		}		
	}
		
	if ( doSubmit )
	{
		form.submit();
	}
}
</script>

<form name="editFrm" action="./index.php?m=delegates&a=addeditdeleg" method="post">
	<input type="hidden" name="dosql" value="do_delegate_aed" />
	<input type="hidden" name="user_id" value=<? echo $user_id; ?> />
<table width="100%" border="0" cellpadding="1" cellspacing="1" class="std">
<tr height="20">
	<th colspan="2"><?php echo $AppUI->_('User delegates');?>: <?php echo "$user_first_name $user_last_name";?></th>
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
				<th>
					&nbsp;
				</th>
			</tr>
			<?
			require_once( $AppUI->getModuleClass( "admin" ) );
			$usr = new CUser();
			$usr->load( $user_id );
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
			if ( count( $delegados ) )
			{
			?>
			<tr>
				<td colspan="6" align="right">
					<input type="button" value="<?=$AppUI->_("save permissions")?>" class="button" onClick="submitIt()" />
				</td>
			</tr>
			<?
			}
			if ( count( $nuevosUsuarios ) > 1 )
			{				
			?>
			<tr>
				<th colspan="4" align="center">
					<?=$AppUI->_("New delegate")?>
				</th>
				<th>
					&nbsp;
				</th>
			</tr>
			<tr>
				<td>
					<?=arraySelect( $nuevosUsuarios, "nuevoDelegado", 'class="text"', "0",'','','250px' ) ?>
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
				<td align = "right">
					<input type="button" value="<?=$AppUI->_("add delegate")?>" class="button" onClick="submitItNewDel()" />
				</td>
			</tr>
			<?
			}
			?>
		</table>
	</td>
</tr>
<tr>
	<td align="left">
		<input class="button"  type="button" value="<?php echo $AppUI->_('close');?>" onClick="javascript: window.close();" />
		&nbsp;&nbsp;&nbsp;
		<input class="button"  type="button" value="<?php echo $AppUI->_('guardar y cerrar');?>" onClick="javascript: submitIt(),window.close();" />
	</td>
</tr>
</table>
<p><?=$AppUI->_("In order to remove a delegate please set the permissions for every module of the delegate to 'none' and press 'save permissions'")?></p>
</form>