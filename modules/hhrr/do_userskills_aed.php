<?php 
global $hhrr_portal;
$del = isset($_POST['del']) ? $_POST['del'] : 0;
$user_id = $_POST["user_id"];
if($_GET["a"] == "addeditrole"){
	$user_id = -1*$user_id;
}

$canEditHHRR = !getDenyEdit("hhrr") || $user_id == $AppUI->user_id;
if (!$canEditHHRR){
	 $AppUI->redirect( "m=public&a=access_denied" );
}

//echo "<pre>";
//var_dump($_POST);
//echo "</pre>";

//echo "<pre>";
$date = date("Y-m-d H:i:s");
$msg = "";

if ($del){
	$idskill = $_POST["idskill"];
	if($_GET["a"]=="viewrole"){
		$user_id = -1*$user_id;
	}
	$sql = "delete from hhrrskills WHERE `idskill` = '$idskill' AND user_id ='$user_id'";
//echo $sql."\n";
	if (!db_exec($sql)){
		$msg .= db_error()."<br />";
	}

	if ($msg==""){
		$AppUI->setMsg( $AppUI->_('User Skills') , UI_MSG_OK, true );
		$AppUI->setMsg( $AppUI->_("deleted"), UI_MSG_ALERT, true );
		
		$sql = "INSERT INTO hhrr_skills_modifications
				(user_skill, user_modifies, modification_date, modificated_skill, skill_type)
				VALUES
				($user_id, $AppUI->user_id, '$date', $idskill, 3)";
		if (!db_exec($sql)){
			$msg .= db_error()."<br />";
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
		}
	}else{
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	}
}else{
	
	for($i=0; $i< count($_POST["idskill"]); $i++){
		$idskill = $_POST["idskill"][$i];
		$value = $_POST["value$idskill"];
		$perceivedValue = $_POST["perceived_value$idskill"];
		$lastuse = $_POST["lastuse$idskill"];
		
		if (trim($lastuse)!=""){
			$date = new CDate($lastuse);
			$lastuse = $date->format(FMT_DATETIME_MYSQL);
		}else{
			$lastuse="0000-00-00";
		}
		
		$monthsofexp = floatval($_POST["monthsofexp$idskill"]) * intval($_POST["timeunit$idskill"]);
		$comment = $_POST["comment$idskill"];
		
		$sql = "SELECT * FROM hhrrskills WHERE idskill = $idskill AND user_id = $user_id";
		$data = mysql_fetch_array(mysql_query($sql));
		
		$value_modified = ( $value != $data["value"] && !empty($data["value"]) ) || (empty($data["value"]) && $value != 1);
		$perceivedValue_modified = ( $perceivedValue != $data["perceived_value"]  && !empty($data["perceived_value"]) ) || (empty($data["perceived_value"]) && $perceivedValue != 0);
		
		if(($value_modified || $perceivedValue_modified) || $comment != "" ){
			//echo "'$user_id', '$idskill', '$value', '$comment', '$lastuse', '$monthsofexp' \n";
			if (!in_array($value,array("1","")) || $comment!="" || !in_array($perceivedValue,array("0","")) || $value != $data["value"] || $perceivedValue != $data["perceived_value"]){
				$sql = "select count(*) from hhrrskills WHERE `idskill` = '$idskill' AND user_id ='$user_id'";
				if (db_loadResult($sql)>0){
					$sql = "
					UPDATE `hhrrskills` 
					SET `comment` = '$comment'
					, `lastuse` = '$lastuse'
					, `value` = '$value'
					, `perceived_value` = '$perceivedValue' 
					, `monthsofexp` = '$monthsofexp' 
					WHERE `idskill` = '$idskill' 
					AND user_id ='$user_id'
					";				
				}else{
					$sql = "
						REPLACE INTO hhrrskills ( `id` , `user_id` , `idskill` , `value` , `perceived_value` ,  `comment` , `lastuse` , `monthsofexp` )
						values( '', '$user_id', '$idskill', '$value', '$perceivedValue', '$comment', '$lastuse', '$monthsofexp');";						
				}
				//echo $sql."\n";
		    	//$resultupdate = mysql_query($sql);
			}else{
				$sql = "delete from hhrrskills WHERE `idskill` = '$idskill' AND user_id ='$user_id'";
			}
			
			if (!db_exec($sql)){
				$msg .= db_error()."<br />";
			}
			
			// Preparo la fecha actual
		          $date_today = new CDate();
		          $today = $date_today->format(FMT_DATETIME_MYSQL);
			
			$sql = "INSERT INTO hhrr_skills_modifications
				(user_skill, user_modifies, modification_date, modificated_skill, old_value, new_value, skill_type)
				VALUES
				($user_id, $AppUI->user_id, '$today', $idskill, ";
			
			
			if($value_modified){
				
				$error = null;
				if(empty($data["value"])) $data["value"] = 1;
				if($_GET["a"] == "addeditrole") $skill_value = 2;
				else $skill_value = 0;
				$query = $sql.$data["value"].", $value, $skill_value )";
				//echo "<pre>".$sql."</pre>";
				
				mysql_query($query);
				$error = mysql_error();
				if ($error){
					$msg .= $error."<br />";
					$AppUI->setMsg( $msg, UI_MSG_ERROR );
				}
			}
			
			if($perceivedValue_modified && !$hhrr_portal){
				
				$error = null;
				if(empty($data["perceived_value"])) $data["perceived_value"] = "0";
				$query = $sql.$data["perceived_value"].", $perceivedValue, 1 )";
				
				//echo "<pre>".$sql."</pre>";
				mysql_query($query);
				$error = mysql_error();
				if ($error){
					$msg .= $error."<br />";
					$AppUI->setMsg( $msg, UI_MSG_ERROR );
				}
			}
		}
	}
	
	if ($msg==""){
		$AppUI->setMsg( 'User Skills' , UI_MSG_OK, true );
		$AppUI->setMsg( 'updated' , UI_MSG_OK, true );
	}else{
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	}
}
/*
if($msg== "" && $user_id){
	//update 'users.date_updated' field
	$userTmp = new CUser();
	
	$userTmp->load($user_id);
	$userTmp->date_updated = date("Y-m-d");
	
	$userTmp->store();
}*/

//echo "</pre>";

?>