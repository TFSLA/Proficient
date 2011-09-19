<?php /* CLASSES $Id: date.class.php,v 1.1 2009-05-19 21:15:27 pkerestezachi Exp $ */
/**
* @package dotproject
* @subpackage utilites
*/

function mktime_fix()
{
	global $dPconfig;
	
	$arguments = func_get_args();
	$str_arguments = implode('", "', $arguments);
	$ts = -1;
	
	eval('$ts = mktime( "'.$str_arguments.'");');
	
	if ($ts <= 0) return $ts;
	$diff = $dPconfig['mktime_difference'];
	if (isset($diff) && $diff !== null){
		$ts += $diff;
	}
	return $ts;
}



require_once( $AppUI->getLibraryClass( 'PEAR/Date' ) );

define( 'FMT_DATEISO', '%Y%m%dT%H%M%S' );
define( 'FMT_DATELDAP', '%Y%m%d%H%M%SZ' );
define( 'FMT_DATETIME_MYSQL', '%Y-%m-%d %H:%M:%S' );
define( 'FMT_DATERFC822', '%a, %d %b %Y %H:%M:%S' );
define( 'FMT_TIMESTAMP', '%Y%m%d%H%M%S' );
define( 'FMT_TIMESTAMP_DATE', '%Y%m%d' );
define( 'FMT_TIMESTAMP_TIME', '%H%M%S' );
define( 'FMT_UNIX', '3' );
define( 'WDAY_SUNDAY',    0 );
define( 'WDAY_MONDAY',    1 );
define( 'WDAY_TUESDAY',   2 );
define( 'WDAY_WENESDAY',  3 );
define( 'WDAY_THURSDAY',  4 );
define( 'WDAY_FRIDAY',    5 );
define( 'WDAY_SATURDAY',  6 );
define( 'SEC_MINUTE',    60 );
define( 'SEC_HOUR',    3600 );
define( 'SEC_DAY',    86400 );

/**
* dotProject implementation of the Pear Date class
*
* This provides customised extensions to the Date class to leave the
* Date package as 'pure' as possible
*/
class CDate extends Date {

/**
* Overloaded compare method
*
* The convertTZ calls are time intensive calls.  When a compare call is
* made in a recussive loop the lag can be significant.
*/
    function compare($d1, $d2, $convertTZ=false)
    {
		if ($convertTZ) {
			$d1->convertTZ(new Date_TimeZone('UTC'));
			$d2->convertTZ(new Date_TimeZone('UTC'));
		}
        $days1 = Date_Calc::dateToDays($d1->day, $d1->month, $d1->year);
        $days2 = Date_Calc::dateToDays($d2->day, $d2->month, $d2->year);
        if($days1 < $days2) return -1;
        if($days1 > $days2) return 1;
        if($d1->hour < $d2->hour) return -1;
        if($d1->hour > $d2->hour) return 1;
        if($d1->minute < $d2->minute) return -1;
        if($d1->minute > $d2->minute) return 1;
        if($d1->second < $d2->second) return -1;
        if($d1->second > $d2->second) return 1;
        return 0;
    }


/**
* Adds (+/-) a number of days to the current date.
* @param int Positive or negative number of days
* @author J. Christopher Pereira <kripper@users.sf.net>
*/
	function addDays( $n ) {
		$this->setDate( $this->getTime() + 60 * 60 * 24 * $n, DATE_FORMAT_UNIXTIME);
	}
	
/**
* Adds (+/-) a number of months to the current date.
* @param int Positive or negative number of months
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
*/
	function addMonths( $n ) {
		$an = abs( $n );
		$years = floor( $an / 12 );
		$months = $an % 12;

		if ($n < 0) {
			$this->year -= $years;
			$this->month -= $months;
			if ($this->month < 1) {
				$this->year--;
				$this->month = 12 - $this->month;
			}
		} else {
			$this->year += $years;
			$this->month += $months;
			if ($this->month > 12) {
				$this->year++;
				$this->month -= 12;
			}
		}
	}	

/**
@author Mauro Chojrin
@param $n Numero de semanas a sumar
*/

	function addWeeks( $n )
	{
		$this->addDays(7 * $n);
	}
/**
@author Mauro Chojrin
@param $n numero de años para sumar
*/
	function addYears( $n )
	{
		$this->addMonths( $n * 12 );
	}
	
/**
@param $a año
@param $m mes
@param $i Numero de orden (primero, segundo, etc... ) de dia deseado
@param $n Numero de dia de la semana deseado
*/
	function getIDayNOfMonth( $a, $m, $i, $n )
	{
		$aux_d = new CDate();
		$aux_d->setDay(1);
		$aux_d->setMonth($m);
		$aux_d->setYear($a);
		$aux_d->setTime();
		//echo "<p>Buscando el ".$i."º dia $n del mes ".$aux_d->getMonth()."</p>";
		//Me voy al primer dia del mes
		while ( $aux_d->getDayOfWeek() != $n )
		{
			//Busco el primer N del mes
			$aux_d->addDays(1);
		}
		//echo "<p>El 1º dia $n del mes ".$aux_d->getMonth()." es el '".$aux_d->format(FMT_DATETIME_MYSQL)."'</p>";
		//Busco el Iº dia N del mes
		for ( $j = 1; $j < $i; $j++ )
		{
			$aux_d->addDays(7);
		}
		//echo "<p>El ".$i."º dia $n del mes ".$aux_d->getMonth()." es el '".$aux_d->format(FMT_DATETIME_MYSQL)."'</p>";
		return $aux_d;
	}
/**
* New method to get the difference in days the stored date
* @param Date The date to compare to
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
*/
	function dateDiff( $when ) {
		return Date_calc::dateDiff(
			$this->getDay(), $this->getMonth(), $this->getYear(),
			$when->getDay(), $when->getMonth(), $when->getYear()
		);
	}

/**
* New method that sets hour, minute and second in a single call
* @param int hour
* @param int minute
* @param int second
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
*/
	function setTime( $h=0, $m=0, $s=0 ) {
		$this->setHour( $h );
		$this->setMinute( $m );
		$this->setSecond( $s );
	}
	
	function isWorkingDay(){
		global $AppUI;
		
		$working_days = $AppUI->getConfig("cal_working_days");
		if(is_null($working_days)){
			$working_days = array('1','2','3','4','5');
		} else {
			$working_days = explode(",", $working_days);
		}
		
		return in_array($this->getDayOfWeek(), $working_days);
	}
	
	function getAMPM() {
		if ( $this->getHour() > 11 ) {
			return "pm";
		} else {
			return "am";
		}
	}
	
    /**
     * Get the Date object for the last day in the current month
     *
     * The time of the returned Date object is the same as this time.
     *
     * @access public
     * @return object Date Date representing the last date of the current month
     * @author Rodrigo Fuentes
     */

    function getLastDayOfMonth()
    {
        $day = Date_Calc::daysInMonth($this->month, $this->year);
        $date = sprintf("%s %02d:%02d:%02d", $this->year."-".$this->month."-".$day, $this->hour, $this->minute, $this->second);
        $newDate = new Date();
        $newDate->setDate($date);
        return $newDate;
    }

    function buildManualDateValidationJS(){
        global $AppUI;
        $tmpHtml = "";
        $tmpHtml .= "/*funciones para el validar fecha*/";
        $tmpHtml .= "function GetFormatDate(strDateFormat){
                            var strValue = false;
                            strSeparadores = new Array();

                            strSeparadores[0]=\"/\";
                            strSeparadores[1]=\".\";

                            if(strDateFormat == \"\") return strValue;

                            for(i=0; i < strSeparadores.length; i++){
                                if(strDateFormat.indexOf(strSeparadores[i]) > -1){
                                    arStrDate = strDateFormat.split(strSeparadores[i]);
                                    if(arStrDate.length > 2) continue;
                                }
                            }

                            if(arStrDate != false){
                                if(arStrDate[0].indexOf(\"d\") > -1 && arStrDate[1].indexOf(\"m\") > -1){
                                    strValue = \"ddmmyyyy\";
                                }else if(arStrDate[0].indexOf(\"m\") > -1 && arStrDate[1].indexOf(\"d\") > -1){
                                    strValue = \"mmddyyyy\";
                                }
                            }

                            return strValue;
                        }";
        $tmpHtml .= "function validateDate(arDate){
        					var rta = false;
                            arMonth = new Array();
                            arMonth[1]=\"31\";
                            arMonth[2]=\"28\";
                            arMonth[3]=\"31\";
                            arMonth[4]=\"30\";
                            arMonth[5]=\"31\";
                            arMonth[6]=\"30\";
                            arMonth[7]=\"31\";
                            arMonth[8]=\"31\";
                            arMonth[9]=\"30\";
                            arMonth[10]=\"31\";
                            arMonth[11]=\"30\";
                            arMonth[12]=\"31\";

                            if(arDate != false){
                                intDay = arDate[0];
                                intMonth = arDate[1];
                                intYear = arDate[2];
                            }else{
                                return rta;
                            }

                            intDay = parseInt(parseFloat(intDay));
                            intMonth = parseInt(parseFloat(intMonth));
                            strYear = intYear;
                            intYear = parseInt(parseFloat(intYear));
                            rta = true;

                            if(intDay < 1 || intDay > 31) rta = false;
                            if(intMonth < 1 || intMonth > 12) rta = false;
                            if(strYear.length != 4) rta = false;

                            if(intYear % 4 == 0) arMonth[2] = \"29\";

                            if(parseInt(arMonth[intMonth]) < intDay) rta=false;

                            return rta;
                        }
                        /*
                        funcion que busca el formato de la fecha
                        param strDate:fecha (separador / .)
                        param strFormat: formato usado en php ej: %m/%d/%y
                        */
                        function setDateToFormat(strDate, strFormat){
                            arDate = new Array();
        					bstrDateOk = false;//si la fecha tiene 3 partes
                            strSeparadores = new Array();
                            strSeparadores[0]=\"/\";
                            strSeparadores[1]=\".\";

                            for(i=0; i < strSeparadores.length; i++){
                                if(strDate.indexOf(strSeparadores[i]) > -1){
                                    arStrDate = strDate.split(strSeparadores[i]);
                                    if(arStrDate.length > 2){ 
        								bstrDateOk = true;
        								continue;
                                    }
                                }
                            }
        				
        					if(!bstrDateOk) return bstrDateOk;//salgo si la fecha esta mal ingresada
        
                            switch(strFormat){
                                case \"ddmmyyyy\":
                                    arDate[0] = arStrDate[0];
                                    arDate[1] = arStrDate[1];
                                    arDate[2] = arStrDate[2];
                                    break;
                                case \"mmddyyyy\":
                                    arDate[0] = arStrDate[1];
                                    arDate[1] = arStrDate[0];
                                    arDate[2] = arStrDate[2];
                                    break;
                                case false:
                                    return false;
                                    break;
                            }
                            return arDate;
                        }";
        $tmpHtml .= "  /*
                        param objFrmDate: elem del form que contine la fecha
                        param objFrmFormatDate: elem del form que contiene el formato usado ej:%d/%m/%y
                        param objFrmfDate: elem del form que contiene la fecha yyyymmdd
                        */
                        function setManualDate(objFrmDate, objFrmFormatDate, objFrmfDate){
                            var bOk = true;
                            strDate = objFrmDate.value;
                            strFormatDate = objFrmFormatDate.value;

                            arDate = setDateToFormat(strDate,GetFormatDate(strFormatDate));
                            if(arDate === false) bOk = false;
                            if(validateDate(arDate) && bOk){
                                if(arDate[0].length < 2) arDate[0] = \"0\" + arDate[0];
                                if(arDate[1].length < 2) arDate[1] = \"0\" + arDate[1];
                                objFrmfDate.value = arDate[2]+arDate[1]+arDate[0];
                                bOk = true;
                            }else{
                                bOk = false;
                            }
                            return bOk;
                        }
                        /*fin funciones para validar fecha*/";
        return $tmpHtml;
    }

    function buildFunctionMDVJS($strOperator="==", $strCompareValue="false"){
        $tmpHtml = "";
        $tmpHtml .= "setManualDate(strMDVparam1, strMDVparam2, strMDVparam3)";
        $tmpHtml .= " $strOperator ";
        $tmpHtml .= " $strCompareValue ";
        return $tmpHtml;
    }
    /*
    function setVariableMDVJS($strVarName, $strOperador, $strValue){
        $tmpHtml = "";
        $tmpHtml .= $strName . " " . $strOperador . " " . $strValue;
        return $tmpHtml;
    }
    */
    
    function trae_inicio1(){
        
		$sql_h = mysql_query("select calendar_day_from_time1 from calendar_days where calendar_id='1' ");
		$user_h = mysql_fetch_array($sql_h);
        
		if ($user_h!="")
		{
		$user_inic1 = substr($user_h[0],11,2);
		}
		else
		{
		$user_inic1 = -1;
		}

        return $user_inic1;
    }
    
	function trae_final1(){
        
		$sql_h = mysql_query("select calendar_day_to_time1 from calendar_days where calendar_id='1' ");
		$user_h = mysql_fetch_array($sql_h);
        
		if ($user_h!="")
		{
		$user_to1 = substr($user_h[0],11,2);
		}
		else
		{
		$user_to1 = -1;
		}


        return $user_to1;
    }
    
	function trae_inicio2(){
        
		$sql_h = mysql_query("select calendar_day_from_time2 from calendar_days where calendar_id='1' ");
		$user_h = mysql_fetch_array($sql_h);
        
		if ($user_h!="")
		{
		$user_inic2 = substr($user_h[0],11,2);
		}
		else
		{
		$user_inic2 = -1;
		}

        return $user_inic2;
    }
    
	function trae_final2(){
        
		$sql_h = mysql_query("select calendar_day_to_time2 from calendar_days where calendar_id='1' ");
		$user_h = mysql_fetch_array($sql_h);
        
		 
		if ($user_h!="")
		{
		$user_to2 = substr($user_h[0],11,2);
		}
		else
		{
		$user_to2 = -1;
		}

        return $user_to2;
    }
	
	function absoluteDays(){
		if (is_a($this, "CDate")){
			$dias = 0;
			/* cantidad de años bisiestos transcurridos hasta la fecha*/
			$bisiestos = intval($this->getYear() / 4);
			if ( $bisiestos == ($this->getYear() / 4)){
				$bisiestos--;
			}

			/* Dias hasta el ultimo bisiesto
			*/
			if ($bisiestos >= 0)
				$dias += $bisiestos * (366 + 365 * 3);
			else 
				$bisiestos = 0;
				
			/* 	dias adicionales por años de 365 dias completos transcurridos 
			*	desde el ultimo bisiesto hasta la fecha
			*/
			$anios_completos = ($this->getYear()  - ($bisiestos * 4));
			$dias += $anios_completos > 0 ? ($anios_completos * 365)+1 : 0 ;
			
			/*	Dias transcurridos en el año de la fecha
			*/
			/* cuando el año actual es bisiesto */
			$anio_base =1970;
			if(($this->getYear() % 4)==0){
				$anio_base =1972;
			}
			$dias += Date_Calc::dateDiff($this->getDay(), $this->getMonth(),$anio_base, 1,1, $anio_base);
			
			return $dias;
		}else{
			return false;
		}
	}
}


?>