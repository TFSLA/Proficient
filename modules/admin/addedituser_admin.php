<script language="javascript">

<?  
$restricted = ($AppUI->user_type != 1 && $user_id==$AppUI->user_id); 

if($_GET['status']=='edit' && $_GET['from']=='tasks'){ ?>  
goback_tasks('<?=$_GET['cph']?>');  
<?}?>

function goback_tasks(costo){
          var user_edit = <?=$_GET[user_id]?>;
          window.opener.set_cost_per_hour(costo,user_edit);
          window.close();
}

 function doRemoveFotoChica()
 {
	var form = document.delFrm;
	form.do_remove.value="user_pic";
	form.submit();
 }	

function setEmailConf(){
  var pop3_arr = new Array();
  var smtp_arr = new Array();
  var imap_arr = new Array();
  var port_arr = new Array();
<?
  $sql = "SELECT * FROM companies ORDER BY company_name";
  $companies = db_loadList( $sql );
  foreach ($companies as $row) {
    echo "  port_arr[".$row["company_id"]."] = '".$row["company_mail_server_port"]."';\n";
    echo "  pop3_arr[".$row["company_id"]."] = '".$row["company_pop3"]."';\n";
    echo "  imap_arr[".$row["company_id"]."] = '".$row["company_imap"]."';\n";
    echo "  smtp_arr[".$row["company_id"]."] = '".$row["company_smtp"]."';\n";
  }
?>
  if(document.editFrm.user_company.value!=0){
    if(port_arr[document.editFrm.user_company.value]==110){
      document.editFrm.user_mail_server_port.selectedIndex=0;
    }
    else{
      document.editFrm.user_mail_server_port.selectedIndex=1;
    }
    document.editFrm.user_pop3.value=pop3_arr[document.editFrm.user_company.value];
    document.editFrm.user_smtp.value=smtp_arr[document.editFrm.user_company.value];
    document.editFrm.user_imap.value=imap_arr[document.editFrm.user_company.value];
  }
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
function isoverlapped (st1, ed1, st2, ed2) {
	return (ed1.selectedIndex > st2.selectedIndex);
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
	
	if ( isoverlapped(st1, ed1, st2, ed2) ){
		alert("<?=$AppUI->_("Turn 1 time and turn 2 time are overlaped")?>");
		return false;
	}
	
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

function popUserSupervisor() {
    var f = document.editFrm;
    if (f.user_company.selectedIndex == 0) {
        alert1("<?=$AppUI->_('Please select a company first!')?>");
    }
    else{
        window.open('./index.php?m=public&a=selector&dialog=1&suppressLogo=1&callback=setUserSupervisor&id=<?=$_GET[user_id]?>&table=user_supervisor&company_id='
            + f.user_company.value
            + '&user_supervisor='+f.user_supervisor.value
            + '&dept_id='+f.user_department.value,'user_supervisor','left=50,top=50,height=250,width=400,resizable')
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

</script>
<?php /* ADMIN $Id: addedituser_admin.php,v 1.4 2009-07-21 20:00:18 nnimis Exp $ */
//add or edit a system user

//Valido que tenga permisos para el modulo
if (getDenyEdit("admin") && $_GET['user_id']!= $AppUI->user_id)
	 $AppUI->redirect( "m=public&a=access_denied" );

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;
$default_user_status = 0; 
$default_timexp_supervisor = -1; 
$another_user = $AppUI->getState( 'UserAddBatch' );
$another_user = $another_user == NULL ? 0 : $another_user;
$canReadHHRR = !getDenyRead("hhrr") || $user_id == $AppUI->user_id;
$canEditHHRR = !getDenyEdit("hhrr") || $user_id == $AppUI->user_id;
// load sysvals
$IMtypes = dPgetSysVal( 'IMType' );
$estCivil = dPgetSysVal( 'MaritalState' );
$SupTypes = dPgetSysVal( 'SupervisionType' );
$SupTypes = arrayMerge($SupTypes, array("user"=>$AppUI->_("Specific User")));
$uploads_dir = $AppUI->getConfig('hhrr_uploads_dir');
$df = $AppUI->getPref('SHDATEFORMAT');
$cal_day_start = $AppUI->getConfig('cal_day_start');
// build array of times in 30 minute increments
$times = array();
$t = new CDate();
$t->setTime( $AppUI->getConfig('cal_day_start'),0,0 );
$max = new CDate();
$max->setTime($AppUI->getConfig('cal_day_end'),0,0 );
if (!defined('LOCALE_TIME_FORMAT'))
  define('LOCALE_TIME_FORMAT', '%I:%M %p');
$times["NULL"]="";
for ($j=0; $t <= $max; $j++) {
	$times[$t->format( "%H%M%S" )] = $t->format( LOCALE_TIME_FORMAT );
	$t->addSeconds( $AppUI->getConfig('cal_day_increment') * 60 );
}
/*<uenrico>*/
// load locations arrays
$Clocation = new CLocation();
$Clocation->loadCountries();
$Clocation->addItemAtBeginOfCountries($Clocation->addItem(0, "Not Specified"));
$Clocation->loadStates();
$Clocation->addItemAtBeginOfStates($Clocation->addItemState("0","0","Not Specified"));
/*</uenrico>*/
// check permissions
if (!$canEdit &&  $user_id!=$AppUI->user_id) {
    $AppUI->redirect( "m=public&a=access_denied" );
}

//Con esto verifico que el usuario edite a su nive: usuario solo pueden editar usuarios, s pueden editar cualquier cosa
if ( !edit_admin($AppUI->user_id) )
	$AppUI->redirect( "m=public&a=access_denied" );




// que no sea system admin y adem? est?modificando sus datos
$restricted = ($AppUI->user_type != 1 && $user_id==$AppUI->user_id); 

$userTypes = $utypes;
unset($userTypes[0]);

if($AppUI->user_type != 1){	
	unset($userTypes[1]);
} 
$sql = "
SELECT u.*, 
    company_id, company_name, 
    dept_name,
	CONCAT(tesup.user_first_name, ' ' , tesup.user_last_name) timexp_supervisor_name
FROM users u
LEFT JOIN companies ON u.user_company = companies.company_id
LEFT JOIN departments ON dept_id = u.user_department
LEFT JOIN users tesup ON tesup.user_id = u.timexp_supervisor
WHERE u.user_id = '$user_id'
";

$was_posted = $_POST["dosql"] == "do_user_aed";
if ( $was_posted ){
	$user = $_POST ;
}else{
	$rta_sql = db_loadHash( $sql, $user );
}
if (!$was_posted && !$rta_sql && $user_id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid User ID', 'user_management.gif', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	$titleBlock->show();
} else {
// pull companies
	$sql = "SELECT company_id, company_name FROM companies ORDER BY company_name";
	$companies = arrayMerge( array( 0 => '' ), db_loadHashList( $sql ) );
	
	if ($user_id) {
		
		$start_time_am = $user["start_time_am"] ? new CDate( "0000-00-00 ".$user["start_time_am"] ) : null;
		$end_time_am = $user["end_time_am"]  ? new CDate( "0000-00-00 ".$user["end_time_am"] ) : null;
		$start_time_pm = $user["start_time_pm"] ? new CDate( "0000-00-00 ".$user["start_time_pm"] ) : null;
		$end_time_pm = $user["end_time_pm"] ? new CDate( "0000-00-00 ".$user["end_time_pm"] ) : null;		
	} else {
		$start_time_am = null;
		$end_time_am = null;
		$start_time_pm = null;
		$end_time_pm = null;
		$user["user_company"] = $AppUI->user_company;
		$user["user_type"] = 2;
	}	
	$user_birthday = intval(  $user["user_birthday"] ) ? new CDate( $user["user_birthday"] ) : null;
	$selected_supervisor_type = $default_timexp_supervisor;
	$timexp_supervisor_name = "";
	if($user["timexp_supervisor"] == -1){
		$selected_supervisor_type =	-2;
	}elseif($user["timexp_supervisor"] == -2){
		$selected_supervisor_type =	-1;
		}else{
			if (isset($user["timexp_supervisor"])){
				if (isset($SupTypes[$user["timexp_supervisor"]])){
					$selected_supervisor_type =	$user["timexp_supervisor"];
					$timexp_supervisor_name = $SupTypes[$user["timexp_supervisor"]];
				}else{
					$selected_supervisor_type =	"user";	
					$timexp_supervisor_name = $user["timexp_supervisor_name"];
				}
			}
		}
	
// setup the title block
	$ttl = $user_id > 0 ? "Edit User" : "Add User";
	$titleBlock = new CTitleBlock( $ttl, 'user_management.gif', $m, "$m.$a" );
	
	if ($user_id > 0 && $_GET['from']!='tasks'){
		$titleBlock->addCrumb( "?m=admin&a=viewuser&user_id=$user_id", "view this user" );
		
		$titleBlock->addCrumb( "?m=system&a=addeditpref&user_id=$user_id", "edit preferences" );

		if ($canEditHHRR)
			$titleBlock->addCrumb( "?m=hhrr&a=addedit&tab=1&id=".$user_id, "edit hhrr information" );
			
		$titleBlock->addCrumb( "?m=admin&a=calendars&user_id=$user_id", 'calendar' );
	}
	$titleBlock->show();

	if($_POST["dosql"] == "do_user_aed")
	{
	$user = $_POST;

	    if ($_POST['user_department'] == "" || $_POST['user_department'] == "-1") 
		{
		  $dept_name = $AppUI->_('All');
		}
		else
		{
		  @$area_in = mysql_result(mysql_query("SELECT dept_name FROM departments WHERE dept_id ='".$_POST['user_department']."' "),0);          
 		  $dept_name = $area_in;  
		}

     $user['dept_name'] = $dept_name;
     $user['user_supervisor'] = $_POST['user_supervisor'];
	}

 // Traigo las empresas que tienen departamento
 $sql_dept = db_exec("SELECT distinct(dept_company) FROM departments WHERE dept_company > '0' ");

 $strJS_dept = "var arDept = new Array();\n";

 while ($vec_dept = mysql_fetch_array($sql_dept))
 {
 $strJS_dept .= "arDept[arDept.length] = new Array('".$vec_dept[dept_company]."');\n";
 }
?>
<SCRIPT language="javascript">
function syncreturn(chk){
	var status = chk.checked;
	chk.form.return1.checked = status;
	chk.form.return2.checked = status;
}
function submitIt(){
    var form = document.editFrm;
    if (form.use_pop_values[1].checked)
    	form.user_smtp_use_pop_values.value = 0;
    else
    	form.user_smtp_use_pop_values.value = 1;
    	
    if (form.smtp_auth_check.checked)
    	form.user_smtp_auth.value = 1;
    else
    	form.user_smtp_auth.value = 0;
    	
<?php 	if (!$restricted){ 	?>
	var selected_st = form.timexp_supervisor_type.options[form.timexp_supervisor_type.selectedIndex].value;
	var cost_per_hour = trim(form.user_cost_per_hour.value);
	cost_per_hour = parseFloat(cost_per_hour!="" ? cost_per_hour : 0);

<?php 	} 	?>
		var today = new Date();
		//var today = today.getYear().toString()+"-"+(today.getMonth()+1).toString()+"-"+today.getDate().toString();
		
		var birth = new Date(form.user_birthday.value.substr(0,4), form.user_birthday.value.substr(4,2), form.user_birthday.value.substr(6,2));
                        var may_18 = new Date((birth.getFullYear() +18),(birth.getMonth()-1),birth.getDate());
                        
                         var diferencia =today.getTime() - may_18.getTime() ;
                         var dias = Math.round(diferencia / (1000 * 60 * 60 * 24));
        
		
		var val_dept = false;
		
	    <?=$strJS_dept;?>
        
	    <?php if ($AppUI->user_type == 1){ 	?>
		    for(var h = 0; h < arDept.length; h++){
				if (form.user_company.value == arDept[h][0])
			    {
				val_dept = true;
				h = arDept.length;
		        }
		    }
	    <?php } ?>

		if(!val_dept)
	    {
		form.user_department.value = '0';
	    }
		
		if (trim(form.user_username.value).length < 3) {
        alert("<?php echo $AppUI->_('adminValidUserName');?>");
        form.user_username.focus();
    } else if (trim(form.user_password.value).length < 4) {
        alert("<?php echo $AppUI->_('adminValidPassword');?>");
        form.user_password.focus();
    } else if (trim(form.user_password.value) !=  trim(form.password_check.value)) {
        alert("<?php echo $AppUI->_('adminPasswordsDiffer');?>");
        form.user_password.focus();
	}else if (trim(form.user_first_name.value).length < 1) {
        alert("<?php echo $AppUI->_('adminValidFirstName');?>");
        form.user_first_name.focus();
    } else if (trim(form.user_last_name.value).length < 1) {
        alert("<?php echo $AppUI->_('adminValidLastName');?>");
        form.user_last_name.focus();
    } else if (!isEmail(form.user_email.value)) {
        alert("<?php echo $AppUI->_('adminInvalidEmail');?>");
        form.user_email.focus();
    } else if (form.user_email_alternative1.value.length > 0 && !isEmail(form.user_email_alternative1.value)) {
        alert("<?php echo $AppUI->_('adminInvalidEmailAlternative1');?>");
        form.user_email_alternative1.focus();
    } else if (form.user_email_alternative2.value.length > 0 && !isEmail(form.user_email_alternative2.value)) {
        alert("<?php echo $AppUI->_('adminInvalidEmailAlternative2');?>");
        form.user_email_alternative2.focus();        
    } else if ((form.user_email_alternative1.value.length > 0 || form.user_email_alternative2.value.length > 0) && (form.user_email.value == form.user_email_alternative1.value || form.user_email.value == form.user_email_alternative2.value || form.user_email_alternative1.value == form.user_email_alternative2.value)) {
        alert("<?php echo $AppUI->_('adminInvalidEmailEquals');?>");
        form.user_email.focus();
    } else if (form.user_company.selectedIndex == 0 ) {
        alert("<?php echo $AppUI->_('adminValidCompany');?>");
        form.user_company.focus();
    }else if ( val_dept && (form.user_department.value == "" || form.user_department.value == "0" || form.user_department.value == "-1") && form.user_type.value != '5') {
		    alert1("<?php echo $AppUI->_('hhrrInvalidepartement');?>");
	} else if (form.user_type.selectedIndex == 0 ) {
        alert("<?php echo $AppUI->_('adminValidType');?>");
        form.user_type.focus();                
    }else if(trim(form.user_email_password.value) !=  trim(form.user_email_password_check.value)){
          alert("<?php echo $AppUI->_('emailPasswordsDiffer');?>");
          form.user_email_password.focus();
    }else if(form.user_smtp_auth.value == 1 && form.user_smtp_use_pop_values.value == 0 &&
    		(trim(form.user_smtp_username.value)=="")){
          alert("<?php echo $AppUI->_('smtpUsernameEmpty');?>");
          form.user_smtp_username.focus();
    /*} else if (form.user_birthday.value > today ) {*/
    } else if (dias <=0 ) {
       
        alert("<?php echo $AppUI->_('adminInvalidBirthday_18');?>");
                  
<?php 	if ($AppUI->user_id != $_GET[user_id]){ 	?>
    }else if(selected_st == "user" && form.timexp_supervisor.value <= "0"){
          alert("<?php echo $AppUI->_('adminInvalidSupervisor');?>");
          form.cmdPopSup.focus();
    }else if ((form.user_supervisor.value =='0' || form.user_supervisor.value =='')&& form.user_type.value !='5'){
           alert("<?php echo $AppUI->_('adminValidDirectReport');?>");
           } 
	else if(isNaN(cost_per_hour) || cost_per_hour < 0){
          alert("<?php echo $AppUI->_('adminInvalidUserCost');?>");
          form.user_cost_per_hour.focus();          
<?php } ?>
	} else {
<?php 	if (!$restricted){ 	?>
		if (selected_st == "-1" || selected_st == "-2")
			form.timexp_supervisor.value = selected_st;
<?php 	}	?>	
        form.submit();
    }
}

function popSup() {
    var f = document.editFrm;
        window.open('./index.php?m=public&a=selector&suppressLogo=1&dialog=1&callback=setSup&table=users&id=<?=$_GET[user_id]?>','sup','left=50,top=50,height=250,width=400,resizable')
}
// Callback function for the generic selector
function setSup( key, val ) {
    var f = document.editFrm;
    if (val != '') {
        f.timexp_supervisor.value = key;
        f.timexp_supervisor_name.value = val;
    } else {
        f.timexp_supervisor.value = '0';
        f.timexp_supervisor_name.value = '';
    }
	
}
function popDept() {
    var f = document.editFrm;
    if (f.selectedIndex == 0) {
        alert( 'Please select a company first!' );
    } else {
        window.open('./index.php?m=public&a=selector&dialog=1&suppressLogo=1&callback=setDept&table=departments&company_id='
            + f.user_company.options[f.user_company.selectedIndex].value
            + '&dept_id='+f.user_department.value,'dept','left=50,top=50,height=250,width=400,resizable')
    }
}


function change_cia(cia)
{
	var f = document.editFrm;

	if (cia.value != f.company_or.value )
	{
	f.user_department.value = '-1';
	f.dept_name.value = "<?=$AppUI->_('All');?>";
	}else{
	f.user_department.value = f.user_department_or.value;
	f.dept_name.value = f.dept_name_or.value;
	}

}

// Callback function for the generic selector
function setDept( key, val ) {
    var f = document.editFrm;
    if (val != '') {
        f.user_department.value = key;
        f.dept_name.value = val;
    } else {
        f.user_department.value = '0';
        f.dept_name.value = '';
    }
}
 
function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.user_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
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
function changeSupType(){
	var f= document.editFrm;
	var st = f.timexp_supervisor_type;
	if (st){
		var dus = document.getElementById("div_user_supervisor");
		var selected_value = st.options[st.selectedIndex].value;
		if (selected_value == "user"){
			dus.style.display = "";
		}else{
			dus.style.display = "none";
		}
	}
}
   function switchSmtpAuth(){
   		var auth = document.editFrm.smtp_auth_check.checked;
   		if (auth){
   			document.getElementById('smtp_auth').style.display='';
   		}else{
   			document.getElementById('smtp_auth').style.display='none';
   		}
   }
   function showSmtpSettings(){
   		var upv = document.editFrm.use_pop_values[1].checked;
   		if (upv){
   			document.getElementById('smtp_settings').style.display='';
   		}else{
   			document.getElementById('smtp_settings').style.display='none';
   		}
   }
/*
locations functions
*/
/*<uenrico>*/
	<?php 
		$Clocation->setFrmName("editFrm");
		$Clocation->setCboCountries("user_country_id");
		$Clocation->setCboStates("user_state_id");
		$Clocation->setJSSelectedState($user["user_state_id"]);
		
		echo $Clocation->generateJS();
		
	?>
/*</uenrico>*/
<? /*
function changeStatusUT(){
		var fields = new Array (
							"Cpy1"
						, "Cpy2"
						, "wrkSchTit"
						, "wrkSchCont"
						, "wrkTime1"
						, "wrkTime2");
		var ut = document.editFrm.user_type;
		var show = false;
		if (ut.options[ut.selectedIndex].value != '5');
			show = true;
				
		for (var i = 0; i < fields.length; i++){
			var f = document.getElementById(fields[i]);
			if (f)
				if (show){
						f.style.display = "";
						f.style.visibility = "show";
				}else{
					f.style.display = "none";
					f.style.visibility = "hide";
				}
		}
}
*/ ?>

function changeUsertype(type)
{   

	// 5 = candidato
	if(type.value == "5")
	{
	   document.getElementById("employdiv0").style.display = 'none';
	   document.getElementById("employdiv1").style.display = 'none';
	   document.getElementById("employdiv2").style.display = 'none';
	   document.getElementById("employdiv3").style.display = 'none';
	   document.getElementById("employdiv4").style.display = 'none';
	   document.getElementById("employdiv5").style.display = 'none';
	   document.getElementById("employdiv6").style.display = 'none';
	   document.getElementById("employdiv7").style.display = 'none';
	}else{
	   document.getElementById("employdiv0").style.display = '';
	   document.getElementById("employdiv1").style.display = '';
	   document.getElementById("employdiv2").style.display = '';
	   document.getElementById("employdiv3").style.display = '';
	   document.getElementById("employdiv4").style.display = '';
	   document.getElementById("employdiv5").style.display = '';
	   document.getElementById("employdiv6").style.display = '';
	   document.getElementById("employdiv7").style.display = '';
	}

	if(type.value == "1")
		document.getElementById("access_level").value = "90";
	else
		document.getElementById("access_level").value = "10";
}

function isset(variable_name) {
    try {
         if (typeof(eval(variable_name)) != 'undefined')
         if (eval(variable_name) != null)
         return true;
     } catch(e) { }
    return false;
   }
   
function select_ajax(company, department, user_type)
{
 
   xajax_addSelect_Departments('user_department', company ,department, '', '', '<?=$AppUI->_('All')?>');	
   
  // xajax_combo_UserSupervisor('user_supervisor', company, supervisor,'direct_report');
   
  // xajax_combo_UserSupervisor('timexp_supervisor', company, timexp_supervisor, 'timexp_supervisor');	
}

function select_ajax_supervisor(company, supervisor)
{
	xajax_combo_UserSupervisor('user_supervisor', company, supervisor,'direct_report');
}

function select_ajax_timexp_supervisor(company, timexp_supervisor)
{
	xajax_combo_UserSupervisor('timexp_supervisor', company, timexp_supervisor, 'timexp_supervisor');
}


</script>
<?
$back = strstr($_SERVER[HTTP_REFERER],'=');

?>

<form name="delFrm" action="" method="POST">
	<input type="hidden" name="dosql" value="do_user_aed" />
	<input type="hidden" name="do_remove" value="" />
	<input type="hidden" name="origen" value="<? echo $origen;?>" />
	<input type="hidden" name="back" value="m<? echo $back;?>" />	
	<input type="hidden" name="user_id" value="<?php echo intval($user["user_id"]);?>" />
	<input type="hidden" name="photo_name" value="<?php echo $user["user_pic"];?>" />
</form>

<table border="0" cellpadding="1" cellspacing="1" height="400" width="100%" class="std">
<form name="editFrm" action="" method="post" enctype="multipart/form-data">
	<input type="hidden" name="user_id" value="<?php echo intval($user["user_id"]);?>" />
	<input type="hidden" name="dosql" value="do_user_aed" />
	<input type="hidden" name="origen" value="<? echo $origen;?>" />
	<input type="hidden" name="back" value="m<? echo $back;?>" />
	<input type="hidden" name="do_remove" value="" />
	<input type="hidden" name = "from" value="<?=$_GET['from']?>">
<tr>
    <td align="left">
        <? if($_GET['from']!='tasks'){?>
        <input type="button" value="<?php echo $AppUI->_('back');?>" onClick="javascript:history.back(-1);" class="button" />
        <? } ?>
    </td>
    <td align="center"  colspan="2">&nbsp;
<?  if(!$user["user_id"]){ ?>    
    <input type="checkbox" name="return1" value="1" <?php echo ($another_user ? "checked" : "")?> 
 			onclick="syncreturn(this)" onchange="syncreturn(this)"/>
    <?php echo $AppUI->_('Return here to add other user')?>
<?  }?>    
    </td>
    <td align="right" nowrap>
		<?php
			if($user_id != $AppUI->user_id ){
				echo $AppUI->_('Security Template');

			$templates = CTemplate::getHash();
			$templates = arrayMerge(array("0"=>""), $templates);

			echo (arraySelect( $templates, 'template_permission_template', 'size="1" align="middle" class="text"', null));
				echo("&nbsp;");
			}?>
        <input type="button" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt()" class="button" />
    </td>
</tr>	
<tr>
	<th colspan="4">
	<b><?=$AppUI->_('User Information')?></b>
	</th>
</tr>	
<?  if(!$user["user_id"]){ ?>
	<input type="hidden" name="date_created" value="<?=date("Y-m-d")?>" />
	<input type="hidden" name="last_visit" value="<?=date("Y-m-d")?>" />
	<input type="hidden" name="enabled" value="0" />
	<input type="hidden" name="protected" value="0" />
	<input type="hidden" name="access_level" value="10" />
<?}?>
<tr>
    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Login Name');?>:</td>
    <td >
<?php
	if ($user["user_id"]){
		echo '<input type="hidden" class="text" name="user_username" value="' . $user["user_username"] . '" />';
		echo '<strong>' . $user["user_username"] . '</strong>';
    } else {
        echo '<input type="text" class="text" name="user_username" value="' . $user["user_username"] . '" maxlength="20" size="20" />';
		echo ' <span class="smallNorm">' . $AppUI->_('*') . '</span>';
    }
?>
	</td>
    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('User Type');?>:</td>
    <td>
<?php
    echo arraySelect( $userTypes, 'user_type', 'class="text" onchange="javascript: changeUsertype(this);" size="1" '.($restricted?" disabled=\"true\"":""), $user["user_type"],'','','120px' )." *";
?>
    </td>
</tr>
<tr>
    <td align="right"><?php echo $AppUI->_('Password');?>:</td>
    <td><input type="password" class="text" name="user_password" value="<?php echo $user["user_password"];?>" maxlength="32" size="32" />&nbsp;* </td>
    <td align="right"><?php echo $AppUI->_('Password');?>2:</td>
    <td><input type="password" class="text" name="password_check" value="<?php echo $user["user_password"];?>" maxlength="32" size="32" />&nbsp;* </td>
</tr>
<tr id="Cpy1">
    <td align="right"><?php echo $AppUI->_('Company');?>:</td>
    <td>
	  <input type = "hidden" name = "company_or" value = "<?=$user["user_company"];?>" >

<?php
		   // Me fijo si el usuario que edita es admin
           $sql = db_exec("SELECT user_type FROM users WHERE user_id = '".$AppUI->user_id."' ");
		   $data_admin = mysql_fetch_array($sql);
		   $u_admin = $data_admin[0];

		   if($u_admin!='1' && $user["user_id"]  >0 ){ $restricted ="true"; }
		   
    if ($restricted){ 
     echo "<span onmouseover=\"tooltipLink('<pre style=&quot;margin:0px;&quot;>".$AppUI->_('Restricted option to Administrators users')."</pre>', '');\" onmouseout=\"tooltipClose();\">";
     } 
        
    echo arraySelect( $companies, 'user_company', 'class=text onchange=\'select_ajax(document.editFrm.user_company.value,"","",document.editFrm.user_type.value)\' size=1'.($restricted?" disabled=\"true\"":""), $user["user_company"] )." *";
    
     if ($restricted){ 
     echo "</span>";
     } 
    
?>
    </td>
    <td align="right"><?php echo $AppUI->_('Department');?>:</td>
    <td>
	   
	    <script type="text/javascript">
			select_ajax('<?=$user["user_company"]?>','<?=$user["user_department"]?>','<?=$user["user_type"]?>');		
	    </script>
			 
        
        <? if ($restricted){ ?>
        
        <span onmouseover="tooltipLink('<pre style=&quot;margin:0px; &quot;><?php echo $AppUI->_('Restricted option<br>to Administrators<br>users'); ?></pre>', '');" onmouseout="tooltipClose();">
        <? } ?>
         
       <select name="user_department" id="user_department" class="text" style="width:160px;" <?php echo ($restricted?" disabled=\"true\"":"");?> >
	   </select> &nbsp;*
	   
        <? if ($restricted){ ?>
        </span>
        <? } ?>
       
        
    </td>
</tr>
<tr id="Cpy2">
    <td align="right"><?php echo $AppUI->_('Job Title');?>:</td>
    <? if($_GET[user_id]==$AppUI->user_id && $AppUI->user_type != 1) $disab = "Disabled";?>
    <td><input type="text" class="text" name="user_job_title" value="<?php echo $user["user_job_title"];?>" maxlength="25" size="25" <?=$disab?>/> </td>
<? if($_GET[user_id]!=$AppUI->user_id){ 
	?>
	<td align="right"><?php echo $AppUI->_( 'Status' );?>:</td>
	<td>
		<?php
		if($user["user_status"]=='0' AND $user["user_id"]!=0){
			echo arraySelect( $ustatus, 'user_status', 'size="1" class="text"', isset($user["user_status"]) ? $user["user_status"] : $default_user_status , true,'','80px' )."&nbsp;*";
		}
		elseif($user["user_status"]=='1' AND $user["user_id"]!=0 AND $license_key==lim_user(1)){
			echo arraySelect( $ustatus, 'user_status', 'size="1" class="text"', isset($user["user_status"]) ? $user["user_status"] : $default_user_status , true )."&nbsp;*";
		}
		elseif($user["user_id"]==0 AND $license_key==lim_user(1)){
			echo arraySelect( $ustatus, 'user_status', 'size="1" class="text"', isset($user["user_status"]) ? $user["user_status"] : $default_user_status , true )."&nbsp;*";
		}
		else{
			echo "<select name='user_status' size='1' class='text'><option value='1'>Inactivo</option></select>&nbsp;*&nbsp<span class='error'>";
		}
		if($license_key!=lim_user(1)) echo $AppUI->_('Active Users Limit Reached')."</span>\n";
		?>
	</td>
<? } ?> 
</tr>
<?php
//	if (!$restricted){ 
	?>
<tr>
    <td align="right" valign="top">
	  <div id="employdiv2" name="employdiv2" <? if($user[user_type]!=5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?>>
     <?php echo $AppUI->_('Time & Expenses Supervisor');?>:
	  </div>
	</td>
    <td nowrap="nowrap">
	<div id="employdiv3" name="employdiv3" <? if($user[user_type]!=5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?>>
	 <?php echo arraySelect( $SupTypes, 'timexp_supervisor_type', 'size="1" '.$disab.' class="text" onchange="changeSupType()"', $selected_supervisor_type , true,true ,'160px' );?>&nbsp;*&nbsp;&nbsp;
	
	<span id="div_user_supervisor"> 
			<?php echo $AppUI->_("User").": ";?>
			
			<? 
			    $timexp_supervisor = $user[timexp_supervisor];
			   
			    if ($timexp_supervisor != "" && $timexp_supervisor!= '0' && $company_timexp_supervisor =="")
			    {
	                $query_sup = "SELECT user_company FROM users WHERE user_id='".$timexp_supervisor."' "; 
					$sql_sup = mysql_query($query_sup);	
					$c_s = mysql_fetch_array($sql_sup);	
					$company_timexp_supervisor	= $c_s[0];
			    }   
			 ?>  
			
			<select name="company_timexp_supervisor" class="text" style="width:160px;" onchange="select_ajax_timexp_supervisor(document.editFrm.company_timexp_supervisor.value, '')" <?=$disab?>>
				    <option value="" ><?php echo $AppUI->_('Not Specified')?></option>
					<?
					$query = "SELECT company_name, company_id FROM companies ORDER BY company_name ASC"; 
					$sql = mysql_query($query);
					
					if($company_timexp_supervisor == "")
					{
						$company_timexp_supervisor = $user["user_company"];
					}
					 
					while($vec = mysql_fetch_array($sql) )
					{
					 $selected = ($vec['company_id']==$company_timexp_supervisor) ? "selected" : "";
					  echo "<option " .$selected ." value=\"$vec[company_id]\">$vec[company_name]</option>";
					}
					?>
		     </select>	
		     
		     <script type="text/javascript">
			   select_ajax_timexp_supervisor('<?=$company_timexp_supervisor?>','<?=$timexp_supervisor?>');	
			 </script>
			
			<select name="timexp_supervisor" id="timexp_supervisor" class="text" style="width:160px;" <?=$disab?>>
	        </select> 
		</span> 
	</div>
	</td>
    <td align="right">
	 <div id="employdiv4" name="employdiv4" <? if($user[user_type]!=5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?>>
	  <?php echo $AppUI->_('Cost Per Hour');?>:
	 </div></td>
    <td>
	   <div id="employdiv5" name="employdiv5" <? if($user[user_type]!=5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?>>
        <input type="text" class="text" name="user_cost_per_hour" value="<?php echo @$user["user_cost_per_hour"];?>" size="12" align="right" <?=$disab?>/>
	   </div>
    </td>	
</tr>
<tr>
   <td align="right" valign="top">
     <div id="employdiv6" name="employdiv6" <? if($user[user_type]!=5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?>>
	  <?php echo $AppUI->_('Direct report');?>:
	 </div>
	 </td>
   <td><div id="employdiv7" name="employdiv7" <? if($user[user_type]!=5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?>>
		  <?
			 $user_supervisor = $user["user_supervisor"];
			 
			 if ($user_supervisor != "" && $user_supervisor!= '0' && $company_supervisor =="")
			 {
	            $query_sup = "SELECT user_company FROM users WHERE user_id='".$user_supervisor."' "; 
				$sql_sup = mysql_query($query_sup);	
				$c_s = mysql_fetch_array($sql_sup);	
				$company_supervisor	= $c_s[0];
			 }
		  ?>
		  
		   <select name="company_supervisor" class="text" style="width:160px;" onchange="select_ajax_supervisor(document.editFrm.company_supervisor.value, '')" <?=$disab?>>
				    <option value="" ><?php echo $AppUI->_('Not Specified')?></option>
					<?
					$query = "SELECT company_name, company_id FROM companies ORDER BY company_name ASC"; 
					$sql = mysql_query($query);
					
					if($company_supervisor == "")
					{
						$company_supervisor = $user["user_company"];
					}
					 
					while($vec = mysql_fetch_array($sql) )
					{
					 $selected = ($vec['company_id']==$company_supervisor) ? "selected" : "";
					  echo "<option " .$selected ." value=\"$vec[company_id]\">$vec[company_name]</option>";
					}
					?>
		     </select>
		     
		     <script type="text/javascript">
			   select_ajax_supervisor('<?=$company_supervisor?>','<?=$user_supervisor?>');
			 </script>
		  
		 
		    <select name="user_supervisor" id="user_supervisor" class="text" style="width:160px;" <?=$disab?> >
		    </select> *
	</div>
   </td>
  

   <td align="right" valign="top">
      <div id="employdiv0" name="employdiv0" <? if($user[user_type]!=5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?>>
     <?php echo $AppUI->_('Legajo');?>:
	 </div>
  </td>
  <td>  
        <div id="employdiv1" name="employdiv1" <? if($user[user_type]!=5){ ?> style="display:" <?}else{?> style="display: none;" <?} ?> >
        <input type="text" class="text" name="legajo" value="<?php echo @$user["legajo"];?>" size="12" align="right" <?=$disab?> />
		</div>
    </td>	
	
</tr>
</div>
<?php// } else
	//{
	//$user_supervisor = $user["user_supervisor"];
    ?> <!-- <input type="hidden" name="user_supervisor" value="<?=$user_supervisor;?>" /> --><?
	//}
	?> 
	<tr><td colspan="4">
	&nbsp;
	</td></tr>
	
	<tr><th colspan="4">
	<b><?=$AppUI->_('Personal Information')?></b>
	</th></tr>
<tr>
    <td align="right"><?php echo $AppUI->_('First Name');?>:</td>
    <td><input type="text" class="text" name="user_first_name" value="<?php echo $user["user_first_name"];?>" maxlength="50" size="30" />&nbsp;*</td>
    <td align="center" rowspan="7" colspan="2" valign="middle">
      <? if($user["user_pic"]!="ninguna" && $user["user_pic"]!="" && file_exists($uploads_dir."/".$user["user_id"]."/".$user["user_pic"])){?>
      	<img border="1" height="100" width="100" src="<?=$uploads_dir."/".$user["user_id"]?>/<?=$user["user_pic"]?>">
		</br></br><input class="text" type="button" value="<?=$AppUI->_('Remove')?>" onclick="doRemoveFotoChica()">
      <?}else echo "<br><br><br><b>".$AppUI->_("NoPhoto")."</b>"?>
    </td>    
</tr>
<tr>
    <td align="right"><?php echo $AppUI->_('Last Name');?>:</td>
    <td> <input type="text" class="text" name="user_last_name" value="<?php echo $user["user_last_name"];?>" maxlength="50" size="30" />&nbsp;*</td>
</tr>
<tr>
    <td align="right"><?php echo $AppUI->_('Email');?>:</td>
    <td><input type="text" class="text" name="user_email" value="<?php echo $user["user_email"];?>" maxlength="255" size="40" />&nbsp;* </td>
</tr>
<tr>
    <td align="right"><?php echo $AppUI->_('E-mail Alternative 1');?>:</td>
    <td><input type="text" class="text" name="user_email_alternative1" value="<?php echo $user["user_email_alternative1"];?>" maxlength="255" size="40" /></td>
</tr>
<tr>
    <td align="right"><?php echo $AppUI->_('E-mail Alternative 2');?>:</td>
    <td><input type="text" class="text" name="user_email_alternative2" value="<?php echo $user["user_email_alternative2"];?>" maxlength="255" size="40" /></td>
</tr>
<tr>
    <td align="right"><?php echo $AppUI->_('Address');?> 1:</td>
    <td><input type="text" class="text" name="user_address1" value="<?php echo $user["user_address1"];?>" maxlength="30" size="30" /> </td></tr>
<tr>
    <td align="right"><?php echo $AppUI->_('Address');?> 2:</td>
    <td><input type="text" class="text" name="user_address2" value="<?php echo $user["user_address2"];?>" maxlength="30" size="30" /> </td></tr>
<tr>
    <td align="right"><?php echo $AppUI->_('City');?>:</td>
    <td><input type="text" class="text" name="user_city" value="<?php echo $user["user_city"];?>" maxlength="30" size="25" />&nbsp;&nbsp;&nbsp;</TD>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('ZIP');?>:</td>
	<td><input type="text" class="text" name="user_zip" value="<?php echo $user["user_zip"];?>" maxlength="11" size="10" /></td>
</tr>
<?php /*<uenrico>*/ ?>
<tr>
	<td align="right"><?php echo $AppUI->_('Country');?>:</td>
    <td><?php echo $Clocation->generateHTMLcboCountries($user["user_country_id"], "text"); ?></td>
	<td nowrap="nowrap" class="right" valign="top"><?=$AppUI->_("Photo");?> (100x100):&nbsp;</td>
	<td colspan="3" valign="top">
		<input class="text" size="25" type="file" name="user_pic">
	</td>
<tr>
<td align="right"><?php echo $AppUI->_('State');?>:</td>
    <td><?php echo $Clocation->generateHTMLcboStates("", "text"); ?></td>
    <td align="right">
       <?php echo $AppUI->_('Nationality');?>:
    </td>
    <td>
       <!--<input type="text" class="text" name="nationality" value="<?php echo $user["nationality"];?>" maxlength="24" size="20" />-->
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
					  if ($vec[nationality_id]==$user["nationality"]){
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
<?php 
	echo $Clocation->generateJScallFunctions();
?>
<?php /*</uenrico>*/ ?>
<tr>
    <td align="right"><?php echo $AppUI->_('Phone');?>:</td>
    <td><input type="text" class="text" name="user_phone" value="<?php echo $user["user_phone"];?>" maxlength="30" size="20" /> </td>
    <td align="right"><?php echo $AppUI->_('Home Phone');?>:</td>
    <td><input type="text" class="text" name="user_home_phone" value="<?php echo $user["user_home_phone"];?>" maxlength="30" size="20" /> </td></tr>
<tr>
    <td align="right"><?php echo $AppUI->_('IM Type');?>:</td>
    <td><?=arraySelect( $IMtypes, 'user_im_type', 'size="1" class="text"', @$user["user_im_type"], true,'','65px' )."&nbsp;".$AppUI->_('IM Id');?>: <input type="text" class="text" name="user_im_id" value="<?php echo $user["user_im_id"];?>" maxlength="50" size="25"> </td>
    <td align="right"><?php echo $AppUI->_('Mobile');?>:</td>
    <td><input type="text" class="text" name="user_mobile" value="<?php echo $user["user_mobile"];?>" maxlength="30" size="20" /> </td>
</tr>
<tr>
 <td align="right"><?php echo $AppUI->_('Birthday');?>:</td>
    <td>
		&nbsp;<input type="text" name="birthday" value="<?php echo $user_birthday ? $user_birthday->format( $df ) : "" ;?>" class="text" disabled="disabled" size="12" />
					<a href="javascript: //" onClick="popCalendar('birthday')"><img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0"></a> 
					<input type="hidden" name="user_birthday" value="<?php echo $user_birthday ? $user_birthday->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />&nbsp;
		<? /*
		<input type="text" class="text" name="user_birthday" value="<?php if(intval($user["user_birthday"])!=0) { echo substr($user["user_birthday"],0,10);}?>" maxlength="50" size="12" />(YYYY-MM-DD)
		*/ ?>
	</td>
	<td align="right"><?php echo $AppUI->_('Marital State');?>:</td>
	<td><?=arraySelect( $estCivil, 'maritalstate', 'size="1" class="text"', @$user["maritalstate"], true,'','120px' );?></td>
</tr>
<tr>
 <td align="right">
 	<select name="doctype" size="1" class="text" >
      <?     
      $doctype=$user["doctype"];
        if($doctype=="DNI") echo '<option value="DNI" selected>DNI</option>';
        else                echo '<option value="DNI">DNI</option>';
        if($doctype=="LC")  echo '<option value="LC" selected>LC</option>';
        else                echo '<option value="LC">LC</option>';
        if($doctype=="LE")  echo '<option value="LE" selected>LE</option>';
        else                echo '<option value="LE">LE</option>';
      ?>
    </select>:</td>
    <td colspan="3">
    <input class="text" size="12" maxlength="16" type="text" name="docnumber" value="<?= $user["docnumber"] ?>"></td>
</tr>
<? /* Removido por implementacion de calendarios *//*
	<tr><td colspan="4">
	<b><?=$AppUI->_('Work')?></b>
	</td></tr>
	
<tr>
    <td align="right" rowspan="2" valign=top><?php echo $AppUI->_('Comments');?>:</td>
    <td rowspan="2"><textarea class="text" rows="6" cols="45" style="height: 50px" name="comments"><?=$user['comments']?></textarea> </td>
	<td id="wrkSchTit" align="right" valign="top"><?php echo $AppUI->_('Work schedule');?>:</td>
	<td id="wrkSchCont" valign=top>
		<table border="0">
		<tr>
			<td>&nbsp;</td>
			<td><?=$AppUI->_('Start')?></td>
			<td><?=$AppUI->_('End')?></td>			
		</tr>
		<tr>
			<td><?php echo $AppUI->_('Turn');?> 1</td>
			<td><?=arraySelect( $times, 'start_time_am', 'size="1" class="text"', 
			$start_time_am ? $start_time_am->format("%H%M%S") : $times["NULL"] )?></td>
			<td><?=arraySelect( $times, 'end_time_am', 'size="1" class="text"', 
			$end_time_am ? $end_time_am->format("%H%M%S") : $times["NULL"] )?></td>		
		</tr>
		<tr>
			<td><?php echo $AppUI->_('Turn');?> 2</td>
			<td><?=arraySelect( $times, 'start_time_pm', 'size="1" class="text"', 
			$start_time_pm ? $start_time_pm->format("%H%M%S") : $times["NULL"] )?></td>
			<td><?=arraySelect( $times, 'end_time_pm', 'size="1" class="text"', 
			$end_time_pm ? $end_time_pm->format("%H%M%S") : $times["NULL"] )?></td>		
		</tr>	
		</table>		
	</td>
</tr>
<tr>
	<td id="wrkTime1" align="right" valign=top><?php echo $AppUI->_('Daily working hours');?>:</td>
	<td id="wrkTime2" valign=top>&nbsp;<input class="text" type="text" name="daily_working_hours" value="<?=$user["daily_working_hours"]?>" size="4" maxlength="4">&nbsp;<input class="text" type="button" value="<?=$AppUI->_('Calculate')?>"onclick="calculateWorkingHours();">
	</td>
</tr>
*/
/* FIn Removido por implementacion de calendarios */
 ?>
 
<? /* ?>
<tr>
    <td align="right"><?php echo $AppUI->_('Curriculum Vitae');?>:</td>
    <td colspan="3">&nbsp;<input class="button" type="file" name="resume" style="width:270px;border:1px">
    <?php
      if($user["resume"]<>"ninguna" && $user["resume"]<>"" && file_exists("$uploads_dir/$user_id/".rawurlencode($user["resume"])) { 
        echo "&nbsp;Actual: <b><a href='$uploads_dir/$user_id/".rawurlencode($user["resume"])."'>{$user["resume"]}</a>";
        echo '</b>&nbsp;<input class="text" type="button" value="'.$AppUI->_("delete").'" onclick="doRemoveResume()">';
      }
    ?>
    </td>
    </tr>    
<tr>
    <td align="right"><?php echo $AppUI->_('Photo');?> (140x140):</td>
    <td colspan="3">&nbsp;<input class="button" type="file" name="user_pic" style="width:270px;border:1px">
    <?php
      if($user["user_pic"]<>"ninguna" && $user["user_pic"]<>"" && file_exists($uploads_dir."/".$user["user_id"]."/".$user["user_pic"])) { 
        echo "&nbsp;Actual: <b><a href='$uploads_dir/$user_id/".rawurlencode($user["user_pic"])."'>{$user["user_pic"]}</a>";
        echo '</b>&nbsp;<input class="text" type="button" value="'.$AppUI->_("delete").'" onclick="doRemoveFotoChica()">';
      }
    ?>
    </td>
    </tr>       
	
<? */ ?>
	<tr><td colspan="4">
	&nbsp;
	</td></tr>	
	<tr><th colspan="4">
	<b><?=$AppUI->_('Email Configuration')?></b>
	</th></tr>	
<tr>
	<td align="right"><?php echo $AppUI->_('SMTP Server');?>:</td>
	<td colspan="3"><input type="text" class="text" name="user_smtp" value="<?php echo $user["user_smtp"];?>" size=30 maxlength="255" />&nbsp;
	<a href="javascript:setEmailConf();" title="<?php echo $AppUI->_('Back to Company\'s default email server values');?>"><?php echo $AppUI->_('Company\'s default values');?></a></td>
</tr>
<tr>
	<td align="right" valign="top"><?php echo $AppUI->_('SMTP Authentication');?>:</td>
	<td colspan="3">
		<input type="checkbox" name="smtp_auth_check" value="1" <?php echo @$user["user_smtp_auth"] == "1" ? "checked" : "";?> onchange="switchSmtpAuth();" onclick="switchSmtpAuth();"/><br>
		<input type="hidden" name="user_smtp_auth" value="<?php echo @$user["user_smtp_auth"];?>" />
		<input type="hidden" name="user_smtp_use_pop_values" value="<?php echo @$user["user_smtp_use_pop_values"];?>" />
		<div id="smtp_auth" name="smtp_auth">
		<input type="radio" name="use_pop_values" value="1" <?php echo @$user["user_smtp_use_pop_values"] != "0" ? "checked" : "";?> onclick="showSmtpSettings();" />
			<?php echo $AppUI->_('Use settings of mail retrieval');?><br>
		<input type="radio" name="use_pop_values" value="0" <?php echo @$user["user_smtp_use_pop_values"] == "0" ? "checked" : "";?> onclick="showSmtpSettings();" />
			<?php echo $AppUI->_('Use specific settings');?><br>
			<div id="smtp_settings" name="smtp_settings">	
			<table cellpadding="0" cellspacing="2" border="0">
			<tr>
				<td align="right"><?php echo $AppUI->_('Username');?>:</td>
				<td>
					<input type="text" class="text" name="user_smtp_username" value="<?php echo $user["user_smtp_username"];?>" size=30 maxlength="64" />
				</td>
			</tr>
			<tr>
				<td align="right"><?php echo $AppUI->_('Password');?>:</td>
				<td>
					<input type="password" class="text" name="user_smtp_password" value="<?php echo $user["user_smtp_password"];?>" size=30 maxlength="64"  />
				</td>	
			</tr>	
			</table>
			</div>
		</div>
	</td>
</tr>
	
<tr>
	<td align="right"><?php echo $AppUI->_('Email Protocol');?>:</td>
	<td colspan="3">
        <select class="text" name="user_mail_server_port" >
        <option value="110" <?if($user["user_mail_server_port"]==110) echo "selected"?>>POP3</option>
	<option value="143" <?if($user["user_mail_server_port"]==143) echo "selected"?>>IMAP</option>
        </select>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('POP3 Server');?>:</td>
	<td colspan="3"><input type="text" class="text" name="user_pop3" value="<?php echo $user["user_pop3"];?>" size=30 maxlength="255" /></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('IMAP Server');?>:</td>
	<td colspan="3"><input type="text" class="text" name="user_imap" value="<?php echo $user["user_imap"];?>" size=30 maxlength="255" /></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Email username');?>:</td>
	<td><input type="text" class="text" name="user_email_user" value="<?php echo $user["user_email_user"];?>" size=30 maxlength="255" /></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Email password');?>:</td>
	<td colspan="3"><input type="password" class="text" name="user_email_password" value="<?php echo $user["user_email_password"];?>" size=30 maxlength="255" /></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Email password2');?>:</td>
	<td colspan="3"><input type="password" class="text" name="user_email_password_check" value="<?php echo $user["user_email_password"];?>" size=30 maxlength="255" /></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Webmail autologin');?>:</td>
	<td colspan="3"><select class="text" name="user_webmail_autologin">
        <option value="Yes" <?php if($user["user_webmail_autologin"]=="Yes")echo " selected ";?>>Yes</option>
        <option value="No"  <?php if($user["user_webmail_autologin"]=="No")echo " selected ";?>>No </option>
        </td>
</tr>
<tr>
    <td align="right" valign=top><?php echo $AppUI->_('Signature');?>:</td>
    <td colspan="3"><textarea class="text" cols=50 name="user_signature" style="height: 50px"><?php echo @$user["user_signature"];?></textarea></td>
</tr>
<tr>
    <td align="left">
         <? if($_GET['from']!='tasks'){?>
        <input type="button" value="<?php echo $AppUI->_('back');?>" onClick="javascript:history.back(-1);" class="button" />
        <?}?>
    </td>
    <td align="center"  colspan="2">&nbsp;
<?  if(!$user["user_id"]){ ?>    
    <input type="checkbox" name="return2" value="1" <?php echo ($another_user ? "checked" : "")?> 
 			onclick="syncreturn(this)" onchange="syncreturn(this)"/>
    <?php echo $AppUI->_('Return here to add other user')?>
<?  }?> 
    </td>
    <td align="right">
        <input type="button" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt()" class="button" />
    </td>
</tr>
</form>
</table>
<script language="javascript">
<!--
	changeSupType();
	switchSmtpAuth();
	showSmtpSettings();
<?php 
if ($another_user || !$user_id) {
	echo "document.editFrm.user_username.focus()";
}?>	
// --></script>
<?php } ?>
