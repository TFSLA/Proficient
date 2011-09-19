<?php
IF ($_GET['from_year'] && $_GET['from_month'] && $_GET['from_day'])
	$from_date=$_GET['from_year'].$_GET['from_month'].$_GET['from_day'].'000000';
ELSE
	$from_date=date("Ymd", mktime(0, 0, 0, date("m")-1, date("d"), date("Y"))).'000000';

IF ($_GET['to_year'] && $_GET['to_month'] && $_GET['to_day'])
	$to_date=$_GET['to_year'].$_GET['to_month'].$_GET['to_day'].'235959';
ELSE
	$to_date=date("Y").date("m").date("d").'235959';

?>
<table width="100%" border="0" cellpadding="1" cellspacing="1" class="std">
<tr height="20">
	<th colspan="2"><?php echo $AppUI->_('Filter');?>:</th>
</tr>
<form action="" method="GET">
<TR>
	<TD width="50%">
		<input type='hidden' name='m' value='<?php echo $_GET['m']; ?>'>
		<input type='hidden' name='a' value='<?php echo $_GET['a']; ?>'>
		<input type='hidden' name='pag' value='<?php echo $_GET['pag']; ?>'>
		<input type='hidden' name='tab' value='<?php echo $_GET['tab']; ?>'>
		<?php mkFromTo($from_date, $to_date, $AppUI);?>
	</TD>
	<TD width="50%">
		<TABLE border="0">
		<?PHP
		if ($AppUI->user_type==1) {
			echo "<TR><TD align='right'>".$AppUI->_('Owner').": ";
			echo "</TD><TD align='left'>\n";
			mkowner($_GET['owner'], $AppUI);
			echo "</TD></TR>\n";
		}
		?>
		<TR>
			<TD align="right"><?php echo $AppUI->_('User').": ";?></TD>
			<TD align="left"><?php mkuser($_GET['user'], $AppUI); ?></TD>
		</TR>
		<TR>
			<TD align="right"><?php echo $AppUI->_('Module').": ";?></TD>
			<TD align="left"><?php mkmod($_GET['mod'], $AppUI); ?></TD>
		</TR>
		</TABLE>
	</TD>
</TR>
</table>
<br>
<?php
showlog ($_GET, $AppUI, $from_date, $to_date)
?>


<?php
function order($_GET, $col, $orderby){
	$link="index.php?m=delegates";
	$link .="&tab=2";
	$link .="&from_day=".$_GET['from_day'];
	$link	.="&from_month=".$_GET['from_month'];
	$link	.="&from_year=".$_GET['from_year'];
	$link	.="&to_day=".$_GET['to_day'];
	$link .="&to_month=".$_GET['to_month'];
	$link .="&to_year=".$_GET['to_year'];
	$link .="&x=".$_GET['x'];
	$link .="&y=".$_GET['y'];
	$link .="&user=".$_GET['user'];
	$link .="&mod=".$_GET['mod'];
	$link .="&pag=".$_GET['pag'];
	$link .="&orderby=".$orderby;
	if ($_GET['order']=='a'){
		$order='d';
		if ($_GET['orderby']==$orderby){
			$img="<img src='./modules/webtracking/images/down.gif' alt=''>";
		}
	}
	else {
		$order='a';
		if ($_GET['orderby']==$orderby){
			$img="<img src='./modules/webtracking/images/up.gif' alt=''>";
		}
	}
	$link .="&order=".$order;
	return ("<a href='$link'>$col</a>&nbsp;&nbsp;&nbsp;$img");
}

function showlog ($_GET, $AppUI, $from_date, $to_date){
	if ($_GET['pag']=='') $pag=1;
	else $pag=$_GET['pag'];
	$linefrom=$pag*25-25;
	$lineto=$pag*25;
	//echo "<br><br>desde $linefrom <br> hasta $lineto <br><br>";
	if ($AppUI->user_type==1 && $_GET['owner']){
		if ($where!='') $where .=" AND ";
		$where .="d.dl_delegate_id=".$_GET['owner'];
	}
	if ($AppUI->user_type!=1){
		if ($where!='') $where .=" AND ";
		$where .="d.dl_delegate_id=".$AppUI->user_id;
	}

	//$from_date .='000000';
	if ($where!='') $where .=" AND ";
	$where	.="d.dl_timestamp>=$from_date";

	//$to_date .='235959';
	if ($where!='') $where .=" AND ";
	$where	.="d.dl_timestamp<=$to_date";

	if ($_GET['user']){
		if ($where!='') $where .=" AND ";
		$where	.="u.user_id=".$_GET['user'];
	}
	if ($_GET['mod']){
		if ($where!='') $where .=" AND ";
		$where	.="d.dl_module_id=".$_GET['mod'];
	}
	if ($where!='') $where="WHERE ".$where;
	if ($_GET['orderby']!=''){
		$orderby="ORDER BY ".$_GET['orderby'];
	}else{
		$orderby=" ORDER BY d.dl_id DESC";
	}
	$desc='dld.dld_'.$AppUI->user_locale;
	$sql="SELECT
					d.dl_id,
					CONCAT(`user_last_name`,', ',`user_first_name`) AS user_name,
					m.mod_name,
					d.dl_timestamp,
					$desc AS description
				FROM delegations_log AS d
				INNER JOIN modules AS m
					ON (d.dl_module_id=m.mod_id)
				INNER JOIN users AS u
					ON (d.dl_user_id=u.user_id)
				INNER JOIN delegations_log_desc AS dld
					ON (d.dl_description=dld.dl_description AND
							d.dl_module_id=dld.dl_module_id)
				$where";
	//echo "<br>$sql<br>";
	$rc=db_exec($sql);
	$num=db_num_rows($rc);
	$tpag=round($num/25);
	if ($num>$tpag*25) $tpag++;
	$sql="SELECT
					CONCAT(`user_last_name`,', ',`user_first_name`) AS user_name,
					m.mod_name,
					d.dl_timestamp,
					date_format(d.dl_timestamp,'%Y%m%d%H%i%s') as dl_timestamp,
					$desc AS description
				FROM delegations_log AS d
				INNER JOIN modules AS m
					ON (d.dl_module_id=m.mod_id)
				INNER JOIN users AS u
					ON (d.dl_user_id=u.user_id)
				INNER JOIN delegations_log_desc AS dld
					ON (d.dl_description=dld.dl_description AND
							d.dl_module_id=dld.dl_module_id)
				$where
				$orderby
					LIMIT $linefrom,25";
	//echo "<br><br>$sql<br><br>";
	$rc=db_exec($sql);
	echo "<TABLE width='100%' border='0' cellpadding='1' cellspacing='1'>\n";
	echo "<TR class='row-category'>\n";
	echo "<TH>&nbsp</TH>";
	echo "<TH>".order($_GET, $AppUI->_('user_name'), 'user_name')."</TH>\n";
	echo "<TH>".order($_GET, $AppUI->_('Module'), 'mod_name')."</TH>\n";
	echo "<TH>".order($_GET, $AppUI->_('timestamp'), 'dl_timestamp')."</TH>\n";
	echo "<TH>".order($_GET, $AppUI->_('activity'), 'description')."</TH>\n";
	echo "</TR>\n";
	$chc=1;
	//echo "<br> nro de filas".mysql_num_rows($rc)."<br>";
	while ($vec=mysql_fetch_array($rc)){
		if ($chc==(-1)) $color='#ffffff';
		else $color="#dddddd";
		
		
		$year=substr($vec['dl_timestamp'], 0, 4);
		$month=substr($vec['dl_timestamp'], 4, 2);
		$day=substr($vec['dl_timestamp'], 6, 2);
		$hour=substr($vec['dl_timestamp'], 8, 2);
		$min=substr($vec['dl_timestamp'], 10, 2);
		$mod=$vec['mod_name'];
		$line=$linefrom+1;
		echo "<TR>\n";
		echo "<TD bgcolor='$color' align='center'>$line</TD>\n";
		echo "<TD bgcolor='$color'>".$vec['user_name']."</TD>\n";
		echo "<TD bgcolor='$color'>";
				eval("echo \$AppUI->_('$mod');");
		echo "</TD>\n";
		echo "<TD bgcolor='$color'>$hour:$min $day/$month/$year</TD>\n";
		echo "<TD bgcolor='$color'>".$vec['description']."</TD>\n";
		echo "</TR>\n";
		$linefrom++;
		$chc=$chc*(-1);
	}
	if ($tpag>1){
		$link="index.php?m=delegates";
		$link .="&tab=2";
		$link .="&from_day=".$_GET['from_day'];
		$link	.="&from_month=".$_GET['from_month'];
		$link	.="&from_year=".$_GET['from_year'];
		$link	.="&to_day=".$_GET['to_day'];
		$link .="&to_month=".$_GET['to_month'];
		$link .="&to_year=".$_GET['to_year'];
		$link .="&x=".$_GET['x'];
		$link .="&y=".$_GET['y'];
		$link .="&user=".$_GET['user'];
		$link .="&mod=".$_GET['mod'];
		$link .="&orderby=".$_GET['orderby'];
		$link .="&order=".$_GET['order'];
		$pagmenos=$pag-1;
		$pagmas=$pag+1;
		if ($pagmenos >= 1){
			$link1=$link."&pag=".$pagmenos;
			$link1="<a href='$link1'> << </a>";
		}
		else $link1=" << ";
		if ($pagmas <= $tpag){
			$link2=$link."&pag=".$pagmas;
			$link2="<a href='$link2'> >> </a>";
		}
		else $link2=" >> ";
		echo "<TR><TD colspan='5' align='center'> $link1 $pag/$tpag $link2 </TD></TR>";
	}
	echo "<TABLE>";
}

function mkmod($mod, $AppUI){
	$sql="SELECT mod_id, mod_name
				FROM modules as m
				INNER JOIN delegations_log AS d
					ON (d.dl_module_id=m.mod_id)
				GROUP BY mod_name";
	//echo "<br>$sql<br>";
	$rc=db_exec($sql);

	if ($_GET['mod']!='') $sel='SELECTED';
	echo "<SELECT name='mod' onchange=\"javascript: this.form.submit();\">";
	echo "<OPTION value='' $sel>".$AppUI->_('All')."</OPTION>";
	while ($vec=db_fetch_array($rc)){
		if ($vec['mod_id']==$_GET['mod']) $sel='SELECTED';
		ELSE $sel='';
		$mod=$vec['mod_name'];
		echo "<OPTION value='".$vec['mod_id']."' $sel>";
		eval("echo \$AppUI->_('$mod');");
		echo "</OPTION>";
	}
}

function mkowner ($owner, $AppUI){
	$sql="SELECT d.dl_delegate_id, CONCAT(`user_last_name`,', ',`user_first_name`) AS name
				FROM users as u
				INNER JOIN delegations_log AS d
					ON (d.dl_delegate_id=u.user_id)
				GROUP BY name";
	//echo "<br>$sql<br>";
	$rc=db_exec($sql);
	if ($owner=='') $sel='SELECTED';
	echo "<SELECT name='owner' onchange=\"javascript: this.form.submit();\">";
	echo "<OPTION value='' $sel>".$AppUI->_('All')."</OPTION>";
	while ($vec=db_fetch_array($rc)){
		if ($vec['dl_delegate_id']==$owner) $sel='SELECTED';
		ELSE $sel='';
		echo "<OPTION value='".$vec['dl_delegate_id']."' $sel>".$vec['name']."</OPTION>";
	}
	?>
	</SELECT>
	<?php
}


function mkuser ($user, $AppUI){
	$sql="SELECT user_id, CONCAT(`user_last_name`,', ',`user_first_name`) AS name
				FROM users as u
				INNER JOIN delegations_log AS d
					ON (d.dl_user_id=u.user_id)
				GROUP BY name";
	//echo "<br>$sql<br>";
	$rc=db_exec($sql);
	if ($user=='') $sel='SELECTED';
	echo "<SELECT name='user' onchange=\"javascript: this.form.submit();\">";
	echo "<OPTION value='' $sel>".$AppUI->_('All')."</OPTION>";
	while ($vec=db_fetch_array($rc)){
		if ($vec['user_id']==$user) $sel='SELECTED';
		ELSE $sel='';
		echo "<OPTION value='".$vec['user_id']."' $sel>".$vec['name']."</OPTION>";
	}
	?>
	</SELECT>
	<?php
}

function mkOption ($from, $to, $formsel) {
	while ($to >= $from){
		if ($from==$formsel) $sel='SELECTED';
		if ($from<10) $cero=0;
		echo "<option value='$cero$from' $sel>$cero$from</option>\n";
		$sel='';
		$cero='';
		$from++;
	}
}

function mkFromTo($from_date, $to_date, $AppUI){
	$from_year=substr($from_date, 0, 4);
	$from_month=substr($from_date, 4, 2);
	$from_day=substr($from_date, 6, 2);
	$to_year=substr($to_date, 0, 4);
	$to_month=substr($to_date, 4, 2);
	$to_day=substr($to_date, 6, 2);
	?>
	<table>
		<TR>
			<TD><?php echo $AppUI->_('From');?>:</TD>
			<TD>
				<select name='from_day' size="1" class="text">
					<?php mkOption (1, 31, $from_day); ?>
				</select>
				<select name='from_month' size="1" class="text">
					<?php mkOption (1, 12, $from_month ); ?>
				</select>
				<select name='from_year' size="1" class="text">
					<?php mkOption (2000, date("Y"), $from_year); ?>
				</select>
				<INPUT type="image" src="images/arrow-right.gif" onclick="javascript: this.form.submit();">
			</TD>
		</TR>
		<TR>
			<TD><?php echo $AppUI->_('To');?>:</TD>
			<TD>
				<select name='to_day' size="1" class="text">
					<?php mkOption (1, 31, $to_day); ?>
				</select>
				<select name='to_month' size="1" class="text">
					<?php mkOption (1, 12, $to_month ); ?>
				</select>
				<select name='to_year' size="1" class="text">
					<?php mkOption (2000, date("Y"), $to_year); ?>
				</select>
				<INPUT type="image" src="images/arrow-right.gif" onclick="javascript: this.form.submit();">
			</TD>
		</TR>
	</table>
	<?php
}
?>