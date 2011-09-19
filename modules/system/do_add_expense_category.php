<?php

	$category_english = $_POST ['category_english'];
	$category_spanish = $_POST ['category_spanish'];

	if ((strlen ($category_english) == 0) || (strlen ($category_spanish) == 0))
	{
			header ("location: index.php?m=system&a=addedit_expense_category&err_msg=1");
	}
	else
	{

		$sql_query_category_english =
			"SELECT name_en
			FROM timexp_expenses_categories
			WHERE name_en = '".$category_english."'
			AND active = 1";

		$sql_query_category_spanish =
			"SELECT name_es
			FROM timexp_expenses_categories
			WHERE name_es = '".$category_spanish."'
			AND active = 1";

		$query_category_english = db_loadList ($sql_query_category_english);
		$query_category_spanish = db_loadList ($sql_query_category_spanish);
		$err_msg = "";

		if ((count ($query_category_english) != 0) || (count ($query_category_spanish) != 0))
		{
			header ("location: index.php?m=system&a=addedit_expense_category&err_msg=2");
		}
		else
		{
			$sql_insert_new_expense_category =
				"INSERT INTO timexp_expenses_categories (name_es, name_en)
				VALUES ('".$category_spanish."', '".$category_english."')";

			db_exec ($sql_insert_new_expense_category);

			header ("location: index.php?m=system&a=addedit_expense_category&msg=1");
		}
	}

?>