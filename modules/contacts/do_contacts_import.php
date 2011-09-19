<?php /* CONTACTS $Id: do_contacts_import.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
//print_r($_GET);
include ('./functions/delegates_func.php');
$delegator_id = dPgetParam( $_GET, "delegator_id", $AppUI->user_id );
$mod_id = 6;
//$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $user_id );


set_time_limit(3600);
ignore_user_abort(true);

$msg = '';

$permisos = "";

if ( $delegator_id != $AppUI->user_id )
{
	require_once( $AppUI->getModuleClass( "admin" ) );
	$usr = new CUser();
	$usr->load( $AppUI->user_id );		
	if ( !$usr->isDelegator($delegator_id, $mod_id) && $AppUI->user_type != 1 )
	{		
		$AppUI->setMsg( 'Delegator' );
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$delegador = new CUser();
	$delegador->load( $delegator_id );
	$permisos = $delegador->getDelegatePermission( $AppUI->user_id, $mod_id );	
	$canEdit = $canEdit || ( $permisos == "AUTHOR" || $permisos == "EDITOR" );
	do_log($delegator_id, $mod_id, $AppUI, 3);	
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Contacts' );

if ( !$canEdit )
{
	$AppUI->redirect( "m=public&a=access_denied" );
}		

//echo "<p>POST = <pre>";print_r( $_POST );echo "</pre></p>";
$format = dpGetParam( $_POST, "format", "" );

$upload = null;

if ( isset( $_FILES['file'] ) ) 
{
	$upload = $_FILES['file'];

	if ($upload['size'] < 1) 
	{
		
		$AppUI->setMsg( 'Upload file size is zero. Process aborted.', UI_MSG_ERROR );
		$AppUI->redirect();		
	} 
	else
	{
		$newName = $AppUI->getConfig( "root_dir" )."/files/temp/".$upload['name'];
		if (!is_dir($AppUI->getConfig( "root_dir" )."/files/temp/")){
			mkdir($AppUI->getConfig('root_dir').'/files/timexp/',0755);
			mkdir($AppUI->getConfig( "root_dir" )."/files/temp/",0755);  //Crear Path si no existe
		}
		if ( move_uploaded_file( $upload['tmp_name'], $newName ) )
		{
            //Array con los distintos separadores de campos
            $arSeparadores=array();
            $arSeparadores[0]['original']=",";
            $arSeparadores[0]['reemplazo']="[co]";
            $arSeparadores[1]['original']=";";
            $arSeparadores[1]['reemplazo']="[pc]";
            $arSeparadores[2]['original']=chr(9);
            $arSeparadores[2]['reemplazo']="[tb]";

            //variable formato de archivo
            $bFormatOK = false;
			
			//Procesar el archivo
			switch ( $format )
			{
				case "Outlook": importFromOutlook( $newName ) ; break;
				case "Outlook Express": importFromOutlookExpress( $newName ); break;
				case "vCard": importFromVCard( $newName ); break;
				case "Palm Desktop": importFromPalmDesktop( $newName ); break;
				case "Netscape": importFromNetscape( $newName ); break;
				case "Yahoo": importFromYahoo( $newName ); break;
				default:
					$AppUI->setMsg( "Format '$format' is unknown.", UI_MSG_ERROR );
					$AppUI->redirect();
			}
			unlink( $newName );
		}
		else
		{
			$AppUI->setMsg( 'Cannot upload file. Process aborted.', UI_MSG_ERROR );
			$AppUI->redirect();
		}
	}
}

//exit;
if($bFormatOK){
    $AppUI->setMsg( "imported", UI_MSG_OK, true );
    $AppUI->redirect();
}else{
    $AppUI->setMsg( "Error: Wrong format.", UI_MSG_ERROR );
    $AppUI->redirect();
}


function detectLanguage($strContent){
    $strValue="Nombre";//si la encuentra dentro de los titulos es espa�l
    $strLanguage="en";
    if(!(strpos($strContent,$strValue)===false)){
        $strLanguage="es";
    }
    return $strLanguage;
}

function parseChar($strContent, $strOldChar, $strNewChar){
    $contenido = $strContent;
    $intOpen=0;
    $intClose=0;
    $intComa=0;
    $intLenOldChar = strlen($strOldChar);//tomo el tama� de los caracteres a reemplazar para establecer el corrimiento
    $intLenNewChar = strlen($strNewChar);
    $intAjuste = $intLenNewChar - $intLenOldChar;//establece el ajuste que debe hacerse una vez reemplazado
    $bSearch=true;
    $bHayOldChar = false;

    while($bSearch){
        $intOpen=strpos($contenido,"\"", $intClose);
        if($intOpen===false){
            $bSearch=false;
        }else{
            $intClose=strpos($contenido, "\"", $intOpen+1);
        }
        if(!($intOpen===false)){
                $bHayOldChar=true;
        }
        while($bHayOldChar){
            $intComa=strpos($contenido, $strOldChar, $intOpen);
            if(!($intComa===false) && $intComa < $intClose){
                    $contenido = substr($contenido,0,($intComa)) . $strNewChar . substr($contenido, $intComa + $intLenOldChar);
                    $intClose += $intAjuste;
             }else{
                $bHayOldChar=false;
            }
        }

        if($intClose===false){
            $bSearch=false;
        }else{
            $intClose++;//lo aumento en 1 para que busque el $intOpen de la proxima coma sino busca la misma
        }
    }

    return $contenido;
}

//debe enviarse la 1linea donde estan los titulos ya que ahi no hay , ni ; ni tabs dentro de los textos
//devuelve el caracter de separacion
function detectSeparator($strContent, $arSeparators){
    $intMax=0;
    $strSeparator = NULL;
    foreach($arSeparators as $separator){
        $arValue=explode($separator['original'], $strContent);
        if(sizeof($arValue) > $intMax){
            $intMax = sizeof($arValue);
            $strSeparator['original'] = $separator['original'];
            $strSeparator['reemplazo'] = $separator['reemplazo'];
        }
    }
    return $strSeparator;
}

function detectQuote($strContent){
    $strValue="";
    if(substr($strContent,0,1)=="\""){
        $strValue = "\"";
    }

    return $strValue;
}

function restoreChars($strFieldValue, $arChars){
    $strReturnValue=str_replace($arChars['reemplazo'], $arChars['original'], $strFieldValue);

    return $strReturnValue;
}

function detectFormat($strContent, $arFields){
    $intContador=0;
    $bOk=false;
    foreach($arFields as $field){
        if(!(strpos($strContent, $field)===false)){
            $intContador++;
        }
    }
    if($intContador > 0){
        $bOk=true;
    }

    return $bOk;
}

function importFromOutlook($name){
    global $arSeparadores, $bFormatOK;
    $contenido = file_get_contents($name);

    $contenido = parseChar($contenido,"\r\n"," ");//elimino los enters dentro de las comillas
    $lineas = split("\r\n", $contenido);
    $titulos = $lineas[0];

    unset ($lineas[0]);//elimino los titulos
    array_pop($lineas);//elimino el ultimo elemento que es vacio

    if(detectLanguage($titulos)=="es"){
        @require_once("mapeo_outlook_es.php");
    }else{
        @require_once("mapeo_outlook.php");
    }

    if(detectFormat($contenido, $mapeo)){
        $bFormatOK = true; //es el mismo tipo de archivo que el valor del combo seleccionado

        $strSpecialMark=detectQuote($titulos);//detecto si los campos estan delimitados con comillas

        $strSeparador=detectSeparator($titulos, $arSeparadores); //detecto el tipo de separador de campos que usa

        foreach($lineas as $linea){
            $linea=parseChar($linea, $strSeparador['original'], $strSeparador['reemplazo']);

            $arDatos  = explode($strSeparador['original'], $linea);
            $arCampos = explode($strSeparador['original'],$titulos);

            $hash=array();
            foreach($mapeo as $k=>$v2){
                $v=$strSpecialMark.$v2.$strSpecialMark;//agrego si tiene comillas a los titulos de los campos

                $v=$v2;

                $strValue=(array_search($v, $arCampos)===false) ? "" : $arDatos[array_search($v, $arCampos)];

                if($strValue=="" && $mapeoAlt[$k]!=""){
                    $strValue=(array_search($mapeoAlt[$k], $arCampos)===false) ? "" : $arDatos[array_search($mapeoAlt[$k], $arCampos)];
                }
                
				$strValue = str_replace(";","",$strValue);
                $strValue = str_replace(",","",$strValue);
				$strValue = str_replace("\"","",$strValue);
                $hash[$k] = restoreChars(str_replace("\"","",$strValue), $strSeparador);

            }

			$hash["contact_first_name"] = str_replace("\"","",$hash["contact_first_name"]);

            $hash["contact_order_by"] = $hash["contact_last_name"].", ".$hash["contact_first_name"];
            $hash["contact_birthday"] = substr($hash["contact_birthday"], strlen($hash["contact_birthday"]) - 4, 4)."-".substr($hash["contact_birthday"], 3, 2)."-".substr($hash["contact_birthday"], 0, 2);
            storeContact( $hash );
         }
     }

}

function importFromOutlookExpress($name){
    global $arSeparadores, $bFormatOK;
    $contenido = file_get_contents($name);

    $contenido = parseChar($contenido,"\r\n"," ");//elimino los enters dentro de las comillas
    $lineas = split("\r\n", $contenido);
    $titulos = $lineas[0];

    unset ($lineas[0]);//elimino los titulos
    array_pop($lineas);//elimino el ultimo elemento que es vacio

    if(detectLanguage($titulos)=="es"){
        @require_once("mapeo_outlook_express_es.php");
    }else{
        @require_once("mapeo_outlook_express.php");
    }

    if(detectFormat($contenido, $mapeo)){
        $bFormatOK = true; //es el mismo tipo de archivo que el valor del combo seleccionado

        $strSpecialMark=detectQuote($titulos);//detecto si los campos estan delimitados con comillas

        $strSeparador=detectSeparator($titulos, $arSeparadores); //detecto el tipo de separador de campos que usa
        
		$arCampos = explode($strSeparador['original'],$titulos);
        foreach($lineas as $linea){
            $linea=parseChar($linea, $strSeparador['original'], $strSeparador['reemplazo']);

            $arDatos = explode($strSeparador['original'], $linea);
            
            $hash=array();
            foreach($mapeo as $k=>$v2){
                $v=$strSpecialMark.$v2.$strSpecialMark;//agrego si tiene comillas a los titulos de los campos

                $strValue=(array_search($v, $arCampos)===false) ? "" : $arDatos[array_search($v, $arCampos)];

                if($strValue=="" && $mapeoAlt[$k]!=""){
                    $strValue=(array_search($mapeoAlt[$k], $arCampos)===false) ? "" : $arDatos[array_search($mapeoAlt[$k], $arCampos)];
                }
                $strValue = str_replace(";","",$strValue);
                $strValue = str_replace(",","",$strValue);
                $hash[$k] = restoreChars(str_replace("\"","",$strValue), $strSeparador);

            }
            $hash["contact_order_by"] = $hash["contact_last_name"].", ".$hash["contact_first_name"];
            $hash["contact_birthday"] = substr($hash["contact_birthday"], strlen($hash["contact_birthday"]) - 4, 4)."-".substr($hash["contact_birthday"], 3, 2)."-".substr($hash["contact_birthday"], 0, 2);
            storeContact( $hash );
          }
    }
}

function importFromVCard($name)
{
	GLOBAL $delegator_id, $AppUI, $bFormatOK;

	$contenido = file_get_contents( $name );
	$lineas = split( "\r\n", $contenido );

    //uso para buscar estas palabras dentro del archivo 
    $mapeo[0]="BEGIN:VCARD";

    if(detectFormat($contenido, $mapeo)){
        $bFormatOK = true; //es el mismo tipo de archivo que el valor del combo seleccionado

        for ( $i = 0; $i < count( $lineas ); $i++ )
        //foreach($lineas as $linea)
    	{
			
            if ( strtoupper($lineas[$i]) == "BEGIN:VCARD" )
    		{
                //Empieza una nueva VCard
				$hash=array();
            }

            $prop = split( ":", $lineas[$i] );
            $nomProp = $prop[0];
            $nomsProp = split( ";", $nomProp );
            $valProp = $prop[1];
            $valsProp = split( ";", $valProp );
            echo "<p>Propiedad = '$nomProp', valor = '$valProp'</p>";
            switch ( strtoupper( $nomsProp[0] ) )
            {
                case "N":
                    echo "<p>Propiedad N, valsProp[0] = $valsProp[0], valsProp[1] = $valsProp[1]</p>";
                    $hash["contact_last_name"] = $valsProp[0];
                    $hash["contact_first_name"] = $valsProp[1];
					$hash["contact_middle_name"] = $valsProp[2];
                    //Aca se puede completar para mas especificidad de nombres
                    break;
                case "FN": 
					$hash["contact_order_by"] = $valsProp[0];
                    break;
                case "ORG":
                    $hash["contact_company"] = $valsProp[0];
                    $hash["contact_department"] = $valsProp[1];
                    break;
                case "TEL":
                    echo "<p>Propiedad TEL</p>";
                    switch ( strtoupper( $nomsProp[1] ) )
                    {
                        case "HOME":
                            echo "<p>Telefono particular</p>";
                            if ( strtoupper( $nomsProp[2] ) == "VOICE" )
                            {
                                echo "<p>Telefono de voz</p>";
                                $hash["contact_phone"] = $valsProp[0];
                            }
                            else
                            {
                                $hash["contact_fax"] = $valsProp[0];
                            }
                            break;
                        case "WORK":
                            if(strtoupper( $nomsProp[2] ) == "VOICE"){
                                $hash["contact_business_phone"] = $valsProp[0];
                            }else{
                                $hash["contact_business_phone2"] = $valsProp[0];
                            }

                            break;
                        case "CELL":
                            $hash["contact_mobile"] = $valsProp[0];
                            break;
                    }
                    break;
                case "ADR":
                    //Chequear que viene despues del adr
                    $hash["contact_address1"] = $valsProp[2];
                    //$hash["contact_address2"] = $valsProp[3];
                    $hash["contact_city"] = $valsProp[3];
                    $hash["contact_state"] = $valsProp[4];
                    $hash["contact_zip"] = $valsProp[5];
                    $hash["contact_country"] = $valsProp[6];
                    break;
                case "BDAY":
                    $hash["contact_birthday"] = $valsProp[0];
                    break;
                case "EMAIL":
                    $hash["contact_email"] = $valsProp[0];
                    break;
                case "TITLE":
                    $hash["contact_title"] = $valsProp[0];
                    break;
                case "AGENT":
                    /*
                    Es otra vCard
                    $hash["contact_assistant"] = $valsProp[0];
                    break;
                    */
                case "NOTE":
                    $hash["contact_notes"] = $valsProp[0];
                    break;

            }
            if ( strtoupper($lineas[$i]) == "END:VCARD" )
            {
            	storeContact( $hash );
            	
            	
               /* $hash = array();
                foreach( $contacto as $k=>$v )
                {
                    $hash[$k] = $contacto[$v];
                    //echo "<p>Mapeando $k en '{$campos[$v]}'</p>";
                }*/
                //storeContact($contacto);
                //die(print_r($contacto));
								//storeContact( $hash );
                //$contacto->store();
            }
        }
    }
}

function importFromPalmDesktop($name)
{
	$mapeo = array(
	"contact_first_name" => "1",
	"contact_last_name" => "3",
 	"contact_title" => "7",
  	"contact_company" => "5",
	"contact_email" => "55",
  	"contact_email2" => "57",
  	"contact_phone" => "37",
  	"contact_phone2" => "38",
  	"contact_mobile" => "40",
  	"contact_address1" => "22",
  	"contact_address2" => "23",
  	"contact_city" => "27",
  	"contact_state" => "28",
  	"contact_zip" => "29",
  	"contact_country" => "30",
  	"contact_business_phone" => "31",
  	"contact_business_phone2" => "32",
  	"contact_fax" => "30", 
  	"contact_website" => "81",
  	"contact_department" => "6",
  	"contact_assistant" => "50"
	);

    $contenido = file_get_contents( $name );
    /*
    echo "<p>Contenido = $contenido</p>";
	$lineas = split( "\r\n", $contenido );
	unset( $lineas[0] ); //La primera es la de los titulos
	foreach( $lineas as $linea )
	{
        echo $linea."<br>";
        $campos = split( ",", $linea );
		//Armar el hash y pasarlo a una funcion que lo haga objeto y lo grabe
		$hash = array();
		foreach( $mapeo as $k=>$v )
		{
			$hash[ $k ] = substr( $campos[ $v ], 1, strlen($campos[ $v ]) - 2 );
		}
		storeContact( $hash );
	}
    */
    for($i=0;$i < strlen($contenido);$i++){
        echo ord($contenido[$i])." ";
    }
    die();
}

function importFromNetscape($name)
{	
	GLOBAL $AppUI, $delegator_id, $bFormatOK;
	
	//echo "<p>Importando netscape</p>";
	$contenido = file_get_contents( $name );
	$registros = split( "\n\n", $contenido );
	
	echo "<pre>";
	var_dump($registros);
	echo "</pre>";
	exit;
/*	
	$lineas = split( "\n", $contenido );
	
	$i = 0;
	//echo "<p>Hay ".count($lineas)." lineas </p>";

    //uso para buscar estas palabras dentro del archivo
    $mapeo[0]="objectclass:";
    $mapeo[1]="cn:";

     if(detectFormat($contenido, $mapeo)){
        $bFormatOK = true; //es el mismo tipo de archivo que el valor del combo seleccionado

    	while ( $i < count( $lineas ) - 1 ) //La ultima linea hay que ignorarla
    	{
    		//echo "<p>Contacto creado</p>";
    		$hash = array();
    		while ( $j < count( $lineas ) && trim($lineas[$i]) != "" )
    		{
    			$palabras = split( ": ", $lineas[$i] );
    			switch ( strtolower($palabras[0]) )
    			{
    				case "cn":
    					$hash["contact_order_by"] = $palabras[1];
    					break;
    				case "sn":
    					$hash["contact_last_name"] = $palabras[1];
    					break;
    				case "givenname":
    					$hash["contact_first_name"] = $palabras[1];
    					break;
    				case "locality":
    					$hash["contact_city"] = $palabras[1];
    					break;
    				case "st":
    					$hash["contact_state"] = $palabras[1];
    					break;
    				case "mail":
    					$hash["contact_email"] = $palabras[1];
    					break;
    				case "postofficebox":
    					$hash["contact_address1"] = $palabras[1];
    					break;
    				case "countryname":
    					$hash["contact_country"] = $palabras[1];
    					break;
    				case "homephone":
    					$hash["contact_phone"] = $palabras[1];
    					break;
    				case "cellphone":
    					$hash["contact_mobile"] = $palabras[1];
    					break;
                    case "facsimiletelephonenumber":
                        $hash["contact_fax"] = $palabras[1];
                        break;
                    case "telephonenumber":
                        $hash["contact_business_phone"] = $palabras[1];
                        break;
                    case "o":
                        $hash["contact_company"] = $palabras[1];
                        break;
                    case "title":
                        $hash["contact_title"] = $palabras[1];
                        break;
                    case "postalcode":
                        $hash["contact_zip"] = $palabras[1];
                        break;

    			}
    			$i++;
    		}
    		$hash["contact_order_by"] = $hash["contact_last_name"].", ".$hash["contact_first_name"];
    		//echo "<p>Contacto</p>";
    		//print_r($hash);echo "<br>";    		
			storeContact( $hash );
			$i++;

    	}
    }
    */
}

function importFromNetscapeMauro($name)
{	
	GLOBAL $AppUI, $delegator_id, $bFormatOK;
	
	//echo "<p>Importando netscape</p>";
	$contenido = file_get_contents( $name );
	$lineas = split( "\n", $contenido );
	
	$i = 0;
	//echo "<p>Hay ".count($lineas)." lineas </p>";

    //uso para buscar estas palabras dentro del archivo
    $mapeo[0]="objectclass:";
    $mapeo[1]="cn:";

     if(detectFormat($contenido, $mapeo)){
        $bFormatOK = true; //es el mismo tipo de archivo que el valor del combo seleccionado

    	while ( $i < count( $lineas ) - 1 ) //La ultima linea hay que ignorarla
    	{
    		//echo "<p>Contacto creado</p>";
    		$hash = array();
    		while ( $j < count( $lineas ) && trim($lineas[$i]) != "" )
    		{
    			$palabras = split( ": ", $lineas[$i] );
    			switch ( strtolower($palabras[0]) )
    			{
    				case "cn":
    					$hash["contact_order_by"] = $palabras[1];
    					break;
    				case "sn":
    					$hash["contact_last_name"] = $palabras[1];
    					break;
    				case "givenname":
    					$hash["contact_first_name"] = $palabras[1];
    					break;
    				case "locality":
    					$hash["contact_city"] = $palabras[1];
    					break;
    				case "st":
    					$hash["contact_state"] = $palabras[1];
    					break;
    				case "mail":
    					$hash["contact_email"] = $palabras[1];
    					break;
    				case "postofficebox":
    					$hash["contact_address1"] = $palabras[1];
    					break;
    				case "countryname":
    					$hash["contact_country"] = $palabras[1];
    					break;
    				case "homephone":
    					$hash["contact_phone"] = $palabras[1];
    					break;
    				case "cellphone":
    					$hash["contact_mobile"] = $palabras[1];
    					break;
                    case "facsimiletelephonenumber":
                        $hash["contact_fax"] = $palabras[1];
                        break;
                    case "telephonenumber":
                        $hash["contact_business_phone"] = $palabras[1];
                        break;
                    case "o":
                        $hash["contact_company"] = $palabras[1];
                        break;
                    case "title":
                        $hash["contact_title"] = $palabras[1];
                        break;
                    case "postalcode":
                        $hash["contact_zip"] = $palabras[1];
                        break;

    			}
    			$i++;
    		}
    		$hash["contact_order_by"] = $hash["contact_last_name"].", ".$hash["contact_first_name"];
    		//echo "<p>Contacto</p>";
    		//print_r($hash);echo "<br>";    		
			storeContact( $hash );
			$i++;
				/*
    		echo "<p>Contacto</p>";
    		print_r($contacto);echo "<br>";
    		if ( $msg = $contacto->store() )
    		{
    			echo "<p>$msg</p>";
    			exit;
    		}
    		$i++;
    		//echo "<p>Contacto grabado</p>";*/
    	}
    }
}

function importFromYahoo($name){
    global $arSeparadores, $bFormatOK;

    $contenido = file_get_contents($name);

    $contenido = parseChar($contenido,"\n"," ");//elimino los enters dentro de las comillas
    $lineas = split("\n", $contenido);
    $titulos = $lineas[0];

    unset ($lineas[0]);//elimino los titulos
    array_pop($lineas);//elimino el ultimo elemento que es vacio

    if(detectLanguage($titulos)=="es"){
        @require_once("mapeo_yahoo_es.php");
    }else{
        @require_once("mapeo_yahoo.php");
    }

    if(detectFormat($contenido, $mapeo)){
        $bFormatOK = true; //es el mismo tipo de archivo que el valor del combo seleccionado

        $strSpecialMark=detectQuote($titulos);//detecto si los campos estan delimitados con comillas

        $strSeparador=detectSeparator($titulos, $arSeparadores); //detecto el tipo de separador de campos que usa

        foreach($lineas as $linea){
            $linea=parseChar($linea, $strSeparador['original'], $strSeparador['reemplazo']);

            $arDatos = explode($strSeparador['original'], $linea);
            $arCampos = explode($strSeparador['original'],$titulos);

            $hash=array();
            foreach($mapeo as $k=>$v2){
                $v=$strSpecialMark.$v2.$strSpecialMark;//agrego si tiene comillas a los titulos de los campos

                $strValue=(array_search($v, $arCampos)===false) ? "" : $arDatos[array_search($v, $arCampos)];

                if($strValue=="" && $mapeoAlt[$k]!=""){
                    $strValue=(array_search($mapeoAlt[$k], $arCampos)===false) ? "" : $arDatos[array_search($mapeoAlt[$k], $arCampos)];
                }
                
				$strValue = str_replace(";","",$strValue);
				$strValue = str_replace(",","",$strValue);
                $hash[$k] = restoreChars(str_replace("\"","",$strValue), $strSeparador);

            }

            $hash["contact_order_by"] = $hash["contact_last_name"].", ".$hash["contact_first_name"];
            $hash["contact_birthday"] = substr($hash["contact_birthday"], strlen($hash["contact_birthday"]) - 4, 4)."-".substr($hash["contact_birthday"], 3, 2)."-".substr($hash["contact_birthday"], 0, 2);
            storeContact( $hash );
          }
    }
}

function importFromYahoo2($name)
{
	@require_once("mapeo_yahoo.php");
	$contenido = file_get_contents( $name );
	$lineas = split( "\n", $contenido );
	$titulos = split ( ",", $lineas[0] );
	//Le voy a sacar las " a los titulos
	for ( $i = 0; $i < count($titulos); $i++ )
	{
		$titulos[$i] = substr($titulos[$i], 1, strlen($titulos[$i]) - 2);
	}
	
	echo "<p>Hay ".count($lineas)." lineas</p>";
	for ( $i = 1; $i < count($lineas) - 1; $i++ ) //Por alguna razon cuenta una linea de mas
	{
		$valores = split( ",", $lineas[$i] );
		for ( $j = 0; $j < count($valores); $j++ )
		{
            if($campos[$titulos[$j]]==""){
                $campos[$titulos[$j]] = substr( $valores[$j], 1, strlen($valores[$j]) - 2 );
			    echo "<p>$j : ['".$titulos[$j]."'] = '".$campos[$titulos[$j]]."'</p>";
            }
		}
		echo "<p><strong>Se acabo 1!</strong></p>";
		echo "<p>Campos<pre>";
		print_r( $campos );
		echo "</pre></p>";
		$hash = array();
		foreach( $mapeo as $k=>$v )
		{
			$hash[$k] = $campos[$v];
			echo "<p>Mapeando $k en '{$campos[$v]}'</p>";
		}
		$hash["contact_order_by"] = $hash["contact_last_name"].", ".$hash["contact_first_name"];
		$hash["contact_birthday"] = substr($hash["contact_birthday"], strlen($hash["contact_birthday"]) - 4, 4)."-".substr($hash["contact_birthday"], 3, 2)."-".substr($hash["contact_birthday"], 0, 2);
		echo "<p>hash</p>";
		print_r($hash);
		storeContact( $hash );
	}
	echo "<p>Listo el pollo</p>";
	//exit;
}

function storeContact( $hash )
{
	GLOBAL $delegator_id, $AppUI;
	// que hacer con los contactos existentes
	// 1 - Actualizar
	// 2 - Omitir
    
	 foreach($hash as $k=>$v2)
	 {
	  $hash[$k] =  str_replace(";","",$hash[$k]);
	  $hash[$k] =  str_replace(",","",$hash[$k]);
	  $hash[$k] =  str_replace("\"","",$hash[$k]);
	 }
    
	$hash["contact_first_name"] = $hash["contact_first_name"]." ".$hash["contact_middle_name"];

	$duplicates = dPgetParam( $_POST, "duplicates", 1 );

	$contact_id = CContact::getIdByFullname($hash["contact_first_name"]
										, $hash["contact_last_name"], $hash["contact_company"]);

	$contact_exist = ($contact_id !== null);

	$contact = new CContact();
	
	if ($contact_exist){
		switch ($duplicates){
		case 1:
			$contact->contact_id = $contact_id;
			$contact->bind( $hash );
		
			$contact->contact_owner = $delegator_id;	
			$contact->contact_creator = $AppUI->user_id;	
			if ( $msg = $contact->store() )
			{
				$AppUI->setMsg( $msg, UI_MSG_ERROR, true );
				$AppUI->redirect();
			}
			break;
			
		case 2:
			break;
		}	
	}else{
		$contact->contact_id = 0;
		$contact->bind( $hash );
	
		$contact->contact_owner = $delegator_id;	
		$contact->contact_creator = $AppUI->user_id;	
		if ( $msg = $contact->store() )
		{
			$AppUI->setMsg( $msg, UI_MSG_ERROR, true );
			$AppUI->redirect();
		}	
	}

}

?>