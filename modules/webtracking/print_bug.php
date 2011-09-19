<?php
$sql="SELECT permission_user FROM permissions WHERE (permission_grant_on='webtracking' OR permission_grant_on='all') AND permission_user=".$AppUI->user_id;
$vec=db_fetch_array(db_exec($sql));
IF ($vec['permission_user']==$AppUI->user_id){							// Verifico que el usuario tenga permisos en el módulo
	
	// Look & Feel
	$font['0']="<b>";
	$font['1']="</b>";
	
	$td['0']="25%";
	$td['1']="75%";

	// Busco datos Bug
	if ($bug){
		$sql="SELECT b.id, b.category, bt.description FROM btpsa_bug_table AS b
						INNER JOIN btpsa_bug_text_table as bt
							on (b.bug_text_id=bt.id)
						INNER JOIN projects AS p
							ON (b.project_id=p.project_id)
						INNER JOIN users as u
							ON (b.reporter_id=u.user_id)
						WHERE b.id=".$_GET['bug'];
		$vec_bug=db_fetch_array(db_exec($sql));
	}

if ($_GET['cpnc']){
	$sql="SELECT
					CONCAT(contact_last_name, ', ',contact_first_name) 
					AS cpnc_name
				FROM contacts AS c 
				WHERE
					contact_id=".$_GET['cpnc'];
		$vec_cpnc=db_fetch_array(db_exec($sql));
}
	// Traigo datos logo y Por Cuenta y Orden
	if ($_GET['cnl']){
		$sql="SELECT fsize, fname, ftype, fheight, fwidth, company_name FROM companies WHERE company_id=".$_GET['cnl'];
		$vec_cnl=db_fetch_array(db_exec($sql));
		$nologo='0';
		if ($vec_cnl['fsize']!=''){
			// Genero Logo
			$alto=$vec_cnl['fheight']/150;
			$ancho=$vec_cnl['fwidth']/150;
			if ($alto>$ancho) {
				$height=$vec_cnl['fheight']/$alto;
				$width=$vec_cnl['fwidth']/$alto;
				
			}
			else{
				$height=$vec_cnl['fheight']/$ancho;
				$width=$vec_cnl['fwidth']/$ancho;
			}
		}
		else 	$nologo='1';
	}
	
	// Traigo datos cliente
	if ($_GET['cpn']){
	$sql="SELECT company_name, company_phone1, company_address1, company_city FROM companies WHERE company_id=".$_GET['cpn'];
	$vec_cpn=db_fetch_array(db_exec($sql));
	}

	// Busco datos responsable
	if ($_GET['au']){
	$sql="SELECT user_last_name, user_first_name, user_email, state_name  FROM users AS u 
				LEFT JOIN location_states AS l
					ON (l.country_id=u.user_country_id AND l.state_id=u.user_state_id)
				WHERE user_id=".$_GET['au'];
	$vec_au=db_fetch_array(db_exec($sql));
	$username=$vec_au['user_last_name'].", ".$vec_au['user_first_name'];
	}
	?>
	<HTML>
	<link rel="stylesheet" type="text/css" href="./style/<?php echo "$uistyle"; ?>/main.css" media="all" />
	<BODY>
	<table align="center" width="100%" >
	<?php
		if ($nologo=='0') ECHO "<TH width='110'><img src='./includes/view.php?mod=1&id=".$_GET['cnl']."' height='$height' width='$width'></TH>";
	?>
	<TH bgcolor="Black">
		<FONT color="White" size="+1"><b>Constancia de Prestaci&oacute;n de Servicios</b></FONT>
	</TH>
	</table>
	<br>
	<table align="center" width="100%" border="1">
	<TR>
		<TD width="50%">
			<TABLE align="left" width="100%">
				<TR>
					<TD width="<?php echo $td['0']; ?>">CATEGOR&Iacute;A:</TD>
					<TD width="<?php echo $td['1']; ?>"><?php echo $font['0'].$vec_bug['category'].$font['1']; ?></TD>
				</TR>
			</TABLE>
		</TD>
		<TD width="50%" valign="middle" align="left">
			<TABLE width="100%" border="0">
							<TR>
								<TD width="<?php echo $td['0']; ?>">NEGOCIO NRO.:</TD>
								<TD width="<?php echo $td['1']; ?>"><?php echo $font['0'].$vec_bug['id'].$font['1']; ?></TD>
							</TR>
			</TABLE>
		</TD>
	</TR>
	<TR>
		<TD>
			<TABLE width="100%" border="0">
				<TR>
					<TD width="<?php echo $td['0']; ?>">CLIENTE:</TD>
					<TD width="<?php echo $td['1']; ?>"><?php echo $font['0'].$vec_cpn['company_name'].$font['1']; ?></TD>
				</TR>
			</TABLE>
				
		</TD>
		<TD>
			<TABLE width="100%" border="0">
				<TR>
					<TD width="<?php echo $td['0']; ?>">RESPONSABLE:</TD>
					<TD width="<?php echo $td['1']; ?>"><?php echo $font['0'].$username.$font['1']; ?></TD>
				</TR>
			</TABLE>
		</TD>
	</TR>
	<TR>
		<TD>
			<TABLE width="100%" border="0">
				<TR>
					<TD width="<?php echo $td['0']; ?>">DOMICILIO:</TD>
					<TD width="<?php echo $td['1']; ?>"><?php echo $font['0'].$vec_cpn['company_address1'].$font['1']; ?></TD>
				</TR>
			</TABLE>
				
		</TD>
		<TD>
			<TABLE width="100%" border="0">
				<TR>
					<TD width="<?php echo $td['0']; ?>">E-MAIL:</TD>
					<TD width="<?php echo $td['1']; ?>"><?php echo $font['0']."<a href='mailto:".$vec_au['user_email']."'>".$vec_au['user_email']."</a>".$font['1']; ?></TD>
				</TR>
			</TABLE>
		</TD>
	</TR>
	<TR>
		<TD>
			<TABLE width="100%" border="0">
				<TR>
					<TD width="<?php echo $td['0']; ?>">TELEFONO:</TD>
					<TD width="<?php echo $td['1']; ?>"><?php echo $font['0'].$vec_cpn['company_phone1'].$font['1']; ?></TD>
				</TR>
			</TABLE>
		</TD>
		<TD ROWSPAN=3 valign="top">
			<TABLE width="100%" border="0">
			<TR>
				<TD <?php echo $td['0']; ?> >REF. TAREAS:</TD>
				<TD width="<?php echo $td['1'] ?> valign="top"><?php echo $font['0'].$vec_bug['description'].$font['1']; ?></TD>
			</TR>
			</TABLE>
		</TD>
	</TR>
	<TR><!--
		<TD>
			<TABLE width="100%" border="0">
				<TR>
					<TD width="<?php echo $td['0']; ?>">Por Cuenta y Orden de:</TD>
					<TD width="<?php echo $td['1']; ?>"><?php echo $font['0'].$vec_cnl['company_name'].$font['1']; ?></TD>
				</TR>
			</TABLE>
			
		</TD>-->
		<TD>
			<TABLE width="100%" border="0">
				<TR>
					<TD width="<?php echo $td['0']; ?>">LOCALIDAD:</TD>
					<TD width="<?php echo $td['1']; ?>">
						<?php echo $font['0'].$vec_cpn['company_city'].$font['1']; ?>
					</TD>
				</TR>
			</TABLE>
		</TD>
		<TD></TD>
	</TR>
	<TR>
		<TD>	
			<TABLE width="100%" border="0">
				<TR>
					<TD width="<?php echo $td['0']; ?>">SOLICITADO POR:</TD>
					<TD width="<?php echo $td['1']; ?>">
						<?php echo $font['0'].$vec_cpnc['cpnc_name'].$font['1']; ?>
					</TD>
				</TR>
			</TABLE>
		</TD>
	</TR>
	<TR>
		
	</TR>
	<TR>
		<TD colspan="2" align="center">DETALLE DE SERVICIOS REALIZADOS</TD>
	</TR>
	<TR>
		<TD colspan="2" align="center">
		<FONT size="-2">
		<br>
		<?php
			for ($i = 1; $i <= 15; $i++) {
				echo "<hr><br>";
			}
		?>
		</FONT>
		</TD>
	</TR>
	<TR>
		<TD>Fecha/Hora de Inicio:
		</TD>
		<TD>Fecha/Hora de finalizaci&oacute;n:
		</TD>
	</TR>
	<TR>
		<TD>Cantidad Horas Normales:
		</TD>
		<TD>Horas Nocturnas/Fin de Semana:
		</TD>
	</TR>
	<TR>
		<TD colspan="2">
			<font size="-1">NOTA:&nbsp;&nbsp;&nbsp; El presente documento es comprobante del servicio prestado y su firma solamente significa conformidad del mismo.&nbsp;&nbsp;El costo del servicio estar&aacute; determinado por las condiciones previamente contratadas con quien corresponda.</font>
		</TD>
	</TR>
	<TR>	
		<TD align="center">
			Conforme Cliente:
			<br>
			<br>
			_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _<br>
			Firma y Aclaraci&oacute;n
		</TD>
		<TD align="center">
			por <?php echo $vec_cnl['company_name']?>:
			<br>
			<br>
			_ _ _ _ _ _ _ _ _ _ _ _ _ _ _ _<br>
			Firma y Aclaraci&oacute;n
		</TD>
	</TR>
	</TABLE>
	<?php
	IF ($_GET['tipo']==1) ECHO "<CENTER>ORIGINAL para ".$vec_cnl['company_name'].":</CENTER>";
	ELSEIF ($_GET['tipo']==0) ECHO "<CENTER>COPIA para CLIENTE</CENTER>";
}
/*ECHO "SQL['1'] = $sql['1'] <BR>";
ECHO "<br>SQL['2']".$sql['2']."<br>";
ECHO "<BR>".$vec['permission_user']."=".$AppUI->user_id."<BR>";
ECHO "VEC = $vec <BR>"; */
?>
</BODY>
</HTML>