<?php /* DEPARTMENTS $Id: index.php,v 1.1 2009-05-19 21:15:43 pkerestezachi Exp $ */
$titleBlock = new CTitleBlock( 'Departments', 'users.gif', $m, '' );
$titleBlock->addCrumb( "?m=companies", "companies list" );
$titleBlock->show();

echo $AppUI->_( 'deptIndexPage' );
?>