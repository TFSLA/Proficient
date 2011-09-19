<?php /* CONTACTS $Id: edit_contact.php,v 1.5 2009-07-29 17:00:00 nnimis Exp $ */

require_once('./modules/contacts/contacts.class.php');

$contact_id = intval( dPgetParam( $_GET, 'contact_id', 0 ) );
$user_id = dPgetParam( $_GET, "user_id", $AppUI->user_id );
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 6;

// load the record data
$msg = '';
$row = new CContact();
$canDelete = $row->canDelete( $msg, $contact_id );

if ( !$row->load( $contact_id ) && $contact_id > 0)  
{
	$AppUI->setMsg( 'Contact' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} 

//echo "<pre>";print_r($row);echo "</pre>";die;

if ( $user_id != $AppUI->user_id )
{
	require_once( $AppUI->getModuleClass( "admin" ) );
	$usr = new CUser();
	$usr->load( $AppUI->user_id );

	//Hay que chequear que este sea un delegador valido	
	if ( !$usr->isDelegator($user_id, $mod_id) && $AppUI->user_type != 1 )
	{		
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$delegador = new CUser();
	$delegador->load( $user_id );
	$permisos = $delegador->getDelegatePermission( $AppUI->user_id, $mod_id );
	if ( $contact_id )
	{
		$canEdit = $permisos == "AUTHOR" && $row->contact_creator == $AppUI->user_id && $row->contact_owner == $user_id;
		$canEdit = $canEdit || ($permisos == "EDITOR" && $row->contact_owner == $user_id);
		$canEdit = $canEdit || $AppUI->user_type == 1;
	}
	else
	{
		$canEdit = 1;
	}
}
else
{
	if (!$canRead)
	{
		$AppUI->redirect( "m=public&a=access_denied" );
	}

	// check permissions for this record
	$canEdit = !getDenyEdit( $m, $contact_id );
}

/*<uenrico>*/
// load locations arrays
$Clocation = new CLocation();

$Clocation->loadCountries();
$Clocation->addItemAtBeginOfCountries($Clocation->addItem(0, "Not Specified"));

$Clocation->loadStates();
$Clocation->addItemAtBeginOfStates($Clocation->addItemState("0","0","Not Specified"));
/*</uenrico>*/

// setup the title block
$ttl = $AppUI->_('Edit contact');
$titleBlock = new CTitleBlock( $ttl, 'handshake.gif', $m, "companies.index" );

$titleBlock->show();

if (!$canEdit) $ro=" READONLY ";


//Creo un array con todos los contactos menos el que estoy editando (si no es uno nuevo)
   $query = " SELECT contact_id, contact_first_name, contact_last_name, contact_company, contact_public
   FROM contacts";
   if ($contact_id !=0)
   		$query .= " WHERE contact_id != $contact_id";

   $sql = mysql_query($query);
   $strJS_contact = "var MyContacts = new Array();\n";      
           
   while($vec=mysql_fetch_array($sql))
	{
		$name = str_replace("'","`",$vec['contact_first_name']); 
    $last_name = str_replace("'","`",$vec['contact_last_name']);
		$company = str_replace("'","`",$vec['contact_company']);
		$contact_public = str_replace("'","`",$vec['contact_public']);
    $strJS_contact .= "MyContacts[MyContacts.length] = new Array('".$name."', '".$last_name."','".$company."','".$vec['contact_id']."','".$contact_public."');\n";
	}

//Retorna si se puede o no hacer privado el contacto que se esta editando.
//Se va a poder si se es el dueño del contacto o un admin
function puede_privatizar()
{
	global $row, $AppUI;
	return ($row->contact_owner==$AppUI->user_id OR $AppUI->user_type==1) ? 'TRUE' : 'FALSE';
}

//Regresa si un contacto PRIVADO ya existe en la DB
function existe_contacto($contact_first_name, $contact_last_name, $contact_company)
{
	global $AppUI;
	$sql = "SELECT COUNT(*) FROM contacts where contact_first_name = '$contact_first_name' AND contact_last_name = '$contact_last_name' AND contact_company = '$contact_company' AND contact_public = 0;";
	
	$resultado = mysql_query( $sql );
	if ( $resultado == FALSE)
		return "false";

	return (mysql_result($resultado, 0) > 1 ) ? "true" : "false";	
}

?>
<script language="javascript">

function duplicado()
{
	var form = document.changecontact;
	var puede_privatizar = '<?php echo puede_privatizar();?>';
	
	//Valido que pueda hacer privado el contacto
	if (puede_privatizar == 'FALSE')
	{
		alert1("<?= $AppUI->_('ContactNotOwner2');?>");
		form.contact_public.checked=true;
		return;
	}
	
	if (form.contact_public.checked)
	{
	  for(var h = 0; h < MyContacts.length; h++)
	  {
    	if((form.contact_first_name.value==MyContacts[h][0])&&(form.contact_last_name.value==MyContacts[h][1])&&(form.contact_company.value==MyContacts[h][2])&&(MyContacts[h][4])=='1')
    	{
    		alert1("<?php echo $AppUI->_('contactPublico');?>");
    		form.contact_public.checked=false;
				
			}
	  }
	}
}


function popCalendar( field ){
	calendarField = field;	
	idate = eval( 'document.changecontact.contact_' + field + '.value' );	
	if ( idate == "0000-00-00" )
	{
		idate = "<?=$date?>";		
	}
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate +'&suppressLogo=1', 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.changecontact.contact_' + calendarField );
	fld_fdate = eval( 'document.changecontact.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;	
}


function stripCharsInBag (s, bag)
{   var i;
    var returnString = "";
    
    for (i = 0; i < s.length; i++)
    {   var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }

     return returnString;
}

<? echo $strJS_contact; echo $strJS_contact_all;?>

function submitIt() {
	var form = document.changecontact;
    var rta = true;

	form.contact_first_name.value = stripCharsInBag(trim(form.contact_first_name.value),',;');
	form.contact_last_name.value = stripCharsInBag(trim(form.contact_last_name.value),',;');
	form.contact_order_by.value = stripCharsInBag(trim(form.contact_order_by.value),';');
	form.contact_email.value = stripCharsInBag(trim(form.contact_email.value),',;');
	form.contact_email2.value = stripCharsInBag(trim(form.contact_email2.value),',;');
	form.contact_icq.value = stripCharsInBag(trim(form.contact_icq.value),',;');
	form.contact_company.value = stripCharsInBag(trim(form.contact_company.value),',;');
	form.contact_title.value =  stripCharsInBag(trim(form.contact_title.value),',;');
	form.contact_type.value = stripCharsInBag(trim(form.contact_type.value),',;');
	form.contact_address1.value = stripCharsInBag(trim(form.contact_address1.value),',;');
	form.contact_city.value = stripCharsInBag(trim(form.contact_city.value),',;');
	form.contact_zip.value = stripCharsInBag(trim(form.contact_zip.value),',;');
	form.contact_department.value = stripCharsInBag(trim(form.contact_department.value),',;');
	form.contact_manager.value = stripCharsInBag(trim(form.contact_manager.value),',;');
	form.contact_assistant.value = stripCharsInBag(trim(form.contact_assistant.value),',;');
	form.contact_website.value = stripCharsInBag(trim(form.contact_website.value),',;');
	form.contact_company_phone.value = stripCharsInBag(trim(form.contact_company_phone.value),',;');
	form.contact_phone.value = stripCharsInBag(trim(form.contact_phone.value),',;');
	form.contact_phone2.value = stripCharsInBag(trim(form.contact_phone2.value),',;');
	form.contact_business_phone.value = stripCharsInBag(trim(form.contact_business_phone.value),',;');
	form.contact_business_phone2.value = stripCharsInBag(trim(form.contact_business_phone2.value),',;');
	form.contact_fax.value = stripCharsInBag(trim(form.contact_fax.value),',;');
	form.contact_mobile.value = stripCharsInBag(trim(form.contact_mobile.value),',;');
	form.contact_notes.value = stripCharsInBag(trim(form.contact_notes.value),',;');

	

	if ((trim(form.contact_last_name.value).length < 1) && (rta) ){
		alert1( "<?php echo $AppUI->_('contactsValidName');?>" );
		rta = false;
		form.contact_last_name.focus();
	} 
    if ((trim(form.contact_order_by.value).length < 1)&& (rta)) {
		alert1( "<?php echo $AppUI->_('contactsOrderBy');?>" );
		form.contact_order_by.focus();
		rta = false;
	}
	 
	if ((form.contact_id.value =='0')&&(rta)) {
	  for(var h = 0; h < MyContacts.length; h++)
	  {
	  	/*
	  	*Me fijo si el usuario que esta creando ya existe (tiene que coincideir el nombre, apellido y empresa)
	  	*/
    	if((form.contact_first_name.value==MyContacts[h][0])&&(form.contact_last_name.value==MyContacts[h][1])&&(form.contact_company.value==MyContacts[h][2]))
      {
      	if (MyContacts[h][4] == '1') //SI EL CONTACTO existe como PUBLICO
      	{
					if ( confirm1("<?php echo $AppUI->_('contactsExist');?>") )
					{
						rta = true;
						break;
					}
					else
					{
						window.location = 'index.php?m=contacts';
						rta = false;
					}
				}
		 }
	  }
	}
     if ((trim(form.contact_email.value).length > 0 && !isEmail(trim(form.contact_email.value)))&& (rta)) {
		alert1( "<?php echo $AppUI->_('Please enter a valid email address').". ".$AppUI->_("E-mail");?>" );
		form.contact_email.focus();
		rta = false;
	} 
    if ((trim(form.contact_email2.value).length > 0 && !isEmail(trim(form.contact_email2.value)))&& (rta)) {
		alert1( "<?php echo $AppUI->_('Please enter a valid email address').". ".$AppUI->_("E-mail")." 2";?>" );
		form.contact_email2.focus();
		rta = false;
	} 

	if ((form.contact_company.value == "")) {
		alert1("<?php echo $AppUI->_('contact_company');?>");
		form.contact_company_ch.value = "1";
	 }

		if(rta){
		form.submit();
		}
}

function delIt(){
	var form = document.changecontact;
	if(confirm1( "<?php echo $AppUI->_('contactsDelete');?>" )) {
		form.del.value = "<?php echo $contact_id;?>";
		form.submit();
	}
}

function orderByName( x ){
	var form = document.changecontact;
	if (x == "name") {
		form.contact_order_by.value = form.contact_last_name.value + ", " + form.contact_first_name.value;
	} else {
		form.contact_order_by.value = form.contact_company.value;
	}
}

/*
locations functions
*/
/*<uenrico>*/
	<?php 
		$Clocation->setFrmName("changecontact");
		$Clocation->setCboCountries("contact_country_id");
		$Clocation->setCboStates("contact_state_id");
		$Clocation->setJSSelectedState(@$row->contact_state_id);
		
		echo $Clocation->generateJS();
		
	?>
/*</uenrico>*/
</script>
<form name="changecontact" action="?m=companies&a=do_contact_aed" method="post">
	<input type="hidden" name="contact_id" value="<?php echo $contact_id;?>" />
	<input type="hidden" name="contact_owner" value="<?php echo ($row->contact_owner) ? $row->contact_owner : $user_id;?>" />
	<input type="hidden" name="contact_company_ch" value="0" />
	<input type="hidden" name="contact_creator" value="<?php echo $row->contact_creator ? $row->contact_creator : $AppUI->user_id;?>" />
	
<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<tr>
	<td valign="top">
		<table border="0" cellpadding="1" cellspacing="1">
		<tr>
			<td align="right"><?php echo $AppUI->_('First Name');?>:</td>
			<td><input <?=$ro?> type="text" class="text" size=25 name="contact_first_name" value="<?php echo @$row->contact_first_name;?>" maxlength="50" /></td>
		</tr>
		<!-- <tr>
			<td align="right"><?php echo $AppUI->_('Second Name');?>:</td>
			<td><input <?=$ro?> type="text" class="text" size=25 name="contact_middle_name" value="<?php echo @$row->contact_middle_name;?>" maxlength="50" /></td>
		</tr> -->
		<tr>
			<td align="right">&nbsp;&nbsp;<?php echo $AppUI->_('Last Name');?>:</td>
			<td><input <?=$ro?> type="text" class="text" size=25 name="contact_last_name" value="<?php echo @$row->contact_last_name;?>" maxlength="50" <?php if($contact_id==0){?> onBlur="orderByName('name')"<?php }?> /><a href="#" onClick="orderByName('name')">[<?php echo $AppUI->_('use in display');?>]</a></td>
		</tr>
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Display Name');?>: </td>
			<td><input <?=$ro?> type="text" class="text" size=25 name="contact_order_by" value="<?php echo @$row->contact_order_by;?>" maxlength="50" /></td>
		</tr>		
		<tr>	
			<td align="right" width="100"><?php echo $AppUI->_('Public Entry');?>
			<?
			if ( $canEdit ) 
			{
			?>			
				:</td><td><input <?=$ro?> type="checkbox" value="1" onclick="duplicado();" name="contact_public" <?php echo (@$row->contact_public ? 'checked' : '');?> disabled />
			<?
			}
			else
			{			
			?>	
				&nbsp;</td><td><input type="hidden" value="1" name="contact_public" />
			<?
			}
			?>
			</td>
		</tr>
		</table>
	</td>
	<td valign="top">
		<table border="0" cellpadding="1" cellspacing="1">
		<tr>
			<td align="right"><?php echo $AppUI->_('Email');?>:</td>
			<td nowrap>
				<input <?=$ro?> type="text" class="text" name="contact_email" value="<?php echo @$row->contact_email;?>" maxlength="255" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Email');?>2:</td>
			<td>
				<input <?=$ro?> type="text" class="text" name="contact_email2" value="<?php echo @$row->contact_email2;?>" maxlength="255" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?=$AppUI->_('IM Address')?>:</td>
			<td>
				<input <?=$ro?> type="text" class="text" name="contact_icq" value="<?php echo @$row->contact_icq;?>" maxlength="20" size="25" />
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td valign="top" width="50%">
		<table border="0" cellpadding="1" cellspacing="1" class="details" width="100%">
		<tr>
			<td align="right"><?php echo $AppUI->_('Company');?>:</td>
			<td nowrap>
				<input <?=$ro?> type="text" class="text" name="contact_company" value="<?php echo @$row->contact_company;?>" maxlength="100" size="25" />
				<a href="#" onClick="orderByName('company')">[<?php echo $AppUI->_('use in display');?>]</a></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Job Title');?>:</td>
			<td><input <?=$ro?> type="text" class="text" name="contact_title" value="<?php echo @$row->contact_title;?>" maxlength="50" size="25" /></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Type');?>:</td>
			<td><input <?=$ro?> type="text" class="text" name="contact_type" value="<?php echo @$row->contact_type;?>" maxlength="50" size="25" /></td>
		</tr>
		<tr>
			<td align="right" valign="top"><?php echo $AppUI->_('Address');?>:</td>
			<td><textarea <?=$ro?> class="textarea" name="contact_address1" rows="3" cols="25"><?php echo @$row->contact_address1;?></textarea></td>			
		</tr>

		<tr>
			<td align="right"><?php echo $AppUI->_('City');?>:</td>
			<td><input <?=$ro?> type="text" class="text" name="contact_city" value="<?php echo @$row->contact_city;?>" maxlength="30" size="25" /></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Zip');?>:</td>
			<td><input <?=$ro?> type="text" class="text" name="contact_zip" value="<?php echo @$row->contact_zip;?>" maxlength="11" size="25" /></td>
		</tr>
		<?php /*<uenrico>*/ ?>
		<tr>
			<td align="right"><?php echo $AppUI->_('Country');?>:</td>
			<td nowrap>
				<?php echo $Clocation->generateHTMLcboCountries(@$row->contact_country_id, "text"); ?>
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('State');?>:</td>
			<td><?php echo $Clocation->generateHTMLcboStates(@$row->contact_state_id, "text"); ?></td>
		</tr>
		<?php 
			echo $Clocation->generateJScallFunctions();
		?>
		<?php /*</uenrico>*/ ?>
		<tr>
			<td align="right"><?php echo $AppUI->_('Birthday');?>:</td>
			<td nowrap>
			<?php
			$df = $AppUI->getPref('SHDATEFORMAT');
			$date = $row->contact_birthday ? new CDate($row->contact_birthday) : '';
			?>
			
			<input type="hidden" name="contact_birthday" value="<?php echo $date ? $date->format( FMT_TIMESTAMP_DATE ) : '';?>">
			<input type="text" name="birthday" maxlength="10" size="12" value="<?php echo $date ? $date->format( $AppUI->getPref('SHDATEFORMAT') ) : '';?>" class="text" disabled="disabled">
			<a href="#" onClick="popCalendar('birthday')">
				<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
			</a>			
						
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Department');?>:</td>
			<td nowrap>
				<input <?=$ro?> type="text" class="text" name="contact_department" value="<?php echo @$row->contact_department;?>" maxlength="32" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Manager\'s name');?>:</td>
			<td nowrap>
				<input <?=$ro?> type="text" class="text" name="contact_manager" value="<?php echo @$row->contact_manager;?>" maxlength="32" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Assistant\'s name');?>:</td>
			<td nowrap>
				<input <?=$ro?> type="text" class="text" name="contact_assistant" value="<?php echo @$row->contact_assistant;?>" maxlength="32" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Web Site address');?>:</td>
			<td nowrap>
				<input <?=$ro?> type="text" class="text" name="contact_website" value="<?php echo @$row->contact_website;?>" maxlength="32" size="25" />
			</td>
		</tr>
		</table>
	</td>
	<td valign="top" width="50%">

		<table border="0" cellpadding="1" cellspacing="1" class="details" width="100%">
		<tr>
			<td align="right"><?php echo $AppUI->_('Company Phone');?>:</td>
			<td>
				<input <?=$ro?> type="text" class="text" name="contact_company_phone" value="<?php echo @$row->contact_company_phone;?>" maxlength="30" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Home Phone');?>:</td>
			<td>
				<input <?=$ro?> type="text" class="text" name="contact_phone" value="<?php echo @$row->contact_phone;?>" maxlength="30" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Home Phone');?> 2:</td>
			<td>
				<input <?=$ro?> type="text" class="text" name="contact_phone2" value="<?php echo @$row->contact_phone2;?>" maxlength="30" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Business Phone');?>:</td>
			<td>
				<input <?=$ro?> type="text" class="text" name="contact_business_phone" value="<?php echo @$row->contact_business_phone;?>" maxlength="30" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Business Phone');?> 2:</td>
			<td>
				<input <?=$ro?> type="text" class="text" name="contact_business_phone2" value="<?php echo @$row->contact_business_phone2;?>" maxlength="30" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Fax');?>:</td>
			<td>
				<input <?=$ro?> type="text" class="text" name="contact_fax" value="<?php echo @$row->contact_fax;?>" maxlength="30" size="25" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Mobile Phone');?>:</td>
			<td>
				<input <?=$ro?> type="text" class="text" name="contact_mobile" value="<?php echo @$row->contact_mobile;?>" maxlength="30" size="25" />
			</td>
		</tr>
		</table>
		<strong><?php echo $AppUI->_('Contact Notes');?></strong><br />
		<textarea <?=$ro?> class="textarea" name="contact_notes" rows="8" cols="60"><?php echo @$row->contact_notes;?></textarea></td>
	</td>
</tr>
<tr>
	<td>		
		<input type="button" value="<?php echo $AppUI->_('close');?>" class="button" onClick="javascript:window.close();" />
	</td>
	<td align="right">
<?if ($canEdit) { ?>		<input type="button" value="<?php echo $AppUI->_('submit');?>" class="button" onClick="submitIt()" />
<?}?>
	</td>
</tr>
</table>
</form>