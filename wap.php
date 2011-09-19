<? 
require_once( "./includes/config.php" );
require_once( "./classes/ui.class.php" );
require_once( "./includes/main_functions.php" );
ini_set("url_rewriter.tags","");

session_name( 'psa'.$dPconfig['instanceprefix'] );
if (get_cfg_var( 'session.auto_start' ) > 0) {
	session_write_close();
}

if(!empty($_REQUEST["sid"])) {
  $sid=$_REQUEST["sid"];
  session_id($sid);
  session_start();
}
else{
    session_start();
    $sid=session_id();
}

session_register( 'AppUI' ); 


if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout'])) {
    $_SESSION['AppUI'] = new CAppUI();
}
$AppUI =& $_SESSION['AppUI'];
$AppUI->setConfig( $dPconfig );

// load the db handler
require_once( "./includes/db_connect.php" );
require_once( "./misc/debug.php" );

if ($AppUI->doLogin()) {
    $AppUI->loadPrefs( 0 );
}

// load the commonly used classes
require_once( $AppUI->getSystemClass( 'date' ) );
require_once( $AppUI->getSystemClass( 'dp' ) );

require_once( "./includes/wapheader.php" );


// check if the user is trying to log in
if (isset($_POST['login'])) {
	$username = dPgetParam( $_POST, 'username', '' );
	$password = dPgetParam( $_POST, 'password', '' );
	$ok = $AppUI->login( $username, $password );
	if (!$ok) $badauth=1;
	else $firsttime=1;
}

if ($ok || !$AppUI->doLogin()) {
// User authenticated.

// bring in the rest of the support and localisation files
require_once( "./includes/permissions.php" );

// set the module and action from the url
/*
$m = dPgetParam( $_GET, 'm', getReadableModule() );
$u = dPgetParam( $_GET, 'u', '' );
$a = dPgetParam( $_GET, 'a', 'index' );
*/

$u = dPgetParam( $_GET, 'u', '' );
$m = dPgetParam( $_GET, 'm', 'nomrecord' );
if($m=="nomrecord"){

	?>


<?if($firsttime==1){
?>
	<card id="intro" title="PSA - Welcome" ontimer="#menu">
	<timer value="20"/>
	<p align="center"><br/><?php echo $AppUI->_('Welcome')." $AppUI->user_first_name $AppUI->user_last_name"; ?></p>
	</card>
<?}?>
	<card id="menu" title="PSA Wireless">
	<p mode="wrap"> 
	<a href="wap.php?sid=<?=$sid?>&amp;m=projects">Projects</a><br/>
	<a href="wap.php?sid=<?=$sid?>&amp;m=contacts">Contacts</a><br/>
	<a href="wap.php?sid=<?=$sid?>&amp;m=wmail">Email</a><br/>
	<a href="wap.php?sid=<?=$sid?>&amp;logout=-1">Logout</a><br/>
	</p>
	</card>
	<?
	  require_once( "./includes/wapfooter.php" );
	  die();
}
else
  $a = dPgetParam( $_GET, 'a', 'index' );

$canRead = !getDenyRead( $m );
$canEdit = !getDenyEdit( $m );
$canAuthor = $canEdit;
$canDelete = $canEdit;

@include_once( "./locales/$AppUI->user_locale/locales.php" );
@include_once( "./locales/core.php" );
setlocale( LC_TIME, $AppUI->user_locale );

// include the module class file
@include_once( $AppUI->getModuleClass( $m ) );
@include_once( "./modules/$m/" . ($u ? "$u/" : "") . "$u.class.php" );

// do some db work if dosql is set
if (isset( $_REQUEST["dosql"]) ) {
    require ("./modules/$m/" . $_REQUEST["dosql"] . ".php");
}

// start output proper
ob_start();

require "./modules/$m/" . ($u ? "$u/" : "") . "wap_$a.php";

require_once( "./includes/wapfooter.php" );
ob_end_flush();
die();
}

if (!$ok) {

    $AppUI->setUserLocale();
    // load basic locale settings
    @include_once( "./locales/$AppUI->user_locale/locales.php" );
    @include_once( "./locales/core.php" );

?>

<?
if($badauth!=1){
?>
<card id="intro" title="PSA - TFS" ontimer="#login">
<timer value="20"/>
<p align="center"><img src="images/tfs.wbmp" alt="TFS"/></p>
</card>
<?}?>

<?
if($badauth==1){
?>
<card id="intro" title="PSA - TFS" ontimer="#login">
<timer value="15"/>
<p align="center"> 
<b>PSA Wireless</b>
<br/><br/>
<? echo "<i>".$AppUI->_('Login Failed')."</i><br/>";?>
</p></card>
<?}?>

<card title="Login" id="login">

<p align="center"> 
<b>PSA Wireless</b>
<br/>
Username:  <input name="username" title="username" size="8"/><br/> 
Password:  <input type="password" title="password" name="password" size="8"/><br/>

<anchor>[<?php echo $AppUI->_('login');?> &gt;&gt;&gt;]
<go method="post" href="wap.php?sid=<?=$sid?>">
<postfield name="login" value="yes"/>
<postfield name="username" value="$(username)"/>
<postfield name="password" value="$(password)"/>
</go></anchor>
</p>

</card> 

<?

}

require_once( "./includes/wapfooter.php" );?>
