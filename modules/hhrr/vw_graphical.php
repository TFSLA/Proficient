<?php
GLOBAL $AppUI, $canEdit;
require_once("import_functions.php");

//Si devolvio -1 significa que tiene permiso para todas las empresas.
//Sino devuelve un string con el formato "12, 55, 34"
$companies=empresas_habilitadas_hhrr_str();
$resource_type = $AppUI->getState( 'RrhhIdxTab' ) == 0 ? "" : 1;

if ($companies == -1)
	$sql="SELECT company_id,company_name FROM companies ORDER BY company_name;";
else
	$sql="SELECT company_id,company_name FROM companies WHERE company_id IN ('$companies') ORDER BY company_name;";

$rows = db_loadList( $sql, NULL );
?>
<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">
	<tr class='tableHeaderGral'>
		<td width='5'></td>
		<td align="left">
			&nbsp;&nbsp;&nbsp;<?=$AppUI->_( 'Name' )?>
		</td>
		<td width='200'>
			<?=$AppUI->_( 'Position' )?>
		</td>
		<td width='100'>
			<?=$AppUI->_( 'Title' )?>
		</td>
		<td width='140'>
			<?=$AppUI->_( 'Direct report' )?>
		</td>
		<td width='1'></td>
	</tr>
</table>
<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">
<?
// Me fijo si tiene permiso de lectura/escritura para todas las empresas, todos los items y todos los departamentos.

$query_Allcompanies = "SELECT count(distinct(id))
					   FROM hhrr_permissions
					   WHERE company = '-1' AND department='-1' AND id_user = '".$AppUI->user_id."'
					   AND personal = '-1'
					   AND matrix = '-1'
					   AND work_experience = '-1'
					   AND education = '-1'
					   AND performance_management = '-1'
					   AND compensations = '-1'
					   AND development = '-1'";

$sql_Allcompany = db_loadColumn($query_Allcompanies,NULL);
$permission_allcia = $sql_Allcompany['0'];

foreach ($rows as $row)
{?>
	<tr>
		<td align="left" width="1%">
			<img onClick="xajax_showDep('<?=$row['company_id']?>_0','<?=$resource_type?>')" id="img_<?=$row['company_id'];?>_0" src='./images/icons/expand.gif' width='16' height='16' border='0' alt='<?php echo $AppUI->_('Show');?>'>
		</td>
		<td>
			<a href="?m=hhrr&tab=1&company_id=<?=$row['company_id']?>" target="_new" style="text-decoration:none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
				<b><?= $row['company_name'];?></b>
			</a>
			&nbsp;
			<?
			if($resource_type == ""){
	            $import_permission = permission_hhrr($row['company_id'],-1,1);
	        
	        if ($import_permission=='-1' || $permission_allcia > 0){
				?>
				<a href="?m=hhrr&a=import_hhrr&company_id=<?=$row['company_id']?>" style="text-decoration:none;">
				[ importar recursos ]
				</a>
			<? }
			} ?>
		</td>
	</tr>
	<tr>
		<td width='5'></td>
		<td colspan='5'>
			<div id="div_<?=$row['company_id']?>_0">
			</div>
		</td>
	</tr>
	<tr style='background-color: rgb(187, 187, 187);' class='tableRowLineCell'>
		<td colspan='7'></td>
	</tr>
<? }?>
</table>

<?php if($resource_type != "") {?>
	
	<form name="delFrm" action="?m=hhrr" method="post">
		<input type="hidden" name="dosql" value="do_role_aed" />
		<input type="hidden" name="del" value="1">
		<input type="hidden" name="job_id" value="0">
	</form>

	<script language="javascript">
	function delRole(id){
		if(confirm("<?=$AppUI->_("confirmRoleDelete")?>")){
			f = document.delFrm;
			f.job_id.value = id;
			f.submit();
		}
	}
	</script>

<?php } ?>