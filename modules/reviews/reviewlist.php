<?php /* $Id: reviewlist.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
$AppUI->savePlace();

// setup the title block
$titleBlock = new CTitleBlock( 'Reviews', 'tasks.gif', $m, "colaboration.index" );
$titleBlock->show();
?>
<center>
<br><b><?=$AppUI->_('Welcome to Reviews Section at')?>&nbsp;<?=$AppUI->getConfig('company_name')?></b><br><br>

[ <a href="index.php?m=reviews&a=newreview"><?=$AppUI->_('Write a Review')?></a> ]<br><br>

</center>

<table align="center" width="90%" class="tbl">
<tr>
  <th><?=$AppUI->_('List of reviews starting with')?> "<?=$initial?>"</th>
</tr>
<tr>
  <td><ul>
<?
$sql = "SELECT review_id, user_id, producttitle, review, score, relatedlink, linktitle, DATE_FORMAT(date,'%m/%d/%Y') as datefmt FROM reviews WHERE producttitle LIKE '$initial%' ORDER BY date DESC ";
$articles = db_loadList( $sql );
$i=0;
foreach ($articles as $row) {
 $i++;
 echo "<li>".$i.") <a href='index.php?m=reviews&a=viewreview&id=".$row["review_id"]."'>".$row["producttitle"]." - ".$row["datefmt"]."</a></li>";
}
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
<br><center><a href="index.php?m=reviews"><?=$AppUI->_('Back to Review Index')?></a></center>
