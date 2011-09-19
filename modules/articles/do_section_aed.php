<?php /* ADMIN $Id: do_section_aed.php,v 1.1 2009-05-19 21:15:41 pkerestezachi Exp $ */

$del = isset($_REQUEST['del']) ? $_REQUEST['del'] : 0;

$obj = new CSection();
if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Section' );
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect();
	}
} else {
     
	 if ($_POST[articlesection_id]==""){
       
	   $sql = mysql_query("Insert into articlesections ( name, description, articlesection_email) values('".$_POST[name]."','".$_POST[description]."', '".$_POST[articlesection_email]."');");
       $id_new = mysql_insert_id();

	   $proj_a = explode (",", $_POST[asign_project]);

	   foreach($proj_a as $pr_a){
         
		   if ($pr_a < 0)
			 {
				  $com = -1 * $pr_a;

				  $query = "Insert into articlesections_projects ( articlesection_id, company_id, project_id) values('".$id_new."','".$com."','-1')";
				  if($pr_a!=""){
				  $sql_insert = mysql_query($query);
				  }
			 }
			 else{
				   $tmp_q = mysql_query("select project_company from projects where project_id='$pr_a' ");
				   $com = mysql_fetch_array($tmp_q);
				   $cia = $com[project_company];

				   $query =  "Insert into articlesections_projects ( articlesection_id, company_id, project_id) values('".$id_new."','".$cia."','".$pr_a."')";
				   if($pr_a!=""){
				   $sql_insert = mysql_query($query);
				   }
			 }
	   }

       $AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );

	 }
	 else{
     
	 $sql = mysql_query("UPDATE articlesections SET name = '".$_POST[name]."',description = '".$_POST[description]."', articlesection_email = '".$_POST[articlesection_email]."' WHERE articlesection_id = '".$_POST[articlesection_id]."' ");
     
	 $sql_s = mysql_query("DELETE FROM articlesections_projects WHERE articlesection_id = '".$_POST[articlesection_id]."'");
	 $proj_a = explode (",", $_POST[asign_project]);

	   foreach($proj_a as $pr_a){
         
		   if ($pr_a < 0)
			 {
				  $com = -1 * $pr_a;

				  $query = "Insert into articlesections_projects ( articlesection_id, company_id, project_id) values('".$_POST[articlesection_id]."','".$com."','-1')";
				  if($pr_a!=""){
				  $sql_insert = mysql_query($query);
				  }
			 }
			 else{
				   $tmp_q = mysql_query("select project_company from projects where project_id='$pr_a' ");
				   $com = mysql_fetch_array($tmp_q);
				   $cia = $com[project_company];

				   $query =  "Insert into articlesections_projects ( articlesection_id, company_id, project_id) values('".$_POST[articlesection_id]."','".$cia."','".$pr_a."')";
				   if($pr_a!=""){
				   $sql_insert = mysql_query($query);
				   }
			 }
	   }

	   $AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );

	 }

    unset($AppUI->companies_o);	 
    unset($AppUI->companies_d);	 
    unset($AppUI->project_o);	 
    unset($AppUI->project_d);	 
     
	/*if (($msg = $obj->store())) {
		
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}*/
	$AppUI->redirect();
}
?>