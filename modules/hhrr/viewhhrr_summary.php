<?php /* HHRR $Id: viewhhrr_summary.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */

global $AppUI, $canEdit, $xajax;

define("NOTE_INTERVIEW", 1); 
define("NOTE_INTERNAL", 2);

$id = isset($_GET['id']) ? $_GET['id'] : 0;

/*
Si esta variable es verdadera significa que el formulario se cargo para imprimirse, 
por lo que tengo que ocultar los botones y el boton de imprimir.
*/
$print = isset($_GET['print']) ? $_GET['print'] : 0;

$maritalstates= dPgetSysVal("MaritalState");
$IMTypes= dPgetSysVal("IMType");
$SCandidateStatus = dPgetSysVal("CandidateStatus");

$uploads_dir = $AppUI->getConfig('hhrr_uploads_dir');


$result = mysql_query("SELECT users.*, departments.dept_name, companies.company_name   FROM users
LEFT JOIN companies ON users.user_company = companies.company_id
LEFT JOIN departments ON users.user_department = departments.dept_id
WHERE user_id = $id;");
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      $id = $row["user_id"];
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
      $company_name =  $row["company_name"];
      $dept_name =  $row["dept_name"];
      $user_input_date_company = $row["user_input_date_company"];
      $salarywanted = $row["salarywanted"];
      
      $candidato = ($row['user_type'] == 5);
 
 
		$start_time_am = $row["start_time_am"] ? new CDate( "0000-00-00 ".$row["start_time_am"] ) : null;
		$end_time_am = $row["end_time_am"]  ? new CDate( "0000-00-00 ".$row["end_time_am"] ) : null;
		$start_time_pm = $row["start_time_pm"] ? new CDate( "0000-00-00 ".$row["start_time_pm"] ) : null;
		$end_time_pm = $row["end_time_pm"] ? new CDate( "0000-00-00 ".$row["end_time_pm"] ) : null;		
     
      //Traigo el nombre del user_supervisor
			$sql = "SELECT concat(user_first_name,' ', user_last_name) AS user_supervisor  FROM users WHERE user_id = ".$row["user_supervisor"];
			$user_supervisor = db_loadResult($sql);
			
			if($candidato)
			{
				$WorkTypes = dPgetSysVal("WorkType");
				$actualjob = $AppUI->_($WorkTypes[$actualjob]);
			}

      
//Si es para imprimir cargo la barra con el link para imprimir
if ($print)
{
	$ttl = $firstname." ".$lastname;

	$titleBlock = new CTitleBlock( $ttl, 'hhrr.gif', $m, 'hhrr.index' );
	$titleBlock->addCell( "[ <a href=\"javascript:window.print();\" style=\"text-decoration:none\"> ".strtolower($AppUI->_('Print'))."</a> | <a href=\"#\" onclick=\"xajax_generar_pdf($id)\" style=\"text-decoration:none\"> ".strtolower($AppUI->_('export to pdf'))."</a> ]", 'width="1%"','','');
	$titleBlock->show();
}
?>
<table border="0" cellpadding="4" cellspacing="0" width="100%" >
	<tr>
		<td align="right">
	  	<? if (!$print)
	  	{?>
	  	<input type="button" class="button" value="<?=$AppUI->_("print")?>" onclick="javascript:window.open('./index.php?m=hhrr&id=<?=$id;?>&a=viewhhrr_summary&print=1&dialog=1&suppressLogo=1', '_blank', 'top=0,left=0,width=810, height=700, scrollbars=yes, status=no' );" />
	  	&nbsp;&nbsp;&nbsp;&nbsp;
	  	<input type="button" class="button" value="<?=$AppUI->_("export to pdf")?>" onclick="xajax_generar_pdf(<?=$id?>)" />
	  	<?}?>
		</td>	
	</tr>
	<tr>
   
<?
if($id == '')$id = '0';

//Valido primero que tenga permisos de lectura sobre la seccion
//Si el formulario se carga para imprimir, y esta oculta la seccion, no muestro absolutamente nada de la misma
if (validar_permisos_hhrr($id,'personal',1) && $id !='0' AND !($print AND $_SESSION['vec_sections']['personal']) )
{?>
	<!-- Tabla de la solapa de datos personales --> 
	<td colspan="2"  align="center">
		<table cellspacing="1" cellpadding="0" border="0" width="100%"  style="border: 1px solid #000000;">
		 <tr>
		   <td align="center">
       	<table width="98%" border="0" cellpadding="2" cellspacing="0" align="center" >
				 <tr class="tableHeaderGral">
				   <td align="center" > 
					  <?=$AppUI->_("Personal data")?>
				   </td>
				   <td align="right" width='1%'> 
					 	<? if(!$print)//Si el formulario se carga para imprimir, NO muestro el boton!
						 	{?>
						 		<img onClick="xajax_hideSection('personal')" id="img_personal" name="img_personal" src='./images/icons/collapse.gif' width='16' height='16' border='0' alt='<?php echo $AppUI->_('Hide');?>'>
						<?}?>
				   </td>				   
				 </tr>
				 <tr class="tableRowLineCell">
				 	<td colspan='2' height='5'>
				 	</td>
				 </tr>
       </table>
		  </td>
		  </tr>
		  
		  <tr>
		   <td align="center">
			  <table id='table_personal' cellspacing="1" cellpadding="0" border="0" width="98%">
			   <tr>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('First Name')?>:&nbsp;</b></td>
				<td class="hilite" align="left">&nbsp;<?= $firstname?></td>
				<td class="right" rowspan="8" colspan="2" valign="top"><CENTER>
				  <? if($logo!="ninguna" and $logo!=""){?>
				  <img border="1" height="140" src="<?=$uploads_dir."/".$id?>/<?=$logo?>"> 
				  <?}
				  else echo "<br><br><br><b>". $AppUI->_('Photo not available')."</b>";?>
				  </center>
				</td>
			   </tr>  
			   <tr>
			    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Last Name')?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?= $lastname ?></td> 
			   </tr>
			   	<!--
			   <tr>
					<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Phone')?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?= $phone ?></td>
			   </tr>
			   <tr>
					<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Email')?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<a href="mailto:<?= $email ?>"><?= $email ?></a></td>
			   </tr>
					!-->			   
			   <tr>
			<? if ($candidato){?>
					<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Actual Company')?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?= $actualcompany ?></td>
				<? }
				else{?>
					<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Company')?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?= $company_name ?></td>
				<?}?>
				 </tr>
				 
				 <?
			   //Si no es un candidato muestro el depto
			   if (!$candidato){?>
			   <tr>
					<td class="right" nowrap="nowrap"><b><?=$AppUI->_('Department')?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?=$dept_name ?></td>
			   </tr>
				<?}?>				 

			   </tr>
			   
			   <?
			   //Si no es un candidato muestro el user_supervisor
			   if (!$candidato){?>
			  <tr>
					<td class="right" nowrap="nowrap"><b><?=$AppUI->_('Direct report')?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?=$user_supervisor ?></td>
			  </tr>
			  <tr>
			  	<td class="right"><b><?php echo $AppUI->_('Position');?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?= $row[user_job_title];?></td>
				</tr>
			  <tr>
			  	<td class="right"><b><?php echo $AppUI->_('Length of service');?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?=calcular_edad($user_input_date_company);?></td>
				</tr>
				<?}else{?>
			  <tr>
					<td rowspan="1" class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Actual job')?>:&nbsp;</b></td>
					<td rowspan="1" class="hilite" align="left">&nbsp;<?=$actualjob?></td>

			  </tr>
			  <tr>
					<td rowspan="1" class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Salary wanted')?>:&nbsp;</b></td>
					<td rowspan="1" class="hilite" align="left">&nbsp;<?= $salarywanted ?></td>  
				</tr>
			  <tr>
					<td rowspan="1" class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Actual working hours')?>:&nbsp;</b></td>
					<td rowspan="1" class="hilite" align="left">&nbsp;<?= $workinghours ?></td>  
				</tr>					
				
				<?}?>
			   <tr>
				  <td class="right" nowrap="nowrap">&nbsp;<b><?php echo $AppUI->_('Title')?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?= user_title( $id ) ?></td>
				 </tr>				
			  <!--			  
			   <tr>

			  <td class="right" nowrap="nowrap">&nbsp;<b><?php echo $AppUI->_('ID')?>:&nbsp;</b></td>
				<td class="hilite" align="left">&nbsp;<?= $doctype ?> <?= $docnumber ?></td>
				 </tr>
			   <tr>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Address')?>:&nbsp;</b></td>
				<td class="hilite" align="left">&nbsp;<?= $address ?></td>
			   </tr>  
			   <tr>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('City')?>:&nbsp;</b></td>
				<td class="hilite" align="left">&nbsp;<?= $city ?></td>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('ZIP')?>:&nbsp;</b></td>
				<td class="hilite" align="left">&nbsp;<?= $zip ?></td>
			   </tr>
			   <tr>
			  <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Home Phone')?>:&nbsp;</b></td>
				<td class="hilite" align="left">&nbsp;<?= $homephone ?></td>
				
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Cell Phone')?>:&nbsp;</b></td>
				<td class="hilite" align="left">&nbsp;<?= $cellphone ?></td>
			   </tr>  
			  !-->		
			   <tr>
					<td class="right" nowrap="nowrap" ><b><?php echo $AppUI->_('Age')?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?=calcular_edad($birthday);?>
				</tr>
				<tr>	
					<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Children')?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?= $children ?></td>
					<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Marital State')?>:&nbsp;</b></td>
					<td class="hilite" nowrap="nowrap" align="left">&nbsp;<?= $AppUI->_($maritalstates[$maritalstate])?></td>						
			   </tr>  
			  <!--
			   <tr>
					<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('IM')?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?=$IMTypes[$im_type].": ".$im_id?></td>
			   </tr>
				!-->			   
				 <tr>
				<? //Si en un candidato muestro el Actual job, sino Job Title
				if ($candidato){?>
				<!--	
					<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Actual job')?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?=$actualjob?></td>
					<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Actual working hours')?>:&nbsp;</b></td>
					<td class="hilite" align="left">&nbsp;<?= $workinghours ?></td>  				
					!-->
				<?}
				else{?>
				<!--
				<td class="right"><b><?php echo $AppUI->_('Work schedule');?>:&nbsp;</b></td>
				<td class="hilite" align="left">
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
			  !-->				
				<?}?>

			   </tr>
		   
			   <? if (!$print)
			   {?>
				 <tr>
				  <td colspan="4" align="right"  bgcolor="#ffffff">
				  	<br>
				   <? $see_more = "index.php?m=hhrr&a=viewhhrr&tab=1&id=".$id; ?>
					 <a href="<?= $see_more; ?>"> <?=$AppUI->_( 'See more' );?></a>
						<?
						if (validar_permisos_hhrr($id,'personal',-1))
						{
							echo "&nbsp;&nbsp";
							$edit_hrf = "index.php?m=hhrr&a=addedit&tab=1&id=".$id;
						?>
					 		<a href="<?= $edit_hrf; ?>"> <?=$AppUI->_( 'edit' );?></a>
					 <?}?>
				  </td>
				 </tr>
				 <?}?>
			  </table>
		   </td>
		 </tr>
		</table>
	</td>
</tr>
<!-- Fin de la Tabla de la solapa de datos personales -->
<? 
} 
//Si es necesario ocultamos la seccion
	if($_SESSION['vec_sections']['personal'])
	{
		?>
		<script type="text/javascript">
			xajax_hideSection('personal');
		</script>
		<?
	}
?>



<!-- Tabla de Compensaciones  -->
<?
//Valido primero que tenga permisos de lectura sobre la seccion
//Si el formulario se carga para imprimir, y esta oculta la seccion, no muestro absolutamente nada de la misma
if (validar_permisos_hhrr($id,'compensations',1) && $id !='0' AND !($print AND $_SESSION['vec_sections']['compensations']) )
{?>
<tr>
  <td colspan="2"  align="center"> 
   <br>
    <table cellspacing="1" cellpadding="0" border="0" width="100%"  style="border: 1px solid #000000;">
    	<tr>
	  		<td align="center">
        	<table width="98%" border="0" cellpadding="2" cellspacing="0" align="center" >
						<tr class="tableHeaderGral">
							<th align="center"><?=$AppUI->_("compensations")?></th>

					   <td align="right" width='1%'> 
						 	<? if(!$print)//Si el formulario se carga para imprimir, NO muestro el boton!
							 	{?>
							 		<img onClick="xajax_hideSection('compensations')" id="img_compensations" name="img_compensations" src='./images/icons/collapse.gif' width='16' height='16' border='0' alt='<?php echo $AppUI->_('Hide');?>'>
							<?}?>
					   </td>
						</tr>
						
					 <tr class="tableRowLineCell">
					 	<td colspan='2' height='5'>
					 	</td>
					 </tr>
					 
						<tr>
							<td colspan="2">
								<? include_once('viewhhrr_summary_comp.php'); ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
  
  </td>
</tr>
<!-- Fin Tabla de Compensaciones  -->
<?
}
//Si es necesario ocultamos la seccion
	if($_SESSION['vec_sections']['compensations'])
	{
		?>
		<script type="text/javascript">
			xajax_hideSection('compensations');
		</script>
		<?
	}
?>

<!-- Tabla de antecedentes laborales  -->
<?
//Valido primero que tenga permisos de lectura sobre la seccion
//Si el formulario se carga para imprimir, y esta oculta la seccion, no muestro absolutamente nada de la misma
if (validar_permisos_hhrr($id,'work_experience',1) AND !($print AND $_SESSION['vec_sections']['work_experience']) )
{?>
<tr>
   <td colspan="2"  align="center"> 
   <br>
    <table cellspacing="1" cellpadding="0" border="0" width="100%"  style="border: 1px solid #000000;">
    	<tr>
	  		<td align="center">
        	<table width="98%" border="0" cellpadding="2" cellspacing="0" align="center" >
						<tr class="tableHeaderGral">
							<th align="center"><?=$AppUI->_("Work Experience")?></th>
						   <td align="right" width='1%'> 
							 	<? if(!$print)//Si el formulario se carga para imprimir, NO muestro el boton!
								 	{?>
								 		<img onClick="xajax_hideSection('work_experience')" id="img_work_experience" name="img_work_experience" src='./images/icons/collapse.gif' width='16' height='16' border='0' alt='<?php echo $AppUI->_('Hide');?>'>
								<?}?>
						   </td>
						</tr>
					 <tr class="tableRowLineCell">
					 	<td colspan='2' height='5'>
					 	</td>
					 </tr>
						<tr>
							<td colspan="5">
								<? include_once('viewhhrr_summary_ant.php'); ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
  
  </td>
</tr>
<!-- Fin Tabla de antecedentes laborales  -->
<?
}
//Si es necesario ocultamos la seccion
if($_SESSION['vec_sections']['work_experience'])
{
	?>
	<script type="text/javascript">
		xajax_hideSection('work_experience');
	</script>
	<?
}


//Valido primero que tenga permisos de lectura sobre la seccion
//Si el formulario se carga para imprimir, y esta oculta la seccion, no muestro absolutamente nada de la misma
if (validar_permisos_hhrr($id,'education',1) AND !($print AND $_SESSION['vec_sections']['education']) )
{?>
<!-- Tabla de Formaci?n profecional  -->
  <tr>
    <td colspan="2"  align="center">
	    <br>
		<table cellspacing="1" cellpadding="0" border="0" width="100%"  style="border: 1px solid #000000;">
		 <tr>
		   <td align="center" valign="top">

        <table cellspacing="0" cellpadding="2" border="0" width="98%">
				 <tr class="tableHeaderGral">
				   <td align="center" ><?=$AppUI->_("Education")?></td>
				   <td align="right" width='1%'> 
					 	<? if(!$print)//Si el formulario se carga para imprimir, NO muestro el boton!
						 	{?>
						 		<img onClick="xajax_hideSection('education')" id="img_education" name="img_education" src='./images/icons/collapse.gif' width='16' height='16' border='0' alt='<?php echo $AppUI->_('Hide');?>'>
						<?}?>
				   </td>				   
				 </tr>
						
				 <tr class="tableRowLineCell">
				 	<td colspan='2' height='5'>
				 	</td>
				 </tr>
				 
       </table>
		  </td>
		 </tr>
     <tr>
		 	<td align="center">
		 		<table id='table_education' cellspacing="1" cellpadding="1" border="0" width="100%">
		 			<tr>
		 				<td>
					 		<br>
			           
			        <table cellspacing="1" cellpadding="1" border="0" width="98%" class="tableForm_bg" align="center">
							 <tr class="tableHeaderGral" >
							  <th align="center" colspan="5"><?=$AppUI->_("Formal Education")?></th>
							 </tr>
							 <tr class="tableHeaderGral">
								 <th align="left"><?=$AppUI->_("Academic level")?></th>
								 <th align="left"><?=$AppUI->_("Title")?></th>
								 <th align="left"><?=$AppUI->_("Institution")?></th>
								 <th align="left"><?=$AppUI->_("Status")?></th>
								 <th align="left"><?=$AppUI->_("Completed")?></th>
						   </tr>
						   <? 			
						    $sql = "SELECT id, id_user, level, title, instit, status, end_date FROM hhrr_education WHERE id_user ='$id' AND type='0' order by level desc LIMIT 3";
						    
							$rc = db_exec($sql);
			
							  while ($vec = db_fetch_array($rc)){
						   ?>
							   <!-- Lista de educaci?n formal -->
							   <tr>
								 <td  bgcolor="#ffffff" >
								 	<?
									if ($AppUI->user_locale == 'es')
					      		$name = 'name_es';
					      	else
					      		$name = 'name_en';
					  
									 @$level = db_loadResult("SELECT $name FROM hhrr_academic_level WHERE id ='$vec[level]' ");
									 echo $level;
									 ?>
								 </td>
									 <td  bgcolor="#ffffff" >
									    <!--<?=$vec['title']?>-->
									    <?
					
											$desc = "name_es";
														
											$query_title = "SELECT $desc  FROM hhrr_education_title WHERE title_id='$vec[title]' "; 
											$sql_title = db_exec($query_title);
											$title_desc = mysql_fetch_array($sql_title);
														
									    ?>
									    <?=$title_desc[0]?>
									 </td>
									 <td  bgcolor="#ffffff" >
									  <!--<?=$vec['instit']?>-->
									  <?
					
										$query_instit = "SELECT name FROM hhrr_education_institution WHERE instit_id='$vec[instit]' "; 
										$sql_instit = db_exec($query_instit);
										$instit_desc = mysql_fetch_array($sql_instit);
													
								     ?>
								     <?=$instit_desc[name]?> 
									  
									 </td>
									 <td  bgcolor="#ffffff" >
									 <?    
										   switch($vec['status']){
											 case "0":
												 echo $AppUI->_("Incomplete");
											 break;
											 case "1":
												 echo $AppUI->_("Completed");
											 break;
											 case "2":
												 echo $AppUI->_("On Course");
											 break;
										   }
									 ?>
									 </td>
									 <td  bgcolor="#ffffff"  width='70'><?=$vec['end_date']?></td>
							   </tr>
						   <? }

								  if(db_num_rows($rc)==0)
								  {
								   echo "<tr>
								   <td colspan=\"3\" align=\"center\">".$AppUI->_('Noitems')."<td>
								   </tr>";
								  }?>
							</table>
			                
							<br><br>
			
							<table cellspacing="1" cellpadding="1" border="0" width="98%" class="tableForm_bg" align="center">

				 <tr class="tableHeaderGral">
				  <th align="center" colspan="5"><?=$AppUI->_("Training")?></th>
				 </tr>
				 <tr class="tableHeaderGral">
					 <th align="left"><?=$AppUI->_("Type")?></th>
					 <th align="left"><?=$AppUI->_("Program")?></th>
					 <th align="left"><?=$AppUI->_("Activity")?></th>
					 <th align="left"><?=$AppUI->_("Institution")?></th>
					 <th align="left"><?=$AppUI->_("Date")?></th>
			   </tr>
			   <? 
			    $query = "SELECT id, id_user, seminary_type, seminary, title, instit, DATE_FORMAT(s_date,'%d-%m-%Y') as sdate FROM hhrr_education WHERE id_user ='$id' AND type='1' LIMIT 3";
				  $sql = mysql_query($query);

				  while ($vec = mysql_fetch_array($sql)){
			   ?>
					   <tr>
							 <td  bgcolor="#ffffff">
							 <?    
								   switch($vec['seminary_type'])
								   {
								     case "0":
											 echo $AppUI->_("Local");
										 break;
										 case "1":
											 echo $AppUI->_("In-Company");
										 break;
										 case "2":
											 echo $AppUI->_("Exterior");
										 break;
							    }?>
							 </td>					   	
							 <td  bgcolor="#ffffff" >
							   <!--<?=$vec['seminary']?>-->
							   <?
					
								$query_program = "SELECT name FROM hhrr_education_program WHERE program_id='$vec[seminary]' "; 
								$sql_program = db_exec($query_program);
								$program_desc = mysql_fetch_array($sql_program);
													
								?>
								
								<?=$program_desc[name]?>
							 </td>
							 <td  bgcolor="#ffffff" ><?=$vec['title']?></td>
							 <td  bgcolor="#ffffff" >
							    <!--<?=$vec['instit']?>-->
							    <?
					
								$query_institut = "SELECT name FROM hhrr_education_institution WHERE instit_id='$vec[instit]' "; 
								$sql_institut = db_exec($query_institut);
								$institut_desc = mysql_fetch_array($sql_institut);
													
								?>
								
								<?=$institut_desc[name]?>
							    
							 </td>
							 <td  bgcolor="#ffffff" ><?=$vec['sdate']?></td>
					   </tr>
			   <? } 
					  
					  if(mysql_num_rows($sql) =="0")
					  {
					   echo "<tr>
					   <td colspan=\"4\" align=\"center\">".$AppUI->_('Noitems')."<td>
					   </tr>";
					  }
				 
				if (!$print)
				{?>
					
				 <tr>
				  <td colspan="5" align="right"  bgcolor="#ffffff">
				  	<br>
				  	<? $see_more = "index.php?m=hhrr&a=viewhhrr&tab=3&id=".$id; ?>
					 <a href="<?= $see_more; ?>"> <?=$AppUI->_( 'See more' );?></a>
						<?
						if (validar_permisos_hhrr($id,'education',-1))
						{
							echo "&nbsp;&nbsp";
							$edit_hrf = "index.php?m=hhrr&a=addedit&tab=3&id=".$id;
						?>
					 		<a href="<?= $edit_hrf; ?>"> <?=$AppUI->_( 'edit' );?></a>
					 <?}?>
				  </td>
				 </tr>
				 <?
				 }?>   
				</table>

						</td>
		  		</tr>
				</table>
			</td>
  	</tr>
</table>
<!-- Fin Tabla de Formaci?n profecional  -->
<?
}

//Si es necesario ocultamos la seccion
if($_SESSION['vec_sections']['education'])
{
	?>
	<script type="text/javascript">
		xajax_hideSection('education');
	</script>
	<?
}

//Valido primero que tenga permisos de lectura sobre la seccion
//Si el formulario se carga para imprimir, y esta oculta la seccion, no muestro absolutamente nada de la misma
if (validar_permisos_hhrr($id,'matrix',1) AND !($print AND $_SESSION['vec_sections']['matrix']) ) 
{?>
<!-- Matriz de conocimientos -->
 <tr>
   <td colspan="2" align="center"> 
	   <br>
	  <table cellspacing="1" cellpadding="0" border="0" width="100%"  style="border: 1px solid #000000;">
	  <tr>
			<td align="center" valign="top">
				<table cellspacing="0" cellpadding="2" border="0" width="98%">
		    	<tr class="tableHeaderGral">
					<th align="center"><?=$AppUI->_("Competences")?></th>
				   <td colspan='2' align="right" width='1%'> 
					 	<? if(!$print)//Si el formulario se carga para imprimir, NO muestro el boton!
						 	{?>
						 		<img onClick="xajax_hideSection('matrix')" id="img_matrix" name="img_matrix" src='./images/icons/collapse.gif' width='16' height='16' border='0' alt='<?php echo $AppUI->_('Hide');?>'>
						<?}?>
				   </td>
				</tr>
				 <tr class="tableRowLineCell">
				 	<td colspan='3' height='5'>
				 	</td>
				 </tr>
		
		   </table>
		  </td>
	 </tr>
		<tr>
			<td align="center">
		 		<table id='table_matrix' cellspacing="1" cellpadding="1" border="0" width="100%">
					  <?
					  $sql = "SELECT * 
					  FROM  hhrrskills, skills, skillcategories 
					  WHERE skillcategories.id = skills.idskillcategory 
					  AND idskill = skills.id 
					  AND user_id='$id' 
					  AND VALUE > 1 
					  ORDER BY skillcategories.sort,skillcategories.name, skills.description limit 3";
		
					  $resultskills = mysql_query($sql);
					  $cant = db_num_rows($resultskills);
					  $lastcat="7dgd7gHs8gM9634YaFDdj5";
					  while ($row = mysql_fetch_array($resultskills, MYSQL_ASSOC)) 
					  {
							if($lastcat!=$row["name"]){
							  echo '<tr class="tableHeaderGral"><th colspan="3">&nbsp;&nbsp;'.$row["name"].'</th></tr>';
							  $lastcat=$row["name"];
							}
							
						 	?>
			
						  <tr>
							  <td>
									&nbsp;&nbsp;<?=$row["description"]?>
							  </td>
							  <td>
									&nbsp;&nbsp;<?=$row["valuedesc"]?><?if($row["valuedesc"]!="")echo ":";?>&nbsp;&nbsp; 
									<?
									  $items = split(",",$row["valueoptions"]);
									  echo $items[$row["value"]-1];
									?>
							  </td>
							  <td width="50%">
									&nbsp;<?=$row["comment"]?>
							  </td>
						  </tr>
						  
						  <tr class="tableRowLineCell"><td colspan="3"></td></tr>
					 <? }?>
	
					<tr>
					  <td colspan="2" align="center">
						  <? 		  
						  if($cant =="0")
						  {
						   echo $AppUI->_('Noitems_matriz');
						  }
						  ?>
					  <td>
					  	
					</tr>
					
					<? if (!$print)
					{?>
			     <tr>
					  <td colspan="3" align="right">
					  	 <? $see_more = "index.php?m=hhrr&a=viewhhrr&tab=7&id=".$id; ?>
					     <!--<input type="button" value="<?php echo $AppUI->_( 'See more' );?>" class="button" onClick="javascript:window.location='<?=$see_more;?>';" />!-->
					     <a href="<?= $see_more; ?>"> <?=$AppUI->_( 'See more' );?></a>
					     <?
					     if (validar_permisos_hhrr($id,'matrix',-1))
								{
									$edit_hrf = "index.php?m=hhrr&a=addedit&tab=7&id=".$id; ?>
									&nbsp;&nbsp;
									<!--<input type="button" value="<?php echo $AppUI->_( 'edit' );?>" class="button" onClick="javascript:window.location='<?=$edit_hrf;?>';" />!-->
									<a href="<?= $edit_hrf; ?>"> <?=$AppUI->_( 'edit' );?></a>
								<?
								}?>
					  </td>
					 </tr>
					 <?
					}?>				
					
					
				</table>	
		
				</td>
			</tr>
			</table>
   </td>
 </tr>
<?}?>
<!-- Matriz de conocimientos -->
<?
//Si es necesario ocultamos la seccion
if($_SESSION['vec_sections']['matrix'])
{
	?>
	<script type="text/javascript">
		xajax_hideSection('matrix');
	</script>
	<?
}


//Valido primero que tenga permisos de lectura sobre la seccion
//Si el formulario se carga para imprimir, y esta oculta la seccion, no muestro absolutamente nada de la misma
if ($id != $AppUI->user_id AND validar_permisos_hhrr($id,'performance_management',1)  AND !($print AND $_SESSION['vec_sections']['performance_management']) )
{?>
 <!-- Evaluaci?n y rendimiento -->
<tr>
   <td colspan="2" align="center"> 
   	<br>
    <table cellspacing="1" cellpadding="0" border="0" width="100%"  style="border: 1px solid #000000;">
    <tr>
	  	<td align="center" valign="top">
    	<table cellspacing="0" cellpadding="2" border="0" width="98%">
				<tr class="tableHeaderGral">
					 <th align="center"><?=$AppUI->_("Performance Management")?></th>
				   <th align="right" width='1%'> 
					 	<? if(!$print)//Si el formulario se carga para imprimir, NO muestro el boton!
						 	{?>
						 		<img onClick="xajax_hideSection('performance_management')" id="img_performance_management" name="img_performance_management" src='./images/icons/collapse.gif' width='16' height='16' border='0' alt='<?php echo $AppUI->_('Hide');?>'>
						<?}?>
				   </th>					
				</tr>
				
				 <tr class="tableRowLineCell">
				 	<td colspan='2' height='5'>
				 	</td>
				 </tr>
					
				<tr>
					<td colspan='2' align="center">
						<table id='table_performance_management' cellspacing="1" cellpadding="1" border="0" width="100%">
							 <tr class="tableHeaderGral">
								 <th align="left"><?=$AppUI->_("From")?></th>
								 <th align="left"><?=$AppUI->_("To")?></th>
								 <th align="left" width="200"><?=$AppUI->_("Performance Evaluation")?></th>
								 <th align="left" width="200"><?=$AppUI->_("Potential")?></th>
								 <th align="left"><?=$AppUI->_("Supervisor")?></th>
							</tr>
				
							 <? 
							$query = "SELECT h.id, DATE_FORMAT(h.from_date,'%d-%m-%Y') as from_date, DATE_FORMAT(h.to_date,'%d-%m-%Y') as to_date, h.performance, h.potential, h.supervisor, u.user_last_name, u.user_first_name FROM hhrr_performance as h, users as u WHERE h.user_id ='$id' AND h.supervisor = u.user_id order by h.from_date desc limit 3";
				
							$sql = mysql_query($query);
							$cant_e = db_num_rows($sql);
							while($vec = mysql_fetch_array($sql))
							{
							?>
							<tr>
							   <td ><?=$vec[from_date];?></td>
							   <td ><?=$vec[to_date];?></td>
							   <td >
							     <?
					
								 $query_performance = "SELECT name_es FROM hhrr_performance_items WHERE id_item='$vec[performance]' "; 
								 $sql_performance = db_exec($query_performance);
								 $performance_desc = mysql_fetch_array($sql_performance);
													
								 ?>
								
								 <?=$performance_desc[0]?>
			 
							     
							   </td>
							   <td >
							     <?
					
								 $query_potential = "SELECT level, name_es FROM hhrr_performance_potential WHERE id_potential = '$vec[potential]' "; 
								 $sql_potential = db_exec($query_potential);
								 $potential_desc = mysql_fetch_array($sql_potential);
													
								 ?>
								
								 Nivel <?=$potential_desc[0]?> <?=$potential_desc[1]?>
							     
							   </td>
							   <td ><?=$vec[user_last_name];?>, <?=$vec[user_first_name];?></td>
							</tr>
							<tr class="tableRowLineCell"><td colspan="5"></td></tr>
							<?
							}
				
							if($cant_e =="0")
							 {
							?>
				       <tr>
							  <td colspan="5" align="center">
							   <? echo $AppUI->_('Noitems'); ?>
							 	<td>
						   </tr>
						   <? 
						   }?>
				
							 
							<? if (!$print)
							{?>
						   <tr>
							  <td colspan="5" align="right">
							  	 <? $see_more = "index.php?m=hhrr&a=viewhhrr&tab=4&id=".$id; ?>
							     <!--<input type="button" value="<?php echo $AppUI->_( 'See more' );?>" class="button" onClick="javascript:window.location='<?=$see_more;?>';" />!-->
							     <a href="<?= $see_more; ?>"> <?=$AppUI->_( 'See more' );?></a>
							     <? 
							     if (validar_permisos_hhrr($id,'performance_management',-1))
										{
											$edit_hrf = "index.php?m=hhrr&a=addedit&tab=4&id=".$id;?>
											&nbsp;&nbsp;
											<!--<input type="button" value="<?php echo $AppUI->_( 'edit' );?>" class="button" onClick="javascript:window.location='<?=$edit_hrf;?>';" />!-->
											<a href="<?= $edit_hrf; ?>"> <?=$AppUI->_( 'edit' );?></a>
										<?
										}?>
							  </td>
							 </tr>
							 <?
							}?>
			 		</table>
			 	</td>
			</tr>
				 

			</table>
	  </td>
	 </tr>
	</table>  
  </td>
</tr>
<? } ?>
<!-- FIN Evaluaci?n y rendimiento -->
<?
//Si es necesario ocultamos la seccion
if($_SESSION['vec_sections']['performance_management'])
{
	?>
	<script type="text/javascript">
		xajax_hideSection('performance_management');
	</script>
	<?
}?>

<!-- Desarrollo -->
<?
//Valido primero que tenga permisos de lectura sobre la seccion
//Si el formulario se carga para imprimir, y esta oculta la seccion, no muestro absolutamente nada de la misma
if (validar_permisos_hhrr($id,'development',1) AND !($print AND $_SESSION['vec_sections']['development']) )
{?>
<tr>
   <td colspan="2"  align="center"> 
   <br>
    <table cellspacing="1" cellpadding="0" border="0" style="border: 1px solid #000000;" width="100%">
    	<tr>
	  		<td align="center">
        	<table width="98%" border="0" cellpadding="2" cellspacing="0" align="center" >
						<tr class="tableHeaderGral">
						 <th align="center"><?=$AppUI->_("Development")?></th>
					   <td align="right" width='1%'> 
						 	<? if(!$print)//Si el formulario se carga para imprimir, NO muestro el boton!
							 	{?>
							 		<img onClick="xajax_hideSection('development')" id="img_development" name="img_development" src='./images/icons/collapse.gif' width='16' height='16' border='0' alt='<?php echo $AppUI->_('Hide');?>'>
							<?}?>
					   </td>
						</tr>
						 <tr class="tableRowLineCell">
						 	<td colspan='2' height='5'>
						 	</td>
						 </tr>
						<tr>
							<td colspan='2'>
								<? include_once('viewhhrr_summary_dev.php'); ?>
							</td>
						</tr>
				</td>
			</tr>
		</table>
  </td>
</tr>
<?
}?>
<!-- FIN Desarrollo -->
<?
//Si es necesario ocultamos la seccion
if($_SESSION['vec_sections']['development'])
{
	?>
	<script type="text/javascript">
		xajax_hideSection('development');
	</script>
	<?
}



if ($_GET[id] != $AppUI->user_id AND !($print AND $_SESSION['vec_sections']['comments']) ){
?>
<!-- Tabla de la solapa de datos personales / comentarios -->
  <tr>
   <td colspan="2"  align="center">
   	<br>
		<table cellspacing="1" cellpadding="0" border="0" style="border: 1px solid #000000;" width="100%">
		 <tr>
		   <td align="center">
       <table width="98%" border="0" cellpadding="2" cellspacing="0" align="center" >
				 <tr class="tableHeaderGral">
				   <td align="center" > 
					  <?=$AppUI->_("Comments")?>
				   </td>
				   <td align="right" width='1%'> 
					 	<? if(!$print)//Si el formulario se carga para imprimir, NO muestro el boton!
						 	{?>
						 		<img onClick="xajax_hideSection('comments')" id="img_comments" name="img_comments" src='./images/icons/collapse.gif' width='16' height='16' border='0' alt='<?php echo $AppUI->_('Hide');?>'>
						<?}?>
				   </td>
				 </tr>
				 <tr class="tableRowLineCell">
				 	<td colspan='2' height='5'>
				 	</td>
				 </tr>
       </table>
		  </td>
		 </tr>
		 <tr>
		 	<td>
		 		<table id='table_comments' cellspacing="1" cellpadding="5" border="0" width="100%" align="center">
				  <tr>
						<?
						$sql="SELECT hhrr_note_id,hhrr_user_id,hhrr_note, DATE_FORMAT(hhrr_note_date, '%d/%m/%Y %H:%i:%s') AS hhrr_note_date,hhrr_note_owner, CONCAT(user_last_name,', ',user_first_name) AS full_name FROM hhrr_notes LEFT JOIN users ON hhrr_notes.hhrr_note_owner=users.user_id WHERE hhrr_user_id = $id AND hhrr_note_type = ".NOTE_INTERNAL." ORDER BY hhrr_note_date DESC, hhrr_note_id DESC LIMIT 3;";
						$hhrr_notes=db_loadList($sql);
						if ( count($hhrr_notes) >0 ) // si hay algun comentario muestro la tabla sino no
						{?>
		
			   	<td colspan="3">
						<table cellspacing="1" cellpadding="2" border="0" align="right" width="100%">
						<?
						foreach($hhrr_notes as $note)
						{
						?>
						
						<tr class="bugnote">
							<td class="form-title" width="20%">
								<?=$note['full_name']?><br>
								<span class="small"><?=$note['hhrr_note_date']?></span>
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
			  	<?}
			  		else
			  			echo "<td colspan='3' align='center'>" .$AppUI->_('Noitems'). "<br></td>"; ?>
				  </tr>
				  
				  <? if (!$print)
					   {?>
						 <tr>
						  <td align="right">
							   <? $see_more = "index.php?m=hhrr&a=viewhhrr&tab=1&id=".$id; ?>
								 <a href="<?= $see_more; ?>"> <?=$AppUI->_( 'See more' );?></a>
									<?
									if (validar_permisos_hhrr($id,'personal',-1))
									{
										echo "&nbsp;&nbsp";
										$edit_hrf = "index.php?m=hhrr&a=addedit&tab=1&id=".$id;
									?>
								 		<a href="<?= $edit_hrf; ?>"> <?=$AppUI->_( 'edit' );?></a>
								 <?}?>
								 &nbsp;&nbsp;<br><br>
						  </td>
						 </tr>
						 <?}?>
		 		</table>
		 	</td>
		 </tr>		  
		  
		</table>
	 </td>
	</tr>
<!-- Fin Tabla de la solapa de datos personales / comentarios --> 
<?
}
?>
</table>
<?
//Si es necesario ocultamos la seccion
if($_SESSION['vec_sections']['comments'])
{
	?>
	<script type="text/javascript">
		xajax_hideSection('comments');
	</script>
	<?
}
?>


