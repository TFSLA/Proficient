<?php /* COMPANIES $Id: vw_evaluations.php,v 1.4 2009-07-29 18:58:56 nnimis Exp $ */
	GLOBAL $AppUI, $company_id;
	
	include_once('./modules/public/satisfaction_suppliers_customers.php');
	
	$rowsCount = 0;
	
	$arrAssessmentSatisfactionTypes = CProject::getAssessmentSatisfactionTypes();
	$arrAssessmentSatisfactionTypes = array_merge(array('0'=>''), $arrAssessmentSatisfactionTypes);
	
	
	$s = '<tr><td colspan="5"><br/><form method="post" action="?m=companies" enctype="multipart/form-data">';
	$s .= '<input class="text" size="40" type="file" name="fileEvaluation" />';
	$s .= '<input type="hidden" name="dosql" value="do_evaluation_file" />';
	$s .= '<input type="hidden" name="company_id" value="'.$company_id.'" />';
	$s .= '&nbsp;<input type="submit" class="buttonbig" type="submit" value="'.$AppUI->_('upload file').'" />';
	$s .= arraySelect( $arrAssessmentSatisfactionTypes, 'combo_level_customer_satisfaction', 'style="width:200px;" class="text"', null, false, false, null);
	$s .= '</form></td></tr>';
	$s .= '</table>';
	$s .= '<table cellpadding="2" cellspacing="0" border="0" width="100%" >';
	$s .= '<tr class="tableHeaderGral">';
	$s .= '<th>&nbsp;</td>';
	$s .= '<th>&nbsp;</td>';
	$s .= '<th align="left">'.$AppUI->_( 'Name' ).'</td>';
	$s .= '<th align="center">'.$AppUI->_( 'Level of Customer Satisfaction' ).'</td>';
	$s .= '<th align="center">'.$AppUI->_( 'Level of Satisfaction as Canal' ).'</td>';
	$s .= '</tr>';	

	
	if ($rows = getSatisfactionLevelProjects($company_id))
	{		
		/*
		1 = projects;
		2 = companies files;
		*/
		$type = 1;

		foreach ($rows as $row)
		{
			$sql = "SELECT COUNT(*) FROM comments";
			$sql .= " WHERE comment_item_id = ".$row["project_id"];
			$sql .= " AND comment_item_type = ".$type;
			
			$countComment = db_loadColumn($sql);
			
			$s .= '<tr class="tableRowLineCell"><td colspan="5"><br/></td></tr>';

			$s .= '<tr>';
			$s .= '<td width="1">';
			$s .= '	<a name="#row_'.$rowsCount.'"></a>';
			$s .= '	<a href="javascript: //" onclick="open_rows=openclose(open_rows, '.$rowsCount.','.$row["project_id"].', '.$type.');">';
			$s .= '	('.$countComment[0].')';
			$s .= '	</a>';
			$s .= '</td>';

			$s .= '<td width="40">';
			$s .= '	<a name="#row_'.$rowsCount.'"></a>';
			$s .= '	<a href="javascript: //" onclick="open_rows = openclose_edit(open_rows, '.$rowsCount.', '.$row["project_id"].', '.$type.');">';
			$s .= '	<img src="./images/icons/comment.gif" width="20" height="20" border="0" alt="'.$AppUI->_('New Comment').'">';
			$s .= '	</a>';
			$s .= '</td>';

			$s .= '<td width="20%"><a href="?m=projects&a=view&project_id='.$row["project_id"].'">'.$row["project_name"].'</a></td>';
			$s .= '<td width="30%">&nbsp;</td>';
			$s .= '<td width="30%">&nbsp;</td>';
			$s .= '</tr>';

			$s .= '<tr><td colspan="8"><span id="new_'.$rowsCount.'"></span></td></tr>';
			$s .= '<tr><td colspan="8"><span id="'.$rowsCount.'"></span></td></tr>';
			
			if($row["typecr"] == '1' || $row["typecr"] != '0.50')
				$rowsCustomerSatisfaction = getSatisfactionLevels($company_id, $row["project_id"], 1);
			else
				$rowsCustomerSatisfaction = null;
				
			if($row["typecr"] == '0.50' || $row["typecr"] != '1')
				$rowsCanalSatisfaction = getSatisfactionLevels($company_id, $row["project_id"], 2);
			else
				$rowsCanalSatisfaction = null;

			$rowsSatisfaction = count($rowsCustomerSatisfaction) > count($rowsCanalSatisfaction) ? $rowsCustomerSatisfaction : $rowsCanalSatisfaction;

			$firstItem = true;

			for ($i=0;$i<count($rowsSatisfaction);$i++)
			{
				$s .= '<tr>';

				$s .= '<td width="1%">&nbsp;</td>';
				$s .= '<td width="1%">&nbsp;</td>';
				$s .= '<td width="1%">&nbsp;</td>';

				for($t=1;$t<=2;$t++)
				{
					if($t == 1)
						$rowsSatisfactionLoop = $rowsCustomerSatisfaction;
					else
						$rowsSatisfactionLoop = $rowsCanalSatisfaction;

					if($i < count($rowsSatisfactionLoop))
					{
						$s .= '<td align="center">';

						for($k=0;$k<$rowsSatisfactionLoop[$i]["level_satisfaction"];$k++)
							$s .= '<img src="modules/reviews/images/blue.gif" alt="">';
							
						if ($k == 0)
							$s .= '----';

						$arrUser = CUser::getUsersFullName(array($rowsSatisfactionLoop[$i]["level_satisfaction_user"]));
						$level_satisfaction_date = new CDate($rowsSatisfactionLoop[$i]["level_satisfaction_date"]);
						$s .= ($i==0 ? '<b>' : '').'<br/>('.$arrUser[0]['fullname'].' '.$AppUI->_('on').' '.$level_satisfaction_date->format($AppUI->getPref('SHDATEFORMAT')).')'.($i==0 ? '</b>' : '');

						$s .= '<br/><br/></td>';
					}
					else
						$s .= '<td>&nbsp;</td>';
				}

				$s .= '</tr>';
			}
			
			$rowsCount++;
		}
	}
	
	$rowsCanalSatisfactionFile = getSatisfactionLevelsFiles($company_id, null, 2);
	
	for ($i=0;$i<count($rowsCanalSatisfactionFile);$i++)
	{		
		$type = 2;
	
		$sql = "SELECT COUNT(*) FROM comments";
		$sql .= " WHERE comment_item_id = ".$company_id;
		$sql .= " AND comment_item_type = ".$type;

		$countComment = db_loadColumn($sql);
		
		$s .= '<tr class="tableRowLineCell"><td colspan="5"><br/></td></tr>';
		
		$s .= '<tr>';

		$s .= '<td width="1%">';
		$s .= '	<a name="#row_'.$rowsCount.'"></a>';
		$s .= '	<a href="javascript: //" onclick="open_rows=openclose(open_rows, '.$rowsCount.','.$company_id.', '.$type.');">';
		$s .= '	('.$countComment[0].')';
		$s .= '	</a>';
		$s .= '</td>';

		$s .= '<td width="40">';
		$s .= '	<a name="#row_'.$rowsCount.'"></a>';
		$s .= '	<a href="javascript: //" onclick="open_rows = openclose_edit(open_rows, '.$rowsCount.', '.$company_id.', '.$type.');">';
		$s .= '	<img src="./images/icons/comment.gif" width="20" height="20" border="0" alt="'.$AppUI->_('New Comment').'">';
		$s .= '	</a>';
		$s .= '</td>';
		
		$userSatisfaction = $rowsCanalSatisfactionFile[$i]["level_satisfaction_user"];
		$idSatisfaction = $rowsCanalSatisfactionFile[$i]['satisfaction_supplier_customer_id'];
		$file_name = $rowsCanalSatisfactionFile[$i]['original_file_name'];
		$real_path = "files/".$rowsCanalSatisfactionFile[$i]['file_name'];

		$s .= '<td width="20%">'.($userSatisfaction == $AppUI->user_id ? '<img src="images/icons/trash_small.gif" style="cursor:pointer;" onclick="javascript:deleteFileSupplier('.$idSatisfaction.');" />' : '').'<a href="download.php?file_name='.$file_name.'&real_path='.$real_path.'"><img src="modules/webtracking/images/attachment.png" border="0" />'.$file_name.'</a></td>';
		
		$s .= '<td align="center"></td>';		

		$s .= '<td align="center">';
		
			for($k=0;$k<$rowsCanalSatisfactionFile[$i]["level_satisfaction"];$k++)
				$s .= '<img src="modules/reviews/images/blue.gif" alt="">';

			if ($k == 0)
				$s .= '----';
		
			$arrUser = CUser::getUsersFullName(array($rowsCanalSatisfactionFile[$i]["level_satisfaction_user"]));
			$level_satisfaction_date = new CDate($rowsCanalSatisfactionFile[$i]["level_satisfaction_date"]);
			$s .= ($i==0 ? '<b>' : '').'<br/>('.$arrUser[0]['fullname'].' '.$AppUI->_('on').' '.$level_satisfaction_date->format($AppUI->getPref('SHDATEFORMAT')).')'.($i==0 ? '</b>' : '');
		
		$s .= '</td>';

		$s .= '</tr>';
		
		$s .= '<tr><td colspan="8"><span id="new_'.$rowsCount.'"></span></td></tr>';
		$s .= '<tr><td colspan="8"><span id="'.$rowsCount.'"></span></td></tr>';

		$s .= '<tr class="tableRowLineCell"><td colspan="5"></td></tr>';
		
		$rowsCount++;
	}
	
	if($rowsCount == 0)
		$s .= '<tr><td>'.$AppUI->_( 'No data available' ).'<br />'.$AppUI->getMsg().'</td></tr>';
	
	$tableStyle = 'class="std" style="border-top-width:1px;border-bottom-width:0px;border-left-width:0px;border-right-width:0px;border-style:solid;border-color:black;"';
	echo '<table cellpadding="2" cellspacing="0" border="0" width="100%" '.$tableStyle.'>' . $s . '</table>';

?>

<script type="text/javascript">
	var open_rows;
	var rows;

	function deleteFileSupplier(filename)
	{
		if (confirm('Esta seguro de borrar este archivo?'))
			xajax_deleteSatisfaction(filename);
	}

	function openclose(open_rows, rows, item, type)
	{
		var open_rows = open_rows;
		var rows = rows;
		var item = item;
		
		if (open_rows[rows][0] == '0')
		{
			if (open_rows[rows][1] == '1') 
			{
				xajax_clearcommentcompanies("new_"+rows);
				open_rows[rows][1]=0;
			}
			
			open_rows[rows][0] = 1;
			xajax_commentcompanies(rows, item, type);
		}
		else 
		{
			xajax_clearcommentcompanies(rows);
			xajax_clearcommentcompanies("new_"+rows);
			open_rows[rows][0]=0;
			open_rows[rows][1]=0;
		}
		return open_rows;
	}

	function openclose_edit(open_rows, rows, item, type)
	{
		var open_rows = open_rows;
		var rows = rows;
		var item = item;
		
		if (open_rows[rows][1] == '0')
		{
			xajax_editcommentcompanies(rows, item, 0, type);
			open_rows[rows][1] = 1;
			open_rows[rows][0] = 0;
			xajax_commentcompanies(rows, item, type);
		}
		else 
		{
			xajax_clearcommentcompanies("new_"+rows);
			xajax_clearcommentcompanies(rows);
			open_rows[rows][1] = 0;
			open_rows[rows][0] = 0;
		}
		
		return open_rows;
	}
</script>

<?

	$strJs = "<script language='Javascript'>";
	$strJs .=  "open_rows = new Array(".$rowsCount.");";
	$strJs .=  "items = new Array(2);";
	$strJs .=  "items[0]=0;";
	$strJs .=  "items[1]=0;";
	$strJs .=  "for(i=0;i<".$rowsCount.";i++) open_rows[i] = items;";
	$strJs .=  "</script>";

	echo($strJs);

?>