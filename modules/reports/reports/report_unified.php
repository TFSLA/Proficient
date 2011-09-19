<?php
error_reporting( E_ALL & ~E_NOTICE);

function getMonthsBetweenDates($pObjDateOne, $pObjDateTwo){
	$arMonths = array();
	$intMonthDateOne = $pObjDateOne->getMonth();
	$intMonthDateTwo = $pObjDateTwo->getMonth();
//recorro todos los meses que hay entre la 1º fecha y la 2º fecha
	for($i=$intMonthDateOne; $i<=$intMonthDateTwo; $i++){
		$arMonths[]=$pObjDateOne->getMonth();//obtengo el mes
		$pObjDateOne->addMonths(1);//agrego un mes
	}
	
	if(count($arMonths)>0) return $arMonths;
}

function getTaskHoursByStatus( $pStartDate, $pEndDate, $pProject=NULL, $pUser=NULL, $pTask=NULL, $pTaskType=NULL, $pStatus=NULL, $pBillable=NULL){
	$arTimExp = array();
	$intSum = 0;
	$objTimExpTmp = new CTimExp();
	$arTimExp = $objTimExpTmp->getTimExpDateList($pUser, NULL, $pTaskType, $pProject, $pTask, NULL, NULL, NULL, $pStartDate, $pEndDate, $pStatus, $pBillable);
	if($arTimExp){
		foreach($arTimExp as $rTimExp){
			$intSum += doubleval($rTimExp["timexp_value"]);
		}
	}
	return $intSum;
}

function getBugHoursByStatus( $pStartDate, $pEndDate, $pProject=NULL, $pUser=NULL, $pBug=NULL, $pTaskType=NULL, $pStatus=NULL, $pBillable=NULL){
	$arTimExp = array();
	$intSum = 0;
	$objTimExpTmp = new CTimExp();
	$arTimExp = $objTimExpTmp->getTimExpDateList($pUser, NULL, $pTaskType, $pProject, NULL, $pBug, NULL, NULL, $pStartDate, $pEndDate, $pStatus, $pBillable);
	if($arTimExp){
		foreach($arTimExp as $rTimExp){
			$intSum += doubleval($rTimExp["timexp_value"]);
		}
	}
	return $intSum;
}

$do_report 	    = dPgetParam( $_POST, "do_report", 0 );
$log_start_date     = dPgetParam( $_POST, "log_start_date", 0 );
$log_end_date 	    = dPgetParam( $_POST, "log_end_date", 0 );
$log_all_projects   = dPgetParam($_POST["log_all_projects"], 0);
$log_all	    = dPgetParam($_POST["log_all"], 0);
$log_pdf = dPgetParam( $_POST, 'log_pdf', 0 );
$p_user_id = dPgetParam( $_POST, 'user_id', 0); //La p por parametro
$p_users_id = dPgetParam( $_POST, 'users_id', null);
$p_project_id = dPgetParam( $_POST, 'project_id', 0);
$p_hourtype = dPgetParam( $_POST, 'hourtype', null);
$p_status = dPgetParam( $_POST, 'status', null);
$p_billable = dPgetParam( $_POST, 'billable', null);
$p_groupby = dPgetParam( $_POST, 'groupby', null);
$p_postback = dPgetParam( $_POST, 'form_postback', 0);

$company_id=dPgetParam( $_POST, 'company_id', 0);
$canal_id = dPgetParam( $_POST, "canal_id", 0 );
$user_id = dPgetParam( $_POST, "user_id", 0 );
$project_id = dPgetParam( $_POST, "project_id", 0 );

$intNroColumnas = 0;

$arStatus = array(
				"-1"=> "All",
				"3" => "Approved",
				"2" => "Disapproved",
				"0" => "Pending"
					);
$arHoursType = array(
					""  => "All",
					"1" => "Assigned",
					"2" => "Reported"
					);
					
$arBillable = array(
					"-1"=> "All",
					"1" => "Yes",
					"0" => "No"
					);

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : new CDate();
$end_date   = intval( $log_end_date )   ? new CDate( $log_end_date ) : new CDate();

if (!$log_start_date) {
	$start_date->subtractSpan( new Date_Span( "14,0,0,0" ) );
}
$end_date->setTime( 23, 59, 59 );

//Load viewable projects
include("modules/projects/read_projects.inc.php");

?>
<script language="javascript">
var strIDs = "<?php echo $p_users_id != null ? $p_users_id : "" ?>";// almacena los valores seleccionados despues del postback
var intOldStatus = 0;
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.log_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

//setea el cbo de Estados segun la opcion de "Tipo Horas" seleccionadas
function setCboStatus(){
	var f = document.editFrm;
	if(f.hourtype.value == "1"){
		intOldStatus = f.status.selectedIndex;
		f.status.selectedIndex = 0;
		f.status.disabled = true;
	}else{
		f.status.disabled = false;
	}
}

//hace el postback para rellenar el cbo de usuarios segun el proyecto
function selectProjectUsers(){
	var f = document.editFrm;
	f.form_postback.value = "0";
	f.submit();
}

function changeSelection(obj){
	document.editFrm.user_id.multiple = true;
	for(i=0; i < document.editFrm.user_id.length; i++ ){
		if(document.editFrm.user_id.options[0].selected){
			document.editFrm.user_id.multiple = false;
			for(i=1; i < document.editFrm.user_id.length; i++ ){
				document.editFrm.user_id.options[i].selected = false;
			}
		}
	}
}

//obtiene los valores de los items seleccionados y los
//almacena en un hidden
function getSelectedUsers(){
	var f;
	var strIDs;
	strIDs = "";
	f = document.editFrm;
	
	//recorro todo el select y busco los que estan seleccionados
	for(i=0; i < f.user_id.length; i++ ){
		if(f.user_id.options[i].selected){
			strIDs += f.user_id.options[i].value + ",";
		}
	}
	strIDs = strIDs.substr(0,(strIDs.length-1));//extraigo la ultima coma separadora
	f.users_id.value = strIDs;
	f.submit();
}

//recupera de una variable los items seleccionados
//y los marca en el select cuando hizo el postback
function setSelectedUsers(){
	var f;
	var arIDs;
	f = document.editFrm;
	if(strIDs.length > 0){
		arIDs = strIDs.split(",");

		for(x=0; x < arIDs.length; x++){
			for(i=0; i < f.user_id.length; i++ ){
				if(f.user_id.options[i].value == arIDs[x]){
					f.user_id.options[i].selected = true;
					continue;
				}
			}	
		}
		
		
	}else{
		f.user_id.options[0].selected = true;
	}
}

function canal_project(company, canal, project, user )
{  
    xajax_addCanal('canal_id', company ,canal,'TRUE','','');
    xajax_addProjects(company, canal, project, 'FALSE', '', '', 'project_id' );
    xajax_addUsersProjects('user_id', project ,canal ,company , user );
}

</script>

<form name="editFrm" action="index.php?m=reports" method="post">
<input type="hidden" name="project_id_1" value="<?php echo $project_id;?>" />
<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />
<input type="hidden" name="form_postback" value="1">
<input type="hidden" name="users_id">

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
	<td align="left" nowrap="nowrap" width="1%"> <?php echo $AppUI->_('Company');?>: </td>
	<td align="left" nowrap="nowrap" width="1%">
		<?
			$sql = "SELECT company_id, company_name FROM companies;";
			$companies = db_loadHashList( $sql );
			 
			echo arraySelect($companies, "company_id", "style='font-size: 10px;' onchange=\"canal_project(document.editFrm.company_id.value, '', '','' )\"", $company_id,'',true,'160px');
		?>
	</td>
	<td width="100%">
		<?php echo $AppUI->_('User');?>:&nbsp;&nbsp;
		<span style="position:relative;top:0px; left:0px;">
		<div style="position:absolute; top:0px; width:100px; height:30px; visibility:visible; z-index: 0;" id="cboUsers">
			<select size="5" multiple  id="user_id" name="user_id" style="font-size:10px; width:160px; "></select>
			<script type="text/javascript">
				canal_project(document.editFrm.company_id.value, '<?=$canal_id?>', '<?=$p_project_id?>','<?=$p_user_id ?>' );	
			</script>		
		</div>
		</span>
	</td>	
</tr>

<tr>
	<td align="left" nowrap="nowrap" width="1%">
	<?php echo $AppUI->_('Canal');?>:
	</td>
	<td align="left" nowrap="nowrap" width="1%">
		<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td>
				<?
				//Con esto primego genero el select y llamo a la funcion de ajax addProjects para que lo complete
				?>
				<select name="canal_id" id="canal_id" onchange="canal_project(document.editFrm.company_id.value, document.editFrm.canal_id.value, '','' )" style="width: 160px; font-size: 10px;"></select>		
				
			</td>
		</tr>
	</table>
</td>
</tr>  

<tr>
	<td align="left" nowrap="nowrap" width="1%">
	<?php echo $AppUI->_('Project');?>:
	</td>
	<td align="left" nowrap="nowrap" width="1%">
		<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td>
				<?
				//Con esto primego genero el select y llamo a la funcion de ajax addProjects para que lo complete
				?>
				<select name="project_id" id="project_id" onchange="canal_project(document.editFrm.company_id.value, document.editFrm.canal_id.value, document.editFrm.project_id.value,'' )" style="width: 160px; font-size: 10px;"></select>		
				
			</td>
		</tr>
	</table>
</td>
</tr>  
  <tr>
    <td align="left" nowrap="nowrap" width="1%"> <?php echo $AppUI->_('Hours Type');?>: </td>
    <td align="left" nowrap="nowrap" width="1%">
      <?php 
      echo arraySelect($arHoursType, "hourtype", "style='font-size:10px' onChange=\"javascript:setCboStatus();\"", intval($p_hourtype), true);
      ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap" width="1%"> <?php echo $AppUI->_('Status');?>: </td>
    <td align="left" nowrap="nowrap" width="1%">
      <?php 
	if($p_status == null) $p_status = -1;//para que se muestre seleccionada la opcion "todas", porque el "" lo toma como null o cero
	echo arraySelect($arStatus, "status","style='font-size:10px'", intval($p_status), true);
	if($p_status == "-1") $p_status = null;//restablezco el valor de null cuando selecciona "todas"
	?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap" width="1%"> <?php echo $AppUI->_('Billables');?>: </td>
    <td align="left" nowrap="nowrap" width="1%">
      <?php 
      if($p_billable == null) $p_billable = -1;
      echo arraySelect($arBillable, "billable", "style='font-size:10px'", $p_billable, true);
      if($p_billable == "-1") $p_billable = null;
      ?>
    </td>
  </tr>
  <tr>
    <td align="left" nowrap="nowrap" width="1%"> <?php echo $AppUI->_('Association');?>: </td>
    <td align="left" nowrap="nowrap" width="1%">
      <select name="groupby" style="font-size:10px">
        <option value="1"><?php echo $AppUI->_('Monthly');?></option>
      </select>
    </td>
  </tr>
  <tr>
          <td align = "left" nowrap="nowrap">
             <?php echo $AppUI->_('From');?>:
          </td>
          <td  nowrap="nowrap">
              <input type="hidden" name="log_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
              <input type="text" size="10" name="start_date" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" />
              <a href="#" onClick="popCalendar('start_date')"> <img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              
          </td>
          <td align = "left" nowrap="nowrap">
              <?php echo $AppUI->_('To');?>&nbsp;&nbsp;
         
              <input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
              <input type="text" size="10" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />
              <a href="#" onClick="popCalendar('end_date')"> <img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" /></a> 
          </td>
       
  </tr>
  <tr>
    <td nowrap="nowrap" colspan="2">
      <input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo "checked" ?> />
      <?php echo $AppUI->_( 'Make PDF' );?>
    </td>
    <td align="right" width="50%"  nowrap="nowrap">
      <input class="button" type="button" name="do_report" onclick="javascript:getSelectedUsers();" value="<?php echo $AppUI->_('submit');?>" />
    </td>
  </tr>
</table>
</form>
<?php
if($p_postback != 0){
?>
<script language="javascript">
setSelectedUsers();
setCboStatus();
</script>
<table  border="1" cellpadding="0" cellspacing="0">
	  <?php
	  $strHtml = "";//almacena todo el html del reporte
	  $intColSpan = 5;//el colspan de "horas reportadas"
	  $strMonthNameOld = "";//el nombre del mes
	  
	  $arProjects = array();
	  if($p_project_id){
	  	$arProjects[]["project_id"] = $p_project_id;
	  }else{
	  	$arProjects = $projects;
	  }
	  
	  
	
	/*echo "<pre>";
	print_r($arProjects);
	echo "</pre>";*/
	//return;  
	  
	  $objProject = new CProject();
	  $objUser = new CUser();
	  $objTimExp = new CTimExp();
	  $objTask = new CTask();
	  
	  foreach($arProjects as $rProj){
	  	//variables
	  	
	  	//total proyecto
	  	$intTotProjTaskWork = 0;
	  	$intTotProjApprovedHours = 0;
	  	$intTotProjDisapprovedHours = 0;
	  	$intTotProjPendingHours = 0;
	  	$intTotProjBillableHours = 0;
	  	$intTotProjNoBillableHours = 0;
		
		//horas
		$intTaskWork = 0;
	  	$intApprovedHours = 0;
	  	$intDisapprovedHours = 0;
	  	$intPendingHours = 0;
	  	$intBillableHours = 0;
	  	$intNoBillableHours = 0;
	  	
	  	
	  	
	  	$objProject->load($rProj["project_id"]);
	  	$strProjectName = $objProject->project_name;

	  	$strHtml .= "<tr>
	  					<td>
		  					<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\" width=\"100%\">
							<tr>
								<td>{$strProjectName}</td>
							</tr>
		  					<tr>
		  						<td>
		  							<table width=\"100%\"  border=\"0\" cellpadding=\"0\" cellspacing=\"0\" id=\"tbGContent\">
	                      				<tr>
	                        				<td>
		  										<table width=\"100%\"  border=\"0\" cellpadding=\"0\" cellspacing=\"0\" id=\"tbGrilla\">
						                          <!--tr>
						                            <td>&nbsp;</td>
						                            <td>&nbsp;</td>
						                            <td>&nbsp;</td>
						                            <td>&nbsp;</td>
						                            <td colspan=\"5\" align=\"center\">" . $AppUI->_( 'Reported Hours' ) . "</td>
	  											  </tr-->
						                          <tr>
						                            <td width=\"80\"><strong>" . $AppUI->_( 'Month' ) . "</strong></td>
						                            <td width=\"100\"><strong>" . $AppUI->_( 'User' ) . "</strong></td>
						                            <td width=\"150\"><strong>" . $AppUI->_( 'Task' ) . "</strong></td>";
	  	
	  	if($p_hourtype == "1" || $p_hourtype == NULL){
	  		$strHtml .= "								<td width=\"100\" align=\"center\" class=\"rep_plannedhours\"><strong>" . $AppUI->_( 'Planned' ) . "</strong></td>";
	  		$intNroColumnas++;
	  	}
	  	
	  	if($p_hourtype == "2" || $p_hourtype == NULL){
	  	  	if($p_status === "3" || $p_status == NULL){
		  	$strHtml .= "	                            <td width=\"100\" align=\"center\" class=\"rep_approvedhours\"><strong>" . $AppUI->_( 'Approved' ) . "</strong></td>";
		  	$intNroColumnas++;
	  	  	}
		  	
		  	if($p_status === "2" || $p_status == NULL){
		  	$strHtml .= "	                            <td width=\"100\" align=\"center\" class=\"rep_disapprovedhours\"><strong>" . $AppUI->_( 'Disapproved' ) . "</strong></td>";
		  	$intNroColumnas++;
		  	}
		  	
		  	if($p_status === "0" || $p_status == NULL){
		  	$strHtml .= "	                            <td width=\"100\" align=\"center\" class=\"rep_pendinghours\"><strong>" . $AppUI->_( 'Pending' ) . "</strong></td>";
		  	$intNroColumnas++;
		  	}
	  	}
	  	
	  	if($p_billable == "1" || $p_billable == NULL){
		  	$strHtml .= "	                            <td width=\"100\" align=\"center\" class=\"rep_billablehours\"><strong>" . $AppUI->_( 'Billables' ) . "</strong></td>";
		  	$intNroColumnas++;
	  	}
	  	if($p_billable == "0" || $p_billable == NULL){
	  		$strHtml .= "								<td width=\"110\" align=\"center\" class=\"rep_nobillablehours\"><strong>" . $AppUI->_( 'No billables' ) . "</strong></td>";
	  	$intNroColumnas++;
	  	}
	  	
	  /*$strHtml .= "<td width=\"80\" align=\"center\" class=\"rep_subtotal\"><strong>SubTotal</strong></td>                         
		                                         </tr>
						                          <tr>
						                            <td colspan=\"3\">&nbsp;</td>
						                            <td colspan=\"$intNroColumnas\">&nbsp;</td>
						                          </tr>";*/

		$strHtml .= " </tr>
						                          <tr>
						                            <td colspan=\"3\">&nbsp;</td>
						                            <td colspan=\"$intNroColumnas\">&nbsp;</td>
						                          </tr>";
	  	//meses
	  	$arTmp = getMonthsBetweenDates($start_date, $end_date);
	  	$tmpObjDate = $start_date;//utilizado para obtener los nombres de los meses sin afectar el date original
	  	
	  	//users
	  	$arUsersTmp = array();
	  	$arUsers = array();
	  	if($user_id){
	  		//cargar todos los usuarios seleccionados
	  		$arUsersTmp = explode(",", $p_users_id);
	  		foreach($arUsersTmp as $rUserTmp){
	  			$arUsers[]["user_id"] = $rUserTmp;
	  		}
	  	}else{
	  		//cargar todos los usuarios
	  		
				$strUsersSqlWhere = "";
				if($p_project_id){
					$strUsersSqlWhere = " AND projects.project_id='$p_project_id' ";
				}
				
				//Esto habria que ponerlo en un inc como se hace con los projects
				if ($strUsersSqlWhere=="")
				{
					$sql = "
					SELECT DISTINCT(user_id), user_username, user_last_name, user_first_name, permission_user, user_email, company_name, user_company
					FROM users
					LEFT JOIN permissions ON user_id = permission_user
					LEFT JOIN companies ON company_id = user_company
					where user_type <> 5 
					";
				}
				else
				{
				$sql="
						SELECT DISTINCT(users.user_id), projects.project_id, user_username, user_last_name, user_first_name
						FROM projects
						LEFT  JOIN project_roles ON projects.project_id = project_roles.project_id
						LEFT JOIN users ON project_roles.user_id = users.user_id
						WHERE users.user_type <> '5' $strUsersSqlWhere";
				}
				
				$users = db_loadList( $sql );
	  		$arUsers = $users;
	  	}
	  	/*
	  	echo "<pre>";
	  	print_r($arUsers);
	  	echo "</pre>";*/
	  	
	  	if($arTmp){
	  		foreach($arTmp as $rTmp){//meses
	  		
	  			//total mensual
			  	$intTotMonthTaskWork = 0;
			  	$intTotMonthApprovedHours = 0;
			  	$intTotMonthDisapprovedHours = 0;
			  	$intTotMonthPendingHours = 0;
			  	$intTotMonthBillableHours = 0;
			  	$intTotMonthNoBillableHours = 0;
			  			
	  			//$bNewMonth = true;
	  			$tmpObjDate->setMonth($rTmp);//seteo el obj date temporal con el nuevo mes
	  			/*si es mayor el tmpObjDate es porque el dia del mes debe empezar en el 01*/
	  			if($tmpObjDate->format(FMT_TIMESTAMP_DATE) > $start_date->format(FMT_TIMESTAMP_DATE)){
	  				$tmpObjDate->setDay(1);
	  			}
	  			$tmpObjEndDate = $tmpObjDate;
	  			$tmpObjEndDate->setDay($tmpObjEndDate->getDaysInMonth());//pongo la fecha en el ultimo dia del mes
	  			if($tmpObjEndDate->format(FMT_TIMESTAMP_DATE) > $end_date->format(FMT_TIMESTAMP_DATE)){
	  				$tmpObjEndDate = $end_date;
	  			}
	  			/*
	  			comparar la fecha hasta con la fecha inicio con el mes agregado si esta ultima
	  			es mas grande debo usar como final la fecha hasta sino tengo que usar la fecha inicio
	  			con el ultimo dia del mes 
	  			*/
	  			//$tmpObjEndDate = 
	  			//$strMonthName = $tmpObjDate->getMonthName();
	  			if($strMonthName != $tmpObjDate->getMonthName()){
					$strMonthName = $tmpObjDate->getMonthName();
	  				$strHtml .= "	<tr class=\"\">
				  						<td colspan=\"3\">{$strMonthName}</td>
					  					<td colspan=\"$intNroColumnas\"></td>
					                </tr>";
					
	  			}
			  	foreach($arUsers as $rUser){
			  		
			  		//subtotal user
				  	$intTotUserTaskWork = 0;
					$intTotUserApprovedHours = 0;
					$intTotUserDisapprovedHours = 0;
					$intTotUserPendingHours = 0; 
					$intTotUserBillableHours = 0; 
					$intTotUserNoBillableHours = 0; 
					
			  		//$bNewUser = true;//para saber si tengo que colocar el nombre de usuario
			  		$objUser->load($rUser["user_id"]);
			  		$strUserName = $objUser->user_username;
			  		
			  		$strHtml .= "	<tr class=\"rep_row_MonthName\">
				  						<td>&nbsp;</td>
					  					<td>{$strUserName}</td>
					  					<td></td>
					                    <td colspan=\"$intNroColumnas\"></td>
					                </tr>";

					$arTasks = $objTask->getTasksForPeriod($tmpObjDate, $tmpObjEndDate, 0, $objProject->project_id, $objUser->user_id);			  		
			  		foreach($arTasks as $rTask){
			  			$intTaskWork = $rTask["task_work"];
			  			if($p_status == "3" || $p_status == NULL)
			  				$intApprovedHours = getTaskHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rTask["task_id"], "1", "3", NULL);
			  			
			  			if($p_status == "2" || $p_status == NULL)
			  				$intDisapprovedHours = getTaskHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rTask["task_id"], "1", "2", NULL);
			  			
			  			if($p_status == "0" || $p_status == NULL){
			  				$intPendingHours = getTaskHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rTask["task_id"], "1", "0", NULL);
			  				$intOnCourseHours = getTaskHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rTask["task_id"], "1", "1", NULL);
			  			}
			  			
			  			$strShowPartialBillable = NULL;
			  			if($p_status || $p_status != NULL) 
			  				$strShowPartialBillable = $p_status;//lo uso como parametro para buscar las horas facturables
			  			
			  			if($p_billable == "1" || $p_billable == NULL)
			  				$intBillableHours = getTaskHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rTask["task_id"], "1", $strShowPartialBillable, "1");
			  			
			  			if($p_billable == "0" || $p_billable == NULL)
			  				$intNoBillableHours = getTaskHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rTask["task_id"], "1", $strShowPartialBillable, "0");
			  			
			  			//si hay que mostrar pendientes, hay que sumarles las oncourse
			  			if($p_status === "0"){
			  				if($p_billable == "1" || $p_billable == NULL)
			  					$intBillableHours += getTaskHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rTask["task_id"], "1", "1", "1");
			  				if($p_billable == "0" || $p_billable == NULL)
			  					$intNoBillableHours += getTaskHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rTask["task_id"], "1", "1", "0");
			  			}
			  			
			  			
			  			$intPendingHours += $intOnCourseHours;
			  			$strHtml .= "<tr>
				  						<td></td>
					  					<td></td>
					  					<td>{$rTask["task_name"]}</td>";
			  			if($p_hourtype == "1" || $p_hourtype == NULL){
			  				$strHtml .= "	<td align=\"center\" class=\"rep_plannedhours\">{$intTaskWork}</td>";
			  			}
			  			
			  			if($p_hourtype == "2" || $p_hourtype == NULL){
				  			if($p_status === "3" || $p_status == NULL)
				  			$strHtml .= "   <td align=\"center\" class=\"rep_approvedhours\">{$intApprovedHours}</td>";
				  			if($p_status === "2" || $p_status == NULL)
				  			$strHtml .= "   <td align=\"center\" class=\"rep_disapprovedhours\">{$intDisapprovedHours}</td>";
				  			if($p_status === "0" || $p_status == NULL)
				  			$strHtml .= "   <td align=\"center\" class=\"rep_pendinghours\">{$intPendingHours}</td>";
			  			}
			  			
			  			if($p_billable == "1" || $p_billable == NULL){
			  				$strHtml .= "   <td align=\"center\" class=\"rep_billablehours\">{$intBillableHours}</td>";
			  			}
			  			
			  			if($p_billable == "0" || $p_billable == NULL){
			  				$strHtml .= "	<td align=\"center\" class=\"rep_nobillablehours\">{$intNoBillableHours}</td>";
			  			}
					  	
					  	$strHtml .= "	</tr>";
				  		/*if($bNewMonth){
		  				$strMonthName = "&nbsp;";
		  				$bNewMonth = false;
			  			}
			  			if($bNewUser){
			  				$strUserName = "&nbsp;";
			  				$bNewUser = false;
			  			}*/
			  			$intTotUserTaskWork += $intTaskWork;
			  			$intTotUserApprovedHours += $intApprovedHours;
			  			$intTotUserDisapprovedHours += $intDisapprovedHours;
			  			$intTotUserPendingHours += $intPendingHours;
			  			$intTotUserBillableHours += $intBillableHours;
			  			$intTotUserNoBillableHours += $intNoBillableHours;
			  			
			  		}
			  		$arBugs = $objTimExp->getTimExpDateList($objUser->user_id, NULL, "1", $objProject->project_id, NULL, "0", NULL, NULL, $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL));
					foreach($arBugs as $rBug){
						if($p_status == "3" || $p_status == NULL)
							$intApprovedHours = getBugHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rBug["bug_id"], "1", "3", NULL);
			  			if($p_status == "2" || $p_status == NULL)
							$intDisapprovedHours = getBugHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rBug["bug_id"], "1", "2", NULL);
			  			if($p_status == "0" || $p_status == NULL){
							$intPendingHours = getBugHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rBug["bug_id"], "1", "0", NULL);
			  				$intOnCourseHours = getBugHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rBug["bug_id"], "1", "1", NULL);
			  			}
			  			$strShowPartialBillable = NULL;
			  			if($p_status || $p_status != NULL) 
			  			$strShowPartialBillable = $p_status;//lo uso como parametro para buscar las horas facturables
			  			
			  			if($p_billable == "1" || $p_billable == NULL)
			  			$intBillableHours = getBugHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rBug["bug_id"], "1", $strShowPartialBillable, "1");
			  			if($p_billable == "0" || $p_billable == NULL)
			  			$intNoBillableHours = getBugHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rBug["bug_id"], "1", $strShowPartialBillable, "0");
			  			
			  			//si hay que mostrar pendientes, hay que sumarles las oncourse
			  			if($p_status === "0"){
			  				$intBillableHours += getBugHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rBug["bug_id"], "1", "1", "1");
			  				$intNoBillableHours += getBugHoursByStatus( $tmpObjDate->format(FMT_DATETIME_MYSQL), $tmpObjEndDate->format(FMT_DATETIME_MYSQL), $objProject->project_id, $objUser->user_id, $rBug["bug_id"], "1", "1", "0");
			  			}
			  			
			  			$intPendingHours += $intOnCourseHours;
			  			$strHtml .= "<tr>
				  						<td></td>
					  					<td></td>
					  					<td>{$rBug["summary"]}</td>";
			  			if($p_hourtype == "1" || $p_hourtype == NULL){
			  				$strHtml .= "	<td align=\"center\" class=\"rep_plannedhours\">0</td>";
			  			}
			  			
			  			if($p_hourtype == "2" || $p_hourtype == NULL){
				  			if($p_status === "3" || $p_status == NULL)
				  			$strHtml .= "   <td align=\"center\" class=\"rep_approvedhours\">{$intApprovedHours}</td>";
				  			if($p_status === "2" || $p_status == NULL)
				  			$strHtml .= "   <td align=\"center\" class=\"rep_disapprovedhours\">{$intDisapprovedHours}</td>";
				  			if($p_status === "0" || $p_status == NULL)
				  			$strHtml .= "   <td align=\"center\" class=\"rep_pendinghours\">{$intPendingHours}</td>";
			  			}
			  			
			  			if($p_billable == "1" || $p_billable == NULL){
			  				$strHtml .= "   <td align=\"center\" class=\"rep_billablehours\">{$intBillableHours}</td>";
			  			}
			  			if($p_billable == "0" || $p_billable == NULL){
			  				$strHtml .= "   <td align=\"center\" class=\"rep_nobillablehours\">{$intNoBillableHours}</td>";
			  			}
					  	
					  	$strHtml .= "	</tr>";
				  		/*if($bNewMonth){
			  				$strMonthName = "&nbsp;";
			  				$bNewMonth = false;
			  			}
			  			if($bNewUser){
			  				$strUserName = "&nbsp;";
			  				$bNewUser = false;
			  			}*/
			  			
			  			$intTotUserApprovedHours += $intApprovedHours;
			  			$intTotUserDisapprovedHours += $intDisapprovedHours;
			  			$intTotUserPendingHours += $intPendingHours;
			  			$intTotUserBillableHours += $intBillableHours;
			  			$intTotUserNoBillableHours += $intNoBillableHours;
					}
					
					//Subtotales
					$strHtml .= "<tr class='rep_subtotal'>
			  						<td colspan='3' align=\"right\">SubTotal $objUser->user_username&nbsp;</td>";
					if($p_hourtype == "1" || $p_hourtype == NULL){
						$strHtml .= "	<td align=\"center\" class=\"rep_plannedhours\">{$intTotUserTaskWork}</td>";
					}
					if($p_hourtype == "2" || $p_hourtype == NULL){
						if($p_status === "3" || $p_status == NULL)
						$strHtml .= "   <td align=\"center\" class=\"rep_approvedhours\">{$intTotUserApprovedHours}</td>";
						
						if($p_status === "2" || $p_status == NULL)
						$strHtml .= "   <td align=\"center\" class=\"rep_disapprovedhours\">{$intTotUserDisapprovedHours}</td>";
						
						if($p_status === "0" || $p_status == NULL)
						$strHtml .= "   <td align=\"center\" class=\"rep_pendinghours\">{$intTotUserPendingHours}</td>";
					}
					if($p_billable == "1" || $p_billable == NULL){
						$strHtml .= "   <td align=\"center\" class=\"rep_billablehours\">{$intTotUserBillableHours}</td>";
					}
					if($p_billable == "0" || $p_billable == NULL){
						$strHtml .= "	<td align=\"center\" class=\"rep_nobillablehours\">{$intTotUserNoBillableHours}</td>";
					}
					  	
					$strHtml .= "	</tr>";

					$intTotMonthTaskWork += $intTotUserTaskWork;
					$intTotMonthApprovedHours += $intTotUserApprovedHours;
					$intTotMonthDisapprovedHours += $intTotUserDisapprovedHours;
					$intTotMonthPendingHours += $intTotUserPendingHours;
					$intTotMonthBillableHours += $intTotUserBillableHours;
					$intTotMonthNoBillableHours += $intTotUserNoBillableHours;
					
					
//**************
			  	}
			  	//Subtotales Mensual
					$strHtml .= "<tr class='rep_subtotal_mensual'>
									<td colspan='3' align=\"left\">Subtotal {$strMonthName}&nbsp;</td>";
					if($p_hourtype == "1" || $p_hourtype == NULL){
						$strHtml .= "	<td align=\"center\" class=\"rep_plannedhours\">{$intTotMonthTaskWork}</td>";
					}
					if($p_hourtype == "2" || $p_hourtype == NULL){
						if($p_status === "3" || $p_status == NULL)
						$strHtml .= "	<td align=\"center\" class=\"rep_approvedhours\">{$intTotMonthApprovedHours}</td>";
						
						if($p_status === "2" || $p_status == NULL)
						$strHtml .= "   <td align=\"center\" class=\"rep_disapprovedhours\">{$intTotMonthDisapprovedHours}</td>";
						
						if($p_status === "0" || $p_status == NULL)
						$strHtml .= "   <td align=\"center\" class=\"rep_pendinghours\">{$intTotMonthPendingHours}</td>";
					}
					
					if($p_billable == "1" || $p_billable == NULL){					
						$strHtml .= "   <td align=\"center\" class=\"rep_billablehours\">{$intTotMonthBillableHours}</td>";
					}
					if($p_billable == "0" || $p_billable == NULL){
						$strHtml .= "	<td align=\"center\" class=\"rep_nobillablehours\">{$intTotMonthNoBillableHours}</td>";
					}
					  	
					$strHtml .= "	</tr>";
					
					$intTotProjTaskWork += $intTotMonthTaskWork;
					$intTotProjApprovedHours += $intTotMonthApprovedHours;
					$intTotProjDisapprovedHours += $intTotMonthDisapprovedHours;
					$intTotProjPendingHours += $intTotMonthPendingHours;
					$intTotProjBillableHours += $intTotMonthBillableHours;
					$intTotProjNoBillableHours += $intTotMonthNoBillableHours;
					
				$strHtml .= "	<tr>
		  							<td colspan=\"3\">&nbsp;</td>	
		  							<td colspan=\"$intNroColumnas\">&nbsp;</td>
		  						</tr>
		  					";
	  		}
  		}

	  	//Total Proyecto
		$strHtml .= "<tr class='rep_total_proyecto'>
						<td colspan='3' align=\"left\">Total {$strProjectName}&nbsp;</td>";
		if($p_hourtype == "1" || $p_hourtype == NULL){
			$strHtml .= "	<td align=\"center\" class=\"rep_plannedhours\">{$intTotProjTaskWork}</td>";
		}
		if($p_hourtype == "2" || $p_hourtype == NULL){
			if($p_status === "3" || $p_status == NULL)
			$strHtml .= "	<td align=\"center\" class=\"rep_approvedhours\">{$intTotProjApprovedHours}</td>";
			
			if($p_status === "2" || $p_status == NULL)
			$strHtml .= "   <td align=\"center\" class=\"rep_disapprovedhours\">{$intTotProjDisapprovedHours}</td>";
			
			if($p_status === "0" || $p_status == NULL)
			$strHtml .= "   <td align=\"center\" class=\"rep_pendinghours\">{$intTotProjPendingHours}</td>";
		}
		
		if($p_billable == "1" || $p_billable == NULL){
			$strHtml .= "   <td align=\"center\" class=\"rep_billablehours\">{$intTotProjBillableHours}</td>";
		}
		if($p_billable == "0" || $p_billable == NULL){
			$strHtml .= "	<td align=\"center\" class=\"rep_nobillablehours\">{$intTotProjNoBillableHours}</td>";
		}

					  	
		$strHtml .= "</tr>";
	  				
	  	$strHtml .= "
						                         </table>
		  									</td>
		  								</tr>
		  							</table>
		  						</td>
		  					</tr>
		  					</table>
	  		 	 		</td>
					</tr>";
	  	
	  }
	  echo $strHtml;
	  ?>
</table>
<?php
}
?>