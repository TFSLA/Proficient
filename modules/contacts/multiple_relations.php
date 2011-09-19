<?php
global $canEdit, $contact_id, $row, $delegator_id, $delegador, $xajax, $AppUI;

$contactList = explode(',',$_POST["ContactList"]);
$type = $_POST["ContactListAction"];

// setup the title block
$titleBlock = new CTitleBlock( 'Contacts', 'contacts.gif', $m, "colaboration.index" );

$titleBlock->addCell( $tblForm );

if ( ( $canAdd && ($delegator_id == $AppUI->user_id || empty($delegator_id) )) || ($permisos == "AUTHOR" && $delegator_id != $AppUI->user_id))
{
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new contact').'">', '',
		'<form action="?m=contacts&delegator_id='.$delegator_id.'&a=addedit&dialog='.$dialog.'" method="post">
		<input type="hidden" name="hideTabs" value=1>
		<input type="hidden" name="origen" value="m=contacts">', '</form>'
	);	
}

$titleBlock->addCrumb("?m=contacts&delegator_id=$delegator_id", $AppUI->_("contacts list"));

$titleBlock->show();

$ObjContact = new CContact();
$ObjContact->contact_id = $contact_id;

switch ($type){
	case 'projects':
		require_once("./modules/projects/projects.class.php");
		$ObjProject = new CProject();
		$items_av = $ObjProject->getAllowedRecords( $AppUI->user_id," project_id, project_name" , "project_name" );
		
		foreach($items_av AS $projID=>$projName){
			$sql = "SELECT company_name FROM companies INNER JOIN projects ON project_company = company_id
			WHERE project_id = ".$projID;
			
			$proj_data = mysql_fetch_array(mysql_query($sql));
			
			$items_av[$projID] = $proj_data["company_name"]." / ".$projName;
		}
	break;

	case 'companies':
		require_once("./modules/companies/companies.class.php");
		$ObjCompany = new CCompany();
		$companies_obtained = $ObjCompany->getCompanies( $AppUI->user_id ,false," company_name ");
		
		for($i=0; $i<count($companies_obtained); $i++){
			$items_av[$companies_obtained[$i]["company_id"]]=$companies_obtained[$i]["company_name"];
		}
	break;

	case 'leads':
		require_once("./modules/pipeline/leads.class.php");
		$ObjLead = new CLead();
		$items_av = $ObjLead->getAllowedLeads();
	break;
	
	default:
		$items = array("0"=>"None");
		$items_av = array("0"=>"None");
	break;
}

$items = array("0"=>"0");
$AppUI->items_av = $items_av;
$AppUI->related_items = null;
?>
<form name="editFrm" method="POST" action="">
<input type="hidden" value="<?php echo $contact_id; ?>" name="contact_id">
<input type="hidden" name="dosql" value="do_multiplerelations_aed">
<input type="hidden" name="related_items" value="">
<input type="hidden" name="relation_type" value="<?php echo $type; ?>">
<input type="hidden" name="ContactList" value="<?php echo $_POST["ContactList"]; ?>">
<input type="hidden" name="ContactListAction" value="<?php echo $_POST["ContactListAction"]; ?>">
<table cellspacing="0" cellpadding="2" border="0" class="std" width="100%" align="center">
	<tr>
		<td>&nbsp;<b><?php echo $AppUI->_("Contacts to be related"); ?>:</b>
		</td>
	</tr>
	<tr>
		<td colspan="3">&nbsp;
		<?php
			//die("<pre>".print_r($contactList)."</pre>");
			for ($i=0; $i<count($contactList); $i++){
				$sql = "SELECT contact_order_by AS contact_desc
						FROM contacts WHERE contact_id = ".$contactList[$i];
				
				$data = mysql_fetch_array(mysql_query($sql));
				echo $data["contact_desc"];
				if($i < count($contactList) - 1){
					echo " - ";
				}
			}
		?>
		</td>
	</tr>
	<tr>
		<td><br><br>
		</td>
	</tr>
	<tr>
		<td width="25%"></td>
		<td align="left"><?php echo $AppUI->_("All ".$type); ?>
		</td>
		<td width="50%"><?php echo $AppUI->_("Related ".$type); ?>
		</td>
	</tr>
	<tr>
		<td></td>
	    <td align="right">
		   <?php echo arraySelect( $items_av, "av_items", 'style="width:250px" size="10" class="text" multiple="multiple"', null, false, false );?>
		</td>
		<td align="left">
			<select name="items" style="width:250px" size="10" class="text" multiple="multiple">
			</select>
		</td>
	<tr>
		<td></td>
		<td align="right">
		  <input type="button" class="button" value="&gt;" onClick="addItem_ajax();" />
		</td>
		<td align="left">
		  <input type="button" class="button" value="&lt;" onClick="delItem_ajax();" />
		</td>
	</tr>
	</tr>
	<tr>
		<td><br><br>
		</td>
	</tr>
	<tr>
		<td colspan="4" align="right">
			<input type="button" value="<?php echo $AppUI->_('save'); ?>" class="button" onclick="submitIt()">
		</td>
		<td width="2%"></td>
	</tr>
	<tr>
		<td><br>
		</td>
	</tr>
</table>
</form>

<script language="javascript">
function addItem_ajax(){
	var form = document.editFrm;
	var fl = form.av_items.length -1;
	
	for(i=0;i<form.av_items.options.length;i++)
    {
        if(form.av_items.options[i].selected)
           xajax_addItem('av_items', 'items', form.av_items.options[i].value);
    }
}

function delItem_ajax(){
	var form = document.editFrm;
	var fl = form.items.length -1;
	
	for(i=0;i<form.items.options.length;i++)
    {
        if(form.items.options[i].selected)
          xajax_delItem('items', 'av_items', form.items.options[i].value);
    }
}

function submitIt() {
	var form = document.editFrm;
    var fl = form.items.length -1;
	var pl = form.items.length -1;
    
	form.related_items.value = "";
	for (pl; pl > -1; pl--){
		form.related_items.value = form.items.options[pl].value +","+ form.related_items.value
	}

	form.submit();
}

</script>