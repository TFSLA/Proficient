<?php /* $Id: fileviewer.php,v 1.3 2011-07-12 04:52:11 pkerestezachi Exp $ */
//file viewer

require "./includes/config.php";
require "./classes/ui.class.php";
require "./functions/files_func.php";

session_name( 'psa'.$dPconfig['instanceprefix'] );

if (get_cfg_var( 'session.auto_start' ) > 0)
{
	session_write_close();
}

session_start();
session_register( 'AppUI' );

// check if session has previously been initialised
if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout']))
{
	if (isset( $_SESSION['AppUI'] ) && isset($_GET['logout']))
		$user_log_id = $AppUI->user_log_id;

    $_SESSION['AppUI'] = new CAppUI();
}

$AppUI =& $_SESSION['AppUI'];
$AppUI->setConfig( $dPconfig );
$AppUI->checkStyle();

// set the default ui style
$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];

// check if we are logged in
if ($AppUI->doLogin())
{
    $AppUI->setUserLocale();
	// load basic locale settings
	@include_once( "./locales/$AppUI->user_locale/locales.php" );
	@include_once( "./locales/core.php" );

	$redirect = @$_SERVER['QUERY_STRING'];
	if (strpos( $redirect, 'logout' ) !== false)
	{
		$redirect = '';
	}

	require "./style/$uistyle/login.php";
	// destroy the current session and output login page
	session_unset();
	session_destroy();
	exit;
}

require "{$AppUI->cfg['root_dir']}/includes/db_connect.php";
include "{$AppUI->cfg['root_dir']}/includes/main_functions.php";
include "{$AppUI->cfg['root_dir']}/classes/dp.class.php";
include "{$AppUI->cfg['root_dir']}/includes/permissions.php";
include "{$AppUI->cfg['root_dir']}/modules/articles/articles.class.php";
include "{$AppUI->cfg['root_dir']}/modules/pipeline/leads.class.php";

/* Si el archivo es un pdf que se genero desde hhrr, hacemos la descarga por aca.*/
if ($_GET ['file_type'] == "pdf_report")
{
	$path_real= "{$AppUI->cfg['root_dir']}/files/temp/{$_GET ['file_name']}";

	$file=array();
	$file['file_name']=$_GET ['file_name'];
	$file['file_size']=filesize ("{$AppUI->cfg['root_dir']}/files/temp/{$_GET ['file_name']}");
	$file['file_type']="application/pdf";

	download($file, $path_real);

	unlink($path_real);//Borramos fisicamente el archivo
}

/* Si el archivo es un zip de varios reportes y se genero desde hhrr, hacemos la descarga por aca.*/
elseif ($_GET ['file_type'] == "zipped_reports")
{
	$path_real= "{$AppUI->cfg['root_dir']}/files/temp/{$_GET ['file_name']}";

	$file=array();
	$file['file_name']=$_GET ['file_name'];
	$file['file_size']=filesize ("{$AppUI->cfg['root_dir']}/files/temp/{$_GET ['file_name']}");
	$file['file_type']="application/zip";

	download($file, $path_real);

	unlink($path_real);//Borramos fisicamente el archivo
}

/* Si es cualquier archivo, como por ejemplo de la seccion files hacemos la descarga por aca.*/
else
{
	include_once("{$AppUI->cfg['root_dir']}/classes/dp.class.php");
	include_once("{$AppUI->cfg['root_dir']}/classes/date.class.php");
	include_once("{$AppUI->cfg['root_dir']}/modules/companies/companies.class.php");
	include_once("{$AppUI->cfg['root_dir']}/modules/files/files.class.php");
	include_once("{$AppUI->cfg['root_dir']}/modules/projects/projects.class.php");

	$file_id = isset($_GET['file_id']) ? $_GET['file_id'] : 0;
	$id_files_ver = isset($_GET['id_files_ver']) ? $_GET['id_files_ver'] : get_last_id_file_ver_signed($file_id); // Si no me pasa el id de la version traigo el id de la ultima version.

	//BEGIN SECURITY
	$query_file = "SELECT file_section, file_project, is_private, file_owner, file_opportunity FROM files WHERE file_id = '".$file_id."' ";
	$sql =  db_exec( $query_file );
	$data_perm = mysql_fetch_array($sql);
	$section_file = $data_perm[0];
	$project_file = $data_perm[1];
	$is_private = $data_perm[2];
	$file_user_id = $data_perm[3];
	$file_opportunity = $data_perm[4];

	//Validacion si el archivo es privado
	if($is_private == 1 && $file_user_id != $AppUI->user_id)
		if($AppUI->user_type != 1)
			$AppUI->redirect( "m=public&a=access_denied" );

	//  Por si acceden directamente poniendo la direccion , verifico los permisos
	$accessdenied = true;

	$objProject = new CProject();
	$prjs = $objProject->getAllowedRecords($AppUI->user_id, "project_id");

	$leads = CLead::getAllowedLeads();

	if ($project_file > 0 && (array_key_exists($project_file, $prjs))){
		$accessdenied = false;
	}
	elseif($file_opportunity > 0){
		if (array_key_exists($file_opportunity, $leads))
			$accessdenied = false;
		else{
			$usr = new CUser();
			$usr->load( $AppUI->user_id );
			$delegs = $usr->getDelegators();

			foreach( $delegs as $deleg )
			{
				$leads = CLead::getAllowedLeads($deleg["delegator_id"], 0);
				if(array_key_exists($file_opportunity, $leads))
					$accessdenied = false;
			}
		}
	}
	else{
		if($section_file <> 0){
			if(!getDenyRead('articles')){
				$accessdenied = false;
			}
			else{
				$userSections = CSection::getSectionsByUser();
						
				if (in_array($section_file, $userSections))
					$accessdenied = false;
			}
		 }
	}

	if($project_file == 0 && $section_file == 0 && $file_opportunity == 0 && !getDenyRead('files'))
		$accessdenied = false;

	if ($accessdenied)
		$AppUI->redirect( "m=public&a=access_denied" );

	//END SECURITY


	$canRead = !getDenyRead( 'files' );//Con esto valido permisos para el modulo de files
	$canEdit = CFile::canEdit($file_id); //Con esto valido que tenga permisos para el proyecto que pertenece el archivo


	// Si no tiene permisos para leer archivos ni para leer articulos sale
	if (getDenyRead( 'files' ) AND getDenyRead( 'articles' ))
	{
	    $sql_file = "SELECT file_section FROM files WHERE file_id = '".$file_id."' ";
		$section =  db_loadColumn( $sql_file );

		if($section[0]==0)
		{
			//$AppUI->redirect( "m=public&a=access_denied" );

		    // antes de sacarlo me fijo si el archivo pertenece a un proyecto, si es asi, verifico que el usuario tenga permiso de lectura para ese projecto, si no tiene permisos lo saco
		    $sql_file = "SELECT file_project FROM files WHERE file_id = '".$file_id."' ";
			$project_file =  db_loadColumn( $sql_file );


			if($project_file[0]>0)
			{
				$obj = new CProject();
				$obj->load($project_file[0], false);

				$canRead =$obj->canRead();

				if (!$canRead) {
					$AppUI->redirect( "m=public&a=access_denied" );
				}
			}
			else
			{
				$AppUI->redirect( "m=public&a=access_denied" );
			}

		}
    }

    // Si tiene permiso denegado para leer archivos me fijo si el archivo pertenece a la kb , si es asi lo dejo , si no lo saco
    if (getDenyRead( 'files' ))
	{

		$sql_file = "SELECT file_section FROM files WHERE file_id = '".$file_id."' ";
		$section =  db_loadColumn( $sql_file );

		if($section[0] == 0) // <> 0 => KB
		{

			//$AppUI->redirect( "m=public&a=access_denied" );

		    // antes de sacarlo me fijo si el archivo pertenece a un proyecto, si es asi, verifico que el usuario tenga permiso de lectura para ese projecto, si no tiene permisos lo saco
			$sql_file = "SELECT file_project FROM files WHERE file_id = '".$file_id."' ";
			$project_file =  db_loadColumn( $sql_file );


			if($project_file[0]>0)
			{
				$obj = new CProject();
				$obj->load($project_file[0], false);

				$canRead =$obj->canRead();

				if (!$canRead) {
					$AppUI->redirect( "m=public&a=access_denied" );
				}
			}
			else
			{
				$AppUI->redirect( "m=public&a=access_denied" );
			}


		}

	}




	if ($file_id)
	{
		// projects that are denied access
		$sql = "
		SELECT project_id
		FROM projects, permissions
		WHERE permission_user = $AppUI->user_id
			AND permission_grant_on = 'projects'
			AND permission_item = project_id
			AND permission_value = 0
		";
		$deny1 = db_loadColumn( $sql );

		$sql = "SELECT *
		FROM permissions, files
		WHERE file_id=$file_id
			AND permission_user = $AppUI->user_id
			AND permission_value <> 0
			AND (
				(permission_grant_on = 'all')
				OR (permission_grant_on = 'projects' AND permission_item = -1)
				OR (permission_grant_on = 'projects' AND permission_item = file_project)
				)"
			.(count( $deny1 ) > 0 ? "\nAND file_project NOT IN (" . implode( ',', $deny1 ) . ')' : '');

		if (!db_loadHash( $sql, $file )) {
			//VER PERMISOS AQUI
			//$AppUI->redirect( "m=public&a=access_denied" );
		};

		$sql_file_real_filename = "SELECT *
		FROM files_versions
		WHERE id_files_ver=$id_files_ver";

		db_loadHash( $sql_file_real_filename, $file_real_filename ) or die ("Error");

		$path_real= "{$AppUI->cfg['root_dir']}/files/{$file_real_filename['version_file_name']}";

		download($file, $path_real, $file_id);
		
		if($file_id)
		{
			$obj = new CFile();
			$obj->file_id=$file_id;
			$obj->saveLog(2,3,NULL,$file_real_filename['version']);
		}
	}
	else
	{
		$AppUI->setMsg( "fileIdError", UI_MSG_ERROR );
		$AppUI->redirect();
	}
}

function download($file, $path_real, $file_id=NULL)
{
	global $AppUI;

	if (!file_exists( $path_real ))
	{
  		$AppUI->setMsg( "El archivo: {$file['file_name']} ($path_real) NO EXISTE", UI_MSG_ERROR );
		$AppUI->redirect();
  	}

	// BEGIN extra headers to resolve IE caching bug (JRP 9 Feb 2003)
	// [http://bugs.php.net/bug.php?id=16173]
	header("Pragma: ");
	header("Cache-Control: ");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Cache-Control: private, max-age=1, pre-check=10");
	// END extra headers to resolve IE caching bug


	//Hay un bug con IE7 que reemplaza los espacios x _. Con esta linea lo solucionamos
	if (browser() == "MSIE")
		$file['file_name']=str_replace(" ","%20",$file['file_name']);

	header("MIME-Version: 1.0");
	header( "Content-length: {$file['file_size']}" );
	header( "Content-type: {$file['file_type']}" );
  header( "Content-transfer-encoding: binary\n");
	header( "Content-Disposition: attachment; filename=\"{$file['file_name']}\"");
	readfile($path_real);
}

	/**
	 * \brief Devuelve el id de la ultima version del archivo que se pase como parametro
	 * \author Fede Ravizzini
	 * \date 26/12/06
	 * \version 1.0
	 * \return FALSE en caso de error.
	 */
	function get_last_id_file_ver_signed($file_id)
	{
		$sql =  " SELECT fv.id_files_ver";
		$sql .= " FROM files_versions fv";
		$sql .= " INNER JOIN documents_history dh ON CAST(fv.version As Char(5)) = dh.history_additional_id";
		$sql .= " WHERE dh.history_document_id = ".$file_id;
		$sql .= " AND dh.history_document_type = 2";
		$sql .= " AND dh.history_action = 4";
		$sql .= " AND fv.file_id = ".$file_id;
		$sql .= " AND fv.delete_pending = 0";
		$sql .= " ORDER BY fv.id_files_ver";
		$sql .= " DESC LIMIT 1";
							
		$resultado = mysql_query( $sql );
		$row = mysql_fetch_array($resultado);
				
		if ($row != null)
			return  $row[0];
		
		$sql =  " SELECT id_files_ver";
		$sql .= " FROM files_versions";
		$sql .= " WHERE file_id = ".$file_id;
		$sql .= " AND delete_pending = 0";
		$sql .= " ORDER BY id_files_ver";
		$sql .= " DESC LIMIT 1";

		$resultado = mysql_query( $sql );
		$row = mysql_fetch_array($resultado);
			
		if ($row != null)
		return  $row[0];
			
		return FALSE;
	}

	/**
	 * \brief Devuelve el nombre del navegador que se esta usando
	 * \date 23/02/07
	 */
	function browser()
	{
		if ((ereg("Nav", $_SERVER["HTTP_USER_AGENT"])) || (ereg("Gold", $_SERVER["HTTP_USER_AGENT"])) || (ereg("X11", $_SERVER["HTTP_USER_AGENT"])) || (ereg("Mozilla", $_SERVER["HTTP_USER_AGENT"])) || (ereg("Netscape", $_SERVER["HTTP_USER_AGENT"])) AND (!ereg("MSIE", $_SERVER["HTTP_USER_AGENT"])) AND (!ereg("Konqueror", $_SERVER["HTTP_USER_AGENT"])) AND (!ereg("Firefox", $_SERVER["HTTP_USER_AGENT"]))) $browser = "Netscape";
		elseif(ereg("Firefox", $_SERVER["HTTP_USER_AGENT"])) $browser = "FireFox";
		elseif(ereg("MSIE", $_SERVER["HTTP_USER_AGENT"])) $browser = "MSIE";
		elseif(ereg("Lynx", $_SERVER["HTTP_USER_AGENT"])) $browser = "Lynx";
		elseif(ereg("Opera", $_SERVER["HTTP_USER_AGENT"])) $browser = "Opera";
		elseif(ereg("WebTV", $_SERVER["HTTP_USER_AGENT"])) $browser = "WebTV";
		elseif(ereg("Konqueror", $_SERVER["HTTP_USER_AGENT"])) $browser = "Konqueror";
		elseif((eregi("bot", $_SERVER["HTTP_USER_AGENT"])) || (ereg("Google", $_SERVER["HTTP_USER_AGENT"])) || (ereg("Slurp", $_SERVER["HTTP_USER_AGENT"])) || (ereg("Scooter", $_SERVER["HTTP_USER_AGENT"])) || (eregi("Spider", $_SERVER["HTTP_USER_AGENT"])) || (eregi("Infoseek", $_SERVER["HTTP_USER_AGENT"]))) $browser = "Bot";
		else $browser = "Other";

		return $browser;
	}
?>
