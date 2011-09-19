<?php
$titleBlock = new CTitleBlock( 'Functional Area', 'users.gif', $m, '' );
$titleBlock->addCrumb( "?m=companies", "companies list" );
$titleBlock->show();

echo $AppUI->_( 'areaIndexPage' );
?>