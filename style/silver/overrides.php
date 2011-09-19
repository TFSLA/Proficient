<?php /* STYLE/DEFAULT $Id: overrides.php,v 1.3 2009-06-25 16:23:43 pkerestezachi Exp $ */

class CTitleBlock extends CTitleBlock_core {
}

##
##  This overrides the show function of the CTabBox_core function
##
class CTabBox extends CTabBox_core 
{
	function show( $extra='' ) 
	{
		
		GLOBAL $AppUI;
		$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];
		reset( $this->tabs );
		$s = '';
		if ($extra) 
		{
			echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\">\n<tr>\n".$extra."</tr>\n</table>\n";
		}
		else
		{
			//echo "<img src=\"./images/shim.gif\" height=\"10\" width=\"1\" alt=\"\" />";
		}
		
		if ($this->active < 0 && @$AppUI->getPref( 'TABVIEW' ) != 2 ) 
		{
		// flat view, active = -1
			echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\">\n";
			foreach ($this->tabs as $v) {
				echo "<tr><td><strong>".$AppUI->_($v[1])."</strong></td></tr>\n";
				echo "<tr><td>";
				include $this->baseInc.$v[0].".php";
				echo "</td></tr>\n";
			}
			echo "</table>\n";
		}
		else
		{
		// tabbed view
			if ( $this->active < 0 || $this->active >= count($this->tabs) )
			{
				$this->active = 0;
			}
			
			$s = '<br/><div><div id="slidemenu" class="slidetabsmenu"><ul>';
			foreach( $this->tabs as $k => $v ) 
			{
				if ($v[2]==TRUE) //Si el 3 campo del vector es VERDADERO significa que tengo que mostrar la solapa! caso contrario no la muestro
				{
					if($k == $this->active)
						$s.="<li><a href=\"{$this->baseHRef}tab=$k\" style=\"text-Decoration='none';\" class=\"specialtab\"><font><b>".$AppUI->_($v[1])."</b></font></a></li>";
					else
						$s.="<li><a href=\"{$this->baseHRef}tab=$k\" style=\"text-Decoration='none';\" class=\"specialtab\"><font>".$AppUI->_($v[1])."</font></a></li>";	
				}
			}
			$s .= "</ul>";

			if(strrpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'safari') > 0)
				$s .= "<br/><br/>";
				
			echo $s;
			require $this->baseInc.$this->tabs[$this->active][0].'.php';
			echo("</div>");
		}
	}

	function showtabbuttons(  ) 
	{
		GLOBAL $AppUI;
		$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];
		reset( $this->tabs );
		$s = '';
		if ($extra) 
		{
			echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\">\n<tr>\n".$extra."</tr>\n</table>\n";
		}
		else
		{
			echo "<img src=\"./images/shim.gif\" height=\"10\" width=\"1\" alt=\"\" />";
		}
		
		if ($this->active < 0 && @$AppUI->getPref( 'TABVIEW' ) != 2 ) 
		{
		// flat view, active = -1
			echo "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\">\n";
			foreach ($this->tabs as $v) {
				echo "<tr><td><strong>".$AppUI->_($v[1])."</strong></td></tr>\n";
				echo "<tr><td>";
				include $this->baseInc.$v[0].".php";
				echo "</td></tr>\n";
			}
			echo "</table>\n";
		}
		else
		{
		// tabbed view
			if ( $this->active < 0 || $this->active >= count($this->tabs) )
			{
				$this->active = 0;
			}
			$s = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
			$s .= '<tr><td><table border="0" cellpadding="0" cellspacing="0" background="images/common/back_botones-01.gif"><tr>';
			$tab_unique = uniqid("tab");
			$j = 0;
			$jsscript = "var tabs = new Array(); ";
			foreach( $this->tabs as $k => $v ) 
			{
				$jsscript .= "tabs[$j] = '$tab_unique$j';\n ";
				
				//$class = ($k == $this->active) ? 'tabon' : 'taboff';
				$class = 'taboff';
				$sel = ($k == $this->active) ? 'Selected' : '';

				$s.="<td width=\"6\" background=\"images/common/inicio_1linea.gif\" valign='top'><img src=\"images/1x1.gif\" width=\"6\" height=\"19\"></td>";
				
				$s.="\n\t\t<td ><img src=\"images/common/cuadradito_naranja.gif\" width=\"9\" height=\"9\" align=\"left\">";
				$s.="\n\t\t\t<a id=\"$tab_unique$j\" href=\"".$v[0]."\" onclick=\"javascript: tabbuttonchangestate('$tab_unique$j'); \" class=\"special\"  >".$AppUI->_($v[1])."</a>";
				$s.="\n\t\t</td>";
				$s.="\n\t\t<td width=\"6\" background=\"images/common/fin_1linea.gif\" valign='top' align='right'><img src=\"images/1x1.gif\" width=\"6\" height=\"1\"></td>";
				$j++;
			}
			$s .= '</tr></table></td></tr>';
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
}
?>