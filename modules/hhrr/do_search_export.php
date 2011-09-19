<?php

function xlsBOF()
{
echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
return;
}
// Excel end of file footer
function xlsEOF()
{ 
echo pack("ss", 0x0A, 0x00);
return;
}
// Function to write a Number (double) into Row, Col
function xlsWriteNumber($Row, $Col, $Value)
{
echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
echo pack("d", $Value);
return;
}
// Function to write a label (text) into Row, Col
function xlsWriteLabel($Row, $Col, $Value )
{
$L = strlen($Value);
echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
echo $Value;
return;
}

/*header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel");
header ("Content-Disposition: attachment; filename=hhrr_search_result.xls" );
header ("Content-Description: PHP/INTERBASE Generated Data" ); */

header( 'Content-type: application/ms-excel' );
header( 'Content-Disposition: attachment; filename="hhrr_search_result.xls"' );


// XLS Data Cell

xlsBOF();
xlsWriteLabel(0,0,"user_type");
xlsWriteLabel(0,1,"user_first_name");
xlsWriteLabel(0,2,"user_last_name");	
xlsWriteLabel(0,3,"user_department");	
xlsWriteLabel(0,4,"user_job_title");	
xlsWriteLabel(0,5,"user_email");	
xlsWriteLabel(0,6,"user_phone");	
xlsWriteLabel(0,7,"user_home_phone");	
xlsWriteLabel(0,8,"user_mobile");	
xlsWriteLabel(0,9,"user_address1");	
xlsWriteLabel(0,10,"user_address2");	
xlsWriteLabel(0,11,"user_city");	
xlsWriteLabel(0,12,"user_state_id");	
xlsWriteLabel(0,13,"user_zip");	
xlsWriteLabel(0,14,"user_country_id");	
xlsWriteLabel(0,15,"user_im_type");	
xlsWriteLabel(0,16,"user_im_id");	
xlsWriteLabel(0,17,"user_birthday");	
xlsWriteLabel(0,18,"user_signature");	
xlsWriteLabel(0,19,"user_smtp");	
xlsWriteLabel(0,20,"user_smtp_auth");	
xlsWriteLabel(0,21,"user_smtp_use_pop_values");	
xlsWriteLabel(0,22,"user_smtp_username");	
xlsWriteLabel(0,23,"user_smtp_password");	
xlsWriteLabel(0,24,"user_mail_server_port");	
xlsWriteLabel(0,25,"user_pop3");	
xlsWriteLabel(0,26,"user_imap");	
xlsWriteLabel(0,27,"user_email_user");	
xlsWriteLabel(0,28,"user_email_password");	
xlsWriteLabel(0,29,"user_webmail_autologin");	
xlsWriteLabel(0,30,"user_cost_per_hour");	
xlsWriteLabel(0,31,"start_time_am");	
xlsWriteLabel(0,32,"end_time_am");	
xlsWriteLabel(0,33,"start_time_pm");	
xlsWriteLabel(0,34,"end_time_pm");	
xlsWriteLabel(0,35,"daily_working_hours");	
xlsWriteLabel(0,36,"protected");	
xlsWriteLabel(0,37,"access_level");	
xlsWriteLabel(0,38,"doctype");	
xlsWriteLabel(0,39,"docnumber");	
xlsWriteLabel(0,40,"maritalstate");	
xlsWriteLabel(0,41,"nationality");	
xlsWriteLabel(0,42,"children");	
xlsWriteLabel(0,43,"taxidtype");	
xlsWriteLabel(0,44,"taxidnumber");	
xlsWriteLabel(0,45,"costperhour");	
xlsWriteLabel(0,46,"actuljob");	
xlsWriteLabel(0,47,"actualcompany");	
xlsWriteLabel(0,48,"workinghours");	
xlsWriteLabel(0,49,"hoursavailableperday");	
xlsWriteLabel(0,50,"wantsfulltime");	
xlsWriteLabel(0,51,"wantspartime");	
xlsWriteLabel(0,52,"wantsfreelance");	
xlsWriteLabel(0,53,"salarywanted");	
xlsWriteLabel(0,54,"wasinterview");	
xlsWriteLabel(0,55,"candidatestatus");	
xlsWriteLabel(0,56,"timexp_supervisor");	
xlsWriteLabel(0,57,"user_supervisor");	
xlsWriteLabel(0,58,"legajo");	
xlsWriteLabel(0,59,"user_input_date_company");	
xlsWriteLabel(0,60,"internal_company");	
xlsWriteLabel(0,61,"company");	
xlsWriteLabel(0,62,"area_external");	
xlsWriteLabel(0,63,"area_internal");	
xlsWriteLabel(0,64,"function");	
xlsWriteLabel(0,65,"from_date");	
xlsWriteLabel(0,66,"to_date");	
xlsWriteLabel(0,67,"profit");	
xlsWriteLabel(0,68,"reports");	
xlsWriteLabel(0,69,"functional_area");	
xlsWriteLabel(0,70,"level_managment");	
xlsWriteLabel(0,71,"level");	
xlsWriteLabel(0,72,"title");	
xlsWriteLabel(0,73,"activity");
xlsWriteLabel(0,74,"instit");	
xlsWriteLabel(0,75,"status");	
xlsWriteLabel(0,76,"s_date");	
xlsWriteLabel(0,77,"end_date");	
xlsWriteLabel(0,78,"program_type");	
xlsWriteLabel(0,79,"program");	
xlsWriteLabel(0,80,"type");	
xlsWriteLabel(0,81,"from_date");	
xlsWriteLabel(0,82,"to_date");	
xlsWriteLabel(0,83,"performance");	
xlsWriteLabel(0,84,"potential");	
xlsWriteLabel(0,85,"supervisor");	
xlsWriteLabel(0,86,"hhrr_comp_remuneration");	
xlsWriteLabel(0,87,"hhrr_comp_last_update_porc");	
xlsWriteLabel(0,88,"hhrr_comp_last_update_date");	
xlsWriteLabel(0,89,"hhrr_comp_gap_pc");	
xlsWriteLabel(0,90,"hhrr_comp_last_reward");	
xlsWriteLabel(0,91,"hhrr_comp_anual_remuneration");	
xlsWriteLabel(0,92,"hhrr_comp_actual_benefits");	
xlsWriteLabel(0,93,"hhrr_comp_gap_mer");	
xlsWriteLabel(0,94,"hhrr_comp_proposed_plan");	
xlsWriteLabel(0,95,"hhrr_comp_last_update");	
xlsWriteLabel(0,96,"hhrr_dev_eval_g_1");	
xlsWriteLabel(0,97,"hhrr_dev_eval_g_S");	
xlsWriteLabel(0,98,"hhrr_dev_eval_t_1");	
xlsWriteLabel(0,99,"hhrr_dev_eval_t_S");	
xlsWriteLabel(0,100,"hhrr_dev_sug");	
xlsWriteLabel(0,101,"hhrr_dev_rst");		
xlsWriteLabel(0,102,"hhrr_dev_rmt");		
xlsWriteLabel(0,103,"hhrr_dev_rlt");	
xlsWriteLabel(0,104,"hhrr_dev_pos_k");	
xlsWriteLabel(0,105,"hhrr_dev_per_k");	
xlsWriteLabel(0,106,"hhrr_dev_mov_af1");	
xlsWriteLabel(0,107,"hhrr_dev_mov_asa1");	
xlsWriteLabel(0,108,"hhrr_dev_mov_af2");	
xlsWriteLabel(0,109,"hhrr_dev_mov_asa2");	
xlsWriteLabel(0,110,"hhrr_dev_mov_af3");	
xlsWriteLabel(0,111,"hhrr_dev_mov_asa3");	
xlsWriteLabel(0,112,"hhrr_dev_int_a");	
xlsWriteLabel(0,113,"hhrr_dev_exp");	
xlsWriteLabel(0,114,"hhrr_dev_pf_action");	
xlsWriteLabel(0,115,"hhrr_dev_pf_date");	
xlsWriteLabel(0,116,"hhrr_dev_pf_coment");	
xlsWriteLabel(0,117,"hhrr_dev_pf_aproved");	
xlsWriteLabel(0,118,"hhrr_dev_pf_status");	
xlsWriteLabel(0,119,"idskills");	
xlsWriteLabel(0,120,"value");	
xlsWriteLabel(0,121,"comment");	
xlsWriteLabel(0,122,"lastuse");	
xlsWriteLabel(0,123,"monthsofexp");


$xlsRow = 1;

$result = substr ($_POST['result'], 1); 

$query = "SELECT user_id, user_username, user_parent, user_type, user_first_name, user_last_name, user_company, companies.company_name, user_department, departments.dept_name, user_job_title, user_email, user_phone, user_home_phone, user_mobile, user_address1, user_address2, user_city, user_state_id, location_states.state_name, user_state, user_zip, user_country_id, location_countries.country_name, user_country, user_im_type, user_im_id, 
date_format(user_birthday, '%d-%m-%Y') as user_birthday, user_signature, user_smtp, user_smtp_auth, user_smtp_use_pop_values, user_smtp_username, user_smtp_password, user_mail_server_port, user_pop3, user_imap, user_email_user, user_email_password, user_webmail_autologin, user_cost_per_hour, user_status, date_format(users.start_time_am, '%H:%m') as start_time_am, date_format(users.end_time_am, '%H:%m') as end_time_am,
user_status, date_format(users.start_time_pm, '%H:%m') as start_time_pm, date_format(users.end_time_pm, '%H:%m') as end_time_pm,
daily_working_hours, enabled, protected, access_level, doctype, docnumber, maritalstate, nationality, location_nationalities.description_es, children, taxidtype, taxidnumber, resume, costperhour, actualjob, actualcompany, workinghours, hoursavailableperday, wantsfulltime, wantsparttime, wantsfreelance, salarywanted, wasinterviewed, candidatestatus, timexp_supervisor, user_supervisor, legajo, date_format( user_input_date_company, '%d-%m-%Y') as user_input_date_company
FROM users
LEFT JOIN departments ON departments.dept_id = user_department AND user_company<> '0'
LEFT JOIN companies ON companies.company_id = user_company
LEFT JOIN location_countries ON location_countries.country_id = user_country_id
LEFT JOIN location_nationalities ON location_nationalities.nationality_id = nationality
LEFT JOIN location_states ON location_states.state_id = user_state_id AND location_states.country_id = user_country_id
WHERE user_id IN (".$result.") ";


$list = db_loadList( $query );

//echo "<pre>";print_r($query);echo "</pre>";

// Recorro el vector y voy armando la tabla
$cant = count($list) - 1 ;


for ($i=0;$i<=$cant;$i++)
{
	//Datos personales
    xlsWriteLabel($xlsRow,0,$list[$i]['user_type']);
	xlsWriteLabel($xlsRow,1,$list[$i]['user_first_name']);
	xlsWriteLabel($xlsRow,2,$list[$i]['user_last_name']);	
	xlsWriteLabel($xlsRow,3,$list[$i]['dept_name']);	
	xlsWriteLabel($xlsRow,4,$list[$i]['user_job_title']);	
	xlsWriteLabel($xlsRow,5,$list[$i]['user_email']);	
	xlsWriteLabel($xlsRow,6,$list[$i]['user_phone']);	
	xlsWriteLabel($xlsRow,7,$list[$i]['user_home_phone']);	
	xlsWriteLabel($xlsRow,8,$list[$i]['user_mobile']);	
	xlsWriteLabel($xlsRow,9,$list[$i]['user_address1']);	
	xlsWriteLabel($xlsRow,10,$list[$i]['user_address2']);	
	xlsWriteLabel($xlsRow,11,$list[$i]['user_city']);	
	xlsWriteLabel($xlsRow,12,$list[$i]['state_name']);	
	xlsWriteLabel($xlsRow,13,$list[$i]['user_zip']);	
	xlsWriteLabel($xlsRow,14,$list[$i]['country_name']);	
	xlsWriteLabel($xlsRow,15,$list[$i]['user_im_type']);	
	xlsWriteLabel($xlsRow,16,$list[$i]['user_im_id']);	
	xlsWriteLabel($xlsRow,17,$list[$i]['user_birthday']);	
	xlsWriteLabel($xlsRow,18,$list[$i]['user_signature']);	
	xlsWriteLabel($xlsRow,19,$list[$i]['user_smtp']);	
	xlsWriteLabel($xlsRow,20,$list[$i]['user_smtp_auth']);	
	xlsWriteLabel($xlsRow,21,$list[$i]['user_smtp_use_pop_values']);	
	xlsWriteLabel($xlsRow,22,$list[$i]['user_smtp_username']);	
	xlsWriteLabel($xlsRow,23,$list[$i]['user_smtp_password']);	
	xlsWriteLabel($xlsRow,24,$list[$i]['user_mail_server_port']);	
	xlsWriteLabel($xlsRow,25,$list[$i]['user_pop3']);	
	xlsWriteLabel($xlsRow,26,$list[$i]['user_imap']);	
	xlsWriteLabel($xlsRow,27,$list[$i]['user_email_user']);	
	xlsWriteLabel($xlsRow,28,$list[$i]['user_email_password']);	
	xlsWriteLabel($xlsRow,29,$list[$i]['user_webmail_autologin']);	
	xlsWriteLabel($xlsRow,30,$list[$i]['user_cost_per_hour']);	
	xlsWriteLabel($xlsRow,31,$list[$i]['start_time_am']);	
	xlsWriteLabel($xlsRow,32,$list[$i]['end_time_am']);	
	xlsWriteLabel($xlsRow,33,$list[$i]['start_time_pm']);	
	xlsWriteLabel($xlsRow,34,$list[$i]['end_time_pm']);	
	xlsWriteLabel($xlsRow,35,$list[$i]['daily_working_hours']);	
	xlsWriteLabel($xlsRow,36,$list[$i]['protected']);	
	xlsWriteLabel($xlsRow,37,$list[$i]['access_level']);	
	xlsWriteLabel($xlsRow,38,$list[$i]['doctype']);	
	xlsWriteLabel($xlsRow,39,$list[$i]['docnumber']);	
	xlsWriteLabel($xlsRow,40,$list[$i]['maritalstate']);	
	xlsWriteLabel($xlsRow,41,$list[$i]['description_es']);	
	xlsWriteLabel($xlsRow,42,$list[$i]['children']);	
	xlsWriteLabel($xlsRow,43,$list[$i]['taxidtype']);	
	xlsWriteLabel($xlsRow,44,$list[$i]['taxidnumber']);	
	xlsWriteLabel($xlsRow,45,$list[$i]['costperhour']);	
	xlsWriteLabel($xlsRow,46,$list[$i]['actuljob']);	
	xlsWriteLabel($xlsRow,47,$list[$i]['actualcompany']);	
	xlsWriteLabel($xlsRow,48,$list[$i]['workinghours']);	
	xlsWriteLabel($xlsRow,49,$list[$i]['hoursavailableperday']);	
	xlsWriteLabel($xlsRow,50,$list[$i]['wantsfulltime']);	
	xlsWriteLabel($xlsRow,51,$list[$i]['wantspartime']);	
	xlsWriteLabel($xlsRow,52,$list[$i]['wantsfreelance']);	
	xlsWriteLabel($xlsRow,53,$list[$i]['salarywanted']);	
	xlsWriteLabel($xlsRow,54,$list[$i]['wasinterview']);	
	xlsWriteLabel($xlsRow,55,$list[$i]['candidatestatus']);	
	xlsWriteLabel($xlsRow,56,$list[$i]['timexp_supervisor']);	
	xlsWriteLabel($xlsRow,57,$list[$i]['user_supervisor']);	
	xlsWriteLabel($xlsRow,58,$list[$i]['legajo']);	
	xlsWriteLabel($xlsRow,59,$list[$i]['user_input_date_company']);	
	
	
	// Antecedentes laborales
	
	$query_ant = "SELECT *, date_format(from_date, '%d-%m-%Y') as f_date, date_format(to_date, '%d-%m-%Y') as t_date  FROM hhrr_ant WHERE user_id='".$list[$i]['user_id']."' order by id desc limit 1";
	$sql_ant = db_exec($query_ant);
	$row_ant = mysql_fetch_array($sql_ant);
	
	if (count($row_ant)>0)
	{
		xlsWriteLabel($xlsRow,60,$row_ant['internal_company']);	
		xlsWriteLabel($xlsRow,61,$row_ant['company']);	
		xlsWriteLabel($xlsRow,62,$row_ant['area_external']);	
		xlsWriteLabel($xlsRow,63,$row_ant['area_internal']);	
		xlsWriteLabel($xlsRow,64,$row_ant['function']);	
		xlsWriteLabel($xlsRow,65,$row_ant['f_date']);	
		xlsWriteLabel($xlsRow,66,$row_ant['t_date']);	
		xlsWriteLabel($xlsRow,67,$row_ant['profit']);	
		xlsWriteLabel($xlsRow,68,$row_ant['reports']);	
		xlsWriteLabel($xlsRow,69,$row_ant['functional_area']);	
		xlsWriteLabel($xlsRow,70,$row_ant['level_managment']);	
	}
	
	// Formacion profesional
	
	$query_edu = "SELECT hhrr_education.*, date_format(hhrr_education.s_date, '%d-%m-%Y') as f_date, date_format(hhrr_education.end_date, '%d-%m-%Y') as t_date,  hhrr_academic_level.name_es as name_level, hhrr_education_program.name as program, hhrr_education_title.name_es as title_name FROM hhrr_education 
	LEFT JOIN hhrr_academic_level ON hhrr_academic_level.level = hhrr_education.level
	LEFT JOIN hhrr_education_title ON hhrr_education_title.title_id = hhrr_education.title
	LEFT JOIN hhrr_education_program ON hhrr_education_program.program_id = hhrr_education.level
	WHERE id_user ='".$list[$i]['user_id']."' and hhrr_education.type='0' order by hhrr_academic_level.level desc limit 1";
	$sql_edu = db_exec($query_edu);
	$row_edu = mysql_fetch_array($sql_edu);
	
	if(count($row_edu)>0)
	{
		if ($row_edu['type']=='0')
		{  
			xlsWriteLabel($xlsRow,71,$row_edu['name_level']);
		    xlsWriteLabel($xlsRow,72,$row_edu['title_name']);
		}else{
			//xlsWriteLabel($xlsRow,71,$row_edu['program']);
			xlsWriteLabel($xlsRow,73,$row_edu['title']);
		}
		
			
		xlsWriteLabel($xlsRow,74,$row_edu['instit']);
		
		if ($row_edu['type']=='0'){
			
			if($row_edu['status']=='0'){
		     xlsWriteLabel($xlsRow,75,$AppUI->_("Incomplete"));	
			}
			if($row_edu['status']=='1'){
		     xlsWriteLabel($xlsRow,75,$AppUI->_("Completed"));	
			}
			if($row_edu['status']=='2'){
		     xlsWriteLabel($xlsRow,75,$AppUI->_("On Course"));	
			}
		}else{
		   //xlsWriteLabel($xlsRow,75,$row_edu['status']);
		}
		
		xlsWriteLabel($xlsRow,76,$row_edu['f_date']);	
		xlsWriteLabel($xlsRow,77,$row_edu['t_date']);	
		
		if ($row_edu['type']=='1'){
			
			if ($row_edu['seminary_type'] == '0'){
			   xlsWriteLabel($xlsRow,78,$AppUI->_("Local"));	
			}
			if ($row_edu['seminary_type'] == '1'){
			   xlsWriteLabel($xlsRow,78,$AppUI->_("In-Company"));	
			}
			if ($row_edu['seminary_type'] == '2'){
			   xlsWriteLabel($xlsRow,78,$AppUI->_("Exterior"));	
			}
			
			xlsWriteLabel($xlsRow,79,$row_edu['program']);	
		}
		
	    
		if ($row_edu['type']=='1'){
		  xlsWriteLabel($xlsRow,80,$AppUI->_("Training"));
		}
		if ($row_edu['type']=='0'){
		  xlsWriteLabel($xlsRow,80,$AppUI->_("Formal Education"));
		}
		
	}
	
	
	// Desempenio y potencial
	$query_dyp = "SELECT *, date_format(from_date, '%d-%m-%Y') as f_date, date_format(to_date, '%d-%m-%Y') as t_date FROM hhrr_performance WHERE user_id='".$list[$i]['user_id']."' order by id desc limit 1";
	$sql_dyp = db_exec($query_dyp);
	$row_dyp = mysql_fetch_array($sql_dyp);
	
	if(count($row_dyp))
	{
		xlsWriteLabel($xlsRow,81,$row_dyp['f_date']);	
		xlsWriteLabel($xlsRow,82,$row_dyp['t_date']);	
		xlsWriteLabel($xlsRow,83,$row_dyp['performance']);	
		xlsWriteLabel($xlsRow,84,$row_dyp['potential']);	
		xlsWriteLabel($xlsRow,85,$row_dyp['supervisor']);	
	}
	
	
	// Compensaciones
	$query_comp = "SELECT *, date_format(hhrr_comp_last_update_date, '%d-%m-%Y') as last_upadate_date, date_format(hhrr_comp_last_update, '%d-%m-%Y %H:%i:%s') as comp_last_update FROM hhrr_comp WHERE hhrr_comp_user_id='".$list[$i]['user_id']."' order by hhrr_comp_id desc limit 1";
	$sql_comp = db_exec($query_comp);
	$row_comp = mysql_fetch_array($sql_comp);
	
	if(count($row_comp))
	{
		xlsWriteLabel($xlsRow,86,$row_comp['hhrr_comp_remuneration']);	
		xlsWriteLabel($xlsRow,87,$row_comp['hhrr_comp_last_update_porc']);	
		xlsWriteLabel($xlsRow,88,$row_comp['last_upadate_date']);	
		xlsWriteLabel($xlsRow,89,$row_comp['hhrr_comp_gap_pc']);	
		xlsWriteLabel($xlsRow,90,$row_comp['hhrr_comp_last_reward']);	
		xlsWriteLabel($xlsRow,91,$row_comp['hhrr_comp_anual_remuneration']);	
		xlsWriteLabel($xlsRow,92,$row_comp['hhrr_comp_actual_benefits']);	
		xlsWriteLabel($xlsRow,93,$row_comp['hhrr_comp_gap_mer']);	
		xlsWriteLabel($xlsRow,94,$row_comp['hhrr_comp_proposed_plan']);	
		xlsWriteLabel($xlsRow,95,$row_comp['comp_last_update']);
	}
	
	
	// Desarrollo
	$query_dev = "SELECT * FROM hhrr_dev WHERE hhrr_dev_user_id='".$list[$i]['user_id']."' ";
	$sql_dev = db_exec($query_dev);
	$row_dev = mysql_fetch_array($sql_dev);
	
	if(count($row_dev))
	{
		xlsWriteLabel($xlsRow,96,$row_dev['hhrr_dev_eval_g_1']);	
		xlsWriteLabel($xlsRow,97,$row_dev['hhrr_dev_eval_g_S']);	
		xlsWriteLabel($xlsRow,98,$row_dev['hhrr_dev_eval_t_1']);	
		xlsWriteLabel($xlsRow,99,$row_dev['hhrr_dev_eval_t_S']);	
		xlsWriteLabel($xlsRow,100,$row_dev['hhrr_dev_sug']);	
		xlsWriteLabel($xlsRow,101,$row_dev['hhrr_dev_rst']);		
		xlsWriteLabel($xlsRow,102,$row_dev['hhrr_dev_rmt']);		
		xlsWriteLabel($xlsRow,103,$row_dev['hhrr_dev_rlt']);	
		xlsWriteLabel($xlsRow,104,$row_dev['hhrr_dev_pos_k']);	
		xlsWriteLabel($xlsRow,105,$row_dev['hhrr_dev_per_k']);	
		xlsWriteLabel($xlsRow,106,$row_dev['hhrr_dev_mov_af1']);	
		xlsWriteLabel($xlsRow,107,$row_dev['hhrr_dev_mov_asa1']);	
		xlsWriteLabel($xlsRow,108,$row_dev['hhrr_dev_mov_af2']);	
		xlsWriteLabel($xlsRow,109,$row_dev['hhrr_dev_mov_asa2']);	
		xlsWriteLabel($xlsRow,110,$row_dev['hhrr_dev_mov_af3']);	
		xlsWriteLabel($xlsRow,111,$row_dev['hhrr_dev_mov_asa3']);	
		xlsWriteLabel($xlsRow,112,$row_dev['hhrr_dev_int_a']);	
		xlsWriteLabel($xlsRow,113,$row_dev['hhrr_dev_exp']);	

	}
	
	// Plan Desarrollo individual
	$query_dev_pf = "SELECT *, date_format(hhrr_dev_pf_date, '%d-%m-%Y') as dev_pf_date FROM hhrr_dev_pf WHERE hhrr_dev_pf_user_id='".$list[$i]['user_id']."' order by hhrr_dev_pf_id desc limit 1";
	$sql_dev_pf = db_exec($query_dev_pf);
	$row_dev_pf = mysql_fetch_array($sql_dev_pf);
	
	if(count($row_dev_pf))
	{
		xlsWriteLabel($xlsRow,114,$row_dev_pf['hhrr_dev_pf_action']);	
		xlsWriteLabel($xlsRow,115,$row_dev_pf['dev_pf_date']);	
		xlsWriteLabel($xlsRow,116,$row_dev_pf['hhrr_dev_pf_coment']);	
		xlsWriteLabel($xlsRow,117,$row_dev_pf['hhrr_dev_pf_aproved']);	
		xlsWriteLabel($xlsRow,118,$row_dev_pf['hhrr_dev_pf_status']);
	}
	
	//Matriz
	// Plan Desarrollo individual
	$query_skill = "SELECT *,date_format(lastuse, '%d-%m-%Y') as last_use FROM hhrrskills WHERE user_id='".$list[$i]['user_id']."' order by id desc limit 1";
	$sql_skill = db_exec($query_skill);
	$row_skill = mysql_fetch_array($sql_skill);
	
	if(count($row_skill))
	{
		xlsWriteLabel($xlsRow,119,$row_skill['idskill']);	
		xlsWriteLabel($xlsRow,120,$row_skill['value']);	
		xlsWriteLabel($xlsRow,121,$row_skill['comment']);	
		xlsWriteLabel($xlsRow,122,$row_skill['last_use']);	
		xlsWriteLabel($xlsRow,123,$row_skill['monthsofexp']);
	}
	

    $xlsRow++;

}


xlsEOF();
exit();

?>
