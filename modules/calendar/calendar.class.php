<?php /* CALENDAR $Id: calendar.class.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
##
## Calendar classes
##

require_once( $AppUI->getLibraryClass( 'PEAR/Date' ) );
require_once( $AppUI->getSystemClass ('dp' ) );

/**
* Displays a configuration month calendar
*
* All Date objects are based on the PEAR Date package
*/

class CMonthCalendar {
/**#@+
* @var Date
*/
	var $this_month;
	var $prev_month;
	var $next_month;
	var $prev_year;
	var $next_year;
/**#@-*/

/** @var string The css style name of the Title */
	var $styleTitle;

/** @var string The css style name of the main calendar */
	var $styleMain;

/** @var string The name of the javascript function that a 'day' link should call when clicked */
	var $callback;

/** @var boolean Show the heading */
	var $showHeader;

/** @var boolean Show the previous/next month arrows */
	var $showArrows;
	var $showArrowsYear;

/** @var boolean Show the day name column headings */
	var $showDays;

/** @var boolean Show the week link (no pun intended) in the first column */
	var $showWeek;

/** @var boolean Show the month name as link */
	var $clickMonth;

/** @var boolean Show events in the calendar boxes */
	var $showEvents;

/** @var string */
	var $dayFunc;

/** @var string */
	var $weekFunc;

	var $suppressLogo;

	//No deberia usarse
	var $user_id;

	var $delegator_id;

/**
* @param Date $date
*/
 function CMonthCalendar( $date=null )
 {
		$this->setDate( $date );

		$this->classes = array();
		$this->callback = '';
		$this->showTitle = true;
		$this->showArrows = true;
		$this->showDays = true;
		$this->showWeek = true;
		$this->showEvents = true;

		$this->styleTitle = '';
		$this->styleMain = '';

		$this->dayFunc = '';
		$this->weekFunc = '';

		$this->events = array();
		$this->suppressLogo = $_GET["suppressLogo"];
	}
// setting functions

/**
 * CMonthCalendar::setDate()
 *
 * { Description }
 *
 * @param [type] $date
 */
	 function setDate( $date=null )
	 {
		$this->this_month = new CDate( $date );

		$d = $this->this_month->getDay();
		$m = $this->this_month->getMonth();
		$y = $this->this_month->getYear();

		//$date = Date_Calc::beginOfPrevMonth( $d, $m, $y-1, FORMAT_ISO );
		$this->prev_year = new CDate( $date );
		$this->prev_year->setYear( $this->prev_year->getYear()-1 );

		$this->next_year = new CDate( $date );
		$this->next_year->setYear( $this->next_year->getYear()+1 );

		$date = Date_Calc::beginOfPrevMonth( $d, $m, $y, FMT_TIMESTAMP_DATE );
		$this->prev_month = new CDate( $date );

		$date = Date_Calc::beginOfNextMonth( $d, $m, $y, FMT_TIMESTAMP_DATE );
		$this->next_month =  new CDate( $date );

	}

/**
 * CMonthCalendar::setStyles()
 *
 * { Description }
 *
 */
	 function setStyles( $title, $main ) {
		$this->styleTitle = $title;
		$this->styleMain = $main;
	}

/**
 * CMonthCalendar::setLinkFunctions()
 *
 * { Description }
 *
 * @param string $day
 * @param string $week
 */
	function setLinkFunctions( $day='', $week='' ) {
		$this->dayFunc = $day;
		$this->weekFunc = $week;
	}

/**
 * CMonthCalendar::setCallback()
 *
 * { Description }
 *
 */
	function setCallback( $function ) {
		$this->callback = $function;
	}

/**
 * CMonthCalendar::setEvents()
 *
 * { Description }
 *
 */
 function setEvents( $e ) {
		$this->events = $e;
	}
// drawing functions
/**
 * CMonthCalendar::show()
 *
 * { Description }
 *
 */
	 function show() {
		$s = '';
		if ($this->showTitle) {
			$s .= $this->_drawTitle();
		}
		$s .= "<table border=\"0\" cellspacing=\"1\" cellpadding=\"2\" width=\"100%\" class=\"" . $this->styleMain . "\">\n";

		if ($this->showDays) {
			$s .= $this->_drawDays();
		}

		$s .= $this->_drawMain();

		$s .= "</table>\n";

		return $s;
	}

     function show_big() {

		$s = '';
		if ($this->showTitle) {
			$s .= $this->_drawTitle();
		}
		$s .= "<table border=\"0\" cellspacing=\"1\" cellpadding=\"2\" width=\"100%\" class=\"" . $this->styleMain . "\">\n";

		if ($this->showDays) {
			$s .= $this->_drawDays_full();
		}

		$s .= $this->_drawMain();

		$s .= "</table>\n";

		return $s;
	}

/**
 * CMonthCalendar::_drawTitle()
 *
 * { Description }
 *
 */
	 function _drawTitle()
	 {
		global $AppUI, $m, $a;

		$url = "./index.php?m=$m";
		$url .= $a ? "&a=$a" : '';

		$url .= isset( $_GET['dialog']) ? "&dialog=".$_GET['dialog'] : '';

		//nuevo diseño
        $s = "\n<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" class=\"\" background=\"images/common/back_1linea_06.gif\">";
        $s .= "\n\t<tr>";
        $s .= "\n\t\t<td width=\"6\"><img src=\"images/common/inicio_1linea.gif\" width=\"6\" height=\"19\"></td>";
        if ($this->showArrows) {
            $href = $url.($this->suppressLogo ? '&suppressLogo='.$this->suppressLogo : '').'&date='.$this->prev_month->format(FMT_TIMESTAMP_DATE).($this->callback ? '&callback='.$this->callback : '');
            $href .= ($this->delegator_id ? '&delegator_id='.$this->delegator_id : '');
            $s .= "\n\t\t<td align=\"left\">";
            $s .= '<a href="'.$href.'"><img src="./images/prev.gif" width="16" height="16" alt="'.$AppUI->_('previous month').'" border="0" /></a>';
            $s .= "</td>";

        }


        $s .= "\n\t<td width=\"99%\" align=\"center\" class=\"tableHeaderText\">";
        if ($this->clickMonth) {
            $href = $url.'&date='.$this->this_month->format(FMT_TIMESTAMP_DATE).($this->callback ? '&callback='.$this->callback : '').'&suppressLogo='.$this->suppressLogo.'&delegator_id='.$this->delegator_id;
            $s .= '<a href="'.$href.'">';
        }

		$mes_tmp = htmlentities($this->this_month->format( "%B"));

        $s .= $AppUI->_($mes_tmp)."</a>&nbsp;";

        if ($this->showArrowsYear){

        	$base_url = "./index.php?c=m";

        	if (isset($_GET['m']))
        	{
        		$base_url .= "&m=$m";
        	}

        	if (isset($_GET['a']))
        	{
        		$base_url .= "&a=$a";
        	}

            $hrefini = $base_url.($this->suppressLogo ? '&suppressLogo='.$this->suppressLogo : '').'&date=';
            $hreffin = ($this->callback ? '&callback='.$this->callback : '');
            $hreffin .= isset( $_GET['dialog']) ? "&dialog=".$_GET['dialog'] : '';
            $hreffin .= ($this->delegator_id ? '&delegator_id='.$this->delegator_id : '');

            $s .= "<script>

            <!--
                function gotoYear(year){    document.location = \"$hrefini\" + year + \"$hreffin\";}
            --></script>";

            $anos = array();
            $min_ano = intval($this->this_month->format( "%Y" ))-100;
            $max_ano = intval($this->this_month->format( "%Y" ))+100;
            for($i=$min_ano; $i<=$max_ano; $i++){
                $anos[htmlentities($i.$this->this_month->format("%m")."01" )] = "$i";
            }

            //$s .= "\n\t\t<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>";
            //$href = $url.($this->suppressLogo ? '&suppressLogo='.$this->suppressLogo : '').'&date='.$this->prev_year->format(FMT_TIMESTAMP_DATE).($this->callback ? '&callback='.$this->callback : '');
            //$href .= ($this->delegator_id ? '&delegator_id='.$this->delegator_id : '');

            //$s .= '<a href="'.$href.'"><img src="./images/prev.gif" width="16" height="16" alt="'.$AppUI->_('previous year').'" border="0" /></a>';
            //$s .= htmlentities($this->this_month->format( "%Y" ));
            $s .= arraySelect( $anos, 'anio', 'class="text" size="1" onChange="javascript: gotoYear(this.value);" ', htmlentities($this->this_month->format( "%Y%m" ))."01", '', '', '60px' );
            //$s .= "<input type=\"text\" class=\"text\" value=\"".htmlentities($this->this_month->format( "%Y" ))."\" size=\"4\" /> ";
            //$href = $url.($this->suppressLogo ? '&suppressLogo='.$this->suppressLogo : '').'&date='.$this->next_year->format(FMT_TIMESTAMP_DATE).($this->callback ? '&callback='.$this->callback : '');
            //$href .= ($this->delegator_id ? '&delegator_id='.$this->delegator_id : '');
            //$s .= '<a href="'.$href.'"><img src="./images/next.gif" width="16" height="16" alt="'.$AppUI->_('next year').'" border="0" /></a>';
            //$s .= "</td></tr></table>";
        }else{
            $s .= htmlentities($this->this_month->format( "%Y" ));
        }
        $s .= "</td>";

        if ($this->showArrows) {
            $href = $url.($this->suppressLogo ? '&suppressLogo='.$this->suppressLogo : '').'&date='.$this->next_month->format(FMT_TIMESTAMP_DATE).($this->callback ? '&callback='.$this->callback : '');
            $href .= ($this->delegator_id ? '&delegator_id='.$this->delegator_id : '');
            $s .= "\n\t\t<td align=\"right\">";
            $s .= '<a href="'.$href.'"><img src="./images/next.gif" width="16" height="16" alt="'.$AppUI->_('next month').'" border="0" /></a>';
            $s .= "</td>";
        }
        $s .= "\n\t\t<td width=\"6\" align=\"right\"><img src=\"images/common/fin_1linea.gif\" width=\"3\" height=\"19\"></td>";
        $s .= "\n\t</tr>";
        $s .= "\n</table>";

        //fin nuevo diseño


		/*$s .= "\n<table border=\"0\" cellspacing=\"0\" cellpadding=\"3\" width=\"100%\" class=\"$this->styleTitle\">";
		$s .= "\n\t<tr>";

		if ($this->showArrows) {
			$href = $url.($this->suppressLogo ? '&suppressLogo='.$this->suppressLogo : '').'&date='.$this->prev_month->format(FMT_TIMESTAMP_DATE).($this->callback ? '&callback='.$this->callback : '');
			$href .= ($this->delegator_id ? '&delegator_id='.$this->delegator_id : '');
			$s .= "\n\t\t<td align=\"left\">";
			$s .= '<a href="'.$href.'"><img src="./images/prev.gif" width="16" height="16" alt="'.$AppUI->_('previous month').'" border="0" /></a>';
			$s .= "</td>";

		}


		$s .= "\n\t<th width=\"99%\" align=\"center\">";
		if ($this->clickMonth) {
			$href = $url.'&date='.$this->this_month->format(FMT_TIMESTAMP_DATE).($this->callback ? '&callback='.$this->callback : '').'&suppressLogo='.$this->suppressLogo.'&delegator_id='.$this->delegator_id;
			$s .= '<a href="'.$href.'">';
		}
		$s .= htmlentities($this->this_month->format( "%B"))."</a>&nbsp;";

		if ($this->showArrowsYear){

			$hrefini = $url.($this->suppressLogo ? '&suppressLogo='.$this->suppressLogo : '').'&date=';
			$hreffin = ($this->callback ? '&callback='.$this->callback : '');
			$hreffin .= ($this->delegator_id ? '&delegator_id='.$this->delegator_id : '');
			$s .= "<script>
			<!--
				function gotoYear(year){	document.location = \"$hrefini\" + year + \"$hreffin\";}
			--></script>";

			$anos = array();
			for($i=intval(date("Y"))-99; $i<=intval(date("Y")); $i++){
				$anos[htmlentities($i.$this->this_month->format("%m")."01" )] = "$i";
			}

			//$s .= "\n\t\t<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>";
			//$href = $url.($this->suppressLogo ? '&suppressLogo='.$this->suppressLogo : '').'&date='.$this->prev_year->format(FMT_TIMESTAMP_DATE).($this->callback ? '&callback='.$this->callback : '');
			//$href .= ($this->delegator_id ? '&delegator_id='.$this->delegator_id : '');

			//$s .= '<a href="'.$href.'"><img src="./images/prev.gif" width="16" height="16" alt="'.$AppUI->_('previous year').'" border="0" /></a>';
			//$s .= htmlentities($this->this_month->format( "%Y" ));
			$s .= arraySelect( $anos, 'anio', 'class="text" size="1" onChange="javascript: gotoYear(this.value);" ', htmlentities($this->this_month->format( "%Y%m" ))."01" );
			//$s .= "<input type=\"text\" class=\"text\" value=\"".htmlentities($this->this_month->format( "%Y" ))."\" size=\"4\" /> ";
			//$href = $url.($this->suppressLogo ? '&suppressLogo='.$this->suppressLogo : '').'&date='.$this->next_year->format(FMT_TIMESTAMP_DATE).($this->callback ? '&callback='.$this->callback : '');
			//$href .= ($this->delegator_id ? '&delegator_id='.$this->delegator_id : '');
			//$s .= '<a href="'.$href.'"><img src="./images/next.gif" width="16" height="16" alt="'.$AppUI->_('next year').'" border="0" /></a>';
			//$s .= "</td></tr></table>";
		}else{
			$s .= htmlentities($this->this_month->format( "%Y" ));
		}
		$s .= "</th>";

		if ($this->showArrows) {
			$href = $url.($this->suppressLogo ? '&suppressLogo='.$this->suppressLogo : '').'&date='.$this->next_month->format(FMT_TIMESTAMP_DATE).($this->callback ? '&callback='.$this->callback : '');
			$href .= ($this->delegator_id ? '&delegator_id='.$this->delegator_id : '');
			$s .= "\n\t\t<td align=\"right\">";
			$s .= '<a href="'.$href.'"><img src="./images/next.gif" width="16" height="16" alt="'.$AppUI->_('next month').'" border="0" /></a>';
			$s .= "</td>";
		}

		$s .= "\n\t</tr>";
		$s .= "\n</table>"; */

		return $s;
	}
/**
* CMonthCalendar::_drawDays()
*
* { Description }
*
* @return string Returns table a row with the day names
*/
	function _drawDays() {

		global $AppUI;

		$bow = Date_Calc::beginOfWeek( null,null,null,null,LOCALE_FIRST_DAY );
		$y = substr( $bow, 0, 4 );
		$m = substr( $bow, 4, 2 );
		$d = substr( $bow, 6, 2 );
		$wk = Date_Calc::getCalendarWeek( $d, $m, $y, "%a", LOCALE_FIRST_DAY );

		$s = $this->showWeek ? "\n\t\t<th>&nbsp;</th>" : "";
		foreach( $wk as $day ) {
			$tmp_day = htmlentities($day);

			$s .= "\n\t\t<th width=\"14%\">".$AppUI->_($tmp_day)."</th>";
		}

		return "\n<tr>$s\n</tr>";
	}

  function  _drawDays_full() {

		global $AppUI;

		$bow = Date_Calc::beginOfWeek( null,null,null,null,LOCALE_FIRST_DAY );
		$y = substr( $bow, 0, 4 );
		$m = substr( $bow, 4, 2 );
		$d = substr( $bow, 6, 2 );
		$wk = Date_Calc::getCalendarWeek( $d, $m, $y, "%A", LOCALE_FIRST_DAY );

		$s = $this->showWeek ? "\n\t\t<th>&nbsp;</th>" : "";
		foreach( $wk as $day ) {
			$tmp_day = htmlentities($day);

			$s .= "\n\t\t<th width=\"14%\">".$AppUI->_($tmp_day)."</th>";
		}

		return "\n<tr>$s\n</tr>";
	}
/**
 * CMonthCalendar::_drawMain()
 *
 * { Description }
 *
 */
	 function _drawMain() {
		GLOBAL $AppUI;
		$today = new CDate();
		$today = $today->format( "%Y%m%d%w" );

		$date = $this->this_month;
		$this_day = $date->getDay();
		$this_month = $date->getMonth();
		$this_year = $date->getYear();
		$cal = Date_Calc::getCalendarMonth( $this_month, $this_year, "%Y%m%d%w", LOCALE_FIRST_DAY );

		$df = $AppUI->getPref( 'SHDATEFORMAT' );

		$html = '';
		foreach ($cal as $week) {
			$html .= "\n<tr>";
			if ($this->showWeek) {
				$html .=  "\n\t<td class=\"week\">";
				$html .= $this->dayFunc ? "<a href=\"javascript:$this->weekFunc('$week[0]')\">" : '';
				$html .= '<img src="./images/view.week.gif" width="16" height="15" border="1" alt="Week View" /></a>';
				$html .= $this->dayFunc ? "</a>" : '';
				$html .= "\n\t</td>";
			}

			foreach ($week as $day) {
				$this_day = new CDate( $day );
				$y = intval( substr( $day, 0, 4 ) );
				$m = intval( substr( $day, 4, 2 ) );
				$d = intval( substr( $day, 6, 2 ) );
				$dow = intval( substr( $day, 8, 1 ) );

				if ($m != $this_month) {
					$class = 'empty';
				} else if ($day == $today) {
					$class = 'today';
				} else if ($dow == 0 || $dow == 6) {
					$class = 'weekend';
				} else {
					$class = 'day';
				}
				$day = substr( $day, 0, 8 );
				$html .= "\n\t<td class=\"$class\">";
				if ($this->dayFunc) {
					$html .= "<a href=\"javascript:$this->dayFunc('$day','".$this_day->format( $df )."')\" class=\"$class\">";
					$html .= htmlentities($d);
					$html .= "</a>";
				} else {
					$html .= htmlentities($d);
				}
				if ($m == $this_month && $this->showEvents) {
					$html .= $this->_drawEvents( substr( $day, 0, 8 ) );
				}
				$html .= "\n\t</td>";
			}
			$html .= "\n</tr>";
		}
		return $html;
	}

/**
 * CMonthCalendar::_drawWeek()
 *
 * { Description }
 *
 */
	 function _drawWeek( $dateObj ) {
		$href = "javascript:$this->weekFunc(".$dateObj->getTimestamp().",'".$dateObj->toString()."')";
		$w = "        <td class=\"week\">";
		$w .= $this->dayFunc ? "<a href=\"$href\">" : '';
		$w .= '<img src="./images/view.week.gif" width="16" height="15" border="0" alt="Week View" /></a>';
		$w .= $this->dayFunc ? "</a>" : '';
		$w .= "</td>\n";
		return $w;
	}

/**
 * CMonthCalendar::_drawEvents()
 *
 * { Description }
 *
 */
	 function _drawEvents( $day ) {
		$s = '';
		if (!isset( $this->events[$day] )) {
			return '';
		}
		$events = $this->events[$day];
		foreach ($events as $e) {
			$href = isset($e['href']) ? $e['href'] : null;
			$alt = isset($e['alt']) ? $e['alt'] : null;

			$s .= "<br />\n";
			$s .= $href ? "<a href=\"$href\" class=\"event\" title=\"$alt\">" : '';
			$s .= "{$e['text']}";
			$s .= $href ? '</a>' : '';
		}
		return $s;
	}
}

/**
* Event Class
*
* { Description }
*
*/
class CEvent extends CDpObject {
/** @var int */
	var $event_id = NULL;

/** @var string The title of the event */
	var $event_title = NULL;

	var $event_start_date = NULL;
	var $event_end_date = NULL;
	//var $event_parent = NULL;
	var $event_description = NULL;
	//var $event_remind = NULL;
	var $event_icon = NULL;
	var $event_owner = NULL;
	var $event_creator = NULL;
	//var $event_project = NULL;
	//var $event_private = NULL;
	var $event_type = NULL;
	//Este atributo probablemente esta de mas
	//var $event_recurs = NULL;
	var $event_location = NULL;
	//Eventos recursivos
	var $event_recurse_type = NULL;
	var $event_start_time = NULL;
	var $event_end_time = NULL;
	var $event_no_occurrences = NULL;
	var $event_end_occurrence = NULL;
	var $event_recur_every_x_days = NULL; //Repite cada X dias
	var $event_recur_every_week_day = NULL; //Boolean: repite todos los dias de semana?
	var $event_recur_every_dd_day = NULL; //Repite todos los dias DD
	var $event_recur_every_nd_day = NULL; //Repite el nº DIA
	var $event_recur_every_n_day = NULL; //Repite el DIA
	var $event_recur_every_x_months = NULL; //Repite cada X meses
	var $event_recur_every_x_weeks = NULL; //Repite cada X semanas
	var $event_recur_every_n_days = NULL; //Repite los dias N
	var $event_recur_every_mm_month = NULL; //Repite todos los meses MM
	//Para las invitaciones
	var $event_invitation_type = NULL;
	var $event_project = NULL;
	var $event_company = NULL;
	var $event_task = 0;
	var $event_salepipeline = 0;

	function CEvent()
	{
		$this->CDpObject( 'events', 'event_id' );
	}

// overload check operation
	function check()
	{
	// ensure changes to check boxes and select lists are honoured
		$this->event_type = intval( $this->event_type );
		//Hay que chequear consistencia de los tipos de recursion.
		return NULL;
	}

	function delete()
	{
		if ( $a = CDpObject::delete() )
		{
			return $a;
		}
		else
		{
			$sql = "DELETE FROM events_invitations WHERE event_id = $this->event_id";
			if ( !db_exec( $sql ) )
			{
				return db_error();
			}
			else
			{
				return NULL;
			}
		}
	}

/**
* Utility function to return an array of people invited to this event
* @return array A list of people
*/
	function getInvitations($order = null)
	{
		$sSql = "SELECT ei.invitation_id, ei.invitation_status, ei.invitation_mail, u.user_last_name, u.user_first_name, u.user_id, u.user_email,
        c.contact_last_name, c.contact_first_name, c.contact_id, c.contact_email, invitation_sent
		FROM events_invitations ei
		LEFT JOIN users u
		ON u.user_id = ei.user_id
        LEFT JOIN contacts c
            ON c.contact_id = ei.contact_id
		WHERE ei.event_id = $this->event_id";
		if ( $order )
		{
			$sSql .= "\nORDER BY $order";
		}
		//echo "<pre>$sSql</pre>";

		return db_loadList($sSql);
	}
/**
@author Mauro Chojrin
@return Fecha de la primera ocurrencia del evento
*/
	function getDailyFirstOccurrence()
	{

        $d = new CDate($this->event_start_date);

		if ( $this->event_recur_every_week_day )
		{
			while ( !$d->isWorkingDay() )
			{
				$d->addDays(1);
			}
		}
		return $d;
	}

/**
@author Mauro Chojrin
@param $d Fecha a partir de la cual calcular la proxima ocurrencia
@return Fecha de la primera ocurrencia del evento
*/

	function getDailyNextOccurrence( $d )
	{
		//echo "<p>Calculando proxima ocurrencia de '$this->event_title'</p>";
		if ( $this->event_recur_every_week_day )
		{
			do
			{
				$d->addDays(1);
			}
			while ( !$d->isWorkingDay());
		}
		else
		{
			$d->addDays( $this->event_recur_every_x_days );
		}

		return $d;
	}

/**
@author Mauro Chojrin
@return Devuelve la primera ocurrencia del evento semanal
*/
	function getWeeklyFirstOccurrence()
	{
		$d = new CDate($this->event_start_date);

		//Adelanto hasta llegar a un día en que ejecute el evento
		while ( !$this->event_recur_every_n_days[$d->getDayOfWeek()] )
		{
			$d->addDays(1);
		}
		return $d;
	}

/**
@author Mauro Chojrin
@param $d Fecha a partir de la cual calcular proxima ocurrencia
@return Fecha de proxima ocurrencia del evento
*/
	function getWeeklyNextOccurrence( $d )
	{
		//echo "<p>Calculando weekly next occ a partir de '".$d->format( FMT_DATETIME_MYSQL )."'</p>";
		//echo "<p>Dia de la semana = '".$d->getDayOfWeek()."'</p>";
		if ( $d->getDayOfWeek() == 6 )
		{
			//Salto a la proxima semana
			$d->addDays(1);
			$d->addWeeks( ( $this->event_recur_every_x_weeks - 1 ) );
		}
		else
		{
			$d->addDays(1);
		}
		//Si no es sabado, todavia puede haber una ocurrencia en la misma semana.

		//Ya estoy en la proxima semana de ejecución
		//Busco otra ocurrencia en la misma semana
		while( $this->event_recur_every_n_days[$d->getDayOfWeek()] != "1" && $d->getDayOfWeek() != 6 )
		{
			$d->addDays(1);
		}

		if ( $d->getDayOfWeek() == 6 )
		{ 	//Si es sabado estoy por cambiar de semana
			if ( !$this->event_recur_every_n_days[6] )
			{
				$d->addDays(1);
				//Cambie de semana y estoy en domingo
				$d->addWeeks( ( $this->event_recur_every_x_weeks - 1 ) );//Con un dia que sumo ya cambie de semana
				while( $this->event_recur_every_n_days[$d->getDayOfWeek()] != "1" )
				{
					$d->addDays(1);
				}
			}
			//Si ocurre justo el domingo de esta semana lo dejo como está
		}
		//Si el dia de semana no es sabado es que habia otra ocurrencia en la misma semana.

		return $d;
	}

/**
@author Mauro Chojrin
@return Fecha de primera ocurrencia del evento
*/

	function getMonthlyFirstOccurrence()
	{
		$d = new CDate( $this->event_start_date );

		//echo "<p>Fecha de comienzo del evento = '".$d->format( FMT_DATETIME_MYSQL )."'</p>";

		if ( $this->event_recur_every_dd_day > 0 )
		{
			//Ocurre todos los dd de cada X meses
			if ( $d->getDay() <= $this->event_recur_every_dd_day )
			{
				//Si el dia es anterior lo adelanto hasta el dia dd
				$d->setDay( $this->event_recur_every_dd_day );
			}
			else
			{
				//Si el dia es posterior hay que sumarle los meses
				if ( $d->getDay() > $this->event_recur_every_dd_day )
				{
					$d->addMonths( $this->event_recur_every_x_months );
					$d->setDay( $this->event_recur_every_dd_day );
				}
			}
			return $d;
		}
		else
		{
			//Ocurre el iº N de cada mes
			$aux = CDate::getIDayNOfMonth( $d->getYear(), $d->getMonth(), $this->event_recur_every_nd_day, $this->event_recur_every_n_day );
			if ( CDate::compare($aux, $d) >= 0 )
			{
				$d = $aux;
			}
			else
			{
				//Ya paso el dia, hay que probar el del proximo mes
				$d = CDate::getIDayNOfMonth( $d->getYear(), $d->getMonth() + $this->event_recur_every_x_months, $this->event_recur_every_nd_day, $this->event_recur_every_n_day );
			}
		}
		return $d;
	}

/**
@author Mauro Chojrin
@param $d Fecha a partir de la cual calcular proxima ocurrencia
@return Fecha de la proxima ocurrencia
*/

	function getMonthlyNextOccurrence( $d )
	{
		if ( $this->event_recur_every_dd_day > 0 )
		{
			//Ocurre todos los dd de cada X meses
			if ( $d->getDay() < $this->event_recur_every_dd_day )
			{
				//Si el dia es anterior lo adelanto hasta el dia dd
				$d->setDay( $this->event_recur_every_dd_day );
			}
			else
			{
				//Si el dia es posterior hay que sumarle los meses
				if ( $d->getDay() > $this->event_recur_every_dd_day )
				{
					$d->addMonths( $this->event_recur_every_x_months );
					$d->setDay( $this->event_recur_every_dd_day );
				}
			}
			return $d;
		}
		else
		{

			//Ocurre el iº N de cada mes
			$aux = CDate::getIDayNOfMonth( $d->getYear(), $d->getMonth(), $this->event_recur_every_nd_day, $this->event_recur_every_n_day );

			if ( CDate::compare($aux, $d) <= 0 )
			{
				//Ya paso el dia, hay que probar el del proximo mes
				$d->addMonths($this->event_recur_every_x_months);
				$d = CDate::getIDayNOfMonth( $d->getYear(), $d->getMonth(), $this->event_recur_every_nd_day, $this->event_recur_every_n_day );
			}
			else
			{
				$d = $aux;
			}
		}

		return $d;
	}

/**
@author Mauro Chojrin
@return Fecha de la primera ocurrencia del evento anual
*/

	function getYearlyFirstOccurrence()
	{
		$d = new CDate($this->event_start_date);

		if ( $this->event_recur_every_dd_day > 0 )
		{
			//Ocurre los dd/mm de cada año
			if ( $d->getMonth() > $this->event_recur_every_mm_month || ( $d->getMonth() == $this->event_recur_every_mm_month && $d->getDay() > $this->event_recur_every_dd_day ) )
			{
				//Ya paso el de este año
				$d->addYears(1);
				$d->setMonth($this->event_recur_every_mm_month);
				$d->setDay(event_recur_every_dd_day);
			}
			else
			{
				if ( $d->getMonth() == $this->event_recur_every_mm_month && $d->getDay() <= $this->event_recur_every_dd_day )
				{
					//Mismo mes, día menor
					$d->setDay( $this->event_recur_every_dd_day );
				}
				else
				{
					//Mes <= y dia <=
					$d->setMonth( $this->event_recur_every_mm_month );
					$d->setDay( $this->event_recur_every_dd_day );
				}
			}
		}
		else
		{
			//Ocurre el iº N de cada M
			$aux = CDate::getIDayNOfMonth( $d->getYear(), $this->event_recur_every_mm_month, $this->event_recur_every_nd_day, $this->event_recur_every_n_day );
			if ( CDate::compare( $d, $aux ) > 0 )
			{
				//Ya paso el de este año
				$aux = CDate::getIDayNOfMonth( $d->getYear() + 1, $this->event_recur_every_mm_month, $this->event_recur_every_nd_day, $this->event_recur_every_n_day );
			}
			$d = $aux;
		}
		return $d;
	}

/**
@author Mauro Chojrin
@param $d Fecha a partir de la cual calcular la proxima ocurrencia
@return Fecha de la proxima ocurrencia del evento anual
*/

	function getYearlyNextOccurrence( $d )
	{
		if ( $this->event_recur_every_dd_day > 0 )
		{
			//Ocurre el dd/mm de cada año
			if ( $d->getMonth() > $this->event_recur_every_mm_month || ($d->getMonth() == $this->event_recur_every_mm_month && $d->getDay() > $this->event_recur_every_dd_day ) )
			{
				//Me pase del de este año, paso al prox
				$d->addYears(1);
			}
			//El mes es menor o igual y el dia tambien
			$d->setMonth($this->event_recur_every_mm_month);
			$d->setDay($this->event_recur_every_dd_day);
		}
		else
		{
			$aux = CDate::getIDayNOfMonth( $d->getYear(), $this->event_recur_every_mm_month, $this->event_recur_every_nd_day, $this->event_recur_every_n_day );
			if ( CDate::compare( $d, $aux ) > 0 )
			{
				//Ya paso el de este año
				$aux = CDate::getIDayNOfMonth( $d->getYear() + 1, $this->event_recur_every_mm_month, $this->event_recur_every_nd_day, $this->event_recur_every_n_day );
			}
			$d = $aux;
		}

		return $d;
	}

	function getFirstOccurrence()
	{
		//echo "<p>Calculando primera ocurrencia para evento '$er->event_recurse_type'</p>";

		switch ( $this->event_recurse_type )
		{
			case "d":
				return $this->getDailyFirstOccurrence();
				break;
			case "w":
				return $this->getWeeklyFirstOccurrence();
				break;
			case "m":
				return $this->getMonthlyFirstOccurrence();
				break;
			case "y":
				return $this->getYearlyFirstOccurrence();
				break;
			default:
				//echo "<p>First occurence: Tipo de evento '$this->event_recurse_type' desconocido para evento '$this->event_id'</p>";
				break;
		}
	}

	/**
	@param $er Evento Recursivo
	@param $d Fecha de ultima ocurrencia anterior
	@param $repets Numero de repeticiones que ya se ejecutaron, e/s
	*/

	function getNextOccurrence( $d )
	{
		//echo "<p>Calculando proxima ocurrencia para evento tipo '$er->event_recurse_type'</p>";

		//La unica a la que no hay que sumarle un dia es a la diaria
		//porque el patron de recursion suma X dias
		switch ( $this->event_recurse_type )
		{
			case "d":
				$d = $this->getDailyNextOccurrence( $d );
				break;
			case "w":
				//echo "<p>Buscando ocurrencia despues de ".$d->format( FMT_DATETIME_MYSQL )."</p>";
				$d = $this->getWeeklyNextOccurrence( $d );
				//echo "<p>Encontrado en ".$d->format( FMT_DATETIME_MYSQL )."</p>";
				break;
			case "m":
				$d->addDays(1);//Para calcular la proxima ocurrencia
				$d = $this->getMonthlyNextOccurrence( $d );
				break;
			case "y":
				$d->addDays(1);//Para calcular la proxima ocurrencia
				$d = $this->getYearlyNextOccurrence( $d );;
				break;
			default:
				echo "<p>Next occurence: Tipo de evento '$this->event_recurse_type' desconocido</p>";
				break;
		}
		if ( $this->event_end_occurrence && $this->event_end_occurrence != "0000-00-00" )
		{
			$fin = new CDate($this->event_end_occurrence);
			if ( CDate::compare( $d, $fin) > 0 )
			{
				return NULL;
			}
		}
		return $d;
	}

	function getIthOcurrence( $i )
	{
		//Calcula la $iº ocurrencia del evento y devuelve su fecha
		//Si $i > event_no_ocurrences => return NULL;
		if ( $this->event_no_occurrences && $i >= $this->event_no_occurrences )
		{
			return NULL;
		}
		$d = $this->getFirstOccurrence();
		for ($j = 0; $j < $i; $j++ )
		{
			$d = $this->getNextOccurrence($d);
		}
		$fin = new CDate($this->event_end_occurrence);
		if ( $this->event_end_occurrence && CDate::compare( $d, $fin ) > 0 )
		{
			return NULL;
		}
		return $d;
	}
/**
* Utility function to return an array of events with a period
* @param Date Start date of the period
* @param Date End date of the period
* @return array A list of events
*/
	function getEventsForPeriod( $start_date, $end_date , $user_id=0, $company_id=null, $project_id=null) {
		global $AppUI;

	//	echo ($debug ? "<pre>":"");
	// the event times are stored as unix time stamps, just to be different
		$uid = @$user_id ? $user_id : $AppUI->user_id;

		if($company_id == null)
		{
			$sql_cia = "SELECT user_company FROM users WHERE user_id ='".$uid."' ";
			$user_cia =  db_loadColumn( $sql_cia );
			$company_user = $user_cia[0];
		}
		else
			$company_user = $company_id;

	// convert to default db time stamp
		$db_start = $start_date->format( FMT_DATETIME_MYSQL );
		$db_end = $end_date->format( FMT_DATETIME_MYSQL );
	//	echo ($debug ? "\nUser: $uid - St: $db_start - Ed: $db_end \n":"");
				
		$p = new CProject();
		$projs = $p->getAllowedRecords( $uid );
		$proyectosPropios = "";
		//	if($debug){ var_dump($projs);}

		$ids = array_keys($projs);
		$proyectosPropios = implode(",",$ids);
		
		$proyectosPropios = $proyectosPropios == "" ? "''":$proyectosPropios ;
		
		/*
		for ( $i = 0; $i < count($ids); $i++ )
		{
			$proyectosPropios .= $ids[$i].",";
		}
		$proyectosPropios .= "0";*/

		$select = "SELECT DISTINCT e.event_id, e.event_start_date, e.event_end_date ";
		$from = "FROM events e ";
		$where = "WHERE  ( event_recurse_type IS NULL ) AND (( event_start_date BETWEEN  '$db_start' AND '$db_end' ) OR ( event_end_date BETWEEN '$db_start' AND '$db_end' ) OR ( '$db_start' BETWEEN event_start_date AND event_end_date ) ) ";
		
		if($company_id != null || $project_id != null)
		{
			$tasks_array = CTask::getTasksList("all", $uid, $company_id, $project_id, 0, "0", false);
			
			for($i=0;$i<count($tasks_array);$i++)
			{
				$tasks_in .= $tasks_array[$i]["task_id"].",";
			}
	
			if($tasks_in)
			{
				$tasks_in = substr($tasks_in, 0, (strlen($tasks_in)-1));
			}
			else
			{
				$tasks_in = -1;
			}
			
			$where .= " AND event_task IN (".$tasks_in.") ";
		}
				
		$eventosPropios = "AND ( event_owner = $uid )";
		$sqlPropios = $select.$from.$where.$eventosPropios;

		$eventosPropiaCompania = "AND ( event_company = '$company_user' AND e.event_invitation_type = 'COMPANY' )";
		$sqlCompania = $select.$from.$where.$eventosPropiaCompania;

		$eventosPropioProyecto = "AND ( event_project IN ($proyectosPropios) AND e.event_invitation_type = 'PROJECT' )";
		$sqlProyectos = $select.$from.$where.$eventosPropioProyecto;

		$joinInvitaciones = "INNER JOIN events_invitations ei ON ei.event_id = e.event_id ";
		$eventosInvitacionesAceptadas = "AND ( ei.user_id = $uid AND ei.invitation_status = 'ACCEPTED' )";
		$sqlInvitacionesAceptadas = $select.$from.$joinInvitaciones.$where.$eventosInvitacionesAceptadas;

	//Agregar las invitaciones
	// assemble query for non-recursive events

		$sql = "($sqlPropios)
		UNION ($sqlCompania)
		UNION ($sqlProyectos)
		UNION ($sqlInvitacionesAceptadas)";
		//echo "<p>Eventos comunes:</p><pre>$sql</pre>";
	//echo $sql;
	// execute
	//	if($debug){ var_dump($sql);}
		$eventList = db_loadList( $sql );
		//echo "<p>Eventos comunes:</p>";
		//print_r($eventList);
	//	if($debug){ var_dump($eventList);}

		//echo "<p>Hay ".count($eventList)." eventos</p>";

		//Hay que agregar el tema de las invitaciones
		$where = "WHERE ( event_recurse_type IS NOT NULL ) AND ( event_start_date <= '$db_end' ) AND ( event_end_occurrence >= '$db_start' OR event_end_occurrence IS NULL OR event_end_occurrence = '0000-00-00')";
		$sqlPropios = $select.$from.$where.$eventosPropios;
		$sqlCompania = $select.$from.$where.$eventosPropiaCompania;
		$sqlProyectos = $select.$from.$where.$eventosPropioProyecto;
		$sqlInvitacionesAceptadas = $select.$from.$joinInvitaciones.$where.$eventosInvitacionesAceptadas;

		$sql = "($sqlPropios)
		UNION ($sqlCompania)
		UNION ($sqlProyectos)
		UNION ($sqlInvitacionesAceptadas)";

        //$debug=true;
		//if($debug) echo "<p>Eventos recursivos</p><pre>$sql</pre>";

		// execute
		//if($debug){ var_dump($sql);}
        //echo $sql;
		$eventListRec = db_loadList( $sql );
		//if($debug){ var_dump($eventListRec);}
        /*
        if($debug){
            foreach($eventListRec as $uu){
                echo "<p>{$uu["event_id"]}</p>";
            }

        }
        */
	//	if($debug) echo "<p>Y son... ".count($eventListRec)." </p>";
		for ($i=0; $i < sizeof($eventListRec); $i++)
		{
			$ev = new CEvent;
			$ev->load( $eventListRec[$i]["event_id"] );

	//		if($debug) echo "<p>Evento '$ev->event_title'</p>";
    //      var_dump($ev->event_start_date);
            $d = $ev->getFirstOccurrence();

	//		if($debug) echo "<p>Primera ocurrencia del evento = '".$d->format( FMT_DATETIME_MYSQL )."'</p>";
			$repets = 0;

			//Salteo toda ocurrencia vieja
	//		if($debug) echo "\nSalteo toda ocurrencia vieja:";
			while ($repets < 100000 && $d && CDate::compare($d, $start_date) < 0 && ( $ev->event_no_occurrences == "-1" || $repets < $ev->event_no_occurrences ) )
			{
				$repets++;
				$d = $ev->getNextOccurrence( $d );
				//if($debug) echo "\n".$d->format( FMT_DATETIME_MYSQL );
			}
			if ($repets > 100000){
				die("Error: Se produjo un error al buscar la recurrencia del evento. Por favor notifique al administrador. Gracias");
			}
	//		if($debug) echo "<p>Fin del ciclo de cálculo de ocurrencias</p>";
	//		if($debug) echo "<p>Fecha luego de $repets ocurrencias '".$d->format( FMT_DATETIME_MYSQL )."'</p>";
			$indEv = count($eventList);
            //echo $indEv;

	//		if($debug) echo "<p>Evento con '$ev->event_no_occurrences' ocurrencias permitidas</p>";
			while ( $d && CDate::compare($d, $end_date) <= 0 && ( $ev->event_no_occurrences == "-1" || $repets < $ev->event_no_occurrences ) )
			{
				//if($debug) echo "<p>Agregando ocurrencia en '".$d->format( FMT_DATETIME_MYSQL )." $ev->event_title</p>";
				$eventList[$indEv]["event_id"] = $ev->event_id;
				$eventList[$indEv]["event_start_date"] = $d->format( FMT_DATETIME_MYSQL );
                $eventList[$indEv]["event_end_date"] = NULL;
                //calcular diferencia de dias
                if($ev->event_end_date != NULL){
                    $dEnd =  new CDate($ev->event_end_date);
                    $intDiffDay = $dEnd->dateDiff($d);

                    switch ( $ev->event_recurse_type )
                    {
                        case "d":
                            $intDiffDay > 1 ? $intDiffDay = $ev->event_recur_every_x_days : $intDiffDay--;
                            $dEnd->addDays($intDiffDay);
                            break;
                        case "w":
                            $dEnd->addDays($intDiffDay+1);
                            break;
                        case "m":
                            $dEnd->addDays($intDiffDay+1);
                            break;
                        case "y":
                            $dEnd->addDays($intDiffDay+1);
                            break;
                        default:

                            break;
                    }
                    //echo $d->format( FMT_DATETIME_MYSQL ) . " " . $dEnd->format( FMT_DATETIME_MYSQL );
                    //echo $dEnd->format( FMT_DATETIME_MYSQL ). " ";
                    $eventList[$indEv]["event_end_date"] = $dEnd->format( FMT_DATETIME_MYSQL );
                }

                $indEv++;
				$repets++;
                //var_dump($d);
				$d = $ev->getNextOccurrence( $d );
                //echo $d->format( FMT_DATETIME_MYSQL )." ";
				//Mientras siga sin dar NULL sigo pidiendo mas ocurrencias del evento.
			}

		}
	//	echo ($debug ? "</pre>":"");
		//return a list of non-recurrent and recurrent events
        return $eventList;
	}
	
	function getEventsByPipeline($pipeline)
	{
		$sql = "SELECT event_id FROM events WHERE event_salepipeline = ".$pipeline;
		
		$eventsPipeline = db_loadList( $sql );
		
		for ($i=0; $i < sizeof($eventsPipeline); $i++)
		{
			$ev = new CEvent;
			$ev->load( $eventsPipeline[$i]["event_id"] );
			
			$arrEventsPipeline[$eventsPipeline[$i]["event_id"]] = $ev;
		}
		
		return $arrEventsPipeline;
	}

	function getEventsForPeriodByTasks( $start_date, $end_date, $tasks){

		$db_start = $start_date->format( FMT_DATETIME_MYSQL );
		$db_end = $end_date->format( FMT_DATETIME_MYSQL );
	
		$select = "SELECT DISTINCT e.event_id, e.event_start_date, e.event_end_date ";
		$from = "FROM events e ";
		$where = "WHERE  ( event_recurse_type IS NULL ) AND (( event_start_date >= '$db_start' AND event_start_date <= '$db_end' ) OR ( event_end_date >= '$db_start' AND event_end_date <= '$db_end' ) OR ( '$db_start' >= event_start_date AND '$db_start' <= event_end_date ) ) ";
		if($tasks)
		$where .= "AND event_task IN (".$tasks.") ";
		
		$eventList = db_loadList( $select.$from.$where );
		
		$where = "WHERE ( event_recurse_type IS NOT NULL ) AND ( event_start_date <= '$db_end' ) AND ( event_end_occurrence >= '$db_start' OR event_end_occurrence IS NULL OR event_end_occurrence = '0000-00-00')";
		if($tasks)
		$where .= " AND event_task IN (".$tasks.") ";
		
		$eventListRec = db_loadList( $select.$from.$where );

		for ($i=0; $i < sizeof($eventListRec); $i++)
		{
			$ev = new CEvent;
			$ev->load( $eventListRec[$i]["event_id"] );

            $d = $ev->getFirstOccurrence();

			$repets = 0;

			while ($repets < 100000 && $d && CDate::compare($d, $start_date) < 0 && ( $ev->event_no_occurrences == "-1" || $repets < $ev->event_no_occurrences ) )
			{
				$repets++;
				$d = $ev->getNextOccurrence( $d );

			}
			if ($repets > 100000){
				die("Error: Se produjo un error al buscar la recurrencia del evento. Por favor notifique al administrador. Gracias");
			}
			$indEv = count($eventList);

			while ( $d && CDate::compare($d, $end_date) <= 0 && ( $ev->event_no_occurrences == "-1" || $repets < $ev->event_no_occurrences ) )
			{
				$eventList[$indEv]["event_id"] = $ev->event_id;
				$eventList[$indEv]["event_start_date"] = $d->format( FMT_DATETIME_MYSQL );
                $eventList[$indEv]["event_end_date"] = NULL;
                
                if($ev->event_end_date != NULL){
                    $dEnd =  new CDate($ev->event_end_date);
                    $intDiffDay = $dEnd->dateDiff($d);

                    switch ( $ev->event_recurse_type )
                    {
                        case "d":
                            $intDiffDay > 1 ? $intDiffDay = $ev->event_recur_every_x_days : $intDiffDay--;
                            $dEnd->addDays($intDiffDay);
                            break;
                        case "w":
                            $dEnd->addDays($intDiffDay+1);
                            break;
                        case "m":
                            $dEnd->addDays($intDiffDay+1);
                            break;
                        case "y":
                            $dEnd->addDays($intDiffDay+1);
                            break;
                        default:

                            break;
                    }
                    $eventList[$indEv]["event_end_date"] = $dEnd->format( FMT_DATETIME_MYSQL );
                }

                $indEv++;
				$repets++;

				$d = $ev->getNextOccurrence( $d );
			}

		}
        return $eventList;
	}
	
	function getEventsForPeriodByContact( $start_date, $end_date, $contact=null){

		$db_start = $start_date->format( FMT_DATETIME_MYSQL );
		$db_end = $end_date->format( FMT_DATETIME_MYSQL );
		
		$sql = "SELECT event_id FROM events_invitations WHERE contact_id = $contact";
		$ids = mysql_query($sql);
		
		$vec = "";
		while($data = mysql_fetch_array($ids)){
			$vec .= $data["event_id"].", ";
		}
		
		$vec = substr($vec,0,strlen($vec)-2);
		
		if(!empty($vec)){
			$select = "SELECT DISTINCT e.event_id, e.event_start_date, e.event_end_date ";
			$from = " FROM events e ";
			$where = " WHERE  ( event_recurse_type IS NULL ) AND (( event_start_date >= '$db_start' AND event_start_date <= '$db_end' ) OR ( event_end_date >= '$db_start' AND event_end_date <= '$db_end' ) OR ( '$db_start' >= event_start_date AND '$db_start' <= event_end_date ) ) ";
			if($contact)
				$where .= " AND event_id IN (".$vec.") ";
			
			$eventList = db_loadList( $select.$from.$where );
					
			$where = "WHERE ( event_recurse_type IS NOT NULL ) AND ( event_start_date <= '$db_end' ) AND ( event_end_occurrence >= '$db_start' OR event_end_occurrence IS NULL OR event_end_occurrence = '0000-00-00')";
			if($contact)
				$where .= " AND event_id IN (".$vec.") ";
			
			$eventListRec = db_loadList( $select.$from.$where );
		}
		
		for ($i=0; $i < sizeof($eventListRec); $i++)
		{
			$ev = new CEvent;
			$ev->load( $eventListRec[$i]["event_id"] );

            $d = $ev->getFirstOccurrence();

			$repets = 0;

			while ($repets < 100000 && $d && CDate::compare($d, $start_date) < 0 && ( $ev->event_no_occurrences == "-1" || $repets < $ev->event_no_occurrences ) )
			{
				$repets++;
				$d = $ev->getNextOccurrence( $d );

			}
			if ($repets > 100000){
				die("Error: Se produjo un error al buscar la recurrencia del evento. Por favor notifique al administrador. Gracias");
			}
			$indEv = count($eventList);

			while ( $d && CDate::compare($d, $end_date) <= 0 && ( $ev->event_no_occurrences == "-1" || $repets < $ev->event_no_occurrences ) )
			{
				$eventList[$indEv]["event_id"] = $ev->event_id;
				$eventList[$indEv]["event_start_date"] = $d->format( FMT_DATETIME_MYSQL );
                $eventList[$indEv]["event_end_date"] = NULL;
                
                if($ev->event_end_date != NULL){
                    $dEnd =  new CDate($ev->event_end_date);
                    $intDiffDay = $dEnd->dateDiff($d);

                    switch ( $ev->event_recurse_type )
                    {
                        case "d":
                            $intDiffDay > 1 ? $intDiffDay = $ev->event_recur_every_x_days : $intDiffDay--;
                            $dEnd->addDays($intDiffDay);
                            break;
                        case "w":
                            $dEnd->addDays($intDiffDay+1);
                            break;
                        case "m":
                            $dEnd->addDays($intDiffDay+1);
                            break;
                        case "y":
                            $dEnd->addDays($intDiffDay+1);
                            break;
                        default:

                            break;
                    }
                    $eventList[$indEv]["event_end_date"] = $dEnd->format( FMT_DATETIME_MYSQL );
                }

                $indEv++;
				$repets++;

				$d = $ev->getNextOccurrence( $d );
			}

		}
        return $eventList;
	}
	
}

class CEventInvitation extends CDpObject
{
	var $invitation_id = NULL;
	var $event_id = NULL;
	var $user_id = NULL;
    var $contact_id = NULL;
	var $invitation_mail = NULL;
	var $invitation_status = NULL;
	var $invitation_hash = NULL;
	var $invitation_sent = NULL;
	var $_hash_length = 32;

	function CEventInvitation()
	{
		$this->CDpObject( 'events_invitations', 'invitation_id' );
	}

	function check()
	{
		if ( !$this->event_id )
		{
			return "event_id is NULL";
		}
		if ( (!$this->user_id && !$this->contact_id) && !$this->invitation_mail )
		{
			return "user_id or contact_id, and invitation_mail are NULL";
		}
		return NULL;
	}

	function loadByHash( $hash )
	{
		$sql = "SELECT * FROM events_invitations WHERE invitation_hash = '$hash'";

		return db_loadObject( $sql, $this, false, true );
	}

	function store()
	{
		if ( $msg = CDpObject::store() )
		{
			return $msg;
		}
		$this->invitation_hash = randomString( "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTWXYZ", $this->_hash_length, strval( $this->invitation_id ) );
		return CDpObject::store();
	}
}
?>
