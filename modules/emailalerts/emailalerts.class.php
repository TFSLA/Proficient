<?php

require_once( $AppUI->getSystemClass ('libmail' ) );
require_once( $AppUI->getModuleClass( 'projects' ) );
require_once( $AppUI->getModuleClass( 'tasks' ) );
require_once( $AppUI->getModuleClass( 'timexp' ) );
//include_once( 'lang/alerts_' . $AppUI->user_locale . '.inc');

$alert_codes = array(
"WORKED_HOURS_EXCEED_TOTAL_HOURS"	=> "1",
"N_DAYS_TO_MILESTONE"				=> "2",
"TODAY_FINISH_MILESTONE"			=> "3",
"TODAY_IS_CONSTRAINT_DATE"			=> "4",
"N_DAYS_TO_CONSTRAINT_DATE"			=> "5",
"TODAY_IS_TARGET_END_DATE"			=> "6",
"N_DAYS_TO_TARGET_END_DATE"			=> "7",
"EXCEED_X_PERC_TARGET_BUDGET"		=> "8",
"EXCEED_TARGET_BUDGET"				=> "9",
"HHRR_BACKEND_UPDATE_REMINDER"		=> "10",
);


class CAlert extends CDpObject {
	var $alert_id              			= NULL;
	var $alert_code            			= NULL;
	var $alert_title_es        			= NULL;
	var $alert_title_en        			= NULL;
	var $alert_message_es      			= NULL;
	var $alert_message_en      			= NULL;
	var $alert_description				= NULL;

	function CAlert(){
		$this->CDpObject( 'alerts', 'alert_id' );
	}
	
	
}
class CAlertSend extends CDpObject {
	var $alert_send_id            		= NULL;
	var $alert_send_alert         		= NULL;
	var $alert_send_related_id    		= NULL;
	var $alert_send_date   				= NULL;  

	function CAlertSend(){
		$this->CDpObject( 'alert_sends', 'alert_send_id' );
	}
	
}

class CAlertRecipient extends CDpObject {
	var $alert_recipient_id                			= NULL;
	var $alert_recipient_send              			= NULL;
	var $alert_recipient_user              			= NULL;
	var $alert_recipient_error_message		  		= NULL;
	var $alert_recipient_read_date					= NULL;        


	function CAlertRecipient(){
		$this->CDpObject( 'alert_recipients', 'alert_recipient_id' );
	}
	
	function getLastSendedAlertDate($code, $user, $related_id=0){
		global $alert_codes;
		@$code_id = $alert_codes[$code];
		$sql = "select max(alert_send_date)
				from alert_recipients ar inner join alert_sends as `as`
					on ar.alert_recipient_send = as.alert_send_id
						inner join alerts a on a.alert_id = as.alert_send_alert
				where
					alert_code = '$code_id'
				and alert_recipient_user = '$user'
				and alert_send_related_id = '$related_id'";
		$date = db_loadResult($sql);
		return $date == null ? "0000-00-00 00:00:00" : $date;
	}
}
class CAlertPreference extends CDpObject {
	var $alert_recipient_id            			= NULL;
	var $alert_recipient_alert          		= NULL;
	var $alert_recipient_user          			= NULL;
	var $alert_recipient_related_id		  		= NULL;
	var $alert_recipient_status					= NULL;        

	function CAlertPreference(){
		$this->CDpObject( 'alert_preferences', 'alert_preference_id' );
	}
	
}


class EmailAlert {	
	var $title = NULL;
	var $code = NULL;
	var $language = NULL;
	var $message = NULL;
	var $parameters = NULL;
	var $is_ready = false;
	var $to = null;
	var $_messages = NULL;
	var $_alert_obj = NULL;
	var $_alert_send_obj = NULL;
	var $_alert_recipt_obj = NULL;

	function EmailAlert(){
		$this->_alert_send_obj = new CAlertSend();
		$this->_alert_send_obj->alert_send_id = '';
		$this->_alert_obj = new CAlert();
		$this->_alert_recipt_obj = new CAlertRecipient();
	}
	
	function To($user_id){
		$user = new CUser();
		if (! $user->load($user_id)){
			return "Invalid User";
		}
		$this->_alert_recipt_obj->alert_recipient_id = '';
		$this->_alert_recipt_obj->alert_recipient_user = $user_id;
		$this->to	 = $user->user_first_name." ".$user->user_last_name.
						" <".$user->user_email.">";
		$prefs = CUser::getUserPrefs($user_id);
		$this->language = isset($prefs["LOCALE"]) ? $prefs["LOCALE"] : "en";
	}
	
	function SetMessageCode($code){
		global $alert_codes;
		if (!isset($alert_codes[$code])) return "Invalid message code: '$code'";
		$this->_alert_obj  = new CAlert();
		if (!$this->_alert_obj->load($alert_codes[$code])) return "Invalid message code: '$code'";

		$this->code = $code;
	}
	
	function LoadAlert($user_id, $code, $related_id=0){
		$msg = "";
		if ($msg = $this->To($user_id)) return $msg;
		if ($msg = $this->SetMessageCode($code)) return $msg;
		if ($this->to === null) return "Recipient is not set";
		if ($this->language === null) return "Language is not set";
		if ($this->code === null) return "Message Code is not set";
		
		$this->_alert_send_obj->alert_send_related_id = $related_id;
		
		$title_field = "alert_title_".$this->language;
		$message_field = "alert_message_".$this->language;
		$this->title = $this->_alert_obj->{$title_field};
		$this->message = $this->_alert_obj->$message_field;
		
		preg_match_all("/<[^<>]+>/i", $this->title."\n".$this->message, $params);
		$params = $params[0];
		for($i = 0; $i < count($params); $i++){
			$this->parameters[$params[$i]] = NULL;
		}
		$this->is_ready = false;
	}
	
	function SetParameter($name, $value){
		$this->parameters[$name] = $value;
	}
	
	function CheckParameters(){
		if ($this->parameters === NULL) return "Parameters not loaded";
		$params = array_keys($this->parameters);
		$msg = "";
		for($i = 0; $i < count($params); $i++){
			if ($this->parameters[$params[$i]] === NULL)
				$msg .= "Parameter ".htmlentities($params[$i])." is not setted\n";
		}
		return $msg == "" ? NULL : $msg; 
	}
	
	function isLoaded(){
		return $this->title === NULL ? false : true;
	}
	
	function PrepareMessage(){
		$msg = "";
		if ($msg = $this->CheckParameters()) return $msg;
		$params = array_keys($this->parameters);
		for($i = 0; $i < count($params); $i++){
			$this->title = str_replace(	$params[$i], 
										$this->parameters[$params[$i]], 
										$this->title);
			$this->message = str_replace(	$params[$i], 
											$this->parameters[$params[$i]], 
											$this->message);
		}
			
		$this->is_ready = true;
		return NULL;
	}
	
	function Send(){
		global $dPconfig;
		if (!$this->isLoaded()) return "No message loaded";
		if (!$this->is_ready){
			$msg = "";
			if ($msg = $this->PrepareMessage()) return $msg;
		}		
		
		$today = new CDate(); 
		if ($this->_alert_send_obj->alert_send_id == ''){
			$this->_alert_send_obj->alert_send_alert = $this->_alert_obj->alert_id;
			$this->_alert_send_obj->alert_send_date = $today->format(FMT_DATETIME_MYSQL);
			$this->_alert_send_obj->store();
		}
		
		$this->_alert_recipt_obj->alert_recipient_send = $this->_alert_send_obj->alert_send_id;
		
		
		$mail = new Mail;
		$mail->To($this->to);
		$mail->From($dPconfig['mailfrom']);
		$mail->Subject($this->title);
		$mail->Body($this->message);	
		
		if ($dPconfig["debugalerts"]==true){
			$folder = $dPconfig["root_dir"]."/files/temp/".strtolower(str_replace(" ", "",$this->to));
			
			$folder = str_replace("<", "_", $folder);
			$folder = str_replace(">", "", $folder);
			if (! is_dir($folder))
				mkdir($folder, 0775);
			
			$file = $folder."/".uniqid($this->code);
			$fp = fopen($file, "w+");
			fwrite($fp, $mail->Get());
			fclose($fp);
			$this->_alert_recipt_obj->alert_recipient_status = 1;
			$this->_alert_recipt_obj->store();
			$this->_alert_recipt_obj = new CAlertRecipient();			
			return NULL;
		}else 		
			if($mail->Send()){
				$this->_alert_recipt_obj->alert_recipient_status = 1;
				$this->_alert_recipt_obj->store();
				$this->_alert_recipt_obj = new CAlertRecipient();
				return NULL;
			}else{ 
				$this->_alert_recipt_obj->alert_recipient_status = 0;
				$this->_alert_recipt_obj->store();	
				$this->_alert_recipt_obj = new CAlertRecipient();		
				return "Problems sending mail";			
			}
			
	}
	
}






class Notifier {


	function ProjectOverWorked(	$users, 
								$project_id, 
								$project_name, 
								$project_start, 
								$project_end, 
								$project_status, 
								$project_percent_complete, 
								$total_hours, 
								$worked_hours){
		global $dPconfig, $m, $AppUI;
			$m = "projects";
			include_once( "locales/core.php" );	
			
		$project = new CProject();
		$project->load($project_id);
					
		$msg_gral ='';
		$alert = new EmailAlert();
		for($i = 0; $i < count($users); $i++){
			
			$prefs = CUser::getUserPrefs($users[$i]);
			$date_format = isset($date_format) ? $date_format : "%d/%m/%Y";

			$st = intval( $project_start ) ? new CDate( $project_start ) : null;
			$ed = intval( $project_end ) ? new CDate( $project_end ) : null;
		
			$url = $dPconfig["base_url"].
				"/index.php?m=projects&a=view&project_id=".$project_id;
			
			$last = CAlertRecipient::getLastSendedAlertDate(
								"WORKED_HOURS_EXCEED_TOTAL_HOURS",	$users[$i], $project_id); 
				
				
			// si la alerta ya fue enviada y no ha cambiado el registro omito el envio
			if ($last > $project->project_total_hours_update)
				continue;
							
			
			$alert->LoadAlert($users[$i], "WORKED_HOURS_EXCEED_TOTAL_HOURS", $project_id);
			$alert->SetParameter("<project_name>", $project_name);
			$alert->SetParameter("<view_project_url>", $url);
			$alert->SetParameter("<project_id>", $project_id);
			$alert->SetParameter("<project_start_date>", $st?$st->format($date_format):'-');
			$alert->SetParameter("<project_end_date>", $ed?$ed->format($date_format):'-');
			$alert->SetParameter("<project_status>", $project_status);
			$alert->SetParameter("<project_percent_complete>", 
									round($project_percent_complete, 2));
			$alert->SetParameter("<total_hours>", $total_hours);
			$alert->SetParameter("<worked_hours>", $worked_hours);
			$msg = '';
			if($msg = $alert->Send()){
				$msg_gral .= $msg."\n";
			}
		}
		if ($msg_gral != "")
			return $msg_gral;
			
		return null;

	}	

	

	function MilestonesEnding(){
		global $dPconfig, $m, $AppUI;
			$m = "tasks";
			include_once( "locales/core.php" );		
		$n = $dPconfig["n_days_milestone_ending"];
		$n = $n ? $n : 5;
		
		$sql = "select project_id, $n days, project_owner from projects";
		$projects = db_loadList($sql);
		$msg_gral ='';
		for($j=0; $j < count($projects); $j++){
			extract($projects[$j]);
			$owners[$project_owner] = '';
			$owners = CProject::getOwners($project_id);
			
			$sql = 	"
	select 	task_id, 1 type from tasks where task_milestone = 1 AND task_project ='$project_id' AND
		DATE_FORMAT(CURDATE(), '%Y-%m-%d') = DATE_FORMAT(task_end_date, '%Y-%m-%d')
	union
	select 	task_id, 2 type from tasks where task_milestone = 1 AND task_project ='$project_id' AND
		DATE_FORMAT(CURDATE(), '%Y-%m-%d') = DATE_FORMAT(date_sub(task_end_date, INTERVAL $days DAY), '%Y-%m-%d') 
			";
			
			$tasks = db_loadList($sql);	
			
			for($i=0; $i < count($tasks); $i++){
				extract($tasks[$i]);
				$task_obj = new CTask();
				$task_obj->load($task_id);

				$users = $task_obj->getAssignedUsers();
				$assigned_users = array_keys($users);
				$recips = array();
				$users_list = "";
				for($h=0; $h < count($users); $h++){
					$user_id = $assigned_users[$h];
					$user_caption = str_repeat(" ", 8);
					$user_caption .= sprintf("%-40s", 	$users[$user_id]["user_first_name"]." ".
														$users[$user_id]["user_last_name"] );
					//$hours = CTimexp::getTaskWorkedHours($task_id, $user_id);
					//$user_caption .= sprintf("%01.2f", $hours);
					$users_list .= $user_caption."\n";
				}
				$assigned_users = $task_obj->task_owner;
				$recips = array_merge(array_keys($owners), $assigned_users);
				$recips = array_unique($recips);
				$alert = new EmailAlert();
				
				for($k = 0; $k < count($recips); $k++){
					$user_id = $recips[$k];
					
					$prefs = CUser::getUserPrefs($user_id);
					$date_format = $prefs['SHDATEFORMAT'];
					$date_format = isset($date_format) ? $date_format : "%d/%m/%Y";
		
					$st = intval( $task_obj->task_start_date ) ? 
									new CDate( $task_obj->task_start_date ) : null;
					$ed = intval( $task_obj->task_end_date ) ? 
									new CDate( $task_obj->task_end_date ) : null;
				
					$url = $dPconfig["base_url"].
						"/index.php?m=tasks&a=view&task_id=".$task_id;
					
					
					if ($type == 1)
						$alert->LoadAlert($user_id, "TODAY_FINISH_MILESTONE", $task_id);
					else if ($type == 2){
						$alert->LoadAlert($user_id, "N_DAYS_TO_MILESTONE", $task_id);
						$alert->SetParameter("<n>", $days);
					}
					
					$alert->SetParameter("<task_name>", $task_obj->task_name);
					$alert->SetParameter("<view_task_url>", $url);
					$alert->SetParameter("<task_id>", $task_id);
					$alert->SetParameter("<task_start_date>", $st?$st->format($date_format):'-');
					$alert->SetParameter("<task_end_date>", $ed?$ed->format($date_format):'-');
					$task_owner = new CUser();
					$task_owner->load($user_id);
					$alert->SetParameter("<task_owner>", $task_owner->user_first_name." ".$task_owner->user_last_name);
					$alert->SetParameter("<task_manual_percent_complete>", $task_obj->task_manual_percent_complete);
					$alert->SetParameter("<task_users>", $users_list);
					
					$msg = '';
					if($msg = $alert->Send()){
						$msg_gral .= $msg."\n";
					}
				}

			}	
		
		}
		if ($msg_gral != "")
			return $msg_gral;
			
		return null;
	}

	
	
	function TaskConstraintDate(){
		global $dPconfig, $task_constraints, $AppUI, $m;
			$m = "tasks";
			include_once( "locales/core.php" );
		
		
		
		$n = $dPconfig["n_days_constraint_date"];
		$n = $n ? $n : 5;
		
		$sql = "select project_id, $n days, project_owner from projects";
		$projects = db_loadList($sql);
		$msg_gral ='';
		for($j=0; $j < count($projects); $j++){
			extract($projects[$j]);
			$owners[$project_owner] = '';
			$owners = CProject::getOwners($project_id);
			
			$sql = 	"
	select 	task_id, 1 type from tasks where task_project ='$project_id' AND
		DATE_FORMAT(CURDATE(), '%Y-%m-%d') = DATE_FORMAT(task_constraint_date, '%Y-%m-%d')
	union
	select 	task_id, 2 type from tasks where task_project ='$project_id' AND
		DATE_FORMAT(CURDATE(), '%Y-%m-%d') = DATE_FORMAT(date_sub(task_constraint_date, INTERVAL $days DAY), '%Y-%m-%d') 
			";
			
			$tasks = db_loadList($sql);	
			
			for($i=0; $i < count($tasks); $i++){
				extract($tasks[$i]);
				$task_obj = new CTask();
				$task_obj->load($task_id);

				$users = $task_obj->getAssignedUsers();
				$assigned_users = array_keys($users);
				$recips = array();
				$users_list = "";
				for($h=0; $h < count($users); $h++){
					$user_id = $assigned_users[$h];
					$user_caption = str_repeat(" ", 8);
					$user_caption .= sprintf("%-40s", 	$users[$user_id]["user_first_name"]." ".
														$users[$user_id]["user_last_name"] );
					//$hours = CTimexp::getTaskWorkedHours($task_id, $user_id);
					//$user_caption .= sprintf("%01.2f", $hours);
					$users_list .= $user_caption."\n";
				}
				$assigned_users = $task_obj->task_owner;
				$recips = array_merge(array_keys($owners), $assigned_users);
				$recips = array_unique($recips);
				$alert = new EmailAlert();
				
				for($k = 0; $k < count($recips); $k++){
					$user_id = $recips[$k];
					
					$prefs = CUser::getUserPrefs($user_id);
					$date_format = $prefs['SHDATEFORMAT'];
					$user_locale = $prefs['LOCALE'] ? $prefs['LOCALE'] : "en";
					$date_format = isset($date_format) ? $date_format : "%d/%m/%Y";
		
					$st = intval( $task_obj->task_start_date ) ? 
									new CDate( $task_obj->task_start_date ) : null;
					$ed = intval( $task_obj->task_end_date ) ? 
									new CDate( $task_obj->task_end_date ) : null;
					$constraint_date = intval( $task_obj->task_constraint_date ) ?
									new CDate($task_obj->task_constraint_date) : null;
				
					$url = $dPconfig["base_url"].
						"/index.php?m=tasks&a=view&task_id=".$task_id;
					
					
					if ($type == 1)
						$alert->LoadAlert($user_id, "TODAY_IS_CONSTRAINT_DATE", $task_id);
					else if ($type == 2){
						$alert->LoadAlert($user_id, "N_DAYS_TO_CONSTRAINT_DATE", $task_id);
						$alert->SetParameter("<n>", $days);
					}
						
					
					$alert->SetParameter("<task_name>", $task_obj->task_name);
					$alert->SetParameter("<view_task_url>", $url);
					$alert->SetParameter("<task_id>", $task_id);
					$alert->SetParameter("<task_start_date>", $st?$st->format($date_format):'-');
					$alert->SetParameter("<task_end_date>", $ed?$ed->format($date_format):'-');
					$task_owner = new CUser();
					$task_owner->load($user_id);
					$alert->SetParameter("<task_owner>", $task_owner->user_first_name." ".$task_owner->user_last_name);
					$alert->SetParameter("<task_manual_percent_complete>", $task_obj->task_manual_percent_complete);
					$alert->SetParameter("<task_constraint_type>",
										$AppUI->_to($user_locale,
													$task_constraints[
														$task_obj->task_constraint_type]));
					$alert->SetParameter("<task_constraint_date>",
										$constraint_date?$constraint_date->format($date_format):'-');
					$alert->SetParameter("<task_users>", $users_list);
					
					
					
					
					
					$msg = '';
					if($msg = $alert->Send()){
						$msg_gral .= $msg."\n";
					}
				}

			}	
		
		}
		if ($msg_gral != "")
			return $msg_gral;
			
		return null;
	}	
	
	
	
	function ProjectTargetEndDate(){
		global $dPconfig, $task_constraints, $AppUI, $m,$pstatus;
			$m = "projects";
			include_once( "locales/core.php" );
		
		
		
		$n = $dPconfig["n_days_target_end_date"];
		$n = $n ? $n : 5;
		
		$sql = "
	select 	project_id, 1 type, $n days  from projects where 
		DATE_FORMAT(CURDATE(), '%Y-%m-%d') = DATE_FORMAT(project_end_date, '%Y-%m-%d')
	union
	select 	project_id, 2 type,  $n days  from projects where 
		DATE_FORMAT(CURDATE(), '%Y-%m-%d') = DATE_FORMAT(date_sub(project_end_date , INTERVAL $n DAY), '%Y-%m-%d') 
		";
		$projects = db_loadList($sql);
		$msg_gral ='';
		for($j=0; $j < count($projects); $j++){
			extract($projects[$j]);

			$project = new CProject();
			$project->load($project_id);
			$owners[$project->project_owner] = '';
			$owners = CProject::getOwners($project_id);
			
			$recips = array_keys($owners);
			$alert = new EmailAlert();
			
			for($k = 0; $k < count($recips); $k++){
				$user_id = $recips[$k];
				
				$prefs = CUser::getUserPrefs($user_id);
				$date_format = $prefs['SHDATEFORMAT'];
				$user_locale = $prefs['LOCALE'] ? $prefs['LOCALE'] : "en";
				$date_format = isset($date_format) ? $date_format : "%d/%m/%Y";
	
				$st = intval( $project->project_start_date ) ? 
								new CDate( $project->project_start_date ) : null;
				$ed = intval( $project->project_end_date ) ? 
								new CDate( $project->project_end_date ) : null;
			
				$url = $dPconfig["base_url"].
					"/index.php?m=projects&a=view&project_id=".$project_id;
				
				
				if ($type == 1)
					$alert->LoadAlert($user_id, "TODAY_IS_TARGET_END_DATE", $project_id);
				else if ($type == 2){
					$alert->LoadAlert($user_id, "N_DAYS_TO_TARGET_END_DATE", $project_id);
					$alert->SetParameter("<n>", $days);
				}
					
				
				$alert->SetParameter("<project_name>", $project->project_name);
				$alert->SetParameter("<view_project_url>", $url);
				$alert->SetParameter("<project_id>", $project->project_id);
				$alert->SetParameter("<project_start_date>", $st?$st->format($date_format):'-');
				$alert->SetParameter("<project_end_date>", $ed?$ed->format($date_format):'-');


				$alert->SetParameter("<project_status>", 
										$AppUI->_to( $user_locale, 
													$pstatus[$project->project_status]));
				$alert->SetParameter("<project_percent_complete>", 
										round($project->project_percent_complete, 2));
				$alert->SetParameter("<total_hours>", 
									round(CProject::getTotalHours($project_id), 2));
				$alert->SetParameter("<worked_hours>", 
									round(CProject::getWorkedHours($project_id), 2));
											
				$msg = '';
				if($msg = $alert->Send()){
					$msg_gral .= $msg."\n";
				}
			}
		
		}
		if ($msg_gral != "")
			return $msg_gral;
			
		return null;
	}		
	
	function ProjectTargetBudgetExceeded(){
		global $dPconfig, $task_constraints, $AppUI, $m,$pstatus;
			$m = "projects";
			include_once( "locales/core.php" );
		
		
		
		$n = $dPconfig["x_perc_target_budget_exceeded"];
		$n = floatval( $n ? $n : 90 );
		
		$sql = "
	select 	project_id, 1 type, $n percent, project_target_budget_update  
		from projects where 
		project_target_budget < project_actual_budget
	union
	select 	project_id, 2 type,  $n percent, project_target_budget_update  
		from projects where 
		(project_target_budget * $n / 100) < project_actual_budget
	and	project_target_budget >= project_actual_budget
		";
		$projects = db_loadList($sql);
		$msg_gral ='';
		for($j=0; $j < count($projects); $j++){
			extract($projects[$j]);

			$project = new CProject();
			$project->load($project_id);
			$last_update = intval( $project_target_budget_update ) ? 
							new CDate( $project_target_budget_update ) : null;			
							
			$owners[$project->project_owner] = '';
			$owners = CProject::getOwners($project_id);
			
			$recips = array_keys($owners);
			$alert = new EmailAlert();
			
			for($k = 0; $k < count($recips); $k++){
				$user_id = $recips[$k];
				
				$prefs = CUser::getUserPrefs($user_id);
				$date_format = $prefs['SHDATEFORMAT'];
				$user_locale = $prefs['LOCALE'] ? $prefs['LOCALE'] : "en";
				$date_format = isset($date_format) ? $date_format : "%d/%m/%Y";
	
				$st = intval( $project->project_start_date ) ? 
								new CDate( $project->project_start_date ) : null;
				$ed = intval( $project->project_end_date ) ? 
								new CDate( $project->project_end_date ) : null;
			
				$url = $dPconfig["base_url"].
					"/index.php?m=projects&a=view&project_id=".$project_id;
				
				if ($type == 1){
					$last = CAlertRecipient::getLastSendedAlertDate(
								"EXCEED_TARGET_BUDGET",	$user_id, $project_id); 
				}else if ($type == 2){
					$last = CAlertRecipient::getLastSendedAlertDate(
								"EXCEED_X_PERC_TARGET_BUDGET",	$user_id, $project_id); 
				}				
				
				// si la alerta ya fue enviada y no ha cambiado el registro omito el envio
				if ($last > $project_target_budget_update)
					continue;
				
				if ($type == 1){
					$alert->LoadAlert($user_id, "EXCEED_TARGET_BUDGET", $project_id);			
				}else if ($type == 2){
					$alert->LoadAlert($user_id, "EXCEED_X_PERC_TARGET_BUDGET", $project_id);
					$alert->SetParameter("<x>", $n * 100);					
				}
					
				
				$alert->SetParameter("<project_name>", $project->project_name);
				$alert->SetParameter("<view_project_url>", $url);
				$alert->SetParameter("<project_id>", $project->project_id);
				$alert->SetParameter("<project_start_date>", $st?$st->format($date_format):'-');
				$alert->SetParameter("<project_end_date>", $ed?$ed->format($date_format):'-');
				$alert->SetParameter("<currency_symbol>", $dPconfig['currency_symbol']);
				$alert->SetParameter("<project_status>", 
										$AppUI->_to( $user_locale, 
													$pstatus[$project->project_status]));
				$alert->SetParameter("<project_percent_complete>", 
										round($project->project_percent_complete, 2));
				$alert->SetParameter("<project_target_budget>", 
									round($project->project_target_budget, 2));
									
				$tbud = floatval($project->project_target_budget);
				$abud = floatval($project->project_actual_budget);
				$actual_str = round($abud, 2);
				if ($tbud != 0){
					$actual_str .= " (".round(100 * $abud / $tbud, 2). "%)";
				}
													
				$alert->SetParameter("<project_actual_budget>", 
									$actual_str);
				$alert->SetParameter("<total_hours>", 
									round(CProject::getTotalHours($project_id), 2));
				$alert->SetParameter("<worked_hours>", 
									round(CProject::getWorkedHours($project_id), 2));											
				$msg = '';
				if($msg = $alert->Send()){
					$msg_gral .= $msg."\n";
				}
			}
		
		}
		if ($msg_gral != "")
			return $msg_gral;
			
		return null;
	}			


	
	
	
	function HhrrBackendUsersWithOldData(){
		global $dPconfig, $task_constraints, $AppUI, $m,$pstatus;
			$m = "admin";
			include_once( "locales/core.php" );
		
		
		
		$n = $dPconfig["x_days_age_data_hhrr"];
		$n = floatval( $n ? $n : 365 );
		$n_resend = $dPconfig["x_days_resend_age_data_hhrr"];
		$n_resend = floatval( $n_resend ? $n_resend : 365 );
		
		$sql = "
SELECT distinct u.user_id, user_username, hhrr_password, date_created, date_updated, last_visit,
date_add(DATE_UPDATED, INTERVAL $n DAY)
FROM `users` u left join hhrrskills hs on u.user_id = hs.user_id
WHERE user_type =5
AND (date_add(DATE_UPDATED, INTERVAL $n DAY) < curdate()
or (user_country = '' and user_country_id = 0)
or resume in ('', 'ninguna')
or hs.id is null
)
		";
	
		$user_list = db_loadList($sql);
		$msg_gral ='';
		$sends_counter = 0;
		for($j=0; $j < count($user_list); $j++){
			extract($user_list[$j]);
			$flag_send = false;
			$user = new CUser();
			$user->load($user_id);
			$last_update = intval( $date_updated ) ? 
							new CDate( $date_updated ) : null;			
							
			
			$alert = new EmailAlert();
		
			$prefs = CUser::getUserPrefs($user_id);
			$date_format = $prefs['SHDATEFORMAT'];
			$user_locale = $prefs['LOCALE'] ? $prefs['LOCALE'] : "es";
			$date_format = isset($date_format) ? $date_format : "%d/%m/%Y";

			
			$last = CAlertRecipient::getLastSendedAlertDate( 
							"HHRR_BACKEND_UPDATE_REMINDER",	$user_id, $user_id); 
							
			/* @var $last_update CDate */				
			$last_update->addDays($n);		
			$today = new CDate();
			
			$last_send = intval( $last ) ? 
							new CDate( $last ) : null;	

			// si ya se le envi�una alerta
			if($last_send!==null){
				// obtengo la fecha de envio de la proxima alerta
				$last_send->addDays($n_resend);
				
				// si la fecha del proximo envio es menor o igual a hoy se le envia
				if($today->format(FMT_DATETIME_MYSQL) >=
					$last_send->format(FMT_DATETIME_MYSQL)){
					$flag_send = true;
				}
			}else{
				$flag_send = true;
			}
		
			if($flag_send){
				$alert->LoadAlert($user_id, "HHRR_BACKEND_UPDATE_REMINDER",$user_id);
				$alert->SetParameter("<user_username>", $user->user_username);
				$alert->SetParameter("<user_password>", $user->hhrr_password);
											
				$msg = '';
				if($msg = $alert->Send()){
					$msg_gral .= $msg."\n";
				} else{
					$sends_counter++;
				}			
			
			
			}
			
		}
		if ($msg_gral != "")
			return $msg_gral;
			
		//return $sends_counter;
		return NULL;
	}	
	



}

?>