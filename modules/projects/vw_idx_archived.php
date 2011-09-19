<?php /* PROJECTS $Id: vw_idx_archived.php,v 1.2 2009-07-27 14:13:29 nnimis Exp $ */
GLOBAL $AppUI, $projects, $company_id,$orderImage,$revertOrder;
$df = $AppUI->getPref('SHDATEFORMAT');
?>

<table width="100%" border="0" cellpadding="3" cellspacing="0" class="">
<tr class="tableHeaderGral">
	<td align="right" width="65" nowrap="nowrap" class="tableHeaderText">&nbsp;<?php echo $AppUI->_('sort by');?>:&nbsp;</td>
	<td nowrap="nowrap" class="tableHeaderText">
		<?php if($_GET["orderby"] == "project_name") echo $orderImage?>
		<a href="?m=projects&orderby=project_name<?=$revertOrder?>" class=""><?php echo $AppUI->_('Name');?></a>
	</td>
	<td nowrap="nowrap" class="tableHeaderText">
		<?php if($_GET["orderby"] == "cpn.company_name") echo $orderImage?>
		<a href="?m=projects&orderby=cpn.company_name<?=$revertOrder?>" class=""><?php echo $AppUI->_('Company/Channel');?></a>
	</td>
	<td nowrap="nowrap" class="tableHeaderText">
		<?php if($_GET["orderby"] == "user_username") echo $orderImage?>
		<a href="?m=projects&orderby=user_username<?=$revertOrder?>" class=""><?php echo $AppUI->_('Owner');?></a>
	</td>
	<td nowrap="nowrap" class="tableHeaderText">
		<?php if($_GET["orderby"] == "total_tasks") echo $orderImage?>
		<a href="?m=projects&orderby=total_tasks<?=$revertOrder?>" class=""><?php echo $AppUI->_('Tasks');?></a>
	</td>
	<td nowrap="nowrap" class="tableHeaderText">
		<?php if($_GET["orderby"] == "project_end_date") echo $orderImage?>
		<a href="?m=projects&orderby=project_end_date<?=$revertOrder?>" class=""><?php echo $AppUI->_('Finished');?></a>
	</td>
</tr>

<?php
global $sql;
$sql = str_replace("project_status = project_status", "project_active < 1", $sql);
$dp = new DataPager($sql, "arc");
$dp->showPageLinks = true;
$projects = $dp->getResults();
$rn = $dp->num_result;
$pager_links = $dp->RenderNav();

$CR = "\n";
$CT = "\n\t";
$none = true;
foreach ($projects as $row) {
	if ($row["project_active"] < 1) {
		$none = false;
		$obj = new CProject();
		$obj->load($row["project_id"]);
		$canEdit = $obj->canEdit();
		$canReadEcVAlues = $obj->canReadEcValues();
		$end_date = intval( @$row["project_actual_end_date"] ) ? new CDate( $row["project_actual_end_date"] ) : null;
		$s = '<tr>';
		$s	.= '<td align="center" style="border: outset #eeeeee 2px;background-color:#'.$row["project_color_identifier"].'">';
		$s	.= $CT . '<font color="' . bestColor( $row["project_color_identifier"] ) . '">';
		$s	.="<span onmouseover=\"tooltipLink('<pre style=&quot;margin:0px;&quot;>".$AppUI->_('Completed Tasks')."</pre>', '');\" onmouseout=\"tooltipClose();\">".$AppUI->_('CT').":".$row["project_percent_completed_tasks"]."</span><BR>\n";
		//$s	.="<span onmouseover=\"tooltipLink('<pre style=&quot;margin:0px;&quot;>".$AppUI->_('Completed Duration')."</pre>', '');\" onmouseout=\"tooltipClose();\">".$AppUI->_('CD').":". sprintf( "%.1f%%", $row["project_percent_completed_duration"] )."</span><BR>\n";
		$s	.="<span onmouseover=\"tooltipLink('<pre style=&quot;margin:0px;&quot;>".$AppUI->_('Completed Duration')."</pre>', '');\" onmouseout=\"tooltipClose();\">".$AppUI->_('CD').":</span>".pdc ($row["project_id"])."<BR>\n";
		if($canReadEcVAlues) {
				$s	.= "<span onmouseover=\"tooltipLink('<pre style=&quot;margin:0px;&quot;>".$AppUI->_('Estimated Completed Work')."</pre>', '');\" onmouseout=\"tooltipClose();\">".$AppUI->_('ECW').":". sprintf( "%.1f%%", project_percent_completed_work($row["project_id"]) )."</span><BR>\n";
				$s	.= "<span onmouseover=\"tooltipLink('<pre style=&quot;margin:0px;&quot;>".$AppUI->_('Estimated Actual Cost')."</pre>', '');\" onmouseout=\"tooltipClose();\">".$AppUI->_('EAC').":". sprintf( "%.1f%%", $row["project_percent_completed_oozed_cost"])."</span><BR>\n";
		}

		$s .=  '</font>';
		$s .= $CR . '</td>';
		$s .= $CR . '<td width="60%">';
		$s .= $CT . '<a href="?m=projects&a=view&project_id=' . $row["project_id"] . '" title="' . htmlspecialchars( $row["project_description"], ENT_QUOTES ) . '">' . htmlspecialchars( $row["project_name"], ENT_QUOTES ) . '</a>';
		$s .= $CR . '</td>';
		$s .= $CR . '<td width="100%">';
		$s .= $CT .	$row['company_name'];
		IF ($row['channel']!='') $s .=" / ". $row['channel'];
		$s .= $CR . '</td>';
		$s .= $CR . '<td nowrap="nowrap">' . htmlspecialchars( $row["user_username"], ENT_QUOTES ) . '</td>';
		$s .= $CR . '<td align="center" nowrap="nowrap">';
		$s .= $CT . $row["total_tasks"];
		$s .= $CR . '</td>';
		$s .= $CR . '<td align="right" nowrap="nowrap">';
		$s .= $CT . ($end_date ? $end_date->format( $df ) : '-');
		$s .= $CR . '</td>';
		$s .= $CR . '</tr>';
		echo $s;
        echo "<tr class=\"tableRowLineCell\"><td colspan=\"6\"></td></tr>";
	}
}
if ($none) {
	echo $CR . '<tr><td colspan="6">' . $AppUI->_( 'No projects available' ) . '</td></tr>';
}
?>
<tr>
	<td colspan="6">&nbsp;</td>
</tr>
</table>
<?php 
echo "
<table border='0' width='100%' cellspacing='0' cellpadding='1'>
<tr bgcolor=#E9E9E9>
	<td align='center'>$pager_links</td>
	<td width='35'>
		<a href='rss_recursos.php?p=psa' target='_blank'>
			<img src='./images/icons/rss_enabled.bmp' width='15'  border='0' alt='RSS'>
		</a>
	</td>	
</tr>
<tr>
		<td height=1 colspan=5 bgcolor=#E9E9E9></td>
</tr>
</table>"; 
?>