<?php /* CALENDAR $Id: calendar.class.php,*/

$calendar_types = array(
"0"=>array(	"Label" => "System Calendar",
			"name" => NULL,
			"field" => NULL,
			"table" => NULL,
			"field_id" => NULL,
			"method" => "getSystemCalendars"				
			),
"1"=>array(	"Label" => "Company",
			"name" => "companies.company_name",
			"field" => "calendar_company",
			"table" => "companies",
			"field_link" => "company_id",
			"field_id" => "company_id",
			"method" => "getCompanyCalendars"		
			),
"2"=>array(	"Label" => "Project",
			"name" => "projects.project_name",
			"field" => "calendar_project",
			"table" => "projects",
			"field_link" => "project_company",
			"field_id" => "project_id",
			"method" => "getProjectCalendars"		
			),
"3"=>array(	"Label" => "User",
			"name" => "concat(user_last_name,', ',user_first_name)",
			"field" => "calendar_user",
			"table" => "users",
			"field_link" => "user_company",
			"field_id" => "user_id"	,
			"method" => "getUserCalendars"		
			));

$calendar_status_list = Array(
"0" => "Inactive",
"1" => "Active"
);			
			
//dias laborables por defecto
$default_working_days = array(2,3,4,5,6);

##
## Calendar classes
##
class CCalendar extends CDpObject
{
	var $calendar_id 		= NULL;
	var $calendar_name 		= NULL;
	var $calendar_company	= NULL;
	var $calendar_project	= NULL;
	var $calendar_user		= NULL;
	var $calendar_from_date	= NULL;
	var $calendar_propagate	= NULL;
	var $calendar_status	= NULL;
	var $_calendar_days	= NULL;
	
	function CCalendar()
	{
		$this->CDpObject( "calendar", "calendar_id" );
	}
	
	function check() {
		if ($this->calendar_id === NULL) {
			return 'calendar id is NULL';
		}
		$this->calendar_id = intval( $this->calendar_id );

		return NULL; // object is ok
	}
	function canDelete(){
		if ($msg = $this->check){
			return $msg;
		}
				
		return !(($this->calendar_project == 0 && 
				$this->calendar_company == 0 && 
				$this->calendar_user == 0 && 
				$this->calendar_from_date == "0000-00-00 00:00:00") || 
				($this->calendar_project == 0 && 
				$this->calendar_company != 0 && 
				$this->calendar_user == 0 && 
				$this->calendar_from_date == "0000-00-00 00:00:00"));
	}
	
	function delete() {
		global $AppUI;
		
		if ($msg = $this->check){
			return $msg;
		}	
				
		$sql = "DELETE FROM calendar_days WHERE calendar_id = $this->calendar_id";
		if (!db_exec( $sql )) {return db_error();}
		$sql = "DELETE FROM calendar WHERE calendar_id = $this->calendar_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
	
	function loadCalendarDays(){
		if ($msg = $this->check){
			return $msg;
		}		
		$sql = "select calendar_day_id, calendar_day_day
				from calendar_days
				where calendar_id = '$this->calendar_id'";
		$days = db_loadList($sql);
		
		for ($i = 0; $i < count($days) ; $i++){
			$day_id = $days[$i]["calendar_day_id"];
			$day = $days[$i]["calendar_day_day"];
			/*
			$this->_hollidays[$holliday] = new CHolliday();
			$this->_hollidays[$holliday]->load($holliday_id);		*/	
			$this->_calendar_days[$day] = new CCalendarDay();
			$this->_calendar_days[$day]->load($day_id);
			//$this->_calendar_days[$day]->calculateWorkingHours();
			
		}
	
	}
	function getSystemCalendars($params=NULL){
		$params["calendar_company"]="0";
		$params["calendar_project"]="0";	
		$params["calendar_user"]="0";	
		return CCalendar::getCalendars($params);		
	}		
	function getCompanyCalendars($company_id,$params=NULL){
		$params["calendar_company"]=$company_id;	
		return CCalendar::getCalendars($params);
	}
	function getProjectCalendars($project_id,$params=NULL){
		$params["calendar_project"]=$project_id;	
		return CCalendar::getCalendars($params);
	}	
	function getUserCalendars($user_id,$params=NULL){
		$params["calendar_user"]=$user_id;	
		return CCalendar::getCalendars($params);
	}	
	
	function getCalendars($params){
		$sql = "
		SELECT calendar.*
		from calendar
		where 1=1 ";
		if(count($params)){
			foreach($params as $field => $value){
				if ($value!="")
					$sql .= "\n\t and $field in ($value)";
				else 
					$sql .= "\n\t and $field <> $field";
			}
		}
		return db_loadList($sql);	
	}

	function getActiveCalendars($type, $id, $project=""){
		global $calendar_types;

		$earliest_calendar = "29991231";
		$list = array();

		$t = $type;
		while ( $t >= 0 && $earliest_calendar != "00000000" ){
			$cal_config = $calendar_types[$t];

			$params=array();
			$params["calendar_status"]="1";	
			if ($t > 0 ){
				if ($type==3 && $t==2)
					$id = $project;
				//el id de este tipo	
				$params[$cal_config["field"]]=$id;
				
				//obtengo el id del siguiente tipo
				if ($t > 1){
					$sql = "
					SELECT {$cal_config["field_link"]}
					from {$cal_config["table"]}
					where {$cal_config["field_id"]} = '{$id}' ";
					$id = db_loadResult($sql);	
				}
			}else{
				for($i=1; $i <= 3; $i++){
					$params[$calendar_types[$i]["field"]]="0";
				}
			}
				
			$calendars = CCalendar::getCalendars($params);	
			//$calendars = ArraySort($calendars, 'calendar_from_date', SORT_ASC);
					
			if	($calendars = ArraySort($calendars, 'calendar_from_date', SORT_ASC))
				for($i=0; $i < count($calendars); $i++){
					$idl = new CDate($calendars[$i]["calendar_from_date"]);
					$idl = $idl->format(FMT_TIMESTAMP_DATE);
					if ($idl < $earliest_calendar){
						$earliest_calendar = $idl;
						$list[$idl] = new CCalendar();
						if ($list[$idl]->load($calendars[$i]["calendar_id"]));
							$list[$idl]->loadCalendarDays();				
					}
	
				}			
					
			$t--;
		
		}

		return $list;
	}
	
}


class CCalendarDay extends CDpObject
{
	var $calendar_day_id 		= NULL;
	var $calendar_id			= NULL;
	var $calendar_day_day		= NULL;
	var $calendar_day_working	= NULL;
	var $calendar_day_from_time1= NULL;
	var $calendar_day_to_time1	= NULL;
	var $calendar_day_from_time2= NULL;
	var $calendar_day_to_time2	= NULL;
	var $calendar_day_from_time3= NULL;
	var $calendar_day_to_time3	= NULL;
	var $calendar_day_from_time4= NULL;
	var $calendar_day_to_time4	= NULL;
	var $calendar_day_from_time5= NULL;
	var $calendar_day_to_time5	= NULL;
	var $calendar_day_hours		= NULL;
	
	function CCalendarDay()
	{
		$this->CDpObject( "calendar_days", "calendar_day_id" );
	}
	
	function check() {
		if ($this->calendar_day_id === NULL) {
			return 'calendar day id is NULL';
		}
		$this->calendar_day_id = intval( $this->calendar_day_id );

		return NULL; // object is ok
	}
	
	function calculateWorkingHours(){
		if ($msg = $this->check){
			return $msg;
		}	
		$this->calendar_day_hours = 0;
		if ($this->calendar_day_working != 0){
			
			$i=1;
			while ($i <= 5){
				$from = "calendar_day_from_time$i";
				$to = "calendar_day_to_time$i";
				$fromtime = $this->$from;
				$totime = $this->$to;
				if ( $fromtime !== NULL && $totime!== NULL){
					$fromtime = new CDate($fromtime);
					$totime = new CDate($totime);
									
					$ts_from = mktime($fromtime->getHour(),$fromtime->getMinute(),$fromtime->getSecond(),1,1,1970);
					$ts_to =  mktime($totime->getHour(),$totime->getMinute(),$totime->getSecond(),1,1,1970);
					
					// hacer el calculo de las horas laborables
					$this->calendar_day_hours += ($ts_to - $ts_from) / 3600;
				}
				$i++;
			}
		}	
	}

/**
 *	Inserts a new row if id is zero or updates an existing row in the database table
 *
 *	Can be overloaded/supplemented by the child class
 *	@return null|string null if successful otherwise returns and error message
 */
	function store( $updateNulls = false ) {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed<br />$msg";
		}
		$k = $this->_tbl_key;
		$this->calculateWorkingHours();
		if( $this->$k ) {
			$ret = db_updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
		} else {
			$ret = db_insertObject( $this->_tbl, $this, $this->_tbl_key );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}
	
}



class CWorkCalendar extends CDate {
	var $_work_calendar = NULL;
	var $_hollidays = NULL;

    /**
     * Loads the work calendars and hollidays
     *
     * type : (0 - system; 1 - Company; 2 - Project; 3 - User)
     *
     * @access public
     * @return object Date Date representing the last date of the current month
	 * @param int type
	 * @param int id
	 * @param int project     
     * @author Rodrigo Fuentes
     */    
    function CWorkCalendar($type, $id="", $project="", $date=""){
    	
    	$this->CDate($date);
    	
    	if ($this){

    		$calendars = CCalendar::getActiveCalendars($type, $id, $project);
    		$cal_dates = array_keys($calendars);
    		sort($cal_dates);
    		for ($i = 0; $i < count($cal_dates); $i++) {
    			$this->_work_calendar[$cal_dates[$i]] = $calendars[$cal_dates[$i]];
    		}

    		
    		switch($type){
    		case "0":
    			$this->_hollidays = CHolliday::getHollidays();
    			break;
    		case "1":
    			$this->_hollidays = CCompany::getHollidays($id);
    			break;
    		case "2":
    			$this->_hollidays = CProject::getHollidays($id);
    			break;
    		case "3":
    			if ($project!="")
    				$this->_hollidays = CProject::getHollidays($project);
    			else 
    				$this->_hollidays = CUser::getHollidays($id);
    			break;	
    		}
    		
    	}
   
    }	
    

	function addDays( $n ) {
		//$this->setDate( $this->getTime() + 60 * 60 * 24 * $n, DATE_FORMAT_UNIXTIME);

		if ($n > 0){
			$total_days = 0;
			$eval_date = $this;
			$cal_dates = array_keys($this->_work_calendar);
			for ($i=0;$i<count($cal_dates);$i++){
				
				$start_calendar = $cal_dates[$i];
				if ($i+1 == count($cal_dates)){
					//es el ultimo calendario entonces la fecha de fin es muy grande
					$end_calendar = "29991231";
				}else{
					$end_calendar = $cal_dates[$i+1];
				}
				
				while (	$start_calendar <= $eval_date->format(FMT_TIMESTAMP_DATE) 	&&
						$end_calendar > $eval_date->format(FMT_TIMESTAMP_DATE)		&& 
						$total_days < $n){
							
					$cal = new CCalendar();		
					$cal = $this->_work_calendar[$start_calendar];
					$calday = new CCalendarDay();
					$day_id = $eval_date->getDayOfWeek() + 1;
					$calday = $cal->_calendar_days[$day_id];
					
					if( $calday->calendar_day_working && !isset($this->_hollidays[$eval_date->format(FMT_TIMESTAMP_DATE)]) ){
						$end_date = $eval_date->format(FMT_TIMESTAMP_DATE);
						$total_days++;
					}
					
					//incremento un dia en la fecha
					$eval_date->setDate( $eval_date->getTime() + 60 * 60 * 24 * 1, DATE_FORMAT_UNIXTIME);
				}
			}
	
			$this->setDate( $eval_date->getTime(), DATE_FORMAT_UNIXTIME);
		}elseif ($n < 0){
			$total_days = 0;
			$eval_date = $this;
			$cal_dates = array_keys($this->_work_calendar);
			for ($i=count($cal_dates)-1;$i>=0;$i--){
				
				$start_calendar = $cal_dates[$i];
				if ($i+1 == count($cal_dates)){
					//es el ultimo calendario entonces la fecha de fin es muy grande
					$end_calendar = "29991231";
				}else{
					$end_calendar = $cal_dates[$i+1];
				}
				
				while (	$start_calendar <= $eval_date->format(FMT_TIMESTAMP_DATE) 	&&
						$end_calendar > $eval_date->format(FMT_TIMESTAMP_DATE)		&& 
						$total_days >= $n){
							
					$cal = new CCalendar();		
					$cal = $this->_work_calendar[$start_calendar];
					$calday = new CCalendarDay();
					$day_id = $eval_date->getDayOfWeek() + 1;
					$calday = $cal->_calendar_days[$day_id];
					
					if( $calday->calendar_day_working && !isset($this->_hollidays[$eval_date->format(FMT_TIMESTAMP_DATE)]) ){
						$end_date = $eval_date->format(FMT_TIMESTAMP_DATE);
						$total_days--;
					}
					
					//si aun no se completaron los dias decremento en un dia la fecha
					if ($total_days >= $n)
						$eval_date->setDate( $eval_date->getTime() + 60 * 60 * 24 * -1, DATE_FORMAT_UNIXTIME);
				}
			}
	
			$this->setDate( $eval_date->getTime(), DATE_FORMAT_UNIXTIME);	
		}
	}
	
	function addHours($n){

		if ($n > 0){
			$total_hours = 0;
			//$eval_date = new CWorkCalendar();
			$eval_date = $this;
			$cal_dates = array_keys($this->_work_calendar);
			for ($i=0;$i<count($cal_dates);$i++){
				
				$start_calendar = $cal_dates[$i];
				if ($i+1 == count($cal_dates)){
					//es el ultimo calendario entonces la fecha de fin es muy grande
					$end_calendar = "29991231";
				}else{
					$end_calendar = $cal_dates[$i+1];
				}
				
				while (	$start_calendar <= $eval_date->format(FMT_TIMESTAMP_DATE) 	&&
						$end_calendar > $eval_date->format(FMT_TIMESTAMP_DATE)		&& 
						$total_hours < $n){
							
					$cal = new CCalendar();		
					$cal = $this->_work_calendar[$start_calendar];
					$calday = new CCalendarDay();
					$day_id = $eval_date->getDayOfWeek() + 1;
					$calday = $cal->_calendar_days[$day_id];
					
					if( $calday->calendar_day_working && !isset($this->_hollidays[$eval_date->format(FMT_TIMESTAMP_DATE)]) ){
						$start_time = new CDate($calday->calendar_day_from_time1);
						$end_time = new CDate($calday->calendar_day_to_time1);
						
						$diff = $n - $total_hours;
						$shift_duration = CWorkCalendar::timesDiff($start_time, $end_time)/ 3600;
						$j=1;
						while ($diff > 0 && $shift_duration > 0 && $j <= 5){
							
							if($eval_date->format(FMT_TIMESTAMP_TIME) < $start_time->format(FMT_TIMESTAMP_TIME)){
								$eval_date->setTime($start_time->getHour(), $start_time->getMinute(), $start_time->getSecond());								
								
								if ($diff < $shift_duration) //si falta menos tiempo para terminar que el tiempo que dura el turno
									// tomo el tiempo faltante para llegar al momento final
									$inc = $diff;//CWorkCalendar::timesDiff($start_time, $actual)/ 3600;
								else 
									$inc = $shift_duration;
							}else{
								$actual = new CDate($end_time->format(FMT_TIMESTAMP_DATE).$eval_date->format(FMT_TIMESTAMP_TIME));
								
								if ($total_hours==0){ //significa que es el primer turno que se emplea
									// tiempo restante del turno
									$remaining_time = CWorkCalendar::timesDiff($actual, $end_time)/ 3600;
								
									//si falta menos tiempo para terminar que el tiempo que lo que resta 
									//del turno
									$inc = $diff < $remaining_time ? $diff : $remaining_time;
								}

							}
							//$inc = $diff >= $shift_duration ? $shift_duration : $diff;
							$total_hours += $inc;
							$eval_date->setDate( $eval_date->getTime() + $inc * 60 * 60, DATE_FORMAT_UNIXTIME);														
							
							$diff = $n - $total_hours;
							$j++;
							$start_time = "calendar_day_from_time$j";
							$end_time = "calendar_day_to_time$j";
							if ($calday->$start_time !== NULL && $calday->$end_time !== NULL){
								$start_time = new CDate($calday->$start_time);
								$end_time = new CDate($calday->$end_time);						
								$shift_duration = CWorkCalendar::timesDiff($start_time, $end_time)/ 3600;
							}else{
								$shift_duration = 0;
							}
						}
					}
					
					//establezco la hora a cero e incremento un dia en la fecha
					if ($total_hours < $n){
						$eval_date->setTime(0, 0, 0);
						$eval_date->setDate( $eval_date->getTime() + 60 * 60 * 24 * 1, DATE_FORMAT_UNIXTIME);
					}
				}
			}
	
			$this->setDate( $eval_date->getTime(), DATE_FORMAT_UNIXTIME);
		}elseif ($n < 0){
			$total_hours = 0;
			//$eval_date = new CWorkCalendar();
			$eval_date = $this;
			$cal_dates = array_keys($this->_work_calendar);
			$n = abs($n);
			for ($i=count($cal_dates)-1;$i>=0;$i--){
				
				$start_calendar = $cal_dates[$i];
				if ($i+1 == count($cal_dates)){
					//es el ultimo calendario entonces la fecha de fin es muy grande
					$end_calendar = "29991231";
				}else{
					$end_calendar = $cal_dates[$i+1];
				}
				
				while (	$start_calendar <= $eval_date->format(FMT_TIMESTAMP_DATE) 	&&
						$end_calendar > $eval_date->format(FMT_TIMESTAMP_DATE)		&& 
						$total_hours < $n){
							
					$cal = new CCalendar();		
					$cal = $this->_work_calendar[$start_calendar];
					$calday = new CCalendarDay();
					$day_id = $eval_date->getDayOfWeek() + 1;
					$calday = $cal->_calendar_days[$day_id];
					
					if( $calday->calendar_day_working && !isset($this->_hollidays[$eval_date->format(FMT_TIMESTAMP_DATE)]) ){
						$start_time = new CDate($calday->calendar_day_from_time1);
						$end_time = new CDate($calday->calendar_day_to_time1);
						
						$diff = $n - $total_hours;
						$shift_duration = CWorkCalendar::timesDiff($start_time, $end_time)/ 3600;
						$j=1;
						while ($diff > 0 && $shift_duration > 0 && $j <= 5
							&&	$eval_date->format(FMT_TIMESTAMP_TIME) > $start_time->format(FMT_TIMESTAMP_TIME)){

							if($eval_date->format(FMT_TIMESTAMP_TIME) > $end_time->format(FMT_TIMESTAMP_TIME)){
								$eval_date->setTime($end_time->getHour(), $end_time->getMinute(), $end_time->getSecond());
								
								if ($diff < $shift_duration) //si falta menos tiempo para terminar que el tiempo que dura el turno
									// tomo el tiempo faltante para llegar al momento final
									$inc = $diff;
								else 
									$inc = $shift_duration;
							}else{
								$actual = new CDate($start_time->format(FMT_TIMESTAMP_DATE).$eval_date->format(FMT_TIMESTAMP_TIME));
								
								if ($total_hours==0){ //significa que es el primer turno que se emplea
								
									// tiempo restante del turno
									$remaining_time = CWorkCalendar::timesDiff($start_time, $actual)/ 3600;
								
									//si falta menos tiempo para terminar que el tiempo que lo que resta 
									//del turno
									$inc = $diff < $remaining_time ? $diff : $remaining_time;
								}

							}
															
							$total_hours += $inc;
							$eval_date->setDate( $eval_date->getTime() - $inc * 60 * 60, DATE_FORMAT_UNIXTIME);														

							
							$diff = $n - $total_hours;
							$j++;
							$start_time = "calendar_day_from_time$j";
							$end_time = "calendar_day_to_time$j";
							if ($calday->$start_time !== NULL && $calday->$end_time !== NULL){
								$start_time = new CDate($calday->$start_time);
								$end_time = new CDate($calday->$end_time);						
								$shift_duration = CWorkCalendar::timesDiff($start_time, $end_time)/ 3600;
								//$shift_duration = ($end_time->getTime() - $start_time->getTime()) / 60 / 60;
							}else{
								$shift_duration = 0;
							}
						}
					}
					
					//establezco la hora a cero e incremento un dia en la fecha
					if ($total_hours < $n){
						$eval_date->setTime(23, 59, 59);
						$eval_date->setDate( $eval_date->getTime() + 60 * 60 * 24 * -1, DATE_FORMAT_UNIXTIME);
					}
				}
			}
	
			$this->setDate( $eval_date->getTime(), DATE_FORMAT_UNIXTIME);
		
		}	
	}

    /**
     * Calculate the difference in hours or days between $from_date and $this using the work calendar
     * and the active times.
     * Number of seconds between $from_date and $this. Is positive if $from_date is before $this
     *
     * duration_type (1:hours ; 24:days)
     *
     * @access public
     * @return int Number of seconds between $when and $this. Is positive if $when < $this
	 * @param CDate when 
	 * @param duration_type (1:hours ; 24:days)
     * @author Rodrigo Fuentes
     */  	
	function dateDiff(&$from_date, $duration_type=1){
		$n = doubleval($this->format(FMT_TIMESTAMP)) - doubleval($from_date->format(FMT_TIMESTAMP));
		if ($n >= 0){
			$from = $from_date;
			$to = $this;
		}else{
			$to = $from_date;
			$from = $this;		
		}
		$no_working = true;
		$total_seconds = 0;
		$total_days = 0;
		$eval_date = new CDate();
		$eval_date = $from;
		$cal_dates = array_keys($this->_work_calendar);
		$diff = CWorkCalendar::timesDiff($eval_date, $to);
		for ($i=0;$i<count($cal_dates) && $diff > 0;$i++){
			
			$start_calendar = $cal_dates[$i];
			if ($i+1 == count($cal_dates)){
				//es el ultimo calendario entonces la fecha de fin es muy grande
				$end_calendar = "29991231";
			}else{
				$end_calendar = $cal_dates[$i+1];
			}
			
			while (	$start_calendar <= $eval_date->format(FMT_TIMESTAMP_DATE) 	&&
					$end_calendar > $eval_date->format(FMT_TIMESTAMP_DATE)		&& 
					$eval_date->format(FMT_TIMESTAMP) < $to->format(FMT_TIMESTAMP)){
						
				$cal = new CCalendar();		
				$cal = $this->_work_calendar[$start_calendar];
				$calday = new CCalendarDay();
				$day_id = $eval_date->getDayOfWeek() + 1;
				$calday = $cal->_calendar_days[$day_id];
				
				if( $calday->calendar_day_working && !isset($this->_hollidays[$eval_date->format(FMT_TIMESTAMP_DATE)]) ){
					
					//actualizo la fecha de inicio
					if ($no_working){
						if ($n >=0){
							$from_date->setDate($eval_date->getTime(), DATE_FORMAT_UNIXTIME);
						}else{
							$this->setDate($eval_date->getTime(), DATE_FORMAT_UNIXTIME);
						}
						$no_working = false;
					}
					
					$total_days+=1;
					$start_time = new CDate($calday->calendar_day_from_time1);
					$end_time = new CDate($calday->calendar_day_to_time1);
					
					$start_time->setDay($eval_date->getDay());
					$start_time->setMonth($eval_date->getMonth());
					$start_time->setYear($eval_date->getYear());
					
					$end_time->setDay($eval_date->getDay());
					$end_time->setMonth($eval_date->getMonth());
					$end_time->setYear($eval_date->getYear());	
									
					// muevo a fecha y hora que se evalua al inicio del turno si es anterior	
					if ($eval_date->format(FMT_TIMESTAMP_TIME) < $start_time->format(FMT_TIMESTAMP_TIME) )
							$eval_date->setTime($start_time->getHour(), $start_time->getMinute(), $start_time->getSecond());

							
							
					//obtengo la duracion del turno en segundos
					$shift_duration = CWorkCalendar::timesDiff($start_time, $end_time);		
					
					//calculo cuanto falta para llegar a la fecha y hora de fin
					//$diff = $eval_date->format(FMT_TIMESTAMP) - $start_time->format(FMT_TIMESTAMP);					
					$diff = CWorkCalendar::timesDiff($eval_date, $to);
					
					
					//si falta menos de un día para el dia y hora de fin
					if ($diff < 86400){
						//tomo como hora de fin la menor entre el fin del turno y el fin horario
						//$diff_end = CWorkCalendar::timesDiff($eval_date, $end_time);
						$diff_end = CWorkCalendar::timesDiff($to, $end_time);
						if ($diff_end > 0){
							$shift_rest = CWorkCalendar::timesDiff($eval_date, $to);
						}else{
							$shift_rest = CWorkCalendar::timesDiff($eval_date, $end_time);
						}
					}else{
						$shift_rest = CWorkCalendar::timesDiff($eval_date, $end_time);
					}
					$j=1;
					
					while ($diff > 0 && $shift_rest > 0 && $shift_duration> 0 && $j <= 5){
						
						// muevo a fecha y hora que se evalua al inicio del turno si es anterior
						if ($eval_date->format(FMT_TIMESTAMP_TIME) < $start_time->format(FMT_TIMESTAMP_TIME))
							$eval_date->setTime($start_time->getHour(), $start_time->getMinute(), $start_time->getSecond());
							
						// si lo que falta para llegar es mayor a la duracion del turno						
						if ($shift_rest >= $shift_duration){
							$total_seconds += $shift_duration;
							$eval_date->setTime($end_time->getHour(), $end_time->getMinute(), $end_time->getSecond());
							
						}else{
							$total_seconds += $shift_rest;
							$eval_date->setDate( $eval_date->getTime() + $shift_rest, DATE_FORMAT_UNIXTIME);				
						}
						//calculo cuanto falta para llegar a la fecha y hora de fin
						//$diff = $to->getTime() - $eval_date->getTime();
						$diff = CWorkCalendar::timesDiff($eval_date, $to);
							
						//tiempo restante hasta el fin del turno desde este momento
						//$shift_rest = CWorkCalendar::timesDiff($eval_date, $end_time);
						
						//si falta menos de un día para el dia y hora de fin
						if ($diff < 86400){
							//tomo como hora de fin la menor entre el fin del turno y el fin horario
							//$diff_end = CWorkCalendar::timesDiff($eval_date, $end_time);
							$diff_end = CWorkCalendar::timesDiff($to, $end_time);
							if ($diff_end > 0){
								$shift_rest = CWorkCalendar::timesDiff($eval_date, $to);
							}else{
								$shift_rest = CWorkCalendar::timesDiff($eval_date, $end_time);
							}
						}else{
							$shift_rest = CWorkCalendar::timesDiff($eval_date, $end_time);
						}						
						$j++;
						$start_time = "calendar_day_from_time$j";
						$end_time = "calendar_day_to_time$j";
						if ($calday->$start_time !== NULL && $calday->$end_time !== NULL){
							$start_time = new CDate($calday->$start_time);
							$end_time = new CDate($calday->$end_time);	
							
							//obtengo la duracion del turno en segundos					
							//$shift_duration = ($end_time->getTime() - $start_time->getTime()) ;
							$shift_duration = CWorkCalendar::timesDiff($start_time, $end_time);
							
							// muevo a fecha y hora que se evalua al inicio del turno si es anterior
							if ($eval_date->format(FMT_TIMESTAMP_TIME) < $start_time->format(FMT_TIMESTAMP_TIME))
								$eval_date->setTime($start_time->getHour(), $start_time->getMinute(), $start_time->getSecond());
							
						}else{
							$shift_duration = 0;
						}
					}
				}
				
				//establezco la hora a cero e incremento un dia en la fecha
				if ($diff>0){
					$eval_date->setTime(0, 0, 0);
					$eval_date->setDate( $eval_date->getTime() + 60 * 60 * 24 * 1, DATE_FORMAT_UNIXTIME);
				}else{
					$eval_date->getDay();
				}
			}	
		}
		
		if ($n >=0){
			$this->setDate($eval_date->getTime(), DATE_FORMAT_UNIXTIME);
		}else{
			$from_date->setDate($eval_date->getTime(), DATE_FORMAT_UNIXTIME);
		}		
		/*
		if ($n >= 0){
			$this=$eval_date;
		}else{
			$when = $eval_date;
		}
			*/	
		if ($duration_type == 1){
			return $total_seconds * ($n > 0 ? -1 : 1) / 3600;
		}else{
			return $total_days; 
		}
	}

	function timesDiff($from, $to){
		if (is_a($from, "CDate") && is_a($to, "CDate")){
			$dias = $to->absoluteDays() - $from->absoluteDays();
			
			$from_ts1 = mktime($from->getHour(), $from->getMinute(), $from->getSecond(),1,1,1970);
			$from_ts2 = mktime(0, 0, 0,1,2,1970);
			
			$to_ts1 = mktime($to->getHour(), $to->getMinute(), $to->getSecond(),1,1,1970);
			$to_ts2 = mktime(0, 0, 0,1,1,1970);
			 
			//cantidad de segundos transcurridos en el dia desde
			$diff_day_from = ($from_ts2 - $from_ts1);
			//cantidad de segundos transcurridos en el dia hasta
			$diff_day_to = ($to_ts1 - $to_ts2);
			
			return ($dias - 1) * 24 * 60 * 60 + $diff_day_from + $diff_day_to;
		}else{
			return false;
		}
	
	}
	
	function fitDateToCalendar(){
		
		$is_working = false;
		//$eval_date = new CWorkCalendar();
		$eval_date = $this;
		$cal_dates = array_keys($this->_work_calendar);
		for ($i=0;$i<count($cal_dates) && $is_working==false;$i++){
			
			$start_calendar = $cal_dates[$i];
			if ($i+1 == count($cal_dates)){
				//es el ultimo calendario entonces la fecha de fin es muy grande
				$end_calendar = "29991231";
			}else{
				$end_calendar = $cal_dates[$i+1];
			}
			
			while (	$start_calendar <= $eval_date->format(FMT_TIMESTAMP_DATE) 	&&
					$end_calendar > $eval_date->format(FMT_TIMESTAMP_DATE)		&& 
					$is_working==false){
						
				$cal = new CCalendar();		
				$cal = $this->_work_calendar[$start_calendar];
				$calday = new CCalendarDay();
				$day_id = $eval_date->getDayOfWeek() + 1;
				$calday = $cal->_calendar_days[$day_id];
				//si el dia es laborable
				if( $calday->calendar_day_working && !isset($this->_hollidays[$eval_date->format(FMT_TIMESTAMP_DATE)]) ){

					//obtengo los horarios del los turnos
					$is_valid_shift = true;
					for($j=1; $j<=5 && $is_working == false && $is_valid_shift; $j++){
						$start_time = "calendar_day_from_time$j";
						$end_time = "calendar_day_to_time$j";
						if ($calday->$start_time !== NULL && $calday->$end_time !== NULL){
							$start_time = new CDate($calday->$start_time);
							$end_time = new CDate($calday->$end_time);						
							
							//si el horario esta entre el inicio y el fin del turno es laborable
							if ($start_time->format(FMT_TIMESTAMP_TIME) <= $eval_date->format(FMT_TIMESTAMP_TIME) &&
								$end_time->format(FMT_TIMESTAMP_TIME) > $eval_date->format(FMT_TIMESTAMP_TIME)){
								$is_working = true;	
							}
							//si el horario es anterior al horario de inicio del turno
							elseif($start_time->format(FMT_TIMESTAMP_TIME) > $eval_date->format(FMT_TIMESTAMP_TIME)){
								$eval_date->setTime($start_time->getHour(), $start_time->getMinute(), $start_time->getSecond());
								$is_working = true;	
							}
						}else{
							$is_valid_shift = false;
						}					
					
					}
				}
					
				
				//establezco la hora a cero e incremento un dia en la fecha
				if ($is_working == false){
					$eval_date->setTime(0, 0, 0);
					$eval_date->setDate( $eval_date->getTime() + 60 * 60 * 24 * 1, DATE_FORMAT_UNIXTIME);
				}
			}
		}

		$this->setDate( $eval_date->getTime(), DATE_FORMAT_UNIXTIME);
	
	
	
	
	}

}

?>