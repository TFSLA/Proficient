<?php /* COMPANIES $Id: do_company_aed.php,v 1.2 2009-07-17 18:53:19 nnimis Exp $ */
$del = dPgetParam( $_POST, 'del', 0 );
$obj = new CCompany();
$msg = '';

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}


//Assign custom fields to task_custom for them to be saved
$custom_fields = dPgetSysVal("CompanyCustomFields");
$custom_field_data = array();
if ( count($custom_fields) > 0 ){
	foreach ( $custom_fields as $key => $array ) {
		$custom_field_data[$key] = $_POST["custom_$key"];
	}
	$obj->company_custom = serialize($custom_field_data);
}

$obj->contact_id = $_POST[contact_id];
$obj->company_canal = $_POST[company_canal];
$obj->company_segment = $_POST[company_segment];

if($company_type == 3 && ($original_supplier_status != $company_supplier_status))
{
	$obj->company_supplier_change_status_user = $AppUI->user_id;
	
	$supplierStatusDate = new CDate();	
	$obj->company_supplier_change_status_date = $supplierStatusDate->format( FMT_TIMESTAMP_DATE );
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Company' );
if ($del) {
	if (!$obj->canDelete( $msg )) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( 'deleted', UI_MSG_ALERT, true );
		//$AppUI->redirect( '', -1 );
		$AppUI->redirect( "m=companies");
	}
} else {
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );		
	} else {	
		$AppUI->setMsg( @$_POST['company_id'] ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect("m=companies");
}
?>