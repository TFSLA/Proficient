<?php
/**
* UserLog class
*/
class CUserLog extends CDpObject {
	var $user_log_id = NULL;
	var $user_log_user = NULL;
	var $user_log_date = NULL;
	var $user_log_ip = NULL;
	var $user_log_last_use = NULL;
	var $user_log_event = NULL;

	function CUserLog() {
		$this->CDpObject( 'user_logs', 'user_log_id' );
	}
	
	function check(){
		global $AppUI;

		return NULL; // object is ok
	}

}
?>