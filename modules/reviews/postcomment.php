<?php /* $Id: postcomment.php,v 1.1 2009-05-19 21:15:45 pkerestezachi Exp $ */
$AppUI->savePlace();

// setup the title block
$titleBlock = new CTitleBlock( 'Reviews', 'tasks.gif', $m, "colaboration.index" );
$titleBlock->show();



if($sendcomment!=""){
  if($review=="") $msg=$AppUI->_("Your comment can not be empty!");
  else {
    $user_id=$AppUI->user_id;
    if($id==""){
      $result = mysql_query("INSERT INTO reviewcomments (review_id, user_id, date, review, score) VALUES ('$review_id','$user_id',NOW(),'$review','$score')");
      echo '<br><center><b>'.$AppUI->_("Your review has been entered").'</b><br><br>Click <a href="index.php?m=reviews&a=viewreview&id='.$review_id.'">'.$AppUI->_("here").'</a> '.$AppUI->_("to see your review").'<br>Click <a href="index.php?m=reviews">'.$AppUI->_("here").'</a> '.$AppUI->_("to see all the reviews").'<br></center>';
    }
/*    else{
      $result = mysql_query("UPDATE reviewcomments SET user_id='$user_id', producttitle='$producttitle', review='$review', score='$score', relatedlink='$relatedlink', linktitle='$linktitle' WHERE review_id = $id");
      echo '<br><center><b>Your review has been updated</b><br><br>Click <a href="index.php?m=reviews&a=viewreview&id='.$review_id.'">here</a> to see your review<br>Click <a href="index.php?m=reviews&a=viewreview&id='.$id.'">here</a> to see your review<br>Click <a href="index.php?m=reviews">here</a> to see all the reviews<br></center>';
      

    }
*/
    die();
  }
}
/*
if($id!=""){
  $result = mysql_query("SELECT user_email, review_id, user_username, reviews.user_id, producttitle, review, score, relatedlink, linktitle, hits, DATE_FORMAT(date,'%m/%d/%Y') as datefmt  FROM reviews, users WHERE users.user_id = reviews.user_id AND review_id = $id ");
  $row = mysql_fetch_array($result, MYSQL_ASSOC);
  $producttitle = $row["producttitle"];
  $review = $row["review"];
  $score = $row["score"];
  $relatedlink = $row["relatedlink"];
  $linktitle = $row["linktitle"];
}
*/
?>




<table width="100%" border="0" cellspacing="1" cellpadding="0" bgcolor="#9cbee6"><tr><td>
<table width="100%" border="0" cellspacing="1" cellpadding="8" bgcolor="#ffffff"><tr><td>
<p align="right"><a href="javascript:history.go(-1)"><?=$AppUI->_("back")?></a></p>
<font class=option><b><?=$AppUI->_("Comment on the Review")?> : <?=$AppUI->_("Product")?></b><br></font>
<?if($msg!="")echo "<br><font color='red'>".$msg."<br><br></font>"?>
<form action="index.php?m=reviews&a=postcomment" method=post>
    <input type=hidden name=review_id value=<?=$review_id?>>
    <b><?=$AppUI->_("Your Product Score")?></b>
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
<br><br>
    <b><?=$AppUI->_("Your Comment")?>:</b><br>
    <textarea name=review rows=10 cols=70><?=$review?></textarea><br>
<?=$AppUI->_("Your actual comment. You may use HTML tags if you know how to use them")?>.
<br><br>
    <input type=hidden name=rop value=savecomment>
		<input type="button" onClick="history.go(-1)" value="<?=$AppUI->_('cancel')?>" class='button'>   <input type="submit" name="sendcomment" value="<?=$AppUI->_('submit')?>" class='button'>
    </form>
    </td></tr></table></td></tr></table>