<?php /* HHRR $Id: print_hhrr.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */

global  $dialog,$id,$AppUI, $canEdit;

$id = isset($_GET['id']) ? $_GET['id'] : 0;

$canAddHHRR = CHhrr::canAdd();
$canEditHHRR = CHhrr::canEdit($id);


$canEditModule = !getDenyEdit( "hhrr" );
		
$result = mysql_query("select * from users where user_id = $id;");
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$id = $row["user_id"];
$firstname = $row["user_first_name"];
$lastname = $row["user_last_name"];
      
$ttl = $firstname." ".$lastname;
$titleBlock = new CTitleBlock( $ttl, 'hhrr.gif', $m, 'hhrr.index' );

$titleBlock->addCell( "[ <a href=\"javascript:window.print();\" style=\"text-decoration:none\"> ".strtolower($AppUI->_('Print'))." : <a href=\"index.php?m=hhrr&a=print_hhrrskills&id=$id&t=mat&dialog=1&suppressLogo=1 \" style=\"text-decoration:none\">".strtolower($AppUI->_('Matrix'))."</a> ]", '','','');

$titleBlock->show();

$maritalstates= dPgetSysVal("MaritalState");
$IMTypes= dPgetSysVal("IMType");
$SCandidateStatus = dPgetSysVal("CandidateStatus");

$uploads_dir = $AppUI->getConfig('hhrr_uploads_dir');


      $result = mysql_query("select * from users where user_id = $id;");
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
      $comments = $row["comments"];
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
      $interviewcomments = $row["interviewcomments"];
      $candidatestatus = $row["candidatestatus"];
     
?>
<br>
<table border="0" cellpadding="4" cellspacing="0" width="770" >
  <tr>
	<td  width="520" >
	   
	    <!-- Tabla de la solapa de datos personales --> 
		<table cellspacing="1" cellpadding="0" border="0" style="border: 1px solid #000000;"  width="525">
		 <tr>
		   <td align="center">

             <table cellspacing="1" cellpadding="0" border="0" width="99%">
				 <tr class="tableHeaderGral">
				   <td align="center" > 
					  <?=$AppUI->_("Personal data")?>
				   </td>
				 </tr>
              </table>
		  </td>
		  </tr>
		  <tr>
		   <td align="center">
		      
			  <table cellspacing="1" cellpadding="0" border="0" width="98%">
			   <tr>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('First Name')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;<?= $firstname?></td>
				<td class="right" rowspan="7" colspan="2" valign="top"><CENTER>
				  <? if($logo!="ninguna" and $logo!=""){?>
				  <img border="1" height="140" width="140" src="<?=$uploads_dir."/".$id?>/<?=$logo?>"> 
				  <?}else echo "<br><br><br><b><?php echo $AppUI->_('Photo not available')?></b>"?>
				  </center>
				</td>
			   </tr>  
			   <tr>
			    <td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Last Name')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;<?= $lastname ?></td>
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
			   </tr>  
			   <tr>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('State')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;<?= $state ?></td>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Nationality')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;<?= $nationality ?></td>
			   </tr>  
			   <tr>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Phone')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;<?= $phone ?></td>
				<td class="right" nowrap="nowrap" ><b><?php echo $AppUI->_('Birthday')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;
				  <?=substr($birthday,8,2)?>/<?=substr($birthday,5,2)?>/<?=substr($birthday,0,4)?>
			    </td>
			   </tr>  
			   <tr>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Home Phone')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;<?= $homephone ?></td>    
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Marital State')?>:&nbsp;</b></td>
				<td class="hilite" nowrap="nowrap">&nbsp;<?= $AppUI->_($maritalstates[$maritalstate])?></td>
			   </tr>  
			   <tr>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Cell Phone')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;<?= $cellphone ?></td>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Children')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;<?= $children ?></td>
			   </tr>  
			   <tr>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('IM')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;<?= $IMTypes[$im_type].": ".$im_id?></td>
				<td class="right" nowrap="nowrap">&nbsp;<b><?php echo $AppUI->_('ID')?>:&nbsp;</b>
				</td>
				<td class="hilite">&nbsp;<?= $doctype ?> <?= $docnumber ?></td>
			   </tr>  
			   <tr>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Actual Company')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;<?= $actualcompany ?></td>    
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Actual job')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;<?=$actualjob?></td>   
			   </tr>  
			   <tr>
				<td class="right" nowrap="nowrap"><b><?php echo $AppUI->_('Actual working hours')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;<?= $workinghours ?></td>
				<td class="right" nowrap="nowrap"><b><?=$AppUI->_('Daily avalability')?>:&nbsp;</b></td>
				<td class="hilite">&nbsp;<?=$hoursavailableperday?></td>     
			   </tr>
			   <tr>
			    <td class="right"><b><?php echo $AppUI->_('Job Title');?>:&nbsp;</b></td>
				<td><?=$row[user_job_title];?></td>
				<td class="right" nowrap="nowrap" valign="top"><b><?php echo $AppUI->_('Tax ID number')?>:&nbsp;</b></td>
				<td class="hilite" valign="top">&nbsp;<?= $taxidnumber ?></td>
			   </tr>  
			  </table>
               
            
		   </td>
		 </tr>
		</table>
		 <!-- Fin de la Tabla de la solapa de datos personales --> 
       
	</td>
    
	<td align="center" valign="bottom" >
       
	   
	   <!-- Tabla de la solapa de datos personales / comentarios --> 
		<table cellspacing="1" cellpadding="0" border="0"  style="border: 1px solid #000000;" width="235" height="100">
		 <tr>
		   <td align="center" valign="top">

             <table cellspacing="1" cellpadding="0" border="0" width="98%">
				 <tr class="tableHeaderGral">
				   <td align="center" > 
					  <?=$AppUI->_("Comments")?>
				   </td>
				 </tr>
              </table>
		  </td>
		  </tr>
		  <tr>
		    <td>
			  &nbsp;<?= $comments ?>
			</td>
		  </tr>
		 </table>
		 <!-- Fin Tabla de la solapa de datos personales / comentarios --> 
	</td>
  <tr>
  
      <!-- Tabla de antecedentes laborales  -->
  <tr>
    <td colspan="2"  align="center">
	    <br>
		<table cellspacing="1" cellpadding="0" border="0" width="770"  style="border: 1px solid #000000;">
		 <tr>
		   <td align="center" valign="top">

             <table cellspacing="1" cellpadding="0" border="0" width="99%">
				 <tr class="tableHeaderGral">
				   <td align="center" > 
					 <?=$AppUI->_("Work Experience")?>
				   </td>
				 </tr>
              </table>
		  </td>
		  </tr>

		  <tr>
		    <td align="center">
			   <br>
               <table cellspacing="1" cellpadding="1" border="0" width="98%" class="tableForm_bg" align="center">
			     <tr class="tableHeaderGral" >
			       <th align="center" colspan="6"><?=$AppUI->_("Internal Companies")?></th>
			     </tr>
				 <tr class="tableHeaderGral">
				 <th align="left" width="150"><?=$AppUI->_("Company")?></th>
				 <th align="left" width="100"><?=$AppUI->_("Management/Area")?></th>
				 <th align="left" width="100"><?=$AppUI->_("Function")?></th>
				 <th align="left" width="70"><?=$AppUI->_("From")?></th>
				 <th align="left" width="70"><?=$AppUI->_("To")?></th>
				 <th align="left" width="300" ><?=$AppUI->_("Accomplishments")?></th>
			    </tr>
			   <?
				  $query = "SELECT id, user_id, company, area, function, DATE_FORMAT(from_date,'%d-%m-%Y') as fdate ,DATE_FORMAT(to_date,'%d-%m-%Y') as tdate, profit FROM hhrr_ant WHERE user_id ='$id'   AND internal_company = '1' ";

				  $sql = mysql_query($query);

				  while ($vec = mysql_fetch_array($sql)){
			   ?>
				   <!-- Lista de antecedentes -->
				   <tr>
					 <td  bgcolor="#ffffff" width="150"><?
					  $cia = mysql_result(mysql_query("SELECT company_name FROM companies WHERE company_id ='$vec[company]' "),0);
					  echo $cia; ?>	
					 </td>
					 <td  bgcolor="#ffffff" width="100"><?=$vec['area']?></td>
					 <td  bgcolor="#ffffff" width="100"><?=$vec['function']?></td>
					 <td  bgcolor="#ffffff" nowrap="nowrap" width="70" ><?=$vec['fdate']?></td>
					 <td  bgcolor="#ffffff" nowrap="nowrap" width="70" ><?=$vec['tdate']?></td>
					 <td  bgcolor="#ffffff" width="300" ><?=$vec['profit']?></td>
				   </tr>
			   <? } ?>
					
					
					  <? 
					  $cant = mysql_num_rows($sql); 
					  
					  if($cant =="0")
					  {
					   echo "<tr>
					  <td colspan=\"5\" align=\"center\">".$AppUI->_('Noitems')."<td>
					</tr>";
					  }

					  ?>

			   </table>
			   <br><br>

			   <table cellspacing="1" cellpadding="1" border="0" width="98%" class="tableForm_bg" align="center">
			     <tr class="tableHeaderGral" >
			       <th align="center" colspan="6"><?=$AppUI->_("Other Companies")?></th>
			     </tr>
				 <tr class="tableHeaderGral">
				 <th align="left" width="150"><?=$AppUI->_("Company")?></th>
				 <th align="left" width="100"><?=$AppUI->_("Management/Area")?></th>
				 <th align="left" width="100"><?=$AppUI->_("Function")?></th>
				 <th align="left" width="70"><?=$AppUI->_("From")?></th>
				 <th align="left" width="70"><?=$AppUI->_("To")?></th>
				 <th align="left" width="300" ><?=$AppUI->_("Accomplishments")?></th>
			    </tr>
			   <?
				  $query = "SELECT id, user_id, company, area, function, DATE_FORMAT(from_date,'%d-%m-%Y') as fdate ,DATE_FORMAT(to_date,'%d-%m-%Y') as tdate, profit FROM hhrr_ant WHERE user_id ='$id'   AND internal_company = '0' ";

				  $sql = mysql_query($query);

				  while ($vec = mysql_fetch_array($sql)){
			   ?>
				   <!-- Lista de antecedentes -->
				   <tr>
					 <td  bgcolor="#ffffff" width="150"><?=$vec['company'];?></td>
					 <td  bgcolor="#ffffff" width="100"><?=$vec['area']?></td>
					 <td  bgcolor="#ffffff" width="100"><?=$vec['function']?></td>
					 <td  bgcolor="#ffffff" nowrap="nowrap" width="70" ><?=$vec['fdate']?></td>
					 <td  bgcolor="#ffffff" nowrap="nowrap" width="70" ><?=$vec['tdate']?></td>
					 <td  bgcolor="#ffffff" width="300" ><?=$vec['profit']?></td>
				   </tr>
			   <? } ?>
					
					
					  <? 
					  $cant = mysql_num_rows($sql); 
					  
					  if($cant =="0")
					  {
					   echo "<tr>
					  <td colspan=\"5\" align=\"center\">".$AppUI->_('Noitems')."<td>
					</tr>";
					  }

					  ?>

			   </table>
			   <br>


			</td>
		  </tr>

		</table>

	</td>
  </tr>
     
	  <!-- Fin Tabla de antecedentes laborales  -->

	   <!-- Tabla de Formación profecional  -->
  <tr>
    <td colspan="2"  align="center">
	    <br>

		<table cellspacing="1" cellpadding="0" border="0" width="770"  style="border: 1px solid #000000;">
		 <tr>
		   <td align="center" valign="top">

             <table cellspacing="1" cellpadding="0" border="0" width="99%">
				 <tr class="tableHeaderGral">
				   <td align="center" > 
					 <?=$AppUI->_("Education")?>
				   </td>
				 </tr>
              </table>
		  </td>
		  </tr>
          <tr>
		    <td align="center">
			   <br>
               
               <table cellspacing="1" cellpadding="1" border="0" width="98%" class="tableForm_bg" align="center">
				 <tr class="tableHeaderGral" >
				  <th align="center" colspan="4"><?=$AppUI->_("Formal Education")?></th>
				 </tr>
				 <tr class="tableHeaderGral">
				 <th align="left"><?=$AppUI->_("Academic level")?></th>
				 <th align="left"><?=$AppUI->_("Title")?></th>
				 <th align="left"><?=$AppUI->_("Institution")?></th>
				 <th align="left"><?=$AppUI->_("Status")?></th>
			   </tr>
			   <?
				  $query = "SELECT id, id_user, level, title, instit, status FROM hhrr_education WHERE id_user ='$id' AND type='0' ";
				  
				  $sql = mysql_query($query);

				  while ($vec = mysql_fetch_array($sql)){
			   ?>
				   <!-- Lista de antecedentes -->
				   <tr>
					 <td  bgcolor="#ffffff" ><?=$vec['level']?></td>
					 <td  bgcolor="#ffffff" ><?=$vec['title']?></td>
					 <td  bgcolor="#ffffff" ><?=$vec['instit']?></td>
					 <td  bgcolor="#ffffff" >
					 <?    
						   switch($vec['status']){
							 case "0":
								 echo $AppUI->_("abandonado");
							 break;
							 case "1":
								 echo $AppUI->_("Finalized");
							 break;
							 case "2":
								 echo $AppUI->_("In course ");
							 break;
						   }
						   
					 ?>
					 </td>
				   </tr>
			   <? } ?>
			  
					  <? 
					  $cant = mysql_num_rows($sql); 
					  
					  if($cant =="0")
					  {
					   echo "<tr>
					  <td colspan=\"3\" align=\"center\">".$AppUI->_('Noitems')."<td>
					</tr>";
					  }

					  ?>
				</table>
                
				<br><br>

				<table cellspacing="1" cellpadding="1" border="0" width="98%" class="tableForm_bg" align="center">
				 <tr class="tableHeaderGral">
				  <th align="center" colspan="4"><?=$AppUI->_("Course/Seminary")?></th>
				 </tr>
				 <tr class="tableHeaderGral">
				 <th align="left"><?=$AppUI->_("Course/Seminary")?></th>
				 <th align="left"><?=$AppUI->_("Title")?></th>
				 <th align="left"><?=$AppUI->_("Institution")?></th>
				 <th align="left"><?=$AppUI->_("Date")?></th>
			   </tr>
			   <?
				  $query = "SELECT id, id_user, level, title, instit, DATE_FORMAT(s_date,'%d-%m-%Y') as sdate FROM hhrr_education WHERE id_user ='$id' AND type='1' ";
				  
				  $sql = mysql_query($query);

				  while ($vec = mysql_fetch_array($sql)){
			   ?>
				   <!-- Lista de antecedentes -->
				   <tr>
					 <td  bgcolor="#ffffff" ><?=$vec['level']?></td>
					 <td  bgcolor="#ffffff" ><?=$vec['title']?></td>
					 <td  bgcolor="#ffffff" ><?=$vec['instit']?></td>
					 <td  bgcolor="#ffffff" ><?=$vec['sdate']?></td>
				   </tr>
			   <? } ?>
					
					  <? 
					  $cant = mysql_num_rows($sql); 
					  
					  if($cant =="0")
					  {
					   echo "<tr>
					  <td colspan=\"3\" align=\"center\">".$AppUI->_('Noitems')."<td>
					</tr>";
					  }

					  ?>

					</table>
					<br>

			</td>
		  </tr>

		</table>

	</td>
  </tr>
     
	  <!-- Fin Tabla de Formación profecional  -->
 <? if ($id != $AppUI->user_id){ ?>
<tr>
   <td colspan="2"  align="center"> 
   <br>
    <!-- Evaluación y rendimiento -->
    <table cellspacing="1" cellpadding="0" border="0" style="border: 1px solid #000000;"  width="770">
     <tr>
	  <td align="center">
	    
        <table width="99%" border="0" cellpadding="2" cellspacing="0" align="center" >
			<tr class="tableHeaderGral">
				<th align="center" colspan="5"><?=$AppUI->_("Performance Management")?></th>
			</tr>
			<tr class="tableRowLineCell"><td colspan="5"></td></tr>

			 <tr class="tableHeaderGral">
			 <th align="left"><?=$AppUI->_("From")?></th>
			 <th align="left"><?=$AppUI->_("To")?></th>
			 <th align="left" width="200"><?=$AppUI->_("Performance")?></th>
			 <th align="left" width="200"><?=$AppUI->_("Potential")?></th>
			 <th align="left"><?=$AppUI->_("Supervisor")?></th>
			</tr>

			 <? 
			$query = "SELECT count(*) FROM hhrr_performance  WHERE user_id ='$id'";
            $cant_e = mysql_result(mysql_query($query),0);

			$query = "SELECT h.id, DATE_FORMAT(h.from_date,'%d-%m-%Y') as from_date, DATE_FORMAT(h.to_date,'%d-%m-%Y') as to_date, h.performance, h.potential, h.supervisor, u.user_last_name, u.user_first_name FROM hhrr_performance as h, users as u WHERE h.user_id ='$id' AND h.supervisor = u.user_id ";

			$sql = mysql_query($query);
			
			while($vec = mysql_fetch_array($sql))
			{
			?>
			<tr>
			   <td ><?=$vec[from_date];?></td>
			   <td ><?=$vec[to_date];?></td>
			   <td ><?=$vec[performance];?></td>
			   <td ><?=$vec[potential];?></td>
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
		   <? }?> 
		</table>

		<br>
        
	  </td>
	 </tr>
	
	</table>

  
  </td>
</tr>
<? } ?>


</table>


