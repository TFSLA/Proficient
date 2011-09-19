<?

if (getDenyRead( $m ))
	$AppUI->redirect( "m=public&a=access_denied" );

$AppUI->savePlace();

// setup the title block
$titleBlock = new CTitleBlock( 'Email Alerts', 'tasks.gif', $m, "$m.$a" );
$titleBlock->show();
?>

<form method=post action="">
<table border=0 width="100%">
<tr>
<td valign=top width="220">

  <? $user_id=$AppUI->user_id; ?>

  <?

    if($alertdel != ""){
      $result = mysql_query("DELETE FROM useralerts WHERE useralert_id = $alertdel;");
    }

    if(isset($_POST["newcell"])){
            $resultib = mysql_query("SELECT * FROM emailalerts;");
            while ($row = mysql_fetch_array($resultib, MYSQL_ASSOC)) {
	            if($GLOBALS["ib".$row["emailalert_id"]] == "on"){
	            	$sql = "INSERT INTO useralerts (`useralert_id`,`user_id`,`emailalert_id`,`recipient`,`params`) VALUES ('','{$AppUI->user_id}','{$row["emailalert_id"]}','{$_POST["rcpt".$row["emailalert_id"]]}','{$_POST["params".$row["emailalert_id"]]}');";
			        $result = mysql_query($sql);
	      }
	    }
    }

    if(isset($_POST["editalert"])){
    	//echo "<pre>";
    	//var_dump($_POST);
    	//echo "</pre>";
    	for($i=0; $i<count($_POST["user_alert_id"]); $i++){
    		$uaid= $_POST["user_alert_id"][$i];
    		$mail = $_POST["rcpt".$uaid];
    		$params = $_POST["params".$uaid];
    		$sql = "update useralerts set recipient = '$mail', params = '$params' where useralert_id = '$uaid'";
    		$result = mysql_query($sql);
    	}

    }


  ?>

  <table width="235" border="0" cellpadding="0" cellspacing="0" class="">
      <tr>
        <td>
            <table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_06.gif">
              <tr>
                <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
                <td align="left" class="tableHeaderText"><img src="images/common/cuadradito_naranja.gif" width="9" height="9"><?php echo $AppUI->_('Alerts'); ?>:</td>
                <td width="6" align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></td>
              </tr>
            </table>
        </td>
      </tr>
  <?

      $resultu = mysql_query("SELECT * FROM users WHERE user_id = '$user_id';");
      $rowu = mysql_fetch_array($resultu, MYSQL_ASSOC);


      $where  = " WHERE emailalert_group_user_type = '".$rowu["user_type"]."' OR emailalert_group_user_type = '0' ";
      $where2 = " AND (emailalert_user_type = '".$rowu["user_type"]."' OR emailalert_user_type = '0')";
      if($rowu["user_type"] == 1){
        $where  = "";
        $where2 = "";
      }

      $result = mysql_query("SELECT * FROM emailalert_groups $where ORDER BY emailalert_group_pos;");

      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        echo "<tr class=\"tableForm_bg\"><td>".$AppUI->_( 'Group' ).": <b>".$AppUI->_( $row["emailalert_group_name"] )."</b></td></tr>";
        $result2 = mysql_query("SELECT * FROM emailalerts WHERE emailalert_group_id = ".$row["emailalert_group_id"]." ".$where2." ORDER BY emailalert_name;");
        while ($row2 = mysql_fetch_array($result2, MYSQL_ASSOC)) {
         	if(!getDenyRead( $row2["emailalert_module"]))
         	 	echo '<tr><td> &nbsp;&nbsp;<input type="checkbox" value="on" name="ib'.$row2["emailalert_id"].'"> <span title="'.$AppUI->_( $row2["emailalert_description"] ).'">'.$AppUI->_( $row2["emailalert_name"] )."</span></b></td></tr>";
        }
      }
      echo '<tr><td> &nbsp;&nbsp;</td></tr>';
  ?>
  <tr class="tableForm_bg"><th><?php echo $AppUI->_( 'Add selected Alerts' );?>
  <input class="button" type="submit" name="newcell" value="<?php echo strtolower($AppUI->_( 'Add' ));?>"></th></tr>
  </table>

</td>
<td valign=top>
  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="">
      <tr>
          <td>
            <table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_06.gif">
              <tr>
                <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
                <td align="left" class="tableHeaderText"><img src="images/common/cuadradito_naranja.gif" width="9" height="9"><?php echo $AppUI->_( 'Your active alerts' );?>:</td>
                <td width="6" align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></td>
              </tr>
            </table>
          </td>
      </tr>
  </table>
  <br>


	  <table width="100%" border="0" cellpadding="2" cellspacing="0" class="plain2">
	  <?php
	  			$i=0;
          $result3 = mysql_query("SELECT * FROM useralerts, emailalerts, emailalert_groups WHERE emailalerts.emailalert_id = useralerts.emailalert_id AND emailalert_groups.emailalert_group_id = emailalerts.emailalert_group_id AND useralerts.user_id = {$AppUI->user_id} ;");
          while ($row3 = mysql_fetch_array($result3, MYSQL_ASSOC)){
	    echo "<tr><td bgcolor='#eeeeee'><b>".$AppUI->_('Alert').":</b> <span title='".$AppUI->_( $row3["emailalert_description"] )."'><u>".$AppUI->_( $row3["emailalert_group_name"] )."</u> / ".$AppUI->_( $row3["emailalert_name"] )."</span></td><td align='right' bgcolor='#eeeeee'>
		  <a href='?m=emailalerts&alertdel=".$row3["useralert_id"]."'><img alt='".$AppUI->_( 'Delete this Alert' )."' border=0 src='images/icons/trash_small2.gif'></a>
		</td></tr>";
	    echo "<tr><td colspan=2 bgcolor='#eeeeee'>";
	    echo $AppUI->_('Recipient').": <input type='text' name='rcpt".$row3["useralert_id"]."' class='camposform' size='50' value='".($row3["recipient"] ? $row3["recipient"] : $AppUI->user_email)."'>";
	    if($row3["emailalert_hasparams"]=="Y")
	    echo "&nbsp;&nbsp;".$AppUI->_($row3["emailalert_paramdesc"]).": <input type='text' name='params".$row3["useralert_id"]."' class='camposform' size='35' value='".$row3["params"]."'>";
	    echo "</td></tr>";
	    echo "<tr>
	    		<input type='hidden' name='user_alert_id[]' value='".$row3["useralert_id"]."' >
	    		<td height=8></td></tr>";
	  }
	  echo '<tr class="tableForm_bg"><td colspan=2 align="right" >  <input class="button" type="submit" name="editalert" value="'. $AppUI->_( 'Save recipients' ).'">&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>';
	  echo '</table>';
  ?>


</td>
</tr>
</table>
</form>
