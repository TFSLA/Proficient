<?
	//include all process php
	include("projects.inc.php");
	include("articles_files.inc.php");
	
	//echo(htmlspecialchars(GetProjects('pruebapruebaprueba', '123456')));
	//echo(htmlspecialchars(GetTasks('pruebapruebaprueba', '123456')));
	//echo(htmlspecialchars(GetSections('pruebapruebaprueba', '123456')));
	//echo(htmlspecialchars(GetCategories('pruebapruebaprueba', '123456')));
	//echo(htmlspecialchars(GetNotifications('pruebapruebaprueba', '123456')));
	
	require_once("nusoap.inc.php");

	$server = new soap_server;

	$ns="http://tfsla.psa.webservice";

	$server->configurewsdl('TFSLA.PSA.WebService',$ns);
	$server->wsdl->schematargetnamespace=$ns;

	$server->register('GetProjects', array('username' => 'xsd:string', 'password' => 'xsd:string'), array('return' => 'xsd:string'), $ns);
	$server->register('GetTasks', array('username' => 'xsd:string', 'password' => 'xsd:string'), array('return' => 'xsd:string'), $ns);
	$server->register('GetSections', array('username' => 'xsd:string', 'password' => 'xsd:string'), array('return' => 'xsd:string'), $ns);
	$server->register('GetCategories', array('username' => 'xsd:string', 'password' => 'xsd:string'), array('return' => 'xsd:string'), $ns);
	$server->register('GetNotifications', array('username' => 'xsd:string', 'password' => 'xsd:string'), array('return' => 'xsd:string'), $ns);

	if (isset($HTTP_RAW_POST_DATA)) {
		$input = $HTTP_RAW_POST_DATA;
	}
	else {
		$input = implode("\r\n", file('php://input'));
	}

	$server->service($input);
	exit();
?>