<?php
global $dPconfig;
$locale_char_set = 'iso-8859-1';
// 0 = sunday, 1 = monday
define( 'LOCALE_FIRST_DAY', 0 );

if (!setlocale( LC_TIME, 'en'))
	setlocale( LC_TIME, '');

//el gantt utiliza las unidades de tiempo que se encuentran configuradas
$dPconfig['jpLocale'] = setlocale( LC_TIME, 0);

$AppUI->setConfig( $dPconfig );

$_DATE_TIMEZONE_DEFAULT='America/New_York';
$_DATE_TIMEZONE_DEFAULT='America/Buenos_Aires';
session_register("_DATE_TIMEZONE_DEFAULT");
?>