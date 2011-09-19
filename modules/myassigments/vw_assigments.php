<?php  /* My Assigment $Id: vw_assigments.php,v 1.3 2009-07-15 00:00:53 nnimis Exp $ */
require_once("./modules/timexp/report_to_items.php");

$company_id = $_POST['company_id'];
$canal_id = $_POST['canal_id'];
$project_id = $_POST['project_id'];
$user_id = $_POST['user_id'];
$s_date = $_POST['filter_from_date'];
$e_date = $_POST['filter_to_date'];
$only_old = $_POST['only_old'];

if ($s_date !=""){
$start_date = substr($s_date,0,4)."-".substr($s_date,4,2)."-".substr($s_date,6,2)." 00:00:00";
}
if ($e_date !=""){
$end_date = substr($e_date,0,4)."-".substr($e_date,4,2)."-".substr($e_date,6,2)." 00:00:00";
}

if (isset($_POST['only_old']))
{
  $only_old = true;
}else{
  $only_old = false;
}

$ts = time();
$today = date("Y-m-d H:m:s",$ts);

?>
<!-- Cabecera de la tabla -->
<form id="frmMyAssigment" name="frmMyAssigment">

<table class="" id="tbtasks" border="0" cellpadding="1" cellspacing="0" width="100%">
	<tbody>
	  <tr class="tableHeaderGral">
	    <td width="20" nowrap></td>
	    <td width="1%" nowrap></td>
		<td width="15%"><?php echo $AppUI->_('Date'); ?></td>
		<td width="50%"><?php echo $AppUI->_('Description'); ?></td>
		<td width="50" nowrap><?php echo $AppUI->_('Type'); ?></td>
		<td nowrap><?php echo $AppUI->_('Due Date'); ?></td>
		<td width="1%" nowrap><?php echo $AppUI->_('Active'); ?></td>
	  </tr>
	

<?
if ($company_id !="" && $company_id != 0)
{
	$sql_cia = "AND projects.project_company = '".$company_id."' "; 
}

if ($canal_id !="" && $canal_id != 0)
{
	$sql_canal = "AND projects.project_canal = '".$canal_id."' "; 
}

if ($project_id !="" && $project_id != 0)
{
	$sql_project_1 = "AND project_id IN ('$project_id') ";
	$sql_project_2 = "AND tasks.task_project IN ('$project_id') ";
}

if ($user_id != "" && $user_id != 0)
{
	$sql_user = "AND (projects.project_owner = $AppUI->user_id OR project_owners.project_owner = $AppUI->user_id) ";
}

if ($s_date!="" && $e_date!="")
{
	$sql_date1 = "AND (last_updated >='".$s_date."' AND last_updated <= '".$e_date."') ";
	
    $sql_date2 = "AND ( date >= '".$s_date."' AND date <='".$e_date."')";
    
    $sql_date3 = "AND ( tasks.task_start_date >= '".$s_date."' AND tasks.task_start_date <='".$e_date."')";
}


if($only_old)
{
	$sql_due1 = "AND ( date_deadline >= '".$today."' OR date_deadline ='0000-00-00 00:00:00' OR date_deadline is null) ";
	
	$sql_due3 = "AND tasks.task_end_date >= '".$today."' ";
	
	$sql_due2 = "AND (due_date >='".$today."' OR due_date='0000-00-00 00:00:00')";
}

/*
## Traigo los proyectos en los que el usuario tiene permisos, sino tengo un proyecto filtrado
if ($project_id == "" || $project_id == 0)
{
	$obj = new CProject();
	$prjs = $obj->getAllowedRecords($user_id, "project_id");


	if(count($prjs)>0)
	{
		$allow_prjs = "AND project_id IN ('".implode( "','", array_keys($prjs) )."')";
	}else{
		$allow_prjs = "AND project_id IN ('-1')";
	}
}
*/

//Si el usuario es distinto de blanco o de 0, seteo el usuario de la session, caso contrario es un usuario seleccionado del combo
if ($user_id == "" || $user_id == 0)
	$user_id = $AppUI->user_id;
	
## Traigo los projectos al que pertenece el usuario y que tiene items no resueltos o completados

$sql="
SELECT distinct(project_id) FROM btpsa_bug_table WHERE handler_id ='".$user_id."' AND  status <> '80' AND status<>'90' 
$sql_project_1
$sql_date1
$sql_due1
UNION
SELECT distinct(project_id) FROM project_todo WHERE user_assigned ='".$user_id."' AND status ='0' 
$sql_project_1
$sql_date2
$sql_due2
UNION
SELECT distinct(tasks.task_project) FROM tasks, user_tasks 
WHERE task_complete = '0' 
AND (tasks.task_id = user_tasks.task_id AND user_tasks.user_id='".$user_id."')
$sql_project_2
$sql_date3
$sql_due3
";

$aut_proj=implode( "','", array_keys(db_loadHashList($sql)) );


# Traigo los detatalles de cada projecto para listar
$sql_project ="SELECT DISTINCT 
				  projects.project_id,
				  projects.project_color_identifier,
				  projects.project_name,
				  projects.project_active,
				  projects.project_status
			   FROM projects
			   LEFT OUTER JOIN project_owners ON project_owners.project_id = projects.project_id
			   WHERE projects.project_id IN ('".$aut_proj."')
			   $sql_cia
               $sql_canal
               $sql_user
               order by project_name
			   ";

$projects = db_loadList($sql_project);
$cant = count($projects) - 1; 

$activeAssigment = getMyAssigmentActive($AppUI->user_id);

# Lista de proyectos
for ($i=0; $i<= $cant; $i++)
{
	//Solo proyectos que sean activos y que 
	if($projects[$i]['project_active'] == '1' && $projects[$i]['project_status'] != '4' && $projects[$i]['project_status'] != '5')
	{
	?>
	<tr class="tableRowLineCell">
		<td colspan="8"></td>
	</tr>
	<tr>
	    <td width='20'>
				<a href="javascript: //" onclick="javascript: show_hide_projects('<?php echo $projects[$i]['project_id'] ?>');">
				<img id="imgprj_<?php echo $projects[$i]['project_id'] ?>" src="./images/icons/collapse.gif" alt="<?=$AppUI->_('Hide')?>" border="0" height="16" width="16">
				</a>
		</td>
		
		<td colspan="8">
			<table border="0" width="100%">
			  <tbody>
			    <tr>
					<td style="border: 2px outset rgb(238, 238, 238); background-color:#<?php echo $projects[$i]['project_color_identifier']; ?>;" nowrap="nowrap" >
						<a href="./index.php?m=projects&amp;a=view&amp;project_id=<?php echo $projects[$i]['project_id'] ?>">
						<span style="color:<?php echo bestColor($projects[$i]['project_color_identifier']); ?>; text-decoration: none;"><strong><?php echo getcompany($projects[$i]['project_id'])." / ".$projects[$i]['project_name']; ?></strong></span></a>
					</td>
					<td width='100%'>
					</td>
				</tr>
			   </tbody>
			 </table>
		 </td>
	</tr>
			<?
			unset($list);
			unset($list_vencidos);
			unset($todos_reg);
			
			if (!$only_old){
			// Traigo las incidencias vencidas de acuerdo al proyecto
			$bug_o = show_bugs($projects[$i]['project_id'],$user_id,'1',$start_date, $end_date);
			}
			
			// Traigo las incidencias no vencidas de acuerdo al proyecto
			$bug = show_bugs($projects[$i]['project_id'],$user_id,'0',$start_date, $end_date);
			
			if (!$only_old){
			// Traigo las tareas asignadas vencidas
			$task_o= show_task($projects[$i]['project_id'],$user_id,'1',$start_date, $end_date);
			}
			
			// Traigo las tareas asignadas no vencidas
			$task = show_task($projects[$i]['project_id'],$user_id,'0',$start_date, $end_date);
			
			if (!$only_old){
			// Traigo los todos asignados vencidos
			$todo_o = show_todos($projects[$i]['project_id'],$user_id,'1',$start_date, $end_date);
			}
			
			// Traigo los todos asignados no vencidos
			$todo = show_todos($projects[$i]['project_id'],$user_id,'0',$start_date, $end_date);
			
			// No vencidos
			$list = array_merge((array)$bug,(array)$list);
                                    $list = array_merge((array)$todo,(array)$list);
                                    $list = array_merge((array)$task,(array)$list);

			rsort($list);
			
			// Vencidos
			$list_vencidos = array_merge((array)$todo_o,(array)$list_vencidos);
			$list_vencidos = array_merge((array)$task_o,(array)$list_vencidos);
			$list_vencidos = array_merge((array)$bug_o,(array)$list_vencidos);
			rsort($list_vencidos);
			
			$todos_reg = array_merge($list_vencidos,$list);
			
			/*foreach ($todos_reg as $key => $vec){
				echo "ID: ".$vec['assignment_id'];
				echo "   Type: ".$vec['assignment_type'];
				echo "<br>";
			}*/
			
			foreach ($todos_reg as $key => $vec)
			{
				$detalles = $vec['detail'];
				$detalles=ereg_replace('"','&quot;',$detalles);
				$detalles=ereg_replace("'","%27",$detalles);
				
			?>
				<tr id="tr_project_<?=$projects[$i]['project_id']?>_<?=$vec['assignment_id'].$vec['assignment_type']."_0"?>" style="display:''">
				  <td></td>
		    	  <td class="tableRowLineCell" colspan="8"></td>
		    	</tr>
				<tr id="tr_project_<?=$projects[$i]['project_id']?>_<?=$vec['assignment_id'].$vec['assignment_type']?>" style="display:''">
	  				<td>&nbsp;</td>
	  				<td nowrap>
	  				<?php if(!getDenyEdit("timexp")) { ?>
		    	  <a href='javascript:report_hours(<? echo $vec['assignment_id']; ?>,"<? echo $vec['assignment_type']; ?>");' >
				  <img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'></a>
				  <?php } ?>
		    	    <span onmouseover="tooltipLink('<pre style=&quot;margin:0px; background:#FFFFFF&quot;><?=@$detalles?></pre>', '');" onmouseout="tooltipClose();">
		    	       <img src='./images/icons/lupa3.gif'  border='0' height='20' width='20'>
		    	    </span>
		    	  </td>
		    	 
		    	  <td width='12%' align='left'>
		    	    <font color="<?=$vec['font']?>"><?=$vec['date']?></font>
		    	  </td>
		    	  <td width='50%' >
		    	    <?=$vec['ref']?>
		    	      <font color="<?=$vec['font']?>"><?=$vec['description']?></font>
		    	    </a>
		    	  </td>
		    	  <td width='70'>
		    	    <font color="<?=$vec['font']?>"><?=$vec['type']?></font>
		    	  </td>
		    	  <td align='left' width='70'>
		    	    <font color="<?=$vec['font']?>"><?=$vec['due_date']?></font>
		    	  </td>
		    	  <td align='center' >
		    	    <font color="<?=$vec['font']?>"><input type="checkbox" onclick="activeMyAssigment(this, <?=$AppUI->user_id?>, <?=$vec['assignment_id']?>, '<?=$vec['assignment_type']?>')" name="chkActive_<?=$vec['assignment_id'].$vec['assignment_type']?>" <?=($activeAssigment[0]['myassigment_id'] == $vec['assignment_id'] ? 'checked' : '')?> <?=($user_id != $AppUI->user_id ? 'disabled' : '')?> /></font>
		    	  </td> 
		    	</tr>
		    	
		      <? } ?>	
	<?
 }
}



## Traigo las incidencias
#
#  1- Todas las vencidas que no estan resueltas
#  ( Que sean distintas de 80 - resuelto  y 90 cerrado
#  Se considera vencida si la fecha date_deadline es menor a la fecha actual y la incidencia esta en algunos de los estados mencionados.

?>

   <tr class="tableRowLineCell">
		<td colspan="7"></td>
	</tr>
 
   </tbody>
</table>

</form>

<?php

function getCompany ($project_id){
	$sql="SELECT c.company_name FROM companies AS c INNER JOIN projects AS p ON p.project_company = c.company_id WHERE p.project_id = $project_id";
	$row=mysql_fetch_array(mysql_query($sql)) or die(mysql_error());

	return $row['company_name'];
}
?>

<script language="javascript">
	function activeMyAssigment(objCheck, user_id, assigment_id, assigment_type)
	{
		for (i=0;i<document.frmMyAssigment.elements.length;i++)
		{
			if(document.frmMyAssigment.elements[i].type == 'checkbox' && document.frmMyAssigment.elements[i].name.indexOf('chkActive_') >= 0 && document.frmMyAssigment.elements[i].name != objCheck.name)
				document.frmMyAssigment.elements[i].checked = false;
		}
	
		xajax_changeMyAssigmentsActive(user_id, assigment_id, assigment_type, (objCheck.checked ? 1 : 0));
	}
</script>
