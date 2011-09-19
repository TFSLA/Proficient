<?php /* CONTACTS $Id: contacts.class.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
/**
 *	@package psa
 *	@subpackage modules
 *	@version $Revision: 1.1 $
*/

require_once( $AppUI->getSystemClass ('dp' ) );
require_once( $AppUI->getSystemClass( 'location' ) );

/**
* Contacts class
*/
class CContact extends CDpObject{
/** @var int */
	var $contact_id = NULL;
/** @var string */
	var $contact_first_name = NULL;
/** @var string */
	var $contact_last_name = NULL;
	var $contact_order_by = NULL;
	var $contact_title = NULL;
	var $contact_birthday = NULL;
	var $contact_company = NULL;
	var $contact_company_phone = NULL;
	var $contact_type = NULL;
	var $contact_email = NULL;
	var $contact_email2 = NULL;
	var $contact_phone = NULL;
	var $contact_phone2 = NULL;
	var $contact_business_phone = NULL;
	var $contact_business_phone2 = NULL;
	var $contact_fax = NULL;
	var $contact_mobile = NULL;
	var $contact_address1 = NULL;
	var $contact_address2 = NULL;
	var $contact_city = NULL;
	var $contact_state_id = 0;
	var $contact_state = NULL;
	var $contact_zip = NULL;
	var $contact_icq = NULL;
	var $contact_notes = NULL;
	var $contact_project = NULL;
	var $contact_country_id = 0;
	var $contact_country = NULL;
	var $contact_icon = NULL;
	var $contact_owner = NULL;
	var $contact_public = NULL;

	var $contact_website = NULL;
	var $contact_manager = NULL;
	var $contact_assistant = NULL;
	var $contact_department = NULL;
	var $country_name = NULL;
	var $state_name = NULL;

/**
 *	Binds an array/hash to this object
 *	@param int $oid optional argument, if not specifed then the value of current key is used
 *	@return any result from the database operation
 */
	function load( $oid=null , $strip = true) {
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		$oid = $this->$k;
		if ($oid === null) {
			return false;
		}
		$sql = "SELECT c.*, s.state_name, location_countries.country_name
		FROM contacts c 
		LEFT JOIN  location_states s ON c.contact_country_id=s.country_id AND c.contact_state_id=s.state_id
		LEFT JOIN  location_countries ON c.contact_country_id=location_countries.country_id
		WHERE contact_id=$oid";
		return db_loadObject( $sql, $this, false, $strip );
	}

	function CContact() {
		$this->CDpObject( 'contacts', 'contact_id' );
	}

	function check() {
		if ($this->contact_id === NULL) {
			return 'contact id is NULL';
		}
	// ensure changes of state in checkboxes is captured
		$this->contact_public = intval( $this->contact_public );
		$this->contact_owner = intval( $this->contact_owner );
		$this->contact_creator = intval( $this->contact_creator );
		return NULL; // object is ok
	}
	
	function getCompanies($user_id){
		$sql=	"select distinct contact_company, contact_company
				from contacts 
				WHERE ( contact_public = 1 )
				OR ( contact_public = 0 AND contact_owner = $user_id )";
		
		return db_loadHashList($sql);
	}
	
	function getIdByFullname($first_name, $last_name,$contact_company, $middle_name){
		global $AppUI;
		
		
		if (($first_name=="")and($last_name!="")and($contact_company!=""))
		{
		 $tipo = 1;
		}

        if (($first_name!="")and($last_name=="")and($contact_company!=""))
		{
		$tipo = 2;
		}

        if (($last_name!="")and($first_name!="")and($contact_company!=""))
		{
		$tipo = 3;
		}

		if (($last_name!="")and($first_name!="")and($contact_company==""))
		{
		$tipo = 4;
		}

        
		switch($tipo)
		{
			case "1":
		        $sql = "select contact_id from contacts where 
		        contact_last_name = '".db_escape($last_name)."'
                and contact_company = '".db_escape($contact_company)."'
				and contact_owner ='".$AppUI->user_id."'";
			break;
			case "2":
		        $sql = "select contact_id from contacts where
			    contact_first_name = '".db_escape($first_name)."'
                and contact_company = '".db_escape($contact_company)."'
				and contact_owner ='".$AppUI->user_id."'";
		    break;
			case "3":
		        $sql = "select contact_id from contacts where
				contact_first_name = '".db_escape($first_name)."'
				and contact_last_name = '".db_escape($last_name)."'
                and contact_company = '$contact_company'
				and contact_owner ='".$AppUI->user_id."'";
			break;
			case "4":
		        $sql = "select contact_id from contacts where
				contact_first_name = '".db_escape($first_name)."'
				and contact_last_name = '".db_escape($last_name)."'
				and contact_owner ='".$AppUI->user_id."'";
			break;
			default:
                $sql = "select contact_id from contacts where
				contact_first_name = '".db_escape($first_name)."'
				and contact_last_name = '".db_escape($last_name)."'
                and contact_company = '".db_escape($contact_company)."'
				and contact_owner ='".$AppUI->user_id."'";
		}
       

		return db_loadResult($sql);
	}
	/*
	<uenrico>
	*/
	function getCountry($id=NULL, $country_id=NULL){
		$vReturn = "";
		$arCountry = array();
		
		$id = !is_null($id) ? $id : $this->contact_id;

		if(is_null($id)) return false;
		
		if(!is_null($country_id)){
			$arCountry = CContact::getCountryName($country_id);
		}else{
			$strSql = "	SELECT location_countries.country_id, location_countries.country_name
						FROM contacts
							LEFT JOIN location_countries ON
								contacts.contact_country_id = location_countries.country_id
						WHERE contacts.contact_id = '$id'
						";
			db_loadHash($strSql, $arCountry);
		}
		
		if(count($arCountry) > 0){
			if($arCountry["country_id"] == 0 || is_null($arCountry["country_id"])) $arCountry["country_name"] = "Not Specified"; 
			$vReturn = $arCountry["country_name"];
		}
		
		return $vReturn;
		
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
	
	function getState($id=NULL, $country_id=NULL, $state_id=NULL){
		$vReturn = "";
		$arState = array();
		
		$id = !is_null($id) ? $id : $this->contact_id;

		if(is_null($id)) return false;
		
		if(!is_null($state_id) && !is_null($country_id)){
			$arState = CContact::getStateName($country_id, $state_id);
		}else{
			$strSql = "	SELECT location_states.country_id, location_states.state_id, location_states.state_name
						FROM contacts
							LEFT JOIN location_states ON
								contacts.contact_country_id = location_states.country_id AND
								contacts.contact_state_id = location_states.state_id
						WHERE contacts.contact_id = '$id'
						";
			db_loadHash($strSql, $arState);
		}
		
		if(count($arState) > 0){
			if($arState["state_id"] == 0 || is_null($arState["state_id"])) $arState["state_name"] = "Not Specified";
			$vReturn = $arState["state_name"];
		}
		
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
	/*
	</uenrico>
	*/
	
	function contact_exists($first_name, $last_name, $company, $public){
    
		$sql = "select contact_id from contacts where
		contact_last_name = '".db_escape($last_name)."' AND contact_first_name = '"
	    .db_escape($first_name)."' AND contact_company = '".$company
	    ."' AND contact_public  = ".$public;
	    
	    
	    $result = mysql_query($sql);
	    $rows = mysql_num_rows($result);
	    
	    if($rows>0) {
	    	$row = mysql_fetch_array($result);
	    	return $row['contact_id'];
	    }
	    else {return 0; }
	}
	
	function getContactId($first_name, $last_name, $company, $public){
    
		$sql = "select contact_id from contacts where
		contact_last_name = '".db_escape($last_name)."' AND contact_first_name = '"
	    .db_escape($first_name)."' AND contact_company = '".$company
	    ."' AND contact_public  = ".$public;
	    
	    $result = mysql_query($sql);
	    $row = mysql_fetch_array($result);
	    
	    return $row['contact_id'];
	}
	
	
	function getCountryString($country_id){
		$sql = "SELECT country_name FROM location_countries WHERE country_id = ".$country_id;
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		
		return $row['country_name'];
	}
	
	function getStateString($state_id){
		$sql = "SELECT state_name FROM location_states WHERE state_id = ".$state_id;
		$result = mysql_query($sql);
		$row = mysql_fetch_array($result);
		
		return $row['state_name'];
	}
	
	function getAllContacts(){
		$sql = "SELECT contact_id, CONCAT_WS(' ',contact_first_name,contact_last_name)
		 FROM contacts";
		
		$rta = db_loadHashList($sql);
		
		return $rta;
	}
	
	function getRelatedCompanies($contact_id=null){
		$id = $contact_id ? $contact_id : $this->contact_id;
		$sql = "(SELECT c.company_id, c.company_name FROM companies AS c
				INNER JOIN contacts_relations AS cr ON cr.relation_type_id = c.company_id
				WHERE cr.contact_id = $id AND cr.relation_type = 'companies')
				UNION
				(SELECT c.company_id, c.company_name FROM companies AS c
				INNER JOIN contacts AS ct ON ct.contact_company = c.company_name
				WHERE ct.contact_id = $id)
				ORDER BY company_name";
		$rta = db_loadHashList($sql);
		return $rta;
	}
	
	function getRelatedProjects($contact_id=null){
		$id = $contact_id ? $contact_id : $this->contact_id;
		$sql = "SELECT p.project_id, p.project_name FROM projects AS p
				INNER JOIN contacts_relations AS cr ON cr.relation_type_id = p.project_id
				WHERE cr.contact_id = $id AND cr.relation_type = 'projects' ORDER BY p.project_name";
		$rta = db_loadHashList($sql);
		return $rta;
	}
	
	function getRelatedLeads($contact_id=null){
		$id = $contact_id ? $contact_id : $this->contact_id;
		$sql = "SELECT l.id, l.accountname FROM salespipeline AS l
				INNER JOIN contacts_relations AS cr ON cr.relation_type_id = l.id
				WHERE cr.contact_id = $id AND relation_type = 'leads' ORDER BY l.accountname";
		$rta = db_loadHashList($sql);
		return $rta;
	}
	
	function getRelatedEvents($contact_id=null){
		$id = $contact_id ? $contact_id : $this->contact_id;
		$sql = "SELECT e.event_id, e.event_title FROM events AS e
				INNER JOIN events_invitations AS ei ON ei.event_id = e.event_id
				WHERE ei.contact_id = $id ORDER BY e.event_title";
		$rta = db_loadHashList($sql);
		return $rta;
	}
	
	function deleteRelations($contact_id=null){
		$id = $contact_id ? $contact_id : $this->contact_id;
		$sql = "DELETE FROM contacts_relations
				WHERE contact_id = $id";
		mysql_query($sql);
		return mysql_error();
	}
	
	function saveRelation($relation_type, $type_id, $contact_id=null){
		global $AppUI;
		
		$date = date("Y-m-d h:i:s");
		$id = $contact_id ? $contact_id : $this->contact_id;
		
		$sql = "INSERT INTO contacts_relations 
			(contact_id, relation_type_id, date, relation_creator, relation_type)
			VALUES
			($id, $type_id, '$date', $AppUI->user_id, '$relation_type')";
		
		mysql_query($sql);
		
		return mysql_error();
	}
}
?>