<?php
/* $Id: common.lib.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */

/**
 * Misc stuff and functions used by almost all the scripts.
 * Among other things, it contains the advanced authentification work.
 */

if (!defined('PMA_COMMON_LIB_INCLUDED')){
    define('PMA_COMMON_LIB_INCLUDED', 1);

    /**
     * Order of sections for common.lib.php:
     *
     * in PHP3, functions and constants must be physically defined
     * before they are referenced
     *
     * some functions need the constants of libraries/defines.lib.php
     *
     * the include of libraries/defines.lib.php must be after the connection
     * to db to get the MySql version
     *
     * the PMA_sqlAddslashes() function must be before the connection to db
     *
     * the PMA_mysqlDie() function must be before the connection to db but after
     * mysql extension has been loaded
     *
     * ... so the required order is:
     *
     * - definition of PMA_auth()
     * - parsing of the configuration file
     * - first load of the libraries/define.lib.php library (won't get the
     *   MySQL release number)
     * - load of mysql extension (if necessary)
     * - definition of PMA_sqlAddslashes()
     * - definition of PMA_mysqlDie()
     * - db connection
     * - advanced authentication work if required
     * - second load of the libraries/define.lib.php library to get the MySQL
     *   release number)
     * - other functions, respecting dependencies 
     */


    /**
     * Avoids undefined variables in PHP3
     */
    if (!isset($use_backquotes)) {
        $use_backquotes   = 0;
    }
    if (!isset($pos)) {
        $pos              = 0;
    }


    /**
     * Parses the configuration file and gets some constants used to define
     * versions of phpMyAdmin/php/mysql...
     */
    include('config.inc.php');

    // For compatibility with old config.inc.php
    if (!isset($cfgExecTimeLimit)) {
        $cfgExecTimeLimit       = 300; // 5 minuts
    }
    if (!isset($cfgShowStats)) {
        $cfgShowStats           = TRUE;
    }
    if (!isset($cfgShowTooltip)) {
        $cfgShowTooltip         = TRUE;
    }
    if (!isset($cfgShowMysqlInfo)) {
        $cfgShowMysqlInfo       = FALSE;
    }
    if (!isset($cfgShowMysqlVars)) {
        $cfgShowMysqlVars       = FALSE;
    }
    if (!isset($cfgShowPhpInfo)) {
        $cfgShowPhpInfo         = FALSE;
    }
    if (!isset($cfgShowAll)) {
        $cfgShowAll             = FALSE;
    }
    if (!isset($cfgNavigationBarIconic)) {
        $cfgNavigationBarIconic = TRUE;
    }
    if (!isset($cfgProtectBinary)) {
        if (isset($cfgProtectBlob)) {
            $cfgProtectBinary   = ($cfgProtectBlob ? 'blob' : FALSE);
            unset($cfgProtectBlob);
        } else {
            $cfgProtectBinary   = 'blob';
        }
    }
    if (!isset($cfgZipDump)) {
        $cfgZipDump             = (isset($cfgGZipDump) ? $cfgGZipDump : TRUE);
    }
    if (!isset($cfgLeftBgColor)) {
        $cfgLeftBgColor         = '#D0DCE0';
    }
    if (!isset($cfgRightBgColor)) {
        $cfgRightBgColor        = '#F5F5F5';
    }
    if (!isset($cfgPointerColor)) {
        $cfgPointerColor        = '#CCFFCC';
    }
    if (!isset($cfgTextareaCols)) {
        $cfgTextareaCols        = 40;
    }
    if (!isset($cfgTextareaRows)) {
        $cfgTextareaRows        = 7;
    }

    // Adds a trailing slash et the end of the phpMyAdmin uri if it does not
    // exist
    if ($cfgPmaAbsoluteUri != '' && substr($cfgPmaAbsoluteUri, -1) != '/') {
        $cfgPmaAbsoluteUri .= '/';
    }

    // Gets some constants
    include('defines.lib.php');

    // If zlib output compression is set in the php configuration file, no
    // output buffering should be run
    if (PMA_PHP_INT_VERSION < 40000
        || (PMA_PHP_INT_VERSION >= 40005 && @ini_get('zlib.output_compression'))) {
        $cfgOBGzip = FALSE;
    }


    /**
     * Loads the mysql extensions if it is not loaded yet
     * staybyte - 26. June 2001
     */
    if (((PMA_PHP_INT_VERSION >= 40000 && !@ini_get('safe_mode'))
        || (PMA_PHP_INT_VERSION > 30009 && !@get_cfg_var('safe_mode')))
        && @function_exists('dl')) {
        if (PMA_PHP_INT_VERSION < 40000) {
            $extension = 'MySQL';
        } else {
            $extension = 'mysql';
        }
        if (PMA_IS_WINDOWS) {
            $suffix = '.dll';
        } else {
            $suffix = '.so';
        }
        if (!@extension_loaded($extension)) {
            @dl($extension.$suffix);
        }
        if (!@extension_loaded($extension)) {
            echo $strCantLoadMySQL;
            exit();
        }
    } // end load mysql extension


    /**
     * Add slashes before "'" and "\" characters so a value containing them can
     * be used in a sql comparison.
     *
     * @param   string   the string to slash
     * @param   boolean  whether the string will be used in a 'LIKE' clause
     *                   (it then requires two more escaped sequences) or not
     *
     * @return  string   the slashed string
     *
     * @access  public
     */
    function PMA_sqlAddslashes($a_string = '', $is_like = FALSE)
    {
        if ($is_like) {
            $a_string = str_replace('\\', '\\\\\\\\', $a_string);
        } else {
            $a_string = str_replace('\\', '\\\\', $a_string);
        }
        $a_string = str_replace('\'', '\\\'', $a_string);
    
        return $a_string;
    } // end of the 'PMA_sqlAddslashes()' function


    /**
     * Displays a MySQL error message in the right frame.
     *
     * @param   string   the error mesage
     * @param   string   the sql query that failed
     * @param   boolean  whether to show a "modify" link or not
     * @param   string   the "back" link url (full path is not required)
     *
     * @access  public
     */
    function PMA_mysqlDie($error_message = '', $the_query = '',
                          $is_modify_link = TRUE, $back_url = '')
    {
        if (!$error_message) {
            $error_message = mysql_error();
        }
        if (!$the_query && !empty($GLOBALS['sql_query'])) {
            $the_query = $GLOBALS['sql_query'];
        }

        if (isset($GLOBALS['strError'])) echo '<b>'. $GLOBALS['strError'] . '</b>' . "\n";
        // if the config password is wrong, or the MySQL server does not
        // respond, do not show the query that would reveal the
        // username/password
        if (!empty($the_query) && !strstr($the_query, 'connect')) {
            $query_base = htmlspecialchars($the_query);
            $query_base = ereg_replace("((\015\012)|(\015)|(\012)){3,}", "\n\n", $query_base);
            echo '<p>' . "\n";
            echo '    ' . $GLOBALS['strSQLQuery'] . '&nbsp;:&nbsp;' . "\n";
            if ($is_modify_link) {
                echo '    ['
                     . '<a href="db_details.php?lang=' . $GLOBALS['lang'] . '&amp;server=' . urlencode($GLOBALS['server']) . '&amp;db=' . urlencode($GLOBALS['db']) . '&amp;sql_query=' . urlencode($the_query) . '&amp;show_query=y">' . $GLOBALS['strEdit'] . '</a>'
                     . ']' . "\n";
            } // end if
            echo '<pre>' . "\n" . $query_base . "\n" . '</pre>' . "\n";
            echo '</p>' . "\n";
        } // end if
        if (!empty($error_message)) {
            $error_message = htmlspecialchars($error_message);
            $error_message = ereg_replace("((\015\012)|(\015)|(\012)){3,}", "\n\n", $error_message);
        }
        echo '<p>' . "\n";
        if (isset($GLOBALS['strMySQLSaid'])) echo '    ' . $GLOBALS['strMySQLSaid'] . '<br />' . "\n";
        echo '<pre>' . "\n" . $error_message . "\n" . '</pre>' . "\n";
        echo '</p>' . "\n";
        if (!empty($back_url)) {
            echo '<a href="' . $back_url . '">' . $GLOBALS['strBack'] . '</a>';
        }
        echo "\n";

        exit();
    } // end of the 'PMA_mysqlDie()' function


    /**
     * Use mysql_connect() or mysql_pconnect()?
     */
    $connect_func = ($cfgPersistentConnections) ? 'mysql_pconnect' : 'mysql_connect';
    $dbNamelist       = array();


    /**
     * Gets the valid servers list and parameters
     */
    reset($cfgServers);
    while (list($key, $val) = each($cfgServers)) {
        // Don't use servers with no hostname
        if (empty($val['host'])) {
            unset($cfgServers[$key]);
        }
    }
 
    if (empty($server) || !isset($cfgServers[$server]) || !is_array($cfgServers[$server])) {
        $server = $cfgServerDefault;
    }


    /**
     * If no server is selected, make sure that $cfgServer is empty (so that
     * nothing will work), and skip server authentication.
     * We do NOT exit here, but continue on without logging into any server.
     * This way, the welcome page will still come up (with no server info) and
     * present a choice of servers in the case that there are multiple servers
     * and '$cfgServerDefault = 0' is set.
     */
    if ($server == 0) {
        $cfgServer = array();
    }

    /**
     * Otherwise, set up $cfgServer and do the usual login stuff.
     */
    else if (isset($cfgServers[$server])) {
        $cfgServer = $cfgServers[$server];

        // Check how the config says to connect to the server
        $server_port   = (empty($cfgServer['port']))
                       ? ''
                       : ':' . $cfgServer['port'];
        if (strtolower($cfgServer['connect_type']) == 'tcp') {
            $cfgServer['socket'] = '';
        }
        $server_socket = (empty($cfgServer['socket']) || PMA_PHP_INT_VERSION < 30010)
                       ? ''
                       : ':' . $cfgServer['socket'];

        // The user can work with only some databases
        if (isset($cfgServer['only_db']) && $cfgServer['only_db'] != '') {
            if (is_array($cfgServer['only_db'])) {
                $dbNamelist   = $cfgServer['only_db'];
            } else {
                $dbNamelist[] = $cfgServer['only_db'];
            }
        } // end if

        if (PMA_PHP_INT_VERSION >= 40000) {
            $bkp_track_err = @ini_set('track_errors', 1);
        }

        // Connects to the server (validates user's login)
        $userlink      = @$connect_func(
                             $cfgServer['host'] . $server_port . $server_socket,
                             $cfgServer['user'],
                             $cfgServer['password']
                         );
        if ($userlink == FALSE) {

            // Standard authentication case
            if (mysql_error()) {
                $conn_error = mysql_error();
            } else if (isset($php_errormsg)) {
                $conn_error = $php_errormsg;
            } else {
                $conn_error = 'Cannot connect: invalid settings.';
            }
            $local_query    = $connect_func . '('
                            . $cfgServer['host'] . $server_port . $server_socket . ', '
                            . $cfgServer['user'] . ', '
                            . $cfgServer['password'] . ')';
            PMA_mysqlDie($conn_error, $local_query, FALSE);
        } // end if

        if (PMA_PHP_INT_VERSION >= 40000) {
            @ini_set('track_errors', $bkp_track_err);
        }

        // If stduser isn't defined, use the current user settings to get his
        // rights
        if ($cfgServer['stduser'] == '') {
            $dbNameh = $userlink;
        }

        // if 'only_db' is set for the current user, there is no need to check for
        // available databases in the "mysql" db
        $dbNamelist_cnt = count($dbNamelist);
        if ($dbNamelist_cnt) {
            $true_dblist  = array();
            $is_show_dbs  = TRUE;
            for ($i = 0; $i < $dbNamelist_cnt; $i++) {
                if ($is_show_dbs && ereg('(^|[^\])(_|%)', $dbNamelist[$i])) {
                    $local_query = 'SHOW DATABASES LIKE \'' . $dbNamelist[$i] . '\'';
                    $rs          = mysql_query($local_query, $dbNameh);
                    // "SHOW DATABASES" statement is disabled
                    if ($i == 0
                        && (mysql_error() && mysql_errno() == 1045)) {
                        $true_dblist[] = str_replace('\\_', '_', str_replace('\\%', '%', $dbNamelist[$i]));
                        $is_show_dbs   = FALSE;
                    }
                    // Debug
                    // else if (mysql_error()) {
                    //    PMA_mysqlDie('', $local_query, FALSE);
                    // }
                    while ($row = @mysql_fetch_array($rs)) {
                        $true_dblist[] = $row['Database'];
                    } // end while
                    if ($rs) {
                        mysql_free_result($rs);
                    }
                } else {
                    $true_dblist[]     = str_replace('\\_', '_', str_replace('\\%', '%', $dbNamelist[$i]));
                } // end if... else...
            } // end for
            $dbNamelist       = $true_dblist;
            unset($true_dblist);
        } // end if

        // 'only_db' is empty for the current user -> checks for available
        // databases in the "mysql" db
        else {
            $auth_query = 'SELECT User, Select_priv '
                        . 'FROM mysql.user '
                        . 'WHERE User = \'' . PMA_sqlAddslashes($cfgServer['user']) . '\'';
            $rs         = mysql_query($auth_query, $dbNameh); // Debug: or PMA_mysqlDie('', $auth_query, FALSE);
        } // end if

        // Access to "mysql" db allowed -> gets the usable db list
        if (!$dbNamelist_cnt && @mysql_numrows($rs)) {
            $row = mysql_fetch_array($rs);
            mysql_free_result($rs);

            if ($row['Select_priv'] != 'Y') {
                // lem9: User can be blank (anonymous user)
                $local_query = 'SELECT DISTINCT Db FROM mysql.db WHERE Select_priv = \'Y\' AND (User = \'' . PMA_sqlAddslashes($cfgServer['user']) . '\' OR User = \'\')';
                $rs          = mysql_query($local_query, $dbNameh); // Debug: or PMA_mysqlDie('', $local_query, FALSE);
                if (@mysql_numrows($rs) <= 0) {
                    $local_query = 'SELECT DISTINCT Db FROM mysql.tables_priv WHERE Table_priv LIKE \'%Select%\' AND User = \'' . PMA_sqlAddslashes($cfgServer['user']) . '\'';
                    $rs          = mysql_query($local_query, $dbNameh); // Debug: or PMA_mysqlDie('', $local_query, FALSE);
                    if (@mysql_numrows($rs)) {
                        while ($row = mysql_fetch_array($rs)) {
                            $dbNamelist[] = $row['Db'];
                        }
                        mysql_free_result($rs);
                    }
                } else {
                    // Will use as associative array of the following 2 code
                    // lines:
                    //   the 1st is the only line intact from before
                    //     correction,
                    //   the 2nd replaces $dbNamelist[] = $row['Db'];
                    $uva_mydbs = array();
                    // Code following those 2 lines in correction continues
                    // populating $dbNamelist[], as previous code did. But it is
                    // now populated with actual database names instead of
                    // with regular expressions.
                    while ($row = mysql_fetch_array($rs)) {
                        // loic1: all databases cases - part 1
                        if (empty($row['Db']) || $row['Db'] == '%') {
                            $uva_mydbs['%'] = 1;
                            break;
                        }
                        // loic1: avoid multiple entries for dbs
                        if (!isset($uva_mydbs[$row['Db']])) {
                            $uva_mydbs[$row['Db']] = 1;
                        }
                    } // end while
                    mysql_free_result($rs);
                    $uva_alldbs = mysql_list_dbs($dbNameh);
                    // loic1: all databases cases - part 2
                    if (isset($uva_mydbs['%'])) {
                        while ($uva_row = mysql_fetch_array($uva_alldbs)) {
                            $dbNamelist[] = $uva_row[0];
                        } // end while
                    } // end if
                    else {
                        while ($uva_row = mysql_fetch_array($uva_alldbs)) {
                            $uva_db = $uva_row[0];
                            if (isset($uva_mydbs[$uva_db]) && $uva_mydbs[$uva_db] == 1) {
                                $dbNamelist[]           = $uva_db;
                                $uva_mydbs[$uva_db] = 0;
                            } else if (!isset($dbNamelist[$uva_db])) {
                                reset($uva_mydbs);
                                while (list($uva_matchpattern, $uva_value) = each($uva_mydbs)) {
                                    // loic1: fixed bad regexp
                                    // TODO: db names may contain characters
                                    //       that are regexp instructions
                                    $re        = '(^|(\\\\\\\\)+|[^\])';
                                    $uva_regex = ereg_replace($re . '%', '\\1.*', ereg_replace($re . '_', '\\1.{1}', $uva_matchpattern));
                                    // Fixed db name matching
                                    // 2000-08-28 -- Benjamin Gandon
                                    if (ereg('^' . $uva_regex . '$', $uva_db)) {
                                        $dbNamelist[] = $uva_db;
                                        break;
                                    }
                                } // end while
                            } // end if ... else if....
                        } // end while
                    } // end else
                    mysql_free_result($uva_alldbs);
                    unset($uva_mydbs);
                } // end else
            } // end if
        } // end building available dbs from the "mysql" db
    } // end server connecting

    /**
     * Missing server hostname
     */
    else {
        echo $strHostEmpty;
    }


    /**
     * Get the list and number of available databases.
     *
     * @param   string   the url to go back to in case of error
     *
     * @return  boolean  always true
     *
     * @global  array    the list of available databases
     * @global  integer  the number of available databases
     */
    function PMA_availableDatabases($error_url = '')
    {
        global $dbNamelist;
        global $num_dbs;

        $num_dbs = count($dbNamelist);

        // 1. A list of allowed databases has already been defined by the
        //    authentification process -> gets the available databases list
        if ($num_dbs) {
            $true_dblist = array();
            for ($i = 0; $i < $num_dbs; $i++) {
                $dbNamelink  = @mysql_select_db($dbNamelist[$i]);
                if ($dbNamelink) {
                    $true_dblist[] = $dbNamelist[$i];
                } // end if
            } // end for
            $dbNamelist      = array();
            $dbNamelist      = $true_dblist;
            unset($true_dblist);
            $num_dbs     = count($dbNamelist);
        } // end if

        // 2. Allowed database list is empty -> gets the list of all databases
        //    on the server
        else {
            $dbNames          = mysql_list_dbs() or PMA_mysqlDie('', 'mysql_list_dbs()', FALSE, $error_url);
            $num_dbs      = @mysql_num_rows($dbNames);
            $real_num_dbs = 0;
            for ($i = 0; $i < $num_dbs; $i++) {
                $dbName_name_tmp = mysql_dbname($dbNames, $i);
                $dbNamelink      = @mysql_select_db($dbName_name_tmp);
                if ($dbNamelink) {
                    $dbNamelist[] = $dbName_name_tmp;
                    $real_num_dbs++;
                }
            } // end for
            mysql_free_result($dbNames);
            $num_dbs = $real_num_dbs; 
        } // end else

        return TRUE;
    } // end of the 'PMA_availableDatabases()' function


    /**
     * Gets constants that defines the PHP, MySQL... releases.
     * This include must be located physically before any code that needs to
     * reference the constants, else PHP 3.0.16 won't be happy; and must be
     * located after we are connected to db to get the MySql version.
     */
    include('defines.lib.php');


    /**
     * Adds backquotes on both sides of a database, table or field name.
     * Since MySQL 3.23.6 this allows to use non-alphanumeric characters in
     * these names.
     *
     * @param   string   the database, table or field name to "backquote"
     * @param   boolean  a flag to bypass this function (used by dump functions)
     *
     * @return  string   the "backquoted" database, table or field name if the
     *                   current MySQL release is >= 3.23.6, the original one
     *                   else
     *
     * @access  public
     */
    function PMA_backquote($a_name, $do_it = TRUE)
    {
        if ($do_it
            && PMA_MYSQL_INT_VERSION >= 32306
            && !empty($a_name) && $a_name != '*') {
            return '`' . $a_name . '`';
        } else {
            return $a_name;
        }
    } // end of the 'PMA_backquote()' function


    /**
     * Defines the <CR><LF> value depending on the user OS.
     *
     * @return  string   the <CR><LF> value to use
     *
     * @access  public
     */
    function PMA_whichCrlf()
    {
        $the_crlf = "\n";

        // The 'PMA_USR_OS' constant is defined in "defines.lib.php"
        // Win case
        if (PMA_USR_OS == 'Win') {
            $the_crlf = "\r\n";
        }
        // Mac case
        else if (PMA_USR_OS == 'Mac') {
            $the_crlf = "\r";
        }
        // Others
        else {
            $the_crlf = "\n";
        }

        return $the_crlf;
    } // end of the 'PMA_whichCrlf()' function


    /**
     * Writes localised date
     *
     * @param   string   the current timestamp
     *
     * @return  string   the formatted date
     *
     * @access  public
     */
    function PMA_localisedDate($timestamp = -1)
    {
        global $datefmt, $month, $day_of_week;

        if ($timestamp == -1) {
            $timestamp = time();
        }

        $date = ereg_replace('%[aA]', $day_of_week[(int)strftime('%w', $timestamp)], $datefmt);
        $date = ereg_replace('%[bB]', $month[(int)strftime('%m', $timestamp)-1], $date);

        return strftime($date, $timestamp);
    } // end of the 'PMA_localisedDate()' function

}
?>