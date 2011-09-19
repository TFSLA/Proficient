<?php /* $Id: import.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
$AppUI->savePlace();

// To configure an aditional filter to use in the search string
$additional_filter = "";
// retrieve any state parameters
if (isset( $_GET['where'] )) {
	$AppUI->setState( 'ContIdxWhere', $_GET['where'] );
}
if (isset( $_GET["search_string"] )){
	$AppUI->setState ('ContIdxWhere', "%".$_GET['search_string']);
				// Added the first % in order to find instrings also
	$additional_filter = "OR contact_first_name like '%{$_GET['search_string']}%'
	                      OR contact_last_name  like '%{$_GET['search_string']}%'
						  OR contact_company    like '%{$_GET['search_string']}%'
						  OR contact_notes      like '%{$_GET['search_string']}%'
						  OR contact_email      like '%{$_GET['search_string']}%'";
}
$where = $AppUI->getState( 'ContIdxWhere' ) ? $AppUI->getState( 'ContIdxWhere' ) : '%';

$orderby = 'contact_order_by';

// Pull First Letters
$let = ":";
$sql = "
SELECT DISTINCT UPPER(SUBSTRING($orderby,1,1)) as L
FROM contacts
WHERE contact_public=1
	OR (contact_public=0 AND contact_owner=$AppUI->user_id)
	OR contact_owner IS NULL OR contact_owner = 0
";
$arr = db_loadList( $sql );
foreach( $arr as $L ) {
    $let .= $L['L'];
}

// optional fields shown in the list (could be modified to allow breif and verbose, etc)
$showfields = array(
	// "test" => "concat(contact_first_name,' ',contact_last_name) as test",    why do we want the name repeated?
	"contact_company" => "contact_company",
	"contact_phone" => "contact_phone",
	"contact_email" => "contact_email"
);

// assemble the sql statement
$sql = "SELECT contact_id, contact_order_by, ";
foreach ($showfields as $val) {
	$sql.="$val,";
}
$sql.= "contact_first_name, contact_last_name, contact_phone
FROM contacts
WHERE (contact_order_by LIKE '$where%' $additional_filter)
	AND (contact_public=1
		OR (contact_public=0 AND contact_owner=$AppUI->user_id)
		OR contact_owner IS NULL OR contact_owner = 0
	)
ORDER BY $orderby
";

$carr[] = array();
$carrWidth = 4;
$carrHeight = 4;

$res = db_exec( $sql );
$rn = db_num_rows( $res );

$t = floor( $rn / $carrWidth );
$r = ($rn % $carrWidth);

if ($rn < ($carrWidth * $carrHeight)) {
	for ($y=0; $y < $carrWidth; $y++) {
		$x = 0;
		//if($y<$r)	$x = -1;
		while (($x<$carrHeight) && ($row = db_fetch_assoc( $res ))){
			$carr[$y][] = $row;
			$x++;
		}
	}
} else {
	for ($y=0; $y < $carrWidth; $y++) {
		$x = 0;
		if($y<$r)	$x = -1;
		while(($x<$t) && ($row = db_fetch_assoc( $res ))){
			$carr[$y][] = $row;
			$x++;
		}
	}
}

$tdw = floor( 100 / $carrWidth );

/**
* Contact search form
*/
 // Let's remove the first '%' that we previously added to ContIdxWhere
$default_search_string = substr($AppUI->getState( 'ContIdxWhere' ), 1, strlen($AppUI->getState( 'ContIdxWhere' )));

$form = "<form action='./index.php' method='get'>".$AppUI->_('Search for')."
           <input type='text' name='search_string' value='$default_search_string' />
		   <input type='hidden' name='m' value='contacts' />
		   <input type='submit' value='>' />
		   <a href='./index.php?m=contacts&amp;search_string='>".$AppUI->_('Reset search')."</a>
		 </form>";
// En of contact search form

$a2z = "\n<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";
$a2z .= "\n<tr>";
$a2z .= "<td width='100%' align='right'>" . $AppUI->_('Show'). ": </td>";
$a2z .= '<td><a href="./index.php?m=contacts&where=0">' . $AppUI->_('All') . '</a></td>';
for ($c=65; $c < 91; $c++) {
	$cu = chr( $c );
	$cell = strpos($let, "$cu") > 0 ?
		"<a href=\"?m=contacts&where=$cu\">$cu</a>" :
		"<font color=\"#999999\">$cu</font>";
	$a2z .= "\n\t<td>$cell</td>";
}
$a2z .= "\n</tr>\n<tr><td colspan='28'>$form</td></tr></table>";

// setup the title block
$titleBlock = new CTitleBlock( 'Contacts', 'contacts.gif', $m, "colaboration.index" );
$titleBlock->addCell( $a2z );
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new contact').'">', '',
		'<form action="?m=contacts&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->show();
?>

<table width="100%" border="0" cellpadding="1" cellspacing="1"  class="contacts">
<tr>
<td>

</td>
</tr>
</table>
