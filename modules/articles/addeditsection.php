<?php
global $AppUI, $xajax;

require_once( $AppUI->getModuleClass ('companies' ) );
require_once( $AppUI->getModuleClass ('projects' ) );


$id = isset($_GET['id']) ? $_GET['id'] : 0;

$id = $articlesection_id;

$sql = "
SELECT *
FROM articlesections 
WHERE articlesection_id = $id
";
if ($id > 0 && !db_loadHash( $sql, $drow ) ) {
	$titleBlock = new CTitleBlock( 'Invalid Section  ID', 'article_management.gif', $m, 'colaboration.index' );
	$titleBlock->addCrumb( "?m=articles&a=admin", "Sections" );
	$titleBlock->show();
} else {


// setup the title block
	$ttl = $id > 0 ? "Edit Section" : "Add Section";
	$titleBlock = new CTitleBlock( $ttl, 'article_management.gif', $m, 'colaboration.index' );
	$titleBlock->show();


    if ($id > 0){
		
		$sql_compD = "SELECT DISTINCT a.company_id, c.company_name FROM articlesections_projects as a, companies as c WHERE articlesection_id = '".$id."' AND a.company_id = c.company_id ";
		$companies_dest = db_loadHashList( $sql_compD );
		
	}

	
$row = new CCompany();
$companies = $row->getAllowedRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );


$companies = arrayMerge( array( '0'=>'' ), $companies );


// Registro el array con todas la empresas en la variable se session
$AppUI->companies_o = $companies;
$AppUI->companies_d = $companies_dest;


$artCompanies = array();

$tmpprj = new CProject();
$projects_a = $tmpprj->getAllowedRecords($AppUI->user_id, "project_id, project_name", 'project_name');

$strJS = "var arProjs = new Array();\n";

foreach($projects_a as $key => $val)
{
  //echo $key."<>".$val."<br>";

  $sql = mysql_query("select project_company from projects where project_id='$key' ");
  $pro = mysql_fetch_array($sql);
  $proj_comp = $pro[project_company];

  $proj[$proj_comp][$key] = $val;

  $strJS .= "arProjs[arProjs.length] = new Array($proj_comp,$key, \"".$val."\");\n";

}

$projects_b = array();
$artProjects = array();

$xajax->printJavascript('./includes/xajax/');

?>
<script language="javascript">
<?php
echo $strJS;
?>
function submitIt() {
	var form = document.editFrm;
    var fl = form.as_companies.length -1;
	var pl = form.as_project.length -1;

	if (form.name.value.length < 1) {
		alert( "<?=$AppUI->_('Please enter the Section Name')?>" );
		form.name.focus();
	}
	else if (trim(form.articlesection_email.value) != "" && !isEmail(form.articlesection_email.value)) {
			alert ( "<?=$AppUI->_('E-Mail section invalid');?>" );
	}
	else {
        
		form.asign_companies.value = "";
		for (fl; fl > -1; fl--){
			form.asign_companies.value = form.as_companies.options[fl].value +","+ form.asign_companies.value
		}
        
		form.asign_project.value = "";
		for (pl; pl > -1; pl--){
			form.asign_project.value = form.as_project.options[pl].value +","+ form.asign_project.value
		}

		form.submit();
	}
}

function addCompany_ajax(){
	var form = document.editFrm;
	var fl = form.companies_av.length -1;
	
	for(i=0;i<form.companies_av.options.length;i++)
    {
        if(form.companies_av.options[i].selected)
           xajax_addCompany('companies_av','as_companies','project_av', form.companies_av.options[i].value);
    }
}

function delCompany_ajax(){
	var form = document.editFrm;
	var fl = form.as_companies.length -1;
	
	for(i=0;i<form.as_companies.options.length;i++)
    {
        if(form.as_companies.options[i].selected)
           xajax_delCompany('as_companies','companies_av','project_av', 'as_project' , form.as_companies.options[i].value);
    }
}

function addProyect_ajax(){
	var form = document.editFrm;
	var fl = form.project_av.length -1;
	
	for(i=0;i<form.project_av.options.length;i++)
    {
        if(form.project_av.options[i].selected)
           xajax_addProyect('project_av', 'as_project', form.project_av.options[i].value);
    }
}

function delProyect_ajax(){
	var form = document.editFrm;
	var fl = form.as_project.length -1;
	
	for(i=0;i<form.as_project.options.length;i++)
    {
        if(form.as_project.options[i].selected)
          xajax_delProyect('as_project', 'project_av', form.as_project.options[i].value);
    }
}

</script>

<table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
<form name="editFrm" action="?m=articles&a=admin" method="post" enctype="multipart/form-data">
	<input type="hidden" name="dosql" value="do_section_aed" />
	<input type="hidden" name="articlesection_id" value="<?php echo $articlesection_id;?>" />
    <input type="hidden" name="asign_companies" />
	<input type="hidden" name="asign_project" />

<tr>
	<td align="right"><?php echo $AppUI->_( 'Section Name' );?>:</td>
	<td><input type="text" class="text" name="name" value="<?php echo @$drow["name"];?>" maxlength="48" size="48"></td>
	<td valign="top" align="center">

        </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'E-Mail' );?>:</td>
	<td><input type="text" class="text" name="articlesection_email" value="<?php echo @$drow["articlesection_email"];?>" maxlength="200" size="48"></td>
	<td valign="top" align="center">

        </td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_( 'Description' );?>:</td>
	<td><textarea rows=4 cols=70 name="description"><?php echo @$drow["description"];?></textarea></td>
	<td valign="top" align="center">

        </td>
</tr>
<tr>
    <td valign="top" align="right">
      <?php echo $AppUI->_( 'Companies' );?>:
    </td>
	<td>   
	       <?php
			if ($id!=""){
			$query = "SELECT c.company_id, c.company_name 
			          FROM companies c
			          INNER JOIN articlesections_projects a 
					  WHERE c.company_id = a.company_id
					  AND articlesection_id ='$id' ";

			$row_a = new CCompany();
			$artCompanies = db_loadHashList( $query );

			}
		   ?>
		   
		   <script type="text/javascript">
				xajax_addCompany('companies_av','as_companies','project_av', '-1');
				
		  </script>
		  
	       <table cellspacing="0" cellpadding="2" border="0">
					<tr>
						<td>
						 <select name="companies_av" id="companies_av" class="text" style="width:180px" size="8" style="font-size:9pt;" multiple="multiple" >
						 </select>
						</td>
						
						<td>
						 <select name="as_companies" id="as_companies" class="text" style="width:180px" size="8" style="font-size:9pt;" multiple="multiple" >
						 </select>
						</td>
						
					<tr>
						<td align="right">
					     <input type="button" class="button" value="&gt;" onClick="addCompany_ajax()" />
						</td>
						
						<td align="left">
						 <input type="button" class="button" value="&lt;" onClick="delCompany_ajax()" />
						</td>
					</tr>
					</tr>
				</table>
	</td>
<tr>
<tr>
    <td valign="top" align="right">
      <?php echo $AppUI->_( 'Projects' );?>:
    </td>
	<td > 
	  <?
		  if ($id!=""){
            
		
			$query_pre = "SELECT distinct a.company_id, c.company_name 
			              FROM articlesections_projects a, companies c
					      WHERE articlesection_id ='$id' AND a.company_id = c.company_id";

            $comp = db_loadHashList($query_pre);
            
            
			foreach($comp as $cia => $ncia){
                $tmp = -1 * $cia;
                $projects_b[$tmp] = "== ".$ncia." ==";

                $query_a = "SELECT project_id, project_name 
			            FROM projects 
					    WHERE project_company = '$cia' ";
            
			    $projs_b = db_loadHashList( $query_a );
                
                foreach($projs_b as $prj => $nprj)
				{
				 $projects_b[$prj] = $nprj;
				}
                
			}
			
			$query = "SELECT p.project_id, p.project_name 
			          FROM projects p
			          INNER JOIN articlesections_projects a 
					  WHERE p.project_id = a.project_id
					  AND articlesection_id ='$id' ";

			$artProjects = db_loadHashList( $query );
		    
			$query_pra = "SELECT company_id 
			              FROM articlesections_projects
					      WHERE articlesection_id ='$id' 
						  AND project_id < 0
						  ";

            $prj = db_loadColumn($query_pra);

				 foreach($prj as $proj){

					$query = "SELECT  c.company_name 
							  FROM companies c
							  INNER JOIN articlesections_projects a 
							  WHERE c.company_id = a.company_id
							  AND a.company_id ='$proj' AND articlesection_id ='$id'";
    
					$prj = db_loadColumn($query);

					$tm_id = -1 * $proj;

					$ep = "== ".$prj[0]." ==";

					$artProjects = arrayMerge( array( $tm_id=>$ep ), $artProjects );
				 }
            
			
			}
			
			$AppUI->project_o = $projects_b;
			$AppUI->project_d = $artProjects;
	  ?>
	  <script type="text/javascript">
				xajax_addProyect('project_av', 'as_project', '0');
	  </script>
	  <table cellspacing="0" cellpadding="2" border="0">
					<tr>
					    <td>
						 <select name="project_av" id="project_av" class="text" style="width:180px" size="8" style="font-size:9pt;" multiple="multiple" >
						 </select>
						</td> 
						
						<td>
							<select name="as_project" id="as_project" class="text" style="width:180px" size="8" style="font-size:9pt;" multiple="multiple" >
						 </select>
						</td>
					<tr>
						<td align="right">
						  <input type="button" class="button" value="&gt;" onClick="addProyect_ajax();" />
						</td>
						<td align="left">
						  <input type="button" class="button" value="&lt;" onClick="delProyect_ajax();" />
						  
						</td>
					</tr>
					</tr>
				</table>
	</td>
		</tr>		
<tr>
	<td>
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:window.location='./index.php?m=articles&a=admin&tab=1&addedit=cancel';" /> 
		
	</td>
	<td colspan="2" align="right">
		<input type="button" value="<?php echo $AppUI->_( 'submit' );?>" class="button" onClick="submitIt()" />
	</td>
</tr>
</form>
</table>
<?php } ?>
