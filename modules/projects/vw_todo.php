<?php
global $project_id,$xajax,$task_id;

include ('./modules/todo/functions.php');
require_once("./modules/timexp/report_to_items.php");

$todo_id = dPgetParam( $_POST, "todo_id", 0 );
$m = dPgetParam( $_GET, "m");
$a = dPgetParam( $_GET, "a");

if($task_id >0)
{
	$path_item = "task_id=".$task_id;
}else{
	$path_item = "project_id=".$project_id;
}


if ($m =="projects"){
  $tab= $AppUI->state[ProjVwTab];
}

if($m=="tasks"){
  $tab= $AppUI->state[TaskLogVwTab];
}


$df = $AppUI->getPref('SHDATEFORMAT');

// Carga los datos del proyecto
$row = new CProject();

if (!$row->load( $project_id, false ) && $project_id > 0) {
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else if (count( $companies ) < 2 && $project_id == 0) {
	$AppUI->setMsg( "noCompanies", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

if ($_GET["accion"] =="delete")
{
  $_GET['pid'] = $project_id;
  $_GET['tid'] = $_GET['todo_id'];
  
   ExecDelToDo($_GET, $AppUI);
   $AppUI->redirect('m='.$m.'&a='.$a.'&'.$path_item);
}

if($_POST["st"]!="")
{
	$todo_id = $_POST["st"];

	$query = "select status,user_assigned,priority,due_date,description, project_id FROM project_todo WHERE id_todo=$todo_id";
	$sql = mysql_query($query);
	$status = mysql_fetch_array($sql);
 
	if($status["status"]==1)
	{
	  $nuevo="0";
	  $MSG['mail_msg'] = $AppUI->_( 'Assignment non completed' );
	}

	if($status["status"]==0)
	{
	  $nuevo="1";
	  $MSG['mail_msg'] = $AppUI->_( 'Assignment completed' );
	}
    
	if ($status["user_assigned"]!="")
	 {
	   $MSG['tid'] = $todo_id;
	   $MSG['todo_assign'] = $status["user_assigned"];
	   $MSG['todo_prio'] = $status["priority"];
	   if($status["due_date"] == '00-00-0000'){
	   	$status['due_date'] = '';
	   }
	   $MSG['due_date'] = $status["due_date"];
	   $MSG['todo_desc'] = $status["description"];
	   
	   if( $MSG['todo_assign'] != $AppUI->user_id ){
	   	send_mail($MSG, $AppUI);
	   }
	 }

    $query_u = "UPDATE project_todo SET 
				status = '$nuevo'
				WHERE id_todo='$todo_id'";
	$sql_u = mysql_query($query_u)or die(mysql_error());
	
}

if ($_GET["accion"] =="edit")
{ 
    $selecion = $_GET["todo_id"];

    $query = "select * FROM project_todo WHERE id_todo=$selecion";
    $sql = mysql_query($query);
	$edito = mysql_fetch_array($sql);

	$priority = $edito["priority"];
	$descripcion = $edito["description"];
	$user_assign = $edito["user_assigned"];
	$dia = substr($edito["due_date"],8,2);
	$mes = substr($edito["due_date"],5,2);
	$anio = substr($edito["due_date"],0,4);
	$todo_id = $edito["id_todo"];

	   if ($anio!="0000")
		{        
		$due_date["year"] = $anio;
		$due_date["month"] = $mes;
        $due_date["day"] = $dia; 
		}
	
	unset($_GET["accion"]);
}

$prole = new CProjectRoles();
$prjUsers = $prole->getAssignedUsers(2 ,$project_id);
$usersAssignedToTasks = $row->getUsersAssignedToTasks($project_id);

//obtengo lista de usuarios asignados como usuarios del proyecto
$project_users_list = $prole->getList(2 ,$project_id);


//Reordeno la lista de usuarios asignados como usuarios del proyecto
$project_users = array();
for($i=0; $i < count($project_users_list); $i++){
	$project_users[$project_users_list[$i]["user_id"]] = $project_users_list[$i];
}
unset($project_users_list);


$due_date = intval( $edito[due_date] ) ? new CDate( $edito[due_date] ) : null;

$comp = $_GET[comp];
$order = $_GET[order];

$sql_owner = mysql_query("select user_last_name, user_first_name from users where user_id='$row->project_owner'");
$owner_data = mysql_fetch_array($sql_owner);
$owner_name =$owner_data["user_first_name"]." ".$owner_data["user_last_name"];

$admins = $row->getOwners();

$admins = arrayMerge( array( $row->project_owner=>$owner_name ), $admins );

foreach($admins as $akey=>$admin){
$prjUsers = arrayMerge( array( $akey=>$admin ), $prjUsers );
}

if ($_POST['accion']=="multi_act")
{
	// Acomodo la lista de todos a actualizar
	$todos = explode(',',$_POST['todos_ids']);
	
	foreach ($todos as $IdTodo)
	{
		if ($IdTodo !="")
		{
			$ok = false;
			
			// actions = 1: trae usuarios, 2: trae tareas
			if($_POST['actions']=='1')
			{
				$query_users = "UPDATE project_todo SET user_assigned = '".$_POST['values_m']."' WHERE id_todo='".$IdTodo."'  AND project_id = '".$project_id."' ";
				db_exec($query_users);
				
				$_POST['mail_msg']='Assignment reassigned';
		        $_POST['tid'] = $IdTodo;
		        $_POST['todo_assign'] = $_POST['values_m'];
		        
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
			
			if($_POST['actions']=='2')
			{
				$query_users = "UPDATE project_todo SET task_id =  '".$_POST['values_m']."' WHERE id_todo='".$IdTodo."'  AND project_id = '".$project_id."' ";
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
			if($_POST['actions']=='3')
			{
                $sql_project = "SELECT project_id FROM project_todo WHERE id_todo = '".$IdTodo."'";
                $project = db_loadColumn($sql_project);
                
				$_POST['pid'] = $project[0];
				$_POST['tid'] = $IdTodo;
				
				ExecDelToDo ($_POST, $AppUI);
				 
			}
			
			// 4 Cambiar prioridad 
			if($_POST['actions']=='4')
			{
			   $query_priority = "UPDATE project_todo SET priority =  '".$_POST['values_m']."' WHERE id_todo='".$IdTodo."' ";
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
			if($_POST['actions']=='5')
			{
			   $query_priority = "UPDATE project_todo SET status =  '".$_POST['values_m']."' WHERE id_todo='".$IdTodo."' ";
			   db_exec($query_priority);
			   
			   $query_det = "SELECT priority,user_assigned ,due_date,description  FROM project_todo WHERE id_todo='".$IdTodo."' ";
		       $sql_det = db_exec($query_det);
			   $detalle_todo = mysql_fetch_array($sql_det);
			   
			   if ($_POST['values_m']=='1'){
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

?>
<script language="javascript">
<?="<!--";?>


function validateForm(){
	var f = document.editFrm;
	var msg = "";
	var ret = false;
 
	if (( f.descripcion.value == "")) {
		msg += "<?=$AppUI->_('descripvalid');?>";
	}

	if (( f.user_assign.value == "0")) {
		msg += "<?=$AppUI->_('assignvalid_todo');?>";
	}

	if (msg==""){
		ret= true;	
	}else{
		alert1 (msg);
	}
	
	return ret;
	
}

var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
    document.editFrm.log_due_date.value = idate; 
}

function actualiza_status(obj){
document.forms.stFrm.st.value = obj.value;
document.forms.stFrm.submit(); 
}

function confirma(fr2,nombre)
{  
  var borrar=confirm('<?=$AppUI->_('Do you want to delete this todo?')?>\n\n'+ nombre);
    if (borrar)
    location.href=fr2;
}

function check_todos(todo_id, val_chk)
{	
	var frm = document.form_multiple;
    
	var current_todos = frm.todos_ids.value;
	
	// si el falso, me fijo si esta entre los todos marcados y lo borro
	if(!val_chk.checked)
	{
		str = new String(frm.todos_ids.value);
		rExp	= new String(todo_id);
		rExp2 = ','+rExp;
		  
		str = new String(str.replace(rExp2, ""));
		  
		var todos = str;
		  
	}
		
	if(val_chk.checked)
	{
		var todos = current_todos+','+todo_id;
	}
	
    frm.todos_ids.value = todos;
    
    var frm2 = document.form_multiple; // Formulario que contiene los select
    
    if(frm2.actions.disabled && frm.todos_ids.value != "")
    {
	  frm2.actions.disabled = false;
	  frm2.values_m.disabled = false;
	  
	  xajax_action_multiple('1', '<?php echo $project_id; ?>' ,'values_m');	
	}
	
	if (frm.todos_ids.value == "" )
	{
	   frm2.actions.disabled = true;
	   frm2.actions.value = '1';
	   frm2.values_m.disabled = true;
	}
		
}


function js_action_multiple(accion, project , field_name)
{
	xajax_action_multiple(accion.value, project,field_name );
}
	
function save_multiple()
{
	var frm = document.form_multiple;
		
	if ( frm.todos_ids.value == "")
	{
		alert1('<?=$AppUI->_('Error_multiple_actions')?>');
	}else{
		frm.submit();
	}
}
	
//-->
</script>


<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td valign="top">

			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableForm_bg">
			  <form name="compFrm" method="get">
					<input type="hidden" name="m" value="<?=$m?>" />
					<input type="hidden" name="a" value="<?=$a?>" />
					<input type="hidden" name="project_id" value="<?=$project_id;?>" />
					<input type="hidden" name="order" value="<?=$order;?>" />
					<input type="hidden" name="tab" value="<?=$tab;?>" />
					<?
					  if($m=="tasks")
					  {
					  	echo "<input type=\"hidden\" name=\"task_id\" value=\"$task_id\" >";
					  }
					?>
               <tr class="tableHeaderGral">
				<th width="20"><font COLOR="#FF0000" onmouseover="tooltipLink('<? echo $AppUI->_('Hide Complete'); ?>');" onMouseOut ="tooltipClose();" >
				<? 
				   if ($_GET[comp]=="1")
				   {
					   $ckec ="checked";
				   }
				?>
				<input type="checkbox" name="comp" value="1" <? echo $ckec; ?> onclick="submit()"/>
				
				</font>
				</th>
               </form>
				<form name="editFrm" method="post" onSubmit="return validateForm();">
			        <input type="hidden" name="dosql" value="do_project_todo_aed" />
					<input type="hidden" name="completos" value="0" />
					<input type="hidden" name="project_id" value="<?=$project_id;?>" />
					<input type="hidden" name="todo_id" value="<?=$todo_id;?>" />
                    <input type="hidden" name="creador" value="<?=$AppUI->user_id;?>" />
					<input type="hidden" name="tab" value="<?=$tab;?>" />
					<?
					  if($m=="tasks")
					  {
					  	echo "<input type=\"hidden\" name=\"task_id\" value=\"$task_id\" >";
					  }
					?>
					
				<th width="60" align="center"><a href="?m=<?=$m?>&a=<?=$a?>&<?=$path_item;?>&tab=<?=$tab?>&order=prio&comp=<?=$comp;?>"><font color="#FFFFFF"><IMG SRC="./images/high_black.png" WIDTH="8" HEIGHT="14" BORDER="0" ALT="<?php echo $AppUI->_( 'Priority' );?>"></font></a></th>
				<th width="100"><a href="?m=<?=$m?>&a=<?=$a?>&<?=$path_item;?>&tab=<?=$tab?>&order=fech&comp=<?=$comp;?>"><font color="#FFFFFF"><?php echo $AppUI->_( 'Date' );?></font></a></th>
				<th ><a href="?m=<?=$m?>&a=<?=$a?>&<?=$path_item;?>&tab=<?=$tab?>&order=descrip&comp=<?=$comp;?>"><font color="#FFFFFF"><?php echo $AppUI->_( 'Description' );?></font></a></th>
				<th width="100"><a href="?m=<?=$m?>&a=<?=$a?>&<?=$path_item;?>&tab=<?=$tab?>&order=owner&comp=<?=$comp;?>"><font color="#FFFFFF"><?php echo $AppUI->_( 'Owner' );?></font></a></th>
				<th width="100"><a href="?m=<?=$m?>&a=<?=$a?>&<?=$path_item;?>&tab=<?=$tab?>&order=assign&comp=<?=$comp;?>"><font color="#FFFFFF"><?php echo $AppUI->_( 'Assigned' );?></font></a></th>
				<th width="100"><a href="?m=<?=$m?>&a=<?=$a?>&<?=$path_item;?>&tab=<?=$tab?>&order=due_date&comp=<?=$comp;?>"><font color="#FFFFFF"><?php echo $AppUI->_( 'Due date' );?></font></a></th>
				<th width="60">&nbsp;</th>
			  </tr>
			  <tr>
			            <td width="20">&nbsp;</td>
						<td nowrap>
							<select name="priority" class="text" size="1">
							    <?
								   if(($priority=="")||($priority=="2"))
								   {
									 $chk2 = "selected";
								   }
								   if($priority=="1")
								   {
									 $chk1 = "selected";
								   }
								   if($priority=="3")
								   {
									 $chk3 = "selected";
								   }
								?>
								<option value="2" <? echo $chk2;?> ><?=$AppUI->_('Normal');?></option>
								<option value="1" <? echo $chk1;?> ><?=$AppUI->_('High');?></option>
								<option value="3" <? echo $chk3;?> ><?=$AppUI->_('Low');?></option>
							</select>
						</td>
						<td >&nbsp;</td>
						
						<td colspan="2">
							<input type="text" name="descripcion" size="50" class="text" value="<? echo $descripcion;?>"/>
						</td>
						<td nowrap>
						    <? 
                               //$prjUsers = arrayMerge( array( '0'=>$AppUI->_('') ), $prjUsers );

							   if($user_assign==""){
							   $user_assign = $AppUI->user_id;
							   }
                            
							   echo arraySelect( $prjUsers, 'user_assign', 'class="text" size="1" ', $user_assign,null,false);
							?> 
							
						</td>	
						<td width="30">

						  <table>
						  <tr>
						  	<td> 
							<input type="hidden" name="log_due_date" value="<?php echo $due_date ? $due_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		                    <input type="text" name="due_date" value="<?php echo $due_date ? $due_date->format( $df ) : '';?>" class="text" disabled="disabled" size="10" />
		
							</td>
						  	<td><a href="#" onClick="popCalendar('due_date', 'due_date')">
							<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
							</a></td>
						  </tr>
						  </table>
						 
						</td>
						<td align="right" colspan="2">
							<input type="submit" value="<?php echo $AppUI->_('save');?>" class="button">&nbsp;
						</td>
					</tr>
			        </form>
					<form name="stFrm" method="POST" >
			        <input type="hidden" name="st" value="">
			<!-- Traigo los datos -->
			<?              
			  if ($comp)
			  {
				$AND = "and status='0' ";
			  }

			  switch($order)
			  {
				  case "prio":
					  $orderby = "order by priority asc, due_date asc";
					  break;
				  case "fech":
					  $orderby = "order by date desc, due_date asc";
					  break;
				  case "descrip":
					  $orderby = "order by description asc, due_date asc";
					  break;
				  case "owner":
					  $orderby = "order by user_owner asc, due_date asc";
					  break;
				  case "assign":
					  $orderby = "order by user_assigned asc, due_date asc";
					  break;
				  case "due_date":
					  $orderby = "order by due_date asc";
					  break;
			  }
              
			  if($order=="")
			  {
				$orderby = "order by priority asc, due_date asc";
			  }

			  if ($m=="tasks" && $task_id > 0)
			  {
			  	$query_tasks = "AND task_id ='".$task_id."' ";
			  }
			  
			  $query = "SELECT 
			  id_todo, 
			  project_id, 
			  description,
			  priority, 
			  user_assigned,
			  user_owner, 
			  status,
			  task_id,
			  date as fecha, 
			  IF (due_date>'0000-00-00 00:00:00',due_date,'3000-01-01 00:00:00') AS due_date
			  FROM project_todo WHERE project_id='$project_id' $AND $query_tasks $orderby";

			  
			  $sql = mysql_query($query);
			  $cant = mysql_num_rows($sql);
              
			  while($vec=mysql_fetch_array($sql))
			  {
			     if ($vec['status']=="1")
				  {
				   $ck1 = "checked";
				  }
				  else
				  {
                   $ck1 = "";
				  }
			?>
			
			      
			<tr bgcolor="#FFFFFF">

			   <td>  
			      
				  <input type="checkbox" name="status[<?=$vec[id_todo]?>]" value="<?=$vec[id_todo]?>"  onclick="actualiza_status(this);" <?=$ck1;?>/>
                
			   </td> 
			  
			   <td align="right" height="18">  
			      
			      <table border="0" width="100%">
			        <tr>
			          <td width="50%" align="right">
			           <? 
				         if ($vec[priority]=="1")
					     {
						 $img = "<IMG SRC=\"./images/high.png\" WIDTH=\"10\" HEIGHT=\"18\" BORDER=\"0\" ALT=\"".$AppUI->_('High priority')."\">";
					     }
	
						 if ($vec[priority]=="3")
					     {
						 $img = "<IMG SRC=\"./images/icons/low.gif\" WIDTH=\"13\" HEIGHT=\"16\" BORDER=\"0\" ALT=\"".$AppUI->_('Low priority')."\">";
					     }
	                     
						 if ($vec[priority]=="2")
					     {
						 $img = "<IMG SRC=\"./images/1x1.gif\" WIDTH=\"13\" HEIGHT=\"16\" BORDER=\"0\" >";
					     }
	
	                     echo $img;
				       ?>
			          </td>
			          <td align="right">
			            <? if ($vec['task_id']>0){ 
			            	
					    	$query_task = "SELECT task_name FROM tasks WHERE task_id='".$vec['task_id']."' ";
					    	$sql_task = db_exec($query_task);
					    	$data_task = mysql_fetch_array($sql_task);
					    	$task_name = $data_task['task_name'];
					    	$task_name=ereg_replace('"','&quot;',$task_name);
							$task_name=ereg_replace("'","%27",$task_name);
							
					    	$detalles = $AppUI->_('To-do associate to the task:')." <br><center>".$task_name."</center>";
					    ?>
					    <span onmouseover="tooltipLink('<pre style=&quot;margin:0px; background:#FFFFFF&quot;><?=$detalles?></pre>', '');" onmouseout="tooltipClose();">
					    	<a href="index.php?m=tasks&a=view&task_id=<?=$vec['task_id']?>"><img src='./images/icons/lupa3.gif'  border='0' height='16' width='16'></a>
					    </span>
					    
					    <? }else{ ?>
					    <IMG SRC="./images/1x1.gif" WIDTH="16" HEIGHT="16" BORDER="0" >
					    <? }?>
			          </td>
			        </tr>
			      </table>
			      
			   </td>
			   <td>  
			      <? 
					  if ($vec[status]=="1")
				    {
					  	$tacho1 = "<strike>";
              $tacho2 = "</strike>";
					  }
					  else{
					  	$tacho1 = "";
              $tacho2 = "";
					  }
					
					//Si el todo esta vencido lo pintamos de rojo  
					if ($vec['due_date']!='N/A' AND ($vec['due_date'] < date ("Y-m-d 00:00:00"))){
						$tacho1.="<font color='red'>";
						$tacho2.="</font>";
					}
					  
					  

					 $dia = substr($vec[fecha],8,2);
				     $mes = substr($vec[fecha],5,2);
					 $anio = substr($vec[fecha],0,4);
				     $fecha = $dia."/".$mes."/".$anio;
				     echo $tacho1.$fecha.$tacho2;
				  ?>
			   </td>
			   <td>  
			      <? 
					  echo $tacho1.$vec[description].$tacho2;
				  ?>
			   </td>
			    <td>  
			      <?  $query2 = "select user_email, CONCAT(user_last_name,', ',user_first_name)as name from users where user_id='$vec[user_owner]'";
					  $sql2 = mysql_query($query2)or die(mysql_error());
					  $resp = mysql_fetch_array($sql2);
					  $nombre_dueno = $resp[name];
					  echo  $tacho1.$nombre_dueno.$tacho2;
				  ?>
			   </td>
			   <td>  
			      <?  $query2 = "select user_email,CONCAT(user_last_name,', ',user_first_name)as name from users where user_id='$vec[user_assigned]'";
					  $sql2 = mysql_query($query2)or die(mysql_error());
					  $resp = mysql_fetch_array($sql2);
					  $nombre_asigned = $resp[name];
					  echo  $tacho1.$nombre_asigned.$tacho2;
				  ?>
			   </td>
			   <td width="20">
			        <? 
					 
				     $dia = substr($vec[due_date],8,2);
				     $mes = substr($vec[due_date],5,2);
					 $anio = substr($vec[due_date],0,4);
				     $due_fecha = $dia."/".$mes."/".$anio;
					 if ($anio!="3000")
				     {
				     echo $tacho1.$due_fecha.$tacho2;
					 }
					 else
					 {
					 echo "N/A";
					 }
				  ?>
               </td>
			   <td align="center" >  
				  <?php  
                  $visible = "0";

				  foreach($admins as $akey => $admin)
				   {
					  if($AppUI->user_id==$akey)
					  {
                      $visible = "1";
					  }
				   }

				   if (($AppUI->user_type == 1) OR ($vec[user_owner]==$AppUI->user_id) OR ($visible=="1")){
				  ?>
				   <table>
					   <tr>
					    <td width='1' align='right'>
					      <input type="checkbox" name="todo_<?=$vec['id_todo']?>" onclick="check_todos('<?=$vec['id_todo']?>', this)" >
					    </td>
						<td width='1' align='right'>
							<a href="?m=<?=$m?>&a=<?=$a?>&<?=$path_item;?>&accion=edit&todo_id=<?php echo $vec['id_todo']; ?>&comp=<?=$comp;?>"><img src="./images/icons/edit_small.gif" alt="<?=$AppUI->_('Edit')?>" border="0"></a>
						</td>
						<td width='1' align='right'>
							<a href="JavaScript:confirma('?m=<?=$m?>&a=<?=$a?>&<?=$path_item;?>&accion=delete&todo_id=<?php echo $vec['id_todo']; ?>','<?=$vec['description'];?>')" ><img src='./images/icons/trash_small.gif' alt='<?=$AppUI->_('Delete')?>' border='0'></a>
						</td>
						<? if(!getDenyEdit("timexp")) { ?>
							<td width='1' align='right' valign="top"><a href='javascript:report_hours(<?php echo $vec['id_todo']; ?>,4);' >
								<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:19px;'></a>
						<?php } ?>
						</td>
					   </tr>
				   </table>
					
				  <?php
			      }
			      else echo "&nbsp;";
	              ?>
                  
			   </td></tr>
			   <tr class="tableRowLineCell" ><td colspan="8"></td></tr>
			  <?
			  } // Fin del while
			   
              ?>
		      </form>
         <tr>
           <td colspan="9" align="right">
           
             <table>
               <tr> 
               
                <form  method='POST' name="form_multiple" id="form_multiple">
				    <input type='hidden' name='accion' value='multi_act'>
				    <input type="hidden" name="todos_ids" value="">
				    <input type="hidden" name="project_id" value="<?=$project_id?>">

				<td>
				   <select name="actions" id="actions" class="text" onchange="js_action_multiple(this, '<?php echo $project_id; ?>','values_m')" style="width:260px;" disabled >
				      <option value="1"><?=$AppUI->_('assign users')?></option>
				      <option value="2"><?=$AppUI->_('associate tasks')?></option>
				      <option value="3"><?=$AppUI->_('delete todos')?></option>
				      <option value="4"><?=$AppUI->_('change priority')?></option>
				      <option value="5"><?=$AppUI->_('chage state')?></option>
				   </select>
				</td>
				<td>
				   <script type="text/javascript">
					    xajax_action_multiple('1', '<?php echo $project_id; ?>','values_m');		
				   </script>
			 
				   <select name="values_m" id="values_m" class="text" style="width:260px;" disabled>
				   </select>
			    </td>
			    <td align="right"><input type="button" class="button" value="<?=$AppUI->_('ok')?>" onclick="save_multiple()"></td>
			      </form>
			      
			  </tr>
			</table>
			
		  </td>
        </tr>
</table>
