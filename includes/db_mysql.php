<?php /* $Id: db_mysql.php,v 1.1 2009-05-19 21:15:30 pkerestezachi Exp $ */
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
*/
	$cur = mysql_query( $sql );
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