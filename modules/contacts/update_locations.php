<?php
$strSql = "SELECT * FROM location_countries";
$arCountries = db_loadList($strSql);

$strSqlStates = "SELECT * FROM location_states";

$strSqlList = "SELECT * FROM contacts";
$arList = db_loadList($strSqlList);

$oObj = new CContact();

$intCountriesFindIt = 0;
$intStatesFindIt = 0;
if($arList && $arCountries){
	foreach($arList as $rList){
		$strSqlStatesTmp = $strSqlStates;
		$bCountryFindIt = false;
		$bStateFindIt = false;
		
		if($oObj->bind($rList)){
			foreach($arCountries as $rCountry){
				if(strtoupper(trim($oObj->contact_country)) == strtoupper($rCountry["country_name"])){
					$oObj->contact_country_id = $rCountry["country_id"];
					$bCountryFindIt = true;
					$intCountriesFindIt++;
					break;
				}
			}
			if(!$bCountryFindIt){
				$oObj->contact_country_id = 0;
			}else{
				$strSqlStatesTmp .= " WHERE country_id = '{$oObj->contact_country_id}'";
			}
			
			$arStates = db_loadList($strSqlStatesTmp);
			
			if($arStates){
				foreach($arStates as $rState){
					if(strtoupper(trim($oObj->contact_state)) == strtoupper($rState["state_name"])){
						$oObj->contact_state_id = $rState["state_id"];
						$bStateFindIt = true;
						$intStatesFindIt++;
						if(!$bCountryFindIt) {
							$oObj->contact_country_id = $rState["country_id"];			
							$intCountriesFindIt++;
						}
						break;
					}
				}

				if(!$bStateFindIt){
					$oObj->contact_state_id = 0;
				}
			}
			
			if($oObj->store()){
				echo "Error Actualizando el usuario " . $oObj->contact_id . "<br>";
			}
		}
	}
	echo "Paises Encontrados: $intCountriesFindIt en ".count($arList)." contactos<br>";
	echo "Provincias Encontradas: $intStatesFindIt en ".count($arList)." contactos<br>";
}

?>