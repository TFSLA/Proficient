<?php
$strSql = "SELECT * FROM location_countries";
$arCountries = db_loadList($strSql);

$strSqlStates = "SELECT * FROM location_states";

$strSqlUsers = "SELECT * FROM users";
$arUsers = db_loadList($strSqlUsers);

$oUser = new CUser();

$intCountriesFindIt = 0;
$intStatesFindIt = 0;
if($arUsers && $arCountries){
	foreach($arUsers as $rUser){
		$strSqlStatesTmp = $strSqlStates;
		$bCountryFindIt = false;
		$bStateFindIt = false;
		
		if($oUser->bind($rUser)){
			foreach($arCountries as $rCountry){
				if(strtoupper(trim($oUser->user_country)) == strtoupper($rCountry["country_name"])){
					$oUser->user_country_id = $rCountry["country_id"];
					$bCountryFindIt = true;
					$intCountriesFindIt++;
					break;
				}
			}
			if(!$bCountryFindIt){
				$oUser->user_country_id = 0;
			}else{
				$strSqlStatesTmp .= " WHERE country_id = '{$oUser->user_country_id}'";
			}
			
			$arStates = db_loadList($strSqlStatesTmp);
			
			if($arStates){
				foreach($arStates as $rState){
					if(strtoupper(trim($oUser->user_state)) == strtoupper($rState["state_name"])){
						$oUser->user_state_id = $rState["state_id"];
						$bStateFindIt = true;
						$intStatesFindIt++;
						if(!$bCountryFindIt) {
							$oUser->user_country_id = $rState["country_id"];			
							$intCountriesFindIt++;
						}
						break;
					}
				}

				if(!$bStateFindIt){
					$oUser->user_state_id = 0;
				}
			}
			
			if($oUser->store()){
				echo "Error Actualizando el usuario " . $oUser->user_id . "<br>";
			}
		}
	}
	echo "Paises Encontrados: $intCountriesFindIt en ".count($arUsers)." usuarios<br>";
	echo "Provincias Encontradas: $intStatesFindIt en ".count($arUsers)." usuarios<br>";
}

?>