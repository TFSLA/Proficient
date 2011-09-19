<?php /* FILES $Id: files.class.php,v 1.2 2011-07-12 04:52:11 pkerestezachi Exp $ */

require_once( $AppUI->getSystemClass( 'projects' ) );
//include_once( "./modules/files/funciones_varias.php");
/**
* File Class
*/
class CFile extends CDpObject {
	var $file_id = NULL;
	var $file_project = NULL;
	var $_file_real_filename = NULL;
	var $file_task = NULL;
	var $file_opportunity = NULL;
	var $file_name = NULL;
	var $file_description = NULL;
	var $file_type = NULL;
	var $file_owner = NULL;
	var $file_date = NULL;
	var $file_size = NULL;
	var $file_section = NULL;
	var $file_category = NULL;
	var $date_modified = NULL;
	var $is_protected = NULL;
	var $is_private = NULL;

	//Asociamos al objeto con la tabla files y la primary key es file_id
	function CFile() {
		$this->CDpObject( 'files', 'file_id' );
	}

	function check() {
	// ensure the integrity of some variables
		$this->file_id = intval( $this->file_id );
		//$this->file_parent = intval( $this->file_parent );
		$this->file_task = intval( $this->file_task );
		$this->file_project = intval( $this->file_project );

		return NULL; // object is ok
	}

	function delete()
	{
		global $AppUI;
	// remove the file from the file system
		//@unlink( "{$AppUI->cfg['root_dir']}/files/$this->file_real_filename" );
	/*
	// delete any index entries
		$sql = "DELETE FROM files_index WHERE file_id = $this->file_id";
		if (!db_exec( $sql )) {
			return db_error();
		}*/
	// delete the main table reference
		$sql = "UPDATE files SET file_delete_pending = 1, file_date_delete = FROM_UNIXTIME(UNIX_TIMESTAMP()) WHERE file_id = $this->file_id";

		if (!db_exec( $sql ))
		{
			return db_error();
		}

		return NULL;
	}

	function recovery()
	{
		global $AppUI;
		$sql = "UPDATE files SET file_delete_pending = 0, file_date_delete = '0000-00-00 00:00:00' WHERE file_id = $this->file_id";
		if (!db_exec( $sql ))
		{
			return db_error();
		}

		return NULL;
	}



// move a file from a temporary (uploaded) location to the file system
	function moveTemp( $upload ) {
		global $AppUI;

		// check that directories are created
		@mkdir( "{$AppUI->cfg['root_dir']}/files", 0777 );

		$this->_filepath = "{$AppUI->cfg['root_dir']}/files/$this->_file_real_filename";

	// Funcion de php que guarda el archivo cargado en el directorio que se le ordene.
		return move_uploaded_file( $upload['tmp_name'], $this->_filepath );
	}

// parse file for indexing
/*	function indexStrings() {
		GLOBAL $ft, $AppUI;
	// get the parser application
		$parser = @$ft[$this->file_type];
		if (!$parser) {
			return false;
		}
	// buffer the file
		$fp = fopen( $this->_filepath, "rb" );
		$x = fread( $fp, $this->file_size );
		fclose( $fp );
	// parse it
		$parser = $parser . " " . $this->_filepath;
		$pos = strpos( $parser, '/pdf' );
		if (false !== $pos) {
			$x = `$parser -`;
		} else {
			$x = `$parser`;
		}
	// if nothing, return
		if (strlen( $x ) < 1) {
			return 0;
		}
	// remove punctuation and parse the strings
		$x = str_replace( array( ".", ",", "!", "@", "(", ")" ), " ", $x );
		$warr = split( "[[:space:]]", $x );

		$wordarr = array();
		$nwords = count( $warr );
		for ($x=0; $x < $nwords; $x++) {
			$newword = $warr[$x];
			if (!ereg( "[[:punct:]]", $newword )
				&& strlen( trim( $newword ) ) > 2
				&& !ereg( "[[:digit:]]", $newword )) {
				$wordarr[] = array( "word" => $newword, "wordplace" => $x );
			}
		}
		db_exec( "LOCK TABLES files_index WRITE" );
	// filter out common strings
		$ignore = array();
		include "{$AppUI->cfg['root_dir']}/modules/files/file_index_ignore.php";
		foreach ($ignore as $w) {
			unset( $wordarr[$w] );
		}
	// insert the strings into the table
		while (list( $key, $val ) = each( $wordarr )) {
			$sql = "INSERT INTO files_index VALUES ('" . $this->file_id . "', '" . $wordarr[$key]['word'] . "', '" . $wordarr[$key]['wordplace'] . "')";
			db_exec( $sql );
		}

		db_exec( "UNLOCK TABLES;" );
		return nwords;
	}*/

	function canEdit($file_id=NULL){
		global $AppUI;
		
		if ($file_id==NULL){
			$file_id = $this->file_id;
			$obj = $this;
		}else{
			$sql = "
			SELECT files.*,
				user_username,
				user_first_name,
				user_last_name,
				project_id,
				projects.project_owner,
				task_id, task_name
			FROM files
			LEFT JOIN users ON file_owner = user_id
			LEFT JOIN projects ON project_id = file_project
			LEFT JOIN tasks ON task_id = file_task
			WHERE file_id = $file_id
			";

			// check if this record has dependancies to prevent deletion
			$msg = '';
			$obj = new CFile();

			// load the record data
			$obj = null;
			if (!db_loadObject( $sql, $obj ) && $file_id > 0) {
				$AppUI->setMsg( 'File' );
				$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
				$AppUI->redirect();
			}
		}

		$prole = new CProjectRoles();
		$prjUsers = $prole->getAssignedUsers(2 ,(int)$obj->project_id);
		//$nombre_usuario = $AppUI->user_last_name.', '. $AppUI->user_first_name;

		$canEdit = !getDenyEdit( "files", $file_id );
		$canEdit = $canEdit && 	($AppUI->user_id ==  $obj->file_owner				//si el user lo creo
														||	$obj->project_owner == $AppUI->user_id	// si es owner del proy
														|| 	array_key_exists($AppUI->user_id, CProject::getOwners($obj->project_id))
														|| 	$AppUI->user_type == 1 								// si es sysadmin
														||  array_key_exists($AppUI->user_id, $prjUsers )  // si es usuario del proyecto al que pertenece el archivo
														||  $obj->project_id == 0); //Si no pertenece a ningun proyecto

		return $canEdit;
	}

	function canDelete($file_id=NULL){
		global $AppUI;
		if ($file_id==NULL){
			$file_id = $this->file_id;
			$obj = $this;
		}else{
			$sql = "
			SELECT files.*,
				user_username,
				user_first_name,
				user_last_name,
				project_id,
				projects.project_owner,
				task_id, task_name
			FROM files
			LEFT JOIN users ON file_owner = user_id
			LEFT JOIN projects ON project_id = file_project
			LEFT JOIN tasks ON task_id = file_task
			WHERE file_id = $file_id
			";

			// check if this record has dependancies to prevent deletion
			$msg = '';
			$obj = new CFile();

			// load the record data
			$obj = null;
			if (!db_loadObject( $sql, $obj ) && $file_id > 0) {
				$AppUI->setMsg( 'File' );
				$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
				$AppUI->redirect();
			}
		}

		/*
		* array_key_exists -- Comprueba si el índice o clave dada existe en la matriz
		* in_array -- Revisa si un valor existe en una matriz
		*/

		$canEdit = !getDenyEdit( "files", $file_id );
		$canEdit = $canEdit && 	($AppUI->user_id ==  $obj->file_owner				//si el user lo creo
														||	$obj->project_owner == $AppUI->user_id	// si es owner del proy
														|| 	array_key_exists($AppUI->user_id, CProject::getOwners($obj->project_id)) //Alguno de los admins
														|| 	$AppUI->user_type == 1 	);							// si es sysadmin

		return $canEdit;
	}

	function canDeleteVer($file_id=NULL, $ver_owner=NULL)
	{
		global $AppUI;
		if ($file_id==NULL){
			$file_id = $this->file_id;
			$obj = $this;
		}else{
			$sql = "
			SELECT files.*,
				user_username,
				user_first_name,
				user_last_name,
				project_id,
				projects.project_owner,
				task_id, task_name
			FROM files
			LEFT JOIN users ON file_owner = user_id
			LEFT JOIN projects ON project_id = file_project
			LEFT JOIN tasks ON task_id = file_task
			WHERE file_id = $file_id
			";

			// check if this record has dependancies to prevent deletion
			$msg = '';
			$obj = new CFile();

			// load the record data
			$obj = null;
			if (!db_loadObject( $sql, $obj ) && $file_id > 0) {
				$AppUI->setMsg( 'File' );
				$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
				$AppUI->redirect();
			}
		}

		$canEdit = !getDenyEdit( "files", $file_id );
		$canEdit = $canEdit && 	($AppUI->user_id ==  $obj->file_owner //si el user que creo el archivo
														||	$AppUI->user_id ==  $ver_owner // Si es el dueño de la version
														||	$obj->project_owner == $AppUI->user_id	// si es owner del proy
														|| 	array_key_exists($AppUI->user_id, CProject::getOwners($obj->project_id)) //Alguno de los admins
														|| 	$AppUI->user_type == 1 	);							// si es sysadmin

		return $canEdit;
	}


	/**
	 * \brief Graba en la DB el nuevo archivo o la nueva version.
	 * Se actualizan las entradas en las tablas files y files_versions
	 * \author Fede Ravizzini
	 * \date 20/12/06
	 * \version 1.0
	 * \return Si fallo store() el msg que devuelve este, si falla la consulta con la DB el error de la misma sino todo ok
	 */
	function store_fede($file_id, $description, $tipo_cambio, $author)
	{
		global $AppUI;

		$version_file_name = $this->_file_real_filename;
		$date = db_unix2dateTime( time() );
        $this->date_modified = date("Y-m-d H:i:s");

		if ( $file_id == 0 ) //Si es un archivo nuevo
		{
			$msg = $this->store();//Llamo a la funcion padre de store que crea la entrada en la tabla FILES

			$sql_file_id = "SELECT file_id FROM files ORDER BY file_id DESC limit 1";
			$resultado = mysql_query( $sql_file_id );
			if ( $resultado == FALSE)
				return mysql_error();

			$row = mysql_fetch_array($resultado); //Guardo el valor que se le asigno al archivo nuevo
			$file_id = $row[0];
			$description = 'Carga Inicial';
			$version = 1;
		}
		elseif ( $version_file_name == NULL )
		{
			$this->saveLog(2,5);//minor update
			return $this->store();//Si se da el caso que solamente actualiza los datos, sin subir nada
		}

		else //Si es una nueva version de un archivo existenete
		{		
			if ( !$this->same_name() )
			{
				$AppUI->setMsg( "El nombre del nuevo archivo que intenta subir no coincide con el existente", UI_MSG_ERROR );
				$AppUI->redirect("m=files&a=addedit&file_id=".$file_id);
			}

			$msg = $this->store();//Llamo a la funcion padre de store que crea la entrada en la tabla FILES

			if ( $tipo_cambio == 'grande' )
				$version =  intval($this->get_file_last_version_without_del() + 1).".0";
			else
				$version = $this->get_file_last_version_without_del()+ 0.1;
		}
		
		$sql = sprintf( "INSERT INTO files_versions (file_id, description, version, version_file_name, author, date) SELECT %d, '%s', %.2f, '%s', %d, '%s' FROM files ORDER BY file_id DESC limit 1", $file_id, $description, $version, $version_file_name, $author, $date  );

		if (!mysql_query( $sql ) )
		{
			return mysql_error();
		}
		else
		{
			if ($version > 1)
				$this->saveLog(2,2);//major update
			return $msg;
		}
	}

	/**
	 * \brief Devuelve la última versión del archivo que esta asociado con el objeto
	 * \author Fede Ravizzini
	 * \date 20/12/06
	 * \version 1.0
	 * \return FALSE en caso de error.
	 */
	function get_file_last_version_with_del()
	{
		return get_file_last_version_with_del( $this->file_id);
	}

	function get_file_last_version_without_del()
	{
		return get_file_last_version_without_del( $this->file_id);
	}

		/**
	 * \brief Devuelve verdadero si el nombre del archivo que se esta subiendo coincide con el existente
	 * \author Fede Ravizzini
	 * \date 20/12/06
	 * \version 1.0
	 * \return FALSE en caso de que sean sistintos.
	 */
	function same_name()
	{
		global $AppUI;
		$sql = "SELECT file_name FROM files WHERE file_id = " .$this->file_id;
		$resultado = mysql_query( $sql );
		if ( $resultado == FALSE)
			return FALSE;

		return ($this->file_name == mysql_result($resultado, 0));
	}

	
	function notifyNewKnowledge($toNotify, $update=null){
		global $AppUI;
		require_once($AppUI->getModuleClass('projects'));
		if($toNotify != 0){ //0: No Notify
			$objPrj = new CProject();
			$objPrj->project_id = $this->file_project;
			
			$toList = array();
			
			if($toNotify == 1){  // Administrators + Users
				$toList = $objPrj->getUsers($this->project,true);
				if(!empty($this->file_task)){		//Validar que los users tengan acceso a la tarea
					while(LIST($user_id,$user_mail) = EACH($toList)){
						$Cproject = new CProjects();
						$Cproject->loadTasks($this->file_project, $user_id);
						$tasks = $Cproject->Tasks();
							
						for($i=0; $i<count($tasks); $i++){
							if($tasks[$i]["task_id"] == $this->file_task){
								$vecMails[$user_id] = $user_mail;
							}
						}
					}
					$toList = $vecMails;
					//die("<pre>".print_r($vecMails)."</pre>");
				}
			}
			
			$projectOwner = $objPrj->getOwner($this->file_project);
			$projectAdministrators = $objPrj->getOwners($this->file_project,true);
			
			while(LIST($adminID,$adminMail) = EACH($projectAdministrators)){
				if(!array_key_exists($adminID,$toList)){
					$toList[$adminID] = $adminMail;
				}
			}
			
			while(LIST($adminID,$adminMail) = EACH($projectOwner)){
				if(!array_key_exists($adminID,$toList)){
					$toList[$adminID] = $adminMail;
				}
			}
		}else{
			return null;
		}
		
		if($update){
			$mail_title = "Document updated";
			$msg = "ProjectDocumentUpdated";
		}else{
			$mail_title = null;
			$msg = null;
		}
		
		//die("<pre>".print_r($toList)."</pre>");
		while(LIST($user_id,$user_mail) = EACH($toList)){
			$objMail = new CFileNotificationMail();
			$objMail->load($this->file_id,$user_id,$mail_title,$msg);
			$error = $objMail->notifyToUser();
			if(!$error){
				echo "Error al enviar a: ".$user_mail;
			}
		}
	}
	
	function saveLog($type,$action,$comment=NULL,$additional_id=0) {
		
		global $AppUI;
		
		$doInsert = true;
		
		if ($additional_id == 0)
		{
			//require_once "./functions/files_func.php";
			require_once($AppUI->getFunctionFile( 'files' ));
			$version = get_file_last_version_with_del($this->file_id);
		}
		else
			$version = $additional_id;
		
		if($action == 3)
		{
			$sql = "select history_action from documents_history ";
			$sql .= "where history_document_id = ".$this->file_id." ";
			$sql .= "and history_document_type = ".$type." ";
			$sql .= "and history_additional_id = '".str_replace(',','.', $version)."' ";
			$sql .= "and (history_action = 2 or history_action = 4 or history_action = 5 ";
			$sql .= "or (history_action = 3 and history_user_id = ".$AppUI->user_id.")) ";
			$sql .= "order by history_date desc limit 0,1";
					
			$actionTypes = db_loadColumn($sql);
		
			if($actionTypes[0] != '3')
				$doInsert = true;
			else
				$doInsert = false;
		}		
		
		if($doInsert)
		{
			$sql = "INSERT INTO documents_history ( ";
			$sql .= "history_document_id, ";
			$sql .= "history_document_type, ";
			$sql .= "history_action, ";
			$sql .= "history_user_id, ";
			$sql .= "history_date, ";
			$sql .= "history_additional_id, ";
			$sql .= "history_comment) VALUES ( ";
			$sql .= $this->file_id.", ";
			$sql .= $type.", ";
			$sql .= $action.", ";
			$sql .= $AppUI->user_id.", ";
			$sql .= "'".date("Y-m-d H:i:s")."', ";
			$sql .= "'".str_replace(',','.', $version)."', ";
			$sql .= ($comment == '' ?  'NULL' : "'".$comment."'").")";

			mysql_query($sql) or die(mysql_error());
		}
	}
	
	function getHistory($item_id, $item_type, $action_type, $additional_id=0)
	{	
		$sql = "select CONCAT(user_last_name,', ', user_first_name) as fullname, history_date from documents_history ";
		$sql .= "inner join users on users.user_id = history_user_id ";
		$sql .= "where history_document_id = ".$item_id." ";
		$sql .= "and history_document_type = ".$item_type." ";
		$sql .= "and history_action = ".$action_type." ";
		$sql .= "and history_additional_id = '".str_replace(',','.', $additional_id)."' ";
		$sql .= "order by history_date desc limit 0,1";
					
		$result = mysql_query($sql) or die(mysql_error());
		return(mysql_fetch_array($result));
	}
	
	function getLastActionHistory($item_id, $item_type)
	{
		$sql = "select CONCAT(user_last_name,', ', user_first_name) as fullname, history_action, history_date from documents_history
				inner join users on users.user_id = history_user_id
				where history_document_id = $item_id
				and history_document_type = $item_type
				and history_action <> 3 AND history_action <> 5
				order by history_date desc limit 0,1";
					
		$result = mysql_query($sql) or die(mysql_error());
		return(mysql_fetch_array($result));
	}	
	
	function showHistory() {
		
		/*
			1 = deleted
			2 = content update
			3 = viewed
			4 = approved
			5 = properties update
		*/
		
		global $AppUI;
		$df = $AppUI->getPref('SHDATEFORMAT').' '.$AppUI->getPref('TIMEFORMAT');
		
		$html = "<hr>";
		$html .= "<table width='100%' border='0' celpadding='0' cellspacing='0' align='center'>";
		$html .= "	<tr>";
		$html .= "		<th colspan='4' align='left'>".$AppUI->_('History')."</th>";
		$html .= "	</tr>";
		$html .= "	<tr bgcolor='gray'>";
		$html .= "		<th align='left' width='20'></th>";
		$html .= "		<th align='left'>".$AppUI->_("Date")."</th>";
		$html .= "		<th align='left'>".$AppUI->_("Action")."</th>";
		$html .= "		<th align='left'>".$AppUI->_("User")."</th>";
		$html .= "	</tr>";
		$html .= "";
		
		$sql = "SELECT file_owner, file_date FROM files WHERE file_id = ".$this->file_id;
		$result = mysql_query($sql) or die(mysql_error());
		$kbData = mysql_fetch_array($result);
		$createDate = new CDate($kbData["file_date"]);
		$createHour = substr($kbData["file_date"],11,5);		
		
		$user_query = "SELECT CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name FROM users u WHERE user_id=".$kbData['file_owner'];
		$user_result_created = mysql_query($user_query) or die(mysql_error());
		$user_name_created = mysql_fetch_array($user_result_created);
		
		$sql = "SELECT * FROM documents_history WHERE history_document_id = ".$this->file_id." 
			AND history_document_type = 2 ORDER BY history_date DESC";
		$result = mysql_query($sql) or die(mysql_error());
		$i = 1;
		
		while($row = mysql_fetch_array($result)){
			$i++;
			$date = new CDate($row["history_date"]);
			$hour = substr($row["history_date"],11,5);
			
			$user_query = "SELECT CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name FROM users u WHERE user_id=".$row['history_user_id'];
			$user_result = mysql_query($user_query) or die(mysql_error());
			$user_name = mysql_fetch_array($user_result);
			
			if($i%2==0) $bgcolor="bgcolor='DDDDDD'";
			else $bgcolor="bgcolor='white'";
			
			$version = ($row['history_additional_id'] ? ' ['.$AppUI->_('Version').' '.$row['history_additional_id'].']' : '');
			$comment = ($row['history_comment'] ? '<br/><u>'.$AppUI->_('comment').'</u>: '.mb_convert_encoding($row['history_comment'], "ISO-8859-1", "UTF-8") : '');
			
			switch($row['history_action'])
			{
				case 1:
					$action_text='deleted';
					$color = "style=\"color:'red';\"";
					break;
				case 2:
					$action_text='content update';
					$color = "style=\"color:'blue';\"";
					break;
				case 3:
					$action_text='viewed';
					$color = "style=\"color:'gray';\"";
					break;
				case 4:
					$action_text='approved';
					$color = "style=\"color:'green';\"";
					break;
				case 5:
					$action_text='properties update';
					$color = "style=\"color:'blue';\"";
					break;					
			}			
			
			$html .= "	<tr  $bgcolor $color>";
			$html .= "		<td align='left'>";
			$html .= "		</td>";
			$html .= "		<td align='left'>".$date->format($df);
			$html .= "		</td>";
			$html .= "		<td align='left'>".$AppUI->_($type_text)." ".$AppUI->_($action_text).$version.$comment;
			$html .= "		</td>";
			$html .= "		<td align='left'>".$user_name['user_name'];
			$html .= "		</td>";
			$html .= "	</tr>";
		}
		
		$i++;
		if($i%2==0) $bgcolor="bgcolor='DDDDDD'";
		else $bgcolor="bgcolor='white'";
		
		$html .= "	<tr  $bgcolor style=\"color:'blue';\">";
		$html .= "		<td align='left'>";
		$html .= "		</td>";
		$html .= "		<td align='left'>".$createDate->format($df);
		$html .= "		</td>";
		$html .= "		<td align='left'>".$AppUI->_('created');
		$html .= "		</td>";
		$html .= "		<td align='left'>".$user_name_created['user_name'];
		$html .= "		</td>";
		$html .= "	</tr>";
		
		$html .= "</table>";
		$html .= "<hr>";
		echo $html;
	}	
}

/**
* Knowledge Mailing Class
*/
class CFileNotificationMail {
	var $to_user_id = NULL;
	var $knowledge_id = NULL;
	
	//Mail Data
	var $mail_title = NULL;
	var $mail_body = NULL;
	var $mail_from = NULL;
	
	function CFileNotificationMail(){
		global $AppUI;
		$this->mail_from = $AppUI->getConfig("mailfrom");
	}
	
	function load($knowledge_id, $to_user_id, $mail_title=null, $msg=null, $mail_body=null){
		global $AppUI;
		
		$this->knowledge_id = $knowledge_id;
		$this->to_user_id = $to_user_id;
		
		$usr = new CUser();
		$usr->load($this->to_user_id);
		
		$prefs = CUser::getUserPrefs($usr->user_id);
		$user_language = isset($prefs["LOCALE"]) ? $prefs["LOCALE"] : $AppUI->getConfig("host_locale");
		
		if(!($mail_title)){
			$mail_title = "New document";
		}
		$this->mail_title = $AppUI->_to($user_language,$mail_title);
		
		$Ccompany = new CCompany();
		if($Ccompany->load($usr->user_company)){
			if(!empty($Ccompany->company_email)){
				$this->mail_from = $Ccompany->company_email;
			}
		}
		
		if(!($mail_body)){
			$df = isset($prefs["SHDATEFORMAT"]) ? $prefs["SHDATEFORMAT"] : $AppUI->getPref('SHDATEFORMAT');
			
			$sql = "SELECT * FROM files WHERE file_id = ".$this->knowledge_id;
			$file_data = mysql_fetch_array(mysql_query($sql));
			
			$sql = "SELECT project_name FROM projects WHERE project_id = ".$file_data['file_project'];
			$project_data = mysql_fetch_array(mysql_query($sql));
			
			$projectName = $project_data['project_name'];
			
			$this->mail_title = '[Proficient / '.$projectName.'] '.$this->mail_title;
			
			$typeName = $AppUI->_to($user_language,'File');
			
			$date = new CDate($file_data["file_date"]);
			
			$hour = substr( strval($file_data['file_date']),11,2);
			$min = substr( strval($file_data['file_date']),14,2);
			$hour = $hour.":".$min;
			
			if(!$msg){
				$msg = "NewDocumentAdded";
			}
			
			$this->mail_body = @"\n".
				$AppUI->_to($user_language,$msg)."\n".
				"\n".str_repeat("=",70)."\n".
				$AppUI->getConfig('base_url')."/index_inc.php?inc=./modules/files/show_versions.php&m=files&file_id=".$this->knowledge_id.
				"\n".str_repeat("=",70)."\n".
				$AppUI->_to($user_language,"Details").
				"\n".str_repeat("=",70)."\n".
				$AppUI->_to($user_language,"Project").": $projectName\n".
				$AppUI->_to($user_language,"Type").": $typeName\n".
				$AppUI->_to($user_language,"Date").": ".$date->format($df)." ".$hour."\n".
				$AppUI->_to($user_language,"Name").": ".$file_data['file_name']."\n".
				$AppUI->_to($user_language,"Summary").": ".$file_data['file_description']."\n".
				str_repeat("=",70)."\n\n";
		}else{
			$this->mail_body = $mail_body;
		}
		
		$this->mail_body = ereg_replace("&aacute;","á",$this->mail_body);
		$this->mail_body = ereg_replace("&eacute;","é",$this->mail_body);
		$this->mail_body = ereg_replace("&iacute;","í",$this->mail_body);
		$this->mail_body = ereg_replace("&oacute;","ó",$this->mail_body);
		$this->mail_body = ereg_replace("&uacute;","ú",$this->mail_body);
	}
	
	function notifyToUser(){
		$usr = new CUser();
		$usr->load($this->to_user_id);
		$msgBody = $this->mail_body;
		$strEmailFrom = $this->mail_from;
		
		$msg = mail($usr->user_email, $this->mail_title,$msgBody, 'From: '.$strEmailFrom);
		return $msg;
	}
}


class CFileTypes extends CDpObject {
	var $id = NULL;
	var $extension = NULL;
	var $mime = NULL;
	var $friendly = NULL;
	var $image = NULL;

	function CFileTypes() {
		$this->CDpObject( 'file_types', 'id' );
	}

	function loadByExtension($extension){
		$sql = "select id from file_types where lower(extension) = lower('$extension')
				union
				select id from file_types where extension = ''";
		$id = db_loadResult($sql);

		return $this->load($id);
	}
}
?>
