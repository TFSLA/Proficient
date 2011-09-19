<?php
global $AppUI;
require_once( $AppUI->getLibraryClass( 'PEAR/Date' ) );

	###########################################################################
	# Date API
	###########################################################################

	# --------------------
	# prints the date given the formating string
	function print_date( $p_format, $p_date=NULL ) {
		echo format_date($p_format, $p_date);
	}

	# --------------------
	# Format the date given the formating string
	function format_date( $p_format, $p_date=NULL ) {
		//Impresion de fecha compatible con la clase PEAR/DATE
		if (!is_null($p_date)){
			$strdate=date( 'Y-m-d H:i:s T', $p_date );
		}else{
			$strdate=date( 'Y-m-d H:i:s T' );
		}
		$obj = new CDate($strdate);
		return $obj ? $obj->format( $p_format ) : '-';
		
		//Impresion de fecha original
		//echo date( $p_format, $p_date );
	}
	# --------------------
	function print_month_option_list( $p_month=0 ) {
		for ($i=1; $i<=12; $i++) {
			//$month_name  = date( 'F', mktime(0,0,0,$i,1,2000) );
			$month_name  = date( 'F', mktime_fix(0,0,0,$i,1,2000) );
			if ( $i == $p_month ) {
				PRINT "<option value=\"$i\" selected=\"selected\">$month_name</option>";
			} else {
				PRINT "<option value=\"$i\">$month_name</option>";
			}
		}
	}
	# --------------------
	function print_day_option_list( $p_day=0 ) {
		for ($i=1; $i<=31; $i++) {
			if ( $i == $p_day ) {
				PRINT "<option value=\"$i\" selected=\"selected\"> $i </option>";
			} else {
				PRINT "<option value=\"$i\"> $i </option>";
			}
		}
	}
	# --------------------
	function print_year_option_list( $p_year=0 ) {
		$current_year = date( "Y" );

		for ($i=$current_year; $i>1999; $i--) {
			if ( $i == $p_year ) {
				PRINT "<option value=\"$i\" selected=\"selected\"> $i </option>";
			} else {
				PRINT "<option value=\"$i\"> $i </option>";
			}
		}
	}
	# --------------------
?>