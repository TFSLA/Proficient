<?php /* SYSTEM $Id: vw_hollidays.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
$cpy = new CCompany();
$company_id = intval( dPgetParam( $_GET, "company_id", 0 ) );
//echo "<p>company id = $company_id</p>";
if ( !$cpy->load( $company_id ) || !$company_id )
{
	$AppUI->setMsg( "Company: InvalidId", UI_ERROR_MSG );
	$AppUI->redirect();
}
$d = new CDate();
$holliday_year = dpGetParam( $_REQUEST, "holliday_year", $d->getYear() );
$AppUI->savePlace( "m=companies&a=view&company_id=$company_id&holliday_year=$holliday_year" );
$years = array();
for ( $i = 2004; $i <= $d->getYear() + 1; $i++ )
{
	$years[$i] = $i;
}

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

@require_once ( $AppUI->getModuleClass( "system" ) );
$action = dPgetParam( $_GET, "action", null );	

$feriados = $cpy->getHollidays( 0, $holliday_year );

$hjs = new CHolliday();
$strJS_holl = "var Myhollidays = new Array();\n";   

	foreach( $feriados as $id )
	{
	   $hjs->load( $id );

	   $name = str_replace("'","`",$hjs->holliday_name); 

       $strJS_holl .= "Myhollidays[Myhollidays.length] = new Array('".$hjs->holliday_day."','".$hjs->holliday_month."','".$hjs->holliday_year."','".$hjs->holliday_id."');\n";
	}

switch ( $action )
{
	case "edit":	
	$holliday_id = dpGetParam( $_GET, "holliday_id", 0 );
	$obj = new CHolliday();
	if ( !$obj->load( $holliday_id) && $holliday_id )
	{
		$AppUI->setMsg( "Holliday InvalidId", UI_MSG_ERROR );
		$AppUI->redirect();	
	}
	?>
	<script language="javascript">
	function submitIt()
	{
		var f = document.editFrm;
		var comboDia = f.holliday_day;
		var comboMes = f.holliday_month;
		
		var doSubmit = true;
				
		<?=$strJS_holl;?>

		
		if ( f.holliday_name.value == "" )
		{
			doSubmit = false;
			alert( "<?=$AppUI->_("Please enter a name for the holliday")?>" );
		}
		else
		{
			var daysOfMonth = new Array( 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
			
			if ( comboDia.value > daysOfMonth[comboMes.value-1] )
			{
				alert( "<?=$AppUI->_("That isn\'t a valid date")?>" );
				doSubmit = false;
			}

			for(var h = 0; h < Myhollidays.length; h++){
			   if((f.holliday_day.value==Myhollidays[h][0])&&(f.holliday_month.value==Myhollidays[h][1])&&(f.holliday_year.value==Myhollidays[h][2])&&(f.holliday_id.value!=Myhollidays[h][3]) ){
					 alert("<?php echo $AppUI->_('Holiday already exist');?>");
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
	<p><?=$AppUI->_("Edit holliday")?></p>
	<table width="100%" border="0" cellpadding="1" cellspacing="0" class="">
	<tr class="tableHeaderGral">
		<th width="8%" nowrap>&nbsp;</th>
		<th width="45%" nowrap><?php echo $AppUI->_( 'Name' );?></th>
		<th width="20%" nowrap><?php echo $AppUI->_( 'Day' );?></th>
		<th width="20%" nowrap><?php echo $AppUI->_( 'Month' );?></th>
		<th width="10%" nowrap>&nbsp;</th>
	</tr>
	<form action="" method="post" name="editFrm">
		<input type="hidden" name="dosql" value="do_holliday_aed" />
		<input type="hidden" name="holliday_id" value="<?=$holliday_id?>" />
		<input type="hidden" name="holliday_year" value="<?=$obj->holliday_year?>" />
		<input type="hidden" name="holliday_company" value="<?=$company_id?>" />
	<tr>
		<td></td>
		<td>
			<input type="text" maxlength="255" size="40" class="text" name="holliday_name" value="<?=$obj->holliday_name?>"/>
		</td>
		<td>
		<?
		$days = array();
		for ( $i = 1; $i < 32; $i++ )
		{
			$days[$i] = $i;
		}
		echo arraySelect( $days, "holliday_day", 'class="text"', $obj->holliday_day );
		?>		
		</td>	
		<td>
			<?=arraySelect( $months, "holliday_month", 'class="text"', $obj->holliday_month, true )?>
		</td>
		<td>
			<input type="button" value="<?=$AppUI->_("save")?>" onclick="submitIt()" class="button">
		</td>
	</tr>	
	</form>
	</table>
	<input type="button" value="<?=$AppUI->_("back")?>" onclick="history.go(-1)" class="button">
	<?	
		break;
	case "copy":
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
		$destination_year = dpGetParam( $_GET, "destination_year", $d->getYear() );
		$sql = "SELECT DISTINCTROW holliday_year 
				FROM hollidays 
				WHERE holliday_company = $company_id";
			
		$or_years = db_loadHashList( $sql );
		$dest_years = $or_years;
		$y = $d->getYear() + 1;
		if ( !array_key_exists($y, $dest_years ) )
		{
			$dest_years[$y] = $y;
		}		
		?>
		<p><?=$AppUI->_("Copy hollidays")?></p>
		<form name="cpyFrm" action="" method="post">
			<input type="hidden" name="dosql" value="do_holliday_copy" />	
			<input type="hidden" name="holliday_company" value="<?php echo $company_id;?>" />
		<table width="100%" border="0" cellpadding="1" cellspacing="1" class="std">
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
	<?
		break;
	default:	
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

		// Me fijo que no este repetida antes de mandarlo
		<?=$strJS_holl;?>
		
		if ( f.holliday_name.value == "" )
		{
			doSubmit = false;
			alert( "<?=$AppUI->_("Please enter a name for the holliday")?>" );
		}
		else
		{
			var daysOfMonth = new Array( 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
			
			if ( comboDia.value > daysOfMonth[comboMes.value-1] )
			{
				alert( "<?=$AppUI->_("That isn\'t a valid date")?>" );
				doSubmit = false;
			}

			for(var h = 0; h < Myhollidays.length; h++){
			   if((f.holliday_day.value==Myhollidays[h][0])&&(f.holliday_month.value==Myhollidays[h][1])&&(f.holliday_year.value==Myhollidays[h][2])){
					 alert("<?php echo $AppUI->_('Holiday already exist');?>");
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
	<?php
	$disabled = $cpy->company_own_hollidays ? " disabled" : "";

	?>
	<form name="useOwnHollidays" action="?m=companies" method=POST>
		<input type="hidden" name="company_id" value="<?=$company_id?>" />
		<input type="hidden" name="dosql" value="do_toggle_hollidays" />
		<p><?=$AppUI->_("Use system hollidays")?>
		<input type="checkbox" name="company_own_hollidays"<?=( $cpy->company_own_hollidays ? " checked" : "" )?> onclick="document.useOwnHollidays.submit()" value="1">
		</p>
	</form>
	
	<form name="yearSelection" action="?m=companies&a=view&company_id=<?=$company_id?>" method="post">
		<p><?=$AppUI->_("Year")?>: <?=arraySelect( $years, "holliday_year", 'class="text" onchange="yearSelection.submit()" '.$disabled, $holliday_year )?></p>
	</form>
	<form name="delFrm" action="" method="post">
		<input type="hidden" name="dosql" value="do_holliday_aed" />
		<input type="hidden" name="del" value="1" />
		<input type="hidden" name="holliday_id" value="0" />
	</form>

	<table width="100%" border="0" cellpadding="1" cellspacing="1" class="">
	<tr class="tableHeaderGral">
		<th width="8%" nowrap>&nbsp;</th>
		<th width="45%" nowrap><?php echo $AppUI->_( 'Name' );?></th>
		<th width="20%" nowrap><?php echo $AppUI->_( 'Day' );?></th>
		<th width="20%" nowrap><?php echo $AppUI->_( 'Month' );?></th>
		<th width="10%" nowrap>&nbsp;</th>
	</tr>
	<?
	$debugsql = 1;
	$hollis = $cpy->getHollidays( 0, $holliday_year );
	
	$h = new CHolliday();
	foreach( $hollis as $id )
	{
		$h->load( $id );
		?>
		<tr>
			<td><? if ( !$disabled) { ?><a href="#" onclick="delIt(<?=$h->holliday_id?>)"><img src="./images/icons/trash_small.gif" alt="<?=$AppUI->_('delete')?>" border="0"></a> <a href="?<?=$_SERVER["QUERY_STRING"]?>&action=edit&holliday_id=<?=$h->holliday_id?>"><img src="./images/icons/edit_small.gif" alt="edit" border="0" width="20" height="20"></a><? } else { ?>&nbsp;<? } ?></td>
			<td><?=$h->holliday_name?></td>
			<td><?=$h->holliday_day?></td>
			<td><?=$AppUI->_( $months[$h->holliday_month] )?></td>
			<td>&nbsp;</td>
		</tr>
		<?
        echo "<tr class=\"tableRowLineCell\"><td colspan=\"5\"></td></tr>";
	}
	if ( !$disabled )
	{
	?>
	<form action="" method="post" name="editFrm">
		<input type="hidden" name="dosql" value="do_holliday_aed" />
		<input type="hidden" name="holliday_year" value="<?=$holliday_year?>" />
		<input type="hidden" name="holliday_company" value="<?=$company_id?>" />
	<tr>
		<td>&nbsp;</td>
		<td><input type="text" maxlength="255" size="40" class="text" name="holliday_name"/></td>
		<td>
		<?
		$days = array();
		for ( $i = 1; $i < 32; $i++ )
		{
			$days[$i] = $i;
		}
		echo arraySelect( $days, "holliday_day", 'class="text"', 1);
		?>		
		</td>
		<td><?=arraySelect( $months, "holliday_month", 'class="text"', 1, true )?></td>
		<td><input type="button" value="<?=$AppUI->_("add")?>" onclick="submitIt()" class="button"></td>
	</tr>	
	</form>
	</table>
	<a href="?<?=$_SERVER["QUERY_STRING"]?>&destination_year=<?=$holliday_year?>&action=copy"><?=$AppUI->_("Copy from a previous year")?></a>
	<?
	}
}
