<?php /* TASKS $Id: print_hhrrskills.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */

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

$titleBlock->addCell( "[ <a href=\"javascript:window.print();\" style=\"text-decoration:none\"> ".strtolower($AppUI->_('Print'))." : <a href=\"index.php?m=hhrr&a=print_hhrr&id=$id&t=mat&dialog=1&suppressLogo=1 \" style=\"text-decoration:none\">".strtolower($AppUI->_('Personal data'))."</a> ]", '','','');

$titleBlock->show();

?>
<br>
<table cellspacing="1" cellpadding="0" border="0" style="border: 1px solid #000000;"  width="770">
  <tr>
	<td align="center">

		<table width="99%" border="0" cellpadding="2" cellspacing="0" align="center">


			  <?
			  $sql = "SELECT * 
			  FROM  hhrrskills, skills, skillcategories 
			  WHERE skillcategories.id = skills.idskillcategory 
			  AND idskill = skills.id 
			  AND user_id='$id' 
			  AND VALUE > 1 
			  ORDER BY skillcategories.sort,skillcategories.name, skills.description;";
			  //echo "<pre>$sql</pre>";
			  $resultskills = mysql_query($sql);
			  $lastcat="7dgd7gHs8gM9634YaFDdj5";
			  while ($row = mysql_fetch_array($resultskills, MYSQL_ASSOC)) {
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
		  $cant = mysql_num_rows($resultskills); 
		  
		  if($cant =="0")
		  {
		   echo $AppUI->_('Noitems_matriz');
		  }

		  ?><td>
		</tr>
		</table>
		<br>

</td>
</tr>
</table>