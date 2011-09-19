<?php
global $canEditHHRR, $AppUI;
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$post_id = $id;
if($_GET["a"] == "viewrole"){
	$id = -1*$id;
}
$eval_id = isset($_GET['eval_id']) ? $_GET['eval_id'] : 0;
$canSaveEvaluation = false;
$tab = $_GET['tab'];
$df = $AppUI->getPref('SHDATEFORMAT');
$timeunits= array("1" =>  $AppUI->_("Months"), "12"=>$AppUI->_("Years"));
$a = $_GET['a'];
$job_id = isset($_GET["job_id"]) ? $_GET["job_id"] : 0;

if((!empty($job_id) && $job_id > 0) && getDenyEdit("hhrr")){
	 $AppUI->redirect( "m=public&a=access_denied" );
}

if($a == "viewevaluation") $job_id = 1;
?>
<script language="Javascript" type="text/javascript"><!--  

function delSkill(id, name){
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Skill');?> " + name + "?" )) {
		document.skillsFrm.idskill.value = id;
		document.skillsFrm.submit();
	}
}

//-->
</script>

<?php if(!getDenyEdit("hhrr") && $a != "viewrole" && $a != "viewevaluation"){ ?>
<table width="100%" border="0" cellpadding="2" cellspacing="0" class="std">
<form name="compareForm" id="compareForm" method="GET" action="">
	<input type="hidden" name="m" id="m" value="<?=$_GET["m"]?>">
	<input type="hidden" name="a" id="a" value="<?=$_GET["a"]?>">
	<input type="hidden" name="id" id="id" value="<?=$_GET["id"]?>">
	<?php if(isset($_GET["tab"])) {?>
		<input type="hidden" name="tab" id="tab" value="<?=$_GET["tab"]?>">
	<?php } ?>
	<tr>
		<td width="40%"><br></td>
		<td><br></td>
	</tr>
	<?php if ($_GET["a"] != "compare") { ?>
	<tr>
		<td align="right" colspan="5"><?php echo $AppUI->_( 'Select a Job to compare' );?>&nbsp;
		
		<?php
			$jobs = CJobs::getJobs();
			if($selectedJob<=0)
				$jobs["0"] = $AppUI->_("none");
			echo arraySelect( $jobs, 'job_id', 'size="1" class="text" style="width:250px;" onChange="compare()"', $job_id);
		?>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td td colspan="2"><br>
		</td>
	</tr>
</form>
</table>
<?php } ?>

<table width="100%" border="0" cellpadding="2" cellspacing="0" >
<form name="skillsFrm" action="" method="POST">
<input type="hidden" name="user_id" value="<?=$post_id;?>" />
<input type="hidden" name="dosql" value="do_userskills_aed" />
<input type="hidden" name="idskill" value="">
<input type="hidden" name="del" value="1">

<tr class="tableHeaderGral">
	<?php if( ( empty($job_id) || $job_id <= 0 ) && $a != "viewevaluation" ) {
		echo "<th nowrap></th>";
		} ?>
	<th nowrap><?=$AppUI->_("Skill")?></th>
	<?php if($a != "viewrole") { ?>
	<th nowrap><?=$AppUI->_("Autoevaluated Value")?></th>
	<th nowrap><?=$AppUI->_("Perceived Value")?></th>
	<?php if(!empty($job_id) && $job_id > 0) {?>
		<th nowrap><?=$AppUI->_("Job Required Value")?></th>
	<?php }
	} else { ?>
		<th nowrap align="left"><?=$AppUI->_("Job Required Value")?></th>
	<?php } ?>
	<th nowrap><?=$AppUI->_("Last Use")?></th>
	<th nowrap><?=$AppUI->_("Accum.Experience")?></th>
	<th nowrap width="35%"><?=$AppUI->_("Comments")?></th>
</tr>
<style type="text/css">
.different {
color: red;
font-weight: bold;
}
</style>

      <?php
      if(!empty($job_id) && $job_id > 0){
      	$AppUI->evaluation["comparing_job"] = $job_id;
      	$AppUI->evaluation["evaluation_user"] = $AppUI->user_id;
      	$AppUI->evaluation["evaluating_user"] = $id;
      	$AppUI->evaluation["evaluation_date"] = date("Y-m-d H:i:s");
      	
      	$sql = "SELECT idskill FROM hhrrskills WHERE user_id = ".(-1*$job_id);
      	$data = mysql_query($sql);
      	
      	$skillsList = " (-1";
      	if(count($data) > 0){
	      	while($row = mysql_fetch_array($data)){
	      		$skillsList .= ", ".$row["idskill"];
	      	}
      	}
      	$skillsList .= " )";
      	$join = "AND skills.id IN $skillsList 
      			 AND user_id = '".(-1*$job_id)."'
      			 AND value > 1";
      }else{
      	$join = "AND (value > 1 OR perceived_value > 0) 
      			 AND user_id='$id' ";
      }
      if($a!="viewevaluation"){
	      $sql = "SELECT * 
	      FROM  hhrrskills, skills, skillcategories 
	      WHERE skillcategories.id = skills.idskillcategory 
	      AND idskill = skills.id 
	      $join 
	      ORDER BY skillcategories.sort,skillcategories.name, skills.description;";
      }else{
      	  $sql = "SELECT item_name AS description, item_group AS name, autoevaluated_value, perceived_value, 
      	  			job_required_value, last_use, experience, user_comments 
	      FROM  hhrr_skills_evaluations_items
	      WHERE evaluation_id = $eval_id
	      ORDER BY item_group, item_name;";
      }
      //echo "<pre>$sql</pre>";
      $resultskills = mysql_query($sql);
      $lastcat="7dgd7gHs8gM9634YaFDdj5";
      $i = 0;
      while ($row = mysql_fetch_array($resultskills, MYSQL_ASSOC)) {
      	  $canSaveEvaluation = true;
      	  
	      if($lastcat!=$row["name"]){
	          echo '<tr bgcolor="FFCC88"><th colspan="7">&nbsp;&nbsp;'.$row["name"].'</th></tr>';
	          $lastcat=$row["name"];
	      }
	      
	      if(!empty($job_id) && $job_id > 0 && $a != "viewevaluation"){
	      	$AppUI->evaluation["items"][$i]["item_group"] = $row["name"];
	      	$AppUI->evaluation["items"][$i]["item_name"] = $row["description"];
	      	$sql = "SELECT * FROM hhrrskills, skills
	      			WHERE user_id = $id 
	      			AND idskill = ".$row["idskill"].
	      			" AND idskill = skills.id";
	      	$userSkillData = mysql_fetch_array(mysql_query($sql));
	      }
	      
	      $class = "";
	      
	      $condition1 = $row["value"] != $row["perceived_value"] && $row["perceived_value"] != 0 && (empty($job_id) || $job_id <= 0);
	      $condition2 = ($userSkillData["value"] != $row["value"] || $userSkillData["perceived_value"] != $userSkillData["value"]) && !empty($job_id) && $job_id > 0;
	      if($condition1 || $condition2){
	      	  $class = "class=\"different\"";
	      }
	      echo "<tr $class>";
	      if (!getDenyEdit("hhrr") && (empty($job_id) || $job_id <= 0) && $a != "viewevaluation"){
	      	$s = '<a href="javascript:delSkill('. $row["idskill"] .', \''. $row["name"] .'\')"><img src="images/icons/trash_small.gif" border="0" alt="'.$AppUI->_('Delete Skill').'"></a>';
	      	echo "<td>$s</td>";
	      }
      ?>
      <td nowrap>
        &nbsp;&nbsp;<?=$row["description"]?>
      </td>
      <td nowrap>
        &nbsp;&nbsp;<?if($row["valuedesc"]!=""){
        	$row["valuedesc"] .= ":";
        	echo $row["valuedesc"];
        }
        ?>
        &nbsp;&nbsp; 
		<?
		  if(!empty($job_id) && $job_id > 0){
		  	$value = $userSkillData["value"];
		  	if(empty($value)){
		  		$value = 1;
		  	}
		  }else{
		  	$value = $row["value"];
		  }
		  $items = split(",",$row["valueoptions"]);
		  $skills_text = $items;
		  if($a != "viewevaluation"){
		  	echo $items[$value-1];
		  	$AppUI->evaluation["items"][$i]["autoevaluated_value"] = $row["valuedesc"]."  ".$items[$value-1];
		  }else{
		  	echo $row["autoevaluated_value"];
		  }
		?>
      </td>
      <?php if($a != "viewrole") { ?>
      <td nowrap>
      <?php
      if(!empty($job_id) && $job_id > 0){
			$value = $userSkillData["perceived_value"];
			if(empty($value)){
		  		$value = 0;
		  	}
	  }else{
	  		$value = $row["perceived_value"];
	  }
      if($value!=0){
      	  	$item_name = $row["valuedesc"]."&nbsp;&nbsp;";
        	$items = array_merge(array("0"=>"N/E"),$items);
		    $item_name .= $items[$value];
      }else{
      	  $item_name = "N/E";
      }
      
      if($a != "viewevaluation"){
	      echo $item_name;
	      $AppUI->evaluation["items"][$i]["perceived_value"] = ereg_replace("&nbsp;"," ",$item_name);
      }else{
      	echo $row["perceived_value"];
      }
	  ?>
	  </td>
	      <?php }
	      if((!empty($job_id) && $job_id > 0) || $a=="viewevaluation") {?>
		      <td nowrap>
		      <?php
		      if($a != "viewevaluation"){
			      $item_name = "";
			      $value = $row["value"];
				  
			      if($value != 0){
			      	$item_name = $row["valuedesc"]."&nbsp;&nbsp;";
			      }
			      $item_name .= $skills_text[$value-1];
				  echo "<b>".$item_name."</b>";
				  $AppUI->evaluation["items"][$i]["job_required_value"] = ereg_replace("&nbsp;"," ",$item_name);
		      }else{
		      	  echo $row["job_required_value"];
		      }
			  ?>
		      </td>
	      <?php } ?>
      <td nowrap>
        &nbsp;<?php
	        if(!empty($job_id) && $job_id > 0) {
	        	$value = $userSkillData["lastuse"];
	        }elseif($a != "viewevaluation"){
	        	$value = $row["lastuse"];
	        }else{
	        	$value = $row["last_use"];
	        }
	        $last_use = intval( $value ) ? new CDate( $sk_lastuse ) : "";
	        $last_use = $last_use ? $last_use->format( $df ) : "" ;
	        echo $last_use;
	        
	        $AppUI->evaluation["items"][$i]["last_use"] = $userSkillData["lastuse"];
        ?>
      </td>
      <td nowrap>
        &nbsp;<?php
        	if($a != "viewevaluation"){
	        	if(!empty($job_id) && $job_id > 0) {
		        	$value = $userSkillData["monthsofexp"];
		        }else{
		        	$value = $row["monthsofexp"];
		        }
	        	if(!empty($value)){
	        		$monthsofexp = $value;
					if (($monthsofexp % 12)==0 && $monthsofexp > 0){
						$timeunit =  "12" ;
						$monthsofexp = $monthsofexp / 12;
					}else{
						$timeunit = "1";
					}
				}else{
					$monthsofexp = "";
				}
				if(!empty($monthsofexp)){
					$exp = $monthsofexp." ".$timeunits[$timeunit];
					echo $exp;
					$AppUI->evaluation["items"][$i]["experience"] = $exp;
				}
        	}else{
        		echo $row["experience"];
        	}
        ?>
      </td>
      <td nowrap>
        &nbsp;<?php
        if(!empty($job_id) && $job_id > 0) {
        	$value = $userSkillData["comment"];
        }elseif($a != "viewevaluation"){
        	$value = $row["comment"];
        }else{
        	$value = $row["user_comments"];
        }
        echo $value;
        $AppUI->evaluation["items"][$i]["user_comments"] = $value;
        ?>
      </td>
      </tr>
      <tr class="tableRowLineCell"><td colspan="7"></td></tr>

     <? 
      $i++;
      }
      ?>

<tr>
  <td colspan="7" >
  <? 
  $cant = mysql_num_rows($resultskills);
  
  if($cant =="0")
  {
	   if(empty($job_id) || $job_id <= 0) echo $AppUI->_('Noitems_matriz');
	   else echo $AppUI->_('evaluationNoResults');
  }

  ?><td>
</tr>
<tr>
  <td colspan="7">
   &nbsp;
  </td>
</tr>
<tr>
   <td colspan="7" align="center">

   <table align="right" border="0">
	<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
	</td>

<?
if($a != "viewevaluation"){
	if($a=="viewrole"){
		$edit_hrf = "index.php?m=hhrr&a=addeditrole&tab=$tab&id=".$post_id;
		$edit = true;
	}elseif (validar_permisos_hhrr($id,'matrix',-1)){
		$edit_hrf = "index.php?m=hhrr&a=addedit&tab=$tab&id=".$id;
		$edit = true;
	}
	
	if((!empty($job_id) && $job_id > 0) && $canSaveEvaluation){ ?>
		<td align="center">
			<input type="button" value="<?php echo $AppUI->_( 'save' );?>" class="button" onClick="javascript:saveEvaluation();" />
		</td>
<?php 
}
if($edit && (empty($job_id) || $job_id <= 0)){
	?>
	<td align="center">
		<input type="button" value="<?php echo $AppUI->_( 'edit' );?>" class="button" onClick="javascript:window.location='<?=$edit_hrf;?>';" />
	</td>
<? } ?>
	<td align="right">
		<?
		if($a != "viewrole" && (empty($job_id) || $job_id <= 0)){
			$tab_desp=$tab+1;
			$edit_next = "index.php?m=hhrr&a=viewhhrr&tab=$tab_desp&id=".$id;
			?>
			<!--<input type="button" value="<?php echo $AppUI->_( 'next' );?>" class="button" onClick="javascript:window.location='<?=$edit_next;?>';" />-->
		<?php } ?>
     </td>
     <?php } ?>
	</tr>
 	</table>
	</td>
</tr>
</form>
</table>
<br>

<div id="progress" name="progress" style='display:none;position:absolute;padding:0px;width:350px;height:70px;background-color: #E9E9E9; left: 40%; top: 40%; border:1px solid;'>
   <br><center><b>Cargando, por favor espere un momento...</b></center>
   <br>
   	<center><? echo dPshowImage( './images/loadinfo-4.net.gif', 24, 24, '' ); ?></center><br>
</div>

<script language="javascript">
function expandDiv(div_id, imgDivId){
	document.getElementById(div_id).style.display='';
	document.getElementById(imgDivId).innerHTML='<img id="imgCollapse" src="images/icons/collapse.gif" alt="<?=$AppUI->_("collapse")?>" border="0" onclick=\'collapseDiv("'+div_id+'","'+imgDivId+'")\'>';
}

function collapseDiv(div_id, imgDivId){
	document.getElementById(div_id).style.display='none';
	document.getElementById(imgDivId).innerHTML='<img id="imgExpand" src="images/icons/expand.gif" alt="<?=$AppUI->_("expand")?>" border="0" onclick=\'expandDiv("'+div_id+'","'+imgDivId+'")\'>';
}

function saveEvaluation(){
	if(confirm('<?php echo $AppUI->_("Are you sure you want to save those results?");?>')){
		document.getElementById('progress').style.top = getWindowPosY() + 150;
		document.getElementById('progress').innerHTML = '<br><center><b>Cargando, por favor espere un momento...</b></center><br><center><? echo dPshowImage( './images/loadinfo-4.net.gif', 24, 24, '' ); ?></center><br>';
		document.getElementById('progress').style.display = '';
		xajax_saveEvaluation('progress');
		setTimeout("closeDiv('progress')", 2*1000);
	}
}

function closeDiv(div_name){
	document.getElementById(div_name).style.display='none';
}

function getWindowPosY(){
    positionY = document.body.scrollTop;
    if (positionY < 0) {
        positionY = 0;
    }
    return positionY;
}

function compare(){
	f = document.compareForm;
	f.submit();
}
</script>

<?php
$AppUI->evaluation["count"] = count($AppUI->evaluation["items"]);

if(!empty($id) && (empty($job_id) || $job_id <= 0) && $a != "viewevaluation"){
	showModificationsHistory();
	if($a != "viewrole")
		showEvaluationsHistory();
}

function showModificationsHistory() {
	global $AppUI, $id;
	if($_GET["a"] == "viewrole"){
		$id = -1*$id;
	}
	$df = $AppUI->getPref('SHDATEFORMAT');
	
	$sql = "SELECT * FROM hhrr_skills_modifications WHERE user_skill = $id
			ORDER BY modification_id DESC";
	
	$result = mysql_query($sql) or die(mysql_error());
	
	$html = "<hr>";
	$html .= "<table width='100%' border='0' celpadding='0' cellspacing='0' align='center'>";
	$html .= "	<tr>";
	$html .= "		<th colspan='4' align='left'>".$AppUI->_('Modifications History')."</th>";
	$html .= "	</tr>";
	$html .= "</table>";
	
	if(mysql_num_rows($result) > 0){
		$i = 1;
		$color_count = 1;
		$day = "0";

		$html .= "<div id=\"imgModif\" name=\"imgModif\">";
		$html .= "	<img id='imgExpand' src='images/icons/expand.gif' alt='".$AppUI->_("expand")."' border='0' onclick='expandDiv(\"modifications\",\"imgModif\")'>";
		$html .= "</div>";
		$html .= "<div id=\"modifications\" name=\"modifications\" style=\"display:none;\" >";
		$html .= "<table width='100%' border='0' celpadding='0' cellspacing='0' align='center'>";
		$html .= "	<tr bgcolor='gray'>";
		$html .= "		<th align='left' width='20'></th>";
		$html .= "		<th align='left'>".$AppUI->_("Date")."</th>";
		$html .= "		<th align='left'>".$AppUI->_("User")."</th>";
		$html .= "		<th align='left'>".$AppUI->_("Skill")."</th>";
		$html .= "		<th align='left'>".$AppUI->_("Skill Category")."</th>";
		$html .= "		<th align='left'>".$AppUI->_("Modification")."</th>";
		$html .= "	</tr>";
		$html .= "";
	
		
		while($row = mysql_fetch_array($result)){
			$i++;
			
			$sql = "SELECT s.description, s.valueoptions, sc.name AS name FROM skills AS s
			 INNER JOIN skillcategories AS sc ON sc.id = s.idskillcategory
			 WHERE s.id = ".$row["modificated_skill"];
			$data = mysql_fetch_array(mysql_query($sql));
			
			if($row['skill_type']==0) $type_text = $AppUI->_('Autoevaluated');
			elseif($row['skill_type']==1) $type_text = $AppUI->_('Perceived');
			elseif($row['skill_type']==2) $type_text = $AppUI->_('Required');
			elseif($row['skill_type']==3) $type_text = $AppUI->_('Deleted');
			
			$date = new CDate($row["modification_date"]);
			$hour = substr($row["modification_date"],11,5);
			$day_switch = substr($row["modification_date"],8,2);
			$user_switch = $row["user_modifies"];
			
			if($day_switch != $day || $user_switch != $user){
				if($color_count % 2 == 0){
					//$bg_light = "FFEEAA";
					//$bg_dark = "FFCC88";
					$bgcolor = "bgcolor='DDDDDD'";
				}else{
					//$bg_light = "white";	//Cambio de color líneas impares
					//$bg_dark = "DDDDDD";
					$bgcolor = "bgcolor='FFEEAA'";
				}
				$color_count++;
				$day = $day_switch;
				$user = $user_switch;
			}
			
			$user_query = "SELECT CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name FROM users u WHERE user_id=".$row['user_modifies'];
			$user_result = mysql_query($user_query) or die(mysql_error());
			$user_name = mysql_fetch_array($user_result);
			
			$options = split(","," ,".$data["valueoptions"]);
			unset($options[0]);
			$options = array_merge(array("0"=>"N/E"),$options);

			$skill = $data["description"];
			if($row['skill_type'] != 3){
				$skill .= " ($type_text)";
				$action = $AppUI->_("from")." ".$options[$row["old_value"]]." ".$AppUI->_("to")." ".$options[$row["new_value"]];
			}else{
				$action = $type_text;
			}
			
			/*if($i % 2 == 0) $bgcolor="bgcolor='$bg_dark'"; //cambio de color líneas impares
			else $bgcolor="bgcolor='$bg_light'";*/
			
			$html .= "	<tr $bgcolor>";
			$html .= "		<td align='left'>";
			$html .= "		</td>";
			$html .= "		<td align='left'>".$date->format($df)." ".$hour;
			$html .= "		</td>";
			$html .= "		<td align='left'>".$user_name['user_name'];
			$html .= "		</td>";
			$html .= "		<td align='left'>".$skill;
			$html .= "		</td>";
			$html .= "		<td align='left'>".$data["name"];
			$html .= "		</td>";
			$html .= "		<td align='left'>".$action;
			$html .= "		</td>";
			$html .= "	</tr>";
			$html .= "	<tr>";
			$html .= "		<td height=1>";
			$html .= "		</td>";
			$html .= "	</tr>";
		}
	}
	else{
		$html .= "<tr>
					<td colspan=8><br>".
						$AppUI->_("No data available")."
						<br>
					</td>
				</tr>";
	}
	
	$html .= "</table>";
	$html .= "</div>";
	$html .= "<hr>";
	echo $html;
}

function showEvaluationsHistory(){
	global $AppUI, $id;
	$df = $AppUI->getPref('SHDATEFORMAT');
	
	$html .= "<table width='100%' border='0' celpadding='0' cellspacing='0' align='center'>";
	$html .= "	<tr>";
	$html .= "		<th colspan='4' align='left'>".$AppUI->_('Evaluations History')."</th>";
	$html .= "	</tr>";
	$html .= "</table>";
	
	$sql = "SELECT * FROM hhrr_skills_evaluations WHERE evaluated_user = $id
			ORDER BY evaluation_date DESC";
	
	$result = mysql_query($sql) or die(mysql_error());
	
	if(mysql_num_rows($result) > 0){
		$i = 1;

		$html .= "<div id=\"imgEval\" name=\"imgEval\">";
		$html .= "	<img id='imgExpandEval' src='images/icons/expand.gif' alt='".$AppUI->_("expand")."' border='0' onclick='expandDiv(\"evaluations\",\"imgEval\")'>";
		$html .= "</div>";
		$html .= "<div id=\"evaluations\" name=\"evaluations\" style=\"display:none;\" >";
		$html .= "<table width='100%' border='0' celpadding='0' cellspacing='0' align='center'>";
		$html .= "	<tr bgcolor='gray'>";
		$html .= "		<th align='left' width='20'></th>";
		$html .= "		<th align='left' width='30%'>".$AppUI->_("Date")."</th>";
		$html .= "		<th align='left'>".$AppUI->_("User")."</th>";
		$html .= "		<th align='left'>".$AppUI->_("Job")."</th>";
		$html .= "	</tr>";
		$html .= "";
		
		while($row = mysql_fetch_array($result)){
			$user_query = "SELECT CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name FROM users u WHERE user_id=".$row['evaluation_user'];
			$user_result = mysql_query($user_query) or die(mysql_error());
			$user_name = mysql_fetch_array($user_result);
			
			$job_query = "SELECT job_name FROM hhrr_jobs WHERE job_id=".$row['comparing_job'];
			$job_result = mysql_query($job_query) or die(mysql_error());
			$job_name = mysql_fetch_array($job_result);
			
			$date = new CDate($row["evaluation_date"]);
			$hour = substr($row["evaluation_date"],11,5);
			$link = "<a href='/index.php?m=hhrr&a=viewevaluation&id=$id&eval_id=".$row['evaluation_id']."'>";
			
			$html .= "	<tr>";
			$html .= "		<td align='left'>";
			$html .= "		</td>";
			$html .= "		<td align='left'>$link".$date->format($df)." ".$hour."</a>";
			$html .= "		</td>";
			$html .= "		<td align='left'>$link".$user_name['user_name']."</a>";
			$html .= "		</td>";
			$html .= "		<td align='left'>$link".$job_name['job_name']."</a>";
			$html .= "		</td>";
			$html .= "	</tr>";
			$html .= "	<tr>";
			$html .= "		<td height=1>";
			$html .= "		</td>";
			$html .= "	</tr>";
		}
	}else{
		$html .= "<tr>
					<td colspan=8>".
						$AppUI->_("No data available")."
						<br>
					</td>
				</tr>";		
	}
	$html .= "</table>";
	$html .= "</div>";
	$html .= "<hr>";
	
	echo $html;
}

?>