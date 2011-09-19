<?php /* $Id: contacts.php,v 1.4 2009-07-29 20:18:58 nnimis Exp $ */
$AppUI->savePlace();

$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$contact_company = dPgetParam( $_GET, "contact_company", "-1" );
$mod_id = 6; 

if ( $delegator_id != $AppUI->user_id )
{	
	require_once( $AppUI->getModuleClass( "admin" ) );
	$usr = new CUser();
	$usr->load( $AppUI->user_id );

	//Hay que chequear que este sea un delegador valido	
	if ( !$usr->isDelegator($delegator_id, $mod_id) && $AppUI->user_type != 1 )
	{
		$AppUI->setMsg( 'Delegator' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$delegador = new CUser();
	$delegador->load( $delegator_id );
	$permisos = $delegador->getDelegatePermission( $AppUI->user_id, $mod_id );
	$canAdd = $permisos == "AUTHOR" || $permisos == "EDITOR" || $AppUI->user_type == 1;
}
else
{
	if ( !$canRead )
	{
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$canAdd = $canEdit;
}

// To configure an aditional filter to use in the search string
$additional_filter = "";
// retrieve any state parameters
if (isset( $_GET['where'] )) 
{
	$AppUI->setState( 'ContIdxWhere', $_GET['where'] );
}
if (isset( $_GET["search_string"] ))
{
	$AppUI->setState ('ContIdxWhere', "%".$_GET['search_string']);
				// Added the first % in order to find instrings also
	$additional_filter = "OR contact_first_name like '%{$_GET['search_string']}%'
	                      OR contact_last_name  like '%{$_GET['search_string']}%'
						  OR contact_company    like '%{$_GET['search_string']}%'
						  OR contact_notes      like '%{$_GET['search_string']}%'
						  OR contact_email      like '%{$_GET['search_string']}%'";
}
$where = $AppUI->getState( 'ContIdxWhere' ) ? $AppUI->getState( 'ContIdxWhere' ) : '%';

$orderby = 'contact_order_by';

// Pull First Letters
$let = ":";
$sql = "
SELECT DISTINCT UPPER(SUBSTRING($orderby,1,1)) as L
FROM contacts
WHERE ( contact_public = 1 )
	OR ( contact_public = 0 AND contact_owner = $delegator_id )	
";
$arr = db_loadList( $sql );
foreach( $arr as $L ) {
    $let .= $L['L'];
}


// optional fields shown in the list (could be modified to allow breif and verbose, etc)
$showfields = array(
    "contact_company" => "contact_company",
	"contact_phone" => "contact_phone",
	"contact_email" => "contact_email"
);

require_once( $AppUI->getSystemClass ('dp' ) );

/**
* Contacts class
*/
class CContact extends CDpObject{
/** @var int */
	var $contact_id = NULL;
/** @var string */
	var $contact_first_name = NULL;
/** @var string */
	var $contact_last_name = NULL;
	var $contact_order_by = NULL;
	var $contact_title = NULL;
	var $contact_birthday = NULL;
	var $contact_company = NULL;
	var $contact_company_phone = NULL;
	var $contact_type = NULL;
	var $contact_email = NULL;
	var $contact_email2 = NULL;
	var $contact_phone = NULL;
	var $contact_phone2 = NULL;
	var $contact_business_phone = NULL;
	var $contact_business_phone2 = NULL;
	var $contact_fax = NULL;
	var $contact_mobile = NULL;
	var $contact_address1 = NULL;
	var $contact_address2 = NULL;
	var $contact_city = NULL;
	var $contact_state = NULL;
	var $contact_zip = NULL;
	var $contact_icq = NULL;
	var $contact_notes = NULL;
	var $contact_project = NULL;
	var $contact_country = NULL;
	var $contact_icon = NULL;
	var $contact_owner = NULL;
	var $contact_public = NULL;

	var $contact_website = NULL;
	var $contact_manager = NULL;
	var $contact_assistant = NULL;
	var $contact_department = NULL;


	function CContact() {
		$this->CDpObject( 'contacts', 'contact_id' );
	}

	function check() {
		if ($this->contact_id === NULL) {
			return 'contact id is NULL';
		}
	// ensure changes of state in checkboxes is captured
		$this->contact_public = intval( $this->contact_public );
		$this->contact_owner = intval( $this->contact_owner );
		$this->contact_creator = intval( $this->contact_creator );
		return NULL; // object is ok
	}
	
	function getCompanies($user_id){
		$sql=	"select distinct contact_company, contact_company
				from contacts 
				WHERE ( contact_public = 1 )
				OR ( contact_public = 0 AND contact_owner = $user_id )";
		
		return db_loadHashList($sql);
	}
	
	function getIdByFullname($first_name, $last_name){
		global $AppUI;
		$sql = "select contact_id from contacts 
				where contact_last_name = '$last_name'
				and contact_first_name = '$first_name'
				and contact_owner ='".$AppUI->user_id."'";
		return db_loadResult($sql);
		
	}
	
}


$companies = CContact::getCompanies($delegator_id);

if (isset($companies[""])){
	unset($companies[""]);
	$companies = arrayMerge(array(""=>"No company"), $companies);
}

sort($companies);
$companies = arrayMerge(array("-1"=>"All"), $companies);

// assemble the sql statement
$sql = "SELECT * ";

if (($contact_company!= "None")&&(!(isset( $_GET["search_string"] ))))
{
$sql.= "
FROM contacts
WHERE ( contact_order_by LIKE '$where%' $additional_filter )
	AND ( ( contact_public = 1 ) 
	OR ( contact_public = 0 AND contact_owner=$delegator_id ) )
	AND (contact_company = '".$companies[$contact_company]."' and '$contact_company' <> -1
		OR '$contact_company' =-1)
ORDER BY $orderby
";
}
else
{
$sql .= "
FROM contacts
WHERE ( contact_order_by LIKE '$where%' $additional_filter )
	AND ( ( contact_public = 1 ) 
	OR ( contact_public = 0 AND contact_owner=$delegator_id ) )
	AND (contact_company = 'All' and '-1' <> -1 OR '-1' =-1)
ORDER BY $orderby
";
}

$carr = array();
$carrWidth = 4;
$carrHeight = 4;


$dp = new DataPager($sql, "contact");

$dp->rows = 20;
$dp->showPageLinks = true;
$carr = $dp->getResults();
$rn = $dp->num_result;
$pager_links = $dp->RenderNav();

$t = floor( $rn / $carrWidth );
$r = ($rn % $carrWidth);


$default_search_string = substr($AppUI->getState( 'ContIdxWhere' ), 1, strlen($AppUI->getState( 'ContIdxWhere' )));

$form = "<form action='./index.php' method='get'><td>".$AppUI->_('Search contacts for')."</td>
   		   <td><input type='text' class='ContactSearchInput' name='search_string' value='$default_search_string' />
		   <input type='hidden' name='m' value='companies' />
		   <input type='hidden' name='delegator_id' value='".$delegator_id."' />
		   <input type='hidden' name='suppressLogo' value='1' />
		   <input type='hidden' name='dialog' value='1' />
		   <input type='hidden' name='a' value='contacts' /></td>
		   <td><input type='submit' class='button' value='".$AppUI->_('Search')."' /></td>
		   <td><a href='./index.php?m=companies&a=contacts&suppressLogo=1&dialog=1&search_string='>".$AppUI->_('Reset search')."</a>
		 </td></form>";


$form .= "<form action='./index.php' method='get'><td> - ".$AppUI->_('Company')."</td>
		   <td><input type='hidden' name='m' value='companies' />
		   <input type='hidden' name='delegator_id' value='".$delegator_id."' />
		   <input type='hidden' name='suppressLogo' value='1' />
		   <input type='hidden' name='a' value='contacts' />
		   <input type='hidden' name='dialog' value='".$dialog."' />".
		   arraySelect( $companies, 'contact_company', 'size="1" class="text" onchange="javascript: this.form.submit();"', $contact_company, true )."</td></form>";

//Agrego la columna en el formulario para que no genere espaciado en la tabla
$tblForm = "\n<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";
$tblForm .= "\n<tr>";
$tblForm .= "$form</tr></table>";


// setup the title block
$titleBlock = new CTitleBlock( 'Contacts', 'handshake.gif', $m, "$m.$a" );

$titleBlock->addCell( $tblForm );


if ( $canAdd ) 
{
	$titleBlock->addCell(
		'<input type="hidden" name="b" value="company" /><input type="submit" class="button" value="'.$AppUI->_('new contact').'">', '',
		'<form action="?m=contacts&suppressLogo=1&delegator_id='.$delegator_id.'&a=addedit&dialog='.$dialog.'" method="post">', '</form>'
	);	
}

$titleBlock->show();
?>
<script language="javascript"><!--

var contact_list = new Array();

function doActions(){
	var frm = document.frmContacts;
	var selectAction = document.getElementById('selectaction').value;
	
	if(selectAction != ""){
	
		strLista = "";
		for (i = 0; i < frm.length; i++)
		{
			if(frm.elements[i].checked){
				if(strLista != "") strLista += ",";
				strLista += frm.elements[i].value;
			}
		}
		
		frm.ContactList.value = strLista;
		
		if(selectAction == "del"){
			strMsg = "<?php echo $AppUI->_("Are you sure you want to delete the selected contacts?")?>";
		}
		if(selectAction == "public"){
			strMsg = "<?php echo $AppUI->_("Are you sure you want to set as public the selected contacts?")?>";	
		}
		if(selectAction == "private"){
			strMsg = "<?php echo $AppUI->_("Are you sure you want to set as private the selected contacts?")?>";	
		}
		
		if(confirm1(strMsg)){
			frm.ContactListAction.value = selectAction;
			frm.submit();
		}
	}
}



function setClose(){
       var frm = document.frmContacts;

	   if(frm.contact_id.value){
            contactid = frm.contact_id.value;
		    contactname = frm.first_name.value;
			last_name = frm.last_name.value;
	        email = frm.email.value;
			email2 = frm.email2.value;
	        title = frm.title.value;
	        phone = frm.phone.value;
	        type = frm.type.value;
	        icq = frm.icq.value;
	        address1 = frm.address1.value;
	        phone2 = frm.phone2.value;
	        business_phone = frm.business_phone.value;
	        city = frm.city.value;
	        fax = frm.fax.value;
	        zip = frm.zip.value;
	        mobile = frm.mobile.value;
	        state = frm.state.value;
	        country = frm.country.value;
	        notes = frm.notes.value;
	        department = frm.department.value;
	        manager = frm.manager.value;
	        assistant = frm.assistant.value;
			company = frm.contact_company.value;
			c_public = frm.contact_public.value;
	   }

	   var i; 
       for (i=0;i<frm.contact_id.length;i++){ 
        if (frm.contact_id[i].checked) 
		   {
		    contactid = frm.contact_id[i].value;
		    contactname = frm.first_name[i].value;
			last_name = frm.last_name[i].value;
	        email = frm.email[i].value;
			email2 = frm.email2[i].value;
	        title = frm.title[i].value;
	        phone = frm.phone[i].value;
	        type = frm.type[i].value;
	        icq = frm.icq[i].value;
	        address1 = frm.address1[i].value;
	        phone2 = frm.phone2[i].value;
	        business_phone = frm.business_phone[i].value;
	        city = frm.city[i].value;
	        fax = frm.fax[i].value;
	        zip = frm.zip[i].value;
	        mobile = frm.mobile[i].value;
	        state = frm.state[i].value;
	        country = frm.country[i].value;
	        notes = frm.notes[i].value;
	        department = frm.department[i].value;
	        manager = frm.manager[i].value;
	        assistant = frm.assistant[i].value;
			company = frm.contact_company[i].value;
			c_public = frm.contact_public[i].value;
		   break;
		   }
	   }

	   var key = contactid;
	   var fname = contactname;
	   
	    if(c_public != 1){
			if(confirm("<?=$AppUI->_("confirmContactPublicUpdate1")?>" + "\n" + "<?=$AppUI->_("confirmContactPublicUpdate2")?>" + "\n" + "<?=$AppUI->_("confirmContactPublicUpdate3")?>" + "\n\n" + "<?=$AppUI->_("confirmContactPublicUpdate4")?>")){
				c_public = 1;
				xajax_updateContactPublic(contactid, 1);
			}
		}
		
		if(c_public == 1){
		   window.opener.setContact(key,fname, last_name, email, email2, title, phone, type, icq, address1, phone2, business_phone, city, fax, zip, mobile, state, country, notes, department, manager, assistant,company, c_public);
		   window.close();
		}
	}
// -->

</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0" >
  <tr> 
	<td valign="bottom""><table border="0" cellpadding="0" cellspacing="0" background="images/common/back_botones-01.gif">
		<tr> 
		  <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
		  <td><img src="images/common/cuadradito_naranja.gif" width="9" height="9"><a href="./index.php?m=companies&a=contacts&suppressLogo=1&where=0&delegator_id=<?php echo $delegator_id?>&dialog=<?php echo $dialog?>"><?php echo $AppUI->_('All'); ?></a> 
			<?php
				for ($c=65; $c < 91; $c++) {
					$cu = chr( $c );
					if(strpos($let, "$cu") > 0){
			?>
			<a href="?m=companies&a=contacts&suppressLogo=1&delegator_id=<?php echo $delegator_id;?>&where=<?php echo $cu;?>&dialog=<?php echo $dialog;?>&contact_company=<?php echo $contact_company;?>"><?php echo $cu;?></a>&nbsp;
			<?php 	}else{ ?>
			<font color="#999999"><?php echo $cu; ?></font>
			<?php 	}
				} 
				?>
			</td>
		  <td width="6"> <div align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></div></td>
		</tr>
	  </table></td>
	  <td>
	  	
	  </td>
  </tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="1">
  <form name="frmContacts" action="?m=companies&a=contacts&suppressLogo=1&delegator_id=<?php echo $delegator_id;?>&dialog=<?php echo $dialog;?>" method="POST">
  <input type="hidden" name="ContactList" value="">
  <input type="hidden" name="ContactListAction" value="">
  <tr bgcolor="#333333" class="boldblanco"> 
	<td width="15%">Seleccionar&nbsp;</td>
  	<td width="20%"> 
	  <div align="left"><?php echo $AppUI->_('Last Name') ?></div></td>
	<td width="20%"> 
	  <div align="left"><?php echo $AppUI->_('Name') ?></div></td>
	<td> 
	  <div align="left"><?php echo $AppUI->_('Company') ?></div></td>
	<td width="20%"> 
	  <div align="right"><?php echo $AppUI->_('Telephone') ?></div></td>
  </tr>
  <tr> 
	<td height="1" colspan="4" bgcolor="#E9E9E9"></td>
  </tr>
  <?php
  	$js_string = "";
		if (sizeof($carr)==0){
			echo '<tr>';
			echo "<td colspan=\"97\">".$AppUI->_('No data available')."</td></tr>";		
		}

		

  	for($f=0;$f<sizeof($carr);$f++){
		$canEdit == ( $carr[$f]["contact_owner"] == $delegator_id && $AppUI->user_id == $delegator_id );
		$canEdit == $canEdit || ( $carr[$f]["contact_owner"] == $delegator_id && $permisos == "EDITOR" );
		$canEdit == $canEdit || ( $carr[$f]["contact_owner"] == $delegator_id && $permisos == "AUTOR" && $carr[$f]["contact_creator"] == $AppUI->user_id );
		
		$color = $carr[$f]["contact_owner"] == $delegator_id ? "" : "red";
		
		$href = "./index.php?m=contacts&dialog=$dialog&delegator_id=$delegator_id&a=addedit&contact_id=".$carr[$f]["contact_id"];     
		$query2 = "SELECT country_name FROM location_countries WHERE country_id = '".$carr[$f][contact_country_id]."' ";
		$sql2 = db_exec($query2);
		$country = mysql_fetch_array($sql2);
		
		$query3 = "SELECT state_name FROM location_states WHERE state_id = '".$carr[$f][contact_state_id]."' ";
		$sql3 = db_exec($query3);
		$state = mysql_fetch_array($sql3);
		
		$s="\n<tr>";
		$s.="\n\t<td nowrap=\"nowrap\">
		    <input type=\"hidden\" name=\"first_name\" value=\"{$carr[$f]["contact_first_name"]}\">
			<input type=\"hidden\" name=\"last_name\" value=\"{$carr[$f]["contact_last_name"]}\"> 
	        <input type=\"hidden\" name=\"email\" value=\"{$carr[$f]["contact_email"]}\">
			<input type=\"hidden\" name=\"email2\" value=\"{$carr[$f]["contact_email2"]}\">
	        <input type=\"hidden\" name=\"title\" value=\"{$carr[$f]["contact_title"]}\">
	        <input type=\"hidden\" name=\"phone\" value=\"{$carr[$f]["contact_phone"]}\">
	        <input type=\"hidden\" name=\"type\" value=\"{$carr[$f]["contact_type"]}\">
	        <input type=\"hidden\" name=\"icq\" value=\"{$carr[$f]["contact_icq"]}\">
	        <input type=\"hidden\" name=\"address1\" value=\"{$carr[$f]["contact_address1"]}\">
	        <input type=\"hidden\" name=\"phone2\" value=\"{$carr[$f]["contact_phone2"]}\">
	        <input type=\"hidden\" name=\"business_phone\" value=\"{$carr[$f]["contact_business_phone"]}\">
	        <input type=\"hidden\" name=\"city\" value=\"{$carr[$f]["contact_city"]}\">
	        <input type=\"hidden\" name=\"fax\" value=\"{$carr[$f]["contact_fax"]}\">
	        <input type=\"hidden\" name=\"zip\" value=\"{$carr[$f]["contact_zip"]}\">
	        <input type=\"hidden\" name=\"mobile\" value=\"{$carr[$f]["contact_mobile"]}\">
	        <input type=\"hidden\" name=\"state\" value=\"{$state["state_name"]}\">
	        <input type=\"hidden\" name=\"country\" value=\"{$country["country_name"]}\">
	        <input type=\"hidden\" name=\"notes\" value=\"{$carr[$f]["contact_notes"]}\">
	        <input type=\"hidden\" name=\"department\" value=\"{$carr[$f]["contact_department"]}\">
	        <input type=\"hidden\" name=\"manager\" value=\"{$carr[$f]["contact_manager"]}\">
	        <input type=\"hidden\" name=\"assistant\" value=\"{$carr[$f]["contact_assistant"]}\">
		    <input type=\"hidden\" name=\"contact_company\" value=\"{$carr[$f]["contact_company"]}\">
		    <input type=\"hidden\" name=\"contact_public\" value=\"{$carr[$f]["contact_public"]}\">
			<input type=\"radio\" name=\"contact_id\" value=\"{$carr[$f]["contact_id"]}\">&nbsp;
			&nbsp;</td>";
		$s.="\n\t<td><strong>".$carr[$f]["contact_last_name"]."</strong></td>";
		$s.="\n\t<td>".$carr[$f]["contact_first_name"]."</td>";
		$s.="\n\t<td><div align=\"left\">".$carr[$f]["contact_company"]."</div></td>";
		$s.="\n\t<td><div align=\"right\">".$carr[$f]["contact_phone"]."</div></td>";
		$s.="\n</tr>"; 
		$s.="\n<tr>"; 
		$s.="\n\t<td height=\"1\" colspan=\"5\" bgcolor=\"#E9E9E9\"></td>";
	  	$s.="\n</tr>";
	  	$js_string .= "contact_list[contact_list.length] = 'contact{$carr[$f]["contact_id"]}';\n";
		echo $s;
	}
  ?>
  </form>
</table>
<? 
echo "
<table border='0' width='100%' cellspacing='0' cellpadding='1'>
<tr bgcolor=#E9E9E9>
	<td align='center'>$pager_links</td>
</tr>
<tr>
		<td height=1 colspan=5 bgcolor=#E9E9E9></td>
</tr>
</table>"; 
?>

<br>


<table cellspacing="0" cellpadding="3" border="0" align="center" width="100%">
<tr>
	<td>
		<input align="left" type="button" class="button" value="<?php echo $AppUI->_( 'cancel' );?>" onclick="window.close()" />
	</td>
	<td align="right">
		<input type="button" class="button" value="<?php echo $AppUI->_( 'Select' );?>" onclick="setClose()" />
	</td>
</tr>
</table>

