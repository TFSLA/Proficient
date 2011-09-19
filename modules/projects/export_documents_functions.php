<?php

function do_post_request($url, $data, $optional_headers = null)
{
	$params = array('http' => array(
		'method' => 'POST',
		'content' => $data
	));

	if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	}

	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
	
	if (!$fp) {
		throw new Exception("Problem with $url, $php_errormsg");
	}
	
	$response = @stream_get_contents($fp);
	
	if ($response === false) {
		throw new Exception("Problem reading data from $url, $php_errormsg");
	}

	return $response;
}

function do_cut_file($zohoUrl, $zohoProject, $zohoProjectFolder, $docName, $docFile, $handle)
{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_URL, $zohoUrl);
		curl_setopt($ch, CURLOPT_POST, true);

		$post = array(
			"projId"=>$zohoProject,
			"folderid"=>$zohoProjectFolder,
			"tags"=>"",
			"docname"=>$docName,
			"uploaddoc"=>"@".$docFile
		);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		
		$response = curl_exec($ch);
						
		$xmlObj = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
		
		if($xmlObj[0] == "Request Processed Successfully")
		{
			fwrite($handle, "OK = ".$docName."\n");
			return true;
		}
		else
		{
			fwrite($handle, "failed (".$xmlObj[0].") = ".$docName."\n");
			return false;
		}
}

?>