<?php

global $AppUI, $canEdit;

$id = isset($_GET['id']) ? $_GET['id'] : 0;

define("NOTE_INTERVIEW", 1); 
define("NOTE_INTERNAL", 2);

$maritalstates= dPgetSysVal("MaritalState");
$IMTypes= dPgetSysVal("IMType");
$SCandidateStatus = dPgetSysVal("CandidateStatus");

$uploads_dir = $AppUI->getConfig('hhrr_uploads_dir');

$canAddHHRR = CHhrr::canAdd();
$canEditHHRR = CHhrr::canEdit($id);



$canEditModule = !getDenyEdit( "hhrr" );
		
      $result = mysql_query("SELECT users.*, departments.dept_name, companies.company_name   FROM users
LEFT JOIN companies ON users.user_company = companies.company_id
LEFT JOIN departments ON users.user_department = departments.dept_id
WHERE user_id = $id;");
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      $id = $row["user_id"];

	  if ($id) {
		
		$start_time_am = $row["start_time_am"] ? new CDate( "0000-00-00 ".$row["start_time_am"] ) : null;
		$end_time_am = $row["end_time_am"]  ? new CDate( "0000-00-00 ".$row["end_time_am"] ) : null;
		$start_time_pm = $row["start_time_pm"] ? new CDate( "0000-00-00 ".$row["start_time_pm"] ) : null;
		$end_time_pm = $row["end_time_pm"] ? new CDate( "0000-00-00 ".$row["end_time_pm"] ) : null;		
	  } else {
		$start_time_am = null;
		$end_time_am = null;
		$start_time_pm = null;
		$end_time_pm = null;
	  }	

      $firstname = $row["user_first_name"];
      $lastname = $row["user_last_name"];
      $birthday = $row["user_birthday"];
      $doctype = $row["doctype"];
      $docnumber = $row["docnumber"];
      $maritalstate = $row["maritalstate"];
      $nationality = $row["nationality"];
      $email = $row["user_email"];
      $phone = $row["user_phone"];
      $homephone = $row["user_home_phone"];
      $cellphone = $row["user_mobile"];
      $address = $row["user_address1"];
      $city = $row["user_city"];
      $zip = $row["user_zip"];
      $country_id = $row["user_country_id"];
      $country = $AppUI->_(CUser::getUserCountry($id, $country_id));
      $state_id = $row["user_state_id"];
      $state = $AppUI->_(CUser::getUserState($id, $country_id, $state_id));
      $children = $row["children"];
      $url = $row["url"];
      $taxidtype = $row["taxidtype"];
      $taxidnumber = $row["taxidnumber"];
      $im_type = $row["user_im_type"];
      $im_id = $row["user_im_id"];
      $resume = $row["resume"];
      $logo = $row["user_pic"];
      $inputdate = $row["date_created"];
      $updateddate = $row["date_updated"];
      $costperhour = $row["costperhour"];
      $actualjob = $row["actualjob"];
      $actualcompany = $row["actualcompany"];
      $workinghours = $row["workinghours"];
      $salarywanted = $row["salarywanted"];
      $wantsfreelance = $row["wantsfreelance"];
      $wantsfulltime = $row["wantsfulltime"];
      $wantsparttime = $row["wantsparttime"];
      $wasinterviewed = $row["wasinterviewed"];
      $hoursavailableperday = $row["hoursavailableperday"];
      $candidatestatus = $row["candidatestatus"];
      $department = $row["user_department"];
      $company = $row["user_company"];
      $position = $row["user_job_title"];
      $user_type = $row["user_type"];
      $legajo = $row["legajo"];
      $user_supervisor = $row["user_supervisor"];
      $company_name =  $row["company_name"];
      $dept_name =  $row["dept_name"];
      $daily_working_hours = $row["daily_working_hours"];
      $user_input_date_company = $row["user_input_date_company"];
      

	$ttl = $firstname." ".$lastname;
	$titleBlock = new CTitleBlock( $ttl, 'hhrr.gif', $m, 'hhrr.index' );

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');
$inputdate = new CDate($inputdate);
$updateddate = new CDate($updateddate);
$inputdate = $inputdate->format($df." ");
$updateddate = $updateddate->format($df." ");

?>
<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<tr>
<td>
  <table cellspacing="1" cellpadding="2" border="0" width="790">
    <tr>
    <td class="right" nowrap="nowrap" ><b><?php echo $AppUI->_('First Name')?>:&nbsp;</b></td>
    <td class="hilite" >&nbsp;<?= $firstname?></td>
    <td class="right" rowspan="7" colspan="2" valign="top"><CENTER>
      <? if($logo!="ninguna" and $logo!=""){?>
      <img border="1" height="140" src="<?=$uploads_dir."/".$id ?>/<?=$logo ?>"> 
      <? }else echo "<br><br><br><b>" .$AppUI->_('Photo not available')."</b>"; ?>
      </center>
    </td>
    </tr>  
    <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Last Name')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?= $lastname ?></td>
    </tr>
    <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Phone')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?= $phone ?></td>    
  	</tr>
    <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Email')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<a href="mailto:<?= $email ?>"><?= $email ?></a></td>
    </tr>  
    <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Address')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?= $address ?></td>
    </tr>  
    <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('City')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?= $city ?></td>
    </tr>  
    <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('ZIP')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?= $zip ?></td>
    </tr>  
    <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Country')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?= $country ?></td>
    
    <td rowspan="13" colspan="2" width="900">
    	
    	
    	
<!-- Tabla para candidatos -->
<div  <? if($user_type==5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?> >

<table cellspacing="1" cellpadding="2" border="0" align="right" width="100%">
<tr>
<td class="right" nowrap="nowrap" width="208"><b><?php echo $AppUI->_('Actual Company')?>:&nbsp;</b></td>
<td class="hilite" width="600" >&nbsp;<?= $actualcompany ?></td>
</tr>
<tr>
	<? $WorkTypes = dPgetSysVal("WorkType");
	$actualjob = $AppUI->_($WorkTypes[$actualjob]);?>
<td class="right" nowrap="nowrap"  ><b><?php echo $AppUI->_('Actual job')?>:&nbsp;</b></td>
<td class="hilite"  width="600">&nbsp;<?=$actualjob?></td>   
</tr>  
 <tr>
<td class="right" nowrap="nowrap" ><b><?php echo $AppUI->_('Actual working hours')?>:&nbsp;</b></td>
<td class="hilite">&nbsp;<?= $workinghours ?></td>
</tr>
<tr>    
<td class="right" nowrap="nowrap"><b><?=$AppUI->_('Daily avalability')?>:&nbsp;</b></td>
<td class="hilite">&nbsp;<?=$hoursavailableperday?></td>     
</tr>
   
<tr>
<td class="right" nowrap="nowrap"><b><?=$AppUI->_('Cost per hour wanted')?>&nbsp;($):&nbsp;</b></td>
<td class="hilite">&nbsp;<?=$costperhour?></td>
</tr>
<tr>
<td class="right" nowrap="nowrap"><b><?=$AppUI->_('Salary wanted')?>&nbsp;($):&nbsp;</b></td>
<td class="hilite">&nbsp;<?=$salarywanted?></td>    
</tr>     	
<tr>
<td class="right" rowspan="3" nowrap="nowrap" valign="top"><b><?php echo $AppUI->_('Work preference')?>:&nbsp;</b></td>
<td class="hilite" rowspan="3" width="600">
	<table border="0" cellpadding="0" cellspacing="1">
	<tr>
	<td><img border="0" height="16" width="16" src="images/icons/<?=($wantsfulltime ? "stock_ok-16.png" : "stock_cancel-16.png")?>" /></td>
	<td><?=$AppUI->_('Wants full time')?></td>
	</tr>
	<tr>
	<td><img border="0" height="16" width="16" src="images/icons/<?=($wantsparttime ? "stock_ok-16.png" : "stock_cancel-16.png")?>" /></td>
	<td><?=$AppUI->_('Wants part time')?></td>
	</tr>    	
	<tr>
	<td><img border="0" height="16" width="16" src="images/icons/<?=($wantsfreelance ? "stock_ok-16.png" : "stock_cancel-16.png")?>" /></td>
	<td><?=$AppUI->_('Wants freelance')?></td>
	</tr>
	</table>
</td>
</tr>
<tr></tr><tr></tr>
<tr>
<td class="right" nowrap="nowrap"><b><?=$AppUI->_('Was interviewed')?>:&nbsp;</b></td>
<td class="hilite">&nbsp;<img border="0" height="16" width="16" src="images/icons/<?=($wasinterviewed ? "stock_ok-16.png" : "stock_cancel-16.png")?>" /></td>    
</tr>
<?php
	if ( $canEditModule ){?>
<tr>
<td class="right" nowrap="nowrap" valign="top"><b><?=$AppUI->_('Candidate Status')?>:&nbsp;</b></td>
<td class="hilite">&nbsp;<?=$AppUI->_($SCandidateStatus[$candidatestatus])?></td>
<td colspan="2"></td>
</tr>
<?php
	}?>
</table>
</div>
<!-- Fin de tablas para candidatos -->        	
  <!-- Tabla para empleados -->
  <div  <? if($user_type!=5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?> >

    <table cellspacing="1" cellpadding="2" border="0" align="right" width="100%">
			<tr>
				<td class="right" nowrap="nowrap"  width="208"><b><?php echo $AppUI->_('Company')?>:&nbsp;</b></td>
				<td class="hilite" width="220">&nbsp;<?= $company_name ?></td>
			</tr>
			<tr>
				<td class="right" nowrap="nowrap" ><b><?php echo $AppUI->_('Department')?>:&nbsp;</b></td>
				<td class="hilite"  >&nbsp;<?=$dept_name; ?></td>
			</tr>			
			<tr>
				<td class="right" nowrap="nowrap" ><b><?php echo $AppUI->_('Direct report')?>:&nbsp;</b></td>
				<?
					$sql = "SELECT concat(user_first_name,' ', user_last_name) 
									FROM users
									WHERE user_id = $user_supervisor";
				?>
				<td class="hilite" width="200">&nbsp;<?=db_loadResult($sql)?></td>
			</tr>
			<tr>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Position')?>:&nbsp;</b></td>
				<td class="hilite" >&nbsp;<?=$position?></td>   
			</tr>			
			<tr>
				<td class="right" nowrap="nowrap" ><b><?php echo $AppUI->_('Legajo')?>:&nbsp;</b></td>
				<td class="hilite" width="220">&nbsp;<?=$legajo?></td>
			</tr>			

			<tr>
				<td class="right" nowrap="nowrap"><b><?=$AppUI->_('Current Salary')?>&nbsp;($):&nbsp;</b></td>
				           <?
							 $sql_s = db_exec("select hhrr_comp_remuneration from hhrr_comp where hhrr_comp_user_id = '$id' ");
							 $data = mysql_fetch_array($sql_s);
		
							 $salarycurrent = $data[0];
							 
						   ?>
				<td class="hilite">&nbsp;<?=$salarycurrent?></td>      
			</tr>
			<tr>
				<td class="right" nowrap="nowrap" ><b><?php echo $AppUI->_('Hired')?>:&nbsp;</b></td>
				<td class="hilite" width="220">&nbsp;<?=substr($user_input_date_company,8,2)?>/<?=substr($user_input_date_company,5,2)?>/<?=substr($user_input_date_company,0,4)?></td>
			</tr>					
			<tr>
				<td class="right" rowspan="2" nowrap="nowrap" valign="top"><b><?php echo $AppUI->_('Work schedule')?>:&nbsp;</b></td>
				<td class="hilite" rowspan="2" >
					<table border="0" cellpadding="0" cellspacing="1" width="100">
					<tr>
						<td>
							<table border="0" cellpadding="0" cellspacing="1" width="100">
								<tr>
		            	<td>
							    <?
									if ($start_time_am !=""){
										if ($start_time_am->format("%H%M") !="" && $start_time_am->format("%H%M") >"0")
											echo "&nbsp;".$start_time_am->format("%H:%M"); 
							    }
									if ($end_time_am !=""){
										if ($end_time_am->format("%H%M") !="" && $end_time_am->format("%H%M") >"0")
											echo "&nbsp;&nbsp;".$end_time_am->format("%H:%M"); 
									}?>
						  		</td>
						  	</tr>
					 	 		<tr>
					  			<td>
								   <?
								    if ($start_time_pm !=""){
											if ($start_time_pm->format("%H%M") !=""  && $start_time_pm->format("%H%M") >"0")
												echo "&nbsp;".$start_time_pm->format("%H:%M"); 
								   }
								   if ($end_time_pm !=""){
											if ($end_time_pm->format("%H%M") !=""  && $end_time_pm->format("%H%M") >"0")
												echo "&nbsp;&nbsp;".$end_time_pm->format("%H:%M"); 
								   }?>
					  			</td>
								</tr>
							</table>
						</td>
					</tr>			
			    </table>
		  	</td>
	  	</tr>
	  	<tr></tr>
			<tr>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Daily working hours')?>:&nbsp;</b></td>
				<td class="hilite" width="220">&nbsp;<?= $daily_working_hours ?></td>		
			</td>
	  </table>
 </div>
  <!-- Fin de la tabla para empleados -->   	
    	
    	
   	</td>
   	
    </tr>  
    <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('State')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?= $state ?></td>
    </tr>  
    <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Nationality')?>:&nbsp;</b></td>
    
     <?
					
		//$desc = "description_".$AppUI->user_prefs['LOCALE'];
		$desc = "description_es";
					
		$query = "SELECT $desc  FROM location_nationalities WHERE nationality_id='$nationality' ORDER BY $desc ASC"; 
		$sql = db_exec($query);
		$nationality_desc = mysql_fetch_array($sql);
					
     ?>
    
    <td class="hilite">&nbsp;<?= $nationality_desc[$desc] ?></td>
		</tr>		
		<tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Birthday')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?=substr($birthday,8,2)?>/<?=substr($birthday,5,2)?>/<?=substr($birthday,0,4)?>
    </td>
  	</tr>

    <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Home Phone')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?= $homephone ?></td>    
    </tr>  
    <tr>
    <td class="right" nowrap="nowrap" width="28%"><b><?php echo $AppUI->_('Cell Phone')?>:&nbsp;</b></td>
    <td class="hilite" width="29%" >&nbsp;<?= $cellphone ?></td>
    </tr>  
    <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('IM')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?= $IMTypes[$im_type].": ".$im_id?></td>
	  </tr>
	  <tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Marital State')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?= $AppUI->_($maritalstates[$maritalstate])?></td>    
    </tr>
    <tr>
    <td class="right"   ><b><?php echo $AppUI->_('Children')?>:&nbsp;</b></td>
    <td class="hilite" width="20%" >&nbsp;<?= $children ?></td>    
  	</tr>
		<tr>
    <td class="right" nowrap="nowrap">&nbsp;<b><?php echo $AppUI->_('ID')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?= $doctype ?> <?= $docnumber ?></td>  	
  	</tr>
    <tr>    
    <td class="right" nowrap="nowrap" valign="top"><b><?php echo $AppUI->_('Tax ID number')?>:&nbsp;</b></td>
    <td class="hilite" valign="top">&nbsp;<?= $taxidnumber ?></td>
    </tr>
		<tr>
    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Tax Type')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?= $taxidtype ?></td>    
    </tr>    
    <tr>
    <td class="right" nowrap="nowrap" valign="top"><b><?php echo $AppUI->_('CV')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;
    <?php
      if($resume<>"ninguna") { 
        echo "&nbsp;<b><a class='link1'  href='$uploads_dir/$id/".str_replace(" ","%20",$resume)."'>$resume</a></b>";
      }
    ?>
    </td>
  	</tr>

    <tr>
    	<td class="right" nowrap="nowrap" colspan="4"> &nbsp;</td>
    </tr>

<!-- Tabla de Comentarios !-->
		<?
		$sql="SELECT hhrr_note_id,hhrr_user_id,hhrr_note, DATE_FORMAT(hhrr_note_date, '%d/%m/%Y %H:%i:%s') AS hhrr_note_date,hhrr_note_owner, CONCAT(user_last_name,', ',user_first_name) AS full_name FROM hhrr_notes LEFT JOIN users ON hhrr_notes.hhrr_note_owner=users.user_id WHERE hhrr_user_id = $id AND hhrr_note_type = ".NOTE_INTERNAL." ORDER BY hhrr_note_date DESC, hhrr_note_id DESC;";
		$hhrr_notes=db_loadList($sql);
		if ( count($hhrr_notes) >0 ) // si hay algun comentario muestro la tabla sino no
		{?>

    <tr>    
    <td class="right" valign="top"><b><?=$AppUI->_('Comments')?>:&nbsp;</b></td>
     	<td colspan="3">
				<table cellspacing="1" cellpadding="2" border="0" align="right" width="100%">
				<?
				foreach($hhrr_notes as $note)
				{
				?>
				
				<tr class="bugnote">
					<td class="form-title" width="20%">
						<?=$note['full_name']?><br>
						<span class="small"><?=$note['hhrr_note_date']?></span><br><br>
					</td>
					
					<td class="form-title" width="80%">
						<span class="small"><?=nl2br($note['hhrr_note'])?></span>
					</td>
				</tr>
				<tr>
					<td class="spacer" colspan="2">&nbsp;</td>
				</tr>
				<?							
				}?>
				</table>
	  	</td>

			</td>
		</tr>
	 <?}?>		
<!-- FIN Tabla de Comentarios !-->

<!-- Tabla de Comentarios de la Entrevista!-->
		<?
						$sql="SELECT hhrr_note_id,hhrr_user_id,hhrr_note, DATE_FORMAT(hhrr_note_date, '%d/%m/%Y %H:%i:%s') AS hhrr_note_date,hhrr_note_owner, CONCAT(user_last_name,', ',user_first_name) AS full_name FROM hhrr_notes LEFT JOIN users ON hhrr_notes.hhrr_note_owner=users.user_id WHERE hhrr_user_id = $id AND hhrr_note_type = ".NOTE_INTERVIEW." ORDER BY hhrr_note_date DESC, hhrr_note_id DESC;";
		$hhrr_notes=db_loadList($sql);
		if ( count($hhrr_notes) >0 ) // si hay algun comentario muestro la tabla sino no
		{?>
		<tr>
			<td class="right" valign="top"><b><?= ($user_type==5 ) ? $AppUI->_('Interview comments'): "&nbsp;"?>:&nbsp;</b></td>
				<td colspan="3">
				<div  <? if($user_type==5 ){ ?> style="display:" <?}else{?> style="display: none;" <?} ?> >
					<table cellspacing="1" cellpadding="2" border="0" align="right" width="100%">
						<?
						foreach($hhrr_notes as $note)
						{
						?>
						
						<tr class="bugnote">
							<td class="form-title"  width="20%">
								<?=$note['full_name']?><br>
								<span class="small"><?=$note['hhrr_note_date'];?></span><br><br>
							</td>
							
							<td class="form-title"  width="80%">
								<span class="small"><?=nl2br($note['hhrr_note']);?></span>
							</td>
						</tr>
						<tr>
							<td class="spacer" colspan="2">&nbsp;</td>
						</tr>
						<?							
						}?>
				</table>
	  	</td>

			</td>
		</tr>
	 <?}?>	
		<!-- FIN Tabla de Comentarios  de la Entrevista!-->
			
    <tr>
    	<td class="right" nowrap="nowrap" colspan="4"> &nbsp;</td>
    </tr>

    <tr>
    <td class="right" nowrap="nowrap" colspan="3"><b><?php echo $AppUI->_('Input Date')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?=$inputdate?></td>
    </tr>
    <tr>
    <td class="right" nowrap="nowrap" colspan="3"><b><?php echo $AppUI->_('Update Date')?>:&nbsp;</b></td>
    <td class="hilite">&nbsp;<?=$updateddate ?></td>
    </tr>
	<tr>
	<td colspan="4" align="right">
		
		<table  align="right" border="0">
			<tr>
				<td>
				<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
				</td>
				<? if (validar_permisos_hhrr($id,'personal',-1))
				{?>
					<td align="center">
          	<?
						if($_GET[a]!="personalinfo")
							$edit_hrf = "index.php?m=hhrr&a=addedit&tab=1&id=".$id;
						else
							$edit_hrf = "index.php?a=personalinfo&id=".$id;
						?>
					<input type="button" value="<?php echo $AppUI->_( 'edit' );?>" class="button" onClick="javascript:window.location='<?=$edit_hrf;?>';" />
				</td>
				<? } ?>
				<td align="right">
				<?
				$edit_next = "index.php?m=hhrr&a=viewhhrr&tab=2&id=".$id;
				?>
				<!--<input type="button" value="<?php echo $AppUI->_( 'next' );?>" class="button" onClick="javascript:window.location='<?=$edit_next;?>';" />-->
				</td>
			</tr>
		</table>

	</td>
	</tr>    
  </table>
</td>
</tr>
</table>

