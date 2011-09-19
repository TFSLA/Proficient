<?php /* DEPARTMENTS $Id: addeditold.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
// Add / Edit Company
$id = isset($_GET['id']) ? $_GET['id'] : 0;


// pull data for this department

$sql = "
SELECT *
FROM hhrr
WHERE id = $id
";
if (!db_loadHash( $sql, $drow ) && $id > 0) {
	$titleBlock = new CTitleBlock( 'Invalid HHRR ID', 'hhrr.gif', $m, 'hhrr.index' );
	$titleBlock->addCrumb( "?m=hhrr", "Human Resources" );
	$titleBlock->show();
} else {


// setup the title block
	$ttl = $id > 0 ? "Edit HHRR" : "Add HHRR";
	$titleBlock = new CTitleBlock( $ttl, 'hhrr.jpg', $m, 'ID_HELP_DEPT_EDIT' );
	$titleBlock->show();
?>
<script language="javascript">

function submitIt() {
	var form = document.editFrm;
	if (form.firstname.value.length < 1) {
		alert( "<?=$AppUI->_('Please enter the first name')?>" );
		form.firstname.focus();
        } else if (form.lastname.value.length < 1) {
		alert( "<?=$AppUI->_('Please enter the last name')?>" );
		form.lastname.focus();
	} else {
		form.submit();
	}
}

  function changeJobStatus(){
    divjob.style.display = 'none'; 
    if(document.editFrm.actualjob.selectedIndex > 1)
	    divjob.style.display = ''; 

  }

</script>
<form name="editFrm" action="?m=hhrr" method="post" enctype="multipart/form-data">
	<input type="hidden" name="dosql" value="do_hhrr_aed" />
	<input type="hidden" name="id" value="<?php echo $id;?>" />
<table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
<tr>
	<td align="right" colspan=2>
  	  <input type="button" value="<?php echo $AppUI->_( 'View Skills' );?>" class="button" onClick="document.location.href='index.php?m=hhrr&a=viewskills&id=<?=$id?>'" />
    </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Input Date' );?>:</td>
	<td><?php echo @$drow["inputdate"];?>
		<input type="hidden" class="text" name="inputdate" value="<?php echo @$drow["inputdate"];?>">
    </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'First Name' );?>:</td>
	<td><input type="text" class="text" name="firstname" value="<?php echo @$drow["firstname"];?>" maxlength="48" size="48"></td>
	<td valign="top" align="center" rowspan="999">
        <br>
        <?if($drow["photo"]!="ninguna" && $id!=""){?>
        <img src="./upload/<?php echo @$drow["id"];?>/<?php echo @$drow["photo"];?>" height="140" width="140" border "1">
        <?}?>
    </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Last Name' );?>:</td>
	<td><input type="text" class="text" name="lastname" value="<?php echo @$drow["lastname"];?>" maxlength="48" size="48"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Home Phone' );?>:</td>
	<td><input type="text" class="text" name="homephone" value="<?php echo @$drow["homephone"];?>" maxlength="20" size="20"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Cell Phone' );?>:</td>
	<td><input type="text" class="text" name="cellphone" value="<?php echo @$drow["cellphone"];?>" maxlength="20" size="20"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Email' );?>:</td>
	<td><input type="text" class="text" name="email" value="<?php echo @$drow["email"];?>" maxlength="48" size="48"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Address' );?>:</td>
	<td><input type="text" class="text" name="address" value="<?php echo @$drow["address"];?>" maxlength="48" size="48"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'City' );?>:</td>
	<td><input type="text" class="text" name="city" value="<?php echo @$drow["city"];?>" maxlength="48" size="48"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'State' );?>:</td>
	<td><input type="text" class="text" name="state" value="<?php echo @$drow["state"];?>" maxlength="30" size="30"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'ZIP' );?>:</td>
	<td><input type="text" class="text" name="zip" value="<?php echo @$drow["zip"];?>" maxlength="8" size="8"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Country' );?>:</td>
	<td><input type="text" class="text" name="country" value="<?php echo @$drow["country"];?>" maxlength="48" size="48"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Birthday' );?>:</td>
	<td><input type="text" class="text" name="birthday" value="<?php echo @$drow["birthday"];?>" maxlength="10" size="10"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'ID' );?>:</td>
	<td>
    	<select name="doctype">
        	<option <?php if($drow["doctype"]=="DNI")echo "selected";?> value="DNI">DNI</option>
            <option <?php if($drow["doctype"]=="LC")echo "selected";?> value="LC">LC</option>
            <option <?php if($drow["doctype"]=="LE")echo "selected";?> value="LE">LE</option>    
        </select>
        <input type="text" class="text" name="docnumber" value="<?php echo @$drow["docnumber"];?>" maxlength="16" size="16">
    </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Marital State' );?>:</td>
	<td>
           <select name="maritalstate">
              <option <?php if($drow["maritalstate"]=="Soltero/a")echo "selected";?> value="Soltero/a">Soltero/a</option>
              <option <?php if($drow["maritalstate"]=="Casado/a")echo "selected";?> value="Casado/a">Casado/a</option>
              <option <?php if($drow["maritalstate"]=="Divorciado/a")echo "selected";?> value="Divorciado/a">Divorciado/a</option>    
              <option <?php if($drow["maritalstate"]=="Viudo/a")echo "selected";?> value="Viudo/a">Viudo/a</option>    
           </select>
    </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Nationality' );?>:</td>
	<td><input type="text" class="text" name="nationality" value="<?php echo @$drow["nationality"];?>" maxlength="28" size="28"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Children' );?>:</td>
	<td><input type="text" class="text" name="children" value="<?php echo @$drow["children"];?>" maxlength="2" size="2"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Tax Type' );?>:</td>
	<td><input type="text" class="text" name="taxidtype" value="<?php echo @$drow["taxidtype"];?>" maxlength="30" size="30"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Tax ID number' );?>:</td>
	<td><input type="text" class="text" name="taxidnumber" value="<?php echo @$drow["taxidnumber"];?>" maxlength="20" size="20"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'MS Messenger' );?>:</td>
	<td><input type="text" class="text" name="msmessenger" value="<?php echo @$drow["msmessenger"];?>" maxlength="48" size="48"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'ICQ UIN' );?>:</td>
	<td><input type="text" class="text" name="icq" value="<?php echo @$drow["icq"];?>" maxlength="48" size="48"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Yahoo Messenger' );?>:</td>
	<td><input type="text" class="text" name="yahoomessenger" value="<?php echo @$drow["yahoomessenger"];?>" maxlength="48" size="48"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Comments' );?>:</td>
	<td><textarea rows="6" cols="40" name="comments"><?php echo @$drow["comments"];?></textarea></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Resume' );?>:</td>
	<td colspan="2"><input type="file" class="text" name="resume">
        <?php
          if($drow["resume"]<>"ninguna") { 
            //echo "&nbsp;Actual: <b><a class='link1'  href='./upload/hhrr/".$drow["id"]."/".urlencode($drow["resume"])."'>".$drow["resume"]."</a>";
            echo "&nbsp;Actual: <b><a class='link1'  href='./upload/".$drow["id"]."/".str_replace(" ","%20",$drow["resume"])."'>".$drow["resume"]."</a>";
            echo '</b>&nbsp;<input class="button" type="button" value="Quitar" onclick="doRemoveResume()">';
          }
        ?>
    </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Photo' );?>:</td>
	<td>
          <input type="file" class="text" name="photo">
          <?php
            if($drow["photo"]<>"ninguna") { 
              echo "&nbsp;Actual: <b><a class='link1'  href='javascript:image_open(\"upload/".$drow["id"]."/".urlencode($drow["photo"])."\", \"img1\")'>".$drow["photo"]."</a>";
              echo '</b>&nbsp;<input class="button" type="button" value="Quitar" onclick="doRemoveFotoChica()">';
            }   
          ?>

    </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Actual job' );?>:</td>
	<td>
	    <select name="actualjob" onChange="changeJobStatus();">
			<option <?if($drow["actualjob"]=="")echo "selected";?>         value=""><?php echo $AppUI->_( '-- Select here --' );?></option>
			<option <?if($drow["actualjob"]=="No trabajando")echo "selected";?>         value="No trabajando"><?php echo $AppUI->_( 'Not working' );?></option>
			<option <?if($drow["actualjob"]=="Trabajando Full Time")echo "selected";?>  value="Trabajando Full Time"><?php echo $AppUI->_( 'Working Full Time' );?></option>
			<option <?if($drow["actualjob"]=="Trabajando Part Time")echo "selected";?>  value="Trabajando Part Time"><?php echo $AppUI->_( 'Working Part Time' );?></option>
			<option <?if($drow["actualjob"]=="Trabajando Free Lance")echo "selected";?> value="Trabajando Free Lance"><?php echo $AppUI->_( 'Working Free Lance' );?></option>
		</select>
    </td>
</tr>
<tr>
    <td class="celdatexto" colspan=2>
		<div style="display: none;" id="divjob" onclick="window.event.cancelBubble = true;">
			<table width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td align="right" width="25%"><?php echo $AppUI->_( 'Actual company' );?>:</td>
					<td align="left">&nbsp;<input type="text" class="text" name="actualcompany" value="<?php echo @$drow["actualcompany"];?>" maxlength="32" size="32"></td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_( 'Actual working hours' );?>:</td>
					<td align="left">&nbsp;<input type="text" class="text" name="workinghours" value="<?php echo @$drow["workinghours"];?>" maxlength="32" size="32"></td>
				</tr>
  			</table>
		</div>
    </td>
</tr>  
<tr>
	<td align="right"><?php echo $AppUI->_( 'Work preference' );?>:</td>
	<td>
	&nbsp;<input class="camposform" type="checkbox"  name="wantsfulltime" <?if($drow["wantsfulltime"]==1)echo "checked"?> value="1">
	Full Time
	&nbsp;<input class="camposform" type="checkbox"  name="wantsparttime" <?if($drow["wantsparttime"]==1)echo "checked"?> value="1">
	Part Time
	&nbsp;<input class="camposform" type="checkbox"  name="wantsfreelance" <?if($drow["wantsfreelance"]==1)echo "checked"?> value="1">
	Free Lance
    </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Diary avalability' );?>:</td>
	<td><input type="text" class="text" name="hoursavailableperday" value="<?php echo @$drow["hoursavailableperday"];?>" maxlength="5" size="5">hs</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Cost Per Hour' );?>:</td>
	<td><input type="text" class="text" name="costperhour" value="<?php echo @$drow["costperhour"];?>" maxlength="5" size="5"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Salary wanted' );?>:</td>
	<td><input type="text" class="text" name="salarywanted" value="<?php echo @$drow["salarywanted"];?>" maxlength="8" size="8"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Salary wanted' );?>:</td>
	<td><input type="text" class="text" name="salarywanted" value="<?php echo @$drow["salarywanted"];?>" maxlength="8" size="8"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Was interviewed' );?>:</td>
	<td><input type="checkbox" class="text" name="wasinterviewed"<?=$drow["wasinterviewed"] ? " checked" : ""?>></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Interview comments' );?>:</td>
	<td><textarea name="interviewcomments" rows="6" cols="40"><?=$drow["interviewcomments"]?></textarea></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Username' );?>:</td>
	<td><input type="text" class="text" name="username" value="<?php echo @$drow["username"];?>" maxlength="16" size="16"></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Password' );?>:</td>
	<td><input type="text" class="text" name="password" value="<?php echo @$drow["password"];?>" maxlength="16" size="16"></td>
</tr>
<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
	</td>
	<td colspan="2" align="right">
		<input type="button" value="<?php echo $AppUI->_( 'submit' );?>" class="button" onClick="submitIt()" />
	</td>
</tr>
</form>
</table>
<script language="javascript">
changeJobStatus();
</script>
<?php } ?>