<?php 

echo "<pre>";
var_dump($_POST);
echo "</pre>";


$del = dPgetParam( $_POST, 'del', 0 );
$obj = new CCalendar();
$msg = '';

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}


// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Calendar' );
if ($del) {
	if (!$obj->canDelete( $msg )) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	$obj->loadCalendarDays();
	//elimino todos los dias del calendario
	for($i=1; $i<=7;$i++){
		if (is_a($obj->_calendar_days[$i],"CCalendarDay")){		
			if (($msg = $obj->_calendar_days[$i]->delete())) {
				$AppUI->setMsg( $msg, UI_MSG_ERROR, true );
				echo $msg."\n";
			}			
		}
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( 'deleted', UI_MSG_ALERT, true );
		$url = $AppUI->getPlace();
		if (strpos($url, "view") > 0 ){
			$AppUI->redirect( '', -1 );
		}else{
			$AppUI->redirect();
		}
	}
} else {

	if ($obj->calendar_id =="0"){
	$obj->calendar_status = "0";
	$nuevo = 1;
	}
	else{
    $nuevo= 0;
	}

	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$obj->_calendar_days=array();
		echo "<u>Control 1</u><br>";
		echo "<br>Calendar id: ".$obj->calendar_id."(Nuevo:$nuevo)<br>";

		echo "<pre>";
		var_dump($obj);
        
		for ($i=1;$i<=7;$i++){
			$obj->_calendar_days[$i] = new CCalendarDay();
			if ($_POST["calendar_day_id"][$i] > 0){
				if(!$obj->_calendar_days[$i]->load($_POST["calendar_day_id"][$i])){
					$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
					echo $obj->getError()."\n";
					$obj->delete();
					$AppUI->redirect();				
				}
			}else{
				$obj->_calendar_days[$i]->calendar_day_id = "";				
				$obj->_calendar_days[$i]->calendar_day_day = $i;
			}
			$obj->_calendar_days[$i]->calendar_id = $obj->calendar_id;
			$obj->_calendar_days[$i]->calendar_day_working = (@$_POST["calendar_working"][$i] == 1? "1" : "0");
			for($j=1;$j<=5;$j++){
     
				$from_field = "calendar_day_from_time$j";
				$to_field = "calendar_day_to_time$j";				
				if (strlen($_POST["cal_from_time$j"][$i])<4 || strlen($_POST["cal_to_time$j"][$i])<4){
					$obj->_calendar_days[$i]->$from_field = NULL;
					$obj->_calendar_days[$i]->$to_field = NULL;
				}else{
					$obj->_calendar_days[$i]->$from_field = "0000-00-00 ".
								substr($_POST["cal_from_time$j"][$i],0,2).":".
								substr($_POST["cal_from_time$j"][$i],2,2).":00";
					
					$obj->_calendar_days[$i]->$to_field = "0000-00-00 ".
								substr($_POST["cal_to_time$j"][$i],0,2).":".
								substr($_POST["cal_to_time$j"][$i],2,2).":00";
				}
                
               $ts_to = (substr($_POST["cal_to_time$j"][$i],0,2) * 3600 )+  (substr($_POST["cal_to_time$j"][$i],2,2) * 60);
               $ts_from = (substr($_POST["cal_from_time$j"][$i],0,2) * 3600 )+  (substr($_POST["cal_from_time$j"][$i],2,2) * 60);

				$hs[$obj->_calendar_days[$i]->calendar_day_id] += ($ts_to - $ts_from) / 3600;
                
				
				if($obj->_calendar_days[$i]->$to_field!= "" && $obj->_calendar_days[$i]->$from_field !="" )
				{
				$query = "UPDATE calendar_days SET 
				calendar_day_to_time$j = '".$obj->_calendar_days[$i]->$to_field."',
                calendar_day_from_time$j = '".$obj->_calendar_days[$i]->$from_field."' 
				WHERE calendar_day_id = '".$obj->_calendar_days[$i]->calendar_day_id."' ";
				
                }
				else{
				$query = "UPDATE calendar_days SET 
				calendar_day_to_time$j = NULL ,
                calendar_day_from_time$j = NULL 
				WHERE calendar_day_id = '".$obj->_calendar_days[$i]->calendar_day_id."' ";
				}
                
				if($nuevo=="0"){
				$sql = mysql_query($query);
				}
			}
                
				if($nuevo=="0"){
				$query_2 = "UPDATE calendar_days SET calendar_day_hours = '".$hs[$obj->_calendar_days[$i]->calendar_day_id]."' WHERE calendar_day_id = '".$obj->_calendar_days[$i]->calendar_day_id."' ";
                
				$sql2 = mysql_query($query_2);
				}

            if($nuevo=="1"){
				if (($msg = $obj->_calendar_days[$i]->store())) {
					$AppUI->setMsg( $msg, UI_MSG_ERROR, true );
					echo $msg."\n";
				} 			
			}
			//var_dump($obj);
		}
		echo "</pre>";
		$AppUI->setMsg( @$_POST['calendar_id'] ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>