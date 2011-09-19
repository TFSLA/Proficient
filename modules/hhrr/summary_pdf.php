<?php

function generar_pdf_resumen($id)
{
	global $AppUI;

	include_once('./modules/admin/admin.class.php');

	$maritalstates= dPgetSysVal("MaritalState");
	$IMTypes= dPgetSysVal("IMType");
	$SCandidateStatus = dPgetSysVal("CandidateStatus");

	$uploads_dir = $AppUI->getConfig('hhrr_uploads_dir');

	$result = mysql_query("SELECT users.*, departments.dept_name, companies.company_name   FROM users
	LEFT JOIN companies ON users.user_company = companies.company_id
	LEFT JOIN departments ON users.user_department = departments.dept_id
	WHERE user_id = $id;"); 
	
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	$id = $row["user_id"];
	$firstname = $row["user_first_name"];
	$lastname = $row["user_last_name"];
	$birthday = $row["user_birthday"];
	$doctype = $row["doctype"];
	$docnumber = $row["docnumber"];
	$maritalstate = $row["maritalstate"];
	$nationality = $row["nationality"];
	$email = $row["user_email"];
	$phone = $row["user_phone"];
	$homephone = $row["user_home_phone"];
	$cellphone = $row["user_mobile"];
	$address = $row["user_address1"];
	$city = $row["user_city"];
	$zip = $row["user_zip"];
	$country_id = $row["user_country_id"];
	$country = $AppUI->_(CUser::getUserCountry($id, $country_id));
	$state_id = $row["user_state_id"];
	$state = $AppUI->_(CUser::getUserState($id, $country_id, $state_id));
	$children = $row["children"];
	$url = $row["url"];
	$taxidtype = $row["taxidtype"];
	$taxidnumber = $row["taxidnumber"];
	$im_type = $row["user_im_type"];
	$im_id = $row["user_im_id"];
	$resume = $row["resume"];
	$logo = $row["user_pic"];
	$inputdate = $row["date_created"];
	$updateddate = $row["date_updated"];
	$costperhour = $row["costperhour"];
	$actualjob = $row["actualjob"];
	$actualcompany = $row["actualcompany"];
	$workinghours = $row["workinghours"];
	$salarywanted = $row["salarywanted"];
	$wantsfreelance = $row["wantsfreelance"];
	$wantsfulltime = $row["wantsfulltime"];
	$wantsparttime = $row["wantsparttime"];
	$wasinterviewed = $row["wasinterviewed"];
	$hoursavailableperday = $row["hoursavailableperday"];
	$candidatestatus = $row["candidatestatus"];
	$company_name =  $row["company_name"];
	$dept_name =  $row["dept_name"];
	$user_job_title =  $row["user_job_title"];
  $user_input_date_company = $row["user_input_date_company"];

	$candidato = ($row['user_type'] == 5);

	/*
	$start_time_am = $row["start_time_am"] ? new CDate( "0000-00-00 ".$row["start_time_am"] ) : null;
	$end_time_am = $row["end_time_am"]  ? new CDate( "0000-00-00 ".$row["end_time_am"] ) : null;
	$start_time_pm = $row["start_time_pm"] ? new CDate( "0000-00-00 ".$row["start_time_pm"] ) : null;
	$end_time_pm = $row["end_time_pm"] ? new CDate( "0000-00-00 ".$row["end_time_pm"] ) : null;
	*/

	//Traigo el nombre del user_supervisor
	$sql = "SELECT concat(user_first_name,' ', user_last_name) AS user_supervisor  FROM users WHERE user_id = ".$row["user_supervisor"];
	$user_supervisor = db_loadResult($sql);

	if($candidato)
	{
		$WorkTypes = dPgetSysVal("WorkType");
		$actualjob = $AppUI->_($WorkTypes[$actualjob]);
	}

	$font_dir = $AppUI->getConfig( 'root_dir' )."/lib/ezpdf/fonts";
	$temp_dir = $AppUI->getConfig( 'root_dir' )."/files/temp";
	$base_url  = $AppUI->getConfig( 'base_url' );
	require_once( $AppUI->getLibraryClass( 'ezpdf/class.ezpdf' ) );

	$pdf =& new Cezpdf();
	$pdf->ezSetCmMargins( 1, 2, 1.5, 1.5 );
	$pdf->selectFont( "$font_dir/Helvetica-Bold.afm" );

	$pdf->ezStartPageNumbers(500,28,10,'','',1); //Con esto ponemos en numero de pagina en cada pagina

	$company_logo = FALSE;
	if (!$candidato)
	{
		/*Nos fijamos si la compania del usuario tiene cargado un logo.*/
		$sql="SELECT fbin_data, fname, fsize FROM companies WHERE company_id=".$row["user_company"];
		//echo $sql;
		$vec=db_fetch_array(db_exec($sql));
		$fdata = $vec['fbin_data'];
		$filename = $vec['fname'];

		/* Si la compania tiene cargado un logo, pasamos el archivo de la BD al file system*/
		if ($vec['fsize'] != 0)
		{
			//Las unicas extensiones posibles para generar el pdf son jpeg, jpg y png
			$ext = findexts($filename);
			if (strcasecmp($ext,"jpeg")==0 OR strcasecmp($ext,"jpg")==0 OR strcasecmp($ext,"png")==0)
			{
				$company_logo_path=$temp_dir."/".$filename;
			  if (!$handle = fopen($company_logo_path, 'wb')) {
			       echo "Cannot open file ($filename)";
			       exit;
			  }
			  if (fwrite($handle, $fdata) === FALSE) {
			      echo "Cannot write to file ($filename)";
			      exit;
			  }
			  fclose($handle);
			  $company_logo = TRUE;
			}
		}
	}
	/*********************************************/

	if ($company_logo)
	{
		$pdf->ezSetDy(115);//Vajo un poco la imagen
		$img_height=$pdf->ezImage($company_logo_path,130,100,'','right',0);
		unlink($company_logo_path);//Borramos el logo
		$pos_fin_tabla= $pdf->y;//Guardo en que lugar termino de imprimir la imagen

		//Con esto pongo la linea superior e inferior en LA PRIMERA PAGINA, dado que es distinta a todas las demas, va a el titulo sobre la linea*/
		$pdf->setStrokeColor(0,0,0,1);
		$pdf->line(20,$pdf->y,578,$pdf->y);//Linea superior
		$pdf->line(20,40,578,40);//Linea inferior
		//$pdf->addText(50,34,6,$AppUI->getConfig( 'company_name' )." - ".$AppUI->_("HHRR")." - ".$AppUI->_("Summary"));
		$pdf->addText(50,34,6,$company_name." - ".$AppUI->_("HHRR")." - ".$AppUI->_("Summary"));
		/*----------------------------------------------------------------------------------------------*/

		//Meto el titulo en una tabla para poder alinearlo y imprimo el logo
		$columns = null;
		$title =  null;
		$options = array(
			'showLines' => 0,
			'showHeadings' => 0,
			'fontSize' => 15,
			'rowGap' => 2,
			'colGap' => 5,
			'xPos' => 175,
			'xOrientation' => 'center',
			'width'=> 400,
			'cols' =>array('1'=>array('justification'=>'center'))
		);

		$pdfdata = array();
		$pdfdata[]=array('0'=>'         ','1'=> $company_name." \n ".$AppUI->_("HHRR")." - ".$AppUI->_("Summary"));


		$pdf->ezSetDy(($img_height/2)+38);//Con esto intento que el nombre de la compania quede en el medio de la imagen

		$pdf->ezTable($pdfdata, $columns, $title, $options );
		$pdf->ezSetY($pos_fin_tabla); //Vuelvo a poner el cursor en la pos correcta.

	}
	else
	{
		//Con esto pongo la linea superior e inferior en LA PRIMERA PAGINA, dado que es distinta a todas las demas, va a el titulo sobre la linea*/
		$pdf->setStrokeColor(0,0,0,1);
		$pdf->line(20,40,578,40);
		$pdf->line(20,780,578,780);
		$pdf->addText(50,34,6,$company_name." - ".$AppUI->_("HHRR")." - ".$AppUI->_("Summary"));

		$pdf->ezText( $company_name." - ".$AppUI->_("HHRR")." - ".$AppUI->_("Summary"),15, array('justification'=>'center'));
	}

	/**************************************************************************************************************/
	/*Con estas lineas agrego las lineas divisoras en todas las paginas.
	  Como no tengo nextall( a todas las paginas desde la siguiente),
	  las agrego en todas las paginas siguientes pares e impares :P*/

	$all = $pdf->openObject();
	$pdf->saveState();
	$pdf->setStrokeColor(0,0,0,1);
	$pdf->line(20,40,578,40);
	$pdf->line(20,822,578,822);
	$pdf->addText(50,34,6,$company_name." - ".$AppUI->_("HHRR")." - ".$AppUI->_("Summary"));
	$pdf->restoreState();
	$pdf->closeObject();
	$pdf->addObject($all,'nexteven');

	$all = $pdf->openObject();
	$pdf->saveState();
	$pdf->setStrokeColor(0,0,0,1);
	$pdf->line(20,40,578,40);
	$pdf->line(20,822,578,822);
	$pdf->addText(50,34,6,$company_name." - ".$AppUI->_("HHRR")." - ".$AppUI->_("Summary"));
	$pdf->restoreState();
	$pdf->closeObject();
	$pdf->addObject($all,'nextodd');
	/**************************************************************************************************************/



	$user_name=$lastname." ".$firstname;
	$file_name=$lastname."_".$firstname;
	$pdf->ezText( "\n $user_name",20, array('justification'=>'center'));

	$pdf->selectFont( "$font_dir/Times-Roman.afm" );

/*********************Datos Personales**************************/
	if (validar_permisos_hhrr($id,'personal',1) AND !$_SESSION['vec_sections']['personal'])
	{
		$width='500';
		$imagen = FALSE;

		if($logo!="ninguna" and $logo!="")//Si tiene una imagen
		{
			//Las unicas extensiones posibles para generar el pdf son jpeg, jpg y png
			$ext =findexts($logo);
			if (strcasecmp($ext,"jpeg")==0 OR strcasecmp($ext,"jpg")==0 OR strcasecmp($ext,"png")==0)
			{
				$width='250';
				$imagen = TRUE;
			}
		}

		$columns = null;//array('num'=>'num','name'=>'<i>Alias</i>','type'=>'<i>Type</i>');
		$title = "<b>".$AppUI->__("Personal data")."</b>";
		$options = array(
			'showLines' => 1,
			'showHeadings' => 0,
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 2,
			'colGap' => 5,
			'xPos' => 50,
			'xOrientation' => 'right',
			'width'=> $width
		);

		$pdfdata = array();

		$pdfdata[]=array("<b>".$AppUI->__('First Name')."</b>",html_entity_decode($firstname));
		$pdfdata[]=array("<b>".$AppUI->__('Last Name')."</b>",html_entity_decode($lastname));
		//$pdfdata[]=array("<b>".$AppUI->__('Phone')."</b>",html_entity_decode($phone));
		//$pdfdata[]=array("<b>".$AppUI->__('Email')."</b>", html_entity_decode($email));
		if ($candidato)
		{
			$pdfdata[]=array("<b>".$AppUI->__('Actual Company')."</b>", html_entity_decode($actualcompany));
			$pdfdata[]=array("<b>".$AppUI->__('Actual job')."</b>", html_entity_decode($actualjob));
			$pdfdata[]=array("<b>".$AppUI->__('Salary wanted')."</b>", html_entity_decode($salarywanted));
			$pdfdata[]=array("<b>".$AppUI->__('Actual working hours')."</b>", html_entity_decode($workinghours));
		}
		else
		{
			$pdfdata[]=array("<b>".$AppUI->__('Company')."</b>", html_entity_decode($company_name));
			$pdfdata[]=array("<b>".$AppUI->__('Department')."</b>", html_entity_decode($dept_name));
			$pdfdata[]=array("<b>".$AppUI->__('Direct report')."</b>", html_entity_decode($user_supervisor));
			$pdfdata[]=array("<b>".$AppUI->__('Position')."</b>", html_entity_decode($user_job_title));
			$pdfdata[]=array("<b>".$AppUI->__('Length of service')."</b>", html_entity_decode(calcular_edad($user_input_date_company)));
		}

		//$pdfdata[]=array("<b>".$AppUI->__('ID')."</b>", html_entity_decode($doctype." ".$docnumber));
		//$pdfdata[]=array("<b>".$AppUI->__('Address')."</b>", html_entity_decode($address));
		//$pdfdata[]=array("<b>".$AppUI->__('City')."</b>", html_entity_decode($city));
		//$pdfdata[]=array("<b>".$AppUI->__('ZIP')."</b>", html_entity_decode($zip));
		//$pdfdata[]=array("<b>".$AppUI->__('Home Phone')."</b>", html_entity_decode($homephone));
		//$pdfdata[]=array("<b>".$AppUI->__('Cell Phone')."</b>", html_entity_decode($cellphone));
		$pdfdata[]=array("<b>".$AppUI->__('Title')."</b>", html_entity_decode(user_title( $id )));
		$pdfdata[]=array("<b>".$AppUI->__('Age')."</b>", html_entity_decode(calcular_edad($birthday)));
		$pdfdata[]=array("<b>".$AppUI->__('Marital State')."</b>", html_entity_decode($AppUI->__($maritalstates[$maritalstate])));
		//$pdfdata[]=array("<b>".$AppUI->__('IM')."</b>", html_entity_decode($IMTypes[$im_type].": ".$im_id));
		$pdfdata[]=array("<b>".$AppUI->__('Children')."</b>", html_entity_decode($children));

		/*
		if ($candidato)
		{
			$pdfdata[]=array("<b>".$AppUI->__('Actual job')."</b>", html_entity_decode($actualjob));
			$pdfdata[]=array("<b>".$AppUI->__('Actual working hours')."</b>", html_entity_decode($workinghours));
		}

		else
		{
			$start_time_am = ($start_time_am != "") ? $start_time_am->format("%H:%M"):'';
			$end_time_am = ($end_time_am != "") ? $end_time_am->format("%H:%M"):'';
			$start_time_pm = ($start_time_pm != "") ? $start_time_pm->format("%H:%M"):'';
			$end_time_pm = ($end_time_pm != "") ? $end_time_pm->format("%H:%M"):'';

			$pdfdata[]=array("<b>".$AppUI->__('Work schedule')."</b>", $start_time_am ." - ".$end_time_am."\n".$start_time_pm." - ".$end_time_pm);
		}
		*/
		$pdf->ezSetDy(-25);
		$inicio = $pdf->y -15;
		$table_height=$inicio-$pdf->ezTable($pdfdata, $columns, $title, $options );


		if ($imagen)
		{
			$pdf->ezSetDy($table_height+90);//Subo la imagen para que quede a la altura de la tabla de info personal
			//ezImage(image,[padding],[width],[resize],[justification],[array border])
			$img_height=$pdf->ezImage("$uploads_dir/$id/$logo",120,150,'','right',0);

			//Si la imagen es mas chica pongo el cursor a la misma altura que la tabla, lo bajo la diferencia
			if ($table_height > $img_height)
				$pdf->ezSetDy(-($table_height-$img_height));

			$pdf->ezSetDy(25);
		//	$pdf->ezText("\n");

			//$pdf->ezText("\n\n img_height: $img_height \n table_height: $table_height \n dif: ". ($table_height-$img_height));
		}

	}
/*********************FIN Datos Personales**************************/

/*********************Compensations********************************/
	if (validar_permisos_hhrr($id,'compensations',1) AND !$_SESSION['vec_sections']['compensations'])
	{
		$columns = array(
		'0'=>'',
		'1'=>"<b>".$AppUI->__("actualmonthremuneration")."</b>",
		'2'=>"<b>".$AppUI->__("porcentuallastupdate")."</b>",
		'3'=>"<b>".$AppUI->__("lastuptdate")."</b>",
		'4'=>"<b>".$AppUI->__("gaptoppcactual")."</b>",
		'5'=>"<b>".$AppUI->__("lastreward")."</b>",
		'6'=>"<b>".$AppUI->__("anualremuneration")."</b>",
		'7'=>"<b>".$AppUI->__("actualbenefits")."</b>",
		'8'=>"<b>".$AppUI->__("marketgap")."</b>",
		'9'=>"<b>".$AppUI->__("proposedplan")."</b>");
		$title = "<b>".$AppUI->__("compensations")."</b>";
		$options = array(
			'showLines' => 1,//0,1,2, default is 1 (1->show the borders, 0->no borders, 2-> show borders AND lines between rows.)
			'showHeadings' => 1,//Encabezado 0 or 1
			'shaded' => 1,//Color de las filas. 0,1,2, default is 1 (1->alternate lines are shaded, 0->no shading, 2->both sets are shaded)
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 5,//the space between the text and the row lines on each row
			'colGap' => 5,//the space between the text and the column lines in each column
			'xPos' => 50,//'left','right','center','centre',or coordinate, reference coordinate in the x-direction
			'xOrientation' => 'right',
			'width'=>'500'
		);


		$sql = "SELECT
				  CONCAT(SUBSTRING(h1.hhrr_comp_last_update,7,2),'-',SUBSTRING(h1.hhrr_comp_last_update,5,2),'-',SUBSTRING(h1.hhrr_comp_last_update,3,2)) AS date,
				  h1.hhrr_comp_remuneration,
				  h1.hhrr_comp_id,
				  h1.hhrr_comp_user_id,
				  h1.hhrr_comp_remuneration,
				  max(h2.hhrr_comp_last_update_date) AS vfecha,
				  IF (h2.hhrr_comp_remuneration<>0,CONCAT(ROUND((h1.hhrr_comp_remuneration/h2.hhrr_comp_remuneration*100)-100),'%'),'N/A') AS hhrr_comp_last_update_porc,
				  h1.hhrr_comp_last_update_date,
				  CONCAT(SUBSTRING(h1.hhrr_comp_last_update_date,9,2),'-',SUBSTRING(h1.hhrr_comp_last_update_date,6,2),'-',SUBSTRING(h1.hhrr_comp_last_update_date,3,2)) AS hhrr_comp_last_update_date,
				  h1.hhrr_comp_gap_pc,
				  h1.hhrr_comp_last_reward,
				  (h1.hhrr_comp_remuneration*13+h1.hhrr_comp_remuneration*h1.hhrr_comp_last_reward) AS hhrr_comp_anual_remuneration,
				  h1.hhrr_comp_actual_benefits,
				  h1.hhrr_comp_gap_mer,
				  h1.hhrr_comp_proposed_plan
				FROM hhrr_comp AS h1
				LEFT JOIN hhrr_comp AS h2
				  ON (
				    h1.hhrr_comp_user_id=h2.hhrr_comp_user_id AND
				    h1.hhrr_comp_last_update_date > h2.hhrr_comp_last_update_date)
				WHERE h1.hhrr_comp_user_id ='$id'
				GROUP BY h1.hhrr_comp_remuneration, h1.hhrr_comp_last_update_date
				ORDER BY h1.hhrr_comp_last_update_date DESC, h2.hhrr_comp_last_update_date DESC";
		//echo "<br><pre>$sql</pre><br>";
		$rc = db_exec($sql);

		$pdfdata = array();
		while ($vec = db_fetch_array($rc)){
			$pdfdata[]=array(
			'0'=>html_entity_decode($vec['date']),
			'1'=>html_entity_decode($vec['hhrr_comp_remuneration']),
			'2'=>html_entity_decode($vec['hhrr_comp_last_update_porc']),
			'3'=>html_entity_decode($vec['hhrr_comp_last_update_date']),
			'4'=>html_entity_decode($vec['hhrr_comp_gap_pc']),
			'5'=>html_entity_decode($vec['hhrr_comp_last_reward']),
			'6'=>html_entity_decode($vec['hhrr_comp_anual_remuneration']),
			'7'=>html_entity_decode($vec['hhrr_comp_actual_benefits']),
			'8'=>html_entity_decode($vec['hhrr_comp_gap_mer']),
			'9'=>html_entity_decode($vec['hhrr_comp_proposed_plan'])
			);
		}

		$pdf->ezSetDy(-25);
		$pdf->ezTable($pdfdata, $columns, $title, $options );
		if( db_num_rows($rc) == 0)
			$pdf->ezText("  ".$AppUI->__("Noitems"),9);
	}
/*********************FIN Compensations********************************/

/*********************Antecedentes laborales - Empresas internas********************************/
	if (validar_permisos_hhrr($id,'work_experience',1) AND !$_SESSION['vec_sections']['work_experience'])
	{
		$columns = array(
		'1'=>"<b>".$AppUI->__("Company")."</b>",
		'2'=>"<b>".$AppUI->__("Management/Area")."</b>",
		'3'=>"<b>".$AppUI->__("Function")."</b>",
		'4'=>"<b>".$AppUI->__("From")."</b>",
		'5'=>"<b>".$AppUI->__("To")."</b>",
		'6'=>"<b>".$AppUI->__("reports")."</b>",
		'7'=>"<b>".$AppUI->__("functional_area")."</b>",
		'8'=>"<b>".$AppUI->__("level_management")."</b>",
		'9'=>"<b>".$AppUI->__("Profit")."</b>");
		$title = "<b>".$AppUI->__("Work Experience")." - ".$AppUI->__("Internal Companies")."</b>";
		$options = array(
			'showLines' => 1,
			'showHeadings' => 1,
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 2,
			'colGap' => 5,
			'xPos' => 50,
			'xOrientation' => 'right',
			'width'=>'500'
		);

		$sql = "
		SELECT hhrr_ant.id, CONCAT_WS(' ',user_first_name,user_last_name) AS name, company_name, internal_company, dept_name, function, DATE_FORMAT(from_date,'%d-%m-%Y') as fdate ,DATE_FORMAT(to_date,'%d-%m-%Y') as tdate, profit, area_name, level_management
		FROM hhrr_ant
		LEFT JOIN companies ON companies.company_id=hhrr_ant.company
		LEFT JOIN departments ON departments.dept_id=hhrr_ant.area_internal
		LEFT JOIN users ON users.user_id=hhrr_ant.reports
		LEFT JOIN hhrr_functional_area ON hhrr_functional_area.id=hhrr_ant.functional_area
		WHERE hhrr_ant.user_id ='$id' AND company_name <>'' AND internal_company = '1' order by fdate desc
		";
		//echo "<br><pre>$sql</pre><br>";
		$rc = db_exec($sql);

		$pdfdata = array();
		while ($vec = db_fetch_array($rc)){
			$pdfdata[]=array(
			'1'=>html_entity_decode($vec['company_name']),
			'2'=>html_entity_decode($vec['dept_name']),
			'3'=>html_entity_decode($vec['function']),
			'4'=>html_entity_decode($vec['fdate']),
			'5'=>html_entity_decode($vec['tdate']),
			'6'=>html_entity_decode($vec['name']),
			'7'=>html_entity_decode($vec['area_name']),
			'8'=>html_entity_decode($vec['level_management']),
			'9'=>html_entity_decode($vec['profit']),
			);
		}

		$pdf->ezSetDy(-25);
		$pdf->ezTable($pdfdata, $columns, $title, $options );
		if( db_num_rows($rc) == 0)
			$pdf->ezText("  ".$AppUI->__("Noitems"),9);
	/*********************FIN Antecedentes laborales - Empresas internas********************************/

	/*********************Antecedentes laborales - Empresas externas********************************/

		$columns = array(
		'1'=>"<b>".$AppUI->__("Company")."</b>",
		'2'=>"<b>".$AppUI->__("Management/Area")."</b>",
		'3'=>"<b>".$AppUI->__("Function")."</b>",
		'4'=>"<b>".$AppUI->__("From")."</b>",
		'5'=>"<b>".$AppUI->__("To")."</b>",
		'6'=>"<b>".$AppUI->__("Profit")."</b>");
		$title = "<b>".$AppUI->__("Work Experience")." - ".$AppUI->__("Other Companies")."</b>";
		$options = array(
			'showLines' => 1,
			'showHeadings' => 1,
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 2,
			'colGap' => 5,
			'xPos' => 50,
			'xOrientation' => 'right',
			'width'=>'500',
			'cols' =>array('1'=>array('width'=>85),'2'=>array('width'=>85),'3'=>array('width'=>85),'4'=>array('width'=>50),'5'=>array('width'=>50),'6'=>array('width'=>160))
		);


		$sql = "SELECT id, user_id, company, internal_company , area_external, function, DATE_FORMAT(from_date,'%d-%m-%Y') as fdate ,DATE_FORMAT(to_date,'%d-%m-%Y') as tdate, profit, reports, functional_area, level_management FROM hhrr_ant WHERE user_id ='$id' AND internal_company = '0' order by fdate desc ";
		//echo "<br><pre>$sql</pre><br>";
		$rc = db_exec($sql);

		$pdfdata = array();
		while ($vec = db_fetch_array($rc)){
			$pdfdata[]=array(
			'1'=>html_entity_decode($vec['company']),
			'2'=>html_entity_decode($vec['area_external']),
			'3'=>html_entity_decode($vec['function']),
			'4'=>html_entity_decode($vec['fdate']),
			'5'=>html_entity_decode($vec['tdate']),
			'6'=>html_entity_decode($vec['profit']),
			);
		}

		$pdf->ezSetDy(-25);
		$pdf->ezTable($pdfdata, $columns, $title, $options );
		if( db_num_rows($rc) == 0)
			$pdf->ezText("  ".$AppUI->__("Noitems"),9);
	}
/*********************FIN Antecedentes laborales - Empresas externas********************************/

/*********************Educacion - Educación formal ********************************/
	if (validar_permisos_hhrr($id,'education',1) AND !$_SESSION['vec_sections']['education'])
	{
		$columns = array(
		'1'=>"<b>".$AppUI->__("Academic level")."</b>",
		'2'=>"<b>".$AppUI->__("Title")."</b>",
		'3'=>"<b>".$AppUI->__("Institution")."</b>",
		'4'=>"<b>".$AppUI->__("Status")."</b>",
		'5'=>"<b>".$AppUI->__("Completed")."</b>");
		$title_tab = "<b>".$AppUI->__("Education")." - ".$AppUI->__("Formal Education")."</b>";
		$options = array(
			'showLines' => 1,
			'showHeadings' => 1,
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 2,
			'colGap' => 5,
			'xPos' => 50,
			'xOrientation' => 'right',
			'width'=>'500'
		);


		$sql ="SELECT id, id_user, level, title, instit, status, end_date FROM hhrr_education WHERE id_user ='$id' AND type='0' order by level desc";
		//echo "<br><pre>$sql</pre><br>";
		$rc = db_exec($sql);
		if ($AppUI->user_locale == 'es')
			$name = 'name_es';
		else
			$name = 'name_en';

		$pdfdata = array();
		while ($vec = db_fetch_array($rc)){

			 @$level = db_loadResult("SELECT $name FROM hhrr_academic_level WHERE id ='$vec[level]' ");

			 switch($vec['status']){
			 case "0":
				 $vec['status']= $AppUI->_("Incomplete");
			 break;
			 case "1":
				 $vec['status']= $AppUI->_("Completed");
			 break;
			 case "2":
				 $vec['status']= $AppUI->_("On Course");
			 break;
		   }
		   
		   
					
								
			 $query_title = "SELECT name_es  FROM hhrr_education_title WHERE title_id='$vec[title]' "; 
			 $sql_title = db_exec($query_title);
			 $title_desc = mysql_fetch_array($sql_title);
								
			 $title = $title_desc[0];
			 
			 $query_instit = "SELECT name FROM hhrr_education_institution WHERE instit_id='$vec[instit]' "; 
			 $sql_instit = db_exec($query_instit);
			 $instit_desc = mysql_fetch_array($sql_instit);
			 
			 $instit = $instit_desc['name'];

			$pdfdata[]=array(
			'1'=>html_entity_decode($level),
			'2'=>html_entity_decode($title),
			'3'=>html_entity_decode($instit),
			'4'=>html_entity_decode($vec['status']),
			'5'=>html_entity_decode($vec['end_date']),
			);
		}

		$pdf->ezSetDy(-25);
		$pdf->ezTable($pdfdata, $columns, $title_tab, $options );
		if( db_num_rows($rc) == 0)
			$pdf->ezText("  ".$AppUI->__("Noitems"),9);
/*********************FIN Educacion - Educación formal ********************************/

/*********************Educacion - Entrenamiento ********************************/
		$columns = array(
		'1'=>"<b>".$AppUI->__("Type")."</b>",
		'2'=>"<b>".$AppUI->__("Program")."</b>",
		'3'=>"<b>".$AppUI->__("Activity")."</b>",
		'4'=>"<b>".$AppUI->__("Institution")."</b>",
		'5'=>"<b>".$AppUI->__("Date")."</b>");
		$title = "<b>".$AppUI->__("Education")." - ".$AppUI->__("Training")."</b>";
		$options = array(
			'showLines' => 1,
			'showHeadings' => 1,
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 2,
			'colGap' => 5,
			'xPos' => 50,
			'xOrientation' => 'right',
			'width'=>'500'
		);


		$sql ="SELECT id, id_user, seminary_type, seminary, title, instit, DATE_FORMAT(s_date,'%d-%m-%Y') as sdate FROM hhrr_education WHERE id_user ='$id' AND type='1' order by sdate desc";
		//echo "<br><pre>$sql</pre><br>";
		$rc = db_exec($sql);

		$pdfdata = array();
		while ($vec = db_fetch_array($rc)){

		   switch($vec['seminary_type'])
		   {
		     case "0":
					 $vec['seminary_type'] = $AppUI->_("Local");
				 break;
				 case "1":
					 $vec['seminary_type'] =  $AppUI->_("In-Company");
				 break;
				 case "2":
					 $vec['seminary_type'] =  $AppUI->_("Exterior");
				 break;
	    }
	    
	        $query_program = "SELECT name FROM hhrr_education_program WHERE program_id='$vec[seminary]' "; 
			$sql_program = db_exec($query_program);
			$program_desc = mysql_fetch_array($sql_program);
			
			$seminary = $program_desc['name'];
			
			$query_institut = "SELECT name FROM hhrr_education_institution WHERE instit_id='$vec[instit]' "; 
			$sql_institut = db_exec($query_institut);
			$institut_desc = mysql_fetch_array($sql_institut);
			
			$intit_e = $institut_desc['name'];
			
			$pdfdata[]=array(
			'1'=>html_entity_decode($vec['seminary_type']),
			'2'=>html_entity_decode($seminary),
			'3'=>html_entity_decode($vec['title']),
			'4'=>html_entity_decode($intit_e),
			'5'=>html_entity_decode($vec['sdate']),
			);
		}

		$pdf->ezSetDy(-25);
		$pdf->ezTable($pdfdata, $columns, $title, $options );
		if( db_num_rows($rc) == 0)
			$pdf->ezText("  ".$AppUI->__("Noitems"),9);
	}
/*********************Educacion - Entrenamiento ********************************/

/*********************Competencias********************************/
	if (validar_permisos_hhrr($id,'matrix',1) AND !$_SESSION['vec_sections']['matrix'])
	{
		$columns = array(
		'0'=>"<b>".$AppUI->__("Categories")."</b>",
		'1'=>"<b>".$AppUI->__("Skills items")."</b>",
		'2'=>"<b>".$AppUI->__("Experience")."</b>",
		'3'=>"<b>".$AppUI->__("Comments")."</b>",);
		$title = "<b>".$AppUI->__("Competences")."</b>";
		$options = array(
			'showLines' => 1,//0,1,2, default is 1 (1->show the borders, 0->no borders, 2-> show borders AND lines between rows.)
			'showHeadings' => 1,//Encabezado 0 or 1
			'shaded' => 1,//Color de las filas. 0,1,2, default is 1 (1->alternate lines are shaded, 0->no shading, 2->both sets are shaded)
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 5,//the space between the text and the row lines on each row
			'colGap' => 5,//the space between the text and the column lines in each column
			'xPos' => 50,//'left','right','center','centre',or coordinate, reference coordinate in the x-direction
			'xOrientation' => 'right',
			'width'=>'500'
		);

	  $sql = "SELECT *
	  FROM  hhrrskills, skills, skillcategories
	  WHERE skillcategories.id = skills.idskillcategory
	  AND idskill = skills.id
	  AND user_id='$id'
	  AND VALUE > 1
	  ORDER BY skillcategories.sort,skillcategories.name, skills.description";
		//echo "<br><pre>$sql</pre><br>";
		$rc = db_exec($sql);


		$pdfdata = array();
		while ($vec = db_fetch_array($rc)){
			$items = split(",",$vec["valueoptions"]);

			$pdfdata[]=array(
			'0'=>html_entity_decode($vec['name']),
			'1'=>html_entity_decode($vec['description']),
			'2'=>html_entity_decode($vec['valuedesc'].": ".$items[$vec["value"]-1]),
			'3'=>html_entity_decode($vec['comment']),
			);
		}

		$pdf->ezSetDy(-25);
		$pdf->ezTable($pdfdata, $columns, $title, $options );
		if( db_num_rows($rc) == 0)
			$pdf->ezText("  ".$AppUI->__("Noitems"),9);
	}
/*********************FIN Competencias********************************/

/*********************Evaluación y Rendimiento********************************/
	if ($id != $AppUI->user_id AND validar_permisos_hhrr($id,'performance_management',1)  AND !$_SESSION['vec_sections']['performance_management'])
	{
		$columns = array(
		'0'=>"<b>".$AppUI->__("From")."</b>",
		'1'=>"<b>".$AppUI->__("To")."</b>",
		'2'=>"<b>".$AppUI->__("Performance Evaluation")."</b>",
		'3'=>"<b>".$AppUI->__("Potential")."</b>",
		'4'=>"<b>".$AppUI->__("Supervisor")."</b>",);
		$title = "<b>".$AppUI->__("Performance Management")."</b>";
		$options = array(
			'showLines' => 1,//0,1,2, default is 1 (1->show the borders, 0->no borders, 2-> show borders AND lines between rows.)
			'showHeadings' => 1,//Encabezado 0 or 1
			'shaded' => 1,//Color de las filas. 0,1,2, default is 1 (1->alternate lines are shaded, 0->no shading, 2->both sets are shaded)
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 5,//the space between the text and the row lines on each row
			'colGap' => 5,//the space between the text and the column lines in each column
			'xPos' => 50,//'left','right','center','centre',or coordinate, reference coordinate in the x-direction
			'xOrientation' => 'right',
			'width'=>'500'
		);

	  $sql = "SELECT h.id, DATE_FORMAT(h.from_date,'%d-%m-%Y') as from_date, DATE_FORMAT(h.to_date,'%d-%m-%Y') as to_date, h.performance, h.potential, h.supervisor, u.user_last_name, u.user_first_name FROM hhrr_performance as h, users as u WHERE h.user_id ='$id' AND h.supervisor = u.user_id";
		//echo "<br><pre>$sql</pre><br>";
		$rc = db_exec($sql);


		$pdfdata = array();
		while ($vec = db_fetch_array($rc)){
			
				
			 $query_performance = "SELECT name_es FROM hhrr_performance_items WHERE id_item='$vec[performance]' "; 
			 $sql_performance = db_exec($query_performance);
			 $performance_desc = mysql_fetch_array($sql_performance);
			 
				
			 $query_potential = "SELECT level, name_es FROM hhrr_performance_potential WHERE id_potential = '$vec[potential]' "; 
			 $sql_potential = db_exec($query_potential);
			 $potential_desc = mysql_fetch_array($sql_potential);
								
			
			
			 $potencial_desc = "Nivel ".$potential_desc[0]." ".$potential_desc[1];
			 

			$pdfdata[]=array(
			'0'=>html_entity_decode($vec['from_date']),
			'1'=>html_entity_decode($vec['to_date']),
			'2'=>html_entity_decode($performance_desc[0]),
			'3'=>html_entity_decode($potencial_desc),
			'4'=>html_entity_decode($vec['user_last_name'].", ".$vec['user_first_name']),
			);
		}

		$pdf->ezSetDy(-25);
		$pdf->ezTable($pdfdata, $columns, $title, $options );
		if( db_num_rows($rc) == 0)
			$pdf->ezText("  ".$AppUI->__("Noitems"),9);
	}
/*********************FIN Evaluación y Rendimiento********************************/

/*********************Desarrollo********************************/
	if (validar_permisos_hhrr($id,'development',1) AND !$_SESSION['vec_sections']['development'])
	{

		$columns = NULL;
		$title = "<b>".$AppUI->__("Work Profile - Mobility area")."</b>";
		$options = array(
			'showLines' => 1,//0,1,2, default is 1 (1->show the borders, 0->no borders, 2-> show borders AND lines between rows.)
			'showHeadings' => 0,//Encabezado 0 or 1
			'shaded' => 0,//Color de las filas. 0,1,2, default is 1 (1->alternate lines are shaded, 0->no shading, 2->both sets are shaded)
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 2,//the space between the text and the row lines on each row
			'colGap' => 5,//the space between the text and the column lines in each column
			'xPos' => 50,//'left','right','center','centre',or coordinate, reference coordinate in the x-direction
			'xOrientation' => 'right',
			'width'=>'500'
		);

		$select = "SELECT * FROM hhrr_dev WHERE hhrr_dev_user_id=$id";
		$sql_sel = mysql_query($select);
		$data = mysql_fetch_array($sql_sel);

		$hhrr_dev_eval_g_1 = $data['hhrr_dev_eval_g_1'];
		$hhrr_dev_eval_g_S = $data['hhrr_dev_eval_g_S'];
		$hhrr_dev_eval_t_1 = $data['hhrr_dev_eval_t_1'];
		$hhrr_dev_eval_t_S = $data['hhrr_dev_eval_t_S'];
		$hhrr_dev_sug = $data['hhrr_dev_sug'];
		$hhrr_dev_rst = $data['hhrr_dev_rst'];
		$hhrr_dev_rmt = $data['hhrr_dev_rmt'];
		$hhrr_dev_rlt = $data['hhrr_dev_rlt'];
		$hhrr_dev_pos_k = ($data['hhrr_dev_pos_k'])? $AppUI->_("Yes") : $AppUI->_("No");
		$hhrr_dev_per_k = ($data['hhrr_dev_per_k'])? $AppUI->_("Yes") : $AppUI->_("No");

		$hhrr_dev_mov_af1 =($data['hhrr_dev_mov_af1']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_af1'] ) : "";
		$hhrr_dev_mov_asa1 =($data['hhrr_dev_mov_asa1']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_asa1'] ) : "";
		$hhrr_dev_mov_af2 =($data['hhrr_dev_mov_af2']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_af2'] ) : "";
		$hhrr_dev_mov_asa2 =($data['hhrr_dev_mov_asa2']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_asa2'] ) : "";
		$hhrr_dev_mov_af3 =($data['hhrr_dev_mov_af3']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_af3'] ) : "";
		$hhrr_dev_mov_asa3 =($data['hhrr_dev_mov_asa3']) ? db_loadResult( "SELECT area_name FROM hhrr_functional_area where id=".$data['hhrr_dev_mov_asa3'] ) : "";

		$hhrr_dev_int_a = $data['hhrr_dev_int_a'];
		$hhrr_dev_exp = $data['hhrr_dev_exp'];

		$pdfdata = array();

		$pdfdata[]=array(
		'0'=>html_entity_decode("<b>".$AppUI->__("Functional Area 1").":</b> ". $hhrr_dev_mov_af1),
		'1'=>html_entity_decode("<b>".$AppUI->__("Functional Area 2").":</b> ". $hhrr_dev_mov_af2),
		'2'=>html_entity_decode("<b>".$AppUI->__("Functional Area 3").":</b> ". $hhrr_dev_mov_af3),
		);
		$pdfdata[]=array(
		'0'=>html_entity_decode("<b>".$AppUI->__("Sub Area")."1:</b> ". $hhrr_dev_mov_asa1),
		'1'=>html_entity_decode("<b>".$AppUI->__("Sub Area")."2:</b> ". $hhrr_dev_mov_asa2),
		'2'=>html_entity_decode("<b>".$AppUI->__("Sub Area")."3:</b> ". $hhrr_dev_mov_asa3),
		);

		$pdf->ezSetDy(-25);
		$pdf->ezTable($pdfdata, $columns, $title, $options );

		$columns = NULL;
		$title = "<b>".$AppUI->__("Could Replace")."</b>";
		$options = array(
			'showLines' => 1,//0,1,2, default is 1 (1->show the borders, 0->no borders, 2-> show borders AND lines between rows.)
			'showHeadings' => 0,//Encabezado 0 or 1
			'shaded' => 0,//Color de las filas. 0,1,2, default is 1 (1->alternate lines are shaded, 0->no shading, 2->both sets are shaded)
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 2,//the space between the text and the row lines on each row
			'colGap' => 5,//the space between the text and the column lines in each column
			'xPos' => 50,//'left','right','center','centre',or coordinate, reference coordinate in the x-direction
			'xOrientation' => 'right',
			'width'=>'200'
		);

		$pdfdata = array();

		$pdfdata[]=array(
		'0'=>html_entity_decode("<b>".$AppUI->__("ST").":</b> ". $hhrr_dev_rst),
		'1'=>html_entity_decode("<b>".$AppUI->__("Position Key Person").":</b> ".$hhrr_dev_pos_k)
		);
		$pdfdata[]=array(
		'0'=>html_entity_decode("<b>".$AppUI->__("MT").":</b> ". $hhrr_dev_rmt),
		'1'=>html_entity_decode("")
		);
		$pdfdata[]=array(
		'0'=>html_entity_decode("<b>".$AppUI->__("LT").":</b> ". $hhrr_dev_rlt),
		'1'=>html_entity_decode("<b>".$AppUI->__("Position Key Person").":</b> ". $hhrr_dev_per_k)
		);

		$pdf->ezSetDy(-5);
		$tam=$pdf->ezTable($pdfdata, $columns, $title, $options );
		//$pdf->ezText("tamaño: $tam");

		$columns = array(
		'0'=>"<b>".$AppUI->__("Management")."</b>",
		'1'=>"<b>".$AppUI->__("Technical Functional")."</b>",);
		$title = "<b>".$AppUI->__("Potential review")."</b>";
		$options = array(
			'showLines' => 1,//0,1,2, default is 1 (1->show the borders, 0->no borders, 2-> show borders AND lines between rows.)
			'showHeadings' => 0,//Encabezado 0 or 1
			'shaded' => 0,//Color de las filas. 0,1,2, default is 1 (1->alternate lines are shaded, 0->no shading, 2->both sets are shaded)
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 2,//the space between the text and the row lines on each row
			'colGap' => 5,//the space between the text and the column lines in each column
			'xPos' => 290,//'left','right','center','centre',or coordinate, reference coordinate in the x-direction
			'xOrientation' => 'right',
			'width'=>'260'
		);

		$pdfdata = array();

		$pdfdata[]=array(
		'0'=>html_entity_decode("<b>1 ".$AppUI->__("Level").":</b> ". $hhrr_dev_eval_g_1),
		'1'=>html_entity_decode("<b>1 ".$AppUI->__("Level").":</b> ".$hhrr_dev_eval_t_1)
		);
		$pdfdata[]=array(
		'0'=>html_entity_decode("<b>".$AppUI->__("More than 1 Level").":</b> ". $hhrr_dev_eval_g_S),
		'1'=>html_entity_decode("<b>".$AppUI->__("More than 1 Level").":</b> ". $hhrr_dev_eval_t_S),
		);

		$pdf->ezSetDy(55);//Subo un poco la tabla para que quede a la altura de la anterior
		//$pdf->ezText("\n");
		$tam = $pdf->ezTable($pdfdata, $columns, $title, $options );
		//$pdf->ezText("tamaño: $tam");



		$columns = NULL;
		$title = NULL;
		$options = array(
			'showLines' => 1,//0,1,2, default is 1 (1->show the borders, 0->no borders, 2-> show borders AND lines between rows.)
			'showHeadings' => 0,//Encabezado 0 or 1
			'shaded' => 0,//Color de las filas. 0,1,2, default is 1 (1->alternate lines are shaded, 0->no shading, 2->both sets are shaded)
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 2,//the space between the text and the row lines on each row
			'colGap' => 5,//the space between the text and the column lines in each column
			'xPos' => 50,//'left','right','center','centre',or coordinate, reference coordinate in the x-direction
			'xOrientation' => 'right',
			'width'=>'500',
			'cols' =>array('0'=>array('justification'=>'left','width'=>150),'1'=>array('justification'=>'left','width'=>350))
		);

		$pdfdata = array();

		$pdfdata[]=array(
		'0'=>html_entity_decode("<b>".$AppUI->__("Employee interest areas").":</b>"),
		'1'=>html_entity_decode($hhrr_dev_int_a),
		);
		$pdfdata[]=array(
		'0'=>html_entity_decode("<b>".$AppUI->__("Employee personal development expectations").":</b>"),
		'1'=>html_entity_decode($hhrr_dev_exp),
		);
		$pdfdata[]=array(
		'0'=>html_entity_decode("<b>".$AppUI->__("Development ideas").":</b>"),
		'1'=>html_entity_decode($hhrr_dev_sug),
		);

		$pdf->ezSetDy(-25);
		$pdf->ezTable($pdfdata, $columns, $title, $options );



		$columns = array(
		'0'=>"<b>".$AppUI->__("Action")."</b>",
		'1'=>"<b>".$AppUI->__("Date")."</b>",
		'2'=>"<b>".$AppUI->__("Comments")."</b>",
		'3'=>"<b>".$AppUI->__("Approved")."</b>",
		'4'=>"<b>".$AppUI->__("Status")."</b>",);
		$title = "<b>".$AppUI->__("Individual Development Plan")."</b>";
		$options = array(
			'showLines' => 1,//0,1,2, default is 1 (1->show the borders, 0->no borders, 2-> show borders AND lines between rows.)
			'showHeadings' => 1,//Encabezado 0 or 1
			'shaded' => 1,//Color de las filas. 0,1,2, default is 1 (1->alternate lines are shaded, 0->no shading, 2->both sets are shaded)
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 5,//the space between the text and the row lines on each row
			'colGap' => 5,//the space between the text and the column lines in each column
			'xPos' => 50,//'left','right','center','centre',or coordinate, reference coordinate in the x-direction
			'xOrientation' => 'right',
			'width'=>'500',
			'cols' =>array('0'=>array('width'=>80),'1'=>array('width'=>50),'2'=>array('width'=>250),'3'=>array('width'=>50),'4'=>array('width'=>60))
		);

	  $sql = "SELECT hhrr_dev_pf_id,hhrr_dev_pf_action, DATE_FORMAT(hhrr_dev_pf_date,'%d-%m-%Y') as hhrr_dev_pf_date,hhrr_dev_pf_coment,hhrr_dev_pf_aproved,hhrr_dev_pf_status FROM hhrr_dev_pf WHERE hhrr_dev_pf_user_id = $id";
		//echo "<br><pre>$sql</pre><br>";
		$rc = db_exec($sql);


		$pdfdata = array();
		while ($vec = db_fetch_array($rc)){

			$pdfdata[]=array(
			'0'=>html_entity_decode($vec['hhrr_dev_pf_action']),
			'1'=>html_entity_decode($vec['hhrr_dev_pf_date']),
			'2'=>html_entity_decode($vec['hhrr_dev_pf_coment']),
			'3'=>html_entity_decode( ($vec['hhrr_dev_pf_aproved']==1) ? $AppUI->_('Yes') :$AppUI->_('No')),
			'4'=>html_entity_decode($vec['hhrr_dev_pf_status']),
			);
		}

		$pdf->ezSetDy(-25);
		$pdf->ezTable($pdfdata, $columns, $title, $options );
		if( db_num_rows($rc) == 0)
			$pdf->ezText("  ".$AppUI->__("Noitems"),9);
	}
/*********************FIN Desarrollo********************************/


/*********************Comentarios********************************/
	if ($id != $AppUI->user_id AND !$_SESSION['vec_sections']['comments'])
	{
		define("NOTE_INTERVIEW", 1);
		define("NOTE_INTERNAL", 2);

		$columns = NULL;
		$title = "<b>".$AppUI->__("Comments")."</b>";
		$options = array(
			'showLines' => 1,//0,1,2, default is 1 (1->show the borders, 0->no borders, 2-> show borders AND lines between rows.)
			'showHeadings' => 0,//Encabezado 0 or 1
			'shaded' => 1,//Color de las filas. 0,1,2, default is 1 (1->alternate lines are shaded, 0->no shading, 2->both sets are shaded)
			'fontSize' => 8,
			'titleFontSize' => 15,
			'rowGap' => 5,//the space between the text and the row lines on each row
			'colGap' => 5,//the space between the text and the column lines in each column
			'xPos' => 50,//'left','right','center','centre',or coordinate, reference coordinate in the x-direction
			'xOrientation' => 'right',
			'width'=>'500',
			'cols' =>array('0'=>array('justification'=>'left','width'=>100),'1'=>array('justification'=>'left','width'=>400))
		);
	  $sql = "SELECT hhrr_note_id,hhrr_user_id,hhrr_note, DATE_FORMAT(hhrr_note_date, '%d/%m/%Y %H:%i:%s') AS hhrr_note_date,hhrr_note_owner, CONCAT(user_last_name,', ',user_first_name) AS full_name FROM hhrr_notes LEFT JOIN users ON hhrr_notes.hhrr_note_owner=users.user_id WHERE hhrr_user_id = $id AND hhrr_note_type = ".NOTE_INTERNAL." ORDER BY hhrr_note_date DESC, hhrr_note_id DESC;";
		//echo "<br><pre>$sql</pre><br>";
		$rc = db_exec($sql);


		$pdfdata = array();
		while ($vec = db_fetch_array($rc)){

			$pdfdata[]=array(
			'0'=>html_entity_decode($vec['full_name']."\n".$vec['hhrr_note_date']),
			'1'=>html_entity_decode($vec['hhrr_note']),
			);
		}

		$pdf->ezSetDy(-25);
		$pdf->ezTable($pdfdata, $columns, $title, $options );
	}
/*********************FIN Comentarios********************************/

//-----------------------------FIN-------------------------//
	//$pdf->setEncryption('trees','frogs',array('copy','print'));//setEncryption([userPass=''],[ownerPass=''],[pc=array])
	if ($fp = fopen( "$temp_dir/$file_name.pdf", 'wb' )) {
		fwrite( $fp, $pdf->ezOutput() );
		fclose( $fp );

		return "$file_name.pdf";
		/*
		echo "<br><center><a href=\"$base_url/files/temp/temp_$id.pdf\" target=\"pdf\">";
		echo $AppUI->_( "View PDF File" );
		echo "</a></center>";*/

		//header( "Location: $base_url/files/temp/temp_$id.pdf" );

	} else {
		echo "Could not open file to save PDF.  ";
		if (!is_writable( $temp_dir )) {
			"The files/temp directory is not writable.  Check your file system permissions.";
		}
		return false;
	}
}

function findexts ($filename)
{
	$filename = strtolower($filename) ;
	$exts = split("[/\\.]", $filename) ;
	$n = count($exts)-1;
	$exts = $exts[$n];
	return $exts;
}
?>