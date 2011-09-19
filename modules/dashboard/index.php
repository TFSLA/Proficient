<?
$AppUI->savePlace();

// setup the title block
$titleBlock = new CTitleBlock( 'DashBoard', 'calendar.gif', $m, "$m.$a" );
$titleBlock->show();
?>
<form method=post action="?m=dashboard&newcell=true">
<table border=0 width="100%">
<tr>
<td valign=top width="220">

  <? $user_id=$AppUI->user_id; ?>

  <?
    if($rowdel != ""){
      $result = mysql_query("SELECT * FROM db_rows WHERE user_id = $user_id and db_row_id = $rowdel ORDER BY db_row_pos;");
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      $result2 = mysql_query("SELECT * FROM db_cols WHERE db_cols.db_row_id = {$row["db_row_id"]} ORDER BY db_col_pos;");
      while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
        $result = mysql_query("DELETE FROM db_cells WHERE db_col_id = {$row2["db_col_id"]};");
        $result = mysql_query("DELETE FROM db_cols WHERE db_col_id = {$row2["db_col_id"]};");
      }
      $result = mysql_query("DELETE FROM db_rows WHERE user_id = $user_id and db_row_id = $rowdel;");
    }

    if($coldel != ""){
      $result = mysql_query("DELETE FROM db_cells WHERE db_col_id = $coldel;");
      $result = mysql_query("DELETE FROM db_cols WHERE db_col_id = $coldel;");
    }

    if($celldel != ""){
      $result = mysql_query("DELETE FROM db_cells WHERE db_cell_id = $celldel;");
    }

    if($cellup != ""){
      $result = mysql_query("SELECT * FROM db_cells WHERE db_cell_id = $cellup;");
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      $colid = $row["db_col_id"];
      $oldpos = $row["db_cell_pos"];
      $result2 = mysql_query("SELECT * FROM db_cells WHERE db_col_id = $colid AND db_cell_pos < $oldpos ORDER BY db_cell_pos DESC;");
      if(mysql_num_rows($result2)>0){
	$row2 = mysql_fetch_array($result2, MYSQL_ASSOC);
	$newpos = $row2["db_cell_pos"];
        $result3 = mysql_query("UPDATE db_cells SET db_cell_pos = $newpos WHERE db_cell_id = $cellup;");
        $result4 = mysql_query("UPDATE db_cells SET db_cell_pos = $oldpos WHERE db_cell_id = {$row2["db_cell_id"]};");
      }
    }

    if($celldown != ""){
      $result = mysql_query("SELECT * FROM db_cells WHERE db_cell_id = $celldown;");
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      $colid = $row["db_col_id"];
      $oldpos = $row["db_cell_pos"];
      $result2 = mysql_query("SELECT * FROM db_cells WHERE db_col_id = $colid AND db_cell_pos > $oldpos ORDER BY db_cell_pos ASC;");
      if(mysql_num_rows($result2)>0){
	$row2 = mysql_fetch_array($result2, MYSQL_ASSOC);
	$newpos = $row2["db_cell_pos"];
        $result3 = mysql_query("UPDATE db_cells SET db_cell_pos = $newpos WHERE db_cell_id = $celldown;");
        $result4 = mysql_query("UPDATE db_cells SET db_cell_pos = $oldpos WHERE db_cell_id = {$row2["db_cell_id"]};");
      }
    }

    if($rowup != ""){
      $result = mysql_query("SELECT * FROM db_rows WHERE db_row_id = $rowup;");
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      $oldpos = $row["db_row_pos"];
      $result2 = mysql_query("SELECT * FROM db_rows WHERE user_id = $user_id AND db_row_pos < $oldpos ORDER BY db_row_pos DESC;");
      if(mysql_num_rows($result2)>0){
	$row2 = mysql_fetch_array($result2, MYSQL_ASSOC);
	$newpos = $row2["db_row_pos"];
        $result3 = mysql_query("UPDATE db_rows SET db_row_pos = $newpos WHERE db_row_id = $rowup;");
        $result4 = mysql_query("UPDATE db_rows SET db_row_pos = $oldpos WHERE db_row_id = {$row2["db_row_id"]};");
      }
    }

    if($rowdown != ""){
      $result = mysql_query("SELECT * FROM db_rows WHERE db_row_id = $rowdown;");
      $row = mysql_fetch_array($result, MYSQL_ASSOC);
      $oldpos = $row["db_row_pos"];
      $result2 = mysql_query("SELECT * FROM db_rows WHERE user_id = $user_id AND db_row_pos > $oldpos ORDER BY db_row_pos ASC;");
      if(mysql_num_rows($result2)>0){
	$row2 = mysql_fetch_array($result2, MYSQL_ASSOC);
	$newpos = $row2["db_row_pos"];
        $result3 = mysql_query("UPDATE db_rows SET db_row_pos = $newpos WHERE db_row_id = $rowdown;");
        $result4 = mysql_query("UPDATE db_rows SET db_row_pos = $oldpos WHERE db_row_id = {$row2["db_row_id"]};");
      }
    }


    if($newrow == "true"){
      $db_row_pos = 0;
      $resultnr = mysql_query("SELECT * FROM db_rows WHERE user_id = $user_id ORDER BY db_row_pos;");
      while ($rownr = mysql_fetch_array($resultnr, MYSQL_ASSOC)) 
	if($rownr["db_row_pos"] >= $db_row_pos) $db_row_pos = $rownr["db_row_pos"] + 1;
      $db_row_height = 0;
      $resultnri = mysql_query("INSERT INTO `db_rows` (`db_row_id`, `user_id`, `db_row_pos`, `db_row_height`) VALUES( '', '$user_id', '$db_row_pos', '$db_row_height') ");
      $resultmx = mysql_query("SELECT MAX(db_row_id) as maxid FROM db_rows;");
      $rowmx = mysql_fetch_array($resultmx, MYSQL_ASSOC);
      $newcol = $rowmx["maxid"];
    }

    if($newcell == "true"){

	$rowid = -1;
	$i=0;
	$result = mysql_query("SELECT * FROM db_rows WHERE user_id = $user_id ORDER BY db_row_pos;");
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	  $i++;
	  if($i == $dbrow) $rowid = $row["db_row_id"];
	}
	if($rowid==-1){
	  ?>
	  <script language="javascript">
	  window.alert("<?=$AppUI->_( 'The Row does not exist: row' )." ".$dbrow?>");
	  </script>
	  <?
	}
	else{
	  $colid = -1;
	  $i=0;
          $result2 = mysql_query("SELECT * FROM db_cols WHERE db_row_id = $rowid ORDER BY db_col_pos;");
          while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
	    $i++;
	    if($i == $dbcol) $colid = $row2["db_col_id"];
	  }
	  if($colid==-1){
	    ?>
	    <script language="javascript">
	    window.alert("<?=$AppUI->_( 'Column' )." ".$dbcol." ".$AppUI->_( 'does not exist for row' )." ".$dbrow?>");
	    </script>
	    <?
	  }
	  else{
            $resultib = mysql_query("SELECT * FROM infoboxes;");
            while ($row = mysql_fetch_array($resultib, MYSQL_ASSOC)) {
              if($GLOBALS["ib".$row["infobox_id"]] == "on"){
		$db_cell_pos = 0;
		$resultpc = mysql_query("SELECT * FROM db_cells WHERE db_col_id = $colid;");
		while ($rowpc = mysql_fetch_array($resultpc, MYSQL_ASSOC)) 
		  if($rowpc["db_cell_pos"] >= $db_cell_pos) $db_cell_pos = $rowpc["db_cell_pos"] + 1;
	        $result = mysql_query("INSERT INTO db_cells (`db_cell_id`,`db_col_id`,`db_cell_pos`,`infobox_id`) VALUES ('','$colid','$db_cell_pos','{$row["infobox_id"]}');");	    
	      }
	    }
	  }
	}
    }

    if($newcol != ""){
      $db_col_pos = 0;
      $result = mysql_query("SELECT * FROM db_cols WHERE db_row_id = $newcol;");
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) 
	if($row["db_col_pos"] >= $db_col_pos) $db_col_pos = $row["db_col_pos"] + 1;
      $db_col_width = 0;
      $result = mysql_query("INSERT INTO `db_cols` (`db_col_id`, `db_row_id`, `db_col_pos`, `db_col_width`) VALUES( '', '$newcol', '$db_col_pos', '$db_col_width') ");
    }
  ?>

  <table width="235" border="0" cellpadding="2" cellspacing="0" class="">
  <tr>
    <td>
        <table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_06.gif">
          <tr>
            <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
            <td align="left" class="tableHeaderText"><img src="images/common/cuadradito_naranja.gif" width="9" height="9">InfoBoxes:</td>
            <td width="6" align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></td>
          </tr>
        </table>
    </td>
  </tr>
  <?
      $result = mysql_query("SELECT * FROM infobox_groups ORDER BY infobox_group_pos;");
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo "<tr class=\"tableForm_bg\"><td colspan=\"3\">".$AppUI->_( 'Group' ).": <b>".$AppUI->_( $row["infobox_group_name"] )."</b></td></tr>";
        $result2 = mysql_query("SELECT * FROM infoboxes WHERE infobox_group_id = {$row["infobox_group_id"]} ORDER BY infobox_name;");
        while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
          echo '<tr><td colspan="3"> &nbsp;&nbsp;<input type="checkbox" value="on" name="ib'.$row2["infobox_id"].'"> <span title="'.$AppUI->_( $row2["infobox_description"] ).'">'.$AppUI->_( $row2["infobox_name"] )."</span></b></td></tr>";

        }
        echo "<tr class=\"tableRowLineCell\"><td colspan=\"3\"></td></tr>";
      }
      echo '<tr><td colspan="3"> &nbsp;&nbsp;</td></tr>';
  ?>
  <tr class="tableForm_bg"><th colspan="3"><?php echo $AppUI->_( 'Add selected InfoBoxes to' );?><br><?php echo $AppUI->_( 'row' );?>

        <script language="javascript">
            function changeCombo2Doble(x){
                for (m=Combo2Doble.options.length-1;m>0;m--)
                    Combo2Doble.options[m]=null;
                    for (i=0;i<arCbo[x].length;i++){
                        Combo2Doble.options[i]=new Option(arCbo[x][i].text,arCbo[x][i].value);
                    }
                    Combo2Doble.options[0].selected=true;
            }

            var arCbo = new Array();
      <?
      $maxrow = 0;
      $result = mysql_query("SELECT * FROM db_rows WHERE user_id = $user_id ORDER BY db_row_pos;");
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $result2 = mysql_query("SELECT * FROM db_cols WHERE db_cols.db_row_id = {$row["db_row_id"]} ORDER BY db_col_pos;");
	    $maxcolaux=0;
	    $maxrow++;
            //Codigo JS
              echo "arCbo[$maxrow-1] = new Array();\n";
            //
        while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
          $maxcolaux++;
             //Codigo JS
             echo "arCbo[$maxrow-1][$maxcolaux-1] = new Option('$maxcolaux','$maxcolaux');\n";
             //

        }
	if($maxcolaux > $maxcol) $maxcol = $maxcolaux;

      }
      ?>
        </script>
      <?
      echo "<select style='font-size:10px' name='dbrow' onchange='changeCombo2Doble(this.options.selectedIndex)'>";

      for($i=1;$i<=$maxrow;$i++){
          $strSelected="selected";
            if($i!=1) $strSelected="";
        echo "<option $strSelected value='".$i."'>".$i."</option>";
      }
      echo "</select>&nbsp;";

      echo $AppUI->_( 'column' );

      echo " <select style='font-size:10px' name='dbcol'>";
      echo " </select>";
      ?>
        <script language="javascript">
            for(r=0;r<document.forms.length;r++){
                //busco el form que contiene el cbo
                if(document.forms[r].dbcol != null){
                    var Combo2Doble=document.forms[r].dbcol;
                    changeCombo2Doble(document.forms[r].dbrow.options.selectedIndex);
                    //ejecuto la funcion para que llene el cbo de columnas de la fila 1 automaticamente en el load
                }
           }
        </script>
      <?
  ?>

  <input class="button" type="submit" name="btn" value="<?php echo $AppUI->_( 'Add' );?>"></th></tr>
  </table>

</td>
<td valign=top>
  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="" background="images/common/back_1linea_06.gif">
  <tr class="">
    <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
    <td class="tableHeaderText"><img src="images/common/cuadradito_naranja.gif" width="9" height="9"><?php echo $AppUI->_( 'Dashboard Layout Designer' );?></td>
    <td align='right' class="tableHeaderText">
            <a href="?m=dashboard&newrow=true">[<?php echo $AppUI->_( 'add new row' );?>]</a>
  &nbsp;|&nbsp; <a href="?m=dashboard&a=viewdb"><b><?php echo $AppUI->_( 'View your Dashboard' );?></b></a>
    </td>
   <td width="6" align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></td>
  </tr>
  </table><br>


  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
  <?
      $i=0;
      $result = mysql_query("SELECT * FROM db_rows WHERE user_id = $user_id ORDER BY db_row_pos;");

      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	$i++;
	echo "<tr>
            <td>
                <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" background=\"images/common/back_1linea_06.gif\">
                    <tr>
                       <td width=\"6\" align=\"left\"><img src=\"images/common/inicio_1linea.gif\" width=\"6\" height=\"19\"></td>
                        <td class=\"tableHeaderText\"><img src=\"images/common/cuadradito_naranja.gif\" width=\"9\" height=\"9\">".$AppUI->_( 'ROW' )." ".$i.
                        "</td>
                        <td align='right' class=\"tableHeaderText\">
                            <a href='?m=dashboard&newcol=".$row["db_row_id"]."'>[".$AppUI->_( 'add a column to this row' )."]</a>&nbsp;&nbsp;&nbsp;
                            <a href='?m=dashboard&rowup=".$row["db_row_id"]."'><img alt='".$AppUI->_( 'Move up' )."' border=0 src='images/icons/up.gif'></a>
                            <a href='?m=dashboard&rowdown=".$row["db_row_id"]."'><img alt='".$AppUI->_( 'Move down' )."' border=0 src='images/icons/down.gif'></a>
                            <a href='?m=dashboard&rowdel=".$row["db_row_id"]."'><img alt='".$AppUI->_( 'Delete this Row' )."' border=0 src='images/icons/trash_small2.gif'></a>
                        </td>
                        <td width=\"6\" align=\"right\"><img src=\"images/common/fin_1linea.gif\" width=\"3\" height=\"19\"></td>
                    </tr>
                </table>
            </td>";
    echo "</tr><tr><td valign=top>";
	echo '<table width="100%" border="0" cellpadding="2" cellspacing="0" class="plain"><tr>';
	$c=0;
        $result2 = mysql_query("SELECT * FROM db_cols WHERE db_row_id = {$row["db_row_id"]} ORDER BY db_col_pos;");

        while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)){
	  $c++;
	  echo "<td valign=top>";
	  echo '<table width="100%" border="0" cellpadding="2" cellspacing="0" class="plain2">';
	  echo "<tr><th>".$AppUI->_( 'COLUMN' )." ".$c."</th><th align='right'>
		<a href='?m=dashboard&coldel=".$row2["db_col_id"]."'><img alt='".$AppUI->_( 'Delete this Column' )."' border=0 src='images/icons/trash_small2.gif'></a>
		</th></tr>";
	  echo "</th></tr>";
          $result3 = mysql_query("SELECT * FROM db_cells, infoboxes WHERE infoboxes.infobox_id = db_cells.infobox_id AND db_col_id = {$row2["db_col_id"]} ORDER BY db_cell_pos;");

          while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)){
	    echo "<tr><td bgcolor='#eeeeee'><b>InfoBox</b></td><td align='right' bgcolor='#eeeeee'>
		<a href='?m=dashboard&cellup=".$row3["db_cell_id"]."'><img alt='".$AppUI->_( 'Move up' )."' border=0 src='images/icons/up.gif'></a>
		<a href='?m=dashboard&celldown=".$row3["db_cell_id"]."'><img alt='".$AppUI->_( 'Move down' )."' border=0 src='images/icons/down.gif'></a>
		<a href='?m=dashboard&celldel=".$row3["db_cell_id"]."'><img alt='".$AppUI->_( 'Delete this InfoBox' )."' border=0 src='images/icons/trash_small2.gif'></a>
		</td></tr>";
	    echo "<tr><td colspan=2 bgcolor='#eeeeee'><span title='".$AppUI->_( $row3["infobox_description"] )."'>".$AppUI->_( $row3["infobox_name"] )."</span>";
	    echo "</td></tr>";
	    echo "<tr><td height=8></td></tr>";
	  }

	  echo '</table>';
	  echo "</td>";
	}

	echo '</tr></table>';
	echo "</td></tr>";
      }
  ?>
  </table>

</td>
</tr>
</table>
</form>