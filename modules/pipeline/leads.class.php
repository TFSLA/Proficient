<?php /* DEPARTMENTS $Id: leads.class.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
##
## CDepartment Class
##

class CLead {
	var $id                = NULL;
	var $lead_creator      = NULL;
	var $lead_owner        = NULL;
	var $accountmanager    = NULL;
    var $_accountmanagername = NULL;
	var $segment           = NULL;
	var $accountname       = NULL;
	var $projecttype       = NULL;
	var $opportunitysource = NULL;
	var $thirdparties      = NULL;
	var $description       = NULL;
	var $competition       = NULL;
	var $totalincome       = NULL;
	var $cost              = NULL;
	var $margin            = NULL;
	var $revised           = NULL;
	var $probability       = NULL;
	var $closingdate       = NULL;
	var $invoicedate       = NULL;
	var $duration          = NULL;
	var $status            = NULL;

	var $clientfeedback     = NULL;
	var $casestudy          = NULL;
	var $teamcomments       = NULL;
	var $selectedcompetitor = NULL;
	var $referenceaccount   = NULL;
	var $opportunitycode  = NULL;


	function CLead() {
		// empty constructor
	}

	function load( $oid ) {
		//$sql = "SELECT * FROM salespipeline WHERE id = $oid";
        $sql = "SELECT salespipeline.*, CONCAT(users.user_first_name, ' ', users.user_last_name) AS _accountmanagername
                FROM salespipeline LEFT JOIN users ON salespipeline.accountmanager = users.user_id
                WHERE salespipeline.id = $oid";
		return db_loadObject( $sql, $this );
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		/*if ($this->id == NULL) {
			return 'lead id is NULL';
		}*/
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed ";
		}
		if( $this->id ) {
			$ret = db_updateObject( 'salespipeline', $this, 'id', false );
		} else {
			$ret = db_insertObject( 'salespipeline', $this, 'id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "DELETE FROM salespipeline WHERE id = $this->id";		
		if (!db_exec( $sql )) 
		{
			return db_error();
		}
		else
		{
			$sql = "DELETE FROM `salespipelinecontacts` WHERE idsalespipeline = $this->id";
			if ( !db_exec( $sql ) )
			{
				return db_error();
			}
			else
			{
				$sql = "DELETE FROM `salespipelinefiles` WHERE idsalespipeline = $this->id";
				if ( !db_exec( $sql ) )
				{
					return db_error();
				}	
				else
				{
					return NULL;
				}
			}			
		}
	}
	
	function getContacts()
	{
		$sql = "SELECT salespipelinecontacts.id
		FROM salespipelinecontacts
		WHERE idsalespipeline = $this->id ORDER BY date";
		
		return db_loadList( $sql );
	}
	
	function getContactsByPipeline($id=null)
	{				
		$pipeline = $id ? $id : $this->id;
		$sql = "SELECT c.contact_id, c.contact_order_by
		 FROM contacts AS c INNER JOIN contacts_relations AS cr ON c.contact_id = cr.contact_id
		 WHERE cr.relation_type_id = $pipeline AND cr.relation_type = 'leads'";
		
		$rta = db_loadHashList($sql);
		return $rta;
	}
	
	function getFiles()
	{
		$sql = "SELECT salespipelinefiles.id
		FROM salespipelinefiles
		WHERE idsalespipeline = $this->id ORDER BY date";
		
		return db_loadList( $sql );
	}
	
	function getAllowedLeads($user_id = 0, $type = 0){
		global $AppUI;
		
		if($user_id == 0)
		{
			$user_id = $AppUI->user_id;
			$type = $AppUI->user_type;
		}

		if($type == 1)
			$where = "";
		else
			$where = "WHERE lead_owner = ".$user_id." OR lead_creator = ".$user_id;
		
		$sql = "SELECT id, accountname FROM salespipeline ".$where." ORDER BY accountname";
		
		return db_loadHashList( $sql );
	}
}

class CLeadContact {
	var $id                = NULL;
	var $idsalespipeline   = NULL;
	var $summary           = NULL;
	var $description       = NULL;
	var $date              = NULL;
	var $kindofcontact     = NULL;
	var $probability       = NULL;

	function CLeadContact() {
		// empty constructor
	}

	function load( $oid ) {
		$sql = "SELECT * FROM salespipelinecontacts WHERE id = $oid";
		return db_loadObject( $sql, $this );
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		/*if ($this->id == NULL) {
			return 'lead contact id is NULL';
		}*/
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed ";
		}
		if( $this->id ) {
			$ret = db_updateObject( 'salespipelinecontacts', $this, 'id', false );
		} else {
			$ret = db_insertObject( 'salespipelinecontacts', $this, 'id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "DELETE FROM salespipelinecontacts WHERE id = '$this->id';";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}


class CLeadFile {
	var $id                = NULL;
	var $idsalespipeline   = NULL;
	var $date              = NULL;
	var $shortdesc         = NULL;
	var $longdesc          = NULL;
	var $filename          = NULL;


	function CLeadContact() {
		// empty constructor
	}

	function load( $oid ) {
		$sql = "SELECT * FROM salespipelinefiles WHERE id = $oid";
		return db_loadObject( $sql, $this );
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
		/*if ($this->id == NULL) {
			return 'lead file id is NULL';
		}*/
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed ";
		}
		if( $this->id ) {
			$ret = db_updateObject( 'salespipelinefiles', $this, 'id', false );
		} else {
			$ret = db_insertObject( 'salespipelinefiles', $this, 'id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		$sql = "DELETE FROM salespipelinefiles WHERE id = '$this->id';";
		if (!db_exec( $sql )) {
			return db_error();
		} else {
			return NULL;
		}
	}
}

?>