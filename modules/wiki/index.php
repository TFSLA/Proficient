<?php


if($AppUI->user_type != 1)
if( ! in_array($AppUI->user_company, array("2","8"))){ 
	$AppUI->redirect( "m=public&a=access_denied" );	
}


$titleBlock = new CTitleBlock( 'Wiki', 'wiki.gif', $m, "$m.$a" );
$titleBlock->show();
?>
<br><br><br><br><br><br>
<center>
<a href="http://tfsla.sismonda.com.ar/wiki/index.php/Portada"  target="_blank">http://tfsla.sismonda.com.ar/wiki/index.php/Portada</a>
</center>