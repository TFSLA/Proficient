<?php

require_once("./includes/xajax/xajax.inc.php");

class myXajaxResponse extends xajaxResponse  {}

$xajax = new xajax();

include("./modules/public/ajax.php");

$xajax->registerFunction("addcommentcompanies");
$xajax->registerFunction("clearcommentcompanies");
$xajax->registerFunction("commentcompanies");
$xajax->registerFunction("editcommentcompanies");
$xajax->registerFunction("delcommentcompanies");
$xajax->registerFunction("deleteSatisfaction");
$xajax->registerFunction("updateContactCompany");
$xajax->registerFunction("changeMainContact");
$xajax->registerFunction("updateContactPublic");

function addcommentcompanies($rows, $item, $text, $comment_id, $type)
{
	global $AppUI;

	$item = checkpost($item);
	$rows = checkpost($rows);
	$text = checkpost($text);
	
	if ($comment_id == 0)
		$sql = "INSERT INTO comments (comment_user_id, comment_note, comment_item_id, comment_item_type) VALUES (".$AppUI->user_id.", \"$text\", \"$item\", \"$type\");";
	else
		$sql = "UPDATE comments SET comment_note=\"$text\" WHERE comment_id = '$comment_id'";
				
	if (db_exec($sql))
	{
		include ('./includes/comments.php');
		$objResponse = new myXajaxResponse();
		$objResponse->addAssign($rows,"innerHTML", $notes);
		return $objResponse;
	}
	else 
	{
		$objResponse = new myXajaxResponse();
		$objResponse->addAssign($rows,"innerHTML", $sql);
		return $objResponse;
	}
}

function delcommentcompanies($rows, $item, $comment_id, $type)
{
	$sql="DELETE FROM comments WHERE comment_id = '$comment_id'";
	
	if (db_exec($sql))
	{
		$sql = "SELECT COUNT(comment_id) AS citems FROM comments WHERE comment_item_id = $item AND comment_item_type = $type";
		
		$vec=db_fetch_array(db_exec($sql));
		
		include ('./includes/comments.php');
		$objResponse = new myXajaxResponse();
		$objResponse->addAssign($rows,"innerHTML", $notes);
		return $objResponse;
	}
	else 
	{
		$objResponse = new myXajaxResponse();
		$objResponse->addAssign($rows,"innerHTML", $sql);
		return $objResponse;
	}
}

function commentcompanies($rows, $item, $type)
{
	include ('./includes/comments.php');
	$objResponse = new myXajaxResponse();
	$objResponse->addAssign($rows,"innerHTML", $notes);
	return $objResponse;
}

function clearcommentcompanies($rows)
{
	$clear='';
	$objResponse = new myXajaxResponse();
	$objResponse->addAssign($rows,"innerHTML", $clear);
	return $objResponse;
}

function editcommentcompanies($rows, $item, $comment_id, $type)
{
	global $AppUI;
			
	if($comment_id > 0)
	{
		$sql = "SELECT comment_note, comment_id FROM comments WHERE comment_id = '$comment_id'";
		$rc = db_exec($sql);
		$vec = db_fetch_array($rc);
	}
	else
		$comment_id = 0;
		
	if($comment_id == '')
		$add = $AppUI->_('Add');
	else
		$add = $AppUI->_('Edit');
		
	$clear = $AppUI->_('Clear');
	
	$edit = "<table width='98%' border='0' align='right' >";
	$edit .= "<tr><td>";
	$edit .= "<form name='edit$rows'>\n";
	$edit .= "<table width='100%' border='0' align='right' bgcolor='#F9F9F9'>\n";
	$edit .= "<tr id='show_$rows'>\n
					<td width='5'></td>
					<td style='background:#F7F7F7' align='left'>
						<textarea rows='2' cols='120' name='text$rows'>".$vec['comment_note']."</textarea>
					</td>
				</tr>
				<tr id='show_$rows'>
					<td align='right' colspan='2'>\n
						<a href='javascript: //' onclick=\"var text = document.forms['edit$rows']['text$rows'].value; xajax_addcommentcompanies($rows, $item, text, $comment_id, $type); document.forms['edit$rows']['text$rows'].value=''; open_rows[$rows][1]=0; openclose_edit(open_rows, $rows, $item, $type);\">[$add]</a>
						<a href='javascript: //' onclick=\"document.forms['edit$rows']['text$rows'].value=''\">[$clear]</a>\n
					</td>\n
				</tr>\n";
	$edit .= "</table></form>\n";
	$edit .= "</td></tr>";
	$edit .= "</table>\n";
	$objResponse = new myXajaxResponse();
	$objResponse->addAssign("new_".$rows,"innerHTML", $edit);
	return $objResponse;
}

function deleteSatisfaction($idSatisfaction)
{
	global $AppUI;
	
	$sql = "DELETE FROM satisfaction_suppliers_customers_files WHERE satisfaction_supplier_customer_id = ".$idSatisfaction;

	db_exec( $sql );	

	$sql = "DELETE FROM satisfaction_suppliers_customers WHERE satisfaction_supplier_customer_id = ".$idSatisfaction;

	db_exec( $sql );
	
	$objResponse = new myXajaxResponse();
	$objResponse->addScript("window.top.location.reload();");
	return $objResponse;
}

function updateContactCompany($contactId, $companyName, $notRefresh=null)
{
	$sql = "UPDATE contacts SET contact_company = '$companyName' WHERE contact_id = $contactId";
	db_exec($sql);
	
	$objResponse = new myXajaxResponse();
	if(!$notRefresh)
		$objResponse->addScript("window.top.location.reload();");
	return $objResponse;
}

function changeMainContact($contact_id, $company_id)
{
	global $AppUI;
	
	$sql = "SELECT contact_id FROM companies WHERE company_id = $company_id";
	$oldContact = db_loadResult($sql);
	
	$objResponse = new myXajaxResponse();
	
	//Si ya está es porque lo está destildando
	if($oldContact == $contact_id)
		$contact_id = 0;
	
	$sql = "UPDATE companies SET contact_id  = $contact_id WHERE company_id = $company_id";
	$ret = db_exec( $sql );
	
	$AppUI->setMsg($AppUI->_('Main contact Updated') , UI_MSG_OK);
	$message = str_replace("'",'', $AppUI->getMsg());
	$message = str_replace('"','\"', $message);	
	
	$objResponse->addScript("showGenericMessage('".$message."');");
	
	return $objResponse->getXML();
}

function updateContactPublic($contact_id, $contact_public)
{
	$sql = "UPDATE contacts SET contact_public = $contact_public WHERE contact_id = $contact_id";
	$ret = db_exec( $sql );
	
	$objResponse = new myXajaxResponse();
	return $objResponse->getXML();
}

$xajax->processRequests();

$xajax->printJavascript('./includes/xajax/');
?>