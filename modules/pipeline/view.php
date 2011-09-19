<?

include_once("modules/projects/read_projects.inc.php");
include_once("modules/calendar/calendar.class.php");
require_once("./modules/timexp/report_to_items.php");

$AppUI->savePlace();

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'PipelineVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'PipelineVwTab' ) !== NULL ? $AppUI->getState( 'PipelineVwTab' ) : 0;

$type = dPgetParam( $_GET, "type", "Opportunity");
$debugsql=0;
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id);
//$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $user_id );
$mod_id = 18;
$lead_id = dPgetParam( $_GET, "lead_id", 0 );
$df = $AppUI->getPref('SHDATEFORMAT');

//El archivo deberia llamarse pipeline.class.php!!!
//require_once( $AppUI->getModuleClass( "pipeline") );
require_once( $AppUI->getConfig( "root_dir")."/modules/pipeline/leads.class.php" );

$lead = new CLead();
if ( !$lead->load( $lead_id ) && $lead_id > 0)  
{
	$AppUI->setMsg( 'Lead' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} 

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
	$canEdit = !$lead_id;
	$canEdit = $canEdit || ( $permisos == "AUTHOR" && $lead->lead_owner == $delegator_id && $lead->lead_creator == $AppUI->user_id );
	$canEdit = $canEdit || ( $permisos == "EDITOR" && $lead->lead_owner == $delegator_id);
	$canEdit = $canEdit || $AppUI->user_type == 1;
}
else
{
	if ( !$canRead )
	{
		$AppUI->redirect( "m=public&a=access_denied" );
	}
}
if (!$canEdit) $ro=" READONLY ";

$extra = array();
$extra['from'] = '';
$extra['where'] ='and company_type=0';
$obj = new CCompany();

$canal = $obj->getAllowedRecords( $AppUI->user_id, 'company_name', 'company_name',$index= null, '');
$canal = arrayMerge( array( '0'=>$AppUI->_('All') ), $canal );

?>
<script language="javascript">

var calendarField = '';

function popCalendar( field )
{
	calendarField = field;
	idate = eval( 'document.editFrm.' + field + '.value' );	
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate +'&suppressLogo=1', 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate )
{	
	fld_date = eval( 'document.editFrm.' + calendarField );	
	fld_fdate = eval( 'document.editFrm._' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;	
}

function writeMargin() {
	if(!isNaN(document.editFrm.totalincome.value - document.editFrm.cost.value))
	  document.editFrm.margin.value = document.editFrm.totalincome.value - document.editFrm.cost.value;
}

function delEvent(pEventId) {
	if (confirm( "¿Borrar el evento?" )) {
		document.frmDeleteEvent.event_id.value = pEventId;
		document.frmDeleteEvent.submit();
	}
}

function popAccountManager() {
    window.open('./index.php?m=public&a=selector&dialog=1&suppressLogo=1&callback=setAccountManager&table=users', 'selector', 'left=50,top=50,height=300,width=400,resizable');
}
function setAccountManager( key, val ) {
    var f = document.editFrm;
    if (key > 0) {
        f.accountmanager.value = key;
        f.accountmanagername.value = val;
    } else {
        f.accountmanager.value = '-1';
        f.accountmanagername.value = '';
    }
}
</script>
<script language="javascript">
function delFile( x, y, z ) {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Lead File');?> " + y + "?" )) {
		document.frmDeleteLeadFile.id.value = x;
		document.frmDeleteLeadFile.lead_id.value = z;
		document.frmDeleteLeadFile.submit();
	}
}

function delContact(id){
	var form = document.delContact;
	if(confirm1( "<?php echo $AppUI->_('contactsDelete');?>" )) {
		form.del.value = id;
		form.submit();
	}
}
</script>
<form name="frmDeleteLeadFile" action="./index.php?m=pipeline&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" method="post">
	<input type="hidden" name="dosql" value="do_leadfile_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="id" value="0" />
	<input type="hidden" name="lead_id" value="<?php echo $lead->id;?>" />
</form>

<form name="frmDeleteEvent" action="./index.php?m=calendar" method="post">
	<input type="hidden" name="dosql" value="do_event_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="frompipeline" value="1" />
	<input type="hidden" name="event_id" value="" />
	<input type="hidden" name="lead_id" value="<?=$lead->id?>">	
</form>

<form name="delContact" method="POST" action="/index.php?m=contacts&a=do_contact_aed">
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="contact_project" value="0" />
	<input type="hidden" name="origen" value="m=pipeline&a=addedit&lead_id=<?=$lead_id?>&delegator_id=<?=$delegator_id?>" />
	<input type="hidden" name="contact_unique_update" value="<?php echo uniqid("");?>" />
	<input type="hidden" name="contact_id" value="<?php echo $contact_id;?>" />
	<input type="hidden" name="contact_owner" value="<?php echo ($row->contact_owner) ? $row->contact_owner : $user_id;?>" />
	<input type="hidden" name="contact_company_ch" value="0" />
	<input type="hidden" name="contact_creator" value="<?php echo $row->contact_creator ? $row->contact_creator : $AppUI->user_id;?>" />
</form>

<?php /* DEPARTMENTS $Id: view.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
// pull data for this department
// setup the title block
$ttl = $lead_id > 0 ? "Edit lead" : "Add lead";
$titleBlock = new CTitleBlock( $ttl, 'pipeline.gif', $m, 'ID_HELP_DEPT_EDIT' );

if ($lead_id > 0)
{
	$titleBlock->addCrumb( "?m=pipeline&delegator_id=".$delegator_id.($listOP == 1 || $delegator_id == $AppUI->user_id ? '&listOP=1' : ''), "opportunities list" );
	$titleBlock->addCrumb( "?m=pipeline&a=addedit&lead_id=".$lead_id."&delegator_id=".$delegator_id.($listOP == 1 || $delegator_id == $AppUI->user_id ? '&listOP=1' : ''), "edit this opportunity" );

	include_once('./modules/public/itemToFavorite_functions.php');
	$deleteFavorite = HasItemInFavorites($project_id, 1);	
	$titleBlock->addCrumb( "javascript:itemToFavorite(".$lead_id.", 2, $deleteFavorite);", $deleteFavorite == 1 ? $AppUI->_('remove from favorites') : $AppUI->_('add to favorites') );
}

$titleBlock->show();
?>
<script language="javascript">

function itemToFavorite(item_id, item_type, item_delete)
{
	window.top.location = "./index.php?m=public&a=itemToFavorite&item_id=" + item_id + "&item_type=" + item_type + "&item_mode_del=" + item_delete + "&dialog=1&suppressLogo=1";
}

</script>
<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
	<form name="editFrm" action="?m=pipeline&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" method="post">
		<input type="hidden" name="dosql" value="do_lead_aed" />
		<input type="hidden" name="id" value="<?php echo $lead->id;?>" />
		<input type="hidden" name="lead_creator" value="<?=$lead->lead_creator ? $lead->lead_creator : $AppUI->user_id?>" />
		<input type="hidden" name="lead_owner" value="<?=$lead->lead_owner ? $lead->lead_owner : $delegator_id?>" />
    	<input type="hidden" name="accountmanager" value="<?=$lead->accountmanager;?>">
    	
	<tr>
		<td width="50%" valign="top">
			<strong><?php echo $AppUI->_('Details');?></strong>
			
			<table cellspacing="1" cellpadding="2" border="0" width="90%" align="center">
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Status' );?>:</td>
					<td class="hilite" width="50%"><?php echo $AppUI->_( $lead->status );?></td>
					<td valign="top" rowspan="5" align="right" nowrap><?php echo $AppUI->_( 'Third Parties' );?>:</td>
					<td rowspan="5" valign="top" class="hilite" width="50%"><?php echo str_replace("\n","<br />", @$lead->thirdparties);?></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Probability of Winning' );?>:</td>
					<td class="hilite"><?php echo @$lead->probability;?></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Account Manager' );?>:</td>
					<td class="hilite"><?php echo @$lead->_accountmanagername;?></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Segment' );?>:</td>
					<?
						if(@$lead->segment != '0')
							$segmentDescription = @$lead->segment;
						else
							$segmentDescription = $AppUI->_( 'Not Specified' );
					?>
					<td class="hilite"><?php echo $segmentDescription;?></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Opportunity Name' );?>:</td>
					<td class="hilite"><?php echo @$lead->accountname;?></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Project Type' );?>:</td>
					<td class="hilite"><?php echo @$lead->projecttype;?></td>
					<td valign="top" rowspan="5" align="right" nowrap><?php echo $AppUI->_( 'Description' );?>:</td>
					<td rowspan="5" valign="top" class="hilite"><?php echo str_replace("\n","<br />", @$lead->description);?></td>					
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Opportunity Source' );?>:</td>
					<?
						if(@$lead->opportunitysource != '0')
							$sourceDescription = @$lead->opportunitysource;
						else
							$sourceDescription = $AppUI->_( 'All' );
					?>
					<td class="hilite"><?php echo $sourceDescription;?></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Total Income' );?>:</td>
					<td class="hilite"><?php echo @$lead->totalincome;?></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Cost' );?>:</td>
					<td class="hilite" nowrap><?php echo @$lead->cost;?></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Margin' );?>:</td>
					<td class="hilite"><?php echo @$lead->margin;?></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Revised' );?>:</td>
					<td class="hilite"><?php echo @$lead->revised;?></td>
					<td valign="top" rowspan="4" align="right"><?php echo $AppUI->_( 'Competition' );?>:</td>
					<td rowspan="4" valign="top" class="hilite" nowrap><?php echo str_replace("\n","<br />", @$lead->competition);?></td>					
				</tr>
				<?
				$closingDate = new CDate( $lead->closingdate );
				$invoiceDate = new CDate( $lead->invoicedate );
				?>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Closing Date' );?>:</td>
					<td class="hilite"><?php echo $closingDate->format( $df );?></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Invoice Date' );?>:</td>
					<td class="hilite"><?php echo $invoiceDate->format( $df );?></td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_( 'Duration' );?>:</td>
					<td class="hilite"><?php echo @$lead->duration;?></td>
				</tr>
				<tr>
					<td valign="top" align="right" nowrap><?php echo $AppUI->_( 'Client Feedback' );?>:</td>
					<td class="hilite" valign="top"><?php echo str_replace("\n","<br />", @$lead->clientfeedback);?></td>
					<td valign="top" align="right" nowrap><?php echo $AppUI->_( 'Team Comments' );?>:</td>
					<td class="hilite" valign="top"><?php echo str_replace("\n","<br />", @$lead->teamcomments);?></td>
				</tr>
				<tr>
					<td valign="top" align="right" nowrap><?php echo $AppUI->_( 'Reference Account' );?>:</DIV></td>
					<td class="hilite"><?php echo str_replace("\n","<br />", @$lead->referenceaccount);?></td>
					<td rowspan="3" valign="top"align="right" nowrap><?php echo $AppUI->_( 'Case Study' );?>:</td>
					<td rowspan="3" class="hilite" valign="top"><?php echo @$lead->casestudy;?></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Selected Competitor' );?>:</td>
					<td class="hilite"><?php echo @$lead->selectedcompetitor;?></td>
				</tr>
				<tr>
					<td align="right" nowrap><?php echo $AppUI->_( 'Code' );?>:</td>
					<td class="hilite"><?php echo @$lead->opportunitycode;?></td>
				</tr>				
			</table>
		</td>
	</tr>
	</form>
</table>
<?
if ( $lead_id )
{
	$tabBox = new CTabBox( "?m=pipeline&a=view&lead_id=$lead_id&delegator_id=$delegator_id", "", $tab);

	if(!getDenyRead( 'files' ))
		$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/pipeline/vw_files", 'Documents' );
		
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/pipeline/vw_contacts", 'Contacts' );
	$tabBox->add( "{$AppUI->cfg['root_dir']}/modules/pipeline/vw_events", 'Events' );
	$tabBox->show();
}
?>
<br />
