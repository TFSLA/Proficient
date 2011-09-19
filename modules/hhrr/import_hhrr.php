<? /* Importa recursos desde archivo xls */
global $AppUI;

require_once("import_functions.php");


// Verifica si tiene permisos para agregar recursos
$canAdd = CHhrr::canAdd();

$company  = $_GET['company_id'];


if($AppUI->user_type!='1'){
	
   $import_permission = permission_hhrr($company,-1,1);

  // Para todos los departamentos de esta empresa tiene algun tab sin permiso de lectura-escritura entonces lo saco
  
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

  if ($import_permission=='0' && $permission_allcia == 0)
  {
  	$AppUI->redirect( "m=public&a=access_denied" );
  }
  
}

// Si llega sin id de ninguna empresa, lo saca
if($company == "")
{
   $AppUI->redirect('m=hhrr&tab=0');
}

// Traigo el nombre de la empresa
$query_company = "SELECT company_name FROM companies WHERE company_id = '".$company."' ";       
$sql_company = db_loadColumn($query_company,NULL);
$company_name = $sql_company['0'];

// setup the title block
$ttl = $id > 0 ? $AppUI->_('Edit HHRR')." - $ttl_data" : $AppUI->_('Import HHRR').": ".$company_name;
$titleBlock = new CTitleBlock( $ttl, 'hhrr.gif', $m, 'hhrr.index' );

  
if($id!=$AppUI->user_id){
  $titleBlock->addCrumb( "?m=hhrr&tab=1", strtolower($AppUI->_('Resources list')) );
  $titleBlock->addCrumb( "?m=hhrr&tab=0", strtolower($AppUI->_('Graphical View')) );
  //$titleBlock->addCrumb( "?m=hhrr&tab=2", strtolower($AppUI->_('Candidates list')) );
}

$titleBlock->show();


/*--  Traigo la lista de empresas para la que tiene permisos -- */

//Si devolvio -1 significa que tiene permiso para todas las empresas.
//Sino devuelve un string con el formato "12, 55, 34"
$companies=empresas_habilitadas_hhrr_str();

if ($companies == -1)
	$sql="SELECT company_id,company_name FROM companies ORDER BY company_name;";
else
	$sql="SELECT company_id,company_name FROM companies WHERE company_id IN ('$companies') ORDER BY company_name;";

$companies = db_loadHashList( $sql);

// Acciones

$accion = $_POST['accion'];
$update_rrhh = $_POST['update_rrhh'];


switch ($accion)
{
	case "enviar":
		// Guardo el archivo
		$up_file = save_file();
		
		// Verifico que el archivo se pueda leer
		$chk_file = check_file($up_file, $company,$update_rrhh );
		
	break;
	
	case "importar":
	     // Descarga el archivo en la base de datos, si llego hasta aca no necesita hacer ninguna validacion mas
	     $save_file = import_resources($_POST['file'], $company);
	break;
	
	case "actualizar":
	     // Actualiza los recursos
	     $update_file = update_resources($_POST['file'], $company);
	break;
}

?>
<script language="javascript">

function submitIt() {
	var f = document.editFrm;
	var rta = true;
	
	var filename = f.file_xls.value.split(".");
	
	if (filename)
	{
	   var ext = filename[filename.length-1].toLowerCase();
	   
	   if(ext == "" || ext != 'xls')
	   {
		   alert1("<?=$AppUI->_('Please select a file')?> *.xls ");
		   f.file_xls.focus();
		   rta = false; 
	   }
	}
	
	if (rta)
	{
		f.submit();
	}
	
}

</script>

<!-- Formulario de carga de archivo -->

<form name="editFrm" action="" method="post" enctype="multipart/form-data">

  <input type="hidden" name="accion" value="enviar">
  <input type="hidden" name="company" value="<?=$company?>" >
  
  <table align="center" border="0" cellpadding="1" cellspacing="1"  >
    <tr>
      <td align="right" >
         <b><?php echo $AppUI->_( 'File' );?>:&nbsp;</b>
      </td>
      <td>
        <input class="text" size="26" type="file" name="file_xls">
      </td>
    </tr>
    <tr>
      <td>
        <b><?php echo $AppUI->_( 'update' );?>:&nbsp;</b>
      </td>
      <td>
        <input type="checkbox" name="update_rrhh" value="1">
      </td>
    </tr>
    <tr>
      <td colspan="2" align="right">
        <br>
        <input type="button" value="<?php echo $AppUI->_( 'save' );?>" name="send" class="button"  onclick="submitIt()"/>
      </td>
    </tr>
  </table>
  <br><br><br>

  &nbsp;&nbsp;<A HREF="<?=$AppUI->cfg['base_url']?>/files/hhrr/Carga_de_datos.xls"><?=$AppUI->_( 'File model' )?></A>
  
</form>

<BR>
<center>
<?=$chk_file;?>
<?=$save_file;?>
<?=$update_file;?>
</center>