<?php /* FORUMS $Id: index.php,v 1.4 2009-06-19 18:33:56 pkerestezachi Exp $ */

if (getDenyRead( $m ))
	$AppUI->redirect( "m=public&a=access_denied" );

$AppUI->savePlace();
if (!class_exists("CProject")){
	require_once( $AppUI->getModuleClass( 'projects' ) );
}

$df = $AppUI->getPref( 'SHDATEFORMAT' );
$tf = $AppUI->getPref( 'TIMEFORMAT' );

$f = dPgetParam( $_POST, 'f', 0 );


// get read denied projects
/*
$deny = array();
$sql = "
SELECT project_id
FROM projects, permissions
WHERE permission_user = $AppUI->user_id
	AND permission_grant_on = 'projects'
	AND permission_item = project_id
	AND permission_value = 0
";
$deny1 = db_loadColumn( $sql );
*/

//get allowed projects
$objPrj = new CProject();
$allowedprjs = $objPrj->getAllowedRecords($AppUI->user_id);


// get read denied forums
$deny = array();
$sql = "
SELECT forum_id
FROM forums, permissions
WHERE permission_user = $AppUI->user_id
	AND permission_grant_on = 'forums'
	AND permission_item = forum_id
	AND permission_value = 0
";
$deny2 = db_loadColumn( $sql );

$max_msg_length = 30;
$select = "
	forum_id, forum_project, forum_description, forum_owner, forum_name, forum_moderated,
	forum_create_date, forum_last_date,
	COUNT(distinct t.message_id) forum_topics, COUNT(distinct r.message_id) forum_replies,
	user_username,
	project_name, project_color_identifier,
	SUBSTRING(l.message_body,1,$max_msg_length) message_body,
	LENGTH(l.message_body) message_length,
	watch_user,
	l.message_parent,
	l.message_id";

$from = " forums ";
$join = "INNER JOIN users ON  user_id = forum_owner";
$join .= "\nLEFT JOIN projects ON projects.project_id = forum_project";
$join .= "\nLEFT JOIN forum_messages t ON t.message_forum = forum_id AND t.message_parent = -1";
$join .= "\nLEFT JOIN forum_messages r ON r.message_forum = forum_id AND r.message_parent > -1";
$join .= "\nLEFT JOIN forum_messages l ON l.message_id = forum_last_id";
$join .= "\nLEFT JOIN forum_watch ON watch_user = $AppUI->user_id AND watch_forum = forum_id";
$join .= ", permissions";

$where = "
	user_id = forum_owner
	AND projects.project_id = forum_project
# filter projects permissions
	AND permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'forums' AND permission_item = -1)
		OR (permission_grant_on = 'forums' AND permission_item = forum_id)
		)"
.(count($allowedprjs) > 0 ? "\nAND forum_project IN (" . implode( ',', array_keys($allowedprjs) ) . ')' : '')
.(count($deny2) > 0 ? "\nAND forum_id NOT IN (" . implode( ',', $deny2 ) . ')' : '')
;

//if (isset($project_id) && $project_id) {
//	$sql.= "\nAND forum_project = $project_id";
//}
switch ($f) {
	case 1:
		$where .= "\nAND project_active=1 AND forum_owner = $AppUI->user_id";
		break;
	case 2:
		$where .= "\nAND project_active=1 AND watch_user IS NOT NULL";
		break;
	case 3:
		$join .= "\nLEFT JOIN project_owners po ON projects.project_id = po.project_id";
		$where .= "\nAND project_active=1 AND ( projects.project_owner = $AppUI->user_id OR po.project_owner = $AppUI->user_id )";
		break;
	case 4:
		$where .= "\nAND project_active=1 AND project_company = $AppUI->user_company";
		break;
	case 5:
		$where .= "\nAND project_active=0";
		break;
	default:
		$where .= "\nAND project_active=1";
		break;
}
$sql = "
SELECT DISTINCT $select 
FROM $from $join 
WHERE $where 
GROUP BY forum_id
ORDER BY forum_project, forum_name";

//echo "<pre>$sql</pre>";
$forums = db_loadList( $sql );
//echo "<pre>$sql</pre>".db_error();##

// setup the title block
$titleBlock = new CTitleBlock( 'Forums', 'forums.gif', $m, "colaboration.index" );
$titleBlock->addCell(
	arraySelect( $filters, 'f', 'size="1" class="text" onChange="document.forum_filter.submit();"', $f , true ), '',
	'<form name="forum_filter" action="?m=forums" method="post">', '</form>'
);
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new forum').'">', '',
		'<form action="?m=forums&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->show();
?>

<table width="100%" cellspacing="0" cellpadding="2" border="0" class="">
<form name="watcher" action="./index.php?m=forums&f=<?php echo $f;?>" method="post">
<tr class="tableHeaderGral">
	<th nowrap="nowrap">&nbsp;</th>
	<th nowrap="nowrap" width="25"><?php echo $AppUI->_( 'Watch' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Forum Name' );?></th>
	<th nowrap="nowrap" width="50" align="center"><?php echo $AppUI->_( 'Topics' );?></th>
	<th nowrap="nowrap" width="50" align="center"><?php echo $AppUI->_( 'Replies' );?></th>
	<th nowrap="nowrap" width="200"><?php echo $AppUI->_( 'Last Post Info' );?></th>
</tr>
<?php
$p ="";
$now = new CDate();
foreach ($forums as $row) {
	$message_date = intval( $row['forum_last_date'] ) ? new CDate( $row['forum_last_date'] ) : null;

	if($p != $row["forum_project"]) {
		$create_date = intval( $row['forum_create_date'] ) ? new CDate( $row['forum_create_date'] ) : null;
?>
<tr>
	<td colspan="6" style="background-color:#<?php echo $row["project_color_identifier"];?>">
		<a href="?m=projects&a=view&project_id=<?php echo $row["forum_project"];?>">
			<font color=<?php echo bestColor( $row["project_color_identifier"] );?>>
			<strong><?php echo $row["project_name"];?></strong>
			</font>
		</a>
	</td>
</tr>
	<?php
		$p = $row["forum_project"];
	}?>
<tr>
	<td nowrap="nowrap" align="center">
	<?php if ($row["forum_owner"] == $AppUI->user_id) { ?>
		<a href="?m=forums&a=addedit&forum_id=<?php echo $row["forum_id"];?>" title="<?php echo $AppUI->_('edit');?>">
		<?php echo dPshowImage( './images/icons/edit_small.gif', 20, 20, '' );?>
		</a>
	<?php } ?>
	</td>

	<td nowrap="nowrap" align="center">
		<input type="checkbox" name="forum_<?php echo $row['forum_id'];?>" <?php echo $row['watch_user'] ? 'checked' : '';?> />
	</td>

	<td>
		<span style="font-size:10pt;font-weight:bold">
			<a href="?m=forums&a=viewer&forum_id=<?php echo $row["forum_id"];?>"><?php echo $row["forum_name"];?></a>
		</span>
		<br /><?php echo $row["forum_description"];?>
		<br /><font color="#777777"><?php echo $AppUI->_( 'Owner' ).' '.$row["user_username"];?>,
		<?php echo $AppUI->_( 'Started' ).' '.$create_date->format( $df );?>
		</font>
	</td>
	<td nowrap="nowrap" align="center"><?php echo $row["forum_topics"];?></td>
	<td nowrap="nowrap" align="center"><?php echo $row["forum_replies"];?></td>
	<td width="225">
<?php
	if ($message_date !== null) {
		echo $message_date->format( "$df $tf" );

		$last = new Date_Span();
		$last->setFromDateDiff( $now, $message_date );

		echo '<br /><font color=#999966>(' . $AppUI->_('Last post').' ';
		printf( "%.1f", $last->format( "%d" ) );
		echo ' '.$AppUI->_('days ago') . ') </font>';

		$id = $row['message_parent'] < 0 ? $row['message_id'] : $row['message_parent'];

		echo '<br />&gt;&nbsp;<a href="?m=forums&a=viewer&forum_id='.$row['forum_id'].'&message_id='.$id.'">';
		echo '<font color=#777777>'.$row['message_body'];
		echo $row['message_length'] > $max_msg_length ? '...' : '';
		echo '</font></a>';
	} else {
		echo $AppUI->_('No posts');
	}
?>
	</td>
<tr class="tableRowLineCell"><td colspan="6"></td></tr>
</tr>

<?php } ?>
</table>

<table width="100%" cellspacing="1" cellpadding="0" border="0" class="">
	<input type="hidden" name="dosql" value="do_watch_forum" />
	<input type="hidden" name="watch" value="forum" />
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td align="left">
		<input type="submit" class="button" value="<?php echo $AppUI->_( 'update watches' );?>" />
	</td>
</tr>
<tr>
    <td>&nbsp;</td>
</tr>
</form>
</table>
