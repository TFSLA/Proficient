<?php

function lim_user($correct){
	$user_limit=100;																							// Límite de usuarios
	$user_limit=$user_limit-$correct;
	$user_limit++;																							// Le sumo 1 al administrador
	$license_key="58c7e3173a83184fe920cdd1203c3dcf";						// Md5('nombre de la empresa')
	$sql="SELECT count(user_id) AS user_act from users WHERE user_status=0";
	$rc=db_exec($sql);
	$vec=db_fetch_array($rc);
	if ($user_limit>$vec['user_act']){
	return($license_key);
	}
	else{
		return(0);
	}
}
