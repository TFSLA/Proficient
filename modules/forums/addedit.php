<?php /* FORUMS $Id: addedit.php,v 1.1 2009-05-19 21:15:43 pkerestezachi Exp $ */
// Add / Edit forum
$forum_id = intval( dPgetParam( $_GET, 'forum_id', 0 ) );

//Pull forum information
$sql = "SELECT * FROM forums WHERE forums.forum_id = $forum_id";
$res = db_exec( $sql );
echo db_error();
$forum_info = db_fetch_assoc( $res );

$status = isset( $forum_info["forum_status"] ) ? $forum_info["forum_status"] : -1;

//Pull project Information
$sql = "SELECT project_id, project_name FROM projects WHERE project_active <> 0 ORDER BY project_name";
$projects = array( '0' => '' ) + db_loadHashList( $sql );
echo db_error();

//Pull user Information
$sql = "SELECT user_id, CONCAT_WS(' ', user_first_name, user_last_name) FROM users WHERE user_type <> 5 ORDER BY user_username";
$users = array( '0' => '' ) + db_loadHashList( $sql );
echo db_error();

// setup the title block
$ttl = $forum_id > 0 ? "Edit Forum" : "Add Forum";
$titleBlock = new CTitleBlock( $ttl, 'forums.gif', $m, "colaboration.index" );
$titleBlock->addCrumb( "?m=forums", "forums list" );
if ($canDelete) {
	$titleBlock->addCrumbRight(
		'<a href="javascript:delIt()">' . $AppUI->_('delete forum')
			. '&nbsp;<img align="absmiddle" src="' . dPfindImage( 'trash_small.gif', $m ) . '"  alt="" border="0" /></a>'
	);
}
$titleBlock->show();
?>
<script language="javascript">
function submitIt(){
	var form = document.changeforum;
	if(form.forum_name.value.length < 1) {
		alert("<?php echo $AppUI->_('forumName');?>");
		form.forum_name.focus();
	} else if(form.forum_project.selectedIndex < 1) {
		alert("<?php echo $AppUI->_('forumSelectProject');?>");
		form.forum_project.focus();
	} else if(form.forum_owner.selectedIndex < 1) {
		alert("<?php echo $AppUI->_('forumSelectOwner');?>");
		form.forum_owner.focus();
	} else {
		form.submit();
	}
}

function delIt(){
	var form = document.changeforum;
	if (confirm( "<?php echo $AppUI->_('forumDeleteForum');?>" )) {
		form.del.value="<?php echo $forum_id;?>";
		form.submit();
	}
}
</script>
<br>
<table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_04.gif">
            <tr>
                <td align="left"><img src="images/common/lado.gif" width="1" height="17"></td>
                <td class="boldtext">
                    <?php
                      echo $AppUI->_( $forum_id ? 'Edit' : 'Add' ).' '.$AppUI->_( 'Forum' );
                    ?>
                </td>
                <td align="right"><img src="images/common/lado.gif" width="1" height="17"></td>
            </tr>
            <tr bgcolor="#666666">
                <td colspan="3"></td>
            </tr>
</table>
<table cellspacing="3" cellpadding="0" border="0" width="100%" class="std">
<form name="changeforum" action="?m=forums" method="post">
	<input type="hidden" name="dosql" value="do_forum_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="forum_unique_update" value="<?php echo uniqid("");?>" />
	<input type="hidden" name="forum_id" value="<?php echo $forum_id;?>" />

<!--tr height="20">
	<th valign="top" colspan="3">
		<strong><?php
		echo $AppUI->_( $forum_id ? 'Edit' : 'Add' ).' '.$AppUI->_( 'Forum' );
		?></strong>
	</th>
</tr-->
<tr>
	<td valign="top" width="50%">
		<strong><?php echo $AppUI->_('Details');?></strong>
		<table cellspacing="1" cellpadding="2" width="100%">
		<tr>
			<td align="right" width="100"><?php echo $AppUI->_('Forum Name');?>:</td>
			<td>
				<input type="text" class="text" size=25 name="forum_name" value="<?php echo @$forum_info["forum_name"];?>" maxlength="50" style="width:200px;" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Related Project');?></td>
			<td>
		<?php
			echo arraySelect( $projects, 'forum_project', 'size="1" class="text"', $forum_info['forum_project'] );
		?>
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Owner');?>:</td>
			<td>
		<?php
			echo arraySelect( $users, 'forum_owner', 'size="1" class="text"', $forum_info['forum_owner'] ? $forum_info['forum_owner'] : $AppUI->user_id );
		?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap valign="top"><? echo $AppUI->_('Status')?>:</td>
			<td valign="top">
				<input type="radio" value="-1" <?php if($status ==-1) echo " checked";?> name="forum_status" /><?php echo $AppUI->_('open for posting');?><br />
				<input type="radio" value="1" <?php if($status == 1) echo " checked";?> name="forum_status" /><?php echo $AppUI->_('read-only');?><br />
				<input type="radio" value="0" <?php if($status ==0) echo " checked";?> name="forum_status" /><?php echo $AppUI->_('closed');?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Moderator');?>:</td>
			<td>
		<?php
			echo arraySelect( $users, 'forum_moderated', 'size="1" class="text"', $forum_info['forum_moderated'] );
		?>
			</td>
		</tr>
		<?php if ($forum_id) { ?>
		<tr>
			<td align="right"><?php echo $AppUI->_('Created On');?></td>
			<td bgcolor="#ffffff"><?php echo @$forum_info["forum_create_date"];?></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Last Post');?>:</td>
			<td bgcolor="#ffffff"><?php echo @$forum_info["forum_last_date"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Message Count');?>:</td>
			<td bgcolor="#ffffff"><?php echo @$forum_info["forum_message_count"];?></td>
		</tr>
		<?php } ?>
		</table>
	</td>
	<td valign="top" width="50%">
		<strong><?php echo $AppUI->_('Description');?></strong><br />
		<textarea class="textarea" name="forum_description" style="height:150px;"><?php echo @$forum_info["forum_description"];?></textarea>
	</td>
</tr>


<tr>
	<td align="left">
		<input type="button" value="<?php echo $AppUI->_('back');?>" class=button onclick="javascript:window.location='./index.php?m=forums';" />
	</td>
	<td align="right" colspan="2"><?php
		if ($AppUI->user_id == $forum_info["forum_owner"] || $forum_id ==0) {
			echo '<input type="button" value="'.$AppUI->_('submit').'" class=button onclick="submitIt()" />';
		}?></td>
</tr>
</form>
</table>