<?php

GLOBAL $AppUI, $canEdit, $tpl_stub, $tpl_where, $tpl_orderby;

require_once( $AppUI->getModuleClass( 'files' ) );
require_once( $AppUI->getModuleClass('projects') );

switch($_POST[order]){
	case "date":
	  $order_by = "ORDER BY ord desc" ;
	break;
	case "title":
	  $order_by = "ORDER BY title asc,ord desc" ;
	break;
	case "owner":
	  $order_by = "ORDER BY user_id desc,ord desc" ;
	break;
	case "section":
	  $order_by = "ORDER BY articlesection_id asc,ord desc" ;
	break;
	case "type":
	  $order_by = "ORDER BY type asc,ord desc" ;
	break;
	default:
	  $order_by = "ORDER BY ord desc" ;
}
	 

if($_POST[articlesection_id]!="")
{

$articlesection_id = $_POST[articlesection_id];
}
else{

    if($_POST[id]!="")
	{
		$articlesection_id = $_POST[id];
	}

	// Si el id de la seccion esta vacio y el id de seccion guardado en la session es distinto de 1 trae la seccion top (-1)
	if($_POST[id]=="" && $_POST[txt_search] =="" && $AppUI->state[ArticleIdxTab]!="1")
	{
		if ($_GET[id]!='')
		{
			$articlesection_id = $_GET[id];
		}else{
        $articlesection_id = "-1";
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


if($_POST[articletype_id] != '')
{
	$articletype_id = $_POST[articletype_id];
}else{
	$articletype_id = $_GET[type];
}
	 
if($articletype_id!="")
{

  if($articletype_id=="0" || $articletype_id=="1")
	{
	$filtro_type = " and type='$articletype_id'";
	$filtro_type_file = " and file_id ='-1' ";
	}
	else{
	$filtro_type = " and type='-1' ";
	}

}

if(isset($_POST[txt_search])!=""){

	$busca = $_POST["txt_search"];

	$where = " and (title like '%$busca%' or abstract like '%$busca%' or body like '%$busca%') ";

    $where_file = " and (file_description like '%$busca%' or file_name like '%$busca%') ";
}


$sql = "(SELECT DATE_FORMAT(date,'%Y%m%d') AS ord,articlesection_id, user_id,article_id, title, type, abstract, DATE_FORMAT(date,'%d/%m/%Y') as datefmt  
FROM articles 
WHERE 1=1
$where 
$filtro_articlesection
$filtro_type
$news
)";


$sql .= "UNION (SELECT DATE_FORMAT(file_date,'%Y%m%d') AS ord,file_section,file_owner,file_id, file_description, file_type, file_name, DATE_FORMAT(file_date,'%d/%m/%Y') as datefmt
FROM files
WHERE file_delete_pending='0'
AND  file_section <> '0'
$where_file 
$filtro_articlesection_file
$filtro_type_file
$news_file
)";

$sql .= $order_by;

$dp = new DataPager_post($sql, "form_vwarticles");
$dp->showPageLinks = true;
$articles = $dp->getResults();
$rn = $dp->num_result;
$pager_links = $dp->RenderNav_post("form_vwarticles");

?>

<SCRIPT LANGUAGE="JavaScript">
//<!-- Begin

function submitFm(campo, valor){
	var f = document.form_vwarticles;
	var campo_form = campo.value;
	
	switch(campo)
	{
		case 'id':
		   f.id.value = valor.value;
		   
		   if (f.id.value == "undefined")
		   {
		   	f.id.value = valor;
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

// End -->
</script>

	<form name="form_vwarticles" method="POST">
       
       <input type="hidden" name="m" value="articles" >
       
       <!-- Variables para paginacion -->
       <input type="hidden" name="form_vwarticles_next_page" value="" > 
       
       <!-- Variables usadas para los filtros -->
       <input type="hidden" name="id" value="<?=$articlesection_id ?>"> 
       <input type="hidden" name="articletype_id" value="<?=$articletype_id?>">
       
       <!-- Ordenamiento -->
       <input type="hidden" name="order" value="<?=$_POST['order']?>">
       
       <!-- Busqueda -->
       <input type="hidden" name="txt_search" value="<?=$_POST['txt_search']?>">
    </form>
  
    
    <form name="frmDelete_file" method="post">
		<input type="hidden" name="dosql" value="do_file_aed" />
		<input type="hidden" name="del" value="1" />
		<input type="hidden" name="file_id" value="" />
    </form>
    
<table cellpadding="2" cellspacing="0" border="0" width="100%" class="">
<tr class="tableHeaderGral">
	<th width="10%" align="right">&nbsp;</th>
   	<th><a href="?m=articles&a=admin&tab=0&order=date&id=<?=$articlesection_id;?>"><font color="#FFFFFF"><?php echo $AppUI->_('Date');?></font></a></th>
	<th><a href="?m=articles&a=admin&tab=0&order=title&id=<?=$articlesection_id;?>"><font color="#FFFFFF"><?php echo $AppUI->_('Article\'s Title');?></font></a></th>
	<th><a href="?m=articles&a=admin&tab=0&order=owner&id=<?=$articlesection_id;?>"><font color="#FFFFFF"><?php echo $AppUI->_('Owner');?></font></a></th>
	
	<th width='5%'>
	    <select name="id" class="text" onchange="submitFm('id',this)" >
		    <option value="0"  <? if($articlesection_id =="")echo "Selected"; ?> ><?php echo $AppUI->_('All sections');?></option>
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
	<th width='5%'>
	     <select name="articletype_id" class="text" onchange="submitFm('articletype_id',this)" >
		    <option value=""><?php echo $AppUI->_('All types');?></option>
			<option value="0" <? if($articletype_id=="0")echo "selected";?> ><?php echo $AppUI->_('Article');?></option>
			<option value="1" <? if($articletype_id=="1")echo "selected";?> ><?php echo $AppUI->_('Link');?></option>
			<option value="2" <? if($articletype_id=="2")echo "selected";?> ><?php echo $AppUI->_('File');?></option>
		 </select>
	</th>
</tr>
<?php 


foreach ($articles as $row) {
  $result2 = mysql_query("SELECT * FROM articlesections WHERE articlesections.articlesection_id = '{$row["articlesection_id"]}';");
  if(mysql_num_rows($result2)>0){
    $row2 = mysql_fetch_array($result2, MYSQL_ASSOC);
    $secname=$row2["name"];
  }
  else $secname="top";
  
?>
<tr>
	<td align="right" nowrap="nowrap" width=45 >
<?php if ($canEdit) { ?>
		<table align=center width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td>
			    <?
				if($row["type"]=="0")
				{
				echo "<a href=\"./index.php?m=articles&a=addeditarticle&sec_id=$articlesection_id&article_id=".$row["article_id"]."\" title=\"".$AppUI->_('edit')."\">";
				echo dPshowImage( './images/icons/edit_small.gif', 20, 20, '' ); 
				echo "</a>";
				}

				if($row["type"]=="1")
				{
				echo "<a href=\"./index.php?m=articles&a=addeditlink&id=".$row["article_id"]."\" title=\"".$AppUI->_('edit')."\" >";
				echo dPshowImage( './images/icons/edit_small.gif', 20, 20, '' ); 
				echo "</a>";
				}
                
				
				
				
				if(($row["type"]!="0" && $row["type"]!="1"))
				{
					$obj_file = new CFile();
					$canDelete_files = CFile::canDelete($row["article_id"] );
					$canEdit_files = CFile::canEdit($row["article_id"]);
				
					if($canEdit_files)
					{
				    echo "<a href=\"./index.php?m=files&a=addedit&file_id=".$row["article_id"]."\" title=\"".$AppUI->_('edit')."\">";
				    echo dPshowImage( './images/icons/edit_small.gif', 20, 20, '' ); 
				    echo "</a>";
					}
				}
				?>

			</td>
			<td>
			    <? if($row["type"]=="0" || $row["type"]=="1") { ?>
				<a href="javascript:delArticle(<?php echo $row["article_id"];?>, '\'<?php echo $row["title"];?>\'')" title="<?php echo $AppUI->_('delete');?>"><?php echo dPshowImage( './images/icons/trash_small.gif', 16, 16, '' ); ?></a>
				<? }else{ 
					if ($canDelete_files){
				?>
				
			    <a href="javascript:delFiles(<?php echo $row["article_id"];?>, '\'<?php echo $row["title"];?>\'')" title="<?php echo $AppUI->_('delete');?>"><?php echo dPshowImage( './images/icons/trash_small.gif', 16, 16, '' ); ?></a>
					
				<?} } ?>

			</td>
		</tr>
		</table>
<?php } ?>
	</td>
	<td>
		<?
		if($row["type"]=="0")
		{
		echo "<a href=\"./index.php?m=articles&a=addeditarticle&article_id=".$row["article_id"]."\" title=\"".$AppUI->_('edit')."\">";
		echo $row["datefmt"]; 
		echo "</a>";
		}

		if($row["type"]=="1")
		{
		echo "<a href=\"./index.php?m=articles&a=addeditlink&id=".$row["article_id"]."\" title=\"".$AppUI->_('edit')."\" >";
		echo $row["datefmt"]; 
		echo "</a>";
		}

		if($row["type"]!="0" && $row["type"]!="1")
		{
		echo $row["datefmt"];
		}
		?>
	</td>
	<td>
		<?
		if($row["type"]=="0")
		{
		echo "<a href=\"./index.php?m=articles&a=addeditarticle&article_id=".$row["article_id"]."\" title=\"".$AppUI->_('edit')."\">";
		echo $row["title"]; 
		echo "</a>";
		}

		if($row["type"]=="1")
		{
		echo "<a href=\"./index.php?m=articles&a=addeditlink&id=".$row["article_id"]."\" title=\"".$AppUI->_('edit')."\" >";
		echo $row["title"]; 
		echo "</a>";
		}

		if($row["type"]!="0" && $row["type"]!="1")
		{
		$file_parts = pathinfo($row['abstract']);
		
			if($canEdit_files)
			{
			echo "<a href=\"./index.php?m=files&a=addedit&file_id=".$row["article_id"]."\" title=\"{$row['abstract']}\">";
			}
			
			echo dPshowImage( getImageFromExtension($file_parts["extension"]), '16', '16', $row['abstract'] ).
			"&nbsp;".$row['title'];
			
			if($canEdit_files)
			{
			echo "</a>"; 
			}
		}
		?>
	</td>
	<td>
		<?php 
		 $sql = mysql_query("SELECT user_first_name, user_last_name FROM users WHERE user_id ='".$row["user_id"]."' ");
		 $autor = mysql_fetch_array($sql);

		 echo $autor["user_first_name"]." ".$autor["user_last_name"];
		?>
	</td>
	<td>
		<?php echo $secname;?>
	</td>
	<td>
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
<tr class="tableRowLineCell"><td colspan="6"></td></tr>  
<?php }?>

<? if(count($articles)=="0"){
   echo "<tr><td colspan=\"6\" align=\"center\">".$AppUI->_('Search not found')."</td></tr>"; 
   }
?>
</table>

        <table border='0' width='100%' cellspacing='0' cellpadding='1'>
		<tr bgcolor="#E9E9E9">
			<td align='center'><? echo $pager_links; ?></td>
		</tr>
		<tr>
				<td height="1" colspan="5" bgcolor="#E9E9E9"></td>
		</tr>
		</table>
