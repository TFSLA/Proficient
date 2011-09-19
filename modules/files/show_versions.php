<?php
GLOBAL $AppUI, $xajax, $msg;
$xajax->printJavascript('./includes/xajax/');
include_once( "./functions/files_func.php");
include_once( "./modules/files/files.class.php");
include_once( "./includes/permissions.php");
include_once( $AppUI->getModuleClass( 'projects' ) );
include_once( $AppUI->getModuleClass( 'articles' ) );
include_once("{$AppUI->cfg['root_dir']}/modules/pipeline/leads.class.php");
//echo $file_id ;

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

//BEGIN SECURITY
$query_file = "SELECT file_section, file_project, is_private, file_owner, file_opportunity FROM files WHERE file_id = '".$file_id."' ";
$sql =  db_exec( $query_file );
$data_perm = mysql_fetch_array($sql);
$section_file = $data_perm[0];
$project_file = $data_perm[1];
$is_private = $data_perm[2];
$file_user_id = $data_perm[3];
$file_opportunity = $data_perm[4];

//Validacion si el archivo es privado
if($is_private == 1 && $file_user_id != $AppUI->user_id)
	if($AppUI->user_type != 1)
		$AppUI->redirect( "m=public&a=access_denied" );

//  Por si acceden directamente poniendo la direccion , verifico los permisos
$accessdenied = true;

$objProject = new CProject();
$prjs = $objProject->getAllowedRecords($AppUI->user_id, "project_id");

$leads = CLead::getAllowedLeads();

if ($project_file > 0 && (array_key_exists($project_file, $prjs))){
	$accessdenied = false;
}
elseif($file_opportunity > 0){
	if (array_key_exists($file_opportunity, $leads))
		$accessdenied = false;
	else{
		$usr = new CUser();
		$usr->load( $AppUI->user_id );
		$delegs = $usr->getDelegators();

		foreach( $delegs as $deleg )
		{
			$leads = CLead::getAllowedLeads($deleg["delegator_id"], 0);
			if(array_key_exists($file_opportunity, $leads))
				$accessdenied = false;
		}
	}
}
else{
	if($section_file <> 0){
		if(!getDenyRead('articles')){
			$accessdenied = false;
		}
		else{
			$userSections = CSection::getSectionsByUser();

			if (in_array($section_file, $userSections))
				$accessdenied = false;
		}
	 }
}

if($project_file == 0 && $section_file == 0 && $file_opportunity == 0 && !getDenyRead('files'))
			$accessdenied = false;

# Si el articulo esta relacionado a una incidencia permito que lo vean
$select_bug = mysql_query("SELECT count(id) as cant_kb_bug FROM btpsa_bug_kb WHERE kb_item='$file_id' AND kb_type='2' ");
$bug_row = mysql_fetch_array($select_bug, MYSQL_ASSOC);

if($bug_row['cant_kb_bug']>0) $accessdenied =false;

if ($accessdenied)
	$AppUI->redirect( "m=public&a=access_denied" );

//END SECURITY

//Si hay algo para borrar lo borramos
if ($del != 0)
{
	if( ultima_ver() == "TRUE")
	{
		$msg=del_file($del);

		if( $msg == NULL)
		{
			$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
			//$AppUI->redirect( );
		}
		else
		{
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
			//$AppUI->redirect();
		}

		$del = 0;
		$recovery = 0;

		echo'
			<script language="javascript">
				window.opener.location.reload()
				window.close();
			</script>';
	}
	else
		$msg=del_file_version($del);

		if ( $msg != NULL )
		{
			if ($msg) echo "<img src='./images/icons/stock_cancel-16.png' width='16' height='16'><font color='red'>".$msg."</font>\n";
		}
		else
		{
			$msg=$AppUI->_("deleted");
			if ($msg) echo "<img src='./images/icons/stock_ok-16.png' width='16' height='16'><td class='message'>".$msg."</td>\n";
		}

		$del = 0;
		$recovery = 0;
}


if ($recovery != 0)
{
	if ( ($msg = recover_file_version($recovery) )  != NULL )
	{
		if ($msg) echo "<img src='./images/icons/stock_cancel-16.png' width='16' height='16'><font color='red'>".$msg."</font>\n";
	}
	else
	{
		$msg=$AppUI->_("recovered");
		if ($msg) echo "<img src='./images/icons/stock_ok-16.png' width='16' height='16'><td class='message'>".$msg."</td>\n";
	}
			$del = 0;
		$recovery = 0;
}



$sql = "select version, delete_pending, description, date, user_first_name, user_last_name, file_name, id_files_ver, author, files.file_id
from files_versions
LEFT JOIN users ON user_id = author
LEFT JOIN files on files.file_id = files_versions.file_id
WHERE files_versions.file_id = $file_id "
.( $_POST['mostrar_versiones_borradas']=='on' ? "" : "AND delete_pending = 0 " )
."order by version desc ";
?>


<html>
	<head>
		<title><?php echo $AppUI->_('Files') ." - " .$AppUI->_('Show Versions'); ?> </title>
	</head>
		<link rel="stylesheet" type="text/css" href="./style/<?php echo "$uistyle"; ?>/main.css" media="all" />
	<body>
		<table width="100%" border='0' cellpadding="0" cellspacing="0">
			<TR>
				<td width="6">
					<img src="./images/common/inicio_title_section.gif" height="34" width="6">
				</td>
				<td width="38" background="./images/common/back_title_section.gif">
					<img src="./modules/files/images/files.gif" alt="" border="0" height="29" width="29">
				</td>
				<td class="titularmain2" background="./images/common/back_title_section.gif" align="center">
					<?php echo "<B>" .$AppUI->_('Files') ." - " .$AppUI->_('Show Versions') ."</B>"; ?>
				</td>
				<td valign="bottom" width="6">
					<img src="./images/common/fin_title_section.gif" height="34" width="6">
				</td>
			</TR>
		</table>


		<table background="./images/common/back_1linea_06.gif" border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td width="6">
					<img src="./images/common/inicio_1linea.gif" height="19" width="6">
				</td>
				<td width="100%">
					<img src="./images/common/cuadradito_naranja.gif" height="9" width="9">
					<span class="boldblanco"> <?php echo $AppUI->_('Files') ." - " .$AppUI->_('Show Versions'); ?></span>
				</td>
				<td align="right" width="6">
					<img src="./images/common/fin_1linea.gif" height="19" width="3">
				</td>
			</tr>

			<tr bgcolor="#666666">
				<td colspan="3" height="1"></td>
			</tr>


			<tr valign="top">
				<td colspan="3">
					<form action="" method="post">
						<table background="images/common/back_degrade.gif" border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr>
								<td width="6"><img src="images/common/ladoizq.gif" height="19" width="6"></td>
								<td align="right">
									<?

									include_once('./modules/public/itemToFavorite_functions.php');
									$deleteFavorite = HasItemInFavorites($file_id, 8);

									echo ("<a href=\"javascript:itemToFavorite(".$file_id.", 8, $deleteFavorite);\">".($deleteFavorite == 1 ? $AppUI->_('Remove from favorites') : $AppUI->_('Add to favorites'))."</a>&nbsp;");

									if(canVersion($file_id) && !getDenyEdit( "files" )){
									?>

									<a href="./index.php?m=files&a=addedit&file_id=<?=$file_id?>" title="<?=$AppUI->_('Add Version')?>"><?=$AppUI->_('Add Version')?></a>
									<?php } ?>
									&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

									<?php
									IF ( $_POST['mostrar_versiones_borradas']=='on')
										$check='checked';

									echo $AppUI->_('Show deleted')?>
									<INPUT TYPE="CHECKBOX" onclick="submit()" NAME="mostrar_versiones_borradas" VALUE="on" <?php echo $check ?> >

								</td>
							</tr>
						</table>
					</form>
				</td>
			</tr>

		</table>


	<table width="100%" border="0" cellpadding="2" cellspacing="0" class="">

		<col width="10"  >
		<col width="100" >
		<col width="5" >
		<col width="30" >
		<col width="230" >
		<col width="10" >
		<col width="110" >
		<col width="120" >
		<tr class="tableHeaderGral">
			<th align="left"> </th>
			<th align="left"> <?php echo $AppUI->_( 'File Name' ); ?> </th>
			<th></th>
			<th align="left"> <?php echo $AppUI->_( 'Version' );?> </th>
			<th align="left"> <?php echo $AppUI->_( 'Comment' );?></th>
			<th></th>
			<th align="left"> <?php echo $AppUI->_( 'Date' );?> </th>
			<th align="left"> <?php echo $AppUI->_( 'Author' );?> </th>
		</tr>

		<?php
		$rc = mysql_query($sql);
		while ($row = mysql_fetch_array($rc) )
		{
			$canDelete = CFile::canDeleteVer($file_id, $row["author"] );
		?>

			<form action="" method="post">
				<TR>
					<TD align="left" width="38">
						<?php

						if ($canDelete)
						{
							if ($row["delete_pending"])
							{
								echo '<input type="image" onclick="return validar_recuperacion_version()" src="./images/icons/log-notice.gif" title="'. $AppUI->_( 'Recover File' ).'" />
								<input type="hidden" name="recovery" value="'.$row['id_files_ver'].'" />';
							}
							else
							{
								echo '<input type="image" onclick="return validar_borrado();   "  src="./images/icons/trash_small.gif" title="'. $AppUI->_( 'delete file' ).'" />
								<input type="hidden" name="del" value="'.$row['id_files_ver'].'" />';
							}
						}

						$lastHistoryData = CFile::getHistory($file_id, 2, 4, $row['version']);

						if($lastHistoryData)
						{
							$historyDate = new CDate($lastHistoryData['history_date']);
							$historyDataText = $lastHistoryData['fullname'].' '.$AppUI->_( 'on' ).' '.$historyDate->format($AppUI->getPref('SHDATEFORMAT').' '.$AppUI->getPref('TIMEFORMAT'));
							echo ("<img src=\"/images/sign.gif\" alt=\"".$historyDataText."\" border=\"0\" />");
						}

						?>



					</TD>
					<TD align="left">
						<?php
						$file_parts = pathinfo($row['file_name']);

						echo "<a href=\"../../fileviewer.php?file_id={$file_id}&id_files_ver={$row['id_files_ver']}\" title=\"{$row['description']}\">".
											dPshowImage( getImageFromExtension($file_parts["extension"]), '16', '16', $row['description'] ).
											"&nbsp;".
											($row["delete_pending"] ? "<font color='#ff0000'>" : "").
											cutString($row['file_name']).
											($row["delete_pending"] ? "</font>" : "").
									"</a>";
						?>
					</TD>
					<TD></TD>
					<TD align="left"> <?
						echo ($row["delete_pending"] ? "<font color='#ff0000'>" : "");
						echo $row['version'];
						echo ($row["delete_pending"] ? "</font>" : "");?>
					</TD>
					<TD align="left"> <?
						echo ($row["delete_pending"] ? "<font color='#ff0000'>" : "");
						//echo cutString($row['description'], 70,0);
						echo $row['description'];
						echo ($row["delete_pending"] ? "</font>" : "");?>
					</TD>
					<TD width="10"></TD>
					<TD align="left"> <?
						echo ($row["delete_pending"] ? "<font color='#ff0000'>" : "");
						$file_date = new CDate( $row['date'] ); echo $file_date->format( "$df $tf" );
						echo ($row["delete_pending"] ? "</font>" : "");?>
					</TD>
					<TD align="left"> <?
						echo ($row["delete_pending"] ? "<font color='#ff0000'>" : "");
						echo $row['user_last_name'].", ".$row['user_first_name'];
						echo ($row["delete_pending"] ? "</font>" : "");?>
					</TD>
				</TR>
			</form>

		<?
		}
		?>
	</form>
<tr><td colspan='10'><span id='new_0'></span></td></tr>
<tr><td colspan='10'><span id='0'></span></td></tr>
</TABLE>

	<!--FOOTER-->
<? require "./style/$uistyle/footer.php";?>

<script language="javascript">
<!--
	function validar_borrado()
	{
		var ultimo;
		ultimo = "<?php echo ultima_ver();?>";
		if ( ultimo == "TRUE" )
			return confirm( "<?php echo $AppUI->_('filesDelete');?>" );
		else
			return confirm( "<?php echo $AppUI->_('versionDelete');?>" );
	}
	xajax_edit(0, <? echo $file_id; ?>, 0, 1);
	xajax_notes(0, <? echo $file_id; ?>, 1);
//-->

function itemToFavorite(item_id, item_type, item_delete)
{
	window.parent.opener.top.location = "./index.php?m=public&a=itemToFavorite&item_id=" + item_id + "&item_type=" + item_type + "&item_mode_del=" + item_delete + "&dialog=1&suppressLogo=1";
	window.top.location.reload();
}

</script>
</script>

<?php

$obj = new CFile();
$obj->file_id=$file_id;
$obj->showHistory();

function canVersion ($file_id) {
	global $AppUI;
	$sql = "SELECT file_owner, is_protected FROM files WHERE file_id = $file_id";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);

	if($row['is_protected']==0){
		return true;
	}

	if($row['file_owner']==$AppUI->user_id || $AppUI->user_type==1){
		return true;
	}else {
		return false;
	}
}

?>