<?php

function iil_StrToTime($str,$opt){
	//replace double spaces with single space
	$str = trim($str);
	$str = str_replace("  ", " ", $str);
	
	//strip off day of week
	$pos=strpos($str, " ");
	$word = substr($str, 0, $pos);
	if (!is_numeric($word)) $str = substr($str, $pos+1);

	//explode, take good parts
	$a=explode(" ",$str);
	$month_a=array("Jan"=>1,"Feb"=>2,"Mar"=>3,"Apr"=>4,"May"=>5,"Jun"=>6,"Jul"=>7,"Aug"=>8,"Sep"=>9,"Oct"=>10,"Nov"=>11,"Dec"=>12);
	$month_str=$a[1];
	$month=$month_a[$month_str];
	$day=$a[0];
	$year=$a[2];
	$time=$a[3];
	$tz_str = $a[4];
	$tz = substr($tz_str, 0, 3);
	$ta=explode(":",$time);
	$hour=(int)$ta[0]-(int)$tz;
	$minute=$ta[1];
	$second=$ta[2];

	//make UNIX timestamp
	if ($opt == 1){
		return mktime($hour, $minute, $second, $month, $day, $year);
	}else{
		return mktime_fix($hour, $minute, $second, $month, $day, $year);
	}
} 


echo date("Y-m-d H:i:s")."<br>";
echo gmdate("Y-m-d H:i:s")."<br>";
echo date("Z")."<br>";

	$now = time(); //local time
echo "ori ".date("Y-m-d H:i:s", $now)."<br>";
	$now = $now - date("Z"); //GMT time
echo "GMT ".date("Y-m-d H:i:s", $now)."<br>";
	$now = $now + (-3 * 3600); //user\\\\\\\\\\\\\\\'s time
echo "Loc ".date("Y-m-d H:i:s", $now)."<br>";
echo $now."<br>";



$str = "Wed, 3 Aug 2005 18:36:08 -0300";
if (($timestamp = strtotime($str)) === -1) {
    echo "La cadena ($str) no es válida.";
} else {
    echo "$str == ". date("l dS of F Y h:i:s A",$timestamp);
}
echo "<br>";echo "<br>";

if (($timestamp = iil_StrToTime($str, 1)) === -1) {
    echo "La cadena ($str) no es válida.";
} else {
    echo "$str == ". date("l dS of F Y h:i:s A",$timestamp);
}
echo "<br>";
if (($timestamp = iil_StrToTime($str, 2)) === -1) {
    echo "La cadena ($str) no es válida.";
} else {
    echo "$str == ". date("l dS of F Y h:i:s A",$timestamp);
}





?>