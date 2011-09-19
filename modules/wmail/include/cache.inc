<?php
/////////////////////////////////////////////////////////
//	
//	include/cache.inc
//
//	(C)Copyright 2003 Ryo Chijiiwa <Ryo@IlohaMail.org>
//
//	This file is part of IlohaMail. IlohaMail is free software released 
//	under the GPL license.  See enclosed file COPYING for details, or 
//	see http://www.fsf.org/copyleft/gpl.html
//
/////////////////////////////////////////////////////////

include("./modules/wmail/conf/conf.inc");

if (($backend=="FS") || (empty($DB_CACHE_TABLE))){
	include("./modules/wmail/include/cache.FS.inc");
}else{
	include_once("./modules/wmail/conf/db_conf.php");
	include_once("./modules/wmail/include/idba.$DB_TYPE.inc");
	include_once("./modules/wmail/include/cache.DB.inc");
}


?>