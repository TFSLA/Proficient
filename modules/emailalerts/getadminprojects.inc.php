<?php
function getAdminProjects($uid){
  return db_loadColumn("select project_id from project_owners where project_owner = $uid");
}
?>
