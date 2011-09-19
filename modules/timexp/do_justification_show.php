<?php

$path = "/files/timexp/licences/";

$sql = "select justification_name from timexp_licences_justifications where 
		justification_id = ".$_REQUEST['id'];
$result = mysql_query($sql);
$row = mysql_fetch_array($result);


echo '
<script language="JavaScript">
    	window.location= "'.$path.$row['justification_name'].'";
</script>' ;

?>