<?php
$sql="SELECT user_type FROM users WHERE user_id='".$_SESSION['AppUI']->user_id."'";
$rc=db_exec($sql);
$vec1=db_fetch_array($rc);
	IF ($_SESSION['AppUI']->user_prefs['LOCALE']=='en'){
		$edit='Edit';
		$del='Delete';
	}
	ELSE {
		$edit='Editar';
		$del='Borrar';
	}
	$notes = "<table width='98%' border='0' align='right' >";
	$notes .= "<tr><td>";
	$notes .= "<table width='100%' border='0' bgcolor='#F9F9F9'>\n";
	$sql="SELECT
				know_base_note_id,
				k.user_id,
				user_username,
				know_base_note,
				know_base_item_id,
				know_base_date AS date,
				date_format( know_base_date, '%d/%m/%Y %H:%i' )  AS know_base_date
			FROM know_base_note AS k
			INNER JOIN users AS u
			 ON (k.user_id=u.user_id)
			WHERE
					know_base_type=$type AND
					know_base_item_id=$item
			ORDER BY date DESC;";
	$rc=db_exec($sql);
	$i=0;
	//$notes .="<tr><td colspan='2'>$sql</td></tr>";
	WHILE ($vec=db_fetch_array($rc)){
		$notes .= "<tr>\n
					<td style='background:#F7F7F7' valign='top' width='150'>
						<b>".$vec['user_username']."</b><br>
						".$vec['know_base_date'];
		if ($i==0 AND $vec['user_id']==$_SESSION['AppUI']->user_id) $notes .="<br>&nbsp;<a href='javascript: //' onclick=\"xajax_edit($rows, $item, '".$vec['know_base_note_id']."', $type);\">[$edit]</a>";
		if (!($i==0 AND $vec['user_id']==$_SESSION['AppUI']->user_id))$notes .="<br>";
		if ($vec1['user_type']==1 OR ($i==0 AND $vec['user_id']==$_SESSION['AppUI']->user_id)) $notes .="&nbsp;<a href='javascript: //' onclick=\"xajax_delnote($rows, $item, '".$vec['know_base_note_id']."', $type);\">[$del]</a>";
		$notes .= "<br></td>\n
							<td style='background:#F7F7F7' valign='top'>".nl2br($vec['know_base_note'])."</td>\n
					  </tr>";
		$i++;
	}
	$notes .= "</table>\n";
	$notes .= "</td></tr>";
	$notes .= "</table>\n";
?>