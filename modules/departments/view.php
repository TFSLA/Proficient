<?php /* DEPARTMENTS $Id: view.php,v 1.4 2009-07-16 17:39:28 nnimis Exp $ */
$dept_id = isset($_GET['dept_id']) ? $_GET['dept_id'] : 0;

// check permissions
$canRead = !getDenyRead( $m, $dept_id );
$canEdit = !getDenyEdit( $m, $dept_id );

if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}


if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'DeptVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'DeptVwTab' ) !== NULL ? $AppUI->getState( 'DeptVwTab' ) : 0;

// pull data
$sql = "
SELECT departments.*,company_name, user_first_name, user_last_name
FROM companies, departments
LEFT JOIN users ON user_id = dept_owner
WHERE dept_id = $dept_id
	AND dept_company = company_id
";
if (!db_loadHash( $sql, $dept )) {
	$titleBlock = new CTitleBlock( 'Invalid Department ID', 'users.gif', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=companies", "companies list" );
	$titleBlock->show();
} else {
	$company_id = $dept['dept_company'];

	// setup the title block
	$titleBlock = new CTitleBlock( $AppUI->_("View Department"), 'users.gif', $m, "$m.$a" );
	
	if ($canEdit) {
		$titleBlock->addCell();
		$titleBlock->addCell(
			'<input type="submit" class="buttonbig" value="'.$AppUI->_('new department').'">', '',
			'<form action="?m=departments&a=addedit&company_id='.$company_id.'&dept_parent='.$dept_id.'" method="post">', '</form>'
		);
	}
	$titleBlock->addCrumb( "?m=companies", "company list" );
	$titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view this company" );
	if ($canEdit) {
		$titleBlock->addCrumb( "?m=departments&a=addedit&dept_id=$dept_id", "edit this department" );

		if ($canDelete) {
			$titleBlock->addCrumbRight(
				'<a href="javascript:delIt()">'
					. '<img align="absmiddle" src="' . dPfindImage( 'trash_small.gif', $m ) . '" alt="" border="0" />&nbsp;'
					. $AppUI->_('delete department') . '</a>'
			);
		}
	}
	$titleBlock->show();
?>
<script language="javascript">
function delIt() {
	if (confirm( "<?php echo $AppUI->_('delDept');?>" )) {
		document.frmDelete.submit();
	}
}
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<form name="frmDelete" action="./index.php?m=departments" method="post">
	<input type="hidden" name="dosql" value="do_dept_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="dept_id" value="<?php echo $dept_id;?>" />
</form>

<tr valign="top">
	<td width="50%">
		<strong><?php echo $AppUI->_("Details");?></strong>
		
		<!--<strong>Details</strong>!-->
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_("Company:");?></td>
			<td bgcolor="#ffffff" width="100%"><?php echo $dept["company_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_("Department:");?></td>
			<td bgcolor="#ffffff" width="100%"><?php echo $dept["dept_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_("Owner:");?></td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$dept["user_first_name"].' '.@$dept["user_last_name"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_("Phone:");?></td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$dept["dept_phone"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap><?php echo $AppUI->_("Fax:");?></td>
			<td bgcolor="#ffffff" width="100%"><?php echo @$dept["dept_fax"];?></td>
		</tr>
		<tr valign=top>
			<td align="right" nowrap><?php echo $AppUI->_("Address:");?></td>
			<td bgcolor="#ffffff"><?php
				echo @$dept["dept_address1"]
					.( ($dept["dept_address2"]) ? '<br />'.$dept["dept_address2"] : '' )
					.'<br />'.$dept["dept_city"]
					.'&nbsp;&nbsp;'.$dept["dept_state"]
					.'&nbsp;&nbsp;'.$dept["dept_zip"]
					;
			?></td>
		</tr>
		</table>
	</td>
	<td width="50%">
		<strong><?php echo $AppUI->_("Description");?></strong>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<td bgcolor="#ffffff" width="100%"><?php echo str_replace( chr(10), "<br />", $dept["dept_desc"]);?>&nbsp;</td>
		</tr>
		</table>
	</td>
</tr>

<tr>
	<td colspan='2'>
		<table cellspacing="1" cellpadding="2" border="0" width="100%">
		<tr>
			<strong><?php echo $AppUI->_("List of Resources");?></strong>
		</tr>
		<tr>
			<td>		
			<table width="100%" cellspacing="1" cellpadding="0" border="0" class="tableForm_bg">
				<tr class="tableHeaderGral">
					<th width="25%" nowrap align="left"><?=$AppUI->_('Name');?></th>
					<th width="18%" nowrap align="left"><?=$AppUI->_('Position');?></th>
					<th width="20%" nowrap align="left"><?=$AppUI->_('Direct report');?></th>
					<th width="16%" nowrap align="left"><?=$AppUI->_('Cell Phone');?></th>
					<th width="20%" nowrap align="left"><?=$AppUI->_('Email');?></th>
				</tr>

<?
		$sql = "SELECT concat( user_first_name,' ', user_last_name ) name, user_job_title, 
				user_supervisor, user_mobile,user_email FROM users 
				WHERE user_department = $dept_id
				AND user_type <> 5
				ORDER BY name";
		$list=db_loadList( $sql );

	  if(!$list)
	  {
			echo"<tr><td align='center' colspan='5' bgcolor='#ffffff'>";  	
	  	echo $AppUI->_('NoUsers');
	  	echo "</td></tr>";
	  }
	  else
	  {
			foreach ($list as $user) 
			{
				$name=$user[name];
				$user_job_title=$user[user_job_title];
				$sql = "SELECT concat(user_first_name,' ', user_last_name) FROM users WHERE user_id = ".$user[user_supervisor];
				$user_supervisor =db_loadResult($sql);
				
				$user_mobile=$user[user_mobile];
				$user_email=$user[user_email];
				echo"			
				<tr>
					<td bgcolor='#ffffff'>$name</td>
					<td bgcolor='#ffffff'>$user_job_title</td>
					<td bgcolor='#ffffff'>$user_supervisor</td>
					<td bgcolor='#ffffff'>$user_mobile</td>
					<td bgcolor='#ffffff'>$user_email</td>
				</tr>
				";
				}
			}?>
			</table>
			</td>
		</tr>				
		
		</table>
	</td>
</tr>



</table>
<?php
	// tabbed information boxes
	$tabBox = new CTabBox( "?m=departments&a=view&dept_id=$dept_id", "{$AppUI->cfg['root_dir']}/modules/departments/", $tab );
}
?>
