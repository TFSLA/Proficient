<?php /* STYLE/DEFAULT $Id: overrides.php,v 1.1 2009-05-19 21:15:50 pkerestezachi Exp $ */

class CTitleBlock extends CTitleBlock_core {
}

##
##  This overrides the show function of the CTabBox_core function
##
class CTabBox extends CTabBox_core {
	function show( $extra='' ) {
		GLOBAL $AppUI;
		$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];
		reset( $this->tabs );
		$s = '';
	// tabbed / flat view options
		if (@$AppUI->getPref( 'TABVIEW' ) == 0) {
			$s .= "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\" height=\"6\"><tr><td></td></tr></table>\n";
			$s .= "<table background=\"images/back_04.jpg\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\" height=\"30\">\n";
			$s .= "<tr>\n";
			$s .= "<td class=botonera2 align=right nowrap=\"nowrap\">";
			$s .= "<a class=botonera2 href=\"".$this->baseHRef."tab=0\">".$AppUI->_('tabbed')."</a> | ";
			$s .= "<a class=botonera2 href=\"".$this->baseHRef."tab=-1\">".$AppUI->_('flat')."</a>";
			$s .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n".$extra."\n</tr>\n</table>\n";
			$s .= "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\" bgcolor=white height=\"1\"><tr><td></td></tr></table>\n";
			$s .= "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\" height=\"6\"><tr><td></td></tr></table>\n";
			echo $s;
		} else {
			if ($extra) {
				echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\">\n<tr>\n".$extra."</tr>\n</table>\n";
			} else {
				echo "<img src=\"./images/shim.gif\" height=\"10\" width=\"1\" alt=\"\" />";
			}
		}

		if ($this->active < 0 && @$AppUI->getPref( 'TABVIEW' ) != 2 ) {
		// flat view, active = -1
			echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\">\n";
			foreach ($this->tabs as $v) {
				echo "<tr><td><strong>".$AppUI->_($v[1])."</strong></td></tr>\n";
				echo "<tr><td>";
				include $this->baseInc.$v[0].".php";
				echo "</td></tr>\n";
			}
			echo "</table>\n";
		} else {
		// tabbed view
			if ( $this->active < 0 || $this->active >= count($this->tabs) )
			{
				$this->active = 0;
			}
			$s = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
			$s .= '<tr><td><table border="0" cellpadding="0" cellspacing="0">';
			foreach( $this->tabs as $k => $v ) {
				$class = ($k == $this->active) ? 'tabon' : 'taboff';
				$sel = ($k == $this->active) ? 'Selected' : '';
				$s .= '<td height="28" valign="middle" width="3"><img src="./style/' . $uistyle . '/images/tab'.$sel.'Left.png" width="3" height="28" border="0" alt="" /></td>';
				$s .= '<td valign="middle" nowrap="nowrap"  background="./style/' . $uistyle . '/images/tab'.$sel.'Bg.png">&nbsp;<a href="'.$this->baseHRef.'tab='.$k.'">'.$AppUI->_($v[1]).'</a>&nbsp;</td>';
				$s .= '<td valign="middle" width="3"><img src="./style/' . $uistyle . '/images/tab'.$sel.'Right.png" width="3" height="28" border="0" alt="" /></td>';
				$s .= '<td width="3" class="tabsp"><img src="./images/shim.gif" height="1" width="3" /></td>';
			}
			$s .= '</table></td></tr>';
			$s .= '<tr><td width="100%" colspan="'.(count($this->tabs)*4 + 1).'" class="tabox">';
			echo $s;
			require $this->baseInc.$this->tabs[$this->active][0].'.php';
			echo '</td></tr></table>';
		}
	}
	
	function showtabbuttons(){
		GLOBAL $AppUI;
		$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];
		reset( $this->tabs );
		$s = '';


		// tabbed view
			if ( $this->active < 0 || $this->active >= count($this->tabs) )
			{
				$this->active = 0;
			}
			$s = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
			$s .= '<tr><td><table border="0" cellpadding="0" cellspacing="0">';
			$tab_unique = uniqid("tab");
			$j = 0;
			$jsscript = "var tabs = new Array(); ";			
			foreach( $this->tabs as $k => $v ) {
				$jsscript .= "tabs[$j] = '$tab_unique$j';\n ";
				$class = 'taboff';//($k == $this->active) ? 'tabon' : 'taboff';
				$sel = ''; //($k == $this->active) ? 'Selected' : '';
				$s .= '<td height="28" valign="middle" width="3"><img src="./style/' . $uistyle . '/images/tab'.$sel.'Left.png" width="3" height="28" border="0" alt="" /></td>';
				$s .= '<td valign="middle" nowrap="nowrap"  background="./style/' . $uistyle . '/images/tab'.$sel.'Bg.png">&nbsp;'."<a id=\"$tab_unique$j\" href=\"".$v[0]."\" onclick=\"javascript: tabbuttonchangestate('$tab_unique$j'); \"  >".$AppUI->_($v[1]).'</a>&nbsp;</td>';
				$s .= '<td valign="middle" width="3"><img src="./style/' . $uistyle . '/images/tab'.$sel.'Right.png" width="3" height="28" border="0" alt="" /></td>';
				$s .= '<td width="3" class="tabsp"><img src="./images/shim.gif" height="1" width="3" /></td>';
				$j++;
			}
			$s .= '</table></td></tr>';
			$s .= '<tr><td width="100%" colspan="'.(count($this->tabs)*4 + 1).'" class="tabox">';
			echo '<script language="JavaScript"> '."
				<!-- 
				function tabbuttonchangestate(tabname){
				$jsscript 
					var tabbutton = '';
					for (var i = 0; i < tabs.length; i++){
						tabbutton = document.getElementById(tabs[i]);
						if (tabbutton)
							if (tabs[i] == tabname){						
								document.getElementById(tabs[i]).style.fontWeight = 'bolder';
							}else{			
								document.getElementById(tabs[i]).style.fontWeight = 'normal';
							}
					}
				}
				tabbuttonchangestate('".$tab_unique."0');
				//-->
				</script>";
			echo $s.'</td></tr></table>';
			echo '<script language="JavaScript"> '."
				<!-- 
				tabbuttonchangestate('".$tab_unique."0');
				//-->
				</script>";

	}
}
?>