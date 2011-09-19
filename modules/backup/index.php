<?php

if (getDenyRead( $m ))
	$AppUI->redirect( "m=public&a=access_denied" );

function automanual($file_exec){
	IF ($file_exec=='M') return 'Manual';
	ELSE return 'Auto';
}
?>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
<td valign="top" align="left" width="98%">
<?
// setup the title block
$titleBlock = new CTitleBlock( 'Backup database', 'system_admin.gif', $m, "$m.$a" );
$titleBlock->show();
IF ($_POST['dobackup']==1){
	$sql="UPDATE backup_data SET status=1 WHERE id=1";
	db_exec($sql);
}
?>
<br>
<table cellspacing="0" cellpadding="2" border="0" width="100%" class="std">
<tr><TD></TD></tr>
<?php 
	$sql="SELECT  date_format(date,'%Y%m%d%H%i%s') as date  FROM backup_data ORDER BY action";
	$rc=db_exec($sql);
	$vec=db_fetch_array($rc);
	
	$year=substr($vec['date'], 0, 4);
	$month=substr($vec['date'], 4, 2);
	$day=substr($vec['date'], 6, 2);
	$hour=substr($vec['date'], 8, 2);
	$min=substr($vec['date'], 10, 2);
?>
<tr>
	<TD align="center">
			<?php
				ECHO $AppUI->_( 'LastAutoBck' );
				ECHO "<b> $hour:$min $day/$month/$year </b>";
			?>
	</TD>
</tr>
<?php 
	$vec=db_fetch_array($rc);
	$year=substr($vec['date'], 0, 4);
	$month=substr($vec['date'], 4, 2);
	$day=substr($vec['date'], 6, 2);
	$hour=substr($vec['date'], 8, 2);
	$min=substr($vec['date'], 10, 2);
?>
<tr>
	<TD align="center">
		<?php
			ECHO $AppUI->_( 'LastManualBck' );
			ECHO "<b> $hour:$min $day/$month/$year </b>";
		?>
	</TD>
</tr>
<tr>
	<TD align="center">
		<?php
			ECHO $AppUI->_( 'SaveLast' );
			ECHO "&nbsp;";
			ECHO $dPconfig['backuphist'];
			ECHO "&nbsp;";
			ECHO $AppUI->_( 'Backups' );
		?>
	</TD>
</tr>
<tr>
	<TD align="center">
		<?php
			ECHO $AppUI->_( 'backfreq' );
			ECHO "&nbsp;";
			ECHO $dPconfig['backupfreq'];
			ECHO "&nbsp;";
			ECHO $AppUI->_( 'Days' );
		?>
	</TD>
</tr>
</table>
<br>
<table cellspacing="0" cellpadding="2" border="0" width="100%" class="std">
<tr>
	<TD align='center'>
		<form action="" method="post">
		<?php ECHO $AppUI->_( 'ForceBackup' ); ?>
		<input type='hidden' name='dobackup' value='1'>
		<input type='checkbox' name='do_backup'><br>
		<input type='submit' class='button' value='<?php ECHO strtolower($AppUI->_( 'Process' )); ?>'>
		</form>
	</TD>
</tr>
</table>

<br>
<?php
IF ($_POST['dobackup']==1){
	?>
	<center>
		<font color='red'><b><?php ECHO $AppUI->_( 'TheBackuP' );?></b></font>
		<br>
		<?php ECHO $AppUI->_( 'Refresh' );?>
		<a href="/index.php?m=backup"><img src="./images/icon_refresh.gif" border="0"></a>
	</center>
	<?php
}
?>
<br>

<table cellspacing="0" cellpadding="2" border="0" width="100%" class="std">
	<tr>
		<TD align="center" width="50%">
			<b>
			<?php ECHO $AppUI->_( 'Download DB Structure And Data' ); ?>
			</b>
		</TD>
		<TD align="center" width="50%">
			<b>
			<?php ECHO $AppUI->_( 'Download Files Backup' ); ?>
			</b>
		</TD>
	</tr>
	<?php
	$sql1="SELECT id, file_name, date_format(file_date,'%Y%m%d%H%i%s') as file_date , file_content, file_exec FROM backup WHERE file_content='DB' ORDER by file_date DESC";
	$sql2="SELECT id, file_name, date_format(file_date,'%Y%m%d%H%i%s') as file_date , file_content, file_exec FROM backup WHERE file_content='FR' ORDER by file_date DESC";
	$rc1=db_exec($sql1);
	$rc2=db_exec($sql2);
	while ($vec1=db_fetch_array($rc1) AND $vec2=db_fetch_array($rc2)) {
	$year=substr($vec1['file_date'], 0, 4);
	$month=substr($vec1['file_date'], 4, 2);
	$day=substr($vec1['file_date'], 6, 2);
	$hour=substr($vec1['file_date'], 8, 2);
	$min=substr($vec1['file_date'], 10, 2);
	echo "<tr><td align='center'>
			$hour:$min $day/$month/$year
			<a href='./functions/bkcup_fileviewer.php?id=".$vec1['id']."'>
			".$vec1['file_name']."</a>  <i>(";
			echo automanual($vec1['file_exec']);
			echo ")</i></td>";
	$year=substr($vec2['file_date'], 0, 4);
	$month=substr($vec2['file_date'], 4, 2);
	$day=substr($vec2['file_date'], 6, 2);
	$hour=substr($vec2['file_date'], 8, 2);
	$min=substr($vec2['file_date'], 10, 2);
	echo "<td align='center'>
			$hour:$min $day/$month/$year
			<a href='./functions/bkcup_fileviewer.php?id=".$vec2['id']."'>
			".$vec2['file_name']."</a>  <i>(";
			echo automanual($vec2['file_exec']);
			echo ")</i></td>";
	echo "</tr>";
	}
	?>
</table>
