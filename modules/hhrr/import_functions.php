<? /* Funciones para la importacion de recursos */

global $AppUI;

$upload_dir = $AppUI->getConfig('hhrr_uploads_dir');


/**
 * Sube el archivo
 * Si esta todo bien devuelve el nombre del archivo
 */
function save_file()
{
	global $upload_dir;
	
    $importdir="$upload_dir/import";
	if (!is_dir($importdir)){
		mkdir($importdir,0755);
	}
	
     $import_file =@$_FILES['file_xls'];
     
	 if ($import_file[size]!=0)
		{
			move_uploaded_file($import_file['tmp_name'], $upload_dir . "/import/". $import_file['name']); 
			$import_file= $import_file['name'];
			return $import_file;
		}
}


/**
 * Verifica que el archivo se pueda leer, devuelve la cantidad de registros que contiene
 *
 * @param unknown_type $file_name = nombre del archivo a verificar
 */
function check_file($file_name,$company,$update_rrhh){
	global $AppUI,$upload_dir;
	
	$company_id = $company;
	
	$file = $upload_dir."/import/".$file_name;
	
	
	require_once("includes/phpexcelreader/Excel/reader.php");
	
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('CP1251');
	$data->read($file);
    
    $cant = $data->sheets['0']['numRows']-1;
    //echo "<pre>"; print_r($data->sheets['0']); echo "</pre>";
    
    if ($cant > 0)
    {
    	// Si tiene registros me fijo que los campos obligatorios existan
    	$row = 2;
    	$ok = true;
    	$cant_real = 0;
    	
    	while ($row<= $data->sheets['0']['numRows'] && $ok )
	    {
	    	
	        $fields = $data->sheets['0']['cells'][$row];
	    	
	        // Se fija que no esten vacios los campos obligatorios de la solapa personal
	        $user_type = $fields['1'];
	        $user_first_name = $fields['2'];
	        $user_last_name = $fields['3'];
	        $user_department = $fields['4'];
	        $user_email = $fields['6'];
	        $user_phone = $fields['7'];
	        $user_state_id = $fields['13'];
	        $user_country = $fields['15'];
	        $timexp_supervisor = $fields['46'];
	        $user_supervisor = $fields['47'];
	        
	        
	        if ( $user_type !=""){
	        
		    	if ( $user_first_name =="" || $user_last_name =="" ||  $user_email =="" || $user_phone =="" || $user_state_id =="" || $user_country =="" )
		    	{
		    		$msg = $AppUI->_('There required fields empty, the importation was canceled')."<br>";
		    		$ok = false;
		    	}
		    	
		    	
		    	if ($user_type !='5' && ($user_department == "" && $timexp_supervisor == "" && $user_supervisor == "") )
		    	{
		    		$msg .= $AppUI->_('There required fields empty, the importation was canceled')."<br>";
		    		$ok = false;
		    	}
		    	
		    	// Primero virifica que el departamento exista
		    	if($user_department !="" && $user_department !=0)
		    	{ 
		    		$query_exist = "SELECT count(*) FROM departments WHERE dept_id='".$user_department."' and dept_company='".$company."' ";
		    		$sql_exist = db_exec($query_exist);
		    		$row_exist = mysql_fetch_array($sql_exist);
		    		$dept_exits = $row_exist['0'];
		    		
		    		if ($dept_exits == 0)
		    		{
		    			$msg .= $AppUI->_('There are departments that do not exist')."<br>";
		    		    $ok = false;
		    		}
		    	}
		    	// Verifica los permisos del usuario sobre los departamentos
		    	if($user_department !="")
		    	{   
		    		if ($user_department == '0'){
		    		   $dept_permission = permission_hhrr($company, -1,0);
		    		}else{
		    		   $dept_permission = permission_hhrr($company, $user_department, 0);
		    		}
		    		
		    		// Me fijo si tiene permiso para todas las empresas, todos los departamentos
		    		$query_Allcompanies = "SELECT count(distinct(id)) 
					   FROM hhrr_permissions 
					   WHERE company = '-1' AND department='-1' AND id_user = '".$AppUI->user_id."'
					   AND personal = '-1'
					   AND matrix = '-1'
					   AND work_experience = '-1'
					   AND education = '-1'
					   AND performance_management = '-1'
					   AND compensations = '-1'
					   AND development = '-1'";   
                
					$sql_Allcompany = db_loadColumn($query_Allcompanies,NULL);
					$permission_allcia = $sql_Allcompany['0'];
		    		
		    		if (($dept_permission == 1 || $dept_permission == 0) && ($permission_allcia < 1) )
		    		{
		    		  $msg .= $AppUI->_('Do not have read write permission for some of the Departments of resources to import')."<br>";
		    		  $ok = false;
		    		}
		    	}
		    	
		    	// Verifica Antecedentes laborales
		    	$internal_company = $fields['50'];
		    	$company_id = $fields['51'];
		    	$area_internal = $fields['53'];
		    	
		    	if ($internal_company =='1' && $area_internal =="")
		    	{
		    		$msg .= $AppUI->_('There required fields empty, the importation was canceled')."<br>";
		    		$ok = false;
		    	}
		    	
		    	if ($internal_company !=""  && $company_id =="")
		    	{
		    		$msg .= $AppUI->_('There required fields empty, the importation was canceled')."<br>";
		    		$ok = false;
		    	}
		    	
		    	$cant_real = $cant_real + 1;
	        }
	    	
	    	$row = $row + 1;
	    }
	    
		if ($ok)
		{
			// Traigo el nombre de la empresa
			$query_company = "SELECT company_name FROM companies WHERE company_id = '".$company."' ";       
			$sql_company = db_loadColumn($query_company,NULL);
			$company_name = $sql_company['0'];
			
			if($update_rrhh)
			{
	    	$msg = "
			        <table align=\"center\" border=\"0\"><tr><td>
			        ".$AppUI->_('Will update')." ".$cant_real." ".$AppUI->_('resources')." ".$AppUI->_('from company')." ".$company_name."
			        </td></tr><tr>
			        <form name=\"ConFrm\" action=\"\" method=\"post\" >
                    <input type=\"hidden\" name=\"accion\" value=\"actualizar\">
                    <input type=\"hidden\" name=\"company\" value=\"".$_POST['company']."\">
                    <input type=\"hidden\" name=\"update_rrhh\" value=\"".$update_rrhh."\">
                    <input type=\"hidden\" name=\"file\" value=\"".$file_name."\">
			        <td align=\"right\"><br><input type=\"submit\" name=\"continuar\" value=\"".$AppUI->_('continue')."\" class=\"button\"></td></tr>
			        </form></table>";	
			}else{
			 $msg = "
			        <table align=\"center\" border=\"0\"><tr><td>
			        ".$AppUI->_('Will import')." ".$cant_real." ".$AppUI->_('resources')." ".$AppUI->_('from company')." ".$company_name."
			        </td></tr><tr>
			        <form name=\"ConFrm\" action=\"\" method=\"post\" >
                    <input type=\"hidden\" name=\"accion\" value=\"importar\">
                    <input type=\"hidden\" name=\"company\" value=\"".$_POST['company']."\">
                    <input type=\"hidden\" name=\"update_rrhh\" value=\"".$update_rrhh."\">
                    <input type=\"hidden\" name=\"file\" value=\"".$file_name."\">
			        <td align=\"right\"><br><input type=\"submit\" name=\"continuar\" value=\"".$AppUI->_('continue')."\" class=\"button\"></td></tr>
			        </form></table>";	
				
			}
         }
    	
    	
    }else{
    	$msg = $AppUI->_('No data for import');
    }
    
    // Si no puedo importar borro el archivo que subi
    if (!$ok)
    {
    	@unlink($file);
    }
    
    return $msg;
}


/**
 * Descarga el archivo en la base de datos, si llego hasta esta funcion ya fueron validados 
 * los campos
 *
 * @param unknown_type $file = nombre de archivo
 * @param unknown_type $company = id de la empresa a la que pertenecen los recursos
 */
function import_resources($file_name,$company)
{
	global $AppUI,$upload_dir;
	
	$file = $upload_dir."/import/".$file_name;
	
	require_once("includes/phpexcelreader/Excel/reader.php");
	
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('CP1251');
	$data->read($file);
	
	$cant = $data->sheets['0']['numRows']-1;
	
	//echo "<pre>"; print_r($data->sheets['0']); echo "</pre>";
	
	if ($cant > 0)
    {
       // Si tiene registros me fijo que los campos obligatorios existan
       $row = 2;
       $today = new CDate();
       $cant_r = 0;
    	
       while ($row<= $data->sheets['0']['numRows'])
	   {
	   	  $fields = $data->sheets['0']['cells'][$row];
	   	  $obj = new CUser();
	   	  
	   	  if($fields['1']!="")
	   	  {
	   	  
	   	 /* Guardo datos correspondientes a la solapa "Personal" */
	   	 
	   	   
	   	   # Genero el nombre de usuario a partir del nombre y apellidos
	   	   $username = substr($fields['2'],0,1).$fields['3'];
           $user_username = $username;
            
	   	   $existuser = db_loadResult("SELECT count(*) FROM users 
									   WHERE user_username = '$username';");
	   	   
	   	   if($existuser > 0){
			
			$proposed_username = substr(trim($fields['2']),0,1).trim($fields['3']);
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

				 $user_username = $newusername;
			  }	
		   }
		   
	   	  
		  
	   	  $obj->user_id = "";
	   	  $obj->user_username = $user_username;    	 
		  $password = $user_username;	
	   	   
	   	  $obj->user_password = $password;
	   	  
	   	  $obj->user_type = $fields['1'];
	   	  $obj->user_first_name = $fields['2'];
	   	  $obj->user_last_name = $fields['3'];
	   	  
	   	  
	   	  $obj->user_company = $company;
	   	  $obj->user_department = $fields['4'];	
	   	 
	   	  
	   	  $obj->user_job_title = $fields['5'];
	   	  $obj->user_email = $fields['6'];
	   	  $obj->user_phone = $fields['7'];
	   	  $obj->user_home_phone = $fields['8'];
	   	  $obj->user_mobile = $fields['9'];
	   	  $obj->user_address1 = $fields['10'];
	   	  $obj->user_address2 =  $fields['11'];
	   	  $obj->user_city = $fields['12'];
	   	  $obj->user_state_id = $fields['13'];
	   	  $obj->user_zip =  $fields['14'];
	   	  $obj->user_country_id = $fields['15'];
	   	  $obj->user_im_type = $fields['16'];
	   	  $obj->user_im_id = $fields['17'];
	   	  
	   	  $obj->user_birthday = $fields['18'];
	   	  
		  $obj->user_owner = $AppUI->user_id;
		  $obj->user_signature = $fields['19'];
		  $obj->user_smtp = $fields['20'];
		  $obj->user_smtp_auth = $fields['21'];
		  $obj->user_smtp_use_pop_values = $fields['22'];
		  $obj->user_smtp_username = $fields['23'];
		  $obj->user_smtp_password = $fields['24'];
		  $obj->user_mail_server_port = $fields['25'];
		  $obj->user_pop3 = $fields['26'];
		  $obj->user_imap = $fields['27'];
		  $obj->user_email_user = $fields['28'];
		  $obj->user_email_password = $fields['29'];
		  $obj->user_webmail_autologin = $fields['30'];
		  $obj->user_cost_per_hour = $fields['31'];
		  
		  # Los usuarios se crean inactivos
		  $obj->user_status = 1;
		  
		  $obj->start_time_am = $fields['32'];
		  $obj->end_time_am = $fields['33'];
		  $obj->start_time_pm = $fields['34'];
		  $obj->end_time_pm = $fields['35'];
		  $obj->daily_working_hours = $fields['36'];
		  
		  # Los usuarios se crean inactivos
		  $obj->enabled = 0; 
		  
		  $obj->protected = 0;
		  $obj->access_level = $fields['38'];
		  
		  $obj->date_created = $today->format(FMT_DATETIME_MYSQL);
		 
		  $obj->last_visit = $today->format(FMT_DATETIME_MYSQL);
		  $obj->doctype = $fields['39'];
		  $obj->docnumber = $fields['40'];
		  $obj->maritalstate = $fields['41'];
		  $obj->nationality = $fields['42'];
		  $obj->children = $fields['43'];
		  $obj->taxidtype = $fields['44'];
		  $obj->taxidnumber = $fields['45'];
		  
		  if ($fields['46']=="")
		  {
		    # Si esta vacio se lo ingresa como no supervisado
		    $obj->timexp_supervisor = '-1';
		  }else{
		  	$obj->timexp_supervisor = $fields['46'];
		  }
		  
		  if ($fields['47']=="")
		  {
		    # Si esta vacio se lo ingresa como no supervisado
		    $obj->user_supervisor = '-1';
		  }else{
		  	$obj->user_supervisor = $fields['47'];
		  }
		  
		  $obj->user_input_date_company = $fields['49'];
		  
		  //echo "<br><b>Datos personales</b><br>";
		  //echo "<pre>";
		   //  print_r($obj);
		  //echo "</pre>";
		  
		  $msg_o = $obj->store();
		  
		  $id_user = $obj->user_id;
		  
		  $legajo = $fields['48'];
		  
		  $sql_personal = db_exec("UPDATE users SET legajo= '".$legajo."' WHERE user_id='".$id_user."'; ");
		  
		
	   	 /* Guardo datos correspondientes a la solapa "Antecedentes laborales" */
	   	  
	   	 $internal_company = $fields['50'];
	   	 
	   	 if ($internal_company !="")
	   	 {
	   	 	$company_al = $fields['51'];
	   	 	$area_external = $fields['52'];
	   	 	$area_internal = $fields['53'];
	   	 	$function_al = $fields['54'];
	   	 	$from_date_al = $fields['55'];
	   	 	$to_date_al = $fields['56'];
	   	 	$profit = $fields['57'];
	   	 	$reports = $fields['58'];
	   	 	$functional_area = $fields['59'];
	   	 	$level_managment = $fields['60'];
	   	 	
	   	 	$query_al = "INSERT INTO hhrr_ant (user_id, company, internal_company, area_external, area_internal, function, to_date, from_date, profit, reports, functional_area, level_management) 
	   	 	VALUES ('$id_user', '$company_al', '$internal_company', '$area_external', '$area_internal', '$function_al', '$to_date_al', '$from_date_al', '$profit', '$reports', '$functional_area', '$level_managment')";             
	   	 	
	   	 	//echo "<b>Antecedentes laborales</br><br>";
	   	 	//echo "<pre>".$query_al."</pre>";
	   	 	$sql_al = db_exec($query_al);
	   	 }
	   	  
	   	 /* Guardo datos correspondientes a la solapa "Formacion profesional" */
	   	 
	   	 $type_fp = $fields['70'];
	   	 
	   	 if ($type_fp !="")
	   	 {
	   	 	$level = $fields['61'];
	   	 	
	   	 	if ($type_fp == '0')
	   	 	{
	   	 	    $title = $fields['62'];
	   	 	}
	   	 	else {
	   	 		$title = $fields['63'];
	   	 	}
	   	 	
	   	 	
	   	 	$instit = $fields['64'];
	   	 	$status = $fields['65'];
	   	 	$s_date = $fields['66'];
	   	 	$end_date = $fields['67'];
	   	 	$seminary_type = $fields['68'];
	   	 	$seminary = $fields['69'];
	   	 	
	   	 	$query_fp = "INSERT INTO hhrr_education (id_user, level, title, instit, status, s_date, end_date, seminary_type, seminary, type) 
	   	 	VALUES 
	   	 	( '$id_user', '$level', '$title', '$instit','$status', '$s_date', '$end_date', '$seminary_type', '$seminary', '$type_fp')";
	   	 	
	   	 	//echo "<b>Formacion profesional</br><br>";
	   	 	//echo "<pre>".$query_fp."</pre>";
	   	 	
	   	 	$sql_fp = db_exec($query_fp);
	   	 }
	   	 
	   	 /* Guardo datos correspondientes a la solapa "Evaluacion y rendimiento" */
	   	 
	   	 $supervisor = $fields['75'];
	   	 
	   	 if ($supervisor != "")
	   	 {
	   	 	$from_date = $fields['71'];
	   	 	$to_date = $fields['72'];
	   	 	$performance = $fields['73'];
	   	 	$potential = $fields['74'];
	   	 	
	   	 	$query_er = "INSERT INTO hhrr_performance (user_id, from_date, to_date, performance, potential, supervisor) 
	   	 	VALUES 
	   	 	( '$id_user', '$from_date', '$to_date', '$performance', '$potential', '$supervisor') ";
	   	 	
	   	    //echo "<b>Evaluacion y rendimiento</br><br>";
	   	 	//echo "<pre>".$query_er."</pre>";
	   	 	
	   	 	$sql_er = db_exec($query_er);
	   	 }
	   	 
	   	 /* Guardo datos correspondientes a la solapa "Compensaciones" */
	   	 
	   	 $hhrr_comp_remuneration = $fields['76'];
	   	 
	   	 if ($hhrr_comp_remuneration !="")
	   	 {
	   	 	$hhrr_comp_last_update_porc = $fields['77'];
	   	 	$hhrr_comp_last_update_date = $fields['78'];
	   	 	$hhrr_comp_gap_pc = $fields['79'];
	   	 	$hhrr_comp_last_reward = $fields['80'];
	   	 	$hhrr_comp_anual_remuneration = $fields['81'];
	   	 	$hhrr_comp_actual_benefits = $fields['82'];
	   	 	$hhrr_comp_gap_mer = $fields['83'];
	   	 	$Hhrr_comp_proposed_plan = $fields['84'];
	   	 	$hhrr_comp_last_update = $today->format(FMT_DATETIME_MYSQL);
	   	 	
	   	 	$query_comp = "INSERT INTO hhrr_comp ( hhrr_comp_user_id, hhrr_comp_remuneration, hhrr_comp_last_update_porc, hhrr_comp_last_update_date, hhrr_comp_gap_pc, hhrr_comp_last_reward, hhrr_comp_anual_remuneration, hhrr_comp_actual_benefits, hhrr_comp_gap_mer, Hhrr_comp_proposed_plan, hhrr_comp_last_update) 
	   	 	VALUES
	   	 	( '$id_user' , '$hhrr_comp_remuneration', '$hhrr_comp_last_update_porc', '$hhrr_comp_last_update_date', '$hhrr_comp_gap_pc', '$hhrr_comp_last_reward', '$hhrr_comp_anual_remuneration', '$hhrr_comp_actual_benefits', '$hhrr_comp_gap_mer', '$Hhrr_comp_proposed_plan', '$hhrr_comp_last_update') ";
	   	 	
	   	 	//echo "<b>Compensaciones</br><br>";
	   	 	//echo "<pre>".$query_comp."</pre>";
	   	 	
	   	 	$sql_comp = db_exec($query_comp);
	   	 	
	   	 }
	   	 
	   	 /* Guardo datos correspondientes a la solapa "Desarrollo" */
	   	 
	   	  $hhrr_dev_user_id = $id_user;
	   	  $hhrr_dev_eval_g_1 = $fields['86'];
	   	  $hhrr_dev_eval_g_S = $fields['87'];
	   	  $hhrr_dev_eval_t_1 = $fields['88'];
	   	  $hhrr_dev_eval_t_S = $fields['89'];
	   	  $hhrr_dev_sug = $fields['90'];
	   	  $hhrr_dev_rst = $fields['91'];
	   	  $hhrr_dev_rmt = $fields['92'];
	   	  $hhrr_dev_rlt = $fields['93'];
	   	  $hhrr_dev_pos_k = $fields['94'];
	   	  $hhrr_dev_per_k = $fields['95'];
	   	  $hhrr_dev_mov_af1 = $fields['96'];
	   	  $hhrr_dev_mov_asa1 = $fields['97'];
	   	  $hhrr_dev_mov_af2 = $fields['98'];
	   	  $hhrr_dev_mov_asa2 = $fields['99'];
	   	  $hhrr_dev_mov_af3 = $fields['100'];
	   	  $hhrr_dev_mov_asa3 = $fields['101'];
	   	  $hhrr_dev_int_a = $fields['102'];
	   	  $hhrr_dev_exp = $fields['103'];
	   	 
	   	  $query_dev = "INSERT INTO hhrr_dev (hhrr_dev_user_id, hhrr_dev_eval_g_1, hhrr_dev_eval_g_S, hhrr_dev_eval_t_1, hhrr_dev_eval_t_S, hhrr_dev_sug, hhrr_dev_rst, hhrr_dev_rmt, hhrr_dev_rlt, hhrr_dev_pos_k, hhrr_dev_per_k, hhrr_dev_mov_af1, hhrr_dev_mov_asa1, hhrr_dev_mov_af2, hhrr_dev_mov_asa2, hhrr_dev_mov_af3, hhrr_dev_mov_asa3, hhrr_dev_int_a, hhrr_dev_exp) 
	   	  VALUES 
	   	  ('$hhrr_dev_user_id', '$hhrr_dev_eval_g_1', '$hhrr_dev_eval_g_S', '$hhrr_dev_eval_t_1', '$hhrr_dev_eval_t_S', '$hhrr_dev_sug', '$hhrr_dev_rst', '$hhrr_dev_rmt', '$hhrr_dev_rlt', '$hhrr_dev_pos_k', '$hhrr_dev_per_k', '$hhrr_dev_mov_af1', '$hhrr_dev_mov_asa1', '$hhrr_dev_mov_af2', '$hhrr_dev_mov_asa2', '$hhrr_dev_mov_af3', '$hhrr_dev_mov_asa3', '$hhrr_dev_int_a', '$hhrr_dev_exp')"; 
	   	  
	   	  //echo "<b>Desarrollo</br><br>";
	   	  //echo "<pre>".$query_dev."</pre>";
	   	  $sql_dev = db_exec($query_dev);
	   	  
	   	  
	   	  $hhrr_dev_pf_date = $fields['105'];
	   	  
	   	  if ($hhrr_dev_pf_date !="")
	   	  {
	   	    $hhrr_dev_pf_user_id = $id_user;
	   	    $hhrr_dev_pf_action = $fields['104'];
	   	    $hhrr_dev_pf_coment = $fields['106'];
	   	    $hhrr_dev_pf_aproved = $fields['107'];
	   	    $hhrr_dev_pf_status = $fields['108'];
	   	    
	   	    $query_dev_pf = "INSERT INTO hhrr_dev_pf ( hhrr_dev_pf_user_id, hhrr_dev_pf_action, hhrr_dev_pf_date, hhrr_dev_pf_coment, hhrr_dev_pf_aproved, hhrr_dev_pf_status) 
	   	     VALUES 
	   	    ( '$hhrr_dev_pf_user_id', '$hhrr_dev_pf_action', '$hhrr_dev_pf_date', '$hhrr_dev_pf_coment', '$hhrr_dev_pf_aproved', '$hhrr_dev_pf_status')";
	   	  
	   	     
	   	 	 //echo "<pre>".$query_dev_pf."</pre>";
	   	     $sql_dev_pf = db_exec($query_dev_pf);
	   	  }
	   	 
	   	 /* Guardo datos correspondientes a la solapa "Matriz" */
	   	     
	   	     $idskill = $fields['109'];
	   	     
	   	     if ($idskill != "")
	   	     {
	   	     	
	   	     $value = $fields['110'];
	   	     $comment = $fields['111'];
	   	     $lastuse = $fields['112'];
	   	     $monthsofexp = $fields['113'];
	   	 
	   	     $query_matriz = "INSERT INTO hhrrskills (user_id, idskill, value, comment, lastuse, monthsofexp) VALUES ( '$id_user', '$idskill', '$value', '$comment', '$lastuse', '$monthsofexp')";
	   	     
	   	     //echo "<b>Matriz</br><br>";
	   	 	 //echo "<pre>".$query_matriz."</pre>";
	   	 	
	   	     $sql_matriz = db_exec($query_matriz);
	   	     
	   	     }
	   	     
	   	 /* ------------------------------------------------------*/
	   	  $cant_r = $cant_r + 1;
	   	  
	   	  }
	   	  
	   	 $row = $row + 1;
	   	
	   }
	   
    }
	
	$msg = $AppUI->_('Was imported')." ".$cant_r." ".$AppUI->_('resources');
	
	@unlink($file);
	
	return $msg;
}


/**
 * Actualiza los recuros en la base de datos, si llego hasta esta funcion ya fueron validados 
 * los campos, controlar si el recurso existe por EMPRESA-NOMBRE-APELLIDO-EMAIL
 *
 * @param unknown_type $file = nombre de archivo
 * @param unknown_type $company = id de la empresa a la que pertenecen los recursos
 */
function update_resources($file_name,$company)
{
	global $AppUI,$upload_dir;
	
	$file = $upload_dir."/import/".$file_name;
	
	require_once("includes/phpexcelreader/Excel/reader.php");
	
	$data = new Spreadsheet_Excel_Reader();
	$data->setOutputEncoding('CP1251');
	$data->read($file);
	
	$cant = $data->sheets['0']['numRows']-1;
	
	if ($cant > 0)
    {

       $row = 2;
       $today = new CDate();
       $cant = 0;	
       
       while ($row<= $data->sheets['0']['numRows'])
	   {
	   	  $fields = $data->sheets['0']['cells'][$row];
	   	  $obj = new CUser();
	   	  
	   	  $user_first_name = $fields['2'];
	   	  $user_last_name = $fields['3'];
	   	  $user_company = $company;
	   	  $user_email = $fields['6'];
	   	  
	   	  // Traigo el id del usuario a actualizar  
	   	  $user_id = db_loadResult("SELECT user_id FROM users WHERE user_first_name= '$user_first_name' AND user_last_name='$user_last_name' AND user_company='$user_company' AND user_email = '$user_email' ");
	   	  
	   	  //echo "$row - $user_id <br>";
	   	  
	   	  if($user_id !="")
	   	  {
	   	  	$cant = $cant + 1;
	   	  	
	   	  	/* Actualizo datos correspondientes a la solapa "Personal" */
	   	 
	   	    $obj->user_id = $user_id;
	   	    $obj->user_type = $fields['1'];
	   	    $obj->user_first_name = $fields['2'];
	   	    $obj->user_last_name = $fields['3'];
	   	    $obj->user_company = $company;
	   	    $obj->user_department = $fields['4'];	
	   	    $obj->user_job_title = $fields['5'];
	   	    $obj->user_email = $fields['6'];
	   	    $obj->user_phone = $fields['7'];
	   	    $obj->user_home_phone = $fields['8'];
	   	    $obj->user_mobile = $fields['9'];
	   	    $obj->user_address1 = $fields['10'];
	   	    $obj->user_address2 =  $fields['11'];
	   	    $obj->user_city = $fields['12'];
	   	    $obj->user_state_id = $fields['13'];
	   	    $obj->user_zip =  $fields['14'];
	   	    $obj->user_country_id = $fields['15'];
	   	    $obj->user_im_type = $fields['16'];
	   	    $obj->user_im_id = $fields['17'];
	   	    $obj->user_birthday = $fields['18'];
		    $obj->user_owner = $AppUI->user_id;
		    $obj->user_signature = $fields['19'];
		    $obj->user_smtp = $fields['20'];
		    $obj->user_smtp_auth = $fields['21'];
		    $obj->user_smtp_use_pop_values = $fields['22'];
		    $obj->user_smtp_username = $fields['23'];
		    $obj->user_smtp_password = $fields['24'];
		    $obj->user_mail_server_port = $fields['25'];
		    $obj->user_pop3 = $fields['26'];
		    $obj->user_imap = $fields['27'];
		    $obj->user_email_user = $fields['28'];
		    $obj->user_email_password = $fields['29'];
		    $obj->user_webmail_autologin = $fields['30'];
		    $obj->user_cost_per_hour = $fields['31'];
		    $obj->start_time_am = $fields['32'];
		    $obj->end_time_am = $fields['33'];
		    $obj->start_time_pm = $fields['34'];
		    $obj->end_time_pm = $fields['35'];
		    $obj->daily_working_hours = $fields['36'];
		    $obj->access_level = $fields['38'];
		    $obj->date_updated = $today->format(FMT_DATETIME_MYSQL);
		    $obj->doctype = $fields['39'];
		    $obj->docnumber = $fields['40'];
		    $obj->maritalstate = $fields['41'];
		    $obj->nationality = $fields['42'];
		    $obj->children = $fields['43'];
		    $obj->taxidtype = $fields['44'];
		    $obj->taxidnumber = $fields['45'];
		    
		    if ($fields['46']=="")
		    {
		    # Si esta vacio se lo ingresa como no supervisado
		    $obj->timexp_supervisor = '-1';
		    }else{
		  	$obj->timexp_supervisor = $fields['46'];
		    }
		  
		    if ($fields['47']=="")
		    {
		    # Si esta vacio se lo ingresa como no supervisado
		    $obj->user_supervisor = '-1';
		    }else{
		  	$obj->user_supervisor = $fields['47'];
		    }
		  
		    $obj->user_input_date_company = $fields['49'];
		  
		    $msg_o = $obj->store();
		  
		    $legajo = $fields['48'];
		    $sql_personal = db_exec("UPDATE users SET legajo= '".$legajo."' WHERE user_id='".$user_id."'; ");
	   	    
		    
		    /* Guardo datos correspondientes a la solapa "Antecedentes laborales" */
		    
		    $hhrr_ant = db_loadResult("SELECT count(*) FROM hhrr_ant WHERE user_id ='$user_id' ");
		    
		    if ($hhrr_ant =='0')
		    {
		     $internal_company = $fields['50'];
	   	 
		   	  if ($internal_company !="")
		   	  {
		   	 	$company_al = $fields['51'];
		   	 	$area_external = $fields['52'];
		   	 	$area_internal = $fields['53'];
		   	 	$function_al = $fields['54'];
		   	 	$from_date_al = $fields['55'];
		   	 	$to_date_al = $fields['56'];
		   	 	$profit = $fields['57'];
		   	 	$reports = $fields['58'];
		   	 	$functional_area = $fields['59'];
		   	 	$level_managment = $fields['60'];
		   	 	
		   	 	$query_al = "INSERT INTO hhrr_ant (user_id, company, internal_company, area_external, area_internal, function, to_date, from_date, profit, reports, functional_area, level_management) 
		   	 	VALUES ('$user_id', '$company_al', '$internal_company', '$area_external', '$area_internal', '$function_al', '$to_date_al', '$from_date_al', '$profit', '$reports', '$functional_area', '$level_managment')";          
		   	 	$sql_al = db_exec($query_al);
		   	  }
		   	  
		    }
		    
		    
		    /* Guardo datos correspondientes a la solapa "Formacion profesional" */
		    $hhrr_fp = db_loadResult("SELECT count(*) FROM hhrr_education WHERE id_user ='$user_id' ");
	   	    
		    if ($hhrr_fp =='0')
		    {
		   	    $type_fp = $fields['70'];
		   	 
			   	 if ($type_fp !="")
			   	 {
			   	 	$level = $fields['61'];
			   	 	
			   	 	if ($type_fp == '0')
			   	 	{
			   	 	    $title = $fields['62'];
			   	 	}
			   	 	else {
			   	 		$title = $fields['63'];
			   	 	}
			   	 	
			   	 	$instit = $fields['64'];
			   	 	$status = $fields['65'];
			   	 	$s_date = $fields['66'];
			   	 	$end_date = $fields['67'];
			   	 	$seminary_type = $fields['68'];
			   	 	$seminary = $fields['69'];
			   	 	
			   	 	$query_fp = "INSERT INTO hhrr_education (id_user, level, title, instit, status, s_date, end_date, seminary_type, seminary, type) 
			   	 	VALUES 
			   	 	( '$user_id', '$level', '$title', '$instit','$status', '$s_date', '$end_date', '$seminary_type', '$seminary', '$type_fp')";
			   	 	$sql_fp = db_exec($query_fp);
			   	 }
		     }
		     
		    /* Guardo datos correspondientes a la solapa "Evaluacion y rendimiento" */
		    $hhrr_performance = db_loadResult("SELECT count(*) FROM hhrr_performance WHERE user_id ='$user_id' ");
		    
	   	    if ($hhrr_performance == '0')
	   	    {
		   	  $supervisor = $fields['75'];
		   	 
		   	  if ($supervisor != "")
		   	   {
		   	 	$from_date = $fields['71'];
		   	 	$to_date = $fields['72'];
		   	 	$performance = $fields['73'];
		   	 	$potential = $fields['74'];
		   	 	
		   	 	$query_er = "INSERT INTO hhrr_performance (user_id, from_date, to_date, performance, potential, supervisor) 
		   	 	VALUES 
		   	 	( '$user_id', '$from_date', '$to_date', '$performance', '$potential', '$supervisor') ";
		   	 	$sql_er = db_exec($query_er);
		   	   }
	   	    }
	   	 
	   	   /* Guardo datos correspondientes a la solapa "Compensaciones" */
	   	   $hhrr_comp = db_loadResult("SELECT count(*) FROM hhrr_comp WHERE hhrr_comp_user_id ='$user_id' ");
	       
	   	   if ($hhrr_comp =='0')
	   	   {
			   $hhrr_comp_remuneration = $fields['76'];
			   	 
			   if ($hhrr_comp_remuneration !="")
			   {
			   	 	$hhrr_comp_last_update_porc = $fields['77'];
			   	 	$hhrr_comp_last_update_date = $fields['78'];
			   	 	$hhrr_comp_gap_pc = $fields['79'];
			   	 	$hhrr_comp_last_reward = $fields['80'];
			   	 	$hhrr_comp_anual_remuneration = $fields['81'];
			   	 	$hhrr_comp_actual_benefits = $fields['82'];
			   	 	$hhrr_comp_gap_mer = $fields['83'];
			   	 	$Hhrr_comp_proposed_plan = $fields['84'];
			   	 	$hhrr_comp_last_update = $today->format(FMT_DATETIME_MYSQL);
			   	 	
			   	 	$query_comp = "INSERT INTO hhrr_comp ( hhrr_comp_user_id, hhrr_comp_remuneration, hhrr_comp_last_update_porc, hhrr_comp_last_update_date, hhrr_comp_gap_pc, hhrr_comp_last_reward, hhrr_comp_anual_remuneration, hhrr_comp_actual_benefits, hhrr_comp_gap_mer, Hhrr_comp_proposed_plan, hhrr_comp_last_update) 
			   	 	VALUES
			   	 	( '$user_id' , '$hhrr_comp_remuneration', '$hhrr_comp_last_update_porc', '$hhrr_comp_last_update_date', '$hhrr_comp_gap_pc', '$hhrr_comp_last_reward', '$hhrr_comp_anual_remuneration', '$hhrr_comp_actual_benefits', '$hhrr_comp_gap_mer', '$Hhrr_comp_proposed_plan', '$hhrr_comp_last_update') ";
			   	 	$sql_comp = db_exec($query_comp);
			   	 	
			   	}
	   	    }
	   	    
	   	   /* Guardo datos correspondientes a la solapa "Desarrollo" */
	   	 
	   	   $hhrr_dev_user_id = $user_id;
	   	   $hhrr_dev_eval_g_1 = $fields['86'];
	   	   $hhrr_dev_eval_g_S = $fields['87'];
	   	   $hhrr_dev_eval_t_1 = $fields['88'];
	   	   $hhrr_dev_eval_t_S = $fields['89'];
	   	   $hhrr_dev_sug = $fields['90'];
	   	   $hhrr_dev_rst = $fields['91'];
	   	   $hhrr_dev_rmt = $fields['92'];
	   	   $hhrr_dev_rlt = $fields['93'];
	   	   $hhrr_dev_pos_k = $fields['94'];
	   	   $hhrr_dev_per_k = $fields['95'];
	   	   $hhrr_dev_mov_af1 = $fields['96'];
	   	   $hhrr_dev_mov_asa1 = $fields['97'];
	   	   $hhrr_dev_mov_af2 = $fields['98'];
	   	   $hhrr_dev_mov_asa2 = $fields['99'];
	   	   $hhrr_dev_mov_af3 = $fields['100'];
	   	   $hhrr_dev_mov_asa3 = $fields['101'];
	   	   $hhrr_dev_int_a = $fields['102'];
	   	   $hhrr_dev_exp = $fields['103'];
	   	 
	   	   $query_dev = "INSERT INTO hhrr_dev (hhrr_dev_user_id, hhrr_dev_eval_g_1, hhrr_dev_eval_g_S, hhrr_dev_eval_t_1, hhrr_dev_eval_t_S, hhrr_dev_sug, hhrr_dev_rst, hhrr_dev_rmt, hhrr_dev_rlt, hhrr_dev_pos_k, hhrr_dev_per_k, hhrr_dev_mov_af1, hhrr_dev_mov_asa1, hhrr_dev_mov_af2, hhrr_dev_mov_asa2, hhrr_dev_mov_af3, hhrr_dev_mov_asa3, hhrr_dev_int_a, hhrr_dev_exp) 
	   	   VALUES 
	   	   ('$hhrr_dev_user_id', '$hhrr_dev_eval_g_1', '$hhrr_dev_eval_g_S', '$hhrr_dev_eval_t_1', '$hhrr_dev_eval_t_S', '$hhrr_dev_sug', '$hhrr_dev_rst', '$hhrr_dev_rmt', '$hhrr_dev_rlt', '$hhrr_dev_pos_k', '$hhrr_dev_per_k', '$hhrr_dev_mov_af1', '$hhrr_dev_mov_asa1', '$hhrr_dev_mov_af2', '$hhrr_dev_mov_asa2', '$hhrr_dev_mov_af3', '$hhrr_dev_mov_asa3', '$hhrr_dev_int_a', '$hhrr_dev_exp')"; 
	   	   $sql_dev = db_exec($query_dev);
	   	  
	   	  
	   	   $hhrr_dev_pf_date = $fields['105'];
	   	  
	   	   if ($hhrr_dev_pf_date !="")
	   	   {
	   	    $hhrr_dev_pf_user_id = $user_id;
	   	    $hhrr_dev_pf_action = $fields['104'];
	   	    $hhrr_dev_pf_coment = $fields['106'];
	   	    $hhrr_dev_pf_aproved = $fields['107'];
	   	    $hhrr_dev_pf_status = $fields['108'];
	   	    
	   	    $query_dev_pf = "INSERT INTO hhrr_dev_pf ( hhrr_dev_pf_user_id, hhrr_dev_pf_action, hhrr_dev_pf_date, hhrr_dev_pf_coment, hhrr_dev_pf_aproved, hhrr_dev_pf_status) 
	   	     VALUES 
	   	    ( '$hhrr_dev_pf_user_id', '$hhrr_dev_pf_action', '$hhrr_dev_pf_date', '$hhrr_dev_pf_coment', '$hhrr_dev_pf_aproved', '$hhrr_dev_pf_status')";
	   	  
	   	     $sql_dev_pf = db_exec($query_dev_pf);
	   	   }
	   	 
	   	   /* Guardo datos correspondientes a la solapa "Matriz" */
	   	     
	   	   $idskill = $fields['109'];
	   	     
	   	   if ($idskill != "")
	   	   {
	   	   	
	   	   	 $sql_mz = db_loadResult("SELECT count(*) FROM hhrrskills WHERE user_id ='$user_id' AND idskill='$idskill' ");
	   	   	 
	   	   	 $value = $fields['110'];
	   	     $comment = $fields['111'];
	   	     $lastuse = $fields['112'];
	   	     $monthsofexp = $fields['113'];
	   	     
	   	   	 if($sql_mz == '0')
	   	   	 {
	   	   	   $query_matriz = "INSERT INTO hhrrskills (user_id, idskill, value, comment, lastuse, monthsofexp) VALUES ( '$user_id', '$idskill', '$value', '$comment', '$lastuse', '$monthsofexp')";
	   	   	 }else{
	   	   	   $query_matriz = "UPDATE hhrrskills SET value= '$value', comment='$comment', lastuse ='$lastuse', monthsofexp ='$monthsofexp'  WHERE user_id ='$user_id' AND idskill='$idskill' ";
	   	   	 }
	   	     
	   	     $sql_matriz = db_exec($query_matriz);
	   	     
	   	   }
		     
	   	    /* ------------------------------------------------------*/
	   	  }
	   	 
	   	  $row = $row + 1;
	   	 
	   }
	   
    }
	
    $msg = "Se actualizaron ".$cant." recursos";
    @unlink($file); 
    
    return $msg;
	
}

/**
 * Determina los permisos sobre el modulo de recursos humanos.
 * Para importar recursos necesita permisos de lectura y escritura sobre todos los tabs del departamento 
 * para el que va a realizar la importación.
 *
 * @param unknown_type $company, id de la empresa sobre la que se realizará la importación
 * @param unknown_type $department, id del departamento, si se ingresa -1 se verifica si tiene permisos
 *                                  sobre todos los departamentos de la empresa $company
 *
 */

function permission_hhrr($company,$department,$gral=null)
{
	global $AppUI;
	
    $user = $AppUI->user_id;
    
    if($AppUI->user_type=='1')
    {
    	$retorno = "-1";  // Permiso total, superadmin
    	return $retorno;
    }
    
    if($gral=='1')
    {   
    	 
    	// Permisos a nivel gral para saber si el usuario tiene permisos al menos sobre 1 dpto de la empresa
    	$query_gral = "SELECT * FROM hhrr_permissions
                       WHERE id_user = '".$user."' AND company='".$company."'  and
                       (personal + matrix + work_experience + education + performance_management + compensations + development) = -7";
    	
    	$sql_gral = db_exec($query_gral);
    	$row = mysql_fetch_array($sql_gral);
    	$cant = $row[0];
    	
    	if($cant > 0)
    	{
    		$retorno = "-1";  // Tiene permiso de lectura-escritura para al menos 1 dpto de la empresa
    		
    	}else{
    		$retorno = "0";  // Permiso denegado, no tiene ni un departamento de la empresa con permisos de lectura y escritura
    	}
    	
    	return $retorno;
    	
    }else{
    	 
    	// Aca entra cuando es a nivel particular, ingresa el id de una empresa, en caso de que sean todas ingresa -1.
    	
    	# Primero verifico si tiene permiso de lectura-escritura para todos los departamentos
    	$query_part = "SELECT count(*) FROM hhrr_permissions
                       WHERE id_user = '".$user."' AND company='".$company."'  and department= '-1' and
                       (personal + matrix + work_experience + education + performance_management + compensations + development) = -7";
    	//echo $query_part;
    	$sql_part = db_exec($query_part);
    	$row_part = mysql_fetch_array($sql_part);
    	$cant_all = $row_part[0];
    	
    	# Busco los permisos sobre el departamento en particular
    	$query_dept  = "SELECT count(*) FROM hhrr_permissions
                        WHERE id_user = '".$user."' AND company='".$company."'  
                        and department= '$department' ";
    	$sql_dept = db_exec($query_dept);
    	$row_dept = mysql_fetch_array($sql_dept);
    	$cant_dept = $row_dept[0];
    	
    	if ($cant_dept == 0)
    	{
    		// Si no tiene permisos sobre el dpto en particular me fijo si tiene permisos sobre todos
    		if($cant_all > 0)
    		{
    			 $retorno = "-1";  // Tiene permiso de lectura-escritura para todos los deptos de la empresa
    		}
    		else
    		{
    			 $retorno = "0";  // Permiso denegado, no tiene decalrado ningun permiso sobre la empresa
    			 
    		}
    	}
    	else
    	{  
    		 $retorno = -1;
    		 
    		 $query_depty  = "SELECT * FROM hhrr_permissions
                        WHERE id_user = '".$user."' AND company='".$company."'  
                        and department= '$department' ";
    	     $sql_depty = db_exec($query_depty);
    	    
    		 // Si tiene permisos sobre departamentos verifico la suma
    		 while ($list= mysql_fetch_array($sql_depty))
			    {
			    	 $personal = -1 * $list['personal'];
			    	 $matriz = -1 * $list['matrix'];
			    	 $work_experience = -1 * $list['work_experience'];
			    	 $education = -1 * $list['education'];
			    	 $performance_management = -1 * $list['performance_management'];
			    	 $compensations = -1 * $list['compensations'];
			    	 $development = - 1 * $list['development'];
			    	 
			    	 
			    	 $suma = $personal + $matriz + $work_experience + $education + $performance_management + $compensations + $development;
			    	 
			    	 if($suma < 7)
			    	 {
			    	   $retorno = "1"; //  el usuario no tiene permisos de lectura-escritura sobre el departamento.	               
			    	 }
			    	 
			    }
    	}
    	
    }
    
    return $retorno;
    
    
} 


?>