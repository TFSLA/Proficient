<?php require_once( 'core.php' ) ?>
<?php
	access_ensure_global_level( ADMINISTRATOR );
?>
<?php 
	html_page_top1();

	# get the phpinfo() content
	ob_start();
	phpinfo();
	$content = ob_get_contents();
	ob_end_clean();

	# get the <style> block
	$style = preg_replace( '|^.*(<style.*</style>).*$|si', '\1', $content );
	# add '.phpinfo' before each style definition
	$style = preg_replace( '/(.*\{.*\}.*)/', '.phpinfo \1', $style );
	# output the <style> block
	echo $style;

	html_page_top2();

	print_manage_menu( 'index.php?m=webtracking&a=documentation_page' );

	print_manage_doc_menu( 'index.php?m=webtracking&a=documentation_page' );

	echo '<br />';

	# output the contents of the <body> block inside a div with class phpinfo
	echo '<div class="phpinfo">';
	$body = preg_replace( '|^.*<body>(.*)</body>.*$|si', '\1', $content );
	echo $body;
	echo '</div>';
?>
<?php html_page_bottom1( __FILE__ ) ?>
