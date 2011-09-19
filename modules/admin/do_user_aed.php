<?php /* ADMIN $Id: do_user_aed.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */
$del = isset($_REQUEST['del']) ? $_REQUEST['del'] : 0;
$upload_dir = $AppUI->getConfig('hhrr_uploads_dir');
$another_user = dPgetParam($_POST, 'return1', 0);

$from_file = $_POST['from'];

$isNotNew = @$_REQUEST['user_id'];
$sql = "select count(*) 
		from users 
		where user_username = '".$_POST["user_username"]."'
		and '' <> '".trim($_POST["user_username"])."'";

if(db_loadResult($sql) == 0 || $isNotNew ){
	$obj = new CUser();
	
	if ($isNotNew)
		$obj->load($isNotNew);
	
	if (!$obj->bind( $_POST )) {
		$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
		$AppUI->redirect();
	}
	
	// prepare (and translate) the module name ready for the suffix
	$AppUI->setMsg( 'User' );
	if ($del) {
		$id=@$_REQUEST['user_id'];
		$userdir="$upload_dir/$id";
		$foto = $obj->user_pic;
		$cv = $obj->resume;
		if (($msg = $obj->delete())) {
			@unlink($userdir ."/". $foto);
			@unlink($userdir ."/". $cv);
			rmdir($userdir);
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
			$AppUI->redirect( 'm=admin' );
		} else {
			$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
			$AppUI->redirect();
			$AppUI->redirect(  'm=admin' );
		}
	} else if (@$_POST['do_remove'] != "" ){//borrado de archivos
		switch ($_POST['do_remove'])
		{
			//case "photo":
			case "user_pic":
				$id = $_POST['user_id'];
				$objRemove = new CUser();
				$objRemove->load( $id );
				@unlink($upload_dir . "/" . $id ."/". $_POST["photo_name"]);

				$objRemove->user_pic="";

				if (($msg = $objRemove->store())) {
					$AppUI->setMsg( $msg. db_error(), UI_MSG_ERROR );
	} else {
					$AppUI->setMsg( 'updated', UI_MSG_OK, true );
				}
				
				
				if ($isNotNew)
				{
					if ($_POST['origen']!="")
						$AppUI->redirect($_POST['back']);
					else
						$AppUI->redirect('m=admin&a=viewuser&user_id='.$id);
				}

				if (!$another_user){
					$AppUI->setState( 'UserAddBatch', 0 );
					$AppUI->redirect('m=admin&a=viewuser&user_id='.$id);
				} else {
    				$AppUI->setState( 'UserAddBatch', 1 );
					$AppUI->redirect('m=admin&a=addedituser');
				}
		
				break;
		}		
	} else {

		$photo=@$_FILES['user_pic'];
		
		if ($photo[size]!=0)
		{
			$photoName = $photo["name"];
		}
		else
		{
			if(@$_POST['user_id']<>-1)
				$photoName = $row["user_pic"];
			else
				$photoName = "";
		}
		$obj->user_pic = $photoName;

		if (!$isNotNew) {	
			$obj->user_owner = $AppUI->user_id;
		}
		
		if($_POST['timexp_supervisor_type'] == -2){
			$obj->timexp_supervisor = -1;
		}
		
		if($_POST['timexp_supervisor_type'] == -1){
			$obj->timexp_supervisor = -2;
		}
		
		$obj->user_supervisor = $_POST['user_supervisor'];
		
		if (($msg = $obj->store())) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
		} else {
			$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
		}
		
		// si el usuario actualizado es el usuario logueado 
		//debo actualizarla información
		if ($_REQUEST['user_id'] == $AppUI->user_id){
			if(!$AppUI->reloadUserInfo()){
				$AppUI->setMsg( "Problems loading your session data, please logout and login again", UI_MSG_ERROR );
			}
		}
		
		$id = $obj->user_id;

		//$sql_sup = "UPDATE users SET user_supervisor= '".$_POST['user_supervisor']."' WHERE user_id='$id' ";
	    //db_exec($sql_sup);
        
		if($_POST['user_type'] != '5')
		{
		  if ($_POST['user_type']!=''){$legajo = $_POST['legajo'];}else{$legajo = "";}
		}
		else{
		 $legajo = "";
		}

		$sql_sup = "UPDATE users SET legajo = '".$legajo."' WHERE user_id='$id' ";
	    db_exec($sql_sup);

	    //////// APLICAR TEMPLATE SEGURIDAD EN CASO DE SELECCIONAR ALGUNO ////////

	    if($template_permission_template > 0)
	    {
			mysql_query("DELETE FROM permissions WHERE permission_user = '$id'") or die(mysql_error());

			$sql =  "INSERT INTO permissions (permission_user, permission_grant_on, permission_item, permission_value) ";
			$sql .= "SELECT ".$id.", template_permission_grant_on, template_permission_item, template_permission_value FROM securitytemplate_permissions WHERE template_permission_template = ".$template_permission_template;

			db_exec($sql);
		}
		////////////////////////////////////////////////////////////////////////////

		$userdir="$upload_dir/$id";
		if (!is_dir($userdir)){
			mkdir($userdir,0755);
		}
	    
		//actualizado de archivos
		if ($photo[size]!=0)
		{
			move_uploaded_file($photo['tmp_name'], $upload_dir . "/" . $id ."/". $photo["name"]); 
			if(@$_POST['user_id']<>-1 && $row["user_pic"] <> "") 
				@unlink($upload_dir . "/" . $id ."/". $row["user_pic"]);
		}

		
		if($from_file =='tasks'){
		   
		      $AppUI->redirect('m=admin&a=addedituser_admin&user_id='.$id.'&dialog=1&suppressLogo=1&from=tasks&status=edit&cph='.$obj->user_cost_per_hour);
		}
		
		if ($isNotNew)  
		 {
			if ($_POST['origen']!="")
			 {
			 $AppUI->redirect($_POST['back']);
			 }
			 else
			 {
			 $AppUI->redirect('m=admin&a=viewuser&user_id='.$id);
			 }
		 }	
		if (!$another_user){
			$AppUI->setState( 'UserAddBatch', 0 );		
			$AppUI->redirect('m=admin&a=viewuser&user_id='.$id);
		}else{ 	
    		$AppUI->setState( 'UserAddBatch', 1 );		
			$AppUI->redirect('m=admin&a=addedituser');
		}
	}
}else{
	$msg = $AppUI->_("Username already exists, choose other username").".<br> ";
	
	$proposed_username = substr(trim($_POST["user_first_name"]),0,1).$_POST["user_last_name"];
	$proposed_username = strtolower(str_replace(" ", "", $proposed_username));
	$newusername = $proposed_username;
	$sql= "select count(*) from users where user_username = '$newusername'";
	if (db_loadResult($sql)>0){
		$rta=1; $i=1;
		while ($rta>0){
			$newusername = $proposed_username.$i;
			$sql= "select count(*) from users where user_username = '$newusername'";
			$rta = db_loadResult($sql);
			$i++;
		}
	}	
	$msg .= $AppUI->_("Suggested username").": <b>$newusername</b>";
	$AppUI->setMsg($msg,UI_MSG_ERROR);	
	$_POST["user_username"] = $newusername;
	$_POST["user_password"] = "";
	$_POST["password_check"] = "";
}
?>