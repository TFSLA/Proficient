<?php

	###########################################################################
	# Language (Internationalization) API
	###########################################################################
	
	# Cache of localization strings in the language specified by the last 
	# lang_load call
	$g_lang_strings = array();
	
	# Currently loaded language
	$g_lang_current = '';
	
	# ------------------
	# Loads the specified language and stores it in $g_lang_strings,
	# to be used by lang_get
	function lang_load( $p_lang ) {
		global $g_lang_strings, $g_lang_current;
		
		if ( $g_lang_current == $p_lang ) {
			return;
		}
		global $AppUI;
		// define current language here so that when custom_strings_inc is
		// included it knows the current language
		$g_lang_current = $p_lang;


		$t_lang_dir = $AppUI->getConfig("root_dir").DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."webtracking".DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR;

		

		if($AppUI->user_locale=="en") $p_lang="english";
		if($AppUI->user_locale=="es") $p_lang="spanish";

		require_once( $t_lang_dir . 'strings_'.$p_lang.'.txt' );
		
		# Allow overriding strings declared in the language file.
		# custom_strings_inc.php can use $g_active_language
		$t_custom_strings = $AppUI->getConfig("root_dir").DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."webtracking".DIRECTORY_SEPARATOR . 'custom_strings_inc.php';
		if ( file_exists( $t_custom_strings ) ) {
			require_once( $t_custom_strings );
		}
		
		$t_vars = get_defined_vars();
		
		foreach ( array_keys( $t_vars ) as $t_var ) {
			$t_lang_var = ereg_replace( '^s_', '', $t_var );
			if ( $t_lang_var != $t_var || 'MANTIS_ERROR' == $t_var ) {
				$g_lang_strings[$t_lang_var] = $$t_var;
			}
		}
	}
	
	# ------------------
	# Loads the user's language or, if the database is unavailable, the default language
	function lang_load_default() {
		$t_cookie_string = gpc_get_cookie( config_get( 'string_cookie' ), '' );

		# Confirm that the user's language can be determined
		if ( db_is_connected() && !is_blank( $t_cookie_string ) ) {
			
			$t_mantis_user_pref_table 	= config_get( 'mantis_user_pref_table' );
			$t_mantis_user_table		= config_get( 'mantis_user_table' );
			
			$query = "SELECT DISTINCT language
					FROM $t_mantis_user_pref_table p, $t_mantis_user_table u
					WHERE u.cookie_string='$t_cookie_string' AND
							u.user_id=p.user_id";

			$result = db_query( $query );
			$t_active_language = db_result( $result, 0 , 0 );

			if ( false == $t_active_language ) {
				$t_active_language = config_get( 'default_language' );
			}
			
		} else {
			$t_active_language = config_get( 'default_language' );
		}
		
		lang_load( $t_active_language );
	}
	
	# ------------------
	# Ensures that a language file has been loaded
	function lang_ensure_loaded() {
		global $g_lang_current;
		
		# Load the language, if necessary
		if ( '' == $g_lang_current ) {
			lang_load_default();
		}
	}

	# ------------------
	# Retrieves an internationalized string
	#  This function will return one of (in order of preference):
	#    1. The string in the current user's preferred language (if defined)
	#    2. The string in English
	function lang_get( $p_string ) {
		global $g_lang_strings;
		
		lang_ensure_loaded();
		
		# note in the current implementation we always return the same value
		#  because we don't have a concept of falling back on a language.  The
		#  language files actually *contain* English strings if none has been
		#  defined in the correct language

		if ( lang_exists( $p_string ) ) {
			return $g_lang_strings[$p_string];
		} else {
			trigger_error( ERROR_LANG_STRING_NOT_FOUND, WARNING );
			return '';
		}
	}

	# ------------------
	# Check the language entry, if found return true, otherwise return false.
	function lang_exists( $p_string ) {
		global $g_lang_strings;
		
		lang_ensure_loaded();
		
		return ( isset( $g_lang_strings[$p_string] ) );
	}

	# ------------------
	# Get language:
	# - If found, return the appropriate string (as lang_get()).
	# - If not found, no default supplied, return the supplied string as is.
	# - If not found, default supplied, return default.
	function lang_get_defaulted( $p_string, $p_default = null ) {
		if ( lang_exists( $p_string) ) {
			return lang_get( $p_string );
		} else {
			if ( null === $p_default ) {
				return $p_string;
			} else {
				return $p_default;
			}
		}
	}
?>
