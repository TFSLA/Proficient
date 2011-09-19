<?php

require_once( 'core.php' );

$t_core_path = config_get( 'core_path' );

require_once( $t_core_path.'bug_api.php' );
require_once( $t_core_path.'custom_field_api.php' );
require_once( $t_core_path.'compress_api.php' );
require_once( $t_core_path.'current_user_api.php' );
require_once( $t_core_path.'file_api.php' );
require_once( $t_core_path.'date_api.php' );
require_once( $t_core_path.'relationship_api.php' );

// Script para vincular un item de la base de conocimientos a una incidencia

//echo "<pre>"; print_r($_POST); echo "</pre>";

$f_bug_id	= gpc_get_int( 'p_bug_id' );
$t_bug = bug_prepare_display( bug_get( $f_bug_id, true ) );

$date = date('Y-m-d H:i:s');

$bug_c = strlen($f_bug_id);
$url_bug = str_repeat('0',7-$bug_c).$f_bug_id;

$title = "[$url_bug]  ".$t_bug->summary;

# Armo el cuerpo del articulo
$body  = "<b>".lang_get( 'description' )." : </b><br>".$t_bug->description;

$t_bugnote_table		= config_get( 'mantis_bugnote_table' );
$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
$t_bugnote_order		= config_get( 'bugnote_order' );

# get the bugnote data
$query = "SELECT *,UNIX_TIMESTAMP(date_submitted) as date_submitted
			FROM $t_bugnote_table
			WHERE bug_id='$f_bug_id' $t_restriction
			ORDER BY date_submitted $t_bugnote_order";
$result = db_query( $query );
$num_notes = db_num_rows( $result );

if($num_notes >0)
{
$body .= "<br><br>";
$body .= "<table  cellspacing=2 cellpadding=5 style=\"border: #000000 1px solid ; width: 80%;\">";
$body .= "<tr><td colspan=\"2\"><b>".lang_get( 'bug_notes_title').":</b></td></tr>";

            for ( $i=0; $i < $num_notes; $i++ ) {
		# prefix all bugnote data with v3_
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v3' );
		$v3_date_submitted = format_date( config_get( 'normal_date_format' ), ( $v3_date_submitted ) );

		# grab the bugnote text and id and prefix with v3_
		$query = "SELECT note
				FROM $t_bugnote_text_table
				WHERE id='$v3_bugnote_text_id'";
		$result2 = db_query( $query );
		$row = db_fetch_array( $result2 );

		$v3_note = $row['note'];
                        $user_rep = user_get_name( $v3_reporter_id );

		$body .= "<tr bgcolor=\"#E9E9E9\" style=\"vertical-align: top;\"><td width=\"150px\">";
		$body .= $user_rep."<br>".$v3_date_submitted."</td>";
		$body .= "<td>".$v3_note."</td></tr>";

            }

            $body .= "</table>";

}

# Verifico si ya existe el articulo de ser asi lo edito
$query_art = "SELECT count(article_id) FROM articles WHERE bug_id='".$f_bug_id."' ";
$result_art = db_query( $query_art );
$exist_art =  db_result( $result_art );

# Ingreso articulo nuevo
if($exist_art == 0 ){
$query_insert = "INSERT INTO articles (articlesection_id,
					file_category,
					date,
					articles_reads,
					user_id,
					title,
					body,
					type,
					article_comments,
					project,
					task,
					bug_id
					)
			         VALUES  ( '0',
					'0',
					'$date',
					'0',
					'".$AppUI->user_id."',
					'$title',
					'$body',
					'0',
					'0',
					'".$t_bug->project_id."',
					'".$t_bug->task_id."',
					'".$f_bug_id."'
					)
                         ";

$sql = db_query($query_insert);
$t_art_id = mysql_insert_id();

# Creado el articulo lo relaciono con la incidencia
$query_v = "INSERT INTO btpsa_bug_kb (project_id,
					    bug_id,
					    kb_type,
					    kb_section,
					    kb_item)
			              VALUES ('".$t_bug->project_id."',
					    '".$f_bug_id."',
					    '0',
					    '0',
					    '".$t_art_id."')";
$sql_v = db_query($query_v);
}else{

    # Si  esta el id de la incidencia en la tabla de articulos, entoces ya fue publicado y lo edito
    $query_update = "UPDATE articles  SET
					date_modified = '$date',
					title = '$title',
					body = '$body'
					WHERE bug_id ='".$f_bug_id."'  ";
    $sql_update = db_query($query_update);
}

$AppUI->redirect('m=webtracking&a=bug_view_page&bug_id='.$url_bug);

?>