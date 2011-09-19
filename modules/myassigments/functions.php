<?php /* My Assigment $Id: functions.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */


/**
 * Con el id de projecto, trae las incidencias en las que el usuario esta asignado
 *
 * @param integer $project_id = id del projecto del que se mostraran las incidencias
 * @param integer $user_id = id del usuario asignado a las incidencias a mostrar
 * @param bolean  $vencidos = indica si va mostrar vencidos o no vencidos, (1= vencidos , 0 = no vencidos)
 * @param date    $start_date = inicio del rango de fechas
 * @param date    $end_date = fin del rango de fechas
 * 
 */
function show_bugs($project_id, $user_id, $vencidos, $start_date, $end_date)
{
	global $AppUI,$_COOKIE;
	$ts = time();
	$today = date("Y-m-d H:m:s",$ts);
	
	if ($start_date != "" && $end_date != "")
	{
		$query_dates = "AND ( last_updated >= '".$start_date."' AND last_updated <='".$end_date."') "; 
	}
	
	$font = "#000000";
	
	if ($vencidos)
	{
		
		$query = "
	        SELECT id, DATE_FORMAT(last_updated,'%d-%m-%Y') as last_updated, summary, DATE_FORMAT(date_deadline,'%d-%m-%Y') as date_deadline, reporter_id, category, severity, status, priority, DATE_FORMAT(last_updated,'%Y%m%d') as date_order
	        FROM btpsa_bug_table 
	        WHERE handler_id= '".$user_id."' 
	        AND project_id = '".$project_id."' 
	        AND ( date_deadline < '".$today."' AND date_deadline <>'0000-00-00 00:00:00') 
            AND status <>'80' AND status <>'90'
            $query_dates
            order by priority desc
	       ";
		
		$font = "#FF0000";
	}
	else 
	{
		$query = "
	        SELECT id, DATE_FORMAT(last_updated,'%d-%m-%Y') as last_updated, summary, DATE_FORMAT(date_deadline,'%d-%m-%Y') as date_deadline, reporter_id, category, severity, status, priority, DATE_FORMAT(last_updated,'%Y%m%d') as date_order
	        FROM btpsa_bug_table 
	        WHERE handler_id= '".$user_id."' 
	        AND ( date_deadline >= '".$today."' OR date_deadline ='0000-00-00 00:00:00' OR date_deadline is null) 
	        AND project_id = '".$project_id."' 
            AND status <>'80' AND status <>'90'
            $query_dates
            order by priority desc
	       ";
	
	}
	
	
	$sql = db_loadList($query);
    $cant = count($sql) - 1; 
    
    for ($i=0; $i<= $cant; $i++)
    {
        $id_bug = $sql[$i]['id'];
        $id = $sql[$i]['id'];
        
        $cant_char = strlen($id_bug); 
        $ceros = 7 - $cant_char;
        
        $bugid = str_repeat("0",$ceros).$id_bug;
        
        $date_deadline = $sql[$i]['date_deadline'];
        
        if ($date_deadline == "00-00-0000" || $date_deadline =="")
        {
        	$date_deadline = "N/A";
        }
        
        switch ($sql[$i]['severity'])
        {
        	case "10":
        		$severity = $AppUI->_('FEATURE');
        	break;
        	case "20":
        		$severity = $AppUI->_('TRIVIAL');
        	break;
        	case "30":
        		$severity = $AppUI->_('TEXT');
        	break;
        	case "40":
        		$severity = $AppUI->_('TWEAK');
        	break;
			case "50":
        		$severity = $AppUI->_('MINOR');
        	break;
        	case "60":
        		$severity = $AppUI->_('MAJOR');
        	break;
        	case "70":
        		$severity = $AppUI->_('CRASH');
        	break;
        	case "80":
        		$severity = $AppUI->_('BLOCK');
        	break;
        	
        }
        
        switch ($sql[$i]['priority'])
        {
        	case "10":
        	   $priority = $AppUI->_('None');
            break;
            case "20":
        	   $priority = $AppUI->_('Low');
            break;
            case "30":
        	   $priority = $AppUI->_('Normal');
            break;
            case "40":
        	   $priority = $AppUI->_('High');
            break;
            case "50":
        	   $priority = $AppUI->_('Urgent');
            break;
            case "60":
        	   $priority = $AppUI->_('immediate');
            break;
        }
        
        switch ($sql[$i]['status']) 
        {
        	case "10":
        		$status = $AppUI->_('New');
        	break;
        	case "20":
        		$status = $AppUI->_('Feedback');
        	break;
        	case "25":
        		$status = $AppUI->_('Feedback submitted');
        	break;
        	case "30":
        		$status = $AppUI->_('Acknowledged');
        	break;
        	case "40":
        		$status = $AppUI->_('Confirmed');
        	break;
        	case "50":
        		$status = $AppUI->_('Assigned');
        	break;
        	case "80":
        		$status = $AppUI->_('Resolved');
        	break;
        	case "90":
        		$status = $AppUI->_('Closed');
        	break;
        	
        }
        
        $detalle_bug = "<table><tr><td colspan=2><b>[$bugid] - ".$sql[$i]['summary']."</b></td></tr><tr><td>".$AppUI->_('Updated').": </td><td>".$sql[$i]['last_updated']."</td><tr><td>".$AppUI->_('Severity').":</td><td>".$severity."</td></tr><tr><td>".$AppUI->_('Priority').":</td><td>".$priority."</td></tr><tr><td>".$AppUI->_('Status').":</td><td>".$status."</td></tr></table>";
        
        
        $bug_order['b'.$id]['order'] = $sql[$i]['priority'];
        $bug_order['b'.$id]['ref'] = "<a href='./index.php?m=webtracking&a=bug_view_page&bug_id=".$bugid."' >";
        $bug_order['b'.$id]['date'] = $sql[$i]['last_updated'];
        $bug_order['b'.$id]['description'] = $sql[$i]['summary'];
        $bug_order['b'.$id]['type'] = $AppUI->_('Bug');
        $bug_order['b'.$id]['due_date'] = $date_deadline;
        $bug_order['b'.$id]['font'] = $font;
        $bug_order['b'.$id]['project'] = $project_id;
        $bug_order['b'.$id]['detail'] = $detalle_bug;
        $bug_order['b'.$id]['assignment_id'] = $id;
        $bug_order['b'.$id]['assignment_type'] = 'b';
    }
    
    return $bug_order;
}


/**
 * Con el id de projecto, trae las tareas en las que el usuario esta asignado
 *
 * @param integer $project_id = id del projecto del que se mostraran las tareas
 * @param integer $user_id = id del usuario asignado a las tareas a mostrar
 * @param bolean  $vencidos = indica si va mostrar vencidos o no vencidos, (1= vencidos , 0 = no vencidos)
 * @param date    $start_date = inicio del rango de fechas
 * @param date    $end_date = fin del rango de fechas
 * 
 */
function show_task($project_id, $user_id, $vencidos, $start_date, $end_date)
{
	global $AppUI;
	
	$ts = time();
	$today = date("Y-m-d H:m:s",$ts);
	
	if ($start_date != "" && $end_date != "")
	{
		$query_dates = "AND ( task_start_date >= '".$start_date."' AND task_start_date <='".$end_date."') "; 
	}
	
	$font = "#000000";
		
	if ($vencidos)
	{
		
		$query = "
			SELECT distinct(tasks.task_id), task_name, task_description, DATE_FORMAT(task_start_date,'%d-%m-%Y') as task_start_date, task_priority, task_owner, DATE_FORMAT(task_end_date ,'%d-%m-%Y') as task_end_date,users.user_first_name,users.user_last_name, DATE_FORMAT(task_start_date,'%Y%m%d') as date_order
	        FROM tasks
	        LEFT JOIN users ON users.user_id = tasks.task_owner
	        ,user_tasks
                WHERE task_project='".$project_id."'
	        AND task_end_date < '".$today."'
	        AND task_complete = '0'
	        AND (tasks.task_id = user_tasks.task_id AND user_tasks.user_id='".$user_id."')
	        $query_dates
	        order by task_priority asc
		  ";
		
	     $font = "#FF0000";
	}
	else 
	{
		$query = "
			SELECT distinct(tasks.task_id), task_name, task_description, DATE_FORMAT(task_start_date,'%d-%m-%Y') as task_start_date, task_priority, task_owner, DATE_FORMAT(task_end_date ,'%d-%m-%Y') as task_end_date,users.user_first_name,users.user_last_name, DATE_FORMAT(task_start_date,'%Y%m%d') as date_order
	        FROM tasks
	        LEFT JOIN users ON users.user_id = tasks.task_owner
	        ,user_tasks
                WHERE task_project='".$project_id."'
	        AND task_end_date >='".$today."'
	        AND task_complete = '0'
	        AND (tasks.task_id = user_tasks.task_id AND user_tasks.user_id='".$user_id."')
	        $query_dates
	        order by task_priority asc
		  ";
	
	}
	
	
	$sql = db_loadList($query);
    $cant = count($sql) - 1; 
    
    for ($i=0; $i<= $cant; $i++)
    { 
    	
    	
    	
    	$detalle_task = "<table><tr><td colspan=2><b>".$sql[$i]['task_name']."</b></td></tr><tr><td>".$AppUI->_('Start date').": </td><td>".$sql[$i]['task_start_date']."</td><tr><td>".$AppUI->_('End date').":</td><td>".$sql[$i]['task_end_date']."</td></tr><tr><td>".$AppUI->_('Priority').":</td><td>".$sql[$i]['task_priority']."</td></tr><tr><td>".$AppUI->_('Task Creator').":</td><td>".$sql[$i]['user_first_name']." ".$sql[$i]['user_last_name']."</td></tr></table>";
    	
    	$id = $sql[$i]['task_id'];
    	
    	if ($sql[$i]['task_priority'] > 100)
    	{
    		$priority_order = $sql[$i]['task_priority'] / 10;
    	}else{
    		$priority_order = $sql[$i]['task_priority'];
    	}
    	
    	$task_order['ta'.$id]['order'] = $priority_order;
    	$task_order['ta'.$id]['ref'] = "<a href='./index.php?m=tasks&a=view&task_id=".$id."' >";
        $task_order['ta'.$id]['date'] = $sql[$i]['task_start_date'];
        $task_order['ta'.$id]['description'] = $sql[$i]['task_name'];
        $task_order['ta'.$id]['type'] = $AppUI->_('Task');
        $task_order['ta'.$id]['due_date'] = $sql[$i]['task_end_date'];
        $task_order['ta'.$id]['font'] = $font;
        $task_order['ta'.$id]['project'] = $project_id;
        $task_order['ta'.$id]['detail'] = $detalle_task;
        $task_order['ta'.$id]['assignment_id'] = $id;
        $task_order['ta'.$id]['assignment_type'] = 'ta';
    }
    
    return $task_order;
}


/**
 * Con el id de projecto, trae los todos en los que el usuario esta asignado
 *
 * @param integer $project_id = id del projecto del que se mostraran los todos
 * @param integer $user_id = id del usuario asignado a los todos a mostrar
 * @param bolean  $vencidos = indica si va mostrar vencidos o no vencidos, (1= vencidos , 0 = no vencidos)
 * @param date    $start_date = inicio del rango de fechas
 * @param date    $end_date = fin del rango de fechas
 * 
 */
function show_todos($project_id, $user_id, $vencidos, $start_date, $end_date)
{
	global $AppUI;
	
	$ts = time();
	$today = date("Y-m-d H:m:s",$ts);
	
	if ($start_date != "" && $end_date != "")
	{
		$query_dates = "AND ( date >= '".$start_date."' AND date <='".$end_date."') "; 
	}
	
	$font = "#000000";
		
	if ($vencidos)
	{
		
		$query = "
		    SELECT id_todo, description, project_todo.user_owner, DATE_FORMAT(date,'%d-%m-%Y') as date, DATE_FORMAT(due_date,'%d-%m-%Y') as due_date, priority, users.user_last_name, users.user_first_name, DATE_FORMAT(date,'%Y%m%d') as date_order
			FROM project_todo
			LEFT JOIN users ON users.user_id = project_todo.user_owner
			WHERE 
			project_id = '".$project_id."'
			AND user_assigned = '".$user_id."'
			AND ( due_date <'".$today."' AND due_date<>'0000-00-00 00:00:00')
			$query_dates
			AND status='0'
			order by priority asc
		";
		
		$font = "#FF0000";
		
    }else
    {
    	$query = "
		    SELECT id_todo, description, project_todo.user_owner, DATE_FORMAT(date,'%d-%m-%Y') as date, DATE_FORMAT(due_date,'%d-%m-%Y') as due_date, priority, users.user_last_name, users.user_first_name, DATE_FORMAT(date,'%Y%m%d') as date_order
			FROM project_todo
			LEFT JOIN users ON users.user_id = project_todo.user_owner
			WHERE 
			project_id = '".$project_id."'
			AND user_assigned = '".$user_id."'
			AND (due_date >='".$today."' OR due_date='0000-00-00 00:00:00')
			$query_dates
			AND status='0'
			order by priority asc
		";
    }
    
    
    $sql = db_loadList($query);
    $cant = count($sql) - 1; 
    
    for ($i=0; $i<= $cant; $i++)
    {   
    	
    	$due_date = $sql[$i]['due_date'];
    	
    	if ($due_date == "00-00-0000")
    	{
    		$due_date = "N/A";
    	}
    	
    	switch ($sql[$i]['priority']) 
    	{
    		case "1":
    			$priority = $AppUI->_('High');
    			$priority_order = 60;
    		break;
    		case "2":
    			$priority = $AppUI->_('Normal');
    			$priority_order = 30;
    		break;
    		case "3":
    			$priority = $AppUI->_('Low');
    			$priority_order = 20;
    		break;
    	}
    	$detalle_todo = "<table><tr><td colspan=2><b>".$sql[$i]['description']."</b></td></tr><tr><td>".$AppUI->_('Owner').": </td><td>".$sql[$i]['user_first_name']." ".$sql[$i]['user_last_name']."</td><tr><td>".$AppUI->_('Priority').":</td><td>".$priority."</td></tr></table>";
    	
    	$id = $sql[$i]['id_todo'];
    	
    	
    	$todo_order['t'.$id]['order'] = $priority_order;
    	$todo_order['t'.$id]['ref'] = " <a href='javascript: //' onclick='javascript: submItF(\"".$user_id."\")' >";
        $todo_order['t'.$id]['date'] = $sql[$i]['date'];
        $todo_order['t'.$id]['description'] = $sql[$i]['description'];
        $todo_order['t'.$id]['type'] = $AppUI->_('To-do');
        $todo_order['t'.$id]['due_date'] = $due_date;
        $todo_order['t'.$id]['font'] = $font;
        $todo_order['t'.$id]['project'] = $project_id;
        $todo_order['t'.$id]['detail'] = $detalle_todo;
        $todo_order['t'.$id]['assignment_id'] = $id;
        $todo_order['t'.$id]['assignment_type'] = 't';
    	
    }
    
    return $todo_order;

}

function getMyAssigmentActive($user_id)
{
	$sql = "SELECT * FROM myassigments_active WHERE user_id = ".$user_id;
	
	return (db_loadList($sql));
}

?>
