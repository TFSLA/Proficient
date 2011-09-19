<?php

	/* *************************************************************
		 Muestra todas las categorias de archivos que hay disponibles.
		 Autor : Lucas Liendo.
		 ************************************************************* */

	/* ***************************************************************************
		 Si el idioma del usuario es español, traemos los registros en dicho idioma.
	   Caso contrario hacemos lo mismo para el caso que el idioma sea ingles.
	   *************************************************************************** */

	function display_error ($err_msg)
	{
		echo "
		<script language='JavaScript'>
			window.alert (\"".$err_msg."\");
		</script>";
	}

	$lang = $AppUI->user_locale;
	
	$sql_query_categories =
		"SELECT category_id, name_es, name_en
		FROM files_category ORDER BY name_es";

	/* Ejecutamos la consulta y traemos los registros. */

	$categories = db_loadList ($sql_query_categories);

	/* Creamos el titulo principal. */

	$titleBlock = new CTitleBlock ($AppUI->_('Document Categories'), 'files.gif', $m, "$m.$a");

	if ($_POST ['mostrar_archivos_borrados'] == 'on')
		$check = 'checked';

	$tblForm = "\n<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";
	$tblForm .= "\n<tr>";
	$tblForm .= "$form</tr></table>";

	$titleBlock->addCell ($tblForm);

	/* Agregamos los dos botones que permiten agregar o eliminar categorias. */

	/* Mostramos la barra. */

	$titleBlock->show ();

	/* Si esta a YES, entonces el usuario hizo clic para modificar una categoria. Mostramos en la barra
	la accion de modificacion y el boton cancelar. En caso que el usuario haga clic en cancelar entonces
	volvemos y mostramos la pagina original. */

		if ($_GET ['modify'] == "yes")
	{
		echo "<table align='center' cellspacing='7' class='tableForm_bg' width='100%'>";

			$mysql_query = "
			SELECT category_id
			FROM files_category
			WHERE name_es = '".$_GET ['name_es']."'
				AND name_en = '".$_GET ['name_en']."'";

			$result = db_loadList ($mysql_query);

			echo "<form action='?m=system&a=do_modify_category&category_id=".$result [0] ['category_id']."' method='post'>";
				echo "<tr>";
	}
	else
	{
		echo "<table align='center' cellspacing='7' class='tableForm_bg' width='100%'>";
			echo "<form action='?m=system&a=do_add_category' method='post'>";
				echo "<tr>";
	}
	
	if ($lang == "es")
	{
		echo "<td align='left' nowrap='nowrap'>";
			echo $AppUI->_('Modify category (spanish) : ');
			echo "&nbsp;&nbsp;&nbsp;";
			echo "<input type='input' class='text' name='category_spanish' value='".$_GET ['name_es']."'>";
			echo "&nbsp;&nbsp;&nbsp;";

			echo $AppUI->_('Modify category (english) : ');
			echo "&nbsp;&nbsp;&nbsp;";
			echo "<input type='input' class='text' name='category_english' value='".$_GET ['name_en']."'>";
			echo "&nbsp;&nbsp;&nbsp;";
		echo "</td>";
	}
	else
	{
		echo "<td align='left'>";
			echo $AppUI->_('Modify category (english) : ');
			echo "&nbsp;&nbsp;&nbsp;";
			echo "<input type='input' class='text' name='category_english' value='".$_GET ['name_en']."'>";
			echo "&nbsp;&nbsp;&nbsp;";

			echo $AppUI->_('Modify category (spanish) : ');
			echo "&nbsp;&nbsp;&nbsp;";
			echo "<input type='input' class='text' name='category_spanish' value='".$_GET ['name_es']."'>";
			echo "&nbsp;&nbsp;&nbsp;";
		echo "</td>";
	}
	
	if ($_GET ['modify'] == "yes")
	{
					echo "<td align='right'>";
						echo "<input type='submit' class='buttonbig' name='do_modify_category' value='".$AppUI->_('Modify Category')."'>";
						echo "</form>";
					echo "<td>";

					echo "<td align='right'>";
						echo "<form action='?m=system&a=addedit_category&modify=no' method='post'>";
							echo "<input type='submit' class='button' name='add_category' value='".$AppUI->_('Cancel')."'>";
						echo "</form>";
					echo "</td>";
				echo "</tr>";
		echo "</table>";
	}
	else
	{
					echo "<td align='right'>";
						echo "<input type='submit' class='buttontitle' name='add_category' value='".$AppUI->_('Add Category')."'>";
					echo "<td>";
				echo "</tr>";
			echo "</form>";
		echo "</table>";
	}

	echo "<form name='addedit_category' method='post' action='index.php?m=system&a=do_delete_category'>";

		/* Construimos la tabla que contiene todas las categorias. Por cada fila agregamos
			 un checkbox (utilizado para marcar aquellas categorias que queremos eliminar), un icono
			 de modificar (que permite cambiar el nombre de la categoria seleccionada) y por ultimo
			 agregamos el nombre de la categoria. */

		echo "<table width='100%' cellpadding='2' cellspacing='0'>";

			/* Armamos la cabecera de la tabla. */

			echo "<tr class='tableHeaderGral'>";
				echo "<td colspan='2' width='2%'>".$AppUI->_('Category')."</td>";
				echo "<td width='98%'>&nbsp</td>";
			echo "</tr>";

			/* Imprimimos todas las categorias en la tabla. */

			$i = 0;

			foreach ($categories as $row_category)
			{
				echo "<tr>";
					echo "<td>";
						echo "<input name='".checkbox_.$i."' type='checkbox' ".($row_category ['category_id'] == -1 ? 'disabled' : '')." value='".$row_category ["name_$lang"]."'/>";
					echo "</td>";

					echo "<td>";
						if($row_category ['category_id']  > 0)
						{
							echo "<a href='./index.php?m=system&a=addedit_category&modify=yes&name_en=".$row_category ['name_en']."&name_es=".$row_category ['name_es']."'> <img src='./images/icons/edit_small.gif' border='0' /> </a>";
						}
					echo "</td>";

					echo "<td>";
						if ($AppUI->user_locale == "es")
							echo $row_category ['name_es']." / ".$row_category ['name_en'];
						else
							echo $row_category ['name_en']." / ".$row_category ['name_es'];
					echo "</td>";
				echo "</tr>";

				$i++;
			}

		echo "</table>";

		echo "<table align='right'>";
			echo "<tr>";
				echo "<td>";
					echo "<input type='submit' class='buttonbig' name='add_category' value='".$AppUI->_('Delete Category')."'>";
				echo "</td>";
			echo "</tr>";
		echo "</table>";

		/* Almacenamos en un input de tipo hidden la cantidad de categorias que se estan mostrando.
		Esto luego lo utilizara do_addedit_category.php para recorrer los checkbox y comprobar
		su estado. */

		echo "<input type='hidden' name='categories_count' value='".count ($categories)."' />";
	echo "</form>";

	/* Chequeamos la variable err_msg. Si existe entonces la eliminacion o creacion de una
	categoria falló. Mostramos el mensaje de error en un POPUP. */

	if (isset ($_GET ['err_msg']))
	{

		switch ($_GET ['err_msg'])
		{
			/* El usuario quiso agregar una categoria y falta algo de los 2 inputs,
			el input en español o el input en ingles*/

			case 1 :
				if ($AppUI->user_locale == "es")
					display_error ("Debe especificar la categoría en español e inglés.");
				else
					display_error ("You must enter the category in spanish and english.");

				break;

			/* El usuario quiso agregar una categoria que ya existe. */

			case 2 :
				if ($AppUI->user_locale == "es")
					display_error ("La categoría que esta intentando crear ya existe.");
				else
					display_error ("The specified category already exists.");

				break;

			/* El usuario quiso eliminar una categoria pero no selecciono ninguna. */

			case 3 :
				if ($AppUI->user_locale == "es")
					display_error ("Debe seleccionar por lo menos una categoría.");
				else
					display_error ("You must select at least one category.");

				break;

			/* El usuario quiso eliminar una categoria que contiene al menos un archivo. */

			case 4 :
				if ($AppUI->user_locale == "es")
					display_error ("Algunas categorías no se han podido eliminar ya que contienen uno o mas archivos.");
				else
					display_error ("Some categories couldn't be deleted. They have one or more associated files.");

				break;

			case 5 :
				if ($AppUI->user_locale == "es")
					display_error ("La categoría que intenta modificar se encuentra duplicada.");
				else
					display_error ("The category you are trying to modify already exists.");

				break;

		}
		//display_error ($_GET ['err_msg']);
	}

?>