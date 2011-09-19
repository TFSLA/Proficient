<?php 

$timexp_types = array(
"1"=>"Time",
"2"=>"Expense"
);


$timexp_applied_to_types = array(
"1"=>"Task",
"2"=>"Bug",
"3"=>"Internal",
"4"=>"To-do"
);

$billables = array ( "0" => "No", "1" => "Yes");
$contributes = $billables;

$timexp_status = array(
"0" => "Unsent",
"1" => "On course",
"2" => "Disapproved",
"3" => "Approved",
"4" => "Annulled"
);

$timexp_status_color = array(
"0" => "#FFFFFF",
"1" => "#DDDDFF",
"2" => "#FFDDDD",
"3" => "#DDFFDD",
"4" => "#FF5555"
);

$qty_units = array(
"1" => "Hours",
"2" => "Costs"
);

$iconsYN = array(
"0" => $AppUI->_( 'No' ), //'<img src="./images/icons/stock_cancel-16.png" alt="'.$AppUI->_( 'No' ).'" border="0" />',
"1" => $AppUI->_( 'Yes' )	//'<img src="./images/icons/stock_ok-16.png" alt="'.$AppUI->_( 'Yes' ).'" border="0" />'
);

$te_status_transition = array(
"0" => "1,4",
"1" => "2,3",
"2" => "1",
"3" => ""
);


//matriz de transición de estados de timesheets
$ts_status_transition = array(
"0" => "1,4",
"1" => "2,3",
"2" => "1",
"3" => "",
"4" => "0"
);


//estados de timesheet que requieren log de los reg asociados
$ts_log_required = array( 
"2" ,
"4" 
);

// nombres descriptivos para las hojas de tiempos y gastos
$name_sheets = array(
"1" => "Timesheet",
"2" => "Expense Sheet"
);
?>