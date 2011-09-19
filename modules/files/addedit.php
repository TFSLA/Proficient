	<?php /* FILES $Id: addedit.php,v 1.3 2009-06-19 14:32:35 pkerestezachi Exp $ */
global $m,$xajax;

require_once( $AppUI->getModuleClass('articles') );

$xajax->printJavascript('./includes/xajax/');

if (isset($_POST['file_id']))
{
   $file_id = $_POST['file_id'];
}else{
   $file_id = intval( dPgetParam( $_GET, 'file_id', 0 ) );
}

// retrieve any state parameters
if ( isset($_GET['project_id']) )
{// Si el proyecto viene por aca es x que me lo pasaron desde el modulo de proyectos
	$file_project = intval( dPgetParam( $_GET, 'project_id', 0 ) );
	$file_task = intval( dPgetParam( $_GET, 'file_task', 0 ) );
}
else
{//Si es x aca es x que viene del modulo de files
	$file_project  = $AppUI->getState( 'FileIdxProject' ) !== NULL ? $AppUI->getState( 'FileIdxProject' ) : 0;
	$file_category = $AppUI->getState( 'CategoryIdxProject' ) !== NULL ? $AppUI->getState( 'CategoryIdxProject' ) : 0;
	$file_section  = $AppUI->getState( 'SectionIdxProject' ) !== NULL ? $AppUI->getState( 'SectionIdxProject' ) : 0;
}

if($file_project == "-1")
	$file_project = "0";

// check permissions for this record
$canEdit = !getDenyEdit( $m, $file_id );

// load the companies class to retrieved denied companies
require_once( $AppUI->getModuleClass('projects') );


$sql = "
SELECT files.*,
	user_username,
	user_first_name,
	user_last_name,
	project_id,
	projects.project_owner,
	task_id, task_name
FROM files
LEFT JOIN users ON file_owner = user_id
LEFT JOIN projects ON project_id = file_project
LEFT JOIN tasks ON task_id = file_task
WHERE file_id = $file_id
";

// check if this record has dependancies to prevent deletion
$msg = '';
$obj = new CFile();
$canDelete = $obj->canDelete( $msg, $file_id );

if ($file_id > 0){
	$canEdit = CFile::canEdit($file_id);
	$canDelete = CFile::canDelete($file_id);
}

$file_last_version = get_file_last_version_with_del($file_id );

// load the record data
$obj = null;
if (!db_loadObject( $sql, $obj ) && $file_id > 0) {
	$AppUI->setMsg( 'File' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}


if (!$canEdit && getDenyRead($m)) {
	
	// Si no tiene permisos sobre el modulo me fijo si es el due;o del link que quiere modificar
	if($obj->file_owner != $AppUI->user_id && $_POST[origen]!="project")
	{
		$AppUI->redirect( "m=public&a=access_denied");
	}
}

//Validacion si el archivo es privado
if($obj->is_private == 1 && $obj->file_owner != $AppUI->user_id)
	if($AppUI->user_type != 1)
		$AppUI->redirect( "m=public&a=access_denied");

if ($m!="projects" && $m!="pipeline")//Si esta pagina se carga desde el modulo de projects no cargo el titulo!
{
	// setup the title block
	$ttl = $file_id ? "Edit File" : "Add File";
	$titleBlock = new CTitleBlock( $ttl, 'files.gif', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=files", "files list" );
	if ($canDelete && $file_id > 0) {
		$titleBlock->addCrumbDelete( 'delete file', $canDelete, $msg );
	}
	$titleBlock->show();
}

if ($obj->file_opportunity) {
	$file_opportunity = $obj->file_opportunity;
}
if ($obj->file_owner) {
	$file_owner = $obj->file_owner;
}
if ($obj->file_project) {
	$file_project = $obj->file_project;
}
if ($obj->file_category) {
	$file_category = $obj->file_category;
}
if ($obj->file_section) {
	$file_section = $obj->file_section;
}
if ($obj->file_task) {
	$file_task = $obj->file_task;
	$task_name = @$obj->task_name;
} else if ($file_task) {
	$sql = "SELECT task_name FROM tasks WHERE task_id=$file_task";
	$task_name = db_loadResult( $sql );
} else {
	$task_name = '';
}

if ( !isset($from_resource) )
	$from_resource = intval( dPgetParam( $_GET, 'from_resource', 0 ) );

//$extra = array('where'=>'AND project_active <> 0');
$project = new CProject();
$projects = $project->getAllowedRecords( $AppUI->user_id, 'project_id,project_name', 'project_name', null, $extra );
$projects = arrayMerge( array( '0'=>$AppUI->_('Projects (None)') ), $projects );

$sql_categorys = "SELECT category_id, name_".$AppUI->user_locale." AS name FROM files_category ORDER BY name_".$AppUI->user_locale;
$file_categorys = db_loadHashList( $sql_categorys);
$file_categorys = arrayMerge( array( Ninguna), $file_categorys );

if($m=="projects")
{
	$project_id = $_GET[project_id];

	// con el poj traigo la company
	$sql = mysql_query("SELECT project_company FROM projects WHERE project_id ='".$project_id."' ");
	$proj_cia = mysql_fetch_array($sql);

	$prj_cia = $proj_cia[project_company];

	$sql_art = "SELECT articlesection_id FROM articlesections_projects
				WHERE company_id ='".$prj_cia."'
				AND project_id ='-1'
				UNION
				SELECT articlesection_id FROM articlesections_projects
				WHERE company_id ='".$prj_cia."'
				AND project_id ='".$project_id."'
				";

	$sec_art = db_loadColumn($sql_art);

	if(count($sec_art)!=0)
		$secs_art=implode( ',', $sec_art);
	else
		$secs_art="''";

	$query = "SELECT *
	          FROM articlesections
			  WHERE 1=1";
	if(!getKBPermissions()){
		$query .= " AND articlesection_id IN ($secs_art)"; 
	}
}
else{
	$query = "SELECT * FROM articlesections";
}

$file_sections = db_loadHashList( $query);
$alguna = key($file_sections);//Esto lo hago para elegir alguna seccion cualquiera(la primera) que no sea Ninguna
$file_sections = arrayMerge( array( Ninguna), $file_sections );

//Solo Agrego la seccion TOP si entra desde el modulo de KB
if ($m=='articles')
	$file_sections = arrayMerge( array( '-1'=>$AppUI->_('Top')), $file_sections );

$traducir = $AppUI->user_locale == "en" ? TRUE :FALSE ;
?>


<table width="100%" border="0" cellpadding="3" cellspacing="3" class="std">

<form name="uploadFrm" action="?m=files" enctype="multipart/form-data" method="post">
	<input type="hidden" name="max_file_size" value="52428800" />
	<input type="hidden" name="dosql" value="do_file_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="file_id" value="<?php echo $file_id;?>" />
	<input type="hidden" id="file_exist" name="file_exist" value="false" />
	<input type="hidden" name="ajaxfile_user_id" value="<?php echo $file_owner;?>" />	

<tr>
	<td width="100%" valign="top" align="center">
		<table cellspacing="1" cellpadding="2" width="60%">
	<?php
	//Si entro para editar, agrego los campos: File Name, Type, Size y Uploaded By.
	 if ($file_id) { ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'File Name' );?>:</td>
			<td align="left" class="hilite"><?php echo strlen($obj->file_name)== 0 ? "n/a" : $obj->file_name;?></td>
			<td>
				<a href="./fileviewer.php?file_id=<?php echo $obj->file_id;?>"><?php echo $AppUI->_( 'download' );?></a>
			</td>
		</tr>
		<tr valign="top">
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Type' );?>:</td>
			<td align="left" class="hilite"><?php echo $obj->file_type;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Size' );?>:</td>
			<td align="left" class="hilite"><?php echo $obj->file_size;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Uploaded By' );?>:</td>
			<td align="left" class="hilite"><?php echo $obj->user_first_name . ' '. $obj->user_last_name;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Version' );?>:</td>
			<td align="left" class="hilite"><?php echo $file_last_version;?> </td>

			<td align="right" nowrap="nowrap"> <INPUT TYPE="radio" NAME="tipo_cambio" VALUE="grande" CHECKED> <?php echo $AppUI->_( 'Big Change' );?></td>
			<td align="right" nowrap="nowrap"> <INPUT TYPE="radio" NAME="tipo_cambio" VALUE="chico"> <?php echo $AppUI->_( 'Small Change' );?></td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Comment' );?>:</td>
			<td align="left">
				<textarea name="version_description" class="textarea" rows="4" style="width:270px"></textarea>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Project' );?>:</td>
			<td align="left">
			<?php
				$disabled_project = "";
			
				if ($from_kb OR $file_project == 0)
					$selected = 0;
				else
					$selected = $file_project;
					
			   if ($selected == "" && $_GET['project_id'] != "")
				{  
					$selected = $_GET['project_id'];
				}
				
				$project_id_selected = $selected;
				
				if($project_id_selected > 0 && !array_key_exists($project_id_selected, $projects))
				{
					if($project->load($file_project, false))
						$projects = arrayMerge(array($file_project=>$project->project_name), $projects);

					if($file_owner != $AppUI->user_id && $AppUI->user_type != '1') $disabled_project = "disabled";
				}

				echo arraySelect( $projects, "file_project", "size=\"1\" class=\"text\" style=\"width:270px\" onchange=\"setTasks();\" ".$disabled_project, $selected);
			?>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Task' );?>:</td>
			<td align="left" colspan="2" valign="top">
				<?php

				$htmlTask .= "	<option value=\"0\">".$AppUI->_('Tasks (None)')."</option>";

				$findTask = false;
				$taskDisabled = "";

				if($file_project){
					$Cproject = new CProjects();
					$Cproject->loadTasks($file_project);
					$tasks = $Cproject->Tasks();

					for($i=0; $i<count($tasks); $i++){
						if($tasks[$i]["project_id"] == $file_project){
							if($file_task == $tasks[$i]["task_id"]){
								$selected="selected";
								$findTask = true;
							}

							$htmlTask .= "<option value=".$tasks[$i]["task_id"]." $selected >".$tasks[$i]["task_name"]."</option>";
							$selected="";
						}
					}
				}

				if(!$findTask && $file_task > 0){
					$objTask = new CTask();

					if($objTask->load($obj->file_task)){
						if($AppUI->user_type != '1') $taskDisabled = "disabled";
						$htmlTask .= "<option value=".$file_task." selected>".$objTask->task_name."</option>";
					}
				}

				$htmlTask = "<select name=\"file_task\" id=\"task\" style=\"width:270px\" class=\"text\" ".$taskDisabled.">".$htmlTask;
				$htmlTask .= "</select>";

				echo($htmlTask);
				?>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'File Section' );?>:</td>
			<td align="left">
			<?php
				$traducir = $AppUI->user_locale == "en" ? TRUE : FALSE;

				$disabled_section = "";

				$arrSections = CSection::getComboData($project_id_selected, $file_section, $file_owner);
				$disabled_section = $arrSections[0];
				$selected = $arrSections[1];
				$file_sections = (array)($arrSections[2]);

				echo arraySelect( $file_sections, "file_section", "size=\"1\" class=\"text\" style=\"width:270px\" ".$disabled_section, $selected , $traducir  );
			?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Opportunity' );?>:</td>
			<td align="left">
				<?php

				require_once( $AppUI->getConfig( "root_dir")."/modules/pipeline/leads.class.php" );
				$opportunities = CLead::getAllowedLeads();

				$opportunities = arrayMerge( array( '0'=>$AppUI->_('Opportunities (None)') ), $opportunities );

				if($file_opportunity > 0)
					$lead_id = $file_opportunity;
				else
					$lead_id = $_GET['lead_id'];		

				if ($lead_id > 0)
					$selected = $lead_id;
				else
					$selected = 0;
					
				$disabledOpportunity = "";
			
				if(!array_key_exists($selected, $opportunities))
				{
					$lead = new CLead();
					$lead->load($selected);
			
					$opportunities[$lead_id] = $lead->accountname;
			
					if($file_owner != $AppUI->user_id && $AppUI->user_type != '1') $disabledOpportunity = "disabled";
				}
				
				echo arraySelect( $opportunities , 'file_opportunity', 'size="1" class="text" style="width:270px" '.$disabledOpportunity, $selected);
				?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'File Category' );?>:</td>
			<td align="left">
			<?php
				echo arraySelect( $file_categorys , 'file_category', 'size="1" class="text" style="width:270px"', $obj->file_category, $traducir );
			?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Notify' );?>:</td>
			<td align="left">
				<?php 
					if($_GET['m']!='articles' && $_GET['m']!='projects' && $_GET['m']!='pipeline'){ 
						require_once("./modules/articles/articles.class.php");
					}
					CArticle::getNotifyHTML(); 
				?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Description' );?>:</td>
			<td align="left">
				<textarea name="file_description" class="textarea" rows="4" style="width:270px"><?php echo $obj->file_description;?></textarea>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Upload File' );?>:</td>
			<td align="left"><input type="File" class="text" name="formfile" size="28" ></td>
		</tr>
		<tr>
			<input type="hidden" name="login_show_hidden" />
			<?
				$lastHistoryData = CFile::getLastActionHistory($obj->file_id, 2);
				
				if(!$lastHistoryData || $lastHistoryData['history_action'] == 2)
				{
					if($AppUI->user_type == 1 || !$project_id_selected || in_array($project_id_selected, CUser::getAdminOwnerProjects()))
						$historyDataText = "<u><p style=\"cursor:pointer;\" onclick=\"javascript:ApprovedDocument(".$file_id.", 2, 4);\">".$AppUI->_( 'sign' )."</p></u>";
				}
				else
				{
					$historyData = CFile::getHistory($obj->file_id, 2, 4, $file_last_version);
				
					if($historyData)
					{
						$historyDate = new CDate($historyData['history_date']);
						$historyDataText = $historyData['fullname'].' '.$AppUI->_( 'on' ).' '.$historyDate->format($AppUI->getPref('SHDATEFORMAT').' '.$AppUI->getPref('TIMEFORMAT'));
					}
					else
					{				
						if($AppUI->user_type == 1 || !$project_id_selected || in_array($project_id_selected, CUser::getAdminOwnerProjects()))
							$historyDataText = "<u><p style=\"cursor:pointer;\" onclick=\"javascript:ApprovedDocument(".$file_id.", 2, 4);\">".$AppUI->_( 'sign' )."</p></u>";
					}
				}
				
				if($historyDataText)
				{
				?>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Approved' );?>:</td>
					<td align="left"><?php echo $historyDataText; ?></td>
				<?
				}
				?>
		</tr>
		<?php 
		if(($file_id && $obj->file_owner==$AppUI->user_id || $AppUI->user_type==1) || !$file_id){
			$disabled="enabled='enabled'";
		}else{
			$disabled="disabled='disabled'";
		}
		?>		
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Protected' );?></td>
			<td align="left"><input type="checkbox" name="is_protected" <?php if($obj->is_protected==1) echo "checked";?> <?php echo  $disabled; ?> ></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Private' );?></td>
			<td align="left"><input type="checkbox" name="is_private" <?php if($obj->is_private==1) echo "checked";?> <?php echo  $disabled; ?> onclick="canNotify();"></td>
		</tr>	
		</table>
	</td>
</tr>
<script type="text/javascript" language="JavaScript">
f=document.uploadFrm;

f.version_description.focus();

if(f.file_project.value!='0' && f.is_private.checked==false){
	f.notify_type.disabled = false;
		f.notify_type.value = 1;
}

var historial_file_id_temp, historial_type_temp, historial_action_temp, historialItem_x, historialItem_y, history_comment_temp;

function ApprovedDocument(file_id, type, action)
{
	historial_file_id_temp = file_id;
	historial_type_temp = type;
	historial_action_temp = action;
	
	if (isIE)
	{
		historialItem_x = posX();
		historialItem_y = posY();
	}
	else
	{
		historialItem_x = netX;
		historialItem_y = netY;
	}
	
	xajax_showLogin();
}

function showLogin()
{
	tooltipLinkXY(document.getElementById('login_show_hidden').value, '', 'tooltip', historialItem_x, historialItem_y - 150);
}

function processLogin(username, password, comment)
{
	history_comment_temp = comment;
	xajax_checkLogin(username, password);
}

function successLogin()
{
	xajax_saveLogHistory(historial_file_id_temp, historial_type_temp, historial_action_temp, history_comment_temp);
}

function failedLogin()
{
	alert("<?=$AppUI->_('Invalid Credentials')?>");
}

</script>
	<?php
		}
		else
		{  					//Si es un archivo nuevo imprimo esto:
		?>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Project' );?>:</td>
			<td align="left">
			<?php
				$disabled_project = "";
			
				if ($from_kb OR $file_project == 0)
					$selected = 0;
				else
					$selected = $file_project;
					
			   if ($selected == "" && $_GET['project_id'] != "")
				{  
					$selected = $_GET['project_id'];
				}
				
				$project_id_selected = $selected;
				
				if($project_id_selected > 0 && !array_key_exists($project_id_selected, $projects))
				{
					if($project->load($file_project, false))
						$projects = arrayMerge(array($file_project=>$project->project_name), $projects);

					if($file_owner != $AppUI->user_id && $AppUI->user_type != '1') $disabled_project = "disabled";
				}

				echo arraySelect( $projects, "file_project", "size=\"1\" class=\"text\" style=\"width:270px\" onchange=\"setTasks();\" ".$disabled_project, $selected);
			?>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Task' );?>:</td>
			<td align="left" colspan="2" valign="top">
				<?
				$htmlTask .= "	<option value=\"0\">".$AppUI->_('Tasks (None)')."</option>";

				$findTask = false;
				$taskDisabled = "";

				if($file_project){
					$Cproject = new CProjects();
					$Cproject->loadTasks($file_project);
					$tasks = $Cproject->Tasks();

					for($i=0; $i<count($tasks); $i++){
						if($tasks[$i]["project_id"] == $file_project){
							if($file_task == $tasks[$i]["task_id"]){
								$selected="selected";
								$findTask = true;
							}

							$htmlTask .= "<option value=".$tasks[$i]["task_id"]." $selected >".$tasks[$i]["task_name"]."</option>";
							$selected="";
						}
					}
				}

				if(!$findTask && $file_task > 0){
					$objTask = new CTask();

					if($objTask->load($file_task)){
						if($AppUI->user_type != '1') $taskDisabled = "disabled";
						$htmlTask .= "<option value=".$file_task." selected>".$objTask->task_name."</option>";
					}
				}

				$htmlTask = "<select name=\"task\" id=\"file_task\" style=\"width:270px\" class=\"text\" ".$taskDisabled.">".$htmlTask;
				$htmlTask .= "</select>";

				echo($htmlTask);
				?>
			</td>
		</tr>

		<tr>
				<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'File Section' );?>:</td>
				<td align="left">
				<?php
					$traducir = $AppUI->user_locale == "en" ? TRUE : FALSE;

					$disabled_section = "";

					$arrSections = CSection::getComboData($project_id_selected, $file_section, $file_owner);
					$disabled_section = $arrSections[0];
					$selected = $arrSections[1];
					$file_sections = (array)($arrSections[2]);

					echo arraySelect( $file_sections, "file_section", "size=\"1\" class=\"text\" style=\"width:270px\" ".$disabled_section, $selected , $traducir  );
				?>
				</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Opportunity' );?>:</td>
			<td align="left">
				<?php

				require_once( $AppUI->getConfig( "root_dir")."/modules/pipeline/leads.class.php" );
				$opportunities = CLead::getAllowedLeads();

				$opportunities = arrayMerge( array( '0'=>$AppUI->_('Opportunities (None)') ), $opportunities );

				if($file_opportunity > 0)
					$lead_id = $obj->file_opportunity;
				else
					$lead_id = $_GET['lead_id'];		

				if ($lead_id > 0)
					$selected = $lead_id;
				else
					$selected = 0;
					
				$disabledOpportunity = "";
			
				if(!array_key_exists($selected, $opportunities))
				{
					$lead = new CLead();
					$lead->load($selected);
			
					$opportunities[$lead_id] = $lead->accountname;
			
					if($file_owner != $AppUI->user_id && $AppUI->user_type != '1') $disabledOpportunity = "disabled";
				}
				
				echo arraySelect( $opportunities , 'file_opportunity', 'size="1" class="text" style="width:270px" '.$disabledOpportunity, $selected);
				?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'File Category' );?>:</td>
			<td align="left">
			<?php

				if ($file_category=='' OR $file_category==0)
				{
					if ($from_kb)
						$selected = array_search('Ninguna',$file_categorys);
					else
						$selected = array_search('Documento',$file_categorys);
				}
				else
					$selected = $file_category;

				echo arraySelect( $file_categorys , 'file_category', 'size="1" class="text" style="width:270px"', $selected, $traducir );
			?>
			</td>
		</tr>
		<?php if($_GET[id]==0) { ?>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Notify' );?>:</td>
			<td align="left">
				<?php 
				if($_GET['m']!='articles' && $_GET['m']!='projects' && $_GET['m']!='pipeline'){  
					require_once("./modules/articles/articles.class.php");
				}
					CArticle::getNotifyHTML(); 
				?>
			</td>
		</tr>
		<?php } ?>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Description' );?>:</td>
			<td align="left">
				<textarea name="file_description" class="textarea" rows="4" style="width:270px"><?php echo $obj->file_description;?></textarea>
			</td>
		</tr>

		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Upload File' );?>:</td>
			<td align="left"><input type="File" class="text" name="formfile" size="28" ></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Protected' );?></td>
			<td align="left"><input type="checkbox" name="is_protected" ></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Private' );?></td>
			<td align="left"><input type="checkbox" name="is_private" onclick="canNotify();"></td>
		</tr>
		</table>
	</td>
</tr>

<script type="text/javascript" language="JavaScript">
document.uploadFrm.file_description.focus();
</script>

<? } ?>


<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
	</td>
	<td align="right">
		<input type="button" class="button" value="<?php echo $AppUI->_( 'submit' );?>" onclick="submitIt()" />
	</td>
</tr>
</form>
</table>

<script language="javascript">
function submitIt() {

	<? //Si se esta cargando desde la solapa de conocimiento le alerto si no selecciono ninuna SECCION ?>
	if( (<?= ($m!="files" AND !$from_resource) ? 'true': 'false'?>) && (document.uploadFrm.file_section.value==0)){
		if( !confirm("<?php echo $AppUI->_('withoutSection');?>") ){
			return;
		}
	}

	<? //Si se esta cargando desde la solapa de recursos le alerto si no selecciono ninuna CATEGORIA ?>
	if( (<?= ($m=="files" OR $from_resource) ? 'true': 'false'?>) && (document.uploadFrm.file_category.value==0)){
		if( !confirm("<?php echo $AppUI->_('withoutCategory');?>") ){
			return;
		}
	}

	<?//Si es un archivo nuevo llamo a la funcion de ajax para ver si ya existe un archivo con ese nombre en la BD.
	//Si se esta editando algun archivo envio directamente el formulario. ?>
	if (<?= ($file_id==0)?'true':'false' ?> == true)
	{
		xajax_file_exist(document.uploadFrm.formfile.value, document.uploadFrm.file_project.value, document.uploadFrm.is_private.checked);
	}
	else
	{
		document.uploadFrm.submit();
	}


}
function delIt() {
	if (confirm( "<?php echo $AppUI->_('filesDelete');?>" )) {
		var f = document.uploadFrm;
		f.del.value='1';
		f.submit();
	}
}
function popTask()
{
    var f = document.uploadFrm;
    if (f.file_project.options[f.file_project.selectedIndex].value == 0) {
        alert( '<?php echo $AppUI->_('Please select a project first!');?>' );
    } else {
        window.open('./index.php?m=public&a=selector&dialog=1&callback=setTask&table=tasks&suppressLogo=1&task_project='
            + f.file_project.options[f.file_project.selectedIndex].value, 'task','left=50,top=50,height=250,width=400,resizable')
    }
}

function clearTask()
{
    var f = document.uploadFrm;
   	f.task_name.value = "";
   	
   	canNotify();
}


function canNotify(){
	var f = document.uploadFrm;
	
	if(f.file_project.value=='0' || f.is_private.checked==true){
   		f.notify_type.disabled = true;
   		f.notify_type.value = 0;
   	}else{
   		f.notify_type.disabled = false;
   		f.notify_type.value = 1;
   	}
}

// Callback function for the generic selector
function setTask( key, val ) {
    var f = document.uploadFrm;
    if (val != '') {
        f.file_task.value = key;
        f.task_name.value = val;
    } else {
        f.file_task.value = '0';
        f.task_name.value = '';
    }
}

function setTasks()
{
	frm = document.uploadFrm;
	//alert(frm.file_project.value);
	//xajax_setComboSections(frm.file_project.value, frm.file_section.value, frm.ajaxfile_user_id.value, 'articlesection_id' );
	xajax_setComboTasks(frm.file_project.value, 'file_task' );
	canNotify();
}
</script>
<?php
function getKBPermissions(){
	global $AppUI;
	
	if($AppUI->user_type == 1) return true;
	
	$sql = "SELECT * FROM permissions WHERE permission_grant_on = 'articles' AND permission_value = -1 AND permission_user = ".$AppUI->user_id;
	$result = mysql_query($sql);
	
	if(mysql_num_rows($result) > 0) {return true;}
	else {return false;}
}
?>