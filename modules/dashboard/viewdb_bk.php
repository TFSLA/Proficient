<?
$AppUI->savePlace();

// setup the title block
$titleBlock = new CTitleBlock( 'DashBoard', 'calendar.gif', $m, "$m.$a" );
$titleBlock->addCell('<a href="?m=dashboard">'.$AppUI->_( 'Customize your Dashboard' ).'</a>&nbsp;&nbsp;');
$titleBlock->show();
?>
<? $user_id=$AppUI->user_id; ?>

 <!-- INICIO DASHBOARD
***************************
***************************
***************************
***************************
***************************
--> 
<?
      $i=0;
      $sql = "SELECT * FROM db_rows WHERE user_id = $user_id ORDER BY db_row_pos;";
      $filas = db_loadList($sql);
      if (count($filas)==0){
		echo "<br><br><br><center>";
        echo $AppUI->_( "Your Dasboard is empty" );
		echo "<br>";
        echo "<a href='index.php?m=dashboard'>".$AppUI->_( "Click Here to compose your Dashboard" )."</a>";
		echo "</center>";
      }

      //echo '<table width="100%" border="0" cellpadding="2" cellspacing="0" class="plain">'."\n";
?>
<table width="100%" border="0" cellpadding="2" cellspacing="0" class="plain">
<?
      foreach($filas as $row){
      	
		

        $sql = "SELECT * FROM db_cols WHERE db_row_id = {$row["db_row_id"]} ORDER BY db_col_pos;";
        $columnas = db_loadList($sql);
      ?>
<tr>
	<td valign="top" width="<?=100/count($columnas);?>%">      
	<table width="100%" border="0" cellpadding="2" cellspacing="0" class="plain">      
	<tr>
<?
      	//echo '<tr><td valign=\"top\">'."\n";
        //echo '<table width="100%" border="0" cellpadding="2" cellspacing="0" class="plain"><tr>'."\n";

        foreach ($columnas as $row2){
      ?>
		<td valign="top">
		<table width="100%" border="0" cellpadding="2" cellspacing="0" class="plain">
<?
	  		//echo "<td valign=\"top\" width=\"".(100/count($columnas))."%\" >"."\n";
	  		//echo '<table width="100%" border="0" cellpadding="2" cellspacing="0" class="plain">'."\n";
   	       	$sql = "SELECT * FROM db_cells, infoboxes WHERE infoboxes.infobox_id = db_cells.infobox_id AND db_col_id = {$row2["db_col_id"]} ORDER BY db_cell_pos;";
	        $celdas = db_loadList($sql);
          	foreach ($celdas as $row3){
	      ?>
			<tr>
				<th><b><?=$AppUI->_( $row3["infobox_name"] );?></b></th>
			</tr>
			<tr>
				<td valign="top">
				<!-- *************************** Inicio <?=$AppUI->_( $row3["infobox_name"] );?>  ***************************-->
				<?
				require( $row3["infobox_program"] );
	      		?>
				<!-- *************************** Fin <?=$AppUI->_( $row3["infobox_name"] );?>  ***************************-->
				</td>
			</tr>
<?
				//echo "<tr><th><b>".$AppUI->_( $row3["infobox_name"] )."</b></th></tr>";
				//echo "<tr><td valign=\"top\">";
				//echo "<!-- ***************************".$AppUI->_( $row3["infobox_name"] )."***************************   -->";
				//require( $row3["infobox_program"] );
				//echo "</td></tr>";
			}
      ?>
		</table>
		</td>
<?			
			//echo '</table>';
			//echo "</td>";
		}
?>
	</tr> 
	</table>
	</td>
</tr>
<?		
		//echo '</tr></table>';
		//echo "</td></tr>";
      }  
      //echo '</table>';
?>
</table>
<!-- ***************************
  ***************************
  ***************************
  FIN DASHBOARD --> 
