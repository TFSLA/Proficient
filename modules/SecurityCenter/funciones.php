<?php
/*$sql = "
SELECT u.user_id, u.user_username,
	p.permission_item, p.permission_id, p.permission_grant_on, p.permission_value,
	c.company_id, c.company_name,
	pj.project_id, pj.project_name,
	t.task_id, t.task_name,
	f.file_id, f.file_name,
	fm.forum_id, fm.forum_name,
	u2.user_id, u2.user_username,
	u3.user_id, concat(u3.user_first_name,' ',u3.user_last_name) delegado
FROM users u, permissions p
LEFT JOIN companies c ON c.company_id = p.permission_item and p.permission_grant_on = 'companies'
LEFT JOIN projects pj ON pj.project_id = p.permission_item and p.permission_grant_on = 'projects'
LEFT JOIN tasks t ON t.task_id = p.permission_item and p.permission_grant_on = 'tasks'
LEFT JOIN files f ON f.file_id = p.permission_item and p.permission_grant_on = 'files'
LEFT JOIN users u2 ON u2.user_id = p.permission_item and p.permission_grant_on = 'users'
LEFT JOIN forums fm ON fm.forum_id = p.permission_item and p.permission_grant_on = 'forums'
LEFT JOIN users u3 ON u3.user_id = p.permission_item and p.permission_grant_on = 'calendar'
WHERE u.user_id = p.permission_user
	AND u.user_id = $user_id
";
*/
	function getAllCompanies()
	{
		$sql = "SELECT company_id, company_name FROM companies;";
		return  db_loadHashList($sql);		
	}

	function getAllUsers()
	{
		$sql = "SELECT user_id,CONCAT_WS(' ',user_first_name,user_last_name) FROM users WHERE user_type <> 5 ORDER BY user_first_name";
		return  db_loadHashList($sql);		
	}	
	
	function updateCompaniSecurity( $companies_r_txt, $companies_rw_txt) 
	{
		global $AppUI, $user_id;
		$val_ret = TRUE;
	// Borro todas las entradas del usuario actual
		$sql = "DELETE FROM permissions WHERE permission_user = $user_id AND permission_grant_on = 'companies'";
		//echo "<br>" .$sql;
		$val_ret = ($val_ret AND db_exec( $sql ));

	// Inserto los nuevos permisos
		$tarr = explode( ",", $companies_r_txt );
		foreach ($tarr as $company_id) {
			if (intval( $company_id ) != 0) {
				$sql = "REPLACE INTO permissions (permission_user, permission_grant_on, permission_item, permission_value) 
																	VALUES ( $user_id, 'companies', $company_id, 1)";
				//echo "<br>" .$sql;
				$val_ret = ($val_ret AND db_exec( $sql ));
			}
		}
		
		$tarr = explode( ",", $companies_rw_txt );
		foreach ($tarr as $company_id) {
			if (intval( $company_id ) != 0) {
				$sql = "REPLACE INTO permissions (permission_user, permission_grant_on, permission_item, permission_value) 
																	VALUES ( $user_id, 'companies', $company_id, -1)";
				//echo "<br>" .$sql;
				$val_ret = ($val_ret AND db_exec( $sql ));
			}
		}
		
		return $val_ret;
	
	}
		
	function updateSecurity($valor, $modulo)
	{
		global $AppUI, $user_id;
		if ($valor == 0)
			$sql = "DELETE FROM permissions WHERE permission_user = $user_id AND permission_grant_on = '$modulo'";
		else
			$sql = "REPLACE INTO permissions (permission_user, permission_grant_on, permission_value, permission_item) 
																VALUES ( $user_id, '$modulo', $valor, -1)";

		//Fix for recursive grant security
		switch($modulo)
		{
			case "projects":
				updateSecurity($valor, 'myassigments');
				break;
		}
		//echo "<br>" .$sql;
		return db_exec( $sql );
	}
	
		
	function updateCalendarSecurity( $calendar_r_txt, $calendar_rw_txt) 
	{
		global $AppUI, $user_id;
		$val_ret = TRUE;
	// Borro todas las entradas del usuario actual
		$sql = "DELETE FROM permissions WHERE permission_user = $user_id AND permission_grant_on = 'calendar'";
		//echo "<br>" .$sql;
		$val_ret = ($val_ret AND db_exec( $sql ));

	// Inserto los nuevos permisos
		$tarr = explode( ",", $calendar_r_txt );
		foreach ($tarr as $company_id) {
			if (intval( $company_id ) != 0) {
				$sql = "REPLACE INTO permissions (permission_user, permission_grant_on, permission_item, permission_value) 
																	VALUES ( $user_id, 'calendar', $company_id, 1)";
				//echo "<br>" .$sql;
				$val_ret = ($val_ret AND db_exec( $sql ));
			}
		}
		
		$tarr = explode( ",", $calendar_rw_txt );
		foreach ($tarr as $company_id) {
			if (intval( $company_id ) != 0) {
				$sql = "REPLACE INTO permissions (permission_user, permission_grant_on, permission_item, permission_value) 
																	VALUES ( $user_id, 'calendar', $company_id, -1)";
				//echo "<br>" .$sql;
				$val_ret = ($val_ret AND db_exec( $sql ));
			}
		}
		
		return $val_ret;
	
	}
	

function SecurityCenterHeader(){
	global $user_id, $AppUI;
	
	$sql= "SELECT user_first_name, user_last_name FROM users where user_id = $user_id;";
	$rc=mysql_query($sql);
	$vec=mysql_fetch_array($rc);
	$user_first_name = $vec['user_first_name'];
	$user_last_name = $vec['user_last_name'];
	
	?>

	<table class="tableTitle" background="images/common/back_title_section.gif" border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody>
	<tr>
		<td valign="bottom" width="6">
			<img src="images/common/inicio_title_section.gif" height="34" width="6">
		</td>
		<td width="38">
					<img src="./modules/admin/images/user_management.gif" alt="" border="0" height="29" width="29">
				</td>
				<td class="titularmain2"><?=$AppUI->_('Security Center')?> - <? echo "$user_last_name, $user_first_name"; ?></td>
				<td align="right" nowrap="nowrap">&nbsp;</td>
				<!--
				<td align="right" width="30">
					<a href="#todo.index" onclick="javascript:window.open('?m=help&dialog=1&suppressLogo=1&hid=todo.index', 'contexthelp', 'width=400, height=400, left=50, top=50, scrollbars=yes, resizable=yes')" title="AYUDA">
					<img src="./images/icons/help_small.gif" alt="AYUDA" border="0" height="16" width="16"></a>
				</td>
				-->
				<td valign="bottom" width="6">
					<div align="right">
						<img src="images/common/fin_title_section.gif" height="34" width="6">
					</div>
				</td>
			</tr>
	</tbody>
	</table>
<?php
}

function proyects()
{
	global $AppUI, $user_id, $pvs, $pgos;
	//Pull User perms
	$sql = "
	SELECT
		p.permission_value,
		pj.project_id, pj.project_name
	FROM users u, permissions p
	LEFT JOIN projects pj ON pj.project_id = p.permission_item and p.permission_grant_on = 'projects'
	WHERE u.user_id = p.permission_user
		AND u.user_id = $user_id
		AND p.permission_grant_on = 'projects'";
	$res = db_exec( $sql );
	
	if ( db_num_rows($res) == 0 )
		$perm_projects = 0; //Denegado
	else
		$perm_projects = db_loadResult( $sql );
	
	?>
	<tr><td>&nbsp;</td></tr>
			<tr>
				<td width="200">
					<b><? echo $AppUI->_('Projects');?></b>
				</td>
				<td>
					<INPUT TYPE=RADIO NAME="projects_radio" VALUE="0" <? echo ($perm_projects == 0) ? "CHECKED" : ""?> ><?echo $AppUI->_('Denied');?>
				</td>
				<td>
					<INPUT TYPE=RADIO NAME="projects_radio" VALUE="1" <? echo ($perm_projects == 1)? "CHECKED" : ""?> ><?echo $AppUI->_('View');?>
				</td>
				<td>
					<INPUT TYPE=RADIO NAME="projects_radio" VALUE="-1" <? echo ($perm_projects == -1)? "CHECKED" : ""?> ><?echo $AppUI->_('View & Create');?>
				</td>
			</tr>
			<td colspan=4>
			<?php
			
				//$AppUI->savePlace();
				
				//include_once("./modules/SecurityCenter/vw_usr_proj.php");
			
				//$tabBox = new CTabBox( "?m=admin&a=viewuser&user_id=$user_id", "{$AppUI->cfg['root_dir']}/modules/admin/", 0 );
				//$tabBox->add( 'vw_task_perms', 'Advanced User Security' );	
				//tabBox->add( 'vw_proj_roles', 'Project Roles' );
				//$tabBox->add( 'vw_usr_roles', 'Roles' );	// under development
				//$tabBox->show();
				
				imprimir_info_proyectos();			
			?>
			</td>
			<tr>
				<td colspan=4 align='center'>
					<a href="javascript:popUp2('index.php?m=admin&a=viewuser_popup&tab=0&user_id=<? echo $user_id; ?>&dialog=1&suppressLogo=1')"> <?php echo "<b>".$AppUI->_('Modify permissions by project'). "</b></a><br><br>"; ?> 
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
		
<?php
}

/**
* Esta funcion imprime a en que proyectos el usuarios es propietario, admio o usuario, pero no tiene ningun formulario, osea que no permite editar, para cambiar los permisos: index.php?m=admin&a=viewuser_popup
* Se sacaron los formulario, por que sino se anidaban con el del modulo prinsipal y se rompia todo
*/
function imprimir_info_proyectos()
{
	global $AppUI, $user_id;
	include_once("./modules/admin/admin.class.php");
?>
	
	<table width="95%" align='center' border=0 cellpadding="2" cellspacing="1" class="">
		<col><col>
		
		<tr class="tableHeaderGral">
			<th colspan="2"><?php echo $AppUI->_('As Owner');?></th>
		</tr>
		<tr class="tableHeaderGral">
			<th><?php echo $AppUI->_('Name');?></th>
			<th><?php echo $AppUI->_('Status');?></th>
		</tr>
		
		<?php 
			$sql = "
			SELECT DISTINCT projects.*
			FROM projects 
			WHERE ( projects.project_owner = $user_id )
				AND project_active <> 0
			ORDER BY project_name
			";
			$projects = db_loadList( $sql );
			
			if (count($projects)==0)
				echo "<tr><td colspan=\"3\">".$AppUI->_("He is not proprietor of any project.")."</td></tr>";
			else	
				foreach ($projects as $row) {	?>
			<tr>
				<td>
					<a href="?m=projects&a=view&project_id=<?php echo $row["project_id"];?>">
						<?php echo $row["project_name"];?>
					</a>
				<td><?php echo $AppUI->_($pstatus[$row["project_status"]]); ?></td>
			</tr>
			<?php } ?>
		<tr class="tableHeaderGral">
			<th colspan="2"><?php echo $AppUI->_('As Administrator');?></th>
		</tr>
		<tr class="tableHeaderGral">
			<th><?php echo $AppUI->_('Name');?></th>
			<th><?php echo $AppUI->_('Status');?></th>
		</tr>
		<?php 
			$sql = "
			SELECT DISTINCT projects.*
			FROM projects INNER JOIN project_owners ON projects.project_id = project_owners.project_id
			WHERE ( project_owners.project_owner = $user_id  )
				AND project_active <> 0
			ORDER BY project_name
			";
			$projects = db_loadList( $sql );
			
			if (count($projects)==0)
				echo "<tr><td colspan=\"3\">".$AppUI->_("He is not administrator of any project.")."</td></tr>";
			else{
				foreach ($projects as $row) {	?>
			<tr>
				<td>
					<a href="?m=projects&a=view&project_id=<?php echo $row["project_id"];?>">
						<?php echo $row["project_name"];?>
					</a>
				<td><?php echo $AppUI->_($pstatus[$row["project_status"]]); ?></td>	
			</tr>
			<?php } 
			}
			?>
			
		<tr class="tableHeaderGral">
			<th colspan="2"><?php echo $AppUI->_('As Project User');?></th>
		</tr>
		<tr class="tableHeaderGral">
			<th><?php echo $AppUI->_('Name');?></th>
			<th><?php echo $AppUI->_('Status');?></th>
		</tr>
		<?php 
			$oprojects = $owned_projects;
			$projects = array();
			$prj_ids = array_keys(cUser::getAssignedProjects($user_id));
			$prj_ids[] = "-1";
			$sql = "
			SELECT DISTINCT projects.*
			FROM projects 
			WHERE projects.project_id in (".implode(", ",$prj_ids).")
				AND project_active <> 0
			ORDER BY project_name
			";
		
			$projects = db_loadList( $sql );
		
			if (count($projects)==0)
				echo "<tr><td colspan=\"3\">".$AppUI->_("He is not user of any project.")."</td></tr>";
			else{
				foreach ($projects as $row) {	?>
			<tr>
				<td>
					<a href="?m=projects&a=view&project_id=<?php echo $row["project_id"];?>">
						<?php echo $row["project_name"];?>
					</a>
				<td><?php echo $AppUI->_($pstatus[$row["project_status"]]); ?></td>
			</tr>
			<?php } 
			}
			?>
			
	</table>
<?
}

function imprimir_info_webtracking()
{
	global $AppUI, $user_id;
	if($AppUI->user_locale=="es")
	{
		$access_levels=array("10"=>"espectador", "25" => "reportero","40" => "actualizador","55" => "desarrollador","70" => "administrador","90" => "administrador del sistema");
		$project_view_state=array("10"=>"publico", "50" => "privado");
	}
	else
	{
		$access_levels=array("10"=>"viewer", "25" => "reporter","40" => "updater","55" => "developer","70" => "manager","90" => "administrator");
		$project_view_state=array("10"=>"public", "50" => "private");
	}
		
?>
	
	<table width="95%" align='center' border=0 cellpadding="2" cellspacing="1" class="">
		<col><col>
		
		<tr class="tableHeaderGral">
			<th colspan="3"><?php echo $AppUI->_('assigned_projects');?></th>
		</tr>
		<tr class="tableHeaderGral">
			<th><?php echo $AppUI->_('Name');?></th>
			<th><?php echo $AppUI->_('access_level');?></th>
			<th><?php echo $AppUI->_('Status');?></th>
		</tr>
		
		<?php 
			$sql = "
			SELECT DISTINCT p.project_id as id, p.project_name as name, p.view_state, u.access_level 
		FROM projects p 
		LEFT JOIN btpsa_project_user_list_table u ON p.project_id=u.project_id 
		WHERE p.enabled=1 AND u.user_id='$user_id'
		ORDER BY p.project_name
			";
			$projects = db_loadList( $sql );
						
			
			if (count($projects)==0)
				echo "<tr><td colspan=\"3\">".$AppUI->_("No data available")."</td></tr>";
			else	
				foreach ($projects as $row) {	?>
			<tr>
				<td>
					<a href="?m=projects&a=view&project_id=<?php echo $row['id'];?>"> <?php echo $row['name']; ?></a>
				</td>
				<td><?php echo $access_levels[$row['access_level']]; ?></td>
				<td><?php echo $project_view_state[$row['view_state']]; ?></td>
			</tr>
			<?php } ?>
	</table>
<?
}
//Modificacion de addeditdeleg.php para que solo muestre los delegados sin poder editarlos
function imprimir_info_delegates()
{
	global $AppUI, $user_id;
	
	if($AppUI->user_locale=="es")
		$tiposPermiso = array( "NONE"=>"ninguno", "REVIEWER"=>"revisor", "AUTHOR"=>"autor", "EDITOR"=>"editor" );
	else
		$tiposPermiso = array( "NONE"=>"NONE", "REVIEWER"=>"REVIEWER", "AUTHOR"=>"AUTHOR", "EDITOR"=>"EDITOR" );



	$sql = "SELECT mod_id, mod_ui_name FROM modules WHERE mod_name IN ( 'Calendar', 'Contacts', 'Pipeline', 'wmail' )";
	$modulos = db_loadHashList( $sql, "mod_id" );	
	?>
	
	<table width="95%" align='center' border=0 cellpadding="2" cellspacing="1" class="">
		<col><col>
		
		<tr class="tableHeaderGral">
			<th colspan="4"><?php echo $AppUI->_('User delegates');?></th>
		</tr>
		<tr class="tableHeaderGral">
			<th><?php echo $AppUI->_('User');?></th>
			
				<?
				foreach ( $modulos as $m )
				{
				?>
				<th>
					<?=$AppUI->_($m["mod_ui_name"]	)?>
				</th>
				<?
				}
				?>
		</tr>
		
		<?php
			
			require_once( $AppUI->getModuleClass( "admin" ) );
			$usr = new CUser();
			$usr->load( $user_id );
			$delegados = $usr->getDelegates();			
			$noDelegados = $usr->getNonDelegates();
			//Tuve que meter la u al principio para que el array_merge no me reviente las claves.
			$nuevosUsuarios = array( "u0" => "-" );
			foreach ( $noDelegados as $nd )
			{
				$und = new CUser();
				$und->load( $nd["user_id"] );
				$nuevoUsuario = array( "u".$und->user_id => $und->user_first_name." ".$und->user_last_name );				
				$nuevosUsuarios = array_merge( $nuevosUsuarios, $nuevoUsuario );				
			}
			
			if (count($delegados)==0)
				echo "<tr><td colspan=\"3\">".$AppUI->_("It does not have delegated modules")."</td></tr>";
			else
			{
				foreach ( $delegados as $d )
				{
					$usr_del = new CUser();
					$usr_del->load( $d["delegate_id"] );
				?>			
				<tr>
					<td>
						<?=$usr_del->user_first_name." ".$usr_del->user_last_name?>					
					</td>
					<?
					foreach ( $modulos as $m )
					{					
						$permiso = $usr->getDelegatePermission( $usr_del->user_id, $m["mod_id"] );
						?>
						<td>
						<? echo $tiposPermiso[$permiso]; ?>
						</td>
						<?
					}
					?>
				</tr>
				<?
				}
			}
?>
	</table>
<?
}

function companies()
{
	global $AppUI, $user_id, $pvs, $pgos;
	//Pull User perms
	$sql = "
	SELECT 
		p.permission_item, p.permission_id, p.permission_grant_on, p.permission_value,
		c.company_id, c.company_name
	FROM users u, permissions p
	LEFT JOIN companies c ON c.company_id = p.permission_item and p.permission_grant_on = 'companies'
	WHERE u.user_id = p.permission_user
		AND u.user_id = $user_id
		AND p.permission_grant_on = 'companies'
	";
	$res = db_exec( $sql );
	
	$perm_companie = db_num_rows($res) > 0;
	?>
	<tr>
		<td>
			<b><? echo $AppUI->_('Company');?></b>
		</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	
	<?php
	//pull the projects into an temp array
	$tarr = array();
	$companies_r = array();
	$companies_rw = array();
	while ($row = db_fetch_assoc( $res )) {
		$item = @$row[@$pgos[$row['permission_grant_on']]];
		if (!$item) {
			$item = $row['permission_item'];
		}
		if ($item == -1) {
			$item = $AppUI->_('All Companies');
		}
		$tarr[] = array_merge( $row, array( 'grant_item'=>$item ) );
		
		if ($row['permission_value'] == 1)
			$companies_r[ isset($row['company_id']) ? $row['company_id'] : -1 ] = $item;
		else
			$companies_rw[ isset($row['company_id']) ? $row['company_id'] : -1] = $item;
	}
	
	//Traigo todas las companias que no estan en $companies_r ni en $companies_rw
	//array array_diff ( array array1, array array2 [, array ...] ) devuelve una matriz que contiene todos los valores de array1 que no aparezcan en ninguna de las otras matrices que se pasan a la funci�n como argumento. Hay que tener en cuenta que las claves se mantienen.
	$todos = array ('-1' => $AppUI->_('All Companies'));
	$allcompanies = array_diff(getAllCompanies() ,$companies_r,$companies_rw);
	
	natcasesort($allcompanies);
	
	if( !(in_array($AppUI->_('All Companies'), $companies_r)) && !(in_array($AppUI->_('All Companies'), $companies_rw)))
		$allcompanies = arrayMerge((array) $todos, (array) $allcompanies);
	//echo "1:<pre>"; print_r($companies_r); echo "</pre>";
	
	$vacio = array();
	?>
	<tr>
		<td colspan=4>
			<table cellspacing="0" cellpadding="2" border="0">
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td><?php echo $AppUI->_( 'View' );?></td>
					<td>&nbsp;</td>
					<td><?php echo $AppUI->_( 'View & Create' );?></td>
				</tr>
				<tr>
					<td>    
                       <?php echo arraySelect( $allcompanies, 'allcompanies', 'id="allcompanies" style="width:180px" size="8" style="font-size:9pt;" multiple="multiple"', null, FALSE, FALSE ); ?>	
					</td>
					<td align="right">
						<input type="button" class="button" value=">" onClick="cambiarDeSelect(document.getElementById('allcompanies'), document.getElementById('companies_r'))" />
						<input type="button" class="button" value="<" onClick="cambiarDeSelect(document.getElementById('companies_r'), document.getElementById('allcompanies'))" />
					</td>
					<td>
						<?php echo arraySelect( $companies_r, 'companies_r', 'id="companies_r" style="width:180px" size="8" style="font-size:9pt;" multiple="multiple"', null, FALSE, FALSE  ); ?>
					</td>
						
					<td align="right">
						<input type="button" class="button" value=">" onClick="cambiarDeSelect(document.getElementById('allcompanies'), document.getElementById('companies_rw'))" />
						<input type="button" class="button" value="<" onClick="cambiarDeSelect(document.getElementById('companies_rw'), document.getElementById('allcompanies'))" />
					</td>
						
					<td>
						<?php echo arraySelect( $companies_rw, 'companies_rw', 'id="companies_rw" style="width:180px" size="8" style="font-size:9pt;" multiple="multiple"', null, FALSE, FALSE  ); ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	
	<!-- Ordeno todos los selects !-->
	<script language="javascript">
		sortSelect(document.getElementById('allcompanies'), '<?php echo $AppUI->_('All Companies'); ?>');
		sortSelect(document.getElementById('companies_r'), '<?php echo $AppUI->_('All Companies'); ?>' );
		sortSelect(document.getElementById('companies_rw'), '<?php echo $AppUI->_('All Companies'); ?>' );
	</script>
	<?php
}

function calendar()
{
	global $AppUI, $user_id, $pvs, $pgos;
	//Pull User perms
	$sql = "
	SELECT 
		p.permission_item, p.permission_id, p.permission_grant_on, p.permission_value,
		u3.user_id, concat(u3.user_first_name,' ',u3.user_last_name) delegado
	FROM users u, permissions p
	LEFT JOIN users u3 ON u3.user_id = p.permission_item and p.permission_grant_on = 'calendar'
	WHERE u.user_id = p.permission_user
		AND u.user_id = $user_id
		AND p.permission_grant_on = 'calendar'
	";
	$res = db_exec( $sql );
	
	$perm_calendar = db_num_rows($res) > 0;
	?>
			<tr>
				<td>
					<b><? echo $AppUI->_('Calendar');?></b>
				</td>
				<td>
					<INPUT TYPE=RADIO NAME="calendar_radio" id="calendar_radio_0" VALUE="0" disabled="true"" <? echo (!$perm_calendar) ? "CHECKED" : ""?> ><?echo $AppUI->_('Denied');?>
				</td>
				<td>
					<INPUT TYPE=RADIO NAME="calendar_radio" id="calendar_radio_-1" VALUE="-1" disabled="true""<? echo ($perm_calendar)? "CHECKED" : ""?> ><?echo $AppUI->_('Enable');?>
				</td>
			</tr>

	
	<?php
	//pull the projects into an temp array
	$tarr = array();
	$calendar_r = array();
	$calendar_rw = array();
	while ($row = db_fetch_assoc( $res )) {
	
		$item = @$row[@$pgos[$row['permission_grant_on']]];
		if (!$item) {
			$item = $row['permission_item'];
		}
		if ($item == -1) {
			$item = $AppUI->_('All');
		}
		$tarr[] = array_merge( $row, array( 'grant_item'=>$item ) );
		
		if ($row['permission_value'] == 1)
			$calendar_r[ isset($row['user_id']) ? $row['user_id'] : -1 ] = $item;
		else
			$calendar_rw[ isset($row['user_id']) ? $row['user_id'] : -1] = $item;
	
	}
	
	//Traigo todas las companias que no estan en $companies_r ni en $companies_rw
	//array array_diff ( array array1, array array2 [, array ...] ) devuelve una matriz que contiene todos los valores de array1 que no aparezcan en ninguna de las otras matrices que se pasan a la funci�n como argumento. Hay que tener en cuenta que las claves se mantienen.
	$todos = array ('-1' => $AppUI->_('All'));
	$allcalendar = array_diff($todos+getAllUsers() ,$calendar_r,$calendar_rw);
	?>
	<td colspan=3>
		<table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td><?php echo $AppUI->_( 'All' );?></td>
				<td>&nbsp;</td>
				<td><?php echo $AppUI->_( 'Ver' );?></td>
				<td>&nbsp;</td>
				<td><?php echo $AppUI->_( 'Ver y Crear' );?></td>
			</tr>
			<tr>
				<td>
					<?php //function arraySelect( &$arr, $select_name, $select_attribs, $selected, $translate=false, $ordenar=TRUE, $width = '160 px' ) ?>
					<?php echo arraySelect( $allcalendar, 'allcalendar', 'id="allcalendar" style="width:180px" size="8" style="font-size:9pt;" multiple="multiple"', null, false, FALSE  ); ?>
				</td>	
				<td align="right">		
					<input type="button" class="button" value="&gt;" onClick="cambiarDeSelect(document.getElementById('allcalendar'),document.getElementById('vacio'), document.getElementById('calendar_r'))" />
					<input type="button" class="button" value="&lt;" onClick="cambiarDeSelect(document.getElementById('calendar_r'), document.getElementById('calendar_rw'), document.getElementById('allcalendar'))" />
				</td>
				<td>
					<?php echo arraySelect( $calendar_r, 'calendar_r', 'id="calendar_r" style="width:180px" size="8" style="font-size:9pt;" multiple="multiple"', null, FALSE, FALSE  ); ?>
				</td>
				<td align="right">
					<input type="button" class="button" value="&gt;" onClick="cambiarDeSelect(document.getElementById('calendar_r'), document.getElementById('allcalendar'), document.getElementById('calendar_rw'))" />
					<input type="button" class="button" value="&lt;" onClick="cambiarDeSelect(document.getElementById('calendar_rw'),document.getElementById('vacio'), document.getElementById('calendar_r'))" />
				</td>
				<td>
					<?php echo arraySelect( $calendar_rw, 'calendar_rw', 'id="calendar_rw" style="width:180px" size="8" style="font-size:9pt;" multiple="multiple"', null, FALSE, FALSE  ); ?>
				</td>
			</tr>
		</table>
	</td>
	
	<?php
}

function show_permissions($module, $module_name)
{
	global $AppUI, $user_id, $pvs, $pgos;
	//Pull User perms
	$sql = "
	SELECT
		p.permission_value
	FROM users u, permissions p
	WHERE u.user_id = p.permission_user
		AND u.user_id = $user_id
		AND p.permission_grant_on = '$module'";
	$res = db_exec( $sql );
	
	if ( db_num_rows($res) == 0 )
		$perm = 0; //Denegado
	else
		$perm = db_loadResult( $sql );
			
	?>
			<tr>
				<td>
					<b><? echo $AppUI->_($module_name);?></b>
				</td>
				<td>
					<INPUT TYPE=RADIO NAME="<?php echo $module."_radio"; ?>" VALUE="0" <? echo ($perm == 0) ? "CHECKED" : ""?> ><? echo ($module != 'all') ? $AppUI->_('Denied') : $AppUI->_('Customized') ;?>
				</td>
				<td>
					<INPUT TYPE=RADIO NAME="<?php echo $module."_radio"; ?>" VALUE="1" <? echo ($perm == 1)? "CHECKED" : ""?> ><? echo $AppUI->_('View');?>
				</td>
				<td>
					<INPUT TYPE=RADIO NAME="<?php echo $module."_radio"; ?>" VALUE="-1" <? echo ($perm == -1)? "CHECKED" : ""?> ><? echo $AppUI->_('View & Create');?>
				</td>		
			</tr>

	<?php
	if ($module == 'webtracking')
	{
		echo "<tr><td colspan=4>";
		imprimir_info_webtracking();
		echo "</td></tr>";
//El link lo muestro solo si el nuvel de acceso a webtraking es = 90:administrator
//g_access_levels_enum_string		= '10:viewer,25:reporter,40:updater,55:developer,70:manager,90:administrator';
		if (admin_webtracking())
			echo "<tr><td colspan=4 align='center'><a href=\"javascript:popUp2('index.php?m=webtracking&a=manage_user_edit_page_popup&user_id=$user_id&dialog=1&suppressLogo=1')\"> <b> " . $AppUI->_('Modify Webtracking security') ."</b></a><br><br>";
		echo "</td></tr>";
	}
	if ($module == 'hhrr')
	{
		echo "<tr><td colspan=5>";
		?>
		<script>
			function resizeMe(obj){ 
				docHeight = mainContent.document.body.scrollHeight-10
				obj.style.height = docHeight + 'px'
			} 
		</script>
		
		<iframe id="mainContent" name="mainContent" onload="resizeMe(this)" src="index_inc.php?m=SecurityCenter&inc=./modules/SecurityCenter/info_hhrr.php&user_id=<?=$user_id;?>&dialog=1&suppressLogo=1" width="100%" scrolling="no" frameborder="0">
    </iframe>
		<?
		echo "</td></tr>";
	}
}

function admin_webtracking()
{
	global $AppUI;
	return ( db_loadResult( "SELECT access_level FROM users where user_id=" .$AppUI->user_id .";" ) == 90);
}

function show_permissions_two_options($module, $module_name, $two_options = FALSE)
{
	global $AppUI, $user_id, $pvs, $pgos;
	//Pull User perms
	$sql = "
	SELECT
		p.permission_value
	FROM users u, permissions p
	WHERE u.user_id = p.permission_user
		AND u.user_id = $user_id
		AND p.permission_grant_on = '$module'";
	$res = db_exec( $sql );
	
	if ( db_num_rows($res) == 0 )
		$perm = 0; //Denegado
	else
		$perm = db_loadResult( $sql );
	
	?>
			<tr>
				<td width="200">
					<b><? echo $AppUI->_($module_name);?></b>
				</td>
				<td>
					<INPUT TYPE=RADIO NAME="<?php echo $module."_radio"; ?>" VALUE="0" <? echo (!$perm) ? "CHECKED" : ""?> ><?echo $AppUI->_('Denied');?>
				</td>
				<td colspan=2>
					<INPUT TYPE=RADIO NAME="<?php echo $module."_radio"; ?>" VALUE='-1'<? echo($perm) ? 'CHECKED' : '' ?> ><?echo $AppUI->_('Enable');?>
				</td>
			</tr>
	<?php
	if ($module == 'delegates')
	{
		echo "<tr><td colspan=4>";
		imprimir_info_delegates();
		echo "</td></tr>";

		echo "<tr><td colspan=4 align='center'><a href=\"javascript:popUp2('index.php?m=delegates&a=addeditdeleg_popup&user_id=$user_id&dialog=1&suppressLogo=1&tab=1')\"> <b> " . $AppUI->_('Modify user delegates') ."</b></a><br><br>";
		echo "</td></tr>";
	}
		?>
	<tr>
		<td>&nbsp;</td>	
	<tr>
	<?
}

function templates()
{
	global $AppUI, $user_id, $pvs, $pgos, $titleBlock;
	$AppUI->savePlace();
	
	$sql = "
	SELECT securitytemplate_id, securitytemplate_name
	FROM securitytemplates
	ORDER BY securitytemplate_name
	";
	$securitytemplates = db_loadHashList( $sql );

	?>
	<table cellspacing="0" cellpadding="0"  border="0" width="100%">
	<form name="frmapplytpl" method="post" action="">
		<input type="hidden" name="del" value="0" />
		<input type="hidden" name="dosql" value="do_perms_aed" />
		<input type="hidden" name="user_id" value="<?php echo $user_id;?>" />
		<input type="hidden" name="applytemplate" value="1" />
		<input type="hidden" name="redirect" value="<?=$_SERVER['QUERY_STRING'];?>" />
	<tr>
		<td colspan=3>
			<?php $titleBlock->showSection1( $AppUI->_('Apply permission template') ); ?>
		</td>
	</tr>
	<tr>
		<td nowrap align="right">
			<?php echo $AppUI->_('Template');?>:
			<?php echo arraySelect($securitytemplates, 'securitytemplate_id', 'size="1" class="text"', '',true);?><input type="submit"  value="<?php echo $AppUI->_('Apply');?>" class="button" name="sqlaction2">
			<? //Si esta en 1 borra todos los permisos anteriores del usuario ?>
			<INPUT TYPE=CHECKBOX NAME="del_old" CHECKED> <?php echo $AppUI->_('Borrar permisos anteriores?');?>
		</td>
	</tr>
	</form>
	</table>	
	
	<?php
}

?>