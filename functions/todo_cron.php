<?php
/** \file todo_cron.php
 *	\brief Este modulo se encarga de enviar la notificacion automatica de los To Do`s con el siguiente formato:
 \htmlonly
<font color="red"><b>TO-DO NOTIFICACIÓN AUTOMÁTICA: ASIGNACIONES VENCIDAS</b><br><br>Personales:<br>- ayer 06/12<br><br>Proyecto: 000000<br>- ayer para proyecto 06/12<br></font> <font color="black"> <br><b>TO-DO NOTIFICACIÓN AUTOMÁTICA: VENCIMIENTO HOY </b><br><br>Personales:<br>- HOY 16/12 personal[ALTA]<br><br>Proyecto: 000000<br>- hoy proyecto 07/12<br></font> <font color="navy"><br><b>TO-DO NOTIFICACIÓN AUTOMÁTICA: VENCIMIENTO EL SIGUIENTE DÍA</b><br><br>Personales:<br>- mañana 8/12<br><br>Proyecto: 000000<br>- mañana proyecto 08/12<br></font>
 \endhtmlonly
 */
?>

<?php
include ('../includes/config.php');
require_once( "../includes/db_mysql.php");
include ('mail.php');


db_connect( $dPconfig["dbhost"], $dPconfig["dbname"], $dPconfig["dbuser"], $dPconfig["dbpass"] );

$sql="SELECT user_assigned as user , project_id , description, priority, due_date, status FROM project_todo AS pt
		INNER JOIN useralerts AS ua
		  ON  (pt.user_assigned=ua.user_id AND ua.emailalert_id=3)
		WHERE  status = '0' AND user_assigned != '0' AND ( due_date <= curdate() +1) AND due_date != '0000-00-00 00:00:00'
		UNION
		SELECT user, 0, description, priority, due_date, status FROM user_todo
		INNER JOIN useralerts AS ua
		  ON  (user=ua.user_id AND ua.emailalert_id=3)
		WHERE  status = '0' AND user          != '0' AND ( due_date <= curdate() +1) AND due_date != '0000-00-00 00:00:00'
		ORDER By user,due_date,project_id, priority";

$rc=db_exec($sql);
make_mails($rc, 'my');

$sql="SELECT user_assigned, pt.user_owner as user, CONCAT(u.user_last_name, ', ', u.user_first_name) AS assign, project_id , description, priority, due_date, status
		FROM project_todo AS pt
		INNER JOIN useralerts AS ua
		  ON  (pt.user_owner=ua.user_id AND ua.emailalert_id=4)
		INNER JOIN users AS u
		  ON  (pt.user_assigned=u.user_id)
		WHERE  status = '0' AND user_assigned != '0' AND ( due_date <= curdate() +1) AND due_date != '0000-00-00 00:00:00'
		ORDER By pt.user_owner, project_id, due_date, priority, user_assigned";

$rc=db_exec($sql);
make_mails($rc, 'sup');

FUNCTION make_mails($rc, $func){
	global $text_loc;
	WHILE ($vec=db_fetch_array($rc) )	{
		//echo "USUARIO:".$vec['user']."-".$vec['project_id']."-".$vec['description']."-".$vec['due_date']."<br>";
		IF ($usuario_anterior!= $vec['user']){
			$txt .= body_closetable();
			IF ($usuario_anterior!=''){
				IF ($func=='sup') $asunto = "[PROFICIENT]".trans("MyAssigToDos");
				ELSE $asunto = "[PROFICIENT]".trans("DelayToDos");
				enviar_mail($vec_mail['user_email'], $txt, $asunto);
			}
			$sql_mail="SELECT user_id, user_email, user_last_name FROM users WHERE user_id =".$vec['user'];
			$rc_mail=db_exec($sql_mail);
			$vec_mail=db_fetch_array($rc_mail);
			
			$sql_loc="SELECT pref_value FROM psa_beta.user_preferences u WHERE pref_user=".$vec['user']." AND pref_name='LOCALE';";
			$vec_loc=db_fetch_array(db_exec($sql_loc));
			$text_loc=locales($vec_loc['0'], 'todo_cron.inc', '../');
			
			
			$usuario_anterior = $vec['user'];
			$entre_vencidas = FALSE;
			$proyecto_ant='';
			//$dia = $vec['due_date'];
		}
		
		
		
		$vec['due_date'] = substr($vec['due_date'],0,10); // Le saco a la fecha la hora

		
		IF ( $dia!=$vec['due_date'] AND ($vec['due_date'] >= date("Y-m-d") OR $entre_vencidas!=TRUE)) {
			IF ( ($vec['due_date'] < date("Y-m-d") ) && (!$entre_vencidas)){
				$title='AssignmentsWithDelayedStatus';
				$entre_vencidas = TRUE;
			}
			ELSEIF ($vec['due_date'] == date("Y-m-d") ){
				$title='AssignmentsToBeCompletedToday';
				$proyecto_ant='';
				$dia = $vec['due_date'];
				$txt .= body_closetable();
			}
			ELSEIF ($vec['due_date'] == date("Y-m-d", time() + 86400) ) { // esto significa mañana
				$title='AssignmentsToBeCompletedTomorrow';
				$proyecto_ant='';
				$dia = $vec['due_date'];
				$txt .= body_closetable();
			}
			$column='';
			$column[]='Priority';
			$column[]='Description';
			IF ($func=='sup') $column[]='AssignedUser';	
			$column[]='DueDate';
			$txt.= body_newtable($title, $column);
		}

		$cols=count($column);	
		IF ($func=='sup'){
			$assign="<td>".$vec['assign']."</td>";
			$cols++;
		}
		
		IF ($proyecto_ant!=$vec['project_id'] ){
			IF ( $vec['project_id'] == 0 ) 
				$txt .= "<tr style='background-color: #878676; font-weight: bold; color: #FFFFFF'>
								<td colspan='$cols'>".trans('Personal')."</td>
							</tr>\n";
			ELSE {
				//Busco el nombre del proyecto
				$sql_proy="SELECT project_name, project_color_identifier FROM projects where project_id =".$vec['project_id'];
				$rc_proy=db_exec($sql_proy);
				$vec_proy=db_fetch_array($rc_proy);		
				$txt .= "<tr style='background-color: #878676; font-weight: bold; color: #FFFFFF'>
								<td colspan='$cols' >".trans('Project').": ".$vec_proy['project_name']. "</td>
							</tr>\n";
			}
			$proyecto_ant=$vec['project_id'];
		} 
		

		
		IF ( $vec['priority'] == 1 ) $prio = "High";
		ELSEIF ( $vec['priority'] == 3 ) $prio= "Low";
		ELSE 	$prio= "Normal";
		$txt .= "<tr>
						<td width='10%' align='center'>".trans($prio)."</td>
						<td>".$vec['description']."</td>
						$assign
						<td width='15%' align='center'>".substr($vec['due_date'],8,2)."-".substr($vec['due_date'],5,2)."-".substr($vec['due_date'],0,4)."</td>
					</tr>\n";			
	}
	IF ( $txt != '' ) { // Si es el ultimo usuario	
		IF ($func=='sup') $asunto = "[PROFICIENT]".trans("MyAssigToDos");
		ELSE $asunto = "[PROFICIENT]".trans("DelayToDos");
		enviar_mail($vec_mail['user_email'], $txt, $asunto);
		//echo "$txt";
		$txt = '';
	}
}

/**
 * \brief Esta funcion envia un mail.
 * Envia en mail al mail al destino en formato HTML.
 * \param destino: La direccion de mail a la que se enviara el mail.
 * \param txt: El texto a enviar.
 * \author Fede Ravizzini
 * \date 06/12/06
 * \version 1.0
 * \return No devuelve ningun valor.
 * \todo Hacer que devuelva algun valor para verificar afuera si pudo enviar el mail o no.
 * \warning Aca iria algun mensaje de alerta...
 * \bug Aca iria algun problema en particular que tenga esta funcion... 
 */
 


function body_newtable($title, $column){
	global $text_loc;
	$cols=count($column);
	$style="style='background-color:#FFFFFF; font-weight: bold; color: #000000; font-size: 16px;'";
	$body ="<font $style>".trans($title)."</font>";
	$body .="<table width='80%' border='1' cellSpacing='0' style='font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px; BORDER-RIGHT: #e0dfe3; PADDING-RIGHT: 3pt; BORDER-TOP: #e0dfe3; PADDING-LEFT: 3pt; PADDING-BOTTOM: 3pt; BORDER-LEFT: #e0dfe3; PADDING-TOP: 3pt; BORDER-BOTTOM: #e0dfe3;'>";
	reset($column);
	$style="style='background-color: #717062; font-weight: bold; color: #FFFFFF; text-align: center;'";
	$body .="<tr $style>";
	while($col_desc = current($column)){
   	 $body .="<td>".trans($col_desc)."</td>";
       next($column);
   }
	$body .="</tr>";
	return ($body);
}
function body_closetable(){
	$body="</table><br>";
	return ($body);
}

?>
