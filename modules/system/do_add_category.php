<?php

	$category_english = $_POST ['category_english'];
	$category_spanish = $_POST ['category_spanish'];

	if ((strlen ($category_english) == 0) || (strlen ($category_spanish) == 0))
	{
			/*if (strlen ($category_english) == 0)
			{
				$err_msg = "You must enter a category (english)";
				$err_msg .= " ";
			}

			if (strlen ($category_spanish) == 0)
				$err_msg .= "You must enter a category (spanish)";*/

			//header ("location: index.php?m=files&a=addedit_category&err_msg=".$err_msg);
			header ("location: index.php?m=system&a=addedit_category&err_msg=1");
	}
	else
	{

		$sql_query_category_english =
			"SELECT name_en
			FROM files_category
			WHERE name_en = '".$category_english."'";

		$sql_query_category_spanish =
			"SELECT name_es
			FROM files_category
			WHERE name_es = '".$category_spanish."'";

		$query_category_english = db_loadList ($sql_query_category_english);
		$query_category_spanish = db_loadList ($sql_query_category_spanish);
		$err_msg = "";

		if ((count ($query_category_english) != 0) || (count ($query_category_spanish) != 0))
		{
			/*if (count ($query_category_english) != 0)
			{
				$err_msg = "The file category : ".$category_english." is already in use.";
				$err_msg .= " ";
			}

			if (count ($query_category_spanish) != 0)
				$err_msg .= "The file category : ".$category_spanish." is already in use.";*/

			//header ("location: index.php?m=files&a=addedit_category&err_msg=".$err_msg);
			header ("location: index.php?m=system&a=addedit_category&err_msg=2");
		}
		else
		{
			$sql_insert_new_file_category =
				"INSERT INTO files_category
				VALUES ('', '".$category_spanish."', '".$category_english."')";

			db_exec ($sql_insert_new_file_category);

			header ("location: index.php?m=system&a=addedit_category");
		}
	}

?>