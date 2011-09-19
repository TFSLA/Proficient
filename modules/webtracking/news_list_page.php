<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'news_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path . 'date_api.php' );
?>
<?php
	access_ensure_project_level( VIEWER );
?>
<?php html_page_top1() ?>
<?php html_page_top2() ?>

<br />
<ul>
<?php
	# Select the news posts
	$rows = news_get_rows( helper_get_current_project() );
    # Loop through results
	for ( $i=0 ; $i < sizeof( $rows ) ; $i++ ) {
		extract( $rows[$i], EXTR_PREFIX_ALL, 'v' );
		if ( VS_PRIVATE == $v_view_state &&
			 ! access_has_project_level( config_get( 'private_news_threshold' ), $v_project_id ) ) 		{
			continue;
		}

		$v_headline 	= string_display( $v_headline );
		$v_date_posted 	= format_date( config_get( 'complete_date_format' ), $v_date_posted );

		$t_notes = array();
		$t_note_string = '';
		if ( 1 == $v_announcement ) {
			array_push( $t_notes, lang_get( 'announcement' ) );
		}
		if ( VS_PRIVATE == $v_view_state ) {
			array_push( $t_notes, lang_get( 'private' ) );
		}
		if ( sizeof( $t_notes ) > 0 ) {
			$t_note_string = '['.implode( ' ', $t_notes ).']';
		}

		echo "<li><span class=\"italic-small\">$v_date_posted</span> - <span class=\"bold\"><a href=\"index.php?m=webtracking&a=news_view_page&news_id=$v_id\">$v_headline</a></span> <span class=\"small\">$t_note_string ";
		
		print_user( $v_poster_id );
		
		echo "</span></li>";
	}  # end for loop
?>
</ul>

<?php html_page_bottom1( __FILE__ ) ?>
