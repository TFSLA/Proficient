<?php
// user types

$traducir = $AppUI->user_locale == "es" ? TRUE :FALSE ;

if ( !$traducir )
{
	$utypes = array(
	// DEFAULT USER (nothing special)
		0 => '',
	// DO NOT CHANGE ADMINISTRATOR INDEX !
		1 => 'SUPERADMIN',
	// you can modify the terms below to suit your organisation
		2 => 'Employee',
		3 => 'Contracted',
		4 => 'Other',
		5 => 'Candidate'
	);
}
else
{
	$utypes = array(
	// DEFAULT USER (nothing special)
		0 => '',
	// DO NOT CHANGE ADMINISTRATOR INDEX !
		1 => 'SUPERADMIN',
	// you can modify the terms below to suit your organisation
		2 => 'Empleado',
		3 => 'Contratado',
		4 => 'Otro',
		5 => 'Candidato'
	);
}
natcasesort($utypes);

if($AppUI->user_type != 1){	
	unset($utypes[1]);
} 

##
##	NOTE: the user_type field in the users table must be changed to a TINYINT
##

$ustatus = array(
 0 => 'Active',
 1 => 'Inactive'
);

?>