<script type="text/javascript">
<!--
	var open_rows;
	var rows;
	function openclose(open_rows, rows, item){
		var open_rows=open_rows;
		var rows=rows;
		var item=item;
		var type=1;
		if (open_rows[rows][0]=='0'){
			if (open_rows[rows][1]=='1') {
				xajax_clear("new_"+rows);
				open_rows[rows][1]=0;
			}
			open_rows[rows][0]=1;
			xajax_notes(rows, item, type);
		}
		else {
			xajax_clear(rows);
			xajax_clear("new_"+rows);
			open_rows[rows][0]=0;
			open_rows[rows][1]=0;
		}
		return open_rows;
	}

	function openclose_edit(open_rows, rows, item){
		var open_rows=open_rows;
		var rows=rows;
		var type=1;
		var item=item;
		if (open_rows[rows][1]=='0'){
			xajax_edit(rows, item, 0, 1);
			open_rows[rows][1]=1;
			open_rows[rows][0]=0;
			xajax_notes(rows, item, type);
		}
		else {
			xajax_clear("new_"+rows);
			xajax_clear(rows);
			open_rows[rows][1]=0;
			open_rows[rows][0]=0;
		}
		return open_rows;
	}
	var arTRs = new Array();
	var imgExpand = new Image;
	var imgCollapse = new Image;
	imgExpand.src = './images/icons/expand.gif';
	imgExpand.alt = 'Mostrar';
	imgCollapse.src = './images/icons/collapse.gif';
	imgCollapse.alt = 'Ocultar';

	function show_hide_project(pro){
		if (document.getElementById(name)){
			var vis = document.getElementById(name).style.display;
			if (vis=='none'){
				document.getElementById(name).style.display = '';
				document.getElementById('img' + name).src ='./images/icons/collapse.gif';
				document.getElementById('img' + name).alt = 'Ocultar';
			}
			else{
	 			document.getElementById(name).style.display = 'none';
				document.getElementById('img' + name).src ='./images/icons/expand.gif';
				document.getElementById('img' + name).alt = 'Mostrar';
			}
		}
	}

	function show_hide_tasks(prj_id){
		var tb = document.getElementById("tbfiles");
		var vis = '';
		for(var i = 0; i < tb.rows.length; i++ ){
			if (tb.rows[i].parentNode.parentNode.id == "tbfiles" && tb.rows[i].id.indexOf('ptsk_'+prj_id+"_") > -1){
				vis = tb.rows[i].style.display;
				if(vis==""){
					vis = 'none';
				}else{
					vis = ''
				}
				tb.rows[i].style.display = vis;
			}
		}
		if (vis==""){
			var img = imgCollapse;
		}else{
			var img = imgExpand;
		}
		document.getElementById('imgprj_' + prj_id).src = img.src;
		document.getElementById('imgprj_' + prj_id).alt = img.alt;
	}

-->
</script>
<style type="text/css">
.private {color: red;}
.private a:link {color: red;}
.private a:visited {color: red;}
.protected {color: blue;}
.protected a:link {color: blue;}
.protected a:visited {color: blue;}
</style>
<?php /* FILES $Id: index_table.php,v 1.4 2009-07-27 17:28:02 nnimis Exp $ */

// Files modules: index page re-usable sub-table
GLOBAL $AppUI, $deny1, $m, $usersections, $customSections;

// load the following classes to retrieved denied records
include_once( $AppUI->getModuleClass( 'projects' ) );
include_once( $AppUI->getModuleClass( 'tasks' ) );
require_once( "./modules/timexp/report_to_items.php" );
require_once( "./classes/projects.class.php" );

if($project_id == "-1")
{
	$allowed[0] = 0;
}
else if($project_id >= "0")
{
	$allowed[$project_id] = $project_id;
}
else
{
	$project = new CProject();
	$allowed=$project->getAllowedRecords( $AppUI->user_id, 'project_id', 'project_id', null );
	
	$task = new CTask();
	$deny2 = $task->getDeniedRecords( $AppUI->user_id );
}

$Projects = new CProjects();
$Projects->loadTasks();
$task_id = $Projects->Tasks();

if(count($task_id > 0)){
	foreach($task_id as $taskArray){
		$taskFilter .= $taskArray["task_id"].", ";
	}
	
	$taskFilter .= "0";
}

$downImage = "<img src='./images/arrow-down.gif' border='0' alt='".$AppUI->_("Ascending")."'>";
$upImage = "<img src='./images/arrow-up.gif' border='0' alt='".$AppUI->_("Descending")."'>";
$orderImage = isset($_GET["revert"]) ? $upImage : $downImage;
$revertOrder = isset($_GET["revert"]) ? "" : "&revert=1";
$url = "?m=files";

if(!isset($_GET["orderby"])) $orderby = "file_order";
else $orderby = $_GET["orderby"];
if(isset($_GET["revert"])) $orderby .= " DESC";

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');
// SETUP FOR FILE LIST
$sql = "
SELECT files.*,
 if (file_description='', file_name, file_description) AS file_order,
 project_name,
 project_color_identifier,
 project_active,
 date_modified,
 files_category.name_".$AppUI->user_locale." AS file_category_name,
 articlesections.name AS file_section_name,
 file_section,
 is_protected,
 is_private
FROM files
LEFT JOIN projects ON project_id = file_project
LEFT JOIN articlesections ON articlesection_id = file_section
LEFT JOIN files_category ON category_id = file_category
LEFT JOIN permissions AS permart
  ON (
		(
			(permart.permission_grant_on='articles' AND permart.permission_item = -1) OR
			permart.permission_grant_on = 'all'
		) AND
		permart.permission_user = $AppUI->user_id
	)
,permissions AS permproj
WHERE
	permproj.permission_user = $AppUI->user_id AND
	permproj.permission_value <> 0 AND
	(
		permproj.permission_grant_on = 'all'
		OR (permproj.permission_grant_on = 'projects' AND permproj.permission_item = -1)
		OR (permproj.permission_grant_on = 'projects' AND permproj.permission_item = project_id)
	)
"
. "\nAND file_project IN (".(count( $allowed ) > 0 ? implode( ',', array_keys($allowed) ) : ''). ')'
. (count( $deny2 ) > 0 ? "\nAND file_task NOT IN (" . implode( ',', $deny2 ) . ')' : '')
. ($task_id ? "\nAND file_task in ( $taskFilter)" : '')
. ($file_category ? "\nAND file_category = $file_category" : '') //Filtro para las categorias
. ($file_section ? "\nAND file_section = $file_section" : '') //Filtro para las Secciones
. (!$file_section && $customSections ? (sizeof($usersections) > 0 ? "\nAND (file_section IN (". implode( ',', $usersections).") OR file_section = 0) " : "\nAND file_section = 0 ") : '') //Filtro para las Secciones
. ($_POST['mostrar_archivos_borrados']=='on' ? "" : "\n AND file_delete_pending = 0") //Filtro para los archivos pendientes de eliminacion
. ($AppUI->user_type != 1 ? "\n AND (is_private = 0 or (is_private = 1 and file_owner = $AppUI->user_id)) " : "")
."
GROUP BY file_id
ORDER BY $orderby
";

$file = array();
if ($canRead)
	$files = db_loadList( $sql );
?>

<table width="100%" border="0" cellpadding="2" cellspacing="0" class="" id="tbfiles">
<col width="7%">  <!-- Espacio vacio !-->
<col width="44%"> <!-- Espacio para descripcion !-->
<col width="10%"> <!-- Espacio para categoria !-->
<col width="7%">  <!-- Espacio para seccion !-->
<col width="10%"> <!-- Espacio para version !-->
<col width="2%">  <!-- Espacio para tamaño !-->
<col width="10%"> <!-- Espacio para fecha !-->
<tr class="tableHeaderGral">
	<th nowrap="nowrap" align="LEFT" colspan='1'></th>
	<th nowrap="nowrap" align="LEFT" class="tableHeaderText">
		<?php if(($_GET["orderby"] == "file_order") || (!isset($_GET["orderby"]))) echo $orderImage?>
	  	<a href="<?=$url?>&orderby=file_order<?=$revertOrder?>" class="">
		<?php echo $AppUI->_( 'Description' );?></a>
	</th>
	<th nowrap="nowrap" align="LEFT" class="tableHeaderText">
		<?php if($_GET["orderby"] == "file_category_name") echo $orderImage?>
	  	<a href="<?=$url?>&orderby=file_category_name<?=$revertOrder?>" class="">
		<?php echo $AppUI->_( 'File Category' );?></a>
	</th>
	<th nowrap="nowrap" align="LEFT" class="tableHeaderText">
		<?php if($_GET["orderby"] == "file_section_name") echo $orderImage?>
	  	<a href="<?=$url?>&orderby=file_section_name<?=$revertOrder?>" class="">
		<?php echo $AppUI->_( 'File Section' );?></a>
	</th>
	<th nowrap="nowrap" align="CENTER" class="tableHeaderText">
		<?php echo $AppUI->_( 'Version' );?>
	</th>
	<th nowrap="nowrap" align="LEFT" class="tableHeaderText">
		<?php if($_GET["orderby"] == "file_size") echo $orderImage?>
	  	<a href="<?=$url?>&orderby=file_size<?=$revertOrder?>" class="">
		<?php echo $AppUI->_( 'Size' );?></a>
	</th>
	<th nowrap="nowrap" align="LEFT" class="tableHeaderText">
		<?php if($_GET["orderby"] == "date_modified") echo $orderImage?>
	  	<a href="<?=$url?>&orderby=date_modified<?=$revertOrder?>" class="">
		<?php echo $AppUI->_( 'Date' );?></a>
	</th>
</tr>
<?php
$fp=-1;
$file_date = new CDate();


$intIndexTRs = 0;//indice de array para cada proyecto
$intTR = 0;//contador de filas
/*script js para ocultar filas y cambiar las imagenes*/
echo "<script language=\"javascript\">
		function hiddenfiles(arVar, pProjIndex, pImage){
			var tb = document.getElementById(\"tbfiles\");
			var bvisible = '';
			var bClosed = false;
			for(i in arVar[pProjIndex]){
				if(arVar[pProjIndex][i]){
					bvisible = 'none';
					arVar[pProjIndex][i] = false;
				}else{
					arVar[pProjIndex][i] = true;
				}
				tb.getElementsByTagName('tr')[i].style.display = bvisible;//fila archivo
				tb.getElementsByTagName('tr')[i-1].style.display = bvisible;//fila acciones
				tb.getElementsByTagName('tr')[i-2].style.display = bvisible;//fila separador
			}
			bvisible == 'none' ? bClosed = true : bClosed = false;
			hiddenfilesChangeImage(pImage, bClosed);
		}
		function hiddenfilesChangeImage(pImage, pClosedState){
			var strSrcExpandImage = imgExpand.src;
			var strSrcCollapseImage = imgCollapse.src;
			var tmpSrc = '';
			if(pClosedState){
				tmpSrc = strSrcExpandImage;
			}else{
				tmpSrc = strSrcCollapseImage;
			}

			pImage.src = tmpSrc;
		}
		var arTRs = new Array();
		var imgExpand = new Image;
		var imgCollapse = new Image;
		imgExpand.src = './images/icons/expand.gif';
		imgCollapse.src = './images/icons/collapse.gif';
		</script>";
/*end script js*/
echo "<script language='Javascript'>
			<!--
			open_rows=new Array(".count($files).");
			items=new Array(2);
			items[0]=0;
 			items[1]=0;
			for(i=0;i<".count($files).";i++) open_rows[i]=items;
			-->
		</script>";
$rows=0;
if(count($files))
{
	foreach ($files as $row)
	{
		if(strrpos($row["file_description"], "[EMBEDDED") === false)
		{
		$file_date = new CDate( $row['date_modified'] );

		if ($fp != $row["file_project"]) {
			if ($showProject && $row["project_name"]) {
				$s = '<tr>';
				$s .= '<td colspan="7" style="background-color:#'.$row["project_color_identifier"].'" style="border: outset 2px #eeeeee">';
				$s .= "<a href='#' onclick=\"javascript: show_hide_tasks('".$row['file_project']."');\"><img id='imgprj_".$row['file_project']."' src='./images/icons/collapse.gif' width='16' height='16' border='0'></a>";
				$s .= '<font color="' . bestColor( $row["project_color_identifier"] ) . '">'
				  . $row["project_name"] . '</font>';
				$s .= '</td></tr>';
				echo $s;
			}
		}
		$fp = $row["file_project"];
		$canEdit = CFile::canEdit($row["file_id"]);
		$canDelete = CFile::canDelete($row["file_id"]);
		$rows++;

		if($row["is_private"] == 1)
			$classNameTR = "class='private'";
		else
			if($row["is_protected"] == 1)
				$classNameTR = "class='protected'";
?>
<tr id="ptsk_<?php echo $row['file_project'] ?>_<?php echo $row["file_id"];?>" <?php if($row["is_private"] == 1) echo(" class=\"private\""); if($row["is_protected"] == 1) echo(" class=\"protected\""); ?>>
	<td nowrap="nowrap">
	<table border="0" cellpadding="0" cellspacing="0">
	<form action="" method="post">
		<input type="hidden" name="dosql" value="do_file_aed" />
		<input type="hidden" name="file_id" value="<?php echo $row["file_id"];?>" />
	<tr>
		<td>
			<a name="#row_$rows"></a>
			<a href="javascript: //" onclick="open_rows=openclose(open_rows, <?php echo $rows; ?>,'<? echo $row["file_id"]; ?>');">
				(<?php echo $row['file_comments']; ?>)
			</a>
		</td>
		<td>
			<a name="#row_$rows"></a>
			<a href="javascript: //" onclick="open_rows=openclose_edit(open_rows, <?php echo $rows; ?>,'<? echo $row["file_id"]; ?>');" >
				<img src='./images/icons/comment.gif' width='20' height='20' border='0' alt='<?php echo $AppUI->_('New Comment');?>'>
			</a>
		</td>
		<!-- <td>
			<a href="javascript: //" onclick="show_hide_block('show_<? echo $rows; ?>')" >
			<img src='./images/icons/comment.gif' width='20' height='20' border='0' alt='Show Comments'>
			</a>
		</td>-->
		
		<td nowrap="nowrap"  align="LEFT">
		<?php if(!getDenyEdit("timexp")) { ?>
				<a href='javascript:report_hours(<? echo $row["file_id"]; ?>,"");' >
				<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'></a>
			<?php } ?>
			<a href="javascript:popUp('index_inc.php?inc=./modules/files/show_versions.php&m=files&file_id=<?php echo $row["file_id"]?>')">
			<img src="./images/icons/lupa3.gif" alt="<?php echo $AppUI->_('Show Versions');?>" border="0" height="20" width="20"></a>
		<?php if ($canEdit && !$row["file_delete_pending"])
		{
			if(($row["is_protected"]==1 && $row["file_owner"] == $AppUI->user_id) || $AppUI->user_type==1 || $row['is_protected']==0){
				echo "\n".'<a href="./index.php?m=files&a=addedit&file_id=' . $row["file_id"] . '" title="'.$AppUI->_('edit file').'">';
				echo dPshowImage( './images/icons/edit_small.gif', '20', '20', $AppUI->_('edit file') );
				echo "</a>";
			}
		}

		if ($canDelete)
		{
			if ($row["file_delete_pending"])
				{
					echo '<input type="image" onclick="return validar_recuperacion_file()" src="./images/icons/log-notice.gif" title="'. $AppUI->_( 'Recover File' ).'" />
					<input type="hidden" name="recovery" value="1"" />';
				}
			else
			{
				echo '<input type="image" onclick="return validar_file_borrado()"  src="./images/icons/trash_small.gif" title="'. $AppUI->_( 'delete file' ).'" />
				<input type="hidden" name="del" value="1" />';
			}
		}
		
		$lastHistoryData = CFile::getLastActionHistory($row["file_id"], 2);

		if($lastHistoryData['history_action'] == 4)
		{
			$historyDate = new CDate($lastHistoryData['history_date']);
			$historyDataText = $lastHistoryData['fullname'].' '.$AppUI->_( 'on' ).' '.$historyDate->format($AppUI->getPref('SHDATEFORMAT').' '.$AppUI->getPref('TIMEFORMAT'));
			echo ("<img src=\"/images/sign.gif\" alt=\"".$historyDataText."\" border=\"0\" />");
		}		
		?>

		</td>


	</tr>
	</form>
	</table>
	</td>
	<td align="left">
		<?php

		$file_parts = pathinfo($row['file_name']);
		echo "<a href=\"./fileviewer.php?file_id={$row['file_id']}\" TITLE=\"{$row['file_name']}\">".
					dPshowImage( getImageFromExtension($file_parts["extension"]), '16', '16', $row['file_description'] ).
					"&nbsp;".
					($row["file_delete_pending"] ? "<font color='#ff0000'><s>" : "").
					( $row['file_description']!='' ? $row['file_description'] : $row['file_name'] ). //Si tiene comentario el archivo lo pongo, sino pongo el nombre
					($row["file_delete_pending"] ? "</s></font>" : "")."</a>"; ?>
	</td>
	<?php
			echo ($row["file_delete_pending"] ? "<font color='#ff0000'><s>" : "");
	?>
	<td nowrap="nowrap" align="LEFT">
		<?php
			echo ($row["file_delete_pending"] ? "<font color='#ff0000'><s>" : "");
			echo $AppUI->_($row['file_category_name']);
			echo ($row["file_delete_pending"] ? "</s></font>" : "");
		?>
	</td>
	<td nowrap="nowrap" align="LEFT">
		<?php
			echo ($row["file_delete_pending"] ? "<font color='#ff0000'><s>" : "");
			echo $row['file_section'] == -1 ? $AppUI->_('Top') : $row['file_section_name'];
			echo ($row["file_delete_pending"] ? "</s></font>" : "");
		?>
	</td>
	<td nowrap="nowrap" align="CENTER">
		<?php
			echo ($row["file_delete_pending"] ? "<font color='#ff0000'><s>" : "");
			echo get_file_last_version_with_del($row['file_id']);
			echo ($row["file_delete_pending"] ? "</s></font>" : "");
		?>
	</td>
	<!--<td nowrap="nowrap" align="LEFT">
		<?php
			echo ($row["file_delete_pending"] ? "<font color='#ff0000'>" : "");
			echo $row["user_first_name"].' '.$row["user_last_name"];
			echo ($row["file_delete_pending"] ? "</font>" : "");
		?>
	</td>!-->
	<td nowrap="nowrap" align="LEFT">
		<?php
			echo ($row["file_delete_pending"] ? "<font color='#ff0000'><s>" : "");
			echo size_hum_read($row["file_size"]);
			echo ($row["file_delete_pending"] ? "</s></font>" : "");
		?>
	</td>
	<td nowrap="nowrap" align="LEFT">
		<?php
			echo ($row["file_delete_pending"] ? "<font color='#ff0000'><s>" : "");
			echo $file_date->format( $df."<br/>".$tf );
			echo ($row["file_delete_pending"] ? "</s></font>" : "");
		?>
	</td>
<tr id="ptsk_<?php echo $row['file_project'] ?>_<?php echo $row["file_id"];?>_1"><td colspan='7'><span id='new_<?php echo $rows; ?>'></span></td></tr>
<tr id="ptsk_<?php echo $row['file_project'] ?>_<?php echo $row["file_id"];?>_2"><td colspan='7'><span id='<?php echo $rows; ?>'></span></td></tr>
<tr id="ptsk_<?php echo $row['file_project'] ?>_<?php echo $row["file_id"];?>_3" class="tableRowLineCell"> <!-- La linea que divide los proyectos -->
    <td colspan="7"></td>
</tr>
<?php
	}
}
}

// check permissions for files module
$canEditFile = !getDenyEdit( "files" );
if ($canEditFile && $m!="files" && $m!="projects"){
?>
<tr>
    <td colspan="7" align="right"><?php
    $url = "?m=files&a=addedit".($project_id >= "0" ?"&project_id=$project_id":"").($task_id?"&file_task=$task_id":"");
    echo '<input type="button" class="button" value="'.$AppUI->_('new file');
    echo '" onclick="document.location=\''.$url.'\';">'
    ?>
    </td>

</tr>
<?php } ?>
</form>
</table>



<?php
/*
echo "<pre>";
echo "Allowed Projects\n";
var_dump($allowed);
echo "Denied Tasks\n";
var_dump($deny2);
echo "Sql:\n";
var_dump($sql);
echo "</pre>";
*/
?>

<script language="javascript">

	function validar_recuperacion_version()
	{
		return confirm( "<?php echo $AppUI->_('versionRecovery');?>" );
	}

	function validar_recuperacion_file()
	{
		return confirm( "<?php echo $AppUI->_('fileRecovery');?>" );
	}

	function validar_file_borrado()
	{
			return confirm( "<?php echo $AppUI->_('filesDelete');?>" );
	}

	function popUp(URL)
	{
		day = new Date();
		id = day.getTime();
		eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0 ,scrollbars=yes, location=0, statusbar=0, menubar=0, resizable=1, width=900, height=500');");
	}
</script>
