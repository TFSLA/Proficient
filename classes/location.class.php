<?php

class CLocation {
	
	var $countries = null;
	var $states = null;
	var $item = array();
	var $items = array();//almacena items que seran insertados en States
	
	var $_frm_name = "";
	var $_cbo_country_name = "countries";
	var $_cbo_state_name = "states";
	var $_state_id_selected = "";
	
	var $_addItems_forEachCountry_inStates = true;//agrega los items almacenados en el array Items en el array States por cada pais
	var $_breset_Items = true;//una vez insertados los registros se vacia
	
	function loadCountries(){
		$strSql = "	SELECT country_id, country_name 
							FROM location_countries 
							ORDER BY country_name";
		
		$this->countries = db_loadHashList($strSql);
	}
	
	function loadStates($intCountry = null){
		$arTmp = array();
		
		$strSql = "	SELECT country_id, state_id, state_name 
					FROM location_states ";
		if($intCountry) $strSql .= "WHERE country_id = '$intCountry' ";
		$strSql .= "ORDER BY country_id, state_name";

		$arStates = db_loadList($strSql);
		$this->states = $arStates;
		
		if($this->_addItems_forEachCountry_inStates && count($this->items) > 0){
			$intCountryId = "";
			foreach($arStates as $rRow){
				if($rRow["country_id"] != $intCountryId){
					$intCountryId = $rRow["country_id"];
					foreach($this->items as $kItem => $rItem){
						$this->addItemAtBeginOfStates($this->addItemState($intCountryId, key($rItem), $rItem[key($rItem)]));
					}
				}
			}
			if($this->_breset_Items) $this->items = array();
		}
	}
	
	/*returns countries array*/
	function Countries(){
		return $this->countries;
	}
	
	/*returns states array*/
	function States(){
		return $this->states;
	}
	
	function getCountryName($country_id){
		$vReturn = array();
			
		$strSql = "	SELECT country_id, country_name 
					FROM location_countries
					WHERE country_id = '$country_id'
					";
		
		db_loadHash($strSql, $vReturn);
		return $vReturn;
	}
	
	function getStateName($country_id, $state_id){
		$vReturn = array();
		
		$strSql = "	SELECT state_id, state_name
					FROM location_states
					WHERE country_id = '$country_id' AND state_id = '$state_id'
					";
		db_loadHash($strSql, $vReturn);
		
		return $vReturn;
	}
	
	/*$bstore: si se almacenan esos items en Items para ser luego 
	insertados en States por cada registro de Country*/
	function addItem($value, $text, $bstore=false){
		if($bstore){
			$this->items[] = array($value => $text);
		}else{
			$this->item = array($value => $text);
		}

		return $this->item;
	}
	
	function addItemState($country, $state, $statename){
		$arTmp = array("country_id" => $country,
						"state_id" => $state,
						"state_name" => $statename	
						);
		return $this->addItem(0, $arTmp);
	}
	
	function addItemAtBeginOf(&$target, $item){
		if($target != "" && (is_array($item) || $item !== "")){
			$target = arrayMerge($item, $target);
		}
	}
	
	function addItemAtBeginOfCountries($item){
		$this->addItemAtBeginOf($this->countries, $item);
	}
	
	function addItemAtBeginOfStates($item){
		$arTmp = array();
		//corro los indices en uno asi puedo insertar el item
		foreach($this->States() as $k => $r){
			$k++;
			$arTmp[$k] = $r;
		}
		$this->states = $arTmp;
		$arTmp = null;
		$this->addItemAtBeginOf($this->states, $item);
	}

	/*metodos y propiedades para el form generado*/
	function setFrmName($strName){
		$this->_frm_name = $strName;
	}
	function getFrmName(){
		return $this->_frm_name;
	}
	
	function setCboCountries($strName){
		$this->_cbo_country_name = $strName;
	}
	function getCboCountries(){
		return $this->_cbo_country_name;
	}
	
	function setCboStates($strName){
		$this->_cbo_state_name = $strName;
	}
	function getCboStates(){
		return $this->_cbo_state_name;
	}
	
	function setJSSelectedState($value){
		$this->_state_id_selected = $value;
	}
	function getJSSelectedState(){
		return $this->_state_id_selected;
	}
	
	
	/*genera el array con las provincias*/
	function _JSgenerateArrayStates(){
		global $AppUI;
		$strJS = "var arStates = new Array();\n";
		
		if($this->States()){
			foreach($this->States() as $rState){
				$strJS .= "arStates[arStates.length] = new Array({$rState["country_id"]}, {$rState["state_id"]}, \"".$AppUI->_($rState["state_name"])."\");\n";
			}
		}
		
		return $strJS;
	}
	
	/*genera las funciones que realizan la actualizacion de los cbos*/
	function _JSgenerateFunctions(){
		$strJS = "";
		$strJS .= "	function selectState(){
						var f = document.".$this->getFrmName().";
						f.".$this->getCboStates().".options[0].selected = true;
					}\n
					";

		$strJS .= "	function changeState() {\n
						var sel = document.". $this->getFrmName().".".$this->getCboStates().";
						var f = document.".$this->getFrmName().";
						// Remove options
						while ( sel.length != 0 ) {
							sel[0] = null;
						}
						var index = f.".$this->getCboCountries().".selectedIndex;
						var jur = f.".$this->getCboCountries()."[index].value;
				
						for( i = 0 ; i < arStates.length ; i++) {
								if ( arStates[i][0] == jur ) {
								//  matches
								var opt = new Option(arStates[i][2], arStates[i][1], false, false);
								sel.options[sel.options.length] = opt;
							}
						}
						selectState();
					}			
				";
		
		$strJS .= "function findState(){
					var f = document.".$this->getFrmName().";
					if(intIdState != \"\"){
						for(x=0; x < f.".$this->getCboStates().".options.length; x++){
							if(f.".$this->getCboStates().".options[x].value == intIdState){
								f.".$this->getCboStates().".options[x].selected = true;
								break;
							}
						}
						f.".$this->getCboStates().".selectedValue;
					}
				}
				";
		
		return $strJS;
	}
	
	/*genera el codigo JS que va en la pagina*/
	function generateJS(){
		
		$strJS = "";
		
		$strJS .= "var intIdState = ";
		
		if($this->getJSSelectedState() != 0){
			$strJS .= $this->getJSSelectedState()."\n";
		}else{
			$strJS .= "'';\n";
		}
		
		$strJS .= $this->_JSgenerateArrayStates();
		$strJS .= $this->_JSgenerateFunctions();
		
		return $strJS;
	}
	
	function generateJScallFunctions($withTag=true){
		$strJS = "	changeState();
					findState();
				";
		if($withTag) $strJS = "<script language=\"javascript\">$strJS</script>";
		
		return $strJS;
	}
	
	
	function generateHTMLcboCountries($selected="", $class="", $attrib=""){
		return arraySelect($this->Countries(), $this->getCboCountries(), "class=\"$class\" $attrib onchange=\"javascript:changeState();\"", $selected, true);
	}
	
	function generateHTMLcboStates($selected="", $class="", $isEmpty=true, $attrib=""){
		$arStatesTmp = array();
		// falta implementar que !$isEmpty deberia cargar en $arStatesTmp algun valor seteado con un metodo en una variable
		return arraySelect($arStatesTmp, $this->getCboStates(), "class=\"$class\" $attrib", $selected, true);
	}
}

?>