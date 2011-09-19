<?
	//include all process php
	include("mail2.inc.php");
	
	require_once("nusoap.inc.php");

	$server = new soap_server;

	$ns="http://tfsla.psa.webservice";
	
	//echo(SendArticleLink('14149989', '459', '97', '0', '0', 'prueba a mano1','mas prubea1','s1', '0', '0'));

	$server->configurewsdl('TFSLA.PSA.WebService',$ns);
	$server->wsdl->schematargetnamespace=$ns;

	$server->register('IsAllowedUser', array('code' => 'xsd:string',
										 'user_email' => 'xsd:string',
										 'item_email' => 'xsd:string',
										 'subject' => 'xsd:string'
										 ), array('return' => 'xsd:string'), $ns);
	
	$server->register('SendArticleLink', array('code' => 'xsd:string',
										   'user_id' => 'xsd:string',
										   'project' => 'xsd:string',
										   'opportunity' => 'xsd:string',
										   'section' => 'xsd:string',
										   'title' => 'xsd:string',
										   'body' => 'xsd:string',
										   'type' => 'xsd:string',
										   'notify_type' => 'xsd:string'
										   ), array('return' => 'xsd:string'), $ns);
										   
	$server->register('SendTodo', array('code' => 'xsd:string',
											'user_id' => 'xsd:string',
											'user_email_assigned' => 'xsd:string',
											'project' => 'xsd:string',
											'description' => 'xsd:string',
											'due_date' => 'xsd:string',
											'notify_type' => 'xsd:string'
											), array('return' => 'xsd:string'), $ns);
											
	$server->register('SearchOpportunity', array('code' => 'xsd:string',
										 'body' => 'xsd:string'
										 ), array('return' => 'xsd:string'), $ns);										
										   		
	if (isset($HTTP_RAW_POST_DATA)) {
		$input = $HTTP_RAW_POST_DATA;
	}
	else {
		$input = implode("\r\n", file('php://input'));
	}

	$server->service($input);
	exit();
?>