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
if (!$canEdit) $ro=" disabled ";

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

<?php /* DEPARTMENTS $Id: addedit.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
// pull data for this department
// setup the title block
$ttl = $lead_id > 0 ? "Edit lead" : "Add lead";
$titleBlock = new CTitleBlock( $ttl, 'pipeline.gif', $m, 'ID_HELP_DEPT_EDIT' );

$titleBlock->addCrumb( "?m=pipeline&delegator_id=".$delegator_id.($listOP == 1 || $delegator_id == $AppUI->user_id ? '&listOP=1' : ''), "opportunities list" );

if ($lead_id > 0)
{
	$titleBlock->addCrumb( "?m=pipeline&a=view&lead_id=".$lead_id."&delegator_id=".$delegator_id.($listOP == 1 || $delegator_id == $AppUI->user_id ? '&listOP=1' : ''), "view this opportunity" );
}

$titleBlock->show();
?>
<script language="javascript">

function submitIt() {
	var form = document.editFrm;
    var berror=false;
    
	if (trim(form.accountname.value).length == 0) {
		alert( "<?php echo $AppUI->_('Please enter opportunity name')?>" );
		form.accountname.focus();
        berror=true;
    }else if(trim(form.description.value).length == 0){
        alert( "<?php echo $AppUI->_('Please enter account description')?>" );
        form.description.focus();
        berror=true;
    }else if(form.probability.value > 100){
        alert( "<?php echo $AppUI->_('Probability value must be between 0-100')?>" );
        form.probability.focus();
        berror=true;
    }    
    
    //Validar el nombre de la cuenta y si esta todo bien, hacer submit
    if(!berror){
    	xajax_validateAccountName(form.id.value, trim(form.accountname.value));
    }

	/*
    if(!berror){
        form.submit();
    }
    */
}

function checkNumeric(bchars){
    a=window.event.keyCode;
    if(!(a>=48 && a <= 57)){
        if (!(bchars && (a==45 || a==46))){
            window.event.returnValue=false;
        }
    }
}

function statusChange() {
  win1.style.display='none';
  win2.style.display='none';
  win3.style.display='none';
  win4.style.display='none';
  loss1.style.display='none';
  loss2.style.display='none';
  common.style.display='none';
  if(document.editFrm.status.selectedIndex==5){
    common.style.display="";
    loss1.style.display="";
    loss2.style.display="";
  }
  if(document.editFrm.status.selectedIndex==4){
    common.style.display="";
    win1.style.display="";
    win2.style.display="";
    win3.style.display="";
    win4.style.display="";
  }
}

</script>
<table cellspacing="0" cellpadding="4" border="0" width="98%" class="tableForm_bg">
<form name="editFrm" action="?m=pipeline&delegator_id=<?=$delegator_id?>&dialog=<?=$dialog?>" method="post">
	<input type="hidden" name="validateAccountName" value="" />
	<input type="hidden" name="dosql" value="do_lead_aed" />
	<input type="hidden" name="id" value="<?php echo $lead->id;?>" />
	<input type="hidden" name="lead_creator" value="<?=$lead->lead_creator ? $lead->lead_creator : $AppUI->user_id?>" />
	<input type="hidden" name="lead_owner" value="<?=$lead->lead_owner ? $lead->lead_owner : $delegator_id?>" />
    <input type="hidden" name="accountmanager" value="<?=$lead->accountmanager;?>">
<tr>
	<td align="right"><?php echo $AppUI->_( 'Status' );?>:</td>
	<td>
          <select name="status" class="text" onChange="statusChange();" <?=$ro?>>
            <option <?if($lead->status=="Opportunity" || (!$lead->status && $type=="Opportunity") )echo "selected";?> value="Opportunity"><?php echo $AppUI->_( 'Opportunity' );?></option>
            <option <?if($lead->status=="On Hold")echo "selected";?> value="On Hold"><?php echo $AppUI->_( 'On Hold' );?></option>
            <option <?if($lead->status=="Negotiation")echo "selected";?> value="Negotiation"><?php echo $AppUI->_( 'Negotiation' );?></option>
            <option <?if($lead->status=="Decision")echo "selected";?> value="Decision"><?php echo $AppUI->_( 'Decision' );?></option>
            <option <?if($lead->status=="Win" || (!$lead->status && $type=="Win") )echo "selected";?> value="Win"><?php echo $AppUI->_( 'Win' );?></option>
            <option <?if($lead->status=="Loss" || (!$lead->status && $type=="Loss") )echo "selected";?> value="Loss"><?php echo $AppUI->_( 'Loss' );?></option>
          </select>
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $AppUI->_( 'Probability of Winning' );?>:
	<input type="text" class="text" name="probability" onkeypress="javascript:checkNumeric(false);" value="<?php echo @$lead->probability;?>" maxlength="3" size="3" <?=$ro?>>
        </td>
	<td valign="top" rowspan="3" align="right"><?php echo $AppUI->_( 'Third Parties' );?>:</td>
	<td rowspan="3"><textarea rows="6" cols="40" class="text" name="thirdparties" <?=$ro?>><?php echo @$lead->thirdparties;?></textarea></td>
</tr>

<tr>
    <td align="right"><?php echo $AppUI->_( 'Account Manager' );?>:</td>
    <td>
	<select class="text" name="accountmanager" <?=$ro?> >
	      <option value="0" ><?php echo $AppUI->_( 'Not Specified' );?></option>

		  <?		  
			require_once( $AppUI->getModuleClass( "companies" ) );
			$accountManagers = CCompany::getUsersCompany($AppUI->user_company);
			//echo arraySelect( $accountManagers, 'accountmanager', 'size="1" class="text" '.$ro, $lead->accountmanager, false, true, '' );
			natcasesort($accountManagers);
		  
		  	foreach($accountManagers as $id => $value)
		  	{
				if($lead->accountmanager == $id) 
					echo "<option value=\"".$id."\" selected >".$value."</option>";
				else
					echo "<option value=\"".$id."\" >".$value."</option>";
		   }
		?>
		</select>    
    </td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_( 'Segment' );?>:</td>
	<td><!-- <input type="text" class="text" name="segment" value="<?php echo @$lead->segment;?>" maxlength="47" size="47" <?=$ro?>> -->
	<select class="text" name="segment" <?=$ro?> >
	      <option value="0" ><?php echo $AppUI->_( 'Not Specified' );?></option>

		  <?

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

		       if(@$lead->segment == $vec[id_segment]) 
			   {
			   $sel = "selected";
			   }
			   else
			   {
			   $sel ="";
			   }

		   echo  "<option value=\"$vec[description]\" $sel >$vec[description]</option>";
		   }


		?>
		</select>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Opportunity Name' );?>:</td>
	<td><input type="text" class="text" name="accountname" value="<?php echo @$lead->accountname;?>" maxlength="45" size="45" <?=$ro?>> *</td>
	<td valign="top" rowspan="4" align="right">* <?php echo $AppUI->_( 'Description' );?>:</td>
	<td rowspan="4" valign="top" ><textarea rows="8" cols="40" class="text" name="description" <?=$ro?>><?php echo @$lead->description;?></textarea></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Code' );?>:</td>
	<td><input type="text" class="text" disabled name="projecttype" value="<?php echo @$lead->opportunitycode;?>" maxlength="47" size="15" <?=$ro?>></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Project Type' );?>:</td>
	<td><input type="text" class="text" name="projecttype" value="<?php echo @$lead->projecttype;?>" maxlength="47" size="47" <?=$ro?>></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Opportunity Source' );?>:</td>
	<td><? echo arraySelect( $canal, 'opportunitysource', 'class="text" '.$ro, @$lead->opportunitysource,TRUE , FALSE, '80%' ); ?></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Total Income' );?>:</td>
		<td nowrap="nowrap"><input onKeyUp="writeMargin()" onKeyPress="javascript:checkNumeric(true);" type="text" class="text" name="totalincome" value="<?php echo @$lead->totalincome;?>" maxlength="9" size="9" <?=$ro?>>
	<?php echo $AppUI->_( 'Cost' );?>:
	<input onKeyUp="writeMargin()" onKeyPress="javascript:checkNumeric(true);" type="text" class="text" name="cost" value="<?php echo @$lead->cost;?>" maxlength="9" size="9" <?=$ro?>>
	<?php echo $AppUI->_( 'Margin' );?>:
	<input type="text" onKeyPress="javascript:checkNumeric(true);" disabled class="text" name="margin" value="<?php echo @$lead->margin;?>" maxlength="9" size="9" <?=$ro?>></td>
	<td valign="top" rowspan="5" align="right"><?php echo $AppUI->_( 'Competition' );?>:</td>
	<td rowspan="5" valign="top" ><textarea rows="10" cols="40" class="text" name="competition" <?=$ro?>><?php echo @$lead->competition;?></textarea></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Revised' );?>:</td>
	<td><input type="text" class="text" name="revised" value="<?php echo @$lead->revised;?>" maxlength="22" size="22" <?=$ro?>></td>	
</tr>
<?
$closingDate = new CDate( $lead->closingdate );
$invoiceDate = new CDate( $lead->invoicedate );
?>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Closing Date' );?>:</td>
	<td>
		<input type="hidden" name="closingdate" value="<?php echo $closingDate->format( FMT_DATETIME_MYSQL ); ?>">
		<input type="text" class="text" name="_closingdate" value="<?php echo $closingDate->format( $df );?>" maxlength="10" size="10" disabled>
		<a href="#" onClick="popCalendar('closingdate')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>	
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Invoice Date' );?>:</td>
	<td>
		<input type="hidden" name="invoicedate" value="<?php echo $invoiceDate->format( FMT_DATETIME_MYSQL )?>">
		<input type="text" class="text" name="_invoicedate" value="<?php echo $invoiceDate->format( $df );?>" maxlength="10" size="10" disabled>
		<a href="#" onClick="popCalendar('invoicedate')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>	
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Duration' );?>:</td>
	<td><input type="text" onKeyPress="javascript:checkNumeric(true);" class="text" name="duration" value="<?php echo @$lead->duration;?>" maxlength="4" size="4" <?=$ro?>></td>
</tr>
<DIV style="display: none;" id=common>
<tr>
	<td valign="top" align="right"><?php echo $AppUI->_( 'Client Feedback' );?>:</td>
	<td><textarea rows="6" cols="40" class="text" name="clientfeedback" <?=$ro?>><?php echo @$lead->clientfeedback;?></textarea>
        </td>
	<td valign="top" align="right"><?php echo $AppUI->_( 'Team Comments' );?>:</td>
	<td><textarea rows="6" cols="40" class="text" name="teamcomments" <?=$ro?>><?php echo @$lead->teamcomments;?></textarea>
        </td>
</tr>
</DIV>
<tr>
	<td valign="top" align="right"><DIV style="display: none;" id=win1><?php echo $AppUI->_( 'Reference Account' );?>:</DIV></td>
	<td><DIV style="display: none;" id=win2><textarea rows="6" class="text" cols="40" name="referenceaccount" <?=$ro?>><?php echo @$lead->referenceaccount;?></textarea></DIV>
        </td>
	<td rowspan="3"  valign="top"align="right"><DIV style="display: none;" id=win3><?php echo $AppUI->_( 'Case Study' );?>:</DIV></td>
	<td rowspan="3"  valign="top"><DIV style="display: none;" id=win4><input type="text" class="text" name="casestudy" value="<?php echo @$lead->casestudy;?>" maxlength="40" size="40" <?=$ro?>></DIV></td>
</tr>
<tr>
  
	<td align="right"><DIV style="display: none;" id=loss1><?php echo $AppUI->_( 'Selected Competitor' );?>:</DIV></td>
	<td><DIV style="display: none;" id=loss2><input type="text" class="text" name="selectedcompetitor" value="<?php echo @$lead->selectedcompetitor;?>" maxlength="47" size="47" <?=$ro?>></DIV></td>
</tr>
<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back();" />
	</td>
	<td colspan="4" align="right">
		<? 
		if ( $canEdit )
		{
			?>
		<input type="button" value="<?php echo $AppUI->_( 'submit' );?>" class="button" onClick="submitIt()" />
			<?
		}
		?>
	</td>
</tr>
</form>
</table>

<script language="javascript">
  statusChange();
</script>
