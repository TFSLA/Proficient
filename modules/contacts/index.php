<?php /* $Id: index.php,v 1.22 2009-08-03 14:50:41 nnimis Exp $ */
$AppUI->savePlace();

global $canEditCompany, $obj; //Variables del módulo de Empresas para establecer como contacto principal

include ('./functions/delegates_func.php');
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$module = dPgetParam( $_GET, "m", "contacts" );
$project_id = dPgetParam( $_GET, "project_id", 0 );
$company_id = dPgetParam( $_GET, "company_id", 0 );
$origen = "m=contacts";

if($project_id > 0){
	$origen = "m=projects&a=view&project_id=$project_id";
}
if($company_id > 0){
	$origen = "m=companies&a=view&company_id=$company_id";
}

if($_REQUEST["privados"]!=""){
  $privados = $_REQUEST["privados"];
}

$mod_id = 6;

if ( $delegator_id != $AppUI->user_id && $module == "contacts")
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
	do_log($delegator_id, $mod_id, $AppUI, 1);
}
else
{
	if ( !$canRead && $module == "contacts")
	{
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$canAdd = ($AppUI->user_type == 1 || !getDenyEdit( "contacts" ));
}

// To configure an aditional filter to use in the search string
$additional_filter = "";
// retrieve any state parameters
if (isset( $_GET['contact_company'] ))
{
	$AppUI->setState( 'ContIdxCompany', $_GET['contact_company'] );
}
$contact_company = $AppUI->getState( 'ContIdxCompany' ) ? $AppUI->getState( 'ContIdxCompany' ) : '-1';

if (isset( $_GET['where'] ))
{
	$AppUI->setState( 'ContIdxWhere', $_GET['where'] );
}

if (isset( $_GET["search_string"] ))
{
	$AppUI->setState ('ContIdxWhere', "%".$_GET['search_string']);
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
if($module == "contacts"){
	$sql = "
	SELECT DISTINCT UPPER(SUBSTRING($orderby,1,1)) as L
	FROM contacts
	WHERE ( contact_public = 1 )
		OR ( contact_public = 0 AND contact_owner = $delegator_id )	
	";
}elseif ($module == "projects"){
	$project_id = $_GET["project_id"];
	$sql = "
	SELECT DISTINCT UPPER(SUBSTRING(c.$orderby,1,1)) as L
	FROM contacts AS c INNER JOIN contacts_relations AS cr ON c.contact_id = cr.contact_id
	WHERE ( c.contact_public = 1 AND cr.relation_type = 'projects' AND cr.relation_type_id = $project_id)
	OR ( c.contact_public = 0 AND (c.contact_owner = $delegator_id OR c.contact_creator = $delegator_id) AND cr.relation_type = 'projects'
	AND cr.relation_type_id = $project_id)
	";
}elseif ($module == "companies"){
	$company_id = $_GET["company_id"];
	$sql = "SELECT company_name FROM companies WHERE company_id = $company_id";
	$data = mysql_fetch_array(mysql_query($sql));
	$company_name = $data["company_name"];
	
	$sql = "
	(SELECT DISTINCT UPPER(SUBSTRING(c.$orderby,1,1)) as L
	FROM contacts AS c INNER JOIN contacts_relations AS cr ON c.contact_id = cr.contact_id
	WHERE ( c.contact_public = 1 AND cr.relation_type = 'companies' AND cr.relation_type_id = $company_id)
		OR ( c.contact_public = 0 AND (c.contact_owner = $delegator_id OR c.contact_creator = $delegator_id) AND cr.relation_type = 'companies'
		AND cr.relation_type_id = $company_id))
	UNION 
	(SELECT UPPER(SUBSTRING(c.$orderby,1,1)) as L FROM contacts AS c
	WHERE c.contact_company = '$company_name')
	UNION
	(SELECT UPPER(SUBSTRING(c.$orderby,1,1)) as L FROM contacts AS c
	INNER JOIN companies ON companies.contact_id = c.contact_id
	WHERE companies.company_id = $_GET[company_id])
	";
}

$arr = db_loadList( $sql );
foreach( $arr as $L ) {
    $let .= $L['L'];
}

// optional fields shown in the list (could be modified to allow breif and verbose, etc)
$showfields = array(
	"contact_company" => "contact_company",
	"contact_business_phone" => "contact_business_phone",
	"contact_email" => "contact_email"
);

$companies = CContact::getCompanies($delegator_id);

if (isset($companies[""])){
	unset($companies[""]);
	$companies = arrayMerge(array(""=>"No company"), $companies);
}

natcasesort($companies);
$companies = arrayMerge(array("-1"=>$AppUI->_("All")), $companies);

// assemble the sql statement
$sql = "SELECT contact_id, contact_order_by, ";
foreach ($showfields as $val) {
	$sql.="$val,";
}

if ($privados=="public"){

	$filter =  "AND  contact_public='1' ";
}

if ($privados=="private")
{
	$filter = "AND ((contact_owner = '".$delegator_id."') AND (contact_public = '0'))";
}

if ($privados ==""){
	$filter = "AND ((contact_owner = '".$delegator_id."')OR(contact_public = '1'))";
}

$downImage = "<img src='./images/arrow-down.gif' border='0' alt='".$AppUI->_("Ascending")."'>";
$upImage = "<img src='./images/arrow-up.gif' border='0' alt='".$AppUI->_("Descending")."'>";
$orderImage = isset($_GET["revert"]) ? $upImage : $downImage;
$revertOrder = isset($_GET["revert"]) ? "" : "&revert=1";
if(isset($_GET["orderby"]))
	$orderby = $_GET["orderby"];
else
	$orderby = "contact_last_name, contact_first_name";

if(isset($_GET["revert"])) $orderby .= " DESC";
	
if (($contact_company != "None")&&(!(isset( $_GET["search_string"] )))&&($module=="contacts")){
	$sql .= "contact_first_name, contact_last_name, contact_business_phone, contact_owner, contact_public
	FROM contacts
	WHERE ( contact_order_by LIKE '$where%' $additional_filter )
		".$filter."
		AND (contact_company = '".$companies[$contact_company]."' and '$contact_company' <> -1
			OR '$contact_company' =-1)
	ORDER BY $orderby
	";
}
elseif($module=="projects"){
	$sql = "
	SELECT c.contact_id, c.contact_order_by, c.contact_first_name, c.contact_last_name, c.contact_business_phone, c.contact_owner, c.contact_public, c.contact_company, c.contact_phone, c.contact_email, c.contact_mobile
	FROM contacts c INNER JOIN contacts_relations AS cr ON cr.contact_id = c.contact_id
	WHERE (cr.relation_type_id = $project_id AND cr.relation_type = 'projects' AND c.contact_order_by LIKE '$where%')
	AND (contact_public = 1 OR (contact_public = 0 AND (contact_owner = $delegator_id OR contact_creator = $delegator_id)))
	ORDER BY $orderby";
}elseif($module=="companies"){
	$mainContactId = db_loadResult("SELECT contact_id FROM companies WHERE company_id = $company_id");
	if(!empty($mainContactId)) $mainContactFilter = "AND c.contact_id <> $mainContactId";
	else $mainContactFilter = "";
	$sql = "
	(SELECT DISTINCT c.contact_id, c.contact_order_by, c.contact_first_name, c.contact_last_name, c.contact_business_phone, 
	c.contact_owner, c.contact_public, c.contact_company, c.contact_phone, c.contact_email, c.contact_mobile, 0 AS main
	FROM contacts c INNER JOIN contacts_relations AS cr ON cr.contact_id = c.contact_id
	WHERE (cr.relation_type_id = $company_id AND cr.relation_type = 'companies' AND c.contact_order_by LIKE '$where%')
	AND ($AppUI->user_type = 1 OR contact_public = 1 OR (contact_public = 0 AND (contact_owner = $delegator_id OR contact_creator = $delegator_id)))
	$mainContactFilter
	)
	UNION
	(SELECT c.contact_id, c.contact_order_by, c.contact_first_name, c.contact_last_name, c.contact_business_phone, 
	c.contact_owner, c.contact_public, c.contact_company, c.contact_phone, c.contact_email, c.contact_mobile, 0 AS main
	FROM contacts c	WHERE (c.contact_company = '$company_name' AND c.contact_order_by LIKE '$where%')
	AND (contact_public = 1 OR (contact_public = 0 AND (contact_owner = $delegator_id OR contact_creator = $delegator_id)))
	$mainContactFilter
	)
	UNION
	(SELECT c.contact_id, c.contact_order_by, c.contact_first_name, c.contact_last_name, c.contact_business_phone, 
	c.contact_owner, c.contact_public, c.contact_company, c.contact_phone, c.contact_email, c.contact_mobile, 1 AS main
	FROM contacts AS c
	INNER JOIN companies ON companies.contact_id = c.contact_id
	WHERE companies.company_id = $company_id AND c.contact_order_by LIKE '$where%'
	AND (c.contact_public = 1 OR (c.contact_public = 0 AND (c.contact_owner = $delegator_id OR c.contact_creator = $delegator_id))))
	ORDER BY $orderby";
}else{
	$sql .= "contact_first_name, contact_last_name, contact_business_phone, contact_owner, contact_public, contact_phone, contact_email, contact_mobile
	FROM contacts
	WHERE ( contact_order_by LIKE '$where%' $additional_filter )
		".$filter."
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

/**
* Contact search form data event as SELECT
*/
 // Let's remove the first '%' that we previously added to ContIdxWhere
$default_search_string = substr($AppUI->getState( 'ContIdxWhere' ), 1, strlen($AppUI->getState( 'ContIdxWhere' )));

$form = "<form action='./index.php' method='get'>
   		   <td><input type='text' class='ContactSearchInput' name='search_string' value='$default_search_string' />
		   <input type='hidden' name='m' value='contacts' />
		   <input type='hidden' name='delegator_id' value='".$delegator_id."' />
		   <input type='hidden' name='dialog' value='".$dialog."' /></td>
		   <td><input type='submit' class='button' value='".$AppUI->_('search')."' /></td>
   		</form>";

$companies_list = array();

while(list($key, $value) = each($companies)){
	if ($key=="0")
	{
		$key ="None";
		$value = "Sin especificar";
	}
	$companies_list[$key] = strlen($value) > 40 ? 
		substr($value, 0, 37)."..." : $value;
}

$form .= "<form action='./index.php' method='get'><td>&nbsp;&nbsp;".$AppUI->_('Company')."</td>
			<td><input type='hidden' name='m' value='contacts' />
		   <input type='hidden' name='delegator_id' value='".$delegator_id."' />
		   <input type='hidden' name='dialog' value='".$dialog."' />".
		   arraySelect( $companies_list, 'contact_company', 'size="1" class="text" onchange="javascript: this.form.submit();"', $contact_company, false,false,'250px' )."</td></form>";

$tblForm = "\n<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";
$tblForm .= "\n<tr>";
$tblForm .= "$form</tr></table>";

$titleBlock = new CTitleBlock( 'Contacts', 'contacts.gif', $m, "colaboration.index" );
$titleBlock->addCell( $tblForm );

if ( ( $canAdd && ($delegator_id == $AppUI->user_id || empty($delegator_id) )) || ($permisos == "AUTHOR" && $delegator_id != $AppUI->user_id && $module == "contacts"))
{
	$titleBlock->addCell(
		'<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new contact').'">', '',
		'<form action="?m=contacts&delegator_id='.$delegator_id.'&a=addedit&dialog='.$dialog.'" method="post">
		<input type="hidden" name="hideTabs" value=1>
		<input type="hidden" name="origen" value="m=contacts">', '</form>'
	);	
}

if($canAdd){
	$titleBlock->addCrumb("?m=contacts&dialog=$dialog&delegator_id=$delegator_id&a=import_export", $AppUI->_("import / export"));
}

$titleBlock->addCrumb("?m=contacts&dialog=$dialog&delegator_id=$delegator_id&privados&search_string", $AppUI->_("show all"));
$titleBlock->addCrumb("?m=contacts&dialog=$dialog&delegator_id=$delegator_id&privados=public", $AppUI->_("show only public"));
$titleBlock->addCrumb("?m=contacts&dialog=$dialog&delegator_id=$delegator_id&privados=private", $AppUI->_("show only private"));

if($module=="contacts")
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
		
		if(strLista!=""){
			if(selectAction == "companies" || selectAction == "projects" || selectAction == "leads"){
				frm.ContactListAction.value = selectAction;
				frm.action = "/index.php?m=contacts&a=multiple_relations&delegator_id=<?php echo $delegator_id; ?>";
				var hiddenDosql = frm.dosql;
				hiddenDosql.parentNode.removeChild(hiddenDosql);
				frm.submit();
			}else{
				if(selectAction == "del"){
					strMsg = "<?php echo $AppUI->_("Are you sure you want to delete the selected contacts?")?>";
				}
				if(selectAction == "public"){
					strMsg = "<?php echo $AppUI->_("Are you sure you want to set as public the selected contacts?")?>";	
				}
				if(selectAction == "private"){
					strMsg = "<?php echo $AppUI->_("Are you sure you want to set as private the selected contacts?")?>";	
				}
			
				if(confirm(strMsg)){
					frm.ContactListAction.value = selectAction;
					frm.submit();
				}
			}
		}else{
			alert("<?php echo $AppUI->_("No contacts selected")?>");
			document.getElementById('selectaction').value = "";
		}
	}
}

function switch_selection( val ){
	var f = document.frmContacts;
	var c = 0;
	for (var i=0; i < contact_list.length; i++) 
	{
		eval("var c = f." + contact_list[i] + ";");
	  	if (c != 0){
	  		if ( val == "select" )
	  			c.checked = true;
	  		else if ( val == "deselect" )
	  			c.checked = false;
	  		else <?php /* if ( val == "invert" ) */ ?>
	  			c.checked = ! c.checked;	
	  	}
		c = 0;	
	}
}

function changeMainContact( id , objCheck, companyName, isPublic){
	if(isPublic != 1){
		if(confirm("<?=$AppUI->_("confirmContactPublicUpdate1")?>" + "\n" + "<?=$AppUI->_("confirmContactPublicUpdate2")?>" + "\n" + "<?=$AppUI->_("confirmContactPublicUpdate3")?>" + "\n\n" + "<?=$AppUI->_("confirmContactPublicUpdate4")?>")){
			isPublic = 1;
			xajax_updateContactPublic(id, 1, true);
		}
	}
	
	if(isPublic == 1) {
		for (i=0;i<document.frmContacts.elements.length;i++)
		{
			if(document.frmContacts.elements[i].type == 'checkbox'  && document.frmContacts.elements[i].name != objCheck.name)
				document.frmContacts.elements[i].checked = false;
		}
		
		xajax_changeMainContact(id,'<?=$obj->company_id?>');
		
		if(companyName != '<?=$obj->company_name?>')
		{
			if(confirm("<?=$AppUI->_("confirmContactCompanyUpdate1")?>" + "\n" + "<?=$AppUI->_("confirmContactCompanyUpdate2")?>" + "\n\n" + "<?=$AppUI->_("confirmContactCompanyUpdate3")?>"))
			{
				xajax_updateContactCompany(id, "<?=$obj->company_name?>");
			}
		}
	} else { objCheck.checked = false; }
}

function editContact(id){
	url = "./index.php?m=contacts&dialog=<?=$dialog?>&delegator_id=<?=$delegator_id?>&a=addedit&tab=0&contact_id=" + id;
	if(document.getElementById("chk_" + id).checked == true) url += "&main=1";
	window.location = url;
}

// -->
</script>
<?php

if($module == "contacts"){
	$url = "?m=contacts&where=0&delegator_id=$delegator_id";
	$style = '';
}elseif($module == "projects"){
	$url = "?m=projects&a=view&project_id=$project_id&tab=".$AppUI->state['ProjVwTab'];
	$filed_url = "&project_id=$project_id";
	$style = 'class="std" style="border-top-width:1px;border-bottom-width:0px;border-left-width:0px;border-right-width:0px;border-style:solid;border-color:black;"';
}elseif($module == "companies"){
	$url = "?m=companies&a=view&company_id=$company_id&tab=".$AppUI->state['CompVwTab'];
	$filed_url = "&company_id=$company_id";
	$style = 'class="std" style="border-top-width:1px;border-bottom-width:0px;border-left-width:0px;border-right-width:0px;border-style:solid;border-color:black;"';
}
?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" <?=$style?>>
  <tr>
	<td valign="bottom""><table border="0" cellpadding="0" cellspacing="0" background="images/common/back_botones-01.gif">
		<tr> 
		  <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
		  <td><img src="images/common/cuadradito_naranja.gif" width="9" height="9"><a href="./index.php<?php echo $url; ?>&where=0&delegator_id=<?php echo $delegator_id?>&dialog=<?php echo $dialog?>"><?php echo $AppUI->_('All'); ?></a>
			<?php
				for ($c=65; $c < 91; $c++) {
					$cu = chr( $c );
					if(strpos($let, "$cu") > 0){
			?>
			<a href="<?php echo $url; ?>&delegator_id=<?php echo $delegator_id;?>&where=<?php echo $cu;?>&dialog=<?php echo $dialog;?>&contact_company=<?php echo $contact_company;?>"><?php echo $cu;?></a>&nbsp;
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
	  	<table width="100%" border="0" cellpadding="0" cellspacing="0" >
	  		<tr>
	  			<td>
	  				<table width="100%" border="0" cellpadding="0" cellspacing="0" >
						<form name="frm1" action="?m=contacts&delegator_id=<?php echo $delegator_id;?>&dialog=<?php echo $dialog;?>" method="POST">
						<input type="hidden" name="dosql" value="contact_actions">
			  		<tr>
	  					<td>
	  					<?php if($module=="contacts"){ ?>
				  				<select id="selectaction" name="newactions" class="text" onchange="doActions();">
				  					<option value="">--- <?php echo $AppUI->_("Actions")?> ---</option>
				  					<option value="del"><?php echo $AppUI->_("Delete selected")?></option>
				  					<option value="public"><?php echo $AppUI->_("Set as public")?></option>
				  					<option value="private"><?php echo $AppUI->_("Set as private")?></option>
				  					<option value="companies"><?php echo $AppUI->_("Link to companies")?></option>
				  					<option value="projects"><?php echo $AppUI->_("Link to projects")?></option>
				  					<option value="leads"><?php echo $AppUI->_("Link to leads")?></option>
				  				</select>
				  		<?php } ?>
	  					</td>
	  				</tr>
	  				</form>
	  				</table>
	  			</td>
				<td align="right" valign="middle" style="height:30px;">
				<?php
					if($_GET["m"]!="contacts"){
						if(isset($_GET["project_id"])){
							$item_id = $_GET["project_id"];
							$item_field = "project_id";
						}elseif(isset($_GET["company_id"])){
							$item_id = $_GET["company_id"];
							$item_field = "company_id";
						}
						if ( !getDenyEdit("contacts") )
					 		echo '<input type="button" value="'.$AppUI->_("new contact").'" class="button" onClick="javascript:window.location=\'./index.php?m=contacts&a=addedit&'.$item_field.'='.$item_id.'&delegator_id='.$delegator_id.'&hideTabs=1&dialog=\';">';
					}
				?>
	  			</td>
	  		</tr>
	  	</table>
	  </td>
  </tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="1" >
 	  <form name="frmContacts" action="?m=contacts&delegator_id=<?php echo $delegator_id;?>&dialog=<?php echo $dialog;?>" method="POST">
	  <input type="hidden" name="ContactList" value="">
	  <input type="hidden" name="ContactListAction" value="">
	  <input type="hidden" name="dosql" value="contact_actions" />
	  
  <tr bgcolor="#333333" class="boldblanco"> 
	<td width="2%">&nbsp;</td>
    <td width="20">
	  <div align="left">&nbsp;</div></td>
  	<td width="20%" class="tableHeaderText" align="left">
  		<?php if(($_GET["orderby"] == "contact_last_name") || (!isset($_GET["orderby"]))) echo $orderImage?>
	  	<a href="<?=$url?>&orderby=contact_last_name<?=$revertOrder?>" class="">
	  	<?php echo $AppUI->_('Last Name') ?></a>
	</td>
	<td width="20%" class="tableHeaderText" align="left">
		<?php if($_GET["orderby"] == "contact_first_name") echo $orderImage?>
	  	<a href="<?=$url?>&orderby=contact_first_name<?=$revertOrder?>" class="">
	  	<?php echo $AppUI->_('Name') ?></a>
	</td>
	<td class="tableHeaderText" align="left">
		<?php if($_GET["orderby"] == "contact_company") echo $orderImage?>
	  	<a href="<?=$url?>&orderby=contact_company<?=$revertOrder?>" class="">
	  	<?php echo $AppUI->_('Company') ?></a>
	</td>
	<td width="20%" class="tableHeaderText" align="left">
		<?php if($_GET["orderby"] == "contact_email") echo $orderImage?>
	  	<a href="<?=$url?>&orderby=contact_email<?=$revertOrder?>" class="">
	  	<?php echo $AppUI->_('Email') ?></a>
	</td>
	<td width="15%" class="tableHeaderText" align="left">
		<?php if($_GET["orderby"] == "contact_business_phone") echo $orderImage?>
	  	<a href="<?=$url?>&orderby=contact_business_phone<?=$revertOrder?>" class="">
	  	<?php echo $AppUI->_('Telephone') ?></a>
	</td>
	<?php
		if($_GET["m"] == "companies") {
	?>
		<td width="10px" class="tableHeaderText">
		  <div align="right" align="center"><?php echo $AppUI->_('Main') ?></div></td>
	<?php } ?>
  </tr>
  <tr>
	<td height="1" colspan="6" bgcolor="#E9E9E9"></td>
  </tr>
  <?php
  	$js_string = "";
		if (sizeof($carr)==0){
			echo '<tr>';
			echo "<td colspan=\"97\">".$AppUI->_('No data available')."</td></tr>";
		}
	if(isset($_GET["company_id"]))
		$company_url = "&company_id=$_GET[company_id]";
	
	if(!$canEditCompany) $setMainContact = "disabled";
	$oldId = 0;
	
  	for($f=0;$f<sizeof($carr);$f++){
		$canEdit = $AppUI->user_type == 1 || (!getDenyEdit( "contacts", $carr[$f]["contact_id"] ) && $delegator_id == $AppUI->user_id);
		$canEdit |= ( $permisos=="EDITOR" || $permisos=="AUTHOR" && $delegator_id != $AppUI->user_id);
		$canDelete = ( $carr[$f]["contact_owner"] == $delegator_id && $AppUI->user_id == $delegator_id ) || $AppUI->user_type == 1;
		$canDelete |= ($carr[$f]["contact_owner"] == $delegator_id && ($permisos == "AUTHOR" || $permisos == "EDITOR"));
		
		$color = $carr[$f]["contact_owner"] == $delegator_id ? "" : "red";
		
		$s="\n<tr>";
		$s.="\n\t<td nowrap=\"nowrap\" valign=\"bottom\">";
		if ($canEdit){
			$href = "./index.php?m=contacts&dialog=$dialog&delegator_id=$delegator_id&a=addedit&tab=0&contact_id={$carr[$f]["contact_id"]}";
			$onClick="";
			if($module=="contacts"){
				$s.="<input type=\"checkbox\" name=\"contact{$carr[$f]["contact_id"]}\" value=\"{$carr[$f]["contact_id"]}\">&nbsp;";
			}
			if($module=="companies"){
				$href = "#";
				$onClick = "onclick=\"editContact({$carr[$f]["contact_id"]})\"";
			}
			$s.="<a href=\"$href\" $onClick><img src=\"./images/icons/edit_small.gif\" border=\"0\" alt=\"".$AppUI->_('edit')."\" /></a>&nbsp;";
			if($canDelete)
				$s.="<a href=\"#\" onclick=\"delIt_contact(".$carr[$f]["contact_id"].")\">".dPshowImage( './images/icons/trash_small.gif', 15, 15, $AppUI->_('delete') )."</a>";
		}
		$s.="&nbsp;</td>";
		
		if($carr[$f]["contact_public"]=='0'){
        	$s.="\n\t<td valign=\"top\"><img src=\"images/obj/edit_permissions_small.gif\" width=\"20\" height=\"20\" border=\"0\" alt=\"".$AppUI->_( 'private contact' )."\" ></td>";
		}
		else{
        	$s.="\n\t<td>&nbsp;</td>";
        }
        
        $contactData = $AppUI->_("Last Name").": ".$carr[$f]["contact_last_name"]."<br>";
        $contactData .= $AppUI->_("First Name").": ".$carr[$f]["contact_first_name"]."<br>";
        $contactData .= $AppUI->_("Company").": ".$carr[$f]["contact_company"]."<br>";
        $contactData .= "Email: ".$carr[$f]["contact_email"]."<br>";
        $contactData .= $AppUI->_("Phone").": ".$carr[$f]["contact_phone"]."<br>";
        $contactData .= $AppUI->_("Mobile Phone").": ".$carr[$f]["contact_mobile"]."<br>";
        
		$eventsJS = " onmouseover=\"tooltipLink('<pre>$contactData</pre>', 'Contacto');\" onmouseout=\"tooltipClose();\" ";
		$s.="\n\t<td><a href='index.php?m=contacts&a=viewcontact&tab=0&contact_id={$carr[$f]["contact_id"]}$company_url' $eventsJS><strong>".$carr[$f]["contact_last_name"]."</strong></a></td>";
		$s.="\n\t<td><a href='index.php?m=contacts&a=viewcontact&tab=0&contact_id={$carr[$f]["contact_id"]}$company_url' $eventsJS>".$carr[$f]["contact_first_name"]."</a></td>";
		$s.="\n\t<td><a href='index.php?m=contacts&a=viewcontact&tab=0&contact_id={$carr[$f]["contact_id"]}$company_url'><div align=\"left\">".$carr[$f]["contact_company"]."</div></a></td>";
		$s.="\n\t<td>".$carr[$f]["contact_email"]."</td>";
		$s.="\n\t<td><div align=\"left\">".$carr[$f]["contact_business_phone"]."</div></td>";
		if($_GET["m"] == "companies")
		{
			$checked = $carr[$f]["main"] == 1 ? "checked" : "";
			$s.="<td align=\"center\"><input type=\"checkbox\" name=\"chk_".$carr[$f]["contact_id"]."\" $setMainContact $checked $ onclick='changeMainContact(".$carr[$f]["contact_id"].", this, \"".$carr[$f]["contact_company"]."\", ".$carr[$f]["contact_public"].")'></td>";
		}
		$s.="\n</tr>"; 
		$s.="\n<tr>"; 
		$s.="\n\t<td height=\"1\" colspan=\"8\" bgcolor=\"#E9E9E9\"></td>";
	  	$s.="\n</tr>";
	  	$js_string .= "contact_list[contact_list.length] = 'contact{$carr[$f]["contact_id"]}';\n";
		echo $s;
			
		$oldId = $carr[$f]["contact_id"];
	}
  ?>
  </form>
  <form name="delContact" method="POST" action="index.php?m=contacts&a=do_contact_aed<?=$filed_url?>">
	<input type="hidden" name="del" value="0" />
		<input type="hidden" name="contact_project" value="0" />
		<input type="hidden" name="origen" value="<?php echo $origen;?>" />
		<input type="hidden" name="contact_unique_update" value="<?php echo uniqid("");?>" />
		<input type="hidden" name="contact_id" value="<?php echo $contact_id;?>" />
		<input type="hidden" name="contact_owner" value="<?php echo ($row->contact_owner) ? $row->contact_owner : $user_id;?>" />
		<input type="hidden" name="contact_company_ch" value="0" />
		<input type="hidden" name="contact_creator" value="<?php echo $row->contact_creator ? $row->contact_creator : $AppUI->user_id;?>" />
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
</table>"; ?>

<br>
<script language="javascript"><!--
<?php  echo $js_string;?>

function delIt_contact(id){
	var form = document.delContact;
	if(confirm1( "<?php echo $AppUI->_('contactsDelete');?>" )) {
		form.del.value = id;
		form.submit();
	}
}
// -->
</script>
<?php if($module=="contacts"){ ?>
<strong><?php echo $AppUI->_("Selection")?>:&nbsp;</strong>
<a href="javascript: //" onclick="javascript: switch_selection('select');"><?php
echo $AppUI->_("selAll")?></a>&nbsp;|&nbsp;
<a href="javascript: //" onclick="javascript: switch_selection('deselect');"><?php
echo $AppUI->_("selNone")?></a>&nbsp;|&nbsp;
<a href="javascript: //" onclick="javascript: switch_selection('invert');"><?php
echo $AppUI->_("selInvert")?></a>
<?php } ?>