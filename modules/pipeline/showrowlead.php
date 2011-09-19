<?php

function showrowlead( &$a ) {
	global $AppUI, $delegator_id, $dialog, $canEdit, $df, $mod_id, $permisos;
	
	$s = '';
	$s .= '<td>';
		
	if ($delegator_id == $AppUI->user_id && $AppUI->user_id != $a["lead_owner"])
	{
		$userDelegator = new CUser();
		$userDelegator->user_id = $a["lead_owner"];
		$permisos = $userDelegator->getDelegatePermission( $AppUI->user_id, $mod_id );	
	
		$canEditR = $permisos == "EDITOR";
	}
	else
	{
		$canEditR = $canEdit || ( $permisos == "EDITOR" && $a["lead_owner"] == $delegator_id );
		$canEditR = $canEditR || ( $permisos == "AUTHOR" && $a["lead_owner"] == $delegator_id && $a["lead_creator"] == $AppUI->user_id );
		$canEditR = $canEditR || ( $AppUI->user_type == 1 );
	}
	
	if ( $canEditR )
	{
		$s .= "<a href=\"./index.php?m=pipeline&a=addedit&lead_id=".$a['id']."&delegator_id=".$delegator_id."&dialog=".$dialog.($listOP == 1 || $delegator_id == $AppUI->user_id ? '&listOP=1' : '')."\">";
		$s .= "<img src=\"./images/icons/edit_small.gif\" alt=\"". $AppUI->_('Edit Lead')."\" border=\"0\" width=\"20\" height=\"20\"></a>";
		$s .= '<a href="javascript:delLead('. $a["id"] .', \''. $a["accountname"] .'\')"><img src="images/icons/trash_small.gif" border="0" alt="' . $AppUI->_('delete') . '"></a>';
	}
	else
	{
		$s .= "&nbsp;";
	}
	$df = $AppUI->getPref('SHDATEFORMAT');	
	$d = new CDate( $a["closingdate"] );
	$s .= '</td>';
	$s .= '<td valign="middle">'.$a["opportunitycode"].'</td>';	
	$s .= '<td valign="middle">'.$a["_accountmanagername"].'</td>';	
	$s .= '<td valign="middle">';
	
	if($a["lead_owner"] != $AppUI->user_id)
		$s .= '<a href="./index.php?m=pipeline&a=view&lead_id='.$a["id"].'&delegator_id='.$delegator_id.'&dialog='.$dialog.'">'.$a["accountname"].' ('.$a["_leadOwner"].')</a>';
	else
		$s .= '<a href="./index.php?m=pipeline&a=view&lead_id='.$a["id"].'&delegator_id='.$delegator_id.'&dialog='.$dialog.'">'.$a["accountname"].'</a>';
		
	$s .= '</td>';	
	$s .= '<td valign="middle">'.$a["projecttype"].'</td>';
	$s .= '<td valign="middle">'.$a["totalincome"].'</td>';
	$s .= '<td valign="middle">&nbsp;&nbsp;'.$a["probability"].'%</td>';
	$s .= '<td valign="middle">'.$d->format( $df ).'</td>';

	if($a["lead_owner"] != $AppUI->user_id)
		echo "<tr class=\"delegatorpipeline\">$s</tr>";
	else
		echo "<tr>$s</tr>";
	
   	echo "<tr class=\"tableRowLineCell\"><td colspan=\"8\"></td></tr>";
}
?>