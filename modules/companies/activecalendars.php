<?php

global $m, $company_id, $calendar_types, $canEdit;
require_once( $AppUI->getModuleClass( 'system' ) );



$params["calendar_status"]="3";	
$params["calendar_company"]=$company_id;	

$calendars = CCalendar::getActiveCalendars(3, 24);

echo "<pre>";
var_dump($calendars);
echo "</pre>";

$calendars = ArraySort($calendars, 'calendar_from_date', SORT_DESC);

echo "<pre>";
var_dump($calendars);
echo "</pre>";


?>