<?php /* COMPANIES $Id: addedit.php,v 1.14 2009-08-11 20:38:10 nnimis Exp $ */
$company_id = intval( dPgetParam( $_GET, "company_id", 0 ) );

// check permissions for this company
$canEdit = !getDenyEdit( $m, $company_id );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// load the company types
$types = dPgetSysVal( 'CompanyType' );

// load the record data
$sql = "
SELECT companies.*,users.user_first_name,users.user_last_name
FROM companies
LEFT JOIN users ON users.user_id = companies.company_owner
WHERE companies.company_id = $company_id
";

$obj = null;
if (!db_loadObject( $sql, $obj ) && $company_id > 0) {
	$AppUI->setMsg( 'Company' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

// collect all the users for the company owner list
$owners = array( '0'=>'' );

$owners = CUser::getEmpleados("user_id, CONCAT_WS(', ',user_last_name,user_first_name)");

/*<uenrico>*/
// load locations arrays
$Clocation = new CLocation();

$Clocation->loadCountries();
$Clocation->addItemAtBeginOfCountries($Clocation->addItem(0, "Not Specified"));

$Clocation->loadStates();
$Clocation->addItemAtBeginOfStates($Clocation->addItemState("0","0","Not Specified"));
/*</uenrico>*/



// setup the title block
$ttl = $company_id > 0 ? "Edit Company" : "Add Company";
$titleBlock = new CTitleBlock( $ttl, 'handshake.gif', $m, "companies.index" );
$titleBlock->addCrumb( "?m=companies", "companies list" );
if ($company_id != 0)
  $titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view this company" );
$titleBlock->show();

// build array of times in 30 minute increments
$times = array();
$t = new CDate();
$t->setTime( 0,0,0 );
if (!defined('LOCALE_TIME_FORMAT'))
  define('LOCALE_TIME_FORMAT', '%I:%M %p');
for ($m=0; $m < 60; $m++) {
	$times[$t->format( "%H%M%S" )] = $t->format( LOCALE_TIME_FORMAT );
	$t->addSeconds( 1800 );
}

// Traigo los datos del contacto // 

$sql2 = "select * from contacts where contact_id= '$obj->contact_id' ";
//$sql2 = mysql_query($query2);

$row = null;
db_loadObject( $sql2, $row );

if(count($row) > 0)
	$BlockEditButton = "";
else 
	$BlockEditButton = "disabled";
$disabled = "disabled";

?>

<script language="javascript">
function changeShowSupplierStatus(typeId)
{
	if(typeId == 3)
	{
		document.getElementById('trSupplierStatusLine').style.display = '';
		document.getElementById('trSupplierStatus').style.display = '';
		document.getElementById('company_supplier_status').options[0].selected = true;
	}
	else
	{
		document.getElementById('trSupplierStatusLine').style.display = 'none';
		document.getElementById('trSupplierStatus').style.display = 'none';
	}
}

function submitIt() {
	var form = document.changeclient;
	var status = true;
	
	if (trim(form.company_name.value).length < 3) {
		alert( "<?php echo $AppUI->_('companyValidName');?>" );
		form.company_name.focus();
		status = false;
	}
	
	if ( !testTimes( form.company_start_time, form.company_end_time ) )
	{
		alert ( "<?php echo $AppUI->_( 'companyValidStartTime' )?>" );
		form.company_start_time.focus();
		status = false;
	}
   	
	if ((!validateEmail(form.company_email.value)) && (status==true)){
		alert( "<?php echo $AppUI->_('companyValidmail');?>" );
		form.company_email.focus();
		status = false;
		document.getElementById("companyEmailValidator").style.display = "";
	}

    if (status == true)
	{
    	form.submit();
	}
}

function testTimes( t1, t2 )
{
	//Meter aca el codigo de validacion de los times
	return true;
}

function testURL( x ) {
	var test = "document.changeclient.company_primary_url.value";
	test = eval(test);
	if (test.length > 6) {
		newwin = window.open( "http://" + test, 'newwin', '' );
	}
}

function checkSMTP(){
	var field = document.changeclient.company_smtp;
	var host = trim(field.value);
	var serv = "smtp";
	var spn = document.getElementById("statusSmtp");
	if (spn)
		spn.innerHTML = "";	
	if (host.length){
		var flag = document.changeclient.checkedsmtp;
		flag.value = "0";
		window.open( 'index.php?m=public&a=validate_host&dialog=1&callback=rtaSMTP&host=' + host +'&serv=' + serv + '&suppressLogo=1', 'checksmtp', 'top=250,left=250,width=250, height=220' );
	}
}

function rtaSMTP(rta){
	var field = document.changeclient.company_smtp;
	var spn = document.getElementById("statusSmtp");
	if (spn){ 
		var flag = document.changeclient.checkedsmtp;
		flag.value = rta;	
		if (rta=="1"){
			spn.innerHTML = "<?php echo $AppUI->_("Status").": ".$AppUI->_("Active");?>";
		}else{
			spn.innerHTML = "<?php echo $AppUI->_("Status").": ".$AppUI->_("Inactive");?>";
		}
	}
	swapsubmit1();
}

function checkPOP3(){
	var field = document.changeclient.company_pop3;
	var host = trim(field.value);
	var serv = "pop3";
	var spn = document.getElementById("statusPop3");
	if (spn)
		spn.innerHTML = "";	
	if (host.length){
		var flag = document.changeclient.checkedpop3;
		flag.value = "0";
		window.open( 'index.php?m=public&a=validate_host&dialog=1&callback=rtaPOP3&host=' + host +'&serv=' + serv + '&suppressLogo=1', 'checkpop3', 'top=250,left=250,width=250, height=220' );
	}
}

function rtaPOP3(rta){
	var field = document.changeclient.company_imap;
	var spn = document.getElementById("statusPop3");
	if (spn){ 
		var flag = document.changeclient.checkedpop3;
		flag.value = rta;	
		if (rta=="1"){
			spn.innerHTML = "<?php echo $AppUI->_("Status").": ".$AppUI->_("Active");?>";
		}else{
			spn.innerHTML = "<?php echo $AppUI->_("Status").": ".$AppUI->_("Inactive");?>";
		}
	}
	swapsubmit1();
}

function checkIMAP(){
	var field = document.changeclient.company_imap;
	var host = trim(field.value);
	var serv = "imap";
	var spn = document.getElementById("statusImap");
	if (spn)
		spn.innerHTML = "";	
	if (host.length){
		var flag = document.changeclient.checkedimap;
		flag.value = "0";
		window.open( 'index.php?m=public&a=validate_host&dialog=1&callback=rtaIMAP&host=' + host +'&serv=' + serv + '&suppressLogo=1', 'checksmtp', 'top=250,left=250,width=250, height=220' );
	}
}

function rtaIMAP(rta){
	var field = document.changeclient.company_imap;
	var spn = document.getElementById("statusImap");
	if (spn){ 
		var flag = document.changeclient.checkedimap;
		flag.value = rta;
		if (rta=="1"){
			spn.innerHTML = "<?php echo $AppUI->_("Status").": ".$AppUI->_("Active");?>";
		}else{
			spn.innerHTML = "<?php echo $AppUI->_("Status").": ".$AppUI->_("Inactive");?>";
		}
	}
	swapsubmit1();
}

function changed(field){
	var valor = trim(document.changeclient.company_smtp.value);
	valor += trim(document.changeclient.company_pop3.value);
	valor += trim(document.changeclient.company_imap.value);	
	
	var spans = new  Array();
	spans["company_smtp"] = "statusSmtp";
	spans["company_pop3"] = "statusPop3";
	spans["company_imap"] = "statusImap";

	var flags = new  Array();
	flags["company_smtp"] = "checkedsmtp";
	flags["company_pop3"] = "checkedpop3";
	flags["company_imap"] = "checkedimap";	
	
	var cpo = eval("document.changeclient."+flags[field.name]+";");
	if (trim(field.value)==""){
		cpo.value="1";
	}else{
		cpo.value="0";
	}
		
	var spn = document.getElementById(spans[field.name]);
	if (valor.length){
		spn.innerHTML = "";	
	}else{
		spn.innerHTML = "";
	}
	swapsubmit1();
}

function swapsubmit1(){
	var smtp = parseFloat(trim(document.changeclient.checkedsmtp.value));
	var pop3 = parseFloat(trim(document.changeclient.checkedpop3.value));
	var imap = parseFloat(trim(document.changeclient.checkedimap.value));
	var boton = document.changeclient.submit1;
	if (smtp + pop3 + imap==3){
		boton.disabled=false;
	}else{
		boton.disabled=true;
	}
	
}
/*
locations functions
*/
/*<uenrico>*/
<?php 
	$Clocation->setFrmName("changeclient");
	$Clocation->setCboCountries("company_country_id");
	$Clocation->setCboStates("company_state_id");
	$Clocation->setJSSelectedState(dPformSafe(@$obj->company_state_id));
	
	echo $Clocation->generateJS();
?>
/*</uenrico>*/

function popContact() {
	window.open('./index.php?m=companies&a=contacts&dialog=1&suppressLogo=1&callback=setContact&table=contacts', 'selector', 'left=50,top=50,height=600,width=1000,resizable,status=yes');
}

function popNewContact() {
	var company_name = document.changeclient.company_name.value;
	window.open('./index.php?m=companies&a=add_contact&dialog=1&suppressLogo=1&callback=setContact&table=contacts&company_name=' + company_name, 'selector', 'left=50,top=50,height=600,width=1000,resizable,status=yes');
}

function popEditContact() {
	var f = document.changeclient;
	window.open('./index.php?m=companies&a=edit_contact&contact_id='+f.contact_id.value+'&dialog=1&suppressLogo=1&callback=setContact&table=contacts', 'selector', 'left=50,top=50,height=600,width=1000,resizable,status=yes');
}

function setContact(key,fname, last_name, email, email2, title, phone, type, icq, address1, phone2, business_phone, city, fax, zip, mobile, state, country, notes, department, manager, assistant,company, c_public ) {
	    var f = document.changeclient;
	    
		f.contact_id.value = key;
		f.contact_first_name.value = fname;
		f.contact_last_name.value = last_name;
		f.contact_email.value = email;
		f.contact_email2.value = email2;
	    f.contact_title.value = title;
		f.contact_phone.value = phone;
		f.contact_type.value = type;
		f.contact_icq.value = icq;
		f.contact_address1.value = address1;
		f.contact_phone2.value = phone2;
		f.contact_business_phone.value = business_phone;
		f.contact_city.value = city;
		f.contact_fax.value = fax;
		f.contact_zip.value = zip;
	    f.contact_mobile.value = mobile;
		f.contact_state.value = state;
		f.contact_country.value = country;
		f.contact_notes.value = notes;
		f.contact_department.value = department;
		f.contact_manager.value = manager;
		f.contact_assistant.value = assistant;
		f.contact_public.value = c_public;
			
		if(f.company_name.value != company)
		{
			if(confirm("<?=$AppUI->_("confirmContactCompanyUpdate1")?>" + "\n" + "<?=$AppUI->_("confirmContactCompanyUpdate2")?>" + "\n\n" + "<?=$AppUI->_("confirmContactCompanyUpdate3")?>"))
				xajax_updateContactCompany(key, f.company_name.value, true);
		}
	
		document.getElementById("editContactButton").disabled='';
}

function validateEmail(valor) {
	var filtro=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i
	var res;
    if(valor.length > 0){
	    if (!filtro.test(valor)){
	    	document.getElementById("companyEmailValidator").style.display = "";
	    	res = false;
	    }else{
	    	document.getElementById("companyEmailValidator").style.display = "none";    
	    	res = true;
	    }
    } else { res = true; }
    return res;
}
</script>
<table width="100%" id="tbConteiner" cellspacing="0" cellpadding="0" border="0">
 <tr>
 <form name="changeclient" action="?m=companies" method="post">
  <td>
<table cellspacing="1" cellpadding="1" border="0" width="100%" class="tableForm_bg">
	<input type="hidden" name="dosql" value="do_company_aed" />
	<input type="hidden" name="company_id" value="<?php echo $company_id;?>" />
	<input type="hidden" name="original_supplier_status" value="<?php echo @$obj->company_supplier_status;?>" />
<tr>
	<td align="right" width="175">&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr>
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Company Name');?>:</td>
	<td>
	   <input type="text" class="text" name="company_name" value="<?php echo dPformSafe(@$obj->company_name);?>" size="50" maxlength="255" /> <span class="notaroja">*<?php echo $AppUI->_('required');?></span>
	</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Email');?>:</td>
	<td>
		<input type="text" class="text" name="company_email" value="<?php echo dPformSafe(@$obj->company_email);?>" size="30" maxlength="255" onchange="validateEmail(this.value)" /><span id="companyEmailValidator" style="display:none;"><font color="Red">*</font></span>
	</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Phone');?>:</td>
	<td>
		<input type="text" class="text" name="company_phone1" value="<?php echo dPformSafe(@$obj->company_phone1);?>" maxlength="30" />
	</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Phone');?>2:</td>
	<td>
		<input type="text" class="text" name="company_phone2" value="<?php echo dPformSafe(@$obj->company_phone2);?>" maxlength="50" />
	</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Fax');?>:</td>
	<td>
		<input type="text" class="text" name="company_fax" value="<?php echo dPformSafe(@$obj->company_fax);?>" maxlength="30" />
	</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<!--
<tr>
	<td align="right"><?php echo $AppUI->_('companycanal'); ?>:</td>
	<td>
	   <? 
	
	   ?>
	   <select class="text" name="company_canal" >
	   <option value="0" ><?php echo $AppUI->_('Not Specified');?></option>
       
		<?
		   // Traigo las companias internas //
		   
		   $sql = "select company_id,company_name from companies where company_type='0' order by company_name asc";
           $query = mysql_query($sql);

		   while($vec = mysql_fetch_array($query))
		   {

		       if(@$obj->company_canal == $vec[company_id]) 
			   {
				$sel = "selected";
			   }
			   else
			   {
				$sel ="";
			   }

		   echo  "<option value=\"$vec[company_id]\" $sel >$vec[company_name]</option>";
		   }


		?>
		</select>
	</td>
</tr> -->
<tr>
	<td align="right"><?php echo $AppUI->_('companysegment');?>:</td>
	<td>

	   <select class="text" name="company_segment" >
	      <option value="0" ><?php echo $AppUI->_('Not Specified');?></option>

		<?
		   // Traigo las companias internas //

		    $lenguage = $AppUI->user_prefs[LOCALE];

			if ($lenguage == "es")
			{
			$sql = "select id_segment,description_es as description from segment order by description asc ";
			}

			if ($lenguage == "en")
			{
			$sql = "select id_segment,description_en as description from segment order by description asc ";
			}

           $query = mysql_query($sql);

		   while($vec = mysql_fetch_array($query))
		   {

		       if(@$obj->company_segment == $vec[id_segment]) 
			   {
			   $sel = "selected";
			   }
			   else
			   {
			   $sel ="";
			   }

		   echo  "<option value=\"$vec[id_segment]\" $sel >$vec[description]</option>";
		   }


		?>
		</select>
	</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Type');?>:</td>
	<td>
<?php 
	foreach ($types as $tipe_lang) {
		$types_lang[] = $AppUI->_($tipe_lang);
	}
	
	echo arraySelect( $types_lang, 'company_type', 'size="1" class="text" onchange="javascript:changeShowSupplierStatus(this.value);"', $obj->company_type);
?>
	</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr class="rowFormEmpty">
	<td colspan="2">&nbsp;</td>
</tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_04.gif">
  <tr> 
	<td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
	<td><img src="images/common/cuadradito_gris.gif" width="9" height="9"><span class="boldtext"><?php echo $AppUI->_('Address');?></span></td>
	<td width="6"> <div align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></div></td>
  </tr>
  <tr bgcolor="#666666">
	<td height="1" colspan="3"></td>
  </tr>
</table>
<table cellspacing="1" cellpadding="1" border="0" width="100%" class="tableForm_bg">
<tr>
	<td align="right" width="175">&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Address');?>1:</td>
	<td><input type="text" class="text" name="company_address1" value="<?php echo dPformSafe(@$obj->company_address1);?>" size=50 maxlength="255" /></td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Address');?>2:</td>
	<td><input type="text" class="text" name="company_address2" value="<?php echo dPformSafe(@$obj->company_address2);?>" size=50 maxlength="255" /></td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('City');?>:</td>
	<td><input type="text" class="text" name="company_city" value="<?php echo dPformSafe(@$obj->company_city);?>" size=50 maxlength="50" /></td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
    <td align="right"><?php echo $AppUI->_('Zip');?>:</td>
    <td><input type="text" class="text" name="company_zip" value="<?php echo dPformSafe(@$obj->company_zip);?>" maxlength="15" /></td>
</tr>
<tr>
    <td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<?php /*<uenrico>*/ ?>
<tr>
    <td align="right"><?php echo $AppUI->_('Country');?>:</td>
    <td><?php echo $Clocation->generateHTMLcboCountries(dPformSafe(@$obj->company_country_id), "text"); ?></td>
</tr>
<tr>
    <td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('State');?>:</td>
	<td><?php echo $Clocation->generateHTMLcboStates(dPformSafe(@$obj->company_state_id), "text"); ?></td>
</tr>
<tr>
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<?php 
	echo $Clocation->generateJScallFunctions();
?>
<?php /*</uenrico>*/ ?>
<tr>
	<td align="right">
		URL http://<A name="x"></a></td><td><input type="text" class="text" value="<?php echo dPformSafe(@$obj->company_primary_url);?>" name="company_primary_url" size="50" maxlength="255" />
		<a href="#x" onClick="testURL('CompanyURLOne')">[<?php echo $AppUI->_('test');?>]</a>
	</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Company Owner');?>:</td>
	<td>
<?php
	if(isset($obj->company_owner)) $owner = $obj->company_owner;
	else $owner = $AppUI->user_id;
	echo arraySelect( $owners, 'company_owner', 'size="1" class="text"', $owner );
?>
	</td>
</tr>
<tr id="trSupplierStatusLine" style="display:<? echo ($AppUI->_(@$obj->company_type) != 3 ? 'none' : '') ?>;">
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr id="trSupplierStatus" style="display:<? echo ($AppUI->_(@$obj->company_type) != 3 ? 'none' : '') ?>;">
	<td align="right"><?php echo $AppUI->_('Status');?>:</td>
	<td>
<?
	$arrSuppliersStatusTypes = CCompany::getSuppliersStatusTypes();
	$arrSuppliersStatusTypes = array_merge(array('0'=>''), $arrSuppliersStatusTypes);

	echo arraySelect( $arrSuppliersStatusTypes, 'company_supplier_status', 'size="1" class="text"', $obj->company_supplier_status, false, false, '200px');
	
	if($obj->company_supplier_change_status_user > 0)
	{
		$arrUser = CUser::getUsersFullName(array($obj->company_supplier_change_status_user));
		$supplierDate = new CDate($obj->company_supplier_change_status_date);
		echo '('.$arrUser[0]['fullname'].' '.$AppUI->_('on').' '.$supplierDate->format($AppUI->getPref('SHDATEFORMAT')).')';
	}
?>
	</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<?php
if ( !$obj->company_start_time )
{
	$obj->company_start_time = $AppUI->getConfig( 'cal_day_start' );
}
if ( !$obj->company_end_time )
{
	$obj->company_end_time = $AppUI->getConfig( 'cal_day_end' );
}
$start_str = "0000-00-00 ".$obj->company_start_time;
$end_str = "0000-00-00 ".$obj->company_end_time;
$start_t = new CDate( $start_str );
$end_t = new CDate( $end_str );
?>
<tr>
	<td align="right"><?php echo $AppUI->_('Start time');?>:</td>
	<td><?php echo arraySelect( $times, 'company_start_time', 'size="1" class="text"', $start_t->format("%H%M%S"),'','','90px' ); ?>&nbsp;(<?=$AppUI->_('Beginning companys labor schedule'); ?>)</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('End time');?>:</td>
	<td><?php echo arraySelect( $times, 'company_end_time', 'size="1" class="text"', $end_t->format("%H%M%S") ,'','','90px'); ?>&nbsp;(<?=$AppUI->_('Ending companys labor schedule'); ?>)</td>
</tr>
<tr>
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right" valign=top><?php echo $AppUI->_('Description');?>:</td>
	<td align="left">
		<textarea cols="70" rows="10" class="text" name="company_description"><?php echo @$obj->company_description;?></textarea>
	</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr class="rowFormEmpty"> 
	<td colspan="2">&nbsp;</td>
</tr>
</table>
<input type="hidden" name="contact_id" value="<? echo $row->contact_id; ?>">
<input type="hidden" name="contact_public" value="<? echo $row->contact_public; ?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_04.gif">
  <tr>
	<td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
	<td><img src="images/common/cuadradito_gris.gif" width="9" height="9"><span class="boldtext"><?php echo $AppUI->_('Principal Contact');?></span></td>
	<td width="6"> <div align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></div></td>
  </tr>
  <tr bgcolor="#666666"> 
	<td height="1" colspan="3"></td>
  </tr>
</table>
<table cellspacing="1" cellpadding="1" border="0" width="100%" class="tableForm_bg">
<tr>
	<td width="175">&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr> 
	<td height="1" colspan="6" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('First Name');?>:</td>
	<td><input <?=$ro?> type="text" class="text" size=25 name="contact_first_name" value="<?php echo @$row->contact_first_name;?>" maxlength="50" <? echo $disabled; ?>/></td>
	<td align="right"><?php echo $AppUI->_('Email');?>:</td>
	<td><input <?=$ro?> type="text" class="text" name="contact_email" value="<?php echo @$row->contact_email;?>" maxlength="255" size="25" <? echo $disabled; ?>/></td>
	<td><input type="button" class="buttonbig" value="<? echo $AppUI->_('link to contact'); ?>" onclick="popContact()" /></td>
</tr>
<tr> 
	<td height="1" colspan="6" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right">&nbsp;&nbsp;<?php echo $AppUI->_('Last Name');?>:</td>
	<td><input <?=$ro?> type="text" class="text" size=25 name="contact_last_name" value="<?php echo @$row->contact_last_name;?>" maxlength="50" <? echo $disabled; ?> /></td>
	<td align="right"><?php echo $AppUI->_('Email');?>2:</td>
	<td><input <?=$ro?> type="text" class="text" name="contact_email2" value="<?php echo @$row->contact_email2;?>" maxlength="255" size="25" <? echo $disabled; ?> /></td>
	<td><input type="button" class="buttonbig" value="<? echo $AppUI->_('new contact'); ?>" onclick="popNewContact()" /></td>
</tr>
<tr> 
	<td height="1" colspan="6" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Job Title');?>:</td>
	<td><input <?=$ro?> type="text" class="text" name="contact_title" value="<?php echo @$row->contact_title;?>" maxlength="50" size="25" <? echo $disabled; ?>/></td>
	<td align="right"><?php echo $AppUI->_('Home Phone');?>:</td>
	<td><input <?=$ro?> type="text" class="text" name="contact_phone" value="<?php echo @$row->contact_phone;?>" maxlength="30" size="25" <? echo $disabled; ?>/></td>
	<td><input type="button" id="editContactButton" name="editContactButton" class="buttonbig" value="<? echo $AppUI->_('edit contact'); ?>" onclick="popEditContact()" <?=$BlockEditButton?> /></td>
</tr>
<tr> 
	<td height="1" colspan="6" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Type');?>:</td>
	<td><input <?=$ro?> type="text" class="text" name="contact_type" value="<?php echo @$row->contact_type;?>" maxlength="50" size="25" <? echo $disabled; ?>/></td>
	<td align="right"><?=$AppUI->_('IM Address')?>:</td>
	<td><input <?=$ro?> type="text" class="text" name="contact_icq" value="<?php echo @$row->contact_icq;?>" maxlength="20" size="25" <? echo $disabled; ?>/></td>
</tr>
<tr> 
	<td height="1" colspan="6" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right" valign="top"><?php echo $AppUI->_('Address');?>:</td>
	<td><textarea <?=$ro?> class="textarea" name="contact_address1" rows="3" cols="25" <? echo $disabled; ?>><?php echo @$row->contact_address1;?></textarea></td>	
	<td rowspan=2 align="right">
		<table cellspacing="0" cellpadding="0">
		<tr>
			<td><?php echo $AppUI->_('Home Phone');?> 2:</td>
		</tr>
		<tr> 
			<td height="5" ></td>
		</tr>
		<tr>
			<td align='right'><?php echo $AppUI->_('Business Phone');?>:</td>
		</tr>
		</table>
	</td>
	<td rowspan=2 align="left">
		<table cellspacing="0" cellpadding="0">
		<tr>
			<td><input <?=$ro?> type="text" class="text" name="contact_phone2" value="<?php echo @$row->contact_phone2;?>" maxlength="30" size="25" <? echo $disabled; ?>/></td>
		</tr>
		<tr> 
			<td height="5" ></td>
		</tr>
		<tr>
			<td><input <?=$ro?> type="text" class="text" name="contact_business_phone" value="<?php echo @$row->contact_business_phone;?>" maxlength="30" size="25" <? echo $disabled; ?>/></td>
		</tr>
		</table>
	</td>
</tr>
<tr> 
	<td height="1" colspan="6" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('City');?>:</td>
	<td><input <?=$ro?> type="text" class="text" name="contact_city" value="<?php echo @$row->contact_city;?>" maxlength="30" size="25" <? echo $disabled; ?>/></td>
	<td align="right"><?php echo $AppUI->_('Fax');?>:</td>
	<td><input <?=$ro?> type="text" class="text" name="contact_fax" value="<?php echo @$row->contact_fax;?>" maxlength="30" size="25" <? echo $disabled; ?>/></td>
</tr>
<tr> 
	<td height="1" colspan="6" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Zip');?>:</td>
	<td><input <?=$ro?> type="text" class="text" name="contact_zip" value="<?php echo @$row->contact_zip;?>" maxlength="11" size="25" <? echo $disabled; ?>/></td>
	<td align="right"><?php echo $AppUI->_('Mobile Phone');?>:</td>
	<td><input <?=$ro?> type="text" class="text" name="contact_mobile" value="<?php echo @$row->contact_mobile;?>" maxlength="30" size="25" <? echo $disabled; ?>/></td>
</tr>
<tr> 
	<td height="1" colspan="6" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right">
	  <table cellspacing="0" cellpadding="0">
		<tr>
			<td><?php echo $AppUI->_('State');?>:</td>
		</tr>
		<tr> 
			<td height="5" ></td>
		</tr>
		<tr>
			<td align='right'><?php echo $AppUI->_('Country');?>:</td>
		</tr>
	   </table>
	</td>
	<td>
	  <table cellspacing="0" cellpadding="0">
		<tr>
			<td><input <?=$ro?> type="text" class="text" name="contact_state" value="<?php echo @$row->contact_state;?>" maxlength="30" size="25" <? echo $disabled; ?>/></td>
		</tr>
		<tr> 
			<td height="5" ></td>
		</tr>
		<tr>
			<td><input <?=$ro?> type="text" class="text" name="contact_country" value="<?php echo @$row->contact_country;?>" maxlength="32" size="25" <? echo $disabled; ?>/></td>
		</tr>
	   </table>
	</td>
	<td align="right" valign="top"><?php echo $AppUI->_('Contact Notes');?>:</td>
	<td><textarea <?=$ro?> class="textarea" name="contact_notes" rows="3" cols="25" <? echo $disabled; ?>><?php echo @$row->contact_notes;?></textarea></td>	
</tr>
<tr> 
	<td height="1" colspan="6" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Department');?>:</td>
    <td><input <?=$ro?> type="text" class="text" name="contact_department" value="<?php echo @$row->contact_department;?>" maxlength="32" size="25" <? echo $disabled; ?>/></td>
</tr>
<tr> 
	<td height="1" colspan="6" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Manager\'s name');?>:</td>
	<td><input <?=$ro?> type="text" class="text" name="contact_manager" value="<?php echo @$row->contact_manager;?>" maxlength="32" size="25" <? echo $disabled; ?>/></td>
</tr>
<tr> 
	<td height="1" colspan="6" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Assistant\'s name');?>:</td>
	<td><input <?=$ro?> type="text" class="text" name="contact_assistant" value="<?php echo @$row->contact_assistant;?>" maxlength="32" size="25" <? echo $disabled; ?>/></td>
</tr>
<tr> 
	<td height="1" colspan="6" class="rowFormHidden_bg"></td>
</tr>
<tr class="rowFormEmpty"> 
	<td colspan="5">&nbsp;</td>	
</tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_04.gif">
  <tr> 
	<td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
	<td><img src="images/common/cuadradito_gris.gif" width="9" height="9"><span class="boldtext"><?php echo $AppUI->_('Email');?></span></td>
	<td width="6"> <div align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></div></td>
  </tr>
  <tr bgcolor="#666666"> 
	<td height="1" colspan="3"></td>
  </tr>
</table>
<table cellspacing="1" cellpadding="1" border="0" width="100%" class="tableForm_bg">
<tr> 
	<td width="175">&nbsp;</td>
	<td>&nbsp;</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('SMTP Server');?>:</td>
	<!--<td><input type="text" class="text" name="company_smtp" value="<?php echo @$obj->company_smtp;?>" size=50 maxlength="255" onkeyup="changed(this);" /><a href="#x" onClick="checkSMTP()">[<?php echo $AppUI->_('check status');?>]</a>&nbsp;<span id="statusSmtp" name="statusSmtp"></span><input type="hidden" name="checkedsmtp" value="1" /></td>-->
	<td><input type="text" class="text" name="company_smtp" value="<?php echo @$obj->company_smtp;?>" size=50 maxlength="255" /><span id="statusSmtp" name="statusSmtp"></span><input type="hidden" name="checkedsmtp" value="1" /></td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Email Protocol');?>:</td>
	<td>
        <select class="text" name="company_mail_server_port" >
        <option value="110" <?if(@$obj->company_mail_server_port==110) echo "selected"?>>POP3</option>
	<option value="143" <?if(@$obj->company_mail_server_port==143) echo "selected"?>>IMAP</option>
        </select>
	</td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('POP3 Server');?>:</td>
	<!--<td><input type="text" class="text" name="company_pop3" value="<?php echo @$obj->company_pop3;?>" size=50 maxlength="255" onkeyup="changed(this);" /><a href="#x" onClick="checkPOP3()">[<?php echo $AppUI->_('check status');?>]</a>&nbsp;<span id="statusPop3" name="statusPop3"></span><input type="hidden" name="checkedpop3" value="1" /></td>-->
	<td><input type="text" class="text" name="company_pop3" value="<?php echo @$obj->company_pop3;?>" size=50 maxlength="255" /><span id="statusPop3" name="statusPop3"></span><input type="hidden" name="checkedpop3" value="1" /></td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('IMAP Server');?>:</td>
	<!--<td><input type="text" class="text" name="company_imap" value="<?php echo @$obj->company_imap;?>" size=50 maxlength="255" onkeyup="changed(this);" /><a href="#x" onClick="checkIMAP()">[<?php echo $AppUI->_('check status');?>]</a>&nbsp;<span id="statusImap" name="statusiImap"></span><input type="hidden" name="checkedimap" value="1" /></td>-->
	<td><input type="text" class="text" name="company_imap" value="<?php echo @$obj->company_imap;?>" size=50 maxlength="255" /><span id="statusImap" name="statusiImap"></span><input type="hidden" name="checkedimap" value="1" /></td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<tr>
	<td align="center">
<?php
	$custom_fields = dPgetSysVal("CompanyCustomFields");
	if ( count($custom_fields) > 0 ){
		//We have custom fields, parse them!
		//Custom fields are stored in the sysval table under CompanyCustomFields, the format is
		//key|serialized array of ("name", "type", "options", "selects")
		//Ej: 0|a:3:{s:4:"name";s:22:"Quote number";s:4:"type";s:4:"text";s:7:"options";s:24:"maxlength="12" size="10"";} 
		if ( $obj->company_custom != "" || !is_null($obj->company_custom))  {
			//Custom info previously saved, retrieve it
			$custom_field_previous_data = unserialize($obj->company_custom);
		}
		
		$output = '';
		foreach ( $custom_fields as $key => $array) {
			$output .= "<tr colspan='3' valign='top' id='custom_tr_$key' >";
			$field_options = unserialize($array);
			$output .= "<td align='right' nowrap='nowrap' >". ($field_options["type"] == "label" ? "<strong>". $field_options['name']. "</strong>" : $field_options['name']) . ":" ."</td>";
			switch ( $field_options["type"]){
				case "text":
					$output .= "<td><input type='text' name='custom_$key' class='text'" . $field_options["options"] . "value='" . ( isset($custom_field_previous_data[$key]) ? $custom_field_previous_data[$key] : "") . "' /></td>";
					break;
				case "select":
					$output .= "<td>". arraySelect(explode(",",$field_options["selects"]), "custom_$key", 'size="1" class="text" ' . $field_options["options"] ,( isset($custom_field_previous_data[$key]) ? $custom_field_previous_data[$key] : "")) . "</td>";
					break;
				case "textarea":
					$output .=  "<td><textarea name='custom_$key' class='text'" . $field_options["options"] . ">" . ( isset($custom_field_previous_data[$key]) ? $custom_field_previous_data[$key] : "") . "</textarea></td>";
					break;
			}
			$output .= "</tr>";
		}
		echo $output;
	}
?>
	</td>
</tr>
<tr>
	<!--<td  colspan="20"><?php echo $AppUI->_('companyCheckServerStatus');?></td>
</tr>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>-->
<?php if($output!=""){?>
<tr> 
	<td height="1" colspan="2" class="rowFormHidden_bg"></td>
</tr>
<?php } ?>
<tr>
	<td align="left"><input type="button" value="<?php echo $AppUI->_('back');?>" class="button" onClick="javascript:history.back(-1);" />&nbsp;</td>
	<td align="right"><input type="button" value="<?php echo $AppUI->_('submit');?>" name="submit1" class="button" onClick="submitIt()" /></td>
</tr>

</table>
  </td>
  </form>
 </tr>
</table>
