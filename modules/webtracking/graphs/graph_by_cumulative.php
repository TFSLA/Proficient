<?php
	require_once( '../core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'graph_api.php' );
?>
<?php
	# Grab Data
	# ---

	$g_start_date = date( 'Y-m-d', strtotime("-1 Month"));

	# usort function
	function tmpcmp ($a, $b) {
	    if ($a == $b) return 0;
	    return ($a < $b) ? -1 : 1;
	}

	# get total bugs before a date
	function get_total_count_by_date( $p_date ) {
		$t_project_id = helper_get_current_project();

		$d_arr = explode( '/', $p_date );
		$p_date = $d_arr[2].'-'.$d_arr[0].'-'.$d_arr[1];
		$query = "SELECT COUNT(*)
				FROM mantis_bug_table
				WHERE date_submitted<='$p_date' AND
					project_id='$t_project_id'";
		$result = db_query ( $query );
		return db_result( $result, 0, 0 );
	}

	# get resolved bugs before a date
	function get_resolved_count_by_date( $p_date ) {
		$t_project_id = helper_get_current_project();

		$d_arr = explode( '/', $p_date );
		$p_date = $d_arr[2].'-'.$d_arr[0].'-'.$d_arr[1];
		$query = "SELECT COUNT(*)
				FROM mantis_bug_table
				WHERE last_updated<='$p_date' AND
					status='80' AND
					project_id='$t_project_id'";
		$result = db_query ( $query );
		return db_result( $result, 0, 0 );
	}

	# get closed bugs before a date
	function get_closed_count_by_date( $p_date ) {
		$t_project_id = helper_get_current_project();

		$d_arr = explode( '/', $p_date );
		$p_date = $d_arr[2].'-'.$d_arr[0].'-'.$d_arr[1];
		$query = "SELECT COUNT(*)
				FROM mantis_bug_table
				WHERE last_updated<='$p_date' AND
					status='90' AND
					project_id='$t_project_id'";
		$result = db_query ( $query );
		return db_result( $result, 0, 0 );
	}

	# -- start --

	$t_project_id = helper_get_current_project();

	$query = "SELECT status,
					UNIX_TIMESTAMP(date_submitted) as date_submitted,
					UNIX_TIMESTAMP(last_updated) as last_updated
			FROM mantis_bug_table
			WHERE project_id='$t_project_id' AND
					date_submitted>='$g_start_date'
			ORDER BY date_submitted ASC";
	$result = db_query( $query );
	$bug_count = db_num_rows( $result );

	$data_date_arr = array();

	while( $row = db_fetch_array( $result ) ) {
		extract( $row );

		if ( $status < 80 ) {
			$date_str = date( 'm/d/Y', $date_submitted );
		} else {
			$date_str = date( 'm/d/Y', $last_updated );
		}

		$data_date_arr[] = $date_str;
	}

	$counter = 0;
	while( $row = db_fetch_array( $result ) ) {
		extract( $row );
	}

	$data_date_arr_temp = array_unique( $data_date_arr );
	$data_date_arr = array();
	foreach( $data_date_arr_temp as $key => $val ) {
		$data_date_arr[] = $val;
	}
	usort( $data_date_arr, 'tmpcmp' );


	# total up open
	$data_open_count_arr = array();
	foreach( $data_date_arr as $val ) {
		$data_open_count_arr[] = get_total_count_by_date( $val );
	}

	# total up resolved
	$data_resolved_count_arr = array();
	foreach( $data_date_arr as $val ) {
		$data_resolved_count_arr[] = get_resolved_count_by_date( $val );
	}

	# total up closed
	$data_closed_count_arr = array();
	foreach( $data_date_arr as $val ) {
		$data_closed_count_arr[] = get_closed_count_by_date( $val );
	}

	foreach( $data_date_arr as $key => $val ) {
		$data_date_arr[$key] = $val.' ';
	}

	$proj_name = project_get_field( $t_project_id, 'name' );

	# Setup Graph
	# ---

	$graph = new Graph(800,600,"auto");
	$graph->img->SetMargin(40,20,40,90);

	$graph->img->SetAntiAliasing("white");
	$graph->SetScale("textlin");
	$graph->SetShadow();
	$graph->SetColor('whitesmoke');
	$graph->title->Set("Cumulative - New, Resolved and Closed: $proj_name");
	$graph->title->SetFont(FF_FONT1,FS_BOLD);

	$graph->xaxis->SetFont(FF_FONT1,FS_NORMAL,5);
	$graph->xaxis->SetTickLabels( $data_date_arr );
	$graph->xaxis->SetLabelAngle(90);

	$graph->legend->Pos(0.75, 0.2);

	# OPEN
	$p1 = new LinePlot($data_open_count_arr);
	$p1->mark->SetType(MARK_FILLEDCIRCLE);
	$p1->mark->SetFillColor("blue");
	$p1->mark->SetWidth(3);
	$p1->SetColor("blue");
	$p1->SetCenter();
	$p1->SetLegend("Total");
	$graph->Add($p1);

	# RESOLVED
	$p2 = new LinePlot($data_resolved_count_arr);
	$p2->mark->SetType(MARK_SQUARE);
	$p2->mark->SetFillColor("hotpink");
	$p2->mark->SetWidth(5);
	$p2->SetColor("hotpink");
	$p2->SetCenter();
	$p2->SetLegend("Resolved");
	$graph->Add($p2);

	# CLOSED
	$p3 = new LinePlot($data_closed_count_arr);
	$p3->mark->SetType(MARK_UTRIANGLE);
	$p3->mark->SetFillColor("yellow1");
	$p3->mark->SetWidth(6);
	$p3->SetColor("yellow1");
	$p3->SetCenter();
	$p3->SetLegend("Closed");
	$graph->Add($p3);

	$p1->value->Show();
	$p2->value->Show();
	$p3->value->Show();

	$p1->value->SetFont(FF_FONT1,FS_NORMAL,8);
	$p2->value->SetFont(FF_FONT1,FS_NORMAL,8);
	$p3->value->SetFont(FF_FONT1,FS_NORMAL,8);

	$p1->value->SetColor("black","darkred");
	$p2->value->SetColor("black","darkred");
	$p3->value->SetColor("black","darkred");

	$p1->value->SetFormat('%d');
	$p2->value->SetFormat('%d');
	$p3->value->SetFormat('%d');

	// Output line
	$graph->Stroke();
?>