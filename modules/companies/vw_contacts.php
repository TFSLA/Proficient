<?php
global $xajax;
global $AppUI;

$xajax->printJavascript('./includes/xajax/');

$prj_id = $_GET['project_id'];
$cmp_id = $_GET['company_id'];

$sql = "SELECT project_company FROM projects WHERE project_id = $prj_id";
$project_data = mysql_fetch_array(mysql_query($sql));

require_once("./modules/contacts/contacts.class.php");
$Ccontact = new CContact();
$contacts = $Ccontact->getAllContacts();
$related_contacts = $Ccontact->getContactsByCompany($project_data['project_company']);

$AppUI->contacts = $contacts;
$AppUI->related_contacts = $related_contacts;


?>
<form id="editFrm" name="editFrm" method="POST" action="index.php?m=projects&a=do_companycontacts_aed">
<input type="hidden" name="project_id" value="<?php echo $_GET['project_id']; ?>">
<table cellspacing="2" cellpadding="0" border="0" width="100%" class="tableForm_bg">
  <tr>
	<td colspan="4">
		<table cellspacing="0" align="center" cellpadding="2" border="0">
			<tr>
				<td><br>
				</td>
			</tr>
			<tr>
				<td><?php echo $AppUI->_( 'All Contacts' );?></td>
				<td><?php echo $AppUI->_( 'Company Contacts' );?></td>
			</tr>
			<tr>
				<td>
					<?php echo arraySelect( $contacts, 'all_contacts', 'style="width:250px" size="10" class="text" multiple="multiple"', null, false, false );?>
				</td>
				<td>
					<?php echo arraySelect( $related_contacts, 'company_contacts', 'style="width:250px" size="10" class="text" multiple="multiple"', null, false, false ); ?>
				</td>
			</tr>
			<tr>
				<td align="right"><input type="button" class="button"  value="&gt;" onClick="addCompanyContact()" /></td>
				<td align="left"><input type="button" class="button"  value="&lt;" onClick="removeCompanyContact()" /></td>
			</tr>
		</table>
	</td>
  </tr>
  <tr>
  	<td align="right">
  		<input type="submit" class="button" value="<?php echo $AppUI->_('Save'); ?>">
  	</td>
  	<td width="1"></td>
  </tr>
</table>
</form>

<script language = "javascript" >
function addCompanyContact(){
	var form = document.editFrm;
	var fl = form.all_contacts.length -1;
	
	for(i=0;i<form.all_contacts.options.length;i++)
    {
        if(form.all_contacts.options[i].selected)
           xajax_addContact('all_contacts','company_contacts', form.all_contacts.options[i].value);
    }
}

function removeCompanyContact(){
	var form = document.editFrm;
	var fl = form.company_contacts.length -1;
	
	for(i=0;i<form.company_contacts.options.length;i++)
    {
        if(form.company_contacts.options[i].selected)
           xajax_delContact('company_contacts','all_contacts', form.company_contacts.options[i].value);
    }
}

</script>