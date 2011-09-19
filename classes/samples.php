<?php
/***************************************************************************

iCalcreator class v0.6.0
originally (c) Kjell-Inge Gustafson
www.kigkonsult.se
ical@kigkonsult.se

Description:
  This file is a PHP implementation of RFC 2445.

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

****************************************************************************/
require_once 'iCalcreator.class.php';


$v = new vcalendar();                          // initiate new CALENDAR

$e = new vevent();                             // initiate a new EVENT
$e->setCategories( 'FAMILY' );                 // catagorize
$e->setDtstart( 2006, 12, 24, 19, 30, 00 );    // 24 dec 2006 19.30
$e->setDuration( 0, 0, 3 );                    // 3 hours
$e->setDescription( 'x-mas evening - diner' ); // describe the event
$e->setLocation( 'Home' );                     // locate the event

$v->addComponent( $e );                        // add component to calendar

// $v->returnCalendar();                          // generate and redirect output to user browser
$str = $v->createCalendar();                   // generate and get output in string, for testing?
echo $str;
echo "<br />\n\n";




$v = new vcalendar();                          // initiate new CALENDAR

$e = new vevent();                             // initiate EVENT
$e->setCategories( 'FAMILY' );                 // catagorize
$e->setDtstart( 2006, 12, 24, 19, 30, 00 );    // 24 dec 2006 19.30
$e->setDuration( 0, 0, 3 );                    // 3 hours
$e->setDescription( 'x-mas evening - diner' ); // describe the event
$e->setLocation( 'Home' );                     // locate the event

$a = new valarm();                             // initiate ALARM
$a->setAction( 'DISPLAY' );                    // set what to do
$a->setDescription( 'Buy X-mas gifts' );       // describe alarm
$a->setTrigger( FALSE, FALSE, FALSE, 1 );      // set trigger one week before

$e->addSubComponent( $a );                     // add alarm component to event component as subcomponent

$v->addComponent( $e );                        // add event component to calendar

$str = $v->createCalendar();                   // generate and get output in string, for testing?
echo $str;
echo "<br />\n\n";




$v = new vcalendar();                          // initiate new CALENDAR

$t = new vtimezone();                          // initiate TIMEZONE
$t->setTzid( 'US-Eastern');
$t->setLastModified( 1987, 1, 1 );

$ts = new vtimezone( 'standard' );
$ts->setDtstart( 1997, 10, 26, 2 );
$rdate1 = array ( 'year' => 1997, 'month' => 10, 'day' => 26, 'hour' => 02, 'min' => 0, 'sec' => 0 ); 
$ts->setRdate ( array( $rdate1 ));
$ts->setTzoffsetfrom( '-0400' );
$ts->setTzoffsetto( '-0500' );
$ts->setTzname( 'EST' );
$t->addSubComponent( $ts );

$td = new vtimezone( 'daylight' );
$td->setDtstart( 1997, 10, 26, 2 );
$rdate1 = array ( 'year' => 1997, 'month' => 4, 'day' => 6, 'hour' => 02, 'min' => 0, 'sec' => 0 ); 
$td->setRdate ( array( $rdate1 ));
$td->setTzoffsetfrom( '-0500' );
$td->setTzoffsetto( '-0400' );
$td->setTzname( 'EDT' );
$t->addSubComponent( $td );

$v->addComponent( $t );

// $v->returnCalendar();                          // generate and redirect output to user browser
$str = $v->createCalendar();                   // generate and get output in string, for testing?
echo $str;
echo "<br />\n\n";

/*
 *   Samples from RFC2445
 */

/*
 * Example: The following is an example of the "VEVENT" calendar
 * component used to represent a meeting that will also be opaque to
 * searches for busy time:
 *   BEGIN:VEVENT
 *   UID:19970901T130000Z-123401@host.com
 *   DTSTAMP:19970901T1300Z
 *   DTSTART:19970903T163000Z
 *   DTEND:19970903T190000Z
 *   SUMMARY:Annual Employee Review
 *   CLASS:PRIVATE
 *   CATEGORIES:BUSINESS,HUMAN RESOU\nRCES
 *   END:VEVENT
 */
$c = new vcalendar ();
$e = new vevent();
$e->setDtstart( '19970901T163000Z' );
$e->setDtend( '19970903T190000Z' );
$e->setSummary( 'Annual Employee Review' );
$e->setClass( 'PRIVATE' );
$e->setCategories( 'BUSINESS' );
$e->setCategories( 'HUMAN RESOURCES' );
$c->addComponent( $e );
$str = $c->createCalendar();
echo $str;

echo "<br />\n\n";
/*
 * The following is an example of the "VEVENT" calendar component used
 * to represent a reminder that will not be opaque, but rather
 * transparent, to searches for busy time:
 *
 *   BEGIN:VEVENT
 *   UID:19970901T130000Z-123402@host.com
 *   DTSTAMP:19970901T1300Z
 *   DTSTART:19970401T163000Z
 *   DTEND:19970402T010000Z
 *   SUMMARY:Laurel is in sensitivity awareness class.
 *   CLASS:PUBLIC
 *   CATEGORIES:BUSINESS,HUMAN RESOURCES
 *   TRANSP:TRANSPARENT
 *   END:VEVENT
 */

$c = new vcalendar ();
$e = new vevent();
$e->setDtstart( '19970401T163000Z' );
$e->setDtend( '19970402T010000Z' );
$e->setSummary( 'Laurel is in sensitivity awareness class.' );
$e->setClass( 'PUBLIC' );
$e->setCategories( 'BUSINESS' );
$e->setCategories( 'HUMAN RESOURCES' );
$e->setTransp( 'TRANSPARENT' );
$c->addComponent( $e );
$str = $c->createCalendar();
echo $str;

echo "<br />\n\n";
/*
 * The following is an example of the "VEVENT" calendar component used
 * to represent an anniversary that will occur annually. Since it takes
 * up no time, it will not appear as opaque in a search for busy time;
 * no matter what the value of the "TRANSP" property indicates:
 *
 *   BEGIN:VEVENT
 *   UID:19970901T130000Z-123403@host.com
 *   DTSTAMP:19970901T1300Z
 *   DTSTART:19971102
 *   SUMMARY:Our Blissful Anniversary
 *   CLASS:CONFIDENTIAL
 *   CATEGORIES:ANNIVERSARY,PERSONAL,SPECIAL OCCASION
 *   RRULE:FREQ=YEARLY
 *   END:VEVENT
 */

$c = new vcalendar ();
$e = new vevent();
$e->setDtstart( '19971102' );
$e->setSummary( 'Our Blissful Anniversary' );
$e->setClass( 'CONFIDENTIAL' );
$e->setCategories( 'ANNIVERSARY' );
$e->setCategories( 'PERSONAL' );
$e->setCategories( 'SPECIAL OCCASION' );
$e->setRrule( array( 'FREQ' => 'EARLY' ));
$c->addComponent( $e );
$str = $c->createCalendar();
echo $str;

echo "<br />\n\n";
echo "<br />\n\n";

echo "<br />\n\n";

/*
 *   BEGIN:VTODO
 *   UID:19970901T130000Z-123404@host.com
 *   DTSTAMP:19970901T1300Z
 *   DTSTART:19970415T133000Z
 *   DUE:19970416T045959Z
 *   SUMMARY:1996 Income Tax Preparation
 *   CLASS:CONFIDENTIAL
 *   CATEGORIES:FAMILY,FINANCE
 *   PRIORITY:1
 *   STATUS:NEEDS-ACTION
 *   END:VTODO
 */
$c = new vcalendar ();
$t = new vtodo();
$t->setDtstart( '19970415T133000 GMT' );
$t->setDue( '19970416T045959 GMT' );
$t->setSummary( '1996 Income Tax Preparation' );
$t->setClass( 'CONFIDENTIAL' );
$t->setCategories( 'FAMILY' );
$t->setCategories( 'FINANCE' );
$t->setPriority( 1 );
$t->setStatus( 'NEEDS-ACTION' );
$c->addComponent( $t );
$str = $c->createCalendar();
echo $str;

?>
