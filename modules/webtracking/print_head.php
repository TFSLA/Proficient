<?php
function select ($selname, $sql, $select=0){
	$rc=db_exec($sql);
	if (mysql_num_rows($rc)!=0){
		ECHO "<select name='$selname' onchange='javascript: this.form.submit();'>";
		ECHO "<option></option>";
		if (db_num_rows($rc)!=0){
			while ($vec=db_fetch_array($rc)) {
				if ($vec['0']==$select) $sel="selected";
				else $sel="";
				ECHO "<option value='".$vec['0']."' $sel>".$vec['1']." ".$vec['2']."</option>";
			}
		}
		ECHO "</select>";
	}
	ELSE{
		ECHO "<FONT COLOR='RED'><B>N/A</B></FONT>\n";
	}
	
}

function hidden ($_GET, $inc){
	echo "<INPUT type='hidden' name='inc' value='$inc'>\n";
	echo "<INPUT type='hidden' name='bug' value=".$_GET['bug'].">\n";
	echo "<INPUT type='hidden' name='au' value=".$_GET['au'].">\n";
	echo "<INPUT type='hidden' name='cpn' value=".$_GET['cpn'].">\n";
	echo "<INPUT type='hidden' name='cnl' value=".$_GET['cnl'].">\n";
	echo "<INPUT type='hidden' name='cpnc' value=".$_GET['cpnc'].">\n";
	echo "<INPUT type='hidden' name='tipo' value=".$_GET['tipo'].">\n";
	echo "<INPUT type='hidden' name='nosearch' value='1'>\n";
}

function inform ($_GET){
	echo "<FORM action='index_inc.php' method='GET' target='todo'>\n";
	$inc="modules/webtracking/print_frame.php";
	hidden ($_GET, $inc);
}

function outform (){
	echo "</FORM>";
}

$sql['1']="SELECT permission_user FROM permissions WHERE (permission_grant_on='webtracking' OR permission_grant_on='all')  AND permission_user=".$AppUI->user_id;
$vec=db_fetch_array(db_exec($sql['1']));

?>

<script>
function framePrint(whichFrame){
parent[whichFrame].focus();
parent[whichFrame].print();
}
</script>
<?php

IF ($vec['permission_user']==$AppUI->user_id){
	?>
	<HTML>
	<link rel="stylesheet" type="text/css" href="./style/<?php echo "$uistyle"; ?>/main.css" media="all" />
	<BODY>
	<TABLE align="center" width="95%" border="1">
	
	<TH bgcolor="Black" colspan="5"><b><font color="White">SELECCI&Oacute;N DE DATOS</font></b></TH>
	<TR>
		<TD align="center">
			<TABLE>
				<TR align="center" valign='middle'>
					<TD valign='middle'>CANAL:</TD>
					<?PHP
						inform ($_GET);
						$sql="SELECT company_id, company_name FROM companies WHERE company_type=0 ORDER BY company_name";
						$selname="cnl";
						echo "<TD valign='middle'>"; 
							select($selname, $sql, $_GET['cnl']);	?>
					</TD>
				</FORM>
				</TR>
			</TABLE>
		</TD>
		<TD align="center">
			<TABLE>
				<!--<TR>
					<TD valign='middle'>CLIENTE:</TD>
					<TD valign='middle'>
						<?PHP
							inform ($_GET);
							$sql="SELECT company_id, company_name FROM companies WHERE company_type=1 ORDER BY company_name";
							$selname="cpn";
							select($selname, $sql, $_GET['cpn']);
						?>
					</TD>
				</FORM>
				</TR>-->
				<TR valign='middle'>
					<TD valign='middle'>CONTACTO:</TD>
					<?PHP
						inform ($_GET);
						$sql="SELECT
										c.contact_id, 
									CONCAT(contact_last_name, ', ',contact_first_name)
									FROM contacts AS c 
									INNER JOIN companies AS cpn
										ON c.contact_company=cpn.company_name
								WHERE
										cpn.company_id='".$_GET['cpn']."'";;
						$selname="cpnc";
						echo "<TD valign='middle'>";
							select($selname, $sql, $_GET['cpnc']);		
						?>
					</TD>
				</FORM>
				</TR>
			</TABLE>
		</TD>
		<!--<TD align="center">
			<TABLE>
				<TR align="center">
					<TD valign='middle'>RESPONSABLE:</TD>
					<TD valign='middle'>
						<?PHP
							inform ($_GET);
							$sql="SELECT user_id, user_last_name, user_first_name  FROM users ORDER BY user_last_name";
							$selname="au";
							select($selname, $sql, $_GET['au']);
						?>
					</TD>
				</FORM>
				</TR>
			</TABLE>
		</TD>-->
		<TD align="center">
			<TABLE align="center" width="95%">
			<TR align="center" valign='middle'>
				<?php
						inform ($_GET);
					?>
				<TD valign='middle'>
					<select name='tipo' onchange='javascript: this.form.submit();'>
					<?
						IF ($_GET['tipo']==1) $sel0='Selected';
						ELSE $sel1='Selected';
					?>
					<option value='0' <?php ECHO "$sel0"; ?> >COPIA</option>
					<option value='1' <?php ECHO "$sel0"; ?> >ORIGINAL</option>
					</select>
				</TD>
				</FORM>
			</TR>
			</TABLE>
		</TD>
	</TR>
	</TABLE>
	<TABLE align="right" width="100%">
		<TR>
			<TD align="right">
				<FORM action="index_inc.php" method="GET" target="formulario">
					<?php
					$inc="modules/webtracking/print_bug.php";
					hidden ($_GET, $inc);
					?>
					<INPUT type="button" class="button" value="Imprimir" onclick="javascript:framePrint('formulario');">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				</FORM>
			</TD>
		</TR>
	</TABLE>
	</BODY>
	</HTML>
	<?PHP
	}
?>	