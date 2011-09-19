<?
$alltables = mysql_list_tables($dPconfig['dbname']);
while ($row = mysql_fetch_row($alltables))
{
		// all data from table
		$result = mysql_query('SELECT count(0) as cnt FROM '.$row[0]);
		$tablerow = mysql_fetch_array($result);
		$c+=$tablerow["cnt"];
}
?>
<script language="javascript">
function doBackup(){
	if(confirm("<?=$AppUI->_( "Backup generation may take a while" )."."?>\n<?=$AppUI->_( "The estimated process time is" ).": ".(floor($c/35/60)+1)." " ?><?=$AppUI->_( 'minutes' )."."?>\n<?=$AppUI->_( 'Do you really want to create generate a DB Backup?' )?> ")){
	  alert("<?=$AppUI->_( 'Please be patient' )."."?>\n<?=$AppUI->_( 'Do NOT reload this page until the backup is done' )."."?> ");
	  check_backup_options();
      //document.frmBackup.action = "?m=backup&a=do_backup&tid=" + Math.random()*(Math.random()+1);
      document.frmBackup.submit();

	}
}

</script>
<?php
// get the correct path to do_backup.php
$result = mysql_query('SELECT mod_directory FROM modules WHERE mod_name=\'backup\'');
$row = mysql_fetch_assoc($result);
$backup_path = './modules/'.$row['mod_directory'].'/';
?>
<script>
	function check_backup_options()
	{
        var f = document.frmBackup;
        if(f.export_what.options[f.export_what.selectedIndex].value==3)
		{
            f.droptable.enabled=false;
			f.droptable.checked=false;
		}
		else
		{
			f.droptable.enabled=true;
		}
	}
</script>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
<td valign="top" align="left" width="98%">
<?
// setup the title block
$titleBlock = new CTitleBlock( 'Backup database', 'system_admin.gif', $m, "$m.$a" );
$titleBlock->show();
?>
<br>
<table cellspacing="0" cellpadding="2" border="0" width="100%" class="std">
	<form onclick="check_backup_options()" name="frmBackup" action="" target="framebackup" method="post">
	<input type="hidden" name="dosql" value="do_backup" />
	<tr>
		<td align="right" valign="top" nowrap="nowrap">
			<?=$AppUI->_( 'Export' )?>
		</td>
		<td width="85%" nowrap="nowrap">
			<select name="export_what" style="font-size:10px" >
				<option value="1" selected><?=$AppUI->_( "Table structure and data" )?></option>
				<option value="2" ><?=$AppUI->_( "Only table strucure" )?></option>
				<option value="3" ><?=$AppUI->_( "Only data" )?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right" valign="top"  nowrap="nowrap">
			<?=$AppUI->_( 'Extra options' )?>
		</td>
		<td width="85%" nowrap="nowrap">
			<input type="checkbox" name="droptable" checked="checked" /><?=$AppUI->_( "Add 'DROP TABLE' to output-script" )?><br />
		</td>
	</tr>
	<tr>
		<td align="right" valign="top"  nowrap="nowrap">
			<?=$AppUI->_( 'Save as' )?>
		</td>
		<td width="85%" nowrap="nowrap">
			<select name="compress" style="font-size:10px" >
				<option value="1" checked="checked" /><?=$AppUI->_( "Compressed .ZIP file" )?>
				<option value="0" /><?=$AppUI->_( "Plain text file" )?>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			&nbsp;
		</td>
		<td align="right">
			<input name="btnSql" type="button" onClick="doBackup();" value="<?=$AppUI->_( 'Download backup' )?>" class="button"/>
		</td>
	</tr>
	</form>
</table>
<br>
<table cellspacing="0" cellpadding="2" border="0" width="100%" class="std">
    <form action="?m=backup&a=do_backup_filerepository" method="post" target="framebackup">
		<!--input type="hidden" name="dosql" value="do_backup_filerepository" /-->
    <tr>
        <td align="right" valign="top" nowrap="nowrap">
            <?=$AppUI->_( 'Export' )?>
        </td>
        <td width="85%" nowrap="nowrap">
            <b><?=$AppUI->_( 'File Repository' );?></b>
        </td>
    </tr>
    <tr>
        <td>
            &nbsp;
        </td>
        <td align="right">
            <input type="submit" value="<?=$AppUI->_( 'Download Files Backup' )?>" class="button"/>
        </td>
    </tr>
    </form>
</table>
</td></tr>
</table>
<iframe id="framebk" name="framebackup" width="0" height="0" frameborder="0"></iframe>
