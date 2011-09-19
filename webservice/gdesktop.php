<?
	//include all process php
	include("kb_projectDocuments.inc.php");//GetKbItems

	require_once("nusoap.inc.php");

	$server = new soap_server;

	$ns="http://tfsla.psa.webservice";

	$server->configurewsdl('TFSLA.PSA.WebService',$ns);
	$server->wsdl->schematargetnamespace=$ns;

	$server->register('GetKbProjectsItems', array('username' => 'xsd:string', 'password' => 'xsd:string', 'date' => 'xsd:string', 'indexItems' => 'xsd:int', 'firstItem' => 'xsd:int'), array('return' => 'xsd:string'), $ns);

	if (isset($HTTP_RAW_POST_DATA)) {
		$input = $HTTP_RAW_POST_DATA;
	}
	else {
		$input = implode("\r\n", file('php://input'));
	}

	$server->service($input);
	exit();
?>