<?php
	# prevent caching
	$t_content_expire = config_get('content_expire');

/*
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Cache-Control" content="no-cache" />
<meta http-equiv="Pragma-directive" content="no-cache" />
<meta http-equiv="Cache-Directive" content="no-cache" />
<meta http-equiv="Expires" content="<?php echo $t_content_expire ?>" />
*/
?>
