<?php
ob_start();

error_reporting( E_PARSE | E_CORE_ERROR | E_WARNING );

// If you experience a 'white screen of death' or other problems,
// uncomment the following line of code:
error_reporting( E_ALL & ~E_NOTICE );

// required includes for start-up

//print_r( $_GET );
$invitation = $_GET["invitation"];
$action = $_GET["action"];

if ( $action == "accept" || $action == "reject" )
{	
	$dPconfig = array();
	require_once( "../../includes/config.php" );
	require_once( "../../includes/db_mysql.php" );
	require_once( "invitation_en.inc" );
	
	db_connect( $dPconfig["dbhost"], $dPconfig["dbname"], $dPconfig["dbuser"], $dPconfig["dbpass"] );
	$invitation=substr($invitation, 0, 33);
	//echo "$invitation";
	$sql = "SELECT * FROM events_invitations WHERE invitation_hash = '$invitation'";	
		
	if ( $res = db_exec( $sql ) )
	{
		if ( $row = db_fetch_array( $res ) )
		{	
			require_once( "invitation_".$row["invitation_locale"].".inc" );
			
			$status = ($action == "accept" ? "ACCEPTED" : "REJECTED" );
			$sql = "UPDATE events_invitations SET invitation_status = '$status', invitation_hash = NULL WHERE invitation_id = ".$row["invitation_id"];
			
			if ( db_exec( $sql ) )
			{				
				?>
				<p><?=$exito?></p>
				<?
			}
			else
			{
				?>
				<p><?=$fracaso?></p>
				<?
			}			
		}		
		else
		{
			?>
			<p><?=$invInexistente?></p>
			<?
		}		
		db_free_result( $res );
	}	
	else
	{
		?>
		<p><?=$fracaso?></p>
		<?
	}
}
else
{
	?>
	<p><?=$accionDesconocida?></p>
	<?
}
?>
<p><a href="#" onClick="window.close();"><?=$cerrar?></a></p>
<?
ob_end_flush();
?>
