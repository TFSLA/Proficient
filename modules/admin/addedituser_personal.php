<script language="javascript">

function popChgPwd()
{
	window.open( './index.php?m=public&a=chpwd&dialog=1&suppressLogo=1', 'chpwd', 'top=250,left=250,width=350, height=220, scollbars=false' );
}

function doRemoveFotoChica()
{
	var form = document.delFrm;
	form.do_remove.value="user_pic";
	form.submit();
}

function setEmailConf()
{
  var pop3_arr = new Array();
  var smtp_arr = new Array();
  var imap_arr = new Array();
  var port_arr = new Array();
  
  <?
  $sql = "SELECT * FROM companies ORDER BY company_name";
  $companies = db_loadList( $sql );
  foreach ($companies as $row)
  {
    echo "  port_arr[".$row["company_id"]."] = '".$row["company_mail_server_port"]."';\n";
    echo "  pop3_arr[".$row["company_id"]."] = '".$row["company_pop3"]."';\n";
    echo "  imap_arr[".$row["company_id"]."] = '".$row["company_imap"]."';\n";
    echo "  smtp_arr[".$row["company_id"]."] = '".$row["company_smtp"]."';\n";
  }
  ?>
  
  if(document.editFrm.user_company.value!=0)
  {
    if(port_arr[document.editFrm.user_company.value]==110)
    {
      document.editFrm.user_mail_server_port.selectedIndex=0;
    }
    else
    {
      document.editFrm.user_mail_server_port.selectedIndex=1;
    }
    document.editFrm.user_pop3.value=pop3_arr[document.editFrm.user_company.value];
    document.editFrm.user_smtp.value=smtp_arr[document.editFrm.user_company.value];
    document.editFrm.user_imap.value=imap_arr[document.editFrm.user_company.value];
  }
}

</script>
<?php /* ADMIN $Id: addedituser_personal.php,v 1.2 2009-05-22 00:20:08 ctobares Exp $ */
//add or edit a system user

//Valido que tenga permisos para el modulo
if (getDenyEdit("admin") && $_GET['user_id']!= $AppUI->user_id)
	 $AppUI->redirect( "m=public&a=access_denied" );

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
$default_user_status = 0;
$canReadHHRR = !getDenyRead("hhrr") || $user_id == $AppUI->user_id;
$canEditHHRR = !getDenyEdit("hhrr") || $user_id == $AppUI->user_id;
// load sysvals
$IMtypes = dPgetSysVal( 'IMType' );
$estCivil = dPgetSysVal( 'MaritalState' );
$uploads_dir = $AppUI->getConfig('hhrr_uploads_dir');
$df = $AppUI->getPref('SHDATEFORMAT');

if (!defined('LOCALE_TIME_FORMAT'))
  define('LOCALE_TIME_FORMAT', '%I:%M %p');

// load locations arrays
$Clocation = new CLocation();
$Clocation->loadCountries();
$Clocation->addItemAtBeginOfCountries($Clocation->addItem(0, "Not Specified"));
$Clocation->loadStates();
$Clocation->addItemAtBeginOfStates($Clocation->addItemState("0","0","Not Specified"));

// check permissions
if (!$canEdit &&  $user_id!=$AppUI->user_id) {
    $AppUI->redirect( "m=public&a=access_denied" );
}

//Con esto verifico que el usuario edite a su nive: usuario solo pueden editar usuarios, s pueden editar cualquier cosa
if ( !edit_admin($AppUI->user_id) )
	$AppUI->redirect( "m=public&a=access_denied" );

// que no sea system admin y adem? est?modificando sus datos
$restricted = ($AppUI->user_type != 1 && $user_id==$AppUI->user_id); 

$sql = "
SELECT u.*, company_id
	FROM users u
	LEFT JOIN companies ON u.user_company = companies.company_id
	WHERE u.user_id = '$user_id'
";

$rta_sql = db_loadHash( $sql, $user );

if (!$rta_sql && $user_id > 0)
{
	$titleBlock = new CTitleBlock( 'Invalid User ID', 'user_management.gif', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	$titleBlock->show();
}
else
{
	// pull companies
	$sql = "SELECT company_id, company_name FROM companies ORDER BY company_name";
	$companies = arrayMerge( array( 0 => '' ), db_loadHashList( $sql ) );
	
	if (!$user_id) {
		$user["user_company"] = $AppUI->user_company;
		$user["user_type"] = 2;
	}	
	
	$user_birthday = intval(  $user["user_birthday"] ) ? new CDate( $user["user_birthday"] ) : null;
	
	// setup the title block
	$ttl = $user_id > 0 ? "Edit User" : "Add User";
	$titleBlock = new CTitleBlock( $ttl, 'user_management.gif', $m, "$m.$a" );
	if(!getDenyRead('users')){
		$titleBlock->addCrumb( "?m=admin&tab=0", "users list" );
		$titleBlock->addCrumb( "?m=admin&tab=1", "template list" );
	}
	if ($user_id > 0){
		$titleBlock->addCrumb( "?m=admin&a=viewuser&user_id=$user_id", "view this user" );
		$titleBlock->addCrumb( "?m=system&a=addeditpref&user_id=$user_id", "edit preferences" );
		
		if ($canReadHHRR)
			$titleBlock->addCrumb( "?m=hhrr&a=addedit&tab=1&id=".$user_id, "edit hhrr information" );
			
		$titleBlock->addCrumb( "?m=admin&a=calendars&user_id=".$user_id, "work calendar" );
		$titleBlock->addCrumb( "javascript: popChgPwd();", "change password" );
	}
	
	$titleBlock->show();
	?>

	<script language="javascript">
	function submitIt(){
		var form = document.editFrm;
		if (form.use_pop_values[1].checked)
			form.user_smtp_use_pop_values.value = 0;
		else
			form.user_smtp_use_pop_values.value = 1;

		if (form.smtp_auth_check.checked)
			form.user_smtp_auth.value = 1;
		else
			form.user_smtp_auth.value = 0;

		var today = new Date();
		//var today = today.getYear().toString()+"-"+(today.getMonth()+1).toString()+"-"+today.getDate().toString();

		var birth = new Date(form.user_birthday.value.substr(0,4), 
													form.user_birthday.value.substr(4,2), 
													form.user_birthday.value.substr(6,2));
													
		 var may_18 = new Date((birth.getFullYear() +18),(birth.getMonth()-1),birth.getDate());
                        
                         var diferencia =today.getTime() - may_18.getTime() ;
                         var dias = Math.round(diferencia / (1000 * 60 * 60 * 24));											

		if (trim(form.user_first_name.value).length < 1) {
			alert("<?php echo $AppUI->_('adminValidFirstName');?>");
			form.user_first_name.focus();
		} else if (trim(form.user_last_name.value).length < 1) {
			alert("<?php echo $AppUI->_('adminValidLastName');?>");
			form.user_last_name.focus();
		} else if (!isEmail(form.user_email.value)) {
			alert("<?php echo $AppUI->_('adminInvalidEmail');?>");
			form.user_email.focus();
		} else if (form.user_email_alternative1.value.length > 0 && !isEmail(form.user_email_alternative1.value)) {
			alert("<?php echo $AppUI->_('adminInvalidEmailAlternative1');?>");
			form.user_email_alternative1.focus();
		} else if (form.user_email_alternative2.value.length > 0 && !isEmail(form.user_email_alternative2.value)) {
			alert("<?php echo $AppUI->_('adminInvalidEmailAlternative2');?>");
			form.user_email_alternative2.focus();        
		} else if ((form.user_email_alternative1.value.length > 0 || form.user_email_alternative2.value.length > 0) && (form.user_email.value == form.user_email_alternative1.value || form.user_email.value == form.user_email_alternative2.value || form.user_email_alternative1.value == form.user_email_alternative2.value)) {
			alert("<?php echo $AppUI->_('adminInvalidEmailEquals');?>");
			form.user_email.focus();
		}else if(trim(form.user_email_password.value) !=  trim(form.user_email_password_check.value)){
			  alert("<?php echo $AppUI->_('emailPasswordsDiffer');?>");
			  form.user_email_password.focus();
		}else if(form.user_smtp_auth.value == 1 && form.user_smtp_use_pop_values.value == 0 &&
				(trim(form.user_smtp_username.value)=="")){
			  alert("<?php echo $AppUI->_('smtpUsernameEmpty');?>");
			  form.user_smtp_username.focus();
		/*} else if (form.user_birthday.value > today ) {*/
		} else if (dias <=0  ) {
			alert("<?php echo $AppUI->_('adminInvalidBirthday_18');?>");

		} else {
			form.submit();
		}
	}

	function popCalendar( field ){
		calendarField = field;
		idate = eval( 'document.editFrm.user_' + field + '.value' );
		window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
	}

	function setCalendar( idate, fdate ) {
		fld_date = eval( 'document.editFrm.user_' + calendarField );
		fld_fdate = eval( 'document.editFrm.' + calendarField );
		fld_date.value = idate;
		fld_fdate.value = fdate;
	}

	   function switchSmtpAuth(){
			var auth = document.editFrm.smtp_auth_check.checked;
			if (auth){
				document.getElementById('smtp_auth').style.display='';
			}else{
				document.getElementById('smtp_auth').style.display='none';
			}
	   }
	   function showSmtpSettings(){
			var upv = document.editFrm.use_pop_values[1].checked;
			if (upv){
				document.getElementById('smtp_settings').style.display='';
			}else{
				document.getElementById('smtp_settings').style.display='none';
			}
	   }

		<?php 
			$Clocation->setFrmName("editFrm");
			$Clocation->setCboCountries("user_country_id");
			$Clocation->setCboStates("user_state_id");
			$Clocation->setJSSelectedState($user["user_state_id"]);

			echo $Clocation->generateJS();

		?>

	</script>
	<?
	$back = strstr($_SERVER[HTTP_REFERER],'=');

	?>

	<form name="delFrm" action="" method="POST">
		<input type="hidden" name="dosql" value="do_user_aed" />
		<input type="hidden" name="do_remove" value="" />
		<input type="hidden" name="origen" value="<? echo $origen;?>" />
		<input type="hidden" name="back" value="m<? echo $back;?>" />	
		<input type="hidden" name="user_id" value="<?php echo intval($user["user_id"]);?>" />
		<input type="hidden" name="photo_name" value="<?php echo $user["user_pic"];?>" />
	</form>

	<table border="0" cellpadding="1" cellspacing="1" height="400" width="100%" class="std">
	<form name="editFrm" action="" method="post" enctype="multipart/form-data">
		<input type="hidden" name="user_id" value="<?php echo intval($user["user_id"]);?>" />
		<input type="hidden" name="dosql" value="do_user_aed" />
		<input type="hidden" name="origen" value="<? echo $origen;?>" />
		<input type="hidden" name="back" value="m<? echo $back;?>" />
		<input type="hidden" name="do_remove" value="" />

		<input type = "hidden" name = "user_company" value = "<?=$user["user_company"];?>" >

	<tr>
		<td align="left">
			<input type="button" value="<?php echo $AppUI->_('back');?>" onClick="javascript:history.back(-1);" class="button" />
		</td>
		<td align="center"  colspan="2">&nbsp;</td>
		<td align="right" nowrap>
			<input type="button" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt()" class="button" />
		</td>
	</tr>		
		<tr><th colspan="4">
		<b><?=$AppUI->_('Personal Information')?></b>
		</th></tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('First Name');?>:</td>
		<td><input type="text" class="text" name="user_first_name" value="<?php echo $user["user_first_name"];?>" maxlength="50" size="30" />&nbsp;*</td>
		<td align="center" rowspan="7" colspan="2" valign="middle">
		  <? if($user["user_pic"]!="ninguna" && $user["user_pic"]!="" && file_exists($uploads_dir."/".$user["user_id"]."/".$user["user_pic"])){?>
			<img border="1" height="100" width="100" src="<?=$uploads_dir."/".$user["user_id"]?>/<?=$user["user_pic"]?>">
			</br></br><input class="text" type="button" value="<?=$AppUI->_('Remove')?>" onclick="doRemoveFotoChica()">
		  <?}else echo "<br><br><br><b>".$AppUI->_("NoPhoto")."</b>"?>
		</td>    
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Last Name');?>:</td>
		<td> <input type="text" class="text" name="user_last_name" value="<?php echo $user["user_last_name"];?>" maxlength="50" size="30" />&nbsp;*</td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Email');?>:</td>
		<td><input type="text" class="text" name="user_email" value="<?php echo $user["user_email"];?>" maxlength="255" size="40" />&nbsp;* </td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('E-mail Alternative 1');?>:</td>
		<td><input type="text" class="text" name="user_email_alternative1" value="<?php echo $user["user_email_alternative1"];?>" maxlength="255" size="40" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('E-mail Alternative 2');?>:</td>
		<td><input type="text" class="text" name="user_email_alternative2" value="<?php echo $user["user_email_alternative2"];?>" maxlength="255" size="40" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Address');?> 1:</td>
		<td><input type="text" class="text" name="user_address1" value="<?php echo $user["user_address1"];?>" maxlength="30" size="30" /> </td></tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Address');?> 2:</td>
		<td><input type="text" class="text" name="user_address2" value="<?php echo $user["user_address2"];?>" maxlength="30" size="30" /> </td></tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('City');?>:</td>
		<td><input type="text" class="text" name="user_city" value="<?php echo $user["user_city"];?>" maxlength="30" size="25" />&nbsp;&nbsp;&nbsp;</TD>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('ZIP');?>:</td>
		<td><input type="text" class="text" name="user_zip" value="<?php echo $user["user_zip"];?>" maxlength="11" size="10" /></td>
	</tr>
	<?php /*<uenrico>*/ ?>
	<tr>
		<td align="right"><?php echo $AppUI->_('Country');?>:</td>
		<td><?php echo $Clocation->generateHTMLcboCountries($user["user_country_id"], "text"); ?></td>
		<td nowrap="nowrap" class="right" valign="top"><?=$AppUI->_("Photo");?> (100x100):&nbsp;</td>
		<td colspan="3" valign="top">
			<input class="text" size="25" type="file" name="user_pic">
		</td>
	<tr>
	<td align="right"><?php echo $AppUI->_('State');?>:</td>
		<td><?php echo $Clocation->generateHTMLcboStates("", "text"); ?></td>
		<td align="right">
		   <?php echo $AppUI->_('Nationality');?>:
		</td>
		<td>
		   <!--<input type="text" class="text" name="nationality" value="<?php echo $user["nationality"];?>" maxlength="24" size="20" />-->
		   <?

						//$desc = "description_".$AppUI->user_prefs['LOCALE'];
						$desc = "description_es";

						$query = "SELECT nationality_id , $desc  FROM location_nationalities ORDER BY $desc ASC"; 
						$sql = mysql_query($query);

					?>
					<select name="nationality" class="text" style="width:160px;" >
						<option value="" ><?php echo $AppUI->_('Not Specified')?></option>

						<?

						while($vec = mysql_fetch_array($sql) )
						{ 
						  if ($vec[nationality_id]==$user["nationality"]){
							$selected = "selected";
						  }else{
							$selected = "";
						  }

						  echo "<option " .$selected ." value=\"".$vec[nationality_id]."\">".$vec[$desc]."</option>";
						}
						?>
					  </select>
		</td>
	</tr>
	<?php 
		echo $Clocation->generateJScallFunctions();
	?>
	<?php /*</uenrico>*/ ?>
	<tr>
		<td align="right"><?php echo $AppUI->_('Phone');?>:</td>
		<td><input type="text" class="text" name="user_phone" value="<?php echo $user["user_phone"];?>" maxlength="30" size="20" /> </td>
		<td align="right"><?php echo $AppUI->_('Home Phone');?>:</td>
		<td><input type="text" class="text" name="user_home_phone" value="<?php echo $user["user_home_phone"];?>" maxlength="30" size="20" /> </td></tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('IM Type');?>:</td>
		<td><?=arraySelect( $IMtypes, 'user_im_type', 'size="1" class="text"', @$user["user_im_type"], true,'','65px' )."&nbsp;".$AppUI->_('IM Id');?>: <input type="text" class="text" name="user_im_id" value="<?php echo $user["user_im_id"];?>" maxlength="50" size="25"> </td>
		<td align="right"><?php echo $AppUI->_('Mobile');?>:</td>
		<td><input type="text" class="text" name="user_mobile" value="<?php echo $user["user_mobile"];?>" maxlength="30" size="20" /> </td>
	</tr>
	<tr>
	 <td align="right"><?php echo $AppUI->_('Birthday');?>:</td>
		<td>
			&nbsp;<input type="text" name="birthday" value="<?php echo $user_birthday ? $user_birthday->format( $df ) : "" ;?>" class="text" disabled="disabled" size="12" />
						<a href="javascript: //" onClick="popCalendar('birthday')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0"></a> 
						<input type="hidden" name="user_birthday" value="<?php echo $user_birthday ? $user_birthday->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />&nbsp;
			<? /*
			<input type="text" class="text" name="user_birthday" value="<?php if(intval($user["user_birthday"])!=0) { echo substr($user["user_birthday"],0,10);}?>" maxlength="50" size="12" />(YYYY-MM-DD)
			*/ ?>
		</td>
		<td align="right"><?php echo $AppUI->_('Marital State');?>:</td>
		<td><?=arraySelect( $estCivil, 'maritalstate', 'size="1" class="text"', @$user["maritalstate"], true,'','120px' );?></td>
	</tr>
	<tr>
	 <td align="right">
		<select name="doctype" size="1" class="text" >
		  <?     
		  $doctype=$user["doctype"];
			if($doctype=="DNI") echo '<option value="DNI" selected>DNI</option>';
			else                echo '<option value="DNI">DNI</option>';
			if($doctype=="LC")  echo '<option value="LC" selected>LC</option>';
			else                echo '<option value="LC">LC</option>';
			if($doctype=="LE")  echo '<option value="LE" selected>LE</option>';
			else                echo '<option value="LE">LE</option>';
		  ?>
		</select>:</td>
		<td colspan="3">
		<input class="text" size="12" maxlength="16" type="text" name="docnumber" value="<?= $user["docnumber"] ?>"></td>
	</tr> 
	<tr><td colspan="4">
		&nbsp;
		</td></tr>	
		<tr><th colspan="4">
		<b><?=$AppUI->_('Email Configuration')?></b>
		</th></tr>	
	<tr>
		<td align="right"><?php echo $AppUI->_('SMTP Server');?>:</td>
		<td colspan="3"><input type="text" class="text" name="user_smtp" value="<?php echo $user["user_smtp"];?>" size=30 maxlength="255" />&nbsp;
		<a href="javascript:setEmailConf();" title="<?php echo $AppUI->_('Back to Company\'s default email server values');?>"><?php echo $AppUI->_('Company\'s default values');?></a></td>
	</tr>
	<tr>
		<td align="right" valign="top"><?php echo $AppUI->_('SMTP Authentication');?>:</td>
		<td colspan="3">
			<input type="checkbox" name="smtp_auth_check" value="1" <?php echo @$user["user_smtp_auth"] == "1" ? "checked" : "";?> onchange="switchSmtpAuth();" onclick="switchSmtpAuth();"/><br>
			<input type="hidden" name="user_smtp_auth" value="<?php echo @$user["user_smtp_auth"];?>" />
			<input type="hidden" name="user_smtp_use_pop_values" value="<?php echo @$user["user_smtp_use_pop_values"];?>" />
			<div id="smtp_auth" name="smtp_auth">
			<input type="radio" name="use_pop_values" value="1" <?php echo @$user["user_smtp_use_pop_values"] != "0" ? "checked" : "";?> onclick="showSmtpSettings();" />
				<?php echo $AppUI->_('Use settings of mail retrieval');?><br>
			<input type="radio" name="use_pop_values" value="0" <?php echo @$user["user_smtp_use_pop_values"] == "0" ? "checked" : "";?> onclick="showSmtpSettings();" />
				<?php echo $AppUI->_('Use specific settings');?><br>
				<div id="smtp_settings" name="smtp_settings">	
				<table cellpadding="0" cellspacing="2" border="0">
				<tr>
					<td align="right"><?php echo $AppUI->_('Username');?>:</td>
					<td>
						<input type="text" class="text" name="user_smtp_username" value="<?php echo $user["user_smtp_username"];?>" size=30 maxlength="64" />
					</td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_('Password');?>:</td>
					<td>
						<input type="password" class="text" name="user_smtp_password" value="<?php echo $user["user_smtp_password"];?>" size=30 maxlength="64"  />
					</td>	
				</tr>	
				</table>
				</div>
			</div>
		</td>
	</tr>

	<tr>
		<td align="right"><?php echo $AppUI->_('Email Protocol');?>:</td>
		<td colspan="3">
			<select class="text" name="user_mail_server_port" >
			<option value="110" <?if($user["user_mail_server_port"]==110) echo "selected"?>>POP3</option>
		<option value="143" <?if($user["user_mail_server_port"]==143) echo "selected"?>>IMAP</option>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('POP3 Server');?>:</td>
		<td colspan="3"><input type="text" class="text" name="user_pop3" value="<?php echo $user["user_pop3"];?>" size=30 maxlength="255" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('IMAP Server');?>:</td>
		<td colspan="3"><input type="text" class="text" name="user_imap" value="<?php echo $user["user_imap"];?>" size=30 maxlength="255" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Email username');?>:</td>
		<td><input type="text" class="text" name="user_email_user" value="<?php echo $user["user_email_user"];?>" size=30 maxlength="255" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Email password');?>:</td>
		<td colspan="3"><input type="password" class="text" name="user_email_password" value="<?php echo $user["user_email_password"];?>" size=30 maxlength="255" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Email password2');?>:</td>
		<td colspan="3"><input type="password" class="text" name="user_email_password_check" value="<?php echo $user["user_email_password"];?>" size=30 maxlength="255" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Webmail autologin');?>:</td>
		<td colspan="3"><select class="text" name="user_webmail_autologin">
			<option value="Yes" <?php if($user["user_webmail_autologin"]=="Yes")echo " selected ";?>>Yes</option>
			<option value="No"  <?php if($user["user_webmail_autologin"]=="No")echo " selected ";?>>No </option>
			</td>
	</tr>
	<tr>
		<td align="right" valign=top><?php echo $AppUI->_('Signature');?>:</td>
		<td colspan="3"><textarea class="text" cols=50 name="user_signature" style="height: 50px"><?php echo @$user["user_signature"];?></textarea></td>
	</tr>
	<tr>
		<td align="left">
			<input type="button" value="<?php echo $AppUI->_('back');?>" onClick="javascript:history.back(-1);" class="button" />
		</td>
		<td align="center"  colspan="2">&nbsp;
	<?  if(!$user["user_id"]){ ?>    
		<input type="checkbox" name="return2" value="1" <?php echo ($another_user ? "checked" : "")?> 
				onclick="syncreturn(this)" onchange="syncreturn(this)"/>
		<?php echo $AppUI->_('Return here to add other user')?>
	<?  }?> 
		</td>
		<td align="right">
			<input type="button" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt()" class="button" />
		</td>
	</tr>
	</form>
	</table>
	<script language="javascript">
		switchSmtpAuth();
		showSmtpSettings();
	<?php 
	</script>
<?php } ?>
