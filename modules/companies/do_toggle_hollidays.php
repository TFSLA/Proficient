<?php 
$company_own_hollidays = dpGetParam( $_POST, "company_own_hollidays", 0 );
$company_id= dpGetParam( $_POST, "company_id", 0 );
$obj = new CCompany();
$msg = '';


if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

$sql = "UPDATE companies
		SET company_own_hollidays = $company_own_hollidays
		WHERE company_id = $company_id";

echo $sql;
		
if ( !db_exec( $sql ) )
{
	$AppUI->setMsg( db_error(), UI_MSG_ERROR );	
}
$AppUI->redirect();
?>