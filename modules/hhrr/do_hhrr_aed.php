<?php /* DEPARTMENTS $Id: do_hhrr_aed.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
$hhrr_portal = isset($_POST['new_candidate']) ? $_POST['new_candidate'] : false;
$del = isset($_POST['del']) ? $_POST['del'] : 0;

// desde el portal de candidatos prohibo el borrado de usuarios
if ($hhrr_portal) $del = 0;

$upload_dir = $AppUI->getConfig('hhrr_uploads_dir');

//return;
$obj = new CUser();

if ($_POST['user_id'])
	$obj->load($_POST['user_id']);

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'HHRR' );
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
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
	}
}
else if (@$_POST['do_remove'] != "" ){//borrado de archivos
	switch ($_POST['do_remove'])
	{
		//case "photo":
		case "user_pic":
			@unlink($upload_dir . "/" . $id ."/". $row["user_pic"]);
			$obj->user_pic="";
			
			if (($msg = $obj->store())) {
				$AppUI->setMsg( $msg. db_error(), UI_MSG_ERROR );
			} else {
				$AppUI->setMsg( 'HHRR' );
				$AppUI->setMsg( 'updated', UI_MSG_OK, true );
			}
			break;
		case "resume":
			@unlink($upload_dir . "/" . $id ."/". $row["resume"]);
			$obj->resume="";
			
			if (($msg = $obj->store())) {
				$AppUI->setMsg( $msg. db_error(), UI_MSG_ERROR );
			} else {
				$AppUI->setMsg( 'HHRR' );
				$AppUI->setMsg( 'updated', UI_MSG_OK, true );
			}
			
			break;
	}
	$nextpage = "m=hhrr&a=addedit&tab=1&id=".$id;
}
else
{
	$id=@$_POST['user_id'];

	$newuser = !$id;
	$today = new CDate();
	
	if ($newuser){

		if ($_POST['user_type'] == '5'){
		// el usuario es de tipo candidato
		$obj->user_type = 5;
		}else{
		$obj->user_type = $_POST['user_type'];
		}
		
		
		if ($hhrr_portal)
		{
			$obj->user_type = 5;
		}

		$obj->user_status = 1;

		mt_srand((double)microtime()*1000000);
		$pass = mt_rand(10000,99999);

		if($_POST["password"] =="")
		{
		$password = $pass;
		}
		else{
		$password = $_POST["password"];
		}

		if($_POST['username'] =="")
		{
		 $username = substr($_POST['user_first_name'],0,1).$_POST['user_last_name'];

		 $user_username = $username;
        
		$obj->user_username = $user_username;

		// pertenece a la empresa interno
		$sql = "select company_id from companies where company_type = '0' limit 0,1;";
		$obj->user_company = db_loadResult($sql);
		$obj->date_created = $today->format(FMT_DATETIME_MYSQL);
		$obj->last_visit = $today->format(FMT_DATETIME_MYSQL);
	
		$obj->hhrr_password = $password;

		$existmail = db_loadResult("SELECT count(*) FROM users 
									WHERE user_email = '$user_email';");
		
        $existuser = db_loadResult("SELECT count(*) FROM users 
									WHERE user_username = '$username';");
		if($existuser > 0){
			
			$proposed_username = substr(trim($_POST['user_first_name']),0,1).trim($_POST['user_last_name']);
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

				 $obj->user_username = $newusername;
			}	
		}

		}
        else{
		$user_username = $_POST['username'];
        
		$obj->user_username = $user_username;

		// pertenece a la empresa interno
		$sql = "select company_id from companies where company_type = '0' limit 0,1;";
		$obj->user_company = db_loadResult($sql);
		$obj->date_created = $today->format(FMT_DATETIME_MYSQL);
		$obj->last_visit = $today->format(FMT_DATETIME_MYSQL);
			
		$obj->hhrr_password = $password;

        $existuser = db_loadResult("SELECT count(*) FROM users 
									WHERE user_username = '".$_POST['username']."';");
		
		echo "<br>SELECT count(*) FROM users WHERE user_username = '".$_POST['username']."';<br>";

		if($existuser > 0){
			$msg = $AppUI->_("Username already exists, choose other username").".<br> ";
			
			$proposed_username = substr(trim($_POST['user_first_name']),0,1).trim($_POST['user_last_name']);
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
			$msg .= $AppUI->_("Suggested username").": $newusername";
			$AppUI->setMsg($msg,UI_MSG_ERROR);
			$_POST["user_username"] = $newusername;
			$AppUI->post = $_POST;
			$AppUI->redirect("m=hhrr&a=addedit&tab=1&e=1");
		}
	}
	

	}
    
	
	 // Reviso que el usuario a crearse no exista ( Verifico empresa-nombre-apellido-email)
	// Si va a updatear me fijo que controlo contra si mismo
	if($_POST['user_id']!=""){
		$query_user = "AND user_id <> '".$_POST['user_id']."' ";
	}else{
		$query_user = "";
	}
	
	$existU = db_loadResult("SELECT count(*) FROM users 
							 WHERE user_company='".$_POST['company']."' and user_first_name='".$_POST['user_first_name']."' and user_last_name='".$_POST['user_last_name']."' and user_email = '".$_POST['user_email']."' ".$query_user." ");
	    
     if($existU > 0){
           $cia_name = db_loadResult("SELECT company_name FROM companies
								 WHERE company_id ='".$_POST['company']."'; ");
           
           $msg = $AppUI->_("The user already exists").".<br> ";
           $msg .= $_POST['user_first_name']." ".$_POST['user_last_name'].", ".$_POST['user_email'].", ".$cia_name;
		   $AppUI->setMsg($msg,UI_MSG_ERROR);
		   $AppUI->redirect();
			
	}
	
	
	if ($hhrr_portal)
	{
	  $obj->user_type = 5;
	}
	else
	{
	  $obj->user_type = $_POST['user_type'];	
	}

	if($_POST['user_type'] !='5' && !$hhrr_portal){
	$obj->user_job_title = $_POST['position'];
	$obj->user_company = $_POST['company'];
    
		if ($_POST['department'] == '-1'){
          $obj->user_department = '0';
		}else{
		  $obj->user_department = $_POST['department'];
		}
	
	}

	$obj->date_updated = $today->format(FMT_DATETIME_MYSQL);
	
	$obj->wasinterviewed = $_POST["wasinterviewed"] == "on";
	
	 $AppUI->post = "";

		if (($msg = $obj->store())) {
			$AppUI->setMsg( $msg. db_error(), UI_MSG_ERROR );
		} else {
			$isNotNew = @$_POST['user_id'];
			$AppUI->setMsg( $isNotNew ? 'updated' : 'inserted', UI_MSG_OK, true );
		}
    
	//echo "<pre> 1\n"; var_dump($obj); echo "</pre>";$isNotNew = @$_POST['user_id'];
    
	if($_POST['user_type'] !='5' && !$hhrr_portal){ 
	$search = db_exec("SELECT count(*) from hhrr_comp where hhrr_comp_user_id = '".$obj->user_id."' ");
	$data = mysql_fetch_array($search);
	$cant_comp = $data[0];

	    if ($cant_comp > 0)
		{
	    $sql_s = " UPDATE hhrr_comp SET hhrr_comp_remuneration= '".$_POST['salarycurrent']."' WHERE hhrr_comp_user_id = '".$obj->user_id."' ";
		}else{
		$sql_s = " INSERT INTO hhrr_comp (hhrr_comp_id, hhrr_comp_user_id, hhrr_comp_remuneration,  hhrr_comp_last_update) VALUES (NULL, '".$obj->user_id."','".$_POST['salarycurrent']."' , 'CURRENT_TIMEST') ";
		}

	db_exec($sql_s);
	}
	 
	if (!$hhrr_portal){
	$sql_u = " UPDATE users SET user_type = '".$_POST['user_type']."', legajo= '".$_POST['legajo']."' WHERE user_id = '".$obj->user_id."' ";
	db_exec($sql_u);
	}

	$id = $obj->user_id;
	$sql  = "SELECT * FROM users WHERE user_id = $id ";
	$rows = db_loadList( $sql, NULL );
	$row  = $rows[0];	
	
	$userdir="$upload_dir/$id";
	if (!is_dir($userdir)){
		mkdir($userdir,0755);
	}
	
		//actualizado de archivos
		$photo=@$_FILES['user_pic'];
		if ($photo[size]!=0)
		{
			move_uploaded_file($photo['tmp_name'], $upload_dir . "/" . $id ."/". $photo["name"]); 
			$photo=$photo["name"];
			if(@$_POST['user_id']<>-1 && $row["user_pic"] <> "") 
				@unlink($upload_dir . "/" . $id ."/". $row["user_pic"]);
		}
		else
		{
			if(@$_POST['user_id']<>-1) $photo=$row["user_pic"];
			else $photo="";
		}
		$obj->user_pic=$photo;

		$resume=@$_FILES['resume'];
		
		if ($resume[size]!=0)
		{   
			$var = explode(".",$_FILES['resume']['name']);
			$extension = $var[1];
			$Path_CV_File=$upload_dir."/". $id."/cv_user".$id.".".$extension;
			
			echo "<br> Path_CV_File $Path_CV_File<br>";

			move_uploaded_file( $resume['tmp_name'], $Path_CV_File); 
			$resume= "cv_user".$id.".".$extension;

			/*if(@$_POST['user_id']<>-1 && $row["resume"] <> "") 
				@unlink($upload_dir . "/" . $id ."/". $row["resume"]);*/
		}
		else
		{
			if(@$_POST['user_id']<>-1) $resume=$row["resume"];
			else $resume="";
		}

	//	echo "Resume: ".$resume;
		$obj->resume=$resume; 
		$obj->date_updated = date("Y-m-d"); 
	if (($msg = $obj->store())) {
		//echo "<pre>"; var_dump($obj); echo "</pre>";return;
		$AppUI->setMsg( $msg. db_error(), UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( 'HHRR' );
		$isNotNew = @$_POST['user_id'];
		$AppUI->setMsg( $isNotNew ? 'updated' : 'inserted', UI_MSG_OK, true );
	}
	
		$nextpage = "m=hhrr&a=addedit&tab=1&id=".$id;
}

//exit();

if ($del) {
$AppUI->redirect("m=hhrr");
}

if ($_POST['user_id']=="")
{
$nextpage = "m=hhrr&a=addedit&tab=1&id=".$id;
}

if (!$hhrr_portal) {
   $AppUI->redirect($nextpage);
}else{
	if($_GET[a]=="personalinfo"){
	$AppUI->redirect("a=personalinfo&tab=1");
	}
}

?>