<?php

GLOBAL $AppUI, $canEdit, $tpl_stub, $tpl_where, $tpl_orderby;

$sql = "SELECT * FROM articlesections order by name";

$sections = db_loadList( $sql );

?>

<table cellpadding="2" cellspacing="0" border="0" width="100%" class="">
<tr class="tableHeaderGral">
	<td width="45" align="right">
		&nbsp;
	</td>
	<th>
		<?php echo $AppUI->_('Section name');?>
	</th>
	<th>
		<?php echo $AppUI->_('E-Mail');?>
	</th>	
</tr>
<?php 
foreach ($sections as $row) {
?>
<tr>
	<td align="right" nowrap="nowrap" width=45 >
<?php if ($canEdit) { ?>
		<table align=center width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td>
				<a href="./index.php?m=articles&a=addeditsection&articlesection_id=<?php echo $row["articlesection_id"];?>" title="<?php echo $AppUI->_('edit');?>">
				<?php echo dPshowImage( './images/icons/edit_small.gif', 20, 20, '' ); ?>
				</a>
			</td>
			<td>
				<a href="javascript:delSection(<?php echo $row["articlesection_id"];?>, '<?php echo $row["name"];?>')" title="<?php echo $AppUI->_('delete');?>"><?php echo dPshowImage( './images/icons/trash_small.gif', 16, 16, '' ); ?></a>
			</td>
		</tr>
		</table>
<?php } ?>
	</td>
	<td align="center">
		<a href="./index.php?m=articles&a=addeditsection&articlesection_id=<?php echo $row["articlesection_id"];?>"><?php echo $row["name"];?></a>
		
	</td>
	<td align="center">
		<?php echo $row["articlesection_email"];?>
	</td>	
</tr>
<tr class="tableRowLineCell"><td colspan="3"></td></tr>
<?php }?>

</table>
