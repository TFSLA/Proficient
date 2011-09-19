<?php
$source_year 		= dpGetParam( $_POST, "source_year", 0 );
$destination_year 	= dpGetParam( $_POST, "destination_year", 0 );
$holliday_company 	= dpGetParam( $_POST, "holliday_company", "NULL" );

if (!$canEdit || ($AppUI->user_type != 1 && $m == "system")) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

if ( !$source_year || !$destination_year )
{	
	$AppUI->setMsg( "Invalid source or destination year", UI_MSG_ERROR );
	$AppUI->redirect();
}

$sql="
	SELECT * FROM hollidays 
	WHERE holliday_year = $source_year 
	AND holliday_company ".($holliday_company == "NULL" ? "IS NULL" : "= '$holliday_company'");
$tmpRows=db_loadlist($sql);
$h = new CHolliday();

/*
Array con las columnas y los nombre de las variables de la clase para
verificar duplicidad de registros
*/
$arCols=array();
$arCols[0]['colname']="holliday_day";
$arCols[0]['coltype']="int";
$arCols[0]['colvalue']="holliday_day";
$arCols[1]['colname']="holliday_month";
$arCols[1]['coltype']="int";
$arCols[1]['colvalue']="holliday_month";
$arCols[2]['colname']="holliday_year";
$arCols[2]['coltype']="int";
$arCols[2]['colvalue']="holliday_year";
$arCols[3]['colname']="holliday_company";
$arCols[3]['coltype']="int";
$arCols[3]['colvalue']="holliday_company";
/*
Fin Array
*/

foreach($tmpRows as $tmpRow){
    $h->load($tmpRow['holliday_id']);
    $arRegVal=array();
    $arRegVal[0]['colname']='holliday_day';
    $arRegVal[0]['coltype']='int';
    $arRegVal[0]['regvalue']=$h->holliday_day;
    $arRegVal[1]['colname']='holliday_month';
    $arRegVal[1]['coltype']='int';
    $arRegVal[1]['regvalue']=$h->holliday_month;
    $arRegVal[2]['colname']='holliday_year';
    $arRegVal[2]['coltype']='int';
    $arRegVal[2]['regvalue']=$destination_year;
    $arRegVal[3]['colname']='holliday_company';
    $arRegVal[3]['coltype']='int';
    $arRegVal[3]['regvalue']=$h->holliday_company;
    $strTable="hollidays";

    $strSql="";
    if(!($msg=DuplicateHolliday($strTable, $arRegVal))){
        if(!($h->holliday_month==2 && $h->holliday_day==29 && $destination_year % 4 > 0)){

           $strSql="INSERT INTO $strTable (holliday_day, holliday_month, holliday_year, holliday_name, holliday_company) VALUES (";
           $strSql.=$h->holliday_day . ", " . $h->holliday_month . ", " . $destination_year . ", '" . $h->holliday_name . "', ".$holliday_company;
           $strSql.=")";
           if ( !db_exec( $strSql ) )
           {
                $AppUI->setMsg( db_error(), UI_MSG_ERROR );
           }
        }
    }

}
$AppUI->redirect();

function DuplicateHolliday($strTableName, $arRegVal){
     $stringSql="SELECT * FROM ". $strTableName ." WHERE";
        $intCount=0;
        $intarSize=count($arRegVal);
        foreach($arRegVal as $ColName){
            $intCount++;
            $stringSql.= " " . $ColName['colname'];
            switch($ColName['coltype']){
                case 'string':
                            $strOperador=" LIKE '";
                            break;
                case 'int':
                            $strOperador=" = ";
                            break;
            }
            //si la columna es obligatoria para verificar la duplicidad
            //compruebo si fue enviado el valor o se usa el default NULL
            if(is_null($ColName['regvalue'])){
                $strOperador = " IS NULL";
            }
            $stringSql.=$strOperador;

            if(!is_null($ColName['regvalue'])){
                $stringSql.=$ColName['regvalue'];
                switch($ColName['coltype']){
                    case 'string':
                                $stringSql.="'";
                                break;
                }
            }
            if($intCount < $intarSize){
                $stringSql.= " AND ";
            }
        }
        //return $stringSql;
        $arRows=db_loadColumn($stringSql);
        if($arRows===false){
            return "queryError";
        }
        if(count($arRows)!=0){
            return "Duplicated Registry";
        }
        return NULL;
}
?>
