<?php /* COMPANIES $Id: index.php,v 1.6 2009-07-21 15:40:13 nnimis Exp $ */
$AppUI->savePlace();
global $allowedProjects;

$objPrj = new CProject();
$prjs = $objPrj->getAllowedRecords($AppUI->user_id, "project_id");
$allowedProjects = (count($prjs) > 0 ? "\n\t IN (" . implode( ',', array_keys($prjs) ) . ')' : "\n\t is null");

//Valido que tenga permisos para el modulo
if (getDenyRead("companies"))
	 $AppUI->redirect( "m=public&a=access_denied" );


// retrieve any state parameters
if (isset( $_GET['orderby'] )) {
	$AppUI->setState( 'CompIdxOrderBy', $_GET['orderby'] );
}
$orderby = $AppUI->getState( 'CompIdxOrderBy' ) ? $AppUI->getState( 'CompIdxOrderBy' ) : 'company_name';

// load the company types
$types = dPgetSysVal( 'CompanyType' );

// get any records denied from viewing
$obj = new CCompany();
$deny = $obj->getDeniedRecords( $AppUI->user_id );

// Company search by Kist
$search_string = dPgetParam( $_POST, 'search_string', '' );
$search_string = dPformSafe($search_string, true);

// setup the title block
$titleBlock = new CTitleBlock( 'Companies', 'handshake.gif', $m, "$m.$a" );

if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('new company').'">', '',
		'<form action="?m=companies&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->show();

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'CompaniesIdxTab', $_GET['tab'] );
}
$companiesTypeTab = defVal( $AppUI->getState( 'CompaniesIdxTab' ), 0 );
$companiesType = $companiesTypeTab;

$tabBox = new CTabBox( "?m=companies", "{$AppUI->cfg['root_dir']}/modules/companies/", $companiesTypeTab );
foreach($types as $type_name){
	$tabBox->add('vw_companies', $type_name);
}

// Only display the All option in tabbed view, in plain mode it would just repeat everything else
// already in the page
if ( $companiesTypeTab != -1 ) $tabBox->add('vw_companies', 'All Companies');

$tabBox->show();
?>