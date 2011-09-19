<?php
$del = isset($_REQUEST['del']) ? $_REQUEST['del'] : 0;

$obj = new CArticle();
if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

//echo "<pre>";print_r($obj);echo "</pre>";die;
//echo "<pre>";print_r($_POST);echo "</pre>";die;

$majorUpdate = false;

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Article' );
if ($del) {
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$obj->saveLog(0,1);
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect();
	}
} else {
	if($obj->is_protected=='on'){
		$obj->is_protected=1;
	}else {
		$obj->is_protected=0;
	}

	if($obj->is_private=='on'){
		$obj->is_private=1;
	}else {
		$obj->is_private=0;
	}

	$isNotNew = @$_REQUEST['article_id'];
	if (!$isNotNew) {
		$obj->user_id = $AppUI->user_id;
		$obj->articles_reads = 0;
		$obj->date = date("Y-m-d H:i:s");
	}else{
		$actualArticle = new CArticle();
		$actualArticle->load($obj->article_id);

		if($obj->body != $actualArticle->body)
		{
			$majorUpdate = true;
			$obj->saveLog(0,2);//major update
		}
		else
			$obj->saveLog(0,5);//minor update
	}
	    $obj->date_modified = date("Y-m-d H:i:s");

	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {

		if($majorUpdate)
		{
			if(!$isNotNew && $obj->is_private!=1){
				$sql = "SELECT MAX(article_id) AS art_id FROM articles";
				$id_data = mysql_fetch_array(mysql_query($sql));
				$article_id = $id_data['art_id'];

				$obj->project = $_POST['project'];
				$obj->article_id = $article_id;
				$obj->notifyNewKnowledge($_POST['notify_type']);
			}elseif($obj->is_private!=1){
				$obj->project = $_POST['project'];
				$obj->notifyNewKnowledge($_POST['notify_type'],true);  //notificación de Doc. actualizado
			}
		}
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}

	if($_POST["bug_id"] !="")
	{
	   $bug_c = strlen($_POST["bug_id"] );
	   $url_bug = str_repeat('0',7-$bug_c).$_POST["bug_id"] ;
	   $redirect = "m=webtracking&a=bug_view_page&bug_id=$url_bug";

	   $up_art = "UPDATE articles SET bug_id='".$_POST["bug_id"]."' WHERE  article_id='".$obj->article_id."' ";
               $sql_v = db_exec($up_art);

	   $query_art = "SELECT count(kb_item) FROM btpsa_bug_kb WHERE bug_id='".$_POST["bug_id"]."' AND kb_type='0' AND  project_id='".$obj->project."' ";
	   $exist_art =  db_loadResult( $query_art );

	   if($exist_art==0){
		   # Creado el articulo lo relaciono con la incidencia
		   $query_v = "INSERT INTO btpsa_bug_kb (project_id,
							    bug_id,
							    kb_type,
							    kb_section,
							    kb_item)
					              VALUES ('".$obj->project."',
							    '".$_POST["bug_id"]."',
							    '0',
							    '".$_POST['articlesection_id']."',
							    '".$obj->article_id."')";
		   echo "<pre>".$query_v."</pre>";
		   $sql_v = db_exec($query_v);
	   }
	   echo "<br>redirect: $redirect";
	}else{
	   $redirect = $AppUI->state['SAVEDPLACE'];
	}

	$AppUI->redirect($redirect);
}
?>