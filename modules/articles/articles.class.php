<?php 
require_once( $AppUI->getSystemClass('projects') );
include_once "{$AppUI->cfg['root_dir']}/includes/permissions.php";
/**
* Article Class
*/
class CArticle extends CDpObject {
	var $article_id = NULL;
	var $articlesection_id = NULL;
	var $file_category = NULL;
	var $date = NULL;
	var $articles_reads = NULL;
	var $user_id = NULL;
	var $title = NULL;
	var $abstract = NULL;
	var $body = NULL;
	var $project = NULL;
	var $task = NULL;
	var $opportunity = NULL;
	var $date_modified = NULL;
	var $is_protected = NULL;
	var $is_private = NULL;
	

	function CArticle() {
		$this->CDpObject( 'articles', 'article_id' );
	}

	function check() {
		if ($this->article_id === NULL) {
			return 'article id is NULL';
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function delete() {
		$sql = "DELETE FROM articles WHERE article_id = '$this->article_id';";
  		if (!db_exec( $sql )) {
			return db_error();
		} else {
		   return NULL;
		}
	}

	function store() {

		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed";
		}
		if( $this->article_id ) {
			$ret = db_updateObject( 'articles', $this, 'article_id', false );
		} else {

			$ret = db_insertObject( 'articles', $this, 'article_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}
	
	function saveLog($type,$action,$comment=NULL) {
		
		global $AppUI;
		
		$doInsert = true;
		
		if($action == 3)
		{
			$sql = "select history_action from documents_history
					where history_document_id = $this->article_id
					and history_document_type = $type
					and (history_action = 2 or history_action = 4 or history_action = 5
					or (history_action = 3 and history_user_id = $AppUI->user_id))
					order by history_date desc limit 0,1";
					
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
			$sql .= "history_comment) VALUES ( ";
			$sql .= $this->article_id.", ";
			$sql .= $type.", ";
			$sql .= $action.", ";
			$sql .= $AppUI->user_id.", ";
			$sql .= "'".date("Y-m-d H:i:s")."', ";
			$sql .= ($comment == '' ?  'NULL' : "'".$comment."'").")";

			mysql_query($sql) or die(mysql_error());
		}
	}
	
	function getHistory($item_id, $item_type, $action_type)
	{
		$sql = "select CONCAT(user_last_name,', ', user_first_name) as fullname, history_date from documents_history
				inner join users on users.user_id = history_user_id
				where history_document_id = $item_id
				and history_document_type = $item_type
				and history_action = $action_type
				order by history_date desc limit 0,1";
				
		$result = mysql_query($sql) or die(mysql_error());
		return(mysql_fetch_array($result));
	}	
	
	function getLastActionHistory($item_id, $item_type)
	{
		$sql = "select CONCAT(user_last_name,', ', user_first_name) as fullname, history_action, history_date from documents_history
				inner join users on users.user_id = history_user_id
				where history_document_id = '$item_id'
				and history_document_type = '$item_type'
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
		
		$sql = "SELECT user_id, date, type FROM articles WHERE article_id = ".$this->article_id;
		$result = mysql_query($sql) or die(mysql_error());
		$kbData = mysql_fetch_array($result);
		
		$createDate = new CDate($kbData["date"]);
		$createHour = substr($kbData["date"],11,5);
		
		$user_query = "SELECT CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name FROM users u WHERE user_id=".$kbData['user_id'];
		$user_result_created = mysql_query($user_query) or die(mysql_error());
		$user_name_created = mysql_fetch_array($user_result_created);
		
		$sql = "SELECT * FROM documents_history WHERE history_document_id = ".$this->article_id." 
			AND (history_document_type = 0 OR history_document_type = 1) ORDER BY history_date DESC";
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
			
			$comment = ($row['history_comment'] ? '<br/><u>'.$AppUI->_('comment').'</u>: '.mb_convert_encoding($row['history_comment'], "ISO-8859-1", "UTF-8") : '');
			
			switch($row['history_action'])
			{
				case 1:
					$action_text='deleted';
					$color = "style=\"color:'blue';\"";
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
			$html .= "		<td align='left'>".$AppUI->_($action_text).$comment;
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
		$html .= "		<td align='left'>".$AppUI->_($type_text)." ".$AppUI->_('created');
		$html .= "		</td>";
		$html .= "		<td align='left'>".$user_name_created['user_name'];
		$html .= "		</td>";
		$html .= "	</tr>";
		
		$html .= "</table>";
		$html .= "<hr>";
		echo $html;
	}
	
	
	function getNotifyHTML($selected=0){
		global $AppUI;
		
		$notify_types = array_merge(
			array(0=>$AppUI->_('No Notify')),
			array(1=>$AppUI->_('Project Users')),
			array(2=>$AppUI->_('Project Administrators'))
		);
		
		if($_GET['m']=='articles' || $_GET['m']=='files' || $_GET['m']=='pipeline') $disabled='disabled';
		else $selected=1;
		
		echo arraySelect( $notify_types , 'notify_type', 'size="1" class="text" style="width:270px" '.$disabled, $selected);
	}
	
	
	function notifyNewKnowledge($toNotify, $update=null){
		global $AppUI;
		require_once($AppUI->getModuleClass('projects'));
		if($toNotify != 0){ //0: No Notify
			$objPrj = new CProject();
			$objPrj->project_id = $this->project;
			
			$toList = array();
			
			if($toNotify == 1){  // Administrators + Users
				$toList = $objPrj->getUsers($this->project,true);
				if(!empty($this->task)){		//Validar que los users tengan acceso a la tarea
					while(LIST($user_id,$user_mail) = EACH($toList)){
						$Cproject = new CProjects();
						$Cproject->loadTasks($this->project, $user_id);
						$tasks = $Cproject->Tasks();
							
						for($i=0; $i<count($tasks); $i++){
							if($tasks[$i]["task_id"] == $this->task){
								$vecMails[$user_id] = $user_mail;
							}
						}
					}
					$toList = $vecMails;
					//die("<pre>".print_r($vecMails)."</pre>");
				}
			}
			
			//die(print_r($toList));
			
			$projectOwner = $objPrj->getOwner($this->project);
			$projectAdministrators = $objPrj->getOwners($this->project,true);
			
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
		
		while(LIST($user_id,$user_mail) = EACH($toList)){
			$objMail = new CKnowledgeNotificationMail();
			$objMail->load($this->article_id,$user_id,$mail_title,$msg);
			$error = $objMail->notifyToUser();
			if(!error){
				echo"Error al enviar a: ".$user_mail;
			}
		}
	}
	
	function canDelete($article_id=NULL)
	{
		global $AppUI;
		
		if ($article_id == NULL)
		{
			$article_id = $this->article_id;
			$obj = $this;
		}
		else
		{
			$sql = "
			SELECT articles.*,
				projects.project_id,
				projects.project_owner,
				tasks.task_id
			FROM articles
			LEFT JOIN users ON articles.user_id = users.user_id
			LEFT JOIN projects ON projects.project_id = articles.project
			LEFT JOIN tasks ON tasks.task_id = articles.task
			WHERE articles.article_id = $article_id
			";

			// check if this record has dependancies to prevent deletion
			$msg = '';
			$obj = new CArticle();

			// load the record data
			$obj = null;
			if (!db_loadObject( $sql, $obj ) && $article_id > 0) {
				$AppUI->setMsg( 'Article' );
				$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
				$AppUI->redirect();
			}
		}

		/*
		* array_key_exists -- Comprueba si el índice o clave dada existe en la matriz
		* in_array -- Revisa si un valor existe en una matriz
		*/

		$canEdit = ($AppUI->user_id ==  $obj->user_id				//si el user lo creo
														||	$obj->project_owner == $AppUI->user_id	// si es owner del proy
														|| 	array_key_exists($AppUI->user_id, CProject::getOwners($obj->project_id)) //Alguno de los admins
														|| 	$AppUI->user_type == 1 	);							// si es sysadmin

		return $canEdit;
	}	
}

/**
* Knowledge Mailing Class
*/
class CKnowledgeNotificationMail {
	var $to_user_id = NULL;
	var $knowledge_id = NULL;
	
	//Mail Data
	var $mail_title = NULL;
	var $mail_body = NULL;
	var $mail_from = NULL;
	
	function CKnowledgeNotificationMail(){
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
			
			$sql = "SELECT * FROM articles WHERE article_id = ".$this->knowledge_id;
			$article_data = mysql_fetch_array(mysql_query($sql));
			
			$sql = "SELECT project_name FROM projects WHERE project_id = ".$article_data['project'];
			$project_data = mysql_fetch_array(mysql_query($sql));
			
			$projectName = $project_data['project_name'];
			$this->mail_title = '[Proficient / '.$projectName.'] '.$this->mail_title;
			
			$date = new CDate($article_data["date"]);
			
			if($article_data['type']==0){
				$a = "viewarticle";
				$typeName = $AppUI->_to($user_language,"Article");
				$summary = $article_data["abstract"];
			}else{
				$a = "vwlink";
				$typeName = $AppUI->_to($user_language,"Link");
				$summary = $article_data["body"];
			}
			
			$hour = substr( strval($article_data['date']),11,2);
			$min = substr( strval($article_data['date']),14,2);
			$hour = $hour.":".$min;
			
			if(!$msg){
				$msg = "NewDocumentAdded";
			}
			
			$this->mail_body = @"\n".
				$AppUI->_to($user_language,$msg)."\n".
				"\n".str_repeat("=",70)."\n".
				$AppUI->getConfig('base_url')."/index_inc.php?inc=./modules/articles/$a.php&m=articles&id=".$this->knowledge_id.
				"\n".str_repeat("=",70)."\n".
				$AppUI->_to($user_language,"Details").
				"\n".str_repeat("=",70)."\n".
				$AppUI->_to($user_language,"Project").": $projectName\n".
				$AppUI->_to($user_language,"Type").": $typeName\n".
				$AppUI->_to($user_language,"Date").": ".$date->format($df)." ".$hour."\n".
				$AppUI->_to($user_language,"Title").": ".$article_data['title']."\n".
				$AppUI->_to($user_language,"Summary").": ".$summary."\n".
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


/**
* Section Class
*/
class CSection extends CDpObject {
	var $articlesection_id = NULL;
	var $name = NULL;
	var $articlesection_email = NULL;
	var $description = NULL;

	function CSection() {
		$this->CDpObject( 'articlesections', 'articlesection_id' );
	}

	function check() {
		if ($this->articlesection_id === NULL) {
			return 'section id is NULL';
		}
		// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed";
		}
		if( $this->articlesection_id ) {
			$ret = db_updateObject( 'articlesections', $this, 'articlesection_id', false );
		} else {
			$ret = db_insertObject( 'articlesections', $this, 'articlesection_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br />" . db_error();
		} else {
			return NULL;
		}
	}


	function delete() {
		$sql = "DELETE FROM articlesections WHERE articlesection_id = '$this->articlesection_id';";
  		if (!db_exec( $sql )) {
			return db_error();
		} else {
	           $resultx = mysql_query("DELETE FROM articles WHERE articlesection_id = {$this->articlesection_id};");	           
		   return NULL;
		}
	}
	
	function getSectionsByUser()
	{
		global $AppUI;
	
		$projects = CProject::getAllowedRecords($AppUI->user_id, "project_id, project_company");

		foreach ($projects as $project_id => $company_id)
		{
			$arrProjects[$project_id] = $project_id;
			$arrCompanies[$company_id] = $company_id;
		}
		
		$arrCompanies[$AppUI->user_company] = $AppUI->user_company;
		
		$sql = "SELECT articlesection_id FROM articlesections_projects ";
		$sql .= "WHERE company_id IN (". implode( ',', $arrCompanies).") ";
		$sql .= "AND project_id = -1 ";
		$sql .= "UNION ";
		$sql .= "SELECT articlesection_id FROM articlesections_projects ";
		$sql .= "WHERE project_id IN (". implode( ',', $arrProjects).") ";
		
		return (db_loadColumn($sql));
	}
	
	function getComboData($project_id, $section_id, $file_user_id)
	{
		global $AppUI, $m, $from_resource;
		
		$disabled_section = "";
		
		if (!$file_user_id)
			$file_user_id = $AppUI->user_id;
	
		if($projectId > 0)
		{
			// con el proyecto traigo la empresa
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

			if(count($sec_art)!=0)
				$secs_art=implode( ',', $sec_art);
			else
				$secs_art="''";

			$query = "SELECT *
					  FROM articlesections
					  WHERE 1=1";
			if(getDenyRead( 'articles' ))
				$query .= " AND articlesection_id IN ($secs_art)";
		}
		else{
			if(!getDenyRead( 'articles' ))
			    $query = "SELECT * FROM articlesections";
			else
				$query = "SELECT * FROM articlesections WHERE articlesection_id IN (". implode( ',', CSection::getSectionsByUser()).")";
		}
		
		$file_sections = db_loadHashList( $query);
		
		if($m == 'articles')
			$alguna = key($file_sections);//Esto lo hago para elegir alguna seccion cualquiera(la primera) que no sea Ninguna
			
		$file_sections = arrayMerge( array(Ninguna), $file_sections );

		//Solo Agrego la seccion TOP si entra desde el modulo de KB
		if ($m=='articles')
			$file_sections = arrayMerge( array( '-1'=>$AppUI->_('Top')), $file_sections );

		if ($section_id == '')
		{
			if ($m!="files" AND !$from_resource)
				$section_id_selected = $alguna;
			else
				$section_id_selected = array_search('Ninguna', $file_sections);
		}
		else
			$section_id_selected = $section_id;
		
		if($section_id_selected > 0 && !array_key_exists($section_id_selected, $file_sections))
		{
			$objSection = new CSection();
		
			if($objSection->load($section_id_selected, false))
				$file_sections = arrayMerge(array($section_id_selected=>$objSection->name), $file_sections);
			
			if($file_user_id != $AppUI->user_id && $AppUI->user_type != '1') $disabled_section = "disabled";
		}
		
		return array($disabled_section, $section_id_selected, $file_sections);
	}
}



?>