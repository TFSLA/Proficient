<?php 

	require_once($AppUI->getLibraryClass("cpaint/cpaint.inc"));

	$cp = new cpaint();
	$cp->register('calculate_tax');
	$cp->start();
	$cp->return_data();

	function calculate_tax($sales_amount) {
		return($sales_amount * 0.075);
	} 


?>