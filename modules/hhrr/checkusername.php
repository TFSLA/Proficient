<?
	global $firstname, $lastname, $callback;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta name="Version" content="1.0" />
	<meta http-equiv="Content-Type" content="text/html;charset=iso-8859-15" />
	<title>Technology for Solutions</title>
	<link rel="stylesheet" type="text/css" href="./style/default/main.css" media="all" />
	<style type="text/css" media="all">@import "./style/default/main.css";</style>
</head>

<body onload="loader()">
<br>
<br>

<table width="95%" align="center" border="0" cellspacing="0" cellpadding="0" bgcolor="#000000">
	<tr>
    <td class="celdatextoizquierda">Su nombre de usuario será:</td>
		<td class="celdatextoizquierda">
<?php
	$username = substr(trim($firstname),0,1).trim($lastname);
	$username = strtolower(str_replace(" ", "", $username));
	$newusername = $username;
	$sql= "select count(*) from users where user_username = '$newusername'";
	if (db_loadResult($sql)>0){
		$rta=1; $i=1;
		while ($rta>0){
			$newusername = $username.$i;
			$sql= "select count(*) from users where user_username = '$newusername'";
			$rta = db_loadResult($sql);
			$i++;
		}
	}
	echo "<b>$newusername</b>";
	//echo "<pre>$sql \n".db_loadResult($sql)."</pre>";
?>
		</td>
	</tr>
<?/*  <tr><td class="celdatextoizquierda" colspan="2"><input type="button" class="button" value="<?=$AppUI->_("ok")?>" onclick="goback()"></td></tr> */?>
</table>
<script language="javascript">
		
function goback(){
		window.opener.<?=$callback?>('<?=$newusername?>');
		window.close();
}

function loader(){
	window.setTimeout("goback()", 1000);
}


</script>
</body>
</html>