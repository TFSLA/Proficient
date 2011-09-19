<?php
##
## CArea Class
##

class CArea {
	var $id = NULL;
	var $area_parent = NULL;
	var $area_company = NULL;
	var $area_name = NULL;


	function CArea() {
		// empty constructor
	}

	function load( $oid ) {
		$sql = "SELECT * FROM hhrr_functional_area WHERE id = $oid";
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

	function check() 
	{			
		if ( $this->id == $this->area_parent && $this->id ) 
		{
		 	return 'cannot make myself my own parent';
		}
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed";
		}
		if( $this->id ) {
			$ret = db_updateObject( 'hhrr_functional_area', $this, 'id', false );
		} else {
			$ret = db_insertObject( 'hhrr_functional_area', $this, 'id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "SELECT * FROM hhrr_functional_area WHERE area_parent = $this->id";

		$res = db_exec( $sql );
		if (db_num_rows( $res )) {
			return "areaWithSub";
		}

		$sql = "DELETE FROM hhrr_functional_area WHERE id = $this->id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}
?>
