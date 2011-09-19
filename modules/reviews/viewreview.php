<?php /* $Id: viewreview.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
$AppUI->savePlace();

// setup the title block
$titleBlock = new CTitleBlock( 'Reviews', 'tasks.gif', $m, "colaboration.index" );
$titleBlock->show();

$result = mysql_query("SELECT user_email, review_id, user_username, reviews.user_id, producttitle, review, score, relatedlink, linktitle, hits, DATE_FORMAT(date,'%m/%d/%Y') as datefmt  FROM reviews, users WHERE users.user_id = reviews.user_id AND review_id = $id ");
$row = mysql_fetch_array($result, MYSQL_ASSOC);
$newhits = $row["hits"]+1;
$result2 = mysql_query("update reviews set hits = $newhits WHERE review_id = $id ");

?>
<table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#9cbee6"><tr><td>
<table width="100%" border="0" cellspacing="1" cellpadding="8" bgcolor="#ffffff">
<tr><td>
<p><i><b><font class="title"><?=$row["producttitle"]?></b></i></font><br>
<BLOCKQUOTE><p align=justify><?=str_replace("\n","<br>",$row["review"])?>
</BLOCKQUOTE>
<?
if ($canEdit || $row["user_id"]==$AppUI->user_id) {
?>
<p>[ <a href="index.php?m=reviews&a=newreview&id=<?=$row["review_id"]?>"><?=$AppUI->_('Edit')?></a> | <a href="index.php?m=reviews&a=deletereview&id=<?=$row["review_id"]?>"><?=$AppUI->_('Delete')?></a> ]<br>
<?}?>
<b><?=$AppUI->_('Added')?>:</b> <?=$row["datefmt"]?><br>
<b><?=$AppUI->_('Reviewer')?>:</b> <a href="mailto:<?=$row["user_email"]?>"><?=$row["user_username"]?></a><br>
<b><?=$AppUI->_('Score')?>:</b>
<?
if($row["score"]==10)
for($i=0;$i<5;$i++) echo '<img src="modules/reviews/images/star.gif" alt="">';
else
for($i=0;$i<floor($row["score"]/2);$i++) echo '<img src="modules/reviews/images/blue.gif" alt="">';
if($row["score"]%2==1) echo '<img src="modules/reviews/images/bluehalf.gif" alt="">';

echo "<br>";
if($row["relatedlink"]!="" && $row["relatedlink"]!="http://")
  echo '<b>'.$AppUI->_('Related Link').':</b> <a href="'.$row["relatedlink"].'" target=new>'.$row["linktitle"].'</a><br>';
?>

<b><?=$AppUI->_('Hits')?>:</b> <?=$row["hits"]?><br>
<br>
[ <a href="index.php?m=reviews"><?=$AppUI->_('Back to Review Index')?></a> | <a href="index.php?m=reviews&a=postcomment&review_id=<?=$id?>"><?=$AppUI->_('Post Comment')?></a> ]
</td></tr>
</table>
</td></tr>
</table>
<?
$sql = "SELECT user_username, user_email, reviewcomment_id, reviewcomments.user_id, review, score, DATE_FORMAT(date,'%m/%d/%Y') as datefmt FROM reviewcomments, users WHERE users.user_id=reviewcomments.user_id AND reviewcomments.review_id={$row["review_id"]} ORDER BY date";
$comments = db_loadList( $sql );
foreach ($comments as $rowc) {
?>
<br>
<table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#9cbee6"><tr><td>
<table width="100%" border="0" cellspacing="1" cellpadding="8" bgcolor="#ffffff">
<tr><td>
<b><font class="title"><?=$row["producttitle"]?></b></font>
<?
if ($canEdit || $row["user_id"]==$AppUI->user_id) {
?>
<br>[<!-- <a href="index.php?m=reviews&a=newreviewcomment&id=<?=$rowc["reviewcomment_id"]?>">Edit</a> |--> <a href="index.php?m=reviews&a=deletereviewcomment&id=<?=$rowc["reviewcomment_id"]?>"><?=$AppUI->_('Delete')?></a> ]<br>
<?}?>
<?=$AppUI->_('Posted by')?> <a href="mailto:<?=$rowc["user_email"]?>"><?=$rowc["user_username"]?></a> <?=$AppUI->_('on')?> <?=$row["datefmt"]?><br>
<?=$AppUI->_('My Score')?>:
<?
if($rowc["score"]==10)
for($i=0;$i<5;$i++) echo '<img src="modules/reviews/images/star.gif" alt="">';
else
for($i=0;$i<floor($rowc["score"]/2);$i++) echo '<img src="modules/reviews/images/blue.gif" alt="">';
if($rowc["score"]%2==1) echo '<img src="modules/reviews/images/bluehalf.gif" alt="">';
echo "<br>";
?>
<hr noshade size=1>
<BLOCKQUOTE><p align=justify><?=str_replace("\n","<br>",$rowc["review"])?>
</BLOCKQUOTE>

</td></tr>
</table>
</td></tr>
</table>


<?
}
?>
