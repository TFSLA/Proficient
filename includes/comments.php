<?php
	global $AppUI;
	
	/*
	TYPES
	1 = projects;
	2 = files evaluation;
	*/

	$sql="SELECT user_type FROM users WHERE user_id='".$AppUI->user_id."'";
	
	$rc=db_exec($sql);

	$vec1=db_fetch_array($rc);
	
	$notes = "<table width='98%' border='0' align='right' >";
	$notes .= "<tr><td>";
	$notes .= "<table width='100%' border='0' bgcolor='#F9F9F9'>\n";
	
	$sql="SELECT
				comment_id,
				comment_user_id,
				user_username,
				comment_note,
				comment_item_id,
				comment_item_type,
				comment_date
			FROM comments
			INNER JOIN users ON (comment_user_id = user_id)
			WHERE comment_item_id = $item
			AND comment_item_type = $type
			ORDER BY comment_date DESC;";
	
	$rc=db_exec($sql);
	
	$edit = $AppUI->_('Edit');
	$delete = $AppUI->_('Clear');
	
	$i=0;
	
	while ($vec=db_fetch_array($rc))
	{
		$datecomment = new CDate($vec["comment_date"]);
			
		$notes .= "<tr>\n
					<td style='background:#F7F7F7' valign='top' width='150'>
						<b>".$vec['user_username']."</b><br>
						".$datecomment->format($AppUI->user_prefs['SHDATEFORMAT'].' '.$AppUI->user_prefs['TIMEFORMAT']);
		if ($i==0 AND $vec['comment_user_id'] == $AppUI->user_id) $notes .="<br>&nbsp;<a href='javascript: //' onclick=\"xajax_editcommentcompanies($rows, $item, '".$vec['comment_id']."', '".$vec['comment_item_type']."');\">[$edit]</a>";
		if (!($i==0 AND $vec['comment_user_id'] == $AppUI->user_id))$notes .="<br>";
		if ($vec1['user_type'] == 1 OR ($i==0 AND $vec['comment_user_id'] == $AppUI->user_id)) $notes .="&nbsp;<a href='javascript: //' onclick=\"xajax_delcommentcompanies($rows, $item, '".$vec['comment_id']."', '".$vec['comment_item_type']."');\">[$delete]</a>";
		$notes .= "<br></td>\n
							<td style='background:#F7F7F7' valign='top'>".nl2br($vec['comment_note'])."</td>\n
					  </tr>";
		$i++;
	}
	$notes .= "</table>\n";
	$notes .= "</td></tr>";
	$notes .= "</table>\n";
?>