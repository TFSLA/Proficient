<?

if (getDenyRead($m)) {
    $AppUI->redirect( "m=public&a=access_denied" );
}
$AppUI->redirect( 'm=timexp&a=vw_sup_day');
?>