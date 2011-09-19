<?php
global $dPconfig;

$locale_char_set = 'iso-8859-15';
// 0 = sunday, 1 = monday
define( 'LOCALE_FIRST_DAY', 1 );
if (!setlocale( LC_TIME, 'spanish'))
	setlocale( LC_TIME, '');

//el gantt utiliza las unidades de tiempo que se encuentran configuradas	
$dPconfig['jpLocale'] = setlocale( LC_TIME, 0);
$AppUI->setConfig( $dPconfig );

$_DATE_TIMEZONE_DEFAULT='America/Buenos_Aires';
session_register("_DATE_TIMEZONE_DEFAULT");
?>