<?php
global $id;
$selectedJob = isset($_GET['job_id']) ? $_GET['job_id'] : 0;

$user = new CUser();
$user->load($id);
$ttl = $user->user_last_name.", ".$user->user_first_name;

$titleBlock = new CTitleBlock( $ttl, 'hhrr.gif', $m, 'hhrr.index' );

$titleBlock->addCrumb( "?m=hhrr&tab=2", strtolower($AppUI->_('Jobs List')) );
$titleBlock->addCrumb( "?m=hhrr&a=viewhhrr&id=$id", strtolower($AppUI->_('view user')) );
$titleBlock->show();
?>

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<form name="editFrm" action="" method="GET">
	<input type="hidden" name="m" value="hhrr" />
	<input type="hidden" name="a" value="compare" />
	<input type="hidden" name="id" value="<?php echo $_GET["id"];?>" />
<tr>
	<td width="30%"><br></td>
	<td><br></td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Please select a Job to compare' );?>:</td>
	<td>
	<?php
		$jobs = CJobs::getJobs();
		if($selectedJob<=0)
			$jobs["0"] = $AppUI->_("none");
		echo arraySelect( $jobs, 'job_id', 'size="1" class="text" style="width:250px;" onChange="submit()"', $selectedJob);
	?>
	</td>
</tr>
<tr>
	<td><br>
	</td>
</tr>
<?php if($selectedJob == 0) {?>
<tr>
	<td colspan="4" align="right">
		<input type="button" class="button" value="<?=$AppUI->_("back")?>" onclick="history.back();">
	</td>
</tr>
<tr>
	<td><br>
	</td>
</tr>
<?php } ?>
</form>
</table>

<?php
if($selectedJob != 0){
	include("viewskills.php");
}

?>