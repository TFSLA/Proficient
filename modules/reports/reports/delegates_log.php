<?
if ($AppUI->user_type!=1) {
    $AppUI->redirect( "m=public&a=access_denied" );
}
$AppUI->redirect( 'm=delegates&tab=2');
?>