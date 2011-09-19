<script language="Javascript" type="text/javascript">
  function doPage(page){
	document.privmsg_list.page.value=page;
  	document.privmsg_list.submit();
  }

	//
	// Should really check the browser to stop this whining ...
	//
	function select_switch(status)
	{
		for (i = 0; i < document.privmsg_list.length; i++)
		{
			document.privmsg_list.elements[i].checked = status;
		}
	}
</script>

<style type="text/css">
<!--

/* Main table cell colours and backgrounds */
td.row1	{ background-color: #EFEFEF; }
td.row2	{ background-color: #DEE3E7; }
td.row3	{ background-color: #D1D7DC; }
.privHeaderCell { background-color: #333333; }
.privLineCell   { background-color: #E9E9E9; }
.privFooterCell { background-color: #E9E9E9; }
.privFooterLineCell { background-color: #FFFFFF; }

/* Form elements */
input,textarea, select {
	color : ;
	font: normal 11px Verdana, Arial, Helvetica, sans-serif;
	border-color : ;
}

/* The text input fields background colour */
input.post, textarea.post, select {
	background-color : #FFFFFF;
}

-->
</style>

<?
$AppUI->savePlace();

if (isset( $_GET['tab'] )) {
    $AppUI->setState( 'PrivMsgIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'PrivMsgIdxTab' ) !== NULL ? $AppUI->getState( 'PrivMsgIdxTab' ) : 0;


// setup the title block
$titleBlock = new CTitleBlock(  $AppUI->_('Private Messaging'), 'webmail.gif', $m, "colaboration.index" );
$titleBlock->show();
include('mnfolders.php');
?>
<br clear="all" />

<form method="post" name="privmsg_list" action="index.php?m=privmsg&a=sentbox">
  <input type="hidden" name="page" value="<?=$page ?>">
  <table border="0" cellpadding="0" cellspacing="0" background="images/common/back_botones-01.gif">
      <tr>
        <td><table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
              <td><img src="images/common/cuadradito_naranja.gif" width="9" height="9">
              <a href="index.php?m=privmsg&a=newmsg"><?php echo $AppUI->_('Send a new private message'); ?></a><span class="boldtext"> </span></td>
              <td width="6"> <div align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></div></td>
            </tr>
          </table></td>
        <td><table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
              <td><img src="images/common/cuadradito_naranja.gif" width="9" height="9">
              <a href="javascript:select_switch(true);"><?php echo $AppUI->_('Mark All'); ?></a><span class="boldtext"> </span>
              </td>
              <td width="6"> <div align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></div></td>
            </tr>
          </table></td>
        <td><table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
              <td><img src="images/common/cuadradito_naranja.gif" width="9" height="9">
              <a href="javascript:select_switch(false);"><?php echo $AppUI->_('Unmark All'); ?></a><span class="boldtext"> </span>
              </td>
              <td width="6"> <div align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></div></td>
            </tr>
          </table></td>
      </tr>
  </table>
  <table border="0" cellpadding="1" cellspacing="0" width="100%">
   <tr>
        <td height="1" colspan="5" bgcolor="#666666"></td>
   </tr>
   <tr class="privHeaderCell">
        <td width="5%"><div align="left">&nbsp;<span class="boldblanco"><?php echo $AppUI->_('Flag'); ?></span></div></td>
        <td width="50%">&nbsp;<span class="boldblanco"><?php echo $AppUI->_('Subject'); ?></span></td>
        <td width="10%" align="center" class="boldblanco"><?php echo $AppUI->_('To'); ?></td>
        <td width="30%" align="center" class="boldblanco"><?php echo $AppUI->_('Date'); ?></td>
        <td width="5%" class="boldblanco"><?php echo $AppUI->_('Mark'); ?></td>
   </tr>
<?

if($msgdays>0){
  if($msgdays==1)     $where = " AND date >= date_sub(now(), interval 1 day) ";
  if($msgdays==7)     $where = " AND date >= date_sub(now(), interval 7 day) ";
  if($msgdays==14)    $where = " AND date >= date_sub(now(), interval 14 day) ";
  if($msgdays==30)    $where = " AND date >= date_sub(now(), interval 30 day) ";
  if($msgdays==90)    $where = " AND date >= date_sub(now(), interval 90 day) ";
  if($msgdays==180)   $where = " AND date >= date_sub(now(), interval 180 day) ";
  if($msgdays==364)   $where = " AND date >= date_sub(now(), interval 254 day) ";
}
      if($deleteall!="") $result=mysql_query("DELETE FROM messages WHERE sender_id = {$AppUI->user_id} AND folder='SENTBOX'");
      if($delete!=""){
        $result = mysql_query("SELECT message_id FROM  messages WHERE sender_id = {$AppUI->user_id} AND folder='SENTBOX' AND saved<>'Y';");
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
          if($GLOBALS["mark".$row["message_id"]]=="on"){
             $resultx=mysql_query("DELETE FROM messages WHERE message_id = {$row["message_id"]}");        
          }
        }
      }
      if($save!=""){
        $result = mysql_query("SELECT message_id FROM  messages WHERE sender_id = {$AppUI->user_id} AND folder='SENTBOX' AND saved<>'Y';");
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
          if($GLOBALS["mark".$row["message_id"]]=="on"){
             $resultx=mysql_query("UPDATE messages set saved = 'Y' WHERE message_id = {$row["message_id"]}");        
          }
        }
      }

      $itemsperpage=15;
      if($page=="") $page=1;
      $initreg = ($page-1)*$itemsperpage;
      $result = mysql_query("SELECT user_username, user_first_name, user_last_name, message_id, subject, isread, sender_id, DATE_FORMAT(date,'%a %b %c, %Y %h:%i %p') as datefmt FROM  messages left join users ON messages.recipient_id=users.user_id WHERE messages.sender_id = {$AppUI->user_id} AND folder='SENTBOX' AND saved<>'Y' $where ORDER BY date DESC  LIMIT $initreg, $itemsperpage;");
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
?>
    <tr>
      <?
        if($row["isread"]=="N")
          echo '<td class="" width="5%" align="center" valign="middle"><img src="modules/privmsg/images/folder_new.gif" width="19" height="18" alt="'. $AppUI->_('Unread message').'" title="'. $AppUI->_('Unread message').'" /></td>';
        else
          echo '<td class="" width="5%" align="center" valign="middle"><img src="modules/privmsg/images/folder.gif" width="19" height="18" alt="'.$AppUI->_('Read message').'" title="'.$AppUI->_('Read message').'" /></td>';
      ?>
      <td width="40%" valign="middle" class=""><span class="topictitle">&nbsp;&nbsp;<a href="index.php?m=privmsg&a=viewmsg&id=<?=$row["message_id"]?>" class="topictitle"><?=$row["subject"]?></a></span></td>
      <td width="20%" valign="middle" align="center" class=""><span class="name">&nbsp;<a href="index.php?m=privmsg&a=newmsg&username=<?=$row["user_username"]?>" class="name"><?=$row["user_username"]?></a></span></td>
      <td width="30%" align="center" valign="middle" class=""><span class="postdetails"><?=$row["datefmt"]?></span></td>
      <td width="5%" align="center" valign="middle" class=""><span class="postdetails">
        <input type="checkbox" name="mark<?=$row["message_id"]?>" value="on" />
        </span></td>
    </tr>
    <tr>
        <td height="1" colspan="5" class="privLineCell"></td>
    </tr>
<?
      }
?>
   </table>
    <table class="privFooterCell" border="0" cellpadding="1" cellspacing="0" width="100%" >
        <tr>
            <td  colspan="5"><?php echo $AppUI->_('Display messages from previous'); ?>:
                <select name="msgdays" class="formularios"><option value="0" <?if($msgdays==0)echo "selected "?>><?php echo $AppUI->_('All Messages'); ?></option><option value="1" <?if($msgdays==1)echo "selected "?>>1 <?php echo $AppUI->_('Day'); ?></option><option value="7" <?if($msgdays==7)echo "selected "?>>7 <?php echo $AppUI->_('Days'); ?></option><option value="14" <?if($msgdays==14)echo "selected "?>>2 <?php echo $AppUI->_('Weeks'); ?></option><option value="30" <?if($msgdays==30)echo "selected "?>>1 <?php echo $AppUI->_('Month'); ?></option><option value="90" <?if($msgdays==90)echo "selected "?>>3 <?php echo $AppUI->_('Months'); ?></option><option value="180" <?if($msgdays==180)echo "selected "?>>6 <?php echo $AppUI->_('Months'); ?></option><option value="364" <?if($msgdays==364)echo "selected "?>>1 <?php echo $AppUI->_('Year'); ?></option>
                </select>
                <input type="submit" value="<?php echo strtolower($AppUI->_('Refresh')); ?>" name="submit_msgdays" class="button" />
            </td>
      </tr>
    </table>
    <table class="privFooterCell" border="0" cellpadding="1" cellspacing="0" width="100%">
        <tr>
            <td height="1" class="privFooterLineCell"></td>
        </tr>
    <tr>
      <td align="right">
        <!--input type="submit" name="save" value="<?php echo $AppUI->_('Save Marked'); ?>" class="button" /-->
        <input type="submit" name="delete" value="<?php echo strtolower($AppUI->_('Delete Marked')); ?>" class="buttonbig" />
        <input type="submit" name="deleteall" value="<?php echo strtolower($AppUI->_('Delete All')); ?>" class="buttonbig" />
      </td>
    </tr>
  </table>
<?
        $query="select count(*) FROM  messages, users WHERE users.user_id = messages.sender_id AND recipient_id = {$AppUI->user_id} AND folder='SENTBOX' AND saved<>'Y'";
    	if ($idbrand!=0) $query.=" where products.brand=$idbrand ";
        $result = mysql_query($query) or die(mysql_error());

	$row = mysql_fetch_row($result);
	$num = $row[0];
        if(floor($num / $itemsperpage) < $num / $itemsperpage) $numpages = floor($num / $itemsperpage) + 1;
        else $numpages = floor($num / $itemsperpage);

?>
  <table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
	<tr>
	  <td align="left" valign="middle"></td>
	  <td align="left" valign="middle" width="100%"><span class="nav"><?php echo $AppUI->_('Page'); ?> <b><?=$page?></b> <?php echo $AppUI->_('of'); ?> <b><?if($numpages==0)echo "1";else echo $numpages;?></b></span></td>
	  <td align="right" valign="top" nowrap="nowrap"></td>
	</tr>
  </table>
<?

        echo '<table align="left"  border="0" cellspacing="0"><tr valign="top">';
        echo '<td colspan="2" width="24%" align="left" class="textonormal"><b>';

        if($numpages > 1){
	  echo "Page: &nbsp;&nbsp;";
          if($page <> 1){
            echo '<a href="javascript:doPage(';
	    echo $page-1;
	    echo ')">';
	    echo 'Previous</a>&nbsp;&nbsp;';
          }
          for($i=1;$i<=$numpages;$i++){
            if($i<>$page) echo "<a href=\"javascript:doPage($i)\">";
            echo "$i";
            if($i<>$page) echo '</a>';
            echo '&nbsp;';
          }

          if($page <> $numpages) {
            echo '&nbsp;&nbsp;<a href="javascript:doPage(';
	    echo $page+1;
	    echo ')">';
            echo 'Next</a>';
          }
        }
        echo '</b></td></tr></table>';
?>
</form>
