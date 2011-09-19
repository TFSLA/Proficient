<?php /* $Id: index.php,v 1.6 2009-07-27 15:53:19 nnimis Exp $ */

//Valido que tenga permisos para el modulo
if (getDenyEdit("admin"))
	 $AppUI->redirect( "m=public&a=access_denied" );

$AppUI->savePlace();

if (isset( $_GET['tab'] )) {
    $AppUI->setState( 'UserIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'UserIdxTab' ) !== NULL ? $AppUI->getState( 'UserIdxTab' ) : 0;
$_GET['stub'] =  isset($_GET['stub']) ?  $_GET['stub'] : "0";
$_GET['utype'] =  isset($_GET['utype']) ?  $_GET['utype'] : "utype";
$_GET['ucompany'] =  isset($_GET['ucompany']) ?  $_GET['ucompany'] : "0";
$_GET['ustatus'] =  isset($_GET['ustatus']) ?  $_GET['ustatus'] : "1";

$userTypes = $utypes;
unset($userTypes[0]);
if($AppUI->user_type != 1){	
	unset($userTypes[1]);
} 

$userTypes = arrayMerge(array("-1" => "All"), $userTypes);

//cuando el tipo de usuario ingresado no esta en la lista permitida pone en -1
if (!isset($userTypes[$_GET['utype']]))
	$_GET['utype']="-1"; 



if (isset( $_GET['stub'] )) {
    $AppUI->setState( 'UserIdxStub', $_GET['stub'] );
    $AppUI->setState( 'UserIdxWhere', '' );
}
if (isset( $_GET['utype'] )) {
    $AppUI->setState( 'UserIdxUtype', $_GET['utype'] );
    $AppUI->setState( 'UserIdxWhere', '' );
}
if (isset( $_GET['ucompany'] )) {
    $AppUI->setState( 'UserIdxUcompany', $_GET['ucompany'] );
    $AppUI->setState( 'UserIdxWhere', '' );
}
if (isset( $_GET['ustatus'] )) {
    $AppUI->setState( 'UserIdxUstatus', $_GET['ustatus'] );
    $AppUI->setState( 'UserIdxWhere', '' );
}
if (isset( $_GET['where'] )) { 
    $AppUI->setState( 'UserIdxWhere', $_GET['where'] );
    //$AppUI->setState( 'UserIdxStub', '' );
}
$stub = $AppUI->getState( 'UserIdxStub' );
$where = $AppUI->getState( 'UserIdxWhere' );
$utype = $AppUI->getState( 'UserIdxUtype' );
$ucompany = $AppUI->getState( 'UserIdxUcompany' );
$ustatus = $AppUI->getState( 'UserIdxUstatus' );


if (isset( $_POST['orderby'] )) {
    $AppUI->setState( 'UserIdxOrderby', $_POST['orderby'] );
}
if (isset( $_POST['revert'] )) {
    $AppUI->setState( 'Revert', $_POST['revert'] );
}
$orderby = $AppUI->getState( 'UserIdxOrderby' ) ? $AppUI->getState( 'UserIdxOrderby' ) : 'user_username';

// Pull First Letters
$let = ":";
$sql = "SELECT DISTINCT UPPER(SUBSTRING(user_username, 1, 1)) AS L FROM users";
$arr = db_loadList( $sql );
foreach( $arr as $L ) {
    $let .= $L['L'];
}
$sql = "SELECT DISTINCT UPPER(SUBSTRING(user_first_name, 1, 1)) AS L FROM users";
$arr = db_loadList( $sql );
foreach( $arr as $L ) {
    if ($L['L'])
	$let .= strpos($let, $L['L']) ? '' : $L['L'];
}

$sql = "SELECT DISTINCT UPPER(SUBSTRING(user_last_name, 1, 1)) AS L FROM users";
$arr = db_loadList( $sql );
foreach( $arr as $L ) {
    if ($L['L'])
	$let .= strpos($let, $L['L']) ? '' : $L['L'];
}

$a2z = "\n<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";
$a2z .=	'<form name="searchFrm" action="index.php?m=admin" method="post" onsubmit="return buscar();">';
$a2z .=	'<input type="hidden" name="orderby" value="'.$orderby.'">';
$a2z .=	'<input type="hidden" name="revert" value="0">';
$a2z .=	'<input type="hidden" name="stub" value="'.$stub.'">';
$a2z .= "\n<tr>";
//$a2z .= '<td width="100%" align="left">' . $AppUI->_('Show'). ': </td>';
$a2z .= '<td>' . $AppUI->_('Show'). ': </td>';
$a2z .= '<td><a href="javascript: //" onclick="javascript: letter('."'0'".');">' . $AppUI->_('All') . '</a></td>';
for ($c=65; $c < 91; $c++) {
	$cu = chr( $c );
	$cell = strpos($let, "$cu") > 0 ?
		"<a href=\"javascript: //\" onclick=\"javascript: letter('$cu');\">$cu</a>" :
		"<font color=\"#999999\">$cu</font>";
	$a2z .= "\n\t<td>$cell</td>";
}

$cell = '<input type="text" name="where" class="text" size="10" value="'.$where.'" />';
$cell .=	' <input type="submit" value="'.$AppUI->_( 'search' ).'" class="button" />';
$a2z .= "\n\t<td nowrap=\"nowrap\">$cell</td>";
$a2z .= "\n</tr>\n</form>\n</table>";

$searchbar = $a2z;

echo('<form name="searchFrm" action="index.php?m=admin" method="post" onsubmit="return buscar();">');
echo('<input type="hidden" name="orderby" value="'.$orderby.'">');
echo('<input type="hidden" name="revert" value="0">');
echo('</form>');

$obj = new CCompany();
$companies = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$companies = arrayMerge( array( '0'=>$AppUI->_('All') ), $companies );

//form de busqueda
$formCell="<table><tr>";
$formCell.="<form action=\"index.php\" name=\"filterFrm\" method=\"get\">";
$formCell.='<input type="hidden" name="m" value="admin">';
$formCell.='<input type="hidden" name="tab" value="'.$tab.'">';
$formCell.='<input type="hidden" name="stub" value="'.$stub.'">';
$formCell.='<input type="hidden" name="ustatus" value="'.$ustatus.'">';
$formCell.="<td><input type=\"text\" name=\"where\" class=\"text\" size=\"10\" value=\"$where\" /></td>";
$formCell.="<td><input type=\"submit\" value=\"".$AppUI->_("search")."\" class=\"button\" />";
$formCell.="&nbsp;<input type=\"button\" value=\"".$AppUI->_("clear")."\" class=\"button\" onclick=\"limpiar()\"/></td>";
$formCell.="<td>&nbsp;".$AppUI->_('Company')."</td>";
$formCell.="<td>".arraySelect( $companies, 'ucompany', 'class="text" size="1" onchange="javascript: this.form.submit()"', $ucompany, true, false, '200 px')."</td>"; 
$formCell.="<td>&nbsp;".$AppUI->_('Type')."</td>";
$formCell.="<td>".arraySelect( $userTypes, 'utype', 'class="text" size="1" onchange="javascript: this.form.submit()"', $utype, true)."</td>"; 
$formCell.="<td>&nbsp;".$AppUI->_('Active')."</td>";
$formCell.="<td><input type=\"checkbox\" name=\"chkustatus\" onclick=\"javascript: this.form.ustatus.value = ".($ustatus == '1' ? '0' : '1')."; this.form.submit();\" ".($ustatus == '1' ? 'checked' : '')." ></td>";
$formCell.="</form>";
$formCell.="</tr></table>";

// setup the title block
$titleBlock = new CTitleBlock( 'User Management', 'user_management.gif', $m, "$m.$a" );
$titleBlock->addCell($formCell);

/*
$titleBlock->addCell(
	'<input type="text" name="where" class="text" size="10" value="'.$where.'" />'
	. ' <input type="submit" value="'.$AppUI->_( 'search' ).'" class="button" />',
	'',
	'<form action="index.php?m=admin" method="post">', '</form>'
);

$titleBlock->addCell( $a2z );
*/
if($AppUI->getState( 'UserIdxTab' )==0)
    $strData='<input type="button" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('add user').'" onClick="javascript:window.location=\'./index.php?m=admin&a=addedituser\';" />';
else
    $strData='<input type="button" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.$AppUI->_('add template').'" onClick="javascript:window.location=\'./index.php?m=admin&a=addedittemplate\';" />';

if($canEdit)    
	$titleBlock->addCell($strData);
$titleBlock->show();
?>
<script language="javascript">
function delMe( x, y ) {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('User');?> " + y + "?" )) {
		document.frmDelete.user_id.value = x;
		document.frmDelete.submit();
	}
}
function delTemplate( x, y ) {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('Template');?> " + y + "?" )) {
		document.frmDeleteTemplate.securitytemplate_id.value = x;
		document.frmDeleteTemplate.submit();
	}
}
function limpiar(){
	var f = document.filterFrm;
	f.stub.value="";
	f.where.value="";
	f.utype.value=-1;
	f.ucompany.value=-1;
	f.ustatus.value=1;
	f.chkustatus.checked=1;
	f.submit();
}

function letter(letra){
	document.searchFrm.stub.value = letra;
	document.searchFrm.submit();
}
function ordenar(campo, val){
	document.searchFrm.orderby.value = campo;
	document.searchFrm.revert.value = val;
	document.searchFrm.submit();
}
function buscar(){
	document.searchFrm.stub.value = 0;
	return true;
}

</script>
<?php
/*
if($AppUI->getState( 'UserIdxTab' )==0)
	$extra = '<td align="right" width="100%"><input type="button" class=button value="'.$AppUI->_('add user').'" onClick="javascript:window.location=\'./index.php?m=admin&a=addedituser\';" /></td>';
else
	$extra = '<td align="right" width="100%"><input type="button" class=button value="'.$AppUI->_('add template').'" onClick="javascript:window.location=\'./index.php?m=admin&a=addedittemplate\';" /></td>';
*/
// tabbed information boxes
$tabBox = new CTabBox( "?m=admin", "{$AppUI->cfg['root_dir']}/modules/admin/", $tab );
$tabBox->add( 'vw_active_usr', 'Users' );
$tabBox->add( 'vw_templates', 'Templates' );
$tabBox->show();
?>


<form name="frmDelete" action="" method="post">
	<input type="hidden" name="dosql" value="do_user_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="user_id" value="0" />
</form>
<form name="frmDeleteTemplate" action="./index.php?m=admin" method="post">
	<input type="hidden" name="dosql" value="do_template_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="securitytemplate_id" value="0" />
</form>
