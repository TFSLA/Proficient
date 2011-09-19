<?php
/* $Id: build_dump.lib.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */

/**
 * Set of functions used to build dumps of tables
 */

if (!defined('PMA_BUILD_DUMP_LIB_INCLUDED')){
    define('PMA_BUILD_DUMP_LIB_INCLUDED', 1);

    /**
     * Uses the 'htmlspecialchars()' php function on databases, tables and fields
     * name if the dump has to be displayed on screen.
     *
     * @param   string   the string to format
     *
     * @return  string   the formatted string
     *
     * @access  private
     */
    function PMA_htmlFormat($a_string = '')
    {
        return (empty($GLOBALS['asfile']) ? htmlspecialchars($a_string) : $a_string);
    } // end of the 'PMA_htmlFormat()' function


    /**
     * Returns $table's CREATE definition
     *
     * Uses the 'PMA_htmlFormat()' function defined in 'tbl_dump.php'
     *
     * @param   string   the database name
     * @param   string   the table name
     * @param   string   the end of line sequence
     * @param   string   the url to go back in case of error
     *
     * @return  string   the CREATE statement on success
     *
     * @global  boolean  whether to add 'drop' statements or not
     * @global  boolean  whether to use backquotes to allow the use of special
     *                   characters in database, table and fields names or not
     *
     * @see     PMA_htmlFormat()
     *
     * @access  public
     */
    function PMA_getTableDef($dbName, $table, $crlf, $error_url)
    {
        global $drop;
        global $use_backquotes;

        $schema_create = '';
        if (!empty($drop)) {
            $schema_create .= 'DROP TABLE IF EXISTS ' . PMA_backquote(PMA_htmlFormat($table), $use_backquotes) . ';' . $crlf;
        }

        // Steve Alberty's patch for complete table dump,
        // modified by Lem9 to allow older MySQL versions to continue to work
        if (PMA_MYSQL_INT_VERSION >= 32321) {
            // Whether to quote table and fields names or not
            if ($use_backquotes) {
                mysql_query('SET SQL_QUOTE_SHOW_CREATE = 1');
            } else {
                mysql_query('SET SQL_QUOTE_SHOW_CREATE = 0');
            }
            $result = mysql_query('SHOW CREATE TABLE ' . PMA_backquote($dbName) . '.' . PMA_backquote($table));
            if ($result != FALSE && mysql_num_rows($result) > 0) {
                $tmpres        = mysql_fetch_array($result);
                $schema_create .= str_replace("\n", $crlf, PMA_htmlFormat($tmpres[1]));
            }
            mysql_free_result($result);
            return $schema_create;
        } // end if MySQL >= 3.23.20

        // For MySQL < 3.23.20
        $schema_create .= 'CREATE TABLE ' . PMA_htmlFormat(PMA_backquote($table), $use_backquotes) . ' (' . $crlf;

        $local_query   = 'SHOW FIELDS FROM ' . PMA_backquote($dbName) . '.' . PMA_backquote($table);
        $result        = mysql_query($local_query) or PMA_mysqlDie('', $local_query, '', $error_url);
        while ($row = mysql_fetch_array($result)) {
            $schema_create     .= '   ' . PMA_htmlFormat(PMA_backquote($row['Field'], $use_backquotes)) . ' ' . $row['Type'];
            if (isset($row['Default']) && $row['Default'] != '') {
                $schema_create .= ' DEFAULT \'' . PMA_htmlFormat(PMA_sqlAddslashes($row['Default'])) . '\'';
            }
            if ($row['Null'] != 'YES') {
                $schema_create .= ' NOT NULL';
            }
            if ($row['Extra'] != '') {
                $schema_create .= ' ' . $row['Extra'];
            }
            $schema_create     .= ',' . $crlf;
        } // end while
        mysql_free_result($result);
        $schema_create         = ereg_replace(',' . $crlf . '$', '', $schema_create);

        $local_query = 'SHOW KEYS FROM ' . PMA_backquote($dbName) . '.' . PMA_backquote($table);
        $result      = mysql_query($local_query) or PMA_mysqlDie('', $local_query, '', $error_url);
        while ($row = mysql_fetch_array($result))
        {
            $kname    = $row['Key_name'];
            $comment  = (isset($row['Comment'])) ? $row['Comment'] : '';
            $sub_part = (isset($row['Sub_part'])) ? $row['Sub_part'] : '';

            if ($kname != 'PRIMARY' && $row['Non_unique'] == 0) {
                $kname = "UNIQUE|$kname";
            }
            if ($comment == 'FULLTEXT') {
                $kname = 'FULLTEXT|$kname';
            }
            if (!isset($index[$kname])) {
                $index[$kname] = array();
            }
            if ($sub_part > 1) {
                $index[$kname][] = PMA_htmlFormat(PMA_backquote($row['Column_name'], $use_backquotes)) . '(' . $sub_part . ')';
            } else {
                $index[$kname][] = PMA_htmlFormat(PMA_backquote($row['Column_name'], $use_backquotes));
            }
        } // end while
        mysql_free_result($result);

        while (list($x, $columns) = @each($index)) {
            $schema_create     .= ',' . $crlf;
            if ($x == 'PRIMARY') {
                $schema_create .= '   PRIMARY KEY (';
            } else if (substr($x, 0, 6) == 'UNIQUE') {
                $schema_create .= '   UNIQUE ' . substr($x, 7) . ' (';
            } else if (substr($x, 0, 8) == 'FULLTEXT') {
                $schema_create .= '   FULLTEXT ' . substr($x, 9) . ' (';
            } else {
                $schema_create .= '   KEY ' . $x . ' (';
            }
            $schema_create     .= implode($columns, ', ') . ')';
        } // end while

        $schema_create .= $crlf . ')';

        return $schema_create;
    } // end of the 'PMA_getTableDef()' function


    /**
     * php >= 4.0.5 only : get the content of $table as a series of INSERT
     * statements.
     * After every row, a custom callback function $handler gets called.
     *
     * Last revision 13 July 2001: Patch for limiting dump size from
     * vinay@sanisoft.com & girish@sanisoft.com
     *
     * @param   string   the current database name
     * @param   string   the current table name
     * @param   string   the 'limit' clause to use with the sql query
     * @param   string   the name of the handler (function) to use at the end
     *                   of every row. This handler must accept one parameter
     *                   ($sql_insert)
     * @param   string   the url to go back in case of error
     *
     * @return  boolean  always true
     *
     * @global  boolean  whether to use backquotes to allow the use of special
     *                   characters in database, table and fields names or not
     *
     * @access  private
     *
     * @see     PMA_getTableContent()
     *
     * @author  staybyte
     */
    function PMA_getTableContentFast($dbName, $table, $add_query = '', $handler, $error_url)
    {
        global $use_backquotes;

		// optimize table (delete record sets physically that have been marked as 'deleted')
        $local_query = 'OPTIMIZE TABLE ' . PMA_backquote($dbName) . '.' . PMA_backquote($table);
        mysql_query($local_query) or PMA_mysqlDie('', $local_query, '', $error_url);

		// select data for dump
        $local_query = 'SELECT * FROM ' . PMA_backquote($dbName) . '.' . PMA_backquote($table) . $add_query;
        $result      = mysql_query($local_query) or PMA_mysqlDie('', $local_query, '', $error_url);
        if ($result != FALSE) {
            $fields_cnt = mysql_num_fields($result);

            // Checks whether the field is an integer or not
            for ($j = 0; $j < $fields_cnt; $j++) {
                $field_set[$j] = PMA_backquote(mysql_field_name($result, $j), $use_backquotes);
                $type          = mysql_field_type($result, $j);
                if ($type == 'tinyint' || $type == 'smallint' || $type == 'mediumint' || $type == 'int' ||
                    $type == 'bigint'  ||$type == 'timestamp') {
                    $field_num[$j] = TRUE;
                } else {
                    $field_num[$j] = FALSE;
                }
            } // end for

            // Sets the scheme
            if (isset($GLOBALS['showcolumns'])) {
                $fields        = implode(', ', $field_set);
                $schema_insert = 'INSERT INTO ' . PMA_backquote(PMA_htmlFormat($table), $use_backquotes)
                               . ' (' . PMA_htmlFormat($fields) . ') VALUES (';
            } else {
                $schema_insert = 'INSERT INTO ' . PMA_backquote(PMA_htmlFormat($table), $use_backquotes)
                               . ' VALUES (';
            }
        
            $search     = array("\x00", "\x0a", "\x0d", "\x1a"); //\x08\\x09, not required
            $replace    = array('\0', '\n', '\r', '\Z');
            $isFirstRow = TRUE;

            @set_time_limit($GLOBALS['cfgExecTimeLimit']);

            while ($row = mysql_fetch_row($result)) {
                for ($j = 0; $j < $fields_cnt; $j++) {
                    if (!isset($row[$j])) {
                        $values[]     = 'NULL';
                    } else if ($row[$j] == '0' || $row[$j] != '') {
                        // a number
                        if ($field_num[$j]) {
                            $values[] = $row[$j];
                        }
                        // a string
                        else {
                            $values[] = "'" . str_replace($search, $replace, PMA_sqlAddslashes($row[$j])) . "'";
                        }
                    } else {
                        $values[]     = "''";
                    } // end if
                } // end for

                // Extended inserts case
                if (isset($GLOBALS['extended_ins'])) {
                    if ($isFirstRow) {
                        $insert_line = $schema_insert . implode(', ', $values) . ')';
                        $isFirstRow  = FALSE;
                    } else {
                        $insert_line = '(' . implode(', ', $values) . ')';
                    }
                }
                // Other inserts case
                else { 
                   $insert_line = $schema_insert . implode(', ', $values) . ')';
                }
                unset($values);

                // Call the handler
                $handler($insert_line);
            } // end while
            
            // Replace last comma by a semi-column in extended inserts case
            if (isset($GLOBALS['extended_ins'])) {
              $GLOBALS['tmp_buffer'] = ereg_replace(',([^,]*)$', ';\\1', $GLOBALS['tmp_buffer']);
            }
        } // end if ($result != FALSE)
        mysql_free_result($result);
    
        return TRUE;
    } // end of the 'PMA_getTableContentFast()' function


    /**
     * Dispatches between the versions of 'getTableContent' to use depending
     * on the php version
     *
     * Last revision 13 July 2001: Patch for limiting dump size from
     * vinay@sanisoft.com & girish@sanisoft.com
     *
     * @param   string   the current database name
     * @param   string   the current table name
     * @param   integer  the offset on this table
     * @param   integer  the last row to get
     * @param   string   the name of the handler (function) to use at the end
     *                   of every row. This handler must accept one parameter
     *                   ($sql_insert)
     * @param   string   the url to go back in case of error
     *
     * @access  public
     *
     * @see     PMA_getTableContentFast()
     *
     * @author  staybyte
     */
    function PMA_getTableContent($dbName, $table, $limit_from = 0, $limit_to = 0, $handler, $error_url)
    {
        // Defines the offsets to use
        if ($limit_from > 0) {
            $limit_from--;
        } else {
            $limit_from = 0;
        }
        if ($limit_to > 0 && $limit_from >= 0) {
            $add_query  = " LIMIT $limit_from, $limit_to";
        } else {
            $add_query  = '';
        }

        PMA_getTableContentFast($dbName, $table, $add_query, $handler, $error_url);
    } // end of the 'PMA_getTableContent()' function


    /**
     * Outputs the content of a table in CSV format
     *
     * Last revision 14 July 2001: Patch for limiting dump size from
     * vinay@sanisoft.com & girish@sanisoft.com
     *
     * @param   string   the database name
     * @param   string   the table name
     * @param   integer  the offset on this table
     * @param   integer  the last row to get
     * @param   string   the field separator character
     * @param   string   the optionnal "enclosed by" character
     * @param   string   the handler (function) to call. It must accept one
     *                   parameter ($sql_insert)
     * @param   string   the url to go back in case of error
     *
     * @global  string   whether to obtain an excel compatible csv format or a
     *                   simple csv one
     *
     * @return  boolean always true
     *
     * @access  public
     */
    function PMA_getTableCsv($dbName, $table, $limit_from = 0, $limit_to = 0, $sep, $enc_by, $esc_by, $handler, $error_url)
    {
        global $what;

        // Handles the "separator" and the optionnal "enclosed by" characters
        if ($what == 'excel') {
            $sep     = ',';
        } else if (!isset($sep)) {
            $sep     = '';
        } else {
            if (get_magic_quotes_gpc()) {
                $sep = stripslashes($sep);
            }
            $sep     = str_replace('\\t', "\011", $sep);
        }
        if ($what == 'excel') {
            $enc_by  = '"';
        } else if (!isset($enc_by)) {
            $enc_by  = '';
        } else if (get_magic_quotes_gpc()) {
            $enc_by  = stripslashes($enc_by);
        }
        if ($what == 'excel'
            || (empty($esc_by) && $enc_by != '')) {
            // double the "enclosed by" character
            $esc_by  = $enc_by;
        } else if (!isset($esc_by)) {
            $esc_by  = '';
        } else if (get_magic_quotes_gpc()) {
            $esc_by  = stripslashes($esc_by);
        }

        // Defines the offsets to use
        if ($limit_from > 0) {
            $limit_from--;
        } else {
            $limit_from = 0;
        }
        if ($limit_to > 0 && $limit_from >= 0) {
            $add_query  = " LIMIT $limit_from, $limit_to";
        } else {
            $add_query  = '';
        }

        // Gets the data from the database
        $local_query = 'SELECT * FROM ' . PMA_backquote($dbName) . '.' . PMA_backquote($table) . $add_query;
        $result      = mysql_query($local_query) or PMA_mysqlDie('', $local_query, '', $error_url);
        $fields_cnt  = mysql_num_fields($result);

        @set_time_limit($GLOBALS['cfgExecTimeLimit']);

        // Format the data
        $i = 0;
        while ($row = mysql_fetch_row($result)) {
            $schema_insert = '';
            for ($j = 0; $j < $fields_cnt; $j++) {
                if (!isset($row[$j])) {
                    $schema_insert .= 'NULL';
                }
                else if ($row[$j] == '0' || $row[$j] != '') {
                    // loic1 : always enclose fields
                    if ($what == 'excel') {
                        $row[$j]   = ereg_replace("\015(\012)?", "\012", $row[$j]);
                    }
                    $schema_insert .= $enc_by
                                   . str_replace($enc_by, $esc_by . $enc_by, $row[$j])
                                   . $enc_by;
                }
                else {
                    $schema_insert .= '';
                }
                if ($j < $fields_cnt-1) {
                    $schema_insert .= $sep;
                }
            } // end for
            $handler(trim($schema_insert));
            ++$i;
        } // end while
        mysql_free_result($result);

        return TRUE;
    } // end of the 'PMA_getTableCsv()' function

}
?>