<?php /* FORUMS $Id: do_watch_forum.php,v 1.1 2009-05-19 21:15:43 pkerestezachi Exp $ */
##
## Change forum watches
##
$watch = isset( $_POST['watch'] ) ? $_POST['watch'] : 0;

if ($watch) {
	// clear existing watches
	$sql = "DELETE FROM forum_watch WHERE watch_user = $AppUI->user_id AND watch_$watch IS NOT NULL";
	if (!db_exec($sql)) {
		$AppUI->setMsg( db_error(), UI_MSG_ERROR );
	} else {
		$sql = '';
		foreach ($_POST as $k => $v) {
			if (strpos($k, 'forum_') !== FALSE) {
				$sql = "INSERT INTO forum_watch (watch_user,watch_$watch) VALUES ($AppUI->user_id,".substr( $k, 6 ).")";
				if (!db_exec($sql)) {
					$AppUI->setMsg( db_error(), UI_MSG_ERROR );
				} else {
					$AppUI->setMsg( "Watch updated", UI_MSG_OK );
				}
			}
		}
	}
} else {
	$AppUI->setMsg( 'Incorrect watch type passed to sql handler.', UI_MSG_ERROR );
}
?>