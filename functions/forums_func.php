<?php /* FUNCTIONS $Id: forums_func.php,v 1.1 2009-05-19 21:15:27 pkerestezachi Exp $ */
$filters = array( '- Filters -' );

if ($a == 'viewer') {
	array_push( $filters,
		'My Watched',
		'Last 30 days'
	);
} else {
	array_push( $filters,
		'My Forums',
		'My Watched',
		'My Projects',
		'My Company',
		'Inactive Projects'
	);
}

natcasesort($filters);

?>