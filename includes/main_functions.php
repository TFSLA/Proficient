<?php /* INCLUDES $Id: main_functions.php,v 1.5 2009-05-22 15:26:48 ctobares Exp $ */
##
## Global General Purpose Functions
##
$CR = "\n";
define('SECONDS_PER_DAY', 60 * 60 * 24);


##
## Returns the best color based on a background color (x is cross-over)
##
function bestColor( $bg, $lt='#ffffff', $dk='#000000' ) {
// cross-over color = x
	$x = 128;
	$r = hexdec( substr( $bg, 0, 2 ) );
	$g = hexdec( substr( $bg, 2, 2 ) );
	$b = hexdec( substr( $bg, 4, 2 ) );

	if ($r < $x && $g < $x || $r < $x && $b < $x || $b < $x && $g < $x) {
		return $lt;
	} else {
		return $dk;
	}
}


/**
		Returns a select box based on an key,value array where selected is based on key


		\param arr Array con los campos que se listaran en el SELECT
		\param select_name Nombre del SELECT
		\param select_attribs Atributos del SELECT
		\param selected Opcion que sera marcada como SELECTED
		\param translate Si es *TRUE* traduce el array. Campo opcional, por defecto *FALSE*.
		\param ordenar Si es *FALSE* no ordena el array. Campo opcional, por defecto *TRUE*. 

		\return Un string con todo el codigo HTML listo para imprimir en pantalla

*/
function arraySelect( &$arr, $select_name, $select_attribs, $selected, $translate=false, $ordenar=TRUE, $width = '160 px' ) {
	GLOBAL $AppUI;
	
	reset( $arr );
            
	//$s = "\n<select name=\"$select_name\" $select_attribs style=\"width : $width;\" >";
	$s = "\n<select name=\"$select_name\" $select_attribs style=\"width : $width;\" >";
	

	//verificamos si la primera linea tiene un "TODOS" para que 
	//luego del ordenamiento podamos ponerla nuevamente en la 
	//primera posicion	
	if ($translate==TRUE) {
		foreach ($arr as $k => $v ) {
			eval ("\$arr['$k'] = @\$AppUI->_( \$v );");
		}
	}

	//ordenamos la lista de opciones del drop down
	//independientemente del idioma
	if ($ordenar==TRUE) {    
		natcasesort($arr);
	} 
    

	foreach ($arr as $k => $v ) {
		$s .= "\n\t<option value=\"".$k."\"".($k == $selected ? " selected=\"selected\"" : '').">".$v ."</option>";
		$arr2[$k] = $v;
	}
	
	$s .= "\n</select>\n";
	
	return $s;
}

function arraySelectJs( &$arr, $select_name, $select_attribs, $selected, $translate=false, $width = '160 px' ) {
	GLOBAL $AppUI;

	reset( $arr );
	$s = "<select name=\"$select_name\" $select_attribs style=\"width : $width;\">";
	foreach ($arr as $k => $v ) {
		if ($translate) {
			$v = @$AppUI->_( $v );
		}
		$s .= "<option value=\"".$k."\"".($k == $selected ? " selected=\"selected\"" : '').">" . dPformSafe( $v ) . "</option>";
	}
	$s .= "</select>";
	return $s;
}


##
## returns a select box based on an key,value array where selected is based on key
##
function arraySelectTree( &$arr, $select_name, $select_attribs, $selected, $translate=false ) {
	GLOBAL $AppUI;
	reset( $arr );

	$children = array();
	// first pass - collect children
	foreach ($arr as $k => $v ) {
		$id = $v[0];
		$pt = $v[2];
		$list = @$children[$pt] ? $children[$pt] : array();
		array_push($list, $v);
	    $children[$pt] = $list;
	}
	$list = tree_recurse($arr[0][2], '', array(), $children);
	return arraySelect( $list, $select_name, $select_attribs, $selected, $translate );
}

function tree_recurse($id, $indent, $list, $children) {
	if (@$children[$id]) {
		foreach ($children[$id] as $v) {
			$id = $v[0];
			$txt = $v[1];
			$pt = $v[2];
			$list[$id] = "$indent $txt";
			$list = tree_recurse($id, "$indent--", $list, $children);
		}
	}
	return $list;
}

##
## Merges arrays maintaining/overwriting shared numeric indicees
##
function arrayMerge( $a1, $a2 ) {
	
	//ambos arrays deben tener elementos
	if (is_array($a1)>0 && is_array($a2)>0){
		foreach ($a2 as $k => $v) {
			$a1[$k] = $v;
		}		
	}
	//si solo tiene elementos el array a2
	elseif (is_array($a2)>0){
		$a1 = $a2;
	}
	//si solo tiene elementos el array a1 o si no tiene devuelvo un array vacio
	else {
		$a1 = is_array($a1)>0 ? $a1 : array();
	}
	/*
	foreach ($a2 as $k => $v) {
		$a1[$k] = $v;
	}	*/	
	return $a1;
}

##
## breadCrumbs - show a colon separated list of bread crumbs
## array is in the form url => title
##
function breadCrumbs( &$arr ) {
	GLOBAL $AppUI;
	$crumbs = array();
	foreach ($arr as $k => $v) {
		$crumbs[] = "<a href=\"$k\">".$AppUI->_( $v )."</a>";
	}
	return implode( ' <strong>:</strong> ', $crumbs );
}
##
## generate link for context help -- old version
##
function contextHelp( $title, $link='' ) {
	return dPcontextHelp( $title, $link );
}

function dPcontextHelp( $title, $link='' ) {
	global $AppUI;
	return "<a href=\"#$link\" onClick=\"javascript:window.open('?m=help&dialog=1&hid=$link', 'contexthelp', 'width=400, height=400, left=50, top=50, scrollbars=yes, resizable=yes')\">".$AppUI->_($title)."</a>";
}

##
## displays the configuration array of a module for informational purposes
##
function dPshowModuleConfig( $config ) {
	GLOBAL $AppUI;
	$s = '<table cellspacing="2" cellpadding="2" border="0" class="std" width="50%">';
	$s .= '<tr><th colspan="2">'.$AppUI->_( 'Module Configuration' ).'</th></tr>';
	foreach ($config as $k => $v) {
		$s .= '<tr><td width="50%">'.$AppUI->_( $k ).'</td><td width="50%" class="hilite">'.$AppUI->_( $v ).'</td></tr>';
	}
	$s .= '</table>';
	return ($s);
}

/**
 *	Function to recussively find an image in a number of places
 *	@param string The name of the image
 *	@param string Optional name of the current module
 */
function dPfindImage( $name, $module=null ) {
// uistyle must be declared globally
	global $AppUI, $uistyle;

	if (file_exists( "{$AppUI->cfg['root_dir']}/style/$uistyle/images/$name" )) {
		return "./style/$uistyle/images/$name";
	} else if ($module && file_exists( "{$AppUI->cfg['root_dir']}/modules/$module/images/$name" )) {
		return "./modules/$module/images/$name";
	} else if (file_exists( "{$AppUI->cfg['root_dir']}/images/icons/$name" )) {
		return "./images/icons/$name";
	} else if (file_exists( "{$AppUI->cfg['root_dir']}/images/obj/$name" )) {
		return "./images/obj/$name";
	} else {
		return "./images/$name";
	}
}

/**
 *	Workaround to display png images with alpha-transparency in IE6.0
 *	@param string The name of the image
 *	@param string The image width
 *	@param string The image height
 *	@param string The alt text for the image
 */
function dPshowImage( $src, $wid='', $hgt='', $alt='' ) {
	if (strpos( $src, '.png' ) > 0 && strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0' ) !== false) {
		return "<div style=\"height:{$hgt}px; width:{$wid}px; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='$src', sizingMethod='scale');\" ></div>";
	} else {
		return "<img src=\"$src\" width=\"$wid\" height=\"$hgt\" alt=\"$alt\" border=\"0\" />";
	}
}


function getImageFromExtension($extension)
{
	$sql = "select id from file_types where lower(extension) = lower('$extension')
			union
			select id from file_types where extension = '' ";

	$id = db_loadResult($sql);

	return "./images/filesicons/".$id.".gif";
}

#
# function to return a default value if a variable is not set
#

function defVal($var, $def) {
	return isset($var) ? $var : $def;
}

/**
* Utility function to return a value from a named array or a specified default
*/
function dPgetParam( &$arr, $name, $def=null ) {
	return isset( $arr[$name] ) ? $arr[$name] : $def;
}

#
# add history entries for tracking changes
#

function addHistory( $description, $project_id = 0, $module_id = 0) {
	global $AppUI;
	/*
	 * TODO:
	 * 1) description should be something like:
	 * 		command(arg1, arg2...)
	 *  for example:
	 * 		new_forum('Forum Name', 'URL')
	 *
	 * This way, the history module will be able to display descriptions
	 * using locale definitions:
	 * 		"new_forum" -> "New forum '%s' was created" -> "Se ha creado un nuevo foro llamado '%s'"
	 *
	 * 2) project_id and module_id should be provided in order to filter history entries
	 *
	 */
	if(!$AppUI->cfg['log_changes']) return;
	$description = str_replace("'", "\'", $description);
	$hsql = "select * from modules where mod_name = 'History' and mod_active = 1";
	$qid = db_exec($hsql);

	if (! $qid || db_num_rows($qid) == 0) {
	  $AppUI->setMsg("History module is not loaded, but your config file has requested that changes be logged.  You must either change the config file or install and activate the history module to log changes.", UI_MSG_ALERT);
	  return;
	}

	$psql =	"INSERT INTO history " .
			"( history_description, history_user, history_date ) " .
	  		" VALUES ( '$description', " . $AppUI->user_id . ", now() )";
	db_exec($psql);
	echo db_error();
}

##
## Looks up a value from the SYSVALS table
##
function dPgetSysVal( $title ) {
	global $AppUI;
	$sql = "
	SELECT syskey_type, syskey_sep1, syskey_sep2, sysval_value
	FROM sysvals,syskeys
	WHERE sysval_title = '$title'
		AND syskey_id = sysval_key_id
	";
	db_loadHash( $sql, $row );
// type 0 = list
	$sep1 = $row['syskey_sep1'];	// item separator
	$sep2 = $row['syskey_sep2'];	// alias separator

	// A bit of magic to handle newlines and returns as separators
	// Missing sep1 is treated as a newline.
	if (!isset($sep1))
	  $sep1 = "\n";
	if ($sep1 == "\\n")
	  $sep1 = "\n";
	if ($sep1 == "\\r")
	  $sep1 = "\r";

  	if ($title=="CompanyType"){
		//echo "<pre>";echo $row['sysval_value'];echo "</pre>";
		$row['sysval_value']="0|".$AppUI->_("Internal")."\n". $row['sysval_value'];
	}

	$temp = explode( $sep1, $row['sysval_value'] );
	$arr = array();
	
	// We use trim() to make sure a numeric that has spaces
	// is properly treated as a numeric
	foreach ($temp as $item) {
		if($item) {
			$temp2 = explode( $sep2, $item );
			if (isset( $temp2[1] )) {
				$arr[trim($temp2[0])] = $temp2[1];
			} else {
				$arr[trim($temp2[0])] = $temp2[0];
			}
		}
	}
	return $arr;
}

function dPuserHasRole( $name ) {
	global $AppUI;
	$uid = $AppUI->user_id;
	$sql = "SELECT r.role_id FROM roles AS r,user_roles AS ur WHERE ur.user_id=$uid AND ur.role_id=r.role_id AND r.role_name='$name'";
	return db_loadResult( $sql );
}

function dPformatDuration($x) {
    global $dPconfig;
    global $AppUI;
    $dur_day = floor($x / $dPconfig['daily_working_hours']);
    //$dur_hour = fmod($x, $dPconfig['daily_working_hours']);
    $dur_hour = $x - $dur_day*$dPconfig['daily_working_hours'];
    $str = '';
    if ($dur_day > 1) {
        $str .= $dur_day .' '. $AppUI->_('days'). ' ';
    } elseif ($dur_day == 1) {
        $str .= $dur_day .' '. $AppUI->_('day'). ' ';
    }

    if ($dur_hour > 1 ) {
        $str .= $dur_hour .' '. $AppUI->_('hours');
    } elseif ($dur_hour > 0 and $dur_hour <= 1) {
        $str .= $dur_hour .' '. $AppUI->_('hour');
    }

    if ($str == '') {
        $str = $AppUI->_("n/a");
    }

    return $str;

}

/**
*/
function dPsetMicroTime() {
	global $microTimeSet;
	list($usec, $sec) = explode(" ",microtime());
	$microTimeSet = (float)$usec + (float)$sec;
}

/**
*/
function dPgetMicroDiff() {
	global $microTimeSet;
	$mt = $microTimeSet;
	dPsetMicroTime();
	return sprintf( "%.3f", $microTimeSet - $mt );
}

/**
* Make text safe to output into double-quote enclosed attirbutes of an HTML tag
*/
function dPformSafe( $txt, $deslash=false ) {
	if (is_object( $txt )) {
		foreach (get_object_vars($txt) as $k => $v) {
			if ($deslash) {
				$obj->$k = htmlspecialchars( stripslashes( $v ) );
			} else {
				$obj->$k = htmlspecialchars( $v );
			}
		}
	} else if (is_array( $txt )) {
		foreach ($txt as $k=>$v) {
			if ($deslash) {
				$txt[$k] = htmlspecialchars( stripslashes( $v ) );
			} else {
				$txt[$k] = htmlspecialchars( $v );
			}
		}
	} else {
		if ($deslash) {
			$txt = htmlspecialchars( stripslashes( $txt ) );
		} else {
			$txt = htmlspecialchars( $txt );
		}
	}
	return $txt;
}

function convert2days( $durn, $units ) {
	global $AppUI;
	switch ($units) {
	case 0:
		return $durn / $AppUI->cfg['daily_working_hours'];
		break;
	case 24:
		return $durn;
	}
}

function formatTime($uts) {
	global $AppUI;
	$date = new CDate();
	$date->setDate($uts, DATE_FORMAT_UNIXTIME);	
	return $date->format( $AppUI->getPref('SHDATEFORMAT') );
}

function randomChar($string) {
    $length = strlen($string);
    $position = mt_rand(0, $length - 1);
    return($string[$position]);
}

function randomString ($charsetString, $length, $originalString) 
{	
	$returnString = "";
	$relleno = $length - strlen($originalString);
	
	for ( $i = 0; $i < strlen($originalString); $i++ )
	{
		$rand = mt_rand( 1, $relleno );
		$relleno -= $rand;
				
		for ( $x = 0; $x < $rand; $x++ )
		{
			$returnString .= randomChar( $charsetString );
		}		
		$returnString .= $originalString[$i];		
	}
	for ( $i = strlen($returnString); $i < $length; $i++ )
	{
		$returnString .= randomChar( $charsetString );
	}
		
    return($returnString);
}


//calcular la edad de una persona 
//recibe la fecha como un string en formato espa?l 
//devuelve un entero con la edad. Devuelve false en caso de que la fecha sea incorrecta o mayor que el dia actual 
function calcular_edad($nacimiento){ 

    //calculo la fecha de hoy 
    $a=date("Y");
		$m=date("m");
		$d=date("d");

    //calculo la fecha que recibo 
    //La descompongo en un array 
		$fecha = str_replace("/","-",$nacimiento);
		$fecha = str_replace(".","-",$fecha);
		$fecha = substr($fecha,0,10);
		$fecha = explode( '-', $fecha);

    //si el array no tiene tres partes, la fecha es incorrecta 
    if (count($fecha)!=3) 
       return false ;

    //compruebo que los ano, mes, dia son correctos 
    $a1 = intval($fecha[0]); 
    if (!is_numeric($a1) || $a1==0) return false ;
    $m1 = intval($fecha[1]); 
    if (!is_numeric($m1)) return false ;
    $d1 = intval($fecha[2]); 
    if (!is_numeric($d1)) return false ;


    //si el a? de la fecha que recibo solo tiene 2 cifras hay que cambiarlo a 4 
    if ($a1<=99) 
       $a1 = $a1 + 1900; 

    //resto los a?s de las dos fechas 
		$edad = $a - $a1 - 1; //-1 porque no se si ha cumplido a?s ya este a? 

		
		if ($m > $m1){ 													// si cumplio a?s en un mes que ya pas?				|| ($m == $m1 && $d >= $d1)){			// o si es el mes del cumplea?s y el d? del cumplea?s ya pas?o es hoy
			$edad = $edad + 1 ;									// ya cumpli?a?s entonces incremento su edad
		} 
		/*
    //si resto los meses y me da menor que 0 entonces no ha cumplido a?s. Si da mayor si ha cumplido 
    if ($m + 1 - $m1 < 0) //+ 1 porque los meses empiezan en 0 
       return $edad ;
    if ($m + 1 - $m1 > 0) 
       return $edad + 1 ;

    //entonces es que eran iguales. miro los dias 
    //si resto los dias y me da menor que 0 entonces no ha cumplido a?s. Si da mayor o igual si ha cumplido 
    if ($d - $d1 >= 0) 
       return $edad + 1; */

    return $edad ;
} 

function get_client_ip ()
{
	// Get REMOTE_ADDR as the Client IP.
	$client_ip = ( !empty($_SERVER['REMOTE_ADDR']) ) ? 
		$_SERVER['REMOTE_ADDR'] : 
		( ( !empty($_ENV['REMOTE_ADDR']) ) ? 
				$_ENV['REMOTE_ADDR'] : 
				$REMOTE_ADDR );
	
	// Check for headers used by proxy servers to send the Client IP. We should look for HTTP_CLIENT_IP before HTTP_X_FORWARDED_FOR.
	if ($_SERVER["HTTP_CLIENT_IP"])
		$proxy_ip = $_SERVER["HTTP_CLIENT_IP"];
	elseif ($_SERVER["HTTP_X_FORWARDED_FOR"])
		$proxy_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	
	// Proxy is used, see if the specified Client IP is valid. Sometimes it's 10.x.x.x or 127.x.x.x... Just making sure.
	if ($proxy_ip)
	{
		if ( preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $proxy_ip, $ip_list) )
		{
			$private_ip = array('/^0\./', '/^127\.0\.0\.1/', '/^192\.168\..*/', '/^172\.16\..*/', '/^10.\.*/', '/^224.\.*/', '/^240.\.*/');
			$client_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);
		}
	}
	
	// Return the Client IP.
	return $client_ip;
}

function loguserevent($event, $user=""){
	
	$AppUI =& $_SESSION["AppUI"]; 
	$user = $AppUI->user_id > 0  ?  $AppUI->user_id : $user;
	//$events = dPgetSysVal("UserEvents");
	$obj = new CUserLog();
	$obj->user_log_id = "";
	$obj->user_log_user = $user;
	$date = new CDate();
	$obj->user_log_date = $date->format(FMT_DATETIME_MYSQL);
	$obj->user_log_ip = get_client_ip ();
	$obj->user_log_last_use = $date->format(FMT_DATETIME_MYSQL);
	$obj->user_log_event = $event;
	
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	}	

	if ($obj->user_log_id){
		$AppUI->user_log_id = $obj->user_log_id;
	}

}

function loguseruse(){
	$AppUI =& $_SESSION["AppUI"]; 	
	if(!is_null($AppUI->user_log_id)){
			
		$obj = new CUserLog();
		$obj->load($AppUI->user_log_id);
		$date = new CDate();
		$obj->user_log_last_use = $date->format(FMT_DATETIME_MYSQL);
		
		if (($msg = $obj->store())) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
		}		
	}
		
}



function loguserlogout($user_log_id){
	
	if(!is_null($user_log_id)){
			
		$obj = new CUserLog();
		$obj->load($user_log_id);
		$date = new CDate();
		$obj->user_log_logout = $date->format(FMT_DATETIME_MYSQL);
		
		if (($msg = $obj->store())) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
		}		
	}
}

function logusergetsessiontime(){
	$AppUI =& $_SESSION["AppUI"]; 	
	if(!is_null($AppUI->user_log_id)){
			
		$obj = new CUserLog();
		$obj->load($AppUI->user_log_id);
		$dateini = new CDate($obj->user_log_date);
		$dateini = mktime_fix($dateini->getHour(),
								$dateini->getMinute(),
								$dateini->getSecond(),
								$dateini->getMonth(),
								$dateini->getDay(),
								$dateini->getYear());
		$date = new CDate();
		$date = mktime_fix($date->getHour(),
								$date->getMinute(),
								$date->getSecond(),
								$date->getMonth(),
								$date->getDay(),
								$date->getYear());
		
		$dif = new CDate("1970-01-01 00:00:00");
		$dif->addSeconds(($date - $dateini));
		
		return $dif;//->format("%H:%M:%S");
	}
		
}

function logusergetstartsessiontime(){
	$AppUI =& $_SESSION["AppUI"]; 	
	if(!is_null($AppUI->user_log_id)){
			
		$obj = new CUserLog();
		$obj->load($AppUI->user_log_id);
		$dateini = new CDate($obj->user_log_date);
		return $dateini;
		/*
		$dateini = mktime_fix($dateini->getHour(),
								$dateini->getMinute(),
								$dateini->getSecond(),
								$dateini->getMonth(),
								$dateini->getDay(),
								$dateini->getYear());
								
		$date = new CDate();
		$date = mktime_fix($date->getHour(),
								$date->getMinute(),
								$date->getSecond(),
								$date->getMonth(),
								$date->getDay(),
								$date->getYear());
		
		$dif = new CDate("1970-01-01 00:00:00");
		$dif->addSeconds(($date - $dateini));
		
		return $dif->format("%H:%M:%S");*/
	}
		
}


/**
 * @return unknown
 * @desc Ordena un array bidimensional y devuelve el mismo array ordenado
 Ejemplo: ArraySort($array, 'campo1', SORT_DESC, 'campo1', SORT_ASC)
*/
function ArraySort() {
/*
// EJEMPLO DE USO:

// Otra manera de declarar un array bidimensional de estos...
$array_a_ordenar = array(
                 0 => array('campo1' => 'patatas', 'campo2' => 1, 'campo3' => 'kkkk'),
                 1 => array('campo1' => 'coles', 'campo2' => 3, 'campo3' => 'aaaa'),
                 2 => array('campo1' => 'tomates', 'campo2' => 1, 'campo3' => 'zzzz'),
                 3 => array('campo1' => 'peras', 'campo2' => 7, 'campo3' => 'hhhh'),
                 4 => array('campo1' => 'tomates', 'campo2' => 4, 'campo3' => 'bbbb'),
                 5 => array('campo1' => 'aguacates', 'campo2' => 3, 'campo3' => 'yyyy'),
         );

$array_ordenadito = ArraySort($array, 'campo1', SORT_DESC, 'campo1', SORT_ASC) or die('<br>ERROR!<br>');
$array_ordenadito2 = ArraySort($array_a_ordenar, 'campo3', SORT_DESC, 'campo2', SORT_DESC, 'campo1', SORT_ASC ) or die('<br>ERROR!<br>'); 

*/	
  $n_parametros = func_num_args(); // Obenemos el nmero de par?etros
  if ($n_parametros<3 || $n_parametros%2!=1) { // Si tenemos el nmero de parametro mal...
    return false;
  } else { // Hasta aqu?todo correcto...veamos si los par?etros tienen lo que debe ser...
    $arg_list = func_get_args();

    if (!(is_array($arg_list[0]) && is_array(current($arg_list[0])))) {
      return false; // Si el primero no es un array...MALO!
    }
    for ($i = 1; $i<$n_parametros; $i++) { // Miramos que el resto de par?etros tb est? bien...
      if ($i%2!=0) {// Par?etro impar...tiene que ser un campo del array...
        if (!array_key_exists($arg_list[$i], current($arg_list[0]))) {
          return false;
        }
      } else { // Par, no falla...si no es SORT_ASC o SORT_DESC...a la calle!
        if ($arg_list[$i]!=SORT_ASC && $arg_list[$i]!=SORT_DESC) {
          return false;
        }
      }
    }
    $array_salida = $arg_list[0];

    // Una vez los par?etros se que est? bien, proceder?a ordenar...
    $a_evaluar = "foreach (\$array_salida as \$fila){\n";
    for ($i=1; $i<$n_parametros; $i+=2) { // Ahora por cada columna...
      $a_evaluar .= "  \$campo{$i}[] = \$fila['$arg_list[$i]'];\n";
    }
    $a_evaluar .= "}\n";
    $a_evaluar .= "array_multisort(\n";
    for ($i=1; $i<$n_parametros; $i+=2) { // Ahora por cada elemento...
      $a_evaluar .= "  \$campo{$i}, SORT_REGULAR, \$arg_list[".($i+1)."],\n";
    }
    $a_evaluar .= "  \$array_salida);";
    // La verdad es que es m? complicado de lo que cre? en principio... :)

    eval($a_evaluar);
    return $array_salida;
  }
}

function filterQueryString($field){
	$query=array();
	$par = explode("&", $_SERVER['QUERY_STRING']);
	if (is_array($field)){
		foreach ($par as $par_str)
		{
			$tmp_par = explode("=",$par_str);
			if (! in_array($tmp_par[0], $field))
				$query[]=$tmp_par[0]."=".$tmp_par[1];
				
		}		
	}else
		foreach ($par as $par_str)
		{
			$tmp_par = explode("=",$par_str);
			if ($tmp_par[0]!=$field)
				$query[]=$tmp_par[0]."=".$tmp_par[1];
				
		}	
	return implode("&",$query);
}

function checkpost($string){
	return str_replace ( array ( '&', '"', "'", '<', '>' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), $string );
	//return htmlentities(trim($string), ENT_QUOTES, 'iso-8859-1');
	//return htmlentities(trim($string), ENT_QUOTES, 'UTF-8');
}

?>
