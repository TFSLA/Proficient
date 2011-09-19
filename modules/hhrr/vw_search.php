<?php
global $AppUI, $cphrelations, $utypes, $slrrelations;
require_once("./functions/admin_func.php");

define( 'FMT_DATETIME_MYSQL_PSA', '%Y-%m-%d' ); //Esto lo uso cuando busco por un rango de fechas, que no le de pelota a la hora

$estCivil = dPgetSysVal( 'MaritalState' );
$wrkTypes = dPgetSysVal( 'WorkType' );
$wrkTypes = arrayMerge(array("-1"=>"Any"), $wrkTypes);
$SCandidateStatus = dPgetSysVal( 'CandidateStatus' );
$SCandidateStatus = arrayMerge(array("-1"=>"Any"), $SCandidateStatus);

$wrkPrefOptions = array("-1"=>"Any", "1"=>"Yes", "0"=>"No");
$arCandidateAge = array("0"=>"Not Specified",
						"1"=>"18-25",
						"2"=>"25-30",
						"3"=>"30-40",
						"4"=>"40-50",
						"5"=>"50+"
						);
/*<uenrico>*/
// load locations arrays

$Clocation = new CLocation();

$Clocation->loadCountries();
$Clocation->addItemAtBeginOfCountries($Clocation->addItem(0, "Not Specified"));
$Clocation->addItemAtBeginOfCountries($Clocation->addItem(-1, "Any"));

$Clocation->addItem(-1, "Any", true);
$Clocation->addItem(0, "Not Specified", true);
$Clocation->loadStates();
$Clocation->addItemAtBeginOfStates($Clocation->addItemState("0","0","Not Specified"));
$Clocation->addItemAtBeginOfStates($Clocation->addItemState("-1","-1","Any"));
/*</uenrico>*/

//Ciudades
$strSqlCity = "SELECT  DISTINCT ( TRIM(user_city) )
				FROM users
				WHERE user_city <> ''
				";
$arCities = db_loadHashList($strSqlCity);
$arCities = arrayMerge(array("-1" => "Any", "0" => "Not Specified"), $arCities);
				
$uploads_dir = $AppUI->getConfig('hhrr_uploads_dir');

$cantfilas = 30;
$df = $AppUI->getPref('SHDATEFORMAT');



$changepage = $_POST["changepage"];
$skilllist=$_POST["skilllist"];
$costperhourrel=$_POST["costperhourrel"];
$costperhour =$_POST["costperhour"];
$salarywantedrel=$_POST["salarywantedrel"];
$salarywanted=$_POST["salarywanted"];
$maritalstate=$_POST["maritalstate"];
$searchhhrr=$_POST["searchhhrr"];
$wantsfulltime=intval( dPgetParam( $_POST, "wantsfulltime", -1 ) );
$wantsparttime=intval( dPgetParam( $_POST, "wantsparttime", -1 ) );
$wantsfreelance=intval( dPgetParam( $_POST, "wantsfreelance", -1 ) );
$firstChoice = $_POST["firstChoice"];
$secondChoice = $_POST["secondChoice"];
$user_type = dPgetParam( $_POST,"user_type", "2" );
$actualjob =  dPgetParam( $_POST,"actualjob", "-1" );
$candidatestatus = dPgetParam( $_POST,"candidatestatus", -1);
$candidateage = dPgetParam( $_POST, "candidateage", 0);
$country_id = dPgetParam($_POST, "country_id", null);
$state_id = dPgetParam($_POST, "state_id", null);
$city = dPgetParam($_POST, "city", null);
$user_name = dPgetParam($_POST, "user_name", null);
$user_email = dPgetParam($_POST, "user_email", null);
$user_legajo = dPgetParam($_POST, "user_legajo", null);
$actual_company = dPgetParam($_POST, "actual_company", null);
$start_time_aw = dPgetParam($_POST, "start_time_aw", null); 
$end_time_aw = dPgetParam($_POST, "end_time_aw", null); 
$user_function = dPgetParam($_POST, "user_function", null);
$functional_area = dPgetParam($_POST, "functional_area", null);
$level_management = dPgetParam($_POST, "level_management", null);
$reports = dPgetParam($_POST, "reports", null);
$salarycurrentrel = dPgetParam($_POST, "salarycurrentrel", null);
$salarycurrent = dPgetParam($_POST, "salarycurrent", null);
$lastuptdate_salary_txt = dPgetParam($_POST, "lastuptdate_salary_txt", null);
$lastuptdate_salary = dPgetParam($_POST, "lastuptdate_salary", null);
$gaptoppcactual = dPgetParam($_POST, "gaptoppcactual", null);
$gaptoppcactual_c = dPgetParam($_POST, "gaptoppcactual_c", null);
$lastreward = dPgetParam($_POST, "lastreward", null);
$lastreward_c = dPgetParam($_POST, "lastreward_c", null);
$actualbenefits = dPgetParam($_POST, "actualbenefits", null);
$actualbenefits_c = dPgetParam($_POST, "actualbenefits_c", null);
$level = dPgetParam($_POST, "level", null);
$hoursavailableperdayl = dPgetParam($_POST, "hoursavailableperdayl", null);
$hoursavailableperday = dPgetParam($_POST, "hoursavailableperday", null);
$internal_company = dPgetParam($_POST, "internal_company", null);
$department = dPgetParam($_POST, "department", null);
$taxidnumber = dPgetParam($_POST, "taxidnumber", null);
$performance = dPgetParam($_POST, "performance", null);
$potential = dPgetParam($_POST, "potential", null);
$title = dPgetParam($_POST, "title", null);
$status = dPgetParam($_POST, "status", null);
$seminary = dPgetParam($_POST, "seminary", null);
$activity = dPgetParam($_POST, "activity", null);
$person__position = dPgetParam($_POST, "person__position", null);
$key_position = dPgetParam($_POST, "key_position", null);
$user_last_name = dPgetParam($_POST, "user_last_name", null);
 			        
$actual_department = dPgetParam($_POST, "actual_department", null);  
$actual_functional_area = dPgetParam($_POST, "actual_functional_area", null);

//fechas
$inputdateFrom = dPgetParam($_POST, "inputdateFrom", null);
$inputdateFrom_txt = dPgetParam($_POST, "inputdateFrom_txt", null);
$inputdateTo = dPgetParam($_POST, "inputdateTo", null);
$updatedateFrom = dPgetParam($_POST, "updatedateFrom", null);
$updatedateFrom_txt = dPgetParam($_POST, "updatedateFrom_txt", null);
$updatedateTo = dPgetParam($_POST, "updatedateTo", null);
$person_position = dPgetParam($_POST, "person_position", null);
$key_position = dPgetParam($_POST, "key_position", null);


if ($user_type == '5'){
	$actual_company = $_POST['actual_company_candidatos'];
}

if ($inputdateFrom == "")
	$inputdateFrom = NULL;
if ($updatedateFrom == "")
	$updatedateFrom = NULL;
	

if($inputdateFrom) $inputdateFrom = new CDate($inputdateFrom);
$inputdateTo = new CDate($inputdateTo);
if($updatedateFrom) $updatedateFrom = new CDate($updatedateFrom);
$updatedateTo = new CDate($updatedateTo);


if($lastuptdate_salary) $lastuptdate_salary = new CDate($lastuptdate_salary);


$CDate_tmp = new CDate(null);


function getAgeSqlString($intArIndex){
	$strSql = "\nAND users.user_birthday IS NOT NULL AND users.user_birthday <> 0 ";
	if($intArIndex != ""){
		switch($intArIndex){
			case "1":
					$strSql .= "AND (YEAR(NOW())-YEAR(users.user_birthday)) >= 18 
								AND (YEAR(NOW())-YEAR(users.user_birthday)) <= 25";
					break;
			case "2":
					$strSql .= "AND (YEAR(NOW())-YEAR(users.user_birthday)) >= 25 
								AND (YEAR(NOW())-YEAR(users.user_birthday)) <= 30";
					break;
			case "3":
					$strSql .= "AND (YEAR(NOW())-YEAR(users.user_birthday)) >= 30 
								AND (YEAR(NOW())-YEAR(users.user_birthday)) <= 40";
					break;
			case "4":
					$strSql .= "AND (YEAR(NOW())-YEAR(users.user_birthday)) >= 40 
								AND (YEAR(NOW())-YEAR(users.user_birthday)) <= 50";
					break;
			case "5":
					$strSql .= "AND (YEAR(NOW())-YEAR(users.user_birthday)) >= 50";
					break;
		}
	}
	return $strSql;
}


$times = array();
$t = new CDate();
$t->setTime( 6,0,0 );
if (!defined('LOCALE_TIME_FORMAT'))
  define('LOCALE_TIME_FORMAT', '%I:%M %p');
$times[""]="";
for ($j=0; $j < 60; $j++) {
	$times[$t->format( "%H%M%S" )] = $t->format( LOCALE_TIME_FORMAT );
	$t->addSeconds( 1800 );
}


?>
<html>
<head>
<SCRIPT LANGUAGE="JavaScript">
<?="<!-- Begin"?> 
<?
  $result = mysql_query("SELECT * from skills ORDER BY description;");
  $i=0;
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
  {
    $i++;
    if($i==1) $desc = $desc . $row["description"];
    else      $desc = $desc . "|" . $row["description"];

    if($i==1) $skillcat = $skillcat . $row["idskillcategory"];
    else      $skillcat = $skillcat . "|" . $row["idskillcategory"];

    if($i==1) $skillid = $skillid . $row["id"];
    else      $skillid = $skillid . "|" . $row["id"];

    if($i==1) $valuedesc = $valuedesc . $row["valuedesc"];
    else      $valuedesc = $valuedesc . "|" . $row["valuedesc"];

    if($i==1) $valueoptions = $valueoptions . $row["valueoptions"];
    else      $valueoptions = $valueoptions . "|" . $row["valueoptions"];

  }
  echo "desc = '" . $desc . "';\r\n";
  echo "skillcat = '" . $skillcat . "';\r\n";
  echo "skillid = '" . $skillid . "';\r\n";
  echo "valuedesc = '" . $valuedesc . "';\r\n";
  echo "valueoptions = '" . $valueoptions . "';\r\n";
?>

var activeskills      = new Array();
var valuedescarray    = new Array(); 
var skillidarray      = new Array(); 
var descarray         = new Array(); 
var skillcatarray     = new Array(); 
var valueoptionsarray = new Array(); 
var skillvalue 		  = new Array();
var commentvalue 		  = new Array();
var categvalue 		  = new Array();

<?
  echo "/*";	var_dump($skilllist); echo "*/";
  if($skilllist!="")
  {
    $skills = split(",",$skilllist);
    for ($loop=0; $loop < count($skills); $loop++){
      echo "activeskills[activeskills.length] = $skills[$loop];\r\n";
      
      echo "skillvalue[skillvalue.length] = '".$_POST["value".$skills[$loop]]."';\r\n";
	  echo "commentvalue[".$skills[$loop]."] = '".$_POST["comment".$skills[$loop]]."';\r\n";
	  echo "categvalue[".$skills[$loop]."] = '".$_POST["cat".$skills[$loop]]."';\r\n";
    }
  }
?>

descarray         = desc.split("|");
skillcatarray     = skillcat.split("|");
valueoptionsarray = valueoptions.split("|");
skillidarray      = skillid.split("|");
valuedescarray    = valuedesc.split("|");

function selectChange()
{
	var myEle;
	var x;
	var control = document.hhrrsearch.firstChoice;
	var controlToPopulate = document.hhrrsearch.secondChoice;
	var skillunused;
	     
	for (var q=controlToPopulate.options.length; q>=0; q--) 
	{
	  	controlToPopulate.options[q]=null;
	}		

	for ( x = 0 ;x < skillcatarray.length; x++ )
    {
    	//Busco la categoria de habilidades    	
    	if ( skillcatarray[x] == control.value )
    	{       
    		//alert( "Estoy en la categoria " + skillcatarray[x] );
    		skillunused = true;    	
        	//Busco si est?activa esta habilidad        	
          	for( q=0; q < activeskills.length; q++ )
          	{          		
          		//Aca hay que buscar en el array de skills
          		for ( var z = 0; z < skillidarray.length && skillunused; z++ )
          		{
            		skillunused = activeskills[q] != skillidarray[x];            		
            	}            
	        }	        
            if( skillunused )
          	{
          		myEle = new Option( descarray[x], skillidarray[x] );
	            controlToPopulate.options[controlToPopulate.length] = myEle;
	            if ( myEle.value == "<?=$secondChoice?>" )
	            {            	
	            	myEle.selected = true;
	            }
          	}
        }
    }    
}

function addskill(id)
{	
	if (id.length > 0){
		activeskills[activeskills.length] = id;
  	refreshfilters();  	
  	selectChange("<?=$secondChoice?>");
	}
	
}

function removeskill(id)
{
  for(i=0;i<activeskills.length;i++){
    if(activeskills[i] == id){ activeskills[i] = -1; activeskills.splice(i,1);}
  }
  refreshfilters();
  selectChange( null );
}


function refreshfilters()
{	
  var comand; 
  var element; 
  var text; 
  var sklist="";

  var content = "";
  for(i=0;i<activeskills.length;i++)
  {
    if(activeskills[i]!=-1)
    {
      sklist = sklist + activeskills[i] + ",";
    }
  }
  sklist = sklist.substr(0,sklist.length-1);
  document.hhrrsearch.skilllist.value=sklist;

  
  content = "<br><table align='center' border='0' cellpadding='1' cellspacing='0' width='100%' bgcolor='#ffffff'>" ;
	if (activeskills.length>0){
		content = content + "<tr class='tableHeaderGral'>";
		content = content + "<td width='20px'>&nbsp;</td>";
		content = content + "<th width='15%'><?=$AppUI->_('Category')?></th>";
		content = content + "<th width='25%'><?=$AppUI->_('Skill')?></th>";
		content = content + "<th ><?=$AppUI->_('Value')?></th>";
		content = content + "<th ></th>";
		content = content + "</tr>";
	}
  for(i=0;i<activeskills.length;i++)
  {
    if(activeskills[i]!=-1)
    {
      for(x=0;x<descarray.length;x++)
      {
        if(skillidarray[x]==activeskills[i])
        {
          var items = valueoptionsarray[x].split(",");	
          content = content + "<tr>";

					content = content + "<td width='20px'><a href='javascript:  //' onclick='javascript: removeskill(" + skillidarray[x] + ")' alt=\"<?=$AppUI->_('remove')?>\"><img src=\"images/icons/trash_small.gif\" border=\"0\" alt=\"<?=$AppUI->_('remove')?>\"  ></a></td>";			
					
					var cattxt = eval("document.hhrrsearch.cat" + skillidarray[x] );
					if (!cattxt)
						if(categvalue[skillidarray[x]])
							var categ = categvalue[skillidarray[x]];
						else
							var categ = document.hhrrsearch.firstChoice.options[document.hhrrsearch.firstChoice.selectedIndex].text;
						
					else
						var categ = cattxt.value;

					content = content + "<td width='15%'>&nbsp;" + categ + "<input type='hidden' name='cat"+skillidarray[x]+"' value='"+categ+"' ></td>";
					content = content + "<td width='25%'>&nbsp;" + descarray[x] + "</td>";
					
					content = content + "<td>" + valuedescarray[x] + ":&nbsp;<select name='value" + skillidarray[x] + "' class='text'>" ;

          for (var loop=0; loop < items.length; loop++)
          {
            content = content + '            <option ';
			
            if ( loop + 1 == skillvalue[i] )
            	content += "selected";
            else
            {
            	var tmpcbo = eval("document.hhrrsearch.value" + skillidarray[x] );
            	if (tmpcbo)
            	if (tmpcbo.selectedIndex == loop)
            		content += "selected";	
            }
            content = content + '  value="' + (loop+1) + '">';
            content = content + items[loop];
            content = content + '</option>';
          }
					var commenttxt = eval("document.hhrrsearch.comment" + skillidarray[x] );
					if (!commenttxt)
						if (commentvalue[skillidarray[x]] ) 
							var comment = commentvalue[skillidarray[x]];
						else
							var comment = "";
					else
						var comment = commenttxt.value;
          content = content + "</select> <?=$AppUI->_("or above")?></td>";
					content = content + "<td></td>";
					
					content = content + "</tr><tr class='tableRowLineCell'><td colspan='5'></td></tr>";
        }
      }
    }
  } 

	
  content = content + "</table>";
  document.getElementById("elDiv").innerHTML = content ;
}

function showSearchPanel()
{
      document.getElementById("part0").style.display = '';
      document.getElementById("part1").style.display = '';
      document.getElementById("elDiv").style.display = '';
      document.getElementById("search").style.display = 'none';
}

function hideSearchPanel()
{
      document.getElementById("part0").style.display = 'none';
      document.getElementById("part1").style.display = 'none';
      document.getElementById("elDiv").style.display = 'none';
      document.getElementById("search").style.display = '';
}



function viewpage(nro){
	var form = document.hhrrsearch;
	form.page.value = nro;
	form.changepage.value = 1;
	form.submit();
}


function searchnow(){
	var form = document.hhrrsearch;
	form.page.value = 1;
	form.changepage.value = 0;
	form.submit();
}


//  End -->

function popCalendar( field ){
    calendarField = field;
    idate = eval( 'document.hhrrsearch.' + field + '.value' );
    window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *    @param string Input date in the format YYYYMMDD
 *    @param string Formatted date
 */
function setCalendar( idate, fdate ) {
    fld_date = eval( 'document.hhrrsearch.' + calendarField );
    fld_fdate = eval( 'document.hhrrsearch.' + calendarField + '_txt');
    fld_date.value = idate;
    fld_fdate.value = fdate;
  
}
 
<?php echo $CDate_tmp->buildManualDateValidationJS(); ?>

function submitIt(){
	
	frm = document.forms["hhrrsearch"];
	errMsg = "";
	var rta = true; 

    strMDVparam1 = frm.inputdateFrom_txt;
    strMDVparam2 = frm.dateformat;
    strMDVparam3 = frm.inputdateFrom;
    if(<?php echo $CDate_tmp->buildFunctionMDVJS(); ?> && trim(strMDVparam1.value) != ""){
        errMsg = "<?php echo $AppUI->_('DateError');?>\n";
        frm.inputdateFrom_txt.focus();
			
			alert1(errMsg);
			rta = false;
    }
		
    strMDVparam1 = frm.inputdateTo_txt;
    strMDVparam2 = frm.dateformat;
    strMDVparam3 = frm.inputdateTo;
    if(<?php echo $CDate_tmp->buildFunctionMDVJS(); ?>  && trim(strMDVparam1.value) != ""){
        errMsg = "<?php echo $AppUI->_('DateError');?>\n";
        frm.inputdateTo_txt.focus();
			
			alert1(errMsg);
			rta = false;
    }
    
    strMDVparam1 = frm.updatedateFrom_txt;
    strMDVparam2 = frm.dateformat;
    strMDVparam3 = frm.updatedateFrom;
    if(<?php echo $CDate_tmp->buildFunctionMDVJS(); ?>  && trim(strMDVparam1.value) != ""){
        errMsg = "<?php echo $AppUI->_('DateError');?>\n";
        frm.updatedateFrom_txt.focus();
			
			alert1(errMsg);
			rta = false;
    }

    strMDVparam1 = frm.updatedateTo_txt;
    strMDVparam2 = frm.dateformat;
    strMDVparam3 = frm.updatedateTo;
    
    if(<?php echo $CDate_tmp->buildFunctionMDVJS(); ?>  && trim(strMDVparam1.value) != ""){
      errMsg = "<?php echo $AppUI->_('DateError');?>\n";
      frm.updatedateTo_txt.focus();
			alert1(errMsg);
			rta = false;
    }
    //Con esto creo el objeto de JS Date(año,mes,dia) Pero los meses los cuenta (0 11)
    //Todo este lio lo hago x que para js 01 es distinto que 1 asi que no puedo comparar las fechas de la forma habitual, para eso lo paso al objeto fecha
        var vec_fecha=frm.inputdateFrom_txt.value.split("/");
		var inputdateFrom_txt=new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
		
		var vec_fecha=frm.inputdateTo_txt.value.split("/");
		var inputdateTo_txt=new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
		
		var vec_fecha=frm.updatedateFrom_txt.value.split("/");
		var updatedateFrom_txt=new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);
		
		var vec_fecha=frm.updatedateTo_txt.value.split("/");
		var updatedateTo_txt=new Date(vec_fecha[2],vec_fecha[1]-1,vec_fecha[0]);

		var today = new Date()
        
		//Con esto verifico que las fechas no sean > a HOY
		if (frm.inputdateFrom_txt.value!= "" && inputdateFrom_txt > today)
        {
		    errMsg = "<?php echo $AppUI->_('DateErrorMayor');?>\n";
			alert1(errMsg);
			rta = false;
		}else if (frm.inputdateTo_txt.value!= "" && inputdateTo_txt > today)
        {
	        errMsg = "<?php echo $AppUI->_('DateErrorMayor');?>\n";
			alert1(errMsg);
			rta = false;
		}else if ( frm.updatedateFrom_txt.value!= "" && updatedateFrom_txt >today)
        {
	        errMsg = "<?php echo $AppUI->_('DateErrorMayor');?>\n";
			alert1(errMsg);
			rta = false;
		}else if (frm.updatedateTo_txt.value!= "" && updatedateTo_txt >today)
        {
	        errMsg = "<?php echo $AppUI->_('DateErrorMayor');?>\n";
			alert1(errMsg);
			rta = false;
		}else if (frm.inputdateFrom_txt.value!= "" &&  inputdateFrom_txt > inputdateTo_txt)
        {
	        errMsg = "<?php echo $AppUI->_('DateErrorFromTo');?>\n";
			alert1(errMsg);
			rta = false;
		}else if ( ((!numerico(frm.salarycurrent)) || (frm.salarycurrentrel.value !="0" && frm.salarycurrent.value =="")) && frm.user_type.value != '5' ){
			errMsg = "<?php echo $AppUI->_('The field current salary must by a integer');?>\n";
			alert1(errMsg);
			rta = false;
			
		}else if ( ((!numerico(frm.gaptoppcactual_c )) || (frm.gaptoppcactual.value !="0" && frm.gaptoppcactual_c.value ==""))&& frm.user_type.value != '5'  ){
			errMsg = "<?php echo $AppUI->_('The field gaptoppcactual must by a integer');?>\n";
			alert1(errMsg);
			rta = false;
			
		}else if ( ((!numerico(frm.lastreward_c)) || (frm.lastreward.value !="0" && frm.lastreward_c.value ==""))&& frm.user_type.value != '5'  ){
			errMsg = "<?php echo $AppUI->_('The field lastreward must by a integer');?>\n";
			alert1(errMsg);
			rta = false;
			
		}else if ( ((!numerico(frm.actualbenefits_c)) || (frm.actualbenefits.value !="0" && frm.actualbenefits_c.value == "") ) && frm.user_type.value != '5'  ){
			errMsg = "<?php echo $AppUI->_('The field actualbenefits must by a integer');?>\n";
			alert1(errMsg);
			rta = false;
			
		}else if ( ((!numerico(frm.costperhour) || (frm.costperhourrel.value !="0" && frm.costperhour.value ==""  ))) && frm.user_type.value == '5' ){
			errMsg = "<?php echo $AppUI->_('The field costperhour must by a integer');?>\n";
			alert1(errMsg);
			rta = false;
			
		}else if ( ((!numerico(frm.hoursavailableperday)) || (frm.hoursavailableperday.value =="" && frm.hoursavailableperdayl.value != "0") )&& frm.user_type.value == '5'  ){
			errMsg = "<?php echo $AppUI->_('The field hoursavailableperday must by a integer');?>\n";
			alert1(errMsg);
			rta = false;
			
		}else if ( ((!numerico(frm.salarywanted)) || ( frm.salarywantedrel.value !="0" && frm.salarywanted.value==""))&& frm.user_type.value == '5'  ){
			errMsg = "<?php echo $AppUI->_('The field salarywanted must by a integer');?>\n";
			alert1(errMsg);
			rta = false;
			
		}else if ( (frm.updatedateFrom_txt.value!= "") && (updatedateFrom_txt > updatedateTo_txt) )
        {  
	        errMsg = "<?php echo $AppUI->_('DateErrorFromTo');?>\n";
			alert1(errMsg);
			rta = false;
		}else{
			rta = true;
		}
		
		
		//Si llego hasta aca si errores es x que esta todo OK
		if (rta)
		{
		frm.page.value = 1;
	    frm.changepage.value = 1;
	    frm.submit();
		}
}

function numerico(campo) {
 
	var charpos = campo.value.search("[^0-9]"); 
    if (campo.value.length > 0 &&  charpos >= 0)  { 
	    return false; 
	} else {
		return true;
	}
}

function formReset(){
	frm = document.forms["hhrrsearch"];
	frm.user_name.value = "";
	frm.user_last_name.value = "";
	frm.taxidnumber.value = "";
	frm.user_email.value = "";
	frm.user_type.value = "2";
	frm.costperhourrel.value = 0;
	frm.costperhour.value = "";
	frm.country_id.value = -1;
	frm.state_id.value = -1;
    frm.city.value = "";
    frm.candidateage.value = 0;
	frm.maritalstate.value = 0;
	frm.wantsfulltime.value = -1;
	frm.wantsparttime.value = -1;
	frm.wantsfreelance.value = -1;
	frm.actualjob.value = -1;
	frm.candidatestatus.value = -1;

	frm.user_legajo.value =""; 
	frm.actual_company.value = "";
	frm.internal_company.value = "";

	frm.hoursavailableperdayl.value = 0;
    frm.hoursavailableperday.value = "";
    frm.salarywantedrel.value = 0;
	frm.start_time_aw.value = "";
    frm.end_time_aw.value = "";
    frm.user_function.value = "";
    frm.functional_area.value = "";
    frm.actual_functional_area.value = "";
    frm.level_management.value = "";
    frm.reports.value = "";
    frm.salarycurrentrel.value = 0;
    frm.salarycurrent.value = "";
    
    frm.gaptoppcactual.value = 0;
    frm.gaptoppcactual_c.value = "";
    frm.lastreward.value = 0;
    frm.lastreward_c.value = "";
    frm.actualbenefits.value = 0;
    frm.actualbenefits_c.value = "";
    frm.level.value = "";
    
    frm.department.value = "-1";
    frm.department_or.value = "-1";
    frm.department_name_or.value = "<? echo $AppUI->_('Any');?>";
    frm.department_name.value = "<? echo $AppUI->_('Any');?>";
    
    frm.actual_department.value = "-1";
    frm.actual_department_or.value = "-1";
    frm.actual_department_name_or.value = "<? echo $AppUI->_('Any');?>";
    frm.actual_department_name.value = "<? echo $AppUI->_('Any');?>";
    
    frm.performance.value = "";
    frm.potential.value = "";
    frm.title.value = "";
    frm.title_or.value = "";
    frm.status.value = "-1";
    frm.seminary.value = "";
    frm.activity.value = "";
    frm.title_name_or.value = "<? echo $AppUI->_('Any');?>";
    frm.title_name.value = "<? echo $AppUI->_('Any');?>";
    
    frm.key_position.checked = false;
    frm.person_position.checked = false;
   
	//fechas
	frm.inputdateFrom_txt.value = "";
	frm.inputdateFrom.value = "";
	frm.inputdateTo_txt.value = "<?php echo $inputdateTo->format( $df ); ?>";
	frm.inputdateTo.value = "<?php echo $inputdateTo->format( FMT_TIMESTAMP_DATE ); ?>";
	frm.updatedateFrom_txt.value = "";
	frm.updatedateFrom.value = "";
	frm.updatedateTo_txt.value = "<?php echo $updatedateTo->format( $df ); ?>";
	frm.updatedateTo.value = "<?php echo $updatedateTo->format( FMT_TIMESTAMP_DATE ); ?>";

	frm.lastuptdate_salary_txt.value = "";
    frm.lastuptdate_salary.value = "";
	
	intLen = activeskills.length;
	
	for(i=0;i<intLen;i++){
		activeskills[i] = -1; 
		activeskills.splice(i,1);
		intLen = activeskills.length;
		i=-1;
		
	}
	
	refreshfilters();
	selectChange();
	frm.secondChoice.selectedIndex='0';
	
    changeUsertype("2");
}


    /*
	locations functions
	*/
/*<uenrico>*/
	<?php 
		$Clocation->setFrmName("hhrrsearch");
		$Clocation->setCboCountries("country_id");
		$Clocation->setCboStates("state_id");
		$Clocation->setJSSelectedState($state_id);
		
		echo $Clocation->generateJS();
		
	?>
/*</uenrico>*/
function changeCPHState()
{
	var form = document.forms["hhrrsearch"];
	if (form.costperhourrel.options[form.costperhourrel.selectedIndex].value=='0'){
		form.costperhour.disabled=true;
	}else{
		form.costperhour.disabled=false;
	}
	
}

function changeSLRState()
{
	var form = document.forms["hhrrsearch"];
	if (form.salarywantedrel.options[form.salarywantedrel.selectedIndex].value=='0'){
		form.salarywanted.disabled=true;
	}else{
		form.salarywanted.disabled=false;
	}
	
}

function changeSLRCState()
{
    var form = document.forms["hhrrsearch"];
	if (form.salarycurrentrel.options[form.salarycurrentrel.selectedIndex].value=='0'){
		form.salarycurrent.disabled=true;
	}else{
		form.salarycurrent.disabled=false;
	}
}

function changeGapState()
{
	var form = document.forms["hhrrsearch"];
	if (form.gaptoppcactual.options[form.gaptoppcactual.selectedIndex].value=='0'){
		form.gaptoppcactual_c.disabled=true;
	}else{
		form.gaptoppcactual_c.disabled=false;
	}
}

function changeHadtate()
{
	var form = document.forms["hhrrsearch"];
	if (form.hoursavailableperdayl.options[form.hoursavailableperdayl.selectedIndex].value=='0'){
		form.hoursavailableperday.disabled=true;
	}else{
		form.hoursavailableperday.disabled=false;
	}
}


function changelastrewardState()
{
    var form = document.forms["hhrrsearch"];
	if (form.lastreward.options[form.lastreward.selectedIndex].value=='0'){
		form.lastreward_c.disabled=true;
	}else{
		form.lastreward_c.disabled=false;
	}
}

function changeactualbenefitsState()
{
    var form = document.forms["hhrrsearch"];
	if (form.actualbenefits.options[form.actualbenefits.selectedIndex].value=='0'){
		form.actualbenefits_c.disabled=true;
	}else{
		form.actualbenefits_c.disabled=false;
	}
}



function changeUsertype(type)
{   
	
    frm = document.forms["hhrrsearch"];
    frm.costperhourrel.value = 0;
	frm.costperhour.value = "";
	
	frm.wantsfulltime.value = -1;
	frm.wantsparttime.value = -1;
	frm.wantsfreelance.value = -1;
	frm.actualjob.value = -1;
	frm.candidatestatus.value = -1;

	frm.user_legajo.value =""; 
	frm.actual_company.value = "";
	frm.internal_company.value = "";

	frm.hoursavailableperdayl.value = 0;
    frm.hoursavailableperday.value = "";
    frm.salarywantedrel.value = 0;
    frm.salarywanted.value = "";
	frm.start_time_aw.value = "";
    frm.end_time_aw.value = "";
    frm.user_function.value = "";
    frm.functional_area.value = "";
    frm.actual_functional_area.value = "";
    frm.level_management.value = "";
    frm.reports.value = "";
    frm.salarycurrentrel.value = 0;
    frm.salarycurrent.value = "";
    
    frm.gaptoppcactual.value = 0;
    frm.gaptoppcactual_c.value = "";
    frm.lastreward.value = 0;
    frm.lastreward_c.value = "";
    frm.actualbenefits.value = 0;
    frm.actualbenefits_c.value = "";
    
    frm.department.value = "-1";
    frm.department_or.value = "-1";
    frm.department_name_or.value = "<? echo $AppUI->_('Any');?>";
    frm.department_name.value = "<? echo $AppUI->_('Any');?>";
    
    frm.actual_department.value = "-1";
    frm.actual_department_or.value = "-1";
    frm.actual_department_name_or.value = "<? echo $AppUI->_('Any');?>";
    frm.actual_department_name.value = "<? echo $AppUI->_('Any');?>";
    
    frm.performance.value = "";
    frm.potential.value = "";
    
	frm.lastuptdate_salary_txt.value = "";
    frm.lastuptdate_salary.value = "";
   
    
	// 5 = candidato
	if(type.value == "5")
	{
	   document.getElementById("candidatediv0").style.display = '';
	   document.getElementById("candidatediv1").style.display = '';
	   document.getElementById("employdiv0").style.display = 'none';
	   document.getElementById("employdiv1").style.display = 'none';
	}else{
	   document.getElementById("candidatediv0").style.display = 'none';
	   document.getElementById("candidatediv1").style.display = 'none';
	   document.getElementById("employdiv0").style.display = '';
	   document.getElementById("employdiv1").style.display = '';
	}
}

function popup_resource(src)
{

window.open(src, '_blank', 'top=0,left=0,width=1015, height=520, scrollbars=yes, status=no' );
}


function popDept() {
    var f = document.hhrrsearch;
    if (f.internal_company.selectedIndex == 0) {
        alert("<?=$AppUI->_('Please select a internal company first!')?>");
    } else {
        window.open('index.php?a=selector&m=public&dialog=1&suppressLogo=1&callback=setDept&table=departments&company_id='
            + f.internal_company.options[f.internal_company.selectedIndex].value
            + '&dept_id='+f.department.value,'dept','left=50,top=50,height=250,width=400,resizable')
    }
}
// Callback function for the generic selector
function setDept( key, val ) {
    var f = document.hhrrsearch;
    if (val != '') {
        f.department.value = key;
        f.department_name.value = val;
    } else {
        f.department.value = '0';
        f.department_name.value = '';
    }
}

function popDept_actual() {
    var f = document.hhrrsearch;
    if (f.actual_company.selectedIndex == 0) {
        alert("<?=$AppUI->_('Please select a current company first!')?>");
    } else {
        window.open('index.php?a=selector&m=public&dialog=1&suppressLogo=1&callback=setDept_actual&table=departments&company_id='
            + f.actual_company.options[f.actual_company.selectedIndex].value
            + '&dept_id='+f.actual_department.value,'dept','left=50,top=50,height=250,width=400,resizable')
    }
}
// Callback function for the generic selector
function setDept_actual( key, val ) {
    var f = document.hhrrsearch;
    if (val != '') {
        f.actual_department.value = key;
        f.actual_department_name.value = val;
    } else {
        f.actual_department.value = '0';
        f.actual_department_name.value = '';
    }
}

function popTitle() {
    var f = document.hhrrsearch;
    
    if (f.level.selectedIndex == 0) {
        alert1("<?=$AppUI->_('Please select a level first!')?>");
    } else {
        window.open('index.php?a=selector&m=public&dialog=1&suppressLogo=1&callback=setTitle&table=hhrr_education_title&level_id='
            + f.level.options[f.level.selectedIndex].value
            + '&title='+f.title.value,'title','left=50,top=50,height=250,width=400,resizable')
    }
}
// Callback function for the generic selector
function setTitle( key, val ) {
    var f = document.hhrrsearch;
    if (val != '') {
        f.title.value = key;
        f.title_name.value = val;
    } else {
        f.title.value = '0';
        f.title_name.value = '';
    }
}

function change_cia(cia){

	var f = document.hhrrsearch;

	if (cia.value != f.internal_company.value )
	{
	f.department.value = '-1';
	f.department_name.value = "<?=$AppUI->_('Any');?>";
	}else{
	f.department.value = f.department_or.value;
	f.department_name.value = f.department_name_or.value;
	}

}

</script>
</head>
<body>

<div id="part0" name="part0">
	<table width="100%" border="0" align="center" class="tableForm_bg">
	  <tr>
		  <td valign="top" colspan="2">
             <b><?php echo $AppUI->_('Search resources')?>...</b>
		  </td>
	  </tr>
	  <tr>
	     <td colspan="2">
		    <br>
		 </td>
	  </tr>
      <tr>
	    <td align="center">
		  <!-- Campos libres -->
		  <form action="" method="POST" name="hhrrsearch" enctype="multipart/form-data" > 
		  
		  <input type="hidden" name="m" value="hhrr">
		  <input type="hidden" name="skilllist" value="">
		  <input type="hidden" name="action" value="x">
		  <input type="hidden" name="dateformat" value="<?php echo $df; ?>">


		  <table width="98%" border="0" align="center" >
			  <tr>
				<td >
					 <?php echo $AppUI->_('Name')?>
				</td>
				<td>
					 <input class="text" type="text" name="user_name" value="<?=$user_name?>" >
				</td>
				
				<td >
					 <?php echo $AppUI->_('Last name')?>
				</td>
				
				<td>
					 <input class="text" type="text" name="user_last_name" value="<?=$user_last_name?>" >
				</td>
				
				<td align="left"><?=$AppUI->_("Tax ID number");?></td>
				
				<td>
				  <input class="text" size="24" type="text" name="taxidnumber" value="<?= $taxidnumber ?>">
				</td>
				
				<td >
					 <?php echo $AppUI->_('Email')?>
				</td>
				<td>
					 <input class="text" type="text" name="user_email" value="<?=$user_email?>" >
				</td>
				
			  </tr>
			  <tr>
			     <td colspan= "8" align="right">
				    <table>
					  <tr>
						<td>
							<INPUT class="button" name="searchhhrr" TYPE="button" onclick="submitIt()" VALUE="<?php echo $AppUI->_('Search')?>">
						</td>
						<td>
							<INPUT class="button" name="clearsearchhhrr" TYPE="button" onclick="javascript:formReset();" VALUE="<?php echo $AppUI->_('Clear')?>">
							&nbsp;
						</td>
					  </tr>
					</table>
				 </td>
			  </tr>
		  </table>


        </td>
	  </tr>
	  <tr>
	    <td colspan="1">
		  <hr>
		</td>
	  </tr>
      <tr>
	    <td >
		  
      
		  <!-- Campos comunes a todos los usuarios -->
		   <table width="100%" border="0" align="center" >
			  <tr>
				<td align="left" width="100">
				  <?php echo $AppUI->_('With marital state')?>
				</td>
				 <td>
					<?
					echo arraySelect($estCivil,"maritalstate",'size="1" class="text"', $maritalstate, true );
					?>	    
				 </td>
				 <td align="left" width="100">
					<?php echo $AppUI->_("Country"); ?>
				 </td>
					<?php 
					if(is_null($country_id)) $country_id = "-1";
					?>
				<td>
					<?php echo $Clocation->generateHTMLcboCountries($country_id, "text"); ?>
				</td>
				<?php 
					if($country_id == "-1") $country_id = NULL;
				?>
			  </tr>
			  <tr>
			    <td align="left">
				  <?php echo $AppUI->_('Age')?>
				</td>
				 <td>
					<?
	                echo arraySelect($arCandidateAge,"candidateage",'size="1" class="text"', $candidateage, true );
                    ?>	    
				 </td>
				 <td>
				   <?php echo $AppUI->_("State"); ?>
                 </td>
					<?php 
						if(is_null($state_id)) $state_id = "-1";
					?>
					<td>
					 <?php echo $Clocation->generateHTMLcboStates($state_id, "text"); ?>
					</td>
					<?php 
	 				if($state_id == "-1") $state_id = NULL;
				 	?>
			  </tr>
			  <tr>
			    <td>&nbsp;</td>
				<td>&nbsp;</td>
				<?php 
					echo $Clocation->generateJScallFunctions();
				?>
				<td>
				  <?php echo $AppUI->_("City"); ?>
				</td>
				<?php 
					//if(is_null($city)) $city = "-1";
				?>
				<td>
				  <?php //echo arraySelect($arCities, "city", "class=\"text\"", $city, true); ?>
				  <input type="text" class="text" size="24" name="city" value="<?=$city?>">
				</td>
				<?php 
				//	if($city == "-1") $city = null;
				?>
			  </tr>
			  
			  <tr>
			    <td colspan="2" width="55%">
                  
                   <table>
				     <tr>
					   <td width="100">
					     <?php echo $AppUI->_('Input Date User Skill')?>
					   </td>
					   <td>
					     <?php echo $AppUI->_('From')?>:
					   </td>
		 			   <td>
		 				 <input type="text" name="inputdateFrom_txt" value="<?php echo $inputdateFrom ? $inputdateFrom->format( $df ) : '' ?>" class="text" size="12" disabled >
						 <a href="javascript: //" onClick="popCalendar('inputdateFrom')">
							<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
						 </a> 
						 <input type="hidden" name="inputdateFrom" value="<?php echo $inputdateFrom ? $inputdateFrom->format( FMT_TIMESTAMP_DATE ) : '' ?>" > 	
		 			   </td>
		 			   <td>
					     &nbsp;&nbsp;<?php echo $AppUI->_('To')?>:&nbsp;
					   </td>
		 			   <td>
		 				 <input type="text" name="inputdateTo_txt" value="<?php echo $inputdateTo ? $inputdateTo->format( $df ) : '' ?>" class="text"  size="12"  disabled >
						 <a href="javascript: //" onClick="popCalendar('inputdateTo')">
							<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
						 </a> 
						 <input type="hidden" name="inputdateTo" value="<?php echo $inputdateTo ? $inputdateTo->format( FMT_TIMESTAMP_DATE ) : '' ?>" > 
		 			   </td>
					 </tr>
					 <tr>
					   <td>
						  <?php echo $AppUI->_('Update Date User Skill')?>
					   </td>
					   <td>
					     <?php echo $AppUI->_('From')?>:&nbsp;
					   </td>
		 			   <td>
		 				 <input type="text" name="updatedateFrom_txt" value="<?php echo $updatedateFrom ? $updatedateFrom->format( $df ) : '' ?>" class="text"  size="12" disabled >
						 <a href="javascript: //" onClick="popCalendar('updatedateFrom')">
							<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
						 </a> 
						 <input type="hidden" name="updatedateFrom" value="<?php echo $updatedateFrom ? $updatedateFrom->format( FMT_TIMESTAMP_DATE ) : '' ?>" > 	
		 			   </td>
		 			   <td>
					      &nbsp;&nbsp;<?php echo $AppUI->_('To')?>:&nbsp;
					   </td>
		 			   <td>
		 				 <input type="text" name="updatedateTo_txt" value="<?php echo $updatedateTo ? $updatedateTo->format( $df ) : '' ?>" class="text"  size="12" disabled >
						 <a href="javascript: //" onClick="popCalendar('updatedateTo')">
							<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
						 </a> 
						 <input type="hidden" name="updatedateTo" value="<?php echo $updatedateTo ? $updatedateTo->format( FMT_TIMESTAMP_DATE ) : '' ?>" > 
		 			   </td>
					 </tr>
				   </table>

				</td>
				<td colspan="2">&nbsp;</td>
			  </tr>
		   </table>

        </td>
	  </tr>
	  
	  <tr>
         <td>
		   <hr>
		 </td>
	  </tr>
      
	  
	  <tr>
	    <td> 
		   <table width="100%" border="0" align="center" >
			  <tr>
			    <td width="16%">
				 <?php echo $AppUI->_('With user type')?>
				</td>
				<td >
				   <?
				   echo arraySelect($utypes,"user_type",'size="1" class="text" onchange="javascript: changeUsertype(this);"', $user_type, true, true, "215px"  );
				   ?>
				</td>
				<td width="45%">
				  <!-- Campos solo para candidatos -->
                  <div id="candidatediv0" name="candidatediv0" <? if($user_type==5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?> > 
				  <table border="0" align="left" width="95%" >
					  <tr>
						<td width="39%">
						   <?php echo $AppUI->_('Candidate Status')?>
						</td>
						<td >
							<?
							echo arraySelect( $SCandidateStatus, "candidatestatus", 'class="text"', $candidatestatus, true );
							?>
						</td>
					  </tr>
				  </table>
                  </div>

				  <!-- Campos para empleados -->
				  <div id="employdiv0" name="employdiv0" <? if($user_type!=5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?> >
				  <table border=0 width="100%">
					  <tr>
						<td width="24%">
						  <?php echo $AppUI->_('Legajo')?>
						</td>
						<td>
						  <input type="text" class="text" name="user_legajo" value="<?=$user_legajo?>" size="20">
						</td>
					  </tr>
				  </table>
				  </div>

				</td>
			  </tr>
		   </table>
		</td>
	  </tr>
	  <tr>
	    <td>
		    <!-- Campos solo para candidatos -->
		    <div id="candidatediv1" name="candidatediv1" <? if($user_type==5 ){ ?> style="display: " <?}else{?> style="display: none;" <?} ?>  > 
		    <table width="100%" border="0" align="center" >
			  <tr>
			    <td>
				    <?php echo $AppUI->_('Current work load')?> &nbsp;
				</td>
				<td>
					<?
					echo arraySelect( $wrkTypes, "actualjob", 'class="text"', $actualjob, true );
					?>
				</td>
				<td>
				    &nbsp;<?php echo $AppUI->_('Actual Company')?> &nbsp; 
				</td>
				<td>
					<input type="text" class="text" name="actual_company_candidatos" size="24" value="">
				</td>
			  </tr>
			  <tr>
			    <td>
			        <?php echo $AppUI->_('With cost per hour')?>
		        </td>
		        <td>
					<?
				    //echo arraySelect($cphrelations,"costperhourrel",'size="1" class="text" onchange="changeCPHState()"', $costperhourrel, true ); 
				    
				    switch ($costperhourrel)
					 {
					 	case "<=";
					 	  $sel_ch0 = "SELECTED";
					 	break;
					 	case "=";
					 	  $sel_ch1 = "SELECTED";
					 	break;
					 	case ">=";
					 	  $sel_ch2 = "SELECTED";
					 	break;
					 	default:
					 	  $sel_ch3 = "SELECTED";
					 }
						
					?>			
					
					<select name="costperhourrel" size="1" class="text" onchange="changeCPHState()" style="width : 160 px">
						<option value="<=" <?=$sel_ch0?> ><=</option>
						<option value="=" <?=$sel_ch1?> >=</option>
						<option value=">="> <?=$sel_ch2?> >=</option>
						<option value="0" <?=$sel_ch3?> ><?=$AppUI->_('Any')?></option>
					</select>	
							  
					<input class="text" type="text" name="costperhour" value="<?=$costperhour?>" size="6" maxlength="4">&nbsp;pesos	    
		        </td>
				<td>
			        &nbsp;<?=$AppUI->_('Diary avalability')?>
		        </td>
				<td>
				    <?php
					echo arraySelect($slrrelations,"hoursavailableperdayl",'size="1" class="text" onchange="changeHadtate()"', $hoursavailableperday, true );
					?>		
				    <input class="text" type="text" size="6" name="hoursavailableperday" value="<?=$hoursavailableperday?>">
				</td>  
			  </tr>
			  <tr>
				<td>
				    <?php echo $AppUI->_('Salary wanted')?>
          		</td>
				<td>
					<?php
						//echo arraySelect($slrrelations,"salarywantedrel",'size="1" class="text" onchange="changeSLRState()"', $salarywantedrel, true );
						
					switch ($salarywantedrel)
					 {
					 	case "<=";
					 	  $sel_sw0 = "SELECTED";
					 	break;
					 	case "=";
					 	  $sel_sw1 = "SELECTED";
					 	break;
					 	case ">=";
					 	  $sel_sw2 = "SELECTED";
					 	break;
					 	default:
					 	  $sel_sw3 = "SELECTED";
					 }
						
					?>			
					
					<select name="salarywantedrel" size="1" class="text" onchange="changeSLRState()" style="width : 160 px">
						<option value="<=" <?=$sel_sw0?> ><=</option>
						<option value="=" <?=$sel_sw1?> >=</option>
						<option value=">="> <?=$sel_sw2?> >=</option>
						<option value="0" <?=$sel_sw3?> ><?=$AppUI->_('Any')?></option>
					</select>				  
				    <input class="text" type="text" name="salarywanted" value="<?=$salarywanted?>" size="6" maxlength="8">&nbsp;pesos	    
			    </td>
                <td>
				    &nbsp;<?php echo $AppUI->_('Actual working hours')?>
				</td>
				<td>

				  <?=arraySelect( $times, 'start_time_aw', 'size="1" class="text"', $start_time_aw ? $$start_time_aw :'',"","","80px" )?>
			       a
				  <?=arraySelect( $times, 'end_time_aw', 'size="1" class="text"', $end_time_aw ? $end_time_aw : '',"","","80px" )?>
				</td>
			  </tr>
			  <tr>
			    <td>
					<?php echo $AppUI->_('Wants full time')?>
				</td>
				<td>
					<?=arraySelect($wrkPrefOptions,"wantsfulltime",'size="1" class="text"', $wantsfulltime, true );?>	
				</td>	
				<td colspan="2">&nbsp;</td>
	          </tr>	
			  <tr>
				  <td>
					<?php echo $AppUI->_('Wants part time')?> 
				  </td>
	              <td>
				    <?=arraySelect($wrkPrefOptions,"wantsparttime",'size="1" class="text"', $wantsparttime, true );?>	
	              </td>	   
				  <td colspan="2">&nbsp;</td>
	          </tr>
	          <tr>
				  <td>
					<?php echo $AppUI->_('Wants freelance')?> 
				  </td>
	              <td>
				    <?=arraySelect($wrkPrefOptions,"wantsfreelance",'size="1" class="text"', $wantsfreelance, true );?>
	              </td>	    
				  <td colspan="2" width="45%">&nbsp;</td>
	          </tr>
	         
			</table>

	  </div>

	  <!-- Campos para empleados, contratados, etc -->
	  <div id="employdiv1" name="employdiv1" <? if($user_type!=5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?> >
		    <table width="100%" border="0" align="center" >
			  <tr>
			    <td width="16%">
				   <?php echo $AppUI->_('Actual Company')?>
				</td>
				<td>
				 <select name="actual_company" class="text" style="width:215px;">
				    <option value="" ><?php echo $AppUI->_('Any')?></option>
					<?
					$query = "SELECT company_name, company_id FROM companies ORDER BY company_name ASC"; 
					$sql = mysql_query($query);
					 
					while($vec = mysql_fetch_array($sql) )
					{
					 $selected = ($vec['company_id']==$actual_company) ? "selected" : "";
					  echo "<option " .$selected ." value=\"$vec[company_id]\">$vec[company_name]</option>";
					}
					?>
		          </select>
				</td>
				<td>
				  &nbsp;<?=$AppUI->_("Department")?>
				</td>
				<td>
			     <?
			          
				      if($actual_department == "")
					  {
					   $actual_department = "-1";
					   
					  }
				  ?>
                  <input type="hidden" name="actual_department_or" value="<?=$actual_department;?>" />
		          <input type="hidden" name="actual_department" value="<?=$actual_department;?>" />
 
				  <?  
				     if ( $actual_department == "-1") 
					  {
					  $actual_department_name = $AppUI->_('Any');
					  }
					  else
					  {
					  @$actual_area_in = mysql_result(mysql_query("SELECT dept_name FROM departments WHERE dept_id ='$actual_department' "),0);          

					  $actual_department_name = $actual_area_in;  
					  }
				  ?>
                  <input type="hidden" name="actual_department_name_or" value="<?=$actual_department_name;?>" />
	              <input type="text" class="text" name="actual_department_name" value="<?=$actual_department_name;?>" size="20" disabled />&nbsp;<input type="button" class="buttonsmall" value="..." onclick="popDept_actual()" />
			     <!-- <select name="department" id="department" class="text" style="width:215px;"></select> -->
			    </td>
				<td>
				  	&nbsp;
				</td>
			  </tr>
			
			   <tr>
			  
			    <td>
				  <?=$AppUI->_("functional_area")?>
				</td>
				<td>
				  <select name="actual_functional_area" class="text" style="width:215px;">
				    <option value="" ><?=$AppUI->_("Any")?></option>
					<?
					$query = "SELECT area_name, id FROM hhrr_functional_area ORDER BY area_name ASC"; 
					$sql = mysql_query($query);
					 
						while($vec = mysql_fetch_array($sql) )
						{
						  $selected = ($vec['id']==$functional_area) ? "selected" : "";
						  echo "<option " .$selected ." value=\"$vec[id]\">$vec[area_name]</option>";
						}
					 ?>
				   </select>
				</td>
			    <td>
			     &nbsp;<?=$AppUI->_("Function")?> 
			    </td>
		        <td >
			 	  <input type="text" name="user_function" value="<?=$user_function?>" size="20" class="text">
			    </td>
			
			   
			    <td>
				 <?=$AppUI->_("Posición Clave")?>
					<input type="checkbox" name="key_position" value="" <? if(isset($key_position)) echo "checked"; ?>>
				  </td>
			  </tr>
			  
			  <tr>
			     <td colspan="4">&nbsp;</td>
			     <td>
				   <?=$AppUI->_("Persona Clave")?>
				  
				  <input type="checkbox" name="person_position" value="" <? if(isset($person_position)) echo "checked"; ?> >
				  </td>
			  </tr>
			  <tr>
			    <td width="16%">
				   <?php echo $AppUI->_('Internal Company')?>
				</td>
				<td>
				 <select name="internal_company" class="text" style="width:215px;" onchange="change_cia(this)" >
				    <option value="" ><?php echo $AppUI->_('Any')?></option>
					<?
					$query = "SELECT company_name, company_id FROM companies  ORDER BY company_name ASC"; 
					$sql = mysql_query($query);
					 
					while($vec = mysql_fetch_array($sql) )
					{
					 $selected = ($vec['company_id']==$internal_company) ? "selected" : "";
					  echo "<option " .$selected ." value=\"$vec[company_id]\">$vec[company_name]</option>";
					}
					?>
		          </select>
				</td>
				<td>
			      &nbsp;<?=$AppUI->_("Department")?>
			    </td>
		          
			    <td>
			     <?
			          
				      if($department == "")
					  {
					   $department = "-1";
					   
					  }
				  ?>
                  <input type="hidden" name="department_or" value="<?=$department;?>" />
		          <input type="hidden" name="department" value="<?=$department;?>" />
 
				  <?  
				     if ( $department == "-1") 
					  {
					  $department_name = $AppUI->_('Any');
					  }
					  else
					  {
					  @$area_in = mysql_result(mysql_query("SELECT dept_name FROM departments WHERE dept_id ='$department' "),0);          

					  $department_name = $area_in;  
					  }
				  ?>
                  <input type="hidden" name="department_name_or" value="<?=$department_name;?>" />
	              <input type="text" class="text" name="department_name" value="<?=$department_name;?>" size="20" disabled />&nbsp;<input type="button" class="buttonsmall" value="..." onclick="popDept()" />
			     <!-- <select name="department" id="department" class="text" style="width:215px;"></select> -->
			    </td>
			    <td>&nbsp;</td>
			    
			  </tr>
			  
			  <tr>
			    <td>
				  <?=$AppUI->_("functional_area")?>
				</td>
				<td>
				  <select name="functional_area" class="text" style="width:215px;">
				    <option value="" ><?=$AppUI->_("Any")?></option>
					<?
					$query = "SELECT area_name, id FROM hhrr_functional_area ORDER BY area_name ASC"; 
					$sql = mysql_query($query);
					 
						while($vec = mysql_fetch_array($sql) )
						{
						  $selected = ($vec['id']==$functional_area) ? "selected" : "";
						  echo "<option " .$selected ." value=\"$vec[id]\">$vec[area_name]</option>";
						}
					 ?>
				   </select>
				</td>
				<td>
				 &nbsp;<?=$AppUI->_("level_management")?>
				</td>
				<td>
				  <input id='level_management' type="text" name="level_management" value="<?=($type_cia==1) ? $level_management : '';?>" size="20" class="text">
				</td>
			  </tr>
			  <tr>
			    <td>
				  <?=$AppUI->_("reports")?>
				</td>
				<td>
				  <select name="reports" class="text" style="width:215px;">
				    <option value=""><?=$AppUI->_("Any")?></option>
					<?
					$query = "SELECT user_id, CONCAT_WS(' ',user_first_name,user_last_name) AS name
							  FROM users
							  WHERE user_status = '0'
							  AND user_company = $AppUI->user_company
							  AND user_type IN (1,3,2,4)
							  ORDER BY user_first_name, user_last_name"; 
					$sql = mysql_query($query);
							
					 
					   while($vec = mysql_fetch_array($sql) )
					   {
						 $selected = ($vec['user_id']==$reports) ? "selected" : "";
						 echo "<option " .$selected ." value=\"$vec[user_id]\">$vec[name]</option>";
						}
					?>
				   </select>
				</td>
				<td colspan="3">&nbsp;</td>
			  </tr>
			  <tr>
			    <td>
				  <?php echo $AppUI->_('Current Salary')?>
				</td>
				<td>
				     <?php
					 
					 switch ($salarycurrentrel)
					 {
					 	case "<=";
					 	  $sel_sc0 = "SELECTED";
					 	break;
					 	case "=";
					 	  $sel_sc1 = "SELECTED";
					 	break;
					 	case ">=";
					 	  $sel_sc2 = "SELECTED";
					 	break;
					 	default:
					 	  $sel_sc3 = "SELECTED";
					 }
						
					?>			
					
					<select name="salarycurrentrel" size="1" class="text" onchange="changeSLRCState()" style="width : 160 px">
						<option value="<=" <?=$sel_sc0?> ><=</option>
						<option value="=" <?=$sel_sc1?> >=</option>
						<option value=">=" <?=$sel_sc2?> > >=</option>
						<option value="0" <?=$sel_sc3?> ><?=$AppUI->_('Any')?></option>
					</select>	
					
				    <input class="text" type="text" name="salarycurrent" value="<?=$salarycurrent?>" size="6" maxlength="8">&nbsp;pesos    
				</td>
				<td>
				  &nbsp;<?=$AppUI->_("lastuptdate")?>
				</td>
				<td>
				  <input type="text" name="lastuptdate_salary_txt" value="<?php echo $lastuptdate_salary ? $lastuptdate_salary->format( $df ) : '' ?>" class="text" size="12" disabled />
				  <a href="javascript: //" onClick="popCalendar('lastuptdate_salary')">
					 <img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0">
				  </a> 
				  <input type="hidden" name="lastuptdate_salary" value="<?php echo $lastuptdate_salary ? $lastuptdate_salary->format( FMT_TIMESTAMP_DATE ) : '' ?>" /> 	
				</td>
			  </tr>
			  <tr>
			    <td>
				  <?=$AppUI->_("gaptoppcactual")?>
				</td>
				<td>
				  <?php
						//echo arraySelect($slrrelations,"gaptoppcactual",'size="1" class="text" onchange="changeGapState()"', $gaptoppcactual, true );
					 switch ($gaptoppcactual)
					 {
					 	case "<=";
					 	  $sel_ga0 = "SELECTED";
					 	break;
					 	case "=";
					 	  $sel_ga1 = "SELECTED";
					 	break;
					 	case ">=";
					 	  $sel_ga2 = "SELECTED";
					 	break;
					 	default:
					 	  $sel_ga3 = "SELECTED";
					 }
						
					?>			
					
					<select name="gaptoppcactual" size="1" class="text" onchange="changeGapState()" style="width : 160 px">
						<option value="<=" <?=$sel_ga0?> ><=</option>
						<option value="=" <?=$sel_ga1?> >=</option>
						<option value=">=" <?=$sel_ga2?> > >=</option>
						<option value="0" <?=$sel_ga3?> ><?=$AppUI->_('Any')?></option>
					</select>	
								  
				    <input class="text" type="text" name="gaptoppcactual_c" value="<?=$gaptoppcactual_c?>" size="6" maxlength="8">&nbsp;pesos	
				</td>
				<td colspan="3">&nbsp;
				</td>
			  </tr>
			  <tr>
			    <td>
				  <?=$AppUI->_("lastreward")?>
                </td>
				<td>
				   <?php
						//echo arraySelect($slrrelations,"lastreward",'size="1" class="text" onchange="changelastrewardState()"', $lastreward, true );
						
					switch ($lastreward)
					 {
					 	case "<=";
					 	  $sel_lr0 = "SELECTED";
					 	break;
					 	case "=";
					 	  $sel_lr1 = "SELECTED";
					 	break;
					 	case ">=";
					 	  $sel_lr2 = "SELECTED";
					 	break;
					 	default:
					 	  $sel_lr3 = "SELECTED";
					 }
						
					?>			
					
					<select name="lastreward" size="1" class="text" onchange="changelastrewardState()" style="width : 160 px">
						<option value="<=" <?=$sel_lr0?> > <= </option>
						<option value="=" <?=$sel_lr1?> > =</option>
						<option value=">=" <?=$sel_lr2?>> >=</option>
						<option value="0" <?=$sel_lr3?> ><?=$AppUI->_('Any')?></option>
					</select>		
							  
				    <input class="text" type="text" name="lastreward_c" value="<?=$lastreward_c?>" size="6" maxlength="8">&nbsp;pesos	
				</td>
				<td colspan="3">&nbsp;
				</td>
			  </tr>
			  <tr>
			    <td>
				  <?=$AppUI->_("actualbenefits")?>
				</td>
				<td>
				   <?php
						//echo arraySelect($slrrelations,"actualbenefits",'size="1" class="text" onchange="changeactualbenefitsState()"', $actualbenefits, true );
						
					switch ($actualbenefits)
					 {
					 	case "<=";
					 	  $sel_ab0 = "SELECTED";
					 	break;
					 	case "=";
					 	  $sel_ab1 = "SELECTED";
					 	break;
					 	case ">=";
					 	  $sel_ab2 = "SELECTED";
					 	break;
					 	default:
					 	  $sel_ab3 = "SELECTED";
					 }
						
					?>			
					
					<select name="actualbenefits" size="1" class="text" onchange="changeactualbenefitsState()" style="width : 160 px">
						<option value="<=" <?=$sel_ab0?> ><=</option>
						<option value="=" <?=$sel_ab1?> >=</option>
						<option value=">=" <?=$sel_ab2?>> >=</option>
						<option value="0" <?=$sel_ab3?> ><?=$AppUI->_('Any')?></option>
					</select>		
								  
				    <input class="text" type="text" name="actualbenefits_c" value="<?=$actualbenefits_c?>" size="6" maxlength="8">&nbsp;pesos	
				</td>
			    <td colspan="3" width="45%">&nbsp;
			  </tr>
			  <tr>
			    <td>
			       <?=$AppUI->_("Performance Evaluation")?>
			    </td>
			    <td>
			    
			      <?
				  $query = "SELECT id_item , name_es  FROM hhrr_performance_items ORDER BY name_es ASC"; 
				  $sql = mysql_query($query);
				 
                  ?>
                  <select name="performance" class="text" style="width:215px;" >
				    <option value="" ><?php echo $AppUI->_('Any')?></option>
					
				    <?
					 
					while($vec = mysql_fetch_array($sql) )
					{ 
					  if ($vec[id_item]==$performance){
					  	$selected = "selected";
					  }else{
					    $selected = "";
					  }
					  
					  echo "<option " .$selected ." value=\"".$vec[id_item]."\">".$vec[name_es]."</option>";
					}
					?>
		         </select>
		         
			    </td>
			    <td colspan="2">
			      &nbsp;
			    </td>
			  <tr>
			  </tr>
			    <td>
			       <?=$AppUI->_("Potential")?>
			    </td>
			     <td>
			     
			      <?
				  $query = "SELECT id_potential ,level, name_es  FROM hhrr_performance_potential "; 
				  $sql = mysql_query($query);
				 
                  ?>
                  <select name="potential" class="text" style="width:215px;" >
				    <option value="" ><?php echo $AppUI->_('Any')?></option>
					
				    <?
					 
					while($vec = mysql_fetch_array($sql) )
					{ 
					  if ($vec[id_potential]==$potential){
					  	$selected = "selected";
					  }else{
					    $selected = "";
					  }
					  
					  echo "<option " .$selected ." value=\"".$vec[id_potential]."\">Nivel ".$vec[level]." ".$vec[name_es]."</option>";
					}
					?>
		          </select>
		          
			    </td>
			    <td colspan="2">
			      &nbsp;
			    </td>
			  </tr>
			</table>
		</td>
	  </tr>
	  </div>

	  <tr>
	    <td><hr></td>
	  </tr>

	  <!-- Matriz de conocimientos -->
	  <tr>
	    <td>
		    <table width="100%" border="0" align="center" >
			  <tr>
				<td>
				  <?=$AppUI->_("Academic level")?>
				</td>
				<td> 
					<select name="level" class="text" style="width:250px;" >
					  <option value=""><?=$AppUI->_("Any");?></option>
					 <?
					 if ($AppUI->user_locale == 'es')
						$name = 'name_es';
					 else
						$name = 'name_en';
					
						$query = "SELECT id, $name AS name
								  FROM hhrr_academic_level
								  ORDER BY name"; 
						$sql = mysql_query($query);
						 
						while($vec = mysql_fetch_array($sql) )
						{
						 $selected = ($vec['id']==$level) ? "selected" : "";
						echo "<option " .$selected ." value=\"$vec[id]\">$vec[name]</option>";
						}
					 ?>
					</select>
				</td>
				 
				
				<td>
				  <?=$AppUI->_("Title")?>
				</td>
				
		          
				<td>
				  
				  <?
				      if($title == "")
					  {
					   $title = "-1";
					  }
				  ?>
                  <input type="hidden" name="title_or" value="<?=$title;?>" />
		          <input type="hidden" name="title" value="<?=$title;?>" />
 
				  <?  
				     if ($title == "" || $title == "-1") 
					  {
					  $title_name = $AppUI->_('Any');
					  }
					  else
					  {
					  @$title_in = mysql_result(mysql_query("SELECT name_es FROM hhrr_education_title WHERE title_id ='$title' "),0);          

					  $title_name = $title_in;  
					  }
				  ?>
                  <input type="hidden" name="title_name_or" value="<?=$title_name;?>" />
	              <input type="text" class="text" name="title_name" value="<?=$title_name;?>" size=51" disabled />&nbsp;<input type="button" class="buttonsmall" value="..." onclick="popTitle()" />
				  <!-- <select name="title" id="title" class="text" style="width:350px;"  ></select> -->
				</td>
				<td>
				  <?=$AppUI->_("Status")?>
				</td>
				<td>
				  <?
                     switch($status){
					 case "0":
						$ck0 = "selected";
					 break;
					 case "1":
						$ck1 = "selected";
					 break;
					 case "2":
						$ck2 = "selected";
					 break;
					 default:
						$ck3 = "selected";
					 break;
				     }
				  ?>
				
				  <select name="status" class="text">
				    <option value="-1" <?=$ck3;?> ><?=$AppUI->_("Any")?></option>
					<option value="0" <?=$ck0;?> ><?=$AppUI->_("Incomplete")?></option>
					<option value="1" <?=$ck1;?> ><?=$AppUI->_("Completed")?></option>
					<option value="2" <?=$ck2;?> ><?=$AppUI->_("On Course")?></option>
				  </select>
				
				</td>
			  </tr>
			  
			  <tr>
			    <td> 
			       <?=$AppUI->_("Program")?>
			    </td>
			    <td>
			    
			     <?
				  $query = "SELECT program_id , name  FROM hhrr_education_program ORDER BY name ASC"; 
				  $sql = mysql_query($query);
					
                 ?>
                 <select name="seminary" class="text" style="width:250px;" >
				    <option value="" ><?php echo $AppUI->_('Not Specified')?></option>
					
				    <?
					 
					while($vec = mysql_fetch_array($sql) )
					{ 
					  if ($vec[program_id]==$seminary){
					  	$selected = "selected";
					  }else{
					    $selected = "";
					  }
					  
					  echo "<option " .$selected ." value=\"".$vec[program_id]."\">".$vec[name]."</option>";
					}
					?>
		          </select>
		          
			    </td>
			    <td>
			      <?=$AppUI->_("Activity")?>
			    </td>
			    <td>
			      <input type="text" name="activity" value="<?=$activity;?>" size="56" class="text">
			    </td>
			  </tr>
			 
			  <tr>
			    <td colspan="6">
			      &nbsp;
			    </td>
			  </tr>
			  </table>
			  
			  
			   <tr>
			    <td colspan="6"><hr></td>
			  </tr>
			  
			  
	     <tr>
	      <td>
	      
		    <table width="100%" border="0" align="center" >
			  <tr>
			    <td colspan="2" >
				  <?php echo $AppUI->_('Select the group')?><br>
				  <select class="text" id="firstChoice" name="firstChoice" onClick="selectChange();">
					<?
					  $result = mysql_query("SELECT * from skillcategories ORDER BY sort;");
					  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
						echo '<option value='.$row["id"].' '.($row["id"] == $firstChoice ? "selected":"").'>' . $row["name"] . '</option>';		 
					  }
					?>
				 </select>
				</td>
				<td colspan="2">
				  <?php echo $AppUI->_('Choose the skill')?><br>	
				  <select  class="text" id="secondChoice" name="secondChoice">
				   <?
					  $result = mysql_query("SELECT * FROM skillcategories ORDER BY id;");
					  $row = mysql_fetch_array($result, MYSQL_ASSOC);  
					  $selcat=$row["id"];
					  $result = mysql_query("SELECT * from skills WHERE idskillcategory = $selcat ORDER BY description;");
					  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
					  {
						echo '<option value="'.$row["id"].'">'.$row["description"].'</option>';
					  }
				   ?>
				  </select>	
	            </td>
				<td  colspan="2">
				  <input type="button" class="button" value="<?php echo $AppUI->_('Add skill for filtering')?>" onclick="addskill(document.forms['hhrrsearch'].secondChoice.value);">
				</td>
			  </tr>
             </table>
		</td>
	  </tr>
	</table>
</div>


<div id="elDiv" name="elDiv" class="tableForm_bg">
</div>

<div id="part1" name="part1" align="right" class="tableForm_bg">
	<table  border="0">
	  <tr>
	    <td>
	        <INPUT type="hidden" name="hhrr_search_next_page" value="" >
	        
			<INPUT class="button"  TYPE="button" onclick="submitIt()" VALUE="<?php echo $AppUI->_('Search')?>">
	    </td>
	    <td>
			<INPUT class="button" name="clearsearchhhrr" TYPE="button" onclick="javascript:formReset();" VALUE="<?php echo $AppUI->_('Clear')?>">
	    </td>
	  </tr>
	</table>
</div>


<script language="javascript">
  refreshfilters();
  selectChange();
  changeGapState();
  changeSLRCState();
  changelastrewardState();
  changeactualbenefitsState();
  changeHadtate();
</script>

<?
  
  if($searchhhrr==$AppUI->_('Search') || $changepage== 1){
?>
    <div id="search" name="search">
    </div>
  <script language="javascript">
//    hideSearchPanel();	
  </script>
<?
 

	$where = " 1=1 \n";


	if (trim($skilllist)!=""){
		$skills = split(",",$skilllist);	
	
		if(count($skills)>0){
			//echo "tiene skills";
			$whereskills = "";
			$first = true;
			foreach ($skills as $act_sk_id =>$skill_id){
					$whereskills .= "\n\t\t".(!$first ? "OR":"") ."	( hhrrskills.idskill = '$skill_id' ";
					$whereskills .= "\n\t\t	AND hhrrskills.value >= '".$_POST["value$skill_id"]."' ";
					$whereskills .= "\n\t\t	AND hhrrskills.comment like '%".$_POST["comment$skill_id"]."%' )";
					$first=false;
			}
			$sql = "drop table if exists hhrrfilter;";
			db_exec($sql);
			$sql = "create temporary table hhrrfilter
						select user_id, count(idskill) cant from hhrrskills
						WHERE  $whereskills 
						group by user_id;";
			
			//echo "<pre>$sql</pre>";
			
			db_exec($sql);
			$sql = "select user_id from hhrrfilter where cant = ".count($skills).";";
		
		$usersskills = db_loadColumn($sql);
		$where .= count($usersskills)>0?
					"\nAND	users.user_id IN ( ".
						implode($usersskills, ", \n\t\t").")" :
					"\nAND	1=0";	
		}	
	}
    
	if ($user_name != "")
	{
		 $texto_names = (explode(" ", $user_name));
		 
		 $sql_nombres = "";
		 
		 foreach ($texto_names as $key=>$names)
		 {
		 	$sql_nombres .=  "or users.user_first_name like '%$names%' ";
		 }
		 
		 /*	 
		 $where .= "\nAND (users.user_first_name IN ('".implode ("','", $texto_names)."') or  users.user_last_name IN ('".implode ("','", $texto_names)."') $sql_nombres )";
		 */
		
		 $where .= "\nAND  users.user_first_name like '%$user_name%' ";
		 
	}
	
	if ($user_last_name != "")
	{
		$where .= "\nAND  users.user_last_name like '%$user_last_name%' ";
	}
	
	
	$where .= $user_email ? "\nAND	users.user_email = '$user_email'" : "";
	$where .= $user_legajo ? "\nAND	users.legajo = '$user_legajo'" : "";
	$where .= $user_type ? "\nAND	users.user_type = '$user_type'" : "";
	$where .= $maritalstate ? "\nAND	users.maritalstate = '$maritalstate'" : "";
	$where .= $costperhourrel ? "\nAND 	users.costperhour $costperhourrel '$costperhour'" : "";
	$where .= $salarywantedrel ? "\nAND 	users.salarywanted $salarywantedrel '$salarywanted'" : "";
	$where .= $wantsfulltime!="-1" ? "\nAND 	users.wantsfulltime = $wantsfulltime ":"";	
	$where .= $wantsparttime!="-1" ? "\nAND 	users.wantsparttime = $wantsparttime ":"";	
	$where .= $wantsfreelance!="-1" ? "\nAND 	users.wantsfreelance = $wantsfreelance ":"";	
	$where .= $hoursavailableperdayl!="0" ? "\nAND 	users.hoursavailableperday = $hoursavailableperday ":"";
	if($user_type == "5") $where .= $actualjob!="-1" ? "\nAND 	users.actualjob = '$actualjob' ":"";
	$where .= $actual_company  ? "\nAND 	users.user_company = '$actual_company' ":"";
	if($actual_department != "" && $actual_department != "0")$where .= $actual_department !='-1' ? "\nAND users.user_department = '$actual_department' ":"";
	$where .= $actual_functional_area ? "\nAND (hhrr_dev.hhrr_dev_mov_af1 ='$actual_functional_area' OR hhrr_dev.hhrr_dev_mov_af2 ='$actual_functional_area' OR hhrr_dev.hhrr_dev_mov_af3 ='$actual_functional_area') ":"";
	
	$where .= $taxidnumber ? "\nAND 	users.taxidnumber = '$taxidnumber' ":"";
	
	$where .= $internal_company ? "\nAND hhrr_ant.internal_company = '$internal_company' ":"";
	if($department != "" && $department != "0")$where .= $department !='-1' ? "\nAND hhrr_ant.area_internal = '$department' ":"";
	
	$where .= $candidatestatus!="-1" ? "\nAND users.candidatestatus = '$candidatestatus'":"";
	$where .= $user_function ? "\nAND hhrr_ant.function like '%$user_function%' ":"";
    
	if(isset($key_position)) $where .= "\nAND hhrr_dev.hhrr_dev_pos_k = '1'";
	if(isset($person_position)) $where .= "\nAND hhrr_dev.hhrr_dev_per_k = '1' ";

	$where .= $functional_area ? "\nAND hhrr_ant.functional_area ='$functional_area' ":"";
	$where .= $level_management ? "\nAND hhrr_ant.level_management ='$level_management' ":"";
	$where .= $reports ? "\nAND hhrr_ant.reports ='$reports' ":"";
	$where .= $salarycurrentrel ? "\nAND hhrr_comp.hhrr_comp_remuneration $salarycurrentrel '$salarycurrent'" : "";
	$where .= $lastuptdate_salary ? "\nAND hhrr_comp.hhrr_comp_last_update_date='".$lastuptdate_salary->format(FMT_DATETIME_MYSQL_PSA)."'" : "";
    $where .= $gaptoppcactual ? "\nAND 	hhrr_comp.hhrr_comp_gap_pc $gaptoppcactual '$gaptoppcactual_c'" : "";
	$where .= $lastreward ? "\nAND 	hhrr_comp.hhrr_comp_last_reward $lastreward '$lastreward_c'" : "";
	$where .= $actualbenefits ? "\nAND 	hhrr_comp.hhrr_comp_actual_benefits $actualbenefits '$actualbenefits_c'" : "";

	if($level != ""){
	$where .= $level !="0" ? "\nAND hhrr_education.level = '$level' ":"";
	}
	
	if ($title != "" )$where .= $title !="-1" ? "\nAND hhrr_education.title = '$title' ":"";
    if ($status != "" )$where .= $status !="-1" ? "\nAND hhrr_education.status = '$status' ":"";
    if ($seminary != "" )$where .= $seminary !="0" ? "\nAND hhrr_education.seminary = '$seminary' ":"";
    $where .= $activity  ? "\nAND (hhrr_education.title like '%$activity%'  and type='1') ":"";
    
    $where .= $performance  ? "\nAND hhrr_performance.performance = '$performance' ":"";
    $where .= $potential  ? "\nAND hhrr_performance.potential = '$potential' ":"";
    
	
	if( $start_time_aw !=""){
		$st_aw_h = substr($start_time_aw,0,2);
        $st_aw_m = substr($start_time_aw,2,2);
		$st_aw0 = $st_aw_h.$st_aw_m;
		$st_aw1 = $st_aw_h.":".$st_aw_m;

		$where .= "\nAND (users.workinghours like '%$st_aw_h%' OR users.workinghours like '%$st_aw0%' OR users.workinghours like '%$st_aw1%') ";
	}

	if($end_time_aw !=""){
		$et_aw_h = substr($end_time_aw,0,2);
        $et_aw_m = substr($end_time_aw,2,2);
		$et_aw0 = $et_aw_h.$st_ew_m;
		$et_aw1 = $et_aw_h.":".$et_aw_m;

		$where .= "\nAND (users.workinghours like '%$et_aw_h%' OR users.workinghours like '%$et_aw0%' OR users.workinghours like '%$et_aw1%') ";
	}
	
	$where .= $candidateage != "0" ? getAgeSqlString($candidateage) : "";
	//Pais-Provincia
	if(!is_null($country_id)) $where .= "\nAND users.user_country_id = '$country_id'";
	if(!is_null($state_id)) $where .= "\nAND users.user_state_id = '$state_id'";
	
	//ciudad
	if($city != "") $where .= "\nAND users.user_city like '%$city%'";
	//if(!is_null($city) && $city == "0") $where .= "\nAND TRIM(users.user_city) = ''";
	//elseif(!is_null($city) && $city != "0") $where .= "\nAND users.user_city = '$city'";

	//fechas
	if ($inputdateFrom != "" && $updatedateFrom != "")// Si esta buscando por las dos cosas es un OR, que haya ingresado O actualizado los datos entre esas fechas
	{
		$where .= "\nAND (users.date_created BETWEEN '".$inputdateFrom->format(FMT_DATETIME_MYSQL_PSA)."' AND '".$inputdateTo->format(FMT_DATETIME_MYSQL_PSA)."'";
		$where .= "\nOR	users.date_updated BETWEEN '".$updatedateFrom->format(FMT_DATETIME_MYSQL_PSA)."' AND '".$updatedateTo->format(FMT_DATETIME_MYSQL_PSA)."')";
	}
	else
	{
		$where .= $inputdateFrom != "" ? "\nAND	users.date_created BETWEEN '".$inputdateFrom->format(FMT_DATETIME_MYSQL_PSA)."' AND '".$inputdateTo->format(FMT_DATETIME_MYSQL_PSA)."'" : "";
		$where .= $updatedateFrom != "" ? "\nAND	users.date_updated BETWEEN '".$updatedateFrom->format(FMT_DATETIME_MYSQL_PSA)."' AND '".$updatedateTo->format(FMT_DATETIME_MYSQL_PSA)."'" : "";
	}

	$sql = "SELECT  distinct users.user_id, user_last_name, user_first_name, user_birthday, user_type,
			user_email, user_phone, user_home_phone, user_mobile, resume, costperhour, salarywanted, user_address1,
			docnumber
			from users 
			left join hhrr_ant on users.user_id = hhrr_ant.user_id 
			left join hhrr_comp on users.user_id = hhrr_comp.hhrr_comp_user_id
			left join hhrr_education on users.user_id = hhrr_education.id_user
			left join hhrr_dev on users.user_id = hhrr_dev_user_id
			left join hhrr_performance on users.user_id = hhrr_performance.user_id
			WHERE $where
			GROUP BY user_id, user_last_name, user_first_name, user_birthday, 
			user_email, user_phone, user_home_phone, user_mobile, resume, costperhour, user_address1,
			docnumber
			ORDER BY user_last_name, user_first_name";
       
	  //echo "<pre> $sql </pre>";
	 

  }
  else
  {
	
  ?>
    <div id="search" name="search">
    </div>
    <script language="javascript">
      showSearchPanel();	
    </script>
  <?
  }

  
  if($searchhhrr==$AppUI->_('Search') || $changepage== 1)
  {  	
  	if($_POST["showsqlquery"]) echo "<!-- $sql -->";
	
	$dp = new DataPager_post($sql, "hhrr_search");
	$dp->showPageLinks = true;
	$rows = $dp->getResults();
	if (!count( $rows)) {
		echo $AppUI->_("There are no matching records");
	} else {
	$rn = $dp->num_result;
	$pager_links = $dp->RenderNav_post("hhrrsearch", "changepage.value = '1' ");

	$fromrecord = (($dp->curr_page - 1) * $dp->rows) + 1;
	
	$torecord = $dp->num_result < ($dp->curr_page* $dp->rows) ? 
				$dp->num_result :
				($dp->curr_page * $dp->rows);
	$inforesult = $AppUI->_("Results").  
					" $fromrecord - $torecord ".
					 $AppUI->_("of"). " $dp->num_result"; 	
					 
					 
					 
	echo "
	<table border='0' width='100%' cellspacing='0' cellpadding='1'>
	<col width='30%'><col width='40%'><col width='30%'>
	<tr>
		<td align='center'>&nbsp;</td>
		<td align='center'>$pager_links</td>
		<td align='right'>$inforesult</td>
	</tr>
	<tr>
			<td height=1 colspan=5 bgcolor=#E9E9E9></td>
	</tr>
	</table>";  					 
?>

<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">
  <tr class="tableHeaderGral">
  <th align="left"><?=$AppUI->_("Name")?></th>
  <th align="left"><?php echo $AppUI->_('Phone')?></th>
  <th align="center"><?php echo $AppUI->_('E-mail')?></th>
  <th align="center"><?php echo $AppUI->_('Skills')?></th>
  <th align="center"><?php echo $AppUI->_('Pers. Info')?></th>
  <th align="center"><?php echo $AppUI->_('Age')?></th>
  <th align="center"><?php echo $AppUI->_('CV')?></th>
  <th align="center"><?php echo $AppUI->_('User Type')?></th>
  </tr>
  <?php  
  $cellid="gray";
  $emaillist="";
  $export = "";
  
	foreach ($rows as $row)
    {
    $id=$row["user_id"];

	$user_fullname = $row["user_last_name"].", ".$row["user_first_name"]; 
    $day = substr($row["user_birthday"],8,2);
    $month = substr($row["user_birthday"],5,2);
    $year = substr($row["user_birthday"],0,4);

    if($emaillist=="") $emaillist = $row["user_email"];
    else $emaillist = $emaillist .",".$row["user_email"];

    if($cellid=="gray") $cellid="white";
    else $cellid="gray";

		$age = calcular_edad($row["user_birthday"]);
		$age = $age ? $age : "--";

		$phones=array();
		if ($row["user_phone"]) $phones[]=$row["user_phone"];
		if ($row["user_home_phone"]) $phones[]=$row["user_home_phone"];
		if ($row["user_mobile"]) $phones[]=$row["user_mobile"];
		
		$phone = implode($phones, "; ");
		
		// Traigo la cantidad de items de matriz de cada usuario
		
		$sql_skill = "SELECT count(idskill) numskills FROM hhrrskills WHERE user_id = '".$id."' ";
		$resultsk = db_loadResult($sql_skill);
		
		//$resultsk = $row["numskills"];
        $hayskill=intval($resultsk>0);
    
  ?>
  <tr>
  <td>
  &nbsp;<a href="javascript: popup_resource('./index.php?m=hhrr&a=viewhhrr&id=<?= $id ?>&dialog=1&suppressLogo=1');" ><?=$user_fullname?>
  </a>  
  </td>
  <td class='celdatextoizquierda<?=$cellid?>'>&nbsp;<?=$phone ?></td>
  <td class='celdatextoizquierda<?=$cellid?>'>&nbsp;<?=$row["user_email"] ?></td>

<?
  if($hayskill==1)
  	echo "<td class='celdatextocentrado".$cellid."'>$resultsk<a class='' href='?m=hhrr&a=viewhhrr&tab=7&id=". $id."'>[".$AppUI->_("View")."]</a></td>";
  else            
  	echo "<td class='celdatextocentrado".$cellid."'>&nbsp;</td>";

  if($row["docnumber"]=="" && $phone =="" && $row["user_address1"]=="")echo "<td class='celdatextocentrado".$cellid."'>&nbsp;</td>";
  else            echo "<td class='celdatextocentrado".$cellid."'>&nbsp;".$AppUI->_("Yes")."</td>";
?>
  <td class='celdatextocentrado<?=$cellid?>'>&nbsp;<?=$age ?></td>
  <td class='celdatextocentrado<?=$cellid?>'>&nbsp;
  <?if($row["resume"]!="ninguna"  && trim($row["resume"])!=""){?>
    <a href="<?=$uploads_dir."/".$id?>/<?=str_replace(" ","%20",$row["resume"])?>"><img src="images/texticon.gif" border="0"></a>
  <?}?>
  </td>
  
  <td align="center">
   <?
      echo $utypes[$row["user_type"]];
     
   ?>
  </td>
  </tr>
  <tr class="tableRowLineCell"><td colspan="8"></td></tr>
  
  <?}?>
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
	
	}
	
}
?>
	<input type="hidden" name="page" value="<?=$page?>">
	<input type="hidden" name="changepage" value="0">
  </form>
  
  <? 
  
  if($dp->num_result>0){
  	
  	$list = db_loadList( $sql);
  	$cant = count($list);
  	$export = "";

	for ($i=0;$i<$cant;$i++)
	{
		$id = $list[$i]['user_id'];
		$export = $export.",".$id;
	}
  	
  	
  	
     echo "
     <table border='0' width='100%' cellspacing='0' cellpadding='15'>
      <tr>
        <td>";
	
		if ( $emaillist != "" )
		{
			$sMailTo=""; //Esta var hay que reemplazarla por la direccion a donde se dirige el mail
			echo "<a href='mailto:".$sMailTo."?BCC=".$emaillist."'>".$AppUI->_('Click Here to send an email to the result list')."</a>";
		}
		
	echo "
		</td>";
	
    echo   "<td align=\"right\"><br>
         <form method=\"POST\" name=\"importFrm\" enctype=\"multipart/form-data\" action=\"\" >
  	        <input type=\"hidden\" name=\"dosql\" value=\"do_search_export\" />
            <input type=\"hidden\" name=\"result\" value=\"$export\" >	
            <input type=\"submit\" value=\"".$AppUI->_('Export')."\" class=\"buttonbig\">
          </form>
        
        </td>
	  </tr>"; 
	  
	  echo " </table>";  
  
      
  }     
  ?>
     
 <SCRIPT LANGUAGE="JavaScript">changeCPHState(); </SCRIPT>  
 <SCRIPT LANGUAGE="JavaScript">changeSLRState(); </script>
 
   </body>
</html>





   
