<?php

	$category_spanish = $_POST ['category_spanish'];
	$category_english = $_POST ['category_english'];

	/* Traemos el toda la info de la categoria a modificar. */

	$mysql_query = "
	SELECT *
	FROM timexp_expenses_categories
	WHERE category_id = '".$_GET ['category_id']."'";

	$old_category = db_loadList ($mysql_query);

	/* Si la nueva categoria en ingles y español tienen nombres distintos a los nuevos ingresados. */

	if (($_POST ['category_spanish'] != $old_category [0] ['name_es']) && ($_POST ['category_english'] != $old_category [0] ['name_en']))
	{
		$mysql_query = "
		SELECT name_es
		FROM timexp_expenses_categories
		WHERE name_es = '".$_POST ['category_spanish']."'";

		$query_result = db_loadList ($mysql_query);

		if (count ($query_result) == 0)
		{
			$mysql_query = "
			SELECT name_es
			FROM timexp_expenses_categories
			WHERE name_en = '".$_POST ['category_english']."'";

			$query_result = db_loadList ($mysql_query);

			if (count ($query_result) == 0)
			{
				$mysql_update_command = "
				UPDATE timexp_expenses_categories
				SET name_es = '".$_POST ['category_spanish']."', name_en = '".$_POST ['category_english']."'
				WHERE category_id = '".$_GET ['category_id']."'";

				db_exec ($mysql_update_command);
			}
			else
				header ("location: index.php?m=files&a=addedit_category&err_msg=5");
		}
		else
			header ("location: index.php?m=files&a=addedit_category&err_msg=5");
	}

	/* Si mantenemos el nombre en español y modificamos el nombre en ingles*/

	if (($_POST ['category_spanish'] == $old_category [0] ['name_es']) && ($_POST ['category_english'] != $old_category [0] ['name_en']))
	{
			echo "entre a 2";

			$mysql_query = "
			SELECT name_en
			FROM timexp_expenses_categories
			WHERE name_en = '".$_POST ['category_english']."'";

			$query_result = db_loadList ($mysql_query);

			count ($query_result);

			if (count ($query_result) == 0)
			{
				$mysql_update_command = "
				UPDATE timexp_expenses_categories
				SET name_en = '".$_POST ['category_english']."'
				WHERE category_id = '".$_GET ['category_id']."'";

				db_exec ($mysql_update_command);

				header ("location: index.php?m=files&a=addedit_category");
			}
			else
				header ("location: index.php?m=files&a=addedit_category&err_msg=5"); // El nombre en ingles que ingreso ya esta en uso.
	}

	/* Si mantenemos el nombre en ingles y modificamos el nombre en español. */

	if (($_POST ['category_english'] == $old_category [0] ['name_en']) && ($_POST ['category_spanish'] != $old_category [0] ['name_es']))
	{
			$mysql_query = "
			SELECT name_es
			FROM timexp_expenses_categories
			WHERE name_es = '".$_POST ['category_spanish']."'";

			$query_result = db_loadList ($mysql_query);

			if (count ($query_result) == 0)
			{
				$mysql_update_command = "
				UPDATE timexp_expenses_categories
				SET name_es = '".$_POST ['category_spanish']."'
				WHERE category_id = '".$_GET ['category_id']."'";

				db_exec ($mysql_update_command);

				header ("location: index.php?m=system&a=addedit_category");
			}
			else
				header ("location: index.php?m=system&a=addedit_category&err_msg=5"); // El nombre en español que ingreso ya esta en uso.
	}

	// Redireccionamos de nuevo a la pantalla anterior sin errores, la categoria se puo modificar.
	header ("location: index.php?m=system&a=addedit_expense_category&msg=2");

?>