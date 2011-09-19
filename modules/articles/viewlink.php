<?php /* $Id: viewlink.php,v 1.2 2009-05-21 20:07:56 ctobares Exp $ */
$AppUI->savePlace();

$id = $_GET[id];

if($id==""){
    $rows[articlesection_id] = '-1';
	$rows[name] = 'Top';
	$id = '-1';
	
}
else{
	$results = mysql_query("SELECT * FROM articlesections WHERE articlesection_id = $id");
    $rows = mysql_fetch_array($results, MYSQL_ASSOC);

}

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" background="images/common/back_1linea_04.gif">
    <tr>
        <td align="left"><img src="images/common/lado.gif" width="1" height="17"></td>
        <td class="boldtext"><? 

		if($_POST[txt_search]==""){
		  echo $AppUI->_($rows["name"]); 
        }else{
		  echo $AppUI->_("Search results"); 
		}

		?></td>
        <td align="right"><img src="images/common/lado.gif" width="1" height="17"></td>
    </tr>
    <tr bgcolor="#666666">
        <td colspan="3"></td>
    </tr>
</table>
<!-- <br>&nbsp;&nbsp;<b><?=$AppUI->_('Read our articles')?>:</b><br>
 -->
<ul>
<?	
if(isset($_POST[txt_search])!=""){

	$busca = $_POST["txt_search"];

	$where = " and (title like '%$busca%' or abstract like '%$busca%' or body like '%$busca%') ";
	$sql = "SELECT articles_reads, article_id, title, abstract, DATE_FORMAT(date,'%m/%d/%Y') as datefmt  FROM articles where type='1' $where ORDER BY date desc";
}
else{
$sql = "SELECT articles_reads, article_id, title, abstract, DATE_FORMAT(date,'%m/%d/%Y') as datefmt  FROM articles where articlesection_id=$id and type='1' $where ORDER BY date desc";
}

$dp = new DataPager($sql, "art");
$dp->showPageLinks = true;
$result = $dp->getResults();
$rn = $dp->num_result;
$pager_links = $dp->RenderNav();

foreach ($result as $row){
  echo '<li>'.$row["datefmt"];
  echo '<br><a href="'.$row["abstract"].'" target=_blank>'.$row["title"].'</a>';
  echo "</li><br><br>";
} 

if(count($result)=="0")
{
 echo $AppUI->_('Search not found');
}

?>
</ul>
        <table border='0' width='100%' cellspacing='0' cellpadding='1'>
		<tr bgcolor="#E9E9E9">
			<td align='center'><? echo $pager_links; ?></td>
		</tr>
		<tr>
				<td height="1" colspan="5" bgcolor="#E9E9E9"></td>
		</tr>
		</table>