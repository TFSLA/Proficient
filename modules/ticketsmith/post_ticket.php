<?php /* TICKETSMITH $Id: post_ticket.php,v 1.1 2009-05-19 21:15:46 pkerestezachi Exp $ */
##
##	Ticketsmith Post Ticket
##

// setup the title block
$titleBlock = new CTitleBlock( 'Submit Trouble Ticket', 'tickets.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=ticketsmith", "tickets list" );
$titleBlock->show();

?>

<SCRIPT language="javascript">
function submitIt() {
	var f = document.ticket;
	var msg = '';
	if (trim(f.name.value).length < 3) {
		msg += "\n- <? echo $AppUI->_('a valid name')?>";
	}
	if ( !isEmail(trim(f.email.value)) ) {
		msg += "\n- <? echo $AppUI->_('a valid email')?>";
	}
	if (trim(f.subject.value).length < 3) {
		msg += "\n- <? echo $AppUI->_('a valid subject')?>";
	}
	if (trim(f.description.value).length < 3) {
		msg += "\n- <? echo $AppUI->_('a valid description')?>";
	}
	
	if (msg.length < 1) {
		f.submit();
	} else {
		alert( "<? echo $AppUI->_('Please provide the following detail before submitting')?>:\n" + msg );
	}
}
</script>

<TABLE width="100%" border=0 cellpadding="0" cellspacing="0" class="">
<form name="ticket" action="?m=ticketsmith" method="post">
<input type="hidden" name="dosql" value="do_ticket_aed">

<TR height="20">
	<Th colspan=2>
        <table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_04.gif">
            <tr>
                <td align="left"><img src="images/common/lado.gif" width="1" height="17"></td>
                <td><? echo $AppUI->_('Trouble Details')?></td>
                <td align="right"><img src="images/common/lado.gif" width="1" height="17"></td>
            </tr>
            <tr bgcolor="#666666">
                <td colspan="3"></td>
            </tr>
        </table>
    </th>
</tr>
<tr class="tableForm_bg">
	<TD align="right"><? echo $AppUI->_('Name')?>:</td>
	<TD><input type="text" class="text" name="name" value="<?php echo @$crow["name"];?>" size=50 maxlength="255"> <span class="smallNorm">(<? echo $AppUI->_('required')?>)</span></td>
</tr>
<tr class="tableForm_bg">
	<TD align="right"><? echo $AppUI->_('Email')?>:</td>
	<TD><input type="text" class="text" name="email" value="" size=50 maxlength="50"> <span class="smallNorm">(<? echo $AppUI->_('required')?>)</span></td>
</tr>
<tr class="tableForm_bg">
	<TD align="right"><? echo $AppUI->_('Subject')?>:</td>
	<TD><input type="text" class="text" name="subject" value="" size=50 maxlength="50"> <span class="smallNorm">(<? echo $AppUI->_('required')?>)</span></td>
</tr>
<tr class="tableForm_bg">
	<TD align="right"><? echo $AppUI->_('Priority')?>:</td>
	<TD>
		<select name="priority" class="text">
			<option value="0"><? echo $AppUI->_('Low')?>
			<option value="1" selected><? echo $AppUI->_('Normal')?>
			<option value="2"><? echo $AppUI->_('High')?>
			<option value="3"><? echo $AppUI->_('Highest')?>
			<option value="4"><strong><? echo $AppUI->_('911 (Showstopper)')?></strong>
		</select>
	</td>
</tr>
<TR class="tableForm_bg">
	<TD align="right"><? echo $AppUI->_('Description of Problem')?>: </td>
	<td><span class="smallNorm">(<? echo $AppUI->_('required')?>)</span></td>
</tr>
<TR class="tableForm_bg">
	<TD colspan=2 align="center">
		<textarea cols="70" rows="10" class="textarea" name="description"><?php echo @$crow["description"];?></textarea>
	</td>
</tr>
<TR class="tableForm_bg">
	<TD><input type="button" value="<? echo $AppUI->_('back')?>" class="button" onClick="javascript:history.back(-1);"></td>
	<TD align="right"><input type="button" value="<? echo $AppUI->_('submit')?>" class="button" onClick="submitIt()"></td>
</tr>
</form>
</TABLE>
&nbsp;<br />&nbsp;<br />&nbsp;
