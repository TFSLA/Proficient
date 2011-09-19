<?php
require_once( 'core.php' );

$t_core_path = config_get( 'core_path' );

require_once( $t_core_path.'string_api.php' );
require_once( $t_core_path.'file_api.php' );
require_once( $t_core_path.'bug_api.php' );
require_once( $t_core_path.'custom_field_api.php' );

// Script para vincular un item de la base de conocimientos a una incidencia

//echo "<pre>"; print_r($_GET); echo "</pre>";

$query = "DELETE FROM btpsa_bug_kb WHERE id='".$_GET['id_kb']."' ";
$result =  db_query($query);

$bug_c = strlen($_GET['bug_id']);
$url_bug = str_repeat('0',7-$bug_c).$_GET['bug_id'];


if($_GET['orig']=="resolve"){
?>
          <form name="editFrm" method="post" action="index.php?m=webtracking&a=bug_resolve_page">
                <input type="hidden"  name="bug_id" value="<?=$_GET['bug_id']?>">
          </form>

         <script language="javascript"><!--
	   document.editFrm.submit();
         //--></script>

<?
}else{
        $AppUI->redirect($AppUI->state['SAVEDPLACE']);
}
?>