<link rel="stylesheet" type="text/css" href="./style/silver/main.css" media="all" />

<script language="javascript">
function popUp2(URL) 
{
	day = new Date();
	id = day.getTime();
	eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0 ,scrollbars=1, location=0, statusbar=0, menubar=0, resizable=1, width=1000, height=600');");
}
</script>

<?
if ( isset($user_id) )
{
	
		$sql= "SELECT user_first_name, user_last_name FROM users where user_id = $user_id;";
		$rc=mysql_query($sql);
		$vec=mysql_fetch_array($rc);
		$user_first_name = $vec['user_first_name'];
		$user_last_name = $vec['user_last_name'];
}
else
{
	$user_id = $AppUI->user_id;
	$user_first_name = $AppUI->user_first_name;
	$user_last_name = $AppUI->user_last_name;
}

$tiposPermiso = array( "-1"=>$AppUI->_('Read Write'), "0"=>$AppUI->_('Denied'), "1"=>$AppUI->_('Read Only') );

	?>
<table width="100%" align='center' border=0 cellpadding="0" cellspacing="0" bgcolor="#e6e6e6">
<tr>
	<td>
		<table width="95%" align='center' border=0 cellpadding="2" cellspacing="1" class="">
		<tr height="20" class="tableHeaderGral">
			<th colspan="2"><?php echo $AppUI->_('User Permissions Module HHRR');?>: <?php echo "$user_first_name $user_last_name";?></th>
		</tr>
		<tr>
			<td>
				<table width="100%" border="0" cellpadding="1" cellspacing="1">			
					<tr class="tableHeaderGral">
						<th>
							<?=$AppUI->_("Company")?>
						</th>
						<th>
							<?=$AppUI->_('Department')?>
						</th>
						<th>
							<?=$AppUI->_('Personal data')?>
						</th>						
						<th>
							<?=$AppUI->_('Matrix')?>
						</th>
						<th>
							<?=$AppUI->_('Work Experience')?>
						</th>
						<th>
							<?=$AppUI->_('Education')?>
						</th>				
						<th>
							<?=$AppUI->_('Performance Management')?>
						</th>
						<th>
							<?=$AppUI->_('compensations')?>
						</th>
						<th>
							<?=$AppUI->_('Development')?>
						</th>												
					</tr>
					<?
						$query="SELECT id, company, company_name, department, dept_name,personal, matrix, work_experience, education, performance_management, compensations, development FROM hhrr_permissions
										LEFT JOIN companies ON hhrr_permissions.company=companies.company_id
										LEFT JOIN departments ON hhrr_permissions.department=departments.dept_id
										WHERE id_user = $user_id;
										";
			  		$sql = mysql_query($query);
			
						if ( !mysql_num_rows($sql))
							echo "<tr><td colspan=\"3\">".$AppUI->_("No data available")."</td></tr>";
			
			 			while ($vec = mysql_fetch_array($sql)){
					?>			
					<tr>
						<td>
							<?=($vec['company_name'] != NULL) ? $vec['company_name'] : $AppUI->_('All');?>
						</td>
						<td>
							<?=($vec['dept_name'] != NULL) ? $vec['dept_name'] : $AppUI->_('All');?>
						</td>
						<td>
							<?= $tiposPermiso[$vec['personal']];?>
						</td>						
						<td>
							<?= $tiposPermiso[$vec['matrix']];?>
						</td>
						<td>
							<?= $tiposPermiso[$vec['work_experience']];?>
						</td>
						<td>
							<?= $tiposPermiso[$vec['education']];?>
						</td>
						<td>
							<?= $tiposPermiso[$vec['performance_management']];?>
						</td>
						<td>
							<?= $tiposPermiso[$vec['compensations']];?>
						</td>			
						<td>
							<?= $tiposPermiso[$vec['development']];?>
						</td>		
		
					</tr>
					<?
					}
					?>
				</table>
			</td>
		</tr>
		<tr>
			<td align='center'>
				<?= "<a href=\"javascript:popUp2('index.php?m=hhrr&a=hhrr_permissions&user_id=$user_id&dialog=1&suppressLogo=1')\"> <b> " . $AppUI->_('Modify HHRR security') ."</b></a><br><br>";?>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>