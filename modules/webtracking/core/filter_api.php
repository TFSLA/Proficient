<?php

	//$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	$t_core_dir = $AppUI->getConfig("root_dir").DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."webtracking".DIRECTORY_SEPARATOR."core".DIRECTORY_SEPARATOR;
	
	require_once( $t_core_dir . 'current_user_api.php' );

	###########################################################################
	# Filter API
	###########################################################################

	# @@@ Had to make all these parameters required because we can't use
	#  call-time pass by reference anymore.  I really preferred not having
	#  to pass all the params in if you didn't want to, but I wanted to get
	#  rid of the errors for now.  If we can think of a better way later
	#  (maybe return an object) that would be great.
	#
	# $p_page_numer
	#   - the page you want to see (set to the actual page on return)
	# $p_per_page
	#   - the number of bugs to see per page (set to actual on return)
	#     -1   indicates you want to see all bugs
	#     null indicates you want to use the value specified in the filter
	# $p_page_count
	#   - you don't need to give a value here, the number of pages will be
	#     stored here on return
	# $p_bug_count
	#   - you don't need to give a value here, the number of bugs will be
	#     stored here on return
	function filter_get_bug_rows( &$p_page_number, &$p_per_page, &$p_page_count, &$p_bug_count ) {
		$t_bug_table			= config_get( 'mantis_bug_table' );
		$t_bug_text_table		= config_get( 'mantis_bug_text_table' );
		$t_bugnote_table		= config_get( 'mantis_bugnote_table' );
		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
		$t_project_table		= config_get( 'mantis_project_table' );
		$t_limit_reporters		= config_get( 'limit_reporters' );
		$t_report_bug_threshold		= config_get( 'report_bug_threshold' );

		$t_filter = current_user_get_bug_filter();
                       
		if ( false === $t_filter ) {
			
			return false; # signify a need to create a cookie
			#@@@ error instead?
		}

		$t_project_id	= helper_get_current_project();
		$t_task_id	= helper_get_current_task();
		$t_user_id		= auth_get_current_user_id();

		$t_where_clauses = array( "$t_project_table.enabled = 1", "$t_project_table.project_id = $t_bug_table.project_id " );
		$t_select_clauses = array( "$t_bug_table.*" );
		$t_from_clauses = array( $t_bug_table, $t_project_table );
		$t_join_clauses = array();
                     
		if ( ALL_PROJECTS == $t_project_id ) {
			if ( ! current_user_is_administrator() ) {
				$t_projects = current_user_get_accessible_projects();

				if ( 0 == sizeof( $t_projects ) ) {
					return array();  # no accessible projects, return an empty array
				} else {
					$t_clauses = array();

					#@@@ use project_id IN (1,2,3,4) syntax if we can
					for ( $i=0 ; $i < sizeof( $t_projects ) ; $i++) {
						array_push( $t_clauses, "($t_bug_table.project_id='$t_projects[$i]')" );
					}

					array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
				}
			}
		} else {
			access_ensure_project_level( VIEWER, $t_project_id );

			array_push( $t_where_clauses, "($t_bug_table.project_id='$t_project_id')" );
		}

		# private bug selection
		if ( ! access_has_project_level( config_get( 'private_bug_threshold' ) ) ) {
			$t_public = VS_PUBLIC;
			$t_private = VS_PRIVATE;
			array_push( $t_where_clauses, "($t_bug_table.view_state='$t_public' OR ($t_bug_table.view_state='$t_private' AND $t_bug_table.reporter_id='$t_user_id'))" );
		}

		# reporter
		if ( 'any' != $t_filter['reporter_id'] ) {
			$c_reporter_id = db_prepare_int( $t_filter['reporter_id'] );
			array_push( $t_where_clauses, "($t_bug_table.reporter_id='$c_reporter_id')" );
		}

		# limit reporter
		if ( ( ON === $t_limit_reporters ) && ( current_user_get_access_level() <= $t_report_bug_threshold ) ) {
			$c_reporter_id = db_prepare_int( auth_get_current_user_id() );
			array_push( $t_where_clauses, "($t_bug_table.reporter_id='$c_reporter_id')" );
		}

		# handler
		if ( 'none' == $t_filter['handler_id'] ) {
			array_push( $t_where_clauses, "$t_bug_table.handler_id=0" );
		} else if ( 'any' != $t_filter['handler_id'] ) {
			$c_handler_id = db_prepare_int( $t_filter['handler_id'] );
			array_push( $t_where_clauses, "($t_bug_table.handler_id='$c_handler_id')" );
		}

		# hide closed
		if ( ( 'on' == $t_filter['hide_closed'] ) && ( CLOSED != $t_filter['show_status'] ) ) {
			$t_closed = CLOSED;
			array_push( $t_where_clauses, "($t_bug_table.status<>'$t_closed')" );
		}

		# hide resolved
		if ( ( 'on' == $t_filter['hide_resolved'] ) && ( RESOLVED != $t_filter['show_status'] ) ) {
			$t_resolved = RESOLVED;
			array_push( $t_where_clauses, "($t_bug_table.status<>'$t_resolved')" );
		}

		# category
		if ( 'any' != $t_filter['show_category'] ) {
			$c_show_category = db_prepare_string( $t_filter['show_category'] );
			array_push( $t_where_clauses, "($t_bug_table.category='$c_show_category')" );
		}

		# severity
		if ( 'any' != $t_filter['show_severity'] ) {
			$c_show_severity = db_prepare_string( $t_filter['show_severity'] );
			array_push( $t_where_clauses, "($t_bug_table.severity='$c_show_severity')" );
		}

		# status
		if ( 'any' != $t_filter['show_status'] ) {
			$c_show_status = db_prepare_string( $t_filter['show_status'] );
			array_push( $t_where_clauses, "($t_bug_table.status='$c_show_status')" );
		}
		
		# deadline
		$date_deadline = $t_filter['date_deadline']!= '' ? new CDate($t_filter['date_deadline']) : NULL ;
		if ( '' != $t_filter['deadline_rel']  && !is_null($date_deadline)) {
			$c_show_deadline = db_prepare_string( $t_filter['date_deadline'] );
			$where = "(($t_bug_table.date_deadline ".$t_filter['deadline_rel']." '$c_show_deadline')";
			//$where .= " OR ($t_bug_table.date_deadline is null)";
			$where .= " )";
			array_push( $t_where_clauses, $where );
		}	


		$date_from = $t_filter['date_from']!= '' ? new CDate($t_filter['date_from']) : NULL ;
		if ( '' != $t_filter['date_from_rel']  && !is_null($date_from)) {
			$c_show_from = db_prepare_string( $t_filter['date_from'] );
			$where = "(($t_bug_table.date_submitted ".$t_filter['date_from_rel']." '$c_show_from')";
			$where .= " )";
			array_push( $t_where_clauses, $where );
		}	


		$date_to = $t_filter['date_to']!= '' ? new CDate($t_filter['date_to']) : NULL ;
		if ( '' != $t_filter['date_to_rel']  && !is_null($date_to)) {
			$c_show_to = db_prepare_string( $t_filter['date_to'] );
			$where = "(($t_bug_table.date_submitted ".$t_filter['date_to_rel']." '$c_show_to')";
			$where .= " )";
			array_push( $t_where_clauses, $where );
		}	



		//Muestro los que se cambiaron en las últimas N horas
		if ( $t_filter['show_n_hours']>0 ) {
			$c_show_n_hours = db_prepare_string( $t_filter['show_n_hours'] );
			$year=date("Y");
			$month=date("m");
			$day=date("d");
			$hour=date("H");
			$minute=date("i");
			$fecha=date("Y-m-d H:i:s", mktime($hour-$c_show_n_hours, $minute, 0, $month, $day, $year));
			array_push( $t_where_clauses, "($t_bug_table.last_updated>='$fecha')" );
		}

		//echo "<br>".$t_filter['date_to']."<br>";
		//echo "<br>$where<br>";
       
	    
		# product version
		if (( $t_filter['show_version'] != "any")&&($t_filter['show_version'] != "")){

			if ($t_filter['show_version'] == "na"){
			$c_show_version = db_prepare_string( $t_filter['show_version'] );
			array_push( $t_where_clauses, "($t_bug_table.version='')" );
			}
			else{
			$c_show_version = db_prepare_string( $t_filter['show_version'] );
			array_push( $t_where_clauses, "($t_bug_table.version='$c_show_version')" );
			}
		}		

		
		
		# Simple Text Search - Thnaks to Alan Knowles
		if ( !is_blank( $t_filter['search'] ) ) {
			$c_search = db_prepare_string( $t_filter['search'] );
			array_push( $t_where_clauses,
							"((summary LIKE '%$c_search%')
							 OR ($t_bug_text_table.description LIKE '%$c_search%')
							 OR ($t_bug_text_table.steps_to_reproduce LIKE '%$c_search%')
							 OR ($t_bug_text_table.additional_information LIKE '%$c_search%')
							 OR ($t_bug_table.id LIKE '%$c_search%')
							 OR ($t_bugnote_text_table.note LIKE '%$c_search%'))" );
			array_push( $t_where_clauses, "($t_bug_text_table.id = $t_bug_table.bug_text_id)" );

			$t_from_clauses = array();
			$t_from_clauses[0] = $t_project_table;
			$t_from_clauses[1] = $t_bug_text_table;
			$t_from_clauses[2] = $t_bug_table;
			
			array_push( $t_join_clauses, "LEFT JOIN $t_bugnote_table ON $t_bugnote_table.bug_id = $t_bug_table.id" );

			array_push( $t_join_clauses, "LEFT JOIN $t_bugnote_text_table ON $t_bugnote_text_table.id = $t_bugnote_table.bugnote_text_id" );
			
		}

		if($t_task_id >0)
		{
			array_push( $t_where_clauses, "($t_bug_table.task_id = '$t_task_id' )" );
		}
		
		$t_select	= implode( ', ', array_unique( $t_select_clauses ) );
		$t_from		= 'FROM ' . implode( ', ', array_unique( $t_from_clauses ) );
		$t_join		= implode( ' ', $t_join_clauses );
		if ( sizeof( $t_where_clauses ) > 0 ) {
			$t_where	= 'WHERE ' . implode( ' AND ', $t_where_clauses );
		} else {
			$t_where	= '';
		}
		
		# Get the total number of bugs that meet the criteria.
		$query = "SELECT COUNT( $t_bug_table.id ) as count $t_from $t_join $t_where";
		$result = db_query( $query );
		$bug_count = db_result( $result );
		
		# write the value back in case the caller wants to know
		$p_bug_count = $bug_count;

		if ( null === $p_per_page ) {
			$p_per_page = (int)$t_filter['per_page'];
		} else if ( -1 == $p_per_page ) {
			$p_per_page = $bug_count;
		}

		# Guard against silly values of $f_per_page.
		if ( 0 == $p_per_page ) {
			$p_per_page = 1;
		}
		$p_per_page = (int)abs( $p_per_page );


		# Use $bug_count and $p_per_page to determine how many pages
		# to split this list up into.
		# For the sake of consistency have at least one page, even if it
		# is empty.
		$t_page_count = ceil($bug_count / $p_per_page);
		if ( $t_page_count < 1 ) {
			$t_page_count = 1;
		}

		# write the value back in case the caller wants to know
		$p_page_count = $t_page_count;

		# Make sure $p_page_number isn't past the last page.
		if ( $p_page_number > $t_page_count ) {
			$p_page_number = $t_page_count;
		}

		# Make sure $p_page_number isn't before the first page
		if ( $p_page_number < 1 ) {
			$p_page_number = 1;
		}
		$query2  = "SELECT DISTINCT $t_select, UNIX_TIMESTAMP(btpsa_bug_table.last_updated) as last_updated
					$t_from
					$t_join
					$t_where";

		# Now add the rest of the criteria i.e. sorting, limit.
		$c_sort = db_prepare_string( $t_filter['sort'] );
		
		if ( 'DESC' == $t_filter['dir'] ) {
			$c_dir = 'DESC';
		} else {
			$c_dir = 'ASC';
		}

		$query2 .= " ORDER BY $c_sort $c_dir";
        
		# Figure out the offset into the db query
		#
		# for example page number 1, per page 5:
		#     t_offset = 0
		# for example page number 2, per page 5:
		#     t_offset = 5
		$c_per_page = db_prepare_int( $p_per_page );
		$c_page_number = db_prepare_int( $p_page_number );
		$t_offset = ( ( $c_page_number - 1 ) * $c_per_page );

		$query2 .= " LIMIT $t_offset, $c_per_page";

		//echo "<pre>"; print_r($query2); echo "</pre>";
		# perform query
		$result2 = db_query( $query2 );
        
		$row_count = db_num_rows( $result2 );

		$rows = array();

		for ( $i=0 ; $i < $row_count ; $i++ ) {
			array_push( $rows, db_fetch_array( $result2 ) );
		}

		return $rows; 
	}

	# --------------------
	# return true if the filter cookie exists and is of the correct version,
	#  false otherwise
	function filter_is_cookie_valid() {
		$t_view_all_cookie = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );

		# check to see if the cookie does not exist
		if ( is_blank( $t_view_all_cookie ) ) {
			return false;
		}

		# check to see if new cookie is needed
		$t_setting_arr 			= explode( '#', $t_view_all_cookie );
		if ( $t_setting_arr[0] != config_get( 'cookie_version' ) ) {
			return false;
		}

		return true;
	}

	
	
	function filter_get_bug_rows_filter( &$p_page_number, &$p_per_page, &$p_page_count, &$p_bug_count, $t_filter ) {
		$t_bug_table			= config_get( 'mantis_bug_table' );
		$t_bug_text_table		= config_get( 'mantis_bug_text_table' );
		$t_bugnote_table		= config_get( 'mantis_bugnote_table' );
		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
		$t_project_table		= config_get( 'mantis_project_table' );
		$t_limit_reporters		= config_get( 'limit_reporters' );
		$t_report_bug_threshold		= config_get( 'report_bug_threshold' );

		if ( false === $t_filter ) {
			return false; # signify a need to create a cookie
			#@@@ error instead?
		}
		
		$t_project_id	= helper_get_current_project();
		$t_user_id		= auth_get_current_user_id();

		$t_where_clauses = array( 	"$t_project_table.enabled = 1", 
									"$t_project_table.project_id = $t_bug_table.project_id" );
		$t_select_clauses = array( "$t_bug_table.*" );
		$t_from_clauses = array( $t_bug_table, $t_project_table );
		$t_join_clauses = array();

		if ($t_filter['start_month'].$t_filter['start_day'].$t_filter['start_year']=!""){
			if (checkdate( intval($t_filter['start_month']),intval($t_filter['start_day']),intval($t_filter['start_year']))){
				$t_start_date = $t_filter['start_year'].$t_filter['start_month'].$t_filter['start_day']." 00:00:00";
			}
		}
		if ($t_filter['end_month'].$t_filter['end_day'].$t_filter['end_year']=!""){
			if (checkdate(intval($t_filter['end_month']),intval($t_filter['end_day']),intval($t_filter['end_year']))){
				$t_end_date = $t_filter['end_year'].$t_filter['end_month'].$t_filter['end_day']." 23:59:59";
			}
		}
		
		if ( ALL_PROJECTS == $t_project_id ) {
			if ( ! current_user_is_administrator() ) {
				$t_projects = current_user_get_accessible_projects();

				if ( 0 == sizeof( $t_projects ) ) {
					return array();  # no accessible projects, return an empty array
				} else {
					$t_clauses = array();

					#@@@ use project_id IN (1,2,3,4) syntax if we can
					for ( $i=0 ; $i < sizeof( $t_projects ) ; $i++) {
						array_push( $t_clauses, "($t_bug_table.project_id='$t_projects[$i]')" );
					}

					array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
				}
			}
		} else {
			access_ensure_project_level( VIEWER, $t_project_id );

			array_push( $t_where_clauses, "($t_bug_table.project_id='$t_project_id')" );
		}

		# private bug selection
		if ( ! access_has_project_level( config_get( 'private_bug_threshold' ) ) ) {
			$t_public = VS_PUBLIC;
			$t_private = VS_PRIVATE;
			array_push( $t_where_clauses, "($t_bug_table.view_state='$t_public' OR ($t_bug_table.view_state='$t_private' AND $t_bug_table.reporter_id='$t_user_id'))" );
		}

		# reporter
		if ( 'any' != $t_filter['reporter_id'] ) {
			$c_reporter_id = db_prepare_int( $t_filter['reporter_id'] );
			array_push( $t_where_clauses, "($t_bug_table.reporter_id='$c_reporter_id')" );
		}

		# limit reporter
		if ( ( ON === $t_limit_reporters ) && ( current_user_get_access_level() <= $t_report_bug_threshold ) ) {
			$c_reporter_id = db_prepare_int( auth_get_current_user_id() );
			array_push( $t_where_clauses, "($t_bug_table.reporter_id='$c_reporter_id')" );
		}

		# handler
		if ( 'none' == $t_filter['handler_id'] ) {
			array_push( $t_where_clauses, "$t_bug_table.handler_id=0" );
		} else if ( 'any' != $t_filter['handler_id'] ) {
			$c_handler_id = db_prepare_int( $t_filter['handler_id'] );
			array_push( $t_where_clauses, "($t_bug_table.handler_id='$c_handler_id')" );
		}

		# hide closed
		if ( ( 'on' == $t_filter['hide_closed'] ) && ( CLOSED != $t_filter['show_status'] ) ) {
			$t_closed = CLOSED;
			array_push( $t_where_clauses, "($t_bug_table.status<>'$t_closed')" );
		}

		# hide resolved
		if ( ( 'on' == $t_filter['hide_resolved'] ) && ( RESOLVED != $t_filter['show_status'] ) ) {
			$t_resolved = RESOLVED;
			array_push( $t_where_clauses, "($t_bug_table.status<>'$t_resolved')" );
		}

		# category
		if ( 'any' != $t_filter['show_category'] ) {
			$c_show_category = db_prepare_string( $t_filter['show_category'] );
			array_push( $t_where_clauses, "($t_bug_table.category='$c_show_category')" );
		}

		# severity
		if ( 'any' != $t_filter['show_severity'] ) {
			$c_show_severity = db_prepare_string( $t_filter['show_severity'] );
			array_push( $t_where_clauses, "($t_bug_table.severity='$c_show_severity')" );
		}

		# status
		if ( 'any' != $t_filter['show_status'] ) {
			$c_show_status = db_prepare_string( $t_filter['show_status'] );
			array_push( $t_where_clauses, "($t_bug_table.status='$c_show_status')" );
		}
		
		# start date
		if ($t_start_date){
			array_push( $t_where_clauses, "($t_bug_table.last_updated >= '$t_start_date')" );
		}
			
		# end date
		if ($t_end_date){
			array_push( $t_where_clauses, "($t_bug_table.last_updated <= '$t_end_date')" );
		}		

		
		# Simple Text Search - Thnaks to Alan Knowles
		if ( !is_blank( $t_filter['search'] ) ) {
			$c_search = db_prepare_string( $t_filter['search'] );
			array_push( $t_where_clauses,
							"((summary LIKE '%$c_search%')
							 OR ($t_bug_text_table.description LIKE '%$c_search%')
							 OR ($t_bug_text_table.steps_to_reproduce LIKE '%$c_search%')
							 OR ($t_bug_text_table.additional_information LIKE '%$c_search%')
							 OR ($t_bug_table.id LIKE '%$c_search%')
							 OR ($t_bugnote_text_table.note LIKE '%$c_search%'))" );
			array_push( $t_where_clauses, "($t_bug_text_table.id = $t_bug_table.bug_text_id)" );

			array_push( $t_from_clauses, $t_bug_text_table );

			array_push( $t_join_clauses, "LEFT JOIN $t_bugnote_table ON $t_bugnote_table.bug_id = $t_bug_table.id" );

			array_push( $t_join_clauses, "LEFT JOIN $t_bugnote_text_table ON $t_bugnote_text_table.id = $t_bugnote_table.bugnote_text_id" );
		}

		$t_select	= implode( ', ', array_unique( $t_select_clauses ) );
		$t_from		= 'FROM ' . implode( ', ', array_unique( $t_from_clauses ) );
		$t_join		= implode( ' ', $t_join_clauses );
		if ( sizeof( $t_where_clauses ) > 0 ) {
			$t_where	= 'WHERE ' . implode( ' AND ', $t_where_clauses );
		} else {
			$t_where	= '';
		}

		# Get the total number of bugs that meet the criteria.
		$query = "SELECT COUNT( $t_bug_table.id ) as count $t_from $t_join $t_where";
		$result = db_query( $query );
		$bug_count = db_result( $result );

		# write the value back in case the caller wants to know
		$p_bug_count = $bug_count;

		if ( null === $p_per_page ) {
			$p_per_page = (int)$t_filter['per_page'];
		} else if ( -1 == $p_per_page ) {
			$p_per_page = $bug_count;
		}

		# Guard against silly values of $f_per_page.
		if ( 0 == $p_per_page ) {
			$p_per_page = 1;
		}
		$p_per_page = (int)abs( $p_per_page );


		# Use $bug_count and $p_per_page to determine how many pages
		# to split this list up into.
		# For the sake of consistency have at least one page, even if it
		# is empty.
		$t_page_count = ceil($bug_count / $p_per_page);
		if ( $t_page_count < 1 ) {
			$t_page_count = 1;
		}

		# write the value back in case the caller wants to know
		$p_page_count = $t_page_count;

		# Make sure $p_page_number isn't past the last page.
		if ( $p_page_number > $t_page_count ) {
			$p_page_number = $t_page_count;
		}

		# Make sure $p_page_number isn't before the first page
		if ( $p_page_number < 1 ) {
			$p_page_number = 1;
		}
		$query2  = "SELECT DISTINCT $t_select, UNIX_TIMESTAMP(last_updated) as last_updated
					$t_from
					$t_join
					$t_where";

		# Now add the rest of the criteria i.e. sorting, limit.
		$c_sort = db_prepare_string( $t_filter['sort'] );
		
		if ( 'DESC' == $t_filter['dir'] ) {
			$c_dir = 'DESC';
		} else {
			$c_dir = 'ASC';
		}

		$query2 .= " ORDER BY '$c_sort' $c_dir";

		# Figure out the offset into the db query
		#
		# for example page number 1, per page 5:
		#     t_offset = 0
		# for example page number 2, per page 5:
		#     t_offset = 5
		$c_per_page = db_prepare_int( $p_per_page );
		$c_page_number = db_prepare_int( $p_page_number );
		$t_offset = ( ( $c_page_number - 1 ) * $c_per_page );

		$query2 .= " LIMIT $t_offset, $c_per_page";

		# perform query
		$result2 = db_query( $query2 );

		$row_count = db_num_rows( $result2 );

		$rows = array();

		for ( $i=0 ; $i < $row_count ; $i++ ) {
			array_push( $rows, db_fetch_array( $result2 ) );
		}

		return $rows;
	}
	
?>
