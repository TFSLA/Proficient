<?php

	$j = 0;

	for ($i = 0; $i < $_POST ['categories_count']; $i++)
	{
		if (isset ($_POST ['checkbox_'.$i]))
		{
			$j++;
		}
	}
		
	if (empty($j))
	{
		header ("location: index.php?m=system&a=addedit_category&err_msg=3");
	}
	else
	{
		for ($i = 0; $i < $_POST ['categories_count']; $i++)
		{
			/* Verificamos que categoria fue tildada. */
			if (isset ($_POST ['checkbox_'.$i]))
			{
				$mysql_update_command = "
					UPDATE timexp_expenses_categories
					SET active = 0
					WHERE category_id = '".$_POST['checkbox_'.$i]."'";
				db_exec ($mysql_update_command);
			}
		}
		header ("location: index.php?m=system&a=addedit_expense_category&msg=3");
	}
?>

