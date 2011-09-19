<?php

//Valido que tenga permisos para el modulo
if (getDenyEdit("admin"))
	 $AppUI->redirect( "m=public&a=access_denied" );

require_once("./modules/SecurityCenter/funciones.js"); //Funciones de Java Script
require_once("./modules/SecurityCenter/funciones.php");//Funciones de PHP

$user_id = isset( $_GET['user_id'] ) ? $_GET['user_id'] : 0;

$pgos = array(
	'files' => 'file_name',
	'users' => 'user_username',
	'projects' => 'project_name',
//	'tasks' => 'task_name',
	'companies' => 'company_name',
	'forums' => 'forum_name',
	'calendar' => 'delegado'
);

$pvs = array(
'-1' => $AppUI->_('read-write'),
//'0' => 'deny',
'1' => $AppUI->_('read only')
);

SecurityCenterHeader();
//SecurityCenterTitulo();


$titleBlock = new CTitleBlock( 'Security Center', 'user_management.gif', $m, "$m.$a" );
templates();


?>


		<table border="0" cellpadding="0" cellspacing="0" width="98%" align="center">
			<form name="frm" action="" method="post">
				<input type="hidden" name="dosql" value="do_securitycenter_aed" /> <!--Cuando mando el form ejecuto este archivo !-->
				<input type="hidden" id="calendar_r_txt" name="calendar_r_txt" />
				<input type="hidden" id="calendar_rw_txt" name="calendar_rw_txt" />
				<input type="hidden" id="companies_r_txt" name="companies_r_txt" />
				<input type="hidden" id="companies_rw_txt" name="companies_rw_txt" />
				<input type="hidden" name="redirect" value="<?=$_SERVER['QUERY_STRING'];?>" />
				<td colspan=4>
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td width='25%'  bgcolor="#E6E6E6" >
								&nbsp;<img src="images/common/cuadradito_naranja.gif" height="9" width="9">
								<FONT SIZE='2'color='#FF8000' ><b><?= $AppUI->_('All Modules');?></b></FONT>
							</td>
							<td >&nbsp;</td>
						</tr>
						<tr>
							<td colspan=2 bgcolor="#E6E6E6">
								
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td width='25%'>								
											<?php
												show_permissions('all', '');
											?>
										</td>
									</tr>
								</table>
								
							</td>
						</tr>
					</table>
					
					<br><br>
					
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td width='25%'  bgcolor="#E6E6E6" >
								&nbsp;<img src="images/common/cuadradito_naranja.gif" height="9" width="9">
								<FONT SIZE='2'color='#FF8000' ><b><?= $AppUI->_('Projects Management');?></b></FONT>
							</td>
							<td >&nbsp;</td>
						</tr>
						<tr>
							<td colspan=2 bgcolor="#E6E6E6">
								
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td>								
											<?php
												companies();
												proyects();
												show_permissions_two_options('reports', 'Reports');
												show_permissions_two_options('timexp', 'Time & Expenses');
												show_permissions('todo', 'To-Do');
												show_permissions('webtracking', 'Webtracking');
											?>
										</td>
									</tr>
								</table>
								
							</td>
						</tr>		
						<tr>
							<td bgcolor="#FFFFFF">&nbsp;</td>
						</tr>
						<tr>
							<td bgcolor="#FFFFFF">&nbsp;</td>
						</tr>	
					</table>
					
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td width='25%'  bgcolor="#E6E6E6" >
								&nbsp;<img src="images/common/cuadradito_naranja.gif" height="9" width="9">
								<FONT SIZE='2'color='#FF8000' ><b><?= $AppUI->_('Collaboration');?></b></FONT>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td colspan=2 bgcolor="#E6E6E6">
								
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td>								
											<?php
												show_permissions('calendar', 'Calendar');
												show_permissions('contacts', 'Contacts');
												show_permissions_two_options('delegates', 'Delegates');
												show_permissions('forums', 'Forums');
												show_permissions('articles', 'Articles');
												show_permissions('reviews', 'Reviews');
												show_permissions('wmail', 'Webmail');
											?>
										</td>
									</tr>
								</table>
								
							</td>
						</tr>
						<tr>
							<td bgcolor="#FFFFFF">&nbsp;</td>
						</tr>
						<tr>
							<td bgcolor="#FFFFFF">&nbsp;</td>
						</tr>							
					</table>
					
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td width='25%'  bgcolor="#E6E6E6" >
								&nbsp;<img src="images/common/cuadradito_naranja.gif" height="9" width="9">
								<FONT SIZE='2'color='#FF8000' ><b><?= $AppUI->_('Resources Management');?></b></FONT>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td colspan=2 bgcolor="#E6E6E6">
								
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td width="200">								
											<?php
												show_permissions('files', 'Files');
												show_permissions('pipeline', 'Pipeline');
												show_permissions('hhrr', 'HHRR');
											?>
										</td>
									</tr>
								</table>
								
							</td>
						</tr>
						<tr>
							<td bgcolor="#FFFFFF">&nbsp;</td>
						</tr>
						<tr>
							<td bgcolor="#FFFFFF">&nbsp;</td>
						</tr>							
					</table>				

					
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td width='25%'  bgcolor="#E6E6E6" >
								&nbsp;<img src="images/common/cuadradito_naranja.gif" height="9" width="9">
								<FONT SIZE='2'color='#FF8000' ><b><?= $AppUI->_('Configuration');?></b></FONT>
							</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td colspan=2 bgcolor="#E6E6E6">
								
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
									<tr>
										<td>								
											<?php
												show_permissions_two_options('backup', 'backup');
												show_permissions('emailalerts', 'emailalerts');
												show_permissions_two_options('admin', 'User Admin');
												show_permissions_two_options('system', 'System Admin');	
											?>
										</td>
									</tr>
								</table>
								
							</td>
						</tr>
						<tr>
							<td bgcolor="#FFFFFF">&nbsp;</td>
						</tr>
						<tr>
							<td bgcolor="#FFFFFF">&nbsp;</td>
						</tr>						
					</table>
					
			<tr>
				<td align=right colspan=4>
					<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel');?>" onClick="javascript:if(confirm('<?php echo $AppUI->_('Are you sure you want to go back without saving the changes?');?>')){history.back(-1);}" />
					&nbsp;&nbsp;&nbsp;
					<INPUT TYPE=SUBMIT class="button" value="<?php echo $AppUI->_('submit');?>" onClick="submitFrm();">
				</td>
			</tr>
			</form>	
		</table>
	<?php
	
