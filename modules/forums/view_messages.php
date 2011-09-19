<?php  /* FORUMS $Id: view_messages.php,v 1.1 2009-05-19 21:15:43 pkerestezachi Exp $ */
$AppUI->savePlace();

$sql = "
SELECT forum_messages.*,
	user_first_name, user_last_name, user_email, user_username,
	forum_moderated
FROM forum_messages, forums
LEFT JOIN users ON message_author = users.user_id
WHERE forum_id = message_forum
	AND (message_id = $message_id OR message_parent = $message_id)" .
  ( @$dPconfig['forum_descendent_order'] ? " ORDER BY message_date DESC" : "" );

//echo "<pre>$sql</pre>";
$messages = db_loadList( $sql );

$crumbs = array();
$crumbs["?m=forums"] = "forums list";
$crumbs["?m=forums&a=viewer&forum_id=$forum_id"] = "topics for this forum";
?>
<script language="javascript">
function delIt(id){
	var form = document.messageForm;
	if (confirm( "<?php echo $AppUI->_('forumsDelete');?>" )) {
		form.del.value = 1;
		form.message_id.value = id;
		form.submit();
	}
}
</script>
<br>
<table border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td>
            <table border="0" cellpadding="0" cellspacing="0" background="images/common/back_botones-01.gif">
                <tr>
                  <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
                  <td><img src="images/common/cuadradito_naranja.gif" width="9" height="9">
                  <a href="index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&message_parent=<?php echo $message_id;?>&post_message=1"><?php echo $AppUI->_('Post Reply'); ?></a><span class="boldtext"> </span></td>
                  <td width="6"> <div align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></div></td>
                </tr>
            </table>
        </td>
     </tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" width="98%" class="">
<!-- <form name="messageForm" method="POST" action="?m=forums&a=viewposts&forum_id=<?php echo $row['message_forum'];?>"> -->
<form name="messageForm" method="POST" action="?m=forums&forum_id=<?php echo $row['message_forum'];?>">
	<input type="hidden" name="dosql" value="do_post_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="message_id" value="0" />
</form>
<tr class="tableHeaderGral">
	<th nowrap><?php echo $AppUI->_('Author');?>:</th>
	<th width="100%"><?php echo $AppUI->_('Message');?>:</th>
</tr>

<?php 
$x = false;

$date = new CDate();

foreach ($messages as $row) {
	$date = intval( $row["message_date"] ) ? new CDate( $row["message_date"] ) : null;

	$s = '';
	$style = $x ? 'background-color:#eeeeee' : '';

	$s .= "<tr>";

	$s .= '<td valign="top" style="'.$style.'" nowrap="nowrap">';
	$s .= '<a href="mailto:'.$row["user_email"].'">';
	$s .= '<font size="2">'.$row["user_first_name"].' '.$row["user_last_name"].'</font></a></td>';
	$s .= '<td valign="top" style="'.$style.'">';
	$s .= '<font size="2"><strong>'.$row["message_title"].'</strong><hr size=1>';
	$s .= str_replace( chr(13), "&nbsp;<br />", $row["message_body"] );
	$s .= '</font></td>';

	$s .= '</tr><tr>';

	$s .= '<td valign="top" style="'.$style.'" nowrap="nowrap">';
	$s .= '<img src="./images/icons/posticon.gif" alt="date posted" border="0" width="14" height="11">'.$date->format( "$df $tf" ).'</td>';
	$s .= '<td valign="top" align="right" style="'.$style.'">';
	
	
	if ($canEdit && $AppUI->user_id == $row['forum_moderated']) {
		$s .= '<table cellspacing="0" cellpadding="0" border="0"><tr>';
	// edit message
		$s .= '<td><a href="./index.php?m=forums&a=viewer&post_message=1&forum_id='.$row["message_forum"].'&message_parent='.$row["message_parent"].'&message_id='.$row["message_id"].'" title="'.$AppUI->_( 'Edit' ).' '.$AppUI->_( 'Message' ).'">';
		$s .= dPshowImage( './images/icons/edit_small.gif', '20', '20' );
		$s .= '</td><td>';
	// delete message
		$s .= '<a href="javascript:delIt('.$row["message_id"].')" title="'.$AppUI->_( 'delete' ).'">';
		$s .= dPshowImage( './images/icons/trash_small.gif', '20', '20' );
		$s .= '</a>';
		$s .= '</td></tr></table>';

	}
	$s .= '</td>';

	$s .= '</tr>';

	echo $s;
	$x = !$x;
}
?>
</table>

