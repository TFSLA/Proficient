<?php /* CLASSES $Id: dp.class.php,v 1.1 2009-05-19 21:15:27 pkerestezachi Exp $ */

/**
 *	@package dotproject
 *	@subpackage modules
 *	@version $Revision: 1.1 $
 */

/**
 *	CDpObject Abstract Class.
 *
 *	Parent class to all database table derived objects
 *	@author Andrew Eddie <eddieajau@users.sourceforge.net>
 *	@abstract
 */
class CDpObject {
/**
 *	@var string Name of the table in the db schema relating to child class
 */
	var $_tbl = '';
/**
 *	@var string Name of the primary key field in the table
 */
	var $_tbl_key = '';
/**
 *	@var string Error message
 */
	var $_error = '';

/**
 *	Object constructor to set table and key field
 *
 *	Can be overloaded/supplemented by the child class
 *	@param string $table name of the table in the db schema relating to child class
 *	@param string $key name of the primary key field in the table
 */
	function CDpObject( $table, $key ) {
		$this->_tbl = $table;
		$this->_tbl_key = $key;
	}
/**
 *	@return string Returns the error message
 */
	function getError() {
		return $this->_error;
	}
/**
 *	Binds a named array/hash to this object
 *
 *	can be overloaded/supplemented by the child class
 *	@param array $hash named array
 *	@return null|string	null is operation was satisfactory, otherwise returns an error
 */
	function bind( $hash ) {
		if (!is_array( $hash )) {
			$this->_error = get_class( $this )."::bind failed.";
			return false;
		} else {
			bindHashToObject( $hash, $this );
			return true;
		}
	}

/**
 *	Binds an array/hash to this object
 *	@param int $oid optional argument, if not specifed then the value of current key is used
 *	@return any result from the database operation
 */
	function load( $oid=null , $strip = true) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		$oid = $this->$k;
		if ($oid === null) {
			return false;
		}
		$sql = "SELECT * FROM $this->_tbl WHERE $this->_tbl_key=$oid";
		return db_loadObject( $sql, $this, false, $strip );
	}

/**
 *	Generic check method
 *
 *	Can be overloaded/supplemented by the child class
 *	@return null if the object is ok
 */
	function check() {
		return NULL;
	}
	
/**
*	Clone de current record
*
*	@author	handco <handco@users.sourceforge.net>
*	@return	object	The new record object or null if error
**/
	function cloneObject() {
		$_key = $this->_tbl_key;
		
		$newObj = $this;
		// blanking the primary key to ensure that's a new record
		$newObj->$_key = '';
		
		return $newObj;
	}

/**
 *	Inserts a new row if id is zero or updates an existing row in the database table
 *
 *	Can be overloaded/supplemented by the child class
 *	@return null|string null if successful otherwise returns and error message
 */
	function store( $updateNulls = false ) {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed<br />$msg";
		}
		$k = $this->_tbl_key;
		if( $this->$k ) {
			$ret = db_updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
		} else {
			$ret = db_insertObject( $this->_tbl, $this, $this->_tbl_key );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

/**
 *	Generic check for whether dependancies exist for this object in the db schema
 *
 *	Can be overloaded/supplemented by the child class
 *	@param string $msg Error message returned
 *	@param int Optional key index
 *	@param array Optional array to compiles standard joins: format [label=>'Label',name=>'table name',idfield=>'field',joinfield=>'field']
 *	@return true|false
 */
	function canDelete( &$msg, $oid=null, $joins=null ) {
		global $AppUI;
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		if (is_array( $joins )) {
			$select = "$k";
			$join = "";
			foreach( $joins as $table ) {
				$select .= ",\nCOUNT(DISTINCT {$table['idfield']}) AS {$table['idfield']}";
				$join .= "\nLEFT JOIN {$table['name']} ON {$table['joinfield']} = $k";
			}
			$sql = "SELECT $select\nFROM $this->_tbl\n$join\nWHERE $k = ".$this->$k." GROUP BY $k";

			$obj = null;
			if (!db_loadObject( $sql, $obj )) {
				$msg = db_error();
				return false;
			}
			$msg = array();
			foreach( $joins as $table ) {
				$k = $table['idfield'];
				if ($obj->$k) {
					$msg[] = $AppUI->_( $table['label'] );
				}
			}

			if (count( $msg )) {
				$msg = $AppUI->_( "noDeleteRecord" ) . ": " . implode( ', ', $msg );
				return false;
			} else {
				return true;
			}
		}

		return true;
	}

/**
 *	Default delete method
 *
 *	Can be overloaded/supplemented by the child class
 *	@return null|string null if successful otherwise returns and error message
 */
	function delete( $oid=null ) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		if (!$this->canDelete( $msg )) {
			return $msg;
		}

		$sql = "DELETE FROM $this->_tbl WHERE $this->_tbl_key = '".$this->$k."'";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}

/**
 *	Get specifically denied records from a table/module based on a user
 *	@param int User id number
 *	@return array
 */
	function getDeniedRecords( $uid ) {
		$uid = intval( $uid );
		$uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getDeniedRecords failed, user id = 0" );

		// get read denied projects
		$deny = array();
/*		$sql = "
		SELECT DISTINCT $this->_tbl_key
		FROM $this->_tbl, permissions
		WHERE permission_user = $uid AND (
			not (		permission_grant_on = 'all' 
							AND permission_item = -1 
							AND permission_value in (-1,1))
			or not(	permission_grant_on = '$this->_tbl' 
							AND permission_item = $this->_tbl_key			
							AND permission_value in (-1,1))
			)
		";*/
		$sql="
		SELECT DISTINCT Denied.$this->_tbl_key  
		FROM  $this->_tbl Denied LEFT JOIN ($this->_tbl Allowed 
						INNER JOIN permissions ON
							 	(
								(		permission_grant_on = 'all' 
									AND 	permission_item = -1 
									AND 	permission_value in (-1,1))
								or (		permission_grant_on = '$this->_tbl' 
									AND 	permission_item = Allowed.$this->_tbl_key 
									AND 	permission_value in (-1,1))
								)
						)
				on	Denied.$this->_tbl_key = Allowed.$this->_tbl_key
		WHERE		Allowed.$this->_tbl_key is null
		AND			permission_user = $uid
			";
		//echo "<pre>$sql</pre>";
		return db_loadColumn( $sql );
	}

/**
 *	Returns a list of records exposed to the user
 *	@param int User id number
 *	@param string Optional fields to be returned by the query, default is all
 *	@param string Optional sort order for the query
 *	@param string Optional name of field to index the returned array
 *	@param array Optional array of additional sql parameters (from and where supported)
 *	@return array
 */
// returns a list of records exposed to the user
	function getAllowedRecords( $uid, $fields='*', $orderby='', $index=null, $extra=null ) {
		$uid = intval( $uid );
		$uid || exit ("FATAL ERROR<br />" . get_class( $this ) . "::getAllowedRecords failed" );
		$deny = $this->getDeniedRecords( $uid );

		$sql = "SELECT $fields"
			. "\nFROM $this->_tbl, permissions";

		if (@$extra['from']) {
			$sql .= ',' . $extra['from'];
		}
		
		$sql .= "\nWHERE permission_user = $uid"
			. "\n	AND permission_value <> 0"
			. "\n	AND ("
			. "\n		(permission_grant_on = 'all')"
			. "\n		OR (permission_grant_on = '$this->_tbl' AND permission_item = -1)"
			. "\n		OR (permission_grant_on = '$this->_tbl' AND permission_item = $this->_tbl_key)"
			. "\n	)"
			. (count($deny) > 0 ? "\n\tAND $this->_tbl_key NOT IN (" . implode( ',', $deny ) . ')' : '');
		
		if (@$extra['where']) {
			$sql .= "\n\t" . $extra['where'];
		}

		$sql .= ($orderby ? "\nORDER BY $orderby" : '');

		return db_loadHashList( $sql, $index );
	}
}
?>