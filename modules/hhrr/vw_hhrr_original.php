<?php /* COMPANIES $Id: vw_hhrr_original.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
##
##	Companies: View Projects sub-table
##
GLOBAL $AppUI, $canEdit;


require_once("functions/admin_func.php");

$AppUI->state[RrhhviewIdxTab] = "0";
$AppUI->state[RrhhaddIdxTab] = "0";

$AppUI->savePlace();

$_GET['utype'] =  isset($_GET['utype']) ?  $_GET['utype'] : "utype";
$_GET['company_id'] =  isset($_GET['company_id']) ?  $_GET['company_id'] : "";

$company_id = $_GET['company_id'];

if ($candidate == "1")
{
$_GET['utype'] = "5";
}


$userTypes = $utypes;

unset($userTypes[0]);
if($AppUI->user_type != 1){	
	unset($userTypes[1]);
} 

if ($candidate != "1")
{
unset($userTypes[5]);
}

$userTypes = arrayMerge(array("-1" => "All"), $userTypes);
if (!isset($userTypes[$_GET['utype']]))
	$_GET['utype']="-1"; 

if (isset( $_GET['utype'] )) {
    $AppUI->setState( 'UserIdxUtype', $_GET['utype'] );
    $AppUI->setState( 'UserIdxWhere', '' );
}

$utype = $AppUI->getState( 'UserIdxUtype' );


function showrowhhrr( &$a ) {
	global $AppUI;
	
	
	$uploads_dir = $AppUI->getConfig('hhrr_uploads_dir');

	$age = calcular_edad($a["user_birthday"]);
	$age = $age ? $age : "--";
    $hayskill=0;
    $resultsk = db_loadResult("SELECT count(*) FROM hhrrskills WHERE user_id = {$a["user_id"]} AND value > 0");

    if($resultsk>0) $hayskill=1;

	$s = '';
	$s .= '<td nowrap="nowrap">';
	$canEditHHRR = CHhrr::canEdit($a["user_id"]);
	if ($canEditHHRR){
	$s .= '<a href="./index.php?m=hhrr&a=addedit&id='.$a["user_id"].'">';
	$s .= '<img src="./images/icons/edit_small.gif" alt="'.$AppUI->_('Edit HHRR').'" border="0" width="20" height="20"></a>';
	$s .= ' <a href="javascript:delHhrr('. $a["user_id"] .', \''. $a["user_last_name"] . ", " . $a["user_first_name"] . '\')"><img src="images/icons/trash_small.gif" border="0" alt="'.$AppUI->_('delete').'"></a>';
	//$s .= ' <a href="javascript:delHhrr('. $a["user_id"] .', \''. $a["user_last_name"] . ", " . $a["user_first_name"] . '\')"><img src="images/icons/trash.gif" width="20" height="20" border="0" alt="'.$AppUI->_('delete').'"></a>';
	}else{
		$s .= '&nbsp;';
	}
	$s .= '</td>';
	$s .= '<td nowrap="nowrap">';
	$s .= '<a href="./index.php?m=hhrr&a=viewhhrr&id='.$a["user_id"].'">'.$a["user_last_name"].", ".$a["user_first_name"].'</a>';
	$s .= '</td>';
	$s .= '<td>';
	$s .= $a["user_home_phone"]." &nbsp;&nbsp;&nbsp; ".$a["user_mobile"] ;
	$s .= '</td>';
	$s .= '<td align=center>&nbsp;';
        //if($hayskill==1)
        // descmentado popr Rodrigo Fuentes 20050929
    if($hayskill==1)
	  $s .= "$resultsk <a href='./index.php?m=hhrr&a=viewhhrr&tab=2&id=".$a["user_id"]."'>[".$AppUI->_( 'View' )."]</a>" ;
	$s .= '</td>';
	$s .= '<td class="celdatextocentrado'.$cellid.'">&nbsp;'.$age.'</td>';
	$s .= '<td class="celdatextocentrado'.$cellid.'">&nbsp;';
	if($a["resume"]!="ninguna" && trim($a["resume"])!=""){
		
	    $s .= '<a href="'.$uploads_dir.'/'.$a["user_id"].'/'.str_replace(" ","%20",$a["resume"]).'"><img src="images/texticon.gif" border="0"></a>';
	}
	$s .= '</td>';
	$s .= '<td class="celdatextocentrado'.$cellid.'">&nbsp;'.$a["costperhour"].'</td>';

	echo "<tr>$s</tr>";
    echo "<tr class=\"tableRowLineCell\"><td colspan=\"7\"></td></tr>";
}


$letter = dPgetParam( $_GET, "l", "-1" );
$nombre = dPgetParam( $_GET, "where", "" );
if ($letter!="-1"){
	$letter = substr($letter, 0, 1);
	$where = " left( user_last_name, 1 ) in ('".strtolower($letter)."', '$letter') ";
}else{
	$where = " 1 ";
}

$letterlist="A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,Z";
$letters = explode(",",$letterlist);

if($utype!="-1" && $candidate != "1"){
$f_type = "and user_type = '$utype'";
}

if($utype =="-1" && $candidate != "1"){
$f_type = "and user_type in (1,2,3,4)";
}

if($company_id !="-1" && $company_id != ""){
$f_type .= "\n and user_company = '$company_id' ";
}

if ($candidate == "1")
{
$f_type = "and user_type = '5'";

}

$sql = "
SELECT users.*
FROM users
where $where 
and (user_username like '%$nombre%'
or user_first_name like '%$nombre%'
or user_last_name like '%$nombre%')
$f_type
ORDER BY user_last_name, user_first_name
";

//echo $sql;

$s = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
$s .= '<tr>';
$s .= '<td width="350" class="" background="images/common/back_botones-01.gif">';
$s .= '<table  border="0" cellpadding="0" cellspacing="0"  width="350"><tr>';
$s .= '<td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>';
$s .= '<td class="right" nowrap="nowrap"><img src="images/common/cuadradito_naranja.gif" width="9" height="9" align="left">';

if ($candidate != "1")
{
	$s .= "<a href=\"index.php?m=hhrr&tab=0&l=-1&utype=$utype\">".$AppUI->_('All')."</a>&nbsp;";
	foreach($letters as $letra){
	$s .= "<a href=\"index.php?m=hhrr&tab=0&l=$letra&utype=$utype\">$letra</a>&nbsp;";
	}
}else{
   $s .= "<a href=\"index.php?m=hhrr&tab=1&l=-1&utype=$utype\">".$AppUI->_('All')."</a>&nbsp;";
   foreach($letters as $letra){
   $s .= "<a href=\"index.php?m=hhrr&tab=1&l=$letra&utype=$utype\">$letra</a>&nbsp;";
   }
}

$s .= '</td>';
$s .= '</tr></table>';
$s .= '</td>';
$s .= '<td width="6" class="" background="images/common/back_botones-01.gif"> <div align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></div></td>';

$s .="<form action=\"index.php\" name=\"filterFrm\" method=\"get\">";
$s .='<input type="hidden" name="m" value="hhrr"><input type="hidden" name="l" value="'.$letter.'">';


// Preparo el array de empresas
$obj = new CCompany();
$companies = $obj->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$companies = arrayMerge( array( '-1'=>$AppUI->_('All') ), $companies );

if ($candidate != "1")
{
$s .= '<td  align="right">'.$AppUI->_('Company').': ';
$s .=  arraySelect( $companies, 'company_id', 'onChange="javascript: this.form.submit()" class="text"', $company_id,true, false );
$s .= '</td>';
$s .= '<td   align="right">'.$AppUI->_('User Type').': '.arraySelect( $userTypes, 'utype', 'class="text" size="1" onchange="javascript: this.form.submit()"', $utype, true,false,'120px');
}else{
$s .= '<td width="100%" align="right">&nbsp;';

}

$s .= '</td>';
$s .= '</tr>';
$s .= '</form>';

$s .= '</table>';
$s .= '<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">';
$s .= '<tr class="tableHeaderGral">';
$s .= '<th width="43">&nbsp;</th>';
$s .= '<th >'.$AppUI->_( 'Name' ).'</th>';
$s .= '<th width="50%">'.$AppUI->_( 'Phone' ).'</th>';
$s .= '<th>'.$AppUI->_( 'Skills' ).'</th>';
$s .= '<th>'.$AppUI->_( 'Age' ).'</th>';
$s .= '<th>'.$AppUI->_( 'C.V.' ).'</th>';
$s .= '<th>'.$AppUI->_( '$/h' ).'</th>';
//$s .= '</tr>';

//$rows = db_loadList( $sql, NULL );
$dp = new DataPager($sql, "hhrr");
$dp->showPageLinks = true;
$rows = $dp->getResults();
if (!count( $rows)) {
    $s .= '</tr><tr>';
	$s .= "<td colspan=\"97\">".$AppUI->_('No data available')."</td>";
}
$rn = $dp->num_result;
$pager_links = $dp->RenderNav();
$s .= '<!--td nowrap="nowrap" rowspan="99" align="right" valign="top" style="background-color:#ffffff">';
if ($canEdit) {
	$s .= '<input type="button" class=button value="'.$AppUI->_( 'new hhrr' ).'" onClick="javascript:window.location=\'./index.php?m=hhrr&a=addedit\';">';
}
$s .= '</td-->';
$s .= '</tr>';
echo $s;

foreach ($rows as $row) {
	showrowhhrr( $row );
}
echo '</table>';

echo "
<table border='0' width='100%' cellspacing='0' cellpadding='1'>
<tr bgcolor=#E9E9E9>
	<td align='center'>$pager_links</td>
</tr>
<tr>
		<td height=1 colspan=5 bgcolor=#E9E9E9></td>
</tr>
</table>"; 

?>
