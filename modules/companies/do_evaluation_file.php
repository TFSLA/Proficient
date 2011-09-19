<?php

$company_id = intval( dPgetParam( $_POST, 'company_id', 0 ) );

$upload = null;

if (isset( $_FILES['fileEvaluation']) && $company_id > 0)
{
	$upload = $_FILES['fileEvaluation'];

	if ( ( $upload['size'] < 1 ) && ( !$file_id ) )
	{
			$AppUI->setMsg( 'Upload file size is zero.  Process aborted.', UI_MSG_ERROR );
			$AppUI->redirect();
	}
	else if ($upload['name'] != NULL)
	{
		$file_original_name = $upload['name'];
		$file_name = uniqid( rand() );

		if ( !moveTemp( $upload, $file_name ))
			$AppUI->setMsg( 'The file upload has failed. Please, contact your system administrator.', UI_MSG_ERROR );
		else
		{
			include_once('./modules/public/satisfaction_suppliers_customers.php');
			
			$satisfactionId = addSatisfaction(2, $_POST['combo_level_customer_satisfaction'], $company_id, 0);
				
			addSatisfactionFile($satisfactionId, $file_name, $file_original_name);
		
			$AppUI->setMsg('New document added to a company', UI_MSG_OK, true );
		}
	}
}

$AppUI->redirect($AppUI->state['SAVEDPLACE']);

function moveTemp( $upload,  $file_name)
{
	global $AppUI;

	// check that directories are created
	@mkdir( "{$AppUI->cfg['root_dir']}/files", 0777 );

	$filepath = "{$AppUI->cfg['root_dir']}/files/$file_name";
	
	// Funcion de php que guarda el archivo cargado en el directorio que se le ordene.
	return move_uploaded_file( $upload['tmp_name'], $filepath );
}
?>