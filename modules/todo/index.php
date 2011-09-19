<?php
global $AppUI, $xajax;

include ('./modules/todo/functions.php');
include ('./modules/timexp/report_to_items.php');

if (getDenyRead( $m ))
	$AppUI->redirect( "m=public&a=access_denied" );

IF ($_POST['FtAuto']=='') $_POST['FtHE']='on';
IF ($_POST['FtAuto']=='') $_POST['FtHD']='on';



//Ejecuto acciones
if ($_POST['act']=='delete') ExecDelToDo ($_POST, $AppUI);
if ($_POST['act']=='new')	ExecNewToDo ($_POST, $AppUI);
if ($_POST['act']=='doedit') ExecEditTodo ($_POST, $AppUI);


// Ejecuta acciones multiples
if($_POST['act']=='multi_act')
{
	// Acomodo la lista de todos a actualizar
	$todos = explode(',',$_POST['todos_ids']);
	
	foreach ($todos as $IdTodo)
	{
		if ($IdTodo !="")
		{
			$ok = false;
			
			// action = 1: trae usuarios,
			if($_POST['accion']=='1')
			{
				$query_users = "UPDATE project_todo SET user_assigned = '".$_POST['values']."' WHERE id_todo='".$IdTodo."' ";
				db_exec($query_users);
				
				$_POST['mail_msg']='Assignment reassigned';
		        $_POST['tid'] = $IdTodo;
		        $_POST['todo_assign'] = $_POST['values'];
		        
		        $query_det = "SELECT priority,due_date,description  FROM project_todo WHERE id_todo='".$IdTodo."' ";
		        $sql_det = db_exec($query_det);
			    $detalle_todo = mysql_fetch_array($sql_det);
		        
		        $_POST['todo_prio'] = $detalle_todo['priority'];
				$_POST['due_date'] = $detalle_todo['due_date'] ;
				$_POST['todo_desc'] = $detalle_todo['description'];
				
				if($AppUI->user_id!=$_POST['todo_assign']){
					send_mail($_POST, $AppUI);
				}
		        
			}
			
			// 2: trae tareas
			if($_POST['accion']=='2')
			{
				$query_users = "UPDATE project_todo SET task_id =  '".$_POST['values']."' WHERE id_todo='".$IdTodo."' ";
				db_exec($query_users);
				
				$query_det = "SELECT priority,due_date,description, user_assigned FROM project_todo WHERE id_todo='".$IdTodo."' ";
				
				$sql_det = db_exec($query_det);
			    $detalle_todo = mysql_fetch_array($sql_det);
			    
			    $_POST['mail_msg']='Assignment associated to a task';
			    $_POST['tid'] = $IdTodo;
				$_POST['todo_assign'] = $detalle_todo['user_assigned'];
				
				$_POST['todo_prio'] = $detalle_todo['priority'];
				$_POST['due_date'] = $detalle_todo['due_date'] ;
				$_POST['todo_desc'] = $detalle_todo['description'];
				
				send_mail($_POST, $AppUI);
			} 
			
			// 3 Borra
			if($_POST['accion']=='3')
			{
                $sql_project = "SELECT project_id FROM project_todo WHERE id_todo = '".$IdTodo."'";
                $project = db_loadColumn($sql_project);
                
				$_POST['pid'] = $project[0];
				$_POST['tid'] = $IdTodo;
				
				ExecDelToDo ($_POST, $AppUI);
				 
			}
			
			// 4 Cambiar prioridad 
			if($_POST['accion']=='4')
			{
			   $query_priority = "UPDATE project_todo SET priority =  '".$_POST['values']."' WHERE id_todo='".$IdTodo."' ";
			   db_exec($query_priority);
			   
			   $query_det = "SELECT user_assigned ,due_date,description  FROM project_todo WHERE id_todo='".$IdTodo."' ";
		       $sql_det = db_exec($query_det);
			   $detalle_todo = mysql_fetch_array($sql_det);
			   
			   $_POST['mail_msg']='Assignment updated';
			   $_POST['tid'] = $IdTodo;
			   $_POST['todo_assign'] = $detalle_todo['user_assigned'];
		       $_POST['todo_prio'] = $_POST['values'];
			   $_POST['due_date'] = $detalle_todo['due_date'] ;
			   $_POST['todo_desc'] = $detalle_todo['description'];
			   
			   send_mail($_POST, $AppUI);
				
			}
			
			// 5 Cambio de estado
			if($_POST['accion']=='5')
			{
			   $query_priority = "UPDATE project_todo SET status =  '".$_POST['values']."' WHERE id_todo='".$IdTodo."' ";
			   db_exec($query_priority);
			   
			   $query_det = "SELECT priority,user_assigned ,due_date,description  FROM project_todo WHERE id_todo='".$IdTodo."' ";
		       $sql_det = db_exec($query_det);
			   $detalle_todo = mysql_fetch_array($sql_det);
			   
			   if ($_POST['values']=='1'){
					$_POST['mail_msg']='Assignment completed';
			   }
			   else{
					$_POST['mail_msg']='Assignment non completed';
			   }
			   
			   $_POST['tid'] = $IdTodo;
			   $_POST['todo_assign'] = $detalle_todo['user_assigned'];
		       $_POST['todo_prio'] = $detalle_todo['priority'];
			   $_POST['due_date'] = $detalle_todo['due_date'] ;
			   $_POST['todo_desc'] = $detalle_todo['description'];
			   
			   send_mail($_POST, $AppUI);
				
			}
			
			
			
		}
	}
}

// setup the title block
$titleBlock = new CTitleBlock( 'To-Do', 'tasks.gif', $m, "$m.index" );

$titleBlock->show();

todoheader($AppUI, $_POST);
todojsp($AppUI);
?>
<script language='Javascript'>
	<!-- 
	
	function js_action_multiple( accion, field_name)
	{
		var frm = document.form_multiple;  // Formulario que se envia
		var todos = frm.todos_ids.value; // Todos los todos que fueron checkeados
		var project = frm.project_id.value; // Campo con proyectos concatendos 
		var frm2 = document.multiple_actions;  // Formulario que contiene los select
		
		// si la accion es asociar a Tareas
		if(accion.value == '2')
		{
		   var str_project = explode(project, ",", "");
				
		   if (str_project.length > 1){
			   alert("<?=$AppUI->_('Error_projects_tasks')?>");
			   frm2.actions.value = '1';
			   
			   xajax_action_multiple( '1', project , field_name );
			   
		   }else{
		   	   xajax_action_multiple( accion.value, project , field_name );
		   }
				
		}
		
		if(accion.value != '2')
		{
		   xajax_action_multiple( accion.value, project , field_name );
		}
		
	}
	
	
	function save_multiple()
	{
		var frm = document.form_multiple; // Form a enviar
		var frm2 = document.multiple_actions; // Formulario que contiene los select
		var ok = true;
		
		frm.accion.value = frm2.actions.value;
		frm.values.value = frm2.values_m.value
		
		if(frm2.actions.value == '2')
		{
		    var str_project = explode(frm.project_id.value, ",", "");
		    
		    if (str_project.length > 1)
		    {
		    	alert1("<?=$AppUI->_('Error_projects_tasks')?>");
		    	ok = false;
		    }
		}
			
		if (frm.todos_ids.value == "")
		{
			alert1('<?=$AppUI->_('Error_multiple_actions')?>');
			ok = false;
		}
		
		if(frm2.actions.value == '3' && frm.todos_ids.value != "")
		{
			ok = false; // No quiero que salga por aca
			var borrar=confirm("<?=$AppUI->_('Do you want to delete this to-dos?')?>\n");
			
			if (borrar){
				frm.submit();
			}
			
		}
		
		if(ok){
			frm.submit();
		} 
	}
	
	function confirma(fr2,nombre)
	{  
	  var borrar=confirm("<?=$AppUI->_('Do you want to delete this to-dos?')?>\n");
	    
	     if (borrar){
	     	var frm = document.form_multiple;
	     	alert(frm.todos_ids.value);
	    //location.href=fr2;
	     }
	}

	
	function check_todos(todo_id,project_id, val_chk, field_check)
	{	
		var frm = document.form_multiple;  // Formulario que se envia
		
		var current_todos = frm.todos_ids.value;
		var proj_todos = frm.proj_todos.value;
		
		// si el falso, me fijo si esta entre los todos marcados y lo borro
		if(!val_chk.checked)
		{
		  str = new String(frm.todos_ids.value);
		  rExp	= new String(todo_id);
		  rExp2 = ','+rExp;
		  
		  str = new String(str.replace(rExp2, ""));
		  
		  var todos = str;
		  
		  str_proj = new String(frm.proj_todos.value);
		  rExp_p = new String(','+project_id+'_'+todo_id);
          
		  str_proj = new String(str_proj.replace(rExp_p,""));
		  var projtodo = str_proj;
		  
		}
		
		if(val_chk.checked)
		{
		  var todos = current_todos+','+todo_id;
		  var projtodo = proj_todos+','+project_id+'_'+todo_id; 
		}
		
		frm.todos_ids.value = todos;
		frm.proj_todos.value = projtodo;
        
		//alert(frm.proj_todos.value);
		
	    var vec_project = explode(projtodo, ",", "");
	    var str_item = "";
	    var str_proj = "";
      
		// armo el sting de proyecto
	    for(j=0; j < vec_project.length; j++) {
	      
	    	str_item = explode(vec_project[j], "_", "");
	    	
	    	sta = str_proj.search(str_item[0]);
	    	
	    	if(sta < '0')
	    	{
	    		str_proj = str_proj+','+str_item[0];
	    	}
	    	
	    }
	    //alert(str_proj);
	    frm.project_id.value = str_proj;
		
		
		var frm2 = document.multiple_actions; // Formulario que contiene los select
		
		if (frm.todos_ids.value == "" )
		{
			frm2.actions.disabled = true;
			frm2.actions.value = '1';
			frm2.values_m.disabled = true;
		}
		
		if(frm2.actions.disabled && frm.todos_ids.value != "")
		{
			// Si es true es la primera vez que entra, habilito los select de acciones con los valores por defecto: asignar usuario con el proyecto del todo marcado
			
			frm2.actions.disabled = false;
			frm2.values_m.disabled = false;
			frm.project_id.value = project_id;
			
			xajax_action_multiple('1', project_id ,'values_m');	
		}
		
		if(!frm2.actions.disabled)
		{
			// Si ya esta activo, no es la primera vez que entra, entonces verifico la accion
			if(frm2.actions.value == '2')
			{
				var str_project = explode(frm.project_id.value, ",", "");
				
				if (str_project.length > 1){
					alert("<?=$AppUI->_('Error_projects_tasks')?>");
				}
				
			}
			
			if (frm2.actions.value == '1')
			{
				// Si asigna usuarios, traigo los usuarios de los proyectos marcados
				xajax_action_multiple('1', frm.project_id.value, 'values_m');
			}
		}
		
		
	}
	
	function explode(inputstring, separators, includeEmpties) {
		inputstring = new String(inputstring);
		separators = new String(separators);
		
		if(separators == "undefined") {
		separators = " :;";
		}
		
		fixedExplode = new Array(1);
		currentElement = "";
		count = 0;
		
		for(x=0; x < inputstring.length; x++) {
		char = inputstring.charAt(x);
		if(separators.indexOf(char) != -1) {
		if ( ( (includeEmpties <= 0) || (includeEmpties == false)) && (currentElement == "")) { } 
		else {
		fixedExplode[count] = currentElement;
		count++;
		currentElement = ""; } }
		else { currentElement += char; }
		}
		
		if (( ! (includeEmpties <= 0) && (includeEmpties != false)) || (currentElement != "")) {
		fixedExplode[count] = currentElement; } 
		return fixedExplode;
	}
	
	-->
</script>

<!-- Form  de acciones multiples -->
<form action='index.php?m=todo' method='POST' name="form_multiple" id="form_multiple">
    <input type='hidden' name='act' value='multi_act'>
    <input type="hidden" name="accion" value="" >
    <input type="hidden" name="values" value="" >
    <input type="hidden" name="todos_ids" value="">
    <input type="hidden" name="project_id" value="">
    <input type="hidden" name="proj_todos" value="">
    
	<? filter_input_ToDo($_POST); ?>
	
</form>
	
<table class="" id="tbtasks" border="0" cellpadding="1" cellspacing="0" width="100%">
<tbody><tr class="tableHeaderGral">
	<td></td>
	<td align='left' colspan='3' width="15%"><?php echo $AppUI->_('Date'); ?></td>
	<td align='left'><?php echo $AppUI->_('Description'); ?></td>
	<td align='left'><?php echo $AppUI->_('Owner'); ?></td>
	<td align='left'><?php echo $AppUI->_('Assigned'); ?></td>
	<td align='left'><?php echo $AppUI->_('DueDate'); ?></td>
	<td colspan='4'></td>
</tr>
<?php
	$rc=todoquery($AppUI, $_POST);
	$get=$_POST;
	while ($vec=mysql_fetch_array($rc))
	{
		//p.project_id, p.project_name, pt.description, pt.priority, user_name, pt.due_date
		if ($vec['project_id']!=$wproject){
			todoProjHeader($vec, $AppUI);
			$filter['FtMTD']=$_POST['FtMTD'];
			$filter['FtOW']=$_POST['FtOW']; 
			$filter['FtHP']=$_POST['FtHP'];
			$filter['FtHD']=$_POST['FtHD']; 
			$filter['FtHE']=$_POST['FtHE'];
			$filter['FtOw']=$_POST['FtOw'];
			$filter['FtAss']=$_POST['FtAss'];
			newTodo($vec, $AppUI, $filter);
			$wproject=$vec['project_id'];
		}
		IF ($vec['id_todo']!=''){
			if ($get['tid']==$vec['id_todo'] AND $vec['pid']==$get['project_id'] AND $get['act']=='edit') newTodo($vec, $AppUI, $get);
			else TodoRow($vec, $AppUI, $get);
		}
		
		if ($vec['project_id']!=$wproject){
		todoProjFooter($vec, $AppUI);
		}
	}
?>

 <tr class="tableRowLineCell" ><td colspan="11"></td></tr>
 <tr>
    <td colspan="11" align="right">
      
      <table border="0" height="50">
        <form name='multiple_actions' action='index.php?m=todo' method='POST'>
           <tr>
			   <td>
				   <select name="actions"  class="text" onchange="js_action_multiple(this,'values_m')" disabled style="width:300px;">
				      <option value="1"><?=$AppUI->_('assign users')?></option>
				      <option value="2"><?=$AppUI->_('associate tasks')?></option>
				      <option value="3"><?=$AppUI->_('delete todos')?></option>
				      <option value="4"><?=$AppUI->_('change priority')?></option>
				      <option value="5"><?=$AppUI->_('chage state')?></option>
				   </select>
			   </td>
			   <td>
				   <script type="text/javascript">
					    xajax_action_multiple('1', '','values_m');		
				   </script>
				   
			 
				   <select name="values_m" id="values_m" class="text" style="width:300px;" disabled >
				   </select>
			   </td>
			   <td>
				   <input type="button" class="button" value="<?=$AppUI->_('ok')?>" onclick="save_multiple()">
			   </td>
		   </tr>
        </form>
      
      </table>
      
	</td>		      
 </tr>		      

</table>
<?php
IF ($get['GTTD']!='') {
	//echo "<br><br>entre!!!<br>";
	echo "<script language='Javascript'>
	<!-- 
		GTTD('".$get['GTTD']."');
	-->
	</script>";
}

?>
