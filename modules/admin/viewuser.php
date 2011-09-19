<?php /* ADMIN $Id: viewuser.php,v 1.2 2009-05-22 00:20:19 ctobares Exp $ */

$AppUI->savePlace();


// load the im types
$IMtypes = dPgetSysVal( 'IMType' );

$user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : 0;

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'UserVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'UserVwTab' ) !== NULL ? $AppUI->getState( 'UserVwTab' ) : 0;

// pull data
$sql = "
SELECT users.*,
	company_id, company_name,
	dept_name, dept_id
FROM users
LEFT JOIN companies ON user_company = companies.company_id
LEFT JOIN departments ON dept_id = user_department
WHERE user_id = $user_id
";
//echo "<br>$sql<br>";
if (!db_loadHash( $sql, $user )) {
	$titleBlock = new CTitleBlock( 'Invalid User ID', 'user_management.jpg', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=projects", "projects list" );
	$titleBlock->show();
} else {

// setup the title block
	$titleBlock = new CTitleBlock( 'View User', 'user_management.gif', $m, "$m.$a" );

	if ( $canEdit AND edit_admin($user_id) )
		$titleBlock->addCrumb( "?m=SecurityCenter&user_id=$user_id", 'Security Center' );

	if( $AppUI->user_type == '1' OR $user_id==$AppUI->user_id OR ($canEdit AND edit_admin($user_id)))
		$titleBlock->addCrumb( "?m=system&a=addeditpref&user_id=$user_id", "edit preferences" );
	
	if ( ($canEdit AND edit_admin($user_id)) OR $user_id==$AppUI->user_id ) {
		$titleBlock->addCrumb( "?m=admin&a=addedituser&user_id=".$user_id, "edit personal information" );
		
		$canReadHHRR = !getDenyRead("hhrr") || $user_id == $AppUI->user_id;
		
		if ($canReadHHRR)
			$titleBlock->addCrumb( "?m=hhrr&a=addedit&tab=1&id=".$user_id, "edit hhrr information" );
		
		$titleBlock->addCrumb( "?m=admin&a=calendars&user_id=".$user_id, "work calendar" );
		
		if($user_id == $AppUI->user_id)
			$titleBlock->addCrumb( "javascript: popChgPwd();", "change password" );
	}
	if ($canEdit){
		$titleBlock->addCrumb( "?m=admin&a=addedituser", 'add user' );
	}
	$titleBlock->show();
?>
<script language="javascript">
function popChgPwd() {
	window.open( './index.php?m=public&a=chpwd&dialog=1&suppressLogo=1', 'chpwd', 'top=250,left=250,width=350, height=220, scollbars=false' );
}
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">
<tr valign="top">
	<td width="50%">
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Login Name');?>:</td>
			<td class="hilite" width="100%"><?php echo $user["user_username"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('User Type');?>:</td>
			<td class="hilite" width="100%"><?php echo $utypes[$user["user_type"]];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Real Name');?>:</td>
			<td class="hilite" width="100%"><?php echo $user["user_first_name"].' '.$user["user_last_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Company');?>:</td>
			<td class="hilite" width="100%">
				<a href="?m=companies&a=view&company_id=<?php echo @$user["company_id"];?>"><?php echo @$user["company_name"];?></a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Department');?>:</td>
			<td class="hilite" width="100%">
				<a href="?m=departments&a=view&dept_id=<?php echo @$user["dept_id"];?>"><?php echo $user["dept_name"];?></a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Phone');?>:</td>
			<td class="hilite" width="100%"><?php echo @$user["user_phone"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Home Phone');?>:</td>
			<td class="hilite" width="100%"><?php echo @$user["user_home_phone"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Mobile');?>:</td>
			<td class="hilite" width="100%"><?php echo @$user["user_mobile"];?></td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap><?php echo $AppUI->_('Address');?>:</td>
			<td class="hilite" width="100%"><?php
				echo @$user["user_address1"]
					.( ($user["user_address2"]) ? '<br />'.$user["user_address2"] : '' )
					.'<br />'.$user["user_city"]
					.'&nbsp;&nbsp;'.$user["user_zip"]
					.'<br />'.CUser::getUserState($user["user_id"], $user["user_country_id"], $user["user_state_id"])
					.'&nbsp;&nbsp;'.CUser::getUserCountry($user["user_id"], $user["user_country_id"])
					;
			?></td>
		</tr>
		</table>

	</td>
	<td width="50%">
		<table width="100%">
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Birthday');?>:</td>
			<? 
			     if ($user["user_birthday"]!= ""){
			     $user_birthday =  new Date($user["user_birthday"]); 
			     $u_birthday = $user_birthday ->format($AppUI->user_prefs['SHDATEFORMAT']);
			     }
			?>
			<td class="hilite" width="300"><?php echo $u_birthday; ?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('IM Type');?>:</td>
			<td class="hilite" width="300"><?=$IMtypes[@$user["user_im_type"]];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('IM Id');?>:</td>
			<td class="hilite" width="300"><?=@$user["user_im_id"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_('Email');?>:</td>
			<td class="hilite" width="300"><?php echo '<a href="mailto:'.@$user["user_email"].'">'.@$user["user_email"].'</a>';?></td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('SMTP Server');?>:</td>
			<td class="hilite" width="300"><?php echo @$user["user_smtp"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Mail Protocol');?>:</td>
			<td class="hilite" width="300">
	      <?php
	        if(@$user["user_mail_server_port"]==110) echo "POP3";
	        else echo "IMAP";
	      ?>
      </td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('POP3 Server');?>:</td>
			<td class="hilite" width="300"><?php echo @$user["user_pop3"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('IMAP Server');?>:</td>
			<td class="hilite" width="300"><?php echo @$user["user_imap"];?></td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Email user name');?>:</td>
			<td class="hilite" width="300"><?php echo @$user["user_email_user"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Webmail autologin');?>:</td>
			<td class="hilite" width="300"><?php echo @$user["user_webmail_autologin"];?></td>
		</tr>

		</table>
	</td>
</tr>

<tr>
	<td colspan="2">
		<table width="100%">
			<tr>
				<td align="right" nowrap="nowrap" width="93">
					<?php echo $AppUI->_('Signature');?>:
				</td>
				<td class="hilite">
					<?php echo str_replace( chr(10), "<br />", $user["user_signature"]);?>&nbsp;
				</td>
			</tr>
		</table>
	</td>
</tr>

</table>

<?php
	// tabbed information boxes
	//Las solapas las muestro solamente si un usuario comun edita otros usuarios comunes, o si es un usuario admin siempre las muestro
	if ( edit_admin($user_id) )
	{
		$tabBox = new CTabBox( "?m=admin&a=viewuser&user_id=$user_id", "{$AppUI->cfg['root_dir']}/modules/admin/", $tab );
		$tabBox->add( 'vw_usr_proj', 'Project Security' );
		$tabBox->add( 'vw_usr_perms', 'General Permissions' );
		$tabBox->add( 'vw_task_perms', 'Advanced User Security' );
		//tabBox->add( 'vw_proj_roles', 'Project Roles' );
		//$tabBox->add( 'vw_usr_roles', 'Roles' );	// under development
		$tabBox->show();
}
}


?>