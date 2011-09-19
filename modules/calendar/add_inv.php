<?
$event_id = intval(dPgetParam( $_GET, "event_id", "" ));
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $delegator_id );
$bCheckListado = dPgetParam( $_POST, "hdnCheckListado", null );
$mod_id = 4;
// check permissions

$obj = new CEvent();

if (!$obj->load( $event_id ) && $event_id) 
{
	$AppUI->setMsg( 'Event' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

if ( $delegator_id != $AppUI->user_id )
{
	require_once( $AppUI->getModuleClass( "admin" ) );	
	$usr = new CUser();
	$usr->load( $AppUI->user_id );
	if ( !$usr->isDelegator( $delegator_id, $mod_id ) && $AppUI->user_type != 1 )
	{
		$AppUI->setMsg("Delegator");
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$usr->load( $delegator_id );
	$permiso = $usr->getDelegatePermission( $AppUI->user_id, $mod_id );
	$canEdit = $canEdit || ( $permiso == "AUTHOR" && $obj->event_creator == $AppUI->user_id && $obj->event_owner == $delegator_id );
	$canEdit = $canEdit || ( $permiso == "EDITOR" && $obj->event_owner == $delegator_id );
	$canEdit = $canEdit || $AppUI->user_type == 1;
}

if ( !$canEdit )
{
	$AppUI->redirect( "m=public&a=access_denied" );
}

$d = new CDate( $obj->event_start_date );

// setup the title block
$titulo = $AppUI->_("Add invitations to")." ".$obj->event_title;

$titleBlock = new CTitleBlock( $titulo, 'calendar.gif', $m, "$m.$a" );

if ( $d && $dialog != 1)
{
	$first_week_day = $d;
	while ( $first_week_day->getDayOfWeek() != 1 )
	{
		$first_week_day->addDays(-1);
	}
	$titleBlock->addCrumb( "?m=calendar&delegator_id=$delegator_id&dialog=$dialog&a=month_view&date=".$d->format( FMT_TIMESTAMP_DATE ), "month view" );
	$titleBlock->addCrumb( "?m=calendar&delegator_id=$delegator_id&date=".$first_week_day->format( FMT_TIMESTAMP_DATE )."&dialog=$dialog", "week view" );
	$titleBlock->addCrumb( "?m=calendar&a=day_view&dialog=$dialog&delegator_id=$delegator_id&date=".$d->format( FMT_TIMESTAMP_DATE ), "day view" );
	$titleBlock->addCrumb( "?m=calendar&a=addedit&delegator_id=$delegator_id&dialog=$dialog&event_id=$event_id", "edit this event" );
}

$titleBlock->show();
?>

<?php
	
	$event = new CEvent();
	
	if (!$event->load( $event_id ) && $event_id)
	{
		$AppUI->setMsg( 'Event' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect();
	}
	
	$invited = $event->getInvitations();
?>
	
<script language="javascript">	
	function OnInit()
	{
		usersSelected = document.editFrm.selectedUsers;
		contactsSelected = document.editFrm.selectedContacts;
		mailsSelected = document.editFrm.selectedMails;

		<? foreach ($invited as $row){ 
			if($row['invitation_sent'] == '0'){?>
				<? if($row["user_id"] > 0){ ?>
					usersSelected.options[ usersSelected.length ] = new Option( '<?php echo($row["user_last_name"].", ".$row["user_first_name"]) ?>', <?php echo($row["user_id"]) ?> );
					document.getElementById("sacarUsuario").style.visibility = "visible";
				<?}?>

				<? if($row["contact_id"] > 0){?>
					contactsSelected.options[ contactsSelected.length ] = new Option( '<?php echo($row["contact_last_name"].", ".$row["contact_first_name"]) ?>', <?php echo($row["contact_id"]) ?> );
					document.getElementById("sacarContacto").style.visibility = "visible";
				<?}?>

				<? if($row["invitation_mail"] != ""){?>
					mailsSelected.options[ mailsSelected.length ] = new Option( '<?php echo($row["invitation_mail"]) ?>', '<?php echo($row["invitation_mail"])?>' );
					document.getElementById("sacarMail").style.visibility = "visible";
				<?}?>
			<?}?>
		<?}?>
		
		if ( usersSelected.length > 1)
			usersSelected.options[0] = null;
			
		if ( contactsSelected.length > 1)
			contactsSelected.options[0] = null;

		if ( mailsSelected.length > 1)
			mailsSelected.options[0] = null;

	}
</script>

<script language="javascript">
function verDisponibilidades()
{
	var href="index.php?m=calendar&a=availabilities&users=" + aplanarCombo( document.editFrm.selectedUsers ) + "&dialog=1&suppressLogo=1&date=<?=$d->format( FMT_TIMESTAMP )?>";
	var height = 80 * document.editFrm.selectedUsers.length;
	window.open( href, 'availabilities', 'top=20,left=20,width=950,height=' + height + ',scrollbars=yes,resizable=yes');
}

function submitIt()
{
	var form = document.editFrm;
	var doSubmit = true;
	
	var su = false;
	var sc = false;
	
	if ( form.selectedUsers )
		su = aplanarCombo( form.selectedUsers ) != "";
		
	if ( form.selectedContacts )
		sc = aplanarCombo( form.selectedContacts ) != "";
		
	if ( form.selectedMails )
		sm = aplanarCombo( form.selectedMails ) != "";
	
	if ( !su && !sm && !sc )
	{		
		alert( "<?=$AppUI->_("Please select some people to invite")?>");
		doSubmit = false;
	}
	
	if ( doSubmit )
	{
		if ( form.selectedUsers ) form.usuarios.value = aplanarCombo( form.selectedUsers );
        if ( form.selectedContacts ) form.contactos.value = aplanarCombo( form.selectedContacts );
        if ( form.selectedMails ) form.mails.value = aplanarCombo( form.selectedMails );
        /*
        if ( form.selectedContacts )
			form.mails.value = (form.mails.value != "" ? form.mails.value + ";" : "") + aplanarCombo( form.selectedContacts );
        */
        form.submit();
	}	
}

function pasarDeCombo( origen, destino, i )
{
	var opt = new Option( origen.options[i].text, origen.options[i].value );
	
	destino.options[ destino.length ] = opt;
	origen.options[i] = null;
}

function selectUser()
{	
	origen = document.editFrm.availableUsers;
	destino = document.editFrm.selectedUsers;
	
	var i, habia = destino.options[0].value != "-1", pase = false;
	
	for ( i = 0; i < origen.length; i++ )
	{		
		if ( origen.options[i].selected )
		{			
			pase = true;
			pasarDeCombo( origen, destino, i );
			i--;
			document.getElementById("sacarUsuario").style.visibility = "visible";
			document.getElementById("verDisponibilidades").style.visibility = "visible";
		}	
	}
	
	if ( pase && !habia )
	{
		destino.options[0] = null;
	}
	
	document.getElementById("agregarUsuario").style.visibility = (origen.length == 0 ? "hidden" : "visible");
	
	if ( origen.length == 0 )
	{
		origen.options[0] = new Option ( "--<?=$AppUI->_("No more users available")?>--", "-1" );
	}
}

function unSelectUser()
{	
	destino = document.editFrm.availableUsers;
	origen = document.editFrm.selectedUsers;
	
	var i, habia = destino.options[0].value != "-1", pase = false;
	
	for ( i = 0; i < origen.length; i++ )
	{		
		if ( origen.options[i].selected )
		{			
			pase = true;
			pasarDeCombo( origen, destino, i );
			i--;
			document.getElementById("agregarUsuario").style.visibility = "visible";			
		}	
	}
	
	if ( pase && !habia )
	{
		destino.options[0] = null;
	}
	
	document.getElementById("sacarUsuario").style.visibility = (origen.length == 0 ? "hidden" : "visible");
	document.getElementById("verDisponibilidades").style.visibility = (origen.length == 0 ? "hidden" : "visible");
	if ( origen.length == 0 )
	{
		origen.options[0] = new Option( "--<?=$AppUI->_("Select some users")?>--", "-1" );
	}
}

function selectContact()
{	
	origen = document.editFrm.availableContacts;
	destino = document.editFrm.selectedContacts;
	
	var i, pase = false, habia = destino.options[0].value != "-1";
	
	for ( i = 0; i < origen.length; i++ )
	{		
		if ( origen.options[i].selected )
		{			
			pase = true;
			pasarDeCombo( origen, destino, i );
			i--;
			document.getElementById("sacarContacto").style.visibility = "visible";			
		}	
	}
	
	if ( pase && !habia )
	{
		destino.options[0] = null;
	}
	
	document.getElementById("agregarContacto").style.visibility = (origen.length == 0 ? "hidden" : "visible");
	
	if ( origen.length == 0 )
	{
		origen.options[0] = new Option ( "--<?=$AppUI->_("No more contacts available")?>--", "-1" );
	}
}

function unSelectContact()
{	
	destino = document.editFrm.availableContacts;
	origen = document.editFrm.selectedContacts;
	
	var i, habia, pase = false;
	
	habia = destino.length > 1;
	for ( i = 0; i < origen.length; i++ )
	{		
		if ( origen.options[i].selected )
		{	
			pase = true;
			pasarDeCombo( origen, destino, i );
			i--;
			document.getElementById("agregarContacto").style.visibility = "visible";			
		}	
	}
	
	if ( pase && !habia )
	{
		destino.options[0] = null;
	}
	document.getElementById("sacarContacto").style.visibility = (origen.length == 0 ? "hidden" : "visible");
	if ( origen.length == 0 )
	{
		origen.options[0] = new Option( "--<?=$AppUI->_("Select some contacts")?>--", -1 );
	}
}

function esMail( s )
{	
	var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
	return emailPattern.test(s);
}

function selectMail()
{	
	destino = document.editFrm.selectedMails;
	origen = document.editFrm.mail;
	
	if ( !esMail( origen.value ) )
	{
		alert( "<?=$AppUI->_("Please enter a valid e-mail address")?>");
		origen.focus();
		return;
	}
	
	for ( i = 0; i < destino.length; i++ )
	{
		if(destino.options[i].value == origen.value)
		{
			alert( "<?=$AppUI->_("That person has already been invited")?>");
			origen.focus();
			return;
		}
	}
	
	if ( destino.length == 1 && destino.options[0].value == -1 )
		destino.options[0] = null;
				
	destino.options[destino.length] = new Option ( origen.value, origen.value );
	origen.value="";
	document.getElementById("sacarMail").style.visibility = "visible";
}

function unSelectMail()
{	
	origen = document.editFrm.selectedMails;
	
	var i;
	
	for ( i = 0; i < origen.length; i++ )
	{		
		if ( origen.options[i].selected )
		{	
			origen.options[i] = null;
			i--;			
		}	
	}
	
	document.getElementById("sacarMail").style.visibility = (origen.length == 0 ? "hidden" : "visible");
	if ( origen.length == 0 )
	{
		origen.options[0] = new Option( "--<?=$AppUI->_("Enter some e-mails")?>--", -1 );
	}
}

function aplanarCombo( combo )
{	
	var s = "";
		
	if ( combo.length > 0 )
	{
		for ( i = 0; i < combo.length - 1; i++ )
		{
			if ( combo.options[i].value != "-1" )
				s += combo.options[i].value + ";";
		}
		if ( combo.options[i].value != "-1" )
			s += combo.options[i].value;	
	}
	return s;
}
</script>
<?
$invitaciones = $obj->getInvitations();
$usuariosInvitados = "$AppUI->user_id";
$mailsInvitados = "";

foreach( $invitaciones as $inv )
{
	if ( $inv["user_id"] )
	{
		$usuariosInvitados .= ",".$inv["user_id"];
	}
	else if ( $inv["contact_id"] )
	{
		if ( strlen( $contactosInvitados ) > 0 )
			$contactosInvitados .= ",";

		$contactosInvitados .= $inv["contact_id"];
	}
	else
	{
		if ( strlen( $mailsInvitados ) > 0 )
		{
			$mailsInvitados .= ",";
		}
		$mailsInvitados .= "'".$inv["invitation_mail"]. "'";
	}
}
$sqlUsuarios = "SELECT * from users WHERE user_id NOT IN ( $usuariosInvitados ) and user_type<>5 ORDER BY user_last_name, user_first_name";
$usuarios = db_loadList( $sqlUsuarios );
?>
<form name="editFrm" action="?m=calendar&a=do_inv_add&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" method="post">
	<input type="hidden" name="dosql" value="do_inv_add" />
	<input type="hidden" name="event_id" value="<?php echo $event_id;?>" />	
	<input type="hidden" name="usuarios" value="" />
    <input type="hidden" name="contactos" value="" />
	<input type="hidden" name="mails" value="" />
<table cellspacing="0" cellpadding="2" border="0" width="100%" class="">
<?
if ( count( $usuarios ) > 0 )
{
	?>
	<tr>
		<th colspan="2" align="right">
			<?=$AppUI->_("PSA users")?>
		</th>
		<th align="right">
			<span id="verDisponibilidades" style="visibility:hidden"><input type="button" class="button" value="<?=$AppUI->_("View user availabilities")?>" onClick="verDisponibilidades()"></span>
		</th>
	</tr>
	<tr class="tableHeaderGral">
		<th align="right">
			<?=$AppUI->_("Available users")?>
		</th>
		<th align="center">
			&nbsp;
		</th>
		<th align="left">
			<?=$AppUI->_("Selected users")?>
		</th>
	</tr>
	<tr class="tableForm_bg">
		<td align="right">
			<select name="availableUsers" size="10" style="width:'300 px';" multiple="yes">
				<?
				foreach( $usuarios as $u )
				{
					?>
					<option value="<?=$u["user_id"]?>"><?=$u["user_last_name"].", ".$u["user_first_name"]?></option>
					<?
				}
				?>
			</select>
		</td>
		<td align="center">
			<table>
				<tr>
					<td id="agregarUsuario" align="center">
						<input type="button" class="button" value="<?=$AppUI->_("Add")?>" onClick="selectUser()">
					</td>
				</tr>
				<tr>
					<td id="sacarUsuario" align="center" style="visibility:hidden">
						<input type="button" class="button" value="<?=$AppUI->_("Remove")?>" onClick="unSelectUser()">
					</td>
				</tr>
			</table>
		</td>
		<td align="left">
			<select name="selectedUsers" size="10" style="width:'300 px';" multiple="yes">
				<option value="-1">--<?=$AppUI->_("Select some users")?>--</option>				
			</select>
		</td>
	</tr>
<?
}
require_once( $AppUI->getModuleClass( "admin" ) );
$usr = new CUser();
$usr->load( $AppUI->user_id );
$where = strlen( $contactosInvitados ) > 0 ? "( contact_id NOT IN ( $contactosInvitados ) ) AND ( contact_email IS NOT NULL )" : "contact_email IS NOT NULL";
//$debugsql = 1;

$contactos = $usr->getContacts( $where, " ORDER BY contact_last_name, contact_first_name" );

if ( count( $contactos ) > 0 )
{	
?>
	<tr>
		<th colspan="3" align="center">
			<?=$AppUI->_("Contacts")?>
		</th>
	</tr>
	<tr class="tableHeaderGral">
		<th align="right">
			<?=$AppUI->_("Available contacts")?>
		</th>
		<th>
			&nbsp;
		</th>
		<th align="left">
			<?=$AppUI->_("Selected contacts")?>
		</th>
	</tr>
	<tr class="tableForm_bg">
		<td align="right">
			<select name="availableContacts" size="10" style="width:'300 px';" multiple="yes">
				<?
				foreach( $contactos as $c )
				{
					?>
					<option value="<?=$c["contact_id"]?>"><?=$c["contact_last_name"].", ".$c["contact_first_name"]?></option>
					<?
				}
				?>
			</select>
		</td>
		<td align="center">
			<table>
				<tr>
					<td id="agregarContacto" align="center">
						<input type="button" class="button" value="<?=$AppUI->_("Add")?>" onClick="selectContact()">
					</td>
				</tr>
				<tr>
					<td id="sacarContacto" align="center" style="visibility:hidden">
						<input type="button" class="button" value="<?=$AppUI->_("Remove")?>" onClick="unSelectContact()">
					</td>
				</tr>
			</table>
		</td>
		<td align="left">
			<select name="selectedContacts" size="10" style="width:'300 px';" multiple="yes">
				<option value="-1">--<?=$AppUI->_("Select some contacts")?>--</option>			
			</select>
		</td>
	</tr>
<?
}
?>
	
	<tr>
		<th colspan="3" align="center">
			<?=$AppUI->_("Others")?>
		</th>
	</tr>
	<tr class="tableHeaderGral">
		<th align="right">
			<?=$AppUI->_("E-mail")?>
		</th>
		<th>
			&nbsp;
		</th>
		<th align="left">
			<?=$AppUI->_("Selected users")?>
		</th>
	</tr>
	<tr class="tableForm_bg">
		<td align="right">
			<table>
				<tr>
					<td align="center">
						<input type="text" class="texto" style="width:'200 px';" name="mail">
					</td>
				</tr>
				<tr>
					<td style="visibility:hidden">
						<input type="button" value="">
					</td>
				</tr>
			</table>			
		</td>
		<td align="center">
			<table>
				<tr>
					<td id="agregarMail" align="center">
						<input type="button" class="button" value="<?=$AppUI->_("Add")?>" onClick="selectMail()">
					</td>
				</tr>
				<tr>
					<td id="sacarMail" align="center" style="visibility:hidden">
						<input type="button" class="button" value="<?=$AppUI->_("Remove")?>" onClick="unSelectMail()">
					</td>
				</tr>
			</table>
		</td>
		<td align="left">
			<select name="selectedMails" size="10" style="width:'300 px';" multiple="yes">
				<option value="-1">--<?=$AppUI->_("Enter some e-mails")?></option>				
			</select>
		</td>
	</tr>
</table>
<br>
<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std">
<tr>
	<td colspan="2">
        <?php
            $strJSaction = "window.location.href='?m=calendar&a=addedit&delegator_id=$delegator_id&dialog=$dialog&event_id=$event_id';";
            if(isset($bCheckListado)) $strJSaction = "javascript:history.back();";
        ?>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onclick="<?php echo $strJSaction; ?>">
	</td>
	<td align="right" colspan="2">
		<input type="button" value="<?php echo $AppUI->_( 'submit' );?>" class="button" onClick="submitIt()">
	</td>
</tr>
</table>
</form>
<script language="javascript">OnInit();</script>