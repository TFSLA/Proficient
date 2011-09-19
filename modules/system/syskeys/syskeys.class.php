<?php /* SYSKEYS $Id: syskeys.class.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */

include_once( $AppUI->getSystemClass ('dp' ) );

##
## CSysKey Class
##

class CSysKey extends CDpObject {
    var $syskey_id = NULL;
	var $syskey_name = NULL;
	var $syskey_label = NULL;
	var $syskey_type = NULL;
	var $syskey_sep1 = NULL;
	var $syskey_sep2 = NULL;
    var $_sysColsName = NULL;

	function CSysKey($name=null, $label=null, $type='0', $sep1="\n", $sep2 = '|' ) {
		$this->CDpObject( 'syskeys', 'syskey_id' );
		$this->syskey_name = $name;
		$this->syskey_label = $label;
		$this->syskey_type = $type;
		$this->syskey_sep1 = $sep1;
		$this->syskey_sep2 = $sep2;
	}

    //verifica que no exista el campo que no es PK con el mismo nombre
    function SysDuplicateRegistry($columnsname){
        $this->_sysColsName = $columnsname;
        $stringSql="SELECT * FROM ". $this->_tbl ." WHERE";
        $intCount=0;
        $intarSize=count($this->_sysColsName);
        foreach($this->_sysColsName as $ColName){
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
            /*  $ColName['colvalue'] posee el nombre de la variable del objeto que almacena
                el valor de la columna a compararse
            */
            ${$ColName['colvalue']}=$ColName['colvalue'];

            //si la columna es obligatoria para verificar la duplicidad
            //compruebo si fue enviado el valor o se usa el default NULL
            if(is_null($this->${$ColName['colvalue']})){
                $strOperador = " IS NULL";
            }
            $stringSql.=$strOperador;

            if(!is_null($this->${$ColName['colvalue']})){
                $stringSql.=$this->${$ColName['colvalue']};
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
}

##
## CSysVal Class
##

class CSysVal extends CDpObject {
	var $sysval_id = NULL;
	var $sysval_key_id = NULL;
	var $sysval_title = NULL;
	var $sysval_value = NULL;
    var $_sysColsName = NULL;

	function CSysVal( $key=null, $title=null, $value=null ) {
		$this->CDpObject( 'sysvals', 'sysval_id' );
		$this->sysval_key_id = $key;
		$this->sysval_title = $title;
		$this->sysval_value = $value;
	}
    //verifica que no exista el campo que no es PK con el mismo nombre
    function SysDuplicateRegistry($columnsname){
        $this->_sysColsName = $columnsname;
        $stringSql="SELECT * FROM ". $this->_tbl ." WHERE";
        $intCount=0;
        $intarSize=count($this->_sysColsName);
        foreach($this->_sysColsName as $ColName){
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
            /*  $ColName['colvalue'] posee el nombre de la variable del objeto que almacena
                el valor de la columna a compararse
            */
            ${$ColName['colvalue']}=$ColName['colvalue'];

            //si la columna es obligatoria para verificar la duplicidad
            //compruebo si fue enviado el valor o se usa el default NULL
            if($this->${$ColName['colvalue']}=="NULL"){
                $strOperador = " IS NULL";
            }
            $stringSql.=$strOperador;

            if($this->${$ColName['colvalue']}!="NULL"){
                $stringSql.=$this->${$ColName['colvalue']};
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
}

?>
