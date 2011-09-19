<?php

// only user_type of Administrator (1) can access this page
if (!$canEdit || $AppUI->user_type != 1) {
	$AppUI->redirect( "m=public&a=access_denied" );
}
$d = new CDate();
$destination_year = dpGetParam( $_GET, "destination_year", $d->getYear() );
$titleBlock = new CTitleBlock( 'Copy hollidays', 'language_support.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system", "system admin" );
$titleBlock->show();
?>
<script language="javascript">
function submitIt()
{
	var f = document.cpyFrm;
	var orig = f.source_year;
	var dest = f.destination_year;
	var doSubmit = true;
	
	if ( dest.value == orig.value )
	{
		alert( "<?=$AppUI->_("Please select different years")?>" );
		doSubmit = false;
	}	
	
	if ( doSubmit )
	{
		f.submit();
	}
}
</script>
<?php
$sql = "SELECT DISTINCTROW holliday_year 
		FROM hollidays WHERE holliday_company IS NULL";
		
$or_years = db_loadHashList( $sql );
$dest_years = $or_years;
$y = $d->getYear() + 1;
if ( !array_key_exists($y, $dest_years ) )
{
	$dest_years[$y] = $y;
}
//print_r( $dest_years )
?>

<table width="100%" border="0" cellpadding="1" cellspacing="0" class="std">
<form name="cpyFrm" action="?m=system" method="post">
    <input type="hidden" name="dosql" value="do_holliday_copy" />
<tr>
	<td nowrap align="right"><?php echo $AppUI->_( 'Source year' );?> <?php echo arraySelect( $or_years, "source_year", 'class="text"', '') ?></td>
	<td nowrap align="left"><?php echo $AppUI->_( 'Destination year' );?> <?php echo arraySelect( $dest_years, "destination_year", 'class="text"', $destination_year ) ?></td>		
</tr>
<tr>
	<td align="left"><input type="button" class="button" value="<?=$AppUI->_("back")?>" onclick="history.go(-1)"></td>
	<td align="right"><input type="button" class="button" value="<?=$AppUI->_("copy")?>" onclick="submitIt()"></td>	
</tr>
</form>
</table>