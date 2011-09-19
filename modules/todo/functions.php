<?php
include ('functions/mail.php');
require_once('modules/admin/admin.class.php');

function todojsp($AppUI){
	?>
	<script language="Javascript">
	<!--
	function close_div(div_name){
		document.getElementById(div_name).style.display='none';
	}
	
	function progress_msg(visibility_st){ 
	
	var f = document.editFrm;
	
	if(visibility_st == 'mostrar')
	{
        // Muestro el cartel de procesando	  
		document.getElementById('progress').style.display='';
		document.getElementById('add_hours').style.display='none';
		
	   setTimeout("progress_msg('error')", 60*1000); 
			
	}else{
		   
	  // Oculto el mensaje de error
	  document.getElementById('progress').style.display = "none"; 		  
		}	
	}
	
	function GTTD(num){
		window.location.hash=num;
	};
	
	function ConfirmSend (msg, name){
    	if (confirm(msg)){   
        	//document.(form_name).Submit();
        	eval ('document.todo_name_'+name+'.submit()');
     	}
  	}

	 function show_hide_project(pro){
	   		if (document.getElementById(name)){
	   			var vis = document.getElementById(name).style.display;
	   			if (vis=='none'){
	   				document.getElementById(name).style.display = '';
						document.getElementById('img' + name).src ='./images/icons/collapse.gif';
						document.getElementById('img' + name).alt = '<?=$AppUI->_('Hide')?>';
	   			}
	   			else{
		    		document.getElementById(name).style.display = 'none';
						document.getElementById('img' + name).src ='./images/icons/expand.gif'; 
						document.getElementById('img' + name).alt = '<?=$AppUI->_('Show')?>';
	   			}
	   		}
	   }

		function show_hide_tasks(prj_id){
			var tb = document.getElementById("tbtasks");
			var vis = '';
			for(var i = 0; i < tb.rows.length; i++ ){

				if (tb.rows[i].parentNode.parentNode.id == "tbtasks"
				&& tb.rows[i].id.indexOf('ptsk_'+prj_id+"_") > -1){
					vis = tb.rows[i].style.display;
					if(vis==""){
						vis = 'none';
					}else{
						vis = ''
					}
					tb.rows[i].style.display = vis;
				
				}
			}			
			if (vis==""){
				var img = imgCollapse;
			}else{
				var img = imgExpand;
			}
			document.getElementById('imgprj_' + prj_id).src = img.src;
			document.getElementById('imgprj_' + prj_id).alt = img.alt;	
		}
		
		function toggle(prj_id) {
			<?php 
			echo "var closemsg = '".$AppUI->_('close')."';";
			echo "var newmsg = '".$AppUI->_('new')."';";
			?>
 			if( document.getElementById('hidethis_'+prj_id).style.display=='none' ){
 				document.getElementById('hidethis_'+prj_id).style.display = '';
   			document.getElementById('hidethis2_'+prj_id).style.display = '';
   			document.getElementById('button_'+prj_id).value = closemsg;
 			}
 			else{
 				document.getElementById('hidethis_'+prj_id).style.display = 'none';
   			document.getElementById('hidethis2_'+prj_id).style.display = 'none';
   			document.getElementById('button_'+prj_id).value = newmsg;
 			}
		}

		var arTRs = new Array();
		var imgExpand = new Image;
		var imgCollapse = new Image;
		imgExpand.src = './images/icons/expand.gif';
		imgExpand.alt = '<?=$AppUI->_('Show')?>';
		imgCollapse.src = './images/icons/collapse.gif'; 
		imgCollapse.alt = '<?=$AppUI->_('Hide')?>';

		function popCalendar_ToDo( field, FrmName ){
			calendarField = field;
			CalFrmName = FrmName;
			idate = eval( 'document.'+FrmName+'.timexp_'+field+'.value' );
			window.open( 'index.php?m=public&a=calendar&dialog=1&suppressLogo=1&callback=setCalendar_ToDo&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
		}
		
		/**
		 *	@param string Input date in the format YYYYMMDD
		 *	@param string Formatted date
		 */
		 
		function setCalendar_ToDo( idate, fdate ) {
			
			fld_date = eval( 'document.'+CalFrmName+'.timexp_'+calendarField );
			fld_fdate = eval( 'document.'+CalFrmName+'.'+calendarField );
			fld_date.value = idate;
			fld_fdate.value = fdate;
			
		}
		
		function validateForm(descrip,user_assign){
			
			var msg = "";
			var ret = false;
			
			if (( descrip == "")) {
				msg += "<?=$AppUI->_('descripvalid');?>";
			}
		
			if (( user_assign == "0")) {
				msg += "<?=$AppUI->_('assignvalid_todo');?>";
			}
		
			if (msg==""){
				ret= true;	
			}else{
				alert1(msg);
			}
			
			return ret;
			
		}

		//-->
		</script>
	<?php
}

function filter_input_ToDo($_POST){
	?>
		<input type='hidden' name='FtMTD' value='<?php echo $_POST['FtMTD']; ?>'>
		<input type='hidden' name='FtOW' value='<?php echo $_POST['FtOW']; ?>'>
		<input type='hidden' name='FtHP' value='<?php echo $_POST['FtHP']; ?>'>
		<input type='hidden' name='FtHD' value='<?php echo $_POST['FtHD']; ?>'>
		<input type='hidden' name='FtHE' value='<?php echo $_POST['FtHE']; ?>'>
		<input type='hidden' name='FtOw' value='<?php echo $_POST['FtOw']?>'>
		<input type='hidden' name='FtAss' value='<?php echo $_POST['FtAss']?>'>
		<input type='hidden' name='FtAuto' value='off'>
	<?php
}


function ExecEditToDo ($_POST, $AppUI){
	$sql="SELECT project_id FROM project_owners po WHERE project_owner='".$AppUI->user_id."' AND project_id='".$_POST['pid']."'";
	$IsProjAdmin=mysql_num_rows(mysql_query($sql));
	//echo "$sql<br>";
	$sql="SELECT user_owner FROM project_todo WHERE user_owner='".$AppUI->user_id."' AND id_todo='".$_POST['tid']."'";
	//echo "$sql<br>";
	$IsTodoOwner=mysql_num_rows(mysql_query($sql));
	//echo "Soy Owner $IsTodoOwner <br><br>";
	//echo $AppUI->user_id."==".$_POST['todo_assign']."<br><br>";
	//echo "validaci?n".$_POST['type']."=='CheckChange'<br><br>";
	IF (($AppUI->user_id==$_POST['todo_assign']OR $IsProjAdmin==1 OR $IsTodoOwner==1) AND $_POST['type']=='CheckChange'){
		//echo "ENTRA";
		IF ($_POST['status']=='on'){
			$status=1;
			$_POST['mail_msg']='The ToDo was done';
		}
		ELSE{
			$status=0;
			$_POST['mail_msg']='The ToDo is undone';
		}
		$sql="UPDATE project_todo 
						SET 
							status='$status'
						WHERE
							project_id='".$_POST['pid']."' AND id_todo='".$_POST['tid']."'";
		$sql_2="SELECT user_assigned, description FROM project_todo WHERE id_todo='".$_POST['tid']."'";
		#echo "<br>$sql_2<br><br>";
		$vec2=mysql_fetch_array(mysql_query($sql_2));
		$_POST['todo_assign']=$vec2['user_assigned'];
		$_POST['todo_desc']=$vec2['description'];
		mysql_query($sql);
		IF ($AppUI->user_id!=$_POST['todo_assign']) send_mail($_POST, $AppUI);
		$AppUI->setMsg( 'ToDo Updated', UI_MSG_OK);	
	}
	ELSEIF (($IsProjAdmin==1 OR $IsTodoOwner==1) AND $_POST['type']!='CheckChange') {	
		$sql="SELECT user_assigned FROM project_todo WHERE id_todo='".$_POST['tid']."'";
		$vec=mysql_fetch_array(mysql_query($sql));
		IF ($_POST['todo_assign']!=$vec['user_assigned']){
			$sql="SELECT user_email FROM users u WHERE user_id=".$vec['user_assigned'];
			$vec=mysql_fetch_array(mysql_query($sql));
			$_POST['mail_msg']='The ToDo was reassigned';
			send_mail($_POST, $AppUI, $vec['user_email']);
		}
		eval ("\$due_date=\$_POST['timexp_date_".$_POST['pid']."_".$_POST['tid']."'];");
		IF ($due_date!='') $_POST['due_date']=substr($due_date,0,4)."-".substr($due_date,4,2)."-".substr($due_date,6,2)." 00:00:00";
		//	user_owner='".$AppUI->user_id."',
		$sql= "UPDATE project_todo 
						SET 
							description='".checkpost($_POST['todo_desc'])."', 
							priority='".$_POST['todo_prio']."', 
							user_assigned='".$_POST['todo_assign']."', 
							date=NOW(),
							due_date='".$_POST['due_date']."',
							task_id = '".$_POST['task']."'
						WHERE
							project_id='".$_POST['pid']."' AND id_todo='".$_POST['tid']."'";
		$_POST['mail_msg']='The ToDo was updated';
		//echo "<br><br>$sql<br><br>";
		mysql_query($sql);
		send_mail($_POST, $AppUI);
		IF ($AppUI->user_id!=$_POST['todo_assign'])	$AppUI->setMsg( 'ToDo Updated', UI_MSG_OK);
	}
	ELSE $AppUI->setMsg( $msg, UI_MSG_ERROR );
}

function todoquery($AppUI){
	if ($_POST['FtOW']=='on'){
		$date=date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+7, date("Y")));
		$filter.=" AND pt.due_date<>'0000-00-00 00:00:00' AND pt.due_date<'$date 00:00:00'";
	}
	if ($_POST['FtOw']!='') $filter.=" AND pt.user_owner=".$_POST['FtOw'];
	if ($_POST['FtMTD']=='on') $filter.=" AND pt.user_assigned=".$AppUI->user_id;
	elseif ($_POST['FtAss']!='')$filter.=" AND pt.user_assigned=".$_POST['FtAss'];
	if ($_POST['FtHP']=='on') $filter.=" AND pt.priority=1";
	if ($_POST['FtHD']=='on') $filter.=" AND (pt.status='0' OR pt.status IS NULL)";
	if ($_POST['FtHE']=='on') $filter.=" AND pt.status IS NOT NULL";
	
	
	$sql="SELECT project_id FROM project_roles WHERE user_id=".$AppUI->user_id."
				UNION
				SELECT project_id FROM project_owners po WHERE project_owner=".$AppUI->user_id."
				UNION
				SELECT project_id FROM projects WHERE project_owner=".$AppUI->user_id."
				GROUP BY project_id
				ORDER BY project_id";
	//echo "$sql";
	$aut_proj=implode( ',', array_keys(db_loadHashList($sql)) );
    IF ($aut_proj=='') $aut_proj='0';
	//ECHO "<br>$aut_proj<br>";
	$sql="SELECT
				  p.project_id,
					IF (po.project_owner>'0','1','0') AS proj_admin,
				  p.project_color_identifier,
				  p.project_name,
			    pt.id_todo,
			    pt.status,
			    pt.task_id,
			    pt.user_owner AS user_owner_id,
				  pt.description,
				  pt.priority,
				  pt.user_assigned,
				  CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name,
				  CONCAT(u_2.user_last_name, ', ', u_2.user_first_name) AS user_owner,
				  pt.date,
				  IF (pt.due_date>'0000-00-00 00:00:00',pt.due_date,'3000-01-01 00:00:00') AS due_date
				FROM projects AS p
				LEFT JOIN project_todo AS pt
				  ON (p.project_id=pt.project_id)
				LEFT JOIN users AS u_2
				  ON (pt.user_owner=u_2.user_id)
				LEFT JOIN users AS u
				  ON (pt.user_assigned=u.user_id)
				LEFT JOIN project_owners AS po
          ON (po.project_id=p.project_id AND po.project_owner=".$AppUI->user_id.")
				WHERE p.project_id IN ($aut_proj) $filter
				ORDER BY p.project_name ASC, pt.priority ASC, due_date ASC, pt.description ASC";
	//echo "<br>$sql<br>";
	return mysql_query($sql);
}

function ExecDelToDo ($_POST, $AppUI){
	$sql="SELECT project_id FROM project_owners po WHERE project_owner='".$AppUI->user_id."' AND project_id='".$_POST['pid']."'";
	$IsProjAdmin=mysql_num_rows(mysql_query($sql));
	$sql="SELECT user_owner FROM project_todo WHERE user_owner='".$AppUI->user_id."' AND id_todo='".$_POST['tid']."'";
	$IsTodoOwner=mysql_num_rows(mysql_query($sql));
	if (1==$IsProjAdmin OR 1==$IsTodoOwner){
		$AppUI->setMsg( 'ToDo Deleted', UI_MSG_OK);
		$sql_2="SELECT user_assigned, description FROM project_todo WHERE id_todo='".$_POST['tid']."'";
			//echo "<br><br>$sql_2<br><br>";
		$vec2=mysql_fetch_array(mysql_query($sql_2));
		$_POST['todo_assign']=$vec2['user_assigned'];
		$_POST['todo_desc']=$vec2['description'];
		$_POST['mail_msg']=$AppUI->_( 'Assignment deleted' );
		send_mail($_POST, $AppUI);
		$sql="DELETE FROM project_todo WHERE id_todo='".$_POST['tid']."'";
		mysql_query($sql);
                
               // Borro las hs reportadas al todo
		$sql = "delete timexp_status.*, timexp.*, timexp_ts
						FROM timexp_status inner join timexp on timexp_status.timexp_id = timexp.timexp_id
						LEFT JOIN timexp_ts on timexp_status.timexp_id = timexp_ts.timexp_ts_id
						WHERE timexp_applied_to_type = '4' and  timexp_applied_to_id = '".$_POST['tid']."' ";
		mysql_query($sql);
	}
	ELSE	$AppUI->setMsg( $msg, UI_MSG_ERROR );
}

function ExecNewToDo ($_POST, $AppUI){
	$sql="SELECT
					u.user_id,
				  CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name
				FROM project_roles AS pr
				INNER JOIN users AS u
				  ON (pr.user_id=u.user_id)
				WHERE pr.user_id='".$AppUI->user_id."'
				UNION
				SELECT
				  u.user_id,
				  CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name
				FROM project_owners AS po
				INNER JOIN users AS u
				  ON (po.project_owner=u.user_id)	
				WHERE po.project_owner='".$AppUI->user_id."'
				GROUP BY user_id";
	$IsProj_Admin_User=mysql_num_rows(mysql_query($sql));
	IF ($IsProj_Admin_User!='0') {	
		eval ("\$due_date=\$_POST['timexp_date_".$_POST['pid']."_".$_POST['tid']."'];");
		//print_r($_POST);
		IF ($due_date!='') $_POST['due_date']=substr($due_date,0,4)."-".substr($due_date,4,2)."-".substr($due_date,6,2)." 00:00:00";
		$sql= "INSERT INTO project_todo (
							project_id, 
							description, 
							priority, 
							user_assigned, 
							user_owner, 
							date, 
							due_date,task_id) 
						VALUES (
							'".$_POST['pid']."', 
							'".checkpost($_POST['todo_desc'])."', 
							'".$_POST['todo_prio']."', 
							'".$_POST['todo_assign']."', 
							'".$AppUI->user_id."', NOW(),
							'".$_POST['due_date']."',
							'".$_POST['task']."'
							)";
		mysql_query($sql);
		$_POST['tid']=mysql_insert_id();
		//echo "<br><br>$sql<br><br>";
		$_POST['mail_msg']='New ToDo Assigned';
		$_POST['tid']=mysql_insert_id();
		IF ($AppUI->user_id!=$_POST['todo_assign']) send_mail($_POST, $AppUI);
		$AppUI->setMsg( $AppUI->_('ToDo Inserted'), UI_MSG_OK);
	}
	ELSE $AppUI->setMsg( $msg, UI_MSG_ERROR );
}



function strike ($string, $status){
	if ($status==1){
		$strike1="<strike>";
		$strike2="</strike>";
	}
	else{
		$strike1="";
		$strike2="";
	}
	echo $strike1.$string.$strike2;
}

function send_mail($_POST, $AppUI, $recipient=''){ 
	if($_POST['todo_assign']!=$AppUI->user_id){

		global $text_loc;
		$usr = new CUser();
		$usr->load($_POST['todo_assign']);
		$prefs = CUser::getUserPrefs($usr->user_id);
		$user_language = isset($prefs["LOCALE"]) ? $prefs["LOCALE"] : $AppUI->getConfig("host_locale");
		
		$sql="SELECT project_name FROM projects AS p INNER JOIN project_todo AS pt ON (p.project_id=pt.project_id) WHERE id_todo= '".$_POST['tid']."'";
		//echo "$sql<br>";
		$vec_project=mysql_fetch_array(mysql_query($sql));
		$sql="SELECT CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name, user_email FROM users u WHERE user_id='".$AppUI->user_id."'";
		//echo "$sql<br>";
		$vec_sender=mysql_fetch_array(mysql_query($sql));
		$sql="SELECT CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name, user_email FROM users u WHERE user_id='".$_POST['todo_assign']."'";
		//echo "$sql<br>";
		//echo "<br><br>$sql<br>";
		//print_r($_POST);
		
		
		$sql_loc="SELECT pref_value FROM user_preferences u WHERE pref_user='".$_POST['todo_assign']."' AND pref_name='LOCALE';";
		//echo "$sql<br>";
		$vec_loc=db_fetch_array(db_exec($sql_loc));
		if ($vec_loc['0']=='') $vec_loc['0']='en';
		$text_loc=locales($vec_loc['0'], 'todo.inc');
		//print_r($text_loc);
		$vec_recipient=mysql_fetch_array(mysql_query($sql));
		if($_POST['todo_prio']=="1") $vcs['priority']="PRIORITY: 1";
		if($_POST['todo_prio']=="3") $vcs['priority']="PRIORITY: 9";
	  	if($_POST['due_date']!='') $mail_due_date=substr($_POST['due_date'],8,2)."-".substr($_POST['due_date'],5,2)."-".substr($_POST['due_date'],0,4);
		$mail_today=date("d-m-Y", mktime(0, 0, 0, date("m")  , date("d"), date("Y")));
		$mail_project=$vec_project['project_name'];
		$mail_description=$_POST['todo_desc'];
		$mail_sender=$vec_sender['user_name'];
		$mail_sender_mail=$vec_sender['user_email'];
		$mail_recipient=$vec_recipient['user_name'];
		if($mail_due_date=='00-00-0000') $mail_due_date='';
		IF (stristr('$recipient', '@')){
			$mail_recipient_mail=$recipient;
			$Re='Re-';
		}
		ELSE  $mail_recipient_mail=$vec_recipient['user_email'];
		$mail_subject="[Proficient] ToDo ".$vec_project['project_name']." - ".$AppUI->_to($user_language,$_POST['mail_msg']);
		$mail_msg=trans($_POST['mail_msg']);
		$style="style='background-color:#FFFFFF; font-weight: bold; color: #000000; font-size: 16px;'";
		$mail_mensaje="<font $style>$mail_msg</font>";
		$style="style='background-color: #717062; font-weight: bold; color: #FFFFFF; text-align: right;'";
		$mail_mensaje .="
		<table width='40%' border='1' cellSpacing='0' style='font-family: Verdana, Arial, Helvetica, sans-serif;
		font-size: 11px; BORDER-RIGHT: #e0dfe3; PADDING-RIGHT: 3pt; BORDER-TOP: #e0dfe3; PADDING-LEFT: 3pt; PADDING-BOTTOM: 3pt; BORDER-LEFT: #e0dfe3; PADDING-TOP: 3pt; BORDER-BOTTOM: #e0dfe3;'>
			<tr>
				<td $style width='40%'>".trans( 'Project' ).":</td>
				<td>$mail_project </td>
			</tr>
			<tr>
				<td $style>".trans( 'Description' ).":</td>
				<td>$mail_description</td>
			</tr>
			<tr>
				<td $style>".$Re."".trans( 'Assigned by' ).":</td>
				<td>$mail_sender</td>
			</tr>
			<tr>
				<td $style>".trans( 'Creation date' ).":</td>
				<td>$mail_today</td>
			</tr>
			<tr>
				<td $style>".trans( 'Due date' ).":</td>
				<td>$mail_due_date</td>
			</tr>
			</table>";
		 if ($mail_recipient_mail != ""){
		 enviar_mail($mail_recipient_mail, $mail_mensaje, $mail_subject);
		 }
	//echo "$mail_recipient_mail','$mail_subject','$mail_mensaje','From:$mail_sender_mail'";
	}
}


function todoProjHeader($vec, $AppUI){
	
	global $AppUI;
	// Traigo el nombre del proyecto
	$query_tasks = "SELECT c.company_name FROM companies as c, projects as p 
WHERE company_id = project_company AND project_id = '".$vec['project_id']."'"; 
	$sql_name = db_exec($query_tasks);
	$data_cia = mysql_fetch_array($sql_name);
	$cia_name = $data_cia['company_name'];
	
	?>
	<tr class="tableRowLineCell">
		<td colspan="12"></td>
	</tr>
	<tr>
		<?php
		IF ($vec['id_todo']!=''){
			?>
			<td width='20'>
				<a href="javascript: //" onclick="javascript: show_hide_tasks('<?php echo $vec['project_id'] ?>');" id="project_<?=$vec['project_id']?>" name="project_<?=$vec['project_id']?>">
				<img id="imgprj_<?php echo $vec['project_id'] ?>" src="./images/icons/collapse.gif" alt="<?=$AppUI->_('Hide')?>" border="0" height="16" width="16">
				</a>
			</td>
			<?php
			$cols='12';
		}
		ELSE{
			$cols='13';
		}
		?>
		<td colspan="<?php echo $cols; ?>">
			<table border="0" width="100%">
			<tbody><tr>
				<td style="border: 2px outset rgb(238, 238, 238); background-color:#<?php echo $vec['project_color_identifier']; ?>;" nowrap="nowrap" >
					<a href="./index.php?m=projects&amp;a=view&amp;project_id=<?php echo $vec['project_id'] ?>">
					<span style="color:<?php echo bestColor($vec['project_color_identifier']); ?>; text-decoration: none;"><strong><?php echo $cia_name."/".$vec['project_name']; ?></strong></span></a>
				</td>
				<td width='100%'>
				</td>
			    
			    <td align="right">
			      <input class="button" type="button" id="button_<?php echo $vec['project_id'] ?>" onclick="javascript: toggle('<?php echo $vec['project_id'] ?>');" value="New ToDo" >&nbsp;
			    </td>
			     
			  </tr>
			
			</tbody>
			</table>
		</td>
		
			
	</tr>
	<?php
}


function newTodo($vec, $AppUI, $_POST=''){
	?>
	
	<?php
	IF ($_POST['act']=='edit' AND $vec['project_id']==$_POST['pid']){
		$sql="SELECT 
						description, 
						CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name,
						priority, 
						due_date, 
						user_id,
						task_id 
					FROM project_todo AS pt
					LEFT JOIN users AS u
				  	ON (pt.user_assigned=u.user_id)
					WHERE id_todo='".$_POST['tid']."'";
		$vec_todo=mysql_fetch_array(mysql_query($sql));
		if ($vec_todo['due_date']>1 AND $vec['due_date']!='3000-01-01 00:00:00'){
				$due_date_from=substr($vec_todo['due_date'],8,2)."/".substr($vec_todo['due_date'],5,2)."/".substr($vec_todo['due_date'],0,4);
				$due_date_hide=substr($vec_todo['due_date'],0,4).substr($vec_todo['due_date'],5,2).substr($vec_todo['due_date'],8,2);
			}
		$input="<input type='hidden' name='act' value='doedit'>";
		$input.= "<input type='hidden' name='tid' value='".$_POST['tid']."'>";
	}
	ELSE {
		$sql="SELECT 
						user_id,
						CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name
					FROM users AS u
					WHERE user_id='".$AppUI->user_id."'";
		$vec_todo=mysql_fetch_array(mysql_query($sql));
		//$due_date_hide=date("Ymd");
		$input="<input type='hidden' name='act' value='new'>";
	}
	//print_r($vec_todo);
	?>
	
	<tr class="tableRowLineCell" id="hidethis_<?php echo $vec['project_id'].$_POST['tid']; ?>">
		<td colspan="11">
			<form name="frmnewtodo_<?php echo $vec['project_id']."_".$_POST['tid']; ?>" action="index.php?m=todo#project_<?=$vec['project_id']?>" method="POST" onSubmit="return validateForm(frmnewtodo_<?php echo $vec['project_id']."_".$_POST['tid'];?>.todo_desc.value, frmnewtodo_<?php echo $vec['project_id']."_".$_POST['tid'];?>.todo_assign.value);">
			<input type='hidden' name='pid' value='<?php echo $vec['project_id']; ?>'>	
			<?php
				echo $input;
				filter_input_ToDo($_POST);
				echo "<a name='todo_".$vec['project_id']."_".$_POST['tid']."'></a>";
			?>
		</td>
	</tr>
	<tr id="hidethis2_<?php echo $vec['project_id'].$_POST['tid']; ?>" valign="middle">
		<td colspan="12">
			<table width="100%">
				<tr>
					<td width='11'>
						<?php
							switch ($vec_todo['priority']) {
								case 1: $high="selected"; break;
								case 2: $normal="selected"; break;
								case 3: $low="selected"; break;
								case 0:$normal="selected"; break;
							}
						?>
						<select name="todo_prio" class="text" size="1">
							<option value="1" <?php echo $high; ?> ><?php echo $AppUI->_('High'); ?></option>
							<option value="2" <?php echo $normal; ?> ><?php echo $AppUI->_('Normal'); ?></option>
							<option value="3" <?php echo $low; ?> ><?php echo $AppUI->_('Low'); ?></option>
						</select>
					</td>
					<td>
					   <select name="task" class="text" style="width:160px">
					       <option value="0"><?=$AppUI->_('Associate to task')?></option>
					   <?php
					      $query_tasks = "SELECT task_id, task_name FROM tasks WHERE task_project = '".$vec['project_id']."' ";
					      $sql_tasks = db_exec($query_tasks);
					      
					      while ($vec_tasks = mysql_fetch_array($sql_tasks)) 
					      {
					      	 if($vec_todo['task_id'] == $vec_tasks['task_id']){
					      	 	$sel_task = "SELECTED";
					      	 }else{
					      	 	$sel_task ="";
					      	 }
					      	 
					      	 echo "<option value=\"".$vec_tasks['task_id']."\" $sel_task>".$vec_tasks['task_name']."</option>";
					      }
					      
					   ?>
					   </select>
					</td>
					<td>
						<input name="todo_desc" size="40" class="text" value="<?php echo $vec_todo['description'] ?>" type="text">
					</td>
					<td>
						<select name="todo_assign" class="text" size="1" style="width:160px">
						<?php 
							$sql="SELECT
										  u.user_id,
										  CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name
										FROM project_roles AS pr
										INNER JOIN users AS u
										  ON (pr.user_id=u.user_id)
										WHERE project_id='".$vec['project_id']."'
										UNION
										SELECT
										  u.user_id,
										  CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name
										FROM project_owners AS po
										INNER JOIN users AS u
										  ON (po.project_owner=u.user_id)	
										WHERE project_id='".$vec['project_id']."'
										GROUP BY user_id";
							$rc2=mysql_query($sql);
							//echo "<option value='0' selected='selected'></option>";
							WHILE ($vec_assign=mysql_fetch_array($rc2)){
								IF  ($vec_assign['user_id']==$vec_todo['user_id'] AND $_POST['act']=='edit') $sel="selected";
								ELSEIF ($_POST['act']!='edit' AND $vec_assign['user_id']==$AppUI->user_id) $sel="selected";
								ELSE $sel="";
								echo "<option $sel value='".$vec_assign['user_id']."'>".$vec_assign['user_name']."</option>";
							}
						?>
						</select>
							
					</td>
					<td>
						<table>
							<tr>
								<td> 
								   <input name="date_<?php echo $vec['project_id']."_".$_POST['tid']; ?>" value="<?php echo $due_date_from; ?>" class="text" size="10" type="text" DISABLED>
								</td>
								<td>
									<input type="hidden" name="timexp_date_<?php echo $vec['project_id']."_".$_POST['tid']; ?>" value="<?php echo $due_date_hide; ?>">
					       			<input type="hidden" name="timexp_date_<?php echo $vec['project_id']."_".$_POST['tid']; ?>_format" value="%d/%m/%Y">
									<a href="#" onClick="popCalendar_ToDo('date_<?php echo $vec['project_id']."_".$_POST['tid']; ?>', 'frmnewtodo_<?php echo $vec['project_id']."_".$_POST['tid']; ?>')"><img src="./images/calendar.gif" width="24" height="12" alt="Calendario" border="0" /></a>
								</td>
							</tr>
						</table>
					</td>
					<td width='1' align='center' colspan='2'>
						<input type='submit' class='button' value='<?php echo $AppUI->_('save'); ?>'>
					</td>
				</form>
				</tr>
				</table>
				<?php
				//echo "<br>Accion ".$_POST['act'];
				//echo "<br>GETproject_id".$_POST['pid']."=".$vec['project_id'];
				IF ($vec['project_id']!=$_POST['pid'] OR $_POST['act']!='edit'){
				?>
				<script language="Javascript">
				<!--
					toggle('<?php echo $vec['project_id'] ?>');
				-->
				</script> 	
				<?php
				}?>
			</td>
		</tr>
	<?php
}

function TodoRow($vec, $AppUI, $_POST){
	//echo "<pre>"; print_r($vec); echo "</pre>";
	?>
	<tr class="tableRowLineCell" id="ptsk_<?php echo $vec['project_id'] ?>_<?php echo $vec['id_todo'] ?>_sep">
		<td colspan="12"></td>
	</tr>
	<tr id="ptsk_<?php echo $vec['project_id'] ?>_<?php echo $vec['id_todo'] ?>" valign="top">
		<td width='1'>
				<form name="frmchecktodo_<?php echo $vec['id_todo'] ?>" action="index.php?m=todo#project_<?=$vec['project_id']?>" method="POST">	
				<input type='hidden' name='pid' value='<?php echo $vec['project_id']; ?>'>
				<input type='hidden' name='act' value='doedit'>
				<input type='hidden' name='type' value='CheckChange'>
				<?php
				if ($vec['due_date']!='N/A' AND ($vec['due_date'] < date ("Y-m-d 00:00:00"))){
					$fc0="<font color='red'>";
					$fc1="</font>";
				}
				filter_input_ToDo($_POST);
				echo "<input type='hidden' name='tid' value=".$vec['id_todo'].">";
				echo "<input type='hidden' name='todo_assign' value=".$vec['user_assigned'].">";
				echo "<a name='todo_".$vec['project_id']."_".$vec['id_todo']."'></a>";
				IF ($vec['status']==1) $check="checked";
				ELSE $check=""; 
				IF ($vec['proj_admin']==1 OR $vec['user_owner_id']==$AppUI->user_id OR $vec['user_assigned']==$AppUI->user_id) $DISABLED="";
				ELSE $DISABLED="DISABLED";
				echo "<input type='checkbox' onclick='submit()' name='status' $check $DISABLED>";
				?>
			
		</td>
		</form>
		<td width='11'>
			<?php
				switch ($vec['priority']) {
				case 1: $prio="./images/high.png"; break;
				case 3: $prio="./images/icons/low.gif"; break;
				default: $prio="./images/1x1.gif";
				}
			?>
			<img src="<?php echo $prio; ?>">
		</td>
		<td width='16'>
		    <? if ($vec['task_id']>0){ 
		    	$query_task = "SELECT task_name FROM tasks WHERE task_id='".$vec['task_id']."' ";
		    	$sql_task = db_exec($query_task);
		    	$data_task = mysql_fetch_array($sql_task);
		    	$task_name = $data_task['task_name'];
				$task_name=ereg_replace('"','&quot;',$task_name);
				$task_name=ereg_replace("'","%27",$task_name);
							
		    	$detalles = $AppUI->_('To-do associate to the task:')." <br><center>".$task_name."</center>";
		    ?>
		    <span onmouseover="tooltipLink('<pre style=&quot;margin:0px; background:#FFFFFF&quot;><?=$detalles?></pre>', '');" onmouseout="tooltipClose();">
		    	<a href="index.php?m=tasks&a=view&task_id=<?=$vec['task_id']?>"><img src='./images/icons/lupa3.gif'  border='0' height='16' width='16'></a>
		    </span>
		    <? } ?>
		</td>
		<td>
			<?php
				echo $fc0;
				if ($vec['date']!='3000-01-01 00:00:00') $date=substr($vec['date'],8,2)."/".substr($vec['date'],5,2)."/".substr($vec['date'],0,4);
				else $date="N/A";
				strike ($date, $vec['status']);
				echo $fc1;
			?>
		</td>
		<td>
			<?php
				echo $fc0;
				$vec['description'] = ereg_replace('&apos;',"'",$vec['description']);
				$vec['description'] = ereg_replace('&acute;','´',$vec['description']);
				strike ($vec['description'], $vec['status']) ;
				echo $fc1;
			?>
		</td>
		<td>
			<?php
				echo $fc0;
				strike ($vec['user_owner'], $vec['status']);
				echo $fc1;
			?>
		</td>
		<td>
			<?php
				echo $fc0;
				strike ($vec['user_name'], $vec['status']);
				echo $fc1;
			?>
		</td>
		<td>
			<?php
				echo $fc0;
				if ($vec['due_date']!='3000-01-01 00:00:00') $due_date=substr($vec['due_date'],8,2)."/".substr($vec['due_date'],5,2)."/".substr($vec['due_date'],0,4);
				else $due_date="N/A";
				strike ($due_date, $vec['status']);
				echo $fc1;
			?>
		</td>
		<?php
			if ($vec['proj_admin']==1 OR $vec['user_owner_id']==$AppUI->user_id){
					
				    echo "<td width='1' align='right' valign='bottom'>";
					echo "<form name=\"multiple_a".$vec['id_todo']."\" action='index.php?m=todo' method='POST'>";
					echo "<input type='hidden' name='act' value='multi_act'>";
					echo "<input type='hidden' name='tid' value='".$vec['id_todo']."'>";
					echo "<input type='hidden' name='pid' value='".$vec['project_id']."'>";
					//echo "<input type='hidden' name='GTTD' value='todo_".$vec['project_id']."_".$vec['id_todo']."'>";
					filter_input_ToDo($_POST);
					echo "<input type=\"checkbox\" name=\"todo_".$vec['id_todo']."\" onclick=\"check_todos('".$vec['id_todo']."','".$vec['project_id']."',this, 'document.multiple_a".$vec['id_todo'].".todo_".$vec['id_todo'].".checked=0')\" >";
					echo "</td></form>";
					
					echo "<td width='1' align='right' valign='bottom'>";
					echo "<form name='todo_name_edit_".$vec['project_id']."_".$vec['id_todo']."' action='index.php?m=todo#project_".$vec['project_id']."' method='POST'>";
					echo "<input type='hidden' name='act' value='edit'>";
					echo "<input type='hidden' name='tid' value='".$vec['id_todo']."'>";
					echo "<input type='hidden' name='pid' value='".$vec['project_id']."'>";
					echo "<input type='hidden' name='GTTD' value='todo_".$vec['project_id']."_".$vec['id_todo']."'>";
					filter_input_ToDo($_POST);
					echo "<input type='image' src='./images/icons/edit_small.gif' alt='".$AppUI->_('Edit')."' style='width:15px;height:15px;cursor:hand;'>";
					echo "</td></form>";
					echo "<td width='1' align='left' valign='bottom'>";
					echo "<form name='todo_name_delete_".$vec['project_id']."_".$vec['id_todo']."' action='index.php?m=todo#project_".$vec['project_id']."' method='POST'>";
					echo "<input type='hidden' name='act' value='delete'>";
					echo "<input type='hidden' name='tid' value='".$vec['id_todo']."'>";
					echo "<input type='hidden' name='pid' value='".$vec['project_id']."'>";
					//echo "<input type='hidden' name='GTTD' value='todo_".$vec['project_id']."_".$vec['id_todo']."'>";
					filter_input_ToDo($_POST);
					echo "<a href=\"javascript:ConfirmSend('".$AppUI->_('Do you want to delete this todo?')."".$vec['description']."', 'delete_".$vec['project_id']."_".$vec['id_todo']."')\"><img src='./images/icons/trash_small.gif' alt='".$AppUI->_('Delete')."' border='0' style='width:15px;height:15px;'></a>";
					echo "</td>";
					if(!getDenyEdit("timexp")) {
						echo "<td valign='bottom'><a href='javascript:report_hours(".$vec['id_todo'].",4,$vec[status]);' >";
						echo "<img src='./images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'></a></td>";
					}else{
						echo "<td valign='bottom'>&nbsp;</td>";
					}
					echo "</form>";
					
					?>
				<?php
			}
			elseif (can_report($AppUI->user_id,$vec['id_todo']) && !getDenyEdit("timexp")){
				echo "<td></td><td></td><td>";
				echo "</td><td valign='bottom'><a href='javascript:report_hours(".$vec['id_todo'].");' >";
				echo "<img src='/images/icons/calendar_report.png' alt='Cargar Horas' border=0 style='height:18px;'></a></td>";
			}
			else echo "<td></td><td></td>";
	?>
	</tr>

	<?php
}

function todoheader($AppUI, $_POST){
	?>
	<br/>
	<table  border="0" cellpadding="0" cellspacing="0" width="100%" align='center'>
	<tbody>
	
	<tr VALIGN=top>	
	  <td colspan="3">
		<form name="filter" action="" method="POST">
		<?php
			IF ($_POST['FtMTD']=='on') $checkMyToDo='checked';
			IF ($_POST['FtOW']=='on') $checkOneWeek='checked';
			IF ($_POST['FtHP']=='on') $checkHighPrio='checked';
			IF ($_POST['FtHD']=='on') $checkHideDone='checked';
			IF ($_POST['FtHE']=='on') $checkHideEmpty='checked';
		?>
		
		
			<table border='0'align='center' VALIGN="top" width='100%' cellpadding="0" cellspacing="0" >
				<tr class="tableForm_bg">
					
					<input type='hidden' name='m' value='todo'>
                    
				<td >
				 <table border='0'align='center'>
				  <tr>
					<td width='1' align='right'><input type='checkbox' onclick='submit()' name='FtMTD' <?php echo $checkMyToDo; ?>></td>
					<td align='left'><?php echo $AppUI->_('Only My ToDos'); ?></td>
					
					<td width='15' align='center'>|</td>
					
					<td width='1' align='right'><input type='checkbox' onclick='submit()' name='FtOW' <?php echo $checkOneWeek; ?>></td>
					<td align='left'><?php echo $AppUI->_('Due Date In Less Than One Week'); ?></td>
					
					<td width='15' align='center'>|</td>
					
					<td width='1' align='right'><input type='checkbox' onclick='submit()' name='FtHP'<?php echo $checkHighPrio; ?>></td>
					<td align='left'><?php echo $AppUI->_('Only High Priority'); ?></td>
					
					<td width='15' align='center'>|</td>
					
					<td width='1' align='right'><input type='checkbox' onclick='submit()' name='FtHD' <?php echo $checkHideDone; ?>></td>
					<td align='left'><?php echo $AppUI->_('Hide Complete ToDos'); ?></td>
					
					<td width='15' align='center'>|</td>
					
					<td width='1' align='right'><input type='checkbox' onclick='submit()' name='FtHE' <?php echo $checkHideEmpty; ?>></td>
					<td align='left'><?php echo $AppUI->_('Hide Empty Projects'); ?><input type='hidden' name='FtAuto' value='off'></td>
				</tr>
				</table>
				</td>			
			</tr>

			</table>
		</td>
	</tr>
	<tr>	
		<td colspan="3">
			<table border='0' align='center' VALIGN="top" width='100%' cellpadding="0" cellspacing="0" ><tr class="tableForm_bg">
				<input type='hidden' name='m' value='todo'>
				<td>
			  <table border='0' align='center'>
				<tr>
					<td align='center'>
						<table border='0' align='center' cellpadding="0" cellspacing="0" >
							<tr>
								<td width='0' align='right'><?php echo $AppUI->_('Owner'); ?>&nbsp;</td>
								<td width='0' align='left'>
									<select name="FtOw" onchange="submit()" class="text">
										<option value=''><?php echo $AppUI->_('All'); ?></option>
										<?php
										$sql="SELECT
											  user_id,
											  CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name
											FROM project_todo AS pt
											LEFT JOIN users AS u ON (pt.user_owner=u.user_id)
											GROUP by user_id
											ORDER BY user_name";

										$rc=mysql_query($sql);
										while ($vec=mysql_fetch_array($rc)){
											IF ($_POST['FtOw']==$vec['user_id']) $sel='SELECTED';
											ELSE $sel='';
											if($vec['user_id']!=""){
											ECHO "<option $sel value='".$vec['user_id']."' $sel>".$vec['user_name']."</option>";
											}
										}
										?>
									</select>
								</td>
							</tr>
						</table>
					</td>
					<td width='15' align='center'>|</td>
					<td align='center'>
						<table border='0' align='center' cellpadding="0" cellspacing="0" >
							<tr>
								<td width='0' align='right'><?php echo $AppUI->_('Assigned User')?> &nbsp;</td>
								<td width='0' align='left'>
										<?php
										IF ($_POST['FtMyToDo']!='') {
											$dis='DISABLED';
											$_POST['FtAss']=$AppUI->user_id;
										}
										$sql="SELECT
											  user_id,
											  CONCAT(u.user_last_name, ', ', u.user_first_name) AS user_name
											FROM project_todo AS pt
											INNER JOIN users AS u ON (pt.user_assigned=u.user_id)
											GROUP by user_id
											ORDER BY user_name";
										echo "<select name='FtAss' onchange='submit()' $dis class='text'>";
										echo "<option value=''>".$AppUI->_('All')."</option>";
										$rc=mysql_query($sql);
										while ($vec=mysql_fetch_array($rc)){
											IF ($_POST['FtAss']==$vec['user_id']) $sel='SELECTED';
											ELSE $sel='';
											ECHO "<option $sel value='".$vec['user_id']."' $sel>".$vec['user_name']."</option>";
										}
										?>
									</select>
								</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>	
			</tr>
			</tbody>
			</table>
		</td>
	</tr>
	</tbody>
	</table>
	</form>
	<?php
}

function can_report($user_id,$todo_id){
	$sql = "SELECT user_assigned, user_owner FROM project_todo WHERE id_todo = $todo_id";
	$todo_data = mysql_fetch_array(mysql_query($sql));
	if($user_id == $todo_data['user_assigned'] || $user_id == $todo_data['user_owner']) return true;
	else return false;
}
?>
