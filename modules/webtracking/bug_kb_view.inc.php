<?php
           // echo "<pre>"; print_r($_POST); echo "</pre>";
	# Este script permite relacionar articulos, enlaces o archivos de la base de conocimientos a una incidencia.
	# $f_bug_id must be set and be set to the bug id

	# Me fijo si el usuario tiene permisos para la base de conocimientos o si has secciones relacionadas a este proyecto.

	$canEdit_kb = !getDenyEdit( 'articles' );

	if($canEdit_kb)
	{
?>
<br>
           <form name="editFrmKb" method="post" action="index.php?m=webtracking&a=bug_kb_addedit">
                <input type="hidden" name="project_id" value="<?=$t_bug->project_id?>">
                <input type="hidden"  name="orig" value="<?=$orig?>">
	    <table class="width100" cellspacing="1">
	        <tr>
	              <td class="form-title" colspan="2">
		     <?php echo lang_get( 'knowledge_base' ) ?>
	              </td>
                   </tr>
                   <tr class="row-1">
	           <td class="category" width="15%">
		<?php echo lang_get( 'select_item_kb' ) ?><br />
	           </td>
	           <td width="85%">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />

		<!--  Tipo -  Seccion - Titulo  -->
		<select name="kb_type" id="kb_type"  onchange="xajax_kb_type_section(document.editFrmKb.kb_type.value, document.editFrmKb.kb_section.value,'');" style="width: 160px;" >
		     <option value="0"  ><?php echo lang_get( 'articles' ) ?></option>
		     <option value="2"  ><?php echo lang_get( 'files' ) ?></option>
		     <option value="1"  ><?php echo lang_get( 'links' ) ?></option>
		</select>

		<select name="kb_section" id="kb_section" onchange="xajax_kb_type_section( document.editFrmKb.kb_type.value, document.editFrmKb.kb_section.value,'' );"; style="width: 160px; ">
		       <option value="-1">Top</option>
		       <?
		               $query = "SELECT articlesection_id, name FROM articlesections order by name";
		               $sql =  db_query($query);

		               while($result = db_fetch_array( $sql ))
		               {
		                   echo "<option value=\"".$result['articlesection_id']."\" >".$result['name']."</option>";
		               }
		       ?>
		  </select>

		  <select name="kb_item" id="kb_item" style="width: 260px; "></select>

		<input type="submit" class="button" value="<?php echo lang_get( 'relation_item' ) ?>" />
	           </td>
                   </tr>
	    </table>

	    </form>
	    <script language="javascript">
	        xajax_kb_type_section( document.editFrmKb.kb_type.value, document.editFrmKb.kb_section.value,'' );
	    </script>
<?
	}
?>