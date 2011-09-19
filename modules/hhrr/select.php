<?php
//SESSION_START();
//$_SESSION['sticky']='hola';
//print_r($_GET);
//sleep(1);
//include_once("/var/www/htdocs/beta/includes/db_connect.php");
//echo "<table  style='background-color:orange; height: 100; text-align: center;' width='100%' border='1'><td width='50%'>Hola ".$_GET['apellido'].", ".$_GET['nombre']." como va?</td><td width='50%' >Bien?</td></table>";

//echo "<table  style='background-color:orange; height: 100; text-align: center;' width='100%' border='1'><td width='50%'>Hola ".$_GET['apellido'].", ".$_GET['nombre']." como va?</td><td width='50%' >Bien?</td></table>";

					$sql = "SELECT * from hhrr_functional_area WHERE area_parent=".$_GET['area_parent'];
					$list = db_loadHashList( $sql );
					//echo "<table  style='background-color:orange; height: 100; text-align: center;' width='100%' border='1'><tr><td width='50%'>";
					
					//echo "$sql";
					echo arraySelect( $list, 'hhrr_dev_mov_asa1', 'id="hhrr_dev_mov_asa1" size="1" class="text" ', $hhrr_dev_mov_asa1 , false, TRUE, '100px');
					//echo "pedo";
					//echo "</td></tr></table>";
?>
