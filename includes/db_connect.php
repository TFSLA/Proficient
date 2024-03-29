<?php /* INCLUDES $Id: db_connect.php,v 1.1 2009-05-19 21:15:30 pkerestezachi Exp $ */
/**
* Generic functions based on library function (that is, non-db specific)
*
* @todo Encapsulate into a database object
*/

// load the db specific handlers
//require_once( "{$AppUI->cfg['root_dir']}/includes/db_{$AppUI->cfg['dbtype']}.php" );

// make the connection to the db
db_connect( $AppUI->cfg['dbhost'], $AppUI->cfg['dbname'],
	$AppUI->cfg['dbuser'], $AppUI->cfg['dbpass'], null, $AppUI->cfg['dbpersist'] );

/**
* This global function loads the first field of the first row returned by the query.
*
* @param string The SQL query
* @return The value returned in the query or null if the query failed.
*/
function db_loadResult( $sql ) {
	$cur = db_exec( $sql );
	$cur or exit( db_error() );
	$ret = null;
	if ($row = db_fetch_row( $cur )) {
		$ret = $row[0];
	}
	db_free_result( $cur );
	return $ret;
}

/**
* This global function loads the first row of a query into an object
*
* If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
* If <var>object</var> has a value of null, then all of the returned query fields returned in the object. 
* @param string The SQL query
* @param object The address of variable
*/
function db_loadObject( $sql, &$object, $bindAll=false ) {
	if ($object != null) {
		$hash = array();
		if( !db_loadHash( $sql, $hash ) ) {
			return false;
		}
		bindHashToObject( $hash, $object, null, true, $bindAll );
		return true;
	} else {
		$cur = db_exec( $sql );
		$cur or exit( db_error() );
		if ($object = db_fetch_object( $cur )) {
			db_free_result( $cur );
			return true;
		} else {
			$object = null;
			return false;
		}
	}
}

/**
* This global function return a result row as an associative array 
*
* @param string The SQL query
* @param array An array for the result to be return in
* @return <b>True</b> is the query was successful, <b>False</b> otherwise
*/
function db_loadHash( $sql, &$hash ) {
	$cur = db_exec( $sql );
	$cur or exit( db_error() );
	$hash = db_fetch_assoc( $cur );
	db_free_result( $cur );
	if ($hash == false) {
		return false;
	} else {
		return true;
	}
}

/**
* Document::db_loadHashList()
*
* { Description }
*
* @param string $index
*/
function db_loadHashList( $sql, $index='' ) {
	$cur = db_exec( $sql );
	$cur or exit( db_error() );
	$hashlist = array();
	while ($hash = db_fetch_array( $cur )) {
		
		$hashlist[$hash[$index ? $index : 0]] = $index ? $hash : (isset($hash[1]) ? $hash[1] : $hash[0]) ;
	}
	db_free_result( $cur );
	return $hashlist;
}

/**
* Document::db_loadList()
*
* { Description }
*
* @param [type] $maxrows
*/
function db_loadList( $sql, $maxrows=NULL ) {
	GLOBAL $AppUI;
	if (!($cur = db_exec( $sql ))) {;
		$AppUI->setMsg( db_error(), UI_MSG_ERROR );
		return false;
	}
	$list = array();
	$cnt = 0;
	while ($hash = db_fetch_assoc( $cur )) {
		$list[] = $hash;
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	db_free_result( $cur );
	return $list;
}

/**
* Document::db_loadColumn()
*
* { Description }
*
* @param [type] $maxrows
*/
function db_loadColumn( $sql, $maxrows=NULL ) {
	GLOBAL $AppUI;
	if (!($cur = db_exec( $sql ))) {;
		$AppUI->setMsg( db_error(), UI_MSG_ERROR );
		return false;
	}
	$list = array();
	$cnt = 0;
	while ($row = db_fetch_row( $cur )) {
		$list[] = $row[0];
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	db_free_result( $cur );
	return $list;
}

/* return an array of objects from a SQL SELECT query
 * class must implement the Load() factory, see examples in Webo classes
 * @note to optimize request, only select object oids in $sql
 */
function db_loadObjectList( $sql, $object, $maxrows = NULL ) {
	$cur = db_exec( $sql );
	if (!$cur) {
		die( "db_loadObjectList : " . db_error() );
	}
	$list = array();
	$cnt = 0;
	while ($row = db_fetch_array( $cur )) {
		$list[] = $object->Load( $row[0] );
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	db_free_result( $cur );
	return $list;
}


/**
* Document::db_insertArray()
*
* { Description }
*
* @param [type] $verbose
*/
function db_insertArray( $table, &$hash, $verbose=false ) {
	$fmtsql = "insert into $table ( %s ) values( %s ) ";
	foreach ($hash as $k => $v) {
		if (is_array($v) or is_object($v) or $v == NULL) {
			continue;
		}
		$fields[] = $k;
		$values[] = "'" . db_escape( $v ) . "'";
	}
	$sql = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );

	($verbose) && print "$sql<br />\n";

	if (!db_exec( $sql )) {
		return false;
	}
	$id = db_insert_id();
	return true;
}

/**
* Document::db_updateArray()
*
* { Description }
*
* @param [type] $verbose
*/
function db_updateArray( $table, &$hash, $keyName, $verbose=false ) {
	$fmtsql = "UPDATE $table SET %s WHERE %s";
	foreach ($hash as $k => $v) {
		if( is_array($v) or is_object($v) or $k[0] == '_' ) // internal or NA field
			continue;

		if( $k == $keyName ) { // PK not to be updated
			$where = "$keyName='" . db_escape( $v ) . "'";
			continue;
		}
		if ($v == '') {
			$val = 'NULL';
		} else {
			$val = "'" . db_escape( $v ) . "'";
		}
		$tmp[] = "$k=$val";
	}
	$sql = sprintf( $fmtsql, implode( ",", $tmp ) , $where );
	($verbose) && print "$sql<br />\n";
	$ret = db_exec( $sql );
	return $ret;
}

/**
* Document::db_delete()
*
* { Description }
*
*/
function db_delete( $table, $keyName, $keyValue ) {
	$keyName = db_escape( $keyName );
	$keyValue = db_escape( $keyValue );
	$ret = db_exec( "DELETE FROM $table WHERE $keyName='$keyValue'" );
	return $ret;
}


/**
* Document::db_insertObject()
*
* Inserta un objeto en la base de datos
*
* @param $table La tabla en la que se va a insertar
* @param $object El objeto que contiene toda la data
* @param $keyName
* @param verbose
*/
function db_insertObject( $table, &$object, $keyName = NULL, $verbose=false ) {
	$fmtsql = "INSERT INTO $table ( %s ) VALUES ( %s ) ";
	foreach (get_object_vars( $object ) as $k => $v) {
		if (is_array($v) or is_object($v) or $v == NULL) {
			continue;
		}
		if ($k[0] == '_') { // internal field
			continue;
		}
		$fields[] = $k;
		$values[] = "'" . db_escape( $v ) . "'";
	}
	$sql = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );
	($verbose) && print "$sql<br />\n";
	if (!db_exec( $sql )) {
		return false;
	}
	$id = db_insert_id();
	($verbose) && print "id=[$id]<br />\n";
	if ($keyName && $id)
		$object->$keyName = $id;
	return true;
}

/**
* Document::db_updateObject()
*
* { Description }
*
* @param [type] $updateNulls
*/
function db_updateObject( $table, &$object, $keyName, $updateNulls=true ) {
	$fmtsql = "UPDATE $table SET %s WHERE %s";
	//echo "<pre>";
	foreach (get_object_vars( $object ) as $k => $v) {
		if( is_array($v) or is_object($v) or $k[0] == '_' ) { // internal or NA field
			continue;
		}
		if( $k == $keyName ) { // PK not to be updated
			$where = "$keyName='" . db_escape( $v ) . "'";
			continue;
		}
		if ($v === NULL && !$updateNulls) {
			//echo "sali?$k=$v \n";
			continue;
		}
		if( $v === '' ) {
			$val = "''";
		} else if ( is_null($v)){
			$val = "NULL";
		}else {
			$val = "'" . db_escape( $v ) . "'";
		}

		$tmp[] = "$k=$val";
	}
	$sql = sprintf( $fmtsql, implode( ",", $tmp ) , $where );
	//echo "\n$sql</pre>";
	return db_exec( $sql );
}

/**
* Document::db_dateConvert()
*
* { Description }
*
*/
function db_dateConvert( $src, &$dest, $srcFmt ) {
	$result = strtotime( $src );
	$dest = $result;
	return ( $result != 0 );
}

/**
* Document::db_datetime()
*
* { Description }
*
* @param [type] $timestamp
*/
function db_datetime( $timestamp = NULL ) {
	if (!$timestamp) {
		return NULL;
	}
	if (is_object($timestamp)) {
		return $timestamp->toString( '%Y-%m-%d %H:%M:%S');
	} else {
		return strftime( '%Y-%m-%d %H:%M:%S', $timestamp );
	}
}

/**
* Document::db_dateTime2locale()
*
* { Description }
*
*/
function db_dateTime2locale( $dateTime, $format ) {
	if (intval( $dateTime)) {
		$date = new CDate( $dateTime );
		return $date->format( $format );
	} else {
		return null;
	}
}

/*
* copy the hash array content into the object as properties
* only existing properties of object are filled. when undefined in hash, properties wont be deleted
* @param array the input array
* @param obj byref the object to fill of any class
* @param string
* @param boolean
* @param boolean
*/
function bindHashToObject( $hash, &$obj, $prefix=NULL, $checkSlashes=true, $bindAll=false ) {
	is_array( $hash ) or die( "bindHashToObject : hash expected" );
	is_object( $obj ) or die( "bindHashToObject : object expected" );

	if ($bindAll) {
		foreach ($hash as $k => $v) {
			$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $hash[$k] ) : $hash[$k];
		}
	} else if ($prefix) {
		foreach (get_object_vars($obj) as $k => $v) {
			if (isset($hash[$prefix . $k ])) {
				$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $hash[$k] ) : $hash[$k];
			}
		}
	} else {
		foreach (get_object_vars($obj) as $k => $v) {
			if (isset($hash[$k])) {
				$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $hash[$k] ) : $hash[$k];
			}
		}
	}
	//echo "obj="; print_r($obj); exit;
}
?><?php /* $Id: db_connect.php,v 1.1 2009-05-19 21:15:30 pkerestezachi Exp $ */
/*
	Based on Leo West's (west_leo@yahooREMOVEME.com):
	lib.DB
	Database abstract layer
	-----------------------
	MYSQL VERSION
	-----------------------
	A generic database layer providing a set of low to middle level functions
	originally written for WEBO project, see webo source for "real life" usages
*/

function db_connect( $host='localhost', $dbname, $user='root', $passwd='', $port='3306', $persist=false ) {
	function_exists( 'mysql_connect' )
		or  die( 'FATAL ERROR: MySQL support not avaiable.  Please check your configuration.' );

	if ($persist) {
		mysql_pconnect( "$host:$port", $user, $passwd )
			or die( 'FATAL ERROR: Connection to database server failed' );
	} else {
		mysql_connect( "$host:$port", $user, $passwd )
			or die( 'FATAL ERROR: Connection to database server failed' );
	}

	if ($dbname) {
		mysql_select_db( $dbname )
			or die( "FATAL ERROR: Database not found ($dbname)" );
	} else {
		die( "FATAL ERROR: Database name not supplied<br />(connection to database server succesful)" );
	}
}

function db_error() {
	return mysql_error();
}

function db_errno() {
	return mysql_errno();
}

function db_insert_id() {
//	return mysql_insert_id();
		if ( mysql_affected_rows() > 0 ) {
			return mysql_insert_id(); 
		} else  {
			return false; 
		}

}

function db_exec( $sql ) {
	global $debugsql, $tracesql, $AppUI;
	if ($tracesql)
		echo "<pre>$sql</pre>";		
/*
	if($fle = fopen($AppUI->getConfig('root_dir')."/debugpsa.txt", "a")){
		fwrite($fle,$sql.chr(13).chr(10));	
		fclose($fle);
	}
*/         //echo "<pre>$sql</pre>";
	$cur = mysql_query( $sql )or die (mysql_error());
	if( !$cur ) {
		if ($debugsql)
			echo "<pre>$sql</pre>";	
		return false;
	}
	return $cur;
}

function db_free_result( $cur ) {
	mysql_free_result( $cur );
}

function db_num_rows( $qid ) {
	return mysql_num_rows( $qid );
}

function db_fetch_row( $cur ) {
	return mysql_fetch_row( $cur );
}

function db_fetch_assoc( $cur ) {
	return mysql_fetch_assoc( $cur );
}

function db_fetch_array( $cur  ) {
	return mysql_fetch_array( $cur );
}

function db_fetch_object( $cur  ) {
	return mysql_fetch_object( $cur );
}

function db_escape( $str ) {
	return mysql_escape_string( $str );
}

function db_version() {
	;
	if( ($cur = mysql_query( "SELECT VERSION()" )) ) {
		$row =  mysql_fetch_row( $cur );
		mysql_free_result( $cur );
		return $row[0];
	} else {
		return 0;
	}
}

function db_unix2dateTime( $time ) {
	// converts a unix time stamp to the default date format
	return $time > 0 ? date("Y-m-d H:i:s", $time) : null;
}

function db_dateTime2unix( $time ) {
	if ($time == '0000-00-00 00:00:00') {
		return -1;
	}
	if( ! preg_match( "/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})(.?)$/", $time, $a ) ) {
		return -1;
	} else {
		//return mktime( $a[4], $a[5], $a[6], $a[2], $a[3], $a[1] );
		return mktime_fix( $a[4], $a[5], $a[6], $a[2], $a[3], $a[1] );
	}
}
?>
