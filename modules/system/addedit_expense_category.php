<?php
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
		FROM timexp_expenses_categories
		WHERE active = 1
		ORDER BY name_$lang";

	$categories = db_loadList ($sql_query_categories);

	$titleBlock = new CTitleBlock ($AppUI->_('Expense Categories'), 'files.gif', $m, "$m.$a");

	if ($_POST ['mostrar_archivos_borrados'] == 'on')
		$check = 'checked';

	$tblForm = "\n<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";
	$tblForm .= "\n<tr>";
	$tblForm .= "$form</tr></table>";

	$titleBlock->addCell ($tblForm);

	$titleBlock->show ();

	if ($_GET ['modify'] == "yes")
	{
		echo "<table align='center' cellspacing='7' class='tableForm_bg' width='100%'>";

			$category_id = $_GET["category_id"];
			echo "<form action='?m=system&a=do_modify_expense_category&category_id=".$category_id."' method='post'>";
				echo "<tr>";
	}
	else
	{
		echo "<table align='center' cellspacing='7' class='tableForm_bg' width='100%'>";
			echo "<form action='?m=system&a=do_add_expense_category' method='post'>";
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
						echo "<input type='submit' class='buttonbig' name='do_modify_expense_category' value='".$AppUI->_('Modify Category')."'>";
						echo "</form>";
					echo "<td>";

					echo "<td align='right'>";
						echo "<form action='?m=system&a=addedit_expense_category&modify=no' method='post'>";
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

	echo "<form name='addedit_category' method='post' action='index.php?m=system&a=do_delete_expense_category'>";
		echo "<table width='100%' cellpadding='2' cellspacing='0'>";
			echo "<tr class='tableHeaderGral'>";
				echo "<td colspan='2' width='2%'>".$AppUI->_('Category')."</td>";
				echo "<td width='98%'>&nbsp</td>";
			echo "</tr>";
			$i = 0;

			foreach ($categories as $row_category)
			{
				echo "<tr>";
					echo "<td>";
						echo "<input name='".checkbox_.$i."' type='checkbox' ".($row_category ['category_id'] == -1 ? 'disabled' : '')." value='".$row_category ["category_id"]."'/>";
						echo "</td>";
					echo "<td>";
						if($row_category ['category_id']  > 0)
						{
							echo "<a href='./index.php?m=system&a=addedit_expense_category&modify=yes&name_en=".$row_category ['name_en']."&name_es=".$row_category ['name_es']."&category_id=".$row_category ['category_id']."'>
								<img src='./images/icons/edit_small.gif' border='0' /> </a>";
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
	}
	
	if(isset($_GET["msg"]))
	{
		switch($_GET["msg"])
		{
				case 1 :
				if ($AppUI->user_locale == "es")
					display_error ("Categoría guardada");
				else
					display_error ("Category saved");
				break;
				
				case 2 :
				if ($AppUI->user_locale == "es")
					display_error ("Categoría modificada");
				else
					display_error ("Category modified");
				break;
				
				case 3 :
				if ($AppUI->user_locale == "es")
					display_error ("Categoría eliminada");
				else
					display_error ("Category deleted");
				break;
		}
	}
?>