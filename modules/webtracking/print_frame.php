<HTML>
<?php
$sql['1']="SELECT permission_user FROM permissions WHERE (permission_grant_on='webtracking' OR permission_grant_on='all') AND permission_user=".$AppUI->user_id;
$vec=db_fetch_array(db_exec($sql['1']));
IF ($vec['permission_user']==$AppUI->user_id){
if ($_GET['nosearch']!=1){
		$sql="SELECT
			".$_GET['p_bug_id']." AS bug,
			b.project_id,
			b.handler_id AS au,
			c1.company_id AS cpn,
			p.project_canal AS cnl,
			c.contact_id AS cpnc
		FROM btpsa_bug_table AS b
		INNER JOIN projects AS p
			ON p.project_id=b.project_id
		INNER JOIN companies AS c1
			ON p.project_company=c1.company_id
		INNER JOIN companies AS c
			ON (c.company_id=p.project_canal)
		WHERE 
			b.id=".$_GET['p_bug_id'];
		$vec=db_fetch_array(db_exec($sql));
		//echo "<br>$sql<br>";
		$vec['tipo']=0;
	}
	ELSE {
	$vec=$_GET;
	}

	$var	="&bug=".$vec['bug'];								//BUG_ID
	$var .="&au=".$vec['au'];								//ASIGNED USER
	$var .="&cpn=".$vec['cpn'];							//COMPANY_ID
	$var .="&cnl=".$vec['cnl'];							//CANAL_ID
	$var .="&cpnc=".$vec['cpnc'];						//COMPANY_CONTACT_ID
	$var .="&tipo=".$vec['tipo'];						//TIPO_FORM

	$headlink="index_inc.php?inc=modules/webtracking/print_head.php$var";
	$mainlink="index_inc.php?inc=modules/webtracking/print_bug.php$var";
	?>
		<FRAMESET ROWS="110,*" border='1'>
			<FRAME NAME="encabezado" SRC="<?php echo $headlink ?>" TITLE="encabezado">
			<FRAME NAME="formulario" SRC="<?php echo $mainlink ?>" TITLE="formulario">
		</FRAMESET>

	<?php
}
?>
</HTML>