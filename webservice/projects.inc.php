<?php
	function GetProjects ( $username, $password)
	{
		include_once('common.inc.php');

		if($AppUI->login($username,$password, true, false))
		{
			include("../includes/permissions.php");
			include_once("functions.inc.php");
			
			$prjs = getAllowedRecords($AppUI->user_id, "project_id");
			
			$sql = "SELECT DISTINCT project_id, project_name FROM projects WHERE project_id IN (". implode( ',', array_keys($prjs) ) .")";
			
			$result = mysql_query($sql);

			$xml = "<projects>";

			while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
			{
				$xml .= "<project>";
				$xml .= "<id>".$row["project_id"]."</id>";
				$xml .= "<name>".$row["project_name"]."</name>";
				$xml .= "</project>";
			}
			
			$xml .= "</projects>";
			
			return ($xml);
		}
		else
		{
			return("<error><code>101</code><description>Invalid credentials.</description></error>");
		}
	}
	
	
	function GetTasks ( $username, $password)
	{
		include_once('common.inc.php');

		if($AppUI->login($username,$password, true, false))
		{
			include("../includes/permissions.php");
			include_once("functions.inc.php");
			require_once( $AppUI->getModuleClass( 'tasks' ) );
			require_once( $AppUI->getSystemClass( 'projects' ) );
			
			$prjs = getAllowedRecords($AppUI->user_id, "project_id");
			
			$Cproject = new CProjects();
			$Cproject->loadTasks($prjs[$k]);
			$tasks = $Cproject->Tasks();
			
			for($i=0;$i<count($tasks);$i++)
				if(in_array($tasks[$i]['project_id'], $prjs))
					$tasksProject[$tasks[$i]['task_id']] = $tasks[$i]['task_id'];
			
			$xml = "<tasks>";
			
			if ($tasksProject)
			{
				$sql = "SELECT DISTINCT task_id, task_name, task_project FROM tasks WHERE task_id IN (". implode( ',', array_keys($tasksProject) ) .")";

				$result = mysql_query($sql);

				while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
				{
					$xml .= "<task projectid=\"".$row["task_project"]."\">";
					$xml .= "<id>".$row["task_id"]."</id>";
					$xml .= "<name>".$row["task_name"]."</name>";
					$xml .= "</task>";
				}
			
			}
			
			$xml .= "</tasks>";
			
			return ($xml);
		}
		else
		{
			return("<error><code>101</code><description>Invalid credentials.</description></error>");
		}
	}
?>