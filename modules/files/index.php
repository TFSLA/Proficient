<?php /* FILES $Id: index.php,v 1.4 2009-06-19 18:27:05 nnimis Exp $ */

if (getDenyRead( $m ))
	$AppUI->redirect( "m=public&a=access_denied" );
	$xajax->printJavascript('./includes/xajax/');
	$AppUI->savePlace ();

	// retrieve any state parameters
	if (isset( $_REQUEST['project_id'] ))
	{
		$AppUI->setState ('FileIdxProject', $_REQUEST['project_id']);
	}

	$project_id = $AppUI->getState ('FileIdxProject') !== NULL ? $AppUI->getState ('FileIdxProject') : -1;

	if (isset ($_REQUEST ['file_category']))
	{
		$AppUI->setState ('CategoryIdxProject', $_REQUEST ['file_category']);
	}

	$file_category = $AppUI->getState ('CategoryIdxProject') !== NULL ? $AppUI->getState ('CategoryIdxProject') : 0;

	if (isset($_REQUEST ['file_section']))
	{
		$AppUI->setState ('SectionIdxProject', $_REQUEST['file_section']);
	}

	$file_section = $AppUI->getState ('SectionIdxProject') !== NULL ? $AppUI->getState ('SectionIdxProject') : 0;

	require_once ($AppUI->getModuleClass ('projects'));

	// Con esto traigo solamente los proyectos que tienen algun archivo
	$extra = array ('from' => 'files', 'where' => 'AND project_id = file_project');

	$project = new CProject ();
	$projects = $project->getAllowedRecords ($AppUI->user_id, 'project_id,project_name', 'project_name', null, $extra );
	$projects = arrayMerge (array( '-1'=>$AppUI->_('Projects (None)'), ''=>$AppUI->_('Projects (All)')), $projects );



	// Creamos el titulo que nos muestra el nombre de la categoria y el icono.
	$titleBlock = new CTitleBlock ('Files', 'files.gif', $m, "$m.$a");

	if ($_POST ['mostrar_archivos_borrados']=='on') $check='checked';

	// -- Boton SHOW DELETED

	/*$titleBlock->addCell ($AppUI->_('Show deleted')."&nbsp;".
	'<INPUT TYPE="CHECKBOX" onclick="submit()" NAME="mostrar_archivos_borrados" VALUE="on" ' .$check .'>', '',
	'<form name="pickSection" action="?m=files" method="post">', '</form>');
	*/

	/* Crea el boton que permite agregar un nuevo archivo */

	if ($canEdit)
	{

		/* Si es superusuario, entonces puede acceder al ABM de categorias de archivos. */
		/*
		if ($AppUI->user_type == 1)
		{
			$titleBlock->addCell(
			'<input type="submit" class="button" value="'.$AppUI->_('New Category').'">', '', "
			<form action='?m=files&a=addedit_category&modify=no' method='post'>
				<input type='hidden' name='file_category' value='{$_POST['file_category']}' />
				<input type='hidden' name='file_section' value='{$_POST['file_section']}' />
				<input type='hidden' name='file_project' value='{$_POST['project_id']}' />",
			'</form>');
		}*/

		if($project_id == "-1")
			$projectValue = "0";
		else
			$projectValue = $project_id;

		$titleBlock->addCell(
		'<input type="submit" class="buttontitle" onmouseout="this.className=\'buttontitle\';" onmouseover="this.className=\'buttontitleover\';" value="'.strtolower($AppUI->_('New File')).'">', '', "
		<form action='?m=files&a=addedit' method='post'>
			<input type='hidden' name='file_category' value='{$_POST['file_category']}' />
			<input type='hidden' name='file_section' value='{$_POST['file_section']}' />
			<input type='hidden' name='file_project' value='$projectValue' />",
		'</form>');
	}

		$tblForm = "\n<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";
$tblForm .= "\n<tr>";
$tblForm .= "$form</tr></table>";

$titleBlock->addCell( $tblForm );


	$titleBlock->show ();

?>


<!-- ****************************************************************************************
     **** Creamos la barra donde estan los filtros de Proyectos, Categorias y Secciones. ****
     **************************************************************************************** !-->

<table cellspacing = "0" cellpadding = "0" border = "0" width = "100%">
	<tr valign="top">
		<td width='100%'>
		<table align="center" border="0" background="images/common/back_degrade.gif" width="100%" cellpadding="2" cellspacing="0" >
			<tr>

				<!-- **************************************************************************************
				     ************** Creamos el boton desplegable del filtro de Proyectos. *****************
				     ************************************************************************************** !-->

				<form name="pickProject" action="?m=files" method="post">
					<td align="center">
					<!--<td width="250" align="left">!-->
						<?php
							//echo arraySelect ($projects, 'project_id', 'onChange="document.pickProject.submit()" size="1" class="text"', $project_id, '', false);
							echo arraySelect ($projects, 'project_id', 'onChange="document.pickProject.submit()" size="1" class="text"', $project_id, '', false, '200px');
						?>
					</td>
				</form>

				<td align="left">|</td>

				<!-- **************************************************************************************
				     ************** Creamos el boton desplegable del filtro de Categorias. ****************
				     ************************************************************************************** !-->

				<form name="pickCategory" action="?m=files" method="post">
					<td align="center">
					<!--<td width="250" align="left">!-->
						<?php
							$sql_categorys = "SELECT category_id, name_".$AppUI->user_locale." AS name FROM files_category ORDER BY name_".$AppUI->user_locale;
							$file_categorys = arrayMerge (array ('0'=>$AppUI->_('Categories (All)')), db_loadHashList ($sql_categorys));
							//echo arraySelect ($file_categorys, 'file_category', 'onChange="submit()" size="1" class="text"', isset ($file_category ) ? $file_category  : 0, $traducir, false, '', '', '');
							echo arraySelect ($file_categorys, 'file_category', 'onChange="submit()" size="1" class="text"', isset ($file_category ) ? $file_category  : 0, $traducir, false, '200px', true, '200');
						?>
					</td>
				</form>

				<td align="left">|</td>

				<!-- **************************************************************************************
				     ************** Creamos el boton desplegable del filtro de Secciones. *****************
				     ************************************************************************************** !-->
				<form name="pickSection" action="?m=files" method="post">
					<td align="center">
					<!--<td align="left">!-->
						<?php
						
							$customSections = false;
						
							if(getDenyRead('articles'))
							{
								$customSections = true;
							
								require_once( $AppUI->getModuleClass( 'articles' ) );
							
								$usersections = CSection::getSectionsByUser();
								
								if(sizeof($usersections) > 0)
									$sql_sections = "SELECT * FROM articlesections WHERE articlesection_id IN (". implode( ',', $usersections).") ORDER BY name";
								else
									$sql_sections = null;
							}
							else
							$sql_sections = "SELECT * FROM articlesections ORDER BY name";
								
							if ($sql_sections != null)
								$file_sections = db_loadHashList ($sql_sections);
								
							if(!getDenyRead('articles'))
								$file_sections = arrayMerge (array ('-1'=>$AppUI->_('Top')), $file_sections);
								
							$file_sections = arrayMerge (array ('0'=>$AppUI->_('Sections (All)')), $file_sections);
							echo arraySelect ($file_sections, 'file_section', 'onChange="submit()" size="1" class="text"', isset($file_section) ? $file_section : 0, $traducir, false, '200px', '', '</form>');
						?>
					</td>
				</form>
				<td align="left"><? echo (sizeof($usersections) > 0 ? '|' : '') ?></td>
					<td nowrap="nowrap">
						<form name="pickSection" action="?m=files" method="post">
							<INPUT TYPE="CHECKBOX" onclick="submit()" NAME="mostrar_archivos_borrados" <? echo $check ?>>
							<?php echo $AppUI->_('Show deleted')."&nbsp;"; ?>
					</td>
				</form>
			</tr>
		</table>
		</td>
	</tr>
</table>
<br>
<table cellspacing = "0" cellpadding = "0" border = "0" width = "100%">
	<tr>
		<td>
			<?php
				$showProject = true;
				require ("{$AppUI->cfg['root_dir']}/modules/files/index_table.php" );
			?>
		</td>
	</tr>
</table>
