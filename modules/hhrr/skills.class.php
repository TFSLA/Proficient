<?php /* DEPARTMENTS $Id: skills.class.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
##
## CDepartment Class
##

class CSkill {
	var $id              = NULL;
	var $description     = NULL;
	var $valuedesc       = NULL;
	var $valueoptions    = NULL;
	var $idskillcategory = NULL;
	var $hidemonthsofexp = NULL;
	var $hidelastuse     = NULL;

	function CSkill() {
		// empty constructor
	}

	function load( $oid ) {
		$sql = "SELECT * FROM skills WHERE id = $oid";
		return db_loadObject( $sql, $this );
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		if ($this->id == NULL) {
			return 'skill id is NULL';
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed ";
		}
		if( $this->id ) {
			$ret = db_updateObject( 'skills', $this, 'id', false );
		} else {
			$ret = db_insertObject( 'skills', $this, 'id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "SELECT * FROM hhrrskills WHERE idskill = $this->id";

		$res = db_exec( $sql );
		if (db_num_rows( $res )) {
			return "skillWithSkillMatrix";
		}
		$sql = "DELETE FROM skills WHERE id = $this->id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}
?>