<?php /* COMPANIES $Id: vw_companies.php,v 1.27 2009-07-31 14:53:53 nnimis Exp $ */

global $companiesType;
global $search_string;
global $allowedProjects;

// retrieve any state parameters
if (isset( $_GET['orderby'] )) {
	$AppUI->setState( 'CompIdxOrderBy', $_GET['orderby'] );
}
$orderby = $AppUI->getState( 'CompIdxOrderBy' ) ? $AppUI->getState( 'CompIdxOrderBy' ) : 'company_name';

if(isset( $_GET["revert"] ))
	$orderby .= " DESC";


// load the company types

$types = dPgetSysVal( 'CompanyType' );
// get any records denied from viewing

$obj = new CCompany();
$deny = $obj->getDeniedRecords( $AppUI->user_id );

if ( $companiesType == -1 )
{
	//Plain view
	foreach ($types as $company_key => $company_type)
	{
		$company_type = trim($company_type);
		$flip_company_types[$company_type] = $company_key;
	}
	$company_type_filter = $flip_company_types[trim($v[1])];		
} 
else
{
	//Tabbed view
	$company_type_filter = $companiesType;
}

// retrieve list of records
$sql = "SELECT company_id, company_name, company_type, company_description, company_primary_url,"
	. "company_phone1, count(distinct projects.project_id) as planning, count(distinct projects2.project_id) as inactive,"
	. "user_first_name, user_last_name, company_supplier_status, count(distinct projects3.project_id) as completed,"
	. "count(distinct projects4.project_id) as inprogress, c.* "
	. " FROM permissions, companies"

	. " LEFT JOIN users ON companies.company_owner = users.user_id"
	. " LEFT JOIN projects ON companies.company_id = projects.project_company AND projects.project_status = 2"
	. " AND projects.project_id $allowedProjects"
	. " LEFT JOIN projects AS projects2 ON companies.company_id = projects2.project_company AND projects2.project_active = 0"
	. " AND projects2.project_id $allowedProjects"
	. " LEFT JOIN projects AS projects3 ON companies.company_id = projects3.project_company AND projects3.project_status = 5"
	. " AND projects3.project_id $allowedProjects"
	. " LEFT JOIN projects AS projects4 ON companies.company_id = projects4.project_company AND projects4.project_status = 3"
	. " AND projects4.project_id $allowedProjects"
	. " LEFT JOIN contacts AS c ON companies.contact_id = c.contact_id"
	. " WHERE permission_user = $AppUI->user_id"
	. "	AND permission_value <> 0"
	. " AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'companies' and permission_item = -1)
		OR (permission_grant_on = 'companies' and permission_item = company_id)
		)"
	. (count($deny) > 0 ? ' AND company_id NOT IN (' . implode( ',', $deny ) . ')' : '')
	. ($companiesType < count($types) ? " AND company_type = $company_type_filter" : "");

if($search_string != ""){
	$sql .= " AND company_name LIKE '%$search_string%' ";
}

$sql .= " GROUP BY company_id
		 ORDER BY $orderby";

//die ("<p>Sql = <pre>$sql</pre></p>");
//$rows = db_loadList( $sql );
$dp = new DataPager($sql, "cpy");
$dp->showPageLinks = true;
$rows = $dp->getResults();
$rn = $dp->num_result;
$pager_links = $dp->RenderNav();

if(isset($_GET["orderby"]) && !isset($_GET["revert"])) $revert = "&revert=1";
else $revert = "";
?>

<style type="text/css">
.rejected {color: red;}
.rejected a:link {color: red;}
.rejected a:visited {color: red;}
</style>
<SCRIPT LANGUAGE="JavaScript">
//<!-- Begin
function popUp(URL) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0 ,scrollbars=0, location=0, statusbar=0, menubar=0, resizable=0, width=640, height=250, left=490, top=302');");
}

function showMenu(menu){
	document.getElementById(1).style.display='none';
	document.getElementById(2).style.display='none';
	document.getElementById(3).style.display='none';
	document.getElementById(menu).style.display='';
}

function hideMenu(){
	document.getElementById(1).style.display='none';
	document.getElementById(2).style.display='none';
	document.getElementById(3).style.display='none';
}
// End -->
</script>

<?//::::::MENU DESPLEGABLE::::::?>
<?php 
//Parámetros de configuración del menú
$menuWidth = "145px";
$leftMargin = "5px";
$dropDownImage = "./images/icons/down.gif";
$downImage = "<img src='./images/arrow-down.gif' border='0' alt='".$AppUI->_("Ascending")."'>";
$upImage = "<img src='./images/arrow-up.gif' border='0' alt='".$AppUI->_("Descending")."'>";
$selectedItemFont = "color = #EE8855";
$menuStyle = " display: none; position: absolute; -ms-filter: 'progid:DXImageTransform.Microsoft.Alpha(Opacity=70)'; filter: alpha(opacity=70); opacity: .7; width: 145px;";
?>

<?//Empresa?>
<div id="1"  style="left:160px; top:215px; <?=$menuStyle?>">
<table border="0" cellpadding="2" cellspacing="0" class="">
	<tr class="tableHeaderGral">
		<td nowrap="nowrap" width="<?=$leftMargin?>" align="right">
		<th nowrap="nowrap" class="tableHeaderText" width="<?=$menuWidth?>" align="left">
			<a href="?m=companies&orderby=company_name" class=""><?=$downImage?></a>
			<a href="?m=companies&orderby=company_name&revert=1" class=""><?=$upImage?></a>
			<?php
				$itemName = $AppUI->_('Name');
				$text = $_GET["orderby"] == "company_name" ? "<font $selectedItemFont>$itemName</font>" : $itemName;
				echo $text;
			?>
			<br>
			
			<a href="?m=companies&orderby=company_type" class=""><?=$downImage?></a>
			<a href="?m=companies&orderby=company_type&revert=1" class=""><?=$upImage?></a>
			<?php
				$itemName = $AppUI->_('Type');
				$text = $_GET["orderby"] == "company_type" ? "<font $selectedItemFont>$itemName</font>" : $itemName;
				echo $text;
			?>
			<br>
			
			<a href="?m=companies&orderby=company_phone1" class=""><?=$downImage?></a>
			<a href="?m=companies&orderby=company_phone1&revert=1" class=""><?=$upImage?></a>
			<?php
				$itemName = $AppUI->_('Phone');
				$text = $_GET["orderby"] == "company_phone1" ? "<font $selectedItemFont>$itemName</font>" : $itemName;
				echo $text;
			?>
			<br>
			
			<a href="?m=companies&orderby=company_primary_url" class=""><?=$downImage?></a>
			<a href="?m=companies&orderby=company_primary_url&revert=1" class=""><?=$upImage?></a>
			<?php
				$itemName = $AppUI->_('Web');
				$text = $_GET["orderby"] == "ccompany_primary_url" ? "<font $selectedItemFont>$itemName</font>" : $itemName; 
				echo $text;
			?>
			<br>
		</th>
	</tr>
</table>
</div>

<?//Proyectos?>
<div id="2" style="left:420px; top:215px; <?=$menuStyle?>">
<table border="0" cellpadding="2" cellspacing="0" class="">
	<tr class="tableHeaderGral">
		<td nowrap="nowrap" width="<?=$leftMargin?>" align="right">
		<th nowrap="nowrap" class="tableHeaderText" width="<?=$menuWidth?>" align="left">
			<a href="?m=companies&orderby=inprogress" class=""><?=$downImage?></a>
			<a href="?m=companies&orderby=inprogress&revert=1" class=""><?=$upImage?></a>
			<?php 
				$itemName = $AppUI->_('In Progress');
				$text = $_GET["orderby"] == "inprogress" ? "<font $selectedItemFont>$itemName</font>" : $itemName; 
				echo $text;
			?>
			<br>
			
			<a href="?m=companies&orderby=planning" class=""><?=$downImage?></a>
			<a href="?m=companies&orderby=planning&revert=1" class=""><?=$upImage?></a>
			<?php 
				$itemName = $AppUI->_('In Planning');
				$text = $_GET["orderby"] == "inplanning" ? "<font $selectedItemFont>$itemName</font>" : $itemName; 
				echo $text;
			?>
			<br>
			
			<a href="?m=companies&orderby=completed" class=""><?=$downImage?></a>
			<a href="?m=companies&orderby=completed&revert=1" class=""><?=$upImage?></a>
			<?php
				$itemName = $AppUI->_('Completed');
				$text = $_GET["orderby"] == "completed" ? "<font $selectedItemFont>$itemName</font>" : $itemName; 
				echo $text;
			?>
			<br>
			
			<a href="?m=companies&orderby=inactive" class=""><?=$downImage?></a>
			<a href="?m=companies&orderby=inactive&revert=1" class=""><?=$upImage?></a>
			<?php echo $AppUI->_('Inactive');?><br>
		</th>
	</tr>
</table>
</div>

<?//Contacto?>
<div id="3" style="left:620px; top:215px; <?=$menuStyle?>">
<table border="0" cellpadding="2" cellspacing="0" class="">
	<tr class="tableHeaderGral">
		<td nowrap="nowrap" width="<?=$leftMargin?>" align="right">
		<th nowrap="nowrap" class="tableHeaderText" width="<?=$menuWidth?>" align="left">
			<a href="?m=companies&orderby=contact_last_name" class=""><?=$downImage?></a>
			<a href="?m=companies&orderby=contact_last_name&revert=1" class=""><?=$upImage?></a>
			<?php
				$itemName = $AppUI->_('Last Name');
				$text = $_GET["orderby"] == "contact_last_name" ? "<font $selectedItemFont>$itemName</font>" : $itemName;
				echo $text;
			?>
			<br>
			
			<a href="?m=companies&orderby=contact_first_name" class=""><?=$downImage?></a>
			<a href="?m=companies&orderby=contact_first_name&revert=1" class=""><?=$upImage?></a>
			<?php
				$itemName = $AppUI->_('First Name');
				$text = $_GET["orderby"] == "contact_first_name" ? "<font $selectedItemFont>$itemName</font>" : $itemName;
				echo $text;
			?>
			<br>
			
			<a href="?m=companies&orderby=contact_mobile" class=""><?=$downImage?></a>
			<a href="?m=companies&orderby=contact_mobile&revert=1" class=""><?=$upImage?></a>
			<?php
				$itemName = $AppUI->_('Mobile Phone');
				$text = $_GET["orderby"] == "contact_mobile" ? "<font $selectedItemFont>$itemName</font>" : $itemName;
				echo $text;
			?>
			<br>
			
			<a href="?m=companies&orderby=contact_email" class=""><?=$downImage?></a>
			<a href="?m=companies&orderby=contact_email&revert=1" class=""><?=$upImage?></a>
			<?php
				$itemName = $AppUI->_('Email');
				$text = $_GET["orderby"] == "contact_email" ? "<font $selectedItemFont>$itemName</font>" : $itemName;
				echo $text;
			?>
			<br>
		</th>
	</tr>
</table>
</div>

<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">
<tr class="tableHeaderGral">
	<td nowrap="nowrap" width="20px" align="right">&nbsp;<?php //echo $AppUI->_('sort by');?>&nbsp;</td>
	<th nowrap="nowrap" class="tableHeaderText" width="230px" align="left">
		<a href="?m=companies&orderby=company_name<?=$revert?>" class=""><?php echo $AppUI->_('Company');?></a>
		<a href="#" onmouseover="showMenu(1);"><img alt="<?=$AppUI->_("Sort")?>" src="<?=$dropDownImage?>" border="0"></a>
	</th>
	<th nowrap="nowrap" width="200px" class="tableHeaderText" align="left">
		<a href="?m=companies&orderby=inprogress<?=$revert?>" class=""><?php echo $AppUI->_('Projects');?></a>
		<a href="#" onmouseover="showMenu(2);"><img alt="<?=$AppUI->_("Sort")?>" src="<?=$dropDownImage?>" border="0"></a>
	</th>
	<th nowrap="nowrap" class="tableHeaderText" width="230px" align="left">
		<a href="?m=companies&orderby=contact_last_name<?=$revert?>" class=""><?php echo $AppUI->_('Contact');?></a>
		<a href="#" onmouseover="showMenu(3);"><img alt="<?=$AppUI->_("Sort")?>" src="<?=$dropDownImage?>" border="0"></a>
	</th>
	<th nowrap="nowrap" class="tableHeaderText" align="right">
	</th>
</tr>
<tr>
    <td colspan="6" height="1px" bgcolor="White"></td>
</tr>
<?php
$s = '';
$CR = "\n"; // Why is this needed as a variable?

$none = true;
foreach ($rows as $row)
{
	$sql="SELECT permission_value FROM permissions WHERE permission_user=$AppUI->user_id AND ((permission_grant_on='companies' AND permission_item='$row[company_id]') OR permission_grant_on='all')";
	$rc=db_exec($sql);
	$perms=db_fetch_array($rc);
	
	$sql="SELECT fsize, fname, ftype, fheight, fwidth FROM companies WHERE company_id='$row[company_id]'";
	$rc=db_exec($sql);
	$vec2=db_fetch_array($rc);
	
	$sql = "SELECT * FROM contacts WHERE contact_id= '$obj->contact_id' ";
	if ($vec2['fsize']!=0)
	{
		$alto=$vec2['fheight']/125;
		$ancho=$vec2['fwidth']/125;
		if ($alto>$ancho) {
			$height=$vec2['fheight']/$alto;
			$width=$vec2['fwidth']/$alto;
		}
		else{
			$height=$vec2['fheight']/$ancho;
			$width=$vec2['fwidth']/$ancho;
		}
		$src = './includes/view.php?mod=1&id='.$row[company_id];
	}
	else
	{
		$src = './images/logo_gray.JPG';
		$height = '40px';
		$width = '80px';
	}
	
	$tel = $row[company_phone1];
	$web = empty($row[company_primary_url]) ? '' : '<a href="#" onclick=window.open("http://'.$row[company_primary_url].'")>'.$row[company_primary_url].'</a>';
	
	$none = false;
	$s .= $CR . '<tr height="100px" '.($row["company_supplier_status"] == 3 && $row["company_type"] == 3 ? 'class="rejected"' : '').' onmouseover="hideMenu();" >';
	$s .= $CR . '<td>&nbsp;</td>';
	$s .= $CR . '<td><a href="./index.php?m=companies&a=view&company_id=' . $row["company_id"] . '" title="'.$row['company_description'].'">' . $row["company_name"];
	$s .= $CR . '</a><br>';
	$s .= $CR . $AppUI->_("Type").': '.$AppUI->_($types[@$row["company_type"]]).'<br>';
	$s .= $CR . 'Tel.: '.$tel.'<br>';
	$s .= $CR . 'Web: '.$web;
	$s .= $CR . '</td>';
	$s .= $CR . '<td align="left" nowrap="nowrap">';
	$s .= $CR . $AppUI->_("In Progress").': '.$row["inprogress"] . '<br>';
	$s .= $CR . $AppUI->_("In Planning").': '.$row["planning"] . '<br>';
	$s .= $CR . $AppUI->_("Completed").': '.$row["completed"] . '<br>';
	$s .= $CR . $AppUI->_("Inactive").': '.$row["inactive"] . '</td>';
	$s .= $CR . '<td>';
	$s .= $CR . $AppUI->_("Last Name").': '.$row["contact_last_name"].'<br>';
	$s .= $CR . $AppUI->_("First Name").': '.$row["contact_first_name"].'<br>';
	$s .= $CR . $AppUI->_("Mobile Phone").': '.$row["contact_mobile"].'<br>';
	$s .= $CR . $AppUI->_("Email").': '.$row["contact_email"].'<br>';
	$s .= $CR . '';
	$s .= $CR . '';
	$s .= $CR . '</td>';
	$s .= $CR . '<td align="right" nowrap="nowrap" valign="middle">';
	
	$row["company_name"] = ereg_replace(" ", "%20", $row["company_name"]);
	
	if ($perms['permission_value']=='-1')
		$s .= $CR .	"<a href=\"#\" onclick=javascript:popUp(\"index_inc.php?inc=./modules/companies/velogo.php&m=companies&id=".$row["company_id"]."\")>";
	
	$s .= $CR .	'<img src="'.$src.'" height="'.$height.'" width="'.$width.'" border="0" ';
	
	if ($perms['permission_value']=='-1')
		$s .= $CR . 'alt="'.$AppUI->_("Edit/View").'"></a>';
	else 
		$s .= $CR .'>';
	
	$s .= $CR .'</td>';
	$s .= $CR . '</tr>';
    $s .= $CR . "<tr class=\"tableRowLineCell\"><td colspan=\"6\"></td></tr>";
}
echo "$s\n";
if ($none) {
	echo $CR . '<tr><td colspan="6">' . $AppUI->_( 'No companies available' ) . '</td></tr>';
}

?>
</table>
<?php
echo "
<table border='0' width='100%' cellspacing='0' cellpadding='1'>
<tr bgcolor=#E9E9E9>
	<td align='center'>$pager_links</td>
</tr>
<tr>
		<td height=1 colspan=5 bgcolor=#E9E9E9></td>
</tr>
</table>"; ?>