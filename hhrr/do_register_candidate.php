<?php
extract($_POST, "");

$empty_fields = array();

if ($firstname == "") 	$empty_fields[] = $AppUI->_("First Name");
if ($lastname == "") 	$empty_fields[] = $AppUI->_("Last Name");
if ($email == "") 		$empty_fields[] = $AppUI->_("Email");
if ($username == "") 	$empty_fields[] = $AppUI->_("Username");
if ($password == "") 	$empty_fields[] = $AppUI->_("Password");


$newusername = $username;
if (count($empty_fields)){
	$msg = $AppUI->_("The following records can not be empty").": ".
			implode(", ", $empty_fields);
  	$AppUI->setMsg($msg, UI_MSG_ERROR);

}else{

	if($password!=$password2){
		$AppUI->setMsg($AppUI->_("chgpwNoMatch"),UI_MSG_ERROR);
	
	}else{

		$existmail = db_loadResult("SELECT count(*) FROM users 
									WHERE user_email = '$email';");

        if($existmail > 0){
           $msg = $AppUI->_("User email already exists").".<br> ";
		   $AppUI->setMsg($msg,UI_MSG_ERROR);
		}

		$existuser = db_loadResult("SELECT count(*) FROM users 
									WHERE user_username = '$username';");


		if(($existuser > 0)or($existmail > 0)){
            
			$msg = $AppUI->_("Username already exists, choose other username").".<br> ";
			
			$proposed_username = substr(trim($firstname),0,1).trim($lastname);
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

			if ($existmail > 0)
			{
			$msg = $AppUI->_("User email already exists").".<br> ";
			}
            
			$AppUI->setMsg($msg,UI_MSG_ERROR);
		}else{
			
			$_POST["user_id"] = "";
			$_POST["user_username"] = $username;
			$_POST["user_first_name"] = $firstname;
			$_POST["user_last_name"] = $lastname;
			$_POST["user_password"] = $password;
			$_POST["user_email"] = $email;
			$_POST["new_candidate"] = true;
			
			include_once( $AppUI->getConfig("root_dir")."/modules/hhrr/hhrr.class.php" );
			include_once($AppUI->getConfig("root_dir")."/modules/hhrr/do_hhrr_aed.php");
			
			$ok = $AppUI->login( $username, $password );
			if (!$ok) {
				$AppUI->setMsg( 'Login Failed' );
				$user = db_loadResult("select user_id from users where user_username = '$username'");
				loguserevent(3, $user);                
			}else{
				loguserevent(1);         
			}
			//$_GET["a"] = "welcome";
			$AppUI->redirect( "a=welcome" );
		
		}
	}
}

?>