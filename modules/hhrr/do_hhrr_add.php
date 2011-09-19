<?php /* DEPARTMENTS $Id: do_hhrr_add.php,v 1.1 2009-05-19 21:15:44 pkerestezachi Exp $ */
$hhrr_portal = isset($_POST['new_candidate']) ? $_POST['new_candidate'] : false;
$del = isset($_POST['del']) ? $_POST['del'] : 0;

// desde el portal de candidatos prohibo el borrado de usuarios
if ($hhrr_portal) $del = 0;

$upload_dir = $AppUI->getConfig('hhrr_uploads_dir');

echo $username;
?>