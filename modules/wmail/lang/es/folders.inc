<?php
include("./modules/wmail/lang/eng/folders.inc"); // <-- do not delete
/********************************************************
	lang/es/folders.inc
	(C)2001-2002 Ryo Chijiiwa <Ryo@IlohaMail.org>

	This file is part of IlohaMail, and released under GPL.
	See COPYING, or http://www.fsf.org/copyleft/gpl.html

********************************************************/

	$fl_str["folders"] = "Carpetas";
	$fl_str["manage"] = "Administrar Carpetas";

	$sentbox=$my_prefs["sent_box_name"];
	$trashname=$my_prefs["trash_name"];
	$defaults["INBOX"] = "Bandeja de Entrada";
	$defaults[$sentbox] = "Enviados";
	$defaults[$trashname] = "Basura";
	$fstr["reload"] = "recargar";
	$fstr["expunge"] = "vaciar";

?>