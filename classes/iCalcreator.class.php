<?
/***************************************************************************

iCalcreator class v0.4.0
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

/**************************************************************************
*          A little setup                                                 *
**************************************************************************/
            // your local language code
// define( 'ICAL_LANG', 'sv' );
            // alt. autosetting
$langstr     = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
$pos         = strpos( $langstr, ';' );
if ($pos   !== false) { 
  $langstr   = substr( $langstr, 0, $pos );
  $pos       = strpos( $langstr, ',' );
  if ($pos !== false) { 
    $pos     = strpos( $langstr, ',' );
    $langstr = substr( $langstr, 0, $pos );
  }
  define( 'ICAL_LANG', $langstr );
}


            // version string, do NOT remove!!
define( 'ICALCREATOR_VERSION', 'iCalcreator 0.4.0' );

/**********************************************************************************
 **********************************************************************************
 * vcalendar class
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 */
class vcalendar {

  var $calscale;
  var $method;
  var $prodid;
  var $version;

/*
 *  container for calendar components
 */
  var $components;

  var $unique_id;
  var $language;
  var $directory;
  var $filename;

  var $nl;

/*
 * constructor for calendar object
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 */
  function vcalendar () {

  $this->_makeVersion();
  $this->calscale = null;
  $this->method   = null;
  $this->_makeUnique_id();
  $this->prodid   = null;

/*
 *   language = <Text identifying a language, as defined in [RFC 1766]>
 */
  $this->language = ICAL_LANG;

  $this->nl = "\n";

  $this->components = array();

  $this->directory  = null;
  $this->filename   = null;
  }
/**********************************************************************************
 * Property Name: CALSCALE
 */
/*
 * creates formatted output for calendar property calscale
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @return string
 */
  function createCalscale( ) {
    if( !isset( $this->calscale ))
      return;
    return 'CALSCALE:'.$this->calscale.$this->nl;
  }
/*
 * set calendar property calscale
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @param string $value
 * @return void
 */
  function setCalscale( $value ) {
    $this->calscale = $value;
  }
/**********************************************************************************
 * Property Name: METHOD
 */
/*
 * creates formatted output for calendar property method
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @return string
 */
  function createMethod( ) {
    if( !isset( $this->method ))
      return;
    return 'METHOD:'.$this->Method.$this->nl;
  }
/*
 * set calendar property method
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @param string $value
 * @return void
 */
  function setMethod( $method ) {
    $this->method = $method;
  }

/**********************************************************************************
 * Property Name: PRODID
 *
 *  The identifier is RECOMMENDED to be the identical syntax to the
 * [RFC 822] addr-spec. A good method to assure uniqueness is to put the
 * domain name or a domain literal IP address of the host on which.. .
 */
/*
 * creates formatted output for calendar property prodid
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @return string
 */
  function createProdid( ) {
    if( !isset( $this->method ))
      $this->_makeProdid();
    return 'PRODID:'.$this->prodid.$this->nl;
  }
/*
 * make default value for calendar prodid
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function _makeProdid() {
    $this->prodid  = '-//'.$this->unique_id.'//NONSGML '.ICALCREATOR_VERSION.'//'.strtoupper( $this->language );
  }
/*
 * make default unique_id for calendar prodid
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function _makeUnique_id() {
    $this->unique_id  = gethostbyname( $_SERVER['SERVER_NAME'] );
  }

/* .. .
 * Conformance: The property MUST be specified once in an iCalendar object.
 * Description: The vendor of the implementation SHOULD assure that this
 * is a globally unique identifier; using some technique such as an FPI
 * value, as defined in [ISO 9070].
 */
/*
 * set unique_id
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string unique_id
 * @return void
 */
  function setUnique_id( $unique_id ) {
    $this->unique_id = $unique_id;
  }

/**********************************************************************************
 * Property Name: VERSION
 *
 * Description: A value of "2.0" corresponds to this memo.
 */
/*
 * creates formatted output for calendar property version
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @return string
 */
  function createVersion( ) {
    if( !isset( $this->version ))
      $this->_makeVersion();
    return 'VERSION:'.$this->version.$this->nl;
  }
/*
 * set default calendar version
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function _makeVersion() {
    $this->version = '2.0';
  }
/*
 * set calendar version
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string version
 * @return void
 */
  function setVersion( $version ) {
    $this->version = $version;
  }

/**********************************************************************************/
/*
 * add calendar component to container
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param object $component calendar component
 * @return void
 */
  function addComponent ( $component ) {
    $this->components[] = $component;
  }

/*
 * creates formatted output for calendar object instance
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createCalendar ( ) {
    $calendar   = null;

    $calendar .= 'BEGIN:VCALENDAR'.$this->nl;
    $calendar .= $this->createVersion();
    $calendar .= $this->createProdid();
    $calendar .= $this->createCalscale();
    $calendar .= $this->createMethod();

    foreach( $this->components as $component ) {
      if( !isset( $component->language ))
        $component->language  = $this->language;
      if( !isset( $component->nl ))
        $component->nl        = $this->nl;
      if( !isset( $component->unique_id ))
        $component->setUnique_id( $this->unique_id );

      $calendar .= $component->createComponent();
    }

    $calendar .= 'END:VCALENDAR'.$this->nl;

    return $calendar;
  }

/*
 * get filename
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-11
 * @return array
 *
 */
  function getFilename() {
    if( !$this->filename )
      $this->setFilename( $this->directory, $this->_makeFilename() );
    $dirfile = $this->directory.'/'.$this->filename;
    $filesize = filesize( $dirfile );
    return array( $this->directory, $this->filename, $filesize );
  }
/*
 * make filename
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-11
 * @return void
 *
 */
  function _makeFilename() {
    $this->filename = date( 'YmdHis' ).'.ics';
  }

/*
 * redirect file to user
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string directory
 * @param string filename
 * @return redirect
 */
  function _redirectCalendar ( $directory=FALSE, $filename=FALSE ) {
    $dirfile = $this->directory.'/'.$this->filename;
    Header( 'Content-Type: text/calendar; charset=utf-8' );
    Header( 'Content-Disposition: attachment; filename='.basename( $dirfile ));
    readfile( $dirfile, 'r' );

    die();
  }
/*
 * an HTTP redirect header is sent with saved calendar
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string directory
 * @param string filename
 * @return redirect
 */
  function returnCalendar ( $directory=FALSE, $filename=FALSE ) {
    if( $this->saveCalendar ( $directory, $filename ))
      $this->_redirectCalendar ( $directory, $filename );
  }

/*
 * save content in a file
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-11
 * @param string directory
 * @param string filename
 * @return array
 */
  function saveCalendar ( $directory=FALSE, $filename=FALSE ) {
    if( $directory || $filename )
      $this->setFilename( $directory, $filename ); 
    elseif( !$this->filename )
      $this->setFilename();

    $dirfile = $this->directory.'/'.$this->filename;

    $iCalFile = fopen( $dirfile, 'w+' );
    if ( $iCalFile ) {
      fputs( $iCalFile, $this->createCalendar() );
      fclose( $iCalFile );
      $filesize = filesize( $dirfile );
      return array( $this->directory, $this->filename, $filesize );
    }
    else
      return FALSE;
  }

/*
 * set filename
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string directory
 * @param string filename
 * @return bool
 */
  function setFilename ( $directory=FALSE, $filename=FALSE ) {
    if( $directory )
      $this->directory = $directory;
    else
      $this->directory = '.';
    if( $filename )
      $this->filename = $filename;
    else
      $this->_makeFilename();

    $dirfile = $this->directory.'/'.$this->filename;
    if( @touch( $dirfile ))
      return TRUE;
    else
      return FALSE;
  }

/*
 * if recent version of file exists (max one hour), an HTTP redirect header is sent
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string directory
 * @param string filename
 * @param int timeout
 * @return bool
 */
  function useCachedCalendar( $directory, $filename, $timeout=3600) {
    $dirfile = $this->directory.'/'.$this->filename;
    if(( file_exists( $dirfile )) && 
       ( time() - filemtime( $dirfile ) < $timeout)) {
      $this->_redirectCalendar ( $directory, $filename );
    }
  }
}

/**********************************************************************************
 **********************************************************************************
 *  abstract class for calendar components
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 */
class calendarComponent {
  var $action;
  var $attach;
  var $attendee;
  var $categories;
  var $comment;
  var $completed;
  var $contact;
  var $class;
  var $created;
  var $description;
  var $dtend;
  var $dtstart;
  var $dtstamp;
  var $due;
  var $duration;
  var $exdate;
  var $exrule;
  var $geo;
  var $lastmodified;
  var $location;
  var $organizer;
  var $percentcomplete;
  var $priority;
  var $rdate;
  var $recurrenceid;
  var $relatedto;
  var $repeat;
  var $requeststatus;
  var $resources;
  var $rrule;
  var $sequence;
  var $status;
  var $summary;
  var $transp;
  var $trigger;
  var $tzid;
  var $tzname;
  var $tzoffsetfrom;
  var $tzoffsetto;
  var $tzurl;
  var $uid;
  var $url;
  var $xprop;

  var $subcomponents;

  var $nl;
  var $unique_id;

/*
 * constructor for calendar component object
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 */
  function calendarComponent() {
    $this->action          = null;
    $this->attach          = array();
    $this->attendee        = array();
    $this->categories      = array();
    $this->class           = null;
    $this->comment         = array();
    $this->completed       = array();
    $this->contact         = array();
    $this->created         = array();
    $this->description     = array();
    $this->dtend           = array();
    $this->dtstart         = array();
    $this->dtstamp         = array();
    $this->due             = array();
    $this->duration        = array();
    $this->exdate          = array();
    $this->exrule          = array();
    $this->geo             = array();
    $this->lastmodified    = array();
    $this->location        = array();
    $this->organizer       = array();
    $this->percentcomplete = null;
    $this->priority        = null;
    $this->rdate           = array();
    $this->recurrenceid    = array();
    $this->relatedto       = array();
    $this->repeat          = null;
    $this->requeststatus   = array();
    $this->resources       = array();
    $this->sequence        = null;
    $this->rrule           = array();
    $this->status          = null;
    $this->summary         = array();
    $this->transp          = null;
    $this->trigger         = array();
    $this->tzid            = null;
    $this->tzname          = null;
    $this->tzoffsetfrom    = null;
    $this->tzoffsetto      = null;
    $this->tzurl           = null;
    $this->uid             = null;
    $this->url             = null;
    $this->xprop           = array();

    $this->subcomponents   = array();

    $this->nl              = null;
    $this->unique_id       = null;

    $this->_makeDtstamp();
  }


/**********************************************************************************
 * Property Name: ACTION
 *
 * Conformance: This property MUST be specified once in a "VALARM"
 * calendar component.
 */
/*
 * creates formatted output for calendar component property action
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createAction( ) {
    if( !isset( $this->action ))
      return;
    return 'ACTION:'.$this->action.$this->nl;
  }
/*
 * set calendar component property action 
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value  "AUDIO" / "DISPLAY" / "EMAIL" / "PROCEDURE"
 * @return void
 */
  function setAction( $value ) {
    $this->action = $value;
  }

/**********************************************************************************
 * Property Name: ATTACH
 *
 * Conformance: The property can be specified in a "VEVENT", "VTODO",
 * "VJOURNAL" or "VALARM" calendar components.
 *
 * This property can be specified multiple times within an iCalendar object.
 *
 */
/*
 * creates formatted output for calendar component property attach
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createAttach( ) {
    $cnt = count( $this->attach );
    if( 0 >= $cnt )
      return;
    $attachments = null;
    foreach( $this->attach as $attachPart ) {
      $attach    = 'ATTACH';
      if( isset( $attachPart['FMTYPE'] )) {
        $attach .= ';FMTYPE='.$attachPart['FMTYPE'];
        unset( $attachPart['FMTYPE'] );
      }
      if( isset( $attachPart['ENCODING'] )) {
        $attach .= ';ENCODING=BASE64;VALUE=BINARY';
        unset( $attachPart['ENCODING'] );
      }
      $attach   .= ':';
      $attach   .= reset( $attachPart );
      $attachments .= $this->_size75( $attach );
    }
    return $attachments;
  }
/*
 * set calendar component property attach
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @param string $fmtype
 * @return void
 */
  function setAttach( $value, $fmtype=FALSE, $encoding=FALSE ) {
    $attach = array( $value );
    if( $fmtype )
      $attach['FMTYPE'] = $fmtype;
    if( $encoding )
      $attach['ENCODING'] = TRUE;
    $this->attach[] = $attach;
  }

/**********************************************************************************
   Property Name: ATTENDEE

   Conformance: This property MUST be specified in an iCalendar object
   that specifies a group scheduled calendar entity. This property MUST
   NOT be specified in an iCalendar object when publishing the calendar
   information (e.g., NOT in an iCalendar object that specifies the
   publication of a calendar user's busy time, event, to-do or journal).
   This property is not specified in an iCalendar object that specifies
   only a time zone definition or that defines calendar entities that
   are not group scheduled entities, but are entities only on a single
   user's calendar.
 *

   Multiple attendees can be specified by including multiple "ATTENDEE"
   properties within the calendar component.


 */
/*
 * creates formatted output for calendar component property attendee
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createAttendee( ) {
    $cnt = count( $this->attendee );
    if( 0 >= $cnt )
      return;
    $attendees = null;
    foreach( $this->attendee as $attendeePart ) {                      // start foreach 1
      $attendee1 = 'ATTENDEE';
      $attendee2 = null;
      foreach( $attendeePart as $paramlabel => $paramvalue ) {         // start foreach 2
        if( 'value' == $paramlabel ) {
          $attendee2  .= ':MAILTO:'.$paramvalue;
        }
        elseif(( 'optparam' == $paramlabel ) && ( is_array( $paramvalue ))) { 
          foreach( $paramvalue as $optparam ) {                        // start freach 3
            foreach( $optparam as $optparamlabel => $optparamvalue ) { // start freach 4
              $attendee11 = $attendee12 = null;
               // echo "$optparamlabel => $optparamvalue <be />\n"; // test 
              switch( $optparamlabel ) {                               // start switch
                case 'CUTYPE':
                  $attendee1 .= ';CUTYPE='.'"'  .$optparamvalue.'"'; break;
                case 'ROLE':
                  $attendee1 .= ';ROLE='.'"'    .$optparamvalue.'"'; break;
                case 'PARTSTAT':
                  $attendee1 .= ';PARTSTAT='.'"'.$optparamvalue.'"'; break;
                case 'RSVP':
                  $attendee1 .= ';RSVP='.'"'    .$optparamvalue.'"'; break;
                case 'SENT-BY':
                  $attendee1 .= ';SENT-BY='.'"' .$optparamvalue.'"'; break;
                case 'MEMBER':
                  $attendee11 = ';MEMBER=';
                case 'DELEGATED-TO':
                  $attendee11 = ( !$attendee11 ) ? ';DELEGATED-TO='   : $attendee11;
                case 'DELEGATED-FROM': {
                  $attendee11 = ( !$attendee11 ) ? ';DELEGATED-FROM=' : $attendee11;
                  foreach( $optparamvalue  as $cix => $calUserAddress ) {
                    if( $cix )
                      $attendee12 .= ',';
                    $attendee12 .= '"MAILTO:'.$calUserAddress.'"';
                  }
                  $attendee1  .= $attendee11.$attendee12;
                  break;
                }
                case 'CN': {
                   $attendee1 .= ';CN='.$optparamvalue;
                   if( isset( $this->language ))
                     $attendee1 .= ';LANGUAGE='.$this->language;
                   break;
                }
                case 'DIR': {
                   $attendee1 .= ';DIR="'.$optparamvalue.'"';
                   break;
                }
              }    // end switch
            }      // end foreach 4
          }        // end foreach 3
        }          // end elseif
      }            // end foreach 2
      $attendees .= $this->_size75( $attendee1.$attendee2 );
    }              // end foreach 1
    return $attendees;
  }
/*
 * set calendar component property attach
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @param string $optparamlabel
 * @param mixed $optparamvalue
 * @return void
 */
  function setAttendee( $value, $optparam=FALSE ) {
    $attendee = array( 'value' => $value );
    if( is_array($optparam )) {
      foreach( $optparam as $optparamlabel => $optparamvalue )
        $attendee['optparam'][] = array( strtoupper( $optparamlabel ) => $optparamvalue );
    }
    $this->attendee[] = $attendee;
  }

/**********************************************************************************
 * Property Name: CATEGORIES
 *
 * Conformance: The property can be specified within "VEVENT", "VTODO" or "VJOURNAL" 
 * calendar components.
 *
 * . ..more than one category can be specified as a list of categories separated by 
 * the COMMA character (US-ASCII decimal 44).
 */
/*
 * creates formatted output for calendar component property categories
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createCategories( ) {
    $cnt = count( $this->categories );
    if( 0 >= $cnt )
      return;
    $categories  = 'CATEGORIES';
    if( isset( $this->language ))
      $categories .= ';LANGUAGE='.$this->language;
    $categories .= ':';
    $oix = 1;
    foreach( $this->categories as $category ) {
      $categories .= $category;
      if( $oix < $cnt )
        $categories .= ',';
      $oix++;
    }

    return $this->_size75( $categories );
  }
/*
 * set calendar component property categories
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @return void
 */
  function setCategories( $value ) {
    $this->categories[] = $value;
  }


/**********************************************************************************
 * Property Name: CLASS
 *
 * Conformance: The property can be specified once in a "VEVENT",
 * "VTODO" or "VJOURNAL" calendar components.
 *
 */
/*
 * creates formatted output for calendar component property class
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createClass( ) {
    if( !isset( $this->class ))
      return;
    return 'CLASS:'.$this->class.$this->nl;
  }
/*
 * set calendar component property class
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value "PUBLIC" / "PRIVATE" / "CONFIDENTIAL" / iana-token / x-name
 * @return void
 */
  function setClass( $value ) {
    $this->class = $value;
  }


/**********************************************************************************
 * Property Name: COMMENT
 *
 * Conformance: This property can be specified in "VEVENT", "VTODO",
 * "VJOURNAL", "VTIMEZONE" or "VFREEBUSY" calendar components.
 *
 * Description: The property can be specified multiple times.
 */
/*
 * creates formatted output for calendar component property comment
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createComment( ) {
    $cnt = count( $this->comment );
    if( 0 >= $cnt )
      return;
    $comment = null;
    foreach( $this->comment as $commentPart ) {
      $label     = 'COMMENT';
      if( isset( $this->language )) {
        $label .= ';LANGUAGE='.$this->language;
      }
      $comment  .= $this->_size75( $label.':'.$this->_strrep( $commentPart ));
    }
    return $comment;
  }
/*
 * set calendar component property comment
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @return void
 */
  function setComment( $value ) {
    $this->comment[] = $value;
  }


/**********************************************************************************
 * Property Name: COMPLETED
 *
 * Conformance: The property can be specified in a "VTODO" calendar
 * component.
 */
/*
 * creates formatted output for calendar component property completed
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.4.1 - 2006-08-18
 * @return string
 */
  function createCompleted( ) {
    if( !isset( $this->completed['year'] )  &&
        !isset( $this->completed['month'] ) &&
        !isset( $this->completed['day'] )   &&
        !isset( $this->completed['hour'] )  &&
        !isset( $this->completed['min'] )   &&
        !isset( $this->completed['sec'] ))
      return;
    $formatted = $this->_format_date_time( $this->completed );
    return 'COMPLETED:'.$formatted[0].$this->nl;
  }
/*
 * set calendar component property completed
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.4.1 - 2006-08-18
 * @param mixed $year
 * @param int $month
 * @param int $day
 * @param int $hour
 * @param int $min
 * @param int $sec
 * @param mixed $tz
 * @return void
 */
  function setCompleted( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $tz=FALSE ) {
    if( is_array( $year ) && 
      (( 6 == count( $year )) ||
       ( 7 == count( $year )) ||
       ( array_key_exists( 'year', $year )))) {
      $this->completed = $this->_date_time_array( $year );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->completed = $this->_date_time_string( $year );
    }
    else {
      $this->completed = array('year'  => $year,
                               'month' => $month,
                               'day'   => $day,
                               'hour'  => $hour,
                               'min'   => $min,
                               'sec'   => $sec,
                               'tz'    => $sec);
    }
  }

/**********************************************************************************
 * Property Name: CONTACT
 */
/*
 * creates formatted output for calendar component property contact
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createContact( ) {
    $cnt = count( $this->contact );
    if( 0 >= $cnt )
      return;
    $label  = 'CONTACT';
    if( isset( $this->language )) {
      $label .= ';LANGUAGE='.$this->language;
    }
    if( isset( $this->contact['ALTREP'] )) {
      $label .= ';ALTREP="'.$this->contact['ALTREP'].'"';
      unset( $this->contact['ALTREP'] );
      $cnt--;
    }
    $contact = null;
    $cno = 1;
    foreach( $this->contact as $contactPart ) {
      $contact .= $contactPart;
      if( $cno < $cnt )
        $contact .= ',';
      $cno++;
    }

    return $this->_size75( $label.':'.$this->_strrep( $contact ));
  }
/*
 * set calendar component property contact
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @param string $altrep
 * @return void
 */
  function setContact( $value, $altrep=FALSE ) {
    $this->contact[] = $value;
    if( $altrep )
      $this->contact['ALTREP'] = $altrep;
  }

/**********************************************************************************
 * Property Name: CREATED
 *
 * Conformance: The property can be specified once in "VEVENT", "VTODO"
 * or "VJOURNAL" calendar components.
 *
 */
/*
 * creates formatted output for calendar component property created
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.4.1 - 2006-08-18
 * @return string
 */
  function createCreated( ) {
    if( !isset( $this->created['year'] )  &&
        !isset( $this->created['month'] ) &&
        !isset( $this->created['day'] )   &&
        !isset( $this->created['hour'] )  &&
        !isset( $this->created['min'] )   &&
        !isset( $this->created['sec'] ))
      return;
    $formatted = $this->_format_date_time( $this->created );
    return 'CREATED:'.$formatted[0].$this->nl;
  }
/*
 * set calendar component property created
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.4.1 - 2006-08-18
 * @param int $year
 * @param int $month
 * @param int $day
 * @param int $hour
 * @param int $min
 * @param int $sec
 * @param mixed $tz
 * @return void
 */
  function setCreated( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $tz=FALSE ) {
    if( is_array( $year ) && 
      (( 6 == count( $year )) ||
       ( 7 == count( $year )) ||
       ( array_key_exists( 'year', $year )))) {
      $this->created = $this->_date_time_array( $year, 7 );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->created = $this->_date_time_string( $year );
    }
    else {
      $this->created = array( 'year'  => $year
                            , 'month' => $month
                            , 'day'   => $day
                            , 'hour'  => $hour
                            , 'min'   => $min
                            , 'sec'   => $sec
                            , 'tz'    => $tz );
    }
  }


/**********************************************************************************
 * Property Name: DESCRIPTION
 *
 *  Conformance: The property can be specified in the "VEVENT", "VTODO",
 * "VJOURNAL" or "VALARM" calendar components. The property can be
 * specified multiple times only within a "VJOURNAL" calendar component.
 *
 * todo "VTODO" "VJOURNAL" or "VALARM"
 */
/*
 * creates formatted output for calendar component property description
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createDescription( ) {
    $cnt = count( $this->description );
    if( 0 >= $cnt )
      return;

    $descriptions    = null;
    foreach( $this->description as $descriptionPart ) {
      $label         = 'DESCRIPTION';
      if( isset( $this->language )) {
        $label .= ';LANGUAGE='.$this->language;
      }
      if( isset( $descriptionPart['ALTREP'] )) {
        $label .= ';ALTREP="'.$descriptionPart['ALTREP'].'"';
        unset( $descriptionPart['ALTREP'] );
      }
      $description   = null;
      $description  .= reset( $descriptionPart );
      $descriptions .= $this->_size75( $label.':'.$this->_strrep( $description ));
    }
    return $descriptions;
  }
/*
 * set calendar component property description
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @param string $altrep
 * @return void
 */
  function setDescription( $value, $altrep=FALSE ) {
    if( $altrep )
      $this->description[] = array( $value, 'ALTREP' => $altrep );
    else
      $this->description[] = array( $value );
  }


/**********************************************************************************
 * Property Name: DTEND
 *
 * Conformance: This property can be specified in "VEVENT" or "VFREEBUSY" components
 *
 */
/*
 * creates formatted output for calendar component property dtend
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.4.1 - 2006-08-18
 * @return string
 */
  function createDtend( ) {
    if( !isset( $this->dtend['year'] )  &&
        !isset( $this->dtend['month'] ) &&
        !isset( $this->dtend['day'] )   &&
        !isset( $this->dtend['hour'] )  &&
        !isset( $this->dtend['min'] )   &&
        !isset( $this->dtend['sec'] ))
      return;
    $formatted = $this->_format_date_time( $this->dtend );
    if( isset( $formatted[2] ))
      $formatted[1] = null;
    return 'DTEND'.$formatted[2].$formatted[1].':'.$formatted[0].$this->nl;
  }
/*
 * set calendar component property dtend
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.4.1 - 2006-08-18
 * @param int $year
 * @param int $month
 * @param int $day
 * @param int $hour
 * @param int $min
 * @param int $sec
 * @param string $tz
 * @return void
 */
  function setDtend( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $tz=FALSE ) {
    if( is_array( $year ) && 
      (( 6 == count( $year )) ||
       ( 7 == count( $year )) ||
       ( array_key_exists( 'year', $year )))) {
      $parno = count( $year );
      $this->dtend = $this->_date_time_array( $year, $parno );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->dtend = $this->_date_time_string( $year );
    }
    else {
      $this->dtend = array('year'  => $year,
                           'month' => $month,
                           'day'   => $day );
      if ( $hour || $min || $sec || $tz ) {
        $this->dtend['hour'] = $hour;
        $this->dtend['min']  = $min;
        $this->dtend['sec']  = $sec;
        $this->dtend['tz']   = $tz;
      }
    }
  }

/**********************************************************************************
 * Property Name: DTSTAMP
 *
 * Conformance: This property MUST be included in the "VEVENT", "VTODO",
 * "VJOURNAL" or "VFREEBUSY" calendar components.
 */
/*
 * creates formatted output for calendar component property dtstamp
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createDtstamp( ) {
    if( !isset( $this->dtstamp['year'] )  &&
        !isset( $this->dtstamp['month'] ) &&
        !isset( $this->dtstamp['day'] )   &&
        !isset( $this->dtstamp['hour'] )  &&
        !isset( $this->dtstamp['min'] )   &&
        !isset( $this->dtstamp['sec'] ))
      $this->_makeDtstamp();
    $formatted = $this->_format_date_time( $this->dtstamp, 7 );
    return 'DTSTAMP:'.$formatted[0].$this->nl;
  }
/*
 * computes datestamp for calendar component object instance creation
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function _makeDtstamp() {
    $this->dtstamp = array( 'year'  => date( 'Y' ), 
                            'month' => date( 'm' ), 
                            'day'   => date( 'd' ), 
                            'hour'  => date( 'H' ), 
                            'min'   => date( 'i' ), 
                            'sec'   => date( 's' ), 
                            'tz'    => date( 'Z' ));
  }

/**********************************************************************************
 * Property Name: DTSTART
 *
 * Conformance: This property can be specified in the "VEVENT", "VTODO",
 * "VFREEBUSY", or "VTIMEZONE" calendar components.
 *
 */
/*
 * creates formatted output for calendar component property dtstart
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.4.1 - 2006-08-18
 * @return string
 */
  function createDtstart( $localtime=FALSE ) {
    if( !isset( $this->dtstart['year'] )  &&
        !isset( $this->dtstart['month'] ) &&
        !isset( $this->dtstart['day'] )   &&
        !isset( $this->dtstart['hour'] )  &&
        !isset( $this->dtstart['min'] )   &&
        !isset( $this->dtstart['sec'] ))
      return;
    $formatted = $this->_format_date_time( $this->dtstart, $localtime );
    if( isset( $formatted[2] ))
      $formatted[1] = null;
    return 'DTSTART'.$formatted[2].$formatted[1].':'.$formatted[0].$this->nl;
  }
/*
 * set calendar component property dtstart
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.4.1 - 2006-08-18
 * @param int $year
 * @param int $month
 * @param int $day
 * @param int $hour
 * @param int $min
 * @param int $sec
 * @param string $tz
 * @return void
 */
  function setDtstart( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $tz=FALSE ) {
    if( is_array( $year ) && 
      (( 6 == count( $year )) || ( 7 == count( $year )) ||
       ( array_key_exists( 'year', $year )))) {
      $parno = count( $year );
      $this->dtstart = $this->_date_time_array( $year, $parno );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->dtstart = $this->_date_time_string( $year );
    }
    else {
      $this->dtstart = array('year'  => $year,
                           'month' => $month,
                           'day'   => $day );
      if ( $hour || $min || $sec ) {
        $this->dtstart['hour'] = $hour;
        $this->dtstart['min']  = $min;
        $this->dtstart['sec']  = $sec;
        $this->dtstart['yz']   = $tz;
      }
    }
  }

/**********************************************************************************
 * Property Name: DUE
 *
 * Conformance: The property can be specified once in a "VTODO" calendar
 * component.
 *
 */
/*
 * creates formatted output for calendar component property due
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createDue( ) {
    if( !isset( $this->due['year'] )  &&
        !isset( $this->due['month'] ) &&
        !isset( $this->due['day'] )   &&
        !isset( $this->due['hour'] )  &&
        !isset( $this->due['min'] )   &&
        !isset( $this->due['sec'] ))
      return;
    $formatted = $this->_format_date_time( $this->due );
    if( isset( $formatted[2] ))
      $formatted[1] = null;
    return 'DUE'.$formatted[2].$formatted[1].':'.$formatted[0].$this->nl;
  }
/*
 * set calendar component property due
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param int $year
 * @param int $month
 * @param int $day
 * @param int $hour
 * @param int $min
 * @param int $sec
 * @return void
 */
  function setDue( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $tz=FALSE ) {
    if( is_array( $year ) && 
      (( 6 == count( $year )) || ( 7 == count( $year )) ||
       ( array_key_exists( 'year', $year )))) {
      $parno = count( $year );
      $this->due = $this->_date_time_array( $year, $parno );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->due = $this->_date_time_string( $year );
    }
    else {
      $this->due = array('year'  => $year,
                         'month' => $month,
                         'day'   => $day );
      if ( $hour || $min || $sec ) {
        $this->due['hour'] = $hour;
        $this->due['min']  = $min;
        $this->due['sec']  = $sec;
        $this->due['tz']   = $tz;
      }
    }
  }

/**********************************************************************************
 * Property Name: DURATION
 *
 * Conformance: The property can be specified in "VEVENT", "VTODO",
 * "VFREEBUSY" or "VALARM" calendar components.
 */
/*
 * creates formatted output for calendar component property duration
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createDuration( ) {
    if( !isset( $this->duration['week'] ) &&
        !isset( $this->duration['day'] )  &&
        !isset( $this->duration['hour'] ) &&
        !isset( $this->duration['min'] )  &&
        !isset( $this->duration['sec'] ))
      return;
    $duration  = 'DURATION:'.$this->_format_duration( $this->duration );
    return $duration.$this->nl;;
  }
/*
 * set calendar component property duration
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param int $week
 * @param int $day
 * @param int $hour
 * @param int $min
 * @param int $sec
 * @return void
 */
  function setDuration( $week=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE ) {
    if( is_array( $week ))
      $this->duration = $this->_duration_array( $week );
    else
      $this->duration = $this->_duration_array( array( $week, $day, $hour, $min, $sec ));
  }

/**********************************************************************************
 * Property Name: EXDATE
 *
 * Conformance: This property can be specified in an iCalendar object
 * that includes a recurring calendar component.
 *
 */
/*
 * creates formatted output for calendar component property exdate
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createExdate( ) {
    $cnt = count( $this->exdate );
    if( 0 >= $cnt )
      return;
    $output = null;
    foreach( $this->exdate as $theExdate ) {
      $cnt = count( $theExdate );
      $exdate = 'EXDATE';
      $eno = 1;
      foreach( $theExdate as $exdatePart ) {
        $formatted = $this->_format_date_time( $exdatePart );
        if( 1 == $eno ) {
          if (isset( $formatted[2] ))
            $formatted[1] = null;
          $exdate .= $formatted[2].$formatted[1].':';
        }
        $exdate .= $formatted[0];
        if( $eno < $cnt )
          $exdate .= ',';
        $eno++;
      }
      $output .= $this->_size75( $exdate );
    }
    return $output;
  }
/*
 * set calendar component property exdate
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param int $year
 * @param int $month
 * @param int $day
 * @param int $hour
 * @param int $min
 * @param int $sec
 * @return void
 */
  function setExdate( $exdates ) {
    $exdate = array();
    $paramcnt = null;
    foreach( $exdates as $theExdate ) {
      if( is_array( $theExdate ) && 
        (( 3 == count( $theExdate )) ||
         ( 6 == count( $theExdate )) ||
         ( 7 == count( $theExdate )) ||
         ( array_key_exists( 'year', $theExdate )))) {
        if( 6 == count( $theExdate ))
          $paramcnt = 7;
        $exdatea = $this->_date_time_array( $theExdate, $paramcnt );
      }
      elseif( 8 <= strlen( trim( $theExdate ))) { // ex. 2006-08-03 10:12:18
        $exdatea = $this->_date_time_string( $theExdate, $paramcnt );
      }
      if( !$paramcnt ) {
        $paramcnt = count( $exdatea );
        if( 6 == $paramcnt )
          $paramcnt = 7;
      }
      $exdate[] = $exdatea;
    }
    if( 0 < count( $exdate ))
      $this->exdate[] = $exdate;
  }

/**********************************************************************************
 * Property Name: EXRULE
 *
 * Conformance: This property can be specified in "VEVENT", "VTODO" or
 * "VJOURNAL" calendar components.
 */
/*
 * creates formatted output for calendar component property exrule
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-11
 * @return string
 */
  function createExrule( ) {
    $cnt = count( $this->exrule );
    if( 0 >= $cnt )
      return;

    return $this->_format_recur( 'EXRULE', $this->exrule );
  }
/*
 * set calendar component property exdate
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param array $exruleset
 * @return void
 */
  function setExrule( $exruleset ) {
    $exrule = array();
    foreach( $exruleset as $exrulelabel => $exrulevalue ) {
      $exrulelabel = strtoupper( $exrulelabel );
      if( 'UNTIL'  != $exrulelabel )
        $exrule[$exrulelabel] = $exrulevalue;
      elseif( is_array( $exrulevalue ) && 
            (( 3 == count( $exrulevalue )) ||
             ( 6 == count( $exrulevalue )) ||
             ( 7 == count( $exrulevalue )) ||
             ( array_key_exists( 'year', $exrulevalue )))) {
        $parno = ( 3 < count( $exrulevalue )) ? 7 : 3 ;
        $exrule[$exrulelabel] = $this->_date_time_array( $exrulevalue, $parno );
      }
      elseif( 8 <= strlen( trim( $exrulevalue ))) { // ex. 2006-08-03 10:12:18
        $exrule[$exrulelabel] = $this->_date_time_string( $exrulevalue );
      }
    }
    $this->exrule[] = $exrule;
  }


/**********************************************************************************
 * Property Name: FREEBUZY
 *
 * Conformance: The property can be specified in a "VFREEBUSY" calendar
 * component.
 */
/*
 * creates formatted output for calendar component property freebuzy
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @return string
 */
  function createFreebuzy( ) {
    $cnt = count( $this->freebuzy );
    if( 0 >= $cnt )
      return;
    $output = null;
    foreach( $this->freebuzy as $freebuzyPart ) {
      $outputPart  = 'FREEBUZY';
      if( isset( $freebuzyPart['fbtype'] )) {
        $outputPart .= ';'.$freebuzyPart['fbtype'];
        unset( $freebuzyPart['fbtype'] );
      }
      $outputPart .= ':';
      $fno = 1;
      $cnt = count( $freebuzyPart);
      foreach( $freebuzyPart as $periodix => $freebuzyPeriod ) {
          $formatted   = $this->_format_date_time( $freebuzyPeriod[0] );
          $outputPart .= $formatted[0];
          $outputPart .= '/';
          $cnt2 = count( $freebuzyPeriod[1]);
          if( array_key_exists( 'year', $freebuzyPeriod[1] ))      // date-time
            $cnt2 = 7;
          elseif( array_key_exists( 'week', $freebuzyPeriod[1] ))  // duration
            $cnt2 = 5;
          if(( 7 == $cnt2 )   &&    // period=  -> date-time
              isset( $freebuzyPeriod[1]['year'] )  &&
              isset( $freebuzyPeriod[1]['month'] ) &&
              isset( $freebuzyPeriod[1]['day'] )) {
            $formatted = $this->_format_date_time( $freebuzyPeriod[1] );
            $outputPart .= $formatted[0];
          }
          else {                                  // period=  -> dur-time
            $outputPart .= $this->_format_duration( $freebuzyPeriod[1] );
          }
        if( $fno < $cnt )
          $outputPart .= ',';
        $fno++;
      }
      $output .= $this->_size75( $outputPart );
    }

    return $output;
  }
/*
 * set calendar component property freebuzy
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @param array $freebuzy
 * @return void
 */
  function setFreebuzy( $fbType, $fbValues ) {
    $freebuzy = array( 'fbtype' => strtoupper( $fbType ) );
    foreach( $fbValues as $fbPeriod ) {   // periods => period
      $freebuzyPeriod = array();
      foreach( $fbPeriod as $fbMember ) { // pairs => singlepart
        $freebuzyPairMember = array();
        if( is_array( $fbMember )) { 
          $cnt = count( $fbMember );
          if(( 6 == count( $fbMember )) || ( 7 == count( $fbMember )) || 
             ( array_key_exists( 'year', $fbMember ))) { // date-time value
            $freebuzyPairMember = $this->_date_time_array( $fbMember, 7 );
          }
          else {                                         // duration
            $freebuzyPairMember = $this->_duration_array( $fbMember );
          }
        }
        elseif( 8 <= strlen( trim( $fbMember ))) { // ex. 2006-08-03 10:12:18
          $freebuzyPairMember = $this->_date_time_string( $fbMember, 7 );
        }
        $freebuzyPeriod[] = $freebuzyPairMember;
      }
      $freebuzy[] = $freebuzyPeriod;
    }
    $this->freebuzy[] = $freebuzy;
  }


/**********************************************************************************
 * Property Name: GEO
 *
 * Conformance: This property can be specified in  "VEVENT" or "VTODO"
 * calendar components.
 *
 */
/*
 * creates formatted output for calendar component property geo
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createGeo( ) {
    $cnt = count( $this->geo );
    if( 0 >= $cnt )
      return;
    $geo  = 'GEO:';
    $geo .= number_format( (float) $this->geo['latitude'], 5, '.', '');
    $geo .= ';';
    $geo .= number_format( (float) $this->geo['longitude'], 5, '.', '');
    $geo .= $this->nl;
    return $geo;
  }
/*
 * set calendar component property geo
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param float $latitude
 * @param float $longitude
 * @return void
 */
  function setGeo( $latitude, $longitude ) {
    $this->geo['latitude']  = $latitude;
    $this->geo['longitude'] = $longitude;
  }

/**********************************************************************************
 * Property Name: LAST-MODIFIED
 *
 * Conformance: This property can be specified in the "EVENT", "VTODO",
 * "VJOURNAL" or "VTIMEZONE" calendar components.
 *
 */
/*
 * creates formatted output for calendar component property last-modified
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createLastModified( ) {
    if( !isset( $this->lastmodified['year'] )  &&
        !isset( $this->lastmodified['month'] ) &&
        !isset( $this->lastmodified['day'] )   &&
        !isset( $this->lastmodified['hour'] )  &&
        !isset( $this->lastmodified['min'] )   &&
        !isset( $this->lastmodified['sec'] ))
      return;
    $formatted = $this->_format_date_time( $this->lastmodified, 7 );
    return 'LAST-MODIFIED:'.$formatted[0].$this->nl;
  }
/*
 * set calendar component property completed
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param int $year
 * @param int $month
 * @param int $day
 * @param int $hour
 * @param int $min
 * @param int $sec
 * @return void
 */
  function setLastModified( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $tz=FALSE ) {
    if( is_array( $year ) && 
      (( 6 == count( $year )) || ( 7 == count( $year )) || 
       ( array_key_exists( 'year', $year )))) {
      $this->lastmodified = $this->_date_time_array( $year, 7 );
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $this->lastmodified = $this->_date_time_string( $year, 7 );
    }
    else {
      $this->lastmodified = array('year'  => $year
                                , 'month' => $month
                                , 'day'   => $day
                                , 'hour'  => $hour
                                , 'min'   => $min
                                , 'sec'   => $sec
                                , 'tz'    => $tz);
    }
  }

/**********************************************************************************
 * Property Name: LOCATION
 *
 * Conformance: This property can be specified in "VEVENT" or "VTODO"
 * calendar component.
 */
/*
 * creates formatted output for calendar component property location
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createLocation( ) {
    $cnt = count( $this->location );
    if( 0 >= $cnt )
      return;
    $label    = 'LOCATION';
    if( isset( $this->language )) {
      $label .= ';LANGUAGE='.$this->language;
    }
    if( isset( $this->location['ALTREP'] )) {
      $label .= ';ALTREP="'.$this->location['ALTREP'].'"';
      unset( $this->location['ALTREP'] );
    }
    $location = reset( $this->location );
    return $this->_size75( $label.':'.$this->_strrep( $location ));
  }
/*
 * set calendar component property location
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @param string $altrep
 * @return void
 */
  function setLocation( $value, $altrep=FALSE ) {
    $this->location[] = $value;
    if( $altrep )
      $this->location['ALTREP'] = $altrep;
  }

/**********************************************************************************
   Property Name: ORGANIZER

   Conformance: This property MUST be specified in an iCalendar object
   that specifies a group scheduled calendar entity. This property MUST
   be specified in an iCalendar object that specifies the publication of
   a calendar user's busy time. This property MUST NOT be specified in
   an iCalendar object that specifies only a time zone definition or
   that defines calendar entities that are not group scheduled entities,
   but are entities only on a single user's calendar.
 */
/*
 * creates formatted output for calendar component property organizer
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createOrganizer( ) {
    $cnt = count( $this->organizer );
    if( 0 >= $cnt )
      return;
    $organizer1 = 'ORGANIZER';
    $organizer2 = null;
    if( isset( $this->language )) {
      $organizer1 .= ';LANGUAGE='.$this->language;
    }
    if( array_key_exists( 'CN', $this->organizer )) {
      $organizer1 .= ';CN='.$this->organizer['CN'];
      unset( $this->organizer['CN'] );
    }
    if( array_key_exists( 'DIR', $this->organizer )) {
      $organizer1 .= ';DIR="'.$this->organizer['DIR'].'"';
      unset( $this->organizer['DIR'] );
    }
    if( array_key_exists( 'SENT-BY', $this->organizer )) {
      $organizer1 .= ';SENT-BY="MAILTO:'.$this->organizer['SENT-BY'].'"';
      unset( $this->organizer['SENT-BY'] );
    }
    $organizer2 .= ':MAILTO:'.$this->organizer['org'];
    unset( $this->organizer['org'] );
    if( 0 < count( $this->organizer )) {
      foreach( $this->organizer as $otherType => $otherValue )
        $organizer1 .= ";$otherType:".$otherValue;
    }
    $organizer = $this->_size75( $organizer1.$organizer2 );

    return $organizer;
  }
/*
 * set calendar component property organizer
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @param string $cnparam
 * @param string $dirparam
 * @param string $sentbyparam
 * @param string $othertype
 * @param string $othervalue
 * @return void
 */
  function setOrganizer( $value, $cnparam=FALSE, $dirparam=FALSE, $sentbyparam=FALSE, $othertype=FALSE, $othervalue=FALSE ) {
    $this->organizer['org'] = $value;
    if( $cnparam )
      $this->organizer['CN'] = $cnparam;
    if( $dirparam )
      $this->organizer['DIR'] = $dirparam;
    if( $sentbyparam )
      $this->organizer['SENT-BY'] = $sentbyparam;
    if( $othertype ) // ??
      $this->organizer[strtoupper( $othertype )] = $othervalue;
  }

/**********************************************************************************
 * Property Name: PERCENT-COMPLETE
 *
 * Conformance: This property can be specified in a "VTODO" calendar
 * component.
 *
 */
/*
 * creates formatted output for calendar component property percent-complete
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createPercentComplete( ) {
    if( !isset( $this->percentcomplete ))
      return;
    return 'PERCENT-COMPLETE:'.$this->percentcomplete.$this->nl;
  }
/*
 * set calendar component property percent-complete
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param int $value
 * @return void
 */
  function setPercentComplete( $value ) {
    $this->percentcomplete = $value;
  }

/**********************************************************************************
 * Property Name: PRIORITY
 *
 * Conformance: The property can be specified in a "VEVENT" or "VTODO"
 * calendar component.
 *
 */
/*
 * creates formatted output for calendar component property priority
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createPriority( ) {
    if( !isset( $this->priority ))
      return;
    return 'PRIORITY:'.$this->priority.$this->nl;
  }
/*
 * set calendar component property priority
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param int $value
 * @return void
 */
  function setPriority( $value ) {
    $this->priority = $value;
  }


/**********************************************************************************
 * Property Name: RDATE
 *
 * Conformance: The property can be specified in "VEVENT", "VTODO",
 * "VJOURNAL" or "VTIMEZONE" calendar components.
 */

/*
 * creates formatted output for calendar component property rdate
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-12
 * @return string
 */
  function createRdate( $localtime=FALSE ) {
    $cnt = count( $this->rdate );
    if( 0 >= $cnt )
      return;
    $output = null;
    foreach( $this->rdate as $theRdate ) {
      $cnt = count( $theRdate );
      $outputr = null;
      $rdate = 'RDATE';
      $rno = 1;
      foreach( $theRdate as $rdatePart ) {
        $outputPart = null;
        if( is_array( $rdatePart ) &&
           ( 2 == count( $rdatePart )) &&
             array_key_exists( '0', $rdatePart ) &&
             array_key_exists( '1', $rdatePart )) { // PERIOD
          $formatted   = $this->_format_date_time( $rdatePart[0], $localtime );
          $outputPart .= $formatted[0];
          if( 1 == $rno )
            $rdate .= $formatted[1].':';
          $outputPart .= '/';
          $cnt2 = count( $rdatePart[1]);
          if( array_key_exists( 'year', $rdatePart[1] ))      // date-time
            $cnt2 = 7;
          elseif( array_key_exists( 'week', $rdatePart[1] ))  // duration
            $cnt2 = 5;
          if(( 7 == $cnt2 )   &&    // period=  -> date-time
              isset( $rdatePart[1]['year'] )  &&
              isset( $rdatePart[1]['month'] ) &&
              isset( $rdatePart[1]['day'] )) {
            $formatted = $this->_format_date_time( $rdatePart[1], $localtime );
            $outputPart .= $formatted[0];
          }
          else {                                  // period=  -> dur-time
            $outputPart .= $this->_format_duration( $rdatePart[1] );
          }
        }
        else {
          $formatted = $this->_format_date_time( $rdatePart, $localtime );
          if( 1 == $rno )
            $rdate .= $formatted[1].':';
          $outputPart .= $formatted[0];
        }
        $outputr .= $outputPart;
        if( $rno < $cnt )
          $outputr .= ',';
        $rno++;
      }
      $output .= $this->_size75( $rdate.$outputr );
    }
    return $output;
  }
/*
 * set calendar component property rdate
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param array $rdates
 * @return void
 */
  function setRdate( $rdates ) {
    $input = array();
    $paramcnt = null;
    foreach( $rdates as $theRdate ) {
 //   echo 'setRdate in '; print_r ( $theRdate ); echo "<br />\n"; // test ##
      $inputa = null;
      if( is_array( $theRdate )) {
        if(( 2 == count( $theRdate )) &&
             array_key_exists( '0', $theRdate ) &&
             array_key_exists( '1', $theRdate )) { // PERIOD
          foreach( $theRdate as $rPeriod ) {
 //   echo 'setRdate i2 '; print_r ( $rPeriod ); echo "<br />\n"; // test ##
            if( is_array( $rPeriod )) {
              if (( 1 == count( $rPeriod )) &&
                  ( 8 <= strlen( trim( $rPeriod[0] )))) { // text-date
                $inputab  = $this->_date_time_string( $rPeriod[0], $paramcnt );
                $inputa[] = $inputab;
                if( !$paramcnt )
                  $paramcnt = count( $inputab );
              }
              elseif (( 3 == count( $rPeriod )) ||
                      ( 6 == count( $rPeriod )) ||
                      ( 7 == count( $rPeriod )) ||
                      ( array_key_exists( 'year', $rPeriod ))) {
                if( !isset( $paramcnt) && 3 < count( $rPeriod ))
                  $paramcnt = 7;
                $inputab  = $this->_date_time_array( $rPeriod, $paramcnt );
                $inputa[] = $inputab;
                if( !$paramcnt )
                  $paramcnt = count( $inputab );
              }
              else {                                       // duration
                $inputa[] = $this->_duration_array( $rPeriod );
              }
            }
            elseif( 8 <= strlen( trim( $rPeriod ))) { // ex. 2006-08-03 10:12:18
              $inputab  = $this->_date_time_string( $rPeriod, $paramcnt );
              $inputa[] = $inputab;
              if( !$paramcnt )
                $paramcnt = count( $inputab );
            }
          }
        }
        elseif (( 3 == count( $theRdate )) ||
                ( 6 == count( $theRdate )) ||
                ( 7 == count( $theRdate )) ||
                ( array_key_exists( 'year', $theRdate ))) {
          if( !isset( $paramcnt) && 3 < count( $rPeriod ))
            $paramcnt = 7;
          $inputa = $this->_date_time_array( $theRdate, $paramcnt );
          if( !$paramcnt )
            $paramcnt = count( $inputa );
        }
      }
      elseif( 8 <= strlen( trim( $theRdate ))) { // ex. 2006-08-03 10:12:18
        $inputa = $this->_date_time_string( $theRdate, $paramcnt );
        if( !$paramcnt )
          $paramcnt = count( $inputa );
      }
      $input[] = $inputa;
    }
    if( 0 < count( $input ))
      $this->rdate[] = $input;

  //  echo 'setRdate ut '; print_r ( $this->rdate ); echo "<br />\n"; // test ##
  }

/**********************************************************************************
 * Property Name: RECURRENCE-ID
 *
 * Conformance: This property can be specified in an iCalendar object
 * containing a recurring calendar component.
 */
/*
 * creates formatted output for calendar component property recurrence-id
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-12
 * @return string
 */
  function createRecurrenceid( ) {
    $cnt = count( $this->recurrenceid );
    if( 0 >= $cnt )
      return;
    $output  = 'RECURRENCE-ID';
    $formatted = $this->_format_date_time( $this->recurrenceid['date'] );
    if( isset( $this->recurrenceid['range'] ))
      $output .= ';RANGE='.$this->recurrenceid['range'];
    elseif( isset( $formatted[2] ))
      $output .= $formatted[2];
    else
      $output .= $formatted[1];
    $output .= ':'.$formatted[0];
    return $this->_size75( $output );
  }
/*
 * set calendar component property recurrence-id
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param array $date
 * @param string $range
 * @return void
 */
  function setRecurrenceid( $date, $range=FALSE ) {
    if( is_array( $date )) {
      $this->recurrenceid['date'] = $this->_date_time_array( $date );
    }
    else {
      $this->recurrenceid['date'] = $this->_date_time_string( $date );
    }
    if( $range )
      $this->recurrenceid['range'] = $range;
  }

/**********************************************************************************
   Property Name: RELATED-TO

   Conformance: The property can be specified one or more times in the
   "VEVENT", "VTODO" or "VJOURNAL" calendar components.

 */
/*
 * creates formatted output for calendar component property related-to
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createRelatedTo( ) {
    $cnt = count( $this->relatedto );
    if( 0 >= $cnt )
      return;
    $output =null;
    foreach( $this->relatedto as $relation ) {
      $relatedto  = 'RELATED-TO';
      if( isset( $relation['reltype'] ))
        $relatedto .= ';RELTYPE='.$relation['reltype'];
      $relatedto .= ':';
      $relatedto .= '<'.$relation['relid'].'>';
      $output .= $this->_size75( $relatedto );
    }
    return $output;
  }
/*
 * set calendar component property related-to
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-11
 * @param float $relid
 * @param float $reltype
 * @return void
 */
  function setRelatedTo( $relid, $reltype=FALSE ) {
    $relation = array();
    $relation['relid'] = $relid;
    if( $reltype )
      $relation['reltype'] = $reltype;
    $this->relatedto[] = $relation;
  }

/**********************************************************************************
 * Property Name: REPEAT
 *
 * Conformance: This property can be specified in a "VALARM" calendar
 * component.
 */
/*
 * creates formatted output for calendar component property repeat
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createRepeat( ) {
    if( !isset( $this->repeat ))
      return;
    return 'REPEAT:'.$this->repeat.$this->nl;
  }
/*
 * set calendar component property transp
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @return void
 */
  function setRepeat( $value ) {
    $this->repeat = $value;
  }


/**********************************************************************************
 * Property Name: REQUEST-STATUS
 *
 * Conformance: The property can be specified in "VEVENT", "VTODO",
 * "VJOURNAL" or "VFREEBUSY" calendar component.
 *
 */
/*
 * creates formatted output for calendar component property request-status
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createRequestStatus( ) {
    $cnt = count( $this->requeststatus );
    if( 0 >= $cnt )
      return;
    $output = null;
    foreach( $this->requeststatus as $rstat ) {
      $requeststatus  = 'REQUEST-STATUS';
      if( isset( $this->language )) {
        $requeststatus .= ';LANGUAGE='.$this->language;
      }
      $requeststatus .= ':';
      $requeststatus .= number_format( (float) $rstat['statcode'], 2, '.', '');
      $requeststatus .= ';'.$this->_strrep( $rstat['text'] );
      if( isset( $rstat['extdata'] ))
        $requeststatus .= ';'.$this->_strrep( $rstat['extdata'] );
      $output .= $this->_size75( $requeststatus );
    }
    return $output;
  }
/*
 * set calendar component property request-status
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param float $value
 * @param string $text
 * @param string $extdata
 * @return void
 */
  function setRequestStatus( $statcode, $text, $extdata=FALSE ) {
    $input = array();
    $input['statcode']  = $statcode;
    $input['text']      = $text;
    if( $extdata )
      $input['extdata'] = $extdata;
    $this->requeststatus[] = $input;
  }

/**********************************************************************************
 * Property Name: RESOURCES
 *
 * Conformance: This property can be specified in "VEVENT" or "VTODO"
 * calendar component.
 *
 */
/*
 * creates formatted output for calendar component property resources
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createResources( ) {
    $cnt = count( $this->resources );
    if( 0 >= $cnt )
      return;
    $output = null;
    foreach( $this->resources as $resource ) {
      $resources  = 'RESOURCES';
      if( isset( $this->language ))
        $resources .= ';LANGUAGE='.$this->language;
      if( isset( $resource['ALTREP'] ))
        $resources .= ';ALTREP="'.$resource['ALTREP'].'"';
      $resources .= ':';
      $rno = 1;
      $cnt = count( $resource['part'] );
      foreach( $resource['part'] as $resourcePart ) {
        $resources .= $resourcePart;
        if( $rno < $cnt )
          $resources .= ',';
        $rno++;
      }
      $output .= $this->_size75( $resources );
    }

    return ( $output );
  }
/*
 * set calendar component property recources
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @param string $altrep
 * @return void
 */
  function setResources( $value, $altrep=FALSE ) {
    $input = array();
    if( is_array( $value )) {
      foreach( $value as $valuePart )
        $input['part'][] = $valuePart;
    }
    else
      $input['part'][] = $value;
    if( $altrep )
      $input['ALTREP'] = $altrep;
    $this->resources[] = $input;
  }

/**********************************************************************************
 * Property Name: RRULE
 *
 * Conformance: This property can be specified one or more times in
 * recurring "VEVENT", "VTODO" and "VJOURNAL" calendar components. It
 * can also be specified once in each STANDARD or DAYLIGHT sub-component
 * of the "VTIMEZONE" calendar component.
 */
/*
 * creates formatted output for calendar component property rrule
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-12
 * @return string
 */
  function createRrule( ) {
    $cnt = count( $this->rrule );
    if( 0 >= $cnt )
      return;

    return $this->_format_recur( 'RRULE', $this->rrule );
  }
/*
 * set calendar component property rrule
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-12
 * @param array $rruleset
 * @return void
 */
  function setRrule( $rruleset ) {
    $rrule = array();
    foreach( $rruleset as $rrulelabel => $rrulevalue ) {
      $rrulelabel = strtoupper( $rrulelabel );
      if( 'UNTIL'  != $rrulelabel )
        $rrule[$rrulelabel] = $rrulevalue;
      elseif( is_array( $rrulevalue ) && 
            (( 3 == count( $rrulevalue )) ||
             ( 6 == count( $rrulevalue )) ||
             ( 7 == count( $rrulevalue )) ||
             ( array_key_exists( 'year', $rrulevalue )))) {
        $parno = ( 3 < count( $rrulevalue )) ? 7 : 3 ;
        $rrule[$rrulelabel] = $this->_date_time_array( $rrulevalue, $parno );
      }
      elseif( 8 <= strlen( trim( $rrulevalue ))) { // ex. 2006-08-03 10:12:18
        $rrule[$rrulelabel] = $this->_date_time_string( $rrulevalue );
      }
    }
    $this->rrule[] = $rrule;
  }

/**********************************************************************************
 * Property Name: SEQUENCE
 *
 * Conformance: The property can be specified in "VEVENT", "VTODO" or
 * "VJOURNAL" calendar component.
 */
/*
 * creates formatted output for calendar component property sequence
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createSequence( ) {
    if( !isset( $this->sequence ))
      return;
    return 'SEQUENCE:'.$this->sequence.$this->nl;
  }
/*
 * set calendar component property sequence
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param int $value
 * @return void
 */
  function setSequence( $value ) {
    $this->sequence = $value;
  }

/**********************************************************************************
 * Property Name: STATUS
 *
 * Conformance: This property can be specified in "VEVENT", "VTODO" or
 * "VJOURNAL" calendar components.
 */
/*
 * creates formatted output for calendar component property status
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createStatus( ) {
    if( !isset( $this->status ))
      return;
    return 'STATUS:'.$this->status.$this->nl;
  }
/*
 * set calendar component property status
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @return void
 */
  function setStatus( $value ) {
    $this->status = $value;
  }


/**********************************************************************************
 * Property Name: SUMMARY
 *
 * Conformance: The property can be specified in "VEVENT", "VTODO",
 * "VJOURNAL" or "VALARM" calendar components.
 */
/*
 * creates formatted output for calendar component property summary
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createSummary( ) {
    $cnt = count( $this->summary );
    if( 0 >= $cnt )
      return;
    $label    = 'SUMMARY';
    if( isset( $this->language )) {
      $label .= ';LANGUAGE='.$this->language;
    }
    if( isset( $this->summary['ALTREP'] )) {
      $label .= ';ALTREP="'.$this->summary['ALTREP'].'"';
      unset( $this->summary['ALTREP'] );
    }
    $summary  = reset( $this->summary );
    return $this->_size75( $label.':'.$this->_strrep( $summary ));
  }
/*
 * set calendar component property summary
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @param string $altrep
 * @return void
 */
  function setSummary( $value, $altrep=FALSE ) {
    $this->summary[] = $value;
    if( $altrep )
      $this->summary['ALTREP'] = $altrep;
  }

/**********************************************************************************
 * Property Name: TRANSP
 *
 * Conformance: This property can be specified once in a "VEVENT"
 * calendar component.
 */
/*
 * creates formatted output for calendar component property transp
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createTransp( ) {
    if( !isset( $this->transp ))
      return;
    return 'TRANSP:'.$this->transp.$this->nl;
  }
/*
 * set calendar component property transp
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @return void
 */
  function setTransp( $value ) {
    $this->transp = $value;
  }

/**********************************************************************************
 * Property Name: TRIGGER
 *
 * Conformance: This property MUST be specified in the "VALARM" calendar
 * component.
 *
 */
/*
 * creates formatted output for calendar component property trigger
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createTrigger( ) {
    $cnt = count( $this->trigger );
    if( 0 >= $cnt )
      return;
    $trigger  = 'TRIGGER';
    if( isset( $this->trigger['year'] ) &&
        isset( $this->trigger['month'] )  &&
        isset( $this->trigger['day'] )) {
      $formatted = $this->_format_date_time( $this->trigger );
      $trigger .= $formatted[1].':'.$formatted[0];
    }
    else {
      if( $this->trigger['relatedstart'] )
        $trigger .= ';RELATED=START';
      else
        $trigger .= ';RELATED=END';
      $trigger .= ':';
      if( $this->trigger['before'] )
        $trigger .= '-';
      $trigger .= $this->_format_duration( $this->trigger );
    }
    return $trigger.$this->nl;;
  }
/*
 * set calendar component property trigger
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param int $year
 * @param int $month
 * @param int $day
 * @param int $week
 * @param int $hour
 * @param int $min
 * @param int $sec
 * @param bool $relatedend
 * @param bool $before
 * @return void
 */
  function setTrigger( $year=FALSE, $month=FALSE, $day=FALSE, $week=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $relatedstart=TRUE, $before=TRUE ) {
    if( $year && $month && $day ) {
      $this->trigger = array( 'year'       => $year,
                              'month'      => $month,
                              'day'        => $day);
      if( $hour )
        $this->trigger['hour'] = $hour;
      if( $min )
        $this->trigger['min']  = $min;
      if( $sec )
        $this->trigger['sec']  = $sec;
    }
    elseif( $week ) {
      $this->trigger = array( 'week'         => $week,
                              'relatedstart' => $relatedstart,
                              'before'       => $before );
    }
    else {
      $this->trigger = array( 'day'          => $day,
                              'hour'         => $hour,
                              'min'          => $min,
                              'sec'          => $sec,
                              'relatedstart' => $relatedstart,
                              'before'       => $before );
    }
  }

/**********************************************************************************
 * Property Name: TZID
 *
 * Conformance: This property MUST be specified in a "VTIMEZONE"
 * calendar component.
 *
 */
/*
 * creates formatted output for calendar component property tzid
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @return string
 */
  function createTzid( ) {
    if( !isset( $this->tzid ))
      return;
    return 'TZID:'.$this->tzid.$this->nl;
  }
/*
 * set calendar component property tzid
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @param string $value
 * @return void
 */
  function setTzid( $value ) {
    $this->tzid = $value;
  }
/**********************************************************************************
 * .. .
 * Property Name: TZNAME
 *
 * Conformance: This property can be specified in a "VTIMEZONE" calendar
 * component.
 */
/*
 * creates formatted output for calendar component property tzname
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @return string
 */
  function createTzname( ) {
    if( !isset( $this->tzname ))
      return;
    $tzname  = 'TZNAME';
    if( isset( $this->language )) {
      $tzname .= ';LANGUAGE='.$this->language;
    }
    $tzname .= ':'.$this->tzname.$this->nl;
    return $tzname;
  }
/*
 * set calendar component property tzname
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @param string $value
 * @return void
 */
  function setTzname( $value ) {
    $this->tzname = $value;
  }

/**********************************************************************************
 * Property Name: TZOFFSETFROM
 *
 * Conformance: This property MUST be specified in a "VTIMEZONE"
 * calendar component.
 */
/*
 * creates formatted output for calendar component property tzoffsetfrom
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @return string
 */
  function createTzoffsetfrom( ) {
    if( !isset( $this->tzoffsetfrom ))
      return;
    return 'TZOFFSETFROM:'.$this->tzoffsetfrom.$this->nl;
  }
/*
 * set calendar component property tzoffsetfrom
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @param string $value
 * @return void
 */
  function setTzoffsetfrom( $value ) {
    $this->tzoffsetfrom = $value;
  }

/**********************************************************************************
 * Property Name: TZOFFSETTO
 *
 * Conformance: This property MUST be specified in a "VTIMEZONE"
 * calendar component.
 */
/*
 * creates formatted output for calendar component property tzoffsetto
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @return string
 */
  function createTzoffsetto( ) {
    if( !isset( $this->tzoffsetto ))
      return;
    return 'TZOFFSETTO:'.$this->tzoffsetto.$this->nl;
  }
/*
 * set calendar component property tzoffsetto
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @param string $value
 * @return void
 */
  function setTzoffsetto( $value ) {
    $this->tzoffsetto = $value;
  }

/**********************************************************************************
   Property Name: TZURL

   Conformance: This property can be specified in a "VTIMEZONE" calendar
   component.
 */
/*
 * creates formatted output for calendar component property tzurl
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @return string
 */
  function createTzurl( ) {
    if( !isset( $this->tzurl ))
      return;
    return $this->_size75( 'TZURL:'.$this->tzurl );
  }
/*
 * set calendar component property tzurl
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 * @param string $value
 * @return void
 */
  function setTzurl( $value ) {
    $this->tzurl = $value;
  }

/**********************************************************************************
 * Property Name: UID
 *
 * Conformance: The property MUST be specified in the "VEVENT", "VTODO",
 * "VJOURNAL" or "VFREEBUSY" calendar components.
 * .. .
 *  The identifier is RECOMMENDED to be the identical syntax to the
 * [RFC 822] addr-spec. A good method to assure uniqueness is to put the
 * domain name or a domain literal IP address of the host on which.. .
 */
/*
 * creates formatted output for calendar component property uid
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createUid( ) {
    if( !isset( $this->uid )) {
      $this->_makeuid();
    }
    return 'UID:'.$this->uid.$this->nl;
  }
/*
 * return calendar component property uid
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function getUid( ) {
    if( !isset( $this->uid )) {
      $this->_makeuid();
    }
    return $this->uid;
  }
/*
 * create an unique id for this calendar component object instance
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function _makeUid() {
    $unique = null;
    $base   = 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPrRsStTuUvVxXuUvVwWzZ1234567890 ';
    $start  = 0;
    $end    = strlen( $base ) - 1;
    $length = 10;
    $str    = null;
    for( $p=0; $p<$length; $p++ ) {
      $basePos = mt_rand( $start, $end );
      $unique .= $base{$basePos};
    }
    $this->uid = date('Ymd\THis\Z').'-'.$unique.'@'.$this->unique_id;
  }
/*
 * set calendar component property uid
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @return void
 */
  function setUid( $value ) {
    $this->uid = $value;
  }

/**********************************************************************************
 * Property Name: URL
 */
/*
 * creates formatted output for calendar component property url
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createUrl( ) {
    if( !isset( $this->url ))
      return;
    return $this->_size75( 'URL:'.$this->url );
  }
/*
 * set calendar component property url
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @return void
 */
  function setUrl( $value ) {
    $this->url = $value;
  }

/**********************************************************************************
 * Property Name: x-prop
 */
/*
 * creates formatted output for calendar component property x-prop
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createXprop( ) {
    $cnt = count( $this->xprop );
    if( 0 >= $cnt )
      return;
    $xprop = null;
    foreach( $this->xprop as $xpropPart ) {
     foreach( $xpropPart as $label => $value )
      $xprop .= $this->_size75( strtoupper( $label ).':'.$value );
    }
    return $xprop;
  }
/*
 * set calendar component property url
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $label
 * @param string $value
 * @return void
 */
  function setXprop( $label, $value ) {
    $this->xprop[] = array( $label => $value);
  }



/**********************************************************************************
 *********************************************************************************/
/*
 * creates formatted output for calendar component property data value type date/date-time
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.4.1 - 2006-08-18
 * @param array 
 * @return array 
 */
  function _format_date_time( $datetime, $parno=6 ) {
    if( !isset( $datetime['year'] )  &&
        !isset( $datetime['month'] ) &&
        !isset( $datetime['day'] )   &&
        !isset( $datetime['hour'] )  &&
        !isset( $datetime['min'] )   &&
        !isset( $datetime['sec'] ))
      return ;
    $output    = array();
    $output[0] = date('Ymd', mktime ( 0, 0, 0, 
                                      $datetime['month'], 
                                      $datetime['day'], 
                                      $datetime['year']));
    if( isset( $datetime['hour'] )  ||
        isset( $datetime['min'] )   ||
        isset( $datetime['sec'] )   ||
        isset( $datetime['tz'] )) {
      $output[1]  = ';VALUE=DATE-TIME';
      $output[0] .= date('\THis', mktime ( $datetime['hour'], 
                                           $datetime['min'], 
                                           $datetime['sec'], 1, 2, 3 ));
      if( isset( $datetime['tz'] ) && ( '' < trim ( $datetime['tz'] ))) {
        $datetime['tz'] = trim( $datetime['tz'] );
        if( 'Z' == $datetime['tz'] )
          $output[0] .= 'Z'; 
        elseif(( 4 == strlen( $datetime['tz'] )) &&
               ( '0000' <= $datetime['tz'] ) && ( '9999' >= $datetime['tz'] )) {
          $offset = substr( $datetime['tz'], 0, 2 ) * 60 + substr( $datetime['tz'], -2 ) * 60;
          $output[0] = date('Ymd\THis\Z', mktime ( $datetime['hour'] 
                                                 , $datetime['min'] 
                                                 , $datetime['sec'] + $offset 
                                                 , $datetime['month'] 
                                                 , $datetime['day'] 
                                                 , $datetime['year']));
        }
        elseif(( 5 == strlen( $datetime['tz'] )) && 
               ( '0000' <= substr( $datetime['tz'], -4 )) && 
               ( '9999' >= substr( $datetime['tz'], -4 )) &&
               (( '+' == substr( $datetime['tz'], 0, 1 )) || 
                ( '-' == substr( $datetime['tz'], 0, 1 )))) {
          $offset = substr( $datetime['tz'], 0, 3 ) * 60 + substr( $datetime['tz'], -2 ) * 60;
          $output[0] = date('Ymd\THis\Z', mktime ( $datetime['hour'] 
                                                 , $datetime['min'] 
                                                 , $datetime['sec'] + $offset 
                                                 , $datetime['month'] 
                                                 , $datetime['day'] 
                                                 , $datetime['year']));
        }
        elseif( '' < $datetime['tz'] ); {
          $output[2] = ';TZID='.(string) $datetime['tz'];
        }
      }
      elseif( 7 == $parno )
        $output[0] .= 'Z'; 
    }
    else {
      $output[1] = ';VALUE=DATE';
    }
    return $output;
  }
/*
 * creates formatted output for calendar component property data value type duration
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-12
 * @param array ( week, day, hour, min, sec )
 * @return string
 */
  function _format_duration( $duration ) {
    if( !isset( $duration['week'] ) &&
        !isset( $duration['day'] )  &&
        !isset( $duration['hour'] ) &&
        !isset( $duration['min'] )  &&
        !isset( $duration['sec'] ))
      return;
    $output = 'P';
    if( isset( $duration['week'] ) && ( 0 < $duration['week'] ))
      $output .= $duration['week'].'W';
    else {
      if( isset($duration['day'] ) && ( 0 < $duration['day'] ))
        $output .= $duration['day'].'D';
      if( isset( $duration['hour'] ) ||
          isset( $duration['min']  ) ||
          isset( $duration['sec']  )) {
        $output .= 'T';
        if( 0 < $duration['hour'] )
          $output .= $duration['hour'];
        else
          $output .= '0';
        $output .= 'H';
        if( 0 < $duration['min'] )
          $output .= $duration['min'];
        else
          $output .= '0';
        $output .= 'M';
        if( 0 < $duration['sec'] )
          $output .= $duration['sec'];
        else
          $output .= '0';
        $output .= 'S';
      }
    }
    return $output;
  }
/*
 * creates formatted output for calendar component property data value type recur
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-12
 * @param array
 * @return string
 */
  function _format_recur ( $recurlabel, $recurdata ) {
    $recur = null;
    foreach( $recurdata as $therule ) {
      $ruleset1 = $recurlabel.':';
      $ruleset2 = null;
      foreach( $therule as $rulelabel => $rulevalue ) {
        switch( $rulelabel ) {
          case 'FREQ': {
            $ruleset1 .= "FREQ=$rulevalue";
            break;
          }
          case 'UNTIL': {
            $ruleset2 .= ";UNTIL=";
            $formatted = $this->_format_date_time( $rulevalue );
            $ruleset2 .= $formatted[0];
            break;
          }
          case 'COUNT':
          case 'INTERVAL': 
          case 'WKST': {
            $ruleset2 .= ";$rulelabel=$rulevalue"; 
            break;
          }
          case 'BYSECOND': 
          case 'BYMINUTE': 
          case 'BYHOUR': 
          case 'BYMONTHDAY':
          case 'BYYEARDAY': 
          case 'BYWEEKNO': 
          case 'BYMONTH': 
          case 'BYSETPOS': {
            $ruleset2 .= ";$rulelabel=";
            if( is_array( $rulevalue )) {
              foreach( $rulevalue as $vix => $valuePart ) {
                if( $vix )
                   $ruleset2 .= ','; 
                $ruleset2 .= $valuePart; 
              }
            }
            else 
             $ruleset2 .= $rulevalue; 
            break;
          }
          case 'BYDAY': {
            $ruleset2 .= ";$rulelabel=";
            $bydaycnt = 0;
            foreach( $rulevalue as $vix => $valuePart ) {
              $ruleset21 = $ruleset22 = null;
              if( is_array( $valuePart )) {
                if( $bydaycnt )
                  $ruleset2 .= ','; 
                foreach( $valuePart as $vix2 => $valuePart2 ) {
                  if( 'DAY' != strtoupper( $vix2 )) {
                      $ruleset21 .= $valuePart2; 
                  }
                  else
                    $ruleset22 .= $valuePart2;
                }
                $ruleset2 .= $ruleset21.$ruleset22; 
                $bydaycnt++;
              }
              else {
                if( $bydaycnt )
                  $ruleset2 .= ','; 
                if( 'DAY' != strtoupper( $vix )) {
                    $ruleset21 .= $valuePart; 
                }
                else {
                  $ruleset22 .= $valuePart;
                  $bydaycnt++;
                }
                $ruleset2 .= $ruleset21.$ruleset22; 
              }
            }
            break;
          }
          default: {
            $ruleset2 .= ";$rulelabel=$rulevalue"; 
            break;
          }
        }
      }
      $recur .= $this->_size75( $ruleset1.$ruleset2 );
    }
    return $recur;
  }
/*
 * ensures internal date-time/date format for input date-time/date in array format
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-15
 * @param array 
 * @return array 
 */
  function _date_time_array( $datetime, $parno=false ) {
    $output = array();
    foreach( $datetime as $dateKey => $datePart ) {
      switch ( $dateKey ) {
        case '0': case 'year':   $output['year']  = $datePart; break;
        case '1': case 'month':  $output['month'] = $datePart; break;
        case '2': case 'day':    $output['day']   = $datePart; break;
      }
      if( 3 != $parno ) {
        switch ( $dateKey ) {
          case '0':
          case '1':
          case '2': break;
          case '3': case 'hour': $output['hour']  = $datePart; break;
          case '4': case 'min' : $output['min']   = $datePart; break;
          case '5': case 'sec' : $output['sec']   = $datePart; break;
          case '6': case 'tz'  : $output['tz']    = $datePart; break;
        }
      }
    }
    if( 3 != $parno ) {
      if( !isset( $output['hour'] ))
        $output['hour'] = 0;
      if( !isset( $output['min']  ))
        $output['min'] = 0;
      if( !isset( $output['sec']  ))
        $output['sec'] = 0;
    }
    return $output;
  }
/*
 * ensures internal date-time/date format for input date-time/date in string fromat
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.6.1 - 2006-08-18
 * @param array $datetime
 * @param int $parno
 * @return array 
 */
  function _date_time_string( $datetime, $parno=false ) {
    $datetime = trim( $datetime );
    $len = strlen( $datetime ) - 1;
    $tz  = null;
    $cx  = 0;    //  19970415T133000Z
    for( $cx = -1; $cx > ( 9 - $len ); $cx-- ) {
      if( ctype_alpha( substr( $datetime, $cx )))
        $tz = substr( $datetime, $cx );
      else
        break;
    }
    $datestring = date( 'Y-m-d H:i:s', strtotime( $datetime ));
    $output     = array();
    $output['year']    = substr( $datestring, 0, 4 );
    $output['month']   = substr( $datestring, 5, 2 );
    $output['day']     = substr( $datestring, 8, 2 );
    if(( 6 == $parno ) || ( 7 == $parno )) {
      $output['hour']  = substr( $datestring, 11, 2 );
      $output['min']   = substr( $datestring, 14, 2 );
      $output['sec']   = substr( $datestring, 17, 2 );
      if( $tz )
        $output['tz']  = $tz;
    }
    elseif( 3 != $parno ) {
      if(( '00' < substr( $datestring, 11, 2 )) ||
         ( '00' < substr( $datestring, 14, 2 )) ||
         ( '00' < substr( $datestring, 17, 2 ))) {
        $output['hour']  = substr( $datestring, 11, 2 );
        $output['min']   = substr( $datestring, 14, 2 );
        $output['sec']   = substr( $datestring, 17, 2 );
        if( $tz )
          $output['tz']  = $tz;
      }
    }
    return $output;
  }
/*
 * ensures internal duration format for input in array format
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-15
 * @param array 
 * @return array 
 */
  function _duration_array( $duration ) {
    $output = array();
    foreach( $duration as $durKey => $durValue ) {
      switch ( $durKey ) {
        case '0': case 'week': $output['week']  = $durValue; break;
        case '1': case 'day':  $output['day']   = $durValue; break;
        case '2': case 'hour': $output['hour']  = $durValue; break;
        case '3': case 'min':  $output['min']   = $durValue; break;
        case '4': case 'sec':  $output['sec']   = $durValue; break;
      }
    }
    if( isset( $output['week'] ) && ( 0 < $output['week'] ))
      return $output;
    elseif (( isset( $output['hour'] ) && ( 0 < $output['hour'] )) || 
            ( isset( $output['min'] )  && ( 0 < $output['min']  )) || 
             (isset( $output['sec'] )  && ( 0 < $output['sec']  ))) {
      if( !isset( $output['hour'] ))
        $output['hour'] = 0;
      if( !isset( $output['min']  ))
        $output['min']  = 0;
      if( !isset( $output['sec']  ))
        $output['sec']  = 0;
    }
    return $output;
  }
/**********************************************************************************
 **********************************************************************************
/*
 * add calendar component as subcomponent to container for subcomponents
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param object $component calendar component
 * @return void
 */
  function addSubComponent ( $component ) {
    $this->subcomponents[]     = $component;
  }
/*
 *
 * creates formatted output for subcomponents
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-11
 * @return string
 */
  function createSubComponent ( ) {
    $subcomponents = null;

    foreach( $this->subcomponents as $component ) {
      if( !isset( $component->language ))
        $component->language  = $this->language;
      if( !isset( $component->nl ))
        $component->nl        = $this->nl;
      if( !isset( $component->unique_id ))
        $component->unique_id = $this->unique_id;

      $subcomponents .= $component->createComponent();
    }

    return $subcomponents;
  }
/*
 * set calendar component property unique_id, used in _makeUid function
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @return void
 */
  function setUnique_id( $value ) {
    $this->unique_id = $value;
  }
/**********************************************************************************
 **********************************************************************************
 * Lines of text SHOULD NOT be longer than 75 octets, excluding the line
 * break. Long content lines SHOULD be split into a multiple line
 * representations using a line "folding" technique. That is, a long
 * line can be split between any two characters by inserting a CRLF
 * immediately followed by a single linear white space character (i.e.,
 * SPACE, US-ASCII decimal 32 or HTAB, US-ASCII decimal 9). Any sequence
 * of CRLF followed immediately by a single linear white space character
 * is ignored (i.e., removed) when processing the content type.
 *
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $value
 * @return string
 */
  function _size75( $string ) {
    $strlen = strlen( $string );
    $tmp    = $string;
    $string = null;
    while( $strlen > 75 ) {
      $string .= substr( $tmp, 0, 75 );
      $string .= $this->nl;
      $tmp     = ' '.substr( $tmp, 75 );
      $strlen  = strlen( $tmp );
    }
    $string .= rtrim( $tmp ); // the rest
    if( $this->nl != substr( $string, ( 0 - strlen( $this->nl ))))
      $string .= $this->nl;
    return $string;
  }
/*
 * special characters
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @param string $string
 * @return string
 */
  function _strrep( $string ) {
    $string = str_replace( $this->nl, '',     $string);
    $string = str_replace('"',        "'",    $string);
    $string = str_replace('\\',       '\\\\', $string);
    $string = str_replace(',',        '\,',   $string);
    $string = str_replace(';',        '\;',   $string);
    return $string;
  }

}


/**********************************************************************************
 **********************************************************************************
 * class for calendar component VEVENT
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 */
class vevent extends calendarComponent {

/*
 * constructor for calendar component vevent object
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function vevent() {
    $this->calendarComponent();
  }


/*
 * create formatted output for calendar component vevent object instance
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createComponent() {
    $component  = null;
    $component .= 'BEGIN:VEVENT'.$this->nl;

    $component .= $this->createAttach();
    $component .= $this->createAttendee();
    $component .= $this->createCategories();
    $component .= $this->createComment();
    $component .= $this->createContact();
    $component .= $this->createClass();
    $component .= $this->createCreated();
    $component .= $this->createDescription();
    $component .= $this->createDtend();
    $component .= $this->createDtstamp();
    $component .= $this->createDtstart();
    $component .= $this->createDue();
    $component .= $this->createDuration();
    $component .= $this->createExdate();
    $component .= $this->createExrule();
    $component .= $this->createGeo();
    $component .= $this->createLastModified();
    $component .= $this->createLocation();
    $component .= $this->createOrganizer();
    $component .= $this->createPriority();
    $component .= $this->createRdate();
    $component .= $this->createRelatedTo();
    $component .= $this->createRequestStatus();
    $component .= $this->createRecurrenceid();
    $component .= $this->createResources();
    $component .= $this->createRrule();
    $component .= $this->createSequence();
    $component .= $this->createStatus();
    $component .= $this->createSummary();
    $component .= $this->createTransp();
    $component .= $this->createUid();
    $component .= $this->createUrl();
    $component .= $this->createXprop();

    $component .= $this->createSubComponent();

    $component .= 'END:VEVENT'.$this->nl;

    return $component;
  }
}

/**********************************************************************************
 **********************************************************************************
 * class for calendar component VTODO
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 */
class vtodo extends calendarComponent {

/*
 * constructor for calendar component vtodo object
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function vtodo() {
    $this->calendarComponent();
  }

/*
 * create formatted output for calendar component vtodo object instance
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createComponent() {
    $component  = null;
    $component .= 'BEGIN:VTODO'.$this->nl;

    $component .= $this->createAttach();
    $component .= $this->createAttendee();
    $component .= $this->createCategories();
    $component .= $this->createClass();
    $component .= $this->createComment();
    $component .= $this->createCompleted();
    $component .= $this->createContact();
    $component .= $this->createCreated();
    $component .= $this->createDescription();
    $component .= $this->createDtstamp();
    $component .= $this->createDtstart();
    $component .= $this->createDue();
    $component .= $this->createDuration();
    $component .= $this->createExdate();
    $component .= $this->createExrule();
    $component .= $this->createGeo();
    $component .= $this->createLastModified();
    $component .= $this->createLocation();
    $component .= $this->createOrganizer();
    $component .= $this->createPercentComplete();  
    $component .= $this->createPriority();
    $component .= $this->createRdate();
    $component .= $this->createRelatedTo();
    $component .= $this->createRequestStatus();
    $component .= $this->createRecurrenceid();
    $component .= $this->createResources();
    $component .= $this->createRrule();
    $component .= $this->createSequence();
    $component .= $this->createStatus();
    $component .= $this->createSequence();
    $component .= $this->createSummary();
    $component .= $this->createUid();
    $component .= $this->createUrl();
    $component .= $this->createXprop();

    $component .= 'END:VTODO'.$this->nl;

    return $component;
  }
}
/**********************************************************************************
 **********************************************************************************
 * class for calendar component VJOURNAL
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 */
class vjournal extends calendarComponent {

/*
 * constructor for calendar component vjournal object
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function vjournal() {
    $this->calendarComponent();
  }

/*
 * create formatted output for calendar component vjournal object instance
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createComponent() {
    $component  = null;
    $component .= 'BEGIN:VJOURNAL'.$this->nl;

    $component .= $this->createAttendee();
    $component .= $this->createAttach();
    $component .= $this->createCategories();
    $component .= $this->createClass();
    $component .= $this->createComment();
    $component .= $this->createCreated();
    $component .= $this->createDescription();
    $component .= $this->createDtstamp();
    $component .= $this->createDtstart();
    $component .= $this->createExdate();
    $component .= $this->createExrule();
    $component .= $this->createFreebuzy();
    $component .= $this->createLastModified();
    $component .= $this->createOrganizer();
    $component .= $this->createRdate();
    $component .= $this->createRequestStatus();
    $component .= $this->createRecurrenceid();
    $component .= $this->createRelatedTo();
    $component .= $this->createRrule();
    $component .= $this->createSequence();
    $component .= $this->createStatus();
    $component .= $this->createSummary();
    $component .= $this->createUid();
    $component .= $this->createUrl();
    $component .= $this->createXprop();

    $component .= 'END:VJOURNAL'.$this->nl;

    return $component;
  }
}
/**********************************************************************************
 **********************************************************************************
 * class for calendar component VFREEBUZY
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 */
class vfreebuzy extends calendarComponent {

/*
 * constructor for calendar component vfreebuzy object
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function vfreebuzy() {
    $this->calendarComponent();
  }

/*
 * create formatted output for calendar component vfreebuzy object instance
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createComponent() {
    $component  = null;
    $component .= 'BEGIN:VFREEBUZY'.$this->nl;

    $component .= $this->createAttendee();
    $component .= $this->createComment();
    $component .= $this->createContact();
    $component .= $this->createDtend();
    $component .= $this->createDtstart();
    $component .= $this->createDtstamp();
    $component .= $this->createDuration();
    $component .= $this->createFreebuzy();
    $component .= $this->createOrganizer();
    $component .= $this->createRequestStatus();
    $component .= $this->createUid();
    $component .= $this->createUrl();
    $component .= $this->createXprop();

    $component .= 'END:VFREEBUZY'.$this->nl;

    return $component;
  }
}
/**********************************************************************************
 **********************************************************************************
 * class for calendar component VALARM
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 */
class valarm extends calendarComponent {

/*
 * constructor for calendar component valarm object
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return void
 */
  function valarm() {
    $this->calendarComponent();
  }

/*
 * create formatted output for calendar component valarm object instance
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-10
 * @return string
 */
  function createComponent() {
    $component  = null;
    $component .= 'BEGIN:VALARM'.$this->nl;

    $component .= $this->createAction();
    $component .= $this->createAttach();
    $component .= $this->createAttendee();
    $component .= $this->createDescription();
    $component .= $this->createDtstamp();
    $component .= $this->createDuration();
    $component .= $this->createRepeat();
    $component .= $this->createTrigger();
    $component .= $this->createXprop();

    $component .= 'END:VALARM'.$this->nl;

    return $component;
  }
}

/**********************************************************************************
 **********************************************************************************
 * class for calendar component VTIMEZONE
 * @author Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since 0.3.0 - 2006-08-13
 */

class vtimezone extends calendarComponent {

  var $timezonetype;

  function vtimezone( $timezonetype=FALSE ) {
    $this->calendarComponent();

    if( !$timezonetype )
      $this->timezonetype = 'VTIMEZONE';
    else
      $this->timezonetype = strtoupper( $timezonetype );
  }

  function createComponent() {
    $component  = null;
    $component .= 'BEGIN:'.$this->timezonetype.$this->nl;

    $component .= $this->createTzid();
    $component .= $this->createLastModified();
    $component .= $this->createTzurl();

    $component .= $this->createDtstart( TRUE );
    $component .= $this->createTzoffsetfrom();
    $component .= $this->createTzoffsetto();

    $component .= $this->createComment();
    $component .= $this->createRdate( TRUE );
    $component .= $this->createRrule();
    $component .= $this->createTzname();
    $component .= $this->createXprop();

    $component .= $this->createSubComponent();

    $component .= 'END:'.$this->timezonetype.$this->nl;

    return $component;
  }
}

?>
