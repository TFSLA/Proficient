<?php  /* $Id: viewsection.php,v 1.11 2009-11-02 15:47:37 nnimis Exp $ */ ?>
<script type="text/javascript">
<!--
	var open_rows;
	var rows;
	function openclose(open_rows, rows, item, type){
		var open_rows=open_rows;
		var rows=rows;
		var item=item;
		var type=type;
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

	function openclose_edit(open_rows, rows, item, type){
		var open_rows=open_rows;
		var rows=rows;
		var type=type;
		var item=item;
		if (open_rows[rows][1]=='0'){
			xajax_edit(rows, item, 0, type);
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
-->
</script>

<?php
global $canEdit, $AppUI;

require_once( $AppUI->getModuleClass( 'files' ) );
require_once( $AppUI->getModuleClass('projects') );
require_once( './modules/timexp/report_to_items.php' );

$AppUI->savePlace();

if (!function_exists('cutString')){
	function cutString($pString, $pLengthToShow=35, $pShowLastChars=4){
		$valReturn = $pString;
		$intPoints = 3;
		$strChar = ".";

		if(strlen($pString) > $pLengthToShow){
			$strTmp = substr($pString, 0, ($pLengthToShow - $intPoints - $pShowLastChars));
			for($i=0; $i < $intPoints; $i++){
				$strTmp .= $strChar;
			}
			if($pShowLastChars > 0){
				$strTmp .= substr($pString, - $pShowLastChars);
			}
			$valReturn = $strTmp;
		}

		return $valReturn;
	}
}
?>

<SCRIPT LANGUAGE="JavaScript">
//<!-- Begin
function popUp(URL) {
var day = new Date();
var id = day.getTime();

eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0 ,scrollbars=1, location=0, statusbar=0, menubar=0, resizable=0, width=850, height=660, left=10, top=10');");
}
// End -->
</script>

<SCRIPT LANGUAGE="JavaScript">
//<!-- Begin

function submitFm(campo, valor){
	var f = document.form_viewsection;
	var campo_form = campo.value;

	switch(campo)
	{
		case 'articlesection_id':
		   f.articlesection_id.value = valor.value;

		   if (f.articlesection_id.value == "undefined")
		   {
		   	f.articlesection_id.value = valor;
		   }
		break;
		
		case 'articletype_id':
		   f.articletype_id.value = valor.value;
		break;
	}

	f.submit();
}

function delFiles( x, y ) {
	var form = document.frmDelete_file;

	if (confirm( "<?php echo $AppUI->_('doDelete'); ?> " + y + "?" ))
	{
		form.file_id.value = x;
		form.submit();
	}
}

function delArticle( x, y ) {
	if (confirm( "<?php echo $AppUI->_('doDelete'); ?> " + y + "?" )) {
		document.frmDelete.article_id.value = x;
		document.frmDelete.submit();
	}
}
// End -->
</script>

<style type="text/css">

.private {color: red;}
.private a:link {color: red;}
.private a:visited {color: red;}
.protected {color: blue;}
.protected a:link {color: blue;}
.protected a:visited {color: blue;}

</style>

<? if(count($result)!="0")
   {
    echo "<br>&nbsp;&nbsp;<b>$AppUI->_('Read our articles'):</b><br>";
   }

$order_by=null;

$hoy = date("Y-m-d",mktime(0,0,0,date("m"),date("d")+1,  date("Y")));
$now = date("YmdHis");
$lastmonth = date("Y-m-d",mktime(0,0,0,date("m")-1,date("d"),  date("Y")));
$lastmothFormatted = date("YmdHis",mktime(0,0,0,date("m")-1,date("d"),  date("Y")));

//Filtro para ARTICULOS
if($AppUI->state["ArticleIdxTab"]=="0"){
	if($order_by==null){
		$order_by = " ORDER BY date_modified desc ";
	}
}

//Filtro para NOVEDADES
if($AppUI->state["ArticleIdxTab"]=="1"){
	$news = "AND date >= '$lastmonth ' AND date <= '$hoy' ";
	$news_file = "AND file_date >= '$lastmonth ' AND file_date <= '$hoy' ";
	$order_by = "ORDER BY date_modified DESC";
}

//Filtro para ULTIMOS COMENTADOS
if($AppUI->state["ArticleIdxTab"]=="2"){
	$selectData = " K.know_base_date AS kbd, MAX(K.know_base_date) as MaxDate,";

	$lastCommented = " AND K.know_base_date >= '$lastmothFormatted' AND K.know_base_date <= '$now' ";
	$lastCommented .= " AND K.know_base_type = 2 ";

	$Join = "LEFT JOIN  know_base_note AS K ON K.know_base_item_id = A.article_id";
	$JoinFiles = "LEFT JOIN  know_base_note AS K ON K.know_base_item_id = file_id";

	$lastCommented_file = " AND K.know_base_date >= '$lastmothFormatted' AND K.know_base_date <= '$now' ";
	$lastCommented_file .= " AND K.know_base_type = 1 ";
	
	//$tablaKB = ", know_base_note AS K";
	
	if($order_by==null){
		$order_by = " ORDER BY MaxDate desc ";
	}
}

//Filtro para ULTIMOS MODIFICADOS
if($AppUI->state["ArticleIdxTab"]=="3"){
	$lastModified = "AND date_modified >= '$lastmonth ' AND date_modified <= '$hoy' AND date <> date_modified ";
	$lastModified_file = "AND date_modified >= '$lastmonth ' AND date_modified <= '$hoy' AND file_date <> date_modified ";	

	if($order_by==null){
		$order_by = " ORDER BY date_modified desc ";
	}
}

if(isset($_REQUEST['order'])){
	switch($_REQUEST['order']){
		case "date":
		  $order_by = "ORDER BY date_modified" ;
		break;
		case "title":
		  $order_by = "ORDER BY title" ;
		break;
		case "owner":
		  $order_by = "ORDER BY article_creator" ;
		break;
		default:
		  $order_by = "ORDER BY date_modified" ;
	}
	
	if(!isset($_GET["revert"])) $order_by .= " DESC";
	
}else{
	if($AppUI->state["ArticleIdxTab"]=="1"){
		$order_by = "ORDER BY date_modified DESC";
		$_GET["order"] = "date";
	}
	elseif ($AppUI->state["ArticleIdxTab"]=="2")
		$order_by = "ORDER BY MaxDate DESC";
	elseif ($AppUI->state["ArticleIdxTab"]=="3")
		$order_by = "ORDER BY date_modified DESC";
	else{
		$_GET["order"] = "date";
		$order_by = "ORDER BY date_modified DESC";
	}
}

if(isset($_REQUEST["txt_search"])!=""){
	$busca = $_REQUEST["txt_search"];
	$busca = ereg_replace("'"," ",$busca);
	$where = " and (title like '%$busca%' or abstract like '%$busca%' or body like '%$busca%') ";
    $where_file = " and (file_description like '%$busca%' or file_name like '%$busca%') ";
}

if($_REQUEST['articlesection_id']!="")
{
	$articlesection_id = $_REQUEST["articlesection_id"];
}
else
{
	// Si el id de la seccion esta vacio y el id de seccion guardado en la session es distinto de 1 trae la seccion top (-1)
	if($_REQUEST['articlesection_id']=="" && $_POST['txt_search'] =="" && $AppUI->state['ArticleIdxTab']!="1")
	{
        if ($_REQUEST['articlesection_id']!='')
        {
			$articlesection_id = $_REQUEST['articlesection_id'];
		}
		else
		{
			if($AppUI->state['ArticleIdxTab'] == "1" || $AppUI->state['ArticleIdxTab'] == "2" || $AppUI->state['ArticleIdxTab'] == "3")
			{
				$articlesection_id = "0";
			}
			else
			{
				$articlesection_id = "-1";
			}
		}
	}
}

if(($articlesection_id!="")&&($articlesection_id!="0"))
{
	$filtro_articlesection = " and articlesection_id='$articlesection_id'";
	$filtro_articlesection_file = " and file_section ='$articlesection_id'";
}

if($articlesection_id == "0")
{
	$filtro_articlesection = " and articlesection_id <>'$articlesection_id'";
}

if($_REQUEST["articletype_id"] != '')
{
	$articletype_id = $_REQUEST["articletype_id"];
}else{
	$articletype_id = $_REQUEST["type"];
}

if($articletype_id != "")
{

  if($articletype_id=="0" || $articletype_id=="1")
	{
		$filtro_type = " and type='$articletype_id'";
		$filtro_type_file = " and file_id ='-1' ";
	}
	else
	{
		$filtro_type = " and type='-1' ";
	}
}

if($AppUI->user_type != 1)
{
	$privateFilterArticle = " and (A.is_private = 0 OR (A.is_private = 1 AND A.user_id = ".$AppUI->user_id.")) ";
	$privateFilterFile = " and (is_private = 0 OR (is_private = 1 AND file_owner = ".$AppUI->user_id.")) ";
}

$sql = "SELECT * FROM ( 
		( SELECT DATE_FORMAT(date,'%Y%m%d') AS ord,
			A.articlesection_id,
			A.user_id,
			A.article_id,
			A.title,
			A.type,
			A.abstract,
			A.date_modified,
			DATE_FORMAT(date,'%d/%m/%Y') as datefmt,
			A.article_comments AS comments,
			A.is_protected,
			A.is_private,
			$selectData
			CONCAT(u.user_last_name, ' ', u.user_first_name) AS article_creator
FROM articles AS A$tablaKB
LEFT JOIN users AS u ON A.user_id = u.user_id
$Join
WHERE 1=1
AND articlesection_id <> '0'
$where
$filtro_articlesection
$filtro_type
$news
$lastCommented
$lastModified
$privateFilterArticle
 GROUP BY A.article_id)";

$sql .= "UNION (
			SELECT DATE_FORMAT(file_date,'%Y%m%d') AS ord,
			file_section,
			file_owner,
			file_id,
			file_description,
			file_type,
			file_name,
			date_modified,
			DATE_FORMAT(file_date,'%d/%m/%Y') as datefmt,
			file_comments AS comments,
			is_protected,
			is_private,
			$selectData
			CONCAT(u.user_last_name, ' ', u.user_first_name) AS article_creator
FROM files$tablaKB
LEFT JOIN users AS u ON file_owner = u.user_id
$JoinFiles
WHERE 1=1 AND
file_delete_pending=0 AND
file_section <> '0'
$where_file
$filtro_articlesection_file
$filtro_type_file
$news_file
$lastCommented_file
$lastModified_file
$privateFilterFile
 GROUP BY files.file_id) ) AS T ";

$sql .= $order_by;

//echo "<pre>$sql</pre>";
$dp = new DataPager_post($sql, "form_viewsection");
$dp->showPageLinks = true;
$result = $dp->getResults();
$rn = $dp->num_result;
$pager_links = $dp->RenderNav_post("form_viewsection");

$downImage = "<img src='./images/arrow-down.gif' border='0' alt='".$AppUI->_("Ascending")."'>";
$upImage = "<img src='./images/arrow-up.gif' border='0' alt='".$AppUI->_("Descending")."'>";
$orderImage = !isset($_GET["revert"]) ? $upImage : $downImage;
$revertOrder = isset($_GET["revert"]) ? "" : "&revert=1";
?>
    <form name="form_viewsection" method="POST">

       <input type="hidden" name="m" value="articles" >

       <!-- Variables para paginacion -->
       <input type="hidden" name="form_viewsection_next_page" value="" >

       <!-- Variables usadas para los filtros -->
       <input type="hidden" name="articlesection_id" value="<?=$articlesection_id ?>">
       <input type="hidden" name="articletype_id" value="<?=$articletype_id?>">

       <!-- Ordenamiento -->
       <input type="hidden" name="order" value="<?=$_GET["order"]?>">

       <!-- Busqueda -->
       <input type="hidden" name="txt_search" value="<?=$_POST['txt_search']?>">
    </form>

    <form name="frmDelete" action="./index.php?m=articles&a=admin" method="post">
		<input type="hidden" name="dosql" value="do_article_aed" />
		<input type="hidden" name="del" value="1" />
		<input type="hidden" name="article_id" value="0" />
	</form>

    <form name="frmDelete_file" method="post">
		<input type="hidden" name="dosql" value="do_file_aed" />
		<input type="hidden" name="del" value="1" />
		<input type="hidden" name="file_id" value="" />
    </form>

    <table cellpadding="2" cellspacing="0" border="0" width="100%" >
      <tr class="tableHeaderGral">
      	<th colspan='2'></th>
      	<th></th>
			<th align="left" >
				<?php if(($_GET["order"] == "date") ) echo $orderImage?>
				<a href="?m=articles&order=date&articlesection_id=<?=$articlesection_id.$revertOrder;?>&articletype_id=<?=$articletype_id?>">
				<font color="#FFFFFF"><?php echo $AppUI->_('Date');?></font></a>
			</th>
			<th align="left" colspan='2'>
				<?php if($_GET["order"] == "title") echo $orderImage?>
				<a href="?m=articles&order=title&articlesection_id=<?=$articlesection_id.$revertOrder;?>&articletype_id=<?=$articletype_id?>">
				<font color="#FFFFFF"><?php echo $AppUI->_('Article\'s Title');?></font></a>
			</th>
			<th align="left">
				<?php if($_GET["order"] == "owner") echo $orderImage?>
				<a href="?m=articles&order=owner&articlesection_id=<?=$articlesection_id.$revertOrder;?>&articletype_id=<?=$articletype_id?>">
				<font color="#FFFFFF"><?php echo $AppUI->_('Owner');?></font></a>
			</th>
			<th align="left" width='1'>
			    <select name="articlesection_id" class="text" onchange="submitFm('articlesection_id',this)">
			   	<option value="0" <? if($articlesection_id =="")echo "Selected"; ?>><?php echo $AppUI->_('All sections');?></option>

					<?
						$query = "SELECT articlesection_id,name
						  FROM articlesections ";

						 $results = db_loadHashList($query);
						 $results['-1'] = "Top";
		                 asort($results);

		                 foreach($results as $key => $value){
		                   echo '<option ';
						   if($articlesection_id==$key) echo "selected";
						   echo ' value="'.$key.'">'.$value.'</option>';
		                 }
					?>
				</select>
			</th>
			<th align="left" width='1'>
			     <select name="articletype_id" class="text" onchange="submitFm('articletype_id',this)">
				    <option value=""><?php echo $AppUI->_('All types');?></option>
					<option value="0" <? if($articletype_id=="0") echo "selected";?> ><?php echo $AppUI->_('Article');?></option>
					<option value="1" <? if($articletype_id=="1") echo "selected";?> ><?php echo $AppUI->_('Link');?></option>
					<option value="2" <? if($articletype_id=="2") echo "selected";?> ><?php echo $AppUI->_('File');?></option>
				 </select>
			</th>
		</tr>
<?
//REGISTROS MOSTRADOS


$rows=0;
foreach ($result as $row){
	
	if(strrpos($row["title"], "[EMBEDDED") === false)
	{
	$result2 = mysql_query("SELECT * FROM articlesections WHERE articlesections.articlesection_id = '{$row["articlesection_id"]}';");
	if(mysql_num_rows($result2)>0){
		$row2 = mysql_fetch_array($result2, MYSQL_ASSOC);
		$secname=$row2["name"];
	}
	else $secname="Top";
	IF($row["type"]!='')$kbn_type=2;
	ELSE $kbn_type=1;
?>
	<tr <?php if($row["is_private"] == 1) echo("class=\"private\""); if($row["is_protected"] == 1) echo(" class=\"protected\""); ?>>
		<td width='1'>
			<a name="#row_$rows"></a>
			<a href="javascript: //" onclick="open_rows=openclose(open_rows, <?php echo $rows; ?>,<? echo $row['article_id']; ?>,<? echo $kbn_type; ?> );" >
				(<?php echo $row['comments']; ?>)
			</a>
		</td>
		<td width='1'>
			<a name="#row_$rows"></a>
			<a href="javascript: //" onclick="open_rows=openclose_edit(open_rows, <?php echo $rows; ?>,<? echo $row['article_id']; ?>,<? echo $kbn_type; ?>);" >
				<img src='./images/icons/comment.gif' width='20' height='20' border='0' alt='<?php echo $AppUI->_('New Comment');?>'>
			</a>
		</td>

		<td nowrap>
		
		<?

		if(checkUserPermission()){

			if(checkPermission($row['article_id'],$row["type"])){

			//EDITAR

				if($row["type"]=="0")
				{
				?>
				<?php 
					if(!getDenyEdit("timexp")) {
						?>
						<a href='javascript:report_hours(<? echo $row['article_id']; ?>,<? echo $row['type']; ?>);' >
						<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'></a>
					<?php
					}
				echo "<a href=\"./index.php?m=articles&a=addeditarticle&sec_id=$articlesection_id&article_id=".$row["article_id"]."\" title=\"".$AppUI->_('edit')."\">";
				echo dPshowImage( './images/icons/edit_small.gif', 20, 20, '' );
				echo "</a>";
				}elseif($row["type"]=="1")
				{
					if(!getDenyEdit("timexp")) {
					?>
						<a href='javascript:report_hours(<? echo $row['article_id']; ?>,<? echo $row['type']; ?>);' >
						<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'></a>
				<?php
					}
				echo "<a href=\"./index.php?m=articles&a=addeditlink&id=".$row["article_id"]."\" title=\"".$AppUI->_('edit')."\" >";
				echo dPshowImage( './images/icons/edit_small.gif', 20, 20, '' );
				echo "</a>";
				}else{
					if(!getDenyEdit("timexp")) {
					?>
						<a href='javascript:report_hours(<? echo $row['article_id']; ?>,-1);' >
						<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'></a>
					<?php
					}
				}


			//ELIMINAR

				if(($row["type"]!="0" && $row["type"]!="1"))
				{
					echo "<a href=\"./index.php?m=files&a=addedit&file_id=".$row["article_id"]."\" title=\"".$AppUI->_('edit')."\">";
				    echo dPshowImage( './images/icons/edit_small.gif', 20, 20, '' );
				    echo "</a>";
				 }
				?>
				<?
					if(($row["type"]=="0" || $row["type"]=="1"))
					{ 	
						if (CArticle::canDelete($row['article_id']))
						{?>
								<a href="javascript:delArticle(<?php echo $row["article_id"];?>, '\'<?php echo $row["title"];?>\'')" title="<?php echo $AppUI->_('delete');?>"><?php echo dPshowImage( './images/icons/trash_small.gif', 16, 16, '' ); ?></a>
				<?		}
					}
					else
					{	
						if(CFile::canDelete($row['article_id']))
						{	?>
							<a href="javascript:delFiles(<?php echo $row["article_id"];?>, '\'<?php echo $row["title"];?>\'')" title="<?php echo $AppUI->_('delete');?>" ><?php echo dPshowImage( './images/icons/trash_small.gif', 16, 16, '' ); ?></a>
							
				<?		}
					}
			}
		}
		if($row["type"]!="0" && $row["type"]!="1"){
		?>
		<a href="javascript:popUp('index_inc.php?inc=./modules/files/show_versions.php&m=files&file_id=<?php echo $row["article_id"]?>')">
		<img src="/images/icons/lupa3.gif" alt="<?php echo $AppUI->_('Show Versions');?>" border="0" height="20" width="20"></a>
		<?php } ?>
		<?		
			$articleType = $row["type"] != '' ? $row["type"] : 2;
			$lastHistoryData = CArticle::getLastActionHistory($row["article_id"], $articleType);
		
			if($lastHistoryData['history_action'] == 4)
			{
				$historyDate = new CDate($lastHistoryData['history_date']);
				$historyDataText = $lastHistoryData['fullname'].' '.$AppUI->_( 'on' ).' '.$historyDate->format($AppUI->getPref('SHDATEFORMAT').' '.$AppUI->getPref('TIMEFORMAT'));
				echo ("<img src=\"/images/sign.gif\" alt=\"".$historyDataText."\" border=\"0\" />");
			}
		?>
		</td>

		<td align="left" width='140'>
			<?
			$datemod = new CDate($row["date_modified"]);
			echo ($datemod->format($AppUI->user_prefs['SHDATEFORMAT'].'<br/>'.$AppUI->user_prefs['TIMEFORMAT']));
			?>
		</td>
		<td width='1'>&nbsp;</td>
		<td align="left">
			<?
			if($row["type"]=="0") echo '<a href="javascript:popUp(\'index_inc.php?inc=./modules/articles/viewarticle.php&m=articles&id='.$row["article_id"].'\')">'.$row["title"].'</a>';
			if($row["type"]=="1") echo '<a href="javascript:popUp(\'index_inc.php?inc=./modules/articles/vwlink.php&m=articles&id='.$row["article_id"].'\')"> '.$row["title"].'</a>';
			if($row["type"]!="0" && $row["type"]!="1"){
				$file_parts = pathinfo($row['abstract']);
				echo "<a href=\"javascript:popUp('index_inc.php?inc=./modules/files/show_versions.php&m=files&file_id=".$row['article_id']."')\">".dPshowImage( getImageFromExtension($file_parts["extension"]), '16', '16', $row['abstract'] )."&nbsp;". ($row['title'] != '' ?  $row['title'] :  $row['abstract']) ."</a>";
			}
		?>
		</td>
		<td align="left" width='15%'>
			<?php echo $row["article_creator"];	?>
		</td>
		<td align="left">
			<?php echo $secname;?>
		</td>
		<td align="left">
			<?php
		    switch($row["type"]){
				case "0":
			       echo $AppUI->_('Article');
				break;
				case "1":
				   echo $AppUI->_('Link');
				break;
				default:
				   echo $AppUI->_('File');
				break;
			}
		?>
		</td>
	</tr>
 	<tr><td colspan='9'><span id='new_<?php echo $rows; ?>'></span></td></tr>
	<tr><td colspan='9'><span id='<?php echo $rows; ?>'></span></td></tr>
  <tr class="tableRowLineCell"><td colspan='9'></td></tr>
  <?
  $rows++;
}
}

echo "<script language='Javascript'>
			<!--
			open_rows=new Array(".count($result).");
			items=new Array(2);
			items[0]=0;
 			items[1]=0;
			for(i=0;i<".count($result).";i++) open_rows[i]=items;
			-->
		</script>";

if(count($result)=="0")
{
	echo "<tr><td colspan=\"8\" align=\"center\">".$AppUI->_('Search not found')."</td></tr>";
}
?>

</table>

<table border='0' width='100%' cellspacing='0' cellpadding='1'>
	<tr bgcolor="#E9E9E9">
		<td align='center'><? echo $pager_links; ?></td>
	</tr>
	<tr>
		<td height="1" colspan="6" bgcolor="#E9E9E9"></td>
	</tr>
</table>

<?php

//Verifica los permisos de usuarios sobre el módulo
function checkUserPermission(){
	global $AppUI;

	if($AppUI->user_type == 1) return true;

	$sql = "SELECT * FROM permissions WHERE permission_grant_on = 'articles' AND permission_value = -1 AND permission_user = ".$AppUI->user_id;
	$result = mysql_query($sql);

	if(mysql_num_rows($result) > 0) {return true;}
	else {return false;}
}


//Verifica los permisos de usuarios sobre elementos
function checkPermission($ID,$type){
	global $AppUI;

	if($AppUI->user_type == 1) return true;

	if(($type==0 || $type==1) && $type != null){
		$table = 'articles';
		$user = 'user_id';
		$field = 'article_id';
	}else{
		$table = 'files';
		$user = 'file_owner';
		$field = 'file_id';
	}

	$sql = "SELECT * FROM $table WHERE $field = $ID";

	$result = mysql_query($sql);
	$row=mysql_fetch_array($result);

	if($row['is_protected']==0){
		return true;
	}else{
		if($row[$user]==$AppUI->user_id){
			return true;
		}else{
			return false;
		}
	}
}
?>