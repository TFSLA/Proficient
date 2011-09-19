<?php
/**
* @package dotproject
* @subpackage core
* @license http://opensource.org/licenses/bsd-license.php BSD License
*/

require_once( $AppUI->getSystemClass( 'libmail' ) );

//
// New password code based oncode from Mambo Open Source Core
// www.mamboserver.com | mosforge.net
//
function sendNewPass() {
 global $AppUI, $dPconfig;

 $_live_site = $dPconfig['base_url'];
 $_sitename = $dPconfig['page_title'];

 // ensure no malicous sql gets past
 $checkusername = trim( dPgetParam( $_POST, 'checkusername', '') );
 $checkusername = db_escape( $checkusername );
 $confirmEmail = trim( dPgetParam( $_POST, 'checkemail', '') );
 $confirmEmail = strtolower( db_escape( $confirmEmail ) );

 $query = "SELECT user_id FROM users"
 . "\nWHERE user_username='$checkusername' AND LOWER(user_email)='$confirmEmail'"
 ;
 if (!($user_id = db_loadResult($query)) || !$checkusername || !$confirmEmail) {
  $AppUI->setMsg( 'Invalid username or email.', UI_MSG_ERROR );
  $AppUI->redirect();
 }
 
 $newpass = makePass();
 $message = $AppUI->_('sendpass0', UI_OUTPUT_RAW)." $checkusername ". $AppUI->_('sendpass1', UI_OUTPUT_RAW) . " $_live_site  ". $AppUI->_('sendpass2', UI_OUTPUT_RAW) ." $newpass ". $AppUI->_('sendpass3', UI_OUTPUT_RAW);
 $subject = "[$_sitename] ".$AppUI->_('sendpass4', UI_OUTPUT_RAW)." - $checkusername";
 
 $m= new Mail; // create the mail
 $m->From( $dPconfig['mailfrom'] );
 $m->To( $confirmEmail );
 $m->Subject( $subject );
 $m->Body( $message, isset( $GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : "" );	// set the body
 $m->Send();	// send the mail

 //$newpass = md5( $newpass );
 $sql = "UPDATE users SET user_password=md5('$newpass') WHERE user_id='$user_id'";
 $cur = db_exec( $sql );
 if (!$cur) {
  die("SQL error" . db_error());
 } else {
  $AppUI->setMsg( 'New User Password created and emailed to you' );
  $AppUI->redirect();
 }
}

function makePass(){
 $makepass="";
 $salt = "abchefghjkmnpqrstuvwxyz0123456789";
 srand((double)microtime()*1000000);
 $i = 0;
 while ($i <= 7) {
  $num = rand() % 33;
  $tmp = substr($salt, $num, 1);
  $makepass = $makepass . $tmp;
  $i++;
 }
 return ($makepass);
}
?>