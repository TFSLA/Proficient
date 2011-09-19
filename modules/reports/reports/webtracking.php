<?

//if ($AppUI->user_type!=1) {
if (getDenyRead( $m )) {
    $AppUI->redirect( "m=public&a=access_denied" );
}

$AppUI->redirect( 'm=webtracking&a=print_all_bug_page');
?>