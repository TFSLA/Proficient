<?php /* DEPARTMENTS $Id: skillcategories.class.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
##
## CDepartment Class
##

class CSkillcategory {
	var $id = NULL;
	var $name = NULL;

	function CSkillcategory() {
		// empty constructor
	}

	function load( $oid ) {
		$sql = "SELECT * FROM skillcategories WHERE id = $oid";
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
			return 'skill category id is NULL';
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
			$ret = db_updateObject( 'skillcategories', $this, 'id', false );
		} else {
			$ret = db_insertObject( 'skillcategories', $this, 'id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "SELECT * FROM skills WHERE skills.idskillcategory = $this->id";

		$res = db_exec( $sql );
		if (db_num_rows( $res )) {
			return "skillcatWithSkills";
		}
		$sql = "DELETE FROM skillcategories WHERE id = $this->id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}
?>