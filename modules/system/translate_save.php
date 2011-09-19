<?php /* SYSTEM $Id: translate_save.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
/**
* Processes the entries in the translation form.
* @version $Revision: 1.1 $
* @author Andrew Eddie <users.sourceforge.net>
*/

/*
Verifica que no exista el mismo registro
Params:
    $filecontent:string con el contenido del archivo
    $words:array con palabras a buscar coincidentes
*/

$AppUI->redirect( "m=public&a=access_denied" );

function findDuplicate($filecontent, $words){
    $findit=false;
    
        foreach($words as $word){
           if(!(strpos($filecontent,$word)==false)){
           //if (isset($filecontent[$word])){
                $findit=true;
                continue;
           }
        }
    return $findit;
}

$module = isset( $HTTP_POST_VARS['module'] ) ? $HTTP_POST_VARS['module'] : 0;
$lang = isset( $HTTP_POST_VARS['lang'] ) ? $HTTP_POST_VARS['lang'] : 'es';

$trans = isset( $HTTP_POST_VARS['trans'] ) ? $HTTP_POST_VARS['trans'] : 0;
//echo '<pre>';print_r( $trans );echo '</pre>';die;


$trans_filename = "{$AppUI->cfg['root_dir']}/locales/$lang/$module.inc";
$file_content = file_get_contents($trans_filename);
if ( strlen($file_content)){
	//echo "tiene contenido";
	//eval ("\$trans_tbl = array (".$file_content.");");
}else{
	$AppUI->setMsg( "Could not open locales file to read.", UI_MSG_ERROR );
	//$AppUI->redirect( "m=system" );
}


echo "<pre>";

//echo $trans_filename. " ".file_exists($trans_filename)."\n";
var_dump($trans_tbl);
echo "</pre>";

/*
if (!($filep = fopen ($trans_filename, "w+"))) {
	$AppUI->setMsg( "Could not open locales file to save.", UI_MSG_ERROR );
	$AppUI->redirect( "m=system" );
}
*/
$txt = "##\n## DO NOT MODIFY THIS FILE BY HAND!\n##\n";
	
$rta = true;
if ($lang == 'en') {
// editing the english file
	for ($i=0; $i < count($trans); $i++){
		$langs = $trans[$i];
		$abbrev = $trans[$i]["abbrev"];
		$english = $trans[$i]["english"];
		$del = $trans[$i]["del"];
		//es un alta
		if ($i==0){
			if (trim($abbrev).trim($english) != "" && empty($del)){
				$abbrev = addslashes( stripslashes(@$abbrev));
				$english = addslashes( stripslashes($english));
				
				$id_lng = empty($abbrev) ? $english : $abbrev;
				echo "id: $id_lng";
				//verifico que la palabra no exista
				//if (in_array($id_lng,$trans_tbl)){
				if (findDuplicate($file_content, array($abbrev, $english))){
					echo "es duplicado";
					$rta=false;
				}else{
	  			if (!empty($abbrev)) {
	  				$txt .= "\"{$abbrev}\"=>";
	  			}
	  			$txt .= "\"{$english}\",\n";
				}				
			}		
		}else{
			if (trim($abbrev).trim($english) != "" && empty($del)){
				$abbrev = addslashes( stripslashes(@$abbrev));
				$english = addslashes( stripslashes($english));
				
  			if (!empty($abbrev)) {
  				$txt .= "\"{$abbrev}\"=>";
  			}
  			$txt .= "\"{$english}\",\n";
				
			}
		}
	
	}
	
} else {
// editing the translation
	foreach ($trans as $langs) {
		if ( empty($langs['del']) ) {
			$langs['english'] = addslashes( stripslashes( $langs['english'] ) );
			$langs['lang'] = addslashes( stripslashes( $langs['lang'] ) );
			//fwrite( $fp, "\"{$langs['english']}\"=>\"{$langs['lang']}\",\n" );
			$txt .= "\"{$langs['english']}\"=>\"{$langs['lang']}\",\n";
		}
	}
}

//echo "<pre> $txt </pre>";
if ($rta){
	if (!($filep = fopen ($trans_filename, "w+"))) {
		$AppUI->setMsg( "Could not open locales file to save.", UI_MSG_ERROR );
		$AppUI->redirect( "m=system" );
	}	
	fwrite( $filep, $txt );
	fclose( $filep );

	$AppUI->setMsg( "Locales file saved", UI_MSG_OK );
}else{
  $AppUI->setMsg("Registro Duplicado", UI_MSG_ERROR);
}


$AppUI->redirect();

?> 