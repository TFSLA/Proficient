<?php /* $Id: deletereviewcomment.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
$AppUI->savePlace();

// setup the title block
$titleBlock = new CTitleBlock( 'Reviews', 'tasks.gif', $m, "colaboration.index" );
$titleBlock->show();

$result = mysql_query("SELECT review_id, reviewcomments.user_id FROM reviewcomments, users WHERE users.user_id = reviewcomments.user_id AND reviewcomment_id = $id ");
$row = mysql_fetch_array($result, MYSQL_ASSOC);
if ($canEdit || $row["user_id"]==$AppUI->user_id) {
  $resultd = mysql_query("DELETE FROM reviewcomments WHERE reviewcomment_id = $id ");
}
    echo '<br><center><b>'.$AppUI->_('The selected review comment has been DELETED').'</b><br><br>Click <a href="index.php?m=reviews&a=viewreview&id='.$row["review_id"].'">'.$AppUI->_('here').'</a> '.$AppUI->_('to see your review').'<br><a href="index.php?m=reviews">'.$AppUI->_('Back to Review Index').'</a><br></center>';
    die();
