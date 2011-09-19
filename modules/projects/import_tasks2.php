<?php /* PROJECTS $Id: import_tasks2.php, Lista las tareas a importar para asignar los recursos  */

global $AppUI;

$project_id = intval( dPgetParam( $_GET, "task_project", 0 ) );
$format_date = $AppUI->user_prefs[SHDATEFORMAT]." ".$AppUI->user_prefs[TIMEFORMAT];



$obj = new CProject();

if (!$obj->load($project_id, false)){
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();

}

$canAddTasks = $obj->canAddTasks();

// Verifico que el usuario tenga permiso para agregar tareas al projecto
if(!$canAddTasks)
{
	$AppUI->redirect( "m=public&a=access_denied" );
}

// setup the title block
$titleBlock = new CTitleBlock( 'Import tasks', 'projects.gif', $m, "$m.$a" );

$titleBlock->addCrumb( "?m=projects", "projects list" );
$titleBlock->addCrumb( "?m=projects&a=view&project_id=$project_id", "view this project" );
$titleBlock->show();

if(count($AppUI->MsProject_tasks)>0)
{
	foreach ($AppUI->MsProject_tasks as $uid_project => $task_project)
    {
		$ord_project_tasks[$task_project[wbs]][uid] = $uid_project;
    }
    
    asort($ord_project_tasks);
    
   /*echo "Vector ordenado <pre>";
      print_r($ord_project_tasks);
    echo "</pre>";*/
}

$sql = "
	SELECT u.user_id, CONCAT_WS(' ',user_first_name,user_last_name)
	FROM users u inner join project_roles pr on u.user_id = pr.user_id
	WHERE pr.project_id = '$project_id' and pr.role_id=2 and u.user_type <>5
	
	UNION
	
	SELECT u.user_id, CONCAT_WS(' ',user_first_name,user_last_name) 
	FROM users u 
	left join project_owners pr on u.user_id = pr.project_owner and pr.project_id='$project_id' 
	left join projects p on u.user_id = p.project_owner and p.project_id='$project_id' 
	WHERE (pr.project_owner is not null and u.user_type <>5 ) OR
	(p.project_owner is not null and u.user_type <>5 )
	";


$users_proficient = db_loadHashList( $sql );	

if (count($users_proficient)>0)
{
	asort($users_proficient);
}

$sql_owners = "SELECT u.user_id, CONCAT_WS(' ',user_first_name,user_last_name) 
			   FROM users u 
			   left join project_owners pr on u.user_id = pr.project_owner and pr.project_id='$project_id' 
			   left join projects p on u.user_id = p.project_owner and p.project_id='$project_id' 
			   WHERE (pr.project_owner is not null and u.user_type <>5 ) OR
			   (p.project_owner is not null and u.user_type <>5 )
			  ";

$owners = db_loadHashList($sql_owners);

if(count($owners)>0) asort($owners);


  /*echo "<pre>";
     print_r($AppUI->MsProject_tasks);
  echo "</pre>";*/
?>

<table width="100%" border="0" cellpadding="3" cellspacing="3" >
 
<tr>
	<td width="100%" valign="top" align="left">

     <form name="uploadFrm"  enctype="multipart/form-data" method="post">
        <input type="hidden" name="dosql" value="do_import_tasks_save" />
        <input type="hidden" name="project_id" value="<?=$project_id?>" >
        
         <table cellspacing="1" cellpadding="2" width="80%" class="std" border="0"> 
         
		  <tr>
		    <td>
		      <b><?=$AppUI->_("Project")?>&nbsp;<?=$obj->project_name;?></b><br><br>&nbsp;
		     
		     </td>
		   </tr>
		  
           <tr>
             <td>
                 
                 
                 <table  cellspacing="1" cellpadding="2" width="<?=$percent?>%" class="std" border="0" > 
                 
                  <tr>
                    <td>
                      <b><?php echo $AppUI->_('Task Visibility');?></b>&nbsp;&nbsp;
                    </td>
                    <td>&nbsp;&nbsp;
                      <?
                       $defaul_task_access = 3;
   					   $task_access = Array ( "2"=> $AppUI->_("Project Wide"), "3"=> $AppUI->_("Only Assigned"));
   					   
   					   echo arraySelect( $task_access, 'task_access', 'class="text"', !is_null($obj->task_access) ? intval( $obj->task_access ) : $defaul_task_access , true );
                      ?>
                    </td>
                  </tr>
                 
                  <tr>
				     <td>
				       <b><?=$AppUI->_("Task Creator")?>&nbsp;&nbsp;</b>
				     </td>
				     <td> &nbsp;&nbsp;
				       <?
		               echo arraySelect( $owners, 'task_owners', 'style="width:250px" size="1" 	class="text"', null ); 
		              ?>
				     </td>
				    
				     
				   </tr>
				   
				    <tr>
				    <td>
				      <br><b><?=$AppUI->_("Resources")?>&nbsp;</b><br>&nbsp;
				     
				     </td>
				   </tr>
                 
                   <?
                      if(count($AppUI->MsProject_Resources)>0)
						{
							foreach ($AppUI->MsProject_Resources as $id_resource => $resource )
							{
								$AppUI->MsProject_Resources[$id_resource][id_proficient]="";
								
								echo "<tr><td   nowrap=\"nowrap\" >".$resource[name]."</td>";
						        echo "<td>=  ".arraySelect( $users_proficient, 'resources['.$id_resource.']', 'style="width:250px" size="1" 	class="text"', null )."</td></tr>"; 
						           	  	
							}
						}
                 ?>
                 </table>
             </td>
           </tr>
           
		   <tr>
            <td>
              <br><b><?=$AppUI->_("Tasks to import")?>&nbsp;</b><br>
            </td>
           </tr>
		
		   <tr>
		    <td align="right">
		      <?
		      // Recorro el vector y voy mostrando las tareas junto a sus recursos 
		      
		      if(count($AppUI->MsProject_tasks)>0)
		      {
		      	foreach ($AppUI->MsProject_tasks as $uid_project => $task_project)
		      	{
		      		$percent = 100;
		      		
		      		for($k=0; $k<=$task_project[wbs_level];$k++)
		      		{
		      		  echo "&nbsp;&nbsp;";
		      		  $percent = $percent - 2;
		      		}
		      		
		      		?>
		      		   <table  cellspacing="1" cellpadding="2" width="<?=$percent?>%" class="std" border="0" > 
						  <tr>
						    <td   nowrap="nowrap" >
						    <? echo $task_project[wbs]." - "; ?>
						    
						    <?
						    if($task_project[wbs_parent]!="")
						    {
						      // Marco la tarea padre como dinamica
							  $AppUI->MsProject_tasks[$ord_project_tasks[$task_project[wbs_parent]][uid]][dynamic]="1";
							 
							  // Agrego el id de la tarea padre
						      $AppUI->MsProject_tasks[$uid_project][task_parent] = $ord_project_tasks[$task_project[wbs_parent]][uid];
						    }
						    
                            ?>
						    </td>
						    <td width="99%" >
						    <? echo $task_project[name]; ?>
						    </td>
						  </tr>
						  <tr>
						    <td></td>
						    </td>
						    <td align="left">
						         <? 
						         $s_date = new CDate($task_project[start_date]);
						         
						         echo $AppUI->_("Start Date")." : ".$s_date->format($format_date);
						         ?><br>
						         <? echo $AppUI->_("Duration")." : ".$task_project[duration]; ?> hs<br>
						         
						         <?
						            switch ($task_project[type])
						            {
						            	case "0":
						            		$descrip_type = $AppUI->_("Fixed Units");
						            		$AppUI->MsProject_tasks[$uid_project][type] = "1";
						            	break;
						            	
						            	case "1":
						            		$descrip_type = $AppUI->_("Fixed Duration");
						            		$AppUI->MsProject_tasks[$uid_project][type] = "2";
						            	break;
						            	
						            	case "2":
						            		$descrip_type = $AppUI->_("Fixed Work");
						            		$AppUI->MsProject_tasks[$uid_project][type] = "3";
						            	break;
						            }
						            
						         ?>
						         <? echo $AppUI->_("Task Type")." : ".$descrip_type; ?><br> 
						         
						         <?
						            switch ($task_project[constraint_type]) 
							            {
							            	case "0":
							            		$descrip_constraint = $AppUI->_("As soon as possible");
						            		    $AppUI->MsProject_tasks[$uid_project][constraint_type] = "3";
							            	break;
							            	
							            	case "1":
							            		$descrip_constraint = $AppUI->_("As late as possible");
						            		    $AppUI->MsProject_tasks[$uid_project][constraint_type] = "4";
							            	break;
							            	
							            	case "2":
							            		$descrip_constraint = $AppUI->_("Must start on");
						            		    $AppUI->MsProject_tasks[$uid_project][constraint_type] = "1";
							            	break;
							            	
							            	case "3":
							            		$descrip_constraint = $AppUI->_("Must finish on");
						            		    $AppUI->MsProject_tasks[$uid_project][constraint_type] = "2";
							            	break;
							            	
							            	case "4":
							            		$descrip_constraint = $AppUI->_("Start no earlier than");
						            		    $AppUI->MsProject_tasks[$uid_project][constraint_type] = "5";
							            	break;
							            	
							            	case "5":
							            		$descrip_constraint = $AppUI->_("Start no later than");
						            		    $AppUI->MsProject_tasks[$uid_project][constraint_type] = "6";
							            	break;
							            	
							            	case "6":
							            		$descrip_constraint = $AppUI->_("Finish no earlier than");
						            		    $AppUI->MsProject_tasks[$uid_project][constraint_type] = "7";
							            	break;
							            	
							            	case "7":
							            		$descrip_constraint = $AppUI->_("Finish no later than");
						            		    $AppUI->MsProject_tasks[$uid_project][constraint_type] = "8";
							            	break;
							            
							            }
						         ?>
						         
						         <? echo $AppUI->_("Constraint type")." : ".$descrip_constraint."<br>"?>
						         <? 
						         
						         if ($AppUI->MsProject_tasks[$uid_project][constraint_date]!="")
						         {
						            $cons_date = new CDate($AppUI->MsProject_tasks[$uid_project][constraint_date]);
						            echo $AppUI->_("Constraint date")." : ".$cons_date->format($format_date)."<br>";
						         }else{
						         	echo $AppUI->_("Constraint date")." : <br>";
						         }
						         
						         ?>
						         <? echo $AppUI->_("Resources")." : "?><br>
						         
						         <table  cellspacing="1" cellpadding="2"  class="std" border="0" > 
								  <tr>
								    <td   nowrap="nowrap" >
						         
						         <?
						           if(count($AppUI->MsProject_tasks[$uid_project][resources])>0)
						           {
						           	  foreach ($AppUI->MsProject_tasks[$uid_project][resources] as $id_resource => $resource )
						           	  {
						           	  	  echo "<tr><td width=\"5\"></td><td   nowrap=\"nowrap\" >".$resource[name]." [".$resource[units]." %] &nbsp;</td></tr>";
						           	  	  
						           	  }
						           }
						         ?>
						         
						            </td>
						          </tr>
						         </table>
						         
						         
						    </td>
						  </tr>
					   </table>
		      		<?
		      		
		      	}
		      }
		      
              ?>
              
		    </td>
		  </tr>
		  
		  <tr>
		    <td align="right">
		      <input type="submit" class="button" value="<?=$AppUI->_("save")?>">
		    </td>
		  </tr>
		  
		 </table>
        
     </form>
     
    </td>
</tr>
</table>