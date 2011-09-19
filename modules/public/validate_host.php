<?php 
	error_reporting( E_ALL & ~E_NOTICE & ~E_WARNING );
	
	$host = $_GET["host"] ? $_GET["host"] : "";
	$serv = $_GET["serv"] ? $_GET["serv"] : "http";
	$port =  getservbyname ( $serv, "tcp");




	?>
<br>
<br>

<table width="95%" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#000000">
	<tr>
    <td class="celdatextoizquierda"><strong><?php echo $AppUI->_("Checking server status. Please wait a moment.");?></strong>
    <br><br><br>
<?php

	
	$fp = fsockopen($host, $port, $errno, $errstr, 30);
	$fp = $fp ? 1 : 0 ;
	$rta = !$fp ? "Inactive" : "Active";

	echo "<strong>$host:$port</strong> - ".$AppUI->_("Status").": ".$AppUI->_($rta);
	//echo "<pre>$sql \n".db_loadResult($sql)."</pre>";
?>
		</td>
	</tr>
<?/*  <tr><td class="celdatextoizquierda" colspan="2"><input type="button" class="button" value="<?=$AppUI->_("ok")?>" onclick="goback()"></td></tr> */?>
</table>
<script language="javascript">
		
function goback(){
		window.opener.<?=$callback?>('<?=$fp?>');
		window.close();
}


window.setTimeout("goback()", 1000);
</script>