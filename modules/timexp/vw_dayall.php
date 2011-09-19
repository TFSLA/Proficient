<?	
global  $timexp_type, $timexp_types;

foreach($timexp_types as $ta_id => $ta_dsc){
	$timexp_type = $ta_id;
	include("vw_daytimexp.php");
} 
?>