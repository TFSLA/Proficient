<?php /* hhrr $Id: addedit_personal.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
// Add / Edit Info personal del rrhh

global $AppUI,$utypes, $hhrr_portal, $xajax;
require_once("./functions/admin_func.php");

define("NOTE_INTERVIEW", 1); 
define("NOTE_INTERNAL", 2);

$AppUI->savePlace();

// Saco el campo vacio del vector de tipo de usuarios
unset($utypes[0]);

$id = isset($_GET['id']) ? $_GET['id'] : 0;
if($id =="")
{
$id = '0';
}

if($_GET['e']!='1')
{
$AppUI->post = "";
}
//echo "<pre>";print_r($AppUI); echo "</pre>";

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

if ($AppUI->user_id == $_GET['id'])
{
$canEdit = '1';
}
					

// check permissions
if (!$canEdit && !$hhrr_portal) {
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
	$titleBlock = new CTitleBlock( $ttl, 'hhrr.gif', $m, 'hhrr.index' );
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
	//$titleBlock->show();

//echo "<pre>"; var_dump($drow);echo "</pre>";	
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
  $country_id = $drow["user_country_id"];
  $children = $drow["children"];
  $url = $drow["url"];
  $taxidtype = $drow["taxidtype"];
  $taxidnumber = $drow["taxidnumber"];
  $im_type = $drow["user_im_type"];
  $im_id = $drow["user_im_id"];
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
  $candidatestatus = $drow["candidatestatus"];
  $department = $drow["user_department"];
  $company = $drow["user_company"];
  $position = $drow["user_job_title"];
  $user_type = $drow["user_type"];
  $legajo = $drow["legajo"];
  $user_supervisor = $drow["user_supervisor"];
  $input_date_company = $drow["user_input_date_company"];
    
  if($user_type =="")
  {
	  if ($_GET[candidate]=='1')
	  {
	  $user_type = "5";
	  }
	  else{
	  $user_type = "2";
	  }
  }
  

// Si viene desde el portal de candidatos el tipo de usuario es candidato
if ($hhrr_portal) 
{ 
$username = $drow["user_username"];
$user_type = '5';
}

if($AppUI->post!="" && !$hhrr_portal)
{
  $username = $AppUI->post['user_username'];
  $password = $AppUI->post["user_password"];
  $firstname = $AppUI->post["user_first_name"];
  $lastname = $AppUI->post["user_last_name"];
  $birthday = $AppUI->post["user_birthday"];
  $doctype = $AppUI->post["doctype"];
  $docnumber = $AppUI->post["docnumber"];
  $maritalstate = $AppUI->post["maritalstate"];
  $nationality = $AppUI->post["nationality"];
  $email = $AppUI->post["user_email"];
  $homephone = $AppUI->post["user_home_phone"];
  $phone = $AppUI->post["user_phone"];
  $cellphone = $AppUI->post["user_mobile"];
  $address = $AppUI->post["user_address1"];
  $city = $AppUI->post["user_city"];
  $zip = $AppUI->post["user_zip"];
  $state_id = $AppUI->post["user_state_id"];
  $country_id = $AppUI->post["user_country_id"];
  $children = $AppUI->post["children"];
  $url = $AppUI->post["url"];
  $taxidtype = $AppUI->post["taxidtype"];
  $taxidnumber = $AppUI->post["taxidnumber"];
  $im_type = $AppUI->post["user_im_type"];
  $im_id = $AppUI->post["user_im_id"];
  $comments = $AppUI->post["comments"];
  $resume = $AppUI->post["resume"];
  $logo = $AppUI->post["user_pic"];
  $inputdate = $AppUI->post["date_created"];
  $updateddate = $AppUI->post["date_updated"];
  $costperhour = $AppUI->post["costperhour"];
  $actualjob = $AppUI->post["actualjob"];
  $actualcompany = $AppUI->post["actualcompany"];
  $workinghours = $AppUI->post["workinghours"];
  $salarywanted = $AppUI->post["salarywanted"];
  $wantsfreelance = $AppUI->post["wantsfreelance"];
  $wantsfulltime = $AppUI->post["wantsfulltime"];
  $wantsparttime = $AppUI->post["wantsparttime"];
  $hoursavailableperday = $AppUI->post["hoursavailableperday"];	
  $wasinterviewed = $AppUI->post["wasinterviewed"];
  $interviewcomments = $AppUI->post["interviewcomments"]; 
  $candidatestatus = $AppUI->post["candidatestatus"];
  $department = $AppUI->post["user_department"];
  $company = $AppUI->post["user_company"];
  $position = $AppUI->post["user_job_title"];
  $user_type = $AppUI->post["user_type"];
  $legajo = $AppUI->post["legajo"];
  $user_supervisor = $AppUI->post["user_supervisor"];
  $input_date_company = $AppUI->post["user_input_date_company"];
  $company_supervisor = $AppUI->post["company_supervisor"];
}

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$user_birthday = intval( $birthday ) ? new CDate( $birthday ) : null;
$user_input_date_company = intval( $input_date_company ) ? new CDate( $input_date_company ) : null;

$inputdate = new CDate($inputdate);
$inputdate = $inputdate->format($df." ");

// Traigo las empresas que tienen departamento
$sql_dept = db_exec("SELECT distinct(dept_company) FROM departments WHERE dept_company > '0' ");

$strJS_dept = "var arDept = new Array();\n";

 while ($vec_dept = mysql_fetch_array($sql_dept))
 {
 $strJS_dept .= "arDept[arDept.length] = new Array('".$vec_dept[dept_company]."');\n";
 }


if ( isset($_POST['hhrr_note_id']) )
{
	$sql= "SELECT * FROM hhrr_notes WHERE hhrr_note_id = ".$_POST['hhrr_note_id'];
	db_loadHash($sql, $edited_hhrr_note);
}

?>

<script language="javascript">
var valid_cv = new Array(<?="'".implode($extfiles_cv, "', '")."'";?>);
var valid_pic = new Array(<?="'".implode($extfiles_pic, "', '")."'";?>);

function submitIt() {
	var f = document.editFrm;
	var rta = false;
	
    var today = new Date();
    
    var vec_fecha = f.birthday.value.split("/");
    var birthday_txt = new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
    
    var vec_fecha = f.input_date_company.value.split("/");
    var input_date_company_txt = new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
	
	var childs = trim(f.children.value) == "" ? 0 : parseInt(trim(f.children.value));
	var docnumber = trim(f.docnumber.value) == "" ? 0 : parseFloat(trim(f.docnumber.value));
	var costph = trim(f.costperhour.value) == "" ? 0 : parseFloat(trim(f.costperhour.value));
	var salaryw = trim(f.salarywanted.value) == "" ? 0 : parseFloat(trim(f.salarywanted.value));
	var hourperday = trim(f.hoursavailableperday.value) == "" ? 0 : parseFloat(trim(f.hoursavailableperday.value));
	var user_username = f.username.value;
    var val_dept = false;

	<?=$strJS_dept;?>
       
	   for(var h = 0; h < arDept.length; h++){
			if (f.company.value == arDept[h][0])
		    {
			val_dept = true;
			h = arDept.length;
	        }
	   }
       
       
       if(trim(user_username).length < 3 && f.user_type.value == '5'){
            alert1( "<?=$AppUI->_('Please enter the username')?>" );
			f.username.focus();
	  }else if(trim(f.user_first_name.value).length < 1) {
			alert1( "<?=$AppUI->_('Please enter the first name')?>" );
			f.user_first_name.focus();
        } else if (trim(f.user_last_name.value).length < 1) {
			alert1( "<?=$AppUI->_('Please enter the last name')?>" );
			f.user_last_name.focus();	
		} else if (trim(f.user_password.value).length < 4 && f.user_type.value == '5') {
		    alert1("<?php echo $AppUI->_('adminValidPassword');?>");
		    f.user_password.focus();
		} else if (f.user_password.value !=  f.password_check.value && f.user_type.value == '5') {
		    alert1("<?php echo $AppUI->_('adminPasswordsDiffer');?>");
		    f.user_password.focus();
        }else if (f.user_type.value != '5' && f.user_supervisor.value =='0'){
           alert("<?php echo $AppUI->_('adminValidDirectReport');?>");
		}else if (trim(f.user_email.value).length < 0) {
		    alert1("<?php echo $AppUI->_('adminInvalidEmail');?>");
		    f.user_email.focus();
		}else if (!isEmail(trim(f.user_email.value))) {
		    alert1("<?php echo $AppUI->_('adminInvalidEmail');?>");
		    f.user_email.focus();
		}else if (trim(f.user_phone.value).length < 1) {
		    alert1("<?php echo $AppUI->_('adminInvalidfono');?>");
		    f.user_phone.focus();
		}else if (f.user_country_id.value == 0) {
		    alert1("<?php echo $AppUI->_('adminInvalidcountry');?>");
		    f.user_country_id.focus();
		}else if (f.user_state_id.value == 0) {
		    alert1("<?php echo $AppUI->_('adminInvalidstate');?>");
		    f.user_state_id.focus();
		}else if (f.user_birthday.value != "" && birthday_txt > today ){
            alert1("<?php echo $AppUI->_('DateErrorMayor_user_birthday');?>");
		}else if (isNaN(childs) || childs < 0) {
		    alert1("<?php echo $AppUI->_('hhrrInvalidChildren');?>");
		    f.children.focus();
		}else if (isNaN(docnumber) || docnumber < 0) {
		    alert1("<?php echo $AppUI->_('hhrrInvalidDocNumber');?>");
		    f.docnumber.focus();
		}else if (isNaN(costph) || costph <0) {
		    alert1("<?php echo $AppUI->_('hhrrInvalidCostPerHour');?>");
		    f.costperhour.focus();
		}else if ((!(f.wantsfulltime.checked))&&(!(f.wantsfreelance.checked))&&(!(f.wantsparttime.checked) && f.user_type.value == '5')){
            alert1("<?php echo $AppUI->_('hhrrInvalidPreference');?>");
		}else if ((f.wantsfreelance.checked)&&(  f.costperhour.value  < 1) && f.user_type.value == '5'){
			alert1("<?php echo $AppUI->_('hhrrInvalidCostPerHour');?>");
			f.costperhour.focus(); 
		}else if ((f.wantsfulltime.checked)&&( f.salarywanted.value < 1) && f.user_type.value == '5'){
			alert1("<?php echo $AppUI->_('hhrrInvalidSalaryWanted');?>");
			f.salarywanted.focus(); 
		}else if ((f.wantsparttime.checked)&&( f.salarywanted.value < 1) && f.user_type.value == '5'){
			alert1("<?php echo $AppUI->_('hhrrInvalidSalaryWanted');?>");
			f.salarywanted.focus(); 
		}else if ((isNaN(hourperday) || hourperday < 0) && f.user_type.value == '5') {
		    alert1("<?php echo $AppUI->_('hhrrInvalidHoursAvPerDay');?>");
		    f.hoursavailableperday.focus();
		}else if (f.input_date_company.value != ""  && input_date_company_txt > today ){
            alert1("<?php echo $AppUI->_('DateErrorMayor_input_date_company');?>");
		}else if ( f.company.value == "" && f.user_type.value != '5') {
		    alert1("<?php echo $AppUI->_('hhrrInvalidcompany');?>");
		    f.company.focus();
		}
		<?php 	if (($AppUI->user_id != $_GET[id]) ){ 	?> 
           else if ((f.user_supervisor.value =='0' || f.user_supervisor.value =='' )&& f.user_type.value != '5')
		   {
           alert("<?php echo $AppUI->_('adminValidDirectReport');?>");
           } 
        <?php } ?>
		else if ( val_dept && (f.department.value == "" || f.department.value == "0" || f.department.value == "-1") && f.user_type.value != '5') 
		{
		    alert1("<?php echo $AppUI->_('hhrrInvalidepartement');?>");
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
  

  function change_cia(cia){

	var f = document.editFrm;

	if (cia.value != f.company_or.value )
	{
	f.department.value = '-1';
	//f.dept_name.value = "<?=$AppUI->_('All');?>";
	}else{
	f.department.value = f.department_or.value;
	//f.dept_name.value = f.dept_name_or.value;
	}

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
		form.dosql.value="do_hhrr_aed";
		form.submit();
  }	
  function doRemoveResume(){	
		var form = document.delFrm;
		form.do_remove.value="resume";
		form.dosql.value="do_hhrr_aed";
		form.submit();
  }	
  
function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.user_' + field + '.value' );
	window.open( './index.php?a=calendar&m=public&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
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
			alert1("<?=$AppUI->_("No start time selected")?>");
			start.focus();
			return false;
	}
		
	if 	(end.selectedIndex == 0)
	{
			alert1("<?=$AppUI->_("No end time selected")?>");
			end.focus();
			return false;
	}	
	
	if 	(start.selectedIndex > end.selectedIndex )
	{
		alert1("<?=$AppUI->_("End time must be greater than start time")?>");
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
				alert1(title+':\n------------------------------\nSeleccion? un archivo: '+ext+'.\n Los tipos de archivo v?lidos son: \n ' + valid_ext.join(", ")  );
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

function changeUsertype(type)
{   
	// 5 = candidato
	if(type.value == "5")
	{
	   document.getElementById("candidatediv0").style.display = '';
       document.getElementById("candidatediv3").style.display = '';
	   document.getElementById("employdiv0").style.display = 'none';
	   document.getElementById("employdiv1").style.display = '';

	}else{
	   document.getElementById("candidatediv0").style.display = 'none';
	   document.getElementById("candidatediv3").style.display = 'none';
	   document.getElementById("employdiv0").style.display = '';
	   document.getElementById("employdiv1").style.display = 'none';
	}
}

function popDept() {
    var f = document.editFrm;
    if (f.company.selectedIndex == 0) {
        alert1("<?=$AppUI->_('Please select a company first!')?>");
    } else {
        window.open('./index.php?m=public&a=selector&dialog=1&suppressLogo=1&callback=setDept&table=departments&company_id='
            + f.company.options[f.company.selectedIndex].value
            + '&dept_id='+f.department.value,'dept','left=50,top=50,height=250,width=400,resizable')
    }
}

// Callback function for the generic selector
function setDept( key, val ) {
    var f = document.editFrm;
    if (val != '') {
        f.department.value = key;
        f.dept_name.value = val;
    } else {
        f.department.value = '0';
        f.dept_name.value = '';
    }
}

function popUserSupervisor() {
    var f = document.editFrm;
   
	if (f.company.selectedIndex == 0) {
        alert1("<?=$AppUI->_('Please select a company first!')?>");
    }
    else{      		  	  
	       window.open('./index.php?m=public&a=selector&dialog=1&suppressLogo=1&callback=setUserSupervisor&id=<?=$_GET[id]?>&table=user_supervisor&company_id='
            + f.company.options[f.company.selectedIndex].value
            + '&user_supervisor='+f.user_supervisor.value
            + '&dept_id='+f.department.value,'user_supervisor','left=50,top=50,height=250,width=400,resizable')
    }
}

function setUserSupervisor( key, val ) {
    var f = document.editFrm;
    if (val != '') {
        f.user_supervisor.value = key;
        f.user_supervisor_name.value = val;
    } else {
        f.user_supervisor.value = '0';
        f.user_supervisor_name.value = '';
    }
}

function submitNote(noteType){
	var f = document.editFrm;
	
	if (noteType==2 && f.comments.value.length == 0)
	{
		alert1("<?=$AppUI->_('commentEmpty')?>");
		f.comments.focus();
		return;	
	}

	if (noteType==1 && f.interviewcomments.value.length == 0)
	{
		alert1("<?=$AppUI->_('commentEmpty')?>");
		f.interviewcomments.focus();
		return;	
	}
	
	if (f.hhrr_note_id.value != 0)
	{
		f.accion.value = "edit_hhrr_note";
	}
	else
		f.accion.value = "new_hhrr_note";
		
	f.noteType.value = noteType;
	f.dosql.value="do_note_aed";
	f.submit();
}

function confirma(obj){
  
   var f = document.delFrm;
   f.hhrr_note_id.value = obj;

   var borrar=confirm("<?=$AppUI->_("delete_reg")?>");

  if (borrar)
	{
    f.submit();
	}
}

function edit_note(hhrr_note_id){  
   var f = document.edit_note;
   f.hhrr_note_id.value = hhrr_note_id;
 
   f.submit();
}

function select_ajax(company, department)
{  
   xajax_addSelect_Departments('department', company ,department, 'hhrr', 'personal', '<?=$AppUI->_('All')?>');	
   
}

function select_ajax_supervisor(company, supervisor)
{
	xajax_combo_UserSupervisor('user_supervisor', company, supervisor, 'direct_report');
}

</script>


<form name="delFrm" action="" method="POST">
	<input type="hidden" name="hhrr_note_id" value="" />
	<input type="hidden" name="accion" value="del_hhrr_note" />
	<input type="hidden" name="dosql" value="do_note_aed" />
	<input type="hidden" name="do_remove" value="" />
	<input type="hidden" name="user_id" value="<?php echo $id;?>" />
</form>

<form name="edit_note" action="" method="POST">
	<input type="hidden" name="hhrr_note_id" value="" />
	<input type="hidden" name="accion" value="edit_hhrr_note" />
</form>



<table align="center" border="0" cellpadding="1" cellspacing="1" width="100%" class="std">
<form name="editFrm" action="" method="post" enctype="multipart/form-data">
  <input type="hidden" name="user_username" value="<?php echo $username;?>" />
	<input type="hidden" name="dosql" value="do_hhrr_aed" />
	<input type="hidden" name="accion" value="" />
	<input type="hidden" name="user_id" value="<?php echo $id;?>" />
	<input type="hidden" name="noteType" value="0" />
	<input type="hidden" name="hhrr_note_id" value="<?= (isset($_POST['hhrr_note_id'])) ? $_POST['hhrr_note_id']: 0;  ?>" />
	
	<? 	if ($id >0){
		//obtengo el id de la empresa interna
		$sql = "select company_id from companies where company_type = 0";
		$company_id = db_loadResult($sql);
		?>
	<input type="hidden" name="user_company" value="<?=$company_id?>" />
	
	<?	}	?>
	

  <tr>
    <td>
    
	    <table border='0'>
		 <tr>
		   <td nowrap="nowrap" class="right">
		     <b><?php echo $AppUI->_( 'First Name' );?>:&nbsp;</b>
		   </td>
           <td nowrap="nowrap">
		     <input class="text" size="25" type="text" name="user_first_name" value="<?= $firstname ?>" > *
		   </td>
          </tr>

		  <tr>
		   <td nowrap="nowrap" class="right">
			 <b><?php echo $AppUI->_( 'Last Name' );?>:&nbsp;</b>
		   </td>
           <td>
		     <input class="text" size="25" type="text" name="user_last_name" value="<?= $lastname ?>" > *
		   </td>
		  </tr>

      <tr>
		  <td nowrap="nowrap" class="right">
			  <b><?php echo $AppUI->_( 'Phone' );?>:&nbsp;</b>
			</td>
			<td>
			  <input class="text" size="25" type="text" name="user_phone" value="<?= $phone ?>"> *
			</td>    
		  </tr>

		  <tr>
		   <td nowrap="nowrap" class="right">
		     <b><?php echo $AppUI->_( 'Email' );?>:&nbsp;</b>
		   </td>
           <td>
		     <input class="text" size="25" type="text" name="user_email" value="<?= $email ?>"> * 
		   </td>
		  </tr>

		  <tr>
		   <td nowrap="nowrap" class="right">
			 <b><?php echo $AppUI->_( 'Address' );?>:&nbsp;</b>
		   </td>
           <td>
			 <input class="text" size="25" type="text" name="user_address1" value="<?= $address ?>">
		   </td>
		  </tr>

          <tr>
           <td nowrap="nowrap" class="right">
		     <b><?php echo $AppUI->_( 'City' );?>:&nbsp;</b>
		   </td>
           <td>
		     <input class="text" size="25" type="text" name="user_city" value="<?= $city ?>">
		   </td>
          </tr>  

          <tr>
           <td nowrap="nowrap" class="right">
		    <b><?php echo $AppUI->_( 'ZIP' );?>:&nbsp;</b>
		   </td>
           <td>
		    <input class="text" size="10" type="text" name="user_zip" value="<?= $zip ?>">
		   </td>
          </tr>  
          
		  <tr>
	       <td nowrap="nowrap" class="right">
		     <b><?php echo $AppUI->_( 'Country' );?>:&nbsp;</b>
		   </td>
           <td>
		     <?php echo $Clocation->generateHTMLcboCountries($country_id, "text"); ?> *
		   </td>  
          </tr>

          <tr>
           <td nowrap="nowrap" class="right">
		     <b><?php echo $AppUI->_( 'State' );?>:&nbsp;</b>
		   </td>
           <td>
		     <?php echo $Clocation->generateHTMLcboStates("", "text"); ?> *
		   </td>
          </tr>

		 <?php 
			echo $Clocation->generateJScallFunctions();
		 ?>
		  <tr>
		    <td nowrap="nowrap" class="right">
			  <b><?php echo $AppUI->_( 'Nationality' );?>:&nbsp;</b>
			</td>
            <td>
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
					  if ($vec[nationality_id]==$nationality){
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

		  <tr>
		    <td nowrap="nowrap" class="right">
			  <b><?php echo $AppUI->_( 'Birthday' );?>:&nbsp;</b>
			</td>
	      <td nowrap="nowrap" >
					<input type="text" name="birthday" value="<?php echo $user_birthday ? $user_birthday->format( $df ) : "" ;?>" class="text" disabled="disabled" size="12" />
						<a href="javascript://" onClick="popCalendar('birthday')">
							<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
						</a> 
				  <input type="hidden" name="user_birthday" value="<?php echo $user_birthday ? $user_birthday->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />   
      	</td>
		  </tr>		 
		  <tr>
		    <td nowrap="nowrap" class="right">
			  <b><?php echo $AppUI->_( 'Home Phone' );?>:&nbsp;</b>
			</td>
		    <td>
			  <input class="text" size="25" type="text" name="user_home_phone" value="<?= $homephone ?>">
			</td>
		  </tr>  

		  <tr>
		    <td nowrap="nowrap" class="right">
			  <b><?php echo $AppUI->_( 'Cell Phone' );?>:&nbsp;</b>
			</td>
		    <td>
			  <input class="text" size="25" type="text" name="user_mobile" value="<?= $cellphone; ?>">
			</td>
		  </tr>  

		  <tr>
		    <td nowrap="nowrap" class="right">
			  <b><?=$AppUI->_("IM");?>:&nbsp;</b>
        	  <?php echo arraySelect( $IMTypes, 'user_im_type', 'size="1" class="text"', $im_type, true,false,'60px' );?>
	        </td>
            <td>
			  <input class="text" size="25" type="text" name="user_im_id" value="<?= $im_id ?>"> 
            </td>
		  </tr>

		  <tr>
		   <td nowrap="nowrap" class="right">
		     <b><?=$AppUI->_("Marital State");?>:&nbsp;</b>
		   </td>
	       <td>
	         <?php echo arraySelect( $MaritalStates, 'maritalstate', 'size="1" class="text"', $maritalstate, true );?>
	       </td>
		  </tr>

		  <tr>
		    <td nowrap="nowrap" class="right" valign="top">
			  <b><?=$AppUI->_("Children");?>:&nbsp;</b>
			</td>
            <td valign="top">
			  <input class="text" size="3" type="text" name="children" value="<?= $children ?>">
			</td>
		  </tr>

		  <tr>
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
           <td>
		     <input class="text" size="25" type="text" name="docnumber" value="<?= $docnumber ?>">
		  </td>
		  </tr>

		  <tr>
			<td nowrap="nowrap" class="right" valign="top"><b><?=$AppUI->_("Tax ID number");?>:&nbsp;</b></td>
			<td valign="top"><input class="text" size="25" type="text" name="taxidnumber" value="<?= $taxidnumber ?>"></td>
		  </tr>

		  <tr>
			<td nowrap="nowrap" class="right">
			 <b><?=$AppUI->_("Tax Type");?>:&nbsp;</b>
			</td>
			<td>
			  <input class="text" size="25" type="text" name="taxidtype" value="<?= $taxidtype ?>">
			</td>
		  </tr>
		 
		</table>
  <!-- Fin de la primera columna -->
	  
	</td>
	
	<td valign="top" width="250">
	    <!-- Segunda columna para empleados -->

	   <?
		   // Me fijo si el usuario que edita es admin
       $sql = db_exec("SELECT user_type FROM users WHERE user_id = '".$AppUI->user_id."' ");
		   $data_admin = mysql_fetch_array($sql);
		   $u_admin = $data_admin[0];
	      
	   ?>


		<div id="employdiv0" name="employdiv0" <? if($user_type!=5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?> >
		<table border="0" width="300">
			<tr>
				<td class="right" nowrap="nowrap" width="150">
		         <b><?php echo $AppUI->_('Company')?>:&nbsp;</b>
		       </td>
		       <td>
			     <input type = "hidden" name = "company_or" value = "<?=$company;?>" >
			     
				 <select name="company" class="text" style="width:160px;" onchange="select_ajax(document.editFrm.company.value, '')" <? if($u_admin!='1' && $id >0 ){ echo "disabled"; }?> >
				    <option value="" ><?php echo $AppUI->_('Not Specified')?></option>
					<?
					$query = "SELECT company_name, company_id FROM companies ORDER BY company_name ASC"; 
					$sql = mysql_query($query);
					 
					while($vec = mysql_fetch_array($sql) )
					{
					 $selected = ($vec['company_id']==$company) ? "selected" : "";
					  echo "<option " .$selected ." value=\"$vec[company_id]\">$vec[company_name]</option>";
					}
					?>
		          </select>*
				</td>
			</tr>
			<tr>
			  <td class="right">
				<b><?php echo $AppUI->_('Department')?>:&nbsp;</b>
			  </td>
			  <td>
			    <?
				    if($department == "")
					  {
					   $department = "-1";
					  }
					  
				?>
			 <script type="text/javascript">
			   select_ajax('<?=$company?>','<?=$department?>');	
			 </script>
             <input type="hidden" name="department_or" value="<?=$department;?>" />
			 <!-- <input type="hidden" name="department" value="<?=$department;?>" /> -->
			 
			 <select name="department" id="department" class="text" style="width:160px;"  <? if($u_admin!='1' && $id >0 ){ echo "disabled"; }?> >
			 </select> *
			  </td>
			</tr>
			<tr>
			   <td align="right">
			   	<b><?php echo $AppUI->_('Direct report')?>:&nbsp;</b>
			   </td>
			   <td>
			   
			 <? 
			    if ($user_supervisor != "" && $user_supervisor!= '0' && $company_supervisor =="")
			    {
	                $query_sup = "SELECT user_company FROM users WHERE user_id='".$user_supervisor."' "; 
					$sql_sup = mysql_query($query_sup);	
					$c_s = mysql_fetch_array($sql_sup);	
					$company_supervisor	= $c_s[0];
			    }
			 ?>  
				  
		  	 <select name="company_supervisor" class="text" style="width:160px;" onchange="select_ajax_supervisor(document.editFrm.company_supervisor.value, '')" <?if($_GET[id]==$AppUI->user_id) echo "Disabled";?> >
				    <option value="" ><?php echo $AppUI->_('Not Specified')?></option>
					<?
					$query = "SELECT company_name, company_id FROM companies ORDER BY company_name ASC"; 
					$sql = mysql_query($query);
					
					if($company_supervisor == "")
					{
						$company_supervisor = $company;
					}
					 
					while($vec = mysql_fetch_array($sql) )
					{
					 $selected = ($vec['company_id']==$company_supervisor) ? "selected" : "";
					  echo "<option " .$selected ." value=\"$vec[company_id]\">$vec[company_name]</option>";
					}
					?>
		     </select>	
             <br>

		     <script type="text/javascript">
			   select_ajax_supervisor('<?=$company_supervisor?>','<?=$user_supervisor?>');	
			 </script>
			  	 			
		  	 <select name="user_supervisor" id="user_supervisor" class="text" style="width:160px;"  <? if($AppUI->user_id == $id ){ echo "disabled"; } ?> >
			 </select> *
			
			   </td>
			</tr>				
			<tr>
			  <td class="right">
				<b><?php echo $AppUI->_('Position')?>:&nbsp;</b>
			  </td>
			  <td>
				<input class="text" size="25" type="text" name="position" value="<?=$position?>" <?if($_GET[id]==$AppUI->user_id) echo "Disabled";?> >
			  </td>
			</tr>

			<tr> 
			   <td align="right">
				<b><?php echo $AppUI->_('Legajo')?>:&nbsp;</b>
			  </td>
			  <td>
			   <input class="text" size="12" type="text" name="legajo" value="<?=$legajo?>" <?if($_GET[id]==$AppUI->user_id) echo "Disabled";?> >
			  </td>
			</tr>
			<tr>
				<td class="right" ><b><?=$AppUI->_('Current Salary')?>&nbsp;($):&nbsp;</b></td>
				<td >
				   <?
					 $sql_s = db_exec("select hhrr_comp_remuneration from hhrr_comp where hhrr_comp_user_id = '$id' ");
					 $data = mysql_fetch_array($sql_s);

					 $salarycurrent = $data[0];
					 
				   ?>
				  <input class="text" type="text" size="12" name="salarycurrent" value="<?=$salarycurrent?>" <?if($_GET[id]==$AppUI->user_id) echo "Disabled";?> >
			</tr>
			<tr> 
			   <td align="right">
				<b><?php echo $AppUI->_('Hired')?>:&nbsp;</b>
			  </td>
	      <td nowrap="nowrap" >
					<input type="text" name="input_date_company" value="<?php echo $user_input_date_company ? $user_input_date_company->format( $df ) : "" ;?>" class="text" disabled="disabled" size="12" />
						<a href="javascript://" onClick="popCalendar('input_date_company')">
							<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
						</a> 
				  <input type="hidden" name="user_input_date_company" value="<?php echo $user_input_date_company ? $user_input_date_company->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />   
      	</td>
			</tr>
			

         <?php
    if (!(isset($hhrr_portal) && @$hhrr_portal == true)){ ?>
    <tr>
	 <td align="right">
	 	<b><?php echo $AppUI->_('Work schedule')?>:&nbsp;</b>
	 </td>    	
	<td valign="top">
		<table border="0">
		<tr>
			<td>&nbsp;</td>
			<td align="center"><b><?=$AppUI->_('Start')?></b></td>
			<td align="center"><b><?=$AppUI->_('End')?></b></td>
		</tr>
		<tr>
		    <?if($_GET[id]==$AppUI->user_id) $disab = "Disabled"; ?>
			<td><b>AM</b></td>
			<td align="center"><?=arraySelect( $times, 'start_time_am', 'size="1" '.$disab.' class="text"',
			$start_time_am ? $start_time_am->format("%H%M%S") : $times["NULL"],'','','80px' )?></td>
			<td align="center"><?=arraySelect( $times, 'end_time_am', 'size="1" '.$disab.'  class="text"',
			$end_time_am ? $end_time_am->format("%H%M%S") : $times["NULL"],'','','80px' )?></td>
		</tr>
		<tr>
			<td><b>PM</b></td>
			<td align="center"><?=arraySelect( $times, 'start_time_pm', 'size="1" '.$disab.'  class="text"',
			$start_time_pm ? $start_time_pm->format("%H%M%S") : $times["NULL"],'','','80px'  )?></td>
			<td align="center"><?=arraySelect( $times, 'end_time_pm', 'size="1" '.$disab.'  class="text"',
			$end_time_pm ? $end_time_pm->format("%H%M%S") : $times["NULL"],'','','80px'  )?></td>		
		</tr>	
		</table>	
	</td>
    </tr>
    
	<tr>
		<td align="right" valign=top><b><?php echo $AppUI->_('Daily working hours');?>:&nbsp;</b></td>
		<td valign="top"><input class="text" type="text" name="daily_working_hours" value="<?=$drow["daily_working_hours"]?>" size="4" maxlength="4" <?if($_GET[id]==$AppUI->user_id) echo "Disabled";?> >&nbsp;<input class="text" type="button" value="<?=$AppUI->_('Calculate')?>"onclick="calculateWorkingHours();" >
	</td>
	
	</tr> 
<?php }else{ echo "<td colspan=2>&nbsp;</td></tr><tr><td colspan=2>&nbsp;</td></tr>"; } ?>


		</table>
       </div>
		<!-- Fin de la segunda columna para empleados -->


		<!-- Segunda columna solo para candidatos -->
        <div id="candidatediv0" name="candidatediv0" <? if($user_type==5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?> > 

		<table border="0" width="350" >
		  <tr>
			<td class="right" nowrap="nowrap" width="150">
			  <b><?php echo $AppUI->_('Actual Company')?>:&nbsp;</b>
			</td>
			<td width="250">
			  <input class="text" size="25" type="text" name="actualcompany" value="<?= $actualcompany ?>">
			</td>  
		  </tr>

		   <tr> 
			<td class="right" ><b><?php echo $AppUI->_('Actual job')?>:&nbsp;</b></td>
			<td><?php echo arraySelect( $WorkTypes, 'actualjob', 'size="1" class="text"', $actualjob, true );?>
			</td> 
		   </tr>
           
		    <tr> 
				<td class="right" ><b><?php echo $AppUI->_('Actual working hours')?>:&nbsp;</b></td>
				<td ><input class="text" size="25" type="text" name="workinghours" value="<?= $workinghours ?>"></td>
			</tr>
			<tr>
				<td class="right"  ><b><?=$AppUI->_('Diary availability')?>:&nbsp;</b></td>
				<td ><input class="text" type="text" size="4" name="hoursavailableperday" value="<?=$hoursavailableperday?>"></td>   
		    </tr>

			<tr> 
				<td class="right"  ><b><?=$AppUI->_('Cost per hour wanted')?>&nbsp;($):&nbsp;</b></td>
				<td ><input class="text" type="text" size="4" name="costperhour" value="<?=$costperhour?>"> * (Freelance)
				</td>
		  </tr>

			<tr> 
				<td class="right" ><b><?=$AppUI->_('Salary wanted')?>&nbsp;($):&nbsp;</b></td>
				<td><input class="text" type="text" size="8" name="salarywanted" value="<?=$salarywanted?>"> * (Full time)</td>
      </tr>

			<tr>
			  <td colspan="2" align="left">
			     <table border='0' >
					<tr> 
						<td  width="145" class="right" rowspan="3"  valign="top"><b><?php echo $AppUI->_('Work preference')?>:&nbsp;*</b></td>
						<td rowspan="3" >
						<input class="text" type="checkbox"  name="wantsfulltime" value="1" <?if($wantsfulltime==1){echo "checked";}?> >	<?=$AppUI->_('Wants full time')?> <br />
						<input class="text" type="checkbox"  name="wantsparttime" value ="1" <?if($wantsparttime==1){echo "checked";}?> > <?=$AppUI->_('Wants part time')?> <br />
						<input class="text" type="checkbox"  name="wantsfreelance" value="1" <?if($wantsfreelance==1){echo "checked";}?> > <?=$AppUI->_('Wants freelance')?>
						</td>
					</tr>
				  </table>
			  </td>
			</tr>
     <? if (!$hhrr_portal){?>
		  <tr>
	 			<td class="right"><b><?=$AppUI->_('Was interviewed')?>:&nbsp;</b></td>
				<td ><input class="text" type="checkbox"  name="wasinterviewed" <? if($wasinterviewed==1)echo "checked"?>></td>
		  </tr>	     
			<tr> 
				<td class="right" ><b><?=$AppUI->_('Candidate Status')?>:&nbsp;</b></td>
				<td ><?php echo arraySelect( $SCandidateStatus, 'candidatestatus', 'size="1" class="text"', true, true );?></td>    
			</tr>		
			<? } ?>
		</table>
		</div>
		<!-- Fin de la segunda columna solo para candidatos -->
		
		<!-- FOTO-->
		<br>
		<CENTER>
      <? if($logo!="ninguna" and $logo!=""){?>
      <img border="1" height="140" src="<?=$uploads_dir."/".$id ?>/<?=$logo ?>"> 
      <? }else echo "<br><br><br><b>" .$AppUI->_('Photo not available')."</b>"; ?>
    </CENTER>
		
		
	</td>

    
	<? if (!$hhrr_portal){?>

	<td valign="top"> 
       <!-- Tercera columna -->
	   <table>
		   <tr>
			<td align="right">
			  <b><? echo $AppUI->_('User Type'); ?></b>
			</td>
			<td >
				<? 
				if ($candidate && $user_type =="")
				{
				  $user_type = '5';
				}

				if (!$candidate && $user_type =="")
	            {
				  $user_type = '2';
				}

				if($AppUI->user_id == $_GET[id])
				{
				$disabled = "disabled";
				}

				echo arraySelect($utypes,"user_type",'size="1"  '.$disabled.' class="text" onchange="javascript: changeUsertype(this); "', $user_type, true );
				?>
			</td>
		   </tr>
		  
	   </table>
       <!-- Fin de la tercera columna -->
	</td> 
	<? }else{ ?> 
    <input type="hidden" name="user_type" value="5">
	<? } ?>

  </tr>
</table>
  
<table align="center" border="0" cellpadding="1" cellspacing="1" width="100%" class="std">
	<tr>
    <td width="100%" align="left">
	  	<table width="100%">
		   <tr>
			  <td nowrap="nowrap" class="right" width="126"><b><?=$AppUI->_("Resume");?>:&nbsp;</b></td>
			  <td colspan="3"><input class="text" size="25" type="file" name="resume">
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
					<td colspan="3"><input class="text" size="25" type="file" name="user_pic">
				     <?php
					  if($logo<>"ninguna" && $logo!="" && file_exists($uploads_dir."/".$id."/".$logo)) { 
						echo "&nbsp;Actual: <b><a class='link1'  href='javascript:image_open(\"".
							$AppUI->getConfig("base_url")."/$uploads_dir/$id/".rawurlencode($logo)."\", \"img1\")'>$logo</a>";
						echo '</b>&nbsp;<input class="text" type="button" value="Quitar" onclick="doRemoveFotoChica()">';
					  }
					?>
					</td>
		   </tr>		  
		  </table>
		</td>
	</tr>
</table>


<!--Inicio comentario !-->
<table align="center" border="0" cellpadding="1" cellspacing="1" width="100%" class="std">
	<tr>
		<td width="1"></td> 
    <td width="100%" align="left">  <? if($_GET[id]!= $AppUI->user_id){ ?>

	  <table width="100%">
	  	<tr>
		   <td rowspan="2" class="right" valign="top" width="120" align="right">
		     <b><?=$AppUI->_('Comments')?>:&nbsp;</b>
		   </td>
		   <td colspan="2"><textarea class="textarea" rows="6" cols="109" name="comments" wrap="virtual"><?=( isset($edited_hhrr_note) AND $edited_hhrr_note['hhrr_note_type'] == NOTE_INTERNAL ) ? $edited_hhrr_note['hhrr_note'] : "";?></textarea>
		   </td>
		  </tr>
		  <tr>
		   <td align="right">
		   	<a href="JavaScript: submitNote(<?= NOTE_INTERNAL ?>)"><?= ( isset($edited_hhrr_note) AND $edited_hhrr_note['hhrr_note_type'] == NOTE_INTERNAL  ) ? $AppUI->_('Update Note') : $AppUI->_('Add Note');?></a>
		   	<br><br>
		  </td>
		  <td width="13">
		  </td>
	   </tr>
	   
	   <tr>
	   	<td>
	   		&nbsp;
	   	</td>
				<?
				
				$sql="SELECT hhrr_note_id,hhrr_user_id,hhrr_note, DATE_FORMAT(hhrr_note_date, '%d/%m/%Y %H:%i:%s') AS hhrr_note_date,hhrr_note_owner, CONCAT(user_last_name,', ',user_first_name) AS full_name FROM hhrr_notes LEFT JOIN users ON hhrr_notes.hhrr_note_owner=users.user_id WHERE hhrr_user_id = $id AND hhrr_note_type = ".NOTE_INTERNAL." ORDER BY hhrr_note_date DESC, hhrr_note_id DESC;";
				$hhrr_notes=db_loadList($sql);
				if ( count($hhrr_notes) >0 ) // si hay algun comentario muestro la tabla sino no
				{?>

	   	<td colspan="2">
				<table class="width90"  width="100" cellspacing="1">
				<tr>
					<td class="form-title" colspan="2">
						<?=$AppUI->_('Comments')?>
					</td>
				</tr>

				<?
				foreach($hhrr_notes as $note)
				{
				?>
				
				<tr class="bugnote">
					<td class="bugnote-public">
						<?=$note['full_name']?><br>
						<span class="small"><?=$note['hhrr_note_date']?></span><br><br>
						<? if ($AppUI->user_type == 1 OR $note['hhrr_note_owner'] == $AppUI->user_id) //Si es admin o el duenio de la nota lo dejo editarla /borrarla
						{?>
						<span class="small">
							<input type="hidden" name="del_hhrr_note_id" value="0" >
							[ <a href="JavaScript: edit_note(<?=$note['hhrr_note_id']?>)"><?=$AppUI->_('Edit')?></a>&nbsp;<a href="JavaScript: confirma(<?=$note['hhrr_note_id']?>)"><?=$AppUI->_('delete')?></a>]
						</span>
						<?}?>
					</td>
					
					<td class="bugnote-note-public">
						<?=nl2br($note['hhrr_note'])?>
					</td>
				</tr>
				<tr>
					<td class="spacer" colspan="2">&nbsp;</td>
				</tr>
				<?							
				}?>
				</table>
	  	</td>
	  	<?}?>
	   </tr>
	   
	  </table>
        <? } ?>
	  </td>
	   </tr>
	 
	</td>
</tr>
<?
$sql="SELECT hhrr_notes.*, CONCAT(user_last_name,', ',user_first_name) AS full_name FROM hhrr_notes INNER JOIN users ON hhrr_notes.hhrr_note_owner = users.user_id WHERE hhrr_user_id = $id ORDER BY hhrr_note_date DESC;";
?>


  <tr>
    <td  width="100%" colspan="3" align="left">
<br><br>
	<div id="candidatediv3" name="candidatediv3" <? if($user_type==5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?> > 
	  
	  <? if (!$hhrr_portal){?>
	  <table width="100%">
	  	<tr>
		   <td rowspan="2" class="right" valign="top" width="120" align="right">
		     <b><?=$AppUI->_('Interview comments')?>:&nbsp;</b>
		   </td>
		   <td colspan="2">&nbsp;<textarea class="textarea" rows="6" cols="109" name="interviewcomments" wrap="virtual"><?=( isset($edited_hhrr_note) AND $edited_hhrr_note['hhrr_note_type'] == NOTE_INTERVIEW ) ? $edited_hhrr_note['hhrr_note'] : "";?></textarea>
		   </td>
		  </tr>
		  <tr>
		   <td align="right">
		   	<a href="JavaScript: submitNote(<?= NOTE_INTERVIEW ?>)"><?= ( isset($edited_hhrr_note) AND $edited_hhrr_note['hhrr_note_type'] == NOTE_INTERVIEW  ) ? $AppUI->_('Update Interview Note') : $AppUI->_('Add Interview Note');?></a>
		   	 <br><br>
		  </td>
		  <td width="23"></td>
	   </tr>
	   <tr>
	   	<td>
	   		&nbsp;
	   	</td>
				<?
				$sql="SELECT hhrr_note_id,hhrr_user_id,hhrr_note, DATE_FORMAT(hhrr_note_date, '%d/%m/%Y %H:%i:%s') AS hhrr_note_date,hhrr_note_owner, CONCAT(user_last_name,', ',user_first_name) AS full_name FROM hhrr_notes LEFT JOIN users ON hhrr_notes.hhrr_note_owner=users.user_id WHERE hhrr_user_id = $id AND hhrr_note_type = ".NOTE_INTERVIEW." ORDER BY hhrr_note_date DESC, hhrr_note_id DESC;";
				$hhrr_notes=db_loadList($sql);
				if ( count($hhrr_notes) >0 ) // si hay algun comentario muestro la tabla sino no
				{?>

	   	<td colspan="2">
				<table class="width90" cellspacing="1">
				<tr>
					<td class="form-title" colspan="2">
						<?=$AppUI->_('Interview comments')?>
					</td>
				</tr>
				<?
				foreach($hhrr_notes as $note)
				{
				?>
				
				<tr class="bugnote">
					<td class="bugnote-public">
						&nbsp;
						<?=$note['full_name']?><br>
						<span class="small"><?=$note['hhrr_note_date']?></span><br><br>
						<? if ($AppUI->user_type == 1 OR $note['hhrr_note_owner'] == $AppUI->user_id) //Si es admin o el duenio de la nota lo dejo editarla /borrarla
						{?>
						<span class="small">
							<input type="hidden" name="del_hhrr_note_id" value="0" >
							[ <a href="JavaScript: edit_note(<?=$note['hhrr_note_id']?>)"><?=$AppUI->_('Edit')?></a>&nbsp;<a href="JavaScript: confirma(<?=$note['hhrr_note_id']?>)"><?=$AppUI->_('delete')?></a>]
						</span>
						<?}?>
					</td>
					
					<td class="bugnote-note-public">
						<?=nl2br($note['hhrr_note'])?>
					</td>
				</tr>
				<tr>
					<td class="spacer" colspan="2">&nbsp;</td>
				</tr>
				<?							
				}?>
				</table>
	  	</td>
	  	<?}?>
	   </tr>
	  </table>  
	  <? }S ?>
	</div>
	</td>
  </tr>

  <tr>
    <td colspan="3" align="left">
	  <div id="employdiv1" name="employdiv1" <? if($user_type!=5){ ?> style="display:none" <?}else{?> style="display: ;" <?} ?> >
	  <table border="0" width="100%">
	     <tr>
		   <td colspan="3">
			<hr>
		   </td>
		  </tr>
	    <tr>
	    <td align="right" width="130">
		  <b><?php echo $AppUI->_('Login');?>:</b>
		</td>
	    <td width="120">
				<?
	                    if ($username=="" || $AppUI->post !="")
	                     {
	                      echo "<input class=\"text\" size=\"20\" type=\"text\" name=\"username\" value=\"$username\">";
						  $dosql = "do_hhrr_add";
						 }
						 else
						 {
                          echo "<input type=\"hidden\" name=\"username\" value=\"$username\"><b><span id=\"txt_username\" style=\"vertical-align: middle\">$username</span></b>";
						 }
	                ?>
			  </td>
		    
		   <td >
		     <table border=0 >
				<tr>
					<td align="right" width="120"><b><?php echo $AppUI->_('Password');?>:</b></td>
					<td nowrap="nowrap"><input type="password" class="text" name="user_password" value="<?php echo $drow["user_password"];?>" maxlength="32" size="20" /> *</td>
				
					<td align="right" width="120"><b><?php echo $AppUI->_('Password');?>2:</b></td>
					<td nowrap="nowrap"><input type="password" class="text" name="password_check" value="<?php echo $drow["user_password"];?>" maxlength="32" size="20" /> *</td>
				</tr> 
				</table>
		   </td>
		 </tr>
	  </table>
	  </div>

	</td>
  </tr>

  <tr>
	  <td colspan="3">
	   &nbsp;<BR>
	  </td>
	</tr>

  <tr>
    <td nowrap="nowrap" class="right" colspan="3">
      <b><?echo $AppUI->_('registryloaded');?>:&nbsp;</b>&nbsp;<?= $inputdate ?>&nbsp;&nbsp;
    </td>
    </tr>
    
	<tr>
		<td colspan="3" align="right">
			<input type="button" value="<?php echo $AppUI->_( 'save' );?>" name="send" class="button"  onclick="submitIt()"/>
		    &nbsp;
			<?
			if($_GET[a]!="personalinfo")
			{
       		if($id >'0'){
						$salir = "index.php?m=hhrr&a=viewhhrr&tab=1&id=".$id;
					}
					else{
						$salir = "index.php?m=hhrr";
					}
			?>
			<input type="button" value="<?php echo $AppUI->_( 'exit' );?>" class="button" onClick="javascript:window.location='<?=$salir;?>';" />
			<? }
			?>
			</td>
	</tr>    
	

 </form>
<table>

<script language="javascript">
changeJobStatus();
</script>	


<?php } ?>
