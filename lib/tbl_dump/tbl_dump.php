<?php
   // define options
   $what = "data";          // structure and data
   $seperator = ";";        // line seperator
   $enclosed = "&quot;";    // enclosing character
   $escaped = "\\";
   $add_character = "\n"; // character that is added at the end of each line (lienbreak)
   //$showcolumns = "yes";  // complete inserts (put column names into INSERT statements (usually not needed)
   //$asfile = "sendit";    // output data as file to browser (download)

   // Defines the url to return to in case of error in a sql statement
   $err_url = 'http://arturo';

   $dbName = DATABASE_NAME;	    // DB-Name

   // Get the variables sent or posted to this script and a core script
   include_once 'common.lib.php';

/* $Id: tbl_dump.php,v 1.1 2009-05-19 21:15:40 pkerestezachi Exp $ */
/**
 * Formats the INSERT statements depending on the target (screen/file) of the
 * sql dump
 *
 * @param   string  the insert statement
 *
 * @global  string  the buffer containing formatted strings
 */
function PMA_myHandler($sql_insert)
{
    global $tmp_buffer;

    // Defines the end of line delimiter to use
    $eol_dlm = (isset($GLOBALS['extended_ins'])) ? ',' : ';';
    // Result will be displays on screen
    if (empty($GLOBALS['asfile'])) {
        $tmp_buffer .= htmlspecialchars($sql_insert . $eol_dlm . $GLOBALS['crlf']);
    }
    // Result will be save in a file
    else {
        $tmp_buffer .= $sql_insert . $eol_dlm . $GLOBALS['crlf'];
    }
} // end of the 'PMA_myHandler()' function


/*
 * Formats the INSERT statements depending on the target (screen/file) of the
 * cvs export
 *
 * Revisions: 2001-05-07, Lem9: added $add_character
 *            2001-07-12, loic1: $crlf should be used only if there is no EOL
 *                               character defined by the user
 *
 * @param   string  the insert statement
 *
 * @global  string  the character to add at the end of lines
 * @global  string  the buffer containing formatted strings
 */
function PMA_myCsvHandler($sql_insert)
{
    global $add_character;
    global $tmp_buffer;

    // Result will be displays on screen
    if (empty($GLOBALS['asfile'])) {
        $tmp_buffer .= htmlspecialchars($sql_insert) . $add_character;
    }
    // Result will be save in a file
    else {
        $tmp_buffer .= $sql_insert . $add_character;
    }
}


function saveDumpAsFile($exportStructure = true, $exportData = true, $zip = 0, $dropTable = 0 )
{	
	global $what;
	global $seperator;
	global $enclosed;
	global $escaped;
	global $add_character;
	global $showcolumns;
	global $asfile;
	global $dbName;
	global $err_url;
	
	global $tmp_buffer;
	global $cfgExecTimeLimit;
	global $cfgServer;
	global $crlf;
	global $strHost;
	global $strGenTime;
	global $strServerVersion;
	global $strPHPVersion;
	global $strDatabase;
	global $strTableStructure;
	global $strDumpingData;

	//include_once 'grab_globals.lib.php';
	include_once 'build_dump.lib.php';

// Increase time limit for script execution and initializes some variables
	@set_time_limit($cfgExecTimeLimit);
	$dump_buffer = '';

// Defines the default <CR><LF> format
	$crlf = PMA_whichCrlf();

	// Defines filename and extension, and also mime types
	$filename = $dbName;
	
	if ( $zip )
	{ 
		$ext       = 'zip';
	    $mime_type = 'application/x-zip';
	}
	else
	{
		$ext = 'sql';
		$mime_type = 'text';
	}    

// Builds the dump

// Gets the number of tables if a dump of a database has been required
    $tables     = mysql_list_tables($dbName);
    $num_tables = @mysql_numrows($tables);

// No table -> error message
	if ($num_tables == 0) 
	{
	    $dump_buffer = '# ' . $strNoTablesFound;
	}
	// At least on table -> do the work
	else 
	{
	    $dump_buffer       .= '# phpMyAdmin MySQL dump' . $crlf
	                       .  '# version ' . PMA_VERSION . $crlf
	                       .  '# http://phpwizard.net/phpMyAdmin/' . $crlf
	                       .  '# http://phpmyadmin.sourceforge.net/ (download page)' . $crlf
	                       .  '#' . $crlf
	                       .  '# ' . $strHost . ': ' . $cfgServer['host'];
	    if (!empty($cfgServer['port'])) 
	    {
	        $dump_buffer   .= ':' . $cfgServer['port'];
	    }
	    $formatted_db_name = (isset($use_backquotes)) ? PMA_backquote($dbName) : '\'' . $dbName . '\'';
	    $dump_buffer       .= $crlf 
	                       .  '# ' . $strGenTime . ': ' . PMA_localisedDate() . $crlf
	                       .  '# ' . $strServerVersion . ': ' . substr(PMA_MYSQL_INT_VERSION, 0, 1) . '.' . substr(PMA_MYSQL_INT_VERSION, 1, 2) . '.' . substr(PMA_MYSQL_INT_VERSION, 3) . $crlf
	                       .  '# ' . $strPHPVersion . ': ' . phpversion() . $crlf
	                       .  '# ' . $strDatabase . ': ' . $formatted_db_name . $crlf;
	
	    $i = 0;
	    if (isset($table_select)) 
	    {
	        $tmp_select = implode($table_select, '|');
	        $tmp_select = '|' . $tmp_select . '|';
	    }
	    while ($i < $num_tables) 
	    {        
	        $table = mysql_tablename($tables, $i);
	       
	        if (isset($tmp_select) && is_int(strpos($tmp_select, '|' . $table . '|')) == FALSE) 
	        {
	            $i++;
	        }
	        else 
	        {
				$formatted_table_name = (isset($use_backquotes)) ? PMA_backquote($table) : '\'' . $table . '\'';				
				$drop = ( $dropTable ? 'DROP TABLE IF EXISTS `'.$formatted_table_name.'`;'.$crlf : '' );
				// Displays table structure
	            if ($exportStructure) 
	            {
                	$dump_buffer.= '# --------------------------------------------------------' . $crlf
                    .  $crlf . '#' . $crlf
                    .  '# ' . $strTableStructure . ' ' . $formatted_table_name . $crlf
                    .  '#' . $crlf . $crlf
                    .  $drop
                    .  PMA_getTableDef($dbName, $table, $crlf, $err_url) . ';' . $crlf;
	            }
	            // At least data
	            if ($exportData) 
	            {
	            	$dump_buffer .= $crlf . '#' . $crlf
	                             .  '# ' . $strDumpingData . ' ' . $formatted_table_name . $crlf
	                             .  '#' . $crlf . $crlf;
	                $tmp_buffer  = '';
	                $limit_from = $limit_to = 0;
	                PMA_getTableContent($dbName, $table, $limit_from, $limit_to, 'PMA_myHandler', $err_url);
	                $dump_buffer .= $tmp_buffer;
	            } // end if
	            $i++;
	        } // end if-else
	    } // end while
	
		// Don't remove, it makes easier to select & copy from browser - staybyte
	    $dump_buffer .= $crlf;    
	} // end building the dump

	//header( 'Content-type: '.$mime_type );
	
	//$filename .= "-".date('Y-m-d').'.'.$ext;
	//header( 'Content-Disposition: attachment; filename="'.$zipName.'"' );

	$bWinOS = false;
	$strServerOS = strtolower($_SERVER["SERVER_SOFTWARE"]);
	if(strpos($strServerOS, "win") !== false && strpos($strServerOS, "unix") === false && strpos($strServerOS, "linux") === false){
		$bWinOS = true;
	}
	
    $output = $dump_buffer;
    $fecha=date("YmdHi");
    $fileName = "scriptsql_backup_$fecha";
    $zipName = $fileName . ".zip";

    $file = $fileName.".sql";
    $dirTmp = "./files/temp/";
    if ( $zip )
	{

        // write to file, so it can be zipped
        $fp = null;
        $fp = @fopen($dirTmp.$file, "w");
        @fwrite($fp, $output);
        @fclose($fp);

        if($bWinOS){
			$strDirToZip = "files\\temp\\$file";
			$strPath = "files\\temp\\$zipName";	
			$strZipCommand = "start /B lib\zip\zip.exe -jq $strPath $strDirToZip ";//windows
		}else{
			$strDirToZip = "files/temp/$file";
			$strPath = $dirTmp.$zipName;
			$strZipCommand = "./lib/zip/zip -jq $strPath $strDirToZip ";//linux	
		}
      

        exec($strZipCommand);

         //delete file
        @unlink($dirTmp.$file);
		
        if(file_exists($strPath)){
			$strFile = file_get_contents($strPath);
			//delete file
		    @unlink($strPath);
			
		    
		    header("Content-type:application/zip");
			$header = "Content-disposition: attachment; filename=\"$zipName\"";
		    header($header);
			header("Content-length: " . strlen($strFile));
			header("Content-transfer-encoding: binary");
			header("Pragma: no-cache");
			header("Expires: 0");
			print($strFile);
		
		}
        //ob_clean();
	}
	else 
	{
        $mime_type = 'text/sql';
        header('Content-Disposition: inline; filename="' . $file . '"');
        header('Content-Type: ' . $mime_type);
        // Write dump to file
       	$output = str_replace('&amp;', '&', $output);
	   	echo $output;
     }
}
?>