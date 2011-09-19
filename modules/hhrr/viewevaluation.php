<?php
$id = isset($_GET['id']) ? $_GET['id'] : 0;
$eval_id = isset($_GET['eval_id']) ? $_GET['eval_id'] : 0;

$user = new CUser();
$user->load($id);
$ttl = $user->user_last_name.", ".$user->user_first_name;
$df = $AppUI->getPref('SHDATEFORMAT');

$titleBlock = new CTitleBlock( $ttl, 'hhrr.gif', $m, 'hhrr.index' );

$titleBlock->addCrumb( "?m=hhrr&tab=2", strtolower($AppUI->_('Jobs List')) );
$titleBlock->addCrumb( "?m=hhrr&a=viewhhrr&id=$id", strtolower($AppUI->_('view user')) );
$titleBlock->show();

$sql = "SELECT * FROM hhrr_skills_evaluations 
		WHERE evaluated_user = $id
		AND evaluation_id = $eval_id";

$evaluation_data = mysql_fetch_array(mysql_query($sql));

$date = new CDate($row["evaluation_date"]);
$hour = $date->hour.":".$date->minute;

$user_query = "SELECT CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name FROM users u WHERE user_id=".$evaluation_data['evaluation_user'];
$user_result = mysql_query($user_query) or die(mysql_error());
$user_name = mysql_fetch_array($user_result);

$job_query = "SELECT job_name FROM hhrr_jobs WHERE job_id=".$evaluation_data['comparing_job'];
$job_result = mysql_query($job_query) or die(mysql_error());
$job_name = mysql_fetch_array($job_result);
?>

<table width="100%" border="0" cellpadding="2" cellspacing="0" class="std">
<tr>
	<td></td>
	<td width="10%"><br></td>
	<td width="50%"><br></td>
	<td width="10"><br></td>
</tr>
<tr>
	<td></td>
	<td align="left"><?=$AppUI->_("Date")?>:</td>
	<td align="left"><?=$date->format($df)." ".$hour?></td>
</tr>
<tr>
	<td></td>
	<td align="left"><?=$AppUI->_("User")?>:</td>
	<td align="left"><?=$user_name["user_name"]?></td>
</tr>
<tr>
	<td></td>
	<td align="left"><?=$AppUI->_("Job")?>:</td>
	<td align="left"><?=$job_name["job_name"]?></td>
</tr>
<tr>
	<td colspan="3" align="right">
		<input type="button" class="button" value="<?=$AppUI->_("back")?>" onclick="history.back();">
	</td>
</tr>
<tr>
	<td><br>
	</td>
</tr>
</table>

<?php
require_once("viewskills.php");
?>