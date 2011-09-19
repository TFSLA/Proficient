<?php

		/* Vector donde se almacenan las categorias que no se pueden eliminar. */

		$categories_not_erasable = array ();

		$j = 0;

		for ($i = 0; $i < $_POST ['categories_count']; $i++)
		{
			if (isset ($_POST ['checkbox_'.$i]))
				$j++;
		}

		echo "J vale : ", $j;

		if ($j == 0)
		{
			/*if ($AppUI->user_locale == "es")
				$err_msg = "Debe seleccionar por lo menos una categoria.";
			else
				$err_msg = "You must select at least one category.";*/

			//header ("location: index.php?m=files&a=addedit_category&err_msg=".$err_msg);
			header ("location: index.php?m=system&a=addedit_category&err_msg=3");
		}
		else
		{

		for ($i = 0; $i < $_POST ['categories_count']; $i++)
		{
				/* Verificamos que categoria fue tildada. */

				if (isset ($_POST ['checkbox_'.$i]))
				{
					/* Verificamos si para dicha categoria existe al menos un archivo. */

					if ($AppUI->user_locale == "es")
					{
						$sql_query_count_files =
						"SELECT file_id FROM files
						INNER JOIN files_category
							ON files_category.category_id = files.file_category
							WHERE files_category.name_es = '".$_POST ['checkbox_'.$i]."'";
					}
					else
					{
						$sql_query_count_files =
						"SELECT file_id FROM files
						INNER JOIN files_category
							ON files_category.category_id = files.file_category
							WHERE files_category.name_en = '".$_POST ['checkbox_'.$i]."'";
					}

					$count_files = db_loadList ($sql_query_count_files);

					/* ***********************************************************************
						 Si no hay archivos relacionados a dicha categoria, entonces se la puede
						 eliminar, caso contrario la almacenamos en el vector.
						 *********************************************************************** */

					if (count ($count_files) == 0)
					{

						/* ************************************************************************
							 Si tenemos el idioma en español, borramos el registro segun el nombre de
							 la categoria en español, sino lo hacemos segun su nombre en ingles.
							 ************************************************************************ */

						if ($AppUI->user_locale == "es")
						{
							db_delete ('files_category', 'name_es', $_POST ['checkbox_'.$i]);
							//db_delete ('files_category', 'name_es', $_POST ['category_'.$i]);
						}
						else
						{
							db_delete ('files_category', 'name_en', $_POST ['checkbox_'.$i]);
							//db_delete ('files_category', 'name_en', $_POST ['category_'.$i]);
						}
					}
					else
						array_push ($categories_not_erasable, $_POST ['checkbox_'.$i]);
			}
		}

		if (count ($categories_not_erasable) != 0)
		{
					/* ********************************************************************
						 Imprimimos un cartel segun el idioma que este utilizando el usuario.
						 ******************************************************************** */

					/*if ($AppUI->user_locale == "es")
						$err_msg = "Las siguientes categorias no se han podido eliminar ya que contienen uno o mas archivos.";
					else
						$err_msg = "The following categories couldn't be erased because one or more files associated are present.";*/

					/*foreach ($categories_not_erasable as $category)
							$err_msg .= $category. " \n";*/

					/*if ($AppUI->user_locale == "es")
						$language = "Las siguientes categorias no se han podido eliminar ya que contienen uno o mas archivos.";
					else
						$language = "The following categories couldn't be erased because one or more files associated are present.";

						echo "<br>";

					for ($i = 0; $i < count ($categories_not_erasable); $i++)
					{
						echo "\t".$categories_not_erasable [$i];
						echo "<br>";
					}*/

					//header ("location: index.php?m=files&a=addedit_category&err_msg=".$err_msg);
					header ("location: index.php?m=system&a=addedit_category&err_msg=4");
		}
		else
			header ("location: index.php?m=system&a=addedit_category");
		}

?>

