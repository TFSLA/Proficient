<?php
function mktime_get_difference()
{

	$date = date("Y-m-d H:i:s");
	ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})[ ]([0-9]{2}):([0-9]{2}):([0-9]{2})",$date,$date_part);
	// obtengo el valor unix timestamp de esa fecha
	$ts = mktime(	$date_part[4], 
						$date_part[5], 
						$date_part[6], 
						$date_part[2], 
						$date_part[3], 
						$date_part[1], 0);	
	$ts_ori = $ts;										
	$input_date = sprintf("%04d%02d%02d%02d%02d%02d",
						$date_part[1], $date_part[2], $date_part[3], $date_part[4], $date_part[5], $date_part[6]);
	$ts_date = date("YmdHis", $ts);
	while  ( $ts_date !== $input_date ){
		$diff = $ts_date - $input_date;
		$tmp_date = $input_date - $diff;
		if (ereg("([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})",
			$tmp_date, $date_part)) {
			$ts = mktime(
						$date_part[4], 
						$date_part[5], 
						$date_part[6], 
						$date_part[2], 
						$date_part[3], 
						$date_part[1], 0);	
			$ts_date = date("YmdHis", $ts);						
		}else{
			return -1;
		}	
	} 
	
	return $ts - $ts_ori;
}


$date_temp = date("Y-m-d H:i:s");
ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})[ ]([0-9]{2}):([0-9]{2}):([0-9]{2})",$date_temp,$date_part);
$date_ts = mktime(	$date_part[4], 
					$date_part[5], 
					$date_part[6], 
					$date_part[2], 
					$date_part[3], 
					$date_part[1], 0);
if ((date("Y-m-d H:i:s", $date_ts) != $date_temp)){
	$diff = mktime_get_difference();
}else{
	$diff = 0;
}

$script = 
		"<?php
		
		\$dPconfig['mktime_difference'] = $diff;
		
		?>";
$fp = fopen("mktime_difference.php","w+");
fwrite($fp, $script, strlen($script));
fclose($fp);

?>
