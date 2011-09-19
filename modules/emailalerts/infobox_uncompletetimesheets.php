<?php
require_once($AppUI->getModuleClass("system"));

?>
<table cellpadding="2" cellspacing="0" border="0" width="100%" class="">
<tr class="tableHeaderGral">
	<th width="150">
		<?php echo $AppUI->_('Login Name');?>
	</th>
<!--	<th>
		<?php echo $AppUI->_('Real Name');?>
	</th>
-->
	<th>
		<?php echo $AppUI->_('Pending Records');?>
	</th>
	<th>
		<?php echo $AppUI->_('Max. Delay');?>
	</th>
</tr>

<?
	$today = new CDate();
  $resultut = mysql_query("SELECT * FROM users WHERE user_type <> 5 ORDER BY user_last_name, user_first_name ;");
  while ($rowut = mysql_fetch_array($resultut, MYSQL_ASSOC)) {
    $resultut2 = mysql_query("SELECT * FROM user_tasks, tasks WHERE user_tasks.user_id = {$rowut["user_id"]} AND tasks.task_id = user_tasks.task_id AND tasks.task_status=0 AND tasks.task_complete = 0 AND tasks.task_manual_percent_complete <> 100 AND tasks.task_start_date < CURDATE() ;");
    $tottasks=0;
    $maxdelay=0;
    while ($rowut2 = mysql_fetch_array($resultut2, MYSQL_ASSOC)) {
    	$sql = "
				SELECT timexp_date, DATE_FORMAT(timexp_date,'%m/%d/%Y') as date 
				FROM timexp 
				WHERE timexp_applied_to_type = 1
				AND timexp_applied_to_id = {$rowut2["task_id"]} 
				AND timexp_type = 1
				AND timexp_creator = {$rowut2["user_id"]} 
				ORDER BY timexp_date  DESC ;";
      $resultut3 = mysql_query($sql);
      if(mysql_num_rows($resultut3)>0){
      	
        $rowut3 = mysql_fetch_array($resultut3, MYSQL_ASSOC);
        //$cal = new CWorkCalendar(3, $rowut2["user_id"], $rowut2["task_project"], $rowut3["timexp_date"] );
		$cal = new CDate($rowut3["timexp_date"]);

        $curdelay = abs($cal->dateDiff($today));
        if($curdelay > 0){  // =0 significa ayer
          if($maxdelay < $curdelay) $maxdelay = $curdelay;
          $tottasks++;
        }        
        /*$lastinput = $rowut3["date"];
        $today = date("m/d/Y");
        $curdelay = count_workdays($lastinput, $today)-2;
        if($curdelay > 0){  // =0 significa ayer
          if($maxdelay < $curdelay) $maxdelay = $curdelay;
          $tottasks++;
        }*/
      }
      else{
        //$cal = new CWorkCalendar(3, $rowut2["user_id"], $rowut2["task_project"], $rowut2["task_start_date"]);
        $cal = new CDate($rowut2["task_start_date"]);
        $curdelay = abs($cal->dateDiff($today));
        /*      	
        $lastinput = $rowut2["date"];
        $today = date("m/d/Y");
        $curdelay = count_workdays($lastinput, $today)-2;*/
        if($curdelay > 0){  // =0 significa ayer
          if($maxdelay < $curdelay) $maxdelay = $curdelay;
          $tottasks++;
        }
      }
    }
    if($tottasks!=0){
?>

<tr>
	<td>
		<a href="mailto:<?php echo $rowut["user_email"];?>"><img src="images/obj/email.gif" width="16" height="16" border="0" alt="email"></a>
		<?php echo $rowut["user_username"];?>
	</td>
<!--
	<td>
		<a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $rowut["user_id"];?>"><?php echo $rowut["user_last_name"].', '.$rowut["user_first_name"];?></a>
	</td>
-->
	<td>
		<?php echo $tottasks;?>
	</td>
	<td>
		<?php echo $maxdelay;?>
	</td>
</tr>
<tr class="tableRowLineCell">
    <td colspan="3"></td>
</tr>
<?
  }
  }
?>
</table>

<?
function count_workdays($date1,$date2){
$firstdate = strtotime($date1);
$lastdate = strtotime($date2);
$firstday = date(w,$firstdate);
$lastday = date(w,$lastdate);
$totaldays = intval(($lastdate-$firstdate)/86400)+1;

//check for one week only
if ($totaldays<=7 && $firstday<=$lastday){
     $workdays = $lastday-$firstday+1;
     //check for weekend
     if ($firstday==0){
             $workdays = $workdays-1;
            }
     if ($lastday==6){
             $workdays = $workdays-1;
            }
     
     }else { //more than one week

             //workdays of first week
            if ($firstday==0){
                 //so we don't count weekend
                 $firstweek = 5; 
                 }else {
                 $firstweek = 6-$firstday;
                 }
            $totalfw = 7-$firstday;

            //workdays of last week
            if ($lastday==6){
                 //so we don't count sat, sun=0 so it won't be counted anyway
                 $lastweek = 5;
                 }else {
                 $lastweek = $lastday;
                 }
            $totallw = $lastday+1;
                 
            //check for any mid-weeks 
            if (($totalfw+$totallw)>=$totaldays){
                 $midweeks = 0;
                 } else { //count midweeks
                 $midweeks = (($totaldays-$totalfw-$totallw)/7)*5;
                 }

            //total num of workdays
            $workdays = $firstweek+$midweeks+$lastweek;

        }

/*
check for and subtract and holidays etc. here
...
*/

return ($workdays);
} //end funtion count_workdays()


?>