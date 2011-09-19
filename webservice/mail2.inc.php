<?		
	function IsAllowedUser ( $code, $user_email, $item_email, $subject)
	{
		if($code == '14149989')
		{
			include_once('common.inc.php');
			include_once('functions.inc.php');

			$foundEmail = false;
			
			$item_email = strtolower($item_email);
			
			$user_id = getUserIdByEmail( $user_email );

			if($user_id > 0)
			{
				if(!$foundEmail)//Search in projects
				{
					$sql = "SELECT project_id, project_email_docs, project_email_support, project_email_todo from projects ";
					$sql .= " WHERE project_email_docs = '".$item_email."'";
					$sql .= " OR project_email_support = '".$item_email."'";
					$sql .= " OR project_email_todo = '".$item_email."'";
					$sql .= " limit 0,1;";

					if(db_loadHash( $sql, $rowProject ))
					{
						$foundEmail = true;

						$project_id = $rowProject["project_id"];

						if ($rowProject["project_email_docs"] == $item_email)
							$type = "D";

						if ($rowProject["project_email_support"] == $item_email)
							$type = "S";
							
						if ($rowProject["project_email_todo"] == $item_email)
							$type = "T";

						$prjs = getAllowedRecords($user_id, "project_id");

						if(in_array($project_id, $prjs))
							$returnValue = $type."|".$user_id."|".$project_id;
					}
				}
				
				if(!$foundEmail)//Search in kb
				{
					$sql = "SELECT articlesection_id";
					$sql .= " FROM articlesections";
					$sql .= " WHERE articlesection_email = '".$item_email."' limit 0,1;";

					if(db_loadHash( $sql, $rowSection ))
					{
						$foundEmail = true;

						$section_id = $rowSection["articlesection_id"];

						$type = "K";

						$returnValue = $type."|".$user_id."|".$section_id;
					}
				}
				
				if(!$foundEmail)//Search in opportunity
				{
					if($item_email == $dPconfig["opportunity_mail"])
					{
						$foundEmail = true;

						$type = "O";
						
						$sql = "  SELECT id, opportunitycode FROM salespipeline";
						$sql .= " WHERE (INSTR('".$subject."', opportunitycode) > 0 AND LENGTH(opportunitycode) > 0)";
						$sql .= " ORDER BY 1 DESC LIMIT 0,1";
						
						if(db_loadHash( $sql, $rowOpportunity))
							$returnValue = $type."|".$user_id."|".$rowOpportunity["id"]."|".$rowOpportunity["opportunitycode"];
						else
							$returnValue = $type."|".$user_id."||";
					}
				}
				
				if(!$foundEmail)//Search in todo
				{
					if($item_email == $dPconfig["todo_mail"])
					{
						$foundEmail = true;

						$type = "T";

						$returnValue = $type."|".$user_id."|0";
					}
				}			
			}
			else		
				$returnValue = "USUARIO NO VALIDO";

			return $returnValue;
		}
	}
	
	function SearchOpportunity($code, $body)
	{
		if($code == '14149989')
		{
			include_once('common.inc.php');
			
			$body = ereg_replace("'","\"",$body);
			//'
			
			$sql = "  SELECT id, opportunitycode FROM salespipeline";
			$sql .= " WHERE (INSTR('".$body."', opportunitycode) > 0 AND LENGTH(opportunitycode) > 0)";
			$sql .= " ORDER BY 1 DESC LIMIT 0,1";
						
			if(db_loadHash( $sql, $rowOpportunity))
				return $rowOpportunity["id"]."|".$rowOpportunity["opportunitycode"];
			else
				return "";
		}
	}
		
	function SendArticleLink ( $code, $user_id, $project, $opportunity, $section, $title, $body, $abstract, $type, $notify_type)
	{
		if($code == '14149989')
		{
			include_once('common.inc.php');

			$AppUI->user_id = $user_id;
			$AppUI->loadPrefs( $AppUI->user_id );

			require_once($AppUI->getModuleClass('articles'));
			
			if($type == 1)
				if(!stristr($abstract, 'http://') === FALSE) $abstract = substr($abstract, 7);

			$task = 0;
			$title = ereg_replace("'","\"",$title);

			//$body = quoted_printable_decode($body);

			$body = ereg_replace("'","\"",$body);
			$body = ereg_replace("<=","<",$body);
			$body = ereg_replace("< /o:p>","",$body);

			if(!strpos(strtolower($body), "<html") && !strpos(strtolower($body), "<head"))
			{
				$body = ereg_replace("\n","<br />",$body);
			}
			
			$sql = "SELECT category_id FROM files_category WHERE INSTR('".$title."', name_es) > 0 OR INSTR('".$title."', name_en) > 0 LIMIT 0,1";
			
			$categories = db_loadColumn($sql);
		
			if(count($categories) == 1)
				$category = $categories[0];
			else
				$category = -1;			

			$is_protected = 0;
			$is_private = 0;

			$ts = time();
			$date = date("Y-m-d H:i:s",$ts);

			$sql = "SELECT article_id, is_protected, is_private, user_id FROM articles WHERE title = '".$title."' AND project = ".$project." AND articlesection_id = ".$section." AND opportunity = ".$opportunity." limit 0,1;";
			
			if(db_loadHash( $sql, $resultArticle ))
			{
				$id = $resultArticle['article_id'];
				$article_is_protected = $resultArticle['is_protected'];
				$article_is_private = $resultArticle['is_private'];
				$article_user_id = $resultArticle['user_id'];
			}
			
			$insert = false;
			$update = true;
			
			if ($id)
			{
				if($article_is_private == 1 && $article_user_id != $user_id)
					$insert = true;
					
				if($article_is_protected == 1 && $article_user_id != $user_id)
					$update = false;
			}
			else
			{
				$insert = true;
			}
			
			if ($insert)
			{

			   $sql = "INSERT INTO articles (articlesection_id, file_category, date, articles_reads, user_id, title, abstract, body, project, opportunity, task, type,date_modified,is_protected, is_private)
			   VALUES (".$section.", ".$category." , '".$date."', 0, ".$user_id.", '".$title."', '".$abstract."', '".$body."', ".$project.", ".$opportunity.", ".$task.", '".$type."', '".$date."', ".$is_protected.", ".$is_private.")";
			   
			   mysql_query($sql);
			   
			   $id = mysql_insert_id();
			}
			elseif($update)
			{
				$sql = "UPDATE articles SET body = '".$body."', date_modified = '".$date."' WHERE article_id = ".$id;
				
				mysql_query($sql);
				
				//major update

				$sql = "INSERT INTO documents_history ( ";
				$sql .= "history_document_id, ";
				$sql .= "history_document_type, ";
				$sql .= "history_action, ";
				$sql .= "history_user_id, ";
				$sql .= "history_date, ";
				$sql .= "history_comment) VALUES ( ";
				$sql .= $id.", ";
				$sql .= "0, ";
				$sql .= "2, ";
				$sql .= $user_id.", ";
				$sql .= "'".date("Y-m-d H:i:s")."', ";
				$sql .= "NULL)";

				mysql_query($sql);
			}

			if($is_private == 0 && $project > 0 && $notify_type > 0)
			{
				$obj = new CArticle();
				$obj->project = $project;
				$obj->task = $task;
				$obj->article_id = $id;
				$obj->notifyNewKnowledge($notify_type);
			}
			
			return("<message>Operation Successfully</message>");
		}
		else
		{
			return("<error><code>101</code><description>Invalid credentials.</description></error>");
		}
	}	
	
	function SendTodo ( $code, $user_id, $user_email_assigned, $project, $description, $due_date, $notify_type )
	{
		if($code == '14149989')
		{
			include_once('common.inc.php');
			include_once('functions.inc.php');

			$AppUI->user_id = $user_id;
			$AppUI->loadPrefs( $AppUI->user_id );
			
			$isAllowedUserAssigned = true;
			
			if($project > 0)
			{
				$user_assigned = getUserIdByEmail($user_email_assigned);
				
				if($user_assigned)
				{			
					$user_projects = getProjectUsers($project);

					if(!in_array($user_assigned, $user_projects))
						$isAllowedUserAssigned = false;
				}
				else
					$isAllowedUserAssigned = false;
			}
			
			if ($isAllowedUserAssigned)
			{
				$description = "[E-MAIL] ".ereg_replace("'","\"",$description);
			
				$due_date = substr($due_date,6,4)."-".substr($due_date,4,2)."-".substr($due_date,0,2)." 00:00:00";
				
				if($project > 0)
				{
					$sql= "INSERT INTO project_todo (
									project_id,
									description, 
									priority, 
									user_assigned,
									user_owner, 
									date, 
									due_date,
									status,
									task_id) 
								VALUES ( 
									'".$project."', 
									'".$description."', 
									'".$notify_type."', 
									'".$user_assigned."', 
									'".$user_id."', 
									NOW(),
									'".$due_date."',
									'0',
									'0')";
					mysql_query($sql);
				}
				else
				{
					$sql= "INSERT INTO user_todo (
									description, 
									priority, 
									user, 
									date, 
									due_date) 
								VALUES ( 
									'".$description."', 
									'".$notify_type."', 
									'".$user_id."', 
									NOW(),
									'".$due_date."')";
					mysql_query($sql);
				}				

				return("<message>Operation Successfully</message>");
			}
		}
		else
		{
			return("<error><code>101</code><description>Invalid credentials.</description></error>");
		}		
	}	

?>