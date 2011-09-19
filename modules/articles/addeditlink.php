<?php
$m = $_GET[m];

$canRead = !getDenyRead( $m );

if (isset($_POST['article_id']))
{
	$id = $_POST['article_id'];
}else{
	$id = isset($_GET['id']) ? $_GET['id'] : 0;
}

if ( isset($_GET['from_resource']) )
	$from_resource = $_GET['from_resource'];

$sec_id = $_GET['sec_id'];

if($sec_id=="")
	$sec_id = $_POST['sec_id'];

$sql = "
SELECT *
FROM articles
WHERE article_id = $id
";

if ($id > 0 && !db_loadHash( $sql, $drow ) ) {
	$titleBlock = new CTitleBlock( 'Invalid Article  ID', 'article_management.gif', $m, 'ID_HELP_ARTICLESECTION_EDIT' );
	$titleBlock->addCrumb( "?m=articles&a=admin", "Articles" );
	$titleBlock->show();
} else {
   if($m!="projects" && $m!="pipeline"){
    // setup the title block
	$ttl = $id > 0 ? $AppUI->_('edit link') : $AppUI->_('add link');
	$titleBlock = new CTitleBlock( $ttl, 'article_management.gif', $m, 'ID_HELP_SECURITY_TEMPLATE_EDIT' );
	$titleBlock->show();
   }
}

//Validacion si el enlace es privado
if($drow['is_private'] == 1 && $drow['user_id'] != $AppUI->user_id)
	if($AppUI->user_type != 1)
		$AppUI->redirect( "m=public&a=access_denied");


if (!isset($from_kb))
	$from_kb=$_GET['from_kb'];

include_once("./modules/projects/projects.class.php");
$project = new CProject();
$projects = $project->getAllowedRecords( $AppUI->user_id, 'project_id,project_name', 'project_name', null, $extra );
$projects = arrayMerge( array( '0'=>$AppUI->_('Projects (None)') ), $projects );

$sql_categorys = "SELECT category_id, name_".$AppUI->user_locale." AS name FROM files_category ORDER BY name_".$AppUI->user_locale;
$file_categorys = db_loadHashList( $sql_categorys);
$file_categorys = arrayMerge( array( Ninguna), $file_categorys );

if ($id!="0"){
 $query = mysql_query($sql);
 $row = mysql_fetch_array($query);

 $articlesection_id = $row[articlesection_id];
 $file_category = $row[file_category];
 $description = $row[title];
 $href = $row['abstract'];
 $abstract = $row[body];
 $file_project = $row[project];
 $file_task = $row[task];
 $file_opportunity = $row[opportunity];
 $file_user_id = $row[user_id]; 

	$sql = "SELECT task_name FROM tasks WHERE task_id='$file_task'";
	$task_name = db_loadResult( $sql );
}
else
{
  $file_project = isset($_GET['project_id']) ? $_GET['project_id'] : 0;
  $file_task = isset($_GET['task_id']) ? $_GET['task_id'] : 0;
 
  if ($_POST[sec_id] != "")
  {
  	$articlesection_id = $_POST[sec_id];
  }else{
  	$articlesection_id = $_GET[sec_id];
  }
}

if (!$canRead && $_POST[accion] != "save")
{
	//  Por si acceden directamente poniendo la direccion , verifico los permisos
	$accessdenied = true;

	$objProject = new CProject();
	$prjs = $objProject->getAllowedRecords($AppUI->user_id, "project_id");

	if ($file_project > 0 && (array_key_exists($file_project, $prjs))){
		$accessdenied = false;
	}
	else{
		if($articlesection_id <> 0){
			if(!getDenyRead('articles')){
				$accessdenied = false;
			}
			else{

				$userSections = CSection::getSectionsByUser();

				if (in_array($articlesection_id, $userSections))
					$accessdenied = false;
			}
		 }
	}

	if ($accessdenied)
		$AppUI->redirect( "m=public&a=access_denied" );
}

$majorUpdate = false;

 if($_POST[accion]=="save")
 {
	if(!stristr($_POST['href'], 'http://') === FALSE) $_POST['href']=substr($_POST['href'], 7);
	    if($_POST["is_protected"]=='on'){
			$_POST["is_protected"]=1;
		}else {
			$_POST["is_protected"]=0;
		}
		
	    if($_POST["is_private"]=='on'){
			$_POST["is_private"]=1;
		}else {
			$_POST["is_private"]=0;
		}
		
		if ($_POST[id]=="0"){
		   $ts = time();
		   $date = date("Y-m-d H:i:s",$ts);
		   $date_modified = date("Y-m-d H:i:s",$ts);

		   $query = "INSERT INTO articles (articlesection_id, file_category, date, articles_reads, user_id, title, abstract, body, project, task, opportunity, type,date_modified,is_protected, is_private)
		   VALUES ('".$_POST[articlesection_id]."','".$_POST[file_category]."' ,'".$date."', '0', '".$AppUI->user_id."', '".$_POST[description]."', '".$_POST[href]."','".$_POST['abstract']."','".$_POST[project]."','".$_POST[task]."','".$_POST[opportunity]."','1','".$date_modified."',".$_POST['is_protected'].",".$_POST['is_private'].")";
	   }
       else{

		   $ts = time();
		   //$date = date("Y-m-d H:i:s",$ts);
		   $date_modified = date("Y-m-d H:i:s",$ts);
		   
			$actualLink = new CArticle();
			$actualLink->load($_POST['id']);		   

		   $query = "UPDATE articles SET articlesection_id= '".$_POST[articlesection_id]."',file_category='".$_POST[file_category]."',title='".$_POST[description]."',abstract='".$_POST[href]."', body='".$_POST['abstract']."',project='".$_POST[project]."',task='".$_POST[task]."', opportunity='".$_POST[opportunity]."', date_modified='".$date_modified."', is_protected=".$_POST['is_protected'].", is_private=".$_POST['is_private']." WHERE article_id='".$_POST[id]."' ";
	   }
	   $sql = mysql_query($query);
	   $article_id = mysql_insert_id();
	   
	   $obj = new CArticle();
	   
	   if($_POST[id]!=0){ 	//Update
			   $obj->article_id=$_POST['id'];

				if($_POST["href"] != $actualLink->abstract)
				{
					$majorUpdate = true;
					$obj->saveLog(1,2);//major update
				}
				else
					$obj->saveLog(1,5);//minor update
			   
			   if($_POST["is_private"] == 0 && $majorUpdate){
					$obj->project = $_POST['project'];
					$obj->task = $_POST['task'];
					$obj->article_id = $_POST['id'];
					$obj->notifyNewKnowledge($_POST['notify_type'],true);
			   }
	   }else{ 				//Create
	   		if($_POST["is_private"]==0){
				$obj->project = $_POST['project'];
				$obj->task = $_POST['task'];
				$obj->article_id = $article_id;
				$obj->notifyNewKnowledge($_POST['notify_type']);
	   		}
	   }
/*
echo " Adentro <pre>";
print_r($_POST);
echo $query;
echo "</pre>";*/
//return;

     $AppUI->redirect($AppUI->state['SAVEDPLACE']);
 }

?>
<script language="javascript">

function submitIt(){
	var form = document.addedit_link;
    var error = true;

	if(form.description.value==""){
		alert( "<?php echo $AppUI->_('error_description');?>" );
		error = false;
	}

	if((form.href.value=="")&&(error)){
		alert("<?php echo $AppUI->_('error_href');?>");
		error = false;
	}

	<? //Si se esta cargando desde la solapa de conocimiento le alerto si no selecciono ninuna SECCION ?>
	if( (<?= !($from_resource) ? 'true': 'false'?>) && (form.articlesection_id.value==0)&&(error)){
		if( !confirm("<?php echo $AppUI->_('withoutSection');?>") ){
			error = false;
		}
	}

	<? //Si se esta cargando desde la solapa de recursos le alerto si no selecciono ninuna CATEGORIA ?>
	if( (<?= ($from_resource) ? 'true': 'false'?>) && (form.file_category.value==0)&&(error)){
		if( !confirm("<?php echo $AppUI->_('withoutCategory');?>") ){
			error = false;
		}
	}

	if(error){
		form.submit();
	}
}

function clearTask()
{
    var f = document.addedit_link;
   	f.task_name.value = "";
   	
   	canNotify();
}

function canNotify(){
	var f = document.addedit_link;
	
	if(f.project.value=='0' || f.is_private.checked==true){
   		f.notify_type.disabled = true;
   		f.notify_type.value = 0;
   	}else{
   		f.notify_type.disabled = false;
   		f.notify_type.value = 1;
   	}
}

function popTask()
{
    var f = document.addedit_link;
    if (f.project.options[f.project.selectedIndex].value == 0) {
        alert( '<?php echo $AppUI->_('Please select a project first!');?>' );
    } else {
        window.open('./index.php?m=public&a=selector&dialog=1&callback=setTask&table=tasks&suppressLogo=1&task_project='
            + f.project.options[f.project.selectedIndex].value, 'task','left=50,top=50,height=250,width=400,resizable')
    }
}

// Callback function for the generic selector
function setTask( key, val ) {
    var f = document.addedit_link;
    if (val != '') {
        f.task.value = key;
        f.task_name.value = val;
    } else {
        f.task.value = '0';
        f.task_name.value = '';
    }
}

function submit_back()
{
	var form = document.edit_back;
	
	form.submit();
}

function setTasks()
{
	frm = document.addedit_link;
	xajax_setComboSections(frm.project.value, frm.articlesection_id.value, frm.ajaxfile_user_id.value, 'articlesection_id' );	
	xajax_setComboTasks(frm.project.value, 'task' );
	canNotify();
}
</script>


    <table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
       <form name="addedit_link" action="?m=articles&a=addeditlink" method="post">
	    	<input type="hidden" name="id" value="<?php echo $id;?>" />
		  	<input type="hidden" name="accion" value="save" />
			<input type="hidden" name="ajaxfile_user_id" value="<?php echo $file_user_id;?>" />		  	
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Project' );?>:</td>
					<td align="left">
					<?php
						$disabled_project = "";
					
						if ($from_kb OR $file_project==0)
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
			
							if($file_user_id != $AppUI->user_id && $AppUI->user_type != '1') $disabled_project = "disabled";
						}
                          
						echo arraySelect( $projects, "project", "size=\"1\" class=\"text\" style=\"width:270px\" onchange=\"setTasks();\" ".$disabled_project, $selected);
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

						if($objTask->load($file_task)){
							if($AppUI->user_type != '1') $taskDisabled = "disabled";
							$htmlTask .= "<option value=".$file_task." selected>".$objTask->task_name."</option>";
						}
					}

					$htmlTask = "<select name=\"task\" id=\"task\" style=\"width:270px\" class=\"text\" ".$taskDisabled.">".$htmlTask;
					$htmlTask .= "</select>";

					echo($htmlTask);
					?>
					</td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'File Category' );?>:</td>
					<td align="left">
					<?php
					if ($file_category=='' OR $file_category==0)
					{
						if ($from_kb OR $m=="articles")
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
						<td align="right"><?php echo $AppUI->_( 'Section' );?>:</td>
						<td>
							<?
							/*
							if($m=="projects" OR $from_kb)
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
							$file_sections = arrayMerge( array(Ninguna), $file_sections );

							//Solo Agrego la seccion TOP si entra desde el modulo de KB
							if ($m=='articles')
								$file_sections = arrayMerge( array( '-1'=>$AppUI->_('Top')), $file_sections );

							if ($articlesection_id=='' )
							{
								if ($m!="files" AND !$from_resource)
									$selected = $alguna;
								else
									$selected = array_search('Ninguna',$file_categorys);
							}
							else
								$selected = $articlesection_id;
							

							$traducir = $AppUI->user_locale == "en" ? TRUE :FALSE ;
							*/
							$traducir = $AppUI->user_locale == "en" ? TRUE : FALSE;
		
							$disabled_section = "";

							$arrSections = CSection::getComboData($project_id_selected, $articlesection_id, $file_user_id);
							$disabled_section = $arrSections[0];
							$selected = $arrSections[1];
							$file_sections = (array)($arrSections[2]);

							echo arraySelect( $file_sections, "articlesection_id", "size=\"1\" class=\"text\" style=\"width:270px\" ".$disabled_section, $selected , $traducir  );
							?>

						</td>
			      <td valign="top" align="center"></td>
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

							if($file_user_id != $AppUI->user_id && $AppUI->user_type != '1') $disabledOpportunity = "disabled";
						}

						echo arraySelect( $opportunities , 'opportunity', 'size="1" class="text" style="width:270px" '.$disabledOpportunity, $selected);
						?>
					</td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_( 'Link\'s title' );?>:</td>
					<td><input type="text" class="text" name="description" value="<?php echo $description;?>" maxlength="128" size="48"></td>
					<td valign="top" align="center">
						</td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_( 'href' );?>:</td>
					<td><input type="text" class="text" name="href" value="<?php echo $href;?>" maxlength="128" size="48"></td>
					<td valign="top" align="center">
						</td>
				</tr>
				<tr>
					<td align="right"><?php echo $AppUI->_( 'Abstract' );?>:</td>
					<td><textarea rows=3 cols=70 name="abstract"><?php echo $abstract;?></textarea></td>
					<td valign="top" align="center"></td>
				</tr>
				<?php 
				if(($id && $drow['user_id']==$AppUI->user_id || $AppUI->user_type==1) || !$id){
					$disabled="enabled='enabled'";
				}else{
					$disabled="disabled='disabled'";
				}
				?>
				<?
					if($id > 0)
					{
				?>
				<tr>
					<input type="hidden" name="login_show_hidden" />
					<?
						$lastHistoryData = CArticle::getLastActionHistory($id, 1);
												
						if(!$lastHistoryData || $lastHistoryData['history_action'] == 2)
						{
							if($AppUI->user_type == 1 || !$project_id_selected || in_array($project_id_selected, CUser::getAdminOwnerProjects()))
								$historyDataText = "<u><p style=\"cursor:pointer;\" onclick=\"javascript:ApprovedDocument(".$id.", 1, 4);\">".$AppUI->_( 'sign' )."</p></u>";
						}
						else
						{
							$historyData = CArticle::getHistory($id, 1, 4);

							if($historyData)
							{
								$historyDate = new CDate($historyData['history_date']);
								$historyDataText = $historyData['fullname'].' '.$AppUI->_( 'on' ).' '.$historyDate->format($AppUI->getPref('SHDATEFORMAT').' '.$AppUI->getPref('TIMEFORMAT'));
							}
							else
							{							
								if($AppUI->user_type == 1 || !$project_id_selected || in_array($project_id_selected, CUser::getAdminOwnerProjects()))
									$historyDataText = "<u><p style=\"cursor:pointer;\" onclick=\"javascript:ApprovedDocument(".$id.", 1, 4);\">".$AppUI->_( 'sign' )."</p></u>";
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
				<?
					}
				?>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Protected' );?></td>
					<td align="left"><input type="checkbox" name="is_protected" <?php if($drow["is_protected"]==1) echo "checked";?> <?php echo  $disabled; ?> ></td>
				</tr>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Private' );?></td>
					<td align="left"><input type="checkbox" name="is_private" <?php if($drow["is_private"]==1) echo "checked";?> <?php echo  $disabled; ?> onclick="canNotify();"></td>
				</tr>
				<tr>
					<td>
						<?
						
						$path = "index.php?".$AppUI->state['SAVEDPLACE'];
						
						
						?>

						<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="submit_back()" />
					</td>
					<td colspan="2" align="right">
						<input type="button" value="<?php echo $AppUI->_( 'submit' );?>" class="button" onClick="submitIt()" />
					</td>
				</tr>
		</form>
  </table>
  
  <form name="edit_back" method="POST" action="<?=$path?>">
    <input type="hidden" name="articlesection_id" value="<?=$sec_id?>">
  </form>
  <script language="javascript">
	var historial_article_id_temp, historial_type_temp, historial_action_temp, historialItem_x, historialItem_y, history_comment_temp;

	function ApprovedDocument(article_id, type, action)
	{
		historial_article_id_temp = article_id;
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
		xajax_saveLogHistory(historial_article_id_temp, historial_type_temp, historial_action_temp, history_comment_temp);
	}

	function failedLogin()
	{
		alert("<?=$AppUI->_('Invalid Credentials')?>");
	}
  
  canNotify();
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