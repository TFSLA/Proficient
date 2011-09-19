<?php /* PROJECTS $Id: import_tasks.php, Formulario para la importacion de tareas desde un proyecto  */

global $AppUI;

$project_id = intval( dPgetParam( $_GET, "task_project", 0 ) );

unset($AppUI->MsProject_tasks);

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

?>

<table width="100%" border="0" cellpadding="3" cellspacing="3" >
 
<tr>
	<td width="100%" valign="top" align="center">

     <form name="uploadFrm"  enctype="multipart/form-data" method="post">
        <input type="hidden" name="dosql" value="do_import_tasks" />
        <input type="hidden" name="project_id" value="<?=$project_id?>" >

		<table cellspacing="1" cellpadding="2" width="40%" class="std" > 
		
		  <tr>
		     <td bgcolor="<?=$obj->project_color_identifier ?>" colspan="2">
		        <b> <?=$obj->project_name ?></b>
		     </td>
		  </tr>
		  
		  <tr>
		     <td colspan="2">
		        <br>&nbsp;
		     </td>
		  </tr>
		  
		  <tr>
			<td align="right" nowrap="nowrap" ><?php echo $AppUI->_( 'File' );?> (*):</td>
			<td align="left" ><input type="File" class="button" name="formfile" size="28" ></td>
		  </tr>
		  
		  <tr>
		     <td align="right" colspan="2">
		       <br>
		        <table>
		          <tr>
		            <td>
		              <input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onClick="javascript:history.back(-1);" />
		              <input type="submit" class="button" value="<?php echo $AppUI->_( 'Import tasks' );?>"  />&nbsp;&nbsp;
		              
		            </td>
		          </tr>
		        </table>
		        
		     </td>
		  </tr>
		  
		  <tr>
		     <td colspan="2">
		       <br> &nbsp;(*) <?php echo $AppUI->_( 'File format required: xml' );?>
		     </td>
		  </tr>
		  
		 </table>
		 
	 </form>
	 
   </td>
 </tr>

</table>