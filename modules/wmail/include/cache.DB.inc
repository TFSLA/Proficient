<?php
/////////////////////////////////////////////////////////
//	
//	include/cache.FS.inc
//
//	(C)Copyright 2003 Ryo Chijiiwa <Ryo@IlohaMail.org>
//
//	This file is part of IlohaMail, and released under GPL.
//	See COPYING, or http://www.fsf.org/copyleft/gpl.html
//
/////////////////////////////////////////////////////////
/********************************************************
	PURPOSE: Unified interface to read/write cache

********************************************************/

$EXISTING_CACHES = array();

function cache_read($user, $host, $key){
	global $DB_CACHE_TABLE;
	global $EXISTING_CACHES;
	global $session_dataID;
	
	$db = new idba_obj;
	if (!$db->connect()) return false;
	
	$data = false;
	$sql = "SELECT * FROM $DB_CACHE_TABLE WHERE owner='$session_dataID' and cache_key='$key'";
	$result = $db->query($sql);
	if (($result) && ($db->num_rows($result)>0)){
		$a = $db->fetch_row($result);
		$data = unserialize($a["cache_data"]);
		$EXISTING_CACHES[$key] = $a["id"];
	}else{
		$result = false;
		$EXISTING_CACHES[$key] = false;
	}
		
	return $data;
}

function cache_write($user, $host, $key, $data){
	global $DB_CACHE_TABLE;
	global $session_dataID;
	
	$this->db = new idba_obj;
	if (!$this->db->connect()) return false;
	
	$db = new idba_obj;
	if (!$db->connect()) return false;
	
	if (!$EXISTING_CACHES[$key]){
		$sql = "SELECT id FROM $DB_CACHE_TABLE WHERE owner='$session_dataID' and cache_key='$key'";
		$result = $db->query($sql);
		if (($result) && ($db->num_rows($result)>0)){
			$a = $db->fetch_row($result);
			$EXISTING_CACHES[$key] = $a["id"];
		}else{
			$EXISTING_CACHES[$key] = false;
		}
	}
	
	$data = serialize($data);
	if ($EXISTING_CACHES[$key]){
		$id = $EXISTING_CACHES[$key];
		$ownerID = $session_dataID;
		$sql = "update $DB_CACHE_TABLE set cache_data='$data' where id='$id' and owner='$session_dataID'";
		$result = $db->query($sql);	
	}else{
		$ownerID = $session_dataID;
		$sql = "insert into $DB_CACHE_TABLE (owner, cache_key, cache_data) ";
		$sql.= "values ('$session_dataID', '$key', '$data')";
		$result = $db->query($sql);	
	}
		
	return $result;
}

function cache_clear($user, $host, $key){
	global $DB_CACHE_TABLE;
	global $session_dataID;
	
	$this->db = new idba_obj;
	if (!$this->db->connect()) return false;
	
	$db = new idba_obj;
	if (!$db->connect()) return false;
	
	$ownerID = $dataID;
	$sql = "update $DB_CACHE_TABLE set cache_data='' where owner='$session_dataID' and cache_key=$key'";
	$result = $db->query($sql);		
}

?>