<?php

$m = $_GET[m];

$id = isset($_GET['id']) ? $_GET['id'] : 0;

$sec_id = $_GET['sec_id'];

if($sec_id=="")
{
$sec_id = $_POST['sec_id'];
}


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
   if($m!="projects"){
    // setup the title block
	$ttl = $id > 0 ? $AppUI->_('edit link') : $AppUI->_('add link');
	$titleBlock = new CTitleBlock( $ttl, 'article_management.gif', $m, 'ID_HELP_SECURITY_TEMPLATE_EDIT' );
	$titleBlock->show();
   }
}  

if ($id!="0"){
 $query = mysql_query($sql);
 $row = mysql_fetch_array($query);
 
 $articlesection_id = $row[articlesection_id];
 $description = $row[title];
 $href = $row[abstract];
 $abstract = $row[body];

 }

 if($_POST[accion]=="save"){

      if ($_POST[id]=="0"){
           
		   $ts = time();
		   $date = date("Y-m-d",$ts);

		   $query = "INSERT INTO articles (articlesection_id, date, reads, user_id, title, abstract, body,type) VALUES ('".$_POST[articlesection_id]."', '".$date."', '0', '".$AppUI->user_id."', '".$_POST[description]."', '".$_POST[href]."','".$_POST[abstract]."','1')";
	   }
       else{

		   $ts = time();
		   $date = date("Y-m-d",$ts);
            
		   $query = "UPDATE articles SET articlesection_id= '".$_POST[articlesection_id]."',date='".$date."',user_id='".$AppUI->user_id."',title='".$_POST[description]."',abstract='".$_POST[href]."', body='".$_POST[abstract]."'  WHERE article_id='".$_POST[id]."' ";

		   
	   }

	   $sql = mysql_query($query);

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
    
	if(error){
		form.submit();
	}
}

</script>

    <table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
       <form name="addedit_link" action="" method="post">
	      <input type="hidden" name="id" value="<?php echo $id;?>" />
		  <input type="hidden" name="accion" value="save" />
            <tr>
				<td align="right"><?php echo $AppUI->_( 'Section' );?>:</td>
				<td> 
				<select name="articlesection_id" class="text">
				  
					<option value="-1">Top</option>';
					<?
					if($m=="projects"){

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

						$query = "SELECT * 
								  FROM articlesections 
								  WHERE 1=1
								  AND articlesection_id IN (" . implode( ',', $sec_art) . ")";

					}
					else{
						$query = "SELECT * FROM articlesections";
					}

					$results = mysql_query($query);
					while ($rows = mysql_fetch_array($results, MYSQL_ASSOC)) {

					    if( $drow["articlesection_id"] == $rows["articlesection_id"] ) 
						{
						 $selected = "selected";
						}
						else{
							if($sec_id == $rows["articlesection_id"])
							{
							$selected = "selected";
							}
							else{
							$selected = "";
							}
						}
      
					  echo '<option ';
					  echo ' value="'.$rows["articlesection_id"].'" '.$selected.'>'.$rows["name"].'</option>';
					}

					?>
				</select>
				</td>
	            <td valign="top" align="center">
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
					<td valign="top" align="center">
						</td>
				</tr>
				<tr>
					<td>
						<?
						$path = "m=articles";
                        
						$past = explode ('&',$AppUI->state[SAVEDPLACE]);
         
						if($past[1]=="a=admin" )
						{
						$path .= "&a=admin";
						}

						if($AppUI->state['ArticleIdxTab']!="")
						{
						$path .= "&tab=".$AppUI->state['ArticleIdxTab'];
						}

						$path .= "&id=$sec_id";

                        $back = "index.php?".$path;
						?>

						<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:window.location='<?=$back;?>';" />
					</td>
					<td colspan="2" align="right">
						<input type="button" value="<?php echo $AppUI->_( 'submit' );?>" class="button" onClick="submitIt()" />
					</td>
				</tr>

				</form>
				</table>
