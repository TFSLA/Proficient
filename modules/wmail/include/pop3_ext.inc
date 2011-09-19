<?php

class pop3Ext_Message{
	var $id = NULL;
	var $seen = null;
	var $deleted = null;
	var $recent = null;
	var $answered = null;
	var $draft = null;
}


class pop3Ext {
	var $conn = null;
	var $messages = null;
	var $_status_file = null;
	
	
	function pop3Ext(&$conn){
		global $CACHE_DIR;
		$cacheDir = $CACHE_DIR;
		if (empty($cacheDir)) $cacheDir = "../cache/";		
		$this->conn = $conn;
		$this->_status_file = $cacheDir.$conn->login.".".$conn->host."/mailbox.info";

		$this->loadStatus();	
	}
	function loadStatus(){
		$mb = array();
		$mb_ = array();		
		if (file_exists(realpath($this->_status_file))){
			$mb_temp = file_get_contents(realpath($this->_status_file));
			$mb_temp = explode("\n", $mb_temp);
			for($i=0;$i < count($mb_temp); $i++){
				$tmp = array();
				$tmp = explode(";", $mb_temp[$i]);
				if (isset($tmp[0])){
					$this->addMessage($tmp[0],@$tmp[1],@$tmp[2],@$tmp[3],@$tmp[4],@$tmp[5]);
				}
			}
		}else{
			$this->messages = array();
		}			
	}
	
	function addMessage($id, $seen='', $deleted='', $recent='', $answered='', $draft=''){
		$msg = new pop3Ext_Message();
		
		$msg->seen = $seen;
		$msg->deleted = $deleted;
		$msg->recent = $recent;
		$msg->answered = $answered;
		$msg->draft = $draft;
		$this->messages[$id] = $msg; 
	}
	function setStatus($id, $name, $value){
		
		//if (isset($this->messages[$id])){
			switch ($name){
			case "SEEN":
				$this->messages[$id]->seen = $value;break;
			case "DELETED":
				$this->messages[$id]->deleted = $value;break;
			case "RECENT":
				$this->messages[$id]->recent = $value;break;
			case "ANSWERED":
				$this->messages[$id]->answered = $value;break;
			case "DRAFT":
				$this->messages[$id]->draft = $value;break;
			}
		//}
	}
	function getStatus($id, $name){
		
		if (isset($this->messages[$id])){
			switch ($name){
			case "SEEN":
				return $this->messages[$id]->seen;
			case "DELETED":
				return $this->messages[$id]->deleted;
			case "RECENT":
				return $this->messages[$id]->recent;
			case "ANSWERED":
				return $this->messages[$id]->answered;
			case "DRAFT":
				return $this->messages[$id]->draft;
			}
		}
	}	
	function setSeen($id, $value){
		if (isset($this->messages[$id])){
			$this->messages[$id]->seen = $value;
		}
	}
	function setDeleted($id, $value){
		if (isset($this->messages[$id])){
			$this->messages[$id]->deleted = $value;
		}
	}
		
	function setRecent($id, $value){
		if (isset($this->messages[$id])){
			$this->messages[$id]->recent = $value;
		}
	}	
	function setAnswered($id, $value){
		if (isset($this->messages[$id])){
			$this->messages[$id]->answered = $value;
		}
	}
	function setDraft($id, $value){
		if (isset($this->messages[$id])){
			$this->messages[$id]->draft = $value;
		}
	}

	function storeStatus(){
		if (file_exists(realpath($this->_status_file)))
			unlink($this->_status_file);
	echo "<!-- POP3_Ext FILE: '".$this->_status_file."' -->";
		$fp = fopen($this->_status_file, "w");	

		$lines = "";
		if (is_array($this->messages) && count($this->messages)>0){
			foreach ($this->messages as $id => $values){
				$lines .= "$id;".
						$this->messages[$id]->seen.";".
						$this->messages[$id]->deleted.";".
						$this->messages[$id]->recent.";".
						$this->messages[$id]->answered.";".
						$this->messages[$id]->draft;
				$lines .= "\n";
			}
		}
		@fwrite($fp, $lines);
		@fclose($fp);

	}	
}

?>