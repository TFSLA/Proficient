<?php
/////////////////////////////////////////////////////////
//	
//	include/data_manager.FS.inc
//
//	(C)Copyright 2003 Ryo Chijiiwa <Ryo@IlohaMail.org>
//
//	This file is part of IlohaMail. IlohaMail is free software released 
//	under the GPL license.  See enclosed file COPYING for details, or 
//	see http://www.fsf.org/copyleft/gpl.html
//
/////////////////////////////////////////////////////////

include("./modules/wmail/conf/conf.inc");
global  $DB_TYPE;
global  $backend;

if ($backend=="FS"){
	include("./modules/wmail/include/data_manager.FS.inc");
	$DB_TYPE = "FS";
}else{
	include_once("./modules/wmail/conf/db_conf.php");
	include_once("./modules/wmail/include/idba.$DB_TYPE.inc");
	include_once("./modules/wmail/include/data_manager.$DB_TYPE.inc");
}


?>