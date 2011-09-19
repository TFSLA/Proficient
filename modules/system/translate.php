<?php /* SYSTEM $Id: translate.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */

$AppUI->redirect( "m=public&a=access_denied" );

// only user_type of Administrator (1) can access this page
if (!$canEdit || $AppUI->user_type != 1) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$module = isset( $_REQUEST['module'] ) ? $_REQUEST['module'] : 'admin';
$lang = isset( $_REQUEST['lang'] ) ? $_REQUEST['lang'] : 'en';

$AppUI->savePlace( "m=system&a=translate&module=$module&lang=$lang" );

// read the installed modules
$modules = arrayMerge( array( 'common', 'styles' ), $AppUI->readDirs( 'modules' ));

// read the installed languages
$locales = $AppUI->readDirs( 'locales' );

ob_start();
	@readfile( "{$AppUI->cfg['root_dir']}/locales/en/$modules[$module].inc" );
	eval( "\$english=array(".ob_get_contents()."\n'0');" );
ob_end_clean();

$trans = array();
foreach( $english as $k => $v ) {
	if ($v != "0") {
		$trans[ (is_int($k) ? $v : $k) ] = array(
			'english' => $v
		);
	}
}

//echo "<pre>";print_r($trans);echo "</pre>";die;

if ($lang != 'en') {
	ob_start();
		@readfile( "{$AppUI->cfg['root_dir']}/locales/$lang/$modules[$module].inc" );
		eval( "\$locale=array(".ob_get_contents()."\n'0');" );
	ob_end_clean();

	foreach( $locale as $k => $v ) {
		if ($v != "0") {
			$trans[$k]['lang'] = $v;
		}
	}
}
ksort($trans);

$titleBlock = new CTitleBlock( 'Translation Management', 'language_support.gif', $m, "$m.$a" );
$titleBlock->addCell(
	$AppUI->_( 'Module' ), '',
	'<form action="?m=system&a=translate" method="post" name="modlang">', ''
);
$titleBlock->addCell(
	arraySelect( $modules, 'module', 'size="1" class="text" onchange="document.modlang.submit();"', $module )
);
$titleBlock->addCell(
	$AppUI->_( 'Language' )
);
$temp = $AppUI->setWarning( false );
$titleBlock->addCell(
	arraySelect( $locales, 'lang', 'size="1" class="text" onchange="document.modlang.submit();"', $lang, true ), '',
	'', '</form>'
);
$AppUI->setWarning( $temp );

$titleBlock->addCrumb( "?m=system", "system admin" );
$titleBlock->show();
?>

<table width="100%" border="0" cellpadding="1" cellspacing="0" class="">
<tr class="tableHeaderGral">
	<th width="15%" nowrap><?php echo $AppUI->_( 'Abbreviation' );?></th>
	<th width="40%" nowrap>English <?php echo $AppUI->_( 'String' );?></th>
	<th width="40%" nowrap><?php echo $AppUI->_( $locales[$lang] ).' '.$AppUI->_( 'String' );?></th>
	<th width="5%" nowrap><?php echo $AppUI->_( 'delete' );?></th>
</tr>
<form action="?m=system&a=translate_save" method="post" name="editlang">
<input type="hidden" name="module" value="<?php echo $modules[$module];?>" />
<input type="hidden" name="lang" value="<?php echo $lang;?>" />
<?php
$index = 0;
if ($lang == 'en') {
	echo "<tr>\n";
	echo "<td><input type=\"text\" name=\"trans[$index][abbrev]\" value=\"\" size=\"20\" class=\"text\" /></td>\n";
	echo "<td><input type=\"text\" name=\"trans[$index][english]\" value=\"\" size=\"40\" class=\"text\" /></td>\n";
	//echo "<td colspan=\"2\">New Entry</td>\n";
    echo "<td>New Entry</td>\n";
    echo "<td><input type=\"submit\" value=\"".$AppUI->_( 'submit' )."\" class=\"button\" /></td>\n";
	echo "</tr>\n";
    echo "<tr class=\"tableRowLineCell\"><td colspan=\"5\"></td></tr>";
}

$index++;
foreach ($trans as $k => $langs){
?>
<tr>
	<td><?php
		if ($k != @$langs['english']) {
			$k = dPformSafe( $k, true );
			if ($lang == 'en') {
				echo "<input type=\"text\" name=\"trans[$index][abbrev]\" value=\"$k\" size=\"20\" class=\"text\" />";
			} else {
				echo $k;
			}
		} else {
			echo '&nbsp;';
		}
	?></td>
	<td><?php
		$langs['english'] = htmlspecialchars( @$langs['english'], ENT_QUOTES );
		if ($lang == 'en') {
			if (strlen($langs['english']) < 40) {
				echo "<input type=\"text\" name=\"trans[$index][english]\" value=\"{$langs['english']}\" size=\"40\" class=\"text\" />";
			} else {
			  $rows = round(strlen($langs['english']/35)) +1 ;
			  echo "<textarea name=\"trans[$index][english]\"  cols=\"40\" class=\"small\" rows=\"$rows\">".$langs['english']."</textarea>";
			}
		} else {
			echo $langs['english'];
			echo "<input type=\"hidden\" name=\"trans[$index][english]\" value=\""
				.($k ? $k : $langs['english'])
				."\" size=\"20\" class=\"text\" />";
		}
	?></td>
	<td><?php
		if ($lang != 'en') {
			$langs['lang'] = dPformSafe( @$langs['lang'], true );
			if (strlen($langs['lang']) < 40) {
				echo "<input type=\"text\" name=\"trans[$index][lang]\" value=\"{$langs['lang']}\" size=\"40\" class=\"text\" />";
			} else {
			  $rows = round(strlen($langs['lang']/35)) +1 ;
			  echo "<textarea name=\"trans[$index][lang]\"  cols=\"40\" class=\"small\" rows=\"$rows\">".$langs['lang']."</textarea>";
			}
		}
	?></td>
	<td align="center"><?php echo "<input type=\"checkbox\" name=\"trans[$index][del]\" />";?></td>
</tr>
<tr class="tableRowLineCell">
    <td colspan="4"></td>
</tr>
<?php
	$index++;
}
?>
<tr>
	<td colspan="4" align="right">
		<input type="submit" value="<?php echo $AppUI->_( 'submit' );?>" class="button" />
	</td>
</tr>
</form>
</table>
