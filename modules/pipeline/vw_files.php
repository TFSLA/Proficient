
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

		function delFiles( x, y ) {
		var form = document.frmDelete_file;

		if (confirm( "<?php echo $AppUI->_('doDelete'); ?> " + y + "?" )) {
			form.file_id.value = x;
			form.submit();
		}
	}
	
	function edit_art(id_art){
	  var form = document.frmEdit_art;
	  form.article_id.value = id_art;

	  form.submit();
	}

	function edit_link(id_link){
	  var form = document.frmEdit_link;
	  form.article_id.value = id_link;

	  form.submit();
	}

	function edit_file(id_file){
	  var form = document.frmEdit_file;
	  form.file_id.value = id_file;

	  form.submit();
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


<?php

GLOBAL $AppUI, $canEdit, $tpl_stub, $tpl_where, $tpl_orderby, $lead_id, $xajax;
require_once("./modules/timexp/report_to_items.php");

require_once($AppUI->getModuleClass('projects'));
require_once($AppUI->getModuleClass('articles'));

$accion = intval( dPgetParam( $_POST, "accion", 0 ) );
$AppUI->setState( 'PipeLineVwTab', 0);
$tab = $AppUI->getState('PipeLineVwTab');


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


if(isset($_POST['accion']))
{
 $accion = $_POST['accion'];
}
else{
	if(isset($_GET['accion']))
	{
	 $accion = $_GET['accion'];
	}
}

switch($_GET['order']){
	case "date":
	  $order_by = "ORDER BY ord desc" ;
	break;
	case "title":
	  $order_by = "ORDER BY title asc,ord desc" ;
	break;
	case "owner":
	  $order_by = "ORDER BY user_id desc,ord desc" ;
	break;
	default:
	  $order_by = "ORDER BY ord desc" ;
}


if(isset($_POST["txt_search"])!="")
{
	$busca = $_POST["txt_search"];

	$where = " and (title like '%$busca%' or abstract like '%$busca%' or body like '%$busca%') ";
	$where_file = " and (file_description like '%$busca%' or file_name like '%$busca%') ";
}

if($_GET['articletype_id']!="")
{
    if($_GET['articletype_id']=="0" || $_GET['articletype_id']=="1")
	{
	$filtro_type = " and type='".$_GET['articletype_id']."'";
	$filtro_type_file = " and file_id ='-1' ";
	}
	else{
	$filtro_type = " and type='-1' ";
	}
}

if($AppUI->user_type != 1)
{
	$privateFilterArticle = " and (is_private = 0 or (is_private = 1 and user_id = ".$AppUI->user_id.")) ";
	$privateFilterFile = " and (is_private = 0 or (is_private = 1 and file_owner = ".$AppUI->user_id.")) ";
}

$sql = "(
				SELECT
					DATE_FORMAT(date,'%Y%m%d %H%i%s') AS ord,
					file_category,
					user_id,
					article_id,
					title,
					type,
					abstract,
					date_modified,
					DATE_FORMAT(date,'%d/%m/%Y') as datefmt,
					article_comments AS comments,
					is_protected,
					is_private
				FROM articles
				WHERE (opportunity = $lead_id)
					$where
					$filtro_type
					$privateFilterArticle)";

$sql .= "UNION
				(SELECT
					DATE_FORMAT(file_date,'%Y%m%d %H%i%s') AS ord,
					file_category,
					file_owner,
					file_id,
					file_description,
					file_type,
					file_name,
					date_modified,
					DATE_FORMAT(file_date,'%d/%m/%Y') as datefmt,
					file_comments AS comments,
					is_protected,
					is_private
				FROM files
				WHERE
					(file_opportunity = $lead_id AND file_delete_pending = 0)
					$where_file
					$filtro_type_file
					$privateFilterFile) ";

$sql .= $order_by;

$dp = new DataPager($sql, "art");
$dp->showPageLinks = true;
$articles = $dp->getResults();

$rn = $dp->num_result;
$pager_links = $dp->RenderNav();

?>

<script language="javascript">
function delArticle( x, y ) {
	var form = document.frmDelete_art;

	if (confirm( "<?php echo $AppUI->_('doDelete');?> " + y + "?" )) {
		form.article_id.value = x;
		form.submit();
	}
}


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

	if(error){
		form.submit();
	}
}


function back(){
  var form = document.addedit_link;
  form.accion.value = "view";

  form.submit();


}

function popUp(URL) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0 ,scrollbars=1, location=0, statusbar=0, menubar=0, resizable=0, width=900, height=660, left=10, top=10');");
}


</script>

<form name="frmDelete_art" action="./index.php?m=articles&a=admin" method="post">
	<input type="hidden" name="dosql" value="do_article_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="article_id" value="0" />
</form>
<form name="frmDelete_file" method="post">
	<input type="hidden" name="dosql" value="do_file_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="file_id" value="" />
</form>

<form name="frmEdit_art"  method="post">
	<input type="hidden" name="accion" value="add">
	<input type="hidden" name="article_id" value="">
	<input type="hidden" name="origen" value="project">
</form>

<form name="frmEdit_file"  method="post">
	<input type="hidden" name="accion" value="add_file">
	<input type="hidden" name="file_id" value="">
	<input type="hidden" name="origen" value="project">
</form>

<form name="frmEdit_link"  method="post">
	<input type="hidden" name="accion" value="add_link">
	<input type="hidden" name="article_id" value="">
	<input type="hidden" name="origen" value="project">
</form>
<?

switch($accion){
	case 'view':
		view_list($articles,$tab,$pager_links,$sec_id);
	break;
	case 'add':
		add_article();
	break;
	case 'add_file':
		add_file();
	break;
	case 'add_link':
		add_link('0',$_POST[sec_id]);
	break;
	case 'edit_link':
		add_link($_GET[article_id],'');

		break;
	case 'save_link':
       if ($_POST[article_id]=="0"){

		   $ts = time();
		   $date = date("Y-m-d",$ts);

		   $query = "INSERT INTO articles (articlesection_id, file_category, date, articles_reads, user_id, title, abstract, body, project, task, type) VALUES ('".$_POST['articlesection_id']."','".$_POST['file_category']."' ,'".$date."', '0', '".$AppUI->user_id."', '".$_POST['description']."', '".$_POST['href']."','".$_POST['abstract']."','".$_POST['project']."','".$_POST['task']."','1')";
	   }
       else{

		   $ts = time();
		   $date = date("Y-m-d",$ts);

		   $query = "UPDATE articles SET articlesection_id= '".$_POST['articlesection_id']."',date='".$date."',user_id='".$AppUI->user_id."',title='".$_POST['description']."',abstract='".$_POST['href']."'  WHERE article_id='".$_POST['article_id']."' ";
	   }

	   $sql = mysql_query($query);
       view_list($articles,$tab,$pager_links,$sec_id);
	break;
	case 'search_txt"':
		view_list($articles,$tab,$pager_links,$sec_id);
	break;
	default:
		view_list($articles,$tab,$pager_links,$sec_id);
}

function view_list($articles,$tab,$pager_links,$sec_id){

   global $AppUI,$canEdit;

?>
		<table cellpadding="4" cellspacing="0" border="0" width="100%" >
		<tr>

			<td colspan="5" nowrap="nowrap">
			<form method="POST">
				<input type="hidden" name="accion" value="search_txt">
			 	<input  class="text" size="30" type="text" name="txt_search" >
			  <input type="submit" class="button" value="<?=$AppUI->_('search');?>" >
			 </td>
			</form>
			<td colspan="3" align="right">
			 <table cellpadding="0" cellspacing="0" border="0"  >
			   <tr>

				  <form name="frmAdd_file"  method="post">
				   <input type="hidden" name="accion" value="add_file">
				   <input type="hidden" name="sec_id" value="<?=$sec_id;?>">
				   <td align="right">
				   	&nbsp;&nbsp;
						<input type="submit" class="button" value="<?=$AppUI->_('new file');?>">
				   </td>
			 	  </form>

				  <form name="frmAdd_link"  method="post">
				   <input type="hidden" name="accion" value="add_link">
				   <input type="hidden" name="sec_id" value="<?=$sec_id;?>">
				   <td  align="right">
				   	&nbsp;&nbsp;
						<input type="submit" class="button" value="<?=$AppUI->_('new link');?>">
				   </td>
			 	  </form>

			    <form name="frmAdd_art"  method="post">
			    	<input type="hidden" name="accion" value="add">
				 		<input type="hidden" name="sec_id" value="<?=$sec_id;?>">
				   <td  align="right" >
				    &nbsp;&nbsp;
						<input type="submit" class="button" value="<?=$AppUI->_('new article');?>">
				   </td>
			   	</form>
			   </tr>
			 </table>
			</td>
		</tr>

		<tr class="tableHeaderGral">
			<td width='1' colspan='2'></td>
			<td width="45" align="left">
				&nbsp;
			</td>
			<th align="left">
				<a href="?m=pipeline&a=addedit&lead_id=<?echo $lead_id;?>&tab=<? echo $tab;?>&order=date&articletype_id=<?=$_GET['articletype_id'];?>"><font color="#FFFFFF"><?php echo $AppUI->_('Date');?></font></a>
			</th>
			<th align="left" width="90%">
				<a href="?m=pipeline&a=addedit&lead_id=<?echo $lead_id;?>&tab=<? echo $tab;?>&order=title&articletype_id=<?=$_GET['articletype_id'];?>"><font color="#FFFFFF"><?php echo $AppUI->_('Title');?></font></a>
			</th>
			<th align="left" width="1%">
				<a href="?m=pipeline&a=addedit&lead_id=<?echo $lead_id;?>&tab=<? echo $tab;?>&order=owner&articletype_id=<?=$_GET['articletype_id'];?>"><font color="#FFFFFF"><?php echo $AppUI->_('Owner');?></font></a>
			</th>

			<th align="left" width="1%">
				<form name="frmfiltro"  method="get">
				<input type="hidden" name="m" value="pipeline" >
				<input type="hidden" name="a" value="addedit" >
				<input type="hidden" name="lead_id" value="<?=$_GET['lead_id'];?>" >
				<input type="hidden" name="tab" value="<?=$tab;?>" >
				<input type="hidden" name="art_next_pag" value="<?=$_GET['art_next_pag'];?>" >
			    <select name="sec_id" class="text" onchange="submit()">
				    <option value=""><?php echo $AppUI->_('All categories');?></option>
					<?

					  if ($AppUI->user_locale == 'es')
							$name = 'name_es';
						else
							$name = 'name_en';


						$query = "SELECT *
								  FROM files_category
								  order by $name
								  ";

						$results = mysql_query($query);

						while ($rows = mysql_fetch_array($results, MYSQL_ASSOC)) {

						  echo '<option ';
						  if($sec_id == $rows["category_id"]) echo "selected";
						  echo ' value="'.$rows["category_id"].'">'.$rows[$name].'</option>';
						}
					?>
				</select>
			</th>
			<th align="left" width="1%">
			     <select name="articletype_id" class="text" onchange="submit()">
				    <option value=""><?php echo $AppUI->_('All types');?></option>
					<option value="0" <? if($_GET["articletype_id"]=="0")echo "selected";?> ><?php echo $AppUI->_('Article');?></option>
					<option value="1" <? if($_GET["articletype_id"]=="1")echo "selected";?> ><?php echo $AppUI->_('Link');?></option>
					<option value="2" <? if($_GET["articletype_id"]=="2")echo "selected";?> ><?php echo $AppUI->_('File');?></option>
				 </select>

				
			</th></form>
		</tr>
		<?php
		$rows=0;
		if(!empty($articles)){
			foreach ($articles as $row) {
			  
			if(strrpos($row["title"], "[EMBEDDED") === false)
			{
			  $canDelete = $AppUI->user_type == 1 || $row["user_id"] == $AppUI->user_id;
			  
	          // Con el ide del file o articulo traigo la seccion 
	          /*if ( $row["type"]!= '0' && $row["type"]!='1' )
	          {
	          	 if ( getDenyRead( 'files' )  &&  $row['articlesection_id']=='0')
	          	 {
	          	    $no_listar = "1";
	        	 }else{
	          	 	$no_listar = "0";
	          	 }
	          }*/
	          
	          //if ($no_listar != 1)
	          if(true)
	          {
	          
			  $result2 = mysql_query("SELECT * FROM files_category WHERE category_id = '{$row["file_category"]}';");
	
			  if(mysql_num_rows($result2)>0){
				$row2 = mysql_fetch_array($result2, MYSQL_ASSOC);
				$secname=$row2[$name];
			  }
			  else $secname="";
	
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
				<td align="left" nowrap="nowrap" width="1%" >
			<?php //if ($canEdit OR $AppUI->user_id == $row["user_id"]) { 
				if(checkPermission($row['article_id'],$row['type'])){
				?>
						     <?
	
								if($row["type"]=="0")
								{
									if(!getDenyEdit("timexp")) {
										?>
										<a href='javascript:report_hours(<? echo $row['article_id']; ?>,<? echo $row['type']; ?>);' >
										<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'></a>
										<?php
									}
									 if(!getDenyEdit( "files" )){
										echo "<a href=\"javascript:edit_art('".$row["article_id"]."');\" title=\"".$AppUI->_('edit')."\">".dPshowImage( './images/icons/edit_small.gif', 20, 20, '' )."</a>";
									 }
								}
								elseif($row["type"]=="1")
								{
									if(!getDenyEdit("timexp")) {
										?>
										<a href='javascript:report_hours(<? echo $row['article_id']; ?>,<? echo $row['type']; ?>);' >
										<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'></a>
										<?php
									}
									if(!getDenyEdit( "files" )){
										echo "<a href=\"javascript:edit_link('".$row["article_id"]."');\" title=\"".$AppUI->_('edit')."\">".dPshowImage( './images/icons/edit_small.gif', 20, 20, '' )."</a>";									    
									}
								}
								else
								{
									if(!getDenyEdit("timexp")) {
										?>
										<a href='javascript:report_hours(<? echo $row['article_id']; ?>,-1);' >
										<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'></a>
										<?php
									}
									if(!getDenyEdit( "files" )){
										echo "<a href=\"javascript:edit_file('".$row["article_id"]."');\" title=\"".$AppUI->_('edit file')."\">".dPshowImage( './images/icons/edit_small.gif', 20, 20, '' )."</a>";										
									}
								}
	
							?>
						<?
						  	if(($row["type"]=="0" || $row["type"]=="1"))
							{ 
								if (CArticle::canDelete($row['article_id']))
								{	?>
											<a href="javascript:delArticle(<?php echo $row["article_id"];?>, '\'<?php echo $row["title"];?>\'')" title="<?php echo $AppUI->_('delete');?>"><?php echo dPshowImage( './images/icons/trash_small.gif', 16, 16, '' ); ?></a>
						<?		}
							}
							else
							{	if (CFile::canDelete($row['article_id']))
								{	?>
									<a href="javascript:delFiles(<?php echo $row["article_id"];?>, '\'<?php echo $row["title"];?>\'')" title="<?php echo $AppUI->_('delete');?>"><?php echo dPshowImage( './images/icons/trash_small.gif', 16, 16, '' ); ?></a>
						<?		}
							}
						?>
						
			<?php } ?>
			<?php if($row["type"]!="0" && $row["type"]!="1")
				{ ?>
				<a href="javascript:popUp('index_inc.php?inc=./modules/files/show_versions.php&m=files&file_id=<?php echo $row["article_id"]?>')"><img src="/images/icons/lupa3.gif" alt="<?php //echo $AppUI->_('Show Versions');?>" border="0" height="20" width="20"></a>
				<?php }
				
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
				<td align="left">
					<?
						$datemod = new CDate($row["date_modified"]);
						echo ($datemod->format($AppUI->user_prefs['SHDATEFORMAT'].'<br/>'.$AppUI->user_prefs['TIMEFORMAT']));
					?>
					</td>
				<td align="left">
					<?
					if($row["type"]=="0")
					{
						echo '<a href="javascript:popUp(\'index_inc.php?inc=./modules/articles/viewarticle.php&m=articles&id='.$row["article_id"].'\')">'.$row["title"].'</a>';
					}
	
					if($row["type"]=="1")
					{
						//echo "<a href=\"".$row["abstract"]."\" target=_blank> ".$row["title"]."</a>";
						echo "<a href=\"javascript:popUp('index_inc.php?inc=./modules/articles/vwlink.php&m=articles&id=".$row['article_id']."')\"> ".$row["title"]."</a>";
					}
	
					if($row["type"]!="0" && $row["type"]!="1")
					{
						$nombre=($row['title']) ? $row['title'] : $row['abstract'];
	
						$file_parts = pathinfo($row['abstract']);
						echo "<a href=\"./fileviewer.php?file_id={$row['article_id']}\" TITLE=\"{$row['abstract']}\">".
						dPshowImage( getImageFromExtension($file_parts["extension"]), '16', '16', $row['abstract']).
						"&nbsp;".$nombre."</a>";
					}
					?>
				</td>
				<td align="left">
					<?php
					  $sql = mysql_query("SELECT user_first_name, user_last_name FROM users WHERE user_id ='".$row["user_id"]."' ");
					  $autor = mysql_fetch_array($sql);
	
					  echo $autor["user_first_name"]." ".$autor["user_last_name"];
					?>
				</td>
				<td>
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
			<tr><td colspan='8'><span id='new_<?php echo $rows; ?>'></span></td></tr>
			<tr><td colspan='8'><span id='<?php echo $rows; ?>'></span></td></tr>
			<tr class="tableRowLineCell"><td colspan="8"></td></tr>
			<?php
			}
				$rows++;
		}
	}
	}
	echo "<script language='Javascript'>
		<!--
		open_rows=new Array(".count($articles).");
		items=new Array(2);
		items[0]=0;
			items[1]=0;
		for(i=0;i<".count($articles).";i++) open_rows[i]=items;
		-->
	</script>";

			if(count($articles)=="0"){
			  echo "<tr><td colspan=\"6\" align=\"center\">".$AppUI->_('Search not found')."</td></tr>";
			}
		?>

	</table>
	
		<table border='0' width='100%' cellspacing='0' cellpadding='1'>
		<tr bgcolor="#E9E9E9">
			<td align='center'><? echo $pager_links; ?></td>
			<td width="35" height="15">
			</td>
		</tr>
		<tr>
				<td height="1" colspan="6" bgcolor="#E9E9E9"></td>
				
		</tr>
		</table>	
<?
}


function add_article(){
 global $AppUI;
 $from_resource=TRUE;
 include($AppUI->cfg['root_dir']."/modules/articles/addeditarticle.php");
}

function add_file(){
	global $AppUI;
	$from_resource=TRUE;
	include( "{$AppUI->cfg['root_dir']}/functions/files_func.php" );
	include($AppUI->cfg['root_dir']."/modules/files/addedit.php");
}

function add_link($article_id, $sec_id){
 global $AppUI;
 $from_resource=TRUE;
 include($AppUI->cfg['root_dir']."/modules/articles/addeditlink.php");
}


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