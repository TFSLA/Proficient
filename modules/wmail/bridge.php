<?

if($xa!="view") include_once("./modules/wmail/include/header.inc.php");
$user=$session;
include("./modules/wmail/include/session_auth.inc");

if($xa=="view") include_once("./modules/wmail/view.php");
else  include_once("./modules/wmail/include/optionfolders.inc.php");

?>
