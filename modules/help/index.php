<?php /* $Id: index.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */


echo '<table width="100%" border="0" cellpadding="0" cellspacing="0"  background="images/silver-back_header.jpg">
      <tr> 
        <td>
        	<img src="images/silver-logo.jpg" width="400" height="43">
        	</td>
      </tr>
    </table>';	

$hid = dPgetParam( $_GET, 'hid', 'help.toc' );
$suppressLogo = dPgetParam( $_GET, 'suppressLogo', 0 );
$inc = "{$AppUI->cfg['root_dir']}/modules/help/{$AppUI->user_locale}/$hid.hlp";
$lang=$AppUI->user_locale;
$FROM="FROM help AS h ";
$JOIN="INNER JOIN modules AS m on (m.mod_id=h.mod_id) ";
$ORDER="ORDER BY title_".$lang." ASC";
IF ($hid) {
	$SELECT="SELECT help_icon, title_".$lang.", body_".$lang." ";
	$WHERE="WHERE help_search='$hid' ";
	$sql=$SELECT.$FROM.$WHERE.$ORDER;
	//echo "$sql<br>";
	$rc=mysql_query($sql);
	$vec=mysql_fetch_array($rc);
	echo "<table style='background-color: #E9E9E9; width: 490;' border='0' cellpadding='0' cellspacing='2'>
		<tr>
			<td colspan='2' height='5'>
			</td>
		</tr>
		<tr valign='middle'>
			<td class='tittle' valign='middle' width='29'><img src='./images/$vec[0]'  border='0' height='29' width='29'></td><td class='titularmain2'>&nbsp;&nbsp;".$AppUI->_($vec[1])."</td>
		</tr>
		<tr>
			<td colspan='2'>
				<blockquote>
					$vec[2]	
				</blockquote>
			</td>
		</tr>
		</table>";
}
ELSE {
	$SELECT="SELECT help_id, title_".$lang.", help_search, mod_base, help_icon ";
	$sql=$SELECT.$FROM.$WHERE.$ORDER;
	//echo "<br>$sql<br>";
	$rc=mysql_query($sql);
	echo "<table style='background-color: #E9E9E9; width: 490;' border='0' cellpadding='0' cellspacing='2'>
		<tr valign='middle'>
			<td  width='35'>&nbsp;<img src='./images/help.gif'  border='0' height='29' width='29'></td>
			<td class='titularmain2' align='left' class='titularmain2'>".$AppUI->_('Help')."</td>
		</tr>
		<tr>
			<td colspan='2'>
				<table align='center' border='0'>
				<br>
				<blockquote>";
	WHILE ($vec=mysql_fetch_array($rc)){
		IF ($vec[3]!=1) $space="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		ELSE $space="&nbsp;";
		echo "
						<tr valing='middle'>
							<td>&nbsp;&nbsp;&nbsp;
								<a href='index.php?m=help&suppressLogo=1&dialog=1&hid=".$vec[2]."'><img src='./images/$vec[4]'  order='0' height='29' width='29'></a>
							</td>
							<td>
								<p style='font-weight:600;'>".$vec[1]."</p>
							</td>
						</tr>";
	}
	echo "	
				</table>
				</blockquote>
			</td>
		</tr>
		</table>";
}
?>