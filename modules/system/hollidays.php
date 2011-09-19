<?php /* SYSTEM $Id: hollidays.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */

// only user_type of Administrator (1) can access this page
if (!$canEdit && $AppUI->user_type != 1) {
	$AppUI->redirect( "m=public&a=access_denied" );
}
$d = new CDate();
$holliday_year = dpGetParam( $_REQUEST, "holliday_year", $d->getYear() );
//$holliday_company = dpGetParam( $_GET, "holliday_company", 0 );

$AppUI->savePlace( "m=system&a=hollidays&holliday_year=$holliday_year" );
$titleBlock = new CTitleBlock( 'Hollidays', 'language_support.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system", "system admin" );
//Por aca el combo del año
$years = array();
for ( $i = 2004; $i <= $d->getYear() + 1; $i++ )
{
	$years[$i] = $i;
}
$titleBlock->addCell( '', '', '<form name="year_selection" action="?m=system&a=hollidays" method="post">', '', arraySelect( $years, 'holliday_year', 'size="1" class="text" onchange="document.year_selection.submit();"', $holliday_year ), '',	'', '</form>' );
$titleBlock->addCell( $AppUI->_( 'Year' ).': '.arraySelect( $years, 'holliday_year', 'size="1" class="text" onchange="document.year_selection.submit();"', $holliday_year,'','','60px' ), '',	'', '</form>' );
$titleBlock->show();

$months = array(
	1=>"January",
	2=>"February",
	3=>"March",
	4=>"April",
	5=>"May",
	6=>"June",
	7=>"July",
	8=>"August",
	9=>"September",
	10=>"October",
	11=>"November",
	12=>"December"	
);
?>
<script language="javascript">
function delIt( it )
{
	var f = document.delFrm;
	
	f.holliday_id.value = it;
	f.submit();
}

function submitIt()
{
	var f = document.editFrm;
	var comboDia = f.holliday_day;
	var comboMes = f.holliday_month;
	
	var doSubmit = true;
	
	if ( trim(f.holliday_name.value).length == 0 )
	{
		doSubmit = false;
		alert( "<?=$AppUI->_("Please enter a name for the holliday")?>" );
        f.holliday_name.value="";
        f.holliday_name.focus();
	}
	else
	{
		var daysOfMonth = new Array( 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		if ( comboDia.value > daysOfMonth[comboMes.value-1] )
		{
			alert( "<?=$AppUI->_("That isn\'t a valid date")?>" );
			doSubmit = false;
		}else{
            //Validacion para el 29 de febrero para años biciestos
            if(comboDia.value==29 && (comboMes.value==2 && f.holliday_year.value % 4 > 0)){
                alert( "<?=$AppUI->_("That isn\'t a valid date")?>" );
                doSubmit = false;
            }

        }
	}
	
	if ( doSubmit )
	{
		f.submit();	
	}	
}
</script>



<table width="100%" border="0" cellpadding="1" cellspacing="0" class="">
<form name="delFrm" action="?m=system" method="post">
    <input type="hidden" name="dosql" value="do_holliday_aed" />
    <input type="hidden" name="del" value="1" />
    <input type="hidden" name="holliday_id" value="0" />
</form>
<tr class="tableHeaderGral">
	<th width="8%" nowrap>&nbsp;</th>
	<th width="45%" nowrap><?php echo $AppUI->_( 'Name' );?></th>
	<th width="20%" nowrap><?php echo $AppUI->_( 'Day' );?></th>
	<th width="20%" nowrap><?php echo $AppUI->_( 'Month' );?></th>
	<th width="10%" nowrap>&nbsp;</th>
</tr>
<?
$sql = "SELECT holliday_id 
		FROM hollidays
		WHERE holliday_year=$holliday_year AND holliday_company IS NULL";
		
/*if ( $holliday_company )
{
	$sql .= " AND holliday_company = $holliday_company";
}*/

$hollis = db_loadList( $sql );
$h = new CHolliday();
foreach( $hollis as $id )
{
	$h->load( $id["holliday_id"] );
	?>
	<tr>
		<td><a href="#" onclick="delIt(<?=$h->holliday_id?>)"><img src="./images/icons/trash_small.gif" width="16" height="16" alt="<?=$AppUI->_('delete')?>" border="0"></a> <a href="?m=system&a=holliday_edit&holliday_id=<?=$h->holliday_id?>"><img src="./images/icons/edit_small.gif" alt="edit" border="0" width="20" height="20"></a></td>
		<td><?=$h->holliday_name?></td>
		<td><?=$h->holliday_day?></td>
		<td><?=$AppUI->_( $months[$h->holliday_month] )?></td>
		<td>&nbsp;</td>
	</tr>
    <tr class="tableRowLineCell">
        <td colspan="5"></td>
    </tr>
	<?
}
?>
<form action="?m=system" method="post" name="editFrm">
	<input type="hidden" name="dosql" value="do_holliday_aed" />
	<input type="hidden" name="holliday_year" value="<?=$holliday_year?>" />
<tr class="">
	<td>&nbsp;</td>
	<td><input type="text" maxlength="255" size="40" class="text" name="holliday_name"/></td>
	<td>
	<?
	$days = array();
	for ( $i = 1; $i < 32; $i++ )
	{
		$days[$i] = $i;
	}
	echo arraySelect( $days, "holliday_day", 'class="text"', 1,'','','50px');
	?>		
	</td>
	<td><?=arraySelect( $months, "holliday_month", 'class="text"', 1, true,'','100px' )?></td>
	<td><input type="button" value="<?=$AppUI->_("add")?>" onclick="submitIt()" class="button"></td>
</tr>	
</form>
</table>
<a href="?m=system&a=copy_hollidays&destination_year=<?=$holliday_year?>"><?=$AppUI->_("Copy from a previous year")?></a>