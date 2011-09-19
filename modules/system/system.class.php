<?php /* SYSTEM $Id: system.class.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */

require("calendar.class.php");

/**
* Preferences class
*/
class CPreferences {
	var $pref_user = NULL;
	var $pref_name = NULL;
	var $pref_value = NULL;

	function CPreferences() {
		// empty constructor
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return "CPreferences::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return "CPreference::store-check failed<br />$msg";
		}
		if (($msg = $this->delete())) {
			return "CPreference::store-delete failed<br />$msg";
		}
		if (!($ret = db_insertObject( 'user_preferences', $this, 'pref_user' ))) {
			return "CPreference::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "DELETE FROM user_preferences WHERE pref_user = $this->pref_user AND pref_name = '$this->pref_name'";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}

/**
* Module class
*/
class CModule extends CDpObject {
	var $mod_id=null;
	var $mod_name=null;
	var $mod_directory=null;
	var $mod_version=null;
	var $mod_setup_class=null;
	var $mod_type=null;
	var $mod_active=null;
	var $mod_ui_name=null;
	var $mod_ui_icon=null;
	var $mod_ui_order=null;
	var $mod_ui_active=null;
	var $mod_description=null;

	function CModule() {
		$this->CDpObject( 'modules', 'mod_id' );
	}

	function install() {
		$sql = "SELECT mod_directory FROM modules WHERE mod_directory = '$this->mod_directory'";
		if (db_loadHash( $sql, $temp )) {
			// the module is already installed
			// TODO: check for older version - upgrade
			return false;
		}
		$this->store();
		return true;
	}

	function remove() {
		$sql = "DELETE FROM modules WHERE mod_id = $this->mod_id";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}

	function move( $dirn ) {
		$temp = $this->mod_ui_order;
		if ($dirn == 'moveup') {
			$temp--;
			$sql = "UPDATE modules SET mod_ui_order = (mod_ui_order+1) WHERE mod_ui_order = $temp";
			db_exec( $sql );
		} else if ($dirn == 'movedn') {
			$temp++;
			$sql = "UPDATE modules SET mod_ui_order = (mod_ui_order-1) WHERE mod_ui_order = $temp";
			db_exec( $sql );
		}
		$sql = "UPDATE modules SET mod_ui_order = $temp WHERE mod_id = $this->mod_id";
		db_exec( $sql );

		$this->mod_id = $temp;
	}
// overridable functions
	function moduleInstall() {
		return null;
	}
	function moduleRemove() {
		return null;
	}
	function moduleUpgrade() {
		return null;
	}
}

class CHolliday extends CDpObject
{
	var $holliday_id 		= NULL;
	var $holliday_day		= NULL;
	var $holliday_month		= NULL;
	var $holliday_year		= NULL;
	var $holliday_name		= NULL;
	var $holliday_company	= NULL;
	
	function CHolliday()
	{
		$this->CDpObject( "hollidays", "holliday_id" );
	}
	
	function check()
	{
		if ( $this->holliday_name == "" || is_null($this->holliday_name) )
		{
			return "holliday_name is null";
		}
		
		if ( is_null($this->holliday_day) )
		{
			return "holliday_day is null";
		}
		
		if ( is_null($this->holliday_month) )
		{
			return "holliday_month is null";
		}
		
		if ( is_null($this->holliday_year) )
		{
			return "holliday_year is null";
		}
		
		return NULL;
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
        
        if($this->holliday_id)
        	$stringSql .= " AND ".$this->_tbl_key." <> ".$this->holliday_id;
        
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
    
    function getHollidays($company_id="", $holliday_year=""){
    	
		$sql = "
			SELECT distinct
				DATE_FORMAT(concat(holliday_year, '-', holliday_month,'-',  holliday_day),  '%Y%m%d')
			,   holliday_id
			FROM `hollidays`
			WHERE 
	        holliday_company is null";

		 $sql .= $holliday_year	!=""? " and holliday_year = '$holliday_year'":"";
			
		//echo "<pre>$sql</pre>";
		return db_loadHashList( $sql );        
    }
    
    function getHollidaysForPeriod($startPeriod, $endPeriod){
    	
		$db_start = $startPeriod->format( FMT_DATETIME_MYSQL );
		$db_end = $endPeriod->format( FMT_DATETIME_MYSQL );
    	
		$sql =  "SELECT distinct DATE_FORMAT(concat(holliday_year, '-', holliday_month,'-',  holliday_day),  '%Y%m%d') as holliday_date";
		$sql .= " , holliday_id, holliday_name";
		$sql .= " FROM hollidays";
		$sql .= " WHERE ( DATE_FORMAT(concat(holliday_year, '-', holliday_month,'-', holliday_day), '%Y-%m-%d %H:%i:%S') BETWEEN '".$db_start."' AND '".$db_end."' )";
		$sql .= " AND holliday_company is null";

		return db_loadList( $sql );      
    }    
}
?>