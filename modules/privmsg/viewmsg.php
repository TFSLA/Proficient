<?
  if($deletemsg!=""){
    $result  = mysql_query("SELECT  recipient_id, folder, user_username, user_first_name, user_last_name, message_id, subject, body, isread, sender_id, DATE_FORMAT(date,'%a %b %c, %Y %h:%i %p') as datefmt, saved  FROM messages, users  WHERE users.user_id = messages.sender_id AND (recipient_id = {$AppUI->user_id} OR sender_id = {$AppUI->user_id}) AND message_id = $id ;");
    $row     = mysql_fetch_array($result, MYSQL_ASSOC);
    if($row["saved"]=="Y") $dest="index.php?m=privmsg&a=savebox";
    else if($row["folder"]=="INBOX") $dest="index.php?m=privmsg";
    else if($row["folder"]=="SENTBOX") $dest="index.php?m=privmsg&a=sentbox";
    $result  = mysql_query("DELETE FROM messages WHERE message_id = $id ;");
	header( "Location: ".$dest );
	die();
  }
?>

<script language="Javascript" type="text/javascript">
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
td.row1	{ background-color: #E9E9E9; }
td.row2	{ background-color: #ffffff; }
td.row3	{ background-color: #D1D7DC; }
.privRow1 { background-color: #E9E9E9; }
.privRow2 { background-color: #E9E9E9; }
.privRow3 { background-color: #D1D7DC; }
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
$titleBlock = new CTitleBlock( 'Private Messaging', 'webmail.gif', $m, "colaboration.index" );
$titleBlock->show();
include('mnfolders.php');
?>

<br clear="all" />

<?
        $result  = mysql_query("SELECT  smileys, recipient_id, folder, user_username, user_first_name, user_last_name, message_id, subject, body, isread, sender_id, DATE_FORMAT(date,'%a %b %c, %Y %h:%i %p') as datefmt, saved  FROM messages, users  WHERE users.user_id = messages.sender_id AND (recipient_id = {$AppUI->user_id} OR sender_id = {$AppUI->user_id}) AND message_id = $id ;");
        $row     = mysql_fetch_array($result, MYSQL_ASSOC);
        $result2 = mysql_query("UPDATE messages set isread='Y' WHERE message_id = $id;");
        $result2 = mysql_query("SELECT  * FROM users  WHERE users.user_id = {$row["recipient_id"]} ;");
        $row2    = mysql_fetch_array($result2, MYSQL_ASSOC);
	$messagebody=str_replace("\n","<br>",$row["body"]);
	if($row["smileys"]=="Y"){
	  $messagebody=str_replace(":D",      "<img src='modules/privmsg/images/smiles/icon_biggrin.gif'>",$messagebody);
	  $messagebody=str_replace(":)",      "<img src='modules/privmsg/images/smiles/icon_smile.gif'>",$messagebody);
	  $messagebody=str_replace(":(",      "<img src='modules/privmsg/images/smiles/icon_sad.gif'>",$messagebody);
	  $messagebody=str_replace("8O",      "<img src='modules/privmsg/images/smiles/icon_eek.gif'>",$messagebody);
	  $messagebody=str_replace("8)",      "<img src='modules/privmsg/images/smiles/icon_cool.gif'>",$messagebody);
	  $messagebody=str_replace(":lol:",   "<img src='modules/privmsg/images/smiles/icon_lol.gif'>",$messagebody);
	  $messagebody=str_replace(":x",      "<img src='modules/privmsg/images/smiles/icon_mad.gif'>",$messagebody);
	  $messagebody=str_replace(":P",      "<img src='modules/privmsg/images/smiles/icon_razz.gif'>",$messagebody);
	  $messagebody=str_replace(":oops:",   "<img src='modules/privmsg/images/smiles/icon_redface.gif'>",$messagebody);
	  $messagebody=str_replace(":o",      "<img src='modules/privmsg/images/smiles/icon_surprised.gif'>",$messagebody);
	  $messagebody=str_replace(":cry:",   "<img src='modules/privmsg/images/smiles/icon_cry.gif'>",$messagebody);
	  $messagebody=str_replace(":evil:",  "<img src='modules/privmsg/images/smiles/icon_evil.gif'>",$messagebody);
	  $messagebody=str_replace(":twisted:","<img src='modules/privmsg/images/smiles/icon_twisted.gif'>",$messagebody);
	  $messagebody=str_replace(":roll:",  "<img src='modules/privmsg/images/smiles/icon_rolleyes.gif'>",$messagebody);
	  $messagebody=str_replace(":wink:",  "<img src='modules/privmsg/images/smiles/icon_wink.gif'>",$messagebody);
	  $messagebody=str_replace(":!:",     "<img src='modules/privmsg/images/smiles/icon_exclaim.gif'>",$messagebody);
	  $messagebody=str_replace(":?:",     "<img src='modules/privmsg/images/smiles/icon_question.gif'>",$messagebody);
	  $messagebody=str_replace(":?",      "<img src='modules/privmsg/images/smiles/icon_confused.gif'>",$messagebody);
	  $messagebody=str_replace(":idea:",  "<img src='modules/privmsg/images/smiles/icon_idea.gif'>",$messagebody);
	  $messagebody=str_replace(":arrow:", "<img src='modules/privmsg/images/smiles/icon_arrow.gif'>",$messagebody);

	}
?>

<form method="post" name="privmsg_list" action="index.php?m=privmsg&a=viewmsg">
  <input type="hidden" name="id" value="<?=$id ?>">
  <table border="0" cellpadding="0" cellspacing="0" background="images/common/back_botones-01.gif">
      <tr>
        <td><table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td width="6"><img src="images/common/inicio_1linea.gif" width="6" height="19"></td>
              <td><img src="images/common/cuadradito_naranja.gif" width="9" height="9">
              <a href="index.php?m=privmsg&a=newmsg&to=<?=$row["sender_id"]?>&msgid=<?=$row["message_id"]?>"><?php echo $AppUI->_('Reply this message'); ?></a><span class="boldtext"> </span></td>
              <td width="6"> <div align="right"><img src="images/common/fin_1linea.gif" width="3" height="19"></div></td>
            </tr>
          </table></td>

      </tr>
    </table>
  <table border="0" cellpadding="1" cellspacing="0" width="100%">

   <tr class="privHeaderCell">
        <td colspan="3">
            <div align="left">&nbsp;<span class="boldblanco">
                <?
                if($row["saved"]=="Y") echo $AppUI->_('Saved')." :: ".$AppUI->_('Message');
                if($row["folder"]=="INBOX"  && $row["saved"]!="Y") echo $AppUI->_('Inbox')." :: ".$AppUI->_('Message');
                if($row["folder"]=="SENTBOX" && $row["saved"]!="Y") echo $AppUI->_('Sentbox')." :: ".$AppUI->_('Message');
                ?>
            </span></div>
        </td>

   </tr>
   <tr>
        <td height="1" class="privLineCell" colspan="3"></td>
   </tr>
   <tr>
	  <td class="row1"><span class=""><?php echo $AppUI->_('From'); ?>:</span></td>
	  <td width="100%" class="row2" colspan="2"><span class="genmed"><?=$row["user_username"]?> (<?=$row["user_first_name"]?> <?=$row["user_last_name"]?>)</span></td>
	</tr>
    <tr>
        <td height="1" class="privLineCell" colspan="3"></td>
   </tr>
	<tr>
	  <td class="row1"><span class="genmed"><?php echo $AppUI->_('To'); ?>:</span></td>
	  <td width="100%" class="row2" colspan="2"><span class="genmed"><?=$row2["user_username"]?> (<?=$row2["user_first_name"]?> <?=$row2["user_last_name"]?>)</span></td>
	</tr>
    <tr>
        <td height="1" class="privLineCell" colspan="3"></td>
   </tr>
	<tr>
	  <td class="row1"><span class="genmed"><?php echo $AppUI->_('Posted'); ?>:</span></td>
	  <td width="100%" class="row2" colspan="2"><span class="genmed"><?=$row["datefmt"]?></span></td>
	</tr>
    <tr>
        <td height="1" class="privLineCell" colspan="3"></td>
   </tr>
	<tr>
	  <td class="row1"><span class="genmed"><?php echo $AppUI->_('Subject'); ?>:</span></td>
	  <td width="100%" class="row2"><span class="genmed"><?=$row["subject"]?></span></td>
	  <td nowrap="nowrap" class="row2" align="right"> <a href="index.php?m=privmsg&a=newmsg&to=<?=$row["sender_id"]?>&quote=yes&msgid=<?=$row["message_id"]?>"><img src="modules/privmsg/images/lang_english/icon_quote.gif" alt="<?php echo $AppUI->_('Quote message'); ?>" border="0"></a> </td>
	</tr>
    <tr>
        <td height="1" class="privLineCell" colspan="3"></td>
   </tr>
	<tr>
	  <td valign="top" colspan="3" class="row2"><span class="postbody"><?=$messagebody?></span></td>
	</tr>
    <tr>
        <td height="1" class="privLineCell" colspan="3"></td>
   </tr>
<!--	<tr>
	  <td width="78%" height="28" valign="bottom" colspan="3" class="row1">
		<table cellspacing="0" cellpadding="0" border="0" height="18">
		  <tr>
			<td valign="middle" nowrap="nowrap"><a href="modules.php?name=Forums&file=profile&mode=viewprofile&amp;u=2&amp;sid=556aedddcab883400b9c116d8b7791f2"><img src="modules/privmsg/images/lang_english/icon_profile.gif" alt="View user's profile" title="View user's profile" border="0" /></a> 
			     </td><td>&nbsp;</td><td valign="top" nowrap="nowrap"><noscript></noscript></td>
		  </tr>
		</table>
	  </td>
	</tr>
-->
	<tr>
	  <td class="privFooterCell" colspan="5" align="right">
		&nbsp;
		<input type="submit" name="deletemsg" value="<?php echo $AppUI->_('Delete Message'); ?>" class="button" />
	  </td>
	</tr>
  </table>

</form>