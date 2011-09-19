<?php /* CALENDAR $Id: do_event_aed.php,v 1.1 2009-05-19 21:15:42 pkerestezachi Exp $ */
require_once( $AppUI->getModuleClass ('projects' ) );
include ('./functions/delegates_func.php');
 
$delegator_id = dPgetParam( $_POST, "delegator_id", $AppUI->user_id );
$mod_id = 4;
$dialog = dPgetParam( $_GET, "dialog", $AppUI->user_id != $delegator_id );
$obj = new CEvent;

$del = dPgetParam( $_POST, 'del', 0 );
 
echo "<pre>";
    print_r($_POST);
 echo "</pre>";
 
 
if($del)
{
	if (!$obj->load( $_POST['event_id'] )){
		$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
		$AppUI->redirect();
	}	
}
else
{
// bind the POST parameter to the object record
if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();	
}
}

if ( $delegator_id != $AppUI->user_id )
{
	require_once( $AppUI->getModuleClass( "admin" ) );	
	$usr = new CUser();
	$usr->load( $AppUI->user_id );
	if ( !$usr->isDelegator( $delegator_id, $mod_id ) && $AppUI->user_type != 1 )
	{
		$AppUI->setMsg("Delegator");
		$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
		$AppUI->redirect( "m=public&a=access_denied" );
	}
	$usr->load( $delegator_id );
	$permiso = $usr->getDelegatePermission( $AppUI->user_id, $mod_id );
	$canEdit = $obj->event_creator == $AppUI->user_id && $obj->event_owner == $delegator_id && $permiso == "AUTHOR";
	$canEdit = $canEdit || $obj->event_owner == $delegator_id && $permiso == "EDITOR";
	$canEdit = $canEdit || $AppUI->user_type == 1;
	$canAdd = $permiso == "AUTHOR" || $permiso == "EDITOR" || $AppUI->user_type == 1;
	if ($del!=0)do_log($delegator_id, $mod_id, $AppUI, 2);
}
else
{
	$canAdd = $canEdit;
}

if ( $obj->event_owner != $AppUI->user_id && $AppUI->user_type != 1)
	$AppUI->redirect( "m=public&a=access_denied" );

$redir = "";
$msg = '';

// configure the date and times to insert into the db table
if ($obj->event_start_date) 
{
	$date = new CDate( $obj->event_start_date.$_POST['start_time'] );
	$obj->event_start_date = $date->format( FMT_DATETIME_MYSQL );
}
if ($obj->event_end_date) 
{
	$date = new CDate( $obj->event_end_date.$_POST['end_time'] );
	$obj->event_end_date = $date->format( FMT_DATETIME_MYSQL );
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Event' );
if ($del) {
	if (!$obj->canDelete( $msg )) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
	}

	$fromproject = dPgetParam( $_POST, 'fromproject', 0 );
	$projectId = dPgetParam( $_POST, 'project_id', 0 );
	$fromtask = dPgetParam( $_POST, 'fromtask', 0 );
	$taskid = dPgetParam( $_POST, 'task_id', 0 );
	$frompipeline = dPgetParam( $_POST, 'frompipeline', 0 );
	$pipelineId = dPgetParam( $_POST, 'lead_id', 0 );
	
	
	
	if($fromproject == 1)
		$AppUI->redirect( "m=projects&a=view&project_id=".$projectId);
	else if($fromtask == 1)
		$AppUI->redirect( "m=tasks&a=view&task_id=".$taskid);
	else if($frompipeline == 1)
		$AppUI->redirect( "m=pipeline&a=addedit&lead_id=".$pipelineId."&delegator_id=".$delegator_id."&dialog=".$dialog );
	else
		$AppUI->redirect( "m=calendar&delegator_id=".$delegator_id."&dialog=".$dialog );		
}
else 
{	
	//var_dump($obj);
	$canAdd = $canAdd || $delegator_id == $AppUI->user_id;
	
	$isNotNew = @$_POST['event_id'];
	$canEdit = 1;
	if (!$isNotNew) 
	{
		if ( !$canAdd )
		{
			$AppUI->redirect( "m=public&a=access_denied");
		}
		$obj->event_owner = $delegator_id;
		$obj->event_creator = $AppUI->user_id;
	}	
	if ( !$canEdit )
	{
		$AppUI->redirect( "m=public&a=access_denied");
	}
	if ( $obj->event_invitation_type != "PROJECT" )
	{		
		$obj->event_project = -1;
	}	
	if ( $obj->event_invitation_type != "COMPANY" )
	{		
		$obj->event_company = -1;
	}
	if ( $obj->event_recurse_type )
	{
		//Los eventos recursivos no tienen fecha de fin
		$obj->event_end_date = NULL;
        //recuperar la fecha para el evento recursivo

		$obj->event_start_date = $_POST["event_start_date_r"];
        
        if($obj->event_start_date != $_POST["event_end_date_r"]){
            $obj->event_end_date = $_POST["event_end_date_r"];
        }

        //if ($_POST["all_day"])
        if($obj->event_type == "3") 
         {
	 $obj->event_start_time ="00:00:00";
	 $obj->event_end_time ="23:59:59";
         }


		switch ( $_POST["event_recurs_end"] )
		{
			case "n":
				$obj->event_end_occurrence = "0000-00-00";		
				$obj->event_no_occurrences = -1;
				break;
			case "x":
				$obj->event_end_occurrence = "0000-00-00";
				break;
			case "d":
				$obj->event_no_occurrences = -1;
				break;			
		}
		switch ( $obj->event_recurse_type )
		{
			case "d":
				if ( $_POST["radio_daily"] == 1 )
				{
					$obj->event_recur_every_week_day = 1;
					$obj->event_recur_every_x_days = -1;
				}
				else
				{
					$obj->event_recur_every_week_day = 0;
					$obj->event_recur_every_x_days = $_POST["event_recur_every_x_days"];
				}
				break;
			case "w":
				$obj->event_recur_every_n_days = "";
				
				for ($i = 0; $i < 7; $i++ )
				{
					$obj->event_recur_every_n_days .= ( $_POST["event_recur_every_n_days_".$i] ? "1" : "0" ); 
				}
				break;
			case "m":
				if ( $_POST["radio_monthly"] == "dd" )
				{
					echo "<p>Mensual con dia fijo</p>";
					$obj->event_recur_every_dd_day = $_POST["m_event_recur_every_dd_day"];
					$obj->event_recur_every_n_day = -1;
					$obj->event_recur_every_nd_day = -1;
				}
				else
				{				
					echo "<p>Mensual con dia de la semana</p>";
					$obj->event_recur_every_dd_day = -1;
					$obj->event_recur_every_n_day = $_POST["m_event_recur_every_n_day"];
					$obj->event_recur_every_nd_day = $_POST["m_event_recur_every_nd_day"];
				}
				break;
			case "y":
				if ( $_POST["radio_yearly"] == "dd" )
				{
					$obj->event_recur_every_dd_day = $_POST["y_event_recur_every_dd_day"];
					$obj->event_recur_every_n_day = -1;
					$obj->event_recur_every_nd_day = -1;
				}
				else
				{
					$obj->event_recur_every_dd_day = -1;
					$obj->event_recur_every_n_day = $_POST["y_event_recur_every_n_day"];
					$obj->event_recur_every_nd_day = $_POST["y_event_recur_every_nd_day"];
				}
				break;
		}
	}
	else
	{
		$obj->event_recurse_type = NULL;
	}	
	var_dump($obj);

	if (($obj->event_type == "3")&&( !$obj->event_recurse_type ))
	{
		//$obj->event_type = "3";
		$obj->event_start_time ="00:00:00";
		$obj->event_end_time ="23:59:59";
        
		$obj->event_start_date = substr($obj->event_start_date,0,10)." 00:00:00";
		$obj->event_end_date = substr($obj->event_end_date,0,10)." 23:59:59";
	}
	
	if ($_POST["task_id"] != "0")
		$obj->event_task = $_POST["task_id"];
		
	if ($_POST["opportunities"] != "0")
		$obj->event_salepipeline = $_POST["opportunities"];		

    var_dump($obj);

	if (($msg = $obj->store(true))) 
	{
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	}
	else
	{
		if($isNotNew){
			$action = 4;
		}else{
			$action = 3;
		}
		do_log($delegator_id, $mod_id, $AppUI, $action);
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}

	if ( $obj->event_invitation_type == "PRIVATE" )
	{		
        $objEvent = new CEvent();
        $objEvent->load( $obj->event_id );
        
        if(isset($_POST['from_projects_tab']))
        	$suppressLogo = 1;        

        if(count($obj->getInvitations("user_last_name")) > 0){
            $redir = "m=calendar&a=inv_addedit&event_id=$obj->event_id&dialog=$dialog&delegator_id=$delegator_id";
        }else{
            $redir = "m=calendar&a=add_inv&event_id=$obj->event_id&dialog=$dialog&delegator_id=$delegator_id";
        }
	}

    echo "<br>";

	// mail del dueño del evento //
    $query = "select user_email, user_first_name, user_last_name from users where user_id ='$obj->event_owner'";
	$sql = mysql_query($query)or die(mysql_error());
    $host = mysql_fetch_array($sql);


	if ( $obj->event_invitation_type == "PROJECT" )
	{		 
		$objEvent = new CEvent();
        $objEvent->load( $obj->event_id );

        $idproject = $obj->event_project;
       
	    $prole = new CProjectRoles();
		$prjUsers = $prole->getAssignedUsers(2 ,$idproject);
        

		foreach($prjUsers as $pk => $pd){
   
			$query = "select user_email, user_first_name, user_last_name from users where user_id ='$pk'";
			$sql = mysql_query($query)or die(mysql_error());
            $vec = mysql_fetch_array($sql);

			$to = $vec[0];
            $event_id = $objEvent->event_id;
            
			$query_inv = "INSERT into events_invitations(event_id,invitation_mail,invitation_locale)VALUES ('$event_id', '$to', '$AppUI->user_locale')";

			$sql = mysql_query($query_inv);
            $inv = mysql_insert_id();

			$invitation_hash = randomString( "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTWXYZ",'32', strval( $inv) );

            $query_inv2 = "UPDATE events_invitations SET invitation_hash= '$invitation_hash' WHERE invitation_id=$inv";

			$sql = mysql_query($query_inv2);

			enviarMail( $to, $host, $invitation_hash,$event_id);         
		}
 	}
    
	if ( $obj->event_invitation_type == "COMPANY" )
	{		 
		$objEvent = new CEvent();
        $objEvent->load( $obj->event_id );

        $idcompany = $obj->event_company;
       
	    $cmpquery = mysql_query("select user_email, user_first_name, user_last_name from users where user_company='$idcompany' and user_status='0' ")or die(mysql_error());

		while($cmpUsers= mysql_fetch_array($cmpquery) )
		 {
       
			$to = $cmpUsers[user_email]; 
			
            $event_id = $objEvent->event_id;
            
			$query_inv = "INSERT into events_invitations(event_id,invitation_mail,invitation_locale)VALUES ('$event_id', '$to', '$AppUI->user_locale')";

			$sql = mysql_query($query_inv);
            $inv = mysql_insert_id();

			$invitation_hash = randomString( "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTWXYZ",'32', strval( $inv) );

            $query_inv2 = "UPDATE events_invitations SET invitation_hash= '$invitation_hash' WHERE invitation_id=$inv";

			$sql = mysql_query($query_inv2);

			enviarMail( $to, $host, $invitation_hash,$event_id);
         
		}
	}


}

function enviarMail( $to, $host, $inv ,$event_id)
{
	$sql="SELECT event_title, event_start_date, event_location, event_description FROM events WHERE event_id=$event_id";
	$rc=db_exec($sql);
	$vec=db_fetch_array($rc);
	global $AppUI;	
	$message = $AppUI->_("Title").": ".$vec['event_title']."<br>\r\n";
	$message .= $AppUI->_("Date").": ".$vec['event_start_date']."<br>\r\n";
	$message .= $AppUI->_("Location").": ".$vec['event_location']."<br>\r\n";
	$message .= $AppUI->_("Description").": ".$vec['event_description']."<br>\r\n";
	$message .= $AppUI->_("automaticallyinvited");
	$message .= " ".$host[user_first_name]." ".$host[user_last_name]." ";
	$message .= $AppUI->_("to participate in the event")."<br><br>";
	
	$procesarInvitacion = "modules/public/invitation.php";
	$id = "&invitation=$inv";
	$linkAceptar = $AppUI->getConfig( "base_url" )."/$procesarInvitacion?action=accept".$id;
	$linkRechazar = $AppUI->getConfig( "base_url" )."/$procesarInvitacion?action=reject".$id;	
	
	$message .= $AppUI->_("In order to accept the invitation click").' <a href="'.$linkAceptar.'">'.$AppUI->_("here")."</a><br>\r\n";
	$message .= $AppUI->_("In order to reject the invitation click").' <a href="'.$linkRechazar.'">'.$AppUI->_("here")."</a><br>\r\n";
		
	$cabeceras  = "MIME-Version: 1.0\r\n";
	$cabeceras .= "Content-type: text/html; charset=iso-8859-1\r\n";

	/* cabeceras adicionales */
	$cabeceras .= "From: $host[user_first_name] $host[user_last_name] <$host[user_email]\r\n";
	
	mail( $to, $vec['event_title'], $message, $cabeceras);
}

if(isset($_POST['from_projects_tab']) && $obj->event_invitation_type != "PRIVATE"){
?>

<script language="JavaScript">
	window.close();
	window.opener.location.reload();
</script>

<?php 
}
else
{
	$AppUI->redirect($redir);
}
?>
