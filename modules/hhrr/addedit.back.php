<?php /* DEPARTMENTS $Id: addedit.back.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
// Add / Edit Company
global $AppUI;
$id = isset($_GET['id']) ? $_GET['id'] : 0;
/*
echo "<pre>";
var_dump($HTTP_COOKIE_VARS);
echo "</pre>";
*/

$MaritalStates= dPgetSysVal("MaritalState");
$IMTypes= dPgetSysVal("IMType");
$WorkTypes= dPgetSysVal("WorkType");
$SCandidateStatus = dPgetSysVal("CandidateStatus");

$uploads_dir = $AppUI->getConfig('hhrr_uploads_dir');
$extfiles_cv = $AppUI->getConfig('hhrr_cv_extensions');
$extfiles_pic = $AppUI->getConfig('hhrr_pic_extensions');

$canAdd = CHhrr::canAdd();
$canEdit = CHhrr::canEdit($id);

if ($id == 0){
	$canEdit = $canAdd;
}


// check permissions
if (!$canEdit) {
    $AppUI->redirect( "m=public&a=access_denied" );
}

$canEditModule = !getDenyEdit( "hhrr" );

// build array of times in 30 minute increments
$times = array();
$t = new CDate();
$t->setTime( 6,0,0 );
if (!defined('LOCALE_TIME_FORMAT'))
  define('LOCALE_TIME_FORMAT', '%I:%M %p');
$times["NULL"]="";
for ($j=0; $j < 60; $j++) {
	$times[$t->format( "%H%M%S" )] = $t->format( LOCALE_TIME_FORMAT );
	$t->addSeconds( 1800 );
}

/*<uenrico>*/
// load locations arrays
$Clocation = new CLocation();

$Clocation->loadCountries();
$Clocation->addItemAtBeginOfCountries($Clocation->addItem(0, "Not Specified"));

$Clocation->loadStates();
$Clocation->addItemAtBeginOfStates($Clocation->addItemState("0","0","Not Specified"));
/*</uenrico>*/


// pull data for this department

$sql = "
SELECT *
FROM users
WHERE user_id = $id
";
if (!db_loadHash( $sql, $drow ) && $id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid HHRR ID', 'hhrr.gif', $m, 'ID_HELP_HHRR_EDIT' );
	$titleBlock->addCrumb( "?m=hhrr", "Human Resources" );	
	if (!(isset($hhrr_portal) && @$hhrr_portal == true))
	$titleBlock->show();
} else {
	if ($id) {
		
		$start_time_am = $drow["start_time_am"] ? new CDate( "0000-00-00 ".$drow["start_time_am"] ) : null;
		$end_time_am = $drow["end_time_am"]  ? new CDate( "0000-00-00 ".$drow["end_time_am"] ) : null;
		$start_time_pm = $drow["start_time_pm"] ? new CDate( "0000-00-00 ".$drow["start_time_pm"] ) : null;
		$end_time_pm = $drow["end_time_pm"] ? new CDate( "0000-00-00 ".$drow["end_time_pm"] ) : null;		
	} else {
		$start_time_am = null;
		$end_time_am = null;
		$start_time_pm = null;
		$end_time_pm = null;
	}	

// setup the title block
	$ttl = $id > 0 ? "Edit HHRR" : "Add HHRR";
	$titleBlock = new CTitleBlock( $ttl, 'hhrr.gif', $m, 'ID_HELP_DEPT_EDIT' );
	if ($canAdd) {
		$titleBlock->addCell();
		$titleBlock->addCell(
		'<input type="submit" class="button" value="'.strtolower($AppUI->_('Add HHRR')).'">', '',
		'<form action="?m=hhrr&a=addedit" method="post">', '</form>'
		);
	}	
	$titleBlock->addCrumb( "?m=hhrr&tab=0", strtolower($AppUI->_('Resources list')) );
  if ($canEdit && $id>0) $titleBlock->addCrumb( "?m=hhrr&a=viewskills&id=$id", strtolower($AppUI->_('View Skills')) );
  if ($canEdit && $id>0) $titleBlock->addCrumb( "?m=hhrr&a=viewhhrr&id=$id", strtolower($AppUI->_('View Personal Information')) );		
  if ($canEdit && $id>0) $titleBlock->addCrumb( "?m=hhrr&a=addedituserskills&id=$id", strtolower($AppUI->_('Edit User Skills')) );  	
	if ($canEdit && $id>0) $titleBlock->addCrumbDelete( 'Delete HHRR', $canDelete, $msg );
		
if (!(isset($hhrr_portal) && @$hhrr_portal == true))	
	$titleBlock->show();

//echo "<pre>"; var_dump($drow);echo "</pre>";	
  $id = $drow["user_id"];
	$username = $drow["user_username"];
	$password = $drow["user_password"];
  $firstname = $drow["user_first_name"];
  $lastname = $drow["user_last_name"];
  $birthday = $drow["user_birthday"];
  $doctype = $drow["doctype"];
  $docnumber = $drow["docnumber"];
  $maritalstate = $drow["maritalstate"];
  $nationality = $drow["nationality"];
  $email = $drow["user_email"];
  $homephone = $drow["user_home_phone"];
  $phone = $drow["user_phone"];
  $cellphone = $drow["user_mobile"];
  $address = $drow["user_address1"];
  $city = $drow["user_city"];
  $zip = $drow["user_zip"];
  $state_id = $drow["user_state_id"];
  //$state = $drow["user_state"];
  $country_id = $drow["user_country_id"];
  //$country = $drow["user_country"];
  $children = $drow["children"];
  $url = $drow["url"];
  $taxidtype = $drow["taxidtype"];
  $taxidnumber = $drow["taxidnumber"];
  $im_type = $drow["user_im_type"];
  $im_id = $drow["user_im_id"];
  $comments = $drow["comments"];
  $resume = $drow["resume"];
  $logo = $drow["user_pic"];
  $inputdate = $drow["date_created"];
  $updateddate = $drow["date_updated"];
  $costperhour = $drow["costperhour"];
  $actualjob = $drow["actualjob"];
  $actualcompany = $drow["actualcompany"];
  $workinghours = $drow["workinghours"];
  $salarywanted = $drow["salarywanted"];
  $wantsfreelance = $drow["wantsfreelance"];
  $wantsfulltime = $drow["wantsfulltime"];
  $wantsparttime = $drow["wantsparttime"];
  $hoursavailableperday = $drow["hoursavailableperday"];	
  $wasinterviewed = $drow["wasinterviewed"];
  $interviewcomments = $drow["interviewcomments"]; 
  $candidatestatus = $drow["candidatestatus"];


  
// format dates
$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$user_birthday = intval( $birthday ) ? new CDate( $birthday ) : null;
$inputdate = new CDate($inputdate);
$inputdate = $inputdate->format($df." ".$tf);

?>
<script language="javascript">
var valid_cv = new Array(<?="'".implode($extfiles_cv, "', '")."'";?>);
var valid_pic = new Array(<?="'".implode($extfiles_pic, "', '")."'";?>);
<? /*
function validar(){
	var form = document.editFrm;var rta = true;
			rta = rta && doCheckFile(valid_cv, form.resume, "Curriculum Vitae");
			rta = rta && doCheckFile(valid_pic, form.photo, "Foto");
			alert(rta);
} */?>


function submitIt() {
	var f = document.editFrm;
	var rta = false;

	var childs = trim(f.children.value) == "" ? 0 : parseInt(trim(f.children.value));
	var docnumber = trim(f.docnumber.value) == "" ? 0 : parseFloat(trim(f.docnumber.value));
	var costph = trim(f.costperhour.value) == "" ? 0 : parseFloat(trim(f.costperhour.value));
	var salaryw = trim(f.salarywanted.value) == "" ? 0 : parseFloat(trim(f.salarywanted.value));
	var hourperday = trim(f.hoursavailableperday.value) == "" ? 0 : parseFloat(trim(f.hoursavailableperday.value));
	var user_username = f.username.value;
    
    
      if(trim(user_username).length < 3){
            alert( "<?=$AppUI->_('Please enter the username')?>" );
			f.username.focus();
	  }else if(trim(f.user_first_name.value).length < 1) {
			alert( "<?=$AppUI->_('Please enter the first name')?>" );
			f.user_first_name.focus();
        } else if (trim(f.user_last_name.value).length < 1) {
			alert( "<?=$AppUI->_('Please enter the last name')?>" );
			f.user_last_name.focus();
		} else if (trim(f.user_password.value).length < 4) {
		    alert("<?php echo $AppUI->_('adminValidPassword');?>");
		    f.user_password.focus();
		} else if (f.user_password.value !=  f.password_check.value) {
		    alert("<?php echo $AppUI->_('adminPasswordsDiffer');?>");
		    f.user_password.focus();			
		}else if (trim(f.user_email.value).length < 0) {
		    alert("<?php echo $AppUI->_('adminInvalidEmail');?>");
		    f.user_email.focus();
		}else if (!isEmail(trim(f.user_email.value))) {
		    alert("<?php echo $AppUI->_('adminInvalidEmail');?>");
		    f.user_email.focus();
		}else if (trim(f.user_phone.value).length < 1) {
		    alert("<?php echo $AppUI->_('adminInvalidfono');?>");
		    f.user_phone.focus();
		}else if (f.user_country_id.value == 0) {
		    alert("<?php echo $AppUI->_('adminInvalidcountry');?>");
		    f.user_country_id.focus();
		}else if (f.user_state_id.value == 0) {
		    alert("<?php echo $AppUI->_('adminInvalidstate');?>");
		    f.user_state_id.focus();
		}else if (isNaN(childs) || childs < 0) {
		    alert("<?php echo $AppUI->_('hhrrInvalidChildren');?>");
		    f.children.focus();
		}else if (isNaN(docnumber) || docnumber < 0) {
		    alert("<?php echo $AppUI->_('hhrrInvalidDocNumber');?>");
		    f.docnumber.focus();
		}else if (isNaN(costph) || costph <0) {
		    alert("<?php echo $AppUI->_('hhrrInvalidCostPerHour');?>");
		    f.costperhour.focus();
		}else if (isNaN(salaryw) || salaryw < 0) {
		    alert("<?php echo $AppUI->_('hhrrInvalidSalaryWanted');?>");
		    f.salarywanted.focus();
		}else if ((!(f.wantsfulltime.checked))&&(!(f.wantsfreelance.checked))&&(!(f.wantsparttime.checked))){
            alert("<?php echo $AppUI->_('hhrrInvalidPreference');?>");
		}else if ((f.wantsfreelance.checked)&&(trim(f.costperhour.value).length < 1)){
			alert("<?php echo $AppUI->_('hhrrInvalidCostPerHour');?>");
			f.costperhour.focus(); 
		}else if ((f.wantsfulltime.checked)&&(trim(f.salarywanted.value).length < 1)){
			alert("<?php echo $AppUI->_('hhrrInvalidSalaryWanted');?>");
			f.salarywanted.focus(); 
		}else if ((f.wantsparttime.checked)&&(trim(f.salarywanted.value).length < 1)){
			alert("<?php echo $AppUI->_('hhrrInvalidSalaryWanted');?>");
			f.salarywanted.focus(); 
		}else if (isNaN(hourperday) || hourperday < 0) {
		    alert("<?php echo $AppUI->_('hhrrInvalidHoursAvPerDay');?>");
		    f.hoursavailableperday.focus();
		}else {
			rta = true;
 <?php    if (!(isset($hhrr_portal) && @$hhrr_portal == true)){ ?>
			rta = rta && validateTimes(f.start_time_am, f.end_time_am);
			rta = rta && validateTimes(f.start_time_pm, f.end_time_pm);
 <?php    } ?>
			rta = rta && doCheckFile(valid_cv, f.resume, "Curriculum Vitae");
			rta = rta && doCheckFile(valid_pic, f.user_pic, "Foto");
		}

		if (rta){
			f.submit();
		}

}

  function changeJobStatus(){
    //divjob.style.display = 'none'; 
    //if(document.editFrm.actualjob.selectedIndex > 1)
	    //divjob.style.display = ''; 

  }

  function image_open(image_loc,img)
  {
    HTML = "<html><style>body{margin:0px 0px 0px 0px}</style><body onBlur='top.close()'><img src='"+ image_loc +"' border=0 name=load_image onLoad='window.resizeTo(document.load_image.width+10,document.load_image.height+30)'></body></html>";
    popupImage = window.open('','_blank','toolbar=no,scrollbars=no');
    popupImage.document.open();
    popupImage.document.write(HTML);
    popupImage.document.close();
  }
	
  function doRemoveFotoChica(){	
		var form = document.delFrm;
		form.do_remove.value="user_pic";
		form.submit();
  }	
  function doRemoveResume(){	
		var form = document.delFrm;
		form.do_remove.value="resume";
		form.submit();
  }	
  
function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.user_' + field + '.value' );
	window.open( '<?php echo $dPconfig[base_url];?>/index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.user_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function validateTimes(start, end)
{
	if 	(start.selectedIndex == 0 && end.selectedIndex == 0)
		return true;
		
	if 	(start.selectedIndex == 0)
	{
			alert("<?=$AppUI->_("No start time selected")?>");
			start.focus();
			return false;
	}
		
	if 	(end.selectedIndex == 0)
	{
			alert("<?=$AppUI->_("No end time selected")?>");
			end.focus();
			return false;
	}	
	
	if 	(start.selectedIndex > end.selectedIndex )
	{
		alert("<?=$AppUI->_("End time must be greater than start time")?>");
		start.focus();
		return false;
	}
	else
	{
		return true;
	}
	
}

function calculateWorkingHours()
{
	var s1 = 0;
	var e1= 0;
	var s2 = 0;
	var e2= 0;
	var f = document.editFrm;
	var st1 = f.start_time_am;
	var ed1 = f.end_time_am;
	var st2 = f.start_time_pm;
	var ed2 = f.end_time_pm;
	
	
	if 	(validateTimes(st1, ed1))
	{
		if (st1.value != "NULL")
		{
			s1 = Date.UTC("1970","01","01",st1.value.substring(0,2), st1.value.substring(2,2));
			e1 = Date.UTC("1970","01","01",ed1.value.substring(0,2), ed1.value.substring(2,2));
		}
	}
	else
	{
		return false;
	}
	if 	(validateTimes(st2, ed2))
	{
		if (st2.value != "NULL")
		{
			s2 = Date.UTC("1970","01","01",st2.value.substring(0,2), st2.value.substring(2,2));
			e2 = Date.UTC("1970","01","01",ed2.value.substring(0,2), ed2.value.substring(2,2));
		}		
	}
	else
	{
		return false;
	}	
	f.daily_working_hours.value = ((e1 - s1) + (e2 - s2) ) / 3600000 ;
}

  function doCheckFile(valid_ext, field,title){	
		var filename = field.value.split(".");
		if (filename){
				var ext = filename[filename.length-1].toLowerCase();
				if (ext=="") {var isvalid = true}
				else{
					var isvalid = false;var i = 0 ;
					while(i<valid_ext.length && !isvalid){
						if (ext == valid_ext[i])
							isvalid=true
						i++;					
					}
				}
		
			if(!isvalid) {
				alert(title+':\n------------------------------\nSeleccionó un archivo: '+ext+'.\n Los tipos de archivo válidos son: \n ' + valid_ext.join(", ")  );
				field.focus();
				return false; 
			}else
				return true;
		}
  }	
<? if (! ($id > 0 )) { ?>
function fillUsername(){

	var f = document.editFrm;
	var username = "";
	username = f.user_first_name.value.toString().substr(0,1);
	username += f.user_last_name.value.toString().substr(0,15);	
  re = / /i;
  username = username.replace(re, "").substr(0,16).toLowerCase();
	f.submit.disabled=true;
	setUsername(username);

}

function check(){
	var f = document.editFrm;
	window.open( 'index.php?m=hhrr&a=checkusername&suppressHeaders=1&callback=setCheckUsername&firstname=' + f.user_first_name.value + '&lastname=' + f.user_last_name.value , 'chkwin', 'top=250,left=250,width=380, height=10, scrollbars=false' );
}

function setCheckUsername(username){
	var f = document.editFrm;
	if (username!=""){
		setUsername(username);
		f.submit.disabled=false;
	}
}
function setUsername(username){
	var f = document.editFrm;
	var d = document.getElementById("txt_username");
	if (f.user_username){
		f.user_username.value = username;	
		d.innerHTML = username; 
	}
}

<? }else{ ?>
function fillUsername(){ }
<? } ?>

   /*
   locations functions
   */
	/*<uenrico>*/
	<?php 
		$Clocation->setFrmName("editFrm");
		$Clocation->setCboCountries("user_country_id");
		$Clocation->setCboStates("user_state_id");
		$Clocation->setJSSelectedState($state_id);
		
		echo $Clocation->generateJS();
		
	?>
/*</uenrico>*/
</script>


<table align="center" border="0" cellpadding="1" cellspacing="1" width="100%" class="std">
<form name="editFrm" action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="user_username" value="<?php echo $username;?>" />
	<input type="hidden" name="dosql" value="do_hhrr_aed" />
	<input type="hidden" name="user_id" value="<?php echo $id;?>" />
	<? 	if ($id >0){
		//obtengo el id de la empresa interna
		$sql = "select company_id from companies where company_type = 0";
		$company_id = db_loadResult($sql);
		?>
	<input type="hidden" name="user_company" value="<?=$company_id?>" />
	
	<?	}	?>
	

	
  <tr>
	    <td align="right"><b><?php echo $AppUI->_('Login');?>:</b></td>
	    <td>
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
					<tr><td width="100%" valign="middle">
					<?
	                    if ($username=="")
	                     {
	                      echo "<input class=\"text\" size=\"30\" type=\"text\" name=\"username\" value=\"$username\">";
						  $dosql = "do_hhrr_add";
						 }
						 else
						 {
                          echo "<input type=\"hidden\" name=\"username\" value=\"$username\"><b><span id=\"txt_username\" style=\"vertical-align: middle\">$username</span></b>";
						 }
	                ?>
					</td>

							<!-- <td align="rigth" valign="middle">
								<? if (!($id > 0)){ ?>
								<br>
								<input class="button" type="button" name="checkuser" value="<?=$AppUI->_("Check")?>" onclick="check()">
								<input class="text" size="16" type="hidden" name="user_username" value="">
								<? } ?>
							</td> -->

					</tr>
					</table>
			</td>

	   <!--  <td colspan="2" align="left">
							<? if (!($id > 0)){ echo $AppUI->_("msgCheckLogin"); } ?>
			</td> -->
	</tr>

	<tr>
	    <td align="right"><b><?php echo $AppUI->_('Password');?>:</b></td>
	    <td nowrap="nowrap"><input type="password" class="text" name="user_password" value="<?php echo $drow["user_password"];?>" maxlength="32" size="32" /> *</td>
	
	    <td align="right"><b><?php echo $AppUI->_('Password');?>2:</b></td>
	    <td nowrap="nowrap"><input type="password" class="text" name="password_check" value="<?php echo $drow["user_password"];?>" maxlength="32" size="32" /> *</td>
	</tr>  
	
    <tr>
    <td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'First Name' );?>:&nbsp;</b></td>
    <td><input class="text" size="35" type="text" name="user_first_name" value="<?= $firstname ?>" > *</td>
    <td rowspan="7" colspan="2" valign="top"><center>
      <? if($logo!="ninguna" && $logo!="" && file_exists($uploads_dir."/".$id."/".$logo)){?>
      <img border="1" height="140" width="140" src="<?=$uploads_dir."/".$id."/".$logo?>"> 
      <? }else echo "<br><br><br><b>Fotografía no<br>disponible</b>"?>
      </center>
    </td>
    </tr>  

    <tr>
    <td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'Last Name' );?>:&nbsp;</b></td>
    <td><input class="text" size="35" type="text" name="user_last_name" value="<?= $lastname ?>" > *</td>
    </tr>  
    <tr>
    <td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'Email' );?>:&nbsp;</b></td>
    <td><input class="text" size="40" type="text" name="user_email" value="<?= $email ?>"> *</td>
    </tr>  
    <tr>
    <td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'Address' );?>:&nbsp;</b></td>
    <td><input class="text" size="30" type="text" name="user_address1" value="<?= $address ?>"></td>
    </tr>  
    <tr>
    <td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'City' );?>:&nbsp;</b></td>
    <td><input class="text" size="25" type="text" name="user_city" value="<?= $city ?>"></td>
    </tr>  
    <tr>
    <td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'ZIP' );?>:&nbsp;</b></td>
    <td><input class="text" size="10" type="text" name="user_zip" value="<?= $zip ?>"></td>
    </tr>  
    <tr>
	<td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'Country' );?>:&nbsp;</b></td>
    <td><?php echo $Clocation->generateHTMLcboCountries($country_id, "text"); ?> *</td>  
    </tr>
    <tr>
    <td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'State' );?>:&nbsp;</b></td>
    <td><?php echo $Clocation->generateHTMLcboStates("", "text"); ?> *</td>
	<td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'Nationality' );?>:&nbsp;</b></td>
    <td><input class="text" size="20" type="text" name="nationality" value="<?= $nationality ?>"></td>    
    </tr>
<?php 
	echo $Clocation->generateJScallFunctions();
?>
    <tr>
    <td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'Phone' );?>:&nbsp;</b></td>
    <td><input class="text" size="30" type="text" name="user_phone" value="<?= $phone ?>"> *</td>
    <td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'Birthday' );?>:&nbsp;</b></td>
    <td nowrap="nowrap" ><input type="text" name="birthday" value="<?php echo $user_birthday ? $user_birthday->format( $df ) : "" ;?>" class="text" disabled="disabled" size="12" />
					<a href="javascript://" onClick="popCalendar('birthday')">
						<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
					</a> 
					<input type="hidden" name="user_birthday" value="<?php echo $user_birthday ? $user_birthday->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />   
    </td>    
    </tr>  
    
    <tr>
    <td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'Home Phone' );?>:&nbsp;</b></td>
    <td><input class="text" size="20" type="text" name="user_home_phone" value="<?= $homephone ?>"></td>
	<td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'Marital State' );?>:&nbsp;</b></td>
	<td>
	<?php echo arraySelect( $MaritalStates, 'maritalstate', 'size="1" class="text"', $maritalstate, true );?>
	</td>
    </tr>  
    
    <tr>
    <td nowrap="nowrap" class="right"><b><?php echo $AppUI->_( 'Cell Phone' );?>:&nbsp;</b></td>
    <td><input class="text" size="30" type="text" name="user_mobile" value="<?= $cellphone ?>"></td>
    <td nowrap="nowrap" class="right" valign="top"><b><?=$AppUI->_("Children");?>:&nbsp;</b></td>
    <td valign="top"><input class="text" size="3" type="text" name="children" value="<?= $children ?>"></td> 
    </tr>

    <tr>   
    <td nowrap="nowrap" class="right"><b><?=$AppUI->_("IM");?>:&nbsp;</b>
	<?php echo arraySelect( $IMTypes, 'user_im_type', 'size="1" class="text"', $im_type, true );?>
	</td>
    <td><input class="text" size="35" type="text" name="user_im_id" value="<?= $im_id ?>"> 
    </td>
    <td nowrap="nowrap" class="right">&nbsp;
    <select name="doctype" class="text">
      <?
        if($doctype=="DNI") echo '<option value="DNI" selected>DNI</option>';
        else                echo '<option value="DNI">DNI</option>';
        if($doctype=="LC")  echo '<option value="LC" selected>LC</option>';
        else                echo '<option value="LC">LC</option>';
        if($doctype=="LE")  echo '<option value="LE" selected>LE</option>';
        else                echo '<option value="LE">LE</option>';
      ?>
    </select>
    </td>
    <td><input class="text" size="15" type="text" name="docnumber" value="<?= $docnumber ?>"></td>
    </tr> 
     
    <tr>
    <td nowrap="nowrap" class="right"><b><?=$AppUI->_("Tax Type");?>:&nbsp;</b></td>
    <td><input class="text" size="30" type="text" name="taxidtype" value="<?= $taxidtype ?>"></td>
    <td nowrap="nowrap" class="right" valign="top"><b><?=$AppUI->_("Tax ID number");?>:&nbsp;</b></td>
    <td valign="top"><input class="text" size="20" type="text" name="taxidnumber" value="<?= $taxidnumber ?>"></td>
    </tr>  
    
    <tr>
    <td nowrap="nowrap" rowspan="2" class="right" valign="top"><b><?=$AppUI->_("Comments");?>:&nbsp;</b></td>
    <td rowspan="2"><textarea class="textarea" rows="6" cols="45" name="comments" wrap="virtual"><?= $comments ?></textarea></td>
 <?php
    if (!(isset($hhrr_portal) && @$hhrr_portal == true)){ ?>
    
    <td align="right" valign=top><b><?php echo $AppUI->_('Work schedule');?>:</b></td>
	<td valign="top">
		<table border="0">
		<tr>
			<td>&nbsp;</td>
			<td align="center"><b><?=$AppUI->_('Start')?></b></td>
			<td align="center"><b><?=$AppUI->_('End')?></b></td>
		</tr>
		<tr>
			<td><b>AM</b></td>
			<td align="center"><?=arraySelect( $times, 'start_time_am', 'size="1" class="text"',
			$start_time_am ? $start_time_am->format("%H%M%S") : $times["NULL"] )?></td>
			<td align="center"><?=arraySelect( $times, 'end_time_am', 'size="1" class="text"',
			$end_time_am ? $end_time_am->format("%H%M%S") : $times["NULL"] )?></td>
		</tr>
		<tr>
			<td><b>PM</b></td>
			<td align="center"><?=arraySelect( $times, 'start_time_pm', 'size="1" class="text"',
			$start_time_pm ? $start_time_pm->format("%H%M%S") : $times["NULL"] )?></td>
			<td align="center"><?=arraySelect( $times, 'end_time_pm', 'size="1" class="text"',
			$end_time_pm ? $end_time_pm->format("%H%M%S") : $times["NULL"] )?></td>		
		</tr>	
		</table>	
	</td>
    </tr>
    
	<tr>
		<td align="right" valign=top><b><?php echo $AppUI->_('Daily working hours');?>:<//b></td>
		<td valign="top"><input class="text" type="text" name="daily_working_hours" value="<?=$drow["daily_working_hours"]?>" size="4" maxlength="4">&nbsp;<input class="text" type="button" value="<?=$AppUI->_('Calculate')?>"onclick="calculateWorkingHours();">
		</td>
	
	</tr> 
<?php }else{ echo "<td colspan=2>&nbsp;</td></tr><tr><td colspan=2>&nbsp;</td></tr>"; } ?>
    <tr>
    <td nowrap="nowrap" class="right"><b><?=$AppUI->_("Curriculum Vitae");?>:&nbsp;</b></td>
    <td colspan="3"><input class="text" type="file" name="resume">
    <?php
      if($resume<>"ninguna" && $resume!="" && file_exists($uploads_dir."/".$id."/".$resume)) { 
        echo "&nbsp;Actual: <b><a class='link1'  href='$uploads_dir/$id/".rawurlencode($resume)."'>$resume</a>";
        echo '</b>&nbsp;<input class="text" type="button" value="Quitar" onclick="doRemoveResume()">';
      }
    ?>
    </td>
    </tr>  

    <tr>
    <td nowrap="nowrap" class="right"><b><?=$AppUI->_("Photo");?> (140x140):&nbsp;</b></td>
    <td colspan="3"><input class="text" type="file" name="user_pic">
    <?php
      if($logo<>"ninguna" && $logo!="" && file_exists($uploads_dir."/".$id."/".$logo)) { 
        echo "&nbsp;Actual: <b><a class='link1'  href='javascript:image_open(\"".
        	$AppUI->getConfig("base_url")."/$uploads_dir/$id/".rawurlencode($logo)."\", \"img1\")'>$logo</a>";
        echo '</b>&nbsp;<input class="text" type="button" value="Quitar" onclick="doRemoveFotoChica()">';
      }
    ?>
    </td>
    </tr>
      
    <tr>
    <td class="right" nowrap="nowrap" colspan="4">
    &nbsp;
    </td>
    </tr>
    
     <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Actual Company')?>:&nbsp;</b></td>
    <td><input class="text" size="32" type="text" name="actualcompany" value="<?= $actualcompany ?>"></td>    
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Actual job')?>:&nbsp;</b></td>
    <td><?php echo arraySelect( $WorkTypes, 'actualjob', 'size="1" class="text"', $actualjob, true );?>
<? /*    
    <select name="actualjob" onChange="changeJobStatus();">
			<option <?if($actualjob=="")echo "selected";?>         value=""><?php echo $AppUI->_( '-- Select here --' );?></option>
			<option <?if($actualjob=="No trabajando")echo "selected";?>         value="No trabajando"><?php echo $AppUI->_( 'Not working' );?></option>
			<option <?if($actualjob=="Trabajando Full Time")echo "selected";?>  value="Trabajando Full Time"><?php echo $AppUI->_( 'Working Full Time' );?></option>
			<option <?if($actualjob=="Trabajando Part Time")echo "selected";?>  value="Trabajando Part Time"><?php echo $AppUI->_( 'Working Part Time' );?></option>
			<option <?if($actualjob=="Trabajando Free Lance")echo "selected";?> value="Trabajando Free Lance"><?php echo $AppUI->_( 'Working Free Lance' );?></option>    
    </select>
*/ ?>    
    </td> 
    </tr>  
    
     <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Actual working hours')?>:&nbsp;</b></td>
    <td><input class="text" size="32" type="text" name="workinghours" value="<?= $workinghours ?>"></td>
    <td class="right" nowrap="nowrap"><b><?=$AppUI->_('Diary avalability')?>:&nbsp;</b></td>
    <td colspan="3"><input class="text" type="text" size="4" name="hoursavailableperday" value="<?=$hoursavailableperday?>"></td>     
    </tr>
       
    <tr>
    <td class="right" nowrap="nowrap"><b><?=$AppUI->_('Cost per hour wanted')?>&nbsp;($):&nbsp;</b></td>
    <td ><input class="text" type="text" size="4" name="costperhour" value="<?=$costperhour?>"> * (Freelance)
    </td>
    
    <td class="right" rowspan="3" nowrap="nowrap" valign="top"><b><?php echo $AppUI->_('Work preference')?>:&nbsp;</b></td>
    <td rowspan="3" >
	<input class="text" type="checkbox"  name="wantsfulltime" value="1" <?if($wantsfulltime==1){echo "checked";}?> >	<?=$AppUI->_('Wants full time')?> <br />
	<input class="text" type="checkbox"  name="wantsparttime" value ="1" <?if($wantsparttime==1){echo "checked";}?> > <?=$AppUI->_('Wants part time')?> <br />
	<input class="text" type="checkbox"  name="wantsfreelance" value="1" <?if($wantsfreelance==1){echo "checked";}?> > <?=$AppUI->_('Wants freelance')?>
    </td>
    </tr>  
    <tr>
    <td class="right" nowrap="nowrap"><b><?=$AppUI->_('Salary wanted')?>&nbsp;($):&nbsp;</b></td>
    <td ><input class="text" type="text" size="8" name="salarywanted" value="<?=$salarywanted?>"> * (Full time)
    </tr>     
 <?php
    if (!(isset($hhrr_portal) && @$hhrr_portal == true)){ ?>
    <tr>
    <td class="right" nowrap="nowrap"><b><?=$AppUI->_('Was interviewed')?>:&nbsp;</b></td>
    <td ><input class="text" type="checkbox"  name="wasinterviewed" <? if($wasinterviewed==1)echo "checked"?>>
    </tr>  
      
    <tr>
    <td class="right" nowrap="nowrap" valign="top"><b><?=$AppUI->_('Interview comments')?>:&nbsp;</b></td>
    <td colspan="3"><textarea class="textarea" rows="6" cols="45" name="interviewcomments" wrap="virtual"><?=$interviewcomments ?></textarea>    
    </tr>
    <?php
    	if ( $canEditModule ){
    ?>
 	<tr>
    <td class="right" nowrap="nowrap"><b><?=$AppUI->_('Candidate Status')?>:&nbsp;</b></td>
    <td colspan="3"><?php echo arraySelect( $SCandidateStatus, 'candidatestatus', 'size="1" class="text"', true, true );?></td>    
    <!-- <td colspan="3"><?php echo arraySelect( $SCandidateStatus, 'candidatestatus', 'size="1" class="text"', $candidatestatus, true );?></td>     -->
    </tr>
    <?php
    	}
    }else 
    	echo "<tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr>";
    ?>
    <tr>
    <td nowrap="nowrap" class="right" colspan="4">
      <b>Registro ingresado el:&nbsp;</b>&nbsp;<?= $inputdate ?>&nbsp;&nbsp;
    </td>
    </tr>
    
	<tr>
		<td><br>
			<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
		</td>
		<td colspan="3" align="right">
			<input type="button" value="<?php echo $AppUI->_( 'submit' );?>" name="send" class="button"  onclick="submitIt()"/>
			<? /*<input class="button" type="button" value="validar" onclick="validar()">*/ ?>
		</td>
	</tr>    
</form>	
	</table>
<script language="javascript">
changeJobStatus();
</script>	
<form name="delFrm" action="" method="post" enctype="multipart/form-data">
	<input type="hidden" name="dosql" value="do_hhrr_aed" />
	<input type="hidden" name="user_id" value="<?php echo $id;?>" />
	<input type="hidden" name="do_remove" value="" />
</form>
<?php } ?>
