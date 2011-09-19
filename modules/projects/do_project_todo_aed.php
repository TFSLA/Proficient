<?php /* PROJECTS $Id: do_project_todo_aed.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */

include ('./modules/todo/functions.php');

$msg = '';

// ingreso los datos en la base de datos

$project = $_REQUEST[project_id];
$descripcion = $_POST[descripcion];
$user_assign = $_POST[user_assign];
$priority = $_POST[priority];
$creador = $_POST[creador];
$fecha = substr($_POST[log_due_date],0,4)."-".substr($_POST[log_due_date],4,2)."-".substr($_POST[log_due_date],6,2)." 00:00:00";
$todo_id = $_POST[todo_id];
$task_id = $_POST[task_id];

if ($todo_id=="0")
{

	if($task_id > 0 )
	{
		$query = "INSERT INTO project_todo (project_id, description, priority, user_assigned, user_owner, date, due_date,task_id) VALUES ('$project', '".checkpost($descripcion)."', '$priority', '$user_assign','$creador', NOW(),'$fecha','$task_id')";
	}else{
		$query = "INSERT INTO project_todo (project_id, description, priority, user_assigned, user_owner, date, due_date) VALUES ('$project', '".checkpost($descripcion)."', '$priority', '$user_assign','$creador', NOW(),'$fecha')";
	}

$sql = mysql_query($query);

$query_id = mysql_insert_id();

 if ($user_assign!="")
	   {
       $MSG['tid'] = $query_id;
	   $MSG['todo_assign'] = $user_assign;
	   $MSG['todo_prio'] = $priority;
	   $MSG['due_date'] = $_POST[log_due_date];
	   $MSG['todo_desc'] = $descripcion;
	   $MSG['mail_msg'] = $AppUI->_( 'New ToDo Assigned' );
		
	   IF ($AppUI->user_id!=$MSG['todo_assign']) send_mail($MSG, $AppUI);
	   }

if ($sql){
	$AppUI->setMsg( $AppUI->_('ToDo Inserted'), UI_MSG_OK);
}
else{
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
}

}
else
{

// Me fijo si cambió el usuario asignado a la tarea
$query_pre = "select user_assigned FROM project_todo WHERE id_todo=$todo_id";
$sql_pre = mysql_query($query_pre);
$temp_vec = mysql_fetch_array($sql_pre);

if($temp_vec[user_asigned]!= $user_assign)
	{
	$cambia_asig ="1";
	}
	else
	{
	$cambia_asig ="0";
	}

$query = "UPDATE project_todo SET 
description = '".checkpost($descripcion)."',
user_assigned =  '$user_assign',
priority = '$priority',
user_owner = '$creador',
date =  NOW(),
due_date = '$fecha'
WHERE id_todo='$todo_id'";

$sql = mysql_query($query);

if ($sql){
	$AppUI->setMsg( 'To-do updated', UI_MSG_OK);
}
else{
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
}
     
       if ($user_assign!="")
	   {
       $MSG['tid'] = $todo_id;
	   $MSG['todo_assign'] = $user_assign;
	   $MSG['todo_prio'] = $priority;
	   $MSG['due_date'] = $_POST[log_due_date];
	   $MSG['todo_desc'] = $descripcion;
	   $MSG['mail_msg'] = $AppUI->_( 'The ToDo was updated' );

	   IF ($AppUI->user_id!=$MSG['todo_assign']) send_mail($MSG, $AppUI);
	   }

       

	   if ($cambia_asig == "1"){

		 $MSG['tid'] = $todo_id;
	     $MSG['todo_assign'] = $temp_vec[user_asigned];
	     $MSG['todo_prio'] = $priority;
	     $MSG['due_date'] = $_POST[log_due_date];
	     $MSG['todo_desc'] = $descripcion;
	     $MSG['mail_msg'] = $AppUI->_( 'The ToDo was reasigned' );

         IF ($AppUI->user_id!=$MSG['todo_assign']) send_mail($MSG, $AppUI);
	   }
	   
}

// Si edita tengo que borrarle el action y demas del get
if ($m == 'projects')
{
  $redirect = "m=".$m."&a=".$a."&project_id=".$project;
}

if ($m == 'tasks')
{
  $redirect = "m=".$m."&a=".$a."&task_id=".$task_id;
}


if ($redirect != "")
{
  $AppUI->redirect($redirect);
}else{
  $AppUI->redirect();
}

//$AppUI->redirect("m=projects&a=view&tab=".$_POST[tab]."&project_id=".$project);

?>
