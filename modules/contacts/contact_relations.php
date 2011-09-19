<?php
global $canEdit, $contact_id, $row, $delegator_id, $delegador, $xajax, $AppUI, $type, $hideControl;

$ObjContact = new CContact();
$ObjContact->contact_id = $contact_id;

switch ($type){
	case 'projects':
		$items = $ObjContact->getRelatedProjects();
		
		require_once("./modules/projects/projects.class.php");
		$ObjProject = new CProject();
		$items_av = $ObjProject->getAllowedRecords( $AppUI->user_id," project_id, project_name" , "project_name" );		
		
		while(LIST($projID,$itemName) = EACH($items)){
			if(!array_key_exists($projID,$items_av)){
				unset($items[$projID]);		//Si no está entre los Proyectos permitidos no lo muestra
			}else{
				unset($items_av[$projID]);	//Si ya está relacionado no lo muestra para relacionar
			}
		}
		
		foreach($items AS $projID=>$projName){
			$sql = "SELECT company_name FROM companies INNER JOIN projects ON project_company = company_id
			WHERE project_id = ".$projID;
			
			$proj_data = mysql_fetch_array(mysql_query($sql));
			
			$items[$projID] = $proj_data["company_name"]." / ".$projName;
		}
		
		foreach($items_av AS $projID=>$projName){
			$sql = "SELECT company_name FROM companies INNER JOIN projects ON project_company = company_id
			WHERE project_id = ".$projID;
			
			$proj_data = mysql_fetch_array(mysql_query($sql));
			
			$items_av[$projID] = $proj_data["company_name"]." / ".$projName;
		}
		
		asort($items);
		asort($items_av);
	break;
	
	case 'companies':
		require_once("./modules/companies/companies.class.php");
		$items = $ObjContact->getRelatedCompanies();
		$ObjCompany = new CCompany();
		$companies_obtained = $ObjCompany->getCompanies( $AppUI->user_id ,false," company_name ");
		
		for($i=0; $i<count($companies_obtained); $i++){
			$items_av[$companies_obtained[$i]["company_id"]]=$companies_obtained[$i]["company_name"];
		}
		
		while(LIST($compID,$comName) = EACH($items)){
			if(!array_key_exists($compID,$items_av)){
				unset($items[$compID]);		//Si no está entre los Proyectos permitidos no lo muestra
			}else{
				unset($items_av[$compID]);	//Si ya está relacionado no lo muestra para relacionar
			}
		}
	break;

	case 'leads':
		$items = $ObjContact->getRelatedLeads();
		
		require_once("./modules/pipeline/leads.class.php");
		$ObjLead = new CLead();
		$items_av = $ObjLead->getAllowedLeads();
		
		//die("<pre>".print_r($items_av)."</pre>");
		
		while(LIST($leadID,$itemName) = EACH($items)){
			if(!array_key_exists($leadID,$items_av)){
				unset($items[$leadID]);
			}else{
				unset($items_av[$leadID]);
			}
		}
	break;
	
	case 'events':
		$items = $ObjContact->getRelatedEvents();
		
		require_once("./modules/calendar/calendar.class.php");
		require_once("./modules/projects/projects.class.php");
		$ObjEvent = new CEvent();
		$first_time = new CDate("01-01-1900");
		$last_time = new CDate();
		$events_obtained = $ObjEvent->getEventsForPeriod($first_time,$last_time);
		
		foreach ($events_obtained AS $event){
			$sql = "SELECT event_title FROM events WHERE event_id = $event[event_id]";
			$row = mysql_fetch_array(mysql_query($sql));
			$items_av[$event["event_id"]] = $row["event_title"];
		}
		asort($items_av);
		
		while(LIST($eventID,$itemName) = EACH($items)){
			if(!array_key_exists($eventID,$items_av)){
				unset($items[$eventID]);
			}else{
				unset($items_av[$eventID]);
			}
		}
	break;
	
	default:
	break;
}

$AppUI->items_av = $items_av;
$AppUI->related_items = $items;
?>
<form name="editFrm" method="POST" action="">
<input type="hidden" value="<?php echo $contact_id; ?>" name="contact_id">
<input type="hidden" name="dosql" value="do_relations_aed">
<input type="hidden" name="related_items" value="">
<input type="hidden" name="relation_type" value="<?php echo $type; ?>">
<table cellspacing="0" cellpadding="2" border="0" class="std" width="100%" align="center">
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
		   <?php echo arraySelect( $items_av, "av_items", 'style="width:350px;height:250px;" size="10" class="text" multiple="multiple"', null, false, false );?>
		</td>
		<td align="left">
			<?php echo arraySelect( $items, "items", 'style="width:350px;height:250px;" size="10" class="text" multiple="multiple"', null, false, false );?>
		</td>
	<tr>
	<?php if(!isset($hideControl)) {?>
		<td></td>
		<td align="right">
		  <input type="button" class="button" value="&gt;" onClick="addItem_ajax();" />
		</td>
		<td align="left">
		  <input type="button" class="button" value="&lt;" onClick="delItem_ajax();" />
		</td>
	<?php } ?>
	</tr>
	</tr>
	<tr>
		<td><br><br>
		</td>
	</tr>
	<tr>
		<td>
			<input type="button" value="<?php echo $AppUI->_('back');?>" class="button" onClick="javascript:history.back();" />
		</td>
	<?php if(!isset($hideControl)) {?>
		<td colspan="3" align="right">
			<input type="button" value="<?php echo $AppUI->_('save'); ?>" class="button" onclick="submitIt()">
		</td>
		<td width="2%"></td>
	<?php } ?>
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