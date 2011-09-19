<?php
require_once( 'core.php' );

$t_core_path = config_get( 'core_path' );

require_once( $t_core_path.'string_api.php' );
require_once( $t_core_path.'file_api.php' );
require_once( $t_core_path.'bug_api.php' );
require_once( $t_core_path.'custom_field_api.php' );

// Script para vincular un item de la base de conocimientos a una incidencia

//echo "<pre>"; print_r($_POST); echo "</pre>";

// Me fijo que ya no este relacionada
$query = "SELECT count(id)
                FROM btpsa_bug_kb
                WHERE bug_id='".$_POST['bug_id']."' AND  kb_type='".$_POST['kb_type']."' AND kb_item='".$_POST['kb_item']."'
                ";
$result = db_query($query);
$cant = db_result($result);

if ($cant ==0){
      $query_insert = "INSERT INTO btpsa_bug_kb (project_id, bug_id,kb_type,kb_section,kb_item) VALUES ('".$_POST['project_id']."','".$_POST['bug_id']."','".$_POST['kb_type']."', '".$_POST['kb_section']."', '".$_POST['kb_item']."') ";
     // echo "<pre>$query_insert</pre>";

      if(db_query( $query_insert )){
      	//echo "Se guardo sin errorres";
      }

}else{
     $AppUI->setMsg( " ".lang_get( 'kb_item_exist' ), UI_MSG_ERROR );
}

$bug_c = strlen($_POST['bug_id']);
$url_bug = str_repeat('0',7-$bug_c).$_POST['bug_id'];

//echo "<pre>"; print_r($AppUI->state['SAVEDPLACE']); echo "</pre>";

if($_POST['orig']=="resolve"){
?>
          <form name="editFrm" method="post" action="index.php?m=webtracking&a=bug_resolve_page">
                <input type="hidden"  name="bug_id" value="<?=$_POST['bug_id']?>">
          </form>

         <script language="javascript"><!--
	   document.editFrm.submit();
         //--></script>

<?
}else{
        $AppUI->redirect($AppUI->state['SAVEDPLACE']);
}
?>