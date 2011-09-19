<?php
	# This page allows actions to be performed an an array of bugs
?>
<?php require_once( 'core.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
    
	$f_action = gpc_get_string( 'action', '' );
	$f_bug_arr = gpc_get_int_array( 'bug_arr', array() );

	# redirects to all_bug_page if nothing is selected
	if ( ( $f_action=='' ) || 0 == sizeof( $f_bug_arr ) ) {
		if($_GET['o']=="tasks" || $_GET['o']=="projects") 
	            {
	                print_header_redirect( 'index.php?'.$AppUI->state['SAVEDPLACE-1'] );
	            }else{
		   print_header_redirect( 'index.php?m=webtracking&a=view_all_bug_page' );
	            }
	}
	
	if($_GET['o']=="projects"){
		 helper_set_current_project( $_GET['project_id'] );
		 helper_set_current_task(  '0' );
		 $p_project_id = $_GET['project_id'];
	}elseif ($_GET['o']=="tasks"){
	             helper_set_current_project( $_GET['project_id'] );
		 helper_set_current_task( $_GET['task_id'] );
		 $p_project_id = $_GET['project_id'];
	}else{
	             $p_project_id = helper_get_current_project();
	}
	
	// Si quiere asociar a tareas y no trae seleccionado un proyecto, lo mando a seleccionarlo
	if($f_action == "ASSOCIATE_TASKS" && $p_project_id =="")
	{
		print_header_redirect( 'index.php?m=webtracking&a=login_select_proj_page&ref=' );
	}

	
	$t_finished = false;
	switch ( $f_action )  {
		# Use a simple confirmation page, if close or delete...
		case 'CLOSE' :
			$t_finished 			= true;
			$t_question_title 		= lang_get( 'close_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'close_group_bugs_button' );
			break;

		case 'DELETE' :
			$t_finished 			= true;
			$t_question_title		= lang_get( 'delete_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'delete_group_bugs_button' );
			break;

		# ...else we define the variables used in the form
		case 'MOVE' :
			$t_question_title 		= lang_get( 'move_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'move_group_bugs_button' );
			$t_form					= 'project_id';
			break;

		case 'ASSIGN' :
			$t_question_title 		= lang_get( 'assign_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'assign_group_bugs_button' );
			$t_form 				= 'assign';
			break;

		case 'RESOLVE' :
			$t_question_title 		= lang_get( 'resolve_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'resolve_group_bugs_button' );
			$t_form 				= 'resolution';
			$t_request 				= 'resolution'; # the "request" vars allow to display the adequate list
			break;

		case 'UP_PRIOR' :
			$t_question_title 		= lang_get( 'priority_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'priority_group_bugs_button' );
			$t_form 				= 'priority';
			$t_request 				= 'priority';
			break;

		case 'UP_STATUS' :
			$t_question_title 		= lang_get( 'status_bugs_conf_msg' );
			$t_button_title 		= lang_get( 'status_group_bugs_button' );
			$t_form 				= 'status';
			$t_request 				= 'status';
			break;
		case 'ASSOCIATE_TASKS':
			$t_question_title 		= lang_get( 'link_task_conf_msg' );
			$t_button_title 		= lang_get( 'task_group_bugs_button' );
			$t_form 				= 'tasks';
			$t_request 				= 'tasks';
			break;
	}
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>
<?php  # displays the choices popup menus
	if ( ! $t_finished ) {
?>
<br />
<div align="center">
<form method="POST" action="index.php?m=webtracking&a=bug_actiongroup&o=<?=$_GET['o']?>&task_id=<?=$_GET['task_id']?>&project_id=<?=$_GET['project_id']?>">
<input type="hidden" name="action" value="<?php echo string_attribute( $f_action ) ?>" />
<table class="width75" cellspacing="1">
<?php foreach( $f_bug_arr as $t_bug_id ) { ?>
		<input type="hidden" name="bug_arr[]" value="<?php echo $t_bug_id ?>" />
<?php } ?>
<tr class="row-1">
	<td class="category">
		<?php echo $t_question_title ?>
	</td>
	<td>
		<select name="<?php echo $t_form ?>">
			<?php
				switch ( $f_action ) {
					case 'MOVE':
						print_project_option_list( null, false );
						break;
					case 'ASSIGN':
						print_assign_to_option_list();
						break;
					case 'ASSOCIATE_TASKS':
						print_link_to_task_option_list($p_project_id);
						break;
					default:
						#other forms use the same function to display the list
						print_enum_string_option_list( $t_request, FIXED );
						break;
				}
			?>
		</select>
	</td>
</tr>
<tr>
          
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo $t_button_title ?>" />
	</td>
</tr>
</table>
</form>
</div>
<?php # Choices Form END ?>
<?php
	} else {
		# else, asks for a simple confirmation to close or delete
?>
<br />
<div align="center">
	<?php print_hr() ?>
	<?php echo $t_question_title . '<br /><br />' ?>

	<form method="post" action="index.php?m=webtracking&a=bug_actiongroup">
		<input type="hidden" name="action" value="<?php echo $f_action ?>" />

	<?php foreach( $f_bug_arr as $value ) { ?>
		<input type="hidden" name="bug_arr[]" value="<?php echo $value ?>" />
	<?php } ?>
	
	           <input type="button" class="button" onClick="history.back()" value="<?=lang_get( 'cancel_bug_button' )?>" />
		<input type="submit" class="button" value="<?php echo $t_button_title ?>" />
	</form>
	<?php print_hr() ?>
</div>
<?php
	}
?>
<?php html_page_bottom1( __FILE__ ) ?>
