<?php /* $Id: deletereview.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
$AppUI->savePlace();

// setup the title block
$titleBlock = new CTitleBlock( 'Reviews', 'tasks.gif', $m, "colaboration.index" );
$titleBlock->show();

$result = mysql_query("SELECT user_email, review_id, user_username, reviews.user_id, producttitle, review, score, relatedlink, linktitle, hits, DATE_FORMAT(date,'%m/%d/%Y') as datefmt  FROM reviews, users WHERE users.user_id = reviews.user_id AND review_id = $id ");
$row = mysql_fetch_array($result, MYSQL_ASSOC);
if ($canEdit || $row["user_id"]==$AppUI->user_id) {
  $resultd = mysql_query("DELETE FROM reviews WHERE review_id = $id ");
}
    echo '<br><center><b>'.$AppUI->_('The selected review has been DELETED').'</b><br><br><a href="index.php?m=reviews">'.$AppUI->_('Back to Review Index').'</a><br></center>';
    die();
