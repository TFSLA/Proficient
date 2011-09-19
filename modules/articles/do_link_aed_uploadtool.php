<?php

	if($error == 0)
	{
		$article_id = 0;
		$article_project = $_POST['article_project'];
		$article_task = $_POST['article_task'];
		$article_section = $_POST['article_section'];
		$article_category = $_POST['article_category'];
		$notify_type = $_POST['notify_type'];
		$title = $_POST['title'];
		$href = $_POST['href'];
		$abstract = $_POST['abstract'];
		$is_protected = $_POST['is_protected'];
		$is_private = $_POST['is_private'];

		if(!stristr($_POST['href'], 'http://') === FALSE) $_POST['href']=substr($_POST['href'], 7);
				
		if ($article_id == 0)
		{
		   $ts = time();
		   $date = date("Y-m-d H:i:s",$ts);
		   $date_modified = date("Y-m-d H:i:s",$ts);

		   $query = "INSERT INTO articles (articlesection_id, file_category, date, articles_reads, user_id, title, abstract, body, project, task, type,date_modified,is_protected, is_private)
		   VALUES (".$article_section.", ".$article_category." , '".$date."', 0, ".$AppUI->user_id.", '".$title."', '".$href."', '".$abstract."', ".$article_project.", ".$article_task.", '1', '".$date_modified."', ".$is_protected.", ".$is_private.")";
	   }

	   $sql = mysql_query($query);
	   $article_id = mysql_insert_id();
	   
	   $obj = new CArticle();
	   
		if($is_private == 0)
		{
			$obj->project = $article_project;
			$obj->task = $article_task;
			$obj->article_id = $article_id;
			$obj->notifyNewKnowledge($notify_type);
	   	}
	}

?>