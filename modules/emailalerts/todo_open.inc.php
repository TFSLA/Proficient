<?  
require_once("getadminprojects.inc.php");
if($params=="") $params = "7";
$prjs = getAdminProjects($user_id);
if (count($prjs)) {

$sql = "
SELECT projects.project_id as projectid, project_todo.*, projects.project_name as project, DATE_FORMAT(due_date,'%d/%m/%Y') as due_datef, DATE_FORMAT(date,'%d/%m/%Y') as datef, users.user_first_name as ofn,  users.user_last_name as oln, usersb.user_first_name as afn, usersb.user_last_name as aln
FROM project_todo, projects, users, users as usersb
WHERE projects.project_id = project_todo.project_id
AND project_todo.status <> '1'
AND project_todo.user_owner = '$user_id'
AND users.user_id  = project_todo.user_owner
AND usersb.user_id = project_todo.user_assigned
AND projects.project_id  IN (" . implode( ',', $prjs ) . ") 
ORDER BY projects.project_id, project_todo.date 
";
$lastproject=-1;
$rows = db_loadList( $sql, NULL );
if (count( $rows)) {
  $subject   = "To Do - Abiertos.";
  $message   = "";
  foreach ($rows as $a) {
        if($lastproject != $a["projectid"]){
	   if($lastproject != -1) $xml.= "</project>";
	   $lastproject = $a["projectid"];
           $xml.= "<project>";
           $xml.= "<name>".$a["project"]."</name>";
        }
        $priority="";
	if($langpref=="en"){
        	if($a["priority"]==1) $priority="High";
		else if($a["priority"]==2) $priority="Normal";
		else if($a["priority"]==3) $priority="Low";
	}
	else{
        	if($a["priority"]==1) $priority="Alta";
		else if($a["priority"]==2) $priority="Normal";
		else if($a["priority"]==3) $priority="Baja";
	}
	$xml.= "<todo>";
	$xml.= "<date><![CDATA[".$a["datef"]."]]></date>";
	$xml.= "<duedate><![CDATA[".$a["due_datef"]."]]></duedate>";
	$xml.= "<priority><![CDATA[".$priority."]]></priority>";
	$xml.= "<ownerfname><![CDATA[".$a["ofn"]."]]></ownerfname>";
	$xml.= "<ownerlname><![CDATA[".$a["oln"]."]]></ownerlname>";
	$xml.= "<assignedfname><![CDATA[".$a["afn"]."]]></assignedfname>";
	$xml.= "<assignedlname><![CDATA[".$a["aln"]."]]></assignedlname>";
	$xml.= "<project><![CDATA[".$a["project"]."]]></project>";
	$xml.= "<description><![CDATA[".$a["description"]."]]></description>";
	$xml.= "</todo>";
  }
  $xml.= "</project>";
}
  if($xml!=""){
    $xml= "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><projects>".$xml."</projects>";
    $subject   = "To Do - Abiertos.";
    $message   = "";
  }

}
?>
