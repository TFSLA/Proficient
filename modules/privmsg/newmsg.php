<?
if($to!="" || $quote!=""){
      $resultu  = mysql_query("SELECT  * FROM users  WHERE user_id = '$to' ;");
      $rowu     = mysql_fetch_array($resultu, MYSQL_ASSOC);
      $username = $rowu["user_username"];
      $resultm  = mysql_query("SELECT  * FROM messages  WHERE message_id = '$msgid' ;");
      $rowm     = mysql_fetch_array($resultm, MYSQL_ASSOC);
      if(substr($rowm["subject"],0,3)=="Re:")
        $subject  = $rowm["subject"];
      else
        $subject  = "Re: ".$rowm["subject"];
}
if($quote!=""){
      $message=str_replace("\n","\n> ",">".$rowm["body"]);
}
if($post){
    if($username=="")      $msg=$AppUI->_("You must specify the recipient's username").".";
    else if($subject=="")  $msg=$AppUI->_("Can't send a message with an empty subject").".";
    else if($message=="")  $msg=$AppUI->_("Can't send an empty message").".";
    else{
      $resultu  = mysql_query("SELECT  * FROM users  WHERE user_username = '$username' ;");
      if(mysql_num_rows($resultu)>0){
        $rowu     = mysql_fetch_array($resultu, MYSQL_ASSOC);
        $recipient_id = $rowu["user_id"];
        if($disable_smilies=="Y") $smileys="N"; else $smileys="Y";
        $result  = mysql_query("INSERT INTO messages (`message_id` , `sender_id` , `recipient_id` , `subject` , `body` , `date` , `smileys` , `folder` , `isread` , `fileattach` , `saved` ) VALUES ('' , {$AppUI->user_id} , '$recipient_id' , '$subject' , '$message' , NOW() , '$smileys' , 'INBOX' , 'N' , '' , 'N' );");
        $result  = mysql_query("INSERT INTO messages (`message_id` , `sender_id` , `recipient_id` , `subject` , `body` , `date` , `smileys` , `folder` , `isread` , `fileattach` , `saved` ) VALUES ('' , {$AppUI->user_id} , '$recipient_id' , '$subject' , '$message' , NOW() , '$smileys' , 'SENTBOX' , 'N' , '' , 'N' );");
	$msgsent="yes";
      }
      else $msg=$AppUI->_("Sorry, but no such user exists").".";
    }
}
if($sentto!=""){
    $username=$sentto;
}
?>



<script language="Javascript" type="text/javascript">
function emoticon(text) {
	text = ' ' + text + ' ';
	if (document.post.message.createTextRange && document.post.message.caretPos) {
		var caretPos = document.post.message.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		document.post.message.focus();
	} else {
	document.post.message.value  += text;
	document.post.message.focus();
	}
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
function SubmitIt(){
    var f=document.post;
    var berr=false;
    if(trim(f.username.value).length == 0){
        alert('<?php echo $AppUI->_('Please enter a valid Username'); ?>');
        berr=true;
    }else if(trim(f.subject.value).length == 0){
        alert('<?php echo $AppUI->_('Please enter a valid Subject'); ?>');
        berr=true;
    }else if(trim(f.message.value).length == 0){
        alert('<?php echo $AppUI->_('Please enter a valid Message'); ?>');
        berr=true;
    }
    if(!berr){
        return true;
    }
    return false;

}
</script>

<style type="text/css">
<!--
/* Main table cell colours and backgrounds */
td.row1	{ background-color: #E9E9E9; }
td.row2	{ background-color: #E9E9E9; }
td.row3	{ background-color: #D1D7DC; }
.privRow1 { background-color: #E9E9E9; }
.privRow2 { background-color: #E9E9E9; }
.privRow3 { background-color: #D1D7DC; }
.privHeaderCell { background-color: #333333; }
.privLineCell   { background-color: #E9E9E9; }
.privFooterCell{ background-color: #E9E9E9; }
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


/* The buttons used for bbCode styling in message post */
input.button {
	background-color : #EFEFEF;
	color : ;
	font-size: 11px; font-family: Verdana, Arial, Helvetica, sans-serif;
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
?>


<?
if($msgsent=="yes"||$msg!=""){
?>
<br><table  width="100%" cellspacing="0" cellpadding="1" border="0">
	<tr>
		<td class="privHeaderCell">&nbsp;<span class="boldblanco"><?=$AppUI->_('Information')?></span></td>
	</tr>
	<tr>
		<td class="row1"><table width="100%" cellspacing="0" cellpadding="1" border="0">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td align="center">
<?
if($msgsent=="yes") echo '<span class="gen">'.$AppUI->_('Your message has been sent').'<br /><br />Click <a href="index.php?m=privmsg">'.$AppUI->_('Here').'</a>  '.$AppUI->_('to return to your Inbox').'<br /><br />';
else echo '<span class="gen">'.$msg.'<br /><br />';
?>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
		</table></td>
	</tr>
</table>
<?
}
if($msgsent!="yes"){
?>



<form action="index.php?m=privmsg&a=newmsg" method="post" name="post" onsubmit="return SubmitIt(this)">
<table border="0" cellpadding="1" cellspacing="0" width="100%">
    <tr class="privHeaderCell">
		<td colspan="2" height="25">&nbsp;<span class="boldblanco"><?=$AppUI->_('Send a new private message')?></span></td>
	</tr>
    <tr>
        <td height="1" bgcolor="#666666" colspan="3"></td>
   </tr>
	<tr>
		<td class="row1"><span class="gen"><b><?=$AppUI->_('Username')?></b></span></td>
		<td class="row2"><span class="genmed"><input type="text"  class="post" name="username" maxlength="25" size="25" tabindex="1" value="<?=$username?>" />&nbsp;<!--<input type="submit" name="usersubmit" value="Find a username" class="liteoption" onClick="window.open('index.php?m=privmsg?a=searchuser', 'win', 'HEIGHT=250,resizable=yes,WIDTH=400');return false;" />--></span></td>
	</tr>
	<tr>
	  <td class="row1" width="22%"><span class="gen"><b><?=$AppUI->_('Subject')?></b></span></td>
	  <td class="row2" width="78%"> <span class="gen">
		<input type="text" name="subject" size="45" maxlength="60" style="width:450px" tabindex="2" class="post" value="<?=$subject?>" />
		</span> </td>
	</tr>
	<tr>
	  <td class="row1" valign="top">
		<table width="100%" border="0" cellspacing="0" cellpadding="1">
		  <tr>
			<td><span class="gen"><b><?=$AppUI->_('Message body')?></b></span> </td>
		  </tr>
		  <tr>
			<td valign="middle" align="center"> <br />
			  <table width="100" border="0" cellspacing="0" cellpadding="5">
				<tr align="center">
				  <td colspan="4" class="gensmall"><b><?=$AppUI->_('Emoticons')?></b></td>
				</tr>
				<tr align="center" valign="middle">
				  <td><a href="javascript:emoticon(':D')"><img src="modules/privmsg/images/smiles/icon_biggrin.gif" border="0" alt="Very Happy" title="Very Happy" /></a></td>
				  <td><a href="javascript:emoticon(':)')"><img src="modules/privmsg/images/smiles/icon_smile.gif" border="0" alt="Smile" title="Smile" /></a></td>
				  <td><a href="javascript:emoticon(':(')"><img src="modules/privmsg/images/smiles/icon_sad.gif" border="0" alt="Sad" title="Sad" /></a></td>
				  <td><a href="javascript:emoticon(':o')"><img src="modules/privmsg/images/smiles/icon_surprised.gif" border="0" alt="Surprised" title="Surprised" /></a></td>
				</tr>
				<tr align="center" valign="middle">
				  <td><a href="javascript:emoticon('8O')"><img src="modules/privmsg/images/smiles/icon_eek.gif" border="0" alt="Shocked" title="Shocked" /></a></td>
				  <td><a href="javascript:emoticon(':?')"><img src="modules/privmsg/images/smiles/icon_confused.gif" border="0" alt="Confused" title="Confused" /></a></td>
				  <td><a href="javascript:emoticon('8)')"><img src="modules/privmsg/images/smiles/icon_cool.gif" border="0" alt="Cool" title="Cool" /></a></td>
				  <td><a href="javascript:emoticon(':lol:')"><img src="modules/privmsg/images/smiles/icon_lol.gif" border="0" alt="Laughing" title="Laughing" /></a></td>
				</tr>
				<tr align="center" valign="middle">
				  <td><a href="javascript:emoticon(':x')"><img src="modules/privmsg/images/smiles/icon_mad.gif" border="0" alt="Mad" title="Mad" /></a></td>
				  <td><a href="javascript:emoticon(':P')"><img src="modules/privmsg/images/smiles/icon_razz.gif" border="0" alt="Razz" title="Razz" /></a></td>
				  <td><a href="javascript:emoticon(':oops:')"><img src="modules/privmsg/images/smiles/icon_redface.gif" border="0" alt="Embarassed" title="Embarassed" /></a></td>
				  <td><a href="javascript:emoticon(':cry:')"><img src="modules/privmsg/images/smiles/icon_cry.gif" border="0" alt="Crying or Very sad" title="Crying or Very sad" /></a></td>
				</tr>
				<tr align="center" valign="middle">
				  <td><a href="javascript:emoticon(':evil:')"><img src="modules/privmsg/images/smiles/icon_evil.gif" border="0" alt="Evil or Very Mad" title="Evil or Very Mad" /></a></td>
				  <td><a href="javascript:emoticon(':twisted:')"><img src="modules/privmsg/images/smiles/icon_twisted.gif" border="0" alt="Twisted Evil" title="Twisted Evil" /></a></td>
				  <td><a href="javascript:emoticon(':roll:')"><img src="modules/privmsg/images/smiles/icon_rolleyes.gif" border="0" alt="Rolling Eyes" title="Rolling Eyes" /></a></td>
				  <td><a href="javascript:emoticon(':wink:')"><img src="modules/privmsg/images/smiles/icon_wink.gif" border="0" alt="Wink" title="Wink" /></a></td>
				</tr>
				<tr align="center" valign="middle">
				  <td><a href="javascript:emoticon(':!:')"><img src="modules/privmsg/images/smiles/icon_exclaim.gif" border="0" alt="Exclamation" title="Exclamation" /></a></td>
				  <td><a href="javascript:emoticon(':?:')"><img src="modules/privmsg/images/smiles/icon_question.gif" border="0" alt="Question" title="Question" /></a></td>
				  <td><a href="javascript:emoticon(':idea:')"><img src="modules/privmsg/images/smiles/icon_idea.gif" border="0" alt="Idea" title="Idea" /></a></td>
				  <td><a href="javascript:emoticon(':arrow:')"><img src="modules/privmsg/images/smiles/icon_arrow.gif" border="0" alt="Arrow" title="Arrow" /></a></td>
				</tr>
			  </table>
			</td>
		  </tr>
		</table>
	  </td>
	  <td class="row2" valign="top"><span class="gen"> <span class="genmed"> </span>
		<table width="450" border="0" cellspacing="0" cellpadding="2">
		  <tr>
			<td colspan="9"><span class="gen">
			  <textarea name="message" rows="15" cols="35" wrap="virtual" style="width:450px" tabindex="3" class="post" ><?=$message?></textarea>
			  </span></td>
		  </tr>
		</table>
		</span></td>
	</tr>
	<tr>
	  <td class="row1" valign="top"><span class="gen"><b><?=$AppUI->_('Options')?></b></span><br /><span class="gensmall"><?=$AppUI->_('Smilies are')?> <u><?=$AppUI->_('ON')?></u></span></td>
	  <td class="row2"><span class="gen"> </span>
		<table cellspacing="0" cellpadding="1" border="0">
		  <tr>
			<td>
			  <input type="checkbox" name="disable_smilies" value="Y" />
			</td>
			<td><span class="gen"><?=$AppUI->_('Disable Smilies in this message')?></span></td>
		  </tr>
		</table>
	  </td>
	</tr>
	
	<tr>
	  <td class="privFooterCell" colspan="2" align="center" > <input type="hidden" name="folder" value="inbox" /><input type="hidden" name="mode" value="post" /><input type="submit" accesskey="s" tabindex="6" name="post" class="button" value="<?=$AppUI->_('Send private message')?>" /></td>
	</tr>

  </table>
</form>
<?}?>
