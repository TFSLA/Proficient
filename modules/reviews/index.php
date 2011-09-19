<?php /* $Id: index.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */

if (getDenyRead( $m ))
	$AppUI->redirect( "m=public&a=access_denied" );

$AppUI->savePlace();

// setup the title block
$titleBlock = new CTitleBlock( 'Reviews', 'tasks.gif', $m, "colaboration.index" );
$titleBlock->show();
?>
<center>
<br><b><?=$AppUI->_('Welcome to Reviews Section at')?>&nbsp;<?=$AppUI->getConfig('company_name')?></b><br><br>

<?
    $alphabet = array ("A","B","C","D","E","F","G","H","I","J","K","L","M",
                       "N","O","P","Q","R","S","T","U","V","W","X","Y","Z","1","2","3","4","5","6","7","8","9","0");
    $num = count($alphabet) - 1;
    echo "<center>[ ";
    $counter = 0;
    while (list(, $ltr) = each($alphabet)) {
        echo "<a href=\"index.php?m=reviews&a=reviewlist&initial=$ltr\">$ltr</a>";
        if ( $counter == round($num/2) ) {
            echo " ]\n<br>\n[ ";
        } elseif ( $counter != $num ) {
            echo "&nbsp;|&nbsp;\n";
        }
        $counter++;
    }
    echo " ]</center><br>\n\n\n";
?>

[ <a href="index.php?m=reviews&a=newreview"><?=$AppUI->_('Write a Review')?></a> ]<br><br>

</center>

<table align="center" width="90%" class="tbl">
<tr>
  <th>10 <?=$AppUI->_('most popular reviews')?></th>
  <th>10 <?=$AppUI->_('most recent reviews')?></th>
</tr>
<tr>
  <td><ul>
<?
$sql = "SELECT review_id, user_id, producttitle, review, score, relatedlink, linktitle, DATE_FORMAT(date,'%m/%d/%Y') as datefmt FROM reviews ORDER BY hits DESC LIMIT 0,10";
$articles = db_loadList( $sql );
$i=0;
foreach ($articles as $row) {
 $i++;
 echo "<li>".$i.") <a href='index.php?m=reviews&a=viewreview&id=".$row["review_id"]."'>".$row["producttitle"]." - ".$row["datefmt"]."</a></li>";
}
$i++;
for(;$i <= 10; $i++) echo "<li>".$i.") _________________</li>"
?>
  </ul></td>
  <td><ul>
<?
$sql = "SELECT review_id, user_id, producttitle, review, score, relatedlink, linktitle, DATE_FORMAT(date,'%m/%d/%Y') as datefmt FROM reviews ORDER BY date DESC LIMIT 0,10";
$articles = db_loadList( $sql );
$i=0;
foreach ($articles as $row) {
 $i++;
 echo "<li>".$i.") <a href='index.php?m=reviews&a=viewreview&id=".$row["review_id"]."'>".$row["producttitle"]." - ".$row["datefmt"]."</a></li>";
}
$i++;
for(;$i <= 10; $i++) echo "<li>".$i.") _________________</li>"
?>
  </ul></td>
</tr>
</table>
<?
$result = mysql_query("SELECT * FROM reviews");
$numreviews=mysql_num_rows($result);
?>
<br>
<center><?=$AppUI->_('There are')." $numreviews " .$AppUI->_('Reviews in the Database')?></center>
