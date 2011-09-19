<?php 
require_once( "./modules/tasks/functions.php" );

$project_id = $_GET['project_id'];

$sort_item1 = $_GET['sort_item1'];
$sort_type1 = $_GET['sort_type1'];
$sort_item2 = $_GET['sort_item2'];
$sort_type2 = $_GET['sort_type2'];
$link = $_GET['link'];


// Traigo todas las tareas del proyecto para hacer la sumatoria de campos para las tareas dinamicas

$query_tasks = "SELECT tasks.task_id, task_wbs_level, task_wbs_number, task_parent, task_dynamic, task_complete,
IF(task_complete like '1', '100', task_manual_percent_complete) AS task_manual_percent_complete
FROM tasks
WHERE task_project = '".$project_id."' 
order by task_wbs_level desc, task_parent , task_wbs_number";
$sql_tasks = db_exec($query_tasks);

while($vec = mysql_fetch_array($sql_tasks))
{	
		$project_tasks[$vec['task_id']][level] = $vec['task_wbs_level'];
	    $project_tasks[$vec['task_id']][parent] = $vec['task_parent'];
	    
	    if(($vec['task_complete'] == '1' || $vec['task_manual_percent_complete'] == '100') && $vec['task_dynamic'] == '0')
	    {
	    	
	    	$project_tasks[$vec['task_id']][complete] = "1";
	    }
	    if(($vec['task_complete'] == '0' || $vec['task_manual_percent_complete'] != '100') && $vec['task_dynamic'] == '0')        {
	    	$project_tasks[$vec['task_id']][complete] = "0";
	    }
	    
	    if($vec['task_complete'] == '1' && $vec['task_dynamic'] == '1')
	    {
	    	$project_tasks[$vec['task_id']][complete] = "1";
	    }
	    if($vec['task_complete'] == '0' && $vec['task_dynamic'] == '1'){
	    	$project_tasks[$vec['task_id']][complete] = "0";
	    }
	    
	    
	    // Traigo de la bd la primera fecha en que se reportaron hs para esta tarea (agregar despues to-dos e incidencias)
	    $query_dates = "SELECT date_format(min(timexp_start_time),'%Y%m%d%H%i') AS min_date, date_format(max(timexp_end_time),'%Y%m%d%H%i') AS max_date from timexp WHERE timexp_type='1' AND timexp_applied_to_type = '1' AND timexp_applied_to_id = '".$vec['task_id']."' ";
	   
	    $sql_dates = db_exec($query_dates);
	    $dates = mysql_fetch_row($sql_dates);
	    
	    $project_tasks[$vec['task_id']][min_start_dates] = $dates['0'];
	    $project_tasks[$vec['task_id']][max_start_dates] = $dates['1'];
	    
	    // Sumatoria de hs reportada a esta tarea (agregar despues to-dos e incidencias)
	    $query_sum_hs = "SELECT sum(timexp_value) FROM timexp WHERE timexp_type='1' AND timexp_applied_to_type = '1' AND timexp_applied_to_id = '".$vec['task_id']."' ";
	    $hs = db_loadResult($query_sum_hs);
	    
	    $project_tasks[$vec['task_id']][real_work] = $hs;
	    
	    $project_tasks[$vec['task_id']][dynamic] = $vec['task_dynamic'];
	 
	    $parents[$vec['task_parent']][] = $vec['task_id'];
	    
}


$query_tasks = "SELECT tasks.task_id, task_wbs_level, task_wbs_number, task_parent, task_dynamic
FROM tasks
WHERE task_project = '".$project_id."' 
order by task_wbs_level desc, task_parent , task_wbs_number";
$sql_tasks = db_exec($query_tasks);

// Actualizo las tareas padres
while($vec = mysql_fetch_array($sql_tasks))
{	
	$hijas_completas = 1;
	
	if ($vec['task_dynamic']=='1')
	{
		
		$padres = $parents[$vec['task_id']];
		//$min_d = "99999999999999";
		//$max_d = "00000000000000";
		$min_d = $project_tasks[$vec['task_id']][min_start_dates];
		$max_d = $project_tasks[$vec['task_id']][max_start_dates];
		$r_work = 0;
		
		
		foreach ($padres as $orden=>$hija)
		{
			if ( $project_tasks[$hija][min_start_dates] <  $min_d && $project_tasks[$hija][min_start_dates] != ""){
			$min_d = $project_tasks[$hija][min_start_dates];
			}
			
			if ( $project_tasks[$hija][max_start_dates] >  $max_d && $project_tasks[$hija][max_start_dates] != ""){
			$max_d = $project_tasks[$hija][max_start_dates];
			}
			
			$r_work = $r_work + $project_tasks[$hija][real_work];
			
			if ($vec['task_parent']!= $hija )
			{
			  $hijas_completas = $hijas_completas * $project_tasks[$hija][complete];
			}
			
		}
		
		
		$project_tasks[$vec['task_id']][level] = $vec['task_wbs_level'];
		$project_tasks[$vec['task_id']][parent] = $vec['task_parent'];
		$project_tasks[$vec['task_id']][hijas_completas] = $hijas_completas;

		if($min_d != "99999999999999")
		$project_tasks[$vec['task_id']][min_start_dates] = $min_d;
		
		if($max_d != "00000000000000")
		$project_tasks[$vec['task_id']][max_start_dates] = $max_d;
		
		$project_tasks[$vec['task_id']][real_work] = $r_work;
		
	}
}


if($_POST[action_form]=="update_complete")
{
	if($_POST['task_complete']=='0') {
		$sql = "UPDATE tasks SET task_complete = '1' WHERE task_id = ".$_POST['task_id'];
		$rc=db_exec($sql);
	}
	if($_POST['task_complete']=='1') {
		$sql = "UPDATE tasks SET task_complete = '0' WHERE task_id = ".$_POST['task_id'];
		$rc=db_exec($sql);
	}
}


?>

<script language="Javascript">
<!-- 

function chage_complete(task,complete)
{
	document.TaskComplete.task_id.value = task;
	document.TaskComplete.task_complete.value = complete;
	document.TaskComplete.submit();
}

//-->
</script>

<form method="POST" name="TaskComplete" id="TaskComplete" >
  <input type="hidden" name="task_id">
  <input type="hidden" name="task_complete">
  <input type="hidden" name="action_form" value="update_complete" >
</form>

<table width="100%" border="0" cellpadding="2" cellspacing="0" class="" id="tbtasks">
	<col width="30"><!-- Check Compleatado -->
	<col width="40"><!--Porcentaje de compleatado-->
	<col width="120px"><!-- Nombre de la tarea -->
	<col width="70"><!-- Trabajo estimdo -->
	<col width="40"><!-- Trabajo actual -->
	<col width="130"><!-- Fecha de inicio -->
	<col width="130"><!-- Fecha de fin -->
	<col ><!-- Usuarios -->
	<tr class="tableHeaderGral">
		<td class="tableHeaderText" align="center"><!-- Check Compleatado --></td>
		<td nowrap="nowrap" class="tableHeaderText"><!--Porcentaje de compleatado--></td>
		<td width="40%"class="tableHeaderText"> 
		  <?
		   
	             switch ($sort_type1)
	             {
	             	case "desc";
	             	   $order1 = "";
	             	   break;
	             	case "asc";
	             	   $order1 = "desc";
	             	   break;
	             	default:
	             		$order1 = "asc";
	             }
	             
             
	             switch ($sort_type2)
	             {
	             	case "desc";
	             	   $order2 = "";
	             	   break;
	             	case "asc";
	             	   $order2 = "desc";
	             	   break;
	             	default:
	             		$order2 = "asc";
	             } 
             
		  ?>
		  
		  <?
		     if($sort_type1 == "asc")
		     {
		     	echo '<img src="./images/icons/low.gif" width=13 height=16>';
		     }
		     if($sort_type1 == "desc")
		     {
		     	echo '<img src="./images/icons/1.gif" width=13 height=16>';
		     }
		  ?>
		  <a href="index.php?m=projects&a=view&project_id=<?=$project_id?>&tab=<?=$AppUI->state['ProjVwTab']?>&sort_item1=task_name&sort_type1=<?=$order1?>" > 
		  <?php echo $AppUI->_('Task Name');?>
		  </a>
		</td>
		<td nowrap="nowrap" class="tableHeaderText">
		 
		  <table> 
		    <tr>
		      <td>
		          <?
				     if($sort_type2 == "asc")
				     {
				     	echo '<img src="./images/icons/low.gif" width=13 height=16>';
				     }
				     if($sort_type2 == "desc")
				     {
				     	echo '<img src="./images/icons/1.gif" width=13 height=16>';
				     }
				  ?>
		      </td>
		      <td>
		           <a href="index.php?m=projects&a=view&project_id=<?=$project_id?>&tab=<?=$AppUI->state['ProjVwTab']?>&sort_item2=est_work&sort_type2=<?=$order2?>" >
				    <?php echo $AppUI->_('Estimated work');?>
				  </a>
		      </td>
		    </tr>
		  </table>
		  
		</td>
		<td class="tableHeaderText" align="center"><?php echo $AppUI->_('Actual work');?></td>
		<td nowrap="nowrap" class="tableHeaderText"><?php echo $AppUI->_('Start Date');?></td>
		<td nowrap="nowrap" class="tableHeaderText"><?php echo $AppUI->_('Finish Date');?></td>
		<td nowrap="nowrap" class="tableHeaderText"><?php echo $AppUI->_( 'Users');?>&nbsp;&nbsp;</td>
	</tr>
	
<?php
   
   $order_by = "";
   
   if ($sort_item1 != "" && $sort_type1 !="")
   {
   	  $order_by = "order by task_name $sort_type1";
   }
   
   if ($sort_item2 != "" && $sort_type2 !="")
   {
   	  if ($order_by != "")
   	  {
   	  	$order_by = $order_by.", task_work $sort_type2";
   	  }else{
   	  	$order_by = "order by task_work $sort_type2";
   	  }
   }
   
   if ($order_by =="")
   {
   	$order_by = "order by task_wbs_number";
   }
   
   // Traigo primero las tareas de nivel 0
   
   show_tasks(0, 0, $project_tasks, $order_by);
?>
	
	
</table>


<?php

  
	function show_tasks($level, $parent_id, $project_tasks, $order_by)
	{
		global $AppUI ,$project_id ;
		
		if( $parent_id != '0')
		{
			$parent = "AND task_parent ='".$parent_id."' ";
		}
		
		$query = "SELECT tasks.task_id,tasks.task_name,tasks.task_dynamic, tasks.task_work, task_complete, task_wbs_level, task_wbs_number, task_parent,
			  IF(task_complete like '1', '100', task_manual_percent_complete) AS task_manual_percent_complete
			  FROM tasks
			  WHERE task_project = '".$project_id."' AND  task_wbs_level = '".$level."' $parent  $order_by";
        
		/*echo "<pre>";
	      print_r($query);
	    echo "</pre>";*/
	    
		$sql = db_exec($query);
		$cant = mysql_num_rows($sql);
	    
	    if ($cant > 0)
	    {
	    	
		    while($data = mysql_fetch_array($sql))
		    {
		    	
		    	?>
		    	<tr class="tableRowLineCell" ><td colspan="8"></td></tr>
		    	<tr valign="top">
		    	  <td>  
		    	     <?
		    	     
		    	        if( ($data['task_dynamic']==1 && $project_tasks[$data['task_id']][hijas_completas]==0) || ($project_tasks[$data['task_parent']][complete]==1 && $data['task_parent']!= $data['task_id']))
		    	        {
		    	        	$check = "disabled"; 
		    	        }else{
		    	        	$check = ""; 
		    	        }
		    	        
		    	        
		    	        if ($data['task_complete']==1)
		    	        {
		    	        	$task_complete = "1";
		    	        	$checked = "checked";
		    	        	$font = "#3366CC";
		    	        }else{
		    	        	$task_complete = "0";
		    	        	$checked = "";
		    	        	$font = "#000000";
		    	        }
		    	        
		    	     ?>
		    	     <input type="checkbox" name="comp<?=$data['task_id']?>" value="<?=$task_complete?>" onclick="chage_complete('<?=$data['task_id']?>','<?=$task_complete?>')" <?=$check?> <?=$checked?> >
		    	     <br>&nbsp;
		    	  </td>
		    	  <td>
		    	     <?=$data['task_manual_percent_complete']?> %
		    	  </td>
		    	  <td>
		    	     <table border="0" width="100%">
		    	       <tr>
		    	         <?
		    	            for ($i=0;$i<=$level;$i++)
		    	            {
		    	            	echo "<td width=\"5\">&nbsp;</td>";
		    	            }
		    	         ?>
		    	         <td>
		    	           <? 
		    	           $a['task_wbs_level'] = $data['task_wbs_level'];
		    	           $a['task_wbs_number'] = $data['task_wbs_number'];
		    	           $a['task_parent'] = $data['task_parent'];
		    	           
		    	           if ($level > 0)
		    	           {
		    	           	 echo "&nbsp;<img src=\"./images/corner-dots.gif\" width=\"16\" height=\"12\" border=\"0\">&nbsp;";
		    	           }
		    	           
		    	           echo "<a href=\"index.php?m=tasks&a=view&task_id=".$data['task_id']."\"><font color=\"$font\"><b>".wbs($a)." - ".$data['task_name']."</b></font></a>"; 
		    	           ?>
		    	         </td>
		    	       </tr>
		    	     </table>
		    	     
		    	  </td>
		    	  <td> 
		    	    <font color="<?=$font?>"> <?=$data['task_work']?> h </font>
		    	  </td>
		    	  <td>
		    	    <font color="<?=$font?>">
		    	    <? 
		    	    if($project_tasks[$data['task_id']][real_work] != "")
		    	    {
		    	      $real_work = number_format($project_tasks[$data['task_id']][real_work],2,".","");
		    	      $numero = explode(".",$real_work);
		    	      
		    	      if ($numero[1] >0)
		    	      {
		    	      	echo $real_work." h"; 
		    	      }else{
		    	      	echo $project_tasks[$data['task_id']][real_work]." h"; 
		    	      }
		    	       
		    	    }else{
		    	      echo "0 h";
		    	    }
		    	    ?>
		    	    </font>
		    	  </td>
		    	   
		    	  <td>
		    	     <font color="<?=$font?>">
		    	     <? 
		    	     if ($project_tasks[$data['task_id']][min_start_dates]!="")
		    	     {
		    	     	$min_date = new CDate( $project_tasks[$data['task_id']][min_start_dates] );
		    	     	echo $min_date->format("%d/%m/%Y %H:%M"); 
		    	     }else{
		    	     	echo "N/A";
		    	     }
		    	     ?>
		    	     </font>
		    	  </td>
		    	  <td>
		    	     <font color="<?=$font?>">
		    	     <? 
		    	     if ($project_tasks[$data['task_id']][max_start_dates]!="")
		    	     {
		    	     	 if ($data['task_complete']==1)
		    	         {
		    	     	   $max_date = new CDate( $project_tasks[$data['task_id']][max_start_dates] );
		    	     	    echo $max_date->format("%d/%m/%Y %H:%M"); 
		    	         }else{
		    	         	echo "N/A";
		    	         }
		    	     }else{
		    	     	echo "N/A";
		    	     }
		    	     ?>
		    	     </font>
		    	  </td>
		    	  <td> 
		    	     <table>
		    	     <?
		    	       // Traigo los recursos y sus porcentajes de asignacion
		    	       $query_users = "SELECT user_tasks.user_id, user_tasks.user_units, users.user_username,users.user_first_name, users.user_last_name FROM user_tasks LEFT JOIN users ON user_tasks.user_id = users.user_id WHERE task_id='".$data['task_id']."' ";
		    	       $sql_users = db_exec($query_users);
		    	       
		    	       while($users_asigned = mysql_fetch_array($sql_users))
		    	       {
		    	       	 echo "<tr><td><font color=\"$font\">".$users_asigned['user_username']." [".$users_asigned['user_units']."%]</font></td></tr>";
		    	       }
		    	       
		    	     ?>
		    	     </table>
		    	     
		    	  </td>
		    	</tr>
		    	<tr class="tableRowLineCell" ><td colspan="8"></td></tr>
		    	<?
		    	$next_level = $level + 1;
		    	show_tasks($next_level, $data['task_id'],$project_tasks, $order_by);
		    	
		    }
		    
	    }else{
	    	return;
	    }

	}


?>