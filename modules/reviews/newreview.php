<?php /* $Id: newreview.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
$AppUI->savePlace();

// setup the title block
$titleBlock = new CTitleBlock( 'Reviews', 'tasks.gif', $m, "colaboration.index" );
$titleBlock->show();
?>

<?
if($publish!=""){
  if($producttitle=="") $msg="You must specify the Product Title!";
  else if($review=="") $msg="Your review can not be empty!";
  else {
    $user_id=$AppUI->user_id;
    if($id==""){
      $result = mysql_query("INSERT INTO reviews (user_id, date, producttitle, review, score, relatedlink, linktitle, hits) VALUES ('$user_id',NOW(),'$producttitle','$review','$score','$relatedlink','$linktitle','$hits')");
      $result = mysql_query("SELECT MAX(review_id) as maxid FROM reviews");
      $row    = mysql_fetch_array($result, MYSQL_ASSOC);
      echo '<br><center><b>Your review has been entered</b><br><br>Click <a href="index.php?m=reviews&a=viewreview&id='.$row["maxid"].'">here</a> to see your review<br>Click <a href="index.php?m=reviews">here</a> to see all the reviews<br></center>';
    }
    else{
      $result = mysql_query("UPDATE reviews SET user_id='$user_id', producttitle='$producttitle', review='$review', score='$score', relatedlink='$relatedlink', linktitle='$linktitle' WHERE review_id = $id");
      echo '<br><center><b>Your review has been updated</b><br><br>Click <a href="index.php?m=reviews&a=viewreview&id='.$id.'">here</a> to see your review<br>Click <a href="index.php?m=reviews">here</a> to see all the reviews<br></center>';
    }
    die();
  }
}

if($id!=""){
  $result = mysql_query("SELECT user_email, review_id, user_username, reviews.user_id, producttitle, review, score, relatedlink, linktitle, hits, DATE_FORMAT(date,'%m/%d/%Y') as datefmt  FROM reviews, users WHERE users.user_id = reviews.user_id AND review_id = $id ");
  $row = mysql_fetch_array($result, MYSQL_ASSOC);
  $producttitle = $row["producttitle"];
  $review = $row["review"];
  $score = $row["score"];
  $relatedlink = $row["relatedlink"];
  $linktitle = $row["linktitle"];
}

?>




<table width="100%" border="0" cellspacing="1" cellpadding="8"><tr><td>

    <b><?=$AppUI->_('Write a Review for')?> <?=$AppUI->getConfig('company_name')?></b><br><br>
<?if($msg!="")echo "<font color='red'>".$msg."<br><br></font>"?>
    <i><?=$AppUI->_('Please enter information according to the specifications')?></i><br><br>
    <form method="post" action="index.php?m=reviews&a=newreview">
    <input type="hidden" name="id" value="<?=$id?>">
    <b><?=$AppUI->_('Product Title')?>:</b><br>
    <input type="text" name="producttitle" size="50" maxlength="150" value="<?=$producttitle?>"><br>
    <i><?=$AppUI->_('Name of the Reviewed Product')?>.</i><br><br><br><b><?=$AppUI->_('Review')?>:</b><br>
    <textarea name="review" rows="12" wrap="virtual" cols="60"><?=$review?></textarea><br><font class="content"><br>
    <i><?=$AppUI->_('Your actual review. Please observe proper grammar! Make it at least 100 words if possible. You may also use HTML tags if you know how to use them')?>.</i><br><br>
    <b><?=$AppUI->_('Score')?>:</b><br>
    <select name="score">
    <option name="score" <?if($score==10)echo "selected ";?>value="10">10</option>
    <option name="score" <?if($score==9)echo "selected ";?>value="9">9</option>
    <option name="score" <?if($score==8)echo "selected ";?>value="8">8</option>
    <option name="score" <?if($score==7)echo "selected ";?>value="7">7</option>
    <option name="score" <?if($score==6)echo "selected ";?>value="6">6</option>
    <option name="score" <?if($score==5)echo "selected ";?>value="5">5</option>
    <option name="score" <?if($score==4)echo "selected ";?>value="4">4</option>
    <option name="score" <?if($score==3)echo "selected ";?>value="3">3</option>
    <option name="score" <?if($score==2)echo "selected ";?>value="2">2</option>
    <option name="score" <?if($score==1)echo "selected ";?>value="1">1</option>
    </select>
    <i><?=$AppUI->_('This Product Score')?></i><br><br>
    <b><?=$AppUI->_('Related Link')?>:</b><br>
    <input type="text" name="relatedlink" size="40" maxlength="100" value="<?if($relatedlink=="") echo "http://"; else echo $relatedlink?>"><br>
    <i><?=$AppUI->_("Product Official Website. Make sure your URL starts with 'http://'")?></i><br><br>
    <b><?=$AppUI->_('Link Title')?>:</b><br>
    <input type="text" name="linktitle" size="40" maxlength="50" value="<?=$linktitle?>"><br>
    <i><?=$AppUI->_('Required if you have a related link, otherwise not required')?>.</i><br><br>
<!--    
	<b>Image Filename:</b><br>
	<input type="text" name="cover" size="40" maxlength="100"><br>
	<i>Name of the cover image, located in images/reviews/. Not required.</i><br><br>
-->	<i><?=$AppUI->_('Please make sure that the information entered is 100% valid and uses proper grammar and capitalization')?>.</i><br><br><input type="hidden" name="rop" value="preview_review">
     <input type="button" onClick="history.go(-1)" value="<?=$AppUI->_('cancel')?>" class='button'>   <input type="submit" name="publish" value="<?=$AppUI->_('submit')?>" class='button'></form></td></tr></table></td></tr></table>
